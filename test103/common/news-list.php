<?php
//-------------------------------------------------------------------------------
//--- LIVE ARTICLES LIST ---

$results_array = array();
$f_page_array = array();

$f_article_category_slug = $c_news->cleanstring_input($_REQUEST['c_slug']);
$_SESSION['category_slug'] = $f_article_category_slug;

$testdisplayvar.= "<br />f_article_category_slug:".$f_article_category_slug;
$testdisplayvar.= "<br />_SESSION['category_slug']:".$_SESSION['category_slug'];

if (!empty($_REQUEST['p_page'])):
	$f_page = intval($c_news->cleanstring_input($_REQUEST['p_page']));
elseif (!empty($_SESSION['page'])):
	//only take the page session var when the category slug isn't coming from the url for the first time
	if (empty($_REQUEST['c_slug'])):
		//place page number into session var
		$f_page = intval($c_news->cleanstring_input($_SESSION['page']));
	endif;
endif;

//give empty f_page a default value
if (empty($f_page)):
	$f_page = 1;
endif;

//place page number into session var
$_SESSION['page'] = $f_page;


	
	
	//--- BY SPECIFIC CATEGORY ---
	if (!empty($f_article_category_slug))
	{
		//get category id
		$c_news->set_select("SELECT id, slug, name");
		$c_news->set_from("article_categories");
		$c_news->set_where("slug = '$f_article_category_slug'");
		
		$result = $c_news->get_data(0);
		$count = $c_news->numrows;//returns the total number of rows generated
		
		// if no matches found from query
		if (!$result || ($count == 0))
		{
			$error = "<p>No news articles currently exist matching your selection</p>";
		}	
		else
		{
			// display product list
			while($row = $result->fetch_object())
			{		
				$article_category_id = $row->id;
				$article_category_slug = $row->slug;					
				$article_category_name = $row->name;
				if ($article_category_name != ""):
					$article_category_disp = $article_category_name;
				endif;
			}
			
			if (!empty($article_category_id) && !empty($article_category_slug))
			{	
				//get article that matches the category slug
				$c_news->set_select("SELECT *, date_format(a.timestamp, '%H:%i %W, %d %M %Y') as timestamp_format, date_format(a.event_date, '%W, %d %M %Y') AS published_date_format, a.slug AS article_slug, c.name AS article_category");				
				$c_news->set_from("news_articles a LEFT JOIN ".$c_news->DB_PREFIX."article_categories c ON a.article_category_id = c.id");
				$c_news->set_where("a.article_category_id = '$article_category_id'");
				
				//switch to archived mode or not
				if($_SESSION['news_type'] == 1){
					$c_news->set_where("a.archived = 0");
				}
				elseif($_SESSION['news_type'] == 2){
					$c_news->set_where("a.archived = 1");
				}
				
				$c_news->set_where("a.content <> ''");	
				$c_news->set_orderby("event_date ASC, published_date ASC, title ASC");
				
				$c_news->set_page_num($f_page);//default value is 1
				$c_news->set_rows_per_page($records_per_page_live);
				
				$result = $c_news->get_data(0);
				$count = $c_news->numrows;//returns the total number of rows generated
				$total_pages = $c_news->get_total_pages();//returns total number of pages
				
				$c_news->current_page(CURRENT_PAGE.'/category/'.$article_category_slug);
			}
		}
	}
	else
	{
		$c_news->set_select("SELECT *, date_format(a.timestamp, '%H:%i %W, %d %M %Y') as timestamp_format, date_format(a.event_date, '%W, %d %M %Y') AS published_date_format, a.slug AS article_slug, c.name AS article_category, c.slug AS category_slug");
		//$c_news->set_from("news_articles");
		$c_news->set_from("news_articles a LEFT JOIN ".$c_news->DB_PREFIX."article_categories c ON a.article_category_id = c.id");
		
		//switch to archived mode or not
		if($_SESSION['news_type'] == 1){
			$c_news->set_where("a.archived = 0");
		}
		elseif($_SESSION['news_type'] == 2){
			$c_news->set_where("a.archived = 1");
		}
		
		$c_news->set_where("a.content <> ''");	
		$c_news->set_orderby("event_date ASC, published_date ASC, title ASC");
		
		$c_news->set_page_num($f_page);//default value is 1
		$c_news->set_rows_per_page($records_per_page_live);
		
		$result = $c_news->get_data();
		$count = $c_news->numrows;//returns the total number of rows generated
		$total_pages = $c_news->get_total_pages();//returns total number of pages
		
		$c_news->current_page(CURRENT_PAGE);
	}
	
	//$get_string .= "&action=".$action;
	$navigation = $c_news->paging($get_string, $f_page, $count, $total_pages);//use paging function
	
		
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
		$num_str .= " in the ".$article_category_name." section"; 
	}
	
		
	//PAGING ******************************************************************************************		
	//start a row to hold paging navigation
	
	$cur = count($results_array);
	$results_array[$cur]["navigation"] = $navigation;
	$results_array[$cur]["searchkeyword"] = $searchkeyword;
	$results_array[$cur]["action"] = $action;
	$results_array[$cur]["num_matches"] = $num_str;
	$results_array[$cur]["num_articles"] = $count;
	$results_array[$cur]["total_pages"] = $total_pages;
	$results_array[$cur]["category"] = $c_news->htmlsafe($article_category_name);
	
	
	//generate results numbers
	if ($f_page == 1)
	{
		$pg_i = 1; //initialise page number counter.
	}
	else
	{
		$pg_i = ($c_news->records_per_page * ($f_page - 1)) + 1; //continue result number based on current page and number of results per page
	}
		
	// if no matches found from query
	if ($result || ($count > 0))
	{	
		while ($row = $result->fetch_object())
		{
			$cnt = count($f_page_array);
			$f_page_array[$cnt]["result_number"] = $pg_i;
			$f_page_array[$cnt]["article_id"] = $row->article_id;
			$f_page_array[$cnt]["article_date"] = $c_news->htmlsafe($row->published_date_format);
			$f_page_array[$cnt]["article_timestamp"] = $c_news->htmlsafe($row->timestamp_format);
			$f_page_array[$cnt]["article_url"] = $row->article_slug;	
			$f_page_array[$cnt]["category_url"] = $row->category_slug;	
			$f_page_array[$cnt]["article_title"] = $c_news->htmlsafe($row->title);
			$f_page_array[$cnt]["article_author"] = $c_news->htmlsafe($row->author);
			$f_page_array[$cnt]["article_synopsis"] = $c_news->htmlsafe($row->synopsis);
			$f_page_array[$cnt]["article_category"] = $c_news->htmlsafe($row->article_category);
			
			//get image from article for listing
			$arr_image = trim($c_cms->strip_tags_content($c_news->htmlsafe_ckeditor($row->content_image), '<img>', false));//return only the IMG and it's contents
			
			$arr_image_path = $c_cms->strip_image($arr_image);
			
			$arr_image_path_thumb = str_replace("/content/images/","/content/_thumbs/Images/",$arr_image_path);
			
			$f_page_array[$cnt]["article_image"] = $arr_image;
			$f_page_array[$cnt]["article_image_path"] = $arr_image_path;
			$f_page_array[$cnt]["arr_image_path_thumb"] = $arr_image_path_thumb;
			
			$pg_i ++;
			
		}						
	}// end display product list
	//---ARTICLES LIST ---
	//-------------------------------------------------------------------------------
?>