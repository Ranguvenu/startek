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
namespace local_challenge;
global $CFG;
require_once($CFG->dirroot .'/local/notifications/lib.php');
class notification{
	public $db;
	public $user;
	public function __construct($db=null, $user=null){
		global $DB, $USER;
		$this->db = $db ? $db :$DB;
		$this->user = $user ? $user :$USER;
	}
	public function get_string_identifiers($emailtype){
		switch($emailtype){
			case 'challenge_created':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
			break;
			case 'challenge_accepted':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
			break;
			case 'challenge_declined':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
			break;
			case 'challenge_complete_win':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
                            
			break;
			case 'challenge_complete_lose':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
                            
			break;
			case 'challenge_incomplete':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
                            
			break;
			case 'challenge_expired':
				$strings = "[module_startdate],
                            [module_enddate], [challenger_name],
                            [challengee_name], [challenge_moduletype], [challenge_modulename],
                            [challengee_email], [challenger_name], [challenger_email]";
                            
			break;
		}
		return $strings;
	}
	public function send_challenge_email($emaildata){
		$notification = $this->get_existing_notification($emaildata);
		if($notification){
			$mailobject = new \stdClass();
			$mailobject->notification_infoid = $notification->id;
			$mailobject->challenge_moduletype = get_string('module_'.$emaildata->challengedata->module_type, 'local_challenge');
			$challenge_lib = new \local_challenge\local\lib();
			$methodexist = $challenge_lib->get_generallib_method_info($emaildata->challengedata->module_type, 'get_custom_data');
			if($methodexist){
				$moduledata = $methodexist->get_custom_data('*', array('id' => $emaildata->challengedata->id));
				$mailobject->module_startdate = $moduledata->startdate ? \local_costcenter\lib::get_userdate("d/m/Y H:i ", $moduledata->startdate) : 'N/A';
				$mailobject->module_enddate = $moduledata->enddate ? \local_costcenter\lib::get_userdate("d/m/Y H:i ", $moduledata->enddate) : 'N/A';
				$mailobject->challenge_modulename = $emaildata->challengedata->module_type == 'local_course' ? $moduledata->fullname : $moduledata->name; 
				$userfields = \user_picture::fields();
				$userfields .= " , open_supervisorid ";
				$user_from = \core_user::get_user($emaildata->challengedata->userid_from, $userfields);
				$user_to = \core_user::get_user($emaildata->challengedata->userid_to, $userfields);
				$mailobject->challenger_name = fullname($user_from);
				$mailobject->challenger_email = $user_from->email;
				$mailobject->challengee_name = fullname($user_to);
				$mailobject->challengee_email = $user_to->email;
			}
			$mailobject->adminbody = NULL;
		    $mailobject->body = $notification->body;
		    $mailobject->subject = $notification->subject;
		    $touser = $emaildata->touserid == $user_to->id ? $user_to : $user_from;
		    $fromuser = $emaildata->fromuserid == $user_from->id ? $user_from : $user_to;
 		    $mailobject->touserid = $emaildata->touserid;
		    $mailobject->fromuserid = $emaildata->fromuserid;
		    $mailobject->teammemberid = 0;
		    
		    if(!empty($notification->adminbody) && !empty($touser->open_supervisorid)){
		    	$superuser = \core_user::get_user($touser->open_supervisorid);
		    }else{
		    	$superuser = false;
		    }
		    $emailtype = $emaildata->notificationtype;
		    $this->log_email_notification($touser, $fromuser, $mailobject, $emailtype);
			if($superuser){
				$mailobject->body = $notification->adminbody;
				$mailobject->touserid = $superuser->id;
				$mailobject->teammemberid = $touser->id;
				$this->log_email_notification($superuser, $fromuser, $mailobject, $emailtype);
			}
		}
	}
	public function get_existing_notification($emaildata){
		global $DB, $USER;
		$moduleinfo = False;
		$classname = "\\{$emaildata->challengedata->module_type}\\local\\general_lib";
		if(class_exists($classname)){
			$class = new $classname();
			if(method_exists($class, 'get_custom_data')){
				$orgfield = $emaildata->challengedata->module_type == 'local_courses' ? 'open_costcenterid as costcenterid' : 'costcenter as costcenterid';
				$fields = " id, {$orgfield} ";
				$moduleinfo = $class->get_custom_data($fields, array('id' => $emaildata->challengedata->module_id));
			}
		}
		$notification = False;
		if($moduleinfo){
			$notification_sql = "SELECT lni.* FROM {local_notification_info} AS lni 
				JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
				WHERE lni.costcenterid = :costcenterid AND lnt.shortname LIKE :emailtype ";
			$notification = $DB->get_record_sql($notification_sql, array('costcenterid' => $moduleinfo->costcenterid, 'emailtype' => $emaildata->notificationtype));
		}
		return $notification;
	}
	public function log_email_notification($user, $fromuser, $datamailobj, $emailtype){
		$dataobject = clone $datamailobj;
		$dataobject->subject = $this->replace_strings($datamailobj, $datamailobj->subject, $emailtype);
		$dataobject->emailbody = $this->replace_strings($datamailobj, $datamailobj->body, $emailtype);
		$dataobject->body = $dataobject->emailbody;
		$dataobject->from_emailid = $fromuser->email;
		$dataobject->from_userid = $fromuser->id;
		$dataobject->ccto = 0;
        $dataobject->to_emailid = $user->email;
        $dataobject->to_userid = $user->id;
        $dataobject->sentdate = 0;
        $dataobject->sent_by = $this->user->id;
        $dataobject->moduleid = $datamailobj->courseid ? $datamailobj->courseid : 0;
        $dataobject->courseid = $datamailobj->courseid ? $datamailobj->courseid : 0;
        
        if($logid = $this->check_pending_mail_exists($user, $fromuser, $datamailobj)){
        	$dataobject->id = $logid;
        	$dataobject->timemodified = time();
            $dataobject->usermodified = $this->user->id;
			$logid = $this->db->update_record('local_emaillogs', $dataobject);
        }else{
        	$dataobject->timecreated = time();
        	$dataobject->usercreated = $this->user->id;
        	$this->db->insert_record('local_emaillogs', $dataobject);
        }

	}
	public function check_pending_mail_exists($user, $fromuser, $datamailobj){
		$sql =  " SELECT id FROM {local_emaillogs} WHERE to_userid = :userid AND notification_infoid = :infoid AND from_userid = :fromuserid AND subject = :subject AND status = 0";
		$params['userid'] = $datamailobj->touserid;
		$params['fromuserid'] = $datamailobj->fromuserid;
		$params['subject'] = $datamailobj->subject;
		$params['infoid'] = $datamailobj->notification_infoid;
        // if($datamailobj->courseid){
        //     $sql .= " AND moduleid=:courseid";
        //     $params['courseid'] = $datamailobj->courseid;
        // }
        // if($datamailobj->teammemberid){
        //     $sql .= " AND teammemberid=:teammemberid";
        //     $params['teammemberid'] = $datamailobj->teammemberid;
        // }
		return $this->db->get_field_sql($sql ,$params);
	}
	public function replace_strings($dataobject, $data, $emailtype){       
        // $local_notification = new \notifications();
        $strings = $this->get_string_identifiers($emailtype);
        $strings = explode(',', $strings);
        if($strings){
            foreach($strings as $string){
                $string = trim($string);
                foreach($dataobject as $key => $dataval){
                    $key = '['.$key.']';
                    if("$string" == "$key"){
                        $data = str_replace("$string", "$dataval", $data);
                    }
                }
            }
        }
        // print_object($data);
        return $data;
    }
}