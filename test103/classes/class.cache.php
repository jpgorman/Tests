<?php
//contains generic functions that can be used on any site

final class cache extends db_object 
{ 
	/////////////////////////////////////////////////
	// PRIVATE VARIABLES
	/////////////////////////////////////////////////

	private $name			= null;
	private $path			= CACHE_PATH; // set in variables file
	private $duration		= null;
	private $cache_array	= array();
	
	//Constructor method
	public function __construct() 
	{ 
		//fire up a connection to the database		
		parent::__construct(); 
	}
	
	/////////////////////////////////////////////////
	// SETTER METHODS
	/////////////////////////////////////////////////
	
	// PUBLIC method
	public function read($input_read = null)
	{	
		if(!empty($this->cache_array)){
			if(!empty($this->name) && !empty($this->path)){
				if(is_file($this->path.$this->name)){
					
					//check if cache is still valid
					if($this->check()){
					
						//unserialize array
						$read_data = unserialize(file_get_contents($this->path.$this->name));
						
						return $read_data;
						
					}else{
						return false;
					}
					
				}else{
					return false;
				}
				
			}else{
				return false;
			}
			
		}else{
			return false;
		}
    }
	
	// PUBLIC method
	public function write($input_data = null)
	{	
		echo $this->name;
		if(!empty($this->cache_array)){
			if(is_dir($this->path) && isset($this->name) && !empty($input_data)){
								
				//serialize array
				$input_data = serialize($input_data);
				
				//write contents to file
				file_put_contents($this->path.$this->name, $input_data, LOCK_EX);			
				
				//set cache duration
				$this->set_timestamp();	
				
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
    }

	// PUBLIC method
	public function check($cache=null)
	{
		//initiliase cache vars
		$this->initialise($cache);
		
		if(!empty($this->name) && !empty($this->path)){
			if(is_file($this->path.$this->name)){
				//look for timestamp
				$file = fopen($this->path.$this->name, 'r');
				//set cursor position to where date should start 21 bytes from the end of the file
				fseek($file, -21, SEEK_END);
				//read date which is 21 bytes long
				$fdate = fread($file, 21);
				
				//use regex to match timestamp if present
				if (preg_match("/\[([0-9]{2})-([0-9]{2})-([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})\]/i", $fdate)) {
					//remove brackets
				    $fdate = substr($fdate, 1, -1);
				    $current_date = date("d-m-Y H:i:s");
						    
				    //delete cache if the duration has been reached
				   	if($current_date > $fdate){
				   		@unlink($this->path.$this->name);
				   		return false;
				   	}else{
				   		return true;
				   	}
				   							
				}else{
					return true;
				}
				
			}else{
				return false;
			}
		}else{
			return false;
		}
    }
    
    // PRIVATE method
	private function initialise($cache=null)
	{
		$this->cache_array = null;

		if(is_array($cache) && $cache['cache'] !== false && empty($this->cache_array)){
			
			$this->cache_array = $cache;
			
			foreach($this->cache_array['cache'] as $name => $value){
				if(($name=='name')){
					$this->name = $this->make_url_safe($this->cleanstring_plain($value));
				}
				if(($name=='expires')){
					$this->duration = $this->cleanstring_plain($value);
				}
			}
		}
    }
    
    // PRIVATE method
	private function set_timestamp(){
		
		if(!empty($this->duration)){
			
			$date = null;
			
			switch($this->duration){
				case'+5 mins':
						$date = date("d-m-Y H:i:s", mktime(date('H'),date("i")+5, date("s"), date("m"), date("d"), date('Y')));
				break;	
				case'+1 hour':
						$date = date("d-m-Y H:i:s", mktime(date('H')+1,date("i"), date("s"), date("m"), date("d"), date('Y')));
				break;					
				case'+1 day':
						$date = date("d-m-Y H:i:s", mktime(date('H'),date("i"), date("s"), date("m"), date("d")+1, date('Y')));
				break;					
				case'+1 week':
						$date = date("d-m-Y H:i:s", mktime(date('H'),date("i"), date("s"), date("m"), date("d")+7, date('Y')));
				break;				
				case'+1 month':
						$date = date("d-m-Y H:i:s", mktime(date('H')+1,date("i"), date("s"), date("m")+1, date("d"), date('Y')));
				break;
			}
			if(!empty($this->path) && !empty($this->name) && !empty($date)){
				//append to cache file
				file_put_contents($this->path.$this->name, '['.$date.']', FILE_APPEND);
			}
		}
	}
	
	public function clear_cache($slug=null){		
		
		
		//delete any page dependant cached files
		//file types to ignore
		$disallowed = array('.', '..', '.svn');
		
		//remove any cahce files prefixed with 'page-'
		if ($handle = opendir(CACHE_PATH)) 
		{					
		    /* This is the correct way to loop over the directory. */
		    while (false !== ($file = readdir($handle))) {
		    	if (!empty($file) && !in_array($file, $disallowed))
				{
					if(!empty($slug)){
						//remove named page cache files
						$cache_prefix = $slug.'-';
						if(substr($file, 0, 5)===$cache_prefix){
							@unlink(CACHE_PATH.$file);
						}
					}else{
						//remove page cache files
						if(substr($file, 0, 5)==='page-'){
							@unlink(CACHE_PATH.$file);
						}
						//remove news cache files
						if(substr($file, 0, 5)==='news-'){
							@unlink(CACHE_PATH.$file);
						}
					}
		        }
		    }
			//close handle on file
			closedir($handle); 
		}
	}
    
	
}//class cache
?>