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
namespace certificationelement_date;

defined('MOODLE_INTERNAL') || die();

/**
 * Date - Course grade date
 */
define('CERTIFICATION_DATE_COURSE_GRADE', '0');

/**
 * Date - Issue
 */
define('CERTIFICATION_DATE_ISSUE', '-1');

/**
 * Date - Completion
 */
define('CERTIFICATION_DATE_COMPLETION', '-2');

/**
 * Date - Course start
 */
define('CERTIFICATION_DATE_COURSE_START', '-3');

/**
 * Date - Course end
 */
define('CERTIFICATION_DATE_COURSE_END', '-4');

require_once($CFG->dirroot . '/lib/grade/constants.php');

/**
 * The certification element date's core interaction API.
 *
 * @package    certificationelement_date
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element extends \local_certification\element {

    /**
     * This function renders the form elements when adding a certification element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        global $COURSE;

        // Get the possible date options.
        $dateoptions = array();
        $dateoptions[CERTIFICATION_DATE_ISSUE] = get_string('issueddate', 'certificationelement_date');
        $dateoptions[CERTIFICATION_DATE_COMPLETION] = get_string('completiondate', 'certificationelement_date');
        $dateoptions[CERTIFICATION_DATE_COURSE_START] = get_string('certificationstartdate', 'certificationelement_date');
        $dateoptions[CERTIFICATION_DATE_COURSE_END] = get_string('certificationenddate', 'certificationelement_date');
        // $dateoptions[CERTIFICATION_DATE_COURSE_GRADE] = get_string('coursegradedate', 'certificationelement_date');
        $dateoptions = $dateoptions;

        $mform->addElement('select', 'dateitem', get_string('dateitem', 'certificationelement_date'), $dateoptions);
        $mform->addHelpButton('dateitem', 'dateitem', 'certificationelement_date');

        $mform->addElement('select', 'dateformat', get_string('dateformat', 'certificationelement_date'), self::get_date_formats());
        $mform->addHelpButton('dateformat', 'dateformat', 'certificationelement_date');

        parent::render_form_elements($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in the
     * certification_elements table.
     *
     * @param \stdClass $data the form data
     * @return string the json encoded array
     */
    public function save_unique_data($data) {
        // Array of data we will be storing in the database.
        $arrtostore = array(
            'dateitem' => $data->dateitem,
            'dateformat' => $data->dateformat
        );

        // Encode these variables before saving into the DB.
        return json_encode($arrtostore);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        global $DB;

        // If there is no element data, we have nothing to display.
        if (empty($this->get_data())) {
            return;
        }

        $certificationid = \local_certification\element_helper::get_certificationid($this->id);

        // Decode the information stored in the database.
        $dateinfo = json_decode($this->get_data());
        $dateitem = $dateinfo->dateitem;
        $dateformat = $dateinfo->dateformat;

        // If we are previewing this certification then just show a demonstration date.
        if ($preview) {
            $date = time();
        } else {
            // Get the page.
            $page = $DB->get_record('local_certification_pages', array('id' => $this->get_pageid()), '*', MUST_EXIST);
            // Get the certification this page belongs to.
            $certification = $DB->get_record('local_certification', array('templateid' => $page->templateid), '*', MUST_EXIST);
            // Now we can get the issue for this user.
            $issue = $DB->get_record('local_certification_issues', array('userid' => $user->id, 'certificationid' => $certification->id),
                '*', MUST_EXIST);

            if ($dateitem == CERTIFICATION_DATE_ISSUE) {
                $date = $issue->timecreated;
            } else if ($dateitem == CERTIFICATION_DATE_COMPLETION) {
                // Get the last completion date.
                $sql = "SELECT MAX(c.completiondate) as timecompleted
                          FROM {local_certification_users} c
                         WHERE c.userid = :userid
                           AND c.certificationid = :certificationid AND completion_status=:completionstatus";
                if ($timecompleted = $DB->get_record_sql($sql, array('userid' => $issue->userid, 'certificationid' => $certificationid,'completionstatus'=>1))) {
                    if (!empty($timecompleted->timecompleted)) {
                        $date = $timecompleted->timecompleted;
                    }
                }
            } else if ($dateitem == CERTIFICATION_DATE_COURSE_START) {
                $date = $DB->get_field('local_certification', 'startdate', array('id' => $certificationid));
            } else if ($dateitem == CERTIFICATION_DATE_COURSE_END) {
                $date = $DB->get_field('local_certification', 'enddate', array('id' => $certificationid));
             }
            //  else {
            //     if ($dateitem == CERTIFICATION_DATE_COURSE_GRADE) {
            //         $grade = \local_certification\element_helper::get_course_grade_info(
            //             $certificationid,
            //             GRADE_DISPLAY_TYPE_DEFAULT,
            //             $user->id
            //         );
            //     } else if (strpos($dateitem, 'gradeitem:') === 0) {
            //         $gradeitemid = substr($dateitem, 10);
            //         $grade = \local_certification\element_helper::get_grade_item_info(
            //             $gradeitemid,
            //             $dateitem,
            //             $user->id
            //         );
            //     } else {
            //         $grade = \local_certification\element_helper::get_local_grade_info(
            //             $dateitem,
            //             GRADE_DISPLAY_TYPE_DEFAULT,
            //             $user->id
            //         );
            //     }

            //     if ($grade && !empty($grade->get_dategraded())) {
            //         $date = $grade->get_dategraded();
            //     }
            // }
        }

        // Ensure that a date has been set.
        if (!empty($date)) {
            \local_certification\element_helper::render_content($pdf, $this, $this->get_date_format_string($date, $dateformat));
        }
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        // If there is no element data, we have nothing to display.
        if (empty($this->get_data())) {
            return;
        }

        // Decode the information stored in the database.
        $dateinfo = json_decode($this->get_data());
        $dateformat = $dateinfo->dateformat;

        return \local_certification\element_helper::render_html_content($this, $this->get_date_format_string(time(), $dateformat));
    }

    /**
     * Sets the data on the form when editing an element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        // Set the item and format for this element.
        if (!empty($this->get_data())) {
            $dateinfo = json_decode($this->get_data());

            $element = $mform->getElement('dateitem');
            $element->setValue($dateinfo->dateitem);

            $element = $mform->getElement('dateformat');
            $element->setValue($dateinfo->dateformat);
        }

        parent::definition_after_data($mform);
    }

    /**
     * This function is responsible for handling the restoration process of the element.
     *
     * We will want to update the course localule the date element is pointing to as it will
     * have changed in the course restore.
     *
     * @param \restore_certification_activity_task $restore
     */
    public function after_restore($restore) {
        global $DB;

        $dateinfo = json_decode($this->get_data());
        if ($newitem = \restore_dbops::get_backup_ids_record($restore->get_restoreid(), 'course_localule', $dateinfo->dateitem)) {
            $dateinfo->dateitem = $newitem->newitemid;
            $DB->set_field('local_certification_elements', 'data', $this->save_unique_data($dateinfo), array('id' => $this->get_id()));
        }
    }

    /**
     * Helper function to return all the date formats.
     *
     * @return array the list of date formats
     */
    public static function get_date_formats() {
        $date = time();

        $suffix = self::get_ordinal_number_suffix(userdate($date, '%d'));

        $dateformats = array(
            1 => userdate($date, '%B %d, %Y'),
            2 => userdate($date, '%B %d' . $suffix . ', %Y'),
            'strftimedate' => userdate($date, get_string('strftimedate', 'langconfig')),
            'strftimedatefullshort' => userdate($date, get_string('strftimedatefullshort', 'langconfig')),
            'strftimedateshort' => userdate($date, get_string('strftimedateshort', 'langconfig')),
            'strftimedatetime' => userdate($date, get_string('strftimedatetime', 'langconfig')),
            'strftimedatetimeshort' => userdate($date, get_string('strftimedatetimeshort', 'langconfig')),
            'strftimedaydate' => userdate($date, get_string('strftimedaydate', 'langconfig')),
            'strftimedaydatetime' => userdate($date, get_string('strftimedaydatetime', 'langconfig')),
            'strftimedayshort' => userdate($date, get_string('strftimedayshort', 'langconfig')),
            'strftimedaytime' => userdate($date, get_string('strftimedaytime', 'langconfig')),
            'strftimemonthyear' => userdate($date, get_string('strftimemonthyear', 'langconfig')),
            'strftimerecent' => userdate($date, get_string('strftimerecent', 'langconfig')),
            'strftimerecentfull' => userdate($date, get_string('strftimerecentfull', 'langconfig')),
            'strftimetime' => userdate($date, get_string('strftimetime', 'langconfig'))
        );

        return $dateformats;
    }

    /**
     * Returns the date in a readable format.
     *
     * @param int $date
     * @param string $dateformat
     * @return string
     */
    protected function get_date_format_string($date, $dateformat) {
        // Keeping for backwards compatibility.
        if (is_number($dateformat)) {
            switch ($dateformat) {
                case 1:
                    $certificationdate = userdate($date, '%B %d, %Y');
                    break;
                case 2:
                    $suffix = self::get_ordinal_number_suffix(userdate($date, '%d'));
                    $certificationdate = userdate($date, '%B %d' . $suffix . ', %Y');
                    break;
                case 3:
                    $certificationdate = userdate($date, '%d %B %Y');
                    break;
                case 4:
                    $certificationdate = userdate($date, '%B %Y');
                    break;
                default:
                    $certificationdate = userdate($date, get_string('strftimedate', 'langconfig'));
            }
        }

        // Ok, so we must have been passed the actual format in the lang file.
        if (!isset($certificationdate)) {
            $certificationdate = userdate($date, get_string($dateformat, 'langconfig'));
        }

        return $certificationdate;
    }

    /**
     * Helper function to return the suffix of the day of
     * the month, eg 'st' if it is the 1st of the month.
     *
     * @param int $day the day of the month
     * @return string the suffix.
     */
    protected static function get_ordinal_number_suffix($day) {
        if (!in_array(($day % 100), array(11, 12, 13))) {
            switch ($day % 10) {
                // Handle 1st, 2nd, 3rd.
                case 1:
                    return get_string('numbersuffix_st_as_in_first', 'certificationelement_date');
                case 2:
                    return get_string('numbersuffix_nd_as_in_second', 'certificationelement_date');
                case 3:
                    return get_string('numbersuffix_rd_as_in_third', 'certificationelement_date');
            }
        }
        return 'th';
    }
}
