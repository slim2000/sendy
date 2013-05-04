<div class="well sidebar-nav">
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Campaigns');?></li>
        <li <?php if(currentPage()=='app.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/app?i='.$_GET['i'];?>"><i class="icon-home <?php if(currentPage()=='app.php'){echo 'icon-white';}?>"></i> <?php echo _('All campaigns');?></a></li>
        <li <?php if(currentPage()=='create.php' || currentPage()=='send-to.php' || currentPage()=='edit.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/create?i='.$_GET['i'];?>"><i class="icon-edit  <?php if(currentPage()=='create.php' || currentPage()=='send-to.php' || currentPage()=='edit.php'){echo 'icon-white';}?>"></i> <?php echo _('Create new campaign');?></a></li>
    </ul>
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Lists & subscribers');?></li>
        <li <?php if(currentPage()=='list.php' || currentPage()=='subscribers.php' || currentPage()=='new-list.php' || currentPage()=='update-list.php' || currentPage()=='delete-from-list.php' || currentPage()=='edit-list.php' || currentPage()=='custom-fields.php' || currentPage()=='autoresponders-list.php' || currentPage()=='autoresponders-create.php' || currentPage()=='autoresponders-emails.php' || currentPage()=='autoresponders-edit.php' || currentPage()=='autoresponders-report.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/list?i='.$_GET['i'];?>"><i class="icon-align-justify  <?php if(currentPage()=='list.php' || currentPage()=='subscribers.php' || currentPage()=='new-list.php' || currentPage()=='update-list.php' || currentPage()=='delete-from-list.php' || currentPage()=='edit-list.php' || currentPage()=='custom-fields.php' || currentPage()=='autoresponders-list.php' || currentPage()=='autoresponders-create.php' || currentPage()=='autoresponders-emails.php' || currentPage()=='autoresponders-edit.php' || currentPage()=='autoresponders-report.php'){echo 'icon-white';}?>"></i> <?php echo _('View all lists');?></a></li>
    </ul>
    <ul class="nav nav-list">
        <li class="nav-header"><?php echo _('Reports');?></li>
        <li <?php if(currentPage()=='report.php' || currentPage()=='reports.php'){echo 'class="active"';}?>><a href="<?php echo get_app_info('path').'/reports?i='.$_GET['i'];?>"><i class="icon-zoom-in  <?php if(currentPage()=='report.php' || currentPage()=='reports.php'){echo 'icon-white';}?>"></i> <?php echo _('See reports');?></a></li>
    </ul>
</div>