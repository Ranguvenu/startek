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

class plugin_certificatecompletions extends pluginbase {

    public function init() {
        $this->fullname = get_string('certificatecompletions', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('certificatecompletions');
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
        global $DB, $CFG,$OUTPUT;

        switch ($data->column) {
            case 'startdate':
                $row->{$data->column} = ($row->{$data->column}) ? \local_costcenter\lib::get_userdate('d m Y H:i',$row->{$data->column}) : '--';
                break;
            case 'enddate':
                $row->{$data->column} = ($row->{$data->column}) ? \local_costcenter\lib::get_userdate('d m Y H:i',$row->{$data->column}) : '--';
                break;
            case 'courses':
                $sql = "SELECT c.id, c.fullname
                        FROM {course} c
                        JOIN {local_certification_courses} lcc ON lcc.courseid = c.id 
                        WHERE lcc.certificationid = :certificationid ";

                $certcourses = $DB->get_records_sql_menu($sql,array('certificationid'=>$row->certificationid));
                $certcourses = array_filter($certcourses);
                if($certcourses){
                    $row->{$data->column} = implode(', ', $certcourses);
                }else{
                    $row->{$data->column} = '--';
                }
                break;
            case 'certificationstatus':
                if($row->certificationstatus == 0){
                    $row->{$data->column} = get_string('new_certification','local_certification');
                }elseif($row->certificationstatus == 1){
                    $row->{$data->column} = get_string('active_certification','local_certification');
                }elseif($row->certificationstatus == 2){
                    $row->{$data->column} = get_string('hold_certification','local_certification');
                }elseif($row->certificationstatus == 3){
                    $row->{$data->column} = get_string('cancel_certification','local_certification');
                }else{
                    $row->{$data->column} = get_string('completed_certification','local_certification');
                }
                break;
                
            case 'trainers':
                $sql = "SELECT lct.trainerid, CONCAT(u.firstname,' ',u.lastname)
                        FROM {user} u 
                        JOIN {local_certification_trainers} lct ON u.id = lct.trainerid 
                        WHERE lct.certificationid = :certificationid  
                        AND u.deleted = :deleted AND u.suspended = :suspended ";
                $params = array('certificationid'=>$row->certificationid,'deleted'=>0,
                                'suspended'=>0);

                $trainers = $DB->get_records_sql_menu($sql, $params); 
                
                if($trainers){
                    $row->trainers = implode(', ', $trainers);
                }else{
                    $row->trainers = 'NA';
                }
                break;      
                case 'employee_completionstatus':
                    $row->{$data->column} = ($row->{$data->column} == 1) ? 'Completed' : 'Not Completed';
                break;
                case 'employee_completiondate':
                    $row->{$data->column} = !empty($row->{$data->column}) ? \local_costcenter\lib::get_userdate('d m Y H:i',$row->{$data->column}) : '--';
                break;
        }
        return (isset($row->{$data->column})) ? $row->{$data->column} : '--';
    }

}
