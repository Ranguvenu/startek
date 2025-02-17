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
 * Definition of Schedule Reports scheduled tasks.
 *
 * @package   learnerscript
 * @category  task
 * @copyright 2017 Arun Kumar
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* List of handlers */

$tasks = array(
    array(
        'classname' => 'block_learnerscript\task\schedule_reports',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\send_emails',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\learnerscript',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '2',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\userscormtimespent',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),  
    array(
        'classname' => 'block_learnerscript\task\coursetimepsent',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\userquiztimespent',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\learningformats',
        'blocking' => 0,
        'minute' => '55',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\certificatesinfo',
        'blocking' => 0,
        'minute' => '5',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ),
    array(
        'classname' => 'block_learnerscript\task\examsinformation',
        'blocking' => 0,
        'minute' => '35',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    )
);
