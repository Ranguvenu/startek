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
 * @subpackage local_onlinetests
 */
require_once('../../config.php');
require_login();
global $DB, $PAGE, $CFG, $USER, $OUTPUT;
$tab = required_param('tab', PARAM_TEXT);
$categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();
$pageurl = new moodle_url('/local/onlinetests/userdashboard.php',array('tab' => $tab));
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_context($categorycontext);
$heading = get_string($tab.'_onlinetests', 'local_onlinetests');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->navbar->add($heading);
$PAGE->requires->js_call_amd('block_userdashboard/userdashboard', 'makeActive',array('identifier' => 'evaluation_'.$tab));
$PAGE->requires->js_call_amd('block_userdashboard/userdashboard', 'load',array('tab' => $tab));
$renderer = $PAGE->get_renderer('local_onlinetests');
$filterparams = $renderer->get_userdashboard_onlinetests($tab, true);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_certification/userdashboard_inner_tab', array());
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
$content = $renderer->get_userdashboard_onlinetests($tab);
echo html_writer::div($content, 'userdashboard_content_detailed', array('data-options' => json_encode($filterparams['options']), 'data-dataoptions' => json_encode($filterparams['dataoptions']), 'data-filterdata' => json_encode($filterparams['filterdata'])));
echo $OUTPUT->footer();
