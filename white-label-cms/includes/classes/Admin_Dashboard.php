<?php

class WLCMS_Admin_Dashboard extends WLCMS_Previewable
{

    private $is_dashboard_all_hidden;
    private $is_fullwidth;
    private $welcome_title;
    private $welcome_show_title;
    private $welcome_description;
    private $welcome_content;

    public function __construct()
    {
        //Check and set if it is a preview
        $this->check_preview();

        add_action('wp_dashboard_setup', array($this, 'dashboard_setup'), 999);
        add_action("wp_ajax_hide_vum_dashboard", array($this, "hide_vum_dashboard"));
        add_action("admin_init", array($this, "reset_welcome_dashboard"));
    }

    public function dashboard_setup()
    {
        $this->reset_dashboard_style();
        $this->add_own_rss_panel();
        $this->dashboard_title();
        $this->set_welcome_metabox();

        //Old version setting 2.0.2
        if (!wlcms_field_setting('dashboard_role_stat')) {
            if (is_wlcms_admin()) return;
        } else {
            $dashboard_widgets_visibility_roles = wlcms_field_setting('dashboard_widgets_visibility_roles');
            if( ! $dashboard_widgets_visibility_roles ) return;
            if (!$this->has_current_user_role($dashboard_widgets_visibility_roles))
                return;
        }

        $this->set_dashboard_all_hidden();
        $this->remove_metaboxes();
        $this->remove_dashed_border();
    }

    private function reset_dashboard_style() {

        wlcms_set_css(
            '.wlcms-welcome-panel .elementor div,
            .wlcms-welcome-panel .elementor h1,
            .wlcms-welcome-panel .elementor h2,
            .wlcms-welcome-panel .elementor h3,
            .wlcms-welcome-panel .elementor h4,
            .wlcms-welcome-panel .elementor h5,
            .wlcms-welcome-panel .fl-builder-content p,
            .wlcms-welcome-panel p', 
            array(
                'border' => '0',
                'font-size' => '100%',
                'font' => 'inherit',
                'line-height' => 'inherit',
                'vertical-align' => 'baseline',
                'color' => 'unset'
                )
            );
        
        wlcms_set_css( '.wlcms-welcome-panel .welcome-panel-content > h2', 
            array(
                'width' => '95%',
                'padding' => '21px'
                )
            );
        wlcms_set_css( '.wlcms-welcome-panel .wlcms-welcome-content', 
            array(
                'padding' => '20px'
                )
            );
        wlcms_set_css( '.wlcms-welcome-panel .welcome-panel-content', 
            array(
                'max-width' => 'none!important',
                'margin-left' => '0!important'
                )
            );

        wlcms_set_css( '.wlcms-welcome-panel .elementor-section-full_width', 
            array(
                'width' => '100%!important',
                'left' => '0!important'
                )
            );
        
        wlcms_set_css( '.wlcms-welcome-panel, .wlcms-welcome-panel .welcome-panel-content', array(
                'padding' => '0!important',
        ));
        wlcms_set_css( '.wlcms-welcome-panel .welcome-panel-close', 
            array(
                'top' => '0!important',
                'right' => '0!important',
                'background' => 'white!important',
                'z-index' => '1000',

                )
            );
    }

    private function set_dashboard_all_hidden()
    {
        $this->is_dashboard_all_hidden = $this->get_settings('hide_all_dashboard_panels');
    }

    public function is_dashboard_all_hidden()
    {
        return $this->is_dashboard_all_hidden;
    }

    /**
     * Change Dashboard H1 Title by using js
     *
     * @return void
     */
    private function dashboard_title()
    {
        global $current_screen, $wp_version;

        $dashboard_title = '';
        if ($icon = $this->get_settings('dashboard_icon')) {
            $dashboard_title .= '<span id=\"wlcms_dashboard_logo\"><img src=\"' . $icon . '\" alt=\"\" /></span>';

            wlcms_set_css('.index-php #wlcms_dashboard_logo img', array('vertical-align' => 'middle', 'padding-right' => '10px'));
        }

        if ($title = $this->get_settings('dashboard_title')) {
            $dashboard_title .= '<span id=\"wlcms_dashboard_title\">' . $title . '</span>';
        }

        if (version_compare($wp_version, '3.8-beta', '>=')) {
            wlcms_add_js('jQuery(".index-php #wpbody-content .wrap h1:eq(0)").html("' . $dashboard_title . '")');
            return;
        }

        wlcms_add_js('jQuery("#icon-index").html("' . $dashboard_title . '")');

        return;
    }

    /**
     * Removed Wordpress Dashboard metaboxes if set to true
     *
     * @return void
     */
    private function remove_metaboxes()
    {
        global $wp_meta_boxes;

        if ($this->is_dashboard_all_hidden() && ($this->get_settings('hide_at_a_glance') &&
            $this->get_settings('hide_activities') &&
            $this->get_settings('hide_recent_comments') &&
            $this->get_settings('hide_news_and_events') &&
            $this->get_settings('hide_quick_press'))) {

            if (isset($wp_meta_boxes['dashboard'])) :
                foreach ($wp_meta_boxes['dashboard'] as $key => $widget) {

                if (isset($wp_meta_boxes['dashboard'][$key]['core'])) :
                    foreach ($wp_meta_boxes['dashboard'][$key]['core'] as $dashboard_key => $dashboard) {
                    if ($this->remove_dashboard_widget($dashboard_key)) {
                        unset($wp_meta_boxes['dashboard'][$key]['core'][$dashboard_key]);
                    }
                }
                endif;
            }
            endif;
            return;
        }

        if ($this->get_settings('hide_at_a_glance')) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
        }

        if ($this->get_settings('hide_activities')) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
        }

        if ($this->get_settings('hide_recent_comments')) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
        }

        if ($this->get_settings('hide_news_and_events')) {
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
        }

        if ($this->get_settings('hide_quick_press')) {
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
        }
    }
    private function remove_dashboard_widget($dashboard_key = false)
    {
        if (!$dashboard_key) return false;

        if (strpos($dashboard_key, 'custom_help_widget') !== false) return false;

        return !in_array($dashboard_key, $this->excluded_widgets());
    }
    private function excluded_widgets()
    {
        return array('wlcms_rss_box');
    }

    /**
     * Add Own Welcome panel if set to true
     *
     * @return void
     */
    private function set_welcome_metabox()
    {

        $user_id = get_current_user_id();
        if( $user_id == 0 )
        {
            return false;
        }

        $welcome_panels = wlcms_field_setting('welcome_panel');

        if (!$welcome_panels || !is_array($welcome_panels)) {
            return;
        }

        if (!count($welcome_panels) === 0) {
            return;
        }

        
        $admin_Dashboard_Welcome_Message = wlcms()->require_class("Admin_Dashboard_Welcome_Message");
        $admin_Dashboard_Welcome_Message = new Admin_Dashboard_Welcome_Message();
        
        foreach ($welcome_panels as $key => $welcome_panel) {
            if (!$this->is_welcome_panel_visible($welcome_panel) || !is_array($welcome_panel)) {
                continue;
            }
            
            $welcome_content_hidden = get_user_meta($user_id, 'vum_hide_dashboard' . $key ,true);
            
            if( $welcome_content_hidden && isset($welcome_panel['dismissible']) ) {

                continue;
            }

            $admin_Dashboard_Welcome_Message->set($welcome_panel, $key);
            $admin_Dashboard_Welcome_Message->handle();
        }

        $admin_Dashboard_Welcome_Message->make_welcome_panel($welcome_panels);

        wlcms_set_css('.index-php .wlcms-welcome-panel-content', array('margin' => '13px', 'padding-bottom' => '25px!important'));
    }

    private function is_welcome_panel_visible($setting)
    {

        if (!isset($setting['is_active'])) {
            return false;
        }

        if ($setting['is_active'] != '1') {
            return false;
        }

        if (!isset($setting['visible_to'])
            || (isset($setting['visible_to']) && !$setting['visible_to']))
            return false;

        $roles = $setting['visible_to'];

        return $this->has_current_user_role($roles);

    }

    private function has_current_user_role($roles)
    {

        $user_role = wlcms_current_user_roles();

        return in_array($user_role, $roles);
    }

    /**
     * Remove Dashboard Border Line if set to true
     *
     * @return void
     */
    private function remove_dashed_border()
    {
        if (!$this->get_settings('remove_empty_dash_panel')) {
            return;
        }

        wlcms_set_css('.postbox-container .meta-box-sortables.empty-container, .index-php .empty-container', array('border' => '0'));
    }

    /**
     * Remove Wordpress Core News and Event if set to troe
     *
     * @return void
     */
    public function remove_news_and_events()
    {
        if (!$this->get_settings('hide_news_and_events')) {
            return;
        }

        remove_meta_box('dashboard_primary', 'dashboard', 'core');

        wlcms_add_js(';jQuery("#community-events").remove();jQuery(".community-events-footer").remove();jQuery("#dashboard_primary h2 span").remove();');
    }

    /**
     * Add RSS Dashboard Metabox
     *
     * @return void
     */
    public function add_own_rss_panel()
    {
        if (!$this->get_settings('add_own_rss_panel')) {
            return;
        }

        if (!$this->get_settings('rss_feed_address')) {
            return;
        }


        $title = '';
        $this->get_settings('rss_title');

        if ($logo = $this->get_settings('rss_logo')) {
            $title .= '<img src="' . esc_url($logo) . '" height="16" width="16" alt="Logo" style="padding-right:5px;vertical-align:bottom;"/> ';
        }

        if ($rss_title = $this->get_settings('rss_title')) {
            $title .= $rss_title;
        }

        wp_add_dashboard_widget(
            'wlcms_rss_box',
            !empty($title) ? $title : '&nbsp;',
            array($this, 'rss_box')
        );
    }

    /**
     * Display RSS Metabox to the dashboard
     * called by wp_add_dashboard_widget
     * 
     * @return void
     */
    public function rss_box()
    {
        include_once(ABSPATH . WPINC . '/feed.php');

        $num_items = $this->get_settings('rss_feed_number_of_item');
        $introduction = $this->get_settings('rss_introduction');
        $show_post_content = $this->get_settings('show_post_content');
        $url = $this->get_settings('rss_feed_address');

        if ($introduction) {
            echo '<p>' . $introduction . '</p>';
        }

        $rss = fetch_feed($url);

        if ($error = is_wp_error($rss)) {
            echo '<div class="warning-text">' . $rss->get_error_message() . '</div>';

            wlcms_set_css(
                '.index-php .warning-text',
                array('color' => 'rgba(240, 116, 95, 0.808)', 'display' => 'block')
            );
            return;
        }

        $maxitems = $rss->get_item_quantity($num_items);
        $rss_items = $rss->get_items(0, $maxitems);

        if ($maxitems == 0) {
            echo 'No items.';
            return;
        }

        $rss_list = '<ul>';

        foreach ($rss_items as $item) :

            $rss_list .= sprintf(
            '<li><strong><a href="%s" title="Posted %s target="_blank">%s</a> </strong> <br />',
            esc_url($item->get_permalink()),
            $item->get_date('j F Y | g:i a'),
            esc_html($item->get_title())
        );

        if ($show_post_content) :
            $rss_list .= preg_replace('/<img[^>]+./', '', $item->get_content());

        endif;
        $rss_list .= '</li>';

        endforeach;

        $rss_list .= '</ul>';

        echo $rss_list;
    }

    public function hide_vum_dashboard()
    {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "vum_hide_dashboard_nonce")) {
            exit("No naughty business please");
        }
        $user_id = get_current_user_id();
        if( $user_id == 0 )
        {
            return false;
        }
        
        $key = sanitize_text_field($_POST['key']);
        update_user_meta($user_id, 'vum_hide_dashboard' . $key, 1);
        echo json_encode(array('type' => 'success'));
        exit;
    }

    public function reset_welcome_dashboard()
    {
        if(!  isset($_GET['wlcms-action'])  ) return;
        
        if( $_GET['wlcms-action'] !== 'reset-welcome-dashboard' ) return;
        if(!  isset($_GET['dashboard'])  ) return;
        
        if( ! is_wlcms_super_admin() ) return;
        $key = sanitize_text_field($_GET['dashboard']);
        
        delete_metadata( 'user', 0, 'vum_hide_dashboard'. $key, '', true );

        WLCMS_Queue('Welcome dashboard message successfully reset.');
    }
}