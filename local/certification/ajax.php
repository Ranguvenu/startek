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
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $CFG, $USER, $PAGE;
//$DB->set_debug(true);
$action = required_param('action', PARAM_ACTION);
$certificationid = optional_param('certificationid', 0, PARAM_INT);
$draw = optional_param('draw', 1, PARAM_INT);
$start = optional_param('start', 0, PARAM_INT);
$length = optional_param('length', 10, PARAM_INT);
$search = optional_param_array('search', '', PARAM_RAW);
$certificationstatus = optional_param('certificationstatus', -1, PARAM_INT);
$certificationmodulehead = optional_param('certificationmodulehead', false, PARAM_BOOL);
$cat = optional_param('categoryname', '', PARAM_RAW);
$context = context_system::instance();
require_login();
$PAGE->set_context($context);
$renderer = $PAGE->get_renderer('local_certification');

switch ($action) {
    case 'viewcertifications':
        $stable = new stdClass();
        $stable->thead = false;
        $stable->search = $search['value'];
        $stable->start = $start;
        $stable->length = $length;
        $stable->certificationstatus = $certificationstatus;
        $return = $renderer->viewcertifications($stable);
    break;
    case 'viewcertificationsessions':
        $stable = new stdClass();
        $stable->search = $search['value'];
        $stable->start = $start;
        $stable->length = $length;
        if ($certificationmodulehead) {
            $stable->thead = true;
        } else {
            $stable->thead = false;
        }
        $return = $renderer->viewcertificationsessions($certificationid, $stable);
    break;
    case 'viewcertificationevaluations':
        $stable = new stdClass();
        if ($certificationmodulehead) {
            $stable->thead = true;
        } else {
            $stable->thead = false;
        }
        $stable->search = $search['value'];
        $stable->start = $start;
        $stable->length = $length;
        $return = $renderer->viewcertificationevaluations($certificationid, $stable);
    break;
    case 'certificationsbystatus':
        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        $stable->certificationstatus = $certificationstatus;
        $return = $renderer->viewcertifications($stable);
    break;
    case 'viewcertificationcourses':
        $stable = new stdClass();
        $stable->search = $search['value'];
        $stable->start = $start;
        $stable->length = $length;
        if ($certificationmodulehead) {
            $stable->thead = true;
        } else {
            $stable->thead = false;
        }
        $return = $renderer->viewcertificationcourses($certificationid, $stable);
    break;
    case 'viewcertificationusers':
        $stable = new stdClass();
        $stable->search = $search['value'];
        $stable->start = $start;
        $stable->length = $length;
        if ($certificationmodulehead) {
            $stable->thead = true;
        } else {
            $stable->thead = false;
        }
        $return = $renderer->viewcertificationusers($certificationid, $stable);
    break;
    case 'managecertificationcategory':
    $rec = new stdClass();
    $rec->fullname = $cat;
    $rec->shortname = $cat;
    if($rec->id){
    $DB->update_record('local_certification_categories',$rec);
     }else{
        $DB->insert_record('local_certification_categories',$rec);
     }
    break;
    case 'certificationlastchildpopup':
        $stable = new stdClass();
        $stable->search = $search['value'];
        $stable->start = $start;
        $stable->length = $length;
        if ($certificationmodulehead) {
            $stable->thead = true;
        } else {
            $stable->thead = false;
        }
        $return = $renderer->viewcertificationlastchildpopup($certificationid, $stable);
    break;
    case 'viewcertificationcompletion_settings_tab':

        $return = $renderer->viewcertificationcompletion_settings_tab($certificationid);
        
    break;
    case 'viewcertificationtarget_audience_tab':

        $return = $renderer->viewcertificationtarget_audience_tab($certificationid);
        
    break;
    case 'viewcertificationrequested_users_tab':
         $certification = $DB->get_records('local_request_records', array('compname' =>'certification','componentid'=>$certificationid));

        $output = $PAGE->get_renderer('local_request');
        $component = 'certification';
        if($certification){
        // $return = $output->render_requestview(new local_request\output\requestview($certification, $component,'','',$certificationid));
        $return = $output->render_requestview(false, $certificationid, 'certification');
        }else{
        $return = '<div class="alert alert-info">'.get_string('requestavail', 'local_classroom').'</div>';
        }
    break;
}

echo json_encode($return);