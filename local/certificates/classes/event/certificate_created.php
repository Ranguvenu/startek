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
namespace local_certificates\event;
use stdClass;
defined('MOODLE_INTERNAL') || die();

class certificate_created extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_certificates';
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        global $DB;
        $firstname=$DB->get_field_sql("SELECT concat(firstname,' ',lastname) 
                                    FROM {user} 
                                    where id=$this->userid");
        $certification_name=$DB->get_field_sql("SELECT name FROM {local_certificate} where id=$this->objectid");
        if($certification_name){

            $stringHelper=new stdClass();
            $stringHelper->firstname=$this->firstname;
            $stringHelper->userid=$this->userid;
            $stringHelper->objectid=$this->objectid;
            $stringHelper->certification_name=$this->certification_name;
            return get_string("certificatenamecreated",'local_certificate_created',$stringHelper);
        }else{
            $stringHelper=new stdClass();
            $stringHelper->firstname=$this->firstname;
            $stringHelper->userid=$this->userid;
            $stringHelper->objectid=$this->objectid;
            return get_string("certificatecreated",'local_certificate_created',$stringHelper);
        }
        
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcertificationcreated', 'local_certificates');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        global $DB;
        $certification_name=$DB->get_field_sql("SELECT name FROM {local_certificate} where id=$this->objectid");
        if($certification_name){
         $url = new \moodle_url('/local/certificates/view.php',array('ctid'=>$this->objectid));
        }else{
          $url = new \moodle_url('/local/certificates/index.php');
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
        $logurl = substr($this->get_url()->out_as_local_url(), strlen('/local/certificates/'));

        return array($this->objectid, 'certificate', 'add post', $logurl, $this->objectid, $this->contextinstanceid);
    }

    

    public static function get_objectid_mapping() {
        return array('db' => 'local_certificate', 'restore' => 'local_certificates');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['certificationid'] = array('db' => 'local_certificate', 'restore' => 'local_certificates');

        return $othermapped;
    }
}
