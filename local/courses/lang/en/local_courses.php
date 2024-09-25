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
 * @subpackage local_courses
 */
$string['pluginname'] = 'Courses';
$string['course'] = 'Course';
$string['organization']='Company';
$string['mooc'] = 'MOOC';
$string['classroom'] = 'Classroom';
$string['elearning'] = 'E-Learning';
$string['learningplan'] = 'Learning Path';
$string['type'] = 'Type';
$string['category'] = 'Category';
$string['enrolled'] = 'Enrollments';
$string['completed'] = 'Completions';
$string['manual_enrolment'] = 'Manual Enrollment';
$string['add_users']='<< Add Users';
$string['remove_users']='Remove Users >>';
$string['employeesearch']='Filter';
$string['agentsearch']='Agent Search';
$string['empnumber']='Learner ID';
$string['email']='Email';
$string['band'] = 'Band';
$string['departments']='Clients';
$string['sub_departments']='LOB';
$string['sub-sub-departments']='Regions';
$string['designation'] = 'Designation';
$string['im:already_in'] = 'The user "{$a}" was already enroled to this course';
$string['im:enrolled_ok'] = 'The user "{$a}" has successfully enroled to this course ';
$string['im:error_addg'] = 'Error in adding group {$a->groupe}  to course {$a->courseid} ';
$string['im:error_g_unknown'] = 'Error, unkown group {$a} ';
$string['im:error_add_grp'] = 'Error in adding grouping {$a->groupe} to course {$a->courseid}';
$string['im:error_add_g_grp'] = 'Error in adding group {$a->groupe} to grouping {$a->groupe}';
$string['im:and_added_g'] = ' and added to Moodle\'s  group  {$a}';
$string['im:error_adding_u_g'] = 'Error in adding to group  {$a}';
$string['im:already_in_g'] = ' already in group {$a}';
$string['im:stats_i'] = '{$a} enroled &nbsp&nbsp';
$string['im:stats_g'] = '{$a->nb} group(s) created : {$a->what} &nbsp&nbsp';
$string['im:stats_grp'] = '{$a->nb} grouping(s) created : {$a->what} &nbsp&nbsp';
$string['im:err_opening_file'] = 'error opening file {$a}';
$string['im:user_notcostcenter'] = '{$a->user} not assigned to {$a->csname} costcenter';
$string['mass_enroll'] = 'Bulk enrolments';
$string['mass_enroll_info'] =
"<p>
With this option you are going to enrol a list of known users from a file with one account per line
</p>
<p>
<b> The firstline </b> the empty lines or unknown accounts will be skipped. </p>
<p>
<b>The first one must contains a unique email of the target user </b>
</p>";
$string['firstcolumn'] = 'First column contains';
$string['creategroups'] = 'Create group(s) if needed';
$string['creategroupings'] = 'Create  grouping(s) if needed';
$string['enroll'] = 'Enrol them to my course';
$string['im:user_unknown'] = 'The user with an employee code "{$a}" doesn\'t exists in this company';
$string['points'] = 'Points';
$string['createnewcourse'] = '<i class="icon popupstringicon fa fa-book" aria-hidden="true"></i>Create Course <div class="popupstring">Here you can create course</div>';
$string['editcourse'] = '<i class="icon popupstringicon fa fa-book" aria-hidden="true"></i>Update Course <div class="popupstring">Here you can update course</div>';
$string['description']   = 'User with Username "{$a->userid}"  created the course  "{$a->courseid}"';
$string['desc']   = 'User with Username "{$a->userid}" has updated the course  "{$a->courseid}"';
$string['descptn']   = 'User with Username "{$a->userid}" has deleted the course with courseid  "{$a->courseid}"';
$string['usr_description']   = 'User with Username "{$a->userid}" has created the user with Username  "{$a->user}"';
$string['usr_desc']   = 'User with Username "{$a->userid}" has updated the user with Username  "{$a->user}"';
$string['usr_descptn']   = 'User with Username "{$a->userid}" has deleted the user with userid  "{$a->user}"';
$string['ilt_description']   = 'User with Username "{$a->userid}"  created the ilt  "{$a->f2fid}"';
$string['ilt_desc']   = 'User with Username "{$a->userid}" has updated the ilt "{$a->f2fid}"';
$string['ilt_descptn']   = 'User with Username "{$a->userid}" has deleted the ilt "{$a->f2fid}"';
$string['coursecompday'] = 'Course Completion Days';
$string['coursecreator'] = 'Course Creator';
$string['coursecode'] = 'Course Code';
$string['addcategory'] = '<i class="fa fa-book popupstringicon" aria-hidden="true"></i><i class="fa fa-book secbook popupstringicon cat_pop_icon" aria-hidden="true"></i> Create New Category <div class= "popupstring"></div>';
$string['editcategory'] = '<i class="fa fa-book popupstringicon" aria-hidden="true"></i><i class="fa fa-book secbook popupstringicon cat_pop_icon" aria-hidden="true"></i> Update Category <div class= "popupstring"></div>';
$string['coursecat'] = 'Course Categories';
$string['deletecategory'] = 'Delete Category';
$string['top'] = 'Top';
$string['parent'] = 'Parent';
$string['actions'] = 'Actions';
$string['count'] = 'Number of Courses';
$string['categorypopup'] = 'Category {$a}';
$string['missingtype'] = 'Missing Type';
$string['catalog'] = 'Catalog';
$string['nocoursedesc'] = 'No description provided';
$string['apply'] = 'Apply';
$string['open_path'] = 'Costcenter';
$string['uploadcoursespreview'] = 'Upload courses preview';
$string['uploadcoursesresult'] = 'Upload courses results';
$string['uploadcourses'] = 'Upload courses';
$string['coursefile'] = 'File';
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['rowpreviewnum'] = 'Preview rows';
$string['preview'] = 'Preview';
$string['courseprocess'] = 'Course process';
$string['shortnametemplate'] = 'Template to generate a shortname';
$string['templatefile'] = 'Restore from this file after upload';
$string['reset'] = 'Reset course after upload';
$string['defaultvalues'] = 'Default course values';
$string['enrol'] = 'Enrol';
$string['courseexistsanduploadnotallowedwithargs'] = 'Course is already exists with shortname "{$a}", please choose other unique shortname.';
$string['canonlycreatecourseincategoryofsameorganisation'] = 'You can only create the course under your assigned organisation';
$string['canonlycreatecourseincategoryofsameorganisationwithargs'] = 'Cannot create a course under the category \'{$a}\'';
$string['createcategory'] = 'Create New Category';
$string['manage_course'] = 'Manage Course';
$string['manage_courses'] = 'Manage Courses';
$string['leftmenu_browsecategories'] = 'Custom Fields';
$string['courseother_details'] = 'Other Details';
$string['view_courses'] = 'view courses';
$string['deleteconfirm'] = 'Are you sure, you want to delete "<b>{$a->name}</b>" course?</br> Once deleted, it can not be reverted.';
$string['department'] = 'Client';
$string['coursecategory'] = 'Category';
$string['fullnamecourse'] = 'Fullname';
$string['coursesummary'] = 'Summary';
$string['courseoverviewfiles'] = 'Banner image';
$string['startdate'] = 'Start Date';
$string['enddate'] = 'End Date';
$string['program'] = 'Program';
$string['certification'] = 'Certification';
$string['create_newcourse'] = 'Create Course';
$string['userenrolments'] = 'User enrollments';
$string['certificate'] = 'Certificate';
$string['points_positive'] = 'Points must be greater than 0';
$string['coursecompletiondays_positive'] ='Completion days must be greater than 0';
$string['enrolusers'] = 'Enrol Users';
$string['grader'] = 'Grader';
$string['activity'] = 'Activity';
$string['courses'] = 'Courses';
$string['nocategories'] = 'No categories available';
$string['nosameenddate'] = '"End date" should not be less than "Start date"';
$string['coursemanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['help_1'] = '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>fullname</td><td>Fullname of the course.</td></tr>
<tr><td>course-code</td><td>course-code of the course.</td></tr>
<tr><td>category_code</td><td>Enter the category code(you can find this code in Manage Categories page).</td></tr>
<tr><td>coursetype</td><td>Type of the course(Comma seperated)(Ex:classroom,elearning,certification,learningpath,program).</td></tr>
<tr><td>format</td><td>Enter course format(Ex: singleactivity,social,toggletop,topics,weeks).</td></tr>';

$string['help_2'] = '</td></tr>
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Normal Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>Summary</td><td>Summary of the course.</td></tr>
<tr><td>Cost</td><td>Cost of the course.</td></tr>
<tr><td>Client_code</td><td>Provide Client code. Client must already exist in system as part of Company hierarchy.</td></tr>
<tr><td>commercial_unit_code</td><td>Enter LOB Code. Client must already exist under specified Client in system as part of Company hierarchy.</td></tr>
<tr><td>commercial_area_code</td><td>Enter LOB Code. LOB must already exist under specified LOB in system as part of Company hierarchy.</td></tr>
<tr><td>territory_code</td><td>Enter Territory Code. Territory must already exist under specified LOB in system as part of Company hierarchy.</td></tr>
<tr><td>Points</td><td>Points for the course.</td></tr>
<tr><td>completiondays</td><td>completiondays should be greater than \'0\'. i.e, 1,2,3..etc</td></tr>
</table>';
$string['back_upload'] = 'Back to upload courses';
$string['manual'] = 'Help manual';
$string['enrolledusers'] = 'Enrolled users';
$string['notenrolledusers'] = 'Not enrolled users';
$string['finishbutton'] = 'Finish';
$string['updatecourse'] = 'Update Course';
$string['course_name'] = 'Course Name';
$string['completed_users'] = 'Completed Users';
$string['course_filters'] = 'Course Filters';
$string['back'] = 'Back';
$string['sample'] = 'Sample';
$string['selectdept'] = '--Select Client--';
$string['selectsubdept'] = '--Select LOB--';
$string['selectorg'] = '--Select Company--';
$string['selectcat'] = '--Select Category--';
$string['select_cat'] = '--Select Categories--';
$string['selectcoursetype'] = '--Select Course Type--';
$string['reset'] = 'Reset';
$string['err_category'] = 'Please select Category';
$string['availablelist'] = '<b>Available Users ({$a})</b>';
$string['selectedlist'] = 'Selected users';
$string['status'] = 'Status';
$string['select_all'] = 'Select All';
$string['remove_all'] = 'Un Select All';
$string['not_enrolled_users'] = '<b>Not Enrolled Users ({$a})</b>';
$string['enrolled_users'] = '<b> Enrolled Users ({$a})</b>';
$string['remove_selected_users'] = '<b> Un Enroll Users </b><i class="fa fa-arrow-right" aria-hidden="true"></i><i class="fa fa-arrow-right" aria-hidden="true"></i>';
$string['remove_all_users'] = '<b> Un Enroll All Users </b><i class="fa fa-arrow-right" aria-hidden="true"></i><i class="fa fa-arrow-right" aria-hidden="true"></i>';
$string['add_selected_users'] = '<i class="fa fa-arrow-left" aria-hidden="true"></i><i class="fa fa-arrow-left" aria-hidden="true"></i><b> Enroll Users</b>';
$string['add_all_users'] = ' <i class="fa fa-arrow-left" aria-hidden="true"></i><i class="fa fa-arrow-left" aria-hidden="true"></i> <b> Enroll All Users </b>';
$string['auto_enrol'] = 'Auto Enroll';
$string['need_manage_approval'] = 'Need Manager Approval';
$string['need_securedcourse'] = 'Secured Course';
$string['costcannotbenonnumericwithargs'] ='Cost should be in numeric but given "{$a}"';
$string['pointscannotbenonnumericwithargs'] ='Points should be in numeric but given "{$a}"';
$string['need_self_enrol'] = 'Need Self Enroll';
$string['enrolluserssuccess'] = '<b>{$a->changecount}</b> Employee(s) successfully enrolled to this <b>"{$a->course}"</b> course .';
$string['unenrolluserssuccess'] = '<b>{$a->changecount}</b> Employee(s) successfully un enrolled from this <b>"{$a->course}"</b> course .';

$string['enrollusers'] = 'Course <b>"{$a}"</b> enrollment is in process...';

$string['un_enrollusers'] = 'Course <b>"{$a}"</b> un enrollment is in process...';
$string['click_continue'] = 'Click on continue';
$string['bootcamp']= 'XSeeD';
$string['manage_br_courses'] = 'Manage courses';
$string['nocourseavailiable'] = 'No Courses Available';
$string['taskcoursenotification'] = 'Course Notification Task';
$string['taskcoursereminder'] = 'Course Reminder Task';
$string['pleaseselectorganization'] = 'Please Select Company';
$string['pleaseselectcategory'] = 'Please Select Category';
$string['enablecourse'] = 'Are you sure, want Active course <b>\'{$a}\'</b>?';
$string['disablecourse'] = 'Are you sure, want In-active course <b>\'{$a}\'</b>?';
$string['courseconfirm'] = 'Confirm';
$string['open_pathcourse_help'] = 'Organisation for the course';
$string['open_departmentidcourse_help'] = 'Client for the course';
$string['open_identifiedascourse_help'] = 'Type of the course (multi select)';
$string['open_pointscourse_help'] = 'Points for the course default (0)';
$string['selfenrolcourse_help'] = 'Check yes if required self enrollment to the course';
$string['approvalrequiredcourse_help'] = 'Check yes if required to enable request manager for enrolling to the course';
$string['open_costcourse_help'] = 'Cost of the course';
$string['open_skillcourse_help'] = 'Skill achieved on completion of course';
$string['open_levelcourse_help'] = 'Level achieved on completion of course';
$string['open_pathcourse'] = 'Organisation';
$string['open_departmentidcourse'] = 'Client';
$string['open_identifiedascourse'] = 'Type';
$string['open_pointscourse'] = 'Points';
$string['selfenrolcourse'] = 'self enrollment';
$string['approvalrequiredcourse'] = 'request manager for enrolling';
$string['open_costcourse'] = 'Cost';
$string['open_skillcourse'] = 'Skill ';
$string['open_levelcourse'] = 'Level';
$string['notyourorg_msg'] = 'You have tried to view this activity is not belongs to your Company';
$string['notyourdept_msg'] = 'You have tried to view this activity is not belongs to your Client';
$string['notyourorgcourse_msg'] = 'You have tried to view this course is not belongs to your Company';
$string['notyourdeptcourse_msg'] = 'You have tried to view this course is not belongs to your Client';
$string['notyourorgcoursereport_msg'] = 'You have tried to view this Grader report is not your Company course, so you cann\'t access this page';
$string['need_manager_approval '] = 'need_manager_approval';
$string['categorycode'] = 'Category Code';
$string['categorycode_help'] = 'The Category Code of a course category is only used when matching the category against external systems and is not displayed anywhere on the site. If the category has an official code name it may be entered, otherwise the field can be left blank.';

$string['categories'] = 'Sub Categories :  ';
$string['makeactive'] = 'Make Active';
$string['makeinactive'] = 'Make Inactive';
$string['courses:bulkupload'] = 'Bulk upload';
$string['courses:create'] = 'Create course';
$string['courses:delete'] = 'Delete  course';
$string['courses:grade_view'] = 'Grade view';
$string['courses:manage'] = 'Manage courses';
$string['courses:report_view'] = 'Report view';
$string['courses:unenrol'] = 'Unenrol course';
$string['courses:update'] = 'Update course';
$string['courses:view'] = 'View course';
$string['courses:visibility'] = 'Course visibility';
$string['courses:enrol'] = 'Course enrol';

$string['reason_linkedtocostcenter'] = 'As this Course category is linked with the Company/Client, you can not delete this category';
$string['reason_subcategoriesexists'] = 'As we have sub-categories in this Course category, you can not delete this category';
$string['reason_coursesexists'] = 'As we have courses in this Course category, you can not delete this category';
$string['reason'] = 'Reason';
$string['completiondayscannotbeletter'] = 'Cannot create course with completion days as {$a} ';
$string['completiondayscannotbeempty'] = 'Cannot create course without completion days.';
$string['tagarea_courses'] = 'Courses';
$string['subcategories'] = 'Subcategories';
$string['tag'] = 'Tag';
$string['tag_help'] = 'tag';
$string['open_subdepartmentcourse_help'] = 'LOB of the course';
$string['open_subdepartmentcourse'] = 'LOB';
$string['suspendconfirm'] = 'Confirmation';
$string['activeconfirm'] = 'Are you sure to make category active ?';
$string['inactiveconfirm'] = 'Are you sure to make category inactive ?';
$string['yes'] = 'Confirm';
$string['no'] = 'Cancel';
$string['add_certificate'] = 'Add Certificate';
$string['add_certificate_help'] = 'If you want to issue a certificate when user completes this course, please enable here and select the template in next field (Certificate template)';
$string['select_certificate'] = 'Select Certificate';
$string['certificate_template'] = 'Certificate template';
$string['certificate_template_help'] = 'Select Certificate template for this course';
$string['err_certificate'] = 'Missing Certificate template';
$string['download_certificate'] = 'Download Certificate';
$string['unableto_download_msg'] = "Still this user didn't completed the course, so you cann't download the certificate";

$string['completionstatus'] = 'Completion Status';
$string['completiondate'] = 'Completion Date';
$string['nousersmsg'] = 'No users Available';
$string['employeename'] = 'Employee Name';
$string['completed'] = 'Completed';
$string['notcompleted'] = 'Not Completed';
$string['messageprovider:course_complete'] = 'Course Completion Notification';
$string['messageprovider:course_enrol'] = 'Course Enrollment Notification';
$string['messageprovider:course_notification'] = 'Course Notification';
$string['messageprovider:course_reminder'] = 'Course Reminder';
$string['messageprovider:course_unenroll'] = 'Course Unenrollment Notification';
$string['completed_courses'] = 'My Courses';
$string['inprogress_courses'] = 'My Courses';
$string['selectcourse'] = 'Select Course';
$string['enrolmethod'] = 'Enrolment Method';
$string['enrolleddate'] = 'Enrolment date';
$string['deleteuser'] = 'Delete confirmation';
$string['confirmdelete'] = 'Are you sure,do you want to unenroll this user.';
$string['edit'] = 'Edit';
$string['err_points'] = 'Points cannot be empty';
$string['browseevidences'] = 'Browse Evidence';
$string['courseevidencefiles'] = 'Course Evidence';
$string['courseevidencefiles_help'] = 'The course evidence is displayed in the course overview on the Dashboard. Additional accepted file types and more than one file may be enabled by a site administrator. If so, these files will be displayed next to the course summary on the list of courses page.';
$string['browseevidencesname'] = '{$a} Evidences';
$string['selfcompletion'] = 'Self Completion';
$string['selfcompletionname'] = '{$a} Self Completion';
$string['selfcompletionconfirm'] = 'Are you sure,do you want to course "{$a}" self completion.';

// strings added on 23 sept 2020.
$string['saveandcontinue'] = 'Save & Continue';
$string['courseoverview'] = 'Course Overview';
$string['selectlevel'] = 'Select Level';
$string['errorinrequestprocessing'] = 'Error occured while processing requests';
$string['featuredcourses'] = 'Featured Courses';
$string['errorinsubmission'] = 'Error in submission';
$string['recentlyenrolledcourses'] = 'Recently Enrolled Courses';
$string['recentlyaccessedcourses'] = 'Recently Accessed Courses';
$string['securedcourse'] = 'Secured Course';
$string['open_securecourse_course'] = 'Secured Course';
$string['open_securecourse_course_help'] = 'Once selected as yes this course will not be displayed over the mobile app.';
$string['parent_category'] = 'Parent Category';
$string['parent_category_code'] = 'Parent Category Code';
$string['select_skill'] = 'Select Skill';
$string['select_level'] = 'Select Level';
$string['what_next'] = "What's next?";
$string['doyouwantto_addthecontent'] = 'Do you want to <b>add the content</b>';
$string['doyouwantto_enrolusers'] = 'Do you want to <b>enrol users</b>';
$string['goto'] = 'Go to';
$string['search'] = 'Search';
$string['no_users_enrolled'] = 'No users enrolled to this course';
$string['missingfullname'] = 'Please Enter Valid Course Name';
$string['missingshortname'] = 'Please Enter Valid Course Code';
$string['missingtype'] = 'Please Select Type';
$string['course_reports'] = 'Course Reports';
$string['cannotuploadcoursewithlob'] = 'With out Client cannot upload a course with LOB';
$string['categorycodeshouldbedepcode'] = 'Category Code should be under the Client i.e \'{$a}\'';
$string['categorycodeshouldbesubdepcode'] = 'Category Code should be short name of LOB i.e \'{$a}\'';
$string['subdeptshouldunderdepcode'] = 'LOB should be under the Client i.e \'{$a}\'';
$string['course_name_help'] = 'Name for the Course';
$string['coursecode_help'] = 'Code for the Course';
$string['enrolled_courses'] = 'My Courses';
$string['listtype']	='LIST';
$string['cardtype']	='CARD';
$string['listicon'] ='icon fa fa-bars fa-fw';
$string['cardicon'] ='icon fa fa-fw fa-th';
$string['coursetype'] = 'Course Type';
$string['requestforenroll'] = 'Request';
$string['download_courses'] = 'Download Courses';
$string['subcategory'] = 'Sub Categories';
$string['coursename'] = 'Course Name';
$string['lastaccess'] = 'Last Access';
$string['progress'] = 'Progress';
$string['enrollments'] = 'Enrolled';
$string['skill'] = 'Skill';
$string['ratings'] = 'Ratings';
$string['tags'] = 'Tags';
$string['subdepartment'] = 'LOB';
$string['summary'] = 'Summary';
$string['format'] = 'Course Format';
$string['selfenrol'] = 'Self Enrol';
$string['approvalreqdcourse'] = 'Approval Course.';
$string['approvalreqdcourse_help'] = 'Select.

* Yes - If you would like to enforce manager or Company head approval while self enrolling to course
* No - If you would like user to self enroll to course without an approval from manager or Company head';
$string['securecoursereqdcourse'] = 'Secured Course.';
$string['securecoursereqdcourse_help'] = 'Once selected as yes this course will not be displayed on mobile app.';
$string['coursedescription'] = 'Description';
$string['exportcourses'] = 'Export Courses to Excel';
$string['make_inactive'] = 'Make Inactive';
$string['make_active'] = 'Make Active';
$string['departmentnotfound'] ='Client not found i.e \'{$a}\'';
$string['categorycode'] = 'Category Code';
// course types strings
$string['open_coursetypecourse'] = 'Course type';
$string['open_coursetypecourse_help'] = 'Select the Course type';
$string['course_type'] = 'Course Type';
$string['course_type_shortname'] = 'Code';
$string['viewcourse_type'] = 'Add/View Course type';
$string['add_coursetype'] = 'Add Course type';
$string['edit_coursetype'] = 'Edit Course type';
$string['listtype']	='LIST';
$string['listicon'] ='icon fa fa-bars fa-fw';
$string['name'] = 'Name';
$string['enablecoursetype'] = 'Are you sure to activate course type <b>{$a}</b>';
$string['disablecoursetype'] = 'Are you sure to inactivate course type <b>{$a}</b>';
$string['statusconfirm'] = 'Are you sure you want to {$a->status} "{$a->name}"';
$string['coursetypeexists'] = 'Course type already created ({$a})';
$string['deletecoursetypeconfirm'] = 'Are you sure, you want to delete <b>{$a->name}</b> course type?</br> Once deleted, it can not be reverted.';
$string['err_coursetype'] = 'Please enter Course type';
$string['err_coursetypeshortname'] = 'Please enter shortname';
$string['add_course_type'] = 'Add Course Type';
$string['cannotcreateorupdatecourse'] = 'This Course Type is not Available for this Category i.e \'{$a}\'';
$string['coursecodeexists'] = 'Coursetype code already exists ({$a})';
$string['deletecoursetypenotconfirm'] = 'You cannot delete <b>{$a->name}</b> as it is currently mapped to a course. Please unmap to delete.';
$string['reason'] = 'Reason';
$string['open_costcenteridlocal_courses'] = 'Organisation';
$string['open_departmentlocal_courses'] = 'Client';
$string['open_subdepartmentlocal_courses'] = 'LOB';
$string['open_level4departmentlocal_courses'] = 'Region';
$string['open_level5departmentlocal_courses'] = 'Territory';
$string['pleaseselectidentifiedtype'] = 'Please Select Type';

$string['open_costcenteridlocal_courses_help'] = 'Company of the course';
$string['open_departmentlocal_courses_help'] = 'Client of the course';
$string['open_subdepartmentlocal_courses_help'] = 'LOB of the course';
$string['open_level4departmentlocal_courses_help'] = 'Region of the course';
$string['open_level5departmentlocal_courses_help'] = 'Territory of the course';

$string['cannotuploadcoursewithsubdepartment'] = 'With out LOB cannot upload a course with Region';
$string['categorycodeshouldbesubdepcode'] = 'Category Code should be under the LOB i.e \'{$a}\'';
$string['categorycodeshouldbesubsubdepcode'] = 'Category Code should be short name of Region i.e \'{$a}\'';
$string['subdeptshouldundersubdepcode'] = 'Region should be under the LOB i.e \'{$a}\'';
$string['subdepartmentnotfound'] ='LOB not found i.e \'{$a}\'';

$string['cannotuploadcoursewithsubsubdepartment'] = 'With out Region cannot upload a course with Territory';
$string['categorycodeshouldbesubsubdepcode'] = 'Category Code should be under the Region i.e \'{$a}\'';
$string['categorycodeshouldbesubsubsubdepcode'] = 'Category Code should be short name of Territory i.e \'{$a}\'';
$string['subdeptshouldundersubsubdepcode'] = 'Territory should be under the Region i.e \'{$a}\'';
$string['subsubdepartmentnotfound'] ='Region not found i.e \'{$a}\'';
$string['open_states_help'] = 'Search and select an available or existing state as target audience';
$string['open_district_help'] = 'Search and select an available or existing district as target audience';
$string['open_subdistrict_help'] = 'Search and select an available or existing subdistrict as target audience';
$string['open_village_help'] = 'Search and select an available or existing village as target audience';
$string['enablereports'] = 'Course reports are currently not configured. <a href="{$a}" target="_blank"> <u>Click here </u></a> to configure reports';
$string['username'] = 'Username';
$string['completioncount'] = 'Completions';
$string['enrolcount'] = 'Enrolments';
$string['coderequired'] = 'Please enter code';
/* $string['remove_selected_users'] = 'Remove Selected Users';
$string['add_selected_users'] = 'Add Selected Users'; */
$string['taskcoursecompletionreminder'] = 'Course Completion Reminder';
$string['taskcoursecompletionfrequency'] = 'Course Completion Frequency Reminder';
$string['courseautoenrol'] = 'Auto enrollment';
$string['user_fullname'] = 'Employee Name';
$string['idnumber'] = 'Employee ID';
$string['platform'] = 'Platform';
$string['enrolled_date'] = 'Enrolled Date';
$string['courseautoenrol_log'] = 'Auto Enrolled Users';
$string['enrolled_users_list'] = 'Enrolled Users Log';
$string['confirmenroll'] = 'Enroll Users';
$string['enrolluserstocourse'] = 'Are you sure, you want to Enroll users.';
$string['completiondays'] = 'Days';
$string['attempt'] = 'Attempt';
$string['submissiondate'] = 'submission Date';
$string['select_ptype'] = 'Select Category';
$string['performance_type'] = 'Category';
$string['course_completion_reminder'] = 'Course Completion Reminder';
