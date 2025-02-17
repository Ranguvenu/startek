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
 * Debug controller.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\controller;

use coding_exception;

/**
 * Debug controller class.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debug_controller extends route_controller {

    /**
     * Authentication.
     *
     * @return void
     */
    protected function require_login() {
        require_login();
    }

    /**
     * Add here all permissions checks related to accessing the page.
     *
     * @throws moodle_exception When the conditions are not met.
     * @return void
     */
    protected function permissions_checks() {
        global $CFG;
        if (!$CFG->debugdeveloper || !is_siteadmin()) {
            throw new coding_exception('No/You/Are/Not');
        }
    }

    /**
     * Moodle page specifics.
     *
     * @return void
     */
    protected function page_setup() {
        global $COURSE, $PAGE;
        $PAGE->set_pagelayout('course');
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url($this->pageurl);
        $PAGE->set_title('i/am/dev');
        $PAGE->set_heading(format_string($COURSE->fullname));
    }

    /**
     * Echo the content.
     *
     * @return void
     */
    protected function content() {
        global $COURSE;

        echo $this->get_renderer()->heading('Level Up gamification Debug');

        echo '<pre>';

        $pluginmanager = \core_plugin_manager::instance();
        $blockgamification = $pluginmanager->get_plugin_info('block_gamification');
        $localgamification = $pluginmanager->get_plugin_info('local_gamification');

        echo 'Plugins:' . PHP_EOL;
        echo '--------' . PHP_EOL;
        echo PHP_EOL;

        echo 'block_gamification:' . PHP_EOL;
        echo ' - version disk: ' . $blockgamification->versiondisk . PHP_EOL;
        echo ' - version db: ' . $blockgamification->versiondb . PHP_EOL;
        echo ' - release: ' . $blockgamification->release . PHP_EOL;
        echo PHP_EOL;
        echo 'local_gamification:' . PHP_EOL;
        if ($localgamification) {
            echo ' - version disk: ' . $localgamification->versiondisk . PHP_EOL;
            echo ' - version db: ' . $localgamification->versiondb . PHP_EOL;
            echo ' - release: ' . $localgamification->release . PHP_EOL;
        } else {
            echo ' - Not present' . PHP_EOL;
        }

        echo PHP_EOL;
        echo 'Dependency injection:' . PHP_EOL;
        echo '---------------------' . PHP_EOL;
        echo PHP_EOL;

        $reflexion = new \ReflectionClass('\block_gamification\di');
        $property = $reflexion->getProperty('container');
        $property->setAccessible(true);
        echo 'Container: ' . get_class($property->getValue()) . PHP_EOL;

        echo PHP_EOL;
        echo 'Global config:' . PHP_EOL;
        echo '--------------' . PHP_EOL;

        $globalconfig = \block_gamification\di::get('config');
        $config = $globalconfig->get_all();
        ksort($config);
        var_export($config);
        echo PHP_EOL;

        echo PHP_EOL;
        echo 'Course config:' . PHP_EOL;
        echo '--------------' . PHP_EOL;

        $factory = \block_gamification\di::get('course_world_factory');
        $world = $factory->get_world($COURSE->id);
        $config = $world->get_config()->get_all();
        ksort($config);
        var_export($config);
        echo PHP_EOL;

        echo '</pre>';
    }

}
