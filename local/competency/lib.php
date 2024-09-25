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

defined('MOODLE_INTERNAL') || die;
/*
* Author Rizwana
* Displays a node in left side menu
* @return  [type] string  link for the leftmenu
*/
/*function local_competency_leftmenunode(){
    global $DB, $USER;
    $systemcontext = context_system::instance();
    $role = $DB->get_record_sql('SELECT DISTINCT(ra.userid), ra.id, r.shortname FROM {role} as r,{role_assignments} as ra WHERE ra.roleid = r.id and userid='.$USER->id.'');
    $competencynode = '';
    if($role && $role->shortname == 'employee' && !is_siteadmin()){ 
        $competencynode .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_browsecompetency', 'class'=>'pull-left user_nav_div browseforums'));
            $competency_url =  new moodle_url('/local/competency/viewcompetencies.php');  
            $competency = html_writer::link($competency_url, '<i class="fa fa-list" aria-hidden="true"></i><span class="user_navigation_link_text">'.get_string('left_menu_mycompetency','local_competency').'</span>',array('class'=>'user_navigation_link'));
            $competencynode .= $competency;
        $competencynode .= html_writer::end_tag('li');
    }

    return array('13' => $competencynode);
}
*/