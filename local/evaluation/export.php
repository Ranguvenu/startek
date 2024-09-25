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
 * @subpackage local_evaluation
 */


require_once("../../config.php");
require_once("lib.php");

// get parameters
$id = required_param('id', PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);

$url = new moodle_url('/local/evaluation/export.php', array('id'=>$id));
if ($action !== false) {
    $url->param('action', $action);
}
$PAGE->set_url($url);

if (! $evaluation = $DB->get_record("local_evaluations", array("id"=>$id))) {
    print_error('invalidcourselocalule');
}

$context = (new \local_evaluation\lib\accesslib())::get_module_context();

require_login();

require_capability('local/evaluation:edititems', $context);

if ($action == 'exportfile') {
    if (!$exportdata = evaluation_get_xml_data($evaluation->id)) {
       // throw new moodle_exception('noquestionsavailable', 'local_evaluation');
    }
    @evaluation_send_xml_data($exportdata, 'evaluation_'.$evaluation->id.'.xml');
    exit;
}

redirect('eval_view.php?id='.$id);
exit;

function evaluation_get_xml_data($evaluationid) {
    global $DB;

    $space = '     ';
    //get all items of the evaluation
    if (!$items = $DB->get_records('local_evaluation_item', array('evaluation'=>$evaluationid), 'position')) {
        return false;
    }

    //writing the header of the xml file including the charset of the currrent used language
    $data = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
    $data .= '<EVALUATION VERSION="200701" COMMENT="XML-Importfile for local/evaluation">'."\n";
    $data .= $space.'<ITEMS>'."\n";

    //writing all the items
    foreach ($items as $item) {
        //start of item
        $data .= $space.$space.'<ITEM TYPE="'.$item->typ.'" REQUIRED="'.$item->required.'">'."\n";

        //start of itemid
        $data .= $space.$space.$space.'<ITEMID>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->id;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of itemid
        $data .= $space.$space.$space.'</ITEMID>'."\n";

        //start of itemtext
        $data .= $space.$space.$space.'<ITEMTEXT>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->name;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of itemtext
        $data .= $space.$space.$space.'</ITEMTEXT>'."\n";

        //start of itemtext
        $data .= $space.$space.$space.'<ITEMLABEL>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->label;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of itemtext
        $data .= $space.$space.$space.'</ITEMLABEL>'."\n";

        //start of presentation
        $data .= $space.$space.$space.'<PRESENTATION>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->presentation;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of presentation
        $data .= $space.$space.$space.'</PRESENTATION>'."\n";

        //start of options
        $data .= $space.$space.$space.'<OPTIONS>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->options;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of options
        $data .= $space.$space.$space.'</OPTIONS>'."\n";

        //start of dependitem
        $data .= $space.$space.$space.'<DEPENDITEM>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->dependitem;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of dependitem
        $data .= $space.$space.$space.'</DEPENDITEM>'."\n";

        //start of dependvalue
        $data .= $space.$space.$space.'<DEPENDVALUE>'."\n";
        //start of CDATA
        $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->dependvalue;
        //end of CDATA
        $data .= ']]>'."\n";
        //end of dependvalue
        $data .= $space.$space.$space.'</DEPENDVALUE>'."\n";

        //end of item
        $data .= $space.$space.'</ITEM>'."\n";
    }

    //writing the footer of the xml file
    $data .= $space.'</ITEMS>'."\n";
    $data .= '</EVALUATION>'."\n";

    return $data;
}

function evaluation_send_xml_data($data, $filename) {
    @header('Content-Type: application/xml; charset=UTF-8');
    @header('Content-Disposition: attachment; filename="'.$filename.'"');
    print($data);
}
