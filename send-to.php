<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/create/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php include('includes/create/timezone.php');?>

<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/create/main.php"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/datepicker.js"></script>
<link rel="stylesheet" type="text/css" href="css/datepicker.css" />
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span3">
    	<div>
	    	<p class="lead"><?php echo get_app_data('app_name');?></p>
    	</div>
    	
    	<div class="alert alert-success" id="test-send" style="display:none;">
		  <button class="close" onclick="$('.alert-success').hide();">×</button>
		  <strong><?php echo _('Email has been sent!');?></strong>
		</div>
		
		<div class="alert alert-error" id="test-send-error" style="display:none;">
		  <button class="close" onclick="$('.alert-error').hide();">×</button>
		  <strong><?php echo _('Sorry, unable to send. Please try again later!');?></strong>
		</div>
		
		<?php
	    	//check if cron is set up
	    	$q = 'SELECT cron FROM login WHERE id = '.get_app_info('main_userID');
	    	$r = mysqli_query($mysqli, $q);
	    	if ($r)
	    	{
	    	    while($row = mysqli_fetch_array($r))
	    	    {
	    			$cron = $row['cron'];
	    	    }  
	    	}
	    	
	    	$timezone = get_app_info('timezone');
	    	
	    	//get scheduled settings
		    $q = 'SELECT send_date, timezone, from_email, bounce_setup, complaint_setup FROM campaigns WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['c']);
  			$r = mysqli_query($mysqli, $q);
  			if ($r)
  			{
  			    while($row = mysqli_fetch_array($r))
  			    {
  					$send_date = $row['send_date'];
  					if($row['timezone']!='')
						$timezone = $row['timezone'];
					$from_email = $row['from_email'];
					$from_email_domain_array = explode('@', $from_email);
					$from_email_domain = $from_email_domain_array[1];
  					date_default_timezone_set($timezone);
		    		$day = strftime("%d", $send_date);
		    		$month = strftime("%m", $send_date);
		    		$year = strftime("%Y", $send_date);
		    		$hour = strftime("%l", $send_date);
		    		$minute = strftime("%M", $send_date);
		    		$ampm = strtolower(strftime("%p", $send_date));
		    		$the_date = $month.'-'.$day.'-'.$year;
		    		$bounce_setup = $row['bounce_setup'];
		    		$complaint_setup = $row['complaint_setup'];
  					
  					if($send_date=='')
  					{
	  					$send_newsletter_now = '';
	  					$send_newsletter_text = _('Schedule this campaign?');
	  					$schedule_form_style = 'style="display:none; width:260px;"';
  					}
  					else
  					{
	  					$send_newsletter_now = 'style="display:none;"';
	  					$send_newsletter_text = '&larr; '._('Back');
	  					$schedule_form_style = 'style="width:260px;"';
  					}
  			    }  
  			}
  			
  			//Check if from email is verified in SES console
  			if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
  			{
	  			require_once('includes/helpers/ses.php');
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
					echo '
						<div class="alert alert-danger">
							<p><strong>'._('Unverified \'From email\'').': '.$from_email.'</strong></p>
							<p>'._('Your \'from email\', (or its domain) is not verified in your SES console, your emails cannot be sent. Please see Step 6 of our Get Started Guide on how to verify').' <a href="http://sendy.co/get-started" target="_blank">http://sendy.co/get-started</a></p>
						</div>
						<script type="text/javascript">
							$(document).ready(function() {
								$("#real-btn").addClass("disabled");
								$("#test-send-btn").addClass("disabled");
								$("#schedule-btn").addClass("disabled");
								$("#real-btn").attr("disabled", "disabled");
								$("#test-send-btn").attr("disabled", "disabled");
								$("#schedule-btn").attr("disabled", "disabled");
								$("#email_list").attr("disabled", "disabled");
							});
						</script>
					';
				}
				else
				{
					//Set email feedback forwarding to false
					$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'));
					$ses->setIdentityFeedbackForwardingEnabled($from_email, 'false');
				}
			}			
	    ?>
    	
    	<h2><?php echo _('Test send this campaign');?></h2><br/>
	    <form action="<?php echo get_app_info('path')?>/includes/create/test-send.php" method="POST" accept-charset="utf-8" class="form-vertical" id="test-form">	    
	    	<label class="control-label" for="test_email"><?php echo _('Test email(s)');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="test_email" name="test_email" placeholder="<?php echo _('Email addresses, separated by comma');?>">
	            </div>
	        </div>
	        <input type="hidden" name="cid" value="<?php echo $_GET['c'];?>">
	        <button type="submit" class="btn" id="test-send-btn"><i class="icon icon-envelope-alt"></i> <?php echo _('Test send this newsletter');?></button>
	    </form>
	    
	    <br/>
	    <h2><?php echo _('Define recipients');?></h2><br/>
		    <?php if(get_app_info('is_sub_user')):?>
			    <?php if(paid()):?>
				<form action="<?php echo get_app_info('path')?>/includes/create/send-now.php" method="POST" accept-charset="utf-8" class="form-vertical" id="real-form">
			    <?php else:?>
				<form action="<?php echo get_app_info('path')?>/payment" method="POST" accept-charset="utf-8" class="form-vertical" id="pay-form">
			    <?php endif;?>	    
			<?php else:?>
				<form action="<?php echo get_app_info('path')?>/includes/create/send-now.php" method="POST" accept-charset="utf-8" class="form-vertical" id="real-form">
			<?php endif;?>
	    	<div class="control-group">
            <label class="control-label" for="multiSelect"><?php echo _('Select email list(s)');?></label>
            <div class="controls">
              <select multiple="multiple" id="email_list" name="email_list[]" style="height:200px">
              	<?php 
	              	$q = 'SELECT * FROM lists WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID').' ORDER BY name ASC';
	              	$r = mysqli_query($mysqli, $q);
	              	if ($r && mysqli_num_rows($r) > 0)
	              	{
	              	    while($row = mysqli_fetch_array($r))
	              	    {
	              			$list_id = stripslashes($row['id']);
	              			$list_name = stripslashes($row['name']);
	              			$list_selected = '';
	              			
	              			$q2 = 'SELECT lists FROM campaigns WHERE id = '.mysqli_real_escape_string($mysqli, $_GET['c']);
	              			$r2 = mysqli_query($mysqli, $q2);
	              			if ($r2)
	              			{
	              			    while($row = mysqli_fetch_array($r2))
	              			    {
	              					$lists = $row['lists'];
	              					$lists_array = explode(',', $lists);
	              					if(in_array($list_id, $lists_array))
	              						$list_selected = 'selected';
	              			    }  
	              			}
	              			
	              			echo '<option value="'.$list_id.'" data-quantity="'.get_list_quantity($list_id).'" id="'.$list_id.'" '.$list_selected.'>'.$list_name.'</option>';
	              	    }  
	              	}
	              	else
	              	{
		              	echo '<option value="" onclick="window.location=\''.get_app_info('path').'/new-list?i='.$_GET['i'].'\'">'._('No list found, click to add one.').'</option>';
	              	}
              	?>
              </select>
            </div>
          </div>
	        <input type="hidden" name="cid" value="<?php echo $_GET['c'];?>">
	        <input type="hidden" name="uid" value="<?php echo $_GET['i'];?>">
	        <input type="hidden" name="path" value="<?php echo get_app_info('path');?>">
	        <input type="hidden" name="grand_total_val" id="grand_total_val">
	        <input type="hidden" name="cron" value="<?php echo $cron;?>">
	        
	        <?php 
	        	//check SES quota and sends left
		    	require_once('includes/helpers/ses.php');
				$ses = new SimpleEmailService(get_app_info('s3_key'), get_app_info('s3_secret'));
				$quotaArray = array();
				foreach($ses->getSendQuota() as $quota){
					array_push($quotaArray, $quota);
				}
				$ses_quota = round($quotaArray[0]);
				$ses_send_rate = round($quotaArray[1]);
				$ses_sends_left = round($quotaArray[0]-$quotaArray[2]);
				if(get_app_info('s3_key')!='' && get_app_info('s3_secret')!='')
					$aws_keys_available = 'true';
				else
					$aws_keys_available = 'false';
					
				//update send_rate into database
				$q = 'UPDATE login SET send_rate = '.$ses_send_rate;
				$r = mysqli_query($mysqli, $q);
				if ($r){}
	    	?>
	        
	        <?php if(get_app_info('is_sub_user')):?>
	        	<input type="hidden" id="ses_sends_left" value="<?php echo $ses_sends_left;?>"/>
	        	<input type="hidden" id="aws_keys_available" value="<?php echo $aws_keys_available;?>"/>
		        <?php if(paid()):?>
		        	<strong><?php echo _('Recipients');?></strong>: <span id="recipients">0</span> <?php echo _('of');?> <?php echo $ses_sends_left;?><br/><br/>
			        <button type="submit" class="btn btn-inverse btn-large" id="real-btn" <?php echo $send_newsletter_now;?>><i class="icon-ok icon-white"></i> <?php echo _('Send newsletter now!');?></button>
			        <!-- success msg -->
			        <div id="view-report" class="alert alert-success" style="margin-top: 20px; display:none;">
			    		<p><h3><?php echo _('Your campaign is now sending!');?></h3></p>
			    		<p><?php echo _('You can safely close this window, your campaign will continue to send.');?></p>
			    		<p><?php echo _('You will be notified by email once your campaign has completed sending.');?></p>
			    	</div>
			        <!-- /success msg -->
			        <p style="margin-top:10px; text-decoration:underline;">
			        	<?php if($cron):?>
			        	<a href="javascript:void(0)" id="send-later-btn"><?php echo $send_newsletter_text;?></a>
			        	<?php endif;?>
			        </p>
		        <?php else:?>
			        <input type="hidden" name="paypal" value="<?php echo get_paypal();?>">
			        <div class="well" style="width:260px;">
				        <strong><?php echo _('Recipients');?></strong>: <span id="recipients">0</span> <?php echo _('of');?> <?php echo $ses_sends_left;?><br/>
				        <strong><?php echo _('Delivery Fee');?></strong>: <?php echo get_fee('currency');?> $<span id="delivery_fee"><?php echo get_fee('delivery_fee');?></span><br/>
				        <strong><?php echo _('Fee per recipient');?></strong>: <?php echo get_fee('currency');?> $<span id="recipient_fee"><?php echo get_fee('cost_per_recipient');?></span><br/><br/>
				        <span class="grand_total"><strong><?php echo _('Grand total');?></strong>: <?php echo get_fee('currency');?> $<span id="grand_total">0</span></span>
			        </div>
			        <button type="submit" class="btn btn-inverse btn-large" id="pay-btn" <?php echo $send_newsletter_now;?>><i class="icon-arrow-right icon-white"></i> <?php echo _('Proceed to pay for campaign');?></button>
			        <p style="margin-top:10px; text-decoration:underline;">
			        	<?php if($cron):?>
			        	<a href="javascript:void(0)" id="send-later-btn"><?php echo $send_newsletter_text;?></a>
			        	<?php endif;?>
			        </p>
		        <?php endif;?>
		    <?php else:?>
		    	<strong><?php echo _('Recipients');?></strong>: <span id="recipients">0</span><br/>
		    	
		    	<?php if($aws_keys_available=='true'):?>
		    	<strong><?php echo _('SES sends left');?></strong>: <span id="sends_left"><?php echo $ses_sends_left.' of '.$ses_quota;?></span><br/>
		    	
		    	<?php if($ses_sends_left==0 && $ses_quota==0):?>
		    	<br/><p class="alert alert-danger"><?php echo _('Unable to get your SES quota from Amazon. You won\'t be able to send emails as well. Verify that your AWS credentials are correct. If you\'re certain they\'re correct and are still seeing zeros in your quota, your server clock is out of sync. To fix this, Amazon requires you to <strong>sync your server clock with NTP</strong>. Request your host to do so if you\'re unsure.');?></p>
		    	
		    	<?php endif;?>
		    	
		    	<?php endif;?>
		    	<br/>
		    	
		    	<?php if($ses_quota==200):?>
		    	<div class="alert" id="no-production-access">
				  <?php echo _('It looks like you have not been granted production access by Amazon. You can only send to email addresses that you\'ve verified in your');?> <a href="https://console.aws.amazon.com/ses/home#verified-senders:email" target="_blank" style="text-decoration:underline"><?php echo _('Amazon SES console.');?></a> <?php echo _('If you try to send newsletters to emails NOT verified in your SES console, your recipient will not receive the newsletter.');?><br/><br/><a href="http://aws.amazon.com/ses/fullaccessrequest/" target="_blank" style="text-decoration:underline"><?php echo _('Request production access');?></a> <?php echo _('to lift this restriction.');?><br/>
				</div>
				<?php endif;?>
		    	
		    	<!-- success msg -->
		    	<div class="alert alert-error" id="over-limit" style="display:none;">
				  <?php echo _('You can\'t send more than your SES daily limit. Either wait till Amazon replenishes your daily limit in the next 24 hours, or');?> <a href="http://aws.amazon.com/ses/extendedaccessrequest" target="_blank" style="text-decoration:underline"><?php echo _('request for extended access');?></a>. 
				</div>
		    	<!-- /success msg -->
		    	
		    	<input type="hidden" id="ses_sends_left" value="<?php echo $ses_sends_left;?>"/>
		    	<input type="hidden" id="aws_keys_available" value="<?php echo $aws_keys_available;?>"/>
		    	<button type="submit" class="btn btn-inverse btn-large" id="real-btn" <?php echo $send_newsletter_now;?>><i class="icon-ok icon-white"></i> <?php echo _('Send newsletter now!');?></button>
		    	
		    	<div id="view-report" class="alert alert-success" style="margin-top: 20px; display:none;">
		    		<p><h3><?php echo _('Your campaign is now sending!');?></h3></p>
		    		<p><?php echo _('You can safely close this window, your campaign will continue to send.');?></p>
		    		<p><?php echo _('You will be notified by email once your campaign has completed sending.');?></p>
		    	</div>
		    	
		    	<?php if(!$cron):?>
		    	<br/><br/>
		    	<div class="alert alert-info">
			    	<p><strong><?php echo _('Note');?>:</strong> <?php echo _('We recommend');?> <a href="#cron-instructions" data-toggle="modal" style="text-decoration:underline"><?php echo _('setting up CRON');?></a> <?php echo _('to send your newsletters');?>. <?php echo _('Newsletters sent via CRON have the added ability to automatically resume sending when your server times out. You\'ll also be able to schedule emails.');?></p>
			    	<p><?php echo _('You haven\'t set up CRON yet, but that\'s okay. You can still send newsletters right now. But keep in mind that you won\'t be able to navigate around Sendy until sending is complete. Also, you\'ll need to manually resume sending (with a click of a button) if your server times out.');?></p>
			    	<p><a href="#cron-instructions" data-toggle="modal" style="text-decoration:underline"><?php echo _('Setup CRON now');?> &rarr;</a></p>
		    	</div>
		    	<?php endif;?>
		    	<p style="margin-top:10px; text-decoration:underline;">
		    		<?php if($cron):?>
		    		<a href="javascript:void(0)" id="send-later-btn"><?php echo $send_newsletter_text;?></a>
		    		<?php else:?>
		        	<a href="#cron-instructions" data-toggle="modal"><?php echo $send_newsletter_text;?></a>
		        	<?php endif;?>
		    	</p>
		    <?php endif;?>
	        
	    </form>
	    
	    <?php if(!$cron):
		    $server_path_array = explode('send-to.php', $_SERVER['SCRIPT_FILENAME']);
		    $server_path = $server_path_array[0];
	    ?>
	    <div id="cron-instructions" class="modal hide fade">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h3><i class="icon icon-time" style="margin-top: 5px;"></i> <?php echo _('Add a cron job');?></h3>
            </div>
            <div class="modal-body">
            <p><?php echo _('To schedule campaigns or to make sending more reliable, add a');?> <a href="http://en.wikipedia.org/wiki/Cron" target="_blank" style="text-decoration:underline"><?php echo _('cron job');?></a> <?php echo _('with the following command.');?></p>
            <h3><?php echo _('Command');?></h3>
            <pre>php <?php echo $server_path;?>scheduled.php > /dev/null 2>&amp;1</pre>
            <p><?php echo _('This command needs to be run every 5 minutes in order to check the database for any scheduled campaigns to send. You\'ll need to set your cron job with the following.');?><br/><em><?php echo _('(Note that adding cron jobs vary from hosts to hosts, most offer a UI to add a cron job easily. Check your hosting control panel or consult your host if unsure.)');?></em>.</p>
            <h3><?php echo _('Cron job');?></h3>
            <pre>*/5 * * * * php <?php echo $server_path;?>scheduled.php > /dev/null 2>&amp;1</pre>
            <p><?php echo _('Once added, wait around 5 minutes. If your cron job is functioning correctly, you\'ll see the scheduling options instead of this modal window when you click on "Schedule this campaign?".');?></p>
            </div>
            <div class="modal-footer">
              <a href="#" class="btn btn-inverse" data-dismiss="modal"><i class="icon icon-ok-sign"></i> <?php echo _('Okay');?></a>
            </div>
          </div>
        <?php endif;?>
	    
	    <div class="well" id="schedule-form-wrapper" <?php echo $schedule_form_style;?>>
	    	<?php if(get_app_info('is_sub_user')):?>
			    <?php if(paid()):?>
			    <form action="<?php echo get_app_info('path');?>/includes/create/send-later.php" method="POST" accept-charset="utf-8" id="schedule-form">
		    	<?php else:?>
			    <form action="<?php echo get_app_info('path');?>/payment" method="POST" accept-charset="utf-8" id="schedule-form">
			    <input type="hidden" name="pay-and-schedule" value="true"/>
			    <input type="hidden" name="paypal2" value="<?php echo get_paypal();?>">
			    <input type="hidden" name="grand_total_val2" id="grand_total_val2">
			    <?php endif;?>
			<?php else:?>
			<form action="<?php echo get_app_info('path');?>/includes/create/send-later.php" method="POST" accept-charset="utf-8" id="schedule-form">
		    <?php endif;?>
		    	<h3><i class="icon-ok icon-time" style="margin-top:5px;"></i> <?php echo _('Schedule this campaign');?></h3><br/>
	    		<input type="hidden" name="campaign_id" value="<?php echo $_GET['c'];?>"/>
	    		<input type="hidden" name="email_lists" id="email_lists"/>
	    		<input type="hidden" name="app" value="<?php echo $_GET['i'];?>"/>
	    		
	    		<label for="send_date"><?php echo _('Pick a date');?></label>
	    		<?php 
	    			if($send_date=='')
	    			{
		    			$tomorrow = time()+86400;
			    		$day = strftime("%d", $tomorrow);
			    		$month = strftime("%m", $tomorrow);
			    		$year = strftime("%Y", $tomorrow);
			    		$the_date = $month.'-'.$day.'-'.$year;
			    	}
	    		?>
	    		<div class="input-prepend date" id="datepicker" data-date="<?php echo $the_date;?>" data-date-format="mm-dd-yyyy">
	             <input type="text" name="send_date" value="<?php echo $the_date;?>" readonly><span class="add-on"><i class="icon-calendar" id="date-icon"></i></span>
	            </div>
	            <br/>
	            <label><?php echo _('Set a time');?></label>
	    		<select id="hour" name="hour" class="schedule-date">
	    		  <?php if($send_date!=''):?>
	    		  <option value="<?php echo $hour;?>"><?php echo $hour;?></option>
	    		  <?php endif;?>
				  <option>1</option> 
				  <option>2</option> 
				  <option>3</option> 
				  <option>4</option> 
				  <option>5</option> 
				  <option>6</option> 
				  <option>7</option> 
				  <option>8</option> 
				  <option>9</option> 
				  <option>10</option> 
				  <option>11</option> 
				  <option>12</option> 
				</select>
				<select id="min" name="min" class="schedule-date">
				  <?php if($send_date!=''):?>
				  <option value="<?php echo $minute;?>"><?php echo $minute;?></option>
				  <?php endif;?>
				  <option>00</option> 
				  <option>05</option> 
				  <option>10</option> 
				  <option>15</option> 
				  <option>20</option> 
				  <option>25</option> 
				  <option>30</option> 
				  <option>35</option> 
				  <option>40</option> 
				  <option>45</option> 
				  <option>50</option> 
				  <option>55</option> 
				</select>
				<select id="ampm" name="ampm" class="schedule-date">
				  <?php if($send_date!=''):?>
				  <option value="<?php echo $ampm;?>"><?php echo $ampm;?></option>
				  <?php endif;?>
				  <option>am</option> 
				  <option>pm</option> 
				</select>
				<br style="clear:both;"/>
				<br/>
	    		<label for="timezone"><?php echo _('Select a timezone');?></label>
	    		<select id="timezone" name="timezone">
				  <option value="<?php echo $timezone;?>"><?php echo $timezone;?></option> 
				  <?php get_timezone_list();?>
				</select>
				<br/><br/>
				<?php if(get_app_info('is_sub_user')):?>
			        <?php if(paid()):?>
					<button type="submit" class="btn btn-inverse btn-large" id="schedule-btn"><i class="icon-ok icon-time icon-white"></i> <?php echo _('Schedule campaign now');?></button>
					<?php else:?>
					<button type="submit" class="btn btn-inverse btn-large" id="schedule-btn"><i class="icon-arrow-right icon-white"></i> <?php echo _('Schedule and pay for campaign');?></button>
					<?php endif;?>
				<?php else:?>
			    	<button type="submit" class="btn btn-inverse btn-large" id="schedule-btn"><i class="icon-ok icon-time icon-white"></i> <?php echo _('Schedule campaign now');?></button>
				<?php endif;?>
	    	</form>
    	</div>
	    <div id="edit-newsletter"><a href="<?php echo get_app_info('path')?>/edit?i=<?php echo get_app_info('app')?>&c=<?php echo $_GET['c'];?>" title=""><i class="icon-pencil"></i> <?php echo _('Edit newsletter');?></a></div>
    </div>   
    
    <div class="span7">
    	<div>
	    	<h2><?php echo _('Newsletter preview');?></h2><br/>
	    	<blockquote><strong><?php echo _('From');?></strong> <span class="label"><?php echo get_saved_data('from_name');?> &lt;<?php echo get_saved_data('from_email');?>&gt;</span></blockquote>
	    	<blockquote><strong><?php echo _('Subject');?></strong> <span class="label"><?php echo get_saved_data('title');?></span></blockquote>
	    	<iframe src="<?php echo get_app_info('path');?>/w/<?php echo short($_GET['c']);?>?<?php echo time();?>" id="preview-iframe"></iframe>
    	</div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		var send_or_schedule = '';
		
		//schedule btn
		$("#schedule-btn").click(function(e){
			e.preventDefault(); 
			
			send_or_schedule = 'schedule';
			email_list = $('select#email_list').val();
			
			if(email_list == null)
			{
				$("#schedule-btn").effect("shake", { times:3 }, 60);
				$("#email_list").effect("shake", { times:3 }, 60);
			}
			else
			{					
				//Check if bounces and complaints notifications are setup
				<?php 
					if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!=''):
					
					//Get bounce/complaint setup statuses
					$q = 'SELECT bounce_setup, complaint_setup FROM apps WHERE from_email = "'.$from_email.'"';
					$r = mysqli_query($mysqli, $q);
					if (mysqli_num_rows($r) > 0)
					{
					    while($row = mysqli_fetch_array($r))
					    {
							$bounce_setup = $row['bounce_setup'];
							$complaint_setup = $row['complaint_setup'];
					    }  
					}
					
					//If bounces or complaints have not been setup, send an email to Amazon SES mailbox simulator to confirm this
					if($bounce_setup==0 || $complaint_setup=0):
				?>					
					//Show loading modal window
					$('#sns-loading').modal('show');
				
					$.post("<?php echo get_app_info('path')?>/includes/campaigns/check_sns.php", { from_email: "<?php echo $from_email;?>", aws_key: "<?php echo get_app_info('s3_key')?>", aws_secret: "<?php echo get_app_info('s3_secret')?>" },
					  function(data) {
					      if(data==true) 
					      {
					      	$('#please-wait-msg').html("<i class=\"icon icon-ok\"></i> <?php echo _('Congrats! Bounces & complaints have been set up');?>");
					      	setTimeout(function(){$("#schedule-form").submit()}, 2000);
					      }
					      else
					      {
					      	$('#sns-warning').modal('show');
					      	$('#sns-loading').modal('hide');
					      }
					  }
					);
					<?php else:?>
						
					 $("#schedule-form").submit();
					
					<?php endif;?>
					
				<?php else:?>
				
				 $("#schedule-form").submit();
				
				<?php endif;?>
			}
		});
		
		//send email for real
		$("#real-form").submit(function(e){
			e.preventDefault(); 
			
			send_or_schedule = 'send';
			
			if($("#email_list").val() == null)
			{
				$("#real-btn").effect("shake", { times:3 }, 60);
				$("#email_list").effect("shake", { times:3 }, 60);
			}
			else
			{
				c = confirm("<?php echo addslashes(_('Have you double checked your selected lists? If so, let\'s go ahead and send this!'));?>");
				
				if(c)
				{					
					//Check if bounces and complaints notifications are setup
					<?php 
						if(!get_app_info('is_sub_user') && get_app_info('s3_key')!='' && get_app_info('s3_secret')!=''):
						
						//Get bounce/complaint setup statuses
						$q = 'SELECT bounce_setup, complaint_setup FROM apps WHERE from_email = "'.$from_email.'"';
						$r = mysqli_query($mysqli, $q);
						if ($r)
						{
						    while($row = mysqli_fetch_array($r))
						    {
								$bounce_setup = $row['bounce_setup'];
								$complaint_setup = $row['complaint_setup'];
						    }  
						}
						
						//If bounces or complaints have not been setup, send an email to Amazon SES mailbox simulator to confirm this
						if($bounce_setup==0 || $complaint_setup=0):
					?>
						//Show loading modal window
						$('#sns-loading').modal('show');
						
						$.post("<?php echo get_app_info('path')?>/includes/campaigns/check_sns.php", { from_email: "<?php echo $from_email;?>", aws_key: "<?php echo get_app_info('s3_key')?>", aws_secret: "<?php echo get_app_info('s3_secret')?>" },
						  function(data) {
						      if(data==true) 
						      {
						      	$('#please-wait-msg').html("<i class=\"icon icon-ok\"></i> <?php echo _('Congrats! Bounces & complaints have been set up');?>");
						      	setTimeout(send_it, 2000);
						      }
						      else 
						      {
						      	$('#sns-warning').modal('show');
						      	$('#sns-loading').modal('hide');
						      }
						  }
						);
						<?php else:?>
						
						send_it();
						
						<?php endif;?>
						
					<?php else:?>
					
					send_it();
					
					<?php endif;?>
				}
			}
		});
		
		//send to PayPal
		$("#pay-form").submit(function(e){
			if($('select#email_list').val() == null)
			{
				e.preventDefault(); 
				$("#pay-btn").effect("shake", { times:3 }, 60);
			}
			else
			{
				c = confirm('<?php echo addslashes(_('Have you double checked your selected lists? If so, proceed to pay for this campaign.'));?>');
					
				if(!c)
					e.preventDefault(); 
			}
		});
		
		function send_it()
		{
			$('#sns-loading').modal('hide');
			
			var $form = $("#real-form"),
			campaign_id = $form.find('input[name="cid"]').val(),
			email_list = $form.find('select#email_list').val(),
			uid = $form.find('input[name="uid"]').val(),
			path = $form.find('input[name="path"]').val(),
			cron = $form.find('input[name="cron"]').val(),
			url = $form.attr('action');
			
			$("#real-btn").addClass("disabled");
			$("#real-btn").text("Your email is on the way!");
			$("#view-report").show();
			$("#edit-newsletter").hide();
				
			$.post(url, { campaign_id: campaign_id, email_list: email_list, app: uid, cron: cron },
			  function(data) {
			  	  
			  	  $("#test-send").css("display", "none");
			  	  $("#test-send-error").css("display", "none");
			  	  
			      if(data)
			      {
			      	if(data=='cron_send')
			      		window.location = path+"/app?i="+uid;
			      	else
			      		window.location = path+"/report?i="+uid+"&c="+campaign_id;
			      }
			  }
			);
		}
		
		$("#send-anyway").click(function(){
			if(send_or_schedule=='send') send_it();
			else $("#schedule-form").submit();
		});
	});
</script>

<div id="sns-loading" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><?php echo _('Checking bounces & complaints set up');?></h3>
</div>
<div class="modal-body">
    <div class="well" style="float:left;">
    	<img src="<?php echo get_app_info('path');?>/img/loader.gif" style="float:left; margin-right:5px; width: 16px;"/> 
    	<p style="float:right; width:450px;">
	    	<span id="please-wait-msg"><?php echo _('Please wait while we check if bounces & complaints have been set up. Checks are only done once per \'From email\'.');?></span>
	    </p>
    </div>
    <p style="float:left; clear:both;"><i><?php echo _('If this window does not disappear after 10 seconds, hit \'Esc\' and try again.');?></i></p>
</div>

</div>

<div id="sns-warning" class="modal hide fade">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h3><?php echo _('Important: Bounces or complaints were not set up');?></h3>
</div>
<div class="modal-body">
    <p class="alert alert-danger"><i class="icon icon-warning-sign"></i> <strong><?php echo _('We\'ve detected that bounces or complaints have not been setup.');?></strong></p> 
    <p><?php echo _('Not having bounces or complaints registered means future campaigns will continue to be sent to emails that bounced and recipients who have marked your emails as spam. This may lead to Amazon suspending your AWS account.');?></p>
    <div class="well">
    <p><strong><?php echo _('We highly recommend setting up bounces & complaints');?>:</strong></p>
    <p><?php echo _('Visit our Get Started Guide and complete steps 7 & 8');?> &rarr; <a href="http://sendy.co/get-started" target="_blank"><u>http://sendy.co/get-started</u></a>.</p></div>
    <p><?php echo _('If you\'re sure bounces & complaints have already been setup properly, just go ahead and send this.');?></p>
</div>
<div class="modal-footer">
  <a href="#" class="btn btn-inverse" data-dismiss="modal"><?php echo _('Don\'t send');?></a>
  <a href="#" class="btn" data-dismiss="modal" id="send-anyway"><?php echo _('Send anyway');?></a>
</div>
</div>
<?php include('includes/footer.php');?>
