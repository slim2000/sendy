<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		echo '<script type="text/javascript">window.location="'.get_app_info('path').'/app?i='.get_app_info('restricted_to_app').'"</script>';
		exit;
	}
?>
<div class="row-fluid"> 
	<div class="span2">
		<h3><?php echo _('Amazon SES Quota');?></h3><br/>
		<div class="well">
			<?php
				if(get_app_info('s3_key')=='' && get_app_info('s3_secret')==''){}
				else
				{
					require_once('includes/helpers/ses.php');
					$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'));
					
					$quoteArray = array();
					
					foreach($ses->getSendQuota() as $quota){
						array_push($quoteArray, $quota);
					}
				}
			?>
			<?php if(get_app_info('s3_key')=='' && get_app_info('s3_secret')==''):?>
			<p><strong><?php echo _('Amazon SES is not set up as we can\'t find your AWS credentials in');?> <a href="<?php echo get_app_info('path');?>/settings" style="text-decoration: underline"><?php echo _('settings');?></a>.</strong></p>
			<p><strong><?php echo _('If you entered SMTP credentials when you create or edit a brand, emails will be sent via SMTP. Otherwise, emails will be sent via your server (not recommended).');?></strong></p>
			<p><a href="http://sendy.co/get-started" target="_blank"><?php echo _('View Get Started guide');?> &rarr;</a></p>
			<?php else:?>
			<p><strong><?php echo _('Max send in 24hrs');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[0]));?></span></p>
			<p><strong><?php echo _('Max send rate');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[1]));?> <?php echo _('per sec');?></span></p>
			<p><strong><?php echo _('Sent last 24hrs');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[2]));?></span></p>
			<p><strong><?php echo _('Sends left');?>:</strong> <span class="label"><?php echo number_format(round($quoteArray[0]-$quoteArray[2]));?></span></p>
			
			<?php if(number_format(round($quoteArray[0]))=='0' && number_format(round($quoteArray[1]))=='0' && number_format(round($quoteArray[2]))=='0' && get_app_info('s3_key')!='' && get_app_info('s3_key')!=''):?>
			<br/>
			<span style="color:#BB4D47;"><?php echo _('Verify that your AWS credentials are correct. If you\'re certain they\'re correct and are still seeing zeros in your quota, your server clock is out of sync. To fix this, Amazon requires you to <strong>sync your server clock with NTP</strong>. Request your host to do so if you\'re unsure.');?></span>
			<?php endif;?>
			
			<?php endif;?>
		</div>
	</div>
    <div class="span10">
    	<h2><?php echo _('Select a brand');?></h2><br/>
    	
    	<p><button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/new-brand'"><i class="icon-plus-sign"></i> <?php echo _('Add a new brand');?></button></p><br/>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Brands');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	<?php 
			  	$q = 'SELECT * FROM apps WHERE userID = '.get_app_info('userID');
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$title = $row['app_name'];
			  			$from_email = explode('@', $row['from_email']);
			  			$get_domain = $from_email[1];
			  			
			  			echo '
			  			<tr id="'.$id.'">
			  				<td><a href="'.get_app_info('path').'/app?i='.$id.'" title=""><img src="https://getfavicon.appspot.com/http://www.'.$get_domain.'?defaulticon='.get_app_info('path').'/img/default-favicon.png" style="margin:-3px 5px 0 0; width:16px; height: 16px;"/>'.$title.'</a></td>
			  				<td><a href="'.get_app_info('path').'/edit-brand?i='.$id.'" title=""><span class="icon icon-pencil"></span></a></td>
			  				<td><a href="#" title="'._('Delete').' '.$title.'" id="delete-btn-'.$id.'"><span class="icon icon-trash"></span></a></td>
			  				<script type="text/javascript">
					    	$("#delete-btn-'.$id.'").click(function(e){
							e.preventDefault(); 
							c = confirm("'._('All campaigns, lists, subscribers will be permanently deleted. Confirm delete').' '.$title.'?");
							if(c)
							{
								$.post("includes/app/delete.php", { id: '.$id.' },
								  function(data) {
								      if(data)
								      {
								      	$("#'.$id.'").fadeOut();
								      }
								      else
								      {
								      	alert("'._('Sorry, unable to delete. Please try again later!').'");
								      }
								  }
								);
							}
							});
						    </script>
			  			</tr>';
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  	<tr>
				  		<td><a href="'.get_app_info('path').'/new-brand" title="">'._('Add your first brand!').'</a></td>
				  		<td></td>
				  		<td></td>
				  	</tr>
				  	';
			  	}
		  	?>
		  </tbody>
		</table>
    </div>   
</div>
<?php include('includes/footer.php');?>
