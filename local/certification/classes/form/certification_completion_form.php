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
namespace local_certification\form;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
use \local_certification\certification as certification;
use moodleform;
use local_certification\local\querylib;
use context_system;

class certification_completion_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;
        $querieslib = new querylib();
        $context = context_system::instance();
        $mform = &$this->_form;
        $ctid = $this->_customdata['ctid'];
        $sid = $this->_customdata['id'];
        //$formhead = $sid > 0 ? get_string('updatesession', 'local_certification') : get_string('addsession', 'local_certification');
        //$mform->addElement('header', 'general', $formhead);

        $mform->addElement('hidden', 'id', $sid);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'certificationid', $ctid);
        $mform->setType('certificationid', PARAM_INT);

        $session_tracking=array(NULL=>get_string('certification_donotsessioncompletion','local_certification'),
                                'AND'=>get_string('certification_allsessionscompletion', 'local_certification'),
                                'OR'=>get_string('certification_anysessioncompletion', 'local_certification'));

        $mform->addElement('select', 'sessiontracking', get_string('sessiontracking', 'local_certification'), $session_tracking, array());
        
        
        $sessions = array();
        $sessions = $this->_ajaxformdata['sessionids'];
        if (!empty($sessions)) {
            $sessions = $sessions;
        } else if ($id > 0) {
            $sessions = $DB->get_records_menu('local_certificatn_completion',
                array('id' => $id), 'id', 'id, sessionids');
        }
        if (!empty($sessions)) {
                if(is_array($sessions)){
                    $sessions=implode(',',$sessions);
                 }
               
                $sessions_sql = "SELECT id, name as fullname
                                    FROM {local_certification_sessions}
                                    WHERE certificationid = {$ctid} and id in ({$sessions})";
                $sessions = $DB->get_records_sql_menu($sessions_sql);
        }elseif (empty($sessions)) {
            $sessions_sql = "SELECT id, name as fullname
                                        FROM {local_certification_sessions}
                                        WHERE certificationid = {$ctid} ";
            $sessions = $DB->get_records_sql_menu($sessions_sql);
        }
        $options = array(
            'ajax' => 'local_certification/form-options-selector',
            'multiple' => true,
            'data-contextid' => $context->id,
            'data-action' => 'certification_completions_sessions_selector',
            'data-options' => json_encode(array('id' => $id,'certificationid'=>$ctid)),
        );
            
        $mform->addElement('autocomplete', 'sessionids', get_string('session_completion', 'local_certification'), $sessions,$options);
        //$mform->disabledIf('sessionids', 'sessiontracking', 'neq','OR');
        
        
        $course_tracking=array(NULL=>get_string('certification_donotcoursecompletion','local_certification'),
                               'AND'=>get_string('certification_allcoursescompletion', 'local_certification'),
                                'OR'=>get_string('certification_anycoursecompletion', 'local_certification'));
                         
        $mform->addElement('select', 'coursetracking', get_string('coursetracking', 'local_certification'), $course_tracking, array());
        

        $courses = array();
        $courses = $this->_ajaxformdata['courseids'];
        if (!empty($courses)) {
            $courses = $courses;
        } else if ($id > 0) {
            $courses = $DB->get_records_menu('local_certificatn_completion',
                array('id' => $id), 'id', 'id, courseids');
        }
        if (!empty($courses)) {
                if(is_array($courses)){
                    $courses=implode(',',$courses);
                }
                $courses_sql = "SELECT c.id,c.fullname as fullname 
                    FROM {course} as c 
                    JOIN {local_certification_courses} as lcc on lcc.courseid=c.id 
                    WHERE lcc.certificationid = {$ctid} and lcc.courseid IN ({$courses})";
                $courses = $DB->get_records_sql_menu($courses_sql);
        }elseif (empty($courses)) {
            $courses_sql = "SELECT c.id,c.fullname as fullname 
                FROM {course} as c 
                JOIN {local_certification_courses} as lcc on lcc.courseid=c.id 
                WHERE lcc.certificationid = {$ctid} ";
            $courses = $DB->get_records_sql_menu($courses_sql);
        }
   
        $options = array(
            'ajax' => 'local_certification/form-options-selector',
            'multiple' => true,
            'data-contextid' => $context->id,
            'data-action' => 'certification_completions_courses_selector',
            'data-options' => json_encode(array('id' => $id,'certificationid'=>$ctid)),
        );
        
        $mform->addElement('autocomplete', 'courseids', get_string('course_completion', 'local_certification'), $courses,$options);
        //$mform->disabledIf('courseids', 'coursetracking', 'neq','OR');


        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
        global $CFG, $DB, $USER;
        $errors = parent::validation($data, $files);
        if (isset($data['sessiontracking']) && $data['sessiontracking'] == "OR" && isset($data['sessionids']) && empty($data['sessionids'])) {
            $errors['sessionids'] = get_string('select_sessions', 'local_certification');
        }
        if (isset($data['coursetracking']) && $data['coursetracking'] == "OR" && isset($data['courseids']) && empty($data['courseids'])) {
            $errors['courseids'] = get_string('select_courses', 'local_certification');
        }
        return $errors;
    }
}