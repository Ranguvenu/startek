<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
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

/**
 * elearning  courses
 *
 * @package    block_userdashboard
 * @copyright  2018 Maheshchandra <maheshchandra@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_gamification\output;
class leaderboard {
	public function leaderboard($courseid){
		global $OUTPUT;
		$weeklyInfo = $this->weeklyLeaderboard($courseid);
		$monthlyInfo = $this->monthlyLeaderboard($courseid);
		$overallInfo = $this->overallLeaderboard($courseid);
		return $OUTPUT->render_from_template('block_gamification/leaderboard', array('weekly' => $weeklyInfo, 'monthly' => $monthlyInfo, 'overall' => $overallInfo));
	}
	public function weeklyLeaderboard($courseid){
		global $CFG, $OUTPUT;
		$leaderboard = new \block_gamification\local\leaderboard();
		$now = time();
        $weekstart = get_config('block_gamification', 'weekstart');
        $weekdays = array(0 => 'Sunday',1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');
        $weekday = $weekdays[$weekstart];
        $weekstart = strtotime("last ".$weekday, $now);
        $weekend = strtotime("next ".$weekday, $now);
		$threerankerinfo = $leaderboard->getTopThreeRankersinfo($courseid, 'weekly', $weekstart, $weekend);
		$leaderboardinfo = $leaderboard->leaderboardData($courseid, 'weekly', $weekstart, $weekend, 3, 7);
		$params = array(
            'three_ranks_data' => $threerankerinfo, 
            'leaderboarddata' => $leaderboardinfo, 
            'rooturl' => $CFG->wwwroot, 
            'courseid' => $courseid
        );
		return $OUTPUT->render_from_template('block_gamification/block_view', $params);
	}
	public function monthlyLeaderboard($courseid){
		global $CFG, $OUTPUT;
		$now = time();
		$startDateOfMonth = date("Y-m-01", $now);
        $lastDateOfMonth = date("Y-m-t", $now);
        $monthstart = strtotime($startDateOfMonth);
        $monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
        $leaderboard = new \block_gamification\local\leaderboard();
		$threerankerinfo = $leaderboard->getTopThreeRankersinfo($courseid, 'monthly', $monthstart, $monthend);
		$leaderboardinfo = $leaderboard->leaderboardData($courseid, 'monthly', $monthstart, $monthend, 3, 7);
		$params = array(
            'three_ranks_data' => $threerankerinfo, 
            'leaderboarddata' => $leaderboardinfo, 
            'rooturl' => $CFG->wwwroot, 
            'courseid' => $courseid
        );
        return $OUTPUT->render_from_template('block_gamification/block_view', $params);
	}
	public function overallLeaderboard($courseid){
		global $CFG, $OUTPUT;
		$leaderboard = new \block_gamification\local\leaderboard();
		$threerankerinfo = $leaderboard->getTopThreeRankersinfo($courseid, 'overall', 0, 0);
		$leaderboardinfo = $leaderboard->leaderboardData($courseid, 'overall', 0, 0, 3, 7);
        $now = time();
        $weekstart = get_config('block_gamification', 'weekstart');
        $weekdays = array(0 => 'Sunday',1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');
        $weekday = $weekdays[$weekstart];
        $weekstart = strtotime("last ".$weekday, $now);
        $weekend = strtotime("next ".$weekday, $now);
        $startDateOfMonth = date("Y-m-01", $now);
        $lastDateOfMonth = date("Y-m-t", $now);
        $monthstart = strtotime($startDateOfMonth);
        $monthend = strtotime($lastDateOfMonth)+86399;//taking timestamp of eod.
		$params = array(
            'three_ranks_data' => $threerankerinfo, 
            'leaderboarddata' => $leaderboardinfo, 
            'rooturl' => $CFG->wwwroot, 
            'courseid' => $courseid,
            'weekstart' => $weekstart,
            'weekend' => $weekend,
            'monthstart' => $monthstart,
            'monthend' => $monthend
        );
        // return $OUTPUT->render_from_template('block_gamification/block_view', $params);
        return $OUTPUT->render_from_template('block_gamification/leaderboard', $params);
	}
	public function leaderboard_data_display($type, $courseid, $startdate, $enddate, $filter=false){
		global $USER,$OUTPUT;
		if($courseid > 1){
			$pagecontext = \context_course::instance($courseid);
		}else{
        	$pagecontext = \context_system::instance();
		}

        $options = array('targetID' => 'leaderboard_content_aggregated','perPage' => 15, 'cardClass' => 'col-md-4', 'viewType' => 'card');
        
        $options['methodName']='block_gamification_detailed_leaderboard_view';
        $options['templateName']='block_gamification/detailed_leaderboard_view'; 
        $options = json_encode($options);

        $dataoptions = array('contextid' => $pagecontext->id);
        $dataoptions['type'] = $type;
        $dataoptions['courseid'] = $courseid;
        $dataoptions['startdate'] = $startdate;
        $dataoptions['enddate'] = $enddate;

        $dataoptions = json_encode($dataoptions);
        $filterdata = json_encode(array());

        $context = [
                'targetID' => 'leaderboard_content_aggregated',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];

        if($filter){
            return  $context;
        }else{
            return  $OUTPUT->render_from_template('local_costcenter/cardPaginate', $context);
        }
	}
}