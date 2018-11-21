<?php

class WLCMS_Admin_Settings
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'), 9999);
        add_action('admin_init', array($this, 'init'), 9999);
        add_filter('mce_css', array($this, 'custom_editor_stylesheet'));
        add_filter('contextual_help', array($this, 'remove_help_tabs'), 999, 3);
        add_action('after_setup_theme', array($this, 'remove_nag_messages'));
        add_action('init', array($this, 'remove_admin_bar'));
    }

    public function init()
    {
        $this->set_admin_css();
        $this->hide_screen_options();
    }

    public function remove_admin_bar()
    {
        
        
        if( ! wlcms_field_setting('hide_admin_bar_all') )
        {
            return;
        }

        return $this->disable_admin_bar_menu();

    }

    private function disable_admin_bar_menu()
    {
        add_filter('show_admin_bar', '__return_false');

    }

    public function admin_menu()
    {
        if (!is_admin()) {
            return;
        }
    }

    public function hide_screen_options()
    {
        if (wlcms_field_setting('hide_screen_options')) {
            add_filter('screen_options_show_screen', '__return_false');
        }
    }

    public function remove_help_tabs($help, $screen_id, $screen)
    {
        if (wlcms_field_setting('hide_help_box')) {
            $screen->remove_help_tabs();
        }
        return $help;
    }

    private function set_admin_css()
    {
        if (!$admin_style = wlcms_field_setting('settings_custom_css_admin')) {
            return;
        }

        wlcms()->Admin_Script()->appendCss($admin_style);

    }

    public function custom_editor_stylesheet($mce_css)
    {
        $mce_style = wlcms_field_setting('settings_custom_css_url');
        if (!$mce_style) {
            return $mce_css;
        }

        if (filter_var($mce_style, FILTER_VALIDATE_URL) === false) {
            $mce_style = get_stylesheet_directory_uri() . '/' . $mce_style;
        }

        $mce_css .= ',' . $mce_style;

        return $mce_css;
    }

    public function remove_nag_messages()
    {
        if (wlcms()->Admin_menus()->has_visible_roles()) {
            return;
        }

        if (!wlcms_field_setting('hide_nag_messages')) {
            return;
        }

        add_action('init', array($this, 'remove_core_updates'), 2);
        add_filter('pre_option_update_core', '__return_null');
        add_filter('pre_site_transient_update_core', '__return_null');
        remove_action('load-update-core.php', 'wp_update_plugins');
        add_filter('pre_site_transient_update_plugins', '__return_null');
        add_filter('pre_site_transient_update_core', array($this, 'remove_core_updates'));
        add_filter('pre_site_transient_update_plugins', array($this, 'remove_core_updates'));
        add_filter('pre_site_transient_update_themes', array($this, 'remove_core_updates'));
    }

    public function remove_core_updates()
    {
        global $wp_version;
        return (object)array('last_checked' => time(), 'version_checked' => $wp_version, );
    }

    public function wp_version_check()
    {
        remove_action('init', 'wp_version_check');
    }
}