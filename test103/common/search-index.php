<?php
//-------------------------------------------------------------------------------
//--- LIVE ARTICLES LIST ---

if ($_GET["search"] == 1){

	$debug_string['hid_search_token'] = $_POST['hid_search_token'];
	$debug_string['session_form_token'] = $_SESSION['form_token'];
	
	// check the form tokens match and user is submitting from the site
	if($_POST['hid_search_token'] != $_SESSION['form_token'] && empty($_SESSION['f_search_keyword']))
	{
		echo $_POST['txt_search_keyword'];
		//debug
		$submit_error=1;
		$errormsg = "<br />Error: This form cannot be resent";
			
	}else{
	
		$results_array = array();
		
		if(!empty($_POST['txt_search_keyword'])){
			
			$f_search_keyword = $c_search_index->cleanstring_plain($_POST['txt_search_keyword']);
			//write to session
			$_SESSION['f_search_keyword'] = $f_search_keyword;
			$_SESSION['page'] = null;
			
		}elseif(!empty($_SESSION['f_search_keyword']) && empty($_POST['txt_search_keyword'])){
		
			$f_search_keyword = $c_search_index->cleanstring_plain($_SESSION['f_search_keyword']);
		}
		
		$debug_string['f_search_keyword'] = $f_search_keyword;
		$debug_string['_SESSION_f_search_keyword'] = $_SESSION['f_search_keyword'];
		
		if (!empty($_GET['p_page'])):
			$f_page = intval($c_search_index->cleanstring_plain($_GET['p_page']));
		elseif (!empty($_SESSION['page'])):
			//only take the page session var when the category slug isn't coming from the url for the first time
			//place page number into session var
			$f_page = intval($c_search_index->cleanstring_plain($_SESSION['page']));
		endif;
		
		//give empty f_page a default value
		if (empty($f_page)):
			$f_page = 1;
		endif;
		
		//place page number into session var
		$_SESSION['page'] = $f_page;
		
		
			
			
			//--- BY SPECIFIC CATEGORY ---
			if (!empty($f_search_keyword))
			{
				//run query from index object
				$c_search_index->page_number($f_page);
				$c_search_index->records_per_page(6);
				$c_search_index->keyword($f_search_keyword);
				$search_results_array = $c_search_index->search_query();
				
				
				$result = $search_results_array['result'];
				$count = $search_results_array['count'];
			}
			
				
			if ($count==1)
			{
				$num_str = "There is 1 article available";
			}
			else
			{
				$num_str = "There are " . $count . " articles available"; 
			}
			
			if (!empty($article_category_name))
			{
				$num_str .= " matching '".$f_search_keyword."'"; 
			}
			
				
			//PAGING ******************************************************************************************		
			//start a row to hold paging navigation
						
			$results_array = null;
			
			$results_array[0]["navigation"] = $search_results_array['navigation'];
			$results_array[0]["searchkeyword"] = $f_search_keyword;
			$results_array[0]["num_matches"] = $num_str;
			$results_array[0]["num_articles"] = $count;
			$results_array[0]["total_pages"] = $search_results_array['total_pages'];
			
			//generate results numbers
			if ($f_page == 1)
			{
				$pg_i = 1; //initialise page number counter.
			}
			else
			{
				$pg_i = ($c_search_index->records_per_page * ($f_page - 1)) + 1; //continue result number based on current page and number of results per page
			}
				
			// if no matches found from query
			if ($result || ($count > 0))
			{	
				
				$f_page_array = null;
				
				while ($row = $result->fetch_object())
				{
					$cnt = count($f_page_array);
					$f_page_array[$cnt]["result_number"] = $pg_i;
					$f_page_array[$cnt]["article_id"] = $row->association_id;
					switch($row->association_table){
						case "pages":
							$f_page_array[$cnt]["article_url"] = $row->name;
							$f_page_array[$cnt]["article_title"] = ucwords($c_news->htmlsafe($row->link_name));	
							$f_page_array[$cnt]["article_synopsis"] = $c_news->htmlsafe($row->description);
						break;
						case "news":
							$f_page_array[$cnt]["article_url"] = '/diary/article/'.$row->slug;	
							$f_page_array[$cnt]["article_title"] = ucwords($c_news->htmlsafe($row->title));
							$f_page_array[$cnt]["article_synopsis"] = $c_news->htmlsafe($row->synopsis);
						break;
					}
					
					$pg_i ++;
					
				}						
			}// end display product list
			//---ARTICLES LIST ---
			//-------------------------------------------------------------------------------
	}
}
?>