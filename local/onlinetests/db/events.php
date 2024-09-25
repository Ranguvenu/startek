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
 * @subpackage local_onlinetest
 */
//// List of observers.
$observers = array(

    array(
        'eventname'   => '\mod_quiz\event\attempt_submitted',
        'callback'    => 'local_onlinetests_observer::attempt_submitted',
    ),
    array(
    	'eventname'   => '\mod_quiz\event\course_module_viewed',
        'callback'    => 'local_onlinetests_observer::onlinetest_attempt_started',
    ),

);
