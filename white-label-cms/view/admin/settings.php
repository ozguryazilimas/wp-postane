<?php
$views = array(
        "branding" => __('Branding'),
        "login" => __('Login'),
        "dashboard" => __('Dashboard'),
        "menus" => __('Menus'),
        "settings" => __('Settings')
    );
?>
<div class="wlcms_wrapper">
    <div class="wlcms_messages">
        <?php do_action("wlcms_messages");?>
        <span></span>
    </div>
    <form method="post" enctype="multipart/form-data" class="wlcms-form" action="<?php echo wlcms()->admin_url(); ?>" >
        <?php wp_nonce_field('wlcms-settings-action', 'wlcms-settings_nonce'); ?>
        <div class="wlcms_header">
            <h1 class="wlcms_page_title"><img src="<?php echo WLCMS_ASSETS_URL ?>images/wlcms-logo.png"><?php _e("White Label CMS", 'white-label-cms'); ?></h1>
        </div>
        <div class="wlcms-navigation navigation">
            <ul>
                <?php
                foreach($views as $slug => $view):
                ?>
                <li>
                    <a href="#tab-<?php echo $slug ?>" data-tab="tab-<?php echo $slug ?>" id="wlcms_tab-<?php echo $slug ?>"<?php $slug == 'branding' ? ' class="current"' : ''?>><?php _e($view, 'white-label-cms'); ?></a>
                </li>
                <?php
                endforeach;
                ?>
                <?php do_action("wlcms_after_menu_tab"); ?>
            </ul>
            <span class="wlcms-pull-right">
                <input type="submit" value="<?php _e('Save', 'white-label-cms') ?>" class="button button-primary button-large" name="wlcms-settings" />
            </span>
        </div>
        <div class="wlcms_content">
            <?php
            
            do_action("wlcms_before_body");
            
            foreach ($views as $slug => $view) :
                echo '<section class="tab-'. $slug .'" id="'. $slug .'">';
                wlcms()->admin_view( 'parts/' . $slug);
                echo '</section>';
            endforeach;
            
            do_action("wlcms_after_body");
            ?>
        </div>
    </form>
    <?php wlcms()->admin_view( 'parts/advert');?>
    <div class="wlcms_footer">
        <?php do_action("wlcms_footer"); ?>
        <div class="wlcms-navigation">
            <ul>
                <li><a href="#tab-import-settings" data-tab="tab-import-settings" id="wlcms_tab_import-settings"><?php _e('Import Settings')?></a></li>
                <li><a href="<?php echo admin_url('options-general.php?page=wlcms&wlcms-action=export') ?>" class="wlcms-ignore"><?php _e('Export Settings') ?></a></li>
                <li><a href="<?php echo admin_url('options-general.php?page=wlcms&wlcms-action=reset') ?>" class="wlcms-ignore reset-confirm"><?php _e('Reset Plugin')?></a></li>
            </ul>
        </div>
    </div>
</div>
<?php
wp_enqueue_media();
