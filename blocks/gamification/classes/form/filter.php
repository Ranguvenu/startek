<?php
namespace block_gamification\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/itemspertime.php');
require_once(__DIR__ . '/duration.php');
require_once($CFG->libdir . '/outputcomponents.php');
use block_gamification\local\leaderboard\course_world_config;
use moodleform;
use html_writer;
use moodle_url;
class filter extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG,$DB,$OUTPUT,$COURSE,$PAGE;
        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $PAGE->requires->yui_module('moodle-block_gamification-exportfilter', 'M.block_gamification.init_exportfilter', array(array('formid' => $mform->getAttribute('id'))));
    	$filterset = [];

        $filters = $this->reporttypes();
        foreach($filters as $key=>$filter) {
            $filterset[$key] = ucwords($filter);
        }
        
        $mform->addelement('select', 'reporttype', get_string('type', 'block_gamification'), array_merge(['select'=>'Select'], $filterset));
        $mform->setType('reporttype', PARAM_RAW);
        // $mform->addRule('reporttype',  get_string('error'),  'required',  'required',  'client',  false,  false);

        $mform->addElement('hidden', 'durationfield');
        $mform->setType('durationfield', PARAM_RAW);

        $mform->registerNoSubmitButton('updatefields');
        $mform->addElement('submit', 'updatefields', get_string('updatefields', 'block_gamification'));

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit','block_gamification'));
        $buttonarray[] = $mform->createElement('cancel','block_gamification');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
     
    function definition_after_data() {
        global $DB,$CFG;
        $mform = $this->_form;
        $reporttype = $mform->getElementValue('reporttype');
        $options = [];
        if(is_array($reporttype) && !empty($reporttype) && $reporttype[0] != 'overall' && $reporttype[0] != 'select') {
            $options = $this->get_duration_values($reporttype[0]);
        
            $duration = $mform->createElement('select', 'duration', get_string('duration', 'block_gamification'), $options);
            $mform->insertElementBefore($duration, 'durationfield');
        }
        
        
        $event = $mform->createElement('select', 'event', get_string('event', 'block_gamification'), $this->get_active_event_options());
        $mform->insertElementBefore($event, 'durationfield');
        $event->setMultiple(true);
    }

    private function get_duration_values($name) {
        global $DB;
        $activeevents = $this->get_active_events();
        $max = [];
        foreach($activeevents as $event) {
            $table = 'block_gm_'.$name.'ly_'.$event->eventcode;
            $maxvalue = $DB->get_record_sql("SELECT max($name) as value from {{$table}}", []);
            $max[] = $maxvalue->value;
        }
        asort($max);
        $options = ['Select'];
        for ($i = 1; $i <= end($max); $i++) {
            $newname = ($i > 1) ? $name.'s' : $name ;
            $options[$i] = ucwords('last '.$i.' '.$newname);
        }
        return $options;
    }

    private function get_active_event_options() {
        $activeevents = $this->get_active_events();
        $options = [];
        foreach($activeevents as $event) {
            $options[$event->eventcode] = ucwords(str_replace('_', ' ', $event->shortname));
        }
        return $options;
    }

    private function reporttypes() {
        return ['overall'=>get_string('overall','block_gamification'), 'week'=>get_string('weekly','block_gamification'), 'month'=>get_string('monthly','block_gamification')];
    }

    private function get_active_events() {
        global $DB;
        $site = new \block_gamification\local\events\eventslib($DB);
        return $site->get_active_events();
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $data = (object) $data;

        if($data->reporttype == 'select') {
            $errors['reporttype'] = get_string('selectreporttype', 'block_gamification');
            return $errors;
        }
        if($data->reporttype != 'overall' && ($data->duration == '' || $data->duration == 0)) {
            $errors['duration'] = get_string('selectduration', 'block_gamification');
        }
        if(!isset($data->event)) {
            $errors['event'] = get_string('selecteventtype', 'block_gamification');
        }
        return $errors;
    }

} 
