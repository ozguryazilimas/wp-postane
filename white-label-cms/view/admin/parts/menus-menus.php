<div class="wlcms-input-group wlcms-toggle-wrapper wlcms-menus-settings">
    <div class="wlcms-help">
        <p><?php _e('Menus will be hidden to all User Roles up to and including Admins (with the exception of White Label CMS Admins and Super Admins).', 'white-label-cms') ?></p>
        <p><?php _e('Select which menus you want to appear.', 'white-label-cms') ?></p>
    </div>
    <input type="hidden" value="index.php" name="admin_menus[main][]">
    <input type="hidden" value="index.php_wlcms_index-php" name="admin_menus[sub][]">
    <?php

    $menu_class = wlcms()->Admin_Menus();
    $admin_menu = wlcms_field_setting('admin_menus');
    $sidebar_url = sanitize_title(wlcms()->Branding()->sidebar_menu_url());
    $setting_admin_menus = wlcms_field_setting('admin_menus');

    if( $admin_menus = $menu_class->get_admin_menus() ): 
        echo '<ul>';
        
        foreach ($admin_menus as $main_key => $main_menu):

            if( $main_key == $sidebar_url ) {
                continue;
            }
            $main_menu_checked = ' checked="checked"';
            $menu_name = $main_menu['name'];
            $menu_slug = $main_menu['slug'];
            if( is_array($setting_admin_menus) && isset($setting_admin_menus['main']) && in_array($menu_slug,  $setting_admin_menus['main'] )) {
                $main_menu_checked = '';
            }
            
            $disabled = '';
            if(  $main_key == 'index.php' ) {
                $disabled = ' disabled';
            }

            ?>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light main-toggle"<?php echo $disabled?> id="admin_menus_<?php echo $main_key?>" name="admin_menus[main][]" value="<?php echo $menu_slug ?>" type="checkbox"<?php echo $main_menu_checked?>/>
                    <label class="wlcms-toggle-btn<?php echo $disabled?>" for="admin_menus_<?php echo $main_key?>"></label><label class="toggle-label" for="admin_menus_<?php echo $main_key?>"><?php _e( $menu_name , 'white-label-cms') ?></label> 
                        <?php
                        if( isset($main_menu['submenus']) && count( $main_menu['submenus'] )):
                            echo '<a href="javascript:void(0)" class="wlcms-toggle-arrow"></a><ul class="sub_menu_wrapper">';
                            $submenus = $main_menu['submenus'];

                            foreach ($submenus as $submenu_key => $sub_menu):

                                $sub_menu_name = $sub_menu['name'];
                                $checked_sub = ' checked="checked"';
                                $submenu_value = $sub_menu['slug'];
                                $disabled = '';
                                if(  $submenu_key == 'index-php' ) {
                                    $disabled = ' disabled';
                                }elseif( $submenu_key == 'wlcms-plugin-php') {
                                    continue;
                                }

                                if( is_array($setting_admin_menus) && (isset($setting_admin_menus['sub']) && in_array($submenu_value,  $setting_admin_menus['sub'] ) )) {
                                    $checked_sub = '';
                                }
                                ?>
                                <li>
                                    <input class="wlcms-toggle wlcms-toggle-light sub-toggle"<?php echo $disabled?> id="admin_sub_menus_<?php echo $submenu_key?>" name="admin_menus[sub][]" value="<?php echo $submenu_value ?>" type="checkbox" <?php echo $checked_sub ?>/>
                                    <label class="wlcms-toggle-btn<?php echo $disabled?>" for="admin_sub_menus_<?php echo $submenu_key?>"></label><label class="toggle-label" for="admin_sub_menus_<?php echo $submenu_key?>"><?php _e( $sub_menu_name , 'white-label-cms') ?></label> 
                                </li>
                            <?php
                            endforeach;
                            echo '</ul>';
                        endif;
                        ?>
                </li>
        <?php 
        endforeach;
        echo '</ul>';
    endif;?>
</div>