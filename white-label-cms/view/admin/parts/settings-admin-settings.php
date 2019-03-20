
<div class="wlcms-input-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light" id="hide_help_box" name="hide_help_box" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_help_box'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="hide_help_box"></label><label class="toggle-label" for="hide_help_box"><?php _e('Hide Help Box', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Hide the help tab which appears in the top right', 'white-label-cms') ?>
    </div>
</div>

<div class="wlcms-input-group">
    <div class="wlcms-input">
    <input class="wlcms-toggle wlcms-toggle-light" id="hide_screen_options" name="hide_screen_options" value="1" type="checkbox" <?php checked(wlcms_field_setting('hide_screen_options'), 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="hide_screen_options"></label><label class="toggle-label" for="hide_screen_options"><?php _e('Hide Screen Options', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Hide the screen options which appear in the top right', 'white-label-cms') ?>
    </div>
</div>