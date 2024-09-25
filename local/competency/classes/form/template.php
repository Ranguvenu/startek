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
 * This file contains the form add/update a competency framework.
 *
 * @package   local_competency
 * @copyright 2015 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_competency\form;
defined('MOODLE_INTERNAL') || die();

use core\form\persistent;

/**
 * Learning plan template form.
 *
 * @package   local_competency
 * @copyright 2015 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template extends persistent {

    protected static $persistentclass = 'core_competency\\template';

    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        $mform = $this->_form;

        $context = $this->_customdata['context'];

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setConstant('contextid', $context->id);

        $mform->addElement('header', 'generalhdr', get_string('general'));

        // Name.
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_competency'), 'maxlength="100"');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->addRule('shortname', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
        // Description.
        $mform->addElement('editor', 'description',
                           get_string('description', 'local_competency'), array('rows' => 4));
        $mform->setType('description', PARAM_CLEANHTML);
        $mform->addElement('selectyesno', 'visible',
                           get_string('visible', 'local_competency'));
        $mform->addElement('date_time_selector',
                           'duedate',
                           get_string('duedate', 'local_competency'),
                           array('optional' => true));
        $mform->addHelpButton('duedate', 'duedate', 'local_competency');

        $mform->setDefault('visible', true);
        $mform->addHelpButton('visible', 'visible', 'local_competency');

        $mform->addElement('static', 'context', get_string('category', 'local_competency'));
        $mform->setDefault('context', $context->get_context_name(false));
        // Disable short forms.
        $mform->setDisableShortforms();

        $this->add_action_buttons(true, get_string('savechanges', 'local_competency'));

    }

}
