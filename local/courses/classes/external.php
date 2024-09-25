<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 * Courses external API
 *
 * @package    local_courses
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */

defined('MOODLE_INTERNAL') || die;

use \local_courses\form\custom_course_form as custom_course_form;
use \local_courses\action\insert as insert;
use \local_courses\local\general_lib as general_lib;


require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/local/courses/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');


class local_courses_external extends external_api {

     /**
     * Describes the parameters for submit_create_course_form webservice.
     * @return external_function_parameters
     */
    public static function submit_create_course_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                'form_status' => new external_value(PARAM_INT, 'Form position', 0),
                'id' => new external_value(PARAM_INT, 'Course id', 0),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create course form, encoded as a json array')
            )
        );
    }

    /**
     * Submit the create course form.
     *
     * @param int $contextid The context id for the course.
     * @param int $form_status form position.
     * @param int $id course id -1 as default.
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @return int new course id.
     */
    public static function submit_create_course_form($contextid, $form_status, $id, $jsonformdata) {
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->libdir.'/formslib.php');
        require_once($CFG->dirroot . '/local/courses/lib.php');
        require_once($CFG->dirroot . '/local/custom_category/lib.php');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_create_course_form_parameters(),
                                            ['contextid' => $contextid, 'form_status'=>$form_status,  'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $data = array();

        if (!empty($params['jsonformdata'])) {

            $serialiseddata = json_decode($params['jsonformdata']);
            if(is_object($serialiseddata)){
                $serialiseddata = serialize($serialiseddata);
            }
            parse_str($serialiseddata, $data);
        }
        $warnings = array();
        if ($id) {
            $course = get_course($id);
            $category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
        }else{
            $course = null;
        }

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
        $overviewfilesoptions = course_overviewfiles_options($course);
        if (!empty($course)) {
            // Add context for editor.
                $editoroptions['context'] = $coursecontext;
                $editoroptions['subdirs'] = file_area_contains_subdirs($coursecontext, 'course', 'summary', 0);
                $course = file_prepare_standard_editor($course, 'summary', $editoroptions, $coursecontext, 'course', 'summary', 0);
                if ($overviewfilesoptions) {
                    file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, $coursecontext, 'course', 'overviewfiles', 0);
                }
            $get_coursedetails=$DB->get_record('course',array('id'=>$course->id));
        } else {
            // Editor should respect category context if course context is not set.
            $editoroptions['context'] = $catcontext;
            $editoroptions['subdirs'] = 0;
            $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
            if ($overviewfilesoptions) {
                file_prepare_standard_filemanager($course, 'overviewfiles', $overviewfilesoptions, null, 'course', 'overviewfiles', 0);
            }
        }
        $categories = $DB->get_records('local_category_mapped', array('moduletype'=>'course', 'moduleid'=>$data['id']));
        $data['childcategoryid'] = [];
        foreach($categories as $parentcat){
            $data['childcategoryid'][$parentcat->parentid] = $parentcat->id;
        }

        $plugin_exists = \core_component::get_plugin_directory('local', 'custom_matrix'); 
        if($plugin_exists && !empty($data['performancecatid'])){	
            $performanceparentid = $DB->get_field('local_custom_category','parentid', array('id'=>$data['performancecatid']));
            $data['performancecatid'] = $data['performancecatid'];
            $data['performanceparentid'] = $performanceparentid;         
        }
      
        $params = array(
            'course' => $course,
            'editoroptions' => $editoroptions,
            'returnto' => $returnto,
            'get_coursedetails'=>$get_coursedetails,
            'form_status' => $form_status,
            'costcenterid' => $data['open_path'],
            'courseid' => $data['id'],
            'childcategoryid' => $data['childcategoryid'],
            'performancecatid' => $data['performancecatid'],
            'performanceparentid' => $performanceparentid
        );
        // The last param is the ajax submitted data.
        $mform = new custom_course_form(null, $params, 'post', '', null, true, $data);
        $validateddata = $mform->get_data();

        if ($validateddata) {

            $formheaders = array_keys($mform->formstatus);
            $category_id=$data['category'];
          
            $categorycontext=(new \local_courses\lib\accesslib())::get_module_context($course->id);

            $open_departmentid =$open_subdepartment=0;

            if(isset($data['open_departmentid']) && is_array($data['open_departmentid'])){

                if(is_siteadmin()){
                  $open_departmentid = implode(',',$data['open_departmentid']);
                }else {
                  $open_departmentid = $data['open_departmentid'];
                }

                $open_departmentid = is_null($open_departmentid) ? 0  : $open_departmentid;
            }

            if(isset($data['open_subdepartment']) && is_array($data['open_subdepartment'])){

              $open_subdepartment = implode(',', $data['open_subdepartment']);

                $open_subdepartment = is_null($open_subdepartment) ? 0 : $open_subdepartment;

            }
           
            if ($validateddata->id <= 0) {
                // $validateddata->open_identifiedas=$validateddata->identifiedtype;
                $validateddata->category = $category_id;
                $validateddata->open_departmentid = $open_departmentid;
                $validateddata->open_subdepartment = $open_subdepartment;
                local_costcenter_get_costcenter_path($validateddata);
                $validateddata->open_hrmsrole = '';
                $validateddata->open_location = '';
                $validateddata->open_group = '';
                $validateddata->open_designation = '';

               
                if($validateddata->open_path){
                    $validateddata->category = $DB->get_field('local_costcenter', 'category', array('path' => $validateddata->open_path));
                }

                $validateddata->startdate=time();
                $validateddata->enddate=0;
                $validateddata->performancecatid = $data['performancecatid'];
                $validateddata->performanceparentid = $data['performanceparentid'];
                $courseid = create_course($validateddata, $editoroptions);

                $coursecat = new stdClass();
                $coursecat->moduletype = 'course';
                $coursecat->moduleid = $courseid->id;
                $coursecat->category = 0;
                $coursecat->costcenterid = $validateddata->open_costcenterid;
                category_mapping($coursecat);

                $courseid->module = get_string('course','local_courses');
                $customcategoryid = get_modulecustomfield_category($courseid);
                if(!empty($customcategoryid)){
                    insert_custom_targetaudience($customcategoryid,$courseid);
                }

                // Update course tags.
                if (isset($validateddata->tags)) {
                    $coursecontext = context_course::instance($courseid->id, MUST_EXIST);
                    local_tags_tag::set_item_tags('local_courses', 'courses', $courseid->id, $coursecontext, $validateddata->tags, 0, $data['open_path'], $validateddata->open_departmentid );
                }
                if(class_exists('\block_trending_modules\lib')){
                    $trendingclass = new \block_trending_modules\lib();
                    if(method_exists($trendingclass, 'trending_modules_crud')){
                        $trendingclass->trending_modules_crud($courseid->id, 'local_courses');
                    }
                }
                //$coursedata = $courseid;
                $enrol_status = $validateddata->selfenrol;
               // insert::add_enrol_method_tocourse($coursedata,$enrol_status);
            } elseif($validateddata->id > 0) {
                $open_path=$DB->get_field('course', 'open_path', array('id' => $validateddata->id));
                list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/",$open_path);
                // $validateddata->open_identifiedas=$validateddata->identifiedtype;
     
                if($form_status == 0){
                     $courseid =new stdClass();
                      $courseid->id=$data['id'];
                      $validateddata->category = $category_id;
                    if($validateddata->open_costcenterid !=$org){
                         local_costcenter_get_costcenter_path($validateddata);

                         if($validateddata->open_path){
                            $validateddata->category = $DB->get_field('local_costcenter', 'category', array('path' => $validateddata->open_path));
                         }
                    }
                    $validateddata->performancecatid = $data['performancecatid'];
                    $validateddata->performanceparentid = $data['performanceparentid'];
                    update_course($validateddata, $editoroptions);
                    $where = "costcenterid != ".$validateddata->open_costcenterid." AND moduleid = ".$validateddata->id." AND moduletype = 'course'";
                    $DB->delete_records_select('local_category_mapped', $where);

                    if($validateddata->open_costcenterid != $org){
                        $coursecat = new stdClass();
                        $coursecat->moduletype = 'course';
                        $coursecat->moduleid = $validateddata->id;
                        $coursecat->category = 0;
                        $coursecat->costcenterid = $validateddata->open_costcenterid;
                        category_mapping($coursecat);
                    }


                    // purge appropriate caches in case fix_course_sortorder() did not change anything
                    cache_helper::purge_by_event('changesincourse');
                    cache_helper::purge_by_event('changesincoursecat');


                    if(class_exists('\block_trending_modules\lib')){
                        $trendingclass = new \block_trending_modules\lib();
                        if(method_exists($trendingclass, 'trending_modules_crud')){
                            $trendingclass->trending_modules_crud($courseid->id, 'local_courses');
                        }
                    }
                    
                    // Update course tags.
                    if (isset($validateddata->tags)) {
                        $coursecontext = context_course::instance($courseid->id, MUST_EXIST);
                        local_tags_tag::set_item_tags('local_courses', 'courses', $courseid->id, $coursecontext, $validateddata->tags, 0, $validateddata->open_departmentid);
                    }
                   //  $coursedata = $DB->get_record('course',array('id' => $data['id']));
                   //  insert::add_enrol_method_tocourse($coursedata, $coursedata->selfenrol);

                }else{
                    $data = (object)$data;
                    $unwantedval=array("0"=>"");

                    if(isset($validateddata->startdate)) {
                        $data->startdate=$validateddata->startdate;
                    }
                    if(isset($validateddata->enddate)) {
                        $data->enddate=$validateddata->enddate;
                    }
                
                    // added for startek data saving.

                    if($data->open_hrmsrole != '_qf__force_multiselect_submission' && $data->open_hrmsrole != null)
                    {
                        $data->open_hrmsrole = array_diff($data->open_hrmsrole,$unwantedval);

                    }
                    $data->open_hrmsrole = ($data->open_hrmsrole == '_qf__force_multiselect_submission') ? '' : implode(',',(array)$data->open_hrmsrole);
                    // $data->open_hrmsrole    = (!empty($data->open_hrmsrole)) ? implode(',', array_filter($data->open_hrmsrole)) : null;
                    // if(!empty($data->open_hrmsrole)) {
                    //     $data->open_hrmsrole = $data->open_hrmsrole;
                    // } else {
                    //     $data->open_hrmsrole = NULL;
                    // }
                    if($data->open_location != '_qf__force_multiselect_submission' && $data->open_location != null)
                    {
                        $data->open_location = array_diff($data->open_location,$unwantedval);

                    }
                    $data->open_location = ($data->open_location == '_qf__force_multiselect_submission') ? '' : implode(',',(array)$data->open_location);
                    // $data->open_location    = (!empty($data->open_location)) ? implode(',', array_filter($data->open_location)) : null;
                    // if(!empty($data->open_location)) {
                    //     $data->open_location = $data->open_location;
                    // } else {
                    //     $data->open_location = NULL;
                    // }
                    
                    $courseid = new stdClass();
                    $courseid->id = $data->id;
                    $courseid->module = 'course';
                    if($form_status == 2){

                        local_costcenter_get_costcenter_path($data);
                        $customcategoryid = get_modulecustomfield_category($courseid);
                        if(!empty($customcategoryid)){
                            update_custom_targetaudience($customcategoryid,$data,$courseid);
                        }
                        
                        if($data->open_path){
                            $data->category = $DB->get_field('local_costcenter', 'category', array('path' => $data->open_path));
                        }
                        if($data->open_group != '_qf__force_multiselect_submission' && $data->open_group != null)
                        {
                            $data->open_group = array_diff($data->open_group,$unwantedval);

                        }
                         $data->open_group = ($data->open_group == '_qf__force_multiselect_submission') ? '' : implode(',',(array)$data->open_group);
                        if($data->open_designation != '_qf__force_multiselect_submission' && $data->open_designation != null)
                        {
                            $data->open_designation = array_diff($data->open_designation,$unwantedval);

                        }
                         $data->open_designation = ($data->open_designation == '_qf__force_multiselect_submission') ? '' : implode(',',(array)$data->open_designation);
                    }else{
                        if($validateddata->map_certificate == 1){

                            // $data->open_certificateid = $validateddata->open_certificateid;

                        }else{

                            $data->open_certificateid = null;

                        }
                    }
                    if($form_status == 1){
                        $data->open_points= (!empty($validateddata->open_points)) ? $validateddata->open_points : 0;
                    
                        $data->childcategoryid = $validateddata->childcategoryid;
                        $courseopenpath = $DB->get_field('course', 'open_path', array('id' => $validateddata->id));
                        $data->costcenterid = explode('/', $courseopenpath)[1];
                        $data->open_group = '';
                        $data->open_designation = '';
                        
                        insert_category_mapped($data);
                    }
                    update_course($data);

                   // purge appropriate caches in case fix_course_sortorder() did not change anything
                    cache_helper::purge_by_event('changesincourse');
                    cache_helper::purge_by_event('changesincoursecat');


                }
            }
            $next = $form_status + 1;
            $nextform = array_key_exists($next, $formheaders);
            if ($nextform !== false) {
                $form_status = $next;
                $error = false;
            } else {
                $form_status = -1;
                $error = true;
            }
            $enrolid = $DB->get_field('enrol', 'id' ,array('courseid' => $courseid->id ,'enrol' => 'manual'));
            $existing_method = $DB->get_record('enrol',array('courseid'=> $courseid->id  ,'enrol' => 'self'));
            $courseenrolid = $DB->get_field('course','selfenrol',array('id'=> $courseid->id));
            if($courseenrolid == 1){
                $existing_method->status = 0;
                $existing_method->customint6 = 1;
            }else{
                $existing_method->status = 1;
            }
            $DB->update_record('enrol', $existing_method);

        } else {
            // Generate a warning.
            throw new moodle_exception('Error in submission');
        }
        $return = array(
            'courseid' => $courseid->id,
            'enrolid' => $enrolid,
            'form_status' => $form_status);

        return $return;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_create_course_form_returns() {
       return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, 'Course id'),
            'enrolid' => new external_value(PARAM_INT, 'manual enrol id for the course'),
            'form_status' => new external_value(PARAM_INT, 'form_status'),
        ));
    }

    

  /** Describes the parameters for delete_course webservice.
   * @return external_function_parameters
  */
  public static function delete_course_parameters() {
    return new external_function_parameters(
      array(
        'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
        'id' => new external_value(PARAM_INT, 'ID of the record', 0),
        'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
        'name' => new external_value(PARAM_RAW, 'name', false),
      )
    );
  }

  /**
   * Deletes course
   *
   * @param int $action
   * @param int $confirm
   * @param int $id course id
   * @param string $name
   * @return int new course id.
   */
  public static function delete_course($action, $id, $confirm, $name) {
    global $DB;
    try {
        if ($confirm) {
            $corcat = $DB->get_field('course','category',array('id' => $id));
            $category = $DB->get_record('course_categories',array('id'=>$corcat));
            delete_course($id,false);
            if(class_exists('\block_trending_modules\lib')){
                $trendingclass = new \block_trending_modules\lib();
                if(method_exists($trendingclass, 'trending_modules_crud')){
                    $course_object = new stdClass();
                    $course_object->id = $id;
                    $course_object->module_type = 'local_courses';
                    $course_object->delete_record = True;
                    $trendingclass->trending_modules_crud($course_object, 'local_courses');
                }
            }
            $category->coursecount = $category->coursecount-1;
            $DB->update_record('course_categories',$category);
            $DB->delete_records('local_category_mapped', array('moduletype'=>'course', 'moduleid'=>$id));
            $return = true;
        } else {
            $return = false;
        }
    } catch (dml_exception $ex) {
        print_error('deleteerror', 'local_classroom');
        $return = false;
    }
    return $return;
  }

  /**
   * Returns description of method result value
   * @return external_description
   */

  public static function delete_course_returns() {
      return new external_value(PARAM_BOOL, 'return');
  }

  /* Describes the parameters for global_filters_form_option_selector webservice.
  * @return external_function_parameters
  */
  public static function global_filters_form_option_selector_parameters() {
    $query = new external_value(
          PARAM_RAW,
          'Query string'
    );
    $action = new external_value(
        PARAM_RAW,
        'Action for the classroom form selector'
    );
    $options = new external_value(
        PARAM_RAW,
        'Action for the classroom form selector'
    );
    $searchanywhere = new external_value(
        PARAM_BOOL,
        'find a match anywhere, or only at the beginning'
    );
    $page = new external_value(
        PARAM_INT,
        'Page number'
    );
    $perpage = new external_value(
        PARAM_INT,
        'Number per page'
    );
    return new external_function_parameters(array(
      'query' => $query,
      'action' => $action,
      'options' => $options,
      'searchanywhere' => $searchanywhere,
      'page' => $page,
      'perpage' => $perpage,
    ));
  }

  /**
   * Creates filter elements
   *
   * @param string $query
   * @param int $action
   * @param array $options
   * @param string $searchanywhere
   * @param int $page
   * @param int $perpage
   * @param string $jsonformdata The data from the form, encoded as a json array.
   * @return string filter form element
  */
  public static function global_filters_form_option_selector($query, $action, $options, $searchanywhere, $page, $perpage) {
    global $CFG, $DB, $USER;
    $params = self::validate_parameters(self::global_filters_form_option_selector_parameters(), array(
        'query' => $query,
        'action' => $action,
        'options' => $options,
        'searchanywhere' => $searchanywhere,
        'page' => $page,
        'perpage' => $perpage
    ));
    $query = $params['query'];
    $action = $params['action'];
    $options = $params['options'];
    $searchanywhere=$params['searchanywhere'];
    $page=$params['page'];
    $perpage=$params['perpage'];

    $formoptions = (array)json_decode($options);

    if ($action) {
      $return = array();
      if($action === 'categories' || $action === 'elearning'){
          $filter = 'courses';
      } else if($action === 'email' || $action === 'employeeid' || $action === 'username' || $action === 'users'){
          $filter = 'users';
      } else if($action === 'organizations' || $action === 'departments' || $action === 'subdepartment' || $action === 'department4level' || $action === 'department5level' ){
          $filter = 'costcenter';
      } else{
          $filter = $action;
      }
      $core_component = new core_component();
      $courses_plugin_exist = $core_component::get_plugin_directory('local', $filter);
      if ($courses_plugin_exist) {
          require_once($CFG->dirroot . '/local/' . $filter . '/lib.php');
          $functionname = $action.'_filter';
          $return=$functionname('',$query,$searchanywhere, $page, $perpage);
      }
      return json_encode($return);
    }
  }

  /**
   * Returns description of method result value
   *
   * @return external_description
   */
  public static function global_filters_form_option_selector_returns() {
      return new external_value(PARAM_RAW, 'data');
  }


  /** Describes the parameters for delete_course webservice.
   * @return external_function_parameters
  */
  public static function courses_view_parameters() {
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
  public static function courses_view($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata) {
    global $DB, $PAGE;
    require_login();
    $PAGE->set_url('/local/courses/courses.php', array());
    $PAGE->set_context($contextid);
    // Parameter validation.
    $params = self::validate_parameters(
        self::courses_view_parameters(),
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

    $stable = new \stdClass();
    $stable->thead = false;
    $stable->start = $offset;
    $stable->length = $limit;
    $stable->status = $decodedata->status;
    $stable->costcenterid = $decodedata->costcenterid;
    $stable->departmentid = $decodedata->departmentid;
    $data = get_listof_courses($stable, $filtervalues,$options);
    $totalcount = $data['totalcourses'];
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

  public static function courses_view_returns() {
      return new external_single_structure([
          'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
          'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
          'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
          'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
          'records' => new external_single_structure(
                  array(
                      'hascourses' => new external_multiple_structure(
                          new external_single_structure(
                              array(
                                  'coursename' => new external_value(PARAM_RAW, 'coursename'),
                                  'shortname' => new external_value(PARAM_RAW, 'shortname'),
                                  'coursenameCut' => new external_value(PARAM_RAW, 'coursenameCut', VALUE_OPTIONAL),
                                  'catname' => new external_value(PARAM_RAW, 'catname'),
                                  'costcentername' => new external_value(PARAM_RAW, 'costcentername'),
                                  'catnamestring' => new external_value(PARAM_RAW, 'catnamestring'),
                                  'courseimage' => new external_value(PARAM_RAW, 'catnamestring'),
                                  'enrolled_count' => new external_value(PARAM_INT, 'enrolled_count', VALUE_OPTIONAL),
                                  'courseid' => new external_value(PARAM_INT, 'courseid'),
                                  'completed_count' => new external_value(PARAM_INT, 'completed_count', VALUE_OPTIONAL),
                                  'points' => new external_value(PARAM_INT, 'points', VALUE_OPTIONAL),
                                  'completiondays' => new external_value(PARAM_INT, 'completiondays', VALUE_OPTIONAL),
                                  'coursetype' => new external_value(PARAM_RAW, 'coursetype', VALUE_OPTIONAL),
                                  'coursesummary' => new external_value(PARAM_RAW, 'coursesummary', VALUE_OPTIONAL),
                                  'courseurl' => new external_value(PARAM_RAW, 'courseurl',VALUE_OPTIONAL),
                                  'enrollusers' => new external_value(PARAM_RAW, 'enrollusers', VALUE_OPTIONAL),
                                  'autoenrollusers' => new external_value(PARAM_RAW, 'autoenrollusers', VALUE_OPTIONAL),
                                  'editcourse' => new external_value(PARAM_RAW, 'editcourse', VALUE_OPTIONAL),
                                  'auto_enrol' => new external_value(PARAM_RAW, 'auto_enrol', VALUE_OPTIONAL),
                                  'auto_enrol_active' => new external_value(PARAM_RAW, 'auto_enrol_active', VALUE_OPTIONAL),
                                  'visibleclass' => new external_value(PARAM_RAW, 'visibleclass', VALUE_OPTIONAL),
                                  'update_status' => new external_value(PARAM_RAW, 'update_status', VALUE_OPTIONAL),
                                  'course_class' => new external_value(PARAM_TEXT, 'course_status', VALUE_OPTIONAL),
                                  'deleteaction' => new external_value(PARAM_RAW, 'designation', VALUE_OPTIONAL),
                                  'grader' => new external_value(PARAM_RAW, 'grader', VALUE_OPTIONAL),
                                  'activity' => new external_value(PARAM_RAW, 'activity', VALUE_OPTIONAL),
                                  'requestlink' => new external_value(PARAM_RAW, 'requestlink', VALUE_OPTIONAL),
                                  'skillname' => new external_value(PARAM_RAW, 'skillname', VALUE_OPTIONAL),
                                  'ratings_value' => new external_value(PARAM_RAW, 'ratings_value', VALUE_OPTIONAL),
                                  'ratingenable' => new external_value(PARAM_BOOL, 'ratingenable', VALUE_OPTIONAL),
                                  'tagstring' => new external_value(PARAM_RAW, 'tagstring', VALUE_OPTIONAL),
                                  'tagenable' => new external_value(PARAM_BOOL, 'tagenable', VALUE_OPTIONAL),
                                  'request_view' => new external_value(PARAM_BOOL, 'request_view', VALUE_OPTIONAL),
                                  'grade_view' => new external_value(PARAM_BOOL, 'grade_view', VALUE_OPTIONAL),
                                   'report_view' => new external_value(PARAM_BOOL, 'report_view', VALUE_OPTIONAL),
                                  'delete' => new external_value(PARAM_BOOL, 'delete', VALUE_OPTIONAL),
                                  'update' => new external_value(PARAM_BOOL, 'update', VALUE_OPTIONAL),
                                  'enrol' => new external_value(PARAM_BOOL, 'enrol', VALUE_OPTIONAL),
                                  'actions' => new external_value(PARAM_BOOL, 'actions', VALUE_OPTIONAL),
                              )
                          )
                      ),

                      'nocourses' => new external_value(PARAM_BOOL, 'nocourses', VALUE_OPTIONAL),
                      'totalcourses' => new external_value(PARAM_INT, 'totalcourses', VALUE_OPTIONAL),
                      'length' => new external_value(PARAM_INT, 'length', VALUE_OPTIONAL),
                  )
              )

      ]);
  }

    public static function get_users_course_status_information_parameters() {
        return new external_function_parameters(
            array('status' => new external_value(PARAM_RAW, 'status of course', true),
                'searchterm' => new external_value(PARAM_RAW, 'searchterm', VALUE_OPTIONAL, ''),
                'page' => new external_value(PARAM_INT, 'page', VALUE_OPTIONAL, 0),
                'perpage' => new external_value(PARAM_INT, 'perpage', VALUE_OPTIONAL, 15),
                'source' => new external_value(PARAM_TEXT, 'Parameter to validate the mobile ', VALUE_DEFAULT, 'mobile')
            )
        );
    }
    public static function get_users_course_status_information($status, $searchterm = "", $page = 0, $perpage = 15, $source = 'mobile') {
        global $USER, $DB,$CFG;
        require_once($CFG->dirroot.'/local/ratings/lib.php');
        $result = array();
        if ($status == 'completed') {
            $user_course_info = general_lib::completed_coursenames($searchterm, $page * $perpage, $perpage, $source);
            $total = general_lib::completed_coursenames_count($searchterm, $source);
        } else if ($status == 'inprogress') {
            $user_course_info = general_lib::inprogress_coursenames($searchterm, $page * $perpage, $perpage, $source);
            $total = general_lib::inprogress_coursenames_count($searchterm, $source);
        } else if($status == 'enrolled') {
            if ($page == -1) {
                $page = 0;
                $perpage = 0;
            }
            $user_course_info = general_lib::enrolled_coursenames($searchterm, $page * $perpage, $perpage, $source);
            $total = general_lib::enrolled_coursenames_count($searchterm, $source);
        }

        foreach ($user_course_info as $userinfo) {
            //course image
            if(file_exists($CFG->dirroot.'/local/includes.php')){
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new user_course_details();
                $courseimage = $includes->course_summary_files($userinfo);                
                if(is_object($courseimage)){
                    $courseimage = $courseimage->out();                    
                }else{
                    $courseimage = $courseimage;
                }                
            } 
            $context = context_course::instance($userinfo->id, IGNORE_MISSING);
            list($userinfo->summary,$userinfo->summaryformat) =
                external_format_text($userinfo->summary ,$userinfo->summaryformat , $context->id, 'course', 'summary', null);
                $progress = null;
            // Return only private information if the user should be able to see it.
            if ($userinfo->enablecompletion) {
                $progress = \core_completion\progress::get_course_progress_percentage($userinfo, $userid);
            }
            $modulerating = $DB->get_field('local_ratings_likes', 'module_rating', array('module_id' => $userinfo->id, 'module_area' => 'local_courses'));
            if(!$modulerating){
                 $modulerating = 0;
            }
            $likes = $DB->count_records('local_like', array('likearea'=> 'local_courses', 'itemid'=>$userinfo->id, 'likestatus'=>'1'));
            $dislikes = $DB->count_records('local_like', array('likearea'=> 'local_courses', 'itemid'=>$userinfo->id, 'likestatus'=>'2'));
            $avgratings = get_rating($userinfo->id, 'local_courses');
            $avgrating = $avgratings->avg;
            $ratingusers = $avgratings->count;
            $certificateid = $DB->get_field('tool_certificate_issues', 'code', array('userid' => $USER->id, 'moduletype' => 'course', 'moduleid' => $userinfo->id));
            if(!$certificateid){
                $certificateid = null;
            }
            $result[] = array(
                'id' => $userinfo->id,
                'fullname' => $userinfo->fullname,
                'shortname' => $userinfo->shortname,
                'summary' => $userinfo->summary,
                'summaryformat' => $userinfo->summaryformat,
                'startdate' => $userinfo->startdate,
                'enddate' => $userinfo->enddate,
                'timecreated' => $userinfo->timecreated,
                'timemodified' => $userinfo->timemodified,
                'visible' => $userinfo->visible,
                'idnumber' => $userinfo->idnumber,
                'format' => $userinfo->format,
                'showgrades' => $userinfo->showgrades,
                'lang' => clean_param($userinfo->lang,PARAM_LANG),
                'enablecompletion' => $userinfo->enablecompletion,
                'category' => $userinfo->category,
                'progress' => $progress,
                'rating' => $modulerating,
                'avgrating' => $avgrating,
                'ratingusers' => $ratingusers,
                'likes' => $likes,
                'dislikes' => $dislikes,
                'certificateid' => $certificateid,
                'courseimage' => $courseimage
            );
        }
        if ($total > $perpage) {
            $maxPages = ceil($total/$perpage);
        } else {
            $maxPages = 1;
        }
        return array('modules' => $result, 'total' => $total);
    }
    public static function get_users_course_status_information_returns(){
        return new external_single_structure(
            array(
                'modules' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'=> new external_value(PARAM_INT, 'id of course'),
                            'fullname'=> new external_value(PARAM_RAW, 'fullname of course'),
                            'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                            'summary' => new external_value(PARAM_RAW, 'course summary'),
                            'summaryformat' => new external_value(PARAM_RAW, 'course summary format'),
                            'startdate' => new external_value(PARAM_RAW, 'startdate of course'),
                            'enddate' => new external_value(PARAM_RAW, 'enddate of course'),
                            'timecreated' => new external_value(PARAM_RAW, 'course create time'),
                            'timemodified' => new external_value(PARAM_RAW, 'course modified time'),
                            'visible' => new external_value(PARAM_RAW, 'course status'),
                            'idnumber' => new external_value(PARAM_RAW, 'course idnumber'),
                            'format' => new external_value(PARAM_RAW, 'course format'),
                            'showgrades' => new external_value(PARAM_RAW, 'course grade status'),
                            'lang' => new external_value(PARAM_RAW, 'course language'),
                            'enablecompletion' => new external_value(PARAM_RAW, 'course completion'),
                            'category' => new external_value(PARAM_RAW, 'course category'),
                            'progress' => new external_value(PARAM_FLOAT, 'Progress percentage'),
                            'rating' => new external_value(PARAM_RAW, 'Course rating'),
                            'avgrating' => new external_value(PARAM_FLOAT, 'Course Avg rating'),
                            'ratingusers' => new external_value(PARAM_INT, 'Course rating users'),
                            'likes' => new external_value(PARAM_INT, 'Course Likes'),
                            'dislikes' => new external_value(PARAM_INT, 'Course Dislikes'),
                            'certificateid' => new external_value(PARAM_RAW, 'Certifictate Code', VALUE_OPTIONAL),
                            'courseimage' => new external_value(PARAM_RAW, 'Courseimage', VALUE_OPTIONAL),
                        )
                    )
                ),
                'total' => new external_value(PARAM_INT, 'Total Pages')
            )
        );
    }
    public static function course_update_status_parameters(){
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for survey'),
                'id' => new external_value(PARAM_INT, 'The survey id for wellness'),
                'params' => new external_value(PARAM_RAW, 'optional parameter for default application'),
            )
        );
    }
    public static function course_update_status($contextid, $id, $params){
        global $DB;
        $params = self::validate_parameters(self::course_update_status_parameters(),
                                    ['contextid' => $contextid,'id' => $id, 'params' => $params]);
        $context = (new \local_courses\lib\accesslib())::get_module_context( $id);
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $course = $DB->get_record('course', array('id' => $id), 'id, visible');
        $course->visible = $course->visible ? 0 : 1;
        $course->timemodified = time();
        $return = $DB->update_record('course', $course);
        $costcenterid = $DB->get_field('course','open_path',array('id' => $id));
        if(class_exists('\block_trending_modules\lib')){
            $dataobject = new stdClass();
            $dataobject->update_status = True;
            $dataobject->id = $id;
            $dataobject->module_type = 'local_courses';
            $dataobject->module_visible = $course->visible;
            $dataobject->costcenterid = $costcenterid;
            $class = (new \block_trending_modules\lib())->trending_modules_crud($dataobject, 'local_courses');
        }
        return $return;
    }
    public static function course_update_status_returns(){
        return new external_value(PARAM_BOOL, 'Status');
    }
    public static function get_recently_enrolled_courses_parameters(){
        return new external_function_parameters(
            array(
                'source' => new external_value(PARAM_TEXT, 'The source for the service', VALUE_OPTIONAL, 'mobile')
            )
        );
    }
    public static function get_recently_enrolled_courses($source = 'mobile'){
        global $DB,$USER,$CFG;
        $result = array();
        $enrolledcourses = general_lib::enrolled_coursenames_formobile('', 0, 10, 'recentlyaccessed', $source);
        if(empty($enrolledcourses)){
            $enrolledcourses = general_lib::enrolled_coursenames_formobile('', 0, 10, '', $source);
            $header = get_string('recentlyenrolledcourses', 'local_courses');
        }
        else {
            $header = get_string('recentlyaccessedcourses', 'local_courses');
        }
        foreach ($enrolledcourses as $userinfo) {
             //course image
             if(file_exists($CFG->dirroot.'/local/includes.php')){
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new user_course_details();
                $courseimage = $includes->course_summary_files($userinfo);                
                if(is_object($courseimage)){
                    $courseimage = $courseimage->out();                    
                }else{
                    $courseimage = $courseimage;
                }                
            }
            $context = context_course::instance($userinfo->id, IGNORE_MISSING);
            list($userinfo->summary,$userinfo->summaryformat) =
                external_format_text($userinfo->summary ,$userinfo->summaryformat , $context->id, 'course', 'summary', null);
                $progress = null;
            // Return only private information if the user should be able to see it.
            if ($userinfo->enablecompletion) {
                $progress = \core_completion\progress::get_course_progress_percentage($userinfo, $userid);
            }
            $ratinginfo = $DB->get_record('local_ratings_likes', array('module_id' => $userinfo->id, 'module_area' => 'local_courses'));
            $userrating = $DB->get_field('local_rating', 'rating', array('ratearea' => 'local_courses', 'userid' => $USER->id, 'itemid' => $userinfo->id));
            $certificateid = $DB->get_field('tool_certificate_issues', 'code', array('userid' => $USER->id, 'moduletype' => 'course', 'moduleid' => $userinfo->id));
            if(!$certificateid){
                $certificateid = null;
            }
            $result[] = array(
                'id' => $userinfo->id,
                'fullname' => $userinfo->fullname,
                'shortname' => $userinfo->shortname,
                'summary' => $userinfo->summary,
                'summaryformat' => $userinfo->summaryformat,
                'startdate' => $userinfo->startdate,
                'enddate' => $userinfo->enddate,
                'timecreated' => $userinfo->timecreated,
                'timemodified' => $userinfo->timemodified,
                'visible' => $userinfo->visible,
                'idnumber' => $userinfo->idnumber,
                'format' => $userinfo->format,
                'showgrades' => $userinfo->showgrades,
                'lang' => clean_param($userinfo->lang,PARAM_LANG),
                'enablecompletion' => $userinfo->enablecompletion,
                'category' => $userinfo->category,
                'progress' => $progress,
                'rating' => $userrating ? $userrating : 0,
                'avgrating' => $ratinginfo->module_rating ? $ratinginfo->module_rating : 0,
                'ratingusers' => $ratinginfo->module_rating_users ? $ratinginfo->module_rating_users : 0,
                'certificateid' => $certificateid,
                'courseimage' =>$courseimage,
            );
        }
        if(empty($result)){
                $header = get_string('recentlyenrolledcourses', 'local_courses');
            }
       return array('mycourses' => $result,'heading' => $header);
    }
    public static function get_recently_enrolled_courses_returns(){
        return new external_single_structure(
            array(
                'mycourses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'=> new external_value(PARAM_INT, 'id of course'),
                            'fullname'=> new external_value(PARAM_RAW, 'fullname of course'),
                            'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                            'summary' => new external_value(PARAM_RAW, 'course summary'),
                            'summaryformat' => new external_value(PARAM_RAW, 'course summary format'),
                            'startdate' => new external_value(PARAM_RAW, 'startdate of course'),
                            'enddate' => new external_value(PARAM_RAW, 'enddate of course'),
                            'timecreated' => new external_value(PARAM_RAW, 'course create time'),
                            'timemodified' => new external_value(PARAM_RAW, 'course modified time'),
                            'visible' => new external_value(PARAM_RAW, 'course status'),
                            'idnumber' => new external_value(PARAM_RAW, 'course idnumber'),
                            'format' => new external_value(PARAM_RAW, 'course format'),
                            'showgrades' => new external_value(PARAM_RAW, 'course grade status'),
                            'lang' => new external_value(PARAM_RAW, 'course language'),
                            'enablecompletion' => new external_value(PARAM_RAW, 'course completion'),
                            'category' => new external_value(PARAM_RAW, 'course category'),
                            'progress' => new external_value(PARAM_FLOAT, 'Progress percentage'),
                            'rating' => new external_value(PARAM_INT, 'Course rating', VALUE_OPTIONAL),
                            'avgrating' => new external_value(PARAM_FLOAT, 'Course avgrating', VALUE_OPTIONAL),
                            'ratingusers' => new external_value(PARAM_INT, 'Course rating users',VALUE_OPTIONAL),
                            'certificateid' => new external_value(PARAM_RAW, 'Certifictate Code', VALUE_OPTIONAL),
                            'courseimage' => new external_value(PARAM_RAW, 'Course image', VALUE_OPTIONAL),
                        )
                    )
                 ),
                'heading' => new external_value(PARAM_RAW, 'Heading')
            )
        );
    }
    public static function data_for_courses_parameters(){
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
    public static function data_for_courses($filter, $filter_text='', $filter_offset = 0, $filter_limit = 0){
        global $PAGE;

        $params = self::validate_parameters(self::data_for_courses_parameters(), array(
            'filter' => $filter,
            'filter_text' => $filter_text,
            'filter_offset' => $filter_offset,
            'filter_limit' => $filter_limit
        ));
        $PAGE->set_context((new \local_courses\lib\accesslib())::get_module_context());
        $renderable = new local_courses\output\userdashboard($params['filter'], $params['filter_text'], $params['filter_offset'], $params['filter_limit']);
        $output = $PAGE->get_renderer('local_courses');
        $data= $renderable->export_for_template($output);
        return $data;

    }
    public static function data_for_courses_returns(){
        $return  = new external_single_structure(array(
            'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),
            'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),
            'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'),
            'courses_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'),
            'enableslider'=>  new external_value(PARAM_INT, 'Flag for enable the slider.'),
            'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
            'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
            'functionname' => new external_value(PARAM_TEXT, 'Function name'),
            'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
            'elearningtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
            'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
            'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, true),
            'moduledetails' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        // 'inprogress_coursename' => new external_value(PARAM_RAW, 'Course name'),
                        'lastaccessdate' => new external_value(PARAM_RAW, 'Last access Time'),
                        'course_image_url' => new external_value(PARAM_RAW, 'Course Image'),
                        'coursesummary' => new external_value(PARAM_RAW, 'Course Summary'),
                        'progress' => new external_value(PARAM_RAW, 'Course Progress'),
                        'progress_bar_width' => new external_value(PARAM_RAW, 'Course Progress bar width'),
                        'course_fullname' => new external_value(PARAM_RAW, 'Course Fullname'),
                        'course_fullname' => new external_value(PARAM_RAW, 'Course Fullname'),
                        'course_url' => new external_value(PARAM_RAW, 'Course Url'),
                        'inprogress_coursename_fullname' => new external_value(PARAM_RAW, 'Course Url'),
                        'rating_element' => new external_value(PARAM_RAW, 'Ratings'),
                        'element_tags' => new external_value(PARAM_RAW, 'Course Tags'),
                        // 'indexClass' => new external_value(PARAM_TEXT, 'Index Card Class'),
                        'index' => new external_value(PARAM_INT, 'Index of Card'),
                         'course_completedon' => new external_value(PARAM_RAW, 'course_completedon'),
                         'label_name' => new external_value(PARAM_RAW, 'course_completedon'),
                        )
                    )
            ),
            'viewMoreCard' => new external_value(PARAM_BOOL, 'More info card to display', false),
            'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
            'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
            'index' => new external_value(PARAM_INT, 'number of courses count'),
            'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
            'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
            'templatename' => new external_value(PARAM_TEXT, 'Templatename for tab content'),
            'pluginname' => new external_value(PARAM_TEXT, 'Pluginname for tab content', VALUE_DEFAULT, 'local_courses'),
            'tabname' => new external_value(PARAM_TEXT, 'Pluginname for tab content', VALUE_DEFAULT, 'local_courses'),
            'status' => new external_value(PARAM_TEXT, 'Pluginname for tab content', VALUE_DEFAULT, 'local_courses'),
            'enrolled_url' => new external_value(PARAM_URL, 'view_more_url for tab'),//added revathi
            'inprogress_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
            'completed_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
        ));
        return $return;
    }
    public static function data_for_courses_paginated_parameters(){
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
    public static function data_for_courses_paginated($options, $dataoptions, $offset = 0, $limit = 0, $contextid, $filterdata){
        global $DB, $PAGE;
        require_login();
        $PAGE->set_url('/local/courses/userdashboard.php', array());
        $PAGE->set_context($contextid);

        $decodedoptions = (array)json_decode($options);
        $decodedfilter = (array)json_decode($filterdata);
        $filter = $decodedoptions['filter'];
        $filter_text = isset($decodedfilter['search_query']) ? $decodedfilter['search_query'] : '';
        $filter_offset = $offset;
        $filter_limit = $limit;

        $renderable = new local_courses\output\userdashboard($filter, $filter_text, $filter_offset, $filter_limit);
        $output = $PAGE->get_renderer('local_courses');
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
    public static function data_for_courses_paginated_returns(){
        return new external_single_structure([
        'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
        'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
        'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
        'filterdata' => new external_value(PARAM_RAW, 'The data for the service'),
        'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'total' => new external_value(PARAM_INT, 'Number of enrolled courses.', VALUE_OPTIONAL),
                        'inprogresscount'=>  new external_value(PARAM_INT, 'Number of inprogress course count.'),
                        'completedcount'=>  new external_value(PARAM_INT, 'Number of complete course count.'),
                        'courses_view_count'=>  new external_value(PARAM_INT, 'Number of courses count.'),

                        'inprogress_elearning_available'=>  new external_value(PARAM_INT, 'Flag to check enrolled course available or not.'),
                        'course_count_view'=>  new external_value(PARAM_TEXT, 'to add course count class'),
                        'functionname' => new external_value(PARAM_TEXT, 'Function name'),
                        'subtab' => new external_value(PARAM_TEXT, 'Sub tab name'),
                        'elearningtemplate' => new external_value(PARAM_INT, 'template name',VALUE_OPTIONAL),
                        'nodata_string' => new external_value(PARAM_TEXT, 'no data message'),
                        'enableflow' => new external_value(PARAM_BOOL, "flag for flow enabling", VALUE_DEFAULT, false),
                        'moduledetails' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                // 'inprogress_coursename' => new external_value(PARAM_RAW, 'Course name'),
                                'lastaccessdate' => new external_value(PARAM_RAW, 'Last access Time'),
                                'course_image_url' => new external_value(PARAM_RAW, 'Course Image'),
                                'coursesummary' => new external_value(PARAM_RAW, 'Course Summary'),
                                'progress' => new external_value(PARAM_RAW, 'Course Progress'),
                                'progress_bar_width' => new external_value(PARAM_RAW, 'Course Progress bar width'),
                                'course_fullname' => new external_value(PARAM_RAW, 'Course Fullname'),
                                'course_fullname' => new external_value(PARAM_RAW, 'Course Fullname'),
                                'course_url' => new external_value(PARAM_RAW, 'Course Url'),
                                'inprogress_coursename_fullname' => new external_value(PARAM_RAW, 'Course Url'),
                                'rating_element' => new external_value(PARAM_RAW, 'Ratings'),
                                'element_tags' => new external_value(PARAM_RAW, 'Course Tags'),
                                // 'indexClass' => new external_value(PARAM_TEXT, 'Index Card Class'),
                                'index' => new external_value(PARAM_INT, 'Index of Card'),
                                 'course_completedon' => new external_value(PARAM_RAW, 'course_completedon'),
                                 'label_name' => new external_value(PARAM_RAW, 'course_completedon'),
                            )
                        )
                    ),
                // 'viewMoreCard' => new external_value(PARAM_BOOL, 'More info card to display', false),
                'menu_heading' => new external_value(PARAM_TEXT, 'heading string of the dashboard'),
                'filter' => new external_value(PARAM_TEXT, 'filter for display data'),
                'index' => new external_value(PARAM_INT, 'number of courses count'),
                'filter_text' => new external_value(PARAM_TEXT, 'filtertext content',VALUE_OPTIONAL),
                'view_more_url' => new external_value(PARAM_URL, 'view_more_url for tab'),
                'templatename' => new external_value(PARAM_TEXT, 'Templatename for tab content'),
                'pluginname' => new external_value(PARAM_TEXT, 'Pluginname for tab content', VALUE_DEFAULT, 'local_courses'),
                'tabname' => new external_value(PARAM_TEXT, 'Pluginname for tab content', VALUE_DEFAULT, 'local_courses'),
                'status' => new external_value(PARAM_TEXT, 'Pluginname for tab content', VALUE_DEFAULT, 'local_courses'),
                )
            )
        )
    ]);
    }

    
    public static function get_course_info_parameters(){
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'The id of the module'),
            )
        );
    }
    public static function get_course_info($id){
        global $DB;
        $params = self::validate_parameters(self::get_course_info_parameters(),
            ['id' => $id]);
        return (new \local_courses\local\general_lib())->get_course_info($id);
    }
    public static function get_course_info_returns(){
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'The id of the module'),
            'fullname' => new external_value(PARAM_TEXT, 'fullname'),
            'shortname' => new external_value(PARAM_TEXT, 'shortname'),
            'category' => new external_value(PARAM_TEXT, 'category', VALUE_OPTIONAL, ''),
            'bannerimage' => new external_value(PARAM_RAW, 'bannerimage'),
            'points' => new external_value(PARAM_RAW, 'points'),
            'isenrolled' => new external_value(PARAM_BOOL, 'isenrolled', VALUE_OPTIONAL, false),
            'startdate' => new external_value(PARAM_INT, 'startdate'),
            'enddate' => new external_value(PARAM_INT, 'enddate', VALUE_OPTIONAL, NULL),
            'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL, ''),
            'avgrating' => new external_value(PARAM_FLOAT, 'avgrating', VALUE_OPTIONAL, 0),
            'ratedusers' => new external_value(PARAM_INT, 'ratedusers', VALUE_OPTIONAL, 0),
            'skill' => new external_value(PARAM_TEXT, 'skill', VALUE_OPTIONAL, ''),
            'level' => new external_value(PARAM_TEXT, 'level', VALUE_OPTIONAL, ''),
        ));
    }
    public static function enable_autoenroll_parameters(){
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'The id of the module'),
                'enrolid' => new external_value(PARAM_INT, 'The id of the module'),
            )
        );
    }
    public static function enable_autoenroll($courseid, $enrollid){
        global $DB;
        // $params = self::validate_parameters(self::enable_autoenroll_parameters(),
        //     ['id' => $courseid,'enrolid'=>$enrollid]);
        $data=[];
        $data['status']=general_lib::enable_autoenroll($courseid, $enrollid);
        return $data;
    }
    public static function enable_autoenroll_returns(){
        return new external_single_structure(array(
            'status' => new external_value(PARAM_INT, 'The id of the module'),
        ));
    }    
}
