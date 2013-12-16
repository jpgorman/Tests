<?php
switch($_SERVER['SERVER_NAME']) {
	
	// jeanpaul
	case 'jeanpaul.tests.co.uk':
		
		define ("DEBUG_DISPLAY_FLAG", 'on');	//turns all debug code on/off for that area		
		
		//path to cache sql dir
		define("CACHE_PATH", "/Users/jean-paul/websites/tests/test103/tmp/cache/sql/");
		
		/* database */
		define ("SITE_DB_HOST", "localhost");
		define ("SITE_DB", "db_test");
		define ("SITE_USER", "root");
		define ("SITE_PASS", "monkey00");
		define ("SITE_DB_PREFIX", "test_");

	break;
}
	
//error logging
if(DEBUG_DISPLAY_FLAG === 'on'){
	ini_set('display_errors', 1); 
	ini_set('log_errors', 1); 
	ini_set('error_log', 'logs/error_log.txt'); 
	error_reporting(E_ALL & ~E_NOTICE);
	define ("DEBUG_FCN_DISPLAY_FLAG", 1);	//turns all debug code on/off for class functions 0/1
}else{
	ini_set('display_errors', 0); 
	ini_set('log_errors', 0); 
	error_reporting(0);
	define ("DEBUG_FCN_DISPLAY_FLAG", 0);	//turns all debug code on/off for class functions 0/1
}
?>