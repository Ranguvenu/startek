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
 * Block gamification log table.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use stdClass;
use table_sql;
use block_gamification\local\course_world;
use block_gamification\local\utils\user_utils;
use html_writer;
use moodle_url;
use pix_icon;

/**
 * Block gamification log table class.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_table extends table_sql {

    /** @var string The key of the user ID column. */
    public $useridfield = 'userid';
    /** @var course_world The world. */
    protected $world;
    /** @var renderer_base The renderer. */
    protected $renderer;
    /** @var int Filter by user ID, falsy means not filtering. */
    protected $filterbyuserid;

    /**
     * Constructor.
     *
     * @param course_world $world The world.
     * @param int $groupid The group ID.
     * @param int|null $userid The user ID.
     */
    public function __construct(course_world $world, $groupid, $userid = null) {
        $userid = max(0, (int) $userid);
        parent::__construct('block_gamification_log_' . $userid);

        $this->world = $world;
        $this->renderer = \block_gamification\di::get('renderer');
        $this->filterbyuserid = $userid;

        $courseid = $world->get_courseid();

        // Define columns.
        $this->define_columns(array(
            'time',
            'fullname',
            'gamification',
            'eventname'
        ));
        $this->define_headers(array(
            get_string('eventtime', 'block_gamification'),
            get_string('fullname'),
            get_string('reward', 'block_gamification'),
            get_string('eventname', 'block_gamification')
        ));

        // Define SQL.
        $sqlfrom = '';
        $sqlparams = array();
        if ($groupid) {
            $sqlfrom = '{block_gamification_log} x
                     JOIN {groups_members} gm
                       ON gm.groupid = :groupid
                      AND gm.userid = x.userid
                LEFT JOIN {user} u
                       ON x.userid = u.id';
            $sqlparams = array('groupid' => $groupid);
        } else {
            $sqlfrom = '{block_gamification_log} x LEFT JOIN {user} u ON x.userid = u.id';
        }

        // Define SQL.
        $this->sql = new stdClass();
        $this->sql->fields = 'x.*, ' . user_utils::name_fields('u');
        $this->sql->from = $sqlfrom;
        $this->sql->where = 'courseid = :courseid';
        $this->sql->params = array_merge(array('courseid' => $courseid), $sqlparams);
        if ($this->filterbyuserid) {
            $this->sql->where .= ' AND userid = :userid';
            $this->sql->params = array_merge($this->sql->params, ['userid' => $this->filterbyuserid]);
        }

        // Define various table settings.
        $this->sortable(true, 'time', SORT_DESC);
        $this->collapsible(false);
    }

    /**
     * Formats the column time.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_fullname($row) {
        $fullname = parent::col_fullname($row);
        if (!$this->filterbyuserid) {
            $fullname .= ' ' . $this->renderer->action_icon(
                new moodle_url($this->baseurl, ['userid' => $row->userid]),
                new pix_icon('i/search', get_string('filterbyuser', 'block_gamification'))
            );
        }
        return $fullname;
    }

    /**
     * Formats the column time.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_time($row) {
        return userdate($row->time);
    }

    /**
     * gamification.
     *
     * @param stdClass $row The row.
     * @return string
     */
    protected function col_gamification($row) {
        return $this->renderer->gamification($row->gamification);
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        $hasfilters = false;
        $showfilters = false;

        if ($this->can_be_reset()) {
            $hasfilters = true;
            $showfilters = true;
        }

        // Render button to allow user to reset table preferences, and the initial bars if some filters
        // are used. If none of the filters are used and there is nothing to display it just means that
        // the course is empty and thus we do not show anything but a message.
        echo $this->render_reset_button();
        if ($showfilters) {
            $this->print_initials_bar();
        }

        $message = get_string('nologsrecordedyet', 'block_gamification');
        if ($hasfilters) {
            $message = get_string('nothingtodisplay', 'core');
        }

        echo \html_writer::div(
            \block_gamification\di::get('renderer')->notification_without_close($message, 'info'),
            '',
            ['style' => 'margin: 1em 0']
        );
    }
}
