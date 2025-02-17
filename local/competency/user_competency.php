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
 * @subpackage local_competency
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
\core_competency\api::require_enabled();

$uc = \core_competency\api::get_user_competency_by_id($id);
$params = array('id' => $id);
$url = new moodle_url('/local/competency/user_competency.php', $params);

$user = core_user::get_user($uc->get('userid'));
if (!$user || !core_user::is_real_user($user->id)) {
    throw new moodle_exception('invaliduser', 'error');
}
$iscurrentuser = ($USER->id == $user->id);

$competency = $uc->get_competency();
$compexporter = new \core_competency\external\competency_exporter($competency, array('context' => $competency->get_context()));

$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->navigation->override_active_url(new moodle_url('/admin/tool/lp/plans.php', array('userid' => $uc->get('userid'))));
$PAGE->set_context($uc->get_context());
if (!$iscurrentuser) {
    $PAGE->navigation->extend_for_user($user);
    $PAGE->navigation->set_userid_for_parent_checks($user->id);
}
$output = $PAGE->get_renderer('local_competency');
$compdata = $compexporter->export($output);
$PAGE->navbar->add($compdata->shortname, $url);
$PAGE->set_title($compdata->shortname);
$PAGE->set_heading($compdata->shortname);

echo $output->header();
$page = new \local_competency\output\user_competency_summary($uc);
echo $output->render($page);
// Trigger viewed event.
\core_competency\api::user_competency_viewed($uc);

echo $output->footer();
