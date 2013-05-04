<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php 
	//------------------------------------------------------//
	//                      	INIT                       //
	//------------------------------------------------------//
	
	$edit = isset($_GET['edit']) ? $_GET['edit'] : '';
	$campaign_id = isset($_GET['c']) ? mysqli_real_escape_string($mysqli, $_GET['c']) : '';	
	$subject = addslashes(mysqli_real_escape_string($mysqli, $_POST['subject']));
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
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
	//make attachments directory if it don't exist
	if(!file_exists("../../uploads/attachments")) mkdir("../../uploads/attachments", 0777); 
	
	if($edit)
	{
		$q = 'UPDATE campaigns SET from_name="'.$from_name.'", from_email="'.$from_email.'", reply_to="'.$reply_to.'", title="'.$subject.'", plain_text="'.$plain.'", html_text="'.addslashes($html).'" WHERE id='.$campaign_id;
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			//Upload attachment(s)
			if($file[0]!='') //check if user uploaded any attachments
			{
				if(!file_exists("../../uploads/attachments/$campaign_id")) mkdir("../../uploads/attachments/$campaign_id", 0777);
				for($i=0;$i<count($file);$i++)
				{
					move_uploaded_file($file[$i], "../../uploads/attachments/$campaign_id/".$filename[$i]);
				}
			}
			
			if($w_clicked)
				header('Location: '.get_app_info('path').'/edit?i='.get_app_info('app').'&c='.$campaign_id);
			else
				header('Location: '.get_app_info('path').'/send-to?i='.get_app_info('app').'&c='.$campaign_id);
		}
	}
	else
	{
		//Insert into campaigns
		$q = 'INSERT INTO campaigns (userID, app, from_name, from_email, reply_to, title, plain_text, html_text, wysiwyg) VALUES ('.get_app_info('main_userID').', '.get_app_info('app').', "'.$from_name.'", "'.$from_email.'", "'.$reply_to.'", "'.$subject.'", "'.$plain.'", "'.addslashes($html).'", '.$wysiwyg.')';
		$r = mysqli_query($mysqli, $q);
		if ($r)
		{
			//get the campaign id from the new insert
		    $campaign_id = mysqli_insert_id($mysqli);
		    
		    //Upload attachment(s)
			if($file[0]!='') //check if user uploaded any attachments
			{
				if(!file_exists("../../uploads/attachments/$campaign_id")) mkdir("../../uploads/attachments/$campaign_id", 0777);
				for($i=0;$i<count($file);$i++)
				{
					move_uploaded_file($file[$i], "../../uploads/attachments/$campaign_id/".$filename[$i]);
				}
			}
		    
		    if($w_clicked)
				header('Location: '.get_app_info('path').'/edit?i='.get_app_info('app').'&c='.$campaign_id);
			else
				header('Location: '.get_app_info('path').'/send-to?i='.get_app_info('app').'&c='.$campaign_id);
		}
	}
?>