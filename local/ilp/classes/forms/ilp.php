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
/* Learning Plan Block
 * This plugin serves as a database and plan for all learning activities in the organziation, 
 * where such activities are organized for a more structured learning program.
 * @package local
 * @sub package learning plan
 * @author: Syed HameedUllah
 * @copyright  Copyrights © 2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_ilp\forms;
require_once($CFG->libdir . '/formslib.php');

use moodleform;
use context_system;
use core_component;
// Add Learning Plans.
class ilp extends moodleform {

	// public $formstatus;
	
	// public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

	//  	$this->formstatus = array(
	//  		'generaldetails' => get_string('generaldetails', 'local_ilp'),
	// 		'otherdetails' => get_string('otherdetails', 'local_ilp')
	// 		);
	//  	parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
	// }
    
    public function definition() {
        global $USER, $DB, $CFG;
        $mform = $this->_form;
		
        $id = $this->_customdata['id'];
		$org = $this->_customdata['costcenterid'];
		$dept = $this->_customdata['department'];
		$sub_dept = $this->_customdata['subdepartment'];
		$sub_sub_dept = $this->_customdata['sub_sub_department'];
		$editoroptions = $this->customdata['editoroptions'];
		$systemcontext = context_system::instance();
		
		$mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('ilp_name', 'local_ilp'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
		
		$mform->addElement('text', 'shortname', get_string('shortname'), 'maxlength="100" size="20"');
		$mform->addRule('shortname', get_string('missing_plan_shortname', 'local_ilp'), 'required', null, 'client');

        $mform->setType('shortname', PARAM_TEXT);	

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_ilp'));
        // $mform->setType('enddate', PARAM_RAW);	
		
		$user_dept=$DB->get_field('user','open_costcenterid', array('id'=>$USER->id));
		$mform->addElement('hidden', 'costcenter', $user_dept);
		$mform->setType('costcenter', PARAM_ALPHANUM);

		$mform->addElement('hidden', 'learning_type', 1);
		$mform->setType('learning_type', PARAM_INT);

		$mform->addElement('hidden', 'credits', 0);
		$mform->setType('credits', PARAM_INT);

        $editoroption = [
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'trust' => false,
        'context' => $systemcontext,
        'noclean' => true,
        'subdirs' => false,
        'autosave'=>false
    	];
		$mform->addElement('editor','description', get_string('description'), null, $editoroption);
        $mform->setType('description', PARAM_RAW);
		
		$mform->addElement('filemanager', 'summaryfile', 'Learning path summary file', null,array('maxbytes' => $maxbytes, 'accepted_types' => ['.jpg','.jpeg','.png','.gif']));
		
        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
       
        $errors = array();
		global $DB;
	    $errors = parent::validation($data, $files);
		// if($data['enddate'] < $data['startdate']){
	 //        $errors['enddate'] = get_string('startdategreaterenddate','local_ilp');
		// }
		
		if(empty(trim($data['name']))){
			$errors['name'] = get_string('provide_valid_name', 'local_ilp');
		}
		if(empty(trim($data['shortname']))){
            $errors['shortname'] = get_string('provide_valid_shortname','local_ilp');
		}
		if($data['enddate'] < strtotime(date('d/m/Y'))){
			$errors['enddate'] = get_string('enddateshouldgreaterthannow', 'local_ilp');
		}

		$where = '';$params = [];
		if($data['id'] > 0) {
			$where = 'AND id <> :id';
			$params['id'] = $data['id'];
		}
    	if ($lplan = $DB->get_record_select('local_ilp', 'shortname = :shortname'.$where, array_merge(array('shortname' => $data['shortname']), $params))) {
			if($data['id'] != $lplan->id && $data['id'] > 0){
    			$errors['shortname'] = get_string('unameexists','local_ilp');
    		}
		}
		return $errors;
    }
}
