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
use block_learnerscript\local\querylib;
defined('MOODLE_INTERNAL') || die();
class report_coursescompletions extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        
        parent::__construct($report, $reportproperties);
        $this->columns = ['userfield' => ['userfield'], 'coursefield' => ['coursefield'], 'coursescompletionscolumns' => ['coursename','enrolledon','completion_percentage','completionstatus','completiondate','startdate','enddate','coursestartdate','completiondays','courseactivitiescount','activitycmplcount','activity_completion_percentage']];
        $this->components = array('columns', 'conditions', 'filters','permissions','orderable');
        $this->filters = array('organization', 'departments','subdepartments','level4department','user','completionstatus');
        $this->parent = true;
        $this->basicparams = array(['name' => 'course']);
        $this->orderable = array('coursename');
        $this->defaultcolumn = 'ra.id';
        $this->userid = isset($report->userid) ? $report->userid : 0;
        $this->reportid = isset($report->reportid) ? $report->reportid : 0;
        $this->scheduleflag = isset($report->scheduling) ? true : false;
    }

    function init() {
        parent::init();
    }

    function count() {
        $this->sql = "SELECT COUNT(ra.id) ";
    }

    function select() {
        $this->sql = " SELECT ra.id as assignmentid ,u.id as userid, CONCAT(u.firstname,' ',u.lastname) AS fullname, u.*
                        , ra.timemodified as enrolledon
                        , c.startdate as startdate
                        , c.enddate as enddate
                        , c.id as courseid 
                        , c.fullname as coursename
                        , c.shortname, c.open_categoryid, c.visible, c.open_skill
                        , c.open_level
                        , c.open_coursecompletiondays as completiondays, cc.timecompleted as completiondate, c.open_path as course_open_path " ;
        parent::select();
    }

    function from() {
        $this->sql .= " FROM {course} c ";
    }

    function joins() {
        global $DB;
        $employeerole = $DB->get_field_sql("SELECT id FROM {role} WHERE shortname IN ('employee','student')");
       
        $this->sql .="  JOIN {context} AS cxt ON cxt.contextlevel = 50 AND cxt.instanceid=c.id
                        JOIN {role_assignments} as ra ON cxt.id=ra.contextid AND ra.roleid = {$employeerole}
                        JOIN {user} u ON ra.userid = u.id 
                        JOIN {local_costcenter} lc ON concat('/',u.open_path,'/') LIKE concat('%/',lc.id,'/%') AND lc.depth = 1
                        LEFT JOIN {course_completions} as cc ON cc.course = c.id AND u.id = cc.userid ";

        parent::joins();
    }

    function where() {
        
        global $DB,$CFG;    
        $this->sql .= " WHERE c.id <> :siteid  AND c.open_coursetype = :type  ";
        $this->params['siteid'] = SITEID;
        $this->params['type'] = 0;
        
        require_once($CFG->dirroot . "/blocks/learnerscript/lib.php");
     
        if (is_siteadmin()) {
            $this->sql .= "";
        } else if ($this->scheduleflag && $this->reportid!=0 && $this->userid != 0) {             
            $usercostcenterpathconcatsql = scheduled_report( $this->reportid,$this->scheduleflag,$this->userid,'c.open_path','u.open_path');
            $this->sql .= $usercostcenterpathconcatsql;        
        }else{
            $costcenterpathconcatsql = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.open_path');
            $usercostcenterpathconcatsql = get_user_costcenterpath($USER->open_path);
            $costcenterpathconcatsql  = $costcenterpathconcatsql  . $usercostcenterpathconcatsql  ; 
            $this->sql .= $costcenterpathconcatsql;    
        } 

        parent::where();
    }


    function search() {
        if (isset($this->search) && $this->search) {
            $fields = array('c.fullname',"CONCAT(u.firstname,' ',u.lastname)",'u.email','u.open_employeeid');
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
      
        if (!empty($this->params['filter_course'])) {
            $courseid = $this->params['filter_course'];
            $this->sql .= " AND c.id = :courseid ";
            $this->params['courseid'] = $courseid;
        }

        if (!empty($this->params['filter_user'])) {
            $userid = $this->params['filter_user'];
            $this->sql .= " AND u.id = :userid ";
            $this->params['userid'] = $userid;
        }

        if($this->ls_startdate > 0 && $this->ls_enddate > 0){
            $this->sql .= " AND cc.timecompleted > :report_startdate ";
            $this->params['report_startdate'] = $this->ls_startdate;

            $this->sql .= " AND cc.timecompleted < :report_enddate ";
            $this->params['report_enddate'] = $this->ls_enddate;
        }

        if(isset($this->params['filter_completionstatus'])){

            if ($this->params['filter_completionstatus'] == 1) {
            $this->sql .= " AND cc.timecompleted IS NOT NULL ";
            }

            if ($this->params['filter_completionstatus'] == 0) {
                $this->sql .= " AND cc.timecompleted IS NULL ";
            }
        }

    }

    public function get_rows($courseusers) {    
        global $DB;
        if($courseusers){
            foreach($courseusers AS $user){
                $sql = "SELECT d.*, f.shortname, f.name, f.datatype
                          FROM {user_info_data} d ,{user_info_field} f
                         WHERE f.id = d.fieldid AND d.userid = ?";
                if ($profiledata = $DB->get_records_sql($sql, array($user->userid))) {
                    foreach ($profiledata as $p) {
                        if ($p->datatype == 'checkbox') {
                            $p->data = ($p->data) ? get_string('yes') : get_string('no');
                        }
                        if ($p->datatype == 'datetime') {
                            $p->data = userdate($p->data);
                        }
                        $user->{'profile_'.$p->name} = $p->data;
                    }
                }
            }
        }
        return $courseusers;
    }
}
