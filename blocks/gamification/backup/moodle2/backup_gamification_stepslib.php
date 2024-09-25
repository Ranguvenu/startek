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
 * Block gamification backup steplib.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block gamification backup structure step class.
 *
 * @package    block_gamification
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_gamification_block_structure_step extends backup_block_structure_step {

    /**
     * Define structure.
     */
    protected function define_structure() {
        global $DB;

        $userinfo = $this->get_setting_value('users');

        // Define each element separated.
        $gamificationconfig = new backup_nested_element('config', array('courseid'), array(
            'enabled', 'levels', 'lastlogpurge', 'enableladder', 'enableinfos', 'levelsdata',
            'enablelevelupnotif', 'enablecustomlevelbadges', 'maxactionspertime', 'timeformaxactions', 'timebetweensameactions',
            'identitymode', 'rankmode', 'neighbours', 'enablecheatguard', 'defaultfilters', 'laddercols', 'instructions',
            'instructions_format', 'blocktitle', 'blockdescription', 'blockrecentactivity', 'blockrankingsnapshot'
        ));
        $gamificationfilters = new backup_nested_element('filters');
        $gamificationfilter = new backup_nested_element('filter', array('courseid'), array('ruledata', 'points', 'sortorder', 'category'));
        $gamificationlevels = new backup_nested_element('gamifications');
        $gamificationlevel = new backup_nested_element('gamification', array('courseid'), array('userid', 'gamification', 'lvl'));
        $gamificationlogs = new backup_nested_element('logs');
        $gamificationlog = new backup_nested_element('log', array('courseid'), array('userid', 'eventname', 'gamification', 'time'));

        // Prepare the structure.
        $gamification = $this->prepare_block_structure($gamificationconfig);

        $gamificationfilters->add_child($gamificationfilter);
        $gamification->add_child($gamificationfilters);

        if ($userinfo) {
            $gamificationlevels->add_child($gamificationlevel);
            $gamification->add_child($gamificationlevels);

            $gamificationlogs->add_child($gamificationlog);
            $gamification->add_child($gamificationlogs);
        }

        // Define sources.
        $gamificationconfig->set_source_table('block_gamification_config', array('courseid' => backup::VAR_COURSEID));
        $gamificationfilter->set_source_table('block_gamification_filters', array('courseid' => backup::VAR_COURSEID));
        $gamificationlevel->set_source_table('block_gamification', array('courseid' => backup::VAR_COURSEID));
        $gamificationlog->set_source_table('block_gamification_log', array('courseid' => backup::VAR_COURSEID));

        // Annotations.
        $gamificationlevel->annotate_ids('user', 'userid');
        $gamificationlog->annotate_ids('user', 'userid');
        $gamification->annotate_files('block_gamification', 'badges', null, context_course::instance($this->get_courseid())->id);

        // Return the root element.
        return $gamification;
    }
}
