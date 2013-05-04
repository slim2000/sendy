<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/app/main.php');?>

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#settings-form").validate({
			rules: {
				app_name: {
					required: true	
				},
				from_name: {
					required: true	
				},
				from_email: {
					required: true,
					email: true
				},
				reply_to: {
					required: true,
					email: true
				}
			},
			messages: {
				app_name: "<?php echo addslashes(_('Please specify your brand\'s name'));?>",
				from_name: "<?php echo addslashes(_('\'From name\' is required'));?>",
				from_email: "<?php echo addslashes(_('A valid \'From email\' is required'));?>",
				reply_to: "<?php echo addslashes(_('A valid \'Reply to\' email is required'));?>"
			}
		});
	});
</script>

<form action="<?php echo get_app_info('path')?>/includes/app/edit.php" method="POST" accept-charset="utf-8" class="form-vertical" id="settings-form">

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
    <div class="span5">
    	<h2><?php echo _('Edit brand');?></h2><br/>
	    	
    	<label class="control-label" for="app_name"><?php echo _('Brand name');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="app_name" name="app_name" placeholder="<?php echo _('The name of the brand you\'re sending from');?>" value="<?php echo get_saved_data('app_name');?>">
            </div>
        </div>
        
        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_saved_data('from_name');?>">
            </div>
        </div>
        
        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_saved_data('from_email');?>">
            </div>
            <p id="verification-check-loader" style="display:none;"><img src="<?php echo get_app_info('path')?>/img/loader.gif" style="width:16px;"/> <?php echo _('Checking if your \'From email\' is verified in your SES console..');?><br/><br/></p>
            <div class="alert" id="unverified-email" style="display:none;"><strong><i class="icon icon-warning-sign"></i> <?php echo _('Unverified \'From email\'');?></strong>: <?php echo _('See Step 6 of our Get Started Guide on how to verify');?> <a href="http://sendy.co/get-started" target="_blank">http://sendy.co/get-started</a></div>
            <div class="alert alert-success" id="verified-email" style="display:none;"><strong><i class="icon icon-ok"></i> <?php echo _('Congrats! This \'From email\' is verified.');?></strong></div>
            <script type="text/javascript">
            	$(document).ready(function() {
            		$("#from_email").focusout(function(){
            			$("#verification-check-loader").show();
            			$("#unverified-email").hide();
            			$("#verified-email").hide();
            			
	            		$.post("<?php echo get_app_info('path')?>/includes/app/check-email-verification.php", { from_email: $("#from_email").val() },
            			  function(data) {
            			      if(data==false)
            			      {
            			      	$("#verification-check-loader").hide();
            			      	$("#unverified-email").show();
            			      	$("#verified-email").hide();
            			      }
            			      else if(data==true)
            			      {
            			      	$("#verification-check-loader").hide();
            			      	$("#unverified-email").hide();
            			      	$("#verified-email").show();
            			      }
            			      else
            			      {
	            			  	$("#verification-check-loader").hide();
            			      }
            			  }
            			);
            		});
            		<?php 
				        //Check if from email is verified in SES console
			  			if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
			  			{
				  			require_once('includes/helpers/ses.php');
					    	require_once('includes/helpers/EmailAddressValidator.php');
					    	$from_email = get_saved_data('from_email');
					    	//Get email's domain
							$from_email_domain_array = explode('@', $from_email);
							$from_email_domain = $from_email_domain_array[1];
							$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'));
							$v_addresses = $ses->ListIdentities();
							$verifiedEmailsArray = array();
							$verifiedDomainsArray = array();
							foreach($v_addresses['Addresses'] as $val){
								$validator = new EmailAddressValidator;
								if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
								else array_push($verifiedDomainsArray, $val);
							}
							if(!in_array($from_email, $verifiedEmailsArray) && !in_array($from_email_domain, $verifiedDomainsArray))
							{
								//From email address or domain is not verified in SES console
								echo '$("#unverified-email").show();';
							}
						}
			        ?>
			        $("#settings-form").submit(function(e){		
						var from_name = $('#from_name').val();
						if (from_name.indexOf(',') != -1) 
						{
							e.preventDefault(); 
							alert("<?php echo _('Please remove commas in your \'From name\'. Emails will fail to send with commas in the \'From name\'');?>");
						}
					});
            	});
            </script>
        </div>
        
        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_saved_data('reply_to');?>">
            </div>
        </div>
        
        <input type="hidden" name="id" value="<?php echo $_GET['i'];?>">
        
        <hr/>
        
        <h3><?php echo _('SMTP settings (only if you\'re not using Amazon SES)');?></h3><br/>
        
        <div class="well">
	        <?php echo _('If you prefer using other email service providers over Amazon SES for sending emails, set your SMTP settings here. Note that multi-threading is not supported, bounces and complaints will also not be registered if you use other email service providers to send emails.');?>
        </div>
        
        <label class="control-label" for="smtp_host"><?php echo _('Host');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="smtp_host" name="smtp_host" placeholder="eg. smtp.gmail.com" value="<?php echo get_saved_data('smtp_host');?>">
            </div>
        </div>
        
        <label class="control-label" for="smtp_port"><?php echo _('Port');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="smtp_port" name="smtp_port" placeholder="eg. 465" value="<?php echo get_saved_data('smtp_port');?>">
            </div>
        </div>
        
        <label class="control-label" for="smtp_ssl">SSL / TLS</label>
    	<div class="control-group">
	    	<div class="controls">
				<select name="smtp_ssl">
				  <option value="ssl" id="ssl">SSL</option>
				  <option value="tls" id="tls">TLS</option>
				 </select>
				 <script type="text/javascript">
					 $("#<?php echo get_saved_data('smtp_ssl');?>").attr("selected", "selected");
				 </script>
            </div>
        </div>
        
        <label class="control-label" for="smtp_username"><?php echo _('Username');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="text" class="input-xlarge" id="smtp_username" name="smtp_username" placeholder="<?php echo _('Username (usually your email)');?>" value="<?php echo get_saved_data('smtp_username');?>" autocomplete="off">
            </div>
        </div>
        
        <label class="control-label" for="smtp_password"><?php echo _('Password');?></label>
    	<div class="control-group">
	    	<div class="controls">
              <input type="password" class="input-xlarge" id="smtp_password" name="smtp_password" placeholder="<?php echo _('Leave blank to not change it');?>" value="" autocomplete="off">
            </div>
        </div>
        
        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
	    
    </div> 
    
    <div class="span5">
	    <h2>Brand settings</h2><br/>
	    
	    <div class="alert alert-info"><?php echo _('If this brand is one of your client, you can allow them to send newsletters on their own at a fee you preset below. Generate a new password below so that they can login with it.');?><br/><br/><?php echo _('Also, don\'t forget to set your PayPal account email address in');?> <a href="<?php echo get_app_info('path');?>/settings"><?php echo _('Settings');?></a>.</div><br/>
	    
	    <div class="well">
	    	<h3><?php echo _('Client login details');?></h3><br/>
	    	<p><strong><?php echo _('Login URL');?></strong>: <?php echo get_app_info('path');?></p>
		    <p><strong><?php echo _('Login email');?></strong>: <input type="text" name="login_email" id="login_email" value="<?php echo get_login_data('username');?>" style="margin-top: 5px;"/></p>
	    	<p><strong><?php echo _('Password');?></strong>: <span id="generate-password-wrapper"><a href="javascript:void(0)" style="text-decoration:underline;" id="generate-password"><?php echo _('Generate new password');?></a></span></p>
	    	<script type="text/javascript">
		    	$("#generate-password").click(function(){
		    		$("#form").submit();
		    		
		    		$.post('<?php echo get_app_info('path');?>/includes/app/generate-password.php', {app: <?php echo $_GET['i'];?>, brand_name: $("#app_name").val(), from_name: $("#from_name").val(), from_email: $("#from_email").val()},
		    		  function(data) {
		    		      if(data)
		    		      {
		    		      	$("#generate-password-wrapper").html(data);
		    		      }
		    		  }
		    		);
		    	});
	    	</script>
			<p><strong><strong><?php echo _('Language');?></strong>: </strong>
				<select id="language" name="language" style="margin-top:5px;">
				  <option value="<?php echo get_login_data('language');?>"><?php echo get_login_data('language');?></option>
				  <?php 
						if($handle = opendir('locale')) 
						{
							$i = -1;						
						    while (false !== ($file = readdir($handle))) 
						    {
						    	if($file!='.' && $file!='..' && substr($file, 0, 1)!='.')	
						    	{
						    		if(get_login_data('language')!=$file)
								    	echo '<option value="'.$file.'">'.$file.'</option>';
							    }
								
								$i++;
						    }
						    closedir($handle);
						}
				  ?>
				</select>
			</p>
	    </div>
    	
    	<br/>
	    	
    	<label class="control-label" for="currency"><?php echo _('Currency');?></label>
    	<div class="control-group">
	    	<div class="controls">
				<select name="currency">
				  <option value="USD" id="USD">U.S. Dollars</option>
				  <option value="CAD" id="CAD">Canadian Dollars</option>
				  <option value="EUR" id="EUR">Euros</option>
				  <option value="GBP" id="GBP">Pounds Sterling</option>
				  <option value="AUD" id="AUD">Australian Dollars</option>
				  <option value="JPY" id="JPY">Yen</option>
				  <option value="NZD" id="NZD">New Zealand Dollar</option>
				  <option value="CHF" id="CHF">Swiss Franc</option>
				  <option value="HKD" id="HKD">Hong Kong Dollar</option>
				  <option value="SGD" id="SGD">Singapore Dollar</option>
				  <option value="SEK" id="SEK">Swedish Krona</option>
				  <option value="DKK" id="DKK">Danish Krone</option>
				  <option value="PLN" id="PLN">Polish Zloty</option>
				  <option value="NOK" id="NOK">Norwegian Krone</option>
				  <option value="HUF" id="HUF">Hungarian Forint</option>
				  <option value="CZK" id="CZK">Czech Koruna</option>
				  <option value="ILS" id="ILS">Israeli Shekel</option>
				  <option value="MXN" id="MXN">Mexican Peso</option>
				  <option value="BRL" id="BRL">Brazilian Real</option>
				  <option value="MYR" id="MYR">Malaysian Ringgits</option>
				  <option value="PHP" id="PHP">Philippine Pesos</option>
				  <option value="TWD" id="TWD">Taiwan New Dollars</option>
				  <option value="THB" id="THB">Thai Baht</option>
				 </select>
				 <script type="text/javascript">
					 $("#<?php echo get_saved_data('currency');?>").attr("selected", "selected");
				 </script>
            </div>
        </div>
        
        <?php 
	        $currency_symbol = get_saved_data('currency');
	        if($currency_symbol=='USD' || $currency_symbol=='SGD' || $currency_symbol=='')
	        	$currency_symbol = '$';
        ?>
        
        <label class="control-label" for="delivery_fee"><?php echo _('Delivery Fee');?></label>
    	<div class="control-group">
	    	<div class="controls">
	    		<div class="input-prepend input-append">
	              <span class="add-on"><?php echo $currency_symbol;?></span><input type="text" class="input-xlarge" id="delivery_fee" name="delivery_fee" placeholder="Eg. 5" value="<?php echo get_saved_data('delivery_fee');?>" style="width: 80px;">
	            </div>
            </div>
        </div>
        
        <label class="control-label" for="cost_per_recipient"><?php echo _('Cost per recipient');?></label>
    	<div class="control-group">
	    	<div class="controls">
	    		<div class="input-prepend input-append">
	              <span class="add-on"><?php echo $currency_symbol;?></span><input type="text" class="input-xlarge" id="cost_per_recipient" name="cost_per_recipient" placeholder="Eg. .01" value="<?php echo get_saved_data('cost_per_recipient');?>" style="width: 80px;">
	            </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save');?></button>
        
    </div>  
</div>

</form>
<?php include('includes/footer.php');?>
