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

/**
 * Class containing data for managecompetencyframeworks page
 *
 * @package    local_competency
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_competency\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use single_button;
use stdClass;
use moodle_url;
use context_system;
use core_competency\api;
use core_competency\competency;
use core_competency\competency_framework;
use core_competency\external\competency_framework_exporter;
use local_competency\competencyview;

/**
 * Class containing data for managecompetencies page
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_competencies_page implements renderable, templatable {

    /** @var \core_competency\competency_framework $framework This competency framework. */
    protected $framework = null;


    protected $frameworks = null;

    /** @var \core_competency\competency[] $competencies List of competencies. */
    protected $competencies = array();

    /** @var string $search Text to search for. */
    protected $search = '';

    /** @var bool $canmanage Result of permissions checks. */
    protected $canmanage = false;

    /** @var moodle_url $pluginurlbase Base url to use constructing links. */
    protected $pluginbaseurl = null;

    /** @var context $pagecontext The page context. */
    protected $pagecontext = null;

    /**
     * Construct this renderable.
     *
     * @param \core_competency\competency_framework $framework Competency framework.
     * @param string $search Search string.
     * @param context $pagecontext The page context.
     */
    public function __construct( $search, $pagecontext) {
       global $USER;
       // print_object( $search);

        $this->frameworks = competencyview::get_user_competencyframework($USER->id);
        
        $this->pagecontext = $pagecontext;
        $this->search = $search;
        $addpage = new single_button(
           new moodle_url('/admin/tool/lp/editcompetencyframework.php'),
           get_string('addnewcompetency', 'local_competency')
        );
        $this->navigation[] = $addpage;

       // $this->canmanage = has_capability('moodle/competency:competencymanage', $framework->get_context());
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Renderer base.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->frameworks = array();
        $data->frameworkSelector = array(); $frameworkSelector=array();
        $data->frameworkShortname= array(); $frameworkShortname=array();
        $data->frameworkId = array(); $frameworkId=array();
        $data->search = $this->search;

        //print_object($data->search);
       // print_object($this->frameworks);
        foreach($this->frameworks as $record){

        $onerow= new stdClass();

         //print_object($framework);
        $framework = \core_competency\api::read_framework($record->id);
        $exporter = new competency_framework_exporter($framework);
        $onerow->framework = $exporter->export($output);

        $onerow->canmanage =  has_capability('moodle/competency:competencymanage', $framework->get_context());
        $data->search = $this->search;
        $onerow->pagecontextid = $this->pagecontext->id;
        $onerow->pluginbaseurl = (new moodle_url('/local/competency'))->out(true);

        $rulesmodules = array();
        $rules = competency::get_available_rules();
        foreach ($rules as $type => $rulename) {

            $amd = null;
            if ($type == 'core_competency\\competency_rule_all') {
                $amd = 'local_competency/competency_rule_all';
            } else if ($type == 'core_competency\\competency_rule_points') {
                $amd = 'local_competency/competency_rule_points';
            } else {
                // We do not know how to display that rule.
                continue;
            }

            $rulesmodules[] = [
                'name' => (string) $rulename,
                'type' => $type,
                'amd' => $amd,
            ];
        }
        $onerow->rulesmodules = json_encode(array_values($rulesmodules));       
      //  print_object($onerow);
        array_push($data->frameworks, $onerow);
        $frameworkSelector[]='[data-enhance=tree'.$record->id.']';       
        $frameworkShortname[]=$record->shortname;
        $frameworkId[]=$record->id;
       
        }  
         
        $data->frameworkSelector = json_encode($frameworkSelector); 
        $data->frameworkShortname = json_encode($frameworkShortname);
        $data->frameworkId = json_encode($frameworkId);
        $data->competencyview = strtolower(get_config('local_competency', 'competencyview')); 

        $data->spanclassnumber = $this->get_spanclass($data->competencyview);
        $data->isadvancedview = $this->tocheck_advancedcompetency_ornot($data->competencyview);
       
       // print_object($data); 
        return $data;
    } // end of function


    private function get_spanclass($competencyview){
       $spanclass='';

        if($competencyview=="basic")
            $spanclassnumber = 6;
        else if($competencyview=="advanced")
            $spanclassnumber = 4;
        else    
            $spanclassnumber = 6;  

        return $spanclassnumber;

    } // end of  get_spanclass function


    private function tocheck_advancedcompetency_ornot($competencyview){
        $isadvancedview ='';
        
       if($competencyview == "basic")
            $isadvancedview = 0;
         else if($competencyview=="advanced")
            $isadvancedview =1;
        else    
            $isadvancedview = 0;  

        return $isadvancedview;
    } // end of tocheck_advancedcompetency_ornot



} // end of the class
 