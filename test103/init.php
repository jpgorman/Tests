<?php
//set script encoding
mb_language('uni');
mb_internal_encoding('UTF-8');
header ('Content-type: text/html; charset=utf-8');

//frontend or CMS
$site_area = "";

//add to debug array
$debug_string = array();

//set the include path for the duration of the script to site root
set_include_path($_SERVER['DOCUMENT_ROOT']);

//include files
include_once("config/variables.php");
include_once("classes/class.registry.php");

//classes

//use autoload to load in classes based on class name e.g. class.db_object.php
function __autoload($class_name) {
   include_once("classes/class." . $class_name . ".php");
}


//*** USE CLASS ***
$c_dbobject = new db_object();
//pass a reference to the registry class
Registry::set( 'db', $c_dbobject ); 

$c_cache = new cache();
//pass a reference to the registry class
Registry::set( 'cache', $c_cache ); 

$c_json = json::get_singleton();
//pass a reference to the registry class
Registry::set( 'json', $c_json ); 
?>