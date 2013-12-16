<?php
class GeneratePagingLinks extends db_object
{ 
	//passed in
	var $current_page = 1; 
	var $total_count = 0;
	var $records_per_page = 10;

	//calculated
	var $total_pages;
    var $first_id = 0;
    var $prev_id = 0;
	var $display_html = "";
	var $list_html = "";
	var $show_page_totals = false;
	var $show_next_links = false;
	var $mod_rewrite = MOD_REWRITE;
	
	//Constructor function
	function GeneratePagingLinks($current_page, $total_count, $records_per_page) 
	{ 	
		//calculated
		$this->total_pages = 0;
		$this->first_id = 0;
		$this->prev_id = 0;
		$this->display_html = "";
		$this->list_html = "";
		
		//passed in	
		if(!empty($current_page)){
			$this->current_page = $current_page; 
		}
		
		$this->total_count = $total_count; 
		$this->records_per_page = $records_per_page; 
		
		$this->SetPagingVars();
	}//function GeneratePagingLinks
	
	// function
	function show_page_totals($input_show_page_totals = NULL)
	{
		if($input_show_page_totals <> NULL){
       		$this->show_page_totals = $this->cleanstring(trim($input_show_page_totals));
		}
		//debug echo $this->Filename;
	}
	
	// function
	function show_next_links($input_show_page_next_links = NULL)
	{
		if($input_show_page_next_links <> NULL){
       		$this->show_next_links = $this->cleanstring(trim($input_show_page_next_links));
		}
		//debug echo $this->Filename;
	}

	function SetPagingVars() 
	{ 
		//get total number of pages
		$this->total_pages = ceil( $this->total_count / $this->records_per_page );
		
		// first setup first/prev link id's
		if ($this->current_page == 1)
		{
			$this->first_id = 0;
			$this->prev_id = 0;
		}
		else
		{
			$this->first_id = 1;
			$this->prev_id = $this->current_page - 1;
		}
		
		// now setup next/last link id's
		if ($this->current_page == $this->total_pages)
		{
			$this->next_id = 0;
			$this->last_id = 0;
		}
		else
		{
			$this->next_id = $this->current_page + 1;
			$this->last_id = $this->total_pages;
		}
		
		// offset for sql query
		$this->offset = ( $this->current_page - 1 ) * $this->records_per_page;		
		
	}//function SetPagingVars
		
	//WE DONT ALWAYS WANT A MOD REWRITE LINK SO SEPERATE FUNCTION SO WE CAN CHOOSE
	//IN THE PAGE REQUESTING IT
	function return_html($MOD_URL="/", $paging_query_str="", $spacer=" | ", $css_style="") 
	{ 		
		if ($css_style!="")
			$this->display_html .= "<span class=\"$css_style\">";
		else
			$this->display_html .= "<span>";
	
		if ($this->first_id == 0)
			$this->display_html .= "First";
		else
		{
			//if mod flag is on then diff layout for links
			if ($this->mod_rewrite==1)
			{
				$this->display_html .= "<a href=\"/".$MOD_URL.'/p1/'.$paging_query_str."\" class=\"ajax\">First</a> ";
			}
			else
			{
				$this->display_html .= "<a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=1\" class=\"ajax\">First</a> ";
			}		
		}
			
		$this->display_html .= $spacer;
		
		if ($this->prev_id == 0)
			$this->display_html .= "Prev";
		else
		{
			//if mod flag is on then diff layout for links
			if ($this->mod_rewrite==1)
			{
				$this->display_html .= "<a href=\"/".$MOD_URL."/p".$this->prev_id."/".$paging_query_str."\" class=\"ajax\">Prev</a>";
			}
			else
			{
				$this->display_html .= "<a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$this->prev_id."\" class=\"ajax\">Prev</a>";
			}		
		}	
			
		$this->display_html .= $spacer;
		
		if ($this->next_id == 0)
			$this->display_html .= "Next";
		else
		{
			//if mod flag is on then diff layout for links
			if ($this->mod_rewrite==1)
			{
				$this->display_html .= "<a href=\"/".$MOD_URL."/p".$this->next_id."/".$paging_query_str."\" class=\"ajax\">Next</a>";
			}
			else
			{
				$this->display_html .= "<a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$this->next_id."\" class=\"ajax\">Next</a>";
			}		
		}			
			
		$this->display_html .= $spacer;
		
		if ($this->last_id == 0)
			$this->display_html .= "Last";
		else
		{
			//if mod flag is on then diff layout for links
			if ($this->mod_rewrite==1)
			{
				$this->display_html .= "<a href=\"/".$MOD_URL."/p".$this->last_id."/".$paging_query_str."\" class=\"ajax\">Last</a>";
			}
			else
			{
				$this->display_html .= "<a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$this->last_id."\" class=\"ajax\">Last</a>";
			}		
		}
						
		$this->display_html .= "</span>";
		
		if ($this->show_page_totals==true)
		{			
			$this->display_html .= " - Page ".$this->current_page." of ".$this->total_pages;
		}
		
		return $this->display_html;
	}//function return_html_modrewrite		
	
	
	function return_numbered_list($MOD_URL="/", $paging_query_str="", $spacer=" ", $css_style="", $next_link=0) 
	{
	
		if ($css_style!="")
			$this->list_html .= "<span class=\"$css_style\">";
		else
			$this->list_html .= "<span>";
			
		//generate list of page links	
		for ($i = 1; $i <= $this->total_pages; $i++)
		{	
			if ($i > 1)
				$this->list_html .= $spacer;
				
			if ($i == $this->current_page){
				//if mod flag is on then diff layout for links
				if ($this->mod_rewrite==1)
				{
					$this->list_html .= "<a href=\"/".$MOD_URL.$paging_query_str."/p".$i."/\" class=\"$css_style on ajax\">".$i."</a>";
				}
				else
				{
					$this->list_html .= "<a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$i."\" class=\"$css_style on ajax\">".$i."</a>";
				}
			}else{
				//if mod flag is on then diff layout for links
				if ($this->mod_rewrite==1)
				{
					$this->list_html .= "<a href=\"/".$MOD_URL.$paging_query_str."/p".$i."/\" class=\"$css_style ajax\">".$i."</a>";
				}
				else
				{
					$this->list_html .= "<a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$i."\" class=\"$css_style ajax\">".$i."</a>";
				}
			}		
		
		}//end for			
		
		if ($next_link==1)
		{
			if ($this->next_id == 0)
				$this->list_html .= "";//"&nbsp; next";
			else
			{
				//if mod flag is on then diff layout for links
				if ($this->mod_rewrite==1)
				{
					$this->list_html .= "&nbsp; <a href=\"/".$MOD_URL.$paging_query_str."/p".$this->next_id."/\" class=\"$css_style\">next</a>";
				}
				else
				{
					$this->list_html .= "&nbsp; <a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$this->next_id."\" class=\"$css_style\">next</a>";
				}//end if
			}//end if
		}//end if
		
		if($this->show_next_links == true){
			if ($this->next_id == 0)
				$this->list_html .= "";//"&nbsp; next";
			else
			{
				//if mod flag is on then diff layout for links
				if ($this->mod_rewrite==1)
				{
					$this->list_html .= "&nbsp; <a href=\"/".$MOD_URL.$paging_query_str."/p".$this->next_id."/\" class=\"$css_style\">next</a>";
				}
				else
				{
					$this->list_html .= "&nbsp; <a href=\"".$_SERVER['PHP_SELF']."?".$paging_query_str."&amp;current_page=".$this->next_id."\" class=\"$css_style\">next</a>";
				}//end if
			}//end if
		}
		
		$this->list_html .= "</span>";
		
		
		if ($this->show_page_totals==true)
		{			
			$this->list_html .= " - Page ".$this->current_page." of ".$this->total_pages;
		}
		
		return $this->list_html;
	}//function return_numbered_list		

}//class GeneratePagingLinks


?>