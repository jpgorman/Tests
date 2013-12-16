<?php
//***********************************************************************************
//Catalogue class covers products/variations 
//i.e. all the main elements of the catalogue display stage of the site
class directory_catalogue extends db_object 
{ 
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////
	var $test_flag = 0;
	
	//declare database constants
	var $DB_TABLE_PRODUCTS = "directory_products";
	var $DB_TABLE_PRODUCT_FIELDS = "category_id, product_name, company_name, address, address2, address3, postcode, phone, fax, email, website, contact_name, contact_position, contact_name2, contact_position2, contact_name3, contact_position3, pr_agency, slug, company_profile_summary, company_profile, variation_fields, visible, featured, default_code, default_price, default_rrp, rrp_text, meta_title, meta_keywords, meta_description, order_id";

	var $DB_TABLE_VARIATIONS = "directory_variations";
	var $DB_TABLE_VARIATION_FIELDS = "product_id, code, var1, var2, var3, price, rrp, stock_status, visible, stock_level, image_id";

	/* STATS TABLE - graduate profile views logs */
	private $DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS = "stats_directory_product_views";
	private $DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS_FIELDS = "`product_id`, `user_id`, `count_views`, `date_last_viewed`";	


	//declare input variables
	var $action = "";
	var $submit = "";
	var $select_category = 0;
	var $category_id = 0;
	var $product_id = 0;
	var $variation_id = 0;	
	var $add_product_id = 0;
	var $update_product_id = 0;
	var $product_name = "";	
	var $company_name = "";
	var $address = "";
	var $address2 = "";
	var $address3 = "";
	var $postcode = "";
	var $phone = "";
	var $fax = "";
	var $email = "";
	var $website = "";
	var $contact_name = "";
	var $contact_position = "";	
	var $contact_name2 = "";
	var $contact_position2 = "";	
	var $contact_name3 = "";
	var $contact_position3 = "";	
	var $pr_agency = "";	
	var $slug = "";
	var $company_profile_summary = "";
	var $company_profile = "";
	var $variation_fields = 0;
	var $visible = 1;
	var $featured = 1;
	var $default_code = "";
	var $default_price = 0;
	var $default_rrp = 0;
	var $rrp_text = "";
	var $meta_title = "";
	var $meta_keywords = "";
	var $meta_description = "";
	var $order_id = 0;
	var $nu_order_id = 0;
	var $success_flag = 0;
	var $add_success_flag = 0;
	var $update_success_flag = 0;
	var $delete_success_flag = 0;
	var $restore_success_flag = 0;
	var $code = "";
	var $var1 = "";
	var $var2 = "";
	var $var3 = "";
	var $price = 0;
	var $rrp = 0;
	var $stock_status = 1;
	var $stock_level = 0;
	var $image_id = 0;
	
	var $num_vars = 0;
	var $var_text = "";
	var $var_field = "";
	//public $debug = 0;
	
	var $paging_links = "";
	var $paging_list = "";

	public $user_id = 0;
	
	public $date_last_viewed = NULL;	

	public $messages = "";
	
	//STATS
	//recordset queries
	var	$rs_directoryproductviews = "";	
	
	var $directoryproductviews_err_code = "";
	
	var	$total_views = 0;	
	var	$unique_views = 0;
	var	$active_users = 0;	
	var	$perc_active_views = 0;	
	
	
	//Constructor function
	function __construct()
	{	
		if($this->c_users = new Users());
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

	// function
	function select_category($input_select_category = NULL)
	{
		if($input_select_category <> NULL){
       		$this->select_category = $this->cleanstring(trim($input_select_category));
		}
		//echo $this->select_category; //debug 
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
	function slug($input_slug = NULL)
	{
		if($input_slug <> NULL){
       		$this->slug = $this->cleanstring(trim($input_slug));
		}
		//echo $this->category_id; //debug 
    }

	// function
	function product_id($input_product_id = NULL)
	{
		if($input_product_id <> NULL){
       		$this->product_id = $this->cleanstring(trim($input_product_id));
		}
		//echo $this->product_id; //debug 
    }
	
	// function
	function variation_id($input_variation_id = NULL)
	{
		if($input_variation_id <> NULL){
       		$this->variation_id = $this->cleanstring(trim($input_variation_id));
		}
		//echo $this->variation_id; //debug 
    }	

	// PUBLIC function
	function product_name($input_product_name = NULL)
	{
		if($input_product_name <> NULL){
			$this->product_name = htmlspecialchars(trim($input_product_name), ENT_QUOTES);	
			$this->slug = $this->make_url_safe($this->cleanstring($input_product_name));
		}
		//echo $this->product_name; //debug  
    }
	
	// PUBLIC function
	function company_name($input_company_name = NULL)
	{
		if ($input_company_name <> NULL)
		{
			$this->company_name = htmlspecialchars(trim($input_company_name), ENT_QUOTES);	
		}
		//echo $this->company_name; //debug  
    }
	
	// PUBLIC function
	function address($input_address = NULL)
	{
		if ($input_address <> NULL)
		{
			$this->address = htmlspecialchars(trim($input_address), ENT_QUOTES);	
		}
		//echo $this->address; //debug  
    }
	
	// PUBLIC function
	function address2($input_address2 = NULL)
	{
		if ($input_address2 <> NULL)
		{
			$this->address2 = htmlspecialchars(trim($input_address2), ENT_QUOTES);	
		}
		//echo $this->address2; //debug  
    }
	
	// PUBLIC function
	function address3($input_address3 = NULL)
	{
		if ($input_address3 <> NULL)
		{
			$this->address3 = htmlspecialchars(trim($input_address3), ENT_QUOTES);	
		}
		//echo $this->address3; //debug  
    }	
		
	// PUBLIC function
	function postcode($input_postcode = NULL)
	{
		if ($input_postcode <> NULL)
		{
			$this->postcode = htmlspecialchars(trim($input_postcode), ENT_QUOTES);	
		}
		//echo $this->postcode; //debug  
    }
	
	// PUBLIC function
	function phone($input_phone = NULL)
	{
		if ($input_phone <> NULL)
		{
			$this->phone = htmlspecialchars(trim($input_phone), ENT_QUOTES);	
		}
		//echo $this->phone; //debug  
    }	
	
	// PUBLIC function
	function fax($input_fax = NULL)
	{
		if ($input_fax <> NULL)
		{
			$this->fax = htmlspecialchars(trim($input_fax), ENT_QUOTES);	
		}
		//echo $this->fax; //debug  
    }	

	// PUBLIC function
	public function email($input_email = NULL)
	{
		if($input_email <> NULL){
			$this->email = htmlspecialchars(trim($input_email), ENT_QUOTES);	
		}
		//echo $this->email; //debug  
    }
	
	// PUBLIC function
	public function website($input_website = NULL)
	{
		if($input_website <> NULL){
			$this->website = htmlspecialchars(trim($input_website), ENT_QUOTES);	
		}
		//echo $this->website; //debug  
    }
	
	// PUBLIC function
	public function contact_name($input_contact_name = NULL)
	{
		if($input_contact_name <> NULL){
			$this->contact_name = htmlspecialchars(trim($input_contact_name), ENT_QUOTES);	
		}
		//echo $this->contact_name; //debug  
    }
	
	// PUBLIC function
	public function contact_position($input_contact_position = NULL)
	{
		if($input_contact_position <> NULL){
			$this->contact_position = htmlspecialchars(trim($input_contact_position), ENT_QUOTES);	
		}
		//echo $this->contact_position; //debug  
    }	
		
	// PUBLIC function
	public function contact_name2($input_contact_name2 = NULL)
	{
		if($input_contact_name2 <> NULL){
			$this->contact_name2 = htmlspecialchars(trim($input_contact_name2), ENT_QUOTES);	
		}
		//echo $this->contact_name2; //debug  
    }
	
	// PUBLIC function
	public function contact_position2($input_contact_position2 = NULL)
	{
		if($input_contact_position2 <> NULL){
			$this->contact_position2 = htmlspecialchars(trim($input_contact_position2), ENT_QUOTES);	
		}
		//echo $this->contact_position2; //debug  
    }	
		
	// PUBLIC function
	public function contact_name3($input_contact_name3 = NULL)
	{
		if($input_contact_name3 <> NULL){
			$this->contact_name3 = htmlspecialchars(trim($input_contact_name3), ENT_QUOTES);	
		}
		//echo $this->contact_name3; //debug  
    }
	
	// PUBLIC function
	public function contact_position3($input_contact_position3 = NULL)
	{
		if($input_contact_position3 <> NULL){
			$this->contact_position3 = htmlspecialchars(trim($input_contact_position3), ENT_QUOTES);	
		}
		//echo $this->contact_position3; //debug  
    }
	
	// PUBLIC function
	public function pr_agency($input_pr_agency = NULL)
	{
		if($input_pr_agency <> NULL){
			$this->pr_agency = htmlspecialchars(trim($input_pr_agency), ENT_QUOTES);	
		}
		//echo $this->pr_agency; //debug  
    }

	// function
	function company_profile_summary($input_company_profile_summary = NULL)
	{
		if ($input_company_profile_summary <> NULL)
		{
			//$this->company_profile_summary = htmlspecialchars(trim($input_company_profile_summary), ENT_QUOTES); //old
			$this->company_profile_summary = htmlspecialchars(strip_tags($input_company_profile_summary,"<a><b><strong><i><u><em><embed><p><div><span><strike><sub><sup><img><table><tbody><tfoot><thead><tr><td><th><ul><ol><li><blockquote><br><hr><h1><h2><h3><h4>"), ENT_QUOTES);
			//echo $this->Content; //debug	
		}
		//echo $this->company_profile_summary; //debug  
    }

	// PUBLIC function
	public function company_profile($input_company_profile = NULL)
	{
		if($input_company_profile <> NULL){
			$this->company_profile = htmlspecialchars(trim($input_company_profile), ENT_QUOTES);	
		}
		//echo $this->company_profile; //debug  
    }

	// function
	function variation_fields($input_variation_fields = NULL)
	{
		if($input_variation_fields <> NULL){
			$this->variation_fields = htmlspecialchars(trim($input_variation_fields), ENT_QUOTES);	
		}
		//echo $this->variation_fields; //debug  
    }
	
	// function
	function visible($input_visible = NULL)
	{
		if($input_visible <> NULL){
			$this->visible = htmlspecialchars(trim($input_visible), ENT_QUOTES);	
		}
		//echo $this->visible; //debug  
    }
	
	// function
	function featured($input_featured = NULL)
	{
		if($input_featured <> NULL){
			$this->featured = htmlspecialchars(trim($input_featured), ENT_QUOTES);	
		}
		//echo $this->featured; //debug  
    }
	
	// function
	function default_code($input_default_code = NULL)
	{
		if($input_default_code <> NULL){
			$this->default_code = htmlspecialchars(trim($input_default_code), ENT_QUOTES);	
		}
		//echo $this->default_code; //debug  
    }

	// function
	function default_price($input_default_price = NULL)
	{
		if($input_default_price <> NULL){
			$this->default_price = htmlspecialchars(trim($input_default_price), ENT_QUOTES);	
		}
		//echo $this->default_price; //debug  
    }

	// function
	function default_rrp($input_default_rrp = NULL)
	{
		if($input_default_rrp <> NULL){
			$this->default_rrp = htmlspecialchars(trim($input_default_rrp), ENT_QUOTES);	
		}
		//echo $this->default_rrp; //debug  
    }

	// function
	function rrp_text($input_rrp_text = NULL)
	{
		if($input_rrp_text <> NULL){
			$this->rrp_text = htmlspecialchars(trim($input_rrp_text), ENT_QUOTES);	
		}
		//echo $this->rrp_text; //debug  
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
	function code($input_code = NULL)
	{
		if($input_code <> NULL){
			$this->code = htmlspecialchars(trim($input_code), ENT_QUOTES);	
		}
		//echo $this->code; //debug  
    }
	
	// function
	function var1($input_var1 = NULL)
	{
		if($input_var1 <> NULL){
			$this->var1 = htmlspecialchars(trim($input_var1), ENT_QUOTES);	
		}
		//echo $this->var1; //debug  
    }
	
	// function
	function var2($input_var2 = NULL)
	{
		if($input_var2 <> NULL){
			$this->var2 = htmlspecialchars(trim($input_var2), ENT_QUOTES);	
		}
		//echo $this->var2; //debug  
    }
	
	// function
	function var3($input_var3 = NULL)
	{
		if($input_var3 <> NULL){
			$this->var3 = htmlspecialchars(trim($input_var3), ENT_QUOTES);	
		}
		//echo $this->var3; //debug  
    }

	// function
	function price($input_price = NULL)
	{
		if($input_price <> NULL){
			$this->price = htmlspecialchars(trim($input_price), ENT_QUOTES);	
		}
		//echo $this->price; //debug  
    }

	// function
	function rrp($input_rrp = NULL)
	{
		if($input_rrp <> NULL){
			$this->rrp = htmlspecialchars(trim($input_rrp), ENT_QUOTES);	
		}
		//echo $this->rrp; //debug  
    }
	
	// function
	function stock_status($input_stock_status = 0)
	{	
		$this->stock_status = (int)$input_stock_status;
		//echo $this->stock_status; //debug 
    }		
	
	// function
	function stock_level($input_stock_level = 0)
	{	
		if($input_stock_level <> 0){
			$this->stock_level = (int)$input_stock_level;
		}
		//echo $this->stock_level; //debug 
    }		
	
	// function
	function image_id($input_image_id = 0)
	{	
		if($input_image_id <> 0){
			$this->image_id = (int)$input_image_id;
		}
		//echo $this->image_id; //debug 
    }			
	
	// function
	function num_vars($input_num_vars = 0)
	{	
		if($input_num_vars <> 0){
			$this->num_vars = (int)$input_num_vars;
		}
		//echo $this->num_vars; //debug 
    }			
	
	// function
	function var_text($input_var_text = NULL)
	{
		if($input_var_text <> NULL){
			$this->var_text = htmlspecialchars(trim($input_var_text), ENT_QUOTES);	
		}
		//echo $this->var_text; //debug  
    }
	
	// function
	function var_field($input_var_field = NULL)
	{
		if($input_var_field <> NULL){
			$this->var_field = htmlspecialchars(trim($input_var_field), ENT_QUOTES);	
		}
		//echo $this->var_field; //debug  
    }	

	// PUBLIC function
	public function user_id($input_user_id = NULL)
	{
		if($input_user_id <> NULL){
       		$this->user_id = $this->cleanstring(trim($input_user_id));
		}
		//echo $this->user_id; //debug 
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
	


	//***********************************************************************************
	//******************************* PRODUCT FUNCTIONS *********************************
	
	//---------- START function to add product ----------
	function add_product($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";
			
		if ($this->default_rrp == "")
			$this->default_rrp = 0;		
		if ($this->rrp_text == "")
			$this->rrp_text = "R.R.P";
	
		//get new order id for new record
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("discontinued != 1");		
		$this->set_where("order_id IS NOT NULL");
		$this->set_where("category_id = '".$this->select_category."'");
		$order_result = $this->get_data();
		$order_count = $this->numrows; //returns the total number of rows generated
		$this->nu_order_id = ($order_count + 1); //set order_id for new record based on total count		
		$debug_string.= "<br />this->nu_order_id:'".$this->nu_order_id."'";	

		//Insert the record
		$this->db_table = $this->DB_TABLE_PRODUCTS;
		$this->set_insert($this->DB_TABLE_PRODUCT_FIELDS);
		//add argument to insert values array
		$this->add_insert_value($this->select_category);
		$this->add_insert_value($this->product_name);	
		$this->add_insert_value($this->company_name);
		$this->add_insert_value($this->address);
		$this->add_insert_value($this->address2);
		$this->add_insert_value($this->address3);
		$this->add_insert_value($this->postcode);
		$this->add_insert_value($this->phone);
		$this->add_insert_value($this->fax);
		$this->add_insert_value($this->email);
		$this->add_insert_value($this->website);
		$this->add_insert_value($this->contact_name);
		$this->add_insert_value($this->contact_position);
		$this->add_insert_value($this->contact_name2);
		$this->add_insert_value($this->contact_position2);
		$this->add_insert_value($this->contact_name3);
		$this->add_insert_value($this->contact_position3);
		$this->add_insert_value($this->pr_agency);
		$this->add_insert_value($this->slug);
		$this->add_insert_value($this->company_profile_summary, "HTML");
		$this->add_insert_value($this->company_profile, "HTML");
		$this->add_insert_value($this->variation_fields); //var fields set to zero default and add types in the admin edit variations page
		$this->add_insert_value($this->visible);
		$this->add_insert_value($this->featured);
		$this->add_insert_value($this->default_code);
		$this->add_insert_value($this->default_price);
		$this->add_insert_value($this->default_rrp);
		$this->add_insert_value($this->rrp_text);			
		$this->add_insert_value($this->meta_title);
		$this->add_insert_value($this->meta_keywords);
		$this->add_insert_value($this->meta_description);
		$this->add_insert_value($this->select_parent);
		$this->add_insert_value($this->nu_order_id);
		//call method to create insert query - returns the row_id for the inserted item
		$insert_id = $this->insert_data();

		if ($insert_id):
			$this->add_product_id = mysql_insert_id();
			$this->product_id = $this->add_product_id;
			$this->add_success_flag = 1;			
			$fcn_msg = "Product has been Added to the Database.<br />";
			$debug_string.= "<br />mysql_insert_id():".mysql_insert_id();
			
			//*** PRODUCT ORDERING ***
			//update order_ids
			$this->update_product_order_ids(DEBUG_FCN_DISPLAY_FLAG);				
			//*** PRODUCT ORDERING ***

			//INSERT DEFAULT VARIATION
			//standard - i.e. var 1,2,3 all blank			
			$this->db_table = $this->DB_TABLE_VARIATIONS;
			$this->set_insert($this->DB_TABLE_VARIATION_FIELDS);
			//add argument to insert values array
			$this->add_insert_value($this->add_product_id);
			$this->add_insert_value($this->default_code);
			$this->add_insert_value($this->var1);
			$this->add_insert_value($this->var2);
			$this->add_insert_value($this->var3);
			$this->add_insert_value($this->default_price);
			$this->add_insert_value($this->default_rrp);
			$this->add_insert_value($this->stock_status);			
			$this->add_insert_value($this->visible);
			$this->add_insert_value($this->stock_level);
			$this->add_insert_value($this->image_id);
			//call method to create insert query - returns the row_id for the inserted item
			$insert_var_id = $this->insert_data();
			$debug_string.= "<br />insert_var_id:$insert_var_id";

			if ($insert_var_id):
				$fcn_msg .= " Product Set to have no variations."; 
			else:
			endif;		
			//INSERT DEFAULT VARIATION
		else:
			$fcn_msg = "Could not add Product.<br />";
		endif;
	
		//debug code
		if ($debug==1)
		{
			echo "<div>add_product testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end add_product	
	//---------- END function to add product ----------	


	//---------- START function to edit product ----------
	//update product function
	function update_product($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";
	
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("discontinued != 1");	
		$this->set_where("product_id = '".$this->product_id."'");
		$result_select = $this->get_data();
		$row_select = mysql_fetch_object($result_select);
		$old_order_id = $row_select->order_id;
		$old_category_id = $row_select->category_id;	
		$debug_string.= "<br />old_order_id:$old_order_id";
		$debug_string.= "<br />old_category_id:$old_category_id";
		$debug_string.= "<br />this nu_order_id:".$this->category_id;

		//build update query
		$this->db_table = $this->DB_TABLE_PRODUCTS;
		$this->set_update();
		$this->add_update_value("category_id",$this->select_category);	
		$this->add_update_value("product_name",$this->product_name);		
		$this->add_update_value("company_name",$this->company_name);
		$this->add_update_value("address",$this->address);
		$this->add_update_value("address2",$this->address2);
		$this->add_update_value("address3",$this->address3);
		$this->add_update_value("postcode",$this->postcode);
		$this->add_update_value("phone",$this->phone);
		$this->add_update_value("fax",$this->fax);
		$this->add_update_value("email",$this->email);
		$this->add_update_value("website",$this->website);
		$this->add_update_value("contact_name",$this->contact_name);
		$this->add_update_value("contact_position",$this->contact_position);
		$this->add_update_value("contact_name2",$this->contact_name2);
		$this->add_update_value("contact_position2",$this->contact_position2);
		$this->add_update_value("contact_name3",$this->contact_name3);
		$this->add_update_value("contact_position3",$this->contact_position3);
		$this->add_update_value("pr_agency",$this->pr_agency);
		$this->add_update_value("slug",$this->slug);		
		$this->add_update_value("company_profile_summary",$this->company_profile_summary, "HTML");
		$this->add_update_value("company_profile",$this->company_profile, "HTML");
		//$this->add_update_value("company_profile",$this->company_profile);	
		$this->add_update_value("visible",$this->visible);
		$this->add_update_value("featured",$this->featured);
		$this->add_update_value("default_code",$this->default_code);
		$this->add_update_value("default_price",$this->default_price);
		$this->add_update_value("default_rrp",$this->default_rrp);
		$this->add_update_value("rrp_text",$this->rrp_text);
		$this->add_update_value("meta_title",$this->meta_title);
		$this->add_update_value("meta_keywords",$this->meta_keywords);
		$this->add_update_value("meta_description",$this->meta_description);
		
		$this->set_where("product_id = '".$this->product_id."'");
		//call method to create update query - returns the row_id for the item
		$update_id = $this->update_data();
		$debug_string.= "<br />update_id:$update_id";		
		
		if (isset($update_id)):	
			$this->update_product_id = $update_id;
			$this->update_success_flag = 1;			
			$fcn_msg = "Product has been Updated.<br />";	
				
			//*** PRODUCT ORDERING ***								
			//*** IS PRODUCT BEING MOVED TO A NEW CATEGORY ***
			//if so then the above has reordered the new category and we need to sort out the old one left behind
			if ($this->category_id != "" && $old_category_id != "" && $this->category_id != $old_category_id)
			{
				//update order of OLD cat left behind - any ids greater than the one being moved REDUCE by one
				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $old_order_id");
				$this->set_where("discontinued != 1");	
				$this->set_where("category_id = '".$old_category_id."'");
				$result_order = $this->update_data(0);	
				
				//JUST MOVING TO THE END SO GET PRODUCT COUNT OF NEW CATEGORY
				$this->set_select("SELECT product_id");
				$this->set_from($this->DB_TABLE_PRODUCTS);
				$this->set_where("category_id = '".$this->category_id."'");
				$this->set_where("discontinued != 1");	
				$result_new = $this->get_data();
				$count_new = $this->numrows; //returns the total number of rows generated

				//update this moved product with new count
				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				$this->add_update_value("order_id",$count_new);
				$this->set_where("product_id = '".$this->product_id."'");
				$result_order = $this->update_data(0);	

				/*				
				//update orders for NEW category - any ids greater than the one being moved in INCREASE by one
				$update_order = "update ".SITE_DB_PREFIX."directory_products set order_id = (order_id + 1)";
				$update_order.= " where order_id >= " . $nu_order_id . " AND discontinued=0 and category_id = " . $category_id;
				$update_order.= " AND product_id != $product_id"; //dont change the order of the one weve just moved
				$debug_string.= "<br />update_order NEW:$update_order";
				$order_result = mysql_query($update_order);	
				*/					
			}
			else //update orders as usual
			{
				//update order_id for this product
				//$this->update_product_order_ids(DEBUG_FCN_DISPLAY_FLAG); //NOT USING ON EDIT
			}
			//*** IS PRODUCT BEING MOVED TO A NEW CATEGORY? ***
			//*** PRODUCT ORDERING ***
			
			
			//*** VARIATION ***
			$this->set_select();
			$this->set_from($this->DB_TABLE_VARIATIONS);
			$this->set_where("product_id = '".$this->product_id."'");
			$result_var = $this->get_data();
			$count_var = $this->numrows; //returns the total number of rows generated			

			//if only one-type update it
			if ($count_var == 1):
				$this->db_table = $this->DB_TABLE_VARIATIONS;
				$this->set_update();
				$this->add_update_value("price",$this->default_price);
				$this->add_update_value("code",$this->default_code);
				$this->set_where("product_id = '".$this->product_id."'");
				$update_var_id = $this->update_data(0);	
				if (!isset($update_var_id)):
					$fcn_msg = "Failed to update product.";
				endif;			
		
				$debug_string.= "<br />update_var_id:$update_var_id";
			endif;
			//*** VARIATION ***			
		else:	
			$fcn_msg = "Error: Could not update Product.<br />";
			$this->submit = "";		
		endif;	
			
		//debug code
		if ($debug==1)
		{
			echo "<div>update_product testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end update_product
	//---------- END function to edit product ----------


	//---------- START function to delete product ----------
	//ACTUALLY SETS IT TO DISCONTINUED SO THAT ALL OLD ORDERS STILL SHOW THE PRODUCT ETC
	function delete_product($restore_code, $debug=0)
	{
		$delete_product = 0;
		$fcn_msg = "";	
		$debug_string = "";	
	
		//get product
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("discontinued != 1");	
		$this->set_where("product_id = '".$this->product_id."'");
		$result_select = $this->get_data();
		$count_select = $this->numrows; //returns the total number of rows generated
		if ( $result_select && $count_select > 0):
			$row_select = mysql_fetch_object($result_select);
			$db_order_id = $row_select->order_id;
			$db_category_id = $row_select->category_id;
			$debug_string.= "<br />db_order_id:$db_order_id";
			$debug_string.= "<br />db_category_id:$db_category_id";
			
			//set item out of stock 
//			//$sql_updt_vars="update ".SITE_DB_PREFIX."variations SET visible = '0', stock_status = '0', stock_level = '0' where product_id='$product_id'";
//			$sql_updt_vars="update ".SITE_DB_PREFIX."variations SET visible = '0', stock_status = '0', restore_code='$restore_code' where product_id='$product_id' AND visible = '1'";
//			$debug_string .= "<br />" . $sql_updt_vars . "<br />";
//			$result_updt_vars = mysql_query($sql_updt_vars);
			
			//set item discontinued
			$this->db_table = $this->DB_TABLE_PRODUCTS;
			$this->set_update();
			$this->add_update_value("discontinued", "1");		
			$this->add_update_value("restore_code", $restore_code);
			$this->add_update_value("order_id", "0"); //remove the order id when deleting - set to 0
			//$this->add_update_value("visible", "0"); //dont set as can restore to its previous value
			$this->set_where("product_id = '".$this->product_id."'");	
			$this->set_where("discontinued != 1");	
			$update_prod_id = $this->update_data(0);	
			if (isset($update_prod_id)):	
				$delete_product = 1;
				$this->delete_success_flag = 1;
				$fcn_msg = "Product Deleted";

				//UPDATE PRODUCT ORDERS
				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $db_order_id");
				$this->set_where("discontinued != 1");
				$this->set_where("category_id = $db_category_id");	
				$result_order = $this->update_data(0);	
				
				//DELETE ALL VARIATIONS FOR THIS PRODUCT
				//REMEMBER THAT DELETE JUST SETS THEM TO DISCONTINUED
				//but still we dont want the variations available once the product has been removed
	
				//instantiate catalogue object
				$this->set_select();
				$this->set_from($this->DB_TABLE_VARIATIONS);
				$this->set_where("product_id = '".$this->product_id."'");
				$this->set_where("discontinued != 1");	
				$result_var = $this->get_data();
				$count_var = $this->numrows; //returns the total number of rows generated
		
				if ($result_var && $count_var > 0):
					while ($row_var = mysql_fetch_object($result_var)):
						$this->variation_id($row_var->variation_id); //set var id for delete
						$this->delete_var($restore_code, DEBUG_FCN_DISPLAY_FLAG);
					endwhile;
				endif;
				///DELETE ALL VARIATIONS FOR THIS PRODUCT						
			else:	
				$fcn_msg = "Error: Product not deleted";	
			endif;							
		else:
			$fcn_msg = "Invalid product.";
		endif;		
					
		//debug code
		if ($debug==1)
		{
			echo "<br />delete_product testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $delete_product;
	}//end delete_product
	//---------- END function to delete product ----------

	
	//---------- START function to restore a discontinued product ----------
	//Simple enuff just set the flag back to zero. whack it at the end of the category order id
	//user then has to change the visible/stock etc from defaults
	function restore_product($restore_code, $debug=0)
	{
		$fcn_msg = "";	
		$debug_string = "";	

		//get product
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("product_id = '".$this->product_id."'");
		$result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated

		if ( $result && $count > 0 ):
			$row = mysql_fetch_object($result);

			$db_discontinued = $row->discontinued;
			$db_restore_code = $row->restore_code;
			$db_category_id = $row->category_id;
			$db_product_name = $row->product_name;
			$debug_string.= "<br />db_discontinued:$db_discontinued";
			$debug_string.= "<br />db_category_id:$db_category_id";			
			
			if ($db_discontinued != 1) 
			{
				$fcn_msg = "Product has not been discontinued.";
			}
			else if ($db_restore_code != $restore_code)
			{
				$fcn_msg = "Product does not match restore code.";
			}
			else
			{
				//get last order_id to reset product order to the end of its category
				$this->set_select();
				$this->set_from($this->DB_TABLE_PRODUCTS);
				$this->set_where("discontinued != 1");		
				$this->set_where("order_id IS NOT NULL");
				$this->set_where("category_id = '".$db_category_id."'");
				$order_result = $this->get_data();
				$order_count = $this->numrows; //returns the total number of rows generated
				$this->nu_order_id = ($order_count + 1); //set order_id for new record based on total count		
				$debug_string.= "<br />this->nu_order_id:'".$this->nu_order_id."'";	

				//DO THE RESTORE FOR THIS PRODUCT
				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				//$this->add_update_value("visible",$this->visible); //force visibility or leave as it was
				$this->add_update_value("order_id",$this->nu_order_id);
				$this->add_update_value("discontinued",0);
				$this->add_update_value("restore_code","");
				$this->set_where("product_id = '".$this->product_id."'");
				$this->set_where("discontinued = 1");
				$this->set_where("restore_code = '$restore_code'");		
				$update_prod_id = $this->update_data(0);		

				if (isset($update_prod_id)):
					$this->restore_success_flag = 1;
					$fcn_msg = "Product Restored";
	
					//RESTORE ALL VARIATIONS FOR THIS PRODUCT	
					//Only products where the restore_code matches the category restore code	
					$this->set_select();
					$this->set_from($this->DB_TABLE_VARIATIONS);
					$this->set_where("product_id = '".$this->product_id."'");
					$this->set_where("discontinued = 1"); //only restore deleted records
					$this->set_where("restore_code = '$restore_code'"); //only restore where code matches that of original item being restored	
					$result_var = $this->get_data();
					$count_var = $this->numrows; //returns the total number of rows generated
			
					if ($result_var && $count_var > 0):
						while ($row_var = mysql_fetch_object($result_var)):
							$this->variation_id($row_var->variation_id); //set var id for restore					
							$this->restore_variation($restore_code, DEBUG_FCN_DISPLAY_FLAG);
						endwhile;
					endif;
					//RESTORE ALL VARIATIONS FOR THIS PRODUCT	
				else:	
					$fcn_msg = "Error: Product not restored";	
				endif;
				//DO THE RESTORE FOR THIS PRODUCT		
			}//end db_discontinued		
		else:
			$fcn_msg = "Invalid product.";
		endif;	
			
		//debug code
		if ($debug==1)
		{
			echo "<br />restore_product testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;
	}//end restore_product
	//---------- END function to restore a discontinued product ----------
		
	
	//---------- START function to test product input ----------
	function test_product_input(&$update_test_flag, $debug=0)
	{					
		if ($update_test_flag == "")
			$update_test_flag = "yes"; 
		
		$fcn_msg = "";
		
		//test not blank
		if ($this->select_category == "" || $this->select_category == 0):
			$fcn_msg.= "* Please choose category<br />";	
			$update_test_flag = "no";
		endif;

		//test not blank
		if ($this->product_name == ""):
			$fcn_msg.= "* Please enter product name<br />";	
			$update_test_flag = "no";
		endif;
		
		//test not blank
		if ($this->company_name == ""):
			$fcn_msg.= "* Please enter company name<br />";	
			$update_test_flag = "no";
		endif;
	
		//test prod name doesnt exist in this category make sure Product name is not duplicated - by mistake or by refresh button!
		$this->set_select("SELECT product_name");
		$this->set_from($this->DB_TABLE_PRODUCTS);	
		//NEED to convert the name as it would be stored in the DB to test for duplicates correctly
		$this->set_where("product_name = '".$this->cleanstring($this->product_name)."'");
		$this->set_where("category_id = '".$this->select_category."'");
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
		if (!is_numeric($this->default_price))
		{
			$fcn_msg.= "* Please enter default price as a number<br />";	
			$update_test_flag = "no";
		}					
			
		//test number
		if ($this->default_rrp != "" && !is_numeric($this->default_rrp))
		{
			$fcn_msg.= "* Please enter default rrp as a number<br />";	
			$update_test_flag = "no";
		}				
					
		//message
		if ($update_test_flag == "no"): 
			$fcn_msg = "Error Occured:<br />".$fcn_msg;
			$this->submit = "";
		endif;
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_product_input testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//end test_product_input	
	//---------- END function to test product input   ----------
		

	//---------- START function to return product from price -----------
	//cheapest value of all variation prices
	function get_prod_fromprice($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	
	
		//GET FROM PRICE FROM VARIATIONS TABLE
		$this->set_select();
		$this->set_from($this->DB_TABLE_VARIATIONS);
		$this->set_where("product_id = '".$this->product_id."'");
		$this->set_where("visible = 1");
		$this->set_orderby("price ASC");
		$result_from = $this->get_data();
		$count_from = $this->numrows; //returns the total number of rows generated
		
		if ( $result_from && $count_from > 0):
			$row_from = mysql_fetch_object($result_from);
			$fcn_msg = $row_from->price;
		endif;
		
		//debug code
		if ($debug==1)
		{
			echo "<div>get_prod_fromprice testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end get_prod_fromprice
	//---------- END function to return product from price -----------	


	//---------- START function to return product link -----------
	//take product_id return a link based on mod_rewrite setting
	function get_prod_link($slug, $debug=0)
	{
		$fcn_msg = "";	
		$debug_string = "";
		
		if (MOD_REWRITE == 1)
		{
			$fcn_msg = "/product/$slug/";
		}
		else
		{
			$fcn_msg = "/product.php?product_id=$slug";
		}		
		
		//debug code
		if ($debug==1)
		{
			echo "<div>get_prod_link testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end get_prod_link
	//---------- START function to return product link -----------	


	//---------- START function to return product image link -----------
	//take product_id to return image html
	//can choose specific image or first one for the prod. can specify width % to rescale if you want. thumb or full image
	function get_prod_image($product_id, $path, $path_default="", $image_id=0, $type="full", $width_perc=0, $image_class="", $debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	
		$image_add = "";

		//type - thumbnail or full image
		if ($type == "thumb")
			$image_type = "thumb_";		
		$debug_string.="<br />image_type:$image_type<br />";

		//path to image folder
		if ($path == "")
			$path = "/img/site/directory-product/";	

		//path to default image file
		if ($path_default == "")
			$path_default = "/img/site/directory-product/".$image_type."default_product.gif"; //$path.$image_type."default.gif";

		//border
		$image_add .= "border=\"0\" ";

		//class		
		if ($image_class != "")
			$image_add .= "class=\"$image_class\" ";
			
						
		//*************************************** IMAGE DISPLAY CODE **************************************************								
		
		//image query - either first image or selected image for the specific variation	id		
		if ($image_id != NULL && $image_id != "" && $image_id>0):
			$this->set_select();
			$this->set_from("directory_product_images");
			$this->set_where("id = '".$image_id."'");			
		else:
			$this->set_select();
			$this->set_from("directory_product_images");
			$this->set_where("product_id = '".$product_id."'");	
			$this->set_orderby("order_id");		
		endif;
		
		$result_ims = $this->get_data();
		$count_ims = $this->numrows; //returns the total number of rows generated	
	
		if ($result_ims && $count_ims > 0)
		{
			$row_image = mysql_fetch_object($result_ims);
	
			//percentage of width
			if ($width_perc > 0)
			{
				list($im_width, $im_height, $im_type, $im_attr) = getimagesize($_SERVER["DOCUMENT_ROOT"].$path.$image_type.$row_image->image_path);
				$debug_string.="<br />im_width:$im_width im_height:$im_height";
				$im_width = $im_width * ($width_perc/100);
				$debug_string.="<br />im_width:$im_width im_height:$im_height";
				
				$image_add .= "width=\"$im_width\" ";
			}//end if	
	
			if (mysql_num_rows($result_ims) > 0)
			{
				$fcn_msg = "<img src=\"".$path.$image_type.$row_image->image_path."\" ".$image_add."alt=\"".$row_image->image_name."\" />";			
			}//end if (mysql_num_rows($result_ims) > 0)
		}
		else
		{
/*			//percentage of width
			if ($width_perc > 0)
			{
				list($im_width, $im_height, $im_type, $im_attr) = getimagesize($_SERVER["DOCUMENT_ROOT"].$path_default);
				$debug_string.="<br />im_width:$im_width im_height:$im_height";
				$im_width = $im_width * ($width_perc/100);
				$debug_string.="<br />im_width:$im_width im_height:$im_height";
				
				$image_add .= "width=\"$im_width\" ";
			}//end if		*/				
		
		//	$fcn_msg = "<img src=\"".$path_default."\" ".$image_add."alt=\"".$row_image->image_name."\" />";
		}//end if ($result_ims
		//*************************************** IMAGE DISPLAY CODE **************************************************	
	
		//debug code
		if ($debug==1)
		{
			echo "<div>get_prod_image testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end get_prod_image
	//---------- START function to return product image link -----------


	//*****---------- QUERY FUNCTIONS ----------*****

	// function	
	//*** retrieve a list of all products - for the CMS ***
	function select_product_list_cms($order_by = "")
	{
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
				
		//$this->set_where("discontinued != 1");	
		//*** only query non-deleted products ***
		if ($_SESSION ['s_show_deleted_products'] != "yes"):
			$this->set_where("discontinued != 1");	
		else: //include this to show just deleted rather than show all
			$this->set_where("discontinued = 1");		
		endif;		
		
		if ($_SESSION['dir_prod_search_vars']['category_id'] != "all"):
			$this->set_where("category_id = '".$_SESSION['dir_prod_search_vars']['category_id']."'");	
		endif;
		
		//LETTER FILTER
		if ( !empty($_SESSION['dir_prod_search_vars']['letter_value']) ):		
			$this->set_where("SUBSTRING(product_name,1,1) = '" . strtolower($_SESSION['dir_prod_search_vars']['letter_value']) . "'");
		endif;

		//SEARCH FILTER
		if ( !empty($_SESSION['dir_prod_search_vars']['search_selection']) && !empty($_SESSION['dir_prod_search_vars']['search_value']) ):		
			$this->set_where($_SESSION['dir_prod_search_vars']['search_selection'] . " LIKE '%" . mysql_escape_string($_SESSION['dir_prod_search_vars']['search_value']) . "%'");
		endif;
		
		//ORDER RESULTS
		if ($_SESSION['dir_prod_search_vars']['order_by'] != "" && $_SESSION['dir_prod_search_vars']['order_type'] != ""):
			$this->set_orderby($_SESSION['dir_prod_search_vars']['order_by'] . " " . $_SESSION['dir_prod_search_vars']['order_type']);	
		else:
			$this->set_orderby("order_id ASC");
		endif;	
		
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
			$err_msg = 'No Products could be found';
			return array(1,$err_msg); //on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	
	}//select_product_list_cms
	//*** retrieve a list of all products ***

	//*****---------- QUERY FUNCTIONS ----------*****

		
	//******************************* PRODUCT FUNCTIONS *********************************
	//***********************************************************************************



	//***********************************************************************************
	//*************************** PRODUCT ORDERING FUNCTIONS ****************************

	//---------- START function to update product orders ----------
	function update_product_order_ids($debug=0)
	{
		$fcn_msg = "";
		$debug_string = "";
		$debug_string.="<br />*** reset_product_orders ***<br />";
	
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("discontinued != 1");		
		$this->set_where("product_id = '".$this->product_id."'");
		$result = $this->get_data();
		$row = mysql_fetch_object($result);
		$this->order_id = $row->order_id;
		$this->category_id = $row->category_id;	
		$debug_string.= "<br />this order_id:".$this->order_id;
		$debug_string.= "<br />this category_id:".$this->category_id;

		if ($this->order_id > $this->nu_order_id): // order higher	
			$this->db_table = $this->DB_TABLE_PRODUCTS;
			$this->set_update();
			$this->add_update_value("order_id", "(order_id + 1)", "MYSQL_FUNCTION");
			$this->set_where("discontinued != 1");
			$this->set_where("order_id >= ".$this->nu_order_id);
			$this->set_where("order_id < ".$this->order_id);
			$this->set_where("category_id = ".$this->category_id);	
			$result_update_id = $this->update_data(0);
			$debug_string.= "<br />result_update_id:$result_update_id";		

			if (isset($result_update_id)):	
				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				$this->add_update_value("order_id",$this->nu_order_id);
				$this->set_where("discontinued != 1");	
				$this->set_where("product_id = ".$this->product_id);	
				$result_order_id = $this->update_data(0);
				$debug_string.= "<br />result_order_id:$result_order_id";

				if (isset($result_order_id)):	
					$fcn_msg = "Products Re-ordered";
				endif;
			endif;				
		elseif ($this->order_id < $this->nu_order_id): // order lower
			$this->db_table = $this->DB_TABLE_PRODUCTS;
			$this->set_update();
			$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
			$this->set_where("discontinued != 1");	
			$this->set_where("order_id > ".$this->order_id);
			$this->set_where("order_id <= ".$this->nu_order_id);
			$this->set_where("category_id = ".$this->category_id);	
			$result_update_id = $this->update_data(0);
			$debug_string.= "<br />result_update_id:$result_update_id";			
			
			if (isset($result_update_id)):
				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				$this->add_update_value("order_id",$this->nu_order_id);
				$this->set_where("discontinued != 1");	
				$this->set_where("product_id = ".$this->product_id);
				$result_order_id = $this->update_data(0);
				$debug_string.= "<br />result_order_id:$result_order_id";

				if (isset($result_order_id)):	
					$fcn_msg = "Products Re-ordered";	
				endif;			
			endif;						
		endif;

		$debug_string.="<br />*** reset_product_orders ***<br />";
		$this->success_flag = 1;			
		
		//debug code
		if ($debug==1)
		{
			echo "<div>update_product_order_ids testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;	
	}//update_product_order_ids
	//---------- END function to update product orders ----------
	
	
	//---------- START function to reset ALL product orders alphabetically within each category ----------
	function reset_product_orders($category_id = 0, $order_by = "", $debug=0)
	{
		$fcn_msg = 0;		
		$debug_string = "";
		$debug_string.= "<br />*** reset_product_orders ***<br />";
	
		$this->set_select("SELECT distinct category_id");
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("discontinued != 1");		
		//if only resetting one specific category
		if ($this->category_id > 0)
			$this->set_where("category_id = '".$this->category_id."'");		
		$this->set_orderby("category_id");
		$result_1 = $this->get_data();		
		$count_1 = $this->numrows; //returns the total number of rows generated		
		
		if (!$result_1 || $count_1 == 0):			
			$fcn_msg = 0;	
		else:
			while ($row_1 = mysql_fetch_object($result_1)):			
				$this->set_select("SELECT product_id");
				$this->set_from($this->DB_TABLE_PRODUCTS);
				$this->set_where("discontinued != 1");	
				$this->set_where("category_id = '".$row_1->category_id."'");
				if ($order_by != "")
					$this->set_orderby("$order_by");	
				else
					$this->set_orderby("product_name");			
				$result_2 = $this->get_data(0);
				$count = 1;
		
				while ($row_2 = mysql_fetch_object($result_2)):	
					//SQL UPDATE METHOD CALLS
					$this->db_table = $this->DB_TABLE_PRODUCTS;
					$this->set_update();
					$this->add_update_value("order_id", "$count");
					$this->set_where("product_id = '".$row_2->product_id."'");	
					$result_update_id = $this->update_data(0);
					//if (isset($result_update_id)):
					//endif;
					
					$count++;
				endwhile;
				
				$fcn_msg = 1;
				$this->success_flag = 1;		
			endwhile;				
		endif;			
		
		//SET DISCONTINUED PRODUCTS TO HAVE NO ORDER ID
		$this->db_table = $this->DB_TABLE_PRODUCTS;
		$this->set_update();
		$this->add_update_value("order_id",0);
		$this->set_where("discontinued = 1");	
		$result_update_disc_id = $this->update_data(0);	
		$count++;
		
		$debug_string.="<br />*** reset_product_orders ***<br />";
		
		//debug code
		if ($debug==1)
		{
			echo "<div>reset_product_orders testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;	
	}//reset_product_orders
	//---------- END function to reset ALL product orders alphabetically within each category ----------

	//*************************** PRODUCT ORDERING FUNCTIONS ****************************
	//***********************************************************************************


	//******************************* VARIATION FUNCTIONS *******************************
	//***********************************************************************************
	
	//---------- START function to add variation ----------
	function add_var($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";
	
		//Insert the record
		$this->db_table = $this->DB_TABLE_VARIATIONS;
		$this->set_insert($this->DB_TABLE_VARIATION_FIELDS);
		//add argument to insert values array
		$this->add_insert_value($this->product_id);
		$this->add_insert_value($this->code);
		$this->add_insert_value($this->var1);
		$this->add_insert_value($this->var2);
		$this->add_insert_value($this->var3);
		$this->add_insert_value($this->price);
		$this->add_insert_value($this->rrp);			
		$this->add_insert_value($this->stock_status);		
		$this->add_insert_value($this->visible);
		$this->add_insert_value($this->stock_level);
		$this->add_insert_value($this->image_id);
		//call method to create insert query - returns the row_id for the inserted item
		$insert_id = $this->insert_data();

		if ($insert_id):
			$fcn_msg .= "Variation has been Added."; 
		else:
			$fcn_msg .= "Could not add variation."; 		
		endif;		
	
		//debug code
		if ($debug==1)
		{
			echo "<div>add_var testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end add_var
	//---------- END function to add variation ----------
	
	
	//---------- START function to edit variation ----------
	function update_var($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	

		//PRODUCT
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("product_id = '".$this->product_id."'");
		$result_prod = $this->get_data();
		$count_prod = $this->numrows; //returns the total number of rows generated
				
		if ($result_prod && $count_prod == 1):
			$row_prod = mysql_fetch_object($result_prod);
			$num_vars = $row_sub->variation_fields;
			
			//VARIATION
			$this->set_select();
			$this->set_from($this->DB_TABLE_VARIATIONS);
			$this->set_where("variation_id = '".$this->variation_id."'");
			$result_var = $this->get_data();
			$count_var = $this->numrows; //returns the total number of rows generated			
			
			if ($result_var && $count_var == 1):
				$row_var = mysql_fetch_object($result_var);
				$variation_id = $row_sub->variation_id;
				
				//update
				$this->db_table = $this->DB_TABLE_VARIATIONS;
				$this->set_update();
				$this->add_update_value("code",$this->code);
				$this->add_update_value("var1",$this->var1);
				$this->add_update_value("var2",$this->var2);
				$this->add_update_value("var3",$this->var3);
				$this->add_update_value("image_id",$this->image_id);
				$this->add_update_value("price",$this->price);
				$this->add_update_value("rrp",$this->rrp);
				$this->add_update_value("stock_status",$this->stock_status);
				$this->add_update_value("visible",$this->visible);				
				$this->set_where("variation_id = '".$this->variation_id."'");			
				$update_var_id = $this->update_data(0);	
				
				if (isset($update_var_id)):	
					$this->update_variation_id = $update_var_id;
					$this->update_success_flag = 1;	
					$fcn_msg = "Variation has been Updated.<br />";
				else:	
					$fcn_msg = "Error: could not update variation.<br />";
					$this->submit = "";		
				endif;						
			else:
				$fcn_msg = "Error: Invalid product - could not update variation.";
			endif;			
		else:
			$fcn_msg = "Error: Invalid product - could not update variation.";
		endif;
	
		//debug code
		if ($debug==1)
		{
			echo "<div>update_var testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end update_var
	//---------- END function to edit variation ----------


	//---------- START function to delete variation ----------
	function delete_var($restore_code, $debug=0)
	{	
		$delete_var = 0;
		$debug_string = "";
		$fcn_msg = "";
		
/*		//ACTUALLY DELETE - but this messes up order history
		$sqlquery = "DELETE FROM ".SITE_DB_PREFIX."variations";
		$sqlquery.= " WHERE variation_id = '".$this->variation_id."'";
		$debug_string.= "<br />sqlquery:$sqlquery";
		$result = mysql_query($sqlquery);
		
		if (!$result):
			 $fcn_msg = "Error: Could not delete.";
		else:
			$fcn_msg = "Variation deleted.";
		endif;
*/
		
		//get product
		$this->set_select();
		$this->set_from($this->DB_TABLE_VARIATIONS);
		$this->set_where("discontinued != 1");	
		$this->set_where("variation_id = '".$this->variation_id."'");	
		$result_select = $this->get_data();
		$count_select = $this->numrows; //returns the total number of rows generated
		if ( $result_select && $count_select > 0):
			$row_select = mysql_fetch_object($result_select);
			$db_order_id = $row_select->order_id;
			$db_product_id = $row_select->product_id;
			$debug_string.= "<br />db_order_id:$db_order_id";
			$debug_string.= "<br />db_product_id:$db_product_id";
			
			//set item out of stock 
//			//$sql_updt_vars="update ".SITE_DB_PREFIX."variations SET visible = '0', stock_status = '0', stock_level = '0' where product_id='$product_id'";
//			$sql_updt_vars="update ".SITE_DB_PREFIX."variations SET visible = '0', stock_status = '0', restore_code='$restore_code' where product_id='$product_id' AND visible = '1'";
//			$debug_string .= "<br />" . $sql_updt_vars . "<br />";
//			$result_updt_vars = mysql_query($sql_updt_vars);
			
			//set item discontinued
			$this->db_table = $this->DB_TABLE_VARIATIONS;
			$this->set_update();
			$this->add_update_value("discontinued", "1");		
			$this->add_update_value("restore_code", $restore_code);
			//$this->add_update_value("order_id", "0"); //remove the order id when deleting - set to 0
			//$this->add_update_value("visible", "0"); //dont set as can restore to its previous value
			$this->set_where("variation_id = '".$this->variation_id."'");	
			$this->set_where("discontinued != 1");	
			$update_prod_id = $this->update_data(0);
				
			if (isset($update_prod_id)):	
				$delete_var = 1;
				$fcn_msg = "Variation Deleted.";

				//ordering
/*				$this->db_table = $this->DB_TABLE_PRODUCTS;
				$this->set_update();
				$this->add_update_value("order_id", "(order_id - 1)", "MYSQL_FUNCTION");
				$this->set_where("order_id > $db_order_id");
				$this->set_where("discontinued != 1");
				$this->set_where("category_id = $db_category_id");	
				$result_order = $this->update_data(0);	*/				
			else:	
				$fcn_msg = "Error: Variation not deleted.";	
			endif;							
		else:
			$fcn_msg = "Invalid Variation.";
		endif;		
				 
		//debug code
		if ($debug==1)
		{
			echo "<div>delete_var testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $delete_var; //$fcn_msg;			 
	}//end delete_var
	//---------- END function to delete variation ----------


	//---------- START function to restore a discontinued variation ----------
	//Simple enuff just set the flag back to zero. whack it at the end of the order id but currently no ordering
	function restore_variation($restore_code, $debug=0)
	{
		$fcn_msg = "";	
		$debug_string = "";	

		//get product
		$this->set_select();
		$this->set_from($this->DB_TABLE_VARIATIONS);
		$this->set_where("variation_id = '".$this->variation_id."'");
		$result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated

		if ( $result && $count > 0 ):
			$row = mysql_fetch_object($result);

			$db_discontinued = $row->discontinued;
			$db_restore_code = $row->restore_code;
			$db_product_id = $row->product_id;
			$debug_string.= "<br />db_discontinued:$db_discontinued";
			$debug_string.= "<br />db_restore_code:$db_restore_code";
			$debug_string.= "<br />db_product_id:$db_product_id";			
			
			if ($db_discontinued != 1):
				$fcn_msg = "Variation has not been discontinued.";
			elseif ($db_restore_code != $restore_code):
				$fcn_msg = "Variation does not match restore code.";
			else:
				/*
				//get last order_id to reset product order to the end of its category
				$this->set_select();
				$this->set_from($this->DB_TABLE_VARIATIONS);
				$this->set_where("discontinued != 1");		
				$this->set_where("order_id IS NOT NULL");
				$this->set_where("product_id = '".$db_product_id."'");
				$order_result = $this->get_data();
				$order_count = $this->numrows; //returns the total number of rows generated
				$this->nu_order_id = ($order_count + 1); //set order_id for new record based on total count		
				$debug_string.= "<br />this->nu_order_id:'".$this->nu_order_id."'";	
				*/
				
				//update product info
				$this->db_table = $this->DB_TABLE_VARIATIONS;
				$this->set_update();
				//$this->add_update_value("order_id",$this->nu_order_id); // no orders atm
				$this->add_update_value("discontinued",0);
				$this->add_update_value("restore_code","");
				$this->set_where("variation_id = '".$this->variation_id."'");
				$this->set_where("discontinued = 1");
				$this->set_where("restore_code = '$restore_code'");		
				$update_prod_id = $this->update_data(0);		

				if (isset($update_prod_id)):
					$fcn_msg = "Variation Restored";			
				else:	
					$fcn_msg = "Error: Variation not restored";	
				endif;			
			endif;	
		else:
			$fcn_msg = "Invalid Variation.";
		endif;	
			
		//debug code
		if ($debug==1)
		{
			echo "<br />restore_variation testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;
	}//end restore_variation
	//---------- END function to restore a discontinued variation ----------


	//---------- START function to test category input ----------
	function test_variation_input(&$update_test_flag, $debug=0)
	{						
		if ($update_test_flag == "")
			$update_test_flag = "yes"; 
		
		$fcn_msg = "";

		//test not blank
		if ($this->code == ""):
			$fcn_msg.= "* Please enter Code<br />";	
			$update_test_flag = "no";
		endif;

		//test name doesnt exist in this category make sure name is not duplicated - by mistake or by refresh button!
		$this->set_select("SELECT variation_id");
		$this->set_from($this->DB_TABLE_VARIATIONS);	
		//NEED to convert the name as it would be stored in the DB to test for duplicates correctly
		$this->set_where("code = '".$this->cleanstring($this->code)."'");
		$this->set_where("product_id = '".$this->product_id."'");
		if ($this->action == "edit")
			$this->set_where("variation_id != '".$this->variation_id."'");
		$result = $this->get_data();
		$count = $this->numrows; //returns the total number of rows generated
		if ($count > 0):
			$fcn_msg.= "Variation Code already exists for this Product.<br />";	
			$update_test_flag = "no";			
			$this->submit = "";
		endif;	

		//test variation combination doesnt already exist for this product
		$this->set_select("SELECT variation_id");
		$this->set_from($this->DB_TABLE_VARIATIONS);	
		$this->set_where("product_id = '".$this->product_id."'");
		if ($this->num_vars > 0) //product has var1 set
			$this->set_where("var1 = '".$this->var1."'");
		if ($this->num_vars > 1) //product has var2 set
			$this->set_where("var2 = '".$this->var2."'");
		if ($this->num_vars > 2) //product has var3 set	
			$this->set_where("var3 = '".$this->var3."'");
		if ($this->action == "edit") //if update then exclude the var we are updating for the test
			$this->set_where("variation_id != '".$this->variation_id."'");			
			
		$result_test = $this->get_data();
		$count_test = $this->numrows; //returns the total number of rows generated
		if ( $result_test && $count_test > 0 ):
			$fcn_msg.= "Variation Combination already exists for this Product.<br />";
			$update_test_flag = "no";
			$this->submit = "";
		endif;
		//do this here ********		
	
		//test number
		if ( !is_numeric($this->price) ):
			$fcn_msg.= "* Please enter valid price<br />";	
			$update_test_flag = "no";
		endif;	
		
		//test number - if rrp supplied then ensure it is valid
		if ( $this->rrp != "" && !is_numeric($this->rrp) ):
			$fcn_msg.= "* Please enter valid rrp<br />";	
			$update_test_flag = "no";
		endif;			
					
		//message
		if ($update_test_flag=="no") 
			$fcn_msg="Error Occured:<br />".$fcn_msg;
		
		//debug code
		if ($debug==1)
		{
			echo "<br />test_variation_input testvar:$debug_string<br />";
		}//end if ($debug==1)				
		return $fcn_msg;		
	}//end test_variation_input	
	//---------- END function to test variation input   ----------	


	//---------- START function to add variation field to product ----------
	function add_var_field($debug=0)
	{	
		$debug_string = "";
		$fcn_msg = "";

		//PRODUCT
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("product_id = '".$this->product_id."'");
		$result_prod = $this->get_data();
		$count_prod = $this->numrows; //returns the total number of rows generated
				
		if ($result_prod && $count_prod == 1):
			$row_prod = mysql_fetch_object($result_prod);
			
			$num_vars = $row_prod->variation_fields;
			$var1_text = $row_prod->var1_text;
			$var2_text = $row_prod->var2_text;
			$var3_text = $row_prod->var3_text;
			$debug_string.= "<br />num_vars:$num_vars";
			$debug_string.= "<br />var1_text:$var1_text";
			$debug_string.= "<br />var2_text:$var2_text";
			$debug_string.= "<br />var3_text:$var3_text";
		endif;
			
		if ($this->var_text == "")
			$fcn_msg = "Variation field is blank.";
		else if ( ($this->var_text == $var1_text) || ($this->var_text == $var2_text) || ($this->var_text == $var3_text) )
			$fcn_msg = "Variation field already exists.";
		else
		{		
			$var_field = "var" . ($num_vars + 1) . "_text";

			//update product info
			$this->db_table = $this->DB_TABLE_PRODUCTS;
			$this->set_update();
			$this->add_update_value("$var_field",$this->var_text);
			$this->add_update_value("variation_fields", "($num_vars + 1)", "MYSQL_FUNCTION");
			$this->set_where("product_id = '".$this->product_id."'");
			$update_prod_id = $this->update_data(0);		

			if (isset($update_prod_id)):
				$fcn_msg = "Variation field added";

				$var = "var" . ($num_vars + 1);
				
				$this->db_table = $this->DB_TABLE_VARIATIONS;
				$this->set_update();
				$this->add_update_value("$var","default");
				$this->set_where("product_id = '".$this->product_id."'");
				$update_var_id = $this->update_data(0);	

				if (isset($update_var_id)):
					//$fcn_msg = "Variations updated.";		
				endif;				
			else:	
				$fcn_msg = "Error: Could not add variation options field.";	
			endif;			
		
		}//end if	
		
		//debug code
		if ($debug==1)
		{
			echo "<div>add_var_field testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;	
	}//end add_var_field
	//---------- END function to add variation field to product ----------


	//---------- START function to edit variation field for product ----------
	function update_var_field($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	

		//update product info
		$this->db_table = $this->DB_TABLE_PRODUCTS;
		$this->set_update();
		$this->add_update_value($this->var_field, $this->var_text);
		$this->set_where("product_id = '".$this->product_id."'");
		$update_prod_id = $this->update_data(0);		

		if (isset($update_prod_id)):
			$this->update_product_id = $update_prod_id;
			$this->update_success_flag = 1;			
			$fcn_msg = "Variation options Updated.";			
		else:	
			$fcn_msg = "Error: Could not update variation options.";	
		endif;	
	
		//debug code
		if ($debug==1)
		{
			echo "<div>update_var_field testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end update_var_field
	//---------- END function to edit variation field for product ----------


	//---------- START function to set all variation prices to default product price ----------
	function update_def_price($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";
	
		$this->db_table = $this->DB_TABLE_VARIATIONS;
		$this->set_update();
		$this->add_update_value("price",$this->default_price);
		$this->set_where("product_id = '".$this->product_id."'");
		$update_var_id = $this->update_data(0);	

		if (isset($update_var_id)):
			$fcn_msg = "Variations set to the default price.";
		else:
			$fcn_msg = "Failed to set default price.";
		endif;		

		//debug code
		if ($debug==1)
		{
			echo "<div>update_def_price testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;			
	}//end update_def_price
	//---------- END function to set all variation prices to default product price ----------

	
	//---------- START function to set all variation rrp's to default product rrp ----------
	function update_def_rrp($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";
		
		$this->db_table = $this->DB_TABLE_VARIATIONS;
		$this->set_update();
		$this->add_update_value("rrp",$this->default_rrp);
		$this->set_where("product_id = '".$this->product_id."'");
		$update_var_id = $this->update_data(0);	

		if (isset($update_var_id)):
			$fcn_msg = "Variations set to the default rrp.";
		else:
			$fcn_msg = "Failed to set default rrp.";
		endif;				
			
		//debug code
		if ($debug==1)
		{
			echo "<div>update_def_rrp testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;			
	}//end update_def_rrp	
	//---------- END function to set all variation rrp's to default product rrp ----------	


	//---------- START function to DISPLAY VARIATION TEXT string ----------
	function variation_text_display($var1, $var2, $var3, $default=0)		
	{
		$variation_text="";
		//variation 1
		if ($var1 != NULL)
		{
			$variation_text.= " ( " . htmlspecialchars($var1);
		
			//variation 2
			if ($var2 != NULL)
			{
				$variation_text.= ", " . htmlspecialchars($var2);
				//if variation 2 then output variation 3
				if ($var3 != NULL)
				{
					$variation_text.= ", " . htmlspecialchars($var3);
				}//end if var3!=NULL
			}//end if var2!=NULL				
			$variation_text.= " )";
		}
		else
		{
			if ($default==1)	
				$variation_text="( standard )";
		}
		//echo "variation_text:'$variation_text'";
		return $variation_text;
	}//end variation_text_display
	//---------- END function to DISPLAY VARIATION TEXT string ----------	


	//*****---------- QUERY FUNCTIONS ----------*****

	// function	
	//*** retrieve a list of all variations ***
	function select_variation_list($order_by = "")
	{
		$this->set_select();
		$this->set_from($this->DB_TABLE_VARIATIONS);

		//$this->set_where("discontinued != 1");	
		//*** only query non-deleted products ***
		if ($_SESSION ['s_show_deleted_variations'] != "yes"):
			$this->set_where("discontinued != 1");	
		else: //include this to show just deleted rather than show all
			$this->set_where("discontinued = 1");		
		endif;	
		
		$this->set_where("product_id = '".$this->product_id."'");	
		
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
			$err_msg = 'No Variations could be found';
			return array(1,$err_msg); //on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	
	}//select_variation_list
	//*** retrieve a list of all variations ***

	//*****---------- QUERY FUNCTIONS ----------*****

	
	//******************************* VARIATION FUNCTIONS *******************************
	//***********************************************************************************



	//***********************************************************************************	
	//******************************* CATALOGUE FUNCTIONS *******************************

	//*****---------- QUERY FUNCTIONS ----------*****	
	
	// function	
	//*** retrieve a list of all products/vars for catalogue ***
	function select_catalogue_list()
	{
		$this->set_select("SELECT DISTINCT(p.product_id), MIN(v.price) AS fromprice, p.*");
		$this->set_from($this->DB_TABLE_PRODUCTS." p, ".$this->DB_PREFIX.$this->DB_TABLE_VARIATIONS." v");
		$this->set_where("p.product_id = v.product_id");
		if(!empty($this->category_id)){
			$this->set_where("p.category_id = '".$this->category_id."'");
		}elseif(!empty($this->slug)){
			$this->set_where("p.slug = '".$this->slug."'");
		}
		$this->set_where("p.visible=1");
		$this->set_where("p.discontinued = 0");	
		$this->set_groupby("p.product_id");
		$this->set_orderby("product_name");
		
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
			$err_msg = 'No Directory Listings could be found';
			return array(1,$err_msg); //on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	
	}//select_catalogue_list
	//*** retrieve a list of all products/vars for catalogue ***


	// function	
	//*** retrieve details for a single directory item product for catalogue ***
	function select_catalogue_item()
	{
		$this->set_select("SELECT DISTINCT(p.product_id), MIN(v.price) AS fromprice, p.*");
		$this->set_from($this->DB_TABLE_PRODUCTS." p, ".$this->DB_PREFIX.$this->DB_TABLE_VARIATIONS." v");
		$this->set_where("p.product_id = v.product_id");
		if ( !empty($this->category_id) ):
			$this->set_where("p.category_id = '".$this->category_id."'");
		elseif (!empty($this->slug)):
			$this->set_where("p.slug = '".$this->slug."'");
		endif;
		if ( !empty($this->product_id) ):
			$this->set_where("p.product_id = '".$this->product_id."'");
		endif;		
		$this->set_where("p.visible=1");
		$this->set_where("p.discontinued = 0");	
		$this->set_groupby("p.product_id");
		$this->set_orderby("product_name");
		
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
			$err_msg = 'Directory Listing could not be found';
			return array(1,$err_msg); //on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	}//select_catalogue_item
	//*** retrieve details for a single directory item product for catalogue ***
	
	//*****---------- QUERY FUNCTIONS ----------*****	
	
	//******************************* CATALOGUE FUNCTIONS *******************************
	//***********************************************************************************	
	
	
	//***********************************************************************************		
	//********************************* OTHER FUNCTIONS *********************************
		
	//---------- START function to get product count - order_id for new product -----------
	//cheapest value of all variation prices
	function get_product_order_count($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	
		$order_id = 0;
	
		//get new order id for new record
		$this->set_select();
		$this->set_from($this->DB_TABLE_PRODUCTS);
		$this->set_where("discontinued != 1");		
		$this->set_where("order_id IS NOT NULL");
		$this->set_where("category_id = '".$this->select_category."'");
		$order_result = $this->get_data();
		$order_count = $this->numrows; //returns the total number of rows generated
		$order_id = ($order_count + 1); //set order_id for new record based on total count		
		$debug_string.= "<br />order_id:'".$order_id."'";	
		
		//debug code
		if ($debug==1)
		{
			echo "<div>get_product_order_count testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $order_id;
	}//end get_product_order_count
	//---------- END function to get product count - order_id for new product -----------	
	
	//********************************* OTHER FUNCTIONS *********************************
	//***********************************************************************************		



	//*******************************************************************************************
	//********---------- JPs PAGING FUNCTION FOR THE AJAX LIVESEARCH ----------******************
	//*******************************************************************************************
	
	function paging($strQuery, $page, $count, $total_pages, $ajax_get_string=NULL)
	{				
		$navigation_list = "";
		$navigation_links = "";
		
		if ($ajax_get_string == NULL)
		{
			$link_url = $_SERVER['PHP_SELF'];
		}
		else
		{
			//set the ajay get string that will be used in the results navigation
			$link_url = "javascript:";
			//use livequery
			//$link_url = $_SERVER['PHP_SELF'];
		}
	
		//write query string that will pass in paging links
		if (empty($strQuery))
		{
			$strQuery = "id=".$id;	
		}
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
		if ( $page == 1 ) 
		{
			# if we are on the first page then "First" and "Prev" should not be links.
			$navigation_links = "<font color=\"#666666\">First</font> | <font color=\"#666666\">Prev</font> | ";
		} 
		else 
		{
			# we are not on page one so "First" and "Prev" can be links
			$prev_page = $page - 1;
			
			if ($ajax_get_string == NULL)
			{
				$navigation_links = "<a href=\"".$link_url."?".$strQuery."&amp;f_page=1\">First</a> | <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$prev_page."\">Prev</a> | ";
			}
			else
			{			
				$navigation_links = "<a href=\"".$link_url."getresults('directory-product-search-xml-results.php', '$strQuery&amp;f_page=1', showresults)\">First</a> | <a href=\"".$link_url."getresults('directory-product-search-xml-results.php', '$strQuery&amp;f_page=$prev_page', showresults)\">Prev</a> | ";				
			}
		}
	
		//loop through total number of pages and add a link to each individual page
		for ($i = 1; $i <= $total_pages; $i++) 
		{
			//show curent page as active link by changin the css class
			if ($ajax_get_string == NULL)
			{
				if ($i == $page)
				{
					$navigation_list .= " <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$i."\" class=\"on\" title=\"".$i."\">$i</a> ";
				}
				else
				{
					$navigation_list .= " <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$i."\" title=\"".$i."\">$i</a> ";
				}
			}
			else
			{			
				if ($i == $page)
				{
					$navigation_list .= " <a href=\"".$link_url."getresults('directory-product-search-xml-results.php', '$strQuery&amp;f_page=$i', showresults)\" class=\"on\" title=\"".$i."\">$i</a> ";
					//$navigation_list .= "<a href=\"".$link_url."&$strQuery&amp;f_page=".$i."\" class=\"on ajax\">".$i."</a> ";
				}
				else
				{
					$navigation_list .= " <a href=\"".$link_url."getresults('directory-product-search-xml-results.php', '$strQuery&amp;f_page=$i', showresults)\" title=\"".$i."\">$i</a> ";
					//$navigation_list .= "<a href=\"".$link_url."&$strQuery&amp;f_page=".$i."\" class=\"ajax\">".$i."</a> ";
				}
			}
		}
		//$navigation_list .= "| ";
	
		# this part will set up the rest of our navigation "Next | Last"
		if ( $page == $total_pages ) 
		{
			# we are on the last page so "Next" and "Last" should not be links
			$navigation_links .= "<font color=\"#666666\">Next</font> | <font color=\"#666666\">Last</font>";
		} 
		else 
		{
			# we are not on the last page so "Next" and "Last" can be links
			$next_page = $page + 1;
			if($ajax_get_string == NULL)
			{
				$navigation_links .= "<a href=\"".$link_url."?".$strQuery."&amp;f_page=".$next_page."\">Next</a> | <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$total_pages."\">Last</a>";
			}
			else
			{
				$navigation_links .= "<a href=\"".$link_url."getresults('directory-product-search-xml-results.php', '$strQuery&amp;f_page=$next_page', showresults)\">Next</a> | <a href=\"".$link_url."getresults('directory-product-search-xml-results.php', '$strQuery&amp;f_page=$total_pages', showresults)\">Last</a>";
			}
		}
		$navigation = $navigation_links . $navigation_list;
			
		$this->paging_links = $navigation_links;
		$this->paging_list = $navigation_list;	
					
		return $navigation;
	}//end function paging
	
	//*******************************************************************************************
	//********---------- JPs PAGING FUNCTION FOR THE AJAX LIVESEARCH ----------******************
	//*******************************************************************************************



	//***********************************************************************************
	//*********************************** STATS *****************************************
	
	//---------- START function to log when a subscription user views details page of a directory product ----------
	//used for directory/user stats
	function log_directory_product_views($debug=0)
	{
		$debug_string = "";
		$fcn_msg = "";	
		
		$debug_string.= "<br />product_id:".$this->product_id;
		$debug_string.= "<br />user_id:".$this->user_id;
		
		//VALID PRODUCT/USER AND USER MUST BE Full Access User
		if ($this->product_id > 0 && $this->user_id > 0 && $_SESSION ['s_user_access'] == 0):
			$this->set_select();
			$this->set_from($this->DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS); 
			$this->set_where("product_id = '".$this->product_id."'");
			$this->set_where("user_id = '".$this->user_id."'");
			$debug_string.= "<br />sql_test:".$this->get_sql()."<br />";
			$result_test = $this->get_data();
			$count_test = $this->numrows;
			if ($count_test > 0): //update
				$this->db_table = $this->DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS;
				$this->set_update();
				$this->add_update_value("count_views", "(count_views + 1)", "MYSQL_FUNCTION");
				$this->add_update_value("date_last_viewed", "NOW()", "MYSQL_FUNCTION");
				$this->set_where("product_id = '".$this->product_id."'");
				$this->set_where("user_id = '".$this->user_id."'");
				//call method to create query - returns the row_id
				$update_id = $this->update_data();			

				if (isset($update_id)):		
					$this->success_flag = 1;
					$this->messages = "Directory Product Views has been updated.";
				else:
					$this->success_flag = 0;
					$this->messages = "Directory Product Views has NOT been updated.";	
				endif;					
			else: //add
				$this->db_table = $this->DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS;
				$this->set_insert($this->DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS_FIELDS);
				//add argument to insert values array
				$this->add_insert_value($this->product_id);		
				$this->add_insert_value($this->user_id);	
				$this->add_insert_value(1);
				$this->add_insert_value('NOW()', "MYSQL_FUNCTION");							
				//call method to create insert query - returns the row_id for the inserted item
				$add_id = $this->insert_data();				

				if (isset($add_id)):
					$this->success_flag = 1;
					$this->messages = "Directory Product Views has been added.";							
				else:
					$this->success_flag = 0;
					$this->messages = "Directory Product Views has NOT been added.";	
				endif;				
			endif;	
		else:
			$this->success_flag = 0;
			$this->messages = "Not a Full Access User.";				
		endif;
		
		$debug_string.= "<br />success_flag:".$this->success_flag;
		$debug_string.= "<br />messages:".$this->messages;
		
		//debug code
		if ($debug==1)
		{
			echo "<div>log_directory_product_views testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_msg;
	}//end log_directory_product_views
	//---------- START function to log when a subscription user views details page of a directory product ----------


	//---------- START function to get subscription user views of directory products stats ----------
	//
	function get_log_directory_product_views_stats($debug=0)
	{
		$err_code = 0;
		$fcn_msg = "";

		//*** GET TOTAL VIEWS ***						
		list($this->msg_err_code, $this->rs_directoryproductviews) = $this->directoryproductviews_list_db();
		if ($this->msg_err_code == 0):	
			$err_code = 0;
		else:		
			$err_code = 1;
			$fcn_msg.= "Error getting custom user count.<br />";
		endif;	
		//*** GET TOTAL VIEWS ***
	
		//*** RECORD SET OF USER VIEWS FOR THIS DIRECTORYPRODUCT ***						
		list($this->directoryproductviews_err_code, $this->rs_directoryproductviews) = $this->directoryproductviews_list_db();
		if ($this->directoryproductviews_err_code == 0):	
			$err_code = 0;
		
			//*** COUNT USERS WHO HAVE VIEWED PRODUCT DETAILS ***
			//echo "ss:".mysql_num_rows($this->rs_directoryproductviews);
			$this->set_select("SELECT sum( count_views ) AS total_views, count( user_id ) AS unique_views");
			$this->set_from($this->DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS);
			$this->set_where("product_id = '".$this->product_id."'");					
			$result = $this->get_data();
			$count = $this->numrows; //returns the total number of rows generated
			
			if ($result && $count == 1):
				$row = mysql_fetch_object($result);
				$this->total_views = $row->total_views;	
				$this->unique_views = $row->unique_views;				
			endif;		
			//*** COUNT USERS WHO HAVE VIEWED PRODUCT DETAILS ***
			
			//total active subscription users in system
			list($this->msg_err_code, $this->active_users) = $this->c_users->count_reg_users();
			
			if ($this->unique_views > 0 && $this->active_users > 0)
				$this->perc_active_views = number_format( ($this->unique_views / $this->active_users) * 100 , 0);
			
			$debug_string.= "<br />total_views:".$this->total_views;
			$debug_string.= "<br />unique_views:".$this->unique_views;
			$debug_string.= "<br />active_users:".$this->active_users;
		else:		
			$err_code = 1;
			$fcn_msg.= "Error getting custom user count.<br />";
		endif;	
		//*** RECORD SET OF USER VIEWS FOR THIS DIRECTORYPRODUCT ***
				
		//debug code
		if ($debug==1)
		{
			echo "<div>get_log_directory_product_views_stats testvar:$debug_string</div>";
		}//end if ($debug==1)	
		
		if ($err_code == 0):
			return array(0,$fcn_msg);	
		else:			
			$fcn_msg = 'An error occured calculating the stats:<br />'.$fcn_msg;
			return array(1,$fcn_msg); //on fail return error code 1 and error message						
		endif;					
	}//end get_log_directory_product_views_stats	
	//---------- END function to get subscription user views of directory products stats ----------


	// public function	
	//*** retrieve a list of all directory product views ***
	public function directoryproductviews_list_db($order_by = "")
	{
		//$this->set_select();
		//$this->set_from($this->DB_TABLE_STATS_DIRECTORY_PRODUCT_VIEWS);

		$var_select = "SELECT *, date_format(dpv.date_last_viewed, ' %W, %d %M %Y, %H:%i') as date_last_viewed_format";
		$this->set_select($var_select);
		$this->set_from("stats_directory_product_views dpv LEFT JOIN ".SITE_DB_PREFIX."users u ON dpv.user_id = u.user_id");
		
		if ($this->product_id > 0)
			$this->set_where("dpv.product_id = '".$this->product_id."'");

		$this->rows_per_page = null;
		$this->set_limit(25);

		//ORDER RESULTS
		$this->set_orderby("count_views DESC");
			
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
			$err_msg = 'No Directory Product Views could be found';
			return array(1,$err_msg); //on fail return error code 1 and error message
		}
		else
		{
			return array(0,$result_check); //on success return error code 0 and array of results				
		}
	}//directoryproductviews_list_db
	//*** retrieve a list of all directory product views ***

	//*********************************** STATS *****************************************	
	//***********************************************************************************


}//class DirectoryCatalogue

?>