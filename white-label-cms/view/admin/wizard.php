<div class="wlcms_wrapper">
    <?php do_action("wlcms_messages"); ?>
    <form method="post" enctype="multipart/form-data" class="wlcms-form" action="<?php echo wlcms()->admin_url(); ?>" >
        <?php wp_nonce_field('wlcms-settings-action', 'wlcms-settings_nonce'); ?>
        <div class="wlcms_header">
            <h1 class="wlcms_page_title"><img src="<?php echo WLCMS_ASSETS_URL ?>images/wlcms-logo.png"><?php _e("White Label CMS", 'white-label-cms'); ?></h1>
        </div>
        <div class="navigation wizard-steps">
            <ul>
                <li id="wlcms_tab_step-1">
                    <?php _e('Quick Setup Wizard: Step 1 of 2 - Developers Branding', 'white-label-cms')?>
                    <span class="wlcms-pull-right">
                        <a href="<?php echo wlcms()->admin_url() ?>" class="button button-large"><?php _e('Skip', 'white-label-cms')?></a>
                        <input type="button" value="<?php _e('Next', 'white-label-cms') ?>" class="button button-primary button-large wlcms-next-step" name="wlcms-settings" />
                    </span>
                </li>
                <li id="wlcms_tab_step-2" style="display:none"><?php _e('Quick Setup Wizard: Step 2 of 2 - Clients Branding', 'white-label-cms')?>
                    <span class="wlcms-pull-right">
                        <input type="button" value="<?php _e('Back to step 1', 'white-label-cms') ?>" class="button wlcms-prev-step button-large" name="wlcms-settings" />
                        <input type="submit" value="<?php _e('Save', 'white-label-cms') ?>" class="button button-primary button-large" name="wlcms-settings" />
                    </span>
                </li>
            </ul>
        </div>
        <div class="wlcms_content">
            <section id="tab-step-1" class="current">
            <?php
                wlcms()->admin_view('parts/wizard-step-1');
            ?>
            </section>
            <section id="tab-step-2" style="display:none">
            <?php
                wlcms()->admin_view('parts/wizard-step-2');
            ?>
            </section>

            <div class="wizard-live-preview">
                <?php wlcms()->admin_view('parts/live-preview'); ?>
            </div>
        </div>
    </form>
</div>
<?php
wp_enqueue_media();