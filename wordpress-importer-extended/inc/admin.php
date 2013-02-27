<?php

/**
 * Admin class
 *
 * @since 0.1
 */
class WordPressImporterExtendedAdmin extends WordPressImporterExtended {

	/**
	 * Constructor
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	public function __construct() {
		parent::__construct();
		register_activation_hook(
			$this->plugin_file,
			array($this, 'activate')
		);
		add_action(
			'admin_menu',
			array( $this, 'add_page' )
		);
		add_action(
			'admin_init',
			array( $this, 'register_settings' )
		);
	}

	/**
	 * Activation hook, used to auto-import after reset
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	public function activate() {
		if ( defined('WORDPRESS_IMPORTER_EXTENDED_AUTO') ) {
			$files = explode(',', WORDPRESS_IMPORTER_EXTENDED_AUTO);
			foreach( $files as $file ) {
				$file = ABSPATH . $file;
				$this->import_file($file);
			}
		}
	}

	/**
	 * Import a WXR file
	 *
	 * @todo should figure out how to work without WP_LOAD_IMPORTERS
	 * defined.
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	private function import_file($file) {
		if ( class_exists('WP_Import') && defined('WP_LOAD_IMPORTERS')  ) {
			$WP_Import = new WP_Import();
			// Not sure why these wouldn't be loaded
			if ( ! function_exists ( 'wp_insert_category' ) )
				include ( ABSPATH . 'wp-admin/includes/taxonomy.php' );
			if ( ! function_exists ( 'post_exists' ) )
				include ( ABSPATH . 'wp-admin/includes/post.php' );
			if ( ! function_exists ( 'comment_exists' ) )
				include ( ABSPATH . 'wp-admin/includes/comment.php' );
			ob_start();
			if ( defined('WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS') && WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS == true ) {
				$WP_Import->fetch_attachments = true;
				$WP_Import->allow_fetch_attachments();
			}
			$WP_Import->import( $file );
			ob_end_clean();
		}
	}

	/**
	 * Set up the options page
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	public function add_page() {
		if ( current_user_can ( 'manage_options' ) ) {
			$options_page = add_management_page (
				__( 'Wordpress Importer Extended' , 'wordpress-importer-extended' ),
				__( 'Wordpress Importer Extended' , 'wordpress-importer-extended' ),
				'manage_options',
				'wordpress-importer-extended',
				array ( $this , 'admin_page' )
			);
			add_action(
				'admin_print_styles-' . $options_page,
				array( $this, 'css' )
			);
		}
	}

	/**
	 * Register the plugin option with the setting API
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	public function register_settings () {
		register_setting(
			'wordpress-importer-extended_options',
			'wordpress-importer-extended',
			array( $this, 'wordpress_importer_extended_options' )
		);
	}

	/**
	 * Form input helper that produces the correct HTML markup
	 *
	 * @since 0.1
	 *
	 * @param string $label Input label
	 * @param string $name Input name
	 * @param string $comment Input comment
	 * @return none
	 */
	private function input( $label, $name, $comment=false, $size=10 ) { ?>
		<tr valign="top">
			<th scope="row"> <?php
				echo $label; ?>
			</th>
			<td> <?php
				echo '<input type="text" name="wordpress-importer-extended[' . $name . ']" value="' . $this->get_option( $name ) . '" size="' . $size . '"/ >';
				if ( $comment )
					echo ' ' . $comment;
				?>
			</td>
		</tr> <?php
	}

	/**
	 * Load admin CSS style
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	public function css() {
		wp_register_style(
			'wordpress-importer-extended',
			plugins_url( basename( $this->plugin_dir ) . '/css/admin.css' ),
			null,
			$this->version
		);
		wp_enqueue_style( 'wordpress-importer-extended' );
	}

	/**
	 * Output the options page
	 *
	 * @since 0.1
	 *
	 * @return none
	 */
	public function admin_page () { ?>
		<div id="nkuttler" class="wrap" >
			<div id="nkcontent">
				<h2><?php _e( 'WordPress Importer Extended', 'wordpress-importer-extended' ) ?></h2>
				<p>This plugin was primary developed to auto-import content on activation, to be used in conjunction with the wordpress-reset plugin. Please refer to the <tt>readme.txt</tt>.
				<br>
				But you can also use it to import files you uploaded through FTP to bypass upload problems. Please refer to the <tt>readme.txt</tt> for instructions.</p>
				<form method="post" action="options.php"> <?php
					settings_fields( 'wordpress-importer-extended_options' ); ?>

					<h3>Import WXR File</h3>
					<table class="form-table form-table-clearnone" > <?php
						$this->input(
							'WXR File',
							'file',
							'File you wish to import. Use a path relative from the WordPress root, e.g. <tt>wp-content/test-data.xml</tt>',
							50
						); ?>

					</table>

					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Import') ?>" />
					</p>
				</form>
			</div> <?php
			include( 'nkuttler.php' );
			if ( function_exists( 'nkuttler_0_3_links' ) ) {
				nkuttler_0_3_links(
					'wordpress-importer-extended',
					'wordpress-importer-extended-plugin'
				);
			} ?>
		</div> <?php
	}

	public function wordpress_importer_extended_options($data) {
		$file = ABSPATH . $data['file'];
		$this->import_file($file);
		return $data;
	}

}
