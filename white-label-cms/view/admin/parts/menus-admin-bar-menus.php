<div class="wlcms-input-group wlcms-toggle-wrapper wlcms-menus-settings">
    <div class="wlcms-help">
        <p><?php _e('Admin Bar Menus will be hidden to all User Roles up to and including Admins with the exception of White Label CMS Admins and Super Admins', 'white-label-cms') ?></p>
    </div>
    <?php

    $menu_class = wlcms()->Admin_Menus();
    $admin_bar_menu_setting = wlcms_field_setting('admin_bar_menus');
    
    if ($admin_bar_menus = $menu_class->get_admin_bar_menus()) {
      
      echo  $menu_class->get_admin_bar_menu_html($admin_bar_menus);
      
    }
 ?>
</div>