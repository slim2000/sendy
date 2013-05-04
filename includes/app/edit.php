<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$id = mysqli_real_escape_string($mysqli, $_POST['id']);
	$app_name = mysqli_real_escape_string($mysqli, $_POST['app_name']);
	$from_name = mysqli_real_escape_string($mysqli, $_POST['from_name']);
	$from_email = mysqli_real_escape_string($mysqli, $_POST['from_email']);
	$reply_to = mysqli_real_escape_string($mysqli, $_POST['reply_to']);
	$currency = mysqli_real_escape_string($mysqli, $_POST['currency']);
	$delivery_fee = mysqli_real_escape_string($mysqli, $_POST['delivery_fee']);
	$cost_per_recipient = mysqli_real_escape_string($mysqli, $_POST['cost_per_recipient']);
	$smtp_host = mysqli_real_escape_string($mysqli, $_POST['smtp_host']);
	$smtp_port = mysqli_real_escape_string($mysqli, $_POST['smtp_port']);
	$smtp_ssl = mysqli_real_escape_string($mysqli, $_POST['smtp_ssl']);
	$smtp_username = mysqli_real_escape_string($mysqli, $_POST['smtp_username']);
	$smtp_password = mysqli_real_escape_string($mysqli, $_POST['smtp_password']);
	$login_email = mysqli_real_escape_string($mysqli, $_POST['login_email']);
	$language = mysqli_real_escape_string($mysqli, $_POST['language']);
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	if($smtp_password=='')
		$q = 'UPDATE apps SET app_name = "'.$app_name.'", from_name = "'.$from_name.'", from_email = "'.$from_email.'", reply_to = "'.$reply_to.'", currency = "'.$currency.'", delivery_fee = "'.$delivery_fee.'", cost_per_recipient = "'.$cost_per_recipient.'", smtp_host = "'.$smtp_host.'", smtp_port = "'.$smtp_port.'", smtp_ssl = "'.$smtp_ssl.'", smtp_username = "'.$smtp_username.'" WHERE id = '.$id.' AND userID = '.get_app_info('userID');
	else
		$q = 'UPDATE apps SET app_name = "'.$app_name.'", from_name = "'.$from_name.'", from_email = "'.$from_email.'", reply_to = "'.$reply_to.'", currency = "'.$currency.'", delivery_fee = "'.$delivery_fee.'", cost_per_recipient = "'.$cost_per_recipient.'", smtp_host = "'.$smtp_host.'", smtp_port = "'.$smtp_port.'", smtp_ssl = "'.$smtp_ssl.'", smtp_username = "'.$smtp_username.'", smtp_password = "'.$smtp_password.'" WHERE id = '.$id.' AND userID = '.get_app_info('userID');
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		//update email in login
		$q2 = 'UPDATE login SET username = "'.$login_email.'", language = "'.$language.'" WHERE app = '.$id;
		$r2 = mysqli_query($mysqli, $q2);
		if ($r2)	header("Location: ".get_app_info('path'));
	}
?>