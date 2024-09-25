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
 * Course list block plugin helper
 *
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms
 * @subpackage block_courselister
 */

namespace block_courselister;

use coding_exception;
use ddl_exception;
use dml_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Class plugin
 * @author eabyas  <info@eabyas.in>
 * @package Bizlms
 * @subpackage block_courselister
 */
abstract class plugin {
    /** @var string */
    const COMPONENT = 'block_courselister';

    /** @var int */
    const ENROLLEDCOURSES = 1;
    /** @var int */
    const LEARNINGPLANS = 2;
    /** @var int */
    const LEARNINGPLANSALL = 5;

    /**
     * Are we on courselister Learn?
     * @return bool
     * @throws ddl_exception
     */
    public static function istocourselister() {
        global $DB;
        static $result = null;
        if ($result === null) {
            $result = $DB->get_manager()->table_exists('local_learningplan');
        }
        return $result;
    }

    /**
     * Return course type text
     * @param stdClass $courseobj
     * @return string
     * @throws coding_exception
     * @throws ddl_exception
     */
    public static function coursetype($courseobj) {
        $result = get_string('none', 'core');

        if (self::istocourselister()) {
            $coursetypes = array(self::ENROLLEDCOURSES=>'elearning',self::LEARNINGPLANS=>'learningplans',self::LEARNINGPLANSALL=>'learningplans');
            if (isset($coursetypes[$courseobj->coursetype])) {

                $result = get_string($coursetypes[$courseobj->coursetype], plugin::COMPONENT);
            }
        }
        return $result;
    }
    public static function enrol_get_my_courses($stable,$filtervalues) {
        global $DB, $USER, $CFG;
        $fields = $stable->fields;
        $sort = $stable->sort; 
        $limit = (isset($stable->length)) ? $stable->length : 0 ; 
        $courseids = [];
        $allaccessible = false;
        $offset = 0; 
        $excludecourses = [];
        // Re-Arrange the course sorting according to the admin settings.
        $sort = enrol_get_courses_sortingsql($sort);

        // Guest account does not have any enrolled courses.
        if (!$allaccessible && (isguestuser() or !isloggedin())) {
            return array();
        }

        $basefields = array('id', 'category', 'sortorder',
                            'shortname', 'fullname', 'idnumber',
                            'startdate', 'visible',
                            'groupmode', 'groupmodeforce', 'cacherev');

        if (empty($fields)) {
            $fields = $basefields;
        } else if (is_string($fields)) {
            // turn the fields from a string to an array
            $fields = explode(',', $fields);
            $fields = array_map('trim', $fields);
            $fields = array_unique(array_merge($basefields, $fields));
        } else if (is_array($fields)) {
            $fields = array_unique(array_merge($basefields, $fields));
        } else {
            throw new coding_exception('Invalid $fields parameter in enrol_get_my_courses()');
        }
        if (in_array('*', $fields)) {
            $fields = array('*');
        }

        $orderby = "";
        $sort    = trim($sort);
        $sorttimeaccess = false;
        $allowedsortprefixes = array('c', 'ul', 'ue');
        if (!empty($sort)) {
            $rawsorts = explode(',', $sort);
            $sorts = array();
            foreach ($rawsorts as $rawsort) {
                $rawsort = trim($rawsort);
                if (preg_match('/^ul\.(\S*)\s(asc|desc)/i', $rawsort, $matches)) {
                    if (strcasecmp($matches[2], 'asc') == 0) {
                        $sorts[] = 'COALESCE(ul.' . $matches[1] . ', 0) ASC';
                    } else {
                        $sorts[] = 'COALESCE(ul.' . $matches[1] . ', 0) DESC';
                    }
                    $sorttimeaccess = true;
                } else if (strpos($rawsort, '.') !== false) {
                    $prefix = explode('.', $rawsort);
                    if (in_array($prefix[0], $allowedsortprefixes)) {
                        $sorts[] = trim($rawsort);
                    } else {
                        throw new coding_exception('Invalid $sort parameter in enrol_get_my_courses()');
                    }
                } else {
                    $sorts[] = 'c.'.trim($rawsort);
                }
            }
            $sort = implode(',', $sorts);
            $orderby = "ORDER BY $sort";
        }

        $wheres = array("c.id <> :siteid");
        $params = array('siteid'=>SITEID);

        if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
            // list _only_ this course - anything else is asking for trouble...
            $wheres[] = "courseid = :loginas";
            $params['loginas'] = $USER->loginascontext->instanceid;
        }

        $coursefields = 'c.' .join(',c.', $fields);
        $ccselect = ', ' . \context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $params['contextlevel'] = CONTEXT_COURSE;
        $wheres = implode(" AND ", $wheres);

        $timeaccessselect = "";
        $timeaccessjoin = "";

        if (!empty($courseids)) {
            list($courseidssql, $courseidsparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
            $wheres = sprintf("%s AND c.id %s", $wheres, $courseidssql);
            $params = array_merge($params, $courseidsparams);
        }

        if (!empty($excludecourses)) {
            list($courseidssql, $courseidsparams) = $DB->get_in_or_equal($excludecourses, SQL_PARAMS_NAMED, 'param', false);
            $wheres = sprintf("%s AND c.id %s", $wheres, $courseidssql);
            $params = array_merge($params, $courseidsparams);
        }

        $courseidsql = "";
        // Logged-in, non-guest users get their enrolled courses.
        if (!isguestuser() && isloggedin()) {
            $courseidsql .= "
                    SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid1)
                     WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1
                           AND (ue.timeend = 0 OR ue.timeend > :now2) AND e.enrol IN('self','manual','auto') ";
            $params['userid1'] = $USER->id;
            $params['active'] = ENROL_USER_ACTIVE;
            $params['enabled'] = ENROL_INSTANCE_ENABLED;
            $params['now1'] = round(time(), -2); // Improves db caching.
            $params['now2'] = $params['now1'];

            if ($sorttimeaccess) {
                $params['userid2'] = $USER->id;
                $timeaccessselect = ', ul.timeaccess as lastaccessed';
                $timeaccessjoin = "LEFT JOIN {user_lastaccess} ul ON (ul.courseid = c.id AND ul.userid = :userid2)";
            }
        }

        // When including non-enrolled but accessible courses...
        if ($allaccessible) {
            if (is_siteadmin()) {
                // Site admins can access all courses.
                $courseidsql = "SELECT DISTINCT c2.id AS courseid FROM {course} c2";
            } else {
                // If we used the enrolment as well, then this will be UNIONed.
                if ($courseidsql) {
                    $courseidsql .= " UNION ";
                }

                // Include courses with guest access and no password.
                $courseidsql .= "
                        SELECT DISTINCT e.courseid
                          FROM {enrol} e
                         WHERE e.enrol = 'guest' AND e.password = :emptypass AND e.status = :enabled2 AND e.enrol IN('self','manual','auto') ";
                $params['emptypass'] = '';
                $params['enabled2'] = ENROL_INSTANCE_ENABLED;

                // Include courses where the current user is currently using guest access (may include
                // those which require a password).
                $courseids = [];
                $accessdata = get_user_accessdata($USER->id);
                foreach ($accessdata['ra'] as $contextpath => $roles) {
                    if (array_key_exists($CFG->guestroleid, $roles)) {
                        // Work out the course id from context path.
                        $context = context::instance_by_id(preg_replace('~^.*/~', '', $contextpath));
                        if ($context instanceof context_course) {
                            $courseids[$context->instanceid] = true;
                        }
                    }
                }

                // Include courses where the current user has moodle/course:view capability.
                $courses = get_user_capability_course('moodle/course:view', null, false);
                if (!$courses) {
                    $courses = [];
                }
                foreach ($courses as $course) {
                    $courseids[$course->id] = true;
                }

                // If there are any in either category, list them individually.
                if ($courseids) {
                    list ($allowedsql, $allowedparams) = $DB->get_in_or_equal(
                            array_keys($courseids), SQL_PARAMS_NAMED);
                    $courseidsql .= "
                            UNION
                           SELECT DISTINCT c3.id AS courseid
                             FROM {course} c3
                            WHERE c3.id $allowedsql";
                    $params = array_merge($params, $allowedparams);
                }
            }
        }

        // Note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why
        // we have the subselect there.
        $countsql = "SELECT COUNT(c.id) ";
        $fromsql= "SELECT $coursefields $ccselect $timeaccessselect ";
        $sql = " FROM {course} c
                  JOIN ($courseidsql) en ON (en.courseid = c.id)
               $timeaccessjoin
               $ccjoin
                 WHERE $wheres
              ";
        if (!empty($stable->search)) {
            $fields = array(
                0 => 'c.fullname',
            );
            $fields = implode(" LIKE '%" . $stable->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $stable->search . "%' ";
            $sql .= " AND ($fields) ";
        }
       

        try {
            $coursescount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " $orderby ";

                $courses = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $coursescount = 0;
        }
        return compact('courses', 'coursescount');
    }
    public static function dateDifference($start_date, $end_date)
    {
        // calulating the difference in timestamps 
        $diff = $start_date - $end_date;
         
        // 1 day = 24 hours 
        // 24 * 60 * 60 = 86400 seconds
        return ceil(abs($diff / 86400)). " Days ";
    }
    public static function course_modulescount($courseid)
    {
        global $DB, $USER, $CFG;
        $sql="SELECT COUNT(c_modules.id)
                FROM {course_modules} c_modules
                WHERE c_modules.course = :courseid AND c_modules.visible = 1 ";

        $course_modulescount=$DB->count_records_sql($sql,array('courseid'=>$courseid));

        return $course_modulescount;        
    }
    public static function enrol_get_my_learningplans($stable,$filtervalues) {
        global $DB, $USER, $CFG;

        $params=array();

        $countsql = "SELECT COUNT(llp.id) ";
        $fromsql = "SELECT llp.id,llp.name as fullname, llp.description as summary,llp.enddate,llp.startdate ";
        $sql = " from {local_learningplan} llp JOIN {local_learningplan_user} as lla on llp.id=lla.planid where userid={$USER->id} and lla.completiondate is NULL and status is NULL and llp.visible=1";
    

        if (!empty($stable->search)) {
            $fields = array(
                0 => 'llp.name',
            );
            $fields = implode(" LIKE '%" . $stable->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $stable->search . "%' ";
            $sql .= " AND ($fields) ";
        }
        try {
            $learningplanscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $sql .= " ORDER BY lla.id desc";
                $learningplans = $DB->get_records_sql($fromsql . $sql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $learningplanscount = 0;
        }
        return compact('learningplans', 'learningplanscount');
    }
    public static function get_all_learningplans($stable,$filtervalues) {
        global $DB, $USER, $CFG;

        $systemcontext = \context_system::instance();

        $params=array();

        $countsql = "SELECT COUNT(l.id) ";

        $fromsql="SELECT l.id,l.name as fullname, l.description as summary,l.enddate,l.startdate ";

        if(is_siteadmin()){
            $sql=" FROM {local_learningplan} AS l WHERE l.id > 0 ";

            $ordersql= " ORDER BY l.id DESC";
         
            
        }elseif(has_capability('local/costcenter:manage_ownorganization',$systemcontext)){
            
            $data=(new \local_learningplan\render\open)->userdetails();

            $sql=" FROM {local_learningplan} AS l WHERE concat(',',l.costcenter,',') LIKE concat('%,',{$USER->open_costcenterid},',%')";
            
            $ordersql= " ORDER BY l.id DESC";
                  
        }elseif(has_capability('local/costcenter:manage_owndepartments',$systemcontext) ){
           
            $sql=" FROM {local_learningplan} AS l WHERE concat(',',l.costcenter,',') LIKE concat('%,',{$USER->open_costcenterid},',%') AND CONCAT(',',l.department,',') LIKE CONCAT('%,',{$USER->open_departmentid},',%') AND l.id > 0 ";
          
            $ordersql= "  ORDER BY l.id DESC";

        }else{
            $data=(new \local_learningplan\render\open)->userdetails();
            
            $sql=" FROM {local_learningplan} AS l WHERE
            CONCAT(',',l.costcenter,',') LIKE CONCAT('%,',$data->open_costcenterid,',%')
            AND CONCAT(',',l.department,',') LIKE CONCAT('%,',$data->open_departmentid,',%')
            AND CONCAT(',',l.subdepartment,',') LIKE CONCAT('%,',$data->open_subdepartment,',%')
            AND l.id > 0 AND l.visible=1 ";

            $ordersql= '  ORDER BY l.timemodified DESC';
        }
        if (!empty($stable->search)) {
            $fields = array(
                0 => 'l.name',
            );
            $fields = implode(" LIKE '%" . $stable->search . "%' OR ", $fields);
            $fields .= " LIKE '%" . $stable->search . "%' ";
            $sql .= " AND ($fields) ";
        }
        try {
            $alllearningplanscount = $DB->count_records_sql($countsql . $sql, $params);
            if ($stable->thead == false) {
                $alllearningplans = $DB->get_records_sql($fromsql . $sql.$ordersql, $params, $stable->start, $stable->length);
            }
        } catch (dml_exception $ex) {
            $alllearningplanscount = 0;
        }
         return compact('alllearningplans', 'alllearningplanscount');
    }

}
