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
 * @package BizLMS
 * @subpackage local_request
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
	'local_request_manage_view' => array(
		'classname'   => 'local_request_external',
        'methodname'  => 'view_availiable_request',
        'classpath'   => 'local/request/classes/external.php',
        'description' => 'Get the request data',
        'type'        => 'read',
        'ajax' => true,
	),
    'local_request_enrol_component' => array(
        'classname' => 'local_request_external',
        'methodname' => 'enrol_component',
        'classpath' => 'local/request/classes/external.php',
        'description' => 'Enrol to component',
        'type' => 'write',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);