<?php
//display errors/test data if u want - CONTROLLED BY GLOBAL SETTING FOR WHOLE SITE
if (DEBUG_DISPLAY_FLAG == "on")
{

	if(is_object($c_dbobject)){
		$debug_string['c_dbobject'] = $c_dbobject->sql_debug('array');
	}
	if(is_object($c_generic)){
		$debug_string['c_generic'] = $c_generic->sql_debug('array');
	}
	if(is_object($c_news)){
		$debug_string['c_news'] = $c_news->sql_debug('array');	
	}
	if(is_object($c_users)){
		$debug_string['c_users'] = $c_users->sql_debug('array');	
	}
	if(is_object($cms)){
		$debug_string['cms'] = $cms->sql_debug('array');
	}
	if(is_object($c_cms)){
		$debug_string['c_cms'] = $c_cms->sql_debug('array');
	}
	if(is_object($c_directory_categories)){
		$debug_string['c_directory_categories'] = $c_directory_categories->sql_debug('array');	
	}
	if(is_object($c_directory_catalogue)){
		$debug_string['c_directory_catalogue'] = $c_directory_catalogue->sql_debug('array');	
	}
	if(is_object($c_import_database)){
		$debug_string['c_import_database'] = $c_import_database->sql_debug('array');	
	}
	if(is_object($c_search_index)){
		$debug_string['c_search_index'] = $c_search_index->sql_debug('array');	
	}
	if(is_object($my_json)){
		$debug_string['my_json'] = $my_json->sql_debug('array');
	}
	
	
	if(is_object($c_dbobject)){
		$c_dbobject->pr($debug_string);
	}
}
?>