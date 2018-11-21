<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Clients Branding', 'wlcms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <input type="hidden" name="form_section" value="wizard" />
        <!-- Start wlcms-body-main -->
        <div class="wlcms-input-group">
            <label><?php _e('Clients Business Name', 'wlcms') ?></label>
            <div class="wlcms-input">
                <input type="text" name="client_business_name" value="<?php echo wlcms_field_setting('client_business_name') ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('For use in Admin Page Title and Dashboard Title.', 'wlcms') ?>
            </div>
        </div>
        
        <div class="wlcms-input-group">
            <?php echo wlcms_form_upload_field('Login Logo', 'login_logo', 'Replace the WordPress logo on the login page. Max width 320px') ?>
        </div>
        <div class="wlcms-input-group toggle-group">
            <div class="wlcms-input">
                <input class="wlcms-toggle wlcms-toggle-light main-toggle" data-revised="1" id="add_retina_logo" name="add_retina_logo" value="1" <?php checked(wlcms_field_setting('add_retina_logo'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="add_retina_logo"></label><label class="toggle-label" for="add_retina_logo"><?php _e('Retina Login Logo', 'wlcms') ?></label> 
            </div>
            <div class="sub-fields">
                <div class="wlcms-input-group">
                    <?php echo wlcms_form_upload_field('Upload Login Logo', 'retina_login_logo', 'Replace the Retina WordPress logo on the login page. Please make sure you use the standard retina format of x2') ?>
                </div>
            </div>
        </div>
        <!-- End wlcms-body-main -->
    </div>
</div>