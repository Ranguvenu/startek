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
 * Comments block caps.
 *
 * @package    block_comments
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL')|| die();
$capabilities=array(
    'local/location:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:viewinstitute' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:createinstitute' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:editinstitute' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:deleteinstitute' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:manageinstitute' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:createroom' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:viewroom' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,

    ),
    'local/location:editroom' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:deleteroom' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
    ),
    'local/location:manageroom' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
    ),

);
