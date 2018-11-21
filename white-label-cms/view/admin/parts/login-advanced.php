<div class="wlcms-input-group">
    <div class="wlcms-input">
        
    <input class="wlcms-toggle wlcms-toggle-light" id="hide_register_lost_password" name="hide_register_lost_password" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_register_lost_password'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="hide_register_lost_password"></label><label class="toggle-label" for="hide_register_lost_password"><?php _e('Hide "Register / Lost your password?" link', 'wlcms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Hide the "Register / Lost your password?" link which appears below the login form.', 'wlcms') ?>
    </div>
</div>
<div class="wlcms-input-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light" id="hide_back_to_link" name="hide_back_to_link" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_back_to_link'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="hide_back_to_link"></label><label class="toggle-label" for="hide_back_to_link"><?php _e('Hide "Back to" link', 'wlcms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Hide the "Back to" link which appears below the login form.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Form Background Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="form_background_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('form_background_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Background color of the login form', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Form Label Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="form_label_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('form_label_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Color of the labels on the login form', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Form Button Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="form_button_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('form_button_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Color of the button on the login form', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Form Button Hover Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="form_button_hover_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('form_button_hover_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Hover color of the button on the login form', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Form Button Text Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="form_button_text_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('form_button_text_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Color of the text on the button on the login form', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Form Button Text Hover Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="form_button_text_hover_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('form_button_text_hover_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Hover color of the text on the button on the login form', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Back to / Register Link Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="back_to_register_link_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('back_to_register_link_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Color of the link text of Back to / Register', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Back to / Register Link Hover Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="back_to_register_link_hover_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('back_to_register_link_hover_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Hover color of the link text of Back to / Register', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Privacy Policy Link Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="privacy_policy_link_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('privacy_policy_link_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Color of the link text of Privacy Policy', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Privacy Policy Link Hover Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="privacy_policy_link_hover_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('privacy_policy_link_hover_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Hover color of the link text of Privacy Policy', 'wlcms') ?>
    </div>
</div>
<?php if( !is_multisite() ):?>
<p align="center"><a href="#wlcms-preview-content" class="wlcms-preview-link"><?php _e('Live Preview', 'wlcms') ?></a></p>
<?php endif;?>