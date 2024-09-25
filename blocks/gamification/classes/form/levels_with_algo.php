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
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

use moodleform;
use block_gamification\local\gamification\level_with_name;
use block_gamification\local\gamification\level_with_description;

/**
 * Block gamification levels form class.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @deprecated Since 3.10.0. Use external service instead.
 */
class levels_with_algo extends moodleform {

    /** @var config The config. */
    protected $config;

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        global $OUTPUT;

        debugging('The class \block_gamification\form\levels_with_algo is deprecated.', DEBUG_DEVELOPER);

        $mform = $this->_form;
        $config = isset($this->_customdata['config']) ? $this->_customdata['config'] : null;

        $mform->setDisableShortforms(true);
        $mform->addElement('header', 'hdrgen', get_string('general', 'form'));

        $mform->addElement('text', 'levels', get_string('levelcount', 'block_gamification'));
        $mform->addRule('levels', get_string('required'), 'required');
        $mform->setType('levels', PARAM_INT);

        $mform->addElement('selectyesno', 'usealgo', get_string('usealgo', 'block_gamification'));

        $mform->addElement('text', 'basegamification', get_string('basegamification', 'block_gamification'));
        $mform->disabledIf('basegamification', 'usealgo', 'eq', 0);
        $mform->setType('basegamification', PARAM_INT);
        $mform->setAdvanced('basegamification', true);

        $mform->addElement('text', 'coefgamification', get_string('coefgamification', 'block_gamification'));
        $mform->disabledIf('coefgamification', 'usealgo', 'eq', 0);
        $mform->setType('coefgamification', PARAM_FLOAT);
        $mform->setAdvanced('coefgamification', true);

        $mform->addElement('submit', 'updateandpreview', get_string('updateandpreview', 'block_gamification'));
        $mform->registerNoSubmitButton('updateandpreview');

        // First level.
        $mform->addElement('header', 'hdrlevel1', get_string('levelx', 'block_gamification', 1));
        $mform->addElement('static', 'lvlgamification_1', get_string('pointsrequired', 'block_gamification'), 0);

        $mform->addElement('text', 'lvlname_1', get_string('levelname', 'block_gamification'), ['maxlength' => 40]);
        $mform->addRule('lvlname_1', get_string('maximumchars', '', 40), 'maxlength', 40);
        $mform->setType('lvlname_1', PARAM_NOTAGS);
        $mform->addHelpButton('lvlname_1', 'levelname', 'block_gamification');

        $mform->addElement('text', 'lvldesc_1', get_string('leveldesc', 'block_gamification'), ['maxlength' => 255, 'size' => 50]);
        $mform->addRule('lvldesc_1', get_string('maximumchars', '', 255), 'maxlength', 255);
        $mform->setType('lvldesc_1', PARAM_NOTAGS);
        $mform->addHelpButton('lvldesc_1', 'leveldesc', 'block_gamification');

        $mform->addelement('hidden', 'insertlevelshere');
        $mform->setType('insertlevelshere', PARAM_BOOL);

        $this->add_action_buttons();

    }

    /**
     * Definition after data.
     *
     * @return void
     */
    public function definition_after_data() {
        $mform = $this->_form;

        // Ensure that the values are not wrong, the validation on save will catch those problems.
        $levels = max((int) $mform->exportValue('levels'), 2);
        $base = max((int) $mform->exportValue('basegamification'), 1);
        $coef = max((float) $mform->exportValue('coefgamification'), 1);

        $defaultlevels = \block_gamification\local\gamification\algo_levels_info::get_gamification_with_algo($levels, $base, $coef);

        // Add the levels.
        for ($i = 2; $i <= $levels; $i++) {
            $el =& $mform->createElement('header', 'hdrlevel' . $i, get_string('levelx', 'block_gamification', $i));
            $mform->insertElementBefore($el, 'insertlevelshere');

            $el =& $mform->createElement('text', 'lvlgamification_' . $i, get_string('pointsrequired', 'block_gamification'));
            $mform->insertElementBefore($el, 'insertlevelshere');
            $mform->setType('lvlgamification_' . $i, PARAM_INT);
            $mform->disabledIf('lvlgamification_' . $i, 'usealgo', 'eq', 1);
            if ($mform->exportValue('usealgo') == 1) {
                // Force the constant value when the algorightm is used.
                $mform->setConstant('lvlgamification_' . $i, $defaultlevels[$i]);
            }

            $el =& $mform->createElement('text', 'lvlname_' . $i, get_string('levelname', 'block_gamification'), ['maxlength' => 40]);
            $mform->insertElementBefore($el, 'insertlevelshere');
            $mform->addRule('lvlname_' . $i, get_string('maximumchars', '', 40), 'maxlength', 40);
            $mform->setType('lvlname_' . $i, PARAM_NOTAGS);

            $el =& $mform->createElement('text', 'lvldesc_' . $i, get_string('leveldesc', 'block_gamification'),
                ['maxlength' => 255, 'size' => 50]);
            $mform->insertElementBefore($el, 'insertlevelshere');
            $mform->addRule('lvldesc_' . $i, get_string('maximumchars', '', 255), 'maxlength', 255);
            $mform->setType('lvldesc_' . $i, PARAM_NOTAGS);
        }
    }

    /**
     * Get the levels info from submitted data.
     *
     * @return block_gamification\local\levels Levels.
     */
    public function get_levels_from_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        // Rearranging the information.
        $newdata = [
            'usealgo' => $data->usealgo,
            'base' => $data->basegamification,
            'coef' => $data->coefgamification,
            'gamification' => [
                '1' => 0
            ],
            'desc' => [],
            'name' => []
        ];

        $keys = ['gamification', 'desc', 'name'];
        for ($i = 1; $i <= $data->levels; $i++) {
            foreach ($keys as $key) {
                $datakey = 'lvl' . $key . '_' . $i;
                if (!empty($data->{$datakey})) {
                    $newdata[$key][$i] = $data->{$datakey};
                }
            }
        }

        return new \block_gamification\local\gamification\algo_levels_info($newdata);
    }

    /**
     * Set the data from the levels.
     *
     * Note that this does not use the interface levels_info. This is
     * dependent on the default implementation.
     *
     * @param \block_gamification\local\gamification\algo_levels_info $levels Levels.
     */
    public function set_data_from_levels(\block_gamification\local\gamification\algo_levels_info $levels) {
        $data = [
            'levels' => $levels->get_count(),
            'usealgo' => (int) $levels->get_use_algo(),
            'coefgamification' => $levels->get_coef(),
            'basegamification' => $levels->get_base(),
        ];
        foreach ($levels->get_levels() as $level) {
            $data['lvlgamification_' . $level->get_level()] = $level->get_gamification_required();
            $data['lvldesc_' . $level->get_level()] = $level instanceof level_with_description ? $level->get_description() : '';
            $data['lvlname_' . $level->get_level()] = $level instanceof level_with_name ? $level->get_name() : '';
        }
        $this->set_data($data);
    }

    /**
     * Data validate.
     *
     * @param array $data The data submitted.
     * @param array $files The files submitted.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = array();
        if ($data['levels'] < 2) {
            $errors['levels'] = get_string('errorlevelsincorrect', 'block_gamification');
        }

        // Validating the gamification points.
        if (!isset($errors['levels'])) {
            $lastgamification = 0;
            for ($i = 2; $i <= $data['levels']; $i++) {
                $key = 'lvlgamification_' . $i;
                $gamification = isset($data[$key]) ? (int) $data[$key] : -1;
                if ($gamification <= 0) {
                    $errors['lvlgamification_' . $i] = get_string('invalidgamification', 'block_gamification');
                } else if ($lastgamification >= $gamification) {
                    $errors['lvlgamification_' . $i] = get_string('errorgamificationrequiredlowerthanpreviouslevel', 'block_gamification');
                }
                $lastgamification = $gamification;
            }
        }

        return $errors;
    }

}
