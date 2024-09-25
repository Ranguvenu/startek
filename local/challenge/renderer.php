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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/challenge/lib.php');
class local_challenge_renderer extends \plugin_renderer_base{
	public function challenge_tabs_content(){

		$options = array('targetID' => 'challenger_tab_content','perPage' => 6, 'cardClass' => 'pull-left col-md-3 clearfix', 'viewType' => 'card');
		$options['methodName']='local_challenge_challenges_view';
        $options['templateName']='local_challenge/challenge_view';

		$cardparams = array(
            'targetID' => 'challenger_tab_content',
            'options' => json_encode($options),
            'dataoptions' => json_encode(array()),
            'filterdata' => json_encode(array('data_type' => 'challenger_tab_content', 'type' => 'challenge')),
        );
		return $this->render_from_template('local_challenge/challenge_tabs', $cardparams);
	}
	public function render_challenge_object($module_type, $module_id){
		if($module_type == 'local_course'){
			$context = context_course::instance($module_id);
		}else{
			$context = context_system::instance();
		}
		$object = html_writer::link('javascript:void(0)', '<i class="icon fa fa-hand-o-right" aria-hidden="true" aria-label="" title ="'.get_string('pluginname','local_challenge').'"></i>', array('class' => 'course_extended_menu_itemlink challenge_trigger_element', 'data-module_id' => $module_id, 'data-moduleid' => $module_id, 'data-module_type' => $module_type, 'data-jsonformdata' => '{}', 'data-type' => 'challenge', 'data-contextid' => $context->id));
		$shareobject = html_writer::link('javascript:void(0)', '<i class="icon fa fa-share-alt" aria-hidden="true" aria-label="" title ="'.get_string('share','local_challenge').'"></i>', array('class' => 'course_extended_menu_itemlink challenge_trigger_element', 'data-module_id' => $module_id, 'data-moduleid' => $module_id, 'data-module_type' => $module_type, 'data-type' => 'share', 'data-jsonformdata' => '{}', 'data-contextid' => $context->id));
		// $challengecontainer = html_writer::div($object, '', array('class' => 'course_extended_menu_itemcontainer text-xs-center'));
		// $sharecontainer = html_writer::div($shareobject, '', array('class' => 'course_extended_menu_itemcontainer text-xs-center'));
		return html_writer::tag('li', $challengecontainer).html_writer::tag('li', $sharecontainer);
	}
	public function display_my_challengees($args){
		global $PAGE;
		$lib = new \local_challenge\local\lib();
		$mychallengees = $lib->get_my_challenged_users($args->userid, $args->module_id, $args->module_type, $args->type, $args->limit_from, $args->limit_to);
		$params = array();
		foreach($mychallengees AS $user){
			$userdata = array();
			$userdata['userimage'] = (new \user_picture($user, array('link'=>false)))->get_url($PAGE)->out();
			$userdata['username'] = fullname($user);
			$params[] = $userdata;
		}
		return $this->render_from_template('local_challenge/challengeusersinfo', array('users' =>$params));
	}
	public function get_challenge_actions($challengeid, $actions){
		$challengelib = new \local_challenge\local\lib();
		$userfields = \user_picture::fields('u');
		$challenge_sql = "SELECT lc.id as challengeid, lc.status, lc.module_type, lc.module_id, {$userfields} 
			FROM {local_challenge} AS lc 
			JOIN {user} AS u ON u.id = lc.userid_from 
			WHERE lc.id = :challengeid ";
		$challenge_data = $challengelib->db->get_record_sql($challenge_sql, array('challengeid' => $challengeid));
		$classname = "\\$challenge_data->module_type\\local\\general_lib";
		if(class_exists($classname)){
			$class = new $classname();
			if(method_exists($class, 'get_custom_data')){
				$field = $challenge_data->module_type == 'local_courses' ? 'fullname AS modulename' : 'name AS modulename';
				$params = array('id' => $challenge_data->module_id);
				$moduledata = $class->get_custom_data($field, $params);
			}
		}
		$modulename = get_string('module_'.$challenge_data->module_type, 'local_challenge');
		$action_icons = '';
		switch($challenge_data->status){
			case CHALLENGE_NEW:
				if($actions){
					$accept_params = array('class' => 'challenge_status_trigger status_approve_link req_status_link', 'data-challengeid' => $challengeid, 'data-newstatus' => CHALLENGE_ACTIVE, 'data-action' => 'challenge', 'data-challenger_name' => fullname($challenge_data), 'data-module_type' => $modulename, 'data-module_name' => $moduledata->modulename);
					$decline_params = array('class' => 'challenge_status_trigger status_reject_link req_status_link', 'data-challengeid' => $challengeid, 'data-newstatus' => CHALLENGE_DECLINED, 'data-action' => 'decline', 'data-challenger_name' => fullname($challenge_data), 'data-module_type' => $modulename, 'data-module_name' => $moduledata->modulename);
					$accepticon = get_string('approve','local_challenge');
					$rejecticon = get_string('reject','local_challenge');
					$actionapprove = html_writer::link('javascript:void(0)', $accepticon, $accept_params);
					$actiondeny = html_writer::link('javascript:void(0)', $rejecticon, $decline_params);
					$action_icons .= html_writer::tag('span', $actionapprove, array('class' => 'text-center req_status'));
					$action_icons .= html_writer::tag('span', $actiondeny, array('class' => 'text-center req_status')); 
				}else{
					
				}
			break;
			case CHALLENGE_ACTIVE:
				$action_icons .= html_writer::tag('i', '', array('class' => 'icon fa fa-check-circle-o text-info'));
			break;
			case CHALLENGE_DECLINED:
				$action_icons .= html_writer::tag('i', '', array('class' => 'icon fa fa-times-circle-o text-danger'));
			break;
			case CHALLENGE_COMPLETED:
				$action_icons .= html_writer::tag('i', '', array('class' => 'icon fa fa-check-circle-o text-success'));
			break;
			case CHALLENGE_INCOMPLETE:
				$action_icons .= html_writer::tag('i', '', array('class' => 'icon fa fa-check-circle-o text-danger'));
			break;
			case CHALLENGE_EXPIRED:
				$action_icons .= html_writer::tag('i', '', array('class' => 'icon fa fa-check-times-o text-danger'));
			break;
		}
		return html_writer::tag('div', $action_icons, array('class' => 'mt-4 challenge_actions d-inline-block w-full text-center mb-2'));
	}
}
