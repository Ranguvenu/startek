<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
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

function xmldb_local_users_upgrade($oldversion)
{
    global $DB, $CFG;
    $dbman = $DB->get_manager();
    if ($oldversion < 2016080911.05) {
        $table = new xmldb_table('user');
        $field1 = new xmldb_field('open_positionid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        $field2 = new xmldb_field('open_domainid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        upgrade_plugin_savepoint(true, 2016080911.05, 'local', 'user');
    }

    if ($oldversion < 2020032600) {
        $table = new xmldb_table('local_uniquelogins');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('day', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('month', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('year', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('count_date', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('type', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2020032600, 'local', 'users');
    }

    if ($oldversion < 2022101800) {
        $table = new xmldb_table('user');
        $field1 = new xmldb_field('open_notify_logins', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }


        $table = new xmldb_table('local_recompletion_qa');
        $table1 = new xmldb_table('local_transcript_history');
        $table2 = new xmldb_table('local_uniquelogins');
        $table3 = new xmldb_table('local_positions');
        $table4 = new xmldb_table('local_domains');

        if ($dbman->table_exists($table)) {

            $index = new xmldb_index('uniqueid', XMLDB_INDEX_NOTUNIQUE, array('uniqueid'));

            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        if ($dbman->table_exists($table1)) {

            $index1 = new xmldb_index('employee_id', XMLDB_INDEX_NOTUNIQUE, array('employee_id'));

            if (!$dbman->index_exists($table1, $index1)) {
                $dbman->add_index($table1, $index1);
            }

            $index2 = new xmldb_index('training_object_id', XMLDB_INDEX_NOTUNIQUE, array('training_object_id'));

            if (!$dbman->index_exists($table1, $index2)) {
                $dbman->add_index($table1, $index2);
            }

            $index3 = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));

            if (!$dbman->index_exists($table1, $index3)) {
                $dbman->add_index($table1, $index3);
            }

            $index4 = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

            if (!$dbman->index_exists($table1, $index4)) {
                $dbman->add_index($table1, $index4);
            }
        }


        if ($dbman->table_exists($table2)) {

            $index5 = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

            if (!$dbman->index_exists($table2, $index5)) {
                $dbman->add_index($table2, $index5);
            }
        }

        if ($dbman->table_exists($table3)) {

            $index6 = new xmldb_index('costcenter', XMLDB_INDEX_NOTUNIQUE, array('costcenter'));

            if (!$dbman->index_exists($table3, $index6)) {
                $dbman->add_index($table3, $index6);
            }
        }

        if ($dbman->table_exists($table4)) {

            $index7 = new xmldb_index('costcenter', XMLDB_INDEX_NOTUNIQUE, array('costcenter'));

            if (!$dbman->index_exists($table4, $index7)) {
                $dbman->add_index($table4, $index7);
            }
        }

        upgrade_plugin_savepoint(true, 2022101800, 'local', 'users');
    }
    if ($oldversion < 2022101800.03) {
        $table = new xmldb_table('local_userssyncdata');
        $field1 = new xmldb_field('costcenterid', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);

        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        upgrade_plugin_savepoint(true, 2022101800.03, 'local', 'user');
    }
    if ($oldversion < 2022101800.08) {

        $table = new xmldb_table('local_userdata');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('costcenterpath', XMLDB_TYPE_CHAR, '512', null, XMLDB_NOTNULL, null, null);
        $table->add_field('categorypath', XMLDB_TYPE_CHAR, '512', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('user');
        $field1 = new xmldb_field('open_path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        upgrade_plugin_savepoint(true, 2022101800.08, 'local', 'users');
    }
    if ($oldversion < 2022101800.09) {
        $table = new xmldb_table('user');
        $field = new xmldb_field('open_states');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field1 = new xmldb_field('open_district');
        $field1->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('open_subdistrict');
        $field2->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        $field3 = new xmldb_field('open_village');
        $field3->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null);
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

        upgrade_plugin_savepoint(true, 2022101800.09, 'local', 'users');
    }
    if ($oldversion < 2022101800.12) {
        $table = new xmldb_table('user');

        $field1 = new xmldb_field('open_joindate');
        $field1->set_attributes(XMLDB_TYPE_CHAR, '512', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        upgrade_plugin_savepoint(true, 2022101800.12, 'local', 'users');
    }
    if ($oldversion < 2022101800.13) {
        $table = new xmldb_table('user');
        $field = new xmldb_field('gender');
        $field->set_attributes(XMLDB_TYPE_CHAR, '512', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field1 = new xmldb_field('open_dateofbirth');
        $field1->set_attributes(XMLDB_TYPE_CHAR, '512', null, null, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('open_employmenttype');
        $field2->set_attributes(XMLDB_TYPE_CHAR, '512', null, null, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }


        upgrade_plugin_savepoint(true, 2022101800.13, 'local', 'users');
    }

    if ($oldversion < 2022101800.14) {
        $table = new xmldb_table('user');
        $field = new xmldb_field('open_prefix');
        $field->set_attributes(XMLDB_TYPE_CHAR, '512', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2022101800.14, 'local', 'users');
    }
    if ($oldversion < 2022101800.17) {
        $table = new xmldb_table('user');
        $orgactive = new xmldb_field('open_orgactive');
        $orgactive->set_attributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $orgactive)) {
            $dbman->add_field($table, $orgactive);
        }
        upgrade_plugin_savepoint(true, 2022101800.17, 'local', 'users');
    }
    if ($oldversion < 2022101800.18) {
        $table = new xmldb_table('user');
        $educationlevel = new xmldb_field('open_educationlevel');
        $educationlevel->set_attributes(XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL,null, 0);
        if (!$dbman->field_exists($table, $educationlevel)) {
            $dbman->add_field($table, $educationlevel);
        }

        $fieldwork = new xmldb_field('open_fieldwork');
        $fieldwork->set_attributes(XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $fieldwork)) {
            $dbman->add_field($table, $fieldwork);
        }

        $jobtitle = new xmldb_field('open_jobtitle');
        $jobtitle->set_attributes(XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $jobtitle)) {
            $dbman->add_field($table, $jobtitle);
        }

        $company = new xmldb_field('open_company');
        $company->set_attributes(XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $company)) {
            $dbman->add_field($table, $company);
        }

        $paymentinfo = new xmldb_field('open_paymentinfo');
        $paymentinfo->set_attributes(XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $paymentinfo)) {
            $dbman->add_field($table, $paymentinfo);
        }

        $privacypolicy = new xmldb_field('open_privacypolicy');
        $privacypolicy->set_attributes(XMLDB_TYPE_INTEGER, '1', null, null, null, 0);
        if (!$dbman->field_exists($table, $privacypolicy)) {
            $dbman->add_field($table, $privacypolicy);
        }

        $termscondition = new xmldb_field('open_termscondition');
        $termscondition->set_attributes(XMLDB_TYPE_INTEGER, '1', null, null, null, 0);
        if (!$dbman->field_exists($table, $termscondition)) {
            $dbman->add_field($table, $termscondition);
        }

        upgrade_plugin_savepoint(true, 2022101800.18, 'local', 'users');
    }

    if ($oldversion < 2022101800.19) {
        $table = new xmldb_table('user');

        $countryid = new xmldb_field('open_countryid');
        $countryid->set_attributes(XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $countryid)) {
            $dbman->add_field($table, $countryid);
        }

        upgrade_plugin_savepoint(true, 2022101800.19, 'local', 'users');
    }

    if ($oldversion < 2023061000.01) {
        $table = new xmldb_table('user_info_field');

        $targetaudience = new xmldb_field('targetaudience');
        $targetaudience->set_attributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $targetaudience)) {
            $dbman->add_field($table, $targetaudience);
        }

        upgrade_plugin_savepoint(true, 2023061000.01, 'local', 'users');
    }


    if($oldversion < 2023061000.02){
        $table = new xmldb_table('user');

        $open_state = new xmldb_field('open_state');
        if ($dbman->field_exists($table, $open_state)) {
            $dbman->drop_field($table, $open_state);
        }

        $open_jobfunction = new xmldb_field('open_jobfunction');
        if ($dbman->field_exists($table, $open_jobfunction)) {
            $dbman->drop_field($table, $open_jobfunction);
        }

        $open_location = new xmldb_field('open_location');
        if ($dbman->field_exists($table, $open_location)) {
            $dbman->drop_field($table, $open_location);
        }

        $open_client = new xmldb_field('open_client');
        if ($dbman->field_exists($table, $open_client)) {
            $dbman->drop_field($table, $open_client);
        }

        $open_band = new xmldb_field('open_band');
        if ($dbman->field_exists($table, $open_band)) {
            $dbman->drop_field($table, $open_band);
        }

        $open_hrmsrole = new xmldb_field('open_hrmsrole');
        if ($dbman->field_exists($table, $open_hrmsrole)) {
            $dbman->drop_field($table, $open_hrmsrole);
        }

        $open_zone = new xmldb_field('open_zone');
        if ($dbman->field_exists($table, $open_zone)) {
            $dbman->drop_field($table, $open_zone);
        }

        $open_region = new xmldb_field('open_region');
        if ($dbman->field_exists($table, $open_region)) {
            $dbman->drop_field($table, $open_region);
        }

        $open_grade = new xmldb_field('open_grade');
        if ($dbman->field_exists($table, $open_grade)) {
            $dbman->drop_field($table, $open_grade);
        }

        $open_states = new xmldb_field('open_states');
        if ($dbman->field_exists($table, $open_states)) {
            $dbman->drop_field($table, $open_states);
        }

        $open_district = new xmldb_field('open_district');
        if ($dbman->field_exists($table, $open_district)) {
            $dbman->drop_field($table, $open_district);
        }

        $open_subdistrict = new xmldb_field('open_subdistrict');
        if ($dbman->field_exists($table, $open_subdistrict)) {
            $dbman->drop_field($table, $open_subdistrict);
        }

        $open_village = new xmldb_field('open_village');
        if ($dbman->field_exists($table, $open_village)) {
            $dbman->drop_field($table, $open_village);
        }

        $open_employmenttype = new xmldb_field('open_employmenttype');
        if ($dbman->field_exists($table, $open_employmenttype)) {
            $dbman->drop_field($table, $open_employmenttype);
        }

        $open_orgactive = new xmldb_field('open_orgactive');
        if ($dbman->field_exists($table, $open_orgactive)) {
            $dbman->drop_field($table, $open_orgactive);
        }


        upgrade_plugin_savepoint(true, 2023061000.02, 'local', 'users');
    }
    if($oldversion < 2023061000.03){
        $table = new xmldb_table('user');

        $open_location = new xmldb_field('open_location');
        $open_location->set_attributes(XMLDB_TYPE_CHAR, '60', null, null, null, null);
        if (!$dbman->field_exists($table, $open_location)) {
            $dbman->add_field($table, $open_location);
        }

        $open_department = new xmldb_field('open_departmentt');
        $open_department->set_attributes(XMLDB_TYPE_CHAR, '80', null, null, null, null);
        if (!$dbman->field_exists($table, $open_department)) {
            $dbman->add_field($table, $open_department);
        }

        $open_level = new xmldb_field('open_level');
        $open_level->set_attributes(XMLDB_TYPE_CHAR, '80', null, null, null, null);
        if (!$dbman->field_exists($table, $open_level)) {
            $dbman->add_field($table, $open_level);
        }



        upgrade_plugin_savepoint(true, 2023061000.03, 'local', 'users');
    }
    if($oldversion < 2023061000.04){
        $table = new xmldb_table('user');

        $field = new xmldb_field('open_departmentt', XMLDB_TYPE_CHAR, '80', null, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'open_hrmsrole');
        }

        upgrade_plugin_savepoint(true, 2023061000.04, 'local', 'users');
    }
    return true;
}
