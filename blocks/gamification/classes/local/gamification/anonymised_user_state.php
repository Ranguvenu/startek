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
 * Anonymised user state.
 *
 * @package    block_gamification
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\gamification;

use block_gamification\local\utils\user_utils;
use stdClass;

/**
 * Anonymised user state.
 *
 * @package    block_gamification
 * @copyright  2019 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class anonymised_user_state extends user_state {

    /** @var stdClass The anonymous user. */
    protected $anonuser;
    /** @var user_state The user state. */
    protected $state;

    /**
     * Constructor.
     *
     * @param stdClass $user The user object.
     * @param int $gamification The user gamification.
     * @param levels_info $levelsinfo Levels info.
     */
    public function __construct(user_state $state, stdClass $anonuser) {
        $this->anonuser = $anonuser;
        $this->state = $state;
    }

    public function get_id() {
        return $this->anonuser->id;
    }

    public function get_link() {
        return null;
    }

    public function get_name() {
        return get_string('someoneelse', 'block_gamification');
    }

    public function get_picture() {
        return user_utils::default_picture();
    }

    public function get_level() {
        return $this->state->get_level();
    }

    public function get_ratio_in_level() {
        return $this->state->get_ratio_in_level();
    }

    public function get_total_gamification_in_level() {
        return $this->state->get_total_gamification_in_level();
    }

    public function get_user() {
        return $this->anonuser;
    }

    public function get_gamification() {
        return $this->state->get_gamification();
    }

    public function get_gamification_in_level() {
        return $this->state->get_gamification_in_level();
    }

}
