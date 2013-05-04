<?php
//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include('../functions.php');
include('../helpers/class.phpmailer.php');

$email = mysqli_real_escape_string($mysqli, $_POST['email']);
$new_pass = str_makerand(8, 8, true, false, true);
$pass_encrypted = hash('sha512', $new_pass.'PectGtma');

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

//------------------------------------------------------//
//                         EVENTS                       //
//------------------------------------------------------//

$q = 'SELECT id, name, company, s3_key, s3_secret FROM login WHERE username = "'.$email.'" LIMIT 1';
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0)
{
	while($row = mysqli_fetch_array($r))
    {
    	$uid = $row['id'];
		$company = stripslashes($row['company']);
		$name = stripslashes($row['name']);
		$aws_key = stripslashes($row['s3_key']);
		$aws_secret = stripslashes($row['s3_secret']);
    } 
    
    //Change user's password to the new one
    $q = 'UPDATE login SET password = "'.$pass_encrypted.'" WHERE id = '.$uid;
    $r = mysqli_query($mysqli, $q);
    if ($r)
    {
    	//send a message to let them know
    	$plain_text = $name.',
'._('Your password has been reset, here\'s your new one').':

'._('Password').': '.$new_pass.'

'._('Remember to change it immediately once you log back in.');

        $message = '
	    <p>'.$name.',</p>
	    <p>'._('Your password has been reset, here\'s your new one').':</p>
	    <p><strong>'._('Password').'</strong>: '.$new_pass.'</p>
	    <p>'._('Remember to change it immediately once you log back in.').'</p>
	    ';
	    
	    //send email to me
		$mail = new PHPMailer();
		if($aws_key!='' && $aws_secret!='')
		{
			$mail->IsAmazonSES();
			$mail->AddAmazonSESKey($aws_key, $aws_secret);
		}
		$mail->CharSet	  =	"UTF-8";
		$mail->From       = $email;
		$mail->FromName   = $company;
		$mail->Subject = '['.$company.'] '._('Your new password');
		$mail->AltBody = $plain_text;
		$mail->MsgHTML($message);
		$mail->AddAddress($email, $company);
		$mail->Send();
    }
    echo true;
}
else
{
	echo _('Email does not exist.');
}
?>