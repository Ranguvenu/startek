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
namespace local_learningplan\forms;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_once($CFG->dirroot . '/local/custom_category/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
use moodleform;
use context_system;
use costcenter;
use events;
use context_user;
use local_users\functions\userlibfunctions as userlib;
use core_component;
// Add Learning Plans.
class learningplan extends moodleform {
 
	public $formstatus;
	public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

	 	$this->formstatus = array(
	 		'generaldetails' => get_string('generaldetails', 'local_learningplan'),
	 		'lp_otherdetails' => get_string('lp_otherdetails', 'local_learningplan'),
			'otherdetails' => get_string('otherdetails', 'local_learningplan')
			);
	 	parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
	}
    public function definition() {
        global $USER, $DB, $CFG;
        $mform = $this->_form;
		
        $id = $this->_customdata['id'];
		// $org = $this->_customdata['costcenterid'];
		// $dept = $this->_customdata['department'];
		// $sub_dept = $this->_customdata['subdepartment'];
		// $sub_sub_dept = $this->_customdata['sub_sub_department'];
        
		$editoroptions = $this->customdata['editoroptions'];
		$form_status = $this->_customdata['form_status'];
		$open_path = $this->_customdata['open_path'];
        $org = $this->_customdata['open_costcenterid'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
		$categorycontext = (new \local_learningplan\lib\accesslib())::get_module_context();
   		//list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/",$open_path);
		$mform->addElement('hidden', 'id', $id, array('id' => 'learningplanid'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'form_status', $form_status);
        $mform->setType('form_status', PARAM_INT);
      
        if (!isset($errors)){
            $errors = array();
        }

        $core_component = new core_component();
        $lplan = $DB->get_record('local_learningplan', array('id' => $id ));
		if($form_status == 0){
			// if (is_siteadmin($USER->id) || has_capability('local/users:manage',$categorycontext)) {
			// 	$sql="select id,fullname from {local_costcenter} where visible =1 and parentid=0 ";
	        //     $costcenters = $DB->get_records_sql($sql);
	        // }

            local_costcenter_get_hierarchy_fields($mform, $this->_ajaxformdata, $this->_customdata,range(1,1), false, 'local_costcenter', $categorycontext, $multiple = false);

	        $mform->addElement('text', 'name', get_string('learning_plan_name', 'local_learningplan'));
	        $mform->addRule('name', null, 'required', null, 'client');
	        $mform->setType('name', PARAM_TEXT);
            $mform->addElement('text', 'shortname', get_string('learningplan','local_learningplan'), 'maxlength="100" size="20"');
			if($id < 0 || empty($id)){
			$mform->addRule('shortname', get_string('missing_plan_learningplan', 'local_learningplan'), 'required', null, 'client');
			}
			if($id > 0){
				$mform->disabledIf('shortname','id');
			}
	        $mform->setType('shortname', PARAM_TEXT);
			// $mform->addElement('hidden', 'lpsequence', 1);
			// $mform->setType('lpsequence', PARAM_INT);
			// $mform->setConstant('lpsequence', 1);

			$manageselfenrol = array();
            $manageselfenrol[] = $mform->createElement('radio', 'selfenrol', '', get_string('yes'), 1, $attributes);
            $manageselfenrol[] = $mform->createElement('radio', 'selfenrol', '', get_string('no'), 0, $attributes);
            $mform->addGroup($manageselfenrol, 'selfenrol',
                get_string('need_self_enrol', 'local_courses'),
                array('&nbsp;&nbsp;'), false);
            $mform->addHelpButton('selfenrol', 'selfenrolcourse', 'local_learningplan');
			
			$manageapproval = array();
			$manageapproval[] = $mform->createElement('radio', 'approvalreqd', '', get_string('yes'), 1, $attributes);
			$manageapproval[] = $mform->createElement('radio', 'approvalreqd', '', get_string('no'), 0, $attributes);
			$mform->addGroup($manageapproval, 'approvalreqd',get_string('need_manage_approval', 'local_learningplan'),
				array('&nbsp;&nbsp;'), false);
			$mform->addHelpButton('approvalreqd','need_manager_approval','local_learningplan');
            $mform->hideIf('approvalreqd', 'selfenrol', 'neq', '1');

	       	$sequence = array();
			$sequence[] = $mform->createElement('radio', 'lpsequence', '', get_string('yes'), 1, $attributes);
			$sequence[] = $mform->createElement('radio', 'lpsequence', '', get_string('no'), 0, $attributes);
			$mform->addGroup($sequence, 'lpsequence',get_string('lp_sequence', 'local_learningplan'),
				array('&nbsp;&nbsp;'), false);
	        $mform->addHelpButton('lpsequence','sequence','local_learningplan');

			$mform->addElement('text', 'open_points', get_string('points','local_learningplan'));
	        $mform->addHelpButton('open_points', 'open_pointslearningpath', 'local_learningplan');
	        $mform->setType('open_points', PARAM_INT);
	       
			$categorycontext = (new \local_learningplan\lib\accesslib())::get_module_context();			
            $mform->addElement('filepicker', 'summaryfile', get_string('learning_path_summary_file','local_learningplan'), null,
            array('maxbytes' => 2048000, 'accepted_types' => ['.jpg','.jpeg','.png','.gif']));
        $mform->addHelpButton('summaryfile', 'learningpaths', 'local_learningplan');
		}else if($form_status == 1){
			$costcenterid = explode('/', $open_path)[1];
            get_custom_categories($costcenterid,$mform,$moduletype = 'learningplan');
            //certificate
            $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='open_path',$costcenterpath=$open_path);
            if($certificate_plugin_exist){
                $checkboxes = array();
                $checkboxes[] = $mform->createElement('advcheckbox', 'map_certificate', null, '', array(),array(0,1));
                $mform->addGroup($checkboxes, 'map_certificate', get_string('add_certificate', 'local_learningplan'), array(' '), false);
                $mform->addHelpButton('map_certificate', 'add_certificate', 'local_learningplan');
                $select = array(null => get_string('select_certificate','local_learningplan'));
                $certificatesql = "SELECT id,name FROM {tool_certificate_templates}
                                    WHERE 1=1 $costcenterpathconcatsql ";
                $cert_templates = $DB->get_records_sql_menu($certificatesql);              
                $certificateslist = $select + $cert_templates;

                $mform->addElement('select',  'certificateid', get_string('certificate_template','local_learningplan'), $certificateslist);
                $mform->addHelpButton('certificateid', 'certificate_template', 'local_learningplan');
                $mform->setType('certificateid', PARAM_INT);
                $mform->hideIf('certificateid', 'map_certificate', 'neq', 1);
            }
            $mform->addElement('hidden', 'open_costcenterid', $org);
            $mform->setType('open_costcenterid', PARAM_RAW);

	        $editoroption = [
	        'maxfiles' => EDITOR_UNLIMITED_FILES,
	        'trust' => false,
	        'context' => (new \local_learningplan\lib\accesslib())::get_module_context(),
	        'noclean' => true,
	        'subdirs' => false,
	        'autosave'=>false
	    	];
			$mform->addElement('editor','description', get_string('description'), null, $editoroption);
	        $mform->setType('description', PARAM_RAW);
	        $mform->addHelpButton('description','descript','local_learningplan');
            //skill related fields---------------------------------------------------------
            $skillselect = array(0 => get_string('select_skill','local_learningplan'));

            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='open_path',$costcenterpath=$open_path);
   
               $skillcostcentersql = "SELECT id,name FROM {local_skill}
                                   WHERE 1=1 $costcenterpathconcatsql ";
   
   
               $skills = $DB->get_records_sql_menu($skillcostcentersql);
   
          
               if(!empty($skills)){
                   $skillselect = $skillselect+$skills;
               }
   
               $mform->addElement('select',  'open_skill', get_string('open_skillonlineexam','local_learningplan'), $skillselect);
               $mform->addHelpButton('open_skill', 'open_skillonlineexam', 'local_learningplan');
               $mform->setType('open_skill', PARAM_INT);
   
               $levelselect = array(0 => get_string('select_level','local_learningplan'));
   
               $levelsql = "SELECT id,name FROM {local_course_levels}
                                   WHERE 1=1 $costcenterpathconcatsql ";
   
               $levels = $DB->get_records_sql_menu($levelsql);
   
               if(!empty($levels)){
                   $levelselect = $levelselect+$levels;
               }
               $mform->addElement('select',  'open_level', get_string('open_levelonlineexam','local_learningplan'), $levelselect);
               $mform->addHelpButton('open_level', 'open_levelonlineexam', 'local_learningplan');
               $mform->setType('open_level', PARAM_INT);
            //skill related fields ends here---------------------------------------------------------

    	}else if($form_status == 2){
            $mform->addElement('hidden', 'open_costcenterid', $org);
            $mform->setType('open_costcenterid', PARAM_RAW);
            local_costcenter_get_hierarchy_fields($mform, $this->_ajaxformdata, $this->_customdata,range(2,HIERARCHY_LEVELS), true, 'local_costcenter', $categorycontext, $multiple = false);			
            $functionname ='globaltargetaudience_elementlist';

            if(function_exists($functionname)) {
                $costcenterfields = local_costcenter_get_fields();
                $firstdepth = current($costcenterfields);
                $mform->modulecostcenterpath =  $this->_customdata[$firstdepth];
                $functionname($mform,array('group','designation', 'hrmsrole', 'location'));
            }
            $lplan->module = 'learningplan';
            local_users_get_custom_userprofilefields($mform,$lplan,'local_learningplan'); 
    	}
        $mform->disable_form_change_checker();
    }

    public function validation($data, $files) {
       
        $errors = array();
		global $DB;
	    $errors = parent::validation($data, $files);
		if($data['enddate'] < $data['startdate']){
	        $errors['enddate'] = get_string('startdategreaterenddate','local_learningplan');
		}
		if($data['form_status']==0){
			if(empty(trim($data['name']))){
				$errors['name'] = get_string('provide_valid_name', 'local_learningplan');
			}
			if(empty(trim($data['shortname']))&&$data['id']=='0'){
                $errors['shortname'] = get_string('provide_valid_shortname','local_learningplan');
			}
        	if ($lplan = $DB->get_record('local_learningplan', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
		    	if (($data['id']=='0') || $lplan->id != $data['id']) {
				 	$errors['shortname'] = get_string('unameexists','local_learningplan');
            	}
			}
		}
		if($data['form_status']==1){
			if($data['map_certificate'] == 1 && empty($data['certificateid'])){
                $errors['certificateid'] = get_string('err_certificate','local_learningplan');
			}
		}		
		return $errors;
    }
}
