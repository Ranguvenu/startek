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
use block_learnerscript\local\pluginbase;

class plugin_departments extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true;
        $this->singleselection = true;
        $this->placeholder = true;
        $this->maxlength = 0; 
        $this->filtertype = 'custom'; 
        if (!empty($this->reportclass->basicparams)) {
            foreach ($this->reportclass->basicparams as $basicparam) {
                if ($basicparam['name'] == 'departments') {
                    $this->filtertype = 'basic';
                }
            }
        }
        $this->fullname = get_string('pluginname', 'local_costcenter');
        $this->reporttypes = array();
    }

    public function summary($data) {
        return get_string('filterdepartments_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {
        $fschool = isset($filters['filter_departments']) ? $filters['filter_departments'] : null;
        $filterschool = optional_param('filter_departments', $fschool, PARAM_INT);
        if (!$filterschool) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterschool);
        } else {
            if (preg_match("/%%FILTER_DEPARTMENTS:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterschool;
                return str_replace('%%FILTER_DEPARTMENTS:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }

    public function filter_data($selectoption = true, $request = []){
        global $DB, $USER;
        $filter_departments = '';
        $filterdepartments = optional_param('filter_departments', 0, PARAM_INT);
        if (empty($this->reportclass->basicparams)) {
            $departmentoptions = array(get_string('pluginname', 'local_costcenter'));
        } 
        $filterdepartment = $this->reportclass->filters;
        // $filteruserid = $filtercourse['filter_users'];
        if($this->reportclass->basicparams){
            $basicparams = array_column($this->reportclass->basicparams, 'name');
            if ($basicparams[0] == 'organization') {
                $orgoptions = $DB->get_records_sql_menu("SELECT id FROM {local_costcenter} WHERE depth = 1 ORDER BY id ASC"); 
                $orgids = array_keys($orgoptions);
                if (empty($request['filter_organization'])) {
                    $deptorgid = array_shift($orgids);
                } else {
                    $deptorgid = $request['filter_organization'];
                }
            }else {
                $deptorgid = null;
            }
        } else {
            $deptorgid = null;
        } 
        $concatsql = " "; 
        $systemcontext = context_system::instance();
        if (!is_siteadmin()) {
            $scheduledreport = $DB->get_record_sql('select id,roleid from {block_ls_schedule} where reportid =:reportid AND sendinguserid IN (:sendinguserid)', ['reportid'=>$this->reportclass->config->id,'sendinguserid'=>$USER->id], IGNORE_MULTIPLE);
            if (!empty($scheduledreport)) {
            $compare_scale_clause = $DB->sql_compare_text('capability')  . ' = ' . $DB->sql_compare_text(':capability');
            $ohs = $DB->record_exists_sql("select id from {role_capabilities} where roleid =:roleid AND $compare_scale_clause", ['roleid'=>$scheduledreport->roleid, 'capability'=>'local/costcenter:manage_ownorganization']);
            $dhs = $DB->record_exists_sql("select id from {role_capabilities} where roleid =:roleid AND $compare_scale_clause", ['roleid'=>$scheduledreport->roleid, 'capability'=>'local/costcenter:manage_owndepartments']);
            } else {
                $ohs = $dhs = 1;
            }
        }

        $params = array();
        $sql = "SELECT id, fullname 
                FROM {local_costcenter} 
                WHERE depth = :depth ";
        $params['depth'] = 2;

        $systemcontext = context_system::instance(); 
        if(is_siteadmin()){
            if (!empty($deptorgid)) {
                $concatsql .= " AND parentid = $deptorgid";
            }
        } else if(!is_siteadmin()){
            // $sql .= " AND parentid = :costcenterid ";
            // $params['costcenterid'] = $USER->open_costcenterid;
            $sql .= (new \local_courses\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='path');
        } 
        $sql .= $concatsql;
        $sql .= " ORDER BY id ASC ";

        $deptoptions = $DB->get_records_sql_menu($sql, $params);
       
        //$deptoptions[] = 'All';
        $selectdept = array(); 
        if(empty($this->reportclass->basicparams)){
            $selectdept[0] = get_string('pluginname', 'local_costcenter');
        }else{
            $deptoptions[0] = 'All';
        }
                
        $deptoptions = $selectdept + $deptoptions;
        ksort($deptoptions);
        
        return $deptoptions;
    }
    public function enabledepth(){
        return 3;
    }
    public function selected_filter($selected, $request = array()) {
        $filterdata = $this->filter_data(false, $request);
        return $filterdata[$selected];
    }

    public function print_filter(&$mform, $selectoption = true) {
        global $USER;
        $depth = $USER->useraccess['currentroleinfo']['depth'];
        if(isset($USER->useraccess['currentroleinfo']['contextinfo']) && count($USER->useraccess['currentroleinfo']['contextinfo']) > 1){
            $depth--;
        }
        if(is_siteadmin() || $depth < 3){
            $request = array_merge($_POST, $_GET);
            $deptoptions = $this->filter_data(false, $request); 
            if ((!$this->placeholder || $this->filtertype == 'basic') && COUNT($deptoptions) > 1) { 
                unset($deptoptions[-2]);
            }
            $array = array('data-select2'=>true,'data-maximum-selection-length' => $this->maxlength);               
            $select = $mform->addElement('select', 'filter_departments', null, $deptoptions, $array);
            if (!$this->singleselection) {
                $select->setMultiple(true);
            }

            $select->setHiddenLabel(true);
            $mform->setType('filter_departments', PARAM_INT);
        }
    }

}
