<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */

class WLCMS_I18n
{
    function __construct()
    {
        add_action('init', array($this, 'load_textdomain'));
    }

    public function load_textdomain()
    {

        $domain = 'white-label-cms';
        $plugin_rel_path = $domain . '/languages/';
        load_plugin_textdomain(
            $domain,
            false,
            $plugin_rel_path
        );
    }
}

new WLCMS_I18n();
