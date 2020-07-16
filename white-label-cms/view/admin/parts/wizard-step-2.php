<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Clients Branding', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <input type="hidden" name="form_section" value="wizard" />
        <!-- Start wlcms-body-main -->
        <div class="wlcms-input-group">
            <label><?php _e('Clients Business Name', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="text" name="client_business_name" value="<?php echo esc_attr(wlcms_field_setting('client_business_name')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('For use in Admin Page Title and Dashboard Title.', 'white-label-cms') ?>
            </div>
        </div>
        
        <div class="wlcms-input-group">
            <?php echo wlcms_form_upload_field('Login Logo', 'login_logo', 'Replace the WordPress logo on the login page. Max width 320px') ?>
        </div>
        <div class="wlcms-input-group toggle-group">
            <div class="wlcms-input">
                <input class="wlcms-toggle wlcms-toggle-light main-toggle" data-revised="1" id="add_retina_logo" name="add_retina_logo" value="1" <?php checked(wlcms_field_setting('add_retina_logo'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="add_retina_logo"></label><label class="toggle-label" for="add_retina_logo"><?php _e('Retina Login Logo', 'white-label-cms') ?></label> 
            </div>
            <div class="sub-fields">
                <div class="wlcms-input-group">
                    <?php echo wlcms_form_upload_field(__('Upload Login Logo', 'white-label-cms'), 'retina_login_logo', __('Replace the Retina WordPress logo on the login page. Please make sure you use the standard retina format of x2', 'white-label-cms')) ?>
                </div>
            </div>
        </div>
        <!-- End wlcms-body-main -->
    </div>
</div>