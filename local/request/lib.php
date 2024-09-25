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
 * @package BizLMS
 * @subpackage local_request
 */

if(file_exists($CFG->dirroot.'/local/costcenter/lib.php')){
	require_once($CFG->dirroot.'/local/costcenter/lib.php');
}
require_once($CFG->libdir.'/adminlib.php');
defined('MOODLE_INTERNAL') || die();
function request_filter($mform){
    global $DB,$USER;
    
    $systemcontext =(new \local_request\lib\accesslib())::get_module_context();
if ((has_capability('local/request:approverecord', (new \local_request\lib\accesslib())::get_module_context()) || is_siteadmin())) {
        $requestlist = $DB->get_records_sql_menu("SELECT distinct(compname), id FROM {local_request_records}");
        $requestlist = array_flip($requestlist);

        
        $customrequestlist = array();
        $trainer_user = ((has_capability('local/classroom:manageclassroom',$systemcontext)||
                has_capability('local/certification:managecertification',$systemcontext)) && !is_siteadmin());
        foreach($requestlist as $key => $value){
        	if($trainer_user && ($value == 'elearning' || $value == 'learningplan')){
                continue;    
        	}

        	$customrequestlist[$value] = get_string($value, 'local_request');
        }
        $requestlist = $customrequestlist; 
    }
    $select = $mform->addElement('autocomplete', 'request', '', $requestlist, array('placeholder' => get_string('compname', 'local_request')));
    $mform->setType('request', PARAM_RAW);
    $select->setMultiple(true);
         
}
function sorting_filter($mform){
	global $DB, $USER;
    $systemcontext =(new \local_request\lib\accesslib())::get_module_context();
	$sortinglist = array(false => get_string('firstrequestedfirst', 'local_request'), true => get_string('latestfirst', 'local_request'));
	$select = $mform->addElement('autocomplete', 'sorting', '', $sortinglist, array('placeholder' => get_string('sorting', 'local_request')));
    $mform->setType('request', PARAM_INT);
}
function requeststatus_filter($mform){
    global $DB, $USER;
    $systemcontext =(new \local_request\lib\accesslib())::get_module_context();
    

    $statuslist = $DB->get_records_sql_menu("SELECT distinct(status), id FROM {local_request_records}");
         $statuslist = array_flip($statuslist);
         $customrequestlist = array();
         // $trainer_user = ((has_capability('local/classroom:manageclassroom',$systemcontext)/*||
         //        has_capability('local/certification:managecertification',$systemcontext)*/) && !is_siteadmin());
         foreach($statuslist as $key => $value){
            // if($trainer_user && ($value == 'APPROVED' || $value == 'PENDING' || $value == 'REJECTED')){
            //     continue;
            // }
            $customrequestlist[$value] = get_string($value, 'local_request');
        }
        $statuslist = $customrequestlist;
        $select = $mform->addElement('autocomplete', 'status', '', $statuslist, array('placeholder' => get_string('status', 'local_request')));
        $mform->setType('status', PARAM_RAW);
        $select->setMultiple(true);
}
/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
function local_request_leftmenunode(){
    $systemcontext =(new \local_request\lib\accesslib())::get_module_context();
    
    $requestnode = '';
    if(has_capability('local/request:viewrecord',$systemcontext) || (has_capability('local/request:approverecord',  $systemcontext )) || (is_siteadmin())) {
        if(is_siteadmin() || (has_capability('local/request:approverecord',  $systemcontext )))
        {
            $requestlabel = get_string('left_menu_requests','local_request');
        }
        else{
            $requestlabel = get_string('left_menu_requests_enduser','local_request');
        }
        $requestnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_browserequests', 'class'=>'pull-left user_nav_div browserequests'));
            $requests_url = new moodle_url('/local/request/index.php');
            $requests = html_writer::link($requests_url, '<i class="fa fa-share-square"></i><span class="user_navigation_link_text">'.$requestlabel.'</span>',array('class'=>'user_navigation_link'));
            $requestnode .= $requests;
        $requestnode .= html_writer::end_tag('li');
    }

    return array('21' => $requestnode);
}
function local_request_search_page_js(){
    global $PAGE;
    $PAGE->requires->js_call_amd('local_request/requestconfirm', 'load', array());
}
