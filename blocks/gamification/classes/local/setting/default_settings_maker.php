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
 * Default settings maker.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\setting;

use admin_category;
use admin_settingpage;
use admin_externalpage;
use admin_setting;
use admin_setting_flag;
use admin_setting_heading;
use admin_setting_configcheckbox;
use admin_setting_configmultiselect;
use admin_setting_configselect;
use admin_setting_configtext;
use admin_setting_configtextarea;
use block_gamification\local\config\config;
use block_gamification\local\config\course_world_config;
use block_gamification\local\routing\url_resolver;
use moodle_database;

/**
 * Default settings maker.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class default_settings_maker implements settings_maker {

    /** @var config The config holding the defaults. */
    protected $defaults;
    /** @var url_resolver The URL resolver. */
    protected $urlresolver;
    /** @var config The repository of locked config. */
    protected $configlocked;

    /**
     * Constructor.
     *
     * @param config $defaults The config object to get the defaults from.
     * @param url_resolver $urlresolver The URL resolver.
     * @param config|null $configlocked The repository of locked config.
     * @param moodle_database|null $db The database.
     */
    public function __construct(config $defaults, url_resolver $urlresolver, config $configlocked = null) {
        $this->defaults = $defaults;
        $this->urlresolver = $urlresolver;
        $this->configlocked = $configlocked;
    }

    /**
     * Get the settings.
     *
     * @param environment $env The environment for creating the settings.
     * @return part_of_admin_tree|null
     */
    public function get_settings(environment $env) {
        $catname = 'block_gamification_category';
        $plugininfo = $env->get_plugininfo();

        // Create a category to hold different pages.
        $settings = new admin_category($catname, $plugininfo->displayname);

        // Block are given a generic settings page.
        // We rename it, add it to the category, and populate it.
        $settingspage = $env->get_settings_page();
        $settingspage->visiblename = get_string('generalsettings', 'admin');
        $settings->add($catname, $settingspage);
        if ($env->is_full_tree()) {
            array_map(function($setting) use ($settingspage) {
                $settingspage->add($setting);
            }, $this->get_general_settings());
        }

        // Default settings page.
        $settingspage = new admin_settingpage('block_gamification_default_settings', get_string('defaultsettings', 'block_gamification'));
        if ($env->is_full_tree()) {
            array_map(function($setting) use ($settingspage) {
                if ($this->configlocked && $this->configlocked->has($setting->name)) {
                    $setting->set_locked_flag_options(admin_setting_flag::ENABLED, false);
                }
                $settingspage->add($setting);
            }, $this->get_default_settings());
        }
        $settings->add($catname, $settingspage);

        // Add the default levels page.
        $settingspage = new admin_externalpage('block_gamification_default_levels',
            get_string('defaultlevels', 'block_gamification'),
            $this->urlresolver->reverse('admin/levels'));
        $settings->add($catname, $settingspage);

        // Add the default rules page.
        $settingspage = new admin_externalpage('block_gamification_default_rules',
            get_string('defaultrules', 'block_gamification'),
            $this->urlresolver->reverse('admin/rules'));
        $settings->add($catname, $settingspage);

        // Add the default visuals page.
        $settingspage = new admin_externalpage('block_gamification_default_visuals',
            get_string('defaultvisuals', 'block_gamification'),
            $this->urlresolver->reverse('admin/visuals'));
        $settings->add($catname, $settingspage);

        // Add the promo page.
        $pluginman = \core_plugin_manager::instance();
        $localgamification = $pluginman->get_plugin_info('local_gamification');
        // $settingspage = new admin_externalpage('block_gamification_promo',
        //     ($localgamification ? '' : '⭐ ') . get_string('navpromo', 'block_gamification'),
        //     $this->urlresolver->reverse('admin/promo'));
        // $settings->add($catname, $settingspage); //comment by revathi

        return $settings;
    }

    /**
     * Get the general settings.
     *
     * @return admin_setting[]
     */
    protected function get_general_settings() {
        $settings = [];

        // Display a list of recommended plugins.
        $settings[] = new recommended_plugins_setting();

        // Context in which the block is enabled.
        $settings[] = (new admin_setting_configselect(
            'block_gamification_context',
            get_string('wherearegamificationused', 'block_gamification'),
            get_string('wherearegamificationused_desc', 'block_gamification'),
            $this->defaults->get('context'),
            [
                CONTEXT_COURSE => get_string('incourses', 'block_gamification'),
                // CONTEXT_SYSTEM => get_string('forthewholesite', 'block_gamification')
            ]
        ));

        // Keeps logs for.
        $settings[] = (new admin_setting_configselect('block_gamification/keeplogs',
            get_string('keeplogs', 'block_gamification'), '',
            $this->defaults->get('keeplogs'), [
                '0' => get_string('forever', 'block_gamification'),
                '1' => get_string('numday', 'core', 1),
                '3' => get_string('numdays', 'core', 3),
                '7' => get_string('numweek', 'core', 1),
                '30' => get_string('nummonth', 'core', 1),
            ]
        ));

        // Usage report.
        $setting = (new admin_setting_configselect(
            'block_gamification/usagereport',
            get_string('usagereport', 'block_gamification'),
            get_string('usagereport_desc', 'block_gamification'),
            $this->defaults->get('usagereport'),
            [
                0 => get_string('never', 'core'),
                1 => get_string('occasionally', 'block_gamification'),
            ]
        ));
        $setting->set_updatedcallback(function() {
            $isenabled = (bool) get_config('block_gamification', 'usagereport');
            \block_gamification\task\usage_report::set_enabled($isenabled);
        });
        $settings[] = $setting;

        return $settings;
    }

    /**
     * Get the default settings.
     *
     * @return admin_setting[]
     */
    protected function get_default_settings() {
        $defaults = $this->defaults->get_all();
        $settings = [];

        // Intro.
        $settings[] = (new admin_setting_heading('block_gamification/hdrintro', '', get_string('admindefaultsettingsintro', 'block_gamification')));

        // General settings.
        $settings[] = (new admin_setting_heading('block_gamification/hdrgeneral', get_string('general'), ''));

        // Enable the information page?
        $settings[] = (new admin_setting_configcheckbox('block_gamification/enableinfos',
            get_string('enableinfos', 'block_gamification'), get_string('enableinfos_help', 'block_gamification'),
            $defaults['enableinfos']));

        // Enable the level-up notification?
        $settings[] = (new admin_setting_configcheckbox('block_gamification/enablelevelupnotif',
            get_string('enablelevelupnotif', 'block_gamification'), get_string('enablelevelupnotif_help', 'block_gamification'),
            $defaults['enablelevelupnotif']));

        // Ladder settings.
        $settings[] = (new admin_setting_heading('block_gamification/hdrladder', get_string('ladder', 'block_gamification'), ''));

        // Enable the ladder?
        $settings[] = (new admin_setting_configcheckbox('block_gamification/enableladder',
            get_string('enableladder', 'block_gamification'), get_string('enableladder_help', 'block_gamification'),
            $defaults['enableladder']));

        // Anonymity.
        $settings[] = (new admin_setting_configselect('block_gamification/identitymode',
            get_string('anonymity', 'block_gamification'), get_string('anonymity_help', 'block_gamification'),
            $defaults['identitymode'], [
                course_world_config::IDENTITY_OFF => get_string('hideparticipantsidentity', 'block_gamification'),
                course_world_config::IDENTITY_ON => get_string('displayparticipantsidentity', 'block_gamification'),
            ]
        ));

        // Neighbours.
        $settings[] = (new admin_setting_configselect('block_gamification/neighbours',
            get_string('limitparticipants', 'block_gamification'), get_string('limitparticipants_help', 'block_gamification'),
            $defaults['neighbours'], [
                0 => get_string('displayeveryone', 'block_gamification'),
                1 => get_string('displayoneneigbour', 'block_gamification'),
                2 => get_string('displaynneighbours', 'block_gamification', '2'),
                3 => get_string('displaynneighbours', 'block_gamification', '3'),
                4 => get_string('displaynneighbours', 'block_gamification', '4'),
                5 => get_string('displaynneighbours', 'block_gamification', '5'),
            ]
        ));

        // Ranking mode.
        $settings[] = (new admin_setting_configselect('block_gamification/rankmode',
            get_string('ranking', 'block_gamification'), get_string('ranking_help', 'block_gamification'),
            $defaults['rankmode'], [
                course_world_config::RANK_OFF => get_string('hiderank', 'block_gamification'),
                course_world_config::RANK_ON => get_string('displayrank', 'block_gamification'),
                course_world_config::RANK_REL => get_string('displayrelativerank', 'block_gamification'),
            ]
        ));

        // Additional columns.
        $settings[] = (new admin_setting_configmultiselect('block_gamification/laddercols',
            get_string('ladderadditionalcols', 'block_gamification'), get_string('ladderadditionalcols_help', 'block_gamification'),
            explode(',', $defaults['laddercols']), [
                'gamification' => get_string('total', 'block_gamification'),
                'progress' => get_string('progress', 'block_gamification'),
            ]
        ));

        // Cheat guard settings.
        $settings[] = (new admin_setting_heading('block_gamification/hdrcheatguard', get_string('cheatguard', 'block_gamification'), ''));

        // Enable the cheat guard?
        $settings[] = (new admin_setting_configcheckbox('block_gamification/enablecheatguard',
            get_string('enablecheatguard', 'block_gamification'), '',
            $defaults['enablecheatguard']));

        // Max actions per time.
        $settings[] = (new admin_setting_configtext('block_gamification/maxactionspertime',
            get_string('maxactionspertime', 'block_gamification'), get_string('maxactionspertime_help', 'block_gamification'),
            $defaults['maxactionspertime'], PARAM_INT));

        // Time for max actions.
        $settings[] = (new admin_setting_configtext('block_gamification/timeformaxactions',
            get_string('timeformaxactions', 'block_gamification'), get_string('timeformaxactions_help', 'block_gamification'),
            $defaults['timeformaxactions'], PARAM_INT));

        // Time between identical actions.
        $settings[] = (new admin_setting_configtext('block_gamification/timebetweensameactions',
            get_string('timebetweensameactions', 'block_gamification'), get_string('timebetweensameactions_help', 'block_gamification'),
            $defaults['timebetweensameactions'], PARAM_INT));

        // Block appearance settings.
        $settings[] = (new admin_setting_heading('block_gamification/hdrblockappearance',
            get_string('blockappearance', 'block_gamification'), ''));

        // Block title.
        $settings[] = (new admin_setting_configtext('block_gamification/blocktitle',
            get_string('configtitle', 'block_gamification'), get_string('configtitle_help', 'block_gamification'),
            $defaults['blocktitle'], PARAM_TEXT));

        // Block description.
        $settings[] = (new admin_setting_configtextarea('block_gamification/blockdescription',
            get_string('configdescription', 'block_gamification'), get_string('configdescription_help', 'block_gamification'),
            $defaults['blockdescription'], PARAM_TEXT));

        // Block ranking snapshot.
        $settings[] = (new admin_setting_configselect('block_gamification/blockrankingsnapshot',
            get_string('configblockrankingsnapshot', 'block_gamification'), get_string('configblockrankingsnapshot_help', 'block_gamification'),
            $defaults['blockrankingsnapshot'], [
                0 => get_string('no'),
                1 => get_string('yes')
            ]));

        // Block recent activity.
        $settings[] = (new admin_setting_configselect('block_gamification/blockrecentactivity',
            get_string('configrecentactivity', 'block_gamification'), get_string('configrecentactivity_help', 'block_gamification'),
            $defaults['blockrecentactivity'], [
                0 => get_string('no'),
                3 => get_string('yes')
            ]));

        return $settings;
    }
}
