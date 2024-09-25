<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms 
 * @subpackage local_certificates
 */

/**
 * Event observers used in this plugin
 *
 * @package    local_certificates
 *
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_certificates.
 */
class local_certificates_observer {
    /**
     * Triggered when course completed for a user
     *
     * @param \core\event\course_completed $event
     */
    public static function issue_course_certificate(\core\event\course_completed $event) {
        global $DB;

        $crs_certificate = $DB->get_field('course','open_certificateid',array('id'=>$event->courseid));
        
        if(!empty($crs_certificate)){
            self::issue_certificate($event->relateduserid, $event->courseid, 'course', $crs_certificate);
        }
    }


    /**
     * Triggered when classroom completed for a user
     *
     * @param \local_classroom\event\classroom_user_completed $event
     */
    public static function issue_classroom_certificate(\local_classroom\event\classroom_user_completed $event) {
        global $DB;

        $cl_certificate = $DB->get_field('local_classroom','certificateid',array('id'=>$event->objectid));
        
        if(!empty($cl_certificate)){
            self::issue_certificate($event->userid, $event->objectid, 'classroom', $cl_certificate);
        }
    }

    /**
     * Triggered when learningplan completed for a user
     *
     * @param \local_learningplan\event\learningplan_user_completed $event
     */
    public static function issue_learningplan_certificate(\local_learningplan\event\learningplan_user_completed $event) {
        global $DB;

        $lp_certificate = $DB->get_field('local_learningplan','certificateid',array('id'=>$event->objectid));
        
        if(!empty($lp_certificate)){
            self::issue_certificate($event->userid, $event->objectid, 'learningplan', $lp_certificate);
        }
    }


    /**
     * Triggered when program completed for a user
     *
     * @param \local_program\event\program_user_completed $event
     */
    public static function issue_program_certificate(\local_program\event\program_user_completed $event) {
        global $DB;

        $pr_certificate = $DB->get_field('local_program','certificateid',array('id'=>$event->objectid));
        
        if(!empty($pr_certificate)){
            self::issue_certificate($event->userid, $event->objectid, 'program', $pr_certificate);
        }
    }


    /**
     * Triggered when onlinetest completed for a user
     *
     * @param \local_onlinetests\event\onlinetest_completed $event
     */
    public static function issue_onlinetest_certificate(\local_onlinetests\event\onlinetest_completed $event) {
        global $DB;

        $ot_certificate=$DB->get_field('local_onlinetests','certificateid',array('id'=>$event->objectid));
        
        if(!empty($ot_certificate)){
            self::issue_certificate($event->userid, $event->objectid, 'onlinetest', $ot_certificate);
        }
    }

    private static function issue_certificate($userid, $moduleid, $moduletype, $certificateid){
        global $DB, $USER;
        
        $dataobj = new stdClass();

        $dataobj->userid = $userid;
        $dataobj->certificateid = $certificateid;
        $dataobj->moduletype = $moduletype;
        $dataobj->moduleid = $moduleid;
        $dataobj->emailed = 0;
        $dataobj->timecreated = time();
        $dataobj->usercreated = $USER->id;
        $dataobj->timemodified = time();
        $dataobj->usermodified = $USER->id;
        
        $array = array('userid'=>$userid,'moduleid'=>$moduleid,
                        'moduletype'=>$moduletype);
        $exist_recordid = $DB->get_record('local_certificate_issues',$array, 'id');
        if($exist_recordid){
            $dataobj->id = $exist_recordid->id;
            $DB->update_record('local_certificate_issues',$dataobj);
        }else{
            $DB->insert_record('local_certificate_issues',$dataobj);
        }
    }
}


