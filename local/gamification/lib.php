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
 * @subpackage local_gamification
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

function local_gamification_output_fragment_userdetails($args){
	global $DB;
	// print_object($args);
	$userid = $args['userid'];
	$type = $args['type']; //week,month,overall.
	$eventname = $args['eventname']; //cc,clc,certc,progc.
	$objectid = $args['objectid']; //id of the tabledata.
	$courseid = $args['courseid'];//id of the course if type==course.
 	// print_object($objectid);
	$data = array();
	$table = new \html_table();
	
	if($eventname == 'site'){
		$table->head = array('Event','Points');
		// $user_data_sql = "SELECT id,$start,$end FROM $table_name";
		$eventparams = array();
		$eventparams['userid'] = $userid;
		if($type == 'overall'){
			$field = 'points';
			$table_name = 'block_gm_'.$type.'_'.$eventname;
			$tablecode = $type; 
		}else{
			$tablecode = $type.'ly';
			$table_name = 'block_gm_'.$type.'ly_'.$eventname;
			$field = $type.'lypoints';
			$start = $type.'start';
			$end = $type.'end';
			$start_enn_data = $DB->get_record($table_name ,array('id' => $objectid),'id,'.$start.','.$end.'');
			$eventparams[$start] = $start_enn_data->$start;
			$eventparams[$end] = $start_enn_data->$end;
			if($eventname == 'course'){
				$eventparams['courseid'] = $courseid;
			}
		}
		$active_events = $DB->get_records('block_gm_events',array('active' => 1));
		foreach($active_events as $events){
			$tabledata = array();
			$tablename = 'block_gm_'.$tablecode.'_'.$events->eventcode;
			// print_object($eventparams);
			$points = $DB->get_field($tablename,$field,$eventparams);
			if($points){
				$tabledata['eventname'] = $events->shortname;
				$tabledata['points'] = $points; 
				$data[] = $tabledata;				
			}

		}
		$tablename = 'block_gm_'.$tablecode.'_course';
		$points = $DB->get_field($tablename,$field,$eventparams);
		if($points){
			$tabledata['eventname'] = 'courselevel';
			$tabledata['points'] = $points; 
			$data[] = $tabledata;				
		}

	}else{
		$eventsarray = array('cc' => 'Completed Courses','clc' => 'Completed Classrooms', 'progc' => 'Completed Programs', 'certc' => 'Completed Certifications', 'lpc' => 'Completed Learningplans','course' => 'Course Points');
		$headname = $eventsarray[$eventname];
		
		$start = $type.'start';
		$end = $type.'end';
		if($type == 'overall'){
			$tablename = 'block_gm_'.$type.'_'.$eventname;
			$custom_db_data = $DB->get_record($tablename, array('userid' => $userid),'courseid');
			if($custom_db_data->courseid){
				$completedids = $custom_db_data->courseid;
				// print_object($completedids);
				// print_object($custom_db_data->courseid);
				$eventtablearray = array('cc' => 'course', 'clc' => 'local_classroom','certc' => 'local_certification', 'progc' => 'local_program','lpc' => 'local_learningplan');
				switch($eventname){
					case 'cc':
						$completed_sql = "SELECT id,fullname FROM {course} where id in($completedids)";
						$name = 'fullname';

					break;
					case 'clc':
						$completed_sql = "SELECT id,name FROM {local_classroom} where id in($completedids)";
						$name = 'name';
					break;
					case 'progc':
						$completed_sql = "SELECT id,name FROM {local_program} where id in($completedids)";
						$name = 'name';
					break;
					case 'certc':
						$completed_sql = "SELECT id,name FROM {local_certification} where id in($completedids)";
						$name = 'name';
					break;
					case 'course':
						$activities_sql = "SELECT id,sum(gamification) as gamification,eventname FROM {block_gamification_log} WHERE courseid=$courseid AND userid=$userid GROUP BY eventname";
						$activities = $DB->get_records_sql($activities_sql);
					break;
				}
				if($eventname != 'course'){
					$completiondata = $DB->get_records_sql($completed_sql);

					$table->head = array($headname);
					foreach($completiondata as $completioninfo){
						$data[] = ['name'=>$completioninfo->$name];
					}
				}else{
					$table->head = array('Events', 'Points');
					foreach($activities as $activityinfo){
						$eventname_arr = explode('\\',$activityinfo->eventname);
						$eventname_module = explode('_',$eventname_arr[1]);
						// print_object($eventname_arr);
						$eventname_str = $eventname_module[1].' - '.$eventname_arr[3]; 
						$data[] = ['eventname' => $eventname_str, 'points' => $activityinfo->gamification];
					}
				}
			}
		}else{
			$tablename = 'block_gm_'.$type.'ly_'.$eventname;
			$user_type_status = $DB->get_record($tablename,array('userid' => $userid,'id' => $objectid),'id,'.$start.','.$end.'');
			// $user_data_sql = "SELECT id,$start,$end FROM $table_name";
			switch($eventname){
				case 'cc':
					$completionsql = "SELECT cc.id,cc.course,c.fullname 
						FROM {course_completions} AS cc
						JOIN {course} AS c ON c.id = cc.course
						WHERE cc.userid=:userid AND cc.timecompleted BETWEEN :timestart AND :timeend";
					$elementname = 'fullname';
					break;
				case 'clc':
					$completionsql = "SELECT lcu.id,lcu.classroomid,lc.name 
						FROM {local_classroom_users} AS lcu
						JOIN {local_classroom} AS lc ON lc.id = lcu.classroomid
						WHERE lcu.userid=:userid AND lcu.completiondate BETWEEN :timestart AND :timeend";
					$elementname = 'name';
					break;
				case 'certc':
					$completionsql = "SELECT lcu.id,lcu.certificationid,lc.name 
						FROM {local_certification_users} AS lcu
						JOIN {local_certification} AS lc ON lc.id = lcu.certificationid
						WHERE lcu.userid=:userid AND lcu.completiondate BETWEEN :timestart AND :timeend";
					$elementname = 'name';
					break;
				case 'progc':
					$completionsql = "SELECT lpu.id,lpu.programid,lp.name 
						FROM {local_program_users} AS lpu
						JOIN {local_program} AS lp ON lp.id = lpu.programid
						WHERE lpu.userid=:userid AND lpu.completiondate BETWEEN :timestart AND :timeend";
					$elementname = 'name';
					break;
			}
			if($eventname == 'course'){
				$table->head = array('Event', 'points');
				$activities_sql = "SELECT id, sum(gamification) as gamification,eventname FROM {block_gamification_log} WHERE `time` BETWEEN :timestart AND :timeend AND userid=:userid AND courseid=:courseid GROUP BY eventname";
				$activity_data = $DB->get_records_sql($activities_sql, array('userid' => $userid,'timestart' => $user_type_status->$start, 'timeend' =>$user_type_status->$end, 'courseid' => $courseid));
				foreach($activity_data as $activity_info){
					$eventname_arr = explode('\\',$activity_info->eventname);
					$eventname_module = explode('_',$eventname_arr[1]);
					$eventname_str = $eventname_module[1].' - '.$eventname_arr[3];
					$data[] = ['event' => $eventname_str, 'points' => $activity_info->gamification];
				}
			}else{
				$table->head = array($headname);
				$completiondata = $DB->get_records_sql($completionsql, array('userid' => $userid,'timestart' => $user_type_status->$start, 'timeend' =>$user_type_status->$end));
				foreach($completiondata as $completioninfo){
					$data[] = ['name'=>$completioninfo->$elementname];
				}
			}

		}

		
	}
	// $
	// print_object($data);
	// $user_data = $DB->get_records_sql($user_data_sql);
	
	$table->data = $data;
	$o .= html_writer::table($table);
	return $o;
}

// function local_gamification_leftmenunode(){
// 	$enabled =  (int)get_config('', 'local_gamification_enable_gamification');
//     if(!$enabled){
//     	return;
//     }
//     $systemcontext = context_system::instance();
//     $gamenode = '';
//     if(has_capability('block/gamification:view',$systemcontext) && !is_siteadmin() && !has_capability('block/gamification:manage',$systemcontext)){
//         $gamenode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_gamification', 'class'=>'pull-left user_nav_div gamification'));
//         $url = new moodle_url('/blocks/gamification/dashboard.php');
//         $label = get_string('levelup','block_gamification');
//         $gamenode .= html_writer::link($url, '<i class="fa fa-trophy" aria-hidden="true"></i><span class="user_navigation_link_text">'.$label.'</span>',array('class'=>'user_navigation_link'));
//         $gamenode .= html_writer::end_tag('li');
//     }

//     return array('13' => $gamenode);
// }
