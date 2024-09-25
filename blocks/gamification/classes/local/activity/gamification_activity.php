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
 * gamification activity.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\activity;

use DateTime;

/**
 * gamification activity.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gamification_activity implements activity, activity_with_gamification {

    /** @var DateTime The date. */
    protected $date;
    /** @var lang_string The description. */
    protected $desc;
    /** @var int The gamification. */
    protected $gamification;

    /**
     * Constructor.
     *
     * @param DateTime $date The date.
     * @param string|lang_string $desc The description.
     * @param int $gamification The gamification.
     */
    public function __construct(DateTime $date, $desc, $gamification) {
        $this->date = $date;
        $this->desc = $desc;
        $this->gamification = $gamification;
    }

    /**
     * Date.
     *
     * @return DateTime
     */
    public function get_date() {
        return $this->date;
    }

    /**
     * Description.
     *
     * @return The description.
     */
    public function get_description() {
        return (string) $this->desc;
    }

    /**
     * The gamification earned at this stage.
     *
     * @return int
     */
    public function get_gamification() {
        return $this->gamification;
    }

}
