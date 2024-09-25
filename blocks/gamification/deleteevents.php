<?php 
require_once(dirname(__FILE__).'/../../config.php');
// require_once('locallib.php');
$id=required_param('id',  PARAM_INT);
$courseid =required_param('cid',  PARAM_INT);
global $DB;
$DB->delete_records('block_gm_events',  array('id'=>$id));
redirect(new moodle_url('/blocks/gamification/index.php/leaderboard/'.$courseid));