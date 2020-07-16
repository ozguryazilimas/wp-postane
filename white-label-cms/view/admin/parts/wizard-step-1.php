<input type="hidden" name="hide_wordpress_logo_and_links" value="1" />
<input type="hidden" name="hide_all_dashboard_panels" value="1" />
<input type="hidden" name="hide_at_a_glance" value="1" />
<input type="hidden" name="hide_activities" value="1" />
<input type="hidden" name="hide_recent_comments" value="1" />
<input type="hidden" name="hide_quick_press" value="1" />
<input type="hidden" name="hide_news_and_events" value="1" />
<input type="hidden" name="remove_empty_dash_panel" value="1" />
<input type="hidden" name="hide_wp_version" value="1" />
<input type="hidden" name="wlcms_wizzard" value="1" />

<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Developer Branding', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main wizard-step1">
        <p><?php _e('You can set up White Label CMS quickly by adding your details below, and on the next page it will ask you about your clients details. Or you can click the Skip button and add these details later.', 'white-label-cms') ?></p>
        <div class="wlcms-input-group">
            <label><?php _e('Developer Name', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="text" name="wizard_developer_name" value="<?php echo esc_attr(wlcms_field_setting('developer_name')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('For use in footer and ALT text\'s.', 'white-label-cms') ?>
            </div>
        </div>

        <div class="wlcms-input-group">
            <label><?php _e('Developer URL', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="url" name="wizard_developer_url" value="<?php echo esc_url(wlcms_field_setting('developer_url')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('For use in footer and admin bar.', 'white-label-cms') ?>
            </div>
        </div>

        <div class="wlcms-input-group">
            <label><?php _e('Footer Text', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="text" name="footer_text" value="<?php echo esc_attr(wlcms_field_setting('footer_text')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('Text which will appear to the right of the Footer Image.', 'white-label-cms') ?>
            </div>
        </div>

        <div class="wlcms-input-group">
            <label><?php _e('RSS Feed', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="url" name="rss_feed_address" value="<?php echo esc_url(wlcms_field_setting('rss_feed_address')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('The RSS feed address. For example http://' . wlcms_site_domain() . '/feed/', 'white-label-cms') ?>
            </div>
        </div>
    </div>
</div>
