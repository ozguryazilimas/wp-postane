<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */

class WLCMS_I18n
{
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'wlcms',
            false,
            WLCMS_DIR . '/languages/'
        );
    }
}
