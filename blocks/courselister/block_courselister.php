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
 * Course list block.
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms
 * @subpackage block_courselister
 */

use block_courselister\output\blockview;
use block_courselister\plugin;

defined('MOODLE_INTERNAL') || die();

/**
 * Class block_courselister
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms
 * @subpackage block_courselister
 */
final class block_courselister extends block_base {

    /**
     * Initialize the block title
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', plugin::COMPONENT);
    }
    /**
     * Generate the block content
     * @return stdClass|null
     * @throws coding_exception
     */
    public function get_content() {

        global $OUTPUT, $PAGE,$CFG;

        $systemcontext = context_system::instance();
        if (isloggedin() and ($this->content === null)) {
            $renderer = $this->page->get_renderer(plugin::COMPONENT);
            /** @var stdClass $config */
            $config = $this->config;
            $returnoutput='';

            $tabs=array();
            $collapse = true;
            $show = '';
            $filterdata = json_encode(array());

            $coursetypes = array(plugin::ENROLLEDCOURSES=>'enrolledcourses',plugin::LEARNINGPLANS=>'enrolledlearningplans',plugin::LEARNINGPLANSALL=>'alllearningplans');

            $dataoptions = (array)$this->config;

            $options = array('targetID' => 'my'.$coursetypes[$this->config->coursetype],'perPage' =>$this->config->coursenumber, 'cardClass' => 'col-xl-4 col-md-6 col-12', 'viewType' => 'card');

            $showpagepermission=true;
            $dataoptions['coursetype'] = $this->config->coursetype ;
            $options['methodName']='block_courselister_get_my'.$coursetypes[$this->config->coursetype].'';
            $options['templateName']='block_courselister/viewmy'.$coursetypes[$this->config->coursetype].'';

            $carddataoptions = json_encode($dataoptions);
            $cardoptions = json_encode($options);
            $cardparams = array(
                'targetID' => 'my'.$coursetypes[$this->config->coursetype].'',
                'options' => $cardoptions,
                'dataoptions' => $carddataoptions,
                'filterdata' => $filterdata,
            );
           

            if((is_siteadmin() || has_capability('local/costcenter:manage_ownorganization',$systemcontext) || has_capability('local/costcenter:manage_owndepartments',$systemcontext)) && ($this->config->coursetype==plugin::LEARNINGPLANSALL)){
                $cardparams['filtertype']= 'my'.$coursetypes[$this->config->coursetype].'';
                $tabs[] = array('active' => 'active','type' => 'my'.$coursetypes[$this->config->coursetype].'', 'filterform' => array(), 'canfilter' => true, 'show' => '','name' => $coursetypes[$this->config->coursetype],'coursetype'=>$coursetypes[$this->config->coursetype],'');

            }elseif((!is_siteadmin() && !has_capability('local/costcenter:manage_ownorganization',$systemcontext) && !has_capability('local/costcenter:manage_owndepartments',$systemcontext)) && ($this->config->coursetype==plugin::LEARNINGPLANS || $this->config->coursetype==plugin::ENROLLEDCOURSES )){
                $cardparams['filtertype']= 'my'.$coursetypes[$this->config->coursetype].'';
                $tabs[] = array('active' => 'active','type' => 'my'.$coursetypes[$this->config->coursetype].'', 'filterform' => array(), 'canfilter' => true, 'show' => '','name' => $coursetypes[$this->config->coursetype],'coursetype'=>$coursetypes[$this->config->coursetype]);
            }
             $fncardparams=$cardparams;
            if($tabs){
                $cardparams = $fncardparams+array(
                        'tabs' => $tabs,
                        'contextid' => $systemcontext->id,
                        'plugintype' => 'block',
                        'plugin_name' =>'courselister',
                        'cfg' => $CFG);
                $returnoutput.=$OUTPUT->render_from_template('block_courselister/block_courselister', $cardparams);
            }
            $this->content = (object)[
                'text' => $returnoutput,
                'footer' => ''
            ];
       
        }

        return $this->content;
    }

    /**
     * Does block have global settings
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Where can we display the block
     * @return array<string, bool>
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Ensure proper title is displayed
     * @throws coding_exception
     * @throws ddl_exception
     */
    public function specialization() {
        // if (!plugin::istocourselister()) {
            if (empty($this->config->blocktitle)) {
                $this->title = get_string('blocktitledef', plugin::COMPONENT);
            } else {
                $this->title = $this->config->blocktitle;
            }
        // }
    }

    /**
     * Do we allow instance configuration
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Do we allow multiple instances on the same page
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }
}
