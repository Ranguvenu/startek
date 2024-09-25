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
 */
namespace block_gamification\local;
use html_writer;
use context_system;
use stdClass;
class badgelib{
	public $db;

	public $user;

	public $accountid;

	public $accountname;

	public function __construct($db = null, $user = null, $accountid){
		global $USER, $DB;
		$this->db = $db ? $db : $DB;
		$this->user = $user ? $user : $USER;
		$this->accountid = $accountid;
		// $this->accountname = $this->db->get_field('local_costcenter', 'fullname', array('id' => $this->accountid));
	}
	public function coins_badges_content($requestdata){
        $systemcontext = context_system::instance();
		$search = $requestdata['search']['value'];
        $params = array("costcenterid" => $this->accountid);
        $countsql = "SELECT count(bgm.id) ";
        $coinssql = "SELECT bgm.*, lc.fullname AS accountname ";
        $conditionsql = " FROM {block_gm_badges} AS bgm 
            JOIN {local_costcenter} AS lc ON lc.id=bgm.costcenterid
            WHERE bgm.type='coins' ";
        if(is_siteadmin() || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || has_capability('local/courses:manage_acccourses', $systemcontext) || has_capability('local/gamification:manage_badges', $systemcontext)){
            $conditionsql .= " AND bgm.costcenterid=:costcenterid ";
        }else{
            $conditionsql .= " AND (bgm.costcenterid=:costcenterid OR bgm.id IN (SELECT bgsm.badgeid FROM {block_gm_site_badges} AS bgsm WHERE bgsm.userid=:userid )) ";
            $params['userid'] = $this->user->id;
        }

        if(!empty($search) && $search != ''){ 
			$conditionsql .= " AND (bgm.badgename LIKE '%{$search}%' OR bgm.points LIKE '%{$search}%' OR lc.fullname LIKE '%{$search}%' ) ";
		}
        $ordersql = " ORDER BY bgm.id DESC ";
		$coinscount = $this->db->count_records_sql($countsql.$conditionsql, $params); 
        $coinsdata = $this->db->get_records_sql($coinssql.$conditionsql.$ordersql, $params, $requestdata['start'], $requestdata['length']);
        if(!is_siteadmin()){
            $mybadges = $this->db->get_records_menu('block_gm_site_badges', array('event' => 'coins', 'userid' => $this->user->id), '', 'id, badgeid');
        }else{
            $mybadges = array();
        }
        $badgecontent = $this->format_badgecontent($coinsdata, $mybadges, 'coins');
        return array('count' => $coinscount, 'data' => $badgecontent);
	}
	public function levels_badges_content($requestdata){
        $systemcontext = context_system::instance();
		$search = $requestdata['search']['value'];
        $params = array("costcenterid" => $this->accountid);
        $countsql = "SELECT count(bgm.id) ";
        $coinssql = "SELECT bgm.*, lc.fullname AS accountname ";
        $conditionsql = " FROM {block_gm_badges} AS bgm 
            JOIN {local_costcenter} AS lc ON lc.id=bgm.costcenterid
            WHERE bgm.type='levels' ";
        if(is_siteadmin() || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || has_capability('local/courses:manage_acccourses', $systemcontext) || has_capability('local/gamification:manage_badges', $systemcontext)){
            $conditionsql .= " AND bgm.costcenterid=:costcenterid ";
        }else{
            $conditionsql .= " AND (bgm.costcenterid=:costcenterid OR bgm.id IN (SELECT bgsm.badgeid FROM {block_gm_site_badges} AS bgsm WHERE bgsm.userid=:userid )) ";
            $params['userid'] = $this->user->id;
        }
		if(!empty($search) && $search != ''){
			$conditionsql .= " AND (bgm.badgename LIKE '%{$search}%' OR bgm.level LIKE '%{$search}%' OR lc.fullname LIKE '%{$search}%' ) ";
		}
        $ordersql = " ORDER BY bgm.id DESC ";
		$coinscount = $this->db->count_records_sql($countsql.$conditionsql, $params); 
        $coinsdata = $this->db->get_records_sql($coinssql.$conditionsql.$ordersql, $params, $requestdata['start'], $requestdata['length']);
        if(!is_siteadmin()){
            $mybadges = $this->db->get_records_menu('block_gm_site_badges', array('event' => 'levels', 'userid' => $this->user->id), '', 'id, badgeid');
        }else{
            $mybadges = array();
        }
        $badgecontent = $this->format_badgecontent($coinsdata, $mybadges, 'levels');
        return array('count' => $coinscount, 'data' => $badgecontent);
	}
	public function course_badges_content($requestdata){
        $systemcontext = context_system::instance();
        $search = $requestdata['search']['value'];
        $params = array("costcenterid" => $this->accountid);
        $countsql = "SELECT count(bgm.id) ";
        $coinssql = "SELECT bgm.*, c.fullname AS 'course name', lc.fullname AS accountname, c.open_costcenterid AS courseaccount ";
        $conditionsql = " FROM {block_gm_badges} AS bgm 
            JOIN {local_costcenter} AS lc ON lc.id=bgm.costcenterid
            LEFT JOIN {course} AS c ON c.id=bgm.courses
            WHERE bgm.type='course_completions' ";
        if(is_siteadmin() || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || has_capability('local/courses:manage_acccourses', $systemcontext) || has_capability('local/gamification:manage_badges', $systemcontext)){
            $conditionsql .= " AND bgm.costcenterid=:costcenterid ";
        }else{
            $time = time();
            $conditionsql .= " AND ((bgm.costcenterid = :costcenterid AND c.open_departmentid IN (0,{$this->user->open_departmentid}) AND concat(',',c.open_roles,',') LIKE '%,{$this->user->open_role},%') OR (
            c.id IN (SELECT courseid FROM {enrol} AS e 
            JOIN {user_enrolments} AS ue ON ue.enrolid=e.id AND e.status=0 AND ue.status=0 AND ue.userid=:ueuserid AND (ue.timeend >= {$time} OR ue.timeend=0)) AND bgm.costcenterid={$this->user->open_costcenterid}) OR bgm.id IN (SELECT bgsm.badgeid FROM {block_gm_site_badges} AS bgsm WHERE bgsm.userid=:userid )) ";//AND ue.timestart <= {$time} AND (ue.timeend >= {$time} OR ue.timeend=0)
            //AND concat(',',c.open_roles,',') LIKE concat('%,',{$this->user->open_role},',%') 
            $params['userid'] = $this->user->id;
            $params['ueuserid'] = $this->user->id;
            
        }
		if(!empty($search) && $search != ''){
			$conditionsql .= " AND (bgm.badgename LIKE '%{$search}%' OR c.fullname LIKE '%{$search}%' OR lc.fullname LIKE '%{$search}%' ) ";
		}
        $ordersql = " ORDER BY bgm.id DESC ";

		$coinscount = $this->db->count_records_sql($countsql.$conditionsql, $params); 
        $coinsdata = $this->db->get_records_sql($coinssql.$conditionsql.$ordersql, $params, $requestdata['start'], $requestdata['length']);
        if(!is_siteadmin()){
            $mybadges = $this->db->get_records_menu('block_gm_site_badges', array('event' => 'course_completions', 'userid' => $this->user->id), '', 'id, badgeid');
        }else{
            $mybadges = array();
        }
        $badgecontent = $this->format_badgecontent($coinsdata, $mybadges, 'course_completions');
        return array('count' => $coinscount, 'data' => $badgecontent);
	}
	public function peer_badges_content($requestdata){
        global $_SESSION;
		$search = $requestdata['search']['value'];
		$countsql = "SELECT count(bgm.id) ";
		$coinssql = "SELECT bgm.*, lc.fullname AS accountname ";
        $params = array("costcenterid" => $this->accountid);
        if($_SESSION['configurebadgepage'] || $_SESSION['awardbadgepage']){
            $conditionsql = " FROM {block_gm_badges} AS bgm 
                JOIN {local_costcenter} AS lc ON lc.id=bgm.costcenterid 
                WHERE bgm.type='peer_recog' AND bgm.costcenterid=:costcenterid ";
        }else{
            $conditionsql = " FROM {block_gm_badges} AS bgm 
                JOIN {local_costcenter} AS lc ON lc.id=bgm.costcenterid
                JOIN {block_gm_site_badges} AS bgsb ON bgsb.badgeid=bgm.id
                WHERE bgm.type='peer_recog' AND bgsb.userid=:userid"; //AND bgm.costcenterid=:costcenterid
            $params['userid'] = $this->user->id;
        }
		if(!empty($search) && $search != ''){
			$conditionsql .= " AND (bgm.badgename LIKE '%{$search}%' OR lc.fullname LIKE '%{$search}%') ";
		}
        $ordersql = " ORDER BY bgm.id DESC ";
		$coinscount = $this->db->count_records_sql($countsql.$conditionsql, $params); 
        $coinsdata = $this->db->get_records_sql($coinssql.$conditionsql.$ordersql, $params, $requestdata['start'], $requestdata['length']);
        if(!is_siteadmin()){
            $mybadges = $this->db->get_records_menu('block_gm_site_badges', array('event' => 'coins', 'userid' => $this->user->id), '', 'id, badgeid');
        }else{
            $mybadges = array();
        }
        $badgecontent = $this->format_badgecontent($coinsdata, $mybadges, 'peer_recog');
        return array('count' => $coinscount, 'data' => $badgecontent);	
	}
	public function format_badgecontent($badgecontent, $mybadges, $badgetype){
		global $CFG, $OUTPUT, $_SESSION;
		$systemcontext = context_system::instance();
		$content = array();
		// $editimage = html_writer::tag('img','',array('src' => $OUTPUT->image_url('t/edit')));
        // $deleteimage = html_writer::tag('img','',array('src' => $OUTPUT->image_url('t/delete')));
        $badgefields = array('coins' => 'points','levels' => 'level' , 'course_completions' => 'course');

		// print_object($badgecontent);
		foreach($badgecontent AS $badge){
			// $file = $this->db->get_record_sql("SELECT * FROM {files} WHERE itemid = :itemid and filename<>:filename and component = :component and filearea =:filearea", array('itemid' => $badge->badgeimg, 'filename' => '.', 'component' => 'block_gamification', 'filearea' => 'badges'));
			// $filedata = get_file_storage();
   //          $files = $filedata->get_area_files($file->contextid, 'block_gamification', 'badges',$file->itemid, 'id', false);
   //          if(!empty($files)){
   //              $url = array();
   //              foreach ($files as $file) {            
   //                  $isimage = $file->is_valid_image();            
   //                  $url[] = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . 'block_gamification' . '/' . 'badges' .'/'.$file->get_itemid(). $file->get_filepath() . $file->get_filename(), !$isimage);
   //              }
   //              // $badgeimg= "<img class='badgeimage' src = '$url[0]'  height='70' width='70'/><br>";
   //          	$badgeurl = $url[0];
   //          }else{
   //              $defaulturl = $CFG->wwwroot.'/local/gamification/pix/defaultimg.png';
   //              // $badgeimg= "<img class='badgeimage' src = '$defaulturl'  height='70' width='70'/><br>";
   //          	$badgeurl = $defaulturl;
   //          }
			$badgeurl = $this->get_badge_image_url($badge->badgeimg);
            // $badgeimg = html_writer::div($badgeimg, 'gmbadgeimg');
            $badgename = strlen($badge->badgename) > 10 ? substr($badge->badgename, 0, 10).'...' : $badge->badgename ;
            $badgetitle = $badge->badgename;
            $accountname = $badge->accountname;
            // $badgename = html_writer::tag('p',$badge->badgename, array('class' => 'badgename'));
            $avail_levels = $this->db->get_field('block_gamification_config', 'levels', array('courseid' => 1, 'costcenterid' => $badge->costcenterid));
            if($_SESSION['configurebadgepage']){
                $badgeclass = 'visible';
                if(is_siteadmin() || has_capability('local/gamification:manage_badges', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext) || has_capability('local/courses:manage_acccourses', $systemcontext) || has_capability('local/gamification:manage_badges', $systemcontext)){
                    $params = json_encode(array('accountid' => $this->accountid, 'badgetype' => $badgetype));
                    if(!$this->db->record_exists('block_gm_site_badges', array('badgeid' => $badge->id))){
                        $editicon = html_writer::link('javascript:void(0)', $OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle', array('class' => 'iconsmall', 'title' => '')), array('class' => 'edit' , 'id' => $badge->id, 'data-fg' => 'u', 'data-method' => 'create_badge', 'data-plugin' => 'block_gamification', 'data-id' => $badge->id, 'data-params' => $params));
                        $deleteicon = html_writer::link('javascript:void(0)', $OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle', array('class' => 'iconsmall', 'title' => '')), array('class' => 'delete' , 'id' => $badge->id, 'onclick' => "(function(e){ require(\"block_gamification/visualformchanges\").init({selector:\"deletebadge\", context:$systemcontext->id, badgeid:$badge->id, badgename:\"$badge->badgename\"}) })(event)"));
                    }else{
                        $editicon = '';
                        $deleteicon = '';
                    }
                    // $manage = TRUE;
                }else{
                    $deleteicon = '';
                    $editicon = '';
                    // $manage = FALSE;
                }
                if(($badgetype == 'levels' && $avail_levels < $badge->level)|| (!is_siteadmin() &&$badgetype =='course_completions' && $badge->courseaccount != $this->user->open_costcenterid)){
                    $editicon = '';
                }
            }else{
                if(in_array($badge->id, $mybadges) || $badgetype == 'peer_recog'){
                    $badgeclass = 'visible';
                    $badgestring = '';
                }else{
                    //$badgeclass = 'inactive ';
                    if($badgetype == 'levels' && $avail_levels < $badge->level){
                        $badgeclass = 'locked';
                        $badgestring = 'Locked';
                    }else{
                        $badgeclass = 'unawarded';
                        $badgestring = 'Achievable';
                    }
                }
                $deleteicon = '';
                $editicon = '';
                // $manage = FALSE;
            }
            if($editicon == '' && $deleteicon == ''){
                $manage = FALSE;
            }else{
                $manage = TRUE; 
            }
            
            // $actions = html_writer::tag('p', $editicon.$deleteicon, array('class' => 'badgeactions'));
            // $badgefor = array_key_exists($badgetype, $badgefields) ? $badgefields[$badgetype] : null;
            // if($badgefor){
            //     $badgereason = get_string('badgereason'.$badgetype ,'block_gamification', $badge->$badgefor);
            // }else{
            //     $badgereason = get_string('achievedfrompeer', 'block_gamification');
            // }
            if($badgetype != 'peer_recog'){
            	$badgeforenable = TRUE;
                if(empty($badge->{$badgefields[$badgetype]})){
                    $badge->{$badgefields[$badgetype]} = get_string('deletedcourse', 'block_gamification') ;
                }
            	$badgefor_value = strlen($badge->{$badgefields[$badgetype]}) > 10 ?substr($badge->{$badgefields[$badgetype]}, 0, 10).'...' : $badge->{$badgefields[$badgetype]} ;
                $badgefor_value_title = $badge->{$badgefields[$badgetype]};
            	$badgeforlabel = $badgetype == 'coins' ? 'Coins' :ucfirst($badgefields[$badgetype]);
                $viewawardees = '';
            }else{
                $badgeforenable = FALSE;
                $badgefor_value = '';
                $badgeforlabel = '';

                
            	/*if((is_siteadmin() || has_capability('local/gamification:manage_badges', $systemcontext) || has_capability('local/costcenter:manage_ownorganization', $systemcontext)) || !$_SESSION['awardbadgepage']){
            		$badgeallocate = '';
                    $viewawarded = '';
            	}else */if($_SESSION['awardbadgepage']){
                    $awardicon = html_writer::tag('span', '', array('class' => 'ic-awrads award-icon'));
                    $myawardicon = html_writer::tag('span', '', array('class' => 'ic-awrads myaward-icon'));
                    $viewawardees = '';
                    $badgeallocate = html_writer::link('javascript:void(0)', $awardicon, array('data-fg' => 'c', 'data-method' => 'award_peer_badge','title' => get_string('awardbadges', 'block_gamification'), 'data-plugin' => 'block_gamification', 'class' => 'pull-right', 'data-id' => $badge->id, 'data-accountid' => $this->accountid));
                    $viewawarded = html_writer::link('javascript:void(0)', $myawardicon, array('data-fg' => 'r', 'data-method' => 'peer_allocation_status','title' => get_string('myawardees', 'block_gamification'), 'data-plugin' => 'block_gamification', 'class' => 'pull-right', 'data-id' => $badge->id, 'data-accountid' => $this->accountid));
                }else if(!$_SESSION['configurebadgepage']){
                    $badgeallocate = '';
                    $viewawarded = '';
                    $badgesurl = $OUTPUT->image_url('my_awarder_blue', 'theme_concentrix');
                    $badges = html_writer::tag('img', '', array('src' => $badgesurl));
                    $viewawardees = html_writer::link('javascript:void(0)', $badges, array('data-fg' => 'r', 'data-method' => 'peer_badge_status', 'data-plugin' => 'block_gamification', 'class' => 'mr-2 pull-right', 'data-id' => $badge->id, 'data-accountid' => $this->accountid));
            	}
            }
            // $badgedetail = html_writer::tag('p', $badgereason, array('class' => 'badgedetail'));
            // $badgedescription = html_writer::div($badgename.$badgedetail.$actions, 'badgedescription');
            $badgedetails = array(
            	'badgeid' => $badge->id,
            	'badgeurl' => $badgeurl,
            	'badgename' => $badgename,
            	'accountname' => $accountname,
            	'editicon' => $editicon,
            	'deleteicon' => $deleteicon,
            	'badgereason' => $badgereason,
            	'badgeforlabel' => $badgeforlabel,
            	'badgefor_value' => $badgefor_value,
                'badgefor_value_title' => $badgefor_value_title,
                'badgetitle' => $badgetitle,
            	'badgeforenable' => $badgeforenable,
            	'manage' => $manage,
            	'badgeallocate' => $badgeallocate,
                'badgeclass' => $badgeclass,
                'badgestring' => $badgestring,
                'viewawardees' => $viewawardees,
                'viewawarded' => $viewawarded,
            );
            $content[] = $OUTPUT->render_from_template('block_gamification/badgeinfo', $badgedetails); 
            // $content[] = html_writer::div($badgeimg.$badgedescription, 'badgecontent');
		}
        // $badgecontent = array_chunk($content, 4);
        $emptycount = 8-count($content);
        if($emptycount < 8){
            for($i=0; $i<$emptycount; $i++){
                $content[] = '';
            }
        }
		$badgecontent = array_chunk($content, 8);
		return $badgecontent;
	}
	public function get_badge_image_url($badgeimgitem){
		global $CFG;
		$file = $this->db->get_record_sql("SELECT * FROM {files} WHERE itemid = :itemid and filename<>:filename and component = :component and filearea =:filearea", array('itemid' => $badgeimgitem, 'filename' => '.', 'component' => 'block_gamification', 'filearea' => 'badges'));
		$filedata = get_file_storage();
        $files = $filedata->get_area_files($file->contextid, 'block_gamification', 'badges',$file->itemid, 'id', false);
        if(!empty($files)){
            $url = array();
            foreach ($files as $file) {            
                $isimage = $file->is_valid_image();            
                $url[] = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . 'block_gamification' . '/' . 'badges' .'/'.$file->get_itemid(). $file->get_filepath() . $file->get_filename(), !$isimage);
            }
        	$badgeurl = $url[0];
        }else{
            $defaulturl = $CFG->wwwroot.'/local/gamification/pix/defaultimg.png';
        	$badgeurl = $defaulturl;
        }
        return $badgeurl;
	}
	public function award_peer_badge($formdata){
		$tousers = $formdata->touser;
		foreach($tousers AS $touser){
			$dataobject = new stdClass();
			$dataobject->touserid = $touser;
			$dataobject->fromuserid = $this->user->id;
			$dataobject->message = $formdata->message;
			$dataobject->timeallocated = time();
			$dataobject->badgeid = $formdata->badgeid;
			$this->db->insert_record('block_gm_peer_allocation', $dataobject);
			\block_gamification\customlib::insert_badge_to_user($formdata->badgeid, $touser, 'peer_recog');
		}
		return true;
	}
}