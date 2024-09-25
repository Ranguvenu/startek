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
$local_challenge = new admin_category('local_challenge', new lang_string('pluginname', 'local_challenge'),false);
$ADMIN->add('localsettings', $local_challenge);
$settings = new admin_settingpage('local_challenge', get_string('pluginname', 'local_challenge'));
$ADMIN->add('localplugins', $settings);
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configselect('local_challenge_enable_challenge',
            get_string('enablegchallenge', 'local_challenge'),  get_string('enablegchallenge_description', 'local_challenge'),
            0, array(0 => get_string('no'), 1 => get_string('yes'))
    ));
}