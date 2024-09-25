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
 * @package Bizlms 
 * @subpackage local_certification
 */
namespace local_certification\output;
require_once($CFG->dirroot . '/local/certification/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
defined('MOODLE_INTERNAL') || die;

use context_system;
use html_table;
use html_writer;
use local_certification\certification;
use plugin_renderer_base;
use user_course_details;
use moodle_url;
use stdClass;
use single_button;
use core_completion\progress;

class renderer extends plugin_renderer_base {
    /**
     * [render_certification description]
     * @method render_certification
     * @param  \local_certification\output\certification $page [description]
     * @return [type]                                  [description]
     */
    public function render_certification(\local_certification\output\certification $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_certification/certification', $data);
    }
    /**
     * [render_form_status description]
     * @method render_form_status
     * @param  \local_certification\output\form_status $page [description]
     * @return [type]                                    [description]
     */
    public function render_form_status(\local_certification\output\form_status $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_certification/form_status', $data);
    }
    /**
     * [render_session_attendance description]
     * @method render_session_attendance
     * @param  \local_certification\output\session_attendance $page [description]
     * @return [type]                                           [description]
     */
    public function render_session_attendance(\local_certification\output\session_attendance $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('local_certification/session_attendance', $data);
    }
    /**
     * Display the certification tabs
     * @return string The text to render
     */
    public function get_certification_tabs() {
        global $CFG,$DB;
        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        $tabscontent = $this->viewcertifications($stable);
        $context = context_system::instance();
        
        $all_tab=$new_tab=$active_tab=$hold_tab=$cancelled_tab=$completed_tab=false;
        //if(has_capability('local/certification:view_allcertificationtab', context_system::instance())){
            $all_tab=true;
        //}
        if(has_capability('local/certification:view_newcertificationtab', context_system::instance())){
            $new_tab=true;
        }
        //if(has_capability('local/certification:view_activecertificationtab', context_system::instance())){
            $active_tab=true;
        //}
        if(has_capability('local/certification:view_holdcertificationtab', context_system::instance())){
            $hold_tab=true;
        }
        //if(has_capability('local/certification:view_cancelledcertificationtab', context_system::instance())){
            $cancelled_tab=true;
        //}
        //if(has_capability('local/certification:view_completedcertificationtab', context_system::instance())){
            $completed_tab=true;
        //}
    
        $certificationtabslist = [
            'certificationtabslist' => $tabscontent,
            'contextid' => $context->id,
            'plugintype' => 'local',
            'plugin_name' =>'certification',
            'all_tab'=>$all_tab,
            'new_tab'=>$new_tab,
            'active_tab'=>$active_tab,
            'hold_tab'=>$hold_tab,
            'cancelled_tab'=>$cancelled_tab,
            'completed_tab'=>$completed_tab,
            'creatacertification' => ((has_capability('local/certification:managecertification',
            context_system::instance()) && has_capability('local/certification:createcertification',
            context_system::instance())) || is_siteadmin()) ? true : false,
        ];
        if ((has_capability('local/location:manageinstitute', context_system::instance()) || has_capability('local/location:viewinstitute', context_system::instance()))&&(has_capability('local/certification:managecertification', context_system::instance()))) {
             $certificationtabslist['location_url']=$CFG->wwwroot.'/local/location/index.php?componentss=certification';

        }
        if ((has_capability('local/request:approverecord', context_system::instance()) || is_siteadmin())) {
             $certificationtabslist['request_url']=$CFG->wwwroot.'/local/request/index.php?component=certification';

        }
         if(is_siteadmin() ||(
            has_capability('local/certification:createprogram', $context)|| has_capability('local/certification:updatecertification', $context)||has_capability('local/certification:managecertification', $context))){
            $sql = "SELECT id,name FROM {block_learnerscript} WHERE category = 'local_certification'";
            $certificationreports = $DB->get_records_sql($sql);
           foreach ($certificationreports as $certification) { 
            $certificationtabslist['reports'][] = ['certificationid' => $certification->id, 'name' => $certification->name];
        }
      }
        return $this->render_from_template('local_certification/certificationtabs', $certificationtabslist);
    }
    /**
     * [viewcertifications description]
     * @method viewcertifications
     * @param  [type]         $stable [description]
     * @return [type]                 [description]
     */
    public function viewcertifications($stable) {
        global $OUTPUT, $CFG, $DB;
        $systemcontext = context_system::instance();
        $includesfile = false;
        if(file_exists($CFG->dirroot.'/local/includes.php')){
            require_once($CFG->dirroot.'/local/includes.php');
            $includesfile = true;
            $includes = new user_course_details();
    	}
        
        if ($stable->thead) {
            $certifications = (new certification)->certifications($stable);
            if ($certifications['certificationscount'] > 0) {
                $table = new html_table();
                $table->head = array('','');
                $table->id = 'viewcertifications';
                $return = html_writer::table($table);
            } else {
                $return = "<div class='alert alert-info text-center'>" . get_string('nocertifications', 'local_certification') . "</div>";
            }
        } else {
            $certifications = (new certification)->certifications($stable);
            $data = array();
            $certificationchunks = array_chunk($certifications['certifications'], 2);
            $startTime = microtime(true);
            foreach($certificationchunks as $cr_data) {
                $row = [];
                foreach ($cr_data as $sdata) {
                    $line = array();
                    //-----class room summary image
                    if ($sdata->certificationlogo > 0) {
                        $certificationesimg = (new certification)->certification_logo($sdata->certificationlogo);
                        if($certificationesimg == false){
                             if($includesfile){
                                $certificationesimg = $includes->get_classes_summary_files($sdata); 
                            }
                        }
                    } else {
                         if($includesfile){
                            $certificationesimg = $includes->get_classes_summary_files($sdata);
                         }   
                    }

                    //-------data variables
                    $certificationname = $sdata->name;
                    $certificationname_string = strlen($certificationname) > 40 ? substr($certificationname, 0, 40)."..." : $certificationname;
                    $usercreated = $sdata->usercreated;
                    //$user = $DB->get_record('user', array('id' => $usercreated));
                    //$createdBy = $user->firstname.'&nbsp;'.$user->lastname;
                    
                    $startdate=$enddate=0;
                    
                    if($sdata->startdate>0&&$sdata->startdate!=null){
                         $startdate = \local_costcenter\lib::get_userdate("d/m/Y ", $sdata->startdate);
                    }
                    if($sdata->enddate>0&&$sdata->enddate!=null){
                   
                        $enddate = \local_costcenter\lib::get_userdate("d/m/Y ", $sdata->enddate);
                    }

                    $description = \local_costcenter\lib::strip_tags_custom(html_entity_decode($sdata->description));
                    $isdescription = '';
                    if (empty($description)) {
                       $isdescription = false;
                    } else {
                        $isdescription = true;
                        if (strlen($description) > 120) {
                            $decsriptionCut = substr($description, 0,120);
                            $decsriptionstring = \local_costcenter\lib::strip_tags_custom(html_entity_decode($decsriptionCut),array('overflowdiv' => false, 'noclean' => false, 'para' => false));
                        }else{
                            $decsriptionstring="";
                        }
                    }
                    
                    $enrolled_users = $sdata->enrolled_users;
                    if ($sdata->department == -1) {
                        $departmentname = 'All';
                    } else {
                        if($sdata->department)
                            $certificationdepartment = $DB->get_fieldset_select("local_costcenter", "fullname", ' concat(\',\','.$sdata->department.',\',\') LIKE concat(\'%,\',id,\',%\') ', array());//"FIND_IN_SET(id, '$sdata->department')"
                        $departmentname = implode(', ', $certificationdepartment);
                    }
                    switch($sdata->status) {
                        case CERTIFICATION_NEW:
                           if(has_capability('local/certification:view_newcertificationtab', context_system::instance())){
                                $line ['certificationstatusclass'] = 'certificationnew';
                                $line ['crstatustitle'] = get_string('newclasses', 'local_certification');
                            }
                        break;
                        case CERTIFICATION_ACTIVE:
                           //if(has_capability('local/certification:view_activecertificationtab', context_system::instance())){ 
                                $line ['certificationstatusclass'] = 'certificationactive';
                                $line ['crstatustitle'] = get_string('activeclasses', 'local_certification');
                           //}
                        break;
                        case CERTIFICATION_HOLD:
                           if(has_capability('local/certification:view_holdcertificationtab', context_system::instance())){ 
                                $line ['certificationstatusclass'] = 'certificationhold';
                                $line ['crstatustitle'] = get_string('holdclasses', 'local_certification');
                           }
                        break;
                        case CERTIFICATION_CANCEL:
                            //if(has_capability('local/certification:view_cancelledcertificationtab', context_system::instance())){ 
                                $line ['certificationstatusclass'] = 'certificationcancelled';
                                $line ['crstatustitle'] = get_string('cancelledclasses', 'local_certification');
                            //}
                        break;
                        case CERTIFICATION_COMPLETED:
                          //if(has_capability('local/certification:view_completedcertificationtab', context_system::instance())){  
                            $line ['certificationstatusclass'] = 'certificationcompleted';
                            $line ['crstatustitle'] = get_string('completedclasses', 'local_certification');
                          //}
                        break;
                    }
                    $certification_actionstatus=$this->certification_actionstatus_markup($sdata);
                    $line ['seatallocation'] = empty($sdata->capacity)?'N/A':$sdata->capacity;
                    $line ['classesimg'] = $certificationesimg;
                    $line ['certificationname'] = $certificationname;
                    $line ['certificationname_string'] = $certificationname_string;
                    $line ['usercreated'] = fullname($user);
                    $line ['startdate'] = $startdate;
                    $line ['enddate'] = $enddate;
                    $line ['description'] =  \local_costcenter\lib::strip_tags_custom(html_entity_decode($sdata->description));
                    $line ['descriptionstring'] = $decsriptionstring;
                    $line ['isdescription'] = $isdescription;
                    $line ['certification_actionstatus'] = array_values(($certification_actionstatus));
                    $certificationcoursessql = "SELECT count(cc.id) as total
                                              FROM {local_certification_courses} AS cc 
                                              JOIN {course} as c on c.id=cc.courseid
                                             WHERE cc.certificationid = $sdata->id ";

                    $certificationcourses = $DB->count_records_sql($certificationcoursessql);
                    //$certificationopen_pointssql = "SELECT sum(c.open_points)
                    //                          FROM {local_certification_courses} AS cc
                    //                          JOIN {course} AS c on c.id=cc.courseid
                    //                         WHERE cc.certificationid = $sdata->id ";
                    //
                    //$certificationopen_points = $DB->get_field_sql($certificationopen_pointssql);
                    $line ['courses_count'] = $certificationcourses;
                    //$line ['courses_points_count'] = empty($certificationcourses)?0:$certificationopen_points;
                    $mnsql="SELECT count(distinct(u.id)) FROM {user} AS u
                                                JOIN {local_certification_users} AS cu ON cu.userid = u.id
                                                WHERE cu.certificationid = $sdata->id AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                    $allocatedseats=$DB->count_records_sql($mnsql) ;
                    
                    //$sql=" AND cu.completion_status=0 ";
                    //
                    //$inprogress=$DB->count_records_sql($mnsql.$sql) ;
                    
                    $sql=" AND cu.completion_status=1 ";
                    
                    $completed=$DB->count_records_sql($mnsql.$sql) ;
                    $line ['users_count'] = $completed."/".$allocatedseats;
                    $line ['courses'] = array();

                    $certificationsessionssql = "SELECT count(cs.id) as total
                                               FROM {local_certification_sessions} AS cs
                                                WHERE cs.certificationid = {$sdata->id} ";
                    $certificationsessions = $DB->count_records_sql($certificationsessionssql);

                    $line ['sessions_count'] = $certificationsessions;

                    if (!empty($certificationcourses)) {
                        foreach($certificationcourses as $certificationcourse) {
                            $courseslimit = true;
                            $coursename = strlen($certificationcourse->fullname) > 15 ? substr($certificationcourse->fullname, 0, 15)."..." : $certificationcourse->fullname;
                            $line ['courses'][] = array('coursesdata'=>'<a href="' . $CFG->wwwroot .'/course/view.php?id=' . $certificationcourse->id .'" title="' . $certificationcourse->fullname . '">' . $coursename . '</a>');

                        }
                    }
                    $line ['certification_actionstatus'] = array_values(($certification_actionstatus));
                    $line ['enrolled_users'] = $enrolled_users;
                    $line ['departmentname'] = $departmentname;
                    $line ['certificationid'] = $sdata->id;
                    $line ['certificationurl'] = new moodle_url('/local/certification/view.php', array('ctid' => $sdata->id));
                    
                   $certificationuserssql = "SELECT count(cu.id) as total
                                               FROM {local_certification_users} AS cu
                                                WHERE cu.certificationid = {$sdata->id}";
                    $certificationusers= $DB->count_records_sql($certificationuserssql);
                    $certificationuserssql.= " AND cu.completion_status=1";
                    $certificationusers_completed= $DB->count_records_sql($certificationuserssql);
                                                
               
                    if (empty($certificationusers)||$certificationusers==0) {
                        $certificationprogress = 0;
                    } else {
                        $certificationprogress = round(($certificationusers_completed/$certificationusers)*100);
                    }
                    $line ['certificationprogress'] = $certificationprogress;

                    $certificationtrainerssql = "SELECT u.id, u.picture, u.firstname, u.lastname,
                                        u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.imagealt, u.email
                                              FROM {user} AS u
                                              JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                                              WHERE u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND ct.certificationid = {$sdata->id}
                                              ";//LIMIT 0, 2

                    $certificationtrainers = $DB->get_records_sql($certificationtrainerssql, array(),  0, 2);
                    $line['trainers']  = array();
                    if(!empty($certificationtrainers)) {
                        $trainerslimit = false;
                        foreach($certificationtrainers as $certificationtrainer) {
                            $trainerslimit = true;
                            $trainername = strlen(fullname($certificationtrainer)) > 8 ? substr(fullname($certificationtrainer), 0, 8)."..." : fullname($certificationtrainer);
                            $certificationtrainerpic = $OUTPUT->user_picture($certificationtrainer, array('size' => 35, 'class'=>'trainer_img'));
                            $line['trainers'][] = array('certificationtrainerpic' => $certificationtrainerpic, 'trainername' => $trainername, 'trainerdesignation' => '');
                        }
                    }
                    if(count($certificationtrainers) > 2){
                        $trainerslimit = false;
                        $line['moretrainers'] = array_slice($line['trainers'], 0, 2);
                    }

                    $line ['trainerslimit'] = $trainerslimit;
                    $line ['editicon'] = $OUTPUT->image_url('t/edit');
                    $line ['deleteicon'] = $OUTPUT->image_url('t/delete');
                    $line ['assignusersicon'] = $OUTPUT->image_url('t/assignroles');
					 $certificationcompletion_id=$DB->get_field('local_certificatn_completion','id',array('certificationid'=>$sdata->id));
                        if(!$certificationcompletion_id){
                            $certificationcompletion_id=0;
                        }

                    $line['certificationcompletion'] = false;
                    $mouse_overicon=false;
                    
                    if ((has_capability('local/certification:managecertification', context_system::instance()) || is_siteadmin())) {
                        $line['action'] = true;
                    }

                    if ((has_capability('local/certification:editcertification', context_system::instance()) || is_siteadmin())) {
                            $line ['edit'] =  true;
                            $mouse_overicon=true;
                    }

                    if ((has_capability('local/certification:deletecertification', context_system::instance()) || is_siteadmin())) {
                            $line ['delete'] =  true;
                            $mouse_overicon=true;
                    }
                    if ((has_capability('local/certification:manageusers', context_system::instance()) || is_siteadmin())) {
                            $line ['assignusers'] =  true;
                            $line ['assignusersurl'] = new moodle_url("/local/certification/enrollusers.php?ctid=".$sdata->id."");
                            $mouse_overicon=true;
                    }
                     if ((has_capability('local/certification:certificationcompletion', context_system::instance()) || is_siteadmin())) {
                        $line['certificationcompletion'] =  true;
                    }
					$line['certificationcompletion_id'] = $certificationcompletion_id;
                    $line['mouse_overicon']=$mouse_overicon;
                    $row[] = $this->render_from_template('local_certification/browsecertification', $line);
                }
                if (!isset($row[1])) {
                    $row[1] = '';
                }
                $time = number_format((microtime(true) - $startTime), 4);
                $data[] = $row;
            }
            $return = array(
                "recordsTotal" => $certifications['certificationscount'],
                "recordsFiltered" => $certifications['certificationscount'],
                "data" => $data,
                "time" => $time
            );
        }
        return $return;
    }
    /**
     * [viewcertificationsessions description]
     * @method viewcertificationsessions
     * @param  [type]                $certificationid [description]
     * @param  [type]                $stable      [description]
     * @return [type]                             [description]
     */
    public function viewcertificationsessions($certificationid, $stable) {
        global $OUTPUT, $CFG, $DB,$USER;
        $context = context_system::instance();
        if ($stable->thead) {
            $return = '';
            if (has_capability('local/certification:createsession', $context)&&(has_capability('local/certification:managecertification', $context))) {
                $return .= '<div class="createicon">
                                <i title="'.get_string('create_session','local_certification').'" class="fa fa-plus create_session createpopicon" aria-hidden="true" onclick="(function(e){ require(\'local_certification/ajaxforms\').init({contextid:' . $context->id . ', component:\'local_certification\', callback:\'session_form\', form_status:0, plugintype: \'local_certification\', pluginname: \'session\', id:0, ctid: ' . $certificationid . ', title: \'addsession\' }) })(event)"></i>
                            </div>';
            }
            $sessions = (new certification)->certificationsessions($certificationid, $stable);
            if ($sessions['sessionscount'] > 0) {
                $table = new html_table();
                if ((has_capability('local/certification:managecertification', context_system::instance()) || is_siteadmin())) {
                    $table->head = array(get_string('name'), get_string('date'));
                    $table->head[] = get_string('time');
                    $table->head[] = get_string('type', 'local_certification');
                    $table->head[] = get_string('room', 'local_certification');
                    $table->head[] = get_string('status', 'local_certification');
                    $table->head[] =get_string('attended_sessions_users', 'local_certification');
                    $table->head[] = get_string('faculty', 'local_certification');
                    $table->align = array('center', 'center', 'center', 'center', 'center', 'center');
                } else {
                    $table->head = array(get_string('name'), get_string('date'));
                    $table->head[] = get_string('time');
                    $table->head[] = get_string('type', 'local_certification');
                    $table->head[] = get_string('room', 'local_certification');
                    $table->head[] = get_string('status', 'local_certification');
                    $table->head[] =get_string('attended_sessions_users', 'local_certification');
                    $table->head[] = get_string('faculty', 'local_certification');
                    $table->align = array('center', 'center', 'center', 'center', 'center');
                }

                if ((has_capability('local/certification:editsession', context_system::instance()) || has_capability('local/certification:deletesession', context_system::instance())|| has_capability('local/certification:takesessionattendance', context_system::instance()))&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $table->head[] = get_string('actions');
                    $table->align[] = 'center';
                }
                $table->id = 'viewcertificationsessions';
                $table->attributes['data-certificationid'] = $certificationid;
                $return .= html_writer::table($table);
            } else {
                $return .= "<div class='alert alert-info text-center w-full pull-left'>" . get_string('nosessions', 'local_certification') . "</div>";
            }
        } else {
            $sessions = (new certification)->certificationsessions($certificationid, $stable);
            $data = array();
            foreach ($sessions['sessions'] as $sdata) {
                $line = array();
                $line[] = $sdata->name;
                $line[] = '<i class="fa fa-calendar" aria-hidden="true"></i>'.date("d/m/Y", $sdata->timestart);
                $line[] = '<i class="fa fa-clock-o"></i>'.date("H:i", $sdata->timestart) . ' <b> - </b> ' . \local_costcenter\lib::get_userdate("H:i", $sdata->timefinish);
                $link=get_string('pluginname', 'local_certification');
                if($sdata->onlinesession==1){
                       
                        $moduleids = $DB->get_field('modules', 'id', array('name' =>$sdata->moduletype));
                        if($moduleids){
                            $moduleid = $DB->get_field('course_modules', 'id', array('instance' => $sdata->moduleid, 'module' => $moduleids));
                            if($moduleid){
                                $link=html_writer::link($CFG->wwwroot . '/mod/' .$sdata->moduletype. '/view.php?id=' . $moduleid,get_string('join', 'local_certification'), array('title' => get_string('join', 'local_certification')));
                                
                                if (!is_siteadmin() && !has_capability('local/certification:managecertification', context_system::instance())) {
                                    $userenrolstatus = $DB->record_exists('local_certification_users', array('certificationid' => $certificationid, 'userid' => $USER->id));
                                   
                                    if (!$userenrolstatus) {
                                        $link=get_string('join', 'local_certification');
                            
                                    }
                                }
                                
                            }
                        }   
                }
                $line[] = $link;
                $line[] = $sdata->room ? $sdata->room : 'N/A';

                $certification_totalusers = $DB->count_records_select('local_certification_users', 'certificationid = :certificationid ', array('certificationid' => $certificationid),"COUNT(DISTINCT(userid))");
                $attendedsessions_users = $DB->count_records('local_certificatn_attendance',
                array('certificationid' => $certificationid,
                    'sessionid' =>$sdata->id, 'status' => SESSION_PRESENT));

                

                if(has_capability('local/certification:managecertification', context_system::instance())){
                    if ($sdata->timefinish <= time() && $sdata->attendance_status == 1) {
                        $line[] = get_string('completed', 'local_certification');
                    } else {
                        $line[] = get_string('pending', 'local_certification');
                    }
               
                }else{
                    $attendance_status=$DB->get_field_sql("SELECT status  FROM {local_certificatn_attendance} where certificationid= {$certificationid} and sessionid=$sdata->id and userid = {$USER->id} and status=1");
                    if ($sdata->timefinish <= time() && $attendance_status == 1) {
                        $line[] = get_string('completed', 'local_certification');
                    } else {
                        $line[] = get_string('pending', 'local_certification');
                    }
                }
               $line[] = $attendedsessions_users. '/' .$certification_totalusers;
                if($sdata->trainerid){
                     $trainer = $DB->get_record('user', array('id' => $sdata->trainerid));
                    $line[] =  $OUTPUT->user_picture($trainer, array('size' => 30)) . fullname($trainer);
                }else{
                     $line[] ="N/A";
                }
                
                $action = '';
                if ((has_capability('local/certification:editsession', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $action .= '<a href="javascript:void(0);" alt = ' . get_string('edit') . ' title = ' . get_string('edit') . ' onclick="(function(e){ require(\'local_certification/ajaxforms\').init({contextid:1, component:\'local_certification\', callback:\'session_form\', form_status:0, plugintype: \'local_certification\', pluginname: \'session\', id: ' . $sdata->id . ', ctid: ' . $certificationid .', title: \'updatesession\'}) })(event)" ><img src="' . $OUTPUT->image_url('i/edit') . '" alt = ' . get_string('edit') . ' title = ' . get_string('edit') . ' class="icon"/></a>';
                }
                if ((has_capability('local/certification:deletesession', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $action .= '<a href="javascript:void(0);" alt = ' . get_string('delete') . ' title = ' . get_string('delete') . ' onclick="(function(e){ require(\'local_certification/certification\').deleteConfirm({action:\'deletesession\', certificationid: ' . $certificationid . ', id: ' . $sdata->id . ' }) })(event)" ><img src="' . $OUTPUT->image_url('i/trash') . '" alt = ' . get_string('delete') . ' title = ' . get_string('delete') . ' class="icon"/></a>';
                }
                if ((has_capability('local/certification:takesessionattendance', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $action .= '<a href="' . $CFG->wwwroot . '/local/certification/attendance.php?ctid=' . $sdata->certificationid . '&sid=' . $sdata->id . '" ><img src="' . $OUTPUT->image_url('t/assignroles') . '" alt = ' . get_string('attendace', 'local_certification') . ' title = ' . get_string('attendace', 'local_certification') . ' class="icon"/></a>';
                }
                if ((has_capability('local/certification:editsession', context_system::instance()) || has_capability('local/certification:deletesession', context_system::instance())|| has_capability('local/certification:takesessionattendance', context_system::instance()))&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $line[] = $action;
                }
                $data[] = $line;
            }
            $return = array(
                "recordsTotal" => $sessions['sessionscount'],
                "recordsFiltered" => $sessions['sessionscount'],
                "data" => $data,
            );
        }
        return $return;
    }
    /**
     * [viewcertificationevaluations description]
     * @method viewcertificationevaluations
     * @param  [type]                   $certificationid [description]
     * @param  [type]                   $stable      [description]
     * @return [type]                                [description]
     */
    public function viewcertificationevaluations($certificationid, $stable) {
        global $OUTPUT, $CFG, $PAGE, $DB, $USER;
        $systemcontext = context_system::instance();
        $return = '';
        //$is_manager = $DB->record_exists_sql("SELECT ra.userid
        //                                        FROM {role_assignments} as ra
        //                                        JOIN {role} as r ON ra.roleid=r.id
        //                                       WHERE r.archetype='manager' AND ra.userid = {$USER->id} ");
        $certificationevaluations = $DB->get_records_menu('local_evaluations', array('plugin' => 'certification', 'instance' => $certificationid), 'id', 'evaluationtype, evaluationtype as type');
        $function = 'certification_evaluationtypes';
        if (function_exists($function)) {
            $evaltypes = $function();
        }
        //$cr_evals = array_diff_key($evaltypes, $certificationevaluations);
         $exist = $DB->record_exists('local_certification',array('id'=>$certificationid,'trainingfeedbackid'=>0));
         $exist_with_tr_fd = $DB->count_records_sql("SELECT count(id) as total FROM {local_certification_trainers} where certificationid={$certificationid} AND feedback_id>0");
          $exist_with_tr = $DB->count_records('local_certification_trainers',array('certificationid'=>$certificationid));
          //    print_object($exist);
          //print_object($exist_with_tr_fd);
          //print_object($exist_with_tr);

        //if ((is_siteadmin() OR has_capability('local/evaluation:edititems', $systemcontext) OR $is_manager) && ($exist || $exist_with_tr_fd!=$exist_with_tr)) {
        if ((has_capability('local/certification:createfeedback', $systemcontext)) && (has_capability('local/certification:managecertification', $systemcontext)) && ($exist || $exist_with_tr_fd!=$exist_with_tr)) {    

        $return .= '<div class="createicon"><i class="fa fa-plus createpopicon" data-action="createevaluationmodal" title="'.get_string('createevaluation', 'local_evaluation').'" onclick="(function(e){ require(\'local_evaluation/newevaluation\').init(\'createevaluationmodal\','.$systemcontext->id.', -1,'.$certificationid.',\'certification\') })(event)"></i></div>';

        }
        $certification_evaluations = (new certification)->certification_evaluations($certificationid);
        if (empty($certification_evaluations)){
            $return .= "<div class='alert alert-info w-full pull-left'>" . get_string('nocertificationevaluations', 'local_certification') . "</div>";
        } else {
            $table = new html_table();
            $table->head = array(get_string('name'), get_string('type', 'local_certification'), get_string('trainer', 'local_certification'), get_string('submitted', 'local_certification'), get_string('status'));
            if ((has_capability('local/certification:editfeedback', context_system::instance()) || has_capability('local/certification:deletefeedback', context_system::instance()))&&(has_capability('local/certification:managecertification', context_system::instance()) )) {
                $table->head[] =get_string('actions');
            }
            $table->id = 'viewevaluations';
            // $certification_evaluationtypes = certification_evaluationtypes();
            $data = array();
            foreach ($certification_evaluations as $sdata) {
                 $certificationtrainerssql = "SELECT CONCAT(u.firstname, ' ', u.lastname) AS fullname FROM {user} AS u JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                        WHERE ct.certificationid = :certificationid AND ct.feedback_id=:feedbackid AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
                    $params = array();
                    $params['certificationid'] = $certificationid;
                    $params['feedbackid'] =  $sdata->id;
                $certificationtrainer = $DB->get_field_sql($certificationtrainerssql, $params);

                $line = array();
                if(has_capability('local/certification:createfeedback', $systemcontext)){
                     $line[] = '<a href="' . $CFG->wwwroot .'/local/evaluation/eval_view.php?id=' . $sdata->id .'">' . $sdata->name . '</a>';
                }else{
                     $line[] = $sdata->name ;
                }
               
                
                if($sdata->evaluationtype==1){
                    $line[] = get_string('training_feeddback', 'local_certification');
                    $line[] = "";

                }else{
                    $line[] = get_string('trainer_feedback', 'local_certification');
                    $line[] = $certificationtrainer;
                }
                $total_count=$DB->count_records_sql("SELECT count(id) as total FROM {local_certification_users} lcu where lcu.certificationid={$certificationid} ");
                if($sdata->evaluationtype==1){
                     $submitted_count=$DB->count_records_sql("SELECT count(id) as total FROM {local_certification_users} where certificationid={$certificationid} AND trainingfeedback=1");
                }else{
                    $submitted_count=$DB->count_records_sql("SELECT count(fb.id) as total FROM {local_certificatn_trainerfb} as fb JOIN {local_certification_trainers} as f ON f.id=fb.clrm_trainer_id where f.certificationid={$certificationid} AND f.feedback_id={$sdata->id}");
                }
                $line[] = "$submitted_count/$total_count";
                if(!has_capability('local/certification:managecertification', context_system::instance())){
                    if($sdata->evaluationtype==1){
                     $submitted_count=$DB->count_records_sql("SELECT count(id) as total FROM {local_certification_users} where certificationid={$certificationid} AND trainingfeedback=1 and userid={$USER->id}");
                    }else{
                        $submitted_count=$DB->count_records_sql("SELECT count(fb.id) as total FROM {local_certificatn_trainerfb} as fb JOIN {local_certification_trainers} as f ON f.id=fb.clrm_trainer_id where f.certificationid={$certificationid} AND f.feedback_id={$sdata->id} and fb.userid={$USER->id}");
                    }
                    if($submitted_count==0){
                     $line[] = '<a href="' . $CFG->wwwroot .'/local/evaluation/complete.php?id=' . $sdata->id .'">' .get_string("submit"). '</a>';
                    }else{
                        if($submitted_count==0){
                            $line[] = get_string('showentries','local_certification');
                        }else{
                            $line[] = '<a href="' . $CFG->wwwroot .'/local/evaluation/show_entries.php?id=' . $sdata->id .'">' .get_string('showentries','local_certification'). '</a>';
                        }

                    }
                }elseif(has_capability('local/certification:managecertification', context_system::instance())){
                        if($submitted_count==0){
                            $line[] = get_string('showentries','local_certification');
                        }else{
                            $line[] = '<a href="' . $CFG->wwwroot .'/local/evaluation/show_entries.php?id=' . $sdata->id .'">' .get_string('showentries','local_certification'). '</a>';
                        }
                }else{
                    $line[] = $certification_evaluationtypes[$sdata->evaluationtype];
                }

                $action = '';
                if ((has_capability('local/certification:editfeedback', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $action .= '<a href="javascript:void(0);" data-action="createevaluationmodal" data-value="'.$sdata->id.'" alt = ' . get_string('edit') . '
                    title = ' . get_string('edit') . ' onclick="(function(e){ require(\'local_evaluation/newevaluation\').init(\'createevaluationmodal\','.$systemcontext->id.','.$sdata->id.','.$certificationid.',\'certification\') })(event)"><img src="' . $OUTPUT->image_url('t/edit') . '" alt = ' . get_string('edit') . ' title = ' . get_string('edit') . ' class="icon"/></a>';
                }
                // if ((has_capability('local/certification:editcertification', context_system::instance()) || is_siteadmin())) {
                //        $action .= html_writer::link(new moodle_url('/local/evaluation/show_entries.php', array('id' => $sdata->id, 'sesskey' => sesskey())),'<img src="' . $OUTPUT->image_url('i/preview') . '" alt = ' .get_string('showentries','local_certification'). ' title = ' . get_string('showentries','local_certification') . ' class="icon"/>');
                // }
                if ((has_capability('local/certification:deletefeedback', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()) )) {
                    $action .= '<a href="javascript:void(0);" alt = ' . get_string('delete') . '
                        title = ' . get_string('delete') . ' onclick="(function(e){ require(\'local_certification/certification\').deleteConfirm({action:\'deletecertificationevaluation\', certificationid: ' . $certificationid . ', id: ' . $sdata->id . ' }) })(event)" ><img src="' . $OUTPUT->image_url('t/delete') . '" alt = ' . get_string('delete') . ' title = ' . get_string('delete') . ' class="icon"/></a>';
                }
                if ((has_capability('local/certification:editfeedback', context_system::instance()) || has_capability('local/certification:deletefeedback', context_system::instance()))&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $line[] = $action;
                }
                $data[] = $line;
            }
            $table->data = $data;
            $return .= html_writer::table($table);
        }
        return $return;
    }
    /**
     * [viewcertificationcourses description]
     * @method viewcertificationcourses
     * @param  [type]               $certificationid [description]
     * @return [type]                            [description]
     */
    public function viewcertificationcourses($certificationid, $stable) {
        global $OUTPUT, $CFG, $DB,$USER;
        $systemcontext = context_system::instance();
        $assign_courses = '';
        if (has_capability('local/certification:createcourse', $systemcontext)&&(has_capability('local/certification:managecertification', $systemcontext))) {
            $assign_courses .=  '<div class="createicon"><i class="fa fa-plus add_certificationcourse createpopicon" aria-hidden="true" title="Add a Course" onclick="(function(e){ require(\'local_certification/ajaxforms\').init({contextid:' . $systemcontext->id . ', component:\'local_certification\', callback:\'course_form\', form_status:0, plugintype: \'local_certification\', pluginname: \'course\', id:0, ctid: ' . $certificationid . ' }) })(event)"></i></div>';
        }


        if ($stable->thead) {
            $certificationcourses = (new certification)->certification_courses($certificationid, $stable);
            if ($certificationcourses['certificationcoursescount'] > 0) {
                $table = new html_table();
                $table->head = array('');
                //$table->head = array(get_string('name'), );
                //if(is_siteadmin() || has_capability('local/certification:managecertification', context_system::instance())) {
                //      $table->head[] = get_string('completion_status', 'local_certification');
                //}else{
                //     $table->head[] = get_string('completion_status_per', 'local_certification');
                //}
                //
                //if ((has_capability('local/certification:deletecourse', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                //    $table->head[] =  get_string('actions');
                //}
                $table->id = 'viewcertificationcourses';
                $table->attributes['data-certificationid'] = $certificationid;
                //$table->align = array('center', 'center');
                $return = $assign_courses.html_writer::table($table);
            }else{
                $return = $assign_courses."<div class='alert alert-info w-full pull-left'>" . get_string('nocertificationcourses', 'local_certification') . "</div>";
            }
        }else{
             $selfenrolmenttabcap = true;
            if (!has_capability('local/certification:managecertification', context_system::instance())) {
                   $userenrolstatus = $DB->record_exists('local_certification_users', array('certificationid' => $certificationid, 'userid' => $USER->id));
           
                    if (!$userenrolstatus) {
                        $selfenrolmenttabcap = false;
            
                    }
            }
            $certificationcourses = (new certification)->certification_courses($certificationid, $stable);
            $data = array();
            $courseprogress = new progress();
            foreach ($certificationcourses['certificationcourses'] as $sdata) {
                $out_data = '';
                $line = array();
                if($selfenrolmenttabcap){
                     $course_heading = '<a href="' . $CFG->wwwroot .'/course/view.php?id=' . $sdata->id .'">' . $sdata->fullname . '</a>';
                }else{
                     $course_heading = $sdata->fullname;
                }
               
                if(is_siteadmin() || has_capability('local/certification:managecertification', context_system::instance())) {
                    // $courseenrolid = $DB->get_field('enrol', 'id', array('courseid' => $sdata->id, 'enrol' => 'certification'));
                    // $studentid = $DB->get_field('enrol','roleid',array('courseid' => $sdata->id, 'enrol'=>'certification'));
                    // $completedusers = $DB->count_records_select('local_certification_users', 'certificationid = :certificationid AND completion_status <> :completion_status ',  array('certificationid' => $certificationid, 'completion_status' => 0));
                    $enrolledusers = $DB->get_records_menu('local_certification_users',  array('certificationid' =>$certificationid), 'id', 'userid as id, userid');
                    //  print_object($sdata->fullname);
                    // print_object($enrolledusers);
                    $course_completions = $DB->get_records_sql_menu("SELECT id,userid  FROM {course_completions} WHERE course = {$sdata->id} AND timecompleted IS NOT NULL");
                     // print_object($course_completions);
                     $result=array_intersect($enrolledusers,$course_completions);
                     // print_object($result);
                    // $line[] = $completedusers . '/' . user_get_total_participants($sdata->id, 0, 0, $studentid, $courseenrolid);
                     $user_completions = count($result) . '/' . count($enrolledusers);

                } else {
                    $completionstatus = $courseprogress->get_course_progress_percentage($sdata);
                    $user_completions =  $completionstatus !== null ? $completionstatus : '--';
                }

                $action = '';
                if ((has_capability('local/certification:deletecourse', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                    $action .= '<a href="javascript:void(0);" alt = ' . get_string('unassign','local_certification') . ' title = ' . get_string('unassign','local_certification') . ' onclick="(function(e){ require(\'local_certification/certification\').deleteConfirm({action:\'deletecertificationcourse\', certificationid: ' . $certificationid . ',
                    id: ' . $sdata->certificationcourseinstance . ' }) })(event)" >
                    <img src="' . $OUTPUT->image_url('t/delete') . '" alt = ' .
                    get_string('unassign','local_certification') . ' title = ' . get_string('unassign','local_certification') .
                    ' class="icon"/></a>';
                }
                //if ((has_capability('local/certification:deletecourse', context_system::instance()) || is_siteadmin())&&(has_capability('local/certification:managecertification', context_system::instance()))) {
                //    $line[] = $action;
                //}
                $course_schedule = 'Any time';

                $coursepoints = $sdata->open_points;
                if(empty($coursepoints)){
                    $coursepoints = 0;
                }else{
                    $coursepoints = $coursepoints;
                }
                $course_duration = 'Regular';
                 $out_data .= '<div class="certification_course">';

                    $out_data .= '<div class="pull-left w-full">
                                    <h4 class="certification_coursename pull-left">'.$course_heading.'</h4>
                                    <span class="pull-right">'.$action.'</span>
                              </div>';
                       
                    $out_data .= '<div class="pull-left w-half">
                                          <span class="certification_course_label">
                                            <i class="fa fa-trophy certification_course_icon"></i>
                                            <span class="certification_course_labeltext">'.get_string('course_points', 'local_certification').'</span>
                                            <span class="certification_course_colon">:</span>
                                        </span>
                                        <span class="certification_course_value"> '.$coursepoints.'</span>
                              </div>';
                   
                      
                        $out_data .= '<div><div class="pull-left w-half">
                                        <span class="certification_course_label">
                                        <i class="fa fa-chart-bar certification_course_icon"></i>
                                        <span class="certification_course_labeltext">'.get_string('completion_status', 'local_certification').'</span>
                                        <span class="certification_course_colon">:</span>
                                    </span>
                                    <span class="certification_course_value"> '.$user_completions.'</span>
                              </div></div>';          
                     
                    if(!has_capability('local/certification:managecertification', context_system::instance())) {
                        
                        $usercompletion=$DB->get_field_sql("SELECT timecompleted FROM {course_completions} WHERE userid = {$USER->id} AND course = {$sdata->id}");
                        if(!empty($usercompletion)&&$usercompletion!=0){
                             $usercompletion= \local_costcenter\lib::get_userdate("d/m/Y", $usercompletion);
                        }else{
                            $usercompletion=get_string('notavailable_complete', 'local_certification');
                        }
                       
                        $out_data .= '<div><div class="pull-right w-half">
                                    <span class="certification_course_label">
                                        <i class="fa fa-calendar certification_course_icon completionstatus"></i>
                                        <span class="certification_course_labeltext">'.get_string('course_completed', 'local_certification').'</span>
                                        <span class="certification_course_colon">:</span>
                                    </span>
                                    <span class="certification_course_value"> '.$usercompletion.'</span>
                                </div></div>';
                                
                        $userenrollment=$DB->get_field_sql("SELECT ue.timecreated FROM {user_enrolments} AS ue JOIN {enrol} AS e on e.id=ue.enrolid where e.courseid = {$sdata->id} and ue.userid = {$USER->id}");
                         
                        if(!empty($userenrollment)&&$userenrollment!=0){
                             $userenrollment= \local_costcenter\lib::get_userdate("d/m/Y", $userenrollment);
                        }else{
                            $userenrollment=get_string('notavailable', 'local_certification');
                        }
                        
                        $out_data .= '<div class="pull-right w-half">
                                             <span class="certification_course_label">
                                            <i class="fa fa-calendar certification_course_icon"></i>
                                            <span class="certification_course_labeltext">'.get_string('course_enrolled', 'local_certification').'</span>
                                            <span class="certification_course_colon">:</span>
                                        </span>
                                        <span class="certification_course_value"> '.$userenrollment.'</span>
                                </div>';
                    
                                
                    }
                    if($selfenrolmenttabcap){
                        $out_data .= '<a href="' . $CFG->wwwroot .'/course/view.php?id=' . $sdata->id .'" class="btn certification_course_launch">' . get_string('launch', 'local_certification').'<i class="fa fa-chevron-right ml-5"></i>'.'</a>';
                    }

                $out_data .= '</div>';
                $line[]=$out_data;
                $data[] = $line;
            }
            $return = array(
                "recordsTotal" => $certificationcourses['certificationcoursescount'],
                "recordsFiltered" => $certificationcourses['certificationcoursescount'],
                "data" => $data,
            );
        }
        return $return;
    }
    /**
     * Display the certification view
     * @return string The text to render
     */
    public function get_content_viewcertification($certificationid) {
        //print_object($certificationid);
        global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $stable = new stdClass();
        $stable->certificationid = $certificationid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $certification = (new certification)->certifications($stable);
        $unenroll=false;
        //print_object($certification);
        $certification_status = $DB->get_field('local_certification','status',array('id' => $certificationid));
        if(!has_capability('local/certification:view_newcertificationtab', context_system::instance()) && $certification_status==0){
            print_error("You don't have permissions to view this page.");
        }
        elseif(!has_capability('local/certification:view_holdcertificationtab', context_system::instance())&& $certification_status==2){
            print_error("You don't have permissions to view this page.");
        }
        if(empty($certification)) {
            print_error("Certification Not Found!");
        }
        if (!has_capability('local/certification:managecertification', context_system::instance()) && !is_siteadmin()
            && !has_capability('local/certification:manage_multiorganizations', context_system::instance())
            && !has_capability('local/costcenter:manage_multiorganizations', context_system::instance())&& !has_capability('local/certification:manage_owndepartments', context_system::instance())
                 && !has_capability('local/costcenter:manage_owndepartments', context_system::instance()) && !has_capability('local/certification:trainer_viewcertification', context_system::instance())) {

            // $now = time(); // or your date as well
            // $your_date = $certification->startdate;
            // $datediff = $now - $your_date;

            // $daysdiff=round($datediff / (60 * 60 * 24));

            $exists=$DB->get_field('local_certification_users', 'usercreated', array('certificationid'=>$certificationid,'userid'=>$USER->id));
            if(!$exists){
                print_error("You don't have permissions to view this page.");
            }else{
                if($exists == $USER->id)
                    $unenroll=true;
            }
        }
        $includesfile = false;
        if(file_exists($CFG->dirroot.'/local/includes.php')){
            $includesfile = true;
            require_once($CFG->dirroot.'/local/includes.php');
            $includes = new user_course_details();
        }
        
        if ($certification->certificationlogo > 0){
            $certification->certificationlogoimg = (new certification)->certification_logo($certification->certificationlogo);
            if($certification->certificationlogoimg == false){
                if($includesfile){
                    $certification->certificationlogoimg = $includes->get_classes_summary_files($certification); 
                }
            }
        } else {
            if($includesfile){
                $certification->certificationlogoimg = $includes->get_classes_summary_files($certification);
            }
        }
        if ($certification->instituteid > 0) {
            $certification->certificationlocation = $DB->get_field('local_location_institutes', 'fullname', array('id' => $certification->instituteid));
        } else {
            $certification->certificationlocation = 'N/A';
        }

        if ($certification->department == -1) {
            $certification->certificationdepartment = 'All';
        } else {
            $certificationdepartment = $DB->get_fieldset_select('local_costcenter', 'fullname', " CONCAT(',',$certification->department,',') LIKE CONCAT('%,',id,',%') ", array());//FIND_IN_SET(id, '$certification->department')
            $certification->certificationdepartment = implode(', ', $certificationdepartment);
        }

        $certificationtrainerssql = "SELECT u.id, u.picture, u.firstname, u.lastname,
                                        u.firstnamephonetic, u.lastnamephonetic, u.middlename,
                                        u.alternatename, u.imagealt, u.email
                                   FROM {user} AS u
                                   JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                                  WHERE u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND ct.certificationid = {$certification->id}";

        $certificationtrainers = $DB->get_records_sql($certificationtrainerssql);
        $totalcertificationtrainers = count($certificationtrainers);
        $certification->trainerpagination = false;
        if ($totalcertificationtrainers > 3) {
            $certification->trainerpagination = true;
        }
        $certification->trainers  = array();
        if (!empty($certificationtrainers)) {
            foreach($certificationtrainers as $certificationtrainer) {
                $certificationtrainerpic = $OUTPUT->user_picture($certificationtrainer, array('size' => 50, 'class'=>'trainerimg'));
                $certification->trainers[] = array('certificationtrainerpic' => $certificationtrainerpic, 'trainername' => fullname($certificationtrainer), 'trainerdesignation' => 'Trainer', 'traineremail' => $certificationtrainer->email);
            }
        }
        $return="";
        $certification->userenrolmentcap = (has_capability('local/certification:managecertification', context_system::instance())&&has_capability('local/certification:manageusers', context_system::instance()) && $certification->status == 0) ? true : false;
        $certification->selfenrolmentcap = false;
        if (!has_capability('local/certification:managecertification', context_system::instance())) {
            $userenrolstatus = $DB->record_exists('local_certification_users', array('certificationid' => $certification->id, 'userid' => $USER->id));

            $return=false;
            if($certification->id > 0 && $certification->nomination_startdate!=0 && $certification->nomination_enddate!=0){
                $params1 = array();
                $params1['certificationid'] = $certification->id;
                // $params1['nomination_startdate'] = \local_costcenter\lib::get_userdate('d/m/Y H:i',time());
                // $params1['nomination_enddate'] = \local_costcenter\lib::get_userdate('d/m/Y H:i',time());

                $params1['nomination_startdate'] = time();
                $params1['nomination_enddate'] = time();
                // $sql1="SELECT * FROM {local_certification} where id=:certificationid and (from_unixtime(nomination_startdate,'%Y-%m-%d %H:%i')<=:nomination_startdate and from_unixtime(nomination_enddate,'%Y-%m-%d %H:%i')>=:nomination_enddate)";
                $sql1="SELECT * FROM {local_certification} 
                    WHERE id=:certificationid AND nomination_startdate <= :nomination_startdate 
                    AND nomination_enddate >= :nomination_enddate";
                $return=$DB->record_exists_sql($sql1,$params1); 

            }elseif($certification->id > 0 && $certification->nomination_startdate==0 && $certification->nomination_enddate==0){
                $return=true;
            }

           
            if ($certification->status == 1 && !$userenrolstatus && $return) {
                $certification->selfenrolmentcap = true;
                $url = new moodle_url('/local/certification/view.php', array('ctid' =>$certification->id,'action' => 'selfenrol'));
                    //$btn = new single_button($url,get_string('enroll','local_catalog'), 'POST');
                    //$btn->add_confirm_action(get_string('certification_self_enrolment', 'local_certification'));
                    //
                    //$cbutton=str_replace("Enroll",''.get_string('enroll','local_catalog'),$OUTPUT->render($btn));
                    // $cbutton=str_replace('title=""','title="'.get_string('enroll','local_catalog').'"',$cbutton);
                     $certification->selfenrolmentcap='<a href="javascript:void(0);" class="" alt = ' . get_string('enroll','local_certification'). ' title = ' .get_string('enroll','local_certification'). ' onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:\'selfenrol\', id: '.$certification->id.', certificationid:'.$certification->id.',actionstatusmsg:\'certification_self_enrolment\',certificationname:\''.$certification->name.'\'}) })(event)" ><i class="fa fa-pencil-square-o" aria-hidden="true"></i>'.get_string('enroll','local_certification').'</a>';
                     //$certification->selfenrolmentcap= array_values(array($cbutton));
            }
                $certification_capacity_check=(new certification)->certification_capacity_check($certificationid);
                if($certification_capacity_check&&$certification->status == 1 && !$userenrolstatus){
                        $certification->selfenrolmentcap=get_string('capacity_check', 'local_certification');
                }

        }
        $userenrolstatus = $DB->record_exists('local_certification_users', array('certificationid' => $certification->id, 'userid' => $USER->id,'completion_status'=>1));
        $download_certification="";
        if ($userenrolstatus) {
            $linkname = get_string('getcertification', 'local_certification');
            $link = new moodle_url('/local/certification/view.php', array('ctid' =>$certification->id,'tid'=>$certification->templateid,'action' => 'download'));
            $downloadbutton = new single_button($link, $linkname);
            $download_certification = html_writer::tag('div', $OUTPUT->render($downloadbutton), array('style' => 'text-align:center'));
        }
        
        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';

         $requested_users_tab=$certificationcompletion=$feedback_tab=$user_tab=$course_tab=$session_tab=$action =$edit= $delete =$assignusers=$assignusersurl=false;
        //if(has_capability('local/certification:viewsession', context_system::instance())){
            $session_tab=true;
            //$certification->certificationsessions = $this->viewcertificationsessions($certificationid, $stable);
        //}
        //if(has_capability('local/certification:viewcourse', context_system::instance())){
            $course_tab=true;
            $certification->certificationsessions = $this->viewcertificationcourses($certificationid, $stable);
        //}
        if(has_capability('local/certification:viewusers', context_system::instance())){
            $user_tab=true;
            //$certification->certificationsessions = $this->viewcertificationusers($certificationid, $stable);
        }
        //if(has_capability('local/certification:viewfeedback', context_system::instance())){
            $feedback_tab=true;
            //$certification->certificationsessions = $this->viewcertificationevaluations($certificationid, $stable);
        //}
        if ((has_capability('local/certification:managecertification', context_system::instance()) || is_siteadmin()) || $unenroll) {
            $action = true;
        }
        if ((has_capability('local/certification:certificationcompletion', context_system::instance()) || is_siteadmin())) {
                $certificationcompletion =  true;
        }
        if ((has_capability('local/certification:editcertification', context_system::instance()) || is_siteadmin())) {
                $edit =  true;
        }

        if ((has_capability('local/certification:deletecertification', context_system::instance()) || is_siteadmin())) {
                $delete =  true;
        }
        if ((has_capability('local/certification:manageusers', context_system::instance()) || is_siteadmin())) {
                $assignusers =  true;
                $assignusersurl = new moodle_url("/local/certification/enrollusers.php?ctid=".$certificationid."");
        }
        if ((has_capability('local/request:approverecord', context_system::instance()) || is_siteadmin())) {
            $requested_users_tab = true;
            $requestrenderer = $PAGE->get_renderer('local_request');
            $requested_context = $requestrenderer->render_requestview(TRUE, $certificationid, 'certification');
            $request_options = $requested_context['options'];
            $request_dataoptions = $requested_context['dataoptions'];
            $request_filterdata = $requested_context['filterdata'];
        }
        $selfenrolmenttabcap = true;
        if (!has_capability('local/certification:managecertification', context_system::instance())) {
        
                $selfenrolmenttabcap = false;
    
            
        }
        $certification_actionstatus=$this->certification_actionstatus_markup($certification,'certification');
        $totalseats=$DB->get_field('local_certification','capacity',array('id'=>$certificationid)) ;
        $mnsql="SELECT count(distinct(u.id)) FROM {user} AS u
                                                JOIN {local_certification_users} AS cu ON cu.userid = u.id
                                                WHERE cu.certificationid = {$certificationid} AND u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND u.id > 2";
        $allocatedseats=$DB->count_records_sql($mnsql) ;
        
        $sql=" AND cu.completion_status=0 ";
        
        $inprogress=$DB->count_records_sql($mnsql.$sql) ;
        
        $sql=" AND cu.completion_status=1 ";
        
        $completed=$DB->count_records_sql($mnsql.$sql) ;
        
        
        if(!empty($certification->description)){
            $description = \local_costcenter\lib::strip_tags_custom(html_entity_decode($certification->description));
        }else{
            $description="";
        }
        $isdescription = '';
        if (empty($description)) {
           $isdescription = false;
           $decsriptionstring="";
        } else {
            $isdescription = true;
            if (strlen($description) > 270) {
                $decsriptionCut = substr($description, 0, 270);
                $decsriptionstring =  \local_costcenter\lib::strip_tags_custom(html_entity_decode($decsriptionCut));
            }else{
                $decsriptionstring="";
            }
        }

        if (empty($totalseats)||$totalseats==0) {
            $seats_progress = 0;
        } else {
            $seats_progress = round(($allocatedseats/$totalseats)*100);
        }
        $certificationcompletion_id=$DB->get_field('local_certificatn_completion','id',array('certificationid'=>$certificationid));
        if(!$certificationcompletion_id){
            $certificationcompletion_id=0;
        }
        $certification_status=(new certification)->certification_status_strip($certificationid,$certification->status);
        
        $completion_settings = (new certification)->certification_completion_settings_tab($certificationid,false);
       
        $courses_condition=$sessions_condition="";
        if(stripos($completion_settings,'all sessions')!== false){
            $sessions_condition=get_string('all_sessions','local_certification');
        }
        if(stripos($completion_settings,'any sessions')!== false){
            $sessions_condition=get_string('any_sessions','local_certification');
        }
        if(stripos($completion_settings,'all courses')!== false){
            $courses_condition=get_string('all_courses','local_certification');
        }
        if(stripos($completion_settings,'any courses')!== false){
            $courses_condition=get_string('any_courses','local_certification');
        }
        if(stripos($completion_settings,'any courses')!== false){
            $courses_condition=get_string('any_courses','local_certification');
        }
        $certificationcoursessql = "SELECT count(cc.id) as total
                                              FROM {local_certification_courses} AS cc 
                                              JOIN {course} as c on c.id=cc.courseid
                                             WHERE cc.certificationid = {$certificationid} ";

        $certificationcourses = $DB->count_records_sql($certificationcoursessql);
        
        $certificationsessionssql = "SELECT count(cs.id) as total
                                               FROM {local_certification_sessions} AS cs
                                                WHERE cs.certificationid = {$certificationid}";
        $certificationsessions = $DB->count_records_sql($certificationsessionssql);

        $equal_condition = false;
        if($certificationcourses > 0 || $certificationsessions > 0){
            $equal_condition = true;
        }

        // Rating for Certtification module
        $ratings_plugin_exist = \core_component::get_plugin_directory('local', 'ratings');
        if($ratings_plugin_exist){
            require_once($CFG->dirroot . '/local/ratings/lib.php');
            /*$PAGE->requires->js('/local/ratings/js/jquery.rateyo.js');
            $PAGE->requires->js('/local/ratings/js/ratings.js');*/
            $display_ratings = display_rating($certificationid,'local_certification');
            $display_like = display_like_unlike($certificationid,'local_certification');
            $display_review = display_comment($certificationid,'local_certification');
        }else{
            $display_ratings = $display_like = $display_review = null;
        }
        if(!is_siteadmin()) {
             $switchedrole = $USER->access['rsw']['/1'];
            if($switchedrole){
                $userrole = $DB->get_field('role', 'shortname', array('id' => $switchedrole));
            }else{
                $userrole = null;
            }
           if(is_null($userrole) || $userrole == 'user'){
             $certificate_plugin_exist = \core_component::get_plugin_directory('local', 'certificates');
                if($certificate_plugin_exist){
                   if(!empty($certification->certificateid)){
                    $certificate_exists = true;
                    $sql = "SELECT id 
                            FROM {local_certification_users}
                            WHERE certificationid = :certificationid
                            AND completion_status = 1 AND userid = :userid";
                    $completed = $DB->record_exists_sql($sql, array('certificationid'=>$certificationid,'userid'=>$USER->id));
                 if($completed){
                    $certificate_download= true;
                 
                }else{
                    $certificate_download = false;
                }
                $certificateid = $certification->certificateid;
                //$certificate_download['moduletype'] = 'classroom';
            }
        }
       
    }
}
        
        $certificationcontext = [
            'certificationcompletion_id'=>$certificationcompletion_id,
            'certification' => $certification,
            'certificationid' => $certificationid,
            'certificate_download' => $certificate_download,
            'certificate_exists' => $certificate_exists,
            'action' => $action,
            'edit' => $edit,
            'unenroll' => $unenroll,
            'certificationcompletion'=>$certificationcompletion,
            'delete' => $delete,
            'assignusers' => $assignusers,
            'assignusersurl' => $assignusersurl,
            'certification_actionstatus'=>array_values(($certification_actionstatus)),
            'totalseats'=>empty($totalseats)?'N/A':$totalseats,
            'allocatedseats'=>$allocatedseats,
            'selfenrolmenttabcap'=> $selfenrolmenttabcap,
            'description'=>$description,
            'descriptionstring'=>$decsriptionstring,
            'isdescription'=>$isdescription,
            'seats_progress'=>$seats_progress,
            'feedback_tab'=>$feedback_tab,
            'user_tab'=>$user_tab,
            'course_tab'=>$course_tab,
            'session_tab'=>$session_tab,
            'certificationname_string'=>$certification->name,
            'enrolled'=>$allocatedseats,
            'inprogress'=>$inprogress,
            'completed'=>$completed,
            'completion_settings_tab'=>true,
            'target_audience_tab'=>true,
            'requested_users_tab'=>$requested_users_tab,
            'request_options' => $request_options,
            'request_dataoptions' => $request_dataoptions,
            'request_filterdata' => $request_filterdata,
            'certification_status'=>$certification_status,
            'courses_condition'=>$courses_condition,
            'sessions_condition'=>$sessions_condition,
            'equal_condition'=>$equal_condition,
            'certificationcourses_count'=>$certificationcourses,
            'certificationsessions_count'=>$certificationsessions,
			'download_certification'=>array_values((array($download_certification))), 
            'display_ratings' => $display_ratings,
            'display_like' => $display_like,
            'display_review' => $display_review,
            'thisuserid' => $USER->id

        ];
       
        return $this->render_from_template('local_certification/certificationContent', $certificationcontext);
    }
    /**
     * [viewcertificationusers description]
     * @method viewcertificationusers
     * @param  [type]             $certificationid [description]
     * @param  [type]             $stable      [description]
     * @return [type]                          [description]
     */
    public function viewcertificationusers($certificationid, $stable) {
        global $OUTPUT, $CFG, $DB;
        if(has_capability('local/certification:manageusers',  context_system::instance()) && has_capability('local/certification:managecertification',  context_system::instance())){
              $url = new moodle_url('/local/certification/enrollusers.php', array('ctid' =>$certificationid));
             //$assign_users =$OUTPUT->single_button($url,get_string('viewcertification_assign_users', 'local_certification'), 'get',array('class'=>'viewcertificationusers'));
              $assign_users ='<div class="createicon"><a href="'.$url.'"><i class="icon fa fa-user-plus fa-fw add_certificationcourse createpopicon cr_usericon" aria-hidden="true" title="'.get_string('viewcertification_assign_users', 'local_certification').'"></i></a></div>';
        }else{
             $assign_users="";
        }
        $certificate_plugin_exist = \core_component::get_plugin_directory('local', 'certificates');
        if($certificate_plugin_exist){
            $cert_certificateid = $DB->get_field('local_certification', 'certificateid',array('id' =>$certificationid));
            $downloadicon = '<i class="icon fa fa-download" aria-hidden="true"></i>';
        }else{
            $cert_certificateid = null;
        }
        if ($stable->thead) {
            $certificationusers = (new certification)->certificationusers($certificationid, $stable);
            if ($certificationusers['certificationuserscount'] > 0) {
                $table = new html_table();
                $head = array(get_string('employee', 'local_certification'), get_string('employeeid', 'local_certification'),get_string('attended_sessions', 'local_certification'), get_string('attended_hours', 'local_certification'), get_string('status'));
                if($cert_certificateid){
                    $head[] = get_string('certificate', 'local_certificates');
                }
                $table->head = $head;
                $table->id = 'viewcertificationusers';
                $table->attributes['data-certificationid'] = $certificationid;
                // $table->align = array('center', 'center', 'center', 'center', 'center', 'center');
                $return = $assign_users.html_writer::table($table);
            } else {
                $return = $assign_users."<div class='alert alert-info w-full pull-left'>" . get_string('nocertificationusers', 'local_certification') . "</div>";
            }
        } else {
            $certificationusers = (new certification)->certificationusers($certificationid, $stable);
            $data = array();
            foreach ($certificationusers['certificationusers'] as $sdata) {
                $line = array();
                $line[] = '<div>
                                <span>' . $OUTPUT->user_picture($sdata) . ' ' . fullname($sdata) . '</span>
                            </div>';
                $line[] = '<span> <label for="employeeid">' . $sdata->open_employeeid . '</lable></span>';
                // $line[] = '<span> <label for="email">' . $sdata->email . '</lable></span>';
                // $supervisor = $DB->get_field('user', 'CONCAT(firstname, " ", lastname)', array('id' => $sdata->open_supervisorid));
                // $line[] = !empty($supervisor) ? $supervisor : '--';
                $line[] = $sdata->attended_sessions . '/' . $sdata->totalsessions;
                $line[] = $sdata->hours;
                if($sdata->status==4){
                    $status=get_string('not_completed','local_certification');
                }else{
                    $status=get_string('inprogress','local_certification');
                }
                $line[] = $sdata->completion_status == 1 ? '<span class="tag tag-success" title="Completed">&#10004;</span>' : '<span class="tag tag-danger" title="'.$status.'">&#10006;</span>';

                if($cert_certificateid){
                    if($sdata->completion_status == 1){
                        $params = array('ctid'=>$cert_certificateid,'mtype'=>'certification',
                                        'uid'=>$sdata->id, 'mid'=>$certificationid);
                        $url = new moodle_url('/local/certificates/view.php', $params);
                        $downloadlink = html_writer::link($url, $downloadicon, array('title'=>get_string('download_certificate','local_certificates')));
                        $line[] = $downloadlink;
                    }else{
                        $line[] = '--';
                    }
                }
                $data[] = $line;
            }
            $return = array(
                "recordsTotal" => $certificationusers['certificationuserscount'],
                "recordsFiltered" => $certificationusers['certificationuserscount'],
                "data" => $data,
            );
        }
        return $return;
    }
    /**
     * [certification_actionstatus_markup description]
     * @method certification_actionstatus_markup
     * @param  [type]                        $certification [description]
     * @return [type]                                   [description]
     */
    public function certification_actionstatus_markup($certification,$view="browsecertifications") {
        global $DB, $PAGE, $OUTPUT,$CFG;
        
        if($view=="browsecertifications"){
            $certificationclass="";
        }else{
            $certificationclass="course_extended_menu_itemlink";
        }
       
        $return = array();
        $certificationcourseexist = $DB->record_exists('local_certification_courses', array('certificationid' => $certification->id));
        $certificationsessionsexist = $DB->record_exists('local_certification_sessions', array('certificationid' => $certification->id));
        $certificationusersexist = $DB->record_exists('local_certification_users', array('certificationid' => $certification->id));
        
        // if ($certification->templateid > 0 && has_capability('local/certification:certificationdesign',  context_system::instance())) {   
        //     $url = new moodle_url("$CFG->wwwroot/local/certification/edit.php", array('ctid'=>$certification->id,'tid' => $certification->templateid));
        //     $btn = new single_button($url, '', 'POST', array('class'=>'certification_design'));
        //     //$btn->add_confirm_action(get_string('certification_design', 'local_certification'));
        //     $cbutton=str_replace('title=""','title="Certificate Design"',$OUTPUT->render($btn));
        //     $return[]= '<div class="Certification_design">'.$cbutton.'</div>';
        // }
        
        
        //if ($certificationcourseexist && $certificationsessionsexist && $certificationusersexist && $certification->status == 0) {
        if ($certification->status == 0 && has_capability('local/certification:managecertification',  context_system::instance())&&has_capability('local/certification:publish',  context_system::instance())) {   
            // $url = new moodle_url($PAGE->url, array('ctid' => $certification->id, 'status' => 1, 'action' => 'certificationstatus'));
            // $btn = new single_button($url, '', 'POST', array('class'=>'publich_btn'));
            // $btn->add_confirm_action(get_string('certification_active_action', 'local_certification'));
            // $cbutton=str_replace('title=""','title="Publish"',$OUTPUT->render($btn));
            // $return[]= '<div class="publish">'.$cbutton.'</div>';
            $return[]= '<a href="javascript:void(0);" class="'.$certificationclass.'" alt = ' . get_string('publish','local_certification') . ' title = ' .get_string('publish','local_certification')  . ' onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:1, id: ' . $certification->id . ', certificationid: ' . $certification->id .',actionstatusmsg:\'certification_active_action\',certificationname:\''.$certification->name.'\'}) })(event)" ><i class="icon fa fa-share fa-fw" aria-hidden="true" aria-label="" title ="'.get_string('publish','local_certification').'"></i></a>';
            
        }
        if ($certification->status == 2 && has_capability('local/certification:release_hold',  context_system::instance())&&has_capability('local/certification:managecertification',  context_system::instance())) {   
            // $url = new moodle_url($PAGE->url, array('ctid' => $certification->id, 'status' =>0, 'action' => 'certificationstatus'));
            // $btn = new single_button($url, '', 'POST', array('class'=>'publich_btn'));
            // $btn->add_confirm_action(get_string('certification_release_hold_action', 'local_certification'));
            // $cbutton=str_replace('title=""','title="Release Hold"',$OUTPUT->render($btn));
            // $return[]= '<div class="publish">'.$cbutton.'</div>';

            $return[]= '<a href="javascript:void(0);" class="'.$certificationclass.'" alt = ' . get_string('release_hold','local_certification') . ' title = ' .get_string('release_hold','local_certification')  . ' onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:0, id: ' . $certification->id . ', certificationid: ' . $certification->id .',actionstatusmsg:\'certification_release_hold_action\',certificationname:\''.$certification->name.'\'}) })(event)" ><i class="icon fa fa-share fa-fw" aria-hidden="true" aria-label="" title ="'.get_string('release_hold','local_certification').'"></i></a>';
        }
        if($certification->status == 1) {
            
           if(has_capability('local/certification:cancel',  context_system::instance())&&has_capability('local/certification:managecertification',  context_system::instance())) {   
                // $url = new moodle_url($PAGE->url, array('ctid' => $certification->id, 'status' => 3, 'action' => 'certificationstatus'));
             
                // $btn = new single_button($url, '', 'POST');
                // $btn->add_confirm_action(get_string('certification_close_action', 'local_certification'));
                
                // // $cbutton=str_replace("Close",'<i class="icon fa fa-lock" aria-hidden="true" aria-label="" title="Close"></i>',$OUTPUT->render($btn));
                // $cbutton=str_replace('title=""','title="Cancel"',$OUTPUT->render($btn));
                // $return[]= '<div class="close_btn">'.$cbutton.'</div>';
                $return[]= '<a href="javascript:void(0);" class="'.$certificationclass.'" alt = ' . get_string('cancel','local_certification') . ' title = ' .get_string('cancel','local_certification')  . ' onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:3, id: ' . $certification->id . ', certificationid: ' . $certification->id .',actionstatusmsg:\'certification_close_action\',certificationname:\''.$certification->name.'\'}) })(event)" ><i class="icon fa fa-lock fa-fw" aria-hidden="true" aria-label="" title ="'.get_string('cancel','local_certification').'"></i></a>';
           }
            
           if(has_capability('local/certification:hold',  context_system::instance())&&has_capability('local/certification:managecertification',  context_system::instance())) {   
                // $url = new moodle_url($PAGE->url, array('ctid' => $certification->id, 'status' => 2, 'action' => 'certificationstatus'));
                // $btn = new single_button($url, '', 'POST');
                // $btn->add_confirm_action(get_string('certification_hold_action', 'local_certification'));
                
                // // $cbutton=str_replace("Hold",'<i class="icon fa fa-hand-o-up" aria-hidden="true" aria-label="" title="Hold"></i>',$OUTPUT->render($btn));
                // $cbutton=str_replace('title=""','title="Hold"',$OUTPUT->render($btn));
                // $return[]= '<div class="hold">'.$cbutton.'</div>';
            $return[]= '<a href="javascript:void(0);" class="'.$certificationclass.'" alt = ' . get_string('hold','local_certification') . ' title = ' .get_string('hold','local_certification')  . ' onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:2, id: ' . $certification->id . ', certificationid: ' . $certification->id .',actionstatusmsg:\'certification_hold_action\',certificationname:\''.$certification->name.'\'}) })(event)" ><i class="icon fa fa-hand-o-up fa-fw" aria-hidden="true" aria-label="" title ="'.get_string('hold','local_certification').'"></i></a>';
           }
            
             
            $sessionnotattendancetaken = $DB->record_exists('local_certification_sessions', array('certificationid' => $certification->id, 'attendance_status' => 0));
            if(!$sessionnotattendancetaken && (($certification->enddate <= time()&& $certification->enddate >0)||(1==1)) && has_capability('local/certification:complete',  context_system::instance())&&has_capability('local/certification:managecertification',  context_system::instance())) {
            // if($certification->enddate <= time() && has_capability('local/certification:complete',  context_system::instance())&&has_capability('local/certification:managecertification',  context_system::instance())) {    
                // $url = new moodle_url($PAGE->url, array('ctid' => $certification->id, 'status' => 4, 'action' => 'certificationstatus'));
                // $btn = new single_button($url, '', 'POST');
                // $btn->add_confirm_action(get_string('certification_complete_action', 'local_certification'));
                // // $cbutton=str_replace("Mark Complete",'<i class="icon fa fa-check" aria-hidden="true" aria-label="" title="Mark Complete"></i>',$OUTPUT->render($btn));
                // $cbutton=str_replace('title=""','title="Mark Complete"',$OUTPUT->render($btn));
                // $return[]= '<div class="complete">'.$cbutton.'</div>';
                $return[]= '<a href="javascript:void(0);" class="'.$certificationclass.'" alt = ' . get_string('mark_complete','local_certification') . ' title = ' .get_string('mark_complete','local_certification')  . ' onclick="(function(e){ require(\'local_certification/certification\').ManagecertificationStatus({action:4, id: ' . $certification->id . ', certificationid: ' . $certification->id .',actionstatusmsg:\'certification_complete_action\',certificationname:\''.$certification->name.'\'}) })(event)" ><i class="icon fa fa-check fa-fw" aria-hidden="true" aria-label="" title ="'.get_string('mark_complete','local_certification').'"></i></a>';
            }
        }
        return $return;
    }
    public function viewcertificationattendance($certificationid, $sessionid = 0) {
        global $PAGE, $OUTPUT, $DB;
        $certification = new certification();
        $attendees = $certification->certification_get_attendees($certificationid, $sessionid);
        $return = '';
        if (empty($attendees)) {
            $return .= "<div class='alert alert-info'>" . get_string('nocertificationusers', 'local_certification') . "</div>";
        } else {
            $return .= '<form method="post" id="formattendance" action="' . $PAGE->url . '">';
            $return .= '<input type="hidden" name="action" value="attendance" />';
            $params = array();
            $params['certificationid'] = $certificationid;
            $sqlsessionconcat = '';
            if ($sessionid > 0) {
                $sqlsessionconcat = " AND id = :sessionid";
                $params['sessionid'] = $sessionid;
            }
            $sessions = $DB->get_fieldset_select('local_certification_sessions', 'id',
                'certificationid = :certificationid ' . $sqlsessionconcat, $params);
            foreach ($attendees as $attendee) {
                if (!$sessionid) {
                    $attendancestatuslist = $DB->get_records_sql('SELECT sessionid, id AS attendanceid, sessionid, status, userid FROM {local_certificatn_attendance} WHERE certificationid = :certificationid AND userid = :userid', array('certificationid' => $certificationid, 'userid' => $attendee->id));
                }
                $list = array();
                $list[] = $OUTPUT->user_picture($attendee, array('size' => 30)) .
                fullname($attendee);
                foreach($sessions as $session) {
                    if($sessionid > 0) {
                        $attendanceid = $attendee->attendanceid;
                        $attendancestatus = $attendee->status;
                    } else {
                        $attendanceid = isset($attendancestatuslist[$session]->attendanceid) && $attendancestatuslist[$session]->attendanceid > 0 ? $attendancestatuslist[$session]->attendanceid : 0;
                        $attendancestatus = isset($attendancestatuslist[$session]->status) && $attendancestatuslist[$session]->status > 0 ? $attendancestatuslist[$session]->status : 0;
                    }

                    $encodeddata = base64_encode(json_encode(array(
                            'certificationid' => $certificationid, 'sessionid' => $session,
                            'userid' => $attendee->id, 'attendanceid' => $attendanceid)));
                    $radio = '<input type="hidden" value="' . $encodeddata . '"
                    name="attendeedata[]">';
                    
                    $check_exist=$DB->get_field('local_certificatn_attendance','id',array('sessionid'=>$session,'userid'=>$attendee->id));
                    if($check_exist){
                        $checked = '';
                    }else{
                        $checked = 'checked';
                    }
                    
                    if ($attendancestatus == 2) {
                        $checked = '';
                        $status = $sessionid > 0 ? "Absent" : "A";
                        $status = '<span class="tag tag-danger">'.$status.'</span>';
                    } else if ($attendancestatus == 1) {
                        $status = $sessionid > 0 ? "Present" : "P";
                        $checked = 'checked';
                        $status = '<span class="tag tag-success">'.$status.'</span>';
                    } else {
                        $status = $sessionid > 0 ? "Not yet given" : "NY";
                        $status = '<span class="tag tag-warning">'.$status.'</span>';
                    }
                    $radio .= '<input type="checkbox" name="status[' . $encodeddata .']"
                         ' . $checked  .' class="checksingle'.$session.'">';
                    if ($sessionid > 0) {
                        $list[] = $status;
                    } else {
                        //$radio .= "<div>$status</div>";
                    }
                    $list[] = $radio;
                }
                $data[] = $list;
            }
            $table = new html_table();
            $script="";
            if ($sessionid > 0) {
                $table->head = array('Employee', 'Status', 'Attendance<p><input type=checkbox name=checkAll id=checkAll'.$sessionid.'> Select All</p>');
                 $script .= html_writer::script("
                         $('#checkAll$sessionid').change(function () {
                                $('.checksingle$sessionid').prop('checked', $(this).prop('checked'));
                         });        
                     ");
            } else {
                $table->head[] = 'Employee';
                foreach ($sessions as $session) {
                    $table->head[] = 'Session ' . $session.'<p><input type=checkbox name=checkAll id=checkAll'.$session.'> Select All</p>';
                     $script .= html_writer::script("
                         $('#checkAll$session').change(function () {
                                $('.checksingle$session').prop('checked', $(this).prop('checked'));
                         });        
                     ");
                }
            }
            $table->data = $data;
            $return .= html_writer::table($table);
            $return .= '<input type="submit" name="submit" value="Submit">';
            $return .= '<input type="submit" name="reset" value="Reset Selected">';
            $return .= '</form>';
            $return .= "<div id='result'></div>".$script;
           
        }
        return $return;
    }
    //  public function managecertificationcategories() {
    //     $stable = new stdClass();
    //     $stable->thead = true;
    //     $stable->start = 0;
    //     $stable->length = -1;
    //     $stable->search = '';
    //     $tabscontent = $this->viewcertifications($stable);
    //     $certificationtabslist = [
    //         'certificationtabslist' => $tabscontent
    //     ];
    //     return $this->render_from_template('local_certification/certificationtabs', $certificationtabslist);
    // }
    public function viewcertificationlastchildpopup($certificationid){
         global $OUTPUT, $CFG, $DB, $USER, $PAGE;
        $stable = new stdClass();
        $stable->certificationid = $certificationid;
        $stable->thead = false;
        $stable->start = 0;
        $stable->length = 1;
        $certification = (new certification)->certifications($stable);
        $context = context_system::instance();
        $certification_status = $DB->get_field('local_certification','status',array('id' => $certificationid));
        if(!has_capability('local/certification:view_newcertificationtab', context_system::instance()) && $certification_status==0){
            print_error("You don't have permissions to view this page.");
        }
        elseif(!has_capability('local/certification:view_holdcertificationtab', context_system::instance())&& $certification_status==2){
            print_error("You don't have permissions to view this page.");
        }
        if(empty($certification)) {
            print_error("Certification Not Found!");
        }
        if(file_exists($CFG->dirroot.'/local/includes.php')){
            require_once($CFG->dirroot.'/local/includes.php');
        }
        $includes = new user_course_details();
        if ($certification->certificationlogo > 0){
            $certification->certificationlogoimg = (new certification)->certification_logo($certification->certificationlogo);
            if($certification->certificationlogoimg == false){
                $certification->certificationlogoimg = $includes->get_classes_summary_files($sdata); 
            }
        } else {
            $certification->certificationlogoimg = $includes->get_classes_summary_files($certification);
        }
        //if ($certification->category > 0) {
        //    $certification->category = $DB->get_field('local_location_institutes', 'category', array('id' => $certification->instituteid));
        //} else {
        //    $certification->category = 'N/A';
        //}
        if ($certification->instituteid > 0) {
            $certification->certificationlocation = $DB->get_field('local_location_institutes', 'fullname', array('id' => $certification->instituteid));
        } else {
            $certification->certificationlocation = 'N/A';
        }


        if ($certification->department == -1) {
             $certification->certificationdepartment = 'All';
            $certification->certificationdepartmenttitle = 'All';
        } else {
            $certificationdepartment = $DB->get_fieldset_select('local_costcenter', 'fullname', " concat(',', id, ',') LIKE '%,$certification->department,%'", array());//FIND_IN_SET(id, '$certification->department')
             $certification->certificationdepartment =  (count($certificationdepartment)>1) ? $certificationdepartment[0].'...' : $certificationdepartment[0];
            $certification->certificationdepartmenttitle = implode(', ', $certificationdepartment);
        }

        $certificationtrainerssql = "SELECT u.id, u.picture, u.firstname, u.lastname,
                                        u.firstnamephonetic, u.lastnamephonetic, u.middlename,
                                        u.alternatename, u.imagealt, u.email
                                   FROM {user} AS u
                                   JOIN {local_certification_trainers} AS ct ON ct.trainerid = u.id
                                  WHERE u.confirmed = 1 AND u.suspended = 0 AND u.deleted = 0 AND ct.certificationid = {$certification->id}";

        $certificationtrainers = $DB->get_records_sql($certificationtrainerssql);
        $totalcertificationtrainers = count($certificationtrainers);
        $certification->trainerpagination = false;
        if ($totalcertificationtrainers > 3) {
            $certification->trainerpagination = true;
        }
        $certification->trainers  = array();
        if (!empty($certificationtrainers)) {
            foreach($certificationtrainers as $certificationtrainer) {
                $certificationtrainerpic = $OUTPUT->user_picture($certificationtrainer, array('size' => 50, 'class'=>'trainerimg'));
                $certification->trainers[] = array('certificationtrainerpic' => $certificationtrainerpic, 'trainername' => fullname($certificationtrainer), 'trainerdesignation' => 'Trainer', 'traineremail' => $certificationtrainer->email);
            }
        }
        $return="";
        $certification->userenrolmentcap = (has_capability('local/certification:manageusers', context_system::instance()) &&has_capability('local/certification:managecertification', context_system::instance()) && $certification->status == 0) ? true : false;
    
        $stable = new stdClass();
        $stable->thead = true;
        $stable->start = 0;
        $stable->length = -1;
        $stable->search = '';
        
        $totalseats=$DB->get_field('local_certification','capacity',array('id'=>$certificationid)) ;
        $allocatedseats=$DB->count_records('local_certification_users',array('certificationid'=>$certificationid)) ;
        $coursesummary = \local_costcenter\lib::strip_tags_custom($course->summary,
                    array('overflowdiv' => false, 'noclean' => false, 'para' => false));
        $description = \local_costcenter\lib::strip_tags_custom(html_entity_decode($certification->description));
        $isdescription = '';
        if (empty($description)) {
           $isdescription = false;
        } else {
            $isdescription = true;
            if (strlen($description) > 250) {
                $decsriptionCut = substr($description, 0, 250);
                $decsriptionstring =  \local_costcenter\lib::strip_tags_custom(html_entity_decode($decsriptionCut),array('overflowdiv' => false, 'noclean' => false, 'para' => false));;
            }else{
                $decsriptionstring="";
            }
        }

        if (empty($totalseats)||$totalseats==0) {
            $seats_progress = 0;
        } else {
            $seats_progress = round(($allocatedseats/$totalseats)*100);
        }
        $certificationcontext = [
            'certification' => $certification,
            'certificationid' => $certificationid,
            'totalseats'=>empty($totalseats)?'N/A':$totalseats,
            'allocatedseats'=>$allocatedseats,
            'description'=>$description,
            'descriptionstring'=>$decsriptionstring,
            'isdescription'=>$isdescription,
            'seats_progress'=>$seats_progress,
            'contextid' => $context->id,
            'linkpath'=>"$CFG->wwwroot/local/certification/view.php?ctid=$certificationid"
        ];
       
        return $this->render_from_template('local_certification/certificationview', $certificationcontext);
    }
    /**
     * [viewcertificationcompletion_settings_tab description]
     * @param  [type] $certificationid [description]
     * @return [type]              [description]
     */
    public function viewcertificationcompletion_settings_tab($certificationid) {
        global $OUTPUT, $CFG, $DB,$USER;
         $completion_settings = (new certification)->certification_completion_settings_tab($certificationid);

         return $completion_settings;
    }
    public function viewcertificationtarget_audience_tab($certificationid) {
        global $OUTPUT, $CFG, $DB,$USER;
         $completion_settings = (new certification)->certificationtarget_audience_tab($certificationid);

         return $completion_settings;
    }
    public function view_certification_sessions($certificationid) {
        global $OUTPUT, $CFG, $DB,$USER;
        $context = context_system::instance();
        if ($stable->thead) {
            $return = '';
            // if (is_siteadmin() || has_capability('local/costcenter:manage', $context)) {
            //     $return .= '<div class="createicon"><img src="' . $OUTPUT->image_url('filtericon', 'local_certification') . '" title="Add a Session" class="create_session createpopicon" onclick="(function(e){ require(\'local_certification/ajaxforms\').init({contextid:' . $context->id . ', component:\'local_certification\', callback:\'session_form\', form_status:0, plugintype: \'local_certification\', pluginname: \'session\', id:0, ctid: ' . $certificationid . ', title: \'addsession\' }) })(event)"/></div>';
            // }
            $sessions = (new certification)->certificationsessions($certificationid, $stable);
            if ($sessions['sessionscount'] > 0) {
                $table = new html_table();
                if ((has_capability('local/certification:managecertification', context_system::instance())|| is_siteadmin())) {
                    $table->head = array(get_string('name'), get_string('date'));
                    $table->head[] = get_string('type', 'local_certification');
                    $table->head[] = get_string('room', 'local_certification');
                    $table->head[] = get_string('status', 'local_certification');
                    $table->head[] = get_string('faculty', 'local_certification');
                } else {
                    $table->head = array(get_string('name'), get_string('date'));
                    $table->head[] = get_string('type', 'local_certification');
                    $table->head[] = get_string('room', 'local_certification');
                    $table->head[] = get_string('status', 'local_certification');
                }

                //if ((has_capability('local/certification:managesession', context_system::instance()) || is_siteadmin() || has_capability('local/certification:takesessionattendance', context_system::instance()))) {
                //    $table->head[] = get_string('options');
                //}
                $table->id = 'viewcertificationsessions';
                $table->attributes['data-certificationid'] = $certificationid;
                $return .= html_writer::table($table);
            } else {
                $return .= "<div class='alert alert-info text-center w-full pull-left'>" . get_string('nosessions', 'local_certification') . "</div>";
            }
        } else {
            $sessions = (new certification)->certificationsessions($certificationid, $stable);
            $data = array();
            foreach ($sessions['sessions'] as $sdata) {
                $line = array();
                $line[] = $sdata->name;
                $line[] = \local_costcenter\lib::get_userdate("d/m/Y H:i", $sdata->timestart) . ' to ' . \local_costcenter\lib::get_userdate("d/m/Y H:i", $sdata->timefinish);
                
                $link=get_string('pluginname', 'local_certification');
                if($sdata->onlinesession==1){
                       
                        $moduleids = $DB->get_field('modules', 'id', array('name' =>$sdata->moduletype));
                        if($moduleids){
                            $moduleid = $DB->get_field('course_modules', 'id', array('instance' => $sdata->moduleid, 'module' => $moduleids));
                            if($moduleid){
                                $link=html_writer::link($CFG->wwwroot . '/mod/' .$sdata->moduletype. '/view.php?id=' . $moduleid,get_string('join', 'local_certification'), array('title' => get_string('join', 'local_certification')));
                                
                                if (!has_capability('local/certification:managecertification', context_system::instance())) {
                                    $userenrolstatus = $DB->record_exists('local_certification_users', array('certificationid' => $certificationid, 'userid' => $USER->id));
                                   
                                    if (!$userenrolstatus) {
                                        $link=get_string('join', 'local_certification');
                            
                                    }
                                }
                                
                            }
                        }   
                }
                $line[] = $link;
                $line[] = $sdata->room ? $sdata->room : 'N/A';
                if ($sdata->timefinish <= time() && $sdata->attendance_status == 1) {
                    $line[] = get_string('completed', 'local_certification');
                } else {
                    $line[] = get_string('pending', 'local_certification');
                }
                $trainer = $DB->get_record('user', array('id' => $sdata->trainerid));

                if($trainer){
                    $line[] = fullname($trainer);
                }else{
                    $line[] = 'N/A';
                }
                // $action = '';
                // if ((has_capability('local/certification:editsession', context_system::instance()) || is_siteadmin())) {
                //     $action .= '<a href="javascript:void(0);" alt = ' . get_string('edit') . ' title = ' . get_string('edit') . ' onclick="(function(e){ require(\'local_certification/ajaxforms\').init({contextid:1, component:\'local_certification\', callback:\'session_form\', form_status:0, plugintype: \'local_certification\', pluginname: \'session\', id: ' . $sdata->id . ', ctid: ' . $certificationid .', title: \'updatesession\'}) })(event)" ><img src="' . $OUTPUT->image_url('t/edit') . '" alt = ' . get_string('edit') . ' title = ' . get_string('edit') . ' class="icon"/></a>';
                // }
                // if ((has_capability('local/certification:deletesession', context_system::instance()) || is_siteadmin())) {
                //     $action .= '<a href="javascript:void(0);" alt = ' . get_string('delete') . ' title = ' . get_string('delete') . ' onclick="(function(e){ require(\'local_certification/certification\').deleteConfirm({action:\'deletesession\', id: ' . $sdata->id . ' }) })(event)" ><img src="' . $OUTPUT->image_url('t/delete') . '" alt = ' . get_string('delete') . ' title = ' . get_string('delete') . ' class="icon"/></a>';
                // }
                // if ((has_capability('local/certification:takesessionattendance', context_system::instance()) || is_siteadmin())) {
                //     $action .= '<a href="' . $CFG->wwwroot . '/local/certification/attendance.php?ctid=' . $sdata->certificationid . '&sid=' . $sdata->id . '" ><img src="' . $OUTPUT->image_url('t/assignroles') . '" alt = ' . get_string('attendace', 'local_certification') . ' title = ' . get_string('attendace', 'local_certification') . ' class="icon"/></a>';
                // }
                // if ((has_capability('local/certification:managecertification', context_system::instance()) || is_siteadmin() || has_capability('local/certification:trainer_viewcertification', context_system::instance()))) {
                //     $line[] = $action;
                // }
                $data[] = $line;
            }
            // $return = array(
            //     "recordsTotal" => $sessions['sessionscount'],
            //     "recordsFiltered" => $sessions['sessionscount'],
            //     "data" => $data,
            // );
            $return = $data;
        }
        $table = new html_table();
        $table->id = "session_view";
        $table->head = array('Name','Date','Type','Room','Status','Trainer');
        $table->align = array('center', 'center');
        $table->width = '99%';
        $table->data = $return;
        $output = html_writer::table($table);
        return $output;
    }
    
    /**
     * Renders html to print list of certifications tagged with particular tag
     *
     * @param int $tagid id of the tag
     * @param bool $exclusivemode if set to true it means that no other entities tagged with this tag
     *             are displayed on the page and the per-page limit may be bigger
     * @param int $fromctx context id where the link was displayed, may be used by callbacks
     *            to display items in the same context first
     * @param int $ctx context id where to search for records
     * @param bool $rec search in subcontexts as well
     * @param array $displayoptions
     * @return string empty string if no courses are marked with this tag or rendered list of courses
     */
  public function tagged_certifications($tagid, $exclusivemode, $ctx, $rec, $displayoptions, $count = 0, $sort='') {
    global $CFG, $DB, $USER;
    $systemcontext = context_system::instance();
    if ($count > 0)
    $sql =" select count(c.id) from {local_certification} c ";
    else
    $sql =" select c.* from {local_certification} c ";

    $where = " where c.id IN (SELECT t.itemid FROM {tag_instance} t WHERE t.tagid = :tagid AND t.itemtype = :itemtype AND t.component = :component) ";

    $joinsql = $groupby = $orderby = '';
    if (!empty($sort)) {
      switch($sort) {
        case 'highrate':
        if ($DB->get_manager()->table_exists('local_rating')) {
          $joinsql .= " LEFT JOIN {local_rating} as r ON r.moduleid = c.id AND r.ratearea = 'local_certification' ";
          $groupby .= " group by c.id ";
          $orderby .= " order by AVG(rating) desc ";
        }
        
        break;
        case 'lowrate':  
        if ($DB->get_manager()->table_exists('local_rating')) {  
          $joinsql .= " LEFT JOIN {local_rating} as r ON r.moduleid = c.id AND r.ratearea = 'local_certification' ";
          $groupby .= " group by c.id ";
          $orderby .= " order by AVG(rating) asc ";
        }
        break;
        case 'latest':
        $orderby .= " order by c.timecreated desc ";
        break;
        case 'oldest':
        $orderby .= " order by c.timecreated asc ";
        break;
        default:
        $orderby .= " order by c.timecreated desc ";
        break;
        }
    }

    $whereparams = array();
    $conditionalwhere = '';
    if (!is_siteadmin()) {
        $wherearray = orgdepsql($systemcontext); // get records department wise
        $whereparams = $wherearray['params'];
        $conditionalwhere = $wherearray['sql'];
    }    

    $tagparams = array('tagid' => $tagid, 'itemtype' => 'certification', 'component' => 'local_certification');
    $params = array_merge($tagparams, $whereparams);
    if ($count > 0) {
      $records = $DB->count_records_sql($sql.$where.$conditionalwhere, $params);
      return $records;
    } else {
      $records = $DB->get_records_sql($sql.$joinsql.$where.$conditionalwhere.$groupby.$orderby, $params);
    }
    $tagfeed = new \local_tags\output\tagfeed(array(), 'certifications');
    $img = $this->output->pix_icon('i/course', '');
    foreach ($records as $key => $value) {
      $url = $CFG->wwwroot.'/local/certification/view.php?ctid='.$value->id.'';
      $imgwithlink = html_writer::link($url, $img);
      $modulename = html_writer::link($url, $value->name);
      $testdetails = get_certification_details($value->id);
      $details = '';
      $details = $this->render_from_template('local_certification/tagview', $testdetails);
      $tagfeed->add($imgwithlink, $modulename, $details);
    }
    return $this->output->render_from_template('local_tags/tagfeed', $tagfeed->export_for_template($this->output));
  }
    public function get_userdashboard_certification($tab, $filter = false){
        $systemcontext = context_system::instance();

        $options = array('targetID' => 'dashboard_certification', 'perPage' => 6, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'card');
        $options['methodName'] = 'local_certification_userdashboard_content_paginated';
        $options['templateName'] = 'local_certification/userdashboard_paginated';
        $options['filter'] = $tab;
        $options = json_encode($options);
        $filterdata = json_encode(array());
        $dataoptions = json_encode(array('contextid' => $systemcontext->id));
        $context = [
                'targetID' => 'dashboard_certification',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];
        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('local_costcenter/cardPaginate', $context);
        }
    }
}
