<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/local/evaluation/item/evaluation_item_class.php');

class evaluation_item_textfield extends evaluation_item_base {
    protected $type = "textfield";

    public function build_editform($item, $evaluation) {
        global $DB, $CFG;
        require_once('textfield_form.php');
        if(!$item){
            $item= (object) $item;
        }
        //get the lastposition number of the evaluation_items
        $position = $item->position;
        $lastposition = $DB->count_records('local_evaluation_item', array('evaluation'=>$evaluation->id));
        if ($position == -1) {
            $i_formselect_last = $lastposition + 1;
            $i_formselect_value = $lastposition + 1;
            $item->position = $lastposition + 1;
        } else {
            $i_formselect_last = $lastposition;
            $i_formselect_value = $item->position;
        }
        //the elements for position dropdownlist
        $positionlist = array_slice(range(0, $i_formselect_last), 1, $i_formselect_last, true);

        $item->presentation = empty($item->presentation) ? '' : $item->presentation;

        $size_and_length = explode('|', $item->presentation);

        if (isset($size_and_length[0]) AND $size_and_length[0] >= 5) {
            $itemsize = $size_and_length[0];
        } else {
            $itemsize = 30;
        }

        $itemlength = isset($size_and_length[1]) ? $size_and_length[1] : 255;

        $item->itemsize = $itemsize;
        $item->itemmaxlength = $itemlength;

        //all items for dependitem
        $evaluationitems = evaluation_get_depend_candidates_for_item($evaluation, $item);
        $commonparams = array('cmid'=>$evaluation->id,
                             'id' => isset($item->id) ? $item->id : null,
                             'typ' => $item->typ,
                             'items' => $evaluationitems,
                             'evaluation' => $evaluation->id);

        //build the form
        $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position);

        $this->item_form = new evaluation_textfield_form('edit_item.php', $customdata);
    }

    public function save_item() {
        global $DB;

        if (!$this->get_data()) {
            return false;
        }
        $item = $this->item;

        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = ''; //to clone this item
            $item->position++;
        }

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('local_evaluation_item', $item);
        } else {
            $DB->update_record('local_evaluation_item', $item);
        }

        return $DB->get_record('local_evaluation_item', array('id'=>$item->id));
    }


    /**
     * Helper function for collected data for exporting to excel
     *
     * @param stdClass $item the db-object from evaluation_item
     * @param int $groupid
     * @param int $courseid
     * @return stdClass
     */
    protected function get_analysed($item, $groupid = false, $courseid = false) {

        $analysed_val = new stdClass();
        $analysed_val->data = null;
        $analysed_val->name = $item->name;

        $values = evaluation_get_group_values($item, $groupid, $courseid);
        if ($values) {
            $data = array();
            foreach ($values as $value) {
                $data[] = str_replace("\n", '<br />', $value->value);
            }
            $analysed_val->data = $data;
        }
        return $analysed_val;
    }

    public function get_printval($item, $value) {

        if (!isset($value->value)) {
            return '';
        }
        return $value->value;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        $values = evaluation_get_group_values($item, $groupid, $courseid);
        $out = '';
        
        if ($values) {
            $out .= "<div class='col-12 col-md-6 pull-left'>";
            $out .= "<table class=\"analysis itemtype_{$item->typ}\">";
            $out .= '<thead><tr><th colspan="2" align="left">';
            $out .= $itemnr . ' ';
            if (strval($item->label) !== '') {
                $out .= '('. format_string($item->label).') ';
            }
            $out .= $this->get_display_name($item);
            $out .= '</th></tr></thead>';
            foreach ($values as $value) {
                $class = strlen(trim($value->value)) ? '' : ' class="isempty"';
                $out .= '<tr'.$class.'><td colspan="2" class="singlevalue">';
                $out .= str_replace("\n", '<br />', $value->value);
                $out .= '</td></tr>';
            }
            $out .= '</table>';
            $out .= '</div>';
        }
        return $out;
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $analysed_item = $this->get_analysed($item, $groupid, $courseid);

        $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, $item->name, $xls_formats->head2);
        $data = $analysed_item->data;
        if (is_array($data)) {
            $worksheet->write_string($row_offset, 2, htmlspecialchars_decode($data[0], ENT_QUOTES), $xls_formats->value_bold);
            $row_offset++;
            $sizeofdata = count($data);
            for ($i = 1; $i < $sizeofdata; $i++) {
                $worksheet->write_string($row_offset, 2, htmlspecialchars_decode($data[$i], ENT_QUOTES), $xls_formats->default);
                $row_offset++;
            }
        }
        $row_offset++;
        return $row_offset;
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param local_evaluation_complete_form $form
     */
    public function complete_form_element($item, $form) {
        $name = $this->get_display_name($item);
        $inputname = $item->typ . '_' . $item->id;
        list($size, $maxlength) = explode ("|", $item->presentation);
        $form->add_form_element($item,
                ['text', $inputname, $name, ['maxlength' => $maxlength, 'size' => $size]]);
        $form->set_element_type($inputname, PARAM_NOTAGS);

        $form->add_element_rule($inputname, get_string('maximumchars', '', $maxlength), 'maxlength', $maxlength, 'client');
    }

    /**
     * Converts the value from complete_form data to the string value that is stored in the db.
     * @param mixed $value element from local_evaluation_complete_form::get_data() with the name $item->typ.'_'.$item->id
     * @return string
     */
    public function create_value($value) {
        return s($value);
    }

    /**
     * Return the analysis data ready for external functions.
     *
     * @param stdClass $item     the item (question) information
     * @param int      $groupid  the group id to filter data (optional)
     * @param int      $courseid the course id (optional)
     * @return array an array of data with non scalar types json encoded
     * @since  Moodle 3.3
     */
    public function get_analysed_for_external($item, $groupid = false, $courseid = false) {

        $externaldata = array();
        $data = $this->get_analysed($item, $groupid, $courseid);

        if (is_array($data->data)) {
            return $data->data; // No need to json, scalar type.
        }
        return $externaldata;
    }
}
