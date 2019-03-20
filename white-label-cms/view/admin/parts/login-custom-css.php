<div class="wlcms-input-group">
    <div class="wlcms-input">
        <textarea class="textarea-full wlcms-css" name="login_custom_css"><?php echo esc_textarea(wlcms_field_setting('login_custom_css')) ?></textarea>
    </div>
    <div class="wlcms-help">
        <p><?php _e('Completely customise the login page by entering your own CSS.', 'white-label-cms') ?></p>
        <p><?php _e('For example', 'white-label-cms') ?>:<br/>
        .login form { background-color: #0013FF }<br/>
        .login #login p#nav a { color: #333 !important }<br/>
        </p>
        <p>
        <?php _e('Or if you want to get fancy', 'white-label-cms') ?>:<br/>
        #wlcms-login-wrapper{ background: url('wp-content/plugins/white-label-cms/images/footergrass.jpg') repeat-x fixed center bottom transparent; display: block; height: 100%; left: 0; overflow: auto; position: absolute; top: 0; width: 100%;}
        </p>
    </div>
</div>
<?php if( !is_multisite() ):?>
<p align="center"><a href="#wlcms-preview-content" class="wlcms-preview-link"><?php _e('Live Preview', 'white-label-cms') ?></a></p>
<?php endif;?>