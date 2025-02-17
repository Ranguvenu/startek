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
 * Ladder controller.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\controller;

use moodle_exception;

/**
 * Ladder controller class.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ladder_controller extends page_controller {

    const PAGE_SIZE_FLAG = 'ladder-pagesize';

    protected $requiremanage = false;
    protected $supportsgroups = true;
    protected $routename = 'ladder';

    protected function permissions_checks() {
        parent::permissions_checks();
        if (!$this->world->get_config()->get('enableladder')) {
            throw new moodle_exception('nopermissions', '', '', 'view_ladder_page');
        }
    }

    protected function page_setup() {
        global $PAGE;
        parent::page_setup();
        $PAGE->add_body_class('block_gamification-ladder');
    }

    /**
     * The optional params egamificationected.
     *
     * @return array
     */
    protected function define_optional_params() {
        return [
            ['pagesize', 0, PARAM_INT, false]
        ];
    }

    /**
     * Get the leadeboard.
     *
     * @return \block_gamification\local\leaderboard\leaderboard
     */
    protected function get_leaderboard() {
        $leaderboardfactory = \block_gamification\di::get('course_world_leaderboard_factory');
        return $leaderboardfactory->get_course_leaderboard($this->world, $this->get_groupid());
    }

    /**
     * Get the table.
     *
     * @return \flexible_table
     */
    protected function get_table() {
        global $USER;
        $table = new \block_gamification\output\leaderboard_table(
            $this->get_leaderboard(),
            $this->get_renderer(),
            [
                'context' => $this->world->get_context(),
                'identitymode' => $this->world->get_config()->get('identitymode'),
                'rankmode' => $this->world->get_config()->get('rankmode'),
            ],
            $USER->id
        );
        $table->show_pagesize_selector(true);
        $table->define_baseurl($this->pageurl);
        return $table;
    }

    protected function get_page_html_head_title() {
        return get_string('ladder', 'block_gamification');
    }

    protected function get_page_heading() {
        return get_string('ladder', 'block_gamification');
    }

    /**
     * Get the page size.
     *
     * @return int
     */
    protected function get_page_size() {
        global $USER;

        $indicator = \block_gamification\di::get('user_generic_indicator');
        $pagesizepref = $indicator->get_user_flag($USER->id, self::PAGE_SIZE_FLAG);
        $defaultpagesize = 20;

        // Get page size from URL argument.
        $pagesize = $this->get_param('pagesize');

        // Fallback on preference.
        if (empty($pagesize)) {
            $pagesize = $pagesizepref;
        }

        // Check that it is the right value.
        if (!in_array($pagesize, [20, 50, 100])) {
            $pagesize = $defaultpagesize;
        }

        if ($pagesize == $defaultpagesize) {
            // When the default, and we've got a saved flag, unset it.
            if (!empty($pagesizepref)) {
                $indicator->unset_user_flag($USER->id, self::PAGE_SIZE_FLAG);
            }

        } else if ($pagesize != $pagesizepref) {
            // It's not the default, and it's not our flag, save the flag.
            $indicator->set_user_flag($USER->id, self::PAGE_SIZE_FLAG, $pagesize);
        }

        return (int) $pagesize;
    }

    protected function page_content() {
        $this->print_group_menu();
        echo $this->get_table()->out($this->get_page_size(), false);
    }

}
