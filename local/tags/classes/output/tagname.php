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
 * Contains class local_tags\output\tagname
 *
 * @package   local_tags
 * @copyright 2019 eAbyas <eAbyas.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tags\output;

use context_system;
use lang_string;
use html_writer;
use local_tags_tag;

/**
 * Class to preapare a tag name for display.
 *
 * @package   local_tags
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tagname extends \core\output\inplace_editable {

    /**
     * Constructor.
     *
     * @param \stdClass|local_tags_tag $tag
     */
    public function __construct($tag) {
        $editable = has_capability('moodle/tag:manage', (new \local_tags\lib\accesslib())::get_module_context());
        $edithint = new lang_string('editname', 'local_tags');
        $editlabel = new lang_string('newnamefor', 'local_tags', $tag->rawname);
        $value = $tag->rawname;
        $displayvalue = html_writer::link(local_tags_tag::make_url($tag->tagcollid, $tag->rawname),
            local_tags_tag::make_display_name($tag));
        parent::__construct('local_tags', 'tagname', $tag->id, $editable, $displayvalue, $value, $edithint, $editlabel);
    }

    /**
     * Updates the value in database and returns itself, called from inplace_editable callback
     *
     * @param int $itemid
     * @param mixed $newvalue
     * @return \self
     */
    public static function update($itemid, $newvalue) {
        require_capability('moodle/tag:manage', (new \local_tags\lib\accesslib())::get_module_context());
        $tag = local_tags_tag::get($itemid, '*', MUST_EXIST);
        $tag->update(array('rawname' => $newvalue));
        return new self($tag);
    }
}