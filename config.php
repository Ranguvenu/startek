<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'startek_dev';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'Venu@5599';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'http://localhost/startek';
$CFG->dataroot  = '/var/www/startek';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// $CFG->debugdisplay = 1;             // NOT FOR PRODUCTION SERVERS!

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
