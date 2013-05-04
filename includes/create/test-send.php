<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/class.phpmailer.php');?>
<?php

//POST variables
$campaign_id = mysqli_real_escape_string($mysqli, $_POST['campaign_id']);
$test_email = mysqli_real_escape_string($mysqli, $_POST['test_email']);
$test_email = str_replace(" ", "", $test_email);
$test_email_array = explode(',', $test_email);

//select campaign to send test email
$q = 'SELECT * FROM campaigns WHERE id = '.$campaign_id.' AND userID = '.get_app_info('main_userID');
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
    while($row = mysqli_fetch_array($r))
    {
    	$from_name = stripslashes($row['from_name']);
    	$from_email = stripslashes($row['from_email']);
    	$reply_to = stripslashes($row['reply_to']);
		$title = stripslashes($row['title']);
		$plain_text = stripslashes($row['plain_text']);
		$html_text = stripslashes($row['html_text']);
    }  
    
    //tags for subject
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{		
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$title = str_replace($tag, $fallback, $title);
	}
    
    //tags for HTML
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+),\s*fallback=/i', $html_text, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*)\]/i', $html_text, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*\])/i', $html_text, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{   
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$html_text = str_replace($tag, $fallback, $html_text);
	}
	//tags for Plain text
	preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+),\s*fallback=/i', $plain_text, $matches_var, PREG_PATTERN_ORDER);
	preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*)\]/i', $plain_text, $matches_val, PREG_PATTERN_ORDER);
	preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*\])/i', $plain_text, $matches_all, PREG_PATTERN_ORDER);
	$matches_var = $matches_var[1];
	$matches_val = $matches_val[1];
	$matches_all = $matches_all[1];
	for($i=0;$i<count($matches_var);$i++)
	{   
		$field = $matches_var[$i];
		$fallback = $matches_val[$i];
		$tag = $matches_all[$i];
		//for each match, replace tag with fallback
		$plain_text = str_replace($tag, $fallback, $plain_text);
	}
    
    //set web version links
	$html_text = str_replace('<webversion', '<a href="#webversion-not-active-during-tests" ', $html_text);
	$html_text = str_replace('</webversion>', '</a>', $html_text);
	$plain_text = str_replace('[webversion]', '[webversion-not-active-during-tests]', $plain_text);
	
	//set unsubscribe links
	$html_text = str_replace('<unsubscribe', '<a href="#unsubscribes-not-active-during-tests" ', $html_text);
	$html_text = str_replace('</unsubscribe>', '</a>', $html_text);
	$plain_text = str_replace('[unsubscribe]', '[unsubscribes-not-active-during-tests]', $plain_text);
	
	//get smtp settings
	$q3 = 'SELECT apps.smtp_host, apps.smtp_port, apps.smtp_ssl, apps.smtp_username, apps.smtp_password FROM campaigns, apps WHERE apps.id = campaigns.app AND campaigns.id = '.$campaign_id;
	$r3 = mysqli_query($mysqli, $q3);
	if ($r3 && mysqli_num_rows($r3) > 0)
	{
	    while($row = mysqli_fetch_array($r3))
	    {
			$smtp_host = $row['smtp_host'];
			$smtp_port = $row['smtp_port'];
			$smtp_ssl = $row['smtp_ssl'];
			$smtp_username = $row['smtp_username'];
			$smtp_password = $row['smtp_password'];
	    }  
	}
}

for($i=0;$i<count($test_email_array);$i++)
{
	//Email tag
	$html_text2 = str_replace('[Email]', $test_email_array[$i], $html_text);
	$plain_text2 = str_replace('[Email]', $test_email_array[$i], $plain_text);
	$title2 = str_replace('[Email]', $test_email_array[$i], $title);
	
	//send test email
	$mail = new PHPMailer();
	if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
	{
		$mail->IsAmazonSES();
		$mail->AddAmazonSESKey(get_app_info('s3_key'), get_app_info('s3_secret'));
	}
	else if($smtp_host!='' && $smtp_port!='' && $smtp_username!='' && $smtp_password!='')
	{
		$mail->IsSMTP();
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = $smtp_ssl;
		$mail->Host = $smtp_host;
		$mail->Port = $smtp_port; 
		$mail->Username = $smtp_username;  
		$mail->Password = $smtp_password;
	}
	$mail->Timezone   = get_app_info('timezone');
	$mail->CharSet	  =	"UTF-8";
	$mail->From       = $from_email;
	$mail->FromName   = $from_name;
	$mail->Subject = $title2;
	$mail->AltBody = $plain_text2;
	$mail->MsgHTML($html_text2);
	$mail->AddAddress($test_email_array[$i], '');
	$mail->AddReplyTo($reply_to, $from_name);
	if(file_exists('../../uploads/attachments/'.$campaign_id))
	{
		foreach(glob('../../uploads/attachments/'.$campaign_id.'/*') as $attachment){
			if(file_exists($attachment))
			    $mail->AddAttachment($attachment);
		}
	}
	$mail->Send();
}

?>