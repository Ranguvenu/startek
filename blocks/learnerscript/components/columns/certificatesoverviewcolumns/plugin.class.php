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

class plugin_certificatesoverviewcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('certificatesoverviewcolumns', 'block_learnerscript');
        $this->type = 'undefined';
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

    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB;
        switch ($data->column) {
            case 'startdate':
                $row->{$data->column} = ($row->{$data->column}) ? \local_costcenter\lib::get_userdate('d m Y H:i',$row->{$data->column}) : '--';
            break;
            case 'enddate':
                $row->{$data->column} = ($row->{$data->column}) ? \local_costcenter\lib::get_userdate('d m Y H:i',$row->{$data->column}) : '--';
            break;

            case 'certificatestatus':
                switch ($row->certificatestatus){
                    case 0:
                        $row->{$data->column} = get_string('new_certification','local_certification');
                        break;
                    case 1:
                        $row->{$data->column} = get_string('active_certification','local_certification');
                        break;
                    case 2:
                        $row->{$data->column} = get_string('hold_certification','local_certification');
                        break;
                    case 3:
                        $row->{$data->column} = get_string('cancel_certification','local_certification');
                        break;
                    case 4:
                        $row->{$data->column} = get_string('completed_certification','local_certification');
                        break;
                }
                break;
            case 'courses':
                $sql = "SELECT c.id,c.fullname 
                        FROM {local_certification_courses} as lcc
                        JOIN {course} as c ON c.id = lcc.courseid 
                        WHERE lcc.certificationid = :certificationid ";
                $courses = $DB->get_records_sql_menu($sql,array('certificationid' => $row->id));

                $row->{$data->column} = $courses ? implode(', ',$courses) : '--';
            break;

            case 'trainers':
                $sql = "SELECT u.id, CONCAT(u.firstname,' ',u.lastname) as trainer 
                        FROM {local_certification_trainers} as lct
                        JOIN {user} as u ON u.id = lct.trainerid 
                        WHERE lct.certificationid = :certificationid AND u.deleted = :deleted 
                        AND u.suspended = :suspended ";
                $trainers = $DB->get_records_sql_menu($sql,array('certificationid' => $row->id, 'deleted'=>0,'suspended'=>0));
                $row->{$data->column} = $trainers ? implode(', ',$trainers) : '--';
            break;
            // case 'enrollmentscount':
            //     $sql = "SELECT COUNT(DISTINCT(u.id))
            //             FROM {local_certification_users} as lcu
            //             JOIN {user} as u ON u.id = lcu.userid 
            //             WHERE lcu.certificationid = :certificationid AND u.deleted = :deleted 
            //             AND u.suspended = :suspended ";
            //     $enrollments = $DB->count_records_sql($sql,array('certificationid' => $row->id, 'deleted'=>0,'suspended'=>0));
            //     $row->{$data->column} = $enrollments;
            // break;
            // case 'completionscount':
            //     $sql = "SELECT COUNT(DISTINCT(u.id))
            //             FROM {local_certification_users} as lcu
            //             JOIN {user} as u ON u.id = lcu.userid 
            //             WHERE lcu.certificationid = :certificationid AND u.deleted = :deleted 
            //             AND u.suspended = :suspended AND lcu.completion_status = :completionstatus ";
            //     $completions = $DB->count_records_sql($sql,array('certificationid' => $row->id,
            //         'deleted'=>0,'suspended'=>0,'completionstatus'=>1));
            //     $row->{$data->column} = $completions;
            // break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }
}