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
 * @package BizLMS
 * @subpackage local_onlinetest
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
include_once($CFG->dirroot . '/mod/quiz/lib.php');
// use \local_onlinetests\notificationemails as onlinetestsnotifications_emails;

/**
 * creates a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $onlinetest the object
 * @return int onlinetestid
 */
function onlinetests_add_instance($onlinetest,$examid) {
    global $DB, $USER;
    $context = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $quiz = new stdClass();
    $quiz->name = $onlinetest->name;
    if (!empty($onlinetest->introeditor['text']))
    $quiz->introeditor['text'] = $onlinetest->introeditor['text'];
    else
    $quiz->introeditor['text'] = $onlinetest->name;
    $quiz->introeditor['format'] = $onlinetest->introeditor['format'];
    $quiz->introeditor['itemid'] = 1;
    if ($onlinetest->timeopen)
    $quiz->timeopen = $onlinetest->timeopen;
    else
    $quiz->timeopen = 0;
    if ($onlinetest->timeclose)
    $quiz->timeclose = $onlinetest->timeclose;
    else
    $quiz->timeclose = 0;
    $quiz->timelimit = $onlinetest->timelimit;
    $quiz->overduehandling = 'autosubmit';
    $quiz->graceperiod = 0;
    $quiz->gradecat = 2;
    $quiz->gradepass = $onlinetest->gradepass;
    $quiz->grade = $onlinetest->grade;
    $quiz->attempts = $onlinetest->attempts;
    $quiz->grademethod = 1;
    $quiz->questionsperpage = 1;
    $quiz->navmethod = 'free';
    $quiz->shuffleanswers = 1;
    $quiz->preferredbehaviour = 'deferredfeedback';
    $quiz->canredoquestions = 0;
    $quiz->attemptonlast = 0;
    $quiz->attemptimmediately = 1;
    $quiz->correctnessimmediately = 1;
    $quiz->marksimmediately = 1;
    $quiz->specificfeedbackimmediately = 1;
    $quiz->generalfeedbackimmediately = 1;
    $quiz->rightanswerimmediately = 1;
    $quiz->overallfeedbackimmediately = 1;
    $quiz->attemptopen = 1;
    $quiz->correctnessopen = 1;
    $quiz->marksopen = 1;
    $quiz->specificfeedbackopen = 1;
    $quiz->generalfeedbackopen = 1;
    $quiz->rightansweropen = 1;
    $quiz->overallfeedbackopen = 1;
    $quiz->attemptclosed = 1;
    $quiz->correctnessclosed = 1;
    $quiz->marksclosed = 1;
    $quiz->specificfeedbackclosed = 1;
    $quiz->generalfeedbackclosed = 1;
    $quiz->rightanswerclosed = 1;
    $quiz->overallfeedbackclosed = 1;
    $quiz->showuserpicture = 0;
    $quiz->decimalpoints = 2;
    $quiz->questiondecimalpoints = -1;
    $quiz->showblocks = 0;
    $quiz->quizpassword = '';
    $quiz->subnet = '';
    $quiz->delay1 = 0;
    $quiz->delay2 = 0;
    $quiz->browsersecurity = '-';
    $quiz->boundary_repeats = 1;
    $feedbacktext =array();
    $feedbacktext[0]=array('text' => '','format' => 1,'itemid' => 45741940);
    $feedbacktext[1]= array('text' => '','format' => 1,'itemid' => 139878390);
    $quiz->feedbacktext = $feedbacktext;
    $feedbackboundaries=array('0'=>'');
    $quiz->feedbackboundaries = $feedbackboundaries;;

    $quiz->visible = $onlinetest->visible;
    $quiz->cmidnumber = '';
    $quiz->groupmode = 0;
    $quiz->groupingid = 0;
    $quiz->availabilityconditionsjson = '';//{"op":"&","c":$quiz->,"showc":$quiz->}
    $quiz->completionunlocked = 1;
    $quiz->completion = 1;
    $quiz->completionpass = 0;
    $quiz->completionattemptsexhausted = 0;
    $quiz->completionexpected = 0;
    $quiz->tags = '';
    $quiz->course = $examid->id;
    $quiz->coursemodule = 0;
    $quiz->section = 0;
    $quiz->module = $DB->get_field('modules','id',array('name'=>'quiz'));
    $quiz->modulename = 'quiz';
    $quiz->instance = 0;
    $quiz->add = 'quiz';
    $quiz->update = 0;
    $quiz->return = 0;
    $quiz->sr = 0;
    $quiz->competency_rule = 0;
    $quiz->submitbutton = 'Save and display';
    //$course = $DB->get_record('course', array('id' => 1));
    //$quizid = add_moduleinfo($quiz,$course);

    $quizid = add_moduleinfo($quiz,$examid);


    $assessment = new stdClass();
    $assessment->quizid = $quizid->id;
    $assessment->name = $onlinetest->name;
    $assessment->costcenterid = $onlinetest->costcenterid;
    if (is_array($onlinetest->departmentid))
    $assessment->departmentid = implode(',',$onlinetest->departmentid);
    else
    $assessment->departmentid = $onlinetest->departmentid;
    if ($onlinetest->timeopen)
    $assessment->timeopen = $onlinetest->timeopen;
    else
    $assessment->timeopen = 0;
    if ($onlinetest->timeclose)
    $assessment->timeclose = $onlinetest->timeclose;
    else
    $assessment->timeclose = 0;
    $assessment->timemodified = time();
    $assessment->usermodified = $USER->id;
    $assessment->visible = $onlinetest->visible;
    $assessment->open_points = $onlinetest->open_points;
    $assessment->certificateid = $onlinetest->certificateid;
    $assessment->courseid = $examid->id;

    local_costcenter_get_costcenter_path($onlinetest);

    $assessment->open_path=$onlinetest->open_path;

    $assessmentid = $DB->insert_record('local_onlinetests', $assessment);

    // Trigger onlinetest created event.
    $assessment->id = $assessmentid;
    $cm = get_coursemodule_from_instance('quiz', $quizid->id, 0, false, MUST_EXIST);
    $assessment->moduleid = $cm->id;

    onlinetest_set_events($assessment);

    $params = array(
        'context' => $context,
        'objectid' => $assessmentid
    );

    $event = \local_onlinetests\event\onlinetest_created::create($params);
    $event->add_record_snapshot('local_onlinetests', $assessment);
    $event->trigger();

    // Update onlinetest tags.
    // if (isset($onlinetest->tags)) {
    //     local_tags_tag::set_item_tags('local_onlinetests', 'onlinetests', $quizid->id, context_system::instance(), $onlinetest->tags, 0, $onlinetest->costcenterid, $onlinetest->departmentid);
    // }

    return $assessmentid;
}



/**
 * this will update a existing instance and return the id number
 *
 * @global object
 * @param object $onlinetest the object
 * @return int
 */
function onlinetests_update_instance($onlinetest) {
    global $DB, $USER;
    
    $record = $DB->get_record('local_onlinetests', array('id'=>$onlinetest->id), '*', MUST_EXIST);
    $quiz = $DB->get_record('quiz', array('id'=>$record->quizid), '*', MUST_EXIST);
    $quiz->name = $onlinetest->name;
    if (!empty($onlinetest->introeditor['text']))
    $quiz->introeditor['text'] = $onlinetest->introeditor['text'];
    else
    $quiz->introeditor['text'] = $onlinetest->name;
    $quiz->introeditor['format'] = $onlinetest->introeditor['format'];
    $quiz->introeditor['itemid'] = 1;
    $quiz->gradepass = $onlinetest->gradepass;
    $quiz->grade = $onlinetest->grade;
    $quiz->attempts = $onlinetest->attempts;
    $quiz->timelimit = $onlinetest->timelimit;
    if ($onlinetest->timeopen)
    $quiz->timeopen = $onlinetest->timeopen;
    else
    $quiz->timeopen = 0;
    if ($onlinetest->timeclose)
    $quiz->timeclose = $onlinetest->timeclose;
    else
    $quiz->timeclose = 0;
    $quiz->quizpassword = '';
    $quiz->submitbutton = 'Save and display';
    $course = $DB->get_record('course', array('id'=> $record->courseid));
    $cm = get_coursemodule_from_instance('quiz', $record->quizid, 0, false, MUST_EXIST);
    
    $quiz->coursemodule = $cm->id;
    $quiz->modulename = 'quiz';
    $quiz->course = $record->courseid;
    $quiz->groupingid = $cm->groupingid;
    $quiz->visible = $onlinetest->visible;
    $quiz->visibleoncoursepage = $onlinetest->visible;
    update_moduleinfo($cm, $quiz, $course, null);
    if ($quiz->grade != $onlinetest->grade) {
        quiz_set_grade($onlinetest->grade, $quiz);
        quiz_update_all_final_grades($quiz);
        quiz_update_grades($quiz, 0, true);
        local_onlinetest_update_grade_status($onlinetest);
    }
    
    
    $record->name = $onlinetest->name;
    $record->costcenterid = $onlinetest->costcenterid;
    if (is_array($onlinetest->departmentid))
    $record->departmentid = implode(',',$onlinetest->departmentid);
    else {
        if ($onlinetest->departmentid)
        $record->departmentid = $onlinetest->departmentid;
        else
        $record->departmentid = null;
    }
    $record->timemodified = time();
    $record->usermodified = $USER->id;
    $record->visible = $onlinetest->visible;
    if ($onlinetest->timeopen)
    $record->timeopen = $onlinetest->timeopen;
    else
    $record->timeopen = 0;
    if ($onlinetest->timeclose)
    $record->timeclose = $onlinetest->timeclose;
    else
    $record->timeclose = 0;
    
    $record->moduleid = $cm->id;
    $record->open_points = $onlinetest->open_points;

    if($onlinetest->map_certificate == 1){
        $record->certificateid = $onlinetest->certificateid;
    }else{
        $record->certificateid = null;
    }

    local_costcenter_get_costcenter_path($onlinetest);

    $record->open_path=$onlinetest->open_path;

    $DB->update_record('local_onlinetests', $record);
    $course->fullname = $onlinetest->name;
    $DB->update_record('course', $record);

    // Update onlinetest tags.
    // if (isset($onlinetest->tags)) {
    //     local_tags_tag::set_item_tags('local_onlinetests', 'onlinetests', $record->quizid, context_system::instance(), $onlinetest->tags, 0, $onlinetest->costcenterid, $onlinetest->departmentid);
    // }
    
    // Trigger onlinetest updated event.
    onlinetest_set_events($record);
    $context = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $params = array(
        'context' => $context,
        'objectid' => $record->id
    );

    $event = \local_onlinetests\event\onlinetest_updated::create($params);
    $event->add_record_snapshot('local_onlinetests', $record);
    $event->trigger();
    return $record->id;
}

function local_onlinetest_update_grade_status($onlinetest){
    global $DB;
    $onlinetest_user_sql = "SELECT lou.id, lou.userid, lou.status, gg.finalgrade, gi.gradepass 
        FROM {local_onlinetest_users} AS lou 
        JOIN {local_onlinetests} AS lo ON lo.id = lou.onlinetestid 
        JOIN {grade_items} AS gi ON gi.iteminstance = lo.quizid AND itemmodule LIKE 'quiz' 
        JOIN {grade_grades} AS gg ON gg.itemid = gi.id
        WHERE lou.onlinetestid = :onlinetestid AND lou.status = :status "; 
    $onlinetest_users = $DB->get_records_sql($onlinetest_user_sql, array('onlinetestid' => $onlinetest->id, 'status' => 0));
    foreach($onlinetest_users AS $user){
        if($user->finalgrade >= $user->gradepass){
            unset($user->finalgrade);
            unset($user->gradepass);
            $user->status = 1;
            $DB->update_record('local_onlinetest_users', $user);
        }
    }
}

/**
 * This creates new events given as timeopen and closeopen by onlinetest.
 *
 * @global object
 * @param object $onlinetest
 * @return void
 */
function onlinetest_set_events($onlinetest) {
    global $DB, $CFG, $USER;
    // Include calendar/lib.php.
    require_once($CFG->dirroot.'/calendar/lib.php');

    // evaluation start calendar events.
    $eventid = $DB->get_field('event', 'id',
            array('modulename' => '0', 'instance' => 0, 'plugin'=> 'local_onlinetests', 'plugin_instance'=>$onlinetest->id, 'eventtype' => 'open', 'local_eventtype' => 'open'));

    if (isset($onlinetest->timeopen) && $onlinetest->timeopen > 0) {
        $event = new stdClass();
        $event->eventtype    = 'open';
        $event->type         = empty($onlinetest->timeclose) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
        $event->name         = $onlinetest->name;
        $event->description  = "<a href='$CFG->wwwroot/local/onlinetests/index.php'>$onlinetest->name</a>";
        $event->timestart    = $onlinetest->timeopen;
        $event->timesort     = $onlinetest->timeopen;
        $event->visible      = $onlinetest->visible;
        $event->timeduration = 0;
        $event->plugin_instance = $onlinetest->id;
        $event->plugin_itemid = $onlinetest->moduleid;
        $event->plugin = 'local_onlinetests';
        $event->local_eventtype    = 'open';
        $event->relateduserid    = $USER->id;
        if ($eventid) {
            // Calendar event exists so update it.
            $event->id = $eventid;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            // Event doesn't exist so create one.
            $event->courseid     = 0;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 0;
            $event->instance     = 0;
            $event->eventtype    = 'open';;
            calendar_event::create($event);
        }
    } else if ($eventid) {
        // Calendar event is on longer needed.
        $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }

    // evaluation close calendar events.
    $eventid = $DB->get_field('event', 'id',
            array('modulename' => '0', 'instance' => 0, 'plugin'=> 'local_onlinetests', 'plugin_instance'=>$onlinetest->id, 'eventtype' => 'close', 'local_eventtype' => 'close'));

    if (isset($onlinetest->timeclose) && $onlinetest->timeclose > 0) {
        $event = new stdClass();
        $event->type         = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype    = 'close';
        $event->name         = $onlinetest->name;
        $event->description  = "<a href='$CFG->wwwroot/local/onlinetests/index.php'>$onlinetest->name</a>";
        $event->timestart    = $onlinetest->timeclose;
        $event->timesort     = $onlinetest->timeclose;
        $event->visible      = $onlinetest->visible;
        $event->timeduration = 0;
        $event->plugin_instance = $onlinetest->id;
        $event->plugin_itemid = $onlinetest->moduleid;
        $event->plugin = 'local_onlinetests';
        $event->local_eventtype    = 'close';
        $event->relateduserid    = $USER->id;
        if ($eventid) {
            // Calendar event exists so update it.
            $event->id = $eventid;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            // Event doesn't exist so create one.
            $event->courseid     = 0;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 0;
            $event->instance     = 0;
            calendar_event::create($event);
        }
    } else if ($eventid) {
        // Calendar event is on longer needed.
        $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }
}
/**
 * this will return sql statement

 * @param $context int contexid of evaluation 
 * @return string
 */
function department_sql($context) {
    global $DB, $USER;

    $usercondition = (new \local_onlinetests\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path');

    $sql = "SELECT lc.id, lc.parentid
                  FROM {local_costcenter} lc
                  JOIN {user} u on (concat('/',u.open_path,'/') LIKE concat('%/',lc.id,'/%') or concat('/',u.open_path,'/') LIKE concat('%/',lc.parentid,'/%') ) AND lc.depth = 1
                WHERE u.id={$USER->id} $usercondition";

    $costcenter = $DB->get_record_sql($sql);


    return $sql;
}
/**
 * Serve the new evalaution form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_onlinetests_output_fragment_new_onlinetest_form($args) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/local/onlinetests/onlinetest_form.php');
    $args = (object) $args;
    $context = $args->context;
    $id = $args->testid;
    $o = '';
 
    $formdata = [];

    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        if(is_object($serialiseddata) && !empty($serialiseddata)){
        $serialiseddata = serialize($serialiseddata);
        }
        parse_str($serialiseddata, $formdata);
    }

    $params = array('id' => $id);
    // Used to set the courseid.
    $data = new stdclass();
    if ($id > 0) {
        $data = $DB->get_record('local_onlinetests', array('id'=>$id));
        $quiz = $DB->get_record('quiz', array('id'=>$data->quizid));
        $cm = get_coursemodule_from_instance('quiz', $data->quizid, 0, false, MUST_EXIST);
		$gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$data->quizid, 'itemmodule'=>'quiz', 'courseid'=>$data->courseid));
        $data->grade = round($gradeitem->grademax, 2);
        $data->gradepass = round($gradeitem->gradepass, 2);
        $data->attempts = $quiz->attempts;
        $data->timelimit = $quiz->timelimit;
        $data->introeditor['text'] = $quiz->intro;
        $data->introeditor['format'] = $quiz->introformat;

        if(!empty($data->certificateid)){
            $data->map_certificate = 1;
        }
        // Populate tags.
        // $data->tags = local_tags_tag::get_item_tags_array('local_onlinetests', 'onlinetests', $quiz->id);
    }else{
        $data->id=0;
    }

    if (is_object($data)) {

        $default_values = (array)$data;

        local_costcenter_set_costcenter_path($default_values);

    }
    $mform = new onlinetests_form(null, $default_values, 'post', '', null, true, $formdata);
    $mform->set_data($default_values);
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

function local_onlinetests_output_fragment_addquestions_or_enrol($args) {
    global $CFG, $DB, $OUTPUT;
    $args = (object) $args;
    $context = $args->context;
    $id = $args->id;
    $onlinetest = $DB->get_record('local_onlinetests', array('id'=>$id), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('quiz', $onlinetest->quizid, 0, false, MUST_EXIST);
    if (!$cm)
    print_error('No module found, error occured while processing');
    require_capability('local/onlinetests:manage', $context);
    $path = 'index.php';
    $iconimage=html_writer::empty_tag('img', array('src'=>$OUTPUT->image_url('i/checked'),'size'=>'15px'));
    $out = "<div class='success_icon'><span class='iconimage'>".$iconimage."</span><span>".get_string('createdsuccessfully', 'local_onlinetests')."</span></div>";
    $out .= "<table class = 'generaltable'>
    <tr><td>".get_string('doaddquestions', 'local_onlinetests')."</td><td><a href='".$CFG->wwwroot."/mod/quiz/edit.php?cmid=$cm->id' class='btn btn-primary'>".get_string('questions', 'local_onlinetests')."</a></td></tr>
    <tr><td>".get_string('doenrollusers', 'local_onlinetests')."</td><td><a href='".$CFG->wwwroot."/local/onlinetests/users_assign.php?id=$id' class='btn btn-primary'>".get_string('assignusers', 'local_onlinetests')."</a></td></tr>
    </table>
    ";
    $out .= "<div style='text-align:center;'><a href='$path' class='btn btn-primary'>".get_string('skip', 'local_evaluation')."</a></div>";
    return $out;
}


/**
* [available_enrolled_users description]
* @param  string  $type       [description]
* @param  integer $onlinetestid [description]
* @param  [type]  $params     [description]
* @param  integer $total      [description]
* @param  integer $offset    [description]
* @param  integer $perpage    [description]
* @param  integer $lastitem   [description]
* @return [type]              [description]
*/
function onlinetest_enrolled_users($type = null, $onlinetestid = 0, $params, $total=0, $offset=-1, $perpage=-1, $lastitem=0){

    global $DB, $USER;
    $context = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $onlinetest = $DB->get_record('local_onlinetests', array('id' => $onlinetestid), '*', MUST_EXIST);

    $condition = (new \local_onlinetests\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path');

 
    $params['suspended'] = 0;
    $params['deleted'] = 0;
 
    if($total==0){
         $sql = "SELECT u.id,concat(u.firstname,' ',u.lastname,' ','(',u.email,')') as fullname";
    }else{
        $sql = "SELECT count(u.id) as total";
    }
    $sql.=" FROM {user} AS u WHERE  u.id > 2 AND u.suspended = :suspended AND u.deleted = :deleted ";
    if($lastitem!=0){
       $sql.=" AND u.id > $lastitem";
    }

    if (!is_siteadmin()) {
        $sql .= $condition;
    }
    $sql .=" AND u.id <> $USER->id";
    if (!empty($params['email'])) {
         $sql.=" AND u.id IN ({$params['email']})";
    }
    if (!empty($params['uname'])) {
         $sql .=" AND u.id IN ({$params['uname']})";
    }
    if (!empty($params['username'])) {
         $sql .=" AND u.id IN ({$params['username']})";
    }
    if (!empty($params['organization'])) {
        $organizations = explode(',', $params['organization']);
        $orgsql = [];
        foreach($organizations AS $organisation){
            $orgsql[] = " concat('/',u.open_path,'/') LIKE :organisationparam_{$organisation}";
            $params["organisationparam_{$organisation}"] = '%/'.$organisation.'/%';
        }
        if(!empty($orgsql)){
            $sql .= " AND ( ".implode(' OR ', $orgsql)." ) ";
        }
    }
    if (!empty($params['department'])) {
        $departments = explode(',', $params['department']);
        $deptsql = [];
        foreach($departments AS $department){
            $deptsql[] = " concat('/',u.open_path,'/') LIKE :departmentparam_{$department}";
            $params["departmentparam_{$department}"] = '%/'.$department.'/%';
        }
        if(!empty($deptsql)){
            $sql .= " AND ( ".implode(' OR ', $deptsql)." ) ";
        }
    }

    if (!empty($params['subdepartment'])) {
        $subdepartments = explode(',', $params['subdepartment']);
        $subdeptsql = [];
        foreach($subdepartments AS $subdepartment){
            $subdeptsql[] = " concat('/',u.open_path,'/') LIKE :subdepartmentparam_{$subdepartment}";
            $params["subdepartmentparam_{$subdepartment}"] = '%/'.$subdepartment.'/%';
        }
        if(!empty($subdeptsql)){
            $sql .= " AND ( ".implode(' OR ', $subdeptsql)." ) ";
        }
    }
    if (!empty($params['department4level'])) {
        $subdepartments = explode(',', $params['department4level']);
        $subdeptsql = [];
        foreach($subdepartments AS $department4level){
            $subdeptsql[] = " concat('/',u.open_path,'/') LIKE :department4levelparam_{$department4level}";
            $params["department4levelparam_{$department4level}"] = '%/'.$department4level.'%';
        }
        if(!empty($subdeptsql)){
            $sql .= " AND ( ".implode(' OR ', $subdeptsql)." ) ";
        }
    }
    if (!empty($params['department5level'])) {
        $subdepartments = explode(',', $params['department5level']);
        $subdeptsql = [];
        foreach($subdepartments AS $department5level){
            $subdeptsql[] = " concat('/',u.open_path,'/') LIKE :department5levelparam_{$department5level}";
            $params["department5levelparam_{$department5level}"] = '%/'.$department5level.'/%';
        }
        if(!empty($subdeptsql)){
            $sql .= " AND ( ".implode(' OR ', $subdeptsql)." ) ";
        }
    }
    if (!empty($params['idnumber'])) {
         $sql .=" AND u.id IN ({$params['idnumber']})";
    }
    if (!empty($params['location'])) {

        $locations = explode(',',$params['location']);
        list($locationsql, $locationparams) = $DB->get_in_or_equal($locations, SQL_PARAMS_NAMED, 'location');
        $params = array_merge($params,$locationparams);            
        $sql .= " AND u.open_location {$locationsql} ";
    }

    if (!empty($params['hrmsrole'])) {

        $hrmsroles = explode(',',$params['hrmsrole']);
        list($hrmsrolesql, $hrmsroleparams) = $DB->get_in_or_equal($hrmsroles, SQL_PARAMS_NAMED, 'hrmsrole');
        $params = array_merge($params,$hrmsroleparams);            
        $sql .= " AND u.open_hrmsrole {$hrmsrolesql} ";
    }
    if (!empty($params['groups'])) {
         $group_list = $DB->get_records_sql_menu("select cm.id, cm.userid from {cohort_members} cm, {user} u where u.id = cm.userid AND u.deleted = 0 AND u.suspended = 0 AND cm.cohortid IN ({$params['groups']})");
         
         $groups_members = implode(',', $group_list);
         if (!empty($groups_members))
         $sql .=" AND u.id IN ({$groups_members})";
         else
         $sql .=" AND u.id =0";
    }

    if ($type=='add') {
        $sql .= " AND u.id NOT IN (SELECT lcu.userid as userid
                              FROM {local_onlinetest_users} AS lcu
                              WHERE lcu.onlinetestid = $onlinetestid)";
    }elseif ($type=='remove') {
        $sql .= " AND u.id IN (SELECT lcu.userid as userid
                              FROM {local_onlinetest_users} AS lcu
                              WHERE lcu.onlinetestid = $onlinetestid)";
    }

    $order = ' ORDER BY u.id ASC ';

    if($total==0){
        $availableusers = $DB->get_records_sql_menu($sql .$order,$params, $offset, $perpage);
    }else{
        $availableusers = $DB->count_records_sql($sql,$params);
    }
    return $availableusers;
}
/**
 * Onlinetests info of the user.
 *
 * @param int $userid user id
 * @return array contains enrolled online tests details
 */
function user_tests($userid, $tabstatus) {
    global $DB, $OUTPUT;
    $sql = "SELECT a.*, ou.timecreated, ou.timemodified as joinedate,a.courseid from {local_onlinetests} a, {local_onlinetest_users} ou where a.id = ou.onlinetestid AND ou.userid = ? AND a.visible = 1";
    $sql .= " ORDER BY ou.timecreated DESC";
    $onlinetests = $DB->get_records_sql($sql, [$userid]);
    $data = array();
    if ($onlinetests) {
        foreach($onlinetests as $record) {
            $row = array();
            $cm = get_coursemodule_from_instance('quiz', $record->quizid, 0, false, MUST_EXIST);
            $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$record->quizid, 'itemmodule'=>'quiz', 'courseid'=>$record->courseid));
            $sql="SELECT * FROM {quiz_attempts} where id = (SELECT max(id) id from {quiz_attempts} where userid = ? and quiz= ? )";
            $userattempt = $DB->get_record_sql($sql, [$userid, $record->quizid]);
            $attempts = ($userattempt->attempt) ? $userattempt->attempt : 0;
            $grademax = ($gradeitem->grademax) ? round($gradeitem->grademax): '-';
            $gradepass = ($gradeitem->gradepass) ? round($gradeitem->gradepass): '-';
            $userquizrecord = $DB->get_record_sql("SELECT * from {local_onlinetest_users} where onlinetestid = ? AND userid = ? ", [$record->id, $userid]);
            $enrolledon = \local_costcenter\lib::get_userdate("d/m/Y H:i", $userquizrecord->timecreated);
            $buttons = array();
            $time = time();
            if ($record->timeclose !=0 AND $time >= $record->timeclose)
            $buttons[] = '-';
            else
            $buttons[] = html_writer::link(new moodle_url('/mod/quiz/view.php', array('id' => $cm->id,'sesskey' => sesskey())), $OUTPUT->pix_icon('t/go', get_string('attemptnumber', 'quiz'), 'moodle', array('class' => 'iconsmall', 'title' => '')));
            if ($attempts)
            $buttons[] = html_writer::link(new moodle_url('/mod/quiz/review.php', array('attempt' => $userattempt->id,'sesskey' => sesskey())), $OUTPUT->pix_icon('i/preview', get_string('review', 'quiz'), 'moodle', array('class' => 'iconsmall', 'title' => '')));
            if ($gradeitem->id)
            $usergrade = $DB->get_record_sql("SELECT * from {grade_grades} where itemid = ? AND userid = ? ", [$gradeitem->id, $userid]);
            if ($usergrade) {
                $mygrade = round($usergrade->finalgrade, 2);
                if ($usergrade->finalgrade >= $gradepass) {
                    $completedon = \local_costcenter\lib::get_userdate("d/m/Y H:i", $usergrade->timemodified);
                    $status = 'Completed';
                    if ($tabstatus == 2) // incomplete
                    continue;
                } else {
                    $status = 'Incomplete';
                    $completedon = '-';
                    if ($tabstatus == 1) // complete
                    continue;
                }
                
            } else {
                if ($tabstatus == 1) // incomplete
                    continue;
                $mygrade = '-';
                $status = 'Pending';
                $completedon = '-';
                $attempts = 0;
            }
            $buttons = implode('',$buttons);
            $row[] = $record->name;
            $row[] = $grademax;
            $row[] = $gradepass;
            $row[] = $mygrade;
            $row[] = $attempts;
            $row[] = $enrolledon;
            $row[] = $completedon;
            $row[] = $status;
            $row[] = $buttons;
            $data[] = $row;
            
        }
    }
    return $data;
}
/**
 * [function to get user enrolled onlinetests]
 * @param  [INT] $userid [id of the user]
 * @return [INT]         [count of the onlinetests enrolled]
 */
function enrol_get_users_onlinetest_count($userid){
    global $DB;
    $onlinetest_sql = "SELECT count(id) FROM {local_onlinetest_users} WHERE userid = :userid";
    $onlinetest_count = $DB->count_records_sql($onlinetest_sql, array('userid' => $userid));
    return $onlinetest_count;
}

function get_enrolled_onlinetest_as_employee($userid){
    global $DB;
    $sql = "SELECT lot.* FROM {local_onlinetests} AS lot
        JOIN {local_onlinetest_users} AS lotu ON lotu.onlinetestid=lot.id
        WHERE  lotu.userid = ?";
    $employeeonlinetests = $DB->get_records_sql($sql, [$userid]);
    return $employeeonlinetests;
}
/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_onlinetests_leftmenunode(){
    $categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $onlinetestsnode = '';
    if(has_capability('local/onlinetests:view', $categorycontext) || is_siteadmin()){
        $onlinetestsnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_browseonlinetests', 'class'=>'pull-left user_nav_div browseonlinetests'));
        $onlinetests_url = new moodle_url('/local/onlinetests/index.php');

        if(has_capability('local/onlinetests:manage', $categorycontext)) {
            $onlinetests_label = get_string('left_menu_onlinetests','local_onlinetests');
        }else{
            $onlinetests_label = get_string('left_menu_myonlinetests','local_onlinetests');
        }
        $onlinetests = html_writer::link($onlinetests_url, '<i class="fa fa-desktop" aria-hidden="true"></i><span class="user_navigation_link_text">'.$onlinetests_label.'</span>',array('class'=>'user_navigation_link'));
        $onlinetestsnode .= $onlinetests;
        $onlinetestsnode .= html_writer::end_tag('li');
    }

    return array('12' => $onlinetestsnode);
}
function local_onlinetests_quicklink_node(){
    global $DB, $PAGE, $USER, $CFG, $OUTPUT;

    $orgid  = optional_param('orgid', 0, PARAM_INT);

    $categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();

    $costcenterpathconcatsql = (new \local_onlinetests\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='open_path',$orgid);


    if (is_siteadmin() || has_capability('local/onlinetests:view',$categorycontext)) {

            $sql = "SELECT count(lo.id) FROM {local_onlinetests} lo WHERE 1=1 ";

            if (is_siteadmin() && $orgid ==0) {
                $sql .= "";
            } else  {
                $sql .= $costcenterpathconcatsql;

            }

            $count_ot = $DB->count_records_sql($sql);
            $count_otactive = $DB->count_records_sql($sql." AND visible=1 ");
            $count_otinactive = $DB->count_records_sql($sql." AND visible=0 ");

        if($count_otactive==0 || $count_ot==0){
            $otpercentage = 0;
        }else{
            $otpercentage = round(($count_otactive/$count_ot)*100);
            $otpercentage = (int)$otpercentage;
        }
        //local onlinetests content
        // $id = 0; //default for /local/users/index.php
        $PAGE->requires->js_call_amd('local_onlinetests/newonlinetests', 'init', array('[data-action=createonlinetestsmodal]', $categorycontext->id, $id));
        // $local_onlinetests_content = $PAGE->requires->js_call_amd('local_onlinetests/newonlinetests', 'init', array('[data-action=createonlinetestsmodal]', $categorycontext->id, $id));
        // $local_onlinetests_content .= "<span class='anch_span'><i class='fa fa-desktop' aria-hidden='true'></i></span>";
        // $local_onlinetests_content .= "<div class='w-100 pull-left'>
        //                                     <div class='quick_navigation_detail'>
        //                                     <div class='span_str'>".get_string('manage_br_onlineexams', 'local_onlinetests')."</div>";
        //     $display_line = false;
        // if(has_capability('local/onlinetests:create', $categorycontext) || is_siteadmin()){
        //     $local_onlinetests_content .= "<span class='span_createlink'>
        //                                         <a href='javascript:void(0);' class='quick_nav_link goto_local_onlinetest' title='".get_string('create_onlinetest', 'local_onlinetests')."' data-action='createonlinetestsmodal'>".get_string('create')."</a>"; 
        //     $display_line = true; 
        // }
                    
        // if($display_line){
        //     $local_onlinetests_content .= " | ";
        // }
        // $local_onlinetests_content .="<a href='".$CFG->wwwroot."/local/onlinetests/index.php' class='viewlink' title= '".get_string('viewonlinetest', 'local_onlinetests')." '>".get_string('view')."</a>";
        // $local_onlinetests_content .="</span>";
        // $local_onlinetests_content .= "</div>
        //                                </div>";
        // $local_onlinetests_content .= '<div class="progress-chart-container">
        //                                <div class="progress-doughnut">
        //                                     <div class="progress-text has-percent">'.$otpercentage.'%</div>
        //                                     <div class="progress-indicator">
        //                                         <svg xmlns="http://www.w3.org/2000/svg">
        //                                             <g>
        //                                                 <title aria-hidden="true">'.$otpercentage.'</title>
        //                                                 <circle class="circle percent-'.$otpercentage.'" r="27.5" cx="35" cy="35"></circle>
        //                                             </g>
        //                                         </svg>
        //                                     </div>
        //                                 </div>
        //                             </div>';
        // $local_onlinetests_content .= '<div class="w-100 pull-left">
        //                                 <div class="progress w-75 mx-auto my-5">
        //                                     <div class="progress-bar" role="progressbar" style="width: '.$otpercentage.'%;" aria-valuenow="'.$otpercentage.'" aria-valuemin="0" aria-valuemax="100">'.$otpercentage.'%</div>
        //                                 </div>
        //                             </div>';
        // $local_onlinetests_content .= '<ul class="dashboard_count_list w-full pull-left p-15">
        //                             <li class="dashbaord_count_item"><span class="">
        //                                 <span class="d-block dashboard_count_string">Total</span><span class="dashboard_count_value">'.$count_ot.'</span></span>
        //                             </li>
        //                             <li class="dashbaord_count_item"><span class="">
        //                                 <span class="d-block dashboard_count_string">Active</span><span class="dashboard_count_value">'.$count_otactive.'</span></span>
        //                             </li>
        //                             <li class="dashbaord_count_item"><span class="">
        //                                 <span class="d-block dashboard_count_string">In Active</span><span class="dashboard_count_value">'.$count_otinactive.'</span></span>
        //                             </li>
        //                         </ul>';
        // $local_onlinetests = '<div class="quick_nav_list manage_onlineexams two_of_three_columns" >'.$local_onlinetests_content.'</div>';


        $local_onlinetest = array();
        $local_onlinetest['pluginname'] = 'onlineexams';
        $local_onlinetest['plugin_icon_class'] = 'fa fa-desktop';
        $local_onlinetest['node_header_string'] = get_string('manage_br_onlineexams', 'local_onlinetests');
        $local_onlinetest['create'] = (has_capability('local/onlinetests:create', $categorycontext) || is_siteadmin()) ? TRUE : FALSE;
        $local_onlinetest['create_element'] = html_writer::link('javascript:void(0)', get_string('create'), array('data-action' =>'createonlinetestsmodal', 'title' => get_string('create_onlinetest', 'local_onlinetests'), 'class' => 'quick_nav_link goto_local_onlinetest'));
        $local_onlinetest['viewlink_url'] = $CFG->wwwroot.'/local/onlinetests/index.php';
        $local_onlinetest['percentage'] = $otpercentage;
        $local_onlinetest['count_total'] = $count_ot;
        $local_onlinetest['count_active'] = $count_otactive;
        $local_onlinetest['inactive_string'] = get_string('inactive_string', 'block_quick_navigation');
        $local_onlinetest['displaystats'] = TRUE;
        $local_onlinetest['count_inactive'] = $count_otinactive;
        $local_onlinetest['view'] = TRUE;
        $local_onlinetest['space_count'] = 'two';
        $content = $OUTPUT->render_from_template('block_quick_navigation/quicklink_node', $local_onlinetest);
    }
    return array('5' => $content);
}
/**
 * process the onlinetest_mass_enroll
 * @param csv_import_reader $cir  an import reader created by caller
 * @param Object $onlinetest  a onlinetest record from table mdl_local_onlinetest
 * @param Object $context  course context instance
 * @param Object $data    data from a moodleform
 * @return string  log of operations
 */
function onlinetest_mass_enroll($cir, $onlinetest, $context, $data) {
    global $CFG,$DB, $USER;
    require_once ($CFG->dirroot . '/group/lib.php');
    if (!$enrol_manual = enrol_get_plugin('manual')) {
      throw new coding_exception('Can not instantiate enrol_manual');
    }
    // init csv import helper
    // require_once($CFG->dirroot.'/local/onlinetests/notifications_emails.php');
    // $emaillogs = new onlinetestsnotifications_emails();
    $categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $notification = new \local_onlinetests\notification();
    $useridfield = $data->firstcolumn;
    $cir->init();
    $enrollablecount = 0;
    while ($fields = $cir->next()) {
        $a = new stdClass();
        if (empty ($fields))
            continue;
        $fields[0]= str_replace('"', '', trim($fields[0]));
        /*First Condition To validate users*/
         $sql="SELECT u.* from {user} u where u.deleted=0 and u.suspended=0 and u.$useridfield LIKE '{$fields[0]}' ";

        if(is_siteadmin()){

            $sql .= (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path',$open_path=$program->open_path,'lowerandsamepath');

        }else{

            $sql .= (new \local_users\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path');

        }

        $sql .= (new \local_users\lib\accesslib())::get_userprofilematch_concatsql($program);


        if (!$user = $DB->get_record_sql($sql)) {
            $result .= '<div class="alert alert-danger">'.get_string('im:user_unknown', 'local_courses', $fields[0] ). '</div>';
            continue;
        } else {
            $timeend = 0;
            $timestart = 0; 
            $courseid = $DB->get_field('local_onlinetests','courseid',array('id'=>$onlinetest->id));
            $enrolid = $DB->get_field('enrol', 'id', array('enrol' => 'manual', 'courseid' => $courseid));
            $instance = $DB->get_record('enrol', array('id' => $enrolid, 'enrol' => 'manual'), '*');
            if($instance){
                $roleid = $instance->roleid;
                $enrol_manual->enrol_user($instance, $user->id, $roleid, $timestart, $timeend);                
            }
            $submitted = new stdClass();
            $submitted->timemodified = time();
            $submitted->timecreated = time();
            $type = 'onlinetest_enrollment';
            $dataobj = $onlinetest->id;
            $fromuserid = $USER->id;
            $submitted->userid = $user->id;
            $submitted->onlinetestid = $onlinetest->id;
            $submitted->creatorid = $USER->id;
            $submitted->status = 0;
            $quizid = $DB->get_field('local_onlinetests','quizid',array('id'=>$onlinetestid));
            $submitted->quizid = $quizid;
            $exist = $DB->record_exists('local_onlinetest_users',array('userid'=>$user->id,'onlinetestid'=>$onlinetest->id));
            if(empty($exist)){
              $insert = $DB->insert_record('local_onlinetest_users',$submitted);
              $params = array(
                  'context' => $categorycontext,
                  'relateduserid' => $user->id,
                  'objectid' => $onlinetest->id
              );
              $event = \local_onlinetests\event\onlinetest_enrolled::create($params);
              $event->add_record_snapshot('local_onlinetests', $onlinetest);
              $event->trigger();

                // $touser = \core_user::get_user($userid);
                // $fromuser = \core_user::get_user($userquiz->creatorid);
                $logmail = $notification->onlinetest_notification($type, $user, $USER, $onlinetest);
              // $email_logs = $emaillogs->onlinetests_emaillogs($type,$dataobj,$user->id,$fromuserid);
              $result .= '<div class="alert alert-success">'.get_string('im:enrolled_ok', 'local_courses', $user->open_employeeid).'</div>';

              $enrollablecount ++;
            } else {
                $result .= '<div class="alert alert-error">'.get_string('user_exist', 'local_onlinetests', $fields[0] ). '</div>';
                continue;
            }
        }
    }
    $result .= '<br />';
    $result .= get_string('im:stats_i', 'local_onlinetests', $enrollablecount) . "";
    return $result;
}
/**
    * function costcenterwise_onlinetests_count
    * @todo count of onlinetests under selected costcenter
    * @param int $costcenter costcenter
    * @param int $department department
    * @return  array onlinetests count of each type
*/
function costcenterwise_onlinetests_count($costcenter,$department = false,$subdepartment = false, $l4department=false, $l5department=false){
    global $USER, $DB,$CFG;
    $params = array();
    $params['costcenterpath'] = '%/'.$costcenter.'/%';
    $countonlinetestsql = "SELECT count(id) FROM {local_onlinetests} WHERE concat('/',open_path,'/') LIKE :costcenterpath ";
    if ($department) {
        $countonlinetestsql .= "  AND concat('/',open_path,'/') LIKE :departmentpath  ";
        $params['departmentpath'] = '%/'.$department.'/%';
    }
    if ($subdepartment) {
        $countonlinetestsql .= " AND concat('/',open_path,'/') LIKE :subdepartmentpath ";
        $params['subdepartmentpath'] = '%/'.$subdepartment.'/%';
    }
    if ($l4department) {
        $countonlinetestsql .= " AND concat('/',open_path,'/') LIKE :l4departmentpath ";
        $params['l4departmentpath'] = '%/'.$l4department.'/%';
    }
    if ($l5department) {
        $countonlinetestsql .= " AND concat('/',open_path,'/') LIKE :l5departmentpath ";
        $params['l5departmentpath'] = '%/'.$l5department.'/%';
    }
    $activesql = " AND visible = 1 ";
    $inactivesql = " AND visible = 0 ";

    $countonlinetests = $DB->count_records_sql($countonlinetestsql, $params);
    $activeonlinetests = $DB->count_records_sql($countonlinetestsql.$activesql, $params);
    $inactiveonlinetests = $DB->count_records_sql($countonlinetestsql.$inactivesql, $params);
    if($countonlinetests >= 0){
        if($costcenter){
            $viewonlineexamlink_url = $CFG->wwwroot.'/local/onlinetests/index.php?costcenterid='.$costcenter;
        }
        if($department){
            $viewonlineexamlink_url = $CFG->wwwroot.'/local/onlinetests/index.php?costcenterid='.$costcenter.'&departmentid='.$department;
        }
        if($subdepartment){
            $viewonlineexamlink_url = $CFG->wwwroot.'/local/onlinetests/index.php?costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment;
        }
        if($l4department){
            $viewonlineexamlink_url = $CFG->wwwroot.'/local/onlinetests/index.php?costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment.'&l4department='.$l4department;
        }
        if($l5department){
            $viewonlineexamlink_url = $CFG->wwwroot.'/local/onlinetests/index.php?costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment.'&l4department='.$l4department.'&l5department='.$l5department;
        }
    }

    if($activeonlinetests >= 0){
        if($costcenter){
            $count_onlineexamactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=active&costcenterid='.$costcenter;
        }
        if($department){
            $count_onlineexamactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=active&costcenterid='.$costcenter.'&departmentid='.$department;
        }
        if($subdepartment){
            $count_onlineexamactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=active&costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment;
        }
        if($l4department){
            $count_onlineexamactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=active&costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment.'&l4department='.$l4department;
        }
        if($l5department){
            $count_onlineexamactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=active&costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment.'&l4department='.$l4department.'&l5department='.$l5department;
        }
    }
    if($inactiveonlinetests >= 0){
        if($costcenter){
            $count_onlineexaminactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=inactive&costcenterid='.$costcenter;
        }
        if($department){
            $count_onlineexaminactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=inactive&costcenterid='.$costcenter.'&departmentid='.$department;
        }
        if($subdepartment){
            $count_onlineexaminactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=inactive&costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment;
        }
        if($l4department){
            $count_onlineexaminactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=inactive&costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment.'&l4department='.$l4department;
        }
        if($l5department){
            $count_onlineexaminactivelink_url = $CFG->wwwroot.'/local/onlinetests/index.php?status=inactive&costcenterid='.$costcenter.'&departmentid='.$department.'&subdepartmentid='.$subdepartment.'&l4department='.$l4department.'&l5department='.$l5department;
        }
    }

    return array('onlineexam_plugin_exist' => true,'onlineexamcount' => $countonlinetests,'activeonlineexamcount' => $activeonlinetests,'inactiveonlineexamcount' => $inactiveonlinetests,'viewonlineexamlink_url'=>$viewonlineexamlink_url,'count_onlineexamactivelink_url' =>$count_onlineexamactivelink_url,'count_onlineexaminactivelink_url' =>$count_onlineexaminactivelink_url);
}
/**
    * function onlinetestslist
    * @todo all exams based  on costcenter / department
    * @param object $stable limit values
    * @param object $filterdata filterdata
    * @return  array courses
*/
function onlinetestslist($stable, $filterdata) {
    global $DB, $PAGE, $CFG, $USER;
    $context = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $departmentsparams = array();
    $organizationsparams = array();
      $onlinetestsparams = array();
    $userorg = array();
    $departmentsparams = array();
    $organizationsparams = array();
    $onlinetestsparams = array();
    $data = array();
    $countsql = "SELECT count(a.id) ";
    $sql ="SELECT a.* ";

    $open_path = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname = 'a.open_path');

    $loop_open_path = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname = 'u.open_path');

   if ( has_capability('local/onlinetests:manage',$context) ) { // check for department head

         $fromsql = " FROM {local_onlinetests} AS a where a.id > 0 $open_path";

    } else { // check for users

        $fromsql = " FROM {local_onlinetests} AS a, {local_onlinetest_users} AS eu where a.id = eu.onlinetestid AND eu.userid = :userid AND a.visible = 1 $open_path";
        $userorder = 1;
        $userorg = array('userid'=>$USER->id);
    }
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){

        $fromsql .= " AND a.name LIKE :search ";

        $searchparams = array('search' => '%'.trim($filterdata->search_query).'%');

    }else{

        $searchparams = array();

    }
    if (!empty($filterdata->filteropen_costcenterid)) {

        $filteropen_costcenterid = explode(',', $filterdata->filteropen_costcenterid);
        $orgsql = [];
        foreach ($filteropen_costcenterid as $organisation) {
            $orgsql[] = " concat('/',a.open_path,'/') LIKE :organisationparam_{$organisation}";
            $searchparams["organisationparam_{$organisation}"] = '%/' . $organisation . '/%';
        }
        if (!empty($orgsql)) {
            $fromsql .= " AND ( " . implode(' OR ', $orgsql) . " ) ";
        }
    }
    if (!empty($filterdata->filteropen_department)) {
        $filteropen_department = explode(',', $filterdata->filteropen_department);

        $deptsql = [];
        foreach ($filteropen_department as $department) {
            $deptsql[] = " concat('/',a.open_path,'/') LIKE :departmentparam_{$department}";
            $searchparams["departmentparam_{$department}"] = '%/' . $department . '/%';
        }
        if (!empty($deptsql)) {
            $fromsql .= " AND ( " . implode(' OR ', $deptsql) . " ) ";
        }
    }
    if (!empty($filterdata->filteropen_subdepartment)) {
        $subdepartments = explode(',', $filterdata->filteropen_subdepartment);

        $subdeptsql = [];
        foreach ($subdepartments as $subdepartment) {
            $subdeptsql[] = " concat('/',a.open_path,'/') LIKE :subdepartmentparam_{$subdepartment}";
            $searchparams["subdepartmentparam_{$subdepartment}"] = '%/' . $subdepartment . '/%';
        }
        if (!empty($subdeptsql)) {
            $fromsql .= " AND ( " . implode(' OR ', $subdeptsql) . " ) ";
        }
    }
    if (!empty($filterdata->filteropen_level4department)) {
        $subsubdepartments = explode(',', $filterdata->filteropen_level4department);

        $subsubdeptsql = [];
        foreach ($subsubdepartments as $department4level) {
            $subsubdeptsql[] = " concat('/',a.open_path,'/') LIKE :department4levelparam_{$department4level}";
            $searchparams["department4levelparam_{$department4level}"] = '%/' . $department4level . '/%';
        }
        if (!empty($subsubdeptsql)) {
            $fromsql .= " AND ( " . implode(' OR ', $subsubdeptsql) . " ) ";
        }
    }
    if (!empty($filterdata->filteropen_level5department)) {
        $subsubsubdepartments = explode(',', $filterdata->filteropen_level5department);
        $subsubsubdeptsql = [];
        foreach ($subsubsubdepartments as $department5level) {
            $subsubsubdeptsql[] = " concat('/',a.open_path,'/') LIKE :department5levelparam_{$department5level}";
            $searchparams["department5levelparam_{$department5level}"] = '%/' . $department5level . '/%';
        }
        if (!empty($subsubsubdeptsql)) {
            $fromsql .= " AND ( " . implode(' OR ', $subsubsubdeptsql) . " ) ";
        }
    }

    if(!empty($filterdata->status)){
        // $status = explode(',',$filterdata->status);
        $status = (array)$filterdata->status;
        if(!(in_array('active',$status) && in_array('inactive',$status))){
            if(in_array('active' ,$status)){
                $fromsql .= " AND a.visible = 1 ";           
            }else if(in_array('inactive' ,$status)){
                $fromsql .= " AND a.visible = 0 ";
            }
        }
    }
  
    if(!empty($filterdata->onlinetests)){
         // $onlinetests = explode(',', $filterdata->onlinetest);
          $onlinetests = $filterdata->onlinetests;
         
        list($onlinetestssql, $onlinetestsparams) = $DB->get_in_or_equal($onlinetests, SQL_PARAMS_NAMED, 'param', true, false);
        $fromsql .= " AND a.id $onlinetestssql ";    
    }

    if ($userorder == 1)
    $ordersql = " order by eu.timecreated DESC ";
    else
    $ordersql = " order by a.id DESC ";

    $params = $userorg+$departmentsparams+$organizationsparams+$onlinetestsparams+$searchparams;
    $recordscount = $DB->count_records_sql($countsql.$fromsql, $params);


    $onlinetests = $DB->get_records_sql($sql.$fromsql.$ordersql, $params, $stable->start, $stable->length);

    foreach($onlinetests as $record){
        $row = array(); 
        $line = array();
        
        $cm = get_coursemodule_from_instance('quiz', $record->quizid, 0, false, MUST_EXIST);
        $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$record->quizid, 'itemmodule'=>'quiz', 'courseid'=>$record->courseid));
        $buttons=array();
        $is_admin = '';
        $actions = '';
        if(has_capability('local/onlinetests:manage', $context)){
            $actions = true;
            if (has_capability('local/onlinetests:create', $context)) {
                $edit = true;
                $hide_show = true;
            }
            if (has_capability('local/onlinetests:create', $context)) {
                $questions = true;
            }
            if (has_capability('local/onlinetests:enroll_users', $context)) {
                $users = true;
                $addusers = true;
                $bulkenrollusers = true;
            }
            if (has_capability('local/onlinetests:delete', $context)) {
                $delete = true;
            }
        }
        $extrainfo = '';
        $enrolled_sql = "SELECT count(ou.id) as attendcount 
            FROM {local_onlinetest_users} AS ou, {user} AS u 
            where u.id = ou.userid AND u.deleted = 0 
            AND u.suspended = 0 AND  ou.onlinetestid= ? $loop_open_path ";

        $attendcount = $DB->get_record_sql($enrolled_sql, [$record->id]);
        
        if ($record->visible) {
            $hide = 1;
            $show = 0;
        } else {
            $show = 1;
            $hide = 0;
        }

        if($record->timeopen==0 AND $record->timeclose==0) {
            $dates= get_string('open', 'local_onlinetests');
        } elseif(!empty($record->timeopen) AND empty($record->timeclose)) {
            $dates = 'From '. \local_costcenter\lib::get_userdate("d/m/Y H:i", $record->timeopen);
        } elseif (empty($record->timeopen) AND !empty($record->timeclose)) {
            $dates = 'Ends on '. \local_costcenter\lib::get_userdate("d/m/Y H:i", $record->timeclose);
        } else {
            $dates = \local_costcenter\lib::get_userdate("d/m/Y H:i", $record->timeopen).  ' to '  . \local_costcenter\lib::get_userdate("d/m/Y H:i", $record->timeclose);
        }
        $completed_sql = "SELECT count(ou.id) from {local_onlinetest_users} AS ou, {user} AS u 
        WHERE u.id = ou.userid AND u.deleted = 0 
        AND u.suspended = 0 AND ou.onlinetestid=? AND ou.status=? $loop_open_path";


        $completed_count = $DB->count_records_sql($completed_sql, array($record->id, 1));

        $grademax = ($gradeitem->grademax) ? round($gradeitem->grademax,2): '-';
        $gradepass = ($gradeitem->gradepass) ? round($gradeitem->gradepass,2): '-';
        $testname = strlen($record->name) > 10 ? substr($record->name, 0, 10)."..." : $record->name;
        if ($record->departmentid)
        $departments = $DB->get_field_sql("select c.fullname as depts from {local_costcenter} c where CONCAT(',',c.id,',') LIKE CONCAT('%,',$record->departmentid,',%')  ");

        $departmentsCut = $departments;

        if (is_siteadmin() OR has_capability('local/onlinetests:manage', $context)) {
            $is_admin = true;
            $departmentscount =   explode(',',$departmentsCut); 
            if(count($departmentscount)>1){ 
               $departmentname = strlen($departmentscount[0]) > 15 ? substr($departmentscount[0], 0, 15) : $departmentscount[0];
              $departmentsCut =  $departmentname. '...';
            }elseif(count($departmentscount)==1){
               $departmentname = strlen($departmentscount[0]) > 16 ? substr($departmentscount[0], 0, 16) : $departmentscount[0];
               $departmentsCut = $departmentname. '...';
            }
            if(empty($departments)){
               $departmentsCut = 'All';
            }
            // print_object($record);
            $line['testname'] = $testname;
            $line['testfullname'] = $record->name;
            $line['testdate'] = $dates;
            $line['maxgrade'] = $grademax;
            $line['mygrade'] = '';
            $line['passgrade'] = $gradepass;
            $line['configpath'] = $CFG->wwwroot;
            $line['edit'] = $edit;
            $line['questions'] = $questions;
            $line['users'] = $users;
            $line['addusers'] = $addusers;
            $line['bulkenrollusers'] = $bulkenrollusers;
            $line['delete'] = $delete;
            $line['enrolled'] = $attendcount->attendcount;
            $line['testid'] = $record->id;
            $line['quizid'] = $record->quizid;
            $line['is_admin'] = $is_admin;
            $line['cmid'] = $cm->id;
            $line['completed'] = $completed_count;
            $line['sesskey'] = sesskey();
            $line['attempts'] = 0;
            $line['departmentsCut'] = $departmentsCut;
            $line['deptname'] = $departments;
            $line['enrolledon'] = '';
            $line['completedon'] = '';
            $line['status'] = '';
            $line['canreview'] = 0;
            $line['userque'] = false;
            $line['usertwoactions'] = false;
            $line['userreview'] = false;
            $line['userhasactions'] = false;
            $line['userattemptid'] = 0;
            $line['completed'] = $completed_count;
            if($record->courseid==1){
                $line['starttest_url'] = $CFG->wwwroot.'/mod/quiz/view.php?id='.$cm->id;
            }else{
                $line['starttest_url'] = $CFG->wwwroot.'/course/view.php?id='.$record->courseid;
            }
            $line['hide_show'] = $hide_show;
            $line['hide'] = $hide;
            $line['show'] = $show;
            $line['actions'] = $actions;
            $line['contextid'] = $context->id;

        } else{
            $is_admin = false;
            $actions = true;
            $can_review = 0;
            $userquizrecord = $DB->get_record_sql("select * from {local_onlinetest_users} where onlinetestid=? AND userid =? ", [$record->id, $USER->id]);
            $enrolledon = \local_costcenter\lib::get_userdate("d/m/Y H:i", $userquizrecord->timecreated);
            $userattempt = new stdclass();
            $attempts = 0;
            $userreview = false;
            $userque = false;
            $sql="SELECT * FROM {quiz_attempts} where id=(SELECT max(id) id from {quiz_attempts} where userid=? and quiz=?)";
            $userattempt = $DB->get_record_sql($sql, [$USER->id, $record->quizid]);
            $attempts = ($userattempt->attempt) ? $userattempt->attempt : 0;
            $time = time();
            if ($record->timeclose !=0 AND $time >= $record->timeclose)
              $timeclose = true;
            else
              $userque = true;
            
            if ($attempts) {                    
              $userreview = true;
            }
            if ($gradeitem->id)
            $usergrade = $DB->get_record_sql("select * from {grade_grades} where itemid = ? AND userid = ? ", [$gradeitem->id, $USER->id]);
            if ($usergrade) {
                $mygrade = round($usergrade->finalgrade, 2);
                if ($usergrade->finalgrade >= $gradepass) {
                    $completedon = \local_costcenter\lib::get_userdate("d/m/Y ", $usergrade->timemodified);
                    $can_review = 1;
                    $status = 'Completed';
                } else {
                    $status = 'Incomplete';
                    $completedon = 'N/A';
                }                   
            } else {
                $mygrade = '-';
                $status = 'Pending';
                $completedon = 'N/A';
                $attempts = 0;
            }    
            $usertwoactions = false;
            $student_attemptid =  $userattempt->id;
            if($userque && $student_attemptid){
                $usertwoactions = true;
            }else{
                $usertwoactions = false;
            }
            $userhasactions = false;
            if(empty($userque || $student_attemptid)){
                $userhasactions = true;
            }else{
                $userhasactions = false;
            }
            if(!is_siteadmin()){
            $switchedrole = $USER->access['rsw']['/1'];
            if($switchedrole){
                $userrole = $DB->get_field('role', 'shortname', array('id' => $switchedrole));
            }else{
                $userrole = null;
            }
            if(is_null($userrole) || $userrole == 'user'){

            $certificate_plugin_exist = \core_component::get_plugin_directory('tool', 'certificate');

            if($certificate_plugin_exist){
                if(!empty($record->certificateid)){
                    $certificate_exists = true;
                    $usergrade = $DB->get_record_sql("select * from {grade_grades} where itemid = ? AND userid = ? ", [$gradeitem->id, $USER->id]);
            if($usergrade) {
                $mygrade = round($usergrade->finalgrade, 2);
                if ($usergrade->finalgrade >= $gradepass) {
                    //$completedon = \local_costcenter\lib::get_userdate("d/m/Y ", $usergrade->timemodified);
                    $can_review = 1;
                    $status = 'Completed';
                    $certificate_download= true;
                } else {
                    $status = 'Incomplete';
                    $certificate_download = false;
                    //$completedon = 'N/A';
                }                   
            }
                    /*$sql = "SELECT id 
                            FROM {local_onlinetest_users}
                            WHERE onlinetestid = $record->id
                            AND status = 1 ";
                    $completed = $DB->record_exists_sql($sql, array('userid'=>$USER->id));*/
               /* if($usergrade){
                    $certificate_download= true;
                 
                }else{
                    $certificate_download = false;
                }*/
                $certificateid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$record->id,'userid'=>$USER->id,'moduletype'=>'onlinetest'));
                //$certificate_download['moduletype'] = 'classroom';
                }
            }
       
        }
    }

            $line['testid'] = $record->id;
            $line['testname'] = $testname;
            $line['testfullname'] = $record->name;
            $line['quizid'] = $record->quizid;
            $line['testdate'] = $dates;
            $line['maxgrade'] = $grademax;
            $line['passgrade'] = $gradepass;
            $line['mygrade'] = $mygrade;
            $line['sesskey'] = sesskey();
            $line['attempts'] = $attempts;
            $line['enrolledon'] = $enrolledon;
            $line['enrolled'] = 0;
            $line['completed'] = 0;
            $line['users'] = 0;
            $line['addusers'] = false;
            $line['bulkenrollusers'] = false;
            $line['delete'] = false;
            $line['edit'] = false;
            $line['questions'] = false;
            $line['completedon'] = $completedon;
            $line['status'] = $status;
            $line['canreview'] = $can_review;
            $line['is_admin'] = $is_admin;
            $line['configpath'] = $CFG->wwwroot;
            $line['timeclose'] = $timeclose;
            $line['actions'] = $actions;
            $line['deptname'] = $departments;
            $line['departmentsCut'] = '';
            $line['userque'] = $userque;
            $line['usertwoactions'] = $usertwoactions;
            $line['userreview'] = $userreview;
            $line['userhasactions'] = $userhasactions;
            $line['userattemptid'] = $userattempt->id;
            $line['cmid'] = $cm->id;
            $line['hide_show'] = false;
            $line['hide'] = 0;
            $line['show'] = 0;
            $line['contextid'] = $context->id;
            $line['starttest_url'] = $CFG->wwwroot .'/local/onlinetests/check_enrolsettings.php?courseid='. $record->courseid.'&cmid='.$cm->id;
            $line['certificate_exists'] = $certificate_exists;
            $line['certificate_download'] = $certificate_download;
            $line['certificateid'] = $certificateid;
        }
        $data[] = $line;
    }

    return array('totalrecords' => $recordscount,'records' => $data);
}

function local_onlinetests_output_fragment_enrolled_users($args) {
    global $DB, $USER;
    $record = (object) $args;
    $sytemcontext = (new \local_onlinetests\lib\accesslib())::get_module_context();

    $core_component = new \core_component();

    $certificate_plugin_exist = \core_component::get_plugin_directory('tool', 'certificate');

    if($certificate_plugin_exist){
        $certid = $DB->get_field('local_onlinetests', 'certificateid', array('id'=>$record->testid));
    }else{
        $certid = false;
    }

    $open_path = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname = 'u.open_path');

    if ($record->type == 1) {
        $sql ="SELECT ou.*,u.id as userid,u.firstname, u.lastname, u.email, u.open_employeeid, 
        o.id as onlinetestid, o.quizid,o.courseid
                           from {local_onlinetest_users} ou
                           JOIN {local_onlinetests} o ON ou.onlinetestid = o.id
                           JOIN {user} u ON ou.userid=u.id AND u.deleted = 0 AND u.suspended = 0
                           where ou.onlinetestid = ?  $open_path ";

        $assignedusers= $DB->get_records_sql($sql, array($record->testid));
        $out='';
        $data=array();
        if(!empty($assignedusers)){
            foreach($assignedusers as $assigneduser){
                $row=array();
                // $user=$DB->get_record_sql("SELECT * FROM {user} WHERE id=$assigneduser->userid");
                
                $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$assigneduser->quizid, 'itemmodule'=>'quiz', 'courseid'=>$assigneduser->courseid));
                $gradepass = ($gradeitem->gradepass) ? round($gradeitem->gradepass,2): '-';
                if ($gradeitem->id)
                $usergrade = $DB->get_record_sql("select * from {grade_grades} where itemid = ? AND userid = ? ", [$gradeitem->id, $assigneduser->userid]);
                if ($usergrade) {
                    $mygrade = round($usergrade->finalgrade, 2);
                    if ($usergrade->finalgrade >= $gradepass) {
                        $status = get_string('completed', 'local_onlinetests');
                    } else {
                        $status = get_string('incompleted', 'local_onlinetests');
                    }                   
                } else {
                    $mygrade = '-';
                    $attempt = $DB->get_record_sql("SELEct max(attempt) as noofattempts from {quiz_attempts} where quiz = ? AND userid = ? ", [$assigneduser->quizid, $assigneduser->userid]);
                    if ($attempt->noofattempts)
                    $status = get_string('pending', 'local_onlinetests');
                    else
                    $status = get_string('notyetstart', 'local_onlinetests');
                }
                // if($user){
                    $row[] = $assigneduser->firstname. ' '. $assigneduser->lastname;
                    $row[] = $assigneduser->email;
                    $row[] = ($assigneduser->open_employeeid) ? $assigneduser->open_employeeid:'-';
                    $row[] = \local_costcenter\lib::get_userdate("d/m/Y H:i", $assigneduser->timecreated);
                    $row[] = $mygrade;
                    $row[] = $status;
                // }
                $data[]=$row;
            }
        } 
        $table = new html_table();
        $head = array('<b>'.get_string('employee', 'local_onlinetests').'</b>', '<b>'.get_string('email').'</b>','<b>'.get_string('employeeid', 'local_users').'</b>','<b>'.get_string('enrolledon', 'local_onlinetests').'</b>', '<b>'.get_string('grade', 'local_onlinetests').'</b>','<b>'.get_string('status', 'local_onlinetests').'</b>');

        $table->head = $head;
        $table->width = '100%';
        $table->align = array('left','left','center','center','center','left');
        $table->id ='onlinetest_assigned_users'.$id.'';
        $table->attr['class'] ='onlinetest_assigned_users';
        if ($data){
            $table->data = $data;
        }else{
            $table->data = array([0 => '<tr><td colspan="6" style="text-align:center;">No Records Found</td></tr>']);
        }

        $out.= html_writer::table($table);
        $out.=html_writer::script('$(document).ready(function() {
            $("#onlinetest_assigned_users'.$id.'").dataTable({
                language: {
                    emptyTable: "No Records Found",
                    paginate: {
                        previous: "<",
                        "next": ">"
                    }
                },
            });
        });');
    }  else {
        $sql = "SELECT distinct(u.id) as userid, u.firstname,u.lastname,u.email, u.open_employeeid, o.id as testid, o.quizid, ou.timecreated,ou.timemodified,o.courseid
                       from {local_onlinetest_users} ou
                       JOIN {local_onlinetests} o ON ou.onlinetestid = o.id
                       JOIN {user} u ON ou.userid = u.id AND u.deleted = 0 AND u.suspended = 0
                       where o.id = ? AND ou.status = 1 $open_path ";

        $assignedusers = $DB->get_records_sql($sql, array($record->testid));
        $out = '';
        $data = array();
        if(!empty($assignedusers)){
           foreach($assignedusers as $assigneduser){
                $row = array();
                // $user = $DB->get_record_sql("SELECT * FROM {user} WHERE id = ? ", [$assigneduser->userid]);
                $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$assigneduser->quizid, 'itemmodule'=>'quiz', 'courseid'=>$assigneduser->courseid));
                if ($gradeitem->id)
                $usergrade = $DB->get_record_sql("SELECT * FROM {grade_grades} where itemid = ? AND userid = ? ", [$gradeitem->id, $assigneduser->userid]);
                if ($usergrade) {
                    $mygrade = round($usergrade->finalgrade, 2);
                } else {
                    $mygrade = '-';
                }
                // if($user){
                    $row[] = $assigneduser->firstname. ' '. $assigneduser->lastname;
                    $row[] = $assigneduser->email;
                    $row[] = ($assigneduser->open_employeeid) ? $assigneduser->open_employeeid:'-';
                    $row[] = $mygrade;
                    $row[] = \local_costcenter\lib::get_userdate("d/m/Y H:i", $assigneduser->timecreated);
                    $row[] = \local_costcenter\lib::get_userdate("d/m/Y H:i", $assigneduser->timemodified);
                    
                    if($certid){

                        $certcode = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$record->testid,'userid'=>$assigneduser->userid,'moduletype'=>'onlinetest'));
                            $array = array('code' =>$certcode);
                        $url = new moodle_url('/admin/tool/certificate/view.php', $array);
                        $icon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
                        $downloadlink = html_writer::link($url, $icon, array('title'=>get_string('download_certificate','tool_certificate')));
                        $row[] = $downloadlink;
                    }
                    

                // }
                $data[] = $row;
             }        
        }
        $table = new html_table();
        $head = array('<b>'.get_string('username', 'local_onlinetests').'</b>', '<b>'.get_string('email').'</b>','<b>'.get_string('employeeid', 'local_users').'</b>', '<b>'.get_string('grade', 'local_onlinetests').'</b>','<b>'.get_string('enrolledon', 'local_onlinetests').'</b>','<b>'.get_string('completedon', 'local_onlinetests').'</b>');
        if($certid){
            $head[] = get_string('certificate','tool_certificate');
        }
        $table->head = $head;
        $align = array('left','left','center','center','center','center');
        if($certid){
            $align[] = 'center';
        }
        $table->align = $align;
        if ($data){
            $table->data = $data;
        }else{
            $table->data = array([0 => '<tr><td colspan="6" style="text-align:center;">No Records Found</td></tr>']);
        }
        $table->width = '100%';
        $table->id ='completed_users_view'.$id.'';
        $table->attr['class'] ='completed_users_view';        
        $out.= html_writer::table($table);
        $out.=html_writer::script('$(document).ready(function() {
             $("#completed_users_view'.$id.'").dataTable({
                bInfo : false,
                lengthMenu: [5, 10, 25, 50, -1],
                    language: {
                              emptyTable: "No Records Found",
                                paginate: {
                                            previous: "<",
                                            next: ">"
                                        }
                         },
             });
        });');
    }

    return $out;
}

/*
* Author sarath
* @return true for reports under category
*/
function learnerscript_onlinetests_list(){
    return 'Onlinetests';
}
function onlinetests_filter($mform, $query='', $searchanywhere=false, $page=0, $perpage=25){
    global $DB,$USER;
    $categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $onlinetestlist=array();
    $data=data_submitted();


     $open_path = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname = 'a.open_path');

   if ( has_capability('local/onlinetests:manage',$categorycontext) ) { // check for department head

        $onlinetest_sql="SELECT id, name AS fullname FROM {local_onlinetests} WHERE 1=1 $open_path ";

    } else { // check for users

        $onlinetest_sql="SELECT id, name AS fullname FROM {local_onlinetests} WHERE id IN (SELECT onlinetestid FROM {local_onlinetest_users} WHERE userid = {$USER->id}) AND visible=1 $open_path ";
    }

    if(!empty($query)){ 
        if ($searchanywhere) {
            $onlinetest_sql.=" AND name LIKE '%$query%' ";
        } else {
            $onlinetest_sql.=" AND name LIKE '$query%' ";
        }
    }
    if(isset($data->onlinetests)&&!empty(($data->onlinetests))){
    
        $implode=implode(',',$data->onlinetests);
        
        $onlinetest_sql.=" AND id in ($implode) ";
    }
    if(!empty($query)||empty($mform)){

        $onlinetestlist = $DB->get_records_sql($onlinetest_sql, array(), $page, $perpage);
        return $onlinetestlist;
    }
    if((isset($data->departments)&&!empty($data->departments))){ 
        $onlinetestlist = $DB->get_records_sql_menu($onlinetest_sql, array(), $page, $perpage);
    }
    
    $options = array(
            'ajax' => 'local_courses/form-options-selector',
            'multiple' => true,
            'data-action' => 'onlinetests',
            'data-options' => json_encode(array('id' => 0)),
            'placeholder' => get_string('onlinetest','local_onlinetests')
    );
        
    $select = $mform->addElement('autocomplete', 'onlinetests', '', $onlinetestlist,$options);
    $mform->setType('onlinetests', PARAM_RAW);
}

/**
 * Returns onlinetests tagged with a specified tag.
 *
 * @param local_tags_tag $tag
 * @param bool $exclusivemode if set to true it means that no other entities tagged with this tag
 *             are displayed on the page and the per-page limit may be bigger
 * @param int $fromctx context id where the link was displayed, may be used by callbacks
 *            to display items in the same context first
 * @param int $ctx context id where to search for records
 * @param bool $rec search in subcontexts as well
 * @param int $page 0-based number of page being displayed
 * @return \local_tags\output\tagindex
 */
function local_onlinetests_get_tagged_tests($tag, $exclusivemode = false, $fromctx = 0, $ctx = 0, $rec = 1, $page = 0, $sort = '') {
    global $CFG, $PAGE;
    // prepare for display of tags related to tests
    $perpage = $exclusivemode ? 10 : 5;
    $displayoptions = array(
        'limit' => $perpage,
        'offset' => $page * $perpage,
        'viewmoreurl' => null,
    );
    $renderer = $PAGE->get_renderer('local_onlinetests');
    $totalcount = $renderer->tagged_onlinetests($tag->id, $exclusivemode, $ctx, $rec, $displayoptions, $count = 1,$sort);
    $content = $renderer->tagged_onlinetests($tag->id, $exclusivemode, $ctx, $rec, $displayoptions,0,$sort);
    $totalpages = ceil($totalcount / $perpage);
    if ($totalcount)
    return new local_tags\output\tagindex($tag, 'local_onlinetests', 'onlinetests', $content,
            $exclusivemode, $fromctx, $ctx, $rec, $page, $totalpages);
    else
    return '';
}


function get_test_details($testid) { // test id not quizid
    global $USER, $DB;
    $context = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $details = array();
    $joinsql = '';
    if(has_capability('local/costcenter:manage_ownorganization',$context) OR 
        has_capability('local/costcenter:manage_owndepartments',$context)) {
        $selectsql = "select o.id as oid, o.quizid,o.courseid ";
        $fromsql = " from  {local_onlinetests} o ";
        if ($DB->get_manager()->table_exists('local_rating')) {
                $selectsql .= " , AVG(rating) as avg ";
                $joinsql .= " LEFT JOIN {local_rating} as r ON r.moduleid = o.id AND r.ratearea = 'local_onlinetests' ";
            }

        $wheresql = "where o.id = ?";

        $adminrecord = $DB->get_record_sql($selectsql.$fromsql.$joinsql.$wheresql, [$testid]);
        $details['manage'] = 1;
        $completedcount = $DB->count_records_sql("select count(ou.id) from {local_onlinetest_users} ou, {user} u where
            u.id = ou.userid AND u.deleted = 0 AND u.suspended = 0 AND ou.onlinetestid=? AND ou.status=?", array($testid, 1));
        $enrolledcount = $DB->count_records_sql("select count(ou.id) from {local_onlinetest_users} ou, {user} u where
            u.id = ou.userid AND u.deleted = 0 AND u.suspended = 0 AND ou.onlinetestid=? ", array($testid));
        $details['completed'] = $completedcount;
        $details['enrolled'] = $enrolledcount;
        $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$adminrecord->quizid, 'itemmodule'=>'quiz', 'courseid'=>$adminrecord->courseid));
        $details['maxgrade'] = ($gradeitem->grademax) ? round($gradeitem->grademax): '-';
        $details['passgrade'] = ($gradeitem->gradepass) ? round($gradeitem->gradepass): '-';
    } else {
        $selectsql = "select ou.*, o.quizid, o.id as oid ";
        $fromsql = " from {local_onlinetest_users} ou 
        JOIN {local_onlinetests} o ON o.id = ou.onlinetestid ";
        if ($DB->get_manager()->table_exists('local_rating')) {
            $selectsql .= " , AVG(rating) as avg ";
            $joinsql .= " LEFT JOIN {local_rating} as r ON r.moduleid = o.id AND r.ratearea = 'local_evaluation' ";
        }
        $wheresql = "where 1 = 1 AND userid = ? AND o.id = ? ";

        $record = $DB->get_record_sql($selectsql.$fromsql.$joinsql.$wheresql, [$USER->id, $testid]);


        $sql="SELECT * FROM {quiz_attempts} where id=(SELECT max(id) id from {quiz_attempts} where userid=? and quiz=?)";
        $userattempt = $DB->get_record_sql($sql, [$USER->id, $record->quizid]);
        $details['manage'] = 0;
        $details['status'] = ($record->status == 1) ? get_string('completed', 'local_onlinetests'):get_string('pending', 'local_onlinetests');
        $details['enrolled'] = ($record->timecreated) ? \local_costcenter\lib::get_userdate("d/m/Y H:i", $record->timecreated): '-';
        $details['completed'] = ($record->timemodified) ? \local_costcenter\lib::get_userdate("d/m/Y H:i", $record->timemodified): '-';
        $details['attempts'] = $userattempt;
    }
    
    return $details;
}

function onlinetests_filters_form($filterparams, $ajaxformdata = null){
    global $CFG;

    require_once($CFG->dirroot . '/local/courses/filters_form.php');
    $categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();
    $action = isset($filterparams['action']) ? $filterparams['action'] : '';

    $mform = new filters_form(null, array('filterlist'=>array( 'hierarchy_fields','onlinetests','status'),'plugins'=>array('onlinetests','costcenter'),'filterparams' => $filterparams, 'action' => $action), 'post', '', null, true, $ajaxformdata);


    return $mform;

}
function get_user_enrolsettings($courseid){
    global $USER, $DB;
    $sql="SELECT ue.timestart,ue.timeend,c.fullname FROM {enrol} e JOIN {user_enrolments} ue ON e.id=ue.enrolid JOIN {course} c ON c.id=e.courseid WHERE e.enrol='manual' AND e.courseid=? AND ue.userid=?";

    $enroldetails = $DB->get_record_sql($sql, [$courseid, $USER->id]);

    return $enroldetails;
}
function backupnrestore_onlinetesquiz(){
    global $DB, $USER, $CFG;
    require_once $CFG->dirroot . '/backup/util/includes/backup_includes.php';
    require_once $CFG->dirroot . '/backup/util/includes/restore_includes.php';
    $start = 0;
    $limit = 1;
    $count =0 ;
   $onlinetests = $DB->get_records_sql("SELECT * FROM {local_onlinetests}
                                    WHERE courseid = 1 AND restored = 0 ORDER BY id DESC LIMIT 1");
if (count($onlinetests) <= 0) {
    echo "No online tests to restore : " . date('Y-m-d H:m:i') . ".\n";
} else {
    foreach ($onlinetests as $onlinetest) {
        echo "\n";
        echo "Step :1 =>  Course Creation Start For online test- " . $onlinetest->name. ".\n";

        // Fetch the quizid from onlinetest.
        $quiz = $DB->get_record('quiz', ['id' => $onlinetest->quizid]);

        // Create object to create course.
        $course = new stdClass();
        $course->fullname = $onlinetest->name; // Replace with your desired course name
        $course->shortname = 'oex_' . $onlinetest->name; // Replace with a unique shortname
        $course->category = $DB->get_field('local_costcenter', 'category', array('path' => $onlinetest->open_path)); // Replace with the category ID where you want to create the course
        $course->format = 'singleactivity';
        //$course->summary = 'Course summary goes here';
        $course->open_module = 'online_exams';
        $course->open_coursetype = 1;
        $course->startdate = $onlinetest->timeopen ? $onlinetest->timeopen : $onlinetest->timemodified;
        $course->enddate = $onlinetest->timeclose;;
        $course->idnumber = '';
        $course->calendartype = '';
        $course->theme = '';
        $course->lang = '';
        $course->open_path = $onlinetest->open_path;
        $course->open_certificateid = $onlinetest->certificateid;

        // Check for course if existing test name.
        $newcourseid = $DB->get_field('course', 'id', ['shortname' => $course->shortname]);
        if (!$newcourseid) {
            // Create new Course.
            $newcourse = create_course($course);
            $newcourseid = $newcourse->id;
            echo "Step :2 => Course is created.\n";
        } else {
            echo "Step :2 => Course was already created.\n";
        }

        // Check Self Enrol plugin Exist or not.
        echo "Step :3 => Enrolling users to the course -" . $onlinetest->name. "\n";
        if (!$enrol_manual = enrol_get_plugin('self')) {
            throw new coding_exception('Can not instantiate enrol_self');
        }

        // Get the intance of self enrol.
        $instance = $DB->get_record('enrol', array('courseid' => $newcourseid, 'enrol' => 'self'));
        $roleid = $instance->roleid;

        // Get Onlinetest user enrolments.
        $onlinetest_user_sql = "SELECT  DISTINCT(lou.userid) as userid,lou.timecreated as timecreated
        FROM {local_onlinetest_users} AS lou
        JOIN {local_onlinetests} AS lo ON lo.id = lou.onlinetestid
        LEFT JOIN {grade_items} AS gi ON gi.iteminstance = lo.quizid AND itemmodule LIKE 'quiz' 
        LEFT JOIN {grade_grades} AS gg ON gg.itemid = gi.id
        JOIN {user} u ON u.id = lou.userid AND u.deleted = 0
        WHERE lou.onlinetestid = :onlinetestid /* AND lou.status = :status */ ";
        $onlinetest_users = $DB->get_records_sql($onlinetest_user_sql, array('onlinetestid' => $onlinetest->id, 'status' => 0));
        
        // Enrol users to courses.
        $context = context_course::instance($newcourseid);
        $i = 0;
        foreach ($onlinetest_users as $user) {
            if (!is_enrolled($context, $user->userid)) {
                if($instance){
                $enrol_manual->enrol_user($instance, $user->userid, $roleid, $user->timecreated);
                $i++;
                }else{
                    echo "Error : self enrolement not enabled.";
                }
            }
        }
        echo "Step :4 => Total users enrolled to the course -" . $i . ".\n";

        // Replace with the actual quiz ID from the site-level course
        $quiz_id = $quiz->id;

        // Replace with the actual target course ID where you want to add the quiz
        $module = $DB->get_field('modules', 'id', ['name' => 'quiz']);
        $cmid = $DB->get_field('course_modules', 'id', ['instance' => $quiz_id, 'module' => $module], '*', MUST_EXIST);

        // First take backup of Activity.
        echo "Step :5 => Taking Backup for quiz activity " . $quiz_id .".\n";
        $bc = new backup_controller(backup::TYPE_1ACTIVITY, $cmid, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id);

        foreach ($backupsettings as $name => $value) {
            if ($setting = $bc->get_plan()->get_setting($name)) {
                $bc->get_plan()->get_setting($name)->set_value($value);
            }
        }

        $backupid = $bc->get_backupid();
        $backupbasepath = $bc->get_plan()->get_basepath();

        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];

        $bc->destroy();
        echo "Step :6 => quiz backup is complete. \n";

        // Restore the backup immediately.

        // Check if we need to unzip the file because the backup temp dir does not contains backup files.
        if (!file_exists($backupbasepath . "/moodle_backup.xml")) {
            $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
        }
        echo "Step :7 => Restoring Backup for quiz activity. \n";
        $rc = new restore_controller($backupid, $newcourseid,
            backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $USER->id, backup::TARGET_NEW_COURSE);

        foreach ($backupsettings as $name => $value) {
            $setting = $rc->get_plan()->get_setting($name);
            if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                $setting->set_value($value);
            }
        }

        if (!$rc->execute_precheck()) {
            $precheckresults = $rc->get_precheck_results();
            if (is_array($precheckresults) && !empty($precheckresults['errors'])) {
                if (empty($CFG->keeptempdirectoriesonbackup)) {
                    fulldelete($backupbasepath);
                }

                $errorinfo = '';

                foreach ($precheckresults['errors'] as $error) {
                    $errorinfo .= $error;
                }

                if (array_key_exists('warnings', $precheckresults)) {
                    foreach ($precheckresults['warnings'] as $warning) {
                        $errorinfo .= $warning;
                    }
                }

                // throw new moodle_exception('backupprecheckerrors', 'webservice', '', $errorinfo);
            }
        }

        $rc->execute_plan();
        $rc->destroy();
        echo "Step :8 => Restoring Backup for quiz activity is complete.\n";
        
        // Update new course id to Onlinetest.
        echo "Step :9 => Updating course and quiz details to the Online test. \n";

        $quizid = $DB->get_field('quiz', 'id', ['course' => $newcourseid]);
        $onlinetest->courseid = $newcourseid;
        $onlinetest->quizid = $quizid;
        $onlinetest->restored = 1;
        $DB->update_record('local_onlinetests', $onlinetest);
        // \core\notification::success('Online test ' . $onlinetest->name . '(' . $onlinetest->id . ') is restored.');
        echo "Step :10 => Details are updated to onlinetest.\n";
        $count++;
        $log = new stdClass();
        $log->onlinetestid = $onlinetest->id;
        $log->onlinetestname = $onlinetest->name;
        $log->newcourseid = $newcourseid;
        $log->newcoursename = $course->fullname;
        $log->enrolments = $i;
        $log->timecreated = time();
        $DB->insert_record('local_backupnrestorelog',$log);
    }
} 
echo " Total number of onlinetests restored -".$count.".\n";
}
function costcenterwise_onlineexams_datacount($costcenter, $department = false, $subdepartment = false, $l4department = false, $l5department = false) {
    global $USER, $DB, $CFG;
    $params = array();

    $params['costcenterpath'] = '%/' . $costcenter . '/%';
    $countonlinetestsql = "SELECT count(id) FROM {local_onlinetests} WHERE concat('/',open_path,'/') LIKE :costcenterpath ";
    if ($department) {
        $countonlinetestsql .= "  AND concat('/',open_path,'/') LIKE :departmentpath  ";
        $params['departmentpath'] = '%/'.$department.'/%';
    }
    if ($subdepartment) {
        $countonlinetestsql .= " AND concat('/',open_path,'/') LIKE :subdepartmentpath ";
        $params['subdepartmentpath'] = '%/'.$subdepartment.'/%';
    }
    if ($l4department) {
        $countonlinetestsql .= " AND concat('/',open_path,'/') LIKE :l4departmentpath ";
        $params['l4departmentpath'] = '%/'.$l4department.'/%';
    }
    if ($l5department) {
        $countonlinetestsql .= " AND concat('/',open_path,'/') LIKE :l5departmentpath ";
        $params['l5departmentpath'] = '%/'.$l5department.'/%';
    }

    $countonlineexams = $DB->count_records_sql($countonlinetestsql, $params);
    return ['datacount' => $countonlineexams];
}
