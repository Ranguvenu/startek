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
/**
 * Admin setting for gamification config.
 *
 * @package    local_gamification
 * @copyright  2019 <eabyas.in>
 */
namespace local_gamification;
defined('MOODLE_INTERNAL') || die();
class gamification_settings extends \admin_setting_configtext {
    public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_INT, $size=null) {
        $this->paramtype = $paramtype;
        if (!is_null($size)) {
            $this->size  = $size;
        } else {
            $this->size  = ($paramtype === PARAM_INT) ? 5 : 30;
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }
    public function validate($data) {
        $temp = $data;
        $temp = (int)$temp;
        if("$temp" === "$data"){
            if($temp<2){
                return get_string('maxlevels_value_error', 'local_gamification');
            }else{
                return true;
            }
        }else{
            return get_string('validateerror', 'admin');
        }
    }
}