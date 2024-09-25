<?php 
require_once(dirname(__FILE__).'/../../config.php');
global $PAGE,$USER,$DB,$CFG,$OUTPUT;
$pid = optional_param('pid',1,PARAM_INT);

$activebadges = $DB->get_records_select('block_gm_events', 'badgeactive = 1');
if($activebadges){
$addbadgeurl= new moodle_url('/blocks/gamification/addbadges.php',array('pid'=>$pid));
$addbadge = html_writer::link($addbadgeurl,'Add Badge',array('id'=>'addbadgebutton'));
	echo '<h4>'.$addbadge.'</h4>';
}
else{
	// echo '<h4>Sorry no badges are active <h4>';
	echo '<h4>'.get_string('nobadgesavailiable', 'block_gamification').'<h4>';
}
// if(is_siteadmin()){
//     // $activeevents = $DB->get_records('block_gm_events');
//     $badgegroups=$DB->get_records_sql('SELECT * FROM {block_gm_badges} WHERE id>0 GROUP BY badgegroupid');
// }else{
//     $activeevents_sql = "SELECT id,costcentercontent,eventid FROM {block_gm_points}";
//     $events = $DB->get_records_sql($activeevents_sql);
//     $activeeventids = array();
//     foreach($events AS $event){
//         $costcentercontent = json_decode($event->costcentercontent);
//         if(!empty($costcentercontent)){
//             foreach($costcentercontent as $key => $value){
//                 if($key == $USER->open_costcenterid){
//                     $decoded_value = json_decode($value);
//                     if($decoded_value->badgeactive == 1){
//                         $activeeventids[] = $event->eventid;  
//                     }
//                 }
//                 continue;
//             }
//         }
//     }
//     $activeeventids = implode(',',$activeeventids);
//     if(!empty($activeeventids)){
//         // $activeevents = $DB->get_records_select('block_gm_events', " id IN($activeeventids)");
//         $badgegroups=$DB->get_records_sql("SELECT bgb.* FROM  {block_gm_badges} AS bgb
// 										JOIN {block_gm_events} AS bge ON bge.id = bgb.badgegroupid
// 										WHERE bge.id IN($activeeventids) 
// 										AND bgb.costcenterid=$USER->open_costcenterid 
// 										GROUP BY badgegroupid");
//     }
// }
// 	// $badgegroups=$DB->get_records_sql('SELECT bgb.* FROM  {block_gm_badges} AS bgb
// 	// 									JOIN {block_gm_events} AS bge ON bge.id = bgb.badgegroupid
// 	// 									WHERE bge.badgeactive = 1
// 	// 									GROUP BY badgegroupid');
//     if(!empty($badgegroups)){   
//     	foreach($badgegroups as $badges){
//     		if(is_siteadmin()){
//     			$data=$DB->get_records('block_gm_badges',array('badgegroupid'=>$badges->badgegroupid),'points ASC');
//     		}else{
//     			$data=$DB->get_records('block_gm_badges',array('badgegroupid'=>$badges->badgegroupid,'costcenterid' => $USER->open_costcenterid),'points ASC');
//     		}
//     		$heading=$DB->get_field('block_gm_events','shortname',array('id'=>$badges->badgegroupid));
//     		$heading = ucfirst(str_replace('_', ' ', $heading));
//     		echo '<h3 class = "badgeheader" >'.$heading.'</h3>';
//     		$out = '';
//     		foreach($data as $result){
//     			$file =$DB->get_record_sql("SELECT * FROM {files} WHERE itemid = $result->badgeimg and filename!='.' and component = 'block_gamification' and filearea = 'badges'");
//     			$filedata = get_file_storage();
//     			// print_object($filedata);
//     			$files = $filedata->get_area_files($file->contextid, 'block_gamification', 'badges',$file->itemid, 'id', false);
//     			// print_object($file);
//     			if(!empty($files)){
//     				$url = array(); 
//     				foreach ($files as $file) {            
//     					$isimage = $file->is_valid_image();            
//     					$url[] = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . 'block_gamification' . '/' . 'badges' .'/'.$file->get_itemid(). $file->get_filepath() . $file->get_filename(), !$isimage);
//     				}
//     				$displaytitle = '';
//     				if($result->type == "course"){
//                 		$title = '';
//             			// print_object($result->courses);
//             			$group = $DB->get_field('block_gm_events','eventcode', array('id' => $result->badgegroupid));
//             			if($group === 'cc' || $group === 'ce'){
//                 			$coursecomp = $DB->get_records_select('course',"id in($result->course)");
//                 			foreach($coursecomp as $courses){
//                 				// print_object($courses);
//                     			$title .= $courses->fullname.'\n';
//                 			}
//                 		}else if($group === 'clc'){
//                 			$coursecomp = $DB->get_records_select('local_classroom',"id in($result->course)");
//                 			foreach($coursecomp as $courses){
//                 				// print_object($courses);
//                     			$title .= $courses->name.'\n';
//                 			}
//                 		}else if ($group === 'ctc'){
//                 			$coursecomp = $DB->get_records_select('competency',"id in($result->course)");
//                 			foreach($coursecomp as $courses){
//                 				// print_object($courses);
//                     			$title .= $courses->shortname.'\n';
//                 			}
//                 		}else if ($group === 'lpc'){
//                 			$coursecomp = $DB->get_records_select('local_learningplan',"id in($result->course)");
//                 			foreach($coursecomp as $courses){
//                 				// print_object($courses);
//                     			$title .= $courses->name.'\n';
//                 			}
//                 		}else if ($group === 'certc'){
//                 			$coursecomp = $DB->get_records_select('local_certification',"id in($result->course)");
//                 			foreach($coursecomp as $courses){
//                 				// print_object($courses);
//                     			$title .= $courses->name.'\n';
//                 			}
//             			}else if ($group === 'progc'){
//                 			$coursecomp = $DB->get_records_select('local_program',"id in($result->course)");
//                 			foreach($coursecomp as $courses){
//                 				// print_object($courses);
//                     			$title .= $courses->name.'\n';
//                 			}
//                 		}
                		
//                 // print_object($url[0]);
//                 $displaytitle = str_replace('\n', '<br>', $title);
//                 // print_object($displaytitle);
//                 $badgeimg= "<span class='badgetooltip'><img id= 'badgeimage'  src = '$url[0]'  height='70' width='70'/><br><span class='badgetooltip-content'><span class='badgetooltip-text'><span class='badgetooltip-inner'>".$displaytitle."</span></span></span></span>";
//             } else{
//          	    $badgeimg= "<img id= 'badgeimage' src = '$url[0]'  height='70' width='70'/><br>";
//      	    }
//     				// $badgeimg= "<span class='badgetooltip'><img id= 'badgeimage'  src = '$url[0]'  height='70' width='70'/><br><span class='badgetooltip-content'><span class='badgetooltip-text'><span class='badgetooltip-inner'>".$displaytitle."</span></span></span></span>";    
//     			}
//     			$rooturl = $CFG->wwwroot.'/blocks/gamification';
//     			$edit = '<a id='.$result->id.' class="edit" href ="'.$rooturl.'/addbadges.php?id='.$result->id.'&pid='.$pid.'"><img src="'.$OUTPUT->image_url('t/edit').'"></a>';
//     			$delete = '<a id=del_'.$result->id.' class="delete" href ="'.$rooturl.'/delbadges.php?id='.$result->id.'&pid='.$pid.'" ><img src="'.$OUTPUT->image_url('t/delete').'"></a>';
//     	  		$confirmationmsg = "Are you sure to delete?";                    
//           		$PAGE->requires->event_handler("#del_".$result->id, 'click', 'M.util.moodle_gamification_confirm_dialog',array('message' => $confirmationmsg,'callbackargs' => array()));
//     			$out .=html_writer::start_span('',array('id' => 'badgedisplay'));
//     			// $out .= $badgeimg.' '.$result->points.' Points '.$edit.' '.$delete;
//     			if($result->type=='points'){
//                 	$badge_details = '<span class="gm-badge_points">'.$result->points.' '.get_string('points_str', 'block_gamification').'</span>';
//     	        }
//     	        else{
//     	            $badge_details = '<span class="gm-badgetype">For Courses</span>';
//     	        }
//     	        $badge_name = '<div class="gm_badgename">'.$result->badgename.'</div>';
//     			$out .= $badgeimg.' '.$badge_name.''.$badge_details.''.$edit.' '.$delete;
//     			$out .=html_writer::start_span('',array('class' => 'badgename'));
//     			// $out .= '<br>'.$result->badgename;
//     			// $out .= '<br>'.$result->points.' Points ';
    			
//     			$out .=html_writer::end_span();
//     			$out .=html_writer::end_span();
//     		}
//     		echo $out.'<br>';
//     	}
//     }else{

//     }
