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
namespace local_challenge\task;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/challenge/lib.php');
class incomplete_challenge_trigger extends \core\task\scheduled_task{
	public function get_name() {
        return get_string('task_incomplete_challenge_trigger', 'local_challenge');
    }
    public function execute(){
    	global $DB;
    	$expired_challenges_sql = "SELECT lc.id FROM {local_challenge} AS lc
    		WHERE lc.status = :challenge_status AND lc.complete_by > 0 AND lc.complete_by < :currenttime ";
		$expired_challenges = $DB->get_fieldset_sql($expired_challenges_sql, array('challenge_status' => CHALLENGE_ACTIVE, 'currenttime' => time()));
		// $notification = new \local_challenge\notification();
		$challenge_lib = new \local_challenge\local\lib();
		foreach($expired_challenges AS $challenge){
			$challenge_lib->challenge_alter_status($challenge, CHALLENGE_INCOMPLETE);
			// $update_data = new \stdClass();
			// $update_data->id = $challenge->id;
			// $update_data->timemodified = time();
			// $update_data->status = CHALLENGE_INCOMPLETE;
			// $DB->update_record('local_challenge', $update_data);

			// $emaildata = new \stdClass();
			// $emaildata->module_type = $challenge->module_type;
			// $emaildata->challengedata = $challenge;
			// $emaildata->notificationtype = 'challenge_incomplete';
			// $emaildata->touserid = $challenge->userid_to;
			// $emaildata->fromuserid = $challenge->userid_from;
			// $notification->send_challenge_email($emaildata);

			// $emaildata->fromuserid = $challenge->userid_to;
			// $emaildata->touserid = $challenge->userid_from;
			// $notification->send_challenge_email($emaildata);
		}
    }
}