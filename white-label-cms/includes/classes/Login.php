<?php

class WLCMS_Login extends WLCMS_Previewable
{

    public function __construct()
    {
        //Check and set if it is a preview
        $this->check_preview();

        add_action('login_footer', array($this, 'scripts'), 1000);

        add_action('wlcms_before_save_preview', array($this, 'save_preview_login'), 10, 2);
        add_action('wlcms_save_addtional_settings', array($this, 'save_preview_login'), 10, 2);

        // Save the preview settings
        add_action('wp_ajax_wlcms_save_login_preview_settings', array($this, 'store_preview'));

        add_filter('wlcms_setting_fields', array($this, 'setting_fields'), 11, 1);
    }

    public function save_preview_login($setting, $placeholder)
    {
        // ignore if it has width or height request
        if (isset($_REQUEST['logo_width']) || isset($_REQUEST['logo_height']))
            return;

        $logo = $setting->get($placeholder . 'login_logo');

        if ($logo) {
            $imagesize = @getimagesize($logo);
            if ($imagesize) {
                list($width, $height) = $imagesize;
                $setting->set($placeholder . 'logo_width', $width);
                $setting->set($placeholder . 'logo_height', $height);
            }
        }

        return $setting;
    }

    public function scripts()
    {

        wp_print_scripts(array('jquery'));

        echo '<script>';
        echo 'jQuery(document).ready(function(){';
        echo $this->set_custom_login_js();
        echo $this->get_js();
        echo '});';
        echo '</script>';
        echo '<style type="text/css">';
        echo $this->compiled_login_css();
        echo '</style>';
    }

    private function compiled_login_css()
    {

        $content = $this->set_custom_css();
        $content .= $this->set_background_css();
        $content .= $this->set_logo_css();
        $content .= $this->set_form_css();
        $content .= $this->set_links_css();

        $content = wp_kses( $content, array( '\'', '\"' ) );
        $content = str_replace( '&gt;', '>', $content );
        return $content;
    }

    private function get_js()
    {
        $js = 'jQuery("#login").wrap("<div id=\'wlcms-login-wrapper\'></div>");';
        if ($this->get_settings('login_logo')) {
            $js .= ';jQuery(\'#login h1 a\').attr(\'title\',\'' . get_bloginfo('name') . '\');jQuery(\'#login h1 a\').attr(\'href\',\'' . get_bloginfo('url') . '\');';
        }
        return $js;
    }

    private function set_logo_css()
    {
        $logo_css = '#login h1 a, .login h1 a { ';
        if ($login_logo = $this->get_settings('login_logo')) {
            $logo_css .= 'background-image: url(' . $login_logo . ');';
        }

        $has_width = false;
        if ($logo_width = $this->get_settings('logo_width')) {
            $logo_css .= 'width:' . wlcms_css_metrics($logo_width) . ';';
            $has_width = true;
        } else {
            $logo_css .= 'width:auto!important;';
        }

        // Add logo max-width same width with the form
        $logo_css .= 'max-width:100%;';

        $has_height = false;
        if ($logo_height = $this->get_settings('logo_height')) {
            $has_height = true;
            $logo_css .= 'height:' . wlcms_css_metrics($logo_height) . ';';
        }

        //Add logo background size
        $logo_css_background_size = 'background-size:contain;background-position-y: center;';
        if ($has_height && $has_width) {
            $logo_css_background_size = sprintf('background-size:%s %s;', wlcms_css_metrics($logo_width), wlcms_css_metrics($logo_height));
        }
        $logo_css .= $logo_css_background_size;

        if ($logo_bottom_margin = $this->get_settings('logo_bottom_margin')) {
            $logo_css .= 'margin-bottom: ' . $logo_bottom_margin . 'px!important;';
        }

        $logo_css .= '}'; // close #login h1 a, .login h1 a 

        if ($retina_login_logo = $this->get_settings('retina_login_logo')) {
            $logo_css .= '@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) { 
                #login h1 a, .login h1 a { background-image: url(' . $retina_login_logo . ');}
            }';

        }

        return $logo_css;
    }

    private function set_background_css()
    {
        $body_login = 'body.login{';

        if ($this->get_settings('full_screen_background_image')) {
            $body_login .= '-webkit-background-size: cover !important;';
            $body_login .= '-moz-background-size: cover !important;';
            $body_login .= '-o-background-size: cover !important;';
            $body_login .= 'background-size: cover !important;';
        }

        if ($background_color = $this->get_settings('background_color')) {
            $body_login .= 'background-color:' . $background_color . '!important;';
        }

        if ($background_image = $this->get_settings('background_image')) {
            $body_login .= 'background-image: url(' . $background_image . ');';
        }

        if ($background_positions = $this->get_settings('background_positions')) {
            $body_login .= 'background-position:' . $background_positions . ';';
        }

        if ($background_repeat = $this->get_settings('background_repeat')) {
            $body_login .= 'background-repeat:' . $background_repeat . ';';
        }

        $body_login .= '}';

        return $body_login;
    }

    private function set_form_css()
    {
        $form_css = '';

        if ($form_label_color = $this->get_settings('form_label_color')) {
            $form_css .= '#loginform label{ color:' . $form_label_color . '}';
        }

        if ($form_background_color = $this->get_settings('form_background_color')) {
            $form_css .= '#loginform{ background-color:' . $form_background_color . '}';
        }

        /**
         * Submit Button css
         */
        $form_button_text_color = $this->get_settings('form_button_text_color');
        $form_button_color = $this->get_settings('form_button_color');

        if ($form_button_text_color || $form_button_color) {
            $form_css .= '#loginform input[type=submit],#loginform .submit input[type=button]{ ';
            if ($form_button_text_color) {
                $form_css .= 'color:' . $form_button_text_color . '!important;';
                $form_css .= 'text-shadow: none;';
                $form_css .= 'border-color: none;';
                $form_css .= 'box-shadow: none;';
            }

            if ($form_button_color) {
                $form_css .= 'background-color:' . $form_button_color . '!important; border: 0;box-shadow:none';
            }

            $form_css .= '}';
        }

        /**
         * Submit Button Hover
         */
        $form_button_text_hover_color = $this->get_settings('form_button_text_hover_color');
        $form_button_hover_color = $this->get_settings('form_button_hover_color');

        if ($form_button_hover_color || $form_button_text_hover_color) {
            $form_css .= '#loginform input[type=submit]:hover,#loginform .submit input[type=button]:hover{ ';
            if ($form_button_text_hover_color) {
                $form_css .= 'color:' . $form_button_text_hover_color . '!important;';
            }

            if ($form_button_hover_color) {
                $form_css .= 'background-color:' . $form_button_hover_color . '!important;';
            }

            $form_css .= '}';
        }

        return $form_css;
    }

    private function set_links_css()
    {
        $form_css = '';
        if ($this->get_settings('hide_register_lost_password')) {
            $form_css .= 'p#nav{display:none;}';
        }

        if ($this->get_settings('hide_back_to_link')) {
            $form_css .= 'p#backtoblog{display:none;}';
        }

        if ($back_to_register_link_color = $this->get_settings('back_to_register_link_color')) {
            $form_css .= 'p#backtoblog a, p#nav a{color:' . $back_to_register_link_color . '!important;}';
        }

        if ($back_to_register_link_hover_color = $this->get_settings('back_to_register_link_hover_color')) {
            $form_css .= 'p#backtoblog a:hover, p#nav a:hover{color:' . $back_to_register_link_hover_color . '!important;}';
        }

        if ($privacy_policy_link_color = $this->get_settings('privacy_policy_link_color')) {
            $form_css .= 'a.privacy-policy-link{color:' . $privacy_policy_link_color . '!important;text-decoration:none}';
        }

        if ($privacy_policy_link_hover_color = $this->get_settings('privacy_policy_link_hover_color')) {
            $form_css .= 'a.privacy-policy-link:hover{color:' . $privacy_policy_link_hover_color . '!important;}';
        }

        return $form_css;
    }

    private function set_custom_css()
    {
        $content = $this->get_settings('login_custom_css');
        
        $content = wp_kses( $content, array( '\'', '\"' ) );
        $content = str_replace( '&gt;', '>', $content );
        return $content;
    }

    private function set_custom_login_js()
    {
        return wlcms_esc_html_e($this->get_settings('login_custom_js'));
    }
    public function settings()
    {
        if ($this->saving_preview_section() == 'wizard') {
            return $this->wizard_settings();
        }

        return $this->complete_settings();
    }

    /**
     * Settings to be stored in preview mode
     *
     * @return array
     */
    private function wizard_settings()
    {
        $settings = array(
            'login_logo' => '',
            'add_retina_logo' => false,
            'retina_login_logo' => '',
        );

        return $settings;
    }

    /**
     * Settings to be included to the overall setting
     * it includes fields from preview mode
     * @return void
     */
    public function complete_settings()
    {

        $settings = array(
            'logo_bottom_margin' => 0,
            'logo_width' => false,
            'logo_height' => false,
            'background_color' => '#ffffff',
            'background_image' => '',
            'full_screen_background_image' => false,
            'background_positions' => 'center center',
            'background_repeat' => 'no-repeat',
            'hide_register_lost_password' => false,
            'hide_back_to_link' => false,
            'form_background_color' => '',
            'form_label_color' => '',
            'form_button_color' => '',
            'form_button_text_color' => '',
            'form_button_hover_color' => '',
            'form_button_text_hover_color' => '',
            'back_to_register_link_color' => '',
            'back_to_register_link_hover_color' => '',
            'privacy_policy_link_color' => '',
            'privacy_policy_link_hover_color' => '',
            'login_custom_css' => '',
            'login_custom_js' => ''
        );

        return array_merge($settings, $this->wizard_settings());
    }

    public function saving_preview_section()
    {
        $sections = array('wizard', 'settings');

        if (!isset($_REQUEST['form_section'])) {
            return;
        }

        if (!in_array($_REQUEST['form_section'], $sections)) {
            return;
        }

        return wp_filter_kses($_REQUEST['form_section']);
    }

    public function setting_fields($settings)
    {
        return array_merge($settings, $this->complete_settings());
    }

}