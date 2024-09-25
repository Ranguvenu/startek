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
 * Learning plan webservice functions.
 *
 *
 * @package    local_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    // Learning plan related functions.
    'local_competency_data_for_competency_frameworks_manage_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_competency_frameworks_manage_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the competency frameworks manage page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
    ),
    'local_competency_data_for_competency_summary' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_competency_summary',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load competency data for summary template.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
    ),
    'local_competency_data_for_competencies_manage_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_competencies_manage_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the competencies manage page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
    ),

    'local_competency_data_for_view_competencies_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_view_competencies_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the competencies manage page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
    ),
    'local_competency_list_courses_using_competency' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'list_courses_using_competency',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'List the courses using a competency',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:coursecompetencyview',
        'ajax'         => true,
    ),
    'local_competency_data_for_course_competencies_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_course_competencies_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the course competencies page template.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:coursecompetencyview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_competency_data_for_template_competencies_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_template_competencies_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the template competencies page template.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:templateview',
        'ajax'         => true,
    ),
    'local_competency_data_for_templates_manage_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_templates_manage_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the learning plan templates manage page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:templateview',
        'ajax'         => true,
    ),
    'local_competency_data_for_plans_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_plans_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the plans page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:planviewown',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_competency_data_for_plan_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_plan_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the plan page template.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:planview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_competency_data_for_related_competencies_section' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_related_competencies_section',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the related competencies template.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
    ),

    'local_competency_search_users' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'search_users',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Search for users.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ),
    // This function was originally in this plugin but has been moved to core.
    'local_competency_search_cohorts' => array(
        'classname'    => 'core_cohort_external',
        'methodname'   => 'search_cohorts',
        'classpath'    => 'cohort/externallib.php',
        'description'  => 'Search for cohorts.',
        'type'         => 'read',
        'capabilities' => 'moodle/cohort:view',
        'ajax'         => true,
    ),

    // User evidence.
    'local_competency_data_for_user_evidence_list_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_user_evidence_list_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the user evidence list page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:userevidenceview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_competency_data_for_user_evidence_page' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_user_evidence_page',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the user evidence page template',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:userevidenceview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    // User competency.
    'local_competency_data_for_user_competency_summary' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_user_competency_summary',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load a summary of a user competency.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:planview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_competency_data_for_user_competency_summary_in_plan' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_user_competency_summary_in_plan',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load a summary of a user competency.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:planview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'local_competency_data_for_user_competency_summary_in_course' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_user_competency_summary_in_course',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load a summary of a user competency.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:coursecompetencyview',
        'ajax'         => true,
        'services'     => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    // added by hema
      'local_competency_data_for_user_competency_brief' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_user_competency_brief',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the related competencies template.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
     
    ),

    'local_competency_data_for_advancedview_of_usercompetency' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_advancedview_of_usercompetency',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load the data for the all activities of course and its assigned activity.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
     
    ),
    'local_competency_data_for_advancedview_of_courselist' => array(
        'classname'    => 'local_competency\external',
        'methodname'   => 'data_for_advancedview_of_courselist',
        'classpath'    => 'local/competency/classes/external.php',
        'description'  => 'Load course of competency.',
        'type'         => 'read',
        'capabilities' => 'moodle/competency:competencyview',
        'ajax'         => true,
     
    ),
      
);

