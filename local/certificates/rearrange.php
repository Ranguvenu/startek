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

// The page of the certificate we are editing.
$ctid = required_param('ctid', PARAM_INT);

$systemcontext = context_system::instance();

require_login();

require_capability('local/certificates:view',$systemcontext);
global $USER, $DB;
if(!is_siteadmin()){
    $cert_orgid = $DB->get_field('local_certificate', 'costcenter', array('id'=>$ctid));
    if($cert_orgid !=  $USER->open_costcenterid){
        print_error('Sorry, but you do not currently have permissions to view this page.');
    }
}

// $ctid = optional_param('ctid',0,PARAM_INT);
$page = $DB->get_record('local_certificate_pages', array('id' => $ctid), '*', MUST_EXIST);
$certificate = $DB->get_record('local_certificate', array('id' => $page->certificateid), '*', MUST_EXIST);
$elements = $DB->get_records('local_certificate_elements', array('pageid' => $ctid), 'sequence');
// Set the template.
$template = new \local_certificates\template($certificate);

// Perform checks.
// if ($cm = $template->get_cm()) {
//     require_login($cm->course, false, $cm);
// } else {
// }
// Make sure the user has the required capabilities.
// $template->require_manage();

// Set the $PAGE settings.
$pageurl = new moodle_url('/local/certificates/rearrange.php', array('ctid' => $ctid));
\local_certificates\page_helper::page_setup($pageurl, $template->get_context(), get_string('cert_preview','local_certificates'));

// Add more links to the navigation.
// if (!$cm = $template->get_cm()) {
//     $str = get_string('managetemplates','local_certificates');
//     $link = new moodle_url('/local/certificates/manage_templates.php');
//     $PAGE->navbar->add($str, new \action_link($link, $str));
// }
$PAGE->navbar->add(get_string('pluginname','local_certificates'),new moodle_url('/local/certificates/index.php',array()));
$str = get_string('editcertificate','local_certificates');
$link = new moodle_url('/local/certificates/edit.php', array('tid' => $template->get_id()));
$PAGE->navbar->add($str, new \action_link($link, $str));

$PAGE->navbar->add(get_string('cert_preview','local_certificates'));

$PAGE->set_heading(get_string('cert_preview', 'local_certificates'));

// Include the JS we need.
$PAGE->requires->yui_module('moodle-local_certificates-rearrange', 'Y.M.local_certificates.rearrange.init',
    array($template->get_id(),
          $page,
          $elements));

// Create the buttons to save the position of the elements.
$html = html_writer::start_tag('div', array('class' => 'buttons'));
$html .= $OUTPUT->single_button(new moodle_url('/local/certificates/edit.php', array('tid' => $template->get_id())),
        get_string('saveandclose','local_certificates'), 'get', array('class' => 'savepositionsbtn'));
$html .= $OUTPUT->single_button(new moodle_url('/local/certificates/rearrange.php', array('ctid'=>$ctid,'ctid' => $ctid)),
        get_string('saveandcontinue','local_certificates'), 'get', array('class' => 'applypositionsbtn'));
$html .= $OUTPUT->single_button(new moodle_url('/local/certificates/edit.php', array('ctid'=>$ctid,'tid' => $template->get_id())),
        get_string('cancel'), 'get', array('class' => 'cancelbtn'));
$html .= html_writer::end_tag('div');

// Create the div that represents the PDF.
$style = 'height: ' . $page->height . 'mm; line-height: normal; width: ' . $page->width . 'mm;';
$marginstyle = 'height: ' . $page->height . 'mm; width:1px; float:left; position:relative;';
$html .= html_writer::start_tag('div', array(
    'data-templateid' => $template->get_id(),
    'data-contextid' => $template->get_contextid(),
    'id' => 'pdf',
    'style' => $style)
);
if ($page->leftmargin) {
    $position = 'left:' . $page->leftmargin . 'mm;';
    $html .= "<div id='leftmargin' style='$position $marginstyle'></div>";
}
if ($elements) {
    foreach ($elements as $element) {
        // $element->moduletype = '{{moduletype}}';
        // Get an instance of the element class.
        if ($e = \local_certificates\element_factory::get_element_instance($element)) {
            switch ($element->refpoint) {
                case \local_certificates\element_helper::CERTIFICATION_REF_POINT_TOPRIGHT:
                    $class = 'element refpoint-right';
                    break;
                case \local_certificates\element_helper::CERTIFICATION_REF_POINT_TOPCENTER:
                    $class = 'element refpoint-center';
                    break;
                case \local_certificates\element_helper::CERTIFICATION_REF_POINT_TOPLEFT:
                default:
                    $class = 'element refpoint-left';
            }
            if($element->element == 'modulename'){
                $html .= html_writer::tag('div', $e->render_html(), array('class' => $class,
                'data-refpoint' => $element->refpoint, 'id' => 'element-' . $element->id));
            }else{
                $html .= html_writer::tag('div', $e->render_html(), array('class' => $class,
                'data-refpoint' => $element->refpoint, 'id' => 'element-' . $element->id));
            }
        }
    }
}
if ($page->rightmargin) {
    $position = 'left:' . ($page->width - $page->rightmargin) . 'mm;';
    $html .= "<div id='rightmargin' style='$position $marginstyle'></div>";
}
$html .= html_writer::end_tag('div');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('rearrangeelementsheading','local_certificates'), 4);
echo $html;
$PAGE->requires->js_call_amd('local_certificates/rearrange-area', 'init', array('#pdf'));
echo $OUTPUT->footer();
