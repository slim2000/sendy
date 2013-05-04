<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$app_name = mysqli_real_escape_string($mysqli, $_POST['app_name']);
	$from_name = mysqli_real_escape_string($mysqli, $_POST['from_name']);
	$from_email = mysqli_real_escape_string($mysqli, $_POST['from_email']);
	$reply_to = mysqli_real_escape_string($mysqli, $_POST['reply_to']);
	$currency = mysqli_real_escape_string($mysqli, $_POST['currency']);
	$delivery_fee = mysqli_real_escape_string($mysqli, $_POST['delivery_fee']);
	$cost_per_recipient = mysqli_real_escape_string($mysqli, $_POST['cost_per_recipient']);
	$password = mysqli_real_escape_string($mysqli, $_POST['pass']);
	$pass_encrypted = hash('sha512', $password.'PectGtma');
	$smtp_host = mysqli_real_escape_string($mysqli, $_POST['smtp_host']);
	$smtp_port = mysqli_real_escape_string($mysqli, $_POST['smtp_port']);
	$smtp_ssl = mysqli_real_escape_string($mysqli, $_POST['smtp_ssl']);
	$smtp_username = mysqli_real_escape_string($mysqli, $_POST['smtp_username']);
	$smtp_password = mysqli_real_escape_string($mysqli, $_POST['smtp_password']);
	$language = mysqli_real_escape_string($mysqli, $_POST['language']);
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	$q = 'INSERT INTO apps (userID, app_name, from_name, from_email, reply_to, currency, delivery_fee, cost_per_recipient, smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password) VALUES ('.get_app_info('userID').', "'.$app_name.'", "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$currency.'", "'.$delivery_fee.'", "'.$cost_per_recipient.'", "'.$smtp_host.'", "'.$smtp_port.'", "'.$smtp_ssl.'", "'.$smtp_username.'", "'.$smtp_password.'")';
	$r = mysqli_query($mysqli, $q);
	if ($r)
	{
		//insert new record
		$q = 'INSERT INTO login (name, company, username, password, tied_to, app, timezone, language) VALUES ("'.$from_name.'", "'.$app_name.'", "'.$from_email.'", "'.$pass_encrypted.'", '.get_app_info('userID').', '.mysqli_insert_id($mysqli).', "'.get_app_info('timezone').'", "'.$language.'")';
		$r = mysqli_query($mysqli, $q);
		if ($r)
			header("Location: ".get_app_info('path'));
	}
?>