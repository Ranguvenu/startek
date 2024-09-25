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

require_once('../../config.php');

$tid = optional_param('tid', 0, PARAM_INT);
$ctid = optional_param('ctid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
if ($action) {
    $actionid = required_param('aid', PARAM_INT);
}
$confirm = optional_param('confirm', 0, PARAM_INT);

require_login();
global $USER, $DB;

if(!is_siteadmin() && !empty($ctid)){
    $cert_orgid = $DB->get_field('local_certificate', 'costcenter', array('id'=>$ctid));
    if($cert_orgid !=  $USER->open_costcenterid){
        print_error('Sorry, but you do not currently have permissions to view this page.');
    }
}

// Need to supply the contextid.
$contextid = \context_system::instance()->id;

$PAGE->navbar->add(get_string('pluginname','local_certificates'),new moodle_url('/local/certificates/index.php',array()));
    
// Edit an existing template.
if ($tid) {
    // Create the template object.
    $certificate = $DB->get_record('local_certificate', array('id' => $tid), '*', MUST_EXIST);
    if(!is_siteadmin()){
        if($certificate->costcenter != $USER->open_costcenterid){
            $message = get_string('notyourorgcertificate_msg','local_certificates');
            redirect($CFG->wwwroot.'/local/certificates/index.php', $message, null, NOTIFY_ERROR);
        }
    }
    if($ctid == 0){
        $ctid = $certificate->id;
    }

    $template = new \local_certificates\template($certificate);
    // Set the page url.
    $pageurl = new moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
    // We are managing a template - add some navigation.
    $PAGE->navbar->add($certificate->name,new moodle_url('/local/certificates/rearrange.php',array('ctid'=>$ctid)));
    $PAGE->navbar->add(get_string('editcertificate', 'local_certificates'));
    $PAGE->set_title(get_string('editcertificate', 'local_certificates'));
} else { // Adding a new template.
    $PAGE->navbar->add(get_string('create_cert', 'local_certificates'));
    $PAGE->set_title(get_string('create_cert', 'local_certificates'));
    // Set the page url.
    $pageurl = new moodle_url('/local/certificates/edit.php', array('contextid' => $contextid));
}


// Flag to determine if we are deleting anything.
$deleting = false;

if ($tid) {
    if ($action && confirm_sesskey()) {
        switch ($action) {
            case 'pmoveup' :
                $template->move_item('page', $actionid, 'up');
                break;
            case 'pmovedown' :
                $template->move_item('page', $actionid, 'down');
                break;
            case 'emoveup' :
                $template->move_item('element', $actionid, 'up');
                break;
            case 'emovedown' :
                $template->move_item('element', $actionid, 'down');
                break;
            case 'addpage' :
                $template->add_page();
                $url = new \moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
                redirect($url);
                break;
            case 'deletepage' :
                if (!empty($confirm)) { // Check they have confirmed the deletion.
                    $template->delete_page($actionid);
                    $url = new \moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
                    redirect($url);
                } else {
                    // Set deletion flag to true.
                    $deleting = true;
                    // Create the message.
                    $message = get_string('deletepageconfirm', 'local_certificates');
                    // Create the link options.
                    $nourl = new moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
                    $yesurl = new moodle_url('/local/certificates/edit.php',
                        array(
                            'tid' => $tid,
                            'ctid'=>$ctid,
                            'action' => 'deletepage',
                            'aid' => $actionid,
                            'confirm' => 1,
                            'sesskey' => sesskey()
                        )
                    );
                }
                break;
            case 'deleteelement' :
                $elementname = $DB->get_field('local_certificate_elements','name',array('id'=>$actionid));
                if (!empty($confirm)) { // Check they have confirmed the deletion.
                    $template->delete_element($actionid);
                } else {
                    // Set deletion flag to true.
                    $deleting = true;
                    // Create the message.
                    $message = get_string('deleteelementconfirm', 'local_certificates',$elementname);
                    // Create the link options.
                    $nourl = new moodle_url('/local/certificates/edit.php', array('tid' => $tid,'ctid'=>$ctid));
                    $yesurl = new moodle_url('/local/certificates/edit.php',
                        array(
                            'tid' => $tid,
                            'ctid'=>$ctid,
                            'action' => 'deleteelement',
                            'aid' => $actionid,
                            'confirm' => 1,
                            'sesskey' => sesskey()
                        )
                    );
                }
                break;
        }
    }
}

// Check if we are deleting either a page or an element.
if ($deleting) {
    // Show a confirmation page.
    $strheading = get_string('deleteconfirm', 'local_certificates');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    echo $OUTPUT->confirm($message, $yesurl, $nourl);
    echo $OUTPUT->footer();
    exit();
}

if ($tid) {
   
    $mform = new \local_certificates\form\edit_form($pageurl, array('tid' => $tid,'ctid'=>$ctid));
    // Set the name for the form.
    $mform->set_data($certificate);
} else {
    $mform = new \local_certificates\form\edit_form($pageurl);
}

if ($data = $mform->get_data()) {
    // If there is no id, then we are creating a template.
    if (!$tid) {

        $template = \local_certificates\template::create($data, $contextid);
        // Create a page for this template.
        $pageid = $template->add_page();

        // Associate all the data from the form to the newly created page.
        // $width = 'pagewidth_' . $pageid;
        // $height = 'pageheight_' . $pageid;
        $leftmargin = 'pageleftmargin_' . $pageid;
        $rightmargin = 'pagerightmargin_' . $pageid;

        // $data->$width = $data->pagewidth_0;
        // $data->$height = $data->pageheight_0;
        $data->$leftmargin = $data->pageleftmargin_0;
        $data->$rightmargin = $data->pagerightmargin_0;

        // We may also have clicked to add an element, so these need changing as well.
        if (isset($data->element_0) && isset($data->addelement_0)) {
            $element = 'element_' . $pageid;
            $addelement = 'addelement_' . $pageid;
            $data->$element = $data->element_0;
            $data->$addelement = $data->addelement_0;

            // Need to remove the temporary element and add element placeholders so we
            // don't try add an element to the wrong page.
            unset($data->element_0);
            unset($data->addelement_0);
        }
    }

    // Save any data for the template.
    $template->save($data);

    // Save any page data.
    $template->save_page($data);

    // Loop through the data.
    foreach ($data as $key => $value) {
        // Check if they chose to add an element to a page.
        if (strpos($key, 'addelement_') !== false) {
            // Get the page id.
            $pageid = str_replace('addelement_', '', $key);
            // Get the element.
            $element = "element_" . $pageid;
            $element = $data->$element;
            // Create the URL to redirect to to add this element.
            $params = array();
            $params['tid'] = $template->get_id();
            $params['ctid'] = $ctid;
            $params['action'] = 'add';
            $params['element'] = $element;
            $params['pageid'] = $pageid;
            $url = new moodle_url('/local/certificates/edit_element.php', $params);
            redirect($url);
        }
    }

    // Check if we want to preview this custom certificates.
    if (!empty($data->previewbtn)) {
        $template->generate_pdf(true);
        exit();
    }
    
    $url = new moodle_url('/local/certificates/index.php', array());
    redirect($url);
}

echo $OUTPUT->header();
if($tid){
    echo $OUTPUT->heading(get_string('editcertificate', 'local_certificates'));
}else{
    echo $OUTPUT->heading(get_string('create_cert', 'local_certificates'));
}

$mform->display();
//if ($tid && $context->contextlevel == CONTEXT_MODULE) {
//    $loadtemplateurl = new moodle_url('/local/certification/load_template.php', array('tid' => $tid));
//    $loadtemplateform = new \local_certification\load_template_form($loadtemplateurl, array('context' => $context), 'post',
//        '', array('id' => 'loadtemplateform'));
//    $loadtemplateform->display();
//}
echo $OUTPUT->footer();
