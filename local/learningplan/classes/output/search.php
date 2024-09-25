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
 * Class containing data for search
 *
 * @package    local_learningplan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningplan\output;
require_once($CFG->dirroot.'/local/learningplan/lib.php');
require_once($CFG->dirroot.'/local/search/lib.php');
require_once($CFG->dirroot.'/local/users/lib.php');
defined('MOODLE_INTERNAL') || die();

use renderable;
use context_user;

use local_search\output\searchlib;
use local_learningplan\lib\lib as lpn;
use local_request\api\requestapi;

/**
 * Class containing data for search
 *
 * @copyright  2019 eabyas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search implements renderable{
    public function get_learningpathlist_query($perpage,$startlimit,$return_noofrecords=false, $returnobjectlist=false, $filters = array()){
        global $DB, $USER, $CFG;

        $search = searchlib::$search;
        //------main queries written here to fetch Classrooms or  session based on condition
        $selectsql = "SELECT llp.*,llp.startdate as trainingstartdate, llp.enddate as trainingenddate ";
        $fromsql = " from {local_learningplan} llp ";

        $leftjoinsql = '';

        // added condition for not displaying retired learningplans.
        $wheresql = " where llp.id > 0 and llp.visible=1 and llp.selfenrol=1  "; //AND llp.open_status <> 4

        //------if not site admin sessions list will be filter by location or bands
        if(searchlib::$search && searchlib::$search!='null'){
            $searchsql = " AND llp.name LIKE '%$search%'";
        }

        $usercontext = context_user::instance($USER->id);
        if(!is_siteadmin()){
           // $usercostcenterpaths = $DB->get_records_menu('local_userdata', array('userid' => $USER->id), '', 'id, costcenterpath');
            $usercostcenterpaths = $DB->get_records_menu('user', array('id' => $USER->id), '', 'id, open_path');
            $paths = [];
            foreach($usercostcenterpaths AS $userpath){
                $userpathinfo = $userpath;
                $paths[] = $userpathinfo.'/%';
                $paths[] = $userpathinfo;
                while ($userpathinfo = rtrim($userpathinfo,'0123456789')) {
                    $userpathinfo = rtrim($userpathinfo, '/');
                    if ($userpathinfo === '') {
                      break;
                    }
                    $paths[] = $userpathinfo;
                }
            }

            
            if(!empty($paths)){
                foreach($paths AS $path){
                    $pathsql[] = " llp.open_path LIKE '{$path}' ";
                }
                $wheresql .= " AND ( ".implode(' OR ', $pathsql).' ) ';
            }
         
            profile_load_custom_fields($USER);
            
            if(isset($USER->profile) && !empty($USER->profile)){                
                foreach($USER->profile as $pkey => $pval){
                    $usercustomcatgid = get_usercustomfield_category($USER);
                   /*  $uif = $DB->get_record('user_info_field',array('shortname' => $pkey,'categoryid' => $usercustomcatgid,'targetaudience' => 1),'id,shortname,targetaudience');
                    if($pkey === $uif->shortname){
                        $fromsql .= " JOIN {local_module_targetaudience} as {$uif->shortname} ON {$uif->shortname}.fieldid = {$uif->id} 
                                        AND (CONCAT(',', {$uif->shortname}.fieldvalue,',') LIKE CONCAT('%,{$pval},%') OR {$uif->shortname}.fieldvalue IS NULL) 
                                        AND {$uif->shortname}.moduleid = llp.id AND {$uif->shortname}.module = 'learningplan' ";
                    } */
                    $uif = $DB->get_record('user_info_field',array('shortname' => $pkey,'categoryid' => $usercustomcatgid, 'targetaudience' => 1),'id,shortname,targetaudience,datatype');
                    $targetsql = "SELECT lp.id FROM {local_module_targetaudience} lmt JOIN {local_learningplan} lp ON lp.id = lmt.moduleid  WHERE fieldid = :fieldid AND lp.visible = 1 ";
                    if($DB->record_exists_sql($targetsql,array('fieldid' => $uif->id)))   {                      
                        if($pkey === $uif->shortname && !empty($pval)){

                            $fromsql .= " JOIN {local_module_targetaudience} as {$uif->shortname} ON {$uif->shortname}.fieldid = {$uif->id} ";                                       
                                            
                            if($uif->datatype == 'autocomplete'){
                                $uservalues = explode(',',$pval);
                                $autosql = array();
                                foreach($uservalues as $ukey=>$uval){
                                    $uval = "'".trim($uval)."'";
                                    $autosql[] = " FIND_IN_SET({$uval}, {$uif->shortname}.fieldvalue ) ";
                                }
                                $autosql[] = " {$uif->shortname}.fieldvalue IS NULL ";
                                $fromsql .= " AND ( ".implode(' OR ', $autosql).' ) ';
                            }else{
                                $fromsql .= " AND (CONCAT(',', {$uif->shortname}.fieldvalue,',') LIKE CONCAT('%,{$pval},%') OR {$uif->shortname}.fieldvalue IS NULL) ";
                            }
                            $fromsql .= "AND {$uif->shortname}.moduleid = llp.id AND {$uif->shortname}.module = 'learningplan'";
                        }
                    }
                    
                }
               
            }  

            $params = array();
            $group_list = $DB->get_records_sql_menu("SELECT cm.id,cm.cohortid as groupid from {cohort_members} cm where cm.userid IN ({$USER->id})");
          
            $grouquery = array(" llp.open_group IS NULL ", " llp.open_group = -1 ");
            if(!empty($group_list)){
                foreach ($group_list as $key => $group) {
                    $grouquery[] = " concat(',',llp.open_group,',') LIKE concat('%,', $group, ',%') ";
                }
            }
            $groupqueeryparams =implode('OR',$grouquery);
            $params[]= '('.$groupqueeryparams.')';

      
            if(!empty($USER->open_hrmsrole) && $USER->open_hrmsrole != ""){
                $hrmsrolelike = "'%,$USER->open_hrmsrole,%'";
            }else{
                $hrmsrolelike = "''";
            }
            $params[]= " 1 = CASE WHEN llp.open_hrmsrole IS NOT NULL
                THEN
                    CASE WHEN CONCAT(',',llp.open_hrmsrole,',') LIKE {$hrmsrolelike}
                    THEN 1
                    ELSE 0 END
                ELSE 1 END ";

            if(!empty($USER->open_location) && $USER->open_location != ""){
                $citylike = "'%,$USER->open_location,%'";
            }else{
                $citylike = "''";
            }
            $params[]= " 1 = CASE WHEN llp.open_location IS NOT NULL
                THEN
                    CASE WHEN CONCAT(',',llp.open_location,',') LIKE {$citylike}
                        THEN 1
                        ELSE 0 END
                ELSE 1 END ";

            if(!empty($params)){
                $finalparams=implode('AND',$params);
            }else{
                $finalparams= '1=1' ;
            }
            $wheresql .= " AND ($finalparams)"; 

            if(!empty($USER->open_designation) && $USER->open_designation != "" && $USER->open_designation != NULL){                 
                $wheresql .= " AND ( concat(',',llp.open_designation,',') LIKE '%,$USER->open_designation,%'  OR llp.open_designation = '-1' OR llp.open_designation = '' OR llp.open_designation IS NULL OR llp.open_designation = '_qf__force_multiselect_submission')";
            }   
        }
        $sqlparams = [];
        $i = 0;
        foreach($filters AS $filtertype => $filtervalues){
            switch($filtertype){
                // case 'categories':
                // break;
                case 'status':
                    $statussql = [];
                    foreach($filters['status'] AS $statusfilter){
                        switch ($statusfilter) {
                            case 'notenrolled':
                                $statussql[] = " llp.id not in (select distinct planid from {local_learningplan_user} where userid=$USER->id) ";
                            break;
                            case 'inprogress':
                                $statussql[] = " llp.id in (select distinct planid from {local_learningplan_user} where userid={$USER->id} AND (status <> 1 OR status IS NULL))";
                            break;
                            case 'completed':
                                $statussql[] = "  llp.id in (select distinct planid from {local_learningplan_user} where userid=$USER->id AND status = 1) ";
                            break;
                        }
                    }
                    if(!empty($statussql)){
                        $wheresql .= " AND (".implode('OR', $statussql).' ) ';
                    }
                break;
                // case 'categories':
                //     $categories = is_array($filtervalues) ? $filtervalues : [$filtervalues];
                //     $catgeorylike = [];
                //     foreach($categories AS $category){
                //         $catgeorylike[] =  " concat(',',lcm.category,',') like :categorylike{$i} ";
                //         $sqlparams['categorylike'.$i] = '%,'.$category.',%';
                //         $i++;
                //     }
                //     if(!empty($catgeorylike)){
                //         $catdependencysql = implode(' OR ', $catgeorylike);
                //         $wheresql .= " AND llp.id IN (SELECT lcm.moduleid FROM {local_category_mapped} AS lcm WHERE lcm.moduletype LIKE 'learningplan' AND ($catdependencysql) )";
                //     }
                // break;
                default:
                    if(strpos($filtertype, 'categories_') === 0){
                        $categories = is_array($filtervalues) ? $filtervalues : [$filtervalues];
                        $catgeorylike = [];
                        foreach($categories AS $category){
                            $catgeorylike[] =  " concat(',',lcm.category,',') like :categorylike{$i} ";
                            $sqlparams['categorylike'.$i] = '%,'.$category.',%';
                            $i++;
                        }
                        if(!empty($catgeorylike)){
                            $catdependencysql = implode(' OR ', $catgeorylike);
                            $wheresql .= " AND llp.id IN (SELECT lcm.moduleid FROM {local_category_mapped} AS lcm WHERE lcm.moduletype LIKE 'learningplan' AND ($catdependencysql) )";
                        }
                    }
                    if(strpos($filtertype, 'coursetype_') === 0){
                        $categories = is_array($filtervalues) ? $filtervalues : [$filtervalues];
                        if(!empty(array_filter($categories))){
                            $wheresql .= " AND 1 <> 1 ";
                        }
                    }
                break;
            }
        }

        $countsql = "SELECT llp.id ";
        $countquery = $countsql.$fromsql.$leftjoinsql.$wheresql.$searchsql;
        $countquery .= " GROUP BY llp.id";
        $numberofrecords = 0;
        if($return_noofrecords)
            $numberofrecords = sizeof($DB->get_records_sql($countquery, $sqlparams));
        $finalsql = $selectsql.$fromsql.$leftjoinsql.$wheresql.$searchsql;
        $finalsql .= " GROUP BY llp.id ORDER by llp.id DESC ";

        $learningplanlist = $DB->get_records_sql($finalsql, $sqlparams, $startlimit, $perpage);
        if($return_noofrecords && !$returnobjectlist){
            return  array('numberofrecords'=>$numberofrecords);
        }
        else if($returnobjectlist && !$return_noofrecords){
            return  array('list'=>$learningplanlist);
        }
        else{
            if($return_noofrecords && $returnobjectlist){
                return  array('numberofrecords'=>$numberofrecords,'list'=>$learningplanlist);
            }
        }
    }

    public function export_for_template($perpage,$startlimit,$selectedfilter = array()){
        global $DB, $USER, $CFG, $PAGE,$OUTPUT;
        $context = \local_costcenter\lib\accesslib::get_module_context();
        $certificationlist_ar =$this->get_learningpathlist_query($perpage, $startlimit, true, true,$selectedfilter);
        $certificationlist= $certificationlist_ar['list'];
        foreach($certificationlist as $list){
          $iltlocation=$DB->get_field('local_location_institutes','fullname',array('id'=>$list->instituteid));
          if($iltlocation){
            $list->iltlocation=$iltlocation;
           }
            $course=$DB->get_record('course', array('id'=>$list->course));
            $name="categoryname";
            $coursefileurl = (new lpn)->get_learningplansummaryfile($list->id);
            $list->categoryname = $name;
            $list->formattedcategoryname = searchlib::format_thestring($name);
            $list->iltfullformatname = searchlib::format_thestring($list->name);
            $list->fullname = searchlib::format_thestring($list->name);
            $list->shortname = searchlib::format_thestring($list->shortname);
            $iltname = searchlib::format_thestring($list->name);
            if (strlen($iltname)>60){
                $iltname = clean_text(substr($iltname, 0, 60))."...";
                $list->iltformatname = searchlib::format_thestring($iltname) ;
            }else {
                $list->iltformatname = searchlib::format_thestring($list->name);
            }

            $list->fileurl =   $coursefileurl;
            $list->bannerimage =  is_object($coursefileurl) ? $coursefileurl->out() : $coursefileurl;
            $list->intro = searchlib::format_thesummary($list->description);
            $list->summary = searchlib::format_thesummary($list->description);
            //------------------Date-----------------------
            $startdate = searchlib::get_thedateformat($list->startdate);
            $enddate = searchlib::get_thedateformat($list->enddate);
            $list->date = $startdate.' - '.$enddate;
            $list->start_date = date("j M 'y", $list->startdate);

            $lpcoursecount = $this->getcoursecount($list->id);
            $list->coursecount = $lpcoursecount ? $lpcoursecount : 'N/A';

            //-------bands----------------------------
            $list->bands = searchlib::trim_theband($list->bands);
            $list->type = learningplan;
            $list->module = 'local_learningpath';
            $list->enroll = $this->get_enrollflag($list->id);
            $list->isenrolled=$list->enroll;
            $userenrolstatus = $DB->record_exists('local_learningplan_user', array('planid' => $list->id, 'userid' => $USER->id));
            $return=false;

            $list->enrollmentbtn = $this->get_enrollbtn($list);
            $list->requeststatus = MODULE_NOT_ENROLLED;
            if($list->isenrolled){
                $list->requeststatus = MODULE_ENROLLED;
            }else{
                if($list->approvalreqd){
                    $sql = "SELECT status FROM {local_request_records} WHERE componentid=:componentid AND compname LIKE :compname AND createdbyid = :createdbyid ORDER BY id desc ";
                    $requeststatus = $DB->get_field_sql($sql, array('componentid' => $list->id,'compname' => 'learningplan', 'createdbyid'=>$USER->id));
                    if($requeststatus == 'PENDING'){
                        $list->requeststatus = MODULE_ENROLMENT_PENDING;
                    }
                }
            }
            if($list->approvalreqd == 1){
                $list->enrolmethods[] = 'request';

            }else if($list->selfenrol == 1){
                $list->enrolmethods[] = 'self';
            }

            $list->rating_element = '';
            $list->avgrating = 0;
            $list->ratedusers = 0;
            $list->likes = 0;
            $list->dislikes = 0;
            if(class_exists('local_ratings\output\renderer')){
                $rating_render = $PAGE->get_renderer('local_ratings');
                $ratinginfo = $DB->get_record('local_ratings_likes', array('module_id' => $list->id, 'module_area' => 'local_learningplan'));
                if($ratinginfo){
                    $list->avgrating = $ratinginfo->module_rating;
                    $list->ratedusers = $ratinginfo->module_rating_users;
                    $list->likes = $ratinginfo->module_like;
                    $list->dislikes = $ratinginfo->module_like_users - $ratinginfo->module_like;
                    $list->rating_element = $rating_render->render_ratings_data('local_classroom', $list->id ,$ratinginfo->module_rating, 14);
                }
            }
            if($list->enroll == 1){
                $list->redirect='<a class="cat_btn viewmore_btn" href ="'.$CFG->wwwroot.'/local/learningplan/view.php?id='.$list->id.'" >'.get_string('start_now','local_search').'</a>';
            }else{
                // $list->redirect='<span data-action="learningplan'.$list->id.'" class="learningplaninfo d-block" onclick ="(function(e){ require(\'local_search/courseinfo\').learningplaninfo({selector:\'learningplan'.$list->id.'\', learningplanid:'.$list->id.'}) })(event)"><span>'.get_string('viewmore','local_search').'</span></span>';
                $list->redirect='<a class="cat_btn viewmore_btn" href ="'.$CFG->wwwroot.'/local/learningplan/lpathinfo.php?id='.$list->id.'" >'.get_string('gotolpath','local_search').'</a>';
            }
            $list->copylink = '';
            if(is_siteadmin() || has_capability('local/learningplan:manage', $context)){
                $list->copylink = '<a data-action="courseinfo'.$course->id.'" onclick ="(function(e){ require(\'local_search/courseinfo\').copy_url({module:\'learningplan\', moduleid:'.$list->id.'}) })(event)"><button class="cat_btn viewmore_btn">'.get_string('copyurl', 'local_search').'</button></a>';
            }
            $enrolled = $DB->get_field('local_learningplan_user','id', array('planid' => $list->id, 'userid' => $USER->id));
            $list->selfenroll = $enrolled ? 2 : 1 ;
            $list->learningplanlink= $CFG->wwwroot.'/local/learningplan/view.php?id='.$list->id;

            $finallist[]= $list;
        } // end of foreach

        $finallist['numberofrecords']=$certificationlist_ar['numberofrecords'];

        return $finallist;
    }
    public function enrol_user_to_component($enrolmethod, $moduleid){
        global $DB, $USER, $CFG;
        $learningplan_lib = new \local_learningplan\lib\lib();
        $learningplan = $DB->get_record('local_learningplan', array('id' => $moduleid));
        if(empty($learningplan)){
            throw new \Exception("Learningplan not found");
        }
        $can_self_enrol = $this->is_plan_accessible($learningplan);
        if(!$can_self_enrol){
            throw new \Exception("You cannot enrol to this learningplan");
        }
        if($this->get_enrollflag($moduleid)){
            throw new \Exception("Already enrolled");
        }
        switch($enrolmethod){
            case 'request':
                if($learningplan->approvalreqd != 1){
                    throw new \Exception("Enrollment method inactive");
                }else{
                    \local_request\api\requestapi::create('learningplan', $moduleid);
                }
            break;
            case 'self':
                if($learningplan->approvalreqd == 1 || $learningplan->selfenrol != 1){
                    throw new \Exception("Enrollment method inactive");
                }else{
                    $record = new \stdClass();
                    $record->planid = $moduleid;
                    $record->userid = $USER->id;
                    $record->timecreated = time();
                    $record->usercreated = $USER->id;
                    $record->timemodified = 0;
                    $record->usermodified = 0;
                    $create_record = $learningplan_lib->assign_users_to_learningplan($record);
                }
            break;
            default:
                throw new \Exception("Unknown enrollment method");
            break;
        }
    }
    public function is_plan_accessible($learningplan){
        global $USER, $DB;
        $selectsql = "SELECT llp.id  from {local_learningplan} llp  ";
        $wheresql = " WHERE llp.id = :learningplanid and llp.visible=1  "; //AND llp.open_status <> 4
        //$usercostcenterpaths = $DB->get_records_menu('local_userdata', array('userid' => $USER->id), '', 'id, costcenterpath');
        $usercostcenterpaths = $DB->get_records_menu('user', array('id' => $USER->id), '', 'id, open_path');
        // $paths = [];
        // foreach($usercostcenterpaths AS $userpath){
        //     $userpathinfo = $userpath->costcenterpath;
        //     $paths[] = $userpathinfo.'%';
        //     while ($userpathinfo = rtrim($userpathinfo,'0123456789')) {
        //         $userpathinfo = rtrim($userpathinfo, '/');
        //         if ($userpathinfo === '') {
        //           break;
        //         }
        //         $paths[] = $userpathinfo;
        //     }
        // }
        if(!empty($usercostcenterpaths)){
            foreach($usercostcenterpaths AS $path){
                list($zero, $org, $ctr, $bu, $cu, $territory) = explode("/",$path);
                $pathsql[]="concat('/',llp.open_path,'/') LIKE '%{$org}%'";
            }
            $wheresql .= " AND ( ".implode(' OR ', $pathsql).' ) ';
        }
        return $DB->record_exists_sql($selectsql.$wheresql, ['learningplanid' => $learningplan->id]);
    }
    private function get_enrollflag($certificationid){
        global $USER, $DB;

        $enrolled =$DB->record_exists('local_learningplan_user',array('planid'=>$certificationid,'userid'=>$USER->id));
        if($enrolled){
            $flag=1;
        }else{
            $flag=0;
        }
        return $flag;
    } // end of get_enrollflag
    public static function get_enrollbtn($planinfo){
     global $DB,$USER;
        $planid = $planinfo->id;
        $planname =  $planinfo->name;

        if(!is_siteadmin()){
                if($planinfo->approvalreqd==1){
                    $componentid =$planid;
                    $component = 'learningplan';
                    $sql = "SELECT status FROM {local_request_records} WHERE componentid=:componentid AND compname LIKE :compname AND createdbyid = :createdbyid ORDER BY id desc ";
                    $request = $DB->get_field_sql($sql,array('componentid' => $planid,'compname' => $component,'createdbyid'=>$USER->id));
                    if($request=='PENDING'){
                    $enrollmentbtn = '<button class="cat_btn btn-primary catbtn_process viewmore_btn">'.get_string('requestprocessing', 'local_search').'</button>';
                    }else{
                    $enrollmentbtn =requestapi::get_requestbutton($componentid, $component, $planname);
                    }
                }
                else{
                    $enrollmentbtn = '<a href="javascript:void(0);" class="fakebtn cat_btn btn-primary catbtn_request viewmore_btn" alt = ' . get_string('enroll','local_search'). ' title = ' .get_string('enroll','local_search'). ' onclick="(function(e){ require(\'local_learningplan/courseenrol\').enrolUser({planid:'.$planid.', userid:'.$USER->id.', planname:\''.$planname.'\' }) })(event)" >'.get_string('enroll','local_classroom').'</a>';
                }
        }
        return $enrollmentbtn;
    } // end of get_enrollbtn
    private function getcoursecount($planid){
        global $DB;
        $coursecount = $DB->count_records('local_learningplan_courses',array('planid'=>$planid));
        return $coursecount;

    }

 }
