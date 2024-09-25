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
 * Block gamification levels form.
 *
 * @package    block_gamification
 * @copyright  2018 Maheshchandra Nerella <maheshchandra@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

use moodleform;
class customlevels extends moodleform{
	public function definition(){
		global $DB, $USER;
        $mform = $this->_form;
        $levels = $this->_customdata['levels'];
        $costcenterid = $this->_customdata['costcenterid'];
        $enabled = $this->_customdata['enabled'];
        $id = $this->_customdata['id'];
        $mform->setDisableShortforms(true);
        $getstringhelpers = (new \block_gamification\customlib)::getstringhelpers($USER->open_costcenterid);
        $mform->addElement('header', 'hdrlevel1', get_string('levelx', 'block_gamification', 1));
        $mform->addElement('static', 'lvlgamification_1', get_string('pointsrequired', 'block_gamification', $getstringhelpers), 0);

        for ($i = 2; $i <= $levels; $i++) {
            $mform->addElement('header', 'hdrlevel' . $i, get_string('levelx', 'block_gamification', $i));

            $mform->addElement('text', 'lvlgamification_' . $i, get_string('pointsrequired', 'block_gamification', $getstringhelpers));
            $mform->setType('lvlgamification_' . $i, PARAM_INT);
            $mform->addElement('text', 'lvldesc_' . $i, get_string('leveldesc', 'block_gamification'));
            $mform->addRule('lvldesc_' . $i, get_string('maximumchars', '', 255), 'maxlength', 255);
            $mform->setType('lvldesc_' . $i, PARAM_NOTAGS);
        }
        $mform->addElement('hidden',  'costcenterid',  $costcenterid);
        $mform->setType('costcenterid', PARAM_INT);
        $mform->addElement('hidden',  'enabled',  $enabled);
        $mform->setType('enabled', PARAM_INT);
        $mform->addElement('hidden',  'levels',  $levels);
        $mform->setType('levels', PARAM_INT);
	}
	public function validation($data, $files) {
        $errors = array();
        if ($data['levels'] < 1) {
            $errors['levels'] = get_string('errorlevelsincorrect', 'block_gamification');
        }
        // Validating the gamification points.
        if (!isset($errors['levels'])) {
            $lastgamification = 0;
            for ($i = 2; $i <= $data['levels']; $i++) {
                $key = 'lvlgamification_' . $i;
                $gamification = isset($data[$key]) ? (int) $data[$key] : -1;
                if ($gamification <= 0) {
                    $errors['lvlgamification_' . $i] = get_string('invalidxp', 'block_gamification');
                } else if ($lastgamification >= $gamification) {
                    $errors['lvlgamification_' . $i] = get_string('errorxprequiredlowerthanpreviouslevel', 'block_gamification');
                }
                $lastgamification = $gamification;
            }
        }

        return $errors;
    }	
}