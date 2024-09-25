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
 * @subpackage local_skillrepository
 */
defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'local/skillrepository:view_skill' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW,
        //     'teacher' => CAP_ALLOW,
        //     'student' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:create_skill' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:delete_skill' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:update_skill' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:view_level' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW,
        //     'teacher' => CAP_ALLOW,
        //     'student' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:create_level' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:delete_level' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:update_level' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        //     'manager' => CAP_ALLOW,
        //     'editingteacher' => CAP_ALLOW
        // )
    ),
    'local/skillrepository:manage' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        // 'archetypes' => array(
        // )
    )
);
