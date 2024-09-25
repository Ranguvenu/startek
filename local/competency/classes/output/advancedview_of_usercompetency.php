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
use core_competency\course_module_competency;
use local_competency\competencyview;


/**
 * Class containing data for course competencies page
 *
 * @copyright  2018 hemalatha c arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advancedview_of_usercompetency implements renderable, templatable {

    /** @var int $courseid Course id for this page. */
    protected $courseid = null;

    /** @var context $context The context for this page. */
    protected $context = null;

    /** @var \core_competency\course_competency[] $competencies List of competencies. */
    protected $coursecompetencylist = array();

    /** @var bool $canmanagecompetencyframeworks Can the current user manage competency frameworks. */
    protected $canmanagecompetencyframeworks = false;

    /** @var bool $canmanagecoursecompetencies Can the current user manage course competency frameworks.. */
    protected $canmanagecoursecompetencies = false;

    /** @var string $manageurl manage url. */
    protected $manageurl = null;


    protected $competencyframeworkid=null;

    /**
     * Construct this renderable.
     * @param int $courseid The course record for this page.
     */
    public function __construct($competencyid, $courseid) {
        global $USER;
      
        $this->competencyid = $competencyid;      
        $this->courseid = $courseid; 
        $this->userid = $USER->id;   
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
       
       // $data->competencies = array();
        $data->competencyframeworkid =  $this->competencyframeworkid;
        $helper = new performance_helper();
        $competency = competencyview::competency_record($this->competencyid);

        $competencycontext = $helper->get_context_from_competency($competency[$this->competencyid]);

        $compexporter = new competency_exporter($competency[$this->competencyid], array('context' => $competencycontext));

        $context = context_course::instance($this->courseid);

        $gradable = is_enrolled($context, $USER, 'moodle/competency:coursecompetencygradable');
        if ($gradable) {
            $usercompetencycourses = api::list_user_competencies_in_course($this->courseid, $USER->id);
            $data->gradableuserid = $USER->id;
        }
        
        $fastmodinfo = get_fast_modinfo($this->courseid); 

        //-------fetching competency module-----------      
        $competency_coursemodules = api::list_course_modules_using_competency($this->competencyid, $this->courseid);
        foreach ($competency_coursemodules as $cmid) {
            $cminfo = $fastmodinfo->cms[$cmid];
            $cmexporter = new course_module_summary_exporter(null, array('cm' => $cminfo));
            $compcourse_modules[] = $cmexporter->export($output);
        }
        //----user achieved competency grade and scale 
         $usercompetencycourse = api::get_user_competency_in_course($this->courseid, $this->userid, $this->competencyid);

          $related = array(
                        'scale' => $helper->get_scale_from_competency($competency[$this->competencyid])
                    );
          $exporter = new user_competency_course_exporter($usercompetencycourse,$related);
          $usercompetencycourse = $exporter->export($output);

         
         $competency_assignedcmodules_itsgrade=array('competencymodule'=>$compcourse_modules);
         if($usercompetencycourse)
          $competency_assignedcmodules_itsgrade['usercompetencycourse']=$usercompetencycourse;
          
        //----Rest of the modules, which is not assigned to competency 
          $cmlist = competencyview::list_course_modules($this->courseid);
          $rest_coursemodules=array_diff($cmlist, $competency_coursemodules);

          foreach ($rest_coursemodules as $cmid) {
            if (competencyview::validate_course_module($cmid, false)) {
                //array_push($result, $cmid);
                $cminfo = $fastmodinfo->cms[$cmid];
                $cmexporter = new course_module_summary_exporter(null, array('cm' => $cminfo));
                $restcoursemodules[]= $cmexporter->export($output);          
              }
          }


          $data->competency = $compexporter->export($output);  
          $data->competency_assignedcmodules =json_encode($competency_assignedcmodules_itsgrade);
          $data->restcoursemodules = $restcoursemodules;            
         


        
       
        return $data; 

    } // end of  function



} // end of class



