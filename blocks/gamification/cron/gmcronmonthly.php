<?php

require_once(dirname(__FILE__).'/../../../config.php');

$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

// Added by Vinod starts
$rankorder = ['rankbytime'=>1, 'rankbypoints'=>2];

$site = new \block_gamification\local\events\eventslib($DB);
$site->update_event_points(); // Updates total points of an events
$events = $site->get_active_events();

// Executes once in a month (default on 1st day of month)
if(\local_costcenter\lib::get_userdate('d') == \local_costcenter\lib::get_userdate('d', strtotime(0))){
	if(!empty($events)){
		foreach($events as $event) {
			$monthly = new \block_gamification\local\events\monthly($event, $rankorder['rankbypoints'], $DB);
			if($monthly->execute()) {
				// NOTE: This must execute only when $rankorder is rankbypoints
				$monthly->set_rank();
			}
		}
	}

} else {
	$text = 'Data will be updated only once on every 1st day of the month.';
    echo $OUTPUT->notification($text, 'notifyproblem');
}
// Vinod code ends 

echo $OUTPUT->footer();

