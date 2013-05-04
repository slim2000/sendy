<?php 
	//----------------------------------------------------------------------------------//	
	//								COMPULSORY SETTINGS
	//----------------------------------------------------------------------------------//
	
	/*  Set the URL to your Sendy installation (without the trailing slash) */
	define('APP_PATH', 'http://your_sendy_installation_url');
	
	/*  MySQL database connection credentials  */
	$dbHost = ''; //MySQL Hostname
	$dbUser = ''; //MySQL Username
	$dbPass = ''; //MySQL Password
	$dbName = ''; //MySQL Database Name
	
	
	//----------------------------------------------------------------------------------//	
	//								  OPTIONAL SETTINGS
	//----------------------------------------------------------------------------------//	
	
	/* 
		Change the database character set to something that supports the language you'll
		be using. Example, set this to utf16 if you use Chinese or Vietnamese characters
	*/
	$charset = 'utf8';
	
	/*  Set this if you use a non standard MySQL port.  */
	$dbPort = 3306;	
	
	/*  Domain of cookie (99.99% chance you don't need to edit this at all)  */
	define('COOKIE_DOMAIN', '');
	
	//----------------------------------------------------------------------------------//
?>