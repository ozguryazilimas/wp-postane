
<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Admin Bar Logo', 'admin_bar_logo', 'Replace the WordPress logo in the admin bar. Max height 20px') ?>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Admin Bar Alt Text', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="admin_bar_alt_text" value="<?php echo wlcms_field_setting('admin_bar_alt_text') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace the "WordPress" Alt text.', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Replace Howdy Text', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="admin_bar_howdy_text" value="<?php echo wlcms_field_setting('admin_bar_howdy_text') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace the Howdy text in admin bar', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Admin Bar URL', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="url" name="admin_bar_url" value="<?php echo wlcms_field_setting('admin_bar_url') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace the link to WordPress.org.', 'wlcms') ?>
    </div>
</div>