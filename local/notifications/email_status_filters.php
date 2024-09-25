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
 * @subpackage local_notifications
 */


define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $PAGE, $OUTPUT,$DB;

if ($_REQUEST['columns'][1]['search'] != "" ){
   $organization_search=$_REQUEST['columns'][1]['search']['value'] ;
}
if ( $_REQUEST['columns'][2]['search'] != "" ){
      $status_search=$_REQUEST['columns'][2]['search']['value'] ;
}


$countquery="SELECT count(le.id) FROM {local_emaillogs} AS le " ;//INNER JOIN {local_notification_info} AS ni ON le.notification_infoid=ni.id where 1=1 ";

$select_query = "SELECT le.id,le.notification_infoid,le.from_userid,le.to_userid,le.status,
	le.timecreated,le.sent_date,ni.open_path,ni.notificationid, 
	concat(u.firstname,' ', u.lastname) AS to_username,
	(SELECT concat(u.firstname,' ', u.lastname) FROM {user} as u WHERE u.id=le.from_userid) as from_username, lnt.name AS notification_type, lc.fullname AS organization
	FROM {local_emaillogs} AS le ";
$from_query =	" INNER JOIN {local_notification_info} AS ni ON le.notification_infoid=ni.id 
	JOIN {user} as u ON u.id = le.to_userid
	JOIN {local_notification_type} as lnt ON lnt.id=ni.notificationid
	JOIN {local_costcenter} as lc ON concat('/',ni.open_path,'/') LIKE concat('%/',lc.id,'/%') AND lc.depth = 1
	WHERE 1=1 ";


$systemcontext =(new \local_notifications\lib\accesslib())::get_module_context();
$params = array();
if(!(is_siteadmin())){
	$cond_query .= (new \local_notifications\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='ni.open_path');
}

if (isset($organization_search) && $organization_search != ""){

	$cond_query .= " AND concat('/',ni.open_path,'/') LIKE :orgid";
	$params['orgid'] = '%'.$organization_search.'%';

}
if(isset($status_search) && $status_search != ""){

	// $cond_query.=" AND le.status=:status ";
	// $params['status'] = $status_search;
	if($status_search){
		$cond_query .= " AND le.status=:status ";
	}else{
		$cond_query .= " AND (le.status=:status OR le.status IS NULL) ";
	}
	$params['status'] = $status_search;

	
}else{

	//$query.="";
	

}

 $resultcount=$DB->count_records_sql($countquery.$from_query.$cond_query, $params);

 // print_object($resultcount);
 $cond_query .=" order by id desc";

 $result = $DB->get_records_sql($select_query.$from_query.$cond_query, $params, $_REQUEST['start'], $_REQUEST['length']);

$data = array();
foreach ($result as $key => $value){

	if($value->status==1){
		$status="Sent";
	}else{
		$status="Not Sent";
	}

	
	$created_date= \local_costcenter\lib::get_userdate("d/m/Y H:i", $value->timecreated);
	if($value->sent_date!="" && $value->sent_date!=0 ){
		$send_date= \local_costcenter\lib::get_userdate("d/m/Y H:i", $value->sent_date);
	}else{
		$send_date="N/A";
	}


	$row = array($value->organization,$value->from_username,$value->to_username,$value->notification_type,$created_date,$send_date,$status,'<a href="'.$CFG->wwwroot.'/local/notifications/email_status_details.php?id='.$value->id.'" target="_blank">View</a>');
	$data[] = $row;

}

$iTotal = $resultcount;
$outputs = array(
        "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 0,
        "sEcho" => intval($requestData['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iTotal,
        "aaData" => $data
    );
echo json_encode($outputs);
          

