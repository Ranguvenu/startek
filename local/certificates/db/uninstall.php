<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * drop the certifiacate's related column from other module tables
 *
 * @package    local_certificates
 * @category   external
 * @copyright  eAbyas <www.eabyas.in>
 */
 
defined('MOODLE_INTERNAL') || die();
function xmldb_local_certificates_uninstall(){
	global $DB;
	$dbman = $DB->get_manager();
    $table = new xmldb_table('course');
	if ($dbman->table_exists($table)) {
		$field ='open_certificateid';
		if($dbman->field_exists($table, $field)){
			$dbman->drop_field($table, $field);
		}
	}

	$table = new xmldb_table('local_classroom');
	if ($dbman->table_exists($table)) {
		$field ='certificateid';
		if($dbman->field_exists($table, $field)){
			$dbman->drop_field($table, $field);
		}
	}

	$table = new xmldb_table('local_learningplan');
	if ($dbman->table_exists($table)) {
		$field ='certificateid';
		if($dbman->field_exists($table, $field)){
			$dbman->drop_field($table, $field);
		}
	}

	$table = new xmldb_table('local_program');
	if ($dbman->table_exists($table)) {
		$field ='certificateid';
		if($dbman->field_exists($table, $field)){
			$dbman->drop_field($table, $field);
		}
	}

	$table = new xmldb_table('local_onlinetests');
	if ($dbman->table_exists($table)) {
		$field ='certificateid';
		if($dbman->field_exists($table, $field)){
			$dbman->drop_field($table, $field);
		}
	}
}