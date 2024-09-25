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
 * @subpackage local_costcenter
 */

require('../../config.php');
//$templateid = required_param('templateid', PARAM_INT);

//$issues = $DB->get_records('tool_certificate_issues',array('templateid'=>$templateid ));

$issues = $DB->get_records('tool_certificate_issues');


foreach($issues as $issue){


    // Make sure the user has the required capabilities.
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
