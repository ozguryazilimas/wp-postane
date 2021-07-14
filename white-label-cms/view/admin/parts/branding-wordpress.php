
<div class="wlcms-input-group">
    <div class="wlcms-input">
        <input class="wlcms-toggle wlcms-toggle-light" name="hide_wordpress_logo_and_links" value="1" id="hide_wordpress_logo_and_links" type="checkbox" <?php checked(wlcms_field_setting('hide_wordpress_logo_and_links'), 1, true) ?>/>
        <label class="wlcms-toggle-btn" for="hide_wordpress_logo_and_links"></label><label class="toggle-label" for="hide_wordpress_logo_and_links"><?php _e('Hide WordPress Logo and Links', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help branding-toggled-off" style="color: #f64031;">
        <?php _e('You have selected no, so all custom branding images will be ignored', 'white-label-cms') ?>
    </div>
    <div class="wlcms-help">
        <?php _e('Hide mentions of WordPress and hide the links to WordPress.org.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light" id="hide_wp_version" name="hide_wp_version" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_wp_version'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="hide_wp_version"></label><label class="toggle-label" for="hide_wp_version"><?php _e('Hide WP Version', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Hide version number of WordPress which appears in the footer.', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Custom Page Titles', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="custom_page_title" value="<?php echo esc_attr(wlcms_field_setting('custom_page_title')) ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Replace WordPress in the page titles.', 'white-label-cms') ?>
    </div>
</div>