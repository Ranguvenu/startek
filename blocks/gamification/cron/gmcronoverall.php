<?php

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/blocks/gamification/badgefunctions.php');

echo $OUTPUT->header();
// Added by Vinod starts
$rankorder = ['rankbytime'=>1, 'rankbypoints'=>2];
$site = new \block_gamification\local\events\eventslib($DB);
$site->update_event_points(); // Updates total points of an event
$events = $site->get_active_events();

if(!empty($events)){
	foreach($events as $event) {
		// Executes once in a day
		$overall = new \block_gamification\local\events\overall($event, $rankorder['rankbypoints'], $DB);
		if($overall->execute()) {
			// NOTE: This must execute only when $rankorder is rankbypoints.
			$overall->set_rank();	
		}
	}
}

$overall->site();

// Vinod code ends 
// Added by Mahesh

total_event_points();

echo $OUTPUT->footer();

