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

class report_onlinetestsoverview extends reportbase implements report
{
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties)
    {
        parent::__construct($report);
        $this->parent = true;
        $this->columns = ['onlinetestfield' => ['onlinetestfield'], 'onlinetestsoverviewcolumns' => ['onlinetestname', 'enrolmentscount', 'completionscount']];
        $this->components = array('columns', 'filters', 'permissions', 'plot', 'orderable');
        $this->filters = array('organization', 'departments', 'subdepartments', 'level4department', 'onlinetests');
        $this->orderable = array('onlinetestname', 'enrolmentscount', 'completionscount');
        $this->defaultcolumn = 'o.id';
        $this->userid = isset($report->userid) ? $report->userid : 0;
        $this->reportid = isset($report->reportid) ? $report->reportid : 0;
        $this->scheduleflag = isset($report->scheduling) ? true : false;
    }
    public function init()
    {
        parent::init();
    }
    public function count()
    {
        $this->sql = "SELECT COUNT(o.id)";
    }
    public function select()
    {
        global $USER;
        if (!is_siteadmin()) {
            list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/", $USER->open_path);
            $costcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql('u.open_path', $org);
        }
        $this->sql = "SELECT o.id as onlinetestid,o.name as onlinetestname,o.open_path as open_path,
                        ( SELECT COUNT(ou.id)
                            FROM {local_onlinetest_users} ou
                            -- JOIN {local_onlinetests} o ON ou.onlinetestid = o.id
                            JOIN {user} u ON ou.userid = u.id AND u.deleted = 0 AND u.suspended = 0
                            WHERE 1 = 1 {$costcenterpathconcatsql} AND  ou.onlinetestid = o.id ) as enrolmentscount,
                        (SELECT COUNT(ou.id)
                            FROM {local_onlinetest_users} ou
                            -- JOIN {local_onlinetests} o ON ou.onlinetestid = o.id
                            JOIN {user} u ON ou.userid = u.id AND u.deleted = 0 AND u.suspended = 0
                            WHERE ou.status = 1 {$costcenterpathconcatsql} AND  ou.onlinetestid = o.id
                                )as completionscount ";

        parent::select();
    }
    public function from()
    {
        $this->sql .= "  FROM {local_onlinetests} o ";
    }
    public function joins()
    {
        parent::joins();
    }
    public function where()
    {
        global $CFG;
        $this->sql .= " WHERE 1 = 1 ";
        $this->params['type'] = 1;
        $this->params['module'] = 'online_exams';

        $costcenterpathconcatsql = (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname = 'o.open_path');
        require_once $CFG->dirroot . "/blocks/learnerscript/lib.php";
        if (is_siteadmin()) {
            $this->sql .= "";
        } else if ($this->scheduleflag && $this->reportid != 0 && $this->userid != 0) {
            $usercostcenterpathconcatsql = scheduled_report($this->reportid, $this->scheduleflag, $this->userid, 'o.open_path', '');
            $this->sql .= $usercostcenterpathconcatsql;
        } else {
            $this->sql .= $costcenterpathconcatsql;
        }

        parent::where();
    }
    public function search()
    {
        if (isset($this->search) && $this->search) {
            $fields = array("o.name");
            $fields = implode(" LIKE '%" . $this->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $this->search . "%' ";
            $this->sql .= " AND ($fields) ";
        }
    }
    public function filters()
    {
        if ($this->params['filter_organization'] > 0) {
            $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_organization'], 'path');
            $this->sql .= " AND concat(o.open_path,'/') like :orgpath ";
            $this->params['orgpath'] = $orgpath . '/%';
        }
        if ($this->params['filter_departments'] > 0) {
            $l2dept = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_departments'], 'path');
            $this->sql .= " AND concat(o.open_path,'/') like :l2dept ";
            $this->params['l2dept'] = $l2dept . '/%';
        }
        if ($this->params['filter_subdepartments'] > 0) {
            $l3dept = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_subdepartments'], 'path');
            $this->sql .= " AND concat(o.open_path,'/') like :l3dept ";
            $this->params['l3dept'] = $l3dept . '/%';
        }
        if ($this->params['filter_level4department'] > 0) {
            $l4dept = \local_costcenter\lib\accesslib::get_costcenter_info($this->params['filter_level4department'], 'path');
            $this->sql .= " AND concat(o.open_path,'/') like :l4dept ";
            $this->params['l4dept'] = $l4dept . '/%';
        }

        if (!empty($this->params['filter_onlinetests'])) {
            $this->sql .= " AND o.id = :coursename";
            $this->params['coursename'] = $this->params['filter_onlinetests'];
        }

        if ($this->ls_startdate > 0 && $this->ls_enddate > 0) {
            $this->sql .= " AND ou.timemodified  > :report_startdate ";
            $this->params['report_startdate'] = $this->ls_startdate;

            $this->sql .= " AND ou.timemodified  < :report_enddate ";
            $this->params['report_enddate'] = $this->ls_enddate;
        }
    }
    public function get_rows($onlinetests)
    {
        return $onlinetests;
    }
}
