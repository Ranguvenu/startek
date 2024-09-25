<?php

// local/filterclass.php
public function get_category_list($costcenter){
	global $DB, $CFG, $USER;
	$systemcontext = (new \local_costcenter\lib\accesslib())::get_module_context();
	
 	if (!is_siteadmin() && has_capability('local/costcenter:assign_multiple_departments_manage', $systemcontext)){
		
		$catid= $DB->get_field('local_costcenter','category',array('shortname'=>'ACD'));
	}elseif(!is_siteadmin() && has_capability('local/courses:enrol', $systemcontext)){
		/*******For The Course Manager both the acd and other costcenter categories should come in Dropdown*****/
		$cat1= $DB->get_field('local_costcenter','category',array('shortname'=>'ACD'));
		$cat2=$DB->get_field('local_costcenter','category',array('id'=>$costcenter));
		$catid=$cat1.','.$cat2;
	}else{
		$catid=$DB->get_field('local_costcenter','category',array('id'=>$costcenter));
	}
		 
	$sql="select id,name  from {course_categories} where id IN ($catid) or parent IN ($catid)";
		 
	$depts=$DB->get_records_sql_menu($sql);
		 
	if($depts){
		foreach($depts as $key => $dept){
			$departments["$key"] = $dept;
		}
	}
	return $departments;
}

// local/filterclass.php
public function category_list(){
	global $DB, $CFG, $USER;
	$catid=$DB->get_field('local_costcenter','category',array());
	$sql="select id,name  from {course_categories} where 1=1";
	$depts=$DB->get_records_sql_menu($sql);
		if($depts){
	  foreach($depts as $key => $dept){
		 $departments["$key"] = $dept;
	  }
	}
	return $departments;
}

// local/courses/classes/external.php
 /**
 * Describes the parameters for submit_create_course_form webservice.
 * @return external_function_parameters
 */
public static function submit_create_category_form_parameters() {
    return new external_function_parameters(
        array(
            'contextid' => new external_value(PARAM_INT, 'The context id for the category'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create course form, encoded as a json array')
        )
    );
}

// local/courses/classes/external.php

/**
 * Submit the create category form.
 *
 * @param int $contextid The context id for the category.
 * @param string $jsonformdata The data from the form, encoded as a json array.
 * @return int new category id.
 */
public static function submit_create_category_form($contextid, $jsonformdata) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot.'/course/lib.php');
    //require_once($CFG->libdir.'/coursecatlib.php');
    require_once($CFG->dirroot . '/local/courses/lib.php');

    // We always must pass webservice params through validate_parameters.
    $params = self::validate_parameters(self::submit_create_course_form_parameters(),
                                        ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

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
    $id = $data['id'];
    if ($id) {
        $coursecat = core_course_category::get($id, MUST_EXIST, true);
    }

    // The last param is the ajax submitted data.
    $mform = new local_courses\form\coursecategory_form(null, array(), 'post', '', null, true, $data);

    $validateddata = $mform->get_data();
    if ($validateddata) {
        if ($validateddata->id > 0) {
            if ((int)$validateddata->parent !== (int)$coursecat->parent && !$coursecat->can_change_parent($validateddata->parent)) {
                print_error('cannotmovecategory');
            }
            $category = $coursecat->update($validateddata, $mform->get_description_editor_options());
        } else {
            $category = core_course_category::create($validateddata, $mform->get_description_editor_options());
        }

    } else {
        // Generate a warning.
        throw new moodle_exception(get_string('errorinsubmission', 'local_courses'));
    }

    return $category->id;
}


// local/courses/classes/external.php
/**
 * Returns description of method result value.
 *
 * @return external_description
 * @since Moodle 3.0
 */
public static function submit_create_category_form_returns() {
    return new external_value(PARAM_INT, 'category id');
}

// local/courses/classes/external.php
/**
 * Describes the parameters for delete_category_form webservice.
 * @return external_function_parameters
 */
public static function submit_delete_category_form_parameters() {
    return new external_function_parameters(
        array(
            //'evalid' => new external_value(PARAM_INT, 'The evaluation id '),
            'contextid' => new external_value(PARAM_INT, 'The context id for the category'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create category form, encoded as a json array'),
            'categoryid' => new external_value(PARAM_INT, 'The category id for the category')
        )
    );
}


// local/courses/classes/external.php
/**
 * Submit the delete category form.
 *
 * @param int $contextid The context id for the category.
 * @param int $categoryid The id for the category.
 * @param string $jsonformdata The data from the form, encoded as a json array.
 * @return int new category id.
 */
public static function submit_delete_category_form($contextid, $jsonformdata, $categoryid) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot.'/course/lib.php');
   // require_once($CFG->libdir.'/coursecatlib.php');
    require_once($CFG->dirroot . '/local/courses/lib.php');

    // We always must pass webservice params through validate_parameters.
    $params = self::validate_parameters(self::submit_create_course_form_parameters(),
                                        ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

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
    if ($categoryid) {
        $category = core_course_category::get($categoryid);
        $context = context_coursecat::instance($category->id);
    }else {
        $category = core_course_category::get_default();
        $categoryid = $category->id;
        $context = context_coursecat::instance($category->id);
    }

    // The last param is the ajax submitted data.
    $mform = new local_courses\form\deletecategory_form(null, $category, 'post', '', null, true, $data);
    $validateddata = $mform->get_data();
    if ($validateddata) {
        // The form has been submit handle it.
            if ($validateddata->fulldelete == 1 && $category->can_delete_full()) {
                $continueurl = new moodle_url('/local/custom_category/index.php');
                if ($category->parent != '0') {
                    $continueurl->param('categoryid', $category->parent);
                }
                $deletedcourses = $category->delete_full(false);
            } else if ($validateddata->fulldelete == 0 && $category->can_move_content_to($validateddata->newparent)) {
                $deletedcourses = $category->delete_move($validateddata->newparent, false);
            } else {
                // Some error in parameters (user is cheating?)
                $mform->display();
            }

    } else {
        // Generate a warning.
        throw new moodle_exception(get_string('errorinsubmission', 'local_courses'));
    }

        return true;
}


// local/courses/classes/external.php
/**
 * Returns description of method result value.
 *
 * @return external_description
 * @since Moodle 3.0
 */
public static function submit_delete_category_form_returns() {
    return new external_value(PARAM_INT, '');
}

// local/courses/classes/external.php
/**
 * Describes the parameters for submit_evidence_course_form webservice.
 * @return external_function_parameters
 */
public static function submit_evidence_course_form_parameters() {
    return new external_function_parameters(
        array(
            'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
            'form_status' => new external_value(PARAM_INT, 'Form position', 0),
            'id' => new external_value(PARAM_INT, 'Course id', 0),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create course form, encoded as a json array')
        )
    );
}

// local/courses/classes/external.php
/**
 * Submit the create course form.
 *
 * @param int $contextid The context id for the course.
 * @param int $form_status form position.
 * @param int $id course id -1 as default.
 * @param string $jsonformdata The data from the form, encoded as a json array.
 * @return int new course id.
 */
public static function submit_evidence_course_form($contextid, $form_status, $id, $jsonformdata) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot.'/course/lib.php');
    require_once($CFG->dirroot . '/local/courses/lib.php');

    // We always must pass webservice params through validate_parameters.
    $params = self::validate_parameters(self::submit_evidence_course_form_parameters(),
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

    $params = array(
        'courseid' => $data['courseid'],
        'userid' =>$data['userid'],
    );
    $mform = new custom_courseevidence_form(null, $params, 'post', '', null, true,$data);
    $validateddata = $mform->get_data();
    if ($validateddata) {
        $coursecontext = context_course::instance($data['courseid']);
      
        file_save_draft_area_files($validateddata->files_filemanager, $coursecontext->id, 'local_courses', 'usercourseevidence_files', $data['userid']);

    } else {
        // Generate a warning.
        throw new moodle_exception(get_string('errorinsubmission', 'local_courses'));
    }
    $return = array(
        'courseid' => $data['courseid'],
        'enrolid' => $data['userid'],
        'form_status' => -1);

    return $return;
}

// local/courses/classes/external.php
/**
 * Returns description of method result value.
 *
 * @return external_description
 * @since Moodle 3.0
 */
public static function submit_evidence_course_form_returns() {
   return new external_single_structure(array(
        'courseid' => new external_value(PARAM_INT, 'Course id'),
        'enrolid' => new external_value(PARAM_INT, 'manual enrol id for the course'),
        'form_status' => new external_value(PARAM_INT, 'form_status'),
    ));
}

// local/courses/classes/external.php
public static function submit_course_type_form_parameters()
{
    return new external_function_parameters(
        array(
            //'coursetype_id' => new external_value(PARAM_INT, 'The course id for the unenrol course'),
            'contextid' => new external_value(PARAM_INT, 'The context id for the unenrol course'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create featured course form, encoded as a json array')
        )
    );
}
// local/courses/classes/external.php
public static function submit_course_type_form($contextid, $jsonformdata)
{
    global $DB, $CFG, $USER;
    // We always must pass webservice params through validate_parameters.
    $params = self::validate_parameters(
        self::submit_course_type_form_parameters(),
        ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]
    );

    $context = context::instance_by_id($params['contextid'], MUST_EXIST);
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

    // The last param is the ajax submitted data.
    $mform = new local_courses\form\coursetype_form(null, array('contextid' => $contextid), 'post', '', null, true, $data);

    $validateddata = $mform->get_data();
    if ($validateddata) {
        $data = new stdClass();
        $data->name = $validateddata->name;
        $data->orgid=$validateddata->open_costcenterid;
        $data->shortname = str_replace(' ', '', trim($validateddata->shortname));
        if ($validateddata->id > 0) {
            $data->id = $validateddata->id;
            $data->usermodified = $USER->id;
            $data->timemodified = time();
            $coursetypeeupdate = $DB->update_record('local_course_types', $data);
        } else {
            $data->usercreated = $USER->id;
            $data->timecreated = time();
            $data->active = 1;
            $coursetypeinsert = $DB->insert_record('local_course_types', $data);
        }
    } else {
        // Generate a warning.
        throw new moodle_exception('Error in submission');
    }
}


// local/courses/classes/external.php
public static function submit_course_type_form_returns()
{
    return new external_value(PARAM_INT, 'course type id');
}


// local/courses/classes/external.php
/** Describes the parameters for delete_coursetype webservice.
 * @return external_function_parameters
 */
public static function delete_coursetype_parameters()
{
    return new external_function_parameters(
        array(
            'action' => new external_value(PARAM_ACTION, 'Action of the event', false),
            'coursetypeid' => new external_value(PARAM_INT, 'ID of the record', 0),
            'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            'name' => new external_value(PARAM_RAW, 'name', false),
        )
    );
}


// local/courses/classes/external.php
/**
 * Deletes course type
 *
 * @param int $action
 * @param int $confirm
 * @param int $id coursetype id
 * @param string $name
 * @return int .
 */
public static function delete_coursetype($action, $id, $confirm, $name)
{
    global $DB;
    try {
        if ($confirm) {
            $DB->delete_records('local_course_types', array('id'=>$id));
            $courses = $DB->get_records('course', array('open_identifiedas' => $id));
            if(!empty($courses)){
                foreach ($courses as $course) {
                    // Set Course type value to default for course
                    $toupdate = new stdClass();
                    $toupdate->id = $course->id;
                    $toupdate->open_identifiedas = 0;
                    $DB->update_record('course', $toupdate);
                }
            }
            $return = true;
        } else {
            $return = false;
        }
    } catch (dml_exception $ex) {
        print_error('deleteerror', 'local_courses');
        $return = false;
    }
    return $return;
}

// local/courses/classes/external.php
/**
 * Returns description of method result value
 * @return external_description
 */

public static function delete_coursetype_returns()
{
    return new external_value(PARAM_BOOL, 'return');
}


// local/courses/classes/external.php
/** Describes the parameters for coursetype_status webservice.
 * @return external_function_parameters
 */
public static function coursetype_update_status_parameters(){
    return new external_function_parameters(
         array(
            'confirm' => new external_value(PARAM_BOOL, 'Confirm', false),
            'contextid' => new external_value(PARAM_INT, 'The context id for course type'),
            'coursetypeid' => new external_value(PARAM_INT, 'The course type id'),
            'status' => new external_value(PARAM_RAW, 'The status of course type'),
            'name' => new external_value(PARAM_RAW, 'Course type name'),
        )
    );
}


// local/courses/classes/external.php
  /**
 * Changes the status of course type
 *
 * @param int $action
 * @param int $confirm
 * @param int $id coursetype id
 * @param string $name
*/
public static function coursetype_update_status($confirm, $contextid, $coursetypeid, $status, $name){
    global $DB,$USER;

    /* $params = self::validate_parameters(self::coursetype_update_status_parameters(),
                                ['contextid' => $contextid,'id' => $coursetypeid, 'status' => $status ,'name' => $name]);
     */$categorycontext = \context_system::instance();
    // We always must call validate_context in a webservice.
    self::validate_context($categorycontext);
    $coursetype = $DB->get_record('local_course_types', array('id' => $coursetypeid), 'id, active');
    $coursetype->active = $coursetype->active ? 0 : 1;
    $coursetype->timemodified = time();
    $return = $DB->update_record('local_course_types', $coursetype);

    return $return;
}

// local/courses/classes/external.php
public static function coursetype_update_status_returns(){
    return new external_value(PARAM_BOOL, 'Status');
}


// local/courses/filterclass.php
public function get_category_list($costcenter){
	global $DB, $CFG, $USER;
	$categorycontext = (new \local_courses\lib\accesslib())::get_module_context();
	if (!is_siteadmin() && has_capability('local/costcenter:assign_multiple_departments_manage', $categorycontext)){

	$catid= $DB->get_field('local_costcenter','category',array('shortname'=>'ACD'));
	}elseif(!is_siteadmin() && has_capability('local/courses:enrol', $categorycontext)){
	/** For The Course Manager both the acd and other costcenter categories should come in Dropdown **/
	$cat1= $DB->get_field('local_costcenter','category',array('shortname'=>'ACD'));
	$cat2=$DB->get_field('local_costcenter','category',array('id'=>$costcenter));
	$catid=$cat1.','.$cat2;
	}else{
	$catid=$DB->get_field('local_costcenter','category',array('id'=>$costcenter));
	}
	 
	$sql="SELECT id,name  from {course_categories} where id IN ($catid) or parent IN ($catid)";
	$depts=$DB->get_records_sql_menu($sql);
	if($depts){
		foreach($depts as $key => $dept){
			$departments["$key"] = $dept;
		}
	}
	return $departments;
}


// local/courses/filterclass.php
public function category_list(){
	global $DB, $CFG, $USER;
	$sql="SELECT id,name  from {course_categories} where 1=1";
	$depts=$DB->get_records_sql_menu($sql);
	if($depts){
		foreach($depts as $key => $dept){
	 		$departments["$key"] = $dept;
		}
	}
	return $departments;
}


// local/courses/lib.php
/**
    * function get_listof_categories
    * @todo all courses based  on costcenter / department
    * @param object $stable limit values
    * @param object $filterdata filterdata
    * @return  array courses
*/
function get_listof_categories($stable, $filterdata) {
    global $DB, $CFG, $OUTPUT, $PAGE ,$USER;
    require_once($CFG->dirroot.'/course/lib.php');
    $categorylib = new local_courses\catslib();
    $organizationsparams = array();
    $deptcategoryparams = array();
    $categoryparams = array();
    $filtercategoriesparams= array();
    $table = new html_table();
    $table->id = 'category_tbl';
    $table->head = array('','','','');

	$categorycontext = (new \local_courses\lib\accesslib())::get_module_context();
    
    $countsql = "select count(c.id) ";
    $sql = "SELECT c.id, c.name, c.parent, c.visible, c.coursecount, c.idnumber ";
    $fromsql = "FROM {course_categories} as  c WHERE id > 1 ";
        
    if(!empty($filterdata->parentid)){
        $fromsql .= " AND c.parent = $filterdata->parentid ";
        
    }else{ 
        if(is_siteadmin()) {
            $fromsql .= " AND c.parent =0 ";
        }else{
            $fromsql .= (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.open_path');
        }
    }

   
    if(isset($filterdata->search_query) && trim($filterdata->search_query) != ''){
        $fromsql .= " AND c.name LIKE :search ";
        $searchparams = array('search' => '%'.$filterdata->search_query.'%');
    }else{
        $searchparams = array();
    }
    $ordersql = " ORDER BY c.id DESC ";

    $params = array_merge($searchparams,$organizationsparams, $deptcategoryparams, $categoryparams);

    $allcategories = $DB->get_records_sql($sql.$fromsql.$ordersql, $params, $stable->start, $stable->length);      
    $categoriescount = $DB->count_records_sql($countsql.$fromsql, $params);
    $data = array();
    $totalrecords = count($allcategories);
    $org_categories = $DB->get_records_menu('local_costcenter', array(),'', 'id, category');
    foreach($allcategories as $categories){
        $row = array();
        $result = $categories->name;
        $cate= $categories->id;
        $sql = $DB->get_records_sql("SELECT c.name FROM {course_categories} as  c
                                    WHERE c.parent=$cate");
        $categorynames =  count($sql);
        $categoryidnumber = $categories->idnumber;
        $categorycontext = context_coursecat::instance($categories->id);
        if($categories->visible ==0){
            $count =  $categories->coursecount;
        }

        if($categorynames > 0){
            $linkurl = new moodle_url("/local/custom_category/index.php?id=".$categories->id."");
        }else{
            $linkurl = null;
        }

        $counts =html_writer::link($linkurl, $categorynames, array());

        $count = html_writer::link('javascript:void(0)', $categories->coursecount, array('title' => '', 'alt' => '', 'class'=>'createcoursemodal course_count_popup', 'onclick' =>'(function(e){ require("local_courses/newcategory").courselist({contextid:'.$categorycontext->id.', categoryname: "'.$categories->name.'", categoryid: "' . $categories->id . '" }) })(event)'));

        $actions = '';
        if(has_capability('local/courses:manage',$categorycontext)){
            $actions = true;
            if(!empty($categories->visible)){
                $visible_value = 0;
                $show = true;
            }else{
                $visible_value = 1;
                $show =  false;
            }
        }
        if($result  != ''){
            $parentname_str = strlen($result) > 20 ? substr($result, 0, 20)."..." : $result;

        }else{
            $parentname_str = 'N/A';
        }

        if(!empty($categories->visible)) {
            $line['parentname_str'] = $parentname_str;
            $line['result'] = $result;
        } else {
            $line['parentname_str'] = $parentname_str;
            $line['result'] = $result;
        }
        if($categoryidnumber != ''){
        $categoryidnumber_idnumber = strlen($categoryidnumber) > 13 ? substr($categoryidnumber, 0, 13)."..." : $categoryidnumber;

        }else{
            $categoryidnumber_idnumber = 'N/A';
        }
        if(!empty($categories->visible)) {
            $line['categoryidnumber_idnumber'] = $categoryidnumber_idnumber;
            $line['categoryidnumber'] = $categoryidnumber;
        } else {
            $line['categoryidnumber_idnumber'] = $categoryidnumber_idnumber;
            $line['categoryidnumber'] = $categoryidnumber;
        }

        if(!empty($categories->visible)){
            $line['catcount'] = $count;
        }else {
            $line['catcount'] = $count;
        }

        if(!empty($categories->visible)) {
            $line['categoryname_str'] = $counts;
        } else {
            $line['categoryname_str'] = $counts;
        }
        $catdepth = $DB->get_field('course_categories','depth',array('id'=>$filterdata->parentid));
        if($catdepth < 2){
            $depth = true;
        }else{
             $depth = false;
        }
        $line['showsubcategory'] =  $depth;

        $catimage = $OUTPUT->image_url('catlist', 'local_courses');
        if(is_object($catimage)){
            $line['catlisticon'] = $catimage->out_as_local_url();
        }else{
            $line['catlisticon'] = $catimage;
        }
        $line['catgoryid'] = $categories->id;
        $line['actions'] = $actions;
        $line['contextid'] = $categorycontext->id;
        $line['show'] = $show;
        $line['visible_value'] = $visible_value;
        $line['sesskey'] = sesskey();

        $coursesexists = $DB->record_exists('course', array('category'=>$categories->id));
        $subcatexists = $DB->record_exists('course_categories', array('parent'=>$categories->id));

        if(in_array($categories->id, $org_categories)){
            $line['delete_enable'] = FALSE;
            $line['unabletodelete_reason'] = get_string('reason_linkedtocostcenter','local_courses');
        }elseif($subcatexists){
            $line['delete_enable'] = FALSE;
            $line['unabletodelete_reason'] = get_string('reason_subcategoriesexists','local_courses');
        }elseif($coursesexists){
            $line['delete_enable'] = FALSE;
            $line['unabletodelete_reason'] = get_string('reason_coursesexists','local_courses');
        }else{
            $line['delete_enable'] = TRUE;
        }
        $data[] = $line;
    }
    return array('totalrecords' => $categoriescount,'records' => $data);
}


// local/courses/lib.php
function get_enrolledusers($courseid){
    global $DB, $USER, $OUTPUT, $CFG;

    $sql = "SELECT ue.id, u.id as userid, u.firstname, u.lastname, u.email, u.open_employeeid, 
            cc.timecompleted
            FROM {course} c
            JOIN {course_categories} cat ON cat.id = c.category
            JOIN {enrol} e ON e.courseid = c.id AND 
                        (e.enrol = 'manual' OR e.enrol = 'self') 
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {user} u ON u.id = ue.userid AND u.deleted = 0
            JOIN {local_costcenter} lc ON lc.id = u.open_path
            JOIN {role_assignments} as ra ON ra.userid = u.id
            JOIN {context} AS cxt ON cxt.id=ra.contextid AND cxt.contextlevel = 50 AND cxt.instanceid=c.id
            JOIN {role} as r ON r.id = ra.roleid AND r.shortname = 'employee'
            LEFT JOIN {course_completions} as cc ON cc.course = c.id AND u.id = cc.userid 
            WHERE c.id = :courseid ";
    $params = array();
    $params['courseid'] = $courseid;

	$categorycontext = (new \local_courses\lib\accesslib())::get_module_context($courseid);
    
    if(!is_siteadmin()){

      $sql .= (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.open_path');

    }

    $courseusers = $DB->get_records_sql($sql , $params);
    $userslist = array();
    if($courseusers){
        $userslist['usersexists'] = true;
        $certificateid = $DB->get_field('course', 'open_certificateid', array('id'=>$courseid));
        if($certificateid){
            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$courseid->id,'userid'=>$enroluser->userid,'moduletype'=>'course'));
            $userslist['certid'] = $certid;
        }else{
            $userslist['certid'] = null;
        }
        $userslist['courseid'] = $courseid;
        $userslist['configpath'] = $CFG->wwwroot;
        foreach ($courseusers as $enroluser) {
            $userinfo = array();
            $userinfo['userid'] = $enroluser->userid;
            $userinfo['employeename'] = $enroluser->firstname.' '.$enroluser->lastname;
            $userinfo['employeeid'] = $enroluser->open_employeeid;
            $userinfo['email'] = $enroluser->email;
            if($enroluser->timecompleted){
                $userinfo['completiondate'] = \local_costcenter\lib::get_userdate('d/m/Y H:i a', $enroluser->timecompleted);
            }else{
                $userinfo['completiondate'] = null;
            }
            $userslist['userdata'][] = $userinfo;
        }
    }else{
        $userslist['usersexists'] = false;
    }
    echo $OUTPUT->render_from_template('local_courses/enrolledusersview', $userslist);

}


// local/courses/lib.php
/**
 * Serve the new course form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_courses_output_fragment_custom_courseevidence_form($args){
    global $DB,$CFG,$PAGE;
    $args = (object) $args;
    $o = '';

    $params = array(
        'courseid' => $args->courseid,
        'userid' => $args->userid,
    );
    $formdata = [];

    if (!empty($args->jsonformdata)) {

        $serialiseddata = json_decode($args->jsonformdata);
        if(is_object($serialiseddata)){
            $serialiseddata = serialize($serialiseddata);
        }
        parse_str($serialiseddata, $formdata);
    }

    $mform = new custom_courseevidence_form(null, $params, 'post', '', null, true,$formdata);
   
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}


// local/courses/lib.php
function local_courses_output_fragment_course_type($args) {
    global $CFG, $DB;

    $args = (object) $args;
    $context = $args->context;
    $coursetypeid = $args->coursetypeid;
    $o = '';
    $formdata = [];

    $o = '';
    if (!empty($args->jsonformdata)) {

        $serialiseddata = json_decode($args->jsonformdata);
        if(is_object($serialiseddata)){
            $serialiseddata = serialize($serialiseddata);
        }
        parse_str($serialiseddata, $formdata);
    }

    if (!empty($coursetypeid)) {
        $data = $DB->get_record('local_course_types', array('id'=>$coursetypeid));
        $formdata = new stdClass();
        $formdata->id = $data->id;
        $costcenterdata = $DB->get_record('local_costcenter', array('id'=>$data->orgid));
        $formdata->name = $data->name;
        $formdata->shortname = $data->shortname;
        $formdata->open_costcenterid=$data->orgid;
        $formdata->orgname = $costcenterdata->fullname;

    }

    $params = array(
        'orgid' => $formdata->open_costcenterid,
        'id' => $coursetypeid,
        'name' => $formdata->name,
        'shortname' => $formdata->shortname,
        'orgname' =>$formdata->orgname,
        'contextid' => $context
    );
    $mform = new local_courses\form\coursetype_form(null, $params, 'post', '', null, true, (array)$formdata);
    $mform->set_data($formdata);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}


// local/courses/lib.php
/**
    * function get_listof_coursetypes
    * @return  array coursetypes
*/
function get_listof_coursetypes($stable, $filterdata) {
    global $DB, $CFG, $OUTPUT, $PAGE ,$USER;

    $categorycontext = (new \local_courses\lib\accesslib())::get_module_context();
    $allcoursetypes=$DB->get_records('local_course_types');
    $coursesContext = array(
        "result" => $allcoursetypes );

    return $coursesContext;
}

// local/courses/renderer.php
/**
 * Display the avialable categories list
 *
 * @return string The text to render
 */
public function get_categories_list($filter = false, $view_type = 'card')
{
    $id = optional_param('id', 0, PARAM_INT);
    $categorycontext = (new \local_courses\lib\accesslib())::get_module_context();

    // change the display according to moodle 3.6
    // $stable = new stdClass();
    // $stable->thead = true;
    // $stable->start = 0;
    // $stable->length = -1;
    // $stable->search = '';
    // $stable->pagetype ='page';

    $templateName = 'local_courses/categorylist';
    $cardClass = 'col-md-3 col-sm-6';
    $perpage = 10;
    if ($view_type == 'table') {
        $templateName = 'local_courses/categorylist_catalog_table';
        $cardClass = 'tableformat';
        $perpage = 20;
    }
    $options = array('targetID' => 'manage_categories', 'perPage' => $perpage, 'cardClass' => $cardClass, 'viewType' => $view_type);
    $options['methodName'] = 'local_courses_categories_view';
    $options['templateName'] = $templateName;
    $options['parentid'] = $id;
    $options = json_encode($options);
    $filterdata = json_encode(array());
    $dataoptions = json_encode(array('contextid' => $categorycontext->id));
    $context = [
        'targetID' => 'manage_categories',
        'options' => $options,
        'dataoptions' => $dataoptions,
        'filterdata' => $filterdata
    ];
    if ($filter) {
        return  $context;
    } else {
        return  $this->render_from_template('local_costcenter/cardPaginate', $context);
    }
}


// local/courses/renderer.php
/**
 * Render the courseevidenceview
 * @param  courseevidenceview $widget
 * @return bool|string
 * @throws moodle_exception
 */
protected function render_courseevidenceview(\local_courses\output\courseevidenceview $page)
{
    $data = $page->export_for_template($this);
    return parent::render_from_template('local_courses/courseevidence', $data);
}


// local/courses/classes/external.php
/**
 * Describes the parameters for departmentlist webservice.
 * @return external_function_parameters
 */
public static function departmentlist_parameters() {
    return new external_function_parameters(
        array(
            'orgid' => new external_value(PARAM_INT, 'The id for the costcenter / organization'),
            'depid' => new external_value(PARAM_RAW, 'The id for the department'),
            'subdepid' => new external_value(PARAM_RAW, 'The id for the sub department'),
            'flag' => new external_value(PARAM_INT, 'falg'),
        )
    );
}


// local/courses/classes/external.php
/**
 * departments list
 *
 * @param int $orgid id for the organization.
 * @param int $depid id for the organization.
 * @param int $flag id for the organization.
 * @return array
 */
public static function departmentlist($orgid, $depid, $flag) {
    global $DB, $CFG, $USER;
    require_once($CFG->dirroot.'/local/courses/lib.php');
    $subdepartmentlist = [];
    if (!empty($depid) && $flag) {
        $sql  = "SELECT category FROM {local_costcenter} WHERE  id = ?";
        $costcentercategory = $DB->get_field_sql($sql, array($depid));
        if ($costcentercategory)
           $allcategories = $DB->get_records_sql_menu("SELECT id,name from {course_categories} where (path like '%/$costcentercategory/%' OR id =$costcentercategory) AND visible=1");
       $departmentlist = array();
       $certlist = array();
       $levelslist = array();
    } else if (!empty($orgid)) {
        $sql  = "SELECT id,fullname FROM {local_costcenter} WHERE parentid IN ($orgid) ORDER BY id DESC";
        $departmentlist = $DB->get_records_sql_menu($sql);
//            mallikarjun added to get tool certificate values 
        $sqlc  = "SELECT id,name FROM {tool_certificate_templates} WHERE costcenter = $orgid ORDER BY name ASC";
        $certlist = $DB->get_records_sql_menu($sqlc);

        $sql  = "SELECT id,name FROM {local_course_levels} WHERE costcenterid = $orgid ORDER BY sortorder ASC";
        $levelslist = $DB->get_records_sql_menu($sql);

        $categorylib = new local_courses\catslib();
        $categorycontext = (new \local_courses\lib\accesslib())::get_module_context();
        

        $orgcategories = $categorylib->get_categories();
        $orgcategoryids = implode(',',$orgcategories);
        $sql = "SELECT c.id,c.name FROM {course_categories} as c WHERE c.visible = 1 AND c.id IN ($orgcategoryids)";

        $sql .= " ORDER BY c.id DESC";
        $allcategories = $DB->get_records_sql_menu($sql);

    } else if($flag){

        if(is_siteadmin()){

            $allcategories = $DB->get_records_sql_menu("select id,name from {course_categories} where visible=1");

        }else{

            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='lc.path',$costcenterpath=null,$datatype='lowerandsamepath');

            $costcentersql = "SELECT lc.id,lc.name
                            FROM {local_costcenter} AS lc
                            WHERE lc.visible=1 $costcenterpathconcatsql ";

            $allcategories = $DB->get_records_sql_menu($costcentersql);
        }



        $departmentlist = array();
        $levelslist = array();
    }
    $return = array(
        'departments' => json_encode($departmentlist),
        'departments' => json_encode($subdepartmentlist),
        'categories' => json_encode($allcategories),
        'levels' => json_encode($levelslist),
        'certificates' => json_encode($certlist)
        );
    return $return;
}


// local/courses/classes/external.php
/**
* Returns description of method result value
*
* @return external_description
*/
public static function departmentlist_returns() {
    return new external_function_parameters(
        array(
            'departments' => new external_value(PARAM_RAW, 'Department and categorylist '),
            'subdepartments' => new external_value(PARAM_RAW, 'sub Department and categorylist '),
            'categories' => new external_value(PARAM_RAW, 'Department and categorylist '),
            'levels' => new external_value(PARAM_RAW, 'LevelL and categorylist '),
            'certificates' => new external_value(PARAM_RAW, 'Certificates list ')
        )
    );
}


// local/courses/lib.php
/**
* todo displays the categories
* @param string $requiredcapability
* @param int $excludeid
* @param string $separator
* @param int $departmentcat
* @param int $orgcat
* @param array $args List of named arguments for the fragment loader.
* @return string
*/
function categorylist($requiredcapability = '', $excludeid = 0, $separator = ' / ',$departmentcat = 0,$orgcat=0) {
    global $DB, $USER;
    $coursecatcache = cache::make('core', 'coursecat');

    // Check if we cached the complete list of user-accessible category names ($baselist) or list of ids
    // with requried cap ($thislist).
    $currentlang = current_language();
    $basecachekey = $currentlang . '_catlist';
    $baselist = $coursecatcache->get($basecachekey);
    $thislist = false;
    $thiscachekey = null;
    if (!empty($requiredcapability)) {
        $requiredcapability = (array)$requiredcapability;
        $thiscachekey = 'catlist:'. serialize($requiredcapability);
        if ($baselist !== false && ($thislist = $coursecatcache->get($thiscachekey)) !== false) {
            $thislist = preg_split('|,|', $thislist, -1, PREG_SPLIT_NO_EMPTY);
        }
    } else if ($baselist !== false) {
        $thislist = array_keys($baselist);
    }

    if ($baselist === false) {
        // We don't have $baselist cached, retrieve it. Retrieve $thislist again in any case.
        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT cc.id, cc.sortorder, cc.name, cc.visible, cc.parent, cc.path, $ctxselect
                FROM {course_categories} cc
                JOIN {context} ctx ON cc.id = ctx.instanceid AND ctx.contextlevel = :contextcoursecat AND cc.visible = :value
                WHERE cc.depth<=2
                ORDER BY cc.sortorder";
        $rs = $DB->get_recordset_sql($sql, array('contextcoursecat' => CONTEXT_COURSECAT,'value' => 1));
        $baselist = array();
        $thislist = array();
        foreach ($rs as $record) {
            // If the category's parent is not visible to the user, it is not visible as well.
            if (!$record->parent || isset($baselist[$record->parent])) {
                context_helper::preload_from_record($record);
                $context = context_coursecat::instance($record->id);
                if (!$record->visible && !has_capability('moodle/category:viewhiddencategories', $context)) {
                    // No cap to view category, added to neither $baselist nor $thislist.
                    continue;
                }
                $baselist[$record->id] = array(
                    'name' => format_string($record->name, true, array('context' => $context)),
                    'path' => $record->path,
                );
                if (!empty($requiredcapability) && !has_all_capabilities($requiredcapability, $context)) {
                    // No required capability, added to $baselist but not to $thislist.
                    continue;
                }
                $thislist[] = $record->id;
            }
        }
        $rs->close();
        $coursecatcache->set($basecachekey, $baselist);
        if (!empty($requiredcapability)) {
            $coursecatcache->set($thiscachekey, join(',', $thislist));
        }
    } else if ($thislist === false) {
        // We have $baselist cached but not $thislist. Simplier query is used to retrieve.
        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
        $sql = "SELECT ctx.instanceid AS id, $ctxselect
                FROM {context} ctx WHERE ctx.contextlevel = :contextcoursecat ";
        $contexts = $DB->get_records_sql($sql, array('contextcoursecat' => CONTEXT_COURSECAT));
        $thislist = array();
        foreach (array_keys($baselist) as $id) {
            context_helper::preload_from_record($contexts[$id]);
            if (has_all_capabilities($requiredcapability, context_coursecat::instance($id))) {
                $thislist[] = $id;
            }
        }
        $coursecatcache->set($thiscachekey, join(',', $thislist));
    }

    // Now build the array of strings to return, mind $separator and $excludeid.
    $names = array();
    foreach ($thislist as $id) {

        $path = preg_split('|/|', $baselist[$id]['path'], -1, PREG_SPLIT_NO_EMPTY);
        if($departmentcat){
            if($path[1] == $departmentcat){
                if (!$excludeid || !in_array($excludeid, $path)) {
                    $namechunks = array();
                    foreach ($path as $parentid) {
                        $namechunks[] = $baselist[$parentid]['name'];
                    }
                    $names[$id] = join($separator, $namechunks);
                }
            }
        }else if($orgcat){
            if($path[0] == $orgcat){
                if (!$excludeid || !in_array($excludeid, $path)) {
                    $namechunks = array();
                    foreach ($path as $parentid) {
                        $namechunks[] = $baselist[$parentid]['name'];
                    }
                    $names[$id] = join($separator, $namechunks);
                }
            }
        }
        else{
                if (!$excludeid || !in_array($excludeid, $path)) {
                    $namechunks = array();
                    foreach ($path as $parentid) {
                        $namechunks[] = $baselist[$parentid]['name'];
                    }
                    $names[$id] = join($separator, $namechunks);
                }
        }
    }
    return $names;
}


// local/courses/lib.php
/**
 * Serve the delete category form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_courses_output_fragment_deletecategory_form($args){
 global $DB,$CFG,$PAGE;
   // require_once($CFG->libdir.'/coursecatlib.php');
    require_once($CFG->libdir . '/questionlib.php');

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {

        $serialiseddata = json_decode($args->jsonformdata);
        if(is_object($serialiseddata)){
            $serialiseddata = serialize($serialiseddata);
        }
        parse_str($serialiseddata, $formdata);
    }

    if ($categoryid) {
        $category = core_course_category::get($categoryid);
        $context = context_coursecat::instance($category->id);
    }else {
        $category = core_course_category::get_default();
        $categoryid = $category->id;
        $context = context_coursecat::instance($category->id);
    }

    $mform = new local_courses\form\deletecategory_form(null, $category, 'post', '', null, true, $formdata);
    // Used to set the courseid.

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}


// local/courses/lib.php
/**
 * Serve the new course category form as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_courses_output_fragment_coursecategory_form($args){
 global $DB,$CFG,$PAGE;
   // require_once($CFG->libdir.'/coursecatlib.php');

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;

    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {

        $serialiseddata = json_decode($args->jsonformdata);
        if(is_object($serialiseddata)){
            $serialiseddata = serialize($serialiseddata);
        }
        parse_str($serialiseddata, $formdata);
    }
    if ($categoryid > 0) {
        // $heading = get_string('updatecourse', 'local_courses');
        // $collapse = false;
        $data = $DB->get_record('course_categories', array('id'=>$categoryid));
        $formdata = new stdClass();
        $formdata->id = $data->id;
        $formdata->parent = $data->parent;
        $formdata->name = $data->name;
        $formdata->idnumber = $data->idnumber;
        $formdata->description_editor['text'] = $data->description;
    }

    if($categoryid){
        $coursecat = core_course_category::get($categoryid, MUST_EXIST, true);
        $category = $coursecat->get_db_record();
        $context = context_coursecat::instance($categoryid);

         $itemid = 0;
    }else{
        $parent = optional_param('parent', 0, PARAM_INT);

        if ($parent) {
            $DB->record_exists('course_categories', array('id' => $parent), '*', MUST_EXIST);
            $context = context_coursecat::instance($parent);
        } else {
            $context = context_system::instance();
        }
        $category = new stdClass();
        $category->id = 0;
        $category->parent = $parent;
    }

    $params = array(
    'categoryid' => $categoryid,
    'parent' => $category->parent,
    'context' => $context,
    'itemid' => $itemid
    );

    $mform = new local_courses\form\coursecategory_form(null, $params, 'post', '', null, true, $formdata);
    // Used to set the courseid.
    $mform->set_data($formdata);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}


// local/courses/lib.php
/**
 * Serve the table for course categories
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string 
 */
function local_courses_output_fragment_coursecategory_display($args){
    global $DB,$CFG,$PAGE,$OUTPUT;

    $args = (object) $args;
    $context = $args->context;
    $categoryid = $args->categoryid;

    $formdata = [];
    if (!empty($args->jsonformdata)) {

        $serialiseddata = json_decode($args->jsonformdata);
        if(is_object($serialiseddata)){
            $serialiseddata = serialize($serialiseddata);
        }
        parse_str($serialiseddata, $formdata);
    }

    $table = new html_table();
    $table->id = 'popup_category';
    $table->align = ['left','center','center','center','center'];
    $table->head = array(get_string('course_name', 'local_courses'),get_string('enrolledusers', 'local_courses'),get_string('completed_users', 'local_courses'),get_string('type', 'local_courses'),get_string('actions', 'local_courses'));
    $courses = $DB->get_records_sql("SELECT c.id,c.category,c.fullname FROM {course} c WHERE c.id > 1
                                     AND c.category = ?", [$categoryid]);
    if($courses){
    $data=array();
    foreach($courses as $course){
        $row = array();
        $row[] = html_writer::link(new moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname);
        $course_sql = "SELECT count(ue.userid) as enrolled,count(cc.course) as completed
                            FROM {user_enrolments} as ue
                            JOIN {enrol} as e ON e.id=ue.enrolid
                            JOIN {user} as u ON u.id = ue.userid
                            RIGHT JOIN {course} as c ON c.id =e.courseid
                            LEFT JOIN {course_completions} cc ON cc.course=e.courseid and ue.userid=cc.userid and cc.timecompleted IS NOT NULL
                            WHERE c.id = ? AND u.deleted = 0 AND u.suspended = 0 
                                group by e.courseid";
        $course_stats = $DB->get_record_sql($course_sql, [$course->id]);
       if($course_stats->enrolled){
            $row[] = $course_stats->enrolled;
        }else{
             $row[] = "N/A";
        }
        if($course_stats->completed){
            $row[] = $course_stats->completed;
        }else{
             $row[] = "N/A";
        }




        $enrolid = $DB->get_field('enrol','id', array('courseid'=>$course->id, 'enrol'=>'manual'));

        $enrolicon = html_writer::link(new moodle_url('/local/courses/courseenrol.php',array('id'=>$course->id,'enrolid' => $enrolid)),html_writer::tag('i','',array('class'=>'fa fa-user-plus icon text-muted', 'title' => get_string('enrol','local_courses'), 'alt' => get_string('enrol'))));
        $actions = $enrolicon.' '.$editicon;
        $row[] = $actions;

        $data[] = $row;
    }
    $table->data = $data;
    $output = html_writer::table($table);
    $output .= html_writer::script("$('#popup_category').DataTable({
        'language': {
            paginate: {
            'previous': '<',
            'next': '>'
            }
        },
        'bInfo' : false,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, ".get_string('all')."]
        ]
    });");
    }else{
        $output = get_string('nocourseavailiable', 'local_courses');
    }

    return $output;
}
