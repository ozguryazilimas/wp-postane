
<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Admin Bar Logo', 'white-label-cms'), 'admin_bar_logo', __('Replace the WordPress logo in the admin bar. Max height 20px', 'white-label-cms')) ?>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Admin Bar Alt Text', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="admin_bar_alt_text" value="<?php echo esc_attr(wlcms_field_setting('admin_bar_alt_text')) ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace the "WordPress" Alt text.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Replace Howdy Text', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="admin_bar_howdy_text" value="<?php echo esc_attr(wlcms_field_setting('admin_bar_howdy_text')) ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Add a space to completely remove it from the admin bar. Or replace it with something like: "Hi,"', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Admin Bar URL', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="url" name="admin_bar_url" value="<?php echo esc_url(wlcms_field_setting('admin_bar_url')) ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace the link to WordPress.org.', 'white-label-cms') ?>
    </div>
</div>