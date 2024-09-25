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
 * @package   Bizlms
 * @subpackage  suggested_modules
 * @author eabyas  <info@eabyas.in>
**/
class block_suggested_modules extends block_base{
	public function init() {
        $this->title = get_string('pluginname','block_suggested_modules');
    }
    public function get_content() {
    	global $OUTPUT, $PAGE;
    	if ($this->content !== null) {
            return $this->content;
        }
        if (is_siteadmin() || !has_capability('block/suggested_modules:view', \context_system::instance())){
            return;
        }
        // $PAGE->requires->js_call_amd('block_trending_modules/trending_modules', 'init');
        $PAGE->requires->js_call_amd('local_catalog/courseinfo', 'load', array());
        $this->content = new stdClass();

        $lib = new block_trending_modules\lib();
    	$args = new stdClass();
    	$args->limitfrom = 0;
		$args->limitnum = 3;
        $args->rateWidth = 12;
        $filtervalues = new stdClass();
        $module_tags = $lib->get_my_tags_info();
        $my_tags = [];
        foreach($module_tags AS $tags){
            $my_tags += $tags;
        }
        $filtervalues->module_tags = implode(',', $my_tags);
        $args->filtervalues = $filtervalues;
    	$data = $lib->user_trending_modules($args);
    	// $renderer = $PAGE->get_renderer('block_trending_modules');

    	$this->content->text = "<div class='pull-right text-right' id='suggested_module_search'>
            <label class='search_module_label'>".get_string('search','block_suggested_modules')." : </label>
            <input id='filter_trending_modules' type='text' name='search_module' data-module_tags='{$filtervalues->module_tags}' data-target='#suggested_modules_content' data-navigator='.block_suggested_modules_navigator'/>
            </div>";
        $total_modules = $lib->get_total_modules_count($args);
        $enableviewmore = $total_modules > 3 ? True : False;
        // $checkbox = $renderer->show_preference_setting_user();
        $viewmore_link = (new moodle_url('/blocks/suggested_modules/index.php'))->out();
    	$this->content->text .= $OUTPUT->render_from_template('block_suggested_modules/block_content', array('records'=> $data, 'enableviewmore' => $enableviewmore,'viewmore_link' => $viewmore_link, 'left_arrow_enable' => False, 'right_arrow_enable' => $enableviewmore,'total_modules' => $total_modules, 'tags' => $filtervalues->module_tags));

        $this->content->footer = '';
        return $this->content;
    }
}