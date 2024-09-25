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
namespace local_users\output;

use renderable;
use renderer_base;
use stdClass;
use templatable;
use context_system;

class form_status implements renderable, templatable {
    /**
     * [__construct description]
     * @method __construct
     */
    public function __construct($formstatus) {
        $this->context = (new \local_users\lib\accesslib())::get_module_context();
        $this->plugintype = 'local';
        $this->plugin_name = 'users';
        $this->formstatus = $formstatus;
    }
    /**
     * [export_for_template description]
     * @method export_for_template
     * @param  renderer_base       $output [description]
     * @return [type]                      [description]
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->contextid = $this->context->id;
        $data->formstatus = $this->formstatus;
        return $data;
    }
}
