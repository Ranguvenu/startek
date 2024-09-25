<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_block_gamification_install(){
	global $CFG,$DB;
	$data = new stdClass();
	// $data->name = 'leaderboard_context';
	// $data->value = 'site';
	// if (empty($DB->get_record('config', array('name'=>'leaderboard_context', 'value'=>'site'))))
	// $DB->insert_record('config',$data);
	// $data->name = 'leaderboard_time';
	// $data->value = '1';	
	// if (empty($DB->get_record('config', array('name'=>'leaderboard_time', 'value'=>1))))
	// $DB->insert_record('config',$data);
	// $data->id='1';
	// $data->event_name = 'login';
	// $data->shortname = 'Login';
	// $data->active = '1';
	// $data->badgeactive = '1';
	// $data->timecreated = time();
	// $data->timemodified = time();
	// $data->usermodified = '2';
	// $DB->insert_record('block_gm_events',$data);
	// $data->id='1';
	// $data->event_name = '1 Course Enrolments';
	// $data->shortname = 'course_enrolments';
	// $data->eventcode = 'ce';
	// $data->active = '1';
	// $data->badgeactive = '1';
	// $data->timecreated = time();
	// $data->timemodified = time();
	// $data->usermodified = '2';
	// if (empty($DB->get_record('config', array('event_name'=>'1 Course enrolments', 'shortname'=>'course_enrolments'))))
	// $DB->insert_record('block_gm_events',$data);
	// $data->id='2';
	$data->event_name = '1 Course Completion';
	$data->event = '/core/event/course_completed';
	$data->shortname = 'course_completions';
	$data->eventcode = 'cc';
	$data->active = '1';
	$data->badgeactive = '1';
	$data->timecreated = time();
	$data->timemodified = time();
	$data->usermodified = '2';
	// if (empty($DB->get_record('block_gm_events', array('event_name'=>'1 Course Completions', 'shortname'=>'course_completions'))))
	$DB->insert_record('block_gm_events',$data);
}
