<!-- Own Welcome Panel -->
<?php
$welcome_panel_is_active = wlcms_welcome_value(0, 'is_active');
?>
<div class="wlcms-input-group toggle-group wlcms-dashboard" data-wlcms_dashboard="1">
    <div class="wlcms-help">
        <?php _e('Add your own Welcome Panel to the Dashboard page. This will appear on the dashboard. We recommend providing your contact details and links to the help files you have made for your client.', 'white-label-cms') ?>
    </div>
    <div class="wlcms-input">
    <input  class="wlcms-toggle wlcms-toggle-light main-toggle" id="welcome_panel" data-revised="1" name="welcome_panel[0][is_active]" value="1" type="checkbox" <?php checked($welcome_panel_is_active, 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="welcome_panel"></label><label class="toggle-label" for="welcome_panel"><?php _e('Add Your Own Welcome Panel', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('You can add your own welcome panel.', 'white-label-cms') ?>
    </div>
    <div class="sub-fields">
        <div class="wlcms-help"></div>
        <label><?php _e('Select the Roles the Welcome Panel Will Be Visible To', 'white-label-cms') ?></label>

        <?php
        echo wlcms_select_roles(array('name' => 'welcome_panel[0][visible_to]', 'class' => 'wlcms_visible_to wlcms-select2'), wlcms_welcome_value(0, 'visible_to'));
        ?>
        <div class="wlcms-help">
            <?php _e('Select the user roles this will be visible to.', 'white-label-cms') ?>
        </div>
        <?php
        $checked_welcome_type = wlcms_welcome_value(0, 'template_type');

        $beaver_args = array(
            'post_type'       => 'fl-builder-template',
            'posts_per_page'  => '-1',  
            'tax_query'       => array(
                array(
                    'taxonomy'  => 'fl-builder-template-type',
                    'field'     => 'slug',
                    'terms'     => array( 'layout', 'row' )
                )
            )
        );
        $elementor_args = array(
            'post_type'         => 'elementor_library',
            'posts_per_page'    => '-1',
            'post_status'		=> 'publish'
        );

        $is_basic = ( ! $checked_welcome_type ) || ($checked_welcome_type == 'html') ? true : false;
        
        ?>
        <div class="wlcms-input-group">
            <label><?php _e('Template Type', 'white-label-cms') ?></label>
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light template_type template_type1" data-template_type="1" data-page_type="html" id="template_type_basic" value="html" name="welcome_panel[0][template_type]" <?php checked($is_basic, true, true) ?> type="radio"/>
            <label class="wlcms-toggle-btn" for="template_type_basic"></label><label class="toggle-label" for="template_type_basic"><?php _e('Basic HTML');?></label> 
            
            <input class="wlcms-toggle wlcms-toggle-light template_type template_type1" data-template_type="1" data-page_type="elementor"<?php echo !wlcms_is_elementor_active() ? ' disabled':''?> id="template_type_elementor" value="Elementor" name="welcome_panel[0][template_type]" <?php checked($checked_welcome_type, 'Elementor', true) ?> type="radio"/>
            <label class="wlcms-toggle-btn<?php echo !wlcms_is_elementor_active() ? ' disabled':''?>" for="template_type_elementor"></label><label class="toggle-label<?php echo !wlcms_is_elementor_active() ? ' disabled':''?>" for="template_type_elementor"><?php _e('Elementor');?></label> 
            <input class="wlcms-toggle wlcms-toggle-light template_type template_type1" data-template_type="1" data-page_type="beaver"<?php echo !wlcms_is_beaver_builder_active() ? ' disabled':''?> id="template_type_beaver" value="Beaver Builder" name="welcome_panel[0][template_type]" <?php checked($checked_welcome_type, 'Beaver Builder', true) ?> type="radio"/>
            <label class="wlcms-toggle-btn<?php echo !wlcms_is_beaver_builder_active() ? ' disabled':''?>" for="template_type_beaver"></label><label class="toggle-label<?php echo !wlcms_is_beaver_builder_active() ? ' disabled':''?>" for="template_type_beaver"><?php _e('Beaver Builder Pro');?></label> 
            </div>
        </div>

        <div class="welcome-page1">
            <label><?php _e('Template', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <?php
                echo wlcms_select_pages(array('name' => 'welcome_panel[0][page_id_elementor]', 'class' => 'wlcms_visible_to wlcms-select2 elementor_page1'), wlcms_welcome_value(0, 'page_id_elementor'), $elementor_args);
                echo wlcms_select_pages(array('name' => 'welcome_panel[0][page_id_beaver]', 'class' => 'wlcms_visible_to wlcms-select2 beaver_page1'), wlcms_welcome_value(0, 'page_id_beaver'), $beaver_args);
                ?>
            </div>
        </div>
        <div class="welcome-basicHtml1">
            <label><?php _e('Title of Welcome Panel', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="text" name="welcome_panel[0][title]" value="<?php echo esc_attr(wlcms_welcome_value(0, 'title')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('Title of the Welcome Panel', 'white-label-cms') ?>
            </div>
        </div>

        <div class="welcome-basicHtml1">
            <label><?php _e('Welcome Panel Description HTML', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <textarea class="textarea-full" name="welcome_panel[0][description]"><?php echo esc_html(wlcms_welcome_value(0, 'description')) ?></textarea>
            </div>
            <div class="wlcms-help"><?php _e('You can add any HTML to the welcome panel.', 'white-label-cms') ?></div>
        </div>

        <div class="welcome-basicHtml1">
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light welcome_panel_fullwidth" id="own_welcome_panel_fullwidth" value="1" name="welcome_panel[0][is_fullwidth]" <?php checked(wlcms_welcome_value(0 , 'is_fullwidth'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="own_welcome_panel_fullwidth"></label><label class="toggle-label" for="own_welcome_panel_fullwidth"><?php _e('Make full-width');?></label> 
            </div>
        </div>
        <?php if( wlcms_has_pagebuilder() ):?>
        <div class="welcome-page1">
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" id="own_welcome_panel_show_title" value="1" name="welcome_panel[0][show_title]" <?php checked(wlcms_welcome_value(0 , 'show_title'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="own_welcome_panel_show_title"></label><label class="toggle-label" for="own_welcome_panel_show_title"><?php _e('Show Title');?></label> 
            </div>
        </div>
        <?php
        endif;
        ?>
        <div class="wlcms-input-group wlcms_welcome_dismissible wlcms_welcome_panel_dismissible1">
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" id="first_welcome_panel_dismissible" value="1" name="welcome_panel[0][dismissible]" <?php checked(wlcms_welcome_value(0, 'dismissible'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="first_welcome_panel_dismissible"></label><label class="toggle-label" for="first_welcome_panel_dismissible"><?php _e('Dismissible'); ?></label> 
            </div>
            <?php
            if( is_wlcms_super_admin() ):?>
            <div class="wlcms_welcome_dismissible_reset"><a href="<?php echo wlcms()->admin_url() ?>&wlcms-action=reset-welcome-dashboard&dashboard=0"><?php _e('Reset Dismissed Welcome Dashboard')?></a></div>
            <?php endif;?>
        </div>
    </div>
</div>

<!-- Second Panel -->
<?php
$welcome_panel_is_active = wlcms_welcome_value(1, 'is_active');
?>
<div class="wlcms-input-group toggle-group wlcms-dashboard" data-wlcms_dashboard="2">
    <div class="wlcms-input">
    <input  class="wlcms-toggle wlcms-toggle-light main-toggle" id="add_second_panel" data-revised="1" name="welcome_panel[1][is_active]" value="1" type="checkbox" <?php checked($welcome_panel_is_active, 1, true) ?>/>
    <label class="wlcms-toggle-btn" for="add_second_panel"></label><label class="toggle-label" for="add_second_panel"><?php _e('Add Second Panel', 'white-label-cms') ?></label> 
    </div>
    <div class="wlcms-help">
        <?php _e('Add a second custom panel to the Dashboard.', 'white-label-cms') ?>
    </div>
    <div class="sub-fields">
        <div class="wlcms-help"></div>
        <label><?php _e('Select the Roles the Welcome Panel Will Be Visible To', 'white-label-cms') ?></label>
        <div class="wlcms-input">
            <?php
            echo wlcms_select_roles(array('name' => 'welcome_panel[1][visible_to]', 'class' => 'wlcms_visible_to wlcms-select2'), wlcms_welcome_value(1, 'visible_to'));
            ?>
        </div>
        <div class="wlcms-help">
            <?php _e('Select the user roles this will be visible to.', 'white-label-cms') ?>
        </div>
    
        <?php
        $checked_welcome_type = wlcms_welcome_value(1, 'template_type');

        $is_basic = ( ! $checked_welcome_type ) || ($checked_welcome_type == 'html') ? true : false;

        ?>
        <div class="wlcms-input-group">
            <label><?php _e('Template', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input class="wlcms-toggle wlcms-toggle-light template_type template_type2" data-template_type="2" data-page_type="html" id="template_type_basic2" value="html" name="welcome_panel[1][template_type]" <?php checked($is_basic, true, true) ?> type="radio"/>
                <label class="wlcms-toggle-btn" for="template_type_basic2"></label><label class="toggle-label" for="template_type_basic2"><?php _e('Basic HTML');?></label> 
                <input class="wlcms-toggle wlcms-toggle-light template_type template_type2" data-template_type="2" data-page_type="elementor" id="template_type_elementor2"<?php echo !wlcms_is_elementor_active() ? ' disabled':''?> value="Elementor" name="welcome_panel[1][template_type]" <?php checked($checked_welcome_type, 'Elementor', true) ?> type="radio"/>
                <label class="wlcms-toggle-btn<?php echo !wlcms_is_elementor_active() ? ' disabled':''?>" for="template_type_elementor2"></label><label class="toggle-label<?php echo !wlcms_is_elementor_active() ? ' disabled':''?>" for="template_type_elementor2"><?php _e('Elementor');?></label> 
                <input class="wlcms-toggle wlcms-toggle-light template_type template_type2" data-template_type="2" data-page_type="beaver" id="template_type_beaver2"<?php echo !wlcms_is_beaver_builder_active() ? ' disabled':''?> value="Beaver Builder" name="welcome_panel[1][template_type]" <?php checked($checked_welcome_type, 'Beaver Builder', true) ?> type="radio"/>
                <label class="wlcms-toggle-btn<?php echo !wlcms_is_beaver_builder_active() ? ' disabled':''?>" for="template_type_beaver2"></label><label class="toggle-label<?php echo !wlcms_is_beaver_builder_active() ? ' disabled':''?>" for="template_type_beaver2"><?php _e('Beaver Builder Pro');?></label> 
            </div>
        </div>
        
        <div class="welcome-page2">
            <label><?php _e('Page Template', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <?php
                echo wlcms_select_pages(array('name' => 'welcome_panel[1][page_id_elementor]', 'class' => 'wlcms_visible_to wlcms-select2 elementor_page2'), wlcms_welcome_value(1, 'page_id_elementor'), $elementor_args);
                echo wlcms_select_pages(array('name' => 'welcome_panel[1][page_id_beaver]', 'class' => 'wlcms_visible_to wlcms-select2 beaver_page2'), wlcms_welcome_value(1, 'page_id_beaver'), $beaver_args);
                ?>
            </div>
        </div>
        <div class="welcome-basicHtml2">
            <label><?php _e('Title of Second Panel', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <input type="text" name="welcome_panel[1][title]" value="<?php echo esc_attr(wlcms_welcome_value(1, 'title')) ?>" />
            </div>
            <div class="wlcms-help">
                <?php _e('Title of the Second Panel', 'white-label-cms') ?>
            </div>
        </div>

        <div class="welcome-basicHtml2">
            <label><?php _e('Second Panel Description (HTML)', 'white-label-cms') ?></label>
            <div class="wlcms-input">
                <textarea class="textarea-full" name="welcome_panel[1][description]"><?php echo esc_html(wlcms_welcome_value(1, 'description')) ?></textarea>
            </div>
            <div class="wlcms-help"><?php _e('You can add any HTML to the second panel.', 'white-label-cms') ?></div>
        </div>

        <div class="welcome-basicHtml2">
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light welcome_panel_fullwidth" id="second_welcome_panel_fullwidth" value="1" name="welcome_panel[1][is_fullwidth]" <?php checked(wlcms_welcome_value(1, 'is_fullwidth'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="second_welcome_panel_fullwidth"></label><label class="toggle-label" for="second_welcome_panel_fullwidth"><?php _e('Make full-width');?></label> 
            </div>
            <div class="wlcms-help">
            </div>
        </div>

        <?php if( wlcms_has_pagebuilder() ):?>
        <div class="welcome-page2">
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" id="second_welcome_panel_show_title" value="1" name="welcome_panel[1][show_title]" <?php checked(wlcms_welcome_value(1 , 'show_title'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="second_welcome_panel_show_title"></label><label class="toggle-label" for="second_welcome_panel_show_title"><?php _e('Show Title');?></label> 
            </div>
        </div>
        <?php
        endif;
        ?>
        <div class="wlcms-input-group wlcms_welcome_dismissible wlcms_welcome_panel_dismissible2">
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" id="second_welcome_panel_dismissible" value="1" name="welcome_panel[1][dismissible]" <?php checked(wlcms_welcome_value(1, 'dismissible'), 1, true) ?> type="checkbox"/>
            <label class="wlcms-toggle-btn" for="second_welcome_panel_dismissible"></label><label class="toggle-label" for="second_welcome_panel_dismissible"><?php _e('Dismissible'); ?></label> 
            </div>
            <?php
            if( is_wlcms_super_admin() ):?>
            <div class="wlcms_welcome_dismissible_reset"><a href="<?php echo wlcms()->admin_url() ?>&wlcms-action=reset-welcome-dashboard&dashboard=1"><?php _e('Reset Dismissed Welcome Dashboard')?></a></div>
            <?php endif;?>
        </div>
    </div>
</div>