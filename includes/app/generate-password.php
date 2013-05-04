<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      VARIABLES                       //
	//------------------------------------------------------//
	
	$app = mysqli_real_escape_string($mysqli, $_POST['app']);
	$company = mysqli_real_escape_string($mysqli, $_POST['brand_name']);
	$name = mysqli_real_escape_string($mysqli, $_POST['from_name']);
	$username = mysqli_real_escape_string($mysqli, $_POST['from_email']);
	$password = str_makerand(8, 8, true, false, true);
	$pass_encrypted = hash('sha512', $password.'PectGtma');
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	$q = 'SELECT id FROM login WHERE app = '.$app;
	$r = mysqli_query($mysqli, $q);
	if ($r && mysqli_num_rows($r) > 0)
	{
		//update password
	    $q = 'UPDATE login SET password = "'.$pass_encrypted.'" WHERE app = '.$app;
	    $r = mysqli_query($mysqli, $q);
	    if ($r) echo $password;
	}
	else
	{
		//insert new record
		$q = 'INSERT INTO login (name, company, username, password, tied_to, app) VALUES ("'.$name.'", "'.$company.'", "'.$username.'", "'.$pass_encrypted.'", '.get_app_info('userID').', '.$app.')';
		$r = mysqli_query($mysqli, $q);
		if ($r)  echo $password;
	}
	
	//random string method
	function str_makerand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
	{
		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
			for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
			return $key;
	}
?>