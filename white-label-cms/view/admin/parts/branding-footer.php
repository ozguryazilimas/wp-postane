<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Footer Image', 'white-label-cms'), 'footer_image', __('Image which will appear in the footer replacing "Thank you for creating with WordPress. Max height 50px', 'white-label-cms')) ?>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Footer Text', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="footer_text" value="<?php echo wlcms_field_setting('footer_text') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Text which will appear to the right of the Footer Image.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Footer URL', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="url" name="footer_url" value="<?php echo wlcms_field_setting('footer_url') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace WordPress in the page titles.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('or use HTML', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <textarea name="footer_html" class="textarea-full"  id="footer_html"><?php echo wlcms_field_setting('footer_html') ?></textarea>
    </div>
    <div class="wlcms-help">
        <?php _e('If you would like more control over what appears in the footer, add your own HTML.<br/>
Note: this will overwrite anything set in the Footer Image, Footer Text and Footer URL.', 'white-label-cms') ?>
    </div>
</div>