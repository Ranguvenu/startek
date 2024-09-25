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
namespace local_challenge\local;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/challenge/lib.php');
class lib{
	public $db;
	public $user;
	public function __construct($user = null){
		global $DB, $USER;
		$this->db = $DB;
		$this->user = is_null($user) ? $USER : $user;
	}
	public function create_challenge($data){
		// $record = new \stdClass();
		$record = clone($data);

		$record->userid_from = $this->user->id;
		// $record->complete_by = $data
		$record->module_id = $data->module_id;
		$record->module_type = $data->module_type;
		$record->module_name = $data->module_name;
		$record->type = $data->type;
		$record->module_startdate = $data->module_startdate;
		$record->module_enddate = $data->module_enddate;
		$record->message = $data->message;
		$record->status = 1;
		$record->timecreated = time();
		foreach($data->userid_to AS $tousers){
			$record->userid_to = $tousers;
			$record->id = $this->db->insert_record('local_challenge', $record);
			$params = array(
                'context' => \context_system::instance(),
                'objectid' => $record->id,
                // 'courseid' => 1,
                'userid' => $this->user->id,
                'relateduserid' => $tousers,
                'other' => array('challenge_area' => $data->module_type,
            		'challenge_moduleid' => $data->module_id)
            );
			$event = \local_challenge\event\new_challenge_posted::create($params);
			$event->add_record_snapshot('local_challenge', $data);
			$event->trigger();
			$notification = new \local_challenge\notification();
			$emaildata = new \stdClass();
			$emaildata->challengedata = $record;
			$emaildata->notificationtype = 'challenge_created';
			$emaildata->touserid = $record->userid_to;
			$emaildata->fromuserid = $this->user->id;
			$notification->send_challenge_email($emaildata);
			// challenge_created _mail
		}
		return true;
	}
	public function get_my_challenged_users($userid, $module_id, $module_type, $type, $limit_from, $limit_to, $search = NULL){
		switch($module_type){
			case 'local_courses':
				$joinsql = " JOIN {course} AS module ON module.id = lc.module_id ";
				$element = " module.fullname ";
			break;
			case 'local_classroom':
			case 'local_certification':
			case 'local_program':
			case 'local_learningplan':
				$joinsql = " JOIN {{$module_type}} AS module ON module.id = lc.module_id ";
				$element = " module.name ";
			break;
		}
		$my_challengees_sql = "SELECT lc.id AS challengeid, lc.message, lc.timecreated, 
			lc.timecompleted, u.*, {$element}
			FROM {local_challenge} AS lc {$joinsql}
			JOIN {user} AS u ON u.id = lc.userid_to 
			WHERE lc.userid_from = :userid AND lc.module_id = :module_id 
			AND lc.module_type LIKE :module_type AND type LIKE :type ";
		$params = array('userid' => $userid, 'module_id' => $module_id, 'module_type' => $module_type, 'type' => $type);
		if(!is_null($search)){
			$my_challengees_sql .= " AND {$element} LIKE :search1 OR CONCAT(u.firstname,' ',u.lastname) LIKE :search2 ";
			$params['search1'] = $params['search2'] = $search;
		}
		$my_challengees = $this->db->get_records_sql($my_challengees_sql, $params, $limit_from, $limit_to);
		return $my_challengees;
	}
	public function get_listof_challenges($args){
		global $PAGE;
		$renderer = $PAGE->get_renderer('local_challenge');
		if($args->filterdata->data_type == 'challenger_tab_content'){
			$fieldname = 'userid_to';
			$join_on = 'userid_from';
			$challenge_label = get_string('challenger', 'local_challenge');
			$actions = True;
		}else{
			$fieldname = 'userid_from';
			$join_on = 'userid_to';
			$challenge_label = get_string('challengee', 'local_challenge');
			$actions = False;
		}
		$type = $args->filterdata->type == 'challenge' ? 'challenge' : 'share';
		$userfields = \user_picture::fields('u');

		$select_sql = "SELECT lc.id as challengeid, lc.module_type, lc.module_id, 
			lc.userid_to, lc.userid_from, lc.timecreated, lc.timemodified, lc.timecompleted, 
			lc.status, lc.complete_by, {$userfields} "; 
		$challenge_sql = " FROM {local_challenge} AS lc 
			JOIN {user} AS u ON u.id = lc.{$join_on}
			WHERE lc.{$fieldname} = :userid AND lc.type = :type ";
		$params = array('userid' => $this->user->id, 'type' => $type);
		if(isset($args->filterdata->searchval) && $args->filterdata->searchval != ''){
			$challenge_sql .= " AND concat(u.firstname,' ',u.lastname) LIKE :usernamelike ";
			$params['usernamelike'] = '%'.$args->filterdata->searchval.'%';
		}
		$order_sql = " ORDER BY lc.id DESC ";
		$challenges = $this->db->get_records_sql($select_sql.$challenge_sql.$order_sql, $params, $args->limitfrom, $args->limitto);

		$countsql = "SELECT count(lc.id) ";
		$totalchallenges = $this->db->count_records_sql($countsql.$challenge_sql, $params); 
		// $totalchallenges = $this->db->count_records('local_challenge', array($fieldname => $this->user->id));
		$records = array();
		foreach($challenges AS $challenge){
			$data = array();
			$data['challengeid'] = $challenge->challengeid;
			$data['challenge_status'] = local_challenge_get_challenge_status($challenge->status);
			$data['challenge_username'] = fullname($challenge);
			$data['challenge_useremail'] = $challenge->email;
			$data['challenge_timecompleted'] = $challenge->timecompleted ? \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timecompleted) : 'N/A';
			$user_picture = new \user_picture($challenge, array('size' => 60, 'class' => 'userpic', 'link'=>false));
        	$user_picture = $user_picture->get_url($PAGE);
			$data['challenge_userimg_url'] = $user_picture->out();
			$data['actions'] = $renderer->get_challenge_actions($challenge->challengeid, $actions);
			$data['challenge_label'] = $challenge_label;
			$data['challenged_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timecreated);
			$data['challenge_completeby'] = $challenge->complete_by ? \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->complete_by) : 'N/A';
			$data['moduletype'] = get_string('module_'.$challenge->module_type, 'local_challenge');


			switch($challenge->status){
				case CHALLENGE_NEW :
					// $data['status_date_label'] = get_string('challenged_on', 'local_challenge');
					// $data['status_change_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timecreated);
				break;
				case CHALLENGE_ACTIVE :
					$data['status_date_label'] = get_string('challengeaccepted_on', 'local_challenge');
					$data['status_change_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timemodified);
				break;
				case CHALLENGE_DECLINED :
					$data['status_date_label'] = get_string('challengedeclined_on', 'local_challenge');
					$data['status_change_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timemodified);
				break;
				case CHALLENGE_COMPLETED :
					$data['status_date_label'] = get_string('challengecompleted_on', 'local_challenge');
					$data['status_change_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timecompleted);
				break;
				case CHALLENGE_INCOMPLETE : 
					$data['status_date_label'] = get_string('challengeincomplete_on', 'local_challenge');
					$data['status_change_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timemodified);
				break;
				case CHALLENGE_EXPIRED : 
					$data['status_date_label'] = get_string('challengeexpired_on', 'local_challenge');
					$data['status_change_date'] = \local_costcenter\lib::get_userdate("d/m/Y H:i ", $challenge->timemodified);
			}
			$classname = "\\$challenge->module_type\\local\\general_lib";
			if(class_exists($classname)){
				$class = new $classname;
				if(method_exists($class, 'get_custom_data')){
					$name_field = $challenge->module_type == 'local_courses' ? " fullname as modulename " : " name as modulename ";
					$fields = " {$name_field} , startdate, enddate ";
					$params = array('id' => $challenge->module_id);
					$moduledata = $class->get_custom_data($fields, $params);
					// print_object($moduledata);
					$data['modulename'] = $moduledata->modulename;
					$data['modulestartdate'] = $moduledata->startdate ? \local_costcenter\lib::get_userdate("d/m/Y H:i ", $moduledata->startdate) : 'N/A';
					$data['moduleenddate'] = $moduledata->enddate ? \local_costcenter\lib::get_userdate("d/m/Y H:i ", $moduledata->enddate) : 'N/A';
				}
				if(method_exists($class, 'get_module_logo_url')){
					 $logo_url = $class->get_module_logo_url($challenge->module_id);
					 $data['modulelogo_url'] = is_a($logo_url, 'moodle_url') ? $logo_url->out() : $logo_url;
				}
				if(method_exists($class, 'get_custom_icon_details')){
					$newdata = $class->get_custom_icon_details();
					$data = array_merge($data, $newdata);
				}
			}
			$records[] = $data;
		}
		return array('records' => $records, 'totalchallenges' => $totalchallenges);
	}
	public function challenge_alter_status($challengeid, $status){
		if($this->check_can_alter_status($challengeid, $status)){
			$newdata = new \stdClass();
			$newdata->id = $challengeid;
			$newdata->status = $status;
			$newdata->timemodified = time();
			$notification = new \local_challenge\notification();
			
			$emaildata = new \stdClass();
			$emaildata->challengedata = local_challenge_get_challenge($challengeid);
			// if($status == CHALLENGE_COMPLETED){


			// }else{
			// 	if($status == CHALLENGE_ACTIVE){
			// 		// $challenge_info = $this->db->get_record('local_challenge', array('id' => $challengeid), 'id, module_type, module_id');
			// 		// $table = $challenge_info->module_type == 'local_courses' ? 'course' : $challenge_info->module_type;
			// 		// $moduleinfo = $this->db->get_record($table, array('id' => $challenge_info->module_id));
			// 		/* //Enabling this code will enrol / request the module for the user accepting the challenge
			// 		$classname = "\\$challenge_info->module_type\\local\\general_lib";
			// 		if(class_exists($classname)){
			// 			$class = new $classname();
			// 			if(method_exists($class, 'enable_enrollment_to_module'))
			// 				$class->enable_enrollment_to_module($challenge_info->module_id, $this->user);
			// 		}*/

			// 	}
			$emaildata = new \stdClass();
			$emaildata->challengedata = local_challenge_get_challenge($challengeid);
			switch($status){
				case CHALLENGE_ACTIVE :		
					$emaildata->notificationtype = 'challenge_accepted';
					$emaildata->touserid = $emaildata->challengedata->userid_from;
					$emaildata->fromuserid = $emaildata->challengedata->userid_to;
					$notification->send_challenge_email($emaildata);
					$newdata->timemodified = time();
				break;
				case CHALLENGE_DECLINED :
					$emaildata->notificationtype = 'challenge_declined';
					$emaildata->touserid = $emaildata->challengedata->userid_from;
					$emaildata->fromuserid = $emaildata->challengedata->userid_to;
					$notification->send_challenge_email($emaildata);
					$newdata->timemodified = time();
				break;
				case CHALLENGE_COMPLETED :
					$newdata->timecompleted = time();

					$emaildata->notificationtype = 'challenge_complete_win';
					$emaildata->touserid = $emaildata->challengedata->userid_to;
					$emaildata->fromuserid = $emaildata->challengedata->userid_from;
					$notification->send_challenge_email($emaildata);

					$emaildata->notificationtype = 'challenge_complete_lose';
					$emaildata->touserid = $emaildata->challengedata->userid_from;
					$emaildata->fromuserid = $emaildata->challengedata->userid_to;
					$notification->send_challenge_email($emaildata);
				break;
				case CHALLENGE_INCOMPLETE : 
					$emaildata->notificationtype = 'challenge_incomplete';
					$emaildata->touserid = $emaildata->challengedata->userid_to;
					$emaildata->fromuserid = $emaildata->challengedata->userid_from;
					// challengee email 
					$notification->send_challenge_email($emaildata);

					$emaildata->touserid = $emaildata->challengedata->userid_from;
					$emaildata->fromuserid = $emaildata->challengedata->userid_to;
					// challenger email
					$notification->send_challenge_email($emaildata);
				break;
				case CHALLENGE_EXPIRED :
					$emaildata->notificationtype = 'challenge_expired';
					$emaildata->touserid = $emaildata->challengedata->userid_to;
					$emaildata->fromuserid = $emaildata->challengedata->userid_from;
					// challengee email 
					$notification->send_challenge_email($emaildata);

					$emaildata->touserid = $emaildata->challengedata->userid_from;
					$emaildata->fromuserid = $emaildata->challengedata->userid_to;
					// challenger email
					$notification->send_challenge_email($emaildata);
				break;

			}
			// }
			$status = $this->db->update_record('local_challenge', $newdata);
		}else{
			$status = False;
		}
		return $status; 
	}
	public function check_can_alter_status($challengeid, $status){
		$currentstatus = $this->get_current_challenge_status($challengeid);
		
		switch($currentstatus){
			case CHALLENGE_NEW :
				return ($status == CHALLENGE_ACTIVE || $status == CHALLENGE_DECLINED || $status == CHALLENGE_EXPIRED) ? True :False;
			break;
			case CHALLENGE_ACTIVE : 
				return ($status == CHALLENGE_COMPLETED || $status == CHALLENGE_INCOMPLETE) ? True : False;
			break;
			case CHALLENGE_DECLINED:
			case CHALLENGE_COMPLETED:
			case CHALLENGE_INCOMPLETE :
			case CHALLENGE_EXPIRED :
				return  False;
			break;
		}
	}
	public function get_current_challenge_status($challengeid){
		return $this->db->get_field('local_challenge', 'status', array('id' => $challengeid));
	}
	public function get_generallib_method_info($moduletype, $methodname){
		$classname = "\\{$moduletype}\\local\\general_lib";
		$return = False;
		if(class_exists($classname)){
			$class = new $classname();
			if(method_exists($class, $methodname)){
				$return = $class;
			}
		}
		return $return;
	}
}