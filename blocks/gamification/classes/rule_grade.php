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
 * Rule cm.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Rule cm class.
 *
 * Option to filter by course module.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_gamification_rule_grade extends block_gamification_rule_property {

    /**
     * Course ID used when we populate the form.
     * @var int
     */
    // protected $courseid;

    /**
     * The class property to compare against.
     *
     * @var string
     */
    // protected $property;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     * @param int $contextid The context ID.
     */
    public function __construct($courseid = 0, $contextid = 0) {
        global $COURSE;
        $this->courseid = empty($courseid) ? $COURSE->id : $courseid;
        parent::__construct(self::EQ, $contextid, 'contextid');
    }

    /**
     * Returns a string describing the rule.
     *
     * @return string
     */
    public function get_description() {
        $context = context::instance_by_id($this->courseid, IGNORE_MISSING);
        $contextname = get_string('errorunknownmodule', 'block_gamification');
        if ($context) {
            $contextname = $context->get_context_name();
        }
        return get_string('rulegradedesc', 'block_gamification', (object)array(
            'contextname' => $contextname
        ));
    }

    /**
     * Returns a form element for this rule.
     *
     * @param string $basename The form element base name.
     * @return string
     */
    public function get_form($basename) {
        global $CFG, $COURSE, $DB;
        require_once($CFG->dirroot.'/grade/querylib.php');
        $options = array();

        $modinfo = get_fast_modinfo($this->courseid);
        // var_dump('$activities');
        $modules = $DB->get_records_sql("SELECT id, name FROM {modules} WHERE visible=:visible AND name not like :name", array('visible' => '1', 'name' => 'workshop'));
        $activities = [];
        foreach($modules AS $module){
            if ($cms = grade_get_gradable_activities($this->courseid, $module->name)) {
                $activities += $cms;
            }
        }
        // print_object($modules);
        // $activities = grade_get_gradable_activities($this->courseid);
        // print_object($activities);
        $courseformat = course_get_format($this->courseid);

        // $completion = new completion_info(get_course($this->courseid));

        foreach ($modinfo->get_sections() as $sectionnum => $cmids) {
            $modules = array();
            foreach ($cmids as $cmid) {
                // print_object($cmid);
                // var_dump(array_key_exists($cmid, $activities));
                if(array_key_exists($cmid, $activities)){
                    $modules[$cmid] = $activities[$cmid]->name;
                }
            }
            $options[] = array($courseformat->get_section_name($sectionnum) => $modules);
        }
        // exit;
        // print_object($options);
        $o = block_gamification_rule::get_form($basename);
        $o .= "<span class='custom_gamification_rule ruletype_grade'>Grade of ";
        $o .= html_writer::select($options, $basename . '[module]', $this->module, '', array('id' => '', 'class' => ''));
        $o .= "<span class='alert alert-danger error_completion_setting hidden'>Required</span>";
        $o .= '&nbsp; is in range between ';
        // $o .= html_writer::empty_tag('input', array('type' => 'text', 'name' => $basename . '[ruletype]', 'value' => 'rule_grade', 'class' => 'hidden'));
        $o .= html_writer::empty_tag('input', array('type' => 'number', 'name' => $basename . '[lowervalue]',
            'value' => $this->lowervalue, 'class' => 'form-control block_gamification-form-control-inline lowervalue_element', 'min'=> '0', 'max' => '100')).'&nbsp;%';
        $o .= "<span class='alert alert-danger error_lowervalue_element hidden'>Required</span>";
        $o .= '&nbsp; and &nbsp;';
        $o .= html_writer::empty_tag('input', array('type' => 'number', 'name' => $basename . '[uppervalue]',
            'value' => $this->uppervalue, 'class' => 'form-control block_gamification-form-control-inline uppervalue_element', 'min'=> '0', 'max' => '100'));
        $o .= "<span class='alert alert-danger error_uppervalue_element hidden'>Required</span>";
        $o .= "<span class='alert alert-danger error_range_element_mismatch hidden'> Lowervalue boundary is greater than upper boundary.</span>";
        $o .= '&nbsp;% </span>';
        return $o;
    }

}
