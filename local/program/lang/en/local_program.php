<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language Strings
 *
 * @package    local_program
 * @copyright  2018 Arun Kumar M <arun@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Manage Programs';
$string['browse_programs'] = 'Manage Programs';
$string['my_programs'] = 'My Programs';
$string['programs'] = 'View program';
$string['costcenter'] = 'Company';
$string['department'] = 'Bussiness Unit';
$string['levels'] = 'Level Count';
$string['shortname_help'] = 'Please enter the partner short name';
$string['program_offline'] = 'Offline Courses';
$string['program_online'] = 'Online Courses';
$string['program_type'] = 'Program Type';
$string['course'] = 'Course';
$string['assign_test'] = 'Assign Test';
$string['course_tests'] = 'Quizs';
$string['trainers'] = 'Trainers';
$string['description'] = 'Description';
$string['description_help'] = 'Enter Program description. This will be displayed in the list of Programs.';
$string['internal'] = 'Internal';
$string['external'] = 'External';
$string['institute_type'] = 'Location Type';
$string['institutions'] = 'Institutions';
$string['startdate'] = 'Start Date';
$string['enddate'] = 'End Date';
$string['create_program'] = 'Create Program';
$string['program_header'] = 'View Programs';
$string['select_course'] = '--Select Course--';
$string['select_quiz'] = '--Select Quiz--';
$string['select_trainers'] = '--Select Trainer--';
$string['select_institutions'] = '--Select Location--';
$string['program_name'] = 'Program Name';
$string['allow_multi_session'] = 'Allow Multiple Session';
$string['stream'] = 'Stream';
$string['fixed'] = 'Fixed';
$string['custom'] = 'Custom';
$string['need_manage_approval'] = 'Need Manager Approval';
$string['capacity'] = 'Capacity';
$string['manage_program'] = 'Manage Program';
$string['manage_programs'] = 'Manage Programs';
$string['assign_course'] = 'Other Details';
$string['session_management'] = 'Session Management';
$string['location_date'] = 'Location & Date';
$string['certification'] = 'Certification';
$string['learningplan'] = 'Learning Plan';
$string['program'] = 'Program';
$string['capacity_positive'] = 'Capacity must be greater than zero (0).';
$string['missingprogram'] = 'Missed program data';
$string['courseduration'] = 'Course Duration';
$string['session_name'] = 'Session Name';
$string['session_type'] = 'Session Type';
$string['bc_location_type'] = 'Location Type';
$string['program_location'] = 'Location';
$string['location_room'] = 'Location Room';
$string['nomination_startdate'] = 'Nomination start date';
$string['nomination_enddate'] = 'Nomination End date';
$string['type'] = 'Type';
$string['select_category'] = '--Select Category--';
$string['deleteconfirm'] = 'Are you sure you want to delete this "<b>{$a}</b>" program?';
$string['deleteallconfirm'] = 'Are you sure you want to delete this "<b>{$a}</b>" level?';
$string['deletecourseconfirm'] = 'Are you sure you want to un-assign?';
$string['createprogram'] = '<i class="fa fa-graduation-cap" aria-hidden="true"></i> Create Program <div class="popupstring">Here you will create programs based upon the Stream </div>';
$string['updateprogram']= '<i class="fa fa-graduation-cap" aria-hidden="true"></i> Update program <div class="popupstring">Here you will update programs based upon the Stream </div>';
$string['save_continue'] = 'Save & Continue';
$string['enddateerror'] = 'End Date should greater than Start Date.';
$string['sessionexisterror'] = 'There are other sessions have in this time.';
$string['nomination_startdateerror'] = 'Nomination Start Date Schedule should be less than program Start Date.';
$string['nomination_enddateerror'] = 'Nomination End Date Schedule should be less than program Start Date.';
$string['nomination_error'] = 'Nomination End Date should greater than Nomination Start Date.';
$string['cs_timestart'] = 'Session Start Date';
$string['showentries'] = 'View response';
$string['cs_timefinish'] = 'Session End Date';
$string['select_room'] = '--Select ROOM--';
$string['select_costcenter'] = '--Select Company--';
$string['select_department'] = 'All Bussiness Units';
$string['program_active_action'] = 'Are you sure you want to publish this "<b>{$a}</b>" program?';
$string['program_release_hold_action'] = 'Are you sure you want to release this "<b>{$a}</b>" program?';
$string['program_hold_action'] = 'Are you sure you want to hold this "<b>{$a}</b>" program?';
$string['program_close_action'] = 'Are you sure you want to cancel this "<b>{$a}</b>" program?';
$string['program_cancel_action'] = 'Are you sure you want to cancel this "<b>{$a}</b>" program?';
$string['program_complete_action'] = 'Are you sure you want to complete this "<b>{$a}</b>" program?';
$string['program_activate'] = 'Are you sure you want to publish this "<b>{$a}</b>" program?';
$string['program'] = 'Program';
$string['learningplan'] = 'Learning Plan';
$string['certificate'] = 'Certificate';
$string['completed'] = 'Completed';
$string['pending'] = 'Pending';
$string['attendace'] = 'Attendance';
$string['attended_sessions'] = 'Attended Sessions';
$string['attended_sessions_users'] = 'Attended Users';
$string['attended_hours'] = 'Attended Hours';
$string['supervisor'] = 'Supervisor';
$string['employee'] = 'Employee';
$string['room'] = 'Room';
$string['status'] = 'Status';
$string['trainer'] = 'Trainer';
$string['faculty'] = 'Trainer';
$string['addcourse'] = 'Add Courses';
$string['selfenrol'] = 'Self Enroll';
$string['selfenroll_help'] = 'Select

* Yes - If you want to let learners self enroll themselves to the Program.

* No - If you dont want to let learners self enroll themselves to the Program';
// Capability strings.
$string['program:createprogram'] = 'Create Program';
$string['program:viewprogram'] = 'View Program';
$string['program:editprogram'] = 'Edit Program';
$string['program:deleteprogram'] = 'Delete Program';
$string['program:manageprogram'] = 'Manage Program';
$string['program:createsession'] = 'Create Session';
$string['program:viewsession'] = 'View Session';
$string['program:editsession'] = 'Edit Session';
$string['program:deletesession'] = 'Delete Session';
$string['program:managesession'] = 'Manage Session';
$string['program:assigntrainer'] = 'Assign Trainer';
$string['program:managetrainer'] = 'Manage Trainer';
$string['program:addusers'] = 'Add users';
$string['program:removeusers'] = 'Remove users';
$string['program:manageusers'] = 'Manage users';

$string['program:cancel'] = 'Cancel program';
$string['program:programcompletion'] = 'Program Completion Setting';
$string['program:complete'] = 'Complete program';
$string['program:createcourse'] = 'Assign program Course';
$string['program:createfeedback'] = 'Assign program Feedback';
$string['program:deletecourse'] = 'Un Assign program Course';
$string['program:deletefeedback'] = 'Un Assign program Feedback';
$string['program:editcourse'] = 'Edit program Course';
$string['program:editfeedback'] = 'Edit program Feedback';
$string['program:hold'] = 'Hold program';
$string['program:managecourse'] = 'Manage program Course';
$string['program:managefeedback'] = 'Manage program Feedback';
$string['program:publish'] = 'Publish program';
$string['program:release_hold'] = 'Release Hold program';
$string['program:takemultisessionattendance'] = 'Program Multisession Attendance';
$string['program:manage_owndepartments'] = 'Manage Own Bussiness Unit program.';
$string['program:manage_multiorganizations'] = 'Manage Multi Companies programs.';
$string['program:takesessionattendance'] = 'Program Session Attendance';
$string['program:trainer_viewprogram'] = 'Trainer View program';
$string['program:viewcourse'] = 'View program Course';
$string['program:viewfeedback'] = 'View program Feedback';
$string['program:viewusers'] = 'View program Users';
$string['program:view_activeprogramtab'] = 'View Active programs Tab';
$string['program:view_allprogramtab'] = 'View All programs Tab';
$string['program:view_cancelledprogramtab'] = 'View Cancelled programs Tab';
$string['program:view_completedprogramtab'] = 'View Completed programs Tab';
$string['program:view_holdprogramtab'] = 'View Hold programs Tab';
$string['program:view_newprogramtab'] = 'View New programs Tab';
// Room Strings.
$string['institute_name'] = 'Location Name';
$string['building'] = 'Building Name';
$string['roomname'] = 'Room Name';
$string['address'] = 'Address';
$string['capacity'] = 'Capacity';
$string['capacity_help'] = 'Capacity help';
// Session Strings.
$string['onlinesession'] = 'Online Session';
$string['onlinesession_help'] = 'If checked this option and submitted online session will be created.';
$string['addasession'] = 'Add one more Session';
$string['addsession'] = 'Create a Session';
$string['createsession'] = 'Add Sessions';
$string['session_dates'] = 'Session Dates';
$string['attendance_status'] = 'Attendance Status';
$string['sessiondatetime'] = 'Session Date and Time';
$string['session_details'] = 'Session Details';
$string['cs_capacity_number'] = 'Capacity must be numeric and positive';
$string['select_cr_room'] = 'Select a room';
// Empty Message Strings.
$string['noprograms'] = 'Programs not available';
$string['nosessions'] = 'Sessions not available';
$string['noprogramusers'] = 'No Users Enrolled to this program';
$string['noprogramsessionusers'] = 'No Users Enrolled to this Session';
$string['noprogramcourses'] = 'Program courses not available';
// program Users.
$string['select_all'] = 'Select all';
$string['remove_all'] = 'Un Select All';
$string['enrolled'] = 'Enrolled';
$string['not_enrolled_users'] = '<b>Not enrolled users ({$a})</b>';
$string['enrolled_users'] = '<b> Enrolled Users ({$a})</b>';
$string['remove_selected_users'] = '<b> Un Enroll Users </b><i class="fa fa-arrow-right" aria-hidden="true"></i><i class="fa fa-arrow-right" aria-hidden="true"></i>';
$string['remove_all_users'] = '<b> Un Enroll All Users </b><i class="fa fa-arrow-right" aria-hidden="true"></i><i class="fa fa-arrow-right" aria-hidden="true"></i>';
$string['add_selected_users'] = '<i class="fa fa-arrow-left" aria-hidden="true"></i><i class="fa fa-arrow-left" aria-hidden="true"></i> <b> Enroll Users</b>';
$string['add_all_users'] = ' <i class="fa fa-arrow-left" aria-hidden="true"></i><i class="fa fa-arrow-left" aria-hidden="true"></i> <b> Enroll All Users </b>';
$string['addusers'] = 'Add Users';
$string['addusers_help'] = 'Add users Help';
$string['potusers'] = 'Potential users';
$string['potusersmatching'] = 'Potential users matching \'{$a}\'';
$string['extusers'] = 'Existing users';
$string['extusersmatching'] = 'Existing users matching \'{$a}\'';

// program Status Tabs.
$string['allclasses'] = 'All';
$string['newclasses'] = 'New';
$string['activeclasses'] = 'Active';
$string['holdclasses'] = 'Hold';
$string['completed'] = 'Completed';
$string['cancelledclasses'] = 'Cancelled';
$string['sessions'] = 'Sessions';
$string['sessionscourses'] = 'Sessions for Course <b>\'{$a->coursename}\'</b> <span class="course_level_label">({$a->levelname})</span>';
$string['courses'] = 'Courses';
$string['users'] = 'Users';
$string['programusers'] = 'Users for program <b>\'{$a}\'</b>';
$string['activate'] = 'Activate';
$string['programstatusmsg'] = 'Are you sure you want to activate the program?';
$string['viewprogram_assign_users']='Assign Users';
$string['assignusers']="Assign Users";
$string['continue']='Continue';
$string['assignusers']="Assign Users";
$string['assignusers_heading']='Enroll users to program <b>\'{$a}\'</b>';
$string['session_attendance_heading']='Attendance for program <b>\'{$a}\'</b>';
$string['online_session_type']='Online session type';
$string['online_session_type_desc']="online session type for online sessions on program.";
$string['online_session_plugin_info']='Online session type plugins not found.';
$string['select_session_type']='Select session type';
$string['join']='Join';
$string['view_program'] = 'View Program';
$string['addcourses'] = 'Assign Course';
$string['completion_status'] = 'Completion Status';
$string['completion_status_per'] = 'Completion Status (%)';
$string['type'] = 'Type';
$string['trainer'] = 'Trainer';
$string['submitted'] = 'Submitted';
$string['program_self_enrolment'] = '<div class="pl-15 pr-15 pb-15">Are you sure you want to enrol this "<b>{$a}</b>" program?</div>';
$string['program_self_unenrolment'] = '<div class="pl-15 pr-15 pb-15">Are you sure you want to unenrol this "<b>{$a}</b>" program?</div>';
$string['capacity_check'] = "<div class='alert alert-danger'>
                                All Seats are filled.
                            </div>";
$string['updatesession'] = 'Update Session';
$string['addnewsession'] = 'Add a new session';
$string['createinstitute'] = 'Create Location';
$string['employeeid'] = 'Employee ID';
$string['program_info'] = 'Program Info';
$string['program_info'] = 'Program Info';
$string['sessionstartdateerror1'] = 'Session start date should greater than program start date.';
$string['sessionstartdateerror2'] = 'Session start date should less than program end date.';
$string['sessionenddateerror1'] = 'Session end date should greater than program start date.';
$string['sessionenddateerror2'] = 'Session end date should less than program end date.';
$string['confirmation'] = 'Confirmation';
$string['unassign'] = 'Un-assign';
$string['roomid'] = 'List out the rooms from program.';
$string['roomid_help'] = 'List out the rooms from program.';
$string['programcompletion'] = 'Program completion settings';
$string['program_anysessioncompletion'] = 'Program is complete when ANY of the below sessions are complete';
$string['program_allsessionscompletion'] = 'Program is complete when ALL sessions are complete';
$string['program_anycoursecompletion'] = 'Program is complete when ANY of the below courses are complete';
$string['program_allcoursescompletion'] = 'Program is complete when ALL courses are complete';
$string['program_completion_settings'] = 'Program completion settings';
$string['sessiontracking'] = 'Program completion sessions requirements';
$string['session_completion'] = 'Sessions completion';
$string['coursetracking'] = 'Level completion courses requirements';
$string['course_completion'] = 'Courses completion';
$string['program_donotsessioncompletion'] = 'Do not indicate sessions program completion';
$string['program_donotcoursecompletion'] = 'Do not indicate courses program completion';
$string['select_courses']='Select Courses';
$string['select_sessions']='Select Sessions';
$string['eventprogramcreated'] = 'Local program created';
$string['eventprogramupdated'] = 'Local program updated';
$string['eventprogramcancel'] = 'Local program cancelled';
$string['eventprogramcompleted'] = 'Local program completed';
$string['eventprogramcompletions_settings_created'] = 'Local program completion settings added.';
$string['eventprogramcompletions_settings_updated'] = 'Local program completion settings updated';
$string['eventprogramcourses_created'] = 'Local program course added';
$string['eventprogramcourses_deleted'] = 'Local program course removed';
$string['eventprogramcourses_deleted'] = 'Local program course removed';
$string['eventprogramdeleted'] = 'Local program deleted';
$string['eventprogramhold'] = 'Local program holded';
$string['eventprogrampublish'] = 'Local program published';
$string['eventprogramsessions_created'] = 'Local program sessions created';
$string['eventprogramsessions_deleted'] = 'Local program sessions deleted';
$string['eventprogramsessions_updated'] = 'Local program sessions updated';
$string['eventprogramusers_created'] = 'Local program users enrolled';
$string['eventprogramusers_deleted'] = 'Local program users un enrolled';
$string['eventprogramusers_updated'] = 'Local program users updated';
$string['eventprogramfeedbacks_created'] = 'Local program feedbacks created';
$string['eventprogramfeedbacks_updated'] = 'Local program feedbacks updated';
$string['eventprogramfeedbacks_deleted'] = 'Local program feedbacks deleted';
$string['eventprogramattendance_created_updated'] = 'Local program sessions attendance present/absent';
$string['publish'] = 'Publish';
$string['release_hold'] = 'Release Hold';
$string['cancel'] = 'Cancel';
$string['hold'] = 'Hold';
$string['mark_complete'] = 'Mark Complete';
$string['enroll'] = 'Enroll';
$string['valnamerequired'] = 'Missing Program name';
$string['numeric'] = 'Only numeric values';
$string['positive_numeric'] = 'Only positive numeric values';
$string['capacity_enroll_check'] = 'Capacity must be greater than allocated seats.';
$string['vallocationrequired'] = 'Please select location.';
$string['new_program'] = 'New';
$string['active_program'] = 'Active';
$string['cancel_program'] = 'Cancelled';
$string['hold_program'] = 'Hold';
$string['completed_program'] = 'Completed';
$string['completed_user_program'] = 'You have not completed this program';
$string['programlogo'] = 'Banner Image';
$string['image_help'] ='Search and select a banner image for the Program';
$string['completion_settings_tab'] = 'Completion Criteria';
$string['target_audience_tab'] = 'Target Audience';
$string['requested_users_tab'] = 'Requested Users';
$string['program_completion_tab_info'] = 'No program criteria found.';

$string['program_completion_tab_info_levelsall'] = 'This program will completed when the below listed <b> all levels </b> should be completed.';

$string['program_completion_tab_info_alllevels'] = 'This program will completed when this "<b> {$a} </b>" listed <b> all levels </b> should be completed.';

$string['program_completion_tab_info_anylevels'] = 'This program will completed when this "<b> {$a} </b>" listed <b> any levels </b> should be completed.';

$string['program_level_completion_tab_info'] = 'No level criteria found.';

$string['program_level_completion_tab_info_coursesall'] = 'This program will completed when the below listed <b> all courses </b> should be completed.';

$string['program_level_completion_tab_info_allcourses'] = 'This level will completed when this "<b> {$a} </b>" listed <b> all courses </b> should be completed.';

$string['program_level_completion_tab_info_anycourses'] = 'This level will completed when this "<b> {$a} </b>" listed <b> any courses </b> should be completed.';

$string['audience_department'] = '<p>This program will eligible below-listed target audience.</p>
<p> <b>Bussiness Units :</b> {$a}</p>';
$string['audience_group'] = '<p> <b>Groups :</b> {$a}</p>';
$string['audience_hrmsrole'] = '<p> <b>Hrms Role :</b> {$a}</p>';
$string['audience_designation'] = '<p> <b>Designations :</b> {$a}</p>';
$string['audience_location'] = '<p> <b>Locations :</b> {$a}</p>';
$string['no_trainer_assigned'] = 'No trainers assigned';
$string['requestforenroll'] = 'Request';
$string['requestavail'] = 'Requested users not available';
$string['nocoursedesc'] = 'No description provided';
$string['level'] = 'Level';
$string['addlevel'] = 'Add Level';
$string['enrolluserssuccess'] = '<b>{$a->changecount}</b> Employee(s) successfully enrolled to this <b>"{$a->program}"</b> program .';
$string['unenrolluserssuccess'] = '<b>{$a->changecount}</b> Employee(s) successfully un enrolled from this <b>"{$a->program}"</b> program .';

$string['enrollusers'] = 'program <b>"{$a}"</b> enrollment is in process...';

$string['un_enrollusers'] = 'program <b>"{$a}"</b> un enrollment is in process...';
$string['click_continue'] = 'Click on continue';
$string['unassign_courses_confirm'] = 'Are you sure you want to un-assign this course from the level.';
$string['unassign'] = 'Yes';
$string['noassignedcourses'] = 'No Courses Assigned';
$string['reschedule'] = 'Reschedule';
$string['cannotdeleteall'] = 'You cannot delete this program. If you want to delete this, first unenroll users from the sessions and delete program.';
$string['editlevel'] = 'Edit Level';
$string['deletelevel'] = 'Delete Level';
$string['cannotunassign_courses_confirm'] = 'You cannot un-assign this course. If you want to un-assign this course, first unenroll users from the sessions and un-assign course.';
$string['cannotdeletesession'] = 'You can not delete the session, because user already existed in this session.';
$string['mincapacity'] = 'Min Capacity';
$string['maxcapacity'] = 'Max Capacity';
$string['selectbc_location_type'] = 'Select program location type';
$string['selectprogram_location'] = 'Select program location';
$string['selectlocation_room'] = 'Select program location room';
$string['selecttrainer'] = 'Select trainer';
$string['selectmincapacity'] = 'Enter minimum capacity';
$string['selectmaxcapacity'] = 'Enter maximum capacity';
$string['lessmincapacity'] = 'Min capacity must be less than Max capacity';
$string['startdatelessthanenddate'] = 'Session Start Date must be less than Session End Date';
$string['cannotdeletelevel'] = 'You cannot delete this level. If you want to delete this level, first unenroll users from the sessions under level and delete level.';
$string['confirmcancelsession'] = 'Are you sure, you want to cancel this session?';
$string['confirmreschedulesession'] = 'Are you sure, you want to Re schedule this session?';

$string['event:programcreated'] = 'Program Created';
$string['event:programupdated'] = 'Program Updated';
$string['event:programdeleted'] = 'Program Deleted';
$string['event:programlevelcreated'] = 'Program Level Created';
$string['event:programlevelupdated'] = 'Program Level Updated';
$string['event:bootcamlevelpdeleted'] = 'Program Level Deleted';
$string['event:bclevelcoursecreated'] = 'Program Level Course Created';
$string['event:bclevelcourseupdated'] = 'Program Level Course Updated';
$string['event:bclevelcoursedeleted'] = 'Program Level Course Deleted';
$string['event:bclevelcoursesession_created'] = 'Program Level Course Session Created';
$string['event:bclevelcoursesession_updated'] = 'Program Level Course Session Updated';
$string['event:bclevelcoursesession_deleted'] = 'Program Level Course Session Deleted';
$string['event:program_completions_settings_updated'] = 'Program Completion Settings updated';
$string['event:session_users_enrol'] = 'User enrolled to session';
$string['event:session_users_unenrol'] = 'User un-enrolled to session';
$string['event:sessiondeleted'] = 'Program Session deleted';
$string['event:program_completions_settings_created'] = 'Program level completion criteria created';
$string['confirmschedulesession'] = 'Are you sure, you want to Enroll to this Session?';
$string['sessionschedule'] = 'Session Date, Time';
$string['sessionlocation'] = 'Session Room, Location';
$string['levelcompleted'] = 'COMPLETED';
$string['levelinprogress'] = 'IN PROGRESS';
$string['addstream'] = 'Create Stream';
$string['updatestream'] = 'Update Stream';
$string['streams_help'] = 'Select a stream to categorize the Program info';
$string['seats'] = 'Seats';
$string['create_streams'] = 'Create Streams';
$string['view_streams'] = 'View Streams';
$string['programusers'] = 'Program Users';
$string['noprogramstreams'] = 'No program Streams are there';
$string['downloadreport'] = 'Download';
$string['nooflevels'] = 'No of Levels';
$string['programdownloadreport'] = 'Program Users Report';
$string['coursedownloadreport'] = 'Course wise session Report';
$string['upcomingsessions'] = 'Upcoming/Present';
$string['completedsessions'] = 'Completed';
$string['manage_br_programs'] = 'Manage Programs';
// $string['enroll'] = 'Enrol them to program'; //commented because pre defined above.
$string['bulk_enroll'] = 'Bulk Enrolments';
$string['user_exist'] = '{$a} - already enrolled to this program';
$string['im:stats_i'] = '{$a} Users successfully enrolled to this program';
$string['view_programs'] = 'View Programs';
$string['session_enrollusers'] = 'Enroll Users for Session <b>\'{$a}\'</b>';
$string['sessiondays'] = 'Enrollment Closes';
$string['sessionenrolments'] = 'Session Enrollment';
$string['unassign_users'] = 'Unassign Users';
$string['noprogramsuser'] = 'No Programs Available';
$string['tasksessionreminder'] = 'Task Session Reminder';

$string['enrolluserssuccess'] = '<b>{$a->changecount}</b> Employee(s) successfully enrolled to this <b>"{$a->program}"</b> program .';
$string['unenrolluserssuccess'] = '<b>{$a->changecount}</b> Employee(s) successfully un enrolled from this <b>"{$a->program}"</b> program .';

$string['enrollusers'] = 'program <b>"{$a}"</b> enrollment is in process...';

$string['un_enrollusers'] = 'program <b>"{$a}"</b> un enrollment is in process...';
$string['click_continue'] = 'Click on continue';
$string['startdatetime'] = 'Start Date Time';
$string['enddatetime'] = 'End Date Time';
$string['session_room_reserved'] = 'Room {$a->roomname} is already reserved at this time slot by {$a->sessionname}';
$string['inactiveconfirm'] = 'Are you sure you want to inactive this "<b>{$a}</b>" program?';
$string['activeconfirm'] = 'Are you sure you want to active this "<b>{$a}</b>" program?';
$string['programactivated'] = 'Local program activated';
$string['programinactivated'] = 'Local program inactivated';
$string['program:activeprogram'] = 'Active program';
$string['program:inactiveprogram'] = 'Inactive program';
$string['generaldetails'] = 'General Details';
$string['target_audiencedetails'] = 'Target Audience';
$string['selfenroll'] = 'selfenroll';
$string['streams'] = 'streams';
$string['image'] = 'image';
$string['makeinactive']='Make Inactive';
$string['makeactive'] = 'Make Active';
$string['program:removecourse'] = 'Remove courses';
$string['program:viewlevel'] = 'View level';
$string['program:addcourse'] = 'Add course';
$string['program:createlevel'] = 'Create levels';
$string['program:deletelevel'] = 'Delete levels';
$string['program:editlevel'] = 'Edit levels';
$string['program:enrolsession'] = 'Enroll sessions';
$string['program:managelevel'] = 'Manage levels';
$string['points'] = 'Points';
$string['open_pointsprogram'] = 'points';
$string['open_pointsprogram_help'] = 'Points for the Program default(0)';
$string['cannotdeleteprogram_help'] = 'Already a couple of Programs created with this Stream,so you cannot delete.';
$string['cannotdeleteprogram'] ='stream';
$string['enrolusers'] = 'Enroll Users';
$string['tagarea_program'] = 'Programs';
$string['enableplugin'] = 'Currently Program enrolment method is disabled. <a href="{$a}" target="_blank"> <u>Click here</u></a> to enable the Enrolment method';
$string['manageplugincapability'] = 'Currently Program enrolment method is disabled. Please contact the Site administrator.';
$string['attendance'] = 'Attendance';
$string['bulkenrolments'] = 'Bulk Enrolments';
$string['add_certificate'] = 'Add Certificate';
$string['add_certificate_help'] = 'If you want to issue a certificate when user completes this program, please enable here and select the template in next field (Certificate template)';
$string['select_certificate'] = 'Select Certificate';
$string['certificate_template'] = 'Certificate template';
$string['certificate_template_help'] = 'Select Certificate template for this program';
$string['err_certificate'] = 'Missing Certificate template';
$string['eventprogramusers_completed'] = 'Program Completed by user';
$string['unableto_download_msg'] = "Still you didn't complete this program so you cannot download the certificate";
$string['messageprovider:program_completion'] = 'Program Completion';
$string['messageprovider:program_enrol'] = 'Enrol Program ';
$string['messageprovider:program_level_completion'] = 'Program level completion';
$string['messageprovider:program_unenroll'] = 'Program unenrollment notification';
$string['inprogress_program'] = 'My Program';
$string['completed_program'] = 'My Program';


/* Strings added by Pallavi Veerla*/
$string['program_not_found'] = 'program not found!';
$string['dont_have_permissions'] = 'You donot have permissions';
$string['not_completed'] = 'Not Completed';
$string['present'] = 'Present';
$string['absent'] = 'Absent'; 
$string['not_available'] = 'NA'; 
$string['reset_selected'] = 'Reset Selected';
$string['user_enrollments'] = 'User enrollments'; 
$string['whats_next'] = 'What\'s next?'; 
$string['do_u_want_to_create_level'] = 'Do you want to <b>Create Level</b>'; 
$string['go_to'] = 'Go To'; 
$string['do_u_want_to_add_users'] = 'Do you want to <b>Add Users</b>'; 
$string['go_to'] = 'Timings';
$string['user_added_course_in_program_level'] = 'The user with "{$a->username} ({$a->id})" has added course in program ({$a->programid}) level ({$a->levelid}) with id  "{$this->objectid}"';
$string['user_deleted_course_in_program_level'] = 'The user with id "{$a->username} ({$a->id})" has deleted course in program ({$a->programid}) level ({$a->levelid}) with id "{$a->objectid}"';
$string['user_created_course_session'] = 'The user with id "{$a->userid}" created course session with id "{$a->objectid}"';
$string['user_updated_course_in_program_level'] = 'The user with id "{$a->username} ({$a->id})" has updated course in program ({$a->programid}) level ({$a->levelid}) with id "{$a->objectid}"';
$string['user_created_program_level'] = 'The user with "{$a->username} ({$a->id})" has created the program level with id " {$a->objectid} "';
$string['user_deleted_program_level'] = 'The user with id "{$a->username} ({$a->id})" has deleted the program level with id "{$a->objectid}"';
$string['user_updated_program_level'] = 'The user with id "{$a->username} ({$a->id})" has updated the program level with id "{$a->objectid}"';
$string['user_activated_program'] = 'The user with id "{$a->username} ({$a->id})" has activated the program with id "{$a->objectid}"';
$string['user_added_course_to_program_level'] = 'The user with id "{$a->userid}" has added a course to the program level';
$string['user_updated_program_completion_by_adding_course'] = 'The user with userid {$a->userid} has updated the program completion criteria by adding courses to program with programid {{$a->programid}}';
$string['user_deleted_program'] = 'The user with id "{$a->username} ({$a->id})" has deleted the program with id "{$a->objectid}"';
$string['user_created_program'] = 'The user with id "{$a->username} ({$a->id})" has created the program with id "{$a->objectid}"';
$string['user_inactivated_program'] = 'The user with id "{$a->username} ({$a->id})" has inactivated the program with id "{$a->objectid}"';
$string['user_updated_program'] = 'The user with id "{$a->username} ({$a->id})" has updated the program with id "{$a->objectid}"';
$string['user_completed_program'] = 'The user with id "({$a->userid})" has completed the program with id "{$a->objectid}"';
$string['user_enrolled_user_from_program'] = 'The user with id "({$a->userid})" has enrolled user with id "{$a->relateduserid}" from program with id "{$a->programid}"';
$string['user_unenrolled_user_from_program'] = 'The user with id "({$a->userid})" has enrolled user with id "{$a->relateduserid}" from program with id "{$a->objectid}"';
$string['user_enrolled_user_to_session'] = 'The user with id "({$a->userid})" has enrolled user with id "{$a->relateduserid}" to session with id "{$a->objectid}"';
$string['user_unenrolled_user_to_session'] = 'The user with id "({$a->userid})" has enrolled user with id "{$a->relateduserid}" to session with id "{$a->objectid}"';
$string['traineralreadymapped'] = 'Trainer Already added to a session in same duration';
$string['selectstrem'] = 'Select Stream'; 
$string['savecontinue'] = 'Save & Continue';
$string['assign'] = 'Assign';
$string['save'] = 'Save';
$string['previous'] = 'Previous';
$string['skip'] = 'Skip';
$string['cancel'] = 'Cancel';
$string['timings'] = 'Timings';
$string['request'] = 'Request';
$string['location'] = 'Location';
$string['search'] = 'Search';
$string['streams'] = 'Streams';
$string['actions'] = 'Actions';
$string['programerror_in_fetching_data'] = 'Unable to fetch data in programs';
$string['assigncourses'] = 'Assign Courses';
$string['program_reports'] = 'Program Reports';
$string['pleaseselectorganization'] = 'Please Select Company';
$string['enrolled_program'] = 'My Program';
$string['programname'] = 'Program Name';
$string['listtype'] = 'LIST';
$string['cardtype'] = 'CARD';
$string['active'] = 'Make Active';
$string['inactive'] = 'Make Inactive';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['listicon'] ='icon fa fa-bars fa-fw';
$string['cardicon'] ='icon fa fa-fw fa-th';

$string['notassigned'] = 'N/A';
$string['add_classroom'] = 'Classroom Courses';

$string['add_classroom_help'] = 'Select

* Checked - If you want to let classroom courses.

* Unchecked - If you want to let all courses';
$string['listofclassrooms'] = 'List of Classrooms';
$string['showless'] = 'Show Less';
$string['showmore'] = 'Show More';
$string['level_completion'] = 'Level Completion';
$string['program_anylevelcompletion'] = 'Program is complete when ANY SELECTED level is complete';
$string['levelcompletion'] = 'Level Completion';
$string['taskprogramcompletion'] = 'Task Program Completion';
$string['addprogramcompletioncriteria'] = 'Add Program Completion Criteria';
$string['updateprogramcompletioncriteria'] = 'Update Program Completion Criteria';
$string['leveltracking'] = 'Level Requirements';
$string['alllevels'] = 'All Levels';
$string['program_selectedlevelscompletion'] = 'Program is complete when ALL SELECTED levels are complete';
$string['updatecompletioncriteria'] = 'Update level completion criteria';
$string['allcourses'] = 'All Courses';
$string['section_allcoursescompletion'] = 'Level is completed when ALL SELECTED courses are completed';
$string['section_anycoursecompletion'] = 'Level is completed when ANY SELECTED courses are completed';
$string['select_levels'] = 'Please select levels';
$string['levelcompletion'] = 'Level Completion';
$string['taskprogrampublishedusers'] = 'Enrol Published program users';
$string['addcompletioncriteria'] = 'Add Level Completion Criteria';
$string['reset_completions_exist'] = 'Reset Completions of program';
$string['err_settingslocked'] = 'One or more persons have already completed the program so the settings have been locked. Unlocking the completion criteria settings will reset any existing user program completion and may cause confusion.';
$string['unlockcompletiondelete'] = 'Reset completion criteria';
$string['taskprogramreminder'] = 'Program reminder';
$string['im:user_alreadyenrolled'] = 'User {$a} is already enrolled to this program';
$string['autoenrolenable'] = 'Enable Auto Enrol';

$string['programexpiry'] = 'Program Expiry';
$string['remaining_days_left'] = 'Completion Due (Days):';
$string['remaining_overdue_days'] = 'Completion Overdue (Days):';
$string['submit_feedback'] = 'Submit Feedback';
$string['auto_extension'] = 'Enable Auto Extension';
$string['extensionperiod'] = 'Extension Period (Days)';
$string['auto_extension_help'] = 'Auto Extension program for users';
$string['extensionperiod_help'] = 'Extension Period days count for users';
$string['extensionperiod_required'] = 'Extension Period Days Required';
$string['extensionperiod_negative'] = 'Extension Period Days cannot be Negative';
$string['taskauto_extension'] = 'Program Auto Extension Task';
$string['programskill'] = 'Skill';
$string['programlevel'] = 'Skill Level';
$string['no_courses_assigned'] = 'No courses assigned';
$string['start_course'] = 'Start course';
$string['unenrol'] = 'Unenrol';
$string['otherdetailsdetails'] = 'Other Details';
$string['open_skillonlineexam_help'] = 'Skill achieved on completion of program';
$string['open_levelonlineexam_help'] = 'Level achieved on completion of program';
