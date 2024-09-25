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
namespace local_certification\local;
class user{

  public function user_profile_content($id,$return = false,$start =0,$limit=5){
        global $OUTPUT,$PAGE, $CFG;
        $returnobj = new \stdClass();
        $returnobj->certificationexist = 1;
        $usercertifications = $this->enrol_get_users_certification($id,false,true,$start,$limit);
        $data = array();
        foreach($usercertifications['data'] as $certification){
            $certificationsarray = array();
            $certificationsarray['id'] = $certification->id;
            $certificationsarray['name'] = $certification->name;
            $certificationsummary = \local_costcenter\lib::strip_tags_custom($certification->description);
            $certificationsummary = strlen($certificationsummary) > 140 ? substr($certificationsummary, 0, 140)."..." : $certificationsummary;
            $certificationsarray['description'] = $certificationsummary;
            $certificationsarray['percentage'] = '';
            $certificationsarray['url'] = '';
            $certificationsarray['module_img_url'] = ((new \local_certification\certification)->certification_logo($certification->module_logo));
            if($certificationsarray['module_img_url'] == 0){
                require_once($CFG->dirroot.'/local/includes.php');
                $includes = new \user_course_details();
                $certificationsarray['module_img_url'] = ($includes->get_classes_summary_files($certification))->out();
            }else{
                $certificationsarray['module_img_url'] = $certificationsarray['module_img_url']->out();
            }
            $data[] = $certificationsarray;
        }
        $returnobj->sequence = 5;
        $returnobj->divid = 'user_certifications';
        $returnobj->string = get_string('certifications', 'local_users');
        $returnobj->moduletype = 'certification';
        $returnobj->targetID = 'display_certification';
        $returnobj->userid = $id;
        $returnobj->count = $usercertifications['count'];
        $returnobj->navdata = $data;
        return $returnobj;
  }
  
	public function user_team_content($user){
		global $OUTPUT;
		$certifications = $this->get_team_member_certification_status($user->id);
		$completed = $certifications->completed;
		$total = $certifications->total;
		$teamstatus = new \local_myteam\output\team_status_lib();
        $templatedata = array();
        $templatedata['elementcolor'] = $teamstatus->get_colorcode_tm_dashboard($completed,$total);
        $templatedata['completed'] = $completed;
        $templatedata['enrolled'] = $total;
        $templatedata['username'] = fullname($user);
        $templatedata['userid'] = $user->id;
        $templatedata['modulename'] = 'certification';
		    //return $OUTPUT->render_from_template('local_users/team_status_element', $templatedata);
        return (object) $templatedata;
	}
	public function get_team_member_certification_status($userid){
    	$return = new \stdClass();
		$return->inprogress = $this->certification_status_count($userid,'1');
		$return->completed = $this->certification_status_count($userid,'4');
		$return->total = $this->certification_status_count($userid,'1,4');
		return $return;
    }

    public function certification_status_count($userid,$status='') {
        global $DB;
        $sql = "SELECT count(lc.id) FROM {local_certification} AS lc 
                JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
                WHERE lc.status IN ({$status}) AND lcu.userid={$userid} ";
        $coursecount = $DB->count_records_sql($sql);
        return $coursecount;
    }

    /**
     * [function to get_user modulecontent]
     * @param  [INT] $id [id of the user]
     * @param  [INT] $start [start]
     * @return [INT] $limit [limit]
     */
    public function user_modulewise_content($id,$start =0,$limit=5){
      global $OUTPUT,$PAGE;
      $returnobj = new \stdClass();
      $certifications = $this->enrol_get_users_certification($id,false,true,$start,$limit);
      $data = array();
      foreach($certifications['data'] as $certification){
          $certificationsarray = array();
          $certificationsarray['name'] = $certification->name;
          $certificationsarray['code'] = $certification->shortname;
          $certificationsarray['enrolldate'] = \local_costcenter\lib::get_userdate('d/m/Y H:i',$certification->enrolldate);
          if($certification->status == 1){
            $status = 'Completed';
            $certificationsarray['completiondate'] = \local_costcenter\lib::get_userdate('d/m/Y H:i', $certification->completiondate);
          }else{
            $status = 'Not Completed';
            $certificationsarray['completiondate'] = 'N/A';
          }
          $certificationsarray['status'] = $status;

          $data[] = $certificationsarray;
      }
      $returnobj->navdata = $data;
      return $returnobj;
    }

     /**
     * [function to get_enrolled classroom data and count]
     * @param  [INT] $userid [id of the user]
     * @param  [BOOLEAN] $count [true or false]
     * @param  [BOOLEAN] $limityesorno [true or false]
     * @param  [INT] $start [start]
     * @return [INT] $limit [limit]
     */
    public function enrol_get_users_certification($userid,$count =false,$limityesorno = false,$start =0,$limit=5) {
        global $DB;
        $countsql = "SELECT count(lc.id)";
        $selectsql = "SELECT lc.id, lc.name,lc.shortname, lc.description,lcu.timecreated as enrolldate,lcu.completion_status as status, lcu.completiondate , lc.certificationlogo as module_logo ";
        $fromsql = " FROM {local_certification} AS lc 
                    JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
                    WHERE  lcu.userid=:userid ";
        $params = array();
        $params['userid'] = $userid;

        $status = array(1,4);
        list($relatedestatussql, $relatedstatusparams) = $DB->get_in_or_equal($status, SQL_PARAMS_NAMED, 'status');
        $params = array_merge($params,$relatedstatusparams);
        $fromsql .= " AND lc.status $relatedestatussql";

        if($limityesorno){
            $certifications = $DB->get_records_sql($selectsql.$fromsql,$params,$start,$limit);
        }else{
            $certifications = $DB->get_records_sql($selectsql.$fromsql,$params);
        }
        $certificationscount = $DB->count_records_sql($countsql.$fromsql,$params);

        return array('count' => $certificationscount,'data' => $certifications);
    }
    public function user_team_headers(){
      return array('certification' => get_string('pluginname', 'local_certification'));
    }
}