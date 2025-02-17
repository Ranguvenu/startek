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
 * @package block_learnerscript
 */
if (!defined('MOODLE_INTERNAL')) {
    //  It must be included from a Moodle page.
    die(get_string('nodirectaccess','block_learnerscript'));
}

require_once($CFG->libdir . '/formslib.php');

class myprograms_form extends moodleform {

    public function definition() {
        global $DB, $USER, $CFG;
        $mform = & $this->_form;
        $mform->addElement('header', 'crformheader', get_string('myprograms', 'block_learnerscript'), '');
        $columns = $DB->get_columns('myprograms');
        $activitycolumns = array();
        foreach ($columns as $c) {
            $activitycolumns[$c->name] = $c->name;
        }

        $mform->addElement('select', 'column', get_string('column', 'block_learnerscript'), $activitycolumns);
        $this->_customdata['compclass']->add_form_elements($mform, $this);
        // Buttons.
        $this->add_action_buttons(true, get_string('add'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->_customdata['compclass']->validate_form_elements($data, $errors);
        return $errors;
    }

}
/*        global $DB;

        if($certificates){
            $sql = "SELECT c.id,c.fullname
                    FROM {local_certification_users} as lcu
                    JOIN {course} as c ON c.id = lcu.courseid 
                    WHERE lcu.courseid = :courseid AND u.deleted = :deleted 
                    AND u.suspended = :suspended ";

            foreach ($certificates as $certificate) {
                $course = $DB->get_field_sql($sql,array('courseid' => $course->id, 'deleted'=>0,'suspended'=>0));
                $certificate->courses = $course;

                $completions = $DB->get_field_sql($sql,array('userid' => $user->id,
                    'deleted'=>0,'suspended'=>0));
                $certificate->users = $completions;
            }
        }*/