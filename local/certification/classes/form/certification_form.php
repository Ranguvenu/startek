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
require_once($CFG->libdir . '/completionlib.php');
use context_system;
use local_certification\local\querylib;
use moodleform;
use core_component;
class certification_form extends moodleform {
    public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post',
        $target = '', $attributes = null, $editable = true, $formdata = null) {
        $this->formstatus = array(
            'manage_certification' => get_string('manage_certification', 'local_certification'),
            'location_date' => get_string('location_date', 'local_certification'),
            'certification_misc' => get_string('assign_course', 'local_certification'),
            'target_audience' => get_string('target_audience', 'local_users'),
        );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }

    public function definition() {
        global $CFG, $USER, $PAGE, $DB;
        $querieslib = new querylib();
        $mform = &$this->_form;
        $renderer = $PAGE->get_renderer('local_certification');
        $context = context_system::instance();
        $formstatus = $this->_customdata['form_status'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];

        //$mform->addElement('header', 'general', $this->formstatus[$formheader]);

        $mform->addElement('hidden', 'id', $id, array('id' => 'certificationid'));
        $mform->setType('id', PARAM_INT);
        
        $core_component = new core_component();
        if ($formstatus == 0) {
            $querieslib = new querylib();
            $mform->addElement('text', 'name', get_string('certification_name', 'local_certification'), array());
            if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('name', PARAM_TEXT);
            } else {
                $mform->setType('name', PARAM_CLEANHTML);
            }
            $mform->addRule('name', null, 'required', null, 'client');

            if (is_siteadmin() || ((has_capability('local/certification:manage_multiorganizations', context_system::instance()) ||has_capability('local/costcenter:manage_multiorganizations', context_system::instance()))) ) {

                $costcenters = array();
                $costcenterslist = $this->_ajaxformdata['costcenter'];
                if (!empty($costcenterslist)) {
                    $costcenterslist = $costcenterslist;
                } else if ($id > 0) {
                    $costcenterslistsql = "SELECT cc.id
                                             FROM {local_costcenter} cc
                                             JOIN {local_certification} c ON c.costcenter = cc.id
                                             AND cc.parentid = 0 AND cc.visible = 1 AND
                                             c.id = :certificationid";
                    $costcenterslist = $DB->get_field_sql($costcenterslistsql, array('certificationid' => $id));
                }
                if (!empty($costcenterslist)) {
                    $costcenterslist = $DB->get_records_menu('local_costcenter',
                        array('visible' => 1, 'parentid' => 0, 'id' => $costcenterslist),
                        'id', 'id, fullname');
                    $costcenters = array(null => get_string('select_costcenter',
                        'local_certification')) + $costcenterslist;
                }

                $options = array(
                    'ajax' => 'local_certification/form-options-selector',
                    'data-contextid' => $context->id,
                    'data-action' => 'certification_costcenter_selector',
                    'data-options' => json_encode(array('id' => $id, 'depth' => 1, 'parnetid' => 0)),
                    'class' => 'organizationselect',
                    'data-class' => 'organizationselect'
                );

                $mform->addElement('autocomplete', 'costcenter',
                    get_string('costcenter', 'local_certification'), $costcenters, $options);
                $mform->addRule('costcenter', get_string('errororganization', 'local_users'), 'required', null, 'client');
               // $mform->addRule('costcenter', null, 'required', null, 'client');
                $mform->setType('costcenter', PARAM_INT);
            } else {
                $mform->addElement('hidden', 'costcenter',get_string('costcenter', 'local_certification'),
                    array( 'data-class' => 'organizationselect'));
                $mform->setType('costcenter', PARAM_INT);
                $mform->setDefault('costcenter', $USER->open_costcenterid);
            }
            $type = array(1 => get_string('certification', 'local_certification'),
                2 => get_string('learningplan', 'local_certification'),
                3 => get_string('certification', 'local_certification'),
            );
            $mform->addElement('hidden', 'type', get_string('type', 'local_certification'), $type,
             array());
            $mform->addRule('type', null, 'required', null, 'client');
            $mform->setType('type', PARAM_INT);

             //*OPEN LMSOL-333 Employee_Search_Certification*//
             
                $manageapproval = array();
                $manageapproval[] = $mform->createElement('radio', 'approvalreqd', '', get_string('yes'), 1, $attributes);
                $manageapproval[] = $mform->createElement('radio', 'approvalreqd', '', get_string('no'), 0, $attributes);
                $mform->addGroup($manageapproval, 'approvalreqd',
                    get_string('need_manage_approval', 'local_certification'),
                    array('&nbsp;&nbsp;'), false);
                $mform->addHelpButton('approvalreqd','need_manager_approval','local_certification');
            
            //*OPEN LMSOL-333 Employee_Search_Certification*//
        
            $allowmultisession = array();
            $allowmultisession[] = $mform->createElement('radio', 'allow_multi_session', '',
             get_string('fixed', 'local_certification'), 1, $attributes);
            $allowmultisession[] = $mform->createElement('radio', 'allow_multi_session', '',
             get_string('custom', 'local_certification'), 0, $attributes);
            $mform->addGroup($allowmultisession, 'radioar',
                get_string('allow_multi_session', 'local_certification'), array('&nbsp;&nbsp;'),
                 false);
            $mform->addHelpButton('radioar','allow_multiple_sessions','local_certification');
            //$mform->addRule('radioar', null, 'required');

           // $mform->addElement('hidden', 'mincapacity', 0);
           // $mform->setType('mincapacity', PARAM_INT);

            $mform->addElement('text', 'capacity', get_string('capacity', 'local_certification'),
             array());
            $mform->setType('capacity', PARAM_RAW);

            $mform->addHelpButton('capacity','capacity_check','local_certification');
           // $mform->addRule('capacity', null, 'required', null, 'client');
           // $mform->addRule(array('capacity', 'mincapacity'), get_string('capacity_positive',
             //'local_certification'), 'compare', 'gt', 'client');

            $trainers = array();
            $trainerslist = $this->_ajaxformdata['trainers'];
            if (!empty($trainerslist)) {
                $trainerslist = $trainerslist;
            } else if ($id > 0) {
                $trainerslist = $DB->get_records_menu('local_certification_trainers',
                    array('certificationid' => $id), 'id', 'id, trainerid');
            }
            if (!empty($trainerslist)) {
                $trainers = $querieslib->get_user_department_trainerslist(false, false,
                    $trainerslist);
            }
            $options = array(
                'ajax' => 'local_certification/form-options-selector',
                'multiple' => true,
                'data-contextid' => $context->id,
                'data-action' => 'certification_trainer_selector',
                'data-options' => json_encode(array('id' => $id,'organizationselect' => 'organizationselect')),
            );

            $mform->addElement('autocomplete', 'trainers', get_string('trainers', 'local_certification'), $trainers, $options);
            $mform->addHelpButton('trainers','traning','local_certification');

            $mform->addElement('text', 'open_points', get_string('points','local_certification'));
            $mform->addHelpButton('open_points', 'open_pointscertificate', 'local_certification');
            $mform->setType('open_points', PARAM_INT);

           // $mform->addRule('trainers', get_string('required'), 'required', null, 'client');


            $mform->addElement('date_time_selector', 'startdate', get_string('startdate',
                'local_certification'), array('optional' => true));
            //$mform->addRule('startdate', null, 'required', null, 'client');

            $mform->addElement('date_time_selector', 'enddate', get_string('enddate',
                'local_certification'), array('optional' => true));
            //$mform->addRule('enddate', null, 'required', null, 'client');

            // tags
            // $mform->addElement('tags', 'tags', get_string('tags'), array('itemtype' => 'certification', 'component' => 'local_certification'));

            //certificate
            $certificate_plugin_exist = $core_component::get_plugin_directory('local', 'certificates');
            if($certificate_plugin_exist){
                $checkboxes = array();
                $checkboxes[] = $mform->createElement('advcheckbox', 'map_certificate', null, '', array(),array(0,1));
                $mform->addGroup($checkboxes, 'map_certificate', get_string('add_certificate', 'local_classroom'), array(' '), false);
                $mform->addHelpButton('map_certificate', 'add_certificate', 'local_classroom');

                $select = array(null => get_string('select_certificate','local_classroom'));

                if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $context)){
                    $cert_templates = $DB->get_records_menu('local_certificate',array(),'name', 'id,name');
                }else{
                    $cert_templates = $DB->get_records_menu('local_certificate',array('costcenter'=>$USER->open_costcenterid),'name', 'id,name');
                }
                $certificateslist = $select + $cert_templates;

                $mform->addElement('select',  'certificateid', get_string('certificate_template','local_classroom'), $certificateslist);
                $mform->addHelpButton('certificateid', 'certificate_template', 'local_classroom');
                $mform->setType('certificateid', PARAM_INT);
                $mform->hideIf('certificateid', 'map_certificate', 'neq', 1);
            }

        } else if ($formstatus == 1) {

            $allowmultisession = array();
            $allowmultisession[] = $mform->createElement('radio', 'institute_type', '',
                get_string('internal', 'local_certification'), 1, $attributes);
            $allowmultisession[] = $mform->createElement('radio', 'institute_type', '',
                get_string('external', 'local_certification'), 2, $attributes);
            $mform->addGroup($allowmultisession, 'radioar', get_string('clrm_location_type',
                'local_certification'), array(' '), false);
            //$mform->addRule('radioar', null, 'required');


            $certificationlocations =  array(null => get_string('select_institutions',
                        'local_certification'));
            $instituteid = $this->_ajaxformdata['instituteid'];
            //print_object($instituteid);
            if (!empty($instituteid)) {
                $instituteid = $instituteid;
            } else if ($id > 0) {
                $instituteid = $DB->get_field('local_certification', 'instituteid',
                    array('id' => $id));
            }
            if (!empty($instituteid)) {
                $certificationlocations =$DB->get_records_menu('local_location_institutes',
                 array('id' => $instituteid), 'id', 'id, fullname');

            }
            $options = array(
                'ajax' => 'local_certification/form-options-selector',
                'data-contextid' => $context->id,
                'data-action' => 'certification_institute_selector',
                'data-options' => json_encode(array('id' => $id)),
                'data-institute_type' => 'institute_type'
            );

            $mform->addElement('autocomplete', 'instituteid', get_string('certification_location',
             'local_certification'),$certificationlocations, $options);
            //$mform->addRule('instituteid', null, 'required', null, 'client');

            $mform->addElement('date_time_selector', 'nomination_startdate',
                get_string('nomination_startdate', 'local_certification'),
                array('optional' => true));
            //$mform->addRule('nomination_startdate', null, 'required', null, 'client');

            $mform->addElement('date_time_selector', 'nomination_enddate',
             get_string('nomination_enddate', 'local_certification'),
             array('optional' => true));

            //$mform->addRule('nomination_enddate', null, 'required', null, 'client');

        } else if ($formstatus == 2) {



            $mform->addElement('filepicker', 'certificationlogo',get_string('certificationlogo','local_certification'), null,
                array('maxbytes' => 2048000, 'accepted_types' => '.jpg'));

            $editoroptions = array(
                'noclean' => false,
                'autosave' => false
            );
            $mform->addElement('editor', 'cr_description', get_string('description',
                'local_certification'), null, $editoroptions);
            $mform->setType('cr_description', PARAM_RAW);
            $mform->addHelpButton('cr_description', 'description', 'local_certification');

        }else if ($formstatus == 3) {
            // OL-1042 Add Target Audience to Certifications//
            
            // if ((!is_siteadmin() && (((!has_capability('local/certification:manage_multiorganizations', context_system::instance()) &&! has_capability('local/costcenter:manage_multiorganizations', context_system::instance()))) && has_capability('local/certification:managecertification', $context)&&(!has_capability('local/certification:manage_owndepartments',$context)&&
            //                              !has_capability('local/costcenter:manage_owndepartments',$context))))) {
            if((is_siteadmin() || has_capability('local/certification:manage_multiorganizations', $context)) || (has_capability('local/certification:managecertification', $context) 
                && !has_capability('local/certification:manage_owndepartments',$context) 
                && !has_capability('local/costcenter:manage_owndepartments',$context)
            )){
                $departments = array();
                $departmentslist = $this->_ajaxformdata['department'];

                $params = array();
                if (!empty($departmentslist)) {
                    $departmentslist = $departmentslist;
                } else if ($id > 0) {
                    $departmentlist = $DB->get_field('local_certification', 'department', array('id' => $id));
                    $departmentslist = explode(', ', $departmentlist);
                }
                if (!empty($departmentslist)) {

                    //list($departmentslistsql, $departmentslistparams) = $DB->get_in_or_equal($departmentslist, SQL_PARAMS_NAMED, 'crdept');
                    //$params = array_merge($params, $departmentslistparams);
                    if (is_array($departmentslist)){
                        $departmentslist=implode(',',$departmentslist);
                    }
                    $params['visible'] = 1;
                    $params['depth'] = 2;
                    $departmentlistsql = "SELECT id, fullname
                                            FROM {local_costcenter}
                                           WHERE visible = :visible AND depth = :depth";
                    if(!empty($departmentslist)) {
                        $departmentlistsql .= " AND id in ($departmentslist)";
                    }
                    $departmentlist = $DB->get_records_sql_menu($departmentlistsql, $params);
                    $departments = array(-1 => get_string('all')) + $departmentlist;
                }

                $options = array(
                    'ajax' => 'local_certification/form-options-selector',
                    'multiple' => False,
                    'data-contextid' => $context->id,
                    'data-action' => 'certification_costcenter_selector',
                    'data-options' => json_encode(array('id' => $id, 'depth' => 2,
                        'organizationselect' => '.organizationselect', 'department' => true,
                    'organizationselect' => 'organizationselect')),
                    'class' => 'departmentselect'
                );

                $mform->addElement('autocomplete', 'department', get_string('department',
                    'local_certification'), $departments, $options);
                $mform->setType('department', PARAM_INT);
                
     //        }elseif (is_siteadmin() || ((!has_capability('local/certification:manage_multiorganizations', context_system::instance()) &&! has_capability('local/costcenter:manage_multiorganizations', context_system::instance())) && has_capability('local/certification:managecertification', $context)&&(has_capability('local/certification:manage_owndepartments',$context)||has_capability('local/costcenter:manage_owndepartments',$context)))) {
                
     //            //$mform->addElement('hidden', 'department', get_string('department',
     //            //    'local_certification'));
     //            //$mform->setType('department', PARAM_INT);
     //            //$mform->setDefault('department', $USER->open_departmentid);
     //                $options = array(
					// 	//'multiple' => true,
					// 	'class' => 'department_select'
					// );
					// $costcenter = $DB->get_field('local_certification','costcenter',array('id'=>$id));
					// $costcenter_department = $DB->get_field('local_certification','department',array('id'=>$id));
                   
					// if(empty($costcenter_department)){
					// 	$departmentslist=array($USER->open_departmentid=>$USER->open_departmentid);
					// }else{
					// 	if($costcenter_department!='-1'){
					// 		$departmentslist=explode(',',$costcenter_department);
					// 		$departmentslist=array_combine($departmentslist,$departmentslist);
					// 	}else{
     //                        $departmentslist=array('-1'=>'-1');
     //                    }
					
					// }
					
					// $mform->addElement('autocomplete', 'department', get_string('department'),$departmentslist,$options);
					// if(empty($costcenter_department)){
					//  $mform->setDefault('department', $USER->open_departmentid);
					// }
     //                if($costcenter_department=='-1'){
					//  $mform->setDefault('department',-1);
					// }
            }else{
                // $departmentlist = $DB->get_field('local_certification', 'department', array('id' => $id));
                $mform->addElement('hidden',  'department', $USER->open_departmentid, array('id' => 'id_department'));
                $mform->setConstant('department', $USER->open_departmentid);
                $mform->setType('department', PARAM_RAW);
            }
            if(is_siteadmin() || has_capability('local/certification:managecertification', $context)){
                $departments = array();
                $subdepartment = $this->_ajaxformdata['subdepartment'];

                $params = array();
                if (!empty($subdepartment)) {
                    $subdepartmentslist = $subdepartment;
                } else if ($id > 0) {
                    $subdepartmentlist = $DB->get_field('local_certification', 'subdepartment', array('id' => $id));
                    $subdepartmentslist = explode(', ', $subdepartmentlist);
                }
                if (!empty($subdepartmentslist)) {
                    if (is_array($subdepartmentslist)){
                        $subdepartmentslist=implode(',',$subdepartmentslist);
                    }
                    // $organisation = $DB->get_field('local_classroom', 'costcenter', array('id' => $id));
                    $departments = $DB->get_field('local_certification', 'department', array('id' => $id));
                    
                    $subdepartmentlistsql = "SELECT id, fullname
                                            FROM {local_costcenter}
                                           WHERE 1 = 1 ";
                    if(!empty($subdepartmentslist)) {
                        $arr_subdepartmentslist = explode(',', $subdepartmentslist);
                        list($subsql, $subparam) = $DB->get_in_or_equal($arr_subdepartmentslist, SQL_PARAMS_NAMED);
                        $subdepartmentlistsql .= " AND id $subsql ";
                        $params = $params + $subparam;
                    }else{
                        $subdepartmentlistsql .= " AND visible = :visible AND depth = :depth 
                        AND parentid IN (:parentid)";
                        $params['visible'] = 1;
                        $params['depth'] = 3;
                        $params['parentid'] = $departments;
                    }
                    
                    $subdepartmentlist = $DB->get_records_sql_menu($subdepartmentlistsql, $params);
                    $subdepartments = array(-1 => get_string('all')) + $subdepartmentlist;
                }

                $options = array(
                    'ajax' => 'local_certification/form-options-selector',
                    'multiple' => False,
                    'data-contextid' => $context->id,
                    'data-action' => 'certification_subdepartment_selector',
                    'data-options' => json_encode(array('id' => $id, 'depth' => 3,
                        'organizationselect' => '.organizationselect', 'subdepartment' => true)),
                    'class' => 'subdepartmentselect'
                );
                //'organizationselect' => 'organizationselect'

                $mform->addElement('autocomplete', 'subdepartment', get_string('subdepartment',
                    'local_costcenter'), $subdepartments, $options);
                $mform->setType('subdepartment', PARAM_INT);
            }
            
            $core_component = new core_component();
            $users_plugin_exist = $core_component::get_plugin_directory('local','users');
            if ($users_plugin_exist) {
                require_once($CFG->dirroot . '/local/users/lib.php');
                $functionname ='globaltargetaudience_elementlist';
                 if(function_exists($functionname)) {
                    $functionname($mform,array('group','hrmsrole','designation','location'));
                }
            }
          // OL-1042 Add Target Audience to Certifications//
        }

        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        global $CFG, $DB, $USER;

        $errors = parent::validation($data, $files);

         $data_db = $DB->get_record_sql('SELECT id,startdate,enddate,capacity FROM {local_certification}
                WHERE id = ' . $data['id']);
         //print_object($data_db);
         //print_object($data);
        if (isset($data['startdate']) && $data['startdate'] &&
                isset($data['enddate']) && $data['enddate']) {
            if ($data['enddate'] <= $data['startdate']) {
                $errors['enddate'] = get_string('enddateerror', 'local_certification');
            }
        }
        
       
        if(isset($data['name']) &&empty(trim($data['name']))){
            $errors['name'] = get_string('valnamerequired','local_certification');
        }
        if(isset($data['name']) && strlen(trim($data['name'])) > 150){
            $errors['name'] = get_string('namelengthexceeded','local_certification');
        }
        
        if(isset($data['institute_type'])&&$data['institute_type']!=0&&$data['instituteid']==0){
            $errors['instituteid'] = get_string('vallocationrequired','local_certification');
        }elseif(isset($data['institute_type'])&&$data['institute_type']!=0&&$data['instituteid']!=0){
            $institutessql = "SELECT id
                                FROM {local_location_institutes}
                               WHERE institute_type = :institute_type and id=:instituteid";

            $params['institute_type'] = $data['institute_type'];           
            $params['instituteid'] = $data['instituteid'];  

            $institutes = $DB->record_exists_sql($institutessql, $params);
            if(!$institutes){
                $errors['instituteid'] = get_string('vallocation','local_certification');
            }
        }
        
	    if(isset($data['capacity']) &&!empty(trim($data['capacity']))){
                // print_object($data);
	    		if(!is_numeric(trim($data['capacity']))){
	    			$errors['capacity'] = get_string('numeric','local_certification');
	    		}
                if(is_numeric(trim($data['capacity']))&&trim($data['capacity'])<0){
	    			$errors['capacity'] = get_string('positive_numeric','local_certification');
	    		}
		}
        
		
        if($data['id']>0){
            $allocatedseats=$DB->count_records('local_certification_users',array('certificationid'=>$data['id'])) ;
            if($data['capacity']!=NULL&&trim($data['capacity'])<$allocatedseats){
                        $errors['capacity'] = get_string('capacity_enroll_check','local_certification');
            }
        }
        if ((isset($data['nomination_startdate']) && $data['nomination_startdate'])||
                 (isset($data['nomination_enddate']) && $data['nomination_enddate'])&&$data['id']>0) {

            $data['startdate']= $data_db->startdate;
            $data['enddate']= $data_db->enddate;

            if ($data['nomination_startdate'] > $data['startdate']&&$data['startdate']>0) {
                $errors['nomination_startdate'] = get_string('nomination_startdateerror', 'local_certification');
            }
            if ($data['nomination_enddate'] > $data['startdate']&&$data['startdate']>0) {
                $errors['nomination_enddate'] = get_string('nomination_enddateerror', 'local_certification');
            }
            elseif ($data['nomination_enddate'] <= $data['nomination_startdate']&&$data['nomination_enddate']>0&&$data['nomination_startdate']>0) {
                $errors['nomination_enddate'] = get_string('nomination_error', 'local_certification');
            }
        }

        if ($data['map_certificate'] == 1 && empty($data['certificateid'])){
            $errors['certificateid'] = get_string('err_certificate', 'local_certification');
        }

        return $errors;
    }

    public function set_data($components) {
        global $DB;
        $context = context_system::instance();
        if ($components->form_status == 0) {
            $data = $DB->get_record('local_certification', array('id' => $components->id));
            //populate tags
            // $data->tags = \local_tags_tag::get_item_tags_array('local_certification', 
            //     'certification', $components->id);
            $params = array();
            $params['certificationid'] = $components->id;

            $sql = " SELECT ct.trainerid AS crtrid, ct.trainerid
                      FROM {local_certification} c
                      JOIN {local_certification_trainers} ct ON ct.certificationid = c.id
                     WHERE c.id = :certificationid";
            $trainers = $DB->get_records_sql_menu($sql, $params);
            $data->trainers = $trainers;

            if(!empty($data->certificateid)){
                $data->map_certificate = 1;
            }

        } else if ($components->form_status == 1) {
         
            $data = $DB->get_record_sql('SELECT id, institute_type, instituteid,
                nomination_startdate, nomination_enddate,startdate,enddate FROM {local_certification}
                WHERE id = ' . $components->id);
            if($data->instituteid==0){
                $data->instituteid=null;
            }

        } else if ($components->form_status == 2) {
            $data = $DB->get_record_sql('SELECT id, description, approvalreqd,
             certificationlogo, nomination_startdate, nomination_enddate
             FROM {local_certification} WHERE id = ' . $components->id);
            $data->cr_description['text'] = $data->description;
            $draftitemid = file_get_submitted_draft_itemid('certificationlogo');
            file_prepare_draft_area($draftitemid, $context->id, 'local_certification', 'certificationlogo', $data->certificationlogo, null);
            $data->certificationlogo = $draftitemid;
        }else if ($components->form_status == 3) {
             // OL-1042 Add Target Audience to Certifications//
            $data = $DB->get_record_sql('SELECT id, open_group, open_hrmsrole,
             open_designation, open_location,department, subdepartment
             FROM {local_certification} WHERE id = ' . $components->id);
            // if($data->department==-1){
            //     $data->department=null;
            // }
            $data->department = (!empty($data->department)) ? array_diff(explode(',',$data->department), array('')) :array(NULL=>NULL);
            $data->subdepartment =(!empty($data->subdepartment)) ? array_diff(explode(',',$data->subdepartment), array('')) :array(NULL=>NULL);
            $data->open_group =(!empty($data->open_group)) ? array_diff(explode(',',$data->open_group), array('')) :array(NULL=>NULL);
            $data->open_hrmsrole =(!empty($data->open_hrmsrole)) ? array_diff(explode(',',$data->open_hrmsrole), array('')) :array(NULL=>NULL);
            $data->open_designation =(!empty($data->open_designation)) ? array_diff(explode(',',$data->open_designation), array('')) :array(NULL=>NULL);
            $data->open_location =(!empty($data->open_location)) ? array_diff(explode(',',$data->open_location), array('')) :array(NULL=>NULL);
             // OL-1042 Add Target Audience to Certifications//
        }
        
        parent::set_data($data);
    }
}