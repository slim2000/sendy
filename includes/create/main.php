<?php 
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	function get_app_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM apps WHERE id = "'.get_app_info('app').'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row[$val];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_saved_data($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		global $edit;
		
		$q = 'SELECT '.$val.' FROM campaigns WHERE id = "'.mysqli_real_escape_string($mysqli, $_GET['c']).'" AND userID = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	$value = stripslashes($row[$val]);
		    	
		    	//if title
		    	if($val == 'title' && !$edit)
		    	{
			    	//tags for subject
					preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+),\s*fallback=/i', $value, $matches_var, PREG_PATTERN_ORDER);
					preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*)\]/i', $value, $matches_val, PREG_PATTERN_ORDER);
					preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*\])/i', $value, $matches_all, PREG_PATTERN_ORDER);
					$matches_var = $matches_var[1];
					$matches_val = $matches_val[1];
					$matches_all = $matches_all[1];
					for($i=0;$i<count($matches_var);$i++)
					{		
						$field = $matches_var[$i];
						$fallback = $matches_val[$i];
						$tag = $matches_all[$i];
						//for each match, replace tag with fallback
						$value = str_replace($tag, $fallback, $value);
					}
					$value = str_replace('[Email]', get_saved_data('from_email'), $value);
		    	}
				
				return $value;
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_list_quantity($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT COUNT(id) FROM subscribers WHERE list = '.$val.' AND unsubscribed = 0 AND bounced = 0 AND complaint = 0 AND confirmed = 1';
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row['COUNT(id)'];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_fee($val)
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT '.$val.' FROM apps WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['i']);
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
		    	if($val=='currency')
					return $row[$val];
				else
					return number_format($row[$val], 3, '.', '');
		    }  
		}
	}
	
	//------------------------------------------------------//
	function get_paypal()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT paypal FROM login WHERE id = '.get_app_info('main_userID');
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				return $row['paypal'];
		    }  
		}
	}
	
	//------------------------------------------------------//
	function paid()
	//------------------------------------------------------//
	{
		global $mysqli;
		$q = 'SELECT delivery_fee, cost_per_recipient FROM apps WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['i']);
		$r = mysqli_query($mysqli, $q);
		if ($r && mysqli_num_rows($r) > 0)
		{
		    while($row = mysqli_fetch_array($r))
		    {
				$cost_per_recipient = $row['cost_per_recipient'];
				$delivery_fee = $row['delivery_fee'];
		    }  
		    
		    if(($delivery_fee=='' && $cost_per_recipient=='') || ($delivery_fee==0 && $cost_per_recipient==0))
		    {
		    	//payment not required
		    	return true;
		    }
		    else
		    {
		    	//payment required
			    return false;
		    }	    
		}
	}
?>