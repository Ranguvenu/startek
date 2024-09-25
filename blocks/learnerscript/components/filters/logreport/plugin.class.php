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

class plugin_logreport extends pluginbase {

    public function init() {
        $this->form = false;
        $this->unique = true;
        $this->singleselection = true;
        $this->placeholder = false;
        $this->maxlength = 0;
        $this->fullname = get_string('logreport', 'block_learnerscript');
        $this->reporttypes = array();
    }

    public function summary($data) {
        return get_string('filterlogreport_summary', 'block_learnerscript');
    }

    public function execute($finalelements, $data, $filters) {
        $fschool = isset($filters['filter_logreport']) ? $filters['filter_logreport'] : null;
        $filterschool = optional_param('filter_logreport', $fschool, PARAM_INT);
        if (!$filterschool) {
            return $finalelements;
        }

        if ($this->report->type != 'sql') {
            return array($filterschool);
        } else {
            if (preg_match("/%%FILTER_LOGREPORT:([^%]+)%%/i", $finalelements, $output)) {
                $replace = ' AND ' . $output[1] . ' = ' . $filterschool;
                return str_replace('%%FILTER_LOGREPORT:' . $output[1] . '%%', $replace, $finalelements);
            }
        }
        return $finalelements;
    }

    public function filter_data(){
        global $DB, $USER;

        $params = array();
        $sql = "SELECT lp.id, lp.name 
                FROM {local_learningplan} lp 
                WHERE 1 = 1 ";

        $systemcontext = context_system::instance();
        if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
            $sql .= "";
        }else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
            $sql .= " AND lp.costcenter = :costcenterid ";
            $params['costcenterid'] = $USER->open_costcenterid;
        }else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
            $sql .= " AND lp.costcenter = :costcenterid AND lp.department = :departmentid";
            $params['costcenterid'] = $USER->open_costcenterid;
            $params['departmentid'] = $USER->open_departmentid;
        }
        $sql .= " ORDER BY lp.name ASC";

        $logreport = $DB->get_records_sql_menu($sql, $params);

        $cloptions = array();
        $cloptions[0] = get_string('local_learningplan', 'block_learnerscript');

        $logreportlist = $cloptions + $logreport;

        return $logreportlist;
    }

    public function selected_filter($selected) {
        $filterdata = $this->filter_data();
        return $filterdata[$selected];
    }

    public function print_filter(&$mform, $selectoption = true) {
        $logreportlist = $this->filter_data();
        $array = array('data-select2'=>true,'data-maximum-selection-length' => $this->maxlength);
        $select = $mform->addElement('select', 'filter_logreport', null, $logreportlist,$array);

        $select->setHiddenLabel(true);
        $mform->setType('filter_logreport', PARAM_RAW);
    }

}
