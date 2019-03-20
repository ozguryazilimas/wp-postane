<?php wlcms()->admin_view('parts/menus-legacy'); ?>
<div class="wlcms-body-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('White Label CMS Admin', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <?php wlcms()->admin_view('parts/menus-white-label-cms-admin'); ?>
    </div>
</div>
<div class="wlcms-body-wrapper menu-admin-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Menus', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <?php wlcms()->admin_view('parts/menus-menus'); ?>
    </div>
</div>
<div class="wlcms-body-wrapper menu-admin-wrapper">
    <div class="wlcms-body-header">
        <h2><?php _e('Admin Bar Menus', 'white-label-cms') ?></h2>
    </div>
    <div class="wlcms-body-main">
        <?php wlcms()->admin_view('parts/menus-admin-bar-menus'); ?>
    </div>
</div>