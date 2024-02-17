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

			// block_categories_all is a replacement for block_categories filter from WP v5.8
			// see: https://developer.wordpress.org/block-editor/reference-guides/filters/block-filters/#block_categories_all
			if ( class_exists('WP_Block_Editor_Context') ) {
				add_filter( 'block_categories_all', array( $this, 'yarpp_block_categories' ), 10, 2 );
			} else {
				add_filter( 'block_categories', array( $this, 'yarpp_block_categories' ), 10, 2 );
			}
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
			$post_id        = null;
			$yarpp_is_admin = false;

			// If preview then return preview image.
			if ( $is_preview && ! empty( $block_attributes['yarpp_preview'] ) ) {
				$preview_image = YARPP_URL . '/images/yarpp-grid.svg';
				return '<img style="width:100%;" src="' . esc_url( $preview_image ) . '">';
			}

			// Since WP 5.8, the widgets are now Gutenberg blocks too, this will help us to differentiate between block and widget
			$yarpp_args = array(
				'domain' => isset($block_attributes['domain']) ? $block_attributes['domain'] : 'block',
			);
			if ( isset( $block_attributes['reference_id'] ) && ! empty($block_attributes['reference_id']) ) {
				$reference_post = get_post( (int) $block_attributes['reference_id']);
				$post_id        = $reference_post->ID;
			}
			// Checks if the block is being used in the admin interface
			if ( isset( $block_attributes['yarpp_is_admin'] ) ) {
				$yarpp_is_admin = $block_attributes['yarpp_is_admin'];
			}
			if ( isset( $block_attributes['heading'] ) ) {
				$yarpp_args['heading'] = $block_attributes['heading'];
			}
			if ( isset( $block_attributes['limit'] ) ) {
				$yarpp_args['limit'] = $block_attributes['limit'];
			}
			if ( isset( $block_attributes['template'] ) ) {
				$yarpp_args['template'] = ( $block_attributes['template'] !== 'builtin' && $block_attributes['template'] !== 'list' ) ? $block_attributes['template'] : false;
			}
			if ( isset( $block_attributes['domain'] ) ) {
				$yarpp_args['domain'] = $block_attributes['domain'];
			}
			if ( isset( $block_attributes['className'] ) ) {
				$yarpp_args['extra_css_class'] = $block_attributes['className'];
			}

			$output = '';

			// if there is no Reference ID specified on Block
			if ( empty($post_id) ) {
				// Check if the block is on the admin interface or if is preview (Gutenberg editor preview)
				if ( $yarpp_is_admin ) {
					$post_id = $post instanceof WP_Post ? $post->ID : null;
				} else {
					$queried_object = get_queried_object();
					// queried_object corresponds to the post that is being called
					// https://developer.wordpress.org/reference/functions/get_queried_object/
					if ( $queried_object instanceof WP_Post ) {
						$post_id = $queried_object->ID;
					}
				}
			}

			if ( $yarpp_args['domain'] === 'widget' && ! $yarpp_args['template'] && ( ! empty($post_id) || $yarpp_is_admin ) ) {
				$output .= '<h3>' . $yarpp_args['heading'] . '</h3>';
			}

			if ( ! empty($post_id) ) {
				$output .= $yarpp->display_related(
					$post_id,
					$yarpp_args,
					false
				);
			} elseif ( $yarpp_is_admin ) {
				$output .= $yarpp->display_demo_related(
					$yarpp_args,
					false
				);
			}

			return $output;
		}
		/**
		 * Get all  yarpp theme style.
		 *
		 * @since 5.19.0
		 * @return array all template data.
		 */
		public function yarpp_get_block_templates() {
			global $yarpp;
			$block_templates = $yarpp->get_all_templates();
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
			global $post;
			$version      = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : YARPP_VERSION;
			$default_deps = array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-components',
				'wp-block-editor',
			);

			$uri = $_SERVER['REQUEST_URI'];
			// batch/v1 is for the request on server_rendering
			$is_widget_page = substr_count( $uri, 'widgets.php' ) > 0 || substr_count( $uri, 'v2/widgets' ) > 0 || substr_count( $uri, '/batch/v1' ) > 0;
			$yarpp_is_admin = ( substr_count( $uri, 'block-renderer/yarpp/yarpp-block') > 0 && substr_count( $uri, '_locale=user') > 0 ) || is_admin() || ( substr_count( $uri, 'wp-json/wp/v2/posts/') > 0 && substr_count( $uri, '_locale=user') > 0 );

			// checks if the current page is the widgets.php admin page, since WP 5.8 there is an error when enqueuing the wp-editor and wp-edit-widgets at the same time
			if ( $is_widget_page ) {
				$default_deps[] = 'wp-edit-widgets';
			} else {
				$default_deps[] = 'wp-editor';
			}

			// automatically load dependencies and version.
			wp_register_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				'yarpp-block',
				yarpp_get_file_url_for_environment( 'js/block.min.js', 'src/js/block.js' ),
				$default_deps,
				$version
			);
			wp_register_style(
				'yarpp-block-style',
				plugins_url( 'style/yarpp-block-editor.css', __DIR__ ),
				array( 'wp-edit-blocks' ),
				$version
			);
			// Fetch chosen template from YARPP setting page.
			$chosen_template = yarpp_get_option( 'template' );
			// Localize the script with data.
			$localized_variables = array(
				'template'             => $this->yarpp_get_block_templates(),
				'selected_theme_style' => $chosen_template,
				'default_domain'       => $is_widget_page ? 'widget' : 'block',
				'yarpp_is_admin'       => $yarpp_is_admin,
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
					'reference_id'  => array(
						'type'    => 'string',
						'default' => null,
					),
					'heading'         => array(
						'type'    => 'string',
						'default' => __( 'You may also like', 'yet-another-related-posts-plugin' ),
					),
					'limit'         => array(
						'type'    => 'number',
						'default' => 6,
					),
					'template'      => array(
						'type'    => 'string',
						'default' => $chosen_template,
					),
					'domain'      => array(
						'type'    => 'string',
					),
					'yarpp_is_admin'      => array(
						'type'    => 'boolean',
						'default' => $yarpp_is_admin,
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
						'title' => __( 'YARPP', 'yet-another-related-posts-plugin' ),
					),
				)
			);
		}
	}
	new YARPP_Block();
}
