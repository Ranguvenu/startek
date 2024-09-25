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
 * @subpackage local_courses
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/onlinetests/lib.php'); 
global $PAGE,$USER;
$courseid = required_param('courseid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
require_login();

$categorycontext = (new \local_onlinetests\lib\accesslib())::get_module_context();

$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_onlinetests/onlinetests', 'usersdatatable', array(array('onlinetestid' => $onlinetestid,'action'=>'enrolledusers')));
$records = get_user_enrolsettings($courseid);
$PAGE->set_context($categorycontext);
$PAGE->set_url('/local/onlinetests/check_enrolsettings.php');
$PAGE->set_title(get_string('onlinetests','local_onlinetests'));
$PAGE->set_heading($records->fullname);
$PAGE->navbar->ignore_active();
echo $OUTPUT->header();

if($records->timestart != 0 && $records->timeend == 0){
    if($records->timestart <= time()){
        if($courseid==1){
            redirect($CFG->wwwroot .'/mod/quiz/view.php?id='.$cmid) ;
        }else{
            redirect($CFG->wwwroot .'/course/view.php?id='. $courseid);
        }  
    }else{
        echo "<div style='margin-top=50px;'><p style='font-size:25px;text-align:center;padding-top:20px;'>You are not access the online exam before '<span style='font-weight:bold;'>".userdate($records->timestart,'%d %b %Y %H:%M')."</span>'</p></div>";
    }

}else if($records->timestart != 0 && $records->timeend != 0){
    if($records->timestart <= time() && $records->timeend > time()){  

        if($courseid==1){
            redirect($CFG->wwwroot .'/mod/quiz/view.php?id='.$cmid) ;
        }else{
            redirect($CFG->wwwroot .'/course/view.php?id='. $courseid);
        }      
    }else if($records->timestart > time() && $records->timeend > time()){
        echo "<div style='margin-top=50px;'><p style='font-size:25px;text-align:center;padding-top:20px;'>You are not access the online exam before '<span style='font-weight:bold;'>".userdate($records->timestart,'%d %b %Y %H:%M')."</span>'</p></div>";

    }else{
        echo "<div style='margin-top=50px;'><p style='font-size:25px;text-align:center;padding-top:20px;'>Your exam date (<span style='font-weight:bold;'>".userdate($records->timeend,'%d %b %Y %H:%M')."</span>) is completed.</p></div>";
    }

}else{
    if($courseid==1){
        redirect($CFG->wwwroot .'/mod/quiz/view.php?id='.$cmid) ;
    }else{
        redirect($CFG->wwwroot .'/course/view.php?id='. $courseid);
    }  
}

echo $OUTPUT->footer();
