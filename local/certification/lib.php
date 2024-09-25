<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms 
 * @subpackage local_certification
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/user/selector/lib.php');
require_once($CFG->libdir . '/formslib.php');
use \local_certification\form\certification_form as certification_form;
use local_certification\local\querylib;
use local_certification\certification;

function local_certification_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    // Make sure the filearea is one of those used by the plugin.
    //if ($filearea !== 'certificationlogo') {
    //    return false;
    //}

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_certification', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_file($file, $filename, 0, $forcedownload, $options);
}

/**
 * Serve the new group form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_certification_output_fragment_certification_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_certification');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;

    $mform = new certification_form(null, array('id' => $args->id,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $certificationdata = new stdClass();
    $certificationdata->id = $args->id;
    $certificationdata->form_status = $args->form_status;
    $mform->set_data($certificationdata);

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $formstatus = array();
    foreach (array_values($mform->formstatus) as $k => $mformstatus) {
        $activeclass = $k == $args->form_status ? 'active' : '';
        $formstatus[] = array('name' => $mformstatus, 'activeclass' => $activeclass,'form-status' => $k);
    }
    $formstatusview = new \local_certification\output\form_status($formstatus);
    $return .= $renderer->render($formstatusview);
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}
/**
 * [certification_evaluation_types description]
 * @method certification_evaluation_types
 * @return [type]                     [description]
 */
function certification_evaluationtypes($evaluationid = 0, $instance = 0,$from="view",$id=-1) {
    global $DB;
    $certificationevaluationtypes = array(
                    1 => 'Training feedback',
                    2 =>'Trainer feedback'
                );
    $trainer_exist=$DB->record_exists('local_certification_trainers',array('certificationid'=>$instance));
    if(!$trainer_exist && $from=='form'){
         unset($certificationevaluationtypes[2]);
    }
    $exist_cl_fd = $DB->count_records('local_evaluations',array('instance'=>$instance,'plugin'=>'certification'));
    if($exist_cl_fd==0 && $from=='form'){
        $return = $certificationevaluationtypes;
    }
    elseif ($id > 0 && $from=='form') {
         $evaluationtype = $DB->get_field('local_evaluations','evaluationtype',array('id' => $id, 'plugin' => 'certification'));
        $return = array($evaluationtype=>$certificationevaluationtypes[$evaluationtype]);
        
    }elseif($from=='form'){
       
           $exist = $DB->record_exists('local_certification',array('id'=>$instance,'trainingfeedbackid'=>$evaluationid));
           $exist_id = $DB->get_field('local_certification','trainingfeedbackid',array('id'=>$instance));
           $exist_with_tr_fd = $DB->count_records_sql("SELECT count(id) as total FROM {local_certification_trainers} where certificationid=$instance AND feedback_id>0");
           $exist_with_tr = $DB->count_records('local_certification_trainers',array('certificationid'=>$instance));
           //
           //print_object($exist);
           // print_object($evaluationid);
           if($exist_id==0){
             $exist_id=-1;
           }
           
            if(($exist && $evaluationid>0)||(!$exist && $evaluationid<0 && $exist_id==$evaluationid )){
                unset($certificationevaluationtypes[2]);
            }
           
            elseif($exist_with_tr_fd==0 || ($exist_with_tr_fd!=$exist_with_tr) || ($exist_with_tr_fd==$exist_with_tr)){
                    unset($certificationevaluationtypes[1]);
            }
             
            $return=$certificationevaluationtypes;
    }else{
          $return=$certificationevaluationtypes;
    }

    return $return;
}
function certification_manage_evaluations($evaluation,$add_update_instance) {
    global $DB, $USER;
    $pluginevaluationtypes = certification_evaluationtypes();
    if($add_update_instance=='update'){
        $params = array('certificationid' => $evaluation->instance,
                'evaluationid' => $evaluation->id, 'timemodified' => time(),
                'usermodified' => $USER->id);
    }
    //echo "AS";
    //print_object($pluginevaluationtypes);
    // print_object($pluginevaluationtypes[$evaluation->evaluationtype]);exit;
    switch($pluginevaluationtypes[$evaluation->evaluationtype]) {
        case 'Trainer feedback':
            
            $sql='UPDATE {local_certification_trainers} SET timemodified = :timemodified,
                    usermodified = :usermodified WHERE feedback_id = :evaluationid AND certificationid = :certificationid';
            
            if($add_update_instance=='add'){
                
                    $sql='UPDATE {local_certification_trainers} SET
                            feedback_id = :evaluationid, timemodified = :timemodified,
                            usermodified = :usermodified WHERE id=:id AND certificationid = :certificationid
                            AND feedback_id = 0';
                    
                    $certificationtrainerssql = "SELECT ct.id,ct.certificationid FROM {user} AS u JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                    WHERE ct.certificationid = :certificationid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2 and ct.feedback_id = 0";
                    $params = array();
                    $params['certificationid'] = $evaluation->instance;
                   
                    $certificationtrainers = $DB->get_records_sql($certificationtrainerssql, $params);
                   
                    foreach($certificationtrainers as $certificationtrainer){
                        
                         $evaluationid = $DB->insert_record("local_evaluations", $evaluation);
                         $evaluation->id = $evaluationid;
                         
                         $params = array(
                                'context' => context_system::instance(),
                                'objectid' => $evaluationid
                         );
                        
                            $event = \local_certification\event\certification_feedbacks_created::create($params);
                            $event->add_record_snapshot('local_certification',$evaluation->instance);
                            $event->trigger();
                         
                        $params = array('certificationid' => $certificationtrainer->certificationid,
                                        'evaluationid' => $evaluation->id, 'timemodified' => time(),
                                        'usermodified' => $USER->id,'id'=>$certificationtrainer->id);
                        certification_evaluations_add_remove_users($certificationtrainer->certificationid,$evaluation->id,'feedback_to_users');
                         $return = $DB->execute($sql, $params);
                         
                           //$params = array(
                           //         'context' => context_system::instance(),
                           //         'objectid' => $evaluation->id
                           // );
                           // $event = \local_certification\event\certification_feedbacks_updated::create($params);
                           // $event->add_record_snapshot('local_certification',$certificationtrainer->certificationid);
                           // $event->trigger();
                    }
                
            }else{
                $return = $DB->execute($sql, $params);
                $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $evaluation->id
                );
                $event = \local_certification\event\certification_feedbacks_updated::create($params);
                $event->add_record_snapshot('local_certification',$evaluation->instance);
                $event->trigger();
        }
        break;
        case 'Training feedback':
            
            $sql='UPDATE {local_certification} SET
                trainingfeedbackid = :evaluationid, timemodified = :timemodified,
                usermodified = :usermodified WHERE id = :certificationid AND
                trainingfeedbackid = 0';
                
           if($add_update_instance=='add'){
                     $evaluationid = $DB->insert_record("local_evaluations", $evaluation);
                     $evaluation->id = $evaluationid;
                     $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $evaluationid
                     );
                    
                    $event = \local_certification\event\certification_feedbacks_created::create($params);
                    $event->add_record_snapshot('local_certification',$evaluation->instance);
                    $event->trigger();
                     $params = array('certificationid' => $evaluation->instance,
                                        'evaluationid' => $evaluation->id, 'timemodified' => time(),
                                        'usermodified' => $USER->id);
                      certification_evaluations_add_remove_users($evaluation->instance,$evaluation->id,'feedback_to_users');
                     $return = $DB->execute($sql, $params);
                    // $params = array(
                    //    'context' => context_system::instance(),
                    //    'objectid' => $evaluation->id
                    // );
                    //$event = \local_certification\event\certification_feedbacks_updated::create($params);
                    //$event->add_record_snapshot('local_certification',$evaluation->instance);
                    //$event->trigger();
           }else{
                    $return = $DB->execute($sql, $params);
                    $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $evaluation->id
                    );
                    $event = \local_certification\event\certification_feedbacks_updated::create($params);
                    $event->add_record_snapshot('local_certification',$evaluation->instance);
                    $event->trigger();
           }
            
        break;
        default:
            $return = false;
        break;
    }
    return $return;
}
function certification_evaluations_add_remove_users($certificationid,$evaluationid=0,$type,$add_update_user=0,$action='add'){
     global $DB, $USER;
     switch($type) {
        case 'feedback_to_users':
            
            $submitted = new stdClass();
            $submitted->timemodified = time();
            $submitted->timecreated = time();
            $submitted->evaluationid = $evaluationid;
            $submitted->creatorid = $USER->id;
                
            $fromsql = "SELECT cu.id,cu.userid FROM {local_certification_users} as cu
                    WHERE cu.certificationid= $certificationid ";//group by cu.userid
                    
             $certificationusers = $DB->get_records_sql($fromsql);
             
            foreach($certificationusers as $certificationuser){
                $submitted->userid = $certificationuser->userid;
              
                $exist = $DB->record_exists('local_evaluation_users',array('userid'=>$certificationuser->userid,'evaluationid'=>$evaluationid));
                if(empty($exist)){
                     $insert = $DB->insert_record('local_evaluation_users',$submitted);
                }
            }
            
        break;
        case 'users_to_feedback':
            $submitted = new stdClass();
            $submitted->timemodified = time();
            $submitted->timecreated = time();
            $submitted->creatorid = $USER->id;
            $submitted->userid = $add_update_user;
            $fromsql = "SELECT e.id
                 FROM {local_evaluations} AS e
                WHERE e.plugin = 'certification' AND e.instance = $certificationid ";
             $certificationevaluations = $DB->get_records_sql($fromsql);
             
            foreach($certificationevaluations as $certificationevaluation){
                $submitted->evaluationid = $certificationevaluation->id;
              
                $exist = $DB->record_exists('local_evaluation_users',array('userid'=>$add_update_user,'evaluationid'=>$certificationevaluation->id));
                if(empty($exist)){
                    if($action=='add'){
                     $insert = $DB->insert_record('local_evaluation_users',$submitted);
                    }
                }
                elseif($exist){
                    if($action=='update'){
                         $fromsql = "SELECT ec.id
                                        FROM {local_evaluation_completed} AS ec
                                       WHERE ec.evaluation = $certificationevaluation->id AND ec.userid = $add_update_user ";
                        $evaluationcompletions= $DB->get_records_sql($fromsql);
                       
                        foreach($evaluationcompletions as $evaluationcomple){
                             // print_object($evaluationcomple);exit;
                             $DB->delete_records('local_evaluation_value', array('completed' => $evaluationcomple->id));
                             $DB->delete_records('local_evaluation_completed', array('id' =>$evaluationcomple->id));
                        }
                             $DB->delete_records('local_evaluation_users',  array('evaluationid' => $certificationevaluation->id, 'userid' => $add_update_user));
                    }
                }
            }
             
        break;
     }
}
function certification_evaluation_completed($evaluationid,$userid,$type){
     global $CFG, $DB,$USER;
     $pluginevaluationtypes = certification_evaluationtypes();
     $evaluation=$DB->get_record_sql("SELECT id,instance,evaluationtype
                                     FROM {local_evaluations}
                                     WHERE id = $evaluationid AND plugin='certification'");

    if($evaluation){

        switch($pluginevaluationtypes[$evaluation->evaluationtype]) {
            case 'Trainer feedback':
                
                 $local_certification_trainers=$DB->get_record_sql("SELECT id,trainerid
                                                        FROM {local_certification_trainers}
                                                        WHERE certificationid = $evaluation->instance AND feedback_id=$evaluation->id");
                if($type=='add'){
                       
                        $params = (object)array('clrm_trainer_id'=>$local_certification_trainers->id,'certificationid' => $evaluation->instance,
                        'trainerid'=>$local_certification_trainers->trainerid,'userid' => $userid,'score'=>1,
                        'timecreated' => time(),'usercreated' => $USER->id);
                        
                       
                        $return =$DB->insert_record('local_certificatn_trainerfb',$params);
                }elseif($type=='update'){
                    
                    if($local_certification_trainers){
                      $DB->delete_records('local_certificatn_trainerfb', array('clrm_trainer_id'=>$local_certification_trainers->id));

                      $return = $DB->execute('UPDATE {local_certification_trainers} SET
                              feedback_id = 0,feedback_score=0, timemodified ='.time().',
                              usermodified = '.$USER->id.' WHERE id = '.$local_certification_trainers->id.'');
                    }
                
                     $params = array(
                        'context' => context_system::instance(),
                        'objectid' =>$evaluationid
                    );
                    $event = \local_certification\event\certification_feedbacks_deleted::create($params);
                    $event->add_record_snapshot('local_certification',$evaluation->instance);
                    $event->trigger();  
                }
                
            break;
            case 'Training feedback':
                
                 $params = array('certificationid' => $evaluation->instance,
                                 'timemodified' => time(),'usermodified' => $USER->id);
                 
                 if($type=='add'){
                    $params ['trainingfeedback']=1;
                    $params ['userid']=$userid;
                    $sqluserid="userid=:userid";
                 }elseif($type=='update'){
                    $params ['trainingfeedback']=0;
                    $sqluserid="1=1";
                 }
                 
                $sql='UPDATE {local_certification_users} SET
                    trainingfeedback = :trainingfeedback, timemodified = :timemodified,
                    usermodified = :usermodified WHERE '.$sqluserid.' AND certificationid = :certificationid';
                $return = $DB->execute($sql, $params);
                $return = $DB->execute('UPDATE {local_certification} SET
                    trainingfeedbackid = 0,training_feedback_score=0, timemodified ='.time().',
                    usermodified = '.$USER->id.' WHERE id = '.$evaluation->instance.'');
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $evaluation->id
                );
                $event = \local_certification\event\certification_feedbacks_deleted::create($params);
                $event->add_record_snapshot('local_certification',$evaluation->instance);
                $event->trigger();  
                
            break;
        }
    }
}
function local_certification_output_fragment_session_form($args) {
    global $CFG, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;
    $formdata['ctid'] = $args->ctid;
    $mform = new \local_certification\form\session_form(null, array('id' => $args->id,
        'ctid' => $args->ctid, 'form_status' => $args->form_status), 'post', '',
            null, true, $formdata);
    if ($args->id > 0) {
        $sessiondata = $DB->get_record('local_certification_sessions', array('id' => $args->id));
        $sessiondata->form_status = $args->form_status;
        $sessiondata->cs_description['text'] = $sessiondata->description;
        if($sessiondata->trainerid ==0){
            $sessiondata->trainerid=NULL;
        }
        $mform->set_data($sessiondata);
    }

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}
function local_certification_output_fragment_certification_completion_form($args) {
    global $CFG, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['id'] = $args->id;
    $formdata['ctid'] = $args->ctid;
    $mform = new \local_certification\form\certification_completion_form(null, array('id' => $args->id,
        'ctid' => $args->ctid, 'form_status' => $args->form_status), 'post', '',
            null, true, $formdata);
    if ($args->id > 0) {
        $certification_completiondata = $DB->get_record('local_certificatn_completion', array('id' => $args->id));
        $certification_completiondata->form_status = $args->form_status;
   
       
        if($certification_completiondata->sessionids=="NULL"){
            $certification_completiondata->sessionids=null;
        }
        if($certification_completiondata->courseids=="NULL"){
            $certification_completiondata->courseids=null;
        }

        $mform->set_data($certification_completiondata);
    }

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}
function local_certification_output_fragment_course_form($args) {
    global $CFG, $PAGE, $DB;
    $args = (object) $args;
    $context = $args->context;
    $return = '';
    $renderer = $PAGE->get_renderer('local_certification');
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $formdata['ctid'] = $args->id;

    $mform = new certificationcourse_form(null, array('ctid' => $args->ctid,
        'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $certificationdata = new stdClass();
    $certificationdata->id = $args->id;
    $certificationdata->form_status = $args->form_status;
    $mform->set_data($certificationdata);

    if (!empty((array) $serialiseddata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    $formheaders = array_keys($mform->formstatus);
    $nextform = array_key_exists($args->form_status, $formheaders);
    if ($nextform === false) {
        return false;
    }
    ob_start();
    $formstatus = new \local_certification\output\form_status(array_values($mform->formstatus));
    $return .= $renderer->render($formstatus);
    $mform->display();
    $return .= ob_get_contents();
    ob_end_clean();

    return $return;
}

class certificationcourse_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $ctid = $this->_customdata['ctid'];
        $context = context_system::instance();

        //$mform->addElement('header', 'general', get_string('addcourses', 'local_certification'));

        $mform->addElement('hidden', 'certificationid', $ctid);
        $mform->setType('certificationid', PARAM_INT);

        $courses = array();
        $course = $this->_ajaxformdata['course'];
        if (!empty($course)) {
            $course = implode(',', $course);
            $coursessql = "SELECT c.id, c.fullname
                              FROM {course} AS c
                              JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                             WHERE c.id IN ($course) AND c.visible = 1 AND CONCAT(',',c.open_identifiedas,',') LIKE '%,6,%' AND c.id <> " . SITEID;//FIND_IN_SET(6, c.open_identifiedas)
            $courses = $DB->get_records_sql_menu($coursessql);
        } else if ($id > 0) {
            $coursessql = "SELECT c.id, c.fullname
                              FROM {course} AS c
                              JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                              JOIN {local_certification_courses} AS cc ON cc.courseid = c.id
                             WHERE cc.certificationid = $ctid AND c.visible = 1 AND CONCAT(',',c.open_identifiedas,',') LIKE '%,6,%' ";//FIND_IN_SET(6, c.open_identifiedas)
            $courses = $DB->get_records_sql_menu($coursessql);
        }

        $options = array(
            'ajax' => 'local_certification/form-course-selector',
            'multiple' => true,
            'data-contextid' => $context->id,
        );
        $mform->addElement('autocomplete', 'course', get_string('course', 'local_certification'), $courses, $options);
        $mform->addRule('course', null, 'required', null, 'client');

        $mform->disable_form_change_checker();
    }
}

/**
 * User selector subclass for the list of potential users on the assign roles page,
 * when we are assigning in a context below the course level. (CONTEXT_MODULE and
 * some CONTEXT_BLOCK).
 *
 * This returns only enrolled users in this context.
 */
class local_certification_potential_users extends user_selector_base {
    protected $certificationid;
    protected $context;
    protected $courseid;
    /**
     * @param string $name control name
     * @param array $options should have two elements with keys grouctid and courseid.
     */
    public function __construct($name, $options) {
        global $CFG;
        if (isset($options['context'])) {
            $this->context = $options['context'];
        } else {
            $this->context = context::instance_by_id($options['contextid']);
        }
        $options['accesscontext'] = $this->context;
        parent::__construct($name, $options);
        $this->certificationid = $options['certificationid'];
        $this->organization = $options['organization'];
        $this->department = $options['department'];
        $this->email = $options['email'];
        $this->idnumber = $options['idnumber'];
        $this->uname = $options['uname'];
        $this->searchanywhere = true;
        require_once($CFG->dirroot . '/group/lib.php');
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = 'local/certification/lib.php';
        $options['certificationid'] = $this->certificationid;
        // $options['courseid'] = $this->courseid;
        $options['contextid'] = $this->context->id;
        return $options;
    }

    public function find_users($search) {
        global $DB;
        $params = array();
        $certification = $DB->get_record('local_certification', array('id' => $this->certificationid));
        if (empty($certification)) {
            print_error('certification not found!');
        }

        // Now we have to go to the database.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        if ($wherecondition) {
            $wherecondition = ' AND ' . $wherecondition;
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(u.id)';
        $params['confirmed'] = 1;
        $params['suspended'] = 0;
        $params['deleted'] = 0;

        $sql   = " FROM {user} AS u
                  WHERE 1 = 1
                        $wherecondition
                    AND u.id > 2 AND u.confirmed = :confirmed AND u.suspended = :suspended
                    AND u.deleted = :deleted
                        ";
        if ($certification->costcenter && (has_capability('local/certification:managecertification', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/certification:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
            $sql .= " AND u.open_costcenterid = :costcenter";
            $params['costcenter'] = $certification->costcenter;
        
            if ($certification->department &&(has_capability('local/certification:manage_owndepartments', context_system::instance())||
                                         has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
               $sql .= " AND u.open_departmentid = :department";
               $params['department'] = $certification->department;
            }
        }

        if (!empty($this->email)) {
            $sql.=" AND u.id IN ({$this->email})";
        }
       if (!empty($this->uname)) {
            $sql .=" AND u.id IN ({$this->uname})";
        }
        if (!empty($this->department)) {
            $sql .=" AND u.open_departmentid IN ($this->department)";
        }
        if (!empty($this->idnumber)) {
            $sql .=" AND u.id IN ($this->idnumber)";
        }

        $options = array('contextid' => $this->context->id, 'certificationid' => $this->certificationid, 'email' => $this->email, 'uname' => $this->uname, 'department' => $this->department, 'idnumber' => $this->idnumber, 'organization' => $this->organization);
        $local_certification_existing_users = new local_certification_existing_users('removeselect', $options);
        $enrolleduerslist = $local_certification_existing_users->find_users('', true);
        if (!empty($enrolleduerslist)) {
            $enrolleduers = implode(',', $enrolleduerslist);
            $sql .= " AND u.id NOT IN ($enrolleduers)";
        }

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        // If not, show them.
        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potusersmatching', 'local_certification', $search);
        } else {
            $groupname = get_string('potusers', 'local_certification');
        }

        return array($groupname => $availableusers);
    }
}

/**
 * User selector subclass for the list of users who already have the role in
 * question on the assign roles page.
 */
class local_certification_existing_users extends user_selector_base {
    protected $certificationid;
    protected $context;
    // protected $courseid;
    /**
     * @param string $name control name
     * @param array $options should have two elements with keys grouctid and courseid.
     */
    public function __construct($name, $options) {
        global $CFG;
        $this->searchanywhere = true;
        if (isset($options['context'])) {
            $this->context = $options['context'];
        } else {
            $this->context = context::instance_by_id($options['contextid']);
        }
        $options['accesscontext'] = $this->context;
        parent::__construct($name, $options);
        $this->certificationid = $options['certificationid'];
        $this->organization = $options['organization'];
        $this->department = $options['department'];
        $this->email = $options['email'];
        $this->idnumber = $options['idnumber'];
        $this->uname = $options['uname'];
        require_once($CFG->dirroot . '/group/lib.php');
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = 'local/certification/lib.php';
        $options['certificationid'] = $this->certificationid;
        // $options['courseid'] = $this->courseid;
        $options['contextid'] = $this->context->id;
        return $options;
    }
    public function find_users($search, $idsreturn = false) {
        global $DB;

        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $params['certificationid'] = $this->certificationid;
        $fields = "SELECT DISTINCT u.id, " . $this->required_fields_sql('u') ;
        $countfields = "SELECT COUNT(DISTINCT u.id) ";
        $params['confirmed'] = 1;
        $params['suspended'] = 0;
        $params['deleted'] = 0;
        $sql = " FROM {user} AS u
                JOIN {local_certification_users} AS cu ON cu.userid = u.id
                 WHERE $wherecondition
                AND u.id > 2 AND u.confirmed = :confirmed AND u.suspended = :suspended
                    AND u.deleted = :deleted AND cu.certificationid = :certificationid";
        if (!empty($this->email)) {
            $sql.=" AND u.id IN ({$this->email})";
        }
       if (!empty($this->uname)) {
            $sql .=" AND u.id IN ({$this->uname})";
        }
        if (!empty($this->department)) {
            $sql .=" AND u.open_departmentid IN ($this->department)";
        }
        if (!empty($this->idnumber)) {
            $sql .=" AND u.id IN ($this->idnumber)";
        }
        if (!$this->is_validating()) {
            $existinguserscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($existinguserscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $existinguserscount);
            }
        }
        if ($idsreturn) {
            $contextusers = $DB->get_records_sql_menu('SELECT DISTINCT u.id, u.id as userid ' . $sql, $params);
            return $contextusers;
        } else {
            $order = " ORDER BY u.id DESC";
            $contextusers = $DB->get_records_sql($fields . $sql . $order, $params);
        }

        // No users at all.
        if (empty($contextusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('enrolledusersmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolledusers', 'enrol');
        }
        return array($groupname => $contextusers);
    }

    protected function this_con_group_name($search, $numusers) {
        if ($this->context->contextlevel == CONTEXT_SYSTEM) {
            // Special case in the System context.
            if ($search) {
                return get_string('extusersmatching', 'local_certification', $search);
            } else {
                return get_string('extusers', 'local_certification');
            }
        }
        $contexttype = context_helper::get_level_name($this->context->contextlevel);
        if ($search) {
            $a = new stdClass;
            $a->search = $search;
            $a->contexttype = $contexttype;
            if ($numusers) {
                return get_string('usersinthisxmatching', 'core_role', $a);
            } else {
                return get_string('noneinthisxmatching', 'core_role', $a);
            }
        } else {
            if ($numusers) {
                return get_string('usersinthisx', 'core_role', $contexttype);
            } else {
                return get_string('noneinthisx', 'core_role', $contexttype);
            }
        }
    }

    protected function parent_con_group_name($search, $contextid) {
        $context = context::instance_by_id($contextid);
        $contextname = $context->get_context_name(true, true);
        if ($search) {
            $a = new stdClass;
            $a->contextname = $contextname;
            $a->search = $search;
            return get_string('usersfrommatching', 'core_role', $a);
        } else {
            return get_string('usersfrom', 'core_role', $contextname);
        }
    }
}

function local_certification_output_fragment_new_catform($args) {
    global $CFG, $DB;

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    if ($args->categoryid > 0) {
        $heading = 'Update category';
        $collapse = false;
        $data = $DB->get_record('local_certification_categories', array('id' => $categoryid));
    }
    $editoroptions = [
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $course->maxbytes,
        'trust' => false,
        'context' => $context,
        'noclean' => true,
        'subdirs' => false,
    ];
    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

    $mform = new local_certification\form\catform(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $formdata);

    $mform->set_data($data);

    if (!empty($formdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
function local_certification_output_fragment_edit_element_form($args) {
    global $DB;
    
   $args = (object) $args;
    // $context = $args->context;
    // $categoryid = $args->categoryid;
    $o = '';
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }
    $element=new stdClass();
    $element->element=$args->element;
    
    $mform = new \local_certification\form\edit_element_form(null, array('id' => $args->id,'element'=>$element), 'post', '', null, true, $formdata);
 //$mform = new \local_certification\form\edit_form(null, array('id' => $args->id,
 //       'form_status' => $args->form_status), 'post', '', null, true, $formdata);
    $mform->set_data($data);

    if (!empty($formdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
/**
 * Serve the edit element as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_certification_output_fragment_editelement($args) {
    global $DB;

    // Get the element.
    $element = $DB->get_record('local_certification_elements', array('id' => $args['elementid']), '*', MUST_EXIST);

    $pageurl = new moodle_url('/local/certification/rearrange.php', array('ctid' => $element->pageid));
    $form = new \local_certification\form\edit_element_form($pageurl, array('element' => $element));

    return $form->render();
}

/**
 * Handles editing the 'name' of the element in a list.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param string $newvalue
 * @return \core\output\inplace_editable
 */
function local_certification_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $PAGE;

    if ($itemtype === 'elementname') {
        $element = $DB->get_record('local_certification_elements', array('id' => $itemid), '*', MUST_EXIST);
        $page = $DB->get_record('local_certification_pages', array('id' => $element->pageid), '*', MUST_EXIST);
        $template = $DB->get_record('local_certification_templts', array('id' => $page->templateid), '*', MUST_EXIST);

        // Set the template object.
        $template = new \local_certification\template($template);
        // Perform checks.
        //if ($cm = $template->get_cm()) {
        //    require_login($cm->course, false, $cm);
        //} else {
            $PAGE->set_context(context_system::instance());
            require_login();
        //}
        // Make sure the user has the required capabilities.
        $template->require_manage();

        // Clean input and update the record.
        $updateelement = new stdClass();
        $updateelement->id = $element->id;
        $updateelement->name = clean_param($newvalue, PARAM_TEXT);
        $DB->update_record('local_certification_elements', $updateelement);

        return new \core\output\inplace_editable('local_certification', 'elementname', $element->id, true,
            $updateelement->name, $updateelement->name);
    }
}

function certification_filter($mform){
    global $DB,$USER;
    $systemcontext = context_system::instance();
    $sql = "SELECT id, name FROM {local_certification} WHERE id > 1";
if ((has_capability('local/request:approverecord', context_system::instance()) || is_siteadmin())) {
        $certifications = (new certification)->certifications($stable,true);
        $componentid=$certifications['certifications']->certificationids;
        if($componentid){
          $courseslist = $DB->get_records_sql_menu("SELECT id, name FROM {local_certification} WHERE id IN ($componentid)");
        }
    }
    $select = $mform->addElement('autocomplete', 'certification', '', $courseslist, array('placeholder' => get_string('certification_name', 'local_certification')));
    $mform->setType('certification', PARAM_RAW);
    $select->setMultiple(true);
}
/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
// function local_certification_leftmenunode(){
//     $systemcontext = context_system::instance();
//     $certificationnode = '';
//     if(((has_capability('local/certification:managecertification', context_system::instance()))&&(!has_capability('local/certification:trainer_viewcertification', context_system::instance())))||(is_siteadmin())) {
//         $certificationnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_browsecertifications', 'class'=>'pull-left user_nav_div browsecertifications'));
//             $certification_url = new moodle_url('/local/certification/index.php');
//             $certification = html_writer::link($certification_url, '<i class="fa fa-graduation-cap"></i><span class="user_navigation_link_text">'.get_string('manage_certification','local_certification').'</span>',array('class'=>'user_navigation_link'));
//             $certificationnode .= $certification;
//         $certificationnode .= html_writer::end_tag('li');
//     }

//     return array('11' => $certificationnode);
// }
function local_certification_quicklink_node(){
    global $CFG, $PAGE, $OUTPUT;
    $systemcontext = context_system::instance();
    $stable = new stdClass();
    if(has_capability('local/certification:managecertification', $systemcontext) || is_siteadmin()){
      
      // $stable->thead = false;
      // $stable->start = 0;
      // $stable->length = 1;
      // $stable->certificationstatus = -1;
      // $certifications = (new certification)->certifications($stable);
      
      // $count_cr = $certifications['certificationscount'];
      
      // $stable->certificationstatus = 1;
      // $certifications = (new certification)->certifications($stable);
      
      // $count_activecr = $certifications['certificationscount'];
      
      // $stable->certificationstatus = 3;
      // $certifications = (new certification)->certifications($stable);
      
      // $count_cancelledcr = $certifications['certificationscount'];
      
      
      //       $local_certificate_content .= "<span class='anch_span'><i class='fa fa-graduation-cap' aria-hidden='true'></i></span>";
      //       $local_certificate_content .= "<div class='quick_navigation_detail'>
      //                                       <div class='span_str'>".get_string('manage_br_certifications', 'local_certification')."</div>";
      //           $local_certificate_content .= "<span class='span_createlink'>";
        
      //   if(has_capability('local/certification:createcertification', $systemcontext) || is_siteadmin()){
      //     $local_certificate_content .= "<a href='javascript:void(0);' class='quick_nav_link goto_local_certification' title='".get_string('create_certification', 'local_certification')."' onclick='(function(e){ require(\"local_certification/ajaxforms\").init({contextid:".$systemcontext->id.", component: \"local_certification\", callback:\"certification_form\", form_status:0, plugintype: \"local\", pluginname: \"certification\", id:0, title: \"createcertification\" }) })(event)' >".get_string('create')."</a> | ";
      //   }
        
      //           $local_certificate_content .= "<a href='".$CFG->wwwroot."/local/certification/index.php' class='viewlink' title= '".get_string('view_certification', 'local_certification')." '>".get_string('view')."</a>
      //                                       </span>";
      //       $local_certificate_content .= "</div>";
      //       $local_certificates = '<div class="quick_nav_list manage_certifications one_of_three_columns" >'.$local_certificate_content.'</div>';


        $certifications = array();
        $certifications['node_header_string'] = get_string('manage_br_certifications', 'local_certification');
        $certifications['pluginname'] = 'certifications';
        $certifications['plugin_icon_class'] = 'fa fa-graduation-cap';
        if(is_siteadmin() || has_capability('local/forum:addinstance', $systemcontext)){
            $certifications['create'] = TRUE;
            $certifications['create_element'] = html_writer::link('javascript:void(0)', get_string('create'), array('class' => 'quick_nav_link goto_local_certification', 'title' => get_string('create_certification', 'local_certification'), 'onclick' => '(function(e){ require("local_certification/ajaxforms").init({contextid:'.$systemcontext->id.', component: "local_certification", callback:"certification_form", form_status:0, plugintype: "local", pluginname: "certification", id:0, title: "createcertification" }) })(event)'));
        }
        // if(has_capability('local/courses:view', $systemcontext) || has_capability('local/courses:manage', $systemcontext)){
        $certifications['viewlink_url'] = $CFG->wwwroot.'/local/certification/index.php';
        $certifications['view'] = TRUE;
        $certifications['viewlink_title'] = get_string('view_certification', 'local_certification');
        // }
        $certifications['space_count'] = 'one';
        $content = $OUTPUT->render_from_template('block_quick_navigation/quicklink_node', $certifications);
        $content .= $PAGE->requires->js_call_amd('local_certification/ajaxforms', 'load');
    }
    
    return array('7' => $content);
}

/*
* Author Sarath
* return count of certifications under selected costcenter
* @return  [type] int count of certifications
*/
function costcenterwise_certification_count($costcenter,$department = false){
    global $USER, $DB;
        $params = array();
        $params['costcenter'] = $costcenter;
        $sql = "SELECT count(id) FROM {local_certification} WHERE costcenter = :costcenter ";

        if($department){
          $sql .= " AND department = :department ";
          $params['department'] = $department;
        }
        $count = $DB->count_records_sql($sql,$params);

        $newsql .= " AND status = 0 ";
        $activesql .= " AND status = 1 ";
        $cancelledsql .= " AND status = 3 ";
        $completedsql .= " AND status = 4 ";

        $newcertificationscount = $DB->count_records_sql($sql.$newsql,$params);
        $activecertificationscount = $DB->count_records_sql($sql.$activesql,$params);
        $cancelledcertificationscount = $DB->count_records_sql($sql.$cancelledsql,$params);
        $completedcertificationscount = $DB->count_records_sql($sql.$completedsql,$params);

    return array('certification_plugin_exist' =>true,'allcertificationcount' => $count,'newcertificationcount' => $newcertificationscount,'activecertificationcount' => $activecertificationscount,'cancelledcertificationcount' => $cancelledcertificationscount,'completedcertificationcount' => $completedcertificationscount);
}

/*
* Author sarath
* @return true for reports under category
*/
function learnerscript_certification_list(){
    return 'Certification';
}
function check_certificationenrol_pluginstatus($value){
 global $DB ,$OUTPUT ,$CFG;
$enabled_plugins = $DB->get_field('config', 'value', array('name' => 'enrol_plugins_enabled'));
$enabled_plugins =  explode(',',$enabled_plugins);
$enabled_plugins = in_array('certification',$enabled_plugins);

if(!$enabled_plugins){

    if(is_siteadmin()){
        $url = $CFG->wwwroot.'/admin/settings.php?section=manageenrols';
        $enable = get_string('enableplugin','local_certification',$url);
        echo $OUTPUT->notification($enable,'notifyerror');
    }
    else{
        $enable = get_string('manageplugincapability','local_certification');
        echo $OUTPUT->notification($enable,'notifyerror');
     }
   }    
}
