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
 * Visuals controller.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\controller;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

use context_course;
use context_system;
use html_writer;
use stdClass;
use block_gamification\local\config\course_world_config;

/**
 * Visuals controller class.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class visuals_controller extends page_controller {

    /** @inheritDoc */
    protected $navname = 'levels';
    /** @var string The route name. */
    protected $routename = 'visuals';

    /** @var moodleform The form. */
    private $form;

    /**
     * Get manager context.
     *
     * @return context
     */
    final protected function get_filemanager_context() {
        return $this->world->get_context();
    }

    /**
     * Get file manager options.
     *
     * @return array
     */
    final protected function get_filemanager_options() {
        return ['subdirs' => 0, 'accepted_types' => array('.jpg', '.png', '.gif', '.svg')];
    }

    /**
     * Define the form.
     *
     * @return moodleform
     */
    protected function define_form() {
        return new \block_gamification\form\visuals($this->pageurl->out(false), [
            'fmoptions' => $this->get_filemanager_options()
        ]);
    }

    /**
     * Get the form.
     *
     * @return moodleform
     */
    final protected function get_form() {
        if (!$this->form) {
            $this->form = $this->define_form();
        }
        return $this->form;
    }

    protected function pre_content() {
        $form = $this->get_form();
        $form->set_data((object) $this->get_initial_form_data());
        if ($data = $form->get_data()) {
            $this->save_form_data($data);
            // TODO Add a confirmation message.
            $this->redirect();

        } else if ($form->is_cancelled()) {
            $this->redirect();
        }
    }

    /**
     * Get the initial form data.
     *
     * @return array
     */
    protected function get_initial_form_data() {
        $config = $this->world->get_config();
        $draftitemid = file_get_submitted_draft_itemid('badges');

        // If the badges are missing, we copy them now.
        if ($config->get('enablecustomlevelbadges') == course_world_config::CUSTOM_BADGES_MISSING) {
            file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_gamification', 'defaultbadges', 0,
                $this->get_filemanager_options());
        } else {
            file_prepare_draft_area($draftitemid, $this->get_filemanager_context()->id, 'block_gamification', 'badges', 0,
                $this->get_filemanager_options());
        }

        return [
            'badges' => $draftitemid
        ];
    }

    /**
     * Save the form data.
     *
     * @param stdClass $data The form data.
     * @return void
     */
    protected function save_form_data($data) {
        $config = $this->world->get_config();

        // Save the area.
        file_save_draft_area_files($data->badges, $this->get_filemanager_context()->id, 'block_gamification', 'badges', 0,
            $this->get_filemanager_options());

        // When we save, we mark the flag as noop because either we copied the default badges,
        // when we loaded the draft area, or the user saved the page as they were in a legacy state,
        // and we want to take them out of it.
        $config->set('enablecustomlevelbadges', course_world_config::CUSTOM_BADGES_NOOP);
    }

    protected function get_page_html_head_title() {
        return get_string('coursevisuals', 'block_gamification');
    }

    protected function get_page_heading() {
        return get_string('coursevisuals', 'block_gamification');
    }

    /**
     * Introduction.
     *
     * @return void
     */
    protected function intro() {
        echo html_writer::tag('p', get_string('visualsintro', 'block_gamification'));
    }

    protected function page_content() {
        $this->intro();

        $this->get_form()->display();

        echo $this->get_renderer()->heading(get_string('preview'), 3);

        $this->preview();
    }

    /**
     * Preview.
     *
     * @return void
     */
    protected function preview() {
        $levelsinfo = $this->world->get_levels_info();
        echo $this->get_renderer()->levels_preview($levelsinfo->get_levels());
    }

}
