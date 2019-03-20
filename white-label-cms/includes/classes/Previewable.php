<?php

class WLCMS_Previewable
{

    public $is_preview = false;
    public $preview_setting_key_placeholder = '_';
    public $preview_section;

    public function check_preview()
    {
        if (!isset($_GET['wlcms-action'])) {
            return;
        }

        if ($_GET['wlcms-action'] != 'preview') {
            return;
        }

        $this->is_preview = true;
        $this->preview_section = isset($_GET['preview_section']) ? wp_filter_kses($_GET['preview_section']) : '';

    }

    public function place_holder()
    {
        return ($this->is_preview) ? $this->preview_setting_key_placeholder : '';
    }

    public function get_placeholder_key()
    {
        return $this->preview_setting_key_placeholder;
    }

    public function store_preview()
    {

        check_ajax_referer('wlcms_ajax_nonce');

        $settings = wlcms()->Settings();

        foreach ($this->settings() as $key => $default) {
            $setting_value = (isset($_POST[$key])) ? wlcms_kses($_POST[$key]) : $default;
            $settings->set($this->preview_setting_key_placeholder . $key, $setting_value);
        }

        do_action('wlcms_before_save_preview', $settings, $this->preview_setting_key_placeholder);

        $settings->save();
        exit;
    }

    public function preview_section()
    {
        return $this->preview_section;
    }

    public function get_settings($key)
    {
        // Prepend "_" placeholder if in preview mode to get the preview login settings
        if ($this->is_preview) {
            $key = $this->preview_setting_key_placeholder . $key;
        }

        return wlcms_field_setting($key);
    }
}
