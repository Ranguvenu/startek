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

/**
 * local local_costcenter
 *
 * @package    local_costcenter
 * @copyright  2019 eAbyas <eAbyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_costcenter\form;
use core;
use moodleform;
use context_system;
use core_component;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
class organization_form extends moodleform { /*costcenter creation form*/

    public function definition() {
        global $USER, $CFG,$DB,$PAGE;
        require_once($CFG->dirroot . '/lib/adminlib.php');
        $costcenter = new \costcenter();
        $corecomponent = new core_component();
        $costcenterquerylib = new \local_costcenter\querylib();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $parentid = $this->_customdata['parentid']; 
        $formtype = $this->_customdata['formtype'];
        $headstring = $this->_customdata['headstring'];
        $categorycontext = (new \local_costcenter\lib\accesslib())::get_module_context();

        if($formtype != 'organization'){

            if($formtype == 'department'){
                $range=range(1,1);
            }else if($formtype == 'subdepartment'){
                $range=range(1,2);
            }else if($formtype == 'subsubdepartment'){
                $range=range(1,3);
            }else if($formtype == 'subsubsubdepartment'){
                $range=range(1,4);
            }
            local_costcenter_organization_hierarchy_fields($mform, $this->_ajaxformdata, $this->_customdata,$range, false, 'local_costcenter', $categorycontext, $multiple = false, $prefix = '',$id);

        }else{
            $mform->addElement('hidden', 'parentid', 0);
            $mform->setType('parentid', PARAM_INT);
        }

        $mform->addElement('text', 'fullname', get_string('costcentername', 'local_costcenter'), array());
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('missingcostcentername', 'local_costcenter'), 'required', null, 'client');
        if(!empty($id)){
            $mform->addElement('static', 'shortname_static', get_string('shortname','local_costcenter'), 'maxlength="100" size="20"');
            
            $mform->addElement('hidden', 'shortname');    
            $mform->setType('shortname', PARAM_TEXT);
        }else{
            if($formtype == 'organization'){

                $mform->addElement('text', 'shortname', get_string('shortname','local_costcenter'), 'maxlength="100" size="20"');

                $mform->addRule('shortname', get_string('shortnamecannotbeempty', 'local_costcenter'), 'required', null, 'client');

                $mform->setType('shortname', PARAM_TEXT);

            }elseif($formtype == 'department' || $formtype == 'subdepartment' || $formtype == 'subsubdepartment' || $formtype == 'subsubsubdepartment'){
                $shortnamestatic = (isset($this->_ajaxformdata['concatshortname'])) ? $this->_ajaxformdata['concatshortname'] : '';

                if ($this->_customdata['open_costcenterid'] > 0) {

                    try {
                        $shortnamestatic = $costcenterquerylib->costcenter_field('shortname', array('id' => $this->_customdata['open_costcenterid']));
                    } catch (Exception $e) {
                        $shortnamestatic = '';
                    }
                }

                $shortname = array();
                $shortname[] = $mform->createElement('hidden',  'concatshortname',$shortnamestatic);
                $shortname[] = $mform->createElement('static',  'shortnamestatic','','<span class="shortnamestatic">'.$shortnamestatic.'</span>_');
                $shortname[] = $mform->createElement('text', 'shortname','', 'maxlength="100" size="20"');
                $mform->addGroup($shortname,  'groupshortname',  get_string('shortname','local_costcenter'),  array(''),  false);
                $mform->addRule('groupshortname', get_string('shortnamecannotbeempty', 'local_costcenter'), 'required', null, 'client');
            }
        }

        $attributes = array('rows' => '8', 'cols' => '40');

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden',  'formtype',  $formtype);
        $mform->setType('formtype', PARAM_TEXT);

        $mform->addElement('hidden',  'headstring', $headstring);
        $mform->setType('headstring', PARAM_TEXT);

        $mform->addElement('hidden', 'timecreated', time());
        $mform->setType('timecreated', PARAM_RAW);
        
        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_RAW);

        if($formtype == 'organization' || $formtype =='department'){
            $theme = $costcenter->get_theme();
            $theme_plugin_exist = $corecomponent::get_plugin_directory('theme', $theme);
            if(!empty($theme_plugin_exist)){

                $logoupload = array('maxbytes'     => $CFG->maxbytes,
                                  'subdirs'        => 0,
                                  'maxfiles'       => 1,
                                  'accepted_types' => 'web_image');
                $mform->addElement('filemanager', 'costcenter_logo', get_string('costcenter_logo', 'local_costcenter'), '', $logoupload);

                //brand_color
                $mform->addElement('html','<div class="brandcolor">');
                $mform->addElement('colorpicker', 'brand_color', 'Primary Color', 'local_costcenter');
                $mform->setType('brand_color', PARAM_RAW);

                //button color
                $mform->addElement('colorpicker', 'button_color','Secondary Color', 'local_costcenter');
                $mform->setType('button_color', PARAM_RAW);
                //hover_color
                $mform->addElement('colorpicker', 'hover_color','Hover_color', 'local_costcenter');
                $mform->setType('hover_color', PARAM_RAW);
                $mform->addElement('html','</div>');
            }
        }
        if($formtype == 'organization'){
             //course event color
           $mform->addElement('html','<div class="brandcolor">');
           $mform->addElement('colorpicker', 'courseevent_color', 'Course Event Color', 'local_costcenter');
           $mform->setType('courseevent_color', PARAM_RAW);

         //classroom event color
           $mform->addElement('colorpicker', 'classroomevent_color','Classroom Event Color', 'local_costcenter');
           $mform->setType('classroomevent_color', PARAM_RAW);
        }
        $submit = ($id > 0) ? get_string('update_costcenter', 'local_costcenter') : get_string('create', 'local_costcenter');
        $this->add_action_buttons('false', $submit);
    }

    /**
     * validates costcenter name and returns instance of this object
     *
     * @param [object] $data 
     * @param [object] $files 
     * @return costcenter validation errors
     */
    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);
        $costcenterquerylib = new \local_costcenter\querylib();
        // fix for OL01 issue by mahesh
        if(empty(trim($data['shortname']))){
            $errors['groupshortname'] = get_string('shortnamecannotbeempty', 'local_costcenter');
        }
        if(empty(trim($data['fullname']))){
            $errors['fullname'] = get_string('fullnamecannotbeempty', 'local_costcenter');
        }
        // OL01 fix ends.
        $shortname = !empty(trim($data['concatshortname'])) ? trim($data['concatshortname']).'_'.trim($data['shortname']) : trim($data['shortname']);
        if ($costcenterquerylib->costcenter_exist(array('shortname' => $shortname), '*', IGNORE_MULTIPLE)) {
            $costcenter = $costcenterquerylib->costcenter_record(array('shortname' => $shortname), '*', IGNORE_MULTIPLE);
            if (empty($data['id']) || $costcenter->id != $data['id']) {
                if($data['parentid'] == 0){
                    $errors['groupshortname'] = get_string('shortnametakenlp', 'local_costcenter', $costcenter->shortname);
                }else{
                    $errors['groupshortname'] = get_string('shortnametakenlp', 'local_costcenter', $costcenter->shortname);
                }
            }
        }
        return $errors;
    } 
}
