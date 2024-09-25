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
namespace local_certification\task;

use core\task;
use local_certification\certification;
class send_certification_reminders extends \core\task\scheduled_task {
	/**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('send_certification_reminders', 'local_certification');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG, $DB;
        $notificationclass = new \local_certification\notification();
        $emailtype = "certification_reminder";
        $modulenotifications = $this->module_due_notifications($emailtype);
        $modules = array(); 
        $certificationsarray = array();
        $moduletousers = array();
        $fromuser = \core_user::get_support_user();
        foreach($modulenotifications AS $notification){
        	$starttime = strtotime(date('d/m/Y', strtotime("+".$notification->reminderdays." day")));
        	$endtime = $starttime+86399;

            list($relatedmoduleidsql, $relatedmoduleidparams) = $DB->get_in_or_equal(explode(',',$notification->moduleid), SQL_PARAMS_NAMED, 'moduleids');
        	$certificationsql = "SELECT lcu.* 
        		FROM {local_certification_users} AS lcu 
        		JOIN {local_certification} AS lc ON lc.id=lcu.certificationid 
        		JOIN {user} AS u ON lcu.userid=u.id AND u.deleted = 0 AND u.suspended = 0
        		WHERE lc.id $relatedmoduleidsql AND lc.startdate BETWEEN :timestart AND :timeend ";
    		$params = array('timestart' => $starttime, 'timeend' => $endtime);
            $params = array_merge($params,$relatedmoduleidparams);
    		$certificationusers = $DB->get_records_sql($certificationsql, $params);
    		foreach($certificationusers AS $user){
    			if(isset($certificationsarray[$user->certificationid]) && !empty($certificationsarray[$user->certificationid])){
    				$certificationinstance = clone $certificationsarray[$user->certificationid];
    			}else{
					$certificationinstance = $DB->get_record('local_certification', array('id' => $user->certificationid));
					$certificationsarray[$user->certificationid] = $certificationinstance;
    			}
    			if(isset($moduletousers[$user->userid]) && !empty($moduletousers[$user->userid])){
    				$touser = clone $moduletousers[$user->userid];
    			}else{
					$moduletousers[$user->userid] = $touser = \core_user::get_user($user->userid);
    			}
				$notificationclass->send_certification_notification($certificationinstance, $touser, $fromuser, $emailtype, $notification);
			}
			$modules[] = $notification->moduleid;
        }
        $globalduenotifications = $this->global_due_notifications($emailtype);
    	$moduleids = implode(',', $modules);
    	foreach($globalduenotifications AS $notification){
        	$starttime = strtotime(date('d/m/Y', strtotime("+".$notification->reminderdays." day")));
        	$endtime = $starttime+86399;

            list($relatedmoduleidsql, $relatedmoduleidparams) = $DB->get_in_or_equal(explode(',',$moduleids), SQL_PARAMS_NAMED, 'moduleids',false);

        	/*$certificationsql = "SELECT lcu.*, u.id as userid 
        		FROM {local_certification_users} AS lcu 
        		JOIN {local_certification} AS lc ON lc.id=lcu.certificationid 
        		JOIN {user} AS u ON lcu.userid=u.id AND u.deleted = 0 AND u.suspended = 0
        		WHERE lc.id NOT IN (:moduleid) AND lc.startdate BETWEEN :timestart AND :timeend ";*/
            $certificationsql = "SELECT lcu.*, u.id as userid 
                FROM {local_certification_users} AS lcu 
                JOIN {local_certification} AS lc ON lc.id=lcu.certificationid 
                JOIN {user} AS u ON lcu.userid=u.id AND u.deleted = 0 AND u.suspended = 0
                WHERE lc.id $relatedmoduleidsql AND lc.startdate BETWEEN :timestart AND :timeend ";
    		$params = array('timestart' => $starttime, 'timeend' => $endtime);
            $params = array_merge($params,$relatedmoduleidparams);

    		$certificationusers = $DB->get_records_sql($certificationsql, $params);
    		foreach($certificationusers AS $user){
    			if(isset($certificationsarray[$user->certificationid]) && !empty($certificationsarray[$user->certificationid])){
    				$certificationinstance = clone $certificationsarray[$user->certificationid];
    			}else{
					$certificationinstance = $DB->get_record('local_certification', array('id' => $user->certificationid));
					$certificationsarray[$user->certificationid] = $certificationinstance;
    			}
				if(isset($moduletousers[$user->userid]) && !empty($moduletousers[$user->userid])){
    				$touser = clone $moduletousers[$user->userid];
    			}else{
					$moduletousers[$user->userid] = $touser = \core_user::get_user($user->userid);
    			}
				$notificationclass->send_certification_notification($certificationinstance, $touser, $fromuser, $emailtype, $notification);
			}
        }
    }
    private function module_due_notifications($emailtype){
    	global $DB;
    	$modulenotification_sql = "SELECT lni.* FROM {local_notification_info} AS lni 
    		WHERE (lni.moduleid!=0 OR lni.moduleid IS NOT NULL) AND lni.notificationid=(SELECT id FROM {local_notification_type} WHERE shortname=:shortname)"; 
    	$notifications = $DB->get_records_sql($modulenotification_sql, array('shortname' => $emailtype));
    	return $notifications;
    }
    private function global_due_notifications($emailtype){
    	global $DB;
    	$globalnotification_sql = "SELECT lni.* FROM {local_notification_info} AS lni 
    		WHERE (lni.moduleid=0 OR lni.moduleid IS NULL) AND lni.notificationid=(SELECT id FROM {local_notification_type} WHERE shortname=:shortname)"; 
    	$notifications = $DB->get_records_sql($globalnotification_sql, array('shortname' => $emailtype));
    	return $notifications;
    }
}