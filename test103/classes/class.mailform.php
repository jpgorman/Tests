<?php

class mailform extends db_object
{
   //version variable
	var $version = "1.0";
	//constructor variable
	var $data;
	//define variables to output sorted data as html and text
	var $html_output;
	var $text_output;

	//constructor method assigns passed in data
	function mailform($data) {
    	$this->data = $data;
	}

	//create a string containing all elements sent from the HHTP vars
   	function get_values(){
		//initiate a count variable to help fill the array
		$chk_cnt = 0;
		while(list($key, $value) = each($this->data))
		{ 
			//stripout first 4 chars of the key only perform the rest of this loop if the key has a prefix listed in the switch 
			$sub_key = substr($key, 0, 4);
			switch ($sub_key){
			case "chk_":
			case "txt_":
			case "txa_":
			case "sel_":
			case "hid_":
			case "rad_":
				if ($value == "")
				{
					$value = "blank<br />";
				}
				else
				{					
					//nl2br for textarea - use type txa_
					if ($sub_key == "txa_") //txt_
						$add = "<br />";
					else
						$add = "";	

					//strip off the first 4 characters of the key during output
					//then remove any html or php tags and then convert any quotes to ascii
					$temp = str_replace("_"," ","<strong>".substr($key,4)."</strong>") ." : $add". strip_tags($value)."<br />";
									
					//nl2br for textarea - use type txa_
					if ($sub_key == "txa_") //txt_
					{
						//$temp = nl2br($temp);
						$temp = $this->nl2br2($temp);
					}			
					
					$this->html_output .= $temp;
										
				}
			}
		$chk_cnt ++;
		}
		
		return $this->html_output;
	} 
	
	function get_text_values()
	{
		//replace all '<br /> tags with new lins characters'
		$this->text_output = str_replace("<br />", "\n", $this->html_output);
		$this->text_output = ereg_replace("(\n)", chr(13), $this->text_output);
		$this->text_output = strip_tags($this->text_output);
		return $this->text_output;
	}
	
	//to replace all linebreaks to <br />
	//the best solution (IMO) is:
	function nl2br2($string) 
	{
		$string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
		return $string;
	}	

}
?>