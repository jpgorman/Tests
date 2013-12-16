<?php
class directory_categories extends db_object 
{ 
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////
	var $test_flag = 0;
	
	//declare database constants
	var $DB_TABLE_PRODUCTS = "directory_products";
	
	var $DB_TABLE_CATEGORIES = "directory_categories";
	var $DB_TABLE_CATEGORY_FIELDS = "category_name, category_desc, category_url, slug, meta_title, meta_keywords, meta_description, parent_id, visible, order_id";

	//declare input variables
	var $action = "";
	var $category_id = 0;
	var $add_category_id = 0;
	var $update_category_id = 0;
	var $category_name = "";
	var $category_desc = "";
	var $category_url = "";
	var $slug = "";
	var $meta_title = "";
	var $meta_keywords = "";
	var $meta_description = "";
	var $parent_id = 0;
	var $select_parent = 0;
	var $show_cat = 1;
	var $order_id = 0;
	var $nu_order_id = 0;
	var $page_number = 1;
	var $current_page = null;
	var $records_per_page = 10;
	var $order_by = "";	
	
	var $submit = "";
	var $add_success_flag = 0;
	var $update_success_flag = 0;
	//public $debug = 0;


	
	//Constructor function
	function __construct(){
		$this->sub_levels = 1;  //1=on 0=off
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

	// PUBLIC function
	function page_number($input_pg_num = "")
	{	
		$this->page_number = (int)trim($input_pg_num);
		//debug echo $this->Name;
    }
	
	// PUBLIC function
	function records_per_page($input_records_per_page = null)
	{	
		$this->records_per_page = (int)$this->cleanstring($input_records_per_page);
		//debug echo $this->records_per_page;
    }

	// PUBLIC function
	function current_page($input_current_page = "")
	{
		$this->current_page = $this->cleanstring(trim($input_current_page));
		//debug echo $this->Name;
    }

	// function
	function category_id($input_category_id = NULL)
	{
		if($input_category_id <> NULL){
       		$this->category_id = $this->cleanstring(trim($input_category_id));
		}
		//echo $this->category_id; //debug 
    }

	// function
	function category_name($input_category_name = NULL)
	{
		if($input_category_name <> NULL){
       		//$this->category_name = $this->cleanstring(trim($input_category_name));
			$this->category_name = htmlspecialchars(trim($input_category_name), ENT_QUOTES);
			$this->slug = $this->make_url_safe($this->cleanstring($input_category_name));
			//$this->category_name = trim($input_category_name);		
		}
		//echo $this->category_name; //debug 
    }

	// function
	function category_desc($input_category_desc = NULL)
	{
		$this->category_desc = htmlspecialchars(strip_tags($input_category_desc,"<a><b><strong><i><u><em><embed><p><div><span><strike><sub><sup><img><table><tbody><tfoot><thead><tr><td><th><ul><ol><li><blockquote><br /><h1><h2><h3><h4><hr><textarea><input><select><option><form>"), ENT_QUOTES);
		//echo $this->category_desc; //debug 		
    }

	// function
	function category_url($input_category_url = NULL)
	{
		if($input_category_url <> NULL){
			$this->category_url = htmlspecialchars(trim($input_category_url), ENT_QUOTES);	
		}
		//echo $this->category_url; //debug  
    }
	
	// function
	function meta_title($input_meta_title = NULL)
	{
		if($input_meta_title <> NULL){
			$this->meta_title = htmlspecialchars(trim($input_meta_title), ENT_QUOTES);	
		}
		//echo $this->meta_title; //debug  
    }
	
	// function
	function meta_keywords($input_meta_keywords = NULL)
	{
		if($input_meta_keywords <> NULL){
			$this->meta_keywords = htmlspecialchars(trim($input_meta_keywords), ENT_QUOTES);	
		}
		//echo $this->meta_keywords; //debug  
    }
	
	// function
	function meta_description($input_meta_description = NULL)
	{
		if($input_meta_description <> NULL){
			$this->meta_description = htmlspecialchars(trim($input_meta_description), ENT_QUOTES);	
		}
		//echo $this->meta_description; //debug  
    }

	// function
	function parent_id($input_parent_id = 0)
	{	
		if($input_parent_id <> 0){
			$this->parent_id = (int)$input_parent_id;
		}
		//echo $this->parent_id; //debug 
    }	
	
	// function
	function select_parent($input_select_parent = 0)
	{	
		if($input_select_parent <> 0){
			$this->select_parent = (int)$input_select_parent;
		}
		//echo $this->select_parent; //debug 
    }
	
	// function
	function show_cat($input_show_cat = NULL)
	{
		if($input_show_cat <> NULL){
			$this->show_cat = htmlspecialchars(trim($input_show_cat), ENT_QUOTES);	
		}
		//echo $this->show_cat; //debug  
    }
	
	// function
	function order_id($input_order_id = 0)
	{	
		if($input_order_id <> 0){
			$this->order_id = (int)$input_order_id;
		}
		//echo $this->order_id; //debug 
    }	
	
	// function
	function nu_order_id($input_nu_order_id = 0)
	{	
		if($input_nu_order_id <> 0){
			$this->nu_order_id = (int)$input_nu_order_id;
		}
		//echo $this->nu_order_id; //debug 
    }			


	// function
	function set_list_page($input_page_num = 0)
	{	
		if($input_page_num <> 0){
			$this->set_list_page = (int)$input_page_num;
		}
		//debug echo $this->set_list_page;
    }
	
	// function
	function set_list_rows($input_page_rows = 0)
	{	
		if($input_page_rows <> 0){
			$this->set_list_rows = (int)$input_page_rows;
		}
		//debug echo $this->set_list_rows;
    }
	

	//---------- START function to add category ----------
	function add_category($debug=0)
	{		
		$debug_string = "";
		$fcn_msg = "";
		
		//Only allow sub-levels if turned on
		if ($this->sub_levels==0 && $this->select_parent!=0)
		{
			$fcn_msg = "Sorry. Sub-levels are not allowed.";
			$this->submit = "";
			return $fcn_msg;
		}		

		//get new order id for new record
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");		
		$this->set_where("order_id IS NOT NULL");
		$this->set_where("parent_id = '".$this->select_parent."'");
		$order_result = $this->get_data();
		
		$order_count = $this->numrows; //returns the total number of rows generated
	
		$this->order_id = ($order_count + 1); //set order_id for new record based on total count
		
	
		//Insert the record
		$this->db_table = $this->DB_TABLE_CATEGORIES;
		$this->set_insert($this->DB_TABLE_CATEGORY_FIELDS);
		//add argument to insert values array
		$this->add_insert_value($this->category_name);
		$this->add_insert_value($this->category_desc, "HTML");
		$this->add_insert_value($this->category_url);
		$this->add_insert_value($this->slug);
		$this->add_insert_value($this->meta_title);
		$this->add_insert_value($this->meta_keywords);
		$this->add_insert_value($this->meta_description);
		$this->add_insert_value($this->select_parent);
		$this->add_insert_value($this->show_cat);
		$this->add_insert_value($this->order_id);
		//call method to create insert query - returns the row_id for the inserted item
		$insert_id = $this->insert_data();

		if ($insert_id):
			$this->add_category_id = mysql_insert_id();
			$this->add_success_flag = 1;			
			$fcn_msg = "Category has been Added to the Database.<br />";

			//update order_ids
			$this->category_id = mysql_insert_id();
			if($order_count > 0){
				$this->update_category_order_ids();
			}
			
			$debug_string.= "<br />mysql_insert_id():".mysql_insert_id();
		else:
			$fcn_msg = "Could not add Category.<br />";
		endif;
	
		//debug code
		if ($debug==1)
		{
			echo "<div>add_category testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//add_category
	//---------- END function to add category ----------
	
	
	//---------- START function to update category ----------
	function update_category($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";

		//Only allow sub-levels if turned on
		if ($this->sub_levels == 0 && $this->select_parent != 0)
		{
			$fcn_msg = "Sorry. Sub-levels are not allowed.";
			$this->submit = "";
			return $fcn_msg;
		}
		
		//cant make it a sub-level of itself!
		if ($this->select_parent == $this->category_id)
		{
			$fcn_msg = "Sorry. Cannot set Parent to be same as itself.";
			$this->submit = "";
			return $fcn_msg;			
		}
		
		//get category info
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");	
		$this->set_where("category_id = '".$this->category_id."'");
		$result_cat = $this->get_data();
		$row_cat = mysql_fetch_object($result_cat);
		$this->order_id = ($order_count + 1); //set order_id for new record based on total count
		$old_order_id = $row_cat->order_id;
		$old_parent_id = $row_cat->parent_id;		
		
		//update the category
		$reorder = 1;

		// check if category is being moved to a new parent
		// have to do it here as can only build one query at a time
		$new_parent = 0;
		if ($this->select_parent >= 0 && $this->select_parent != $old_parent_id):
			$new_parent = 1;

			$this->set_select();
			$this->set_from("directory_categories");
			$this->set_where("discontinued != 1");	
			$this->set_where("order_id IS NOT NULL");
			$this->set_where("parent_id = '".$this->select_parent."'");
			$order_result = $this->get_data();
			$order_count = $this->numrows; //returns the total number of rows generated
			$this->order_id = ($order_count + 1); //set order_id for new record based on total count
		
			//dont reorder as its moved to a new parent and goes at the end
			$reorder = 0;	
			
			//need to update the orders of the old category after the update has gone through below			
			$update_old_orders = "yes";
		endif;

		//build update query
		$this->db_table = $this->DB_TABLE_CATEGORIES;
		$this->set_update();
		$this->add_update_value("category_name",$this->category_name);	
		$this->add_update_value("category_desc",$this->category_desc, "HTML");
		$this->add_update_value("category_url",$this->category_url);
		$this->add_update_value("slug",$this->slug);
		$this->add_update_value("meta_title",$this->meta_title);
		$this->add_update_value("meta_keywords",$this->meta_keywords);
		$this->add_update_value("meta_description",$this->meta_description);
		$this->add_update_value("visible",$this->show_cat);
		
		if ($new_parent == 1):
			$this->add_update_value("parent_id",$this->select_parent);	
			//update order id
			$this->add_update_value("order_id",$this->order_id);			
		endif;
		
		$this->set_where("category_id = '".$this->category_id."'");
		
		//call method to create update query - returns the row_id for the item
		$update_id = $this->update_data();
		$debug_string.= "<br />update_id:$update_id";

		if (isset($update_id)):	
			$this->update_category_id = $update_id;
			$this->update_success_flag = 1;			
			$fcn_msg = "Category has been Updated.<br />";

			if ($update_old_orders == "yes"):
				//update order of parent cat left behind
				//SQL UPDATE METHOD CALLS
				$this->db_table = $this->DB_TABLE_CATEGORIES;
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $old_order_id");
				$this->set_where("parent_id = $old_parent_id");	
				$result_order = $this->update_data(0);					
			endif;				

			//set new order_id for current category - but not if we moved to a new category cos it must go at the end
			if ($reorder == 1):			
				//update order_id
				$this->update_category_order_ids();
			endif;				
		else:	
			$fcn_msg = "Error: could not update category.<br />";
			$this->submit = "";		
		endif;
		
		//debug code
		if ($debug==1)
		{
			echo "<div>update_category testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//update_category
	//---------- END function to update category ----------
	
	
	//---------- START function to delete category ----------
	function delete_category($category_id, $restore_code, $debug=0)
	{
		$delete_category = 0;
		$debug_string = "";	

		//get category
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");
		$this->set_where("category_id = '$category_id'");
		$result_order = $this->get_data();
		$count_order = $this->numrows; //returns the total number of rows generated
		$row_order = mysql_fetch_object($result_order);
		$order_id = $row_order->order_id;
		$parent_id = $row_order->parent_id;

		//if category not found then cant delete e.g. they click refresh and its already deleted
		if ( !$result_order || $count_order == 0):
			return 0;
		endif;
			
		//get subs
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");	
		$this->set_where("parent_id = '$category_id'");
		$result_sub = $this->get_data();
		$count_sub = $this->numrows; //returns the total number of rows generated
	
		$sub_success = 1;
		
		if ($result_sub && $count_sub > 0):
			while ($row_sub = mysql_fetch_object($result_sub)):
				if ( !$this->delete_category($row_sub->category_id, $restore_code) ):
					$sub_success = 0;
					break;					
				endif;	
			endwhile;
		endif;
		
/*		//OPTION 1
		//DELETE CATEGORY - Actually remove category and all sub-categories
		$sql_del = "DELETE FROM ".SITE_DB_PREFIX."directory_categories WHERE category_id = $category_id";
		$debug_string.= "<br />sql_del:$sql_del";
		if (!$del_res = mysql_query($sql_del) || mysql_num_rows($del_res) == 0):		
			$delete_category = 0;
		else:
		*/
	
		//OPTION 2
		//DELETE CATEGORY  - OR set category and all sub-categories to discontinued as for products
		//SQL UPDATE METHOD CALLS
		$this->db_table = $this->DB_TABLE_CATEGORIES;
		$this->set_update();
		$this->add_update_value("discontinued", "1");
		$this->add_update_value("restore_code", $restore_code);
		$this->add_update_value("order_id", "0"); //remove the order id when deleting - set to 0
		//$this->add_update_value("visible", "0"); //dont set as can restore to its previous value
		$this->set_where("category_id = $category_id");	
		$this->set_where("discontinued != 1");		
		$update_id = $this->update_data(0);	
		if (!isset($update_id)):
			$delete_category = 0;				
		else:
		
		
			//UPDATE CATEGORY ORDERS
			$this->db_table = $this->DB_TABLE_CATEGORIES;
			$this->set_update();
			$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
			$this->set_where("order_id > ".$order_id);
			$this->set_where("parent_id = '".$parent_id."'");
			$result_update = $this->update_data(0);	
			//echo "parent_id:".$category_id;

			//DELETE ALL PRODUCTS FOR THIS CATEGORY
			//REMEMBER THAT DELETE JUST SETS THEM TO DISCONTINUED
			//but still we dont want the products available once the category has been removed

			//instantiate catalogue object
			if($this->catalogue = new Catalogue());

			$this->set_select();
			$this->set_from($this->DB_TABLE_PRODUCTS);
			$this->set_where("category_id = '$category_id'");
			$this->set_where("discontinued != 1");	
			$result_prod = $this->get_data();
			$count_prod = $this->numrows; //returns the total number of rows generated
	
			if ($result_prod && $count_prod > 0):
				while ($row_prod = mysql_fetch_object($result_prod)):
					$this->catalogue->product_id($row_prod->product_id);
					$this->catalogue->delete_product($restore_code, DEBUG_FCN_DISPLAY_FLAG);
				endwhile;
			endif;
			//DELETE ALL PRODUCTS FOR THIS CATEGORY
			
			$delete_category = 1;
		endif;
		
		//debug code
		if ($debug==1)
		{
			echo "<div>delete_category testvar:$debug_string</div>";
		}//end if ($debug==1)
		return $delete_category;			
	}//end delete_category
	//---------- END function to delete category ----------

	
	
	//---------- START function to test category input ----------
	function test_category_input(&$update_test_flag, $debug=0)
	{						
		if ($update_test_flag == "")
			$update_test_flag = "yes"; 
		
		$fcn_msg = "";
		
		//test not blank
		if ($this->category_name == ""):
			$fcn_msg = "* Name is blank<br />";	
			$update_test_flag = "no";
		endif;

		//test name exists
		$this->set_select("SELECT category_name");
		$this->set_from("directory_categories c");	
		//$this->set_where("category_name = '".$this->category_name."'");
		//NEED to convert the name as it would be stored in the DB to test for duplicates correctly
		$this->set_where("category_name = '".$this->cleanstring($this->category_name)."'");
		$this->set_where("parent_id = '".$this->select_parent."'");
		if ($this->action == "edit")
			$this->set_where("category_id != '".$this->category_id."'");
		$result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated
		if ($count > 0):
			//same as edit places into the form on failure
			$display_category_name = strip_tags(htmlspecialchars($this->category_name), ENT_QUOTES); //cleanstring but without the escape_string DB bit
			$display_category_name = $this->htmlsafe($display_category_name); // $p_category_name $c_directory_categories->category_name			
			
			$fcn_msg.= "There is already a Category with the name '".$display_category_name."'.<br />";	
			$fcn_msg.= "Please enter a different Category name or select a different parent Category.<br />";
			$update_test_flag = "no";			
		endif;
			
		//test Description not blank
/*		if ($this->category_desc == "")
		{
			$fcn_msg = "* Description is blank";	
			$update_test_flag = "no";
		}	*/				
					
		//message
		if ($update_test_flag == "no") 
			$fcn_msg = "Error Occured:<br />".$fcn_msg;
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_category_input testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//end test_category_input	
	//---------- END function to test category input   ----------		
	
	
	//---------- START function to restore a discontinued category ----------
	function restore_category($parent_id = 0, $loop = 1, $current_restore_code = "", $debug = 0, $debug_string = "", $fcn_msg = "")
	{		
		//echo "$parent_id ";
		//$fcn_msg = "";

		//instantiate catalogue object
		if($this->catalogue = new Catalogue());
		
		//Restore the current category before then doing all the subs
		if ($loop == 1):
			$debug_string .= "<br />********************* restore_category **********************";
			
			//grab initial category details
			$this->set_select();
			$this->set_from("directory_categories");
			$this->set_where("category_id = '$parent_id'");		
			$result_cat = $this->get_data();
			$count_cat = $this->numrows; //returns the total number of rows generated				
			$row_cat = mysql_fetch_object($result_cat);
			$current_category_id = $row_cat->category_id;
			$current_category_name = $row_cat->category_name;
			$current_parent_id = $row_cat->parent_id;
			$current_restore_code = $row_cat->restore_code;		
			$debug_string .= "<br />current_category_id $current_category_id";	
			$debug_string .= "&nbsp;current_category_name $current_category_name";
			$debug_string .= "&nbsp;current_parent_id $current_parent_id";	
			$debug_string .= "&nbsp;current_restore_code $current_restore_code";	
			//echo $debug_string;
		
			//get new order id for restored record - put it at the end
			$this->set_select();
			$this->set_from("directory_categories");
			$this->set_where("discontinued != 1");		
			$this->set_where("order_id IS NOT NULL");
			$this->set_where("parent_id = '".$current_parent_id."'");
			$order_result = $this->get_data();
			$order_count = $this->numrows; //returns the total number of rows generated
			$restore_order_id = ($order_count + 1); //set order_id for new record based on total count
			
			//DO THE RESTORE FOR THIS CATEGORY
			$this->db_table = $this->DB_TABLE_CATEGORIES;
			$this->set_update();
			$this->add_update_value("discontinued", "0");
			//$this->add_update_value("visible", "1"); //force visibility or leave as it was
			$this->add_update_value("order_id", "$restore_order_id");
			$this->add_update_value("restore_code", "");
			$this->set_where("category_id = $parent_id");
			$this->set_where("discontinued = 1"); //only restore deleted records
			$this->set_where("restore_code = '$current_restore_code'"); //only restore where code matches that of original item being restored
			$update_id = $this->update_data(0);	

			if (isset($update_id)):
				$fcn_msg = "Category Restored";			
			
				//RESTORE ALL PRODUCTS FOR THIS CATEGORY
				//Only products where the restore_code matches the category restore code	
				$this->set_select();
				$this->set_from($this->DB_TABLE_PRODUCTS);
				$this->set_where("category_id = '$parent_id'");
				$this->set_where("discontinued = 1"); //only restore deleted records
				$this->set_where("restore_code = '$current_restore_code'"); //only restore where code matches that of original item being restored	
				$result_prod = $this->get_data();
				$count_prod = $this->numrows; //returns the total number of rows generated
		
				if ($result_prod && $count_prod > 0):
					while ($row_prod = mysql_fetch_object($result_prod)):
						$this->catalogue->product_id($row_prod->product_id);
						$this->catalogue->restore_product($current_restore_code, DEBUG_FCN_DISPLAY_FLAG);
					endwhile;
				endif;
				//RESTORE ALL PRODUCTS FOR THIS CATEGORY
			else:	
				$update_category = 0;
				$fcn_msg = "Error: Category not restored.";	
			endif;		
			//DO THE RESTORE FOR THIS CATEGORY					
		endif;				
		
		//restore any deleted sub-categories
		$count_cat = 0; //default
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("parent_id = '$parent_id'");
//		$this->set_where("restore_code = '$current_restore_code'");
		$result_cat = $this->get_data();
		$count_cat = $this->numrows; //returns the total number of rows generated
	
		//if ($result_cat && $count_cat > 0):
		//endif;

		while ($row_cat = mysql_fetch_object($result_cat)):		
			$category_id = $row_cat->category_id;
			$category_name = $this->htmlsafe($row_cat->category_name);
			$category_url = $this->htmlsafe($row_cat->category_url);
			$parent_id = $row_cat->parent_id;		
			$debug_string .= "<br />category_name $category_name";
			$debug_string .= "&nbsp;category_id $category_id";

			//get new order id for restored record - put it at the end
			$this->set_select();
			$this->set_from("directory_categories");
			$this->set_where("discontinued != 1");		
			$this->set_where("order_id IS NOT NULL");
			$this->set_where("parent_id = '".$parent_id."'");
			$order_result = $this->get_data();
			$order_count = $this->numrows; //returns the total number of rows generated
			$restore_order_id = ($order_count + 1); //set order_id for new record based on total count

			//DO THE RESTORE FOR THIS CATEGORY
			$this->db_table = $this->DB_TABLE_CATEGORIES;
			$this->set_update();
			$this->add_update_value("discontinued", "0");
			//$this->add_update_value("visible", "1"); //force visibility or leave as it was
			$this->add_update_value("order_id", "$restore_order_id");
			$this->add_update_value("restore_code", "");
			$this->set_where("category_id = $category_id");
			$this->set_where("discontinued = 1"); //only restore deleted records
			$this->set_where("restore_code = '$current_restore_code'"); //only restore where code matches that of original item being restored
			$update_id = $this->update_data(0);	
			if (isset($update_id)):
				$fcn_msg = "Category Restored";			
			
				//RESTORE ALL PRODUCTS FOR THIS CATEGORY
				//Only products where the restore_code matches the category restore code	
				$this->set_select();
				$this->set_from($this->DB_TABLE_PRODUCTS);
				$this->set_where("category_id = '$category_id'");
				$this->set_where("discontinued = 1"); //only restore deleted records
				$this->set_where("restore_code = '$current_restore_code'"); //only restore where code matches that of original item being restored	
				$result_prod = $this->get_data();
				$count_prod = $this->numrows; //returns the total number of rows generated
		
				if ($result_prod && $count_prod > 0):
					while ($row_prod = mysql_fetch_object($result_prod)):
						$this->catalogue->product_id($row_prod->product_id);
						$this->catalogue->restore_product($current_restore_code, DEBUG_FCN_DISPLAY_FLAG);
					endwhile;
				endif;
				//RESTORE ALL PRODUCTS FOR THIS CATEGORY								
			else:	
				$update_category = 0;
				$fcn_msg = "Error: Category not restored.";	
			endif;				
			//DO THE RESTORE FOR THIS CATEGORY				

			//re-call the function to look for subcategories within the current category
			$fcn_msg .= $this->restore_category($category_id, 0, $current_restore_code, DEBUG_FCN_DISPLAY_FLAG, $debug_string, $fcn_msg);
		endwhile;	
	
		//debug code
		if ($debug == 1 && $loop == 1)
		{
			$debug_string .= "<br />********************* restore_category **********************";
			echo "<br />restore_category testvar:$debug_string<br />";
		}//end if ($debug==1)
		return $fcn_msg;
	}//restore_category

	
	
	//---------- START function to update category orders ----------
	function update_category_order_ids($debug=0)
	{
		$fcn_msg = "";
		$debug_string = "";	
		
		//get 
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");	
		$this->set_where("category_id = '".$this->category_id."'");
		$result_order = $this->get_data();
		$row_order = mysql_fetch_object($result_order);
		$this->order_id = $row_order->order_id;
		$this->parent_id = $row_order->parent_id;
		$debug_string.= "<br />this order_id:".$this->order_id;
		$debug_string.= "<br />this parent_id:".$this->parent_id;
		$debug_string.= "<br />this nu_order_id:".$this->nu_order_id;	

		if ($this->order_id > $this->nu_order_id): // order higher	
			$this->db_table = $this->DB_TABLE_CATEGORIES;
			$this->set_update();
			$this->add_update_value("order_id", "(order_id + 1)", "MYSQL_FUNCTION");
			$this->set_where("discontinued != 1");
			$this->set_where("order_id >= ".$this->nu_order_id);
			$this->set_where("order_id < ".$this->order_id);
			$this->set_where("parent_id = ".$this->parent_id);	
			$result_update_id = $this->update_data(0);
			$debug_string.= "<br />result_update_id:$result_update_id";
			if (isset($result_update_id)):	
				$this->db_table = $this->DB_TABLE_CATEGORIES;
				$this->set_update();
				$this->add_update_value("order_id",$this->nu_order_id);
				$this->set_where("discontinued != 1");	
				$this->set_where("category_id = ".$this->category_id);	
				$result_order_id = $this->update_data(0);
				$debug_string.= "<br />result_order_id:$result_order_id";

				if (isset($result_order_id)):	
					$fcn_msg = "Categories Re-ordered";
				endif;
			endif;	
		elseif ($this->order_id < $this->nu_order_id): // order lower
			$this->db_table = $this->DB_TABLE_CATEGORIES;
			$this->set_update();
			$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
			$this->set_where("discontinued != 1");	
			$this->set_where("order_id > ".$this->order_id);
			$this->set_where("order_id <= ".$this->nu_order_id);
			$this->set_where("parent_id = ".$this->parent_id);	
			$result_update_id = $this->update_data(0);
			$debug_string.= "<br />result_update_id:$result_update_id";
			
			if (isset($result_update_id)):
				$this->db_table = $this->DB_TABLE_CATEGORIES;
				$this->set_update();
				$this->add_update_value("order_id",$this->nu_order_id);
				$this->set_where("discontinued != 1");	
				$this->set_where("category_id = ".$this->category_id);	
				$result_order_id = $this->update_data(0);
				$debug_string.= "<br />result_order_id:$result_order_id";

				if (isset($result_order_id)):	
					$fcn_msg = "Categories Re-ordered";	
				endif;			
			endif;	
		endif;
	
		//debug code
		if ($debug==1)
		{
			echo "<div>update_category_order_ids testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;		
	}//update_category_order_ids
	//---------- END function to update category orders ----------
	
	
	//---------- START function to reset ALL category/sub-category orders alphabetically ----------
	function reset_category_orders($select_parent = 0, $type = "", $debug=0)
	{
		$fcn_msg = "";
		$debug_string = "";
		$debug_string.="<br />*** reset_category_orders ***<br />";	
		$debug_string.= "<br />select_parent:$select_parent";

		$this->set_select("select distinct parent_id");
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");
		//if only resetting one specific category - inc top level. if select_parent is blank then reset ALL
		if ($select_parent >= 0 && $type != "all")
			$this->set_where("parent_id = '$select_parent'");	
		$result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated

		if (!$result && $count == 0):
			$fcn_msg = 0;
		else:
			while ($row = mysql_fetch_object($result)):
				$parent_id = $row->parent_id;		
			
				$this->set_select("select category_id");
				$this->set_from("directory_categories");
				$this->set_where("discontinued != 1");	
				$this->set_where("parent_id = $parent_id");
				$this->set_orderby("category_name");			
				$result2 = $this->get_data(0);
				$count = 1;
		
				while ($row2 = mysql_fetch_object($result2)):
					$category_id = $row2->category_id;
					
					//SQL UPDATE METHOD CALLS
					$this->db_table = $this->DB_TABLE_CATEGORIES;
					$this->set_update();
					$this->add_update_value("order_id", "$count");
					$this->set_where("category_id = $category_id");		
					$update_result = $this->update_data(0);
					$update = "success";
					
					$count++;
				endwhile;						
			endwhile;
			
			$fcn_msg = 1;								
		endif;

		//debug code
		if ($debug==1)
		{
			echo "<div>reset_category_orders testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;		
	}//reset_category_orders
	//---------- END function to reset ALL category/sub-category orders alpabetically ----------
	
	
	//---------- START function to generate category drop-list options for forms ----------
	//generate the category menu for the admin pages of the CMS
	function cms_cat_menu($id, $tree = "", $selected = 0, $exclude_id = "")
	{
		$this->set_select("SELECT category_name, category_id, parent_id");
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");	
		$this->set_where("parent_id = '$id'");
		if ($exclude_id != "")
			$this->set_where("category_id != '$exclude_id'");
		$this->set_orderby("order_id");
		$result_nav = $this->get_data();
		$result_count = $this->numrows; //returns the total number of rows generated
	
		if ($result_nav && $result_count > 0):
			$cnt = 0;
			while ($row_nav = mysql_fetch_object($result_nav)):
				$nav_name = $this->htmlsafe($row_nav->category_name);
				$nav_id = $this->htmlsafe($row_nav->category_id);
				$this->parent_id = $row_nav->parent_id;			   
				//echo $this->parent_id;
				//echo $nav_id;
				//echo $id;
			   
				/*if ($this->parent_id == 0)
				{
					$tree = "";
				}
				else
				{*/
					if ( ($this->parent_id == $id) && ($cnt > 0) )
					{
					}
					else
					{
						$tree .= "$nbsp ->";
					}
				//}//end if ($parent_id == 0)
				
				$selected_text = "";
				if ($nav_id == $selected)
					$selected_text = "selected=\"selected\"";
				   
				if ($this->parent_id == 0)
				   $menu_item = "<option value=\"$nav_id\" $selected_text>$tree$nav_name</option>";
				else
				   $menu_item = "<option value=\"$nav_id\" $selected_text>$tree$nav_name</option>";
				
				echo $menu_item;
				
				$this->cms_cat_menu($nav_id, $tree, $selected, $exclude_id);
				$cnt++;
			endwhile;
		endif;
	}//cms_cat_menu
	//---------- END function to generate category drop-list options for forms ----------


	//---------- START function to generate display list of categories ----------
	//FUNCTION - RETURNS DISPLAY HTML FOR LIST OF CATEGORIES - UP TO CURRENT CATEGORY
	function cat_link_menu($type = "product", $cat_id, $page_name = "", $spacer = "&gt;", $css_style = "", $limit = 10, $debug=0)
	{
		$fcn_msg = "";
		
		//page_name for the links to go to
		if ($page_name == "")
			$page_name = "products/list";
		
		//invalid category crashes it
		if ( !($cat_id > 0) )
			return $fcn_msg;
		
		$debug_string = "";		
	
		//DONT WANT AN INFINITE LOOP SO ONLY LOOK LIMIT TIMES BEFORE RETURNING ERROR
		$nolevels = 0;
		$finshed = 0;
		$cnt = 0;
		while ($finshed == 0)
		{
			$this_link = "";
			
			$debug_string.= "<br />cnt:$cnt";
			if ($cnt >= $limit)
			{
				//error
				$fcn_msg = "";
				return $fcn_msg;
			}

			//loop parent_id as category_id round until we have reached top-level - this it the parent.
			$this->set_select();
			$this->set_from("directory_categories");
			$this->set_where("discontinued != 1");	
			$this->set_where("category_id = '$cat_id'");
			$result_parent = $this->get_data();
			$count_parent = $this->numrows; //returns the total number of rows generated
			
			//loop parent_id as category_id round until we have reached top-level - this is the parent.
			if (!$result_parent || $count_parent == 0):
				//error will loop forever - return empty string
				$fcn_msg = "";
				return $fcn_msg;
			else:
				$row_parent = mysql_fetch_object($result_parent);
				
				if ($row_parent->parent_id == 0)
				{
					//we've found the parent
					$finshed = 1;
					//how many levels to find parent
					$nolevels = $cnt;
					
					//PARENT LINK
					//directory-products.php shows featured items list - product_list is just prods for the category
				//	$this_link="<a href=\"products/list.php?c=".$row_parent->category_id."\" class=\"$css_style\">".$this->htmlsafe($row_parent->category_name)."</a>";
					
					//OR
					
					//PARENT LINK - function using mod_rewrite flag
					$this_link = "<a href=\""; //start link
					//$this_link.="products/list.php?c=".$row_parent["category_id"]."\"";
					//use the modrewrite function link generator
					$this_link.= $this->gen_link_action($type,$page_name,".php","c",$row_parent->category_id,"p",1);
					//manual
				//	$this_link.="products/list/c/".$row_parent->category_id."/\"";
					
					$this_link.= "\" class=\"$css_style\">".$this->htmlsafe($row_parent->category_name)."</a>"; //close link
					
					$fcn_msg = $this_link . $fcn_msg;					
					
					return $fcn_msg;
				}
				else
				{
					//not parent keep going
					$cat_id = $row_parent->parent_id;
					//$debug_string.= "<br />cat_id:$cat_id";
										
					//PARENT LINK
				//	$this_link="<a href=\"products/list.php?c=".$row_parent->category_id."\" class=\"$css_style\">".$this->htmlsafe($row_parent->category_name)."</a>";			
					
					//OR
					
					//PARENT LINK - function using mod_rewrite flag					
					$this_link = "<a href=\""; //start link
					//use the modrewrite function link generator
					$this_link.= $this->gen_link_action($type,$page_name,".php","c",$row_parent->category_id,"p",1);
					//manual
				//	$this_link.="products/list/c/".$row_parent->category_id."/\"";
					
					$this_link.= "\" class=\"$css_style\">".$this->htmlsafe($row_parent->category_name)."</a>"; //close link
									
					$fcn_msg = $this_link . $fcn_msg;					
					//add spacer in front of last link as more to go
					$fcn_msg = $spacer . $fcn_msg;
				}	
			endif;
			
			$cnt++;
		}//end while	
		
		//debug code
		if ($debug==1)
		{
			echo "<br />cat_link_menu testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//end cat_link_menu
	//---------- END function to generate display list of categories ----------
	
	
	//---------- START function to return top level parent id for a category ----------
	//FUNCTION - RETURNS THE TOP LEVEL PARENT ID FOR ANY SUPPLIED CATEGORY
	//always pass 0 for $nolevels
	function get_cat_parent($cat_id, $limit = 10)
	{
		//DONT WANT AN INFINITE LOOP SO ONLY LOOK LIMIT TIMES BEFORE RETURNING ERROR
		$nolevels = 0;
		$finshed = 0;
		$cnt = 0;
		while ($finshed == 0)
		{
			//echo "cnt:$cnt";
			if ($cnt >= $limit)
			{
				//error
				return array (0,0);
			}
			$cnt++;
			
		
			//loop parent_id as category_id round until we have reached top-level - this it the parent.
			$this->set_select("SELECT parent_id");
			$this->set_from("directory_categories");
			$this->set_where("category_id = '$cat_id'");
			$result_parent = $this->get_data();
			$count_parent = $this->numrows; //returns the total number of rows generated
								
			if ( !$result_parent || $count_parent == 0 ):
				//error
				return array (0,0);
			else:
				$row_parent = mysql_fetch_object($result_parent);
				if ($row_order->parent_id == 0):
					//we've found the parent
					$finshed = 1;
					//how many levels to find parent
					$nolevels = $cnt;
					return array ($cat_id,$nolevels);
				else:
					//not parent keep going
					$cat_id = $row_order->parent_id;
					//echo "<br />cat_id:$cat_id";
				endif;	
			endif;
		}//end while	
	}//end get_cat_parent
	//FUNCTION - RETURNS THE TOP LEVEL PARENT ID FOR ANY SUPPLIED CATEGORY
	//---------- END function to return top level parent id for a category ----------



	//*****---------- START MENU FUNCTIONS - css/JavaScript menu ----------*****

	//---------- START function to display HTML list of categories for the menu ----------
	//loop lets us indentify the first main call of the fuction not the sub calls back to itself
	function disp_cat_menu($parent_id = 0, $selected_id = 0, $loop = 1, $debug=0)
	{		
		$debug_string.= "";
		
		//we want to color the top level only - so whatever the selected_id is we need 
		//to return the top level category related to that
		$debug_string.= "<br />selected_id:$selected_id";
		if ($loop == 1) //if first main call of function not sub call
			list($my_selected_id,$levels) = $this->get_cat_parent($selected_id);
		$debug_string.= "<br />my_selected_id:$my_selected_id";
		
		//categories
		$count_cat = 0; //default
		$this->set_select();
		$this->set_from("directory_categories");
		$this->set_where("discontinued != 1");	
		$this->set_where("parent_id = '$parent_id'");
		$this->set_where("visible = 1");
		$this->set_orderby("order_id");
		$result_cat = $this->get_data();
		$count_cat = $this->numrows; //returns the total number of rows generated
	
		if ($result_cat && $count_cat > 0):
			if ($parent_id == 0)
				$menu_item .= "<ul id=\"navmenu\">";
			else
				$menu_item .= "<ul>";
		endif;

		while ($row_cat = mysql_fetch_object($result_cat)):		
			$category_id = $row_cat->category_id;
			$category_name = $this->htmlsafe($row_cat->category_name);
			$category_url = $this->htmlsafe($row_cat->category_url);
			$parent_id = $row_cat->parent_id;
			$menu_bar_colour = $row_cat->colour1;
	
			$style_code = "";
			if ($my_selected_id == $category_id)
				$style_code = " style=\"background-color:$menu_bar_colour\" ";
			//$debug_string.= "<br />style_code:$style_code";
		
			if ($category_url != "")
			{
				$menu_item .= "<li><a href=\"".$category_url."\"$style_code>$category_name</a>";
			}
			else if ($parent_id == 0)
			{
				if (MOD_REWRITE == 1)
				{
					//MOD_REWRITE
					$menu_item .= "<li><a href=\"/products/list/c/$category_id/\"$style_code>$category_name</a>";
				}
				else
				{
					//directory-products.php shows featured items list - product_list is just prods for the category
					//$menu_item .= "<li><a href=\"directory-products.php?c=$category_id\"$style_code>$category_name</a>";
					$menu_item .= "<li><a href=\"products/list.php?c=$category_id\"$style_code>$category_name</a>";				
				}
			}
			else
			{
				if (MOD_REWRITE == 1)
				{
					//MOD_REWRITE
					$menu_item .= "<li><a href=\"/products/list/c/$category_id/\"$style_code>$category_name</a>";				
				}
				else
				{
					$menu_item .= "<li><a href=\"products/list.php?c=$category_id\"$style_code>$category_name</a>";
				}//end if (MOD_REWRITE			
			}
							
			//re-call the function to look for items and subcategories within the current categroy
			$menu_item .= $this->disp_cat_menu($category_id, $selected_id, 0,$debug);
	
			$menu_item .= "</li>";
		endwhile;
		
		if ($count_cat > 0)
		{
			$menu_item .= "</ul>";
		}
	
		//debug code
		if ($debug==1)
		{
			echo "<br />disp_cat_menu testvar:$debug_string<br />";
		}//end if ($debug==1)
		return $menu_item;
	}//disp_cat_menu
	//---------- END function to display HTML list of categories for the menu ----------	
	
	//*****---------- END MENU FUNCTIONS ----------*****



	//*****---------- QUERY FUNCTIONS ----------*****

	// function	
	//*** retrieve a list of all categories ***
	function select_category_list_cms($order_by = "")
	{
		$this->set_select();
		$this->set_from("directory_categories");
		
		//$this->set_where("discontinued != 1");	
		//*** only query non-deleted categories ***
		if ($_SESSION ['s_show_deleted_categories'] != "yes"):
			$this->set_where("discontinued != 1");	
		else: //include this to show just deleted rather than show all
			$this->set_where("discontinued = 1");		
		endif;		
		
		$this->set_where("parent_id = '".$this->parent_id."'");				
			
		//LETTER FILTER
		if ( !empty($_SESSION['dir_cat_search_vars']['letter_value']) ):		
			$this->set_where("SUBSTRING(name,1,1) = '" . strtolower($_SESSION['dir_cat_search_vars']['letter_value']) . "'");
		endif;
		
		//SEARCH FILTER
		if ( !empty($_SESSION['dir_cat_search_vars']['search_selection']) && !empty($_SESSION['dir_cat_search_vars']['search_value']) ):		
			$this->set_where($_SESSION['dir_cat_search_vars']['search_selection'] . " LIKE '%" . mysql_escape_string($_SESSION['dir_cat_search_vars']['search_value']) . "%'");
		endif;
	
		//ORDER RESULTS
		if ($_SESSION['dir_cat_search_vars']['order_by'] != "" && $_SESSION['dir_cat_search_vars']['order_type'] != ""):
			$this->set_orderby($_SESSION['dir_cat_search_vars']['order_by'] . " " . $_SESSION['dir_cat_search_vars']['order_type']);	
		else:
			$this->set_orderby("order_id ASC");
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
		$count = $this->numrows;//returns the total number of rows generated
	
		if ($count == 0)
		{
			$err_msg = 'No Categories could be found';
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check);//on success return error code 0 and array of results				
		}
	
	}//select_category_list_cms
	//*** retrieve a list of all categories ***


	// public function	
	//*** retrieve a list of all categories ***
	public function select_category_list($order_by = "")
	{
		$this->set_select();
		$this->set_from("directory_categories");
		
		$this->set_where("discontinued != 1");	
		$this->set_where("parent_id = '".$this->parent_id."'");	
		$this->set_where("visible = 1");

		//ORDER RESULTS
		$this->set_orderby("order_id ASC");
			
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
		$count = $this->numrows;//returns the total number of rows generated
	
		if ($count == 0)
		{
			$err_msg = 'No Categories could be found';
			return array(1,$err_msg);//on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check);//on success return error code 0 and array of results				
		}
	}//select_category_list
	//*** retrieve a list of all categories ***

	//*****---------- QUERY FUNCTIONS ----------*****
	


	//*****---------- OTHER FUNCTIONS ----------*****
	//create a link for page types - sometimes based on mod_rewrite
	function gen_link_action($type = "", $page_name, $page_ext = ".php", $x1, $y1, $x2, $y2, $debug=0)
	{	
		$debug_string = "";	
		$gen_link_action = ""; 

		if ($type == "product"):
			if (MOD_REWRITE == 1):
				//must put preceding / so that we go from the root or the link goes from 
				//the last / and keeps repeating and getting bigger 
				$gen_link_action = "/".$page_name."/";
				$gen_link_action.= "$x1/$y1/";
				$gen_link_action.= "$x2$y2/";
			else:
				$gen_link_action = "/".$page_name.$page_ext."?";
				$gen_link_action.= "$x1=".$y1;
				$gen_link_action.= "&amp;$x2=".$y2;
			endif;
		elseif ($type == "category"):
			if (MOD_REWRITE == 1):
				$gen_link_action = "/ds_admin/".$page_name."/";
				$gen_link_action.= "$x1/$y1/";
				$gen_link_action.= "$x2$y2/";
			else:
				$gen_link_action = "/ds_admin/".$page_name.$page_ext."?";
				$gen_link_action.= "$x1=".$y1;
				$gen_link_action.= "&amp;$x2=".$y2;
			endif;			
		endif;

		//debug code
		if ($debug==1)
		{
			echo "<div>gen_link_action testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $gen_link_action;			
	}//end gen_link_action	
	//*****---------- OTHER FUNCTIONS ----------*****

}//class DirectoryCategories

?>