
<div class="wlcms-input-group">
    <label><?php _e('Gutenberg Exit Button', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <select class="wlcms-select gutenberg-exit-toggle" name="gutenberg_exit_icon">
            <option value="exit-icon" <?php selected(wlcms_field_setting('gutenberg_exit_icon'), 'exit-icon', true) ?><?php selected(wlcms_field_setting('gutenberg_exit_icon'), '', true) ?>>Exit Icon</option>
            <option value="admin-bar-logo" <?php selected(wlcms_field_setting('gutenberg_exit_icon'), 'admin-bar-logo', true) ?>>Admin Bar Logo</option>
            <option value="custom-icon" <?php selected(wlcms_field_setting('gutenberg_exit_icon'), 'custom-icon', true) ?>>Custom Icon</option>
        </select>
    </div>
</div>
<div class="wlcms-input-group custom_gutenberg_exit_wrapper">
    <?php echo wlcms_form_upload_field(__('Custom Image', 'white-label-cms'), 'gutenberg_exit_custom_icon', __('Max width and height 50px', 'white-label-cms')) ?>
</div>
<div class="wlcms-help branding-toggled-off" style="color: #f64031;">
Note: You need to turn on the Hide WordPress Logo and Links, for this to work.
</div>
<div class="wlcms-help help_gutenberg_exit">
        <?php _e('Replace the WordPress logo on the Gutenberg Editor page with <span></span>', 'white-label-cms') ?>
</div>
