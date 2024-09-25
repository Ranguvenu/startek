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

require_once($CFG->dirroot.'/local/evaluation/item/evaluation_item_form_class.php');

class evaluation_numeric_form extends evaluation_item_form {
    protected $type = "numeric";

    public function definition() {

        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string($this->type, 'local_evaluation'));
        $mform->addElement('advcheckbox', 'required', get_string('required', 'local_evaluation'), '' , null , array(0, 1));

        $mform->addElement('text',
                            'name',
                            get_string('item_name', 'local_evaluation'),
                            array('size'=>EVALUATION_ITEM_NAME_TEXTBOX_SIZE, 'maxlength'=>255));
         $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('text',
                            'label',
                            get_string('item_label', 'local_evaluation'),
                            array('size'=>EVALUATION_ITEM_LABEL_TEXTBOX_SIZE, 'maxlength'=>255));

        $mform->addElement('text',
                            'rangefrom',
                            get_string('numeric_range_from', 'local_evaluation'),
                            array('size'=>10, 'maxlength'=>10));
        $mform->setType('rangefrom', PARAM_RAW);

        $mform->addElement('text',
                            'rangeto',
                            get_string('numeric_range_to', 'local_evaluation'),
                            array('size'=>10, 'maxlength'=>10));
        $mform->setType('rangeto', PARAM_RAW);

        parent::definition();
        $this->set_data($item);

    }

    public function get_data() {
        if (!$item = parent::get_data()) {
            return false;
        }

        $num1 = unformat_float($item->rangefrom, true);
        if ($num1 === false || $num1 === null) {
            $num1 = '-';
        }

        $num2 = unformat_float($item->rangeto, true);
        if ($num2 === false || $num2 === null) {
            $num2 = '-';
        }

        if ($num1 === '-' OR $num2 === '-') {
            $item->presentation = $num1 . '|'. $num2;
            return $item;
        }

        if ($num1 > $num2) {
            $item->presentation =  $num2 . '|'. $num1;
        } else {
            $item->presentation = $num1 . '|'. $num2;
        }
        return $item;
    }

}
