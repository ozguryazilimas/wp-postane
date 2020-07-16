<div class="wlcms-input-group">
    <div class="wlcms-input">
        <textarea class="textarea-full wlcms-css" name="login_custom_js"><?php echo esc_html(wlcms_field_setting('login_custom_js')) ?></textarea>
    </div>
    <div class="wlcms-help">
        <p><?php _e('Completely customise the login page by entering your own Javascript code.', 'white-label-cms') ?></p>
        <p><?php _e('For example', 'white-label-cms') ?>:<br/>
        </p>
        <code>document.getElementById('user_login').placeholder='Username';<br/>&nbsp;document.getElementById('user_pass').placeholder='Password';</code>
    </div>
</div>
<?php if( !is_multisite() ):?>
<p align="center"><a href="#wlcms-preview-content" class="wlcms-preview-link"><?php _e('Live Preview', 'white-label-cms') ?></a></p>
<?php endif;?>
