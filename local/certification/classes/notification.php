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
 * @subpackage local_certification
 */
namespace local_certification;
class notification{
	public $db;
	public $user;
	public function __construct($db=null, $user=null){
		global $DB, $USER;
		$this->db = $db ? $db :$DB;
		$this->user = $user ? $user :$USER;
	}
    public function get_notification_strings($emailtype){
        switch($emailtype){
            case 'certification_enrol':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],[certification_link],
                            [certification_enroluseremail],
                            [certification_location_fullname],
                            [certification_Addresscertificationlocation],
                            [certification_certificationsummarydescription],
                            [certification_certification_image]";
                break;
            
            case 'certification_unenroll':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],[certification_link],
                            [certification_enroluseremail],
                            [certification_location_fullname],
                            [certification_Addresscertificationlocation],
                            [certification_certificationsummarydescription],
                            [certification_certification_image]";
                break;
            
            case 'certification_invitation':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],
                            [certification_enroluseremail],[certification_link],
                            [certification_location_fullname],
                            [certification_Addresscertificationlocation],
                            [certification_certificationsummarydescription],
                            [certification_certification_image]";
                break;
            
            case 'certification_hold':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],
                            [certification_enroluseremail],[certification_link]";
                break;
            
            case 'certification_cancel':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],
                            [certification_enroluseremail],[certification_link]";
                break;
            
            case 'certification_complete':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],
                            [certification_enroluseremail], [certification_completiondate],[certification_link]";
                break;
            case 'certification_reminder':
                $strings = "[certification_name], [certification_course], [certification_startdate],
                            [certification_enddate], [certification_creater], [certification_department], [certification_sessionsinfo], [certification_enroluserfulname],
                            [certification_enroluseremail],[certification_link],
                            [certification_location_fullname],
                            [certification_Addresscertificationlocation],
                            [certification_certificationsummarydescription],
                            [certification_certification_image]";   
                break;
        }
    }
	public function certification_notification($emailtype, $touser, $fromuser, $certificationinstance){
        if($notification = $this->get_existing_notification($certificationinstance, $emailtype)){
            $this->send_certification_notification($certificationinstance, $touser, $fromuser, $emailtype, $notification);
        }
    }
    public function get_existing_notification($certificationinstance, $emailtype){
    	$corecomponent = new \core_component();
        $costcenterexist = $corecomponent::get_plugin_directory('local','costcenter');
        $params = array();
        $notification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni 
            JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid
            WHERE concat(',',lni.moduleid,',') LIKE concat('%,',:moduleid,',%') AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
        $params['moduleid'] = $certificationinstance->id;
        $params['emailtype'] = $emailtype;
        if($costcenterexist){
            $notification_typesql .= " AND lni.costcenterid=:costcenter";
            $params['costcenter'] = $certificationinstance->costcenter;
        }
        // echo $notification_typesql;
        // print_object($params);
        $notification = $this->db->get_record_sql($notification_typesql, $params);
        // print_object($notification);
        if(empty($notification)){ // sends the default notification for the type.
            $params = array();
            $notification_typesql = "SELECT lni.* FROM {local_notification_info} AS lni 
                JOIN {local_notification_type} AS lnt ON lnt.id=lni.notificationid 
                WHERE (lni.moduleid IS NULL OR lni.moduleid LIKE '0') 
                AND lnt.shortname LIKE :emailtype AND lni.active=1 ";
            $params['emailtype'] = $emailtype;
            if($costcenterexist){
                $notification_typesql .= " AND lni.costcenterid=:costcenter";
                $params['costcenter'] = $certificationinstance->costcenter;
            }
            // echo "<br/>sss".$notification_typesql;
            // print_object($params);
            $notification = $this->db->get_record_sql($notification_typesql, $params);
             // print_object($notification);
        }
        if(empty($notification)){
            return false;
        }else{
            return $notification;
        }
    }
    public function send_certification_notification($certificationinstance, $touser, $fromuser, $emailtype, $notification){
        global $PAGE;
        $certificationcourses_sql = "SELECT lcc.courseid, c.fullname 
            FROM {local_certification_courses} lcc
            JOIN {course} c ON lcc.courseid = c.id
            WHERE lcc.certificationid = {$certificationinstance->id} ";
        $creater = \core_user::get_user($certificationinstance->usercreated);
        $certificationcourses =  $this->db->get_records_sql_menu($certificationcourses_sql); 
        $datamailobject = new \stdClass();
        $datamailobject->certification_name = $certificationinstance->name;
        //$datamailobject->certification_course = implode(', ', $certificationcourses).'.';
        $datamailobject->certification_course = $certificationcourses ? implode(', ', $certificationcourses): 'N/A';
        $datamailobject->certification_startdate = $certificationinstance->startdate ? \local_costcenter\lib::get_userdate("d/m/Y H:i",$certificationinstance->startdate): 'N/A';
        $datamailobject->certification_enddate = $certificationinstance->enddate ? \local_costcenter\lib::get_userdate("d/m/Y H:i",$certificationinstance->enddate) : 'N/A';
        $datamailobject->certification_creater = fullname($creater);
        $datamailobject->notification_infoid = $notification->id;
        $corecomponent = new \core_component();
        $costcenterexist = $corecomponent::get_plugin_directory('local','costcenter');
        if($costcenterexist && !empty($certificationinstance->department)){
            $datamailobject->certification_department = $this->db->get_field('local_costcenter', 'fullname', array('id' => $certificationinstance->department));
        }
        $renderer = $PAGE->get_renderer('local_certification');
        $datamailobject->certification_sessionsinfo = $renderer->view_certification_sessions($certificationinstance->id);
        $datamailobject->certification_asigned_rolename = $this->get_rolename_incertification($certificationinstance, $touserid);
        $datamailobject->certification_enroluserfulname = fullname($touser);
        $datamailobject->certification_enroluseremail = $touser->email;
        $url = new \moodle_url($CFG->wwwroot.'/local/certification/view.php?ctid='.$certificationinstance->id);
        $datamailobject->certification_link = '<a href='.$url.'>'.$url.'</a>';
        $additionrequirements = array('certification_reminder', 'certification_invitation', 
            'certification_unenroll', 'certification_enrol');
        if(in_array($emailtype, $additionrequirements)){
           $institutes = $this->db->get_record_sql("SELECT li.fullname, li.address
                        FROM {local_certification} lc
                        JOIN {local_location_institutes} li ON lc.instituteid = li.id
                        WHERE lc.id = {$certificationinstance->id}");
            $datamailobject->certification_location_fullname = $institutes->fullname ? $institutes->fullname : 'N/A';
            $datamailobject->certification_Addresscertificationlocation = $institutes->address ? $institutes->address : 'N/A';
            $datamailobject->certification_certificationsummarydescription = $certificationinstance->description;

             $certificationinclude = new \local_certification\includes();
             $classesimg = $certificationinclude->get_certification_summary_file($certificationinstance);
             $datamailobject->certification_certification_image = \html_writer::img($classesimg, $certificationinstance->name,array());
        }
        $completiondatestring = array('certification_complete');
        if(in_array($emailtype, $completiondatestring)){
            $completiondate = $this->db->get_field('local_certification_users', 'completiondate', array('userid' => $touser->id, 'certificationid' => $certificationinstance->id));
            $datamailobject->certification_completiondate = $completiondate ? \local_costcenter\lib::get_userdate("d/m/Y H:i", $completiondate) : 'N/A';
        }
        $datamailobject->body = $notification->body;
        $datamailobject->subject = $notification->subject;
        $datamailobject->certificationid = $certificationinstance->id;
        $datamailobject->touserid = $touser->id;
        // $fromuser = \core_user::get_support_user();
        $datamailobject->fromuserid = $fromuser->id;
        $datamailobject->teammemberid = 0;
        if(!empty($notification->adminbody) && !empty($touser->open_supervisorid)){
            $superuser = \core_user::get_user($touser->open_supervisorid);
        }else{
            $superuser = false;
        }
        // if(class_exists('\notifications')){
        //     $notifications_lib = new \notifications();
        //     $notifications_lib->send_email_notification($emailtype, $datamailobj, $touser->id, $fromuser->id);
        //     if($superuser){
        //         $datamailobj->body = null;
        //         $datamailobj->adminbody = $notification->adminbody;
        //         $notifications_lib->send_email_notification($emailtype, $datamailobj, $superuser->id, $fromuser->id);
        //     }
        // }else{
            $this->log_email_notification($touser, $fromuser, $datamailobject);
            if($superuser){
                $datamailobject->body = $notification->adminbody;
                $datamailobject->touserid = $superuser->id;
                $datamailobject->teammemberid = $touser->id;
                $this->log_email_notification($superuser, $fromuser, $datamailobject);
            }
        // }
    }
    public function log_email_notification($touser, $fromuser, $datamailobj){
        $dataobject = clone $datamailobj;
        $dataobject->subject = $this->replace_strings($datamailobj, $datamailobj->subject);
        $dataobject->emailbody = $this->replace_strings($datamailobj, $datamailobj->body);
        $dataobject->from_emailid = $fromuser->email;
        $dataobject->from_userid = $fromuser->id;
        $dataobject->to_emailid = $touser->email;
        $dataobject->to_userid = $touser->id;
        $dataobject->ccto = 0;
        $dataobject->sentdate = 0;
        $dataobject->sent_by = $fromuser->id;
        $dataobject->moduleid = $datamailobj->certificationid;
        
        if($logid = $this->check_pending_mail_exists($touser, $fromuser, $datamailobj)){
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
        $sql =  " SELECT id FROM {local_emaillogs} WHERE to_userid = :userid AND notification_infoid = :infoid AND from_userid = :fromuserid AND subject = :subject AND status = 0 ";
        $params['userid'] = $datamailobj->touserid;
        $params['fromuserid'] = $datamailobj->fromuserid;
        $params['infoid'] = $datamailobj->notification_infoid;
        $params['subject'] = $datamailobj->subject;
        if($datamailobj->certificationid){
            $sql .= " AND moduleid=:certificationid";
            $params['certificationid'] = $datamailobj->certificationid;
        }
        if($datamailobj->teammemberid){
            $sql .= " AND teammemberid=:teammemberid";
            $params['teammemberid'] = $datamailobj->teammemberid;
        }
        return $this->db->get_field_sql($sql ,$params);
    }
    public function replace_strings($dataobject, $data){       
        $strings = $this->db->get_records('local_notification_strings', array('module' => 'certification'));
        if($strings){
            foreach($strings as $string){
                foreach($dataobject as $key => $dataval){
                    $key = '['.$key.']';
                    if("$string->name" == "$key"){
                        $data = str_replace("$string->name", "$dataval", $data);
                    }
                }
            }
        }
        return $data;
    }
    public function get_rolename_incertification($certificationinstance, $touserid){
        $istrainer = $this->db->record_exists('local_certification_trainers', array('certificationid' => $certificationinstance->id, 'trainerid' => $touser->id));
        if(!$istrainer){
            return get_string('employeerolestring', 'local_certification');
        }else{
            return get_string('trainerrolestring', 'local_certification');
        }
    }
}