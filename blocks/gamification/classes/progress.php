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
 * Block gamification progress renderable.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block gamification progress renderable.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @deprecated Since 3.0.0
 */
class block_gamification_progress implements renderable {

    /** @var int The course ID. */
    public $courseid = null;

    /** @var int The manager context ID. */
    public $contextid = null;

    /** @var int The user ID. */
    public $userid = null;

    /** @var int The gamification of the user. */
    public $gamification = null;

    /** @var int The level of the user. */
    public $level = null;

    /** @var int The total gamification to get to the level of the user. */
    public $levelgamification = null;

    /** @var string The plugin file URL to the level badge. */
    public $levelimgsrc = null;

    /** @var int The total gamification to get to the next level. */
    public $nextlevelgamification = null;

    /** @var bool False if the user reached the last level. */
    public $hasnextlevel = null;

    /** @var int The percentage of gamification accumulated to get to the next level. */
    public $percentage = null;

    /** @var int The next level. */
    public $nextlevel = null;

    /** @var int The gamification require for the next level, relative to the current level. */
    public $gamificationforlevel = null;

    /** @var int The gamification in the current level, relative to the current level. */
    public $gamificationinlevel = null;

    /** @var array The required fields. */
    protected static $required = array('courseid', 'contextid', 'userid', 'gamification', 'level', 'levelgamification', 'nextlevelgamification');

    /**
     * Constructor.
     *
     * @param array $params The fields to initialise the renderable.
     */
    public function __construct(array $params) {
        global $OUTPUT;

        $diff = array_diff_key($params, array_flip(self::$required));
        if (count($diff) > 0) {
            throw new coding_exception('Missing, or unexpected, properties');
        }

        // Assigning the properties.
        foreach ($params as $key => $value) {
            if (in_array($key, self::$required)) {
                $this->$key = $value;
            }
        }

        // Has next level.
        if (!empty($this->nextlevelgamification) && $this->nextlevelgamification > $this->gamification) {
            $this->hasnextlevel = true;
            $this->nextlevel = $this->level + 1;

            // Percentage.
            $this->gamificationinlevel = ($this->gamification - $this->levelgamification);
            $this->gamificationforlevel = ($this->nextlevelgamification - $this->levelgamification);
            $this->percentage = round($this->gamificationinlevel / $this->gamificationforlevel * 100);
        } else {
            $this->nextlevel = null;
            $this->hasnextlevel = true;
            $this->gamificationforlevel = $this->gamification;
            $this->gamificationinlevel = $this->gamification;
            $this->percentage = 100;
        }

        // Image URL.
        $this->levelimgsrc = moodle_url::make_pluginfile_url($this->contextid, 'block_gamification',
            'badges', 0, '/', $this->level);

    }

}
