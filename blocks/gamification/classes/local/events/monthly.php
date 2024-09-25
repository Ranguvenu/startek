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
 *
 */
class monthly extends events {
        
    /* monthly log table */
    protected $table;

    protected $eventslib;

    protected $sitetable = 'block_gm_monthly_site';
    
    /**
     * @param $event moodle database
     * @param $order moodle database
     * @param $db moodle database
     **/
    public function __construct($event, $order, $db) {        
        
        parent::__construct($event, $order, $db);
        
        if(!$this->db->get_manager()->table_exists($this->eventtables->monthly)) {
            throw new exception('Table '.$this->eventtables->monthly.' doesn\'t exist');
        }

        $this->table = $this->eventtables->monthly;
        $this->eventslib = $this->get_eventslib();
    }
    
    /**
     * updates monthly log table
     **/    
    public function execute() {
        return $this->update($this->get_overall_logs());
    }
    
    /**
     * @param $record Overall logs of the user
     * updates monthly log table
     **/
    private function update($record) {
        if(empty($record)) {
            $text = 'No Monthly '.$this->event_shortname(true).' logs were found';
            echo $this->notify_status($text, 'notifyproblem');      
            return false;
        }

        //If cron has already run today?
        if(!empty($this->get_latest_month($this->table, 'monthlypoints'))) {
            return false;
        }

        $currentmonth = 1;
        if($exist = $this->db->get_record_sql("SELECT * FROM {{$this->table}} WHERE month = (SELECT MAX(month) FROM {{$this->table}}) LIMIT 1")) {
            $currentmonth = $exist->month + 1;
        }

        foreach($record as $rec){
            $month = new stdClass();
            $month->userid = $rec->userid;
            $month->courseid = $rec->courseid;
            $month->points = $rec->points;
            $month->level = $rec->level;
            $month->rank = $rec->rank;            
            
            $monthlydata = $this->get_monthly_data($rec);
            
            $month->month = $currentmonth;
            $month->monthstart = $monthlydata->monthstart; 
            $month->monthend = $monthlydata->monthend; 
            $month->monthlypoints = $monthlydata->monthlypoints;            
            $month->monthlylevel = $monthlydata->monthlylevel; 
            $month->monthlyrank = $monthlydata->monthlyrank; 
            $month->timemodified = time();

            $this->db->insert_record($this->table, $month);
        }   
        return true;     
    }
    
    /**
     * @param $rec recent month data
     * @return monthly leaderboard data of a user
     **/
    private function get_monthly_data($rec) {
        $sql = "SELECT *
            FROM {{$this->table}}
            WHERE userid = :userid
            ORDER BY month DESC
            LIMIT 1";
        $params = array('userid'=>$rec->userid);
        $available = $this->db->get_record_sql($sql, $params);
        
        $levels = $this->get_userlevels();
                
        $data = new stdClass;
        if($available) {
            $data->month = $available->month + 1;
            $points = $this->db->get_field($this->table, 'sum(points)', array('userid'=>$rec->userid));
            $data->monthlypoints = ($rec->points >= $points) ? ($rec->points - $points) : 0 ;
            $data->monthlylevel = $levels->get_mylevel($data->monthlypoints);
            $data->monthlyrank = 0;
            $data->monthstart = $available->monthend;
            $data->monthend = strtotime(date('d m Y'));
        } else {
            $data->month = 1;
            $data->monthlypoints = $rec->points;
            $data->monthlylevel = $rec->level;
            $data->monthlyrank = $rec->rank;
            $data->monthstart = strtotime(date('d m Y', mktime(0, 0, 0, date("m") - 1, 1, date("Y"))));
            $data->monthend = strtotime(date('d m Y')); //must be month end
        }
        
        return $data;
    }
    
    /**
     * set_rank - sets monthly rank
     **/  
    public function set_rank() {        
        if($this->rank_order() != events::RANK_BY_POINTS){
            return false;
        }
        $this->eventslib->set_rank($this->table, 'monthlypoints', $this->get_latest_month($this->table, 'monthlypoints'), 'monthlypoints');
        $text = 'Monthly '.$this->event_shortname(true).' were updated.';
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

        $monthly = [];
        foreach($users as $user) {
            $monthly[$user->id]['events'] = [];
            $monthly[$user->id]['points'] = [];
            $monthly[$user->id]['monthlypoints'] = [];
            foreach($events as $event) {
                $sql = 'SELECT * FROM {block_gm_monthly_' . $event->eventcode.'} WHERE userid = :userid AND month = (SELECT max(month) FROM {block_gm_monthly_' . $event->eventcode.'})';
                $data = $this->db->get_record_sql($sql, ['userid'=>$user->id]);
                if($data) {
                    $monthly[$user->id]['monthlypoints'] = array_merge($monthly[$user->id]['monthlypoints'], array($data->monthlypoints));
                    $monthly[$user->id]['events'] = array_merge($monthly[$user->id]['events'], array($event->eventcode));
                    $monthly[$user->id]['points'] = array_merge($monthly[$user->id]['points'], array($data->points));
                }
            }
        }
        $currentmonth = 1;
        if($exist = $this->db->get_record_sql("SELECT * FROM {{$this->sitetable}} WHERE month = (SELECT MAX(month) FROM {{$this->sitetable}}) LIMIT 1")) {
            $currentmonth = $exist->month + 1;
        }
        foreach($monthly as $userid => $data) {
            if(empty($data['events']) || empty($data['points'])) {
                continue ;
            }

            $rec = new stdClass();
            $rec->userid = $userid;
            $rec->events = implode(',', $data['events']);
            $rec->points = array_sum($data['points']);
            $rec->rank = 0; 
            $rec->level = $this->get_userlevels()->get_mylevel($rec->points);

            $sql = "SELECT * FROM {{$this->sitetable}} ORDER BY month DESC LIMIT 1";
            $monthexist = $this->db->get_record_sql($sql, []);

            if($monthexist) {
                $rec->monthstart = $monthexist->monthend;
                $rec->monthend = strtotime(date('d m Y'));
            } else {
                $rec->monthstart = strtotime(date('d m Y', mktime(0, 0, 0, date("m") - 1, 1, date("Y"))));
                $rec->monthend = strtotime(date('d m Y'));
            }

            $rec->month = $currentmonth;
            $rec->monthlypoints = array_sum($data['monthlypoints']);   
            $rec->monthlyrank = 0;         
            $rec->monthlylevel = $this->get_userlevels()->get_mylevel($rec->monthlypoints);
            $rec->timemodified = time();

            $this->db->insert_record($this->sitetable, $rec);

        }
        $this->eventslib->set_rank($this->sitetable, 'points', $this->get_latest_month($this->sitetable, 'points'));
        $this->eventslib->set_rank($this->sitetable, 'monthlypoints', $this->get_latest_month($this->sitetable, 'monthlypoints'), 'monthlyrank');
    }
        
    /**
     * @return recent month leaderboard data of a user
     **/
    private function get_latest_month($monthtable, $order) {
        $sql = "SELECT *
            FROM {{$monthtable}}
            WHERE monthend = ".strtotime(date('d m Y'))."
            ORDER BY {$order} DESC";
        // This must excecute on the same day when monthly cron runs.
        // Returns empty value otherwise.
        return $this->db->get_records_sql($sql);
    }
}