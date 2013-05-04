<hr>
	
	      <footer>
	      	<!-- Check if sub user -->
			<?php if(!get_app_info('is_sub_user')):?>
	        <p>
	        	&copy; <?php echo date("Y",time())?> <a href="http://sendy.co" title="" target="_blank">Sendy</a> | <a href="http://sendy.co/forum/" target="_blank">Support forum</a> | <?php echo get_app_info('version');?> 
	        	<?php 
		        	if(get_app_info('version_latest') > get_app_info('version')):
	        	?>
		        <a href="http://sendy.co/get-updated?l=<?php echo get_app_info('license');?>" target="_blank" style="text-decoration:none;"><span class="label label-info">new version: <?php echo get_app_info('version_latest');?> available</span></a>
		        <?php endif;?>
	        </p>
	        <?php else:?>
	        <p>&copy; <?php echo date("Y",time())?> <?php echo get_app_info('company');?></p>
	        <?php endif;?>
	      </footer>
	    </div>
	</body>
</html>