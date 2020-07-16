<?php
$default_settings = wlcms()->Settings()->get_default_option('dashboard_widgets_visibility_roles');
$dashboard_role_stat = wlcms_field_setting('dashboard_role_stat');
$dashboard_widgets_visibility_roles = wlcms_field_setting('dashboard_widgets_visibility_roles');
if( ! $dashboard_role_stat) {
    $dashboard_widgets_visibility_roles = $default_settings;
}
?>
<div class="wlcms-input-group">
    <?php echo wlcms_form_upload_field(__('Dashboard Icon', 'white-label-cms'), 'dashboard_icon', __('Add a logo to the Dashboard. Suggested height 40px', 'white-label-cms')) ?>
</div>

<div class="wlcms-input-group">
    <label><?php _e('Dashboard Title', 'white-label-cms') ?></label>
    <div class="wlcms-input">
        <input type="text" name="dashboard_title" value="<?php echo esc_attr(wlcms_field_setting('dashboard_title')) ?>" />
    </div>
    <div class="wlcms-help">
        <?php _e('Change the heading for the Dashboard', 'white-label-cms') ?>
    </div>
</div>
<div class="wlcms-input-group">
    <label><?php _e('Select the Roles the Dashboard Panels Will Be Hidden To', 'white-label-cms') ?></label>
        <?php
        echo wlcms_select_roles(array('name' => 'dashboard_widgets_visibility_roles', 'class' => 'dashboard_widgets_visibility_roles wlcms-select2'), $dashboard_widgets_visibility_roles);
        ?>
        <div class="wlcms-help">
            <?php _e('Select the user roles this will be hidden to.', 'white-label-cms') ?>
        </div>
</div>
<div class="wlcms-input-group toggle-group">
    <ul>
        <li>
        <input type="hidden" value="1" name="dashboard_role_stat" />
        <input class="wlcms-toggle wlcms-toggle-light main-toggle main-toggle-reverse" id="hide_all_dashboard_panels" name="hide_all_dashboard_panels" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_all_dashboard_panels'), 1, true) ?>/>
        <label class="wlcms-toggle-btn" for="hide_all_dashboard_panels"></label><label class="toggle-label" for="hide_all_dashboard_panels"><?php _e('Hide All Dashboard Panels', 'white-label-cms')?></label> 
        <div class="wlcms-help">
            <?php _e('This will hide all the WordPress default dashboard panels. Or you can specify which panels should appear.', 'white-label-cms') ?>
        </div>
            <ul class="sub-fields">
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_at_a_glance" name="hide_at_a_glance" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_at_a_glance'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_at_a_glance"></label><label class="toggle-label" for="hide_at_a_glance"><?php _e('Hide \'At a Glance\'', 'white-label-cms')?></label> 
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_activities" name="hide_activities" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_activities'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_activities"></label><label class="toggle-label" for="hide_activities"><?php _e('Hide \'Activity\'', 'white-label-cms')?></label> 
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_recent_comments" name="hide_recent_comments" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_recent_comments'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_recent_comments"></label><label class="toggle-label" for="hide_recent_comments"><?php _e('Hide \'Recent Comments\'', 'white-label-cms') ?></label>
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_quick_press" name="hide_quick_press" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_quick_press'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_quick_press"></label><label class="toggle-label" for="hide_quick_press"><?php _e('Remove \'Quick Draft\'', 'white-label-cms')?></label>
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="hide_news_and_events" name="hide_news_and_events" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_news_and_events'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="hide_news_and_events"></label><label class="toggle-label" for="hide_news_and_events"><?php _e('Remove WordPress Events and News Widget', 'white-label-cms')?></label>
                </li>
                <li>
                    <input class="wlcms-toggle wlcms-toggle-light" id="remove_empty_dash_panel" name="remove_empty_dash_panel" value="1" type="checkbox" <?php checked(wlcms_field_setting('remove_empty_dash_panel'), 1, true) ?>/>
                    <label class="wlcms-toggle-btn" for="remove_empty_dash_panel"></label><label class="toggle-label" for="remove_empty_dash_panel"><?php _e('Remove Empty Dashboard Panel', 'white-label-cms')?></label>
                </li>
            </ul>
        </li>
    </ul>
</div>