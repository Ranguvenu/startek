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
 * Shortcode handler.
 *
 * @package    block_gamification
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\shortcode;

use block_gamification\di;
use block_gamification\local\config\config_stack;
use block_gamification\local\config\static_config;
use block_gamification\local\sql\limit;
use block_gamification\local\utils\user_utils;
use block_gamification\local\gamification\level_with_name;

/**
 * Shortcode handler class.
 *
 * @package    block_gamification
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handler {

    /**
     * Best guess what group ID to use.
     *
     * @param int $courseid The course ID.
     * @param int $userid The user ID.
     * @return int
     */
    protected static function get_group_id($courseid, $userid) {
        return user_utils::get_primary_group_id($courseid, $userid);
    }

    /**
     * Get the world from the env.
     *
     * Also check whether the current user has access to the world.
     *
     * @param object $env The environment.
     * @return world|null
     */
    protected static function get_world_from_env($env) {
        $config = di::get('config');
        $courseid = SITEID;

        if ($config->get('context') == CONTEXT_COURSE) {
            $context = $env->context->get_course_context(false);
            if (!$context) {
                return null;
            }
            $courseid = $context->instanceid;
        }

        $world = di::get('course_world_factory')->get_world($courseid);
        $perms = $world->get_access_permissions();
        return $perms->can_access() ? $world : null;
    }

    /**
     * Handle the shortcode.
     *
     * @param string $shortcode The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function gamificationbadge($shortcode, $args, $content, $env, $next) {
        global $USER;
        $world = static::get_world_from_env($env);
        if (!$world) {
            return;
        }
        $state = $world->get_store()->get_state($USER->id);
        return di::get('renderer')->level_badge($state->get_level());
    }

    /**
     * Handle the shortcode.
     *
     * The supported formats are:
     *
     * [gamificationiflevel 2 3 4 5]
     * [gamificationiflevel >=2 <=5]
     * [gamificationiflevel >1 <6]
     *
     * Greater/less than arguments must all match. Whereas a strictly defined level will match
     * regardless of the other rules.
     *
     * @param string $shortcode The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function gamificationiflevel($shortcode, $args, $content, $env, $next) {
        global $USER;
        if (empty($args)) {
            return '';
        }

        $world = static::get_world_from_env($env);
        if (!$world) {
            return '';
        }

        // Always return the content to teachers.
        $perms = $world->get_access_permissions();
        if ($perms->can_manage()) {
            return $next($content);
        }

        // There is a match on a specific level.
        $level = $world->get_store()->get_state($USER->id)->get_level()->get_level();
        if (array_key_exists($level, $args)) {
            return $next($content);
        }

        // Reorganise the less/greater than, and less/greater than or equal.
        $lessgreater = ['<' => null, '<=' => null, '>' => null, '>=' => null];

        // Attributes can be HTML encoded.
        $args = array_combine(array_map(function($key) {
            return html_entity_decode($key);
        }, array_keys($args)), array_values($args));

        // The user types <=3, so they are stored in the < key.
        $lessgreater['<='] = isset($args['<']) ? $args['<'] : null;
        $lessgreater['>='] = isset($args['>']) ? $args['>'] : null;

        // Find the keys using < or > followed by a number.
        foreach ($args as $key => $val) {
            if ($val !== true || strlen($key) < 2) {
                // Skip the <= and >=. And the < and > by themselves.
                continue;
            }
            if (substr($key, 0, 1) == '<') {
                $lessgreater['<'] = (int) substr($key, 1);
            } else if (substr($key, 0, 1) == '>') {
                $lessgreater['>'] = (int) substr($key, 1);
            }
        }

        if ($lessgreater['<'] === null && $lessgreater['<='] === null
                && $lessgreater['>'] === null && $lessgreater['>='] === null) {
            return '';
        } else if ($lessgreater['<'] !== null && $level >= $lessgreater['<']) {
            return '';
        } else if ($lessgreater['<='] !== null && $level > $lessgreater['<=']) {
            return '';
        } else if ($lessgreater['>'] !== null && $level <= $lessgreater['>']) {
            return '';
        } else if ($lessgreater['>='] !== null && $level < $lessgreater['>=']) {
            return '';
        }

        return $next($content);
    }

    /**
     * Handle the shortcode.
     *
     * @param string $shortcode The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function gamificationladder($shortcode, $args, $content, $env, $next) {
        global $PAGE, $USER;
        $world = static::get_world_from_env($env);
        if (!$world) {
            return;
        } else if (!$world->get_config()->get('enableladder')) {
            return;
        }

        // Compute the best group we can think of.
        $groupid = 0;
        if (di::get('config')->get('context') == CONTEXT_COURSE) {
            $groupid = static::get_group_id($world->get_courseid(), $USER->id);
        }

        // Prepare the config.
        $config = $world->get_config();
        if (!empty($args['top'])) {
            // Disable the neighbours when argument top is set.
            $config = new config_stack([new static_config(['neighbours' => 0]), $config]);
        }

        // Retrieve the leaderboard.
        $factory = di::get('course_world_leaderboard_factory_with_config');
        $leaderboard = $factory->get_course_leaderboard_with_config($world, $config, $groupid);

        // Check the position of the user.
        $pos = $leaderboard->get_position($USER->id);
        if ($pos === null) {
            if (!$world->get_access_permissions()->can_manage()) {
                return;
            }
            $pos = 0;
        }

        if (!empty($args['top'])) {
            // Show the top n users.
            if ($args['top'] === true) {
                $count = 10;
            } else {
                $count = max(1, intval($args['top']));
            }
            $limit = new limit($count, 0);

        } else {
            // Determine what part of the leaderboard to show and fence it.
            $before = 2;
            $after = 4;
            $offset = max(0, $pos - $before);
            $count = $before + $after + 1;
            $limit = new limit($count + min(0, $pos - $before), $offset);
        }

        // Output the table.
        $baseurl = $PAGE->url;
        $table = new \block_gamification\output\leaderboard_table($leaderboard, di::get('renderer'), [
            'context' => $world->get_context(),
            'fence' => $limit,
            'rankmode' => $config->get('rankmode'),
            'identitymode' => $config->get('identitymode'),
            'discardcolumns' => !empty($args['withprogress']) ? [] : ['progress'],
        ], $USER->id);
        $table->define_baseurl($baseurl);
        ob_start();
        $table->out($count);
        $html = ob_get_contents();
        ob_end_clean();

        // Output.
        $urlresolver = di::get('url_resolver');
        $link = '';
        $withlink = empty($args['hidelink']);
        if ($withlink) {
            $link = \html_writer::div(
                \html_writer::link(
                    $urlresolver->reverse('ladder', ['courseid' => $world->get_courseid()]),
                    get_string('gotofullladder', 'block_gamification')
                ),
                'gamification-link-to-full-ladder'
            );
        }
        return \html_writer::div(
            $html . $link,
            'shortcode-gamificationladder'
        );
    }

    /**
     * Handle the shortcode.
     *
     * @param string $shortcode The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function gamificationlevelname($shortcode, $args, $content, $env, $next) {
        global $USER;
        $world = static::get_world_from_env($env);
        if (!$world) {
            return;
        }
        $levelsinfo = $world->get_levels_info();

        if (!empty($args['level'])) {
            $levelno = intval($args['level']);
            if (!$levelno || $levelno > $levelsinfo->get_count()) {
                return '';
            }
            $level = $levelsinfo->get_level($levelno);
        } else {
            $state = $world->get_store()->get_state($USER->id);
            $level = $state->get_level();
        }

        $name = $level instanceof level_with_name ? $level->get_name() : null;
        return empty($name) ? get_string('levelx', 'block_gamification', $level->get_level()) : $name;
    }

    /**
     * Handle the shortcode.
     *
     * @param string $shortcode The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function gamificationpoints($shortcode, $args, $content, $env, $next) {
        $world = static::get_world_from_env($env);
        if (!$world) {
            return;
        }

        // The number of points is egamificationected to be passed as a standalone argument, which would
        // make it an attribute with the value of true, so we're interested in its key.
        $points = 0;
        if (count($args) === 1) {
            $points = (int) array_keys($args)[0];
        }

        return di::get('renderer')->gamification_highlight($points);
    }

    /**
     * Handle the shortcode.
     *
     * @param string $shortcode The shortcode.
     * @param array $args The arguments of the code.
     * @param string|null $content The content, if the shortcode wraps content.
     * @param object $env The filter environment (contains context, noclean and originalformat).
     * @param Closure $next The function to pass the content through to process sub shortcodes.
     * @return string The new content.
     */
    public static function gamificationprogressbar($shortcode, $args, $content, $env, $next) {
        global $USER;
        $world = static::get_world_from_env($env);
        if (!$world) {
            return;
        }
        $state = $world->get_store()->get_state($USER->id);
        return \html_writer::div(di::get('renderer')->progress_bar($state), '', ['style' => 'max-width: 200px']);
    }

}
