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
 * @package Bizlms 
 * @subpackage local_certification
 */
namespace local_certification;
class includes{
	public function get_temp_classes_summary_files(){
    		global $OUTPUT;
			$url = $OUTPUT->image_url('classviewnew', 'local_certification');
        return $url;
    }
    public function get_certification_summary_file($certification){
    	global $DB;
        $certificationlogourl = false;
        if ($certification->certificationlogo > 0) {
            $sql = "SELECT * FROM {files} WHERE itemid = :logoid AND filename != '.' AND filearea ='certificationlogo' AND component='local_certification' ORDER BY id DESC ";
            $certificationlogorecord = $DB->get_record_sql($sql, array('logoid' => $certification->certificationlogo));
            
        }
        if (!empty($certificationlogorecord)) {  
            $certificationlogourl = \moodle_url::make_pluginfile_url($certificationlogorecord->contextid, $certificationlogorecord->component,
                $certificationlogorecord->filearea, $certificationlogorecord->itemid, $certificationlogorecord->filepath,
                                $certificationlogorecord->filename);
    
        }else{
            $certificationlogourl = $this->get_temp_classes_summary_files();
        }
        return $certificationlogourl;
    }
}