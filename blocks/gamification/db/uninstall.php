<?php
defined('MOODLE_INTERNAL') || die();
function xmldb_block_gamification_uninstall(){
	global $DB;
	$DB->delete_records('block_gm_events');
	// $data = new \stdClass();
	// $data->name = 'leaderboard_context';
	// $data->value = 'site';
	// $DB->delete_records('config', array('name' => 'leaderboard_context','value' => 'site'));
	// $DB->delete_records('config', array('name' => 'leaderboard_time','value' => 1));
}