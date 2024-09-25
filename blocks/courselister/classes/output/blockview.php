<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This courselister is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This courselister is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this courselister.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderable for course list view.
 *
 * @author eabyas  <info@eabyas.in>

 * @package Bizlms
 * @subpackage block_courselister
 */

namespace block_courselister\output;
use local_learningplan\lib\lib as lib;

use block_courselister\plugin;
use local_courses\output\courseevidenceview;
use local_courses\output\selfcompletion;


use context_course;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use moodle_url;
use moodle_exception;
use context_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Class view
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms
 * @subpackage block_courselister
 */
final class blockview implements renderable, templatable {

    /** @var stdClass|null */
    private $config;

    private $stable;

    private $filtervalues;

    /**
     * blockview constructor.
     * @param stdClass|null $config
     */
    public function __construct($config,$stable,$filtervalues) {
        $this->config = $config;
        $this->stable = $stable;
        $this->filtervalues = $filtervalues;
    }

    /**
     * Generate template
     * @param renderer_base $output
     * @return array
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $CFG, $USER,$PAGE;
        require_once($CFG->libdir.'/enrollib.php');

        switch ($this->config->coursetype) {
            case plugin::ENROLLEDCOURSES:
                $courses = plugin::enrol_get_my_courses($this->stable,$this->filtervalues);
                $courses =$courses['courses'];
                break;
            case plugin::LEARNINGPLANS:
                $courses = plugin::enrol_get_my_learningplans($this->stable,$this->filtervalues);
                $courses =$courses['learningplans'];
                break;
            case plugin::LEARNINGPLANSALL:
                $courses = plugin::get_all_learningplans($this->stable,$this->filtervalues);
                $courses =$courses['alllearningplans'];
                break;
        }
        $row=array();
        if($this->config->coursetype==plugin::LEARNINGPLANS || $this->config->coursetype==plugin::LEARNINGPLANSALL){
            if($this->config->coursetype==plugin::LEARNINGPLANSALL){
                $courseurl = new moodle_url('/local/learningplan/plan_view.php');
            }else{
                $courseurl = new moodle_url('/local/learningplan/view.php');
            }
            $numco = count($courses);
               //course image
            if(file_exists($CFG->dirroot.'/local/includes.php')){
                require_once($CFG->dirroot.'/local/includes.php');
                $learningplan_lib = new lib();

                          
            }   
            foreach ($courses as $course) {
                $contextitem = (object)[
                    'coursenums' => $numco,
                    'courseid' => $course->id,
                    'url' => $courseurl->out(false, ['id' => $course->id]),
                    'title' => format_string($course->fullname),
                    'description' => format_text(
                        $course->summary
                    ),
                    'duration' => plugin::dateDifference($course->startdate,$course->enddate),
                    'modules' => ''
                ];
                if(file_exists($CFG->dirroot.'/local/includes.php')){
   
                    $contextitem->imageurl =  $learningplan_lib->get_learningplansummaryfile($course->id);
                      
                }
                if ($course->enddate > 0) {
                    $contextitem->completebefore = userdate($course->enddate);
                }
                if (plugin::istocourselister()) {
                    $course->coursetype=$this->config->coursetype;
                    $icondata = $output->course_icon($course);
                    $contextitem->coursetype = plugin::coursetype($course);
                    $contextitem->iconurl = $icondata[0];
                    $contextitem->iconalt = $icondata[1];
                }
                $row[]=(array)$contextitem;
            }
        }else{
            $courseurl = new moodle_url('/course/view.php');
            $numco = count($courses);
               //course image
            if(file_exists($CFG->dirroot.'/local/includes.php')){
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new \user_course_details();              
            }   
            $renderer=$PAGE->get_renderer('local_courses');
            foreach ($courses as $course) {
                $contextitem = (object)[
                	'coursenums' => $numco,
                    'courseid' => $course->id,
                    'url' => $courseurl->out(false, ['id' => $course->id]),
                    'title' => format_string($course->fullname),
                    'description' => format_text(
                        $course->summary,
                        $course->summaryformat,
                        ['context' => context_course::instance($course->id)]
                    ),
                    'imageurl'   => $output->course_image($course),
                    'duration' => plugin::dateDifference($course->startdate,$course->enddate),
                    'modules' => plugin::course_modulescount($course->id),
                    'browseevidencescourse'=>$renderer->render(new courseevidenceview($course->id,$USER->id,'courseview')),
                    'selfcompletioncourse'=>$renderer->render(new selfcompletion($course->id,$USER->id))
                ];
                if(file_exists($CFG->dirroot.'/local/includes.php')){
                    $courseimage = $includes->course_summary_files($course);                
                    if(is_object($courseimage)){
                        $contextitem->imageurl = $courseimage->out();                    
                    }else{
                        $contextitem->imageurl = $courseimage;
                    }  
                }
                if ($course->enddate > 0) {
                    $contextitem->completebefore = userdate($course->enddate);
                }
                if (plugin::istocourselister()) {
                    $course->coursetype=$this->config->coursetype;
                    $icondata = $output->course_icon($course);
                    $contextitem->coursetype = plugin::coursetype($course);
                    $contextitem->iconurl = $icondata[0];
                    $contextitem->iconalt = $icondata[1];
                }
                $row[]=(array)$contextitem;
            }
        }
        return $row;
    }
}
