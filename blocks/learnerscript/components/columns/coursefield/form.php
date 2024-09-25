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

class coursefield_form extends moodleform {

    public function definition() {
        global $DB, $USER, $CFG;

        $mform = & $this->_form;

        $mform->addElement('header', 'crformheader', get_string('coursefield', 'block_learnerscript'), '');

        // $columns = $DB->get_columns('course');

        // $coursecolumns = array();
        // foreach ($columns as $c) {
        //     $coursecolumns[$c->name] = ucfirst($c->name);
        // }

        $columns = array('coursename','coursecode','coursecategory','coursevisible',get_string('courseorg','local_costcenter'),get_string('coursedept', 'local_costcenter'),get_string('course_subdept','local_costcenter'),get_string('course_commercialarea', 'local_costcenter'),get_string('course_territory', 'local_costcenter'),/*'points',*/'courseskill','courselevel','coursetype');
        $coursecolumns = array_map('ucfirst', $columns);
        $coursecolumns = array_combine($columns, $coursecolumns);

        $mform->addElement('select', 'column', get_string('column', 'block_learnerscript'), $coursecolumns);

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

        $columns = array('coursename','coursecode','coursecategory','coursecategory',get_string('courseorg','local_costcenter'),get_string('coursedept', 'local_costcenter'),get_string('course_subdept','local_costcenter'),'points','courseskill','courselevel','coursestartdate',
            'courseenddate','coursetype');
        $coursecolumns = array_map('ucfirst', $columns);
        $coursecolumns = array_combine($columns, $coursecolumns);

        return $coursecolumns;
    }

}
