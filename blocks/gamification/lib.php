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
 * Block gamification lib.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * File serving.
 *
 * @param stdClass $course The course object.
 * @param stdClass $bi Block instance record.
 * @param context $context The context object.
 * @param string $filearea The file area.
 * @param array $args List of arguments.
 * @param bool $forcedownload Whether or not to force the download of the file.
 * @param array $options Array of options.
 * @return void|false
 */
function block_gamification_pluginfile($course, $bi, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $fs = \block_gamification\di::get('file_server');
    if ($fs instanceof \block_gamification\local\file\block_file_server) {
        $fs->serve_block_file($course, $bi, $context, $filearea, $args, $forcedownload, $options);
    }
}

function block_gamification_extend_navigation_course($navigation, $course, $context) {
    $url = new moodle_url('/blocks/gamification/index.php/rules/'.$course->id);
    $name = get_string('gamificationsettings', 'block_gamification');
    $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
} 
// function block_gamification_leftmenunode(){

//     $gamificationnode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_browsegamifications', 'class'=>'pull-left user_nav_div browsegamifications'));
//     if(is_siteadmin()) {
//         $gamifications_url = new moodle_url('/blocks/gamification/index.php/levels/1');
//         $string = get_string('gamificationsettings','block_gamification');
//     } else {
//         $gamifications_url = new moodle_url('/blocks/gamification/dashboard.php');
//         $string = get_string('gamification_dashboard','block_gamification');
//     }
    
//     $gamification_icon = '<i class="fa fa-graduation-cap" aria-hidden="true"></i>';
//     $gamifications = html_writer::link($gamifications_url, $gamification_icon.'<span class="user_navigation_link_text">'.$string.'</span>',array('class'=>'user_navigation_link'));
//     $gamificationnode .= $gamifications;
//     $gamificationnode .= html_writer::end_tag('li');

//     return array('10' => $gamificationnode);
// }
