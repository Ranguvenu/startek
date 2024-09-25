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
 * Config controller.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\controller;
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');
use context_system;
use block_gamification\local\config\block_config;
use moodle_url;
global $PAGE;
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/select2.full.js'), true);
$PAGE->requires->css(new moodle_url('/blocks/gamification/css/select2.min.css'));
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/lbformchanges.js'));
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/on-off-switch-onload.js'));
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/on-off-switch.js'));
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/toggle.js'));
$PAGE->requires->css(new moodle_url('/blocks/gamification/css/on-off-switch.css'));
/**
 * Config controller class.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class leaderboard_controller extends page_controller {

    /** @var string The route name. */
    protected $routename = 'leaderboard';
    /** @var moodleform The form. */
    private $form;
    /** @var config The block config. */
    private $blockconfig;

    /**
     * Define the form.
     *
     * @param bool $withblockconfig With block config?
     * @return moodleform
     */
    protected function define_form($withblockconfig = false) {
        // echo $this->pageurl;
        return new \block_gamification\form\leaderboard($this->pageurl->out(false), [
            'showblockconfig' => $withblockconfig
        ]);
    }

    /**
     * Get the form.
     *
     * Private so that we do not override this one.
     *
     * @return moodleform
     */
    private function get_form() {
        if (!$this->form) {

            $this->form = $this->define_form(!empty($this->blockconfig));
        }
        return $this->form;
    }
    protected function get_page_html_head_title() {
        return get_string('leaderboard', 'block_gamification');
    }

    protected function get_page_heading() {
        return get_string('leaderboard', 'block_gamification');
    }

    protected function page_content() {
        global $DB,$COURSE;
        echo '<style>.custom_course_top_section{ display : none; } </style>';
        if ($COURSE->id !=1) {
            print_error('No Permissions For Course Level', 'error');
        }
        $form = $this->get_form();
        if ($form->is_cancelled()) {
            redirect(new moodle_url($COURSE->id));
        }
        else if ($data= $form->get_data()) {
            $result=updatepointstable($data);
            redirect(new moodle_url($COURSE->id));
        }
        else{
            $form->display();
        }
    }
}
