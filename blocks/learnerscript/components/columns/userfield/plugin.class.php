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
require_once($CFG->dirroot.'/local/users/lib.php');

class plugin_userfield extends pluginbase {

    public function init() {
        $this->fullname = get_string('userfield', 'block_learnerscript');
        $this->type = 'advanced';
        $this->form = true;
        $this->reporttypes = array();
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    // Data -> Plugin configuration data.
    // Row -> Complet user row c->id, c->fullname, etc...
    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB, $CFG, $OUTPUT;
        $row->id = isset($row->userid) ? $row->userid : 2;

        //$userrecord = $DB->get_record('user',array('id'=>$row->id));
        list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/",$row->open_path);
        switch ($data->column) {
            case 'employeeid':
                $row->{$data->column} = $row->open_employeeid;
            break;
            case 'reportingmanager':
                if($row->open_supervisorid > 0){
                    $fields = 'id,firstname,lastname,open_employeeid';
                    $reportingto = $DB->get_record('user', array('id'=>$row->open_supervisorid),$fields);
                    $row->{$data->column} = $reportingto->firstname.' '.$reportingto->lastname.' ('.$reportingto->open_employeeid.')';
                }else{
                    $row->{$data->column} = 'NA';
                }
                
                break;
            case 'userstatus':
                $row->{$data->column} = ($row->suspended == 0) ?
                                            '<span class="label label-success">' .  get_string('active') . '</span>' :
                                            '<span class="label label-warning">' . get_string('inactive') . '</span>';
                break;
            case 'designation':
                $row->{$data->column} = ($row->open_designation) ? $row->open_designation : 'NA';
                break;
            case 'level':
                $row->{$data->column} = ($row->open_level) ? $row->open_level : '--';
                break;
            case 'state':
                $row->{$data->column} = ($row->open_state) ? $row->open_state : '--';
                break;
            case 'branch':
                $row->{$data->column} = ($row->open_branch) ? $row->open_branch : '--';
                break;
            case 'organization':
                $u_org = $DB->get_field('local_costcenter', 'fullname', array('id'=>$org));
                $row->{$data->column} = $u_org;
                break;
            case 'client':
                if(!empty($ctr)){
                    $row->{$data->column} = $DB->get_field('local_costcenter', 'fullname', array('id'=>$ctr));
                }else{
                    $row->{$data->column} = 'All';
                }
                break;
            case 'lob':
                if(!empty($bu)){
                    $row->{$data->column} = $DB->get_field('local_costcenter', 'fullname', array('id'=>$bu));
                }else{
                    $row->{$data->column} = 'All';
                }
                break;      
            case 'region':
                if(!empty($cu)){
                    $row->{$data->column} = $DB->get_field('local_costcenter', 'fullname', array('id'=>$cu));
                }else{
                    $row->{$data->column} = 'All';
                }
                break;
            case 'location':
                    $row->{$data->column} = ($row->city) ? $row->city : '--';
                break;
            case 'department':
                    $row->{$data->column} = ($row->open_hrmsrole) ? $row->open_hrmsrole : '--';
                break;
            case 'contactnumber':
                    $row->{$data->column} = ($row->phone1) ? $row->phone1 : '--';
                break;
            default:
                $row->{$data->column} = isset($row->{$data->column}) ? $row->{$data->column} : $row->{$data->column};
            break;
        }
        if (strpos($data->column, 'profile_') === 0) {
            $usercustomcatgid = get_usercustomfield_category($row);
            if(isset( $usercustomcatgid) && !empty($usercustomcatgid)){
                $pfrofilefield = str_replace('profile_', '', $data->column);
                $sql = "SELECT d.data, f.shortname, f.datatype
                        FROM {user_info_data} d ,{user_info_field} f
                        WHERE f.id = d.fieldid AND d.userid = ? AND f.categoryid = {$usercustomcatgid} AND f.shortname = '{$pfrofilefield}'";
                if ($profiledata = $DB->get_records_sql($sql, array($row->id))) {
                
                    foreach ($profiledata as $p) {
                        if ($p->datatype == 'checkbox') {
                            $p->data = ($p->data) ? get_string('yes') : get_string('no');
                        }
                        if ($p->datatype == 'datetime') {
                            $p->data = userdate($p->data);
                        }
                        $row->{$data->column} =  $p->data ;
                    }
                }
            }
        }
        return (isset($row->{$data->column}) ) ? $row->{$data->column} : 'NA';
    }
}
