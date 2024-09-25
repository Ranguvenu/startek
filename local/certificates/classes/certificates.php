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
 * @subpackage local_certificates
 */
namespace local_certificates;
defined('MOODLE_INTERNAL') || die();
use context_system;
use stdClass;
use moodle_url;
use completion_completion;
use html_table;
use html_writer;
// use \local_certification\notifications_emails as certificatenotifications_emails;
require_once($CFG->dirroot . '/local/certificates/lib.php');
// require_once($CFG->dirroot.'/local/certification/notifications_emails.php');
// Certification
define('CERTIFICATION_NEW', 0);
define('CERTIFICATION_ACTIVE', 1);
define('CERTIFICATION_HOLD', 2);
define('CERTIFICATION_CANCEL', 3);
define('CERTIFICATION_COMPLETED', 4);
// Session Attendance
define('SESSION_PRESENT', 1);
define('SESSION_ABSENT', 2);
// Types
define('CERTIFICATION', 1);
define('LEARNINGPLAN', 2);
define('CERTIFICATE', 3);

class certificates {
    protected $certificationid;
    protected $certification;
    protected $clasroomcourses = array();
    protected $certificationcourse;
    protected $clasroomusers = array();
    protected $certificationuser;
    protected $clasroomsessions = array();
    protected $certificationsession;
    protected $clasroomtrainers = array();
    protected $certificationtrainer;
    protected $clasroomevaluations = array();
    protected $clasroomevaluation;
    protected $clasroomattendance = array();
     /**
     * @var string the print protection variable
     */
    const PROTECTION_PRINT = 'print';

    /**
     * @var string the modify protection variable
     */
    const PROTECTION_MODIFY = 'modify';

    /**
     * @var string the copy protection variable
     */
    const PROTECTION_COPY = 'copy';

    /**
     * @var int the number of issues that will be displayed on each page in the report
     *      If you want to display all certifications on a page set this to 0.
     */
    const CUSTOMCERT_PER_PAGE = '50';

    /**
     * Handles setting the protection field for the certification
     *
     * @param \stdClass $data
     * @return string the value to insert into the protection field
     */
    public static function set_protection($data) {
        $protection = array();

        if (!empty($data->protection_print)) {
            $protection[] = self::PROTECTION_PRINT;
        }
        if (!empty($data->protection_modify)) {
            $protection[] = self::PROTECTION_MODIFY;
        }
        if (!empty($data->protection_copy)) {
            $protection[] = self::PROTECTION_COPY;
        }

        // Return the protection string.
        return implode(', ', $protection);
    }

    /**
     * Handles uploading an image for the certification module.
     *
     * @param int $draftitemid the draft area containing the files
     * @param int $contextid the context we are storing this image in
     * @param string $filearea indentifies the file area.
     */
    public static function upload_files($draftitemid, $contextid, $filearea = 'image') {
        global $CFG;

        // Save the file if it exists that is currently in the draft area.
        require_once($CFG->dirroot . '/lib/filelib.php');
         file_save_draft_area_files($draftitemid, $contextid, 'local_certificates', $filearea ,$draftitemid);
    }

    /**
     * Return the list of possible fonts to use.
     */
    public static function get_fonts() {
        global $CFG;

        // Array to store the available fonts.
        $options = array();

        // Location of fonts in Moodle.
        $fontdir = "$CFG->dirroot/lib/tcpdf/fonts";
        // Check that the directory exists.
        if (file_exists($fontdir)) {
            // Get directory contents.
            $fonts = new \DirectoryIterator($fontdir);
            // Loop through the font folder.
            foreach ($fonts as $font) {
                // If it is not a file, or either '.' or '..', or
                // the extension is not php, or we can not open file,
                // skip it.
                if (!$font->isFile() || $font->isDot() || ($font->getExtension() != 'php')) {
                    continue;
                }
                // Set the name of the font to null, the include next should then set this
                // value, if it is not set then the file does not include the necessary data.
                $name = null;
                // Some files include a display name, the include next should then set this
                // value if it is present, if not then $name is used to create the display name.
                $displayname = null;
                // Some of the TCPDF files include files that are not present, so we have to
                // suppress warnings, this is the TCPDF libraries fault, grrr.
                @include("$fontdir/$font");
                // If no $name variable in file, skip it.
                if (is_null($name)) {
                    continue;
                }
                // Remove the extension of the ".php" file that contains the font information.
                $filename = basename($font, ".php");
                // Check if there is no display name to use.
                if (is_null($displayname)) {
                    // Format the font name, so "FontName-Style" becomes "Font Name - Style".
                    $displayname = preg_replace("/([a-z])([A-Z])/", "$1 $2", $name);
                    $displayname = preg_replace("/([a-zA-Z])-([a-zA-Z])/", "$1 - $2", $displayname);
                }
                $options[$filename] = $displayname;
            }
            ksort($options);
        }

        return $options;
    }

    /**
     * Return the list of possible font sizes to use.
     */
    public static function get_font_sizes() {
        // Array to store the sizes.
        $sizes = array();

        for ($i = 1; $i <= 60; $i++) {
            $sizes[$i] = $i;
        }

        return $sizes;
    }

    /**
     * Returns a list of issued certifications.
     *
     * @param int $certificateid
     * @param bool $groupmode are we in group mode
     * @param \stdClass $cm the course module
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort
     * @return \stdClass the users
     */
    public static function get_issues($certificationid, $groupmode, $cm, $limitfrom, $limitnum, $sort = '') {
        global $DB;

        // Get the conditional SQL.
        list($conditionssql, $conditionsparams) = self::get_conditional_issues_sql($cm, $groupmode);

        // If it is empty then return an empty array.
        if (empty($conditionsparams)) {
            return array();
        }

        // Add the conditional SQL and the certificationid to form all used parameters.
        $allparams = $conditionsparams + array('certificateid' => $certificationid);

        // Return the issues.
        $ufields = \user_picture::fields('u');
        $sql = "SELECT $ufields, ci.id as issueid, ci.code, ci.timecreated
                  FROM {user} u
            INNER JOIN {local_certificate_issues} ci
                    ON u.id = ci.userid
                 WHERE u.deleted = 0
                   AND ci.certificationid = :certificationid
                       $conditionssql";
        if ($sort) {
            $sql .= "ORDER BY " . $sort;
        } else {
            $sql .= "ORDER BY " . $DB->sql_fullname();
        }

        return $DB->get_records_sql($sql, $allparams, $limitfrom, $limitnum);
    }

    /**
     * Returns the total number of issues for a given certification.
     *
     * @param int $certificationid
     * @param \stdClass $cm the course module
     * @param bool $groupmode the group mode
     * @return int the number of issues
     */
    public static function get_number_of_issues($certificationid, $cm, $groupmode) {
        global $DB;

        // Get the conditional SQL.
        list($conditionssql, $conditionsparams) = self::get_conditional_issues_sql($cm, $groupmode);

        // If it is empty then return 0.
        if (empty($conditionsparams)) {
            return 0;
        }

        // Add the conditional SQL and the certificationid to form all used parameters.
        $allparams = $conditionsparams + array('certificateid' => $certificationid);

        // Return the number of issues.
        $sql = "SELECT COUNT(u.id) as count
                  FROM {user} u
            INNER JOIN {local_certificate_issues} ci
                    ON u.id = ci.userid
                 WHERE u.deleted = 0
                   AND ci.certificationid = :certificationid
                       $conditionssql";
        return $DB->count_records_sql($sql, $allparams);
    }

    /**
     * Returns an array of the conditional variables to use in the get_issues SQL query.
     *
     * @param \stdClass $cm the course module
     * @param bool $groupmode are we in group mode ?
     * @return array the conditional variables
     */
    public static function get_conditional_issues_sql($cm, $groupmode) {
        global $DB, $USER;

        // Get all users that can manage this certification to exclude them from the report.
        $context =  context_system::instance();
        $conditionssql = '';
        $conditionsparams = array();

        // Get all users that can manage this certification to exclude them from the report.
        $certmanagers = array_keys(get_users_by_capability($context, 'local/certificates:manage', 'u.id'));
        $certmanagers = array_merge($certmanagers, array_keys(get_admins()));
        list($sql, $params) = $DB->get_in_or_equal($certmanagers, SQL_PARAMS_NAMED, 'cert');
        $conditionssql .= "AND NOT u.id $sql \n";
        $conditionsparams += $params;

        if ($groupmode) {
            $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);
            $currentgroup = groups_get_activity_group($cm);

            // If we are viewing all participants and the user does not have access to all groups then return nothing.
            if (!$currentgroup && !$canaccessallgroups) {
                return array('', array());
            }

            if ($currentgroup) {
                if (!$canaccessallgroups) {
                    // Guest users do not belong to any groups.
                    if (isguestuser()) {
                        return array('', array());
                    }

                    // Check that the user belongs to the group we are viewing.
                    $usersgroups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
                    if ($usersgroups) {
                        if (!isset($usersgroups[$currentgroup])) {
                            return array('', array());
                        }
                    } else { // They belong to no group, so return an empty array.
                        return array('', array());
                    }
                }

                $groupusers = array_keys(groups_get_members($currentgroup, 'u.*'));
                if (empty($groupusers)) {
                    return array('', array());
                }

                list($sql, $params) = $DB->get_in_or_equal($groupusers, SQL_PARAMS_NAMED, 'grp');
                $conditionssql .= "AND u.id $sql ";
                $conditionsparams += $params;
            }
        }

        return array($conditionssql, $conditionsparams);
    }

    /**
     * Get number of certifications for a user.
     *
     * @param int $userid
     * @return int
     */
    public static function get_number_of_certifications_for_user($userid) {
        global $DB;

        $sql = "SELECT COUNT(*)
                  FROM {local_certificate} c
            INNER JOIN {local_certificate_issues} ci
                    ON c.id = ci.certificationid
                 WHERE ci.userid = :userid";
        return $DB->count_records_sql($sql, array('userid' => $userid));
    }

    /**
     * Gets the certifications for the user.
     *
     * @param int $userid
     * @param int $limitfrom
     * @param int $limitnum
     * @param string $sort
     * @return array
     */
    public static function get_certifications_for_user($userid, $limitfrom, $limitnum, $sort = '') {
        global $DB;

        if (empty($sort)) {
            $sort = 'ci.timecreated DESC';
        }

        $sql = "SELECT c.id, c.name, co.fullname as coursename, ci.code, ci.timecreated
                  FROM {local_certificate} c
            INNER JOIN {local_certificate_issues} ci
                    ON c.id = ci.certificationid
            INNER JOIN {course} co
                    ON c.course = co.id
                 WHERE ci.userid = :userid
              ORDER BY $sort";
        return $DB->get_records_sql($sql, array('userid' => $userid), $limitfrom, $limitnum);
    }
        /**
     * Generates a 10-digit code of random letters and numbers.
     *
     * @return string
     */
    public static function generate_code() {
        global $DB;

        $uniquecodefound = false;
        $code = random_string(10);
        while (!$uniquecodefound) {
            if (!$DB->record_exists('local_certificate_issues', array('code' => $code))) {
                $uniquecodefound = true;
            } else {
                $code = random_string(10);
            }
        }

        return $code;
    }
    /**
     * [certificationtypes description]
     * @method certificationtypes
     * @return [type]         [description]
     */
    public static function certificationtypes(){
        return array(1 => get_string('certification', 'local_certificates'),
                     2 => get_string('learningplan', 'local_certificates'),
                     3 => get_string('certificate', 'local_certificates')
                 );
    }
    /**
     * Manage Certification (Create or Update the certification)
     * @method manage_certification
     * @param  Object           $data Clasroom Data
     * @return Integer               Certification ID
     */
    public function manage_certification($certification) {
        global $DB, $USER;
        $certification->shortname = $certification->name;
        if(empty($certification->trainers)){
            
                $certification->trainers=NULL;
        }
        if(empty($certification->capacity)||$certification->capacity==0){
            
               $certification->capacity=NULL;
        }
        try {
            if ($certification->id > 0) {
                $certification->timemodified = time();
                $certification->usermodified = $USER->id;

                $local_certification=$DB->get_record_sql("SELECT id,startdate,enddate,allow_multi_session,instituteid FROM {local_certificate} where id = {$certification->id} ");
                $allow_multi_session=$local_certification->allow_multi_session;

                    //if(($certification->startdate != $local_certification->startdate) || ($certification->enddate != $local_certification->enddate)){
                    //  $this->certification_sessions_delete($certification->id);
                    //}

                $DB->update_record('local_certificate', $certification);
                 $certification->id=$DB->get_field('local_certificate','id',array('certificateid'=>$certification->id));
                $DB->update_record('local_certificate', $certification);
                $this->certification_set_events($certification); // added by sreenivas
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $certification->id,
                    'other'=>'certification',
                    'url'=>'view.php',
                    ''
                );
                // Trigger certification updated event.
                
                $event = \local_certification\event\certification_updated::create($params);
                $event->add_record_snapshot('local_certificate', $certification->id);
                $event->trigger();
                // Update certification tags.
                // if (isset($certification->tags)) {
                //     \local_tags_tag::set_item_tags('local_certificate', 'certification', $certification->id, context_system::instance(), $certification->tags, 0, $certification->costcenter, $certification->department);
                // }
                
            } else {
                $certification->status = 0;
                $certification->timecreated = time();
                $certification->usercreated = $USER->id;
                if (has_capability('local/certificates:manage', context_system::instance())){ 
                    $certification->department = -1;
                    $certification->subdepartment = -1;
                      if (!is_siteadmin() && (has_capability('local/certificates:manage', context_system::instance())
                       || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                        $certification->department=$USER->open_departmentid;
                      }
                }
                $certification->certificateid=$certification->id = $DB->insert_record('local_certificate', $certification);

                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $certification->id
                );
            
                $event = \local_certificate\event\certificate_created::create($params);
                $event->add_record_snapshot('local_certificate', $certification->id);
                $event->trigger();

                // Update certification tags.
                // if (isset($certification->tags)) {
                //     \local_tags_tag::set_item_tags('local_certificate', 'certificate', $certification->id, context_system::instance(), $certification->tags, 0, $certification->costcenter, $certification->department);
                // }
                
                $newitemid = $DB->insert_record('local_certificate', $certification);
           
                $data=new stdClass();
                $data->templateid = $newitemid;
                $data->pagewidth_0 = 210;
                $data->pageheight_0 = 275;
                $data->pageleftmargin_0 = 0;
                $data->pagerightmargin_0 = 0;
                $data->sequence =1;
                
                $this->add_update_design_certification($data);
            
                $certification->templateid = $newitemid;
                
                $certification->shortname = 'class' . $certification->id;
                $DB->update_record('local_certificate', $certification);
                
                // Trigger certification updated event.
                
                //$event = \local_certificates\event\certification_updated::create($params);
                //$event->add_record_snapshot('local_certificate', $certification->id);
                //$event->trigger();
                
            }
            if ($certification->id) {
                $this->manage_certification_trainers($certification->id, 'all', $certification->trainers);

                $sessions_count=$DB->count_records('local_certificate', array('id' => $certification->id));

                 if(($certification->id == 0 && $certification->allow_multi_session==1)||(($certification->allow_multi_session!=$allow_multi_session || $sessions_count==0) && $certification->id >0 && $certification->allow_multi_session==1)&&$certification->startdate>0&&$certification->enddate>0){
                //if($certification->allow_multi_session==1){    
                    $this->manage_certification_automatic_sessions($certification->id,$certification->startdate,$certification->enddate);
                }
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $certification->id;
    }
     /**
    * This creates new events given as timeopen and closeopen by certification.
    *
    * @global object
    * @param object $certification
    * @return void
    */
    function certification_set_events($certification) {
        global $DB, $CFG, $USER;
        // Include calendar/lib.php.
        require_once($CFG->dirroot.'/calendar/lib.php');
   
        // evaluation start calendar events.
        $eventid = $DB->get_field('event', 'id',
               array('modulename' => '0', 'instance' => 0, 'plugin'=> 'local_certificates', 'plugin_instance'=>$certification->id, 'eventtype' => 'open', 'local_eventtype' => 'open'));
   
        if (isset($certification->startdate) && $certification->startdate > 0) {
           $event = new stdClass();
           $event->eventtype    = 'open';
           $event->type         = empty($certification->enddate) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
           $event->name         = $certification->name;
           $event->description  = $certification->name;
           $event->timestart    = $certification->startdate;
           $event->timesort     = $certification->startdate;
           $event->visible      = 1;
           $event->timeduration = 0;
           $event->plugin_instance = $certification->id;
           $event->plugin = 'local_certificates';
           $event->local_eventtype    = 'open';
           $event->relateduserid    = $USER->id;
           if ($eventid) {
               // Calendar event exists so update it.
               $event->id = $eventid;
               $calendarevent = \calendar_event::load($event->id);
               $calendarevent->update($event);
           } else {
               // Event doesn't exist so create one.
               $event->courseid     = 0;
               $event->groupid      = 0;
               $event->userid       = 0;
               $event->modulename   = 0;
               $event->instance     = 0;
               $event->eventtype    = 'open';;
               \calendar_event::create($event);
           }
       } else if ($eventid) {
           // Calendar event is on longer needed.
           $calendarevent = \calendar_event::load($eventid);
           $calendarevent->delete();
       }
   
       // evaluation close calendar events.
       $eventid = $DB->get_field('event', 'id',
               array('modulename' => '0', 'instance' => 0, 'plugin'=> 'local_certificates', 'plugin_instance'=>$certification->id, 'eventtype' => 'close', 'local_eventtype' => 'close'));
   
       if (isset($certification->enddate) && $certification->enddate > 0) {
           $event = new stdClass();
           $event->type         = CALENDAR_EVENT_TYPE_ACTION;
           $event->eventtype    = 'close';
           $event->name         = $certification->name;
           $event->description  = $certification->name;
           $event->timestart    = $certification->enddate;
           $event->timesort     = $certification->enddate;
           $event->visible      = 1;
           $event->timeduration = 0;
           $event->plugin_instance = $certification->id;
           $event->plugin = 'local_certificates';
           $event->local_eventtype    = 'close';
           $event->relateduserid    = $USER->id;
           if ($eventid) {
               // Calendar event exists so update it.
               $event->id = $eventid;
               $calendarevent = \calendar_event::load($event->id);
               $calendarevent->update($event);
           } else {
               // Event doesn't exist so create one.
               $event->courseid     = 0;
               $event->groupid      = 0;
               $event->userid       = 0;
               $event->modulename   = 0;
               $event->instance     = 0;
               \calendar_event::create($event);
           }
       } else if ($eventid) {
           // Calendar event is on longer needed.
           $calendarevent = \calendar_event::load($eventid);
           $calendarevent->delete();
       }
    }
    /**
     * Manage Certification Sessions (Create / Update)
     * @method session_management
     * @param  Object             $data Session Data
     * @return Integer                  Session ID
     */
    public function manage_certification_sessions($session) {
        global $DB, $USER;
         
        $session->description = $session->cs_description['text'];
        try {
            $sessions_validation_start=$this->sessions_validation($session->certificationid,$session->timestart,$session->id);
             $session->duration=($session->timefinish - $session->timestart)/60;
            if($sessions_validation_start){
                return true;
            }
            $sessions_validation_end=$this->sessions_validation($session->certificationid,$session->timefinish,$session->id);
            if($sessions_validation_end){
                return true;
            }
            if ($session->id > 0) {
                $session->timemodified = time();
                $session->usermodified = $USER->id;
                //print_object($session);exit;
                $DB->update_record('local_certification_sessions', $session);
                $this->session_set_events($session); //added by sreenivas
                $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $session->id
                     );
                    
                $event = \local_certification\event\certification_sessions_updated::create($params);
                $event->add_record_snapshot('local_certification', $session->certificationid);
                $event->trigger();
                
                if($session->onlinesession ==1){
                       $online_sessions_integration=new \local_certification\event\online_sessions_integration();
                         $online_sessions_integration->online_sessions_type($session, $session->id,$type=1,'update');
                }
                $certification = new stdClass();
                $certification->id = $session->certificationid;
                $certification->totalsessions = $DB->count_records('local_certification_sessions', array('certificationid' => $session->certificationid));
                // $certification->activesessions = $DB->count_records('local_certification', array('id' => $certificationid));
                $DB->update_record('local_certificate', $certification);
                
                //$params = array(
                //    'context' => context_system::instance(),
                //    'objectid' => $session->certificationid
                //);
                //
                //$event = \local_certification\event\certification_updated::create($params);
                //$event->add_record_snapshot('local_certification',$session->certificationid);
                //$event->trigger();
            } else {
                $session->timecreated = time();
                $session->usercreated = $USER->id;
          
                $session->id = $DB->insert_record('local_certification_sessions', $session);
                $this->session_set_events($session); // added by sreenivas
                $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $session->id
                     );
                    
                $event = \local_certificates\event\certification_sessions_created::create($params);
                $event->add_record_snapshot('local_certificate', $session->certificationid);
                $event->trigger();
                if ($session->id) {
                    if($session->onlinesession ==1){
                        $online_sessions_integration=new \local_certificates\event\online_sessions_integration();
                         $online_sessions_integration->online_sessions_type($session, $session->id,$type=1,'create');
                    }
                    $certification = new stdClass();
                    $certification->id = $session->certificationid;
                    $certification->totalsessions = $DB->count_records('local_certification_sessions', array('certificationid' => $session->certificationid));
                    $certification->activesessions = $DB->count_records('local_certification_sessions', array('certificationid' => $session->certificationid,'attendance_status'=>1));
                    $DB->update_record('local_certificate', $certification);
                //$params = array(
                //    'context' => context_system::instance(),
                //    'objectid' => $session->certificationid
                //);
                //
                //$event = \local_certification\event\certification_updated::create($params);
                //$event->add_record_snapshot('local_certification',$session->certificationid);
                //$event->trigger();
                }
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $session->id;
    }
    /**
    * This creates new events given as timeopen and closeopen by session.
    *
    * @global object
    * @param object session
    * @return void
    */
    function session_set_events($session) {
        global $DB, $CFG, $USER;
        // Include calendar/lib.php.
        require_once($CFG->dirroot.'/calendar/lib.php');
   
        // session start calendar events.
        $eventid = $DB->get_field('event', 'id',
               array('modulename' => '0', 'instance' => 0, 'plugin'=> 'local_certificate', 'plugin_instance'=>$session->certificationid, 'plugin_itemid'=>$session->id, 'eventtype' => 'open', 'local_eventtype' => 'session_open'));
   
        if (isset($session->timestart) && $session->timestart > 0) {
            $event = new stdClass();
            $event->eventtype    = 'open';
            $event->type         = empty($session->timefinish) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
            $event->name         = $session->name;
            $event->description  = $session->name;
            $event->timestart    = $session->timestart;
            $event->timesort     = $session->timestart;
            $event->visible      = 1;
            $event->timeduration = 0;
            $event->plugin_instance = $session->certificationid;
            $event->plugin_itemid = $session->id;
            $event->plugin = 'local_certificate';
            $event->local_eventtype    = 'session_open';
            $event->relateduserid    = $USER->id;
            if ($eventid) {
               // Calendar event exists so update it.
               $event->id = $eventid;
               $calendarevent = \calendar_event::load($event->id);
               $calendarevent->update($event);
            } else {
               // Event doesn't exist so create one.
               $event->courseid     = 0;
               $event->groupid      = 0;
               $event->userid       = 0;
               $event->modulename   = 0;
               $event->instance     = 0;
               $event->eventtype    = 'open';
               \calendar_event::create($event);
            }
       } else if ($eventid) {
            // Calendar event is on longer needed.
            $calendarevent = \calendar_event::load($eventid);
            $calendarevent->delete();
       }
   
        // session close calendar events.
        $eventid = $DB->get_field('event', 'id',
               array('modulename' => '0', 'instance' => 0, 'plugin'=> 'local_certificates', 'plugin_instance'=>$session->certificationid, 'plugin_itemid'=>$session->id, 'eventtype' => 'close', 'local_eventtype' => 'session_close'));
   
        if (isset($session->timefinish) && $session->timefinish > 0) {
            $event = new stdClass();
            $event->type         = CALENDAR_EVENT_TYPE_ACTION;
            $event->eventtype    = 'close';
            $event->name         = $session->name;
            $event->description  = $session->name;
            $event->timestart    = $session->timefinish;
            $event->timesort     = $session->timefinish;
            $event->visible      = 1;
            $event->timeduration = 0;
            $event->plugin_instance = $session->certificationid;
            $event->plugin_itemid = $session->id;
            $event->plugin = 'local_certification';
            $event->local_eventtype    = 'session_close';
            $event->relateduserid    = $USER->id;
            if ($eventid) {
               // Calendar event exists so update it.
               $event->id = $eventid;
               $calendarevent = \calendar_event::load($event->id);
               $calendarevent->update($event);
            } else {
                // Event doesn't exist so create one.
                $event->courseid     = 0;
                $event->groupid      = 0;
                $event->userid       = 0;
                $event->modulename   = 0;
                $event->instance     = 0;
                \calendar_event::create($event);
            }
        } else if ($eventid) {
            // Calendar event is on longer needed.
            $calendarevent = \calendar_event::load($eventid);
            $calendarevent->delete();
        }
    }
    public function manage_certification_completions($completions) {
        global $DB, $USER;
        //print_object($completions);
        if(!empty($completions->sessionids)&&is_array ($completions->sessionids)){
            $completions->sessionids=implode(',',$completions->sessionids);
        }else{
            $completions->sessionids=null;
        }
        if(!empty($completions->courseids)&&is_array ($completions->courseids)){
            $completions->courseids=implode(',',$completions->courseids);
        }else{
            $completions->courseids=null;
        }
        if(empty($completions->sessiontracking)){
           $completions->sessiontracking=null;
        }
        if(empty($completions->coursetracking)){
           $completions->coursetracking=null;
        }
        try { 
            if ($completions->id > 0) {
                $completions->timemodified = time();
                $completions->usermodified = $USER->id;
                $DB->update_record('local_certificate', $completions);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $completions->id
                );
            
                $event = \local_certification\event\certification_completions_settings_updated::create($params);
                $event->add_record_snapshot('local_certificate',$completions->certificationid);
                $event->trigger();
            } else {
                $completions->timecreated = time();
                $completions->usercreated = $USER->id;
          
                $completions->id = $DB->insert_record('local_certificatn_completion', $completions);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $completions->id
                );
            
                $event = \local_certification\event\certification_completions_settings_created::create($params);
                $event->add_record_snapshot('local_certificate', $completions->certificationid);
                $event->trigger();
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $completions->id;
    }
    /**
     * [certification_sessions_delete description]
     * @param  [type] $certificationid [description]
     * @return [type]              [description]
     */
    public function certification_sessions_delete($certificationid){
         global $DB, $USER;
         $certification_sessions=$DB->get_records_sql_menu("SELECT id,id as sessionid
                                                FROM {local_certification_sessions} 
                                                WHERE certificationid = {$certificationid}");
         foreach ($certification_sessions as $id) {
    
                $DB->delete_records('local_certificatn_attendance', array('sessionid' => $id));
               
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' =>$id
                );
                
                $event = \local_certification\event\certification_sessions_deleted::create($params);
                $event->add_record_snapshot('local_certificate', $certificationid);
                $event->trigger();
               
                $DB->delete_records('local_certification_sessions', array('id' => $id));
                 
                $certification = new stdClass();
                $certification->id = $certificationid;
                $certification->totalsessions = $DB->count_records('local_certification_sessions', array('certificationid' => $certificationid));
                $certification->activesessions = $DB->count_records('local_certification_sessions', array('certificationid' => $certificationid,'attendance_status'=>1));
                $DB->update_record('local_certification', $certification);
                //$params = array(
                //    'context' => context_system::instance(),
                //    'objectid' => $certificationid
                //);
                //
                //$event = \local_certification\event\certification_updated::create($params);
                //$event->add_record_snapshot('local_certification', $certificationid);
                //$event->trigger();
         }
            $certification_users=$DB->get_records_menu('local_certification_users',
                                                       array('certificationid' =>$certificationid), 'id', 'id, userid');
                
                foreach($certification_users as $certification_user){
                   $attendedsessions = $DB->count_records('local_certificatn_attendance',
                        array('certificationid' => $certificationid,
                            'userid' => $certification_user, 'status' => SESSION_PRESENT));
        
                    $attendedsessions_hours=$DB->get_field_sql("SELECT ((sum(lcs.duration))/60) AS hours
                                                FROM {local_certification_sessions} as lcs
                                                WHERE  lcs.certificationid =$certificationid
                                                and lcs.id in(SELECT sessionid  FROM {local_certificatn_attendance} where certificationid=$certificationid and userid=$certification_user and status=1)");
                    
                    if(empty($attendedsessions_hours)){
                        $attendedsessions_hours=0;
                    }
        
                    $DB->execute('UPDATE {local_certificate_users} SET attended_sessions = ' .
                        $attendedsessions . ',hours = ' .
                        $attendedsessions_hours . ', timemodified = ' . time() . ',
                        usermodified = ' . $USER->id . ' WHERE certificationid = ' .
                        $certificationid . ' AND userid = ' . $certification_user);
                }
    }
    /**
     * Update Certification Location and Date
     * @method location_date
     * @param  Object        $data Certification Location and Nomination Data
     * @return Integer        Certification ID
     */
    public function location_date($data) {
        global $DB, $USER;
        $location = new stdClass();
        $location->institute_type = $data->institute_type;
        $location->instituteid = $data->instituteid;
        $location->nomination_startdate = $data->nomination_startdate;
        $location->nomination_enddate = $data->nomination_enddate;
        try {
            $local_certification=$DB->get_record_sql("SELECT id,instituteid FROM {local_certificate} where id = {$data->id}");
            if(isset($location->instituteid)&&($location->instituteid != $local_certification->instituteid) && ($local_certification->instituteid!=0)){
                $DB->execute('UPDATE {local_certification_sessions} SET roomid =0,timemodified = ' . time() . ',
                   usermodified = ' . $USER->id . ' WHERE certificationid = ' .
                   $data->id. '');
           }
            $location->id = $data->id;
            $location->timemodified = time();
            $location->usermodified = $USER->id;
            $DB->update_record('local_certificate', $location);
           // $params = array(
           //    'context' => context_system::instance(),
           //    'objectid' => $location->id
           // );
           //
           //$event = \local_certification\event\certification_updated::create($params);
           //$event->add_record_snapshot('local_certification', $location->id);
           //$event->trigger();
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $data->id;
    }
    public function add_update_design_certification($data) {
        global $DB, $USER;

        try {
            $certification=new stdClass();
            $certification->templateid = $data->templateid;
            $certification->width = $data->pagewidth_0;
            $certification->height = $data->pageheight_0;
            $certification->leftmargin = $data->pageleftmargin_0;
            $certification->rightmargin = $data->pagerightmargin_0;
            $certification->sequence =1;
            
            
            if(isset($certification->templateid)&&$certification->templateid>0){
                $certification_pages=$DB->get_field('local_certificate_pages','id',array('templateid'=>$certification->templateid));
                if($certification_pages){
                    $certification->id =  $certification_pages;
                    $certification->timemodified =  time();
                    $newitemid = $DB->update_record('local_certificate_pages', $certification);
                }else{
                    $certification->timecreated =  time();
                    $newitemid = $DB->insert_record('local_certificate_pages', $certification);
                }
            }
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $data->id;
    }
    /**
     * certifications
     * @method certifications
     * @param  Object     $stable Datatable fields
     * @return Array  Certifications and totalcertificationcount
     */
    public function certifications($stable,$request = false) {
        global $DB, $USER;
        $params = array();
        $certifications = array();
        $certificationscount = 0;
        $concatsql = '';
        $status_array=array();

        if(has_capability('local/certificates:view', context_system::instance())){
            $status_array[]=0;
        }
        //if(has_capability('local/certification:view_activecertificationtab', context_system::instance())){
            $status_array[]=1;
        //}
        if(has_capability('local/certificates:view', context_system::instance())){
            $status_array[]=2;
        }
        //if(has_capability('local/certification:view_cancelledcertificationtab', context_system::instance())){
            $status_array[]=3;
        //}
        //if(has_capability('local/certification:view_completedcertificationtab', context_system::instance())){
            $status_array[]=4;
        //}
        if (!empty($stable->search)) {
            $fields = array("name");
            $fields = implode(" LIKE :search1 OR ", $fields);
            $fields .= " LIKE :search2 ";
            $params['search1'] = '%' . $stable->search . '%';
            $params['search2'] = '%' . $stable->search . '%';
            $concatsql .= " AND ($fields) ";
        }

        if ((has_capability('local/certificates:manage', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/certificates:manage', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance()))) ) {
                $condition= " AND (cc.id = :costcenter)";
                $params['costcenter'] = $USER->open_costcenterid;
                $status_arrays=implode(',',$status_array);
                $concatsql .= " AND c.status in ($status_arrays) ";
              if ((has_capability('local/certificates:manage_owndepartments', context_system::instance())
                       || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                     $condition.= " AND (c.department = :department )";
                     $params['department'] = $USER->open_departmentid;
                    
                
              }
              $concatsql .=$condition;
             if(has_capability('local/certificates:trainer_viewcertification', context_system::instance())) {
                  $mycertifications = $DB->get_records_menu('local_certification_trainers', array('trainerid' => $USER->id), 'id', 'id, certificationid');
                  if(!empty($mycertifications)) {
                      $mycertifications = implode(', ', $mycertifications);
                      $concatsql .= " AND c.id IN ( $mycertifications )";
                     
                  } else {
                      return compact('certifications', 'certificationscount');
                  }
            }
        }elseif(!is_siteadmin() && (!has_capability('local/certification:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance()))){
            $mycertifications = $DB->get_records_menu('local_certification_users', array('userid' => $USER->id), 'id', 'id, certificationid');
            if(isset($stable->certificationid)&&!empty($stable->certificationid)){
                    $userenrolstatus = $DB->record_exists('local_certification_users', array('certificationid' => $stable->certificationid, 'userid' => $USER->id));
                    $status=$DB->get_field('local_certification','status',array('id'=> $stable->certificationid));
                     $certification_costcenter=$DB->get_field('local_certification','costcenter',array('id'=> $stable->certificationid));  
                    if ($status == 1 && !$userenrolstatus && $certification_costcenter==$USER->open_costcenterid) {
                        
                    }else{
                        if(!empty($mycertifications)) {
                            $mycertifications = implode(', ', $mycertifications);
                            $concatsql .= " AND c.id IN ( $mycertifications )";
                             $status_arrays=implode(',',$status_array);
                             $concatsql .= " AND c.status in ($status_arrays) ";
                        }else{
                            return compact('certifications', 'certificationscount');
                        }
                    }
            }else{
                if(!empty($mycertifications)) {
                    $mycertifications = implode(', ', $mycertifications);
                    $concatsql .= " AND c.id IN ( $mycertifications )";
                    $status_arrays=implode(',',$status_array);
                    $concatsql .= " AND c.status in ($status_arrays) ";
                }else{
                    return compact('certifications', 'certificationscount');
                }
            }
        }else{
             $status_arrays=implode(',',$status_array);
             $concatsql .= " AND c.status in ($status_arrays) ";
        }
        if (isset($stable->certificationid) && $stable->certificationid > 0) {
            $concatsql .= " AND c.id = :certificationid";
            $params['certificationid'] = $stable->certificationid;
        }

        if (isset($stable->certificationstatus) && $stable->certificationstatus!=-1) {
            $concatsql .= " AND c.status = :certificationstatus";
            $params['certificationstatus'] = $stable->certificationstatus;
        }
        
        $countsql = "SELECT COUNT(c.id) ";
        if($request == true){
            $fromsql = "SELECT group_concat(c.id) as certificationids"; 
        }else{
          $fromsql = "SELECT c.*, (SELECT COUNT(DISTINCT cu.userid)
                                  FROM {local_certification_users} AS cu
                                  WHERE cu.certificationid = c.id
                              ) AS enrolled_users";  
        }
        if ((has_capability('local/certification:managecertification', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/certification:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())) ) ) {

                $joinon="cc.id = c.costcenter";
             if ((has_capability('local/certification:manage_owndepartments', context_system::instance())
                       || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                $joinon="cc.id = c.department OR cc.id = c.costcenter";
             }

         }else{
            $joinon="cc.id = c.costcenter";
         }                                        
        $sql = " FROM {local_certification} AS c
                 JOIN {local_costcenter} AS cc ON $joinon
                WHERE 1 = 1 ";
        $sql .= $concatsql;
        //echo $fromsql . $sql;
        // print_object($params);
        // echo $countsql . $sql;
        // print_object($params);
        if (isset($stable->certificationid) && $stable->certificationid > 0) {
            $certifications = $DB->get_record_sql($fromsql . $sql, $params);
        } else {
            try {
                $certificationscount = $DB->count_records_sql($countsql . $sql, $params);
               
                if ($stable->thead == false) {
                    $sql .= " ORDER BY c.id DESC";
                    if($request == true){
                        $certifications = $DB->get_record_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    }else{
                        $certifications = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
                    }
                }
            } catch (dml_exception $ex) {
                $certificationscount = 0;
            }
        }
        if (isset($stable->certificationid) && $stable->certificationid > 0) {
            return $certifications;
        } else {
            return compact('certifications', 'certificationscount');
        }

    }
    /**
     * Certification sessions
     * @method sessions
     * @param  Integer   $certificationid Certification ID
     * @param  Object   $stable      Datatable fields
     * @return Array    Sessions and total session count for the perticular certification
     */
    public function certificationsessions($certificationid, $stable) {
        global $DB, $USER;
        $certification = $DB->get_record('local_certification', array('id' => $certificationid));
        if (empty($certification)) {
            print_error('certification data missing');
        }
        $concatsql = '';
        if (!empty($stable->search)) {
            $fields =array (0=>'cs.name',
									1=>'cr.name'
							);
				$fields = implode(" LIKE '%" .$stable->search. "%' OR ", $fields);
				$fields .= " LIKE '%" .$stable->search. "%' ";
                $concatsql .= " AND ($fields) ";
        }
        $params = array();
        $certifications = array();
       
        $countsql = "SELECT COUNT(cs.id) ";
        $fromsql = "SELECT cs.*, cr.name as room";
        $sql = " FROM {local_certification_sessions} AS cs
                LEFT JOIN {user} AS u ON u.id = cs.trainerid
                LEFT JOIN {local_location_room} AS cr ON cr.id = cs.roomid
                WHERE 1 = 1 AND cs.certificationid = {$certificationid}";
        $sql .= $concatsql;
        try {
            $sessionscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY cs.id ASC";
                $sessions = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $sessionscount = 0;
        }
        return compact('sessions', 'sessionscount');
    }
     public function sessions_validation($certificationid,$sessiondate,$sessionid=0) {
        global $DB;
        $return=false;
        if($certificationid && $sessiondate){
            $params = array();
            $params['certificationid'] = $certificationid;
            // $params['sessiondate_start'] = \local_costcenter\lib::get_userdate('d/m/Y H:i',$sessiondate);
            // $params['sessiondate_end'] = \local_costcenter\lib::get_userdate('d/m/Y H:i',$sessiondate);

            $params['session_start'] = $sessiondate;
            $params['session_end'] = $sessiondate;

            // $sql="SELECT * FROM {local_certification_sessions} where certificationid=:certificationid and (from_unixtime(timestart,'%Y-%m-%d %H:%i')=:sessiondate_start or from_unixtime(timefinish,'%Y-%m-%d %H:%i')=:sessiondate_end)";
            $sql = "SELECT * FROM {local_certification_sessions} WHERE certificationid=:certificationid AND (timestart = :session_start OR timefinish =:session_end) ";
            if($sessionid>0){
                 $sql.=" AND id !=:sessionid ";
                 $params['sessionid'] = $sessionid;
            }
            // print_object($params);
            // echo $sql;
            $return=$DB->record_exists_sql($sql,$params); 

        }

        return $return;
     }
    /**
     * [add_certification_signups description]
     * @method add_certification_signups
     * @param  [type]                $certificationid [description]
     * @param  [type]                $userid      [description]
     * @param  integer               $sessionid   [description]
     */
    public function add_certification_signups($certificationid, $userid, $sessionid = 0) {
        global $DB, $USER;
        $certification = $DB->record_exists('local_certification',  array('id' => $certificationid));
        if (!$certification) {
            print_error("Certification Not Found!");
        }
        $user = $DB->record_exists('user', array('id' => $userid));
        if (!$user) {
            print_error("User Not Found!");
        }
        if ($sessionid > 0) {
            $session = $DB->record_exists('local_certification_sessions', array('id' => $sessionid, 'certificationid' => $certificationid));
            if (!$session) {
                print_error("Session Not Found!");
            }
        }
        $sessions = $DB->get_records('local_certification_sessions', array('certificationid' => $certificationid));
        foreach($sessions as $session) {
            $checkattendeesignup = $DB->get_record('local_certificatn_attendance', array('certificationid' => $certificationid, 'sessionid' => $session->id, 'userid' => $userid));
            if (!empty($checkattendeesignup)) {
                continue;
            } else {
                $attendeesignup = new stdClass();
                $attendeesignup->certificationid = $certificationid;
                $attendeesignup->sessionid = $session->id;
                $attendeesignup->userid = $userid;
                $attendeesignup->status = 0;
                $attendeesignup->usercreated = $USER->id;
                $attendeesignup->timecreated = time();
                $id=$DB->insert_record('local_certificatn_attendance',  $attendeesignup);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $id
                );
            
                $event = \local_certification\event\certification_attendance_created_updated::create($params);
                $event->add_record_snapshot('local_certification',$certificationid);
                $event->trigger();
            }
        }
        return true;
    }
    /**
     * [remove_certification_signups description]
     * @method remove_certification_signups
     * @param  [type]                   $certificationid [description]
     * @param  [type]                   $userid      [description]
     * @param  integer                  $sessionid   [description]
     * @return [type]                                [description]
     */
    public function remove_certification_signups($certificationid, $userid, $sessionid = 0) {
        global $DB, $USER;
        if ($sessionid > 0) {
            $sessions = $DB->get_records('local_certification_sessions', array('certificationid' => $certificationid, 'id' => $sessionid));
        } else {
            $sessions = $DB->get_records('local_certification_sessions', array('certificationid' => $certificationid));
        }
        foreach($sessions as $session) {
            $checkattendeesignup = $DB->get_record('local_certificatn_attendance', array('certificationid' => $certificationid, 'sessionid' => $session->id, 'userid' => $userid));
            if (!empty($checkattendeesignup)) {
                
                $DB->delete_records('local_certificatn_attendance', array('certificationid' => $certificationid, 'sessionid' => $session->id, 'userid' => $userid));
            }
        }
        return true;
    }
    /**
     * [certification_get_attendees description]
     * @method certification_get_attendees
     * @param  [type]                  $sessionid [description]
     * @return [type]                             [description]
     */
    public function certification_get_attendees($certificationid, $sessionid = 0) {
        global $DB, $OUTPUT,$USER;
        $concatsql = "";
        $selectfileds = '';
        $whereconditions = '';
        $params = array();
        if ($sessionid > 0) {
            $selectfileds = ", ca.id as attendanceid, ca.status";
            $concatsql .= " JOIN {local_certification_sessions} AS cs ON cs.certificationid = cu.certificationid AND cs.certificationid = $certificationid
            LEFT JOIN {local_certificatn_attendance} AS ca ON ca.certificationid = cu.certificationid
              AND ca.sessionid = cs.id AND ca.userid = cu.userid";
            $whereconditions = " AND cs.id = $sessionid";
        }
        $signupssql = "SELECT DISTINCT u.id, u.firstname, u.lastname,
                              u.email, u.picture, u.firstnamephonetic, u.lastnamephonetic,
                              u.middlename, u.alternatename, u.imagealt $selectfileds
                        FROM {user} AS u
                        JOIN {local_certification_users} AS cu ON
                                (cu.userid = u.id AND cu.certificationid = $certificationid)
                            $concatsql
                       WHERE cu.certificationid = :certificationid $whereconditions";
                        $params['certificationid'] = $certificationid;
                        
        if ((has_capability('local/certification:managecertification', context_system::instance())) && (!is_siteadmin()
            && (!has_capability('local/certification:manage_multiorganizations', context_system::instance())
                && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
            $condition            = " AND (u.open_costcenterid = :costcenter)";
            $params['costcenter'] = $USER->open_costcenterid;
            if ((has_capability('local/certification:manage_owndepartments', context_system::instance())
                 || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                $condition .= " AND (u.open_departmentid = :department )";
                $params['department'] = $USER->open_departmentid;
            }
            if (has_capability('local/certification:trainer_viewcertification', context_system::instance())) {
                 $condition="";
            }
            $signupssql .= $condition;
        }               
        $signups = $DB->get_records_sql($signupssql,$params);
        return $signups;
    }
    /**
     * [certification_evaluations description]
     * @method certification_evaluations
     * @param  [type]                $certificationid [description]
     * @return [type]                             [description]
     */
    public function certification_evaluations($certificationid){
        global $DB, $USER;
        $params = array();
        $certifications = array();
        $concatsql = '';
        $sql = "SELECT e.*
                 FROM {local_evaluations} AS e
                WHERE e.plugin = 'certification' AND e.instance = {$certificationid} AND e.deleted=0 ";
        if ((has_capability('local/certification:editfeedback', context_system::instance()) || has_capability('local/certification:deletefeedback', context_system::instance()))&&(has_capability('local/certification:managecertification', context_system::instance()) )) {
            $sql .=" AND e.visible <> 2 ";
        }else{
            $sql .=" AND e.visible =1 ";
        }        
        $sql .= $concatsql;
        try {
            $sql .= " ORDER BY e.id DESC";
            $evaluations = $DB->get_records_sql($sql, $params);
        } catch (dml_exception $ex) {
            $evaluations = array();
        }
        return $evaluations;
    }
    /**
     * [certification_add_assignusers description]
     * @method certification_add_assignusers
     * @param  [type]                    $certificationid   [description]
     * @param  [type]                    $userstoassign [description]
     * @return [type]                                   [description]
     */
    public function certification_add_assignusers($certificationid, $userstoassign, $request=false) {
        global $DB, $USER,$CFG;
        if(file_exists($CFG->dirroot . '/local/lib.php')){
            require_once($CFG->dirroot . '/local/lib.php');
        }
        $certificationenrol = enrol_get_plugin('certification');
        //$studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $courses = $DB->get_records_menu('local_certification_courses', array('certificationid' => $certificationid), 'id', 'id, courseid');
        $allow = true;
        $type = 'certification_enrol';
        $dataobj = $certificationid;
        $fromuserid = $USER->id;
        if ($allow) {
            $local_certification=$DB->get_record_sql("SELECT id,name,status FROM {local_certification} where id= {$certificationid}");

            $progress = 0;            
            if($request!=1){
                $progressbar = new \core\progress\display_if_slow(get_string('enrollusers', 'local_certification',$local_certification->name));
                $progressbar->start_html();
                $progressbar->start_progress('',count($userstoassign)-1);
            }
            foreach ($userstoassign as $key=>$adduser) {
                if($request!=1){
                    $progressbar->progress($progress);
                }
                $progress++;
                $certification_capacity_check=$this->certification_capacity_check($certificationid);
                if(!$certification_capacity_check){
                    $certificationuser = new stdClass();
                    $certificationuser->certificationid = $certificationid;
                    $certificationuser->courseid = 0;
                    $certificationuser->userid = $adduser;
                    $certificationuser->supervisorid = 0;
                    $certificationuser->prefeedback = 0;
                    $certificationuser->postfeedback = 0;
                    $certificationuser->hours = 0;
                    $certificationuser->usercreated = $USER->id;
                    $certificationuser->timecreated = time();
                    try {
                        $certificationuser->id = $DB->insert_record('local_certification_users', $certificationuser);
                        $params = array(
                            'context' => context_system::instance(),
                            'objectid' => $certificationuser->id 
                        );
                    
                        $event = \local_certification\event\certification_users_created::create($params);
                        $event->add_record_snapshot('local_certification', $local_certification);
                        $event->trigger();
                         
                        if($local_certification->status!=0){                            
                            // $emaillogs = new certificatenotifications_emails();
                            // $email_logs = $emaillogs->certificate_emaillogs($type,$dataobj,$certificationuser->userid,$fromuserid);
                            $emaillogs = new \local_certification\notification();
                            $touser = \core_user::get_user($certificationuser->userid);
                            $certificationinstance = $DB->get_record('local_certification', array('id' => $certificationid));
                            $email_logs = $emaillogs->certification_notification($type, $touser, $USER, $certificationinstance);
                            foreach($courses as $course) {
                                // $instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol'=>'certification'), '*', MUST_EXIST);
                                if ($certificationuser->id) {
                                    // $certificationenrol->enrol_user($instance, $adduser,$instance->roleid, time());
                                    $enrolcertificationuser = $this->manage_certification_course_enrolments($course, $adduser,'employee', 'enrol');
                                    
                                    // $DB->execute("UPDATE {local_certification_users} SET courseid = $course WHERE id = :id ", array('id' => $certificationuser->id));
                                }
                            }
                        }  
                        certification_evaluations_add_remove_users($certificationid,0,'users_to_feedback',$adduser);
                    } catch (dml_exception $ex) {
                        print_error($ex);
                    }
                }else{
                      $progress--;
                      break;
                }
            }
            if($request!=1){
                $progressbar->end_html();
            }
            $result=new stdClass();
            $result->changecount=$progress;
            $result->certification=$local_certification->name;
        }
        return $result;
    }
    /**
     * [certification_remove_assignusers description]
     * @method certification_remove_assignusers
     * @param  [type]                       $certificationid     [description]
     * @param  [type]                       $userstounassign [description]
     * @return [type]                                        [description]
     */
    public function certification_remove_assignusers($certificationid, $userstounassign, $request=false) {
        global $DB, $USER,$CFG;
        if(file_exists($CFG->dirroot . '/local/lib.php')){
            require_once($CFG->dirroot . '/local/lib.php');
        }
        $certificationenrol = enrol_get_plugin('certification');
        //$studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $courses = $DB->get_records_menu('local_certification_courses', array('certificationid' => $certificationid), 'id', 'id, courseid');
        $type = 'certification_unenroll';
        $dataobj = $certificationid;
        $fromuserid = $USER->id;
        try {
            // a large amount of grades.
            $local_certification=$DB->get_record_sql("SELECT id,name,status FROM {local_certification} where id= {$certificationid}");

            $progress = 0;
            if($request!=1){
                $progressbar = new \core\progress\display_if_slow(get_string('un_enrollusers', 'local_certification',$local_certification->name));
                $progressbar->start_html();
                $progressbar->start_progress('', count($userstounassign)-1);
            }
            foreach ($userstounassign as $key=>$removeuser) {
                if($request!=1){
                    $progressbar->progress($progress);
                }
                $progress++;
           
                    if($local_certification->status!=0){
                        if (!empty($courses)) {
                            foreach($courses as $course) {
                                if ($course > 0) {
                                    //$instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol'=>'certification'), '*', MUST_EXIST);
                                    //$certificationenrol->unenrol_user($instance, $removeuser, $instance->roleid, time());
                                    $unenrolcertificationuser = $this->manage_certification_course_enrolments($course, $removeuser,'employee', 'unenrol');
                                }
                            }
                        }
                }
                 certification_evaluations_add_remove_users($certificationid,0,'users_to_feedback',$removeuser,'update');
                $params = array(
                   'context' => context_system::instance(),
                   'objectid' =>$certificationid
                );
                
                $event = \local_certification\event\certification_users_deleted::create($params);
                $event->add_record_snapshot('local_certification', $certificationid);
                $event->trigger();
                $DB->delete_records('local_certification_users',  array('certificationid' => $certificationid, 'userid' => $removeuser));
                if($local_certification->status!=0){
                    // $emaillogs = new certificatenotifications_emails();
                    // $email_logs = $emaillogs->certificate_emaillogs($type,$dataobj,$removeuser,$fromuserid);
                    $emaillogs = new \local_certification\notification();
                    $touser = \core_user::get_user($removeuser);
                    $certificationinstance = $DB->get_record('local_certification', array('id' => $dataobj));
                    $email_logs = $emaillogs->certification_notification($type, $touser, $USER, $certificationinstance);
                }
                $DB->delete_records('local_certificatn_trainerfb',  array('certificationid' => $certificationid, 'userid' => $removeuser));
               
                $this->remove_certification_signups($certificationid, $removeuser);
            }
            if($request!=1){
                $progressbar->end_html();
            }
            $result=new stdClass();
            $result->changecount=$progress;
            $result->certification=$local_certification->name;
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return $result;
    }
    /**
     * [certification_manage_evaluations description]
     * @method certification_manage_evaluations
     * @param  [type]                       $certificationid [description]
     * @param  [type]                       $evaluation  [description]
     * @return [type]                                    [description]
     */
    public function certification_manage_evaluations($certificationid, $evaluation) {
        global $DB, $USER;
        $plugin_evaluationtypes = plugin_evaluationtypes();
        $params = array('certificationid' => $certificationid, 'evaluationid' => $evaluation->id, 'timemodified' => time(), 'usermodified' => $USER->id);
        switch($plugin_evaluationtypes[$evaluation->evaluationtype]) {
            case 'Trainer feedback':
                $return = $DB->execute('UPDATE {local_certification_trainers} SET feedback_id = :evaluationid, timemodified = :timemodified, usermodified = :usermodified WHERE certificationid = :certificationid AND feedback_id = 0', $params);
            break;
            case 'Training feedback':
                $return = $DB->execute('UPDATE {local_certification} SET trainingfeedbackid = :evaluationid, timemodified = :timemodified, usermodified = :usermodified WHERE id = :certificationid AND trainingfeedbackid = 0', $params);
            break;
            default:
                $return = false;
            break;
        }
        return $return;
    }
    /**
     * [manage_certification_trainers description]
     * @method manage_certification_trainers
     * @param  [type]                    $certificationid [description]
     * @param  [type]                    $action      [description]
     * @param  array                     $trainers    [description]
     * @return [type]                                 [description]
     */
    public function manage_certification_trainers($certificationid, $action, $trainers = array()) {
        global $DB, $USER,$CFG;
        if(file_exists($CFG->dirroot . '/local/lib.php')){
            require_once($CFG->dirroot . '/local/lib.php');
        }
        $certification_trainers = $DB->get_records_menu('local_certification_trainers', array('certificationid' => $certificationid), 'trainerid', 'id, trainerid');
        $enrolcertification = enrol_get_plugin('certification');
        //$teacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
       
        $certificationcourses = $DB->get_records_menu('local_certification_courses', array('certificationid' => $certificationid), 'id', 'courseid as course, courseid');
        switch ($action) {
            case 'insert':
                if(!empty($trainers)){
                     $newtrainers = array_diff($trainers, $certification_trainers);
                }else{
                    $newtrainers=$trainers;
                }
               $type = 'certification_enrol';
                $dataobj = $certificationid;
                $fromuserid = $USER->id;
                $string = 'trainer';
                if (!empty($newtrainers)) {
                    foreach($newtrainers as $newtrainer){
                        $trainer = new stdClass();
                        $trainer->certificationid = $certificationid;
                        $trainer->trainerid = $newtrainer;
                        $trainer->feedback_id = 0;
                        $trainer->timecreated = time();
                        $trainer->usercreated = $USER->id;
                        $trainer->id = $DB->insert_record('local_certification_trainers', $trainer);
                        $certification_status = $DB->get_field('local_certification','status',array('id' => $certificationid));
                        if($certification_status!=0){
                            // $emaillogs = new certificatenotifications_emails();
                            // $email_logs = $emaillogs->certificate_emaillogs($type,$dataobj,$trainer->trainerid,$fromuserid,$string);
                            $emaillogs = new \local_certification\notification();
                            $touser = \core_user::get_user($trainer->trainerid);
                            $certificationinstance = $DB->get_record('local_certification', array('id' => $dataobj));
                            $email_logs = $emaillogs->certification_notification($type, $touser, $USER, $certificationinstance);
                        if (!empty($certificationcourses)) {
                            foreach ($certificationcourses as $course) {
                                //$instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol' => 'certification'), '*', MUST_EXIST);
                                //if (!empty($instance)) {
                                //    $enrolcertification->enrol_user($instance, $newtrainer,editingteacher, time());
                                //    
                                //}
                                $enrolcertificationuser = $this->manage_certification_course_enrolments($course, $newtrainer,'editingteacher', 'enrol');
                            }
                        }
                    }
                }
            }
            break;
            case 'update':
            break;
            case 'delete';
                if(!empty($trainers)){
                      $toremove_trainers = array_diff($certification_trainers, $trainers);
                }else{
                     $toremove_trainers = $certification_trainers;
                }
               $type = 'certification_unenroll';
                $dataobj = $certificationid;
                $fromuserid = $USER->id;
                $string = 'trainer';
                if (!empty($toremove_trainers)) {
                    list($remove_trainerscondition, $toremove_trainersparams) = $DB->get_in_or_equal($toremove_trainers);
                 
                   
                        foreach($toremove_trainers as $toremove_trainer) {
                            $certification_status = $DB->get_field('local_certification','status',array('id' => $certificationid));
                             if (!empty($certificationcourses)) {
                                foreach ($certificationcourses as $course) {
                                    //$instance = $DB->get_record('enrol', array('courseid' => $course, 'enrol' => 'certification'), '*', MUST_EXIST);
                                    //$enrolcertification->unenrol_user($instance, $toremove_trainer, $teacherroleid, time());
                                    
                                    $enrolcertificationuser = $this->manage_certification_course_enrolments($course, $toremove_trainer,'editingteacher', 'unenrol');
                                }
                            }
                            $feedbackid=$DB->get_field('local_certification_trainers','feedback_id',array('trainerid'=>$toremove_trainer,'certificationid'=>$certificationid));
                        // print_object($feedbackid);exit;
                            if($feedbackid>0){
                                 require_once($CFG->dirroot . '/local/evaluation/lib.php');
                                
                                evaluation_delete_instance($feedbackid);
                            }
                            $DB->execute('UPDATE {local_certification_sessions} SET trainerid =0,timemodified = ' . time() . ',
                            usermodified = ' . $USER->id . ' WHERE certificationid = ' .
                            $certificationid. ' AND trainerid='.$toremove_trainer.'');
                            if($certification_status!=0){
                                // $emaillogs = new certificatenotifications_emails();
                                // $email_logs = $emaillogs->certificate_emaillogs($type,$dataobj,$toremove_trainer,$fromuserid,$string);
                                $emaillogs = new \local_certification\notification();
                                $touser = \core_user::get_user($toremove_trainer);
                                $certificationinstance = $DB->get_record('local_certification', array('id' => $dataobj));
                                $email_logs = $emaillogs->certification_notification($type, $touser, $USER, $certificationinstance);
                            }
                        }
                    
                    $DB->delete_records_select('local_certification_trainers', " certificationid = $certificationid AND trainerid $remove_trainerscondition  ", $toremove_trainersparams);
                }
            break;
            case 'all':
                $this->manage_certification_trainers($certificationid, 'insert', $trainers);
                $this->manage_certification_trainers($certificationid, 'update', $trainers);
                $this->manage_certification_trainers($certificationid, 'delete', $trainers);
            break;
            case 'default':
            break;
        }
        return true;
    }
    /**
     * [certification_misc description]
     * @method certification_misc
     * @param  [type]         $certification [description]
     * @return [type]                    [description]
     */
    public function certification_misc($certification){
        global $DB;
        if ($certification->id > 0) {
            $systemcontext = context_system::instance();
            $certification->description = $certification->cr_description['text'];
            $certification->certificationlogo = $certification->certificationlogo;
            file_save_draft_area_files($certification->certificationlogo, $systemcontext->id, 'local_certification', 'certificationlogo', $certification->certificationlogo);
            $DB->update_record('local_certification', $certification);
           // $params = array(
           //    'context' => context_system::instance(),
           //    'objectid' => $certification->id
           // );
           //
           //$event = \local_certification\event\certification_updated::create($params);
           //$event->add_record_snapshot('local_certification', $certification->id);
           //$event->trigger();
        }
        return $certification->id;
    }
        // OL-1042 Add Target Audience to Certifications//
    public function target_audience($certification){
        global $DB;
        if ($certification->id > 0) {
            $certification->open_group =(!empty($certification->open_group)) ? implode(',',array_filter($certification->open_group)) :NULL;
            $certification->open_hrmsrole =(!empty($certification->open_hrmsrole)) ? implode(',',array_filter($certification->open_hrmsrole)) :NULL;
            if(!empty($certification->open_hrmsrole)) {
                $certification->open_hrmsrole = $certification->open_hrmsrole;
            } else {
                $certification->open_hrmsrole = NULL;
            }
            $certification->open_designation =(!empty($certification->open_designation)) ? implode(',',array_filter($certification->open_designation)) :NULL;
            if(!empty($certification->open_designation)) {
                $certification->open_designation = $certification->open_designation;
            } else {
                $certification->open_designation = NULL;
            }
            $certification->open_location =(!empty($certification->open_location)) ? implode(',',array_filter($certification->open_location)) :NULL;
            if(!empty($certification->open_location)) {
                $certification->open_location = $certification->open_location;
            } else {
                $certification->open_location = NULL;
            }
          
            if(is_array ($certification->department)){
            
                $certification->department = !empty($certification->department) ? implode(',', $certification->department) : -1;
            }else{
                 $certification->department = !empty($certification->department) ? $certification->department : -1;
            }
            if(is_array ($certification->subdepartment)){
            
                $certification->subdepartment = !empty($certification->subdepartment) ? implode(',', $certification->subdepartment) : -1;
            }else{
                 $certification->subdepartment = !empty($certification->subdepartment) ? $certification->subdepartment : -1;
            }
          $DB->update_record('local_certification', $certification);
           // $params = array(
           //    'context' => context_system::instance(),
           //    'objectid' => $certification->id
           // );
           //
           //$event = \local_certification\event\certification_updated::create($params);
           //$event->add_record_snapshot('local_certification', $certification->id);
           //$event->trigger();
        }
        return $certification->id;
    }
    // OL-1042 Add Target Audience to Certifications//
    /**
     * [certification_logo description]
     * @method certification_logo
     * @param  integer        $certificationlogo [description]
     * @return [type]                        [description]
     */
    public function certification_logo($certificationlogo = 0) {
        global $DB;
        $certificationlogourl = false;
        if ($certificationlogo > 0){
            $sql = "SELECT * FROM {files} WHERE itemid = $certificationlogo AND filename != '.' ORDER BY id DESC ";//LIMIT 1
            $certificationlogorecord = $DB->get_record_sql($sql);
        }
        if (!empty($certificationlogorecord)){
          if($certificationlogorecord->filearea=="certificationlogo"){
            $certificationlogourl = moodle_url::make_pluginfile_url($certificationlogorecord->contextid, $certificationlogorecord->component, $certificationlogorecord->filearea, $certificationlogorecord->itemid, $certificationlogorecord->filepath, $certificationlogorecord->filename);
          }
        }
        return $certificationlogourl;
    }
    /**
     * [manage_certification_courses description]
     * @method manage_certification_courses
     * @param  [type]                   $courses [description]
     * @return [type]                            [description]
     */
    public function manage_certification_courses($courses) {
        global $DB, $USER;
        $certificationtrainers = $DB->get_records_menu('local_certification_trainers', array('certificationid' => $courses->certificationid), 'trainerid', 'id, trainerid');
        $certificationusers = $DB->get_records_menu('local_certification_users',
                    array('certificationid' => $courses->certificationid), 'userid', 'id, userid');
        foreach($courses->course as $course) {
            $certificationcourseexists = $DB->record_exists('local_certification_courses', array('certificationid' => $courses->certificationid, 'courseid' => $course));
            if (!empty($certificationcourseexists)) {
                continue;
            }
            $certificationcourse = new stdClass();
            $certificationcourse->certificationid = $courses->certificationid;
            $certificationcourse->courseid = $course;
            $certificationcourse->timecreated = time();
            $certificationcourse->usercreated = $USER->id;
            $certificationcourse->id = $DB->insert_record('local_certification_courses', $certificationcourse);
            $params = array(
                   'context' => context_system::instance(),
                   'objectid' => $certificationcourse->id
            );
           
               $event = \local_certification\event\certification_courses_created::create($params);
               $event->add_record_snapshot('local_certification', $courses->certificationid);
               $event->trigger();
            if ($certificationcourse->id) {
                foreach($certificationtrainers as $certificationtrainer) {
                    $this->manage_certification_course_enrolments($course, $certificationtrainer, 'editingteacher', 'enrol');
                }
                foreach ($certificationusers as $certificationuser) {
                        $unenrolcertificationuser = $this->manage_certification_course_enrolments($course, $certificationuser,
                            'employee', 'enrol');
                }
            }
        }
        return true;
    }
    /**
     * [manage_certification_course_enrolments description]
     * @method manage_certification_course_enrolments
     * @param  [type]                             $cousre        [description]
     * @param  [type]                             $user          [description]
     * @param  string                             $roleshortname [description]
     * @param  string                             $type          [description]
     * @param  string                             $pluginname    [description]
     * @return [type]                                            [description]
     */
    public function manage_certification_course_enrolments($cousre, $user, $roleshortname = 'employee', $type = 'enrol', $pluginname = 'certification') {
        global $DB;

        $courseexist=$DB->record_exists('enrol', array('courseid' => $cousre, 'enrol' => $pluginname));
        if($courseexist){   
            $enrolmethod = enrol_get_plugin($pluginname);
            $roleid = $DB->get_field('role', 'id', array('shortname' => $roleshortname));
            $instance = $DB->get_record('enrol', array('courseid' => $cousre, 'enrol' => $pluginname), '*', MUST_EXIST);
            if (!empty($instance)) {
                if ($type == 'enrol') {
                    $enrolmethod->enrol_user($instance, $user, $roleid, time());
                } else if ($type == 'unenrol'){
                    $enrolmethod->unenrol_user($instance, $user,$roleid, time());
                }
            }
        }
        return true;
    }
    /**
     * [certification_courses description]
     * @method certification_courses
     * @param  [type]            $certificationid [description]
     * @return [type]                         [description]
     */
    public function certification_courses($certificationid, $stable) {
        global $DB, $USER;
        $params = array();
        $certificationcourses = array();
        $concatsql = '';
        if (!empty($stable->search)) {
            $fields =array (0=>'c.fullname'
							);
				$fields = implode(" LIKE '%" .$stable->search. "%' OR ", $fields);
				$fields .= " LIKE '%" .$stable->search. "%' ";
                $concatsql .= " AND ($fields) ";
        }
        $countsql = "SELECT COUNT(cc.id) ";
        $fromsql = "SELECT c.*, cc.id as certificationcourseinstance ";
        $sql =" FROM {course} AS c
                                  JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                                  JOIN {local_certification_courses} AS cc ON cc.courseid = c.id
                                  WHERE cc.certificationid = :certificationid ";
        $params['certificationid'] = $certificationid;
        $sql .= $concatsql;
        try {
            $certificationcoursescount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY cc.id ASC";
                $certificationcourses = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $certificationcoursescount = 0;
        }
        return compact('certificationcourses', 'certificationcoursescount');
    }
    /**
     * [update_certification_status description]
     * @method update_certification_status
     * @param  [type]                  $certificationid     [description]
     * @param  [type]                  $certificationstatus [description]
     * @return [type]                                   [description]
     */
    public function update_certification_status($certificationid, $certificationstatus) {
        global $DB, $USER;
        $certification = new stdClass();
        $certification->id = $certificationid;
        $certification->status = $certificationstatus;
        if($certificationstatus == CERTIFICATION_COMPLETED) {
            $activeusers = $DB->count_records('local_certification_users', array('certificationid' => $certificationid, 'completion_status' => 1));
            $certification->activeusers = $activeusers;
            $totalusers = $DB->count_records('local_certification_users', array('certificationid' => $certificationid));
            $certification->totalusers = $totalusers;
            $activesessions = $DB->count_records('local_certification_sessions', array('certificationid' => $certificationid, 'attendance_status' => 1));
            $certification->activesessions = $activesessions;
            $totalsessions = $DB->count_records('local_certification_sessions', array('certificationid' => $certificationid));
            $certification->totalsessions = $totalsessions;
        }
        $certification->usermodified = $USER->id;
        $certification->timemodified = time();
        $certification->completiondate = time();
        try {
            $DB->update_record('local_certification', $certification);
           //  $params = array(
           //     'context' => context_system::instance(),
           //     'objectid' => $certification->id
           //  );
       
           // $event = \local_certification\event\certification_updated::create($params);
           // $event->add_record_snapshot('local_certification', $certification->id);
           // $event->trigger();
        } catch (dml_exception $ex) {
            print_error($ex);
        }
        return true;
    }
    /**
     * [certificationusers description]
     * @method certificationusers
     * @param  [type]         $certificationid [description]
     * @param  [type]         $stable      [description]
     * @return [type]                      [description]
     */
    public function certificationusers($certificationid, $stable) {
        global $DB, $USER;
        $params = array();
        $certificationusers = array();
        $concatsql = '';
        if (!empty($stable->search)) {
            $fields =array (0=>'u.firstname',
									1=>'u.lastname',
                                    2=>'u.email',
									3=>'u.idnumber'
							);
				$fields = implode(" LIKE '%" .$stable->search. "%' OR ", $fields);
				$fields .= " LIKE '%" .$stable->search. "%' ";
                $concatsql .= " AND ($fields) ";
        }
        $countsql = "SELECT COUNT(DISTINCT(u.id)) ";
        $fromsql = "SELECT u.*, cu.attended_sessions, cu.hours, cu.completion_status, c.totalsessions, c.activesessions,c.status";
        $sql = " FROM {user} AS u
                 JOIN {local_certification_users} AS cu ON cu.userid = u.id
                 JOIN {local_certification} AS c ON c.id = cu.certificationid
                WHERE c.id = :certificationid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                 $params['certificationid'] = $certificationid;
        if ((has_capability('local/certification:managecertification', context_system::instance())) && (!is_siteadmin()
            && (!has_capability('local/certification:manage_multiorganizations', context_system::instance())
                && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
            $condition            = " AND (u.open_costcenterid = :costcenter)";
            $params['costcenter'] = $USER->open_costcenterid;
            if ((has_capability('local/certification:manage_owndepartments', context_system::instance())
                 || has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                $condition .= " AND (u.open_departmentid = :department )";
                $params['department'] = $USER->open_departmentid;
            }
            if (has_capability('local/certification:trainer_viewcertification', context_system::instance())) {
                 $condition="";
            }
            $sql .= $condition;
        }       
        $sql .= $concatsql;

        try {
            $certificationuserscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY id ASC";
                $certificationusers = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $certificationuserscount = 0;
        }
        return compact('certificationusers', 'certificationuserscount');
    }
    /**
     * [certification_completions description]
     * @method certification_completions
     * @param  [type]                $certificationid [description]
     * @return [type]                             [description]
     */
    public function certification_completions($certificationid){
        global $DB, $USER, $CFG;
        require_once($CFG->libdir.'/completionlib.php');
        require_once($CFG->dirroot.'/completion/criteria/completion_criteria_role.php');
        $certificationuserssql = "SELECT cu.*
                                FROM {user} AS u
                                JOIN {local_certification_users} AS cu ON cu.userid = u.id
                                WHERE u.id > 2 AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND cu.certificationid = $certificationid";
        $certificationusers = $DB->get_records_sql($certificationuserssql);
        
        $certification_completiondata = $DB->get_record('local_certificatn_completion', array('certificationid' => $certificationid));
        
        $totalsessionssql="SELECT count(id) as total
                                        FROM {local_certification_sessions}
                                        WHERE certificationid = $certificationid ";
        if(!empty($certification_completiondata)&&$certification_completiondata->sessiontracking=="OR"&&$certification_completiondata->sessionids!=null){
             $totalsessionssql.=" AND id in ($certification_completiondata->sessionids)";
        }
        $totalsessions = $DB->count_records_sql($totalsessionssql);
        
        $certificationcoursessql = "SELECT c.*
                                  FROM {course} AS c
                                  JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                                  JOIN {local_certification_courses} AS cc ON cc.courseid = c.id
                                 WHERE cc.certificationid = $certificationid";
       
        if(!empty($certification_completiondata)&&$certification_completiondata->coursetracking=="OR"&&$certification_completiondata->courseids!=null){
             $certificationcoursessql.=" AND cc.courseid in ($certification_completiondata->courseids)";
        }
        $certificationcourses = $DB->get_records_sql($certificationcoursessql);
        
        
        if(!empty($certificationusers)) {
            foreach($certificationusers as $certificationuser){
                $usercousrecompletionstatus =array();
               
                foreach($certificationcourses as $certificationcourse) {
                    $params = array(
                        'userid'    => $certificationuser->userid,
                        'course'    => $certificationcourse->id
                    );
                    $ccompletion = new completion_completion($params);
                    
                    $ccompletionis_complete =  $ccompletion->is_complete();

                    if ($ccompletionis_complete) {
                        $usercousrecompletionstatus[]= true;
                    }
                }
                // print_object($usercousrecompletionstatus);
                if(empty($certification_completiondata)||($certification_completiondata->sessiontracking==null&&$certification_completiondata->coursetracking==null)){
                    
                    if (($certificationuser->attended_sessions == $totalsessions) && (count($usercousrecompletionstatus)==count($certificationcourses))) {
                        $certificationuser->completion_status = 1;
                    } else {
                        $certificationuser->completion_status = 0;
                    }
                }else{
                    
                    $certificationuser->completion_status = 0;
                    
                    $attended_sessions_sql="SELECT count(id) as total FROM {local_certificatn_attendance} where certificationid=$certificationid and userid=$certificationuser->userid and status=1 ";
                     
                    if(!empty($certification_completiondata)&&$certification_completiondata->sessiontracking=="OR"&&$certification_completiondata->sessionids!=null){
                     
                        $attended_sessions_sql.=" AND sessionid in ($certification_completiondata->sessionids)";
                    }
                    
                    $attended_sessions = $DB->count_records_sql($attended_sessions_sql);
                    
                    if (($attended_sessions == $totalsessions &&$certification_completiondata->sessiontracking=="AND")) {
                       
                        $certificationuser->completion_status = 1;
                    }
                    if (($attended_sessions <= $totalsessions &&$attended_sessions!=0&&$certification_completiondata->sessiontracking=="OR")) {
                       
                        $certificationuser->completion_status = 1;
                    }
                  
                    if (count($usercousrecompletionstatus)==count($certificationcourses)&&$certification_completiondata->coursetracking=="AND") {
                         
                         if (($attended_sessions == $totalsessions &&$certification_completiondata->sessiontracking=="AND")) {
                            
                                $certificationuser->completion_status = 1;
                         }
                         if (($attended_sessions <= $totalsessions &&$attended_sessions!=0&&$certification_completiondata->sessiontracking=="OR")) {
                             
                             $certificationuser->completion_status = 1;
                         }
                         if ($certification_completiondata->sessiontracking==null){
                            
                             $certificationuser->completion_status = 1;
                         }
                    }elseif($certification_completiondata->coursetracking=="AND") {
                         
                        $certificationuser->completion_status = 0;
                    }
                    
                   if (count($usercousrecompletionstatus)<=count($certificationcourses)&&count($usercousrecompletionstatus)!=0&&$certification_completiondata->coursetracking=="OR") {
                   
                        if (($attended_sessions == $totalsessions &&$certification_completiondata->sessiontracking=="AND")) {
                         
                                $certificationuser->completion_status = 1;
                         }
                         if (($attended_sessions <= $totalsessions &&$attended_sessions!=0&&$certification_completiondata->sessiontracking=="OR")) {
                         
                             $certificationuser->completion_status = 1;
                         }
                         if ($certification_completiondata->sessiontracking==null){
                     
                             $certificationuser->completion_status = 1;
                         }
                    }elseif($certification_completiondata->coursetracking=="OR") {
                        
                        $certificationuser->completion_status = 0;
                    }  
                    //print_object($certificationuser);
                }
               
                $certificationuser->usermodified = $USER->id;
                $certificationuser->timemodified = time();
                $certificationuser->completiondate = time();
                $DB->update_record('local_certification_users', $certificationuser);
                $params = array(
                    'context' => context_system::instance(),
                    'objectid' => $certificationuser->id
                );
                
                $event = \local_certification\event\certification_users_updated::create($params);
                $event->add_record_snapshot('local_certification', $certificationid);
                $event->trigger();
            }
        }
        //exit;
        return true;
    }
    public function certificationcategories($formdata){
        global $DB;
        if($formdata->id){
            $DB->update_record('local_certification_categories',$formdata);
        }else{
            $DB->insert_record('local_certification_categories',$formdata);
        }
    }
    //******************==================Raju Tummoji=====================================****************//
    /**
     * [select_to_and_from_users description]
     * @param  [type]  $type       [description]
     * @param  integer $clasroomid [description]
     * @param  [type]  $params     [description]
     * @param  integer $total      [description]
     * @param  integer $offset1    [description]
     * @param  integer $perpage    [description]
     * @param  integer $lastitem   [description]
     * @return [type]              [description]
     */
    public function select_to_and_from_users($type=null,$clasroomid=0,$params,$total=0,$offset1=-1,$perpage=-1,$lastitem=0){

        global $DB,$USER;
        $certification = $DB->get_record('local_certification', array('id' => $clasroomid));

        $params['suspended'] = 0;
        $params['deleted'] = 0;

            if($total==0){
                 $sql = "SELECT u.id,concat(u.firstname,' ',u.lastname,' ','(',u.email,')') as fullname";
            }else{
                $sql = "SELECT count(u.id) as total";
            }
             $sql.=" FROM {user} AS u
                                WHERE  u.id > 2 AND u.suspended = :suspended
                                     AND u.deleted = :deleted ";
            // OL-1042 Add Target Audience to Certifications//                         
            //if(!empty($certification->open_group)){
            //    $group_list = $DB->get_records_sql_menu("select cm.id, cm.userid from {cohort_members} cm, {user} u where u.id = cm.userid AND u.deleted = 0 AND u.suspended = 0 AND cm.cohortid IN ({$certification->open_group})");
            //     
            //     $groups_members = implode(',', $group_list);
            //     if (!empty($groups_members))
            //     $sql .=" AND u.id IN ({$groups_members})";
            //     else
            //     $sql .=" AND u.id =0";
            //     
            //}                         
            //if(!empty($certification->open_hrmsrole)){
            //    $sql .= " AND u.open_hrmsrole IN (:roleinfo)";
            //    $params['roleinfo'] = $certification->open_hrmsrole;
            //}
            //if(!empty($certification->open_designation)){
            //    $sql .= " AND u.open_designation IN (:designationinfo)";
            //    $params['designationinfo'] = $certification->open_designation;
            //}
            //if(!empty($certification->open_location)){
            //    $sql .= " AND u.city IN (:locationinfo)";
            //    $params['locationinfo'] = $certification->open_location;
            //}
            // OL-1042 Add Target Audience to Certifications//                         
            if($lastitem!=0){
                $sql.=" AND u.id > $lastitem";
             }
           if ((has_capability('local/certification:managecertification', context_system::instance())) && ( !is_siteadmin() && (!has_capability('local/certification:manage_multiorganizations', context_system::instance()) && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())))) {
                $sql .= " AND u.open_costcenterid = :costcenter";
                $params['costcenter'] = $USER->open_costcenterid;
                if ((has_capability('local/certification:manage_owndepartments', context_system::instance())|| has_capability('local/costcenter:manage_owndepartments', context_system::instance()))) {
                    $sql .= " AND u.open_departmentid = :department";
                    $params['department'] = $USER->open_departmentid;
                 }
           }
            $sql .=" AND u.id <> $USER->id ";
            if (!empty($params['email'])) {
                $sql.=" AND u.id IN ({$params['email']})";
            }
            if (!empty($params['uname'])) {
                $sql .=" AND u.id IN ({$params['uname']})";
            }
            if (!empty($params['department'])) {
                $sql .=" AND u.open_departmentid IN ({$params['department']})";
            }
            if (!empty($params['organization'])) {
                $sql .=" AND u.open_costcenterid IN ({$params['organization']})";
            }
            if (!empty($params['idnumber'])) {
                $sql .=" AND u.id IN ({$params['idnumber']})";
            }
            
            if (!empty($params['groups'])) {

                 $sql .=" AND u.id IN (select cm.userid from {cohort_members} cm, {user} u where u.id = cm.userid AND u.deleted = 0 AND u.suspended = 0 AND cm.cohortid IN ({$params['groups']}))";
     
            }
            if ($type=='add') {
                $sql .= " AND u.id NOT IN (SELECT lcu.userid as userid
                                       FROM {local_certification_users} AS lcu
                                       WHERE lcu.certificationid = $clasroomid )";
            }elseif ($type=='remove') {
  
                $sql .= " AND u.id IN (SELECT lcu.userid as userid
                                       FROM {local_certification_users} AS lcu
                                       WHERE lcu.certificationid = $clasroomid )";
            }

            $sql .= " AND u.id NOT IN (SELECT lcu.trainerid as userid
                                       FROM {local_certification_trainers} AS lcu
                                       WHERE lcu.certificationid = $clasroomid)";
            
            $order = ' ORDER BY u.id ASC ';
            if($perpage!=-1){
                // $order.="LIMIT $perpage";
                $limits = $perpage;
            }else{
                $limits = 0;
            }

            if($total==0){
                $availableusers = $DB->get_records_sql_menu($sql .$order,$params, 0, $limits);
            }else{
                $availableusers = $DB->count_records_sql($sql,$params);
            }
           return $availableusers;
    }
    /**
     * [certification_capacity_check description]
     * @param  [type] $certificationid [description]
     * @return [type]              [description]
     */
    public function certification_capacity_check($certificationid){
                global $DB;
                $return =false;
                $certification_capacity=$DB->get_field('local_certification','capacity',array('id'=>$certificationid));
                $enrolled_users=$DB->count_records('local_certification_users',array('certificationid'=>$certificationid));
                //if($certification_capacity <= $enrolled_users){
                //    $return =true;
                //}
                if($certification_capacity <= $enrolled_users && !empty($certification_capacity) && $certification_capacity!=0){
                    $return =true;
                }
                return $return;
    }
    /**
     * [manage_certification_automatic_sessions description]
     * @param  [type] $certificationid        [description]
     * @param  [type] $certificationstartdate [description]
     * @param  [type] $certificationenddate   [description]
     * @return [type]                     [description]
     */
    public function manage_certification_automatic_sessions($certificationid,$certificationstartdate,$certificationenddate) {
        global $DB;
            
            $i=1;

            $start_hours_minuates="09:00:00";
            $finish_hours_minuates="18:00:00";

            $first_time= \local_costcenter\lib::get_userdate("H:i",$certificationstartdate);
          
            if($first_time>=$finish_hours_minuates){
                $certificationstartdate=strtotime('+1 day',strtotime(date("d/m/Y",$certificationstartdate)));
                $certificationstartdate=strtotime(date('d/m/Y',$certificationstartdate).' '. $start_hours_minuates);
               
            }

            $last_time=date("H:i",$certificationenddate);
            if($last_time<$start_hours_minuates){
                $certificationenddate=strtotime('-1 day',strtotime(date("d/m/Y",$certificationenddate)));
                $certificationenddate=strtotime(date('d/m/Y',$certificationenddate).' '. $finish_hours_minuates);
               
            }

            $first=strtotime(date("d/m/Y",$certificationstartdate));
            

            $last=strtotime(date("d/m/Y",$certificationenddate));

         
            while( $first <= $last ) {  
                $session=new stdClass();
                $session->id=0;
                $session->datetimeknown=1;
                $session->certificationid=$certificationid;
                $session->mincapacity=0;
                $session->onlinesession=0;
                $session->roomid=0;
                $session->trainerid=$DB->get_field('local_certification_trainers','trainerid',array('certificationid'=>$certificationid));
                $session->cs_description=array
                                        (
                                        'text' =>"", 
                                        'format' =>1
                                        );

                $date= \local_costcenter\lib::get_userdate('d/m/Y', $first);

                $session->name="Session$i";

                $session->timestart=strtotime($date.' '.$start_hours_minuates);


                $session->timefinish=strtotime($date.' '.$finish_hours_minuates);
                
                if($first==$last){

                     $session->timefinish=strtotime($date.' '.date("H:i",$certificationenddate));
                } 
                

                $condition = strtotime('+1 day', $first );

                if($i==1){

                 $session->timestart=strtotime($date.' '.date("H:i",$certificationstartdate));

                }elseif($condition > $last){

                   $session->timefinish=strtotime($date.' '.date("H:i",$certificationenddate));
                }


                $this->manage_certification_sessions($session);
                $first = strtotime('+1 day', $first );
                $i++;
 
            }
    }
    /**
     * [function to get user enrolled certifications count]
     * @param  [INT] $userid [id of the user]
     * @return [INT]         [count of the certifications enrolled]
     */
    public function enrol_get_users_certifications_count($userid){
        global $DB;
        $certification_sql = "SELECT count(id) FROM {local_certification_users} WHERE userid = :userid";
        $certification_count = $DB->count_records_sql($certification_sql, array('userid' => $userid));
        return $certification_count; 
    }
    /**
     * [function to get user enrolled certifications ]
     * @param  [int] $userid [id of the user]
     * @return [object]         [certifications object]
     */
    public function enrol_get_users_certifications($userid){
        global $DB;
        $certification_sql = "SELECT lc.id,lc.name,lc.description FROM {local_certification} AS lc 
        JOIN {local_certification_users} AS lcu ON lcu.certificationid = lc.id WHERE userid = :userid AND lc.status IN (1,4)";
        $certifications = $DB->get_records_sql($certification_sql, array('userid' => $userid));
        return $certifications;
    }
    public function certification_status_strip($certificationid,$certificationstatus){
        global $DB,$USER;
            $return="";
            $id = $DB->get_field('local_certification_users','id', array('certificationid'=>$certificationid,'userid'=>$USER->id)); 
            if(!$id && !is_siteadmin() && (!has_capability('local/certification:managecertification', context_system::instance()))){
              return $return;
            }
               switch($certificationstatus) {
                    case CERTIFICATION_NEW:
                    
                    $return=get_string('new_certification', 'local_certification');
                    
                    break;
                    case CERTIFICATION_ACTIVE:
                         $return=get_string('active_certification', 'local_certification');
                    break;
                    case CERTIFICATION_CANCEL:
                         $return=get_string('cancel_certification', 'local_certification');
                    break;
                    case CERTIFICATION_HOLD:
                         $return=get_string('hold_certification', 'local_certification');
                    break;
                    case CERTIFICATION_COMPLETED:
                         $return=get_string('completed_certification', 'local_certification');
                        if(!is_siteadmin() && (!has_capability('local/certification:managecertification', context_system::instance()))){
                            
                         $completion_status=$DB->get_field('local_certification_users','completion_status', array('certificationid'=>$certificationid,'userid'=>$USER->id));   
                          // $status_string=$completion_status == 1 ? 'completed' : 'Not completed';
                           
                          $return=$completion_status == 1 ? get_string('completed_certification', 'local_certification') : get_string('completed_user_certification', 'local_certification');
                     
                        }
                    break;    
               }
        return $return;     
    }
    public function certification_completion_settings_tab($certificationid,$preview=true){
         global $DB,$USER;
            $certification_completiondata = $DB->get_record('local_certificatn_completion', array('certificationid' => $certificationid));
        
            $sessionssql="SELECT id,name FROM {local_certification_sessions}
                                            WHERE certificationid = $certificationid ";
            if(!empty($certification_completiondata)&&$certification_completiondata->sessiontracking=="OR"&&            $certification_completiondata->sessionids!=null){
                
                 $sessionssql.=" AND id in ($certification_completiondata->sessionids)";
                 
            }
            $sessions = $DB->get_records_sql_menu($sessionssql);
            
            $certificationcoursessql = "SELECT c.id,fullname
                                  FROM {course} AS c
                                  JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                                  JOIN {local_certification_courses} AS cc ON cc.courseid = c.id
                                 WHERE cc.certificationid = {$certificationid}";
       
            if(!empty($certification_completiondata) && $certification_completiondata->coursetracking=="OR" && $certification_completiondata->courseids!=null){
                 $certificationcoursessql .= " AND cc.courseid in ({$certification_completiondata->courseids})";
            }
            $certificationcourses = $DB->get_records_sql_menu($certificationcoursessql);
            
            $return="";
             if (!empty($sessions)||!empty($certificationcourses)) {
                $table = new html_table();
                $table->head = array(get_string('courses', 'local_certification'), get_string('sessions', 'local_certification'));
                if(!empty($certificationcourses)){
                    $courses=implode(',',$certificationcourses);
                }else{
                    $courses=get_string('nocertificationcourses', 'local_certification') ;
                }
                 
                if(!empty($sessions)){
                    $session=implode(',',$sessions);
                }else{
                    $session=get_string('nosessions', 'local_certification') ;
                }
                $table->data = array(array($courses,$session));
                $table->id = 'viewcertificationcompletion_settings_tab';
                $table->attributes['data-certificationid'] = $certificationid;
                $table->align = array('center', 'center');
                
           
               
                $return =html_writer::table($table);
             }
                
             if(empty($certification_completiondata)||($certification_completiondata->sessiontracking==null&&$certification_completiondata->coursetracking==null)){
                    $table = new html_table();
                 $sessiontracking=$coursetracking="";   
                 if (!empty($sessions)) {
                       $sessiontracking="_allsessions";
                       $table->data [0][1]=$session;
                 }
                 if (!empty($certificationcourses)) {
                        $coursetracking="_allcourses";
                        $table->data [0][0]=$courses;
                 }
                 $completion_tab=get_string('certification_completion_tab_info'.$sessiontracking.$coursetracking.'', 'local_certification');
                  
             }else{
                    $sessiontracking=$coursetracking="";
                    
                    if ($certification_completiondata->sessiontracking=="AND" &&!empty($sessions)) {
                       $sessiontracking="_allsessions";
                        $table->data [0][1]=$session;
                    }
                    if ($certification_completiondata->sessiontracking=="OR" &&!empty($sessions)) {
                       $sessiontracking="_anysessions";
                        $table->data [0][1]=$session;
                    }
                    if ($certification_completiondata->coursetracking=="AND" &&!empty($certificationcourses)) {
                        $coursetracking="_allcourses";
                        $table->data [0][0]=$courses;
                    }
                    if ($certification_completiondata->coursetracking=="OR" &&!empty($certificationcourses)) {
                        $coursetracking="_anycourses";
                        $table->data [0][0]=$courses;
                    }
                    
                    $completion_tab=get_string('certification_completion_tab_info'.$sessiontracking.$coursetracking.'', 'local_certification');
             }
             if($preview){
                 $return =html_writer::table($table);
                 return "<div class='alert alert-info'>".$completion_tab."</div>".$return;
             }else{
                return $completion_tab;
             }
    }
    public function certificationtarget_audience_tab($certificationid){
         global $DB,$USER;
            $data = $DB->get_record_sql('SELECT id, open_group, open_hrmsrole,
             open_designation, open_location,department, subdepartment
             FROM {local_certification} WHERE id = ' .$certificationid);
            
            if($data->department==-1||$data->department==null){
                $department=get_string('audience_department','local_certification','All');
            }else{
                 // $departments = $DB->get_field_sql("SELECT GROUP_CONCAT(fullname)  FROM {local_costcenter} WHERE id IN ($data->department)");
                $departments = $DB->get_fieldset_sql("SELECT fullname FROM {local_costcenter} WHERE id IN (:department)",  array ('department' => $data->department));
                $departments = implode(', ', $departments);
                 $department=get_string('audience_department','local_certification',$departments);
            }
            if($data->subdepartment == -1 || $data->subdepartment == null){
                $subdepartment = get_string('audience_subdepartment','local_certification','All');
            }else{
                $subdepartments = $DB->get_fieldset_sql("SELECT fullname FROM {local_costcenter} WHERE id IN (:subdepartment)",  array ('subdepartment' => $data->subdepartment));
                $subdepartments = implode(', ', $subdepartments);
                $subdepartment = get_string('audience_subdepartment','local_certification',$subdepartments);
            }
            if(empty($data->open_group)){
                 $group=get_string('audience_group','local_certification','All');
            }else{
                 // $groups = $DB->get_field_sql("SELECT GROUP_CONCAT(name) FROM {cohort} WHERE id IN ($data->open_group)");
                 $groups = $DB->get_fieldset_sql("SELECT name FROM {cohort} WHERE id IN (:groups)", array('groups' => $data->open_group));
                 $groups = implode(', ', $groups);
                 $group=get_string('audience_group','local_certification',$groups);
            }
            
            $data->open_hrmsrole =(!empty($data->open_hrmsrole)) ? $hrmsrole=get_string('audience_hrmsrole','local_certification',$data->open_hrmsrole) :$hrmsrole=get_string('audience_hrmsrole','local_certification','All');
            
            $data->open_designation =(!empty($data->open_designation)) ? $designation=get_string('audience_designation','local_certification',$data->open_designation) :$designation=get_string('audience_designation','local_certification','All');
            
            $data->open_location =(!empty($data->open_location)) ? $location=get_string('audience_location','local_certification',$data->open_location) :$location=get_string('audience_location','local_certification','All');
            
             return $department.$subdepartment.$group.$hrmsrole.$designation.$location;
    }
    public function send_certification_completions(){
        global $DB,$USER,$CFG;
        
        require_once($CFG->libdir.'/completionlib.php');
        require_once($CFG->dirroot.'/completion/criteria/completion_criteria_role.php');
        
        $prevdaytime = time() - 86400;
        $main_sql = "SELECT lpu.id,lpu.userid,lpu.certificationid 
            FROM {course_completions} as cc 
            JOIN  {local_certification_courses} as lpc ON lpc.courseid=cc.course 
            JOIN {local_certification_users} as lpu ON lpu.certificationid=lpc.certificationid AND lpu.userid=cc.userid 
            JOIN {local_certification} as lp on lp.id=lpu.certificationid 
            WHERE cc.timecompleted > {$prevdaytime}
            AND (SELECT count(id) as total
                FROM {local_certification_sessions}
                WHERE certificationid = lpu.certificationid ) = 0 and lp.status=4";
                // unix_timestamp(CURRENT_TIMESTAMP - INTERVAL 24 HOUR)
        $records=$DB->get_records_sql($main_sql);
        foreach($records as $record){
            
            $certificationid=$record->certificationid;
            
            $certification_completiondata = $DB->get_record('local_certificatn_completion', array('certificationid' => $certificationid));
            
            $certificationcoursessql = "SELECT c.id
                                    FROM {course} AS c
                                    JOIN {enrol} AS en on en.courseid=c.id and en.enrol='certification' and en.status=0
                                    JOIN {local_certification_courses} AS cc ON cc.courseid = c.id
                                    WHERE cc.certificationid = {$certificationid} ";
            
            if(!empty($certification_completiondata)&&$certification_completiondata->coursetracking=="OR"&&$certification_completiondata->courseids!=null){
                $certificationcoursessql.=" AND cc.courseid in ({$certification_completiondata->courseids})";
            }
            $certificationcourses = $DB->get_records_sql($certificationcoursessql);

            $usercousrecompletionstatus =array();
            
            foreach($certificationcourses as $certificationcourse) {
                $params = array(
                    'userid'    => $record->userid,
                    'course'    => $certificationcourse->id
                );
                $ccompletion = new completion_completion($params);
                
                $ccompletionis_complete =  $ccompletion->is_complete();
                
                if ($ccompletionis_complete) {
                    $usercousrecompletionstatus[]= true;
                }
            }
            if(empty($certification_completiondata)||($certification_completiondata->coursetracking==null)){
            
                if ((count($usercousrecompletionstatus)==count($certificationcourses))) {
                    $completion_status = 1;
                } else {
                    $completion_status = 0;
                }
            }else{
            
                $completion_status = 0;
                
                if (count($usercousrecompletionstatus)==count($certificationcourses)&&$certification_completiondata->coursetracking=="AND") {
                
                    $completion_status = 1;
                
                }elseif($certification_completiondata->coursetracking=="AND") {
                
                    $completion_status = 0;
                }
                
                if (count($usercousrecompletionstatus)<=count($certificationcourses)&&count($usercousrecompletionstatus)!=0&&$certification_completiondata->coursetracking=="OR") {
                
                    $completion_status = 1;
                
                }elseif($certification_completiondata->coursetracking=="OR") {
                
                    $completion_status = 0;
                }  
            }
            $certificationuser=new stdClass();
            $certificationuser->id = $record->id;
            $certificationuser->usermodified = $USER->id;
            $certificationuser->timemodified = time();
            $certificationuser->completiondate = time();
            $certificationuser->completion_status = $completion_status;
            $DB->update_record('local_certificate_users', $certificationuser);

        }
   }
}