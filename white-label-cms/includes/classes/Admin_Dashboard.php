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
            if (!$this->has_current_user_role($dashboard_widgets_visibility_roles))
                return;
        }

        $this->set_dashboard_all_hidden();
        $this->remove_metaboxes();
        $this->remove_dashed_border();
    }

    private function reset_dashboard_style() {

        wlcms_set_css(
            '.wlcms-welcome-panel-content .elementor p,
            .wlcms-welcome-panel-content .elementor div,
            .wlcms-welcome-panel-content .elementor h1,
            .wlcms-welcome-panel-content .elementor h2,
            .wlcms-welcome-panel-content .elementor h3
            .wlcms-welcome-panel-content .elementor h4
            .wlcms-welcome-panel-content .elementor h5', 
            array(
                'border' => '0',
                'font-size' => '100%',
                'font' => 'inherit',
                'line-height' => 'inherit',
                'vertical-align' => 'baseline',
                'color' => 'inherit'
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

        $welcome_panels = wlcms_field_setting('welcome_panel');

        if (!$welcome_panels || !is_array($welcome_panels)) {
            return;
        }

        if (!count($welcome_panels) === 0) {
            return;
        }


        foreach ($welcome_panels as $key => $welcome_panel) {

            if (!$this->is_welcome_panel_visible($welcome_panel) || !is_array($welcome_panel)) {

                continue;
            }

            $this->set_welcome_panel_content($welcome_panel);
            $this->make_welcome_panel($key);
        }

        wlcms_set_css('.index-php .wlcms-welcome-panel-content', array('margin' => '13px', 'padding-bottom' => '25px!important'));
        add_action('admin_footer', array($this, 'welcome_panel_footer'));
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

    private function set_welcome_panel_content($config)
    {
        $this->is_fullwidth = true;

        $this->welcome_show_title = isset($config['show_title']) && $config['show_title'] == 1;
        $template_type = isset($config['template_type']) ? $config['template_type'] : 'html';

        if ($template_type == 'Elementor' && wlcms_is_elementor_active()) :
            $id = isset($config['page_id_elementor']) ? $config['page_id_elementor'] : null;
        $desciption = $this->page_builder_elementor($id);
        $title = get_the_title($id);
        elseif ($template_type == 'Beaver Builder' && wlcms_is_beaver_builder_active()) :
            $id = isset($config['page_id_beaver']) ? $config['page_id_beaver'] : null;
        $desciption = $this->page_builder_beaver_builder($id);
        $title = get_the_title($id);
        else :
            $title = isset($config['title']) ? $config['title'] : null;
        $desciption = isset($config['description']) ? $config['description'] : null;
        $this->is_fullwidth = isset($config['is_fullwidth']) ? $config['is_fullwidth'] : false;
        $this->welcome_show_title = true;
        endif;

        $this->welcome_title = $title;

        $this->set_welcom_description($desciption);

    }

    public function set_welcom_description($des)
    {
        $this->welcome_description = $des;
    }

    public function get_welcom_description()
    {
        return $this->welcome_description;
    }

    public function compile_welcome_content($key)
    {
        $title = ($this->welcome_show_title) ? sprintf("<h2>%s</h2>", $this->welcome_title) : '';
        $content = sprintf(
            "<div id=\"welcome-panel%1\$d\" class=\"welcome-panel\" style=\"display:none\"><div class=\"wlcms-welcome-panel-content\">%2\$s<div class=\"welcome-panel-column-container\">%3\$s</div></div></div>",
            $key,
            $title,
            $this->welcome_description
        );

        $this->welcome_content .= $content;
    }

    public function welcome_panel_footer()
    {
        echo $this->welcome_content;
    }

    private function make_welcome_panel($key)
    {

        if ($this->is_fullwidth) {
            $this->compile_welcome_content($key);
            $welcome = sprintf(";jQuery('#welcome-panel%1\$d').insertBefore('#dashboard-widgets-wrap');jQuery('#welcome-panel%1\$d').show();", $key);
            wlcms_add_js($welcome);
            return;
        }

        wp_add_dashboard_widget(
            'custom_help_widget' . $key,
            $this->welcome_title,
            array($this, 'welcome_description'),
            null,
            array('desc' => $this->get_welcom_description())
        );

    }

    public function welcome_description($post, $callback_args)
    {
        echo $callback_args['args']['desc'];
    }

    public function page_builder_beaver_builder($template = false)
    {
        if (!$template) return;

        add_action('admin_enqueue_scripts', 'FLBuilder::register_layout_styles_scripts');
        return do_shortcode('[fl_builder_insert_layout id="' . $template . '"]');
    }

    public function page_builder_elementor($template = false)
    {
        if (!$template) return;

        $elementor = @Elementor\Plugin::instance();

        $elementor->frontend->register_styles();
        $elementor->frontend->enqueue_styles();

        $elementor->frontend->register_scripts();
        $elementor->frontend->enqueue_scripts();
        return $elementor->frontend->get_builder_content($template, true);
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
            $title .= '<img src="' . $logo . '" height="16" width="16" alt="Logo" style="padding-right:5px;vertical-align:bottom;"/> ';
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

}