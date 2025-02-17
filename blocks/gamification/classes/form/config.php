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
 * Block gamification config form.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/itemspertime.php');
require_once(__DIR__ . '/duration.php');

use block_gamification\local\config\course_world_config;
use moodleform;
use moodle_url;

/**
 * Block gamification config form class.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config extends moodleform {

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $PAGE;
        // Conditional check (on world) for compatibility with older versions of local_gamification.
        $world = !empty($this->_customdata['world']) ? $this->_customdata['world'] : null;
        $config = \block_gamification\di::get('config');
        $renderer = \block_gamification\di::get('renderer');

        $mform = $this->_form;
        $mform->setDisableShortforms(true);

        $mform->addElement('header', 'hdrgeneral', get_string('general'));

        $mform->addElement('selectyesno', 'enabled', get_string('enablegamificationgain', 'block_gamification'));
        $mform->addHelpButton('enabled', 'enablegamificationgain', 'block_gamification');

        $mform->addElement('selectyesno', 'enableinfos', get_string('enableinfos', 'block_gamification'));
        $mform->addHelpButton('enableinfos', 'enableinfos', 'block_gamification');

        $mform->addElement('selectyesno', 'enablelevelupnotif', get_string('enablelevelupnotif', 'block_gamification'));
        $mform->addHelpButton('enablelevelupnotif', 'enablelevelupnotif', 'block_gamification');

        $mform->addElement('header', 'hdrladder', get_string('ladder', 'block_gamification'));

        $mform->addElement('selectyesno', 'enableladder', get_string('enableladder', 'block_gamification'));
        $mform->addHelpButton('enableladder', 'enableladder', 'block_gamification');

        $mform->addElement('select', 'identitymode', get_string('anonymity', 'block_gamification'), array(
            course_world_config::IDENTITY_OFF => get_string('hideparticipantsidentity', 'block_gamification'),
            course_world_config::IDENTITY_ON => get_string('displayparticipantsidentity', 'block_gamification'),
        ));
        $mform->addHelpButton('identitymode', 'anonymity', 'block_gamification');
        $mform->disabledIf('identitymode', 'enableladder', 'eq', 0);

        $mform->addElement('select', 'neighbours', get_string('limitparticipants', 'block_gamification'), array(
            0 => get_string('displayeveryone', 'block_gamification'),
            1 => get_string('displayoneneigbour', 'block_gamification'),
            2 => get_string('displaynneighbours', 'block_gamification', '2'),
            3 => get_string('displaynneighbours', 'block_gamification', '3'),
            4 => get_string('displaynneighbours', 'block_gamification', '4'),
            5 => get_string('displaynneighbours', 'block_gamification', '5'),
        ));
        $mform->addHelpButton('neighbours', 'limitparticipants', 'block_gamification');
        $mform->disabledIf('neighbours', 'enableladder', 'eq', 0);

        $mform->addElement('select', 'rankmode', get_string('ranking', 'block_gamification'), array(
            course_world_config::RANK_OFF => get_string('hiderank', 'block_gamification'),
            course_world_config::RANK_ON => get_string('displayrank', 'block_gamification'),
            course_world_config::RANK_REL => get_string('displayrelativerank', 'block_gamification'),
        ));
        $mform->addHelpButton('rankmode', 'ranking', 'block_gamification');
        $mform->disabledIf('rankmode', 'enableladder', 'eq', 0);

        $el = $mform->addElement('select', 'laddercols', get_string('ladderadditionalcols', 'block_gamification'), [
            'gamification' => get_string('total', 'block_gamification'),
            'progress' => get_string('progress', 'block_gamification'),
        ], ['style' => 'height: 4em;']);
        $el->setMultiple(true);
        $mform->addHelpButton('laddercols', 'ladderadditionalcols', 'block_gamification');

        $mform->addElement('hidden', '__generalend');
        $mform->setType('__generalend', PARAM_BOOL);

        $mform->addElement('header', 'hdrcheating', get_string('cheatguard', 'block_gamification'));

        $mform->addElement('selectyesno', 'enablecheatguard', get_string('enablecheatguard', 'block_gamification'));
        $mform->addHelpButton('enablecheatguard', 'enablecheatguard', 'block_gamification');

        $mform->addElement('block_gamification_form_itemspertime', 'maxactionspertime', get_string('maxactionspertime', 'block_gamification'), [
            'maxunit' => 60,
            'itemlabel' => get_string('actions', 'block_gamification')
        ]);
        $mform->addHelpButton('maxactionspertime', 'maxactionspertime', 'block_gamification');
        $mform->disabledIf('maxactionspertime', 'enablecheatguard', 'eq', 0);

        $mform->addElement('block_gamification_form_duration', 'timebetweensameactions', get_string('timebetweensameactions', 'block_gamification'), [
            'maxunit' => 60,
            'optional' => false,        // We must set this...
        ]);
        $mform->addHelpButton('timebetweensameactions', 'timebetweensameactions', 'block_gamification');
        $mform->disabledIf('timebetweensameactions', 'enablecheatguard', 'eq', 0);

        if ($world && $world->get_config()->get('enablecheatguard') && $config->get('enablepromoincourses')) {
            $worldconfig = $world->get_config();
            $timeframe = max(0, $worldconfig->get('timebetweensameactions'), $worldconfig->get('timeformaxactions'));

            $promourl = new moodle_url('https://www.levelup.plus');
            if (!empty($this->_customdata['promourl'])) {
                $promourl = $this->_customdata['promourl'];
            }

            if ($timeframe > HOURSECS * 6) {
                $mform->addElement('static', '', '', $renderer->notification_without_close(
                    get_string('promocheatguard', 'block_gamification', ['url' => $promourl->out()]
                ), 'warning'));
            }
        }

        $mform->addElement('hidden', '__cheatguardend');
        $mform->setType('__cheatguardend', PARAM_BOOL);

        $mform->addElement('header', 'hdrblockconfig', get_string('blockappearance', 'block_gamification'));

        $mform->addElement('text', 'blocktitle', get_string('configtitle', 'block_gamification'));
        $mform->addHelpButton('blocktitle', 'configtitle', 'block_gamification');
        $mform->setType('blocktitle', PARAM_TEXT);

        $mform->addElement('textarea', 'blockdescription', get_string('configdescription', 'block_gamification'));
        $mform->addHelpButton('blockdescription', 'configdescription', 'block_gamification');
        $mform->setType('blockdescription', PARAM_TEXT);

        $mform->addElement('select', 'blockrankingsnapshot', get_string('configblockrankingsnapshot', 'block_gamification'), [
            0 => get_string('no'),
            1 => get_string('yes'),
        ]);
        $mform->addHelpButton('blockrankingsnapshot', 'configblockrankingsnapshot', 'block_gamification');
        $mform->setType('blockrankingsnapshot', PARAM_INT);
        $mform->disabledIf('blockrankingsnapshot', 'enableladder', 'eq', '0');

        $mform->addElement('select', 'blockrecentactivity', get_string('configrecentactivity', 'block_gamification'), [
            0 => get_string('no'),
            3 => get_string('yes'),
        ]);
        $mform->addHelpButton('blockrecentactivity', 'configrecentactivity', 'block_gamification');
        $mform->setType('blockrecentactivity', PARAM_INT);

        $mform->addElement('hidden', '__blockappearanceend');
        $mform->setType('__blockappearanceend', PARAM_BOOL);

        $this->add_action_buttons();
    }

    /**
     * Definition after data.
     *
     * @return void
     */
    public function definition_after_data() {
        $mform = $this->_form;

        // Lock the settings that have been locked by an admin. We do this in definition_after_data
        // because as we support Moodle 3.1 in which self::after_definition() is not available.
        $configlocked = \block_gamification\di::get('config_locked');
        foreach ($configlocked->get_all() as $key => $islocked) {
            if (!$islocked || !$mform->elementExists($key)) {
                continue;
            }
            $mform->hardFreeze($key);
        }
    }

    /**
     * Get the data.
     *
     * @return stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        unset($data->__generalend);
        unset($data->__cheatguardend);
        unset($data->__blockappearanceend);
        unset($data->__loggingend);

        // Convert back from itemspertime.
        if (!isset($data->maxactionspertime) || !is_array($data->maxactionspertime)) {
            $data->maxactionspertime = 0;
            $data->timeformaxactions = 0;
        } else {
            $data->timeformaxactions = (int) $data->maxactionspertime['time'];
            $data->maxactionspertime = (int) $data->maxactionspertime['points'];
        }

        // When not selecting any, the data is not sent.
        if (!isset($data->laddercols)) {
            $data->laddercols = [];
        }
        $data->laddercols = implode(',', $data->laddercols);

        // When the cheat guard is disabled, we remove the config fields so that
        // we can keep the defaults and the data previously submitted by the user.
        if (empty($data->enablecheatguard)) {
            unset($data->maxactionspertime);
            unset($data->timeformaxactions);
            unset($data->timebetweensameactions);
        }

        unset($data->submitbutton);
        return $data;
    }

    /**
     * Set the data.
     *
     * @param mixed $name The data.
     */
    public function set_data($data) {
        $data = (array) $data;
        if (isset($data['laddercols'])) {
            $data['laddercols'] = explode(',', $data['laddercols']);
        }

        // Convert to itemspertime.
        if (isset($data['maxactionspertime']) && isset($data['timeformaxactions'])) {
            $data['maxactionspertime'] = [
                'points' => (int) $data['maxactionspertime'],
                'time' => (int) $data['timeformaxactions']
            ];
            unset($data['timeformaxactions']);
        }

        parent::set_data($data);
    }

}
