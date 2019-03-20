<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Side Menu Image', 'white-label-cms'), 'side_menu_image', __('Image will appear at the top of the side menu. Max width 160px', 'white-label-cms')) ?>
</div>

<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Collapsed Side Menu Image', 'white-label-cms'), 'collapsed_side_menu_image', __('Image will appear at the top of the side menu when it is collapsed. Max width 36px', 'white-label-cms')) ?>
</div>
<div class="wlcms-input-group">
    <label><?php _e('Side Menu Link URL', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="url" name="side_menu_link_url" value="<?php echo wlcms_field_setting('side_menu_link_url') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('URL the Side Menu Image will link to.', 'white-label-cms') ?>
    </div>
</div>
<div class="wlcms-input-group">
    <label><?php _e('Side Menu Alt Text', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="side_menu_alt_text" value="<?php echo wlcms_field_setting('side_menu_alt_text') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Alt text for the Side Menu Image link.', 'white-label-cms') ?>
    </div>
</div>