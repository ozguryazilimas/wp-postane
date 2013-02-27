<?php
/*
Plugin Name: Permalink Editor
Plugin URI: http://fubra.com
Description: Provides the functionality to customise the global page permalink structure, allowing the addition of prefixes or file extensions to page urls e.g. <em>/page/about-us.html</em>. It also allows you to specify a full custom permalink on an <em>individual post or page</em> basis.
Version: 0.2.12
Author: Fubra Limited
Author URI: http://fubra.com
License: GPL2
*/

class Permalink_Editor
{

	/**
	 * The plugin directory name as it appears in the plugins folder.
	 * @var string
	 */
	var $dir_name = 'permalink-editor';

	/**
	 * Generic tag name used for prefixing settings / inputs.
	 * @var string
	 */
	var $tag = 'custom_permalink';

	/**
	 * Local store of the current permalink structures.
	 * @var array|false
	 */
	var $structures = false;

	/**
	 * List of customisable permalinks, includes the required structure tag.
	 * @var array
	 */
	var $structure_tags = array(
		'category' => array(
			'name' => 'category',
			'tag' => '%category%'
		),
		'post_tag' => array(
			'name' => 'tag',
			'tag' => '%post_tag%'
		),
		'page' => array(
			'name' => 'page',
			'tag' => '%pagename%'
		),
		'author' => array(
			'name' => 'author',
			'tag' => '%author%'
		)
	);

	/**
	 * Filters applied to individual post or page permalinks.
	 * @var array
	 */
	var $individual_permalink_filters = array(
		'page_link' => 'page_link',
		'post_link' => 'page_link',
		'author_link' => 'trailingslash',
		'request' => 'request',
	);

	/**
	 * Add actions and filters to ensure the urls are correctly re-written. We
	 * use priority 11 here to try and catch anything that may have been added
	 * on or before the default priority of 10 (such as custom post types).
	 */
	function Permalink_Editor()
	{
		add_action( 'init', array( &$this, 'init' ), 11 );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Modify the page permastruct and set the new custom structure if rewriting
	 * is enabled.
	 */
	function init()
	{
		if ( $this->rewrite_enabled() ) {
			// Add the filters for custom permalink output...
			$this->add_filters( $this->individual_permalink_filters );
			// Generate a list of custom rewrite rules...
			if ( $this->generate_rewrite_rules() ) {
				// Load additional modules...
				$this->load_modules();
			}
			// Add filters...
			add_filter( 'user_trailingslashit', array( &$this, 'trailingslash' ), 10 );
		}
	}

	/**
	 * Load any existing compatability modules that allow this plugin to work
	 * alongside other plugins.
	 */
	function load_modules()
	{
		$dir = trailingslashit( WP_PLUGIN_DIR ) . basename( $this->dir_name ) . '/modules/';
		if ( $handle = opendir( $dir ) ) {
		    while ( false !== ( $filename = readdir( $handle ) ) ) {
		    	$file = $dir . $filename;
		        if ( @is_file( $file ) && preg_match( '/\.module\.php$/', $file ) ) {
		        	include_once $file;
		        }
		    }
	    	closedir( $handle );
		}
	}

	/**
	 * Add any admin only hooks and filters if the current user is capable of
	 * modifying permalinks...
	 */
	function admin_init()
	{
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->permalink_settings();
		if ( $this->rewrite_enabled() ) {
			add_action( 'save_post', array( &$this, 'save_post' ) );
			add_filter( 'get_sample_permalink_html', array( &$this, 'get_sample_permalink_html' ), 10, 4 );
			add_action( 'admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'), 10, 1 );
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
		}
	}

	/**
	 * Add the page permalink option to the Settings > Permalinks page, under
	 * the Optional section.
	 */
	function permalink_settings()
	{
		foreach ( $this->structure_tags AS $name => $settings ) {
			add_settings_field(
				$this->tag . '_' . $name,
				ucfirst( $settings['name'] ) . ' permalink',
				array( &$this, 'settings_permalink_input' ),
				'permalink',
				'optional',
				array_merge( array( 'id' => $name), $settings )
			);
		}
		// Refresh the locally stored page structure...
		$this->structures = $this->validate_structures();
	}

	/**
	 * Check the request to see if the current request is a custom permalink or
	 * alias and if so lookup the destination page or post.
	 *
	 * @param object $query
	 */
	function request( $query )
	{
		if ( count( $query ) == 0 )
			return $query;
		// Parse the page request and fetch the URI...
		$request = $this->parse_request();
		if ( $request_uri = untrailingslashit( $request['wp_page_path'] ) ) {
			if ( ! ( $post = $this->get_page_by_path( $request_uri ) ) ) {
				$request_uri = untrailingslashit( urldecode( $request_uri ), '/' );
				// Lookup the original post based on the full request...
				if ( ! ( $post = $this->get_post_by_custom_permalink( $request_uri ) ) ) {
					// Check to see if we are looking up an alias...
					if ( $post = $this->get_post_by_custom_permalink( $request_uri, false, '_alias' ) ) {
						wp_redirect( get_permalink( $post->ID ), 301 ); exit;
					}
					// Check to see if we might be requesting something with pagination...
					if ( ! preg_match( '#/(\d+)/$#', trailingslashit( $request_uri ), $paged_request ) ) {
						return $query;
					}
					list( $paginated, $page ) = $paged_request;
					// Remove the pagination number and try looking up again...
					if ( $request_uri = str_replace( $paginated, '', trailingslashit( $request_uri ) ) ) {
						$post = $this->get_post_by_custom_permalink( $request_uri );
					}
				}
			}
			if ( $post ) {
				// Get the original permalink...
				$this->remove_filters( $this->individual_permalink_filters );
				$request = apply_filters(
					'permalink_editor_request',
					str_replace( home_url(), '', get_permalink( $post->ID ) )
				);
				$_SERVER['REQUEST_URI'] = $request;
				if ( isset( $_SERVER['PATH_INFO'] ) ) {
					$_SERVER['PATH_INFO'] = $request;
				}
				// Parse the newly created / pseudo request...
				global $wp;
				$wp->parse_request();
				$query = $wp->query_vars;
				// Set the page number if required...
				if ( isset( $page ) ) {
					$query['page'] = $page;
				}
				// Replace the filters we removed earlier...
				$this->add_filters( $this->individual_permalink_filters );
			}
		}
		return $query;
	}

	/**
	 * Parses the current page request to determine the actual page path if WP
	 * is installed within a sub directory.
	 */
	function parse_request()
	{
		// Parse the current page request...
		$request = parse_url( $_SERVER['REQUEST_URI'] );
		// Now parse the WP install directory...
		$install = parse_url( get_bloginfo( 'wpurl' ) );
		if ( ! isset( $install['path'] ) ) {
			$install['path'] = '/';
		}
		$request['wp_page_path'] = str_replace(
			trailingslashit( $install['path'] ), '/', $request['path']
		);
		return $request;
	}

	/**
	 * Perform a slightly more robust get_page_by_path lookup which loops through all
	 * of the available public post types.
	 *
	 * @param string $path
	 * @return object|false
	 */
	function get_page_by_path( $path )
	{
		if ( $post_types = get_post_types( array( 'public' => true ), 'names' ) ) {
			foreach ( $post_types AS $post_type ) {
				if ( $post = get_page_by_path( trim( $path, '/' ), OBJECT, $post_type ) ) {
					return $post;
				}
			}
		}
		return false;
	}

	/**
	 * Fetch a single post based on the custom permalink value stored as custom
	 * meta data.
	 *
	 * @param string $permalink
	 */
	function get_post_by_custom_permalink( $permalink, $exclude = false, $suffix = '' )
	{
		$post = false;
		if ( $front = $this->front() ) {
			$permalink = str_replace( $front, '', $permalink );
		}
		// Fetch all the public post types to lookup against...
		if ( $post_types = get_post_types( array( 'public' => true ), 'names' ) ) {
			if ( $posts = get_posts( array(
				'post_type' => $post_types,
				'meta_key' => '_' . $this->tag . $suffix,
				'meta_value' => $permalink,
				'posts_per_page' => 1,
				'exclude' => $exclude
			) ) ) {
				$post = array_shift( $posts );
			} else if ( substr( $permalink, 0, 1 ) == '/' ) {
				$post = $this->get_post_by_custom_permalink(
					ltrim( $permalink, '/' ), $exclude, $suffix
				);
			} else if ( substr( $permalink, -1 ) != '/' ) {
				$post = $this->get_post_by_custom_permalink(
					trailingslashit( $permalink ), $exclude, $suffix
				);
			}
		}
		return $post;
	}

	/**
	 * Adds JavaScript file to the edit page for handling the permalink editing.
	 *
	 * @param string $hook
	 */
	function admin_enqueue_scripts( $hook )
	{
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			$dirname = plugin_basename( pathinfo( __FILE__, PATHINFO_DIRNAME ) );
			wp_enqueue_script(
				$dirname,
				WP_PLUGIN_URL . '/' . $dirname . '/admin.js'
			);
		}
	}

	/**
	 * Generates the custom permalink for an individual post, page or custom
	 * post type.
	 *
	 * @param string $permalink
	 * @param integer|object $page
	 */
	function page_link( $permalink, $page )
	{
		if ( is_object( $page ) ) {
			$page = $page->ID;
		}
		if ( ( 'page' == get_option( 'show_on_front' ) )
			&& ( $page == get_option( 'page_on_front' ) )
		) {
			$permalink = home_url( '/' );
		} else if ( $custom = get_post_meta( $page, '_' . $this->tag, true ) ) {
			$permalink = trailingslashit( get_bloginfo( 'url' ) ) . $this->front( ) . ltrim( $custom, '/' );
		}
		return apply_filters( 'permalink_editor_page_link', $permalink );
	}

	/**
	 * Adds meta boxes to the page and post editing page allowing an individual
	 * permalink and an alias to be specified.
	 */
	function add_meta_boxes()
	{
		// By default we'll add metaboxes to all public post types...
		$post_types = get_post_types( array(
			'public' => true
		) );
		foreach ( $post_types AS $post_type ) {
			add_meta_box(
				'custompermalinkdiv',
				'Custom Permalink',
	            array( &$this, 'permalink_meta_box'),
	            $post_type,
	            'normal',
	            'low'
			);
			add_meta_box(
				'custompermalinkaliasdiv',
				'Permalink Alias',
	            array( &$this, 'alias_meta_box'),
	            $post_type,
	            'normal',
	            'low'
			);
		}
	}

	/**
	 * Meta box for editing a custom permalink per post or page.
	 *
	 * @param object $post
	 * @param mixed $metabox
	 */
	function permalink_meta_box( $post, $metabox )
	{
		$value = get_post_meta( $post->ID, '_' . $this->tag, true );
		wp_nonce_field( plugin_basename( __FILE__ ), $this->tag . '_nonce' );
		?>
		<label for="<?php echo $this->tag; ?>">
			<span id="edit-custom-permalink">
				<?php echo trailingslashit( get_option( 'home' ) ); ?>
		 		<input type="text"
		 			id="<?php echo $this->tag; ?>"
		 			name="<?php echo $this->tag; ?>"
		 			value="<?php esc_html_e( $this->reformat_permalink( $value, '' ) ); ?>"
		 			size="40"
		 		/>
	 		</span>
 		</label>
 		<?php
	}

	/**
	 * Output the permalink editing form with the option to fully customise the
	 * slug alongside the default editing option.
	 *
	 * NOTE: A custom filter is applied here, allowing you to modify the
	 * permalink structure via external plugins by adding a filter:
	 * add_filter( 'get_custom_permalink_sample', 'callback_function', 1, 2 );
	 *
	 * @param string $html
	 * @param int|object $id
	 * @param string $new_title
	 * @param string $new_slug
	 */
	function get_sample_permalink_html( $html, $id, $new_title, $new_slug )
	{
		$post = &get_post( $id );
		// Get the current original...
		list( $permalink, $post_name ) = get_sample_permalink( $post->ID, $new_title, $new_slug );
		// Define the home url...
		$home_url = home_url( '/' );
		// Fetch the default permalink...
		$this->remove_filters( $this->individual_permalink_filters );
		list( $default, ) = get_sample_permalink( $post->ID );
		// Build the default permalink and replace any tokens...
		$default_permalink = apply_filters(
			'get_custom_permalink_sample',
			$this->build_permalink( $default, $post_name ),
			$post
		);
		$this->add_filters( $this->individual_permalink_filters );
		// Set the permalink to the new one...
		if ( isset( $_REQUEST['custom_slug'] ) ) {
			$custom_slug = $this->reformat_permalink( $_REQUEST['custom_slug'], '' );
			if ( ! empty( $custom_slug ) && ! $this->permalinks_match( $custom_slug, $default_permalink ) ) {
				$post_name = $this->unique_custom_permalink( $post->ID, $custom_slug );
				$permalink = $home_url . $post_name;
			} else {
				$permalink = $default;
			}
		} else if ( $new_slug ) {
			$permalink = $default;
		} else if ( $custom = get_post_meta( $id, '_' . $this->tag, true ) ) {
			$post_name = ltrim( $custom, '/' );
			$permalink = $home_url . $post_name;
		}
		// By default we will display the permalink as it is...
		$view_link = $permalink;
		// Fetch the post type label and set the edit title...
		if ( 'publish' == $post->post_status ) {
			$post_type = get_post_type_object( $post->post_type );
			$view_post = $post_type->labels->view_item;
			$title = __( 'Click to edit this part of the permalink' );
		} else {
			$title = __( 'Temporary permalink. Click to edit this part.' );
		}
		// Run the permalink through our custom filter...
		$permalink = apply_filters( 'get_custom_permalink_sample', remove_accents( $permalink ), $post );
		// Highlight the post name in the permalink...
		$post_name_html = '<span id="editable-post-name" title="' . $title . '">' . $post_name . '</span>';
		ob_start();
		?>
		<strong><?php _e( 'Permalink:' ); ?></strong>
		<?php
		if ( false === strpos( $permalink, '%postname%' ) && false === strpos( $permalink, '%pagename%' ) ) {
			$display_link = str_replace( $permalink, $post_name_html, $view_link );
		?>
			<?php echo $home_url; ?><span id="sample-permalink"><?php echo $display_link; ?></span>
		<?php
		} else {
			$view_link = $home_url . $this->build_permalink( $permalink, $post_name );
			$display_link = $this->build_permalink( $permalink, $post_name_html );
		?>
			<?php echo $home_url; ?><span id="sample-permalink"><?php echo $display_link; ?></span>
			&lrm;
			<span id="edit-slug-buttons">
				<a href="#post_name"
					class="edit-slug button hide-if-no-js"
					onclick="editOriginalPermalink(<?php echo $id; ?>); return false;"><?php _e( 'Edit' )?></a>
			</span>
		<?php
		}
		?>
			<span id="customise-permalink-buttons">
				<a href="#"
					class="customise-permalink button hide-if-no-js"
					onclick="editCustomPermalink(<?php echo $id; ?>); return false;"><?php _e( 'Customise' )?></a>
			</span>
			<span id="editable-post-name-full"><?php echo $post_name; ?></span>
		<?php
		// If the post is publicly viewable, display permalink view options...
		if ( isset( $view_post ) ) {
		?>
			<span id="view-post-btn">
				<a href="<?php echo $view_link; ?>"
					class="button"
					target="_blank"><?php echo $view_post; ?></a>
			</span>
			<?php if ( $new_title && ( $shortlink = wp_get_shortlink( $post->ID, $post->post_type ) ) ) { ?>
    		<input id="shortlink" type="hidden" value="<?php esc_attr_e( $shortlink ); ?>" />
    		<a href="#"
    			class="button hide-if-nojs"
    			onclick="prompt( 'URL:', jQuery( '#shortlink' ).val() ); return false;"><?php _e( 'Get Shortlink' ); ?></a>
			<?php } ?>
		<?php
		}
		$return = ob_get_contents(); ob_end_clean();
		return $return;
	}

	/**
	 * Check that a permialink is unique and if not append a suffix, for
	 * exmaple "/post.html" becomes "/post.html2".
	 *
	 * @param object $post
	 * @param string $permalink
	 */
	function unique_custom_permalink( $post_id, $permalink )
	{
		$slug = $unique = '/' . str_replace( home_url( '/' ), '', $permalink );
		if ( $this->get_post_by_custom_permalink( $slug, $post_id ) ) {
			$suffix = 2;
			do {
				$slug = $unique . $suffix;
				$check = $this->get_post_by_custom_permalink( $slug, $post_id );
				$suffix++;
			} while ( $check );
		}
		return ltrim( $slug, '/' );
	}

	/**
	 * Adds meta box for entering a permalink alias / redirect to an individual
	 * page or post.
	 *
	 * @param object $post
	 * @param mixed $metabox
	 */
	function alias_meta_box( $post, $metabox )
	{
		$name = $this->tag . '_alias';
		$value = get_post_meta( $post->ID, '_' . $name, true );
		?>
		<label for="<?php echo $name; ?>">
			<?php esc_html_e( trailingslashit( get_option( 'home' ) ) ); ?>
			<input type="text"
	 			id="<?php echo $name; ?>"
	 			name="<?php echo $name; ?>"
	 			value="<?php esc_html_e( $this->reformat_permalink( $value, '' ) ); ?>"
	 			size="40" />
		</label>
		&rarr; <code><?php esc_html_e( str_replace( home_url(), '', get_permalink( $post->ID ) ) ); ?></code>
 		<?php
	}

	/**
	 * Update the custom permalink and alias values when a post is updated.
	 *
	 * @param int $post_id
	 */
	function save_post( $post_id )
	{
		if ( ! isset( $_POST[$this->tag . '_nonce'] ) ||
			! wp_verify_nonce( $_POST[$this->tag . '_nonce'], plugin_basename( __FILE__ ) )
		) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		$fields = array(
			$this->tag,
			$this->tag . '_alias'
		);
		foreach ( $fields AS $field ) {
			if ( isset( $_POST[$field] ) ) {
				$value = $this->reformat_permalink( $_POST[$field], '' );
				$key = '_' . $field;
				if ( empty( $value ) ) {
					delete_post_meta( $post_id, $key, $value );
				} else if ( ! update_post_meta( $post_id, $key, $value ) ) {
					add_post_meta( $post_id, $key, $value, true );
				}
			}
		}
		// Refresh rewrite rules...
		$this->generate_rewrite_rules( true );
		return $fields;
	}

	/**
	 * Display the input field on the settings page and prefill it with the
	 * current value.
	 */
	function settings_permalink_input( $settings )
	{
		if ( $this->rewrite_enabled() ) {
			extract( $settings );
			$value = isset( $this->structures[$id] ) ? $this->structures[$id] : null;
		?>
		<input id="<?php echo $this->tag . '_' . $id; ?>"
			name="<?php echo $this->tag . '[' . $id . ']' ?>"
			class="regular-text code"
			type="text"
			value="<?php esc_attr_e( $value ); ?>"
		/>
		Must contain the <code><?php echo $tag; ?></code>
		structure tag e.g. <code>/<?php echo $name; ?>/<?php echo $tag; ?>.html</code>.
		<?php
		} else {
			 if ( 'post' == $settings['id'] ) {
		?>
		<code><?php echo get_option( 'home' ) . '?page_id=123'; ?></code>
		<?php
			}
		?>
		<span class="description">
			Cannot be changed as you are using the default permalink
			structure defined above.
		</span>
		<?php
		}
	}

	/**
	 * Ensure the custom permalinks are valid, and if not output an error message
	 * to the user and do not update the permalink.
	 *
	 * TODO: Ideally this would be called via the register_setting callback,
	 * however it appears that this is currently not possible so we have to
	 * check manually. (http://core.trac.wordpress.org/ticket/9296)
	 */
	function validate_structures()
	{
		if ( ! isset( $_REQUEST[$this->tag] ) || ! is_array( $_REQUEST[$this->tag] )
		) {
			return $this->structures;
		}
		$structures = $_REQUEST[$this->tag];
		foreach ( $this->structure_tags AS $key => $settings ) {
			$tag = $settings['tag'];
			if ( ! isset( $structures[$key] ) || empty( $structures[$key] ) ) {
				if ( is_array( $this->structures ) ) {
					unset( $this->structures[$key] );
				}

			} else if ( ! stristr( $structures[$key], $tag ) ) {
				add_settings_error(
					$this->tag . '_' . $tag,
					$this->tag . '_' . $tag . '_error',
					ucfirst( $key ) . ' permalink must contain the ' . $tag . ' structure tag.',
					'error'
				);
			} else {
				$valid = $this->reformat_permalink( $structures[$key] );
				$this->structures[$key] = $valid;
			}
		}
		if ( count( $this->structures ) > 0 ) {
			update_option( $this->tag, $this->structures );
		} else {
			delete_option( $this->tag );
		}
		// Flush the rewrite rules...
		$this->generate_rewrite_rules( true );
		return $this->structures;
	}

	/**
	 * Cleans the permalink and removes any unwanted characters.
	 *
	 * @param string $permalink
	 * @param string $prefix
	 */
	function reformat_permalink( $permalink, $prefix = '/' )
	{
		if ( empty( $permalink ) )
			return null;
		// Basic sanitize functionality...
		$permalink = apply_filters( 'sanitize_text_field', $permalink );
		// Replace hashes and white space...
		$permalink = str_replace( array( '#', ' ' ), array( '', '-' ), $permalink );
		// Remove multiple slashes...
		$permalink = preg_replace(
			array( '#\-+#', '#/+#', '#\.+#' ),
			array( '-', '/', '.' ),
			remove_accents( $permalink )
		);
		// Return formatted permalink with prefix...
		return $prefix . ltrim( $permalink, '/' );
	}

	/**
	 * Regenerate all of the required rewrite rules, and optionally flush the
	 * list of existing rules.
	 *
	 * @param boolean $flush Set to true in order to flush the rules.
	 */
	function generate_rewrite_rules( $flush = false )
	{
		// Fetch the custom structure options...
		if ( $this->structures = get_option( $this->tag ) ) {
			global $wp_rewrite;
			foreach ( $this->structure_tags AS $name => $settings ) {
				if ( isset( $this->structures[$name] ) ) {
					$structure = $this->front( '' ) . $this->structures[$name];
					// Ignore if custom structure is exactly the same as the default...
					if ( $settings['tag'] == trim( $structure, '/' ) )
						continue;
					// Append the base value...
					$structure = $this->with_base( $name, $structure );
					global $wp_version;
					if ( version_compare( $wp_version, '3.0.5', '>' ) ) {
						$wp_rewrite->add_permastruct( $name, $structure, false );
						if ( in_array( $name, array( 'page', 'author' ) ) )
							$wp_rewrite->{$name . '_structure'} = $structure;
					} else {
						$wp_rewrite->{$name . '_structure'} = $structure;
					}
				}
			}
			if ( true == $flush )
	   			flush_rewrite_rules( false );
			return true;
		}
		return false;
	}

	/**
	 * Replaces permalink tokens and home url to provide a page path, for
	 * example: "2011/01/26/about/"
	 *
	 * @param string $permalink
	 * @param string $post_name
	 */
	function build_permalink( $permalink, $post_name )
	{
		return str_replace(
			array( trailingslashit( home_url() ), '%pagename%', '%postname%' ),
			array( '', $post_name, $post_name ),
			$permalink
		);
	}

	/**
	 * Quick test to see if two permalinks appear to be the same.
	 *
	 * @param string $a
	 * @param string $b
	 */
	function permalinks_match( $a, $b )
	{
		return ( trailingslashit( trim( $a ) ) == trailingslashit( trim( $b ) ) );
	}

	/**
	 * Prepend the base value to permalink structures (category / tag / author).
	 *
	 * @param string $type
	 * @param string $structure
	 */
	function with_base( $type, $structure )
	{
		$prefix = '';
		if ( $type != 'page' ) {
			$prefix = get_option( $type . '_base', '' );
			if ( empty( $prefix ) && substr( $structure, 1, 1 ) == '%' ) {
				$prefix = $type;
			}
		}
		return trailingslashit( $prefix ) . ltrim( $structure, '/' );
	}

	/**
	 * Return the url prefix including the base path and index file if we are
	 * using index permalinks.
	 *
	 * @param string $after
	 * @return string|null
	 */
	function front( $after = '/' )
	{
		global $wp_rewrite;
		if ( $wp_rewrite->using_index_permalinks() ) {
			return 'index.php' . $after;
		}
		return null;
	}

	/**
	 * Remove the trailing slash from page permalinks that have an extension,
	 * such as /page/%pagename%.html.
	 *
	 * @param string $request
	 */
	function trailingslash( $request )
	{
		if ( pathinfo( $request, PATHINFO_EXTENSION ) ) {
			return untrailingslashit( $request );
		}
		return trailingslashit( $request );
	}

	/**
	 * Very simple check to see whether or not we are using a custom permalink
	 * structure.
	 */
	function rewrite_enabled()
	{
		global $wp_rewrite;
		if ( $wp_rewrite->using_permalinks() ) {
			return true;
		}
		return false;
	}

	/**
	 * Quick method of adding multiple filters in a single call.
	 *
	 * @param array $filters
	 */
	function add_filters( $filters )
	{
		foreach ( $filters AS $filter => $callback ) {
			add_filter( $filter, array( &$this, $callback ), 10, 2 );
		}
	}

	/**
	 * As we can quickly add filters, this does the opposite and removes them.
	 *
	 * @param array $filters
	 */
	function remove_filters( $filters )
	{
		foreach ( $filters AS $filter => $callback ) {
			remove_filter( $filter, array( &$this, $callback ), 10, 2 );
		}
	}

}

$PermalinkEditor = new Permalink_Editor();

?>