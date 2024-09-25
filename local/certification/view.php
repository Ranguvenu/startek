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
require_once(dirname(__FILE__) . '/../../config.php');
use local_certification\certification;
global $CFG;
$certificationid = required_param('ctid', PARAM_INT);
$tid = optional_param('tid',0,PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$status = optional_param('status', 0, PARAM_INT);

$sitecontext = context_system::instance();
require_login();
$PAGE->set_url('/local/certification/view.php', array('ctid' => $certificationid));
$PAGE->set_context($sitecontext);
$PAGE->set_title(get_string('certifications', 'local_certification'));
// $PAGE->set_heading(get_string('certifications', 'local_certification'));

$certification = $DB->get_record('local_certification', array('id' => $certificationid));
if (empty($certification)) {
    redirect("$CFG->wwwroot/local/certification/index.php");
}

$certcost = $certification->costcenter;
$enrolled = $DB->record_exists('local_certification_users',  array('userid' => $USER->id, 'certificationid' => $certificationid));
if(!is_siteadmin() && $certcost != $USER->open_costcenterid && !$enrolled) {
    redirect($CFG->wwwroot . '/local/certification/index.php');
}

$PAGE->navbar->add(get_string("pluginname", 'local_certification'), new moodle_url('index.php'));
$PAGE->navbar->add($certification->name);
$PAGE->set_heading($certification->name);

$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/local/certification/css/jquery.dataTables.min.css', true);
$PAGE->requires->js_call_amd('local_certification/ajaxforms', 'load');
$PAGE->requires->js_call_amd('local_certification/certification', 'CoursesDatatable');
//$PAGE->requires->js_call_amd('local_evaluation/newevaluation', 'init',
//    array('[data-action=createevaluationmodal]', $sitecontext->id, -1,
//        $certificationid, 'certification'));
$PAGE->requires->js_call_amd('local_evaluation/newevaluation','load');

$renderer = $PAGE->get_renderer('local_certification');
if ($action === 'certificationstatus') {
    $return = (new certification)->certification_status_action($certificationid, $status);
    if ($return) {
        redirect($PAGE->url);
    }
}elseif ($action === 'download') {
    // Now we want to generate the PDF.
    if($tid){
           // Create new customcert issue record if one does not already exist.
        if (!$DB->record_exists('local_certification_issues', array('userid' => $USER->id, 'certificationid' => $certificationid))) {
            $customcertissue = new stdClass();
            $customcertissue->certificationid = $certificationid;
            $customcertissue->userid = $USER->id;
            $customcertissue->code = \local_certification\certification::generate_code();
            $customcertissue->timecreated = time();
            // Insert the record into the database.
            $DB->insert_record('local_certification_issues', $customcertissue);
        }
        $template = $DB->get_record('local_certification_templts', array('id' => $tid), '*', MUST_EXIST);
        $template = new \local_certification\template($template);
        $template->generate_pdf();
        exit();
    }
}
elseif ($action === 'selfenrol') {

    $return = (new certification)->certification_self_enrolment($certificationid,$USER->id);
    if ($return) {
        redirect($PAGE->url);
    }
}
//$PAGE->set_cacheable(true);
echo $OUTPUT->header();
 //if (is_siteadmin() || has_capability('local/costcenter:manage', $sitecontext)) {
 //    echo $renderer->certification_actionstatus_markup($certification);
 //}
echo $renderer->get_content_viewcertification($certificationid);
echo $OUTPUT->footer();