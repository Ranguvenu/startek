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

use block_learnerscript\local\querylib;
use block_learnerscript\local\reportbase;
use block_learnerscript\report;

class report_orgusers extends reportbase implements report {
    /**
     * [__construct description]
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        global $USER;
        parent::__construct($report);
        $this->components = array('columns', 'filters', 'permissions', 'calcs', 'plot');
        $this->columns = array('orgusers' => array('employeename','assignedroles'));
        //$this->courselevel = true;
        $this->parent = true;
        $this->filters = ['organization','users'];
        $this->orderable = array('employeename');
        $this->defaultcolumn = 'u.id';
        $this->userid = isset($report->userid) ? $report->userid : 0;
        $this->reportid = isset($report->reportid) ? $report->reportid : 0;
        $this->scheduleflag = isset($report->scheduling) ? true : false;

    }
   function init() {
        parent::init();
    }

    function count() {
        $this->sql = " SELECT COUNT(u.id) ";
    }
    function select() {
        $this->sql = "SELECT u.id, CONCAT(u.firstname,' ',u.lastname) as employeename,lc.id as costcenterid";
        parent::select();
    }
    function from() {
        $this->sql .= " FROM {user} u ";
    }
    function joins() { 
         $this->sql .= "JOIN {local_costcenter} lc ON concat('/',u.open_path,'/') LIKE concat('%/',lc.id,'/%') AND lc.depth = 1 ";
          parent::joins();
    }
    function where() {
        global $CFG;
        $this->sql .= " WHERE u.id > :id  ";
        $this->params['id'] = 2;
    
        $costcenterpathconcatsql = (new \local_users\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path', null, 'lowerandsamepath');

        // getscheduled report
        
        require_once($CFG->dirroot . "/blocks/learnerscript/lib.php");
        if (is_siteadmin()) {
            $this->sql .= "";
        } else if ($this->scheduleflag && $this->reportid!=0 && $this->userid != 0 ) {             
            $usercostcenterpathconcatsql = scheduled_report( $this->reportid,$this->scheduleflag,$this->userid,'','u.open_path');
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
            $fields = array("CONCAT(u.firstname, ' ' , u.lastname)", "lc.fullname");
            $fields = implode(" LIKE '%" . $this->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $this->search . "%' ";
            $this->sql .= " AND ($fields) ";
        }
    }
    function filters()
    {
        if (!empty($this->params['filter_organization'])  && $this->params['filter_organization'] > 0) {
            $organization = $this->params['filter_organization'];
            $filter_organization[] = " concat('/',u.open_path,'/') LIKE :organizationparam_{$organization}";
            $this->params["organizationparam_{$organization}"] = '%/' . $organization . '/%';
            $this->sql .= " AND ( " . implode(' OR ', $filter_organization) . " ) ";
        }

        if ($this->params['filter_departments'] > 0) {
            $department = $this->params['filter_departments'];
            $filter_department[] = " concat('/',u.open_path,'/') LIKE :departmentparam_{$department}";
            $this->params["departmentparam_{$department}"] = '%/' . $department . '/%';
            $this->sql .= " AND ( " . implode(' OR ', $filter_department) . " ) ";
        }

        if ($this->params['filter_subdepartments'] > 0) {
            $subdepartments = $this->params['filter_subdepartments'];
            $filter_subdepartments[] = " concat('/',u.open_path,'/') LIKE :subdepartmentsparam_{$subdepartments}";
            $this->params["subdepartmentsparam_{$subdepartments}"] = '%/' . $subdepartments . '/%';
            $this->sql .= " AND ( " . implode(' OR ', $filter_subdepartments) . " ) ";
        }

        if (!empty($this->params['filter_users'])  && $this->params['filter_users'] > 0) {
            $userid = $this->params['filter_users'];
            $this->sql .= " AND u.id = :userid ";
            $this->params['userid'] = $userid;
        }
  
    }
    public function get_rows($users)
    {
        return $users;
    }
}
