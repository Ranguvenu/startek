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
 * @subpackage local_custom_matrix
 */

namespace local_custom_matrix\local;
require_once($CFG->dirroot . '/local/custom_matrix/lib.php');

use dml_exception;
use stdClass;

class local_custom_matrix_store {

    const COMPONENT = 'local_custom_matrix';
    const TABLE_PERFORMANCE_MATRIX = 'local_performance_matrix';
    const TABLE_PERFORMANCE_LOGS = 'local_performance_logs';
    const TABLE_PERFORMANCE_OVERALL = 'local_performance_overall';
    
    public function save_matrix_data($formdata): object {
        global $DB;

        //print_object($formdata);exit;

        $transaction = $DB->start_delegated_transaction();

        foreach ($formdata->type_parameter_rowid as $typerowkey=>$typerowarray) {

            $row=array();

            foreach ($formdata->colid as $typecolkey=>$typecolvalue) {

                $cellname="cell{$typerowkey}_$typecolkey";

                if(isset($formdata->$cellname) ){

                    if($typecolkey ==3){

                        if($formdata->$cellname > -1 && $formdata->$cellname < 101){

                            $row[$typerowkey][$formdata->column_header[$typecolkey]]=$formdata->$cellname;

                        }
                        else{

                            unset($row[$typerowkey]);

                        }
                    }else{

                        if(!empty($formdata->$cellname)){


                            $row[$typerowkey][$formdata->column_header[$typecolkey]]=$formdata->$cellname;

                        }else{

                            unset($row[$typerowkey]);

                        }
                    }
                }

            }

            if(isset($row[$typerowkey])){

                $matrix=(object)$row[$typerowkey];

                $matrix->open_costcenterid=$formdata->open_costcenterid;
                $matrix->role=$formdata->role;

                local_costcenter_get_costcenter_path($matrix);

                $matrix->path = $matrix->open_path;

                $returnmatrix=$this->insert_matrix($matrix);

                unset($row[$typerowkey]);

                foreach ($typerowarray as $typeparamkey=>$typeparamvalue) {

                    $row=array();

                    foreach ($formdata->colid as $typeparamcolkey=>$typeparamcolvalue) {

                        $cellname="cell{$typeparamkey}_$typeparamcolkey";

                        if(isset($formdata->$cellname) ){

                            if($typeparamcolkey ==2){

                                if($formdata->$cellname > -1 && $formdata->$cellname < 101){

                                    $row[$typeparamkey][$formdata->column_header[$typeparamcolkey]]=$formdata->$cellname;

                                }else{

                                    unset($row[$typeparamkey]);

                                }
                            }else{

                                if(!empty($formdata->$cellname)){

                                    $row[$typeparamkey][$formdata->column_header[$typeparamcolkey]]=$formdata->$cellname;

                                }else{

                                    unset($row[$typeparamkey]);

                                }
                            }
                        }
                    }
                    if(isset($row[$typeparamkey])){

                        $submatrix=(object)$row[$typeparamkey];

                        $submatrix->open_costcenterid=$formdata->open_costcenterid;
                        $submatrix->parentid=$returnmatrix->id;
                        $submatrix->role=$formdata->role;

                        local_costcenter_get_costcenter_path($submatrix);

                        $submatrix->path = $submatrix->open_path;

                        $returnsubmatrix=$this->insert_matrix($submatrix);

                        unset($row[$typeparamkey]);
                    }
                }
            }

        }

        $transaction->allow_commit();
        return (object) [];
    }

    /**
     *
     * @param object $matrix
     * @return object
     * @throws dml_exception
     */
    public function insert_matrix(object $matrix): object {
        global $DB,$USER;
        $month = date('m', time());
        $year = date("Y", time());

        $data = clone $matrix;
        $data->timecreated = time();
        $data->usercreated = $USER->id;
        $data->month = $month;
        $data->year = $year;

        $newid = $DB->insert_record(self::TABLE_PERFORMANCE_MATRIX, $data);
        $data->id = $newid;
        $matrix->id = $newid;
        return $matrix;
    }

    /**
     *
     * @param object $matrix
     * @return object
     * @throws dml_exception
     */
    public function upadte_matrix(object $matrix): object {
        global $DB,$USER;

        $month = date('m', time());
        $year = date("Y", time());

        $data = clone $matrix;
        $data->timemodified = time();
        $data->usermodified = $USER->id;
        $data->month = $month;
        $data->year = $year;
        $newid = $DB->update_record(self::TABLE_PERFORMANCE_MATRIX, $data);
        $data->id = $newid;
        $matrix->id = $newid;
        return $matrix;
    }

    /**
     *
     * @param object $matrix
     * @return object
     * @throws dml_exception
     */
    public function insert_update_log_matrix(object $matrix){
        global $DB,$USER; 
        $periodtype = get_config('local_custom_matrix','performance_period_type');
        $current_month = date('M',time());
        $period = get_current_period($periodtype,$current_month);
        $querylib = new \local_custom_matrix\querylib();       

        $data = new stdClass();
        $data->userid = $matrix->userid;
        $data->performancetype = $matrix->performancetype;
        $data->performancecatid = $matrix->performancecatid;        
        $data->maxpoints = ($matrix->maxpoints== '')?0:$matrix->maxpoints;
        $data->pointsachieved = ($matrix->totalpoints== '')?0:$matrix->totalpoints;
        $data->weightage = ($matrix->weightage== '')?0:$matrix->weightage; 
             
        $data->parentid = $matrix->parentid;
        $data->type = $matrix->type;        
        $data->role = $matrix->role; 
        if($matrix->logid != 0){           
            $data->id = $matrix->logid;
            $data->timemodified = $matrix->timemodified;
            $data->usermodified = $matrix->usermodified;
            $newid = $DB->update_record(self::TABLE_PERFORMANCE_LOGS, $data);
        }else{
            $data->month = $matrix->month;
            $data->year = $matrix->year;
            $data->period = $matrix->period;
            $data->usercreated = $matrix->usercreated;
            $data->timecreated = $matrix->timecreated;
            $data->submitteduser = $matrix->submitteduser;  
            $newid = $DB->insert_record(self::TABLE_PERFORMANCE_LOGS, $data);
        }

        return $newid;
    }

    /**
     *
     * @param object $matrix
     * @return object
     * @throws dml_exception
     */

    public function insert_overall_matrix(object $matrix): object {
        global $DB,$USER;

        $month = date('F', time());
        $year = date("Y", time());
        $nextyear = date('Y', strtotime('+1 year'));
        $periodtype = get_config('local_custom_matrix','performance_period_type');
        $current_month = date('M',time());
        $period = get_current_period($periodtype,$current_month);

        $data = clone $matrix;
        $data->timecreated = time();
        $data->usercreated = $USER->id;
        $data->submitteduser = $USER->id;
        $data->month = $month;
        $data->year = $year;
        $data->financialyear = $year.'-'.$nextyear;
        $data->period = $period;       
        $logid = $this->insert_update_log_matrix($data);

        $newid = $DB->insert_record(self::TABLE_PERFORMANCE_OVERALL, $data);
        $data->id = $newid;
        $matrix->id = $newid;
        $matrix->id = $logid;

        return $matrix;
    }

    /**
     *
     * @param object $matrix
     * @return object
     * @throws dml_exception
     */
    public function upadte_overall_matrix(object $matrix): object {
        global $DB,$USER;

        $month = date('F', time());
        $year = date("Y", time());
        $nextyear = date('Y', strtotime('+1 year'));

        $data = clone $matrix;
        $data->timemodified = time();
        $data->usermodified = $USER->id;       
        $data->financialyear = $year.'-'.$nextyear;

        $logid = $this->insert_update_log_matrix($data);
               
        $newid = $DB->update_record(self::TABLE_PERFORMANCE_OVERALL, $data);
        $data->id = $newid;
        $matrix->id = $newid;
        return $matrix;
    }
    

}
