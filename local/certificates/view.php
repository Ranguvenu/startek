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
require_once(dirname(__FILE__) . '/../../config.php');
use local_certificates\certificates;
global $CFG;
$cert_id = required_param('ctid', PARAM_INT);
$moduletype = optional_param('mtype', null, PARAM_TEXT);
$moduleid = optional_param('mid', null, PARAM_INT);
$userid = optional_param('uid', null, PARAM_INT);
$savepdf = optional_param('savepdf', false, PARAM_BOOL);

$sitecontext = context_system::instance();
require_login();
$PAGE->set_url('/local/certificates/view.php', array('ctid' => $cert_id));
$PAGE->set_context($sitecontext);
$PAGE->set_title(get_string('download_certificate', 'local_certificates'));

// $certcost = $cert->costcenter;
// if(!is_siteadmin() && $certcost != $USER->open_costcenterid) {
//     redirect($CFG->wwwroot . '/my/index.php');
// }

$template = $DB->get_record('local_certificate', array('id' => $cert_id), '*', MUST_EXIST);
$PAGE->navbar->add($template->name);
$PAGE->navbar->add(get_string("download_certificate", 'local_certificates'));
$PAGE->set_heading($template->name);

// Now we want to generate the PDF.
if($cert_id){
    // Create new customcert issue record if one does not already exist.
    // if (!$DB->record_exists('local_certificate_issues', array('userid' => $USER->id, 'certificateid' => $cert_id))) {
    //     $customcertissue = new stdClass();
    //     $customcertissue->certificateid = $cert_id;
    //     $customcertissue->userid = $USER->id;
    //     $customcertissue->code = \local_certificates\certificates::generate_code();
    //     $customcertissue->timecreated = time();
    //     // Insert the record into the database.
    //     $DB->insert_record('local_certificate_issues', $customcertissue);
    // }
    $moduleinfo = new \stdClass();
    $moduleinfo->moduletype = $moduletype;
    $moduleinfo->moduleid = $moduleid;

    $template = new \local_certificates\template($template);
    $template->generate_pdf(false, $userid, false, $moduleinfo, $savepdf);
    exit();
}
//$PAGE->set_cacheable(true);
echo $OUTPUT->header();
echo $OUTPUT->footer();