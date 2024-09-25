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
 * Block gamification restore steplib.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block gamification restore structure step class.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_gamification_block_structure_step extends restore_structure_step {

    /**
     * Execution conditions.
     *
     * @return bool
     */
    protected function execute_condition() {
        global $DB;

        // No restore on the front page.
        if ($this->get_courseid() == SITEID) {
            return false;
        }

        return true;
    }

    /**
     * Define structure.
     */
    protected function define_structure() {
        global $DB;

        $paths = array();
        $userinfo = $this->get_setting_value('users');

        // Define each path.
        $paths[] = new restore_path_element('block', '/block');
        $paths[] = new restore_path_element('config', '/block/config');
        $paths[] = new restore_path_element('filter', '/block/filters/filter');

        if ($userinfo) {
            $paths[] = new restore_path_element('gamification', '/block/gamifications/gamification');
            $paths[] = new restore_path_element('log', '/block/logs/log');
        }

        return $paths;
    }

    /**
     * Process block.
     */
    protected function process_block($data) {
        global $DB;

        $target = $this->get_task()->get_target();
        $courseid = $this->get_courseid();

        // The backup target egamificationects that all content is first being removed. Since deleting the block
        // instance does not delete the data itself, we must manually delete everything.
        if ($target == backup::TARGET_CURRENT_DELETING || $target == backup::TARGET_EXISTING_DELETING) {
            $this->log('block_gamification: deleting all data in target course', backup::LOG_DEBUG);

            // Removing associated data.
            $conditions = ['courseid' => $courseid];
            $DB->delete_records('block_gamification', $conditions);
            $DB->delete_records('block_gamification_config', $conditions);
            $DB->delete_records('block_gamification_filters', $conditions);
            $DB->delete_records('block_gamification_log', $conditions);

            // Removing old preferences.
            $sql = $DB->sql_like('name', ':name');
            $DB->delete_records_select('user_preferences', $sql, [
                'name' => 'block_gamification-notice-block_intro_' . $courseid
            ]);
            $DB->delete_records_select('user_preferences', $sql, [
                'name' => 'block_gamification_notify_level_up_' . $courseid
            ]);
        }
    }

    /**
     * Process config.
     */
    protected function process_config($data) {
        global $DB;
        $data['courseid'] = $this->get_courseid();

        // Guarantees that older backups are given the egamificationected legacy value here.
        if (!isset($data['defaultfilters'])) {
            $data['defaultfilters'] = \block_gamification\local\config\course_world_config::DEFAULT_FILTERS_STATIC;
        }

        if ($DB->record_exists('block_gamification_config', array('courseid' => $data['courseid']))) {
            $this->log('block_gamification: config not restored, existing config was found', backup::LOG_DEBUG);
            return;
        }
        $DB->insert_record('block_gamification_config', $data);
    }

    /**
     * Process filter.
     */
    protected function process_filter($data) {
        global $DB;
        $data['courseid'] = $this->get_courseid();

        // We must never have more than one category grades rule, and it should be a ruleset.
        if (!empty($data['category']) && $data['category'] == block_gamification_filter::CATEGORY_GRADES) {

            // If there is only rule, and its empty, then we restore on top of it.
            $records = $DB->get_records('block_gamification_filters', ['courseid' => $data['courseid'], 'category' => $data['category']]);
            if (count($records) === 1) {

                $record = reset($records);
                $filter = block_gamification_filter::load_from_data($record);
                $rule = $filter->get_rule();

                if ($rule instanceof block_gamification_ruleset) {
                    $rules = $rule->get_rules();
                    if (!empty($rules)) {
                        // The ruleset is not empty, there are existing rules, pass.
                        $this->log("block_gamification: grades rules not restored, existing grade rules found", backup::LOG_DEBUG);
                        return;
                    }

                    // Update the record.
                    $DB->update_record('block_gamification_filters', ['id' => $record->id] + $data);
                    return;

                } else {
                    // It really should be a ruleset, odd, let's just pass.
                    $this->log("block_gamification: grades rules not restored, existing grade rules found", backup::LOG_DEBUG);
                    return;
                }

            } else if (count($records) > 1) {
                // It's safer to ignore this.
                $this->log("block_gamification: grades rules not restored, multiple existing grade rules found", backup::LOG_DEBUG);
                return;
            }
        }

        $DB->insert_record('block_gamification_filters', $data);
    }

    /**
     * Process log.
     */
    protected function process_log($data) {
        global $DB;
        $data['courseid'] = $this->get_courseid();
        $data['userid'] = $this->get_mappingid('user', $data['userid']);
        $DB->insert_record('block_gamification_log', $data);
    }

    /**
     * Process gamification.
     */
    protected function process_gamification($data) {
        global $DB;
        $data['courseid'] = $this->get_courseid();
        $data['userid'] = $this->get_mappingid('user', $data['userid']);
        if ($DB->record_exists('block_gamification', array('courseid' => $data['courseid'], 'userid' => $data['userid']))) {
            $this->log("block_gamification: gamification of user with id '{$data['userid']}' not restored, existing entry found", backup::LOG_DEBUG);
            return;
        }
        $DB->insert_record('block_gamification', $data);
    }

    /**
     * After execute.
     */
    protected function after_execute() {
        $this->add_related_files('block_gamification', 'badges', null, $this->task->get_old_course_contextid());
    }

    /**
     * After restore.
     */
    protected function after_restore() {
        global $DB;
        $courseid = $this->get_courseid();

        // Update each filter (the rules).
        $filters = $DB->get_recordset('block_gamification_filters', ['courseid' => $courseid]);
        foreach ($filters as $filter) {
            $filter = block_gamification_filter::load_from_data($filter);
            $filter->update_after_restore($this->get_restoreid(), $courseid, $this->get_logger());
        }
        $filters->close();

        // Attempt to purge the filters cache. It should not be needed, but just in case.
        try {
            $factory = block_gamification\di::get('course_world_factory');
            $world = $factory->get_world($courseid);
            $filtermanager = $world->get_filter_manager();
            $filtermanager->invalidate_filters_cache();
        } catch (Exception $e) {
            $this->log("block_gamification: Could not invalidate filter cache", backup::LOG_DEBUG);
        }
    }

}
