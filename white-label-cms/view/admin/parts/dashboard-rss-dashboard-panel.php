<div class="wlcms-input-group toggle-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light main-toggle" data-revised="1" id="add_own_rss_panel" name="add_own_rss_panel" value="1" type="checkbox" <?php checked(wlcms_field_setting('add_own_rss_panel'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="add_own_rss_panel"></label><label class="toggle-label" for="add_own_rss_panel"><?php _e('Add Your Own RSS  Panel', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('This will appear on the dashboard. If you want your client to be kept up to date with what you are doing in your business, set up your RSS feed.', 'white-label-cms') ?>
    </div>
    <div class="sub-fields">

        <div class="wlcms-input">
            <div class="wlcms-input-group">
                <label><?php _e('RSS Title', 'white-label-cms') ?></label>
                <div class="wlcms-input">
                    <input type="text" name="rss_title" value="<?php echo esc_attr(wlcms_field_setting('rss_title')) ?>" />
                </div>
                <div class="wlcms-help">
                    <?php _e('The title of the RSS Panel', 'white-label-cms') ?>
                </div>
            </div>

            <div class="wlcms-input-group">
                <?php echo wlcms_form_upload_field(__('Add Your Logo', 'white-label-cms'), 'rss_logo', __('Add a logo to appear on the panel before the title.', 'white-label-cms')) ?>
            </div>

            <div class="wlcms-input-group">
                <label><?php _e('RSS Feed', 'white-label-cms') ?></label>
                <div class="wlcms-input">
                    <input type="text" id="rss_feed_address" name="rss_feed_address" value="<?php echo esc_url(wlcms_field_setting('rss_feed_address')) ?>" />
                </div>
                <div class="wlcms-help">
                    <?php _e('The RSS feed address. For example feed://' . wlcms_site_domain() . '/feed/', 'white-label-cms') ?>
                </div>
            </div>

            <div class="wlcms-input-group">
                <label><?php _e('Number of Items to appear', 'white-label-cms') ?></label>
                <div class="wlcms-input">
                    <select name="rss_feed_number_of_item">
                        <?php 
                        $item_setting = wlcms_field_setting('rss_feed_number_of_item');
                        for($item = 1; $item <= 10; $item++) {?>
                        <option value="<?php echo esc_attr($item)?>" <?php echo ($item == $item_setting) ? ' selected="selected"' : '' ?>><?php echo esc_attr($item)?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="wlcms-help">
                    <?php _e('Number of RSS items to show.', 'white-label-cms') ?>
                </div>
            </div>

            <div class="wlcms-input-group">
                <div class="wlcms-input">
                <input class="wlcms-toggle wlcms-toggle-light" id="show_post_content" value="1" name="show_post_content" <?php checked(wlcms_field_setting('show_post_content'), 1, true) ?> type="checkbox"/>
                <label class="wlcms-toggle-btn" for="show_post_content"></label><label class="toggle-label" for="show_post_content"><?php _e('Show Post Contents');?></label> 
                </div>
                <div class="wlcms-help">
                </div>
            </div>

            <div class="wlcms-input-group">
                <label><?php _e('Introduction HTML', 'white-label-cms') ?></label>
                <div class="wlcms-input">
                    <textarea class="textarea-full" name="rss_introduction"><?php echo wlcms_esc_html_e(wlcms_field_setting('rss_introduction')) ?></textarea>
                </div>
                <div class="wlcms-help">
                    <?php _e('Add introduction text to appear above the RSS items. You can use HTML.', 'white-label-cms') ?>
                </div>
            </div>
        </div>
    </div>
</div>