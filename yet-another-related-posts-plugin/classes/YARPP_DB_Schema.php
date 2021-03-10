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
	const CACHE_KEY_TITLE_INDEX                 = 'title_index';
	const CACHE_KEY_CONTENT_INDEX               = 'content_index';
	const CACHE_KEY_FULLTEXT_SUPPORT            = 'fulltext_support';
	const CACHE_KEY_POSTS_TABLE_DATABASE_ENGINE = 'posts_table_database_engine';
	const CACHE_GROUP                           = 'yarpp';
	/**
	 * Checks if there is an index for the post title column
	 *
	 * @return bool
	 */
	public function title_column_has_index() {
		$result = wp_cache_get( self::CACHE_KEY_TITLE_INDEX, self::CACHE_GROUP, false, $found );
		if ( ! $found ) {
			global $wpdb;
			$wpdb->get_results( "SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = 'yarpp_title'" );

			$result = $wpdb->num_rows >= 1;
			wp_cache_set( self::CACHE_KEY_TITLE_INDEX, $result, self::CACHE_GROUP );
		}
		return $result;
	}

	/**
	 * Checks if there is an index for the post content column
	 *
	 * @return bool
	 */
	public function content_column_has_index() {
		$result = wp_cache_get( self::CACHE_KEY_CONTENT_INDEX, self::CACHE_GROUP, false, $found );
		if ( ! $found ) {
			global $wpdb;
			$wpdb->get_results( "SHOW INDEX FROM {$wpdb->posts} WHERE Key_name = 'yarpp_content'" );

			$result = $wpdb->num_rows >= 1;
			wp_cache_set( self::CACHE_KEY_CONTENT_INDEX, $result, self::CACHE_GROUP );
		}
		return $result;
	}

	/**
	 * Checks if the posts table supports fulltext indexes.
	 *
	 * @since 5.2.0
	 * @return bool
	 */
	public function database_supports_fulltext_indexes() {
		$result = wp_cache_get( self::CACHE_KEY_FULLTEXT_SUPPORT, self::CACHE_GROUP, false, $found );
		if ( ! $found ) {
			global $wpdb;
			// Check if the database is a version that supports InnoDB fulltext indexes.
			$innodb_fulltext_params = $wpdb->get_results( "show variables like 'innodb_ft%';" );
			if ( ( count( $innodb_fulltext_params ) > 0 ) || ( $this->posts_table_database_engine() === 'MyISAM' ) ) {
				$result = true;
			} else {
				// They don't seem to support full text indexes, sorry.
				$result = false;
			}
			wp_cache_set( self::CACHE_KEY_FULLTEXT_SUPPORT, $result, self::CACHE_GROUP );
		}
		return $result;
	}

	/**
	 * Gets the database engine of the posts' table.
	 *
	 * @since 5.2.0
	 * @return string|null
	 */
	public function posts_table_database_engine() {
		$result = wp_cache_get( self::CACHE_KEY_POSTS_TABLE_DATABASE_ENGINE, self::CACHE_GROUP, false, $found );
		if ( ! $found ) {
			global $wpdb;
			$tables = $wpdb->get_results( "SHOW TABLE STATUS WHERE Name = '{$wpdb->posts}'" );
			foreach ( $tables as $table ) {
				$result = $table->Engine;
				break;
			}

			wp_cache_set( self::CACHE_KEY_POSTS_TABLE_DATABASE_ENGINE, $result, self::CACHE_GROUP );
		}
		return $result;
	}

	/**
	 * Adds the post title fulltext index on the posts table. Silences any errors, but you can still get them from
	 * $wpdb->last_error.
	 *
	 * @return boolean succeess
	 */
	public function add_title_index() {
		global $wpdb;
		/* Temporarily ensure that errors are not displayed: */
		$previous_value = $wpdb->hide_errors();
		$wpdb->query( "ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_title` (`post_title`)" );
		/* Restore previous setting */
		$wpdb->show_errors( $previous_value );
		wp_cache_delete( self::CACHE_KEY_TITLE_INDEX, self::CACHE_GROUP );

		return empty( $wpdb->last_error );
	}

	/**
	 * Adds the post content fulltext index on the posts table. Silences any errors, but you can still get them from
	 * $wpdb->last_error.
	 *
	 * @return boolean succeess
	 */
	public function add_content_index() {
		global $wpdb;
		/* Temporarily ensure that errors are not displayed: */
		$previous_value = $wpdb->hide_errors();
		$wpdb->query( "ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_content` (`post_content`)" );
		/* Restore previous setting */
		$wpdb->show_errors( $previous_value );
		wp_cache_delete( self::CACHE_KEY_CONTENT_INDEX, self::CACHE_GROUP );

		return empty( $wpdb->last_error );
	}
}
