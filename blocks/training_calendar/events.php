<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Ajax page returns events for the selected date
 * 
 * @package block my calendar
 * @copyright  2018 Sreenivas
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/training_calendar/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');
global $CFG, $DB, $PAGE, $USER,$OUTPUT;
$systemcontext = context_system::instance();
$cal_startdate = optional_param('start',0,PARAM_RAW);
$cal_endtdate = optional_param('end',0,PARAM_RAW);
$PAGE->set_context($systemcontext);

// $startdate = strtotime($cal_startdate);
// $enddate = strtotime($cal_endtdate);

$timestamp=get_month_between_two_datetime($cal_startdate, $cal_endtdate);
// $timestamp    = strtotime($monthstartdate);
// $startdatearr = explode('-',date('Y-m-01 00:00:00', $timestamp));
// $enddatearr  = explode('-',date('Y-m-t 23:59:59', $timestamp)); // A leap year!
$startdatearr = explode('-', date('Y-m-01',$timestamp));
$enddatearr = explode('-', date('Y-m-t',$timestamp));
// get local events
// $startdate = make_timestamp($startdatearr[0], $startdatearr[1], $startdatearr[2]);
// $enddate = make_timestamp($enddatearr[0], $enddatearr[1], $enddatearr[2], 23, 59, 59);
$startdate = mktime(0 ,0 , 0, $startdatearr[1], $startdatearr[2], $startdatearr[0]);
$enddate = mktime(23, 59, 59, $enddatearr[1], $enddatearr[2], $enddatearr[0]);
function get_month_between_two_datetime($start, $end){
    // $start = $start=='' ? time() : strtotime($start);
    // $end = $end=='' ? time() : strtotime($end);
	// $startindex = $start=='' ? date("Y-m", time()) : date("Y-m", strtotime($start));
 //    $endindex = $end=='' ? date("Y-m", time()) : date("Y-m", strtotime($end));
	
	// $startindex_arr = explode('-', $startindex);    
	// $endindex_arr = explode('-', $endindex);

	$startindex_arr = explode('-', $start);    
	$endindex_arr = explode('-', $end);

	$endmonthindex = (int)$endindex_arr[1];
	$startmonthindex = (int)$startindex_arr[1];

	$currentyearindex = (int)$startindex_arr[0];
	$endyearindex = (int)$endindex_arr[0];
	if($currentyearindex != $endyearindex){
		$endmonthindex += 12;
	}

    $currentmonthindex = ($endmonthindex - $startmonthindex) > 1 ? $startmonthindex + 1 : $startmonthindex;

    $currentmonthtimestamp = mktime(10, 10, 10, $currentmonthindex, 1, $currentyearindex);
    // print_object($currentmonthtimestamp);
    // for ($i = $start; $i <= $end; $i = get_next_month($i)) {
    //     $months[] = \local_costcenter\lib::get_userdate('Y-m', $i); 
    // }
     
    return $currentmonthtimestamp; 
}
function get_next_month($tstamp) {
    return (strtotime('+1 months', strtotime(date('Y-m-01', $tstamp)))); 
}

$onlinetests_sql = plugins_access_sql($table = 'local_onlinetests');
$evaluations_sql = plugins_access_sql($table = 'local_evaluations');
$classrooms_sql = plugins_access_sql($table = 'local_classroom');
$programs_sql = plugins_access_sql($table = 'local_program');
$certifications_sql = plugins_access_sql($table = 'local_certification');

$local_events_sql = "SELECT * from {event} e where timestart >= :timestart AND timestart <= :timeend AND modulename = :modulename AND instance = :instance AND plugin like '%local_%' AND ";

if (has_capability('local/costcenter:manage_multiorganizations', $systemcontext ) OR is_siteadmin()) {
	$local_events_sql .= " 1=1 ";
} else if (has_capability('local/costcenter:manage_ownorganization',$systemcontext) OR has_capability('local/costcenter:manage_owndepartments',$systemcontext)) {
	$local_events_sql .= commonmodule_plugin_access_sql($onlinetests_sql,$evaluations_sql,$classrooms_sql,$programs_sql,$certifications_sql);
} else {
	$user_access_sql = users_plugin_access_sql();
	$local_events_sql .= commonuser_plugin_access_sql($user_access_sql);
	
}
$local_events = $DB->get_records_sql($local_events_sql, array('timestart'=>$startdate,'timeend'=>$enddate, 'modulename' => '0', 'instance' => 0));

// moodle default events
$enrolledlist = enrol_get_users_courses($USER->id);
foreach ($enrolledlist as $key => $value) {
	$enrolledcourses[] = $value->id;
}
if (is_siteadmin())
$defaultevents = default_calendar_get_events($startdate, $enddate, true, false, true);
else
$defaultevents = default_calendar_get_events($startdate, $enddate, $USER->id, false, $enrolledcourses);

$allevents = array_merge($local_events, $defaultevents);

$context = context_system::instance();
foreach ($allevents as $local_event) {
	$can_access = true;
	// check event access to user
	$can_access = calendar_check_event_access($local_event);
	if ($can_access['enrolled'] OR $can_access['self_enrol']) {
		$local_eventstartdate = \local_costcenter\lib::get_userdate('d m Y H:i', $local_event->timestart);
		if (( is_siteadmin() OR has_capability('local/costcenter:manage_multiorganizations',$context) OR has_capability('local/costcenter:manage_ownorganization',$context) OR has_capability('local/costcenter:manage_owndepartments',$context) )) {
			$can_access['self_enrol'] = false;
			$can_access['enrolled'] = true;
		}

		if($can_access['training_info']->instance){
			$local_event->plugin_instance = $local_event->plugin_itemid = $can_access['training_info']->instance;
			$local_event->local_eventtype = $local_event->eventtype;
			$local_event->plugin = 'mod';
		}

		switch ($local_event->plugin) {
			case 'local_evaluation':
                $pluginurl = $CFG->wwwroot.'/local/evaluation/eval_view.php?id='.$local_event->plugin_instance;
                $popup = false;
            break;
            case 'local_classroom':
                $pluginurl = 'javascript:void(0)';
                $popup = true;
            break;
            case 'local_program':
                 $pluginurl = 'javascript:void(0)';
                 $popup = true;
            break;
            case 'local_certification':
                $pluginurl = 'javascript:void(0)';
                $popup = true;
            break;
            case 'local_onlinetests':
                $pluginurl = $CFG->wwwroot.'/mod/quiz/view.php?id='.$local_event->plugin_itemid;
                $popup = false;
            break;
            case 'mod':
                $pluginurl = $CFG->wwwroot.'/'.$local_event->plugin.'/'.$local_event->modulename.'/view.php?id='.$local_event->plugin_instance;
                $popup = false;
            break;
            case 'user':
                $pluginurl = 'javascript:void(0)';
                $popup = true;
            break;

            default:
                $pluginurl = $CFG->wwwroot.'/';
                $popup = false;
		}

		if ($local_event->eventtype === "open" || $local_event->eventtype === "session_open" ) {
			$string = get_string('openson', 'block_training_calendar');
			$sumstring = $local_event->name.' '.$string.' '.$local_eventstartdate;
		}else if($local_event->eventtype === "close" || $local_event->eventtype === "session_close"){
			$string = get_string('closeson', 'block_training_calendar');
			$sumstring = $local_event->name.' '.$string.' '.$local_eventstartdate;
			continue;
		}else{
			$string = $local_event->eventtype;
			$sumstring = $local_event->name;
		}

		if(strlen($sumstring) > 42){
			$smallstring = substr($sumstring, 0, 42);
			$eventsname = $smallstring.'...';
		}else{
			$eventsname = $sumstring;
		}

		if ($can_access['training_info']->endtime > 0) {
			$eventenddate = true;
		}else{
			$eventenddate = false;
		}

		$singleevent = array(
			'id' => $local_event->id,
			'instance'=>(empty($local_event->plugin_instance))? ((!empty($local_event->instance))? $DB->get_field_sql("SELECT cm.id from {course_modules} cm, {modules} m where m.name LIKE '{$local_event->modulename}' AND m.id = cm.module AND cm.instance = {$local_event->instance}"): (($local_event->courseid)? $local_event->courseid: $local_event->id)): $local_event->plugin_instance,
			'itemid'=>$local_event->plugin_itemid,
			'local_eventname'=>$local_event->name,
			'title'=>$local_event->name,
			'summary'=>strip_tags($local_event->description),
			'local_eventstartdate' =>$local_eventstartdate,
			'local_eventenddate' =>$can_access['training_info']->endtime,
			'start' => \local_costcenter\lib::get_userdate('Y m d', $local_event->timestart),
			'eventtype' => (is_null($local_event->local_eventtype))? $local_event->eventtype: $local_event->local_eventtype,
			'plugin' => (is_null($local_event->plugin))? ((!empty($local_event->instance))? 'mod': $local_event->eventtype): $local_event->plugin,
			'modulename' => $local_event->modulename,		
			'capability'=>1,
			'enrolled'=>$can_access['enrolled'],
			'self_enrol'=>$can_access['self_enrol'],
			'location'=>$can_access['training_info']->location,
			'training_type'=>$can_access['training_info']->type,
			'trainer'=>$can_access['training_info']->trianers,
			'string'=>$string,
			'pluginurl'=>$pluginurl,
			'eventenddate' => $eventenddate,
			'popup' => $popup,
			'eventfullname' => $eventsname,
		);


		$singleevent['content'] = $OUTPUT->render_from_template('block_training_calendar/allevents', $singleevent);
		$events[] = $singleevent;
	} else {
		continue;
	}
}
echo $response = json_encode($events);
