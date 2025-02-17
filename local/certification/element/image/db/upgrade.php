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

defined('MOODLE_INTERNAL') || die;

/**
 * Certification image element upgrade code.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_certificationelement_image_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2016120501) {
        // Go through each 'image' element and update the file stored information.
        if ($images = $DB->get_records_select('local_certification_elements', $DB->sql_compare_text('element') . ' = \'image\'')) {
            // Create a file storage instance we are going to use to create pathname hashes.
            $fs = get_file_storage();
            // Go through and update the details.
            foreach ($images as $image) {
                // Get the current data we have stored for this element.
                $elementinfo = json_decode($image->data);
                if ($file = $fs->get_file_by_hash($elementinfo->pathnamehash)) {
                    $arrtostore = array(
                        'contextid' => $file->get_contextid(),
                        'filearea' => $file->get_filearea(),
                        'itemid' => $file->get_itemid(),
                        'filepath' => $file->get_filepath(),
                        'filename' => $file->get_filename(),
                        'width' => (int) $elementinfo->width,
                        'height' => (int) $elementinfo->height
                    );
                    $arrtostore = json_encode($arrtostore);
                    $DB->set_field('local_certification_elements', 'data', $arrtostore,  array('id' => $image->id));
                }
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2016120501, 'certificationelement', 'image');
    }

    return true;
}
