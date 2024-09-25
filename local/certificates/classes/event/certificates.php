<?php
namespace local_certificates\event;
use stdClass;
defined('MOODLE_INTERNAL') or die;
use stdclass;
class certificates{
    /**
     * @param [object] $data
     * @return institute updated
     */
    public function certificates_update_instance($data){
    	global $DB, $USER;
    	
      $data->timecreated = time();
      $data->usercreated = $USER->id;
      $data->timemodified = time();
      $data->usermodified = $USER->id;

      return $DB->update_record('local_certificate', $data);
    }
    /**
     * @param [integer] $id
     * @return institute form setdata
     */
    // public function set_data_institute($id){
    //     if($id > 0){
    //         global $DB;
    //   $userdata=$DB->get_record('local_location_institutes',array('id'=>$id));
    //       $record=new stdClass();
    //       $record->costcenter = $userdata->costcenter;
    //       $record->fullname = $userdata->fullname;
    //       $record->shortname = $userdata->address;
    //       $record->address = $userdata->address;
    //       $record->visible = 1;
    //       $record->institute_type = $userdata->institute_type;
    //       $record->usercreated = $USER->id;
    //       $record->timecreated = time();

    //      return $record;
    //     }
    // }
    /**
     * @param [object] $data
     * @return institute inserted
     */
    public function certificate_insert_instance($data){
        global $DB, $CFG, $USER;
          $record = new stdClass();
          $record->costcenter = $data->costcenter;
          $record->fullname = $data->fullname;
          $record->shortname = $data->fullname;
          $record->address = $data->address;
          $record->visible = 1;
          $record->institute_type = $data->institute_type;
          $record->usercreated = $USER->id;
          $record->timecreated = time();
          try{
            $institutes = $DB->insert_record('local_certificate', $record);
          }catch(dml_exception $ex) {
            print_error($ex);
          }
        return $institutes;
    }
    /**
     * @param [integer] $id
     * @return institute deleted
     */
    public function delete_certificate($id){
        global $DB, $CFG;

        $res = $DB->delete_records('local_certificate',array('id'=>$id));

        redirect($CFG->wwwroot .'/local/certificates/index.php');
    }
}
