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

namespace local_certification\form;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->dirroot . '/local/certification/includes/colourpicker.php');

\MoodleQuickForm::registerElementType('certification_colourpicker',
    $CFG->dirroot . '/local/certification/includes/colourpicker.php', 'MoodleQuickForm_certification_colourpicker');

/**
 * The form for handling the layout of the certification instance.
 *
 * @package    local_certification
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_form extends \moodleform {

    /**
     * @var int The id of the template being used.
     */
    protected $tid = null;

    /**
     * @var int The total number of pages for this cert.
     */
    protected $numpages = 1;

    /**
     * Form definition.
     */
     public $formstatus;
    public function __construct($action = null, $customdata = null, $method = 'post',
        $target = '', $attributes = null, $editable = true, $formdata = null) {
        $this->formstatus = array(
            'manage_certification' => get_string('manage_certification', 'local_certification'),
            'location_date' => get_string('location_date', 'local_certification'),
            'certification_misc' => get_string('assign_course', 'local_certification'),
        );
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $formdata);
    }
    public function definition() {
        global $DB, $OUTPUT;

        $mform =& $this->_form;
        
        $formstatus = $this->_customdata['form_status'];
        $id = $this->_customdata['id'] > 0 ? $this->_customdata['id'] : 0;
        
        $formheaders = array_keys($this->formstatus);
        $formheader = $formheaders[$formstatus];

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        if ($id>0) {
            $templateid=$DB->get_field('local_certification','templateid', array('id'=>$id));

        }else{
             $templateid=0;
        }
        $mform->addElement('hidden', 'templateid', $templateid);
        $mform->setType('templateid', PARAM_INT);

        // $mform->addElement('text', 'name', get_string('name', 'local_certification'), 'maxlength="255"');
        // $mform->setType('name', PARAM_TEXT);
        // $mform->addRule('name', null, 'required');

        // Get the number of pages for this localule.
       
        if (isset($this->_customdata['ctid'])) {
            $this->ctid = $this->_customdata['ctid'];
        }
        if (isset($this->_customdata['tid'])) {
            $this->tid = $this->_customdata['tid'];
            if ($pages = $DB->get_records('local_certification_pages', array('templateid' => $this->tid), 'sequence')) {
                $this->numpages = count($pages);
                foreach ($pages as $p) {
                    $this->add_certification_page_elements($p,$formstatus);
                }
            }
        } else { // Add a new template.
            // Create a 'fake' page to display the elements on - not yet saved in the DB.
            $page = new \stdClass();
            $page->id = 0;
            $page->sequence = 1;
            $this->add_certification_page_elements($page,$formstatus);
        }

        // Link to add another page.
        $addpagelink = new \moodle_url('/local/certification/edit.php',
            array(
                'tid' => $this->tid,
                'ctid' => $this->ctid,
                'aid' => 1,
                'action' => 'addpage',
                'sesskey' => sesskey()
            )
        );
        $icon = $OUTPUT->pix_icon('t/switch_plus', get_string('addcertpage', 'local_certification'));
        $addpagehtml = \html_writer::link($addpagelink, $icon . get_string('addcertpage', 'local_certification'));
        // $mform->addElement('html', \html_writer::tag('div', $addpagehtml, array('class' => 'addpage')));
        if(empty($formstatus)){
        // Add the submit buttons.
            $group = array();
            $group[] = $mform->createElement('submit', 'submitbtn', get_string('savechanges'));
            $group[] = $mform->createElement('submit', 'previewbtn', get_string('savechangespreview', 'local_certification'), array(), false);
            $mform->addElement('group', 'submitbtngroup', '', $group, '', false);
        }

        $mform->addElement('hidden', 'tid');
        $mform->setType('tid', PARAM_INT);
        $mform->setDefault('tid', $this->tid);
        
        $mform->addElement('hidden', 'ctid');
        $mform->setType('ctid', PARAM_INT);
        $mform->setDefault('ctid', $this->ctid);
    }

    /**
     * Fill in the current page data for this certification.
     */
    public function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // Check that we are updating a current certification.
        if ($this->tid) {
            // Get the pages for this certification.
            if ($pages = $DB->get_records('local_certification_pages', array('templateid' => $this->tid))) {
                // Loop through the pages.
                foreach ($pages as $p) {
                    // Set the width.
                    $element = $mform->getElement('pagewidth_' . $p->id);
                    $element->setValue($p->width);
                    // Set the height.
                    $element = $mform->getElement('pageheight_' . $p->id);
                    $element->setValue($p->height);
                    // Set the left margin.
                    $element = $mform->getElement('pageleftmargin_' . $p->id);
                    $element->setValue($p->leftmargin);
                    // Set the right margin.
                    $element = $mform->getElement('pagerightmargin_' . $p->id);
                    $element->setValue($p->rightmargin);
                }
            }
        }
    }

    /**
     * Some basic validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (\core_text::strlen($data['name']) > 255) {
            $errors['name'] = get_string('nametoolong', 'local_certification');
        }

        // Go through the data and check any width, height or margin  values.
        foreach ($data as $key => $value) {
            if (strpos($key, 'pagewidth_') !== false) {
                $page = str_replace('pagewidth_', '', $key);
                $widthid = 'pagewidth_' . $page;
                // Validate that the width is a valid value.
                if ((!isset($data[$widthid])) || (!is_numeric($data[$widthid])) || ($data[$widthid] <= 0)) {
                    $errors[$widthid] = get_string('invalidwidth', 'local_certification');
                }
            }
            if (strpos($key, 'pageheight_') !== false) {
                $page = str_replace('pageheight_', '', $key);
                $heightid = 'pageheight_' . $page;
                // Validate that the height is a valid value.
                if ((!isset($data[$heightid])) || (!is_numeric($data[$heightid])) || ($data[$heightid] <= 0)) {
                    $errors[$heightid] = get_string('invalidheight', 'local_certification');
                }
            }
            if (strpos($key, 'pageleftmargin_') !== false) {
                // Validate that the left margin is a valid value.
                if (isset($data[$key]) && ($data[$key] < 0)) {
                    $errors[$key] = get_string('invalidmargin', 'local_certification');
                }
            }
            if (strpos($key, 'pagerightmargin_') !== false) {
                // Validate that the right margin is a valid value.
                if (isset($data[$key]) && ($data[$key] < 0)) {
                    $errors[$key] = get_string('invalidmargin', 'local_certification');
                }
            }
        }

        return $errors;
    }

    /**
     * Adds the page elements to the form.
     *
     * @param \stdClass $page the certification page
     */
    protected function add_certification_page_elements($page,$formstatus) {
        global $DB, $OUTPUT;

        // Create the form object.
        $mform =& $this->_form;

        if ($this->numpages > 1) {
            $mform->addElement('header', 'page_' . $page->id, get_string('page', 'certification', $page->sequence));
        }

        $editlink = '/local/certification/edit.php';
        $editlinkparams = array('tid' => $this->tid,'ctid' => $this->ctid,'sesskey' => sesskey());
        $editelementlink = '/local/certification/edit_element.php';
        $editelementlinkparams = array('tid' => $this->tid,'ctid' => $this->ctid, 'sesskey' => sesskey());

        // Place the ordering arrows.
        // Only display the move up arrow if it is not the first.
        if ($page->sequence > 1) {
            $url = new \moodle_url($editlink, $editlinkparams + array('action' => 'pmoveup', 'aid' => $page->id));
            $mform->addElement('html', $OUTPUT->action_icon($url, new \pix_icon('t/up', get_string('moveup'))));
        }
        // Only display the move down arrow if it is not the last.
        if ($page->sequence < $this->numpages) {
            $url = new \moodle_url($editlink, $editlinkparams + array('action' => 'pmovedown', 'aid' => $page->id));
            $mform->addElement('html', $OUTPUT->action_icon($url, new \pix_icon('t/down', get_string('movedown'))));
        }

        $mform->addElement('text', 'pagewidth_' . $page->id, get_string('width', 'local_certification'));
        $mform->setType('pagewidth_' . $page->id, PARAM_INT);
        $mform->setDefault('pagewidth_' . $page->id, '210');
        $mform->addRule('pagewidth_' . $page->id, null, 'required', null, 'client');
        $mform->addHelpButton('pagewidth_' . $page->id, 'width', 'local_certification');

        $mform->addElement('text', 'pageheight_' . $page->id, get_string('height', 'local_certification'));
        $mform->setType('pageheight_' . $page->id, PARAM_INT);
        $mform->setDefault('pageheight_' . $page->id, '297');
        $mform->addRule('pageheight_' . $page->id, null, 'required', null, 'client');
        $mform->addHelpButton('pageheight_' . $page->id, 'height', 'local_certification');

        $mform->addElement('text', 'pageleftmargin_' . $page->id, get_string('leftmargin', 'local_certification'));
        $mform->setType('pageleftmargin_' . $page->id, PARAM_INT);
        $mform->setDefault('pageleftmargin_' . $page->id, 0);
        $mform->addHelpButton('pageleftmargin_' . $page->id, 'leftmargin', 'local_certification');

        $mform->addElement('text', 'pagerightmargin_' . $page->id, get_string('rightmargin', 'local_certification'));
        $mform->setType('pagerightmargin_' . $page->id, PARAM_INT);
        $mform->setDefault('pagerightmargin_' . $page->id, 0);
        $mform->addHelpButton('pagerightmargin_' . $page->id, 'rightmargin', 'local_certification');

        // Check if there are elements to add.
        if ($elements = $DB->get_records('local_certification_elements', array('pageid' => $page->id), 'sequence ASC')) {
            // Get the total number of elements.
            $numelements = count($elements);
            // Create a table to display these elements.
            $table = new \html_table();
            $table->attributes = array('class' => 'generaltable elementstable');
            $table->head  = array(get_string('name', 'local_certification'), get_string('type', 'local_certification'), '');
            $table->align = array('left', 'left', 'left');
            // Loop through and add the elements to the table.
            foreach ($elements as $element) {
                $elementname = new \core\output\inplace_editable('local_certification', 'elementname', $element->id,
                    true, format_string($element->name), $element->name);

                $row = new \html_table_row();
                $row->cells[] = $element->name;
                $row->cells[] = ucfirst($element->element);
                // Link to edit this element.
                $link = new \moodle_url($editelementlink, $editelementlinkparams + array('id' => $element->id,
                    'action' => 'edit'));
                $icons = $OUTPUT->action_icon($link, new \pix_icon('t/edit', get_string('edit')), null,
                    array('class' => 'action-icon edit-icon'));
                // Link to delete the element.
                $link = new \moodle_url($editlink, $editlinkparams + array('action' => 'deleteelement',
                    'aid' => $element->id));
                $icons .= $OUTPUT->action_icon($link, new \pix_icon('t/delete', get_string('delete')), null,
                    array('class' => 'action-icon delete-icon'));
                // Now display any moving arrows if they are needed.
                if ($numelements > 1) {
                    // Only display the move up arrow if it is not the first.
                    $moveicons = '';
                    if ($element->sequence > 1) {
                        $url = new \moodle_url($editlink, $editlinkparams + array('action' => 'emoveup',
                            'aid' => $element->id));
                        $moveicons .= $OUTPUT->action_icon($url, new \pix_icon('t/up', get_string('moveup')));
                    }
                    // Only display the move down arrow if it is not the last.
                    if ($element->sequence < $numelements) {
                        $url = new \moodle_url($editlink, $editlinkparams + array('action' => 'emovedown',
                            'aid' => $element->id));
                        $moveicons .= $OUTPUT->action_icon($url, new \pix_icon('t/down', get_string('movedown')));
                    }
                    $icons .= $moveicons;
                }
                $row->cells[] = $icons;
                $table->data[] = $row;
            }
            // Create link to order the elements.
            $link = \html_writer::link(new \moodle_url('/local/certification/rearrange.php', array('ctid' => $page->id)),
                get_string('rearrangeelements', 'local_certification'));
            // Add the table to the form.
            $mform->addElement('static', 'elements_' . $page->id, get_string('elements', 'local_certification'), \html_writer::table($table)
                . \html_writer::tag( 'div', $link));
            $mform->addHelpButton('elements_' . $page->id, 'elements', 'local_certification');
        }
        //$mform->addElement('html',  \html_writer::tag('span',get_string('elements_help', 'local_certification'),array('class'=>'tag tag-danger')));
        $group = array();
        $element_helper=new \local_certification\element_helper();
        $group[] = $mform->createElement('select', 'element_' . $page->id, '', $element_helper->get_available_element_types());
        if(empty($formstatus)){
            $group[] = $mform->createElement('submit', 'addelement_' . $page->id, get_string('addelement', 'local_certification'),
            array(), false);
        }else{
         $group[] = $mform->createElement('html',"<a href='#' onclick ='(function(e){ require(\"local_certification/addelements\").init({selector:\"pdf\",contextid:1,templateid:1,elementid:0,action:\"add\"}) })(event)'>".get_string('addelement', 'local_certification')."</a>");
        }
        $mform->addElement('group', 'elementgroup', '', $group, '', false);
   

        // Add option to delete this page if there is more than one page.
        if ($this->numpages > 1) {
            // Link to delete the page.
            $deletelink = new \moodle_url($editlink, $editlinkparams + array('action' => 'deletepage', 'aid' => $page->id));
            $icon = $OUTPUT->pix_icon('t/delete', get_string('deletecertpage', 'local_certification'));
            $deletepagehtml = \html_writer::link($deletelink, $icon . get_string('deletecertpage', 'local_certification'));
            $mform->addElement('html', \html_writer::tag('div', $deletepagehtml, array('class' => 'deletebutton')));
        }
    }
}
