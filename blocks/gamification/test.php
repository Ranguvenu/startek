<?php
require(__DIR__ . '/../../config.php');
global $DB;
// $record = $DB->get_record('block_gamification_log',array('id' =>1));
// $event = explode('\\',$record->eventname);
// $customevent = $event[3];
// $customevents_arr = array('course_completed' => 'cc','certification_completed' => 'certc', 'classroom_completed' => 'clc', 'learningplan_completion' => 'lpc', 'program_completion' => 'progc');
//     $curr_event = $customevents_arr[$customevent];
//     print_object($event);
//     print_object($record);
//     print_object('$curr_event');
//     $weekstart = get_config('block_gamification', 'weekstart');
//     print_object($weekstart);
//     $type = get_config('block_gamification','type');
//     print_object($type);

$tablename = 'block_gm_overall_cc';
$active_users = $DB->get_records($tablename,array(),'','id,userid');
print_object($active_users);
foreach($active_users as $users){
	$completed_courses_sql = "SELECT id,course FROM {course_completions} WHERE userid=:userid AND timecompleted IS NOT NULL";
	$completed_courses = $DB->get_records_sql_menu($completed_courses_sql,array('userid' => $users->userid));
	$courses = implode(',',$completed_courses);
	print_object($courses);
}