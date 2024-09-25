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

/**
 * Block gamification course world test.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/fixtures/events.php');

use block_gamification\tests\base_testcase;

/**
 * Course world testcase.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_gamification\local\local\course_world
 */
class course_world_test extends base_testcase {

    public function test_reset_data() {
        global $DB;

        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u2->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u1->id, $c2->id);

        $world = $this->get_world($c1->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_gamification\event\something_happened::mock(array('crud' => 'c', 'userid' => $u1->id, 'courseid' => $c1->id));
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $e = \block_gamification\event\something_happened::mock(array('crud' => 'c', 'userid' => $u2->id, 'courseid' => $c1->id));
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_gamification\event\something_happened::mock(array('crud' => 'c', 'userid' => $u1->id, 'courseid' => $c2->id));
        $strategy->collect_event($e);

        $this->assertEquals(2, $DB->count_records('block_gamification', array('courseid' => $c1->id)));
        $this->assertEquals(4, $DB->count_records('block_gamification_log', array('courseid' => $c1->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c2->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification_log', array('courseid' => $c2->id)));

        $world = $this->get_world($c1->id);
        $world->get_store()->reset();

        $this->assertEquals(0, $DB->count_records('block_gamification', array('courseid' => $c1->id)));
        $this->assertEquals(0, $DB->count_records('block_gamification_log', array('courseid' => $c1->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c2->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification_log', array('courseid' => $c2->id)));
    }

    public function test_reset_data_with_groups() {
        global $DB;

        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        $g1 = $this->getDataGenerator()->create_group(array('courseid' => $c1->id));

        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u2->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u1->id, $c2->id);
        $this->getDataGenerator()->create_group_member(array('groupid' => $g1->id, 'userid' => $u1->id));

        $world = $this->get_world($c1->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_gamification\event\something_happened::mock(array('crud' => 'c', 'userid' => $u1->id, 'courseid' => $c1->id));
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $e = \block_gamification\event\something_happened::mock(array('crud' => 'c', 'userid' => $u2->id, 'courseid' => $c1->id));
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_gamification\event\something_happened::mock(array('crud' => 'c', 'userid' => $u1->id, 'courseid' => $c2->id));
        $strategy->collect_event($e);

        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c1->id, 'userid' => $u1->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c1->id, 'userid' => $u2->id)));
        $this->assertEquals(2, $DB->count_records('block_gamification_log', array('courseid' => $c1->id, 'userid' => $u1->id)));
        $this->assertEquals(2, $DB->count_records('block_gamification_log', array('courseid' => $c1->id, 'userid' => $u2->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c2->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification_log', array('courseid' => $c2->id)));

        $world = $this->get_world($c1->id);
        $world->get_store()->reset_by_group($g1->id);

        $this->assertEquals(0, $DB->count_records('block_gamification', array('courseid' => $c1->id, 'userid' => $u1->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c1->id, 'userid' => $u2->id)));
        $this->assertEquals(0, $DB->count_records('block_gamification_log', array('courseid' => $c1->id, 'userid' => $u1->id)));
        $this->assertEquals(2, $DB->count_records('block_gamification_log', array('courseid' => $c1->id, 'userid' => $u2->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification', array('courseid' => $c2->id)));
        $this->assertEquals(1, $DB->count_records('block_gamification_log', array('courseid' => $c2->id)));
    }

    public function test_delete_user_state() {
        global $DB;

        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $u1 = $this->getDataGenerator()->create_user();
        $u2 = $this->getDataGenerator()->create_user();
        $g1 = $this->getDataGenerator()->create_group(['courseid' => $c1->id]);

        $this->getDataGenerator()->enrol_user($u1->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u2->id, $c1->id);
        $this->getDataGenerator()->enrol_user($u1->id, $c2->id);

        $world = $this->get_world($c1->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_gamification\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id, 'courseid' => $c1->id]);
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $e = \block_gamification\event\something_happened::mock(['crud' => 'c', 'userid' => $u2->id, 'courseid' => $c1->id]);
        $strategy->collect_event($e);
        $strategy->collect_event($e);

        $world = $this->get_world($c2->id);
        $world->get_config()->set_many(['enabled' => true, 'timebetweensameactions' => 0]);
        $strategy = $world->get_collection_strategy();

        $e = \block_gamification\event\something_happened::mock(['crud' => 'c', 'userid' => $u1->id, 'courseid' => $c2->id]);
        $strategy->collect_event($e);

        $world = $this->get_world($c1->id);

        $this->assertGreaterThan(0, $world->get_store()->get_state($u1->id)->get_gamification());
        $this->assertGreaterThan(0, $world->get_store()->get_state($u2->id)->get_gamification());
        $this->assertEquals(1, $DB->count_records('block_gamification', ['courseid' => $c1->id, 'userid' => $u1->id]));
        $this->assertEquals(1, $DB->count_records('block_gamification', ['courseid' => $c1->id, 'userid' => $u2->id]));
        $this->assertEquals(2, $DB->count_records('block_gamification_log', ['courseid' => $c1->id, 'userid' => $u1->id]));
        $this->assertEquals(2, $DB->count_records('block_gamification_log', ['courseid' => $c1->id, 'userid' => $u2->id]));
        $this->assertEquals(1, $DB->count_records('block_gamification', ['courseid' => $c2->id]));
        $this->assertEquals(1, $DB->count_records('block_gamification_log', ['courseid' => $c2->id]));

        $world->get_store()->delete($u1->id);

        $this->assertEquals(0, $world->get_store()->get_state($u1->id)->get_gamification());
        $this->assertGreaterThan(0, $world->get_store()->get_state($u2->id)->get_gamification());
        $this->assertEquals(0, $DB->count_records('block_gamification', ['courseid' => $c1->id, 'userid' => $u1->id]));
        $this->assertEquals(1, $DB->count_records('block_gamification', ['courseid' => $c1->id, 'userid' => $u2->id]));
        $this->assertEquals(0, $DB->count_records('block_gamification_log', ['courseid' => $c1->id, 'userid' => $u1->id]));
        $this->assertEquals(2, $DB->count_records('block_gamification_log', ['courseid' => $c1->id, 'userid' => $u2->id]));
        $this->assertEquals(1, $DB->count_records('block_gamification', ['courseid' => $c2->id]));
        $this->assertEquals(1, $DB->count_records('block_gamification_log', ['courseid' => $c2->id]));
    }
}
