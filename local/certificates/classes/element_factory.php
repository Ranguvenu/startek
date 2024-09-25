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
 * @package BizLMS
 * @subpackage local_certificates
 */

namespace local_certificates;
defined('MOODLE_INTERNAL') || die();

/**
 * The factory class responsible for creating custom certificates instances.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class element_factory {

    /**
     * Returns an instance of the element class.
     *
     * @param \stdClass $element the element
     * @return \local_certificates\element|bool returns the instance of the element class, or false if element
     *         class does not exists.
     */
    public static function get_element_instance($element) {
        // Get the class name.
        $classname = '\\certificateelement_' . $element->element . '\\element';
        $data = new \stdClass();
        $data->id = isset($element->id) ? $element->id : null;
        $data->pageid = isset($element->pageid) ? $element->pageid : null;
        $data->name = isset($element->name) ? $element->name : get_string('pluginname', 'certificateelement_' . $element->element);
        $data->element = $element->element;
        $data->data = isset($element->data) ? $element->data : null;
        $data->font = isset($element->font) ? $element->font : null;
        $data->fontsize = isset($element->fontsize) ? $element->fontsize : null;
        $data->colour = isset($element->colour) ? $element->colour : null;
        $data->posx = isset($element->posx) ? $element->posx : null;
        $data->posy = isset($element->posy) ? $element->posy : null;
        $data->width = isset($element->width) ? $element->width : null;
        $data->refpoint = isset($element->refpoint) ? $element->refpoint : null;
        
        if($element->element == 'modulename'){
            $data->modulename = '{{modulename}}';
            $data->moduletype = isset($element->moduletype) ? $element->moduletype : null;
            $data->moduleid = isset($element->moduleid) ? $element->moduleid : null;
        }
        // Ensure the necessary class exists.
        if (class_exists($classname)) {
            $classdata = new $classname($data);
            return $classdata;
        }
        return false;
    }
}
