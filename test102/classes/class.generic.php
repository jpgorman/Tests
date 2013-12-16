<?php
//contains generic functions that can be used on any site

class generic extends db_object 
{ 
	/////////////////////////////////////////////////
	// PUBLIC VARIABLES
	/////////////////////////////////////////////////

	//declare database constants
	private $DB_TABLE_SETTINGS = "settings";
	
	//declare input variables
	public $page_id = 0;
	
	public $action = "";
	public $submit = "";
	public $add_success_flag = 0;
	public $update_success_flag = 0;
	public $update_test_flag = "yes";	
	public $messages = "";	
	
	
	//Constructor function
	public function __construct() 
	{ 
		//$this->current_page = $current_page; 
	}//function GeneratePagingLinks
	

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
	public function page_id($input_page_id = 0)
	{	
		$this->page_id = trim($input_page_id);
		//debug echo $this->page_id;
    }


	//---------- START function to return text for a given column id for a given table ----------
	public function get_namefromid($table,$col_name,$col_value,$return_col,$quotes=0)
	{
		$lq="";
		$rq="";	
		if ($quotes==1)
		{
			$lq="'";
			$rq="'";
		}
		
		$this->set_select("SELECT ".$return_col);
		$this->set_from($table);
		$this->set_where($col_name.'='.$lq.$col_value.$rq);
		$this->set_limit(1);
		
		//$sql = "select $return_col FROM ".SITE_DB_PREFIX."$table WHERE $col_name=$lq$col_value$rq limit 1";
		$result = $this->get_data();
		$count = $this->numrows;//returns the total number of rows generated
		
		//echo "<br />$sql";		
		if($result&&$count==1)
		{
			$row = $result->fetch_assoc();	
			return trim($row["$return_col"]);
		}
	}//end get_namefromid
	//---------- END function to return text for a given id for a given table ----------

	
	//---------- START convert date format function ----------
	public function convertdate($fromdate,$fromformat,$fromdelimit,$toformat,$todelimit)
	{
		$format1 = split($fromdelimit,$fromformat);
		$format2 = split($todelimit,$toformat);
		$datearray = split($fromdelimit,$fromdate);
		$todate = '';
		for($i=0;$i<count($format2);$i++)
		{
			$todate.=($todate != '')?$todelimit:'';
			$index = array_search ( $format2[$i], $format1);
			$todate.= $datearray[$index];
		}
		return $todate;
	}//end convertdate
	//---------- END convert date format function ----------
		

	//---------- START function to UPDATE column for a given record_id for a given table ----------
	public function update_table($id_col,$id,$table,$col_name,$col_value,$quotes=0)
	{
		$update_table=0;
		
		$lq="";
		$rq="";	
		if ($quotes==1)
		{
			$lq="'";
			$rq="'";
		}
		if ($id!="")
		{
			$sql="UPDATE ".SITE_DB_PREFIX."$table SET"; 
			$sql.=" $col_name=$lq$col_value$rq";
			$sql.=" where $id_col='$id' limit 1";	
			//echo "<br />$sql";
			$result = mysql_query($sql);		
			if($result&&mysql_affected_rows==1)
			{	
				$update_table=1;
			}
		}//end if ($id!="")
		
		return $update_table;
	}//end update_table
	//---------- END function to return text for a given id for a given table ----------


	//**********************************************************************************************
	//********************************** IMAGE FUNCTIONS *******************************************
	
	//---------- START function to upload image ----------
	//filename is stored in database - filefolder is used to place the image in a specific folder - call with allowgifs 0 if GDLIB not 2.0.28 compatible
	public function upload_image ($name, $tmp_name, $type, $size, $max_nuwidth, $max_nuheight, $max_thumbwidth, $max_thumbheight, 
						   &$filename, $thumbnail, $filefolder="", $allowGifs=1, $max_size=1048576)
	{	
		//max size in bytes convert to MB 
		$max_size_mb = round($max_size/1048576);
		$size_mb = round(($size/1048576), 2);
		
		if ($name!="")
		{	
			if ($type == "image/pjpeg" || $type == "image/jpeg" || $type == "image/jpg" || $type == "pjpeg" || $type == "jpeg" || 
				$type == "jpg" || ($type == "image/gif" && $allowGifs == 1) || ($type == "gif" && $allowGifs == 1) || $size == 0)
			{ 					
				if ($size > $max_size || $size == 0)
				{
					//filesize of temp uploaded image - kick out for files greater than max (1 meg=1048576 bytes) or 0k (error uploading)
					return "Invalid file size ($size bytes = ".$size_mb."mb). <br />Max File Size = ".$max_size_mb."mb";
				}	
				else
				{			
					$imageInfo = getimagesize($tmp_name); 
					$width = $imageInfo[0]; 
					$height = $imageInfo[1]; 
					$token = md5(uniqid(rand(),1));
					//set thumbnail naming variable
					$thumbnail_prefix = "";
	
					if (is_uploaded_file($tmp_name))
					{			
						//if the thumbnail parameter has been set to 1 then run the upload image function twice once to 
						//upload the main image again to create a thumbnail image, otherwise just loop through once
						if ($thumbnail == 1)
						{
							$loop_limit = 3;
						}
						else
						{
							$loop_limit = 2;
						}
						  
						for ($i = 1; $i < $loop_limit; $i++)
						{
							if ($i > 1)
							{
								//create the thumbnail image on the second loop through
								list ($nu_width,$nu_height) = $this->cons_resize($width,$height,$max_thumbwidth,$max_thumbheight);
								//populate thumbnail naming variable
								$thumbnail_prefix = "thumb_";
							}
							else
							{
								//create the thumbnail image on the second loop through
								list ($nu_width,$nu_height) = $this->cons_resize($width,$height,$max_nuwidth,$max_nuheight);
								//populate thumbnail naming variable
							}
							//echo "width = $width , height = $height , nuWidth = $nu_width , nuheight = $nu_height max_nuwdith = $max_nuwidth , max_nuheight = $max_nuheight";
								
							$dstbigImg = imagecreatetruecolor($nu_width,$nu_height);
							
							switch ($type)
							{
								case "image/pjpeg"://---- all type jpeg code 
								case 'image/jpeg': 
								case 'image/jpg':						
								case "pjpeg":
								case "jpeg": 		
								case "jpg":
									$srcImg = imagecreatefromjpeg($tmp_name);								
									imagecopyresampled ($dstbigImg, $srcImg, 0, 0, 0, 0, $nu_width, $nu_height, $width, $height);
									if (!(imagejpeg($dstbigImg, "$filefolder/" . $thumbnail_prefix . $token . $name)))
									{
											return "Cannot create JPEG";
									}
									else
									{	
										$filename =  $token . $name;
									}
								break;
								
								case "image/gif":
								case "gif":
									//requires 2.0.28 compatible GDlib installation
									$srcImg = imagecreatefromgif($tmp_name);																	
									imagecopyresampled ($dstbigImg, $srcImg, 0, 0, 0, 0, $nu_width, $nu_height, $width, $height);									
									if (!(imagegif($dstbigImg, "$filefolder/" . $thumbnail_prefix . $token . $name)))
									{
										return "Cannot create GIF";
									}
									else
									{	
										$filename =  $token . $name;
									}						
								break;						
							}//end switch type						   
						}//end loop images
					}//end is upload check 				
				}//end size check	
			}//end type check
			else
			{
				return "Wrong file type ($type).";
			}//end gif/jpeg check 
		}
		else
		{
			return "No image found";
		}//end name check	
	}//end upload_image
	//---------- END function to upload image ----------
	
	
	//---------- START function to upload image - VERSION 2 (3 image sizes) ----------
	//options are uploading the main image - and a choice to have thumbnail/big images as well - e.g. to use for product zoom
	//filename is stored in database - filefolder is used to place the image in a specific folder - call with allowgifs 0 if GDLIB not 2.0.28 compatible
	public function upload_image_fcn ($thumbnail=0, $bigimage=0, $mainimage=1, $upl_name, $upl_tmp_name, $upl_type, $upl_size, 
						   	   $max_thumbwidth, $max_thumbheight, $max_mainwidth, $max_mainheight, $max_bigwidth, $max_bigheight, 
						  	   &$filename,  $filefolder="", $allow_gifs=1, $max_size=1048576, &$token,
							   $debug=1)
	{	
		$debug_string = "";
		$upload_image_fcn = 0;
	
		//max size in bytes convert to MB 
		$max_size_mb = round($max_size/1048576);
		$upl_size_mb = round(($upl_size/1048576), 2);
		
		//if image was uploaded
		if ($upl_name!="")
		{	
			//test valid file type
			if ($upl_type == "image/pjpeg" || $upl_type == "image/jpeg" || $upl_type == "image/jpg" || $upl_type == "pjpeg" || $upl_type == "jpeg" || 
				$upl_type == "jpg" || ($upl_type == "image/gif" && $allow_gifs == 1) || ($upl_type == "gif" && $allow_gifs == 1) || $upl_size == 0)
			{ 					
				if ($upl_size > $max_size || $upl_size == 0)
				{
					//filesize of temp uploaded image - kick out for files greater than max (1 meg=1048576 bytes) or 0k (error uploading)
					return "Invalid file size ($upl_size bytes = ".$upl_size_mb."mb). <br />Max File Size = ".$max_size_mb."mb.";
				}	
				else
				{			
					//get dimensions of uploaded image
					$imageInfo = getimagesize($upl_tmp_name); 
					$width = $imageInfo[0]; 
					$height = $imageInfo[1]; 
					
					//random token to make name unique
					//can pass token in e.g. if generating seperate thumbnail only, else generate one
					if ($token == "")									
						$token = md5(uniqid(rand(),1));
					$debug_string.= "<br />token:$token<br />";
						
					//set prefix naming variable - thumbnail/big prefix before filename
					$image_prefix = "";
	
					//test temp file exists
					if (is_uploaded_file($upl_tmp_name))
					{			
						//run the upload image function - loop through once for main image - 
						//if thumbnail parameter has been set to 1 then run it again and if bigimage set to 1 run it again 
						$loop_limit = 2; //loop 1 main
						if ($thumbnail == 1)
						{
							//$loop_limit++;
							$loop_limit = 3; //go up to loop 2 main,thumb
						}
						if ($bigimage == 1)
						{
							//$loop_limit++;
							$loop_limit = 4; //go up to loop 3 main,thumb,big
						}
						$debug_string.= "<br />loop_limit:$loop_limit<br />";
						
						//loop - create/store necessary versions of the image - main, then thumb/big if set
						for ($i = 1; $i < $loop_limit; $i++)
						{
							$debug_string.= "i:$i <br />";
							$debug_string.= "bigimage:$bigimage mainimage:$mainimage thumbnail:$thumbnail<br />";
						
							$generate_image = 0;
						
							//Determine if we should generate each image
							if ($i == 3 && $bigimage == 1): //BIG
								$generate_image = 1;
								//create the big zoom image on the third loop through
								list ($new_width,$new_height) = $this->cons_resize($width,$height,$max_bigwidth,$max_bigheight);
								//populate prefix naming variable - thumbnail
								$image_prefix = "big_";
							elseif ($i == 2 && $thumbnail == 1): //THUMB
								$generate_image = 1;
								//create the thumbnail image on the second loop through
								list ($new_width,$new_height) = $this->cons_resize($width,$height,$max_thumbwidth,$max_thumbheight);
								//populate prefix naming variable - thumbnail
								$image_prefix = "thumb_";
							elseif ($i == 1 && $mainimage == 1): //NORMAL/MAIN
								$generate_image = 1;
								//create the main image on the first loop through
								list ($new_width,$new_height) = $this->cons_resize($width,$height,$max_mainwidth,$max_mainheight);
								//populate thumbnail naming variable

							endif;
							$debug_string.="image_prefix = $image_prefix, width = $width, height = $height, new_width = $new_width, new_height = $new_height<br />";							
							
							//If generate was set
							if ($generate_image == 1):
								$dstbigImg = imagecreatetruecolor($new_width,$new_height);
								
								switch ($upl_type)
								{
									case "image/pjpeg": //---- all type jpeg code 
									case 'image/jpeg': 
									case 'image/jpg':						
									case "pjpeg":
									case "jpeg": 		
									case "jpg":
										$srcImg = imagecreatefromjpeg($upl_tmp_name);								
										imagecopyresampled ($dstbigImg, $srcImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
										if ( !(imagejpeg($dstbigImg, "$filefolder/" . $image_prefix . $token . $upl_name)) )
										{
											return "Cannot create JPEG";
										}
										else
										{	
											$filename =  $token . $upl_name;
										}
									break;
									
									case "image/gif": //---- all type gif code
									case "gif":
										//requires 2.0.28 compatible GDlib installation
										$srcImg = imagecreatefromgif($upl_tmp_name);																	
										imagecopyresampled ($dstbigImg, $srcImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);									
										if ( !(imagegif($dstbigImg, "$filefolder/" . $image_prefix . $token . $upl_name)) )
										{
											return "Cannot create GIF";
										}
										else
										{												
											$filename =  $token . $upl_name;
										}						
									break;						
								}//end switch type
								
								$debug_string.= "<br />filename:$filename<br />";
								$debug_string.= "<br />full filename:$image_prefix$filename<br />";
							endif;						   
						}//end loop images
					}
					else
					{
						return "is_uploaded_file failed";
					}//end is upload check 					
				}//end size check	
			}//end type check
			else
			{
				return "Wrong file type ($upl_type).";
			}//end gif/jpeg check 
		}
		else
		{
			return "No image found";
		}//end name check	
		
		$upload_image_fcn = 1;
		
		//debug code
		if ($debug==1)
		{
			echo "<div><hr />upload_image_fcn testvar:$debug_string</div>";
		}//end if ($debug==1)	
		return $upload_image_fcn;		
	}//end upload_image_fcn
	//---------- END function to upload image - VERSION 2 (3 image sizes) ----------	
	
	
	//---------- START function to resize image constraints ----------
	//proportionally generate resize constraints within $max_nuwidth and $max_nuheight
	private function cons_resize($old_width,$old_height,$max_nuwidth,$max_nuheight)
	{
		//set nu width and nu height in case they dont need changing
		$new_width = $old_width;
		$new_height = $old_height;
		
		//check for images that are too wide - resize proportionally to max width
		if ($old_width > $max_nuwidth)
		{
			// now we check for over-sized images and pare them down to the dimensions we need for display purposes 
			$ratio = ( $old_width > $max_nuwidth ) ? (real)($max_nuwidth / $old_width) : 1 ; 
			$new_width = ((int)($old_width * $ratio));    //full-size width 
			$new_height = ((int)($old_height * $ratio));    //full-size height 
		}
		
		//check for images that are still too high after width resize - resize proportionally to max height
		if ($new_height > $max_nuheight)
		{	
			$ratio = ( $new_height > $max_nuheight ) ? (real)($max_nuheight / $new_height) : 1 ; 
			$new_width = ((int)($new_width * $ratio));    //mid-size width 
			$new_height = ((int)($new_height * $ratio));    //mid-size height 	
		}
		//echo "<br />ratio:$ratio new_width:$new_width new_height $new_height";
		
		//return new width/height constraints in an array
		return array ($new_width,$new_height);
	}//end function cons_resize
	//---------- END function to resize image constraints ----------
	
	//********************************** IMAGE FUNCTIONS *******************************************
	//**********************************************************************************************


	//**********************************************************************************************
	//********************************** COOKIE FUNCTIONS ******************************************

	//re-generate token until unique
	public function gen_unique_token($token, $repeats=10)
	{	
		$token = trim($token);
		//echo "<br />token:$token<br />";
		$i=1;
		
		while ($this->test_unique_token($token)==1)
		{
			//no infinite loops
			if ($i>($repeats))
			{
				return $token;
			}
		
			//new token - 32 char random userID based on current time
			$token = md5(uniqid(rand(),1)); 
			//echo "<br />token:$token<br />";
			
			$i++;
		}//end while
		
		return $token;
	}//end gen_unique_token

	//test if token is unique 1=no	0=yes
	public function test_unique_token($token, $debug=0)
	{
		$debug_string = "";		
		
		$test_unique_token=1;
		$token=trim($token);
				
		$sql_test="SELECT * FROM ".SITE_DB_PREFIX."orders WHERE cookie_id='$token'";
		$debug_string.="<br />sql_test:$sql_test<br />";
		$result_test=mysql_query($sql_test);							
		if ($result_test&&mysql_num_rows($result_test)==0)
		{
			//order id doesnt exist
			$test_unique_token=0;		
		}
		else
		{
			//order id exists or an error
			$test_unique_token=1;
		}
		
		//debug code
		if ($debug==1)
		{
			echo "<div>test_unique_token testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $test_unique_token;	
	}//end test_unique_token
	
	//********************************** COOKIE FUNCTIONS ******************************************
	//**********************************************************************************************
	
	
	//************** FUNCTION TO TEST IF AN ORDER ID EXISTS IN ORDERS TABLE ******************	
	
	//test if cookie_id is for a successful completed order
	public function test_order_completed($token, &$return_message, $debug=0)
	{	
		$debug_string = "";		
	
		$test_order_completed=1;
		$token=trim($token);
				
		//test if specific order is completed
		$sql_test="SELECT * FROM ".SITE_DB_PREFIX."orders WHERE cookie_id='$token'";
		$sql_test.=" AND status<>'ns'";
		$sql_test.=" AND status<>'' AND status IS NOT NULL ";
		$debug_string.="<br />sql_test:$sql_test<br />";
		
		$result_test=mysql_query($sql_test);							
		if ($result_test&&mysql_num_rows($result_test)==0)
		{
			//order id DOES NOT match a completed order so OK
			$test_order_completed=0;	
			$return_message="Order not processed.";
		}
		else
		{
			//order id DOES match a completed order so FAILED
			$test_order_completed=1;
			$return_message="Order $token has already been processed.";
		}

		//debug code
		if ($debug==1)
		{
			echo "<div>test_order_completed testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $test_order_completed;			
	}//end test_order_completed	
	
	
	//*** TEST USER DETALS SET IN ORDER BEFORE PROCESSING ***
	public function test_order_data($token)
	{	
		$test_order_data=1; //failed
		$token=trim($token);
				
		$sql_test="SELECT * FROM ".SITE_DB_PREFIX."orders WHERE cookie_id='$token'";
		$sql_test.=" AND user_type<>'' AND user_type is not null";
		$sql_test.=" AND user_id<>'' AND user_id is not null";
		
		//echo $sql_test;
		$result=mysql_query($sql_test);							
		if ($result&&mysql_num_rows($result)==1)
		{
			//order found so ok
			return 0;		
		}
		else
		{
			//user details not stored in order
			return 1;
		}
		
		return $test_order_data;
	}//end test_order_data
	//*** TEST USER DETALS SET IN ORDER BEFORE PROCESSING ***			
	
	//************** FUNCTION TO TEST IF AN ORDER ID EXISTS IN ORDERS TABLE ******************			


	//********************************** META FUNCTIONS *****************************************
	//*******************************************************************************************

	//---------- START function to update meta tags ----------
	public function update_meta_tags($table, $col_name, $col_value, &$meta_title, &$meta_keywords, &$meta_description, $quotes=0, $debug=0)
	{		
		$debug_string = "";
		$fcn_result = 0;

		//Blank metas to ensure defaults will be used if update fails
		$meta_title = "";
		$meta_keywords = "";
		$meta_description = "";

		$lq="";
		$rq="";	
		if ($quotes==1)
		{
			$lq="'";
			$rq="'";
		}
		$sql_select = "select meta_title, meta_keywords, meta_description FROM ".SITE_DB_PREFIX."$table";
		$sql_select.= " WHERE $col_name=$lq$col_value$rq limit 1";
		$debug_string.="<br />sql_select:$sql_select<br />";
		$result_select = mysql_query($sql_select);		
		if ($result_select&&mysql_num_rows($result_select)==1)
		{
			$row_select = mysql_fetch_assoc($result_select);	
			//update the meta tags
			$meta_title = trim($row_select["meta_title"]);
			$meta_keywords = trim($row_select["meta_keywords"]);
			$meta_description = trim($row_select["meta_description"]);
		
			$fcn_result = 1;
		}//end if ($result_select
		
		//debug code
		if ($debug==1)
		{
			echo "<div>update_meta_tags testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $fcn_result;
	}//update_meta_tags
	//---------- END function to update meta tags ----------		

	//********************************** META FUNCTIONS *****************************************
	//*******************************************************************************************
	
	
	
	//*******************************************************************************************
	//********************---------- START INJECTION FUNCTIONS ----------************************
	//*******************************************************************************************

	//*** FORM EMAIL INJECTION INPUT TEST  ***
	//TESTING FORM INPUT BY LOOPING ELEMENTS - checking for \r \n 
	// We can see in the previous script that any occurence of "\r" or "\n" will make it die(). 
	// "\n" is equal to <LF> (Line Feed or 0x0A/%0A in hexadecimal), and "\r" is equal to <CR> 
	//(Carriage return or 0x0D/%0D in hexadecimal). Some chars like %0A%0D can be used as a 
	//substitute to %0A, but it is always the last char that is really dangerous. 
	
	//NO GOOD FOR TEXT AREAS AS THERE HAVE \n NEWLINES WHICH ARE PERFECTLY VALID!!
	//JUST NEED TO CHECK HEADER FIELDS AS SOMETIMES FILLED FROM USER FORM INPUT e.g. $from and $subject 
	
	//*** SINGLE FIELD TEST ***
	//urldecode the value before passing it in
	public function injection_test(&$result_msg, $input, $debug=0)
	{
		$injection_test=1; //OK - ANY ERRORS SET IT TO FAIL=0
		$debug_string = "";	
		
		//urldecode here before testing
		$input = urldecode($input);
		
		//echo "input:$input";
		if (eregi("\r",$input) || eregi("\n",$input))
		{
			//echo "input:$input<br />";
			//die("Invalid input: Possible Injection input.");	
			$result_msg="Invalid input: Possible Injection input.<br />"; 
			$injection_test=0; 				
		}//end if	
		
		$debug_string.="<br />injection_test:$injection_test";
		$debug_string.="<br />result_msg:$result_msg<br />";
		
		//debug code
		if ($debug==1)
		{
			//echo "<br />injection_test testvar:$debug_string<br />";
			$debug_string['injection_test'] = "<div>injection_test testvar:$debug_string</div>";
		}//end if ($debug==1)
		return $injection_test;				
	}//end function injection_test()
	//*** SINGLE FIELD TEST ***
	
	public function injection_form_test(&$result_msg, $debug=0)
	{
		$injection_form_test=1; //OK - ANY ERRORS SET IT TO FAIL=0
		$debug_string = "";			
		
		//auto looping form elements so urldecode here before testing
		
		//Loop through each POST'ed value and test if it contains one of the injection strings
		foreach($_POST as $v)
		{ 
			//strings only crashes if array
			if (gettype($v)!="string")
			{
				//echo "<br />v:$v";
				//echo "input:$v<br />";
				//die("Invalid input: Possible Injection input.");	
				$result_msg="Invalid input: Possible Injection input.<br />"; 
				$injection_form_test=0; 
			}//end if (gettype($v)=="string")
		}//end foreach
		
		//debug code
		if ($debug==1)
		{
			$debug_string['injection_form_test'] = "<div>injection_form_test testvar:$debug_string</div>";
		}//end if ($debug==1)
		return $injection_form_test;		
	}//end function injection_form_test()

	//*** GENERAL INJECTION INPUT TESTING  ***
	/*
	//******************************************************************************
	Note: dont use urldecode on GET form input - ok on POST
	A reminder: if you are considering using urldecode() on a $_GET variable, DON'T!
	Evil PHP:
	<?php
	# BAD CODE! DO NOT USE!
	$term = urldecode($_GET['sterm']);
	?>
	Good PHP:
	<?php
	$term = $_GET['sterm'];
	?>
	The webserver will arrange for $_GET to have been urldecoded once already by the time it reaches you!
	Using urldecode() on $_GET can lead to extreme badness, PARTICULARLY when you are assuming "magic quotes" on GET is protecting you against quoting.
	Hint: script.php?sterm=%2527 [...]
	PHP "receives" this as %27, which your urldecode() will convert to "'" (the singlequote). This may be CATASTROPHIC when injecting into SQL or some 
	PHP functions relying on escaped quotes -- magic quotes rightly cannot detect this and will not protect you!
	This "common error" is one of the underlying causes of the Santy.A worm which affects phpBB < 2.0.11.
	//******************************************************************************
	
	
	Spammers have recently been using mail header injection to send spam e-mail from contact forms that have in the past viewed as secure.
	If you are a webmaster you can edit your forums to ensure they are secure and safe from spammers
	This code is posted on http://uk2.php.net/manual/en/ref.mail.php#59012 by Tim
	Anyway, I have several websites that all use a common contact form. Every contact form posts to the same script.
	This is how I defend against header injections. (I typically use this script as an include file)
	This script requires your html form to use action="post". Make sure this is only used on the script that the html form will be posted to. 
	If you use this script on a regular page request, it will die().
	* More error checking should be done when testing posted values for bad strings. Possibly a regular expression...
	*/

	//PREVENT HEADER INJECTION
	public function injection_general_test(&$result_msg, $debug=0)
	{
		$injection_general_test=1; //OK - ANY ERRORS SET IT TO FAIL=0
		$debug_string = "";		
		
		//----------------------------------------------------------------------------------------
		//******************************* TEST FORM POSTED FROM BROWSER **************************
		// First, make sure the form was posted from a browser. 
		// For basic web-forms, we don't care about anything 
		// other than requests from a browser:     
		if(!isset($_SERVER['HTTP_USER_AGENT']))
		{ 
		   $result_msg.="Forbidden - You can only view this page via a browser.<br />"; 
		   $injection_general_test=0; 
		} 
		//******************************* TEST FORM POSTED FROM BROWSER **************************
		//----------------------------------------------------------------------------------------
		
		
		//----------------------------------------------------------------------------------------
		//******************************* TEST FORM METHOD WAS POST ******************************
		//TEST FORM METHOD WAS POST IF THIS IS THE CASE AND WE ARE NOT USING ANY GET VARS 
		//i.e. set form_method=post to do this
		// Make sure the form was indeed POST'ed: 
		// (requires your html form to use: action="post")  
		if($_SERVER['REQUEST_METHOD'] != "POST")
		{ 
		   $result_msg.="Forbidden - Form was not submitted as expected. You are not authorized to view this page.<br />"; 
		   $injection_general_test=0; 	       
		}
		//******************************* TEST FORM METHOD WAS POST ******************************
		//----------------------------------------------------------------------------------------
		
		
		//----------------------------------------------------------------------------------------
		//************************* TEST FORM POSTED FROM APPROVED HOST **************************
		// Host names from where the form is authorized taken from constants set at top of this script
		// to be posted from:  
		$authHosts = unserialize(ALLOWED_DOMAINS);
		
		// Where have we been posted from? 
		$fromArray = parse_url(strtolower($_SERVER['HTTP_REFERER'])); 
		
		// Test to see if the $fromArray used www or any authorised prefix is used to get here. 
		//unserialize the prefix contstant to use the prefix array
		//serialise converts any data type e.g. object or array into a string byte-stream representation
		//unserialise converts it back again
		$array_prefixs = unserialize(ALLOWED_URL_PREFIX);
		
		$debug_string .= "<br />ALLOWED_URL_PREFIX (array_prefixs): ".print_r($array_prefixs, TRUE);
		$debug_string .= "<br />fromArray: ".print_r($fromArray, TRUE);
		$debug_string .= "<br />fromArray['host']: ".$fromArray['host'];
		//strip off the prefix i.e. everything after first dot
		$debug_string .= "<br />substr(stristr(fromArray['host'],'.'), 1): ".substr(stristr($fromArray['host'],'.'), 1);
		$debug_string .= "<br />ALLOWED_DOMAINS (authHosts): ".print_r($authHosts, TRUE);
		
		//loop allowed prefixes - test f one of them prefix is in the actual full request URL
		$wwwUsed = false;
		for ($i = 0; $i < count($array_prefixs); $i++)
		{
			if ($wwwUsed===false)
			{
				//Find the last occurrence of a character A in a string B - strrchr(A,B) - NOTE: needle can only be ONE char 
				//so is useless as is stristr to return the match SO i recon just use strpos as a test of a MATCH and set wwwUsed
				//$wwwUsed = strrchr($array_prefixs[$i], $fromArray['host']);
				//$wwwUsed = stristr($fromArray['host'], $array_prefixs[$i]);
				
				$mystring = $fromArray['host'];
				$findme   = $array_prefixs[$i];
				$pos = strpos($mystring, $findme);
				//if the prefix was found AT POSITION 0 e.g. at the start then ok otherwise wwwUsed is false and keep looping
				//e.g if not found at start of string
				if ($pos === false || $pos > 0):
					$wwwUsed = false;
				else:
					$wwwUsed = true; // true/1/boolean woteva
				endif;
				$debug_string .= "<br />LOOP: pos:$pos wwwUsed:$wwwUsed "."fromArray['host']:".$fromArray['host']."' - "."array_prefixs[$i]:'".$array_prefixs[$i]."'";					
			}
		}
		$debug_string .= "<br />wwwUsed:".$wwwUsed;		
		
		$debug_string .= "<br />value: ".($wwwUsed === false ? $fromArray['host'] : substr(stristr($fromArray['host'],'.'), 1));
		
		// Make sure the form was posted from an approved host name. 
		// ternary operator: ? used to select between two expressions depending on a third one
		// $action = A ? B : C; - if A is true do B else do C
		// so if prefix didnt match pick whole host else take the prefix off - then match this to the list of allowed hosts
		// this sort of half-negates the prefix test doesnt it - i.e. grove. didnt match but fuk it lets just test the domain part for a match anyway
		if (!in_array( ( $wwwUsed === false ? $fromArray['host'] : substr(stristr($fromArray['host'],'.'), 1) ), $authHosts) )
		{     
			//logBadRequest(); 
			//header("HTTP/1.0 403 Forbidden");  
			$result_msg.="Form was NOT posted from an approved host name.<br />"; 
			//$result_msg.="<br /><br />debug_string:<br />".$debug_string;	
			$injection_general_test=0; 	     
		} 
		//************************* TEST FORM POSTED FROM APPROVED HOST **************************
		//----------------------------------------------------------------------------------------
		
		
		//----------------------------------------------------------------------------------------
		//************************* TEST FORM INPUT FOR HEADER INJECTIONS ************************
		// Attempt to defend against header injections: 
		$badStrings = array("Content-Type:", 
							 "MIME-Version:", 
							 "Content-Transfer-Encoding:", 
							 "bcc:", 
							 "cc:",
							 "[/url]",
							 "[/URL]");  
		
		// Loop through each POST'ed value and test if it contains 
		// one of the $badStrings: 
		foreach($_POST as $k => $v)
		{ 
		   foreach($badStrings as $v2)
		   { 
			   if(strpos($v, $v2) !== false)
			   { 
					//logBadRequest(); 
					//header("HTTP/1.0 403 Forbidden"); 
					$result_msg.="Bad String Form Input.<br />"; 
					$injection_general_test=0;		    
			   } 
		   } 
		}     
		//************************* TEST FORM INPUT FOR HEADER INJECTIONS ************************
		//----------------------------------------------------------------------------------------
		
		
		//----------------------------------------------------------------------------------------
		//******************************* CLEANUP - FREE UP MEMORY *******************************
		// Made it past spammer test, free up some memory 
		// and continue rest of script:     
		unset($k, $v, $v2, $badStrings, $authHosts, $fromArray, $wwwUsed); 
		//******************************* CLEANUP - FREE UP MEMORY *******************************
		//----------------------------------------------------------------------------------------	
	
	
		//debug code
		if ($debug==1)
		{
			echo "<br />injection_general_test testvar:$debug_string<br />";
			//$debug_string['injection_general_test'] = "<div>injection_general_test testvar:$debug_string</div>";
		}//end if ($debug==1)
		return $injection_general_test;
	}//end function injection_general_test()
	//*** GENERAL INJECTION INPUT TESTING  ***


	//*******************************************************************************************
	//*********************---------- END INJECTION FUNCTIONS ----------*************************
	//*******************************************************************************************	
	
	

	//*******************************************************************************************
	//*************************---------- ENCRYPTION FUNCTIONS ----------************************
	//*******************************************************************************************

	//*****---------- START ENCRYPTION (MCRYPT) FUNCTION ----------*****
	public function encrypt ($data, $type="mcrypt")
	{
		$encrypt = "";
		
		//trim and stripslashes first
		$data = trim($data);
		$data = stripslashes($data);
		
		if ($type=="mcrypt")
		{
			//*** MCRYPT - KEY/VECTOR/ENCRYPTION STRING ***
			//encryption initialisation vector
			$IV = mcrypt_create_iv (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			//encryption key
			$KEY = KEY_MCRYPT;
			//encrypted string
			$encrypt = mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $KEY, $data, MCRYPT_MODE_ECB, $IV);
			//*** MCRYPT - KEY/VECTOR/ENCRYPTION STRING ***
		}
		else if ($type=="xor")
		{
			$encrypt = $this->xor_encrypt($data);
		}
		else if ($type=="base64")
		{
			//*** JUST base64_encode ***
			//so just use input data string without mcrypt encoding
			$encrypt = $data;
		}//end if ($type=="mcrypt")
		
		//then base64_encode if we want to
		$encrypt = base64_encode( $encrypt );

		//no whitespace
		$encrypt = trim($encrypt);

		return $encrypt;
			
		//REFERENCE
		//$form_password_enc=trim( base64_encode( encrypt( stripslashes( trim($form_password) ) ) ) );  //MCRYPT
		//$p_code_enc = base64_encode(stripslashes($p_input_code)); //base 64 only	
				
	}//encrypt
	//*****---------- END ENCRYPTION (MCRYPT) FUNCTION ----------*****	
		
		
	//*****---------- START DECRYPTION (MCRYPT) FUNCTION ----------*****
	public function decrypt ($data, $type="mcrypt")
	{
		$decrypt = "";
		
		//trim and stripslashes first
		$data = trim($data);
		$data = stripslashes($data);		
		
		//base64_decode first if we want to
		$data = base64_decode( $data );	
		
		if ($type=="mcrypt")
		{
			//*** MCRYPT - KEY/VECTOR/DECRYPTION STRING ***
			//encryption initialisation vector
			$IV = mcrypt_create_iv (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
			//encryption key
			$KEY = KEY_MCRYPT;
			//decrypted string
			$decrypt = mcrypt_decrypt (MCRYPT_RIJNDAEL_256, $KEY, $data, MCRYPT_MODE_ECB, $IV);
			//*** MCRYPT - KEY/VECTOR/DECRYPTION STRING ***
		}
		else if ($type=="xor")
		{
			$decrypt = $this->xor_decrypt($data);
		}		
		else if ($type=="base64")
		{
			//*** JUST base64_decode ***
			//so just use base64_decoded input data string without mcrypt decoding
			$decrypt = $data;
		}//end if ($type=="mcrypt")		
		
		//no whitespace
		$decrypt = trim($decrypt);
		
		return $decrypt;
		
		//REFERENCE
		//$p_code_dec = decrypt( base64_decode( stripslashes( trim($p_code_enc) ) ) );
		//$p_code_dec = base64_decode( stripslashes( trim($p_input_code) ) );	//base 64 only		
		
	}//decrypt
	//*****---------- END DECRYPTION (MCRYPT) FUNCTION ----------*****


	//*****---------- START ENCRYPTION (XOR) FUNCTION ----------*****
	//xor_convert encrypt
	public function xor_encrypt($value) 
	{
		$value = $this->xor_convert(stripslashes($value),KEY_XOR);
		
		// Initialise output variable
		$output = "";
		
		// Do encoding
		$output = base64_encode($value);
		
		// Return the result
		return $output;
	}//end xor_encrypt
	//*****---------- END ENCRYPTION (XOR) FUNCTION ----------*****	
		
	//*****---------- START DECRYPTION (XOR) FUNCTION ----------*****	
	//xor_convert decrypt	
	public function xor_decrypt($value) 
	{
		// Do encoding
		$value = base64_decode($value);
	
		// Initialise output variable
		$output = "";
		
		// Do decoding
		$output = $this->xor_convert($value,KEY_XOR);
		
		// Return the result
		return $output;
	}//end xor_decrypt
	//*****---------- END DECRYPTION (XOR) FUNCTION ----------*****		
	
	//*****---------- START CONVERT (XOR) FUNCTION ----------*****	
	//xor_convert function
	public function xor_convert($InString, $Key) 
	{
		// Initialise key array
		$KeyList = array();
		// Initialise out variable
		$output = "";
		
		// Convert $Key into array of ASCII values
		for ($i = 0; $i < strlen($Key); $i++)
		{
			$KeyList[$i] = ord(substr($Key, $i, 1));
		}
		
		// Step through string a character at a time
		for ($i = 0; $i < strlen($InString); $i++) 
		{
			// Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
			// % is MOD (modulus), ^ is XOR
			$output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
		}
		
		// Return the result
		return $output;
	}//end xor_convert	
	//*****---------- END CONVERT (XOR) FUNCTION ----------*****	

	
	//*******************************************************************************************
	//*************************---------- ENCRYPTION FUNCTIONS ----------************************
	//*******************************************************************************************	


	
	//*******************************************************************************************
	//***************************---------- OTHER FUNCTIONS ----------***************************
	//*******************************************************************************************		
	public function cleanstring($str_string)
	{				
		//remove all tags and comments marks
		$str_string = mysql_escape_string(strip_tags(htmlspecialchars($str_string, ENT_QUOTES)));		
		//echo $string;
		return $str_string;
	}//end function cleanstring()
	
	
	//create a link for page types - sometimes based on mod_rewrite
	public function gen_link_action($type="", $page_name, $page_ext=".php", $x1, $y1, $x2, $y2, $debug=0)
	{	
		$debug_string = "";	
		
		if ($type=="product")
		{
			if (MOD_REWRITE==1)
			{
				$gen_link_action = $page_name."/";
				$gen_link_action.= "$x1/$y1/";
				$gen_link_action.= "$x2$y2/";
			}
			else
			{
				$gen_link_action = $page_name.$page_ext."?";
				$gen_link_action.= "$x1=".$y1;
				$gen_link_action.= "&amp;$x2=".$y2;
			}
		}//end if ($type="product")

		//debug code
		if ($debug==1)
		{
			echo "<div>gen_link_action testvar:$debug_string</div>";
		}//end if ($debug==1)			
		return $gen_link_action;			
	}//end gen_link_action		
	
	
	//*******************************************************************************************
	//***************************---------- OTHER FUNCTIONS ----------***************************
	//*******************************************************************************************
		
	

	//*******************************************************************************************
	//*************************---------- STRING FUNCTION ----------*****************************
	//*******************************************************************************************

	// PUBLIC function
	//---------- START function to move back x words though a string ----------
	//START function to move back x words though a string from a give position
	//and return the new starting position
	public function search_word_spaces($string, $words, $pos, $debug=0)
	{
		$fcn_msg = "";
		$debug_string = "";	
	
		$found_space = 0;

		if ($pos > 0)	
		{
			for ($i = $pos; $i > 0; $i--) 
			{
				$debug_string.= $string[$i];
				
				if ($string[$i] == " ")
				{
					$found_space++;
					
					//if ($found_space => 10)
						//return $i;
						
					if ($found_space >= $words + 1)
						return $i+1;
				}//end if
			}//end for
		}//end if
			
		//debug code
		if ($debug==1)
		{
			echo "<div>search_word_spaces testvar:$debug_string</div>";
		}//end if ($debug==1)			
		
		return 0;		
		
	}//search_word_spaces
	//---------- END function to reset ALL page/sub-level orders alphabetically ----------	

	//*******************************************************************************************
	//*************************---------- STRING FUNCTION ----------*****************************
	//*******************************************************************************************



	//*******************************************************************************************
	//***************************---------- URL FUNCTIONS ----------*****************************
	//*******************************************************************************************
	//get the current page URL	
	public function curPageURL() 
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} 
		else 
		{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}//curPageURL
	
	function curPageName() 
	{
		return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	}//curPageName
	//*******************************************************************************************
	//***************************---------- URL FUNCTIONS ----------*****************************
	//*******************************************************************************************

	
	//*******************************************************************************************
	//********---------- JPs PAGING FUNCTION FOR THE AJAX LIVESEARCH ----------******************
	//*******************************************************************************************
	
	public function paging($strQuery, $page, $count, $total_pages, $ajax_get_string=NULL)
	{				
		if($ajax_get_string == NULL){
			$link_url = $_SERVER['PHP_SELF'];
		}else{
			//set the ajay get string that will be used in the results navigation
			$link_url = "javascript:";
		}
	
		//write query string that will pass in paging links
		if(empty($strQuery)){
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
		if ( $page == 1 ) {
			# if we are on the first page then "First" and "Prev" should not be links.
			$navigation = "<span class=\"off\">First</span> | <span class=\"off\">Prev</span> | ";
		} else {
			# we are not on page one so "First" and "Prev" can
			# be links
			$prev_page = $page - 1;
			
			if($ajax_get_string == NULL){
				$navigation = "<a href=\"".$link_url."?".$strQuery."&amp;f_page=1\">First</a> | <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$prev_page."\">Prev</a> | ";
			}else{
				$navigation = "<a href=\"".$link_url."getresults('page-search-xml-results.php', '$strQuery&amp;f_page=1', showresults)\">First</a> | <a href=\"".$link_url."getresults('page-search-xml-results.php', '$strQuery&amp;f_page=$prev_page', showresults)\">Prev</a> | ";
				
			}
		}
	
		//loop through total number of pages and add a link to each individual page
		for ($i = 1; $i <= $total_pages; $i++) {
		//show curent page as active link by changin the css class
			if($ajax_get_string == NULL){
				if ($i == $page){
					$navigation .= " <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$i."\" class=\"on\" title=\"".$i."\">$i</a> ";
				}else{
					$navigation .= " <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$i."\" title=\"".$i."\">$i</a> ";
				}
			}else{
				if ($i == $page){
					$navigation .= " <a href=\"".$link_url."getresults('page-search-xml-results.php', '$strQuery&amp;f_page=$i', showresults)\" class=\"on\" title=\"".$i."\">$i</a> ";
				}else{
					$navigation .= " <a href=\"".$link_url."getresults('page-search-xml-results.php', '$strQuery&amp;f_page=$i', showresults)\" title=\"".$i."\">$i</a> ";
				}
			}
		}
		$navigation .= "| ";
	
		# this part will set up the rest of our navigation "Next | Last"
		if ( $page == $total_pages ) {
			# we are on the last page so "Next" and "Last"
			# should not be links
			$navigation .= "<span class=\"off\">Next</span> | <span class=\"off\">Last</span>";
		} else {
			# we are not on the last page so "Next" and "Last"
			# can be links
			$next_page = $page + 1;
			if($ajax_get_string == NULL){
				$navigation .= "<a href=\"".$link_url."?".$strQuery."&amp;f_page=".$next_page."\">Next</a> | <a href=\"".$link_url."?".$strQuery."&amp;f_page=".$total_pages."\">Last</a>";
			}else{
				$navigation .= "<a href=\"".$link_url."getresults('page-search-xml-results.php', '$strQuery&amp;f_page=$next_page', showresults)\">Next</a> | <a href=\"".$link_url."getresults('page-search-xml-results.php', '$strQuery&amp;f_page=$total_pages', showresults)\">Last</a>";
			}
		}
					
		return $navigation;
	}//end function paging
	
	//*******************************************************************************************
	//********---------- JPs PAGING FUNCTION FOR THE AJAX LIVESEARCH ----------******************
	//*******************************************************************************************
	
	
	
	
	
	
	
}//class GenericClass


?>