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

class report_coursesoverview extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        parent::__construct($report, $reportproperties);
        $this->components = array('columns','ordering', 'filters', 'permissions', 'plot');
        $columns = array('coursefield'=>['coursefield'], 'coursesoverviewcolumns' => ['noofenrollments', 'noofcompletions','noofinprogress','percentofcompletions']);
        $this->columns = $columns;
        $this->filters = array('organization','departments', 'subdepartments','level4department', 'course');
        $this->orderable = array('coursename', 'noofenrollments', 'noofcompletions','noofinprogress','percentofcompletions');
        $this->defaultcolumn = 'c.id';   
        $this->userid = isset($report->userid) ? $report->userid : 0;
        $this->reportid = isset($report->reportid) ? $report->reportid : 0;
        $this->scheduleflag = isset($report->scheduling) ? true : false;    
    }

    function init() {
        parent::init();
    }

    function count() {
        $this->sql = "SELECT COUNT(c.id) ";
    }

    function select() {

        $this->sql = "SELECT c.id courseid, c.fullname as coursename, c.open_path as course_open_path,
                             c.shortname, c.open_categoryid, c.visible, c.open_skill, c.open_level " ;

        parent::select();
    }

    function from() {
        $this->sql .= " FROM {course} c ";
    }

    function joins() {
        $this->sql .= " JOIN {local_costcenter} AS co ON co.path = c.open_path" ;
        parent::joins();
    }

    function where() {
       
        global $CFG;
        $this->sql .= " WHERE c.id <> :siteid  AND c.open_coursetype = :type";
       
        $this->params['siteid'] = SITEID;
        $this->params['type'] = 0;
        
        $costcenterpathconcatsql = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.open_path');
        require_once($CFG->dirroot . "/blocks/learnerscript/lib.php");
        if (is_siteadmin()) {
            $this->sql .= "";
        } else if ($this->scheduleflag && $this->reportid!=0 && $this->userid != 0 ) {             
            $usercostcenterpathconcatsql = scheduled_report( $this->reportid,$this->scheduleflag,$this->userid,'c.open_path','');
            $this->sql .= $usercostcenterpathconcatsql;     
        }else{             
            $this->sql .= $costcenterpathconcatsql;
        }
        parent::where();
    }

    function search() {
        if (isset($this->search) && $this->search) {
            $fields = array("c.fullname");
            $fields = implode(" LIKE '%" . $this->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $this->search . "%' ";
            $this->sql .= " AND ($fields) ";
        }
    }

    function filters() {
        if ($this->params['filter_organization'] > 0) {
            $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_organization'], 'path');
            $this->sql .= " AND concat(c.open_path,'/') like :orgpath ";
            $this->params['orgpath'] = $orgpath.'/%';
        }
        if ($this->params['filter_departments']  > 0) {
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
     
        if(isset($this->params['filter_course']) && $this->params['filter_course'] > 0) {
            $this->sql .= " AND c.id = :courseid ";
            $this->params['courseid'] = $this->params['filter_course'];
        }
    }

    public function get_rows($courses) {
        global $DB,$USER;
        $data = array();
        if(!is_siteadmin()){
            list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/",$USER->open_path);
            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql('u.open_path',$org);               
        }
        if($courses){
            //$costcenterpathconcatsql = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path', null, 'lowerandsamepath');
            $enrolsql = "SELECT COUNT(ra.id)
                        FROM {role_assignments} ra
                        JOIN {context} cxt ON cxt.id = ra.contextid AND cxt.contextlevel = 50
                        JOIN {role} r ON r.id = ra.roleid
                        JOIN {user} u ON ra.userid = u.id
                        WHERE r.shortname  IN ('employee','student')
                            AND cxt.instanceid = :courseid {$costcenterpathconcatsql} ";
                                          
            $completedsql = "SELECT COUNT(ra.id)
                        FROM {role_assignments} ra
                        JOIN {context} AS cxt ON cxt.id = ra.contextid AND cxt.contextlevel = 50
                        JOIN {role} r ON r.id = ra.roleid
                        JOIN {user} u ON ra.userid = u.id
                        JOIN {course_completions} cc ON cxt.instanceid = cc.course
                            AND cc.userid = u.id AND cc.timecompleted IS NOT NULL
                        WHERE  r.shortname IN ('employee','student')
                            AND cxt.instanceid = :courseid {$costcenterpathconcatsql} ";
            if($this->ls_startdate > 0 && $this->ls_enddate > 0){
                $enrolsql .= " AND ra.timemodified > :ls_fstartdate ";
                $completedsql .= " AND cc.timecompleted > :ls_fstartdate ";
           
                $enrolsql .= " AND ra.timemodified < :ls_fenddate ";
                $completedsql .= " AND cc.timecompleted < :ls_fenddate ";
            }
            foreach ($courses as $course) {
                $course->noofenrollments = $DB->count_records_sql($enrolsql, array('courseid' => $course->courseid, 'ls_fstartdate' => $this->ls_startdate, 'ls_fenddate' => $this->ls_enddate));

                $course->noofcompletions = $DB->count_records_sql($completedsql, array('courseid' => $course->courseid, 'ls_fstartdate' => $this->ls_startdate, 'ls_fenddate' => $this->ls_enddate));
                $course->noofinprogress = ($course->noofcompletions > 0 && $course->noofenrollments  > 0) ? $course->noofenrollments-$course->noofcompletions : 0 ;
             
                $percentofcompletions = ($course->noofenrollments > 0 && $course->noofcompletions > 0) ? round(($course->noofcompletions/$course->noofenrollments)*100) : 0;
                $percentofcompletion = is_NAN($percentofcompletions) ? 0 : $percentofcompletions;
                $course->percentofcompletions = '<div class="progress">
                    <div class="progress-bar text-center" role="progressbar" aria-valuenow="'.$percentofcompletion.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentofcompletion.'%">
                        <span class="progress_percentage ml-2">'.$percentofcompletion.'%</span>
                    </div>
                </div>';    
                $data[] = $course;
            }
        }
        return $data;
  
     
    }
}
