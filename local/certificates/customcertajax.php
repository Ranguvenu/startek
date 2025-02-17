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

require_once(__DIR__ . '/../../config.php');

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}

$tid = required_param('tid', PARAM_INT);
$values = required_param('values', PARAM_RAW);
$values = json_decode($values);

// Make sure the template exists.
$template = $DB->get_record('local_certificate', array('id' => $tid), '*', MUST_EXIST);

// Set the template.
$template = new \local_certificates\template($template);
// Perform checks.
// if ($cm = $template->get_cm
//     $courseid = $cm->course;
//     require_login($courseid, false, $cm);
// } else {
    require_login();
// }
// Make sure the user has the required capabilities.
// $template->require_manage();

// Loop through the data.
foreach ($values as $value) {
    $element = new stdClass();
    $element->id = $value->id;
    $element->posx = $value->posx;
    $element->posy = $value->posy;
    $DB->update_record('local_certificate_elements', $element);
}