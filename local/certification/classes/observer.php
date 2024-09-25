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
 * @package Bizlms 
 * @subpackage local_certification
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_certification.
 */
class local_certification_observer {

    /**
     * Triggered via response_deleted event.
     *
     * @param \local_evaluation\event\response_deleted $event
     */
    public static function response_deleted(\local_evaluation\event\response_deleted $event) {
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot.'/local/certification/lib.php');
        //print_object($event);exit;
        $sql = "SELECT id,evaluationtype,instance,plugin  FROM {local_evaluations} WHERE id =:id ";
                    $params = array();
                    $params['id'] =  $event->objectid;
         
        $feedback = $DB->get_record_sql($sql, $params);
        if($feedback&&$feedback->plugin=="certification"&&$feedback->instance>0){
            $pluginevaluationtypes = certification_evaluationtypes();
            switch($pluginevaluationtypes[$feedback->evaluationtype]) {
                case 'Trainer feedback':
                    $certification_trainers_sql = "SELECT id,trainerid
                        FROM {local_certification_trainers}
                        WHERE certificationid = :certificationid AND feedback_id = :feedback_id";
                    $local_certification_trainers=$DB->get_record_sql($certification_trainers_sql, array('certificationid' => $feedback->instance, 'feedback_id' => $feedback->id));
                    
                    $return = $DB->delete_records('local_certificatn_trainerfb', array('clrm_trainer_id'=>$local_certification_trainers->id,'userid'=>$event->relateduserid));
                break;
            
                case 'Training feedback':
                       $params = array('certificationid' => $feedback->instance,'userid'=>$event->relateduserid,
                                 'timemodified' => time(),'usermodified' => $USER->id,'trainingfeedback'=>0);
 
                       $sql='UPDATE {local_certification_users} SET
                           trainingfeedback = :trainingfeedback, timemodified = :timemodified,
                           usermodified = :usermodified WHERE certificationid = :certificationid AND userid=:userid';
                       $return = $DB->execute($sql, $params);
                break;
            }
        }
    
    }
}
