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
class block_gamification_rule_completion extends block_gamification_rule_property {

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
        $context = context::instance_by_id($this->value, IGNORE_MISSING);
        $contextname = get_string('errorunknownmodule', 'block_gamification');
        if ($context) {
            $contextname = $context->get_context_name();
        }
        return get_string('rulecompletiondesc', 'block_gamification', (object)array(
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
        global $CFG, $COURSE;
        require_once($CFG->dirroot.'/grade/querylib.php');
        $options = array();

        $modinfo = get_fast_modinfo($this->courseid);
        // var_dump('$activities');
        $activities = grade_get_gradable_activities($this->courseid);
        // print_object($activities);
        $courseformat = course_get_format($this->courseid);

        $completion = new completion_info(get_course($this->courseid));

        foreach ($modinfo->get_sections() as $sectionnum => $cmids) {
            $modules = array();
            foreach ($cmids as $cmid) {
                $cm = $modinfo->get_cm($cmid);
                // print_object($cm->id);
                if($completion->is_enabled($cm)){
                    $modules['modulecompletion'.$cm->id.'modulecompletion'] = $cm->name;
                }
            }
            $options[] = array($courseformat->get_section_name($sectionnum) => $modules);
        }

        $o = block_gamification_rule::get_form($basename);
        $modules = html_writer::select($options, $basename . '[value]', $this->value, '', array('id' => '', 'class' => ''));
        $o .= get_string('activitycompletionis', 'block_gamification', $modules);
        return $o;
    }

}
