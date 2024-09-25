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
 * @subpackage block_suggested_modules
 */
class block_suggested_modules_renderer extends plugin_renderer_base {
	public function suggested_modules_content($filter = false){
		global $USER;

        $systemcontext = context_system::instance();

        $options = array('targetID' => 'suggested_modules','perPage' => 8, 'cardClass' => 'col-md-6 col-12', 'viewType' => 'table');
        
        $options['methodName'] = 'block_trending_modules_display_paginated';
        $options['templateName'] = 'block_trending_modules/trending_module_paginated'; 
        $options = json_encode($options);

        $dataoptions = json_encode(array());
        $trending_querylib = new \block_trending_modules\querylib();
        $module_tags = $trending_querylib->get_my_tags_info();
        $my_tags = [];
        foreach($module_tags AS $tags){
            $my_tags += $tags;
        }
        $module_tags = implode(',', $my_tags);
        $filterdata = json_encode(array('module_tags' => $module_tags));

        $context = [
                'targetID' => 'suggested_modules',
                'options' => $options,
                'dataoptions' => $dataoptions,
                'filterdata' => $filterdata
        ];

        if($filter){
            return  $context;
        }else{
            return  $this->render_from_template('block_suggested_modules/suggested_content', $context);
        }
	}

}