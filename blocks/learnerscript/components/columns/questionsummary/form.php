<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package BizLMS
 * @subpackage block_learnerscript
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir . '/formslib.php');

class questionsummary_form extends moodleform {

    function definition() {
        global $DB, $USER, $CFG;
        $mform = & $this->_form;
        $mform->addElement('header', 'crformheader', get_string('courseparticipationcolumns', 'block_learnerscript'), '');
        
        $columns = array();
         $coursecolumns = array();
        foreach($columns as $c){
           $coursecolumns[$c] = ucwords($c);
        }
        $mform->addElement('select', 'column', get_string('column', 'block_learnerscript'), $coursecolumns);
        $this->_customdata['compclass']->add_form_elements($mform, $this);
        // buttons
        $this->add_action_buttons(true, get_string('add'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->_customdata['compclass']->validate_form_elements($data, $errors);
        return $errors;
    }

}