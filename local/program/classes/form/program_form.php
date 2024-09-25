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
 * @subpackage local_program
 */

namespace local_program\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/custom_category/lib.php');
require_once($CFG->libdir . '/completionlib.php');
use local_program\local\querylib;
use moodleform;
use core_component;

class program_form extends moodleform {
    public $formstatus;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $formdata = null) {

        $this->formstatus = array(
            'generaldetails' => get_string('generaldetails', 'local_program'),
            'otherdetails' => get_string('otherdetailsdetails', 'local_program'),
            'target_audience' => get_string('target_audiencedetails', 'local_program'),
            );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }

    public function definition() {
        global $CFG, $USER, $PAGE, $DB;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $renderer = $PAGE->get_renderer('local_program');
        $form_status = $this->_customdata['form_status'];
        $open_path = $this->_customdata['open_path'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];

        $categorycontext = (new \local_program\lib\accesslib())::get_module_context();

        $mform->addElement('hidden', 'id', $id, array('id' => 'programid'));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'form_status', $form_status);
        $mform->setType('form_status', PARAM_INT);

        $core_component = new core_component();

        if($form_status == 0){

            $querieslib = new querylib();

            local_costcenter_get_hierarchy_fields($mform, $this->_ajaxformdata, $this->_customdata,range(1,1), false, 'local_costcenter', $categorycontext, $multiple = false);

            $mform->addElement('text', 'name', get_string('program_name', 'local_program'), array());
            if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('name', PARAM_TEXT);
            } else {
                $mform->setType('name', PARAM_CLEANHTML);
            }
            $mform->addRule('name', null, 'required', null, 'client');

            $selfenrol = array();
            $selfenrol[] = $mform->createElement('radio', 'selfenrol', '', get_string('yes'), 1, $attributes);
            $selfenrol[] = $mform->createElement('radio', 'selfenrol', '', get_string('no'), 0, $attributes);
            $mform->addGroup($selfenrol, 'selfenrol', get_string('selfenrol', 'local_program'), array('&nbsp;&nbsp;'), false);
            $mform->addHelpButton('selfenrol','selfenroll','local_program');            
            
            $manageapproval = array();
            $manageapproval[] = $mform->createElement('radio', 'approvalreqd', '', get_string('yes'), 1, $attributes);
            $manageapproval[] = $mform->createElement('radio', 'approvalreqd', '', get_string('no'), 0, $attributes);
            $mform->addGroup($manageapproval, 'approvalreqd', get_string('need_manage_approval', 'local_program'), array('&nbsp;&nbsp;'), false);
            $mform->hideIf('approvalreqd', 'selfenrol', 'neq', '1');
            
            $mform->addElement('text', 'points', get_string('points','local_program'));
            $mform->addHelpButton('points', 'open_pointsprogram', 'local_program');
            $mform->setType('points', PARAM_INT);

            $mform->addElement('filepicker', 'programlogo',
                    get_string('programlogo', 'local_program'), null,
                    array('maxbytes' => 2048000, 'accepted_types' => '.jpg'));
            $mform->addHelpButton('programlogo','image','local_program');
            $editoroptions = array(
                'noclean' => false,
                'autosave' => false
            );
            $mform->addElement('editor', 'cr_description',
                    get_string('description', 'local_program'), null, $editoroptions);
            $mform->setType('cr_description', PARAM_RAW);
            $mform->addHelpButton('cr_description', 'description', 'local_program');


        }else if($form_status == 1){
            $costcenterid = explode('/', $open_path)[1];
            get_custom_categories($costcenterid,$mform,$moduletype = 'program');

            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='open_path',$costcenterpath=$open_path);

            $certificate_plugin_exist = $core_component::get_plugin_directory('tool', 'certificate');
            if($certificate_plugin_exist){
                $checkboxes = array();
                $checkboxes[] = $mform->createElement('advcheckbox', 'map_certificate', null, '', array(),array(0,1));
                $mform->addGroup($checkboxes, 'map_certificate', get_string('add_certificate', 'local_program'), array(' '), false);
                $mform->addHelpButton('map_certificate', 'add_certificate', 'local_program');

                $select = array(null => get_string('select_certificate','local_program'));

                $certificatesql = "SELECT id,name FROM {tool_certificate_templates}
                                    WHERE 1=1 $costcenterpathconcatsql ";

                $cert_templates = $DB->get_records_sql_menu($certificatesql);

                $certificateslist = $select + $cert_templates;

                $mform->addElement('select',  'certificateid', get_string('certificate_template','local_program'), $certificateslist);
                $mform->addHelpButton('certificateid', 'certificate_template', 'local_program');
                $mform->setType('certificateid', PARAM_INT);
                $mform->hideIf('certificateid', 'map_certificate', 'neq', 1);
            }
            $mform->addElement('hidden', 'open_costcenterid');
            $mform->setType('open_costcenterid', PARAM_INT);

            //skill related fields
            $skillselect = array(0 => get_string('select_skill','local_learningplan'));

            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='open_path',$costcenterpath=$open_path);
   
           $skillcostcentersql = "SELECT id,name FROM {local_skill}
                               WHERE 1=1 $costcenterpathconcatsql ";


           $skills = $DB->get_records_sql_menu($skillcostcentersql);

      
           if(!empty($skills)){
               $skillselect = $skillselect+$skills;
           }

           $mform->addElement('select',  'open_skill', get_string('open_skillonlineexam','local_learningplan'), $skillselect);
           $mform->addHelpButton('open_skill', 'open_skillonlineexam', 'local_program');
           $mform->setType('open_skill', PARAM_INT);

           $levelselect = array(0 => get_string('select_level','local_learningplan'));

           $levelsql = "SELECT id,name FROM {local_course_levels}
                               WHERE 1=1 $costcenterpathconcatsql ";

           $levels = $DB->get_records_sql_menu($levelsql);

           if(!empty($levels)){
               $levelselect = $levelselect+$levels;
           }
           $mform->addElement('select',  'open_level', get_string('open_levelonlineexam','local_learningplan'), $levelselect);
           $mform->addHelpButton('open_level', 'open_levelonlineexam', 'local_program');
           $mform->setType('open_level', PARAM_INT);
            //skill related fields ends here

        }else if($form_status == 2){

            local_costcenter_get_hierarchy_fields($mform, $this->_ajaxformdata, $this->_customdata,range(2,HIERARCHY_LEVELS), true, 'local_costcenter', $categorycontext, $multiple = false);
           
            $functionname = 'globaltargetaudience_elementlist';

            if(function_exists($functionname)) {
                $costcenterfields = local_costcenter_get_fields();
                $firstdepth = current($costcenterfields);
                $mform->modulecostcenterpath = $this->_customdata[$firstdepth];

                $functionname($mform,array('group','hrmsrole','designation','location'));
            }
            $program = new \stdclass();
            $program->id = $id;
            $program->module = 'program';
            local_users_get_custom_userprofilefields($mform,$program,'local_program'); //target audience
            
        }

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        global $CFG, $DB, $USER;

        $errors = parent::validation($data, $files);
        $form_status = $data['form_status'];
        if($form_status == 0){
            if (isset($data['name']) && empty(trim($data['name']))) {
                $errors['name'] = get_string('valnamerequired', 'local_program');
            }

        }elseif($form_status == 1){

            if ($data['map_certificate'] == 1 && empty($data['certificateid'])){
                $errors['certificateid'] = get_string('err_certificate', 'local_courses');
            }

         }
        return $errors;
    }

    public function set_data($components) {
        global $DB;
        $categorycontext =(new \local_program\lib\accesslib())::get_module_context();
        $data = $DB->get_record('local_program', array('id' => $components->id));
        if ($components->form_status == 0) {           
            if(!empty($data)){
                $data->cr_description = array();
                $data->cr_description['text'] = $data->description;
                $draftitemid = file_get_submitted_draft_itemid('programlogo');

                file_prepare_draft_area($draftitemid, $categorycontext->id, 'local_program', 'programlogo',
                    $data->programlogo, null);

                $data->programlogo = $draftitemid;
            }
        }
        if($components->form_status == 1) {
            if(!empty($data->certificateid)){
                $data->map_certificate = 1;
            }
            $costcenterid = explode('/',$data->open_path)[1];
            $sql = "SELECT id FROM {local_custom_fields} WHERE costcenterid =".$costcenterid ." AND parentid = 0";
            $parentid = $DB->get_records_sql($sql);
            if($parentid){
                $parentcat = [];
                foreach($parentid as $categoryid){
                    $parentcat[] = $categoryid->id;
                    $childcategories = $DB->get_field('local_category_mapped', 'category', array ('parentid' => $categoryid->id, 'moduletype' => 'program', 'moduleid' => $data->id));
                    $data->{'category_'.$categoryid->id} = $childcategories ? $childcategories : 0;
                }
                $data->parentid = implode(',', $parentcat);
            }
        }
        if($components->form_status == 2) {

            $data->open_group =(!empty($data->open_group)) ? array_diff(explode(',',$data->open_group), array('')) :array(NULL=>NULL);
            $data->open_designation =(!empty($data->open_designation)) ? array_diff(explode(',',$data->open_designation), array('')) :array(NULL=>NULL);

        }
        $data =  (array)$data;
        local_costcenter_set_costcenter_path($data);
        $data = (object)$data;
        parent::set_data($data);
    }
}
