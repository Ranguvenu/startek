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
 * @subpackage local_certificates
 */

require_once('../../config.php');

$tid = required_param('tid', PARAM_INT);
$ctid = optional_param('ctid', 0, PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$template = $DB->get_record('local_certificate', array('id' => $tid), '*', MUST_EXIST);
if($ctid==0){
    $ctid =$template->id;
}
// Set the template object.
$template = new \local_certificates\template($template);
$sitecontext = context_system::instance();
require_login();
global $USER;

if(!is_siteadmin()){
    $cert_orgid = $DB->get_field('local_certificate', 'costcenter', array('id'=>$ctid));
    if($cert_orgid !=  $USER->open_costcenterid){
        print_error('Sorry, but you do not currently have permissions to view this page.');
    }
}

if ($action == 'edit') {
    // The id of the element must be supplied if we are currently editing one.
    $id = required_param('id', PARAM_INT);
    $element = $DB->get_record('local_certificate_elements', array('id' => $id), '*', MUST_EXIST);
    $pageurl = new moodle_url('/local/certificates/edit_element.php', array('id' => $id, 'tid' => $tid,'ctid'=>$ctid,'action' => $action));
} else { // Must be adding an element.
    // We need to supply what element we want added to what page.
    $pageid = required_param('pageid', PARAM_INT);
    $element = new stdClass();
    $element->element = required_param('element', PARAM_ALPHA);
    $pageurl = new moodle_url('/local/certificates/edit_element.php', array('tid' => $tid,'ctid'=>$ctid,'element' => $element->element,
        'pageid' => $pageid, 'action' => $action));
}

// Set up the page.
$title = get_string('editelement','local_certificates');
\local_certificates\page_helper::page_setup($pageurl,$sitecontext, $title);

// Additional page setup.
if ($sitecontext->contextlevel == CONTEXT_SYSTEM) {
    $certification = $DB->get_field('local_certificate','name',array('id' => $ctid));
    // We are managing a template - add some navigation.
    $PAGE->navbar->add($certification,
        new moodle_url('/local/certificates/rearrange.php',array('ctid'=>$ctid)));
}
$PAGE->navbar->add(get_string('editcertification','local_certificates'), new moodle_url('/local/certificates/edit.php',
    array('tid' => $tid,'ctid'=>$ctid)));

$PAGE->navbar->add($title);

$mform = new \local_certificates\form\edit_element_form($pageurl, array('element' => $element));

// Check if they cancelled.
if ($mform->is_cancelled()) {
    $url = new moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
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
    if ($e = \local_certificates\element_factory::get_element_instance($data)) {
        $e->save_form_elements($data);
    }
    $url = new moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
    redirect($url);
}

echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('editelement','local_certificates'));
$mform->display();
echo $OUTPUT->footer();
