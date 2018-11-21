
<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field('Dashboard Icon', 'dashboard_icon', 'Add a logo to the Dashboard. Suggested height 40px') ?>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Dashboard Title', 'wlcms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="dashboard_title" value="<?php echo wlcms_field_setting('dashboard_title') ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Change the heading for the Dashboard', 'wlcms') ?>
    </div>
</div>

<div class="wlcms-input-group toggle-group">
    <ul>
        <li>
        <input class="wlcms-toggle wlcms-toggle-light main-toggle" id="hide_all_dashboard_panels" name="hide_all_dashboard_panels" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_all_dashboard_panels'), 1, true) ?>/>
        <label class="wlcms-toggle-btn" for="hide_all_dashboard_panels"></label><label class="toggle-label" for="hide_all_dashboard_panels"><?php _e('Hide All Dashboard Panels', 'wlcms')?></label> 
        <div class="wlcms-help">
            <?php _e('This will hide all the WordPress default dashboard panels. Or you can specify which panels should appear. Note this will not affect White Label CMS admins.', 'wlcms') ?>
        </div>
            <ul class="sub-fields">
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_at_a_glance" name="hide_at_a_glance" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_at_a_glance'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_at_a_glance"></label><label class="toggle-label" for="hide_at_a_glance"><?php _e('Hide \'At a Glance\'', 'wlcms')?></label> 
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_activities" name="hide_activities" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_activities'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_activities"></label><label class="toggle-label" for="hide_activities"><?php _e('Hide \'Activity\'', 'wlcms')?></label> 
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_recent_comments" name="hide_recent_comments" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_recent_comments'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_recent_comments"></label><label class="toggle-label" for="hide_recent_comments"><?php _e('Hide \'Recent Comments\'', 'wlcms') ?></label>
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_quick_press" name="hide_quick_press" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_quick_press'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_quick_press"></label><label class="toggle-label" for="hide_quick_press"><?php _e('Remove \'Quick Draft\'', 'wlcms')?></label>
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_news_and_events" name="hide_news_and_events" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_news_and_events'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_news_and_events"></label><label class="toggle-label" for="hide_news_and_events"><?php _e('Remove WordPress Events and News Widget', 'wlcms')?></label>
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="remove_empty_dash_panel" name="remove_empty_dash_panel" value="1" type="checkbox" <?php checked(wlcms_field_setting('remove_empty_dash_panel'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="remove_empty_dash_panel"></label><label class="toggle-label" for="remove_empty_dash_panel"><?php _e('Remove Empty Dashboard Panel', 'wlcms')?></label>
                </li>
            </ul>
        </li>
    </ul>
</div>