<?php
/**
 * YARPP block setup
 *
 * @package YARPP
 * @since   5.19.0
 */

if ( ! class_exists( 'YARPP_Block', false ) && function_exists( 'register_block_type' ) ) {
	/**
	 * YARPP_Block Class.
	 *
	 * @class YARPP_Block
	 */
	class YARPP_Block {
		/**
		 * YARPP Constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'yarpp_gutenberg_block_func' ), 100 );
			add_filter( 'block_categories', array( $this, 'yarpp_block_categories' ), 10, 2 );
			add_action( 'enqueue_block_editor_assets', array( $this, 'yarpp_enqueue_block_editor_assets' ) );
		}

		/**
		 * YARPP enqueue thumbnail stylesheet.
		 */
		public function yarpp_enqueue_block_editor_assets() {
			global $yarpp;
			$dimension = $yarpp->thumbnail_dimensions();
			$yarpp->enqueue_thumbnails_stylesheet( $dimension );
		}
		/**
		 * YARPP yarpp_block_render_callback.
		 *
		 * @param array[] $block_attributes YARPP block attributes.
		 * @param string  $content block content.
		 * @param bool    $is_preview block preview.
		 * @return string Rendered YARPP block HTML.
		 */
		public function yarpp_block_render_callback( $block_attributes, $content, $is_preview = false ) {
			global $yarpp, $post;
			// If preview then return preview image.
			if ( $is_preview && ! empty( $block_attributes['yarpp_preview'] ) ) {
				$preview_image = YARPP_URL . '/images/yarpp-grid.svg';
				return '<img style="width:100%;" src="' . esc_url( $preview_image ) . '">';
			}
			$yarpp_args = array(
				'domain' => 'block',
			);
			if ( isset( $block_attributes['limit'] ) ) {
				$yarpp_args['limit'] = $block_attributes['limit'];
			}
			if ( isset( $block_attributes['template'] ) ) {
				$yarpp_args['template'] = $block_attributes['template'];
			}
			return $yarpp->display_related(
				$post->ID,
				$yarpp_args,
				false
			);
		}
		/**
		 * Get all  yarpp theme style.
		 *
		 * @since 5.19.0
		 * @return array all template data.
		 */
		public function yarpp_get_block_templates() {
			global $yarpp;
			$templates       = $yarpp->get_templates();
			$block_templates = array(
				esc_attr( 'builtin' )    => esc_html__( 'List', 'yarpp' ),
				esc_attr( 'thumbnails' ) => esc_html__( 'Thumbnail', 'yarpp' ),
			);
			foreach ( $templates as $template ) {
				$block_templates[ esc_attr( $template['basename'] ) ] = sprintf(
					/* translators: %s: yarpp template name */
					esc_html__( 'Custom: %s', 'yarpp' ),
					$template['name']
				);
			}
			/**
			 * Filter the array containing templates.
			 *
			 * @since 5.19.0
			 *
			 * @param string $block_templates yarpp templates.
			 */
			return apply_filters( 'yarpp_get_block_templates', $block_templates );
		}
		/**
		 * YARPP yarpp_gutenberg_block_func.
		 */
		public function yarpp_gutenberg_block_func() {
			$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : YARPP_VERSION;
			// automatically load dependencies and version.
			wp_register_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				'yarpp-block',
				yarpp_get_file_url_for_environment( 'js/block.min.js', 'src/js/block.js' ),
				array(
					'wp-blocks',
					'wp-i18n',
					'wp-element',
					'wp-components',
					'wp-block-editor',
					'wp-editor',
				),
				$version
			);
			wp_register_style(
				'yarpp-block-style',
				plugins_url( 'style/yarpp-block-editor.css', dirname( __FILE__ ) ),
				array( 'wp-edit-blocks' ),
				$version
			);
			// Fetch chosen template from YARPP setting page.
			$chosen_template = yarpp_get_option( 'template' );
			// Localize the script with data.
			$localized_variables = array(
				'template'             => $this->yarpp_get_block_templates(),
				'selected_theme_style' => $chosen_template,
			);
			wp_localize_script( 'yarpp-block', 'yarpp_localized', $localized_variables );
			$args = array(
				'editor_style'    => 'yarpp-block-style',
				'editor_script'   => 'yarpp-block',
				'render_callback' => array( $this, 'yarpp_block_render_callback' ),
				'attributes'      => array(
					'className'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'limit'         => array(
						'type'    => 'number',
						'default' => 6,
					),
					'template'      => array(
						'type'    => 'string',
						'default' => $chosen_template,
					),
					'yarpp_preview' => array(
						'type' => 'string',
					),
				),
			);
			register_block_type( 'yarpp/yarpp-block', $args );
		}
		/**
		 * Filters the default array of block categories.
		 *
		 * @param array[] $categories Array of block categories.
		 * @param WP_Post $post Post being loaded.
		 */
		public function yarpp_block_categories( $categories, $post ) {
			return array_merge(
				$categories,
				array(
					array(
						'slug'  => 'yarpp',
						'title' => __( 'YARPP', 'yarpp' ),
					),
				)
			);
		}

	}
	new YARPP_Block();
}
