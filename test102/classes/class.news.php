<?php
final class news extends db_object 
{
// CLASS METHODS
// ****************
// DATABASE METHODS
// ----------------
// connect_db2
// insert_page_db
// update_page_db
// delete_page_db
// ----------------
// HTML METHODS
// ----------------
// create_page_html
// delete_page_html
// update_all_html
// ----------------
// FTP METHODS
// ----------------
// ftp_connect
// chmod_write
// chmod_read
// make_directory
// ----------------
// GET input methods
// ----------------
// page_id
// page_name
// page_filename
// page_linkname
// page_content
// page_title
// page_keywords
// page_description
// page_parent_id
// page_template
// ****************


	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////
	//declare database constants
	private $DB_TABLE_ARTICLES_FIELDS = "article_category_id, title, sub_title, content, content_alt, content_image, author, synopsis, slug, archived, published_date, event_date";
	private $DB_TABLE_ARTICLES_CATEGORY_FIELDS = "name, slug, created, modified";
	
	//varable to hold object instances
	private $c_generic = null;
	private $c_usersadmin = null;
	private $c_cms = null;
	private $c_search_index = null;
	
	/* live settings */
	private $chmod_ip = SITE_FTP_HOST;
	private $chmod_login = SITE_FTP_USER;
	private $chmod_pass = SITE_FTP_PASS;
	private $chmod_file = SITE_FTP_PATH;
	private $search_index = 0; //indexing on

	public $action = "";
	public $submit = "";	
	public $article_type = 0;
	public $Page_id = 0;
	public $Name = "";
	public $Filename = "";
	public $Oldfilename = "";
	public $Oldirectory = "";
	public $Linkname = "";
	public $Content = "";
	public $Content_alt = "";
	public $Author = "";
	public $Synopsis = "";
	public $Title = "";
	public $sub_title = "";
	public $Keywords = "";
	public $Description = "";
	public $Searchkeywords = "";
	public $Parent_id = 0;
	public $Template = "";
	public $Directory = NULL;
	public $section = "";
	public $Article_id = 0;
	public $Publish_date = NULL;
	public $Event_date = NULL;
	public $Category_id = 0;
	public $Category = NULL;
	public $Category_URL = NULL;
	public $Archived = 0;
	public $output_string = "";
	public $page_number = 1;
	public $mode = NULL;
	public $ftp_on = 0; //ONLINE_FLAG
	public $current_page = null;
	public $records_per_page = 10;
	public $records_limit = 10;
	public $order_by = "";

	public $int_number_words = 50;

	public $select_category_id = 0;
	public $select_article_id = 0;


	//CONSTRUCTOR function
	public function __construct(){	
		
		//fire up a connection to the database		
		parent::__construct();
		
		//create instance statically from registry class
		if($this->c_generic = Registry::get('generic'));
		if($this->c_usersadmin = Registry::get('usersadmin'));
		if($this->c_search_index = Registry::get('search_index'));
		if($this->c_cms = Registry::get('cms'));
		if($this->c_cache = Registry::get('cache'));
		
		$this->rootdir = $_SERVER["DOCUMENT_ROOT"];
	}//constructor

	// PUBLIC function
	public function action($input_action = NULL)
	{
		if($input_action <> NULL){
       		$this->action = $this->cleanstring(trim($input_action));
		}
		//echo $this->action; //debug 
    }
	
	// PUBLIC function
	public function submit($input_submit = NULL)
	{
		if($input_submit <> NULL){
       		$this->submit = $this->cleanstring(trim($input_submit));
		}
		//echo $this->submit; //debug 
    }	

	// PUBLIC function
	public function article_type($input_article_type = NULL)
	{	
		$this->article_type = (int)$this->cleanstring($input_article_type);
		//echo $this->article_type; //debug  
    }
	
	// PUBLIC function
	public function Article_id($input_id = 0)
	{	
		$this->Article_id = trim($this->cleanstring($input_id));
		//debug echo $this->Page_id;
    }

	// PUBLIC function
	public function article_title($input_title = "")
	{	
		$this->Title = ereg_replace( "['\"\]", "", trim($input_title));
		$this->Article_URL = $this->make_url_safe($this->cleanstring_input($input_title));
		//debug echo $this->Name;
    }

	// PUBLIC function
	public function article_sub_title($input_sub_title = "")
	{	
		$this->sub_title = $this->cleanstring_input(ereg_replace( "['\"\]", "", trim($input_sub_title)));
		//debug echo $this->Name;
    }

	// PUBLIC function
	public function article_name($input_name = "")
	{	
		$this->Name = trim($input_name);
		//debug echo $this->Name;
    }

	// PUBLIC function
	public function article_content($input_content = "")
	{
		$this->Content = strip_tags($input_content,"<a><b><strong><i><u><em><embed><p><dt><dl><dd><div><span><strike><sub><sup><img><table><tbody><tfoot><thead><tr><td><th><ul><ol><li><blockquote><br><hr><h1><h2><h3><h4><small><pre><video>");
		//debug echo $this->Content;
    }	

	// PUBLIC function
	public function article_content_alt($input_content_alt = "")
	{
		$this->Content_alt = strip_tags($input_content_alt,"<a><b><strong><i><u><em><embed><p><dt><dl><dd><div><span><strike><sub><sup><img><table><tbody><tfoot><thead><tr><td><th><ul><ol><li><blockquote><br><hr><h1><h2><h3><h4><small><pre><video>");
		//debug echo $this->Content;
    }

	// PUBLIC function
	public function article_image($input_article_image = "")
	{
		$this->article_image = strip_tags($input_article_image,"<img>");
		//debug echo $this->article_image;
    }

	// PUBLIC function
	public function article_author($input_author = "")
	{
		$this->Author = trim($input_author);
		//debug echo $this->Author;
    }	

	// PUBLIC function
	public function article_synopsis($input_synopsis = "")
	{
		$this->Synopsis = trim($input_synopsis);
		//debug echo $this->Synopsis;
    }	
	
	// PUBLIC function
	public function category_id($input_category_id = "")
	{	
		$this->Category_id = (int)$this->cleanstring_input($input_category_id);
		//debug echo $this->Name;
    }	
	
	// PUBLIC function
	public function select_category_id($input_select_category_id = "")
	{	
		$this->select_category_id = (int)$this->cleanstring_input($input_select_category_id);
		//debug echo $this->select_category_id;
    }	
	
	// PUBLIC function
	public function category_name($input_category_name = "")
	{	
		$this->Category = ereg_replace( "['\"\]", "", trim($this->cleanstring_input($input_category_name)));
		$this->Category_URL = $this->make_url_safe($this->cleanstring_input($input_category_name));
		//debug echo $this->Name;
    }
	
	// PUBLIC function
	public function page_number($input_pg_num = "")
	{	
		$this->page_number = (int)trim($input_pg_num);
		//debug echo $this->Name;
    }
	
	// PUBLIC function
	public function records_per_page($input_records_per_page = null)
	{	
		$this->records_per_page = (int)$this->cleanstring_input($input_records_per_page);
		//debug echo $this->records_per_page;
    }

	// PUBLIC function
	public function current_page($input_current_page = "")
	{
		$this->current_page = $this->cleanstring_input(trim($input_current_page));
		//debug echo $this->Name;
    }
	
	public function edit_mode($input_mode = ""){
		$this->mode = trim($input_mode);
		//debug echo $this->Name;
	}
	
	public function article_archived($input_archived = ""){
		if($input_archived == "live"){
			$this->Archived = 0;
		}else if($input_archived == "archived"){
			$this->Archived = 1;		
		}
		//debug echo $this->Archived;
	}
	
	// PUBLIC function
	public function article_publish_date($input_article_publish_date = NULL)
	{	
		if($input_article_publish_date <> NULL){
		
			//clean the input
			$input_article_publish_date = $this->cleanstring_input(trim($input_article_publish_date));
			//convert to database format date
			$input_article_publish_date = $this->c_generic->convertdate($input_article_publish_date,'dd/mm/yyyy','/','yyyy-mm-dd','-');
			//assign to object var
       		$this->Publish_date = $input_article_publish_date;
		}else{
			$this->Publish_date = date("Y-m-d");
		}
		//debug echo $this->Name;
    }
	
	// PUBLIC function
	public function article_event_date($input_event_publish_date = NULL)
	{	
		if($input_event_publish_date <> NULL){
		
			//clean the input
			$input_event_publish_date = $this->cleanstring_input(trim($input_event_publish_date));
			//convert to database format date
			$input_event_publish_date = $this->c_generic->convertdate($input_event_publish_date,'dd/mm/yyyy','/','yyyy-mm-dd','-');
			//assign to object var
       		$this->Event_date = $input_event_publish_date;
		}else{
			$this->Event_date = date("Y-m-d");
		}
		//debug echo $this->Name;
    }
	
	

	// PUBLIC function	
	//insert page details into the database
	public function insert_article_db(){
			
			//specify table name
			$this->db_table = "news_articles";
			$this->set_insert($this->DB_TABLE_ARTICLES_FIELDS);
			
			//add argument to insert values array
			$this->add_insert_value($this->Category_id, "input");
			$this->add_insert_value($this->Title, "input");
			$this->add_insert_value($this->sub_title, "input");
			$this->add_insert_value($this->Content, "ck_editor");
			$this->add_insert_value($this->Content_alt, "ck_editor");
			$this->add_insert_value($this->article_image, "ck_editor");
			$this->add_insert_value($this->Author, "input");
			$this->add_insert_value($this->Synopsis, "input");
			$this->add_insert_value($this->Article_URL, "input");
			$this->add_insert_value($this->Archived, "input");
			$this->add_insert_value($this->Publish_date, "input");
			$this->add_insert_value($this->Event_date, "input");
			//call method to ceate insert query
			//returns the row_id for the inserted tiem
			$result = $this->insert_data();
			
			if ($result){
				
				//clear any news cache files
				if(is_object($this->c_cache)){
					$this->c_cache->clear_cache('news');
				}

				if($this->search_index == 1){
				// call to indexing method
					$article_id = $result;
					//reindex the search index 
					$this->c_search_index->re_index_search($article_id, 'news');
				}
				
				return "Article created";
			}else{
				return "Error: Failed to add page";
			}
			
		
	}

	
	// PUBLIC function
	//insert page details into the database
	public function update_article_db(){


		//check the current template
		// if the selected template is different 
		// from the current one then update the 
		// pages table of the database


		//******************* check article exist s
		$this->set_select();
		$this->set_from("news_articles");
		$this->set_where("article_id = ".$this->Article_id."");
		//echo $sql_check,"<br>";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		

		$row = $result_check->fetch_object();
		
		$db_archived_state = $row->archived;
	
		if ($count < 1)
		{
			return "Error: There is no article with the id ".$this->Article_id.".";
		}
		else
		{	

			if(empty($this->Title)){
				return "Article Title is a required field.";
			}
			if(empty($this->Publish_date)){
				return "Article Date is a required field.";
			}
			if(empty($this->Event_date)){
				return "Article Event Date is a required field.";
			}
			if(empty($this->Content)){
				return "Article Content is a required field.";
			}
			/*if(empty($this->input_article_image)){
				return "Article Image is a required field.";
			}*/
						
			//SQL UPDATE METHOD CALLS
			$this->db_table = "news_articles";
			//no need to pass in table columns 
			$this->set_update();

			$this->add_update_value("article_category_id", $this->Category_id, "input");
			$this->add_update_value("title", $this->Title, "input");
			$this->add_update_value("sub_title", $this->sub_title, "input");
			$this->add_update_value("content", $this->Content, "ck_editor");
			$this->add_update_value("content_alt", $this->Content_alt, "ck_editor");
			$this->add_update_value("content_image", $this->article_image, "ck_editor");
			$this->add_update_value("author", $this->Author, "input");
			$this->add_update_value("synopsis", $this->Synopsis, "input");
			$this->add_update_value("slug", $this->Article_URL, "input");
			
			//echo $this->Archived;
			if(isset($this->Archived)){
				if($db_archived_state <> $this->Archived){
				$this->add_update_value("archived", $this->Archived, "input");
				}
			}

			if(isset($this->Publish_date)){
				$this->add_update_value("published_date", $this->Publish_date, "input");
			}

			if(isset($this->Event_date)){
				$this->add_update_value("event_date", $this->Event_date, "input");
			}

			$this->set_where("article_id = ".$this->Article_id."");
				

			//echo $sql;
			$result = $this->update_data();
			
			//clear any news cache files
			if(is_object($this->c_cache)){
				$this->c_cache->clear_cache('news');
			}

			if($this->search_index === 1){
				// call to indexing method
				$this->c_search_index->re_index_search($this->Article_id, 'news');
			}
			
			//re-order articles based on input order_id
			//$this->reorder_article_db();

			return "Article updated";

			
		}// end insert page
		
	}

	// PUBLIC function
	public function delete_article_db(){


		// check if page exists
		$this->set_select();
		$this->set_from("news_articles");
		$this->set_where("article_id = ".$this->Article_id."");
		//echo $sql_check,"<br>";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		
		if ($count == 0)
			return "Error: Invalid Article, article not deleted.";	
		else
		{
			//SQL DELETE METHOD CALLS
			$this->db_table = "news_articles";
			$this->set_delete();//initialise delete SQL
			$this->set_where("article_id = $this->Article_id");
			$this->delete_data();
			
			//clear any news cache files
			if(is_object($this->c_cache)){
				$this->c_cache->clear_cache('news');
			}

			if($this->search_index === 1){
				// call to indexing method
				$this->c_search_index->delete_index_search($this->Article_id, 'news');
			}
			
			return "Article deleted.";
			
		}
	}
	
	// PUBLIC function
	public function select_news_article_list()
	{	
		//set the page that is to be used to process the 
		// article content based on the $type argument
		$file = "news-cms-update.php";
		
		$this->set_select("SELECT *, date_format(timestamp, '%d/%m/%Y %H:%i:%s') as timestamp_format, date_format(published_date, '%W, %d %M %Y') AS published_date_format");
		$this->set_from("news_articles");

		if ($this->select_category_id > 0):
			$this->set_where("article_category_id = '".$this->select_category_id."'");
		endif;
		
		//by default query only live articles
		if ($this->article_type==NULL || $this->article_type=="0")
		{
			$this->set_where("archived = 0");
		}
		else
		{
			$this->set_where("archived = 1");
		}
		
		//$this->set_where("news = $this->News_flag");
		
		$this->set_orderby("published_date DESC, title ASC");
			
		$this->set_page_num($this->page_number);//default value is 1
		$this->set_rows_per_page($this->records_per_page);
		//echo $sqlquery;
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		$total_pages = $this->get_total_pages();//returns total number of pages

		$html_output = NULL;				
			
		if ($result && ($count > 0))
		{		
			if ($count == 1):
				$temp = "There is 1 article.";
			elseif ($count > 1):
				$temp = "There are " . $count . " articles."; 
			else:
				$temp = "There are no articles.";
			endif;
			
			//******************************** PAGING PREPARATION  *******************************************************
			//write query string that will pass in paging links
			$strQuery="mode=$this->mode&";
			$html_output = "<p id=\"paging\">$temp<br />".$this->paging($strQuery, $this->page_number, $count, $total_pages)."<p>";//use paging function		
			//******************************** PAGING PREPARATION  *******************************************************
	
			while($row = $result->fetch_object())
			{				
				$title = strip_tags($this->htmlsafe_input($row->title));
				$sub_title = strip_tags($this->htmlsafe_input($row->sub_title));
							
				//output only the fisrt 50 words of the content field using str_word_count
				$content = strip_tags($this->htmlsafe_ckeditor($row->content));
				
				$array_str_words = explode(" ", $content);
				//$int_number_words = 50;
				
				$str_words = "";
				for($i = 0; $i < $this->int_number_words; $i++)
				{
					if($i != 0) { $str_words .= " "; }
					$str_words .= $array_str_words[$i];
					//echo $array_str_words[$i];
				}
				
				$html_output .= "<div class=\"main_site\">";
				
					$html_output .= "<div class=\"div_article_normal\">";
						$html_output .= "<h3>".$title."</h3>";
						if(!empty($sub_title)){
							$html_output .= "<p><strong>".$sub_title."</strong></p>";
						}
						$html_output .= "<p>".$str_words."</p>";
					$html_output .= "</div><div class=\"clear\"></div>";
	
					//assign variables
					$article_id = $row->article_id;
					
						$html_output .= "<div style=\"float:left;\">";
							$html_output .= "<span class=\"cms-actions\">";
								$html_output .= "<span class=\"bubbleInfo\">";
									$html_output .= "<a href=\"javascript:poptastic2('".$file."?article_id=".$article_id."&amp;mode=edit&amp;archive=$this->mode');\">[update]</a>&nbsp;&nbsp;";
									$html_output .= "<em class=\"trigger\">What's This?</em>&nbsp;&nbsp;";								
									$html_output .= "<div class=\"popup\" title=\"pop-right-cms\">";
										$html_output .= "<h3>Update Article</h3>";
										$html_output .= "<p>Use this link to <em>update</em> the various elements of the News Article</p>";				
									$html_output .= "</div>";								
								$html_output .= "</span>";
							$html_output .= "</span>";
							$html_output .= "<span class=\"cms-actions\">";
								$html_output .= "<span class=\"bubbleInfo\">";
									$html_output .= "<a href=\"javascript:poptastic2('".$file."?article_id=".$article_id."&amp;mode=del&amp;archive=$this->mode');\">[delete]</a>&nbsp;&nbsp;";
									$html_output .= "<em class=\"trigger\">What's This?</em>&nbsp;&nbsp;";
									$html_output .= "<div class=\"popup\" title=\"pop-right-cms\">";
										$html_output .= "<h3>Delete Article</h3>";
										$html_output .= "<p>Use this link to <em>remove</em> the News Article</p>";				
									$html_output .= "</div>";										
								$html_output .= "</span>";
							$html_output .= "</span>";
							$html_output .= "last edited - $row->published_date_format";	
							
							//get the category for the given article
							$this->category_id($row->article_category_id);							
							list($msg_err_code, $db_category_list) = $this->category_list_db();
							if($msg_err_code == 0){
								$cat_row = $db_category_list->fetch_object();
								$html_output .= " <strong>Category</strong> - ".$cat_row->name;
							}
								
						$html_output .= "</div>";
						$html_output .= "<div class=\"clearfix\"></div>";
						$html_output .= "<hr />";
						$html_output .= "<div class=\"cms-actions\">";	
							$html_output .= "<span class=\"bubbleInfo\">";								
								$html_output .= "<a href=\"javascript:poptastic2('".$file."?article_id=".$article_id."&amp;mode=add&amp;archive=$this->mode');\">[add a new article]</a>&nbsp;&nbsp;";
									$html_output .= "<em class=\"trigger\">What's This?</em>&nbsp;&nbsp;";
									$html_output .= "<div class=\"popup\" title=\"pop-right-cms\">";
										$html_output .= "<h3>Add New Article</h3>";
										$html_output .= "Use this link to <em>load</em> the editing window and <em>create</em> a new News Article";				
									$html_output .= "</div>";	
							$html_output .= "</span>";
						$html_output .= "</div>";
						$html_output .= "<hr />";
						$html_output .= "<div class=\"clear\"></div>";
				$html_output .= "</div>";	
				
			}			
		}
		else
		{
			$html_output .= "<div style=\"clear:both\">";
			$html_output .= "<a href=\"javascript:poptastic2('".$file."?mode=add&amp;archive=$this->mode');\">[add a new record]</a><small> - invisible</small>";
			$html_output .= "</div>";			
		}
		return $html_output;
	}//select_news_article_list
	
	
	// PUBLIC function	
	//insert page details into the database
	public function insert_category_db(){
		
		
		if(empty($this->Category)){
			$err_msg = "Error: Category name is a required field.";
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		
		//******************* check for duplicates
		$this->set_select();
		$this->set_from("article_categories");
		$this->set_where("name = '".$this->Category."'");
		$this->set_where("id <> ".$this->Category_id."");
		//echo $sql_check,"<br>";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
			
		if ($count > 0)
		{
			$err_msg = "Error: There is already a category with the name ".$this->Category.".";
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{
		
			//specify table name
			$this->db_table = "article_categories";
			$this->set_insert($this->DB_TABLE_ARTICLES_CATEGORY_FIELDS);
			
			//add argument to insert values array
			$this->add_insert_value($this->Category, "input");
			$this->add_insert_value($this->Category_URL, "input");
			$this->add_insert_value(date("Y-m-d h:i:s"));
			$this->add_insert_value(date("Y-m-d h:i:s"));
			//call method to ceate insert query
			//returns the row_id for the inserted tiem
			$result = $this->insert_data();
			if ($result){
				$err_msg = "Category created";
				return array(0,$err_msg);//on fail return error code 1 and error message
			}else{
				$err_msg = "Error: Failed to add category";
				return array(1,$err_msg);//on fail return error code 1 and error message
			}
		}
		
	}

	
	// PUBLIC function
	//insert page details into the database
	public function update_category_db(){


		//check the current template
		// if the selected template is different 
		// from the current one then update the 
		// pages table of the database


		//******************* check article exist s
		$this->set_select();
		$this->set_from("article_categories");
		$this->set_where("id = ".$this->Category_id."");
		//echo $sql_check,"<br>";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		

		$row = $result_check->fetch_object();
		
		$db_archived_state = $row->archived;
	
		if ($count < 1)
		{
			$err_msg = "Error: There is no category with the id ".$this->Category_id.".";
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{	
		
			if(empty($this->Category)){
				$err_msg = "Category name is a required field.";
				return array(0,$err_msg);//on fail return error code 1 and error message
			}
		
			//******************* check for duplicates
			$this->set_select();
			$this->set_from("article_categories");
			$this->set_where("name = '".$this->Category."'");
			$this->set_where("id <> ".$this->Category_id."");
			//echo $sql_check,"<br>";
			$result_check = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			
			if ($count > 0)
			{
				$err_msg = "Error: There is already a category with the name ".$this->Category.".";
				return array(1,$err_msg);//on fail return error code 1 and error message
			}
			else
			{
			
				
				//SQL UPDATE METHOD CALLS
				$this->db_table = "article_categories";
				//no need to pass in table columns 
				$this->set_update();
	
				$this->add_update_value("name", $this->Category, "input");
				$this->add_update_value("slug", $this->Category_URL, "input");
				$this->add_update_value("modified", date("Y-m-d h:i:s"));
	
				$this->set_where("id = ".$this->Category_id."");
					
	
				//echo $sql;
				$result = $this->update_data();
				
				//re-order articles based on input order_id
				//$this->reorder_article_db();
				$err_msg = "Category updated";
				return array(0,$err_msg);//on fail return error code 1 and error message
			}

			
		}// end insert page
		
	}

	// PUBLIC function
	public function delete_category_db(){


		// check if page exists
		$this->set_select();
		$this->set_from("article_categories");
		$this->set_where("id = ".$this->Category_id."");
		//echo $sql_check,"<br>";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		
		if ($count == 0){
			$err_msg = "Error: Invalid Category, category not deleted.";	
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{
			
			//SQL UPDATE METHOD CALLS
			$this->db_table = "news_articles";
			//no need to pass in table columns 
			$this->set_update();
			$this->add_update_value("article_category_id", 0);			
			$this->set_where("article_category_id = ".$this->Category_id."");					
	
			//echo $sql;
			$result = $this->update_data();
			
			//SQL DELETE METHOD CALLS
			$this->db_table = "article_categories";
			$this->set_delete();//initialise delete SQL
			$this->set_where("id = $this->Category_id");
			$this->delete_data();
			
			$err_msg = "Category deleted.";
			return array(0,$err_msg);//on fail return error code 1 and error message
			
		}
	}
	
	// PUBLIC function
	public function show_category_form()
	{	
		//set the page that is to be used to process the 
		// article content based on the $type argument
		$file = "news-cms-update.php";
		
		$this->set_select("SELECT *, date_format(modified, '%d/%m/%Y %H:%i:%s') as modified_date, date_format(created, '%d/%m/%Y') AS created_date");
		$this->set_from("article_categories");
		
		//$this->set_where("news = $this->News_flag");
		
		$this->set_orderby("name DESC");
			
		$this->set_page_num($this->page_number);//default value is 1
		$this->set_rows_per_page($this->records_per_page);
		//echo $sqlquery;
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		$total_pages = $this->get_total_pages();//returns total number of pages

		$html_output = NULL;				
		
		
		$cnt = 1;
		if($result && ($count > 0)){
		
			//******************************** PAGING PREPARATION  *******************************************************
			//write query string that will pass in paging links
			$strQuery="mode=$this->mode&";
			$html_output = "<p id=\"paging\">".$this->paging($strQuery, $this->page_number, $count, $total_pages)."<p>";//use paging function		
			//******************************** PAGING PREPARATION  *******************************************************
			
			$html_output .= '<div class="table_gallery_list">';
			$html_output .= '<table id="listing" cellspacing="0" cellpadding="0" border="0">';
			$html_output .= '<tr align="center">
								<th>ID</th>
								<th>Name</th>
								<th>Modified</th>
								<th>Actions</th>
								<th>Articles</th>
							  </tr>';
			
			while($row = $result->fetch_object()){
				
				if ($cnt%2)
					$bg_class="bg_colour_one";
				else
					$bg_class="bg_colour_two";			
				
				
				$html_output .= '<tr class="'.$bg_class.'" id="category-row-'.$cnt.'" edit" align="center">
					<form name="formimgedit" id="formimgedit" method="post" action="'.$_SERVER["PHP_SELF"].'?mode=edit">
					<input name="action" type="hidden" value="edit" />					
					<input name="f_category_id" type="hidden" value="'.$row->id.'" />
					<input type="hidden" name="current_page" value="'.$current_page.'" />		
					<td></td>
					<td><input type="text" name="f_category_name" value="'.$row->name.'" /></td>
					
					<td></td>										
					<td colspan="2"><input type="submit" name="Submit" value="Update Category" /></td>
					</form>
				  </tr>';
				 
				$html_output .= '<tr class="'.$bg_class.' list" align="center">
							
					<td style="width:10%;">'.$row->id.'</td>
					<td style="width:35%;">'.$row->name.'</td>
					
					<td style="width:25%;">'.$row->modified_date.'</td>										
					<td style="width:14%;"><a href="#category-row-'.$cnt.'" class="showhide">Edit</a> | <a href="'.$_SERVER["PHP_SELF"].'?mode=del_confirm&f_category_id='.$row->id.'" class="del">Delete</a></td>
					<td style="width:16%;"><a href="news-cms.php?type=live&action=set_category&select_category_id='.$row->id.'">Live</a> | <a href="news-cms.php?type=archived&action=set_category&select_category_id='.$row->id.'">Archived</a></td>
				  </tr>';
				
				$cnt++;
				
			}
			
		}
		else
		{
			$html_output .= '<div class="table_gallery_list">';
			$html_output .= '<table id="listing">';
			$html_output .= '<tr align="center">
								<th>ID</th>
								<th>Name</th>
								<th>Modified</th>
								<th>Actions</th>
								<th>Articles</th>	
							  </tr>';
							  
			$html_output .= '<tr class="'.$bg_class.'" align="center">
								<td class="error" colspan="4">No categories currently exist</td>
							</tr>';
		}
		
		$html_output .= '</table>';
		$html_output .= '</div>';
		
		return $html_output;
	}
	
	// public function	
	//retrieve a list of all article categories
	public function category_list_db()
	{
		//generate list of messages			
		$this->set_select();
		$this->set_from("article_categories");	
		
		if (!empty($this->Category_id))
		{
			$this->set_where("id = ".$this->Category_id);
		}
		
		//set query ordering
		$this->set_orderby("name ASC");
		
		//run query
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
	
		if ($count == 0)
		{
			$err_msg = 'No categories could be found';
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check);//on success return error code 0 and array of results				
		}	
	}//insert_page_db
	
	// public function	
	//retrieve a list of all articles based on optional category
	private function newlist_list_db($slug = null, $limit=3)
	{

		$this->debug_values['news-slug'] = $slug;
		
		//-------------------------------------------------------------------------------
		//--- LIVE ARTICLES LIST ---
		
		$results_array = array();
		$f_page_array = array();
		
		$f_article_category_slug = $this->cleanstring_input($slug);
		$_SESSION['category_slug'] = $f_article_category_slug;
		
		//NO PAGING ON THE LIVE NEWS PAGE
		$f_page = 1;
		
		//place page number into session var
		$_SESSION['page'] = $f_page;
				
		$this->records_per_page($records_per_page_live);
		
		//--- BY SPECIFIC CATEGORY ---
		if (!empty($f_article_category_slug))
		{
			//get category id
			$this->set_select("SELECT id, slug, name");
			$this->set_from("article_categories");
			$this->set_where("slug = '$f_article_category_slug'");
			$this->rows_per_page = null;
			if ($limit > 0):
				$this->set_limit($limit);
			endif;
			
			$result = $this->get_data(0);
			$live_count = $this->numrows;//returns the total number of rows generated
			
			// if no matches found from query
			if (!$result || ($live_count == 0))
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
						$article_category_disp = " - " . $article_category_name;
					endif;
				}
				
				if (!empty($article_category_id) && !empty($article_category_slug))
				{	
					//get article that matches the category slug
					$this->set_select("SELECT *, date_format(a.timestamp, '%H:%i %W, %d %M %Y') as timestamp_format, date_format(a.published_date, '%d.%m.%Y') AS published_date_format, a.slug AS article_slug, c.name AS article_category");				
					$this->set_from("news_articles a LEFT JOIN ".$this->DB_PREFIX."article_categories c ON a.article_category_id = c.id");
					$this->set_where("a.article_category_id = '$article_category_id'");
					$this->set_where("a.archived = 0");
					$this->set_where("a.content <> ''");	
					$this->set_where("a.published_date <= NOW()");
					$this->set_orderby("published_date DESC, event_date DESC, title ASC");
					$this->set_page_num($f_page);//default value is 1
					//$c_dbobject->set_rows_per_page($c_news->records_per_page); //show paging
					$this->rows_per_page = null;
					if ($limit > 0):
						$this->set_limit($limit);
					endif;	
					$result = $this->get_data(0);
					$live_count = $this->numrows;//returns the total number of rows generated
					$total_pages = $this->get_total_pages();//returns total number of pages
					
					$this->current_page(CURRENT_PAGE.'/category/'.$article_category_slug);
				}
			}
		}
		else
		{
			$this->set_select("SELECT *, date_format(a.timestamp, '%H:%i %W, %d %M %Y') as timestamp_format, date_format(a.published_date, '%W, %d %M %Y') AS published_date_format, a.slug AS article_slug, c.name AS article_category, c.slug AS category_slug");
			//$c_dbobject->set_from("news_articles");
			$this->set_from("news_articles a LEFT JOIN ".$this->DB_PREFIX."article_categories c ON a.article_category_id = c.id");
			$this->set_where("a.archived = 0");
			$this->set_where("a.content <> ''");	
			$this->set_where("a.published_date <= NOW()");
			$this->set_orderby("published_date DESC, event_date DESC, title ASC");
			$this->set_page_num($f_page);//default value is 1
			//$c_dbobject->set_rows_per_page($c_news->records_per_page); //show paging
			$this->rows_per_page = null;
			if ($limit > 0):
				$this->set_limit($limit);
			endif;	
			$result = $this->get_data();
			$live_count = $this->numrows;//returns the total number of rows generated
			$total_pages = $this->get_total_pages();//returns total number of pages
			
			$this->current_page(CURRENT_PAGE);
		}
		
			
		if ($live_count==1)
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
		$results_array[$cur]["num_articles"] = $live_count;
		$results_array[$cur]["total_pages"] = $total_pages;
		$results_array[$cur]["category"] = $this->htmlsafe($article_category_name);
		
		
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
				$f_page_array[$cnt]["article_date"] = $this->htmlsafe_input($row->published_date_format);
				$f_page_array[$cnt]["article_timestamp"] = $this->htmlsafe_input($row->timestamp_format);
				$f_page_array[$cnt]["article_url"] = $row->article_slug;	
				$f_page_array[$cnt]["category_url"] = $row->category_slug;	
				$f_page_array[$cnt]["article_title"] = $this->htmlsafe_input($row->title);
				$f_page_array[$cnt]["article_sub_title"] = $this->htmlsafe_input($row->sub_title);
				$f_page_array[$cnt]["article_author"] = $this->htmlsafe_input($row->author);
				$f_page_array[$cnt]["article_synopsis"] = $this->htmlsafe_input($row->synopsis);
				$f_page_array[$cnt]["article_category"] = $this->htmlsafe_input($row->article_category);
				
				//get image from article for listing
				$arr_image = trim($this->c_cms->strip_tags_content($this->htmlsafe_ckeditor($row->content_image), '<img>', false));//return only the IMG and it's contents
				$arr_image_path = $this->c_cms->strip_image($arr_image);
				$arr_image_path_thumb = str_replace("/content/images/","/content/_thumbs/Images/",$arr_image_path);
				
				$f_page_array[$cnt]["article_image"] = $arr_image;
				$f_page_array[$cnt]["article_image_path"] = $arr_image_path;
				$f_page_array[$cnt]["arr_image_path_thumb"] = $arr_image_path_thumb;
				
				$pg_i ++;
				
			}						
		}// end display product list
		//--- LIVE ARTICLES LIST ---
		//-------------------------------------------------------------------------------
		
		
		
		
		
		//-------------------------------------------------------------------------------
		//--- ARCHIVE ARTICLES LIST ---
		
		$this->records_per_page($records_per_page_arch);
		
		$results_array_archive = array();

		//NO PAGING ON THE LIVE NEWS PAGE
		$f_page = 1;
		
		//place page number into session var
		$_SESSION['page'] = $f_page;
		
		//generate a list of ARCHIVED articles within the given category
		$f_assoc_article_array = array();
		
		//get 10 newest articles that match the article category but not the current article_id
		$this->set_select("SELECT *, date_format(published_date, '%W, %d %M %Y') as published_date_format");
		$this->set_from("news_articles");
		if (!empty($article_category_id)):
			$this->set_where("article_category_id = '".$article_category_id."'");
		endif;
		$this->set_where("archived = '1'");
		$this->set_orderby("published_date DESC, title ASC");
		$this->set_page_num($f_page);//default value is 1
		$this->set_rows_per_page($c_news->records_per_page); //show paging
		
		if (!empty($article_category_id)):
			if ($limit_arch_cat > 0):
				$this->set_limit($limit_arch_cat);
			endif;
		else:
			if ($limit_arch > 0):
				$this->set_limit($limit_arch);
			endif;
		endif;	
			
		$result = $this->get_data(0);
		$archive_count = $this->numrows;//returns the total number of rows generated
		$total_pages = $this->get_total_pages();//returns total number of pages
		
		$cur = count($results_array_archive);
		$results_array_archive[$cur]["navigation"] = $navigation;
		$results_array_archive[$cur]["searchkeyword"] = $searchkeyword;
		$results_array_archive[$cur]["action"] = $action;
		$results_array_archive[$cur]["num_matches"] = $num_str;
		$results_array_archive[$cur]["num_articles"] = $archive_count;
		$results_array_archive[$cur]["total_pages"] = $total_pages;
		$results_array_archive[$cur]["category"] = $this->htmlsafe($article_category_name);
		
		//generate results numbers
		if ($f_page == 1)
		{
			$pg_i = 1; //initialise page number counter.
		}
		else
		{
			$pg_i = ($this->records_per_page * ($f_page - 1)) + 1; //continue result number based on current page and number of results per page
		}
		
		
		// if no matches found from query
		if ($result || $archive_count > 0)
		{
			while ($row = $result->fetch_object())
			{		
				$cnt = count($f_assoc_article_array);
				$f_assoc_article_array[$cnt]["article_id"] = $this->htmlsafe_input($row->article_id);
				$f_assoc_article_array[$cnt]["article_date"] = $this->htmlsafe_input($row->published_date_format);
				$f_assoc_article_array[$cnt]["article_timestamp"] = $this->htmlsafe_input($row->timestamp_format);
				$f_assoc_article_array[$cnt]["article_url"] = $this->htmlsafe_input($row->slug);
				$f_assoc_article_array[$cnt]["article_synopsis"] = $this->htmlsafe_input($row->synopsis);		
				
				$f_assoc_article_array[$cnt]["article_title"] = $this->htmlsafe_input($row->title);				
				$f_assoc_article_array[$cnt]["article_sub_title"] = $this->htmlsafe_input($row->sub_title);
			}
		}	
		//--- ARCHIVE ARTICLES LIST ---
		//-------------------------------------------------------------------------------

		//output debug values	
		if(!empty($slug)){
			$this->debug_values[$slug]['results_live'][] = $results_array;
			$this->debug_values[$slug]['results_live_articles'][] = $f_page_array;
			$this->debug_values[$slug]['results_archive'][] = $results_array_archive;
			$this->debug_values[$slug]['results_archive_articles'][] = $f_assoc_article_array;
		}else{			
			$this->debug_values['results_live'][] = $results_array;
			$this->debug_values['results_live_articles'][] = $f_page_array;
			$this->debug_values['results_archive'][] = $results_array_archive;
			$this->debug_values['results_archive_articles'][] = $f_assoc_article_array;
		}
	
		if ($live_count == 0)
		{
			$err_msg = 'No articles could be found';
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{
			return array(0,$results_array, $f_page_array, $results_array_archive ,$f_assoc_article_array);//on success return error code 0 and array of results				
		}	
	}//insert_page_db
	
	//PRIVATE Function
	public function get_list_db($slug=null, $limit=null){
		
		$args = func_get_args(); // return array of arguments			
		
		//first argument should be slug	
		$slug = $this->cleanstring_plain(array_shift($args));
		//second arg is limit for returned results
		$limit = intval($this->cleanstring_plain(array_shift($args)));
		//third arg is cache array
		$cache = array_shift($args);

		
		$this->list_array = null;
			
		if(is_array($cache) && $cache['cache'] !== false){
			
			//look for existing cache
			if($this->c_cache->check($cache)){
			
				$this->list_array = $this->c_cache->read();
				
				return $this->list_array;
					
			}else{

				//call to generate news list 
				list($err, $live, $articles_live) = $this->newlist_list_db($slug, $limit);

				if($err == 0){
					$this->list_array['live'] = $live;
					$this->list_array['articles_live'] = $articles_live;
					//create cache
					$this->c_cache->write($this->list_array);
					return $this->list_array;
				}
			}

		}else{

			//call to generate news list 
			list($err, $live, $articles_live) = $this->newlist_list_db($slug, $limit);
			
			if($err == 0){
				$this->list_array['live'] = $live;
				$this->list_array['articles_live'] = $articles_live;
				return $this->list_array;
			}
		}

	}

	//PUBLIC Function
	public function paging(&$strQuery, $page, $count, $total_pages)
	{
		//PAGING ******************************************************************************************
	
		//write query string that will pass in paging links
		//$strQuery .= "&f_page=".$page;
		//grab the page number that we're on
		$full_count = $count;
		//set max number of records per page	
		/*
		This just sets up a page variable which will be past in a "next" and "prev"
		link.  The first link to this page may not actually have $page so this just
		sets it up.
		*/		
		if ( $page == "" ) { $page = 1; }
		//echo " <br />page number".$page; #debug
		
		# this will set up the "First | Prev | " part of our navigation.
		if ( $page == 1 ) {
			# if we are on the first page then "First" and "Prev" should not be links.
			$navigation = "First | Prev | ";
		} else {
			# we are not on page one so "First" and "Prev" can
			# be links
			$prev_page = $page - 1;
			if(!empty($this->current_page)){
				$navigation = "<a href=\"/".$this->current_page."/p1/\" class=\"ajax\">First</a> | <a href=\"/".$this->current_page."/p".$prev_page."/\" class=\"ajax\">Prev</a> | ";
			}else{
				$navigation = "<a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&p_page=1\" class=\"ajax\">First</a> | <a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&p_page=".$prev_page."\" class=\"ajax\">Prev</a> | ";
			}
		}

		//loop through total number of pages and add a link to each individual page
		for ($i = 1; $i <= $total_pages; $i++) {
		//show curent page as active link by changin the css class
			if ($i == $page){
				if(!empty($this->current_page)){
					$navigation .= " <a href=\"/".$this->current_page."/p".$i."/\" class=\"on ajax\" title=\"".$i."\">$i</a> ";
				}else{
					$navigation .= " <a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&p_page=".$i."\" class=\"on ajax\" title=\"".$i."\">$i</a> ";
				}
			}else{
				if(!empty($this->current_page)){
					$navigation .= " <a href=\"/".$this->current_page."/p".$i."/\" title=\"".$i."\" class=\"ajax\">$i</a> ";
				}else{
					$navigation .= " <a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&p_page=".$i."\" title=\"".$i."\" class=\"ajax\">$i</a> ";							
				}
			}
		}
		$navigation .= "| ";

		# this part will set up the rest of our navigation "Next | Last"
		if ( $page == $total_pages ) {
			# we are on the last page so "Next" and "Last"
			# should not be links
			$navigation .= "Next | Last";
		} else {
			# we are not on the last page so "Next" and "Last"
			# can be links
			$next_page = $page + 1;
			if(!empty($this->current_page)){
				$navigation .= "<a href=\"/".$this->current_page."/p".$next_page."/\" class=\"ajax\">Next</a> | <a href=\"/".$this->current_page."/p".$total_pages."/\" class=\"ajax\">Last</a>";
			}else{
				$navigation .= "<a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&p_page=".$next_page."\" class=\"ajax\">Next</a> | <a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&p_page=".$total_pages."\" class=\"ajax\">Last</a>";
			}
		}
				
		return $navigation;
		//END PAGING ******************************************************************************************	
		//list products							
	}
	
	// PRIVATE function
	//connect to the server via ftp and set a chmod
	private function ftp_connect($ip, $login, $pass, $file, $chmod_mode){
	
		$conn_id=ftp_connect($ip);
		$login_result=@ftp_login($conn_id, $login, $pass);
		//debug echo $login_result;
		$chmod_cmd="CHMOD ".$chmod_mode." ".$file;
		$chmod=ftp_site($conn_id, $chmod_cmd);	
		//debug echo $chmod;
		if ($chmod == 1){
			//$message = "<br>Succesfully ran ftp cmd: $chmod_cmd, current dir= " . ftp_pwd($conn_id) . "<br>";
		}else{
			$message = "<br>failed to run ftp cmd: $chmod_cmd, current dir= " . ftp_pwd($conn_id) . "<br>";
		}
		ftp_quit($conn_id);
	return $message;
	}

	// PUBLIC function
	//connect to the server via ftp and set a chmod to 777
	public function chmod_write($chmod_dir=NULL){
	
		if(empty($chmod_dir)){
			
			//iterate through all directories stored in the pages table and make each writeable
			$this->set_select("SELECT DISTINCT directory");
			$this->set_from("pages");
			$this->set_where("directory <> '../'");
			//echo $sql_check,"<br>";
			$result_check = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if($count > 0){
				while($row = $result_check->fetch_object()){
			
					$current_dir = $row->directory;
									
					$tmp_dir = $current_dir;
					$tmp_dir = str_replace( "../", "", $tmp_dir);
					$tmp_dir_end = strrchr($tmp_dir, "/");
					
					if($tmp_dir_end <> "/"){
						$tmp_dir = str_replace($tmp_dir_end, "", $tmp_dir);
					}else{
						$tmp_dir = substr($tmp_dir, 0, -1);
					}
					//regular expression that matches a string doesn't start with an '/' OR end with and '/'
					$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
					$string = $tmp_dir;
					
					if(ereg($reg_ex, $string)){
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}else{
		
						if($string{0}=="/"){
							$string = substr($string, 1);
						}
						//echo $string;
						if (substr($string, -1) == "/"){
							$string = substr($string, 0, -1);
						}
		
						$chmod_dir = $string."/";
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}
					
					//make each directory writeable
					$this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "0777");
				}//end while
			}
			//finally call default function call to make site root writeable
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $this->chmod_file, "0777");
							
		}else{
			
				//regular expression that matches a string doesn't start with an '/' OR end with and '/'
				$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
				$string = $chmod_dir;
				
				if(ereg($reg_ex, $string)){
					$chmod_dir = $this->chmod_file.$chmod_dir;
				}else{
	
					if($string{0}=="/"){
						$string = substr($string, 1);
					}
					//echo $string;
					if (substr($string, -1) == "/"){
						$string = substr($string, 0, -1);
					}
	
					$chmod_dir = $string."/";
					$chmod_dir = $this->chmod_file.$chmod_dir;
				}
				//echo $chmod_dir;
				return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "0777");
		}
	}

	// PRIVATE function
	//connect to the server via ftp and set a chmod to 777
	public function chmod_read($chmod_dir=NULL){
		if(empty($chmod_dir)){
			
			//iterate through all directories stored in the pages table and make each readable
			$this->set_select("SELECT DISTINCT directory");
			$this->set_from("pages");
			$this->set_where("directory <> '../'");
			//echo $sql_check,"<br>";
			$result_check = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if($count > 0){
				while($row = $result_check->fetch_object()){
			
					$current_dir = $row->directory;
									
					$tmp_dir = $current_dir;
					$tmp_dir = str_replace( "../", "", $tmp_dir);
					$tmp_dir_end = strrchr($tmp_dir, "/");
					
					if($tmp_dir_end <> "/"){
						$tmp_dir = str_replace($tmp_dir_end, "", $tmp_dir);
					}else{
						$tmp_dir = substr($tmp_dir, 0, -1);
					}					
					//regular expression that matches a string doesn't start with an '/' OR end with and '/'
					$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
					$string = $tmp_dir;
					
					if(ereg($reg_ex, $string)){
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}else{
		
						if($string{0}=="/"){
							$string = substr($string, 1);
						}
						//echo $string;
						if (substr($string, -1) == "/"){
							$string = substr($string, 0, -1);
						}
		
						$chmod_dir = $string."/";
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}
					
					//make each directory readable
					$this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "0755");
				}//end while
			}
			
			//finally call default function call to make site root readable
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $this->chmod_file, "0755");
		}else{
			//regular expression that matches a string doesn't start with an '/' AND does end with '/'
			$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
			$string = $chmod_dir;
			//echo $string;
			if(ereg($reg_ex, $string)){
				$chmod_dir = $this->chmod_file.$chmod_dir;
			}else{
				if($string{0}=="/"){
					$string = substr($string, 1);
				}
				if (substr($string, -1) == "/"){
					$string = substr($string, 0, -1);
				}
				$chmod_dir = $string."/";
				$chmod_dir = $this->chmod_file.$chmod_dir;
			}
			//echo $chmod_dir;
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "0755");
		}
	}	

	//use regular expression to get content of link href and body
	private function strip_links($output_string){
		preg_match_all ("/<(a|A) (href|HREF)\=\"(.+?)\">(.*?)<\/(a|A)>/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) {

			//return matched tages we want
			$TAG0= $matches[0][$i]; //returns entire matched string
			$TAG1= $matches[1][$i]; //matches (a|A)
			$TAG2 = $matches[2][$i]; //matches (href|HREF)
			$TAG3= $matches[3][$i]; //matches (.+?)
			$TAG4 = $matches[4][$i]; //matches (.*?)
			$TAG5 = $matches[5][$i]; //matches (a|A)
			//echo "<br>".$TAG3;
			//echo "<br>".$TAG4;
		}
	return array($TAG3, $TAG4);
	}

	//use regular expression to get content of image src
	private function strip_image($output_string){
		preg_match_all ("/(^<(img|IMG)).*((src|SRC)\=(\".+?\")).*(\/>$)/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) {

			//return matched tages we want
			$TAG0= $matches[0][$i]; //matches entire string format to regex
			$TAG1= $matches[1][$i]; //matches string starts with < img|IMG
			$TAG2 = $matches[2][$i]; //matches (img|IMG)
			$TAG3= $matches[3][$i]; //matches (src="")
			$TAG4 = $matches[4][$i]; //matches (src|SRC)
			$TAG5 = $matches[5][$i]; //matches (src tag contents) returns attribute
			/*echo "<br>".$TAG0;
			echo "<br>".$TAG1;
			echo "<br>".$TAG2;
			echo "<br>".$TAG3;
			echo "<br>".$TAG4;
			echo "<br>".$TAG5;*/
			return  ereg_replace( "['\"\]", "", trim($TAG5));
		}
	//return array($TAG3, $TAG4);
	}
	
	//use regular expression to get content of image src
	private function strip_h2($output_string){
		preg_match_all ("/<(h2|H2)>(.*?)<\/(h2|H2)>/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) {

			//return matched tages we want
			$TAG0= $matches[0][$i]; //matches entire string format to regex
			$TAG1= $matches[1][$i]; //matches string starts with < img|IMG
			$TAG2 = $matches[2][$i]; //matches (img|IMG)
			$TAG3= $matches[3][$i]; //matches (src="")
			$TAG4 = $matches[4][$i]; //matches (src|SRC)
			$TAG5 = $matches[5][$i]; //matches (src tag contents) returns attribute
			/*echo "<br>0".$TAG0;
			echo "<br>1".$TAG1;
			echo "<br>2".$TAG2;
			echo "<br>3".$TAG3;
			echo "<br>4".$TAG4;
			echo "<br>5".$TAG5;*/
			return $TAG2;
		}
	//return array($TAG3, $TAG4);
	}

	//PRIVATE FUNCTION
	//next generate the main menu (based on divs for the p7TMnav JavaScript menu)
	//---------- START function to generate main menu ----------
	private function generate_main_nav_menu($id, $parent_id="", $tabindex=1, $current_level=0, $current_level_pos=1)
	{	
		$current_level++;
		
		//echo "id:$id  parent_id:$parent_id<br/>";
	
		$c_dbobject = new db_object;
	
		//*** info for this page ***
		//data for the current page id if needed	
		$c_dbobject->set_select();
		$c_dbobject->set_from("pages");
		$c_dbobject->set_where("id = $id");
		$c_dbobject->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
		//$c_dbobject->set_where("id != 1");
		$c_dbobject->set_orderby("order_id");
		$result_current = $c_dbobject->get_data();
		$row_current = mysql_fetch_assoc($result_current);
		$current_name = $row_current["name"];
		//$current_filename = $row_current["filename"];
		//$current_link_name = $row_current["link_name"];	
		//*** info for this page ***
				

		//*** info on sub-pages for this page ***
		//grab subpage info/count
		//$c_dbobject = new db_object;
		$c_dbobject->set_select();
		$c_dbobject->set_from("pages");
		$c_dbobject->set_where("parent = $id");
		$c_dbobject->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
		//$c_dbobject->set_where("id != 1");
		$c_dbobject->set_orderby("order_id");	
		$sub_result = $c_dbobject->get_data();
		$sub_count = $c_dbobject->numrows;//returns the total number of rows generated
		
		if ($sub_count > 0)
		{		
			if ($id > 0)
				$content .= "<ul class=\"$current_name\">";
			else
				$content .= "<ul>";
		}
/*		else
		{
			$content .= "<ul>";	
		}	*/		
		
		while ($sub_row = mysql_fetch_assoc($sub_result))
		{		
			$temp = "";
			
			//html_entity_decode v4.3.0+ only  
			$page_name = $sub_row["name"];
			$page_filename = $sub_row["filename"];
			$page_link_name =  $this->htmlsafe($sub_row["link_name"]);
			$db_external_url = $sub_row["external_url"];
			$nav_id = $sub_row["id"];
			$parent_id = $sub_row["parent"];

			//*** get sub-page count for the page we are currently outputting ***
			$c_dbobject->set_select();
			$c_dbobject->set_from("pages");
			$c_dbobject->set_where("parent = $nav_id");
			$c_dbobject->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
			//$c_dbobject->set_where("id != 1");
			$c_dbobject->set_orderby("order_id");	
			$nextsub_result = $c_dbobject->get_data();
			$nextsub_count = $c_dbobject->numrows;//returns the total number of rows generated		
			//*** get sub-page count for the page we are currently outputting ***

			//$content .= "$last:current_level:$current_level:$current_level_pos-$sub_count:$nextsub_count"; //testing
			
			if($nav_id == $this->Page_id){
				$temp = " class=\"active\"";
			}
			
			if ($nextsub_count > 0)
			{	
				if(!empty($db_external_url))
				{
					//if an external URL exists for this page then link to that	
					$content .= "<li><a href=\"$db_external_url\"".$temp.">$page_link_name</a>";				
				}
				else
				{
					$content .= "<li><a href=\"/$page_name\"".$temp.">$page_link_name</a>";					
				}
			}
			else
			{
				if(!empty($db_external_url))
				{						
					$content .= "<li><a href=\"$db_external_url\"".$temp." title=\"$page_link_name\">$page_link_name</a>";			
				}
				else
				{
					$content .= "<li><a href=\"/$page_name\"".$temp." title=\"$page_link_name\">$page_link_name</a>";
					//$content .= "<li><a href=\"$page_link_name\" tabindex=\"$tabindex\">$page_link_name</a>";					
				}
			}

			$tabindex++;
						
			//re-call the function to look for items and subpages within the current page
			//$content .= $this->generate_main_nav_menu($nav_id, $parent_id, $tabindex);
			$content .= $this->generate_main_nav_menu($nav_id, $parent_id, $tabindex, $current_level);

			if ($nextsub_count > 0)
			{
				$content .= "</a>";
			}               
			
			$content .= "</li>";	
				
			$current_level_pos++;		
		}//end while			
		
		if ($sub_count > 0)
		{
			$content .= "</ul>";
		}		
		
		$current_level--;
		
		return $content;

	}//end generate_main_nav_menu
	//---------- START function to generate main menu ----------

}
?>