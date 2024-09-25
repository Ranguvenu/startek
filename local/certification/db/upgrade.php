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

function xmldb_local_certification_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2017050404) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('type', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', 'shortname');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050404, 'local', 'certification');
    }
    if ($oldversion < 2017050405) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('config', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050405, 'local', 'certification');
    }
    if ($oldversion < 2017050406) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('manage_approval', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('allow_multi_session', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        upgrade_plugin_savepoint(true, 2017050406, 'local', 'certification');
    }
    if ($oldversion < 2017050410) {
        $table = new xmldb_table('local_certification_courses');
        $field = new xmldb_field('course_duration', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050410, 'local', 'certification');
    }
    if ($oldversion < 2017050411) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('cr_category', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050411, 'local', 'certification');
    }
    if ($oldversion < 2017050413) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('nomination_startdate', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('nomination_enddate', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        upgrade_plugin_savepoint(true, 2017050413, 'local', 'certification');
    }
    if ($oldversion < 2017050415) {
        $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('timestart', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field1 = new xmldb_field('timefinish', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('sessiontimezone', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        $field3 = new xmldb_field('attendance_status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, '0', null);
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }
        upgrade_plugin_savepoint(true, 2017050415, 'local', 'certification');
    }
    if ($oldversion < 2017050417) {
        $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('certificationidid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'certificationid');
        }
        upgrade_plugin_savepoint(true, 2017050417, 'local', 'certification');
    }
    if ($oldversion < 2017050418) {
        $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('institueid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'instituteid');
        }
        upgrade_plugin_savepoint(true, 2017050418, 'local', 'certification');
    }
    if ($oldversion < 2017050419) {
        $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('capacity', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050419, 'local', 'certification');
    }
    if ($oldversion < 2017050421) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('department', XMLDB_TYPE_INTEGER, '10', XMLDB_NOTNULL, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050421, 'local', 'certification');
    }
    if ($oldversion < 2017050422) {
        $table = new xmldb_table('local_certification_signups');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'local_certificatn_attendance');
        }
        upgrade_plugin_savepoint(true, 2017050422, 'local', 'certification');
    }
    if ($oldversion < 2017050424) {
        $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050424, 'local', 'certification');
    }
    if ($oldversion < 2017050425) {
        $table = new xmldb_table('local_certificatn_trainerfb');
        $field = new xmldb_field('certificationidid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'certificationid');
        }
        upgrade_plugin_savepoint(true, 2017050425, 'local', 'certification');
    }
    if ($oldversion < 2017050430) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('certificationlogo', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050430, 'local', 'certification');
    }
    if ($oldversion < 2017050433) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('actualsessions', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'activesessions');
        }
        $field1 = new xmldb_field('attendees', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field1)) {
            $dbman->rename_field($table, $field1, 'activeusers');
        }
        $field2 = new xmldb_field('enrolled_users', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field2)) {
            $dbman->rename_field($table, $field2, 'totalusers');
        }
        $field3 = new xmldb_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', null);
        if ($dbman->field_exists($table, $field3)) {
            $dbman->rename_field($table, $field3, 'status');
        }
        upgrade_plugin_savepoint(true, 2017050433, 'local', 'certification');
    }
    if ($oldversion < 2017050436) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('department', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050436, 'local', 'certification');
    }
    if ($oldversion < 2017050439) {
        $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('moduletype', XMLDB_TYPE_CHAR, '250', null, null, null,null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('moduleid', XMLDB_TYPE_INTEGER, '10', null, null, null,'0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050439, 'local', 'certification');
    }
    if ($oldversion < 2017050441) {
         $table = new xmldb_table('local_certification_sessions');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '250', null, null, null,null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050441, 'local', 'certification');
    }
    if ($oldversion < 2017050444) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('completiondate',XMLDB_TYPE_INTEGER, '10', null, null, null,'0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $table = new xmldb_table('local_certification_users');
        $field = new xmldb_field('completiondate',XMLDB_TYPE_INTEGER, '10', null, null, null,'0', null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050444, 'local', 'certification');
    }
    if ($oldversion < 2017050448) {
            $table = new xmldb_table('local_certificatn_completion');
            if (!$dbman->table_exists($table)) {
               $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
               
               $table->add_field('certificationid', XMLDB_TYPE_INTEGER, '10', null, null, null);
               
               $table->add_field('sessiontracking',XMLDB_TYPE_CHAR, '225', null,null,null,"OR");
               
               $table->add_field('sessionids',XMLDB_TYPE_TEXT, 'big', null,null,null,NULL);
               
               $table->add_field('coursetracking',XMLDB_TYPE_CHAR, '225', null,null,null,"OR");
               
               $table->add_field('courseids',XMLDB_TYPE_TEXT, 'big', null,null,null,NULL);
          
               $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null);
               $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null,null, null,0);
               $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10',null,null, null,0);
               $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10',null,null, null,0);
               
             
                
               $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
               
               $dbman->create_table($table);
            }
         upgrade_plugin_savepoint(true, 2017050448, 'local', 'certification');
    }    
    // OL-1042 Add Target Audience to Certifications//
    if ($oldversion < 2017050455) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('open_group',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('open_hrmsrole',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('open_designation',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('open_location',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050455, 'local', 'certification');
    }
    // OL-1042 Add Target Audience to Certifications//
    if ($oldversion < 2017050456) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('approvalreqd', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050456, 'local', 'certification');
    }

    if ($oldversion < 2017050459) {
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('open_points', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050459, 'local', 'certification');
    }
    if($oldversion < 2017050459.03){
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('subdepartment', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2017050459.03, 'local', 'certification');   
    }

    if($oldversion < 2019101700.02){
        $table = new xmldb_table('local_certification');
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2019101700.02, 'local', 'certification');   
    }
    
    return true;
}