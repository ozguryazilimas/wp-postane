<?php
/**
 * Class YARPP_DB_Options
 * Class for storing and retrieving options saved to the WordPress options table. Does not contain extra logic
 * about the default YARPP options' values or when to clear the cache. This is just a central way to get/set
 * database options used by YARPP and to describe them.
 *
 * @package        YARPP
 * @since          5.2.0
 */
class YARPP_DB_Options {
	/**
	 * Key in YARPP option.
	 * Currently indicates that YARPP couldn't install the fulltext indexes upon activation, so the user
	 * had to run a database query to change their posts table to use MyISAM database engine. This option is set
	 * when they assert they have done that and then YARPP can re-attempt creating the database indexes.
	 */
	const YARPP_MYISAM_OVERRIDE = 'myisam_override';

	/**
	 * Key in options table whose value indicates the last error relating to adding fulltext indexes.
	 */
	const FULLTEXT_DB_ERROR = 'yarpp_fulltext_db_error';

	/**
	 * Gets all the raw YARPP settings as stored in the DB. You should probably use `YARPP::get_option` instead
	 * as that is merged with the defaults.
	 *
	 * @return array
	 */
	public function get_yarpp_options() {
		$options = (array) get_option( 'yarpp', array() );

		return $options;
	}

	/**
	 * Updates all the YARPP settings. You should probably use YARPP::set_option() instead, as that merges the input
	 * with the defaults, and it intelligently checks whether we should clear the YARPP cache or not.
	 *
	 * @param array $options an array where keys are option names and values are their values.
	 *
	 * @return bool success
	 */
	public function set_yarpp_options( $options ) {
		return update_option( 'yarpp', (array) $options );
	}

	/**
	 * Gets whether fulltext indexes were not found to be supported.
	 *
	 * @deprecated in 5.14.0 because we just always try to use fulltext indexes
	 * @return bool
	 */
	public function is_fulltext_disabled() {
		return (bool) get_option( 'yarpp_fulltext_disabled', false );
	}

	/**
	 * Records that fulltext indexes weren't supported.
	 *
	 * @param boolean $new_value True if we found fulltext indexes were supported, false otherwise.
	 * @deprecated in 5.14.0 because we just check the actual DB instead
	 * @return bool indicating success
	 */
	public function set_fulltext_disabled( $new_value ) {
		return update_option( 'yarpp_fulltext_disabled', (bool) $new_value );
	}

	/**
	 * Gets the installed version of YARPP
	 *
	 * @return string
	 */
	public function plugin_version_in_db() {
		return get_option( 'yarpp_version' );
	}

	/**
	 * Updates the version YARPP knows is installed.
	 *
	 * @return bool indicating success
	 */
	public function update_plugin_version_in_db() {
		return update_option( 'yarpp_version', YARPP_VERSION );
	}

	/**
	 * Gets the "yarpp_activated" option, which indicates YARPP was just activated.
	 *
	 * @return bool
	 */
	public function after_activation() {
		return (bool) get_option( 'yarpp_activated', false );
	}

	/**
	 * Deletes the WP option that indicates YARPP was just activated.
	 *
	 * @return bool
	 */
	public function delete_activation_flag() {
		return delete_option( 'yarpp_activated' );
	}

	/**
	 * Checks if YARPP was upgraded during this request.
	 *
	 * @return bool
	 */
	public function after_upgrade() {
		return (bool) get_option( 'yarpp_upgraded' );
	}

	/**
	 * Sets a flag that indicates YARPP was just upgraded.
	 *
	 * @return bool
	 */
	public function add_upgrade_flag() {
		return update_option( 'yarpp_upgraded', true );
	}

	/**
	 * Deletes the flag that indicates YARPP was just activated.
	 *
	 * @return bool
	 */
	public function delete_upgrade_flag() {
		return delete_option( 'yarpp_upgraded' );
	}

	/**
	 * Stores the $wpdb->last_error in a WP option with key "yarpp_fulltext_db_error" for later retrieval.
	 * This should be called right after YARPP_DB_Schema::add_title_index() or YARPP_DB_Schema::add_content_index().
	 *
	 * @since 5.2.0
	 * @return bool success
	 */
	public function update_fulltext_db_record() {
		global $wpdb;
		return update_option( self::FULLTEXT_DB_ERROR, $wpdb->last_error . '(' . current_time( 'mysql' ) . ')' );
	}

	/**
	 * Deletes the option that indicates there was an error adding the fulltext index.
	 *
	 * @return bool success
	 */
	public function delete_fulltext_db_error_record() {
		return delete_option( self::FULLTEXT_DB_ERROR );
	}

	/**
	 * Gets the last error relating to adding YARPP's fulltext indexes.
	 *
	 * @since 5.2.0
	 * @return string
	 */
	public function get_fulltext_db_error() {
		return (string) get_option( self::FULLTEXT_DB_ERROR, esc_html__( 'No error recorded.', 'yet-another-related-posts-plugin' ) );
	}

	/**
	 * @return bool
	 */
	public function has_fulltext_db_error() {
		return (bool) get_option( self::FULLTEXT_DB_ERROR, false );
	}
}
