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
 * @package   local
 * @subpackage  challenge
 * @author eabyas  <info@eabyas.in>
**/
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

//systemcontest defining
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);	

//set url and layout and title
$pageurl = new moodle_url('/local/challenge/index.php');
$PAGE->set_url($pageurl);
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->set_pagelayout('standard');
$heading = get_string('pluginname', 'local_challenge');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);

$PAGE->requires->js_call_amd('local_catalog/courseinfo', 'load', array());

$PAGE->navbar->add($heading);
$renderer = $PAGE->get_renderer('local_challenge');
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
echo $renderer->challenge_tabs_content();
echo $OUTPUT->footer();