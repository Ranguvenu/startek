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
 */
defined('MOODLE_INTERNAL') || die();
function xmldb_local_program_install() {
    global $CFG, $DB;
 	$usertours = $CFG->dirroot . '/local/program/usertours/';
    $totalusertours = count(glob($usertours . '*.json'));
    $usertoursjson = glob($usertours . '*.json');
    $pluginmanager = new \tool_usertours\manager();
    for ($i = 0; $i < $totalusertours; $i++) {
        $importurl = $usertoursjson[$i];
        if (file_exists($usertoursjson[$i])
                && pathinfo($usertoursjson[$i], PATHINFO_EXTENSION) == 'json') {
            $data = file_get_contents($importurl);
            $tourconfig = json_decode($data);
            $tourexists = $DB->record_exists('tool_usertours_tours', array('name' => $tourconfig->name));
            if (!$tourexists) {
                $tour = $pluginmanager->import_tour_from_json($data);
            }
        }
    }
    /*notifictaions content*/
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table('local_notification_type');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('parent_module', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('pluginname', XMLDB_TYPE_CHAR, '255', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // $table->add_key('primary', XMLDB_KEY_FOREIGN, array('id'));
        $result = $dbman->create_table($table);
    }
    $table = new xmldb_table('local_notification_info');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('open_path', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('notificationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        
        $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        // $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, null, null, null);
        // courses
        $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('attach_certificate', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('completiondays', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('body', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('adminbody', XMLDB_TYPE_TEXT, null, null, null, null, '0');
        $table->add_field('attachment_filepath', XMLDB_TYPE_CHAR, null, null, null, null, '0');
        $table->add_field('status', XMLDB_TYPE_INTEGER, 10, null, null, null, '0');
        
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        //$table->add_key('foreign', XMLDB_KEY_FOREIGN, array('open_path'));
        // $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('notificationid'));
        // $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('notificationid'));
        $result = $dbman->create_table($table);
    }
    $table = new xmldb_table('local_emaillogs');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('notification_infoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('from_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('to_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        
        $table->add_field('from_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('to_emailid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        // $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('moduletype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moduleid', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('teammemberid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        // courses
        $table->add_field('reminderdays', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enable_cc', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('emailbody', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, '0');
        $table->add_field('adminbody', XMLDB_TYPE_TEXT, null, null, null, null, '0');
        $table->add_field('attachment_filepath', XMLDB_TYPE_CHAR, null, null, null, null, '0');

        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        $table->add_field('sent_date', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('sent_by', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('open_path'));
        // $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('notificationid'));
        // $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('notificationid'));
        $result = $dbman->create_table($table);
    }
    $table = new xmldb_table('local_notification_strings');
    if (!$dbman->table_exists($table)) {
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('module', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        // $table->add_field('pluginname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        // $table->add_key('primary', XMLDB_KEY_FOREIGN, array('id'));
        $result = $dbman->create_table($table);
    }
    $time = time();
    $initcontent = array('name' => 'Program','shortname' => 'program','parent_module' => '0','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program');
    $parentid = $DB->get_field('local_notification_type', 'id', array('shortname' => 'program'));
    if(!$parentid){
        $parentid = $DB->insert_record('local_notification_type', $initcontent);
    }
    $notification_type_data = array(
        array('name' => 'Program Enrollment','shortname' => 'program_enrol','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program'),
        array('name' => 'Program Unenrollment','shortname' => 'program_unenroll','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program'),
        array('name' => 'Program Completion','shortname' => 'program_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program'),
        array('name' => 'Program Level Completion','shortname' => 'program_level_completion','parent_module' => $parentid,'usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL, 'pluginname' => 'program')
    );
    foreach($notification_type_data as $notification_type){
        unset($notification_type['timecreated']);
        if(!$DB->record_exists('local_notification_type',  $notification_type)){
            $notification_type['timecreated'] = $time;
            $DB->insert_record('local_notification_type', $notification_type);
        }
    }
    $strings = array(
        array('name' => '[program_name]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_startdate]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_enddate]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_level]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_enroluserfulname]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_link]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_enroluseremail]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_completiondate]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_organization]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_course]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_creater]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_level_link]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_lc_course_link]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_lc_course_creater]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_level_creater]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_level_completiondate]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL),
        array('name' => '[program_lc_course_completiondate]','module' => 'program','usercreated' => '2','timecreated' => $time,'usermodified' => 2,'timemodified' => NULL)
    );
    foreach($strings as $string){
        unset($string['timecreated']);
        if(!$DB->record_exists('local_notification_strings', $string)){
            $string_obj = (object)$string;
            $string_obj->timecreated = $time;
            $DB->insert_record('local_notification_strings', $string_obj);
        }
    }
    $corecomponent = new \core_component();
    $pluginexist = $corecomponent::get_plugin_directory('tool', 'certificate');
    if($pluginexist){
    $table = new xmldb_table('local_program');
      if ($dbman->table_exists($table)) {
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
           if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
           }   
        }
    }
}