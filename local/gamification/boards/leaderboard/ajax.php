<?php
// This file is part of the gamification localule for Moodle - http://moodle.org/
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

// define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_login();
global $PAGE;
$PAGE->set_context(context_system::instance());
$eventname = required_param('eventname',PARAM_TEXT);
if($eventname == 'course'){
	$courseid = required_param('course',PARAM_INT);
}else{
	$courseid = 1;
}

$leaderboardclass = new gamificationboards_leaderboard\view();
$leaderboardcontent = $leaderboardclass->view_content($eventname,$courseid);

echo $leaderboardcontent;