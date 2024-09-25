<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
// require_once('lib.php');
// require_once('renderer.php');
    $eventname = optional_param('eventname','',PARAM_TEXT);
    $courseid = optional_param('course','',PARAM_INT);
	global $CFG,$PAGE;
	$PAGE->set_url('/local/gamification/viewallbadges.php');
	$PAGE->set_context(context_system::instance());
	$PAGE->set_pagelayout('standard');
	$PAGE->set_title(get_string('badges-header', 'local_gamification'));
	$PAGE->set_heading(get_string('badges-header', 'local_gamification'));
	$PAGE->navbar->ignore_active();
    $PAGE->navbar->add(get_string('leaderboard', 'local_gamification'), new moodle_url('/blocks/gamification/dashboard.php'/*, array('eventname' => $eventname,'course'=>$courseid,)*/));
	$PAGE->navbar->add(get_string('badges-header', 'local_gamification'));
    $PAGE->requires->css('/blocks/gamification/css/badgetooltip-line.css');
	echo $OUTPUT->header();
    // $eventname = array('cc' => 'course_completions', 'ce' => 'course_enrolments');
    // $eventrequested = optional_param('eventname', 'cc', PARAM_RAW);
    // $shortname = $eventname[$eventrequested];
    $eventinfo = $DB->get_records_select('block_gm_events', 'active = 1 AND badgeactive = 1');
    $renderer = $PAGE->get_renderer('local_gamification');
    $out = '';
    foreach($eventinfo as $data) {
    		// $badgeid = $DB->get_field('block_gm_events',  'id',  array('shortname' => $data->shortname));
            // $badgeid = $data->id;
            $badgeid = $renderer->get_scorebadges($data->eventcode);
            // if($badgeid){
                $out .= $renderer->complete_badge_display($data->shortname,$badgeid,$data->id);
            // }
    }
echo $out;
echo $OUTPUT->footer();