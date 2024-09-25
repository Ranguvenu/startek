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
 * @subpackage local_program
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/program/filters_form.php');
$categorycontext = (new \local_program\lib\accesslib())::get_module_context();
require_login();
$value = '';
$id = optional_param('id', 0, PARAM_INT); // program id
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);

$status = optional_param('status', '', PARAM_TEXT);

$costcenterid = optional_param('costcenterid', '', PARAM_INT);
$departmentid = optional_param('departmentid', '', PARAM_INT);
$subdepartmentid = optional_param('subdepartmentid', '', PARAM_INT);
$l4department = optional_param('l4department', '', PARAM_INT);
$l5department = optional_param('l5department', '', PARAM_INT);
$programid = optional_param('program', '', PARAM_INT);


$formattype = optional_param('formattype', 'card', PARAM_TEXT);

if ($formattype == 'card') {
     $formattype_url = 'table';
    $display_text = get_string('listtype','local_program');
    $display_icon = get_string('listicon','local_program');
} else {
    $formattype_url = 'card';
    $display_text = get_string('cardtype','local_program');
    $display_icon = get_string('cardicon','local_program');
}
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
$PAGE->set_url($CFG->wwwroot . '/local/program/index.php');
$PAGE->set_context($categorycontext);
if (!is_siteadmin() && !(has_capability('local/program:manageprogram', $categorycontext))) {
	$PAGE->set_title(get_string('my_programs', 'local_program'));
	$PAGE->set_heading(get_string('my_programs', 'local_program'));
}else{
	$PAGE->set_title(get_string('browse_programs', 'local_program'));
	$PAGE->set_heading(get_string('browse_programs', 'local_program'));
}
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/local/program/css/jquery.dataTables.min.css', true);
$PAGE->requires->js_call_amd('local_program/ajaxforms', 'load');

$corecomponent = new core_component();
$epsilonpluginexist = $corecomponent::get_plugin_directory('theme', 'epsilon');
if (!empty($epsilonpluginexist)) {
	$PAGE->requires->js_call_amd('theme_epsilon/quickactions', 'quickactionsCall');
}
$renderer = $PAGE->get_renderer('local_program');
$PAGE->navbar->add(get_string('dashboard', 'local_costcenter'), new moodle_url('/my'));
$PAGE->navbar->add(get_string("pluginname", 'local_program'));
echo $OUTPUT->header();


// hide the program.
if ($hide AND $id) {
	$program = $DB->get_record('local_program', array('id'=>$id));
	$DB->set_field('local_program', 'visible', 0, array('id'=>$id));
	redirect('index.php');
}
//show the program
if ($show AND $id) {
	$program = $DB->get_record('local_program', array('id'=>$id));
	$DB->set_field('local_program', 'visible', 1, array('id'=>$id));
	redirect('index.php');
}
$enabled = check_programenrol_pluginstatus($value);

$filterparams = $renderer->get_program_records(true,$formattype);


$thisfilters = array('hierarchy_fields','program', 'status');

$formdata = new stdClass();
$formdata->filteropen_costcenterid = $costcenterid;
$formdata->filteropen_department = $departmentid;
$formdata->filteropen_subdepartment = $subdepartmentid;
$formdata->filteropen_level4department = $l4department;
$formdata->filteropen_level5department = $l5department;
$formdata->status = $status;

$mform = new filters_form(null, array('filterlist'=> $thisfilters, 'filterparams' => $filterparams),'post', '', null, true, null);

// $mform = new filters_form(new moodle_url('/local/program/index.php',array('formattype'=>$formattype)), array('filterlist'=> $thisfilters)+(array)$datasubmitted);
// $filterdata = null;     
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/program/index.php');
} else{
    $filterdata =  $mform->get_data();
    if($filterdata){
        $collapse = false;
        $show = 'show';
    } else{
        $collapse = true;
        $show = '';
    }
}
if(empty($filterdata) && !empty($jsonparam)){
    $filterdata = json_decode($jsonparam);
    foreach($thisfilters AS $filter){
        if(empty($filterdata->$filter)){
            unset($filterdata->$filter);
        }
    }
    $mform->set_data($filterdata);
}
if(!empty($costcenterid)|| !empty($status1) || !empty($departmentid) || !empty($subdepartmentid)){   
    $formdata = new stdClass();
    $formdata->filteropen_costcenterid[] = $costcenterid;
    $formdata->filteropen_department[] = $departmentid;
    $formdata->filteropen_subdepartment[] = $subdepartmentid;
    $formdata->filteropen_level4department[] = $l4department;
    $formdata->filteropen_level5department[] = $l5department;
    $formdata->status[] = $status1;
    $mform->set_data($formdata);
echo '<span id="global_filter" class="hidden" data-filterdata='.json_encode($formdata).'></span>';

}

if($filterdata){
    $collapse = false;
    $show = 'show';
} else{
    $collapse = true;
    $show = '';
}

echo '<a class="btn-link btn-sm" data-toggle="collapse" data-target="#local_courses-filter_collapse" aria-expanded="false" aria-controls="local_courses-filter_collapse">
        <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
      </a>';
echo  '<div class="collapse '.$show.'" id="local_courses-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
                $mform->display();
echo        '</div>
        </div>';
// echo '<span id="global_filter" class="hidden" data-filterdata='.json_encode($filterdata).'></span>';
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);

    $condition="";
    $filterparams['submitid'] = 'form#filteringform';
    $filterparams['filterdata'] = json_encode($formdata); 

    $display_url = new moodle_url('/local/program/index.php');
    if($costcenterid){
      $display_url->param('costcenterid', $costcenterid);  
    }
    if($departmentid){
     $display_url->param('departmentid',$departmentid);
    }
    if($subdepartmentid){
     $display_url->param('subdepartmentid',$subdepartmentid);
    }
    if($status1){
     $display_url->param('status1',$status1);
    }
    if($formattype_url){
     $display_url->param('formattype', $formattype_url);      
    } 

$displaytype_div = '<div class="col-12 d-inline-block">';
$displaytype_div .= '<a class="btn btn-outline-secondary pull-right" href="' . $display_url . '">';
$displaytype_div .= '<span class="'.$display_icon.'"></span>' . $display_text;
$displaytype_div .= '</a>';
$displaytype_div .= '</div>';

echo $displaytype_div;

echo $renderer->get_program_tabs($formdata,$programid,$status,$formattype);
echo $renderer->get_program_records(false,$formattype);

echo $OUTPUT->footer();
