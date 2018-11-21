<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Login Logo', 'login_logo', 'Replace the WordPress logo on the login page. Max width 320px') ?>
</div>

<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Retina Login Logo', 'retina_login_logo', 'Replace the Retina WordPress logo on the login page. Please make sure you use the standard retina format of x2') ?>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Logo Width', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="number" name="logo_width" class="wlcms-upload-input" value="<?php echo wlcms_field_setting('logo_width', '') ?>" />px
    </div>
    <div class="wlcms-help">
        <?php _e('Add a width to your Login Logo. Max width 320px', 'wlcms') ?>
    </div>
</div>
<div class="wlcms-input-group">
    <label><?php _e('Logo Height', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="number" name="logo_height" class="wlcms-upload-input" value="<?php echo wlcms_field_setting('logo_height', '') ?>" />px
    </div>
    <div class="wlcms-help">
        <?php _e('Add a height to your Login Logo.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Logo Bottom Margin', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="number" name="logo_bottom_margin" class="wlcms-upload-input" value="<?php echo wlcms_field_setting('logo_bottom_margin') ?>" />px
    </div>
    <div class="wlcms-help">
        <?php _e('Add a bottom margin to your Login Logo.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Background Color', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="background_color" class="wlcms-color-field" value="<?php echo wlcms_field_setting('background_color') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Background color for the login page. Changing to White will help your logo standout.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Background Image', 'background_image', 'Adds a background image to the login page.') ?>
</div>

<div class="wlcms-input-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light" id="full_screen_background_image" name="full_screen_background_image" value="1" type="checkbox" <?php checked(wlcms_field_setting('full_screen_background_image'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="full_screen_background_image"></label><label class="toggle-label" for="full_screen_background_image"><?php _e('Full Screen Background Image', 'wlcms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Stretch the background image to appear full screen.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Background Position', 'wlcms') ?></label>
    <div class="wlcms-input">
        <select class="wlcms-select" name="background_positions">
            <option value="center center" <?php selected(wlcms_field_setting('background_positions'), 'center center', true) ?>>Center Center</option>
            <option value="center top" <?php selected(wlcms_field_setting('background_positions'), 'center top', true) ?>>Center Top</option>
            <option value="center bottom" <?php selected(wlcms_field_setting('background_positions'), 'center bottom', true) ?>>Center Bottom</option>
            <option value="left top" <?php selected(wlcms_field_setting('background_positions'), 'left top', true) ?>>Left Top</option>
            <option value="left center" <?php selected(wlcms_field_setting('background_positions'), 'left center', true) ?>>Left Center</option>
            <option value="left bottom" <?php selected(wlcms_field_setting('background_positions'), 'left bottom', true) ?>>Left Bottom</option>
            <option value="right top" <?php selected(wlcms_field_setting('background_positions'), 'right top', true) ?>>Right Top</option>
            <option value="right center" <?php selected(wlcms_field_setting('background_positions'), 'right center', true) ?>>Right Center</option>
            <option value="right bottom" <?php selected(wlcms_field_setting('background_positions'), 'right bottom', true) ?>>Right Bottom</option>
        </select>
    </div>
    <div class="wlcms-help">
        <?php _e('Specify the CSS background position.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Background Repeat', 'wlcms') ?></label>
    <div class="wlcms-input">
        <select class="wlcms-select" name="background_repeat">
            <option value="repeat" <?php selected(wlcms_field_setting('background_repeat'), 'repeat', true) ?>>repeat</option>
            <option value="repeat-y" <?php selected(wlcms_field_setting('background_repeat'), 'repeat-y', true) ?>>repeat-y</option>
            <option value="no-repeat" <?php selected(wlcms_field_setting('background_repeat'), 'no-repeat', true) ?>>no-repeat</option>
        </select>
    </div>
    <div class="wlcms-help">
        <?php _e('Specify the CSS background-repeat.', 'wlcms') ?>
    </div>
</div>
<?php if( !is_multisite() ):?>
<p align="center"><a href="#wlcms-preview-content" class="wlcms-preview-link"><?php _e('Live Preview', 'wlcms') ?></a></p>
<?php endif;?>