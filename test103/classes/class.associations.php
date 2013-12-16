<?php
class associations extends db_object 
{
	
//declare database constants
private $DB_ASSOC_TABLE = "assoc_file_page";

//declare input variables
var $action = "";
var $submit = "";
var $add_success_flag = 0;
var $update_success_flag = 0;
var $delete_success_flag = 0;
var $update_test_flag = "yes";	
var $messages = "";	
var $file_id = 0;
var $page_id = 0;
var $add_assoc_id = 0;
var $has_assoc = 0;


	function __construct(){
	}


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
	function file_id($input_file_id = 0)
	{	
		$this->file_id = trim($input_file_id);
		//debug echo $this->file_id;
    }

	// PUBLIC function
	function page_id($input_page_id = 0)
	{	
		$this->page_id = trim($input_page_id);
		//debug echo $this->page_id;
    }

 
 	// PUBLIC function	   
	//set an association between file and page
	public function set_assoc_file_page()
	{
		$debug_string = "";
		$fcn_msg = "";

		if ( $this->file_id > 0 && $this->page_id > 0 ):
			$this->set_select();
			$this->set_from($this->DB_ASSOC_TABLE);
			$this->set_where("`file_id` = ".$this->file_id);
			$this->set_where("`page_id` = ".$this->page_id);
			
			$result = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if ($count > 0):
				$this->messages = "Association is already set.";
			else:
				//create user association
				$this->db_table = $this->DB_ASSOC_TABLE;
				$this->set_insert("page_id, file_id");
				$this->add_insert_value($this->page_id);
				$this->add_insert_value($this->file_id);
				$insert_id = $this->insert_data();			
					
				if ($insert_id):
					$this->add_assoc_id = mysql_insert_id();
					$this->add_success_flag = 1;
					
					$this->messages = "Association has been set.";
				else:
					$this->messages = "Association could not be set.";
				endif;
			endif;	
		else:
			$this->messages = "Association could not be set.";
		endif;
		
		//debug code
		if ($debug==1)
		{
			echo "<br />set_assoc_file_page testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//set_assoc_file_page
	

	// PUBLIC function	
	//remove association between file and page
	public function del_assoc_file_page()
	{
		$debug_string = "";
		$fcn_msg = "";

		if ( $this->file_id > 0 && $this->page_id > 0 ):
			$this->set_select();
			$this->set_from($this->DB_ASSOC_TABLE);
			$this->set_where("`file_id` = ".$this->file_id);
			$this->set_where("`page_id` = ".$this->page_id);
			
			$result = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if ($count > 0):
				$sql_del = "DELETE FROM ".SITE_DB_PREFIX.$this->DB_ASSOC_TABLE;
				$sql_del.= " WHERE file_id='".$this->file_id."'";
				$sql_del.= " AND page_id='".$this->page_id."'";
				$debug_string.= "<br />sql_del:$sql_del<br />";
				$result_del = mysql_query($sql_del);
				
				if ($result_del && mysql_affected_rows() != 0):
					$this->delete_success_flag = 1;
					$this->messages = "Association has been deleted.";
				else:
					$this->messages = "Association could not be deleted.";
				endif;					
			else:
				$this->messages = "Association does not exist.";
			endif;	
		else:
			$this->messages = "Association could not be deleted.";
		endif;
		
		//debug code
		if ($debug==1)
		{
			echo "<br />del_assoc_file_page testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//del_assoc_file_page


	// PUBLIC function	
	//test if there is an association between file and page
	public function test_assoc_file_page()
	{
		if ( (!empty($this->file_id)) && (!empty($this->page_id)) )
		{
			$this->set_select();
			$this->set_from($this->DB_ASSOC_TABLE);
			$this->set_where("`file_id` = ".$this->file_id);
			$this->set_where("`page_id` = ".$this->page_id);
			
			$result = $this->get_data();
			$count = $this->numrows;//returns the total number of rows generated
			if ($count > 0):
				$this->has_assoc = 1;
				$this->messages = "Association exists.";
			else:
				$this->has_assoc = 0;
				$this->messages = "Association does not exist.";
			endif;
		}
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_assoc_file_page testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;				
	}//test_assoc_file_page



}//end class
?>