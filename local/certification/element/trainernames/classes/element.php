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
 * @subpackage local_certification
 */

namespace certificationelement_trainernames;

defined('MOODLE_INTERNAL') || die();

/**
 * The certification element trainernames's core interaction API.
 *
 * @package    certificationelement_trainernames
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \local_certification\element {

    /**
     * This function renders the form elements when adding a certification element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        $mform->addElement('select', 'trainer', get_string('trainer', 'certificationelement_trainernames'),
            $this->get_list_of_trainers($this->get_id()),array('class'=>'trainers_open'));
        // $mform->addHelpButton('trainer', 'trainer', 'certificationelement_trainernames');

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
        if (!empty($data->trainer)) {
            return $data->trainer;
        }
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        global $DB;
        // $trainer = $DB->get_record('user', array('id' => $this->get_data()));
        // $trainernames = fullname($trainer);
        $trainernames = $this->get_list_of_trainers($this->get_id());
      
        \local_certification\element_helper::render_content($pdf, $this, implode(',',$trainernames));
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        global $DB;
        // $trainer = $DB->get_record('user', array('id' => $this->get_data()));
        // $trainernames = fullname($trainer);
         $trainernames = $this->get_list_of_trainers($this->get_id());

        return \local_certification\element_helper::render_html_content($this, implode(',',$trainernames));
    }

    /**
     * Helper function to return the trainers for this course.
     *
     * @return array the list of trainers
     */
    protected function get_list_of_trainers($elementid) {
        global $PAGE,$DB;

        // The list of trainers to return.
        $trainers = array();

        // Now return all users who can manage the certification in this context.
        if(isset($elementid)){
            $certificationtrainerssql = "SELECT u.id, u.picture, u.firstname, u.lastname,
                                        u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.imagealt, u.email
                                              FROM {user} AS u
                                               INNER JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                                               INNER JOIN {local_certification} c ON c.id=ct.certificationid
                                               INNER JOIN {local_certification_pages} cp ON c.templateid = cp.templateid
                                               INNER JOIN {local_certification_elements} ce ON cp.id = ce.pageid
                                              WHERE u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND ce.id = :elementid";

            $certificationtrainers = $DB->get_records_sql($certificationtrainerssql,array('elementid' =>$elementid));
       }else{
            $ctid = optional_param('ctid',0,PARAM_INT);
            if($ctid>0){
                 $certificationtrainerssql = "SELECT u.id, u.picture, u.firstname, u.lastname,
                                        u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.imagealt, u.email
                                              FROM {user} AS u
                                              JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                                              WHERE u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND ct.certificationid = :certificationid";
                $certificationtrainers = $DB->get_records_sql($certificationtrainerssql,array('certificationid' =>$ctid));                              
            }
       }
      
        if(!empty($certificationtrainers)) {

            foreach($certificationtrainers as $user) {
                 $trainers[$user->id] = fullname($user);
            }
        }
        // if ($users = get_users_by_capability($PAGE->context, 'local/certification:manage')) {
        //     foreach ($users as $user) {
        //         $trainers[$user->id] = fullname($user);
        //     }
        // }

        return $trainers;
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data())) {
            $element = $mform->getElement('trainer');
            $element->setValue($this->get_data());
        }
        parent::definition_after_data($mform);
    }
}
