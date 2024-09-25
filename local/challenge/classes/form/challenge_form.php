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
 * @package   local
 * @subpackage  challenge
 * @author eabyas  <info@eabyas.in>
**/
namespace local_challenge\form;
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
class challenge_form extends \moodleform{
	public function definition(){
		global $DB, $USER, $PAGE;

		$systemcontext = \context_system::instance();

		$mform = $this->_form;
		$module_type = $this->_customdata['module_type'];
		$module_id = $this->_customdata['module_id'];
        $type = $this->_customdata['type'];
        // $module_name = $this->_customdata['module_name'];
        // $module_startdate = $this->_customdata['module_startdate'];
        // $module_startdate = $this->_customdata['module_startdate'];
		
		$mform->addElement('hidden', 'module_type', $module_type);
        $mform->setType('module_type', PARAM_TEXT);

		$mform->addElement('hidden', 'module_id', $module_id);
		$mform->setType('module_id', PARAM_INT);

        $mform->addElement('hidden', 'type', $type);
        $mform->setType('module_type', PARAM_TEXT);


        // $mform->addElement('hidden', 'module_name', $module_name);
        // $mform->setType('module_name', PARAM_TEXT);

        // $mform->addElement('hidden', 'module_startdate', $module_startdate);
        // $mform->setType('module_startdate', PARAM_INT);
        
        // $mform->addElement('hidden', 'module_enddate', $module_enddate);
        // $mform->setType('module_enddate', PARAM_INT);

		$challengee_options = array(
            'ajax' => 'local_challenge/form-options-selector',
            'data-contextid' => $systemcontext->id,
            'data-action' => 'challenge_user_selector',
            'data-options' => json_encode(array('module_id' => $module_id, 'module_type' => $module_type)),
            'class' => 'challenge_user_selector',
            'data-class' => 'challenge_user_selector',
            'multiple' => True,
        );
        if(isset($this->_ajaxformdata['userid_to'])){
            $challengeeids = ','.implode(',', $this->_ajaxformdata['userid_to']).',';
            $challengees = $DB->get_records_sql_menu("SELECT id, concat(firstname,' ',lastname) FROM {user} WHERE (:challengeeids) LIKE concat('%,',id,',%') ", array('challengeeids' => $challengeeids));
        }else{
            $challengees = array();
        }
        $userLabel = ($type == 'challenge') ? get_string('challenge_to','local_challenge') : get_string('refer_to','local_challenge') ;
        $userhelp = ($type == 'challenge') ? 'challenge_to_user': 'refer_to_user';

        $mform->addElement('autocomplete', 'userid_to', $userLabel, $challengees, $challengee_options);
        $mform->addHelpButton('userid_to', $userhelp, 'local_challenge');
        $mform->setType('userid_to', PARAM_RAW);
        $mform->addRule('userid_to', get_string('pleaseselecttouser','local_challenge'), 'required', null, 'client');

        if($type == 'challenge'){
            $attributes = array('optional' => True, 'startyear' => \local_costcenter\lib::get_userdate('Y', time()));
            $mform->addElement('date_selector', 'complete_by', get_string('challenge_complete_by', 'local_challenge'), $attributes);
            $mform->setType('date_selector', PARAM_INT);
            $label_str = get_string('my_challengees', 'local_challenge');
            $label_class = 'my_challengees';
        }else{
            $label_str = get_string('myreferals', 'local_challenge');
            $label_class = 'my_referrals';
        }
            // $attributes = array();
            // $mform->addElement('textarea', 'message_body', get_string("introtext", "survey"), 'wrap="virtual" rows="5" cols="40"');
            // $mform->setType('message_body', PARAM_RAW);
        $renderer = $PAGE->get_renderer('local_challenge');
        $args = new \stdClass();
        $args->userid = $USER->id;
        $args->module_id = $module_id;
        $args->module_type = $module_type;
        $args->limit_from = 0;
        $args->limit_to = 10;
        $args->type = $type;
        $element = $renderer->display_my_challengees($args);
        $html = "<div class='{$label_class}'><label>{$label_str}</label>{$element}</div>";
        $mform->addElement('html', $html);
	}
    public function validation($data, $files){
        $errors = array();
        $errors = parent::validation($data, $files);
        if($data['complete_by'] > 0 && $data['complete_by'] < time()){
            $errors['complete_by'] = get_string('cannot_challengeto_previousdate', 'local_challenge');
        }
        return $errors;
    }
}