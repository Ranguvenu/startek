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

// The page of the certification we are editing.
$ctid = required_param('ctid', PARAM_INT);
// $ctid = optional_param('ctid',0,PARAM_INT);
$page = $DB->get_record('local_certification_pages', array('id' => $ctid), '*', MUST_EXIST);
$template = $DB->get_record('local_certification_templts', array('id' => $page->templateid), '*', MUST_EXIST);
$elements = $DB->get_records('local_certification_elements', array('pageid' => $ctid), 'sequence');

if($ctid==0){
    $ctid =$template->certificationid;
}
// Set the template.
$template = new \local_certification\template($template);

// Perform checks.
// if ($cm = $template->get_cm()) {
//     require_login($cm->course, false, $cm);
// } else {
    require_login();
// }
// Make sure the user has the required capabilities.
// $template->require_manage();

// Set the $PAGE settings.
$pageurl = new moodle_url('/local/certification/rearrange.php', array('ctid' => $ctid,'ctid'=>$ctid));
\local_certification\page_helper::page_setup($pageurl, $template->get_context(), get_string('rearrangeelements','local_certification'));

// Add more links to the navigation.
// if (!$cm = $template->get_cm()) {
//     $str = get_string('managetemplates','local_certification');
//     $link = new moodle_url('/local/certification/manage_templates.php');
//     $PAGE->navbar->add($str, new \action_link($link, $str));
// }

$str = get_string('editcertification','local_certification');
$link = new moodle_url('/local/certification/edit.php', array('tid' => $template->get_id()));
$PAGE->navbar->add($str, new \action_link($link, $str));

$PAGE->navbar->add(get_string('rearrangeelements','local_certification'));

// Include the JS we need.
$PAGE->requires->yui_module('moodle-local_certification-rearrange', 'Y.M.local_certification.rearrange.init',
    array($template->get_id(),
          $page,
          $elements));

// Create the buttons to save the position of the elements.
$html = html_writer::start_tag('div', array('class' => 'buttons'));
$html .= $OUTPUT->single_button(new moodle_url('/local/certification/edit.php', array('tid' => $template->get_id())),
        get_string('saveandclose','local_certification'), 'get', array('class' => 'savepositionsbtn'));
$html .= $OUTPUT->single_button(new moodle_url('/local/certification/rearrange.php', array('ctid'=>$ctid,'ctid' => $ctid)),
        get_string('saveandcontinue','local_certification'), 'get', array('class' => 'applypositionsbtn'));
$html .= $OUTPUT->single_button(new moodle_url('/local/certification/edit.php', array('ctid'=>$ctid,'tid' => $template->get_id())),
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
// print_object($elements);
if ($elements) {
    foreach ($elements as $element) {
        // Get an instance of the element class.
        if ($e = \local_certification\element_factory::get_element_instance($element)) {
            switch ($element->refpoint) {
                case \local_certification\element_helper::CERTIFICATION_REF_POINT_TOPRIGHT:
                    $class = 'element refpoint-right';
                    break;
                case \local_certification\element_helper::CERTIFICATION_REF_POINT_TOPCENTER:
                    $class = 'element refpoint-center';
                    break;
                case \local_certification\element_helper::CERTIFICATION_REF_POINT_TOPLEFT:
                default:
                    $class = 'element refpoint-left';
            }
            $html .= html_writer::tag('div', $e->render_html(), array('class' => $class,
                'data-refpoint' => $element->refpoint, 'id' => 'element-' . $element->id));
        }
    }
}
if ($page->rightmargin) {
    $position = 'left:' . ($page->width - $page->rightmargin) . 'mm;';
    $html .= "<div id='rightmargin' style='$position $marginstyle'></div>";
}
$html .= html_writer::end_tag('div');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('rearrangeelementsheading','local_certification'), 4);
echo $html;
$PAGE->requires->js_call_amd('local_certification/rearrange-area', 'init', array('#pdf'));
echo $OUTPUT->footer();
