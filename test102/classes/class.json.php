<?php
final class json extends db_object 
{

	// store the single instance ofobject
    private static $instance; 

	/////////////////////////////////////////////////
	// PRIVATE MEMBERS
	/////////////////////////////////////////////////
	
	//varable to hold object instances
	private $c_generic = null;
	private $c_cache = null;
	private $_id = null;
	private $_cache_on = true;
	
	
	//declare database constants
	private $DB_TABLE = "table";
	private $DB_TABLE_FIELDS = "id, name, attributes, comments, modified, created";
	
	/////////////////////////////////////////////////
	// PUBLIC MEMBERS
	/////////////////////////////////////////////////


	//CONSTRUCTOR function
	public function __construct(){	
		
		//fire up a connection to the database
		$this->connect_db();
	
		//create instance statically from registry class
		if($this->c_generic = Registry::get('generic'));
		if($this->c_cache = Registry::get('cache'));
		
		
	}//constructor
	
	// get instance of object or return existing object
    public static function get_singleton() 
    { 
        if (!self::$instance) 
        { 
            self::$instance = new json(); 
        } 

        return self::$instance; 
    }
	
	
	/* GET/SET $_forms */
          
	public function setId($id)
	{
	  $this->_id = (int) $id;
	  return $this;
	}
	
	public function getId()
	{
	  return $this->_id;
	}
	
	public function getById(){
		
			$cache_name =	'json_id_'.$this->_id;
			$cache = array('cache'=>array('name'=>$cache_name, 'expires'=>'+1 hour')); // Cache query to /cache/sql/page-navigation . $slug
			
			//look for existing cache
			if($this->c_cache->check($cache) && $this->_cache_on){
				
				$json_array = $this->c_cache->read();
				
				return $json_array;
				
			}else{
						
				$this->set_select();
				$this->set_from($this->DB_TABLE);
				$this->set_where("id = $this->_id");	
				
				$result_select = $this->get_data();
				$count_select = $this->numrows;//returns the total number of rows generated
				
				$results = $result_select->fetch_object();
				
				$json_array = $this->returnData($results, 'json');
				
				if($this->_cache_on){
					//create cache
					$this->c_cache->write($json_array);
				}		
	
				return $json_array;
			}
		
	}
	
	private function returnData(){
	
		$args = func_get_args();
		$type = (count($args) > 1) ? array_pop($args) : 'array';
		switch($type){
			case 'json':
				return json_encode($args);
			break;			
			default:
				return $args;
			break;
		}
	
	}
	
}