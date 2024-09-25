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
class certification_user_completed extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud']        = 'u';
        $this->data['edulevel']    = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_certification';
    }
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {

            $stringHelper=new stdClass();
            $stringHelper->userid=$this->userid;
            $stringHelper->objectid=$this->objectid;
            return get_string("strcerficationusercompleted",'local_certification',$stringHelper);
    }
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventcertificationusercompleted', 'local_certification');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    // public function get_url() {
        
    // }
    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    // protected function get_legacy_logdata() {
       
    // }
    // public static function get_objectid_mapping() {
        
    // }
    // public static function get_other_mapping() {
        
    // }
}
