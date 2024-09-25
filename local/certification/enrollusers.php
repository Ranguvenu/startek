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
define('NO_OUTPUT_BUFFERING', true);
use \local_certification\certification as certification;
use core_component;
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/certification/lib.php');
global $DB, $USER, $OUTPUT, $CFG;
$core_component = new core_component();
$courses_plugin_exists = $core_component::get_plugin_directory('local', 'courses');
if(!empty($courses_plugin_exists)){
    require_once($CFG->dirroot . '/local/courses/filters_form.php');
}

$certificationid = required_param('ctid', PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);
$submit_value = optional_param('submit_value','', PARAM_RAW);
$add = optional_param('add',array(), PARAM_RAW);
$remove=optional_param('remove',array(), PARAM_RAW);
$view=optional_param('view','page', PARAM_RAW);
$type=optional_param('type','', PARAM_RAW);
$lastitem=optional_param('lastitem',0, PARAM_INT);

$url = new moodle_url('/local/certification/enrollusers.php', array('ctid' => $certificationid));
$certification = $DB->get_record('local_certification', array('id' => $certificationid));
if (empty($certification)) {
    print_error('certification not found!');
}
if ((has_capability('local/certification:managecertification', context_system::instance())) && (!is_siteadmin()
    && (!has_capability('local/certification:manage_multiorganizations', context_system::instance())
        && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
        if($certification->costcenter!=$USER->open_costcenterid){
         print_error("You donot have permissions");
        }

        if ((has_capability('local/certification:manage_owndepartments', context_system::instance())
         || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
            if($certification->department!=$USER->open_departmentid){
                print_error("You donot have permissions");
            }
        }
}

$context = context_system::instance();
$sesskey=sesskey();
$certificationclass = new certification();
// Security.
require_login();
require_capability('local/certification:managecertification', $context);
require_capability('local/certification:manageusers', $context);
if($view=='ajax'){
    $options =(array)json_decode($_GET["options"],false);
     $select_from_users=(new certification)->select_to_and_from_users($type,$certificationid,$options,false,$offset1=-1,$perpage=50,$lastitem);
    echo json_encode($select_from_users);
    exit;
}
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/certification/js/jquery.bootstrap-duallistbox.js',true);
$PAGE->requires->css('/local/certification/css/bootstrap-duallistbox.css');
$PAGE->requires->js_call_amd('local_certification/certification', 'init', array(array('certificationid' => $certificationid)));
$pageurl = new moodle_url($url);
if ($returnurl) {
    $url->param('returnurl', $returnurl);
}
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($certification->name);
$PAGE->set_pagelayout('admin');
$data_submitted=data_submitted();


// print_object($data_submitted);exit;
if ($certificationid) {
    $organization = null;
    $department   = null;
    $email        = null;
    $idnumber     = null;
    $uname        = null;
    $groups        = null;
    $location     = null;
    $hrmsrole     = null;
	if(file_exists($CFG->dirroot.'/local/lib.php')){
        require_once($CFG->dirroot.'/local/lib.php');
        $filterlist = get_filterslist();
    }
    if(!empty($courses_plugin_exists)){
        $mform = new filters_form($url, array('filterlist'=>$filterlist, 'action' => 'user_enrolment'));
        if ($mform->is_cancelled()) {
            redirect($PAGE->url);
        } else{
            $filterdata =  $mform->get_data();
            if($filterdata){
                $collapse = false;
                $show = 'show';
            } else{
                $collapse = true;
                $show = '';
            }
            $organization = !empty($filterdata->organizations) ? implode(',', $filterdata->organizations) : null;
            $department = !empty($filterdata->departments) ? implode(',', $filterdata->departments) : null;
            $email = !empty($filterdata->email) ? implode(',', $filterdata->email) : null;
            $idnumber = !empty($filterdata->idnumber) ? implode(',', $filterdata->idnumber) : null;
            $uname = !empty($filterdata->users) ? implode(',', $filterdata->users) : null;
            $groups = !empty($filterdata->groups) ? implode(',', $filterdata->groups) : null;
            $location = !empty($filterdata->location) ? implode(',', $filterdata->location) : null;
            $hrmsrole = !empty($filterdata->hrmsrole) ? implode(',', $filterdata->hrmsrole) : null;
        }
    }

    // Create the user selector objects.
    $options = array('context' => $context->id, 'certificationid' => $certificationid, 'organization' => $organization, 'department' => $department, 'email' => $email, 'idnumber' => $idnumber, 'uname' => $uname, 'groups' => $groups, 'location' => $location, 'hrmsrole' => $hrmsrole);
    //$potentialuserselector = new local_certification_potential_users('addselect', $options);
    //$currentuserselector = new local_certification_existing_users('removeselect', $options);
    
    if ( $add&& confirm_sesskey()) {
        
        if($submit_value=="Add_All_Users"){
			  $options =(array)json_decode($_REQUEST["options"],false);
              $userstoassign=array_flip((new certification)->select_to_and_from_users('add',$certificationid,$options,false,$offset1=-1,$perpage=-1));
        }else{
            $userstoassign =$add;
        }
     
        if (!empty($userstoassign)) {
            echo $OUTPUT->header();  
            $result=$certificationclass->certification_add_assignusers($certificationid, $userstoassign,$request=0);
           
            echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_certification',$result),'success');
            $button = new single_button($url, get_string('click_continue','local_certification'), 'get', true);
            $button->class = 'continuebutton';
            echo $OUTPUT->render($button);
            echo $OUTPUT->footer();
            die();
        }
        // redirect($PAGE->url);
    }
    if ( $remove&& confirm_sesskey()) {
        
        if($submit_value=="Remove_All_Users"){
			 $options =(array)json_decode($_REQUEST["options"],false);
             $userstounassign=array_flip((new certification)->select_to_and_from_users('remove',$certificationid,$options,false,$offset1=-1,$perpage=-1));
        }else{
            $userstounassign =$remove;
        }
        //print_object($userstounassign);exit;
        if (!empty($userstounassign)) {
            echo $OUTPUT->header();
            $result=$certificationclass->certification_remove_assignusers($certificationid, $userstounassign, $request=0);
           
                echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_certification',$result),'success');
                $button = new single_button($url, get_string('click_continue','local_certification'), 'get', true);
                $button->class = 'continuebutton';
                echo $OUTPUT->render($button);
            echo $OUTPUT->footer();
            die();
        }
        //redirect($PAGE->url);
    }
    $certification_capacity_check=(new certification)->certification_capacity_check($certificationid);
    $capacity_check = '';
    $disabled="s";
    if($certification_capacity_check){
        $capacity_check = '<div class="w-100 pull-left text-center">'.get_string('capacity_check', 'local_certification').'</div>';
        $disabled='disabled';
    }

    $select_to_users=(new certification)->select_to_and_from_users('add',$certificationid,$options,false,$offset=-1,$perpage=50);
    $select_to_users_total=(new certification)->select_to_and_from_users('add',$certificationid,$options,true,$offset1=-1,$perpage=-1);
 
    $select_from_users=(new certification)->select_to_and_from_users('remove',$certificationid,$options,false,$offset1=-1,$perpage=50);
    $select_from_users_total=(new certification)->select_to_and_from_users('remove',$certificationid,$options,true,$offset1=-1,$perpage=-1);

    $select_all_enrolled_users='&nbsp&nbsp<button type="button" id="select_add" name="select_all" value="Select All" title="Select All"/ class="btn btn-default" '.$disabled.'>'.get_string('select_all', 'local_certification').'</button>';
    $select_all_enrolled_users.='&nbsp&nbsp<button type="button" id="add_select" name="remove_all" value="Remove All" title="Remove All" class="btn btn-default" '.$disabled.'>'.get_string('remove_all', 'local_certification').'</button>';

    
    $select_all_not_enrolled_users='&nbsp&nbsp<button type="button" id="select_remove" name="select_all" value="Select All" title="Select All" class="btn btn-default"/>'.get_string('select_all', 'local_certification').'</button>';
    $select_all_not_enrolled_users.='&nbsp&nbsp<button type="button" id="remove_select" name="remove_all" value="Remove All" title="Remove All" class="btn btn-default"/>'.get_string('remove_all', 'local_certification').'</button>';
    
    
   $content='<div class="bootstrap-duallistbox-container">
           ';
   $content.='<form  method="post" name="form_name" id="user_assign" class="form_class" ><div class="box2 col-md-5 col-12 pull-left">
    <input type="hidden" name="ctid" value="'.$certificationid.'"/>
    <input type="hidden" name="sesskey" value="'.sesskey().'"/>
	<input type="hidden" name="options"  value=\''.json_encode($options).'\' />
   <label>'.get_string('enrolled_users', 'local_certification',$select_from_users_total).'</label>'.$select_all_not_enrolled_users;
   $content.='<select multiple="multiple" name="remove[]" id="bootstrap-duallistbox-selected-list_duallistbox_certification_users" class="dual_select">';
   foreach($select_from_users as $key=>$select_from_user){
          $content.="<option value='$key'>$select_from_user</option>";
    }

   $content.='</select>';
   $content.='</div><div class="box3 col-md-2 col-12 pull-left actions"><button type="submit" class="custom_btn btn remove btn-default" disabled="disabled" title="Remove Selected Users" name="submit_value" value="Remove Selected Users" id="user_unassign_all"/>
       '.get_string('remove_selected_users', 'local_certification').'
        </button></form>
       
       ';
   $content.='<form  method="post" name="form_name" id="user_un_assign" class="form_class" ><button type="submit" class="custom_btn btn move btn-default" disabled="disabled" title="Add Selected Users" name="submit_value" value="Add Selected Users" id="user_assign_all" />
       '.get_string('add_selected_users', 'local_certification').'
        </button></div><div class="box1 col-md-5 col-12 pull-left">
    <input type="hidden" name="ctid" value="'.$certificationid.'"/>
    <input type="hidden" name="sesskey" value="'.sesskey().'"/>
	<input type="hidden" name="options"  value=\''.json_encode($options).'\' />
   <label> '.get_string('not_enrolled_users', 'local_certification',$select_to_users_total).'</label>'.$select_all_enrolled_users;
    $content.='<select multiple="multiple" name="add[]" id="bootstrap-duallistbox-nonselected-list_duallistbox_certification_users" class="dual_select">';
    foreach($select_to_users as $key=>$select_to_user){
          $content.="<option value='$key'>$select_to_user</option>";
    }
    $content.='</select>';
    $content.='</div></form>';
    $content.='</div>';
}
$PAGE->navbar->add(get_string("pluginname", 'local_certification'), new moodle_url('index.php',array('ctid'=>$certification->id)));
$PAGE->navbar->add($certification->name,new moodle_url('view.php',array('ctid'=>$certification->id)));
$PAGE->navbar->add(get_string("enrolusers", 'local_certification'));
$PAGE->set_heading(get_string('assignusers_heading', 'local_certification',$certification->name));
echo $OUTPUT->header();

//// Print heading.
//echo $OUTPUT->heading_with_help($certification->name, 'addusers', 'local_certification');

if(!empty($courses_plugin_exists)){
    // print_collapsible_region_start(' ', 'filters_form', ' '.' '.get_string('filters'), false, $collapse);
    // $mform->display();
    // print_collapsible_region_end();
    echo '<a class="btn-link btn-sm" title="Filter" href="javascript:void(0);" data-toggle="collapse" data-target="#local_certificateenrol-filter_collapse" aria-expanded="false" aria-controls="local_certificateenrol-filter_collapse">
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
          </a>';
    echo  '<div class="collapse '.$show.'" id="local_certificateenrol-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo        '</div>
            </div>';
}
if ($certificationid) {

    $assignurl = new moodle_url($PAGE->url, array('ctid' => $certificationid));

    $select_div = '<div class="row d-block">
                        <div class="col-12 pull-left">'.$capacity_check.$content.'</div>
                   </div>';
echo $select_div;
$myJSON = json_encode($options);
echo "<script language='javascript'>

$( document ).ready(function() {
    $('#select_remove').click(function() {
        $('#bootstrap-duallistbox-selected-list_duallistbox_certification_users option').prop('selected', true);
        $('.box3 .remove').prop('disabled', false);
        $('#user_unassign_all').val('Remove_All_Users');

        $('.box3 .move').prop('disabled', true);
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_certification_users option').prop('selected', false);
        $('#user_assign_all').val('Add Selected Users');

    });
    $('#remove_select').click(function() {
        $('#bootstrap-duallistbox-selected-list_duallistbox_certification_users option').prop('selected', false);
        $('.box3 .remove').prop('disabled', true);
        $('#user_unassign_all').val('Remove Selected Users');
    });
    $('#select_add').click(function() {
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_certification_users option').prop('selected', true);
        $('.box3 .move').prop('disabled', false);
        $('#user_assign_all').val('Add_All_Users');

        $('.box3 .remove').prop('disabled', true);
        $('#bootstrap-duallistbox-selected-list_duallistbox_certification_users option').prop('selected', false);
        $('#user_unassign_all').val('Remove Selected Users');
        
    });
    $('#add_select').click(function() {
       $('#bootstrap-duallistbox-nonselected-list_duallistbox_certification_users option').prop('selected', false);
        $('.box3 .move').prop('disabled', true);
        $('#user_assign_all').val('Add Selected Users');
    });
    $('#bootstrap-duallistbox-selected-list_duallistbox_certification_users').on('change', function() {
        if(this.value!=''){
            $('.box3 .remove').prop('disabled', false);
            $('.box3 .move').prop('disabled', true);
        }
    });
    $('#bootstrap-duallistbox-nonselected-list_duallistbox_certification_users').on('change', function() {
        if(this.value!=''){
            if('$disabled'=='s'){
            $('.box3 .move').prop('disabled', false);
            $('.box3 .remove').prop('disabled', true);
           }
        }
    });
    jQuery(
        function($)
        {
          $('.dual_select').bind('scroll', function()
            {
              if($(this).scrollTop() + $(this).innerHeight()>=$(this)[0].scrollHeight)
              {
                var get_id=$(this).attr('id');
                if(get_id=='bootstrap-duallistbox-selected-list_duallistbox_certification_users'){
                    var type='remove';
                    var total_users=$select_from_users_total;
                }
                if(get_id=='bootstrap-duallistbox-nonselected-list_duallistbox_certification_users'){
                    var type='add';
                    var total_users=$select_to_users_total;
                   
                }
                var count_selected_list=$('#'+get_id+' option').length;
               
                var lastValue = $('#'+get_id+' option:last-child').val();
             
              if(count_selected_list<total_users){  
                   //alert('end reached');
                    var selected_list_request = $.ajax({
                        method: 'GET',
                        url: M.cfg.wwwroot + '/local/certification/enrollusers.php?options=$myJSON',
                        data: {ctid:'$certificationid',sesskey:'$sesskey', type:type,view:'ajax',lastitem:lastValue},
                        dataType: 'html'
                    });  
                    var appending_selected_list = '';
                    selected_list_request.done(function(response){
                    //console.log(response);
                    response = jQuery.parseJSON(response);
                    //console.log(response);
                  
                    $.each(response, function (index, data) {
                   
                        appending_selected_list = appending_selected_list + '<option value=' + index + '>' + data + '</option>';
                    });
                    $('#'+get_id+'').append(appending_selected_list);
                    });
                }
              }
            })
        }
    );
 
});
    </script>";
}
 
  $continue='<div class="col-md-12 pull-right text-right mt-15">';
  $continue.='<a href='.$CFG->wwwroot.'/local/certification/view.php?ctid='.$certificationid.' class="singlebutton"><button class="btn">'.get_string('continue', 'local_certification').'</button></a>';
  $continue.='</div>';
  echo $continue;
echo $OUTPUT->footer();
