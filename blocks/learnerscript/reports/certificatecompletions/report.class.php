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

class report_certificatecompletions extends reportbase implements report {
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = true;
        $this->columns = ['userfield'=>['userfield'],'certificatecompletions' =>['certificationname',
            'startdate','enddate','certificationstatus','trainers','courses','attendedsessions','totalsessions',
        'employee_completionstatus','employee_completiondate']];
        $this->components = array('columns', 'filters', 'permissions','orderable');
        $this->filters = array('organization','departments','certificates','completionstatus');
        $this->orderable = array('certificationname');
        $this->defaultcolumn = 'lc.id';
    }
    function init() {
        parent::init();
    }
    function count() {
        $this->sql = "SELECT COUNT(lcu.id)";
    }
    function select() {
        $this->sql = "SELECT lcu.id, lcu.userid, lc.id as certificationid, u.id AS userid, 
                        lc.name AS certificationname,lc.startdate, lc.enddate,
                        lc.status as certificationstatus,
                        lcu.completion_status AS employee_completionstatus,
                        lcu.completiondate AS employee_completiondate,                        
                        (SELECT COUNT(lcs.id) 
                        FROM {local_certification_sessions} lcs 
                        WHERE lcs.certificationid = lc.id) AS totalsessions,
                        lcu.attended_sessions AS attendedsessions ";
        parent::select();
    }
    function from() {
        $this->sql .= " FROM {local_certification} lc  ";
    }
    function joins() {
        $this->sql .= "JOIN {local_certification_users} lcu ON lc.id =lcu.certificationid
                        JOIN {user} u ON u.id = lcu.userid"; 
          parent::joins();
    }
    function where(){
         global $USER, $DB;
         $this->sql .= " WHERE u.deleted = 0 AND u.suspended = 0 ";
         $systemcontext = \context_system::instance();
         // getscheduled report
        if (!is_siteadmin()) {
            $scheduledreport = $DB->get_record_sql('select id,roleid from {block_ls_schedule} where reportid =:reportid AND sendinguserid IN (:sendinguserid)', ['reportid'=>$this->reportid,'sendinguserid'=>$USER->id], IGNORE_MULTIPLE);
            if (!empty($scheduledreport)) {
            $compare_scale_clause = $DB->sql_compare_text('capability')  . ' = ' . $DB->sql_compare_text(':capability');
            $ohs = $DB->record_exists_sql("select id from {role_capabilities} where roleid =:roleid AND $compare_scale_clause", ['roleid'=>$scheduledreport->roleid, 'capability'=>'local/costcenter:manage_ownorganization']);
            // $dhs = $DB->record_exists_sql("select id from {role_capabilities} where roleid =:roleid AND $compare_scale_clause", ['roleid'=>$scheduledreport->roleid, 'capability'=>'local/costcenter:manage_owndepartments']);
            } else {
                $ohs = 1;
            }
        }
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
            $this->sql .= " ";
        }else if(!is_siteadmin() || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || $ohs){
            $this->sql .= " AND lc.costcenter = :costcenterid ";
            $this->params['costcenterid']= $USER->open_costcenterid;

        }else{
            $this->sql .= " AND lc.costcenter =:costcenterid 
                        AND lc.department = :departmentid";
            $this->params['costcenterid']= $USER->open_costcenterid;
            $this->params['departmentid']= $USER->open_departmentid;
        }

         parent::where();
         //echo $this->sql;
         //print_object($this->params);

    }
    function search(){
        if (isset($this->search) && $this->search) {
            $fields = array("CONCAT(u.firstname,' ', u.lastname)", 'u.email', 'u.open_employeeid','lc.name');
            $fields = implode(" LIKE '%$this->search%' OR ", $fields);
            $fields .= " LIKE '%$this->search%' ";
            $this->sql .= " AND ($fields) ";
        }
    }
    function filters() {    
        if (!empty($this->params['filter_organization']) && $this->params['filter_organization'] > 0) {
            $this->sql .= " AND lc.costcenter = :orgid ";;
            $this->params['orgid']= $this->params['filter_organization'];
        }
        if (!empty($this->params['filter_departments']) && $this->params['filter_departments'] > 0) {
           $this->sql .= " AND lc.department = :deptid ";
           $this->params['deptid']= $this->params['filter_departments'];
        }
        if (!empty($this->params['filter_certificates']) && $this->params['filter_certificates'] > 0) {
            $this->sql .= " AND lc.id = ".$this->params['filter_certificates']." ";
        }
       /* if (isset($this->params['filter_completionstatus']) && !empty($this->params['filter_completionstatus'])) {
           $this->sql .= " AND lcu.completion_status = :status ";
           $this->params['status']= $this->params['filter_completionstatus'];
        }*/
        if ((isset($this->params['filter_completionstatus'])) && ($this->params['filter_completionstatus'] != -1)) {
            $cid = $this->params['filter_completionstatus'];
            if($cid == 1){
                $this->sql .= " AND lcu.completion_status = $cid";
            }else{
                $this->sql .= " AND lcu.completion_status != 1";
            }
        }
    }    
    public function get_rows($certusers) {
        return $certusers;
    }
}
