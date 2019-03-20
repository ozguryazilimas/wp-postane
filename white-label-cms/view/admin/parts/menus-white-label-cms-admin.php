<?php
    $current_user = wp_get_current_user();
?>
<div class="wlcms-input-group toggle-group wlcms_admin_wrapper">
    <div class="wlcms-input">
        <label class="toggle-label" for="enable_wlcms_admin"><?php _e('Want to hide menus for your client?', 'white-label-cms') ?></label>
        <input class="wlcms-toggle wlcms-toggle-light main-toggle" data-revised="1" id="enable_wlcms_admin" name="enable_wlcms_admin" value="1" type="checkbox" <?php checked(wlcms_field_setting('enable_wlcms_admin'), 1, true) ?>/>
        <label class="wlcms-toggle-btn" for="enable_wlcms_admin"></label>
    </div>
    <div class="sub-fields">
        <p><?php _e('You are now a White Label CMS Admin. This allows you to modify the menus that other people will see.', 'white-label-cms') ?></p>
        <p><?php echo sprintf('<strong>%s: %s</strong>', __('You', 'white-label-cms'), $current_user->user_email); ?></p>
        
            <input name="wlcms_admin[]" value="<?php echo  $current_user->ID ?>" type="hidden"/>
            <?php
                $admin_args = array(
                    'role' => 'administrator',
                    'exclude' => $current_user->ID,
                );
                $adminusers = get_users( $admin_args );
                $wlcms_admin_setting = wlcms_field_setting('wlcms_admin');
                
            if( count( $adminusers ) ):
                echo sprintf('<label>%s</label>', __('Other Admins:', 'white-label-cms'));
                echo '<div class="wlcms-other-admins">';
                foreach ( $adminusers as $user ) { ?>
                    <div class="wlcms-input">
                    <input class="wlcms-toggle wlcms-toggle-light wlcms_admin_users" id="wlcms_admin_<?php echo  $user->ID?>" name="wlcms_admin[]" value="<?php echo  $user->ID ?>" type="checkbox" <?php
                    if( $wlcms_admin_setting && in_array( $user->ID, $wlcms_admin_setting ) ):
                        ?>checked="checked"<?php
                    endif;?>/>
                    <label class="wlcms-toggle-btn" for="wlcms_admin_<?php echo  $user->ID?>"></label>&nbsp;&nbsp;<label class="toggle-label" for="wlcms_admin_<?php echo  $user->ID?>"><?php echo  $user->user_email ?></label> 
                    </div>
            <?php
                }
                echo '</div>';
            endif;
            ?>
        <div class="wlcms-help">
            <p><?php _e('By selecting a White Label CMS Admin it means that only the selected Admins will be able to modify the settings for White Label CMS, as it won\'t be visible in the menu to anybody else. <a href="https://www.videousermanuals.com/white-label-cms/" target="_blank">Learn more about this feature</a>.', 'white-label-cms') ?></p>
        </div>
    </div>
</div>