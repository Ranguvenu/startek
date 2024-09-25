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
 * Class for loading/storing competencies from the DB.
 *
 * @package    core_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use context_system;
use lang_string;
use stdClass;
use core_competency\competency;
use core_competency\course_competency; 
use core_competency\persistent; 


require_once($CFG->libdir . '/grade/grade_scale.php');

/**
 * Class for loading/storing competencies from the DB.
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competencyview extends persistent{



    public static function get_the_usercompetency_courses($competencyid, $userid){
        global $DB;

        $competency_courses=$DB->get_records('competency_usercompcourse',
          array('competencyid'=>$competencyid, 'userid'=>$userid));
        
        return $competency_courses;
        
    } // end of function

   
    public static function competency_record($competencyid){
        global $DB;


        $results= $DB->get_records('competency',array('id'=>$competencyid));
        $instances = array();
        foreach ($results as $result) {
            $comp = new competency(0, $result);
           // print_object($comp);
            $instances[$comp->get('id')] = $comp;
        }
        //$results->close(); 

        return $instances;


    } // end of function


     /**
     * List the course_competencies in this course.
     *
     * @param int $courseid The course id
     * @return course_competency[]
     */    
    public static function competencycourse_info($competencyid, $courseid){
    

        global $DB;

        $sql = 'SELECT coursecomp.*
                  FROM {competency_coursecomp} coursecomp
              
                 WHERE coursecomp.courseid = ? and coursecomp.competencyid=?';
        $params = array($courseid, $competencyid);

        $sql .= ' ORDER BY coursecomp.sortorder ASC';
        $results = $DB->get_recordset_sql($sql, $params);

      //  print_object($results);
       // exit;
        $instances = array();
        foreach ($results as $result) {
           // print_object($result);
            array_push($instances, new course_competency(0, $result));
        }
        $results->close(); 
      //  $instances=new course_competency(0, $results);

       return $instances;
    } // end of function


    public static function get_user_competencyframework($userid){
      global $DB;
      
      //-----get  user enrolled courses--------
        $enrolledcourses = enrol_get_users_courses($userid);
        $usercourses = array();
        if($enrolledcourses){
            foreach($enrolledcourses as $enrolledcourse){
                $usercourses[$enrolledcourse->id] = $enrolledcourse->id;
            }
        }
        $imp_usercourses = implode(',',$usercourses);

      // print_object($enrolledcourses);
        if($usercourses){
        $sql ="SELECT cf.* from {competency_coursecomp} uc
              JOIN {competency} as cp ON cp.id=uc.competencyid 
              JOIN {competency_framework} as cf ON cf.id=cp.competencyframeworkid
              WHERE uc.courseid in ($imp_usercourses) " ; //group by cf.id

        //$params= array('userid' =>$userid);
        
        $results=$DB->get_records_sql($sql);
      }
        return $results;
    }


    public static function get_competencyframeworkid($competencyid){
     
      global $DB;    
      $competencyframeworkid=$DB->get_field('competency', 'competencyframeworkid', array('id' => $competencyid));
      return $competencyframeworkid;
    }
      
  
    /**
    * Return the module IDs and visible flags that include this competency in a single course.
    *
    * @param int $competencyid The competency id
    * @param int $courseid The course ID.
    * @return array of ints (cmids)
    */
    public static function list_course_modules($courseid) {
        global $DB;

       /* $results = $DB->get_records_sql('SELECT coursemodules.id as id
                                           FROM {' . self::TABLE . '} modcomp
                                           JOIN {course_modules} coursemodules
                                             ON modcomp.cmid = coursemodules.id
                                          WHERE modcomp.competencyid = ? AND coursemodules.course = ?',
                                          array($competencyid, $courseid)); */
        
        $results = $DB->get_records_sql('SELECT coursemodules.id as id               
                                           FROM {course_modules} coursemodules
                                           WHERE  coursemodules.course = ?',
                                          array($courseid));   


        return array_keys($results);
    } 


    /**
    * Validate if current user have acces to the course_module if hidden.
    *
    * @param mixed $cmmixed The cm_info class, course module record or its ID.
    * @param bool $throwexception Throw an exception or not.
    * @return bool
    */
    public static function validate_course_module($cmmixed, $throwexception = true) {
        $cm = $cmmixed;
        if (!is_object($cm)) {
            $cmrecord = get_coursemodule_from_id(null, $cmmixed);
            $modinfo = get_fast_modinfo($cmrecord->course);
            $cm = $modinfo->get_cm($cmmixed);
        } else if (!$cm instanceof cm_info) {
            // Assume we got a course module record.
            $modinfo = get_fast_modinfo($cm->course);
            $cm = $modinfo->get_cm($cm->id);
        }

        if (!$cm->visible) {
            if ($throwexception) {
                throw new require_login_exception('Course module is hidden');
            } else {
                return false;
            }
        }

        return true;
    } // validate_course_module

   
  public static function check_coursemodule_assignedto_competency($coursemodule){
      global $DB;

      if($DB->record_exists('competency_modulecomp', array('cmid'=>$coursemodule)))
        return true;
        else
        return false;
      
  } // end of check_coursemodule_assignedto_competency


  public static function get_competency_courses($competencyid){
    global $DB;

    $courselist=$DB->get_records_sql("select id,competencyid,ruleoutcome, courseid from {competency_coursecomp} where competencyid=$competencyid");
    
    return $courselist;   

  } // end of get_competency_courses

  
    
    

} // end of classes
