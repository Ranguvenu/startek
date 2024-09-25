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

class session_form extends moodleform {

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

        $mform->addElement('hidden', 'datetimeknown', 1);
        $mform->setType('datetimeknown', PARAM_INT);

        $mform->addElement('hidden', 'certificationid', $ctid);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'mincapacity', 0);
        $mform->setType('mincapacity', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'), array());
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $get_config=get_config('local_certification', 'certification_onlinesession_type');
		
        if(!empty($get_config)){
            $instance_type=explode('_',$get_config);
			$visible = $DB->get_field('modules','visible',array('name'=>$instance_type[1]));
			if($visible){
                $mform->addElement('advcheckbox', 'onlinesession', get_string('onlinesession',
                    'local_certification'), '', array(), array(0, 1));
                $mform->addHelpButton('onlinesession', 'onlinesession', 'local_certification');
            }
        }

        $locationroomlists = $querieslib->get_certification_institute_rooms($ctid);
        $locationrooms = array(null => get_string('select_room', 'local_certification')) + $locationroomlists;

        $mform->addElement('select', 'roomid', get_string('location_room', 'local_certification'), $locationrooms, array());
        $mform->addHelpButton('roomid', 'roomid', 'local_certification');
        $mform->disabledIf('roomid', 'onlinesession', 'checked');

        $trainers = array();
        $trainerid = $this->_ajaxformdata['trainerid'];
        if (!empty($trainerid)) {
            $trainerid = $trainerid;
        } else if ($sid > 0) {
            $trainerid = $DB->get_field('local_certification_sessions', 'trainerid',
                array('id' => $sid));
        }
        if (!empty($trainerid)) {
            $sql = "SELECT id, CONCAT(firstname,' ', lastname) AS fullname
                    FROM {user}
                    WHERE id = :userid ";
            $trainers = $DB->get_records_sql_menu($sql, array('userid' => $trainerid));
        }
        $options = array(
            'ajax' => 'local_certification/form-options-selector',
            'data-contextid' => $context->id,
            'data-action' => 'certificationsession_trainer_selector',
            'data-options' => json_encode(array('certificationid' => $ctid)),
        );

        $mform->addElement('autocomplete', 'trainerid', get_string('trainer', 'local_certification'), $trainers, $options);
        //$mform->addRule('trainerid', null, 'required', null, 'client');
        $mform->setType('trainerid', PARAM_INT);

        $mform->addElement('date_time_selector', 'timestart', get_string('cs_timestart', 'local_certification'));
        $mform->setType('timestart', PARAM_INT);

        $mform->addElement('date_time_selector', 'timefinish', get_string('cs_timefinish', 'local_certification'));
        $mform->setType('timefinish', PARAM_INT);

        $editoroptions = array(
            'noclean' => false,
            'autosave' => false
        );
        $mform->addElement('editor', 'cs_description', get_string('description', 'local_certification'), null, $editoroptions);
        $mform->setType('cs_description', PARAM_RAW);
        $mform->addHelpButton('cs_description', 'description', 'local_certification');

        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
        global $CFG, $DB, $USER;
        $errors = parent::validation($data, $files);
        //if (isset($data['onlinesession']) && $data['onlinesession'] == 0 && isset($data['roomid']) && $data['roomid'] <= 0) {
        //    $errors['roomid'] = get_string('select_cr_room', 'local_certification');
        //}
        $certificationdates = $DB->get_record_select('local_certification', 'id = :certificationid ',
            array('certificationid' => $data['certificationid']), 'startdate, enddate');
        if (isset($data['timestart']) && $data['timestart'] && isset($data['timefinish']) && $data['timefinish']) {
			
            $certificationstartdate = $certificationdates->startdate;
            $certificationenddate = $certificationdates->enddate;
            $sessionstartdate = $data['timestart'];
            $sessionenddate = $data['timefinish'];
			
			
			$sessions_validation_start=(new certification)->sessions_validation($data['certificationid'],$sessionstartdate,$data['id']);
			
			$sessions_validation_end=(new certification)->sessions_validation($data['certificationid'],$sessionenddate,$data['id']);
			
			
            if ($sessionstartdate < $certificationstartdate &&$certificationstartdate>0) {
                $errors["timestart"] = get_string('sessionstartdateerror1', 'local_certification');
            } else if ($sessionstartdate > $certificationenddate&&$certificationenddate>0) {
                $errors["timestart"] = get_string('sessionstartdateerror2', 'local_certification');
            }
            if ($data['timefinish'] <= $data['timestart']) {
                $errors["timefinish"] = get_string('enddateerror', 'local_certification');
            } else if ($certificationstartdate > $sessionenddate &&$certificationstartdate>0) {
                $errors["timefinish"] = get_string('sessionenddateerror1', 'local_certification');
            } else if ($sessionenddate > $certificationenddate &&$certificationenddate>0) {
                $errors["timefinish"] = get_string('sessionenddateerror2', 'local_certification');
            }elseif($sessions_validation_start || $sessions_validation_end){
				$errors["timestart"] = get_string('sessionexisterror', 'local_certification');
				$errors["timefinish"] = get_string('sessionexisterror', 'local_certification');
			}
        }
		if(isset($data['name']) &&empty(trim($data['name']))){
            $errors['name'] = get_string('valnamerequired','local_certification');
        }
        return $errors;
    }
}