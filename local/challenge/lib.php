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
 * @package BizLMS
 * @subpackage local_challenge
 */
define('CHALLENGE_NEW', 1);
define('CHALLENGE_ACTIVE', 2);
define('CHALLENGE_DECLINED', 3);
define('CHALLENGE_COMPLETED', 4);
define('CHALLENGE_INCOMPLETE', 5);
define('CHALLENGE_EXPIRED', 6);
function local_challenge_render_navbar_output() {
	$enabled =  (int)get_config('', 'local_challenge_enable_challenge');
	if(!$enabled){
		return;
	}
	global $PAGE;
	$PAGE->requires->js_call_amd('local_challenge/challenge', 'init', array());
}
function local_challenge_output_fragment_challenge_module($args){
	// print_object($args);
	$args = (object)$args;
	$o = '';
	$formdata = [];
    $validate = False;
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
        $validate = True;
    }
  //   switch($args->module_type){
  //   	case 'local_courses':
  //   		$modulesql = "SELECT id, fullname AS name, startdate, enddate FROM {course} WHERE id = :id";
		// break;
		// case 'local_learningplan':
		// case 'local_program':
		// 	$modulesql = "SELECT id, name, startdate, enddate FROM {{$args->module_type}} WHERE id = :id ";
		// break;
  //   }
	// $moduleinfo = $DB->get_record_sql($modulesql, array('id' => $args->module_id));
	$mform = new local_challenge\form\challenge_form(null, array('module_type' => $args->module_type, 'module_id' => $args->module_id ,'type' => $args->type/*, 'moduleinfo' => $moduleinfo*/), 'post', '', null, true, $formdata);
	if($validate){
		$mform->is_validated();
	}
	ob_start();
	$mform->display();
	$o .= ob_get_contents();
    ob_end_clean();
    return $o;
}
function local_challenge_get_challenge_status($status){
	switch($status){
		case CHALLENGE_NEW:
			$return = get_string('newchallenge', 'local_challenge');
		break;
		case CHALLENGE_ACTIVE:
			$return = get_string('activechallenge', 'local_challenge');
		break;
		case CHALLENGE_DECLINED:
			$return = get_string('declinedchallenge', 'local_challenge');
		break;
		case CHALLENGE_COMPLETED:
			$return = get_string('completedchallenge', 'local_challenge');
		break;
		case CHALLENGE_INCOMPLETE:
			$return = get_string('incompletechallenge', 'local_challenge');
		break;
		case CHALLENGE_EXPIRED:
			$return = get_string('expiredchallenge', 'local_challenge');
		break;
	}
	return $return;
}
// function local_challenge_leftmenunode(){
// 	$enabled =  (int)get_config('', 'local_challenge_enable_challenge');
// 	if(!$enabled){
// 		return;
// 	}
//     global $USER, $DB;

//     $systemcontext = context_system::instance();
//     $challengenode = '';
//     if(has_capability('local/challenge:challenge_user', $systemcontext)){
//     	$challengenode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_challenge', 'class'=>'pull-left user_nav_div users'));
//             $challenge_url = new moodle_url('/local/challenge/index.php');
//             $challenge = html_writer::link($challenge_url, '<i class="fa fa-thumbs-o-up" aria-hidden="true"></i><span class="user_navigation_link_text">'.get_string('pluginname','local_challenge').'</span>',array('class'=>'user_navigation_link'));
//             $challengenode .= $challenge;
//         $challengenode .= html_writer::end_tag('li');
//     }
//     return array('20' => $challengenode);
// }
function local_challenge_get_challenge($challengeid){
	global $DB;
	return $DB->get_record('local_challenge', array('id' => $challengeid));
}
