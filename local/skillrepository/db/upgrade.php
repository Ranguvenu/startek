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
 * @subpackage local_skillrepository
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_skillrepository_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2016031003) {
        $table = new xmldb_table('local_skill');
        $field = new xmldb_field('parentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2016031003, 'local', 'skillrepository');
    }
    if($oldversion < 2016031011){
        $table = new xmldb_table('local_course_levels');
        $field = new xmldb_field('costcenterid',XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2016031011, 'local', 'skillrepository');
    }
    if($oldversion < 2016031029.10){
        $table = new xmldb_table('local_course_levels');
        $field = new xmldb_field('sortorder',XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2016031029.10, 'local', 'skillrepository');
    }

    //local_skill
    if ($oldversion <  2022101800) {
        $table = new xmldb_table('local_skill');
        $table1 = new xmldb_table('local_skillmatrix');
        $table2 = new xmldb_table('local_skill_categories');
        $table3 = new xmldb_table('local_skill_categories');

        $index = new xmldb_index('costcenterid', XMLDB_INDEX_NOTUNIQUE, array('costcenterid'));

        if (!$dbman->index_exists($table,$index)) {
            $dbman->add_index($table,$index);
        }

        $index1 = new xmldb_index('parentid', XMLDB_INDEX_NOTUNIQUE, array('parentid'));
        if (!$dbman->index_exists($table,$index1)) {
            $dbman->add_index($table,$index1);
        }

        $index3 = new xmldb_index('costcenterid', XMLDB_INDEX_NOTUNIQUE, array('costcenterid'));
        if (!$dbman->index_exists($table1,$index3)) {
            $dbman->add_index($table1,$index3);
        }

        $index4 = new xmldb_index('skill_categoryid', XMLDB_INDEX_NOTUNIQUE, array('    skill_categoryid'));
        if (!$dbman->index_exists($table1,$index4)) {
            $dbman->add_index($table1,$index4);
        }

        $index5 = new xmldb_index('skillid', XMLDB_INDEX_NOTUNIQUE, array('skillid'));
        if (!$dbman->index_exists($table1,$index5)) {
            $dbman->add_index($table1,$index5);
        }

        $index6 = new xmldb_index('positionid', XMLDB_INDEX_NOTUNIQUE, array('positionid'));
        if (!$dbman->index_exists($table1,$index6)) {
            $dbman->add_index($table1,$index6);
        }

        $index7 = new xmldb_index('levelid', XMLDB_INDEX_NOTUNIQUE, array('levelid'));
        if (!$dbman->index_exists($table1,$index7)) {
            $dbman->add_index($table1,$index7);
        }
        
        $index8 = new xmldb_index('costcenterid', XMLDB_INDEX_NOTUNIQUE, array('costcenterid'));
        if (!$dbman->index_exists($table2,$index8)) {
            $dbman->add_index($table2,$index8);
        }

        $index9 = new xmldb_index('parentid', XMLDB_INDEX_NOTUNIQUE, array('parentid'));
        if (!$dbman->index_exists($table2,$index9)) {
            $dbman->add_index($table2,$index9);
        }
        
        $index10 = new xmldb_index('costcenterid', XMLDB_INDEX_NOTUNIQUE, array('costcenterid'));
        if (!$dbman->index_exists($table3,$index10)) {
            $dbman->add_index($table3,$index10);
        }
        upgrade_plugin_savepoint(true,2022101800, 'local', 'skillrepository');
    }
    if ($oldversion < 2022101800.12) {
        $table = new xmldb_table('local_skill');
        $table1 = new xmldb_table('local_skill_categories');
        $table2 = new xmldb_table('local_course_levels');

        $field = new xmldb_field('open_path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table1, $field)) {
            $dbman->add_field($table1, $field);
        }
        if (!$dbman->field_exists($table2, $field)) {
            $dbman->add_field($table2, $field);
        }

        upgrade_plugin_savepoint(true, 2022101800.12, 'local', 'skillrepository');
    }

    if ($oldversion < 2022101801) {
        $table = new xmldb_table('local_interested_skills'); 
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('interested_skill_ids', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
            $table->add_field('open_costcenterid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('active', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($table);
        }
       upgrade_plugin_savepoint(true, 2022101801, 'local', 'skillrepository');
    }
    if($oldversion < 2022101803){
        $table = new xmldb_table('local_comp_skill_mapping');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('costcenterid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('competencylevelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('skillid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('skilllevelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            
            $result = $dbman->create_table($table);
        }
          upgrade_plugin_savepoint(true, 2022101803, 'local', 'skillrepository');
    }
    if($oldversion < 2022101803.04){
        $table = new xmldb_table('local_skill_levels');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('costcenterid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('skillid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $result = $dbman->create_table($table);
        }
          upgrade_plugin_savepoint(true, 2022101803.04, 'local', 'skillrepository');
    }

    if ($oldversion < 2022101803.06) {
        $table = new xmldb_table('local_comp_skill_mapping');
        $field1 = new xmldb_field('skilllevelid');
        if ($dbman->field_exists($table, $field1)) {
            $field1->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $dbman->change_field_type($table, $field1);
        }

        $table1 = new xmldb_table('local_skill');
        $field = new xmldb_field('category');
        if ($dbman->field_exists($table1, $field)) {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $dbman->change_field_type($table1, $field);
        }
        upgrade_plugin_savepoint(true, 2022101803.06, 'local', 'skillrepository');
    }

    if ($oldversion < 2022101803.08) {
        $table = new xmldb_table('local_skill_levels');

        $field1 = new xmldb_field('competencyid',XMLDB_TYPE_INTEGER, '10', null, null, null,null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        upgrade_plugin_savepoint(true, 2022101803.08, 'local', 'skillrepository');
    }

    if ($oldversion < 2022101803.10) {
        $table = new xmldb_table('local_skill_categories');

        $field = new xmldb_field('description',XMLDB_TYPE_TEXT, 'big', null, null, null,null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022101803.10, 'local', 'skillrepository');
    }

    if ($oldversion < 2022101803.11) {
        $table = new xmldb_table('local_comp_course_mapping'); 
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('competencyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('skillid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('levelid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

            $dbman->create_table($table);
        }
       upgrade_plugin_savepoint(true, 2022101803.11, 'local', 'skillrepository');
    }

    return true;
}
