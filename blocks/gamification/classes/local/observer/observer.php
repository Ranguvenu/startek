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
 * Block gamification observer.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\observer;

/**
 * Block gamification observer class.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Act when a course is deleted.
     *
     * @param  \core\event\course_deleted $event The event.
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        $courseid = $event->objectid;

        // Clean up the data that could be left behind.
        $conditions = array('courseid' => $courseid);
        $DB->delete_records('block_gamification', $conditions);
        $DB->delete_records('block_gamification_config', $conditions);
        $DB->delete_records('block_gamification_filters', $conditions);
        $DB->delete_records('block_gamification_log', $conditions);

        // Flags. Note that this is based on the actually implementation.
        $sql = $DB->sql_like('name', ':name');
        $DB->delete_records_select('user_preferences', $sql, [
            'name' => 'block_gamification-notice-block_intro_' . $courseid
        ]);
        $DB->delete_records_select('user_preferences', $sql, [
            'name' => 'block_gamification_notify_level_up_' . $courseid
        ]);

        // Delete the files.
        $fs = get_file_storage();
        $fs->delete_area_files($event->contextid, 'block_gamification', 'badges');
    }

    /**
     * Observe all events.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public static function catch_all(\core\event\base $event) {
        $cs = \block_gamification\di::get('collection_strategy');
        if ($cs instanceof \block_gamification\local\strategy\event_collection_strategy) {
            $cs->collect_event($event);
        }
    }

}
