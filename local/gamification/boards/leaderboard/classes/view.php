<?php
// This file is part of the gamification localule for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace gamificationboards_leaderboard;

defined('MOODLE_INTERNAL') || die();

class view{
	public function view_content($eventname,$courseid){
		global $OUTPUT;
		$overalldata = $this->overall_info($eventname,$courseid);
		$monthlydata = $this->monthly_info($eventname,$courseid);
		$weeklydata = $this->weekly_info($eventname,$courseid);
		$view_content = [
			'overall_leaderboard' => $overalldata,
			'weekly_leaderboard' => $weeklydata,
			'monthly_leaderboard' => $monthlydata,
			'has_capability' => false,
		];
		// return $overalldata.$monthlydata.$weeklydata;

		return $OUTPUT->render_from_template('gamificationboards_leaderboard/leaderboard_innercontent', $view_content);
	}

	protected function overall_info($eventname,$courseid){
		global $DB,$OUTPUT,$COURSE,$USER,$PAGE;
		$tablename = 'block_gm_overall_'.$eventname;
		$type = get_config('block_gamification','type');
		if($type == 'rank'){
			$userrank_sql = "SELECT x.id, x.rank, x.points, GROUP_CONCAT(DISTINCT x.userid) AS userid FROM {{$tablename}} AS x JOIN {user} AS u ON u.id=x.userid WHERE u.id > 2";
			if(!is_siteadmin()){
        		$userrank_sql .= " AND u.open_costcenterid = $USER->open_costcenterid ";
    		}
			if($eventname == 'course'){
				$userrank_sql .= " AND x.courseid=$courseid";
			}
			$userrank_sql .= " GROUP BY x.rank ORDER BY x.rank ASC LIMIT 0, 5";
			$userranks = $DB->get_records_sql($userrank_sql);
			$name = 'Rank';
		}else if($type == 'level'){
			$userrank_sql = "SELECT x.id, x.level as rank FROM {{$tablename}} AS x 
							JOIN {user} AS u ON u.id=x.userid WHERE u.id>2";
			if(!is_siteadmin()){
        		$userrank_sql .= " AND u.open_costcenterid = $USER->open_costcenterid";
    		}
			if($eventname == 'course'){
				$userrank_sql .= " AND courseid=$courseid";
			}
			$userrank_sql .= " GROUP BY level ORDER BY level DESC LIMIT 0, 5";
			$userranks = $DB->get_records_sql($userrank_sql);
			$name = 'Level';
		}
		if(empty($userranks)){
			return get_string('no_data_available', 'gamificationboards_leaderboard');
		}else{
			$leaderboard = array();
			foreach($userranks as $rank){
				if($rank->rank){
					if($type == 'level'){
						$userids = $DB->get_records_sql_menu("SELECT id, userid FROM {{$tablename}} WHERE level = $rank->rank ORDER BY points DESC LIMIT 0,4");
						$user_array = array();
						foreach ($userids as $userid) {
							$user_array[] = $userid;
						}
						$userslist = array_slice($user_array , 0, 4);
					}else if($type == 'rank'){
						$users = explode(',', $rank->userid);
						$userslist = array_slice($users , 0, 4);
					}
					$userpictures_array = array();
					foreach ($userslist as $user) {
						if($type == 'level'){
							$userpoints = $DB->get_record_sql("SELECT id,points FROM {{$tablename}} WHERE userid=$user AND level = $rank->rank");
						} else if($type == 'rank'){
							$userpoints = $DB->get_record_sql("SELECT id,points FROM {{$tablename}} WHERE userid=$user AND rank = $rank->rank");						
						}
						$userinfo = \core_user::get_user($user);
						if($userinfo->id == $USER->id || is_siteadmin()){

							$systemcontext = \context_system::instance();
							$userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo)."<span class='score clickable' onclick='(function(e){ require(\"gamificationboards_leaderboard/pointsinfo\").pointsInfo({selector:\"user_points_description_modal\", context:".$systemcontext->id.",userid:".$user.",eventname:\"$eventname\",type:\"overall\",objectid:".$userpoints->id.",points:".$userpoints->points.",courseid:".$courseid."}) })(event)'>".$userpoints->points."</span></span>";

						}else{
							$userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo)."<span class='score'>".$userpoints->points."</span></span>";
						}

					}
					$userpictures = implode('',$userpictures_array);
					if($rank->rank > 10){
						$rank_class = 'last';
					} else {
						$rank_class = $rank->rank;
					}
					
					$leaderboardcols=array();
					$leaderboardcols['rank_class'] = $rank_class;
					$leaderboardcols['rank'] = $rank->rank;
					$leaderboardcols['name'] = $name;
					$leaderboardcols['userpictures'] = $userpictures;
					$leaderboard[] = $leaderboardcols;

				}else{
					$leaderboard[] = '';					
				}
			}

			if(!empty($leaderboard)){
				$viewmoreurl = new \moodle_url('/local/gamification/boards/leaderboard/overall_detailed_info.php?eventname='.$eventname, array('course' => $courseid));
			}else{
				$viewmoreurl = false;
			}
			$innercontent = [
				'leaderboard' => $leaderboard, 
				'viewmoreurl' => $viewmoreurl,
			];
			return $OUTPUT->render_from_template('gamificationboards_leaderboard/innercontent', $innercontent);
		}

	}
	protected function monthly_info($eventname,$courseid){
		global $DB,$OUTPUT,$COURSE,$USER;

		$tablename = 'block_gm_monthly_'.$eventname;

		$type = get_config('block_gamification','type');

		if($type == 'rank'){
			$userrank_sql = "SELECT x.monthlyrank as rank, x.monthlypoints as points, GROUP_CONCAT(DISTINCT x.userid) AS userid FROM {{$tablename}} AS x JOIN {user} AS u ON u.id = x.userid WHERE month = (SELECT max(month) from {{$tablename}})";
			if(!is_siteadmin()){
        		$userrank_sql .= " AND u.open_costcenterid = $USER->open_costcenterid ";
    		}
			if($eventname == 'course'){
				$userrank_sql .= " AND courseid=$courseid";
			}
			$userrank_sql .= " GROUP BY monthlyrank ORDER BY monthlyrank ASC LIMIT 0, 5";
			$userranks = $DB->get_records_sql($userrank_sql);
			$name = 'Rank';
		}else if($type == 'level'){
			$userrank_sql = "SELECT x.monthlylevel as rank FROM {{$tablename}} AS x JOIN {user} as u ON u.id = userid where month = (SELECT max(month) from {{$tablename}})";
			if(!is_siteadmin()){
        		$userrank_sql .= " AND u.open_costcenterid = $USER->open_costcenterid ";
    		}
			if($eventname == 'course'){
				$userrank_sql .= " WHERE courseid=$courseid";
			}
			$userrank_sql .= " GROUP BY monthlylevel ORDER BY monthlylevel DESC LIMIT 0, 5";
			$userranks = $DB->get_records_sql($userrank_sql);
			$name = 'Level';
		}
		if(empty($userranks)){
			return get_string('no_data_available', 'gamificationboards_leaderboard');
		}else{
			$leaderboard = array();
			foreach($userranks as $rank){
				if($rank->rank){
					if($type == 'level'){
						$userids = $DB->get_records_sql_menu("SELECT id, userid FROM {{$tablename}} WHERE month = (SELECT max(month) from {{$tablename}}) AND monthlylevel = $rank->rank ORDER BY monthlypoints DESC LIMIT 0,4");
						$user_array = array();
						foreach ($userids as $userid) {
							$user_array[] = $userid;
						}
						$userslist = array_slice($user_array , 0, 4);
					} else  if($type == 'rank'){
						$users = explode(',', $rank->userid);
						$userslist = array_slice($users , 0, 4);
					}
					$userpictures_array = array();
					foreach ($userslist as $user) {
						if($type == 'level'){
							$userpoints = $DB->get_record_sql("SELECT id,monthlypoints FROM {{$tablename}} WHERE month = (SELECT max(month) from {{$tablename}}) AND userid=$user AND monthlylevel = $rank->rank");
						} else if($type == 'rank'){
							$userpoints = $DB->get_record_sql("SELECT id,monthlypoints FROM {{$tablename}} WHERE month = (SELECT max(month) from {{$tablename}}) AND userid=$user AND monthlyrank = $rank->rank");
						}
						$userinfo = \core_user::get_user($user);
						if($userinfo->id == $USER->id || is_siteadmin()){
							$systemcontext = \context_system::instance();
							$userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo)."<span class='score clickable' onclick='(function(e){ require(\"gamificationboards_leaderboard/pointsinfo\").pointsInfo({selector:\"user_points_description_modal\", context:".$systemcontext->id.",userid:".$user.",eventname:\"$eventname\",type:\"month\",objectid:".$userpoints->id.",points:".$userpoints->monthlypoints.",courseid:".$courseid."}) })(event)'>".$userpoints->monthlypoints."</span></span>";

						}else{
							$userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo)."<span class='score'>".$userpoints->monthlypoints."</span></span>";
						}

					}
					$userpictures = implode('',$userpictures_array);
					if($rank->rank > 10){
						$rank_class = 'last';
					} else {
						$rank_class = $rank->rank;
					}

					$leaderboardcols=array();
					$leaderboardcols['rank_class'] = $rank_class;
					$leaderboardcols['rank'] = $rank->rank;
					$leaderboardcols['name'] = $name;
					$leaderboardcols['userpictures'] = $userpictures;
					$leaderboard[] = $leaderboardcols;
				}else{
					$leaderboard[] = '';					
				}
			}
			if(!empty($leaderboard)){
				$viewmoreurl = new \moodle_url('/local/gamification/boards/leaderboard/monthly_detailed_info.php?eventname='.$eventname, array('course' => $courseid));
			}else{
				$viewmoreurl = false;
			}
			$innercontent = [
				'leaderboard' => $leaderboard, 
				'viewmoreurl' => $viewmoreurl,
			];
			return $OUTPUT->render_from_template('gamificationboards_leaderboard/innercontent', $innercontent);
		}
		// return 'true';
	}

	protected function weekly_info($eventname,$courseid){
		global $DB,$OUTPUT,$COURSE,$USER;
		$tablename = 'block_gm_weekly_'.$eventname;
		$type = get_config('block_gamification','type');

		if($type == 'rank'){
			$userrank_sql = "SELECT x.weeklyrank as rank, x.weeklypoints as points, GROUP_CONCAT(DISTINCT x.userid) AS userid FROM {{$tablename}} AS x JOIN {user} AS u ON u.id=x.userid WHERE week = (SELECT max(week) FROM {{$tablename}}) ";
			if(!is_siteadmin()){
        		$userrank_sql .= " AND u.open_costcenterid = $USER->open_costcenterid ";
    		}
			if($eventname == 'course'){
				$userrank_sql .= " AND courseid=$courseid";
			}
			$userrank_sql .= " GROUP BY weeklyrank ORDER BY weeklyrank ASC LIMIT 0, 5";
			$userranks = $DB->get_records_sql($userrank_sql);
			$name = 'Rank';
		} else if($type == 'level'){
			$userrank_sql = "SELECT x.weeklylevel as rank FROM {{$tablename}} as x JOIN {user} as u on u.id=x.userid where week = (SELECT max(week) from {{$tablename}}) ";
			if(!is_siteadmin()){
        		$userrank_sql .= " AND u.open_costcenterid = $USER->open_costcenterid ";
    		}
			if($eventname == 'course'){
				$userrank_sql .= " WHERE courseid=$courseid";
			}
			$userrank_sql .= " GROUP BY weeklylevel ORDER BY weeklylevel DESC LIMIT 0, 5";
			$userranks = $DB->get_records_sql($userrank_sql);
			$name = 'Level';
		}
		if(empty($userranks)){
			return get_string('no_data_available', 'gamificationboards_leaderboard');
		}else{
			$leaderboard = array();
			foreach($userranks as $rank){
				if($rank->rank){
					if($type == 'level'){
						$userids = $DB->get_records_sql_menu("SELECT id, userid FROM {{$tablename}} WHERE week = (SELECT max(week) from {{$tablename}}) AND weeklylevel = $rank->rank ORDER BY weeklypoints DESC LIMIT 0,4");
						$user_array = array();
						foreach ($userids as $userid) {
							$user_array[] = $userid;
						}
						$userslist = array_slice($user_array , 0, 4);
					}else if($type == 'rank'){
						$users = explode(',', $rank->userid);
						$userslist = array_slice($users , 0, 4);
					}
					$userpictures_array = array();
					foreach ($userslist as $user) {
						if($type == 'level'){
							$userpoints = $DB->get_record_sql("SELECT id,weeklypoints FROM {{$tablename}} WHERE week = (SELECT max(week) from {{$tablename}}) AND userid=$user AND weeklylevel = $rank->rank");
						} else if($type == 'rank'){
							$userpoints = $DB->get_record_sql("SELECT id,weeklypoints FROM {{$tablename}} WHERE week = (SELECT max(week) from {{$tablename}}) AND userid=$user AND weeklyrank = $rank->rank");
						}
						$userinfo = \core_user::get_user($user);
						if($userinfo->id == $USER->id || is_siteadmin()){
							$systemcontext = \context_system::instance();
							$userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo)."<span class='score clickable' onclick='(function(e){ require(\"gamificationboards_leaderboard/pointsinfo\").pointsInfo({selector:\"user_points_description_modal\", context:".$systemcontext->id.",userid:".$user.",eventname:\"$eventname\",type:\"week\",objectid:".$userpoints->id.",points:".$userpoints->weeklypoints.",courseid:".$courseid."}) })(event)'>".$userpoints->weeklypoints."</span></span>";

						}else{
							$userpictures_array[] = '<span class="user_info">'.$OUTPUT->user_picture($userinfo)."<span class='score'>".$userpoints->weeklypoints."</span></span>";
						}

					}
					$userpictures = implode('',$userpictures_array);
					if($rank->rank > 10){
						$rank_class = 'last';
					} else {
						$rank_class = $rank->rank;
					}
					$leaderboardcols=array();
					$leaderboardcols['rank_class'] = $rank_class;
					$leaderboardcols['rank'] = $rank->rank;
					$leaderboardcols['name'] = $name;
					$leaderboardcols['userpictures'] = $userpictures;
					$leaderboard[] = $leaderboardcols;

				}else{
					$leaderboard[] = '';					
				}
			}
			if(!empty($leaderboard)){
				$viewmoreurl = new \moodle_url('/local/gamification/boards/leaderboard/weekly_detailed_info.php?eventname='.$eventname, array('course' => $courseid));
			}else{
				$viewmoreurl = false;
			}
			$innercontent = [
				'leaderboard' => $leaderboard, 
				'viewmoreurl' => $viewmoreurl,
			];
			return $OUTPUT->render_from_template('gamificationboards_leaderboard/innercontent', $innercontent);
		}
	}
	public function view_course_leaderboard(){
		global $DB,$COURSE;
		$courseleaderboard = new \gamificationboards_leaderboard\course();
		$out = '<div class = "heading-str">'.get_string('weeklygamification','gamificationboards_leaderboard').'</div>';
		$out .= $courseleaderboard->weekly_leaderboard();
		$out .= $courseleaderboard->get_my_week_rank();
		if(!empty($out)){
			$out .= \html_writer::link(new \moodle_url('/blocks/gamification/dashboard.php', array('eventname' => 'course','course' => $COURSE->id)),get_string('viewmore', 'gamificationboards_leaderboard'), array('class'=>'viewmore pull-right'));
		}
		return $out;
	}
}