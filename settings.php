<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/settings/main.php');?>
<?php include('includes/create/timezone.php');?>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/settings/main.js"></script>
<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#settings-form").validate({
			rules: {
				company: {
					required: true
				},
				personal_name: {
					required: true
				},
				email: {
					required: true,
					email: true
				}
			},
			messages: {
				company: "<?php echo addslashes(_('Your company name is required'));?>",
				personal_name: "<?php echo addslashes(_('Your name is required'));?>",
				email: "<?php echo addslashes(_('A valid email is required'));?>"
			}
		});
	});
</script>
<div class="row-fluid">
	<div class="span2">
		
		<?php if(get_app_info('is_sub_user')):?>
		
		<?php include('includes/sidebar.php');?>
		
		<?php else:?>
		
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
			<span style="color:#BB4D47;"><?php echo _('Unable to get your SES quota from Amazon. Verify that your AWS credentials are correct. If you\'re certain they\'re correct and are still seeing zeros in your quota, your server clock is out of sync. To fix this, Amazon requires you to <strong>sync your server clock with NTP</strong>. Request your host to do so if you\'re unsure.');?></span>
			<?php endif;?>
			
			<?php endif;?>
		</div>
		
		<?php endif;?>
		
	</div>
    <div class="span5">
    	<h2><?php echo _('Settings');?></h2><br/>
    	
    	<div class="alert alert-success" style="display:none;">
		  <button class="close" onclick="$('.alert-success').hide();">×</button>
		  <strong><?php echo _('Settings have been saved!');?></strong>
		</div>
		
		<div class="alert alert-error" style="display:none;">
		  <button class="close" onclick="$('.alert-error').hide();">×</button>
		  <strong><?php echo _('Sorry, unable to save. Please try again later!');?></strong>
		</div>
		
	    <form action="<?php echo get_app_info('path')?>/includes/settings/save.php" method="POST" accept-charset="utf-8" class="form-vertical" id="settings-form">
	    	
	    	<label class="control-label" for="company"><?php echo _('Company');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="company" name="company" placeholder="<?php echo _('Your company');?>" value="<?php echo get_user_data('company');?>">
	            </div>
	        </div>
	        
	    	<label class="control-label" for="personal_name"><?php echo _('Name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="personal_name" name="personal_name" placeholder="<?php echo _('Your name');?>" value="<?php echo get_user_data('name');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="email"><?php echo _('Email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="email" name="email" placeholder="<?php echo _('Your email address');?>" value="<?php echo get_user_data('username');?>" autocomplete="off">
	            </div>
	        </div>
	        
	        <label class="control-label" for="password"><?php echo _('Password (leave blank to not change it)');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="password" class="input-xlarge" id="password" name="password" placeholder="<?php echo _('Your password');?>" autocomplete="off">
	            </div>
	        </div>
	        
	        <label for="timezone"><?php echo _('Select your timezone');?></label>
    		<select id="timezone" name="timezone">
			  <option value="<?php echo get_user_data('timezone');?>"><?php echo get_user_data('timezone');?></option> 
			  <?php get_timezone_list();?>
			</select>
			
			<br/><br/>
			
			<label for="language"><?php echo _('Select your language');?></label>
    		<select id="language" name="language">
			  <option value="<?php echo get_user_data('language');?>"><?php echo get_user_data('language');?></option>
			  <?php 
					if($handle = opendir('locale')) 
					{
						$i = -1;						
					    while (false !== ($file = readdir($handle))) 
					    {
					    	if($file!='.' && $file!='..' && substr($file, 0, 1)!='.')	
					    	{
					    		if(get_user_data('language')!=$file)
							    	echo '<option value="'.$file.'">'.$file.'</option>';
						    }
							
							$i++;
					    }
					    closedir($handle);
					}
			  ?>
			</select>
	        
	        <br/><br/>
			<?php if(!get_app_info('is_sub_user')): //if not sub user ?>
			<h3><?php echo _('Amazon Web Services Credentials');?></h3><br/>
			
	        <label class="control-label" for="aws_key"><?php echo _('AWS Access Key ID');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="aws_key" name="aws_key" placeholder="<?php echo _('AWS Access Key ID');?>" value="<?php echo get_user_data('s3_key');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="aws_secret"><?php echo _('AWS Secret Access Key');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="aws_secret" name="aws_secret" placeholder="<?php echo _('AWS Secret Acesss Key');?>" value="<?php echo get_user_data('s3_secret');?>">
	            </div>
	        </div>
	        <br/>
	        <h3><?php echo _('PayPal account');?></h3><br/>
	        <p style="width: 280px;"><?php echo _('If you charge your client(s) a fee for sending newsletters, they\'ll pay to this PayPal account. Also, don\'t forget to turn <strong>Auto Return</strong> ON in your PayPal account under <strong>Profile > My sellings tools > Website preferences</strong> so that your client will be automatically re-directed to the sending script after payment. Just use your website\'s URL for the <strong>Return URL</strong> to be able to save.');?></p>
	        <label class="control-label" for="paypal"><?php echo _('PayPal email address');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="paypal" name="paypal" placeholder="<?php echo _('PayPal account email address');?>" value="<?php echo get_user_data('paypal');?>">
	            </div>
	        </div>
	        
	        <?php else:?>
	        <h3><?php echo _('Sending preferences');?></h3><br/>
	        
	        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_saved_data('from_name');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" disabled="disabled" id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_saved_data('from_email');?>">
	            </div>
	        </div>
	        
	        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_saved_data('reply_to');?>">
	            </div>
	        </div>
	        
	        <?php endif;?>
	        
	        <input type="hidden" name="uid" value="<?php echo get_app_info('userID');?>">
	        <input type="hidden" name="redirect" id="redirect" value="<?php echo get_app_info('path').'/settings';?>">
	        
	        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
	    </form>
    </div>   
    
    <!-- Check if sub user -->
	<?php if(!get_app_info('is_sub_user')):?>	 
    <div class="span5">
	    <h2><?php echo _('Your license key');?></h2><br/>
	    <div>
		    <p><?php echo _('You\'ll need this license key to');?> <a href="http://sendy.co/get-updated" target="_blank" style="text-decoration:underline"><?php echo _('download the latest version of Sendy on our website');?></a>.</p>
		    <div class="well"><strong><?php echo get_user_data('license');?></strong></div>
	    </div>
	    <h2><?php echo _('Your API key');?></h2><br/>
	    <div>
		    <div class="well"><strong><?php echo get_user_data('api_key');?></strong></div>
	    </div>
    </div>
    <?php endif;?>
</div>
<?php include('includes/footer.php');?>
