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
 * @subpackage local_gamification
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
class local_gamification_renderer extends plugin_renderer_base {
	public function dashboard_divisions(){
		$profile_content = $this->profile_content();
		$badges_content = $this->badges_content();
		$leaderboard_content = $this->leaderboard_content();
		$redeems_content = $this->redeems_content();
		$dashboard = [
			'profile' => $profile_content,
			'badges' => $badges_content,
			'leaderboard' => $leaderboard_content,
			'redeems' => $redeems_content
		];
		echo $this->render_from_template('local_gamification/dashboard',$dashboard);
		// $divisions = ['profile']		
	}

	protected function profile_content(){
        global $USER,$DB,$OUTPUT,$PAGE,$CFG;
        $user_info = $DB->get_record('block_gm_overall_site',array('userid' => $USER->id));
        if($user_info){
            $userrank = $user_info->rank;
            $userpoints = $user_info->points;
            $userbadges = $user_info->badgecount ? $user_info->badgecount : get_string('statusna');

        }else{
            $userrank = get_string('statusna');
            $userpoints = get_string('statusna');
            $userbadges = get_string('statusna');            
        }
        $profiledata = [
            'profile_header' => get_string('profile_header', 'local_gamification'),
            'user_image' => $OUTPUT->user_picture($USER,array('size' => 70, 'link' => false)),
            'user_name' => fullname($USER),
            'user_email' => $USER->email,
            'user_rank' => $userrank,
            'user_percentage' => 100,
            'user_points' => $userpoints,
            'user_badges' => $userbadges,
            'download_img' => $OUTPUT->pix_icon('download','download', 'block_gamification'),
            'download_url' => $CFG->wwwroot.'/local/gamification/download.php?save=0',

        ];
        // print_object($OUTPUT->pix_icon('download', 'block_gamification'));
        return $this->render_from_template('local_gamification/profile',$profiledata);
	}

	protected function badges_content(){
        global $DB,$USER,$CFG,$OUTPUT;

        $badgeids = [];
        $badges_information = array();
        
        $badgessql = "SELECT gmsb.* FROM {block_gm_site_badges} as gmsb JOIN {block_gm_events} as gme on gmsb.event = gme.eventcode where userid = :userid and gme.badgeactive = 1  ";

        $badges = $DB->get_records_sql($badgessql,array('userid' => $USER->id),0,3);
        foreach($badges as $badge){
            if($badge){
                $badgeids[] = $badge->badgeid;
            }
        }
        $badgeid = implode(',', $badgeids);
        if(!empty($badgeid)){

            $badgesql = 'SELECT bgmb.* FROM {block_gm_badges} as bgmb JOIN {block_gm_events} as bgme on bgme.id = bgmb.badgegroupid where bgmb.id in('.$badgeid.') AND bgmb.active = 1 AND bgme.active = 1 AND bgme.badgeactive = 1';
            $data = $DB->get_records_sql($badgesql);

            foreach ($data as $result) {
                $badge_innercontent = array();
                
                $shortname = $DB->get_field('block_gm_events', 'shortname', array('id' => $result->badgegroupid));
                
                $file =$DB->get_record_sql("SELECT * FROM {files} WHERE itemid = $result->badgeimg and filename!='.' and component = 'block_gamification' and filearea = 'badges'");
                $filedata = get_file_storage();
                $files = $filedata->get_area_files($file->contextid, 'block_gamification', 'badges',$file->itemid, 'id', false);
                
                if (!empty($files)) {
                    $url = array(); 
                    foreach ($files as $file) {  
                        $isimage = $file->is_valid_image();            
                        $url[] = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->  get_contextid() . '/' . 'block_gamification' . '/' . 'badges' .'/'.$file->get_itemid(). $file->get_filepath() . $file->get_filename(), !$isimage);
                    }
                    $badge_innercontent['imageurl'] = $url[0];
                    $eventname = str_replace('_', ' ', $shortname);
                    $badge_innercontent['eventfullname'] = $eventname;
                    $badge_innercontent['eventname'] = strlen($eventname) > 15 ? substr($eventname, 0, 10).'...' : $eventname;
                }

                
                if($result->type == 'course'){
                    $badge_event = explode('_', $shortname);
                    $name = $badge_event[0]; 
                    $coursenames_type = new \stdClass();
                    switch($name){
                        case 'course':
                            $eventnamestr = get_string('courses');
                            $coursenames_type->name = get_string('coursecompletions'); 
                            $coursenames = $DB->get_records_select_menu('course', 'id IN ('.$result->course.') ', array(), '', 'id,fullname');
                            $coursenames_type->courses = $coursenames;
                            // print_object($coursenames); 
                            break;
                        case 'classroom':
                            $eventnamestr = get_string('classroom','local_gamification');
                            $coursenames_type->name = get_string('classroom_completions', 'local_gamification');
                            $coursenames = $DB->get_records_select_menu('local_classroom', 'id IN '.($result->course).' ',array(), '','id,name');
                            $coursenames_type->courses = $coursenames;
                            // print_object($coursenames);
                            break;
                        case 'program':
                            $eventnamestr = get_string('program', 'local_gamification');
                            $coursenames_type->name =  get_string('program_completions', 'local_gamification');
                            $coursenames = $DB->get_records_select_menu('local_program', 'id IN '.($result->course).' ',array(), '','id,name');
                            $coursenames_type->courses = $coursenames;
                            // print_object($coursenames);
                            break;
                        case 'certification':
                            $eventnamestr = get_string('certification', 'local_gamification');
                            $coursenames_type->name = get_string('certification_completions', 'local_gamification');;
                            $coursenames = $DB->get_records_select_menu('local_certification', 'id IN '.($result->course).' ',array(), '','id,name');
                            $coursenames_type->courses = $coursenames;
                            // print_object($coursenames);
                            break;
                        case 'learningplan':
                            $eventnamestr = get_string('learningplan', 'local_gamification');
                            $coursenames_type->name = get_string('learningplan_completions', 'local_gamification');
                            $coursenames = $DB->get_records_select_menu('local_learningplan', 'id IN '.($result->course).' ',array(), '','id,name');
                            $coursenames_type->courses = $coursenames;
                            // print_object($coursenames);
                            break;
                    }
                    // $badge_course_names = implode(',',$coursenames_type->courses);
                    // 
                    $i = 1;
                    $divarray = '<ul class="ul_courselist">';
                    foreach($coursenames_type->courses as $key => $course){
                        $divarray .= '<li class="div_coursename">'.$i.'. '.$course.'</li>';
                        $i++;
                    }
                    $divarray .= '</ul>';
                    $coursenames_type->course_list = $divarray;

                    $badge_innercontent['tooltip_content'] = get_string('tooltip_content_courses','local_gamification',$coursenames_type);
                    $badge_innercontent['points'] = false;
                }else {
                    $badge_innercontent['tooltip_content'] = get_string('tooltip_content_points','local_gamification',$result->points);
                    $badge_innercontent['points'] = $result->points;
                }
                $badges_information[] = $badge_innercontent;
                // $badge_innercontent['imageurl'] = ;
                // $badge_innercontent['points'] = $result->points;
                // $badge_innercontent['name'] = $eventnamestr;
            }
                
        }

        $badge_data = [
            'badges_header' => 'Badges',
            'badges_information' => $badges_information,//array of badges.
            'height' => 70,
            'width' => 70,
            'viewmore_url' => $CFG->wwwroot.'/local/gamification/viewallbadges.php',
        ];
        return $this->render_from_template('local_gamification/badges',$badge_data);
        // return 'chandra';
	}

	protected function leaderboard_content(){
		global $DB,$USER,$CFG,$OUTPUT;
        $course = optional_param('course', 1,  PARAM_INT);
        if(is_siteadmin()){
            $activeevents = $DB->get_records_select('block_gm_events', "shortname NOT LIKE 'learningplan_completions' AND shortname NOT LIKE 'program_completions' ");
        }else{
            $activeevents_sql = "SELECT id,costcentercontent,eventid FROM {block_gm_points}";
            $events = $DB->get_records_sql($activeevents_sql);
            $activeeventids = array();
            foreach($events AS $event){
                $costcentercontent = json_decode($event->costcentercontent);
                foreach($costcentercontent as $key => $value){
                    if($key == $USER->open_costcenterid){
                        $decoded_value = json_decode($value);
                        if($decoded_value->active == 1){
                            $activeeventids[] = $event->eventid;  
                        }
                    }
                    continue;
                }
            }
            $activeeventids = implode(',',$activeeventids);
            if(!empty($activeeventids)){
                $activeevents = $DB->get_records_select('block_gm_events', " id IN($activeeventids) AND shortname NOT LIKE 'learningplan_completions' AND shortname NOT LIKE 'program_completions' ");
            }else{
                $activeevents = new stdClass();
            }
        }

        $activetabs = array();
        $activetabs[] = ['name' => 'overall', 'ajax_event_url' => $CFG->wwwroot.'/local/gamification/boards/leaderboard/ajax.php?eventname=site', 'head-text' => get_string('overall','local_gamification'), 'icon-class' => 'fa fa-list-ul pr-10'];
        $iconclass_arr = array('cc' => 'fa fa-book pr-10', 'certc' =>'fa fa-graduation-cap pr-10', 'lpc' => 'fa fa-map pr-10', 'progc' => 'fa fa-tasks pr-10','clc' => '');
        if(!empty($activeevents)){
            foreach($activeevents as $event){
                $tabcontent = array();
                $tabcontent['name'] = explode('_',$event->shortname)[0];
                $tabcontent['ajax_event_url'] = $CFG->wwwroot.'/local/gamification/boards/leaderboard/ajax.php?eventname='.$event->eventcode;
                $tabcontent['head-text'] = get_string($tabcontent['name'],'local_gamification');
                $tabcontent['icon-class'] = $iconclass_arr[$event->eventcode];
                $activetabs[] = $tabcontent;
            }
        }
        // print_object($activetabs);
        if($course == 1){
            $type = true;
        }else{
            $type = false;
        }
        $leaderboarddata = [
            'activetabs' => $activetabs,
            'ajaxlb_course_url' => $CFG->wwwroot.'/local/gamification/boards/leaderboard/ajax.php?eventname=course&course='.$course,
            'type' => $type,
        ];
        return $this->render_from_template('local_gamification/leaderboard',$leaderboarddata);
	}

	protected function redeems_content(){
		// return 'mahesh';	
	}
    public function get_scorebadges($eventname) {
        global $DB, $USER;
        $out = $DB->get_records('block_gm_'.$eventname.'_badges', array('userid' => $USER->id));
        $data = '';
        // if(isset($out->points)){        
        //     $data = $out->points;
        // }
        if($out){
                $badgeids = [];
                foreach($out as  $badges) {
                    $badgeids[] = $badges->badgeid;
                }
                $badgeid = implode(',', $badgeids);
                return $badgeid;
        }else{
            return 0;
        }
    }
    public function complete_badge_display($shortname, $badgeid, $groupid) {
        global $DB, $PAGE, $CFG;
        $heading = str_replace('_', ' ', $shortname);
        $badgearray =explode(',', $badgeid);
        $lastbadge = end($badgearray);
        $heading = '<div class = "heading">'.$heading.'</div>';
        // $torecievesql = 'SELECT * FROM {block_gm_badges} where badgegroupid = '.$groupid.' and id > '.$lastbadge.'  ORDER BY points ASC';
        // $torecievedata = $DB->get_records_sql($torecievesql);
        $badgesql = 'SELECT * FROM {block_gm_badges} where badgegroupid = '.$groupid.' ORDER by points ASC';
        $data = $DB->get_records_sql($badgesql);
        $out = '';
        foreach($data as $result){
            $file =$DB->get_record_sql("SELECT * FROM {files} WHERE itemid = $result->badgeimg and filename!='.' and component = 'block_gamification' and filearea = 'badges'");
            $filedata = get_file_storage();
            $files = $filedata->get_area_files($file->contextid, 'block_gamification', 'badges',$file->itemid, 'id', false);
            if (!empty($files)) {
                $url = array(); 
                foreach ($files as $file) {            
                    $isimage = $file->is_valid_image();            
                    $url[] = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . 'block_gamification' . '/' . 'badges' .'/'.$file->get_itemid(). $file->get_filepath() . $file->get_filename(), !$isimage);
                }
                $badgeimg= "<img id= 'badgeimage' src = '$url[0]'  height='70' width='70'/><br>";  
            }
            if(in_array($result->id, $badgearray)) {
                $out .= html_writer::start_span('',array('id' => 'badgedisplay'));
            }
            else{
                $out .= html_writer::start_span('',array('id' => 'fadebadgedisplay'));
            }
            // $out .= $badgeimg.' '.$result->points.' Points ';
            if($result->type == 'course'){
                $group = $DB->get_field('block_gm_events','eventcode', array('id' => $result->badgegroupid));
                if($group === 'cc' || $group === 'ce'){
                    $coursecomp = $DB->get_records_select('course',"id in($result->course)");
                    foreach($coursecomp as $courses){
                        // print_object($courses);
                        $title .= $courses->fullname.'\n';
                    }
                }else if($group === 'clc'){
                    $coursecomp = $DB->get_records_select('local_classroom',"id in($result->course)");
                    foreach($coursecomp as $courses){
                        // print_object($courses);
                        $title .= $courses->name.'\n';
                    }
                }else if ($group === 'ctc'){
                    $coursecomp = $DB->get_records_select('competency',"id in($result->course)");
                    foreach($coursecomp as $courses){
                        // print_object($courses);
                        $title .= $courses->shortname.'\n';
                    }
                }else if ($group === 'lpc'){
                    $coursecomp = $DB->get_records_select('local_learningplan',"id in($result->course)");
                    foreach($coursecomp as $courses){
                        // print_object($courses);
                        $title .= $courses->name.'\n';
                    }
                }else if ($group === 'certc'){
                    $coursecomp = $DB->get_records_select('local_certification',"id in($result->course)");
                    foreach($coursecomp as $courses){
                        // print_object($courses);
                        $title .= $courses->name.'\n';
                    }
                }else if ($group === 'progc'){
                    $coursecomp = $DB->get_records_select('local_program',"id in($result->course)");
                    foreach($coursecomp as $courses){
                        // print_object($courses);
                        $title .= $courses->name.'\n';
                    }
                }
                $displaytitle = str_replace('\n', '<br>', $title);
            }
            else{
                $displaytitle = get_string('for_points_badge','local_gamification',$result->points);
            }
            $out .= "<span class='badgetooltip'>".$badgeimg.' '.$result->badgename."<span class='badgetooltip-content'><span class='badgetooltip-text'><span class='badgetooltip-inner'>".$displaytitle."</span></span></span></span>";

            $out .= html_writer::start_span('',array('class' => 'userbadgename'));
            // $out .= '<br>'.$result->badgename;
            if($result->type == 'course'){
                $out .= '<br>'.get_string('for_courses','local_gamification');
            }else{
                $out .= '<br>'.$result->points.' Points ';
            }

            $out .= html_writer::end_span();
            $out .= html_writer::end_span();
        }
        // foreach($torecievedata as $result){
        //     $file =$DB->get_record_sql("SELECT * FROM {files} WHERE itemid = $result->badgeimg and filename!='.' and component = 'blocks' and filearea = 'badges'");
        //     $filedata = get_file_storage();
        //     $files = $filedata->get_area_files($file->contextid, 'blocks', 'badges',$file->itemid, 'id', false);
        //     if (!empty($files)) {
        //         $url = array(); 
        //         foreach ($files as $file) {            
        //             $isimage = $file->is_valid_image();            
        //             $url[] = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . 'blocks' . '/' . 'badges' .'/'.$file->get_itemid(). $file->get_filepath() . $file->get_filename(), !$isimage);
        //         }
        //         $badgeimg= "<img id= 'badgeimage' src = '$url[0]'  height='70' width='70'/><br>";  
        //     }
        //     $out .= html_writer::start_span('',array('id' => 'fadebadgedisplay'));
        //     // $out .= $badgeimg.' '.$result->points.' Points ';
        //     $out .= $badgeimg.' '.$result->badgename;
            
        //     $out .= html_writer::start_span('',array('class' => 'fadebadgename'));
        //     // $out .= '<br>'.$result->badgename;
        //     $out .= '<br>'.$result->points.' Points ';
        //     $out .= html_writer::end_span();
        //     $out .= html_writer::end_span();
        // }
        if(empty($out)) {
            $out = '<div class = "nobadges_string">'.get_string('no_badges_defined_yet', 'local_gamification').'</div>';
        }
        return $heading.$out;
    }
}