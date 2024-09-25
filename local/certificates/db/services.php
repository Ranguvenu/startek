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
 * @subpackage local_certificates
 */

// We defined the web service functions to install.

defined('MOODLE_INTERNAL') || die;
$functions = array(
	'local_certificates_submit_certificate_form' => array(
		'classname' => 'local_certificates_external',
		'methodname' => 'submit_certificate_form',
		'classpath' => 'local/certificates/classes/external.php',
		'description' => 'Submit form',
		'type' => 'write',
		'ajax' => true,
	),
	'local_certificates_get_element_html' => array(
        'classname'   => 'local_certificates_external',
        'methodname'  => 'get_element_html',
        'classpath'   => 'local/certificates/classes/external.php',
        'description' => 'Returns the HTML to display for an element',
        'type'        => 'read',
        'ajax'        => true
    ),
    'local_certificates_save_element' => array(
        'classname'   => 'local_certificates_external',
        'methodname'  => 'save_element',
        'classpath'   => 'local/certificates/classes/external.php',
        'description' => 'Saves data for an element',
        'type'        => 'write',
        'ajax'        => true
    ),
    'local_certificates_delete_certificate' => array(
        'classname'   => 'local_certificates_external',
        'methodname'  => 'delete_certificate',
        'classpath'   => 'local/certificates/classes/external.php',
        'description' => 'Delete certificate',
        'type'        => 'write',
        'ajax'        => true
    ),
);
