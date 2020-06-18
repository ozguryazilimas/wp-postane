<?php

/**
 * Class YARPP_DB_Schema
 * Class for database schema inspection and changes.
 *
 * @package YARPP
 * @author  Mike Nelson
 * @since   5.1.7
 */
class YARPP_DB_Schema {


	/**
	 * Checks if there is an index for the post title column
	 *
	 * @return bool
	 */
	public function title_column_has_index() {
		global $wpdb;
		// Disable a few inspections because this method is only called once on a very specific request.
		// phpcs:disable WordPress.VIP.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.VIP.DirectDatabaseQuery.NoCaching
		$wpdb->get_results( "SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = 'yarpp_title'" );
		// phpcs:enable WordPress.VIP.DirectDatabaseQuery.DirectQuery
		// phpcs:enable WordPress.VIP.DirectDatabaseQuery.NoCaching
		return ( $wpdb->num_rows >= 1 );
	}

	/**
	 * Checks if there is an index for the post content column
	 *
	 * @return bool
	 */
	public function content_column_has_index() {
		global $wpdb;
		// Disable a few inspections because this method is only called once on a very specific request.
		//phpcs:disable WordPress.VIP.DirectDatabaseQuery.DirectQuery
		//phpcs:disable WordPress.VIP.DirectDatabaseQuery.NoCaching
		$wpdb->get_results( "SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = 'yarpp_content'" );
		//phpcs:enable WordPress.VIP.DirectDatabaseQuery.DirectQuery
		//phpcs:enable WordPress.VIP.DirectDatabaseQuery.NoCaching
		return ( $wpdb->num_rows >= 1 );
	}
}
