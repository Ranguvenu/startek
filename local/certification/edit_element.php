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

require_once('../../config.php');

$tid = required_param('tid', PARAM_INT);
$ctid = optional_param('ctid', 0, PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$template = $DB->get_record('local_certification_templts', array('id' => $tid), '*', MUST_EXIST);
if($ctid==0){
    $ctid =$template->certificationid;
}
// Set the template object.
$template = new \local_certification\template($template);
$sitecontext = context_system::instance();
// Perform checks.
// if ($cm = $template->get_cm()) {
//     require_login($cm->course, false, $cm);
// } else {
    require_login();
// }
// Make sure the user has the required capabilities.
// $template->require_manage();

if ($action == 'edit') {
    // The id of the element must be supplied if we are currently editing one.
    $id = required_param('id', PARAM_INT);
    $element = $DB->get_record('local_certification_elements', array('id' => $id), '*', MUST_EXIST);
    $pageurl = new moodle_url('/local/certification/edit_element.php', array('id' => $id, 'tid' => $tid,'ctid'=>$ctid,'action' => $action));
} else { // Must be adding an element.
    // We need to supply what element we want added to what page.
    $pageid = required_param('pageid', PARAM_INT);
    $element = new stdClass();
    $element->element = required_param('element', PARAM_ALPHA);
    $pageurl = new moodle_url('/local/certification/edit_element.php', array('tid' => $tid,'ctid'=>$ctid,'element' => $element->element,
        'pageid' => $pageid, 'action' => $action));
}

// Set up the page.
$title = get_string('editelement','local_certification');
\local_certification\page_helper::page_setup($pageurl,$sitecontext, $title);

// Additional page setup.
if ($sitecontext->contextlevel == CONTEXT_SYSTEM) {
    $certification = $DB->get_field('local_certification','name',array('id' => $ctid));
    // We are managing a template - add some navigation.
    $PAGE->navbar->add($certification,
        new moodle_url('/local/certification/view.php',array('ctid'=>$ctid)));
}
$PAGE->navbar->add(get_string('editcertification','local_certification'), new moodle_url('/local/certification/edit.php',
    array('tid' => $tid,'ctid'=>$ctid)));

$PAGE->navbar->add($title);

$mform = new \local_certification\form\edit_element_form($pageurl, array('element' => $element));

// Check if they cancelled.
if ($mform->is_cancelled()) {
    $url = new moodle_url('/local/certification/edit.php', array('tid' => $tid,'ctid'=>$ctid));
    redirect($url);
}

if ($data = $mform->get_data()) {
    // Set the id, or page id depending on if we are editing an element, or adding a new one.
    if ($action == 'edit') {
        $data->id = $id;
    } else {
        $data->pageid = $pageid;
    }
    // Set the element variable.
    $data->element = $element->element;
    // Get an instance of the element class.
    if ($e = \local_certification\element_factory::get_element_instance($data)) {
        $e->save_form_elements($data);
    }

    $url = new moodle_url('/local/certification/edit.php', array('tid' => $tid,'ctid'=>$ctid));
    redirect($url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editelement','local_certification'));
$mform->display();
echo $OUTPUT->footer();
