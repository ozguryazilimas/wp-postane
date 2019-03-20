<div class="wlcms-input-group">
    <label><?php _e('Custom Stylesheet URL', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="settings_custom_css_url" value="<?php echo wlcms_field_setting('settings_custom_css_url') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Create and upload a custom stylesheet with all style rules prefixed with .mceContentBody to your themes directory and enter the filename', 'white-label-cms') ?>
    </div>
</div>
