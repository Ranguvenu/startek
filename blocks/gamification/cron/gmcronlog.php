<?php

require_once(dirname(__FILE__).'/../../../config.php');
require_once('../lib.php');

$PAGE->set_pagelayout('admin');

$id = optional_param('id',  0,  PARAM_RAW);//Course id

echo $OUTPUT->header();

$config = $DB->get_field('config',  'value', array('name' => 'leaderboard_context'));

//For site level events based on settings
$timeduration = $DB->get_field('config',  'value', array('name' => 'leaderboard_time'));


switch ($timeduration):
	case '1':
		$choosen = strtotime("-1 week");
		break;
	case '2':
		$choosen = strtotime("-1 month");
		break;
	case '3':
		$choosen = strtotime("-3 months");
		break;
	case '4':
		$choosen = strtotime("-6 months");
		break;
	default:
		$choosen = strtotime("-1 year");
endswitch;

$choosendate = ($choosen) ? strtotime(date('d M Y', $choosen)) : 0;


// block_gamification_sitelevelevnts_allusers();//To Update/Insert {Custom site level points} for {all users}
$events = $DB->get_records_sql('SELECT id, shortname,eventcode FROM {block_gm_events} WHERE active=1');
foreach ($events as $event) {
block_gamification_sitelevelevnts_allusers($event);//To Update/Insert {Custom site level points} for {all users}
}

siteleveleevents_in_gamification();//To Update/Insert {Custom site level points} for {logged in user}

block_gamification_userlevels_allcourses($choosendate);//To get top 5 levels of users based on their each and every single course points

if($config == 'course') { //Courses sum users Wise = course
	block_gamification_userlevels_uservise($choosendate);//To get top 5 levels of users based on their {Courses sum}
	echo "Site level Courses sum";
}elseif ($config == 'site') { //Custom events  = site 
	block_gamification_userlevels(1, $choosendate);//To get top 5 levels of users based on their {Custom events} points
	echo "Site level custom events";
}

echo $OUTPUT->notification("All are updated", 'notifysuccess');

echo $OUTPUT->footer();

