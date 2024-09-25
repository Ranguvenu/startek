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
    //  It must be included from a Moodle page.
    die(get_string('nodirectaccess','block_learnerscript'));
}

require_once($CFG->libdir . '/formslib.php');

class userfield_form extends moodleform {

    public function definition() {
        global $DB, $USER, $CFG;
        $mform = & $this->_form;
        $mform->addElement('header', 'crformheader', get_string('userfield', 'block_learnerscript'), '');
        // $columns = $DB->get_columns('user');
        $columns = array('fullname','username','firstname','lastname','email','employeeid','reportingmanager','userstatus','designation','level','state','branch','organization','client','lob','region','location','department','contactnumber');
        $customfields = $DB->get_fieldset_sql("SELECT concat('profile_',name) FROM {user_info_field} WHERE 1");

        $columns = array_merge($columns,$customfields);
        $usercolumns = array_map('ucfirst', $columns);
        $usercolumns = array_combine($columns, $usercolumns);

        $mform->addElement('select', 'column', get_string('column', 'block_learnerscript'), $usercolumns);
        $this->_customdata['compclass']->add_form_elements($mform, $this);
        // Buttons.
        $this->add_action_buttons(true, get_string('add'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->_customdata['compclass']->validate_form_elements($data, $errors);
        return $errors;
    }

    public function advanced_columns() {
        global $DB;
        $columns = array('fullname','username','firstname','lastname','email','employeeid','reportingmanager','userstatus','designation','level','state','branch','organization','client','lob','region','location','department','contactnumber');
        $customfields = $DB->get_fieldset_sql("SELECT concat('profile_',name) FROM {user_info_field} WHERE 1");
        $columns = $columns+$customfields;

        $usercolumns = array_map('ucfirst', $columns);
        $usercolumns = array_combine($columns, $usercolumns);
        return $usercolumns;
    }

}
