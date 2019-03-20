<?php

class WLCMS_Admin_Script
{

    private $css = array();
    private $bulk_css = array();
    private $hidded_elements = array();
    private $js = array();
    private $additional_css;

    public function __construct()
    {
        add_action('admin_footer', array($this, 'footer_script'));
        add_action('wp_footer', array($this, 'footer_script'), 99999);
        add_action('in_admin_header', array($this, 'header_css'), 99999);
        add_action('wp_head', array($this, 'header_css'), 99999);
    }

    public function header_css()
    {
       
        // no need to append scripts for guest users
        if (!is_user_logged_in()) {
            return;
        }
        $scripts = "<!-- WLCMS Style-->\n";
        if (count($this->css) || count($this->hidded_elements) || count($this->bulk_css)) {
            $scripts .= sprintf('<style type="text/css">%s %s</style>', $this->compileCss(), $this->additional_css);
        }
        $scripts .= "\n<!-- WLCMS End Style-->";
        echo $scripts;

    }

    public function footer_script()
    {
        $scripts = '';

        // no need to append scripts for guest users
        if (!is_user_logged_in()) {
            return;
        }

        if (count($this->js)) {
            $scripts .= '<script type="text/javascript">';
            $scripts .= '/* <![CDATA[ */';
            $scripts .= '   jQuery(document).ready(function() { ' . $this->compileJs() . ' });';
            $scripts .= '/* ]]> */';
            $scripts .= '</script>';
        }

        if (!empty($scripts)) {
            $scripts = "<!-- WLCMS Scripts-->\n" . $scripts . "\n<!-- WLCMS End Scripts-->";
        }

        echo $scripts;
    }

    public function set_CssHidden($props)
    {
        $this->hidded_elements[] = $props;
    }

    public function setCss($element, $props = array())
    {
        $this->css[$element] = $props;
    }

    public function appendCss($css)
    {
        $this->bulk_css[] = $css;
    }

    private function _setHiddenCss()
    {
        $hidden = implode($this->hidded_elements, ',');
        if (!empty($hidden)) {
            $this->setCss($hidden, array('display' => 'none'));
        }
    }

    private function _setBulkCss()
    {
        return implode($this->bulk_css, '');
    }

    public function appendJs($js)
    {
        $this->js[] = $js;
    }

    function compileJs()
    {
        return implode($this->js, '');
    }

    function compileCss()
    {
        $this->_setHiddenCss();

        if (!count($this->css)) {
            return;
        }

        $css_output = '';
        foreach ($this->css as $element => $props) {

            $css_output .= $element . '{';
            foreach ($props as $prop => $value) {
                $css_output .= $prop . ':' . $value . ';';
            }

            $css_output .= '}';

        }

        $css_output .= $this->_setBulkCss();

        return $css_output;
    }

    public function additional_css($css)
    {
        $this->additional_css .= $css;
    }

}