<?php
namespace local_skillrepository\form;
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
 * @subpackage local_skillrepository
 */
use moodleform;
use context_system;
require_once(dirname(__FILE__) . '/../../../../config.php');
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/costcenter/lib.php');
class levelsform extends \moodleform {
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $costcenterid = $this->_customdata['open_costcenterid'];

        $id = $this->_customdata['id'];
        $context =(new \local_skillrepository\lib\accesslib())::get_module_context();
        
        if($id > 0)
        {
            $skilllevel = $DB->get_records_sql("SELECT id FROM {local_skill_levels} WHERE levelid = $id");
        }

        if($skilllevel && is_siteadmin())
        {
            $orgname= $DB->get_field('local_costcenter','fullname',array('id'=>$costcenterid));
            $mform->addElement('static','costcentername', get_string('open_costcenterid', 'local_costcenter'), $orgname);
            $mform->addElement('hidden','open_costcenterid', $costcenterid);
        }else
        {
            local_costcenter_get_hierarchy_fields($mform, $this->_ajaxformdata, $this->_customdata,range(1,1),false, 'local_skillrepository', $context, $multiple = false);
        }
        $mform->addElement('text',  'name',  get_string('levelname','local_skillrepository'));
        $mform->addRule('name', get_string('levelnamereq', 'local_skillrepository'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text',  'code',  get_string('levelcode',  'local_skillrepository'));
        $mform->addRule('code', get_string('levelcodereq', 'local_skillrepository'), 'required', null, 'client');
        $mform->setType('code', PARAM_RAW);

        $mform->addElement('hidden',  'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->disable_form_change_checker();
    }
    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $code = $data['code'];
        $name = $data['name'];
        $path = "/". $data['open_costcenterid'];
        if(empty($data['name'])){
            $error['name'] = get_string('nonemptyname', 'local_skillrepository');
        }
        if(empty($data['code'])){
            $error['code'] = get_string('nonemptycode', 'local_skillrepository');
        }
        if ($levelid = $DB->get_field('local_course_levels', 'id', array('code' => $data['code'], 'open_path' => $path))) {
            if (empty($data['id']) || $levelid != $data['id']) {
                $errors['code'] = get_string('codeexists', 'local_skillrepository');
            }
        }
        if(strlen($code) > 150){
            $errors['code'] = get_string('shortnamelengthexceeds', 'local_skillrepository');
        }
        if(strlen($name) > 150){
            $errors['name'] = get_string('namelengthexceeds', 'local_skillrepository');
        }
        if (empty(trim($data['name']))) {
            $errors['name'] = get_string('validlevelname', 'local_skillrepository');
        }
        if (empty(trim($data['code']))) {
            $errors['code'] = get_string('validcode', 'local_skillrepository');
        }
        return $errors;
    }
}
