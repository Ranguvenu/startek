<?php
require_once('additionalforms.php');
require_once('locallib.php');
global $PAGE,$USER;
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/badgeformchanges.js'));
$PAGE->requires->js(new moodle_url('/blocks/gamification/js/select2.full.js'), true);
$PAGE->requires->css(new moodle_url('/blocks/gamification/css/select2.min.css'));

$id = optional_param('id' , 0 , PARAM_INT);
$PAGE->set_url('/blocks/gamification/addbadges.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
if($id) {
	$PAGE->set_title(get_string('editbadges', 'block_gamification'));
	$PAGE->set_heading(get_string('editbadges', 'block_gamification'));
	$PAGE->navbar->add( get_string('editbadges', 'block_gamification'), new moodle_url('/blocks/gamification/addbadges.php?id='.$id));
}
else {
	$PAGE->set_title(get_string('addbadges', 'block_gamification'));
	$PAGE->set_heading(get_string('addbadges', 'block_gamification'));
	$PAGE->navbar->add( get_string('addbadges', 'block_gamification'), new moodle_url('/blocks/gamification/addbadges.php'));
}
$PAGE->navbar->ignore_active();
	echo $OUTPUT->header();
	$pid = 1;
	$badgeurl= new moodle_url('/blocks/gamification/index.php/visuals/'.$pid);
    $badgesetting = html_writer::link($badgeurl,'Back',array('id'=>'backbutton'));
    echo $OUTPUT->heading($badgesetting,4);
    if ($id) {
        $record = $DB->get_record('block_gm_badges', array('id'=>$id));
        $badgegroupid = $record->badgegroupid;
        // $shortname = $record->shortname;
        $type = $record->type;
    } else {
        $badgegroupid = NULL;
        // $shortname = NULL;
        $type = NULL;
    }
// print_object($shortname);
	$mform = new badge_form('',array('pid'=>$pid, 'id' => $id, 'type' => $type, 'badgegroupid' => $badgegroupid));
	if($id) {
        $retrieved = update_badge_data($id);
        $mform->set_data($retrieved);
    }
    if($mform->is_cancelled()) {
        redirect(new moodle_url('/blocks/gamification/index.php/visuals/'.$pid));
	} elseif($data= data_submitted()) {//$mform->get_data()
            // print_object($data);exit;
// $data->courses = implode(',', $data->courses);
// print_object($data->courses);
            // exit;
        $out = insert_badge_data($data);
        redirect(new moodle_url('/blocks/gamification/index.php/visuals/'.$pid));
    } else{
        $mform->display();
    }
    // print_object(data_submitted());exit;

    echo $OUTPUT->footer();