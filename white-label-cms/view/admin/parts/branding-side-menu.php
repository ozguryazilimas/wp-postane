<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Side Menu Image', 'side_menu_image', 'Image will appear at the top of the side menu. Max width 160px') ?>
</div>

<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Collapsed Side Menu Image', 'collapsed_side_menu_image', 'Image will appear at the top of the side menu when it is collapsed. Max width 36px') ?>
</div>
<div class="wlcms-input-group">
    <label><?php _e('Side Menu Link URL', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="url" name="side_menu_link_url" value="<?php echo wlcms_field_setting('side_menu_link_url') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('URL the Side Menu Image will link to.', 'wlcms') ?>
    </div>
</div>
<div class="wlcms-input-group">
    <label><?php _e('Side Menu Alt Text', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="side_menu_alt_text" value="<?php echo wlcms_field_setting('side_menu_alt_text') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Alt text for the Side Menu Image link.', 'wlcms') ?>
    </div>
</div>