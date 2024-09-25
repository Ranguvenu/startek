<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once('locallib.php');
class badge_form extends moodleform {
    public function definition() {
        global $CFG,$DB,$COURSE,$USER;
        $mform = $this->_form;
        $pid = $this->_customdata['pid'];
        $id = $this->_customdata['id'];
        $type = $this->_customdata['type'];
        $badgegroupid = $this->_customdata['badgegroupid'];
        $disabled = '';
        $options = '';
        $select1 = array();
        $select1[0] = '';
        if($id){
            $disabled = 'disabled';
        }
        if($badgegroupid){
            $event = $DB->get_field('block_gm_events', 'eventcode',  array('id' => $badgegroupid));
            if(is_siteadmin()){
                $params = array('costcenter' => $USER->open_costcenterid);
            }else{
                $params = array();
            }
            if($event == 'ce' || $event == 'cc'){
                if(is_siteadmin()){
                    $options = $DB->get_records_select_menu('course', 'id!=1 order by id ASC', array(), '','id,fullname');    
                }else{
                    $options = $DB->get_records_select_menu('course', 'id!=1 AND open_costcenterid='.$USER->open_costcenterid.' order by id ASC', array(), '','id,fullname');
                }
                $select1[0] = 'Select Courses';
            } elseif($event == 'clc'){
                $options = $DB->get_records_menu('local_classroom', $params, '', 'id,name');
                $select1[0] = 'Select classrooms';
            }
            elseif($event == 'ctc'){
                $options = $DB->get_records_menu('competency', array(), '', 'id,shortname');
                $select1[0] = 'Select competencies';
            }
            elseif($event == 'lpc'){
                $options = $DB->get_records_menu('local_learningplan', $params, '', 'id,name');
                $select1[0] = 'Select learningplan';
            }elseif($event == 'certc'){
                $options = $DB->get_records_menu('local_certification', $params, '', 'id,name');
                $select1[0] = 'Select certification';
            }else if($event == 'progc'){
                $options = $DB->get_records_menu('local_program', $params, '', 'id,name');
                $select1[0] = 'Select program';
            }
            // $options = $select1+$options;
        }
        $mform->setDisableShortforms(true);
        $badgegroups = $DB->get_records_menu('block_gm_events');
        $select[NULL] = 'Select badge group';
        $badgegroups = $select+$badgegroups;
        $addbadgeurl= new moodle_url('/blocks/gamification/addbadges.php',array('pid'=>$COURSE->id, 'id' => $id));
        $addbadges = html_writer::link($addbadgeurl,'Add Badges');
        $mform->addElement('hidden', 'pid',$pid);
        $mform->setType('pid',PARAM_INT);
        if($id){
            $mform->addElement('hidden', 'id',$id);
            $mform->setType('id',PARAM_INT);
            $mform->addElement('hidden', 'type',$type);
            $mform->setType('type',PARAM_TEXT);
            $addbadges = html_writer::link($addbadgeurl,'Edit Badges');
        }
        $mform->addElement('header', 'addbadgesheader', $addbadges);
        if(is_siteadmin()){
            $costcenters = $DB->get_records_menu('local_costcenter', array('parentid' => 0), '',  'id,fullname');
            $costcenter_select[null] = 'select costcenter';
            $costcenters = $costcenter_select+$costcenters;
            $mform->addElement('select',  'costcenterid',  get_string('costcenter','block_gamification'),$costcenters,array($disabled => true,'id'=>'costcenter','data-placeholder'=>'--Select costcenter--'));
            $mform->addRule('costcenterid',  get_string('costcenternamemissing', 'block_gamification'), 'required', null, 'client');
        }else{
            $mform->addElement('hidden', 'costcenterid',$USER->open_costcenterid);
            $mform->setType('costcenterid',PARAM_INT);
        }
        $mform->addElement('select',  'badgegroupid',  get_string('badgegroup','block_gamification'),$badgegroups,array($disabled => true,'id'=>'badgegroup','data-placeholder'=>'--Select badge group--'));
        // $mform->addElement('html', '<span id="badgegroupiderror" style="margin-left:50px;color:red">badgegroup</span>');
        
        $mform->addElement('text',  'badgename',get_string('badgename','block_gamification'), array('id' => 'badgename'));
        $mform->setType('badgename',PARAM_RAW);
        $mform->addRule('badgename', get_string('badgenamemissing', 'block_gamification'), 'required', null, 'client');
        // $mform->addElement('html', '<span id="badgenameerror" style="margin-left:50px;color:red">badgenameerror</span>');
        $mform->addElement('text','shortname',get_string('shortname','block_gamification'),array($disabled => true,'id'=>'shortname'));
        $mform->setType('shortname',PARAM_RAW);
        // $mform->addElement('html', '<span id="shortnameerror" style="margin-left:50px;color:red">shortnameerror</span>'); 
        $displayname = 'Course';
        if($id){
            $radiobuttons = 'radiobuttonsdisplay';
            $typename = $DB->get_field('block_gm_events', 'eventcode', array('id'=> $badgegroupid));
            if ($typename=='ctc') {
                $displayname = 'Competencies';
            } else if ($typename=='clc'){
                $displayname = 'Classrooms';
            } else if ($typename=='lpc'){
                $displayname = 'Learningplan';
            } else if($typename=='certc'){
                $displayname = 'Certification';
            }else if($typename=='progc'){
                $displayname = 'Program';
            }
            // $name = html_writer::span($displayname, '', array('class' => 'courseorpointsdisplay'));
        }
        else{
            $radiobuttons = 'radiobuttons';
        }
        $mform->addElement('html', '<div id='.$radiobuttons.'>');
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'type', '', 'Points &nbsp ', 'points',array('onclick'=>'displaytextbox();', 'id' => 'pointsradio', $disabled => true));

        $name = html_writer::span($displayname,'',array('class' => 'courseorpointsdisplay'));

        $radioarray[] = $mform->createElement('radio', 'type', '', $name , 'course' ,array('onclick'=>'displayselectbox();', 'id' => 'courseradio', $disabled => true));
        $mform->addGroup($radioarray, 'radioar', get_string('badgetype','block_gamification'), array(''), false);
        // print_object($type);
        $mform->setDefault('type', $type);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<span id="radioerror" style="margin-left:50px;color:red;display:none"></span>');
        $placeholder = '';
        if($type=='course'){
            $course_display = 'displaycourse';
            $mform->addElement('html', '<div id="courseselecteditdisplay">');
            $placeholder = $select1[0];
            $selectedit = $mform->addElement('select',  'course',  $name,$options,array('id'=>'courseselectedit','data-placeholder'=> $placeholder));
            $selectedit->setMultiple(true); 
            $mform->addElement('html', '</div>');

        } else {
            $course_display = 'displaycoursesfield';

        }
        if($type == 'points' || !$id){
            $mform->addElement('html', '<div id='.$course_display.'>');
            $select = $mform->addElement('select',  'course',  $name,$options,array('id'=>'courseselect'/*,'data-placeholder'=> $placeholder*/));
            $select->setMultiple(true);
         
            $mform->addElement('html', '</div>');
        }
        $mform->addElement('html', '<span id="courseserror" style="margin-left:50px;color:red;display:none"></span>');
        // $mform->setData
        if($type=='points'){
            $data_display = "displaypoints";
        }else{
            $data_display = "displaypointsfield";
        }
        $mform->addElement('html', '<div id='.$data_display.'>');
        $mform->addElement('text', 'points',get_string('points','block_gamification'), array('id' => 'pointsfield')); 
        $mform->setType('points',PARAM_INT);
        $mform->addElement('html', '<span id="pointserror" style="margin-left:50px;color:red;display:none"></span>');       
        // html_writer::span('','',array('id' => 'pointserror'));
        // $mform->addRule('points', get_string('pointsmissing', 'block_gamification'), 'required', null, 'client');
        // $mform->addRule('points', get_string('invalidformatpoints', 'block_gamification'), 'numeric', null, 'client');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<span id="courseerror" style="margin-left:50px;color:red;display:none"></span>');
        // $mform->addElement('checkbox', 'active', get_string('active','block_gamification'));
        // $mform->addHelpButton('active', 'active','block_gamification');
        $mform->addElement('filepicker', 'badgeimg', get_string('badgeimage','block_gamification'), null, array('accepted_types' => array('web_image')));
        $mform->addRule('badgeimg', get_string('noimage', 'block_gamification'), 'required', null, 'client');
        $mform->addElement('html', '<span id="imagenotplacederror" style="margin-left:50px;color:red;display: none;"></span>');

        if(!$id){
        $mform->addRule('badgegroupid', get_string('badgegroupidmissing', 'block_gamification'), 'required', null, 'client');
        $mform->addRule('shortname', get_string('badgeshortnamemissing', 'block_gamification'), 'required', null, 'client');
        $mform->addGroupRule('radioar', get_string('pointsmissing', 'block_gamification'), 'required', null, 'client');
        }
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit','block_gamification'),array('id' => 'badgesubmitbutton'));
        
        $buttonarray[] = $mform->createElement('cancel','block_gamification');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
        // $this->add_action_buttons();
    }
    // function validation($data, $files) {
    //     exit;
    //     global $CFG, $DB;
    //     $errors = array();
    //     echo 'hello';
    //     print_object($data);exit;
        
    //     // if(isset($data[]))
    //     if ($data->type == 'points') {

    //     } else {

    //     }
    //     return $errors;
    // }
}
// class events_form extends moodleform {
//     public function definition(){
//         $mform = $this->_form;
//         $pid = $this->_customdata['pid'];
//         $mform->setDisableShortforms(true);
//         $mform->addElement('hidden', 'pid',$pid);
//         $mform->addElement('text','event_name',get_string('eventname','block_gamification'));
//         $mform->addElement('text','shortname',get_string('shortname','block_gamification'));
//         $mform->addElement('checkbox', 'status',get_string('active','block_gamification'));
//         $buttonarray=array();
//         $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit','block_gamification'));
//         $mform->addGroup($buttonarray, 'action', '', ' ', false);
//     }
// }