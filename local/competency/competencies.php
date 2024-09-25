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
 * @subpackage local_competency
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id = required_param('competencyframeworkid', PARAM_INT);
$pagecontextid = required_param('pagecontextid', PARAM_INT);  // Reference to the context we came from.
$search = optional_param('search', '', PARAM_RAW);

require_login();
\core_competency\api::require_enabled();

$pagecontext = context::instance_by_id($pagecontextid);
$framework = \core_competency\api::read_framework($id);
$context = $framework->get_context();

if (!\core_competency\competency_framework::can_read_context($context)) {
    throw new required_capability_exception($context, 'moodle/competency:competencyview', 'nopermissions', '');
}

$title = get_string('competencies', 'core_competency');
$pagetitle = get_string('competenciesforframework', 'local_competency', $framework->get('shortname'));

// Set up the page.
$url = new moodle_url("/admin/local/competency/competencies.php", array('competencyframeworkid' => $framework->get('id'),
    'pagecontextid' => $pagecontextid));
$frameworksurl = new moodle_url('/admin/local/competency/competencyframeworks.php', array('pagecontextid' => $pagecontextid));

$PAGE->set_context($pagecontext);
$PAGE->navigation->override_active_url($frameworksurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->navbar->add($framework->get('shortname'), $url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$output = $PAGE->get_renderer('local_competency');
echo $output->header();

$page = new \local_competency\output\manage_competencies_page($framework, $search, $pagecontext);
echo $output->render($page);

// Log the framework viewed event after rendering the page.
//\core_competency\api::competency_framework_viewed($framework);

echo $output->footer();
