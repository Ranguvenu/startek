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
 * @package   local
 * @subpackage  challenge
 * @author eabyas  <info@eabyas.in>
**/
class local_challenge_external extends \external_api{
	public static function post_challenge_parameters(){
		return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the challenge', false),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data challenge, encoded as a json array', false)
            )
        );
	}
	public static function post_challenge($contextid, $jsonformdata){
		global $PAGE, $CFG;
        // We always must pass webservice params through validate_parameters.
		$params = self::validate_parameters(
            self::post_challenge_parameters(),
            [
                'contextid' => $contextid,
                'jsonformdata' => $jsonformdata
            ]
        );

        // We always must call validate_context in a webservice.
		$context = context::instance_by_id($contextid, MUST_EXIST);

		self::validate_context($context);

		$serialiseddata = json_decode($jsonformdata);

		$data = array();
        parse_str($serialiseddata, $data);
        $warnings = array();

        $mform = new \local_challenge\form\challenge_form(null, (array)$data, 'post', '', null, true, (array)$data);

        $validateddata = $mform->get_data();
        // print_object($validateddata);
        if($validateddata){
        	$lib = new \local_challenge\local\lib();
        	$return = $lib->create_challenge($validateddata);
        }else{
        	throw new moodle_exception('Error in creation');
        }
        return $return;
	}
	public static function post_challenge_returns(){
		return new external_value(PARAM_BOOL, 'return');
	}
	public static function form_option_selector_parameters(){
        return new external_function_parameters(array(
            'query' => new external_value(PARAM_RAW, 'Query string'),
            'context' => self::get_context_parameters(),
            'action' => new external_value(PARAM_RAW, 'Action for the costcenter form selector'),
            'options' => new external_value(PARAM_RAW, 'Action for the kpichallenge form selector'),
            'searchanywhere' => new external_value(PARAM_BOOL, 'find a match anywhere, or only at the beginning'),
            'page' => new external_value(PARAM_INT, 'Page number'),
            'perpage' => new external_value(PARAM_INT, 'Number per page'),
        ));
	}
	public static function form_option_selector($query, $context, $action, $options, $searchanywhere, $page, $perpage){
		global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::form_option_selector_parameters(), array(
            'query' => $query,
            'context' => $context,
            'action' => $action,
            'options' => $options,
            'searchanywhere' => $searchanywhere,
            'page' => $page,
            'perpage' => $perpage
        ));
        $query = $params['query'];
        $action = $params['action'];
        $context = self::get_context_from_params($params['context']);
        $options = json_decode($params['options']);

        $searchanywhere=$params['searchanywhere'];
        $page=$params['page'];
        $perpage=$params['perpage'];

        if (!empty($options)) {
            $formoptions = json_decode($options);
        }
        self::validate_context($context);
        $allobject = new \stdClass();
        $allobject->id = 0;
        $allobject->fullname = 'All';
        $allobjectarr = array(0 => $allobject);
        if ($action) {
            $return = array();
            switch($action) {
                case 'challenge_user_selector':
                    $fields = array("u.firstname", "u.lastname");
                    $sqlparams['parentid'] = 0;
                    $likesql = array();
                    $i = 0;
                    foreach ($fields as $field) {
                        $i++;
                        $likesql[] = $DB->sql_like($field, ":queryparam$i", false);
                        $sqlparams["queryparam$i"] = "%$query%";
                    }
                    if(!empty($likesql)){
                        $sqlfields = " AND ( ".implode(" OR ", $likesql)." ) ";
                    }else{
                        $sqlfields = " ";
                    }

                    // $concatsql .= " ($sqlfields) ";//AND 
                    $conditional_sql = '';
                    switch($options->module_type){
                    	case "local_courses":
                    		$moduleobj = get_course($options->module_id);
                    		$dependencysql = " OR u.id IN (SELECT ue.userid 
                    			FROM {user_enrolments} AS ue 
                    			JOIN {enrol} AS e ON e.id = ue.enrolid AND e.enrol IN ('manual', 'self')
                    			LEFT JOIN {course_completions} AS cc ON cc.course=e.courseid AND cc.userid = ue.userid AND cc.timecompleted IS NOT NULL
                    			WHERE e.courseid = :moduleid ) ";
                			$sqlparams['moduleid'] = $options->module_id;
                			// $conditional_sql .= " AND ";
                		break;
                		// case "local_classroom":
                		// 	$sql = "SELECT id, costcenter AS open_costcenterid, 
                		// 	department AS open_departmentid, 
                		// 	subdepartment AS open_subdepartment, open_group, 
                		// 	open_hrmsrole, open_designation, open_location, 
                		// 	approvalreqd, 1 as selfenrol 
                		// 	FROM {local_classroom} WHERE id = :id ";
                		// 	$moduleobj = $this->db->get_record_sql($sql, array('id' => $options['module_id']));
                		// break;
                		// case "local_certification":
                		// 	$sql = "SELECT id, costcenter AS open_costcenterid, 
                		// 	department AS open_departmentid, 
                		// 	subdepartment AS open_subdepartment, open_group, 
                		// 	open_hrmsrole, open_designation, open_location, 
                		// 	approvalreqd, 1 as selfenrol 
                		// 	FROM {local_certification} WHERE id = :id ";
                		// 	$moduleobj = $this->db->get_record_sql($sql, array('id' => $options['module_id']));
                		// break;
                		case "local_learningplan":
                			$sql = "SELECT id, costcenter AS open_costcenterid, 
                			department AS open_departmentid, 
                			subdepartment AS open_subdepartment, open_group, 
                			open_hrmsrole, open_designation, open_location, 
                			approvalreqd, 1 as selfenrol 
                			FROM {local_learningplan} WHERE id = :id ";
                			$moduleobj = $DB->get_record_sql($sql, array('id' => $options->module_id));
                			$dependencysql = " OR u.id IN (SELECT userid FROM {local_learningplan_user} WHERE planid = :moduleid AND completiondate IS NULL )";
                			$sqlparams['moduleid'] = $options->module_id;

                            $exclude_sql = " AND u.id NOT IN (SELECT userid_to FROM {local_challenge} WHERE userid_from = :this_userid AND module_id = :this_moduleid AND module_type LIKE :this_moduletype) ";
                            $sqlparams['this_userid'] = $USER->id;
                            $sqlparams['this_moduleid'] = $options->module_id;
                            $sqlparams['this_moduletype'] = 'local_courses';
                		break;
                		case "local_program":
                			$sql = "SELECT id, costcenter AS open_costcenterid, 
                			department AS open_departmentid, 
                			subdepartment AS open_subdepartment, open_group, 
                			open_hrmsrole, open_designation, open_location, 
                			approvalreqd, selfenrol 
                			FROM {local_program} WHERE id = :id ";
                			$moduleobj = $DB->get_record_sql($sql, array('id' => $options->module_id));
                			$dependencysql = " OR u.id IN (SELECT userid FROM {local_program_users} WHERE programid = :moduleid AND completiondate > 0 )";
                			$sqlparams['moduleid'] = $options->module_id;
                		break;
                    }
                    $concatsql = ' 1=1 ';
                    if(isset($moduleobj->open_costcenterid)){
                    	$concatsql .= " AND u.open_costcenterid = {$moduleobj->open_costcenterid} ";
                    }
                    if(isset($moduleobj->open_departmentid) && $moduleobj->open_departmentid > 0){
                    	$concatsql .= " AND u.open_departmentid = {$moduleobj->open_departmentid} ";
                    }
                    if(isset($moduleobj->open_subdepartment) && $moduleobj->open_subdepartment > 0){
                    	$concatsql .= " AND u.open_subdepartment = {$moduleobj->open_subdepartment} ";	
                    }
                    if(isset($moduleobj->open_group) && $moduleobj->open_group != ''){
                    	// $concatsql .= " AND CONCAT('%,',u.open_subdepartment,',%') LIKE :subdeptlike ";
                    	// $sqlparams['subdeptlike'] = ','.$moduleobj->open_group.',';
                    	$concatsql .= " AND u.id IN (SELECT userid FROM {cohort_members} WHERE :grouplike LIKE CONCAT('%,',cohortid,',%') )";
                    	$sqlparams['grouplike'] = ','.$moduleobj->open_group.',';
                    }
                    if(isset($moduleobj->open_hrmsrole) && $moduleobj->open_hrmsrole != ''){
                    	$concatsql .= " AND :hrmsrolelike LIKE CONCAT('%,',u.open_hrmsrole,',%') ";
                    	$sqlparams['hrmsrolelike'] = ','.$moduleobj->open_hrmsrole.',';
                    }

                    if(isset($moduleobj->open_designation) && $moduleobj->open_designation != ''){
                    	$concatsql .= " AND :designationlike LIKE CONCAT('%,',u.open_designation,',%') ";
                    	$sqlparams['designationlike'] = ','.$moduleobj->open_designation.',';
                    }
                    if(isset($moduleobj->open_location) && $moduleobj->open_location != ''){
                    	$concatsql .= " AND :locationlike LIKE CONCAT('%,',u.open_location,',%') ";
                    	$sqlparams['locationlike'] = ','.$moduleobj->open_location.',';
                    }
                    if(isset($moduleobj->open_location) && $moduleobj->open_location != ''){
                    	$concatsql .= " AND :locationlike LIKE CONCAT('%,',u.open_location,',%') ";
                    	$sqlparams['locationlike'] = ','.$moduleobj->open_location.',';
                    }

                    $fields      = "SELECT u.id, CONCAT(u.firstname,' ',u.lastname) AS fullname ";
                    $userssql = " FROM {user} AS u
                                     WHERE u.suspended = 0 AND u.deleted = 0 AND u.id > 2 AND u.id <> :currentuserid {$sqlfields} AND ({$concatsql} {$dependencysql} ) AND u.id NOT IN (SELECT userid_to FROM {local_challenge} WHERE userid_from = :this_userid AND module_id = :this_moduleid AND module_type LIKE :this_moduletype) ";
                    $sqlparams['this_userid'] = $USER->id;
                    $sqlparams['currentuserid'] = $USER->id;
                    $sqlparams['this_moduleid'] = $options->module_id;
                    $sqlparams['this_moduletype'] = $options->module_type;
                                     // echo $fields.$userssql;
                                     // print_object($sqlparams);
                    $users = $DB->get_records_sql($fields.$userssql, $sqlparams, ($page * $perpage) -0, $perpage + 1);
                    if ($users) {
                        $totalusers = count($users);
                        $moreusers = $totalusers > $perpage;
            
                        if ($moreusers) {
                            // We need to discard the last record.
                            array_pop($users);
                        }
                    }
                    $return = array_values(json_decode(json_encode(($users)), true));
                break;
            }
        }
        return json_encode($return);
	}
	public static function form_option_selector_returns(){
		return new external_value(PARAM_RAW, 'data');
	}
    public static function challenges_view_parameters(){
        return new external_function_parameters([
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'offset' => new external_value(PARAM_INT, 'Number of items to skip from the begging of the result set',
                VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum number of results to return',
                VALUE_DEFAULT, 0),
            // 'contextid' => new external_value(PARAM_INT, 'contextid'),
            'filterdata' => new external_value(PARAM_RAW, 'filters applied'),
        ]);
    }
    public static function challenges_view($options, $dataoptions, $offset, $limit/*, $contextid*/, $filterdata){
        global $DB, $PAGE;
        require_login();
        $PAGE->set_context($contextid);
        // Parameter validation.
        $params = self::validate_parameters(
            self::challenges_view_parameters(),
            [
                'options' => $options,
                'dataoptions' => $dataoptions,
                'offset' => $offset,
                'limit' => $limit,
                // 'contextid' => $contextid,
                'filterdata' => $filterdata
            ]
        );
        $filtervalues = json_decode($filterdata);
        $challengelib = new \local_challenge\local\lib();
        $args = new stdClass();
        $args->limitfrom = $params['offset'];
        $args->limitto = $params['limit'];
        $args->filterdata = (object) $filtervalues;
        $data = $challengelib->get_listof_challenges($args);
        $totalcount = $data['totalchallenges'];
        return [
            'totalcount' => $totalcount,
            'length' => $totalcount,
            'options' => $options,
            'dataoptions' => $dataoptions,
            'filterdata' => $filterdata,
            'records' =>$data['records'],
        ];
    }
    public static function challenges_view_returns(){
        return new external_single_structure([
            'totalcount' => new external_value(PARAM_INT, 'total number of challenges in result set'),
            'length' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'options' => new external_value(PARAM_RAW, 'The paging data for the service'),
            'dataoptions' => new external_value(PARAM_RAW, 'The data for the service'),
            'filterdata' => new external_value(PARAM_RAW, 'total number of challenges in result set'),
            'records' => new external_multiple_structure(
                new external_single_structure(
                    array(
                    'challengeid' => new external_value(PARAM_INT, 'Challenge id'),
                    'challenge_status' => new external_value(PARAM_TEXT, 'Challenge Status'),
                    'challenge_username' => new external_value(PARAM_TEXT, 'Challenge Username'),
                    'challenge_useremail' => new external_value(PARAM_RAW, 'Challenge Useremail'),
                    'challenge_userimg_url' => new external_value(PARAM_RAW, 'Challenge Userimg Url'),
                    'challenge_timecompleted' => new external_value(PARAM_RAW, 'Challenge T imecompleted'),
                    'modulename' => new external_value(PARAM_RAW, 'Module Name'),
                    'modulestartdate' => new external_value(PARAM_RAW, 'Module Startdate'),
                    'moduleenddate' => new external_value(PARAM_RAW, 'Module Enddate'),
                    'actions' => new external_value(PARAM_RAW, 'Challenge actions'),
                    'challenge_label' => new external_value(PARAM_TEXT, 'Challenge Label'),
                    'status_date_label' => new external_value(PARAM_TEXT, 'Challenge Status Date Label', VALUE_OPTIONAL),
                    'status_change_date' => new external_value(PARAM_TEXT, 'Challenge Status Change Date', VALUE_OPTIONAL),
                    'challenged_date' => new external_value(PARAM_TEXT, 'Challenged Date'),
                    'challenge_completeby' => new external_value(PARAM_TEXT, 'Challenge Complete by Date'),
                    'moduletype' => new external_value(PARAM_TEXT, 'Module type'),
                    'modulelogo_url' => new external_value(PARAM_RAW, 'Module Logo Url'),
                    'customimage_required' => new external_value(PARAM_RAW, 'Flag for customimage'),
                    'componenticonclass' => new external_value(PARAM_RAW, 'componenticonclass'),
                    )
                )
            )
        ]);
    }
    public static function alter_challenge_status_parameters(){
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the challenge', false),
                'challengeid' => new external_value(PARAM_INT, 'The id of challenge', false),
                'newstatus' => new external_value(PARAM_RAW, 'The newstatus of challenge', false)
            )
        );
    }
    public static function alter_challenge_status($contextid, $challengeid, $newstatus){
        $params = self::validate_parameters(
            self::alter_challenge_status_parameters(),
            [
                'contextid' => $contextid,
                'challengeid' => $challengeid,
                'newstatus' => $newstatus
            ]
        );
        $challengelib = new \local_challenge\local\lib();
        return $challengelib->challenge_alter_status($challengeid, $newstatus);
    }
    public static function alter_challenge_status_returns(){
        return new external_value(PARAM_RAW, 'data');
    }

}