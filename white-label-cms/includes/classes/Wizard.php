<?php

class WLCMS_Wizard extends WLCMS_Previewable
{
    public function __construct()
    {
        //Check and set if it is a preview
        $this->check_preview();

        add_filter('wlcms_setting_fields', array($this, 'setting_fields'), 10, 1);

       // add_action('wlcms_before_saving', array($this, 'store'));
        add_action('wp_ajax_wlcms_save_dashboard_preview_settings', array($this, 'store_preview'));
        add_action('wlcms_save_addtional_settings', array($this, 'before_save'), 10, 2);
        add_action('wlcms_before_save_preview', array($this, 'before_save'), 10, 2);

    }

    public function before_save($settings, $placeholder)
    {
        if (!isset($_POST['wlcms_wizzard'])) {
            return $settings;
        }
        if (isset($_POST['wizard_developer_name'])) {
            $developer_name = wlcms_kses($_POST['wizard_developer_name']);
            $settings->set($placeholder . 'developer_name', $developer_name);
            $settings->set($placeholder . 'admin_bar_alt_text', $developer_name);
            $settings->set($placeholder . 'side_menu_alt_text', $developer_name);
            $settings->set($placeholder . 'rss_title', $developer_name);
        }

        if (isset($_POST['wizard_developer_url'])) {
            $developer_url = wlcms_kses($_POST['wizard_developer_url']);
            $settings->set($placeholder . 'admin_bar_url', $developer_url);
            $settings->set($placeholder . 'side_menu_link_url', $developer_url);
            $settings->set($placeholder . 'footer_url', $developer_url);
        }

        if (isset($_POST['client_business_name'])) {
            $custom_page_title = wlcms_kses($_POST['client_business_name']);
            $settings->set($placeholder . 'custom_page_title', $custom_page_title);
            $settings->set($placeholder . 'dashboard_title', $custom_page_title);
        }

        if (isset($_POST['rss_feed_address']) && !empty($_POST['rss_feed_address'])) {
            $settings->set($placeholder . 'add_own_rss_panel', true);
        }

        $settings->set($placeholder . 'welcome_panel', array(
            array(
                'is_active' => false,
                'show_title' => false,
                'template_type' => 'html',
                'visible_to' => array('administrator', 'editor', 'author', 'contributor', 'subscriber'),
            ), array(
                'is_active' => false,
                'show_title' => false,
                'template_type' => 'html',
                'visible_to' => array('administrator', 'editor', 'author', 'contributor', 'subscriber'),
            )
        ));

        return $settings;
    }

    public function settings()
    {
        $settings = $this->wizard_settings();
        $wizard_settings = array(
            'version' => WLCMS_VERSION,
            'use_developer_side_menu_image' => false,
            'developer_icon_admin_bar' => false,
            'developer_branding_footer' => false,
            'hide_wordpress_logo_and_links' => true,
            'hide_at_a_glance' => true,
            'hide_activities' => true,
            'hide_recent_comments' => true,
            'hide_quick_press' => true,
            'hide_news_and_events' => true,
            'remove_empty_dash_panel' => true,
            'hide_wp_version' => true,
            'rss_title' => '&nbsp;'
        );

        return array_merge($wizard_settings, $settings);
    }

    public function wizard_settings()
    {
        return array(
            'developer_name' => '',
            'footer_text' => '',
            'rss_feed_address' => '',
            'custom_page_title' => '',
        );
    }

    public function setting_fields($settings)
    {
        return array_merge($settings, $this->wizard_settings());
    }
}