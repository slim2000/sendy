<?php 
	include('includes/config.php');
	//--------------------------------------------------------------//
	function dbConnect() { //Connect to database
	//--------------------------------------------------------------//
	    // Access global variables
	    global $mysqli;
	    global $dbHost;
	    global $dbUser;
	    global $dbPass;
	    global $dbName;
	    
	    // Attempt to connect to database server
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	
	    // If connection failed...
	    if ($mysqli->connect_error) {
	        fail("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\"/><link rel=\"Shortcut Icon\" type=\"image/ico\" href=\"/img/favicon.png\"><title>"._('Can\'t connect to database')."</title></head><style type=\"text/css\">body{background: #ffffff;font-family: Helvetica, Arial;}#wrapper{background: #f2f2f2;width: 300px;height: 110px;margin: -140px 0 0 -150px;position: absolute;top: 50%;left: 50%;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;}p{text-align: center;line-height: 18px;font-size: 12px;padding: 0 30px;}h2{font-weight: normal;text-align: center;font-size: 20px;}a{color: #000;}a:hover{text-decoration: none;}</style><body><div id=\"wrapper\"><p><h2>"._('Can\'t connect to database')."</h2></p><p>"._('There is a problem connecting to the database. Please try again later.')."</p></div></body></html>");
	    }
	    
	    global $charset; mysqli_set_charset($mysqli, isset($charset) ? $charset : "utf8");
	    
	    return $mysqli;
	}
	//--------------------------------------------------------------//
	function fail($errorMsg) { //Database connection fails
	//--------------------------------------------------------------//
	    echo $errorMsg;
	    exit;
	}
	// connect to database
	dbConnect();
?>
<?php
	include('includes/helpers/short.php');
	
	//get variable
	$i = mysqli_real_escape_string($mysqli, $_GET['i']);
	$i_array = explode('/', $i);
	$userID = short($i_array[0], true);
	$link_id = short($i_array[1], true);
	
	$q = 'SELECT clicks, link FROM links WHERE id = '.$link_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$clicks = $row['clicks'];
			$link = htmlspecialchars_decode($row['link']);
			
			if($clicks=='')
				$val = $userID;
			else
			{
				$clicks .= ','.$userID;
				$val = $clicks;
			}
	    }  
	}
	
	$q2 = 'UPDATE links SET clicks = "'.$val.'" WHERE id = '.$link_id;
	$r2 = mysqli_query($mysqli, $q2);
	if ($r2){}
	
	//tags for links
	$q = 'SELECT name, email, list, custom_fields FROM subscribers WHERE id = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$name = $row['name'];
			$email = $row['email'];
			$list_id = $row['list'];
			$custom_values = $row['custom_fields'];
	    }  
	}	
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+),\s*fallback=/i', $link, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*)\]/i', $link, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*\])/i', $link, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{   
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		
		//if tag is Name
		if($field=='Name')
		{
			if($name=='')
				$link = str_replace($tag, $fallback, $link);
			else
				$link = str_replace($tag, $name, $link);
		}
		else //if not 'Name', it's a custom field
		{
			//if subscriber has no custom fields, use fallback
			if($custom_values=='')
				$link = str_replace($tag, $fallback, $link);
			//otherwise, replace custom field tag
			else
			{					
				$q5 = 'SELECT custom_fields FROM lists WHERE id = '.$list_id;
				$r5 = mysqli_query($mysqli, $q5);
				if ($r5)
				{
				    while($row2 = mysqli_fetch_array($r5)) $custom_fields = $row2['custom_fields'];
				    $custom_fields_array = explode('%s%', $custom_fields);
				    $custom_values_array = explode('%s%', $custom_values);
				    $cf_count = count($custom_fields_array);
				    $k = 0;
				    
				    for($j=0;$j<$cf_count;$j++)
				    {
					    $cf_array = explode(':', $custom_fields_array[$j]);
					    $key = str_replace(' ', '', $cf_array[0]);
					    
					    //if tag matches a custom field
					    if($field==$key)
					    {
					    	//if custom field is empty, use fallback
					    	if($custom_values_array[$j]=='')
						    	$link = str_replace($tag, $fallback, $link);
					    	//otherwise, use the custom field value
					    	else
					    	{
					    		//if custom field is of 'Date' type, format the date
					    		if($cf_array[1]=='Date')
						    		$link = str_replace($tag, strftime("%a, %b %d, %Y", $custom_values_array[$j]), $link);
					    		//otherwise just replace tag with custom field value
					    		else
							    	$link = str_replace($tag, $custom_values_array[$j], $link);
					    	}
					    }
					    else
					    	$k++;
				    }
				    if($k==$cf_count)
				    	$link = str_replace($tag, $fallback, $link);
				}
			}
		}
	}
	//Email tag
	$link = str_replace('[Email]', $email, $link);
	
	//redirect to link
	header("Location: $link");
?>