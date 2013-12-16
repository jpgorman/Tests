<?php
class import_database extends db_object 
{
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////

	var $DB_TABLE_VARIATIONS = "directory_variations"; //test_
	var $DB_TABLE_VARIATION_FIELDS = "product_id, code, var1, var2, var3, image_id";
	//"product_id, code, var1, var2, var3, price, rrp, stock_status, visible, stock_level, image_id";
	
	var $DB_TABLE_PRODUCTS = "directory_products"; //test_

	var $DB_TABLE = '';
	var $DB_TABLE_FIELDS = '';
		
	/* define FTP constants - USE GLOBAL SETTINGS */
	public $chmod_ip = SITE_FTP_HOST;
	public $chmod_login = SITE_FTP_USER;
	public $chmod_pass = SITE_FTP_PASS;
	public $chmod_file = SITE_FTP_PATH;	
	
	//declare input publiciables
	public $csv_name = "file_";
	//public $save_dir = "/export_csv/"; //$localizer/    /public_html/demoshop/csv/ test.csv
	public $save_dir = "C:/websites/_danlearning/demoshop/site/export_csv/";
	public $save_folder = "/export_csv/";
	public $db_table = "";
	public $db_orderby = "";
	public $field_value = array();
	public $file_name = "";
	public $file_path = "";
	public $result_message = "";
	public $set_import_rows = 13;
	public $new_order_id = 0;
	public $order_id = 0;

	// Maximum file size for uploaded file (xMB) work out using (x * 1024 * 1024;) in this case 5MB 5242880 is the MAX
	var $max_file_size = 5242880; 		
	var $max_file_size_mb = 0;
	
	//create a list of file types that can be accepted in array
	var $array_file_type = array('application/vnd.ms-excel','text/plain', 'application/octet-stream', 'text/comma-separated-values');	
	var $array_file_extension = array('csv');		//create a list of file extensions that can be accepted in array
	var $upload_file_extension = NULL; //this variable is set when checking the uploaded files extension
	
	public $records_added = 0;
	public $records_failed = 0;
	public $records_failed_input = 0;	
	public $messages = "";	

	//define ftp_on variable set to 1: live site 0: offline
	public $ftp_on = 0; //ONLINE_FLAG;	
	
	
	//Constructor function
	function __construct()
	{
		ini_set('auto_detect_line_endings', '1'); //This setting detects if it is using Unix, MS-Dos or Macintosh line-ending conventions. 
		
		//max size in bytes convert to MB 
		$this->max_file_size_mb = round(($this->max_file_size/1048576), 2);
	}	
	
	
	// function
	function action($input_action = NULL)
	{
		if($input_action <> NULL){
       		$this->action = $this->cleanstring(trim($input_action));
		}
		//echo $this->action; //debug 
    }
	
	// function
	function submit($input_submit = NULL)
	{
		if($input_submit <> NULL){
       		$this->submit = $this->cleanstring(trim($input_submit));
		}
		//echo $this->submit; //debug 
    }	
	
	function set_table($input_set_table = "")
	{
        $this->DB_TABLE = trim($input_set_table);
		//debug echo $this->set_table;
    }
	
	function set_table_fields($input_set_table_fields = "")
	{
        $this->DB_TABLE_FIELDS = trim($input_set_table_fields);
		//debug echo $this->set_table_fields;
    }	
	
	function csv_set_object($input_csv_set_object = "")
	{
		if (!empty($input_csv_set_object))
		{						
			//assign
			$this->object_csv = $input_csv_set_object;
		}
	}//csv_object

	function csvfile_error($input_csvfile_error = "")
	{
        $this->csvfile_error = trim($input_csvfile_error);
		//debug echo $this->csvfile_error;
    }
	
	function csvfile_size($input_csvfile_size = "")
	{
        $this->csvfile_size = trim($input_csvfile_size);
		//debug echo $this->csvfile_size;
    }
	
	function csvfile_name($input_csvfile_name = "")
	{
        $this->csvfile_name = trim($input_csvfile_name);
		//debug echo $this->csvfile_name;
    }
	
	function csvfile_tmp_name($input_csvfile_tmp_name = "")
	{
        $this->csvfile_tmp_name = trim($input_csvfile_tmp_name);
		//debug echo $this->csvfile_tmp_name;
    }

	// PUBLIC function
	function category_id($input_category_id = "")
	{	
		$this->Category_id = (int)$this->cleanstring($input_category_id);
		//debug echo $this->Name;
    }	
	
	// PUBLIC function
	function select_category_id($input_select_category_id = "")
	{	
		$this->select_category_id = (int)$this->cleanstring($input_select_category_id);
		//debug echo $this->select_category_id;
    }

	// function
	function new_order_id($input_new_order_id = 0)
	{	
		if ($input_new_order_id <> 0)
		{
			$this->new_order_id = (int)$input_new_order_id;
		}
		//echo $this->new_order_id; //debug 
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


	//***********************************************************************************	
	//******************************** IMPORT FUNCTIONS *********************************

	// public function	
	//import CSV file data
	public function import_data_directory_items($debug=0)
	{
		$err_flag = 0;
		$err_msg = "";
			
		$import_message = "";
		
		//detect the character encoding of the incoming file
		//$encoding = mb_detect_encoding( $this->csvfile_tmp_name, "auto" );
		//echo "<hr />encoding:<br />$encoding";
		
		//*** insert from CSV ***
		$handle = fopen($this->csvfile_tmp_name, "r");
		$cnt_rows = 1;
		$this->order_id = $this->new_order_id;
		while ( ($data = fgetcsv($handle, 1000, ",") ) !== FALSE):			
			// miss out the first 1 rows (table header)
			if ($cnt_rows > 1):	
				$product_name = trim($data[0]);
				$company_profile = trim($data[12]);
				$this->product_name = $product_name;
			
				$update_test_flag = "yes";
				$page_msg = $this->test_product_input($update_test_flag, DEBUG_FCN_DISPLAY_FLAG);	
				if ($update_test_flag == "yes"):	
			
					$debug_string.= "<hr />LOOP<br />";
					
					//$this->variable = trim($data[0]);							
					//$debug_string.= "<br />variable:'".$this->variable."'";
					//$debug_string.= "&nbsp;variable:'".$this->variable."'";	
		
					//CSV fields
					//loop row column fields and create an insert item for the query
					$cnt_fields = 1;
					$cnt_data = 0;
					foreach ($data as $sField):				
						//specify table name
						$this->db_table = $this->DB_TABLE;
						$this->set_insert($this->DB_TABLE_FIELDS);
						
						//$sField	= mb_convert_encoding(html_entity_decode($sField),"ASCII","ISO-8859-1"); //UTF-8
						
						//add field to database and make each word lower case with first char capitalised
						//$this->add_insert_value(ucwords(strtolower($sField)));
						$this->add_insert_value( trim($sField) );
						$debug_string.= "<br />sField:'".$sField."'";
						
						$test = trim($data[$cnt_fields-1]);		
						$debug_string.= "<br />data[$cnt_data]:'".$test."'";	
				
						$cnt_fields++;
						$cnt_data++;
						$debug_string.= "<br />cnt_rows: $cnt_rows: cnt_fields'".$cnt_fields."'";
					endforeach;
				
					//Specific fields
					$this->add_insert_value($company_profile); //company_profile
					$this->add_insert_value($this->select_category_id); //category_id
					$this->add_insert_value($product_name); //product_name
					$this->add_insert_value($product_name); //slug
					$this->add_insert_value($product_name); //meta_title
					$this->add_insert_value($product_name); //meta_keywords
					$this->add_insert_value($product_name); //meta_description				
					$this->add_insert_value($this->order_id); //order_id
					
					//returns the row_id for the inserted item
					$insert_id = $this->insert_data();
					
					if ($insert_id):
						$this->records_added++;
						
						//*** INSERT DEFAULT VARIATION ***
						//standard - i.e. var 1,2,3 all blank			
						$this->db_table = $this->DB_TABLE_VARIATIONS;
						$this->set_insert($this->DB_TABLE_VARIATION_FIELDS);
						//add argument to insert values array
						$this->add_insert_value($insert_id);
						$this->add_insert_value($product_name);
						$this->add_insert_value('');
						$this->add_insert_value('');
						$this->add_insert_value('');
	//					$this->add_insert_value($this->default_price);
	//					$this->add_insert_value($this->default_rrp);
	//					$this->add_insert_value($this->stock_status);			
	//					$this->add_insert_value($this->visible);
	//					$this->add_insert_value($this->stock_level);
						$this->add_insert_value(0);
						//call method to create insert query - returns the row_id for the inserted item
						$insert_var_id = $this->insert_data();
						$debug_string.= "<br />insert_var_id:$insert_var_id";
			
						if ($insert_var_id):
							$fcn_msg .= " Product Set to have no variations."; 
						else:
						endif;		
						//*** INSERT DEFAULT VARIATION ***					
					else:
						$this->records_failed++;
					endif;		
					
					/*$num = count($data);
					for ($c=0; $c < $num; $c++) 
					{
						echo $data[$c] . "<br />\n";
					}*/	
					
					$this->order_id++;
				else:
					$this->records_failed++;
					$this->records_failed_input++;
				endif;
			endif;
			
			$cnt_rows++;			
		endwhile;
		fclose($handle);
		
		//debug code
		if ($debug==1)
		{
			echo "<br />import_data testvar:$debug_string<br />";
		}//end if ($debug==1)	
		
		if ($err_flag == 1):
			$err_msg = 'An error occured importing file data';
			return array($err_msg, $import_message); //on fail return error code 1 and error message
		else:			
			return array($err_msg, $import_message);		
		endif;
	}//import_data_directory_items	


	//---------- START function to test input of data for the CSV import ----------
	function test_import_data_input($debug=0)								 
	{								
		$err_flag = 0; 
		
		$debug_string = "";
		$fcn_msg = "";
		
		//**************************** TEST INPUT ************************************

		
		//test not blank
		if ($this->csvfile_name == ""):
			$fcn_msg.= "<li>Please choose a CSV to upload</li>";	
			$err_flag = 1;
		endif;		

		//check uploaded file size
		$check_filesize = 0;
		$check_filesize = $this->check_filesize();
		if ($check_filesize == 1):
			$fcn_msg.= "<li>CSV was blank</li>";	
			$err_flag = 1;	
		elseif ($check_filesize == 2):
			$fcn_msg.= "<li>CSV was bigger then the maximum allowed size ".$this->max_file_size_mb."MB.</li>";	
			$err_flag = 1;
		endif;	
		$debug_string.= "<br />this->object_csv['size']:'".$this->object_csv['size']."'";
									
		//check uploaded file type
		$check_filetype = 0;
		$check_filetype = $this->check_filetype();
		if ($check_filetype == 1):
			$fcn_msg.= "<li>File type ".$this->object_csv['type']." is not allowed.</li>";	
			$err_flag = 1;	
		elseif ($check_filetype == 2):
			$fcn_msg.= "<li>File type is blank</li>";	
			$err_flag = 1;
		elseif ($check_filetype == 3):
			$fcn_msg.= "<li>File extension ".$this->upload_file_extension." is not allowed.</li>";	
			$err_flag = 1;	
		endif;			
		$debug_string.= "<br />this->object_csv['type']:'".$this->object_csv['type']."'";
		$debug_string.= "<br />this->upload_file_extension:'".$this->upload_file_extension."'";

		//format testing
		if ($check_filesize == 0 && $check_filetype == 0):
			if ($this->csvfile_tmp_name != ""):
				$csv_error_count = 0;
				$csv_error_blank = 0;
				
				$cnt_rows = 1;
				//test CSV structure
				$handle = fopen($this->csvfile_tmp_name, "r");
				while ( ($data = fgetcsv($handle, 1000, ",") ) !== FALSE && $csv_error == 0): //stop looping once we have an invalid row						
					$num = count($data);
					//$debug_string.= "<br />".$data['0']." row num:'".$cnt_rows."' columns: $num this->set_import_rows:'".$this->set_import_rows."'<br />";
					//$debug_string.= "<hr /><br />gettype(num):'".gettype($num)."' gettype(set_import_rows):'".gettype($this->set_import_rows)."'<br />";
					
					if ($num != $this->set_import_rows):
						$csv_error = 1;
						$csv_error_count = 1;
					endif;	
						
					for ($c=0; $c < $num; $c++) :				
						$temp = $data[$c];
						/*if ($temp == "" && $c > 0): //test for blank cells
							$csv_error = 1;
							$csv_error_blank = 1;							
						endif;*/
					//	$debug_string.= " $c:'".$temp."'";
						//$debug_string.= " type:'".gettype($temp)."'";
					endfor;	
					
					$cnt_rows++;		
				endwhile;
				fclose($handle);	
				$debug_string.= "<br />csv_error:".$csv_error." csv_error_count:".$csv_error_count." csv_error_blank:".$csv_error_blank;
				
				if ($csv_error_count != 0):
					$fcn_msg.= "<li>CSV file is invalid. <br />Format should be $this->set_import_rows columns of data.</li>";		
					$err_flag = 1;		
				endif;
				
				if ($csv_error_blank != 0):
					$fcn_msg.= "<li>CSV file is invalid. <br />There should be no blank fields.</li>";		
					$err_flag = 1;		
				endif;
			else:
				$fcn_msg.= "<li>CSV file is invalid.</li>";	
				$err_flag = 1;			
			endif;
		endif;	
		//**************************** TEST INPUT ************************************	
		
		
		//complete the list with the ul tags
		if ($fcn_msg != "")
		{
			$fcn_msg = "<ul>".$fcn_msg."</ul>";		
		}	
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_import_data_input testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return array($err_flag, $fcn_msg);		
	}//end test_import_data_input	
	//---------- END function to test input of data for the CSV import ----------

	//******************************** IMPORT FUNCTIONS *********************************
	//***********************************************************************************	






	//***********************************************************************************	
	//****************************** TEST FILE FUNCTIONS ********************************
	
	function check_filesize()
	{
		$err_flag = 0;
				
		// check if no file or an empty file was uploaded
		if ( ( ($this->object_csv['error']) > 0) || ( ($this->object_csv['size']) == 0) )
		{
			$err_flag = 1;	//return error for uploaded file is blank or too large		
		}//next check if file is bigger then max_file_size
		else if ($this->object_csv['size'] > $this->max_file_size)
		{
			$err_flag = 2;	//return error for uploaded file is blank or too large
		}
		
		return $err_flag;
	}//check_filesize	

	function check_filetype()
	{		
		$err_flag = 0;
		
		// check if file is of correct type
		if ($this->object_csv['type'] <> "")
		{
			//set default type flag value 
			$bln_type_flag = 0;

			//iterate through array_file_type array to check uploaded file is within this list
			for ($i = 0; $i < count($this->array_file_type); $i++)
			{
				//echo "<br />this->array_file_type[$i]:'".$this->array_file_type[$i]."'";
				
				if ($this->array_file_type[$i] == $this->object_csv['type'])
				{
					//set flag to 1 if uploaded file type matches types in array
					$bln_type_flag = 1;
				}
			}
			//return error if flag is 0
			if ($bln_type_flag == 0)
			{
				$err_flag = 1;	//return error for uploaded file if type is wrong
			}
		}
		else
		{
			//return error if uploaded file type is blank
			$err_flag = 2;
		}

		//check the file extension
		// check file extension is .csv
		$filename = ($this->object_csv['name']);
		$this->upload_file_extension = explode ( '.', $filename );
		$this->upload_file_extension = strtolower($this->upload_file_extension[count($this->upload_file_extension)-1]);

		//set default type flag value 
		$bln_type_flag = 0;

		//iterate through array_file_type array to check uploaded file is within this list
		for ($i = 0; $i < count($this->array_file_extension); $i++)
		{
			if ($this->array_file_extension[$i] == $this->upload_file_extension)
			{
				//set flag to 1 if uploaded file type matches types in array
				$bln_type_flag = 1;
			}
		}
		//return error if flag is 0
		if ($bln_type_flag == 0)
		{
			$err_flag = 3;	//return error if uploaded file extension doesnt match
		}
		
		return $err_flag;
	}//check_filetype


	//---------- START function to test product input ----------
	//scaled down version of add product input test to use for CSV item input
	function test_product_input(&$update_test_flag, $debug=0)
	{					
		if ($update_test_flag == "")
			$update_test_flag = "yes"; 
		
		$fcn_msg = "";
		
		//test not blank
		if ($this->select_category_id == "" || $this->select_category_id == 0):
			$fcn_msg.= "* Please choose category<br />";	
			$update_test_flag = "no";
		endif;

		//test not blank
		if ($this->product_name == ""):
			$fcn_msg.= "* Please enter product name<br />";	
			$update_test_flag = "no";
		endif;
		
		/*//test not blank
		if ($this->company_name == ""):
			$fcn_msg.= "* Please enter company name<br />";	
			$update_test_flag = "no";
		endif;*/
	
		//test prod name doesnt exist in this category make sure Product name is not duplicated - by mistake or by refresh button!
		$this->set_select("SELECT product_name");
		$this->set_from($this->DB_TABLE_PRODUCTS);	
		//NEED to convert the name as it would be stored in the DB to test for duplicates correctly
		$this->set_where("product_name = '".$this->cleanstring($this->product_name)."'");
		$this->set_where("category_id = '".$this->select_category_id."'");
			if ($this->action == "edit")
				$this->set_where("product_id != '".$this->product_id."'");
		$result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated
		if ($count > 0):
			//same as edit places into the form on failure
			$display_product_name = strip_tags(htmlspecialchars($this->product_name), ENT_QUOTES); //cleanstring but without the escape_string DB bit
			$display_product_name = $this->htmlsafe($display_product_name); // $p_category_name $c_directory_categories->category_name				
		
			$fcn_msg.= "There is already a Product with the name '".$display_product_name."' in this category.<br />";	
			$fcn_msg.= "Please try again with a different Product Name or specify a different category.<br />";
			$update_test_flag = "no";			
			$this->submit = "";
		endif;	
	
		//test not blank
		/*if ($this->default_code == ""):
			$fcn_msg.= "* Please enter default barcode<br />";	
			$update_test_flag = "no";
		endif;*/
		
		//test number
		/*if (!is_numeric($this->default_price))
		{
			$fcn_msg.= "* Please enter default price as a number<br />";	
			$update_test_flag = "no";
		}		*/			
			
		//test number
		/*if ($this->default_rrp != "" && !is_numeric($this->default_rrp))
		{
			$fcn_msg.= "* Please enter default rrp as a number<br />";	
			$update_test_flag = "no";
		}	*/			
					
		//message
		if ($update_test_flag == "no"): 
			$fcn_msg = "Error Occured:<br />".$fcn_msg;
			$this->submit = "";
		endif;
		
		$debug_string = $fcn_msg;
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_product_input testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//end test_product_input	
	//---------- END function to test product input   ----------

	//****************************** TEST FILE FUNCTIONS ********************************
	//***********************************************************************************	



}//class ImportDatabase
?>