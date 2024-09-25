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
 * Data provider.
 *
 * @package    block_gamification
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\privacy;

use context;
use context_course;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use block_gamification\local\privacy\addon_userlist_provider;

/**
 * Data provider class.
 *
 * The privacy information displayed in this class is based on the actual implementation of various
 * objects from block_gamification and should be kept in sync as the software evolves. The depenency injection
 * container should not be used as we must not inherit from another implementation.
 *
 * @package    block_gamification
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider ,
    \core_privacy\local\request\user_preference_provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function _get_metadata(collection $collection) { // @codingStandardsIgnoreLine

        $collection->add_database_table('block_gamification', [
            'gamification' => 'privacy:metadata:gamification:gamification',
            'lvl' => 'privacy:metadata:gamification:lvl',
            'userid' => 'privacy:metadata:gamification:userid',
        ], 'privacy:metadata:gamification');

        $collection->add_database_table('block_gamification_log', [
            'userid' => 'privacy:metadata:log:userid',
            'eventname' => 'privacy:metadata:log:eventname',
            'gamification' => 'privacy:metadata:log:gamification',
            'time' => 'privacy:metadata:log:time',
        ], 'privacy:metadata:log');

        $collection->add_user_preference('block_gamification_notices', 'privacy:metadata:prefnotices');
        $collection->add_user_preference('block_gamification-generic-ladder-pagesize', 'privacy:metadata:prefladderpagesize');
        $collection->add_user_preference('block_gamification-generic-promo-page-seen', 'privacy:metadata:prefseenpromo');
        $collection->add_user_preference('block_gamification-notice-block_intro_%d', 'privacy:metadata:prefintro');
        $collection->add_user_preference('block_gamification_notify_level_up_%d', 'privacy:metadata:preflevelup');

        return $collection;
    }

    /**
     * export all user preferences.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function _export_user_preferences($userid) { // @codingStandardsIgnoreLine
        $prefs = static::get_preferences_for_user($userid);
        foreach ($prefs as $pref) {
            writer::export_user_preference('block_gamification', $pref->name, $pref->value, $pref->description);
        }

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            $addon::export_addon_user_preferences($userid);
        }
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid($userid) { // @codingStandardsIgnoreLine
        $sql = "
            SELECT ctx.id
              FROM {block_gamification} gamification
              JOIN {context} ctx
                ON (gamification.courseid <> :siteid1 AND ctx.contextlevel = :contextlevel AND ctx.instanceid = gamification.courseid)
                OR (gamification.courseid = :siteid2 AND ctx.contextlevel = :contextsystem AND ctx.instanceid = 0)
             WHERE gamification.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'contextsystem' => CONTEXT_SYSTEM,
            'siteid1' => SITEID,
            'siteid2' => SITEID,
            'userid' => $userid,
        ];

        $contextlist = new \core_privacy\local\request\contextlist();
        $contextlist->add_from_sql($sql, $params);

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            $addon::add_addon_contexts_for_userid($contextlist, $userid);
        }

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users.
     */
    public static function get_users_in_context(userlist $userlist) {
        $courseid = static::get_courseid_from_context($userlist->get_context());
        if (!$courseid) {
            return;
        }

        $userlist->add_from_sql('userid', 'SELECT userid FROM {block_gamification} WHERE courseid = ?', [$courseid]);

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            if ($addon instanceof addon_userlist_provider) {
                $addon::add_addon_users_in_context($userlist);
            }
        }
    }

    /**
     * export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function _export_user_data(approved_contextlist $contextlist) { // @codingStandardsIgnoreLine
        $db = \block_gamification\di::get('db');
        $user = $contextlist->get_user();
        $levelup = get_string('pluginname', 'block_gamification');

        $courseids = array_filter(array_map(function($context) {
            return static::get_courseid_from_context($context);
        }, $contextlist->get_contexts()));

        list($insql, $inparams) = $db->get_in_or_equal($courseids, SQL_PARAMS_NAMED);

        // Fetch the record of points for each course.
        $sql = "
            SELECT gamification.userid, gamification.lvl, gamification.gamification, gamification.courseid
              FROM {block_gamification} gamification
             WHERE gamification.courseid $insql
               AND gamification.userid = :userid
          ORDER BY gamification.courseid";
        $params = ['userid' => $user->id] + $inparams;

        // There is only one row per course, so simply loop over.
        $path = [$levelup, get_string('privacy:path:level', 'block_gamification')];
        $recordset = $db->get_recordset_sql($sql, $params);
        foreach ($recordset as $record) {
            $context = static::get_context_from_courseid($record->courseid);
            writer::with_context($context)->export_data($path, (object) [
                'level' => $record->lvl,
                'userid' => transform::user($record->userid),
                'points' => $record->gamification,
            ]);
        }
        $recordset->close();

        // Fetch the logs.
        $sql = "
            SELECT l.userid, l.eventname, l.gamification, l.time, l.courseid
              FROM {block_gamification_log} l
             WHERE l.courseid $insql
               AND l.userid = :userid
          ORDER BY l.courseid, l.time";
        $params = ['userid' => $user->id] + $inparams;

        $path = [$levelup, get_string('privacy:path:logs', 'block_gamification')];
        $flushlogs = function($courseid, $data) use ($path) {
            $context = static::get_context_from_courseid($courseid);
            writer::with_context($context)->export_data($path, (object) ['data' => $data]);
        };

        // export the logs for each course.
        $recordset = $db->get_recordset_sql($sql, $params);
        $logs = [];
        $lastcourseid = null;
        foreach ($recordset as $record) {

            if ($lastcourseid && $lastcourseid != $record->courseid) {
                $flushlogs($lastcourseid, $logs);
                $logs = [];
            }

            $eventclass = $record->eventname;
            $eventname = get_string('unknowneventa', 'block_gamification', $eventclass);
            if (is_subclass_of($eventclass, '\core\event\base')) {
                $eventname = $eventclass::get_name();
            }

            $logs[] = (object) [
                'eventname' => $eventname,
                'time' => transform::datetime($record->time),
                'userid' => transform::user($record->userid),
                'points' => $record->gamification,
            ];
            $lastcourseid = $record->courseid;
        }

        // Flush the last iteration.
        if ($lastcourseid) {
            $flushlogs($lastcourseid, $logs);
        }

        $recordset->close();

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            $addon::export_addon_user_data([$levelup, get_string('privacy:path:addon', 'block_gamification')], $contextlist);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function _delete_data_for_all_users_in_context(context $context) { // @codingStandardsIgnoreLine
        $db = \block_gamification\di::get('db');

        $courseid = static::get_courseid_from_context($context);
        if ($courseid === null) {
            // OK, weirdly enough we cannot delete things from such a context.
            return;
        }

        $db->delete_records('block_gamification', ['courseid' => $courseid]);
        $db->delete_records('block_gamification_log', ['courseid' => $courseid]);

        // We manually delete the preferences within the context because core cannot find out which
        // preferences we assigned to specific contexts and the users they belong to.
        static::delete_preferences_for_all_users_in_context($context);

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            $addon::delete_addon_data_for_all_users_in_context($context);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) { // @codingStandardsIgnoreLine
        $db = \block_gamification\di::get('db');
        $user = $contextlist->get_user();
        $userid = $user->id;

        // Get the corresponding course IDs.
        $courseids = array_filter(array_map(function($context) {
            return static::get_courseid_from_context($context);
        }, $contextlist->get_contexts()));

        // Delete all the things.
        list($insql, $inparams) = $db->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $sql = "courseid $insql AND userid = :userid";
        $params = ['userid' => $userid] + $inparams;
        $db->delete_records_select('block_gamification', $sql, $params);
        $db->delete_records_select('block_gamification_log', $sql, $params);

        // Delete the user preferences in each context.
        foreach ($contextlist as $context) {
            static::delete_preferences_for_user_in_context($userid, $context);
        }

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            $addon::delete_addon_data_for_user($contextlist);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $db = \block_gamification\di::get('db');
        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        // Get the corresponding course ID.
        $context = $userlist->get_context();
        $courseid = static::get_courseid_from_context($context);
        if (!$courseid) {
            return;
        }

        // Delete all the things.
        list($insql, $inparams) = $db->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $sql = "courseid = :courseid AND userid $insql";
        $params = ['courseid' => $courseid] + $inparams;
        $db->delete_records_select('block_gamification', $sql, $params);
        $db->delete_records_select('block_gamification_log', $sql, $params);

        // Delete the user preferences in the context.
        foreach ($userids as $userid) {
            static::delete_preferences_for_user_in_context($userid, $context);
        }

        // Defer to the add-on.
        if ($addon = static::get_addon()) {
            if ($addon instanceof addon_userlist_provider) {
                $addon::delete_addon_data_for_users($userlist);
            }
        }
    }

    /**
     * Delete all preferences in context.
     *
     * @param context $context The context.
     */
    protected static function delete_preferences_for_all_users_in_context(context $context) {
        $courseid = static::get_courseid_from_context($context);
        if (!$courseid) {
            return;
        }

        $db = \block_gamification\di::get('db');
        $sql = $db->sql_like('name', ':name');
        $db->delete_records_select('user_preferences', $sql, [
            'name' => 'block_gamification-notice-block_intro_' . $courseid
        ]);
        $db->delete_records_select('user_preferences', $sql, [
            'name' => 'block_gamification_notify_level_up_' . $courseid
        ]);
    }

    /**
     * Delete all preferences of user.
     *
     * This should not be used, deleting all user preferences is taken care of by core.
     *
     * @param int $userid The user ID.
     */
    protected static function delete_preferences_for_user($userid) {
        $prefs = static::get_preferences_for_user($userid);
        if (empty($prefs)) {
            return;
        }

        $names = array_map(function($pref) {
            return $pref->name;
        }, $prefs);

        $db = \block_gamification\di::get('db');
        list($insql, $inparams) = $db->get_in_or_equal($names, SQL_PARAMS_NAMED);
        $params = ['userid' => $userid] + $inparams;
        $db->delete_records_select('user_preferences', "userid = :userid AND name {$insql}", $params);
    }

    /**
     * Delete preferences for user in context.
     *
     * @param int $userid The user ID.
     * @param context $context The context.
     */
    protected static function delete_preferences_for_user_in_context($userid, context $context) {
        $courseid = static::get_courseid_from_context($context);
        if (!$courseid) {
            return;
        }

        $db = \block_gamification\di::get('db');
        $likesql = $db->sql_like('name', ':name');
        $sql = "$likesql AND userid = :userid";
        $db->delete_records_select('user_preferences', $sql, [
            'name' => 'block_gamification-notice-block_intro_' . $courseid,
            'userid' => $userid,
        ]);
        $db->delete_records_select('user_preferences', $sql, [
            'name' => 'block_gamification_notify_level_up_' . $courseid,
            'userid' => $userid,
        ]);
    }

    /**
     * Get the context from a course ID.
     *
     * @param int $courseid The course ID.
     * @return context
     */
    protected static function get_context_from_courseid($courseid) {
        return $courseid == SITEID ? context_system::instance() : context_course::instance($courseid);
    }

    /**
     * Return a course ID from a context.
     *
     * @param context $context The context.
     * @return int|null
     */
    protected static function get_courseid_from_context(context $context) {
        $courseid = null;
        if ($context instanceof context_course) {
            $courseid = $context->instanceid;
        } else if ($context instanceof context_system) {
            $courseid = SITEID;
        }
        return $courseid;
    }

    /**
     * Get all the preferences of a user.
     *
     * @param int $userid The user ID
     * @return stdClass[] Contain properties name, value and description.
     */
    protected static function get_preferences_for_user($userid) {
        $prefs = [];

        $preferences = get_user_preferences(null, null, $userid);
        foreach ($preferences as $name => $value) {
            $desc = null;

            if ($name === 'block_gamification_notices') {
                $desc = get_string('privacy:metadata:prefnotices', 'block_gamification');
                $value = transform::yesno($value);

            } else if ($name === 'block_gamification-generic-promo-page-seen') {
                $desc = get_string('privacy:metadata:prefseenpromo', 'block_gamification');
                $value = transform::datetime($value);

            } else if ($name === 'block_gamification-generic-ladder-pagesize') {
                $desc = get_string('privacy:metadata:prefladderpagesize', 'block_gamification');

            } else if (strpos($name, 'block_gamification-notice-block_intro_') === 0) {
                $desc = get_string('privacy:metadata:prefintro', 'block_gamification');
                $value = transform::yesno($value);

            } else if (strpos($name, 'block_gamification_notify_level_up_') === 0) {
                $desc = get_string('privacy:metadata:preflevelup', 'block_gamification');
                $value = transform::yesno($value);

            } else {
                continue;
            }

            $prefs[] = (object) [
                'name' => $name,
                'value' => $value,
                'description' => $desc
            ];
        }

        return $prefs;
    }

    /**
     * Get the add-on class.
     *
     * @return string|null
     */
    protected static function get_addon() {
        $class = 'local_gamification\privacy\provider';
        if (is_subclass_of($class, 'block_gamification\local\privacy\addon_provider')) {
            return $class;
        }
        return null;
    }
}
