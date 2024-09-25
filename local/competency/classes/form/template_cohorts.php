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
 * Template cohorts form.
 *
 * @package    local_competency
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_competency\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;
use core\form\persistent;

require_once($CFG->libdir . '/formslib.php');

/**
 * Template cohorts form class.
 *
 * @package    local_competency
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_cohorts extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $options = array(
            'ajax' => 'local_competency/form-cohort-selector',
            'multiple' => true,
            'data-contextid' => $this->_customdata['pagecontextid'],
            'data-includes' => get_string("competencyparents",'local_competency')
        );
        $mform->addElement('autocomplete', 'cohorts', get_string('selectcohortstosync', 'local_competency'), array(), $options);
        $mform->addElement('submit', 'submit', get_string('addcohorts', 'local_competency'));
    }

}
