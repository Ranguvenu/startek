<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once('badgefunctions.php');
$id = required_param('id',PARAM_INT);
$pid = required_param('pid',PARAM_INT);
// function insertbadgedata($event,$eventid){
//     global $DB;
//     $userinfo = $DB->get_records_select('user', 'id > 1');
//     foreach($userinfo as $users){
//         $badgeinfo = $DB->get_record_select('block_gm_'.$event.'_badges','userid = '.$users->id.' order by time DESC limit 1');
//         $userpoints = $DB->get_record('block_gm_overall_'.$event, array('userid' => $users->id), 'points');
//         if(!$userpoints){
//         	$userscore = 0;
//         }
//         else{
//         	$userscore = $userpoints->points;
//         }
//             $newbadges = $DB->get_records_select('block_gm_badges', ' points <= '.$userscore.' AND badgegroupid = '.$eventid);
//             foreach($newbadges as $badges){
//                 $insertbadge = new stdClass();
//                 $insertbadge->badgeid = $badges->id;
//                 $insertbadge->time = time();
//                 $insertbadge->userid = $users->id;
//                 $out = $DB->record_exists('block_gm_'.$event.'_badges', array('badgeid' => $insertbadge->badgeid, 'userid' => $insertbadge->userid));
//                 if(!$out){
//                 $DB->insert_record('block_gm_'.$event.'_badges', $insertbadge);
//             }
//         }
//     }
// }
// function insertsitebadges(){
//     global $DB;
//     $userinfo = $DB->get_records_select('user', 'id > 1');
//     foreach($userinfo as $users){
//         $events = $DB->get_records_select('block_gm_events', 'shortname!= "login"');
//         foreach($events as $event){
//             $badgeinfo = $DB->get_record_select('block_gm_'.$event->eventcode.'_badges', 'userid = '.$users->id.' order by time desc limit 1');
//             if($badgeinfo){
//                 $sitedata = new stdClass();
//                 $sitedata->event = $event->eventcode;
//                 $sitedata->userid = $badgeinfo->userid;
//                 $sitedata->badgeid = $badgeinfo->badgeid;
//                 $sitedata->time = time();
//                 $badgeexist = $DB->get_record('block_gm_site_badges', array('badgeid' => $sitedata->badgeid, 'userid' => $sitedata->userid, 'event' => $sitedata->event));
//                 if(!$badgeexist){
//                     $DB->insert_record('block_gm_site_badges', $sitedata);
//                 }
//                 else{
//                     $sitedata->id = $badgeexist->id;
//                     $DB->update_record('block_gm_site_badges', $sitedata);
//                 }
//             }
//         }
//     }
// }
global $DB;
$eventid = $DB->get_field('block_gm_badges', 'badgegroupid',  array('id' => $id));
$event = $DB->get_record('block_gm_events', array('id' => $eventid));
$out = $DB->delete_records('block_gm_'.$event->eventcode.'_badges',  array('badgeid' => $id));
$DB->delete_records('block_gm_site_badges', array('badgeid' => $id));
// For updating the block_gm_site_badges.
insertsitebadges();
update_badge_count_ofuser();
    // updation complete.
$DB->delete_records('block_gm_badges',  array('id' => $id));
redirect(new moodle_url('/blocks/gamification/index.php/visuals/'.$pid));