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

$id = required_param('courseid', PARAM_INT);

$params = array('id' => $id);
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

require_login($course);
\core_competency\api::require_enabled();

$context = context_course::instance($course->id);
$urlparams = array('courseid' => $id);

$url = new moodle_url('/local/competency/coursecompetencies1.php', $urlparams);

list($title, $subtitle) = \local_competency\page_helper::setup_for_course($url, $course);

$output = $PAGE->get_renderer('local_competency');
 $page = new \local_competency\output\user_competency_brief(6, $USER->id);


       // $competency = \core_competency\api::read_competency(6);
       // $framework = \core_competency\api::read_framework($competency->get_competencyframeworkid());
        

      //  $page = new \local_competency\output\competency_summary($competency, $framework, false,true);
     //   $renderable = new output\competency_summary($competency, $framework, $params['includerelated'], $params['includecourses']);

echo $output->header();
echo $output->heading($title);

//print_object($page);

echo $output->render($page);

echo $output->footer();
