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


//// List of observers.
$observers = array(
     array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'local_certificates_observer::issue_course_certificate',
    ), 
    array(
        'eventname'   => '\local_classroom\event\classroom_user_completed',
        'callback'    => 'local_certificates_observer::issue_classroom_certificate',
    ),
    array(
        'eventname'   => '\local_learningplan\event\learningplan_user_completed',
        'callback'    => 'local_certificates_observer::issue_learningplan_certificate',
    ),
      array(
        'eventname'   => '\local_program\event\program_user_completed',
        'callback'    => 'local_certificates_observer::issue_program_certificate',
    ),  
      array(
        'eventname'   => '\local_onlinetests\event\onlinetest_completed',
        'callback'    => 'local_certificates_observer::issue_onlinetest_certificate',
    ),
);
