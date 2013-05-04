<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/dashboard/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.get_app_info('path').'/app?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
	}
?>
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div>
	    	<p class="lead"><?php echo get_app_data('app_name');?></p>
    	</div>
    	<h2><?php echo _('Recent campaigns');?></h2><br/>
    	<p><button class="btn" onclick="window.location='<?php echo get_app_info('path');?>/create?i=<?php echo get_app_info('app');?>'"><i class="icon-plus-sign"></i> <?php echo _('Create & send new campaign');?></button></p>
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('Campaign title');?></th>
		      <th><?php echo _('Recipients');?></th>
		      <th><?php echo _('Sent');?></th>
		      <th><?php echo _('Unique Opens');?></th>
		      <th><?php echo _('Unique Clicks');?></th>
		      <th><?php echo _('Duplicate');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 
		  		$limit = 10;
				$total_subs = totals($_GET['i']);
				$total_pages = ceil($total_subs/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
				
			  	$q = 'SELECT * FROM campaigns WHERE userID = '.get_app_info('main_userID').' AND app='.get_app_info('app').' ORDER BY id DESC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = stripslashes($row['id']);
			  			$timezone = stripslashes($row['timezone']);
			  			if($timezone=='' || $timezone==0) date_default_timezone_set(get_app_info('timezone'));
			  			else date_default_timezone_set($timezone);
			  			$title = stripslashes(htmlentities($row['title'],ENT_QUOTES,"UTF-8"));
			  			$recipients = stripslashes($row['recipients']);
			  			$sent = stripslashes($row['sent']);
			  			$opens = stripslashes($row['opens']);
			  			$send_date = stripslashes($row['send_date']);
			  			$scheduled_lists = stripslashes($row['lists']);
			  			$to_send = stripslashes($row['to_send']);
			  			$to_send_lists = stripslashes($row['to_send_lists']);
			  			$from_email = stripslashes($row['from_email']);
			  			$error_stack = stripslashes($row['errors']);
			  			$error_stack_array = explode(',', $error_stack);
			  			$no_of_errors = count($error_stack_array);
			  			
			  			//check if campaign is completely sent
			  			if($sent!='')
			  			{
			  				//check if campaign sending is incomplete
			  				if($recipients>=$to_send)
			  				{
			  					$sent_to_all = true;
			  				}
			  				else
				  			{
					  			if($to_send==NULL)
					  				$sent_to_all = true;
					  			else
					  				$sent_to_all = false;
				  			}
			  			}
			  			else
			  			{
			  				$sent_to_all = false;
			  				
			  				//check if scheduled
				  			if($send_date=='')
				  			{
				  				$label = '<span class="label">'._('Draft').'</span>';
				  				$scheduled_title = _('Define recipients & send');
				  			}
				  			else
				  			{
				  				//get lists name
				  				$j = 1;
				  				$q2 = 'SELECT name FROM lists WHERE id in ('.$scheduled_lists.')';
				  				$r2 = mysqli_query($mysqli, $q2);
				  				if ($r2 && mysqli_num_rows($r2) > 0)
				  				{
				  					$scheduled_list_name = '';
				  				    while($row = mysqli_fetch_array($r2))
				  				    {
				  						$scheduled_list_name .= $row['name'];
				  						if($j < mysqli_num_rows($r2) && $j != mysqli_num_rows($r2)-1)
				  							$scheduled_list_name .= ', ';
				  						else if($j == mysqli_num_rows($r2)-1)
				  							$scheduled_list_name .= ' '._('and').' ';
				  						$j++;
				  				    }  
				  				}
				  				
				  				$send_date_totime = strftime("%a, %b %d, %Y %I:%M%p", $send_date);
				  				$label = '<span class="label label-info">'._('Scheduled').'</span>';
				  				$scheduled_title = _('Scheduled on').' '.$send_date_totime.' ('.$timezone.') '._('to').' ('.$scheduled_list_name.')';
				  			}
			  			}
			  			
			  			if($opens=='')
			  			{
			  				$percentage_opened = 0;
				  			$opens_unique = 0;
			  			}
			  			else
			  			{
				  			$opens_array = explode(',', $opens);
				  			$opens_unique = count(array_unique($opens_array));
				  			$percentage_opened = round($opens_unique/$recipients * 100, 2);
				  		}
				  		if($recipients==0 || $recipients=='') $percentage_clicked = round(get_click_percentage($id) *100, 2);
			  			else $percentage_clicked = round(get_click_percentage($id)/$recipients *100, 2);
			  			
			  			//tags for subject
						preg_match_all('/\[([a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+),\s*fallback=/i', $title, $matches_var, PREG_PATTERN_ORDER);
						preg_match_all('/,\s*fallback=([a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*)\]/i', $title, $matches_val, PREG_PATTERN_ORDER);
						preg_match_all('/(\[[a-zA-Z0-9!#%^&*()+=$@._-|\/?<>~`"\'\s]+,\s*fallback=[a-zA-Z0-9!,#%^&*()+=$@._-|\/?<>~`"\'\s]*\])/i', $title, $matches_all, PREG_PATTERN_ORDER);
						$matches_var = $matches_var[1];
						$matches_val = $matches_val[1];
						$matches_all = $matches_all[1];
						for($i=0;$i<count($matches_var);$i++)
						{		
							$field = $matches_var[$i];
							$fallback = $matches_val[$i];
							$tag = $matches_all[$i];
							//for each match, replace tag with fallback
							$title = str_replace($tag, $fallback, $title);
						}
						$title = str_replace('[Email]', $from_email, $title);
			  			
			  			if(!$sent_to_all)
			  			{
			  				if($sent!='')
				  			{
				  				//if sending incomplete
				  				if($recipients<$to_send)
				  				{
				  					//if CRON has executed the script / sending has started
				  					if($send_date!='0' && $timezone!='0')
				  					{
					  					echo '
					  						<tr id="'.$id.'">
										      <td id="label'.$id.'"><span class="label label-warning">'._('Sending').'</span> <a href="'.get_app_info('path').'/report?i='.get_app_info('app').'&c='.$id.'" title="'._('Currently sending your campaign to').' '.number_format($to_send).' '._('recipients').' ('._('excluding duplicates between lists').')">'.$title.'</a> ';
										      
										if(!get_app_info('cron_sending')) 
										echo '
									    <span id="separator'.$id.'">|</span> <span id="continue-sending-text"><a href="javascript:void(0)" id="continue-sending-btn-'.$id.'" title="'._('If you think sending has stopped before it was completely sent, click to continue sending to the rest of your recipients').'" data-url="'.get_app_info('path').'/includes/create/send-now.php" data-id="'.$id.'" data-email_list="'.$to_send_lists.'" data-app="'.get_app_info('app').'" data-offset="'.$recipients.'">'._('Resume').'</a></span>
									    ';
										      
										echo ' </td>
										      <td id="progress'.$id.'">'._('Checking..').'</td>
										      <td id="sent-status'.$id.'">'.parse_date($sent, 'long', true).'</td>
										      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
										      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
										      <td><a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a></td>
										      <td><a href="javascript:void(0)" title="Delete '.$title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
										      <script type="text/javascript">
										    	$("#delete-btn-'.$id.'").click(function(e){
												e.preventDefault(); 
												c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
												if(c)
												{
													$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
												
												$("#continue-sending-btn-'.$id.'").click(function(e){
													e.preventDefault();
													c = confirm("'._('Only continue if you think that sending has stopped. Resume sending?').'");
													if(c)
													{
														url = $(this).data("url");
														campaign_id = $(this).data("id");
														email_list = $(this).data("email_list");
														app = $(this).data("app");
														offset = $(this).data("offset");
														
														$(this).tooltip("hide");
														$("#continue-sending-text").html("'._('Ok').'");
														
														$.post(url, { campaign_id: campaign_id, email_list: email_list, app: app, offset: offset },
														  function(data) {													  	  
														      if(data)
														      {
														      	//
														      }
														  }
														);
													}
												});
												
												$(document).ready(function() {
								    			
								    				refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
									    			
									    			function get_sent_count(cid)
									    			{
									    				clearInterval(refresh_interval);
									    				
										    			$.post("includes/app/progress.php", { campaign_id: cid },
														  function(data) {
														      if(data)
														      {
														      	if(data.indexOf("%)") == -1)
														      	{													      		
														      		$("#label'.$id.' span.label").text("'._('Sent').'");
															    	$("#label'.$id.' span.label").removeClass("label-warning");
															    	$("#label'.$id.' span.label").addClass("label-success");
															    	$("#label'.$id.' a").tooltip("hide").attr("data-original-title", "'._('View report for this campaign').'").tooltip("fixTitle");
																    $("#sent-status'.$id.'").text("'.parse_date($sent, 'long', true).'");
																    $("#separator'.$id.'").hide();
																    $("#continue-sending-btn-'.$id.'").hide();
														      	}
														      	else refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
														      		
														      	$("#progress'.$id.'").html(data);
														      }
														      else
														      {
														      	$("#progress'.$id.'").html("'._('Error retrieving count').'");
														      }
														  }
														);
													}
													
									    		});
												</script>
										    </tr>
					  					';
					  				}
				  					
				  					//CRON have not executed the sending script
				  					else
				  					echo '
				  						<tr id="'.$id.'">
									      <td id="label'.$id.'"><span class="label label-warning">'._('Preparing').'</span> <a href="javascript:void(0)" title="'._('Preparing to send your campaign to').' '.number_format($to_send).' '._('recipients')._(' (excluding duplicates between lists), please wait.').'">'.$title.'</a></td>
									      <td id="progress'.$id.'">'._('Checking..').'</td>
									      <td id="sent-status'.$id.'">'._('Preparing to send').'..</td>
									      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
									      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
									      <td><a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a></td>
									      <td><a href="javascript:void(0)" title="Delete '.$title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
									      <script type="text/javascript">
									    	$("#delete-btn-'.$id.'").click(function(e){
											e.preventDefault(); 
											c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
											if(c)
											{
												$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
											
											$(document).ready(function() {
							    			
							    				refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
								    			
								    			function get_sent_count(cid)
								    			{
								    				clearInterval(refresh_interval);
								    				
									    			$.post("includes/app/progress.php", { campaign_id: cid },
													  function(data) {
													      if(data)
													      {
													      	if(data.indexOf("%)") != -1)
													      		refresh_interval = setInterval(function(){get_sent_count('.$id.')}, 2000);
													      	
													      	$("#progress'.$id.'").html(data);
													      	
													      	if(data != "0 <span style=\"color:#488846;\">(0%)</span> <img src=\"'.get_app_info('path').'/img/loader.gif\" style=\"width:16px;\"/>")
														    {
														    	window.location = "'.get_app_info('path').'/app?i='.get_app_info('app').'";
														    }
													      }
													      else
													      {
													      	$("#progress'.$id.'").html("'._('Error retrieving count').'");
													      }
													  }
													);
												}
												
								    		});
											</script>
									    </tr>
				  					';
				  				}
				  			}
				  				
				  			else
				  			{
				  				echo '
					  				<tr id="'.$id.'">
								      <td>'.$label.' <a href="'.get_app_info('path').'/send-to?i='.get_app_info('app').'&c='.$id.'" title="'.$scheduled_title.'">'.$title.'</a> | <a href="'.get_app_info('path').'/edit?i='.get_app_info('app').'&c='.$id.'" title="'._('Edit this campaign').'"> '._('Edit').'</a></td>
								      <td>-</td>
								      <td>-</td>
								      <td>-</td>
								      <td>-</td>
								      <td><a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a></td>
								      <td><a href="javascript:void(0)" title="Delete '.$title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
								      <script type="text/javascript">
								    	$("#delete-btn-'.$id.'").click(function(e){
										e.preventDefault(); 
										c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
										if(c)
										{
											$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
								    </tr>
					  			';
					  		}
			  			}
			  			else
			  			{
			  				if($error_stack != '')
				  				$download_errors = ' | <a href="'.get_app_info('path').'/includes/app/download-errors-csv.php?c='.$id.'" title="'._('Download CSV of emails that were not delivered to even after retrying').'">'.$no_of_errors.' '._('not delivered').'</a>';
				  			else
				  				$download_errors = '';
			  				
				  			echo '
				  				<tr id="'.$id.'">
							      <td><span class="label label-success">'._('Sent').'</span></a> <a href="'.get_app_info('path').'/report?i='.get_app_info('app').'&c='.$id.'" title="'._('View report for this campaign').'">'.$title.'</a>'.$download_errors.'</td>
							      <td>'.number_format($recipients).'</td>
							      <td>'.parse_date($sent, 'long', true).'</td>
							      <td><span class="label">'.$percentage_opened.'%</span> '.number_format($opens_unique).' '._('opened').'</td>
							      <td><span class="label">'.$percentage_clicked.'%</span> '.number_format(get_click_percentage($id)).' '._('clicked').'</td>
							      <td><a href="#duplicate-modal" title="" id="duplicate-btn-'.$id.'" data-toggle="modal" data-cid="'.$id.'" class="duplicate-btn"><i class="icon icon-copy"></i></a></td>
							      <td><a href="javascript:void(0)" title="Delete '.$title.'?" id="delete-btn-'.$id.'" class="delete-campaign"><i class="icon icon-trash"></i></a></td>
							      <script type="text/javascript">
							    	$("#delete-btn-'.$id.'").click(function(e){
									e.preventDefault(); 
									c = confirm(\''._('Confirm delete').' '.addslashes($title).'?\');
									if(c)
									{
										$.post("includes/campaigns/delete.php", { campaign_id: '.$id.' },
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
							    </tr>
				  			';
				  			$download_errors = '';
				  		}
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  		<tr>
					      <td>'._('There are no campaigns yet').'. <a href="'.get_app_info('path').'/create?i='.get_app_info('app').'" title="">'._('Send one').'</a>!</td>
					      <td></td>
					      <td></td>
					      <td></td>
					      <td></td>
					      <td></td>
					      <td></td>
					    </tr>
				  	';
			  	}
		  	?>
		    
		  </tbody>
		</table>
		
		<?php pagination($limit); ?>
			
		<div id="duplicate-modal" class="modal hide fade">
		    <div class="modal-header">
		      <button type="button" class="close" data-dismiss="modal">&times;</button>
		      <h3><?php echo _('Duplicate on which brand?');?></h3>
		    </div>
		    <div class="modal-body">
		    	<form action="<?php echo get_app_info('path')?>/includes/app/duplicate.php" method="POST" accept-charset="utf-8" class="form-vertical" name="duplicate-form" id="duplicate-form">
		    	<div class="control-group">
		            <label class="control-label" for="on-brand"><?php echo _('Choose a brand you\'d like to duplicate this campaign on');?>:</label><br/>
		            <div class="controls">
		              <select id="on-brand" name="on-brand">
		              	<?php 
		              		echo '<option value="'.get_app_info('app').'" id="brand-'.get_app_info('app').'">'.get_app_data('app_name').'</option>';
		              	
			              	$q = 'SELECT id, app_name FROM apps WHERE userID = '.get_app_info('main_userID');
			              	$r = mysqli_query($mysqli, $q);
			              	if ($r && mysqli_num_rows($r) > 0)
			              	{
			              	    while($row = mysqli_fetch_array($r))
			              	    {
			              	    	$app_id = $row['id'];
			              			$app_name = $row['app_name'];
			              			
			              			//sub users can only duplicate a campaign in their own brand
			              			if(get_app_info('is_sub_user')!=true)
			              			{
				              			if($app_id != get_app_info('app'))
					              			echo '<option value="'.$app_id.'" id="brand-'.$app_id.'">'.$app_name.'</option>';
				              		}
			              	    }  
			              	}
		              	?>
		              </select>
		              <input type="hidden" name="campaign_id" id="campaign_id" value=""></input>
		            </div>
		          </div>
		          </form>
		    </div>
		    <div class="modal-footer">
		      <a href="#" class="btn btn" data-dismiss="modal"><?php echo _('Cancel');?></a>
		      <a href="javascript:void(0)" class="btn btn-inverse" id="duplicate-btn"><?php echo _('Duplicate');?></a>
		    </div>
	    
		    <script type="text/javascript">
			    $(".duplicate-btn").click(function(){
				    cid = $(this).data("cid");
				    $("#campaign_id").val(cid);
			    });
			    $("#duplicate-btn").click(function(){
				    $("#duplicate-form").submit();
			    });
		    </script>
		  </div>
		
    </div>   
</div>
<?php include('includes/footer.php');?>
