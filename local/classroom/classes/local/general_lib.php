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
 * @subpackage classroom
 * @author eabyas  <info@eabyas.in>
**/
namespace local_classroom\local;
class general_lib{
	public function get_completion_count_from($moduleid, $userstatus, $date = NULL){
		global $DB;
		$params = array('moduleid' => $moduleid);
		switch($userstatus){
			case 'enrolled':
				$count_sql = "SELECT count(id) FROM {local_classroom_users} WHERE classroomid = :moduleid ";
				if(!is_null($date)){
					$count_sql .= " AND timecreated > :fromtime ";
					$params['fromtime'] = $date;
				}
			break;
			case 'completed':
				$count_sql = "SELECT count(id) FROM {local_classroom_users} WHERE classroomid = :moduleid AND completion_status = 1 ";
				if(!is_null($date)){
					$count_sql .= " AND completiondate > :fromtime ";
					$params['fromtime'] = $date;
				}
			break;
		}
		$count = $DB->count_records_sql($count_sql, $params);
		return $count;
	}
	public function get_custom_icon_details(){
		return ['componenticonclass' => 'classroom_icon', 'customimage_required' => True];
	}

	public function get_classroom_info($id){
        global $DB, $USER, $CFG;
        require_once($CFG->dirroot.'/local/search/lib.php');
        require_once($CFG->dirroot.'/local/ratings/lib.php');
        $classroom = $DB->get_record('local_classroom', array('id' => $id));
        if($classroom){
            $classroom->fullname = $classroom->name;
            $classroom->summary = $classroom->description;
            $classroom->points = $course->open_points;

            if(file_exists($CFG->dirroot.'/local/includes.php')){
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new \user_course_details();
            }
            $coursefileurl = (new \local_classroom\classroom)->classroom_logo($coursefileurl = $classroom->classroomlogo);
            if($coursefileurl == false){
                $coursefileurl = $includes->get_classes_summary_files($classroom);
            }
            $classroom->isenrolled = $DB->record_exists('local_classroom_users', array('classroomid' => $classroom->id, 'userid' => $USER->id));
            $waitlist = $DB->get_field('local_classroom_waitlist','id',array('classroomid' => $list->id,'userid'=>$USER->id,'enrolstatus'=>0));
            $classroom->requeststatus = MODULE_NOT_ENROLLED;
            $certificate_code = ($DB->get_field('tool_certificate_issues','code',array('moduletype'=> 'classroom','moduleid' => $classroom->id, 'userid' => $USER->id))) ;
            $classroom->certificateid = $certificate_code ? $certificate_code : '';
            if($waitlist > 0){
                $classroom->requeststatus = MODULE_ENROLMENT_WAITING;
            }else{
                if($classroom->isenrolled){
                    $classroom->requeststatus = MODULE_ENROLLED;
                }else{
                    if($classroom->approvalreqd == 1){
                        $sql = "SELECT status FROM {local_request_records} WHERE componentid=:componentid AND compname LIKE :compname AND createdbyid = :createdbyid ORDER BY id desc ";
                        $requeststatus = $DB->get_field_sql($sql, array('componentid' => $classroom->id,'compname' => 'classroom', 'createdbyid'=>$USER->id));
                        if($requeststatus == 'PENDING'){
                            $classroom->requeststatus = MODULE_ENROLMENT_PENDING;
                        }
                    }
                }
            }
            $classroom->bannerimage = is_object($coursefileurl) ? $coursefileurl->out() : $coursefileurl;
            $classroom->category = ($DB->get_field('local_custom_fields','fullname',array('id' => $classroom->open_category))) ;
            $classroom_capacity_check = (new \local_classroom\classroom)->classroom_capacity_check( $classroom->id);
            $classroom->enrolment_status_message = 0;
            if($classroom_capacity_check && $classroom->status == 1 && !$classroom->isenrolled &&  $classroom->allow_waitinglistusers == 0){
                $classroom->enrolment_status_message = 1;
            }else if($classroom->nomination_startdate > 0 && $classroom->nomination_startdate >  time()){
                $classroom->enrolment_status_message = 2;
            }else if($classroom->nomination_enddate > 0 && $classroom->nomination_enddate < time()){
                $classroom->enrolment_status_message = 3;
            }
            $classroom->coursecount = $DB->count_records_sql("SELECT count(c.id) FROM {course} AS c JOIN {local_classroom_courses} AS lcc ON lcc.courseid = c.id WHERE lcc.classroomid = :classroomid ", array('classroomid' => $classroom->id));         
            $modulerating = $DB->get_field('local_ratings_likes', 'module_rating', array('module_id' => $classroom->id, 'module_area' => 'local_classroom'));
            if(!$modulerating){
                 $modulerating = 0;
            }
            $classroom->rating = $modulerating;
            $likes = $DB->count_records('local_like', array('likearea'=> 'local_classroom', 'itemid'=>$classroom->id, 'likestatus'=>'1'));
            $dislikes = $DB->count_records('local_like', array('likearea'=> 'local_classroom', 'itemid'=>$classroom->id, 'likestatus'=>'2'));
            $avgratings = get_rating($classroom->id, 'local_classroom');
            $avgrating = $avgratings->avg;
            $ratingusers = $avgratings->count;
            $classroom->likes = $likes;
            $classroom->dislikes = $dislikes;
            $classroom->avgrating = $avgrating;
            $classroom->ratingusers = $ratingusers;
            $classroom->module = 'local_classroom';
            $likeinfo = ($DB->get_field('local_like','likestatus',array('itemid' => $classroom->id,'likearea' => 'local_classroom','userid'=>$USER->id))) ;
			$classroom->likedstatus =$likeinfo ? $likeinfo : '0';

            
            return $classroom;
        }else{
            throw new \Exception("Classroom Not found");
        }
    }
}
