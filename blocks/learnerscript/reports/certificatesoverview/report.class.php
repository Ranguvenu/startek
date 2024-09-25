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

class report_certificatesoverview extends reportbase implements report {
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = true;
        $this->columns = array('certificatesoverviewcolumns' => array('certificationname','organization','department',
         'trainers','certificatestatus', 'startdate', 'enddate', 'location',
         'enrollmentscount','completionscount'));
        $this->components = array('columns', 'filters', 'permissions', 'orderable','plot');
        $this->filters = array('organization','departments','certificates');
        $this->orderable = array('certificationname','enrollmentscount','completionscount');
        $this->defaultcolumn = 'lc.id';
    }
    function init() {
        parent::init();
    }
    function count() {
        $this->sql = "SELECT COUNT(lc.id)";
    }
    function select() {
        $this->sql = "SELECT lc.id, lc.name AS certificationname,
                                (SELECT fullname 
                                FROM {local_costcenter}
                                WHERE id = lc.costcenter) AS organization,
                                (CASE
                                    WHEN lc.department = -1 THEN 'All'
                                    ELSE (SELECT fullname 
                                        FROM {local_costcenter}
                                        WHERE id = lc.department)
                                END) AS department,
                                lc.status AS certificatestatus,
                                lc.startdate, lc.enddate,
                                (SELECT ll.fullname 
                                FROM {local_location_institutes} ll 
                                WHERE ll.id = lc.instituteid) AS location";
        parent::select();
    }
    function from() {
        $this->sql .= " FROM {local_certification} lc  ";
    }
    function joins() {
          parent::joins();
    }
    function where(){
        global $USER, $DB;
         $this->sql .= " WHERE 1 = 1 ";
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
        }else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext) && $ohs){
            $this->sql .= " AND lc.costcenter = :costcenterid ";
            $this->params['costcenterid']= $USER->open_costcenterid;

        }else{
            $this->sql .= " AND lc.costcenter =:costcenterid 
                        AND lc.department = :departmentid";
            $this->params['costcenterid']= $USER->open_costcenterid;
            $this->params['departmentid']= $USER->open_departmentid;
        }

         parent::where();
    }
    function search(){
        if (isset($this->search) && $this->search) {
            $fields = array('lc.name');
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
    }    
    public function get_rows($certificates = array()) {
        global $DB;

        if($certificates){
            $sql = " SELECT COUNT(DISTINCT(u.id))
                        FROM {local_certification_users} as lcu
                        JOIN {user} as u ON u.id = lcu.userid 
                        WHERE lcu.certificationid = :certificationid AND u.deleted = :deleted 
                        AND u.suspended = :suspended ";

            $completedsql .= " AND lcu.completion_status = :completionstatus ";

            foreach ($certificates as $certificate) {
                $enrollments = $DB->count_records_sql($sql,array('certificationid' => $certificate->id, 'deleted'=>0,'suspended'=>0));
                $certificate->enrollmentscount = $enrollments;

                $completions = $DB->count_records_sql($sql.$completedsql,array('certificationid' => $certificate->id,
                    'deleted'=>0,'suspended'=>0,'completionstatus'=>1));
                $certificate->completionscount = $completions;
            }
        }
        return $certificates;
    }
}
