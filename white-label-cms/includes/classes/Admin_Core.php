<?php

class WLCMS_Admin_Core
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'add_option_menu'));
    }

    /**
     * Add WLCMS to setting menu
     *
     * @return void
     */
    public function add_option_menu()
    {
        $page = add_options_page(
            __('White Label CMS', 'white-label-cms'),		// Page title
            __('White Label CMS', 'white-label-cms'),		// Menu name
            'manage_options', 					// Permissions
            'wlcms-plugin.php',					// Menu slug
            array($this, 'view')                // Function callback
        );

        add_action('load-' . $page, array($this, 'load'));

    }

    /**
     * wlcms setting menu page is loaded
     *
     * @return void
     */
    public function load()
    {

        do_action("wlcms_load-page");

        // Check if initial setup
        if (!wlcms_field_setting('version') && !isset($_GET['view'])) {
            wp_redirect(wlcms()->admin_url('wizard'));
            exit;
        }

        // Register scripts
        add_action("admin_enqueue_scripts", array($this, 'enqueue_scripts'));

        //check store;
        $this->store();
    }

    public function enqueue_scripts()
    {

        $setting_js = 'js/admin-settings.js';

        wp_register_script(
            'wlcms-admin-settings',
            WLCMS_ASSETS_URL . $setting_js,
            array('jquery', 'select2', 'wp-color-picker', 'jquery-validate'),
            WLCMS_VERSION
        );

        $jquery_validate = 'js/jquery.validate.min.js';

        wp_register_script(
            'jquery-validate',
            WLCMS_ASSETS_URL . $jquery_validate,
            array('jquery'),
            WLCMS_VERSION
        );

        $ays_before_js = 'js/ays-beforeunload-shim.js';
        wp_register_script(
            'ays-beforeunload-shim',
            WLCMS_ASSETS_URL . $ays_before_js,
            array('jquery'),
            WLCMS_VERSION
        );

        $areyousure_js = 'js/jquery-areyousure.js';
        wp_register_script(
            'jquery-areyousure',
            WLCMS_ASSETS_URL . $areyousure_js,
            array('jquery'),
            WLCMS_VERSION
        );

        $setting_css = 'css/admin-settings.css';
        wp_register_style(
            'wlcms-admin-settings',
            WLCMS_ASSETS_URL . $setting_css,
            array('select2', 'wp-color-picker'),
            WLCMS_VERSION
        );

        wp_register_style('select2', WLCMS_ASSETS_URL . 'css/select2.min.css');
        wp_register_script('select2', WLCMS_ASSETS_URL . 'js/select2.min.js');

        wp_enqueue_script(array('select2', 'wp-color-picker', 'ays-beforeunload-shim', 'jquery-areyousure', 'wlcms-admin-settings'));
        wp_enqueue_style(array('select2', 'wp-color-picker', 'wlcms-admin-settings'));

        wp_localize_script(
            'wlcms-admin-settings',
            'wlcms_settings',
            array(
                'loginurl' => site_url("/wp-login.php"),
                'adminurl' => admin_url("index.php"),
                'wlcms_ajax_nonce' => wp_create_nonce("wlcms_ajax_nonce")
            )
        );
    }

    private function store()
    {
        do_action('wlcms_save_before_validation');

        if (!isset($_POST['wlcms-settings'])) {
            return;
        }

        if (isset($_POST['wlcms-action']) && $_POST['wlcms-action'] == 'reset') {
            return;
        }
        //  nonce checking
        if (!isset($_POST['wlcms-settings_nonce'])
            || !wp_verify_nonce($_POST['wlcms-settings_nonce'], 'wlcms-settings-action')) {

            print 'Sorry, your nonce did not verify.';
            exit;
        }

        wlcms()->Settings()->store();
        return;
    }

    public function view()
    {
        $wlcms = wlcms();
        $view = $wlcms->get_active_view();
        $wlcms->admin_view($view);
    }
}

