<?php
define('AJAX_SCRIPT', true);
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
global $DB, $PAGE,$USER,$CFG,$OUTPUT,$COURSE;
$PAGE->set_context(context_system::instance());
$courseid = optional_param('course', SITEID, PARAM_INT);
$eventname = optional_param('eventname' , '' , PARAM_TEXT);
$requestData = $_REQUEST;
$requestDatacount=array();
$employee = ''; $level = 0;
    if ( $requestData['columns'][2]['search'] != "" ){
      $employee=$requestData['columns'][2]['search']['value'] ;
    }
    if ( $requestData['columns'][1]['search'] != "" ){
      $level=$requestData['columns'][1]['search']['value'] ;
    }

    // $levelinfo = $DB->get_record_sql("SELECT id FROM {config_plugins} where plugin='block_leaderboard' and name='leaderboard_display' and value='level'");

    $tablename = 'block_gm_weekly_'.$eventname;

    $sTable = "ajax";
    
    // if($levelinfo)  {
    //     $sql1 = "SELECT userid FROM {{$tablename}} WHERE  week = (SELECT max(week) from {{$tablename}} ) GROUP BY weeklylevel ORDER BY weeklylevel DESC ";
    // } else {
    //     $sql1 = "SELECT userid FROM {{$tablename}} WHERE  week = (SELECT max(week) from {{$tablename}} ) GROUP BY weeklyrank ORDER BY weeklyrank ASC ";
    // }
    //                     // $params = array('courseid' => $courseid);
    //                     $entries = $DB->get_recordset_sql($sql1/*, $params*/);
    //                     $ids = array();
    //                     foreach ($entries as $entry) {
    //                         $ids[$entry->userid] = $entry->userid;
    //                     }
    //                     $entries->close();
    //                     list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'param', true, null);
    $levelinfo = get_config('block_gamification','type');
    if($levelinfo == 'level')  {
        $sql2 = 'SELECT u.id, u.firstname, u.lastname, u.email, x.weeklylevel as rank, x.weeklypoints as points ';//.user_picture::fields('u') . ', COALESCE(x.lvl, 1) AS rank, x.weeklypoints as points ' ;

        $sql2 .= " FROM {{$tablename}} x
                  JOIN {user} u
                  ON u.id = x.userid ";
                  $sql2 .= " WHERE x.week = (SELECT max(week) from {{$tablename}} ) ";
            // $params = $inparams;
  // }
    } else {
        $sql2 = 'SELECT x.id as objectid, u.id, u.firstname, u.lastname, u.email, x.weeklyrank as rank, x.weeklypoints as points ';//.user_picture::fields('u') . ', COALESCE(x.lvl, 1) AS rank, x.weeklypoints as points ' ;

        $sql2 .= " FROM {{$tablename}} x
                  JOIN {user} u
                  ON u.id = x.userid ";
                  $sql2 .= " WHERE x.week = (SELECT max(week) from {{$tablename}} ) ";
            // $params = $inparams;
  // }
    }
    if(!is_siteadmin()){
        $sql2 .= " AND u.open_costcenterid = $USER->open_costcenterid";
    } 
    if($eventname == 'course'){
        $sql2 .= " AND courseid = $courseid";
    }
    
    if (isset($level) && $level != "" && is_numeric($level))
    {
        if($levelinfo == 'level')  {
            $sql2.= " and x.weeklylevel = {$level} ";
        } else {
            $sql2.= " and x.weeklyrank = {$level} ";            
        }
    }
    
    if (isset($employee) && $employee != "")
    {
        $sql2.= " and CONCAT(u.firstname,' ',u.lastname) LIKE '%{$employee}%' ";
    }
    
    if($levelinfo == 'level')  {
        $sql2 .=  " ORDER BY x.weeklylevel DESC";
    } else {
        $sql2 .=  " ORDER BY x.weeklyrank ASC";           
    }
    

 $activeusers_count = sizeof($DB->get_records_sql($sql2/*, $params*/));
 
        $sql2.= " LIMIT ".$_GET['start'].", ".$_GET['length'];
    
    $activeusers = $DB->get_records_sql($sql2/*, $params*/);

    
$c = count($activeusers);
$i = 1; $data = array();$record = array();
foreach($activeusers as $activeuser) {
    $record[$activeuser->id] = $activeuser;
    if($i%3 == 0){
        $data[] = $record; 
        $record = array();
    }
    if($i == $c && !empty($record)){
        $data[] = $record; 
    }
    $i++;     
}


$row = array();
foreach($data as $rec) {
    $cell = array();
    foreach ($rec as $userid => $ups) {
        $user = core_user::get_user($userid);
        if($user){
            if($user->id == $USER->id || is_siteadmin()){
                $systemcontext = context_system::instance();
                $attributes = array('class'=>'points', 'onclick' => '(function(e){ require(\'gamificationboards_leaderboard/pointsinfo\').pointsInfo({selector:\'user_points_description_modal\', context:'.$systemcontext->id.',userid:'.$user->id.',eventname:"'.$eventname.'",type:"week",objectid:'.$ups->objectid.',points:'.$ups->points.',courseid:'.$courseid.'}) })(event)');
            }else{
                $attributes = array('class'=>'points');
            }
            $name = html_writer::tag('div', fullname($user), array('class'=>'name'));
            $email = html_writer::tag('div', $user->email, array('class'=>'email'));
            $points = '<span class="points_number">'.get_string('points', 'gamificationboards_leaderboard').': </span>'.html_writer::tag('div', $ups->points, $attributes);
            $picture = html_writer::tag('div', $OUTPUT->user_picture($user,array('link' =>false)).' <div class="user_details">'.$name . $email . $points.'</div>', array('class'=>'picture'));
            
            if($levelinfo == 'level'){
               $cell[] = $picture /*. $name . $email . $points*/ . '<div class="block_gamification-level level-'.$ups->level.' small pull-right" aria-label="Level #'.$ups->level.'"><span>'.$ups->level.'</span></div>';
            } else {
               $cell[] = $picture /*. $name . $email . $points */. '<div class="block_gamification-level level-'.$ups->rank.' small pull-right" aria-label="Rank #'.$ups->rank.'"><span>'.$ups->rank.'</span></div>';
            }
        }
    }
    $row[] = $cell;
}
            
$iTotal = $activeusers_count;
$iFilteredTotal = $iTotal;
$outputs = array(
        "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
        "sEcho" => intval($requestData['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => $row
    );
echo json_encode($outputs);