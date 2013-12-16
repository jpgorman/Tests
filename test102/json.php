<?php
//localise the file system so that files are included correctly
include_once("init.php");


// retrieve json object from registry
$my_json = Registry::get( 'json'); 
$my_json->setId(1);
$json = $my_json->getById();
echo $json;
// output debug
// include_once("debug.php");
?>