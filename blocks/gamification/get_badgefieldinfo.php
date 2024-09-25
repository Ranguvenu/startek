<?php
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__).'/../../config.php');
// global $CFG;
// require_once($CFG->wwwroot.'/config.php');
// echo 'hello';
global $DB,$USER;
$costcentervalue = optional_param('costcenter', '0', PARAM_INT);
if($costcentervalue){
	// $DB->get_field('block_gm_points',)
	$event_points = $DB->get_records_menu('block_gm_points',array(),'','eventid,costcentercontent');
	$totaldata = array();
	foreach($event_points as $key => $value){//key == eventid ,value == costcentercontent.
		$data = array();

		$decoded = json_decode($value);
		foreach($decoded as $costcenterkey => $costcentercontent){
			if($costcentervalue == $costcenterkey){
				$content = json_decode($costcentercontent);
				break;
			}
		}
		$data['eventid'] = $key;
		$data['value'] = $content->points ? $content->points : 0 ;
		$data['active'] = $content->active;
		$data['badgeactive'] = $content->badgeactive ? $content->badgeactive : 0;
		$totaldata[] = $data; 
	}
	echo json_encode($totaldata);
}else{
	$value = required_param('value', PARAM_INT);
	$event = $DB->get_field('block_gm_events','eventcode',array('id'=> $value));
	$options = '';
	// echo $event;exit;
	if($event == 'ce' || $event == 'cc'){
		$options = $DB->get_records_select_menu('course', 'id!=1 AND open_costcenterid='.$USER->open_costcenterid.' order by id ASC', array(), '','id,fullname');
	}elseif($event == 'clc'){
		$options = $DB->get_records_menu('local_classroom', array('costcenter' => $USER->open_costcenterid), '', 'id,name');
	}elseif($event == 'ctc'){
		$options = $DB->get_records_menu('competency', array(), '', 'id,shortname');
	}elseif($event == 'lpc'){
		$options = $DB->get_records_menu('local_learningplan', array('costcenter' => $USER->open_costcenterid), '', 'id,name');
	}elseif($event == 'progc'){
		$options = $DB->get_records_menu('local_program', array('costcenter' => $USER->open_costcenterid), '', 'id,name');
	}elseif($event == 'certc'){
		$options = $DB->get_records_menu('local_certification', array('costcenter' => $USER->open_costcenterid), '', 'id,name');
	}

	echo json_encode(['data'=>$options, 'event' => $event]);
}