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
 * @subpackage local_program
 */
defined('MOODLE_INTERNAL') || die();
global $PAGE, $OUTPUT;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/program/lib.php');
use \local_program\program as program;
use \local_program\form\program_form as program_form;
use local_program\local\userdashboard_content  as DashboardProgram;

class local_program_external extends external_api {

    public static function program_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function program_instance($id, $categorycontextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $categorycontext = context::instance_by_id($categorycontextid, MUST_EXIST);
        self::validate_context($categorycontext);
        $data = array();
        if (!empty($jsonformdata)) {

            $serialiseddata = json_decode($jsonformdata);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }

        $warnings = array();

        $program = new stdClass();

        if($data['id']){
            $open_path = $DB->get_field('local_program', 'open_path', array('id' => $data['id']));
            $costcenterid = explode('/', $open_path)[1];
            $sql = "SELECT DISTINCT(pc.id) FROM {local_custom_fields} AS pc
                JOIN {local_custom_fields} AS cc ON cc.parentid = pc.id
                WHERE pc.depth = 1 AND pc.costcenterid = ".$costcenterid;

            $parentid = $DB->get_records_sql($sql);
            if($parentid){
                $parentcat = [];
                foreach($parentid as $categoryid){
                    $parentcat[] = $categoryid->id;
                }
                $data['parentid'] = implode(',', $parentcat);
            }
        }

        // The last param is the ajax submitted data.
        $mform = new program_form(null, array('form_status' => $form_status), 'post', '', null, true, $data);

        $validateddata = $mform->get_data();
        $pgid = new \stdclass();
        if ($validateddata) {
            // Do the action.
            if($form_status == 0){
                $programid = (new program)->manage_program($validateddata);
            }else if ($form_status == 1){
                if($parentid){
                    $validateddata->parentid = $data['parentid'];
                    $validateddata->costcenterid = $data['open_costcenterid'];
                    $parentids = explode(',', $validateddata->parentid);
                    foreach($parentids as $parentcat){

                        $validateddata->{'category_'.$parentcat} = $data['category_'.$parentcat];
                    }
                    $categories = $DB->get_records('local_category_mapped', array('moduletype'=>'program', 'moduleid'=>$data['id']));
                    if($categories){
                        $validateddata->childcategoryid = [];
                        foreach($categories as $parentcat){
                            $validateddata->childcategoryid[$parentcat->parentid] = $parentcat->id;
                        }
                    }
                }
                if(empty($parentid)){
                    $validateddata->parentid = 0;
                }

                $programid = (new program)->program_other_details($validateddata);
            }else if ($form_status == 2){
                $validateddata->open_costcenterid = $costcenterid;
                $programid = (new program)->program_target_audience($validateddata);
                $pgid->module = 'program';
                $pgid->id = $programid;
                $customcategoryid = get_modulecustomfield_category($pgid,'local_program');
                if(!empty($customcategoryid)){
                    update_custom_targetaudience($customcategoryid,$data,$pgid);
                }
            }

            if(class_exists('\block_trending_modules\lib')){
                $trendingclass = new \block_trending_modules\lib();
                if(method_exists($trendingclass, 'trending_modules_crud')){
                    $trendingclass->trending_modules_crud($programid, 'local_program');
                }
            }        
           
            $formheaders = array_keys($mform->formstatus);
            $next = $form_status + 1;
            $nextform = array_key_exists($next, $formheaders);
            if ($nextform !== false/*&& end($formheaders) !== $form_status*/) {
                $form_status = $next;
                $error = false;
            } else {
                $form_status = -1;
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $programid,
            'form_status' => $form_status);
        return $return;

    }

    public static function program_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }

    public static function delete_program_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'programname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function delete_program_instance($action, $id, $confirm,$programname) {
        global $DB;
        try {
            $categorycontext = (new \local_program\lib\accesslib())::get_module_context($id);
            $DB->delete_records('local_program_level_courses', array('programid' => $id));

            $DB->delete_records('local_program_users', array('programid' => $id));
            $DB->delete_records('local_program_trainers', array('programid' => $id));
            $DB->delete_records('local_program_trainerfb', array('programid' => $id));
            $DB->delete_records('local_category_mapped', array('moduletype'=>'program', 'moduleid'=>$id));

            // delete events in calendar
            $DB->delete_records('event', array('plugin_instance'=>$id, 'plugin'=>'local_program')); // added by sreenivas
            $params = array(
                    'context' => $categorycontext,
                    'objectid' =>$id
            );

            $event = \local_program\event\program_deleted::create($params);
            $event->add_record_snapshot('local_program', $id);
            $event->trigger();
            $DB->delete_records('local_program', array('id' => $id));
            if(class_exists('\block_trending_modules\lib')){
                $trendingclass = new \block_trending_modules\lib();
                if(method_exists($trendingclass, 'trending_modules_crud')){
                    $program_object = new stdClass();
                    $program_object->id = $id;
                    $program_object->module_type = 'local_program';
                    $program_object->delete_record = True;
                    $trendingclass->trending_modules_crud($program_object, 'local_program');
                }
            }
            $return = true;
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }
    public static function delete_program_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    public static function program_course_selector_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $includes = new external_value(
            PARAM_ALPHA,
            'What other contexts to fetch the frameworks from. (all, parents, self)',
            VALUE_DEFAULT,
            'parents'
        );
        $programid = new external_value(
            PARAM_INT,
            'Program Id'
        );
        $levelid = new external_value(
            PARAM_INT,
            'Level Id'
        );
        $classroomcourses = new external_value(
            PARAM_INT,
            'classroomcourses', VALUE_DEFAULT, 0
        );
        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'includes' => $includes,
            'programid' => $programid,
            'levelid' => $levelid,
            'classroomcourses'=>$classroomcourses
        ));
    }

    public static function program_course_selector($query, $categorycontext, $includes, $programid, $levelid, $classroomcourses) {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::program_course_selector_parameters(), array(
            'query' => $query,
            'context' => $categorycontext,
            'includes' => $includes,
            'programid' => $programid,
            'levelid' => $levelid,
            'classroomcourses'=>$classroomcourses
        ));
        $query = $params['query'];
        $includes = $params['includes'];
        $programid = $params['programid'];
        $levelid = $params['levelid'];
        $classroomcourses = $params['classroomcourses'];

        $categorycontext = self::get_context_from_params($params['context']);

        self::validate_context($categorycontext);

        $open_path = $DB->get_field('local_program', 'open_path', array('id' => $programid));
        $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.open_path');
        $cousresql = "SELECT c.id as id, c.fullname FROM {course} as c WHERE c.id > 1 AND c.visible = 1 AND c.open_coursetype = 0 AND c.id not in (SELECT courseid FROM {local_program_level_courses} WHERE programid=$programid and levelid=$levelid) ";
        if(is_siteadmin()){
            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.open_path',$open_path);
        }
        $cousresql .= $costcenterpathconcatsql;

        if($classroomcourses === 1){

            $cousresql .= " AND (EXISTS (SELECT clcrs.id FROM {local_classroom_courses} AS clcrs
                                        WHERE FIND_IN_SET(clcrs.courseid,c.id) > 0 ) > 0)";

        }

        if($query){
            $cousresql .=" AND c.fullname LIKE '%$query%'";
        }

        $courses = $DB->get_records_sql($cousresql);
        return array('courses' => $courses);
    }
    public static function program_course_selector_returns() {
        return new external_single_structure(array(
            'courses' => new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'ID of the course'),
                    'fullname' => new external_value(PARAM_RAW, 'course fullname'),
                ))
            ),
        ));
    }
    public static function program_form_option_selector_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $action = new external_value(
            PARAM_RAW,
            'Action for the program form selector'
        );
        $options = new external_value(
            PARAM_RAW,
            'Action for the program form selector'
        );

        return new external_function_parameters(array(
            'query' => $query,
            'context' => self::get_context_parameters(),
            'action' => $action,
            'options' => $options
        ));
    }

    public static function program_form_option_selector($query, $categorycontext, $action, $options) {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::program_form_option_selector_parameters(), array(
            'query' => $query,
            'context' => $categorycontext,
            'action' => $action,
            'options' => $options
        ));
        $query = trim($params['query']);
        $action = $params['action'];
        $categorycontext = self::get_context_from_params($params['context']);
        $options = $params['options'];
        if (!empty($options)) {
            $formoptions = json_decode($options);
        }
        self::validate_context($categorycontext);
        if ($query && $action) {
            $querieslib = new \local_program\local\querylib();
            $return = array();

            switch($action) {
                case 'program_trainer_selector':
                    $parent = array();
                    if ($formoptions->parnetid > 0) {
                        $parent = array($formoptions->parnetid);
                    }
                    $return = $querieslib->get_user_department_trainerslist(true, $parent, array(),
                        $query);
                break;
                case 'program_institute_selector':
                    $service = array();
                    $service['programid'] = $formoptions->programid;
                    $service['query'] = $query;
                    $return = $querieslib->get_program_institutes($formoptions->institute_type, $service);
                break;

                case 'program_completions_courses_selector':

                    $courses_sql = "SELECT c.id, c.fullname FROM {course} as c JOIN {local_program_level_courses} as lcc on lcc.courseid=c.id where lcc.programid = {$formoptions->programid} AND lcc.levelid = {$formoptions->levelid} ";
                    $return = $DB->get_records_sql($courses_sql);

                break;
                case 'program_room_selector':
                    if (!empty($formoptions->instituteid)) {
                        $locationroomlistssql = "SELECT cr.id, cr.name AS fullname
                                           FROM {local_location_room} AS cr
                                           WHERE cr.visible = 1 AND cr.instituteid = {$formoptions->instituteid}";
                        $return = $DB->get_records_sql($locationroomlistssql);
                    } else {
                        $return = array();
                    }

                break;
            }
            return json_encode($return);
        }
    }
    public static function program_form_option_selector_returns() {
        return new external_value(PARAM_RAW, 'data');
    }
    public static function program_completion_settings_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function program_completion_settings_instance($id, $categorycontextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $categorycontext = context::instance_by_id($categorycontextid, MUST_EXIST);
        self::validate_context($categorycontext);
        $data = array();
        if (!empty($jsonformdata)) {

            $serialiseddata = json_decode($jsonformdata);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }

        $warnings = array();
        $program = new stdClass();
        // The last param is the ajax submitted data.
        $mform = new \local_program\form\program_completion_form(null, array('id' => $data['id'],
            'bcid' => $data['programid'], 'form_status' => $form_status), 'post', '', null,
             true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $program_completionid = (new program)->manage_program_completions($validateddata);
            if ($program_completionid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $program_completionid,
            'form_status' => $form_status);
        return $return;
    }

    public static function program_completion_settings_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function program_course_instance_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'ID', 0),
                'contextid' => new external_value(PARAM_INT, 'The context id', false),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function program_course_instance($id, $categorycontextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $categorycontext = context::instance_by_id($categorycontextid, MUST_EXIST);
        self::validate_context($categorycontext);
        $data = array();
        if (!empty($jsonformdata)) {

            $serialiseddata = json_decode($jsonformdata);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }

        $warnings = array();
        $program = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new programcourse_form(null, array('bcid' => $data['programid'],
            'levelid' => $data['levelid'], 'form_status' => $form_status),
            'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            // Do the action.
            $courseid = (new program)->manage_program_courses($validateddata);
            if ($courseid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $courseid,
            'form_status' => $form_status);
        return $return;
    }

    public static function program_course_instance_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function delete_programcourse_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'programid' => new external_value(PARAM_INT, 'program ID', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            )
        );
    }

    public static function delete_programcourse_instance($action, $id, $programid, $confirm) {
        global $DB;
        try {
            $categorycontext = (new \local_program\lib\accesslib())::get_module_context($programid);
            if ($confirm) {
                $course = $DB->get_field('local_program_level_courses', 'courseid', array('programid' => $programid, 'id' => $id));

                // $program_completiondata =$DB->get_record_sql("SELECT id,courseids
                //                         FROM {local_program_completion}
                //                         WHERE programid = $programid");

                // if ($program_completiondata->courseids != null) {
                //     $program_courseids = explode(',', $program_completiondata->courseids);
                //     $array_diff = array_diff($program_courseids, array($course));
                //     if (!empty($array_diff)) {
                //         $program_completiondata->courseids = implode(',', $array_diff);
                //     } else {
                //         $program_completiondata->courseids = "NULL";
                //     }
                //     $DB->update_record('local_program_completion', $program_completiondata);
                //     $params = array(
                //         'context' => $categorycontext,
                //         'objectid' => $program_completiondata->id
                //     );

                //     $event = \local_program\event\program_completions_settings_updated::create($params);
                //     $event->add_record_snapshot('local_program', $programid);
                //     $event->trigger();
                // }

                $programtrainers = $DB->get_records_menu('local_program_trainers',
                    array('programid' => $programid), 'trainerid', 'id, trainerid');
                if (!empty($programtrainers)) {
                    foreach ($programtrainers as $programtrainer) {
                        $unenrolprogramtrainer = (new program)->manage_program_course_enrolments($course, $programtrainer,
                            'editingteacher', 'unenrol', $pluginname = 'program',$programid);
                    }
                }
                $programusers = $DB->get_records_menu('local_program_users',
                    array('programid' => $programid), 'userid', 'id, userid');
                if (!empty($programusers)) {
                    foreach ($programusers as $programuser) {
                        $unenrolprogramuser = (new program)->manage_program_course_enrolments($course, $programuser,
                            'employee', 'unenrol', $pluginname = 'program',$programid);
                    }
                }
                $params = array(
                    'context' => $categorycontext,
                    'objectid' =>$id
                );

                $event = \local_program\event\program_courses_deleted::create($params);
                $event->add_record_snapshot('local_program_level_courses', $id);
                $event->trigger();
                $DB->delete_records('local_program_level_courses', array('id' => $id));
                $return = true;
            } else {
                $return = false;
            }
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }

    public static function delete_programcourse_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }

    /*sree*/
    public static function submit_instituteform_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array')
            )
        );
    }

    /**
     * form submission of institute name and returns instance of this object
     *
     * @param int $categorycontextid
     * @param [string] $jsonformdata
     * @return institute form submits
     */
    public static function submit_catform_form($categorycontextid, $jsonformdata){
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/program/lib.php');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_instituteform_form_parameters(),
                                    ['contextid' => $categorycontextid, 'jsonformdata' => $jsonformdata]);
        $categorycontext = (new \local_program\lib\accesslib())::get_module_context();
        // We always must call validate_context in a webservice.
        self::validate_context($categorycontext);
        // throw new moodle_exception('Error in creation');
        // die;
        $data = array();

        if (!empty($params['jsonformdata'])) {

            $serialiseddata = json_decode($params['jsonformdata']);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }
        $warnings = array();
         $mform = new local_program\form\catform(null, array(), 'post', '', null, true, $data);
        $category  = new local_program\event\category();
        $valdata = $mform->get_data();

        if ($valdata) {
            if ($valdata->id > 0) {
                $category->category_update_instance($valdata);
            } else {
                $category->category_insert_instance($valdata);
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('Error in creation');
        }
    }


    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_catform_form_returns() {
        return new external_value(PARAM_INT, 'category id');
    }

    public static function manageprogramlevels_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id', true, 1),
                'form_status' => new external_value(PARAM_INT, 'Form position', false, 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'Submitted Form Data', false),
            )
        );
    }

    public static function manageprogramlevels($categorycontextid, $form_status, $jsonformdata) {
        global $PAGE, $DB, $CFG, $USER;
        $categorycontext = context::instance_by_id($categorycontextid, MUST_EXIST);
        self::validate_context($categorycontext);
        $data = array();
        if (!empty($jsonformdata)) {

            $serialiseddata = json_decode($jsonformdata);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }

        $warnings = array();
        $program = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new program_managelevel_form(null, array('id' => $data['id'],
            'programid' => $data['programid'],
            'form_status' => $form_status), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {

            $action = 'create';
            if ($validateddata->id > 0) {
                $action = 'update';
            }
            $levelid = (new program)->manage_program_stream_levels($validateddata);
            if ($levelid > 0) {
                $form_status = -2;
                $error = false;
            } else {
                $error = true;
            }
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $levelid,
            'form_status' => $form_status);
        return $return;
    }

    public static function manageprogramlevels_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function bclevel_unassign_course_parameters(){
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'ID of the program'),
                'levelid' => new external_value(PARAM_INT, 'ID of the program level'),
                'bclcid' => new external_value(PARAM_INT, 'ID of the program level course to be unassigned')
            )
        );
    }
    public static function bclevel_unassign_course($programid, $levelid, $bclcid){
        if ($programid > 0 && $bclcid > 0 && $levelid > 0) {
            $program = new program();
            $program->unassign_courses_to_bclevel($programid, $levelid, $bclcid);
            return true;
        } else {
            throw new moodle_exception('Error in unassigning of course');
            return false;
        }
    }
    public static function bclevel_unassign_course_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function delete_level_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'levelname' => new external_value(PARAM_RAW, 'ID of the record', false),
            )
        );
    }

    public static function delete_level_instance($action, $id, $programid, $confirm,$levelname) {
        global $DB,$USER;
        try {

            $categorycontext = (new \local_program\lib\accesslib())::get_module_context($programid);


            $DB->delete_records('local_program_level_courses', array('levelid' => $id));
            // delete events in calendar
            // $DB->delete_records('event', array('plugin_instance'=>$id, 'plugin'=>'local_program')); // added by sreenivas
            $params = array(
                    'context' => $categorycontext,
                    'objectid' =>$id
            );

            $event = \local_program\event\level_deleted::create($params);
            $event->add_record_snapshot('local_program_levels', $id);
            $event->trigger();
//            $levels = $DB->get_records_sql("SELECT lpl.id, lpl.position FROM {local_program_levels} AS lpl WHERE lpl.position > {$position} AND lpl.programid = {$programid} ");
//            if(count($levels) > 0){
//                foreach($levels AS $level){
//                    --$level->position;
//                    $DB->update_record('local_program_levels', $level);
//                }
//            }
            $DB->delete_records('local_program_levels', array('id' => $id));
            $return = true;
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }

    public static function delete_level_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function manageprogramStatus_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_RAW, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'actionstatusmsg' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'programname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function manageprogramStatus_instance($action, $id, $confirm,$actionstatusmsg,$programname) {
        global $DB,$USER;
        try {
            if ($action === 'selfenrol') {
                $return = (new program)->program_self_enrolment($id,$USER->id, $selfenrol=1);          
            }else{
                $return = (new program)->program_status_action($id, $action);
            }
       
        } catch (dml_exception $ex) {
            print_error('deleteerror', 'local_program');
            $return = false;
        }
        return $return;
    }

    public static function manageprogramStatus_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function inactive_program_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'programname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function inactive_program_instance($action, $id, $confirm,$programname) {
        global $DB;
        try {
            $program=$DB->get_record('local_program',array('id'=>$id));
            $categorycontext = (new \local_program\lib\accesslib())::get_module_context($id);
            $program->visible=0;
            $DB->update_record('local_program', $program);
            if(class_exists('\block_trending_modules\lib')){
                $dataobject = new stdClass();
                $dataobject->update_status = True;
                $dataobject->id = $id;
                $dataobject->module_type = 'local_program';
                $dataobject->module_visible = 0;
                $class = (new \block_trending_modules\lib())->trending_modules_crud($dataobject, 'local_program');
            }
            $params = array(
                    'context' => $categorycontext,
                    'objectid' =>$id
            );
            $event = \local_program\event\program_inactivated::create($params);
            $event->add_record_snapshot('local_program', $id);
            $event->trigger();
            $return = true;
        } catch (dml_exception $ex) {
            print_error('inactiveerror', 'local_program');
            $return = false;
        }
        return $return;
    }
    public static function inactive_program_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function active_program_instance_parameters() {
        return new external_function_parameters(
            array(
                'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
                'id' => new external_value(PARAM_INT, 'ID of the record', 0),
                 'programid' => new external_value(PARAM_INT, 'ID of the record', 0),
                'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
                'programname' => new external_value(PARAM_RAW, 'Action of the event', false),
            )
        );
    }

    public static function active_program_instance($action, $id, $confirm,$programname) {
        global $DB;
        try {
            $program=$DB->get_record('local_program',array('id'=>$id));
            $categorycontext = (new \local_program\lib\accesslib())::get_module_context($id);
            $program->visible=1;
            $DB->update_record('local_program', $program);
            if(class_exists('\block_trending_modules\lib')){
                $dataobject = new stdClass();
                $dataobject->update_status = True;
                $dataobject->id = $id;
                $dataobject->module_type = 'local_program';
                $dataobject->module_visible = 1;
                $class = (new \block_trending_modules\lib())->trending_modules_crud($dataobject, 'local_program');
            }
            $params = array(
                    'context' => $categorycontext,
                    'objectid' =>$id
            );
            $event = \local_program\event\program_activated::create($params);
            $event->add_record_snapshot('local_program', $id);
            $event->trigger();
            $return = true;
        } catch (dml_exception $ex) {
            print_error('inactiveerror', 'local_program');
            $return = false;
        }
        return $return;
    }
    public static function active_program_instance_returns() {
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function data_for_programs_parameters(){
        $filter = new external_value(PARAM_TEXT, 'Filter text');
        $filter_text = new external_value(PARAM_TEXT, 'Filter name',VALUE_OPTIONAL);
        $filter_offset = new external_value(PARAM_INT, 'Offset value',VALUE_OPTIONAL);
        $filter_limit = new external_value(PARAM_INT, 'Limit value',VALUE_OPTIONAL);
        $params = array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        );
        return new external_function_parameters($params);
    }
    public static function data_for_programs($filter, $filter_text='', $filter_offset = 0, $filter_limit = 0){
        global $PAGE;

        if(!$filter)
        {
            $filter = "inprogress";
        }
        $params = self::validate_parameters(self::data_for_programs_parameters(), array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        ));

        $PAGE->set_context($categorycontext);
        $renderable = new local_program\output\program_courses($params['filter'],$params['filter_text'], $params['filter_offset'], $params['filter_limit']);
        $output = $PAGE->get_renderer('block_userdashboard');

        $data= $renderable->export_for_template($output);

        return $data;
    }
    public static function data_for_programs_returns(){
        return new external_single_structure(array (
            'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),
            'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),
            'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'),
            'program_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'), 
            'enableslider'=>  new external_value(PARAM_INT, 'Flag for enable the slider.'),
            'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
            'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
            'functionname' => new external_value(PARAM_TEXT, 'Function name'),
            'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
            'programtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, true),
            'moduledetails' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'ProgramDescription' => new external_value(PARAM_RAW, 'Description of Program'),
                        'ProgramFullname' => new external_value(PARAM_RAW, 'Fullname of Program'),
                        'DisplayProgramFullname' => new external_value(PARAM_RAW, 'Displayed Program Fullname'),
                        'ProgramUrl' => new external_value(PARAM_RAW, 'Url for the Program'),
                        'ProgramIcon' => new external_value(PARAM_RAW, 'Icon for the program'),
                        'rating_element' => new external_value(PARAM_RAW, 'Rating Element for Program'),
                        'index' => new external_value(PARAM_INT, 'Index of Card'),
                    )
                )
            ),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
            'index' => new external_value(PARAM_INT, 'number of courses count'),
            'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
            'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
            'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
            'viewMoreCard' => new external_value(PARAM_BOOL, 'More info card to display'),
            'enrolled_url' => new external_value(PARAM_URL, 'enrolled_url for tab'),//added revathi
            'inprogress_url' => new external_value(PARAM_URL, 'inprogress_url for tab'),
            'completed_url' => new external_value(PARAM_URL, 'completed_url for tab'),
        ));
    }
    public static function data_for_programs_paginated_parameters(){
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function data_for_programs_paginated($options, $dataoptions, $offset, $limit, $categorycontextid, $filterdata){
        global $DB, $PAGE;
        require_login();
        $PAGE->set_context($categorycontextid);

        $decodedoptions = (array)json_decode($options);
        $decodedfilter = (array)json_decode($filterdata);
        $PAGE->set_url('/local/program/userdashboard.php', array('tab' => $filter));
        $filter = $decodedoptions['filter'];
        $filter_text = isset($decodedfilter['search_query']) ? $decodedfilter['search_query'] : '';
        $filter_offset = $offset;
        $filter_limit = $limit;

        $PAGE->set_context($categorycontext);
        $renderable = new local_program\output\program_courses($filter, $filter_text, $filter_offset, $filter_limit);
        $output = $PAGE->get_renderer('local_program');

        $data = $renderable->export_for_template($output);
        $totalcount = $renderable->coursesViewCount;
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'filterdata' => $filterdata,
            'records' => array($data),
            'options' => $options,
            'dataoptions' => $dataoptions,
        ];
    }
    public static function data_for_programs_paginated_returns(){
        return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        'records' => new external_multiple_structure(
                new external_single_structure(array (
                    'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),
                    'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),
                    'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'),
                    'program_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'), 
                    // 'enableslider'=>  new external_value(PARAM_INT, 'Flag for enable the slider.'),
                    'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
                    'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
                    'functionname' => new external_value(PARAM_TEXT, 'Function name'),
                    'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
                    'programtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
                    'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                    'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, false),
                    'moduledetails' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'ProgramDescription' => new external_value(PARAM_RAW, 'Description of Program'),
                                'ProgramFullname' => new external_value(PARAM_RAW, 'Fullname of Program'),
                                'DisplayProgramFullname' => new external_value(PARAM_RAW, 'Displayed Program Fullname'),
                                'ProgramUrl' => new external_value(PARAM_RAW, 'Url for the Program'),
                                'ProgramIcon' => new external_value(PARAM_RAW, 'Icon for the program'),
                                'rating_element' => new external_value(PARAM_RAW, 'Rating Element for Program')
                            )
                        )
                    ),
                    'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                    'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
                    'index' => new external_value(PARAM_INT, 'number of courses count'),
                    'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
                    'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
                    // 'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
                )
            ) 
        )
    ]);
    }
    public static function unenrol_user_parameters(){
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context for the service'),
            'programid' => new external_value(PARAM_INT, 'Program id for the service'),
            'userid' => new external_value(PARAM_INT, 'Userid For the service')
        ]);
    }
    public static function unenrol_user($categorycontextid, $programid, $userid){
        $params = self::validate_parameters(self::unenrol_user_parameters(), array(
            'contextid' => $categorycontextid,
            'programid' => $programid,
            'userid' => $userid
        ));
        $programclass = new \local_program\program();
        $programclass->program_remove_assignusers($programid, [$userid]);
        return true;
    }
    public static function unenrol_user_returns(){
        return new external_value(PARAM_BOOL, 'return');
    }
    public static function level_completion_settings_parameters(){
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'form_status' => new external_value(PARAM_INT, 'Form position', 0),
            'jsonformdata' => new external_value(PARAM_RAW, 'jsonformdata'),
        ]);
    }
    public static function level_completion_settings($contextid, $form_status, $jsonformdata){
        global $DB;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $data = array();

        if (!empty($jsonformdata)) {

            $serialiseddata = json_decode($jsonformdata);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }
        $warnings = array();

        $program = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new \local_program\form\level_completion_form(null, array('id' => $data['id'], 'pid' => $data['programid'],'levelid' => $data['levelid']), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            $programid = $validateddata->programid;
            $levelid = $validateddata->levelid;
            $courses = $validateddata->courseids;
            $programid = (new program)->manage_program_level_completions($programid, $levelid, $courses, $validateddata);
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $programid,
            'form_status' => $form_status = -2);
        return $return;
    }
    public static function level_completion_settings_returns(){
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    public static function program_completion_settings_parameters(){
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'contextid'),
            'form_status' => new external_value(PARAM_INT, 'Form position', 0),
            'jsonformdata' => new external_value(PARAM_RAW, 'jsonformdata'),
        ]);
    }
    public static function program_completion_settings($contextid, $form_status, $jsonformdata){
        global $DB;
        $context = context::instance_by_id($contextid, MUST_EXIST);
        self::validate_context($context);
        $data = array();
        if (!empty($jsonformdata)) {

            $serialiseddata = json_decode($jsonformdata);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }

        $warnings = array();

        $program = new stdClass();

        // The last param is the ajax submitted data.
        $mform = new \local_program\form\program_completion_form(null, array('id' => $data['id'], 'pid' => $data['programid'], 'form_status' => $data['form_status']), 'post', '', null, true, $data);
        $validateddata = $mform->get_data();
        if ($validateddata) {
            $programid = (new program)->program_completion_settings($validateddata);
        } else {
            // Generate a warning.
            throw new moodle_exception('missingprogram', 'local_program');
        }
        $return = array(
            'id' => $programid,
            'form_status' => $form_status = -2);
        return $return;
    }
    public static function program_completion_settings_returns(){
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Context id for the framework'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }
    /**
    * [data_for_program_courses_parameters description]
     * @return parameters for data_for_program_courses
     */
    public static function myprograms_parameters() {
        return new external_function_parameters(
            array(
                'status' => new external_value(PARAM_TEXT, 'status'),
                'search' =>  new external_value(PARAM_TEXT, 'search', VALUE_OPTIONAL, ''),
                'page' =>  new external_value(PARAM_INT, 'page', VALUE_OPTIONAL, 0),
                'perpage' =>  new external_value(PARAM_INT, 'perpage', VALUE_OPTIONAL, 15)
            )
        );
    }

    public static function myprograms($status, $search = '', $page = 0, $perpage = 15) {
        global $PAGE, $DB, $CFG;
        require_once($CFG->dirroot . '/local/ratings/lib.php');
        $params = self::validate_parameters(self::myprograms_parameters(), array(
            'status' => $status, 'search' => $search, 'page' => $page, 'perpage' => $perpage
        ));
        if ($status == 'inprogress') {
            $programs = DashboardProgram::inprogress_programs($search, $page, $perpage);
        } else if ($status == 'completed') {
            $programs = DashboardProgram::completed_programs($search, $page, $perpage);
        } else {
            $programs = DashboardProgram::enrolled_programs($search, $page, $perpage);
        }
        foreach($programs as $program) {

            $programfileurl = (new \local_program\local\general_lib())->get_module_logo_url($program->id);
            $modulerating = $DB->get_field('local_ratings_likes', 'module_rating', array('module_id' => $program->id, 'module_area' => 'local_program'));
            if(!$modulerating){
                 $modulerating = 0;
            }
            $program->rating = round($modulerating);
            $likes = $DB->count_records('local_like', array('likearea' => 'local_program', 'itemid' => $program->id, 'likestatus' => '1'));
            $dislikes = $DB->count_records('local_like', array('likearea' => 'local_program', 'itemid' => $program->id, 'likestatus' => '2'));
            $avgratings = get_rating($program->id, 'local_program');
            $avgrating = round($avgratings->avg);
            $ratingusers = $avgratings->count;
            $program->likes = $likes;
            $program->dislikes = $dislikes;
            $program->avgrating = $avgrating;
            $program->ratingusers = $ratingusers;
            $program->bannerimage =  is_object($programfileurl) ? $programfileurl->out() : $programfileurl;
        }
        return array('programs' => $programs);
    }
    public static function myprograms_returns() {
        return new external_single_structure(array(
                'programs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'fullname' => new external_value(PARAM_RAW, 'fullname'),
                            'shortname' => new external_value(PARAM_RAW, 'shortname'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'rating' => new external_value(PARAM_INT, 'program Rating'),
                            'likes' => new external_value(PARAM_INT, 'program Likes'),
                            'dislikes' => new external_value(PARAM_INT, 'program Dislikes'),
                            'avgrating' => new external_value(PARAM_FLOAT, 'program avgrating'),
                            'ratingusers' => new external_value(PARAM_FLOAT, 'program users rating'),
                            'bannerimage' => new external_value(PARAM_RAW, 'bannerimage'),
                        )
                    ), VALUE_DEFAULT, array()
                )
            )
        );
    }
    /**
    * [data for program levels}
     * @return parameters for programlevels
     */
    public static function programlevels_parameters() {
        return new external_function_parameters(
            array('programid' => new external_value(PARAM_INT, 'programid')
            )
        );
    }

    public static function programlevels($programid) {
        global $PAGE, $CFG;

        $params = self::validate_parameters(self::programlevels_parameters(), array(
            'programid' => $programid
        ));

        $PAGE->set_context(context_system::instance());

        $program_levels = (new program)->programlevels($programid);
        return array('levels' => $program_levels);
    }

    public static function programlevels_returns() {
        return new external_single_structure(array (
                'levels' => new external_multiple_structure(
                     new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'programid' => new external_value(PARAM_INT, 'programid'),
                            'level' => new external_value(PARAM_RAW, 'name'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'status' => new external_value(PARAM_INT, 'status'),
                            'totalcourses' => new external_value(PARAM_INT, 'totalcourses'),
                            'position' => new external_value(PARAM_INT, 'position'),
                            'totalusers' => new external_value(PARAM_INT, 'totalusers'),
                            'activeusers' => new external_value(PARAM_INT, 'activeusers'),
                            'usercreated' => new external_value(PARAM_RAW, 'usercreated'),
                            'timecreated' => new external_value(PARAM_RAW, 'timecreated'),
                            'usermodified' => new external_value(PARAM_RAW, 'usermodified'),
                            'timemodified' => new external_value(PARAM_RAW, 'timemodified'),
                        )
                    ), VALUE_DEFAULT, array()
                 )
            )
        );
    }
    /**
    * [data for program courses}
     * @return parameters for programlevels
     */
    public static function levelcourses_parameters() {
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'programid'),
                'levelid' => new external_value(PARAM_INT, 'levelid', VALUE_OPTIONAL, 0)
            )
        );
    }

    public static function levelcourses($programid, $levelid = 0) {
        global $PAGE, $CFG;

        $params = self::validate_parameters(self::levelcourses_parameters(), array(
            'programid' => $programid, 'levelid' => $levelid
        ));

        $programlevelcourses = (new program)->levelcourses($programid, $levelid);

        return array('courses' => $programlevelcourses);
    }

    public static function levelcourses_returns() {
        return new external_single_structure(array (
                'courses' => new external_multiple_structure(
                     new external_single_structure(
                        array(
                            'bclevelcourseid' => new external_value(PARAM_INT, 'program level course id'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                            'category' => new external_value(PARAM_INT, 'category id'),
                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
                            'summary' => new external_value(PARAM_RAW, 'summary'),
                            'summaryformat' => new external_format_value('summary'),
                            'format' => new external_value(PARAM_PLUGIN,
                                    'course format: weeks, topics, social, site,..'),
                            'showgrades' => new external_value(PARAM_INT,
                                    '1 if grades are shown, otherwise 0', VALUE_OPTIONAL),
                            'newsitems' => new external_value(PARAM_INT,
                                    'number of recent items appearing on the course page', VALUE_OPTIONAL),
                            'startdate' => new external_value(PARAM_INT,
                                    'timestamp when the course start'),
                            'enddate' => new external_value(PARAM_INT,
                                    'timestamp when the course end'),
                            'maxbytes' => new external_value(PARAM_INT,
                                    'largest size of file that can be uploaded into the course',
                                    VALUE_OPTIONAL),
                            'showreports' => new external_value(PARAM_INT,
                                    'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT,
                                    '1: available to student, 0:not available', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
                                    VALUE_OPTIONAL),
                            'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
                                    VALUE_OPTIONAL),
                            'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
                                    VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT,
                                    'timestamp when the course have been created', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT,
                                    'timestamp when the course have been modified', VALUE_OPTIONAL),
                            'enablecompletion' => new external_value(PARAM_INT,
                                    'Enabled, control via completion and activity settings. Disbaled,
                                        not shown in activity settings.',
                                    VALUE_OPTIONAL),
                            'completionnotify' => new external_value(PARAM_INT,
                                    '1: yes 0: no', VALUE_OPTIONAL),
                            'lang' => new external_value(PARAM_SAFEDIR,
                                    'forced course language', VALUE_OPTIONAL),
                            'levelid' => new external_value(PARAM_INT,
                                    'levelid'),
                            'courseimage' => new external_value(PARAM_RAW, 'courseimage'),

                        )
                    ), VALUE_DEFAULT, array()
                 )
            )
        );
    }
     /**
    * [data_for_program_courses_parameters description]
     * @return parameters for data_for_program_courses
     */
    public static function myprogramstatus_parameters() {
        return new external_function_parameters(
            array(
                'programid' => new external_value(PARAM_INT, 'programid')
            )
        );
    }
    public static function myprogramstatus($programid) {
        global $PAGE;

        $params = self::validate_parameters(self::myprogramstatus_parameters(), array('programid' => $programid));

        $programstatus = (new program)->myprogramstatus($programid);
        return $programstatus;
    }
    public static function myprogramstatus_returns() {
        return new external_single_structure(
            array(
                'completion_status' =>  new external_value(PARAM_BOOL, 'completion_status'),
                'levels' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'programid' => new external_value(PARAM_INT, 'programid'),
                            'status' => new external_value(PARAM_INT, 'status'),
                            'position' => new external_value(PARAM_INT, 'position'),
                            'totalcourses' => new external_value(PARAM_INT, 'totalcourses'),
                            'completed' => new external_value(PARAM_INT, 'completed'),
                            'courses' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'id'),
                                        'category' => new external_value(PARAM_INT, 'category'),
                                        'programid' => new external_value(PARAM_INT, 'programid'),
                                        'bclevelcourseid' =>  new external_value(PARAM_INT, 'bclevelcourseid'),
                                        'completionstatus' =>  new external_value(PARAM_INT, 'completionstatus')
                                    )
                                ), VALUE_DEFAULT, array()
                            ),
                        )
                    ), VALUE_DEFAULT, array()
                ),
                'levelcoursestatus' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'programid' => new external_value(PARAM_INT, 'programid'),
                            'bclevelcourseid' =>  new external_value(PARAM_INT, 'bclevelcourseid'),
                            'completionstatus' =>  new external_value(PARAM_INT, 'completionstatus'),
                        )
                    ), VALUE_DEFAULT, array()
                ),
            )
        );
    }
    public static function get_program_info_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'The id of the module'),
            )
        );
    }
    public static function get_program_info($id){
        global $DB;
        $params = self::validate_parameters(self::get_program_info_parameters(),
            ['id' => $id]);
        return (new \local_program\local\general_lib())->get_program_info($id);
    }
    public static function get_program_info_returns(){
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'The id of the module'),
            'name' => new external_value(PARAM_TEXT, 'name'),
            'shortname' => new external_value(PARAM_TEXT, 'shortname'),
            'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL,
                ''),
            'category' => new external_value(PARAM_TEXT, 'category', VALUE_OPTIONAL,
                ''),
            'bannerimage' => new external_value(PARAM_RAW, 'bannerimage'),
            'points' => new external_value(PARAM_INT, 'points', VALUE_OPTIONAL, 0),
            'isenrolled' => new external_value(PARAM_BOOL, 'isenrolled'),
            'startdate' => new external_value(PARAM_INT, 'startdate', VALUE_OPTIONAL, ''),
            'enddate' => new external_value(PARAM_INT, 'enddate', VALUE_OPTIONAL, ''),
            'avgrating' => new external_value(PARAM_FLOAT, 'avgrating', VALUE_OPTIONAL, 0),
            'rating' => new external_value(PARAM_FLOAT, 'rating', VALUE_OPTIONAL, 0),
            'ratedusers' => new external_value(PARAM_INT, 'ratedusers', VALUE_OPTIONAL, 0),
            'likes' => new external_value(PARAM_INT, 'likes', VALUE_OPTIONAL, 0),
            'dislikes' => new external_value(PARAM_INT, 'dislikes', VALUE_OPTIONAL, 0),
            'certificateid' => new external_value(PARAM_RAW, 'certificateid', VALUE_OPTIONAL, 0),
            'open_location' => new external_value(PARAM_RAW, 'open_location'),
        ));
    }

  public static function get_program_records_parameters() {
    return new external_function_parameters([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
            VALUE_DEFAULT, 0),
        'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
            VALUE_DEFAULT, 0),
        'contextid' => new external_value(PARAM_INT, 'contextid'),
        'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
    ]);
  }

  /**
   * lists all courses
   *
   * @param array $options
   * @param array $dataoptions
   * @param int $offset
   * @param int $limit
   * @param int $contextid
   * @param array $filterdata
   * @return array courses list.
   */
  public static function get_program_records($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
    global $DB, $PAGE;
    require_login();
    $PAGE->set_url('/local/courses/courses.php', array());
    $PAGE->set_context($contextid);
    // Parameter validation.
    $params = self::validate_parameters(
        self::get_program_records_parameters(),
        [
            'options' => $options,
            'dataoptions' => $dataoptions,
            'offset' => $offset,
            'limit' => $limit,
            'contextid' => $contextid,
            'filterdata' => $filterdata
        ]
    );
    $offset = $params['offset'];
    $limit = $params['limit'];
    $decodedata = json_decode($params['dataoptions']);
    $filtervalues = json_decode($filterdata);

    $stable = clone $filtervalues;
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $stable->search = '';

    $renderer = $PAGE->get_renderer('local_program');
    $programsres = $renderer->viewprograms_records($stable,$program = null,$status = null,$view_type='card');

    $stable = new \stdClass();
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $stable->status = $decodedata->status;
    $stable->costcenterid = $decodedata->costcenterid;
    $stable->departmentid = $decodedata->departmentid;
    $data = $programsres;
    $totalcount = $programsres['totalprograms'];
    return [
        'totalcount' => $totalcount,
        'length' => $totalcount,
        'filterdata' => $filterdata,
        'records' =>$data,
        'options' => $options,
        'dataoptions' => $dataoptions,
    ];
  }

  /**
   * Returns description of method result value
   * @return external_description
   */

  public static function get_program_records_returns() {
      return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hasprograms' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'program' => new external_value(PARAM_RAW, 'program'),
                                  'programname' => new external_value(PARAM_RAW, 'programname'),
                                  'totallevels' => new external_value(PARAM_INT, 'totallevels'),
                                  // 'programicon' => new external_value(PARAM_RAW, 'programicon'),
                                  'description' => new external_value(PARAM_RAW, 'description'),
                                  'descriptionstring' => new external_value(PARAM_RAW, 'descriptionstring'),
                                  'isdescription' => new external_value(PARAM_RAW, 'isdescription'),
                                  'bannerimage' => new external_value(PARAM_RAW, 'bannerimage'),
                                  'enrolled_users' => new external_value(PARAM_INT, 'enrolled_users'),
                                  'completed_users' => new external_value(PARAM_INT, 'completed_users'),
                                  'programid' => new external_value(PARAM_INT, 'programid'),
                                  // 'editicon' => new external_value(PARAM_RAW, 'editicon'),
                                  // 'deleteicon' => new external_value(PARAM_RAW, 'deleteicon'),
                                  // 'assignusersicon' => new external_value(PARAM_RAW, 'assignusersicon'),
                                  'programcompletion' => new external_value(PARAM_RAW, 'programcompletion'),
                                  'action' => new external_value(PARAM_BOOL, 'action'),
                                  'edit' => new external_value(PARAM_BOOL, 'edit'),
                                  'delete' => new external_value(PARAM_BOOL, 'delete'),
                                  'hide_show' => new external_value(PARAM_BOOL, 'hide_show'),
                                  'assignusers' => new external_value(PARAM_BOOL, 'assignusers'),
                                  'assignusersurl' => new external_value(PARAM_RAW, 'assignusersurl'),
                                  'programcompletionstatus' => new external_value(PARAM_RAW, 'programcompletionstatus'),
                                  'programcompletion_id' => new external_value(PARAM_INT, 'programcompletion_id'),
                                  'hide' => new external_value(PARAM_BOOL, 'hide', VALUE_OPTIONAL),
                                  'show' => new external_value(PARAM_BOOL, 'show', VALUE_OPTIONAL),
                                  'mouse_overicon' => new external_value(PARAM_INT, 'mouse_overicon'),
                                  'actions' => new external_value(PARAM_RAW, 'actions'),
                              )
                          )
                      ),

                      'noprograms' => new external_value(PARAM_BOOL, 'noprograms', VALUE_OPTIONAL),
                      'totalprograms' => new external_value(PARAM_INT, 'totalprograms', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )

      ]);
  }

}
