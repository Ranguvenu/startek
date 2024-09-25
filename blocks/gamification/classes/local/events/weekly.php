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
 * Block gamification lib.
 *
 * @package    block_gamification
 * @copyright  2017 A Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\events;

defined('MOODLE_INTERNAL') || die();

use exception;
use stdClass;
use html_writer;

/**
 * Weekly leaderboard
 */
class weekly extends events {
        
    /* weekly log table */
    protected $table;

    protected $eventslib;

    protected $sitetable = 'block_gm_weekly_site';
    
    /**
     * @param $event moodle database
     * @param $order moodle database
     * @param $db moodle database
     **/
    public function __construct($event, $order, $db) {        
        
        parent::__construct($event, $order, $db);

        if(!$this->db->get_manager()->table_exists($this->eventtables->weekly)) {
            throw new exception('Table '.$this->eventtables->weekly.' doesn\'t exist');
        }
        
        $this->table = $this->eventtables->weekly;
        $this->eventslib = $this->get_eventslib();       
    }

    /**
     * updates weekly log table
     **/    
    public function execute() {
        return $this->update($this->get_overall_logs());
    }

    /**
     * @param $record Overall logs of the user
     * updates weekly log table
     **/
    private function update($record) {
        if(empty($record)) {
            $text = 'No Weekly '.$this->event_shortname(true).' logs were found';
            echo $this->notify_status($text, 'notifyproblem');            
            return false;
        }

        //If cron has already run today?
        if(!empty($this->get_latest_week($this->table, 'weeklypoints'))) {
            return false;
        }

        $currentweek = 1;
        if($exist = $this->db->get_record_sql("SELECT * FROM {{$this->table}} WHERE week = (SELECT MAX(week) FROM {{$this->table}}) LIMIT 1")) {
            $currentweek = $exist->week + 1;
        }

        foreach($record as $rec){
            $week = new stdClass();
            $week->userid = $rec->userid;
            $week->courseid = $rec->courseid;
            $week->points = $rec->points;
            $week->level = $rec->level;
            $week->rank = $rec->rank;            
            
            $weeklydata = $this->get_weekly_data($rec);
            
            $week->week = $currentweek;
            $week->weekstart = $weeklydata->weekstart; 
            $week->weekend = $weeklydata->weekend; 
            $week->weeklypoints = $weeklydata->weeklypoints;            
            $week->weeklylevel = $weeklydata->weeklylevel; 
            $week->weeklyrank = $weeklydata->weeklyrank; 
            $week->timemodified = time();

            $this->db->insert_record($this->table, $week);
        }      
        return true;  
    }
    
    /**
     * @param $rec recent week data
     * @return weekly leaderboard data of a user
     **/
    private function get_weekly_data($rec) {
        $sql = "SELECT *
            FROM {{$this->table}}
            WHERE userid = :userid
            ORDER BY week DESC
            LIMIT 1";

        //WHERE userid = :userid
        $params = array('userid'=>$rec->userid);
        $available = $this->db->get_record_sql($sql, $params);
        
        $levels = $this->get_userlevels();
        
        
        $data = new stdClass;
        if($available) {
            $data->week = $available->week + 1;
            $points = $this->db->get_field($this->table, 'sum(points)', array('userid'=>$rec->userid));
            $data->weeklypoints = ($rec->points >= $points) ? ($rec->points - $points) : 0 ;
            $data->weeklylevel = $levels->get_mylevel($data->weeklypoints);
            $data->weeklyrank = 0;
            $data->weekstart = $available->weekend;
            $data->weekend = strtotime(date('d m Y'));
        } else {
            $data->week = 1;
            $data->weeklypoints = $rec->points;
            $data->weeklylevel = $rec->level;
            $data->weeklyrank = $rec->rank;
            $data->weekstart = strtotime(date('d m Y', strtotime("-1 week")));
            $data->weekend = strtotime(date('d m Y'));
        }
        
        return $data;
    }

    /**
     * set_rank - sets weekly rank
     **/    
    public function set_rank() {        
        if($this->rank_order() != events::RANK_BY_POINTS){
            return false;
        }
        
        $this->eventslib->set_rank($this->table, 'weeklypoints', $this->get_latest_week($this->table, 'weeklypoints'), 'weeklyrank');

        $text = 'Weekly '.$this->event_shortname(true).' were updated.';
        echo $this->notify_status($text, 'success');

        return true;        
    }
    
    private function get_eventslib() {
        return new \block_gamification\local\events\eventslib($this->db);
    }

    public function site() {

        $users = $this->eventslib->get_users();
        if(empty($users)) {
            return false;
        }

        $events = $this->eventslib->get_active_events();
        if(empty($events)) {
            return false;
        }

        $weekly = [];
        foreach($users as $user) {
            $weekly[$user->id]['events'] = [];
            $weekly[$user->id]['points'] = [];
            $weekly[$user->id]['weeklypoints'] = [];
            foreach($events as $event) {
                $sql = 'SELECT * FROM {block_gm_weekly_' . $event->eventcode.'} WHERE userid = :userid AND week = (SELECT max(week) FROM {block_gm_weekly_' . $event->eventcode.'})';
                $data = $this->db->get_record_sql($sql, ['userid'=>$user->id]);
                if($data) {
                    $weekly[$user->id]['weeklypoints'] = array_merge($weekly[$user->id]['weeklypoints'], array($data->weeklypoints));
                    $weekly[$user->id]['events'] = array_merge($weekly[$user->id]['events'], array($event->eventcode));
                    $weekly[$user->id]['points'] = array_merge($weekly[$user->id]['points'], array($data->points));
                }
            }
        }

        $currentweek = 1;
        if($exist = $this->db->get_record_sql("SELECT * FROM {{$this->sitetable}} WHERE week = (SELECT MAX(week) FROM {{$this->sitetable}}) LIMIT 1")) {
            $currentweek = $exist->week + 1;
        }

        foreach($weekly as $userid => $data) {
            if(empty($data['events']) || empty($data['points'])) {
                continue ;
            }

            $rec = new stdClass();
            $rec->userid = $userid;
            $rec->events = implode(',', $data['events']);
            $rec->points = array_sum($data['points']);
            $rec->rank = 0; 
            $rec->level = $this->get_userlevels()->get_mylevel($rec->points);
            $rec->week = $currentweek;

            $sql = "SELECT * FROM {{$this->sitetable}} ORDER BY week DESC LIMIT 1";
            $weekexist = $this->db->get_record_sql($sql, []);

            if($weekexist) {
                $rec->weekstart = $weekexist->weekend; 
                $rec->weekend = strtotime(date('d m Y'));
            } else {
                $rec->weekstart = strtotime(date('d m Y', strtotime("-1 week")));
                $rec->weekend = strtotime(date('d m Y'));
            }


            $rec->weeklypoints = array_sum($data['weeklypoints']);   
            $rec->weeklyrank = 0;         
            $rec->weeklylevel = $this->get_userlevels()->get_mylevel($rec->weeklypoints);
            $rec->timemodified = time();

            $this->db->insert_record($this->sitetable, $rec);

        }
        $this->eventslib->set_rank($this->sitetable, 'points', $this->get_latest_week($this->sitetable, 'points'));
        $this->eventslib->set_rank($this->sitetable, 'weeklypoints', $this->get_latest_week($this->sitetable, 'weeklypoints'), 'weeklyrank');
    }
        
    /**
     * @return recent week leaderboard data of a user
     **/
    private function get_latest_week($weektable, $order) {
        $sql = "SELECT *
            FROM {{$weektable}}
            WHERE weekend = ".strtotime(date('d m Y'))."
            ORDER BY {$order} DESC";
        // This must excecute on the same day when weekly cron runs.
        // Returns empty value otherwise.
        return $this->db->get_records_sql($sql);
    }
}