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
 * TODO describe file regenrate_certificate
 *
 * @package    local_costcenter
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require '../../config.php';

require_login();
$templateid = required_param('templateid', PARAM_INT);
$url = new moodle_url('/local/costcenter/regenrate_certificate.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();
$issuecerts = $DB->get_records('tool_certificate_issues', ['templateid' => $templateid]);
foreach ($issuecerts as $issuecert) {
    regenerate_issue_file($issuecert->id);
}
function regenerate_issue_file($issueid)
{
    global $DB;
    $issue = $DB->get_record('tool_certificate_issues', ['id' => $issueid], '*', MUST_EXIST);
    // Make sure the user has the required capabilities.
    $context = \context_system::instance();
    $template = \tool_certificate\template::instance($issue->templateid);
    if (!$template->can_issue($issue->userid)) {
        throw new \required_capability_exception($template->get_context(), 'tool/certificate:issue', 'nopermissions', 'error');
    }

    // Regenerate the issue file.
    $template->create_issue_file($issue, true);
    // Update issue userfullname data.
    if ($user = $DB->get_record('user', ['id' => $issue->userid])) {
        $issuedata = @json_decode($issue->data, true);
        $issuedata['userfullname'] = fullname($user);
        $issue->data = json_encode($issuedata);
        $DB->update_record('tool_certificate_issues', $issue);
    }
}
echo $OUTPUT->footer();
