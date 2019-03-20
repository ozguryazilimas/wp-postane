<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Logo and Background', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <input type="hidden" name="form_section" value="setting" />
        <?php wlcms()->admin_view('parts/login-logo-and-background'); ?>
    </div>
</div>

<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Advanced', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <?php wlcms()->admin_view('parts/login-advanced'); ?>
    </div>
</div>

<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Custom CSS', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <?php wlcms()->admin_view('parts/login-custom-css'); ?>
    </div>
</div>

<div class="login-live-preview">
    <?php wlcms()->admin_view('parts/live-preview'); ?>
</div>
