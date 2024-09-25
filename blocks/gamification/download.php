<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/blocks/gamification/export/export.php');
// require_once($CFG->dirroot.'/blocks/gamification/export/exp.php');

// require_once($CFG->dirroot.'/blocks/gamification/classes/form/filter.php');
global $PAGE,$USER;

require_login();
if(!is_siteadmin()) {
	print_error('You dont have permissions to access this page.');
}
$PAGE->set_url('/blocks/gamification/download.php');
$PAGE->set_pagelayout('leaderboard');
$PAGE->set_context(context_system::instance());
$heading = get_string('gamification_dashboard', 'block_gamification');
$PAGE->set_title($heading);

$PAGE->set_heading($heading);

// export_report();
$renderer = \block_gamification\di::get('renderer');
$filterset = ['overall', 'weekly', 'monthly'];
// echo $renderer->filters($filterset);
$form = new \block_gamification\form\filter();		
// $form = new \block_gamification\form\filter;

if($form->is_cancelled()) {
	redirect($PAGE->url);
}
if($data = $form->get_data()) {
	export_report($data);
} else {
	echo $OUTPUT->header();
	$form->display();
	echo $OUTPUT->footer();
}
