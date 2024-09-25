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
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
$eventname = required_param('eventname',PARAM_TEXT);

$courseid = optional_param('course', SITEID, PARAM_INT);


$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js(new moodle_url('/local/gamification/boards/leaderboard/js/jquery.dataTables.min.js'),true);
$PAGE->requires->js_call_amd('gamificationboards_leaderboard/pointsinfo', 'load', array());
$PAGE->requires->css('/local/gamification/boards/leaderboard/css/jquery.dataTables.min.css');

if($courseid == SITEID){
	$context = context_system::instance();	
} else {
	$context = context_course::instance($courseid);
	$course = get_course($courseid);
	$PAGE->set_course($course);
}
$PAGE->set_url('/local/gamification/boards/leaderboard/overall_detailed_info.php?course='.$courseid.'&eventname='.$eventname);
$PAGE->set_pagelayout('standard');
require_login();
if (isguestuser()) {
    print_error('noguest');
}
$PAGE->set_context($context);
$PAGE->set_title(get_string('overallldata', 'local_gamification'));
$PAGE->navbar->add(get_string('leaderboard', 'local_gamification'), new moodle_url('/blocks/gamification/dashboard.php', array('course'=>$courseid,'eventname' => $eventname)));
$PAGE->navbar->add(get_string('overallldata', 'local_gamification'));

echo $OUTPUT->header();

echo "<h2 class='tmhead2'>".get_string('overallldata', 'local_gamification').'</h2>';
if($courseid == SITEID) {
	$url = new moodle_url('/blocks/gamification/dashboard.php', array());
} else {
	$url = new moodle_url('/blocks/gamification/dashboard.php', array('course'=>$courseid));
}

$back =  html_writer::link($url, 'Back', array('class' => 'back_url'));

echo html_writer::tag('div', $back, array('class'=>'viewall pull-right'));
	$output='';
   $output .='<div class="listng_container text-center w-100 pull-left">
                <div class="listng_filters w-100 pull-left">
                <div class="form-group col-md-6 col-sm-6 col-12 pull-left">
                	<label for="#1">'.get_string('level', 'gamificaiton_leaderboard').'</label>
                 	<input type="text" id="1"  class="employee-search-input form-control" placeholder="Level">
				</div>
				<div class="form-group col-md-6 col-sm-6 col-12 pull-left">
					<label for="#2">'.get_string('employee_name', 'gamificaiton_leaderboard').'</label>
					<input type="text" id="2"  class="employee-search-input form-control" placeholder="Employee Name">
                </div>
                </div>
                </div>
              </div>';
          
 $output .='<table class="leaderboard" id = "picture_list" cellpadding="30" cellspacing="10" style="width:100%;">
			<thead>
			<tr>
			<th> </th>
			<th> </th>
			<th> </th>
			
			</tr>
			</thead>';
          // <th>Progress</th>
            
          $output .='</table>';
			  

    $output .= html_writer::script("$(document).ready(function() {

    	

								   $.fn.dataTable.ext.errMode = 'none';
					var oTable = $('#picture_list').DataTable( {
						'lengthMenu': [ [10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] ],
    					'pageLength': 20,
						'lengthChange': true,
						'processing': true,
						'serverSide': true,
						'ajax': M.cfg.wwwroot + '/local/gamification/boards/leaderboard/overall_ajax.php?eventname=$eventname&course=$courseid',
			
					});
					$('.dataTables_filter').css('display','none');
					$('.employee-search-input').on( 'keyup  change', function () {
							var i =$(this).attr('id');  // getting column index
							var v =$(this).val();  // getting search input value
							oTable.columns(i).search(v).draw();
					} );
					$('table#picture_list thead').css('display' , 'none');
                });
              ");
    
    echo $output;

echo $OUTPUT->footer();