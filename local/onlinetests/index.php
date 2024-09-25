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
 * @subpackage local_onlinetest
 */
require_once("../../config.php");
require_once("lib.php");
global $DB, $OUTPUT,$USER,$CFG;
require_once($CFG->dirroot . '/local/courses/filters_form.php');
$id = optional_param('id', -1, PARAM_INT); // onlinetest id
$delete = optional_param('delete', 0, PARAM_INT);
$hide = optional_param('hide', '', PARAM_INT);
//$show = optional_param('show', 0, PARAM_BOOL);*/
$visible = optional_param('visible', 0, PARAM_BOOL);
$tab = optional_param('tab',0,PARAM_RAW);
$userid = optional_param('userid','',PARAM_INT);

$status = optional_param('status', '', PARAM_RAW);
$costcenterid = optional_param('costcenterid', '', PARAM_INT);
$departmentid = optional_param('departmentid', '', PARAM_INT);
$subdepartmentid = optional_param('subdepartmentid','',PARAM_INT);
$l4department = optional_param('l4department', '', PARAM_INT);
$l5department = optional_param('l5department', '', PARAM_INT);

require_login();

$categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();
$PAGE->set_url('/local/onlinetests/index.php');
$PAGE->set_context($categorycontext);
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('pluginname', 'local_onlinetests'));
$PAGE->set_heading(get_string('pluginname', 'local_onlinetests'));
$PAGE->requires->jquery();
$PAGE->requires->css('/local/onlinetests/css/jquery.dataTables.css');
$PAGE->requires->js_call_amd('theme_epsilon/quickactions', 'quickactionsCall');
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());

// $current_langcode = current_language();  /* $SESSION->lang;*/
// $stringman = get_string_manager();
// $strings = $stringman->load_component_strings('local_onlinetests', $current_langcode);   /*'en'*/
// $PAGE->requires->strings_for_js(array_keys($strings), 'local_onlinetests');
$pagenavurl = new moodle_url('/local/onlinetests/index.php');
$PAGE->navbar->add(get_string("pluginname", 'local_onlinetests'));
echo $OUTPUT->header();

if (!has_capability('local/onlinetests:view', $categorycontext)) {
	print_error(get_string('dont_have_permission_view_page', 'local_onlinetests'));
}
if (is_siteadmin() OR has_capability('local/onlinetests:manage', $categorycontext)) {
	//if (is_siteadmin() OR has_capability('local/onlinetests:create', $categorycontext)) {
		
			// <li>
			// 	<div class = "coursebackup course_extended_menu_itemcontainer">
			// 		<a class="course_extended_menu_itemlink" data-action="createonlinetestsmodal" title="'.get_string("create_onlinetest", "local_onlinetests").'"><i class="icon fa fa-plus" aria-hidden="true"></i>
			// 		</a>
			// 	</div>
			// </li>';
	//}
	echo '<ul class="course_extended_menu_list">';
	if(is_siteadmin() ||(
        has_capability('local/onlinetests:manage', $categorycontext))){
     $sql = "SELECT id,name FROM {block_learnerscript} WHERE category = 'local_onlinetests'" ;
     $onlinetestreports = $DB->get_records_sql($sql);
    if($onlinetestreports){
    	$out .= '<li><div class="dropdown"><a href="#" tabindex="0" class=" dropdown-toggle icon-no-margin" id="dropdown-1" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-controls="reportswitch" title="'.get_string('onlineeaxam_reports','local_onlinetests').'">            
            <i class="icon fa fa-signal" aria-hidden="true"></i>
        </a>';


    	$out .= '<div class="dropdown-menu dropdown-menu-right menu  align-tr-br" id="reportswitch" data-rel="menu-content" aria-labelledby="action-menu-toggle-1" role="menu" data-align="tr-br">';
		 $reports_info = array();
		foreach($onlinetestreports as $onlinetest){
		    $reports = array();
		    $reports['id'] = $onlinetest->id;
		    $reports['name'] = $onlinetest->name;
		    $reports_info[] = $reports;
    		$out .= '<div class="dropdown-divider" role="presentation"><span class="filler">&nbsp;</span></div>
                                        <a href='.$CFG->wwwroot.'/blocks/learnerscript/viewreport.php?id='.$onlinetest->id.' class="dropdown-item menu-action" role="menuitem" data-title="'.$onlinetest->name.'" aria-labelledby="'.$onlinetest->name.'" target="_blank"> 
                                            <span class="menu-action-text">
                                                '.$onlinetest->name.'
                                            </span>
                                        </a>';

   	}
   	$out .= '</div></div></li>';
	}
}
echo $out;
echo '<li>
		<div class = "coursebackup course_extended_menu_itemcontainer">
					<a class="course_extended_menu_itemlink" data-action="createonlinetestsmodal" title="'.get_string("create_onlinetest", "local_onlinetests").'"><i class="icon fa fa-plus" aria-hidden="true"></i>
					</a>
				</div>
			</li>';
echo '</ul>';
	
	$PAGE->requires->js_call_amd('local_onlinetests/newonlinetests', 'init', array('[data-action=createonlinetestsmodal]', $categorycontext->id, $id));
	$PAGE->requires->js_call_amd('local_onlinetests/newonlinetests', 'getdepartmentlist');
}

// Delete the module.
if ($delete) {
	$onlinetest = $DB->get_record('local_onlinetests', array('quizid'=>$delete));
	$cm = get_coursemodule_from_instance('quiz', $delete, 0, false, MUST_EXIST);
    course_delete_module($cm->id);
	// delete events related to local quiz
	$DB->delete_records('event', array('plugin_instance'=>$onlinetest->id, 'plugin'=>'local_onlinetests'));
	// delete local tables records related to quiz
	$DB->delete_records('local_onlinetests', array('id'=>$onlinetest->id));
	$DB->delete_records('local_onlinetest_users', array('onlinetestid'=>$onlinetest->id));
	if($onlinetest->courseid != 1){
		$corcat = $DB->get_field('course','category',array('id' => $onlinetest->courseid));
        $category = $DB->get_record('course_categories',array('id'=>$corcat));
		delete_course($onlinetest->courseid,false);
		$category->coursecount = $category->coursecount-1;
        $DB->update_record('course_categories',$category);
	}

	
	$params = array(
        'context' => $categorycontext,
        'objectid' => $onlinetest->id
    );

    $event = \local_onlinetests\event\onlinetest_deleted::create($params);
    $event->add_record_snapshot('local_onlinetests', $onlinetest);
    $event->trigger();
	redirect('index.php');
}

if ($hide  AND $id) { 
    $onlinetest = $DB->get_record('local_onlinetests', array('id'=>$id));
	$DB->set_field('local_onlinetests', 'visible', $visible , array('id'=>$id));
	$cm = get_coursemodule_from_instance('quiz', $onlinetest->quizid, 0, false, MUST_EXIST);
	$DB->set_field('course_modules', 'visible', $visible , array('id'=>$cm->id));
	$DB->set_field('course_modules', 'visibleoncoursepage', $visible , array('id'=>$cm->id));
	redirect('index.php');
}
//print_object($show);
/*if ($show AND $id) {
	$onlinetest = $DB->get_record('local_onlinetests', array('id'=>$id));
	$DB->set_field('local_onlinetests', 'visible', 1, array('id'=>$id));
	$cm = get_coursemodule_from_instance('quiz', $onlinetest->quizid, 0, false, MUST_EXIST);
	$DB->set_field('course_modules', 'visible', 1, array('id'=>$cm->id));
	$DB->set_field('course_modules', 'visibleoncoursepage', 1, array('id'=>$cm->id));
	redirect('index.php');
}
*/
$renderer = $PAGE->get_renderer('local_onlinetests');
$filterparams = $renderer->get_onlinetests(true);
// if(is_siteadmin()){
// 	$thisfilters = array('organizations', 'departments', 'onlinetests', 'status');
// 	$enablefilters = true;
// }else if(has_capability('local/costcenter:manage_ownorganization',$categorycontext)){
// 	$thisfilters = array('departments', 'onlinetests', 'status');
// 	$enablefilters = true;
// }else if(has_capability('local/costcenter:manage_owndepartments',$categorycontext)){
// 	$thisfilters = array('onlinetests', 'status');
// 	$enablefilters = true;
// } else {
// 	$thisfilters = array('onlinetests');
// 	$enablefilters = true;
// }

// $mform = new filters_form(null, array('filterlist'=> $thisfilters, 'filterparams' => $filterparams));

$formdata = new stdClass();
$formdata->filteropen_costcenterid = $costcenterid;
$formdata->filteropen_department = $departmentid;
$formdata->filteropen_subdepartment = $subdepartmentid;
$formdata->filteropen_level4department = $l4department;
$formdata->filteropen_level5department = $l5department;

$mform = onlinetests_filters_form($filterparams, (array)$formdata);
if ($mform->is_cancelled()) {
	redirect($CFG->wwwroot . '/local/onlinetests/index.php');
} else{
	$filterdata =  $mform->get_data();
	if($filterdata){
		$collapse = false;
	} else{
		$collapse = true;
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
if(!empty($costcenterid) || !empty($status) || !empty($departmentid) || !empty($subdepartmentid) || !empty($department4levelid) || !empty($department5levelid)){
        // $formdata = new stdClass();
        // $formdata->organizations = $costcenterid;
        // $formdata->departments = $departmentid;
        // $formdata->subdepartment = $subdepartmentid;
        // $formdata->department4level = $department4levelid;
        // $formdata->department5level = $department5levelid;
        $formdata->status = $status;
        $mform->set_data($formdata);
}
if($filterdata){
	$collapse = false;
	$show = 'show';
} else{
	$collapse = true;
	$show = '';
}
if(is_siteadmin() ||(
	has_capability('local/onlinetests:manage', $categorycontext))){
		echo '<a class="btn-link btn-sm" href="javascript:void(0);" data-toggle="collapse" data-target="#local_onlinetests-filter_collapse" aria-expanded="false" aria-controls="local_onlinetests-filter_collapse" title="Filters">
				<i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
			</a>';
		echo  '<div class="collapse '.$show.' local_filter_collapse" id="local_onlinetests-filter_collapse">
					<div id="filters_form" class="card card-body p-2">';
						$mform->display();
		echo        '</div>
				</div>';
	}

$filterparams['submitid'] = 'form#filteringform';
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
echo $renderer->get_onlinetests();

// if (!empty($thisfilters)) {
// 	$heading = '<span class="filter-lebel">'.get_string('filters').'</span>';
// 	print_collapsible_region_start(' ', 'filters_form', ' '.' '.$heading, false, $collapse);
// 	$mform->display();
// 	print_collapsible_region_end();
// }

echo $OUTPUT->footer();

