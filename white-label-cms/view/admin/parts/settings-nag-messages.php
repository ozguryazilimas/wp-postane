<div class="wlcms-input-group">
    <div class="wlcms-help">
        <?php _e('Nag Messages will be hidden to all User Roles up to and including Admins', 'white-label-cms') ?>
    </div>
    <div class="wlcms-input">
        <input class="wlcms-toggle wlcms-toggle-light" id="hide_nag_messages" value="1" name="hide_nag_messages" <?php checked(wlcms_field_setting('hide_nag_messages'), 1, true) ?> type="checkbox"/>
        <label class="wlcms-toggle-btn" for="hide_nag_messages"></label><label class="toggle-label" for="hide_nag_messages"><?php _e('Nag Update Messages'); ?></label> 
    </div>
</div>