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
 * List the tool provided
 *
 * 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace gamificationboards_leaderboard;

defined('MOODLE_INTERNAL') || die();
use core_user;
class course {

    public function get_my_week_rank(){
        global $DB, $USER, $CFG, $COURSE;
        $myrank_sql = 'SELECT id,weeklyrank FROM {block_gm_weekly_course} WHERE week = (SELECT max(week) FROM {block_gm_weekly_course}) AND userid=:userid AND courseid=:courseid';
        $myrank = $DB->get_record_sql($myrank_sql,  array('userid' => $USER->id, 'courseid' => $COURSE->id));
        if($myrank){
            $value = '<span class="footer_cl_levels"><i class="fa fa-star" aria-hidden="true"></i>
            <span class="lvi">'.$myrank->weeklyrank.'</span> </span>';
            return '<div class = "youareat-container">'.get_string('youareat', 'local_gamification', $value).'</div>';
        }else{
            return;
        }
    }

    public function weekly_leaderboard(){
        global $DB, $OUTPUT, $USER, $COURSE;
        // $COURSE->id = 3;
        
        $tablename = 'block_gm_weekly_course';
        $userranks = $DB->get_records_sql("SELECT id, weeklypoints as points, GROUP_CONCAT(DISTINCT userid) AS userid,weeklyrank FROM {{$tablename}} where weeklypoints > 0 AND courseid = {$COURSE->id} AND week = (SELECT max(week) from {{$tablename}}) GROUP BY weeklypoints ORDER BY weeklypoints DESC LIMIT 0, 5");
        $name = 'Rank';
        
            

        
        if(empty($userranks)){
            return '';
        }
        
        $out = '';
        $data = array();
        $rankval = 0;
        
        foreach($userranks as $rank){
            // print_object($rank);
            // $rankval++;
            if($rank){
                
                $users = explode(',', $rank->userid);
                $userslist = array_slice($users , 0, 4);
                $userpictures_array = array();

                foreach ($userslist as $user) {
                    $userpoints = $rank->points;
                    $userinfo = \core_user::get_user($user);
                    $userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo,array('link' => false))."<span class='score'>".$userpoints."</span></span>";
                }
                $userpictures = implode('',$userpictures_array);
                if($rank->weeklyrank > 10){
                    $rank_class = 'last';
                } else {
                    $rank_class = $rank->weeklyrank;
                }
                $out .='<div class="w-full pull-left cdt">
                            <span class="gm_rank_container">
                                
                                <div class="block_gamification-level level-'.$rank_class.' small" aria-label="Rank #'.$rank_class.'">
                                    <span>'.$rank_class.'</span>
                                </div>
                            </span>'
                            .$userpictures.
                        '</div>';
            }else{
                $out .= '';
            }
        }
        return $out;
    }
}