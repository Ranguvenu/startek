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

require_once($CFG->libdir . '/formslib.php');

/**
 * The form for loading certification templates.
 *
 * @package    local_certification
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class load_template_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;

        // Get the context.
        $context = $this->_customdata['context'];

        $mform->addElement('header', 'loadtemplateheader', get_string('loadtemplate','local_certification'));

        // Display a link to the manage templates page.
        //if ($context->contextlevel != CONTEXT_SYSTEM && has_capability('local/certification:manage', \context_system::instance())) {
        //    $link = \html_writer::link(new \moodle_url('/local/certification/manage_templates.php'),
        //        get_string('managetemplates','local_certification'));
        //    $mform->addElement('static', 'managetemplates', '', $link);
        //}

        $templates = $DB->get_records_menu('local_certification_templts',
            array('contextid' => \context_system::instance()->id), 'name ASC', 'id, name');
        if ($templates) {
            $group = array();
            $group[] = $mform->createElement('select', 'ltid', '', $templates);
            $group[] = $mform->createElement('submit', 'loadtemplatesubmit', get_string('load','local_certification'));
            $mform->addElement('group', 'loadtemplategroup', '', $group, '', false);
            $mform->setType('ltid', PARAM_INT);
        } else {
            $msg = \html_writer::tag('div', get_string('notemplates','local_certification'), array('class' => 'alert'));
            $mform->addElement('static', 'notemplates', '', $msg);
        }
    }
}
