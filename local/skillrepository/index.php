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
global $USER, $CFG, $PAGE, $OUTPUT, $DB;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/skillrepository/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/skillrepository/js/jquery.dataTables.js',true);
$PAGE->requires->js('/local/skillrepository/js/skills_script.js',true); //For downloading csv
$PAGE->requires->js('/local/skillrepository/js/dataTables.buttons.min.js',true);
$PAGE->requires->js('/local/skillrepository/js/buttons.html5.min.js',true);
$PAGE->requires->css('/local/skillrepository/css/buttons.dataTables.min.css');


$id = optional_param('id', 0, PARAM_INT);
$delete_id = optional_param('delete_id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$submitbutton = optional_param('submitbutton', '', PARAM_RAW);

require_login();
$PAGE->set_url('/local/skillrepository/index.php');
$PAGE->set_context((new \local_skillrepository\lib\accesslib())::get_module_context());
$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('pluginname', 'local_skillrepository'));
$PAGE->navbar->add(get_string('dashboard', 'local_costcenter'), new moodle_url('/my'));
$PAGE->navbar->add(get_string('manage_skill_category', 'local_skillrepository'), new moodle_url('/local/skillrepository/competency_view.php'));
$PAGE->navbar->add(get_string('manage_skill', 'local_skillrepository'));
$systemcontext =(new \local_skillrepository\lib\accesslib())::get_module_context();
$PAGE->requires->js_call_amd('local_skillrepository/newassignlevel', 'load', array());
$PAGE->requires->js_call_amd('local_skillrepository/newcategory', 'load', array());
$PAGE->requires->js_call_amd('local_skillrepository/newrepository', 'load', array());

$id = 1; 

if (!has_capability('local/skillrepository:create_skill', (new \local_skillrepository\lib\accesslib())::get_module_context()) && !is_siteadmin()) {
    print_error(get_string('accessissue','local_skillrepository'));
}
$renderer = $PAGE->get_renderer('local_skillrepository');
$filterparams = $renderer->manageskills_content(true);
$repository = new local_skillrepository\event\insertrepository();
// if id exists, get curernt id details else create a new class

if($id > 0) {
    $toform = $repository->skillrepository_opertaions('local_skill', 'fetch-single', '', 'id', $id);
    $description= isset($toform->description['text']);
} else {
    $fromform = new stdClass();
}

// skill repository form
$mform = new local_skillrepository\form\skill_repository_form(null, array('id'=>$id)); //create object for Skill Repository Form

if ($mform->is_cancelled()) {
    redirect('index.php');
} else if ($fromform = $mform->get_data()) {
    $fromform->description = $fromform->description['text'];
    if($fromform->id){
        $result = $repository->skillrepository_opertaions('local_skill', 'update', $fromform);
    } else{
        $result = $repository->skillrepository_opertaions('local_skill', 'insert', $fromform);
    }
    if($result)
        redirect($PAGE->url);

} else {
    if($id > 0) {
        $collapse = false;
        $description = array();

        $description['format'] = 1;
        $mform->set_data($toform);
    }else if($submitbutton){
        $collapse = false;
    }else{
        $collapse = true;
    }
}

    $advance = get_config('local_skillrepository','advance');

$PAGE->set_heading(get_string('manage_skill', 'local_skillrepository'));
echo $OUTPUT->header();
echo "<ul class='course_extended_menu_list'>";
    if($advance == 1)
    {
        echo "  <li>
                    <div class='coursebackup course_extended_menu_itemcontainer'>
                        <a href='".$CFG->wwwroot."/local/skillrepository/competency_view.php' title='".get_string("back")."' class='course_extended_menu_itemlink'><i class='icon fa fa-reply'></i>
                        </a>
                    </div>
                </li>";
    }else
    {
        echo "  <li>
                    <div class='coursebackup course_extended_menu_itemcontainer'>
                        <a href='".$CFG->wwwroot."/local/skillrepository/level.php' title='".get_string('manage_level', 'local_skillrepository')."' class='course_extended_menu_itemlink'><i class='icon fa fa-list-alt'></i>
                        </a>
                    </div>
                </li>";
    }
   echo  "<li>
    <div class='coursebackup course_extended_menu_itemcontainer'>
          <a id='extended_menu_syncstats' title='".get_string('adnewrepository', 'local_skillrepository')."' class='course_extended_menu_itemlink' href='javascript:void(0)' onclick ='(function(e){ require(\"local_skillrepository/newrepository\").init({selector:\"createrepositorymodal\", contextid:$systemcontext->id, repositoryid:0}) })(event)'><i class='icon fa fa-plus' aria-hidden='true' aria-label=''></i>
          </a>
      </div>
</li>

</ul>";
$filterparams['submitid'] = 'form#filteringform';
echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);
$skill = $repository->skillrepository_opertaions('local_skill', 'fetch-multiple','','','');
if(empty($skill)){
    $collapse = false;
}
echo $renderer->manageskills_content();
echo $OUTPUT->footer();
