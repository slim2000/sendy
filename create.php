<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/create/main.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.get_app_info('path').'/create?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
	}
?>

<script src="<?php echo get_app_info('path');?>/js/redactor/redactor.min.js"></script>
<link rel="stylesheet" href="<?php echo get_app_info('path');?>/js/redactor/redactor.css" />

<!-- Validation -->
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#edit-form").validate({
			rules: {
				subject: {
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
				},
				html: {
					required: true
				}
			},
			messages: {
				subject: "<?php echo addslashes(_('The subject of your email is required'));?>",
				from_name: "<?php echo addslashes(_('\'From name\' is required'));?>",
				from_email: "<?php echo addslashes(_('A valid \'From email\' is required'));?>",
				reply_to: "<?php echo addslashes(_('A valid \'Reply to\' email is required'));?>",
				html: "<?php echo addslashes(_('Your HTML code is required'));?>"
			}
		});
	});
</script>

<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div class="row-fluid">
	    	<div>
		    	<p class="lead"><?php echo get_app_data('app_name');?></p>
	    	</div>
	    	<h2><?php echo _('Create new campaign');?></h2><br/>
    	</div>
    	<div class="row-fluid">
    		<form action="<?php echo get_app_info('path')?>/includes/create/save-campaign.php?i=<?php echo get_app_info('app')?>" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form" enctype="multipart/form-data">
			    <div class="span3">
				    
			    	<label class="control-label" for="subject"><?php echo _('Subject');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="subject" name="subject" placeholder="<?php echo _('Subject of this email');?>">
			            </div>
			        </div>
			        
			        <label class="control-label" for="from_name"><?php echo _('From name');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="from_name" name="from_name" placeholder="<?php echo _('From name');?>" value="<?php echo get_app_data('from_name');?>">
			            </div>
			        </div>
			        
			        <label class="control-label" for="from_email"><?php echo _('From email');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" <?php if(get_app_info('is_sub_user')) echo 'readonly="readonly"';?> id="from_email" name="from_email" placeholder="<?php echo _('From email');?>" value="<?php echo get_app_data('from_email');?>">
			            </div>
			        </div>
			        
			        <label class="control-label" for="reply_to"><?php echo _('Reply to email');?></label>
			    	<div class="control-group">
				    	<div class="controls">
			              <input type="text" class="input-xlarge" id="reply_to" name="reply_to" placeholder="<?php echo _('Reply to email');?>" value="<?php echo get_app_data('reply_to');?>">
			            </div>
			        </div>
			        
			        <label class="control-label" for="plain"><?php echo _('Plain text');?></label>
		            <div class="control-group">
				    	<div class="controls">
			              <textarea class="input-xlarge" id="plain" name="plain" rows="10" placeholder="<?php echo _('Plain text of this email');?>"></textarea>
			            </div>
			        </div>
			        
			        <label class="control-label" for="attachments"><?php echo _('Attachments');?></label>
		            <div class="control-group">
				    	<div class="controls">
				    		<input type="file" id="attachments" name="attachments[]" multiple />
			            </div>
			        </div><br/>
			        
			        <button type="submit" class="btn btn-inverse"><i class="icon-ok icon-white"></i> <?php echo _('Save & next');?></button>
			        
			        <script type="text/javascript">
			        	$(document).ready(function() {
			        		$("#edit-form").submit(function(e){		
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
			    <div class="span9">
			    	<p>
				    	<label class="control-label" for="html"><?php echo _('HTML code');?></label>
				    	<div class="btn-group">
				    	<button class="btn" id="toggle-wysiwyg"><?php echo _('Save and switch to WYSIWYG editor');?></button> 
				    	<span class="wysiwyg-note"><?php echo _('WYSIWYG editor strips off html, head and body tags which may affect the look of your newsletter.');?></span>
						<script type="text/javascript">
							$("#toggle-wysiwyg").click(function(e){
								e.preventDefault();
								
								$('<input>').attr({
								    type: 'hidden',
								    id: 'wysiwyg',
								    name: 'wysiwyg',
								    value: '1',
								}).appendTo("#edit-form");
								
								$('<input>').attr({
								    type: 'hidden',
								    id: 'w_clicked',
								    name: 'w_clicked',
								    value: '1',
								}).appendTo("#edit-form");
								
								$("#subject").rules("remove");
								$("#html").rules("remove");
								if($("#subject").val()=="") $("#subject").val("Untitled");
								
								$("#edit-form").submit();
							});
						</script>
						</div>
						<br/>
			            <div class="control-group">
					    	<div class="controls">
				              <textarea class="input-xlarge" id="html" name="html" rows="10" placeholder="HTML code of this email"></textarea>
				            </div>
				        </div>
				    	<p><?php echo _('Use the following tags in your subject, plain text or HTML code and they\'ll automatically be formatted when your campaign is sent. For web version and unsubscribe tags, you can style them with inline CSS.');?></p><br/>
				    	<div class="row-fluid">
					    	<div class="span6">
						    	<h3><?php echo _('Essential tags (HTML)');?></h3><br/>
						    	<p><strong><?php echo _('Webversion link');?>: </strong><br/><code>&lt;webversion&gt;<?php echo _('View web version');?>&lt;/webversion&gt;</code></p>
						    	<p><strong><?php echo _('Unsubscribe link');?>: </strong><br/><code>&lt;unsubscribe&gt;<?php echo _('Unsubscribe here');?>&lt;/unsubscribe&gt;</code></p>
						    	<br/>
						    	<h3><?php echo _('Essential tags (Plain text)');?></h3><br/>
						    	<p><strong><?php echo _('Webversion link');?>: </strong><br/><code>[webversion]</code></p>
						    	<p><strong><?php echo _('Unsubscribe link');?>: </strong><br/><code>[unsubscribe]</code></p>
						    	<br/>
					    	</div>
					    	<div class="span6">
						    	<h3><?php echo _('Personalization tags (HTML & plain text)');?></h3><br/>
						    	<p><strong><?php echo _('Name');?>: </strong><br/><code>[Name,fallback=]</code></p>
						    	<p><strong><?php echo _('Email');?>: </strong><br/><code>[Email]</code></p>
						    	<br/>
						    	<h3><?php echo _('Custom field tags');?></h3><br/>
						    	<p><?php echo _('You can also use custom fields to personalize your newsletter, eg.');?> <code>[Country,fallback=anywhere in the world]</code>.</p>
						    	<p><?php echo _('To manage or get a reference of tags from custom fields, go to any subscriber list. Then click \'Custom fields\' button at the top right.');?></p>
					    	</div>
				    	</div>
			    	</p>
			    </div> 
			 </form>
		</div>
	</div>
</div>
<?php include('includes/footer.php');?>
