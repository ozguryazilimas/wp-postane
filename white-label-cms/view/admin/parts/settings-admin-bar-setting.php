
<div class="wlcms-input-group">
    <div class="wlcms-input">
        <input class="wlcms-toggle wlcms-toggle-light" id="hide_admin_bar_all" value="1" name="hide_admin_bar_all" <?php checked(wlcms_field_setting('hide_admin_bar_all'), 1, true) ?> type="checkbox"/>
        <label class="wlcms-toggle-btn" for="hide_admin_bar_all"></label><label class="toggle-label" for="hide_admin_bar_all"><?php _e('Hide Front-end Admin Bar'); ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('This will disable the admin bar on the front-end for all logged in users', 'white-label-cms') ?>
    </div>
</div>
