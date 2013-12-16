<?php
final class cms extends db_object 
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
// PRIVATE MEMBERS
/////////////////////////////////////////////////

//declare database constants
private $DB_TABLE_PAGES = "pages";
private  $DB_TABLE_PAGES_FIELDS = "name, filename, external_url, link_name, title, keywords, description, parent, template, directory, order_id, menu_page, footer_menu_page, search_page, track_page, protected_page, no_index, admin_user_id";
private $DB_TABLE_ARTICLES = "articles";
private  $DB_TABLE_ARTICLES_FIELDS = "page_id, order_id, poll_id, section, template, content, admin_user_id, temp_content, temp_admin_user_id, contact_admin_user_id, temp_timestamp";

//varable to hold object instances
private $c_generic = null;
private $c_usersadmin = null;
//get the correct root directory from the scripts physical location
private $rootdir = "";

private $ROOT_URL = NULL; // this can be set is the /content/ folder is in a sub-folder of the root

/* USE GLOBAL SETTINGS */
private $chmod_ip = SITE_FTP_HOST;
private $chmod_login = SITE_FTP_USER;
private $chmod_pass = SITE_FTP_PASS;
private $chmod_file = SITE_FTP_PATH;

//define directory to save created files to
private $html_dir = "../";
//defive the dir that the site is publishing to. leave blank when pblishing to the root dir
private $site_dir = "/";
//define templates directory
private $tmpl_dir = "templates/";
//define template file extension
private $tmpl_extension = "php";
private $URL = "";
private $parent_array = array();
private $select_parent = 0;

private $sub_page_id = 0;
private $sub_page_name = "";

private $temp = "";
private $link_type = "";
//results sets
private $row_parent;


/////////////////////////////////////////////////
// PUBLIC MEMBERS
/////////////////////////////////////////////////

//declare input variables
public $Page_id = 0;
public $Name = "";
public $old_page_name = "";
public $Filename = "";
public $External_URL = "";
public $Oldfilename = "";
public $Oldirectory = "";
public $Linkname = "";
public $Content = "";
public $Title = "";
public $Keywords = "";
public $Description = "";
public $Searchkeywords = "";
public $Parent_id = 0;
public $Template = "";
public $Directory = NULL;
public $section = "";
public $Noindex_page = NULL;
public $Order_id = 0;
public $Article_id = 0;
public $Menu_page = 0;
public $footer_menu_page = 0;
public $search_page = 0;
public $track_page = 0;
public $protected_page = 0;
public $output_string = "";
public $Poll_id = 0;
public $set_top_level_page_id = 0; //set the top level page ID that you want to limit the generated site menu to e.g. home page ID = 1, 0 is all pages

	//define ftp_on variable set to 1: live site 0: offline
	public	$ftp_on = 0; //ONLINE_FLAG;

	//CONSTRUCTOR function
	public function __construct(){	
		
		//fire up a connection to the database
		$this->connect_db();
	
		//create instance statically from registry class
		if($this->c_generic = Registry::get('generic'));
		if($this->c_usersadmin = Registry::get('usersadmin'));
		if($this->c_search_index = Registry::get('search_index'));
		if($this->c_cache = Registry::get('cache'));
		
		//set the top level ids
		$this->set_top_lvl_ids();

		$this->rootdir = $_SERVER["DOCUMENT_ROOT"];
	}//constructor
	
	
	// PUBLIC function
	public function page_id($input_id = 0)
	{	
		$this->Page_id = trim($input_id);
		//debug echo $this->Page_id;
    }

	// PUBLIC function
	public function page_name($input_name = "")
	{	
		$this->Name = $this->make_url_safe($input_name);
		//debug echo $this->Name;
    }
	
	// PUBLIC function
	public function old_page_name($input_old_page_name = "")
	{	
		$this->old_page_name = $this->make_url_safe($input_old_page_name);
		//debug echo $this->old_page_name;
    }
	
	// PUBLIC function
	public function sub_page_id($input_sub_page_id = 0)
	{	
		$this->sub_page_id = trim($input_sub_page_id);
		//debug echo $this->sub_page_id;
    }

	// PUBLIC function
	public function sub_page_name($input_sub_page_name = "")
	{	
		$this->sub_page_name = $this->make_url_safe($input_sub_page_name);
		//debug echo $this->sub_page_name;
    }
	
	// PUBLIC function
	public function page_external_url($input_url = "")
	{
		if (!empty($input_url))
		{
			//if we are trying to use a local URL for a download document then dont add prefix	
			$str = $input_url;
			$findme = "download-file";
			$pos = strpos($str, $findme);
			//echo "pos:".$pos;
	
			//link is normal - not a download
			if ($pos === false):
				//$this->link_type = "";
					
				//decode any urlencoded elements
				//$url = urldecode($input_url);
				
				//////////////////////////////////////////////////////////////////////////////////
				//if the link doesn't start with 'http://', 'ftp://' or 'https://' then add one on
				//////////////////////////////////////////////////////////////////////////////////
				preg_match_all ("/(((ht|f)tp(s?))\:\/\/)/i", $input_url, $matches);
				
				for ($i=0; $i< count($matches[0]); $i++) 
				{
					//return matched tages we want
					$TAG0= $matches[0][$i]; //returns entire matched string
					$url_protocol= $matches[1][$i]; //matches (http://|ftp://|https://)
				}
				//if the url starts with any of the following do not modify it, else add http:// prefix
				switch($url_protocol)
				{
					case "ftp://":
					case "http://":
					case "https://":
					break;
					default:
						$input_url = "http://".$input_url;					
				}									
			else: //download link - leave $input_url as is		
				//$this->link_type = "download";
				//echo "dl";			
			endif;
			
			//echo "link_type:".$this->link_type;
			
			//re-encode the URL to ensure tha the url is properly tracked
			//$url = urlencode($url);
			$this->External_URL = $input_url;
			//debug echo $this->External_URL;			
		}
    }

	// PUBLIC function
	public function page_external_url_target($input_external_url_target = 0)
	{
        if ($input_external_url_target == 1):
			$this->external_url_target = trim($input_external_url_target);
		else:
			$this->external_url_target = 0;
		endif;
		//echo "<br />this->external_url_target:".$this->external_url_target; //debug
    }

	// PUBLIC function
	public function page_old_filename($input_old_filename = "")
	{
        $this->Oldfilename = strtolower(ereg_replace( "['\"\]", "", trim($input_old_filename))).".".$this->tmpl_extension;
		//debug echo $this->Filename;
    }

	// PUBLIC function
	public function page_old_directory($input_old_directory = "")
	{
        $this->Olddirectory = strtolower(trim($input_old_directory));
		//debug echo $this->Filename;
    }

	// PUBLIC function
	public function page_linkname($input_link_name = "")
	{
        //$this->Linkname = ereg_replace( "['\"\]", "", trim($input_link_name));
		//$this->Linkname = $this->make_url_safe($input_link_name);
		//$this->Linkname = trim($input_link_name);
		$this->Linkname = cleanstring(trim($input_link_name));
		//debug echo $this->Linkname;
    }

	// PUBLIC function
	public function page_content($input_content = "")
	{
		$this->Content = strip_tags($input_content,"<a><b><strong><i><u><em><embed><p><div><span><strike><sub><sup><img><table><tbody><tfoot><thead><tr><td><th><ul><ol><li><blockquote><br><hr><h1><h2><h3><h4><pre><video>");
		//echo $this->Content; //debug
    }

	// PUBLIC function
	public function page_title($input_title = "")
	{
        $this->Title = ereg_replace( "['\"\]", "", trim($input_title));
		//debug echo $this->Title;
    }

	// PUBLIC function
	public function page_keywords($input_keywords = "")
	{
        $this->Keywords = ereg_replace( "['\"\]", "", trim($input_keywords));

		//debug echo $this->Keywords;
    }

	// PUBLIC function
	public function page_description($input_description = "")
	{
        $this->Description = ereg_replace( "['\"\]", "", trim($input_description));
		//debug echo $this->Description;
    }

	// PUBLIC function
	public function page_parent_id($input_parent_id = 0)
	{
        $this->Parent_id = trim($input_parent_id);
		//debug echo $this->Parent_id;
    }
	
	// PUBLIC function
	public function select_parent($input_select_parent = 0)
	{	
        $this->select_parent = trim($input_select_parent);
		//debug echo $this->select_parent;
    }	

	// PUBLIC function
	public function page_template($input_template = "")
	{
        $this->Template = trim($input_template);
		//echo "<br />this->Template:".$this->Template; //debug
    }	

	// PUBLIC function
	public function page_filename($input_filename = "")
	{
       //last 3 chars of full file name name - set extension to php for the page if needed
	  
		$this_page_ext = substr($this->Template, -3, 3); // last x chars
		
		if ($this_page_ext=="php")
		{
			$this->tmpl_extension = "php";
		}else{
			$this->tmpl_extension = "html";
		}	
	
        $this->Filename = $this->make_url_safe($input_filename).".".$this->tmpl_extension;
		//debug echo $this->Filename;
    }

	// PUBLIC function
	public function page_directory($input_dir = "")
	{

		if(!empty($input_dir) && $input_dir <> "none"){
			//regular expression that matches a string doesn't start with an '/' AND does end with '/'
			$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
			$dir = $input_dir;
			//echo $string;
			if(ereg($reg_ex, $dir))
			{
				$dir = $this->html_dir.$dir;
			}
			else
			{
				if($dir{0}=="/")
				{
					$dir = substr($dir, 1);
				}
				if (substr($dir, -1) == "/")
				{
					$dir = substr($dir, 0, -1);
				}
				$dir = $dir."/";
				$dir = $this->html_dir.$dir;
			}
			//echo $chmod_dir;
			//echo $dir;
			$this->Directory = trim($dir);
			//debug echo $this->Template;
		}
		else if(!empty($input_dir) && $input_dir == "none")
		{
			$this->Directory = $this->html_dir;
		}	
    }//end page_directory

	// PUBLIC function
	public function section($input_section = "")
	{
        $this->Section = trim($input_section);
		//debug echo $this->Template_id;
    }

	// PUBLIC function
	public function order_id($input_order_id = "")
	{
        $this->Order_id = trim($input_order_id);
		//echo $this->Order_id; //debug 
    }

	public function article_id($input_article_id = "")
	{
        $this->Article_id = trim($input_article_id);
		//debug echo $this->Template_id;
    }

	public function poll_id($input_poll_id = "")
	{
        $this->Poll_id = trim($input_poll_id);
		//debug echo $this->Template_id;
    }
	
	// PUBLIC function
	public function menu_page($input_menu_page = 0)
	{
        $this->Menu_page = trim($input_menu_page);
		//debug 
    }
	
	// PUBLIC function
	public function footer_menu_page($input_footer_menu_page = 0)
	{
        $this->footer_menu_page = trim($input_footer_menu_page);
		//debug 
    }
	
	// PUBLIC function
	public function search_page($input_search_page = 0)
	{
        $this->search_page = trim($input_search_page);
		//echo "<br />this->search_page:".$this->search_page; //debug
    }		
	
	// PUBLIC function
	public function track_page($input_track_page = 0)
	{
        $this->track_page = trim($input_track_page);
		//echo "<br />this->track_page:".$this->track_page; //debug
    }
	
	// PUBLIC function
	public function protected_page($input_protected_page = 0)
	{
        $this->protected_page = trim($input_protected_page);
		//echo "<br />this->protected_page:".$this->protected_page; //debug
    }
	
	// PUBLIC function
	public function noindex_page($input_noindex_page = 0)
	{
        $this->Noindex_page = trim($input_noindex_page);
		//debug 
    }
	
	// PUBLIC function
	public function admin_user_id($input_admin_user_id = 0)
	{
        $this->Admin_user_id = trim($input_admin_user_id);
		//debug echo $this->Admin_user_id;
    }	

	// PUBLIC function
	public function contact_admin_user_id($input_contact_admin_user_id = 0)
	{
        $this->Contact_admin_user_id = trim($input_contact_admin_user_id);
		//debug echo $this->Contact_admin_user_id;
    }	


	// PUBLIC function	
	//insert page details into the database
	public function insert_page_db()
	{
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("parent = '".$this->Parent_id."'");
		//******************* check page name doesnt already exist 
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$page_count = $this->numrows;//returns the total number of rows generated
		$page_count = $page_count + 1;//set order_id for new page based on total page count
	
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("name = '".$this->Name."'");
		$this->set_where("directory = '".$this->Directory."'");
		//******************* check page name doesnt already exist 
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
	
		if ($count > 0)
		{
			return "Error: There is already a page with name = ".$this->Name.".";
		}
		else
		{
			//echo "t:".$this->Template;
			//exit;
			
			//specify table name
			$this->db_table = "pages";
			$this->set_insert($this->DB_TABLE_PAGES_FIELDS);
			//add argument to insert values array
			$this->add_insert_value($this->Name, "input");
			$this->add_insert_value($this->Filename, "input");
			$this->add_insert_value($this->External_URL, "input");
			$this->add_insert_value($this->Linkname, "input");
			$this->add_insert_value($this->Title, "input");
			$this->add_insert_value($this->Keywords, "input");
			$this->add_insert_value($this->Description, "input");
			$this->add_insert_value($this->Parent_id, "input");
			$this->add_insert_value($this->Template, "input");
			$this->add_insert_value($this->Directory, "input");
			$this->add_insert_value($page_count, "input");
			$this->add_insert_value($this->Menu_page, "input");
			$this->add_insert_value($this->footer_menu_page, "input");
			$this->add_insert_value($this->search_page, "input");
			$this->add_insert_value($this->track_page, "input");
			$this->add_insert_value($this->protected_page, "input");
			$this->add_insert_value($this->Noindex_page, "input");
			//user id last change - set to logged in article creator to begin with
			$this->add_insert_value($_SESSION ['s_admin_id']);			
			//call method to create insert query
			//returns the row_id for the inserted tiem
			//echo "Searchkeywords".$this->Searchkeywords;
			$this->Page_id = $this->insert_data(0);
			if ($this->Page_id)
			{		
				if(!empty($this->Page_id)){
					//create file
					$msg_output = $this->create_page_html($this->tmpl_dir, $this->Directory);

					//clear any page cache files
					$this->c_cache->clear_cache();
				}
				
				return "Page created<br />".$msg_output;
			}
			else
			{
				return "Error: Failed to add page";
			}		
		}//end insert page
	}//insert_page_db



	// PUBLIC function
	//insert page details into the database
	public function update_page_db()
	{	
		//grab info for this page
		if (isset($this->Page_id))
		{
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("id = $this->Page_id");	
			
			$result_select = $this->get_data();
			$count_select = $this->numrows;//returns the total number of rows generated
			
			$row_select = $result_select->fetch_assoc();
			$old_parent_id = $row_select["parent"];
			$old_order_id = $row_select["order_id"];
		}//end if
	
		//******************* check page name doesnt already exist *******************
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("name = '".$this->Name."'");
		$this->set_where("directory = '".$this->Directory."'");
		$this->set_where("id <> ".$this->Page_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
				
		if ($count > 0)
		{
			return "Error: There is already a page with name = ".$this->Name." with the id ".$this->Page_id.".";
		}
		else
		{		
			$reorder = 1;
		
			//grab count for moving page to end of new parent
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("order_id is not NULL");
			$this->set_where("parent = '".$this->Parent_id."'");
			$result_check = $this->get_data(0);
			$page_count = $this->numrows;//returns the total number of rows generated
			$page_count = $page_count + 1;//set order_id for new page based on total page count
			//echo "<br />page_count:".$page_count;

			//SQL UPDATE METHOD CALLS
			$this->db_table = "pages";
			//no need to pass in table columns 
			$this->set_update();
			$this->add_update_value("name", $this->Name, "input");
			$this->add_update_value("filename", $this->Filename, "input");
			$this->add_update_value("external_url", $this->External_URL, "input");
			$this->add_update_value("external_url_target", $this->external_url_target, "input");			
			$this->add_update_value("link_name", $this->Linkname, "input");
			$this->add_update_value("title", $this->Title, "input");
			$this->add_update_value("keywords", $this->Keywords, "input");
			$this->add_update_value("description", $this->Description, "input");			
			
			if ($this->Parent_id <> "")
			{
				//check that the current page has not been set to the parent page as well
				if ($this->Parent_id <> $this->Page_id)
				{
					$this->add_update_value("parent", $this->Parent_id, "input");
				}
			}
					
			if ($this->Template <> "")
			{
				$this->add_update_value("template", $this->Template, "input");
			}

			$this->add_update_value("directory", $this->Directory, "input");
			
			$this->add_update_value("timestamp", "now()", "MYSQL_FUNCTION");
			
			//check if page is being moved to a new parent - if yes move it to the end of the new parent
			if ($this->Parent_id <> "" &&  $this->Parent_id != $old_parent_id)
			{
				//count is grabbed above as u cant run one query in the middle of an update build
							
				$this->add_update_value("order_id", $page_count);
				
				//dont reorder as its moved to a new parent and goes at the end
				$reorder = 0;
				//need to update the orders of the old category after the update had gone through below
				$update_old_orders = "yes";
				
			}//end if			
			
			
			$this->add_update_value("menu_page", $this->Menu_page, "input");
			$this->add_update_value("footer_menu_page", $this->footer_menu_page, "input");
			$this->add_update_value("search_page", $this->search_page, "input");
			$this->add_update_value("track_page", $this->track_page, "input");
			$this->add_update_value("protected_page", $this->protected_page, "input");
			$this->add_update_value("no_index", $this->Noindex_page, "input");			
			$this->add_update_value("admin_user_id", $_SESSION ['s_admin_id'], "input");				
			
			$this->set_where("id = ".$this->Page_id."");	

			//echo $sql;
			$result = $this->update_data();
			
			//need to update the orders of the old category after the update had gone through if moved to new parent page


			if ($update_old_orders == "yes")
			{
				//SQL UPDATE METHOD CALLS
				$this->db_table = "pages";
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $old_order_id");
				$this->set_where("parent = $old_parent_id");
		
				$result = $this->update_data(0);						
				
			}//end if		
			
			//set new order_id for current page - but not if we moved to a new category cos it must go at the end
			if ($reorder == 1)
			{			
				//re-order pages based on input order_id
				$this->reorder_page_db();
			}

			/*//chmod the ftp folder to 0777*/
			if($this->ftp_on == 1){
				$chmod_write = $this->chmod_write();
				if ($chmod_write <> "" )
				{
					echo $chmod_write;
				}
			}
			//then remove the old file from the file filesystem
			$deleted = $this->delete_page_html($this->Olddirectory.$this->Oldfilename);
			
			//clear any page cache files
			$this->c_cache->clear_cache();

			if($this->ftp_on == 1){
				/*//chmod the ftop folder to 0755*/
				$chmod_read = $this->chmod_read();
				if ($chmod_read <> "")
				{
					echo $chmod_read;
				}
			}
			
			if ($deleted <> 0)
			{
				
				$page_update = "Error: $filename could not be deleted.";
			}
			else
			{						
				if(!empty($this->Page_id)){
					//create file
					$update_result = $this->create_page_html($this->tmpl_dir, $this->Directory);
				}
				
				return "Page updated<br />" . $update_result;
			}
		
		}//end insert page	
	}//end update_page_db



	// PUBLIC function	
	//insert page details into the database
	public function insert_article_db(&$new_article_id,$auth=0)
	{
		//check the current template
		// if the selected template is different 
		// from the current one then update the 
		// pages table of the database

		//******************* get page name
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = ".$this->Page_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		$row = $result_check->fetch_object();
		
		//assign page vars
		$current_template = $row->template;
		$this->Directory = $row->directory;
		$this->Filename = $row->filename;	
		$this->Name = $row->name;	

		if (($current_template <> $this->Template) && ($this->Template <> ""))
		{
			//SQL UPDATE METHOD CALLS
			$this->db_table = "pages";
			//no need to pass in table columns 
			$this->set_update();
			$this->add_update_value("template", $this->Template, "HTML");
				
			$this->set_where("id = ".$this->Page_id."");	
	
			//echo $sql;
			$result = $this->update_data();
		}//end if

		$this->set_select();
		$this->set_from("articles");
		$this->set_where("page_id = ".$this->Page_id);
		$this->set_where("section = '".$this->Section."'");
		$this->set_where("template = '".$this->Template."'");
		//******************* check page name doesnt already exist 
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

			
		//specify table name
		$this->db_table = "articles";
		$this->set_insert($this->DB_TABLE_ARTICLES_FIELDS);
		//add argument to insert values array
		$this->add_insert_value($this->Page_id);

		//order the input article
		//increment current order_id by 1
		$this->Order_id = $count + 1;

		$this->add_insert_value($this->Order_id, "input");
		$this->add_insert_value($this->Poll_id, "input");			
		$this->add_insert_value($this->Section, "input");
		$this->add_insert_value($this->Template, "input");
		
		if ($auth==1)
		{
			$this->add_insert_value("", "input");
			$this->add_insert_value("", "input");		
			$this->add_insert_value($this->Content, "ck_editor");			
			$this->add_insert_value($_SESSION ['s_admin_id'], "input"); //user id last change - set to logged in article creator to begin with
			$this->add_insert_value($this->Contact_admin_user_id, "input"); //user id of admin to notify
		}
		else
		{
			$this->add_insert_value($this->Content, "ck_editor");
			$this->add_insert_value($_SESSION ['s_admin_id'], "input"); //user id last change - set to logged in article creator to begin with
			$this->add_insert_value("", "input");
			$this->add_insert_value("0", "input");
			$this->add_insert_value("0", "input");	
		}//end if ($auth==1)
		
		$this->add_insert_value("now()", "MYSQL_FUNCTION");
		
		//call method to create insert query
		//returns the row_id for the inserted item
		$result = $this->insert_data(0);
		$new_article_id = $result;
		
		if($this->ftp_on == 1){
			//chmod the ftop folder to 0777
			$chmod_write = $this->chmod_write();
			if($chmod_write <> "" )
			{
				echo $chmod_write;
				exit;
			}
		}
		
		//then remove the old file from the file filesystem
		$deleted = $this->delete_page_html($this->Directory.$this->Filename);
		
			
		if($deleted <> 0 )
		{
			return $msg_output = "Error: $this->Filename could not be deleted.";						
		}
				
		if(!empty($this->Page_id)){
			//create file
			$msg_output = $this->create_page_html($this->tmpl_dir, $this->Directory);
		}
		
		if($this->ftp_on == 1){
			//chmod the ftop folder to 0755
			$chmod_read = $this->chmod_read();
			if($chmod_read <> "" )
			{
				echo $chmod_read;
				exit;
			}
		}
		
		if ($result)
		{
			return "Article created<br />".$msg_output;
		}
		else
		{
			return "Error: Failed to add Article";
		}	
	}//end insert_article_db



	// PUBLIC function
	//insert page details into the database
	public function update_article_db($auth=0)
	{
		//check the current template
		// if the selected template is different 
		// from the current one then update the 
		// pages table of the database

		//******************* get page name
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = ".$this->Page_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		$row = $result_check->fetch_object();
		
		$current_template = $row->template;

		if ( ($current_template <> $this->Template) && ($this->Template <> "") )
		{

			//SQL UPDATE METHOD CALLS
			$this->db_table = "pages";
			//no need to pass in table columns 
			$this->set_update();
			$this->add_update_value("template", $this->Template, "input");
				
			$this->set_where("id = ".$this->Page_id."");	
	
			//echo $sql;
			$result = $this->update_data();

		}//end if

		//******************* get page name
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = ".$this->Page_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		$row = $result_check->fetch_object();

		//get the curent page details
		$this->Oldfilename = $row->filename;		
		$this->Olddirectory = $row->directory;	
		$this->Directory = $row->directory;
		$this->Filename = $row->filename;	
		$this->Name = $row->name;	

		//******************* check article exists
		$this->set_select();
		$this->set_from("articles");
		$this->set_where("article_id = ".$this->Article_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		$row =$result_check->fetch_object();

		$current_template = $row->template;
	
		//ADMINS CAN EDIT THE USERS CHANGE AS WELL AS AUTHORING/REJECTING THE USERS CHANGE
		//******************** SECURITY LEVEL TEST ************************
		//*** ONLY LEVEL (X) OR BETTER ALLOWED TO ACCESS THIS PAGE/CODE ***
		$set_level = $_SESSION ['s_admin_level'];
		$required_level = 2;
		if ( ($required_level >= $set_level) && ($set_level > 0) )
		{
			$success_flag=1; //level ok
		}
		else
		{	
			$success_flag=0; //level failed
		}
		//******************** SECURITY LEVEL TEST ************************		
	
		//TEST - if content waiting for auth and not done by current logged in user then abort the update - unless the user is an admin
		if ( $row->temp_content != "" && $row->temp_admin_user_id != $_SESSION ['s_admin_id'] && $success_flag==0 ) //
		{
			return "Error: content waiting for authorisation. Cannot update.";
		}
		else if ($count < 1)
		{
			return "Error: There is no article with the id ".$this->Article_id.".";
		}
		else
		{	

			//SQL UPDATE METHOD CALLS
			$this->db_table = "articles";
			//no need to pass in table columns 
			$this->set_update();

			if ($auth==1)
			{
				$this->add_update_value("temp_content", $this->Content, "ck_editor");
				$this->add_update_value("temp_timestamp", "now()", "MYSQL_FUNCTION");
				$this->add_update_value("temp_admin_user_id", $_SESSION ['s_admin_id'], "input");
				$this->add_update_value("contact_admin_user_id", $this->Contact_admin_user_id, "input"); 					
			}
			else
			{
				$this->add_update_value("content", $this->Content, "ckl_editor");
				$this->add_update_value("timestamp", "now()", "MYSQL_FUNCTION");
				$this->add_update_value("admin_user_id", $_SESSION ['s_admin_id'], "input");	
			}//end if ($auth==1)

			//if the user has selected a new template whilst updating the current page then
			// check that the template is different and then pog it into the articles table
			if($current_template <> $this->Template)
			{
				$this->add_update_value("template", $this->Template, "input");			
			}

			$this->add_update_value("poll_id", $this->Poll_id, "input");

			$this->set_where("article_id = ".$this->Article_id."");
			
			$result = $this->update_data(0);
			
			//only run reorder if that article is not a poll
			//re-order articles based on input order_id
			$this->reorder_article_db();

			if($this->ftp_on == 1){
				//chmod the ftop folder to 0777
				$chmod_write = $this->chmod_write();
				if($chmod_write <> "" )
				{
					echo $chmod_write;
					exit;
				}
			}
			
			//then remove the old file from the file filesystem
			$deleted = $this->delete_page_html($this->Directory.$this->Filename);
			
				
			if($deleted <> 0 )
			{
				return $msg_output = "Error: $this->Filename could not be deleted.";						
			}
					
			if(!empty($this->Page_id)){
				//create file
				$msg_output = $this->create_page_html($this->tmpl_dir, $this->Directory);
			}
			
			if($this->ftp_on == 1){
				//chmod the ftop folder to 0755
				$chmod_read = $this->chmod_read();
				if($chmod_read <> "" )
				{
					echo $chmod_read;
					exit;
				}
			}

			return "Article updated<br />".$msg_output;		
						
		}// end insert page	
	}//end update_article_db



	// PUBLIC function
	//auth article changes by shifing temp cells to live ones and blanking the temp cells at the end
	public function auth_article_db($type="accept")
	{
		//******************** SECURITY LEVEL TEST ************************
		//*** ONLY LEVEL (X) OR BETTER ALLOWED TO ACCESS THIS PAGE/CODE ***
		$set_level = $_SESSION ['s_admin_level'];
		$required_level = 2;
		if ( ($required_level >= $set_level) && ($set_level > 0) )
		{
			//ok
		}
		else
		{	
			return "Sorry your access level is not high enough to authorise content.";
		}
		//******************** SECURITY LEVEL TEST ************************			
	
	
		//******************* check article exists *******************
		$this->set_select();
		$this->set_from("articles");
		$this->set_where("article_id = ".$this->Article_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		$row = $result_check->fetch_object();

		$current_template = $row->template;
	
		if ($count < 1)
		{
			return "Error: There is no article with the id ".$this->Article_id.".";
		}
		else
		{	
			//SQL UPDATE METHOD CALLS
			$this->db_table = "articles";
			//no need to pass in table columns 
			$this->set_update();

			//if accepted copy data to live cells - if not just blank the temp ones below
			if ($type=="accept")
			{
				$this->add_update_value("content", $row->temp_content, "ck_editor");
				$this->add_update_value("timestamp", $row->temp_timestamp, "input");
				$this->add_update_value("admin_user_id", $row->temp_admin_user_id, "input");
			}//end if
			
			//clear the temp fields
			$this->add_update_value("temp_content", "", "ck_editor");
			$this->add_update_value("temp_timestamp", "", "input");
			$this->add_update_value("temp_admin_user_id", "input");
			$this->add_update_value("contact_admin_user_id", "", "input");			

			$this->set_where("article_id = ".$this->Article_id."");
			
			$result = $this->update_data(0);
			
			//only run reorder if that article is not a poll
			//re-order articles based on input order_id
			//$this->reorder_article_db();

			if ($type=="accept")
				return "Article content changes were published successfully.";		
			else
				return "Article content changes were declined successfully.";	
						
		}//end
	}//end auth_article_db



	// PUBLIC function
	public function reorder_article_db()
	{		
		/*debug
		echo "Article_id".$this->Article_id;
		echo "Order_id".$this->Order_id;
		echo "Section".$this->Section;
		echo "Template".$this->Template;
		*/
		if (isset($this->Section) && isset($this->Template) && isset($this->Article_id) && isset($this->Page_id))
		{
			$this->set_select();
			$this->set_from("articles");
			$this->set_where("section = '$this->Section'");
			$this->set_where("template = '$this->Template'");
			$this->set_where("article_id = $this->Article_id");
			$this->set_where("page_id = $this->Page_id");
	
			$order_result = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
	
			$order_row = $order_result->fetch_assoc();
		
			$this->Current_order_id = $order_row["order_id"];
			//echo "Current_order_id".$this->Current_order_id;
		
			if ($this->Current_order_id == $this->Order_id) // order unchanged
			{
				//DO NOTHING
			}
			else if ($this->Current_order_id > $this->Order_id) // order higher
			{	
				//SQL UPDATE METHOD CALLS
				$this->db_table = "articles";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "(order_id + 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id >= $this->Order_id");
				$this->set_where("order_id < $this->Current_order_id");
				$this->set_where("section = '$this->Section'");
				$this->set_where("template = '$this->Template'");
				$this->set_where("page_id = '$this->Page_id'");	
		
				//echo $sql;
				$result = $this->update_data();
	
				//SQL UPDATE METHOD CALLS
				$this->db_table = "articles";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "$this->Order_id", "input");
				$this->set_where("article_id = $this->Article_id");	
		
				//echo $sql;
				$order_result = $this->update_data();

				$update = "success";
	
				//echo "" . $update_order . "<br />";
			}
			else if ($this->Current_order_id < $this->Order_id ) // order lower
			{
				//SQL UPDATE METHOD CALLS
				$this->db_table = "articles";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $this->Current_order_id");
				$this->set_where("order_id <= $this->Order_id");
				$this->set_where("section = '$this->Section'");
				$this->set_where("template = '$this->Template'");
				$this->set_where("page_id = '$this->Page_id'");	
			
				//echo $sql;
				$update_result = $this->update_data();
	
	
				//SQL UPDATE METHOD CALLS
				$this->db_table = "articles";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "$this->Order_id", "input");
				$this->set_where("article_id = $this->Article_id");
	
				$order_result = $this->update_data();
	
				$update = "success";
			}//end if
		}//end if	
			
		if ($update == "success")
		{
?>
			<div class="box-error">Display Order has been Updated.</div>
<?php
		}
		
		return "Article Order updated";
	}//end reorder_article_db


	// PUBLIC function
	//---------- START function to update page orders ----------
	public function reorder_page_db($debug=0)
	{
		//echo "<br />this->Order_id:".$this->Order_id; //debug 
	
		if (!empty($this->Page_id) && !empty($this->Order_id))
		{
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("id = $this->Page_id");	
	
			$order_result = $this->get_data(0);
			$count = $this->numrows;//returns the total number of rows generated
	
			$order_row = $order_result->fetch_assoc();
			$order_id = $order_row["order_id"];
			$parent_id = $order_row["parent"];	
			
			$nu_order_id = $this->Order_id;
		
			
			if ($order_id > $nu_order_id) // order higher
			{	
				//SQL UPDATE METHOD CALLS
				$this->db_table = "pages";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "(order_id + 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id >= $nu_order_id");
				$this->set_where("order_id < $order_id");
				$this->set_where("parent = $parent_id");
		
				//echo $sql;
				$result = $this->update_data();		
	
					//SQL UPDATE METHOD CALLS
					$this->db_table = "pages";
					//no need to pass in table columns 
					$this->set_update();
					$this->add_update_value("order_id", "$nu_order_id", "input");
					$this->set_where("id = $this->Page_id");	
			
					//echo $sql;
					$order_result = $this->update_data();
					$update = "success";	
			}
			else if ($order_id < $nu_order_id) // order lower
			{
				//SQL UPDATE METHOD CALLS
				$this->db_table = "pages";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $order_id");
				$this->set_where("order_id <= $nu_order_id");
				$this->set_where("parent = $parent_id");
			
				//echo $sql;
				$update_result = $this->update_data();		
			
				//SQL UPDATE METHOD CALLS
				$this->db_table = "pages";
				//no need to pass in table columns 
				$this->set_update();
				$this->add_update_value("order_id", "$nu_order_id", "input");
				$this->set_where("id = $this->Page_id");
	
				$order_result = $this->update_data();
				$update = "success";
			}//end if ($order_id
		}//end if
		//debug code
		if ($debug==1)
		{
			echo "<div>reorder_page_db testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;		
	}//reorder_page_db
	//---------- END function to update page orders ----------


	// PUBLIC function
	//---------- START function to update page orders on page DELETE ----------
	public function reorder_page_deleted($debug=0)
	{
		$debug_string.= "<br />this->Order_id:".$this->Order_id;
		$debug_string.= "<br />this->Parent_id:".$this->Parent_id;
			
		if ($this->Order_id > 0 && $this->Parent_id >= 0)
		{
			//re-order pages now one is deleted
			//UPDATE ORDERS
			$this->db_table = $this->DB_TABLE_PAGES;
			$this->set_update();
			$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
			$this->set_where("order_id > ".$this->Order_id);
			$this->set_where("parent = '".$this->Parent_id."'");
			$result_order = $this->update_data(0);				
			$debug_string.= "<br />result_order:".$result_order;		
		}

		//debug code
		if ($debug==1)
		{
			echo "<div><hr />reorder_page_deleted testvar:$debug_string<hr /></div>";
		}//end if ($debug==1)			
		return $fcn_msg;		
	}//reorder_page_deleted
	//---------- START function to update page orders on page DELETE ----------

	
	// PUBLIC function
	//---------- START function to reset ALL page/sub-level orders alphabetically ----------
	public function reset_page_orders($debug=0)
	{
		$fcn_msg = "";
		$debug_string = "";	
	
		$this->set_select("SELECT DISTINCT parent");
		$this->set_from("pages");
		//if only resetting one specific category - inc top level. if select_parent is blank then reset ALL
		if ($this->select_parent >= 0):
			//$this->set_where("parent = ".$this->select_parent."");	
			$this->set_where("id = ".$this->Page_id."");			
		endif;
		$result = $this->get_data(0);
		$count = $this->numrows; //returns the total number of rows generated

		if (!$result && $count == 0)
		{
			$fcn_msg = 0;	
		}
		else
		{
			while ($row = $result_check->fetch_object())
			{
				$parent = $row->parent;		
				//echo "<br />p:".$parent;
			
				$this->set_select("SELECT id");
				$this->set_from("pages");
				$this->set_where("parent = $parent");
				$this->set_orderby("link_name");	
		
				$result2 = $this->get_data(0);
				$cnt = 1;
		
				while ($row2 = $result2->fetch_object())
				{
					$page_id = $row2->id;
					//echo "page_id:".$page_id."&nbsp;";
					
					//SQL UPDATE METHOD CALLS
					$this->db_table = "pages";
					//no need to pass in table columns 
					$this->set_update();
					$this->add_update_value("order_id", "$cnt", "input");
					$this->set_where("id = $page_id");
		
					$order_result = $this->update_data(0);
					$update = "success";
					
					$cnt++;
				}//end while
							
			}//end while			
			$fcn_msg = 1;
		}//end select
		
		//debug code
		if ($debug==1)
		{
			echo "<div>reset_page_orders testvar:$debug_string</div>";
		}//end if ($debug==1)			
		
		return $fcn_msg;		
		
	}//reset_page_orders
	//---------- END function to reset ALL page/sub-level orders alphabetically ----------


	// PUBLIC function
	public function delete_page_db($page_id=NULL)
	{	
		//use object vaiable in funtion if no page_id is passed as function argument
		if ($page_id == NULL)
		{
			$page_id = $this->Page_id;
		}
		// check if page exists
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = ".$page_id."");
		
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		
		if ($count == 0)
			echo "Error: Invalid Page, page not deleted.";	
		else
		{
			$row = $result_check->fetch_assoc();
			$this->Filename = $row["filename"];
			$this->Directory = $row["directory"];
			$this->Parent_id = $row["parent"];
			$this->Order_id = $row["order_id"];
			
			//CANT DELETE TOP LEVEL PAGES OR THE LEFTMENU PAGES PAGES
			//1=content-panels, 3=site-pages, 18=news
			if ($this->Parent_id == 0 || $page_id == 1 
				|| $this->Parent_id == 3 || $page_id == 3 
				|| $page_id == 18) // $this->Name=="leftmenupages"
				return "Error: cannot delete Top Level pages or the CONTENT PANELS/SITE PANELS/NEWS page."; //or the X Page.			
							
			if ($this->ftp_on == 1){
				//chmod the ftop folder to 0777
				$chmod_write = $this->chmod_write();
				if ($chmod_write <> "")
				{
					echo $chmod_write;
				}
			}
			
			//delete the htm file from the filesystem 
			$deleted = $this->delete_page_html($this->Directory.$this->Filename);
			
			if ($this->ftp_on == 1)
			{
				//chmod the ftop folder to 0755
				$chmod_read = $this->chmod_read();
				if ($chmod_read <> "")
				{
					echo $chmod_read;
				}
			}

			if ($deleted <> 0)
			{
				echo "Error: $filename could not be deleted.";
			}
			else
			{
				//delete all entries for this page from the pages table
				//SQL DELETE METHOD CALLS
				$this->db_table = "pages";
				$this->set_delete();//initialise delete SQL
				$this->set_where("id = $page_id");
				//echo $this->query."<br />";
				$this->delete_data();
				
				//clear any page cache files
				$this->c_cache->clear_cache();

				//delete all entries for this page from the articles table
				//SQL DELETE METHOD CALLS
				$this->db_table = "articles";
				$this->set_delete();//initialise delete SQL
				$this->set_where("page_id = $page_id");
				$this->delete_data();
				
				//--------------------------------------------------------------------
				//re-order pages now one is deleted
				$this->reorder_page_deleted(DEBUG_FCN_DISPLAY_FLAG);
				//--------------------------------------------------------------------
			}
					
			//next check for any child pages and delete them by recalling this function
			// check if page exists
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("parent = ".$page_id."");
			$result_check = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated

			if ($result_check && ($count > 0))
			{
				while ($row = $result_check->fetch_object())
				{
					//a spot of recurrsion here
					$this->delete_page_db($row->id);
				}

			}		
			
			return "Page deleted.";			
		}
	}//delete_page_db
	


	// PUBLIC function
	public function delete_article_db()
	{
		//check the current template
		// if the selected template is different 
		// from the current one then update the 
		// pages table of the database

		//******************* get page name
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = ".$this->Page_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		$row = $result_check->fetch_object();

		//get the curent page details
		$this->Oldfilename = $row->filename;		
		$this->Olddirectory = $row->directory;	
		$this->Directory = $row->directory;
		$this->Filename = $row->filename;	
		$this->Name = $row->name;
		
		$current_template = $row->template;

		if (($current_template <> $this->Template) && ($this->Template <> ""))
		{
			//SQL UPDATE METHOD CALLS
			$this->db_table = "pages";
			//no need to pass in table columns 
			$this->set_update();
			$this->add_update_value("template", $this->Template, "input");
				
			$this->set_where("id = ".$this->Page_id."");	
	
			//echo $sql;
			$result = $this->update_data();
		}//end if

		// check if page exists
		$this->set_select();
		$this->set_from("articles");
		$this->set_where("article_id = ".$this->Article_id."");
		//echo $sql_check,"<br />";
		$result_check = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		
		if ($count == 0)
			return "Error: Invalid Article, article not deleted.";	
		else
		{
			//get the order_id of the article being deleted
			$order_row = $result_check->fetch_assoc();
			$this->Current_order_id = $order_row["order_id"];
			$this->Section = $order_row["section"];
			$this->Template = $order_row["template"];
			//UPDATE THE ORDER TO ACCOUNT FOR REMOVED ITEM
			//SQL UPDATE METHOD CALLS
			$this->db_table = "articles";
			//no need to pass in table columns 
			$this->set_update();
			$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
			$this->set_where("order_id > $this->Current_order_id");
			$this->set_where("page_id = $this->Page_id");
			$this->set_where("section = '$this->Section'");
			$this->set_where("template = '$this->Template'");	
				
			//echo $sql;
			$update_result = $this->update_data();

			//SQL DELETE METHOD CALLS
			$this->db_table = "articles";
			$this->set_delete();//initialise delete SQL
			$this->set_where("article_id = $this->Article_id");
			$this->delete_data();				
			
			//then remove the old file from the file filesystem
			$deleted = $this->delete_page_html($this->Directory.$this->Filename);
			
				
			if($deleted <> 0 )
			{
				return $msg_output = "Error: $this->Filename could not be deleted.";						
			}

			if(!empty($this->Page_id)){
				//create file
				$msg_output = $this->create_page_html($this->tmpl_dir, $this->Directory);
			}

			return "Article deleted<br />".$msg_output;		
		}//end if
	}//end delete_article_db


	//PUBLIC function
	public function get_page_menu(){
		
		$this->page_array = null;
		
		$args = func_get_args(); // return array of arguments			
		
		//first argument should be caching array		
		$cache = array_shift($args);
		
		if(is_array($cache) && $cache['cache'] !== false){
			
			//look for existing cache
			if($this->c_cache->check($cache)){
				
				$this->page_array = $this->c_cache->read();
				
				return $this->page_array;
				
			}else{

				/*
				METHODS ARGUMENTS
				************************
				$id						@page id int - default null to initiate count
				$parent_id				@page parent_id int - default null to initiate count
				$tabindex				@menu tabindex int - default 1 to initiate count
				$current_level			@menu current_level int - default 0 to initiate count
				$current_level_pos		@menu current_level_pos int - default 1 to initiate count
				$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
				$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
				optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
				************************
				*/
				
				$this->generate_pages_array($this->static_pages_id, "", 1, 0, 1, 0, 1); //list of pages under STATIC PAGES
				
				
				//create cache
				$this->c_cache->write($this->page_array);				

				return $this->page_array;
			}

		}else{

			/*
			METHODS ARGUMENTS
			************************
			$id						@page id int - default null to initiate count
			$parent_id				@page parent_id int - default null to initiate count
			$tabindex				@menu tabindex int - default 1 to initiate count
			$current_level			@menu current_level int - default 0 to initiate count
			$current_level_pos		@menu current_level_pos int - default 1 to initiate count
			$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
			$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
			optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
			************************
			*/
			
			$this->generate_pages_array($this->static_pages_id, "", 1, 0, 1, 0, 1); //list of pages under STATIC PAGES			
			
			return $this->page_array;
		}
	}

	//PUBLIC function
	public function get_sub_page_menu($page_id = null){

		$this->page_array = null;
		
		$args = func_get_args(); // return array of arguments			
		
		//first argument should be page_id	
		$page_id = intval($this->cleanstring_plain(array_shift($args)));
		//second arg is cache array
		$cache = array_shift($args);
		
		if(is_array($cache) && $cache['cache'] !== false){
			
			//look for existing cache
			if($this->c_cache->check($cache)){
				
				$this->page_array = $this->c_cache->read();
				
				return $this->page_array;
				
			}else{

				/*
				METHODS ARGUMENTS
				************************
				$id						@page id int - default null to initiate count
				$parent_id				@page parent_id int - default null to initiate count
				$tabindex				@menu tabindex int - default 1 to initiate count
				$current_level			@menu current_level int - default 0 to initiate count
				$current_level_pos		@menu current_level_pos int - default 1 to initiate count
				$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
				$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
				optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
				************************
				*/
				
				$this->generate_pages_array($page_id, "", 1, 0, 1, 0, 0); //all pages under the portfolio section
				
				//create cache
				$this->c_cache->write($this->page_array);				

				return $this->page_array;
			}

		}else{

			/*
			METHODS ARGUMENTS
			************************
			$id						@page id int - default null to initiate count
			$parent_id				@page parent_id int - default null to initiate count
			$tabindex				@menu tabindex int - default 1 to initiate count
			$current_level			@menu current_level int - default 0 to initiate count
			$current_level_pos		@menu current_level_pos int - default 1 to initiate count
			$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
			$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
			optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
			************************
			*/
			
			$this->generate_pages_array($page_id, "", 1, 0, 1, 0, 0); //all pages under the portfolio section			
			
			return $this->page_array;
		}
	

		if(!empty($page_id)){
			$this->page_array = null;
			$this->generate_pages_array($page_id, "", 1, 0, 1, 0, 0); //all pages under the portfolio section
			if(!empty($this->page_array)){
				return $this->page_array;
			}
		}
	}
	
	//PUBLIC function
	public function get_footer_page_menu(){

		$this->page_array = null;
		
		$args = func_get_args(); // return array of arguments			
		
		//first argument should be caching array		
		$cache = array_shift($args);
		
		if(is_array($cache) && $cache['cache'] !== false){
			
			//look for existing cache
			if($this->c_cache->check($cache)){
				
				$this->page_array = $this->c_cache->read();
				
				return $this->page_array;
				
			}else{

				/*
				METHODS ARGUMENTS
				************************
				$id						@page id int - default null to initiate count
				$parent_id				@page parent_id int - default null to initiate count
				$tabindex				@menu tabindex int - default 1 to initiate count
				$current_level			@menu current_level int - default 0 to initiate count
				$current_level_pos		@menu current_level_pos int - default 1 to initiate count
				$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
				$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
				optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
				************************
				*/
				
				$this->page_array = null;
				$this->generate_pages_array(132, "", 1, 0, 1, 1, 1, array('footer_menu_page' => '1')); //all pages under the portfolio section

				if(!empty($this->page_array)){
				
					//create cache
					$this->c_cache->write($this->page_array);				

					return $this->page_array;
				}
			}

		}else{

			/*
			METHODS ARGUMENTS
			************************
			$id						@page id int - default null to initiate count
			$parent_id				@page parent_id int - default null to initiate count
			$tabindex				@menu tabindex int - default 1 to initiate count
			$current_level			@menu current_level int - default 0 to initiate count
			$current_level_pos		@menu current_level_pos int - default 1 to initiate count
			$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
			$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
			optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
			************************
			*/
			
			$this->page_array = null;
			$this->generate_pages_array(132, "", 1, 0, 1, 1, 1, array('footer_menu_page' => '1')); //all pages under the portfolio section
			if(!empty($this->page_array)){
				return $this->page_array;
			}
		}		
	}


	// PRIVATE function
	//connect to the server via ftp and set a chmod
	private function ftp_connect($ip, $login, $pass, $file, $chmod_mode)
	{
		$conn_id=ftp_connect($ip);
		$login_result=@ftp_login($conn_id, $login, $pass);
		//debug echo $login_result;
		$chmod_cmd="CHMOD ".$chmod_mode." ".$file;
		$chmod=ftp_site($conn_id, $chmod_cmd);	
		//debug echo $chmod;
		if ($chmod == 1)
		{
			//$message = "<br />Succesfully ran ftp cmd: $chmod_cmd, current dir= " . ftp_pwd($conn_id) . "<br />";
		}
		else
		{
			$message = "<br />failed to run ftp cmd: $chmod_cmd, current dir= " . ftp_pwd($conn_id) . "<br />";
		}
		ftp_quit($conn_id);
		return $message;
	}//end ftp_connect
	

	// PUBLIC function
	//connect to the server via ftp and set a chmod to 777
	public function chmod_write($chmod_dir=NULL)
	{
		if(empty($chmod_dir))
		{	
			//iterate through all directories stored in the pages table and make each writeable
			$this->set_select("SELECT DISTINCT directory");
			$this->set_from("pages");
			$this->set_where("directory <> '../'");
			//echo $sql_check,"<br />";
			$result_check = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if ($count > 0)
			{
				while ($row = $result_check->fetch_object())
				{
					$current_dir = $row->directory;
									
					$tmp_dir = $current_dir;
					$tmp_dir = str_replace( "../", "", $tmp_dir);
					$tmp_dir_end = strrchr($tmp_dir, "/");
					
					if($tmp_dir_end <> "/")
					{
						$tmp_dir = str_replace($tmp_dir_end, "", $tmp_dir);
					}
					else
					{
						$tmp_dir = substr($tmp_dir, 0, -1);
					}
					//regular expression that matches a string doesn't start with an '/' OR end with and '/'
					$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
					$string = $tmp_dir;
					
					if (ereg($reg_ex, $string))
					{
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}
					else
					{
						if($string{0}=="/")
						{
							$string = substr($string, 1);
						}
						//echo $string;
						if (substr($string, -1) == "/")
						{
							$string = substr($string, 0, -1);
						}
		
						$chmod_dir = $string."/";
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}//end if
					
					//make each directory writeable
					$this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "2777");
				}//end while
			}//end if ($count
			//finally call default function call to make site root writeable
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $this->chmod_file, "2777");					
		}
		else
		{	
			//regular expression that matches a string doesn't start with an '/' OR end with and '/'
			$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
			$string = $chmod_dir;
			
			if(ereg($reg_ex, $string))
			{
				$chmod_dir = $this->chmod_file.$chmod_dir;
			}
			else
			{
				if ($string{0}=="/")
				{
					$string = substr($string, 1);
				}
				//echo $string;
				if (substr($string, -1) == "/")
				{
					$string = substr($string, 0, -1);
				}

				$chmod_dir = $string."/";
				$chmod_dir = $this->chmod_file.$chmod_dir;
			}
			//echo $chmod_dir;
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "2777");
		}
	}//end chmod_write
	
	

	// PUBLIC function
	//connect to the server via ftp and set a chmod to 777
	public function chmod_read($chmod_dir=NULL)
	{
		if (empty($chmod_dir))
		{		
			//iterate through all directories stored in the pages table and make each readable
			$this->set_select("SELECT DISTINCT directory");
			$this->set_from("pages");
			$this->set_where("directory <> '../'");
			//echo $sql_check,"<br />";
			$result_check = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if ($count > 0)
			{
				while ($row = $result_check->fetch_object())
				{
					$current_dir = $row->directory;
									
					$tmp_dir = $current_dir;
					$tmp_dir = str_replace( "../", "", $tmp_dir);
					$tmp_dir_end = strrchr($tmp_dir, "/");
					
					if ($tmp_dir_end <> "/")
					{
						$tmp_dir = str_replace($tmp_dir_end, "", $tmp_dir);
					}
					else
					{
						$tmp_dir = substr($tmp_dir, 0, -1);
					}					
					//regular expression that matches a string doesn't start with an '/' OR end with and '/'
					$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
					$string = $tmp_dir;
					
					if (ereg($reg_ex, $string))
					{
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}
					else
					{
						if($string{0}=="/")
						{
							$string = substr($string, 1);
						}
						//echo $string;
						if (substr($string, -1) == "/")
						{
							$string = substr($string, 0, -1);
						}
		
						$chmod_dir = $string."/";
						$chmod_dir = $this->chmod_file.$chmod_dir;
					}//end if
					
					//make each directory readable
					$this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "2755");
				}//end while
			}//end if ($count
			
			//finally call default function call to make site root readable
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $this->chmod_file, "2755");
		}
		else
		{
			//regular expression that matches a string doesn't start with an '/' AND does end with '/'
			$reg_ex = "(^[^/][a-zA-Z0-9]*)([a-zA-Z0-9]*[/]$)";
			$string = $chmod_dir;
			//echo $string;
			if(ereg($reg_ex, $string))
			{
				$chmod_dir = $this->chmod_file.$chmod_dir;
			}
			else
			{
				if ($string{0}=="/")
				{
					$string = substr($string, 1);
				}
				if (substr($string, -1) == "/")
				{
					$string = substr($string, 0, -1);
				}
				$chmod_dir = $string."/";
				$chmod_dir = $this->chmod_file.$chmod_dir;
			}
			//echo $chmod_dir;
			return $this->ftp_connect($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $chmod_dir, "2755");
		}//end if
	}//end chmod_read
	
	
	
	//PRIVATE FUNCTION
	//Get the ID of the top level category for any given page
	// note that default top level is set to 0
	private function get_top_parent_id($page_id, $top_level = 0)
	{
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = '$page_id'");
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		
		if($result && $count > 0)
		{
			$row = $result->fetch_object();
			
			if($row->parent <> $top_level)//in this site the top level parent ID is 1
			{
				//if this is not a top level category then use recurssion to get the next level category
				$top_page_id .= $this->get_top_parent_id($row->parent, $top_level);
			}
			else
			{
				//return the ID of the top level category
				$top_page_id = $row->id;
			}
		}
		
		return $top_page_id;
	}//end get_top_parent_id
	
	//PRIVATE FUNCTION
	//Get the ID of the top level category for any given page
	// note that default top level is set to 0
	public function get_page_level($page_id=null, $top_parent_id=null)
	{
		$page_id = (int)$this->cleanstring_input($page_id);
		$top_parent_id = (int)$this->cleanstring_input($top_parent_id);
		
		if(!empty($top_parent_id)&&!empty($page_id)){
			return	count($this->get_top_parent_id_list($page_id, $top_parent_id));
		}
	}

	private function get_top_parent_id_list($page_id, $top_level = 0)
	{
		$page_id = (int)$this->cleanstring_input($page_id);
		$top_level = (int)$this->cleanstring_input($top_level);
		
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = '$page_id'");
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated		
		
		if($result && $count > 0)
		{
			$row = $result->fetch_object();
			
			if($row->id <> $top_level)//in this site the top level parent ID is 1
			{
				$cur = count($this->parent_array);
				$this->parent_array[$cur] = $row->id;
				//if this is not a top level category then use recurssion to get the next level category
				$top_page_id .= $this->get_top_parent_id_list($row->parent, $top_level);
			}
		}
		
		return $this->parent_array;
	}//end get_top_parent_id
	
	
	
	//PRIVATE FUNCTION
	//generate the category menu for webpage navigation
	private function generate_sub_nav_menu($id, $parent_id="")
	{
		//echo "nav_id".$id;
		//echo "parent_id".$parent_id;
		
		if ($id)
		{
			// display sub-categories
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("parent = $id");
			$this->set_orderby("order_id ASC");
				
			$sub_result = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			$while_cnt = 0;
			if ($count > 0)
			{	
				//get a list of top level pages that do have subpages
				$this->set_select();
				$this->set_from("pages");
				$this->set_where("id = $id");
				$this->set_where("parent = 0");
				$this->set_orderby("order_id ASC");
				
				$par_result = $this->get_data();
				$par_count = $this->numrows;//returns the total number of rows generated
				$while_cnt = 0;
				if ($count > 0)
				{
					while ($par_row = $par_result->fetch_object())
					{
						//  html_entity_decode v4.3.0+ only 
						$db_link_name = html_entity_decode($par_row->link_name);
						$db_filename = html_entity_decode($par_row->filename); 
						$db_directory = $par_row->directory;
						$db_nav_id = $par_row->id;
						$db_parent_id = $par_row->parent;
						
						//remove directory prefix that makes files relative to the CMS dir.
						$db_directory = str_replace($this->html_dir, $this->site_dir, $db_directory);
						
						//create menu string
						if($while_cnt > 0){$menu_item .= "\n\t<li style=\"list-style: none\">|</li>";}
						$menu_item .= "\n\t<li><a href=\"".$db_directory.$db_filename."\">$db_link_name</a></li>";
						
						$while_cnt ++;
					}//end while
				}//end if
			
				//now get a list of pages that fit the original query
				while ($sub_row = $sub_result->fetch_object())
				{
					//  html_entity_decode v4.3.0+ only 
					$db_link_name = html_entity_decode($sub_row->link_name);
					$db_filename = html_entity_decode($sub_row->filename); 
					$db_directory = $sub_row->directory;
					$db_nav_id = $sub_row->id;
					$db_parent_id = $sub_row->parent;
					
					//remove directory prefix that makes files relative to the CMS dir.
					$db_directory = str_replace($this->html_dir, $this->site_dir, $db_directory);
					
					//create menu string
					$menu_item .= "\n\t\t<li style=\"list-style: none\">|</li>";
					$menu_item .= "\n\t\t<li><a href=\"".$db_directory.$db_filename."\">$db_link_name</a></li>";
					
					//re-call the function to look for items and subcategories within the current categroy
					$menu_item .= $this->generate_sub_nav_menu($db_nav_id, $db_parent_id);
					$while_cnt ++;			
				}//end while
			}//end if
		}//end if ($id)
	
		return $menu_item;
	}//end generate_sub_nav_menu
	
	

	private function build_content($output_string, $input_name=NULL)
	{
		/*********************************************
		********* DEAL WITH TEMPLATE TOKENS *********/
		//using the only  HTMLTemplateTAG:: tokens, only output a 
		//section content if content is present and has a tag i.e. TAG_TITLE_TAG the section name present in the database articles table
		//if the content is not present then remowe the html defined between HTMLTemplateTAG:: start and end tokens and the tokens themselves

		//decode html tags so that we are working with proper html not encoded html
		$output_string = html_entity_decode($output_string , ENT_QUOTES, 'UTF-8');
		//echo $output_string;
				
		preg_match_all ("/<!-- HTMLTemplateTAG::([^:]+?)::start -->(.*?)<!-- HTMLTemplateTAG::\\1::end -->/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) 
		{
			//SET CLASS VARS HERE FROM THE DATABASE USING CURRENT PAGE INFO
			//echo "<br />this->Page_id:".$this->Page_id;
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("id = '".$this->Page_id."'");
			$result_page = $this->get_data();
			$count_page = $this->numrows;	
			$row_page = $result_page->fetch_object();
			$this->Title = $row_page->title;
			$this->Keywords = $row_page->keywords;
			$this->Description = $row_page->description;
			$this->Menu_page = $row_page->menu_page;
			$this->footer_menu_page = $row_page->footer_menu_page;
			$this->search_page = $row_page->search_page;
			$this->track_page = $row_page->track_page;
			$this->protected_page = $row_page->protected_page;
			$this->Filename = $row_page->filename;  

			//CURRENT PAGE - PARENT PAGE INFO
			if ($row_page->parent > 0):
				$this->set_select();
				$this->set_from("pages");
				$this->set_where("id = ".$row_page->parent."");
				$result_parent = $this->get_data();
				$count_parent = $this->numrows; //returns the total number of rows generated
				if ($result_parent && $count_parent == 1):
					$row_parent = $result_parent->fetch_object();
					$this->row_parent = $row_parent;
					//$page_name = $row_parent->name;
					//echo $this->row_parent->name;
				endif;	
			endif;		
		
			//define HTML TOKEN
			$TOKEN = $matches[0][$i];
			//define TAG inside of HTML TOKEN
			$TAG = $matches[1][$i];
			//echo $TAG;
			switch($TAG)
			{	
				case "[TAG_TITLE_TAG]":
						$output_string = str_replace($TOKEN, $this->htmlsafe_input($this->Title), $output_string);
				break;
				case "[TAG_META_KEYWORDS_TAG]":
						$output_string = str_replace($TOKEN, $this->htmlsafe_input($this->Keywords), $output_string);
				break;
				case "[TAG_META_DESCRIPTION_TAG]":
						$output_string = str_replace($TOKEN, $this->htmlsafe_input($this->Description), $output_string);
				break;
				case "[TAG_HIDE_FROM_ROBOTS_TAG]":
					if ($this->Noindex_page == 1)
					{
						$content = "";
						$content .= '
							<meta name="robots" content="nofollow" />
							<meta name="robots" content="noindex" />
							<meta name="googlebot" content="index" />
						';
						$output_string = str_replace($TOKEN, $content, $output_string);
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/								
					}
					else
					{						
						$content = "";
						$content .= '
							<meta name="robots" content="all" />
						';
										
						//remove HTML from output string
						$output_string = str_replace($TOKEN, $content, $output_string);				
					
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/					
					}
				break;
				case "[TAG_BODY_ID_TAG]":
						$body_id = " id='".$this->make_url_safe($this->htmlsafe_input($this->Linkname))."'";
						$output_string = str_replace($TOKEN, $body_id, $output_string);
				break;
				case "[TAG_DATE_TAG]":
						$output_string = str_replace($TOKEN, date("Y"), $output_string);
				break;
				case "[TAG_PAGE_NAME_TAG]":
						$content = "";
						$content .= "<h1>".$this->htmlsafe_input($this->Linkname)."</h1>";
						$output_string = str_replace($TOKEN, $content, $output_string);
				break;
				case "[TAG_PAGE_HEADING_TAG]":
						list($count, $result) = $this->query_content($TAG, $template);
						//echo "<br />TAG:$TAG&nbsp;&nbsp;template:$template";
						
						$content = "";
						
						if ($count > 0 && $result)
						{
							while($row = $result->fetch_object())
							{
								//decode html tags so that we are working with proper html not encoded html
								//convert any spcial chars to html equilvelents i.e. " to $rsquot;
								$title = $this->htmlsafe_input($row->content);
								$content .= "<h2>".$title."</h2>";
							}
							
							//replace HTML token with db content
							$output_string = str_replace($TOKEN, $content, $output_string);
							
							/*********************************************
							******* DO NOT DISPLAY TOKEN TAGS ****/
							$output_string = $this->remove_tags($TAG, $output_string);	
							/*********************************************
							******* DO NOT DISPLAY TOKEN TAGS ****/								
						}
						else
						{											
							//remove HTML from output string
							$output_string = str_replace($TOKEN, "", $output_string);				
					
							/*********************************************
							******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
							$output_string = $this->build_head_foot($TAG, $output_string);	
							/*********************************************
							******* DO NOT DISPLAY TOKEN HEAD FOOT ****/					
						}						
				break;	

				case "[TAG_PAGE_ID_TAG]":
					$temp = $this->Page_id;
					$content = "";
					$content .= '$page_id = "'.$temp.'";';
					$output_string = str_replace($TOKEN, $content, $output_string);
				break;				

				case "[TAG_PAGE_INFO_TAG]":
					$content = "";
					$content .= '$page_name = "'.$row_page->link_name.'";';
					$content .= "\n";
					$content .= '$protected_page = "'.$row_page->protected_page.'";';
					$content .= "\n";
					if($row_parent->link_name != 'STATIC PAGES'){
						$content .= '$parent_page_name = "'.$row_parent->link_name.'";';
					}
					$content .= "\n";
					$content .= '$top_parent_id = '.$this->static_pages_id.';';					
					$content .= "\n";
					$content .= '$page_level = $c_cms->get_page_level($page_id, $top_parent_id);';
					$output_string = str_replace($TOKEN, $content, $output_string);
				break;		

				case "[TAG_TRACK_HEAD_TAG]":
					$content = "";
					//echo "<br />this->track_page:".$this->track_page;
					if ($this->track_page == 1)
						$content .= 'include_once("common/track-head.php");';
					$output_string = str_replace($TOKEN, $content, $output_string);
				break;			

				case "[TAG_TRACK_IFRAME_TAG]":
					$content = "";
					if ($this->track_page == 1)
						$content .= 'include_once("common/track-iframe.php");';
					$output_string = str_replace($TOKEN, $content, $output_string);
				break;																				
										
				case "[TAG_PRINT_ITEMS_TAG]":
						$content = "<p>&copy;$this->URL ".date('d/m/Y')."</p>";
						$content .= "<p>$this->URL/$this->Name</p>";
						$output_string = str_replace($TOKEN, $content, $output_string);
				break;
				//MAIN CSS NAV MENU
				case "[TAG_MAIN_NAV_TAG]":
					//SET HOMEPAGE LINK
					$this->parent_array = array();
					$this->get_top_parent_id_list($this->Page_id);	
					
					/*echo "<br /><hr />Page_id:".$this->Page_id."Name:".$this->Name;
					echo "<br />parent_array:";
					foreach ($this->parent_array as $key => $value):
						echo "<br />Key: $key; Value: $value<br />\n";
					endforeach;*/

					$content = "";	
					
					if (!empty($this->Page_id))
					{																						
						if (isset($this->set_top_level_page_id))
						{
							$content .= $this->generate_main_nav_menu($this->set_top_level_page_id);
						}
																				
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);		
						
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/						
					}
					else
					{						
						//remove HTML token from output string
						$output_string = str_replace($TOKEN, "", $output_string);				

						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
					}					
				break;	
				//STATIC PAGES MENU			
				case "[TAG_STATIC_PAGES_MENU_TAG]":
					$content = "";	
					
					if (!empty($this->Page_id))
					{																						
						//get a list of pages that the current we're creating page belongs to
						$this->get_top_parent_id_list($this->Page_id);		
						//echo "pa:".$this->parent_array[0];
								
						if (isset($this->set_top_level_page_id))
						{
							$content .= $this->generate_pages_nav_menu(2, "", 1, 0, 1, 0); //$this->set_top_level_page_id
						}														
						
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);		
						
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/							
					}
					else
					{
						//remove HTML token from output string
						$output_string = str_replace($TOKEN, "", $output_string);				

						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
					}				
				break;								
				//BREADCRUMB HTML LIST
				case "[TAG_BREADCRUMB_MENU_TAG]":	
					$content = "";	
					
					//next generate the breadcrumb menu (ul css list menu)
					$content .= $this->breadcrumb_menu($this->Page_id, "&nbsp;/&nbsp;", "", 10, 1);
					
					//replace HTML token with db content
					$output_string = str_replace($TOKEN, $content, $output_string);
					
					/*********************************************
					******* DO NOT DISPLAY TOKEN TAGS ****/
					$output_string = $this->remove_tags($TAG, $output_string);	
					/*********************************************
					******* DO NOT DISPLAY TOKEN TAGS ****/									
				break;
				case "[TAG_SITE_MAP_TAG]":				
					$content = "";
					
					//next generate the main menu as a sitemap (ul css list menu)
					$content .= $this->generate_sitemap_menu(0);	//menu_flag=1 is for the main menu
					
					if(!empty($content))
					{					
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);
						
							
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/		
					}
					else
					{							
						//remove HTML token from output string
						$output_string = str_replace($TOKEN, "", $output_string);				

						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
					}
				break;	
				//STRIP ALL TAGS FROM ARTICLES EXCEPT IMAGES/LINKS						
				case "[TAG_CONTENT_AREA_STRIP_TAG]":
				case "[TAG_MAIN_TITLE_TAG]":			
				case "[TAG_MAIN_SUBTITLE_TAG]":
				case "[TAG_FORM_TITLE_TAG]":
				case "[TAG_INTRO_IMAGE_TAG]":				
				case "[TAG_ERROR_COPY_TAG]":																					
					$this->set_select();
					$this->set_from("articles");
					$this->set_where("page_id = ".$this->Page_id);
					$this->set_where("section = '".trim($TAG)."'");
					$this->set_orderby("order_id");
					//echo $sql_check,"<br />";
					$result = $this->get_data();
					$count = $this->numrows;//returns the total number of rows generated
					$content = "";						
					
					if ($count > 0)
					{						
						while ($row = $result->fetch_object())
						{				
							//decode html tags so that we are working with proper html not encoded html
							//convert any spcial chars to html equilvelents i.e. " to $rsquot;
							$temp =  $this->htmlsafe_ckeditor($row->content);
							//remove all other html bar the image tag
							$temp = strip_tags($temp,"<img><a>");
							//$image = $this->strip_image($content);
						
							$content.= $temp;
						}				
					}
					//replace HTML token with db content
					$output_string = str_replace($TOKEN, $content, $output_string);
				break;
				
				//deal with alterting layout based on tag name
				case "[TAG_ITEM_1_TAG]":
				case "[TAG_ITEM_2_TAG]":
				case "[TAG_ITEM_3_TAG]":
					//get database content for given tag
					list($count, $result) = $this->query_content($TAG, $template);

					$content = "";
					
					if($count > 0 && $result){
						while($row = $result->fetch_object()){
							//decode html tags so that we are working with proper html not encoded html
							//convert any spcial chars to html equilvelents i.e. " to $rsquot;
							$header = null; $body = null; $bg_img = null; 
							
							$body = $this->htmlsafe_ckeditor($row->content);
							
							$header = $this->strip_tags_content($body, '<h2>', false);//return only the H2 and it's contents							
							$header = $this->get_tag_contents($header, "h2");//retrieve the contents of the H2 tag
							
							$bg_img = trim($this->strip_tags_content($body, '<img>', false));//return only the IMG and it's contents
								
							if(!empty($bg_img)){
								$bg_img = $this->strip_image($bg_img);
							
								$bg_img = 'style="background-image:url('.$bg_img.');"';
							}
							
							$details = $this->strip_tags_content($body, '<h2><img>', true);//return all tags and contents EXCEPT THE h2
							
							//get the filename for the associated tag
							$item_id = $this->filename_from_tag($TAG);
							
							
							//if no external URL is present then link to the pages filename
							if(!empty($header)):
								$content .= '<div class="item" id="'.$item_id.'" '.$bg_img.'>';
									$content .= '<div class="details">';
									if(!empty($header)):
										$content .= '<div class="header"><p>';
										$content .= $header;		
										$content .= '</p></div>';
									endif;
									
									if(!empty($details)):
										$content .= '<div class="content">';
										$content .= $details;
										$content .= '</div>';
									endif;	
									$content .= '</div>';
								$content .= '</div>';
							endif;	
						}
	
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);
						
							
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/	

					}else{
						
						//remove HTML from output string
						$output_string = str_replace($TOKEN, "", $output_string);				

						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/

					}
				break;
				
				case "[TAG_BANNERS_TAG]":
					//get database content for given tag
					list($count, $result) = $this->query_content($TAG, $template);

					$content = "";
					$cnt = 1;
					


					if($count > 0 && $result){
						while($row = $result->fetch_object()){
							//decode html tags so that we are working with proper html not encoded html
							//convert any spcial chars to html equilvelents i.e. " to $rsquot;
							$body = null; $bg_img = null; 
							
							$body = $this->htmlsafe_ckeditor($row->content);
							
							$bg_img = trim($this->strip_tags_content($body, '<img>', false));//return only the IMG and it's contents
								
							if(!empty($bg_img)){
								$bg_img = $this->strip_image($bg_img);
							
								$bg_img = 'style="background-image:url('.$bg_img.');"';
							}
							
							$details = $this->strip_tags_content($body, '<img>', true);//return all tags and contents EXCEPT THE h2
							
							//get the filename for the associated tag
							$item_id = $this->filename_from_tag($TAG);
							
							//create link id to next element id
							
							//if we aren't on the last item then increment id by one
							if($count != $cnt){
								$next_id = $cnt + 1;
							//otherwise link to the first item
							}else{
								$next_id = 1;
							}
							
							//if no external URL is present then link to the pages filename
							if(!empty($details)):
								$content .= '<div id="banner-panel-'.$cnt.'" class="col-2 {item: \''.$cnt.'\'}">';									
									if(!empty($details)):
										$content .= $details;
									endif;	
									$content .= '<p><a href="#banner-panel-'.$next_id.'" class="showhide"><img src="/img/buttons/btn-more.gif" alt="more" class="button" /></a></p>';
									$content .= '<div class="example" '.$bg_img.'></div>';
								$content .= '</div>';
							endif;	
							
							$cnt ++;
						}
	
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);
						
							
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/	

					}else{
						
						//remove HTML from output string
						$output_string = str_replace($TOKEN, "", $output_string);				

						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/

					}
				break;

				
				////////////////////// INHERIT TOP LEVEL CONTENT ///////////////////
				// NEXT DEAL WITH SECTIONS OF A TEMPLATE THAT SHOULD INHERIT MISSING 
				// CONTENT FROM THEIR TOP LEVEL PAGE. 
				//TO DO THIS LIST THE TEMPLATE TAGS BELOW
				////////////////////////////////////////////////////////////////////
				case "[TAG_CONTACT_DETAILS_TAG]":					
					//get database content for given tag
					list($count, $result) = $this->query_content($TAG, $template);

					$content = NULL;
					
					if ($count > 0 && $result)
					{			
						while ($row_replace = $result->fetch_object())
						{
							$content .= $this->htmlsafe_ckeditor($row_replace->content);
						}//end while				
					}
					else
					{
						//get the toplevel parent page ID that this page belongs to
						// set the top level page ID that you want to work with						
						
						if ($this->Parent_id >= $this->set_top_level_page_id)
						{
							$toplevel_page_id = $this->get_top_parent_id($this->Page_id, $this->set_top_level_page_id);
							
							$this->set_select();
							$this->set_from("articles");
							$this->set_where("page_id = $toplevel_page_id");
							$this->set_where("section = '$TAG'");			
							$result_replace = $this->get_data();
							$count_replace = $this->numrows;//returns the total number of rows generated
							
							if ($count_replace > 0)
							{						
								while ($row_replace = $result_replace->fetch_object())
								{
									$content .= $this->htmlsafe_ckeditor($row_replace->content);
								}//end while				
							}
						}						
					}
					
					if (!empty($content))
					{					
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);
						
							
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/	
					}
					else
					{							
						//remove HTML token from output string
						$output_string = str_replace($TOKEN, "", $output_string);				

						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
						$output_string = $this->build_head_foot($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
					}
				break;				
				
				/*****************************************************************************************/
				/*specific tag handling for the bespoke website remove when using object in other sites*/
				/*****************************************************************************************/
				default: // all other tags can be handled generically
					//echo "<br />$TAG: grum"; //TESTING

					//get database content for given tag
					list($count, $result) = $this->query_content($TAG, $template);

					$content = "";
					$embed_matches = null;
					
					if ($count > 0 && $result)
					{
						while($row = $result->fetch_object())
						{
							//decode html tags so that we are working with proper html not encoded html
							//convert any spcial chars to html equilvelents i.e. " to $rsquot;
							
							/* embed code */
							/**************************************************/
							//deal with any embedded flash movies again.
							$embed_matches = null;
							preg_match_all ("/<(embed|EMBED).*((src|SRC)\=(\".+?\")).*>(.*?)<\/(embed|EMBED)>/s", $this->htmlsafe_ckeditor($row->content), $embed_matches);
							
							if(!empty($embed_matches[4][0])){
								$embed_matches[4][0] = str_replace('"','',$embed_matches[4][0]);
								$unobtrusive = '<div class="flash-movie"><a class="{width:425, height:344}" href="/css/skins/default/flash/player.swf?file='.$embed_matches[4][0].'"></a></div>';
								$content .= str_replace($embed_matches[0][0],$unobtrusive,$this->htmlsafe_ckeditor($row->content));
								
							}else{
								$content .= $this->htmlsafe_ckeditor($row->content);
							}
							/**************************************************/					
						
							/*if ($TAG=="[TAG_CONTENT_AREA_TAG]"): //TESTING
								echo "<br />TAG_CONTENT_AREA_TAG: ROW:<br />".$row->content;
							endif;*/						
						
							//decode html tags so that we are working with proper html not encoded html
							//convert any spcial chars to html equilvelents i.e. " to $rsquot;
							//$content .= html_entity_decode($row->content, ENT_QUOTES);
						}
	
						//replace HTML token with db content
						$output_string = str_replace($TOKEN, $content, $output_string);
												
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/
						$output_string = $this->remove_tags($TAG, $output_string);	
						/*********************************************
						******* DO NOT DISPLAY TOKEN TAGS ****/	
					}
					else
					{	
						$content = "";
								
						//CONTENT PANELS							
						switch($TAG)
						{	
							//USE TOP LEVEL CONTENT PANELS PAGE TO GET CONTENT
							case "[TAG_FOOTER_MENU_TAG]":
							//case "[TAG_CONTENT_TAG]":	
								$this->set_select();
								$this->set_from("articles");
								$this->set_where("page_id = '1'");
								$this->set_where("section = '$TAG'");			
								$result_replace = $this->get_data();
								$count_replace = $this->numrows;//returns the total number of rows generated
								
								if ($count_replace > 0):					
									while ($row_replace = $result_replace->fetch_object()):
										$content .= $this->htmlsafe_ckeditor($row_replace->content);
									endwhile;				
								endif;	
								
								//replace HTML token with db content
								$output_string = str_replace($TOKEN, $content, $output_string);								
									
								/*********************************************
								******* DO NOT DISPLAY TOKEN TAGS ****/
								$output_string = $this->remove_tags($TAG, $output_string);	
								/*********************************************
								******* DO NOT DISPLAY TOKEN TAGS ****/																	
							break;	
							/*****************************************************************************************/
							/* CONTENT PANELS - pages generated directly as includes files */
							/*****************************************************************************************/													
							case "[TAG_PAGE_ACCESS_KEYS_TAG]":
							case "[TAG_PAGE_HEADER_TAG]":
							case "[TAG_PAGE_CORPORATE_FOOTER_LINKS_TAG]":
							case "[TAG_PAGE_FOOTER_NAVIGATION_TAG]":
							case "[TAG_PAGE_QUICK_CONTACT_TAG]":
							case "[TAG_PAGE_NAVIGATION_TAG]":
							case "[TAG_PAGE_SUB_NAVIGATION_TAG]":
							case "[TAG_PAGE_ALT_STYLES_TAG]":
							case "[TAG_PAGE_NEWS_LIST_TAG]":
							case "[TAG_PAGE_COMMENTS_LIST_TAG]":
								
								//get database content for given tag
								list($count, $result) = $this->query_content($TAG, $template);
								
								$content = "";
								//if content exists then show for the given page
								if ($count > 0 && $result)
								{
									while($row = $result->fetch_object())
									{
										$content .= $this->htmlsafe_ckeditor($row_replace->content);
									}
								//else show file as include
								}else{
									
									//get the filename for the associated tag
									$include_file = $this->filename_from_tag($TAG);
									//output as include file
									$content .= '<?php include_once("panels/'.$include_file.'.php"); ?>';
								}
								
								
							
								//replace HTML token with db content
								$output_string = str_replace($TOKEN, $content, $output_string);								
									
								/*********************************************
								******* DO NOT DISPLAY TOKEN TAGS ****/
								$output_string = $this->remove_tags($TAG, $output_string);	
								/*********************************************
								******* DO NOT DISPLAY TOKEN TAGS ****/									
							break;								
							default: 
								//remove HTML from output string
								$output_string = str_replace($TOKEN, "", $output_string);				
		
								/*********************************************
								******* DO NOT DISPLAY TOKEN HEAD FOOT ****/
								$output_string = $this->build_head_foot($TAG, $output_string);	
								/*********************************************
								******* DO NOT DISPLAY TOKEN HEAD FOOT ****/							
							break;
						}
					}				
			}//switch
		}//for
				
		//make /content/ folder relative to file system
		$output_string = $output_string = str_replace("/content/", $this->ROOT_URL."/content/", $output_string);
	
		return $output_string;
	
		/*********************************************
		******* END DEAL WITH TEMPLATE TOKENS *******/
	}//end build_content
	

	//use regular expression to get content of link href and body
	private function strip_links($output_string)
	{
		preg_match_all ("/<(a|A) (href|HREF)\=\"(.+?)\">(.*?)<\/(a|A)>/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) 
		{
			//return matched tags we want
			$TAG0 = $matches[0][$i]; //returns entire matched string
			$TAG1 = $matches[1][$i]; //matches (a|A)
			$TAG2 = $matches[2][$i]; //matches (href|HREF)
			$TAG3 = $matches[3][$i]; //matches (.+?)
			$TAG4 = $matches[4][$i]; //matches (.*?)
			$TAG5 = $matches[5][$i]; //matches (a|A)
			//echo "<br />".$TAG3;
			//echo "<br />".$TAG4;
		}
		
		return array($TAG3, $TAG4);
	}

	//use regular expression to get content of image src
	public function strip_image($output_string){
		preg_match_all ("/(^<(img|IMG)).*((src|SRC)\=(\".+?\")).*(\/>$)/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) {

			//return matched tages we want
			$TAG0= $matches[0][$i]; //matches entire string format to regex
			$TAG1= $matches[1][$i]; //matches string starts with < img|IMG
			$TAG2 = $matches[2][$i]; //matches (img|IMG)
			$TAG3= $matches[3][$i]; //matches (src="")
			$TAG4 = $matches[4][$i]; //matches (src|SRC)
			$TAG5 = $matches[5][$i]; //matches (src tag contents) returns attribute
			/*echo "<br />".$TAG0;
			echo "<br />".$TAG1;
			echo "<br />".$TAG2;
			echo "<br />".$TAG3;
			echo "<br />".$TAG4;
			echo "<br />".$TAG5;*/
			return  ereg_replace( "['\"\]", "", trim($TAG5));
		}
	//return array($TAG3, $TAG4);
	}
	
	//use regular expression to get content of a given block level HTML tag
	private function get_tag_contents($output_string, $tag_name){
	
		preg_match_all ("/<(".strtolower($tag_name)."|".strtoupper($tag_name).")>(.*?)<\/(".strtolower($tag_name)."|".strtoupper($tag_name).")>/s", $output_string, $matches);

		for ($i=0; $i< count($matches[0]); $i++) {

			//return matched tages we want
			$TAG0= $matches[0][$i]; //matches entire string format to regex
			$TAG1= $matches[1][$i]; //matches string starts with < img|IMG
			$TAG2 = $matches[2][$i]; //matches (img|IMG)
			$TAG3= $matches[3][$i]; //matches (src="")
			$TAG4 = $matches[4][$i]; //matches (src|SRC)
			$TAG5 = $matches[5][$i]; //matches (src tag contents) returns attribute
			/*echo "<br />0".$TAG0;
			echo "<br />1".$TAG1;
			echo "<br />2".$TAG2;
			echo "<br />3".$TAG3;
			echo "<br />4".$TAG4;
			echo "<br />5".$TAG5;*/
			return $TAG2;
		}
	//return array($TAG3, $TAG4);
	}

	//function to get content from database for a given TAG
	private function query_content($TAG, $template)
	{
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = $this->Page_id");
		//echo $sql_check,"<br />";
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
	
		$row = $result->fetch_object();
	
		$template = $row->template;
	
		$this->set_select();
		$this->set_from("articles");
		$this->set_where("page_id = $this->Page_id");
		$this->set_where("section = '$TAG'");
		$this->set_where("template = '$template'");
		$this->set_orderby("order_id");
		//echo $sql_check,"<br />";
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated

		return array($count, $result);
	}//end query_content



	private function build_head_foot($match_tag, $output_string)
	{
		/******* DISPLAY SECTION HEAD FOOT ***********/
		//using the only  HTMLTemplate:: tokens, only output a 
		//section header or footer if content is present and has a matching tag i.e. TAG_TITLE_TAG
		//if the content is not present then remowe the header / footer html defined between HTMLTemplate:: start and end tokens and the tokens themselves

		//take off brackets to conform with start and end token names
		$match_tag = substr(substr($match_tag, 1), 0, -1);

		preg_match_all ("/<!-- HTMLTemplate::([^:]+?)::start -->(.*?)<!-- HTMLTemplate::\\1::end -->/s", $output_string, $matches);
		//echo count($matches[0])."<br />";
		for ($i=0; $i< count($matches[0]); $i++) 
		{
			//define HTML TOKEN
			$TOKEN = $matches[0][$i];
			//define TAG inside of HTML TOKEN
			$TAG = $matches[1][$i];
			
			if ($TAG == $match_tag)
			{
				$output_string = str_replace($TOKEN, "", $output_string);
			}
		}
		//echo $newsfooter;

		return $output_string;
		/*********************************************
		******* DISPLAY SECTION HEAD FOOT ***********/
	}//end build_head_foot
	
	
	//PRIVATE FUNCTION
	//next generate the pages menu - for info/help sections
	/*
	METHODS ARGUMENTS
	************************
	$id						@page id int - default null to initiate count
	$parent_id				@page parent_id int - default null to initiate count
	$tabindex				@menu tabindex int - default 1 to initiate count
	$current_level			@menu current_level int - default 0 to initiate count
	$current_level_pos		@menu current_level_pos int - default 1 to initiate count
	$show_all				@menu current_level_pos bool - default 0 to show all set to appear in menu
	$show_sub_pages			@menu current_level_pos bool - default 0 to NOT show sub level pages
	optional 				@array - arguments passed in as array e.g. array('footer_menu_page' => '1')
	************************
	*/
	//---------- START function to generate pages menu ----------
	private function generate_pages_array($id, $parent_id="", $tabindex=1, $current_level=0, $current_level_pos=1, $show_all=0, $show_sub_pages = 0)
	{
		
		$args = func_get_args(); // return array of arguments	
		
				
		$id = $this->cleanstring_input(array_shift($args));
		$parent_id = $this->cleanstring_input(array_shift($args));
		$tabindex = $this->cleanstring_input(array_shift($args));
		$current_level = $this->cleanstring_input(array_shift($args));
		$current_level_pos = $this->cleanstring_input(array_shift($args));
		$show_all = $this->cleanstring_input(array_shift($args));
		$show_sub_pages = $this->cleanstring_input(array_shift($args));
		
		//move optional arg array into 2d array
		$args = $args[0];
		
		$arg_count = strlen($args);
		
		$array_optional_args = array();
		
		//any more args left and we are using anonymous args
		if($arg_count > 0){
		
			$i = 0;
				//look at each key value pair
				while(key($args)):
					//echo $this->cleanstring_input(key($args)) . " : " . $this->cleanstring_input(current($args));
					//passinto option arrgs array
					$array_optional_args[$i]['name'] = $this->cleanstring_input(key($args));
					$array_optional_args[$i]['value'] = $this->cleanstring_input(current($args));
					
					$i ++;
					next($args);
				endwhile;
		}
		
		//initialise page array
		if(empty($this->page_array)){
			$this->page_array = array();
		}
		
		$current_level++;		
		//echo "id:$id  parent_id:$parent_id<br/>";
	
		//*** info for this page ***
		//data for the current page id if needed	
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("id = $id");
		
		
		
		if ($show_all == 0)
		{
			$this->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
		}
		
		//iterate through each option 
		if(count($array_optional_args) > 0):
			foreach($array_optional_args as $optional_arg):
				//create query for each optional
				$this->set_where($optional_arg['name'] . " = " . $optional_arg['value']);
			endforeach;
		endif;
		
		//$this->set_where("id != 1");
		$this->set_orderby("order_id");
		$result_current = $this->get_data();
		$row_current = $result_current->fetch_assoc();

		//*** info on sub-pages for this page ***
		//grab subpage info/count
		//$this = new db_object();
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("parent = $id");
		if ($show_all == 0)
		{
			$this->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
		}
			
		//iterate through each option 
		if(count($array_optional_args) > 0):
			foreach($array_optional_args as $optional_arg):
				//create query for each optional
				$this->set_where($optional_arg['name'] . " = " . $optional_arg['value']);
			endforeach;
		endif;
		
		//$this->set_where("id != 1");
		$this->set_orderby("order_id");	
		$sub_result = $this->get_data();
		$sub_count = $this->numrows;//returns the total number of rows generated	
		
		$cnt = 1;
		
		while ($sub_row = $sub_result->fetch_assoc())
		{		
		
			$temp = "";
			$li_class = "";
			$a_class = "";
			
			//html_entity_decode v4.3.0+ only  
			$page_id = $sub_row["id"];
			$page_name = $sub_row["name"];
			$page_filename = $sub_row["filename"];
			$page_link_name =  ucfirst($this->htmlsafe_input($sub_row["link_name"]));
			$page_link_title =  $this->make_url_safe($sub_row["link_name"]);
			$page_no_index = $sub_row["no_index"];			
			$db_external_url = $sub_row["external_url"];
			$nav_id = $sub_row["id"];
			$parent_id = $sub_row["parent"];
			

			//*** get sub-page count for the PARENT of the current page ***
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("parent = $parent_id");
			if ($show_all == 0)
			{
				$this->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
			}			
		
			//iterate through each option 
			if(count($array_optional_args) > 0):
				foreach($array_optional_args as $optional_arg):
					//create query for each optional
					$this->set_where($optional_arg['name'] . " = " . $optional_arg['value']);
				endforeach;
			endif;
				
			//$this->set_where("id != 1");
			$this->set_orderby("order_id");	
			$thissub_result = $this->get_data();
			$thissub_count = $this->numrows;//returns the total number of rows generated	

			//*** get sub-page count for the page we are currently outputting ***
			$this->set_select();
			$this->set_from("pages");
			$this->set_where("parent = $nav_id");
			
			if ($show_all == 0)
			{
				$this->set_where("menu_page = 1"); //only pages to be included on the menu using flag called menu_page
			}				
		
			//iterate through each option 
			if(count($array_optional_args) > 0):
				foreach($array_optional_args as $optional_arg):
					//create query for each optional
					$this->set_where($optional_arg['name'] . " = " . $optional_arg['value']);
				endforeach;
			endif;		
			
			$this->set_orderby("order_id");	
			$nextsub_result = $this->get_data();
			$nextsub_count = $this->numrows;//returns the total number of rows generated			
			
			$page_rel = null;
			
			if ($page_no_index == 1)
			{
				$page_rel = ' rel="nofollow"';
			}
				
			
			$this->page_array['Pages'][] = array(
				'Page'=>array(
					'current_level' => $current_level,								
					'page_id' => $page_id,
					'parent_id' => $parent_id,
					'page_filename' => $page_filename,
					'page_link_name' => $page_link_name,
					'page_rel' => $page_rel,
					'url' => $page_name,
					'db_external_url' => $db_external_url,
					'page_name' => $page_name,
					'page_no_index' => $page_no_index,
					'nav_id' => $nav_id,
					'current_level' => $current_level,
					'current_level_pos' => $current_level_pos,
					'children' => $nextsub_count
				)
			);
			
			

			$tabindex++;
			$cnt++;
			
			//show sub level pages if set to 1
			if($show_sub_pages == 1){
				//re-call the function to look for items and subpages within the current page
				//$content .= $this->generate_pages_nav_menu($nav_id, $parent_id, $tabindex);
				$this->generate_pages_array($nav_id, $parent_id, $tabindex, $current_level, $current_level_pos, $show_all, $show_sub_pages, $args);		
			}
			$current_level_pos++;		
		}//end while			
		
		$current_level--;

	}//end generate_pages_nav_menu
	//---------- START function to generate pages menu ----------


	private function remove_tags($match_tag, $output_string)
	{

	/******* DISPLAY SECTION HEAD FOOT ***********/
	//using the only  HTMLTemplate:: tokens, only output a 
	//section header or footer if content is present and has a matching tag i.e. TAG_TITLE_TAG
	//if the content is not present then remowe the header / footer html defined between HTMLTemplate:: start and end tokens and the tokens themselves

		//take off brackets to conform with start and end token names
		$match_tag = substr(substr($match_tag, 1), 0, -1);

		preg_match_all ("/<!-- HTMLTemplate::([^:]+?)::start -->/s", $output_string, $matches);
		//echo count($matches[0])."<br />";
		for ($i=0; $i< count($matches[0]); $i++) {
			//define HTML TOKEN
			$TOKEN = $matches[0][$i];
			//define TAG inside of HTML TOKEN
			$TAG = $matches[1][$i];
			
			if($TAG == $match_tag){
				$output_string = str_replace($TOKEN, "", $output_string);
			}
		}
		
		preg_match_all ("/<!-- HTMLTemplate::([^:]+?)::end -->/s", $output_string, $matches);
		//echo count($matches[0])."<br />";
		for ($i=0; $i< count($matches[0]); $i++) {
			//define HTML TOKEN
			$TOKEN = $matches[0][$i];
			//define TAG inside of HTML TOKEN
			$TAG = $matches[1][$i];
			
			if($TAG == $match_tag){
				$output_string = str_replace($TOKEN, "", $output_string);
			}
		}
		//echo $newsfooter;

	return $output_string;
	/*********************************************
	******* DISPLAY SECTION HEAD FOOT ***********/
	}

	// PRIVATE function
	private function create_page_html($tmpl_dir, $html_dir, $stream=0)
	{		
		//perform quick variable validation loops
		if ($this->Template == "")
		{
			$ConfMsg = "<LI>No template name was provided";
			return $ConfMsg;
		}
		else if ($this->Filename == "")
		{
			$ConfMsg = "<LI>No html filename was provided";
			return $ConfMsg;
		}
		else
		{
			#get the correct root directory from the scripts physical location
			$rootdir = substr_replace($_SERVER["SCRIPT_FILENAME"], '', strrpos($_SERVER["SCRIPT_FILENAME"], "/"));
			//echo $rootdir;
			$template_dir = $rootdir."/".$tmpl_dir;
			$template_name = $this->Template;
			$template_path = $template_dir.$template_name;
			//echo "<br />template_path: ".$template_path;
			
			//open the template file and place a handle on it
			$template_handle = @fopen($template_path,"r");
			if (!$template_handle) 
			{ 		
				$ConfMsg = "<LI><B>$template_path</B> not found on server";	
			}
			else
			{										
				/*********************************************
				********* OPEN TEMPLATE AND PASS CONTENTS TO A BUFFER *******/
				
				while(!feof($template_handle))
				{
					$buffer = fgets($template_handle,4096);
					$text .= $buffer;
				}
				
				/*********************************************
				************* END UPDATE METATAGS  **********/
				
				//initialise vars used to pass in template HTML
				$input_string = $text;
				$this->output_string = "";
	
				//decode html tags so that we are working with proper html not encoded html
				$this->output_string = html_entity_decode($input_string, ENT_QUOTES, 'UTF-8');
	
				/*********************************************
				********* DEAL WITH TEMPLATE TOKENS *********/
	
				//build content based on HTML embedded tokens and remove tokens from HTML
				$this->output_string = $this->build_content($this->output_string);
				
				
				/******* END DEAL WITH TEMPLATE TOKENS *******/
				/*********************************************

				
			    /*********************************************
				************* CREATE HTML FILE **************/
			   // place the new file in the correct directory using fopen to create if not existing
			   //create reference to new html path but strip out any local PHP folder notation i.e. (./ or ../)
			   //echo $html_dir;
			   $file_folder = $html_dir; //substr($html_dir, strpos($html_dir, "/"),strlen($html_dir));
			   $file_dir = $file_folder;
			   
			   $this_page_ext = substr($this->Template, -3, 3); // last x chars
	
				if ($this_page_ext=="php")
				{
					$this->tmpl_extension = "php";
				}
				else
				{
					$this->tmpl_extension = "html";
				}
			   
			   //$file_name = $this->Filename;
			   $file_name =  $this->Name.".".$this->tmpl_extension;
			   $file_path = $file_dir.$file_name;
			  
			   if ($stream==0)
			   {
				   if ($this->output_string) 
				   {
					   //create directory for html file making sure to loop through dir passed in i.e. /new/sub/sub2/
					   //call makedirectory function create all required directorys
					   $dir_create = $this->make_directory($html_dir);
	
					   if ($dir_create == 1)
					   {
					   	$ConfMsg = "<li>Folder <B>$filefolder</B> could not be created</li>";
					   }
					   
					   $BuildStatic = fopen($file_path, "w+");
					   fputs($BuildStatic,$this->output_string);
					   fclose($BuildStatic);
					   $ConfMsg = "<li><a href=\"". $html_dir . $file_name . "\" target=\"_blank\">$file_name</a> created.</li>";
				   }
				   else
				   {
						$ConfMsg = "Could not create HTML";
				   }
				   unset($text);
				   unset($this->output_string);
				   unset($BuildStatic);				   
			   }
			   else
			   {
				   if ($this->output_string) 
				   {
				   		$ConfMsg = $this->output_string;
				   }
				   else
				   {
				   		$ConfMsg = "Could not create HTML";
				   }
			   }
			   
				/*********************************************
				************ END CREATE HTML FILE ***********/
								   
			}//end check that template file can be found 
		
		}//end if check for html file name and template name
		return $ConfMsg;
		//end function
	}//end create_page_html
	
	

	// PRIVATE function
	// create new directory on file system
	private function make_directory($html_dir)
	{
		$tok  = strtok($html_dir,"/");
		
		while ($tok) 
		{
			$path .= $tok."/";			
			if(is_dir($path) == false)
			{ 
				if(!mkdir($path , 0777))
				{
					return 0;
				}
			}
			$tok = strtok("/");
		}
	}//end make_directory
	
	
	
	// PRIVATE function
	//delete a file from the file system
	private function delete_page_html($file)
	{
		//debug echo $file;
		if($file <> "../"){
			if(file_exists($file)) 
			{
			//debug echo "file to delete".$file;
				if(!(unlink($file))){
					return 1;
				}else{
					return 0;
				}
			}else{
				return 0;
			}		
		}
	}//end delete_page_html
	
	
	
	//PUBLIC function
	//re-publish all webpages
	public function update_all_html($update_common_elements = null)
	{
		$this->set_select();
		$this->set_from("pages");
		
		//ONLY UPDATE PAGES THAT BELONG TO THE CONTENT PANELS SECTION
		if(!empty($update_common_elements) && $update_common_elements == "COMMON_ELEMENTS"){
			$this->set_where("parent = '1'");
		}
		
		//echo $sql_check,"<br />";
		$result_update = $this->get_data();
		$count_update = $this->numrows;//returns the total number of rows generated

		if ($result_update && ($count_update != 0))
		{
			$msg_output = "<h4>Updating all pages... Please wait this may take a few minutes.</h4><br />";
			
			/**/
			if ($this->ftp_on == 1)
			{
				//chmod the ftp folder to 0777
				$chmod_write = $this->chmod_write();
				if ($chmod_write <> "" )
				{
					echo $chmod_write;
					exit;
				}
			}
			
			while ($row_update = $result_update->fetch_assoc())
			{			
				//  html_entity_decode v4.3.0+ only  
				$this->Page_id = $this->htmlsafe_input($row_update["id"]);
				$this->Name = $this->htmlsafe_input($row_update["name"]);
				$this->Template = $this->htmlsafe_input($row_update["template"]);
				//make sure to use the correct file type when publishing pages
				$this->Filename = $this->htmlsafe_input($row_update["filename"]);
				$this->Linkname = $this->htmlsafe_input($row_update["link_name"]);
				$this->Content = $this->htmlsafe_ckeditor($row_update["content"]);
				$this->Title = $row_update["title"]; 
				$this->Keywords = $row_update["keywords"]; 
				$this->Description = $row_update["description"]; 
				$this->Parent_id = $this->htmlsafe_input($row_update["parent"]);
				$this->Directory = $this->htmlsafe_input($row_update["directory"]);
				$this->Type_id = $this->htmlsafe_input($row_update["type_id"]);
				$this->Animate = $this->htmlsafe_input($row_update["animate"]); 
				$this->Noindex_page = $this->htmlsafe_input($row_update["no_index"]);					   
										
				$html_path = $this->Directory . $this->Filename;
					
				//debug echo $html_path;
				if (file_exists($html_path)) 
				{
					//call the delete file method to remove all old html files
					$deleted = $this->delete_page_html($html_path);	
				}
								
				if ($deleted <> 0 )
				{
					return $msg_output = "Error: $this->Filename could not be deleted.";						
				}
				
				//if ($this->Page_id == '1'):
				//	$msg_output .= "<li><a href=\"#\">".$this->Filename."</a> NOT created.</li>";
				//else:
					//$msg_output .= createHTML($tmpl_dir, $tmpl_name, $html_dir, $html_name, $body, $title, $keywords, $description);
					$msg_output .= $this->create_page_html($this->tmpl_dir, $this->Directory);
					//echo $msg_output;
				//endif;				
			}//end while
					
			/**/	
			if($this->ftp_on == 1)
			{
				//chmod the ftop folder to 0755
				$chmod_read = $this->chmod_read();
				if($chmod_read <> "" )
				{
					echo $chmod_read;
					exit;
				}
			}			
						
			$msg_output .= "<br /><div class='success'><h4>All pages have been updated.</h4></div>";
		}
		else
		{
			$msg_output = "<div class='error'><h4>No pages were found to update.</h4></div>";
		}//end result if

		return $msg_output;	
	}//end update_all_html
	
	
	
	//************************************************************************************
	//******************************** LIST FUNCTIONS ************************************		
	public function cms_select_template($dir, $existing_template="")
	{
		//file types to ignore
		$disallowed = array('.', '..', '.svn');
		
		if ($handle = opendir($dir)) 
		{			
			$template_list = array();
			$template_list_cnt = 0;
			/* This is the correct way to loop over the directory. */
			$row_cnt = 0;
			while (false !== ($file = readdir($handle))) 
			{ 		
				var_dump($file);
				if (!empty($file) && !in_array($file, $disallowed))
				{			
					//break up the templates name so that its a bit more user firendly and just use the name between the tokenising dots
					$tok = strtok($file,"..");
					while ($tok) 
					{
						switch($tok)
						{
							case "tmpl":
							case "htm":
							case "html":
							case "php":
							break;
							default:
								//populate array with template file names
								$template_list[$template_list_cnt]["file"] = $file;
								
								//rename template name that is displayed in drop list. 
								//This is only done to avoid renaming the template file 
								//itself and having to re-enter content for the given page
								switch($tok)
								{
									default:
										$template_list[$template_list_cnt]["tok"] = str_replace(array("-","_"), ' ', $tok);
								}
								$template_list_cnt ++;
								/*
								if ($file==$existing_template){
								$output = "<option value=\"$file\" selected>$tok</option>\n";
								}else{
								$output = "<option value=\"$file\">$tok</option>\n";
								}
								*/
						}
						$tok = strtok("..");
					}
					//echo $output;
					$row_cnt ++;				
				}		
			}
			//close handle on file
			closedir($handle); 
	
			//sort the array into alphabetical order	
			sort($template_list);
				
			for ($i = 0; $i < count($template_list); $i++)
			{
				//exclude user templates if page name doesnt contain 'user'
				$pos_page_user = strpos(strtolower($this->Name), 'user');
				if ($pos_page_user === false):
					$pos_page_user = 0;
				else:
					$pos_page_user = 1;
				endif;
										
				$pos_file_user = strpos(strtolower($template_list[$i]["file"]), 'user');
				if ($pos_file_user === false):
					$pos_file_user = 0;
				else:
					$pos_file_user = 1;
				endif;
			
				//exclude user templates if page name doesnt contain 'directory'
				$pos_page_directory = strpos(strtolower($this->Name), 'directory');
				if ($pos_page_directory === false):
					$pos_page_directory = 0;
				else:
					$pos_page_directory = 1;
				endif;
										
				$pos_file_directory = strpos(strtolower($template_list[$i]["file"]), 'directory');
				if ($pos_file_directory === false):
					$pos_file_directory = 0;
				else:
					$pos_file_directory = 1;
				endif;			
			
				if ($pos_page_user == 0 && $pos_file_user == 1):
				elseif ($pos_page_directory == 0 && $pos_file_directory == 1):
				else:			
					if ($template_list[$i]["file"] == $existing_template):
						$output .= "<option value=\"".$template_list[$i]["file"]."\" selected>".$template_list[$i]["tok"]."</option>\n";
					else:
						$output .= "<option value=\"".$template_list[$i]["file"]."\">".$template_list[$i]["tok"]."</option>\n";
					endif;
				endif;	
			}
			//$output .= "<option value=\"\">this->Name:".$this->Name."</option>"; //TESTING
			echo $output;
		}
	}//cms_select_template
	//******************************** LIST FUNCTIONS ************************************		
	//************************************************************************************	
	
	
	//************************************************************************************
	//********************************** CMS FUNCTIONS ***********************************		

	public function cms_menu_ul($id, $parent_id="")
	{
		//display sub-categories
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("parent = $id");
		$this->set_orderby("order_id ASC");
		
		$sub_result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated
		
		if ($count > 0)
		{
			//uncomment this entire if structure to use without javascript tree menu and if statment
			//replace with $menu_item .= "\n\t<ul>\n";
			if ($id == 0)
			{
				$menu_item .= "\n\t<ul id=\"navigation\">\n";
			}
			else
			{
				$menu_item .= "\n\t<ul>\n";
			}
		}
	
		while ($sub_row = $sub_result->fetch_assoc())
		{
			//  html_entity_decode v4.3.0+ only  
			$nav_name = $sub_row["name"];
			$link_name = $this->htmlsafe_input($sub_row["link_name"]);
			$nav_id = $sub_row["id"];
			$parent_id = $sub_row["parent"];
	
			//add current item to <li> tag
			$menu_item.= "\n\t<li>";
			$menu_item.= "<a href=\"web-cms.php?page_id=$nav_id&mode=edit\" target=\"mainFrame\">$link_name</a>";
	
			//re-call the function to look for items and subcategories within the current categroy
			$menu_item .= $this->cms_menu_ul($nav_id, $parent_id, 0);
	
			$menu_item .= "</li>\n";
		}
		if ($count > 0)
		{
			//$menu_item .= "\n\t<li></li>";
			$menu_item .= "\n\t</ul>\n";
		}
		
		return $menu_item;
	}//cms_menu_ul

	
	//generates article listing on edit page
	public function show_cms_form($token, $page_id, $template, $type="TEXT")
	{ 
	
		//set the page that is to be used to process the 
		// article content based on the $type argument
		switch($type)
		{
			case "TEXT":
				$file = "web-cms-update.php";
			break;
			case "POLL":
				$file = "web-cms-poll.php";
			break;
		}
	
		$section = $token;
		
		$this->set_select("SELECT *, date_format(timestamp, '%d/%m/%Y %H:%i:%s') as format_date, date_format(temp_timestamp, '%d/%m/%Y %H:%i:%s') as format_temp_timestamp");
		$this->set_from("articles");	
		$this->set_where("page_id = $page_id");
		$this->set_where("section = '$section'");
		$this->set_where("template = '$template'");
		$this->set_orderby("order_id");
	
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
	
		if ($result && ($count > 0))
		{
			$running_count=1;
			while ($row = $result->fetch_object())
			{
				//assign variables
				$article_id = $row->article_id;		
					
				switch($type)
				{
					case "TEXT":
						if ($running_count>1)
							echo "<hr class=\"hr\" />";
?>
						<div class="div_article_normal">
<?php					
						if ($row->content=="")
							echo "ARTICLE IS NEW.<br />NO PREVIOUS CONTENT.";
						else
							echo $this->htmlsafe_ckeditor($row->content);
?>
						</div>
<?php
						//IS THERE A CHANGE WAITING FOR AUTH?
						if ($row->temp_content != "")
						{
							//echo "<br />";
							echo "<span class=\"auth_title_red\">THERE IS A CHANGE WAITING FOR AUTHORISATION:</span><br />";						
?>
							<div class="div_article_auth">
<?php					
							echo $this->htmlsafe_ckeditor($row->temp_content);
?>
							</div>
<?php						
							$db_fullname = $this->c_generic->get_namefromid("usersadmin","user_id",$row->temp_admin_user_id,"fullname",0);
							$db_deleted_message= $this->c_usersadmin->deleted_message($row->temp_admin_user_id);
							echo "<span class=\"auth_title_red\">Change by user: '".$db_fullname."'$db_deleted_message. Time of change - ".$row->format_temp_timestamp."</span><br />";
							
							//AUTHORISE LINK
							//******************** SECURITY LEVEL TEST ************************
							//*** ONLY LEVEL (X) OR BETTER ALLOWED TO ACCESS THIS PAGE/CODE ***
							
							//admin users session security level
							$set_level = $_SESSION ['s_admin_level'];
							$required_level = 2;
							$success_flag=0;
							$this->c_usersadmin->test_admin_level($set_level, $required_level, $success_flag, 0);
							if ($success_flag == 1)
							{
?>
								<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=auth&section=$section&template=$template"; ?>');">[authorise this change]</a>&nbsp;&nbsp;
<?php
							}
							else
							{
?>
								<span class="hidelink">[You do not have a high-enough security level to authorise this change.]</span>
<?php						
							}
							//******************** SECURITY LEVEL TEST ************************	
								
							//echo "<br />";					
						}//end if
					break;
					case "POLL":
						if ($running_count>1)
							echo "<hr class=\"hr\" />";				
?>
						<div class="div_article_normal">
<?php
							
							$this->set_select();
							$this->set_from("poll_details");
							$this->set_where("poll_id = $row->poll_id");
						
							$result_poll = $this->get_data();
							
							$row_poll = $result_poll->fetch_object();
					
							//echo "<br /><br />";
							echo "<strong>Poll Name:</strong><br />";
							//echo html_entity_decode($row_poll->name, ENT_QUOTES);
							echo $this->htmlsafe_input($row_poll->name);
							echo "<br /><br />";
							echo "<strong>Poll Question:</strong><br />";
							//echo html_entity_decode($row_poll->question, ENT_QUOTES);				
							echo $this->htmlsafe_input($row_poll->question);
?>
						</div>
<?php					
					break;
				}//end switch
?>
				
				<div style="clear:both;float:left;">
					[<?php echo $row->order_id; ?>].&nbsp;
				</div>
				<div style="float:left;">
<?php
				//******************** SECURITY LEVEL TEST ************************
				//*** ONLY LEVEL (X) OR BETTER ALLOWED TO ACCESS THIS PAGE/CODE ***
				
				//admin users session security level
				$set_level = $_SESSION ['s_admin_level'];
				$required_level = 2;
				$success_flag=0;
				$this->c_usersadmin->test_admin_level($set_level, $required_level, $success_flag, 0);
				if ($success_flag == 0)
				{
					//IF CHANGE WAITING FOR AUTH - AND CURRENT USER DIDNT MAKE THE CHANGE - LOCK IT OUT
					//remove the links to update here and also have blocked the update in the actual function to be sure
					//TEST - if content waiting for auth and not done by current logged in user then abort the links
					if ( $row->temp_content != "" && $row->temp_admin_user_id != $_SESSION ['s_admin_id'])
					{	
?>
						<!--<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=add&section=$section&template=$template"; ?>');">[add]</a>&nbsp;&nbsp;-->
<?php
					}
					else
					{			
?>
						<!--<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=add&section=$section&template=$template"; ?>');">[add]</a>&nbsp;&nbsp;-->
						<span class="cms-actions">
							<span class="bubbleInfo">
								<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=edit&section=$section&template=$template"; ?>');">[update]</a> <em class="trigger">What's This?</em>&nbsp;&nbsp;
								<div class="popup" title="pop-right-cms">
									<h3>Update Article</h3>
									<p>
										Use this link to <em>update</em> this article for the given section of the page
									</p>				
								</div>
							</span>
						</span>
						<!--<span class="hidelink">[delete]&nbsp;&nbsp;</span>-->
<?php
					}//end if ($row->temp_content == "")
				}
				else
				{
					if ( $row->temp_content != "" && $row->temp_admin_user_id != $_SESSION ['s_admin_id'])
					{
?>					
						<!--<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=add&section=$section&template=$template"; ?>');">[add]</a>&nbsp;&nbsp;-->
						<span class="cms-actions">
							<span class="bubbleInfo">
								<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=edit&section=$section&template=$template"; ?>');">[update]</a>  <em class="trigger">What's This?</em>&nbsp;&nbsp;				
								<div class="popup" title="pop-right-cms">
									<h3>Update Article</h3>
									<p>
										Use this link to <em>update</em> this article for the given section of the page
									</p>				
								</div>
							</span>
						</span>
						<span class="cms-actions">
							<span class="bubbleInfo">
							<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=del&section=$section&template=$template"; ?>');">[delete]</a>  <em class="trigger">What's This?</em>&nbsp;&nbsp;						
								<div class="popup" title="pop-right-cms">
									<h3>Delete Article</h3>
									<p>
										Use this link to <em>remove</em> this article for the given section of the page
									</p>				
								</div>
							</span>
						</span>
<?php
					}
					else
					{				
?>	
						<!--<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=add&section=$section&template=$template"; ?>');">[add]</a>&nbsp;&nbsp;-->
						<span class="cms-actions">
							<span class="bubbleInfo">
								<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=edit&section=$section&template=$template"; ?>');">[update]</a>  <em class="trigger">What's This?</em>&nbsp;&nbsp;					
								<div class="popup" title="pop-right-cms">
									<h3>Update Article</h3>
									<p>
										Use this link to <em>update</em> this article for the given section of the page
									</p>				
								</div>
							</span>
						</span>
						<span class="cms-actions">
							<span class="bubbleInfo">
								<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&article_id=$article_id&mode=del&section=$section&template=$template"; ?>');">[delete]</a>  <em class="trigger">What's This?</em>&nbsp;&nbsp;						
								<div class="popup" title="pop-right-cms">
									<h3>Delete Article</h3>
									<p>
										Use this link to <em>remove</em> this article for the given section of the page
									</p>				
								</div>
							</span>
						</span>
<?php
					}//end if ($row->temp_content == "")
				}
				//******************** SECURITY LEVEL TEST ************************		
	
					//LAST UPDATED AND WHO BY
					$db_admin_user_name = $this->c_generic->get_namefromid("usersadmin","user_id",$row->admin_user_id,"fullname",0);
					$db_deleted_message= $this->c_usersadmin->deleted_message($row->admin_user_id);
?>
					last updated - 
<?php
					if ($db_admin_user_name!="")
					{
						echo "($db_admin_user_name$db_deleted_message) - ";
					}
?> 
					<?php echo $row->format_date; ?>
				</div>			
				<div class="clearfix"></div>
<?php		
				if ($running_count==$count)
					echo "<hr class=\"hr\" />";
				
				$running_count++;
			}//end while	
?>
			<div class="cms-actions">
				<span class="bubbleInfo">
					<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&mode=add&section=$section&template=$template"; ?>');">[add a new article]</a>  <em class="trigger">What's This?</em>&nbsp;&nbsp;
					<div class="popup" title="pop-right-cms">
						<h3>New Article</h3>
						<p>
							Use this link to <em>load</em> the editing window and <em>create</em> an article for the given section of the page
						</p>				
					</div>	
				</span>
			</div>
			<hr class="hr" />
<?php	
		}
		else
		{	
			//******************** SECURITY LEVEL TEST ************************
			//*** ONLY LEVEL (X) OR BETTER ALLOWED TO ACCESS THIS PAGE/CODE ***
			
			//admin users session security level
			$set_level = $_SESSION ['s_admin_level'];
			$required_level = 2;
			$success_flag=0;
			$this->c_usersadmin->test_admin_level($set_level, $required_level, $success_flag, 0);
			if ($success_flag == 0)
			{
				//<span class="hidelink">[add a new article]<small> - invisible</small></span>
?>
				<div class="cms-actions">
					<div class="bubbleInfo">
						<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&mode=add&section=$section&template=$template"; ?>');">[add a new article]</a><small> - invisible  </small> <em class="trigger">What's This?</em>
						<div class="popup" title="pop-right-cms">
							<h3>New Article</h3>
							<p>
								Use this link to <em>load</em> the editing window and <em>create</em> an article for the given section of the page
							</p>				
						</div>				
					</div>
				</div><?php
			}
			else
			{
?>
				<div class="cms-actions">
					<div class="bubbleInfo">
						<a href="javascript:poptastic('<?php echo $file; ?>?<?php echo "page_id=$page_id&mode=add&section=$section&template=$template"; ?>');">[add a new article]</a><small> - invisible </small>  <em class="trigger">What's This?</em>
						<div class="popup" title="pop-right-cms">
							<h3>New Article</h3>
							<p>
								Use this link to <em>load</em> the editing window and <em>create</em> an article for the given section of the page
							</p>			
						</div>
					</div>
				</div>
<?php 		
			}
			//******************** SECURITY LEVEL TEST ************************		
	
		}//end if ($result &&
	}//end show_cms_form


	//generate the category menu for the product admin pages of the CMS
	public function cms_select_page($id, $tree="", $selected="")
	{
		//set default $id
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("parent = $id");
		$this->set_orderby("order_id ASC"); //id ASC
		 
		//echo $sql_check,"<br />";
		$nav_result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
	
		if ($nav_result && ($count != 0))
		{
		   $cnt = 0;
		   while ($nav_row = $nav_result->fetch_assoc())
		   {
			   //  html_entity_decode v4.3.0+ only  
			   $nav_name = $this->htmlsafe_input($nav_row["link_name"]);
			   $nav_id = $nav_row["id"];
			   $parent_id = $nav_row["parent"];
			   
			   //echo $parent_id;
			   //echo $nav_id;
			   //echo $id;
			   
				if ($parent_id == 0)
				{
					$tree = "";
				}
				else
				{
					if (($parent_id == $id)&&($cnt > 0))
					{
					}
					else
					{
						 $tree .= "$nbsp ->";
					}
				}
					   
			   $selected_text = "";
			   if ($nav_id == $selected)
			   {
					$selected_text = "selected";
			   }
				  
			   if ($parent_id == 0)
			   {
				   $menu_item = "<option value=\"\">:::::::::::::::::::::::::::::::::::::::::::</option>";
				   $menu_item .= "<option value=\"$nav_id\" $selected_text>$tree$nav_name</option>";
			   }
			   else
			   {
					$menu_item = "<option value=\"$nav_id\" $selected_text>$tree$nav_name</option>";
			   }		   
			   
			   echo $menu_item;
			   
			   $this->cms_select_page($nav_id, $tree, $selected);
			   $cnt ++;
			}
		}
	}//cms_select_page

	

	
	// public function	
	//*** retrieve a list of all files ***
	public function select_page_list($order_by="", $assoc=0, $type="")
	{
		
		//fiond id for static page
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("name = 'static-pages'");
		//echo $sql_check,"<br />";
		$result_parent = $this->get_data();
		$count_parent = $this->numrows;//returns the total number of rows generated
		if ($result_parent && ($count_parent != 0))
		{
			$row_parent = $result_parent->fetch_object();
			$static_pages_id = $row_parent->id;
		}
		
		//fiond id for content page
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("name = 'content-panels'");
		//echo $sql_check,"<br />";
		$result_parent = $this->get_data();
		$count_parent = $this->numrows;//returns the total number of rows generated
		if ($result_parent && ($count_parent != 0))
		{
			$row_parent = $result_parent->fetch_object();
			$content_panel_id = $row_parent->id;
		}
	
		$this->set_select();
		$this->set_from("pages");
		$this->set_where("`parent` != $content_panel_id");
		$this->set_where("`id` != $content_panel_id");
		$this->set_where("`id` != $static_pages_id");
		
		if ($assoc == 1):
		
		endif;	
			
		//override order by using parameter list
		if ($order_by != "")
			$this->set_orderby($order_by);
		
		//get a list of total records for the query
		$this->get_record_count();
		
		//calculate the total number of pages the query will generate
		if (isset($this->set_list_rows))
		{
			$this->total_pages = ceil( $this->numrows / $this->set_list_rows );
		}
		
		//add in paging limits
		$this->set_page_num($this->set_list_page);
		$this->set_rows_per_page($this->set_list_rows);		
		
		//run query
		$result_check = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated
	
		if ($count == 0)
		{
			$err_msg = 'No Pages could be found';
			return array(1,$err_msg); //on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	
	}//select_file_list
	//*** retrieve a list of all files ***
	
	
	// public function	
	//*** retrieve a list of all files ***
	private function assoc_page_list_query($page_id = null)
	{
		//SELECT * FROM st_files f, st_assoc_file_page a WHERE a.file_id = f.id AND a.page_id=26 ORDER BY name
		$page_id = $this->cleanstring_input($page_id);
		if(!empty($page_id)){
			$select = "";
			$from = "pages p, ".$this->DB_PREFIX."assoc_page_pages a";
			$where = "a.related_page_id = p.id AND a.page_id = '".$page_id."'";
			$order_by = "name";
			
			$this->set_select($select);
			$this->set_from($from);
			$this->set_where($where);
			$this->set_orderby($order_by);	
				
			//override order by using parameter list
			if ($order_by != "")
				$this->set_orderby($order_by);
			
			//get a list of total records for the query
			$this->get_record_count();
			
			//calculate the total number of pages the query will generate
			if (isset($this->set_list_rows))
			{
				$this->total_pages = ceil( $this->numrows / $this->set_list_rows );
			}
			
			//add in paging limits
			$this->set_page_num($this->set_list_page);
			$this->set_rows_per_page($this->set_list_rows);		
			
			//run query
			$result_check = $this->get_data();
			$count = $this->numrows; //returns the total number of rows generated
		
			if ($count == 0)
			{
				$err_msg = 'No Files could be found';
				return array(1,$err_msg); //on fail return error code 1 and error message
			}
			else
			{
				while ($sub_row = $result_check->fetch_assoc())
				{		
				
					$temp = "";
					$li_class = "";
					$a_class = "";
					
					//html_entity_decode v4.3.0+ only  
					$page_id = $sub_row["id"];
					$page_name = $sub_row["name"];
					$page_filename = $sub_row["filename"];
					$page_link_name =  ucfirst($this->htmlsafe_input($sub_row["link_name"]));
					$page_link_title =  $this->make_url_safe($sub_row["link_name"]);
					$page_no_index = $sub_row["no_index"];			
					$db_external_url = $sub_row["external_url"];
					$nav_id = $sub_row["id"];
					$parent_id = $sub_row["parent"];
				
					$this->page_array['Pages'][] = array(
						'Page'=>array(
							'current_level' => $current_level,								
							'page_id' => $page_id,
							'parent_id' => $parent_id,
							'page_filename' => $page_filename,
							'page_link_name' => $page_link_name,
							'page_rel' => $page_rel,
							'url' => $page_name,
							'db_external_url' => $db_external_url,
							'page_name' => $page_name,
							'page_no_index' => $page_no_index,
							'nav_id' => $nav_id,
							'current_level' => $current_level,
							'current_level_pos' => $current_level_pos,
							'children' => $nextsub_count
						)
					);
				}
				
				return array($this->page_array); //on success return error code 0 and array of results				
			}
		}
	
	}//select_file_list
	//*** retrieve a list of all files ***
	
	
	
	private function set_top_lvl_ids(){
		//fiond id for static page
		$this->set_select('SELECT `id`');
		$this->set_from("pages");
		$this->set_where("name = 'static-pages'");
		//echo $sql_check,"<br />";
		$result_parent = $this->get_data();
		$count_parent = $this->numrows;//returns the total number of rows generated
		if ($result_parent && ($count_parent != 0))
		{
			$row_parent = $result_parent->fetch_object();
			$this->static_pages_id = $row_parent->id;
		}
		//fiond id for static page
		$this->set_select('SELECT `id`');
		$this->set_from("pages");
		$this->set_where("name = 'content-panels'");
		//echo $sql_check,"<br />";
		$result_parent = $this->get_data();
		$count_parent = $this->numrows;//returns the total number of rows generated
		if ($result_parent && ($count_parent != 0))
		{
			$row_parent = $result_parent->fetch_object();
			$this->content_panels_id = $row_parent->id;
		}
	}

		
	//********************************** CMS FUNCTIONS ***********************************		
	//************************************************************************************
	
	private function filename_from_tag($tag = null){
		
		$parts = explode('_',strtolower($tag));
		array_shift($parts);
		array_pop($parts);
		$parts = implode('-',$parts);
		
		return $parts;
		
	}	



	//clean up section tag for display use - email etc
	private function clean_tag_article($string, $type="") 
	{
		if ($type=="anchor")
		{
			//START AND END OF TAGS
			$string = str_replace("[TAG_", "TAG_", $string);
			$string = str_replace("_TAG]", "_TAG", $string);	
		}
		else
		{
			//START AND END OF TAGS
			$string = str_replace("[TAG_", "", $string);
			$string = str_replace("_TAG]", "", $string);								
			$string = str_replace("TAG_", "", $string);
			$string = str_replace("_TAG", "", $string);	
			//REMOVE ALL UNDERSCORES
			$string = str_replace("_", " ", $string);
		}//end type
		
		return $string;
	}//end clean_tag_article
	
	
	//strip certain tags and their contents
	public function strip_tags_content($text, $tags = '', $invert = FALSE) 
	{	
		preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
		$tags = array_unique($tags[1]);
		
		if (is_array($tags) AND count($tags) > 0) 
		{
			if ($invert == FALSE) 
			{			
				//array of all self closing tags
				$tags_self_closing = array('area','base','basefont','br','hr','img','input','link','meta');
				//iterate through each self closing tag
				foreach ($tags_self_closing as $tag)
				{
					if (in_array($tag,$tags))
					{						
						//use regex to match all self closing img tags to only return the matched tag
						preg_match_all('#<'.$tag.'[^>]*>#i', $text, $matches); 
						
						if (!empty($matches[0][0]))
						{
							return $matches[0][0];							
						}
					}
				}
				
				return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
			}
			else 
			{
				/*return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);*/
				return preg_replace('@<('. implode('|', $tags) .')\b.*?(\/>|.*?</\1>)@si', '', $text);
			}
		}
		elseif ($invert == FALSE) 
		{
			return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
		}
		return $text;
	}//strip_tags_content
	
	
}//class cms
?>