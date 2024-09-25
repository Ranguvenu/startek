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
 * @package   Bizlms
 * @subpackage  challenge
 * @author eabyas  <info@eabyas.in>
**/
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/challenge/lib.php');
class local_challenge_observer{
	public static function course_completed(\core\event\course_completed $event){
		global $DB, $USER;
		$userid = $event->relateduserid;
		$moduleid = $event->courseid;
		$challenge_sql = "SELECT lc.id FROM {local_challenge} AS lc 
			WHERE lc.userid_to = :userid AND lc.module_id = :moduleid AND module_type LIKE 'local_courses' AND status = :chalenge_status "; // AND lc.complete_by < time() 
		$challenges = $DB->get_fieldset_sql($challenge_sql, array('userid' => $userid, 'moduleid' => $moduleid, 'chalenge_status' => CHALLENGE_ACTIVE));
		// $notification = new \local_challenge\notification();
		$challenge_lib = new \local_challenge\local\lib();
		foreach($challenges AS $challenge){
			$challenge_lib->challenge_alter_status($challenge, CHALLENGE_COMPLETED);
			// $update_data = new \stdClass();
			// $update_data->id = $challenge;
			// $update_data->timecompleted = time();
			// $update_data->status = CHALLENGE_COMPLETED;
			// $DB->update_record('local_challenge', $update_data);
			// $emaildata = new \stdClass();
			// $emaildata->module_type = 'local_courses';
			// $emaildata->challengedata = $challenge;
			// $emaildata->notificationtype = 'challenge_complete_win';
			// $emaildata->touserid = $userid;
			// $emaildata->fromuserid = $challenge->userid_from;
			// $notification->send_challenge_email($emaildata);

			// $emaildata->notificationtype = 'challenge_complete_lose';
			// $emaildata->touserid = $challenge->userid_from;
			// $emaildata->fromuserid = $userid;
			// $notification->send_challenge_email($emaildata);
		}
	}
	public static function learningplan_user_completed(\local_learningplan\event\learningplan_user_completed $event){
		global $DB, $USER;
		$userid = $event->relateduserid;
		$moduleid = $event->objectid;
		$challenge_sql = "SELECT lc.id FROM {local_challenge} AS lc 
			WHERE lc.userid_to = :userid AND lc.module_id = :moduleid AND module_type LIKE 'local_learningplan' AND status = :chalenge_status "; // AND lc.complete_by < time() 
		$challenges = $DB->get_fieldset_sql($challenge_sql, array('userid' => $userid, 'moduleid' => $moduleid, 'chalenge_status' => CHALLENGE_ACTIVE));
		// $notification = new \local_challenge\notification();
		$challenge_lib = new \local_challenge\local\lib();
		foreach($challenges AS $challenge){
			$challenge_lib->challenge_alter_status($challenge, CHALLENGE_COMPLETED);
			// $update_data = new \stdClass();
			// $update_data->id = $challenge->id;
			// $update_data->timecompleted = time();
			// $update_data->status = CHALLENGE_COMPLETED;
			// $DB->update_record('local_challenge', $update_data);

			// $emaildata = new \stdClass();
			// $emaildata->module_type = 'local_learningplan';
			// $emaildata->challengedata = $challenge;
			// $emaildata->notificationtype = 'challenge_complete_win';
			// $emaildata->touserid = $userid;
			// $emaildata->fromuserid = $challenge->userid_from;
			// $notification->send_challenge_email($emaildata);

			// $emaildata->notificationtype = 'challenge_complete_lose';
			// $emaildata->touserid = $challenge->userid_from;
			// $emaildata->fromuserid = $userid;
			// $notification->send_challenge_email($emaildata);
		}
	}
	public static function program_user_completed(\local_program\event\program_user_completed $event){
		global $DB, $USER;
		$userid = $event->relateduserid;
		$moduleid = $event->objectid;
		$challenge_sql = "SELECT lc.id FROM {local_challenge} AS lc 
			WHERE lc.userid_to = :userid AND lc.module_id = :moduleid AND module_type LIKE 'local_program' AND status = :chalenge_status "; // AND lc.complete_by < time() 
		$challenges = $DB->get_fieldset_sql($challenge_sql, array('userid' => $userid, 'moduleid' => $moduleid, 'chalenge_status' => CHALLENGE_ACTIVE));
		// $notification = new \local_challenge\notification();
		$challenge_lib = new \local_challenge\local\lib();
		foreach($challenges AS $challenge){
			$challenge_lib->challenge_alter_status($challenge, CHALLENGE_COMPLETED);
			// $update_data = new \stdClass();
			// $update_data->id = $challenge->id;
			// $update_data->timecompleted = time();
			// $update_data->status = CHALLENGE_COMPLETED;
			// $DB->update_record('local_challenge', $update_data);

			// $emaildata = new \stdClass();
			// $emaildata->module_type = 'local_program';
			// $emaildata->challengedata = $challenge;
			// $emaildata->notificationtype = 'challenge_complete_win';
			// $emaildata->touserid = $userid;
			// $emaildata->fromuserid = $challenge->userid_from;
			// $notification->send_challenge_email($emaildata);

			// $emaildata->notificationtype = 'challenge_complete_lose';
			// $emaildata->touserid = $challenge->userid_from;
			// $emaildata->fromuserid = $userid;
			// $notification->send_challenge_email($emaildata);
		}
	}
}