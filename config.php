<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

// added part of post installation
$CFG->debug = E_ALL;
$CFG->debugdisplay = 1;
$CFG->langstringcache = 0;
$CFG->cachetemplates = 0;
$CFG->cachejs = 0;
$CFG->perfdebug = 15;
$CFG->debugpageinfo = 1;
$CFG->showcrondebugging = 1;

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'moodle';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '1',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot   = 'http://localhost:8888/moodle311';
// $CFG->wwwroot   = 'http://192.168.0.199:8888/moodle311';
$CFG->dataroot  = '/Applications/MAMP/data/moodle311';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
