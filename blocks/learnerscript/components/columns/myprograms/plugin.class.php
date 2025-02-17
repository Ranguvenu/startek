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

class plugin_myprograms extends pluginbase {

    public function init() {
        $this->fullname = get_string('myprograms', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('myprograms');
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
         global $DB, $USER;
          switch ($data->column) {
            case 'levelscount':
                $levelscount = $DB->count_records('local_program_levels',array('programid'=>$row->programid));
                $row->{$data->column} = $levelscount;
                break;
            case 'completedlevelscount':
                $completedlevels = $DB->count_records('local_bc_level_completions',
                                array('programid'=>$row->programid,'userid'=>$USER->id,'completion_status'=>1));
                $row->{$data->column} = $completedlevels;
                break;
            case 'completion_status':
                $row->{$data->column} = ($row->{$data->column} == 1) ? 'Completed' : 'Not Completed';
                break;
            case 'completiondate':
                $row->{$data->column} = !empty($row->{$data->column}) ? date('d-m-Y',$row->{$data->column}) : 'NA';
                break;
        }
        return (isset($row->{$data->column})) ? $row->{$data->column} : '--';
    }

}

