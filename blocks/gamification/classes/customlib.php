<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * custom lib
 * @package    block_gamification
 * @copyright  2018 Maheshchandra Nerella <maheshchandra@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_gamification;
class customlib {
	public static function block_gamification_get_level_coins($coins, $costcenterid){
		$db = \block_gamification\di::get('db');
		$currentlevel = 1;
		$basevalue = 0;
		$levelsconfig = $db->get_field('block_gamification_config', 'levelsdata', array('courseid' => 1, 'costcenterid' => $costcenterid));
        if(!$levelsconfig) {
            return $currentlevel;
        }
		$levels = json_decode($levelsconfig);

		foreach((array)$levels->gamification AS $key => $value){
			if($value > $coins){
				break;
			}else if($basevalue > $coins AND $value <= $coins){
				$currentlevel = $key;
				break;
			}else if($value <= $coins){
                $currentlevel = $key;
            // }else{
            }
            $basevalue = $value;
			continue;
		}
		return $currentlevel;
	}
	public static function insert_badge_to_user($badgeid, $userid, $type){
    	$db = \block_gamification\di::get('db');
        if($type != 'peer_recog' && $db->record_exists('block_gm_site_badges', array('userid' => $userid, 'badgeid' => $badgeid))){
            return;
        }
        $record = new \stdClass();
        $record->badgeid = $badgeid;
        $record->userid = $userid;
        $record->event = $type;
        $record->time = time();
    	$db->insert_record('block_gm_site_badges', $record);
    	$badgecountsql = "SELECT count(id) FROM {block_gm_site_badges} WHERE userid=:userid ";
    	$badgecount = $db->count_records_sql($badgecountsql, array('userid' => $record->userid));
    	$db->set_field('block_gm_overall_site', 'badgecount', $badgecount, array('userid' => $record->userid));
        // $messagedata = new \stdClass();
        // $messagedata->userto = $userid;
        // $messagedata->userfrom = \core_user::get_noreply_user()->id;
        // $messagedata->userfrom = \core_user::get_support_user()->id;
    }
    public static function course_completion_badge($courseid, $userid){
    	$db = \block_gamification\di::get('db');
        $userinfo = \core_user::get_user($userid);
    	$badgeid = $db->get_field('block_gm_badges', 'id', array('type' => 'course_completions', 'courses' => $courseid, 'costcenterid' => $userinfo->open_costcenterid));
    	return $badgeid;
    }
    public static function save_badge_data($data) {
        global $DB, $USER;
        $data->active = 1;
        $data->usercreated = $USER->id;
        $data->timecreated = time();
        $systemcontext= \context_system::instance();
        file_save_draft_area_files($data->badgeimg,  $systemcontext->id, 'block_gamification', 'badges', $data->badgeimg,  array());
        if($data->id){
            $id = $DB->update_record('block_gm_badges', $data);
        }else{
            $id = $DB->insert_record('block_gm_badges', $data);
        }
        return $id;
    }
    public static function save_customised_level_data($data){
        global $DB, $USER;
        $data = (object)$data;
        $newdata = [
            'usealgo' => 0,
            'base' => 0,
            'coef' => 0,
            'gamification' => [
                '1' => 0
            ],
            'desc' => [
                '1' => ''
            ]
        ];
        for ($i = 2; $i <= $data->levels; $i++) {
            $newdata['gamification'][$i] = $data->{'lvlgamification_' . $i};
            $newdata['desc'][$i] = $data->{'lvldesc_' . $i};
        }
        $levelsdata = json_encode($newdata);
        $data->levelsdata = $levelsdata;
        $record_id = $DB->get_field('block_gamification_config', 'id', array('courseid' => 1, 'costcenterid' => $data->costcenterid));
        if($record_id){
            $data->id = $record_id;
            $id = $DB->update_record('block_gamification_config', $data);
        }else{
            // $newrecord = new \stdClass();
            // $newrecord->courseid = 1;
            // $newrecord->levelsdata = $levelsdata;
            // $newrecord->costcenterid = $data->costcenterid;
            // $newrecord->levels = $data->levels;
            // $newrecord->levels = $data->levels;
            $data->courseid = 1;
            $id = $DB->insert_record('block_gamification_config', $data);
        }
        return $id;
    }
    public static function get_level_completion_percentage($points, $costcenterid){
        global $DB;
        $levelsconfig = $DB->get_field('block_gamification_config', 'levelsdata', array('courseid' => 1, 'costcenterid' => $costcenterid));
        $levelsdata = (array)json_decode($levelsconfig, true);
        $levelgamification = (array)$levelsdata['gamification'];
        $prevlvlgamification = 0;
        foreach($levelgamification AS $key => $value){
            if($value > $points && $points >= $prevlvlgamification){
                $currentlevel = $key;
                $currentlvlgamification = $value;
                break;
            }
            $prevlvlgamification = $value;
        }
        $difference = $currentlvlgamification-$prevlvlgamification;
        $percentage = round((($points-$prevlvlgamification)/$difference)*100);
        $return = new \stdClass();
        $return->percentage = $percentage;
        $return->currentlvlgamification = $currentlvlgamification;
        $return->prevlvlgamification = $prevlvlgamification;
        $return->currentlevel = $currentlevel;
        return $return;
    }
    public static function validate_rule_existance($record){
        $ruledata = json_decode($record->ruledata);
        if(isset($ruledata->rules))
            return true;
        else
            return false;
    }
    public static function get_teammanager_teamleadinfo($userid){

    }
    public static function getstringhelpers($costcenterid= null){
        // global $DB;
        // $costcenters = $DB->get_records_menu('local_costcenter', array('parentid' => 0), '', 'id,fullname');
        // $i=0;
        // foreach ($costcenters as $key => $value) {
        //     $indexarr[$i] = $key;
        //     $i++; 
        // }
        // $staticvalues = array(
        //     $indexarr[0] => array('coinstr' => 'Coins', 'levelsstr' => 'Levels', 'ranksstr' => 'Ranks', 'coin_str' => 'Coin', 'level_str' => 'Level', 'rank_str' => 'Rank'), 
        //     $indexarr[1] => array('coinstr' => 'Goals', 'levelsstr' => 'Stages', 'ranksstr' => 'Position', 'coin_str' => 'Goal', 'level_str' => 'Stage', 'rank_str' => 'Position'), 
        //     $indexarr[3] => array('coinstr' => 'Runs', 'levelsstr' => 'Status', 'ranksstr' => 'Rankings', 'coin_str' => 'Run', 'level_str' => 'Status', 'rank_str' => 'Ranking'));
        // if(isset($staticvalues[$costcenterid])){
        //     return (object) $staticvalues[$costcenterid];
        // }else{
            $returnstr = new \stdClass();
            $returnstr->coinstr = 'Coins';
            $returnstr->levelsstr = 'Levels';
            $returnstr->ranksstr = 'Ranks';
            $returnstr->coin_str = 'Coin';
            $returnstr->level_str = 'Level';
            $returnstr->rank_str = 'Rank';
            return $returnstr;
        // }
    }
    public static function update_aggregationtables($userid, $points, $courseid){
        global $DB;
        $now = time();
        $weekdays = array(0 => 'Sunday',1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');
        $maxweek_sql = "SELECT id, week, weekstart,weekend FROM {block_gm_weekly_site} WHERE id > 0 AND week = (SELECT MAX(week) FROM {block_gm_weekly_site}) ORDER BY id DESC ";//LIMIT 1
        $maxweek = $DB->get_record_sql($maxweek_sql);
        if($maxweek){
            if($now > $maxweek->weekstart && $now < $maxweek->weekend){
                $weekval = $maxweek->week;
                $weekstart = $maxweek->weekstart;
                $weekend = $maxweek->weekend;
            }else{
                $weekval = $maxweek->week+1;
            }
            
        }else{
            $weekval = 1;
        }

        $exist_sql = "SELECT id,points,weeklypoints FROM {block_gm_weekly_site} WHERE userid = :userid AND weekstart < :now1 AND weekend > :now2";
        $week_data = $DB->get_record_sql($exist_sql, array('userid' => $userid, 'now1' => $now, 'now2' => $now));

        $weekrecord = new \stdClass();

        if($week_data){
            $weekrecord->id = $week_data->id;
            $weekrecord->points = $week_data->points+$points;
            $weekrecord->weeklypoints = $week_data->weeklypoints+$points;
            $DB->update_record('block_gm_weekly_site', $weekrecord);
        }else{
            $weekstart = get_config('block_gamification', 'weekstart');
            $weekday = $weekdays[$weekstart];
            if(date("w", $now) == $weekstart || date("l", $now) == $weekday){//if curr day is weekstart defined in config.
                $weekstart = strtotime(date("d m Y"), $now);
            }else{
                $weekstart = strtotime("last ".$weekday, $now);
            }
            $weekend = strtotime("next ".$weekday, $now);
            $weekrecord->userid = $userid;
            $weekrecord->week = $weekval;
            $weekrecord->weekstart = $weekstart;
            $weekrecord->weekend = $weekend;
            $weekrecord->points = $points;
            $weekrecord->weeklypoints = $points;
            // print_object($weekrecord);
            $DB->insert_record('block_gm_weekly_site', $weekrecord);
        }
        $courseexist_sql = "SELECT id,points,weeklypoints FROM {block_gm_weekly_course} WHERE userid = :userid AND weekstart < :now1 AND weekend > :now2 AND courseid = :courseid";
        $courseweek_data = $DB->get_record_sql($courseexist_sql, array('userid' => $userid, 'now1' => $now, 'now2' => $now, 'courseid' => $courseid));
        $courseweekrecord = new \stdClass();
        // print_object($courseweek_data);
        if($courseweek_data){
            $courseweekrecord->id = $courseweek_data->id;
            $courseweekrecord->points = $courseweek_data->points+$points;
            $courseweekrecord->weeklypoints = $courseweek_data->weeklypoints+$points;
            $DB->update_record('block_gm_weekly_course', $courseweekrecord);
        }else{
            $courseweekrecord->userid = $userid;
            $courseweekrecord->courseid = $courseid;
            $courseweekrecord->week = $weekval;
            $courseweekrecord->weekstart = $weekstart;
            $courseweekrecord->weekend = $weekend;
            $courseweekrecord->points = $points;
            $courseweekrecord->weeklypoints = $points;
            // print_object($courseweekrecord);
            $DB->insert_record('block_gm_weekly_course', $courseweekrecord);
        }

        $maxmonth_sql = "SELECT month,monthstart,monthend FROM {block_gm_monthly_site} WHERE id > 0 AND month=(SELECT MAX(month) FROM {block_gm_monthly_site} ) ORDER BY id DESC ";//LIMIT 1
        $maxmonth = $DB->get_record_sql($maxmonth_sql);
        // setting monthval
        if($maxmonth){
            if($now > $maxmonth->monthstart && $now < $maxmonth->monthend){
                $monthval = $maxmonth->month;
            }else{
                $monthval = $maxmonth->month+1;
            }
        }else{
            $monthval = 1;
        }

        $startDateOfMonth = date("Y-m-01", $now);
        $lastDateOfMonth = date("Y-m-t", $now);

        $monthlysql = "SELECT id,points,monthlypoints FROM {block_gm_monthly_site} WHERE userid = :userid AND monthstart < :now1 AND monthend > :now2";
        $monthly_data = $DB->get_record_sql($monthlysql, array('userid' => $userid, 'now1' => $now, 'now2' => $now));
        $month_record = new \stdClass();
        if($monthly_data){
            $month_record->id = $monthly_data->id;
            $month_record->points = $monthly_data->points+$points;
            $month_record->monthlypoints = $monthly_data->monthlypoints+$points;
            $DB->update_record('block_gm_monthly_site', $month_record);
        }else{
            $month_record->monthstart = strtotime($startDateOfMonth);
            $month_record->monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
            $month_record->points = $points;
            $month_record->userid = $userid;
            $month_record->month = $monthval;
            $month_record->monthlypoints = $points;
            $DB->insert_record('block_gm_monthly_site',$month_record);
        }

        $coursemonthlysql = "SELECT id,points,monthlypoints FROM {block_gm_monthly_course} WHERE userid = :userid AND monthstart < :now1 AND monthend > :now2 AND courseid =:courseid";
        $coursemonthly_data = $DB->get_record_sql($coursemonthlysql, array('userid' => $userid, 'now1' => $now, 'now2' => $now, 'courseid' => $courseid));
        $coursemonth_record = new \stdClass();
        if($coursemonthly_data){
            $coursemonth_record->id = $coursemonthly_data->id;
            $coursemonth_record->points = $coursemonthly_data->points+$points;
            $coursemonth_record->monthlypoints = $coursemonthly_data->monthlypoints+$points;
            $DB->update_record('block_gm_monthly_course', $coursemonth_record);
        }else{
            $coursemonth_record->monthstart = strtotime($startDateOfMonth);
            $coursemonth_record->monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
            $coursemonth_record->points = $points;
            $coursemonth_record->userid = $userid;
            $coursemonth_record->courseid = $courseid;
            $coursemonth_record->month = $monthval;
            $coursemonth_record->monthlypoints = $points;
            $DB->insert_record('block_gm_monthly_course',$coursemonth_record);
        }

        $site_record = $DB->get_record('block_gm_overall_site',array('userid' => $userid), 'id,points');
        $site_overall = new \stdClass();
        if($site_record){
            $site_overall->id = $site_record->id; 
            $site_overall->points = $site_record->points+$points;
            $site_overall->time = $now;
            $DB->update_record('block_gm_overall_site', $site_overall);
        }else{
            $site_overall->userid = $userid;
            $site_overall->points = $points;
            $site_overall->time = $now;
            $DB->insert_record('block_gm_overall_site', $site_overall);
        }

        $coursesite_record = $DB->get_record('block_gm_overall_course',array('userid' => $userid, 'courseid' => $courseid), 'id,points');
        $course_overall = new \stdClass();
        if($coursesite_record){
            $course_overall->id = $coursesite_record->id; 
            $course_overall->points = $coursesite_record->points+$points;
            $course_overall->time = $now;
            $DB->update_record('block_gm_overall_course', $course_overall);
        }else{
            $course_overall->userid = $userid;
            $course_overall->courseid = $courseid;
            $course_overall->points = $points;
            $course_overall->time = $now;
            $DB->insert_record('block_gm_overall_course', $course_overall);
        }
        return array($weekval, $monthval);

    }
    public static function get_activecoursesto_costcenter($costcenterid){
        global $DB;
        $activecourses_sql = "SELECT oc.courseid FROM {block_gm_overall_course} as oc 
            JOIN {user} AS u ON u.id=oc.userid WHERE u.open_costcenterid={$costcenterid} GROUP BY oc.courseid ";
        $activecourses = $DB->get_records_sql($activecourses_sql);
        return $activecourses;
    }
}