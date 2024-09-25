<?php
namespace block_gamification\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/itemspertime.php');
require_once(__DIR__ . '/duration.php');
require_once($CFG->libdir . '/outputcomponents.php');
use block_gamification\local\leaderboard\course_world_config;
use moodleform;
use html_writer;
use moodle_url;
class leaderboard extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG,$DB,$OUTPUT,$COURSE,$PAGE,$USER;
        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $mform->addelement('hidden', 'leaderboard_context', 'site');
        $mform->setType('leaderboard_context', PARAM_RAW);
        $mform->addElement('hidden', 'leaderboard_time' ,'1');
        $mform->setType('leaderboard_time', PARAM_INT);
        // $mform->addElement('header', 'leaderheader',get_string('settings','block_gamification'));
        // $context = $DB->get_field('config','value',array('name'=>'leaderboard_context'));
        $disable = "";
        if(is_siteadmin()){
            $costcenters = $DB->get_records_menu('local_costcenter', array('parentid' => 0), '', 'id,fullname');
            $mform->addElement('select', 'costcenter', get_string('organization','block_gamification'),$costcenters,array('id'=>'costcenter'));
            $mform->setType('costcenter',PARAM_INT);    
            $mform->addRule('costcenter',  get_string('costcenternamemissing','block_gamification'), 'required', null, 'client');
        }else{
            $mform->addElement('hidden',  'costcenter',  $USER->open_costcenterid);
            $mform->setType('costcenter', PARAM_INT);
        }
        // if($context == 'course'){
        //     $disable = "disabled";
        // }
        // $radioarray=array();
        // $radioarray[] = $mform->createElement('radio', 'leaderboard_context', '', 'Custom Points &nbsp ', 'site',array('onclick'=>'enableform();'));
        // $radioarray[] = $mform->createElement('radio', 'leaderboard_context', '', 'Course Sum' , 'course' ,array('onclick'=>'disableform();'));
        // $mform->addGroup($radioarray, 'radioar', get_string('leaderboardlevel','block_gamification'), array(' '), false);
        // $mform->addHelpButton('radioar', 'leaderboard_context','block_gamification');
        // $mform->setDefault('leaderboard_context',  $context);
        // $array =array(
        //     '1' => "Weekly", 
        //     '2'  => "Monthly",
        //     '3'  => "Quarterly",
        //     '4'  => "Halfyearly",
        //     '5' => "Yearly"
        // );
        // $value = $DB->get_field('config','value',array('name' => 'leaderboard_time'));
        // $mform->addElement('select', 'leaderboard_time','Duration',$array);
        // $mform->setDefault('leaderboard_time',$value);
        // $mform->addHelpButton('leaderboard_time', 'duration','block_gamification');
        // $addevents =  '<input id = "on-off-switch" class = "onoffbutton pointscheck"   type="checkbox" checked >';
        $addevents = ' ';
        $data = $DB->get_records_select('block_gm_events', "shortname NOT LIKE 'learningplan_completions' AND shortname NOT LIKE 'program_completions' ");
        $mform->addElement('header', 'pointsheader', get_string('pointsrewarded','block_gamification').$addevents);
        foreach($data as $event){
            // $active = $DB->get_field('block_gm_points','active',array('eventid'=>$event->id));
            $costcenterinfo = $DB->get_field('block_gm_points', 'costcentercontent', array('eventid'=>$event->id));
            $costcenterinfo = json_decode($costcenterinfo);
            $costcenterinfo =(array)$costcenterinfo;
            if(is_siteadmin()){
                foreach($costcenterinfo as $key => $value){
                    if($key == 1){
                        $costcenterdata = $value;
                    }else{
                        continue;
                    }    
                }
            }else{
                foreach($costcenterinfo as $key => $value){
                    if($key == $USER->open_costcenterid){
                        $costcenterdata = $value;
                    }else{
                        continue;
                    }    
                }
            }
            if($costcenterdata){
                $costcenterdata = json_decode($costcenterdata);
                $active = $costcenterdata->active;
                $pointsalloted = $costcenterdata->points;
            }
            // $pointsalloted = $DB->get_field('block_gm_points','points',array('eventid'=>$event->id));
            $array= array();
            $array[]=$mform->createElement('checkbox', 'event','','',array('class' => 'disable',$disable =>true));
            $array[]=$mform->createElement('text', 'eventname','',array('size'=>'4','class' => 'disable',$disable =>true)); 
            $array[]=$mform->createElement('hidden', 'id', $event->id);
            $eventname = strtolower($event->event_name);
            $mform->addGroup($array, 'events'.$event->id,'', array(' &nbsp','Points for '.$eventname));
            $mform->setDefault('events'.$event->id.'[event]',$active);
            $mform->setType('events'.$event->id.'[event]',PARAM_RAW);
            $mform->setDefault('events'.$event->id.'[eventname]',$pointsalloted);
            $mform->setType('events'.$event->id.'[eventname]',PARAM_INT);
            $mform->setType('events'.$event->id.'[id]',PARAM_INT);
        }
        $checked = '';
        $activatedbadges = $DB->count_records('block_gm_events', array('badgeactive' => 1));
        if($activatedbadges){
            $checked = 'checked';
        }
        $viewbadges =  '<input id = "on-off-switch1", class = "onoffbutton"  type="checkbox" name = "badges" '.$checked.'  >';
        $mform->addElement('header', 'badgeheader', get_string('badges','block_gamification').$viewbadges);
        
        $badge = $DB->get_records('block_gm_events');
        foreach($badge as $badges){
            $points = $DB->get_records('block_gm_badges',array('badgegroupid'=>$badges->id));

            $costcenterinfo = $DB->get_field('block_gm_points', 'costcentercontent', array('eventid'=>$badges->id));
            $costcenterinfo = json_decode($costcenterinfo);
            $costcenterinfo =(array)$costcenterinfo;
            if(is_siteadmin()){
                foreach($costcenterinfo as $key => $value){
                    if($key == 1){
                        $costcenterdata = $value;
                    }else{
                        continue;
                    }    
                }
            }else{
                foreach($costcenterinfo as $key => $value){
                    if($key == $USER->open_costcenterid){
                        $costcenterdata = $value;
                    }else{
                        continue;
                    }    
                }
            }
            if($costcenterdata){
                $costcenterdata = json_decode($costcenterdata);
                // print_object($costcenterdata);
                $badgeactive = $costcenterdata->badgeactive ? $costcenterdata->badgeactive : 0;
            }
            
            // print_object($badgeactive);

            $shortname = ucfirst(str_replace('_',' ',$badges->shortname));
            $options = ' ( ';
            foreach($points as $point){
                $options .= $point->points.',';
            }
            if(strlen($options)>3){
            $options .=' For '.$shortname.' )';
            }
            else{
                $options = get_string('nobadgemessage', 'block_gamification');
            }
            $group = array();
            $group[] = $mform->createElement('hidden','badgegroupid',$badges->id);
            $group[] = $mform->createElement('checkbox', 'badgegroup','','For '.$badges->event_name.$options,$disable);
            $mform->addGroup($group,'group'.$badges->id);
            $mform->setDefault('group'.$badges->id.'[badgegroup]',$badgeactive);
            $mform->setType('group'.$badges->id.'[badgegroup]',PARAM_RAW);
            $mform->setType('group'.$badges->id.'[badgegroupid]',PARAM_INT);
        }
        $mform->addElement('header','noheader',' ');


        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit','block_gamification'));
        $buttonarray[] = $mform->createElement('cancel','block_gamification');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }
} 
