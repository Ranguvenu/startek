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
 * Class containing data for course competencies page
 *
 * @package    local_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use context_system;
use context_course;
use core_competency\api;
use local_competency\course_competency_statistics;
use core_competency\competency;
use core_competency\course_competency;
use core_competency\external\performance_helper;
use core_competency\external\competency_exporter;
use core_competency\external\course_competency_exporter;
use core_competency\external\course_competency_settings_exporter;
use core_competency\external\user_competency_course_exporter;
use core_competency\external\user_competency_exporter;
use local_competency\external\competency_path_exporter;
use local_competency\external\course_competency_statistics_exporter;
use core_course\external\course_module_summary_exporter;
use local_competency\competencyview;


/**
 * Class containing data for course competencies page
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_competency_brief implements renderable, templatable {

    /** @var int $courseid Course id for this page. */
    protected $courseid = null;

    /** @var context $context The context for this page. */
    protected $context = null;

    /** @var \core_competency\course_competency[] $competencies List of competencies. */
    protected $coursecompetencylist = array();




    protected $competencyframeworkid=null;

    /**
     * Construct this renderable.
     * @param int $courseid The course record for this page.
     */
    public function __construct($competencyid) {
        global $USER;

        $this->competencyid = $competencyid;
        $userid = $USER->id;
        $this->userid= $userid;
        $this->competencycourselist = competencyview::get_the_usercompetency_courses($competencyid, $userid);
        $this->competencyframeworkid=competencyview::get_competencyframeworkid($competencyid);         
      
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer base.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $USER, $DB;

        $data = new stdClass();
        //$data->courseid = $this->courseid;
        //$data->pagecontextid = $this->context->id;
        $data->competencies = array();
        $data->competencyframeworkid =  $this->competencyframeworkid;



        // print_object($this->competencycourselist);
        foreach($this->competencycourselist as $competencycourse){      
    
        $context = context_course::instance($competencycourse->courseid);
        $gradable = is_enrolled($context, $USER, 'moodle/competency:coursecompetencygradable');
        if ($gradable) {
            $usercompetencycourses = api::list_user_competencies_in_course($competencycourse->courseid, $USER->id);
            $data->gradableuserid = $USER->id;
        }

        $ruleoutcomelist = course_competency::get_ruleoutcome_list();
        $ruleoutcomeoptions = array();
        foreach ($ruleoutcomelist as $value => $text) {
            $ruleoutcomeoptions[$value] = array('value' => $value, 'text' => (string) $text, 'selected' => false);
        } 

       
    

            $competency = competencyview::competency_record($this->competencyid);

            $coursecompetency_array =competencyview::competencycourse_info($this->competencyid, $competencycourse->courseid);
            $coursecompetency =   $coursecompetency_array[0];
             
           // $competency= $competency[$this->competencyid];
             $helper = new performance_helper();
            $context = $helper->get_context_from_competency($competency[$this->competencyid]);
           // print_object($context);    


            $compexporter = new competency_exporter($competency[$this->competencyid], array('context' => $context));
            $ccexporter = new course_competency_exporter($coursecompetency, array('context' => $context));

            $ccoutcomeoptions = (array) (object) $ruleoutcomeoptions;
            $ccoutcomeoptions[1]['selected'] = true;

            $coursemodules = api::list_course_modules_using_competency($this->competencyid, $competencycourse->courseid);

            $fastmodinfo = get_fast_modinfo($competencycourse->courseid);
            $exportedmodules = array();
            foreach ($coursemodules as $cmid) {
                $cminfo = $fastmodinfo->cms[$cmid];
                $cmexporter = new course_module_summary_exporter(null, array('cm' => $cminfo));
                $exportedmodules[] = $cmexporter->export($output);
            }
            // Competency path.
         /*   $pathexporter = new competency_path_exporter([
               // 'ancestors' => $competency->get_ancestors(),
                'framework' => $helper->get_framework_from_competency($competency[$this->competencyid]),
                'context' => $context
            ]); */

             $coursefullname = $DB->get_field('course','fullname',array('id'=>$competencycourse->courseid));
            $onerow = array(
                'competency' => $compexporter->export($output),
                'coursecompetency' => $ccexporter->export($output),
                'ruleoutcomeoptions' => $ccoutcomeoptions,
                'coursemodules' => $exportedmodules,
                'coursefullname' => $coursefullname,
                //'comppath' => $pathexporter->export($output)
            );
            if ($gradable) {
                $foundusercompetencycourse = false;
                foreach ($usercompetencycourses as $usercompetencycourse) {
                    if ($usercompetencycourse->get('competencyid') == $competency[$this->competencyid]->get('id')) {
                        $foundusercompetencycourse = $usercompetencycourse;
                    }
                }
                if ($foundusercompetencycourse) {
                    $related = array(
                        'scale' => $helper->get_scale_from_competency($competency[$this->competencyid])
                    );
                    $exporter = new user_competency_course_exporter($foundusercompetencycourse, $related);
                    $onerow['usercompetencycourse'] = $exporter->export($output);
                }
            }
            array_push($data->competencies, $onerow);
        }

       
  
        return $data;
    }// end of function

}// end of class



