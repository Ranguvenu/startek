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
 * @subpackage local_certificates
 */

namespace local_certificates;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/grade/constants.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * Class helper.
 *
 * Provides useful functions related to elements.
 *
 * @package    local_certificates
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element_helper {

    /**
     * @var int the top-left of element
     */
    const CERTIFICATION_REF_POINT_TOPLEFT = 0;

    /**
     * @var int the top-center of element
     */
    const CERTIFICATION_REF_POINT_TOPCENTER = 1;

    /**
     * @var int the top-left of element
     */
    const CERTIFICATION_REF_POINT_TOPRIGHT = 2;

    /**
     * Common behaviour for rendering specified content on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param \local_certification\element $element the certification element
     * @param string $content the content to render
     */
    public static function render_content($pdf, $element, $content) {
        list($font, $attr) = self::get_font($element);
        $pdf->setFont($font, $attr, $element->get_fontsize());
        $fontcolour = \TCPDF_COLORS::convertHTMLColorToDec($element->get_colour(), $fontcolour);
        $pdf->SetTextColor($fontcolour['R'], $fontcolour['G'], $fontcolour['B']);

        $x = $element->get_posx();
        $y = $element->get_posy();
        $w = $element->get_width();
        $refpoint = $element->get_refpoint();
        $actualwidth = $pdf->GetStringWidth($content);

        if ($w and $w < $actualwidth) {
            $actualwidth = $w;
        }

        switch ($refpoint) {
            case self::CERTIFICATION_REF_POINT_TOPRIGHT:
                $x = $element->get_posx() - $actualwidth;
                if ($x < 0) {
                    $x = 0;
                    $w = $element->get_posx();
                } else {
                    $w = $actualwidth;
                }
                break;
            case self::CERTIFICATION_REF_POINT_TOPCENTER:
                $x = $element->get_posx() - $actualwidth / 2;
                if ($x < 0) {
                    $x = 0;
                    $w = $element->get_posx() * 2;
                } else {
                    $w = $actualwidth;
                }
                break;
        }

        if ($w) {
            $w += 0.0001;
        }
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->writeHTMLCell($w, 0, $x, $y, $content, 0, 0, false, true);
    }

    /**
     * Common behaviour for rendering specified content on the drag and drop page.
     *
     * @param \local_certification\element $element the certification element
     * @param string $content the content to render
     * @return string the html
     */
    public static function render_html_content($element, $content) {
        list($font, $attr) = self::get_font($element);
        $fontstyle = 'font-family: ' . $font;
        if (strpos($attr, 'B') !== false) {
            $fontstyle .= '; font-weight: bold';
        }
        if (strpos($attr, 'I') !== false) {
            $fontstyle .= '; font-style: italic';
        }

        $style = $fontstyle . '; color: ' . $element->get_colour() . '; font-size: ' . $element->get_fontsize() . 'pt;';
        if ($element->get_width()) {
            $style .= ' width: ' . $element->get_width() . 'mm';
        }
        return \html_writer::div($content, '', array('style' => $style));
    }

    /**
     * Helper function to render the font elements.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance.
     */
    public static function render_form_element_font($mform) {
        $mform->addElement('select', 'font', get_string('font','local_certificates'), \local_certification\certification::get_fonts());
        $mform->setType('font', PARAM_TEXT);
        $mform->setDefault('font', 'times');
        $mform->addHelpButton('font', 'font','local_certification');
        $mform->addElement('select', 'fontsize', get_string('fontsize','local_certificates'),
            \local_certification\certification::get_font_sizes());
        $mform->setType('fontsize', PARAM_INT);
        $mform->setDefault('fontsize', 12);
        $mform->addHelpButton('fontsize', 'fontsize','local_certificates');
    }

    /**
     * Helper function to render the colour elements.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance.
     */
    public static function render_form_element_colour($mform) {
        $mform->addElement('certificates_colourpicker', 'colour', get_string('fontcolour','local_certificates'));
        $mform->setType('colour', PARAM_RAW); // Need to validate that this is a valid colour.
        $mform->setDefault('colour', '#000000');
        $mform->addHelpButton('colour', 'fontcolour','local_certificates');
    }

    /**
     * Helper function to render the position elements.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance.
     */
    public static function render_form_element_position($mform) {
        $mform->addElement('text', 'posx', get_string('posx','local_certificates'), array('size' => 10));
        $mform->setType('posx', PARAM_INT);
        $mform->setDefault('posx', 0);
        $mform->addHelpButton('posx', 'posx','local_certificates');
        $mform->addElement('text', 'posy', get_string('posy','local_certificates'), array('size' => 10));
        $mform->setType('posy', PARAM_INT);
        $mform->setDefault('posy', 0);
        $mform->addHelpButton('posy', 'posy','local_certification');
    }

    /**
     * Helper function to render the width element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance.
     */
    public static function render_form_element_width($mform) {
        $mform->addElement('text', 'width', get_string('elementwidth','local_certificates'), array('size' => 10));
        $mform->setType('width', PARAM_INT);
        $mform->setDefault('width', 0);
        $mform->addHelpButton('width', 'elementwidth','local_certificates');
        $refpointoptions = array();
        $refpointoptions[self::CERTIFICATION_REF_POINT_TOPLEFT] = get_string('topleft','local_certificates');
        $refpointoptions[self::CERTIFICATION_REF_POINT_TOPCENTER] = get_string('topcenter','local_certificates');
        $refpointoptions[self::CERTIFICATION_REF_POINT_TOPRIGHT] = get_string('topright','local_certificates');
        $mform->addElement('select', 'refpoint', get_string('refpoint','local_certificates'), $refpointoptions);
        $mform->setType('refpoint', PARAM_INT);
        $mform->setDefault('refpoint', self::CERTIFICATION_REF_POINT_TOPCENTER);
        $mform->addHelpButton('refpoint', 'refpoint','local_certificates');
    }

    /**
     * Helper function to performs validation on the colour element.
     *
     * @param array $data the submitted data
     * @return array the validation errors
     */
    public static function validate_form_element_colour($data) {
        $errors = array();
        // Validate the colour.
        if (!self::validate_colour($data['colour'])) {
            $errors['colour'] = get_string('invalidcolour','local_certificates');
        }
        return $errors;
    }

    /**
     * Helper function to performs validation on the position elements.
     *
     * @param array $data the submitted data
     * @return array the validation errors
     */
    public static function validate_form_element_position($data) {
        $errors = array();

        // Check if posx is not set, or not numeric or less than 0.
        if ((!isset($data['posx'])) || (!is_numeric($data['posx'])) || ($data['posx'] < 0)) {
            $errors['posx'] = get_string('invalidposition', 'local_certificates', 'X');
        }
        // Check if posy is not set, or not numeric or less than 0.
        if ((!isset($data['posy'])) || (!is_numeric($data['posy'])) || ($data['posy'] < 0)) {
            $errors['posy'] = get_string('invalidposition', 'local_certificates', 'Y');
        }

        return $errors;
    }

    /**
     * Helper function to perform validation on the width element.
     *
     * @param array $data the submitted data
     * @return array the validation errors
     */
    public static function validate_form_element_width($data) {
        $errors = array();

        // Check if width is less than 0.
        if (isset($data['width']) && $data['width'] < 0) {
            $errors['width'] = get_string('invalidelementwidth','local_certificates');
        }

        return $errors;
    }

    /**
     * Returns the font used for this element.
     *
     * @param \local_certificates\element $element the certification element
     * @return array the font and font attributes
     */
    public static function get_font($element) {
        // Variable for the font.
        $font = $element->get_font();
        // Get the last two characters of the font name.
        $fontlength = strlen($font);
        $lastchar = $font[$fontlength - 1];
        $secondlastchar = $font[$fontlength - 2];
        // The attributes of the font.
        $attr = '';
        // Check if the last character is 'i'.
        if ($lastchar == 'i') {
            // Remove the 'i' from the font name.
            $font = substr($font, 0, -1);
            // Check if the second last char is b.
            if ($secondlastchar == 'b') {
                // Remove the 'b' from the font name.
                $font = substr($font, 0, -1);
                $attr .= 'B';
            }
            $attr .= 'I';
        } else if ($lastchar == 'b') {
            // Remove the 'b' from the font name.
            $font = substr($font, 0, -1);
            $attr .= 'B';
        }
        return array($font, $attr);
    }

    /**
     * Validates the colour selected.
     *
     * @param string $colour
     * @return bool returns true if the colour is valid, false otherwise
     */
    public static function validate_colour($colour) {
        // List of valid HTML colour names.
        $colournames = array(
            'aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure',
            'beige', 'bisque', 'black', 'blanchedalmond', 'blue',
            'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse',
            'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson',
            'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray',
            'darkgrey', 'darkgreen', 'darkkhaki', 'darkmagenta',
            'darkolivegreen', 'darkorange', 'darkorchid', 'darkred',
            'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray',
            'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink',
            'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick',
            'floralwhite', 'forestgreen', 'fuchsia', 'gainsboro',
            'ghostwhite', 'gold', 'goldenrod', 'gray', 'grey', 'green',
            'greenyellow', 'honeydew', 'hotpink', 'indianred', 'indigo',
            'ivory', 'khaki', 'lavender', 'lavenderblush', 'lawngreen',
            'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan',
            'lightgoldenrodyellow', 'lightgray', 'lightgrey', 'lightgreen',
            'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue',
            'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow',
            'lime', 'limegreen', 'linen', 'magenta', 'maroon',
            'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple',
            'mediumseagreen', 'mediumslateblue', 'mediumspringgreen',
            'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream',
            'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace', 'olive',
            'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod',
            'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip',
            'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red',
            'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown',
            'seagreen', 'seashell', 'sienna', 'silver', 'skyblue', 'slateblue',
            'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan',
            'teal', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white',
            'whitesmoke', 'yellow', 'yellowgreen'
        );

        if (preg_match('/^#?([[:xdigit:]]{3}){1,2}$/', $colour)) {
            return true;
        } else if (in_array(strtolower($colour), $colournames)) {
            return true;
        }

        return false;
    }

    /**
     * Helper function that returns the sequence on a specified certification page for a
     * newly created element.
     *
     * @param int $pageid the id of the page we are adding this element to
     * @return int the element number
     */
    public static function get_element_sequence($pageid) {
        global $DB;

        // Set the sequence of the element we are creating.
        $sequence = 1;
        // Check if there already elements that exist, if so, overwrite value.
        $sql = "SELECT MAX(sequence) as maxsequence
                  FROM {local_certificate_elements}
                 WHERE pageid = :id";
        // Get the current max sequence on this page and add 1 to get the new sequence.
        if ($maxseq = $DB->get_record_sql($sql, array('id' => $pageid))) {
            $sequence = $maxseq->maxsequence + 1;
        }

        return $sequence;
    }

    /**
     * Helper function that returns the course id for this element.
     *
     * @param int $elementid The element id
     * @return int The course id
     */
    public static function get_certificationid($elementid) {
        global $DB, $SITE;

        $sql = "SELECT c.id
                FROM {local_certificate} c
                JOIN {local_certificate_pages} cp ON c.id = cp.certificateid
                JOIN {local_certificate_elements} ce ON cp.id = ce.pageid
                WHERE ce.id = :elementid";

        // Check if there is a course associated with this element.
        if ($certification = $DB->get_record_sql($sql, array('elementid' => $elementid))) {
            return $certification->id;
        } else { // Must be in a site template.
            return $SITE->id;
        }
    }

    /**
     * Return the list of possible elements to add.
     *
     * @return array the list of element types that can be used.
     */
    public static function get_available_element_types() {
        global $CFG;

        // Array to store the element types.
        $options = array();

        // Check that the directory exists.
        $elementdir = "$CFG->dirroot/local/certificates/element";
        if (file_exists($elementdir)) {
            // Get directory contents.
            $elementfolders = new \DirectoryIterator($elementdir);

            // Loop through the elements folder.
            foreach ($elementfolders as $elementfolder) {
                // If it is not a directory or it is '.' or '..', skip it.
                if (!$elementfolder->isDir() || $elementfolder->isDot()) {
                    continue;
                }
                // Check that the standard class exists, if not we do
                // not want to display it as an option as it will not work.
                $foldername = $elementfolder->getFilename();
                  // print_object($foldername);
                // Get the class name.
                $classname = '\\certificateelement_' . $foldername . '\\element';
                 // print_object($classname);
                // Ensure the necessary class exists.
                if (class_exists($classname)) {
                    $component = "certificateelement_{$foldername}";
                    $options[$foldername] = get_string('pluginname', $component);
                }
            }
        }

        \core_collator::asort($options);
        return $options;
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     * @param obj $moduleinfo having information with moduletype and module
    **/
    public static function get_modulename($elementid,$user=2, $moduleinfo = false) {
        global $DB;

        if($moduleinfo){
            switch ($moduleinfo->moduletype) {
                case 'course':
                    $mname = $DB->get_field('course', 'fullname', array('id'=>$moduleinfo->moduleid));
                    break;
                case 'classroom':
                    $mname = $DB->get_field('local_classroom', 'name', array('id'=>$moduleinfo->moduleid));
                    break;
                case 'learningplan':
                    $mname = $DB->get_field('local_learningplan', 'name', array('id'=>$moduleinfo->moduleid));
                    break;
                case 'program':
                    $mname = $DB->get_field('local_program', 'name', array('id'=>$moduleinfo->moduleid));
                    break;
                case 'onlinetest':
                    $mname = $DB->get_field('local_onlinetests', 'name', array('id'=>$moduleinfo->moduleid));
                    break;
                default:
                    $mname = 'Module name';
                    break;
            }
        }else{
            $mname = 'Module name';
        }
        
        return $mname;
    }
    public static function generate_certificationcode($elementid,$user=2) {
        global $DB, $SITE;

        $sql = "SELECT c.name
                  FROM {local_certificate} c
            INNER JOIN {local_certificate_pages} cp
                    ON c.templateid = cp.templateid
            INNER JOIN {local_certificate_elements} ce
                    ON cp.id = ce.pageid
                ";
                $usercontext = \context_system::instance();
       if(!has_capability('local/certificates:manage',$usercontext)){
            $sql.=" INNER JOIN {local_certificate_users} cu
                    ON cu.certificationid = c.id";
        }
        $sql.=" WHERE ce.id = :elementid";
        // Check if there is a course associated with this element.
        if ($certification = $DB->get_record_sql($sql, array('elementid' => $elementid))) {
            return $certification->name;
        } else { // Must be in a site template.
            return $SITE->id;
        }
    }
}
