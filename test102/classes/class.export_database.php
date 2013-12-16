<?php
class export_database extends db_object 
{
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////
	
	/* define FTP constants - USE GLOBAL SETTINGS */
	public $chmod_ip = SITE_FTP_HOST;
	public $chmod_login = SITE_FTP_USER;
	public $chmod_pass = SITE_FTP_PASS;
	public $chmod_file = SITE_FTP_PATH;	
	
	//declare input publiciables
	public $csv_name = "file_";
	//public $save_dir = "/export_csv/"; //$localizer/    /public_html/demoshop/csv/ test.csv
	public $save_dir = "C:/websites/_danlearning/demoshop/site/export_csv/";
	public $save_folder = "export_csv/";
	public $file_extension = "";
	public $db_table = "";
	public $db_orderby = "";
	public $field_value = array();
	public $file_name = "";
	public $file_path = "";

	public $result_message = "";

	//define ftp_on variable set to 1: live site 0: offline
	public $ftp_on = 0; //ONLINE_FLAG;	0
	
	//Constructor function
	function __construct()
	{
		//ini_set('auto_detect_line_endings', '1'); //This setting detects if it is using Unix, MS-Dos or Macintosh line-ending conventions. 
	}	
	

	//PRIVATE function
	function cleanfields($str_string)
	{
		if(isset($str_string))
		{
			$this->str_string = strtolower(ereg_replace( "['\"\]", "", trim($str_string)));
			return $this->str_string;
		}
	}

	// PRIVATE function
	//connect to the server via ftp and set a chmod
	function ftp_connect_fcn($ip, $login, $pass, $file, $chmod_mode)
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
	}

	// PRIVATE function
	//connect to the server via ftp and set a chmod to 777
	function chmod_write()
	{
		return $this->ftp_connect_fcn($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $this->chmod_file, "0777");
	}

	// PRIVATE function
	//connect to the server via ftp and set a chmod to 703 no one can view the directory online
	function chmod_read()
	{
		return $this->ftp_connect_fcn($this->chmod_ip, $this->chmod_login, $this->chmod_pass, $this->chmod_file, "0703");
	}

	// PRIVATE function
	// create new directory on file system
	function make_directory($dir)
	{
		$tok  = strtok($dir,"/");
		while ($tok) 
		{
			$path .= $tok."/";			
			//echo "path:$path";
			if(is_dir($path) == false)
			{ 
				if(!mkdir($path , 0777))
				{
					return 0;
				}
			}
			$tok = strtok("/");
		}
	}

	// PUBLIC function
	function set_date($set_date = "")
	{	
		$this->set_date = $set_date;
		//debug echo $this->Page_id;
    }

	// PUBLIC function
	function csv_name($db_csv_name = "")
	{	
		$this->csv_name = strtolower(ereg_replace( "['\"\]", "", trim($db_csv_name)));
		//debug echo $this->csv_name;
    }

	// PUBLIC function
	function save_dir($save_dir = 0)
	{	
		$this->save_dir = ereg_replace( "['\"\]", "", trim($save_dir));
		//debug echo $this->Page_id;
		//create directory for file making sure to loop through dir passed in i.e. /new/sub/sub2/
		//call makedirectory function create all required directorys
		$dir_create = $this->make_directory($this->save_dir);
		if($dir_create == 1)
		{
			$this->result_message.= "Folder <B>$save_dir</B> could not be created";
		}
    }

	// PUBLIC function
	function file_extension($file_extension = "")
	{	
		$this->file_extension = strtolower(ereg_replace( "['\"\]", "", trim($file_extension)));
		//check that a dot has been entered for the extension if not the add one

		if($this->file_extension{0} <> "." )
		{
			$this->file_extension = ".".$this->file_extension;
		}
		//debug echo $this->Name;
    }

	// PUBLIC function
	function db_table($db_table = "")
	{	
		$this->db_table = strtolower(ereg_replace( "['\"\]", "", trim($db_table)));
		//debug echo $this->Name;
    }

	// PUBLIC function
	function db_orderby($db_orderby = "")
	{	
		$this->db_orderby = strtolower(ereg_replace( "['\"\]", "", trim($db_orderby)));
		//debug echo $this->Name;
    }

	// PUBLIC function
	function add_db_table_field($str_value, $bln_cln=1)
	{
		if(isset($str_value))
		{
			//run cleanfields function of bln_cln set to 0
			if($bln_cln==1)
			{
				$str_value = $this->cleanfields($str_value);
			}

			//add to the field_value array
			//first set var £cur to the next array key number
			$cur = count($this->field_value);
			$this->field_value[$cur] = $str_value;
		}
	}

	function add_db_between_criteria1($str_value, $bln_cln=1)
	{
		if(isset($str_value))
		{
			//run cleanfields function of bln_cln set to 0
			if($bln_cln==1)
			{
				$this->between_criteria1 = $this->cleanfields($str_value);
			}
			else
			{
				$this->between_criteria1 = $str_value;
			}
		}
	}
	
	function add_db_between_criteria2($str_value, $bln_cln=1)
	{
		if(isset($str_value))
		{
			//run cleanfields function of bln_cln set to 0
			if($bln_cln==1)
			{
				$this->between_criteria2 = $this->cleanfields($str_value);
			}
			else
			{
				$this->between_criteria2 = $str_value;
			}
		}
	}

	function between_field($str_value, $bln_cln=1)
	{
		if(isset($str_value))
		{
			//run cleanfields function of bln_cln set to 0
			if($bln_cln==1)
			{
				$this->between_field = $this->cleanfields($str_value);
			}
			else
			{
				$this->between_field = $str_value;
			}
		}
	}
	

	function create_file($debug=0)
	{	
		$create_file = 0;
		$debug_string = "";		
	
		if (!empty($this->db_table))
		{
			//SQL SELECT METHOD CALLS
			$this->set_select();
			$this->set_from($this->db_table);

			if(!empty($this->between_criteria1) && !empty($this->between_criteria2) && !empty($this->between_field))
			{
				$this->set_where($this->between_field ." BETWEEN '".$this->between_criteria1."' AND '".$this->between_criteria2."'");
			}	

			if(!empty($this->db_orderby))
			{
				$this->set_orderby($this->db_orderby);
			}	
	
			$result = $this->get_data();
			$count = $this->numrows; //returns the total number of rows generated
		
			if ($result && $count > 0)
			{	
				//$this->file_name = $this->csv_name."data_(".$this->set_date.")";		
				//$this->file_path = $this->save_dir.$this->file_name.$this->file_extension;
				//$this->file_path = $this->save_dir."test".$this->file_extension; //TESTING
				$debug_string.= "<br />this->file_path:".$this->file_path;
			
				// columns 
				//create columns string using 'field value' array
				$columns = "";
				
				for($i = 0; $i < count($this->field_value); $i++)
				{
					//add comma after first loop
					if($i != 0) { $columns .= ","; }
		
					$columns .= strtoupper($this->field_value[$i]);
			
					//add new line at final loop
					if($i == (count($this->field_value)-1)) 
					{
						 $columns .= "\r\n"; //\n \r\n
					}
				}
		
				//chmod the ftp folder to 0777
				if ($this->ftp_on == 1)
				{
					$chmod_write = $this->chmod_write();
					if ($chmod_write <> "")
					{
						$this->result_message.= $chmod_write;
					}
				}

				//echo "<div>create_file testvar:$debug_string</div>"; return; //TESTING
	
				$fp = fopen ($this->file_path, "w+"); 
		 		//$fp = fopen ("C:/websites/_danlearning/demoshop/site/export_csv/test.csv", "w+"); //TESTING
		 
				if ($columns)
				{
					if (fwrite ($fp, $columns))
					{
						$this->result_message.= "Column titles added.<br />"; 
					}
					else
					{
						$this->result_message.= "Column titles not added.<br />"; 
					}
				}
					
				// MySQL Array 
				if ($result) 
				{ 			
					while ($row = mysql_fetch_array($result)) 
					{ 		
						// columns 
						//create columns string using 'field value' array
	
						for($i = 0; $i < count($this->field_value); $i++)
						{
							//add comma after first loop
							if($i != 0) { $data .= ","; }
		
							$field = $this->field_value[$i];
							//replace any commas with semi-colons to avoid upseetting the CSV file format
							$data .= $this->strip_all_slashes($this->nl2blank(html_entity_decode(str_replace(",", ";",html_entity_decode($row["$field"], ENT_QUOTES)), ENT_QUOTES)));
							//$data = trim($data);
	
							//add new line at final loop
							if ($i == (count($this->field_value)-1)) 
							{ 
								$data .= "\r\n";  //\n \r\n
							}	
						}//end for	
					}//end while
				
					if ($data)
					{
						if (fwrite ($fp, $data))
						{
							$create_file = 1;
							$this->result_message.= "A CSV file has been created."; 
						}
						else
						{
							$this->result_message.= "File not created. There maybe an error when trying to create the ($this->file_extension) file."; 
						}
					}								
				} 
				else 
				{	
					$this->result_message.= "No data. There maybe an error in the database."; 	
				}//end result
				
				fclose($fp); 
	
				if ($this->ftp_on == 1)
				{
					//chmod the ftop folder to 0755
					$chmod_read = $this->chmod_read();
					if ($chmod_read <> "")
					{
						$this->result_message.= $chmod_read;
					}
				}							
			}//end if $count > 0	
		}//end if table isset

		//debug code
		if ($debug==1)
		{
			echo "<div>create_file testvar:$debug_string</div>";
		}//end if ($debug==1)
		return $create_file;
	}//create_file


	//function used to remove all\r and \n chars
	function nl2blank($string) 
	{
		$string = str_replace(array("\\r\\n", "\r\n", "\r", "\n"), " ", $string);
		return $string;
	}
	
	function strip_all_slashes($string)
	{
		$string = str_replace(array("\\"), "", $string);
		return $string;
	}

}//class ExportDatabase
?>