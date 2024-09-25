<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
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

define('EVALUATION_RADIORATED_ADJUST_SEP', '<<<<<');

define('EVALUATION_MULTICHOICERATED_MAXCOUNT', 10); //count of possible items
define('EVALUATION_MULTICHOICERATED_VALUE_SEP', '####');
define('EVALUATION_MULTICHOICERATED_VALUE_SEP2', '/');
define('EVALUATION_MULTICHOICERATED_TYPE_SEP', '>>>>>');
define('EVALUATION_MULTICHOICERATED_LINE_SEP', '|');
define('EVALUATION_MULTICHOICERATED_ADJUST_SEP', '<<<<<');
define('EVALUATION_MULTICHOICERATED_IGNOREEMPTY', 'i');
define('EVALUATION_MULTICHOICERATED_HIDENOSELECT', 'h');

class evaluation_item_multichoicerated extends evaluation_item_base {
    protected $type = "multichoicerated";

    public function build_editform($item, $evaluation) {
        global $DB, $CFG;
        require_once('multichoicerated_form.php');
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
        $info = $this->get_info($item);

        $item->ignoreempty = $this->ignoreempty($item);
        $item->hidenoselect = $this->hidenoselect($item);

        //all items for dependitem
        $evaluationitems = evaluation_get_depend_candidates_for_item($evaluation, $item);
        $commonparams = array('cmid'=>$evaluation->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$evaluationitems,
                             'evaluation'=>$evaluation->id);

        //build the form
        $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position,
                            'info' => $info);

        $this->item_form = new evaluation_multichoicerated_form('edit_item.php', $customdata);
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

        $this->set_ignoreempty($item, $item->ignoreempty);
        $this->set_hidenoselect($item, $item->hidenoselect);

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('local_evaluation_item', $item);
        } else {
            $DB->update_record('local_evaluation_item', $item);
        }

        return $DB->get_record('local_evaluation_item', array('id'=>$item->id));
    }


    /**
     * Helper function for collected data, both for analysis page and export to excel
     *
     * @param stdClass $item the db-object from evaluation_item
     * @param int $groupid
     * @param int $courseid
     * @return array
     */
    protected function get_analysed($item, $groupid = false, $courseid = false) {
        $analysed_item = array();
        $analysed_item[] = $item->typ;
        $analysed_item[] = $item->name;

        // extract the possible answers
        $info = $this->get_info($item);
        $lines = null;
        $lines = explode (EVALUATION_MULTICHOICERATED_LINE_SEP, $info->presentation);
        if (!is_array($lines)) {
            return null;
        }

        //get the values
        $values = evaluation_get_group_values($item, $groupid, $courseid, $this->ignoreempty($item));
        if (!$values) {
            return null;
        }
        // loop over the values ​​and over the answer possibilities
        $analysed_answer = array();
        $sizeoflines = count($lines);
        for ($i = 1; $i <= $sizeoflines; $i++) {
            $item_values = explode(EVALUATION_MULTICHOICERATED_VALUE_SEP, $lines[$i-1]);
            $ans = new stdClass();
            $ans->answertext = $item_values[1];
            $avg = 0.0;
            $anscount = 0;
            foreach ($values as $value) {
                // is the answer equal to the index of answers + 1?
                if ($value->value == $i) {
                    $avg += $item_values[0]; // sum up all values ​​first
                    $anscount++;
                }
            }
            $ans->answercount = $anscount;
            $ans->avg = doubleval($avg) / doubleval(count($values));
            $ans->value = $item_values[0];
            $ans->quotient = $ans->answercount / count($values);
            $analysed_answer[] = $ans;
        }
        $analysed_item[] = $analysed_answer;
        return $analysed_item;
    }

    public function get_printval($item, $value) {
        $printval = '';

        if (!isset($value->value)) {
            return $printval;
        }

        $info = $this->get_info($item);

        $presentation = explode (EVALUATION_MULTICHOICERATED_LINE_SEP, $info->presentation);
        $index = 1;
        foreach ($presentation as $pres) {
            if ($value->value == $index) {
                $item_label = explode(EVALUATION_MULTICHOICERATED_VALUE_SEP, $pres);
                $printval = format_string($item_label[1]);
                break;
            }
            $index++;
        }
        return $printval;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT, $PAGE;
        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        $out = '';
        
        if ($analysed_item) {
            $out .= "<div class='col-12 col-md-6 pull-left'>";
            $out .= "<table class=\"analysis itemtype_{$item->typ}\">";
            $out .= '<tr><th colspan="2" align="left">';
            $out .= $itemnr . ' ';
            if (strval($item->label) !== '') {
                $out .= '('. format_string($item->label).') ';
            }
            $out .= format_string($analysed_item[1]);
            $out .= '</th></tr>';
            $out .= '</table>';
            $analysed_vals = $analysed_item[2];
            $avg = 0.0;
            $count = 0;
            $data = [];
            $tabledata = array();
            $line = array();
            
            foreach ($analysed_vals as $val) {
                
                $avg += $val->avg;
                $quotient = format_float($val->quotient * 100, 2);
                $answertext = '('.$val->value.') ' . format_text(trim($val->answertext), FORMAT_HTML,
                        array('noclean' => true, 'para' => false));
                //$answertext = '';

                if ($val->quotient > 0) {
                    $strquotient =  ''.$quotient.' %';
                } else {
                    $strquotient = '';
                }

                $data['labels'][$count] = $answertext;
                $data['series'][$count] = $val->answercount;
                $data['series_labels'][$count] = $val->answercount . $strquotient;
                $count++;
                // create data for custom table
                $line[] = '<span class="pr-15 pl-15 anaresp_lable">'.$answertext.'</span>'.'<span class="pull-right"><span class="pr-5"><b>'.$val->answercount . '</b></span> users(<span class="quotient"><b>'. $strquotient.'</span></b>)</span>';
                
            }
            $tabledata[] = $line;
            $chart = new \core\chart_bar();
            $chart->set_horizontal(false);
            $series = new \core\chart_series(format_string(get_string("responses", "local_evaluation")), $data['series']);
            $series->set_labels($data['series_labels']);
            $chart->add_series($series);
            $chart->set_labels($data['labels']);

            $avg = format_float($avg, 2);
            $average[] = '<div class="col-md-6 text-xs-center"><span><img src="'.$OUTPUT->image_url('avg', 'local_evaluation').'" class="respavg_img"/></span><span class="resp_avg">'.get_string('average', 'local_evaluation').'</span><span class="respavg_count"><b>'.$avg.'</b></span></div>';
            $table = new html_table();
            $tabledata[] = $average;
            $table->data = $tabledata;
            $out .= html_writer::table($table);
            $out .= '</div>';
        }
        return $out;
    }
    public function custom_print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT, $PAGE;
        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        $out = '';
        
        if ($analysed_item) {
            
            $out .= "<table class=\"analysis itemtype_{$item->typ}\">";
            $out .= '<tr><th colspan="2" align="left">';
            $out .= $itemnr . ' ';
            if (strval($item->label) !== '') {
                $out .= '('. format_string($item->label).') ';
            }
            $out .= format_string($analysed_item[1]);
            $out .= '</th></tr>';
            $out .= '</table>';
            $analysed_vals = $analysed_item[2];
            $avg = 0.0;
            $count = 0;
            $data = [];
           
            foreach ($analysed_vals as $val) {              
                $avg += $val->avg;
                $quotient = format_float($val->quotient * 100, 2);
                $answertext = '('.$val->value.') ' . format_text(trim($val->answertext), FORMAT_HTML,
                        array('noclean' => true, 'para' => false));
                //$answertext = '';
        
                if ($val->quotient > 0) {
                    $strquotient = ' ('.$quotient.' %)';
                } else {
                    $strquotient = '';
                }
        
                $data['labels'][$count] = $answertext;
                $data['series'][$count] = $val->answercount;
                $data['series_labels'][$count] = $val->answercount . $strquotient;
                $count++;                
            }
            $chart = new \core\chart_bar();
            $chart->set_horizontal(false);
            $series = new \core\chart_series(format_string(get_string("responses", "local_evaluation")), $data['series']);
            $series->set_labels($data['series_labels']);
            $chart->add_series($series);
            $chart->set_labels($data['labels']);
            echo $OUTPUT->render($chart);
        }
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $analysed_item = $this->get_analysed($item, $groupid, $courseid);

        $data = $analysed_item[2];

        //write the item
        $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, $analysed_item[1], $xls_formats->head2);
        if (is_array($data)) {
            $avg = 0.0;
            $sizeofdata = count($data);
            for ($i = 0; $i < $sizeofdata; $i++) {
                $analysed_data = $data[$i];

                $worksheet->write_string($row_offset,
                                $i + 2,
                                trim($analysed_data->answertext).' ('.$analysed_data->value.')',
                                $xls_formats->value_bold);

                $worksheet->write_number($row_offset + 1,
                                $i + 2,
                                $analysed_data->answercount,
                                $xls_formats->default);

                $avg += $analysed_data->avg;
            }
            //mittelwert anzeigen
            $worksheet->write_string($row_offset,
                                count($data) + 2,
                                get_string('average', 'local_evaluation'),
                                $xls_formats->value_bold);

            $worksheet->write_number($row_offset + 1,
                                count($data) + 2,
                                $avg,
                                $xls_formats->value_bold);
        }
        $row_offset +=2;
        return $row_offset;
    }

    /**
     * Options for the multichoice element
     * @param stdClass $item
     * @return array
     */
    protected function get_options($item) {
        $info = $this->get_info($item);
        $lines = explode(EVALUATION_MULTICHOICERATED_LINE_SEP, $info->presentation);
        $options = array();
        foreach ($lines as $idx => $line) {
            list($weight, $optiontext) = explode(EVALUATION_MULTICHOICERATED_VALUE_SEP, $line);
            $optiontextstring = strlen($optiontext) > 19 ? substr($optiontext, 0, 19)."..." : $optiontext;
            $options[$idx + 1] = format_text("<span class=\"weight\">($weight) </span><span title=\"$optiontext\">$optiontextstring</span>",FORMAT_HTML, array('noclean' => true, 'para' => false));
        }
        if ($info->subtype === 'r' && !$this->hidenoselect($item)) {
            $options = array(0 => get_string('not_selected', 'local_evaluation')) + $options;
        }

        return $options;
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param mod_evaluation_complete_form $form
     */
    public function complete_form_element($item, $form) {
        $info = $this->get_info($item);
        $name = $this->get_display_name($item);
        $class = 'multichoicerated-' . $info->subtype;
        $inputname = $item->typ . '_' . $item->id;
        $options = $this->get_options($item);
        if ($info->subtype === 'd' || $form->is_frozen()) {
            $el = $form->add_form_element($item,
                    ['select', $inputname, $name, array('' => '') + $options, array('class' => $class)]);
        } else {
            $objs = array();
            if (!array_key_exists(0, $options)) {
                // Always add '0' as hidden element, otherwise form submit data may not have this element.
                $objs[] = ['hidden', $inputname];
            }
            foreach ($options as $idx => $label) {
                $objs[] = ['radio', $inputname, '', $label, $idx];
            }
            // Span to hold the element id. The id is used for drag and drop reordering.
            $objs[] = ['static', '', '', html_writer::span('', '', ['id' => 'evaluation_item_' . $item->id])];
            $separator = $info->horizontal ? ' ' : '<br>';
            $class .= ' multichoicerated-' . ($info->horizontal ? 'horizontal' : 'vertical');
            $el = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
            $form->set_element_type($inputname, PARAM_INT);

            // Set previously input values.
            $form->set_element_default($inputname, $form->get_item_value($item));

            // Process "required" rule.
            if ($item->required) {
                $form->add_validation_rule(function($values, $files) use ($item) {
                    $inputname = $item->typ . '_' . $item->id;
                    return empty($values[$inputname]) ? array('group_' . $inputname => get_string('required')) : true;
                });
            }
        }
    }

    /**
     * Compares the dbvalue with the dependvalue
     *
     * @param stdClass $item
     * @param string $dbvalue is the value input by user in the format as it is stored in the db
     * @param string $dependvalue is the value that it needs to be compared against
     */
    public function compare_value($item, $dbvalue, $dependvalue) {

        if (is_array($dbvalue)) {
            $dbvalues = $dbvalue;
        } else {
            $dbvalues = explode(EVALUATION_MULTICHOICERATED_LINE_SEP, $dbvalue);
        }

        $info = $this->get_info($item);
        $presentation = explode (EVALUATION_MULTICHOICERATED_LINE_SEP, $info->presentation);
        $index = 1;
        foreach ($presentation as $pres) {
            $presvalues = explode(EVALUATION_MULTICHOICERATED_VALUE_SEP, $pres);

            foreach ($dbvalues as $dbval) {
                if ($dbval == $index AND trim($presvalues[1]) == $dependvalue) {
                    return true;
                }
            }
            $index++;
        }
        return false;
    }

    public function get_info($item) {
        $presentation = empty($item->presentation) ? '' : $item->presentation;

        $info = new stdClass();
        //check the subtype of the multichoice
        //it can be check(c), radio(r) or dropdown(d)
        $info->subtype = '';
        $info->presentation = '';
        $info->horizontal = false;

        $parts = explode(EVALUATION_MULTICHOICERATED_TYPE_SEP, $item->presentation);
        @list($info->subtype, $info->presentation) = $parts;

        if (!isset($info->subtype)) {
            $info->subtype = 'r';
        }

        if ($info->subtype != 'd') {
            $parts = explode(EVALUATION_MULTICHOICERATED_ADJUST_SEP, $info->presentation);
            @list($info->presentation, $info->horizontal) = $parts;

            if (isset($info->horizontal) AND $info->horizontal == 1) {
                $info->horizontal = true;
            } else {
                $info->horizontal = false;
            }
        }

        $info->values = $this->prepare_presentation_values_print($info->presentation,
                                                    EVALUATION_MULTICHOICERATED_VALUE_SEP,
                                                    EVALUATION_MULTICHOICERATED_VALUE_SEP2);
        return $info;
    
    }

    public function prepare_presentation_values($linesep1,
                                         $linesep2,
                                         $valuestring,
                                         $valuesep1,
                                         $valuesep2) {

        $lines = explode($linesep1, $valuestring);
        $newlines = array();
        foreach ($lines as $line) {
            $value = '';
            $text = '';
            if (strpos($line, $valuesep1) === false) {
                $value = 0;
                $text = $line;
            } else {
                @list($value, $text) = explode($valuesep1, $line, 2);
            }

            $value = intval($value);
            $newlines[] = $value.$valuesep2.$text;
        }
        $newlines = implode($linesep2, $newlines);
        return $newlines;
    }

    public function prepare_presentation_values_print($valuestring, $valuesep1, $valuesep2) {
        $valuestring = str_replace(array("\n","\r"), "", $valuestring);
        return $this->prepare_presentation_values(EVALUATION_MULTICHOICERATED_LINE_SEP,
                                                  "\n",
                                                  $valuestring,
                                                  $valuesep1,
                                                  $valuesep2);
    }

    public function prepare_presentation_values_save($valuestring, $valuesep1, $valuesep2) {
        $valuestring = str_replace("\r", "\n", $valuestring);
        $valuestring = str_replace("\n\n", "\n", $valuestring);
        return $this->prepare_presentation_values("\n",
                        EVALUATION_MULTICHOICERATED_LINE_SEP,
                        $valuestring,
                        $valuesep1,
                        $valuesep2);
    }

    public function set_ignoreempty($item, $ignoreempty=true) {
        $item->options = str_replace(EVALUATION_MULTICHOICERATED_IGNOREEMPTY, '', $item->options);
        if ($ignoreempty) {
            $item->options .= EVALUATION_MULTICHOICERATED_IGNOREEMPTY;
        }
    }

    public function ignoreempty($item) {
        if (strstr($item->options, EVALUATION_MULTICHOICERATED_IGNOREEMPTY)) {
            return true;
        }
        return false;
    }

    public function set_hidenoselect($item, $hidenoselect=true) {
        $item->options = str_replace(EVALUATION_MULTICHOICERATED_HIDENOSELECT, '', $item->options);
        if ($hidenoselect) {
            $item->options .= EVALUATION_MULTICHOICERATED_HIDENOSELECT;
        }
    }

    public function hidenoselect($item) {
        if (strstr($item->options, EVALUATION_MULTICHOICERATED_HIDENOSELECT)) {
            return true;
        }
        return false;
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

        if (!empty($data[2]) && is_array($data[2])) {
            foreach ($data[2] as $d) {
                $externaldata[] = json_encode($d);
            }
        }
        return $externaldata;
    }
}
