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
 * @subpackage local_certificates
 */
namespace local_certificates;

defined('MOODLE_INTERNAL') || die();

/**
 * Class represents a certificate template.
 *
 * @package    local_certificates
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template {

    /**
     * @var int $id The id of the template.
     */
    protected $id;

    /**
     * @var string $name The name of this template
     */
    protected $name;

    /**
     * @var int $contextid The context id of this template
     */
    protected $contextid;

    /**
     * The constructor.
     *
     * @param \stdClass $template
     */
    public function __construct($template) {

        $this->id = $template->id;
        $this->name = $template->name;
        $this->contextid =  1;
    }

    /**
     * Handles saving data.
     *
     * @param \stdClass $data the template data
     */
    public function save($data) {
        global $DB, $USER;
        
        $savedata = new \stdClass();
        $savedata->id = $this->id;
        $savedata->costcenter = $data->costcenter;
        $savedata->name = $data->name;
        // $savedata->code = $data->code;
        $savedata->timecreated = time();
        $savedata->timemodified = time();
        $savedata->usercreated = $USER->id;
        $savedata->usermodified = $USER->id;

        $DB->update_record('local_certificate', $savedata);
    }

    /**
     * Handles adding another page to the template.
     *
     * @return int the id of the page
     */
    public function add_page() {
        global $DB, $USER;

        // Set the page number to 1 to begin with.
        $sequence = 1;
        // Get the max page number.
        $sql = "SELECT MAX(sequence) as maxpage
                  FROM {local_certificate_pages} cp
                 WHERE cp.certificateid = :certificateid";
        if ($maxpage = $DB->get_record_sql($sql, array('certificateid' => $this->id))) {
            $sequence = $maxpage->maxpage + 1;
        }

        // New page creation.
        $page = new \stdClass();

        $page->certificateid = $this->id;
        if($page->certificatesize == 'a4_portrait'){
            $page->width = 210;
            $page->height = 297;
        }elseif($page->certificatesize == 'a4_landscape'){
            $page->height = 210;
            $page->width = 297;
        }elseif($page->certificatesize == 'letter_portrait'){
            $page->width = 216;
            $page->height = 279;
        }else{
            $page->height = 216;
            $page->width = 279;
        }
        
        $page->sequence = $sequence;
        $page->timecreated = time();
        $page->usercreated = $USER->id;
        $page->timemodified = time();
        $page->usermodified = $USER->id;

        // Insert the page.
        return $DB->insert_record('local_certificate_pages', $page);
    }

    /**
     * Handles saving page data.
     *
     * @param \stdClass $data the template data
     */
    public function save_page($data) {
        global $DB, $USER;

        // Set the time to a variable.
        $time = time();

        // Get the existing pages and save the page data.
        if ($pages = $DB->get_records('local_certificate_pages', array('certificateid' => $data->tid))) {
            // Loop through existing pages.
            foreach ($pages as $page) {
                // Get the name of the fields we want from the form.
                // $width = 'pagewidth_' . $page->id;
                // $height = 'pageheight_' . $page->id;
                $leftmargin = 'pageleftmargin_' . $page->id;
                $rightmargin = 'pagerightmargin_' . $page->id;
                // Create the page data to update the DB with.
                $p = new \stdClass();
                $p->id = $page->id;

                if($page->certificatesize == 'a4_portrait'){
                    $p->width = 210;
                    $p->height = 297;
                }elseif($page->certificatesize == 'a4_landscape'){
                    $p->height = 210;
                    $p->width = 297;
                }elseif($page->certificatesize == 'letter_portrait'){
                    $p->width = 216;
                    $p->height = 279;
                }else{
                    $p->height = 216;
                    $p->width = 279;
                }
                $p->certificatesize = $data->certificatesize;
                // $p->width = $data->$width;
                // $p->height = $data->$height;

                $p->leftmargin = $data->$leftmargin;
                $p->rightmargin = $data->$rightmargin;
                $p->timemodified = $time;
                $p->usermodified = $USER->id;
                // Update the page.
                $DB->update_record('local_certificate_pages', $p);
            }
        }
    }

    /**
     * Handles deleting the template.
     *
     * @return bool return true if the deletion was successful, false otherwise
     */
    public function delete() {
        global $DB;

        // Delete the elements.
        $sql = "SELECT e.*
                FROM {local_certificate_elements} e
                JOIN {local_certificate_pages} p ON e.pageid = p.id
                WHERE p.certificateid = :certificateid";
                
        if ($elements = $DB->get_records_sql($sql, array('certificateid' => $this->id))) {
            foreach ($elements as $element) {
                // Get an instance of the element class.
                if ($e = \local_certificates\element_factory::get_element_instance($element)) {
                    $e->delete();
                } else {
                    // The plugin files are missing, so just remove the entry from the DB.
                    $DB->delete_records('local_certificate_elements', array('id' => $element->id));
                }
            }
        }

        // Delete the pages.
        if (!$DB->delete_records('local_certificate_pages', array('certificateid' => $this->id))) {
            return false;
        }

        // Now, finally delete the actual template.
        if (!$DB->delete_records('local_certificate', array('id' => $this->id))) {
            return false;
        }

        return true;
    }

    /**
     * Handles deleting a page from the template.
     *
     * @param int $pageid the template page
     */
    public function delete_page($pageid) {
        global $DB;

        // Get the page.
        $page = $DB->get_record('local_certificate_pages', array('id' => $pageid), '*', MUST_EXIST);

        // Delete this page.
        $DB->delete_records('local_certificate_pages', array('id' => $page->id));

        // The element may have some extra tasks it needs to complete to completely delete itself.
        if ($elements = $DB->get_records('local_certificate_elements', array('pageid' => $page->id))) {
            foreach ($elements as $element) {
                // Get an instance of the element class.
                if ($e = \local_certificates\element_factory::get_element_instance($element)) {
                    $e->delete();
                } else {
                    // The plugin files are missing, so just remove the entry from the DB.
                    $DB->delete_records('local_certificate_elements', array('id' => $element->id));
                }
            }
        }

        // Now we want to decrease the page number values of
        // the pages that are greater than the page we deleted.
        $sql = "UPDATE {local_certificate_pages}
                   SET sequence = sequence - 1
                 WHERE certificateid = :certificateid
                   AND sequence > :sequence";
        $DB->execute($sql, array('certificateid' => $this->id, 'sequence' => $page->sequence));
    }

    /**
     * Handles deleting an element from the template.
     *
     * @param int $elementid the template page
     */
    public function delete_element($elementid) {
        global $DB;

        // Ensure element exists and delete it.
        $element = $DB->get_record('local_certificate_elements', array('id' => $elementid), '*', MUST_EXIST);

        // Get an instance of the element class.
        if ($e = \local_certificates\element_factory::get_element_instance($element)) {
            $e->delete();
        } else {
            // The plugin files are missing, so just remove the entry from the DB.
            $DB->delete_records('local_certificate_elements', array('id' => $elementid));
        }

        // Now we want to decrease the sequence numbers of the elements
        // that are greater than the element we deleted.
        $sql = "UPDATE {local_certificate_elements}
                   SET sequence = sequence - 1
                 WHERE pageid = :pageid
                   AND sequence > :sequence";
        $DB->execute($sql, array('pageid' => $element->pageid, 'sequence' => $element->sequence));
    }

    /**
     * Generate the PDF for the template.
     *
     * @param bool $preview true if it is a preview, false otherwise
     * @param int $userid the id of the user whose certificate we want to view
     * @param bool $return Do we want to return the contents of the PDF?
     * @param obj $moduleinfo having information with moduletype and moduleid
     * @return string|void Can return the PDF in string format if specified.
     */
    public function generate_pdf($preview = false, $userid = null, $return = false, $moduleinfo = null, $savepdf = false) {
        global $CFG, $DB, $USER;

        if (empty($userid)) {
            $user = $USER;
        } else {
            $user = \core_user::get_user($userid);
        }
       
        require_once($CFG->libdir . '/pdflib.php');
 
        // Get the pages for the template, there should always be at least one page for each template.
        if ($pages = $DB->get_records('local_certificate_pages', array('certificateid' => $this->id), 'sequence ASC')) {
          
            // Create the pdf object.
            $pdf = new \pdf();

            // If the template belongs to a certification then we need to check what permissions we set for it.
            // if ($protection = $DB->get_field('local_certificate', 'protection', array('id' => $this->id))) {
            //     if (!empty($protection)) {
            //         $protection = explode(', ', $protection);
            //         $pdf->SetProtection($protection);
            //     }
            // }
           
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetTitle($this->name);
            $pdf->SetAutoPageBreak(true, 0);
            // Remove full-stop at the end, if it exists, to avoid "..pdf" being created and being filtered by clean_filename.
            if($savepdf){
                $filename = 'Biz_user'.$userid.'_'.$moduleinfo->moduletype.''.$moduleinfo->moduleid;
                $filename = clean_filename($filename . '.pdf');
            }else{
                $filename = rtrim($this->name, '.');
                $filename = clean_filename($filename . '.pdf');
            }
            
            
            // Loop through the pages and display their content.
            foreach ($pages as $page) {
                // Add the page to the PDF.
                if ($page->width > $page->height) {
                    $orientation = 'L';
                } else {
                    $orientation = 'P';
                }
                $pdf->AddPage($orientation, array($page->width, $page->height));
                $pdf->SetMargins($page->leftmargin, 0, $page->rightmargin);
                // Get the elements for the page.
                 
                if ($elements = $DB->get_records('local_certificate_elements', array('pageid' => $page->id), 'sequence ASC')) {
                    // Loop through and display.
                   
                    foreach ($elements as $element) {
                        if($element->element == 'modulename'){
                            $element->moduletype = $moduleinfo->moduletype;
                            $element->moduleid = $moduleinfo->moduleid;
                        }                    
                        // Get an instance of the element class.
                        $e = \local_certificates\element_factory::get_element_instance($element);if($element->element == 'modulename' || $element->element == 'date'){
                            $e->render($pdf, $preview, $user, $moduleinfo);
                        }else{
                            $e->render($pdf, $preview, $user, $moduleinfo);
                        }
                        //$e->render($pdf, $preview, $user, $moduleinfo);
                    }
                }
            }
           
            if ($return) {
               return $pdf->Output('', 'S');
            }
            if($savepdf){
                $folderpath = $CFG->dataroot.'/user_certificates/';
                if(!is_dir($folderpath)) {
                    mkdir($folderpath);
                    chmod($folderpath, 0777);
                }
                $filepath = $CFG->dataroot."/user_certificates/$filename";
                $pdf->Output($filepath, 'F');
                chmod($filepath, 0777);
                

            }else{
                $pdf->Output($filename, 'D');
            }
        }
    }

    /**
     * Handles copying this template into another.
     *
     * @param int $copytotemplateid The template id to copy to
     */
    public function copy_to_template($copytotemplateid) {
        global $DB;

        // Get the pages for the template, there should always be at least one page for each template.
        if ($templatepages = $DB->get_records('local_certificate_pages', array('certificateid' => $this->id))) {
            // Loop through the pages.
            foreach ($templatepages as $templatepage) {
                $page = clone($templatepage);
                $page->templateid = $copytotemplateid;
                $page->timecreated = time();
                $page->timelocalified = $page->timecreated;
                // Insert into the database.
                $page->id = $DB->insert_record('local_certificate_pages', $page);
                // Now go through the elements we want to load.
                if ($templateelements = $DB->get_records('local_certificate_elements', array('pageid' => $templatepage->id))) {
                    foreach ($templateelements as $templateelement) {
                        $element = clone($templateelement);
                        $element->pageid = $page->id;
                        $element->timecreated = time();
                        $element->timelocalified = $element->timecreated;
                        // Ok, now we want to insert this into the database.
                        $element->id = $DB->insert_record('local_certificate_elements', $element);
                        // Load any other information the element may need to for the template.
                        if ($e = \local_certification\element_factory::get_element_instance($element)) {
                            if (!$e->copy_element($templateelement)) {
                                // Failed to copy - delete the element.
                                $e->delete();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Handles moving an item on a template.
     *
     * @param string $itemname the item we are moving
     * @param int $itemid the id of the item
     * @param string $direction the direction
     */
    public function move_item($itemname, $itemid, $direction) {
        global $DB;

        $table = 'local_certificate_';
        if ($itemname == 'page') {
            $table .= 'pages';
        } else { // Must be an element.
            $table .= 'elements';
        }

        if ($moveitem = $DB->get_record($table, array('id' => $itemid))) {
            // Check which direction we are going.
            if ($direction == 'up') {
                $sequence = $moveitem->sequence - 1;
            } else { // Must be down.
                $sequence = $moveitem->sequence + 1;
            }

            // Get the item we will be swapping with. Make sure it is related to the same template (if it's
            // a page) or the same page (if it's an element).
            if ($itemname == 'page') {
                $params = array('id' => $moveitem->certificateid);
            } else { // Must be an element.
                $params = array('pageid' => $moveitem->pageid);
            }
            $swapitem = $DB->get_record($table, $params + array('sequence' => $sequence));
        }

        // Check that there is an item to move, and an item to swap it with.
        if ($moveitem && !empty($swapitem)) {
            $DB->set_field($table, 'sequence', $swapitem->sequence, array('id' => $moveitem->id));
            $DB->set_field($table, 'sequence', $moveitem->sequence, array('id' => $swapitem->id));
        }
    }

    /**
     * Returns the id of the template.
     *
     * @return int the id of the template
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Returns the name of the template.
     *
     * @return string the name of the template
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Returns the context id.
     *
     * @return int the context id
     */
    public function get_contextid() {
        return $this->contextid;
    }

    /**
     * Returns the context id.
     *
     * @return \context the context
     */
    public function get_context() {
        return \context::instance_by_id($this->contextid);
    }

    /**
     * Returns the context id.
     *
     * @return \context_localule|null the context localule, null if there is none
     */
    // public function get_cm() {
    //     $context = $this->get_context();
    //     if ($context->contextlevel === CONTEXT_MODULE) {
    //         return get_courselocalule_from_id('certificates', $context->instanceid, 0, false, MUST_EXIST);
    //     }

    //     return null;
    // }

    /**
     * Ensures the user has the proper capabilities to manage this template.
     *
     * @throws \required_capability_exception if the user does not have the necessary capabilities (ie. Fred)
     */
    public function require_manage() {
        require_capability('local/certificates:manage', $this->get_context());
    }

    /**
     * Creates a template.
     *
     * @param string $templatename the name of the template
     * @param int $contextid the context id
     * @return \local_certification\template the template object
     */
    public static function create($data, $contextid) {
        global $DB, $USER;
        
        // print_object($data);
        // print_object($contextid);
        // exit;

        $template = new \stdClass();
        $template->costcenter = $data->costcenter;
        $template->name = $data->name;
        $template->code = $data->code;
        $template->contextid = $contextid;
        $template->timecreated = time();
        $template->timemodified = time();
        $template->usercreated = $USER->id;
        $template->usermodified = $USER->id;

        $template->id = $DB->insert_record('local_certificate', $template);

        return  new \local_certificates\template($template);
    }
}
