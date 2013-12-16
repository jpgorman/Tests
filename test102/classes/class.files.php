<?php

class Files extends db_object 
{ 
	/////////////////////////////////////////////////
	// PRIVATE VARIABLES
	/////////////////////////////////////////////////
	/* EMPLOYER TABLE */
	private $DB_TABLE_FILE_FIELDS = "(name,data,filename,filesize,filetype,`default`,order_id,last_modified)";	

	//declare input variables
	var $page_id = 0;
	
	//CONSTRUCTOR function
	function __construct(){
		//can turn off the server validation if replacing with Ajax alternative 
		$this->server_validation = 1;  //1=on 0=off
	}//constructor


	// PUBLIC function
	function page_id($input_page_id = 0)
	{	
		$this->page_id = trim($input_page_id);
		//debug echo $this->page_id;
    }

	
	//************************************************************************************
	//********************************** EMPLOYERS ***************************************
	
	//---------- START function to add file ----------
	public function add_file($action, &$add_file_id, 
					  $name, $data, $filename, $filesize, $filetype, $set_default, 	  
					  &$submit, &$success_flag, $debug=0, $admin=0)						  						  				
	{	
		$debug_string = "";
		$fcn_msg = "";
			
		//pre set any variables for add
		$mailing_list = 1; //default
		
		$file_count = $this->get_file_count(0);
		//if no FILES yet then this MUST be the default
		if ($file_count == 0)
		{
			$set_default = 1;
		}		
			
		//Insert the record
		$sql_add = "INSERT INTO ".SITE_DB_PREFIX."files ".$this->DB_TABLE_FILE_FIELDS." VALUES (" ; 
		$sql_add.= "'" . $name . "', ";
		$sql_add.= "'" . $data . "', ";
		$sql_add.= "'" . $filename . "', ";
		$sql_add.= "'" . $filesize . "', ";
		$sql_add.= "'" . $filetype . "', ";
		$sql_add.= "'0', "; //no defaults on this
		//$sql_add.= "'" . $set_default . "', ";
			//order id
			$order_sql = "SELECT * FROM ".SITE_DB_PREFIX."files WHERE order_id is not NULL";
			$order_sql.= " AND order_id!='' ORDER by order_id";
			$order_result = mysql_query($order_sql);
			$count = mysql_num_rows($order_result);
		$sql_add.= "'" . ($count + 1) . "', ";
		$sql_add.= "now()";
		$sql_add.= ")";	

		$debug_string.= "<br />sql_add:$sql_add";
		$result_add = mysql_query($sql_add);

		if (!$result_add)
			$fcn_msg = "Could not add File.<br />";
		else
		{			
			$success_flag = 1; //success
			
			//new file id
			$add_file_id = mysql_insert_id();	
		
			if ($set_default == 1)
				$this->set_default_file($add_file_id, 0);	
				
			$fcn_msg = "File has been Added to the Database.<br />";
		}//end result_add	

		//debug code
		if ($debug==1)
		{
			echo "<br />add_file testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//add_file
	//---------- END function to add file ----------	


	//---------- START function to update file ----------
	public function update_file($action, $file_id,
					   $name, $data, $filename, $filesize, $filetype, $set_default,
					   &$submit, &$success_flag, $debug=0, $admin=0)													 						 							 
	{
		$update_default = 0;
		
		//UPDATE
		$sql_update = "UPDATE ".SITE_DB_PREFIX."files SET"; 				
		//$sql_update.= " filename = '" . htmlspecialchars($_FILES['form_data']['name'], ENT_QUOTES) . "'";	
		$sql_update.= " name = '" . $name . "'";	
		//update file if one was uploaded
		if ($data != "")
		{
			$sql_update.= ", data = '" . $data . "'";
			$sql_update.= ", filename = '" . $filename . "'";
			$sql_update.= ", filesize = '" . $filesize . "'";
			$sql_update.= ", filetype = '" . $filetype . "'";
		}//end if
		//update default if neeeded
		$sql_update.= " WHERE id = '$file_id'";
		$debug_string.= "<br />sql_update:$sql_update<br />";
		$result = mysql_query($sql_update);
	
		if (!$result)
		{
			$fcn_msg = "Could not update File.";
		}
		else
		{
			//if ($set_default == 1)
			//	$this->set_default_file($file_id, 0);

			$success_flag = 1; //success
			$fcn_msg = "File Details were succesfully updated.<br />";
			
			//UPDATE ORDER STUFF
			$new_order_id = $_POST['order_id'];
			$change_order = "yes";
			//$testdisplayvar "nu = " . $new_order_id . ", change_order = $change_order , parent_id = " . $subrow["parent_id"];
			$file_id = $edit_id;				
		}//end else
	
		//debug code
		if ($debug==1)
		{
			echo "<br />update_file testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//update_file
	//---------- END function to update file ----------


	//---------- START function to delete file ----------
	public function delete_file($action, $file_id,
					   &$deleted_order_id,
					   &$submit, &$success_flag, $debug=0, $admin=0)	
	{
		$debug_string = "";
		$fcn_msg = "";	
	
		if ($update_test_flag == "")
			$update_test_flag = "yes"; 	
	
/*		//test global file delete variable allows file deletion
		if (ALLOW_DELETE_FILE==0)
		{
			$fcn_msg="Deleting Files is not allowed - Cannot Delete.";
			return $fcn_msg;
		}//end if (ALLOW_DELETE_FILE==0)*/

		//TEST we are not trying to delete the last FILE - which is not allowed
		
		$file_count = $this->get_file_count(0);
		if ($file_count < 2)
		{
			$fcn_msg = "Cannot Delete. You must keep at least one FILE on your account.";
			$submit = "";
			$update_test_flag = "no"; 	
		}

		if ($update_test_flag == "yes")
		{
			//test record was valid
			$sql = "SELECT id, name, filename, `default`, order_id FROM ".SITE_DB_PREFIX."files";
			$sql.= " WHERE id = '$file_id'";				
			$debug_string.= "<br />sql:$sql";
			$result = mysql_query($sql);
			if (!$result || (mysql_num_rows($result)==0))
			{
				$fcn_msg = "File does not exist - Cannot Delete.";
				$submit = "";
			}
			else
			{	
				$row = mysql_fetch_assoc($result);
				
				$db_name = $row["name"];
				$db_filename = $row["filename"];
				$db_default = $row["default"];
				$deleted_order_id = $row["order_id"];
				
				if ($db_default == 1)
				{
					$fcn_msg.= "File: '$db_name:$db_filename' is your default FILE.<br />You cannot delete your default FILE.<br /><br />";
					$submit = "";					
				}
				else
				{
					//delete the record
					$sql_delete = "DELETE FROM ".SITE_DB_PREFIX."files WHERE id ='$file_id'";
					$result_del = mysql_query($sql_delete);				
					if (!$result_del)
					{
						$fcn_msg.= "File: '$db_name:$db_filename' COULD NOT be Deleted from the Database.<br />";
						$submit = "";		
					}
					else
					{
						$success_flag = 1; //success
						$fcn_msg.= "File: '$db_name:$db_filename' has been Deleted from the Database.<br />";
					}//end if ($resultdel)
				}//end if ($db_default
			}//end if (!$result
		}//end if ($update_test_flag == "no")

		//debug code
		if ($debug==1)
		{
			echo "<br />delete_file testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;
	}//end delete_file
	//---------- END function to delete file ----------

			  					  	
	//---------- START function to test file input ----------
	public function test_file_input($action, $file_id,
						  	 $name, $data, $filename, $filesize, $filetype, $set_default,
							 $max_files_test, $current_file_count, $max_files_allowed, $allowed_file_types, $max_size = 1048576,
							 $debug=0, &$update_test_flag, $admin=0)								 
	{	
	
		//echo $filename;
		
		$test_filename_extension = strtolower(substr(strrchr($filename,"."),1)); 
		//echo $test_filename;
		
		$allowed_file_extensions = array('pdf','ppt','doc','docx','txt');

		
		//if vaidation set to off just return SUCCESS
		if ( $this->server_validation == 0 )
		{
			$update_test_flag = "yes"; 
			return;
		}	
		
		if ($update_test_flag == "")
			$update_test_flag = "yes"; 
		
		$debug_string = "";
		$fcn_msg = "";
		
		//**************************** TEST INPUT ************************************

		//max number of files test
		if ( $max_files_test == 1 && ($current_file_count >= $max_files_allowed) )
		{
			$fcn_msg.= "<li>You have reached the maximum allowed number of $max_files_allowed files.</li>";
			$update_test_flag = "no";
		}//end if

		//test file type
		if ($action == "add")
		{
			//check mimetype
			if ( !in_array($filetype, $allowed_file_types) ) 
			{
				
				$update_test_flag = "no";				
				
				//check file extension
				if ( !in_array($test_filename_extension, $allowed_file_extensions) ) 
				{
					$fcn_msg.= "<li>Invalid file type.</li>";
					$fcn_msg.= "<li>Invalid file extension.</li>";
					$update_test_flag = "no";
				}//if it is a valid extension then allow the file.
				else{
					$update_test_flag = "yes"; 
				}
			}//end if
		}//end if
		
		//test file size (bytes)
		if ( ($filesize > $max_size || $filesize == 0) && $action == "add")
		{		
			//filesize of temp uploaded file - kick out for files greater than max (1 meg=1048576 bytes) or 0kb (error uploading)
			$fcn_msg.= "<li>Invalid file size (File Size = ".$this->file_size_info($filesize);
			$fcn_msg.= ", Max File Size = ".$this->file_size_info($max_size).").</li>";
			$update_test_flag = "no";
		}//end if

		//test file (name) doesnt already exist
		$sql_test = "SELECT id FROM ".SITE_DB_PREFIX."files";
		$sql_test.= " WHERE filename='". $filename ."'";
		if ($action != "add")
			$sql_test.= " AND id <> '$file_id'";		
		$debug_string.= "<br />sql_test:$sql_test";
		$result = mysql_query($sql_test);
		$count_test = mysql_num_rows($result);
		if ($count_test > 0)
		{
			$fcn_msg.= "<li>File already exists.</li>";
			$update_test_flag = "no";
		}//end if

		if ($action == "edityes")
		{
			$sql_select = "SELECT filename FROM ".SITE_DB_PREFIX."files";
			$sql_select.= " WHERE id='$edit_id'";
			$result_select = mysql_query($sql_select);
			
			//if no matches found from query
			if (!$result_select || (mysql_num_rows($result_select) == 0))
			{
				$fcn_msg.= "<li>File does not exist.</li>";
				$update_test_flag = "no";	
			}//end if			
		}//end if

		if ($name == "")
		{
			$fcn_msg.= "<li>Name is blank</li>";	
			$update_test_flag = "no";
		}//end if

		//**************************** TEST INPUT ************************************	
		
		
		//complete the list with the ul tags
		if ($fcn_msg != "")
		{
			$fcn_msg = "<ul>".$fcn_msg."</ul>";		
		}	
					
		//message
//		if ($update_test_flag == "no") 
//		{
//			//$fcn_msg = $fcn_msg;		
//		}
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_file_input testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//end test_file_input	
	//---------- END function to test file input ----------



	// public function	
	//*** retrieve a list of all files ***
	public function select_file_list($order_by="", $assoc=0, $type="")
	{
		$this->set_select();
		$this->set_from("files");
			
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
			$err_msg = 'No Files could be found';
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
	public function select_file_list_query($select="", $from="", $where="", $order_by="")
	{
		//SELECT * FROM st_files f, st_assoc_file_page a WHERE a.file_id = f.id AND a.page_id=26 ORDER BY name
		
		$select = "";
		$from = "files f, ".$this->DB_PREFIX."assoc_file_page a";
		$where = "a.file_id = f.id AND a.page_id = '".$this->page_id."'";
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
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	
	}//select_file_list
	//*** retrieve a list of all files ***




	//*******************************************************************************************
	//****************************---------- FILE FUNCTIONS ----------***************************
	//*******************************************************************************************		

	//---------- START function to convert bytes to readable format ----------
	public function file_size_info($fs_bytes, $round = 0, $debug=0)
	{
		$fcn_msg = "";
		$debug_string = "";	
	
		//Size must be bytes! 
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'); 
		for ($i=0; $fs_bytes > 1024 && $i < count($sizes) - 1; $i++) $fs_bytes /= 1024; 
					
		//debug code
		if ($debug==1)
		{
			echo "<div>file_size_info testvar:$debug_string</div>";
		}//end if ($debug==1)			
		
		return round($fs_bytes,$round).$sizes[$i]; 	
	}//file_size_info
	//---------- END function to convert bytes to readable format ----------	
	
	//*******************************************************************************************
	//****************************---------- FILE FUNCTIONS ----------***************************
	//*******************************************************************************************



	//*********************************** OTHER *****************************************
	
	//---------- START function FILE COUNT  ----------
	function get_file_count($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	
		
		$sql_test = "SELECT id FROM ".SITE_DB_PREFIX."files";			
		$debug_string.= "<br />sql_test:$sql_test";
		$result_test = mysql_query($sql_test);
		if ($result_test)
		{
			$file_count = mysql_num_rows($result_test);	
		}	
		
		//debug code
		if ($debug==1)
		{
			echo "<br />get_file_count testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $file_count;
	}//end get_file_count
	//---------- END function FILE COUNT  ----------		


	//---------- START function SET DEFAULT FILE ----------
	private function set_default_file($file_id, $debug=0)
	{
		$set_default_file = 0;
		$debug_string = "";
		$fcn_msg = "";	
		
		if ($file_id > 0)
		{		
			$sql = "UPDATE ".SITE_DB_PREFIX."files SET"; 
			$sql.= " `default` = '0'";
			$debug_string.= "<br />sql:$sql<br />";
			$result = mysql_query($sql);
			if ($result)
			{
				$sql2 = "UPDATE ".SITE_DB_PREFIX."files SET"; 
				$sql2.= " `default` = '1'";
				$sql2.= " WHERE id = '$file_id'";
				$debug_string.= "<br />sql2:$sql2<br />";
				$result2 = mysql_query($sql2);
				if ($result2)
					$set_default_file = 1;
			}//end if
		}//end if test ids
		
		//debug code
		if ($debug==1)
		{
			echo "<br />set_default_file testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $set_default_file;
	}//end set_default_file
	//---------- END function SET DEFAULT FILE ----------	

	//*********************************** OTHER *****************************************
	

}//class Files

?>