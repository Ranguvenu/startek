<?php
require_once(dirname(__FILE__).'/../../config.php');
function updatepointstable($data){
    global $DB, $USER;
    $result = new stdClass();
    if($DB->record_exists('config',  array('name'=>'leaderboard_context'))){
        set_config('leaderboard_context', $data->leaderboard_context);
    }
    else{
        $result->name='leaderboard_context';
        $result->value = $data->leaderboard_context;
        $DB->insert_record('config', $result);
    }
    if($DB->record_exists('config', array('name'=>'leaderboard_time'))){
        set_config('leaderboard_time', $data->leaderboard_time);
    }
    else{
        $result->name = 'leaderboard_time';
        $result->value = $data->leaderboard_time;
        $DB->insert_record('config', $result);
    }

    foreach($data as $result){
        if(is_array($result)){
            $result1 = new stdClass();
            if(isset($result['badgegroupid'])){
                $result1->eventid = $result['badgegroupid'];
                // $result1->id = $result['badgegroupid'];
                // $result1->badgeactive = ($result['badgegroup'])? 1 : 0;
                $costcentercontent = $DB->get_field('block_gm_points','costcentercontent',array('eventid' => $result['badgegroupid']));
                $result1->id = $DB->get_field('block_gm_points', 'id', array('eventid' => $result['badgegroupid']));
                $decoded_content = json_decode($costcentercontent);
                $new_content = array();
                foreach($decoded_content as $key => $value){
                    if($key == $data->costcenter){
                        $decoded_value = json_decode($value);
                        $decoded_value->badgeactive = ($result['badgegroup'])? 1 : 0;
                        $value = json_encode($decoded_value);
                        $new_content[$key] = $value;    
                        break;
                    }
                    $new_content[$key] = $value;
                    continue;
                }
                $new_content = json_encode($new_content);
                $result1->costcentercontent = $new_content;
                // print_object($result1->costcentercontent);
                $out = $DB->update_record('block_gm_points',$result1);
                // $out = $DB->update_record('block_gm_events',$result1);
            }else{
                $sql = 'SELECT * from {block_gm_events} where id='.$result['id'];
                $eventdata = $DB->get_record_sql($sql);
                $result1 = new stdClass();
                
                $result1->points =($result['eventname']) ? $result['eventname'] : '';
                $result1->active =($result['event']) ? 1 : 0;
                $result1_json = json_encode($result1);

                $result1->shortname = $eventdata->shortname;
                $result1->eventid=$result['id'];
                $existing_record = $DB->get_record('block_gm_points',array('eventid'=>$result1->eventid));
                if($existing_record){
                    $existing_costcentercontent =(array)json_decode($existing_record->costcentercontent);
                    // print_object($existing_costcentercontent);

                    if($existing_costcentercontent){
                        $new_costcentercontent = array($data->costcenter => $result1_json);
                        $costcentercontent = array();
                        $existing_content = array();
                        foreach($existing_costcentercontent as $key => $value){
                            if($key != $data->costcenter){
                                $existing_content[$key] = $value; 
                            }
                        }
                        $costcentercontent = $existing_content+$new_costcentercontent;
                    }else{
                        $costcentercontent =array($data->costcenter => $result1_json);
                    }
                    
                    $result1->costcentercontent = json_encode($costcentercontent);
                    $result1->timemodified = time();
                    $result1->usermodified = $USER->id;
                    $result1->id = $DB->get_field('block_gm_points','id',array('eventid'=>$result1->eventid));
                    $out = $DB->update_record('block_gm_points',$result1);
                    $result2 = new stdClass();
                    $result2->id = $result1->eventid;
                    $result2->active = $result1->active;
                    $out = $DB->update_record('block_gm_events',$result2);
                }else{
                    $costcentercontent = array($data->costcenter => $result1_json);
                    $result1->costcentercontent = json_encode($costcentercontent);
                    $result1->timecreated = time();
                    $result1->timemodified = time();
                    $result1->usermodified = $USER->id;
                    $out = $DB->insert_record('block_gm_points',  $result1);

                } 
            }
        }
        
    }
    // print_object($out);exit;
    return $out;
}
function insert_events($data){
    global $DB,$USER;
    $event = new stdClass();
    $event->event_name = $data->event_name;
    $event->shortname = $data->shortname;
    $event->active = (isset($data->status)) ? 1 : 0;
    $event->timemodified = time();
    $event->usermodified = $USER->id;
    if($update){
        $event->id = $DB->get_field('block_gm_events','id',array('shortname'=>$event->shortname));
        $result = $DB->update_record('block_gm_events',$event);
    } 
    else{
        $event->timecreated = time();
        $result = $DB->insert_record('block_gm_events',$event);
    }
    return $result;
}
function insert_badge_data($data){
    global $DB, $USER;
    $badgeres = new stdClass();    
    $badgeres->badgegroupid = $data->badgegroupid;
    $badgeres->badgename = $data->badgename;
    $badgeres->active = 1;
    $badgeres->badgeimg = $data->badgeimg;
    $context= context_system::instance();
    file_save_draft_area_files($data->badgeimg,  $context->id,  'block_gamification',  'badges',  $data->badgeimg,  array());
    $badgeres->type = $data->type;
    if($badgeres->type == 'course'){ 
        $data->course = implode(',',$data->course);
        
        $badgeres->course = $data->course;
        $badgeres->points = 0;
    } else {
        $badgeres->points = $data->points;
        $badgeres->course = NULL;
    } 
    $badgeres->timemodified = time();
    $badgeres->usermodified = $USER->id;
    if($DB->record_exists('block_gm_badges',array('id'=>$data->id))){
        $existdata = $DB->get_record('block_gm_badges', array('id'=>$data->id));
        // $badgeres->id = $DB->get_field('block_gm_badges','id',array('id'=>$data->id));
        $badgeres->id = $data->id;
        $badgeres->badgegroupid = $existdata->badgegroupid;
        $badgeres->shortname = $existdata->shortname;
        $badgeres->type = $existdata->type;
        if($badgeres->type == 'course'){ 
            $badgeres->course = $data->course;
            $badgeres->points = 0;
        } else {
            $badgeres->points = $data->points;
            $badgeres->course = NULL;
        }
        $out = $DB->update_record('block_gm_badges',  $badgeres);
    // } else if($DB->record_exists('block_gm_badges',array('badgegroupid' => $badgeres->badgegroupid, 'points' => $badgeres->points))){
    // //     $badgeres->id = $DB->get_field('block_gm_badges','id',array('shortname'=>$data->shortname));
    // //     $out = $DB->update_record('block_gm_badges',  $badgeres);
    } else{
    $badgeres->shortname = $data->shortname;
    $badgeres->costcenterid = $data->costcenterid;
    $badgeres->timecreated = time();
    // if($badgeres->points){
        $out = $DB->insert_record('block_gm_badges',$badgeres);
    // }
    }
    return $out;
}
function update_badge_data($id){
    global $DB,$USER;
    $retrivdata = $DB->get_record('block_gm_badges',array('id'=>$id));
	$retrivdata->courses = explode(',', $retrivdata->courses);
    return $retrivdata;
}
// define("")

