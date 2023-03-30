<?php #comp-page builds: premium

/**
 * Обновление параметра для редиректа на страницу About после обновления плагина
 */
class WAPTUpdate030600 extends Wbcr_Factory466_Update {

	/**
	 * Do migration
	 */
	public function install() {
		if ( is_multisite() && $this->plugin->isNetworkActive() ) {
			global $wpdb;

			$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			if ( ! empty( $blogs ) ) {
				foreach ( $blogs as $id ) {

					switch_to_blog( $id );

					$this->new_migration();

					restore_current_blog();
				}
			}

			return;
		}

		$this->new_migration();
	}

	/**
	 * @author Artem Prihodko <webtemyk@yandex.ru>
	 * @since  3.6.0
	 */
	public function new_migration() {

		if ( null === get_option( $this->plugin->getOptionName( 'whats_new_v360' ), null ) ) {
			update_option( $this->plugin->getOptionName( 'whats_new_v360' ), 1 );
		}
	}
}
