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
 * @package Bizlms 
 * @subpackage local_certificates
 */
namespace certificateelement_userfield;

defined('MOODLE_INTERNAL') || die();

class element extends \local_certificates\element {

    /**
     * This function renders the form elements when adding a certification element.
     *
     * @param \local_certificates\edit_element_form $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        // Get the user profile fields.
        $fields = array(
            'firstname' => get_user_field_name('firstname'),
            'lastname' => get_string('lastname', 'local_users'),
            'employeeid' => get_string('employeeid','local_users'),
            'email' => get_user_field_name('email'),
            'designation'=> get_string('designation','local_users'),
            'team'=> get_string('team','local_users'),
            'address' => get_user_field_name('address')
        );
        // \core_collator::asort($fields);

        // Create the select box where the user field is selected.
        $mform->addElement('select', 'userfield', get_string('userfield', 'certificateelement_userfield'), $fields);
        $mform->setType('userfield', PARAM_ALPHANUM);
        $mform->addHelpButton('userfield', 'userfield', 'certificate_element_userfield');

        parent::render_form_elements($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * certification_elements table.
     *
     * @param \stdClass $data the form data
     * @return string the text
     */
    public function save_unique_data($data) {
        return $data->userfield;
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        global $CFG, $DB;

        // The user field to display.
        $field = $this->get_data();
        // The value to display on the PDF.
        $field = $this->get_usertable_columnname($field);
        if (!empty($user->$field)) { // Field in the user table.
            $value = $user->$field;
        }else{
            $value = 'NA';
        }

        \local_certification\element_helper::render_content($pdf, $this, $value);
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     */
    public function render_html() {
        global $CFG, $DB, $USER;

        // The user field to display.
        $field = $this->get_data();
        switch ($field) {
            case 'employeeid':
                $field = 'open_employeeid';
                break;
            case 'designation':
                $field = 'open_designation';
                break;
            case 'team':
                $field = 'open_team';
                break;
            default:
                $field = $field;
                break;
        }
        $field = $this->get_usertable_columnname($field);
        // The value to display - we always want to show a value here so it can be repositioned.
        if (!empty($USER->$field)) { // Field in the user table.
            $value = $USER->$field;
        }else{
            $value = 'NA';
        }

        return \local_certification\element_helper::render_html_content($this, $value);
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data())) {
            $element = $mform->getElement('userfield');
            $element->setValue($this->get_data());
        }
        parent::definition_after_data($mform);
    }

    public function get_usertable_columnname($field){
        switch ($field) {
            case 'employeeid':
                $field = 'open_employeeid';
                break;
            case 'designation':
                $field = 'open_designation';
                break;
            case 'team':
                $field = 'open_team';
                break;
            default:
                $field = $field;
                break;
        }
        return $field;
    }
}
