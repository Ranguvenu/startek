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
 * This file provides form for splitting discussions
 *
 * @package    local_forum
 * @copyright  2018 Sreenivas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once("$CFG->libdir/formslib.php");


/**
 * Form which displays fields for splitting forum post to a separate threads.
 *
 * @package    local_forum
 * @copyright  2018 Sreenivas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_forum_prune_form extends moodleform {

    /**
     * Form constructor.
     *
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('discussionname', 'local_forum'), array('size' => '60', 'maxlength' => '255'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->add_action_buttons(true, get_string('prune', 'local_forum'));

        $mform->addElement('hidden', 'prune');
        $mform->setType('prune', PARAM_INT);
        $mform->setConstant('prune', $this->_customdata['prune']);

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_INT);
        $mform->setConstant('confirm', $this->_customdata['confirm']);
    }
}
