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
 * @subpackage local_forum
 */

ini_set('memory_limit', '-1');
define('NO_OUTPUT_BUFFERING', true);
require('../../config.php');
require_once($CFG->dirroot . '/local/courses/lib.php');
require_once($CFG->dirroot . '/local/courses/filters_form.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courses/lib.php');
// require_once($CFG->dirroot.'/local/courses/notifications_emails.php');
// use \local_forum\notificationemails as coursenotifications_emails;
global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SESSION;

$view = optional_param('view', 'page', PARAM_RAW);
$type = optional_param('type', '', PARAM_RAW);
$lastitem = optional_param('lastitem', 0, PARAM_INT);
$countval = optional_param('countval', 0, PARAM_INT);
$enrolid      = required_param('enrolid', PARAM_INT);
$course_id      = optional_param('id', 0, PARAM_INT);
$roleid       = optional_param('roleid', -1, PARAM_INT);
$instance = $DB->get_record('enrol', array('id' => $enrolid, 'enrol' => 'manual'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $instance->courseid), '*', MUST_EXIST);
$submit_value = optional_param('submit_value', '', PARAM_RAW);
$add = optional_param('add', array(), PARAM_RAW);
$remove = optional_param('remove', array(), PARAM_RAW);
$sesskey = sesskey();
$context = context_course::instance($course->id, MUST_EXIST);


$categorycontext = (new \local_forum\lib\accesslib())::get_module_context();

require_login();

if ($view == 'ajax') {
 // $options = (array)json_decode($_GET["options"], false);
  if(is_string($_GET["options"])){
    $options = json_decode($_GET["options"], false);
  }else{
    $options = $_GET["options"];  
  }
  $select_from_users = course_enrolled_users($type, $course_id, $options, false, $offset1 = -1, $perpage = 50, $countval);
  echo json_encode($select_from_users);
  exit;
}

$canenrol = has_capability('local/forum:enrol', $categorycontext);
//$canunenrol = has_capability('local/courses:unenrol', $context);
// Note: manage capability not used here because it is used for editing
// of existing enrolments which is not possible here.
// if (!$canenrol) {
// No need to invent new error strings here...
require_capability('local/forum:enrol', $categorycontext);
require_capability('local/forum:unenrol', $categorycontext);
require_capability('local/forum:manage', $categorycontext);

// }

/*Department level restrictions */
require_once($CFG->dirroot . '/local/includes.php');
$userlist = new has_user_permission();
$haveaccess = $userlist->access_courses_permission($course_id);
if (!$haveaccess) {
  redirect($CFG->wwwroot . '/local/forum/error.php?id=2');
}
if ($roleid < 0) {
  $roleid = $instance->roleid;
}
// $roles = get_assignable_roles($context);
// $roles = array('0'=>get_string('none')) + $roles;
// if (!isset($roles[$roleid])) {
//     //Weird - security always first!
//     $roleid = 0;
// }

if (!$enrol_manual = enrol_get_plugin('manual')) {
  throw new coding_exception('Can not instantiate enrol_manual');
}

$instancename = $enrol_manual->get_instance_name($instance);

$PAGE->set_context($context);
$PAGE->set_url('/local/forum/forumenrol.php', array('id' => $course_id, 'enrolid' => $instance->id));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('manage_forum', 'local_forum'), new moodle_url('/local/forum/index.php'));
$PAGE->navbar->add(get_string('userenrolments', 'local_forum'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/courses/js/jquery.bootstrap-duallistbox.js', true);
$PAGE->requires->css('/local/courses/css/bootstrap-duallistbox.css');
$PAGE->set_title($enrol_manual->get_instance_name($instance));
$data_submitted = data_submitted();

if (!$add && !$remove) {
  $PAGE->set_heading($course->fullname);
}

navigation_node::override_active_url(new moodle_url('/local/mass_enroll/mass_enroll.php', array('id' => $course->id)));


echo $OUTPUT->header();
if ($course) {
  $organization = null;
  $department   = null;
  $subdepartment   = null;
  $department4level   = null;
  $department5level   = null;
  $email        = null;
  $idnumber     = null;
  $uname        = null;
  $groups        = null;
  $states = null;
  $district = null;
  $subdistrict   = null;
  $village   = null;
  $filterlist = get_filterslist();
  $filterparams = array('options' => null, 'dataoptions' => null);
  $mform = new filters_form($PAGE->url, array('filterlist' => $filterlist, 'enrolid' => $enrolid, 'courseid' => $course_id, 'filterparams' => $filterparams, 'action' => 'user_enrolment') + (array)$data_submitted);
  if ($mform->is_cancelled()) {
    redirect($PAGE->url);
  } else {
    $filterdata =  $mform->get_data();
    if ($filterdata) {
      $collapse = false;
      $show = 'show';
    } else {
      $collapse = true;
      $show = '';
    }
    $organization = !empty($filterdata->filteropen_costcenterid) ? implode(',', (array)$filterdata->filteropen_costcenterid) : null;
    $department = !empty($filterdata->filteropen_department) ? implode(',', (array)$filterdata->filteropen_department) : null;
    $subdepartment = !empty($filterdata->filteropen_subdepartment) ? implode(',', (array)$filterdata->filteropen_subdepartment) : null;
    $department4level = !empty($filterdata->filteropen_level4department) ? implode(',', (array)$filterdata->filteropen_level4department) : null;
    $department5level = !empty($filterdata->filteropen_level5department) ? implode(',', (array)$filterdata->filteropen_level5department) : null;

    $states = !empty($filterdata->states) ? implode(',', (array)$filterdata->states) : null;
    $district = !empty($filterdata->district) ? implode(',', (array)$filterdata->district) : null;
    $subdistrict = !empty($filterdata->subdistrict) ? implode(',', (array)$filterdata->subdistrict) : null;
    $village = !empty($filterdata->village) ? implode(',', (array)$filterdata->village) : null;

    $email = !empty($filterdata->email) ? implode(',', (array)$filterdata->email) : null;
    $idnumber = !empty($filterdata->idnumber) ? implode(',', (array)$filterdata->idnumber) : null;
    $uname = !empty($filterdata->users) ? implode(',', (array)$filterdata->users) : null;
    $groups = !empty($filterdata->groups) ? implode(',', (array)$filterdata->groups) : null;
    $location = !empty($filterdata->location) ? implode(',', (array)$filterdata->location) : null;
    $hrmsrole = !empty($filterdata->hrmsrole) ? implode(',', (array)$filterdata->hrmsrole) : null;
  }

  // Create the user selector objects.
  $options = array('context' => $context->id, 'courseid' => $course_id, 'organization' => $organization, 'department' => $department, 'subdepartment' => $subdepartment, 'department4level' => $department4level, 'department5level' => $department5level, 'email' => $email, 'idnumber' => $idnumber, 'uname' => $uname, 'groups' => $groups, 'hrmsrole' => $hrmsrole, 'location' => $location, 'states' => $states, 'district' => $district, 'subdistrict' => $subdistrict, 'village' => $village);
  $dataobj = $course_id;
  $fromuserid = $USER->id;
  if ($add and confirm_sesskey()) {
    $type = 'forum_enrol';
    if ($submit_value == "Add_All_Users") {
      $options = json_decode($_REQUEST["options"], false);
      $userstoassign = array_flip(course_enrolled_users('add', $course_id, (array)$options, false, $offset1 = -1, $perpage = -1));
    } else {
      $userstoassign = $add;
    }
    if (!empty($userstoassign)) {
      // BAYER-23 Assign below CAP to user so that he can enrolusers to courses.
      $capabilities = ['enrol/manual:manage', 'enrol/manual:enrol'];
      $loggedinroleid = $USER->access['rsw']['currentroleinfo']['roleid'];
      if (has_capability('local/forum:enrol', $context) && $roleid && !is_siteadmin()) {
        foreach ($capabilities as $capability) {
          if (!has_capability($capability, $context)) {
            assign_capability($capability, CAP_ALLOW, $loggedinroleid, $context->id, true);
          }
        }
      }
      $progress = 0;
      $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_forum', $course->fullname));
      $progressbar->start_html();
      $progressbar->start_progress('', count($userstoassign) - 1);

      foreach ($userstoassign as $key => $adduser) {
        $progressbar->progress($progress);
        $progress++;
        $timeend = 0;
        $timestart = 0;

        $enrol_manual->enrol_user($instance, $adduser, $roleid, $timestart, $timeend);
        $notification = new \local_forum\notification();
        $course = $DB->get_record('course', array('id' => $dataobj));        
        $course->costcenter = explode('/',$course->open_path)[1];
        $user = core_user::get_user($adduser);
        $notificationdata = $notification->get_existing_notification($course, $type);
        if($notificationdata)
            $notification->forum_notification($type, $user, $fromuser, $course);
      }
      $progressbar->end_html();
      $result = new stdClass();
      $result->changecount = $progress;
      $result->course = $course->fullname;
      if (has_capability('local/forum:enrol', $context) && $roleid && !is_siteadmin()) {
        foreach ($capabilities as $capability) {
          unassign_capability($capability, $loggedinroleid, $context->id);
        }
      }

      echo $OUTPUT->notification(get_string('enrolluserssuccess', 'local_forum', $result), 'success');
      $button = new single_button($PAGE->url, get_string('click_continue', 'local_forum'), 'get', true);
      $button->class = 'continuebutton';
      echo $OUTPUT->render($button);
      echo $OUTPUT->footer();
      die();
    }
  }
  if ($remove && confirm_sesskey()) {
    $type = 'forum_unenroll';
    if ($submit_value == "Remove_All_Users") {
      $options = json_decode($_REQUEST["options"], false);
      $userstounassign = array_flip(course_enrolled_users('remove', $course_id, (array)$options, false, $offset1 = -1, $perpage = -1));
    } else {
      $userstounassign = $remove;
    }
    if (!empty($userstounassign)) {
      $capabilities = ['enrol/manual:manage', 'enrol/manual:unenrol'];
      $loggedinroleid = $USER->access['rsw']['currentroleinfo']['roleid'];
      if (has_capability('local/forum:enrol', $context) && $loggedinroleid && !is_siteadmin()) {
        foreach ($capabilities as $capability) {
          if (!has_capability($capability, $context)) {
            assign_capability($capability, CAP_ALLOW, $loggedinroleid, $context->id, true);
          }
        }
      }
      $progress = 0;
      $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_forum', $course->fullname));
      $progressbar->start_html();
      $progressbar->start_progress('', count($userstounassign) - 1);
      foreach ($userstounassign as $key => $removeuser) {
        $progressbar->progress($progress);
        $progress++;
        if ($instance->enrol == 'manual') {
          $manual = $enrol_manual->unenrol_user($instance, $removeuser);
          //\core\session\manager::kill_user_sessions($removeuser);
        }
        $data_self = $DB->get_record_sql("SELECT * FROM {user_enrolments} ue
                    JOIN {enrol} e ON ue.enrolid=e.id
                    WHERE e.courseid={$course_id} and ue.userid=$removeuser");
        $enrol_self = enrol_get_plugin('self');
        if ($data_self->enrol == 'self') {
          $self = $enrol_self->unenrol_user($data_self, $removeuser);
          //\core\session\manager::kill_user_sessions($removeuser);
        }
        $notification = new \local_forum\notification();
        $course = $DB->get_record('course', array('id' => $dataobj));
        $course->costcenter = explode('/',$course->open_path)[1];
        $user = core_user::get_user($removeuser);
        // $notificationdata = $notification->get_existing_notification($course, $type);
        // if ($notificationdata)
          $notification->forum_notification($type, $user, $fromuser, $course);
      }
      $progressbar->end_html();
      $result = new stdClass();
      $result->changecount = $progress;
      $result->course = $course->fullname;
      if (has_capability('local/forum:enrol', $context) && $loggedinroleid && !is_siteadmin()) {
        foreach ($capabilities as $capability) {
          unassign_capability($capability, $loggedinroleid, $context->id);
        }
      }

      echo $OUTPUT->notification(get_string('unenrolluserssuccess', 'local_forum', $result), 'success');
      $button = new single_button($PAGE->url, get_string('click_continue', 'local_forum'), 'get', true);
      $button->class = 'continuebutton';
      echo $OUTPUT->render($button);
      die();
    }
  }

  $select_to_users = course_enrolled_users('add', $course_id, $options, false, $offset = -1, $perpage = 50);
  $select_to_users_total = course_enrolled_users('add', $course_id, $options, true, $offset1 = -1, $perpage = -1);

  $select_from_users = course_enrolled_users('remove', $course_id, $options, false, $offset1 = -1, $perpage = 50);
  $select_from_users_total = course_enrolled_users('remove', $course_id, $options, true, $offset1 = -1, $perpage = -1);

  $select_all_enrolled_users = '&nbsp&nbsp<button type="button" id="select_add" name="select_all" value="Select All" title="' . get_string('select_all', 'local_forum') . '" class="btn btn-default">' . get_string('select_all', 'local_forum') . '</button>';
  $select_all_enrolled_users .= '&nbsp&nbsp<button type="button" id="add_select" name="remove_all" value="Remove All" title="' . get_string('remove_all', 'local_forum') . '" class="btn btn-default"/>' . get_string('remove_all', 'local_forum') . '</button>';

  $select_all_not_enrolled_users = '&nbsp&nbsp<button type="button" id="select_remove" name="select_all" value="Select All" title="' . get_string('select_all', 'local_forum') . '" class="btn btn-default"/>' . get_string('select_all', 'local_forum') . '</button>';
  $select_all_not_enrolled_users .= '&nbsp&nbsp<button type="button" id="remove_select" name="remove_all" value="Remove All" title="' . get_string('remove_all', 'local_forum') . '" class="btn btn-default"/>' . get_string('remove_all', 'local_forum') . '</button>';
  $content = '<div class="bootstrap-duallistbox-container">';

  $content .= '<form  method="post" name="form_name" id="user_assign" class="form_class" ><div class="box2 col-md-5 col-12 pull-left">
  <input type="hidden" name="id" value="' . $course_id . '"/>
  <input type="hidden" name="enrolid" value="' . $enrolid . '"/>
  <input type="hidden" name="sesskey" value="' . sesskey() . '"/>
  <input type="hidden" name="options"  value=\'' . json_encode($options) . '\' />
  <label>' . get_string('enrolled_users', 'local_forum', $select_from_users_total) . '</label>' . $select_all_not_enrolled_users;
  $content .= '<select multiple="multiple" name="remove[]" id="bootstrap-duallistbox-selected-list_duallistbox_courses_users" class="dual_select">';
  foreach ($select_from_users as $key => $select_from_user) {
    $content .= "<option value='$key'>$select_from_user</option>";
  }

  $content .= '</select>';
  $content .= '</div><div class="box3 col-md-2 col-12 pull-left actions"><button type="submit" class="custom_btn btn remove btn-default" disabled="disabled" title="' . get_string('remove_users', 'local_forum') . '" name="submit_value" value="Remove Selected Users" id="user_unassign_all"/>
  ' . get_string('remove_selected_users', 'local_forum') . '
  </button></form>

  ';

  $content .= '<form  method="post" name="form_name" id="user_un_assign" class="form_class" ><button type="submit" class="custom_btn btn move btn-default" disabled="disabled" title="' . get_string('add_users', 'local_forum') . '" name="submit_value" value="Add Selected Users" id="user_assign_all" />
  ' . get_string('add_selected_users', 'local_forum') . '
  </button></div><div class="box1 col-md-5 col-12 pull-left">
  <input type="hidden" name="id" value="' . $course_id . '"/>
  <input type="hidden" name="enrolid" value="' . $enrolid . '"/>
  <input type="hidden" name="sesskey" value="' . sesskey() . '"/>
  <input type="hidden" name="options"  value=\'' . json_encode($options) . '\' />
  <label> ' . get_string('availablelist', 'local_forum', $select_to_users_total) . '</label>' . $select_all_enrolled_users;
  $content .= '<select multiple="multiple" name="add[]" id="bootstrap-duallistbox-nonselected-list_duallistbox_courses_users" class="dual_select">';
  foreach ($select_to_users as $key => $select_to_user) {
    $content .= "<option value='$key'>$select_to_user</option>";
  }
  $content .= '</select>';
  $content .= '</div></form>';
  $content .= '</div>';

}

echo '<a class="btn-link btn-sm" title="' . get_string('filter') . '" href="javascript:void(0);" data-toggle="collapse" data-target="#local_forumenrol-filter_collapse" aria-expanded="false" aria-controls="local_forumenrol-filter_collapse">
        <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>
      </a>';
echo  '<div class="collapse ' . $show . '" id="local_forumenrol-filter_collapse">
            <div id="filters_form" class="card card-body p-2">';
$mform->display();
echo        '</div>
        </div>';

if ($course) {
  $select_div = '<div class="row d-block">
                <div class="w-100 pull-left">' . $content . '</div>
              </div>';
  echo $select_div;
  $myJSON = json_encode($options);
  echo "<script language='javascript'>

  $( document ).ready(function() {
    $('#select_remove').click(function() {
        $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users option').prop('selected', true);
        $('.box3 .remove').prop('disabled', false);
        $('#user_unassign_all').val('Remove_All_Users');

        $('.box3 .move').prop('disabled', true);
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users option').prop('selected', false);
        $('#user_assign_all').val('Add Selected Users');

    });
    $('#remove_select').click(function() {
        $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users option').prop('selected', false);
        $('.box3 .remove').prop('disabled', true);
        $('#user_unassign_all').val('Remove Selected Users');
    });
    $('#select_add').click(function() {
        $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users option').prop('selected', true);
        $('.box3 .move').prop('disabled', false);
        $('#user_assign_all').val('Add_All_Users');

        $('.box3 .remove').prop('disabled', true);
        $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users option').prop('selected', false);
        $('#user_unassign_all').val('Remove Selected Users');

    });
    $('#add_select').click(function() {
       $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users option').prop('selected', false);
        $('.box3 .move').prop('disabled', true);
        $('#user_assign_all').val('Add Selected Users');
    });
    $('#bootstrap-duallistbox-selected-list_duallistbox_courses_users').on('change', function() {
        if(this.value!=''){
            $('.box3 .remove').prop('disabled', false);
            $('.box3 .move').prop('disabled', true);
        }
    });
    $('#bootstrap-duallistbox-nonselected-list_duallistbox_courses_users').on('change', function() {
        if(this.value!=''){
            $('.box3 .move').prop('disabled', false);
            $('.box3 .remove').prop('disabled', true);
        }
    });
    jQuery(
        function($)
        {
          $('.dual_select').bind('scroll', function()
            {           
              if(Math.round($(this).scrollTop() + $(this).innerHeight())>=$(this)[0].scrollHeight)
              {
                var get_id=$(this).attr('id');
                if(get_id=='bootstrap-duallistbox-selected-list_duallistbox_courses_users'){
                    var type='remove';
                    var total_users=$select_from_users_total;
                }
                if(get_id=='bootstrap-duallistbox-nonselected-list_duallistbox_courses_users'){
                    var type='add';
                    var total_users=$select_to_users_total;

                }
                var count_selected_list=$('#'+get_id+' option').length;

                var lastValue = $('#'+get_id+' option:last-child').val();
                var countval = $('#'+get_id+' option').length;
                if(count_selected_list<total_users){
                   //alert('end reached');
                    var selected_list_request = $.ajax({
                        method: 'GET',
                        url: M.cfg.wwwroot + '/local/forum/forumenrol.php',
                        data: {id:'$course_id',sesskey:'$sesskey', type:type,view:'ajax',countval:countval,enrolid:'$enrolid', options: $myJSON},
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
$backurl = new moodle_url('/local/forum/index.php');
$continue = '<div class="col-md-12 pull-left text-right mt-6">';
$continue .= $OUTPUT->single_button($backurl, get_string('continue'));
$continue .= '</div>';
echo $continue;
echo $OUTPUT->footer();
