<?php
/**
 * This file is part of eAbyas
 *
 * Copyright eAbyas Info Solutons Pvt Ltd, India
 *
 * This courselister is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This courselister is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this courselister.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course lister block.
 *
 * @author eabyas  <info@eabyas.in>

 * @package Bizlms
 * @subpackage block_courselister
 */

use block_courselister\plugin;

defined('MOODLE_INTERNAL') || die();

/**
 * Class block_courselister_edit_form
 *
 * @author eabyas  <info@eabyas.in>

 * @package Bizlms
 * @subpackage block_courselister
 */
class block_courselister_edit_form extends block_edit_form {

    /**
     * Returns list of available course categories
     * @return array<string, string>
     * @throws coding_exception
     */
    protected function coursecatoptions() {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');
        $categories = [0 => get_string('allcategories')];
        $choices = make_categories_options();
        foreach ($choices as $id => $category) {
            $categories[$id] = html_entity_decode($category, null, 'UTF-8');
        }
        return $categories;
    }

    /**
     * Course type list of options
     * @return string[]
     * @throws coding_exception
     * @throws ddl_exception
     */
    protected function coursetypeoptions() {
        $result = [
            0 => get_string('none'),
            plugin::ENROLLEDCOURSES => get_string('frontpageenrolledcourselist'),
        ];

        // courselister specific elements.
        if (plugin::istocourselister()) {
            $result[plugin::LEARNINGPLANS] = get_string('learningplan', plugin::COMPONENT);
            $result[plugin::LEARNINGPLANSALL] = get_string('learningplanall', plugin::COMPONENT);
        }

        return $result;
    }

    /**
     * Course learningplan options
     * @return string[]
     * @throws coding_exception|ddl_exception
     * @throws dml_exception
     */
    protected function courselearningplanoptions() {
        global $DB;
        $result = [0 => get_string('none')];
        if (plugin::istocourselister()) {
            $result += $DB->get_records_menu('local_learningplan', null, '', 'id, name');
        }
        return $result;
    }

    /**
     * Define the instance settings
     * @param MoodleQuickForm $mform
     * @throws coding_exception|ddl_exception
     * @throws dml_exception
     */
    protected function specific_definition($mform) {

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        //if (!plugin::istocourselister()) {
            // This already exists in courselister.
            $text = $mform->addElement(
                'text',
                'config_blocktitle',
                get_string('blocktitle', plugin::COMPONENT),
                ['size' => 35]
            );
            $mform->setType($text->getName(), PARAM_TEXT);
            $mform->setDefault($text->getName(), get_string('blocktitledef', plugin::COMPONENT));
        //}

        $coursetype = $mform->addElement(
            'select',
            'config_coursetype',
            get_string('coursetype', plugin::COMPONENT),
            $this->coursetypeoptions()
        );
        $mform->setType($coursetype->getName(), PARAM_INT);
        $mform->addHelpButton($coursetype->getName(), 'coursetype-info', plugin::COMPONENT);

        $category = $mform->addElement(
            'select',
            'config_category',
            get_string('category', plugin::COMPONENT),
            $this->coursecatoptions()
        );
        $mform->setType($category->getName(), PARAM_INT);
        $mform->addHelpButton($category->getName(), 'category-info', plugin::COMPONENT);
        $mform->hideIf(
            $category->getName(),
            $coursetype->getName(),
            'in',
            sprintf('%d|%d', plugin::LEARNINGPLANS, plugin::LEARNINGPLANSALL)
        );

        if (plugin::istocourselister()) {
            $learningplanid = $mform->addElement(
                'select',
                'config_learningplanid',
                get_string('learningplanid', plugin::COMPONENT),
                $this->courselearningplanoptions()
            );
            $mform->setType($learningplanid->getName(), PARAM_INT);
            $mform->addHelpButton($learningplanid->getName(), 'learningplanid-info', plugin::COMPONENT);
            $mform->hideIf(
                $learningplanid->getName(),
                $coursetype->getName(),
                'in',
                sprintf(
                    '%d|%d|%d|%d',
                    plugin::ENROLLEDCOURSES,
                    0
                )
            );
        } else {
            $learningplanid = $mform->addElement('hidden', 'config_learningplanid', 0);
            $mform->setType($learningplanid->getName(), PARAM_INT);
            $mform->setConstant($learningplanid->getName(), 0);
        }

        $coursenumber = $mform->addElement(
            'text',
            'config_coursenumber',
            get_string('coursenumber', plugin::COMPONENT),
            ['size' => 8]
        );
        $mform->setType($coursenumber->getName(), PARAM_INT);
        $mform->addHelpButton($coursenumber->getName(), 'coursenumber-info', plugin::COMPONENT);

        $elementids = $mform->addElement(
            'textarea',
            'config_elementids',
            get_string('elementids', plugin::COMPONENT)
        );
        $mform->setType($elementids->getName(), PARAM_TEXT);
        $mform->addHelpButton($elementids->getName(), 'elementids-info', plugin::COMPONENT);
    }

}
