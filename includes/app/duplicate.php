<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$campaign_id = mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
	$app_id = mysqli_real_escape_string($mysqli, $_POST['on-brand']);
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//get brand's data
	$q = 'SELECT from_name, from_email, reply_to FROM apps WHERE id = '.$app_id;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$from_name = $row['from_name'];
			$from_email = $row['from_email'];
			$reply_to = $row['reply_to'];
	    }  
	}
	
	//get campaign's data
	$q2 = 'SELECT title, plain_text, html_text FROM campaigns WHERE id = '.$campaign_id;
	$r2 = mysqli_query($mysqli, $q2);
	if ($r2)
	{
	    while($row = mysqli_fetch_array($r2))
	    {
			$title = stripslashes($row['title']);
			$plain_text = stripslashes($row['plain_text']);
			$html_text = stripslashes($row['html_text']);
	    }  
	}
	
	//Insert into database
	$q3 = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, title, plain_text, html_text) VALUES ('.get_app_info('main_userID').', '.$app_id.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.addslashes($title).'", "'.addslashes($plain_text).'", "'.addslashes($html_text).'")';
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3)
	     header("Location: ".get_app_info('path')."/app?i=".$app_id);
	else
		echo 'Error duplicating.';
?>