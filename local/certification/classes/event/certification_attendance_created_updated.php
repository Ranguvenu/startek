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
 * @subpackage local_certification
 */

namespace local_certification\event;
use stdclass;
defined('MOODLE_INTERNAL') || die();
class certification_attendance_created_updated extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_certification';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        global $DB;
        if($this->userid){
            $firstname=$DB->get_field_sql("SELECT concat(firstname,' ',lastname) FROM {user} where id=$this->userid");
        }
        if($this->objectid){
            $userid=$DB->get_field_sql("SELECT userid FROM {local_certificatn_attendance} where id=$this->objectid");
        }  
        if($userid&&$this->objectid){
            $certification_users_name=$DB->get_field_sql("SELECT concat(firstname,' ',lastname) FROM {user} where id=$userid");
            $session_id=$DB->get_field_sql("SELECT sessionid FROM {local_certificatn_attendance} where id=$this->objectid");
            $session_name=$DB->get_field_sql("SELECT name FROM {local_certification_sessions} where id=$session_id");
            $certification_id=$DB->get_field_sql("SELECT certificationid FROM {local_certificatn_attendance} where id=$this->objectid");
            $certification_name=$DB->get_field_sql("SELECT name FROM {local_certification} where id=$certification_id");

            $stringHelper=new stdClass();
            $stringHelper->firstname=$this->firstname;
            $stringHelper->userid=$this->userid;
            $stringHelper->certification_users_name=$this->certification_users_name;
            $stringHelper->session_name=$this->session_name;
            $stringHelper->session_id=$this->session_id;
            $stringHelper->certification_name=$this->certification_name;
            $stringHelper->certification_id=$this->certification_id;
            return get_string("strcertificationattendancecreatedobjid",'local_certification',$stringHelper);
        }else{
            $stringHelper=new stdClass();
            $stringHelper->firstname=$this->firstname;
            $stringHelper->userid=$this->userid;
            $stringHelper->objectid=$this->objectid;
            return get_string("strcertificationattendancecreated",'local_certification',$stringHelper);
        }
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcertificationattendance_created_updated', 'local_certification');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        global $DB;
        if($this->objectid){
           $session_id=$DB->get_field_sql("SELECT sessionid FROM {local_certificatn_attendance} where id=$this->objectid");
        }
        if($session_id&&$this->objectid){
             $certification_id=$DB->get_field_sql("SELECT certificationid FROM {local_certificatn_attendance} where id=$this->objectid");
            $url = new \moodle_url('/local/certification/attendance.php',array('ctid'=>$certification_id,'sid'=>$session_id));
        }else{
            $url = new \moodle_url('/local/certification/index.php');
        }
       
        $url->set_anchor('p'.$this->objectid);
        return $url;
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        // The legacy log table expects a relative path to /local/forum/.
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/local/certification/'));

        return array($this->objectid, 'certification', 'add post', $logurl, $this->objectid, $this->contextinstanceid);
    }

    

    public static function get_objectid_mapping() {
        return array('db' => 'local_certification', 'restore' => 'local_certification');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['certificationid'] = array('db' => 'local_certification', 'restore' => 'local_certification');

        return $othermapped;
    }
}
