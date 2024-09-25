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
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
global $CFG, $DB, $PAGE, $USER, $OUTPUT;
require_once($CFG->dirroot.'/local/certification/lib.php');
require_login();
use local_certification\certification;
$certificationid = required_param('ctid', PARAM_INT);
$sessionid = optional_param('sid', 0, PARAM_INT);
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$url = new moodle_url($CFG->wwwroot . '/local/certification/attendance.php', array('ctid' => $certificationid));
if($sessionid > 0) {
    $url->param('sid', $sessionid);
}
// $DB->set_debug(true);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

$certification = $DB->get_record('local_certification', array('id' => $certificationid));
if (empty($certification)) {
    print_error('certification not found!');
}
if ((has_capability('local/certification:managecertification', context_system::instance())) && (!is_siteadmin()
    && (!has_capability('local/certification:manage_multiorganizations', context_system::instance())
        && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
        if($certification->costcenter!=$USER->open_costcenterid){
         print_error("You donot have permissions");
        }

        if ((has_capability('local/certification:manage_owndepartments', context_system::instance())
         || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
            if($certification->department!=$USER->open_departmentid){
                print_error("You donot have permissions");
            }
        }
}

$certification_name = $certification->name;
$PAGE->navbar->add(get_string("pluginname", 'local_certification'), new moodle_url('view.php',array('ctid'=>$certificationid)));
$PAGE->navbar->add($certification_name,new moodle_url('view.php',array('ctid'=>$certificationid)));
$PAGE->navbar->add(get_string("sessions",'local_classroom'),new moodle_url('view.php',array('ctid'=>$certificationid)));
$PAGE->navbar->add(get_string("attendance", 'local_certification'));
$PAGE->set_title($certification_name);
$PAGE->set_heading(get_string('session_attendance_heading', 'local_certification',$certification_name));
$renderer = $PAGE->get_renderer('local_certification');
$certification = new certification();
$attendancedata = data_submitted();
if (!empty($attendancedata)) {
    if ($attendancedata->reset == 'Reset Selected') {
        $DB->execute("UPDATE {local_certificatn_attendance} SET status = 0
            WHERE certificationid = :certificationid AND sessionid = :sessionid",
            array('certificationid' => $attendancedata->ctid,
                'sessionid' => $attendancedata->sid));
        redirect($PAGE->url);
    } else if ($attendancedata->action == 'attendance') {
        foreach ($attendancedata->attendeedata as $k => $attendancesignup) {
            $decodeddata = json_decode(base64_decode($attendancesignup));
            if ($decodeddata->attendanceid > 0) {
                $checkattendeestatus = new stdClass();
                $checkattendeestatus->id = $decodeddata->attendanceid;
                if (isset($attendancedata->status[$attendancesignup]) && $attendancedata->status[$attendancesignup]) {
                    $checkattendeestatus->status = SESSION_PRESENT;
                } else {
                    $checkattendeestatus->status = SESSION_ABSENT;
                }
                $checkattendeestatus->timemodified = time();
                $checkattendeestatus->usermodified = $USER->id;
                $DB->update_record('local_certificatn_attendance',  $checkattendeestatus);
                    $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $checkattendeestatus->id
                );
            
                $event = \local_certification\event\certification_attendance_created_updated::create($params);
                $event->add_record_snapshot('local_certification', $decodeddata->certificationid);
                $event->trigger();
            } else {
                $userattendance = new stdClass();
                $userattendance->certificationid = $decodeddata->certificationid;
                $userattendance->sessionid = $decodeddata->sessionid;
                $userattendance->userid = $decodeddata->userid;
                if (isset($attendancedata->status[$attendancesignup]) && $attendancedata->status[$attendancesignup]) {
                    $userattendance->status = SESSION_PRESENT;
                } else {
                    $userattendance->status = SESSION_ABSENT;
                }
                $userattendance->usercreated = $USER->id;
                $userattendance->timecreated = time();
                $record_exist=$DB->record_exists_sql("SELECT id FROM {local_certificatn_attendance}
                                                    WHERE certificationid={$decodeddata->certificationid}
                                                    and userid={$decodeddata->userid} and sessionid={$decodeddata->sessionid}");
                if(!$record_exist){
                    $id=$DB->insert_record('local_certificatn_attendance',  $userattendance);
                }
                
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $id
                );
            
                $event = \local_certification\event\certification_attendance_created_updated::create($params);
                $event->add_record_snapshot('local_certification', $decodeddata->certificationid);
                $event->trigger();
            }
            $attendedsessions = $DB->count_records('local_certificatn_attendance',
                array('certificationid' => $decodeddata->certificationid,
                    'userid' => $decodeddata->userid, 'status' => SESSION_PRESENT));

            $attendedsessions_hours=$DB->get_field_sql("SELECT ((sum(lcs.duration))/60) AS hours
                                        FROM {local_certification_sessions} as lcs
                                        WHERE  lcs.certificationid = {$certificationid}
                                        and lcs.id in (SELECT sessionid  FROM {local_certificatn_attendance} where certificationid={$certificationid} and userid = {$decodeddata->userid} and status=1)");
            
            if(empty($attendedsessions_hours)){
                $attendedsessions_hours=0;
            }

            $DB->execute('UPDATE {local_certification_users} SET attended_sessions = ' .
                $attendedsessions . ' , hours = ' .
                $attendedsessions_hours . ' , timemodified = ' . time() . ',
                usermodified = ' . $USER->id . ' WHERE certificationid = ' .
                $decodeddata->certificationid . ' AND userid = ' . $decodeddata->userid);
        }
        if($sessionid > 0) {
            // $DB->execute("UPDATE {local_certification_sessions} SET attendance_status = 1 WHERE id = :id ", array('id' => $sessionid));
            $DB->set_field('local_certification_sessions', 'attendance_status',  1, array('id' => $sessionid));
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' =>  $sessionid
                );
            
                $event = \local_certification\event\certification_sessions_updated::create($params);
                $event->add_record_snapshot('local_certification',$certificationid);
                $event->trigger();
        } else {
            $DB->execute("UPDATE {local_certification_sessions} SET attendance_status = 1,
            timemodified = :timemodified, usermodified = :usermodified WHERE
            certificationid = :certificationid ", array('certificationid' => $certificationid,
                'usermodified' => $USER->id, 'timemodified' => time()));
        }

        redirect($PAGE->url);
    }
}

echo $OUTPUT->header();
if ($sessionid > 0) {
    $sessionattendance = new \local_certification\output\session_attendance($sessionid);
   
    echo $renderer->render($sessionattendance);
}
echo $renderer->viewcertificationattendance($certificationid, $sessionid);

$continue='<div class="col-md-1 pull-right">';
$continue.='<a href='.$CFG->wwwroot.'/local/certification/view.php?ctid='.$certificationid.' class="singlebutton"><button>'.get_string('continue', 'local_certification').'</button></a>';
$continue.='</div>';
echo $continue;

echo $OUTPUT->footer();