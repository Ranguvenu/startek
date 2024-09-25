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
class overall extends events {
    
    /* Overall log table */
    protected $table;

    protected $eventslib;

    protected $sitetable = 'block_gm_overall_site';
    
    /**
     * @param $event moodle database
     * @param $order moodle database
     * @param $db moodle database
     **/
    public function __construct($event, $order, $db){
        
        
        parent::__construct($event, $order, $db);

        if(!$this->db->get_manager()->table_exists($this->eventtables->overall)) {
            throw new exception('Table '.$this->eventtables->overall.' doesn\'t exist');
        }
        
        $this->table = $this->eventtables->overall;
        $this->eventslib = $this->get_eventslib();   
    }
    
    /**
     * updates Overall log table
     **/   
    public function execute() {
        return $this->update($this->get_logs(), $this->get_userlevels());
    }
    
    /**
     * @param $record Overall logs of the user
     * @param $userlevel - site level userlevels
     * updates Overall log table
     **/    
    private function update($records, $userlevel){
        $i = 1;
        
        if(empty($records)) {
            $text = 'No '.$this->event_shortname(true).' logs were found';
            echo $this->notify_status($text, 'notifyproblem');
            return false;
        }

        foreach($records as $record){
            
            $data = new stdClass();
            $data->userid = $record->userid;
            $data->courseid = $record->courseid;
            
            $data->level = $userlevel->get_mylevel($record->points);
            
            switch ($this->rank_order()):
                case events::RANK_BY_TIME:
                    // Ranking will be given on course completion date
                    $data->rank = $i; // $record is ordered in desc on points.
                    $i++ ;
                    break;
                case events::RANK_BY_POINTS:
                    // Ranking will be given on points
                    $data->rank = 0;
                    break;
                default:
                    $data->rank = $i;
                    $i++;
                    break;
            endswitch;
            
            $data->timemodified = time();

            $exist = $this->db->get_record($this->table, array('userid'=>$record->userid));            
            if($exist){
                $data->id = $exist->id;
                $data->courseid = $record->courseid .','.$exist->courseid;
                $data->points = $exist->points + $record->points;
                $data->badges = $this->get_mybadges($data->points, $exist);
                $this->db->update_record($this->table, $data);
            } else {
                $data->id = -1;
                $data->courseid = $record->courseid;
                $data->points = $record->points;
                $data->badges = $this->get_mybadges($data->points);
                $data->timecreated = time();
                $data->id = $this->db->insert_record($this->table, $data);
            }
            $this->update_cronstatus($record->logid);
        }
        return true;
    }

    /**
     * set_rank - sets Overall rank
     **/  
    public function set_rank() {
        if($this->rank_order() != events::RANK_BY_POINTS){
            return false;
        }

        $this->eventslib->set_rank($this->table, 'points', $this->get_overall_logs());        
        $text = 'Overall '.$this->event_shortname(true).' were updated.';
        echo $this->notify_status($text, 'success');
        return true;        
    }


    private function get_eventslib() {
        return new \block_gamification\local\events\eventslib($this->db);
    }

    public function site() {
        $events = $this->eventslib->get_active_events();

        if(empty($events)) {
            return false;
        }

        $users = $this->eventslib->get_users();
        if(empty($users)) {
            return false;
        }

        $overall = [];
        foreach($users as $user) {
            $overall[$user->id]['events'] = [];
            $overall[$user->id]['points'] = [];
            foreach($events as $event) {
                $data = $this->db->get_record('block_gm_overall_' . $event->eventcode, ['userid'=>$user->id]);
                if($data) {
                    $overall[$user->id]['points'] = array_merge($overall[$user->id]['points'], array($data->points));
                    $overall[$user->id]['events'] = array_merge($overall[$user->id]['events'], array($event->eventcode));
                }
            }
        }

        foreach($overall as $userid => $data) {
            if(empty($data['events']) || empty($data['points'])) {
                continue ;
            }

            $rec = new stdClass();
            $rec->userid = $userid;
            $rec->events = implode(',', $data['events']);
            $rec->points = array_sum($data['points']);
            $rec->totalpoints = $this->eventslib->total_event_points();
            $rec->level = $this->get_userlevels()->get_mylevel($rec->points);
            $rec->time = time();
            $rec->badges = $this->eventslib->get_latest_badges($userid);

            $exist = $this->db->get_record($this->sitetable, ['userid'=>$userid]);
            if($exist) {
                $rec->id = $exist->id;
                $this->db->update_record($this->sitetable, $rec);
            } else {
                $this->db->insert_record($this->sitetable, $rec);
            }
            
        }

        $this->eventslib->set_rank($this->sitetable, 'points', $this->get_overall_logs($this->sitetable));
    }

}