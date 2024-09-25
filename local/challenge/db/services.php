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
 * @package   local
 * @subpackage  challenge
 * @author eabyas  <info@eabyas.in>
**/
defined('MOODLE_INTERNAL') || die;
$functions = array(
	'local_challenge_post_challenge' => array(
        'classname'   => 'local_challenge_external',
        'methodname'  => 'post_challenge',
        'classpath'   => 'local/challenge/classes/external.php',
        'description' => 'Post Challenge',
        'type'        => 'write',
        'ajax' => true,
    ),
    'local_challenge_form_option_selector' => array(
    	'classname'   => 'local_challenge_external',
        'methodname'  => 'form_option_selector',
        'classpath'   => 'local/challenge/classes/external.php',
        'description' => 'Form option selector',
        'type'        => 'read',
        'ajax' => true,
    ),
    'local_challenge_challenges_view' => array(
        'classname'   => 'local_challenge_external',
        'methodname'  => 'challenges_view',
        'classpath'   => 'local/challenge/classes/external.php',
        'description' => 'Challenges view',
        'type'        => 'read',
        'ajax' => true,
    ),
    'local_challenge_alter_challenge_status' => array(
        'classname'   => 'local_challenge_external',
        'methodname'  => 'alter_challenge_status',
        'classpath'   => 'local/challenge/classes/external.php',
        'description' => 'Alter Challenge Status',
        'type'        => 'write',
        'ajax' => true,
    )
);