<?php
require_once(dirname(__FILE__).'/../../../config.php');
echo $OUTPUT->header();
// Added by Vinod starts
$options = array('Mon'=>'Monday',
	'Tue'=>'Tuesday',
	'Wed'=>'Wednesday',
	'Thu'=>'Thursday',
	'Fri'=>'Friday',
	'Sat'=>'Saturday',
	'Sun'=>'Sunday');

$rankorder = ['rankbytime'=>1, 'rankbypoints'=>2];
$executeon = get_config('block_leaderboard', 'weekstart');

$site = new \block_gamification\local\events\eventslib($DB);
$site->update_event_points(); // Updates total points of an events
$events = $site->get_active_events();

//Executes once in a week
if(\local_costcenter\lib::get_userdate('D') == $executeon) {
	if(!empty($events)){
		foreach($events as $event) {
			$weekly = new \block_gamification\local\events\weekly($event, $rankorder['rankbypoints'], $DB);
			if($weekly->execute()) {
				// NOTE: This must execute only when $rankorder is rankbypoints
				$weekly->set_rank();
			}
		}
	}
	$weekly->site();
} else {
	$text = 'Data will be updated only once on every '.$options[$executeon];
    echo $OUTPUT->notification($text, 'notifyproblem');
}
// Vinod code ends 

echo $OUTPUT->footer();

