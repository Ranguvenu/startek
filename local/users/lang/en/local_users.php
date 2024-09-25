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
 */
$string['costcenter'] = 'Company';
$string['employeesearch'] = 'Filter';
$string['subsubdepartment'] = 'subsubdepartment';
$string['msg_pwd_change'] = 'Hi {$a->username}<br>Your password changed successfully!';
$string['adduser'] = 'Add User';
$string['pluginname'] = 'Manage Users';
$string['selectrole'] = 'Select Role';
$string['assignrole'] = 'Assign Role';
$string['joiningdate'] = 'DATE OF JOINING';
$string['generaldetails'] = 'General Details';
$string['personaldetails'] = 'Personal Details';
$string['contactdetails'] = 'Contact Details';
$string['not_assigned'] = 'Not Assigned.';
$string['address'] = 'Address';

/*sarath added for teamdasbord view string*/
$string['usersinfo'] = '{$a->username} User Information';
$string['search'] = 'Search';
$string['enrolldate'] = 'Enroll Date';
$string['name'] = 'Name';
$string['code'] = 'Code';
/*ended here by sarath*/

//$string['table_head'] = '' . get_string('semester', 'local_semesters') . ' (Course enrolled in)';
$string['userpicture'] = 'User Picture';
$string['newuser'] = 'New User';
$string['createuser'] = 'Create User';
$string['edituser'] = '<i class="fa fa-user-plus popupstringicon" aria-hidden="true"></i> Update User <div class= "popupstring"></div>';
$string['updateuser'] = 'Update User';
$string['role'] = 'Role Assigned';
$string['browseusers'] = 'Browse Users';
$string['browseuserspage'] = 'This page allows the user to view the list of users with their profile details which also includes the login summary.';
$string['deleteuser'] = 'Delete User';
$string['delconfirm'] = 'Are you sure? you really want  to delete <b>"{$a->name}"</b> ?';
$string['deletesuccess'] = 'User "{$a->name}" deleted successfully.';
$string['usercreatesuccess'] = 'User "{$a->name}" created Successfully.';
$string['userupdatesuccess'] = 'User "{$a->name}" updated Successfully.';
$string['addnewuser'] = 'Add New User +';
$string['assignedcostcenteris'] = '{$a->label} is "{$a->value}"';
$string['emailexists'] = 'Email exists already.';
$string['givevaliddob'] = 'Give a valid Date of Birth';
$string['dateofbirth'] = 'Date of Birth';
$string['dateofbirth_help'] = 'User should have minimum 20 years age for today.';
$string['assignrole_help'] = 'Assign a role to the user in the selected Company.';
$string['siteadmincannotbedeleted'] = 'Site Administrator can not be deleted.';
$string['youcannotdeleteyourself'] = 'You can not delete yourself.';
$string['siteadmincannotbesuspended'] = 'Site Administrator can not be suspended.';
$string['youcannotsuspendyourself'] = 'You can not suspend yourself.';
$string['users:manage'] = 'Manage Users';
$string['manage_users'] = 'Manage Users';
$string['users:view'] = 'View Users';
$string['users:create'] = 'users:create';
$string['users:delete'] = 'users:delete';
$string['users:edit'] = 'users:edit';
$string['infohelp'] = 'Info/Help';
$string['report'] = 'Report';
$string['viewprofile'] = 'View Profile';
$string['myprofile'] = 'My Profile';
$string['adduserstabdes'] = 'This page allows you to add a new user. This can be one by filling up all the required fields and clicking on "submit" button.';
$string['edituserstabdes'] = 'This page allows you to modify details of the existing user.';
$string['helpinfodes'] = 'Browse user will show all the list of users with their details including their first and last access summary. Browse users also allows the user to add new users.';
$string['youcannoteditsiteadmin'] = 'You can not edit Site Admin.';
$string['suspendsuccess'] = 'User "{$a->name}" suspended Successfully.';
$string['unsuspendsuccess'] = 'User "{$a->name}" Unsuspended Successfully.';
$string['p_details'] = 'PERSONAL/ACADEMIC DETAILS';
$string['acdetails'] = 'Academic Details';
$string['manageusers'] = 'Manage Users';
$string['username'] = 'User Name';
$string['unameexists'] = 'Username Already exists';
$string['open_employeeidexist'] = 'Employee ID Already exists';
$string['open_employeeiderror'] = 'Employee ID can contain only alplabets or numericals special charecters not allowed';
$string['total_courses'] = 'Total number of Courses';
$string['enrolled'] = 'Number of Courses Enrolled';
$string['completed'] = 'Number of Courses Completed';
$string['signature'] = "Registrar's Signature";
$string['status'] = "Status";
$string['courses'] = "Courses";
$string['date'] = "Date";
$string['doj'] = 'Date of Joining';
$string['hcostcenter'] = 'Company';
$string['paddress'] = 'PERMANENT ADDRESS';
$string['caddress'] = 'PRESENT ADDRESS';
$string['invalidpassword'] = 'Invalid password';
$string['dol'] = 'Date of leave';
$string['dor'] = 'Date of resignation';
$string['serviceid'] = 'Employee ID';
$string['help_1'] = '<div class="helpmanual_table"><table class="generaltable" border="1">
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;padding-left:50px;">Mandatory Fields</td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>company_code</td><td>Provide the Organization code</td></tr>
<tr><td>username</td><td>Enter the username, avoid additional spaces.</td></tr>
<tr><td>employee_code</td><td>Enter the employee code, avoid additional spaces.</td></tr>
<tr><td>firstname</td><td>Enter the first name.</td></tr>
<tr><td>lastname</td><td>Enter the last name.</td></tr>
<tr><td>email</td><td>Enter valid email.</td></tr>
<tr><td>employee_status</td><td>Enter employee status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
<tr><td>gender</td><td>Enter gender as either \'male\',\'female\' or \'other\', avoid additional spaces.</td></tr>';
$string['help_2'] = '</td></tr>
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;"><b  class="pad-md-l-50 hlep2-oh">Non-Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>password</td><td>Provide the password,Password must be at least 8 characters long,Password must have at least 1 digit(s),Password must have at least 1 upper case letter(s),
Password must have at least 1 non-alphanumeric character(s) such as as *, -, or #..</td></tr>
<tr><td>prefix</td><td>Enter prfix as either \'mr\', \'mrs\' or \'ms\', avoid additional spaces.</td></tr>
<tr><td>bussiness_unit_code</td><td>Provide bussiness unit code. Bussiness unit must already exist in system as part of Organization hierarchy.</td></tr>
<tr><td>department_code</td><td>Enter department code. Department must already exist under specified bussiness unit in system as part of Organization hierarchy.</td></tr>
<tr><td>subdepartment_code</td><td>Enter sub department code. Sub Department must already exist under specified department in system as part of Organization hierarchy.</td></tr>
<tr><td>mobileno</td><td>Enter Numerics only.</td></tr>
<tr><td>reportingmanager_empid</td><td>Enter reporting manger employee code, avoid additional spaces..</td></tr>

<tr><td>designation</td><td>Enter Designation for the user.</td></tr>
<tr><td>employment_type</td><td>Enter employment type for the user.</td></tr>
<tr><td>region</td><td>Enter region for the user.</td></tr>
<tr><td>grade</td><td>Enter grade for the user.</td></tr>
<tr><td>date_of_birth</td><td>Enter date of birth of the user. (Date format is \'dd-mm-yyyy\')</td></tr>
<tr><td>date_of_joining</td><td>Enter date of joining of the user. (Date format is \'dd-mm-yyyy\')</td></tr>
<tr><td>force_password_change</td><td>Provide the value as 1 if need to enable force password or 0 to disable it..</td></tr>
</table>';

$string['help_1_orghead'] = '<table class="generaltable" border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;"><b class="pad-md-l-50 hlep1-oh">Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>Company_code</td><td>Provide the Organization</td></tr>
<tr><td>username</td><td>Enter the username, avoid additional spaces.</td></tr>
<tr><td>learner_id</td><td>Enter the employee code, avoid additional spaces.</td></tr>
<tr><td>firstname</td><td>Enter the first name.</td></tr>
<tr><td>lastname</td><td>Enter the last name.</td></tr>
<tr><td>email</td><td>Enter valid email.</td></tr>
<tr><td>learner_status</td><td>Enter Learner Status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
';

$string['help_1_dephead'] = '<table class="generaltable" border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;"><b class="pad-md-l-50 hlep1-dh">Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>Company_code</td><td>Provide the Organization</td></tr>
<tr><td>username</td><td>Enter the username, avoid additional spaces.</td></tr>
<tr><td>learner_id</td><td>Enter the employee code, avoid additional spaces.</td></tr>
<tr><td>firstname</td><td>Enter the first name.</td></tr>
<tr><td>lastname</td><td>Enter the last name.</td></tr>
<tr><td>email</td><td>Enter valid email.</td></tr>
<tr><td>learner_status</td><td>Enter Learner Status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
';

$string['already_assignedstocostcenter'] = '{$a} already assigned to costcenter. Please unassign from costcenter to proceed further';
$string['already_instructor'] = '{$a} already assigned as instructor. Please unassign this user as instructor to proceed further';
$string['already_mentor'] = '{$a} already assigned as mentor. Please unassign this user as mentor to proceed further';
// ***********************Strings for bulk users**********************
$string['download'] = 'Download';
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['uploadusers'] = 'Upload Users';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploaduser'] = 'Upload Users';
$string['back_upload'] = 'Back to Upload Users';
$string['bulkuploadusers'] = 'Bulk Upload Users';
$string['uploaduser_help'] = ' The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';

$string['uploaduserspreview'] = 'Upload Users Preview';
$string['userscreated'] = 'Users created';
$string['usersskipped'] = 'Users skipped';
$string['usersupdated'] = 'Users updated';
$string['uuupdatetype'] = 'Existing users details';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing users';
$string['uuoptype_addupdate'] = 'Add new and update existing users';
$string['uuoptype_update'] = 'Update existing users only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uploadusersresult'] = 'Uploaded Users Result';
$string['helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual'] = 'Help Manual';
$string['info'] = 'Help';
$string['helpinfo'] = 'Browse user will show all the list of users with their details including their first and last access summary. Browse users also allows the user to add new users.';
$string['changepassdes'] = 'This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/delete/inactivate) the users.';
$string['changepassinstdes'] = 'This page allows you to update or modify the password at any point of time; provided the instructor must furnish the current password correctly.';
$string['changepassregdes'] = 'This page allows you to update or modify the password at any point of time; provided the registrar must furnish the current password correctly.';
$string['info_help'] = '<h1>Browse Users</h1>
This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/delete/inactivate) the users.
<h1>Add New/Create User</h1>
This page allows you to add a new user. This can be one by filling up all the required fields and clicking on ‘submit’ button.';
$string['enter_grades'] = 'Enter Grades';
$string['firstname'] = 'First Name';
$string['middlename'] = 'Middle Name';
$string['lastname'] = 'Last Name';
$string['departmentt'] = 'Department';
$string['level'] = 'Level';
$string['location'] = 'Location';
$string['female']='Female';
$string['male']='Male';
$string['userdob']='Date of Birth';
$string['phone']='Mobile';
$string['email']='Email';
$string['emailerror']='Enter valid Email ID';
$string['phoneminimum']='Please enter minimum 10 digits';
$string['phonemaximum']='Please enter maximum 10 digits';
$string['O_error']='Please select a Bussiness Unit';
$string['numeric'] = 'Only numeric values';
$string['pcountry']='Bussiness Unit';
$string['genderheading']='Generate Heading';
$string['primaryyear']='Primary Year';
$string['score']='Score';
$string['contactname']='Contact Name';
$string['hno']='House Number';
$string['phno']='Phone Number';
$string['pob']='Place of Birth';
$string['contactname']='Contact Name';
$string['bulkassign'] = 'Bulk assignment to the costcenter';
$string['im:costcenter_unknown'] = 'Unknown costcenter';
$string['im:user_unknown'] = 'Unkown user'; 
$string['im:user_notcostcenter'] = 'Loggedin manager not assigned to this costcenter "{$a->csname}"';
$string['im:already_in'] = 'User already assigned to the costcenter';
$string['im:assigned_ok'] = '{$a} User assigned successfully';
$string['upload_employees'] = 'Upload learners';
$string['assignuser_costcenter'] = 'Assign users to Organization';
//-------added by rizwana-----------//
$string['button'] = 'CONTINUE';
/*-----------------------strings added by mani kanta -------------------------------*/
$string['idnumber'] = 'Id number';
$string['username'] = 'Username';
$string['firstcolumn'] = 'User column contains';
$string['enroll_batch'] ='Batch Enroll';
$string['mass_enroll'] = 'Bulk enrolments';
$string['mass_enroll_help'] = <<<EOS
<h1>Bulk enrolments</h1>

<p>
With this option you are going to enrol a list of known users from a file with one account per line
</p>
<p>
<b> The firstline </b> the empty lines or unknown accounts will be skipped. </p>

<p>
The file may contains one or two columns, separated by a comma, a semi-column or a tabulation.

You should prepare it from your usual spreadsheet program from official lists of students, for example,
and add if needed a column with groups to which you want these users to be added. Finally export it as CSV. (*)</p>

<p>
<b> The first one must contains a unique account identifier </b>: idnumber (by default) login or email  of the target user. (**). </p>

<p>
The second <b>if present,</b> contains the group's name in wich you want that user to be added. </p>

<p>
If the group name does not exist, it will be created in your course, together with a grouping of the same name to which the group will be added.
.<br/>
This is due to the fact that in Moodle, activities can be restricted to groupings (group of groups), not groups,
 so it will make your life easier. (this requires that groupings are enabled by your site administrator).

<p>
You may have in the same file different target groups or no groups for some accounts
</p>

<p>
You may unselect options to create groups and groupings if you are sure that they already exist in the course.
</p>

<p>
By default the users will be enroled as students but you may select other roles that you are allowed to manage (teacher, non editing teacher
or any custom roles)
</p>

<p>
You may repeat this operation at will without dammages, for example if you forgot or mispelled the target group.
</p>


<h2> Sample files </h2>

Id numbers and a group name to be created in needed in the course (*)
<pre>
"idnumber";"group"
" 2513110";" 4GEN"
" 2512334";" 4GEN"
" 2314149";" 4GEN"
" 2514854";" 4GEN"
" 2734431";" 4GEN"
" 2514934";" 4GEN"
" 2631955";" 4GEN"
" 2512459";" 4GEN"
" 2510841";" 4GEN"
</pre>

only idnumbers (**)
<pre>
idnumber
2513110
2512334
2314149
2514854
2734431
2514934
2631955
</pre>

only emails (**)
<pre>
email
toto@insa-lyon.fr
titi@]insa-lyon.fr
tutu@insa-lyon.fr
</pre>

usernames and groups, separated by a tab :

<pre>
username	 group
ppollet      groupe_de_test              will be in that group
codet        groupe_de_test              also him
astorck      autre_groupe                will be in another group
yjayet                                   no group for this one
                                         empty line skipped
unknown                                  unknown account skipped
</pre>

<p>
<span <font color='red'>(*) </font></span>: double quotes and spaces, added by some spreadsheet programs will be removed.
</p>

<p>
<span <font color='red'>(**) </font></span>: target account must exit in Moodle ; this is normally the case if Moodle is synchronized with
some external directory (LDAP...)
</p>


EOS;


$string['reportingto'] = 'Reports To';
$string['functionalreportingto'] = 'Functional Reporting To';
$string['ou_name'] = 'OU Name';
$string['department'] = 'Bussiness Unit';
$string['costcenter_custom'] = 'Costcenter';
$string['subdepartment'] = 'Bussiness Unit';
$string['designation'] = 'Designation';
$string['designations_help'] = 'Search and select a designation from the available pool. Designation made available here are the designation that are mapped to users on the system. Selecting a designation means that any user in the system who has the selected designation mapped to them will be eligible for enrollment.';
$string['client'] = 'Client';
$string['grade'] = 'Grade';
$string['team'] = 'Team';
$string['hrmrole'] = 'Department';
$string['role_help'] = "Search and select a role from the available pool. Roles made available here are the roles that are mapped to users on the system. Selecting a 'role (s)' means that any user in the system who has the selected role mapped to them will be eligible for enrollment.";
$string['zone'] = 'Zone';
$string['region'] = 'Region';
$string['branch'] = 'Branch';
$string['subbranch'] = 'Sub Branch';
$string['group'] = 'Group';
$string['preferredlanguage'] = 'Language';
$string['open_group'] = 'Discipline';
$string['open_band'] = 'Band';
$string['open_role'] = 'Role';
$string['open_zone'] = 'Zone';
$string['open_region'] = 'Region';
$string['open_grade'] = 'Grade';
$string['open_branch'] = 'Branch';
$string['position'] = 'Role';
$string['emp_status'] = 'Learner Status';
$string['resign_status'] = 'Resignation Status';
$string['emp_type'] = 'Learner Type';
$string['dob'] = 'Date of Birth';
$string['career_track_tag'] = 'Career Track';
$string['campus_batch_tag'] = 'Campus Batch';
$string['calendar'] = 'Calendar Name';
$string['otherdetails'] = 'Other Details';
$string['location'] = 'Location';
$string['city'] = 'City';
$string['gender'] = 'Gender';
$string['usersupdated'] = 'Users updated';
$string['supervisor'] = 'Reporting To';
$string['selectasupervisor'] = 'Select Reporting To';
$string['reportingmanagerid'] = 'Functional Reporting To';
$string['selectreportingmanager'] = 'Select Functional Reporting';
$string['salutation'] = 'Salutation';
$string['employment_status'] = 'Employment Status';
$string['confirmation_date'] = 'Confirmation Date';
$string['confirmation_due_date'] = 'Confirmation Due Date';
$string['age'] = 'Age';
$string['paygroup'] = 'Paygroup';
$string['physically_challenge'] = 'Physically Challenge';
$string['disability'] = 'Disability';
$string['employment_type'] = 'Employment Type';
$string['employment_status'] = 'Employment Status';
$string['employee_status'] = 'Learner Status';
$string['enrol_user'] = 'Enrol Users';
$string['level'] = 'Discipline';
$string['select_career'] = 'Select Career Track';
$string['select_grade'] = 'Select Grade';
/*------------------------------------Ended Here-----------------------------------*/

// added by anil

$string['userinfo'] = 'User info';
$string['addtional_info'] = 'Addtional info';
$string['user_transcript'] = 'User transcript';
$string['type'] = 'Type';
$string['transcript_history'] = 'Transcript History (2015-2016)';
//added by Ravi
$string['sub_sub_department']='Sub Sub Depatement';
$string['zone_region']='Zone Region';
$string['area']='Area';
$string['dob']='DOB';
$string['matrail_status']='Martial Status';
$string['state']='State';

//added by Shivani
$string['course_header']='CURRENT LEARNING';
$string['courses_header_emp']='CURRENT LEARNING FOR ';
$string['courses_data']='No Courses to display.';       
$string['page_header']='Profile Details';
$string['adnewuser']='<i class="fa fa-user-plus popupstringicon" aria-hidden="true"></i> Create User <div class= "popupstring"></div>';
$string['empnumber']='Employee ID';
$string['departments']='Countries';
$string['sub_departments']='Bussiness Unit';
$string['open_costcenteridlocal_users_help'] = 'Company of the User';
$string['open_departmentlocal_users_help'] = 'Bussiness Unit of the User';
$string['open_subdepartmentlocal_users_help'] = 'Department of the User';
$string['open_level4departmentlocal_users_help'] = 'Sub Department of the User';

$string['errordept']='Please select Bussiness Unit';
$string['errorsubdept']='Please select Bussiness Unit';
$string['errorsubsubdept']='Please select Sub Bussiness Unit';
$string['errorfirstname']='Please enter First Name';
$string['errorlastname']='Please enter Last Name';
$string['errordepartmentt']='Please enter Department';
$string['errorlocation']='Please enter Location';
$string['erroremail']='Please enter Email Address';
$string['filemail']='Email Address';
$string['idexits']='Employee ID Already exists';
//-------for sync lang files-------
$string['options']='Option';
$string['enrollmethods']='Enroll method';
$string['authenticationmethods'] = 'Authentication method';


$string['assigned_courses'] = 'Assigned Courses';		
$string['completed_courses'] = 'Completed Courses';		
$string['not_started_courses'] = 'Not Started';		
$string['inprogress_courses'] = 'In Progress';		
$string['employee_id'] = 'Employee ID';
$string['certificates'] = 'Certificates';
$string['already_assignedlp']='User assigned to Learning plan';
$string['coursehistory']='History';
$string['employees']="Learner's";
$string['learningplans']="Learning Paths";
$string['lowercaseunamerequired'] = 'Username should be in lowercase only';
$string['sync_users'] = 'Sync users';
$string['sync_errors'] = 'Sync errors';
$string['sync_stats'] = 'Sync statistics';
$string['view_users'] = 'view users';
$string['nodepartmenterror'] = 'Bussiness Unit cannot be empty';
$string['syncstatistics'] = 'Sync Statistics';
$string['phonenumvalidate']='10 digit positive numbers only';

$string['cannotcreateuseremployeeidadderror'] = 'Learner with learnerid {$a->employee_id} already exist so cannot create user in adduser mode at line {$a->linenumber}';
$string['cannotfinduseremployeeidupdateerror'] = 'Learner with learnerid {$a->employee_id} doesn\'t exist';
$string['cannotcreateuseremailadderror'] = 'Learner with mailid {$a->email} already exist so cannot create user in adduser mode at line {$a->linenumber}';
$string['cannotedituseremailupdateerror'] = 'Learner with mailid {$a->email} doesn\'t exist so cannot update in update mode at line {$a->linenumber}';
$string['multipleuseremployeeidupdateerror'] = 'Multiple learners with learnerid {$a} exist';
$string['multipleedituseremailupdateerror'] = 'Multiple learners with email {$a} exist';
$string['multipleedituserusernameediterror'] = 'Multiple learners with username {$a} exist';
$string['cannotedituserusernameediterror'] = 'Learner with username {$a} doesn\'t exist in update mode';
$string['cannotcreateuserusernameadderror'] = 'Learner with username {$a->username} already exist cannot create user in add mode at line {$a->linenumber}';
$string['deleteconfirm'] = 'Are you sure you want to delete <b>"{$a->fullname}"</b> learner ?';
$string['local_users_table_footer_content'] = 'Showing {$a->start_count} to {$a->end_count} of {$a->total_count} entries';
$string['suspendconfirm'] = 'Are you sure you want to change status of {$a->fullname} ?';
$string['suspendconfirmenable'] = 'Are you sure to make learner <b>\'{$a->fullname}\'</b> inactive ?';
$string['suspendconfirmdisable'] = 'Are you sure to make learner <b>\'{$a->fullname}\'</b> active ?';
$string['firstname_surname'] = 'First Name / Surname';
$string['employeeid'] = 'Employee ID';
$string['emailaddress'] = 'Email Address';
$string['organization']='Company';
$string['supervisorname'] = 'Reporting To';
$string['lastaccess'] = 'Last Access';
$string['actions'] = 'Actions';
$string['classrooms'] = 'Classrooms';
$string['onlineexams'] = 'Online exams';
$string['programs'] = 'Programs';
$string['contactno'] = 'Contact No';
$string['nosupervisormailfound'] = 'No reporting managers found with email {$a->email} at line {$a->line}.';
$string['nosupervisorempidfound'] = 'No reporting managers found with employee code {$a->empid} at line {$a->line}.';
$string['valusernamerequired'] = 'Please enter a valid Username';
$string['valfirstnamerequired'] = 'Please enter a valid Firstname';
$string['vallastnamerequired'] = 'Please enter a valid Lastname';
$string['errororganization'] = 'Please select Company';
$string['usernamerequired'] = 'Please enter Username';
$string['passwordrequired'] = 'Please enter Password';
$string['departmentrequired'] = 'Please select Bussiness Unit';
$string['employeeidrequired'] = 'Please enter Employee ID';
$string['noclassroomdesc'] = 'No description provided';
$string['noprogramdesc'] = 'No description provided';

$string['team_dashboard'] = 'Team Dashboard';
$string['myteam'] = 'My Team';
$string['idnumber'] = 'Employee ID';
//==============For target audience=========
// OL-1042 Add Target Audience to Classrooms////
$string['target_audience'] = 'Target audience';
$string['open_group'] = 'Group';
$string['groups_help'] = 'Search and select an available or existing custom group as target audience';
$string['open_band'] = 'Band';
$string['open_hrmsrole'] = 'Department';
$string['role_help'] = "Search and select a role from the available pool. Roles made available here are the roles that are mapped to users on the system. Selecting a 'role (s)' means that any user in the system who has the selected role mapped to them will be eligible for enrollment.";
$string['open_branch'] = 'Branch';	
$string['open_subbranch'] = 'Sub Branch';
$string['open_designation'] = 'Designation';
$string['designation_help'] = 'Enter the designation of the user';
$string['open_location'] = 'Location';
$string['location_help'] = "Enter the location of the user.";
$string['team_allocation'] = 'Team allocation';
$string['myteam'] = 'My team';
$string['allocate'] = 'Allocate';
$string['learning_type'] = 'Learning Type';

$string['team_confirm_selected_allocation'] = 'Confirm allocation?';
$string['team_select_user'] = 'Please select a user.';
$string['team_select_course_s'] = 'Please select valid course/s.';
$string['team_approvals'] = 'Team approvals';
$string['approve'] = 'Approve';
$string['no_team_requests'] = 'No requests from team';
$string['team_no_learningtype'] = 'Please select any learning type.';
$string['select_requests'] = 'Select any requests.';
$string['select_learningtype'] = 'Select any learning type.';
$string['allocate_search_users'] = 'Search Users...';
$string['allocate_search_learnings'] = 'Search Learning Types...';
$string['select_user_toproceed'] = 'Select a user to proceed.';
$string['no_coursesfound'] = 'No courses found';
$string['no_classroomsfound'] = 'No classrooms found';
$string['no_programsfound'] = 'No programs found';
$string['team_requests_search'] = 'Search Team Requests by Users...';
$string['team_nodata'] = 'No records found';
$string['allocate_confirm_allocate'] = 'Are you sure you want to Approve selected requests?';
$string['team_request_confirm'] = 'Are you sure you want to Approve selected requests?';
$string['members'] = 'Members';
$string['permissiondenied'] = 'You dont have permissions to view this page.';
$string['onlinetests'] = 'Online Tests';
$string['manage_br_users'] = 'Manage users';
$string['profile'] = 'Profile';
$string['badges'] = 'Badges';
$string['completed'] = 'Completed';
$string['notcompleted'] = 'Not Completed';
$string['nopermission'] = 'You dont have permissions to view ths page';
$string['selectdepartment'] = 'Select Bussiness Unit';
$string['selectsupervisor'] = 'Select Reporting To';
$string['total'] = 'Total';
$string['active'] = 'Active';
$string['inactive'] = 'In Active';
$string['missing'] = 'Missing {$a->field} at line {$a->linenumber}';
$string['deleteconfirmsynch'] = 'Are you sure you want to delete the selected values ?';
$string['classroom'] = 'Classrooms';
$string['learningplan'] = 'Learningplan';
$string['program'] = 'Program';
$string['open_level'] = 'Discipline';
$string['certification'] = 'Certification';
$string['certifications'] = 'Certifications';
$string['groups'] = 'groups';
$string['notbrandedmobileapp'] = 'You are not using BizLMS branded mobile App';
$string['makeactive'] = 'Make Active';
$string['makeinactive'] = 'Make Inactive';
$string['positions'] = 'Position';
$string['positionsreq'] = 'Select Position';
$string['positionreq'] = 'Select Role';
$string['domain'] = 'Domain';
$string['domainreq'] = 'Select Domain';
$string['skillname'] = 'Skill Name';
$string['level'] = 'Discipline';
$string['categorypopup'] = 'Competency {$a}';
$string['competency'] = 'Competency';
$string['skill_profile'] = 'Skill Profile';
$string['competency'] = 'Competency';
$string['skills'] = 'Skills';
$string['open_level'] = 'Discipline';
$string['competencyprogress'] = 'Competency Progress';

$string['login'] = 'Login';
$string['users'] = 'Users';
$string['selectonecheckbox_msg'] = 'Please Select atleast one checkbox';
$string['save_continue'] = 'Save & Continue';
$string['skip'] = 'Skip';
$string['previous'] = 'Previous';
$string['cancel'] = 'Cancel';
$string['emailaleadyexists'] = 'User with email {$a->email} already exist at line {$a->excel_line_number}.';

$string['usernamealeadyexists'] = 'User with username {$a->username} already exist at line {$a->excel_line_number}.';

$string['employeeid_alreadyexists'] = 'User with employee code {$a->employee_id} already exist at line {$a->excel_line_number}.';

$string['email_alreadyexists'] = 'User with email {$a->email} already exist at line {$a->excel_line_number}.';

$string['empiddoesnotexists'] = 'User with employee code {$a->employee_id} does not exist at line  {$a->excel_line_number}.';
$string['empfile_syncstatus'] = 'Learner file sync status';
$string['multiple_user'] = 'Multiple user exists';
$string['addedusers_msg'] = 'Total {$a} new users added to the system.';
$string['updatedusers_msg'] = 'Total {$a} users details updated.';
$string['errorscount_msg'] = 'Total {$a} errors occured in the sync update.';
$string['warningscount_msg'] = 'Total {$a} warnings occured in the sync update.';
$string['superwarnings_msg'] = 'Total {$a} Warnings occured while updating supervisor.';

$string['filenotavailable'] = 'File with Learner data is not available for today.';

$string['orgmissing_msg'] = 'Provide the Company info for employee code \'{$a->employee_id}\' of uploaded sheet at line {$a->excel_line_number} .';

$string['invalidorg_msg'] = 'Company "{$a->org_shortname}" for employee code \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['otherorg_msg'] = 'Company "{$a->org_shortname}" entered at line \'{$a->employee_id}\' for employee code {$a->excel_line_number} in uploaded excelsheet does not belongs to you.';

$string['invaliddept_msg'] = 'Bussiness Unit "{$a->dept_shortname}" for employee code \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['otherdept_msg'] = 'Bussiness Unit "{$a->dept_shortname}" entered at line {$a->excel_line_number} for employee code \'{$a->employee_id}\' in uploaded excelsheet does not belongs to you.';


$string['invalidempid_msg'] = 'Provide valid employee code value \'{$a->employee_id}\' inserted in the excelsheet at line {$a->excel_line_number} .';

$string['empidempty_msg'] = 'Provide employee code for username \'{$a->username}\' of uploaded sheet at line {$a->excel_line_number}. ';
$string['error_employeeidcolumn_heading'] = 'Error in employee code column heading in uploaded excelsheet ';

$string['firstname_emptymsg'] = 'Provide firstname for  employee code \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}.';
$string['error_firstnamecolumn_heading'] = 'Error in first name column heading in uploaded excelsheet ';

$string['latname_emptymsg'] = 'Provide last name for  employee code \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';
$string['error_lastnamecolumn_heading'] = 'Error in last name column heading in uploaded excelsheet';

$string['email_emptymsg'] = 'Provide email id for  employee code \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';
$string['invalidemail_msg'] = 'Invalid email id entered for  learnerid \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}.';

$string['columnsarragement_error'] = 'Error in arrangement of columns in uploaded excelsheet at line {$a}';

$string['invalidusername_error'] = 'Provide valid username for employee code \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['usernameempty_error'] = 'Provide username for employee code \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['empstatusempty_error'] = 'Provide learner status for  employee code \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['mobileno_error'] = 'Enter a valid mobile number for employee code \'{$a->learner_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['validmobileno_error'] = 'Enter a valid mobile number of 10 digits for employee code \'{$a->learner_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['select_org'] = '--Select Company--';
$string['select_dept'] = '--Select Bussiness Unit--';
$string['select_reportingto'] = '--Select Reporting To--';
$string['select_domain'] = '--Select Domain--';
$string['select_role'] = '--Select Role--';
$string['select_position'] = '--Select Position--';
$string['select_subdept'] = '--Select Bussiness Unit--';
$string['select_opt'] = '-- Select --';
$string['only_add'] = 'Only Add';
$string['only_update'] = 'Only Update';
$string['add_update'] = 'Both Add and Update';
$string['disable'] = 'Disable';
$string['enable'] = 'Enable' ;
$string['employee'] = 'Learner' ;
$string['error_in_creation'] = 'Error in creation' ;
$string['error_in_inactivating'] = 'Error in inactivating';
$string['error_in_deletion'] = 'Error in deletion';
$string['file_notfound_msg'] = 'file not found/ empty file error';
$string['back'] = 'Back';
$string['sample'] = 'Sample';
$string['help_manual'] = 'Help manual';
$string['sync_errors'] = 'Sync Errors';
$string['welcome'] = 'Welcome';
$string['edit_profile'] = 'Edit Profile';
$string['messages'] = 'Messages';
$string['competencies'] = 'Competencies';
$string['error_with'] = 'Error with';
$string['uploaded_by'] = 'Uploaded by';
$string['uploaded_on'] = 'Uploaded On';
$string['new_users_count'] = 'New Users Count';
$string['sup_warningscount'] = 'Supervisor Warnings Count';
$string['warningscount'] = 'Warnings Count';
$string['errorscount'] = 'Errors Count';
$string['updated_userscount'] = 'Updated Users Count';
$string['personalinfo'] = 'Personal Info :';
$string['professionalinfo'] = 'Professional Info :';
$string['otherinfo'] = 'Other Info :';
$string['delete'] = 'Delete';
$string['pictureof'] = 'Picture of';
$string['syncnow'] = 'Sync Now';
$string['authmethod'] = 'Auth Method';
$string['errorphoneno'] = 'Phone Number Required';
$string['open_locationrequired'] = 'Please provide Location information';
$string['open_hrmsrolerequired'] = 'Please provide Hrms Role information';
$string['password_required'] = 'Provide Password information of the user at line {$a->linenumber}';
$string['hrmsrole_upload_error'] = 'Provide Hrms Role information of the user at line {$a->linenumber}';
$string['location_upload_error'] = 'Provide Location information of the user at line {$a->linenumber}';
$string['password_upload_error'] = '{$a->errormessage} at line {$a->linenumber}';
$string['hrmrole_help'] = 'Enter the role of the user';
$string['open_location_help'] = 'Enter the location of the user';
$string['open_level_help'] = 'Enter the level of the user';
$string['client_upload_error'] = 'Provide Bussiness Unit information of the user at line {$a->linenumber}';
$string['position_upload_error'] = 'With out domain you cannot upload Position only';
$string['notifylogins'] = 'Notify Login Details';
$string['logininfo'] = 'Login Details';
$string['logininfobody'] = ' <p>Hi {$a->firstname},</p>
<p>Please find the below login detials for the site {$a->siteurl}.</p>
<p>Username: {$a->firstname}</p>
<p>Password: {$a->password}</p>
<p>Thanks,</p>
<p>Admin.</p>';

$string['noorganizationidfound'] = 'No {$a->identifier} found with {$a->orgid} at line {$a->line}.';
$string['noorsubdepartmentfound'] = 'No business unit found with {$a->subdepartmentid} at line {$a->line}.';
$string['statusvalidation'] = 'Please Enter Learner Status as either Active or Inactive, avoid additional spaces at line {$a->line}.';
$string['nodomainfound'] = 'Domain {$a->domainid} does not exist under Company {$a->orgid} at line {$a->line}.';
$string['nopositionfound'] = 'No position found with {$a->positiond} at line {$a->line}.';
$string['emailisexists'] = 'Email exists already at line {$a->excel_line_number}.';
$string['downloadusers'] = 'Download Users'; 
$string['userpictureurl'] = 'UserPictureURL';
$string['costcentername'] = 'Costcenter';
$string['institution'] = 'Institution';
$string['discipline'] = 'Discipline';
$string['phonenumber'] = 'Contact No';
$string['userid'] = 'UserID';
$string['fullname'] = 'FullName';
$string['suspend'] = 'Suspend';
$string['edit'] = 'Edit';
$string['listtype'] = 'LIST';
$string['cardtype'] = 'CARD';
$string['listicon'] ='icon fa fa-bars fa-fw';
$string['cardicon'] ='icon fa fa-fw fa-th';
$string['exportusers'] = 'Export Users to Excel';
$string['employeeid_nospecialcharacters'] = 'User with employee code {$a->learner_id} connot contain special characters at line {$a->excel_line_number}.';
$string['deptcheckwithorg'] = ' User with employee code {$a->learner_id} the department \'{$a->dept}\' not belongs to \'{$a->org}\'   at line \'{$a->excel_line_number}\' ';
$string['subdeptcheckwithdept'] = ' User with employee code {$a->learner_id} the business unit \'{$a->subdept}\' not belongs to \'{$a->dept}\'   at line \'{$a->excel_line_number}\' ';
$string['invalidtimezone'] = 'Invalid Timezone - User with employee code {$a->learner_id} at line {$a->excel_line_number}';
$string['invalidcountrycode'] = 'Invalid Bussiness Unit code - User with employee code {$a->learner_id} at line {$a->excel_line_number}';
$string['orgcheckwithdhoh'] = ' Dont have permissions to {$a->identifier} \'{$a->orgid}\' at line \'{$a->line}\' ';
$string['invalidemail'] = "Invalid Email id entered ";
$string['village'] = "Village";
$string['subdistrict'] = "Sub District";
$string['district'] = "District";
$string['territory'] = "Territory";
$string['territories'] = "Territories";

$string['open_states'] = "State";
$string['open_district'] = "District";
$string['open_subdistrict'] = "Sub District";
$string['open_village'] = "Village";
$string['filteropen_states'] = "State";
$string['filteropen_district'] = "District";
$string['filteropen_subdistrict'] = "Sub District";
$string['filteropen_village'] = "Village";
$string['open_states_help'] = 'Search and select an available or existing state as target audience';
$string['open_district_help'] = 'Search and select an available or existing district as target audience';
$string['open_subdistrict_help'] = 'Search and select an available or existing subdistrict as target audience';
$string['open_village_help'] = 'Search and select an available or existing village as target audience';
$string['selectopen_states'] = 'Select State';
$string['selectopen_district'] = 'Select District';
$string['selectopen_subdistrict'] = 'Select Sub District';
$string['selectopen_village'] = 'Select Village';
$string['commercialunit'] = 'Department';
$string['commercialarea'] = 'Sub Department';
$string['territory'] = 'Territory';
$string['addressinfo'] = 'Address Info :';
$string['open_costcenteridlocal_users'] = 'Company';
$string['open_departmentlocal_users'] = 'Bussiness Unit';
$string['open_subdepartmentlocal_users'] = 'Department';
$string['open_level4departmentlocal_users'] = 'Sub Department';
$string['open_level5departmentlocal_users'] = 'Territory';
$string['managestates'] = 'Manage State';
$string['states'] = 'State';
$string['organisation'] = 'Company';
$string['invalidnoorganizationidfound'] = 'Department "{$a->commercial_unitid}" is not under "{$a->parentid}" at line {$a->line}.';
$string['noorcommercial_unitfound'] = 'No department found with "{$a->commercial_unitid}" at line {$a->line}.';
$string['invalidbussinessunitgiven'] = 'Department "{$a->commercial_unitid}" is not under "{$a->parentid}" at line {$a->line}.';
$string['noorcommercial_areafound'] = 'No sub department found with "{$a->commercial_areaid}" at line {$a->line}.';
$string['invalidcommercialunitgiven'] = 'Sub Department "{$a->commercial_areaid}" is not under "{$a->parentid}" at line {$a->line}.';
$string['noorterritoryfound'] = 'No Territory found with "{$a->territoryid}" at line {$a->line}.';
$string['invalidterritorygiven'] = 'Territory "{$a->territoryid}" is not under "{$a->parentid}" at line {$a->line}.';
$string['invalidstatevalue'] = 'State "{$a->state}" is not under "{$a->parentid}" at line {$a->line}.';
$string['invaliddistrictvalue'] = 'District "{$a->district}" is not under "{$a->parentid}" at line {$a->line}.';
$string['invalidsubdistrictvalue'] = 'Sub district "{$a->subdistrict}" is not under "{$a->parentid}" at line {$a->line}.';
$string['invalidvillagevalue'] = 'Village "{$a->village}" is not under "{$a->parentid}" at line {$a->line}.';
$string['usersrole'] = 'Roles assigned to {$a->username}';
$string['rolename'] = 'Role';
$string['timeassign'] = 'Assigned on';
$string['noroleassign'] = 'no role assigned';
$string['hierarchy'] = 'Hierarchy context';
$string['level'] = 'Level';
$string['na'] = 'N/A';
$string['managegeographyfields'] = 'Geography Location Master';
$string['onlylowercase'] = 'Only small letter accept';
$string['action'] = 'Action';
$string['joiningdate'] = 'Date of Joining';
$string['branch_help'] = 'Enter the branch of the user';
$string['subbranch_help'] = 'Enter the sub branch of the user';
$string['level_help'] = 'Enter the level of the user';
$string['grade_help'] = 'Enter the grade of the user';
$string['region_help'] = 'Enter the region of the user';
$string['joiningdate_help'] = 'Enter the joining date of the user';
$string['other'] = 'Other';
$string['transgender'] = 'Transgender';
$string['employmenttype'] = 'Employment Type';
$string['employmenttype_help'] = 'Enter the employment type of the user';
$string['employmentstatus'] = 'Employment Status';
$string['employmentstatus_help'] = 'Enter the employment status of the user';
$string['prefix'] = 'Prefix';
$string['skilltype'] = 'Skill Type';
$string['invalidgender'] = 'Invailid gender at line {$a->line}. Enter only male\female\other';
$string['invalidprefix'] = 'Invailid prefix at line {$a->line}. Enter only mr\mrs\ms';
$string['user_status'] = 'Select Status';
$string['select_email'] = 'Select Work Email';
$string['idnumber_select'] = 'Select Employee ID';
$string['skilltype_help'] = 'Enter the skill type of the user';
$string['forum'] = 'Forum';
$string['profilefields'] = 'Profile Fields';
$string['open_userenrollment'] = 'User Enrollment';
$string['open_professionalrole'] = 'Professional Role';
$string['open_speciality'] = 'Speciality';
$string['open_areaofwork'] = 'Area of Work';
$string['registration_no'] = 'Registration Number';
$string['userlog'] = 'User Update Log';
$string['open_knownlanguages'] = 'Known Languages';
$string['errorregistrationno'] = 'Registration Number is required';
$string['erroruserenrollment'] = 'User Enrollment is required';
$string['selectuserenrolment'] = 'Select User Enrollment';
$string['open_regulatorycompliance'] = 'Regulatory Compliance';
$string['password'] = 'Password';
$string['submit'] = 'Submit';
$string['countryrequired'] = 'Select the Country';
$string['dateofbirthrequired'] = 'DOB Required';
$string['companyname'] = 'Company Name';
$string['registrationtitle'] = 'Registration Form';
$string['privacypolicy'] = 'Privacy Policy';
$string['termscondition'] = 'Terms & Condition';
$string['education_level'] = 'Education Level';
$string['jobtitle'] = 'Job Title';
$string['fieldwork'] = 'Field of Work';
$string['registraionsuccess'] = '<div class="text-center">Congratulations, your account has been successfully created.</div>';
$string['backtohome'] = 'Click here to Continue';
$string['phoneexists'] = 'Phone number already exists.';
$string['privacypolicyrequired'] = 'Please Check the Polices.';
$string['termsconditionrequired'] = 'Please Check the Terms & Conditions.';
$string['regisemailbody'] = 'Hi {$a->username}, Your registration successfully completed.';
$string['emailsubject'] = 'Confirmation';
$string['organization_shortname'] = 'Organization Short Name';
$string['activeregistration'] = 'Registration';
$string['policystring'] = 'I have read and accept the ';
$string['termsconditionstring'] = 'I have read and agree to the ';
