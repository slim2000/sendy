<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$userID = mysqli_real_escape_string($mysqli, $_POST['uid']);
	$company = mysqli_real_escape_string($mysqli, $_POST['company']);
	$name = mysqli_real_escape_string($mysqli, $_POST['personal_name']);
	$email = mysqli_real_escape_string($mysqli, $_POST['email']);
	$password = mysqli_real_escape_string($mysqli, $_POST['password']);
	$aws_key = isset($_POST['aws_key']) ? mysqli_real_escape_string($mysqli, $_POST['aws_key']) : '';
	$aws_secret = isset($_POST['aws_secret']) ? mysqli_real_escape_string($mysqli, $_POST['aws_secret']) : '';
	$paypal = isset($_POST['paypal']) ? mysqli_real_escape_string($mysqli, $_POST['paypal']) : '';
	$timezone = mysqli_real_escape_string($mysqli, $_POST['timezone']);
	$language = mysqli_real_escape_string($mysqli, $_POST['language']);
	//app data
	$from_name = isset($_POST['from_name']) ? mysqli_real_escape_string($mysqli, $_POST['from_name']) : '';
	$from_email = isset($_POST['from_email']) ? mysqli_real_escape_string($mysqli, $_POST['from_email']) : '';
	$reply_to = isset($_POST['reply_to']) ? mysqli_real_escape_string($mysqli, $_POST['reply_to']) : '';
	
	if($password=='')
		$change_pass = false;
	else
	{
		$change_pass = true;
		$pass_encrypted = hash('sha512', $password.'PectGtma');		
	}
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	if(!get_app_info('is_sub_user'))
	{
		if($change_pass)
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", password="'.$pass_encrypted.'", s3_key="'.$aws_key.'", s3_secret="'.$aws_secret.'", paypal="'.$paypal.'", timezone = "'.$timezone.'", language = "'.$language.'" WHERE id = '.$userID;
		else
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", s3_key="'.$aws_key.'", s3_secret="'.$aws_secret.'", paypal="'.$paypal.'", timezone = "'.$timezone.'", language = "'.$language.'" WHERE id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    echo true; 
		}
	}
	else
	{
		if($change_pass)
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", password="'.$pass_encrypted.'", timezone = "'.$timezone.'", language = "'.$language.'" WHERE id = '.$userID;
		else
			$q = 'UPDATE login SET company="'.$company.'", name="'.$name.'", username="'.$email.'", timezone = "'.$timezone.'", language = "'.$language.'" WHERE id = '.$userID;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
		    //save sending app data
			$q = 'UPDATE apps SET from_name = "'.$from_name.'", from_email = "'.$from_email.'", reply_to = "'.$reply_to.'" WHERE id = '.get_app_info('restricted_to_app').' AND userID = '.get_app_info('main_userID');
			$r = mysqli_query($mysqli, $q);
			if ($r)
			{
			    echo true; 
			} 
		}
	}
?>