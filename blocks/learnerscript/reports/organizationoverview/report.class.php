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

class report_organizationoverview extends reportbase implements report {
    /**
     * [__construct description]
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        global $USER;
        parent::__construct($report);
        $this->components = array('columns', 'filters', 'permissions', 'calcs', 'plot');
        $this->columns = array('organizationoverviewcolumns' => array(get_string('open_costcenterid','local_costcenter'), get_string('totaldepartments','local_costcenter'), 'totalcourses', 'totallp' ,'totalilts', 'totalprogram', 'totalactuser'));
        //$this->courselevel = true;
        $this->parent = true;
        $this->filters = ['organization'];
        $this->orderable = array(get_string('open_costcenterid','local_costcenter'));
        $this->defaultcolumn = 'c.id';
        $this->userid = isset($report->userid) ? $report->userid : 0;
        $this->reportid = isset($report->reportid) ? $report->reportid : 0;
        $this->scheduleflag = isset($report->scheduling) ? true : false;

    }
    function init() {
        parent::init();
    }

    function count() {
        $this->sql = " SELECT COUNT(c.id) ";
    }
    function select() {
        $this->sql = "SELECT c.id, c.fullname ";
        parent::select();
    }
    function from() {
        $this->sql .= " FROM {local_costcenter} c ";
    }
    function joins() {
        $this->sql .= " ";
        parent::joins();
    }
    function where() {
        global $CFG;
        $this->sql .= " WHERE c.depth = 1 AND c.visible = 1 ";
        $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='c.path'); 

        // getscheduled report
        require_once($CFG->dirroot . "/blocks/learnerscript/lib.php");
        if (is_siteadmin()) {
            $this->sql .= "";
        } else if ($this->scheduleflag && $this->reportid!=0 && $this->userid != 0 ) {             
            $usercostcenterpathconcatsql = scheduled_report( $this->reportid,$this->scheduleflag,$this->userid,'c.path','');
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
        if (!empty($this->params['filter_organization'])  && $this->params['filter_organization'] > 0) {
            $organization = $this->params['filter_organization'];
            $filter_organization[] = " c.id = $organization";
            // $this->params["or"] = '%/'.$organization.'/%';
            $this->sql .= " AND (".implode(' OR ', $filter_organization)." ) ";
        }

    }
    public function get_rows($costcenters) {
        return $costcenters;
    }
}
