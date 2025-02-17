<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * gamification learningplan element install code.
 *
 * @package    gamificationelement_learningplan
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_gamificationboards_learningplan_install(){
	global $CFG,$DB;
	$data = new stdClass();
	$data->event_name = 'Each learningplan completion';
	$data->event = '/local_learningplan/event/learningplan_completed';
	$data->shortname = 'learningplan_completions';
	$data->eventcode = 'lpc';
	$data->active = '1';
	$data->badgeactive = '1';
	$data->timecreated = time();
	$data->timemodified = time();
	$data->usermodified = '2';
	$dbman = $DB->get_manager();
	if ($dbman->table_exists('local_learningplan'))
		$DB->insert_record('block_gm_events',$data);
}