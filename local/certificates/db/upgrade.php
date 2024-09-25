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

function xmldb_local_certificates_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();

    if($oldversion < 2016120504.03){
        $table = new xmldb_table('course');
        $field = new xmldb_field('open_certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2016120504.03, 'local', 'certificates');   
    }

    if($oldversion < 2016120504.04){
        $table = new xmldb_table('local_classroom');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_learningplan');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_program');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('local_onlinetests');
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2016120504.04, 'local', 'certificates');   
    }


    if($oldversion < 2016120504.05){
        $table = new xmldb_table('local_certificate_pages');
        $field = new xmldb_field('certificatesize', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016120504.05, 'local', 'certificates');   
    }

    if($oldversion < 2016120504.06){

        $table = new xmldb_table('local_certificate');
        $field1 = new xmldb_field('open_path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        upgrade_plugin_savepoint(true, 2016120504.06, 'local', 'certificates');
    }

    return true;
}
