<?php 
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG,$PAGE,$OUTPUT;
require_once($CFG->dirroot . '/blocks/gamification/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/blocks/gamification/badges.php');
$PAGE->set_title(get_string('pluginname', 'block_gamification'));
$PAGE->set_pagelayout('admin');
//Header and the navigation bar
$PAGE->set_heading(get_string('userbadgepage', 'block_gamification'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add( get_string('pluginname', 'block_gamification'), new moodle_url('/blocks/gamification/badges.php'));
echo $OUTPUT->header();
 // $badge= new gamification_plugin();

$badges=block_gamification_badge_groups();
echo $badges;

echo $OUTPUT->footer();
