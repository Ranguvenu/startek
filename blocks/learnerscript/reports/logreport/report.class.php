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
 
class report_logreport extends reportbase implements report {
    /**
     * @param object $report Report object
     * @param object $reportproperties Report properties object
     */
    public function __construct($report, $reportproperties) {
        parent::__construct($report);
        $this->parent = true;
        $columns = ['date', 'component', 'name', 'description', 'usercreated'];
        $this->components = array('columns', 'filters', 'permissions');
        $this->columns = ['logreportcolumns' => $columns];
        // $this->filters = array();
        $this->filters = array('learningpath');
        $this->defaultcolumn = 'lsl.id';
        $this->searchable = array('lsl.component', 'lsl.eventname', 'lsl.action', 'lsl.other');
    }


    function init() {
        parent::init();
    }

    function count() {
        $this->sql = "SELECT COUNT(lsl.id) ";
    }

    function select() {
        $this->sql = "SELECT lsl.* " ;

        parent::select();
    }

    function from() {
        $this->sql .= " FROM {logstore_standard_log} lsl ";
    }


    function joins() {
        if(!is_siteadmin()){
            // $this->sql .=" JOIN {local_learningplan} llp ON llp.id = lsl.objectid ";
            $this->sql .=" JOIN {user} u ON u.id = lsl.userid ";
        }
        parent::joins();
    }

    function where() {
        global $USER, $DB;

        $systemcontext = context_system::instance();
        // getscheduled report
        $this->sql .= " WHERE 1 = 1 AND lsl.component = 'local_learningplan'";

        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
            $this->sql .= "";
        }else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            // $this->sql .= " AND llp.costcenter = :costcenterid ";
            $this->sql .= " AND u.open_costcenterid = :costcenterid ";
            $this->params['costcenterid'] = $USER->open_costcenterid;
        }else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            // $this->sql .= " AND llp.costcenter = :costcenterid AND llp.department = :departmentid";
            $this->sql .= " AND u.open_costcenterid = :costcenterid AND u.open_departmentid = :departmentid";
            $this->params['costcenterid'] = $USER->open_costcenterid;
            $this->params['departmentid'] = $USER->open_departmentid;
        }

        parent::where();
    }

    function search() {
      global $DB;
        if (isset($this->search) && $this->search) {
            $statsql = array();
            foreach ($this->searchable as $key => $value) {
                $statsql[] =$DB->sql_like($value, "'%" . $this->search . "%'",$casesensitive = false,$accentsensitive = true, $notlike = false);
            }
            $fields = implode(" OR ", $statsql);          
            $this->sql .= " AND ($fields) ";
        }
    }

    function filters() {
        if ($this->ls_startdate >= 0 && $this->ls_enddate) {
            $this->params['ls_fstartdate'] = ROUND($this->ls_startdate);
            $this->params['ls_fenddate'] = ROUND($this->ls_enddate);
            $this->sql .= " AND lsl.timecreated BETWEEN :ls_fstartdate AND :ls_fenddate ";
        }
        
        if ($this->params['filter_learningpath'] > 0) {
            $this->sql .= " AND lsl.objectid = :objectid ";
            $this->params['objectid'] = $this->params['filter_learningpath'];
        }

        /*if ($this->params['filter_components'] == 1) {
            $this->sql .= " AND lsl.component = 'local_userapproval'";
        } else if ($this->params['filter_components'] == 2){
            $this->sql .= " AND lsl.component = 'local_trainingprogram'";
        } else if ($this->params['filter_components'] == 3){
            $this->sql .= " AND lsl.component = 'local_competencies'";
        } else if ($this->params['filter_components'] == 4){
            $this->sql .= " AND lsl.component = 'local_exams'";
        } else if ($this->params['filter_components'] == 5){
            $this->sql .= " AND lsl.component = 'local_sector'";
        } else if ($this->params['filter_components'] == 6){
            $this->sql .= " AND lsl.component = 'local_organization'";
        } else {

        }*/
    }

    /**
     * [get_rows description]
     * @param  array  $logreport [description]
     * @return [type] [description]
     **/
    public function get_rows($logreport) {
        return $logreport;
    }
}
