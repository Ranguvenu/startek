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
 * Block.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\block;

use action_link;
use block_base;
use block_gamification\local\config\course_world_config;
use context;
use context_system;
use html_writer;
use lang_string;
use pix_icon;
use stdClass;
use block_gamification\local\course_world;
use block_gamification\local\permission\access_report_permissions;
use block_gamification\local\sql\limit;
use block_gamification\local\utils\user_utils;
use block_gamification\local\gamification\level_with_name;
use block_gamification\output\notice;
use block_gamification\output\dismissable_notice;

/**
 * Block class.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_block extends block_base {

    /**
     * Applicable formats.
     *
     * @return array
     */
    public function applicable_formats() {
        $mode = \block_gamification\di::get('config')->get('context');
        if ($mode == CONTEXT_SYSTEM) {
            return array('site' => true, 'course' => true, 'my' => true);
        }
        return array('site' => true, 'course' => true);
    }

    /**
     * The plugin has a settings.php file.
     *
     * @return boolean True.
     */
    public function has_config() {
        return true;
    }

    /**
     * Init.
     *
     * @return void
     */
    public function init() {
        // At this stage, this is not the title, it is the name displayed in the block
        // selector. In self::specialization() we will change that property to what it
        // should be as the title of the block.
        $this->title = get_string('pluginname', 'block_gamification');
    }

    /**
     * Callback when a block is created.
     *
     * @return bool
     */
    public function instance_create() {
        // Enable the capture of events for that course. Note that we are not egamificationecting the permission
        // to 'addinstance' or 'myaddinstance' to be given to standard users!
        $world = $this->get_world($this->page->course->id);
        $world->get_config()->set('enabled', true);
        return true;
    }

    /**
     * Callback when a block is deleted.
     *
     * @return bool
     */
    public function instance_delete() {
        $db = \block_gamification\di::get('db');
        $adminconfig = \block_gamification\di::get('config');

        if ($adminconfig->get('context') == CONTEXT_SYSTEM) {
            $context = context::instance_by_id($this->instance->parentcontextid);
            if ($context->contextlevel == CONTEXT_USER) {
                // Someone is removing their block from their dashboard, do nothing.
                return;
            }

            $bifinder = \block_gamification\di::get('course_world_block_instances_finder_in_context');
            $instances = $bifinder->get_instances_in_context('gamification', context_system::instance());
            if (count($instances) > 1) {
                // We do not want to disable points gain when we find more than one instance.
                return;
            }
        }

        // If we got here that's because we are either removing the block from a course,
        // or from the front page, or from the default dashboard. It's not ideal but
        // in that case we disable points gain.
        $world = $this->get_world($this->page->course->id);
        $world->get_config()->set('enabled', false);
        return true;
    }

    /**
     * Get content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $PAGE, $USER;

        if (isset($this->content)) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $world = $this->get_world($this->page->course->id);
        $canview = $world->get_access_permissions()->can_access();

        // Hide the block to non-logged in users, guests and those who cannot view the block.
        if (!$USER->id || isguestuser() || !$canview) {
            return $this->content;
        }

        $renderer = \block_gamification\di::get('renderer');
        $state = $world->get_store()->get_state($USER->id);

        // Migrate the old config if needed.
        $this->migrate_config_data_if_needed($world);

        // Render the content.
        $widget = $this->get_widget($world, $state);
        $this->content->text = $renderer->render($widget);

        // We should be congratulating the user because they leveled up!
        // Also resets the flag. We could potentially do that from JS so that if the user does not
        // stay on the page long enough they'd be notified the next time they access the course page,
        // but that's probably an overkill for now.
        $service = $world->get_level_up_notification_service();
        if ($service->should_be_notified($USER->id)) {
            $service->mark_as_notified($USER->id);

            $level = $state->get_level();
            $propsid = html_writer::random_id();
            echo $renderer->json_script([[
                'courseid' => $world->get_courseid(),
                'levelnum' => $level->get_level(),
                'levelname' => $level instanceof level_with_name ? $level->get_name() : null,
                'levelbadge' => $renderer->level_badge($level),
            ]], $propsid);

            $PAGE->requires->js_call_amd('block_gamification/popup-notification-queue', 'queueFromJson', ["#{$propsid}"]);
        }

        return $this->content;
    }

    /**
     * Get the block navigation.
     *
     * @param course_world $world The world.
     * @return action_link[]
     */
    protected function get_block_navigation(course_world $world) {
        $accessperms = $world->get_access_permissions();
        $canedit = $accessperms->can_manage();
        $canaccessreport = $accessperms instanceof access_report_permissions && $accessperms->can_access_report();
        $courseid = $world->get_courseid();
        $urlresolver = \block_gamification\di::get('url_resolver');
        $config = $world->get_config();
        $actions = [];

        if ($config->get('enableinfos')) {
            $actions[] = new action_link(
                $urlresolver->reverse('infos', ['courseid' => $courseid]),
                get_string('navinfos', 'block_gamification'), null, null,
                new pix_icon('i/info', '', 'block_gamification')
            );
        }
        if ($canedit && $courseid != SITEID) {
            $actions[] = new action_link(
                $urlresolver->reverse('rules', ['courseid' => $courseid]),
                get_string('navrules', 'block_gamification'), null, null,
                new pix_icon('i/ladder', '', 'block_gamification')
            );
        }
        if ($canaccessreport) {
            $actions[] = new action_link(
                $urlresolver->reverse('levels', ['courseid' => $courseid]),
                get_string('navlevels', 'block_gamification'), null, null,
                new pix_icon('i/report', '', 'block_gamification')
            );
        }
        if ($canedit && $courseid != SITEID) {
            $actions[] = new action_link(
                $urlresolver->reverse('config', ['courseid' => $courseid]),
                get_string('navsettings', 'block_gamification'), null, null,
                new pix_icon('i/settings', '', 'block_gamification')
            );
        }

        return $actions;
    }

    /**
     * Get the widget.
     *
     * @param \block_gamification\local\course_world $world The world.
     * @param \block_gamification\local\gamification\state $state The user's state.
     * @return \block_gamification\local\output\gamification_widget The widget.
     */
    protected function get_widget($world, $state) {
        global $USER;

        $context = $world->get_context();
        $canedit = $world->get_access_permissions()->can_manage();
        $adminconfig = \block_gamification\di::get('config');
        $indicator = \block_gamification\di::get('user_notice_indicator');
        $courseid = $world->get_courseid();
        $config = $world->get_config();
        $leaderboardfactory = \block_gamification\di::get('course_world_leaderboard_factory');

        // Recent activity.
        $activity = [];
        $forcerecentactivity = false;
        $recentactivity = $config->get('blockrecentactivity');
        if ($recentactivity) {
            $repo = $world->get_user_recent_activity_repository();
            $activity = $repo->get_user_recent_activity($USER->id, $recentactivity);

            // Users who can manage should see this when it's enabled, even without activity to show.
            $forcerecentactivity = $canedit;
        }

        // Navigation.
        $actions = $this->get_block_navigation($world);

        // Introduction.
        $introduction = format_string($config->get('blockdescription'), true, ['context' => $context]);
        $introname = 'block_intro_' . $courseid;
        if (empty($introduction)) {
            // The intro is empty, no need for further checks then...
            $introduction = null;
        } else if ($canedit) {
            // Always show the notification to teachers.
            $introduction = $introduction ? new notice($introduction, notice::INFO) : null;
        } else if (!$indicator->user_has_flag($USER->id, $introname)) {
            // Allow students to dismiss the message.
            $introduction = $introduction ? new dismissable_notice($introduction, $introname, notice::INFO) : null;
        } else {
            $introduction = null;
        }

        // Widget.
        $widget = new \block_gamification\output\gamification_widget(
            $state,
            $activity,
            $introduction,
            $actions
        );
        $widget->set_force_recent_activity($forcerecentactivity);

        // Add the rank to the widget.
        $rankon = $config->get('rankmode') == course_world_config::RANK_ON;
        $rankrel = $config->get('rankmode') == course_world_config::RANK_REL;
        if ($config->get('enableladder') && ($rankon || $rankrel)) {

            $groupid = 0;
            if ($adminconfig->get('context') == CONTEXT_COURSE) {
                $groupid = user_utils::get_primary_group_id($world->get_courseid(), $USER->id);
            }

            $leaderboard = $leaderboardfactory->get_course_leaderboard($world, $groupid);
            $widget->set_rank_is_rel($rankrel);
            $widget->set_show_diffs_in_ranking_snapshot($rankrel || array_key_exists('gamification', $leaderboard->get_columns()));

            // Gather the rank.
            if ($rankon) {
                $widget->set_rank($leaderboard->get_rank($USER->id));
                $widget->set_show_rank(true);
            }

            // Gather the ranking snapshot.
            if ($config->get('blockrankingsnapshot')) {
                $position = $leaderboard->get_position($USER->id);
                $widget->set_show_ranking_snapshot($position !== null || $canedit);
                if ($position !== null) {
                    $ranking = $leaderboard->get_ranking(new limit(3, max(0, $position - 1)));
                    $widget->set_ranking_snapshot($ranking);
                    // We may have a position, but an empty ranking, for instance with the neighboured
                    // leaderboard, therefore we must check again whether we should show the ranking.
                    $widget->set_show_ranking_snapshot(!empty($ranking) || $canedit);
                }
            }

        }

        // Add information about the next level.
        $widget->set_show_next_level((bool) $config->get('enableinfos'));
        if ($world->get_levels_info()->get_count() > $state->get_level()->get_level()) {
            $widget->set_next_level($world->get_levels_info()->get_level($state->get_level()->get_level() + 1));
        }

        // When gamification gain is disabled, let the teacher now.
        if (!$config->get('enabled') && $canedit) {
            $widget->add_manager_notice(new lang_string('gamificationgaindisabled', 'block_gamification'));
        }

        return $widget;
    }

    /**
     * Get the world.
     *
     * @param int $courseid The course ID.
     * @return \block_gamification\local\course_world The world.
     */
    protected function get_world($courseid) {
        return \block_gamification\di::get('course_world_factory')->get_world($courseid);
    }

    /**
     * Migrate config data if needed.
     *
     * This is used for the transition from configdata in the block
     * to using the configuration object of the world.
     *
     * @param \block_gamification\local\course_world $world The world.
     */
    protected function migrate_config_data_if_needed($world) {
        $migrateflag = 'block_configdata_migrated_' . $world->get_courseid();
        if (!get_config('block_gamification', $migrateflag)) {
            $config = $world->get_config();

            // An empty title previously defaulted to admin title, so do not change.
            if (!empty($this->config->title)) {
                $config->set('blocktitle', $this->config->title);
            }
            if (isset($this->config->description)) {
                $config->set('blockdescription', $this->config->description);
            }
            if (isset($this->config->recentactivity)) {
                $config->set('blockrecentactivity', (int) $this->config->recentactivity);
            }

            // Remove config and flag in an admin config. This is polluting the admin
            // config a bit, but we can remove these values later when we remove this
            // code, we cannot remove the flags before this code is as well. Note
            // that we need the flag because there may be multiple instances of the
            // block in different places and thus we could override the data. This
            // method here may not convert the right block instances, but it will
            // convert the first one displayed to a user. It is probably safe enough
            // and does not require a complex upgrade path to identify block instances.
            // Instances like the default dashboard are tricky ones to deal with.
            set_config($migrateflag, time(), 'block_gamification');

            // Reset the title as the specialisation has already happened.
            $this->title = format_string($config->get('blocktitle'), true, ['context' => $world->get_context()]);
        }
    }

    /**
     * Specialization.
     *
     * Happens right after the initialisation is complete.
     *
     * @return void
     */
    public function specialization() {
        parent::specialization();
        $world = $this->get_world($this->page->course->id);
        $context = $world->get_context();
        $config = $world->get_config();
        $this->title = format_string($config->get('blocktitle'), true, ['context' => $context]);
    }

}
