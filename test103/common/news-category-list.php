<?php
//initialise
$f_category_array = array();

$c_dbobject->set_select();
$c_dbobject->set_from("article_categories");

$c_dbobject->rows_per_page = null;
$c_dbobject->set_orderby("name ASC");
$c_dbobject->rows_per_page = null;
$c_dbobject->set_limit("10");

$result = $c_dbobject->get_data();
$count = $c_dbobject->numrows;//returns the total number of rows generated

	
// if no matches found from query
if ($result || ($count > 0))
{	
	while ($row = $result->fetch_object())
	{
		$cnt = count($f_category_array);
		$f_category_array[$cnt]["article_id"] = $row->id;
		$f_category_array[$cnt]["article_url"] = $row->slug;
		
		$f_category_array[$cnt]["article_title"] = $c_news->htmlsafe_input($row->name);
	}						
}//endlist
?>