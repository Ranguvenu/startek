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
class report_skill extends reportbase implements report {

    public function __construct($report, $reportproperties) {
        global $DB;
        parent::__construct($report);
        $this->columns = ['userfield'=>['userfield'],'skill' => ['course','skill','level','achievedon']];
        $this->components = array('columns', 'filters','permissions');
        $this->filters = array('skills');
        $this->defaultcolumn = 'cc.id';
        $this->orderable = array('skill','course','level');
        $this->filters = array('skills');
        $this->userid = isset($report->userid) ? $report->userid : 0;
        $this->reportid = isset($report->reportid) ? $report->reportid : 0;
        $this->scheduleflag = isset($report->scheduling) ? true : false;

    }
        function init() {
        parent::init();
    }
    function count() {
        $this->sql = "SELECT COUNT(DISTINCT(cc.id)) ";
    }
    function select() {
        $this->sql ="SELECT (@cnt := @cnt + 1) AS rowNumber,cc.id,u.id as userid,c.fullname as course,ls.name as skill,u.*,
                    cl.name as level,cc.timecompleted as achievedon, CONCAT(u.firstname,' ',u.lastname) AS fullname,u.open_employeeid as employeeid"; 
        parent::select();
    }
    function from() {
        $this->sql .= " FROM {user} as u  ";
    }
    function joins() {
         $this->sql .= "JOIN {course_completions} as cc ON u.id = cc.userid 
                        JOIN {course} as c ON c.id = cc.course 
                        JOIN {local_skill} as ls ON ls.id = c.open_skill 
                        JOIN {local_course_levels} as cl ON c.open_level = cl.id
                        CROSS JOIN (SELECT @cnt := 0) AS dummy";
          parent::joins();
    }
    function where(){
        global $CFG,$USER;
         $this->sql .=  " WHERE c.visible = :visible 
                        AND cc.timecompleted != 'NULL' ";

        $this->params['visible']= 1;   
        // getscheduled report
      
        $costcenterpathconcatsql = (new \local_skillrepository\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='ls.open_path', null, 'lowerandsamepath');
        require_once($CFG->dirroot . "/blocks/learnerscript/lib.php");
        if (is_siteadmin()) {
            $this->sql .= "";
        } else if ($this->scheduleflag && $this->reportid!=0 && $this->userid != 0) {             
            $usercostcenterpathconcatsql = scheduled_report( $this->reportid,$this->scheduleflag,$this->userid,'ls.open_path','u.open_path');
            $this->sql .= $usercostcenterpathconcatsql;     
        }else{
            $usercostcenterpathconcatsql = get_user_costcenterpath($USER->open_path);
            $costcenterpathconcatsql  = $costcenterpathconcatsql  . $usercostcenterpathconcatsql  ;     
            $this->sql .= $costcenterpathconcatsql;    
        } 

         parent::where();
    }
    
    function search() {
       if (isset($this->search) && $this->search) {
            $fields = array('ls.name','c.fullname',"CONCAT(u.firstname,' ',u.lastname)",'u.open_employeeid');
            $fields = implode(" LIKE '%" . $this->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $this->search . "%' ";
            $this->sql .= " AND ($fields) ";
        } 
    }
    function filters() {    
       if (!empty($this->params['filter_skills'])) {
            $skill = $this->params['filter_skills'];
            $this->sql .= " AND ls.id = :skill";
            $this->params['skill'] = $skill;
        }
        if($this->ls_startdate > 0 && $this->ls_enddate > 0){
            $this->sql .= " AND cc.timecompleted > :report_startdate ";
            $this->params['report_startdate'] = $this->ls_startdate;

            $this->sql .= " AND cc.timecompleted < :report_enddate ";
            $this->params['report_enddate'] = $this->ls_enddate;
        }
    }    
    public function get_rows($skill) {
      
        return $skill;
    }
}
