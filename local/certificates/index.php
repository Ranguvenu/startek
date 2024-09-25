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
 * @subpackage local_certificates
 */


require_once(dirname(__FILE__) . '/../../config.php');

global $CFG,$PAGE, $USER;

require_once($CFG->dirroot . '/local/certificates/lib.php');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot .'/local/certificates/index.php');
$PAGE->set_title(get_string('pluginname', 'local_certificates'));
$PAGE->set_heading(get_string('manage_certificates', 'local_certificates'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'local_certificates'));

require_login();

require_capability('local/certificates:view',$systemcontext);

$renderer = $PAGE->get_renderer('local_certificates');

$PAGE->requires->jquery();
$PAGE->requires->css('/local/certificates/css/datatables.min.css');

$PAGE->requires->js_call_amd('local_certificates/deletecertificate', 'init');
$PAGE->requires->js_call_amd('local_certificates/script' , 'certificates');

$id = optional_param('id',0,PARAM_INT);

echo $OUTPUT->header();


	if (has_capability('local/certificates:create', $systemcontext)) {
		$createurl = new moodle_url('/local/certificates/edit.php',array());
		$createicon = '<i class="icon fa fa-plus" aria-hidden="true"></i>';
		$createlink = html_writer::link($createurl, $createicon, array('class'=>'course_extended_menu_itemlink'));

		$menu1 = html_writer::tag('li', $createlink, array('title'=>get_string('create_cert','local_certificates')));

		echo html_writer::tag('ul',$menu1, array('class'=>'course_extended_menu_list'));
	}

	if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations',$systemcontext)){
		$certificates = $DB->get_records('local_certificate',array(),'id DESC','*');

		//print_object($certificates);exit
	}else if(!is_siteadmin() && has_capability('local/costcenter:manage_ownorganization', $systemcontext)){
		$certificates = $DB->get_records('local_certificate',array('costcenter'=>$USER->open_costcenterid),'id DESC','*');
	}else if(!is_siteadmin() && has_capability('local/costcenter:manage_owndepartments', $systemcontext)){
        $array['costcenter'] = $USER->open_costcenterid;
        // commenting as certificates are not designed as per department
        // $array['department'] = $USER->open_departmentid;
        $certificates = $DB->get_records('local_certificate', $array, 'id DESC','*');
    }else{
		$certificates = array();
	}
	
	if($certificates){
		$data = array();
		foreach ($certificates as $certificate) {
			$row = array();
			$certurl = new moodle_url('/local/certificates/rearrange.php',array('ctid'=>$certificate->id));
			$certnamelink = html_writer::link($certurl, $certificate->name, array('target' =>'_blank'));
			$row[] = $certnamelink;
			
			if (has_capability('local/certificates:edit', $systemcontext)) {
				$editurl = new moodle_url('/local/certificates/edit.php',array('tid'=>$certificate->id));
				$editicon = '<i class="fa fa-pencil icon"></i>';
				$array = array('title'=>get_string('edit'),
								'alt'=>get_string('edit'));
				$edit = html_writer::link($editurl, $editicon, $array);
			}else{
				$edit = null;
			}
			
			$exists = array();
			if(is_siteadmin() || has_capability('local/certificates:delete', $systemcontext)){
				$core_component = new \core_component();
				$plugins = $core_component::get_plugin_list('local');
				$localplugins = array_keys($plugins);
				$unwantedplugins = array('assignroles', 'catalog', 'certificates', 'challenge', 'competency', 'costcenter', 'evaluation', 'forum', 'gamification', 'groups', 'hierarchy' , 'ilp', 'location',
					'myteam', 'notifications', 'ratings', 'request', 'skillrepository', 'tags', 'udemy', 'users',
					'wavatar');
				$learningmodules = array_diff($localplugins, $unwantedplugins);
				
				foreach($learningmodules AS $pluginname){
					switch($pluginname){
		            	case 'courses':
		            	$exists[] = $DB->record_exists('course' , array('open_certificateid'=>$certificate->id));
		            	break;
		            	case 'classroom':
	                    $exists[] = $DB->record_exists('local_classroom' , array('certificateid'=>$certificate->id));
		            	break;
		            	case 'program':
		            	$exists[] = $DB->record_exists('local_program', array('certificateid'=>$certificate->id));
		            	break;
		            	case 'learningplan':
		            	$exists[] = $DB->record_exists('local_learningplan', array('certificateid'=>$certificate->id));
		            	break;
		            	case 'onlinetests':
		            	$exists[] = $DB->record_exists('local_onlinetests', array('certificateid'=>$certificate->id));
		            	break;
		            	case 'certification':
		            	$exists[] = $DB->record_exists('local_certification', array('certificateid'=>$certificate->id));
					}					
				}
				$deleteurl = 'javascript:void(0)';
				$deleteicon = '<i class="icon fa fa-trash fa-fw"></i>';
        	}
			
	        if(!array_filter($exists)){
		        $array = array('title'=>get_string('delete'),
										'alt'=>get_string('delete'),
										'onclick'=>"(function(e){ require('local_certificates/deletecertificate').deleteConfirm({ action: 'delete_certificate' ,id:".$certificate->id.", fullname:'".$certificate->name."'}) })(event)");
						$delete = html_writer::link($deleteurl, $deleteicon, $array);
				     
				}else{
					$array = array('title'=>get_string('cantdelete_certificate','local_certificates'),'alt'=>get_string('cantdelete_certificate','local_certificates'),'class' =>'disabled');
					$delete = html_writer::link($deleteurl, $deleteicon, $array);
				}

				$row[] = $edit.' '.$delete;
				$data[] = $row;
			}
			$table = new html_table();
			$table->id = 'certificateslist';
			$table->head = array(get_string('certificatename','local_certificates'),get_string('actions'));
			$table->align = array('left','center');
			$table->size = array('80','20');
			$table->data = $data;

			echo html_writer::table($table);
	}else{
		echo html_writer::tag('div', get_string('no_records', 'local_certificates'), array('class'=>'text-center alert alert-info mt-1'));
	}

echo $OUTPUT->footer();



