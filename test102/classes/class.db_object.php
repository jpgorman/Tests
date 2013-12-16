<?php
class db_object extends mysqli
{	
	//PRIVATE MEMBERS	
	/* USE GLOBAL SETTINGS */
  	protected $DB_HOST = SITE_DB_HOST;
	protected $DB = SITE_DB;
	protected $USER = SITE_USER;
	protected $PASS = SITE_PASS;
	
	protected $db_connection = NULL;
	protected $db_table;
	protected $str_string;
   	protected $sql_select;
   	protected $sql_from;
   	protected $sql_where;
   	protected $sql_groupby;
   	protected $sql_having;
   	protected $sql_orderby;
   	protected $sql_limit;
   	protected $sql_query;
	protected $result;
	protected $total_pages;
	protected $pre_query_total_pages;
	protected $page_num;
	protected $db_insert_sql;
	protected $db_update_sql;
	protected $db_delete_sql;
	
	// PUBLIC MEMBERS
	
	/*DEBUG VARIABLE FOR SQL ERROR MESSAGE SET TO 1 TO SHOW SQL ERROR MESSAGE SET TO 0 TO SHOW GENERIC MESSAGE*/
	public $show_errors = null;
	public $debug_values = array();
	public $update_value = array();
	public $insert_value = array();
   	public $rows_per_page;
	public $DB_PREFIX = SITE_DB_PREFIX;

	public 	function __construct(){
		//fire up a connection to the database
		$this->connect_db();
	}
	
	// store the single instance of mysqli object
    private static $mysqli_instance; 
	
	// get instance of object or return existing object
    public static function get_instance($host, $user, $pass, $db) 
    { 
        if (!self::$mysqli_instance) 
        { 
            self::$mysqli_instance = new mysqli($host, $user, $pass, $db); 

			//change character set to utf8
			if (!self::$mysqli_instance->set_charset("utf8")) {
				printf("Error loading character set utf8: %s\n", self::$mysqli_instance->error);
			}
        } 

        return self::$mysqli_instance; 
    }

	// ***** OBJECT WIDE PRIVATE FUNCTIONS ***** //
	/*******************************************/
	// PRIVATE function
	// database connection method
	public function connect_db()
	{
		if(DEBUG_DISPLAY_FLAG == 'on'){
			$this->show_errors = 1;
		}else{
			$this->show_errors = 0;
		}
		
		// use singleton pattern to get instance of mysqli object
		$this->db_connection = $this->get_instance($this->DB_HOST, $this->USER, $this->PASS, $this->DB);
		
		if(!$this->db_connection){
			$message = "Unable to connect to MySQL server: <strong><em>" . $this->DB_HOST . "</em></strong>";
			$message .= 'Connect Error: ' . $this->db_connection->error;
			$this->db_connection = 0;
		}else{
			$message = "connected";
			$this->db_connection;			
		}
		//return array($message, $db_connection);
		return array($message, $this->db_connection);
	}
	
	
	public function cleanstring($str_string)
	{	
		//For basic input fields data	
		//remove all tags, HTML encode, then Escape special characters
		$this->str_string = $this->db_connection->real_escape_string( htmlspecialchars( strip_tags($str_string) , ENT_QUOTES, 'UTF-8') ); //mysql_real_escape_string() mysql_escape_string
		//echo "this->str_string:".$this->str_string;
		return $this->str_string;
	}

	public function cleanstring_input($str_string)
	{	
		//For basic input fields data - encoding of normal data to database
		$this->str_string = $this->db_connection->real_escape_string( html_entity_decode( strip_tags($str_string) , ENT_QUOTES, 'UTF-8') ); //mysql_real_escape_string() mysql_escape_string
		//$this->str_string = $str_string; //mysql_real_escape_string() mysql_escape_string
		//echo "this->str_string:".$this->str_string;
		return $this->str_string;
	}

	public function cleanstring_ckeditor($str_string)
	{	
		//encoding of ckeditor data to database
		$this->str_string = $this->db_connection->real_escape_string($str_string); //mysql_real_escape_string() mysql_escape_string
		//echo "this->str_string:".$this->str_string;
		return $this->str_string;
	}	
	

	public function cleanstring_plain($str_string)
	{	
		//For basic input fields data - encoding of normal data to database
		//strip out any encoded html entities
		$this->str_string = preg_replace('/(&)[^;]+;/','',$str_string);
		$this->str_string = html_entity_decode( strip_tags($this->str_string) , ENT_QUOTES, 'UTF-8'); 
		$this->str_string = $this->nl2zero($this->str_string); //remove any newlines or tabs
		//echo "this->str_string:".$this->str_string;
		return $this->str_string;
	}


	public function htmlsafe($str_string)
	{		
		//remove all tags and comments marks
		$str_string = stripslashes($str_string);
		$str_string = html_entity_decode($str_string, ENT_QUOTES);
		return $str_string;
	}//htmlsafe
	
	public function htmlsafe_input($str_string)
	{		
		//normalise output of normal data from database
		$str_string = htmlspecialchars( stripslashes($str_string) , ENT_QUOTES, 'UTF-8'); //, "UTF-8"
		return $str_string;
	}//htmlsafe	

	public function htmlsafe_ckeditor($str_string)
	{		
		//normalise output of ck_editor data from database
		$str_string = stripslashes($str_string);
		return $str_string;
	}//htmlsafe	
	
	//make a string safe for use in the GET querystring
	public function make_url_safe($str, $settings=array('separator'=>'-'))
	{
		$illegal_chars = array("'", "\"", "£", "$", "%", "^", "&", "*", "(", ")", "+", "=", "`", "¬"); // list of illegal chars
		
		$str = strtolower($str); //make lowercase
		$str = str_replace($illegal_chars, "", $str); //remove illegal characters
		$str = preg_replace('/[^a-z0-9_]/i', $settings['separator'], $str);
		$str = preg_replace('/' . preg_quote($settings['separator']) . '[' . preg_quote($settings['separator']) . ']*/', $settings['separator'], $str);
		// return
		return $str;
	}

	private function cleanfields($str_string){
		if(isset($str_string)){
			$this->str_string = strtolower(ereg_replace( "['\"\]", "", trim($str_string)));
			return $this->str_string;
		}
	}

	public function pr($var=NULL){
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}

	public function sql_failure_handler($query, $error) 
	{
		$msg = htmlspecialchars("Failed Query: {$query}<br />SQL Error: {$error}");
		//error_log($msg, 3, "/home/site/logs/sql_error_log");
		if ($this->show_errors == 1) {
			return $msg;
		}else{
			return "Requested page is temporarily unavailable, please try again later.";
		}
	}

	// ***** DATABASE SELECT QUERYING FUNCTIONS ***** //
	/*******************************************/
	public function set_page_num($page_num=1)
	{
		if(empty($page_num)){
			$this->page_num = 1;
		}else if(gettype($page_num) == "integer"){
			$this->page_num = $page_num;
		}else{
			echo "Page number must be an integer";
		}
		return $this->page_num;
	}

	public function set_rows_per_page($rows_per_page)
	{
		if(isset($rows_per_page) && gettype($rows_per_page) == "integer" && isset($this->page_num)){
			$this->rows_per_page = $rows_per_page;
			$this->set_limit();
		}
	}

	public function set_select($sql_select=NULL)
	{
		if(empty($sql_select)){
		   $this->sql_select = 'SELECT *';    // the default is all fields
		} else {
		   $this->sql_select = $sql_select;   
		} // if
		//echo $this->sql_select;
	}

	public function set_from($sql_from)
	{	
		if (isset($sql_from)) {
		   $this->sql_from = $this->DB_PREFIX.$sql_from;   // the default is current table
		} // if
		//echo $this->sql_from;
    }
	
	public function set_where($sql_where, $or = NULL)
	{
		if (empty($sql_where)) {
		   $this->sql_where = NULL;
		} else {

		   if (isset($this->sql_where)) {
		   	  if(empty($or)){
			  	$this->sql_where .= " AND $sql_where";
			  }else{
			  	$this->sql_where .= " OR $sql_where";
			  }
		   } else {
			  $this->sql_where = "WHERE $sql_where";
		   } // if

		} // if
		//echo $this->sql_where;
	}

	public function set_groupby($sql_groupby=NULL)
	{
		if (isset($sql_groupby)) {
		   $this->sql_groupby = "GROUP BY $sql_groupby";
		} else {
		   $this->sql_groupby = NULL;
		} // if
	}

	public function set_having($sql_having=NULL)
	{
		if (isset($sql_having)) {
	   		$this->sql_having = "HAVING $sql_having";
		} else {
		    $this->sql_having = NULL;
		} // if
	}

	public function set_orderby($sql_orderby=NULL)
	{
		if (isset($sql_orderby)) {
	   		$this->sql_orderby = "ORDER BY $sql_orderby";
		} else {
		    $this->sql_orderby = NULL;
		} // if
	}

	// PRIVATE function
	public function set_limit($sql_limit=NULL)
	{
		if(isset($this->rows_per_page)){
			if ($this->rows_per_page > 0) {				
				//if the current page number is greater than the total number of pages then it will need to decremented
				//this only happend when deleting the last record on a given page off records
				if(!empty($this->total_pages)){//check that the total number of pages has been calculated using the get_record_count() method
					echo $this->total_pages;
					if($this->page_num > $this->total_pages){
						//check if the number of total rows in the query is divisible by the number of rows per page
						//if it is then the last record on that page has been deleted
						if($this->numrows % $this->rows_per_page == 0){
							//decrement the current page number by 1
							$this->page_num --;					
						}
					}
				}
				$this->sql_limit = 'LIMIT ' .($this->page_num - 1) * $this->rows_per_page .',' .$this->rows_per_page;			
			} else {
			   	$this->sql_limit = NULL;
			} // if
		}elseif(isset($sql_limit)){
			$this->sql_limit = 'LIMIT ' .$sql_limit;
		}
	}

	// PUBLIC function
	public function get_sql($limit=1){
		$this->query = "$this->sql_select
				FROM $this->sql_from 
					 $this->sql_where
					 $this->sql_groupby 
					 $this->sql_having
					 $this->sql_orderby";
		if($limit==1){
			$this->query .= " $this->sql_limit";
		}
		//echo $this->query;
		return $this->query;					 
	}
	
	public function get_data($test=0){
		//get a database connection
		list ($err_con_msg, $db_connection) = $this->connect_db();
		if($db_connection == 0){
			echo $err_con_msg;
		}
		
		//get the total number of records by omiting the limit clause from the sql string
		$this->get_sql($limit=0);
		
		//start time
		$this->timer('start');
		
		
		//echo $query;
		if($test==0){
			if($this->db_connection->real_query($this->query) or die($this->sql_failure_handler($this->query, $this->db_connection->error))){
				
				//run query and store result set
				$this->result = $this->db_connection->store_result();
				
				//echo "<br />".$this->query;
				$this->numrows = $this->db_connection->affected_rows;	
				//return result
			}
			//mysql_free_result($this->result);
		}
		//free memory		
		
		//get live data set
		$this->get_sql();
		
		if($test==0){
			if($this->db_connection->real_query($this->query) or die($this->sql_failure_handler($this->query, $this->db_connection->error))){
				
				//run query and store result set
				$this->result = $this->db_connection->store_result();
				
				//end timer
				$time = $this->timer('stop');
				
				//add to the sql string to the debug array
				//first set var $cur to the next array key number
				$cur = count($this->debug_values);
				$this->debug_values[$cur]['SQL_SELECT'] = array('query'=> $this->query, 'time'=> "$time seconds");
				
				$this->clear_sql();
				//return result		
				return $this->result;
			}
			//free memory
			//mysql_free_result($this->result);
		}else{
			//output query string to debug
			echo $this->query."<br />";
			//empty sql variables
			$this->clear_sql();
		}
		
		//empty sql variables			
	}

	public function get_total_pages(){
		if(isset($this->rows_per_page)){
			return $this->total_pages = ceil( $this->numrows / $this->rows_per_page );
		}
	}
	
	//get the number of records returned by a query without blanking the query string	
	public function get_record_count(){
		//get a database connection
		list ($err_con_msg, $db_connection) = $this->connect_db();
		if($db_connection == 0){
			echo $err_con_msg;
		}	
		
		//get the total number of records by omiting the limit clause from the sql string
		$this->get_sql($limit=0);
		//echo $query;
		if($test==0){/**/
			if($this->result = $this->db_connection->query($this->query) or die($this->sql_failure_handler($this->query, $this->db_connection->error))){
				//echo "<br />".$this->query;
				$this->numrows = $this->db_connection->affected_rows;
				//echo mysql_num_rows($this->result);	
				//return result
			}
		}
	}

	// ***** DATABASE INSERT FUNCTIONS ***** //
	/*******************************************/
	public function set_insert($db_insert_fields){
		if(isset($this->DB_PREFIX) && isset($this->db_table)){
			if(isset($db_insert_fields)){
				//clean the input field list of any illiegal characters
				$this->query = $this->DB_PREFIX.$this->db_table." (";
				$this->query .= mysql_escape_string($this->cleanfields($db_insert_fields));			
				$this->query .= ") ";
				//echo $this->db_insert_fields;
			}else{
				$this->query = NULL;
			}
		}else{
			$this->query = NULL;
			echo "Make sure that $this->DB_PREFIX and $this->db_table are set";
		}
	}

	public function add_insert_value($str_value, $bln_cln=1)
	{
		if (isset($str_value))
		{		
			//run cleanstring function if bln_cln set
			if ($bln_cln == "input") 
			{
				$str_value = $this->cleanstring_input($str_value);
			}
			else if ($bln_cln == "ck_editor") 
			{
				$str_value = $this->cleanstring_ckeditor($str_value);
			}
			else if ($bln_cln == 1)
			{
				$str_value = $this->cleanstring($str_value);
			}

			//if the "MYSQL_FUNCTION" value is set then just pass the value
			// straight into the query string. This is for mysql funstions like SUM, COUNT etc
			// that would otherwise be passed in as strings

			if ($bln_cln == "MYSQL_FUNCTION")
			{
				$str_name = "`".trim($str_name)."`";
				$str_value = trim($str_value);
			}
			else
			{
				switch(gettype($str_value))
				{
					case "string":
						$str_value = "'".trim($str_value)."'";
					break;
					case "integer":
					case "double":
						$str_value = trim($str_value);
					break;
					default:
						$str_value = "'".trim($str_value)."'";			
				}
			}

			//---------------------------------------------------------------------------------------------------------
			//encoding of data when doing database inserts. At the moment we're relying on PHP to encode data using the 
			//default environment settings. I think we should standardise this in the database class. This can be done 
			//by updating the add_insert_value method and the add_updated_method by adding in the folowing code : 
			
			//make sure that the string being inserted is using the correct form of encoding
			if(mb_detect_encoding($str_value, "UTF-8") != "UTF-8"){
				$str_value = mb_convert_encoding($str_value, 'UTF-8', 'UTF-8');
			}
            //$str_value = mb_convert_encoding($str_value, "UTF-8");
			//---------------------------------------------------------------------------------------------------------

			//add to the insert_value array
			//first set var £cur to the next array key number
			$cur = count($this->insert_value);
			$this->insert_value[$cur] = $str_value;
			//echo $this->insert_value[$cur];
		}
	}//add_insert_value

	public function insert_data($test=0){
		//only build inest string if the insert values array is not blank
		//and the insert sql is not blank		
		if(count($this->insert_value) > 0 && (isset($this->query))){

			$this->query .= "VALUES (";
			//iterate through $this->insert_value array to build insert SQL
	
				$value = "";
				for($i = 0; $i < count($this->insert_value); $i++)
				{
					if($i != 0) { $value .= ", "; }
					$value .= $this->insert_value[$i];
				}
	
			$this->query .= $value;
			$this->query .= ")";

			//complete the insert statement
			$this->query = "INSERT INTO ".$this->query;
			//finallly call the do insert fucntion to update the datbase
			//only run the database query when the dunctions test mode is set to 0 else output sql string
			if($test==0){
				return $this->do_insert();
			}else{
				//output query string to debug
				echo $this->query."<br />";
				//empty sql variables
				$this->clear_sql();
			}
		}
	}

	private function do_insert(){
		//get a database connection
		list ($err_con_msg, $db_connection) = $this->connect_db();
		if($db_connection == 0){
			echo $err_con_msg;
		}else{
		
			//start timer
			$this->timer('start');
			
			//run sql query
			if($this->result = $this->db_connection->query($this->query) or die($this->sql_failure_handler($this->query, $this->db_connection->error))){
				
				//end timer
				$time = $this->timer('stop');			
		
				//add to the sql string to the debug array
				//first set var $cur to the next array key number
				$cur = count($this->debug_values);
				$this->debug_values[$cur]['SQL_INSERT'] = array('query'=> $this->query, 'time'=> "$time seconds");
				
				//empty sql variables
				$this->clear_sql();
				//return result		
				return $this->db_connection->insert_id;
			}
		}		
	}

	// ***** DATABASE UPDATE FUNCTIONS ***** //
	/*******************************************/
	public function set_update(){
		if(isset($this->DB_PREFIX) && isset($this->db_table)){
				$this->query = $this->DB_PREFIX.$this->db_table." SET ";
		}else{
			$this->query = NULL;
			echo "Make sure that $this->DB_PREFIX and $this->db_table are set";
		}
	}

	public function add_update_value($str_name, $str_value, $bln_cln=1)
	{
		if (isset($str_value))
		{
			//run cleanstring function if bln_cln set to 0
			if ($bln_cln == "input") 
			{
				$str_name = $this->cleanstring_input($str_name);
				$str_value = $this->cleanstring_input($str_value);
			}
			else if ($bln_cln == "ck_editor") 

			{
				$str_name = $this->cleanstring_ckeditor($str_name);
				$str_value = $this->cleanstring_ckeditor($str_value);
			}
			else if ($bln_cln==1)
			{
				$str_name = $this->cleanstring($str_name);
				$str_value = $this->cleanstring($str_value);
			}

			//if the "MYSQL_FUNCTION" value is set then just pass the value
			// straight into the query string. This is for mysql funstions like SUM, COUNT etc
			// that would otherwise be passed in as strings

			if ($bln_cln == "MYSQL_FUNCTION")
			{
				$str_name = "`".trim($str_name)."`";
				$str_value = trim($str_value);
			}
			else
			{
				switch(gettype($str_value))
				{
					case "string":
						$str_name = "`".trim($str_name)."`";
						$str_value = "'".trim($str_value)."'";
					break;
					case "integer":
					case "double":
						$str_name = "`".trim($str_name)."`";
						$str_value = trim($str_value);
					break;
					default:
						$str_name = "`".trim($str_name)."`";
						$str_value = "'".trim($str_value)."'";			
				}
			}

			//---------------------------------------------------------------------------------------------------------
			//encoding of data when doing database inserts. At the moment we're relying on PHP to encode data using the 
			//default environment settings. I think we should standardise this in the database class. This can be done 
			//by updating the add_insert_value method and the add_updated_method by adding in the folowing code : 
			
			//make sure that the string being inserted is using the correct form of encoding
            //$str_value = mb_convert_encoding($str_value, "ISO-8859-1");
			if(mb_detect_encoding($str_value, "UTF-8") != "UTF-8"){
				$str_value = mb_convert_encoding($str_value, 'UTF-8', 'UTF-8');
			}
			//---------------------------------------------------------------------------------------------------------

			//add to the update_value array
			//first set var £cur to the next array key number
			//this is a two dimensional array where [0] = name and [1] = value
			$cur = count($this->update_value);
			$this->update_value[$cur][0] = trim($str_name);
			$this->update_value[$cur][1] = trim($str_value);
		}
	}//add_update_value

	public function update_data($test=0){
		//only build inest string if the insert values array is not blank
		//and the insert sql is not blank
		if(count($this->update_value) > 0 && (isset($this->query))){

			//iterate through $this->insert_value array to build insert SQL
	
				$value = "";
				for($i = 0; $i < count($this->update_value); $i++)
				{
					if($i != 0) { $value .= ", "; }
					$value .= $this->update_value[$i][0];
					$value .=  "=".$this->update_value[$i][1];
				}

				$this->query .= $value;

				//add where clause if one has been entered
				if(isset($this->sql_where)){
					$this->query .= " ".$this->sql_where;
				}
			
			$this->query = "UPDATE ".$this->query;

			//only run the database query when the dunctions test mode is set to 0 else output sql string
			if($test==0){
				//finallly call the do update fucntion to update the datbase
				return $this->do_update();
			}else{
				//output query string to debug
				echo $this->query."<br />";
				//empty sql variables
				$this->clear_sql();
			}
		}
	}

	private function do_update(){
		//get a database connection
		list ($err_con_msg, $db_connection) = $this->connect_db();
		if($db_connection == 0){
			echo $err_con_msg;
		}else{
		
			//start timer
			$this->timer('start');
			
			//run sql query
			if($this->result = $this->db_connection->query($this->query) or die($this->sql_failure_handler($this->query, $this->db_connection->error))){					
			
				//end timer
				$time = $this->timer('stop');
				
				//add to the sql string to the debug array
				//first set var $cur to the next array key number
				$cur = count($this->debug_values);
				$this->debug_values[$cur]['SQL_UPDATE'] = array('query'=> $this->query, 'time'=> "$time seconds");
			
				//empty sql variables
				$this->clear_sql();		
				//return result		
				return $this->db_connection->insert_id;
			}
		}
		//empty sql variables
		$this->clear_sql();		
	}


	// ***** DATABASE DELETE FUNCTIONS ***** //
	/*******************************************/
	public function set_delete($query=NULL)
	{
		if(empty($query)){
		   $this->query = ' ';    // the default is delete all fields
		} 
		
		if(isset($this->query) && isset($this->db_table)){
			$this->query .= " FROM ".$this->DB_PREFIX.$this->db_table;
		}else{
			echo "SQL string incomplete (".$this->query.")";
		}
	}

	public function delete_data($test=0){
		//only build inest string if the insert values array is not blank
		//and the insert sql is not blank
		if(isset($this->query)){

			//add where clause if one has been entered
			if(isset($this->sql_where)){
				$this->query .= " ".$this->sql_where;
			}
			
			$this->query = "DELETE ".$this->query;
			
			//only run the database query when the dunctions test mode is set to 0 else output sql string
			if($test==0){
				//finallly call the do delete function to update the datbase
				return $this->do_delete();
			}else{
				//output query string to debug
				echo $this->query."<br />";
				//empty sql variables
				$this->clear_sql();
			}
		}
	}
	
	private function do_delete(){
		//get a database connection
		list ($err_con_msg, $db_connection) = $this->connect_db();
		if($db_connection == 0){
			echo $err_con_msg;
		}else{
		
			//start timer
			$this->timer('start');
		
			//add to the sql string to the debug array
			//first set var $cur to the next array key number
			$cur = count($this->debug_values);
			$this->debug_values[$cur]['SQL_DELETE'] = $this->query;
			//run sql query
			if($this->result = $this->db_connection->query($this->query) or die($this->sql_failure_handler($this->query, $this->db_connection->error))){
				
				//end timer
				$time = $this->timer('stop');				
			
				//add to the sql string to the debug array
				//first set var $cur to the next array key number
				$cur = count($this->debug_values);
				$this->debug_values[$cur]['SQL_DELETE'] = array('query'=> $this->query, 'time'=> "$time seconds");
				
				//empty sql variables
				$this->clear_sql();	
				
				//return 1;
				return $this->db_connection->affected_rows;
			}
		}
		//empty sql variables
		$this->clear_sql();		
	}
	
	private function clear_sql(){
		//echo $this->query."<br /><br />";
		/*clear query strings and name value pairs*/
		if (isset($this->sql_select)){
			$this->sql_select = NULL;
		}
		if (isset($this->sql_from)){
			$this->sql_from = NULL;
		}
		if (isset($this->sql_where)){
			$this->sql_where = NULL;
		}
		if (isset($this->sql_groupby)){
			$this->sql_groupby = NULL;
		}
		if (isset($this->sql_having)){
			$this->sql_having = NULL;
		}
		if (isset($this->sql_orderby)){
			$this->sql_orderby = NULL;
		}
		if (isset($this->sql_limit)){
			$this->sql_limit = NULL;
		}
		unset($this->insert_value);
		unset($this->update_value);

		if (isset($this->query)){
			$this->query = NULL;
			echo $this->query;
		}
	}
	
	public function sql_debug($return_as = "echo"){

		if(count($this->debug_values) > 0){
			switch($return_as){
				case "echo":
					echo "<pre>";
					print_r($this->debug_values);
					echo "</pre>";
				break;
				case "array":
					return $this->debug_values;
				break;
			}
		}
	}
	
	//start / stop and return time in secs
	private function timer($mode = "start"){
		
		if(empty($this->sql_time) && $mode == "start"){
			//start timer
			$this->sql_time = microtime(true);
		}
		
		if(!empty($this->sql_time) && $mode == "stop"){
			//end timer
			$time_end = microtime(true);
			//calc time
			$time = $time_end - $this->sql_time;
			
			$this->sql_time = null;
			
			return $time;
		}
	}	
			
	public function nl2br2($string) 
	{
		$string = str_replace(array("\\r\\n", "\r\n", "\r", "\n"), "<br />", $string);
		return $string;
	}	
	
	
	public function nl2zero($string) 
	{
			$string = str_replace(array("\\r\\n", "\r\n", "\r", "\n", "\t"), "", $string);
			return $string;
	}

}
 
//$c_dbobject = new db_object;

/*
//SQL SELECT METHOD CALLS
$c_dbobject->set_select("p.*, p.product_name AS p_name, ps.*, st.* ");
$c_dbobject->set_from("artcrowd_products p LEFT JOIN artcrowd_pricing_structure ps ON p.pricing_structure = ps.ps_id LEFT JOIN artcrowd_status st ON st.article = p.product_id ");
$c_dbobject->set_where("p.category_id = '61'");
$c_dbobject->set_orderby("p.category_id ASC");
//SELECT QUERY RESULT METHODS
$c_dbobject->set_page_num(4);
$c_dbobject->set_rows_per_page(10);
//OUTPUT QUERY RESULTS TO PAGE

$result = $c_dbobject->get_data();

while ($row = mysql_fetch_assoc($result)){
	echo $row['p_name'] . " " . $row['product_id']."<br />";
}
$total_pages = $c_dbobject->get_total_pages();
//echo $total_pages;
//loop through total number of pages and add a link to each individual page
for ($i = 1; $i <= $total_pages; $i++) {
//show curent page as active link by changin the css class
	if ($i == $page){
   		$navigation .= " <a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&page=".$i."\" class=\"on\" title=\"".$i."\">$i</a> ";
	}else{
		$navigation .= " <a href=\"".$_SERVER['PHP_SELF']."?".$strQuery."&page=".$i."\" title=\"".$i."\">$i</a> ";
	}
}
echo $navigation;
*/
/*
//SQL INSERT METHOD CALLS
//specify table name
$c_dbobject->db_table = "competition_entries";
$c_dbobject->set_insert("title, f_name, l_name, email, answer");
//add argument to insert values array
$c_dbobject->add_insert_value("title");
$c_dbobject->add_insert_value("f_name");
$c_dbobject->add_insert_value("l_name");
$c_dbobject->add_insert_value(email);
$c_dbobject->add_insert_value(answer, "HTML");//allows for HTML string to be passed into DB
//call method to ceate insert query
$c_dbobject->insert_data(1);//add 1 in method call to allow for query to be passed out using $c_dbobject->query;
//returns the row_id for the inserted item
echo $c_dbobject->query;
*/
/*
//SQL UPDATE METHOD CALLS
$c_dbobject->db_table = "competition_entries";
//no need to pass in table columns 
$c_dbobject->set_update();
$c_dbobject->add_update_value("title","new title2");
$c_dbobject->add_update_value("f_name","new firstname2");
$c_dbobject->add_update_value("l_name","new lastname2");
$c_dbobject->add_update_value("email","new email2");
$c_dbobject->add_update_value("answer", "new answer2", "HTML");//allows for HTML string to be passed into DB
$c_dbobject->set_where("r_id = 10");
$c_dbobject->update_data(1);//add 1 in method call to allow for query to be passed out using $c_dbobject->query;
echo $c_dbobject->query;
*/
/*
//SQL DELETE METHOD CALLS
$c_dbobject->db_table = "competition_entries";
$c_dbobject->set_delete();//initialise delete SQL
$c_dbobject->set_where("title = 'title'");
$c_dbobject->set_where("l_name = 'l_name'");
$c_dbobject->delete_data(1);//add 1 in method call to allow for query to be passed out using $c_dbobject->query;
echo $c_dbobject->query;
*/
?>