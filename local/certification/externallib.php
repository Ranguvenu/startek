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
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/certification/lib.php');
use \local_certification\certification as certification;
use \local_certification\form\certification_form as certification_form;

class local_certification_external extends external_api {

    public static function certification_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function certification_instance($id, $contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();

        $certification = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new certification_form(null, array('form_status' => $form_status), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $formheaders = array_keys($mform->formstatus);
            if (method_exists(new certification, $formheaders[$form_status])) {
                $certificationid = (new certification)->{$formheaders[$form_status]}($validateddata);
                if($formheaders[$form_status] == 'manage_certification' || $formheaders[$form_status] == 'certification_misc' || $formheaders[$form_status] == 'target_audience'){
                    if(class_exists('\block_trending_modules\lib')){
                        $trendingclass = new \block_trending_modules\lib();
                        if(method_exists($trendingclass, 'trending_modules_crud')){
                            $trendingclass->trending_modules_crud($certificationid, 'local_certification');
                        }
                    }
                }
            } else {
                throw new moodle_exception('missingfunction', 'local_certification');
            }
            $next = $form_status + 1;
            $nextform = array_key_exists($next, $formheaders);
            if ($nextform !== false/*&& end($formheaders) !== $form_status*/) {
                $form_status = $next;
                $error = false;
            } else {
                $form_status = -1;
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcertification', 'local_certification');
        }
        $return = array(
            'id' => $certificationid,
            'form_status' => $form_status);
        return $return;

    }

    public static function certification_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }

    public static function delete_certification_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'certificationid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'certificationname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function delete_certification_instance($action, $id, $confirm,$certificationname) {
        global $DB;
        try {
            
            //$certificationcourses = $DB->get_records_menu('local_certification_courses',
            //    array('certificationid' => $certificationid), 'courseid', 'id, courseid');
            //foreach($certificationcourses as $certificationcourse){
            //    $certificationtrainers = $DB->get_records_menu('local_certification_trainers',
            //        array('certificationid' => $certificationid), 'trainerid', 'id, trainerid');
            //    if (!empty($certificationtrainers)) {
            //        foreach ($certificationtrainers as $certificationtrainer) {
            //            $unenrolcertificationtrainer = (new certification)->manage_certification_course_enrolments($certificationcourse, $certificationtrainer,
            //                'editingteacher', 'unenrol');
            //        }
            //    }
            //    $certificationusers = $DB->get_records_menu('local_certification_users',
            //        array('certificationid' => $certificationid), 'userid', 'id, userid');
            //    if (!empty($certificationusers)) {
            //        foreach ($certificationusers as $certificationuser) {
            //            $unenrolcertificationuser = (new certification)->manage_certification_course_enrolments($certificationcourse, $certificationuser,
            //                'employee', 'unenrol');
            //        }
            //    }
            //    $DB->delete_records('local_certification_courses', array('certificationid' => $id,'courseid' => $certificationcourse));
            //}
        
            $DB->delete_records('local_certification_courses', array('certificationid' => $id));
            
            $local_evaluations=$DB->get_records_menu('local_evaluations',  array('plugin' =>'certification', 'instance' =>$id), 'id', 'id, id as evid');  
            foreach($local_evaluations as $local_evaluation){
                
                $DB->delete_records('local_evaluation_item', array('evaluation' => $local_evaluation));
                $DB->delete_records('local_evaluation_users',  array('evaluationid' => $local_evaluation));
                
                $evaluation_completions=$DB->get_records_menu('local_evaluation_completed',  array('evaluation' =>$local_evaluation), 'id', 'id, id as evcmtd');
                foreach($evaluation_completions as $evaluation_completion){
                    $DB->delete_records('local_evaluation_value', array('completed' =>$evaluation_completion));
                    $DB->delete_records('local_evaluation_completed', array('id' =>$evaluation_completion));
                }
                $DB->delete_records('local_evaluations',  array('id' => $local_evaluation));
            }
            
            
            $DB->delete_records('local_certificatn_attendance', array('certificationid' => $id));
            $DB->delete_records('local_certification_sessions', array('certificationid' => $id));
            
            $DB->delete_records('local_certification_users', array('certificationid' => $id));
            $DB->delete_records('local_certification_trainers', array('certificationid' => $id));
            $DB->delete_records('local_certificatn_trainerfb', array('certificationid' => $id));
            $DB->delete_records('local_certificatn_completion', array('certificationid' => $id));
			            
            // delete events in calendar
            $DB->delete_records('event', array('plugin_instance'=>$id, 'plugin'=>'local_certification'));            
			$params = array(
                    'context' => context_system::instance(),
                    'objectid' =>$id
            );
            
            $event = \local_certification\event\certification_deleted::create($params);
            $event->add_record_snapshot('local_certification', $id);
            $event->trigger();
            $DB->delete_records('local_certification', array('id' => $id));
            if(class_exists('\block_trending_modules\lib')){
                $trendingclass = new \block_trending_modules\lib();
                if(method_exists($trendingclass, 'trending_modules_crud')){
                    $certification_object = new stdClass();
                    $certification_object->id = $id;
                    $certification_object->module_type = 'local_certification';
                    $certification_object->delete_record = True;
                    $trendingclass->trending_modules_crud($certification_object, 'local_certification');
                }
            }
            $return = true;
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_certification');
            $return = false;
        }
        return $return;
    }

    public static function delete_certification_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function managecertificationStatus_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_RAW, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'certificationid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'actionstatusmsg' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'certificationname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function managecertificationStatus_instance($action, $id, $confirm,$actionstatusmsg,$certificationname) {
        global $DB,$USER, $PAGE;
        try {
            $PAGE->set_context(\context_system::instance());
            if ($action === 'selfenrol') {

                $return = (new certification)->certification_self_enrolment($id,$USER->id, $selfenrol=1);
          
            }else{
                $return = (new certification)->certification_status_action($id, $action);
            }
       
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_certification');
            $return = false;
        }
        return $return;
    }

    public static function managecertificationStatus_instance_returns() {
        return new external_value(PARAM_RAW, 'return');
    }
    public static function certification_course_selector_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $includes = new external_value(
            PARAM_ALPHA,
            'What other contexts to fetch the frameworks from. (all, parents, self)',
            VALUE_DEFAULT,
            'parents'
        );
        // $limitfrom = new external_value(
        //  PARAM_INT,
        //  'limitfrom we are fetching the records from',
        //  VALUE_DEFAULT,
        //  0
        // );
        // $limitnum = new external_value(
        //  PARAM_INT,
        //  'Number of records to fetch',
        //  VALUE_DEFAULT,
        //  25
        // );
        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'includes' => $includes,
            // 'limitfrom' => $limitfrom,
            // 'limitnum' => $limitnum,
        ));
    }

    public static function certification_course_selector($query, $context, $includes = 'parents' /*, $limitfrom = 0, $limitnum = 25*/) {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::certification_course_selector_parameters(), array(
            'query' => $query,
            'context' => $context,
            'includes' => $includes,
            // 'limitfrom' => $limitfrom,
            // 'limitnum' => $limitnum,
        ));
        $query = $params['query'];
        $includes = $params['includes'];
        $context = self::get_context_from_params($params['context']);
        // $limitfrom = $params['limitfrom'];
        // $limitnum = $params['limitnum'];

        self::validate_context($context);
        $courses = array();
        if ($query) {
            $queryparams = array();
            $concatsql = '';
            if ((has_capability('local/certification:managecertification', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/certification:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
                $concatsql .= " AND open_costcenterid = :costcenterid";
                $queryparams['costcenterid'] = $USER->open_costcenterid;
                if ((has_capability('local/certification:manage_owndepartments', context_system::instance())|| has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                     $concatsql .= " AND open_departmentid = :department";
                     $queryparams['department'] = $USER->open_departmentid;
                 }
           }

            $cousresql = "SELECT c.id, c.fullname
                           FROM {course} AS c
                           JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                          WHERE c.visible = 1 AND CONCAT(',',c.open_identifiedas,',') LIKE '%,6,%' AND c.fullname LIKE '%{$query}%' AND c.id <> " . SITEID . " $concatsql";//FIND_IN_SET(6,c.open_identifiedas)
            $courses = $DB->get_records_sql($cousresql, $queryparams);
        }

        return array('courses' => $courses);
    }
    public static function certification_course_selector_returns() {
        return new external_single_structure(array(
            'courses' => new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'ID of the course'),
                    'fullname' => new external_value(PARAM_RAW, 'course fullname'),
                ))
            ),
        ));
    }
    public static function delete_session_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'certificationid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_session_instance($action, $id, $confirm) {
        global $DB,$USER;
        try {
            if ($confirm) {
                $certificationid=$DB->get_field('local_certification_sessions','certificationid',array('id'=>$id));
                
                //$DB->execute("UPDATE {local_certification_users}
                //             SET attended_sessions=(attended_sessions-1)
                //             WHERE certificationid=$certificationid and userid in (SELECT userid
                //             FROM {local_certificatn_attendance} WHERE sessionid = $id)");
                //             
                $certification_completiondata =$DB->get_record_sql("SELECT id,sessionids 
                                        FROM {local_certificatn_completion}
                                        WHERE certificationid = $certificationid");

                if($certification_completiondata->sessionids!=null){
                    $certification_sessionids=explode(',',$certification_completiondata->sessionids);
                    $array_diff=array_diff($certification_sessionids, array($id));
                    if(!empty($array_diff)){
                        $certification_completiondata->sessionids = implode(',',$array_diff);
                    }else{
                        $certification_completiondata->sessionids="NULL";
                    }
                    //$DB->execute('UPDATE {local_certificatn_completion}
                    //             SET sessionids = REPLACE(sessionids,'.$certification_sessionids.')
                    //             WHERE id = ' .$certification_completiondata->id. '');
                    $DB->update_record('local_certificatn_completion', $certification_completiondata);
                    $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $certification_completiondata->id
                    );
                
                    $event = \local_certification\event\certification_completions_settings_updated::create($params);
                    $event->add_record_snapshot('local_certification', $certificationid);
                    $event->trigger();
                    
                   
                }                        
                
                
                $DB->delete_records('local_certificatn_attendance', array('sessionid' => $id));
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' =>$id
                );
                
                $event = \local_certification\event\certification_sessions_deleted::create($params);
                $event->add_record_snapshot('local_certification', $certificationid);
                $event->trigger();
                
                $DB->delete_records('local_certification_sessions', array('id' => $id));
                 
                $certification = new stdClass();
                $certification->id = $certificationid;
                $certification->totalsessions = $DB->count_records('local_certification_sessions', array('certificationid' => $certificationid));
                $certification->activesessions = $DB->count_records('local_certification_sessions', array('certificationid' => $certificationid,'attendance_status'=>1));
                $DB->update_record('local_certification', $certification);
                
                //$params = array(
                //    'context' => context_system::instance(),
                //    'objectid' => $certificationid
                //);
                //
                //$event = \local_certification\event\certification_updated::create($params);
                //$event->add_record_snapshot('local_certification',$certificationid);
                //$event->trigger();
                
                $certification_users=$DB->get_records_menu('local_certification_users',  array('certificationid' =>$certificationid), 'id', 'id, userid');
                
                foreach($certification_users as $certification_user){
                 
                    $attendedsessions = $DB->count_records('local_certificatn_attendance',
                    array('certificationid' => $certificationid,
                        'userid' => $certification_user, 'status' => 1));
   
                    $attendedsessions_hours=$DB->get_field_sql("SELECT ((sum(lcs.duration))/60) AS hours
                                                FROM {local_certification_sessions} as lcs
                                                WHERE  lcs.certificationid =$certificationid
                                                and lcs.id in(SELECT sessionid  FROM {local_certificatn_attendance} where certificationid=$certificationid and userid=$certification_user and status=1)");
 
                    if(empty($attendedsessions_hours)){
                        $attendedsessions_hours=0;
                    }
        
                    $DB->execute('UPDATE {local_certification_users} SET attended_sessions = ' .
                        $attendedsessions . ',hours = ' .
                        $attendedsessions_hours . ', timemodified = ' . time() . ',
                        usermodified = ' . $USER->id . ' WHERE certificationid = ' .
                    $certificationid . ' AND userid = ' . $certification_user);
                }
                
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_certification');
            $return = false;
        }
        return $return;
    }

    public static function delete_session_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function delete_certificationevaluation_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'certificationid' => new external_value(PARAM_INT, 'Certification ID', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_certificationevaluation_instance($action, $id, $certificationid, $confirm) {
        global $DB,$CFG;
        try {
            if ($confirm) {
                 require_once($CFG->dirroot . '/local/evaluation/lib.php');
                // $DB->delete_records('local_evaluations', array('id' => $id));
                evaluation_delete_instance($id);
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_certification');
            $return = false;
        }
        return $return;
    }

    public static function delete_certificationevaluation_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function certification_form_option_selector_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $action = new external_value(
            PARAM_RAW,
            'Action for the certification form selector'
        );
        $options = new external_value(
            PARAM_RAW,
            'Action for the certification form selector'
        );
        // $limitfrom = new external_value(
        //  PARAM_INT,
        //  'limitfrom we are fetching the records from',
        //  VALUE_DEFAULT,
        //  0
        // );
        // $limitnum = new external_value(
        //  PARAM_INT,
        //  'Number of records to fetch',
        //  VALUE_DEFAULT,
        //  25
        // );
        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'action' => $action,
            'options' => $options,
            // 'limitfrom' => $limitfrom,
            // 'limitnum' => $limitnum,
        ));
    }

    public static function certification_form_option_selector($query, $context, $action, $options/*, $limitfrom = 0, $limitnum = 25*/) {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::certification_form_option_selector_parameters(), array(
            'query' => $query,
            'context' => $context,
            'action' => $action,
            'options' => $options
            // 'limitfrom' => $limitfrom,
            // 'limitnum' => $limitnum,
        ));
        $query = $params['query'];
        $action = $params['action'];
        $context = self::get_context_from_params($params['context']);
        $options = $params['options'];
        if (!empty($options)) {
            $formoptions = json_decode($options);
        }

        // $limitfrom = $params['limitfrom'];
        // $limitnum = $params['limitnum'];
        // 

        self::validate_context($context);
        if ($query && $action) {
            $querieslib = new \local_certification\local\querylib();
            $return = array();

            switch($action) {
                case 'certification_trainer_selector':
                    $parentid = $formoptions->parnetid;
                    $return = $querieslib->get_user_department_trainerslist(true,array($parentid), array(), $query);
                break;
                case 'certification_institute_selector':
                    $service = array();
                    $service['certificationid'] = $formoptions->id;
                    $service['query'] = $query;
                    $return = $querieslib->get_certification_institutes($formoptions->institute_type, $service);
                break;
                case 'certification_costcenter_selector':
                // OL-1042 Add Target Audience to Certifications//
                    if($formoptions->id>0&&!isset($formoptions->parnetid)){
                        $parentid=$DB->get_field('local_certification','costcenter', array('id'=>$formoptions->id));
                    }else{
                         $parentid = $formoptions->parnetid;
                    }
                // OL-1042 Add Target Audience to Certifications//  
                    $depth = $formoptions->depth;
                    $params = array();
                    $costcntersql = "SELECT id, fullname
                                        FROM {local_costcenter}
                                        WHERE visible = 1 ";
                    if ($parentid >= 0) {
                        $costcntersql .= " AND parentid = :parentid ";
                        $params['parentid'] = $parentid;
                    }
                    if ($depth > 0) {
                        $costcntersql .= " AND depth = :depth ";
                        $params['depth'] = $depth;
                    }
                    if (!empty($query)) {
                        $costcntersql .= " AND fullname LIKE :query ";
                        $params['query'] = '%' . $query . '%';
                    }
                    if($depth == 1){
                        $concat_array = array();
                    }else{
                        $concat_array = array(-1 => array('id' => -1,'fullname' => 'All'));
                    }
                    $return = $concat_array + $DB->get_records_sql($costcntersql, $params);
                    // $return=(object)((array)$return+array('0'=>(object)array('id'=>-1,'fullname'=>get_string('all')) ));
                break;
		case 'certification_subdepartment_selector':
                    if($formoptions->departments_selected){
                        $subdept_sql = "SELECT id, fullname
                                        FROM {local_costcenter}
                                        WHERE visible = 1 AND parentid IN (:departments_selected)";
                        $params['departments_selected'] = is_array($formoptions->departments_selected) ? implode(',', $formoptions->departments_selected): $formoptions->departments_selected;
                        $depth = $formoptions->depth;
                        if ($depth > 0) {
                            $subdept_sql .= " AND depth = :depth ";
                            $params['depth'] = $depth;
                        }
                        if (!empty($query)) {
                            $subdept_sql .= " AND fullname LIKE :query ";
                            $params['query'] = '%' . $query . '%';
                        }
                        $return = array(-1 => array('id' => -1,'fullname' => 'All'))+$DB->get_records_sql($subdept_sql, $params);
                    }
                break;
                case 'certificationsession_trainer_selector':
                    $certificationtrainerssql = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM {user} AS u JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                        WHERE ct.certificationid = :certificationid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                    $params = array();
                    $params['certificationid'] = $formoptions->certificationid;
                    if (!empty($query)) {
                        $certificationtrainerssql .= " AND CONCAT(u.firstname, ' ', u.lastname) LIKE :query ";
                        $params['query'] = '%' . $query . '%';
                    }
                    $return = $DB->get_records_sql($certificationtrainerssql, $params);
                break;
                case 'certification_completions_sessions_selector':
                    $sessions_sql = "SELECT id, name as fullname
                                        FROM {local_certification_sessions}
                                        WHERE certificationid = $formoptions->certificationid";
                    $return = $DB->get_records_sql($sessions_sql);

        
                break;
                case 'certification_completions_courses_selector':
                    $courses_sql = "SELECT c.id,c.fullname FROM {course} as c JOIN {local_certification_courses} as lcc on lcc.courseid=c.id where lcc.certificationid=$formoptions->certificationid";
                    $return = $DB->get_records_sql($courses_sql);
        
                break;
            }
            return json_encode($return);
        }
    }
    public static function certification_form_option_selector_returns() {
        return new external_value(PARAM_RAW, 'data');
    }
    public static function certification_session_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function certification_session_instance($id, $contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $certification = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new \local_certification\form\session_form(null, array('id' => $data['id'],
            'ctid' => $data['certificationid'], 'form_status' => $form_status), 'post', '', null,
             true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $sessionid = (new certification)->manage_certification_sessions($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcertification', 'local_certification');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function certification_session_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function certification_completion_settings_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function certification_completion_settings_instance($id, $contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $certification = new stdClass();
        //print_object($data);
        // The last param is the ajax submitted data.
        $mform = new \local_certification\form\certification_completion_form(null, array('id' => $data['id'],
            'ctid' => $data['certificationid'], 'form_status' => $form_status), 'post', '', null,
             true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $certification_completionid = (new certification)->manage_certification_completions($validateddata);
            if ($certification_completionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcertification', 'local_certification');
        }
        $return = array(
            'id' => $certification_completionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function certification_completion_settings_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function certification_course_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function certification_course_instance($id, $contextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $serialiseddata = json_decode($jsonformdata);
        $data = array();
        parse_str($serialiseddata, $data);

        $warnings = array();
        $certification = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new certificationcourse_form(null, array('ctid' => $data['certificationid'],
            'form_status' => $form_status), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $sessionid = (new certification)->manage_certification_courses($validateddata);
            if ($sessionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingcertification', 'local_certification');
        }
        $return = array(
            'id' => $sessionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function certification_course_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function delete_certificationcourse_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'certificationid' => new external_value(PARAM_INT, 'Certification ID', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_certificationcourse_instance($action, $id, $certificationid, $confirm) {
        global $DB;
        try {
            if ($confirm) {

                $course = $DB->get_field('local_certification_courses', 'courseid', array('certificationid' => $certificationid, 'id' => $id));

                $certification_completiondata =$DB->get_record_sql("SELECT id,courseids 
                                        FROM {local_certificatn_completion}
                                        WHERE certificationid = $certificationid");

                if($certification_completiondata->courseids!=null){

                    $certification_courseids=explode(',',$certification_completiondata->courseids);
              
                    $array_diff=array_diff($certification_courseids, array($course));

                    if(!empty($array_diff)){
                        $certification_completiondata->courseids = implode(',',$array_diff);
                    }else{
                        $certification_completiondata->courseids="NULL";
                    }
                
                    //$DB->execute('UPDATE {local_certificatn_completion} SET courseids = ' .
                    //    $certification_courseids . ' WHERE id = ' .
                    //$certification_completiondata->id. '');                        
                    $DB->update_record('local_certificatn_completion', $certification_completiondata);
                    $params = array(
                        'context' => context_system::instance(),
                        'objectid' => $certification_completiondata->id
                    );
                
                    $event = \local_certification\event\certification_completions_settings_updated::create($params);
                    $event->add_record_snapshot('local_certification', $certificationid);
                    $event->trigger();
                    
                }        

         
                $certificationtrainers = $DB->get_records_menu('local_certification_trainers',
                    array('certificationid' => $certificationid), 'trainerid', 'id, trainerid');
                if (!empty($certificationtrainers)) {
                    foreach ($certificationtrainers as $certificationtrainer) {
                        $unenrolcertificationtrainer = (new certification)->manage_certification_course_enrolments($course, $certificationtrainer,
                            'editingteacher', 'unenrol');
                    }
                }
                $certificationusers = $DB->get_records_menu('local_certification_users',
                    array('certificationid' => $certificationid), 'userid', 'id, userid');
                if (!empty($certificationusers)) {
                    foreach ($certificationusers as $certificationuser) {
                        $unenrolcertificationuser = (new certification)->manage_certification_course_enrolments($course, $certificationuser,
                            'employee', 'unenrol');
                    }
                }
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' =>$id
                );
                
                $event = \local_certification\event\certification_courses_deleted::create($params);
                $event->add_record_snapshot('local_certification', $certificationid);
                $event->trigger();
                $DB->delete_records('local_certification_courses', array('id' => $id));
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_certification');
            $return = false;
        }
        return $return;
    }

    public static function delete_certificationcourse_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }


/*sree*/
public static function submit_instituteform_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),

            )
        );
    }

    /**
     * form submission of institute name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return institute form submits
     */
    public static function submit_catform_form($contextid, $jsonformdata){
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/certification/lib.php');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_instituteform_form_parameters(),
                                    ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);
        // $context = $params['contextid'];
        $context = context_system::instance();
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        // throw new moodle_exception('Error in creation');
        // die;
        $data = array();

        parse_str($serialiseddata, $data);
        $warnings = array();
         $mform = new local_certification\form\catform(null, array(), 'post', '', null, true, $data);
        $category  = new local_certification\event\category();
        $valdata = $mform->get_data();

        if($valdata){
            if($valdata->id>0){

                $institutes->category_update_instance($valdata);
            } else{

                $institutes->category_insert_instance($valdata);
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('Error in creation');
        }
    }


    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_catform_form_returns() {
        return new external_value(PARAM_INT, 'category id');
    }

// Get Certificates for Mobile By Srilekha
     public static function get_certification_courses_parameters() {
        return new external_function_parameters(
             array('userid' => new external_value(PARAM_INT, 'UserID'),
                    'status' => new external_value(PARAM_INT, 'Status'),
                    'searchterm' => new external_value(PARAM_RAW, 'Search')
                )
        );
    }
     public static function get_certification_courses($userid,$status,$searchterm = "") {
        global $DB,$USER;
        $certificateinfo = array();
        $session_list = array();
        $sql = "SELECT *
                FROM {local_certification_users} as lbu
                JOIN {local_certification} as lb ON lbu.certificationid = lb.id";
        // if($status == 10){
        //     $sql .= " AND lb.status IN(1, 3, 4) WHERE lbu.userid=".$USER->id;
        // }
        if($status == 1){
            $sql .= " AND lb.status = 1 WHERE lbu.userid=".$USER->id;
        }
        if($status == 8){
            $sql .= " AND lb.status = 4 WHERE lbu.userid=".$USER->id;
        }
        if($searchterm !=""){
            $sql.=" AND lb.name LIKE '%".$searchterm."%'";
        }
        $allcertificates = $DB->get_records_sql($sql);
        $data = array();
        foreach ($allcertificates as $certificate) {
            $certificatecourse = array();
            $certificateinfo['certificationid'] = $certificate->id;
            $certificateinfo['certificationname'] = $certificate->name;
            $certificateinfo['startdate'] = date('d  M Y',$certificate->startdate);
            $certificateinfo['enddate'] = date('d  M Y',$certificate->enddate);
            $certificatecourse = $DB->get_records_sql("SELECT c.id,c.fullname FROM {course} as c
                JOIN {local_certification_courses} as lbc ON lbc.courseid = c.id WHERE lbc.certificationid=".$certificate->id);
            foreach($classroomcourse as $key => $course){
                $certificatecourse[$key]['id'] = $course->id;
                $certificatecourse[$key]['fullname'] = $course->fullname;
            }
            $certificateinfo['courseslist'] = $certificatecourse;
            $primarytrainer = $DB->get_records_sql("SELECT concat(u.firstname, u.lastname)  as username FROM {local_certification_trainers} as lbt
                JOIN {user} as u ON lbt.trainerid = u.id WHERE lbt.certificationid=".$certificate->id);
            $certificateinfo['primarytrainer'] = $primarytrainer;
            $certificateinfo['count'] = COUNT($allcertificates);
            $data[] = $certificateinfo;
        }
         return array('mycertificates' => $data);
    }
    public static function get_certification_courses_returns() {
        return new external_single_structure(
            array(
                'mycertificates' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'certificationid' => new external_value(PARAM_RAW, 'Batchid'),
                            'certificationname' => new external_value(PARAM_RAW, 'Batchname'),
                            'startdate' => new external_value(PARAM_RAW, 'Batch Start Date'),
                            'enddate' => new external_value(PARAM_RAW, 'Batch End Date'),
                            'courseslist' => new external_multiple_structure(
                            new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Course Id'),
                            'fullname' => new external_value(PARAM_RAW, 'Course name'),
                        )
                    )
                ),
            // 'primarytrainer' => new external_value(PARAM_RAW, 'Fullname of ILT'),
            'count' => new external_value(PARAM_RAW, 'Count of batches'),

        )
        )
    ),
    )

    );
    }
    public static function get_certification_sessions_parameters() {
        return new external_function_parameters(
             array('userid' => new external_value(PARAM_INT, 'UserID'),
                    'status' => new external_value(PARAM_INT, 'Status'),
                    'classroomid' => new external_value(PARAM_INT, 'Classroomid')
                )
        );
    }
     public static function get_certification_sessions($userid,$status,$classroomid) {
        global $DB,$USER;
        $data = array();
            $classroom = $DB->get_record_sql("SELECT lbs.name FROM {local_certification} as lbs WHERE lbs.id=".$classroomid);
            // $certification_completiondata = $DB->get_record('local_certificatn_completion', array('certificationid' => $classroomid));

            // $sessionssql="SELECT id,name FROM {local_certification_sessions}
            //                                 WHERE certificationid = $classroomid ";
            // if(!empty($certification_completiondata)&&$certification_completiondata->sessiontracking=="OR"&&            $certification_completiondata->sessionids!=null){

            //      $sessionssql.=" AND id in ($certification_completiondata->sessionids)";

            // }
            // $sessionss = $DB->get_records_sql_menu($sessionssql);
            // print_r($sessionss);exit;
            $sessions = $DB->get_records_sql("SELECT * FROM {local_certification_sessions} as lbs WHERE lbs.certificationid=".$classroomid);
            $sessiondata =  array();
            $sessioninfo = array();
            foreach($sessions as $key => $session){
                $sessiondata[$key]['sessionname'] = $session->name;
                $sessiondata[$key]['sessiontime'] = \local_costcenter\lib::get_userdate('d  M Y',$session->timestart).' - '. \local_costcenter\lib::get_userdate('d  M Y',$session->timefinish);
                if($session->onlinesession == 0) {
                    $sessiondata[$key]['sessiontype'] = 'Classroom';
                }
                else{
                    $sessiondata[$key]['sessiontype'] = 'Webex';
                }
                $sessionroom = $DB->get_record_sql("SELECT CONCAT(name, building, address) as roominfo FROM {local_location_room} WHERE id=".$session->roomid);
                if($sessionroom){
                    $sessiondata[$key]['sessionroom'] = $sessionroom->roominfo;
                }
                else{
                     $sessiondata[$key]['sessionroom'] = 'NA';
                }
            }
            $classroominfo['sessionslist'] = $sessiondata;
            $classroominfo['classroomname'] = $classroom->name;
            $classroominfo['count'] = COUNT($sessions);
            $data[] = $classroominfo;
         return array('mysessions' => $data);
    }
     public static function get_certification_sessions_returns() {
        return new external_single_structure(
            array(
               'mysessions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                        'classroomname' => new external_value(PARAM_RAW, 'classroomname'),
                        'sessionslist' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                'sessionname' => new external_value(PARAM_RAW, 'Session name'),
                                'sessiontime' => new external_value(PARAM_RAW, 'Session start and end date.'),
                                'sessiontype' => new external_value(PARAM_RAW, 'Session type'),
                                'sessionroom' => new external_value(PARAM_RAW, 'Session location'),
                                )
                            )
                        ),
                        'count' => new external_value(PARAM_RAW, 'Count of sessions'),
                        )
                    )
                ),
            )
        );
    }
    public static function data_for_certifications_parameters(){
        $filter = new external_value(PARAM_TEXT, 'Filter text');
        $filter_text = new external_value(PARAM_TEXT, 'Filter name',VALUE_OPTIONAL);
        $filter_offset = new external_value(PARAM_INT, 'Offset value',VALUE_OPTIONAL);
        $filter_limit = new external_value(PARAM_INT, 'Limit value',VALUE_OPTIONAL);
        $params = array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        );
        return new external_function_parameters($params);
    }
    public static function data_for_certifications($filter, $filter_text='', $filter_offset = 0, $filter_limit = 0){
        global $PAGE;

        $params = self::validate_parameters(self::data_for_certifications_parameters(), array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        ));

        $PAGE->set_context(context_system::instance());
        $renderable = new \local_certification\output\certification_courses($params['filter'],$params['filter_text'], $params['filter_offset'], $params['filter_limit']);
        $output = $PAGE->get_renderer('local_certification');

        $data= $renderable->export_for_template($output);
        return $data;
    }
    public static function data_for_certifications_returns(){
        return new external_single_structure(array (
            'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),           
            'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),  
            'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'), 
            'certification_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'), 
            'enableslider'=>  new external_value(PARAM_INT, 'Flag for enable the slider.'),
            'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
            'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
            'functionname' => new external_value(PARAM_TEXT, 'Function name'),
            'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
            'certificationtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
            'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, true),
            'moduledetails' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'certificateId' => new external_value(PARAM_INT, 'Certification id'),
                        'certificationSummary' => new external_value(PARAM_RAW, 'Certification Summary'),
                        'certificationFullname' => new external_value(PARAM_RAW, 'Certification Fullname'),
                        'displayCertificationFullname' => new external_value(PARAM_RAW, 'Display Certification Fullname'),
                        'displayCertificationFullname' => new external_value(PARAM_RAW, 'Display Certification Fullname'),
                        'startdate' => new external_value(PARAM_RAW, 'Certification Start Date'),
                        'enddate' => new external_value(PARAM_RAW, 'Certification End Date'),
                        'rating_element' => new external_value(PARAM_RAW, 'Certification Rating element'),
                        'certificateUrl' => new external_value(PARAM_RAW, 'Certification url'),
                        'certificateProgress' => new external_value(PARAM_TEXT, 'Certification Progress '),
                        'index' => new external_value(PARAM_INT, 'Index of Card'),
                    )
                )
            ),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
            'index' => new external_value(PARAM_INT, 'number of courses count'),
            'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
            'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
            'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
            'viewMoreCard' => new external_value(PARAM_BOOL, 'More info card to display'),

        ));
    }
    public static function data_for_certifications_paginated_parameters(){
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function data_for_certifications_paginated($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata){
        global $DB, $PAGE;
        require_login();
        $PAGE->set_context($contextid);

        $decodedoptions = (array)json_decode($options);
        $decodedfilter = (array)json_decode($filterdata);
        $filter = $decodedoptions['filter'];
        $PAGE->set_url('/local/certification/userdashboard.php', array('tab' => $filter));
        $filter_text = isset($decodedfilter['search_query']) ? $decodedfilter['search_query'] : '';
        $filter_offset = $offset;
        $filter_limit = $limit;

        $PAGE->set_context(context_system::instance());
        $renderable = new \local_certification\output\certification_courses($filter, $filter_text, $filter_offset, $filter_limit);
        $output = $PAGE->get_renderer('local_program');

        $data = $renderable->export_for_template($output);
        $totalcount = $renderable->coursesViewCount;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => array($data),
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }
    public static function data_for_certifications_paginated_returns(){
        return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        'records' => new external_multiple_structure(
                new external_single_structure(array (
                    'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),           
                    'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),  
                    'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'), 
                    'certification_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'), 
                    // 'enableslider'=>  new external_value(PARAM_INT, 'Flag for enable the slider.'),
                    'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
                    'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
                    'functionname' => new external_value(PARAM_TEXT, 'Function name'),
                    'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
                    'certificationtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
                    'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, false),
                    'moduledetails' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'certificateId' => new external_value(PARAM_INT, 'Certification id'),
                                'certificationSummary' => new external_value(PARAM_RAW, 'Certification Summary'),
                                'certificationFullname' => new external_value(PARAM_RAW, 'Certification Fullname'),
                                'displayCertificationFullname' => new external_value(PARAM_RAW, 'Display Certification Fullname'),
                                'displayCertificationFullname' => new external_value(PARAM_RAW, 'Display Certification Fullname'),
                                'startdate' => new external_value(PARAM_RAW, 'Certification Start Date'),
                                'enddate' => new external_value(PARAM_RAW, 'Certification End Date'),
                                'rating_element' => new external_value(PARAM_RAW, 'Certification Rating element'),
                                'certificateUrl' => new external_value(PARAM_RAW, 'Certification url'),
                                'certificateProgress' => new external_value(PARAM_TEXT, 'Certification Progress '),
                            )
                        )
                    ),
                    'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                    'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                    'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
                    'index' => new external_value(PARAM_INT, 'number of courses count'),
                    'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
                    'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
                    // 'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),

                )
            )
        )
    ]);
    }
    public static function delete_certificationuser_instance_parameters(){
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context for the service'),
            'certificationid' => new external_value(PARAM_INT, 'Certification id for the service'),
            'userid' => new external_value(PARAM_INT, 'Userid For the service')
        ]);
    }
    public static function delete_certificationuser_instance($contextid, $certificationid, $userid){
        $params = self::validate_parameters(self::delete_certificationuser_instance_parameters(), array(
            'contextid' => $contextid,
            'certificationid' => $certificationid,
            'userid' => $userid
        ));
        $certificationclass = new \local_certification\certification();
        $certificationclass->certification_remove_assignusers($certificationid, [$userid], 1);
        // exit;
        return true;
    }
    public static function delete_certificationuser_instance_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
}