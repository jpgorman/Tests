<?php

$email_err_code = 1;

if ($_GET["send"] == 1){

	//set submission cookie
	$cookie_name = 's_contact';
	$cookie_location = null;

	$submit_error=0;
	
	// check the form tokens match
	if($_POST['hid_token'] != $_SESSION['form_token'])
	{
		//debug
		$debug_string['post_form_token'] = $_POST['hid_token'];
		$debug_string['session_form_token'] = $_SESSION['form_token'];
		$errormsg = "Error: This form cannot be resent";
		$email_err_code = 1;
		$debug_string['errormsg'] = $errormsg;
		$debug_string['email_err_code'] = $email_err_code;
		
	}else{

		if (!isset($_COOKIE[$cookie_name])){//set a cookie to see if the form has already been submitted
	
			
			/*test for possible hacks */
			$result_msg="";
			$injection_result=1;
			$injection_result=$c_generic->injection_form_test($result_msg, DEBUG_FCN_DISPLAY_FLAG);
			if ($injection_result==0)
			{
				$errormsg .= "<br />Error: Possible Injection<br />".$result_msg."<br />";
				$email_err_code = 1;
			}
	
			$result_msg="";
			$injection_result=1;
			$injection_result=$c_generic->injection_general_test($result_msg, DEBUG_FCN_DISPLAY_FLAG);
			if ($injection_result==0)
			{
				$errormsg .= "<br />Error: Possible Injection<br />".$result_msg."<br />";
				$email_err_code = 1;
			}
			/*end test for possible hacks */
	
			if($injection_result == 1){
	
				switch($_POST["hid_Form"]){
				case "Site Contact Form" :
					// set filters for use in filter_input_array to sanitize form input
					$filters = array(
					   "txt_title"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_firstname"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_lastname"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_enquiry"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_email" => FILTER_SANITIZE_EMAIL,
					   "hid_Form"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "notset"=> FILTER_VALIDATE_INT
					);
					
					// apply the filters to the POST array
					$filtered = filter_input_array(INPUT_POST, $filters);
					
					//debug
					$debug_string['frm_title'] = $filtered['txt_title'];
					$debug_string['frm_firstname'] = $filtered['txt_firstname'];
					$debug_string['frm_lastname'] = $filtered['txt_lastname'];
					$debug_string['frm_enquiry'] = $filtered['txt_enquiry'];
					$debug_string['frm_email'] = $filtered['txt_email'];
					$debug_string['frm_form'] = $filtered['hid_Form'];
	
					$resubmit = null;	

					if(!empty($filtered)){
					
						
						//if(filter_var($float, FILTER_VALIDATE_FLOAT) === false)
						if ($filtered['txt_email'] == "")
						{
							$errormsg.= "* Email is blank<br />";	
							$resubmit = "yes";
							$email_err_code = 1;
						}
						elseif(filter_var($filtered['txt_email'], FILTER_VALIDATE_EMAIL) === FALSE)
						{
							$errormsg.= "* Email is invalid<br />";	
							$resubmit = "yes";
							$email_err_code = 1;
						}

					}	
				break;				
				case "Site Contact Us Form" :
					
					// set filters for use in filter_input_array to sanitize form input
					$filters = array(
					   "txt_contact_title"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_contact_firstname"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_contact_lastname"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_contact_enquiry"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "txt_contact_email" => FILTER_SANITIZE_EMAIL,
					   "hid_Form"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "notset"=> FILTER_VALIDATE_INT
					);
					
					// apply the filters to the POST array
					$filtered = filter_input_array(INPUT_POST, $filters);
					
					//debug
					$debug_string['txt_contact_title'] = $filtered['txt_contact_title'];
					$debug_string['txt_contact_firstname'] = $filtered['txt_contact_firstname'];
					$debug_string['txt_contact_lastname'] = $filtered['txt_contact_lastname'];
					$debug_string['txt_contact_enquiry'] = $filtered['txt_contact_enquiry'];
					$debug_string['txt_contact_email'] = $filtered['txt_contact_email'];
					$debug_string['frm_form'] = $filtered['hid_Form'];
	
					$resubmit = null;	
					

					if(!empty($filtered)){

						//if(filter_var($float, FILTER_VALIDATE_FLOAT) === false)
						if ($filtered['txt_contact_email'] == "")
						{
							$errormsg.= "* Email is blank<br />";	
							$resubmit = "yes";
							$email_err_code = 1;
						}
						elseif(filter_var($filtered['txt_contact_email'], FILTER_VALIDATE_EMAIL) === FALSE)
						{
							$errormsg.= "* Email is invalid<br />";	
							$resubmit = "yes";
							$email_err_code = 1;
						}

					}	
				break;	
				case "Site Signup Form" :
					
					// set filters for use in filter_input_array to sanitize form input
					$filters = array(
					   "txt_news_email" => FILTER_SANITIZE_EMAIL,
					   "hid_Form"  => FILTER_SANITIZE_SPECIAL_CHARS,
					   "notset"=> FILTER_VALIDATE_INT
					);
					
					// apply the filters to the POST array
					$filtered = filter_input_array(INPUT_POST, $filters);
					
					//debug
					$debug_string['txt_news_email'] = $filtered['txt_news_email'];
					$debug_string['frm_form'] = $filtered['hid_Form'];
	
					$resubmit = null;	

					if(!empty($filtered)){

						//if(filter_var($float, FILTER_VALIDATE_FLOAT) === false)
						if ($filtered['txt_news_email'] == "")
						{
							$errormsg.= "* Email is blank<br />";	
							$resubmit = "yes";
							$email_err_code = 1;
						}
						elseif(filter_var($filtered['txt_news_email'], FILTER_VALIDATE_EMAIL) === FALSE)
						{
							$errormsg.= "* Email is invalid<br />";	
							$resubmit = "yes";
							$email_err_code = 1;
						}

					}	
				break;
				}
	
				if(($resubmit != 'yes') && (isset($filtered['hid_Form']) && !empty($filtered['hid_Form']))){

					//include the mailform class
					include_once("classes/class.mailform.php");
					
					//instantiate the mailform class and pass in the HTTP vars
					$form = new mailform($filtered);
					
					//get contents sent by form to a variable
					$html_body = $form->get_values();
					$text_body = $form->get_text_values();

					//debug
					$debug_string['text_body'] = $text_body;
					$debug_string['html_body'] = $html_body;
	
					//if the $mailbody variable is not empty the send an email with the contacts of mailbody
					if ($html_body){
	
						$mail_to = "jean-paul@firecast.co.uk"; //send to first colleague that was added to the registration form
						
						$mail_bcc_1 = "bishoi@gotadsl.co.uk"; //send to second colleague that was added to the registration form
	
						$mail_from = $filtered['txt_email'];
	
	
						if((!empty($filtered['txt_firstname']) && !empty($filtered['txt_lastname']))){
							//set the mail varaibles
							$mail_name = $filtered['txt_firstname'] . ' ' . $filtered['txt_lastname'];
						}else{
							$mail_name = "Contact Form";
						}
	
						$mail_subject = "Contact Form : ".DOMAIN_NAME;
	
						//*** BUILD AND SEND EMAIL ****/
						$recipient_email = $mail_to; //"dan@firecast.co.uk"; //dan@firecast.co.uk tony_grove@hotmail.com
						$recipient_name = $mail_name; //"Mr Dan Angell";
						//$mail_subject = "Contact Form";
						$mail_body = $html_body;
	
						//debug
						$debug_string['mail_to'] = $mail_to;
						$debug_string['mail_bcc'] = $mail_bcc;
						$debug_string['mail_from'] = $mail_from;
						$debug_string['mail_name'] = $mail_name;
						$debug_string['mail_subject'] = $mail_subject;
	
						$c_emailbuilder->mail_from = $mail_from;
						$c_emailbuilder->mail_name = $mail_name;
						$c_emailbuilder->mail_recipient($recipient_email);
						$c_emailbuilder->mail_recipient_name($recipient_name);
						//$c_emailbuilder->mail_recipient_cc("");
						$c_emailbuilder->mail_recipient_bcc($mail_bcc_1);
						$c_emailbuilder->mail_recipient_bcc($mail_bcc_2);
						$c_emailbuilder->mail_recipient_bcc($mail_bcc_3);
						$c_emailbuilder->mail_recipient_bcc($mail_bcc_4);
						$c_emailbuilder->mail_recipient_bcc($mail_bcc_5);
						$c_emailbuilder->mail_subject($mail_subject);
						$c_emailbuilder->mail_body($mail_body);
	
						//debug
						$debug_string['mail_to'] = $mail_to;
						$debug_string['mail_bcc'] = $mail_bcc;
						$debug_string['mail_from'] = $c_emailbuilder->mail_from;
						$debug_string['mail_name'] = $c_emailbuilder->mail_name;
						$debug_string['mail_subject'] = $mail_subject;
	

						list($email_err_code, $email_err_msg) = $c_emailbuilder->send_email();
						//*** BUILD AND SEND EMAIL ****/

						if ($email_err_code == "1"){
							$errormsg .= "<br />Error: Email could not be sent. " . $email_err_msg;
							$email_err_code = 1;
						}else{
							//set timed cookie to destroy after 60 seconds
							setcookie($cookie_name, 'fc_contact__'.date('d-m-y H:i:s'), time()+60, '/', $_SERVER['SERVER_NAME'], FALSE);//set cookie to expire in 60 seconds
							 // unset the form token in the session
							unset( $_SESSION['form_token']);
							
							$errormsg = "Success: Message sent.";
	
						}
	
						
	
						//debug
						$debug_string['mail_success'] = $mail_success;
	
	
					}//html body
					else
					{	
						$errormsg .= "<br />Error: Message body is blank.";
						$email_err_code = 1;
					}
	
				}//end if(isset($to_firstname) && isset($to_email) && isset($from_name) && isset($from_email))
				else
				{
					$required_fields=0;
					$errormsg .= "<br />Error: Not all required fields were submitted.";
					$email_err_code = 1;
				}
	
			}
	
		}//cookie
		else
		{
			//set cookie is set flag
			$mail_cookie_set = 1;
			$email_err_code = 1;
			$errormsg = "Error: The form has already been sent, please wait at least one minute before re-sending.";
		}
	
	}//session check


	$xml = "<root>";
		$xml .= "<message><![CDATA[".$errormsg."]]></message>";
		$xml .= "<status><![CDATA[".$email_err_code."]]></status>";
	$xml .= "</root>";	
	/************ XML OUTPUT********************/
	
	if($_GET['get_xml'] == 1){
		$XML_flag = 1;
	}

	//output XML string if XML_flag is set to 1
	if($XML_flag == 1){
		// !!! IMPORTANT !!! - the server must set the content type to XML header('Content-type: text/xml'); 
		header('Content-type: text/xml'); 
		echo $xml;
		exit;
	}

}//send	
?>