<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * elearning  courses
 *
 * @package    block_userdashboard
 * @copyright  2018 Maheshchandra <maheshchandra@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_onlinetests\output;
require_once $CFG->dirroot . '/local/onlinetests/lib.php';

use context_course;
use html_writer;
use stdClass;
use moodle_url;
// use block_userdashboard\lib\onlinetests  as onlinetests_lib;
use local_onlinetests\local\userdashboard_content  as onlinetests_lib;
use block_userdashboard\includes\generic_content;

class onlinetests_courses implements \renderable, \templatable {

    //-----hold the courselist inprogress or completed
    private $courseslist;

    private $subtab='';

    private $onlineteststemplate=0;

    private $filter_text='';

    private $filter='';


    public function __construct($filter, $filter_text='', $offset, $limit){
        $this->inprogressCount = onlinetests_lib::inprogress_onlinetests_count($filter_text);
        $this->completedCount = onlinetests_lib::completed_onlinetests_count($filter_text);
        $this->enrolledCount = onlinetests_lib::enrolled_onlinetests_count($filter_text);
        switch ($filter){
            case 'inprogress':
                    $this->courseslist = onlinetests_lib::inprogress_onlinetests($filter_text, $offset, $limit);
                    $this->coursesViewCount = $this->inprogressCount;
                    $this->subtab='elearning_inprogress';
                break;

            case 'completed' :
                    $this->courseslist = onlinetests_lib::completed_onlinetests($filter_text, $offset, $limit);
                    $this->coursesViewCount = $this->completedCount;
                    $this->subtab='elearning_completed';
                break;
            case 'enrolled' :                           
                    $this->courseslist = onlinetests_lib::enrolled_onlinetests($filter_text, $offset, $limit);
                    $this->coursesViewCount = $this->enrolledCount;
                    $this->subtab = 'elearning_enrolled';
                break;           

        }
        $this->filter = $filter;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->filter_text = $filter_text;
        $this->onlineteststemplate=1;

    } // end of the function

    /**
     * Export the data.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template($output) {
        global $DB, $USER, $CFG, $OUTPUT;

        $data = new stdClass();
        $can_review = 0;
        $courses_active = '';
        $tabs = '';
        $data->inprogress_elearning = array();
        $data->enrolled_url = $CFG->wwwroot.'/local/onlinetests/userdashboard.php?tab=enrolled';
        $data->inprogress_url = $CFG->wwwroot.'/local/onlinetests/userdashboard.php?tab=inprogress';
        $data->completed_url = $CFG->wwwroot.'/local/onlinetests/userdashboard.php?tab=completed';
        // $inprogress_programs = onlinetests_lib::inprogress_onlinetests($this->filter_text, $this->offset, $this->limit);
        // $completed_onlinetests = onlinetests_lib::completed_onlinetests($this->filter_text, $this->offset, $this->limit);
        $inprogresscount = $this->inprogressCount;
        $completedcount = $this->completedCount;
        $total = $inprogresscount+$completedcount;
        // $total = courseslist_lib::gettotal_programs();
         if ($courseslist == '') {
            $courseslist = null;
        }

        $data->course_count_view =0;
        $data->total =$total;
        $data->index = $this->coursesViewCount > 10 ? 9 : $this->coursesViewCount - 1;
        $data->viewMoreCard = $this->coursesViewCount > 10 ? true : false;
        $data->inprogresscount= $this->inprogressCount;
        $data->completedcount = $this->completedCount;
        $data->functionname ='onlinetests_courses';
        $data->subtab= $this->subtab;
        $data->filter = $this->filter;
        $data->filter_text = $this->filter_text;
        $data->onlineteststemplate= $this->onlineteststemplate;
        $exam_count_view = $this->coursesViewCount;
        $data->view_more_url = $CFG->wwwroot.'/local/onlinetests/userdashboard.php?tab='.explode('_',$this->subtab)[1];
        $data->exam_count_view = $exam_count_view;
        if($exam_count_view > 2)
            $data->enableslider = 1;
        else
            $data->enableslider = 0;

        if (!empty($this->courseslist))
            $data->inprogress_elearning_available = 1;
        else
            $data->inprogress_elearning_available = 0;

        // if($this->subtab == 'inprogress_onlinetests')
        //     $data->sub_tab = 0;
        // else if($this->subtab == 'completed_onlinetests')
        //     $data->sub_tab = 1;
        // $data->table_class = '';

        if (!empty($this->courseslist)) {
            $data->course_count_view = generic_content::get_coursecount_class($this->courseslist);
            $i = 0;
            $userdate = \local_costcenter\lib::get_userdate('m-d-Y-h-i-s', time());
            $dateobject = explode('-', $userdate);
            $usertime = mktime($dateobject[3], $dateobject[4], $dateobject[5], $dateobject[0], $dateobject[1], $dateobject[2]);
            foreach ($this->courseslist as $inprogress_coursename) {
                $onerow = array();
                $onerow['index'] = $i++;
                // $i++;
                $cm = get_coursemodule_from_instance('quiz', $inprogress_coursename->quizid, 0, false, MUST_EXIST);
                $gradeitem = $DB->get_record('grade_items', array('iteminstance'=>$inprogress_coursename->quizid, 'itemmodule'=>'quiz', 'courseid'=>$inprogress_coursename->courseid));
                $sql="SELECT *
                    FROM {quiz_attempts}
                    WHERE id= (SELECT max(id) id
                                from {quiz_attempts}
                                where userid={$USER->id} and quiz={$inprogress_coursename->quizid})";
                $userattempt = $DB->get_record_sql($sql);
                $attempts = ($userattempt->attempt) ? $userattempt->attempt : 0;
                $grademax = ($gradeitem->grademax) ? round($gradeitem->grademax): '-';
                $gradepass = ($gradeitem->gradepass) ? round($gradeitem->gradepass): '-';
                $userquizrecord = $DB->get_record_sql("SELECT * FROM {local_onlinetest_users} where onlinetestid={$inprogress_coursename->id} AND userid = {$USER->id}");
                $enrolledon = \local_costcenter\lib::get_userdate("d/m/Y H:i", $userquizrecord->timecreated);
                if ($gradeitem->id)
                    $usergrade = $DB->get_record_sql("SELECT *
                                                    FROM {grade_grades}
                                                    WHERE itemid = {$gradeitem->id} AND userid = {$USER->id}");
                if ($usergrade) {
                    $mygrade = round($usergrade->finalgrade, 2);
                    if ($usergrade->finalgrade >= $gradepass) {
                        $completedon = \local_costcenter\lib::get_userdate("d/m/Y H:i", $usergrade->timemodified);
                        $status = get_string('completed','local_onlinetests');
                        $can_review = 1;
                        // if ($this->subtab == 'elearning_inprogress') // incomplete
                        //  continue;
                    } else {
                        $status = get_string('incompleted','local_onlinetests');
                        $completedon = '-';
                        // if ($this->subtab == 'elearning_completed') // complete
                        //  continue;
                    }

                } else {
                    // if ($this->subtab == 'elearning_inprogress') // incomplete
                    //      continue;
                    $mygrade = '-';
                    $status = get_string('pending','local_onlinetests');
                    $completedon = '-';
                    $attempts = 0;
                }
                if($inprogress_coursename->timeopen == 0 AND $inprogress_coursename->timeclose == 0) {
                    $dates= get_string('open', 'local_onlinetests');
                } elseif(!empty($inprogress_coursename->timeopen) AND empty($inprogress_coursename->timeclose)) {
                    $dates = 'From '. \local_costcenter\lib::get_userdate("d/m/Y H:i", $inprogress_coursename->timeopen);
                } elseif (empty($inprogress_coursename->timeopen) AND !empty($inprogress_coursename->timeclose)) {
                    $dates = 'Ends on '. \local_costcenter\lib::get_userdate("d/m/Y H:i", $inprogress_coursename->timeclose);
                } else {
                    $dates = \local_costcenter\lib::get_userdate("d/m/Y H:i", $inprogress_coursename->timeopen).  ' - '  . \local_costcenter\lib::get_userdate("d/m/Y H:i", $inprogress_coursename->timeclose);
                }
                $testid = $inprogress_coursename->id;
                $testfullname = $inprogress_coursename->name;
                $testname = strlen($testfullname) > 35 ? substr($testfullname, 0, 35)."..." : $testfullname;
                if(!is_siteadmin()){
                    $switchedrole = $USER->access['rsw']['/1'];
                    if($switchedrole){
                        $userrole = $DB->get_field('role', 'shortname', array('id' => $switchedrole));
                    }else{
                        $userrole = null;
                    }
                    if(is_null($userrole) || $userrole == 'user'){
                        $certificate_plugin_exist = \core_component::get_plugin_directory('tool', 'certificate');
                        $certificate_exists = false;
                        if($certificate_plugin_exist){
                            if(!empty($inprogress_coursename->certificateid)){
                                $certificate_exists = true;
                                $usergrade = $DB->get_record_sql("select * from {grade_grades} where itemid = ? AND userid = ? ", [$gradeitem->id, $USER->id]);
                                if($usergrade) {
                                    $mygrade = round($usergrade->finalgrade, 2);
                                    if ($usergrade->finalgrade >= $gradepass) {
                                        $can_review = 1;
                                        $status = 'Completed';
                                        $certificate_download= true;
                                    } else {
                                        $status = 'Incomplete';
                                        $certificate_download = false;
                                    }
                                }
                            $gcertificateid = $DB->get_field('local_onlinetests', 'certificateid', array('id'=>$inprogress_coursename->id));
                            $certid = $DB->get_field('tool_certificate_issues', 'code', array('moduleid'=>$inprogress_coursename->id,'userid'=>$USER->id,'moduletype'=>'onlinetest'));

                            $certificateid = $certid;

                            }
                        }
                    }
                }
                $onerow['id'] = $testid;
                $onerow['name'] = $testname;
                $onerow['testfullname'] = $testfullname;
                $onerow['maxgrade'] = $grademax;
                $onerow['passgrade'] = $gradepass;
                $onerow['mygrade'] = $mygrade;
                $onerow['attempts'] = $attempts;
                $onerow['enrolledon'] = $enrolledon;
                $onerow['completedon'] = $completedon;
                $onerow['status'] = $status;
                $onerow['canreview'] = $can_review;
                $onerow['dates'] = $dates;
                $onerow['userattemptid'] = $userattempt->id;
                $onerow['sesskey'] = sesskey();
                $onerow['configpath'] = $CFG->wwwroot;
                $onerow['certificate_exists'] = $certificate_exists;
                $onerow['certificate_download'] = $certificate_download;
                $onerow['certificateid'] = $certificateid;
                $onerow['certid'] = $certid;
                $onerow['testid'] = $testid;

                $usertime = time();
                $onerow['notyetstarted'] = false;
                if(($inprogress_coursename->timeclose < $usertime && $inprogress_coursename->timeclose > 0)){
                    $onerow['starttest_url'] = 'javascript:void(0)';
                    $onerow['can_start_test'] = False;
                }elseif(($inprogress_coursename->timeopen > $usertime && $inprogress_coursename->timeopen > 0)){
                    $onerow['starttest_url'] = 'javascript:void(0)';
                    $onerow['notyetstarted'] = true;
                    $onerow['can_start_test'] = False;
                }else{
                    $onerow['starttest_url'] = $CFG->wwwroot .'/local/onlinetests/check_enrolsettings.php?courseid='. $inprogress_coursename->courseid.'&cmid='.$cm->id;                                  
                    $onerow['can_start_test'] = True;
                }
                array_push($data->inprogress_elearning, $onerow);
            }
        }
        $data->moduledetails = $data->inprogress_elearning;
        $data->menu_heading = get_string('onlineexams','block_userdashboard');
        $data->nodata_string = get_string('noonlinetestsavailable','block_userdashboard');
        return $data;
    } // end of export_for_template function
} // end of class
