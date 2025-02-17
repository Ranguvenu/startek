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
namespace certificateelement_bgimage;

defined('MOODLE_INTERNAL') || die();

class element extends \certificateelement_image\element {

    /**
     * This function renders the form elements when adding a certification element.
     *
     * @param \local_certification\edit_element_form $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        // $mform->addElement('select', 'fileid', get_string('image', 'certificateelement_image'), self::get_images());
      $mform->addElement('filemanager', 'certificationimage', get_string('uploadimage', 'local_certificates'),null,array('subdirs' => 0, 'maxbytes' => $maxbytes, 'areamaxbytes' => 10485760, 'maxfiles' => 1),
            $this->filemanageroptions);
    }

    /**
     * Performs validation on the element values.
     *
     * @param array $data the submitted data
     * @param array $files the submitted files
     * @return array the validation errors
     */
    public function validate_form_elements($data, $files) {
        // Array to return the errors.
        return array();
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        // If there is no element data, we have nothing to display.
        if (empty($this->get_data())) {
            return;
        }

        $imageinfo = json_decode($this->get_data());

        // If there is no file, we have nothing to display.
        if (empty($imageinfo->filename)) {
            return;
        }

        if ($file = $this->get_file()) {
            $location = make_request_directory() . '/target';
            $file->copy_content_to($location);

            // Set the image to the size of the PDF page.
            $mimetype = $file->get_mimetype();
            if ($mimetype == 'image/svg+xml') {
                $pdf->ImageSVG($location, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight());
            } else {
                $pdf->Image($location, 0, 0, $pdf->getPageWidth(), $pdf->getPageHeight());
            }
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
        global $DB;

        // If there is no element data, we have nothing to display.
        if (empty($this->get_data())) {
            return '';
        }

        $imageinfo = json_decode($this->get_data());

        // If there is no file, we have nothing to display.
        if (empty($imageinfo->filename)) {
            return '';
        }

        if ($file = $this->get_file()) {
            $url = \moodle_url::make_pluginfile_url($file->get_contextid(), 'local_certificates', 'image', $file->get_itemid(),
                $file->get_filepath(), $file->get_filename());
            // Get the page we are rendering this on.
            $page = $DB->get_record('local_certificate_pages', array('id' => $this->get_pageid()), '*', MUST_EXIST);

            // Set the image to the size of the page.
            $style = 'width: ' . $page->width . 'mm; height: ' . $page->height . 'mm';
            return \html_writer::tag('img', '', array('src' => $url, 'style' => $style));
        }
    }

    /**
     * Fetch stored file.
     *
     * @return \stored_file|bool stored_file instance if exists, false if not
     */
    public function get_file() {
        $imageinfo = json_decode($this->get_data());
        
        $fs = get_file_storage();

        return $fs->get_file($imageinfo->contextid, 'local_certificates', $imageinfo->filearea,
                    $imageinfo->itemid, $imageinfo->filepath, $imageinfo->filename);
           
    }
}

