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
$ltid = required_param('ltid', PARAM_INT); // The template to load.
$confirm = optional_param('confirm', 0, PARAM_INT);

$template = $DB->get_record('local_certification_templts', array('id' => $tid), '*', MUST_EXIST);
$template = new \local_certification\template($template);

$loadtemplate = $DB->get_record('local_certification_templts', array('id' => $ltid), '*', MUST_EXIST);
$loadtemplate = new \local_certification\template($loadtemplate);

if ($cm = $template->get_cm()) {
    require_login($cm->course, false, $cm);
} else {
    require_login();
}
$template->require_manage();

// Check that they have confirmed they wish to load the template.
if ($confirm && confirm_sesskey()) {
    // First, remove all the existing elements and pages.
    $sql = "SELECT e.*
              FROM {local_certification_elements} e
        INNER JOIN {local_certification_pages} p
                ON e.pageid = p.id
             WHERE p.templateid = :templateid";
    if ($elements = $DB->get_records_sql($sql, array('templateid' => $template->get_id()))) {
        foreach ($elements as $element) {
            // Get an instance of the element class.
            if ($e = \local_certification\element_factory::get_element_instance($element)) {
                $e->delete();
            }
        }
    }

    // Delete the pages.
    $DB->delete_records('local_certification_pages', array('templateid' => $template->get_id()));

    // Copy the items across.
    $loadtemplate->copy_to_template($template->get_id());

    // Redirect.
    $url = new moodle_url('/local/certification/edit.php', array('tid' => $tid));
    redirect($url);
}

// Create the link options.
$nourl = new moodle_url('/local/certification/edit.php', array('tid' => $tid));
$yesurl = new moodle_url('/local/certification/load_template.php', array('tid' => $tid,
                                                                    'ltid' => $ltid,
                                                                    'confirm' => 1,
                                                                    'sesskey' => sesskey()));

$pageurl = new moodle_url('/local/certification/load_template.php', array('tid' => $tid, 'ltid' => $ltid));
\local_certification\page_helper::page_setup($pageurl, $template->get_context(), get_string('loadtemplate','local_certification'));

// Show a confirmation page.
echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('loadtemplatemsg','local_certification'), $yesurl, $nourl);
echo $OUTPUT->footer();