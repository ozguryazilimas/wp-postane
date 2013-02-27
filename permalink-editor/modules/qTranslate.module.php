<?php

/*
 * Permalink Editor Module: qTranslate
 *
 * Adds support when using the qTranslate plugin with Pre-Path Mode enabled,
 * (e.g. '/en/' in front of URL), with support for hiding the default language
 * option.
 *
 * http://wordpress.org/extend/plugins/qtranslate/
 */

class Permalink_Editor_Module_Qtranslate
{

	/**
	 * Stores the qTranslate config array.
	 * @var array
	 */
	var $config = false;

	/**
	 * Detect if the plugin is installed, fetch the config file and add the
	 * filters if required.
	 */
	function Permalink_Editor_Module_Qtranslate()
	{
		global $q_config;
		if ( defined( 'QT_URL_PATH' ) && is_array( $q_config ) ) {
			$this->config = $q_config;
		}
		if ( $this->using_path_mode() ) {
			add_filter( 'permalink_editor_page_link', array( &$this, 'append_prefix' ) );
			add_filter( 'permalink_editor_request', array( &$this, 'remove_prefix' ) );
		}
	}

	/**
	 * Check to see if path mode is enabled and the prefix should be appended.
	 *
	 * @return boolean
	 */
	function using_path_mode()
	{
		if ( isset( $this->config['language'], $this->config['url_mode'] ) ) {
			return ( $this->config['url_mode'] == QT_URL_PATH );
		}
		return false;
	}

	/**
	 * Add the language prefix to the url path.
	 *
	 * @param string $permalink

	 */
	function append_prefix( $permalink )
	{
		if ( $language = $this->get_prefix() ) {
			$prefix = '/' . trailingslashit( $language );
			$permalink = home_url( $prefix . ltrim( str_replace( array( $prefix, home_url() ), '', $permalink ), '/' ) );
		}
		return $permalink;
	}

	/**
	 * Remove the language prefix from the url path.
	 *
	 * @param string $permalink
	 * @return string
	 */
	function remove_prefix( $permalink )
	{
		if ( $language = $this->get_prefix() ) {
			$permalink = str_replace( $language, '', $permalink );
		}
		return $permalink;
	}

	/**
	 * Fetch the prefix for the current language if one is set and we are not
	 * hiding the default language.
	 *
	 * @return string|false
	 */
	function get_prefix()
	{
		$language = $this->config['language'];
		if ( qtrans_isEnabled( $language ) ) {
			if ( ! $this->config['hide_default_language'] || ( $language != $this->config['default_language'] ) ) {
				return $language;
			}
		}
		return false;
	}

}

new Permalink_Editor_Module_Qtranslate();

?>