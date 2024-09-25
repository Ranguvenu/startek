
<?php

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/externallib.php");
class local_certificates_external extends external_api {

        /**
     * Describes the parameters for submit_create_group_form webservice.
     * @return external_function_parameters
     */
    public static function submit_certificate_form_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the evaluation'),
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),

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
    public function submit_certificate_form($contextid, $jsonformdata){
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/location/lib.php');
        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_instituteform_form_parameters(),
                                    ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);
        // $context = $params['contextid'];
        $context = context_system::instance();
        // We always must call validate_context in a webservice.
        self::validate_context($context);
        $serialiseddata = json_decode($params['jsonformdata']);
        // throw new moodle_exception('Error in creation');
        // die;
        $data = array();

        parse_str($serialiseddata, $data);
        $warnings = array();
         $mform = new local_location\form\instituteform(null, array(), 'post', '', null, true, $data);
        $institutes  = new local_location\event\location();
        $valdata = $mform->get_data();

        if($valdata){
            if($valdata->id>0){

                $institutes->institute_update_instance($valdata);
            } else{

                $institutes->institute_insert_instance($valdata);
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
    public static function submit_certificate_form_returns() {
        return new external_value(PARAM_INT, 'certificate id');
    }


    /**
     * Returns get_element() parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_element_html_parameters() {
        return new \external_function_parameters(
            array(
                'templateid' => new \external_value(PARAM_INT, 'The certificate id'),
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
    public static function get_element_html($certificateid, $elementid) {
        global $DB;

        $params = array(
            'templateid' => $certificateid,
            'elementid' => $elementid
        );
        self::validate_parameters(self::get_element_html_parameters(), $params);

        $certificate = $DB->get_record('local_certificate', array('id' => $certificateid), '*', MUST_EXIST);
        $element = $DB->get_record('local_certificate_elements', array('id' => $elementid), '*', MUST_EXIST);

        // Set the template.
        $template = new \local_certificates\template($certificate);

        // Perform checks.
        //if ($cm = $template->get_cm()) {
        //    self::validate_context(\context_localule::instance($cm->id));
        //} else {
            self::validate_context(\context_system::instance());
        //}

        // Get an instance of the element class.
        $e = \local_certificates\element_factory::get_element_instance($element);
        
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

    /**
     * Returns the save_element() parameters.
     *
     * @return \external_function_parameters
     */
    public static function save_element_parameters() {
        return new \external_function_parameters(
            array(
                'templateid' => new \external_value(PARAM_INT, 'The certificate id'),
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
    public static function save_element($certificateid, $elementid, $values) {
        global $DB;

        $params = array(
            'templateid' => $certificateid,
            'elementid' => $elementid,
            'values' => $values
        );
        self::validate_parameters(self::save_element_parameters(), $params);

        $template = $DB->get_record('local_certificate', array('id' => $certificateid), '*', MUST_EXIST);
        $element = $DB->get_record('local_certificate_elements', array('id' => $elementid), '*', MUST_EXIST);

        // Set the template.
        $template = new \local_certificates\template($template);

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
        $e = \local_certificates\element_factory::get_element_instance($element);
        
        return $e->save_form_elements($data);
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
     * Returns the save_element() parameters.
     *
     * @return \external_function_parameters
     */
    public static function delete_certificate_parameters() {
        return new \external_function_parameters(
            array(
                'certificateid' => new \external_value(PARAM_INT, 'Id of the certificate')
            )
        );
    }

    /**
     * Handles saving element data.
     *
     * @param int $certificateid Id of the Certificate.
     * @return boolean
     */
    public static function delete_certificate($certificateid) {
        global $DB;
        // print_object($certificateid);
        $params = array(
            'certificateid' => $certificateid
        );
        self::validate_parameters(self::delete_certificate_parameters(), $params);
        $certificateinfo = $DB->get_record('local_certificate',array('id'=>$certificateid),'id,name');
        // $certificateinfo->contextid = 1;
        $cert = new \local_certificates\template($certificateinfo);
        $deleted = $cert->delete();

        if($deleted){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Returns the save_element result value.
     *
     * @return \external_value
     */
    public static function delete_certificate_returns() {
        return new \external_value(PARAM_BOOL, 'True if successful, false otherwise');
    }

}
