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
 * This is the external API for this tool.
 *
 * @package    local_competency
 * @copyright  2018 hemalatha c arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/grade/grade_scale.php");

use context;
use context_system;
use context_course;
use context_helper;
use context_user;
use coding_exception;
use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;
use required_capability_exception;

use core_cohort\external\cohort_summary_exporter;
use local_competency\external\competency_path_exporter;
use local_competency\external\competency_summary_exporter;
use local_competency\external\course_competency_statistics_exporter;
use core_course\external\course_module_summary_exporter;
use core_course\external\course_summary_exporter;
use local_competency\external\template_statistics_exporter;
use local_competency\external\user_competency_summary_exporter;
use local_competency\external\user_competency_summary_in_course_exporter;
use local_competency\external\user_competency_summary_in_plan_exporter;
use local_competency\external\user_evidence_summary_exporter;
use local_competency\output\user_competency_summary_in_plan;
use local_competency\output\user_competency_summary_in_course;

use core_competency\api;
use core_competency\external\competency_exporter;
use core_competency\external\competency_framework_exporter;
use core_competency\external\course_competency_exporter;
use core_competency\external\course_competency_settings_exporter;
use core_competency\external\plan_exporter;
use core_competency\external\template_exporter;
use core_competency\external\user_competency_course_exporter;
use core_competency\external\user_competency_exporter;
use core_competency\external\user_competency_plan_exporter;
use core_user\external\user_summary_exporter;

/**
 * This is the external API for this tool.
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns a prepared structure to use a context parameters.
     * @return external_single_structure
     */
    protected static function get_context_parameters() {
        $id = new external_value(
            PARAM_INT,
            'Context ID. Either use this value, or level and instanceid.',
            VALUE_DEFAULT,
            0
        );
        $level = new external_value(
            PARAM_ALPHA,
            'Context level. To be used with instanceid.',
            VALUE_DEFAULT,
            ''
        );
        $instanceid = new external_value(
            PARAM_INT,
            'Context instance ID. To be used with level',
            VALUE_DEFAULT,
            0
        );
        return new external_single_structure(array(
            'contextid' => $id,
            'contextlevel' => $level,
            'instanceid' => $instanceid,
        ));
    }

    /**
     * Returns description of data_for_competency_frameworks_manage_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_competency_frameworks_manage_page_parameters() {
        $params = array('pagecontext' => self::get_context_parameters());
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the competency_frameworks_manage_page template.
     *
     * @param context $pagecontext The page context
     * @return \stdClass
     */
    public static function data_for_competency_frameworks_manage_page($pagecontext) {
        global $PAGE;

        $params = self::validate_parameters(
            self::data_for_competency_frameworks_manage_page_parameters(),
            array(
                'pagecontext' => $pagecontext
            )
        );
        $context = self::get_context_from_params($params['pagecontext']);
        self::validate_context($context);

        $renderable = new output\manage_competency_frameworks_page($context);
        $renderer = $PAGE->get_renderer('local_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_competency_frameworks_manage_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_competency_frameworks_manage_page_returns() {
        return new external_single_structure(array (
            'competencyframeworks' => new external_multiple_structure(
                competency_framework_exporter::get_read_structure()
            ),
            'pluginbaseurl' => new external_value(PARAM_LOCALURL, 'Url to the local_competency plugin folder on this Moodle site'),
            'navigation' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'HTML for a navigation item that should be on this page')
            ),
            'pagecontextid' => new external_value(PARAM_INT, 'The page context id')
        ));

    }

    /**
     * Returns description of data_for_competencies_manage_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_competencies_manage_page_parameters() {
        $competencyframeworkid = new external_value(
            PARAM_INT,
            'The competency framework id',
            VALUE_REQUIRED
        );
        $search = new external_value(
            PARAM_RAW,
            'A search string',
            VALUE_DEFAULT,
            ''
        );
        $params = array(
            'competencyframeworkid' => $competencyframeworkid,
            'search' => $search
        );
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the competencies_manage_page template.
     *
     * @param int $competencyframeworkid Framework id.
     * @param string $search Text to search.
     *
     * @return boolean
     */
    public static function data_for_competencies_manage_page($competencyframeworkid, $search) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_competencies_manage_page_parameters(), array(
            'competencyframeworkid' => $competencyframeworkid,
            'search' => $search
        ));

        $framework = api::read_framework($params['competencyframeworkid']);
        self::validate_context($framework->get_context());
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new output\manage_competencies_page($framework, $params['search'], $framework->get_context());

        $data = $renderable->export_for_template($output);

        return $data;
    }

    /**
     * Returns description of data_for_competencies_manage_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_competencies_manage_page_returns() {
        return new external_single_structure(array (
            'framework' => competency_framework_exporter::get_read_structure(),
            'canmanage' => new external_value(PARAM_BOOL, 'True if this user has permission to manage competency frameworks'),
            'pagecontextid' => new external_value(PARAM_INT, 'Context id for the framework'),
            'search' => new external_value(PARAM_RAW, 'Current search string'),
            'rulesmodules' => new external_value(PARAM_RAW, 'JSON encoded data for rules'),
            'pluginbaseurl' => new external_value(PARAM_RAW, 'Plugin base url')
        ));

    }

    /**
     * Returns description of data_for_competency_summary() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_competency_summary_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'The competency id',
            VALUE_REQUIRED
        );
        $includerelated = new external_value(
            PARAM_BOOL,
            'Include or not related competencies',
            VALUE_DEFAULT,
            false
        );
        $includecourses = new external_value(
            PARAM_BOOL,
            'Include or not competency courses',
            VALUE_DEFAULT,
            false
        );
        $params = array(
            'competencyid' => $competencyid,
            'includerelated' => $includerelated,
            'includecourses' => $includecourses
        );
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the competency_page template.
     *
     * @param int $competencyid Competency id.
     * @param boolean $includerelated Include or not related competencies.
     * @param boolean $includecourses Include or not competency courses.
     *
     * @return \stdClass
     */
    public static function data_for_competency_summary($competencyid, $includerelated = false, $includecourses = false) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_competency_summary_parameters(), array(
            'competencyid' => $competencyid,
            'includerelated' => $includerelated,
            'includecourses' => $includecourses
        ));

        $competency = api::read_competency($params['competencyid']);
        $framework = api::read_framework($competency->get_competencyframeworkid());
        self::validate_context($framework->get_context());
        $renderable = new output\competency_summary($competency, $framework, $params['includerelated'], $params['includecourses']);
        $renderer = $PAGE->get_renderer('local_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_competency_summary_() result value.
     *
     * @return \external_description
     */
    public static function data_for_competency_summary_returns() {
        return competency_summary_exporter::get_read_structure();
    }

    /**
     * Returns description of list_courses_using_competency() parameters.
     *
     * @return \external_function_parameters
     */
    public static function list_courses_using_competency_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'The competency id',
            VALUE_REQUIRED
        );
        $params = array(
            'id' => $competencyid,
        );
        return new external_function_parameters($params);
    }

    /**
     * Count the courses (visible to this user) that use this competency.
     *
     * @param int $competencyid Competency id.
     * @return array
     */
    public static function list_courses_using_competency($competencyid) {
        global $PAGE;

        $params = self::validate_parameters(self::list_courses_using_competency_parameters(), array(
            'id' => $competencyid,
        ));

        $competency = api::read_competency($params['id']);
        self::validate_context($competency->get_context());
        $output = $PAGE->get_renderer('local_competency');

        $results = array();
        $courses = api::list_courses_using_competency($params['id']);
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            $exporter = new course_summary_exporter($course, array('context' => $context));
            $result = $exporter->export($output);
            array_push($results, $result);
        }
        return $results;
    }

    /**
     * Returns description of list_courses_using_competency() result value.
     *
     * @return \external_description
     */
    public static function list_courses_using_competency_returns() {
        return new external_multiple_structure(course_summary_exporter::get_read_structure());
    }


    /**
     * Returns description of data_for_course_competenies_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_course_competencies_page_parameters() {
        $courseid = new external_value(
            PARAM_INT,
            'The course id',
            VALUE_REQUIRED
        );
        $params = array('courseid' => $courseid);
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the course_competencies_page template.
     *
     * @param int $courseid The course id to check.
     * @return boolean
     */
    public static function data_for_course_competencies_page($courseid) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_course_competencies_page_parameters(), array(
            'courseid' => $courseid,
        ));
        self::validate_context(context_course::instance($params['courseid']));

        $renderable = new output\course_competencies_page($params['courseid']);
        $renderer = $PAGE->get_renderer('local_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_course_competencies_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_course_competencies_page_returns() {
        $ucc = user_competency_course_exporter::get_read_structure();
        $ucc->required = VALUE_OPTIONAL;

        return new external_single_structure(array (
            'courseid' => new external_value(PARAM_INT, 'The current course id'),
            'pagecontextid' => new external_value(PARAM_INT, 'The current page context ID.'),
            'gradableuserid' => new external_value(PARAM_INT, 'Current user id, if the user is a gradable user.', VALUE_OPTIONAL),
            'canmanagecompetencyframeworks' => new external_value(PARAM_BOOL, 'User can manage competency frameworks'),
            'canmanagecoursecompetencies' => new external_value(PARAM_BOOL, 'User can manage linked course competencies'),
            'canconfigurecoursecompetencies' => new external_value(PARAM_BOOL, 'User can configure course competency settings'),
            'cangradecompetencies' => new external_value(PARAM_BOOL, 'User can grade competencies.'),
            'settings' => course_competency_settings_exporter::get_read_structure(),
            'statistics' => course_competency_statistics_exporter::get_read_structure(),
            'competencies' => new external_multiple_structure(new external_single_structure(array(
                'competency' => competency_exporter::get_read_structure(),
                'coursecompetency' => course_competency_exporter::get_read_structure(),
                'coursemodules' => new external_multiple_structure(course_module_summary_exporter::get_read_structure()),
                'usercompetencycourse' => $ucc,
                'ruleoutcomeoptions' => new external_multiple_structure(
                    new external_single_structure(array(
                        'value' => new external_value(PARAM_INT, 'The option value'),
                        'text' => new external_value(PARAM_NOTAGS, 'The name of the option'),
                        'selected' => new external_value(PARAM_BOOL, 'If this is the currently selected option'),
                    ))
                ),
                'comppath' => competency_path_exporter::get_read_structure(),
            ))),
            'manageurl' => new external_value(PARAM_LOCALURL, 'Url to the manage competencies page.'),
        ));

    }

    /**
     * Returns description of data_for_templates_manage_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_templates_manage_page_parameters() {
        $params = array('pagecontext' => self::get_context_parameters());
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the templates_manage_page template.
     *
     * @param array $pagecontext The page context info.
     * @return boolean
     */
    public static function data_for_templates_manage_page($pagecontext) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_templates_manage_page_parameters(), array(
            'pagecontext' => $pagecontext
        ));
        $context = self::get_context_from_params($params['pagecontext']);
        self::validate_context($context);

        $renderable = new output\manage_templates_page($context);
        $renderer = $PAGE->get_renderer('local_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_templates_manage_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_templates_manage_page_returns() {
        return new external_single_structure(array (
            'templates' => new external_multiple_structure(
                template_exporter::get_read_structure()
            ),
            'pluginbaseurl' => new external_value(PARAM_LOCALURL, 'Url to the local_competency plugin folder on this Moodle site'),
            'navigation' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'HTML for a navigation item that should be on this page')
            ),
            'pagecontextid' => new external_value(PARAM_INT, 'The page context id'),
            'canmanage' => new external_value(PARAM_BOOL, 'Whether the user manage the templates')
        ));

    }

    /**
     * Returns description of data_for_template_competenies_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_template_competencies_page_parameters() {
        $templateid = new external_value(
            PARAM_INT,
            'The template id',
            VALUE_REQUIRED
        );
        $params = array('templateid' => $templateid, 'pagecontext' => self::get_context_parameters());
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the template_competencies_page template.
     *
     * @param int $templateid Template id.
     * @param array $pagecontext The page context info.
     * @return boolean
     */
    public static function data_for_template_competencies_page($templateid, $pagecontext) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_template_competencies_page_parameters(), array(
            'templateid' => $templateid,
            'pagecontext' => $pagecontext
        ));

        $context = self::get_context_from_params($params['pagecontext']);
        self::validate_context($context);

        $template = api::read_template($params['templateid']);
        $renderable = new output\template_competencies_page($template, $context);
        $renderer = $PAGE->get_renderer('local_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_template_competencies_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_template_competencies_page_returns() {
        return new external_single_structure(array (
            'template' => template_exporter::get_read_structure(),
            'pagecontextid' => new external_value(PARAM_INT, 'Context ID'),
            'canmanagecompetencyframeworks' => new external_value(PARAM_BOOL, 'User can manage competency frameworks'),
            'canmanagetemplatecompetencies' => new external_value(PARAM_BOOL, 'User can manage learning plan templates'),
            'competencies' => new external_multiple_structure(
                competency_summary_exporter::get_read_structure()
            ),
            'manageurl' => new external_value(PARAM_LOCALURL, 'Url to the manage competencies page.'),
            'pluginbaseurl' => new external_value(PARAM_LOCALURL, 'Base URL of the plugin.'),
            'statistics' => template_statistics_exporter::get_read_structure()
        ));

    }

    /**
     * Returns description of data_for_plan_competenies_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_plan_page_parameters() {
        $planid = new external_value(
            PARAM_INT,
            'The plan id',
            VALUE_REQUIRED
        );
        $params = array('planid' => $planid);
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the plan_page template.
     *
     * @param int $planid Learning Plan id.
     * @return boolean
     */
    public static function data_for_plan_page($planid) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_plan_page_parameters(), array(
            'planid' => $planid
        ));
        $plan = api::read_plan($params['planid']);
        self::validate_context($plan->get_context());

        $renderable = new output\plan_page($plan);
        $renderer = $PAGE->get_renderer('local_competency');

        $data = $renderable->export_for_template($renderer);

        return $data;
    }

    /**
     * Returns description of data_for_plan_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_plan_page_returns() {
        $uc = user_competency_exporter::get_read_structure();
        $ucp = user_competency_plan_exporter::get_read_structure();

        $uc->required = VALUE_OPTIONAL;
        $ucp->required = VALUE_OPTIONAL;

        return new external_single_structure(array (
            'plan' => plan_exporter::get_read_structure(),
            'contextid' => new external_value(PARAM_INT, 'Context ID.'),
            'pluginbaseurl' => new external_value(PARAM_URL, 'Plugin base URL.'),
            'competencies' => new external_multiple_structure(
                new external_single_structure(array(
                    'competency' => competency_exporter::get_read_structure(),
                    'comppath' => competency_path_exporter::get_read_structure(),
                    'usercompetency' => $uc,
                    'usercompetencyplan' => $ucp
                ))
            ),
            'competencycount' => new external_value(PARAM_INT, 'Count of competencies'),
            'proficientcompetencycount' => new external_value(PARAM_INT, 'Count of proficientcompetencies'),
            'proficientcompetencypercentage' => new external_value(PARAM_FLOAT, 'Percentage of competencies proficient'),
            'proficientcompetencypercentageformatted' => new external_value(PARAM_RAW, 'Displayable percentage'),
        ));
    }

    /**
     * Returns description of data_for_plans_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_plans_page_parameters() {
        $userid = new external_value(
            PARAM_INT,
            'The user id',
            VALUE_REQUIRED
        );
        $params = array('userid' => $userid);
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the plans_page template.
     *
     * @param int $userid User id.
     * @return boolean
     */
    public static function data_for_plans_page($userid) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_plans_page_parameters(), array(
            'userid' => $userid,
        ));

        $context = context_user::instance($params['userid']);
        self::validate_context($context);
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new \local_competency\output\plans_page($params['userid']);

        return $renderable->export_for_template($output);
    }

    /**
     * Returns description of data_for_plans_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_plans_page_returns() {
        return new external_single_structure(array (
            'userid' => new external_value(PARAM_INT, 'The learning plan user id'),
            'plans' => new external_multiple_structure(
                plan_exporter::get_read_structure()
            ),
            'pluginbaseurl' => new external_value(PARAM_LOCALURL, 'Url to the local_competency plugin folder on this Moodle site'),
            'navigation' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'HTML for a navigation item that should be on this page')
            ),
            'canreaduserevidence' => new external_value(PARAM_BOOL, 'Can the current user view the user\'s evidence'),
            'canmanageuserplans' => new external_value(PARAM_BOOL, 'Can the current user manage the user\'s plans'),
        ));
    }

    /**
     * Returns description of external function parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_user_evidence_list_page_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'The user ID')
        ));
    }

    /**
     * Loads the data required to render the user_evidence_list_page template.
     *
     * @param int $userid User id.
     * @return boolean
     */
    public static function data_for_user_evidence_list_page($userid) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_user_evidence_list_page_parameters(),
            array('userid' => $userid));

        $context = context_user::instance($params['userid']);
        self::validate_context($context);
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new \local_competency\output\user_evidence_list_page($params['userid']);
        return $renderable->export_for_template($output);
    }

    /**
     * Returns description of external function result value.
     *
     * @return \external_description
     */
    public static function data_for_user_evidence_list_page_returns() {
        return new external_single_structure(array (
            'canmanage' => new external_value(PARAM_BOOL, 'Can the current user manage the user\'s evidence'),
            'userid' => new external_value(PARAM_INT, 'The user ID'),
            'pluginbaseurl' => new external_value(PARAM_LOCALURL, 'Url to the local_competency plugin folder on this Moodle site'),
            'evidence' => new external_multiple_structure(user_evidence_summary_exporter::get_read_structure()),
            'navigation' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'HTML for a navigation item that should be on this page')
            ),
        ));
    }

    /**
     * Returns description of external function parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_user_evidence_page_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'The user evidence ID')
        ));
    }

    /**
     * Loads the data required to render the user_evidence_page template.
     *
     * @param int $id User id.
     * @return boolean
     */
    public static function data_for_user_evidence_page($id) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_user_evidence_page_parameters(),
            array('id' => $id));

        $userevidence = api::read_user_evidence($id);
        self::validate_context($userevidence->get_context());
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new \local_competency\output\user_evidence_page($userevidence);
        return $renderable->export_for_template($output);
    }

    /**
     * Returns description of external function result value.
     *
     * @return \external_description
     */
    public static function data_for_user_evidence_page_returns() {
        return new external_single_structure(array(
            'userevidence' => user_evidence_summary_exporter::get_read_structure(),
            'pluginbaseurl' => new external_value(PARAM_LOCALURL, 'Url to the local_competency plugin folder on this Moodle site')
        ));
    }

    /**
     * Returns the description of the data_for_related_competencies_section_parameters() parameters.
     *
     * @return external_function_parameters.
     */
    public static function data_for_related_competencies_section_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'The competency id',
            VALUE_REQUIRED
        );
        return new external_function_parameters(array('competencyid' => $competencyid));
    }

    /**
     * Data to render in the related competencies section.
     *
     * @param int $competencyid
     * @return array Related competencies and whether to show delete action button or not.
     */
    public static function data_for_related_competencies_section($competencyid) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_related_competencies_section_parameters(), array(
            'competencyid' => $competencyid,
        ));
        $competency = api::read_competency($params['competencyid']);
        self::validate_context($competency->get_context());

        $renderable = new \local_competency\output\related_competencies($params['competencyid']);
        $renderer = $PAGE->get_renderer('local_competency');

        return $renderable->export_for_template($renderer);
    }

    /**
     * Returns description of data_for_related_competencies_section_returns() result value.
     *
     * @return external_description
     */
    public static function data_for_related_competencies_section_returns() {
        return new external_single_structure(array(
            'relatedcompetencies' => new external_multiple_structure(competency_exporter::get_read_structure()),
            'showdeleterelatedaction' => new external_value(PARAM_BOOL, 'Whether to show the delete relation link or not')
        ));
    }

    /**
     * Returns the description of external function parameters.
     *
     * @return external_function_parameters.
     */
    public static function search_users_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $capability = new external_value(
            PARAM_RAW,
            'Required capability'
        );
        $limitfrom = new external_value(
            PARAM_INT,
            'Number of records to skip',
            VALUE_DEFAULT,
            0
        );
        $limitnum = new external_value(
            PARAM_RAW,
            'Number of records to fetch',
            VALUE_DEFAULT,
            100
        );
        return new external_function_parameters(array(
            'query' => $query,
            'capability' => $capability,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum
        ));
    }

    /**
     * Search users.
     *
     * @param string $query
     * @param string $capability
     * @param int $limitfrom
     * @param int $limitnum
     * @return array
     */
    public static function search_users($query, $capability = '', $limitfrom = 0, $limitnum = 100) {
        global $DB, $CFG, $PAGE, $USER;

        $params = self::validate_parameters(self::search_users_parameters(), array(
            'query' => $query,
            'capability' => $capability,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum,
        ));
        $query = $params['query'];
        $cap = $params['capability'];
        $limitfrom = $params['limitfrom'];
        $limitnum = $params['limitnum'];

        $context = context_system::instance();
        self::validate_context($context);
        $output = $PAGE->get_renderer('local_competency');

        list($filtercapsql, $filtercapparams) = api::filter_users_with_capability_on_user_context_sql($cap,
            $USER->id, SQL_PARAMS_NAMED);

        $extrasearchfields = array();
        if (!empty($CFG->showuseridentity) && has_capability('moodle/site:viewuseridentity', $context)) {
            $extrasearchfields = explode(',', $CFG->showuseridentity);
        }
        $fields = \user_picture::fields('u', $extrasearchfields);

        list($wheresql, $whereparams) = users_search_sql($query, 'u', true, $extrasearchfields);
        list($sortsql, $sortparams) = users_order_by_sql('u', $query, $context);

        $countsql = "SELECT COUNT('x') FROM {user} u WHERE $wheresql AND u.id $filtercapsql";
        $countparams = $whereparams + $filtercapparams;
        $sql = "SELECT $fields FROM {user} u WHERE $wheresql AND u.id $filtercapsql ORDER BY $sortsql";
        $params = $whereparams + $filtercapparams + $sortparams;

        $count = $DB->count_records_sql($countsql, $countparams);
        $result = $DB->get_recordset_sql($sql, $params, $limitfrom, $limitnum);

        $users = array();
        foreach ($result as $key => $user) {
            // Make sure all required fields are set.
            foreach (user_summary_exporter::define_properties() as $propertykey => $definition) {
                if (empty($user->$propertykey) || !in_array($propertykey, $extrasearchfields)) {
                    if ($propertykey != 'id') {
                        $user->$propertykey = '';
                    }
                }
            }
            $exporter = new user_summary_exporter($user);
            $newuser = $exporter->export($output);

            $users[$key] = $newuser;
        }
        $result->close();

        return array(
            'users' => $users,
            'count' => $count
        );
    }

    /**
     * Returns description of external function result value.
     *
     * @return external_description
     */
    public static function search_users_returns() {
        global $CFG;
        require_once($CFG->dirroot . '/user/externallib.php');
        return new external_single_structure(array(
            'users' => new external_multiple_structure(user_summary_exporter::get_read_structure()),
            'count' => new external_value(PARAM_INT, 'Total number of results.')
        ));
    }

    /**
     * Returns description of external function.
     *
     * @return \external_function_parameters
     */
    public static function data_for_user_competency_summary_parameters() {
        $userid = new external_value(
            PARAM_INT,
            'Data base record id for the user',
            VALUE_REQUIRED
        );
        $competencyid = new external_value(
            PARAM_INT,
            'Data base record id for the competency',
            VALUE_REQUIRED
        );
        $params = array(
            'userid' => $userid,
            'competencyid' => $competencyid,
        );
        return new external_function_parameters($params);
    }

    /**
     * Data for user competency summary.
     *
     * @param int $userid The user ID
     * @param int $competencyid The competency ID
     * @return \stdClass
     */
    public static function data_for_user_competency_summary($userid, $competencyid) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_user_competency_summary_parameters(), array(
            'userid' => $userid,
            'competencyid' => $competencyid,
        ));

        $uc = api::get_user_competency($params['userid'], $params['competencyid']);
        self::validate_context($uc->get_context());
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new \local_competency\output\user_competency_summary($uc);
        return $renderable->export_for_template($output);
    }

    /**
     * Returns description of external function.
     *
     * @return \external_description
     */
    public static function data_for_user_competency_summary_returns() {
        return user_competency_summary_exporter::get_read_structure();
    }

    /**
     * Returns description of data_for_user_competency_summary_in_plan() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_user_competency_summary_in_plan_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'Data base record id for the competency',
            VALUE_REQUIRED
        );
        $planid = new external_value(
            PARAM_INT,
            'Data base record id for the plan',
            VALUE_REQUIRED
        );

        $params = array(
            'competencyid' => $competencyid,
            'planid' => $planid,
        );
        return new external_function_parameters($params);
    }

    /**
     * Read a user competency summary.
     *
     * @param int $competencyid The competency id
     * @param int $planid The plan id
     * @return \stdClass
     */
    public static function data_for_user_competency_summary_in_plan($competencyid, $planid) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_user_competency_summary_in_plan_parameters(), array(
            'competencyid' => $competencyid,
            'planid' => $planid
        ));

        $plan = api::read_plan($params['planid']);
        $context = $plan->get_context();
        self::validate_context($context);
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new user_competency_summary_in_plan($params['competencyid'], $params['planid']);
        return $renderable->export_for_template($output);
    }

    /**
     * Returns description of data_for_user_competency_summary_in_plan() result value.
     *
     * @return \external_description
     */
    public static function data_for_user_competency_summary_in_plan_returns() {
        return user_competency_summary_in_plan_exporter::get_read_structure();
    }

    /**
     * Returns description of data_for_user_competency_summary_in_course() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_user_competency_summary_in_course_parameters() {
        $userid = new external_value(
            PARAM_INT,
            'Data base record id for the user',
            VALUE_REQUIRED
        );
        $competencyid = new external_value(
            PARAM_INT,
            'Data base record id for the competency',
            VALUE_REQUIRED
        );
        $courseid = new external_value(
            PARAM_INT,
            'Data base record id for the course',
            VALUE_REQUIRED
        );

        $params = array(
            'userid' => $userid,
            'competencyid' => $competencyid,
            'courseid' => $courseid,
        );
        return new external_function_parameters($params);
    }

    /**
     * Read a user competency summary.
     *
     * @param int $userid The user id
     * @param int $competencyid The competency id
     * @param int $courseid The course id
     * @return \stdClass
     */
    public static function data_for_user_competency_summary_in_course($userid, $competencyid, $courseid) {
        global $PAGE;
        $params = self::validate_parameters(self::data_for_user_competency_summary_in_course_parameters(), array(
            'userid' => $userid,
            'competencyid' => $competencyid,
            'courseid' => $courseid
        ));
        $context = context_user::instance($params['userid']);
        self::validate_context($context);
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new user_competency_summary_in_course($params['userid'], $params['competencyid'], $params['courseid']);
        return $renderable->export_for_template($output);
    }

    /**
     * Returns description of data_for_user_competency_summary_in_course() result value.
     *
     * @return \external_description
     */
    public static function data_for_user_competency_summary_in_course_returns() {
        return user_competency_summary_in_course_exporter::get_read_structure();
    }



    //---------- added by hema------------
    /**
     * Returns the description of the data_for_related_competencies_section_parameters() parameters.
     *
     * @return external_function_parameters.
     */
    public static function data_for_user_competency_brief_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'The competency id',
            VALUE_REQUIRED
        );
        return new external_function_parameters(array('competencyid' => $competencyid));
    }


    /**
     * Data to render in the related competencies section.
     *
     * @param int $competencyid
     * @return array Related competencies and whether to show delete action button or not.
     */
    public static function data_for_user_competency_brief($competencyid) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_user_competency_brief_parameters(), array(
            'competencyid' => $competencyid,
        ));
        $competency = api::read_competency($params['competencyid']);
        self::validate_context($competency->get_context());

        $output = $PAGE->get_renderer('local_competency');
        
         //$renderable = new \local_competency\output\advancedview_of_usercompetency($params['competencyid'], 3);
         
        $renderable = new \local_competency\output\user_competency_brief($params['competencyid']);
       
       
       // echo $renderable->export_for_template($renderer);
        $data= $renderable->export_for_template($output);

     
        return $data;
    }

    /**
     * Returns description of data_for_related_competencies_section_returns() result value.
     *
     * @return external_description
     */
   public static function data_for_user_competency_brief_returns() {
        $ucc = user_competency_course_exporter::get_read_structure();
        $ucc->required = VALUE_OPTIONAL;

        return new external_single_structure(array (
    
            'gradableuserid' => new external_value(PARAM_INT, 'Current user id, if the user is a gradable user.', VALUE_OPTIONAL),
           
            'competencyframeworkid'=>  new external_value(PARAM_INT, 'Competency framework id.'),
           
            
            'competencies' => new external_multiple_structure(new external_single_structure(array(
                'competency' => competency_exporter::get_read_structure(),
                'coursecompetency' => course_competency_exporter::get_read_structure(),
                'coursemodules' => new external_multiple_structure(course_module_summary_exporter::get_read_structure()),
                'coursefullname'=>  new external_value(PARAM_TEXT, 'Competency assigned course fullname.'),
           
                'usercompetencycourse' => $ucc,
                'ruleoutcomeoptions' => new external_multiple_structure(
                    new external_single_structure(array(
                        'value' => new external_value(PARAM_INT, 'The option value'),
                        'text' => new external_value(PARAM_NOTAGS, 'The name of the option'),
                        'selected' => new external_value(PARAM_BOOL, 'If this is the currently selected option'),
                    ))
                ),
               
            ))),
         
        ));
    } 


/* public static function data_for_user_competency_brief_returns() {
        return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, 'Context id for the framework'),
        
        ));
    } */


     /**
     * Returns description of data_for_competencies_manage_page() parameters.
     *
     * @return \external_function_parameters
     */
    public static function data_for_view_competencies_page_parameters() {
        $competencyframeworkid = new external_value(
            PARAM_INT,
            'The competency framework id',
            VALUE_REQUIRED
        );
        $search = new external_value(
            PARAM_RAW,
            'A search string',
            VALUE_DEFAULT,
            ''
        );
        $params = array(
            'competencyframeworkid' => $competencyframeworkid,
            'search' => $search
        );
        return new external_function_parameters($params);
    }

    /**
     * Loads the data required to render the competencies_manage_page template.
     *
     * @param int $competencyframeworkid Framework id.
     * @param string $search Text to search.
     *
     * @return boolean
     */
    public static function data_for_view_competencies_page($competencyframeworkid, $search) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_view_competencies_page_parameters(), array(
            'competencyframeworkid' => $competencyframeworkid,
            'search' => $search
        ));

        $framework = api::read_framework($params['competencyframeworkid']);
        self::validate_context($framework->get_context());
        $output = $PAGE->get_renderer('local_competency');

        $renderable = new output\view_competencies_page($framework, $params['search'], $framework->get_context());

        $data = $renderable->export_for_template($output);
       

        return $data;
    }

    /**
     * Returns description of data_for_competencies_manage_page() result value.
     *
     * @return \external_description
     */
    public static function data_for_view_competencies_page_returns() {
        


        return new external_single_structure(array ( 
            
            'frameworks' => new external_multiple_structure(new external_single_structure(array(
                'framework' => competency_framework_exporter::get_read_structure(),
                'coursecompetency' => course_competency_exporter::get_read_structure(),
                'canmanage' => new external_value(PARAM_BOOL, 'True if this user has permission to manage competency frameworks'),
            'pagecontextid' => new external_value(PARAM_INT, 'Context id for the framework'),
            'search' => new external_value(PARAM_RAW, 'Current search string'),
            'rulesmodules' => new external_value(PARAM_RAW, 'JSON encoded data for rules'),
            'pluginbaseurl' => new external_value(PARAM_RAW, 'Plugin base url'),
            'competencyview' => new external_value(PARAM_RAW, 'View of Competency BASIC or ADVANCED'),  
            'spanclassnumber'=> new external_value(PARAM_TEXT, 'Used to add the span class(4 or 6)'), 
            'isadvancedview'=> new external_value(PARAM_INT, 'To check view is Advanced or Basic'),  

            ))),         
        ));


    }



    /**
     * Returns the description of the data_for_advancedview_of_usercompetency_parameters() parameters.
     *
     * @return external_function_parameters.
     */
    public static function data_for_advancedview_of_usercompetency_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'The competency id',
            VALUE_REQUIRED
        );
        $courseid = new external_value(
            PARAM_INT,
            'The course id',
            VALUE_REQUIRED
        );
        return new external_function_parameters(array('competencyid' => $competencyid,'courseid'=>$courseid));
    }


    /**
     * Data to render in the related competencies section.
     *
     * @param int $competencyid
     * @return array Related competencies and whether to show delete action button or not.
     */
    public static function data_for_advancedview_of_usercompetency($competencyid, $courseid) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_advancedview_of_usercompetency_parameters(), array(
            'competencyid' => $competencyid,'courseid'=>$courseid
        ));
        $competency = api::read_competency($params['competencyid']);
        self::validate_context($competency->get_context());
         
        $renderable = new \local_competency\output\advancedview_of_usercompetency($params['competencyid'], $params['courseid']);
        $output = $PAGE->get_renderer('local_competency');
       // echo $renderable->export_for_template($renderer);
        $data= $renderable->export_for_template($output);

      //  print_object($data);
        return $data;
    }

    /**
     * Returns description of data_for_related_competencies_section_returns() result value.
     *
     * @return external_description
     */
   public static function data_for_advancedview_of_usercompetency_returns() {
        $ucc = user_competency_course_exporter::get_read_structure();
        $ucc->required = VALUE_OPTIONAL;

        return new external_single_structure(array (
    
           
           
            'competencyframeworkid'=>  new external_value(PARAM_INT, 'Competency framework id.'),
           
            
    
                'competency' => competency_exporter::get_read_structure(),
               
                'restcoursemodules' => new external_multiple_structure(course_module_summary_exporter::get_read_structure()),
                 'competency_assignedcmodules'=>new external_value(PARAM_RAW, 'Competency assigned module and user competency grade.'),
               
           
         
        ));
    } 


    /**
     * Returns the description of the data_for_advancedview_of_usercompetency_parameters() parameters.
     *
     * @return external_function_parameters.
     */
    public static function data_for_advancedview_of_courselist_parameters() {
        $competencyid = new external_value(
            PARAM_INT,
            'The competency id',
            VALUE_REQUIRED
        );
      
        return new external_function_parameters(array('competencyid' => $competencyid));
    }


    /**
     * Data to render in the related competencies section.
     *
     * @param int $competencyid
     * @return array Related competencies and whether to show delete action button or not.
     */
    public static function data_for_advancedview_of_courselist($competencyid) {
        global $PAGE;

        $params = self::validate_parameters(self::data_for_advancedview_of_courselist_parameters(), array(
            'competencyid' => $competencyid
        ));
        $competency = api::read_competency($params['competencyid']);
        self::validate_context($competency->get_context());
        $renderable='';
         ob_clean(); 
          gc_enable($renderable);
        $renderable = new \local_competency\output\advancedview_of_courselist($params['competencyid']);

        $output = $PAGE->get_renderer('local_competency');
       // echo $renderable->export_for_template($renderer);
        $data= $renderable->export_for_template($output);

      //  print_object($data);
        return $data;
    }

    /**
     * Returns description of data_for_related_competencies_section_returns() result value.
     *
     * @return external_description
     */
   public static function data_for_advancedview_of_courselist_returns() {
        

        return new external_single_structure(array (          
           
            'competencyframeworkid'=>  new external_value(PARAM_INT, 'Competency framework id.'),           
            
          /*  'competencies' => new external_multiple_structure(new external_single_structure(array(  
               
              'coursefullname'=>  new external_value(PARAM_TEXT, 'Competency assigned course fullname.'),
              'competencycourse'=>  new external_value(PARAM_TEXT, 'Competency assigned course fullname.'),   
               
            ))), */
             'competencies'=>  new external_value(PARAM_RAW, 'Competency and course info.'),  
             'competency' => competency_exporter::get_read_structure(),
         
        ));
    } 



} // end of class
