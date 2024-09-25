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
 * @subpackage local_certificates
 */

defined('MOODLE_INTERNAL') or die;


function local_certificates_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.

    // Make sure the filearea is one of those used by the plugin.
    //if ($filearea !== 'certificationlogo') {
    //    return false;
    //}

    $itemid = array_shift($args);

    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_certificates', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
    send_file($file, $filename, 0, $forcedownload, $options);
}

/*
 *  @method certificates output fragment
 *  @param $args
 */
function local_certificates_output_fragment_certificateform($args) {
	global $CFG, $DB;

	$args = (object) $args;
	$context = $args->context;
	$roomid = $args->roomid;
	$o = '';
	$formdata = [];
	if (!empty($args->jsonformdata)) {
		$serialiseddata = json_decode($args->jsonformdata);
		parse_str($serialiseddata, $formdata);
	}

	if ($args->roomid > 0) {
		$heading = 'Update room';
		$collapse = false;
		$data = $DB->get_record('local_location_room', array('id' => $roomid));
	}
	$editoroptions = [
		'maxfiles' => EDITOR_UNLIMITED_FILES,
		'maxbytes' => $course->maxbytes,
		'trust' => false,
		'context' => $context,
		'noclean' => true,
		'subdirs' => false,
	];
	$group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

	$mform = new local_location\form\roomform(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $formdata);

	$mform->set_data($data);

	if (!empty($formdata)) {
		// If we were passed non-empty form data we want the mform to call validation functions and show errors.
		$mform->is_validated();
	}

	ob_start();
	$mform->display();
	$o .= ob_get_contents();
	ob_end_clean();
	return $o;
}


/**
 * Serve the edit element as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function local_certificates_output_fragment_editelement($args) {
    global $DB;

    // Get the element.
    $element = $DB->get_record('local_certificate_elements', array('id' => $args['elementid']), '*', MUST_EXIST);

    $pageurl = new moodle_url('/local/certificates/rearrange.php', array('ctid' => $element->pageid));
    $form = new \local_certificates\form\edit_element_form($pageurl, array('element' => $element));

    return $form->render();
}

/*
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
// function local_certificates_leftmenunode(){
//     $systemcontext = context_system::instance();
//     $certificatenode = '';
//     if(has_capability('local/certificates:manage', $systemcontext) || is_siteadmin() ) {
//         $certificatenode .= html_writer::start_tag('li', array('class' => 'pull-left user_nav_div browsecertifications'));
//             $certification_url = new moodle_url('/local/certificates/index.php');
//             $certification = html_writer::link($certification_url, '<i class="fa fa-certificate"></i><span class="user_navigation_link_text">'.get_string('manage_certificates','local_certificates').'</span>',array('class'=>'user_navigation_link'));
//             $certificatenode .= $certification;
//         $certificatenode .= html_writer::end_tag('li');
//     }

//     return array('15' => $certificatenode);
// }

