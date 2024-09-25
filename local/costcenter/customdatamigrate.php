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
 * @package BizLMS
 * @subpackage local_costcenter
 */

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
require __DIR__ . '/../../config.php';

global $DB;

/*===========1.local coscenter====================*/

// coscenter table missing course categories mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_costcenter} SET category=(SELECT cat.id FROM {course_categories} as cat WHERE cat.idnumber=shortname) WHERE category IS NULL";
$DB->execute($sql);

/*===========2.local users====================*/

// user table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {user} AS ut
SET ut.open_path = (
 SELECT CONCAT('/',u.open_costcenterid,'/',u.open_departmentid,'/',u.open_subdepartment)
 FROM (SELECT id,open_costcenterid, open_departmentid, open_subdepartment FROM {user}) AS u
 WHERE u.id = ut.id
)
WHERE ut.open_costcenterid > 0 AND ut.open_departmentid > 0 AND ut.open_subdepartment > 0";

$DB->execute($sql);

$sql = "UPDATE {user} AS ut
SET ut.open_path = (
 SELECT CONCAT('/',u.open_costcenterid,'/',u.open_departmentid)
 FROM (SELECT id,open_costcenterid, open_departmentid FROM {user}) AS u
 WHERE u.id = ut.id
)
WHERE ut.open_costcenterid > 0 AND ut.open_departmentid > 0 AND (ut.open_subdepartment = 0 OR ut.open_subdepartment = '' OR ut.open_subdepartment IS NULL )";

$DB->execute($sql);

$sql = "UPDATE {user} AS ut
SET ut.open_path = (
 SELECT CONCAT('/',u.open_costcenterid)
 FROM (SELECT id,open_costcenterid FROM {user}) AS u
 WHERE u.id = ut.id
)
WHERE ut.open_costcenterid > 0 AND (ut.open_departmentid = 0 OR ut.open_departmentid = '' OR ut.open_departmentid IS NULL ) AND (ut.open_subdepartment = 0 OR ut.open_subdepartment = '' OR ut.open_subdepartment IS NULL)";

$DB->execute($sql);

/*===========3.local courses====================*/

// course table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {course} AS ct
SET ct.open_path = (
 SELECT CONCAT('/',c.open_costcenterid,'/',c.open_departmentid,'/',c.open_subdepartment)
 FROM (SELECT id,open_costcenterid, open_departmentid, open_subdepartment FROM {course}) AS c
 WHERE c.id = ct.id
)
WHERE ct.open_costcenterid > 0 AND ct.open_departmentid > 0 AND ct.open_subdepartment > 0 AND ct.open_departmentid != ct.open_subdepartment";

$DB->execute($sql);

$sql = "UPDATE {course} AS ct
SET ct.open_path = (
 SELECT CONCAT('/',c.open_costcenterid,'/',c.open_departmentid)
 FROM (SELECT id,open_costcenterid, open_departmentid FROM {course}) AS c
 WHERE c.id = ct.id
)
WHERE ct.open_costcenterid > 0 AND ct.open_departmentid > 0 AND (ct.open_subdepartment = 0 OR ct.open_subdepartment = '' OR ct.open_subdepartment IS NULL OR ct.open_departmentid = ct.open_subdepartment )";

$DB->execute($sql);

$sql = "UPDATE {course} AS ct
SET ct.open_path = (
 SELECT CONCAT('/',c.open_costcenterid)
 FROM (SELECT id,open_costcenterid FROM {course}) AS c
 WHERE c.id = ct.id
)
WHERE ct.open_costcenterid > 0 AND (ct.open_departmentid = 0 OR ct.open_departmentid = '' OR ct.open_departmentid IS NULL ) AND (ct.open_subdepartment = 0 OR ct.open_subdepartment = '' OR ct.open_subdepartment IS NULL)";

$DB->execute($sql);

/*===========4.local groups====================*/

// group table open_path mapping data migration from system context to category context upgradation.

// $sql = "UPDATE {local_groups} AS gt
// SET gt.open\_path = (
// SELECT CONCAT('/',g.costcenterid,'/',g.departmentid)
// FROM (SELECT id,costcenterid, departmentid FROM mdl\_local\_groups) AS g
// WHERE g.id = gt.id
// )
// WHERE gt.costcenterid > 0 AND gt.departmentid > 0 AND gt.departmentid != '' AND gt.departmentid IS NOT NULL ; ";

// $DB->execute($sql);

// $sql = "UPDATE {local_groups} AS gt
// SET gt.open_path = (
//  SELECT CONCAT('/',g.costcenterid)
//  FROM (SELECT id,costcenterid FROM {local_groups}) AS g
//  WHERE g.id = gt.id
// )
// WHERE gt.costcenterid > 0 AND (gt.departmentid = 0 OR gt.departmentid = '' OR gt.departmentid IS NULL ) ";

// $DB->execute($sql);

/*===========5.local classroom====================*/

// classroom table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_classroom} AS clt
SET clt.open_path = (
 SELECT CONCAT('/',cl.costcenter,'/',cl.department,'/',cl.subdepartment)
 FROM (SELECT id,costcenter, department, subdepartment FROM {local_classroom}) AS cl
 WHERE cl.id = clt.id
)
WHERE clt.costcenter > 0 AND clt.department > 0 AND clt.subdepartment > 0";

$DB->execute($sql);

$sql = "UPDATE {local_classroom} AS clt
SET clt.open_path = (
 SELECT CONCAT('/',cl.costcenter,'/',cl.department)
 FROM (SELECT id,costcenter, department FROM {local_classroom}) AS cl
 WHERE cl.id = clt.id
)
WHERE clt.costcenter > 0 AND clt.department > 0 AND (clt.subdepartment = -1 OR clt.subdepartment = 0 OR clt.subdepartment = '' OR clt.subdepartment IS NULL )";

$DB->execute($sql);

$sql = "UPDATE {local_classroom} AS clt
SET clt.open_path = (
 SELECT CONCAT('/',cl.costcenter)
 FROM (SELECT id,costcenter FROM {local_classroom}) AS cl
 WHERE cl.id = clt.id
)
WHERE clt.costcenter > 0 AND (clt.department = -1 OR clt.department = 0 OR clt.department = '' OR clt.department IS NULL ) AND (clt.subdepartment = -1 OR clt.subdepartment = 0 OR clt.subdepartment = '' OR clt.subdepartment IS NULL ) ";

$DB->execute($sql);

/*===========6.local evaluations====================*/

// evaluations table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_evaluations} AS evt
SET evt.open_path = (
 SELECT CONCAT('/',ev.costcenterid,'/',ev.departmentid,'/',ev.subdepartment)
 FROM (SELECT id,costcenterid, departmentid, subdepartment FROM {local_evaluations}) AS ev
 WHERE ev.id = evt.id
)
WHERE evt.costcenterid > 0 AND evt.departmentid > 0 AND evt.subdepartment > 0";

$DB->execute($sql);

$sql = "UPDATE {local_evaluations} AS evt
SET evt.open_path = (
 SELECT CONCAT('/',ev.costcenterid,'/',ev.departmentid)
 FROM (SELECT id,costcenterid, departmentid FROM {local_evaluations}) AS ev
 WHERE ev.id = evt.id
)
WHERE evt.costcenterid > 0 AND evt.departmentid > 0 AND (evt.subdepartment = -1 OR evt.subdepartment = 0 OR evt.subdepartment = '' OR evt.subdepartment IS NULL )";

$DB->execute($sql);

$sql = "UPDATE {local_evaluations} AS evt
SET evt.open_path = (
 SELECT CONCAT('/',ev.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_evaluations}) AS ev
 WHERE ev.id = evt.id
)
WHERE evt.costcenterid > 0 AND (evt.departmentid = -1 OR evt.departmentid = 0 OR evt.departmentid = '' OR evt.departmentid IS NULL ) AND (evt.subdepartment = -1 OR evt.subdepartment = 0 OR evt.subdepartment = '' OR evt.subdepartment IS NULL ) ";

$DB->execute($sql);

$sql = "UPDATE {local_evaluation_template} AS evlt
SET evlt.open_path = (
 SELECT CONCAT('/',evl.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_evaluation_template}) AS evl
 WHERE evl.id = evlt.id
)
WHERE evlt.costcenterid > 0 ";

$DB->execute($sql);

/*===========7.local learningplan====================*/

// learningplan table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_learningplan} AS lpt
SET lpt.open_path = (
 SELECT CONCAT('/',lp.costcenter,'/',lp.department,'/',lp.subdepartment)
 FROM (SELECT id,costcenter, department, subdepartment FROM {local_learningplan}) AS lp
 WHERE lp.id = lpt.id
)
WHERE lpt.costcenter > 0 AND lpt.department > 0 AND lpt.subdepartment > 0";

$DB->execute($sql);

$sql = "UPDATE {local_learningplan} AS lpt
SET lpt.open_path = (
 SELECT CONCAT('/',lp.costcenter,'/',lp.department)
 FROM (SELECT id,costcenter, department FROM {local_learningplan}) AS lp
 WHERE lp.id = lpt.id
)
WHERE lpt.costcenter > 0 AND lpt.department > 0 AND (lpt.subdepartment = -1 OR lpt.subdepartment = 0 OR lpt.subdepartment = '' OR lpt.subdepartment IS NULL )";

$DB->execute($sql);

$sql = "UPDATE {local_learningplan} AS lpt
SET lpt.open_path = (
 SELECT CONCAT('/',lp.costcenter)
 FROM (SELECT id,costcenter FROM {local_learningplan}) AS lp
 WHERE lp.id = lpt.id
)
WHERE lpt.costcenter > 0 AND (lpt.department = -1 OR lpt.department = 0 OR lpt.department = '' OR lpt.department IS NULL ) AND (lpt.subdepartment = -1 OR lpt.subdepartment = 0 OR lpt.subdepartment = '' OR lpt.subdepartment IS NULL ) ";

$DB->execute($sql);

/*===========8.local notification====================*/

// notification table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_notification_info} AS ntf
SET ntf.open_path = (
 SELECT CONCAT('/',nt.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_notification_info}) AS nt
 WHERE nt.id = ntf.id
)
WHERE ntf.costcenterid > 0  ";

$DB->execute($sql);

/*===========9.local program====================*/

// program table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_program} AS lpt
SET lpt.open_path = (
 SELECT CONCAT('/',lp.costcenter,'/',lp.department,'/',lp.subdepartment)
 FROM (SELECT id,costcenter, department, subdepartment FROM {local_program}) AS lp
 WHERE lp.id = lpt.id
)
WHERE lpt.costcenter > 0 AND lpt.department > 0 AND lpt.subdepartment > 0";

$DB->execute($sql);

$sql = "UPDATE {local_program} AS lpt
SET lpt.open_path = (
 SELECT CONCAT('/',lp.costcenter,'/',lp.department)
 FROM (SELECT id,costcenter, department FROM {local_program}) AS lp
 WHERE lp.id = lpt.id
)
WHERE lpt.costcenter > 0 AND lpt.department > 0 AND (lpt.subdepartment = -1 OR lpt.subdepartment = 0 OR lpt.subdepartment = '' OR lpt.subdepartment IS NULL )";

$DB->execute($sql);

$sql = "UPDATE {local_program} AS lpt
SET lpt.open_path = (
 SELECT CONCAT('/',lp.costcenter)
 FROM (SELECT id,costcenter FROM {local_program}) AS lp
 WHERE lp.id = lpt.id
)
WHERE lpt.costcenter > 0 AND (lpt.department = -1 OR lpt.department = 0 OR lpt.department = '' OR lpt.department IS NULL ) AND (lpt.subdepartment = -1 OR lpt.subdepartment = 0 OR lpt.subdepartment = '' OR lpt.subdepartment IS NULL ) ";

$DB->execute($sql);

/*===========10.local skillrepository====================*/

// skillrepository table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_skill} AS sklt
SET sklt.open_path = (
 SELECT CONCAT('/',skl.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_skill}) AS skl
 WHERE skl.id = sklt.id
)
WHERE sklt.costcenterid > 0 ";

$DB->execute($sql);

$sql = "UPDATE {local_skill_categories} AS sklt
SET sklt.open_path = (
 SELECT CONCAT('/',skl.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_skill_categories}) AS skl
 WHERE skl.id = sklt.id
)
WHERE sklt.costcenterid > 0 ";

$DB->execute($sql);

$sql = "UPDATE {local_course_levels} AS sklt
SET sklt.open_path = (
 SELECT CONCAT('/',skl.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_course_levels}) AS skl
 WHERE skl.id = sklt.id
)
WHERE sklt.costcenterid > 0 ";

$DB->execute($sql);

/*===========11.blocks trending_modules====================*/

// trending_modules table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {block_trending_modules} AS tmdt
SET tmdt.open_path = (
 SELECT CONCAT('/',tmd.costcenterid)
 FROM (SELECT id,costcenterid FROM {block_trending_modules}) AS tmd
 WHERE tmd.id = tmdt.id
)
WHERE tmdt.costcenterid > 0 ";

$DB->execute($sql);

/*===========12.local onlinetests====================*/

// onlinetests table open_path mapping data migration from system context to category context upgradation.

$sql = "UPDATE {local_onlinetests} AS onlt
SET onlt.open_path = (
 SELECT CONCAT('/',onl.costcenterid,'/',onl.departmentid,'/',onl.subdepartment)
 FROM (SELECT id,costcenterid, departmentid, subdepartment FROM {local_onlinetests}) AS onl
 WHERE onl.id = onlt.id
)
WHERE onlt.costcenterid > 0 AND onlt.departmentid > 0 AND onlt.subdepartment > 0";

$DB->execute($sql);

$sql = "UPDATE {local_onlinetests} AS onlt
SET onlt.open_path = (
 SELECT CONCAT('/',onl.costcenterid,'/',onl.departmentid)
 FROM (SELECT id,costcenterid, departmentid FROM {local_onlinetests}) AS onl
 WHERE onl.id = onlt.id
)
WHERE onlt.costcenterid > 0 AND onlt.departmentid > 0 AND (onlt.subdepartment = -1 OR onlt.subdepartment = 0 OR onlt.subdepartment = '' OR onlt.subdepartment IS NULL )";

$DB->execute($sql);

$sql = "UPDATE {local_onlinetests} AS onlt
SET onlt.open_path = (
 SELECT CONCAT('/',onl.costcenterid)
 FROM (SELECT id,costcenterid FROM {local_onlinetests}) AS onl
 WHERE onl.id = onlt.id
)
WHERE onlt.costcenterid > 0 AND (onlt.departmentid = -1 OR onlt.departmentid = 0 OR onlt.departmentid = '' OR onlt.departmentid IS NULL ) AND (onlt.subdepartment = -1 OR onlt.subdepartment = 0 OR onlt.subdepartment = '' OR onlt.subdepartment IS NULL ) ";

$DB->execute($sql);

/*===========13.tool certificate_templates====================*/

// local certificate table open_path mapping data migration from system context to category context upgradation.
//

$sql = "UPDATE {local_certificate} AS crtt
SET crtt.open_path = (
 SELECT CONCAT('/',crt.costcenter)
 FROM (SELECT id,costcenter FROM {local_certificate}) AS crt
 WHERE crt.id = crtt.id
)
WHERE crtt.costcenter > 0 AND (crtt.department = -1 OR crtt.department = 0 OR crtt.department = '' OR crtt.department IS NULL ) AND (crtt.subdepartment = -1 OR crtt.subdepartment = 0 OR crtt.subdepartment = '' OR crtt.subdepartment IS NULL )";

$DB->execute($sql);

// tool certificate_templates table open_path mapping and data migration.

$DB->execute("INSERT INTO {tool_certificate_templates} (name, contextid, shared,timecreated,timemodified,open_path)
SELECT name,1,0,timecreated, timemodified,open_path FROM {local_certificate}");

// tool certificate_elements table open_path mapping and data migration.

$DB->execute("INSERT INTO {tool_certificate_elements} (pageid, name, element,data,font,fontsize,colour,posx,posy,width,refpoint,sequence,timecreated,timemodified)
SELECT pageid, name, element,data,font,fontsize,colour,posx,posy,width,refpoint,sequence,timecreated,timemodified FROM {local_certificate_elements}");

// tool certificate_pages table open_path mapping and data migration.

$DB->execute("INSERT INTO {tool_certificate_pages} (templateid, width, height,leftmargin,rightmargin,sequence,timecreated,timemodified) SELECT certificateid, width, height,leftmargin,rightmargin,sequence,timecreated,timemodified FROM {local_certificate_pages}");

// tool certificate_issues table open_path mapping and data migration.

$DB->execute("INSERT INTO {tool_certificate_issues} (userid, templateid, emailed,timecreated,component,courseid,moduletype,moduleid)
SELECT userid,certificateid,emailed,timecreated,'tool_certificate',0,moduletype,moduleid FROM {local_certificate_issues}");

// tool certificate_issues table genrate code for migrated data.

$sql = "UPDATE {tool_certificate_issues} SET code = CONCAT(CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26)), CHAR(FLOOR(65 + RAND() * 26))) WHERE code IS NULL ";

$DB->execute($sql);

//Need update exsiting tool certifacte templates from front end.
//And run regenrate file custom update file (regenrate_custom_file.php).

// announcement table open_path data.

$sql = "UPDATE {block_announcement} AS ba
		SET ba.open_path = (
		 SELECT CONCAT('/',bab.costcenterid,'/',bab.departmentid)
		 FROM (SELECT id,costcenterid, departmentid FROM {block_announcement}) AS bab
		 WHERE bab.id = ba.id
		)
		WHERE ba.costcenterid > 0 AND ba.departmentid > 0";

$DB->execute($sql);

$sql = "UPDATE {block_announcement} AS onlt
SET onlt.open_path = (
 SELECT CONCAT('/',onl.costcenterid)
 FROM (SELECT id,costcenterid FROM {block_announcement}) AS onl
 WHERE onl.id = onlt.id
)
WHERE onlt.costcenterid > 0 AND (onlt.departmentid = -1 OR onlt.departmentid = 0 OR onlt.departmentid = '' OR onlt.departmentid IS NULL ) ";

$DB->execute($sql);

//course format changes

$sql = "UPDATE {course} SET format = 'topics' WHERE format = 'tabtopics'";
$DB->execute($sql);

//course certificate update

$sql = 'UPDATE {course} AS c
	    SET c.open_certificateid = (
	    SELECT tc.id
	    FROM {local_certificate} AS lc, {tool_certificate_templates} AS tc
	    WHERE lc.name = tc.name AND c.open_certificateid = lc.id
	    ) WHERE c.open_certificateid IS NOT NULL';
$DB->execute($sql);

//classroom certificate update

$sql = 'UPDATE {local_classroom} AS c
    	SET c.certificateid = (
	    SELECT tc.id
	    FROM {local_certificate} AS lc, {tool_certificate_templates} AS tc
	    WHERE lc.name = tc.name AND c.certificateid = lc.id
	    ) WHERE c.certificateid IS NOT NULL';
$DB->execute($sql);

//learningplan certificate update

$sql = 'UPDATE {local_learningplan} AS c
    	SET c.certificateid = (
	    SELECT tc.id
	    FROM {local_certificate} AS lc, {tool_certificate_templates} AS tc
	    WHERE lc.name = tc.name AND c.certificateid = lc.id
	    ) WHERE c.certificateid IS NOT NULL';
$DB->execute($sql);

//program certificate update

$sql = 'UPDATE {local_program} AS c SET c.certificateid = ( SELECT tc.id FROM {local_certificate} AS lc, {tool_certificate_templates} AS tc WHERE lc.name = tc.name AND c.certificateid = lc.id ) WHERE c.certificateid IS NOT NULL';
$DB->execute($sql);

//onlinetests certificate update

$sql = 'UPDATE {local_onlinetests} AS lo SET lo.certificateid = ( SELECT tc.id FROM {local_certificate} AS lc, {tool_certificate_templates} AS tc WHERE lc.name = tc.name AND lo.certificateid = lc.id ) WHERE lo.certificateid IS NOT NULL';
$DB->execute($sql);
