
<div class="wlcms-input-group">
    <label><?php _e('Developer Name', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="developer_name" value="<?php echo wlcms_field_setting('developer_name') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('For use in footer and ALT text\'s.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Developer URL', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="url" name="developer_url" value="<?php echo wlcms_field_setting('developer_url') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('For use in footer and admin bar.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Developer Icon', 'white-label-cms'), 'developer_icon', __('We recommend at 16 x 16 image', 'white-label-cms')) ?>
</div>

<div class="wlcms-input-group toggle-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light main-toggle" id="use_developer_icon_footer" value="1" name="use_developer_icon_footer" <?php checked(wlcms_field_setting('use_developer_icon_footer'), 1, true) ?> type="checkbox"/>
    <label class="wlcms-toggle-btn" for="use_developer_icon_footer"></label><label class="toggle-label" for="use_developer_icon_footer"><?php _e('Use Developer Icon in Footer');?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('If you wish to use a different image for the footer, you can.', 'white-label-cms') ?>
    </div>

    <div class="sub-fields">
        <div class="wlcms-input-group">
            <?php echo wlcms_form_upload_field(__('Developer Footer Icon', 'white-label-cms'), 'developer_icon_footer_url', __x('Image will appear in footer menu.', 'white-label-cms')) ?>
        </div>

    </div>
</div>

<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Developer Side Menu Image', 'white-label-cms'), 'developer_side_menu_image', __x('Image will appear in side menu.', 'white-label-cms')) ?>
</div>