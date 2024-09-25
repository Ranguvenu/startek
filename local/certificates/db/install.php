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
function xmldb_local_certificates_install(){
	global $CFG,$DB,$USER;
	
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
	$table = new xmldb_table('course');
	if ($dbman->table_exists($table)) {
        $field = new xmldb_field('open_certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	}

    $table = new xmldb_table('local_classroom');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    $table = new xmldb_table('local_learningplan');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }   

    $table = new xmldb_table('local_program');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    $table = new xmldb_table('local_onlinetests');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    $table = new xmldb_table('local_certification');
    if ($dbman->table_exists($table)) {
        $field = new xmldb_field('certificateid', XMLDB_TYPE_INTEGER, 10, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }  
}