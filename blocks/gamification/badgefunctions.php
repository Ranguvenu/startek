<?php 
function insertbadgedata($event,$eventid){
    global $DB;
    $userinfo = $DB->get_records_select('user', 'id > 1');
    foreach($userinfo as $users){
        $badgeinfo = $DB->get_record_select('block_gm_'.$event.'_badges','userid = '.$users->id.' order by time DESC limit 1');
        $userpoints = $DB->get_record('block_gm_overall_'.$event, array('userid' => $users->id), 'points');
        if(!$userpoints){
        	$userscore = 0;
        }
        else{
        	$userscore = $userpoints->points;
        }
        $active = $DB->get_field('block_gm_events', 'badgeactive', array('id' => $eventid));
        if($active){
            $newbadges = $DB->get_records_select('block_gm_badges', ' points <= '.$userscore.' AND badgegroupid = '.$eventid.' and active = 1 and type = "points"');
            foreach($newbadges as $badges){
                $insertbadge = new stdClass();
                $insertbadge->badgeid = $badges->id;
                $insertbadge->time = time();
                $insertbadge->userid = $users->id;
                $out = $DB->record_exists('block_gm_'.$event.'_badges', array('badgeid' => $insertbadge->badgeid, 'userid' => $insertbadge->userid));
                if(!$out){
                    $DB->insert_record('block_gm_'.$event.'_badges', $insertbadge);
                }
            }
            //For course badges starts here.
            $newcoursebadges = $DB->get_records_select('block_gm_badges','active =1 and type = "course" AND badgegroupid = '.$eventid);
            foreach($newcoursebadges as $coursebadges){
                $courses = $DB->get_field('block_gm_overall_'.$event, 'courseid', array('userid' => $users->id));
                $courses = explode(',', $courses);
                $requiredcourses = explode(',', $coursebadges->courses);
                $flag = 0;
                foreach($requiredcourses as $key => $value){
                    if(in_array($value, $courses, TRUE)){
                        $flag = 1;
                    }
                    else {
                        $flag = 0;
                        break;
                    }
                }
                if($flag){
                    $insertcoursebadge = new stdClass();
                    $insertcoursebadge->badgeid = $coursebadges->id;
                    $insertcoursebadge->time = time();
                    $insertcoursebadge->userid = $users->id;
                    $out = $DB->record_exists('block_gm_'.$event.'_badges', array('badgeid' => $insertcoursebadge->badgeid, 'userid' => $insertcoursebadge->userid));
                    if(!$out){
                        $DB->insert_record('block_gm_'.$event.'_badges', $insertcoursebadge);
                    }
                }
            }
            //For course badges ends here.
        }
    }
}
function insertsitebadges(){
    global $DB;
    $userinfo = $DB->get_records_select('user', 'id > 1');
    foreach($userinfo as $users){
        $events = $DB->get_records_select('block_gm_events', 'shortname!= "login" AND active = 1');
        foreach($events as $event){
            $badgesinfo = $DB->get_records_select('block_gm_'.$event->eventcode.'_badges', 'userid = '.$users->id.' order by time desc limit 4');
            if($badgesinfo){
                foreach($badgesinfo as $badgeinfo){
                    $sitedata = new stdClass();
                    $sitedata->event = $event->eventcode;
                    $sitedata->userid = $badgeinfo->userid;
                    $sitedata->badgeid = $badgeinfo->badgeid;
                    $sitedata->time = time();
                    $badgeexist = $DB->get_record('block_gm_site_badges', array('userid' => $sitedata->userid, 'badgeid' => $sitedata->badgeid));
                    if(!$badgeexist){
                        $DB->insert_record('block_gm_site_badges', $sitedata);
                    }
                    else{
                        $sitedata->id = $badgeexist->id;
                        $DB->update_record('block_gm_site_badges', $sitedata);
                    }
                }
            }
        }
    }
}
function update_badge_count_ofuser(){
    global $DB;
    $userinfo = $DB->get_records_select('user', 'id > 1');
    foreach($userinfo as $users){
        $count = 0;
        $events = $DB->get_records_select('block_gm_events', 'shortname != "login" AND active = 1 AND badgeactive = 1');
        foreach($events as $event){
            $badgecount = $DB->count_records('block_gm_'.$event->eventcode.'_badges', array('userid' => $users->id));
            $count += $badgecount; 
        }
        $site = $DB->get_record('block_gm_overall_site',array('userid' => $users->id));
        if($site){
            $updata = new stdClass();
            $updata->id = $site->id; 
            $updata->userid = $users->id;
            $updata->badgecount = $count;
            $DB->update_record('block_gm_overall_site', $updata);
        }
    }
}
function total_event_points() {
    global $DB;
    $activeevents = $DB->get_records('block_gm_events', ['active' => 1]);
    $sitedata = $DB->get_records('block_gm_overall_site');
    $cepts = $DB->get_field('block_gm_points', 'points', ['shortname' => 'course_enrolments']);
    $ccpts = $DB->get_field('block_gm_points', 'points', ['shortname' => 'course_completions']);
    $ctcpts = $DB->get_field('block_gm_points','points', ['shortname' => 'competency_completions']);
    $iltcpts = $DB->get_field('block_gm_points', 'points', ['shortname' => 'ilt_completions']);
    $lpcpts = $DB->get_field('block_gm_points','points',['shortname' => 'learningplan_completions']);
    $event = array();
    foreach($activeevents as $events){
        $event[] = $events->eventcode;
    }
    foreach($sitedata as $data){
        $total = 0;
        if(in_array('cc',$event) || in_array('ce', $event)){
            $enrolledsql = "SELECT ra.id, r.shortname, u.id AS userid, u.username, c.fullname,c.id as course
                FROM mdl_role_assignments AS ra
                JOIN mdl_context AS cxt ON ra.contextid = cxt.id
                JOIN mdl_course AS c ON cxt.instanceid = c.id
                JOIN mdl_role AS r ON ra.roleid = r.id
                JOIN mdl_user AS u ON u.id = ra.userid
                WHERE cxt.contextlevel = 50 and u.id = $data->userid and r.id = 5";
            $enrolledcourses = $DB->get_records_sql($enrolledsql);
            $enccount = count($enrolledcourses);
            $courses = '';
            $coursearray = array();
            foreach($enrolledcourses as $course){
                $coursearray[] = $course->course;
            }
            $total += $enccount*($cepts+$ccpts);
            $courses = implode(',', $coursearray);
            if($courses && in_array('ctc', $event)){
                $competencysql = "SELECT * 
                    FROM  `mdl_competency_coursecomp` 
                    WHERE courseid
                    IN ($courses) 
                    GROUP BY competencyid";
                $competencies = $DB->get_records_sql($competencysql);
                $ctccount = count($competencies);
                $total += $ctccount*$ctcpts;
            }
        }
        if(in_array('iltc', $event)){
            $iltenrol = $DB->count_records('local_facetoface_users',['userid' => $data->userid]);
            $total += $iltcpts*$iltenrol;
        }
        if(in_array('lpc',$event)){
            $lpcenrol = $DB->count_records('local_learningplan_user', ['userid' => $data->userid]);
            $total += $lpcpts*$lpcenrol;
        }
    $update = new stdclass();
    $update->id = $data->id;
    $update->totalpoints = $total;
    $DB->update_record('block_gm_overall_site', $update);
    }
    
}

function event_points(){
    global $DB;
    $points = $DB->get_records('block_gm_points', ['active' => 1]);
    return $points;
}