<?php
class email_builder extends PHPMailer 
{
// CLASS METHODS
// ****************
// DATABASE METHODS
// ----------------
// select_message_db
// select_message_all_db
// insert_message_db
// ----------------
// GET input methods
// ----------------
// current_msg_id
// current_msg_pid
// from_id
// to_id
// subject
// message
// ****************


/////////////////////////////////////////////////
// PRIVATE VARIABLES - private
/////////////////////////////////////////////////
//Outgoing Mail Server: mail.yourbeautyassistant.co.uk (server requires authentication) port 25
//Outgoing Mail Server: (SSL) victorious.eukhost.com (server requires authentication) port 465
private $MAIL_HOST = "mail.yourbeautyassistant.co.uk"; //localhost  
private $MAIL_SMTP_USERNAME = "info@yourbeautyassistant.co.uk"; //
private $MAIL_SMTP_PASSWORD = ""; //
private $MAIL_PORT = '26'; //localhost

/////////////////////////////////////////////////
// PUBLIC VARIABLES - public
/////////////////////////////////////////////////
public $email_send_type = ''; //normal(default)="", smtp_noauth, smtp_auth
public $send_type = 'html'; //plain html
public $mail_recipient = NULL;
public $mail_recipient_name = NULL;
public $mail_from = "info@yourbeautyassistant.co.uk";
public $mail_name = "Your Beauty Assistant";
public $mail_subject = "Message from Your Beauty Assistant";
public $mail_body = NULL;
public $text_body = NULL;

	//CONSTRUCTOR function
	function __construct(){
		//create instance statically from registry class
		if($this->c_dbobject = Registry::get('db'));
		
	}
	
	// PUBLIC function
	public function email_send_type($input_email_send_type = NULL)
	{
		if($input_email_send_type <> NULL){
       		$this->email_send_type = $this->c_dbobject->cleanstring(trim($input_email_send_type));
		}
		//debug echo $this->email_send_type;
    }

	// PUBLIC function
	public function send_type($input_send_type = NULL)
	{
		if($input_send_type <> NULL){
       		$this->send_type = $this->c_dbobject->cleanstring(trim($input_send_type));
		}
		//debug echo $this->Filename;
    }
	
	// PUBLIC function
	public function mail_recipient($input_mail_recipient = NULL)
	{
		if($input_mail_recipient <> NULL){
       		$this->mail_recipient = $this->c_dbobject->cleanstring(trim($input_mail_recipient));
		}
		//debug echo $this->mail_recipient;
    }
	
	public function mail_recipient_name($input_mail_recipient_name = NULL)
	{
		if($input_mail_recipient_name <> NULL){
       		$this->mail_recipient_name = $this->c_dbobject->cleanstring(trim($input_mail_recipient_name));
		}
		//debug echo $this->mail_recipient_name;
    }
	
	// PUBLIC function
	public function mail_recipient_cc($input_mail_recipient_cc = NULL)
	{
		if($input_mail_recipient_cc <> NULL){
       		$this->mail_recipient_cc = $this->c_dbobject->cleanstring(trim($input_mail_recipient_cc));
		}
		//debug echo $this->mail_recipient_cc;
    }
	
	// PUBLIC function
	public function mail_recipient_bcc($input_mail_recipient_bcc = NULL)
	{

		if($input_mail_recipient_bcc <> NULL){

			//run cleanstring
			$input_mail_recipient_bcc = $this->c_dbobject->cleanstring(trim($input_mail_recipient_bcc));

			//add to the insert_value array
			//first set var £cur to the next array key number
			$cur = count($this->mail_recipient_bcc);
			$this->mail_recipient_bcc[$cur] = $input_mail_recipient_bcc;
			//echo $this->insert_value[$cur];
		}

		//debug echo $this->mail_recipient_bcc;
    }		
	
	// PUBLIC function
	public function mail_from($input_mail_from = NULL)
	{
		if($input_mail_from <> NULL){
       		$this->mail_from = $this->c_dbobject->cleanstring(trim($input_mail_from));
		}
		//debug echo $this->Filename;
    }
	
	// PUBLIC function
	public function mail_name($input_mail_name = NULL)
	{
		if($input_mail_name <> NULL)
		{
       		$this->mail_name = $this->c_dbobject->cleanstring(trim($input_mail_name));
		}
		//debug echo $this->Filename;
    }
	
	// PUBLIC function
	public function mail_subject($input_mail_subject = NULL)
	{
		if($input_mail_subject <> NULL)
		{
       		$this->mail_subject = $this->c_dbobject->cleanstring(trim($input_mail_subject));
		}
		//debug echo $this->Filename;
    }
	
	// PUBLIC function
	public function mail_body($input_mail_body = NULL)
	{
		if($input_mail_body <> NULL)
		{
			$this->mail_body = $input_mail_body;
			//generate plain text version for message
			$this->text_body();
       		//$this->mail_body = $this->c_dbobject->cleanstring(trim($input_mail_body));
		}
		//debug echo $this->Filename;
    }//mail_body
	
	// PUBLIC function
	public function text_body()
	{
		//replace all '<br> tags with new lins characters'
		$this->text_body = str_replace("<br>", "\n\r", $this->mail_body);
		$this->text_body = str_replace("<br />", "\n\r", $this->mail_body);
		$this->text_body = ereg_replace("(\n\r)", chr(13), $this->text_body);
		$this->text_body = strip_tags($this->text_body);
	}//text_body
	
	public function send_email()
	{
		//*** EMAIL SETUP/SEND ***
		if (EMAIL_FLAG==1):
			if ($this->email_send_type=="smtp_auth"):
				if ($this->send_type == "html"):
					//*** SMTP AUTH MAIL SEND USING VALID ACCOUNT ***
										
					//*** SMTP AUTH MAIL SEND USING VALID ACCOUNT ***						
				else: //plain
					//*** SMTP AUTH MAIL SEND USING VALID ACCOUNT ***
	
					//*** SMTP AUTH MAIL SEND USING VALID ACCOUNT ***
				endif;
			elseif ($this->email_send_type=="smtp_noauth"):
				if ($this->send_type == "html"):																	
					//*** SEND VIA SMTP - MAIL SEND NOT USING AUTHENTICATION ***
					$mail = new PHPMailer();
					$mail->IsSMTP(); 						// telling the class to use SMTP
					$mail->Host = $MAIL_HOST;  				// SMTP servers
					$mail->From = $this->mail_from;  				// Sender address
					$mail->FromName = $this->mail_name;     	// Appears in the FROM field
					$mail->Subject = $this->mail_subject;
					$mail->AltBody = $this->text_body;
					$mail->MsgHTML($this->mail_body);
					$mail->SMTPDebug = true;	//DEBUGGING
					//if ($MAIL_PORT != "")
					//	$mail->Port = $MAIL_PORT; 
					//$mail->Port = '26';   
					//RECIPIENT
					if (!empty($this->mail_recipient_name)):
						$mail->AddAddress($this->mail_recipient, $this->mail_recipient_name);
					else:
						$mail->AddAddress($this->mail_recipient);
					endif;
					//CC
					if (!empty($this->mail_recipient_cc)):
						$mail->AddCC($this->mail_recipient_cc);
					endif;
					//BCC
					if (!empty($this->mail_recipient_bcc)):

						//iterate through $this->mail_recipient_bcc array to build insert SQL
						for($i = 0; $i < count($this->mail_recipient_bcc); $i++)
						{
							$mail->AddBCC($this->mail_recipient_bcc[$i]);
						}

					endif;
					//$mail->AddAddress("dan@firecast.co.uk"); //Recipient address and name
					//$mail->AddAddress("tony_grove@hotmail.com"); //Recipient address and name														
					//*** SEND VIA SMTP - MAIL SEND NOT USING AUTHENTICATION ***
				else: //plain
					//*** SEND VIA SMTP - MAIL SEND NOT USING AUTHENTICATION ***
	
					//*** SEND VIA SMTP - MAIL SEND NOT USING AUTHENTICATION ***					
				endif;					
			else: //normal	
				if ($this->send_type == "html"):			
					//*** NORMAL ***
					$mail = new PHPMailer();
					$mail->From = $this->mail_from;  				// Sender address
					$mail->FromName = $this->mail_name;     	// Appears in the FROM field
					$mail->Subject = $this->mail_subject;
					$mail->AltBody = $this->text_body;	
					$mail->MsgHTML($this->mail_body);									
					//RECIPIENT
					if (!empty($this->mail_recipient_name)):
						$mail->AddAddress($this->mail_recipient, $this->mail_recipient_name);
					else:
						$mail->AddAddress($this->mail_recipient);
					endif;
					//CC
					if (!empty($this->mail_recipient_cc)):
						$mail->AddCC($this->mail_recipient_cc);
					endif;
					//BCC
					if (!empty($this->mail_recipient_bcc)):

						//iterate through $this->mail_recipient_bcc array to build insert SQL
						for($i = 0; $i < count($this->mail_recipient_bcc); $i++)
						{
							$mail->AddBCC($this->mail_recipient_bcc[$i]);
						}

					endif;
					//$mail->AddAddress("dan@firecast.co.uk"); //Recipient address and name
					//$mail->AddAddress("tony_grove@hotmail.com"); //Recipient address and name																		
					//*** NORMAL ***	
				else: //plain
					//*** NORMAL ***
					$mail = new PHPMailer();
					$mail->IsHTML(false); 
					$mail->From = $this->mail_from;  				// Sender address
					$mail->FromName = $this->mail_name;     	// Appears in the FROM field
					$mail->Subject = $this->mail_subject;
					$mail->Body = $this->mail_body;								
					//RECIPIENT
					if (!empty($this->mail_recipient_name)):
						$mail->AddAddress($this->mail_recipient, $this->mail_recipient_name);
					else:
						$mail->AddAddress($this->mail_recipient);
					endif;
					//CC
					if (!empty($this->mail_recipient_cc)):
						$mail->AddCC($this->mail_recipient_cc);
					endif;
					//BCC
					if (!empty($this->mail_recipient_bcc)):

						//iterate through $this->mail_recipient_bcc array to build insert SQL
						for($i = 0; $i < count($this->mail_recipient_bcc); $i++)
						{
							$mail->AddBCC($this->mail_recipient_bcc[$i]);
						}

					endif;
					//$mail->AddAddress("dan@firecast.co.uk"); //Recipient address and name
					//$mail->AddAddress("tony_grove@hotmail.com"); //Recipient address and name																		
					//*** NORMAL ***					
				endif;				
			endif;
			
			if ($mail->Send()):
				$mail_result = 0;
				
				$fcn_msg.="An email has been sent. <br />Thank You for your interest.<br />";
				return array($mail_result, $fcn_msg);
			else:
				$mail_result = 1;
				
				$fcn_msg.= "<br />Email could not be sent.";		
				$fcn_msg.= "<br />Mailer Error: " . $mail->ErrorInfo;
				
				return array($mail_result, $fcn_msg);
			endif;	
		else:	
			$mail_result = 1;
				
			$fcn_msg.= "Emails turned off<br />";			
			
			return array($mail_result, $fcn_msg);		
		endif;
	}//send_email


}//class cms
?>