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
//$DB->set_debug(true);
$sitecontext = context_system::instance();
require_login();
$PAGE->set_url('/local/certification/index.php', array());
$PAGE->set_context($sitecontext);
if (!is_siteadmin() && (!has_capability('local/certification:manage_multiorganizations', context_system::instance())
                && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance()))
	&& !(has_capability('local/certification:managecertification', context_system::instance()))) {
	$PAGE->set_title(get_string('my_certifications', 'local_certification'));
	$PAGE->set_heading(get_string('my_certifications', 'local_certification'));
}else{
	$PAGE->set_title(get_string('browse_certifications', 'local_certification'));
	$PAGE->set_heading(get_string('browse_certifications', 'local_certification'));
}
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->css('/local/certification/css/jquery.dataTables.min.css', true);
$PAGE->requires->js_call_amd('local_certification/ajaxforms', 'load');
$PAGE->requires->js_call_amd('local_certification/certification', 'certificationDatatable', array(array('certificationstatus' => -1)));
$PAGE->requires->js_call_amd('theme_epsilon/quickactions', 'quickactionsCall');
$renderer = $PAGE->get_renderer('local_certification');
$PAGE->navbar->add(get_string("pluginname", 'local_certification'));
echo $OUTPUT->header();
$enabled = check_certificationenrol_pluginstatus($value);
echo $renderer->get_certification_tabs();
echo $OUTPUT->footer();
