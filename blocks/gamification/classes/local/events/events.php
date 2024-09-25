<?php
namespace block_gamification\local\events;

use exception;
use html_writer;

abstract class events {
    
    /** Constant Set rank by time */
    const RANK_BY_TIME = 1;
    
    /* Constant Set rank by achieved points */
    const RANK_BY_POINTS = 2;
    
    /* Event Name */
    protected $event;
    
    /* tables related to event */
    protected $eventtables;
    
    /** @var int The course ID. */
    protected $courseid = SITEID; 
    
    /** @var int Ranking Order. */
    protected $rankorder;

    protected $renderer;
    
    /**
     * Constructor.
     *
     * @param string $event the event name.
     * @param int $rankorder Order of the rank.
     * @param int $db the DB.
     */
    public function __construct($event, $rankorder, $db) {
        $this->event = $event;
        $this->rankorder = $rankorder;
        $this->db = $db;
        $this->eventtables = (object) $this->events();
        $this->renderer = \block_gamification\di::get('renderer');
    }
    
    /**
     * Get the tables list out od event name.
     *
     * @return array tablenames of events.
     */
    protected function events() {
        return ['log'=>'block_gm_'.$this->event->eventcode.'_log',
                'overall'=>'block_gm_overall_'.$this->event->eventcode,
                'weekly'=>'block_gm_weekly_'.$this->event->eventcode,
                'monthly'=>'block_gm_monthly_'.$this->event->eventcode];
    }

    /**
     * Get the defined levelsinfo
     */    
    protected function get_levelsinfo() {
        $levelsdata = $this->db->get_field('block_gamification_config', 'levelsdata', array('courseid'=>$this->courseid));
        if(!$levelsdata) {
            throw new exception('Levels are not defined');
        }
        return $levelsdata;
    }
    
    /**
     * Get the Levels information of a course.
     */
    protected function get_userlevels() {
        return new \block_gamification\local\gamification\course_user_state_store(
                                    $this->db,
                                    new \block_gamification\local\gamification\algo_levels_info((array)json_decode($this->get_levelsinfo(), true)),
                                    $this->courseid,
                                    new \block_gamification\local\logger\course_user_event_collection_logger($this->db, $this->courseid)
                                );
    }
    
    /**
     * rank order
     */
    protected function rank_order() {
        return $this->rankorder;
    }
    
    /**
     * Get the logs of a user based out of event
     */
    protected function get_logs($lastupdated = false) {
        if(!$this->db->get_manager()->table_exists($this->eventtables->log)) {
            throw new exception('Table '.$this->eventtables->log.' doesn\'t exist');
        }

        $sql = "SELECT userid,
        GROUP_CONCAT(DISTINCT courseid) AS courseid,
        SUM(points) AS points, GROUP_CONCAT(id) AS logid
        
        FROM {{$this->eventtables->log}}";

        // $yesterday = strtotime(date('d m Y', strtotime("-1 days")));
        // if($lastupdated) {
        //     $sql .= " WHERE timecreated > {$yesterday}" ;
        // }

        $sql .= " WHERE cronstatus IS NULL";
         
        $sql .= " GROUP BY userid";

        return $this->db->get_records_sql($sql, array());
    }

    protected function notify_status($text, $status) {
        return html_writer::div($this->renderer->notification_without_close($text, $status), 'block_gamification-dismissable-notice');
    }

    protected function event_shortname($proper = false) {
        if($proper) {
            return ucwords(str_replace('_',' ', $this->event->shortname));
        }
        return $this->event->shortname;
    }


    /**
     * Updates cronstatus field in log tables
     */
    protected function update_cronstatus($logid) {
        $ids = explode(',', $logid);

        if(empty($ids)) {
            return false;
        }
        foreach($ids as $id) {
            if($id) {
                $this->db->set_field($this->eventtables->log, 'cronstatus', 'P', ['id'=>$id]);
            }
        }
        return true;
    }

    /**
     * @return overall_logs of user
     **/
    public function get_overall_logs($overalltable = '') {
        $overall = $overalltable ? $overalltable : $this->eventtables->overall;
        return $this->db->get_records($overall);
        }
    public function get_monthly_logs($monthlytable = '') {
        $monthly = $monthlytable ? $monthlytable : $this->eventtables->monthly;
        return $this->db->get_records($monthly);
    }
    public function get_weekly_logs($weeklytable = '') {
        $weekly = $weeklytable ? $weeklytable : $this->eventtables->weekly;
        return $this->db->get_records($weekly);
    }

    protected function get_mybadges($points, $record = NULL) {

        $badges = $this->db->get_records_select('block_gm_badges', 
                            'badgegroupid = :bgid AND active = :active AND points <= :points',
                            ['bgid'=>$this->event->id, 'active'=>1, 'points'=>$points],
                            'points ASC');

        if(empty($badges)) {
            return NULL; 
        }

        $achieved = [];
        if(!is_null($record)){
            $achieved = (array) json_decode($record->badges);
        }        
            
        foreach($badges as $badge) {

            if(in_array($badge->id, array_keys($achieved))) {
                continue;
            }

            $achieved[$badge->id] = ['badge'=>$badge->badgename, 
                                    'points'=>$badge->points,
                                    'time'=>time()] ;
        }

        return json_encode($achieved);
    }
    
}