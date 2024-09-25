<?php
// require_once($CFG->dirroot . '/local/points/locallib.php');
require_once('additionalforms.php');
require_once('locallib.php');
require_once(dirname(__FILE__).'/../../config.php');
$PAGE->set_url('/block/gamification/addevents.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('addevents','block_gamification'));
$PAGE->set_heading(get_string('addevents','block_gamification'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add( get_string('addevents','block_gamification'), new moodle_url('/blocks/gamification/addevents.php'));
$pid = required_param('pid', PARAM_INT); 
// echo $pid;
echo $OUTPUT->header();
$leaderboardurl= new moodle_url('/blocks/gamification/index.php/leaderboard/'.$pid);
$leaderboard = html_writer::link($leaderboardurl,'Back',array('id'=>'backbutton'));
echo $OUTPUT->heading($leaderboard);
	$eventform = new events_form('',array('pid'=>$pid));
	if ($data= $eventform->get_data()) {
		$out = insert_events($data);
	    	redirect(new moodle_url('/blocks/gamification/index.php/leaderboard/'.$data->pid));
	}
	else{
		$eventform->display();
	}
echo $OUTPUT->footer();