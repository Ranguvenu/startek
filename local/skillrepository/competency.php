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
 * @subpackage local_skillrepository
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/skillrepository/lib.php');

global $CFG, $PAGE;
$advance = get_config('local_skillrepository','advance');
if($advance != 1)
{
    print_error(" You don't have permissions to access this page.");
}

$systemcontext = (new \local_skillrepository\lib\accesslib())::get_module_context();
$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$delete_id = optional_param('delete_id', 0, PARAM_INT);
$submitbutton = optional_param('submitbutton', '', PARAM_RAW);
$returnurl = new moodle_url('/local/skillrepository/competency.php');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/skillrepository/js/script.js');
$PAGE->requires->js('/local/skillrepository/js/jquery.dataTables.js',true);
$PAGE->requires->css('/local/skillrepository/css/jquery.dataTables.css');
//$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/skillrepository/competency.php');
require_login();

if (!has_capability('local/skillrepository:create_skill', (new \local_skillrepository\lib\accesslib())::get_module_context()) && !is_siteadmin()) {
    print_error('Sorry, You are not accessable to this page');
}

if ($id > 0){
    $string = get_string('skill_category', 'local_skillrepository') . ':' . get_string('edit_skill_category', 'local_skillrepository');
} else {
    $string = get_string('skill_category', 'local_skillrepository') . ':' . get_string('create_newskill_category', 'local_skillrepository');
}
$title=get_string('manage_skill_category', 'local_skillrepository');
// exit;
$PAGE->set_title($title);

$PAGE->navbar->add(get_string('manage_skill_category', 'local_skillrepository'));
$PAGE->set_heading(get_string('manage_skill_category', 'local_skillrepository'));
$PAGE->requires->js_call_amd('local_skillrepository/newcategory', 'load', array());
$PAGE->requires->js_call_amd('local_skillrepository/newassigncompetencylevel', 'load', array());
$PAGE->requires->js_call_amd('theme_epsilon/quickactions', 'quickactionsCall');
$PAGE->requires->js_call_amd('local_skillrepository/newassignlevel', 'load', array());
$PAGE->requires->js_call_amd('local_skillrepository/newassignlevel', 'getLevels', array());
$PAGE->requires->js_call_amd('local_skillrepository/newassignskill', 'load', array());
$PAGE->requires->js_call_amd('local_costcenter/costcenterdatatables', 'costcenterDatatable', array());
$PAGE->requires->js_call_amd('local_skillrepository/newassigncourse', 'load', array());
echo $OUTPUT->header();
$lib =  new local_skillrepository\event\insertcategory();
$repository = new local_skillrepository\event\insertrepository();
$renderer = $PAGE->get_renderer('local_skillrepository');
if ($id > 0) {
    $tool = $DB->get_record('local_skill_categories', array('id' => $id));
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

if($id > 0){
    $collapse = false;
}else{
    $collapse = true;
}

/* Start of delete the skill category */

if ($delete) {
    if ($confirm and confirm_sesskey()) {        
        $result = $repository->skillrepository_opertaions('local_skill_categories', 'delete', '', 'id', $delete_id);
        redirect($returnurl);
    }
    $strheading = get_string('deleteskill_category', 'local_skillrepository');
    $PAGE->set_title($strheading);

    echo $OUTPUT->heading($strheading);

        $yesurl = new moodle_url('/local/skillrepository/competency.php', array('delete_id' => $delete_id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm_skillcategory', 'local_skillrepository');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    
    echo $OUTPUT->footer();
    die;
}

//this is the return url
$editform =  new local_skillrepository\form\skill_category_form(null, array('id'=>$id));
    
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) { 
    $skill_category = $lib->create_skill_category($data);
    redirect($returnurl);
}else{
    //we will get when ever we submit the form
    if($submitbutton){
        $collapse = false;
    }    
}

$skill_categories = $repository->skillrepository_opertaions('local_skill_categories', 'fetch-multiple','','','');
if(empty($skill_categories)){
    $collapse = false;
}
echo $renderer->get_top_action_buttons_skills();

echo $renderer->competency_view();

echo $OUTPUT->footer();
