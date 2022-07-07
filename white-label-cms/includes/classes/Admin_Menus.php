<?php

class WLCMS_Admin_Menus
{

    private $admin_menus;
    private $admin_bar_menus = array();
    private $is_wlcms_admin = false;
    private $admin_bar_menu_setting = false;
    private $submenu_placeholder = '_wlcms_';
    private $wlcms_admin_bar_menus_option = 'wlcms_admin_bar_menus';
    private $hide_woo_home = false;

    public function __construct()
    {
        add_action('init', array($this, 'set_wlcms_admin'), 10);
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wlcms_save_addtional_settings', array($this, 'save'), 12, 1);
        add_action('admin_menu', array($this, 'rebuild_user_admin_menu'), 9999999); // rebuild sidebar menu
        add_action('wp_before_admin_bar_render', array($this, 'init_admin_bar_menu'), 9999999); // setup admin bar menu
    }

    public function admin_init()
    {
        $this->set_admin_bar_menu_setting();
        $this->compile_menus();
    }

    public function set_wlcms_admin()
    {
        //Remove actions from preview mode
        if (defined('DOING_AJAX') && DOING_AJAX) {
            remove_action('admin_init', array($this, 'admin_init'));
            remove_action('admin_init', array($this, 'rebuild_user_admin_menu'), 9999999);
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $wlcms_admin = wlcms_field_setting('wlcms_admin');

        if (!$wlcms_admin) {
            return;
        }

        //Check if the current user is editor and with legacy menu
        if ($this->is_legacy_menu_role()) {
            return;
        }

        $current_user = wp_get_current_user();

        $this->is_wlcms_admin = in_array($current_user->ID, $wlcms_admin);
    }

    public function is_legacy_menu_role()
    {

        if (!wlcms_field_setting('legacy_menu')) {
            return false;
        }

        $user = wp_get_current_user();
        $role = ( array )$user->roles;

        if (!in_array('editor', $role, true)) {
            return false;
        }

        return true;
    }
    /**
     * check current user is wlcms admin or super admin
     *
     * @return boolean
     */
    public function has_visible_roles()
    {
        if (is_multisite() && is_super_admin()) {
            return true;
        }

        return $this->is_wlcms_admin();
    }

    public function enable_admin_menu()
    {
        return wlcms_field_setting('enable_wlcms_admin');
    }

    /**
     * check current user is wlcms admin 
     * 
     */
    public function is_wlcms_admin()
    {
        return $this->is_wlcms_admin;
    }

    public function save($settings)
    {

        if (!isset($_POST['enable_wlcms_admin'])) {
            $settings->remove('admin_menus');
            $settings->remove('admin_bar_menus');
            return;
        }

        $this->save_sidemenu($settings);
        $this->save_admin_bar_menu($settings);

        if (!isset($_POST['remove_legacy_menu'])) {
            return;
        }

        $settings->remove('legacy_menu');
    }

    private function save_sidemenu($settings)
    {
        $menus = $this->get_admin_menus();
        $db_menu_main = array();
        $db_menu = array();

        // No menu selected
        if (!isset($_POST['admin_menus'])) {
            $settings->remove('admin_menus');
            return $settings;
        }

        $post_main_menu = isset($_POST['admin_menus']['main']) ? $_POST['admin_menus']['main'] : array();
        $post_sub_menu = isset($_POST['admin_menus']['sub']) ? $_POST['admin_menus']['sub'] : array();

        $sidebar_url = sanitize_title(wlcms()->Branding()->sidebar_menu_url());

        if (is_array($menus) && $menus > 0) :
            foreach ($menus as $main_key => $main_menu) {

            if ($main_key == $sidebar_url) {
                continue;
            }

            if (!in_array($main_key, $post_main_menu)) {
                $db_menu['main'][] = $main_key;
            }

            if (isset($main_menu['submenus']) && is_array($main_menu['submenus']) && count($main_menu['submenus'])) {
                foreach ($main_menu['submenus'] as $sub_key => $submenu) {
                    if ($sub_key == $sidebar_url) {
                        continue;
                    }
                    $submenu_value = $submenu['slug'];

                    if (!in_array($submenu_value, $post_sub_menu)) {
                        $db_menu['sub'][] = $submenu_value;
                    }
                }
            }
        }
        endif;

        if (count($db_menu)) {
            $settings->set('admin_menus', $db_menu);
        } else {
            $settings->remove('admin_menus');
        }

        return $settings;
    }

    private function save_admin_bar_menu($settings)
    {
        $menus = get_option($this->wlcms_admin_bar_menus_option, array());

        // No menu selected
        if (!isset($_POST['admin_bar_menus'])) {
            $settings->remove('admin_bar_menus');
            return;
        }

        $post_menu = isset($_POST['admin_bar_menus']) ? $_POST['admin_bar_menus'] : array();

        $db_menu = array();
        if (is_array($menus) && $menus > 0) :
            foreach ($menus as $menu_key => $menu) {

            if (!in_array($menu_key, $post_menu)) {
                $db_menu[] = $menu_key;
            }
        }
        endif;
        if (count($db_menu)) {
            $settings->set('admin_bar_menus', $db_menu);
        }

    }

    /**
     * Re-organize sidebar menus 
     * Combine menu and submenu in single array
     *
     * @return void
     */
    public function compile_menus()
    {
        global $menu, $submenu;


        $output = array();

        $sidebar_url = wlcms()->Branding()->sidebar_menu_url();

        if (is_array($menu) && count($menu) > 0) {
            foreach ($menu as $menu_item) {
                // some menu items are seperators, skip them
                if ($menu_item[0] == '') {
                    continue;
                }

                if ($menu_item[2] == $sidebar_url) {
                    continue;
                }

                $menu_name = preg_replace('#(<span.*?>).*?(</span>)#', '', $menu_item[0]);
                $menu_key = $menu_item[2];
                $output[$menu_key] = array(
                    'name' => $menu_name,
                    'slug' => $menu_item[2],
                    'submenus' => array()
                );
            }
        }
        if (is_array($submenu) && count($submenu) > 0) :

            foreach ($submenu as $key => $submenu_item) {

            $mainmenu_key = $key;

                // If a submenu does not have a valid parent, skip
            if (!isset($output[$mainmenu_key])) {
                continue;
            }

            foreach ($submenu_item as $sm_info) {
                $submenu_item = remove_query_arg('return', $sm_info[2]);
                $submenu_key = sanitize_title($submenu_item);
                $menu_name = preg_replace('#(<span.*?>).*?(</span>)#', '', $sm_info[0]);

                $slug = $mainmenu_key . $this->get_submenu_placeholder() . $submenu_key;
                $output[$key]['submenus'][$submenu_key] = array(
                    'name' => $menu_name,
                    'slug' => $slug
                );
            }

        }
        endif;

        $this->admin_menus = $output;

    }

    /**
     * Sibar Admin menu getter
     * 
     * @return array
     */
    public function get_admin_menus()
    {
        return $this->admin_menus;
    }

    /**
     * remove sidebar menus that enable by wlcms
     *
     * @return void
     */
    public function rebuild_user_admin_menu()
    {
        global $submenu;

        if (!$this->enable_admin_menu() && !$this->is_legacy_menu_role()) {
            return;
        }

        if ($this->has_visible_roles()) {
            return;
        }

        $setting_admin_menus = wlcms_field_setting('admin_menus');
        if (isset($setting_admin_menus['main']) && is_array($setting_admin_menus['main'])) {

            foreach ($setting_admin_menus['main'] as $menu_item) {
                $this->remove_menu_page($menu_item);
            }
        }

        if (isset($setting_admin_menus['sub']) && is_array($setting_admin_menus['sub'])) {
            foreach ($setting_admin_menus['sub'] as $submenu_item) {
                $submenu_list = explode($this->get_submenu_placeholder(), $submenu_item);
                $main_menu = $submenu_list[0];
                $main_submenu = $submenu_list[1];
                $this->remove_submenu_page($main_menu, $main_submenu);
            }
            
            $this->fix_woocommerce();
            $this->fix_yoast($setting_admin_menus['sub']);
        }
    }

    /**
     * Get sidebar sub-menu
     * 
     * @return array
     */
    public function get_new_submenus($key)
    {
        return isset($this->admin_menus[$key]) ? $this->admin_menus[$key] : false;

    }
    /**
     * Remove a top-level admin menu.
     *
     * @global array $menu
     *
     * @param string $menu_slug The slug of the menu.
     * @return array|bool The removed menu on success, false if not found.
     */
    public function remove_menu_page($menu_slug)
    {
        global $menu;

        if (!is_array($menu)) {
            return false;
        }

        foreach ($menu as $i => $item) {

            $menu_item = remove_query_arg('return', $item[2]);
            $menu_item = urldecode($menu_item);

            if ($menu_slug == $menu_item) {
                unset($menu[$i]);
                return $item;
            }
        }
        return false;
    }

    /**
     * Remove an admin submenu.
     *
     * @global array $submenu
     *
     * @param string $menu_slug    The slug for the parent menu.
     * @param string $submenu_slug The slug of the submenu.
     * @return array|bool The removed submenu on success, false if not found.
     */
    function remove_submenu_page($menu_slug, $submenu_slug)
    {
        global $submenu;

        if (!isset($submenu[$menu_slug]) || !is_array($submenu[$menu_slug]))
            return false;

        foreach ($submenu[$menu_slug] as $i => $item) {
            $submenu_item = remove_query_arg('return', $item[2]);
            $submenu_item = sanitize_title($submenu_item);

            //Patch whitelist
            if( in_array($submenu_slug, array('wc-admin'))) {
                $this->hide_woo_home = true;
                continue;
            }

            if ($submenu_slug == $submenu_item) {
                unset($submenu[$menu_slug][$i]);
                return $item;
            }
        }

        return false;
    }

    public function fix_woocommerce()
    {
        global $submenu;
        
        
        if (!isset($submenu) || !$this->hide_woo_home) {
            return false;
        }

        if (!isset($submenu['woocommerce'])) {
            return false;
        }

        $home = $submenu['woocommerce'][0];
        $home[1] = 'manage_woocommerce';
        unset($submenu['woocommerce'][0]);
        $count = count($submenu['woocommerce']);
        
        if($count == 0) {
            wlcms_set_hidden_css('li.toplevel_page_woocommerce');
        }
        

        $submenu['woocommerce'] = array_values($submenu['woocommerce']);
        $home[0] = '';
        $home[3] = '';
        $submenu['woocommerce'][$count] = $home;
        
        wlcms_set_hidden_css('li.toplevel_page_woocommerce li > a:empty');
    }
    
    public function fix_yoast($sub)
    {
        global $menu;

        if(array_search('wpseo_dashboard_wlcms_wpseo_workouts', $sub) === false) {
            return;
        }
        
        foreach($menu as $key => $item) {
            if($item[2] == 'wpseo_workouts') {
                unset($menu[$key]);
                break;
            }
        }
    }

    public function init_admin_bar_menu()
    {
        $this->set_admin_bar_menu();
        $this->buid_new_admin_bar_menu();
    }

    /**
     * Prepare white label cms admin bar menu and build in multidimentional array
     *
     * @return void
     */
    private function set_admin_bar_menu()
    {
        global $wp_admin_bar;

        $nodes = $wp_admin_bar->get_nodes();

        if (!$nodes || !is_array($nodes)) {
            return;
        }

        // Admin menus is not set from action hoo admin_init
        // Use for saving menus
        if (is_admin()) {
            $screen = get_current_screen();
            if ($screen && $screen->id == WLCMS_SCREEN_ID) {

                update_option($this->wlcms_admin_bar_menus_option, $nodes, false);
            }
        }

        $wlcms_admin_bar = $this->_createMenuTree($nodes);

        $this->admin_bar_menus = $wlcms_admin_bar;
    }

    /**
     * Add all main menu
     *
     * @param object $flat
     * @param integer $root
     * @return array
     */
    private function _createMenuTree($flat, $root = 0)
    {
        $parents = array();
        $sub_root = array();
        if (is_array($flat) && $flat > 0) :
            foreach ($flat as $a) {
            if ($this->excluded_admin_menu($a->id)) continue;
            $menu_parent = ($a->parent == '') ? false : $a->parent; 
            $parents[$menu_parent][] = $a;
        }
        $sub_root = isset($parents[$root]) ? $parents[$root] : array();
        endif;

        return $this->_createMenuBranch($parents, $sub_root);
    }

    /**
     * Add all sub menu to each main menu
     *
     * @param array $parents main menus
     * @param object $children
     * @return array
     */
    private function _createMenuBranch(&$parents, $children)
    {
        $tree = array();
        if (is_array($children) && $children > 0) :
            foreach ($children as $child) {

            if (isset($parents[$child->id])) {

                $child->sub = $this->_createMenuBranch($parents, $parents[$child->id]);

            }

            $tree[] = $child;

        }
        endif;

        return $tree;
    }

    /**
     * check if the menu is listed from excluded items
     *
     * @param string $menu
     * @return boolean
     */
    private function excluded_admin_menu($menu = '')
    {
        $exclude = array('menu-toggle', 'wlcms-admin-logo', 'wp-logo');

        return in_array($menu, $exclude);
    }

    /**
     * Create admin bar menu base from the wlcms settings
     *
     * @return void
     */
    private function buid_new_admin_bar_menu()
    {
        global $wp_admin_bar;

        if ($this->has_visible_roles()) {
            return;
        }

        $admin_bar_menu = wlcms_field_setting('admin_bar_menus');

        if (!$admin_bar_menu || ($admin_bar_menu && !is_array($admin_bar_menu))) {
            return;
        }

        if (!count($admin_bar_menu)) {
            return;
        }


        $nodes = $wp_admin_bar->get_nodes();

        if (is_array($nodes) && count($nodes) > 0) :
            foreach ($nodes as $menu) {

            if ($this->excluded_admin_menu($menu->id)) continue;

            if (in_array($menu->id, $admin_bar_menu)) {
                $wp_admin_bar->remove_menu($menu->id);
            }
        }
        endif;
    }

    /**
     * Getter of admin bar menus list
     *
     * @return void
     */
    public function get_admin_bar_menus()
    {
        return $this->admin_bar_menus;
    }

    /**
     * Change menu Title for wlcms settings
     *
     * @param object $menu
     * @return string
     */
    public function get_menu_title($menu)
    {
        if (!isset($menu->title)) {
            return '';
        }

        $title = trim(wp_strip_all_tags($menu->title));

        return ((!$title || empty($title)) ? '-' : $title) . ' <small>(' . $menu->id . ')</small>';
    }

    public function set_admin_bar_menu_setting()
    {
        $this->admin_bar_menu_setting = wlcms_field_setting('admin_bar_menus');

    }

    public function get_admin_bar_menu_setting()
    {
        return $this->admin_bar_menu_setting;
    }

    public function get_admin_bar_menu_html($menu, $depth = 0)
    {
        $out = '';

        if (!is_array($menu) && $depth > 0) {
            return $out;
        }

        $ul_class_wrapper = '';
        if ($depth > 0) {
            $out .= '<a href="javascript:void(0)" class="wlcms-toggle-arrow"></a>';
            $ul_class_wrapper = ' class="sub_menu_wrapper"';
        }
        $out .= '<ul' . $ul_class_wrapper . '>';
        $out .= '<input type="hidden" value="top-secondary" name="admin_bar_menus[]">
        <input type="hidden" value="my-account" name="admin_bar_menus[]">
        <input type="hidden" value="user-actions" name="admin_bar_menus[]">
        <input type="hidden" value="logout" name="admin_bar_menus[]">
        ';

        foreach ($menu as $menu_props) {

            $menu_key = $menu_props->id;
            $checked_sub = '';
            $admin_bar_menu_setting = $this->get_admin_bar_menu_setting();
            $checked_sub = ' checked="checked"';
            if ($admin_bar_menu_setting && in_array($menu_key, $admin_bar_menu_setting)) {
                $checked_sub = '';
            }

            $disabled = '';
            if (in_array($menu_key, array('top-secondary', 'my-account', 'user-actions', 'logout'))) {
                $disabled = ' disabled';
            }

            $sub_html = (isset($menu_props->sub)) ? $this->get_admin_bar_menu_html($menu_props->sub, ($depth + 1)) : '';
            $out .= sprintf(
                '
                    <li><input class="wlcms-toggle wlcms-toggle-light" id="admin_bar_menus_%1$s"%5$s name="admin_bar_menus[]" value="%1$s" type="checkbox" %2$s/>
                        <label class="wlcms-toggle-btn%5$s" for="admin_bar_menus_%1$s"></label><label class="toggle-label" for="admin_bar_menus_%1$s">%3$s</label> 
                       %4$s
                    </li>
                ',
                $menu_key,
                $checked_sub,
                $this->get_menu_title($menu_props),
                $sub_html,
                $disabled
            );
        }

        $out .= '</ul>';
        return $out;
    }

    public function get_submenu_placeholder()
    {
        return $this->submenu_placeholder;
    }
}