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
 * Defines the renderer for the quiz localule.
 *
 * @package   local_notifications
 * @copyright  2018 sreenivas
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notifications\output;

defined('MOODLE_INTERNAL') || die();
use renderable;
class notifications implements renderable {
     public function __construct($id=null) {
        $systemcontext =(new \local_notifications\lib\accesslib())::get_module_context();
        $this->id = $id;
        $this->context = $systemcontext;
     }
}

