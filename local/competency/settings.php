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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_competency', get_string('pluginname', 'local_competency'));
   

    $ADMIN->add('localplugins', $settings);

    
    $menu = array('BASIC'=>'Basic View', 'ADVANCED'=>'Advanced View');

    $name = new lang_string('competencyview', 'local_competency');
    $description = new lang_string('local_competencyview_desc', 'local_competency');
    $settings->add(new admin_setting_configselect('local_competency/competencyview',
                                                      $name,
                                                      $description,
                                                      'online_session_type_comments',
                                                      $menu));


}

