<?php
$legacy = wlcms_field_setting('legacy_menu');
if($legacy) {
?>
<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('White Label CMS Legacy Menus', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <div class="wlcms-input-group">
            <div class="wlcms-help">
                <p><i><?php _e('You are seeing this because you have installed version 1 of the plugin in the past.', 'white-label-cms') ?></i></p>
                <p><i><?php _e('In version 1 you could only change the menus for Editors.', 'white-label-cms') ?></i></p>
                <p><i><?php _e('We recommend using the new White Label CMS Admin to manage the menus and in order to do so you must reset the menus to the WordPress Defaults.  If you do this, this section will disappear.', 'white-label-cms') ?></i></p>
            </div>
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" name="remove_legacy_menu" value="1" id="remove_legacy_menu" type="checkbox" <?php checked(wlcms_field_setting('remove_legacy_menu'), 1, true) ?>/>
            <label class="wlcms-toggle-btn" for="remove_legacy_menu"></label><label class="toggle-label" for="remove_legacy_menu"><?php _e('Use the new version', 'white-label-cms') ?></label> 
            </div>
        </div>
    </div>
</div>
<?php }