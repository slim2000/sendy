<?php include('includes/functions.php');?>
<?php if(isset($_COOKIE['logged_in'])){start_app();}?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="Shortcut Icon" type="image/ico" href="<?php echo get_app_info('path');?>/img/favicon.png">
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/bootstrap-responsive.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/responsive-tables.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/font-awesome.min.css" />
		<link rel="apple-touch-icon-precomposed" href="<?php echo get_app_info('path');?>/img/sendy-icon.png" />
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	    <!--[if lt IE 9]>
	      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	    <![endif]-->
		<link rel="stylesheet" type="text/css" href="<?php echo get_app_info('path');?>/css/all.css" />
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/jquery-migrate-1.1.0.min.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/jquery-ui-1.8.21.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/bootstrap.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/responsive-tables.js"></script>
		<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/main.js"></script>
		<title><?php echo get_app_info('company');?></title>
	</head>
	<body>
		<div class="navbar navbar-fixed-top">
	      <div class="navbar-inner">
	        <div class="container-fluid">
	          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </a>
	          	          
	          <!-- Check if sub user -->
	          <?php if(!get_app_info('is_sub_user')):?>
	          <a class="brand" href="<?php echo get_app_info('path');?>/"><img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim(get_app_info('email'))));?>?s=36&d=<?php echo get_app_info('path');?>/img/sendy-avatar.png" title="" class="main-gravatar"/><?php echo get_app_info('company');?></a>
	          <?php else:?>
	          <a class="brand" href="<?php echo get_app_info('path');?>/app?i=<?php echo get_app_info('restricted_to_app');?>"><img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim(get_app_info('email'))));?>?s=36&d=<?php echo get_app_info('path');?>/img/default-favicon-alt.png" title="" class="main-gravatar"/><?php echo get_app_info('company');?></a>
	          <?php endif;?>
	          
	          <?php if(currentPage()!='login.php' && currentPage()!='_install.php'): ?>
	          <div class="btn-group pull-right">
	            <a class="btn btn-inverse dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
	              <i class="icon-user icon-white"></i> <?php echo get_app_info('name');?>
	              <span class="caret"></span>
	            </a>
	            <ul class="dropdown-menu">
	              <li><a href="<?php echo get_app_info('path');?>/settings"><i class="icon icon-cog"></i> <?php echo _('Settings');?></a></li>
	              <li class="divider"></li>
	              <li><a href="<?php echo get_app_info('path');?>/logout"><i class="icon icon-off"></i> <?php echo _('Logout');?></a></li>
	            </ul>
	          </div>
	          
	          
	          <!-- Check if sub user -->
	          <?php if(!get_app_info('is_sub_user')):?>	          
	          <div class="btn-group pull-right">
				  <a class="btn btn-white dropdown-toggle" data-toggle="dropdown" href="#">
				    <?php 
				    	$get_i = isset($_GET['i']) ? mysqli_real_escape_string($mysqli, $_GET['i']) : '';
				    	
					    $q = 'SELECT app_name, from_email FROM apps WHERE id = '.$get_i;
					    $r = mysqli_query($mysqli, $q);
					    if ($r && mysqli_num_rows($r) > 0)
					    {
					        while($row = mysqli_fetch_array($r))
					        {
					        	$from_email = explode('@', $row['from_email']);
					  			$get_domain = $from_email[1];
					    		echo '<img src="https://getfavicon.appspot.com/http://www.'.$get_domain.'?defaulticon='.get_app_info('path').'/img/default-favicon.png" style="margin:-4px 5px 0 0; width:16px; height: 16px;"/>'.$row['app_name'];
					        }  
					    }
					    else
					    	echo '<span class="icon icon-th-list"></span> '._('Brands');
				    ?>
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu">
				  	<?php 
		              $q = 'SELECT * FROM apps WHERE userID = '.get_app_info('userID');
		              $r = mysqli_query($mysqli, $q);
		              if ($r && mysqli_num_rows($r) > 0)
		              {
		                  while($row = mysqli_fetch_array($r))
		                  {
		                  	$app_id = $row['id'];
		              		$app_name = $row['app_name'];
		              		$from_email = explode('@', $row['from_email']);
				  			$get_domain = $from_email[1];
		              		echo '<li';
		              		if($get_i==$app_id)
		              			echo ' class="active"';
		              		echo'><a href="'.get_app_info('path').'/app?i='.$app_id.'"><img src="https://getfavicon.appspot.com/http://www.'.$get_domain.'?defaulticon='.get_app_info('path').'/img/default-favicon.png" style="margin:-4px 5px 0 0; width:16px; height: 16px;"/>'.$app_name.'</a></li>';
		                  }  
		              }
		              else
		              {
			              echo '<li><a href="'.get_app_info('path').'/new-brand" title="">'._('Add a new brand').'</a></li>';
		              }
		            ?>
				  </ul>
				</div>
				<?php endif;?>
				
				
	          <div class="nav-collapse">
	            <ul class="nav">
	              
	            </ul>
	          </div><!--/.nav-collapse -->
	          
	          
	          
	          <?php endif;?>
	          
	        </div>
	      </div>
	    </div>
	    <div class="container-fluid">
	    <?php ini_set('display_errors', 0);?>