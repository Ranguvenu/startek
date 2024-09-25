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
 */
defined('MOODLE_INTERNAL') || die;
$local_gamification = new admin_category('local_gamification', new lang_string('pluginname', 'local_gamification'),false);
$ADMIN->add('localsettings', $local_gamification);
$settings = new admin_settingpage('local_gamification', get_string('pluginname', 'local_gamification'));
$ADMIN->add('localplugins', $settings);
if ($ADMIN->fulltree) {
    $settings->add(new \local_gamification\gamification_settings('local_gamification_max_levels', 
    	get_string('maxlevels', 'local_gamification'), 
    	get_string('maxlevelsdesc', 'local_gamification'), 
    	10, 
    	PARAM_INT));
    $settings->add(new admin_setting_configselect('local_gamification_enable_gamification',
            get_string('enablegamification', 'local_gamification'),  get_string('enablegamification_description', 'local_gamification'),
            0, array(0 => get_string('no'), 1 => get_string('yes'))
    ));
}

$local_gamification = new admin_category('gamificationboards_marketplace', new lang_string('marketplace_settings', 'gamificationboards_marketplace'),false);
$ADMIN->add('localsettings', $local_gamification);
$settings = new admin_settingpage('gamificationboards_marketplace', get_string('marketplace_settings', 'gamificationboards_marketplace'));
$ADMIN->add('localplugins', $settings);
if ($ADMIN->fulltree) {
    // $settings->add(new \local_gamification\gamification_settings('local_gamification_max_levels', 
    // 	get_string('maxlevels', 'gamificationboards_marketplace'), 
    // 	get_string('maxlevelsdesc', 'gamificationboards_marketplace'), 
    // 	10, 
    // 	PARAM_INT));
	$values = array();
	for($i=1;$i<=30;$i++){
		$values[$i] = $i;
	}


    $settings->add(new admin_setting_configselect('gamificationboards_marketplace/days',
            get_string('reminderdays', 'gamificationboards_marketplace'),  get_string('marketplace_description', 'gamificationboards_marketplace'),
            7, $values
        ));
}