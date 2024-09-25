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
 * @package Bizlms 
 * @subpackage local_certification
 */
defined('MOODLE_INTERNAL') || die;
$capabilities = array(
    'local/certification:createcertification' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_ALLOW,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:editcertification' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:deletecertification' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:managecertification' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:createsession' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:viewsession' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:editsession' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:deletesession' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:managesession' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:assigntrainer' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:managetrainer' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:addusers' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:removeusers' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:manageusers' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:viewusers' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:takesessionattendance' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_ALLOW,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:takemultisessionattendance' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:trainer_viewcertification' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:view_allcertificationtab' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:view_newcertificationtab' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:view_activecertificationtab' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:view_holdcertificationtab' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:view_cancelledcertificationtab' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:view_completedcertificationtab' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:createfeedback' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:viewfeedback' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:editfeedback' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
          'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:deletefeedback' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:managefeedback' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:createcourse' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:viewcourse' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:editcourse' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:deletecourse' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:managecourse' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:publish' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:cancel' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:release_hold' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:hold' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:complete' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:manage_owndepartments' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:manage_multiorganizations' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'coursecreator' => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'          => CAP_PREVENT,
            'user'        => CAP_PREVENT,
            'student'      => CAP_PREVENT,
            'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:certificationcompletion' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
    'local/certification:certificationdesign' => array(
       'riskbitmask' => RISK_SPAM | RISK_XSS,
       'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
           'coursecreator' => CAP_PREVENT,
           'teacher'        => CAP_PREVENT,
           'editingteacher' => CAP_PREVENT,
           'manager'          => CAP_ALLOW,
           'user'        => CAP_PREVENT,
           'student'      => CAP_PREVENT,
           'guest' => CAP_PREVENT
        ),
    ),
);
