<?php

class WLCMS_Upgrades
{

    private $menu_class;
    private $legacy_menus = array();
    private $legacy_submenus = array();
    private $legacy_db_setting = array();
    private $settings;

    public function __construct()
    {
        add_action('admin_init', array($this, 'upgrader_process_complete'), 999999);
    }

    public function upgrader_process_complete()
    {
        global $wpdb;

        $this->settings = wlcms()->Settings();

        $legacy_version = get_option('wlcms_o_ver', false);

        if (!$legacy_version) {
            return;
        }

        $new_wlcms_options = get_option('wlcms_options', false);
        if ($legacy_version && $new_wlcms_options) {
            return;
        }

        $newdbsetting = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->options WHERE option_name = 'wlcms_options'");

        if ($legacy_version && $newdbsetting) {
            return;
        }

        $this->do_import();
    }

    public function do_import()
    {
        $this->get_settings();
        $this->perform();
    }

    private function get_settings()
    {
        global $wpdb;
        
        // Get all WLCMS vals from options table
        $results = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE 'wlcms_o_%'");

        // Are there any options to grab?
        if (!$results) return;

        // Loop through results and prep array.
        foreach ($results as $result) :
            if ($result->option_value == '') continue;

        $this->legacy_db_setting[$result->option_name] = $result->option_value;
        endforeach;
    }

    public function upgrade_reset()
    {
        $this->settings = wlcms()->Settings();
    }

    private function perform()
    {
        $admin_bar_menu = array();

        $this->upgrade_reset();

        $this->settings->reset();
        $this->settings->set('version', WLCMS_VERSION);

        foreach ($this->legacy_mapping() as $key => $setting_key) {

            if ($new_setting = $this->get_legacy_setting($setting_key)) {
                if ($new_setting == 'true')
                    $new_setting = 1;

                $this->settings->set($key, $new_setting);
            }

        }
        $this->settings->set('legacy_menu', '1');

        $this->menu_class = wlcms()->Admin_Menus();

        //Post
        $this->get_legacy_menu_settings('wlcms_o_hide_posts', 'edit.php');
        if (!$this->get_legacy_setting('wlcms_o_hide_posts')) {
            $admin_bar_menu[] = 'new-post';
        }

        //Media
        $this->get_legacy_menu_settings('wlcms_o_hide_media', 'upload.php');
        if (!$this->get_legacy_setting('wlcms_o_hide_media')) {
            $admin_bar_menu[] = 'new-media';
        }

        //Pages
        $this->get_legacy_menu_settings('wlcms_o_hide_pages', 'edit.php?post_type=page');
        if (!$this->get_legacy_setting('wlcms_o_hide_pages')) {
            $admin_bar_menu[] = 'new-page';
        }

        //Comments
        $this->get_legacy_menu_settings('wlcms_o_hide_comments', 'edit-comments.php');
        if (!$this->get_legacy_setting('wlcms_o_hide_comments')) {
            $admin_bar_menu[] = 'comments';
        }
        
        //User
        $this->get_legacy_menu_settings('wlcms_o_hide_profile', 'users.php');
        if (!$this->get_legacy_setting('wlcms_o_hide_profile')) {
            $admin_bar_menu[] = 'new-user';
        }

        //Tools
        $this->get_legacy_menu_settings('wlcms_o_hide_tools', 'tools.php');

        if (count($admin_bar_menu)) {
            $this->settings->set('admin_bar_menus', $admin_bar_menu);
        }

        $this->hide_sidebar_menu('plugins.php');
        $this->hide_sidebar_menu('options-general.php');

        //Appearance
        $this->get_legacy_appearance_menu_settings();
        $this->settings->set('admin_menus', array('main' => $this->legacy_menus, 'sub' => $this->legacy_submenus));

        //Set Admin users to be wlcms admin
        $adminusers = get_users('role=administrator');

        if (count($adminusers)) :

            $wlcms_admin = array();
        foreach ($adminusers as $user) :
            $wlcms_admin[] = $user->user_email;
        endforeach;

        $this->settings->set('wlcms_admin', $wlcms_admin);

        endif;
        
        //Welcome Dashboard
        $welcome = array();
        if ($this->get_legacy_setting('wlcms_o_show_welcome')) {
            $welcome[] = array(
                'is_active' => true,
                'template_type' => 'html',
                'show_title' => true,
                'is_fullwidth' => false,
                'visible_to' => $this->get_legacy_roles($this->get_legacy_setting('wlcms_o_welcome_visible_to')),
                'title' => $this->get_legacy_setting('wlcms_o_welcome_title'),
                'description' => $this->get_legacy_setting('wlcms_o_welcome_text'),

            );
        }

        //Second Panel
        if ($this->get_legacy_setting('wlcms_o_welcome_text1')) {
            $welcome[] = array(
                'is_active' => true,
                'template_type' => 'html',
                'show_title' => true,
                'is_fullwidth' => false,
                'visible_to' => $this->get_legacy_roles($this->get_legacy_setting('wlcms_o_welcome_visible_to1')),
                'title' => $this->get_legacy_setting('wlcms_o_welcome_title1'),
                'description' => $this->get_legacy_setting('wlcms_o_welcome_text1'),
            );
        }

        $this->settings->set('welcome_panel', $welcome);

        if ($this->get_legacy_setting('wlcms_o_login_custom_logo')) {
            $this->settings->set('logo_width', false);
            $this->settings->set('logo_height', false);
        }

        if ($this->get_legacy_setting('wlcms_o_loginbg_white')) {
            $this->settings->set('background_color', '#FFF');
        }

        //Delete all legacy options
        $this->delete_legacy_settings();

        //Save new settings
        $this->settings->save();

        $redirect_url = admin_url();
        if (current_user_can('manage_options')) {
            $redirect_url = wlcms()->admin_url();
        }

        wp_redirect($redirect_url);
        exit;
    }

    private function get_legacy_setting($key)
    {
        return isset($this->legacy_db_setting[$key]) ? $this->legacy_db_setting[$key] : false;
    }

    private function get_legacy_appearance_menu_settings()
    {
        $menus = array();
        $new_sub_menus = array();
        $get_submenu_placeholder = $this->menu_class->get_submenu_placeholder();
        $url = 'themes.php';
        $count_sub_menus = 0;
        
        /*
            'wlcms_o_hide_links', //////          
            'wlcms_o_subtemplate_hide_16', = Hide Header
            'wlcms_o_subtemplate_hide_15', = Hide Header
            'wlcms_o_subtemplate_hide_10', = Hide Menus
            'wlcms_o_subtemplate_hide_7', = Hide Widgets
            'wlcms_o_subtemplate_hide_6', = Hide Customize
            'wlcms_o_subtemplate_hide_5', = Hide Themes
         */

        $theme_subs = array(
            'wlcms_o_subtemplate_hide_16' => 'custom-header',
            'wlcms_o_subtemplate_hide_15' => 'customize-php038autofocus%5bcontrol%5dheader_image',
            'wlcms_o_subtemplate_hide_10' => 'nav-menus-php',
            'wlcms_o_subtemplate_hide_7' => 'widgets-php',
            'wlcms_o_subtemplate_hide_6' => 'customize-php',
            'wlcms_o_subtemplate_hide_5' => 'themes-php'
        );

        if ($this->get_legacy_setting('wlcms_o_editor_template_access') == 0) {
            $theme_subs['wlcms_o_subtemplate_hide_theme-php'] = 'themes-php';

            foreach ($theme_subs as $theme_sub_key => $theme_sub) {
                $this->legacy_submenus[] = $url . $get_submenu_placeholder . $theme_sub;
                $count_sub_menus++;
            }

            $this->legacy_menus[] = $url;

            return;
        }

        if ($this->get_legacy_setting('wlcms_o_editor_template_access') == 1) {

            $submenus = $this->menu_class->get_new_submenus($url);

            if ($submenus) {

                $count_sub_menus = 0;

                foreach ($theme_subs as $theme_sub_key => $theme_sub) {
                    if ($this->get_legacy_setting($theme_sub_key)) {
                        $this->legacy_submenus[] = $url . $get_submenu_placeholder . $theme_sub;
                        $count_sub_menus++;
                    }
                }

                if ($count_sub_menus == count($submenus['submenus'])) {
                    $this->legacy_menus[] = $url;
                }
            }
        }
    }

    private function get_legacy_menu_settings($option = "", $url = "")
    {
        if (!$this->get_legacy_setting($option)) {
            return;
        }

        $this->hide_sidebar_menu($url);
    }

    private function hide_sidebar_menu($url)
    {

        $this->legacy_menus[] = $url;

        $submenus = $this->menu_class->get_new_submenus($url);

        if (!$submenus) {
            return;
        }

        foreach ($submenus['submenus'] as $submenu) {
            $this->legacy_submenus[] = $submenu['slug'];
        }
    }

    private function get_legacy_roles($key)
    {
        $roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        $allowed_roles = array();
        foreach ($roles as $role) {

            $allowed_roles[] = $role;

            if ($key == $role) {
                break;
            }
        }

        return $allowed_roles;
    }

    private function delete_legacy_settings()
    {
        global $wpdb;

        delete_option('wlcms_o_ver');
        $wpdb->get_results("DELETE FROM $wpdb->options WHERE option_name = 'wlcms_o_ver'");
    }

    private function legacy_settings()
    {
        return array_flip($this->legacy_mapping());
    }

    public function legacy_mapping()
    {
        return array(
            'developer_name' => 'wlcms_o_developer_name',
            'developer_url' => 'wlcms_o_developer_url',
            'developer_icon' => 'wlcms_o_adminbar_custom_logo',
            'admin_bar_alt_text' => 'wlcms_o_developer_name',
            'admin_bar_url' => 'wlcms_o_developer_url',
            'hide_wordpress_logo_and_links' => 'wlcms_o_hide_wp_adminbar',
            'hide_wp_version' => 'wlcms_o_hide_wpversion',
            'custom_page_title' => 'wlcms_o_admin_page_title',
            'admin_bar_logo' => 'wlcms_o_adminbar_custom_logo',
            'footer_image' => 'wlcms_o_footer_custom_logo',
            'footer_text' => 'wlcms_o_developer_name',
            'footer_url' => 'wlcms_o_developer_url',
            'login_logo' => 'wlcms_o_login_custom_logo',
            'retina_login_logo' => 'wlcms_o_login_custom_logo',
            'background_color' => 'wlcms_o_loginbg_white',
            'login_custom_css' => 'wlcms_o_login_bg_css',
            'dashboard_icon' => 'wlcms_o_header_custom_logo',
            'dashboard_title' => 'wlcms_o_dashboard_override',
            'hide_all_dashboard_panels' => 'wlcms_o_dashboard_others',
            'hide_at_a_glance' => 'wlcms_o_dashboard_remove_right_now',
            'hide_activities' => 'wlcms_o_dashboard_remove_activity_panel',
            'hide_recent_comments' => 'wlcms_o_dashboard_remove_recent_comments',
            'remove_empty_dash_panel' => 'wlcms_o_dashboard_border',
            'own_welcome_panel' => 'wlcms_o_show_welcome',
            'own_welcome_panel_visible_to' => 'wlcms_o_welcome_visible_to',
            'own_welcome_panel_title' => 'wlcms_o_welcome_title',
            'welcome_panel_description' => 'wlcms_o_welcome_text',
            'second_panel_title' => 'wlcms_o_welcome_title1',
            'second_panel_visible_to' => 'wlcms_o_welcome_visible_to1',
            'second_panel_description' => 'wlcms_o_welcome_text1',
            'add_own_rss_panel' => 'wlcms_o_show_rss_widget',
            'rss_feed_number_of_item' => 'wlcms_o_rss_num_items',
            'show_post_content' => 'wlcms_o_rss_show_intro',
            'rss_introduction' => 'wlcms_o_rss_intro_html',
            'rss_feed_address' => 'wlcms_o_rss_value',
            'rss_logo' => 'wlcms_o_rss_logo',
            'rss_title' => 'wlcms_o_rss_title',
            'hide_help_box' => 'wlcms_o_dashboard_remove_help_box',
            'hide_screen_options' => 'wlcms_o_dashboard_remove_screen_options',
            'hide_nag_messages' => 'wlcms_o_dashboard_remove_nag_update',
            'settings_custom_css_admin' => 'wlcms_o_custom_css',
            'settings_custom_css_url' => 'wlcms_o_welcome_stylesheet'
        );
    }
}
