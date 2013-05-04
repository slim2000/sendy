<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$edit = isset($_GET['edit']) ? $_GET['edit'] : '';
	$ae = isset($_GET['ae']) ? mysqli_real_escape_string($mysqli, $_GET['ae']) : '';
	$ares_id = mysqli_real_escape_string($mysqli, $_GET['a']);
	$time_condition_number = mysqli_real_escape_string($mysqli, $_POST['time_condition_number']);
	$time_condition_intervals = mysqli_real_escape_string($mysqli, $_POST['time_condition_intervals']);
	$time_condition_beforeafter = mysqli_real_escape_string($mysqli, $_POST['time_condition_beforeafter']);
	$ares_type = $_POST['ares_type'];
	$subject = mysqli_real_escape_string($mysqli, $_POST['subject']);
	$from_name = mysqli_real_escape_string($mysqli, $_POST['from_name']);
	$from_email = mysqli_real_escape_string($mysqli, $_POST['from_email']);
	$reply_to = mysqli_real_escape_string($mysqli, $_POST['reply_to']);
	$plain = addslashes($_POST['plain']);
	$html = stripslashes($_POST['html']);
	$filename = $_FILES['attachments']['name'];	
	$file = $_FILES['attachments']['tmp_name'];	
	$wysiwyg = isset($_POST['wysiwyg']) ? mysqli_real_escape_string($mysqli, $_POST['wysiwyg']) : 0;
	$w_clicked = isset($_POST['w_clicked']) ? $_POST['w_clicked'] : null;
	$wysiwyg = $wysiwyg=='1' ? 1 : 0;
	$time_condition_sign = $time_condition_beforeafter=='before' ? '-' : '+';
	
	if($ares_type==1)
	{
		//drip
		if($time_condition_intervals == 'immediately')
			$time_condition = $time_condition_intervals;
		else
			$time_condition = $time_condition_sign.$time_condition_number.' '.$time_condition_intervals;
	}
	else
	{
		//others
		if($time_condition_beforeafter == 'on')
			$time_condition = '';
		else
			$time_condition = $time_condition_sign.$time_condition_number.' '.$time_condition_intervals;
	}
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//make attachments directory if it don't exist
	if(!file_exists("../../uploads/attachments")) mkdir("../../uploads/attachments", 0777); 
	
	if($edit)
	{
		$q = 'UPDATE ares_emails SET from_name="'.$from_name.'", from_email="'.$from_email.'", reply_to="'.$reply_to.'", title="'.$subject.'", plain_text="'.$plain.'", html_text="'.addslashes($html).'", time_condition="'.$time_condition.'" WHERE id='.$ae.' AND ares_id='.$ares_id;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			//Upload attachment(s)
			if($file[0]!='') //check if user uploaded any attachments
			{
				if(!file_exists("../../uploads/attachments/a$ae")) mkdir("../../uploads/attachments/a$ae", 0777);
				for($i=0;$i<count($file);$i++)
				{
					move_uploaded_file($file[$i], "../../uploads/attachments/a$ae/".$filename[$i]);
				}
			}
			
			if($w_clicked)
				header('Location: '.get_app_info('path').'/autoresponders-edit?i='.get_app_info('app').'&a='.$ares_id.'&ae='.$ae);
			else
				header('Location: '.get_app_info('path').'/autoresponders-emails?i='.get_app_info('app').'&a='.$ares_id);
		}
	}
	else
	{
		//Insert into campaigns
		$q = 'INSERT INTO ares_emails (ares_id, from_name, from_email, reply_to, title, plain_text, html_text, time_condition, created, wysiwyg) VALUES ('.$ares_id.', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$subject.'", "'.$plain.'", "'.addslashes($html).'", "'.$time_condition.'", "'.time().'", '.$wysiwyg.')';
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			//get the ares id from the new insert
		    $ae = mysqli_insert_id($mysqli);
		    
			//Upload attachment(s)
			if($file[0]!='') //check if user uploaded any attachments
			{
				if(!file_exists("../../uploads/attachments/a$ae")) mkdir("../../uploads/attachments/a$ae", 0777);
				for($i=0;$i<count($file);$i++)
				{
					move_uploaded_file($file[$i], "../../uploads/attachments/a$ae/".$filename[$i]);
				}
			}
			
			if($w_clicked)
				header('Location: '.get_app_info('path').'/autoresponders-edit?i='.get_app_info('app').'&a='.$ares_id.'&ae='.$ae);
			else
				header('Location: '.get_app_info('path').'/autoresponders-emails?i='.get_app_info('app').'&a='.$ares_id);
		}
	}
?>