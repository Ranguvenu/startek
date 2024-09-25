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
 * @subpackage block_learnerscript
 */
use block_learnerscript\local\pluginbase;

class plugin_logreportcolumns extends pluginbase {

    public function init() {
        $this->fullname = get_string('logreportcolumns', 'block_learnerscript');
        $this->type = 'undefined';
        $this->form = true;
        $this->reporttypes = array('logreport');
    }

    public function summary($data) {
        return format_string($data->columname);
    }

    public function colformat($data) {
        $align = (isset($data->align)) ? $data->align : '';
        $size = (isset($data->size)) ? $data->size : '';
        $wrap = (isset($data->wrap)) ? $data->wrap : '';
        return array($align, $size, $wrap);
    }

    // Data -> Plugin configuration data.
    // Row -> Complet user row c->id, c->fullname, etc...
    public function execute($data, $row, $user, $courseid, $starttime = 0, $endtime = 0) {
        global $DB;
        $others = json_decode($row->other);
        $learningplan_name=$DB->get_field_sql("SELECT name FROM {local_learningplan} where id=:lpid", array('lpid'=>$others->learningplan_id));
        $test = array('context' => \context_system::instance(), 'objectid' => $row->objectid, 'component' => 'local_learningplan','userid'=>$row->userid, 'other' =>  array('userid'=>$others->userid,'learningplan_id'=>$others->learningplan_id,'lpname'=>$learningplan_name?$learningplan_name:$others->lpname, 'courseid' => $others->courseid, 'coursename' => $others->coursename));
        $event = $row->eventname::create($test);
        switch ($data->column) {
            case 'date':
                $row->{$data->column} = !empty($row->timecreated) ? date('d F Y, h:i A',$row->timecreated)/*userdate($row->timecreated,get_string('strftimedatemonthabbr', 'core_langconfig'))*/ : '--';
            break;
            case 'component':
                if(!isset($row->component) && isset($data->subquery)) {
                    $component =  $DB->get_field_sql($data->subquery);
                } else {
                    $component = get_string('pluginname', $row->component);
                } 
                $row->{$data->column} = !empty($component) ? $component : '--';
            break;
            case 'name':
                if(!isset($row->name) && isset($data->subquery)) {
                    $name =  $DB->get_field_sql($data->subquery);
                } else {
                    $name = $event->get_name();
                } 
                $row->{$data->column} = !empty($name) ? $name : '--';
            break;
            case 'description':
                if(!isset($row->description) && isset($data->subquery)) {
                    $description =  $DB->get_field_sql($data->subquery);
                } else {
                    $description = $event->get_description();
                } 
                $row->{$data->column} = !empty($description) ? $description : '--';
            break;
            case 'usercreated';
                if(!isset($row->usercreated) && isset($data->subquery)) {
                    $usercreated =  $DB->get_field_sql($data->subquery);
                } else {
                    $usercreated = $DB->get_field_sql("SELECT CONCAT(firstname, ' ', lastname) FROM {user} WHERE id = $row->userid");
                } 
                $row->{$data->column} = !empty($usercreated) ? $usercreated : '--';
            break;
        }
        return (isset($row->{$data->column}))? $row->{$data->column} : ' -- ';
    }

}
