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

function xmldb_local_forum_upgrade($oldversion) {
	global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2023022400.02) {
        $table = new xmldb_table('course');
        if ($dbman->table_exists($table)) {

            $field1 = new xmldb_field('open_module');
            $field1->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
            if (!$dbman->field_exists($table, $field1)) {
                $dbman->add_field($table, $field1);
            }
            $field1 = new xmldb_field('open_coursetype', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);
            if (!$dbman->field_exists($table, $field1)) {
                $dbman->add_field($table, $field1);
            }
        }

        upgrade_plugin_savepoint(true, 2023022400.02, 'local', 'forum');
    }
    return true;
}
