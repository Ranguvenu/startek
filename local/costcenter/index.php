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
 * @subpackage local_costcenter
 */

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $USER, $PAGE, $OUTPUT, $DB;
require_once($CFG->dirroot . '/local/costcenter/lib.php');
$costcenter = new costcenter();
$theme = $costcenter->get_theme();

$categorycontext = (new \local_costcenter\lib\accesslib())::get_module_context();

$PAGE->requires->css('/local/costcenter/css/jquery.dataTables.min.css');
$PAGE->requires->js_call_amd('local_costcenter/costcenterdatatables', 'costcenterDatatable', array());
$PAGE->requires->js_call_amd('local_assignroles/newcostcenterassignrole', 'load', array());
$PAGE->requires->js_call_amd('local_assignroles/rolespopup', 'init',array(array('contextid' => $categorycontext->id, 'selector' => '.rolescostcenterpopup')));
$PAGE->requires->js_call_amd('local_assignroles/popup', 'Datatable', array());

 
$PAGE->requires->js_call_amd('theme_'.$theme.'/quickactions', 'quickactionsCall');
require_login();

if(!(is_siteadmin() || has_capability('local/costcenter:view', $categorycontext))) {
    print_error('nopermissiontoviewpage');
}
if(!is_siteadmin()){
    $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='lc.path',$costcenterpath=null,$datatype='lowerandsamepath');

    $costcentersql = "SELECT lc.id, lc.fullname,lc.parentid,lc.depth
                        FROM {local_costcenter} AS lc WHERE 1=1 $costcenterpathconcatsql ";

    $depart = $DB->get_record_sql($costcentersql);

    if (!$depart) {

        print_error('invalidcostcenterid');
    }
}

$PAGE->set_pagelayout('standard');
$PAGE->set_context($categorycontext);
$PAGE->set_url('/local/costcenter/index.php');
$PAGE->set_heading(get_string('orgmanage', 'local_costcenter'));
$PAGE->set_title(get_string('orgmanage', 'local_costcenter'));
$PAGE->navbar->add(get_string('dashboard', 'local_costcenter'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('orgmanage', 'local_costcenter'));
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());

$PAGE->requires->js_call_amd('local_costcenter/newsubdept', 'load', array());

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_costcenter');
echo $renderer->get_dept_view_btns();
echo $renderer->departments_view();

echo $OUTPUT->footer();
