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
 * local custom_matrix
 *
 * @package    local_custom_matrix
 * @copyright  2019 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$observers = array(  
    array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'local_custom_matrix_observer::user_course_performance',
    ), array(
        'eventname'   => '\local\classroom\event\classroom_user_completed',
        'callback'    => 'local_custom_matrix_observer::user_classroom_performance',
    ), array(
        'eventname'   => '\local\learningplan\event\learningplan_user_completed',
        'callback'    => 'local_custom_matrix_observer::user_learningplan_performance',
    ), array(
        'eventname'   => '\core\event\course_deleted',
        'callback'    => 'local_custom_matrix_observer::user_course_delete_performance'
    )
);