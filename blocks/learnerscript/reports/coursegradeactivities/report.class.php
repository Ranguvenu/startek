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
 * @subpackage block_learnerscript
 */

use block_learnerscript\local\reportbase;
use block_learnerscript\report;


class report_coursegradeactivities extends reportbase implements report
{
  /**
   * [__construct description]
   * @param [type] $report           [description]
   * @param [type] $reportproperties [description]
   */
  public function __construct($report, $reportproperties)
  {
    parent::__construct($report);
    $this->components = array('columns', 'filters', 'permissions');
    $columns = array('completionstatus', 'completiondate', 'totalnoofactivities', 'noofactivitiescompleted');
    $columnsarray = array('coursefield' => ['coursefield'], 'userfield' => ['userfield'], 'coursegradeactivities' => $columns);
    $this->basicparams = array(['name' => 'course']);
    $this->columns = $columnsarray;
    $this->filters = array('organization', 'departments', 'subdepartments','level4department');
    $this->defaultcolumn = 'u.id';
    $this->userid = isset($report->userid) ? $report->userid : 0;
    $this->reportid = isset($report->reportid) ? $report->reportid : 0;
    $this->scheduleflag = isset($report->scheduling) ? true : false;
  }

  function init()
  {
    parent::init();
  }

  function count()
  {
    $this->sql = "SELECT COUNT(u.id) ";
  }

  function select()
  {
    $this->sql = "SELECT u.id as userid, c.id as courseid, CONCAT(u.firstname,' ',u.lastname) AS fullname,cc.timecompleted ,u.*, 
                    c.open_path as course_open_path,c.fullname as coursename, c.shortname, c.open_categoryid, c.visible, c.open_skill, c.open_level";

    parent::select();
  }

  function from()
  {
    $this->sql .= " FROM {course} c ";
  }

  function joins()
  {
    global $DB;
    $employeerole = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname IN ('employee','student')");
    $this->sql .=" JOIN {context} AS cxt ON cxt.contextlevel = 50 AND cxt.instanceid=c.id
                  JOIN {role_assignments} as ra ON cxt.id=ra.contextid AND ra.roleid = {$employeerole}
                  JOIN {user} u ON ra.userid = u.id 
                  JOIN {local_costcenter} lc ON concat('/',u.open_path,'/') LIKE concat('%/',lc.id,'/%') AND lc.depth = 1
                  LEFT JOIN {course_completions} as cc ON cc.course = c.id AND u.id = cc.userid ";
    parent::joins();
  }

  function where()
  {
    global $USER,$CFG;
    $this->sql .= " WHERE c.id <> :siteid AND c.open_coursetype = :type  AND c.visible = 1 ";
    $this->params['siteid'] = SITEID;
    $this->params['type'] = 0;
    
    $costcenterpathconcatsql = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname = 'c.open_path');

    require_once($CFG->dirroot . "/blocks/learnerscript/lib.php");
     
    if (is_siteadmin()) {
        $this->sql .= "";
    } else if ($this->scheduleflag && $this->reportid!=0 && $this->userid != 0) {             
        $usercostcenterpathconcatsql = scheduled_report( $this->reportid,$this->scheduleflag,$this->userid,'c.open_path','u.open_path');
        $this->sql .= $usercostcenterpathconcatsql;        
    }else{
        $usercostcenterpathconcatsql = get_user_costcenterpath($USER->open_path);
        $costcenterpathconcatsql  = $costcenterpathconcatsql  . $usercostcenterpathconcatsql  ; 
        $this->sql .= $costcenterpathconcatsql;    
    } 
    
    parent::where();
  }

  function search()
  {
    if (isset($this->search) && $this->search) {
      $fields = array('c.fullname', "CONCAT(u.firstname,' ', u.lastname)", 'u.email', 'u.open_employeeid');
      $fields = implode(" LIKE '%$this->search%' OR ", $fields);
      $fields .= " LIKE '%$this->search%' ";
      $this->sql .= " AND ($fields) ";
    }
  }

  function filters()
  {

    if ($this->params['filter_organization'] > 0) {
        $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_organization'], 'path');
        $this->sql .= " AND concat(c.open_path,'/') like :orgpath ";
        $this->params['orgpath'] = $orgpath.'/%';
    }
    if ($this->params['filter_departments'] > 0) {
        $l2dept = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_departments'], 'path');
        $this->sql .= " AND concat(c.open_path,'/') like :l2dept ";
        $this->params['l2dept'] = $l2dept.'/%';
    }

    if ($this->params['filter_subdepartments'] > 0) {
        $l3dept = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_subdepartments'], 'path');
        $this->sql .= " AND concat(c.open_path,'/') like :l3dept ";
        $this->params['l3dept'] = $l3dept.'/%';
    }

    if ($this->params['filter_level4department'] > 0) {
        $l4dept = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_level4department'], 'path');
        $this->sql .= " AND concat(c.open_path,'/') like :l4dept ";
        $this->params['l4dept'] = $l4dept.'/%';
    }


    if (isset($this->params['filter_course']) && $this->params['filter_course'] > 0) {
      $this->sql .= " AND c.id = :courseid ";
      $this->params['courseid'] = $this->params['filter_course'];
    }
   
  }

  public function get_rows($courseusers)
  {
    global $DB;

    $finalelements = array();
    if ($courseusers) {
      $data = array();
      foreach ($courseusers as $courseuser) {

        if ($courseuser->timecompleted) {
          $courseuser->completionstatus = get_string('completed', 'block_learnerscript');
          $courseuser->completiondate = date('d-M-Y', $courseuser->timecompleted);
        } else {
          $courseuser->completionstatus = get_string('not_completed', 'block_learnerscript');
          $courseuser->completiondate = 'NA';
        }

        $sql =  "SELECT cm.*,m.name as itemname
              FROM {course_modules} cm
              JOIN {modules} m ON cm.module = m.id
              WHERE cm.deletioninprogress = 0 AND cm.visible=1 AND cm.course = $courseuser->courseid ";
        $criteria = $DB->get_records_sql($sql);

        $finalassesresult = $this->get_finalassesment($courseuser->courseid, $courseuser->userid);

        $courseuser->finalassesment = $finalassesresult['finalassesment'];
        $courseuser->noofattempts = $finalassesresult['noofattempts'];
        $courseuser->finalassesmentgrade = $finalassesresult['finalassesmentgrade'];

        // $courseuser->finalassesment = $this->get_finalassesment($courseuser->courseid,$courseuser->userid);
        $courseuser->totalnoofactivities = $this->get_totalactivitiescount($courseuser->courseid);
        $courseuser->noofactivitiescompleted = $this->get_completedactivitiescount($courseuser->userid, $courseuser->courseid);
        if ($criteria) {
          $activitycount = 0;
          foreach ($criteria as $key => $class) {
            $sqllist = "SELECT gi.id,gi.itemname as name,gi.grademax
                        FROM  {grade_items} gi
                        WHERE gi.courseid= $courseuser->courseid AND gi.itemtype = 'mod'
                        AND gi.iteminstance = '$class->instance' AND gi.itemmodule = '$class->itemname' ";
            $data_list = $DB->get_record_sql($sqllist);


            if ($data_list) {
              $activity1 = $class->itemname;
              $classid = "classid_$data_list->id";
              $activity_grades = $this->get_activitygrades($courseuser->userid, $courseuser->courseid, $class->instance, $activity1, $data_list->id);
              if ($activity_grades == 'NA') {
                $courseuser->$classid = "N/A";
              } else if (!empty($activity_grades) && $activity_grades != 'NA') {
                $courseuser->$classid = round($activity_grades, 2);
              } else {
                $courseuser->$classid = "Not Yet Graded";
              }

              $grade = "gradeclassid_$data_list->id";
              if ($data_list->grademax) {
                $courseuser->$grade = round($data_list->grademax, 2);
              } else {
                $courseuser->$grade = "N/A";
              }
            }
          }
        }
        $data[] = $courseuser;
      }
      return $data;
    }
    return $finalelements;
  }

  function get_activitygrades($userid, $courseid, $moduleid, $activity1, $itemid)
  {
    global $CFG, $DB;

    $checkgrade = $DB->get_field('grade_items', 'id', array('itemtype' => 'mod', 'itemmodule' => $activity1));

    if ($checkgrade) {
      $sql = "SELECT gg.id,ROUND(gg.finalgrade,2) AS finalgrade
        FROM {grade_grades} as gg
        WHERE gg.userid={$userid} AND gg.itemid =  $itemid ";

      $result = $DB->get_record_sql($sql);

      return $result->finalgrade;
    } else {
      return 'NA';
    }
  }

  function get_totalactivitiescount($courseid)
  {
    global $DB;
   
    $sql = "SELECT count(gi.id)
                FROM  {grade_items} gi
                WHERE gi.courseid= :courseid AND gi.itemtype = 'mod'";
    $count = $DB->count_records_sql($sql, array('courseid' => $courseid));
    return $count;
  }

  function get_completedactivitiescount($userid, $courseid)
  {
    global $DB;
    $sql = "SELECT count(cmc.id) FROM {course_modules_completion} AS cmc
                JOIN {course_modules} AS cm ON cm.id = cmc.coursemoduleid
                WHERE cmc.userid = :userid AND cm.course = :courseid";
    $count = $DB->count_records_sql($sql, array('userid' => $userid, 'courseid' => $courseid));
    return $count;
  }

  function get_finalassesment($courseid, $userid)
  {
    global $DB;

    $sql = " SELECT gi.id FROM  {grade_items} gi WHERE gi.courseid= :courseid AND gi.itemtype = 'mod' AND gi.itemmodule = 'quiz' ";
    $coursequizids = $DB->get_fieldset_sql($sql, array('courseid' => $courseid));

    $coursesectionssql = "SELECT cs.sequence FROM {course_sections} cs WHERE cs.course = $courseid ORDER BY cs.section desc";
    $coursesections = $DB->get_records_sql($coursesectionssql);
    $quizid = 0;
    $finalassesmentgrade = '-';
    $finalassesment = get_string('pending', 'local_onlineexams');
    foreach ($coursesections as $cs) {
      $sequence = explode(',', $cs->sequence);
      $sections = (array_reverse($sequence));
      foreach ($sections as $qid) {
        $quizid = (in_array($qid, $coursequizids)) ? $qid : 0;
        break;
      }
    }

    if ($quizid) {
      $gradeitem = $DB->get_record_select('grade_items', 'id = :id', array('id' => $quizid), '*');
      $sql = "SELECT MAX(attempt) FROM {quiz_attempts} WHERE quiz = :instanceid AND userid = :userid ";
      $noofattempts = $DB->get_field_sql($sql, array('userid' => $userid, 'instanceid' => $gradeitem->iteminstance));
      $gradepass = ($gradeitem->gradepass) ? round($gradeitem->gradepass, 2) : '-';
      if ($gradeitem->id)
        $usergrade = $DB->get_record_sql("select * from {grade_grades} where itemid = $gradeitem->id AND userid = $userid");
      if ($usergrade) {
        //$finalassesmentgrade = (round($usergrade->finalgrade, 2))*10 . '%';
        //$finalassesmentgrade = (round(($usergrade->finalgrade * 100) / $gradeitem->grademax)) . '%';
        $finalassesmentgrade = (round(($usergrade->finalgrade / $gradeitem->grademax) * 100)) . '%';
        if ($usergrade->finalgrade >= $gradepass) {
          $finalassesment = get_string('pass', 'local_onlineexams');
        } else {
          $finalassesment = get_string('fail', 'local_onlineexams');
        }
      }
    }
    $noofattempts = ($noofattempts) ? $noofattempts : 0;


    return array('noofattempts' => $noofattempts, 'finalassesment' => $finalassesment, 'finalassesmentgrade' => $finalassesmentgrade);
  }
}
