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
 * LearnerScript Lib
 *
 * @package    block_learnerscript
 * @copyright  2017 eAbyas Info Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
use block_learnerscript\local\ls;
use block_learnerscript\local\reportbase;
use block_learnerscript\local\schedule;
/**
 * [block_learnerscript_pluginfile description]
 * @param  [type] $course        [description]
 * @param  [type] $cm            [description]
 * @param  [type] $context       [description]
 * @param  [type] $filearea      [description]
 * @param  [type] $args          [description]
 * @param  [type] $forcedownload [description]
 * @param  array  $options       [description]
 * @return [type]                [description]
 */
function block_learnerscript_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;
    if ($filearea == 'logo') {
        $itemid = (int) array_shift($args);

        if ($itemid > 0) {
            return false;
        }
        $fs = get_file_storage();
        $filename = array_pop($args);
        if (empty($args)) {
            $filepath = '/';
        } else {
            $filepath = '/' . implode('/', $args) . '/';
        }

        $file = $fs->get_file($context->id, 'block_learnerscript', $filearea, $itemid, $filepath, $filename);

        if (!$file) {
            return false;
        }
        $filedata = $file->resize_image(200, 200);
        \core\session\manager::write_close();
        send_stored_file($file, null, 0, 1);
    }

    send_file_not_found();
}
/**
 * [get_reportheader_imagepath description]
 * @param  boolean $excel [description]
 * @return [type]         [description]
 */
function get_reportheader_imagepath($excel = false) {
    global $CFG;
    $fs = get_file_storage();
    $syscontext = context_system::instance();
    $reportheaderimagepath = '';
    // Now get the full list of stamp files for this instance.
    if ($files = $fs->get_area_files($syscontext->id, 'block_learnerscript', 'logo', 0,
        'filename', false)) {
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if ($filename !== '.') {
                if ($excel) {
                    $reportheaderimagepath = '/var/www/analytics/blocks/learnerscript/pix/logo.jpg';
                } else {
                    $url = moodle_url::make_pluginfile_url($syscontext->id,
                        'block_learnerscript', 'logo', 0, '/', $file->get_filename(), false);
                    $reportheaderimagepath = $url->out();
                }
            }
        }
    }
    return $reportheaderimagepath;
}

/**
 * Serve the Plot generate form.
 *
 * @param object $args List of named arguments for the fragment loader.
 * @return array $output contains error, html and javascript.
 */
function block_learnerscript_plotforms_ajaxform($args) {
    global $CFG, $DB, $OUTPUT, $PAGE;

    $args = (object) $args;
    $o = '';

    if (!$report = $DB->get_record('block_learnerscript', array('id' => $args->reportid))) {
        print_error(get_string('noreportexists', 'block_learnerscript'));
    }
    require_once($CFG->dirroot . '/blocks/learnerscript/reports/' . $report->type . '/report.class.php');
    $reportclassname = 'report_' . $report->type;
    $properties = new stdClass;
    $reportclass = new $reportclassname($report, $properties);
    
    if(array_search($args->pname, ['bar', 'column', 'line'])){
        $pname = 'bar';
    }else{
        $pname = $args->pname;
    }
    require_once($CFG->dirroot . '/blocks/learnerscript/components/' . $args->component . '/' . $pname . '/plugin.class.php');
    $pluginclassname = 'plugin_' . $pname;
    $pluginclass = new $pluginclassname($report);

    require_once($CFG->dirroot . '/blocks/learnerscript/components/' . $args->component . '/component.class.php');
    $componentclassname = 'component_' . $args->component;
    $compclass = new $componentclassname($report->id);

    require_once($CFG->dirroot . '/blocks/learnerscript/components/' . $args->component . '/' . $pname . '/form.php');
    $classname = $pname . '_form';
    $comp = $args->component;
    $formurlparams = array('id' => $args->reportid, 'comp' => $args->component, 'pname' => $args->pname);
    if ($args->cid) {
        $formurlparams['cid'] = $args->cid;
    }
    $cid = $args->cid;
    $formurl = new moodle_url('/blocks/learnerscript/editplugin.php', $formurlparams);

    if (!empty($args->jsonformdata)) {
        if (!empty($args->jsonformdata)) {
            parse_str($args->jsonformdata, $ajaxformdata);
        }

        if (!empty($ajaxformdata)) {
            if ($args->pname == 'combination') {
                if (isset($ajaxformdata['lsitofcharts']) && (!is_array($ajaxformdata['lsitofcharts']) ||
                    $ajaxformdata['lsitofcharts'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['lsitofcharts']);
                }
                if (isset($ajaxformdata['yaxis_line']) && (!is_array($ajaxformdata['yaxis_line']) ||
                    $ajaxformdata['yaxis_line'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['yaxis_line']);
                }
                if (isset($ajaxformdata['yaxis_bar']) && (!is_array($ajaxformdata['yaxis_bar']) ||
                    $ajaxformdata['yaxis_bar'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['yaxis_bar']);
                }
            } else if ($args->pname == 'bar') {
                if (isset($ajaxformdata['yaxis']) && (!is_array($ajaxformdata['yaxis']) ||
                    $ajaxformdata['yaxis'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['yaxis']);
                }
            } else if ($args->pname == 'column') {
                if (isset($ajaxformdata['yaxis']) && (!is_array($ajaxformdata['yaxis']) ||
                    $ajaxformdata['yaxis'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['yaxis']);
                }
            } else if ($args->pname == 'line') {
                if (isset($ajaxformdata['yaxis']) && (!is_array($ajaxformdata['yaxis']) ||
                    $ajaxformdata['yaxis'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['yaxis']);
                }
            }
        } else {
            $ajaxformdata = array();
        }
    } else {
        $ajaxformdata = array();
    }

    $mform = new $classname($formurl, compact('comp', 'cid', 'id', 'pluginclass', 'compclass', 'report', 'reportclass'), 'post', '', null, true, $ajaxformdata);

    if ($args->cid) {
        $components = (new block_learnerscript\local\ls)->cr_unserialize($report->components);
        $elements = isset($components[$args->component]['elements']) ?
                            $components[$args->component]['elements'] : array();
        $cdata = array();
        if ($elements) {
            foreach ($elements as $e) {
                if ($e['id'] == $args->cid) {
                    $cdata = $e;
                    $plugin = $e['pluginname'];
                    break;
                }
            }
        }
        $mform->set_data($cdata['formdata']);
    }

    if (!empty($ajaxformdata) && $mform->is_validated()) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $component = $args->component;
        $validated = $mform->is_validated();

        $validateddata = $mform->get_data();
        if($pname == 'combination'){
            foreach ($validateddata->yaxis_bar as $value) {
                if(isset($ajaxformdata[$value.'_value']) && $ajaxformdata[$value.'_value'] != ''){
                    $validateddata->{$value} = $ajaxformdata[$value];
                    $validateddata->{$value.'_value'} = $ajaxformdata[$value.'_value'];
                }
            }
            foreach ($validateddata->yaxis_line as $value) {
                if(isset($ajaxformdata[$value.'_value']) && $ajaxformdata[$value.'_value'] != ''){
                    $validateddata->{$value} = $ajaxformdata[$value];
                    $validateddata->{$value.'_value'} = $ajaxformdata[$value.'_value'];
                }
            }
        }else{
            if (!empty($validateddata->yaxis)) {
                foreach ($validateddata->yaxis as $value) {
                    if(isset($ajaxformdata[$value.'_value']) && $ajaxformdata[$value.'_value'] != ''){
                        $validateddata->{$value} = $ajaxformdata[$value];
                        $validateddata->{$value.'_value'} = $ajaxformdata[$value.'_value'];
                    }
                }
            }
        }
        if ($validated && $validateddata) {
            $elements = (new block_learnerscript\local\ls)->cr_unserialize($report->components);
            $elements = isset($elements[$args->component]['elements']) ? $elements[$args->component]['elements'] : array();
            if ($args->cid) {
                if ($elements) {
                    foreach ($elements as $key => $e) {
                        if ($e['id'] == $args->cid) {
                            $elements[$key]['formdata'] = $validateddata;
                            break;
                        }
                    }
                }

                $allelements = (new block_learnerscript\local\ls)->cr_unserialize($report->components);
                $allelements[$args->component]['elements'] = $elements;

                $report->components = (new block_learnerscript\local\ls)->cr_serialize($allelements);
            } else {
                $uniqueid = random_string(15);
                while (strpos($report->components, $uniqueid) !== false) {
                    $uniqueid = random_string(15);
                }
                $validateddata->id = $uniqueid;

                $existingcomponentsdata = $DB->get_field('block_learnerscript', 'components', array('id' => $args->reportid));
                $componentsdata = (new block_learnerscript\local\ls)->cr_unserialize($existingcomponentsdata);
                if(!empty( $componentsdata))   {          
                    $componentsdata[$component] = isset($componentsdata[$component]) ? $componentsdata[$component] : array();
                    $componentelements = isset($componentsdata[$component]['elements']) ? $componentsdata[$component]['elements'] : array();
                    $componentsdata[$component]['elements'] = isset($componentelements) ?
                                                                $componentelements : array();

                    $cdata = array('id' => $uniqueid, 'formdata' => $validateddata,
                                    'pluginname' => $args->pname,
                                    'pluginfullname' => $pluginclass->fullname,
                                    'summary' => $pluginclass->summary($validateddata));
                    $componentsdata[$component]['elements'][] = $cdata;
                }else{
                    $componentsdata = array();
                }
                $report->components = (new block_learnerscript\local\ls)->cr_serialize($componentsdata);
            }

            try {
                if ($args->component == 'plot') {
                    $return = $DB->update_record('block_learnerscript', $report);
                }
                return array('error' => false, 'data' => $validateddata);
            } catch (dml_exception $ex) {
                print_error($ex);
            }
        }
    } else {
        $output = array();

        if (!empty($ajaxformdata)) {
            $mform->is_validated();
            $output['formerror'] = true;
        }
        $OUTPUT->header();
        $PAGE->start_collecting_javascript_requirements();
        ob_start();
        $mform->display();
        $o .= ob_get_contents();
        ob_end_clean();

        $data = $o;

        $jsfooter = $PAGE->requires->get_end_code();
        $output['error'] = false;
        $output['html'] = $data;
        $output['javascript'] = $jsfooter;

        return $output;
    }
}

function block_learnerscript_schreportform_ajaxform($args) {
    global $CFG, $DB, $OUTPUT, $PAGE, $USER;

    $args = (object) $args;
    $o = '';
    $context = context_system::instance();
    $reportid = $args->reportid;
    $instance = $args->instance;
    $scheduleid = 0;
    $ajaxformdata = array();
    if (!empty($args->jsonformdata)) {
        parse_str($args->jsonformdata, $ajaxformdata);
        if (!empty($ajaxformdata)) {
            if (isset($ajaxformdata['users_data']) && (!is_array($ajaxformdata['users_data']) || $ajaxformdata['users_data'] == '_qf__force_multiselect_submission')) {
                    unset($ajaxformdata['users_data']);
            }
        }
    }

    if ((has_capability('block/learnerscript:managereports', $context) ||
        has_capability('block/learnerscript:manageownreports', $context) ||
        is_siteadmin()) && !empty($reportid)) {
        require_once($CFG->dirroot . '/blocks/learnerscript/components/scheduler/schedule_form.php');
        $roleslist = (new schedule)->reportroles('', $reportid);
        $schuserslist = !empty($ajaxformdata['schuserslist']) ? $ajaxformdata['schuserslist'] : array();
        list($schusers, $schusersids) = (new schedule)->userslist($reportid, $scheduleid, $schuserslist);
        $exportoptions = (new ls)->cr_get_export_plugins();
        $frequencyselect = (new schedule)->get_options();
        if (!empty($ajaxformdata['frequency']) && $ajaxformdata['frequency']) {
            $schedulelist = (new schedule)->getschedule($ajaxformdata['frequency']);
        } else {
            $schedulelist = array(null => '--SELECT--');
        }
        $scheduleurl = $CFG->wwwroot . '/blocks/learnerscript/components/scheduler/schedule.php';
        $scheduleform = new scheduled_reports_form($scheduleurl, array('id' => $reportid,
                                'scheduleid' => $scheduleid, 'roles_list' => $roleslist,
                                'schusers' => $schusers, 'schusersids' => $schusersids,
                                'exportoptions' => $exportoptions,
                                'schedule_list' => $schedulelist,
                                'frequencyselect' => $frequencyselect,
                            'instance' => $instance), 'post', '', null, true, $ajaxformdata);
        $setdata = new stdClass();
        $setdata->schuserslist = $schusersids;
        $setdata->users_data = explode(',', $schusersids);

        $scheduleform->set_data($setdata);
        if (!empty($ajaxformdata) && $scheduleform->is_validated()) {
            // If we were passed non-empty form data we want the mform to call validation functions and show errors.
            $validated = $scheduleform->is_validated();

            $validateddata = $scheduleform->get_data();
            if ($validateddata) {
                try {
                    $fromform = new stdClass();
                    $formrole = explode('_', $ajaxformdata['role']);
                    $fromform->reportid = $ajaxformdata['reportid'];
                    $fromform->roleid = $formrole[0];
                    $fromform->sendinguserid = $ajaxformdata['schuserslist'];

                    $fromform->exportformat = $ajaxformdata['exportformat'];

                    $fromform->frequency = $ajaxformdata['frequency'];
                    $fromform->schedule = $ajaxformdata['schedule'];
                    $fromform->exporttofilesystem = $ajaxformdata['exporttofilesystem'];
                    $fromform->userid = $USER->id;
                    $fromform->nextschedule = (new schedule)->next($fromform);
                    $fromform->timemodified = time();
                    $fromform->timecreated = time();
                    if (array_key_exists(1, $formrole)) {
                        $fromform->contextlevel = $formrole[1];
                    } else {
                        $fromform->contextlevel = 10;
                    }

                    $schedule = $DB->insert_record('block_ls_schedule', $fromform);
                    $event = \block_learnerscript\event\schedule_report::create(array(
                                    'objectid' => $fromform->reportid,
                                    'context' => $context
                                ));
                    $event->trigger();
                    return array('error' => false, 'data' => $validateddata);
                } catch (dml_exception $ex) {
                    print_error($ex);
                }
            }
        } else {
            $output = array();

            if (!empty($ajaxformdata)) {
                $scheduleform->is_validated();
                $output['formerror'] = true;
            }

            $OUTPUT->header();
            $PAGE->start_collecting_javascript_requirements();
            ob_start();
            $scheduleform->display();
            $o .= ob_get_contents();
            ob_end_clean();

            $data = $o;

            $jsfooter = $PAGE->requires->get_end_code();
            $output['error'] = false;
            $output['html'] = $data;
            $output['javascript'] = $jsfooter;

            return $output;
        }
    }
}

function block_learnerscript_sendreportemail_ajaxform($args) {
    global $CFG, $DB, $OUTPUT, $PAGE, $USER;

    $args = (object) $args;
    $o = '';
    $context = context_system::instance();
    $reportid = $args->reportid;
    $instance = $args->instance;
    $scheduleid = 0;
    $ajaxformdata = array();
    if (!empty($args->jsonformdata)) {
        parse_str($args->jsonformdata, $ajaxformdata);
        if (!empty($ajaxformdata)) {
            if (isset($ajaxformdata['email']) && (!is_array($ajaxformdata['email']) ||
                $ajaxformdata['email'] == '_qf__force_multiselect_submission')) {
                unset($ajaxformdata['email']);
            }
        }
    }

    if ((has_capability('block/learnerscript:managereports', $context) ||
        has_capability('block/learnerscript:manageownreports', $context) ||
        is_siteadmin()) && !empty($reportid)) {
        require_once($CFG->dirroot . '/blocks/reportdashboard/email_form.php');
        $emailform = new analytics_emailform($CFG->wwwroot . '/blocks/reportdashboard/dashboard.php', array('reportid' => $reportid, 'AjaxForm' => true, 'instance' => $instance, 'ajaxformdata' => $ajaxformdata), 'post', '', null, true, $ajaxformdata);

        if (!empty($ajaxformdata) && $emailform->is_validated()) {
            // If we were passed non-empty form data we want the mform to call validation functions and show errors.
            $validated = $emailform->is_validated();

            $validateddata = $emailform->get_data();
            if ($validateddata) {
                try {
                    $roleid = 0;
                    $rolecontext = 0;
                    if (!empty($_SESSION['role'])) {
                        $roleid = $DB->get_field('role', 'id', array('shortname' => $_SESSION['role']));
                        $rolecontext = $DB->get_field('role_context_levels',  'contextlevel', array('roleid' => $roleid));
                    }
                    $data = new stdClass();
                    $userlist = implode(',', $ajaxformdata['email']);
                    $data->sendinguserid = $userlist;
                    $data->exportformat = $ajaxformdata['format'];
                    $data->frequency = -1;
                    $data->schedule = 0;
                    $data->exporttofilesystem = 1;
                    $data->reportid = $ajaxformdata['reportid'];
                    $data->timecreated = time();
                    $data->timemodified = 0;
                    $data->userid = $USER->id;
                    $data->roleid = $roleid;
                    $data->nextschedule = 0;
                    $data->contextlevel = $rolecontext;
                    $insert = $DB->insert_record('block_ls_schedule', $data);
                    return array('error' => false, 'data' => $validateddata);
                } catch (dml_exception $ex) {
                    print_error($ex);
                }
            }
        } else {
            $output = array();

            if (!empty($ajaxformdata)) {
                $emailform->is_validated();
                $output['formerror'] = true;
            }

            $OUTPUT->header();
            $PAGE->start_collecting_javascript_requirements();
            ob_start();
            $emailform->display();
            $o .= ob_get_contents();
            ob_end_clean();

            $data = $o;

            $jsfooter = $PAGE->requires->get_end_code();
            $output['error'] = false;
            $output['html'] = $data;
            $output['javascript'] = $jsfooter;

            return $output;
        }
    }
}

function get_roles_in_context($contextlevel, $excludedroles = null){
    global $DB;
    $rolesincontext = implode(',', array_values(get_roles_for_contextlevels($contextlevel)));
    if (!empty($excludedroles)) {
        $rolesexcluded = implode(',', array_values($excludedroles));
    }    
    if (!empty($rolesexcluded)) {
        $roles = $DB->get_records_sql('select id, shortname, name from {role} where id in ('.$rolesincontext.') and shortname not in ('.$rolesexcluded.')');
    } else {
        $roles = $DB->get_records_sql('select id, shortname, name from {role} where id in ('.$rolesincontext.')');
    }
    $userroles = array();
    foreach ($roles as $r) {
        if ($contextlevel == CONTEXT_SYSTEM && $r->shortname == 'manager') {
            continue;
        }
        $userroles[$r->id] = role_get_name($r);
    }
    return $userroles;
}

/*
* Displays a node in left side menu (reports)
* @return  [type] string  link for the leftmenu
*/
function block_learnerscript_leftmenunode(){
    global $USER, $DB; 
    // $systemcontext = context_system::instance();
    $systemcontext = (new \local_costcenter\lib\accesslib())::get_module_context();
    $reportsnode = '';
    if(has_capability('block/learnerscript:viewreports', $systemcontext) || is_siteadmin()) {
        $reportsnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_learnerscript', 'class'=>'pull-left user_nav_div learnerscript'));
        // $params = get_reportdashboard();
        $reports_url = new moodle_url('/blocks/reportdashboard/dashboard.php',array());
        $reportstext =  get_string('analytics','block_learnerscript');
        $label = '<i class="fa fa-bar-chart"></i><span class="user_navigation_link_text">'.$reportstext.'</span>';
        $reportslink = html_writer::link($reports_url, $label,array('class'=>'user_navigation_link'));
        $reportsnode .= $reportslink;
        $reportsnode .= html_writer::end_tag('li');
    }
    return array('24' => $reportsnode);
}


function get_reportdashboard(){
    global $USER, $DB;

    if (!is_siteadmin()) {
        if(!empty($USER->access['rsw']['/1'])){
            $userrole = $DB->get_field('role','shortname',array('id'=>$USER->access['rsw']['/1']));
        }else{
            $userrole = 'user';
        }
    } else {
        $userrole = '';
    }

    $params = array();
    $params['role'] = $userrole;
    $sql = "SELECT DISTINCT(subpagepattern) 
            FROM {block_instances} "; 
    if (!empty($userrole)) {
        $sql .= " WHERE pagetypepattern = 'blocks-reportdashboard-dashboard-$userrole' ";
    } else {
        $sql .= " WHERE pagetypepattern = 'blocks-reportdashboard-dashboard'";
    } 
    $sql .= "  ORDER BY CASE subpagepattern
      WHEN 'Maindashboard' THEN 1
      WHEN 'Learnerdashboard' THEN 2
      WHEN 'Examdashboard' THEN 3
      WHEN 'Certification' THEN 4 
      WHEN 'Compliances' THEN 5
      WHEN 'Dashboard' THEN 6 
      WHEN 'Course' THEN 7
      ELSE 8 
   END ";
    $roledashboard = $DB->get_field_sql($sql);
    if($roledashboard){
        $params['dashboardurl'] = $roledashboard;
    }else{
        $params['dashboardurl'] = 'dashboard';
    }

    return $params;
}

function block_learnerscript_masterinfo(){
    global $CFG, $PAGE, $OUTPUT, $DB, $USER;
    $costcenterid = explode('/',$USER->open_path)[1];
    $systemcontext = (new \local_costcenter\lib\accesslib())::get_module_context();
    $content = '';
    if(has_capability('block/learnerscript:viewreports', $systemcontext) || is_siteadmin()) {

        // reports
        $reports = "SELECT count(id) FROM {block_learnerscript}";
        // if(!is_siteadmin()){
        //     $reports .=" WHERE open_path = '/$costcenterid'";
        // }
        $totalreports = $DB->count_records_sql($reports);

        if($totalreports > 0) {
            $report = '('.$totalreports.')';
        }
        $templatedata = array();
        $templatedata['show'] = true;
        $templatedata['count'] = $report;
        $templatedata['link'] = $CFG->wwwroot.'/blocks/learnerscript/reportsview.php';
        $templatedata['stringname'] = get_string('report','block_masterinfo');
        $templatedata['icon'] = '<i class="fa fa-bar-chart"></i>';

        $content = $OUTPUT->render_from_template('block_masterinfo/masterinfo', $templatedata);
    }
    return array('5' => $content);
}

function get_costcenterpathconcatsql($reportid,$scheduleflag,$userid,$columnname ,$userpath = false){
    global $USER;
    $usercostcenterpathconcatsql = '';
    if ($scheduleflag && $reportid!=0 && $userid != 0 && $USER->id != $userid) {             
        $usercostcenterpathconcatsql = scheduled_report( $reportid,$scheduleflag,$userid,$columnname);                 
    }else if($userpath){
        $usercostcenterpathconcatsql = get_user_costcenterpath($USER->open_path);          
    }
    return $usercostcenterpathconcatsql;
}

function scheduled_report( $reportid,$scheduleflag,$userid,$modulecol='',$usercol='' ){
    global $DB;
    $sql = "";

    if ($scheduleflag && $reportid!=0 && $userid != 0) {         
        $scheduledreport = $DB->get_record_sql('SELECT id,roleid FROM {block_ls_schedule} where reportid =:reportid AND sendinguserid = :sendinguserid ', ['reportid' => (int)$reportid,'sendinguserid' => (int)$userid ], IGNORE_MULTIPLE);
    }
    if (!empty($scheduledreport)) { 
		
        $usercostcenterpath =  $DB->get_records_sql("SELECT lc.path as open_path FROM {role_assignments} AS ra
                                    JOIN {context} AS c ON c.id = ra.contextid AND c.contextlevel = 40
                                    JOIN {course_categories} AS cc ON cc.id = c.instanceid
                                    JOIN {local_costcenter} as lc ON lc.category = cc.id
                                    JOIN {role} AS r ON r.id = ra.roleid
                                    WHERE ra.userid =:userid ",array('userid' =>  $userid));  
        if($usercostcenterpath){                            
            $paths = [];

            foreach($usercostcenterpath AS $userpath){ 
                if(!empty($modulecol)){
                    $userpathinfo = $userpath->open_path;
                    $paths[] = $userpathinfo.'/%';				
                    $paths[] = $userpathinfo;	
                    $orgpath = explode("/", $userpathinfo, 3);
                    if(!empty($orgpath[1])){
                        $paths[] = '/'.$orgpath[1];
                    }
                }
                if(!empty($usercol)){
                    $userpathinfo = $userpath->open_path;
                    $userpaths[] = $userpathinfo.'/%';
                    $userpaths[] = $userpathinfo;	
                }
            
            }
        
            if(!empty($paths)){
                if(!empty($modulecol)){
                    foreach($paths AS $path){
                        $mpathsql[] = " {$modulecol} LIKE '{$path}' ";					
                    }
                    $sql .= " AND ( ".implode(' OR ', $mpathsql).' ) ';
                }
            }
            if(!empty($userpaths)){
                if(!empty($usercol)){
                    foreach($userpaths AS $upath){
                        $upathsql[] = " {$usercol} LIKE '{$upath}' ";
                    }
                    $sql .= " AND ( ".implode(' OR ', $upathsql).' ) ';
                }
            } 
        }
              
    }
    return $sql;

}


function get_user_costcenterpath($open_path){
    list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/",$open_path);
    $usercostcenterpathconcatsql = (new \local_costcenter\lib\accesslib())::get_costcenter_path_field_concatsql($columnname='u.open_path',$open_path,'lowerandsamepath');
    return $usercostcenterpathconcatsql;
}

function get_orgpath($params){
    $orgpath = '';
    if ($params['filter_organization'] > 0) {
        $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($params['filter_organization'], 'path');
        $orgpath = $orgpath.'/%';    
    }

    if ($params['filter_departments'] > 0) {
        $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($params['filter_departments'], 'path');
        $orgpath = $orgpath.'/%';
    }

    if ($params['filter_subdepartments'] > 0) {
        $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($params['filter_subdepartments'], 'path');
        $orgpath = $orgpath.'/%';
    }

    if ($params['filter_l4departments'] > 0) {
        $orgpath = \local_costcenter\lib\accesslib::get_costcenter_info($params['filter_l4departments'], 'path');
        $orgpath = $orgpath.'/%';
    }

    return $orgpath;

}
