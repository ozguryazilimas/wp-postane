<?php

class WLCMS_Branding extends WLCMS_Previewable
{

    public function __construct()
    {
        //Check and set if it is a preview
        $this->check_preview();

        add_action('init', array($this, 'init'));
        add_filter('admin_title', array($this, 'admin_title'), 10, 2);
        add_action('admin_bar_menu', array($this, 'admin_bar_logo'));
        add_action('admin_bar_menu', array($this, 'admin_bar_howdy_text'));
        add_filter('admin_footer_text', array($this, 'admin_footer'), 2000);
        add_action('admin_menu', array($this, 'admin_menu'), 0);
        add_filter('admin_body_class', array($this, 'admin_body_class'), 12);
    }

    public function init()
    {
        if ($this->get_settings('hide_wp_version')) {
            add_filter('update_footer', '__return_false', 11);
        }

        if ($this->get_settings('hide_wordpress_logo_and_links')) {
            wlcms_set_hidden_css('#wp-admin-bar-wp-logo');
            wlcms_set_hidden_css('#wpadminbar .quicklinks li .blavatar');
        }

        // Setup css for the admin bar logo
        if ($this->get_settings('admin_bar_logo')){
            /**
             * Create css for admin bar custom logo
             */
            $css_args = array();

            $css_args['vertical-align'] = 'middle!important';


            //limit admin logo height
            $css_args['max-height'] = '20px!important';
            $css_args['margin'] = '0 auto';
            $css_args['vertical-align'] = 'middle';

            wlcms_set_css('.wlcms-admin-logo img', $css_args);

            wlcms_set_css(
                '.wlcms-admin-logo .ab-item, .wlcms-admin-logo a',
                array(
                    'line-height' => '28px!important',
                    'display' => 'flex',
                    'align-items' => 'center'
                )
            );
            
            wlcms_set_css('#footer-left img', array('vertical-align' => 'middle', 'max-height' => '50px', 'margin-right' => '5px'));
            wlcms_set_css('#footer-left a', array('text-decoration' => 'none'));
        }
    }

    public function admin_title($admin_title)
    {
        if ($custom_admin_title = $this->get_settings('custom_page_title')) {
            $admin_title = str_replace(
                "&#8212; WordPress",
                "&#8212; " . $custom_admin_title,
                $admin_title
            );
        }

        return $admin_title;
    }

    public function admin_body_class($classes)
    {
        $classes = trim($classes) . ' ' . (!is_wlcms_admin() ? 'not-' : '') . 'wlcms-admin ';

        return $classes;
    }

    public function admin_bar_logo($wp_admin_bar)
    {

        $admin_menu_bar_url = $this->get_settings('admin_bar_url');
        $admin_menu_bar_alt_text = $this->get_settings('admin_bar_alt_text');
        $admin_menu_bar_image = $this->get_settings('admin_bar_logo');

        if (!$admin_menu_bar_image) {
            return;
        }

        /**
         * Add custom logo to the admin bar menu
         */
        $args = array(
            'id' => 'wlcms-admin-logo',
            'href' => $admin_menu_bar_url,
            'title' => sprintf('<img src="%s" />', $admin_menu_bar_image),
            'meta' => array('class' => 'wlcms-admin-logo', 'title' => $admin_menu_bar_alt_text, 'target' => '_blank')
        );
        $wp_admin_bar->add_node($args);

    }

    public function sidebar_menu_url()
    {
        if ($this->get_settings('use_developer_side_menu_image')) {
            return $this->get_settings('developer_url');
        }

        $sidebar_url = $this->get_settings('side_menu_link_url');

        return $sidebar_url;
    }

    public function admin_menu()
    {
        global $menu;

        $sidebar_url = $this->get_settings('side_menu_link_url');
        $sidebar_text = $this->get_settings('side_menu_alt_text');
        $sidebar_image = $this->get_settings('side_menu_image');
        $collapsed_sidebar_image = $this->get_settings('collapsed_side_menu_image');


        if (!$sidebar_image) {
            return;
        }

        $logo = sprintf('<img src=\"%s\" title=\"%s\" class=\"large-side-bar-logo\" /><img src=\"%s\" alt=\"%s\" class=\"collapsed-side-bar-logo\" />', $sidebar_image, $sidebar_text, $collapsed_sidebar_image, $sidebar_text);

        $target = '_self';
        if($sidebar_url) {
            $domain = wp_parse_url($sidebar_url);
            if( isset($domain['host']) ){
                if (strpos($_SERVER['HTTP_HOST'], $domain['host']) === false) {
                    $target = '_blank';
                }
            }
        }

        if ($sidebar_url) {
            $logo = sprintf('<a href=\"%s\" title=\"%s\" target=\"%s\">%s</a>', $sidebar_url, $sidebar_text, $target, $logo);
        }
        wlcms_set_css('.collapsed-side-bar-logo', array('display' => 'none', 'padding' => '5px', 'max-width' => '36px'));
        wlcms_set_css('.folded .collapsed-side-bar-logo', array('display' => 'block', 'margin' => '0 auto', 'max-width' => '25px'));

        wlcms_set_css('.large-side-bar-logo', array('display' => 'block', 'max-width' => '150px', 'margin' => '0 auto', 'padding' => '5px'));
        wlcms_set_css('.folded .large-side-bar-logo', array('display' => 'none'));

        wlcms_add_js(sprintf('jQuery("#adminmenuwrap").prepend("<span class=\"wlcms-logo\">%s</span>");', $logo));

        wlcms()->Admin_Script()->additional_css('
        @media only screen and (max-width: 960px) {
            .wlcms-logo .large-side-bar-logo{
                display:none;
            }
            .wlcms-logo .collapsed-side-bar-logo{
                display:block;
            }
        }');
    }

    public function admin_bar_howdy_text($wp_admin_bar)
    {

        $user = wp_get_current_user();
        $user_id = $user->ID;

        if (0 == $user_id) return;

        $profile_url = get_edit_profile_url($user_id);
        $admin_bar_howdy_text = $this->get_settings('admin_bar_howdy_text');


        if (!$admin_bar_howdy_text) return;


        $avatar = get_avatar($user_id, 28);
        $howdy = sprintf(__('%1s %2$s'), $admin_bar_howdy_text, $user->display_name);
        $class = empty($avatar) ? '' : 'with-avatar';

        $wp_admin_bar->add_menu(
            array(
                'id' => 'my-account',
                'parent' => 'top-secondary',
                'title' => $howdy . $avatar,
                'href' => $profile_url,
                'meta' => array(
                    'class' => $class
                )
            )
        );

    }

    /**
     * Use custom footer
     *
     * @return mixed
     */
    public function admin_footer($original_text)
    {

        $footer_html = $this->get_settings('footer_html');
        $footer_url = $this->get_settings('footer_url');
        $footer_text = $this->get_settings('footer_text');
        $footer_image = $this->get_settings('footer_image');
        $developer_name = $this->get_settings('developer_name');

        if ($footer_html) {
            return $footer_html;

        }

        $footer_main_text = "";

        if ($footer_image) {
            $footer_main_text .= '<img src="' . $footer_image . '" /> ';
        }

        if ($footer_text) {
            $footer_main_text .= $footer_text;
        }

        if (empty($footer_main_text)) {
            return $original_text;
        }

        if (!$footer_url) {
            return $footer_main_text;
        }

        $footer_html = '<a href="' . esc_url($footer_url) . '" title="' . $developer_name . '" target="_blank">' . $footer_main_text . '</a>';

        return $footer_html;
    }
}