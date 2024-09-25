<?php
namespace local_certification\local;
class userdashboard_content extends \block_userdashboard\content{
	public function userdashboard_menu_content(){
		$returndata = array();
		$returndata['id'] = 'certification_courses';
		$returndata['order'] = 4;
		$returndata['pluginname'] = 'local_certification';
		$returndata['tabname'] = 'inprogress';
		$returndata['status'] = 'inprogress';
		$returndata['class'] = 'userdashboard_menu_link';
		$returndata['iconclass'] = 'fa fa-graduation-cap';
		$returndata['label'] = get_string('certificationtrainings', 'block_userdashboard');
		$returndata['templatename'] = 'local_certification/userdashboard_content';
		return $returndata;
	}
	public static function inprogress_certification($filter_text='', $offset = 0, $limit = 10) {
        global $DB, $USER;
        $sql = "SELECT lc.id,lc.name AS fullname,lc.startdate,lc.enddate,lc.description  FROM {local_certification} AS lc 
                JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
                WHERE lc.status=1 AND lcu.userid={$USER->id} ";
        if(!empty($filter_text)){
            $sql .= " AND lc.name LIKE '%%{$filter_text}%%'";
        }
        $sql .= " ORDER BY lcu.id desc";
        $coursenames = $DB->get_records_sql($sql, array(), $offset, $limit);
        return $coursenames;
    }
    public static function inprogress_certification_count($filter_text = ''){
        global $DB, $USER;
        $sql = "SELECT COUNT(lc.id) 
            FROM {local_certification} AS lc 
            JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
            WHERE lc.status = 1 AND lcu.userid = {$USER->id} ";
        if(!empty($filter_text)){
            $sql .= " AND lc.name LIKE '%%{$filter_text}%%'";
        }
        $certificationCount = $DB->count_records_sql($sql);
        return $certificationCount;
    }

    public static function completed_certification($filter_text='', $offset = 0, $limit = 10) {
        global $DB, $USER;
        $sql = "SELECT lc.id,lc.name AS fullname,lc.startdate,lc.enddate,lc.description  FROM {local_certification} as lc     
                JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
                WHERE  lc.status=4 and lcu.userid={$USER->id} ";
        if(!empty($filter_text)){
            $sql .= " AND lc.name LIKE '%%{$filter_text}%%'";
        }
        $sql .= " ORDER BY lcu.id desc";
        $coursenames = $DB->get_records_sql($sql, array(), $offset, $limit);
        return $coursenames;
    }

    public static function completed_certification_count($filter_text='') {
        global $DB, $USER;
        $sql = "SELECT COUNT(lc.id) 
            FROM {local_certification} as lc     
            JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
            WHERE  lc.status=4 and lcu.userid={$USER->id} ";
        if(!empty($filter_text)){
            $sql .= " AND lc.name LIKE '%%{$filter_text}%%'";
        }
        $certificationCount = $DB->count_records_sql($sql);
        return $certificationCount;
    }

    public static function gettotal_certification($filter_text=''){
        // global $DB, $USER;
        // $sql = "SELECT lc.id,lc.name AS fullname,lc.startdate,lc.enddate,lc.description  FROM {local_certification} AS lc 
        //         JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid
        //         WHERE lc.status IN(1,4) AND lcu.userid={$USER->id} ";
        // $coursenames = $DB->get_records_sql($sql);
        // return count($coursenames);
        global $DB,$USER;
        $sql = "SELECT count(lc.id) FROM {local_certification} AS lc 
        	JOIN {local_certification_users} AS lcu ON lc.id=lcu.certificationid 
        	WHERE lc.status IN(1,4) AND lcu.userid={$USER->id} ";
    	$count = $DB->count_records_sql($sql);
    	return $count; 
    }
}