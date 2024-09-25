<?php
require(__DIR__ . '/../../config.php');
global $PAGE,$USER,$CFG,$COURSE;

$type = required_param('type', PARAM_RAW);
$courseid = optional_param('course', 1, PARAM_INT);
// print_object($courseid);
$types = ['overall' => get_string('overall','block_gamification'), 'monthly' => get_string('monthly','block_gamification'), 'weekly' => get_string('weekly','block_gamification')];
require_login();
if($courseid == 1) {
	$context = context_system::instance();
	require_login();
} else {
	if(!$course = get_course($courseid)){
		print_error('courseidnotvalid');
	}
	$context = context_course::instance($courseid);
	$PAGE->set_course($course);
	require_login($course);
}
// $PAGE->set_title(get_string('gamification_dashboard', 'block_gamification'));
if($courseid == 1){
	$heading =  get_string('leaderboard', 'block_gamification');
}else{
	$coursename = $DB->get_field('course','fullname',array('id' => $courseid));
	$heading = $coursename;
	$PAGE->navbar->add($coursename, new moodle_url('/course/view.php', array('id' => $courseid)));
	$systemcontext = context_system::instance();
	if(!(is_enrolled($context) || is_siteadmin() || has_capability('local/costcenter:manage',$systemcontext))){
		// throw new Exception("you cannot enroll to this course");

	redirect($CFG->wwwroot.'/blocks/gamification/error.php');
	
	}
}
$PAGE->set_url('/blocks/gamification/detailed_leaderboard.php', array('type' => $type, 'course' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$heading = get_string('detailed_leaderboard', 'block_gamification', $types[$type]);
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
echo $OUTPUT->header();
	$leaderboard = new \block_gamification\output\leaderboard();
	$now = time();
	if($type == 'weekly'){
		$weekstart = get_config('block_gamification', 'weekstart');
        $weekdays = array(0 => 'Sunday',1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');
        $weekday = $weekdays[$weekstart];
        $startdate = strtotime("last ".$weekday, $now);
        $enddate = strtotime("next ".$weekday, $now);	
	}elseif($type == 'monthly'){
		$startDateOfMonth = date("Y-m-01", $now);
        $lastDateOfMonth = date("Y-m-t", $now);
        $startdate = strtotime($startDateOfMonth);
        $enddate = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
	}else{
		$startdate = $enddate = 0;
	}
	$filterparams = $leaderboard->leaderboard_data_display($type, $courseid, $startdate, $enddate, true);
	echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
	echo $leaderboard->leaderboard_data_display($type, $courseid, $startdate, $enddate);
echo $OUTPUT->footer();