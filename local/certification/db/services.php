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
 * @package Bizlms 
 * @subpackage local_certification
 */
defined('MOODLE_INTERNAL') || die;
$functions = array(
    'local_certification_submit_instance' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'certification_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_deletecertification' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'delete_certification_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_form_course_selector' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'certification_course_selector',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_certification_deletesession' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'delete_session_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_deleteuser' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'delete_certificationuser_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'unenrolling users from certification',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_deletecertificationevaluation' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'delete_certificationevaluation_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write',
    ),
    'local_certification_form_option_selector' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'certification_form_option_selector',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'read',
    ),
    'local_certification_session_submit_instance' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'certification_session_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_course_submit_instance' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'certification_course_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_deletecertificationcourse' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'delete_certificationcourse_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write',
    ),
    // 'local_certification_createcategory' => array(
    //     'classname' => 'local_certification_external',
    //     'methodname' => 'createcategory_instance',
    //     'classpath' => 'local/certification/externallib.php',
    //     // 'description' => 'All class room forms event handling',
    //     'ajax' => true,
    //     'type' => 'write',
    // ),
    'local_certification_completion_settings_submit_instance' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'certification_completion_settings_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
     'local_certification_managecertificationStatus' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'managecertificationStatus_instance',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'All class room forms event handling',
        'ajax' => true,
        'type' => 'write'
    ),
    'local_certification_submit_elements_form' => array(
        'classname'   => 'local_certification\external',
        'methodname'  => 'submit_elements_form',
        'classpath'   => 'local/certification/classes/external.php',
        //'description' => 'Saves data for an element',
        'type'        => 'write',
        'ajax'        => true
    ),
    'local_certification_save_element' => array(
        'classname'   => 'local_certification\external',
        'methodname'  => 'save_element',
        'classpath'   => '',
        'description' => 'Saves data for an element',
        'type'        => 'write',
        'ajax'        => true
    ),
    'local_certification_get_element_html' => array(
        'classname'   => 'local_certification\external',
        'methodname'  => 'get_element_html',
        'classpath'   => '',
        'description' => 'Returns the HTML to display for an element',
        'type'        => 'read',
        'ajax'        => true
    ),
    'local_certification_get_mobile_certifications' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'get_certification_courses',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'Get user Certifactes',
        'ajax' => true,
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_certification_get_certification_sessions' => array(
        'classname' => 'local_certification_external',
        'methodname' => 'get_certification_sessions',
        'classpath' => 'local/certification/externallib.php',
        'description' => 'get sessions list',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_certification_userdashboard_content' => array(
        'classname'    => 'local_certification_external',
        'methodname'   => 'data_for_certifications',
        'classpath'    => 'local/certification/externallib.php',
        'description'  => 'Load the data for the certification courses.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ),
    'local_certification_userdashboard_content_paginated' => array(
        'classname'    => 'local_certification_external',
        'methodname'   => 'data_for_certifications_paginated',
        'classpath'    => 'local/certification/externallib.php',
        'description'  => 'Load the data for the certification courses.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    )
);