<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../helpers/EmailAddressValidator.php');?>
<?php

/********************************/
$userID = get_app_info('main_userID');
$app = $_POST['app'];
$listID = mysqli_real_escape_string($mysqli, $_POST['list_id']);
$line = $_POST['line'];
$time = time();
/********************************/

//if user did not enter anything
if($line=='')
{
	//show error msg
	header("Location: ".get_app_info('path').'/update-list?i='.$app.'&l='.$listID.'&e=2'); 
	exit;
}

$line_array = explode("\r\n", $line);

for($i=0;$i<count($line_array);$i++)
{
	$the_line = explode(',', $line_array[$i]);
	
	if(count($the_line)==1)
	{
		$name = '';
		$email = $the_line[0];
	}
	else
	{
		$name = $the_line[0];
		$email = $the_line[1];
	}
	
	$email = trim($email);
	
	$q = 'SELECT email FROM subscribers WHERE list = '.$listID.' AND (email = "'.$email.'" || email = " '.$email.'") AND userID = '.$userID;
	$r = mysqli_query($mysqli, $q);
	if (mysqli_num_rows($r) > 0){}
	else
	{
		//Check if this email is previously marked as bounced, if so, we shouldn't add it
		$q = 'SELECT email from subscribers WHERE (email = "'.$email.'" || email = " '.$email.'") AND bounced = 1';
		$r = mysqli_query($mysqli, $q);
		if (mysqli_num_rows($r) > 0){}
		else
		{
			$validator = new EmailAddressValidator;
			
			if ($validator->check_email_address(trim($email))) 
			{
				$q = 'INSERT INTO subscribers (userID, name, email, list, timestamp) values('.$userID.', "'.$name.'", "'.trim($email).'", '.$listID.', '.$time.')';
				$r = mysqli_query($mysqli, $q);
				if ($r){}
			}
		}
	}
}

header("Location: ".get_app_info('path').'/subscribers?i='.$app.'&l='.$listID); 

?>
