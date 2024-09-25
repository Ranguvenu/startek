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
 * @subpackage local_request
 */
namespace local_request\api;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use context_system;
use context_course;
use local_classroom\classroom;
use local_program\program;
use local_certification\certification;
use local_learningplan\lib\lib as learningplanlib;
use core_user;
// use \local_request\notifications_emails as reqnotifications_emails;

//define('ADD', 1);
/**
 * Class  to do request CRUD operations 
 */
  class requestapi {

    /** @var text to hold component name to differentiate(program,elearning, classroom, learningpath). */
    protected $componentname = null;

    /** @var int to hold the componrntid like (programid, elearningid etc. */
    protected $componentid = null;

    /**
     * Construct this renderable.
     * @param int $courseid The course record for this page.
     */
    public function __construct($componentname=null, $componentid=null) {
        global $USER;
        $this->componentname = $componentname;    
        $this->componentid = $componentid;       

    }

    public static function create($component, $componentid){
      global $DB, $USER, $CFG;  
      if(file_exists($CFG->dirroot . '/local/lib.php')){
          require_once($CFG->dirroot . '/local/lib.php');
      }
      $notification = new \local_request\notification();
      if($component=='classroom'){
        $context =(new \local_classroom\lib\accesslib())::get_module_context($componentid);
      }else if($component=='learningplan'){
        $context =(new \local_learningplan\lib\accesslib())::get_module_context($componentid);
      }else if($component=='elearning'){
        $context = context_course::instance($componentid);
      }else{
        $context = context_system::instance();
      }
      $record = new stdClass();
      $record->compname = $component;
      $record->componentid  = $componentid;
      $record->module_id  = $componentid;

      $record->createdbyid = $USER->id;
      $record->timecreated = time();
      $record->timemodified= time();
      $record->usermodified = $USER->id;
      $record->status = 'PENDING';    

      $newrecordid=0;
      $type = 'request_add';
      $dataobj = $componentid;
      $exists=$DB->record_exists('local_request_records', array('componentid'=>$componentid, 'compname'=>$component, 'createdbyid'=>$USER->id));
      $exist_req=$DB->get_record('local_request_records', array('componentid'=>$componentid, 'compname'=>$component, 'createdbyid'=>$USER->id));
       $newrecordid=$DB->insert_record('local_request_records',$record);
       $lpcreated_id = get_users_by_capability($context,
                            'local/request:approverecord',
                            '',
                            'lastname',
                            '',
                            '',
                            '',
                            'suspended != 0,deleted != 0',
                            false); 
          // Trigger request created event.
          $params = array(
              'context' => (new \local_request\lib\accesslib())::get_module_context(),
              'objectid' => $newrecordid,
              'other'=>array('component'=>$component,
                  'componentid'=>$componentid)
          );
      
          $event = \local_request\event\request_created::create($params);
          $requests=$DB->get_record('local_request_records', array('id'=> $newrecordid));
          $event->add_record_snapshot('local_request_records', $requests);
          $event->trigger();
          $requesteduser = \core_user::get_user($requests->createdbyid);
          list($zero, $org_id, $ctr_id, $bu, $cu, $territory) = explode("/",$requesteduser->open_path);
          $requesteduser->open_costcenterid=$org_id;
          $requesteduser->open_departmentid = $ctr_id;     
          
          $touser = \core_user::get_support_user();
          $logmail = $notification->request_notification($type, $requests, $touser, $lpcreatedid, $requesteduser);

      /*     foreach($lpcreated_id as $created_id){
          $costcenterid=explode('/',$created_id->open_path)[1];
          $countryid=explode('/',$created_id->open_path)[2];
          if($costcenterid == $org_id){
               if($countryid != $ctr_id){
                continue;
              }
            $lpcreatedid = core_user::get_support_user();
            $touser = core_user::get_user($created_id->id);
            $touser->costcenter=$costcenterid;
            $logmail = $notification->request_notification($type, $requests, $touser, $lpcreatedid, $requesteduser);
          }
        } */
      
      return $newrecordid;
    } // end of  addinstance();


    public static function delete($id){
      global $DB, $USER;
      $deleterecord=0;      
      $beforedeleteinfo = $DB->get_records('local_request_records',array('id'=>$id));
      $exists=$DB->record_exists('local_request_records', array('id'=>$id));
      if($exists){ 
        // Trigger request deleted event.
        $params = array(
              'context' => (new \local_request\lib\accesslib())::get_module_context(),
              'objectid' => $id,
              'other'=>array('component'=>$beforedeleteinfo->compname,
                  'componentid'=>$beforedeleteinfo->componentid)
        );      
        $event = \local_request\event\request_deleted::create($params);    
        $event->trigger();

        $DB->delete_records('local_request_records',array('id'=>$id));
        $deleterecord=1;
      }
      return $deleterecord; 
  } // end of  addinstance();

  public static function approve($id){     
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/local/lib.php'); 
    $notification = new \local_request\notification();
    
    $context =(new \local_request\lib\accesslib())::get_module_context();
    $updated_recordid =0;
    $type = 'request_approve';
    $dataobj = $id;
    $exists = $DB->get_record('local_request_records', array('id'=>$id)); 
    if($exists){
      $updaterecord = new stdclass();
      $updaterecord = $exists;
      $updaterecord->createdbyid = $exists->createdbyid;
      $updaterecord->id = $exists->id;
      $updaterecord->responder = $USER->id;
      $updaterecord->respondeddate = time();
      $updaterecord->usermodified = $USER->id;
      $updaterecord->timemodified = time();
      $updaterecord->status = 'APPROVED';
      if($updaterecord->compname && $updaterecord->componentid){
        $component= $updaterecord->compname;
        $componentid = $updaterecord->componentid;
        $userid= $updaterecord->createdbyid;
        $enroll_to_component=self::enroll_to_component($component, $componentid, $userid);
        if($enroll_to_component !=-1){
          $updated_recordid = $DB->update_record('local_request_records', $updaterecord);
        }
        // Trigger request approved event.
        $params = array(
              'context' => (new \local_request\lib\accesslib())::get_module_context(),
              'objectid' => $updaterecord->id,
              'other'=>array('component'=>$component,
                  'componentid'=>$componentid)
        );
      
        $event = \local_request\event\request_approved::create($params);
        $requests=$DB->get_record('local_request_records', array('id'=> $updaterecord->id));
        $event->add_record_snapshot('local_request_records', $requests);
        $event->trigger();
        $requesteduser = \core_user::get_user($requests->createdbyid);
        $touser = core_user::get_user($updaterecord->createdbyid);
        list($zero, $org_id, $ctr_id, $bu, $cu, $territory) = explode("/",$touser->open_path);
          $touser->costcenter=$org_id;     
        $fromuserid = core_user::get_support_user();
        $logmail = $notification->request_notification($type, $requests, $touser, $fromuserid, $requesteduser);
      } else{
        $updated_recordid =0;
      }
    } else{
      $updated_recordid =0;
    }
    if($component=='classroom'){
      if($enroll_to_component > 0){

          $params=array();        
            $sql = "SELECT lw.sortorder as classroomwaitinglistno,c.name as classroom,concat(u.firstname,'',u.lastname) as username, c.id AS classroomid
                FROM {local_classroom_waitlist} as lw
                JOIN {local_classroom} AS c ON c.id = lw.classroomid
                JOIN {user} as u ON u.id=lw.userid
                where lw.id=:waitlistid";
          $params['waitlistid'] = $enroll_to_component;        
          $stringobj=$DB->get_record_sql($sql, $params);
          $activesql = "SELECT lcw.id FROM {local_classroom_waitlist} as lcw where lcw.classroomid=:classroomid and lcw.enrolstatus=0 ";
          $active = $DB->get_fieldset_sql($activesql, array('classroomid' => $stringobj->classroomid));
          $classroomwaitinglistno=array_search ($enroll_to_component, $active);
          $stringobj->classroomwaitinglistno=($classroomwaitinglistno+1) ? ($classroomwaitinglistno+1) : $stringobj->classroomwaitinglistno ;
          $return_status=get_string("otherclassroomwaitlistinfo",'local_classroom',$stringobj);
      }else{
        $return_status=$enroll_to_component;
      }
      return $return_status;
    }else{
      if($component=='certification' && $enroll_to_component ==-1){
          $return_status=$enroll_to_component;
          return $return_status;
      }else{
        return  $updated_recordid;
      }
      
    }
  }  // end of approve_requestinstance 


  public static function deny($id){     
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/local/lib.php');
    $notification = new \local_request\notification(); 
    $previousrecord = new stdclass();
     
    $context =(new \local_request\lib\accesslib())::get_module_context();
    $updated_recordid =0;
    $type = 'request_deny';
    $dataobj = $id;
    $exists = $DB->get_record('local_request_records', array('id'=>$id));
    $status = $exists->status;
    if($exists){
      $updaterecord = new stdclass();
      $updaterecord = $exists;
      $updaterecord->createdbyid = $exists->createdbyid;
      $updaterecord->id = $exists->id;
      $updaterecord->responder = $USER->id;
      $updaterecord->respondeddate = time();
      $updaterecord->usermodified = $USER->id;
      $updaterecord->timemodified = time();
      $updaterecord->status = 'REJECTED';
      //--- checking earlier approved or not
      if($updaterecord->compname && $updaterecord->componentid){
        $component= $updaterecord->compname;
        $componentid = $updaterecord->componentid;
        $userid= $updaterecord->createdbyid;
        $previousrecord = $DB->get_record('local_request_records', array('id'=>$id));
        if($status =='APPROVED'){
          self::unenroll_to_component($component, $componentid, $userid);
        }

        $updated_recordid = $DB->update_record('local_request_records', $updaterecord);
        // Trigger request approved event.
        $params = array(
              'context' => (new \local_request\lib\accesslib())::get_module_context(),
              'objectid' => $updaterecord->id,
              'other'=>array('component'=>$component,
                  'componentid'=>$componentid)
        );
      
        $event = \local_request\event\request_rejected::create($params);
        $requests=$DB->get_record('local_request_records', array('id'=> $updaterecord->id));
        $event->add_record_snapshot('local_request_records', $requests);
        $event->trigger();
        $requesteduser = \core_user::get_user($requests->createdbyid);
        $touser = core_user::get_user($updaterecord->createdbyid);
        list($zero, $org_id, $ctr_id, $bu, $cu, $territory) = explode("/",$touser->open_path);
          $requesteduser->costcenter=$org_id;
        $fromuserid = core_user::get_support_user();
        $logmail = $notification->request_notification($type, $requests, $touser, $fromuserid, $requesteduser);
      }else{
        $updated_recordid =0;
      }
    }else{
      $updated_recordid =0;
    }
    return  $updated_recordid;
  }  // end of approve_requestinstance 


  public static function get_requestbutton($componentid, $component, $componentname){
    $action = 'add';
    //  $component = '".$component."';
    $enrollmentbtn = '<a href="javascript:void(0);" class="req_button cat_btn btn btn-block viewmore_btn" alt = ' . get_string('requestforenroll','local_request'). ' title = ' .get_string('requestforenroll','local_request'). ' onclick="(function(e){ require(\'local_request/requestconfirm\').init({componentid:'.$componentid.', component:\''.$component.'\', action:\''.$action.'\', componentname:\''.$componentname.'\' }) })(event)" >'.get_string('requestforenroll','local_classroom').'</a>';
    return $enrollmentbtn;

   } // end of  get_requestbutton function

  public static function enroll_to_component($component, $componentid, $userid){
    global $DB;
    switch($component){     
      case 'classroom' : 
        $newrecordid=(new classroom)->classroom_self_enrolment($componentid, $userid, $request=1,'request');         
        return $newrecordid;
        break;

      case 'program' :
        $newrecordid=(new program)->program_self_enrolment($componentid, $userid);
        return $newrecordid; 
        break;
                          
      case 'certification' :
        $newrecordid=(new certification)->certification_self_enrolment($componentid, $userid, $request=1);
        return $newrecordid;
        break;                    

      case 'learningplan' : 
        $newrecord = new stdClass();
        $newrecord->planid= $componentid;
        $newrecord->userid = $userid;
        $learningmsg= (new learningplanlib)->assign_users_to_learningplan($newrecord);
        return $learningmsg;
        break;
      case 'elearning' :    
        $enrolledid=0;
        $course_context = context_course::instance($componentid);
        if(!is_enrolled($course_context, $userid)){
          $enrol_id = $DB->get_record_sql("SELECT me.id,me.enrol FROM
                        {enrol} AS me WHERE me.courseid = $componentid 
                        AND me.enrol LIKE 'self'"); 
          if(!empty($enrol_id)){
            $instance = $DB->get_record('enrol', array('id'=>$enrol_id->id, 'enrol'=>'self'), '*', MUST_EXIST);  
            $employee = $DB->get_record('role', array('shortname' => 'employee'));
            $enrolplugin = enrol_get_plugin('self');

            $enrolledid= $enrolplugin->enrol_user($instance, $userid, $employee->id);
          }
        }   
        return $enrolledid;
        break;
    } // end of switch statement
  } // end of enroll_to_component
  public static function unenroll_to_component($component, $componentid, $userid){
    global $DB;
    switch($component){     
      case 'classroom' : 
        $newrecordid=(new classroom)->classroom_remove_assignusers($componentid, array($userid), $request=1);
        return $newrecordid;
        break; 
      case 'learningplan' :  
        $newrecord = new stdClass();
        $newrecord->planid= $componentid;
        $newrecord->userid = $userid;
        $learningmsg= (new learningplanlib)->delete_users_to_learningplan($newrecord);
        return $learningmsg;
        break;
      case 'elearning' :
        $enrolledid=0;
        $course_context = context_course::instance($componentid);
        if(is_enrolled($course_context, $userid)){
          $enrol_id = $DB->get_records_sql("SELECT distinct(mue.enrolid),me.enrol
            FROM {user_enrolments} AS mue
            INNER JOIN {enrol} AS me ON mue.enrolid = me.id
            WHERE me.courseid = {$componentid} ");
          foreach($enrol_id as $enrolid){
            if($enrolid->enrol == 'self'){
              $instance = $DB->get_record('enrol', array('id'=>$enrolid->enrolid, 'enrol'=>'self'), '*', MUST_EXIST);
              $employee = $DB->get_record('role', array('shortname' => 'employee'));
              $enrolplugin = enrol_get_plugin('self');
              $enrolledid= $enrolplugin->unenrol_user($instance, $userid);
            }
          }
        }
        return $enrolledid;
        break;
   } // end of switch statement
  } // end of unenroll_to_component
} // end of class
