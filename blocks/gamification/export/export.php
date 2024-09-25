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

/** Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */
// use 
function export_report($report) { // 2nd parameter by Vinod
    //$report, $reportconfig = ''
    global $DB, $CFG;
    require_once($CFG->dirroot . '/lib/excellib.class.php');
    $filename = 'report_' . (time()) . '.xls'; //this line is default

    $table = new html_table();
    $table->id = "Overallreport";
    $head = array();
    $head[] = ('Name');
    $head[] = ('Courses');
    $head[] = ('Points');
    $head[] = ('Rank');
    $table->head = $head;

    $type = $report->reporttype == 'overall' ? $report->reporttype : $report->reporttype.'ly' ;
    $class = "\\block_gamification\\local\\events\\$type";
    
    $workbook = new MoodleExcelWorkbook("-");
    foreach($report->event as $eventcode) {
        $event = $DB->get_record('block_gm_events', ['eventcode'=>$eventcode]);
        $eventname = ucwords(str_replace('_', ' ', $event->shortname));
        $myxls = $workbook->add_worksheet($eventname);
        $obj = new $class($event, 2, $DB);
        if($report->reporttype == 'overall'){
        $records = $obj->get_overall_logs(); 
        }
        if($report->reporttype == 'month'){
           $records = $obj->get_monthly_logs();  
        }
        if($report->reporttype == 'week'){
           $records = $obj->get_weekly_logs();  
       }
        $matrix = array();
        $data = array();
        foreach($records as $rkey => $record) {
            $list = array();
            $name = $DB->get_record('user',array('id' => $record->userid));
            $list[] = fullname($name);
            $cn = array();
            $coursenames = $DB->get_records_sql('SELECT fullname from {course} where id in ('.$record->courseid.')');
            foreach($coursenames as $coursename){
               $cn[] = $coursename->fullname;
            }
            $display = implode(',',$cn);
            $list[] = $display;
            $list[] = $record->points;
            $list[] = $record->rank;
            $data[] = $list;
        }
        $table->data = $data;
        if (!empty($table->head)) {
            $countcols = count($table->head);
            $keys = array_keys($table->head);
            $lastkey = end($keys);
            foreach ($table->head as $key => $heading) {
                
                $matrix[1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
            } 
        }

        if (!empty($table->data)) {
            // print_object($table->head);
            // print_object($table->data);
            // exit;
            foreach ($table->data as $rkey => $row) {
                foreach ($row as $key => $item) {
                    $matrix[$rkey + 2][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
                }
            }
        }
        $filename = 'report.xls';
        $downloadfilename = clean_filename($filename);
        $workbook->send($downloadfilename);
        
        foreach ($matrix as $ri => $col) {
            foreach ($col as $ci => $cv) {
                $format = array('border'=>1);
                if($ri == 1){
                    $format['bold'] = 1;
                    $format['bg_color'] = '#2c4e86';
                    $format['color'] = '#FFFFFF';
                }
                if(is_numeric($cv)){
                    $format['align'] = 'center';
                    $myxls->write_number($ri, $ci, $cv, $format);
                   
                } else {
                    $myxls->write_string($ri, $ci, $cv, $format);
                   
                }
            }
        }
    // $workbook->save();
   
    $workbook->close();
  } 
   exit;
}