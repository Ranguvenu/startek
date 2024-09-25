<?php

namespace local_users\cron;
//
// This file is part of eAbyas
//
// Copyright eAbyas Info Solutons Pvt Ltd, India
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author eabyas  <info@eabyas.in>
 * @package BizLMS
 * @subpackage local_users
 */

defined('MOODLE_INTERNAL') || die;


require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_once($CFG->dirroot . '/admin/tool/uploaduser/locallib.php');

use html_writer;
use stdClass;

define('MANUAL_ENROLL', 1);
define('LDAP_ENROLL', 2);
define('SAML2', 3);
define('ADWEBSERVICE', 4);
define('ADD_UPDATE', 3);
class syncfunctionality
{
    private $data;
    private $errors = array();
    private $mfields = array();
    private $warnings = array();
    private $wmfields = array();
    private $errorcount = 0;
    private $orgcount = 0;
    private $warningscount = 0;
    private $updatesupervisor_warningscount = 0;    
    private $insertedcount = 0;
    private $updatedcount = 0;   
    private $existing_user;
    private $mobileno;

    public function __construct($data = null)
    {
        global $CFG;
        $this->data = $data;
        $this->timezones = \core_date::get_list_of_timezones($CFG->forcetimezone);
    } // end of constructor
    public function main_hrms_frontendform_method($cir, $filecolumns, $formdata)
    {
        global $DB, $USER, $CFG;
    
        $linenum = 1;
        $this->organizations = $this->get_organizations();
        $corecomponent = new \core_component();
        $pluginexists = $corecomponent::get_plugin_directory('local', 'domains');
        $positionpluginexists = $corecomponent::get_plugin_directory('local', 'positions');
        if ($pluginexists) {
            $this->domainlist = $this->get_domainlist();
        }
        if ($positionpluginexists) {
            $this->positionlist = $this->get_positionlist();
        }

        $mandatory_fields = [
            'first_name',
            'last_name',
            'username',
            'organization_code',
            'employee_id',
            'employee_status',
            // 'client_code',
            'open_location',
            'departmenttext',
            // 'gender',
            // 'email',
        ];
        $this->mandatory_field_count = 0;
        while ($line = $cir->next()) {
            $this->orgcount = 0;
            $linenum++;
            $user = new \stdClass();
            foreach ($line as $keynum => $value) {
                if (!isset($filecolumns[$keynum])) {
                    continue;
                }
                $key = $filecolumns[$keynum];
                $user->$key = trim($value);
            }
      
            $this->data[] = $user;
            $this->errors = array();
            $this->warnings = array();
            $this->mfields = array();
            $this->wmfields = array();
            $this->excel_line_number = $linenum;
            foreach ($mandatory_fields as $field) {
                // Mandatory field validation.
                $this->mandatory_field_validation($user, $field);
                $this->mandatory_field_count++;
            }

            // To check for existing user record.
            // $sql = "SELECT u.id,u.username,u.open_path, u.email FROM {user} u WHERE ((u.email = :email) OR (u.open_employeeid = :open_employeeid) OR (u.username = :username)) AND u.deleted = 0";
            $sql = "SELECT u.id,u.username,u.open_path, u.email FROM {user} u WHERE ((u.open_employeeid = :open_employeeid) OR (u.username = :username)) AND u.deleted = 0";
            $params = array();
            $params['username'] = $user->username;
            $params['open_employeeid'] = $user->employee_id;
            // $params['email'] = $user->email;
            $existing_user = $DB->get_records_sql($sql, $params);
            if (count($existing_user) == 1) {
                $this->existing_user = array_values($existing_user)[0];
            } else if (count($existing_user) > 1) {
                $this->errors[] = get_string('multiple_user', 'local_users');
            } else {
                $this->existing_user = null;
                $exists = $DB->record_exists('user', array('username' => $user->username));
                if ($exists) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->username = $user->username;
                    echo "<div class='local_users_sync_error'>" . get_string('usernamealeadyexists', 'local_users', $strings) . "</div>";
                    $this->errors[] = get_string('usernamealeadyexists', 'local_users', $strings);
                    $this->mfields[] = "username";
                    $this->errorcount++;
                    continue;
                }

                $exists = $DB->record_exists('user', array('open_employeeid' => $user->employee_id));
                if ($exists) {
                    $strings = new stdClass;
                    $strings->excel_line_number = $this->excel_line_number;
                    $strings->employee_id = $user->employee_id;
                    echo "<div class='local_users_sync_error'>" . get_string('employeeid_alreadyexists', 'local_users', $strings) . "</div>";
                    $this->errors[] = get_string('employeeid_alreadyexists', 'local_users', $strings);
                    $this->mfields[] = "employee_id";
                    $this->errorcount++;
                    continue;
                }

                // $exists = $DB->record_exists('user', array('email' => $user->email));
                // if ($exists) {
                //     $strings = new stdClass;
                //     $strings->excel_line_number = $this->excel_line_number;
                //     $strings->email = $user->email;
                //     echo "<div class='local_users_sync_error'>" . get_string('email_alreadyexists', 'local_users', $strings) . "</div>";
                //     $this->errors[] = get_string('email_alreadyexists', 'local_users', $strings);
                //     $this->mfields[] = "email";
                //     $this->errorcount++;
                //     continue;
                // }
            }

            $patharr = (new \local_costcenter\lib\accesslib())::get_user_role_switch_path();

            foreach ($patharr as $path) {
                list($zero[], $orgid[], $countryid[], $buid[], $cuid[], $territoryid[]) = explode('/', $path);
            }


            $orgid = !empty($orgid) ? array_unique($orgid): $orgid;

            $countryid = !empty($countryid) ? array_unique($countryid): $countryid;

            $buid= !empty($buid) ? array_unique($buid):  $buid;

            $cuid = !empty($cuid) ? array_unique($cuid): $cuid;

            $territoryid = !empty($territoryid) ? array_unique($territoryid): $territoryid;

            // To hold costcenterid.
            if (($this->orgcount == 0)) {
                $this->costcenterid = $this->get_org_hierarchyid($user->organization_code, $parent = 0, $orgid);
            }else{
                 $this->costcenterid =0;
            }

            // To hold countryid.
            if (!empty($user->client_code) && ($this->orgcount == 0)) {
                $client_code =  $user->client_code;
                $this->countryid = $this->get_country_hierarchyid($client_code, $parent = $this->costcenterid, $countryid, $user);
            }else{
                 $this->countryid =0;
            }

            if (!empty($user->lob_code) && ($this->orgcount == 0)) {
                $lob_code = $user->lob_code;
                $this->level3_bussinessid = $this->get_commercial_unitid($lob_code, $this->countryid, $user, $buid);
            }else{
                 $this->level3_bussinessid =0;
            }

            if (!empty($user->subdepartment_code) && ($this->orgcount == 0)) {
                $subdepartment_code =  $user->subdepartment_code;
                $this->level4_commercialid = $this->get_commercial_areaid($subdepartment_code, $this->level3_bussinessid, $user, $cuid);
            }else{
                 $this->level4_commercialid =0;
            }
            // if ($user->territory_code && ($this->orgcount == 0)) {
            //     $territory_code = $user->organization_code."_".$user->client_code."_".$user->lob_code."_".$user->subdepartment_code."_".$user->territory_code;
            //     $this->level5_territoryid = $this->get_territoryid($territory_code, $this->level4_commercialid, $user, $territoryid);
            // }
            // if ($this->costcenterid && ($this->orgcount == 0)) {
            //     $profilefields = $this->get_profile_fields_values($user);
            //     if (!empty($profilefields)) {
            //         $this->open_states = $profilefields->state;
            //         $this->open_district = $profilefields->district;
            //         $this->open_subdistrict = $profilefields->subdistrict;
            //         $this->open_village = $profilefields->village;
            //     }
            // }
            // if (!empty($user->organization_code)) {

            //     $this->categoryvalidations($user);
            // }



            $this->gendervalidations($user);

            if (!empty($user->prefix)) {
                $this->prefixvalidations($user);
            }
            if (!empty($user->timezone)) {
                $this->timezonevalidations($user);
            }
            // Validation for employee status.
            $this->employee_status_validation($user);
            //validation for mobile number
            if (!empty($user->mobileno)) {
                $this->mobilenumber_validation($user);
            }
            if (!empty($user->email)) {
                $this->emailid_validation($user);
            }
            if (!empty($user->employee_id)) {
                $this->empid_validation($user);
            }
            if (!empty($user->date_of_birth)) {
                $this->date_of_birth_validation($user);
            }
            if (!empty($user->date_of_joining)) {
                $this->date_of_joining_validation($user);
            }
            if (!empty($user->force_password_change)) {
                $this->force_password_change_validation($user);
            }
            if (!empty($user->password) && !check_password_policy($user->password, $errmsg)) {
                $strings = new stdClass;
                $strings->errormessage = $errmsg;
                $strings->linenumber = $this->excel_line_number;
                $this->errors[] = get_string('password_upload_error', 'local_users', $strings);
                echo '<div class=local_users_sync_error>' . get_string('password_upload_error', 'local_users', $strings) . '</div>';
                $this->errorcount++;
            }
            $userobject = $this->preparing_users_object($user, $formdata);
            // To display error messages.
            if (count($this->errors) > 0) {
                $this->write_error_in_db($user);
            } else {
                if (is_null($this->existing_user)) {
                    $this->add_row($userobject, $formdata);
                } else {
                    $this->update_row($user, $userobject, $formdata);
                }
            }
            if (count($this->warnings) > 0) {
                $this->write_warnings_db($user);
                $this->updatesupervisor_warningscount = count($this->warnings);
            }
        }
        if (empty($line = $cir->next())) {
            if ($this->mandatory_field_count == 0) {
                foreach ($mandatory_fields as $field) {
                    // Mandatory field validation.
                    $this->mandatory_field_validation($user, $field);
                }
            }
        }
        $upload_info = '<div class="critera_error1"><h3 style="text-decoration: underline;">'
            . get_string('empfile_syncstatus', 'local_users') . '</h3>';
        $upload_info .= '<div class=local_users_sync_success>' . get_string(
            'addedusers_msg',
            'local_users',
            $this->insertedcount
        ) . '</div>';
        $upload_info .= '<div class=local_users_sync_success>' . get_string(
            'updatedusers_msg',
            'local_users',
            $this->updatedcount
        ) . '</div>';
        $upload_info .= '<div class=local_users_sync_error>' . get_string(
            'errorscount_msg',
            'local_users',
            $this->errorcount
        ) . '</div>
            </div>';
        $upload_info .= '<div class=local_users_sync_warning>' . get_string(
            'warningscount_msg',
            'local_users',
            $this->warningscount
        ) . '</div>';
        $upload_info .= '<div class=local_users_sync_warning>' . get_string(
            'superwarnings_msg',
            'local_users',
            $this->updatesupervisor_warningscount
        ) . '</div>';
        $button = html_writer::tag('button', get_string('button', 'local_users'), array('class' => 'btn btn-primary'));
        $link = html_writer::tag('a', $button, array('href' => $CFG->wwwroot . '/local/users/index.php'));
        $upload_info .= '<div class="w-full pull-left text-xs-center">' . $link . '</div>';
        mtrace($upload_info);
        $sync_data = new \stdClass();
        $sync_data->newuserscount = $this->insertedcount;
        $sync_data->updateduserscount = $this->updatedcount;
        $sync_data->errorscount = $this->errorcount;
        $sync_data->warningscount = $this->warningscount;
        $sync_data->supervisorwarningscount = $this->updatesupervisor_warningscount;
        $sync_data->usercreated = $USER->id;
        $sync_data->usermodified = $USER->id;
        $sync_data->timecreated = time();
        $sync_data->timemodified = time();
        $sync_data->costcenterid = $this->costcenterid;
        $insert_sync_data = $DB->insert_record('local_userssyncdata', $sync_data);
    } //end of main_hrms_frontendform_method

    public function get_organizations()
    {
        global $DB;
        $sql = "SELECT shortname, id, parentid FROM {local_costcenter}";
        $costcenterslist = $DB->get_records_sql($sql);
        return $costcenterslist;
    }

    public function categoryvalidations($excel)
    {
        global $DB, $USER;
        $strings = new stdClass;
        $strings->org = $excel->organization_code;
        $strings->dept = $excel->client_code;
        $strings->subdept = $excel->lob_code;
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $categorycontext = (new \local_users\lib\accesslib())::get_module_context();
        $orgerror = 0;
        $categorylib = new \local_courses\catslib();
        if (!is_siteadmin()) {
            $orgcostcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->organization_code));
            if ($orgcostcenterid !== $orgid) {
                echo '<div class=local_users_sync_error>' . get_string('orgcheckwithdhoh', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('orgcheckwithdhoh', 'local_users', $strings);
                $this->mfields[] = 'usercategory';
                $this->errorcount++;
                $orgerror = 1;
            }
        }
        if ($orgerror == 0) {
            if (isset($excel->client_code) && empty($excel->lob_code)) {
                $orgcostcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->organization_code));
                // $categories = $categorylib->get_categories($orgcostcenterid);
                // $countrycategory = $DB->get_field('local_costcenter', 'category', array('shortname' => $excel->country));
                $countrycostcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->client_code));
                if ($countrycostcenterid !== $countryid && !empty($countryid)) {
                    echo '<div class=local_users_sync_error>' . get_string('countrycheckwithdh', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('countrycheckwithdh', 'local_users', $strings);
                    $this->mfields[] = 'usercategory';
                    $this->errorcount++;
                }
            } else if (isset($excel->client_code) && !empty($excel->lob_code)) {
                $countrycostcenterid = $DB->get_field('local_costcenter', 'id', array('shortname' => $excel->client_code));
                if ($countrycostcenterid !== $countryid && !empty($countryid)) {
                    echo '<div class=local_users_sync_error>' . get_string('countrycheckwithdh', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('countrycheckwithdh', 'local_users', $strings);
                    $this->mfields[] = 'usercategory';
                    $this->errorcount++;
                }
            }
        }
    }

    public function countryvalidations($excel)
    {
        $strings = new stdClass;
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $country = get_string_manager()->get_list_of_countries();
        if (!array_key_exists($excel->client_code, $country)) {
            echo '<div class=local_users_sync_error>' . get_string('invalidcountrycode', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('invalidcountrycode', 'local_users', $strings);
            $this->mfields[] = 'usercategory';
            $this->errorcount++;
        }
    }
    public function timezonevalidations($excel)
    {
        $strings = new stdClass;
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        if (!array_key_exists($excel->timezone, $this->timezones)) {
            echo '<div class=local_users_sync_error>' . get_string('invalidtimezone', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('invalidtimezone', 'local_users', $strings);
            $this->mfields[] = 'usercategory';
            $this->errorcount++;
        }
    }
    public function get_org_hierarchyid($fieldvalue, $parent, $orgid)
    {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$fieldvalue];
        $strings = new stdClass;
        if ($parent == 0) {
            $strings->identifier = 'organization';
            $strings->orgid = $fieldvalue;
        } else {
            $strings->identifier = 'client';
            $strings->orgid = $fieldvalue;
        }
        $strings->line = $this->excel_line_number;
        if ($datal) {

            if(is_array($orgid)){

                if (!in_array($datal->id, $orgid) && !empty(array_filter($orgid))) {
                    echo '<div class=local_users_sync_error>' . get_string('orgcheckwithdhoh', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('orgcheckwithdhoh', 'local_users', $strings);
                    $this->mfields[] = $fieldvalue;
                    $this->errorcount++;
                    $this->orgcount++;
                }
            }
            if ($this->orgcount == 0) {
                if ($parent == $datal->parentid) {
                    return $datal->id;
                } else {
                    echo '<div class=local_users_sync_error>' . get_string('noorganizationidfound', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('noorganizationidfound', 'local_users', $strings);
                    $this->mfields[] = $fieldvalue;
                    $this->errorcount++;
                    $this->orgcount++;
                }
            }
        } else {
            echo '<div class=local_users_sync_error>' . get_string('noorganizationidfound', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('noorganizationidfound', 'local_users', $strings);
            $this->mfields[] = $fieldvalue;
            $this->errorcount++;
            $this->orgcount++;
        }
    } //end of get_org_hierarchyid method
    public function get_country_hierarchyid($client_code, $parent, $orgid, $user)
    {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$client_code];
        $strings = new stdClass;
        $strings->countryid = $user->client_code;
        $strings->orgid = $user->lob_code;
        $strings->parentid = $user->client_code;

        $strings->identifier = 'client';
        $strings->orgid = $user->client_code;
        $strings->parentid = $user->organization_code;
        $strings->line = $this->excel_line_number;

        if ($datal) {
            if (!empty($orgid) && !in_array($datal->id, $orgid) && !empty(array_filter($orgid))) {
                echo '<div class=local_users_sync_error>' . get_string('orgcheckwithdhoh', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('orgcheckwithdhoh', 'local_users', $strings);
                $this->mfields[] = $client_code;
                $this->errorcount++;
                $this->orgcount++;
            }

            if ($this->orgcount == 0) {
                if ($parent == $datal->parentid) {
                    return $datal->id;
                } else {
                    echo '<div class=local_users_sync_error>' . get_string('noorganizationidfound', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('noorganizationidfound', 'local_users', $strings);
                    $this->mfields[] = $client_code;
                    $this->errorcount++;
                    $this->orgcount++;
                }
            }
        } else {
            echo '<div class=local_users_sync_error>' . get_string('noorganizationidfound', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('noorganizationidfound', 'local_users', $strings);
            $this->mfields[] = $client_code;
            $this->errorcount++;
            $this->orgcount++;
        }
    } //end of get_org_hierarchyid method

    public function mandatory_field_validation($user, $field)
    {
        //validation for mandatory missing fields
        if (empty(trim($user->$field))) {
            $strings = new stdClass;
            $strings->field = $field;
            $strings->linenumber = $this->excel_line_number;
            echo '<div class=local_users_sync_error>' . get_string('missing', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('missing', 'local_users', $strings);
            $this->mfields[] = $field;
            $this->orgcount++;
            $this->errorcount++;
        }
    } //end of mandatory_field_validation
    public function employee_status_validation($excel)
    {
        //validation for employee status
        $strings = new stdClass;
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $employee_status = $excel->employee_status;
        $this->deletestatus = 0;
        if (array_key_exists('employee_status',(array)$excel)) {
            if (strtolower($excel->employee_status) == 'active') {
                $this->activestatus = 0;
            } else if (strtolower($excel->employee_status) == 'inactive') {
                $this->activestatus = 1;
            } else if (strtolower($excel->employee_status) == 'delete') {
                $this->deletestatus = 1;
            } else if($this->mandatory_field_count == 0){
                $strings = new stdClass;
                $strings->line = $this->excel_line_number;
                echo '<div class=local_users_sync_error>' . get_string('statusvalidation', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('statusvalidation', 'local_users', $strings);
                $this->mfields[] = $excel->employee_status;
                $this->errorcount++;
            }
        } else {
            echo '<div class=local_users_sync_error>Error in arrangement of columns in uploaded excelsheet at line
             ' . $this->excel_line_number . '</div>';
            $this->errormessage = get_string('columnsarragement_error', 'local_users', $excel);
            $this->errorcount++;
        }
    } // end of  employee_status_validation method

    public function gendervalidations($excel)
    {
        //validation for gender
        $strings = new stdClass;
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        if (array_key_exists('gender', (array)$excel)) {
            if (strtolower($excel->gender) == 'male') {
                $this->usergender = 0;
            } else if (strtolower($excel->gender) == 'female') {
                $this->usergender = 1;
            } else if (strtolower($excel->gender) == 'other') {
                $this->usergender = 2;
            } else if($this->mandatory_field_count == 0){
                $strings = new stdClass;
                $strings->line = $this->excel_line_number;
                echo '<div class=local_users_sync_error>' . get_string('invalidgender', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('invalidgender', 'local_users', $strings);
                $this->mfields[] = $excel->gender;
                $this->errorcount++;
            }
        } else {
            echo '<div class=local_users_sync_error>Error in arrangement of columns in uploaded excelsheet at line
             ' . $this->excel_line_number . '</div>';
            $this->errormessage = get_string('columnsarragement_error', 'local_users', $excel);
            $this->errorcount++;
        }
    }

    public function prefixvalidations($excel)
    {
        //validation for gender
        $strings = new stdClass;
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        if (array_key_exists('prefix', (array)$excel)) {
            if (strtolower($excel->prefix) == 'mr') {
                $this->prefix = 1;
            } else if (strtolower($excel->prefix) == 'mrs') {
                $this->prefix = 2;
            } else if (strtolower($excel->prefix) == 'ms') {
                $this->prefix = 3;
            } else {
                $strings = new stdClass;
                $strings->line = $this->excel_line_number;
                echo '<div class=local_users_sync_error>' . get_string('invalidprefix', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('invalidprefix', 'local_users', $strings);
                $this->mfields[] = $excel->gender;
                $this->errorcount++;
            }
        }
    }
    public function date_of_birth_validation($excel){
        global $DB;
        $strings = new stdClass();
        $strings->learner_id = $excel->employee_id;
        $strings->date_of_birth = $excel->date_of_birth;
        $strings->excel_line_number = $this->excel_line_number;

       if(!strtotime($excel->date_of_birth)){

           echo '<div class="local_users_sync_error">'.get_string('validdateofbirth','local_users', $strings).'</div>';
            $this->errors[] = get_string('validdateofbirth','local_users', $excel);
            $this->mfields[] = 'userdateofbirth';
            $this->errorcount++;

       }

    }
    public function date_of_joining_validation($excel){
        global $DB;
        $strings = new stdClass();
        $strings->learner_id = $excel->employee_id;
        $strings->date_of_joining = $excel->date_of_joining;
        $strings->excel_line_number = $this->excel_line_number;

       if(!strtotime($excel->date_of_joining)){

           echo '<div class="local_users_sync_error">'.get_string('validdateofjoining','local_users', $strings).'</div>';
            $this->errors[] = get_string('validdateofjoining','local_users', $excel);
            $this->mfields[] = 'userdateofjoining';
            $this->errorcount++;

       }

    }
    public function empid_validation($excel)
    {
        global $DB;
        $strings = new stdClass();
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $this->learner_id = $excel->employee_id;

        if (preg_match('/[^a-z0-9 ]+/i', $excel->employee_id)) {
            echo '<div class="local_users_sync_error">' . get_string(
                'employeeid_nospecialcharacters',
                'local_users',
                $strings
            ) . '</div>';
            $this->errors[] = get_string('employeeid_nospecialcharacters', 'local_users', $strings);
            $this->mfields[] = "useremployeeid";
            $this->errorcount++;
        }
        // echo $userid."=>userid";die;
        // if ($user = $DB->record_exists('user', array('open_employeeid' => $excel->employee_id))) {
        //     if ($user = $DB->get_record('user', array('open_employeeid' => $excel->employee_id, 'open_path' =>
        //     $this->costcenterid))) {
        //         if ($user->open_path == $this->costcenterid) {
        //             if (!isset($userid) || $user->id != $userid) {
        //                 echo '<div class="local_users_sync_error">' . get_string(
        //                     'employeeid_alreadyexists',
        //                     'local_users',
        //                     $strings
        //                 ) . '</div>';
        //                 $this->errors[] = get_string('employeeid_alreadyexists', 'local_users', $strings);
        //                 $this->mfields[] = "useremployeeid";
        //                 $this->errorcount++;
        //             }
        //         }
        //     }
        // }
    }

    private function write_error_in_db($excel)
    {
        global $DB, $USER;
        //condition to hold the sync errors
        $syncerrors = new \stdclass();
        $today = \local_costcenter\lib::get_userdate('Y-m-d');
        $syncerrors->date_created = time();
        $errors_list = implode(',', $this->errors);
        $mandatory_list = implode(',', $this->mfields);
        $syncerrors->error = $errors_list;
        $syncerrors->modified_by = $USER->id;
        $syncerrors->mandatory_fields = $mandatory_list;
        if (empty($excel->email)) {
            $syncerrors->email = '-';
        } else {
            $syncerrors->email = $excel->email;
        }
        if (empty($excel->employee_id)) {
            $syncerrors->idnumber = '-';
        } else {
            $syncerrors->idnumber = $excel->employee_id;
        }
        $syncerrors->firstname = $excel->first_name;
        $syncerrors->lastname = $excel->first_name;
        $syncerrors->sync_file_name = "Employee";
        $DB->insert_record('local_syncerrors', $syncerrors);
    } // end of write_error_db method

    public function get_super_userid($reportinguserid, $orgid){

        global $DB;

        list($zero, $parentorgid, $countryid, $buid, $cuid, $territoryid) = explode('/', $orgid);

        $sqlparams=array();

        $sqlparams['parentpath'] = $parentorgid ? '%/' . $parentorgid . '/%' : 0;

        $userssql = "SELECT id FROM {user} AS u
                             WHERE u.suspended = 0 AND u.deleted = 0 $concatsql AND CONCAT('/', u.open_path,'/') LIKE :parentpath AND u.open_employeeid = :employee_id";

        $sqlparams['employee_id'] = $reportinguserid;

        $userid = $DB->get_field_sql($userssql, $sqlparams);

        if ($userid) {

            return $userid;

        } else {

            $strings = new \stdClass();
            $strings->empid = $reportinguserid;
            $strings->line = $this->excel_line_number;
            $warningmessage = get_string('nosupervisorempidfound', 'local_users', $strings);
            $this->errormessage = $warningmessage;
            echo '<div class=local_users_sync_warning>' . $warningmessage . '</div>';
            $this->warningscount++;
        }
    }

    public function get_commercial_unitid($commercial_unitid, $parentid, $user, $buid)
    {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$commercial_unitid];
        $strings = new \stdClass();
        $strings->commercial_unitid = $user->lob_code;
        $strings->orgid = $user->lob_code;
        $strings->parentid = $user->client_code;
        $strings->identifier = 'LOB';
        $strings->line = $this->excel_line_number;
        if ($this->orgcount == 0) {
            if ($datal) {

                if(is_array($buid)){

                    if (!in_array($datal->id, $buid)  && !empty(array_filter($buid))) {
                        echo '<div class=local_users_sync_error>' . get_string('orgcheckwithdhoh', 'local_users', $strings) . '</div>';
                        $this->errors[] = get_string('bucheckwithdhoh', 'local_users', $strings);
                        $this->errorcount++;
                        $this->orgcount++;
                    }
                }
                if ($parentid == $datal->parentid) {
                    return $datal->id;
                } else {
                    echo '<div class=local_users_sync_error>' . get_string('invalidbussinessunitgiven', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('invalidbussinessunitgiven', 'local_users', $strings);
                    $this->errorcount++;
                    $this->orgcount++;
                }
            } else {
                echo '<div class=local_users_sync_error>' . get_string('noorcommercial_unitfound', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('noorcommercial_unitfound', 'local_users', $strings);
                $this->errorcount++;
                $this->orgcount++;
            }
        }
    }
    public function get_commercial_areaid($commercial_areaid, $parentid, $user, $cuid)
    {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$commercial_areaid];
        $strings = new \stdClass();
        $strings->commercial_areaid = $user->subdepartment_code;
        $strings->orgid = $user->subdepartment_code;
        $strings->identifier = 'Region';
        $strings->parentid = $user->lob_code;
        $strings->line = $this->excel_line_number;
        if ($this->orgcount == 0) {
            if ($datal) {

                if(is_array($cuid)){

                    if (!in_array($datal->id, $cuid) && !empty(array_filter($cuid))) {
                        echo '<div class=local_users_sync_error>' . get_string('orgcheckwithdhoh', 'local_users', $strings) . '</div>';
                        $this->errors[] = get_string('bucheckwithdhoh', 'local_users', $strings);
                        $this->errorcount++;
                        $this->orgcount++;
                    }
                }
                if ($parentid == $datal->parentid) {
                    return $datal->id;
                } else {
                    echo '<div class=local_users_sync_error>' . get_string('invalidcommercialunitgiven', 'local_users', $strings) . '</div>';
                    $this->errors[]  = get_string('invalidcommercialunitgiven', 'local_users', $strings);
                    $this->errorcount++;
                    $this->orgcount++;
                }
            } else {
                echo '<div class=local_users_sync_error>' . get_string('noorcommercial_areafound', 'local_users', $strings) . '</div>';
                $this->errors[]  = get_string('noorcommercial_areafound', 'local_users', $strings);
                $this->errorcount++;
                $this->orgcount++;
            }
        }
    }
    public function get_territoryid($territoryid, $parentid, $user, $terrid)
    {
        global $DB;
        $datalist = $this->organizations;
        $datal = $datalist[$territoryid];
        $strings = new \stdClass();
        $strings->territoryid = $user->territory_code;
        $strings->orgid = $user->territory_code;
        $strings->identifier = 'Territory';
        $strings->parentid = $user->subdepartment_code;
        $strings->line = $this->excel_line_number;
        if ($this->orgcount == 0) {
            if ($datal) {
                if(is_array($terrid)){

                    if (!in_array($datal->id, $terrid) && !empty(array_filter($terrid))) {
                        echo '<div class=local_users_sync_error>' . get_string('orgcheckwithdhoh', 'local_users', $strings) . '</div>';
                        $this->errors[] = get_string('bucheckwithdhoh', 'local_users', $strings);
                        $this->errorcount++;
                        $this->orgcount++;
                    }
                }
                if ($parentid == $datal->parentid) {
                    return $datal->id;
                } else {
                    echo '<div class=local_users_sync_error>' . get_string('invalidterritorygiven', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('invalidterritorygiven', 'local_users', $strings);
                    $this->errorcount++;
                    $this->orgcount++;
                }
            } else {
                echo '<div class=local_users_sync_error>' . get_string('noorterritoryfound', 'local_users', $strings) . '</div>';
                $this->errors[] = get_string('noorterritoryfound', 'local_users', $strings);
                $this->errorcount++;
                $this->orgcount++;
            }
        }
    }
    public function get_profile_fields_values($user)
    {
        global $DB;
        $locationfields = array('state' => $user->state, 'district' => $user->district, 'subdistrict' => $user->subdistrict, 'village' => $user->village);
        $strings = new \stdClass();
        foreach ($locationfields as $key => $lfield) {
            if (!empty($lfield)) {
                $select = '';
                $where = '';
                $params = array();
                $join = '';
                if ($key == 'state') {
                    $select .= " ls.id as state";
                    $where .= " AND ls.code = :state ";
                    $params[$key] = $user->state;
                    $strings->state = $user->state;
                    $strings->parentid = $user->organization_code;
                }
                if ($key == 'district') {
                    $select .= " ls.id as state, ld.id as district";
                    $join .= " JOIN {local_district} as ld ON ld.statesid=ls.id ";
                    $where .= " AND ld.code = :district ";
                    $params['district'] = $user->district;
                    $strings->district = $user->district;
                    $strings->parentid = $user->state;
                }
                if ($key == 'subdistrict') {
                    $select .= " ls.id as state, ld.id as district,lsd.id as subdistrict";
                    $where .= " AND lsd.code = :subdistrict ";
                    $join .= " JOIN {local_district} as ld ON ld.statesid=ls.id
                    JOIN {local_subdistrict} as lsd ON lsd.districtid=ld.id ";
                    $params['subdistrict'] = $user->subdistrict;
                    $strings->subdistrict = $user->subdistrict;
                    $strings->parentid = $user->district;
                }
                if ($key == 'village') {
                    $select .= " ls.id as state, ld.id as district,lsd.id as subdistrict,lv.id as village ";
                    $where .= " AND lv.code = :village ";
                    $join .= " JOIN {local_district} as ld ON ld.statesid=ls.id
                    JOIN {local_subdistrict} as lsd ON lsd.districtid=ld.id
                    JOIN {local_village} as lv ON lv.subdistrictid=lsd.id ";
                    $params['village'] = $user->village;
                    $strings->village = $user->village;
                    $strings->parentid = $user->subdistrict;
                }
                $sql = " SELECT $select
            FROM {local_states} as ls  $join
            WHERE 1=1 AND ls.costcenterid = $this->costcenterid $where ";
                $data = $DB->get_record_sql($sql, $params);
                if (empty($data)) {
                    $strings->line = $this->excel_line_number;
                    echo '<div class=local_users_sync_error>' . get_string('invalid' . $key . 'value', 'local_users', $strings) . '</div>';
                    $this->errors[] = get_string('invalid' . $key . 'value', 'local_users', $strings);
                    $this->errorcount++;
                    break;
                }
            }
        }
        return $data;
    }
    public function get_prfilefield_validations()
    {
        $strings = new \stdClass();
        $strings->line = $this->excel_line_number;
        echo '<div class=local_users_sync_error>' . get_string('wronglocationfieldvalues', 'local_users', $strings) . '</div>';
        $this->errors[] = get_string('wronglocationfieldvalues', 'local_users', $strings);
        $this->errorcount++;
    }
    public function preparing_users_object($excel, $formdata = null)
    {
        global $USER, $DB, $CFG;
        $user = new \stdclass();
        // $user->auth = "manual"; //by default accepts manual
        $user->mnethostid = 1;
        $user->confirmed = 1;
        $user->suspended = $this->activestatus;
        $user->gender = $this->usergender;
        $user->open_dateofbirth = strtotime($excel->date_of_birth);
        $user->open_joindate = strtotime($excel->date_of_joining);
        $user->idnumber = $excel->employee_id;
        $user->open_prefix = $this->prefix;
        $user->open_employeeid = $excel->employee_id;
        $user->username = strtolower($excel->username);
        $user->firstname = $excel->first_name;
        $user->lastname = $excel->last_name;
        $user->middlename = $excel->middle_name ? $excel->middle_name : ' ';
        $user->phone1 = $excel->mobileno ? $excel->mobileno : '';
        $user->email = strtolower($excel->email);
        $user->country = $excel->client_code ? $excel->client_code : 'IN';
        $user->lang = $excel->language ? $excel->language : 'en';
        $user->open_group = $excel->discipline ? $excel->discipline : ' ';
        $user->learner_status = $excel->employee_status;
        $user->open_level = $excel->level;
        $user->open_employmenttype = $excel->employment_type ? $excel->employment_type : '';

        $user->open_state = $excel->state_name ? $excel->state_name : ' ';
        $user->city = $excel->location ? $excel->location : ' ';
        $user->location = $user->location;
        $user->area = $excel->area ? $excel->area : ' ';
        $user->address = $excel->address ? $excel->address : ' ';
        $user->open_team = $excel->team ? $excel->team : null;
        $user->open_region = $excel->region ? $excel->region : null;
        $user->open_grade = $excel->grade ? $excel->grade : null;
        $user->open_designation = $excel->designation ? $excel->designation : '';
        $user->open_costcenterid = $this->costcenterid;
        $user->open_department = $this->countryid;
        $user->open_subdepartment = $this->level3_bussinessid;
        $user->open_level4department = $this->level4_commercialid;
        $user->open_level5department = $this->level5_territoryid;
        $user->open_states = $this->open_states;
        $user->open_district = $this->open_district;
        $user->open_subdistrict = $this->open_subdistrict;
        $user->open_village = $this->open_village;
        $user->country = $excel->country;
        $user->timezone = in_array($excel->timezone, $this->timezones) ? $excel->timezone : $CFG->forcetimezone;
        local_costcenter_get_costcenter_path($user);
        if ($excel->reportingmanager_empid) {
            $super_user = $this->get_super_userid($excel->reportingmanager_empid, $user->open_path);
            $user->open_supervisorid = $super_user;
        }
        $user->open_hrmsrole = $excel->role;
        $user->institution = $excel->client_code;
        $user->usermodified = $USER->id;
        $user->open_hrmsrole = $excel->departmenttext;
        $user->city = $excel->open_location ? $excel->open_location : ' ';
        if (!empty(trim($excel->password))) {
            $user->password = hash_internal_user_password(trim($excel->password));
        } else {
            unset($user->password);
        }
        if ($this->deletestatus == 1) {
            $user->deleted = 0;
            $user->username = time() . $user->username;
            $user->email = time() . $user->email;
            $user->open_employeeid = time() . $user->open_employeeid;
        }
        if ($formdata) {
            switch ($formdata->enrollmentmethod) {
                case MANUAL_ENROLL:
                    $user->auth = "manual";
                    break;
                case LDAP_ENROLL:
                    $user->auth = "ldap";
                    break;
                case SAML2:
                    $user->auth = "saml2";
                    break;               
                case OTP_ENROLL:
                    $user->auth = "otp";
                    break;
            }
        }
        $user->force_password_change = (empty($excel->force_password_change)) ? 0 : $excel->force_password_change;
        $result = preg_grep("/profile_field_/", array_keys((array)$excel));
        
        if (count($result) > 0) {
            foreach ($result as $key => $val) {
                $user->$val = $excel->$val;
            }            
        }

        return $user;
    } // end of  preparing_users_object method

    public function add_row($userobject, $formdata)
    {
        global $DB, $USER, $CFG;

        local_costcenter_get_costcenter_path($userobject);

        $insertnewuserfromcsv = user_create_user($userobject, false);
        $userobject = (object)$userobject;
        $userobject->id = $insertnewuserfromcsv;

        // Pre-process custom profile menu fields data from csv file.
        $user = uu_pre_process_custom_profile_data($userobject);
        // Save custom profile fields data.
        profile_save_data($user);

        if ($userobject->force_password_change == 1) {
            set_user_preference('auth_forcepasswordchange', $userobject->force_password_change, $insertnewuserfromcsv);
        }
        if ($formdata->createpassword) {
            $usernew = $DB->get_record('user', array('id' => $insertnewuserfromcsv));
            setnew_password_and_mail($usernew);
            unset_user_preference('create_password', $usernew);
            set_user_preference('auth_forcepasswordchange', 1, $usernew);
        }
        $this->insertedcount++;
    } // end of add_row method

    public function update_row($excel, $user1, $formdata)
    {
        global $USER, $DB, $CFG;
        // Condition to get the userid to update the data.
        $userid = $this->existing_user->id;
        if ($userid) {
            $user=clone $user1;
            $user->id = $userid;
            $user->timemodified = time();
            $user->suspended = $this->activestatus;
            $user->gender = $this->usergender;
            $user->open_dateofbirth = strtotime($excel->date_of_birth);
            $user->open_joindate = strtotime($excel->date_of_joining);
            $user->idnumber = $excel->employee_id;
            if (isset($user->open_path)) {
                $existingcostcenter = $this->existing_user->open_path;
                if ($user->open_path != $existingcostcenter) {
                    \core\session\manager::kill_user_sessions($user->id);
                }
            }
        $user->open_level = $excel->level;
            $user->open_prefix = $this->prefix;
            $user->open_costcenterid = $this->costcenterid;
            $user->open_department = $this->countryid;
            $user->open_subdepartment = $this->level3_bussinessid;
            $user->open_level4department = $this->level4_commercialid;
            $user->open_level5department = $this->level5_territoryid;
            $user->open_states = $this->open_states;
            $user->open_district = $this->open_district;
            $user->open_subdistrict = $this->open_subdistrict;
            $user->open_village = $this->open_village;
            $user->country = $excel->country;
            $user->open_hrmsrole = $excel->role;
            $user->institution = $excel->client_code;
            $user->phone1 = $excel->mobileno ? $excel->mobileno : '';
            $user->open_state = $excel->state_name;
            $user->open_designation = $excel->designation;
            $user->usermodified = $USER->id;
            $user->open_group = $excel->discipline;
            $user->open_client = $excel->client;
            $user->open_team = $excel->team;
            $user->open_region = $excel->region ? $excel->region : null;
            $user->open_grade = $excel->grade ? $excel->grade : null;
            $user->timezone = in_array($excel->timezone, $this->timezones) ? $excel->timezone : $CFG->forcetimezone;
            $user->open_hrmsrole = $excel->departmenttext;
            $user->city = $excel->open_location ? $excel->open_location : ' ';
            if (!empty($excel->password)) {
                $user->password = hash_internal_user_password($excel->password);
            } else {
                unset($user->password);
            }
            if ($this->deletestatus == 1) {
                $user->deleted = 0;
                $user->username = time() . $user->username;
                $user->email = time() . $user->email;
                $user->open_employeeid = time() . $user->open_employeeid;
            }
            local_costcenter_get_costcenter_path($user);
            if ($excel->reportingmanager_empid) {
                $super_user = $this->get_super_userid($excel->reportingmanager_empid, $user->open_path);
                $user->open_supervisorid = $super_user;

            }
            user_update_user($user, false);
          
            // Pre-process custom profile menu fields data from csv file.
            $existinguser = uu_pre_process_custom_profile_data($excel);
            $existinguser->id = $user->id;
            
            // Save custom profile fields data from csv file.
            profile_save_data($existinguser);

            if ($formdata->createpassword) {
                $usernew = $DB->get_record('user', array('id' => $user->id));
                setnew_password_and_mail($usernew);
                unset_user_preference('create_password', $usernew);
                set_user_preference('auth_forcepasswordchange', 1, $usernew);
            }
            if ($user->force_password_change == 1) {
                set_user_preference('auth_forcepasswordchange', $user->force_password_change, $user->id);
            } else if ($user->force_password_change == 0) {
                set_user_preference('auth_forcepasswordchange', 0, $user->id);
            }
            $this->updatedcount++;
        }
    } // end of  update_row method

    public function write_warnings_db($excel)
    {
        global $DB, $USER;
        if (!empty($this->warnings) && !empty($this->wmfields)) {
            $syncwarnings = new \stdclass();
            $today = \local_costcenter\lib::get_userdate('Y-m-d');
            $syncwarnings->date_created = strtotime($today);
            $werrors_list = implode(',', $this->warnings);
            $wmandatory_list = implode(',', $this->wmfields);
            $syncwarnings->error = $werrors_list;
            $syncwarnings->modified_by = $USER->id;
            $syncwarnings->mandatory_fields = $wmandatory_list;
            if (empty($excel->email)) {
                $syncwarnings->email = '-';
            } else {
                $syncwarnings->email = $excel->email;
            }
            if (empty($excel->employee_id)) {
                $syncwarnings->idnumber = '-';
            } else {
                $syncwarnings->idnumber = $excel->employee_id;
            }
            $syncwarnings->firstname = $excel->first_name;
            $syncwarnings->lastname = $excel->last_name;
            $syncwarnings->type = 'Warning';
            $DB->insert_record('local_syncerrors', $syncwarnings);
        }
    } // end of write_warning_db method

    public function mobilenumber_validation($excel)
    {
        $strings = new StdClass();
        $strings->learner_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $this->mobileno = $excel->mobileno;
        if (!is_numeric($this->mobileno)) {
            echo '<div class=local_users_sync_error>' . get_string('mobileno_error', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('mobileno_error', 'local_users', $strings);
            $this->mfields[] = 'mobileno';
            $this->errorcount++;
        } else if (($this->mobileno < 999999999 || $this->mobileno > 10000000000)) {
            echo '<div class=local_users_sync_error>' . get_string('validmobileno_error', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('validmobileno_error', 'local_users', $strings);
            $this->mfields[] = 'mobileno';
            $this->errorcount++;
        }
    } //end of mobilenumber_validation method

    public function emailid_validation($excel)
    {
        global $DB;
        $strings = new StdClass();
        $strings->employee_id = $excel->employee_id;
        $strings->excel_line_number = $this->excel_line_number;
        $this->email = $excel->email;
        if (!validate_email($excel->email)) {
            echo '<div class="local_users_sync_error">' . get_string('invalidemail_msg', 'local_users', $strings) . '</div>';
            $this->errors[] = get_string('invalidemail_msg', 'local_users', $strings);
            $this->mfields[] = 'email';
            $this->errorcount++;
        }
    }

    /**
     * [force_password_change_validation description]
     * @param  [type] $excel [description]
     */
    private function force_password_change_validation($excel)
    {
        $this->force_password_change = $excel->force_password_change;
        if (!is_numeric($this->force_password_change) || !(($this->force_password_change == 1) ||
            ($this->force_password_change == 0))) {
            echo '<div class=local_users_sync_error>force_password_change column should have value as 0 or 1 at line
             ' . $this->excel_line_number . '</div>';
            $this->errors[] = 'force_password_change column should value as 0 or 1 at line ' . $this->excel_line_number . '';
            $this->mfields[] = 'force_password_change';
            $this->errorcount++;
        }
    }
    public function get_domainlist()
    {
        global $DB;
        $sql = " SELECT code, id, costcenter FROM {local_domains}";
        $domainlist = $DB->get_records_sql($sql);
        return $domainlist;
    }

    public function get_positionlist()
    {
        global $DB;
        $sql = " SELECT code, id, domain FROM {local_positions}";
        $positionlist = $DB->get_records_sql($sql);
        return $positionlist;
    }
    public function get_domainid($costcenterid, $domain)
    {
        $domainlist = $this->domainlist;
        $datal = $domainlist[$domain];
        if ($datal) {
            if ($costcenterid == $datal->costcenter) {
                return $datal->id;
            }
        } else {
            $strings = new \stdClass();
            $strings->domainid = $domain;
            $strings->orgid = $this->costcenter_shortname;
            $strings->line = $this->excel_line_number;
            $warningmessage = get_string('nodomainfound', 'local_users', $strings);
            $this->errormessage = $warningmessage;
            echo '<div class=local_users_sync_warning>' . $warningmessage . '</div>';
            $this->warningscount++;
        }
    }
    public function get_positionid($domainid, $positiond)
    {
        $positionlist = $this->positionlist;
        $data = $positionlist[$positiond];
        if ($data) {
            if ($domainid == $data->domain) {
                return $data->id;
            }
        } else {
            $strings = new \stdClass();
            $strings->positiond = $positiond;
            $strings->line = $this->excel_line_number;
            $warningmessage = get_string('nopositionfound', 'local_users', $strings);
            $this->errormessage = $warningmessage;
            echo '<div class=local_users_sync_warning>' . $warningmessage . '</div>';
            $this->warningscount++;
        }
    }

    public function get_allusers()
    {
        global $DB;
        $usersql = " SELECT open_employeeid, open_path, id FROM {user}";
        $users = $DB->get_records_sql($usersql);
        return $users;
    }
} //end of class
