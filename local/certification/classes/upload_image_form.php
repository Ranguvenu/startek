<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms 
 * @subpackage local_certification
 */

namespace local_certification;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');

/**
 * Handles uploading files.
 *
 * @package    local_certification
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload_image_form extends \moodleform {

    /** @var array the filemanager options */
    protected $filemanageroptions = array();

    /**
     * Form definition.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $this->filemanageroptions = array(
            'maxbytes' => $CFG->maxbytes,
            'subdirs' => 1,
            'accepted_types' => 'image');
        $mform->addElement('filemanager', 'certificationimage', get_string('uploadimage','local_certification'), '',
            $this->filemanageroptions);

        $this->add_action_buttons();
    }

    /**
     * Fill in the current page data for this certification.
     */
    public function definition_after_data() {
        $mform = $this->_form;

        // Editing existing instance - copy existing files into draft area.
        $draftitemid = file_get_submitted_draft_itemid('certificationimage');
        file_prepare_draft_area($draftitemid, \context_system::instance()->id, 'local_certification', 'image', 0,
            $this->filemanageroptions);
        $element = $mform->getElement('certificationimage');
        $element->setValue($draftitemid);
    }
}
