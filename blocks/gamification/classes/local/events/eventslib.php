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
// require_once('events.php');
use stdClass;

// use \block_gamification\local\events;
class eventslib { 

    const SHOW_BADGES = 4;   

    protected $db;

    

    public function __construct($db) {
        $this->db = $db;
    }

    public function get_users() {
        return $this->db->get_records_select('user', 'deleted <> :deleted AND id > :admin', ['deleted'=>1, 'admin'=>2]);
    }

    public function set_rank($table, $points, $record, $otherrank = '') {

        if(!$record) {
            return false;
        }
        $pointsdata = array();
        foreach($record as $data) {
            $pointsdata[$data->{$points}] = $data->{$points};
        }
        krsort($pointsdata);
        $rank = 1;
        foreach($pointsdata as $k=>$data) {
            $pointsdata[$k] = $rank;
            $rank++;
        }
        foreach($record as $data) {
            if($otherrank != '') {
                $data->{$otherrank} = $pointsdata[$data->{$points}];
            } else {
                $data->rank = $pointsdata[$data->{$points}];
            }
            
            $this->db->update_record($table, $data);
        }
        return true;        
    }

    public function get_active_events($limit = null, $eventcode = '') {
        $sql = 'SELECT * 
                FROM {block_gm_events}
                WHERE active = :active';

        $params = ['active' => 1];

        if($eventcode != '') {
            $sql .= ' AND eventcode = :eventcode';
            $params['eventcode'] = $eventcode;
        }

        $sql .= ' ORDER BY id DESC';

        if(!is_null($limit)) {
            $sql .= ' LIMIT '.$limit; 
        }        
        
        if($limit == 1) {
            return $this->db->get_record_sql($sql, $params);
        } else {
            return $this->db->get_records_sql($sql, $params);
        }
    }

    public function total_event_points() {
        $total = $this->db->get_record_sql('SELECT sum(totalpoints) AS totalpoints FROM {block_gm_points} WHERE active = :active', ['active'=>1]);
        return $total ? $total->totalpoints : 0 ;
    }

    public function update_event_points() {
        global $CFG;
        $activeevents = $this->get_active_events(null);
        if(empty($activeevents)) {
            return false;
        }

        foreach($activeevents as $ae) {
            $points = $this->db->get_record('block_gm_points', ['eventid' => $ae->id]);

            $total = 0;
            if($ae->eventcode == 'ce' || $ae->eventcode == 'cc') {
                $total = $this->db->count_records_sql('SELECT count(id) FROM {course} WHERE id <> :id AND visible = :visible', ['id'=>SITEID, 'visible'=>1]);
            } else {
                require_once($CFG->dirroot.'/blocks/gm'.$ae->eventcode.'/lib.php');
                $class = 'gm'.$ae->eventcode;
                $gmevent = new $class();
                if (method_exists($gmevent, 'active_records')) {
                    $total = $gmevent->active_records();
                }
            }
            
            
            $points->totalpoints = $points->points * $total ;

            $this->db->update_record('block_gm_points', $points);
        }

        /*    switch ($ae->eventcode) {
                case self::COURSE_ENROLMENTS :   
                case self::COURSE_COMPLETIONS :
                    $table = 'course';
                    $where = 'id <> :id AND visible = :visible';
                    $params = ['id'=>1, 'visible'=>1];
                    break;
                case self::ILT_COMPLETIONS :  
                    $table = 'local_facetoface';
                    $where = 'visible = :visible';
                    $params = ['visible'=>1];
                    break;
                case self::LEARNINGPLAN_COMPLETIONS :
                    $table = 'local_learningplan';
                    $where = 'visible = :visible';
                    $params = ['visible'=>1];
                    break;
                case self::COMPETENCY_COMPLETIONS :
                    $table = 'competency';
                    $where = '1=1';
                    $params = [];
                    break;
                default :
                    $table = 'course';
                    $where = 'id <> :id AND visible = :visible';
                    $params = ['id'=>1, 'visible'=>1];
                    break;

            }

            $quantity = $this->db->count_records_sql("SELECT count(id) FROM {{$table}} WHERE {$where}", $params);

            $points->totalpoints = $points->points * $quantity ;

            $this->db->update_record('block_gm_points', $points);
        }*/
    }

    function get_latest_badges($userid) {
        $events = $this->get_active_events();

        $latest = [];
        foreach($events as $event) {
            $table = 'block_gm_overall_'.$event->eventcode;
            $eventbadge = $this->db->get_record($table, ['userid'=>$userid]);
            if($eventbadge && !is_null($eventbadge->badges)) {
                $badges = (array) json_decode($eventbadge->badges);
                if(empty($badges)) {
                    return NULL;
                }
                $finalbadge = end($badges);
                $latest[$finalbadge->time] = array_merge(['event'=>$event->eventcode, 'badgeid'=>key($badges)], (array) $finalbadge);
            }
            if(count($latest) >= self::SHOW_BADGES) {
                break;
            }
        }
        krsort($latest);
        return json_encode($latest);
    }
}
