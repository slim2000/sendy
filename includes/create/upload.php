<?php 
	include('../functions.php');
	include('../login/auth.php');
	
	//Init
	$file = $_FILES['file']['tmp_name'];
	$file_name = $_FILES['file']['name'];
	$extension_explode = explode('.', $file_name);
	$extension = $extension_explode[count($extension_explode)-1];
	$time = time();
	chmod("../../uploads",0777);
	
	//Check filetype
	$allowed = array("jpeg", "jpg", "gif", "png");
	if(in_array($extension, $allowed)) //if file is an image, allow upload
	{
		//Upload file
		move_uploaded_file($file, '../../uploads/'.$time.'.'.$extension);
		
		//return result
		$array = array(
			'filelink' => APP_PATH.'/uploads/'.$time.'.'.$extension
		);
		echo stripslashes(json_encode($array)); 
	}
	else exit;
?>