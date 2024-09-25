<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
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
 * The quick_navigation block
 *
 * @package    block_leaderboard
 * @copyright 2023 Hemanth <hemanth@eabyas.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
// require_once($CFG->dirroot.'/blocks/leaderboard/lib.php');
$id = optional_param('id', SITEID, PARAM_INT); // Course id
global $PAGE, $OUTPUT, $DB, $CFG,$USER;
require_login();
$context = context_course::instance($id);
$PAGE->set_context($context);
// $course = get_course($id);
// $PAGE->set_course($course);
$PAGE->set_pagelayout('gamification');

$PAGE->set_url('/blocks/gamification/dashboard.php');
$PAGE->set_title(get_string('pluginname', 'block_gamification'));
$PAGE->set_heading($course->fullname);

$renderer = $PAGE->get_renderer('block_leaderboard');


echo $OUTPUT->header();

echo $OUTPUT->footer();
