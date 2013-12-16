<?php
//contains generic functions that can be used on any site

final class search_index extends db_object
{ 
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////

	//declare database constants	
	private $DB_TABLE_SEARCH_INDEX_FIELDS = '`association_table`, `association_id`, `data`';	
	private $index_table_name = 'search_index';
	private $table_fields = array('pages'=>
											array('title', 'keywords', 'description', 'link_name', 'content'),
									'news'=>
											array('title', 'content', 'content_alt', 'author', 'synopsis')
									);
	
	public $current_page = null;
	public $records_per_page = 10;
	public $records_limit = 10;
	
	//Constructor function
	public function __construct() 
	{ 
		//fire up a connection to the database		
		parent::__construct(); 
	}
	
	/*********** setter methods **********/
	
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
	public function keyword($input_keyword = "")
	{
		$this->keyword = $this->cleanstring_plain(trim($input_keyword));
		//debug echo $this->Name;
    }

	// PUBLIC function
	public function current_page($input_current_page = "")
	{
		$this->current_page = $this->cleanstring_input(trim($input_current_page));
		//debug echo $this->Name;
    }
	
	/* indexing methods */
	
	//function to re-index content into the search index tables
	public function re_index_search($slug_id = null, $association = null){
	
		$slug_id = $this->cleanstring_input($slug_id);
		$association = $this->cleanstring_input($association);
		$search_index_data = null;
		
		if(!empty($slug_id) && !empty($association)){	
			
			//get page data
			$this->set_select();
			
			//select content based on association
			switch($association){
				case "pages":
					$this->set_from("pages");
					$this->set_where("`id` = '$slug_id'");
				break;
				case "news":
					$this->set_from("news_articles");
					$this->set_where("`article_id` = '$slug_id'");
				break;
			}
			//echo $sql_check,"<br />";
			$result = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if ($result && ($count != 0))
			{
				while($row_page = $result->fetch_object()){		
					$cnt = 0;
					foreach ($this->table_fields[$association] as $field){
						if(!empty($row_page->{$field})){
							if($cnt > 0){$search_index_data .= ' ';}
							$search_index_data .= $this->cleanstring_plain($row_page->{$field});
						}
						$cnt++;
					}
				}
			}
			
			//for pages only
			switch($association){
				case "pages":					
					//get page data
					$this->set_select('SELECT `content`');
					$this->set_from("articles");
					$this->set_where("`page_id` = '$slug_id'");
					$this->set_where("(`content` <> NULL");
					$this->set_where("`content` <> '')", "OR");
					//echo $sql_check,"<br />";
					$result = $this->get_data();
					$count = $this->numrows;//returns the total number of rows generated
					if ($result && ($count != 0))
					{
						
						//add trailing space
						if(!empty($search_index_data)){ $search_index_data .= ' ';}
						
						$cnt = 0;
						while($row_page = $result->fetch_object()){					
							if(!empty($row_page->content)){
								if($cnt > 0){$search_index_data .= ' ';}
								$search_index_data .= $this->cleanstring_plain($row_page->content);
							}
							$cnt++;
						}
					}
				break;
			}
			
			if(!empty($search_index_data)){
				
				//if a row already ecists in the table then update or create a new one
				$this->set_select('SELECT `association_id`');
				$this->set_from($this->index_table_name);
				$this->set_where("`association_table` = '$association'");
				$this->set_where("`association_id` = '$slug_id'");
				//echo $sql_check,"<br />";
				$result = $this->get_data();
				$count = $this->numrows;//returns the total number of rows generated
				if ($result && ($count != 0)){
					
					//SQL UPDATE METHOD CALLS
					$this->db_table = $this->index_table_name;
					//no need to pass in table columns 
					$this->set_update();
					$this->add_update_value("association_table", $association);
					$this->add_update_value("association_id", $slug_id);
					$this->add_update_value("data", $search_index_data);
					$this->set_where("`association_id` = $slug_id");
			
					//echo $sql;
					$result = $this->update_data();	
					$new_article_id = $result;
					
				}else{
					
					//create new row
					$this->db_table = $this->index_table_name;
					$this->set_insert($this->DB_TABLE_SEARCH_INDEX_FIELDS); // "association_table, association_id, data"
					//add argument to insert values array
					$this->add_insert_value($association);
					$this->add_insert_value($slug_id);
					$this->add_insert_value($search_index_data);
					
					//call method to create insert query
					//returns the row_id for the inserted item
					$result = $this->insert_data();
					$new_article_id = $result;
				}
			}
		}
		
	}
	
	//function to delete index from search index tables
	public function delete_index_search($slug_id = null, $association = null){
	
		$slug_id = $this->cleanstring_input($slug_id);
		$association = $this->cleanstring_input($association);
		
		if(!empty($slug_id) && !empty($association)){
				
				//if a row already ecists in the table then update or create a new one
				$this->set_select('SELECT `association_id`');
				$this->set_from($this->index_table_name);
				$this->set_where("`association_table` = '$association'");
				$this->set_where("`association_id` = '$slug_id'");
				//echo $sql_check,"<br />";
				$result = $this->get_data();
				$count = $this->numrows;//returns the total number of rows generated
				if ($result && ($count != 0)){					
						
					$this->db_table = $this->index_table_name;
					$this->set_delete();//initialise delete SQL
					$this->set_where("`association_table` = '$association'");
					$this->set_where("`association_id` = $slug_id");
					$num_affect_rows = $this->delete_data();	
					
				}
		}
		
	}
	
	/* front-end methods */
	
	public function search_query(){
		
		if(!empty($this->keyword)){
			$this->set_select("SELECT `SearchIndex`.`association_id`, `SearchIndex`.`association_table`, `Page`.`link_name`, `Page`.`name`, `Page`.`description`, `Article`.`title`, `Article`.`synopsis`, `Article`.`slug`, MATCH(`SearchIndex`.`data`) AGAINST('\"$this->keyword\"' IN BOOLEAN MODE) AS score ");
			$this->set_from("search_index AS `SearchIndex` LEFT JOIN `oots_pages` AS `Page` ON (`SearchIndex`.`association_table` = 'pages' AND `SearchIndex`.`association_id` = `Page`.`id`) LEFT JOIN `oots_news_articles` AS `Article` ON (`SearchIndex`.`association_table` = 'news' AND `SearchIndex`.`association_id` = `Article`.`article_id`)");
			$this->set_where("MATCH(`SearchIndex`.`data`) AGAINST('\"$this->keyword\"' IN BOOLEAN MODE) AND ((`Page`.`id` IS NOT NULL AND `Page`.`search_page`=1) OR `Article`.`article_id` IS NOT NULL)");
			$this->set_orderby("`score` DESC");
			
			$this->set_page_num($this->page_number);//default value is 1
			$this->set_rows_per_page($this->records_per_page);
							
			$result = $this->get_data(0);
			$count = $this->numrows;//returns the total number of rows generated
			$total_pages = $this->get_total_pages();//returns total number of pages
			
			$this->current_page('search/'.$this->htmlsafe($this->keyword));		
				
			//$get_string .= "&action=".$action;
			$navigation = $this->paging($get_string, $this->page_number, $count, $total_pages);//use paging function
			
			return array('result'=>$result, 'count'=>$count, 'total_pages'=>$total_pages, 'navigation'=>$navigation);
		}
	}
	
	//public Function
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
	
}//class search_index


?>