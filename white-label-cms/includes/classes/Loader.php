<?php

class WLCMS_Loader
{
    const CLASS_DIR = 'includes/classes/';
    const VIEW_DIR = 'view/';

    private $admin_core_class;
    private $wizard_class;
    private $admin_menus_class;
    private $settings_class;
    private $login_class;
    private $branding_class;
    private $admin_script_class;
    private $admin_dashboard_class;
    private $admin_settings_class;
    private $upgrade_class;

    private $admin_url;


    private static $_instance; //The single instance


    function __construct()
    {
        $this->loadClasses();
    }

    public static function getInstance()
    {
        if (!self::$_instance) { // If no instance then make one
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function loadClasses()
    {
        $this->require_class('Messages');
        $this->require_class('Previewable');

        $this->require_class('Admin_Core');
        $this->admin_core_class = new WLCMS_Admin_Core();

        $this->require_class('Wizard');
        $this->wizard_class = new WLCMS_Wizard();

        $this->require_class('Admin_Script');
        $this->admin_script_class = new WLCMS_Admin_Script();

        $this->require_class('Settings');
        $this->settings_class = new WLCMS_Settings();

        $this->require_class('Upgrade');
        $this->upgrade_class = new WLCMS_Upgrades();

        $this->require_class('Admin_Menus');
        $this->admin_menus_class = new WLCMS_Admin_Menus();

        $this->require_class('Login');
        $this->login_class = new WLCMS_Login();

        $this->require_class('Branding');
        $this->branding_class = new WLCMS_Branding();

        $this->require_class('Admin_Dashboard');
        $this->admin_dashboard_class = new WLCMS_Admin_Dashboard();

        $this->require_class('Admin_Settings');
        $this->admin_settings_class = new WLCMS_Admin_Settings();

    }

    public function Admin_Core()
    {
        return $this->admin_core_class;
    }

    public function Wizard()
    {
        return $this->wizard_class;
    }

    public function Settings()
    {
        return $this->settings_class;
    }

    public function Upgrade()
    {
        return $this->upgrade_class;
    }

    public function Admin_Dashboard()
    {
        return $this->admin_dashboard_class;
    }

    public function Admin_Menus()
    {
        return $this->admin_menus_class;
    }

    public function Login()
    {
        return $this->login_class;
    }

    public function Branding()
    {
        return $this->branding_class;
    }

    public function Admin_Script()
    {
        return $this->admin_script_class;
    }

    public function Admin_Settings()
    {
        return $this->admin_settings_class;
    }

    public function require_class($file = "")
    {
        return $this->required(self::CLASS_DIR . $file);
    }

    public function admin_url($view = 'settings')
    {
        return admin_url('options-general.php?page=wlcms-plugin.php&view=' . $view);
    }

    public function required($file = "")
    {
        $dir = WLCMS_DIR;

        if (empty($dir) || !is_dir($dir)) {
            return false;
        }

        $file = path_join($dir, $file . '.php');

        if (!file_exists($file)) {
            return false;
        }

        require_once $file;
    }

    public function get_view($file = "")
    {
        $this->required(self::VIEW_DIR . $file);
    }

    public function admin_view($file = "")
    {
        $this->get_view('admin/' . $file);
    }

    public function get_active_view()
    {
        $default = 'settings';

        if (!isset($_GET['view'])) {
            return $default;
        }

        $available = array('wizard', 'settings');
        $view = wp_filter_kses($_GET['view']);

        return (in_array($view, $available)) ? $view : $default;

    }
}
