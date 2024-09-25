<?php

require_once(dirname(__FILE__).'/../../../config.php');
require_once('../lib.php');
require_once('../badgefunctions.php');

$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

$site = new \block_gamification\local\events\eventslib($DB);
$site->update_event_points(); // Updates total points of an events
$events = $site->get_active_events();

if(!empty($events)){
	foreach($events as $event) {
		insertbadgedata($event->eventcode,$event->id);// By Mahesh.
	}
}

insertsitebadges();// By Mahesh.
update_badge_count_ofuser(); //By Mahesh.
echo $OUTPUT->notification("All are updated", 'notifysuccess');

echo $OUTPUT->footer();

