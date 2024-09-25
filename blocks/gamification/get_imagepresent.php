<?php
require_once(dirname(__FILE__).'/../../config.php');
global $DB;
$value = required_param('image', PARAM_INT);
// $value = '621604113';
$isexist = $DB->get_field_select('files', 'id', 'itemid = "'.$value.'" and filename != "."');
$data = $isexist ? $isexist : 0;
echo $data;
// echo 1;