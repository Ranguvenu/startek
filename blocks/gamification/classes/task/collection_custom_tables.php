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
 * Course user event collection log purge task.
 *
 * @package    block_gamification
 *
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\task;
defined('MOODLE_INTERNAL') || die();

use DateTime;
use stdClass;

/**
 * Log purge task class.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collection_custom_tables extends \core\task\scheduled_task {

    protected $weekdays = array(0 => 'Sunday',1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');

    public function get_name() {
        return get_string('taskcollectioncustomtableentry', 'block_gamification');
    }

    public function execute() {
        $db = \block_gamification\di::get('db');
        $config = \block_gamification\di::get('config');
        // print_object($db);
        // print_object($config);
        
        $records = $db->get_records('block_gamification_log',array('cronstatus' => 0));
        if($records){
            foreach($records AS $record){
                $this->insert_records_to_custom_tables($record);
                $statuschanged = new \stdClass();
                $statuschanged->id = $record->id;
                $statuschanged->cronstatus = 1;
                $db->update_record('block_gamification_log',$statuschanged);
            }
            
        }
            // $this->update_badge_content_users();
            // $this->insertsitebadges();
            // $this->update_badge_count_ofuser();
            $this->set_rank_custom_tables();
            
        return;
    }

    protected function insert_records_to_custom_tables($record){
        global $DB,$USER;
        $now = $record->time;
        // print_object('here');
        // site level overall.
        $site_overall = new \stdClass();
        $site_record = $DB->get_record('block_gm_overall_site',array('userid' => $record->userid));
        // print_object($site_record);
        // print_object('$site_record');
        
        if($site_record){
            $site_overall->id = $site_record->id; 
            $site_overall->points = $site_record->points+$record->gamification;
            $site_overall->level = $this->get_level($site_overall->points);
            // $site_overall->totalpoints = $site_record->totalpoints+$record->gamification;
            // print_object('here1');
            $DB->update_record('block_gm_overall_site', $site_overall);
        }else{
            $site_overall->userid = $record->userid;
            $site_overall->points = $record->gamification;
            // $site_overall->totalpoints = $record->gamification;
            $site_overall->level = $this->get_level($site_overall->points);

            $site_overall->time = $now;
            // print_object($site_overall);
            // print_object('here15');
            $DB->insert_record('block_gm_overall_site', $site_overall);
        }
        // print_object('here1');

        
        // monthly overall
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

        $monthlysql = "SELECT id,points,monthlypoints FROM {block_gm_monthly_site} WHERE userid = :userid AND monthstart < :now1 AND monthend > :now2";
        $monthly_data = $DB->get_record_sql($monthlysql, array('userid' => $record->userid, 'now1' => $now, 'now2' => $now));
        $month_record = new \stdClass();
        if($monthly_data){
            $month_record->id = $monthly_data->id;
            $month_record->points = $monthly_data->points+$record->gamification;
            $month_record->monthlypoints = $monthly_data->monthlypoints+$record->gamification;
            $DB->update_record('block_gm_monthly_site', $month_record);
        }else{
            $startDateOfMonth = date("Y-m-01", $now);
            $lastDateOfMonth = date("Y-m-t", $now);
            $month_record->monthstart = strtotime($startDateOfMonth);
            $month_record->monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
            $month_record->points = $record->gamification;
            $month_record->userid = $record->userid;
            $month_record->month = $monthval;
            $month_record->monthlypoints = $record->gamification;
            $DB->insert_record('block_gm_monthly_site',$month_record);
        }
        // print_object('here2');

        // weekly overall
        $maxweek_sql = "SELECT id, week, weekstart,weekend FROM {block_gm_weekly_site} WHERE id > 0 AND week = (SELECT MAX(week) FROM {block_gm_weekly_site}) ORDER BY id DESC ";//LIMIT 1
        $maxweek = $DB->get_record_sql($maxweek_sql);
        if($maxweek){
            if($now > $maxweek->weekstart && $now < $maxweek->weekend){
                $weekval = $maxweek->week;
            }else{
                $weekval = $maxweek->week+1;
            }
            
        }else{
            $weekval = 1;
        }

        $exist_sql = "SELECT id,points,weeklypoints FROM {block_gm_weekly_site} WHERE userid = :userid AND weekstart < :now1 AND weekend > :now2";
        $week_data = $DB->get_record_sql($exist_sql, array('userid' => $record->userid, 'now1' => $now, 'now2' => $now));

        $weekrecord = new \stdClass();

        if($week_data){
            $weekrecord->id = $week_data->id;
            $weekrecord->points = $week_data->points+$record->gamification;
            $weekrecord->weeklypoints = $week_data->weeklypoints+$record->gamification;
            $DB->update_record('block_gm_weekly_site', $weekrecord);
        }else{
            $weekstart = get_config('block_gamification', 'weekstart');
            $weekday = $this->weekdays[$weekstart];
            if(date("w", $now) == $weekstart || date("l", $now) == $weekday){//if curr day is weekstart defined in config.
                $weekstart = strtotime(date("d m Y"), $now);
            }else{
                $weekstart = strtotime("last ".$weekday, $now);
            }
            $weekend = strtotime("next ".$weekday, $now);

            $weekrecord->userid = $record->userid;
            $weekrecord->week = $weekval;
            $weekrecord->weekstart = $weekstart;
            $weekrecord->weekend = $weekend;
            $weekrecord->points = $record->gamification;
            $weekrecord->weeklypoints = $record->gamification;
            // print_object($weekrecord);
            $DB->insert_record('block_gm_weekly_site', $weekrecord);
        }
        // print_object('here3');

        if($record->courseid == 1){
            // print_object('here4');
            $this->insert_custom_event_records_todatabase($record);
        }else{
            $this->insert_course_custom_tables($record);
        }
    }
    protected function insert_custom_event_records_todatabase($record){
        global  $DB;
        // print_object('here5');

        $now = $record->time;
        $event = explode('\\',$record->eventname);

        $customevent = $event[3];
        $customevents_arr = array('course_completed' => 'cc','certification_completed' => 'certc', 'classroom_completed' => 'clc', 'learningplan_completed' => 'lpc', 'program_completed' => 'progc');
        $curr_event = $customevents_arr[$customevent];
        // print_object($customevents_arr);
        // print_object($curr_event);
        // var_dump($curr_event);
        if(!empty($curr_event)){
            $weektable = 'block_gm_weekly_'.$curr_event;
            $monthtable = 'block_gm_monthly_'.$curr_event;
            $overalltable = 'block_gm_overall_'.$curr_event;

            // weekly record check
            $maxweek_sql = "SELECT id, week, weekstart,weekend FROM {{$weektable}} WHERE id > 0 AND week = (SELECT MAX(week) FROM {{$weektable}}) ORDER BY id DESC ";// LIMIT 1
            $maxweek = $DB->get_record_sql($maxweek_sql);

            if($maxweek){
                if($now > $maxweek->weekstart && $now < $maxweek->weekend){
                    $weekval = $maxweek->week;
                }else{
                    $weekval = $maxweek->week+1;
                }
                
            }else{
                $weekval = 1;
            }
            $exist_sql = "SELECT id,points,weeklypoints FROM {{$weektable}} WHERE userid = :userid AND weekstart < :now1 AND weekend > :now2";
            $record_exist = $DB->get_record_sql($exist_sql, array('userid' => $record->userid, 'now1' => $now, 'now2' => $now));
            $weekrecord = new \stdClass();
            if($record_exist){
                $weekrecord->id = $record_exist->id;
                $weekrecord->points = $record_exist->points+$record->gamification;
                $weekrecord->weeklypoints = $record_exist->weeklypoints+$record->gamification;
                $DB->update_record($weektable, $weekrecord);
            }else{
                $weekstart = get_config('block_gamification', 'weekstart');
                $weekday = $this->weekdays[$weekstart];
                if(date("w", $now) == $weekstart || date("l", $now) == $weekday){//if curr day is the weekstart defined in cfg.
                    $weekstart = strtotime(date("d m Y"), $now);
                }else{
                    $weekstart = strtotime("last ".$weekday, $now);
                }
                $weekend = strtotime("next ".$weekday, $now);

                $weekrecord->userid = $record->userid;
                $weekrecord->week = $weekval;
                $weekrecord->weekstart = $weekstart;
                $weekrecord->weekend = $weekend;
                $weekrecord->points = $record->gamification;
                $weekrecord->weeklypoints = $record->gamification;
                $DB->insert_record($weektable, $weekrecord);
            }

            // print_object('here6');

            $maxmonth_sql = "SELECT month,monthstart,monthend FROM {{$monthtable}} WHERE id > 0 AND month=(SELECT MAX(month) FROM {{$monthtable}} ) ORDER BY id DESC ";//LIMIT 1
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
            $monthlysql = "SELECT id,points,monthlypoints FROM {{$monthtable}} WHERE userid = :userid AND monthstart < :now1 AND monthend > :now2";
            $monthly_data = $DB->get_record_sql($monthlysql, array('userid' => $record->userid, 'now1' => $now, 'now2' => $now));

            $month_record = new \stdClass();
            if($monthly_data){
                $month_record->id = $monthly_data->id;
                $month_record->points = $monthly_data->points+$record->gamification;
                $month_record->monthlypoints = $monthly_data->monthlypoints+$record->gamification;
                $DB->update_record($monthtable, $month_record);
            }else{
                $startDateOfMonth = date("Y-m-01", $now);
                $lastDateOfMonth = date("Y-m-t", $now);
                $month_record->monthstart = strtotime($startDateOfMonth);
                $month_record->monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
                $month_record->points = $record->gamification;
                $month_record->userid = $record->userid;
                $month_record->month = $monthval;
                $month_record->monthlypoints = $record->gamification;
                $DB->insert_record($monthtable, $month_record);

            }
            // print_object('here7');

            // overall data
            
            $site_data = $DB->get_record($overalltable,array('userid' => $record->userid));
            $site_record = new \stdClass();
            if($site_data){
                $site_record->id = $site_data->id; 
                $site_record->points = $site_data->points+$record->gamification;
                // $site_record->totalpoints = $site_data->totalpoints+$record->gamification;
                $site_record->timemodified = $now;
                $DB->update_record($overalltable, $site_record);
            }else{
                $site_record->userid = $record->userid;
                $site_record->points = $record->gamification;
                // $site_record->totalpoints = $record->gamification;
                $site_record->timecreated = $now;
                $site_record->timemodified = $now;
                $DB->insert_record($overalltable, $site_record);
            }
            // print_object('here8');
        }else{ //added as we use activities in site level.
            $this->insert_course_custom_tables($record);
        }

    }
    protected function insert_course_custom_tables($record){
        global  $DB;
        $now = $record->time;
        $weektable = 'block_gm_weekly_course';
        $monthtable = 'block_gm_monthly_course';
        $overalltable = 'block_gm_overall_course';

        $maxweek_sql = "SELECT id, week, weekstart,weekend FROM {{$weektable}} WHERE id > 0 AND week = (SELECT MAX(week) FROM {{$weektable}}) ORDER BY id DESC ";//LIMIT 1
        $maxweek = $DB->get_record_sql($maxweek_sql);

        if($maxweek){
            if($now > $maxweek->weekstart && $now < $maxweek->weekend){
                $weekval = $maxweek->week;
            }else{
                $weekval = $maxweek->week+1;
            }
            
        }else{
            $weekval = 1;
        }

        $exist_sql = "SELECT id,points,weeklypoints FROM {{$weektable}} WHERE userid = :userid AND weekstart < :now1 AND weekend > :now2 AND courseid=:courseid";
        $record_exist = $DB->get_record_sql($exist_sql, array('userid' => $record->userid, 'now1' => $now, 'now2' => $now,'courseid' => $record->courseid));
        $weekrecord = new \stdClass();
        if($record_exist){
            $weekrecord->id = $record_exist->id;
            $weekrecord->points = $record_exist->points+$record->gamification;
            $weekrecord->weeklypoints = $record_exist->weeklypoints+$record->gamification;
            $DB->update_record($weektable, $weekrecord);
        }else{
            $weekstart = get_config('block_gamification', 'weekstart');
            $weekday = $this->weekdays[$weekstart];
            if(date("w", $now) == $weekstart || date("l", $now) == $weekday){//if curr day is monday curr day as weekstart
                $weekstart = strtotime(date("d m Y"), $now);
            }else{
                $weekstart = strtotime("last ".$weekday, $now);
            }
            $weekend = strtotime("next ".$weekday, $now);

            $weekrecord->userid = $record->userid;
            $weekrecord->week = $weekval;
            $weekrecord->weekstart = $weekstart;
            $weekrecord->weekend = $weekend;
            $weekrecord->points = $record->gamification;
            $weekrecord->weeklypoints = $record->gamification;
            $weekrecord->courseid = $record->courseid;
            $DB->insert_record($weektable, $weekrecord);
        }

        $maxmonth_sql = "SELECT month,monthstart,monthend FROM {{$monthtable}} WHERE id > 0 AND month=(SELECT MAX(month) FROM {{$monthtable}} ) ORDER BY id DESC";// LIMIT 1
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
        $monthlysql = "SELECT id,points,monthlypoints FROM {{$monthtable}} WHERE userid = :userid AND monthstart < :now1 AND monthend > :now2 AND courseid=:courseid";
        $monthly_data = $DB->get_record_sql($monthlysql, array('userid' => $record->userid, 'now1' => $now, 'now2' => $now,'courseid' => $record->courseid));

        $month_record = new \stdClass();
        if($monthly_data){
            $month_record->id = $monthly_data->id;
            $month_record->points = $monthly_data->points+$record->gamification;
            $month_record->monthlypoints = $monthly_data->monthlypoints+$record->gamification;
            $DB->update_record($monthtable, $month_record);
        }else{
            $startDateOfMonth = date("Y-m-01", $now);
            $lastDateOfMonth = date("Y-m-t", $now);
            $month_record->monthstart = strtotime($startDateOfMonth);
            $month_record->monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
            $month_record->points = $record->gamification;
            $month_record->userid = $record->userid;
            $month_record->month = $monthval;
            $month_record->monthlypoints = $record->gamification;
            $month_record->courseid = $record->courseid;
            $DB->insert_record($monthtable, $month_record);

        }

        // overall data
        
        $site_data = $DB->get_record($overalltable,array('userid' => $record->userid));
        $site_record = new \stdClass();
        if($site_data){
            $site_record->id = $site_data->id; 
            $site_record->points = $site_data->points+$record->gamification;
            // $site_record->totalpoints = $site_data->totalpoints+$record->gamification;
            $site_record->timemodified = $now;
            $DB->update_record($overalltable, $site_record);
        }else{
            $site_record->userid = $record->userid;
            $site_record->points = $record->gamification;
            $site_record->totalpoints = $record->gamification;
            $site_record->courseid = $record->courseid;
            $site_record->timecreated = $now;
            $site_record->timemodified = $now;
            $DB->insert_record($overalltable, $site_record);
        }
    }
    protected function set_rank_custom_tables(){
        global $DB;
        $eventtables = array(); 
        $customevents_arr = array('course_completed' => 'cc','certification_completed' => 'certc', 'classroom_completed' => 'clc', 'learningplan_completed' => 'lpc', 'program_completed' => 'progc','site' => 'site','course' => 'course');
        foreach($customevents_arr as $key => $value){
            $eventtables['weeklycustomtable_'.$key] = 'block_gm_weekly_'.$value;
            $eventtables['monthlycustomtable_'.$key]= 'block_gm_monthly_'.$value;
            $eventtables['overallcustomtable_'.$key]= 'block_gm_overall_'.$value;
            // $eventtables[] = $weektable, $monthtable, $overalltable);
        }
        // print_object($eventtables);
        foreach($eventtables AS $type => $event){
            $explode = explode('_',$type);
            // print_object($explode);
            // print_object()
            $type = $explode[0];
            $this->$type($event);   
        }
    }
    protected function weeklycustomtable($table){
        // print_object($table);
        global $DB;
        $costcenters = $DB->get_records('local_costcenter', array('parentid' => 0), '', 'id,shortname');
        foreach($costcenters AS $costcenter){
            $weeklysql = "SELECT x.* FROM {{$table}} AS x JOIN {user} AS u ON u.id=x.userid WHERE week= (SELECT MAX(week) FROM {{$table}}) AND u.open_costcenterid = {$costcenter->id} ORDER BY weeklypoints DESC";
            // print_object($weeklysql);
            $weeklydata = $DB->get_records_sql($weeklysql);

            $weeklyrank = 0;
            $prevweekpoints = 0;
            foreach($weeklydata as $weekinfo){
                $weekpoints = $weekinfo->weeklypoints;
                $weekupdate = new \stdClass();
                if($weekpoints == $prevweekpoints){
                    $weekupdate->id = $weekinfo->id;
                    $weekupdate->weeklyrank = $weeklyrank;
                    $DB->update_record($table, $weekupdate);
                }else{
                    $weeklyrank++;
                    $weekupdate->id = $weekinfo->id;
                    $weekupdate->weeklyrank = $weeklyrank;
                    $DB->update_record($table, $weekupdate);
                }
                $prevweekpoints = $weekpoints;
            }
        }
    }
    protected function monthlycustomtable($table){
        global $DB;
        // print_object($table);
        $costcenters = $DB->get_records('local_costcenter', array('parentid' => 0), '', 'id,shortname');
        foreach($costcenters AS $costcenter){
            $monthrecord_sql = "SELECT x.* FROM {{$table}} AS x JOIN {user} AS u ON u.id=x.userid WHERE month=(SELECT MAX(month) FROM {{$table}}) AND u.open_costcenterid = {$costcenter->id} ORDER BY monthlypoints DESC";
            $monthrecords = $DB->get_records_sql($monthrecord_sql);

            $monthlyrank = 0;
            $prevmonthpoints = 0;
            foreach($monthrecords as $monthinfo){
                $monthpoints = $monthinfo->monthlypoints;
                $monthupdate = new \stdClass();
                if($monthpoints == $prevmonthpoints){
                    $monthupdate->id = $monthinfo->id;
                    $monthupdate->monthlyrank = $monthlyrank;
                    $DB->update_record($table, $monthupdate);
                }else{
                    $monthlyrank++;
                    $monthupdate->id = $monthinfo->id;
                    $monthupdate->monthlyrank = $monthlyrank;
                    // print_object($monthupdate);
                    $DB->update_record($table, $monthupdate);
                }
                $prevmonthpoints = $monthpoints;
            }
        }
    }
    protected function overallcustomtable($table){
        global $DB;
        // print_object($table);

        // print_object('overallsuccess');
        $costcenters = $DB->get_records('local_costcenter', array('parentid' => 0), '', 'id,shortname');

        foreach($costcenters AS $costcenter){
            $userrecords_sql = "SELECT x.* FROM {{$table}} AS x JOIN {user} AS u ON u.id=x.userid WHERE u.id>0 AND u.open_costcenterid = {$costcenter->id} ORDER BY points DESC";
            $userrecords = $DB->get_records_sql($userrecords_sql);

            $rank = 0;
            $prevpoints = 0;
            foreach($userrecords as $userinfo){
                $points = $userinfo->points;
                $rankupdate = new \stdClass();
                if($points == $prevpoints){
                    $rankupdate->id = $userinfo->id;
                    $rankupdate->rank = $rank;
                    $DB->update_record($table, $rankupdate);
                }else{
                    $rank++;
                    $rankupdate->id = $userinfo->id;
                    $rankupdate->rank = $rank;
                    $DB->update_record($table, $rankupdate); 
                }
                $prevpoints = $points;
            }
        }
    }
    protected function update_badge_content_users(){
        global $DB;
        $active_events = $DB->get_records('block_gm_events',array('active' => 1,'badgeactive' => 1));
            // print_object($active_events);

        foreach($active_events as $event){
            $tablename = 'block_gm_overall_'.$event->eventcode;
            // print_object($tablename);
            // print_object($event);
            $active_users = $DB->get_records($tablename,array(),'','id,userid');
            foreach($active_users as $users){
                switch($event->eventcode){
                    case 'cc':
                        $completed_courses_sql = "SELECT id,course FROM {course_completions} WHERE userid=:userid AND timecompleted IS NOT NULL";
                        $completed_courses = $DB->get_records_sql_menu($completed_courses_sql,array('userid' => $users->userid));
                        $courses = implode(',',$completed_courses);
                        $courseupdated = new \stdClass();
                        $courseupdated->id = $users->id;
                        $courseupdated->courseid = $courses;
                        $DB->update_record($tablename,$courseupdated);
                    break;
                    case 'clc':
                        $completed_classroom_sql = "SELECT id,classroomid FROM {local_classroom_users} WHERE userid=:userid AND completion_status=1";
                        $completed_classes = $DB->get_records_sql_menu($completed_classroom_sql,array('userid' => $users->userid));
                        $classes = implode(',',$completed_classes);
                        $classupdated = new \stdClass();
                        $classupdated->id = $users->id;
                        $classupdated->courseid = $classes;
                        $DB->update_record($tablename,$classupdated);
                    break;
                    case 'progc':
                        $completed_program_sql = "SELECT id,programid FROM {local_program_users} WHERE userid=:userid AND completion_status=1";
                        $completed_programs = $DB->get_records_sql_menu($completed_program_sql,array('userid' => $users->userid));
                        $programs = implode(',',$completed_programs);
                        $programupdated = new \stdClass();
                        $programupdated->id = $users->id;
                        $programupdated->courseid = $programs;
                        $DB->update_record($tablename,$programupdated);
                    break;
                    case 'certc':
                        $completed_certification_sql = "SELECT id,certificationid FROM {local_certification_users} WHERE userid=:userid AND completion_status=1";
                        $completed_certifications = $DB->get_records_sql_menu($completed_certification_sql,array('userid' => $users->userid));
                        $certifications = implode(',',$completed_certifications);
                        $certificationupdated = new \stdClass();
                        $certificationupdated->id = $users->id;
                        $certificationupdated->courseid = $certifications;
                        $DB->update_record($tablename,$certificationupdated);
                    break;
                }
            }
            $this->insertbadgedata($event->eventcode,$event->id);
        }
    }
    protected function insertbadgedata($event,$eventid){
        global $DB;
        $userinfo = $DB->get_records_select('user', 'id > 1');
        foreach($userinfo as $users){
            $badgeinfo = $DB->get_record_select('block_gm_'.$event.'_badges','userid = '.$users->id.' order by time DESC ');//limit 1
            $userpoints = $DB->get_record('block_gm_overall_'.$event, array('userid' => $users->id), 'points');
            if(!$userpoints){
                $userscore = 0;
            }
            else{
                $userscore = $userpoints->points;
            }
            $active = $DB->get_field('block_gm_events', 'badgeactive', array('id' => $eventid));
            if($active){
                $newbadges = $DB->get_records_select('block_gm_badges', ' points <= '.$userscore.' AND badgegroupid = '.$eventid.' and active = 1 and type = "points"');
                foreach($newbadges as $badges){
                    $insertbadge = new \stdClass();
                    $insertbadge->badgeid = $badges->id;
                    $insertbadge->time = time();
                    $insertbadge->userid = $users->id;
                    $out = $DB->record_exists('block_gm_'.$event.'_badges', array('badgeid' => $insertbadge->badgeid, 'userid' => $insertbadge->userid));
                    if(!$out){
                        $DB->insert_record('block_gm_'.$event.'_badges', $insertbadge);
                    }
                }
                //For course badges starts here.
                $newcoursebadges = $DB->get_records_select('block_gm_badges','active =1 and type = "course" AND badgegroupid = '.$eventid);
                foreach($newcoursebadges as $coursebadges){
                    $comp_courses = $DB->get_field('block_gm_overall_'.$event, 'courseid', array('userid' => $users->id));
                    if($comp_courses){
                        $courses = explode(',', $comp_courses);


                        $requiredcourses = explode(',', $coursebadges->course);

                        $flag = 0;
                        foreach($requiredcourses as $key => $value){
                            if(in_array($value, $courses, TRUE)){
                                $flag = 1;
                            }
                            else {
                                $flag = 0;
                                break;
                            }
                        }
                        if($flag){
                            $insertcoursebadge = new \stdClass();
                            $insertcoursebadge->badgeid = $coursebadges->id;
                            $insertcoursebadge->time = time();
                            $insertcoursebadge->userid = $users->id;
                            $out = $DB->record_exists('block_gm_'.$event.'_badges', array('badgeid' => $insertcoursebadge->badgeid, 'userid' => $insertcoursebadge->userid));
                            if(!$out){
                                $DB->insert_record('block_gm_'.$event.'_badges', $insertcoursebadge);
                            }
                        }
                    }
                }
                //For course badges ends here.
            }
        }
    }
    protected function insertsitebadges(){
        global $DB;
        $userinfo = $DB->get_records_select('user', 'id > 1');
        foreach($userinfo as $users){
            $events = $DB->get_records_select('block_gm_events', 'shortname!= "login" AND active = 1');
            foreach($events as $event){
                $badgesinfo = $DB->get_records_select('block_gm_'.$event->eventcode.'_badges', 'userid = '.$users->id.' order by time desc limit 4');
                if($badgesinfo){
                    foreach($badgesinfo as $badgeinfo){
                        $sitedata = new \stdClass();
                        $sitedata->event = $event->eventcode;
                        $sitedata->userid = $badgeinfo->userid;
                        $sitedata->badgeid = $badgeinfo->badgeid;
                        $sitedata->time = time();
                        $badgeexist = $DB->get_record('block_gm_site_badges', array('userid' => $sitedata->userid, 'badgeid' => $sitedata->badgeid));
                        if(!$badgeexist){
                            $DB->insert_record('block_gm_site_badges', $sitedata);
                        }
                        else{
                            $sitedata->id = $badgeexist->id;
                            $DB->update_record('block_gm_site_badges', $sitedata);
                        }
                    }
                }
            }
        }
    }
    protected function update_badge_count_ofuser(){
        global $DB;
        $userinfo = $DB->get_records_select('user', 'id > 1');
        foreach($userinfo as $users){
            $count = 0;
            $events = $DB->get_records_select('block_gm_events', 'shortname != "login" AND active = 1 AND badgeactive = 1');
            foreach($events as $event){
                $badgecount = $DB->count_records('block_gm_'.$event->eventcode.'_badges', array('userid' => $users->id));
                $count += $badgecount; 
            }
            $site = $DB->get_record('block_gm_overall_site',array('userid' => $users->id));
            if($site){
                $updata = new \stdClass();
                $updata->id = $site->id; 
                $updata->userid = $users->id;
                $updata->badgecount = $count;
                $DB->update_record('block_gm_overall_site', $updata);
            }
        }
    }
    protected function get_level($points){
        global $DB;
        $levelsinfo = $DB->get_field('block_gamification_config', 'levelsdata', array('courseid' => 1));
        if($levelsinfo){
            $leveldata = json_decode($levelsinfo);
            $prevlevelpoints = 0;
            foreach($leveldata->gamification as $level => $levelpoints){
                if($points < $levelpoints && $points > $prevlevelpoints){
                    return $level;
                }
                $prevlevelpoints = $levelpoints;
            }
        // }else{

        }
        return 1;

    }
}
