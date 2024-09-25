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
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * This is the external API for this tool.
 *
 * @copyright  2016 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends \external_api {

    /**
     * Returns the save_element() parameters.
     *
     * @return \external_function_parameters
     */
    public static function save_element_parameters() {
        return new \external_function_parameters(
            array(
                'templateid' => new \external_value(PARAM_INT, 'The template id'),
                'elementid' => new \external_value(PARAM_INT, 'The element id'),
                'values' => new \external_multiple_structure(
                    new \external_single_structure(
                        array(
                            'name' => new \external_value(PARAM_ALPHANUMEXT, 'The field to update'),
                            'value' => new \external_value(PARAM_RAW, 'The value of the field'),
                        )
                    )
                )
            )
        );
    }

    /**
     * Handles saving element data.
     *
     * @param int $templateid The template id.
     * @param int $elementid The element id.
     * @param array $values The values to save
     * @return array
     */
    public static function save_element($templateid, $elementid, $values) {
        global $DB;

        $params = array(
            'templateid' => $templateid,
            'elementid' => $elementid,
            'values' => $values
        );
        self::validate_parameters(self::save_element_parameters(), $params);

        $template = $DB->get_record('local_certification_templts', array('id' => $templateid), '*', MUST_EXIST);
        $element = $DB->get_record('local_certification_elements', array('id' => $elementid), '*', MUST_EXIST);

        // Set the template.
        $template = new \local_certification\template($template);

        // Perform checks.
        //if ($cm = $template->get_cm()) {
        //    self::validate_context(\context_localule::instance($cm->id));
        //} else {
            self::validate_context(\context_system::instance());
        //}
        // Make sure the user has the required capabilities.
        //$template->require_manage();

        // Set the values we are going to save.
        $data = new \stdClass();
        $data->id = $element->id;
        $data->name = $element->name;
        foreach ($values as $value) {
            $field = $value['name'];
            $data->$field = $value['value'];
        }

        // Get an instance of the element class.
        //print_object($element);
        $e = \local_certification\element_factory::get_element_instance($element);
         //print_object($e);
         //print_object($data);
            return $e->save_form_elements($data);
       

        return false;
    }

    /**
     * Returns the save_element result value.
     *
     * @return \external_value
     */
    public static function save_element_returns() {
        return new \external_value(PARAM_BOOL, 'True if successful, false otherwise');
    }

    /**
     * Returns get_element() parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_element_html_parameters() {
        return new \external_function_parameters(
            array(
                'templateid' => new \external_value(PARAM_INT, 'The template id'),
                'elementid' => new \external_value(PARAM_INT, 'The element id'),
            )
        );
    }

    /**
     * Handles return the element's HTML.
     *
     * @param int $templateid The template id
     * @param int $elementid The element id.
     * @return string
     */
    public static function get_element_html($templateid, $elementid) {
        global $DB;

        $params = array(
            'templateid' => $templateid,
            'elementid' => $elementid
        );
        self::validate_parameters(self::get_element_html_parameters(), $params);

        $template = $DB->get_record('local_certification_templts', array('id' => $templateid), '*', MUST_EXIST);
        $element = $DB->get_record('local_certification_elements', array('id' => $elementid), '*', MUST_EXIST);

        // Set the template.
        $template = new \local_certification\template($template);

        // Perform checks.
        //if ($cm = $template->get_cm()) {
        //    self::validate_context(\context_localule::instance($cm->id));
        //} else {
            self::validate_context(\context_system::instance());
        //}

        // Get an instance of the element class.
        $e = \local_certification\element_factory::get_element_instance($element);
        
            return $e->render_html();
      

        return '';
    }

    /**
     * Returns the get_element result value.
     *
     * @return \external_value
     */
    public static function get_element_html_returns() {
        return new \external_value(PARAM_RAW, 'The HTML');
    }
    
    /*Raju*/

    public static function submit_elements_form_parameters() {
            return new \external_function_parameters(
                array(
                    'contextid' => new \external_value(PARAM_INT, 'The context id for the evaluation'),
                     'templateid' => new \external_value(PARAM_INT, 'The template id'),
                    'action'=>new \external_value(PARAM_RAW, 'The action'),
                    'element' => new \external_value(PARAM_RAW, 'The element id'),
                    'jsonformdata' => new \external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
    
                )
            );
        }

    /**
     * form submission of institute name and returns instance of this object
     *
     * @param int $contextid
     * @param [string] $jsonformdata
     * @return institute form submits
     */
    public function submit_elements_form($contextid,$templateid,$action,$element,$jsonformdata){
        global $PAGE, $CFG, $DB;
     $params = array('contextid' => $contextid, 'templateid' =>$templateid,
                                     'action'=>$action,'element'=>$element,
                                     'jsonformdata' => $jsonformdata
        );
      
        // We always must pass webservice params through validate_parameters.
        self::validate_parameters(self::submit_elements_form_parameters(), $params);
        // $context = $params['contextid'];
         $context = \context_system::instance();
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        // throw new moodle_exception('Error in creation');
        // die;
        $data = array();

        parse_str($serialiseddata, $data);
         $element1=new \stdClass();

        $element1->element=$element;
        $mform = new \local_certification\form\edit_element_form(null, array('id' =>$templateid,'element'=>$element1), 'post', '', null, true, $data);
        
        $valdata = $mform->get_data();
   // print_object($valdata);
         if($valdata){
      
             if($valdata->id>0){
             
                 if ($action == 'edit') {
                     $valdata->id = $valdata->id;
                 } else {
                     $valdata->pageid = 1;
                 }
             } else{
             
                 if ($action == 'edit') {
                    $valdata->id = $valdata->id;
                 } else {
                    $valdata->pageid = 1;
                 }
             }
                // Set the id, or page id depending on if we are editing an element, or adding a new one.
                
                 // Set the element variable.
                $valdata->element = $element1->element;
 
                 // Get an instance of the element class.
                 $ele = \local_certification\element_factory::get_element_instance($valdata);
                 //print_object($ele);
                 if ($ele) {

                     $ele->save_form_elements($valdata);
                 }
         } else {
             // Generate a warning.
             throw new moodle_exception('Error in creation');
         }
    }


    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function submit_elements_form_returns() {
        return new \external_value(PARAM_INT, 'category id');
    }
 /**Raju**/   
}
