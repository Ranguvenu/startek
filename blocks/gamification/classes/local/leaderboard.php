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
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*
* @author eabyas <info@eabyas.in>
* @package BizLMS
* @subpackage block_gamification
*/
namespace block_gamification\local;
defined('MOODLE_INTERNAL') || die();
class leaderboard {
	public static function getTopThreeRankersinfo($courseid, $type, $startdate, $enddate, $filterdata = null){
		global $DB, $OUTPUT;
		list($sql, $params) = self::get_leaderboard_sql($courseid, $type, $startdate, $enddate);
		$threerankers = $DB->get_records_sql($sql, $params, 0, 3);
		$topThree = array();
		$i = 1;
		foreach($threerankers AS $rankinfo){
			$rankdata = array();
			$rankdata['rank'] = $i;
			$rankdata['username'] = fullname($rankinfo);
			$rankdata['email'] = $rankinfo->email;
			$rankdata['userpicture'] = $OUTPUT->user_picture($rankinfo, array('link' => false));
			$rankdata['points'] = $rankinfo->points;
			$topThree[] = $rankdata;
			$i++;
		}
		if(!is_siteadmin() || has_capability('block/gamification:earngamification', \context_system::instance())){

		}
		return $topThree;
	}
	public static function leaderboardData($courseid, $type, $startdate, $enddate, $offset, $limit, $filterdata = null){
		global $DB, $OUTPUT;
		list($sql, $params) = self::get_leaderboard_sql($courseid, $type, $startdate, $enddate, (array) $filterdata);
		$leaderboardContent = $DB->get_records_sql($sql, $params, $offset, $limit);
		$leaderboarddata = array();
		foreach($leaderboardContent AS $content){
			// print_object($content);
			$rankdata = array();
			$rankdata['rank'] = $offset+1;
			$rankdata['username'] = fullname($content);
			$rankdata['email'] = $content->email;
			$rankdata['userpicture'] = $OUTPUT->user_picture($content, array('link' => false));
			$rankdata['points'] = $content->points;
			$leaderboarddata[] = $rankdata;
			$offset++;
		}
		return $leaderboarddata;
	}
	public static function leaderboardDataCount($courseid, $type, $startdate, $enddate, $filterdata = null){
		global $DB;
		$filterdata = [];
		if(!is_null($filterdata)){
			$filterdata = (array)$filterdata;
		}
		$filterdata['count'] = true;
		list($sql, $params) = self::get_leaderboard_sql($courseid, $type, $startdate, $enddate, $filterdata);
		return $DB->count_records_sql($sql, $params); 
	}
	public static function get_leaderboard_sql($courseid, $type, $startdate, $enddate, $filterparams=NULL){
		// print_object($filterparams);
		global $DB, $USER;
		$sitetables = array('weekly' => 'block_gm_weekly_site', 'monthly' => 'block_gm_monthly_site', 'overall' => 'block_gm_overall_site');
		$coursetables = array('weekly' => 'block_gm_weekly_course', 'monthly' => 'block_gm_monthly_course', 'overall' => 'block_gm_overall_course');
		if($courseid > 1){
			$tablename = $coursetables[$type];
		}else{
			$tablename = $sitetables[$type];
		}
		if(isset($filterparams['count']) && $filterparams['count']){
			$sql = "SELECT count(tab.userid) ";	
		}else{
			$sql = "SELECT tab.userid, tab.points, u.* ";	
		}
		$sql .= " FROM {{$tablename}} AS tab 
			JOIN {user} as u ON u.id = tab.userid 
			WHERE u.deleted = 0 ";
		$params = array();
		$systemcontext = \context_system::instance();
		if(!(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext))){
			$sql .= " AND u.open_costcenterid = :costcenterid ";
			$params['costcenterid'] = $USER->open_costcenterid;
		}elseif(isset($filterparams['costcenterid']) && $filterparams['costcenterid']){
			$sql .= " AND u.open_costcenterid = :costcenterid ";
			$params['costcenterid'] = $filterparams['costcenterid'];
		}
		if(isset($filterparams['departmentid']) && $filterparams['departmentid']){
			$sql .= " AND u.open_departmentid = :departmentid ";
			$params['departmentid'] = $filterparams['departmentid'];
		}
		if($courseid > 1){
			$sql .= " AND tab.courseid = :courseid ";
			$params['courseid'] = $courseid;	
		}
		if(isset($filterparams['search_query']) && $filterparams['search_query'] != ''){
			$sql .= " AND CONCAT(u.firstname,' ',u.lastname) LIKE :usernamelike ";
			$params['usernamelike'] = "%{$filterparams['search_query']}%";	
		}
		if($type == 'weekly'){
			$weekval = $DB->get_field('block_gm_weekly_site', 'week', array('weekstart' => $startdate, 'weekend' => $enddate));
			if($weekval){
				$sql .= " AND tab.week = :weekval ";
				$params['weekval'] = $weekval;
			}else{
				$sql .= " AND 1 != 1 ";
			}
		}else if ($type == 'monthly'){
			$month = $DB->get_field('block_gm_monthly_site', 'month', array('monthstart' => $startdate, 'monthend' => $enddate));
			if($month){
				$sql .= " AND tab.month = :month ";
				$params['month'] = $month;
			}else{
				$sql .= " AND 1 != 1 ";
			}
		}
		$sql .= " ORDER BY points DESC ";
		// $sql = "SELECT log.userid, sum(log.gamification) AS points, u.*
		// 	FROM {block_gamification_log} AS log
		// 	JOIN {user} as u ON u.id = log.userid 
		// 	WHERE u.deleted = 0 ";
		// $systemcontext = \context_system::instance();
		// $params = array();
		// if(!(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext))){
		// 	$sql .= " AND u.open_costcenterid = :costcenterid ";
		// 	$params['costcenterid'] = $USER->open_costcenterid;
		// }
		// if($courseid > 1){
		// 	$sql .= " AND log.courseid = :courseid ";
		// 	$params['courseid'] = $courseid;	
		// }
		// if($startdate){
		// 	$sql .= " AND log.time >= :starttime ";
		// 	$params['starttime'] = $startdate;
		// }
		// if($enddate){
		// 	$sql .= " AND log.time <= :endtime ";
		// 	$params['endtime'] = $enddate;
		// }
		// $sql .= " GROUP BY log.userid order by sum(log.gamification) DESC ";
		// echo $sql;
		// print_object($params);
		return array($sql, $params);
	} 
}