<?php

class YARPP_Admin {

	/**
	 * @var YARPP
	 */
	public $core;
	public $hook;

	const ACTIVATE_TIMESTAMP_OPTION = 'yarpp_activate_timestamp';
	const REVIEW_DISMISS_OPTION     = 'yarpp_review_notice';
	const REVIEW_FIRST_PERIOD       = 518400; // 6 days in seconds
	const REVIEW_LATER_PERIOD       = 5184000; // 60 days in seconds
	const REVIEW_FOREVER_PERIOD     = 63113904; // 2 years in seconds

	function __construct( &$core ) {
		$this->core = &$core;

		/* If action = flush and the nonce is correct, reset the cache */
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'flush' && check_ajax_referer( 'yarpp_cache_flush', false, false ) !== false ) {
			$this->core->cache->flush();
			wp_safe_redirect( admin_url( '/options-general.php?page=yarpp' ) );
			exit;
		}

		/* If action = copy_templates and the nonce is correct, copy templates */
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'copy_templates' && check_ajax_referer( 'yarpp_copy_templates', false, false ) !== false ) {
			$this->copy_templates();
			wp_safe_redirect( admin_url( '/options-general.php?page=yarpp' ) );
			exit;
		}

		add_action( 'admin_init', array( $this, 'ajax_register' ) );
		add_action( 'admin_init', array( $this, 'review_register' ) );
		add_action( 'admin_menu', array( $this, 'ui_register' ) );
		add_action( 'save_post', array( $this, 'yarpp_save_meta_box' ) );

		add_filter( 'current_screen', array( $this, 'settings_screen' ) );
		add_filter( 'default_hidden_meta_boxes', array( $this, 'default_hidden_meta_boxes' ), 10, 2 );
		add_filter( 'shareaholic_deactivate_feedback_form_plugins', array( $this, 'deactivation_survey_data' ) );
	}

	/**
	 * @since 4.0.3 Moved method to Core.
	 */
	public function get_templates() {
		return $this->core->get_templates();
	}

	/**
	 * Register Review notice
	 */
	function review_register() {
		self::check_review_dismissal();
		self::check_plugin_review();
	}

	/**
	 * Register AJAX services
	 */
	function ajax_register() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_yarpp_display_exclude_terms', array( $this, 'ajax_display_exclude_terms' ) );
			add_action( 'wp_ajax_yarpp_display_demo', array( $this, 'ajax_display_demo' ) );  // deprecated action and function
			add_action( 'wp_ajax_yarpp_display_preview', array( $this, 'ajax_display_preview' ) );
			add_action( 'wp_ajax_yarpp_display', array( $this, 'ajax_display' ) );
			add_action( 'wp_ajax_yarpp_optin_data', array( $this, 'ajax_optin_data' ) );
			add_action( 'wp_ajax_yarpp_optin_enable', array( $this, 'ajax_optin_enable' ) );
			add_action( 'wp_ajax_yarpp_optin_disable', array( $this, 'ajax_optin_disable' ) );
			add_action( 'wp_ajax_yarpp_switch', array( $this, 'ajax_switch' ) );
			add_action( 'wp_ajax_yarpp_clear_cache', array( $this, 'ajax_clear_cache' ) );
		}
	}
	/**
	 * Ajax callback for clearing the YARPP cache
	 *
	 * @since 5.13.0
	 */
	public function ajax_clear_cache() {
		if ( false === check_ajax_referer( 'clear_cache_yarpp', false, false ) ) {
			echo 'nonce_fail';
		} elseif ( current_user_can( 'manage_options' ) ) {
			$this->core->cache->flush();
			echo 'success';
		} else {
			echo 'forbidden';
		}
		wp_die();
	}
	/**
	 * Check review notice status for current user
	 *
	 * @since 5.1.0
	 */
	public static function check_review_dismissal() {

		global $current_user;
		$user_id = $current_user->ID;

		if ( ! is_admin() ||
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( $_GET['_wpnonce'], 'review-nonce' ) ||
		! isset( $_GET['yarpp_defer_t'] ) ||
		! isset( $_GET[ self::REVIEW_DISMISS_OPTION ] ) ) {
			return;
		}

		$the_meta_array = array(
			'dismiss_defer_period' => $_GET['yarpp_defer_t'],
			'dismiss_timestamp'    => time(),
		);

		update_user_meta( $user_id, self::REVIEW_DISMISS_OPTION, $the_meta_array );
	}

	/**
	 * Check if we should display the review notice
	 *
	 * @since 5.1.0
	 */
	public static function check_plugin_review() {

		global $current_user;
		$user_id = $current_user->ID;

		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		$show_review_notice     = false;
		$activation_timestamp   = get_site_option( self::ACTIVATE_TIMESTAMP_OPTION );
		$review_dismissal_array = get_user_meta( $user_id, self::REVIEW_DISMISS_OPTION, true );
		$dismiss_defer_period   = isset( $review_dismissal_array['dismiss_defer_period'] ) ? $review_dismissal_array['dismiss_defer_period'] : 0;
		$dismiss_timestamp      = isset( $review_dismissal_array['dismiss_timestamp'] ) ? $review_dismissal_array['dismiss_timestamp'] : time();

		if ( $dismiss_timestamp + $dismiss_defer_period <= time() ) {
			$show_review_notice = true;
		}

		if ( ! $activation_timestamp ) {
			$activation_timestamp = time();
			add_site_option( self::ACTIVATE_TIMESTAMP_OPTION, $activation_timestamp );
		}

		// display review message after a certain period of time after activation
		if ( ( time() - $activation_timestamp > self::REVIEW_FIRST_PERIOD ) && $show_review_notice == true ) {
			add_action( 'admin_notices', array( 'YARPP_Admin', 'display_review_notice' ) );
		}
	}

	/**
	 * @since 5.1.0
	 */
	public static function display_review_notice() {

		$dismiss_forever = add_query_arg(
			array(
				self::REVIEW_DISMISS_OPTION => true,
				'yarpp_defer_t'             => self::REVIEW_FOREVER_PERIOD,
			)
		);

		$dismiss_forlater = add_query_arg(
			array(
				self::REVIEW_DISMISS_OPTION => true,
				'yarpp_defer_t'             => self::REVIEW_LATER_PERIOD,
			)
		);

		$dismiss_forever_url  = wp_nonce_url( $dismiss_forever, 'review-nonce' );
		$dismiss_forlater_url = wp_nonce_url( $dismiss_forlater, 'review-nonce' );

		echo '
      <style>
        .yarpp-review-notice {
          background-size: contain; background-position: right bottom; background-repeat: no-repeat; background-image: url(' . plugins_url( '../images/icon-256x256.png', __FILE__ ) . ');
        }
         .yarpp-review-notice-text {
           background: rgba(255, 255, 255, 0.9); text-shadow: white 0px 0px 10px; margin-right: 8em !important;
        }
        
        @media only screen and (max-width: 782px) {
          .yarpp-review-notice-text {
            margin-right: 12em !important;
         }
        }
        @media screen and (max-width: 580px) {
          .yarpp-review-notice {
            background: #ffffff;
          }
          .yarpp-review-notice-text {
            margin-right: 0 !important;
         }
        }
      </style>
      
      <script>
        function yarpp_openWindowReload(link, reload) {
          window.open(link, "_blank");
          document.location.href = reload;
        }
      </script>	
      
    <div class="notice notice-info is-dismissible yarpp-review-notice">
      <p class="yarpp-review-notice-text">' . __( 'Hey there! We noticed that you have had success using ', 'yet-another-related-posts-plugin' ) . '<a href="' . admin_url( 'options-general.php?page=yarpp' ) . '">YARPP - Related Posts</a>! ' . __( 'Could you please do us a BIG favor and give us a quick 5-star rating on WordPress? It will boost our motivation and spread the word. We would really appreciate it 🤗 — Team YARPP', 'yet-another-related-posts-plugin' ) . '
        <br />
        <br />
        <a onClick="' . "yarpp_openWindowReload('https://wordpress.org/support/plugin/yet-another-related-posts-plugin/reviews/?rate=5#new-post', '$dismiss_forever_url')" . '" class="button button-primary">' . __( 'Ok, you deserve it', 'yet-another-related-posts-plugin' ) . '</a> &nbsp;
        <a href="' . $dismiss_forlater_url . '">' . __( 'No, not good enough', 'yet-another-related-posts-plugin' ) . '</a> &nbsp;
        <a href="' . $dismiss_forever_url . '">' . __( 'I already did', 'yet-another-related-posts-plugin' ) . '</a>  &nbsp;
        <a href="' . $dismiss_forever_url . '">' . __( 'Dismiss', 'yet-another-related-posts-plugin' ) . '</a>
      </p>
    </div>';
	}

	function ui_register() {
		global $wp_version;

		if ( $this->core->db_options->after_activation() ) {

			$this->core->db_options->delete_activation_flag();
			$this->core->db_options->delete_upgrade_flag();

			/* Optin/Pro message */
			add_action( 'admin_notices', array( $this, 'install_notice' ) );

		} elseif ( $this->core->db_options->after_upgrade() && current_user_can( 'manage_options' ) && $this->core->get_option( 'optin' ) ) {
			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
		}

		if ( $this->core->get_option( 'optin' ) ) {
			$this->core->db_options->delete_upgrade_flag();
		}

		/*
		* Setup Admin
		*/
		$titleName  = 'YARPP';
		$this->hook = add_options_page( $titleName, $titleName, 'manage_options', 'yarpp', array( $this, 'options_page' ) );

		/**
		* @since 3.0.12  Add settings link to the plugins page.
		*/
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );

		$metabox_post_types = $this->core->get_option( 'auto_display_post_types' );
		if ( ! in_array( 'post', $metabox_post_types ) ) {
			$metabox_post_types[] = 'post';
		}

		/**
		* @since 3.0  Add meta box in Editor
		*/
		if ( ! $this->core->yarppPro['active'] ) {
			foreach ( $metabox_post_types as $post_type ) {
				$title = __( 'YARPP: Related Posts', 'yet-another-related-posts-plugin' );
				add_meta_box( 'yarpp_relatedposts', $title, array( $this, 'metabox' ), $post_type, 'normal' );
			}
		}

		/**
		* @since 3.3: properly enqueue scripts for admin.
		*/
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * @since 3.5.4 Only load metabox code if we're going to be on the settings page.
	 */
	function settings_screen( $current_screen ) {
		if ( $current_screen->id !== 'settings_page_yarpp' ) {
			return $current_screen;
		}

		/**
		* @since 3.3: Load options page sections as meta-boxes.
		*/
		include_once YARPP_DIR . '/includes/yarpp_meta_boxes_hooks.php';

		/**
		* @since 3.5.5 Check that add_help_tab method callable (WP >= 3.3).
		*/
		if ( is_callable( array( $current_screen, 'add_help_tab' ) ) ) {
			$current_screen->add_help_tab(
				array(
					'id'       => 'faq',
					'title'    => __( 'Frequently Asked Questions', 'yet-another-related-posts-plugin' ),
					'callback' => array( &$this, 'help_faq' ),
				)
			);

			$current_screen->add_help_tab(
				array(
					'id'       => 'dev',
					'title'    => __( 'Developing with YARPP', 'yet-another-related-posts-plugin' ),
					'callback' => array( &$this, 'help_dev' ),
				)
			);

			$current_screen->add_help_tab(
				array(
					'id'       => 'optin',
					'title'    => __( 'Optional Data Collection', 'yet-another-related-posts-plugin' ),
					'callback' => array( &$this, 'help_optin' ),
				)
			);
		}

		return $current_screen;
	}

	private $readme = null;

	public function help_faq() {
		if ( is_null( $this->readme ) ) {
			$this->readme = file_get_contents( YARPP_DIR . '/readme.txt' );
		}

		if ( preg_match( '!== Frequently Asked Questions ==(.*?)^==!sm', $this->readme, $matches ) ) {
			echo $this->markdown( $matches[1] );
		} else {
			echo(
			'<a href="https://wordpress.org/plugins/yet-another-related-posts-plugin/#faq">' .
			  __( 'Frequently Asked Questions', 'yet-another-related-posts-plugin' ) .
			'</a>'
			);
		}
	}

	public function help_dev() {
		if ( is_null( $this->readme ) ) {
			$this->readme = file_get_contents( YARPP_DIR . '/readme.txt' );
		}

		if ( preg_match( '!== Developing with YARPP ==(.*?)^==!sm', $this->readme, $matches ) ) {
			echo $this->markdown( $matches[1] );
		} else {
			echo(
			'<a href="https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation" target="_blank">' .
			  __( 'Developing with YARPP', 'yet-another-related-posts-plugin' ) .
			'</a>'
			);
		}
	}

	public function help_optin() {
		echo(
			'<p>' .
				__(
					"With your permission, YARPP will send information about YARPP's settings, usage, and environment
                    back to a central server at ",
					'yet-another-related-posts-plugin'
				) . '<code>yarpp.org</code>' . '.&nbsp;' .
			'</p>' .
			'<p>' .
				'We would really appreciate your input to help us continue to improve the product. We are primarily looking ' .
				'for country, domain, and date installed information.' .
			'</p>' .
			'<p>' .
				__(
				'This information will be used to improve YARPP in the future and help decide future development
                decisions for YARPP.',
				'yet-another-related-posts-plugin'
			) . ' ' .
			'</p>' .
			'<p>' .
			'<strong>' .
				__(
					'Contributing this data will help make YARPP better for you and for other YARPP users.',
					'yet-another-related-posts-plugin'
				) . '</strong>' .
			'</p>'
		);

		echo(
			'<p>' .
				__( 'The following information is sent back to YARPP:', 'yet-another-related-posts-plugin' ) .
			'</p>' .
		'<div id="optin_data_frame"></div>' .
			'<p>' .
				__( 'In addition, YARPP also loads an invisible pixel image with your YARPP results to know how often YARPP is being used.', 'yet-another-related-posts-plugin' ) .
			'</p>'
		);
	}

	function the_optin_button( $action, $echo = false ) {
		$status = ( $this->core->yarppPro['active'] ) ? 'disabled' : null;

		if ( $action === 'disable' ) {
			$out =
			'<a id="yarpp-optin-button' . $status . '" class="button" ' . $status . '>' .
			'No, Thanks. Please <strong>' . $action . '</strong> sending usage data' .
			'</a>';
		} else {
			$out =
			'<a id="yarpp-optin-button' . $status . '" class="button" ' . $status . '>' .
			'Yes, <strong>' . $action . '</strong> sending usage data back to help improve YARPP' .
			'</a>';
		}

		if ( $echo ) {
			echo $out;
			return null;
		} else {
			return $out;
		}
	}

	function the_donothing_button( $msg, $echo = false ) {
		$out = '<a href="options-general.php?page=yarpp" class="button">' . $msg . '</a>';
		if ( $echo ) {
			echo $out;
			return null;
		} else {
			return $out;
		}
	}

	function optin_button_script( $optinAction, $echo = false ) {
		wp_nonce_field( 'yarpp_optin_' . $optinAction, 'yarpp_optin-nonce', false );

		ob_start();
		include YARPP_DIR . '/includes/optin_notice.js.php';
		$out = ob_get_contents();
		ob_end_clean();

		if ( $echo ) {
			echo $out;
			return null;
		} else {
			return $out;
		}
	}

	function upgrade_notice() {
		$optinAction = ( $this->core->get_option( 'optin' ) ) ? 'disable' : 'enable';
		$this->optin_notice( 'upgrade', $optinAction );
	}

	public function install_notice() {
		$optinAction = ( $this->core->get_option( 'optin' ) ) ? 'disable' : 'enable';
		$this->optin_notice( 'install', $optinAction );
	}

	public function optin_notice( $type = false, $optinAction = 'disable' ) {
		$screen = get_current_screen();
		if ( is_null( $screen ) || $screen->id == 'settings_page_yarpp' ) {
			return;
		}

		switch ( $type ) {
			case 'upgrade':
				$this->core->db_options->delete_upgrade_flag();
				break;
			case 'install':
			default:
				$user = get_current_user_id();
				update_user_option( $user, 'yarpp_saw_optin', true );
		}

		$out = '<div class="updated fade"><p>';

		if ( $type === 'upgrade' ) {
			$out .= '<strong>' . sprintf( __( '%1$s updated successfully.', 'yet-another-related-posts-plugin' ), 'Yet Another Related Posts Plugin' ) . '</strong>';
		}

		if ( $type === 'install' ) {
			$tmp  = __( 'Thank you for installing <span>Yet Another Related Posts Plugin</span>!', 'yet-another-related-posts-plugin' );
			$out .= '<strong>' . str_replace( '<span>', '<span style="font-style:italic; font-weight: inherit;">', $tmp ) . '</strong>';
		}

		if ( $this->core->yarppPro['active'] ) {

			$out .=
			  '<p>' .
				  'You currently have <strong>YARPP Basic</strong> and <strong>YARPP Pro</strong> enabled.<br/><br/>' .
				  '<a href="options-general.php?page=yarpp" class="button">Take me to the settings page</a>' .
			  '</p>';

		} else {

			$out .= '</p><p>';
			if ( $optinAction !== 'disable' ) {
				$out .= $this->the_donothing_button( 'No, thanks' ) . '&nbsp;&nbsp;';
			} else {
				$out .= $this->the_donothing_button( 'Yes, keep sending usage data' ) . '&nbsp;&nbsp;';
			}
			$out .= $this->the_optin_button( $optinAction );
			$out .= $this->optin_button_script( $optinAction );

		}

		echo $out . '</div>';
	}

	// faux-markdown, required for the help text rendering
	protected function markdown( $text ) {
		$replacements = array(
			// strip each line
			'!\s*[\r\n] *!'                    => "\n",
			// headers
			'!^=(.*?)=\s*$!m'                  => '<h3>\1</h3>',
			// bullets
			'!^(\* .*([\r\n]\* .*)*)$!m'       => "<ul>\n\\1\n</ul>",
			'!^\* (.*?)$!m'                    => '<li>\1</li>',
			'!^(\d+\. .*([\r\n]\d+\. .*)*)$!m' => "<ol>\n\\1\n</ol>",
			'!^\d+\. (.*?)$!m'                 => '<li>\1</li>',
			// code block
			'!^(\t.*([\r\n]\t.*)*)$!m'         => "<pre>\n\\1\n</pre>",
			// wrap p
			'!^([^<\t].*[^>])$!m'              => '<p>\1</p>',
			// bold
			'!\*([^*]*?)\*!'                   => '<strong>\1</strong>',
			// code
			'!`([^`]*?)`!'                     => '<code>\1</code>',
			// links
			'!\[([^]]+)\]\(([^)]+)\)!'         => '<a href="\2" target="_new">\1</a>',
		);
		$text         = preg_replace( array_keys( $replacements ), array_values( $replacements ), $text );

		return $text;
	}

	/**
	 * @deprecated since 5.26.0
	 */
	public function render_screen_settings( $output, $current_screen ) {
		_deprecated_function( 'YARPP_Admin::render_screen_settings', '5.26.0');
		return '';
	}

	// since 3.3
	public function enqueue() {
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : YARPP_VERSION;
		$screen  = get_current_screen();
		if ( ! is_null( $screen ) && $screen->id === 'settings_page_yarpp' ) {
			wp_enqueue_style( 'yarpp_switch_options', plugins_url( 'style/options_switch.css', dirname( __FILE__ ) ), array(), $version );
			wp_enqueue_script( 'yarpp_switch_options', yarpp_get_file_url_for_environment( 'js/options_switch.min.js', 'src/js/options_switch.js' ), array( 'jquery' ), $version );

			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_style( 'yarpp_options', plugins_url( 'style/options_basic.css', dirname( __FILE__ ) ), array(), $version );
			wp_enqueue_style( 'yarpp_remodal', plugins_url( 'lib/plugin-deactivation-survey/remodal.css', dirname( __FILE__ ) ), array(), $version );
			wp_enqueue_style( 'yarpp_deactivate', plugins_url( 'lib/plugin-deactivation-survey/deactivate-feedback-form.css', dirname( __FILE__ ) ), array(), $version );
			wp_enqueue_style( 'yarpp_default_theme', plugins_url( 'lib/plugin-deactivation-survey/remodal-default-theme.css', dirname( __FILE__ ) ), array(), $version );

			wp_enqueue_script( 'postbox' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'yarpp_remodal', plugins_url( 'lib/plugin-deactivation-survey/remodal.min.js', dirname( __FILE__ ) ), array(), $version );
			wp_enqueue_script( 'yarpp_options', yarpp_get_file_url_for_environment( 'js/options_basic.min.js', 'src/js/options_basic.js' ), array( 'jquery' ), $version );
			// Localize the script with messages
			$translation_strings = array(
				'alert_message' => __( 'This will clear all of YARPP’s cached related results.<br> Are you sure?', 'yet-another-related-posts-plugin' ),
				'model_title'   => __( 'YARPP Cache', 'yet-another-related-posts-plugin' ),
				'success'       => __( 'Cache cleared successfully!', 'yet-another-related-posts-plugin' ),
				'logo'          => plugins_url( '/images/icon-256x256.png', YARPP_MAIN_FILE ),
				'bgcolor'       => '#fff',
				'forbidden'     => __( 'You are not allowed to do this!', 'yet-another-related-posts-plugin' ),
				'nonce_fail'    => __( 'You left this page open for too long. Please refresh the page and try again!', 'yet-another-related-posts-plugin' ),
				'error'         => __( 'There is some error. Please refresh the page and try again!', 'yet-another-related-posts-plugin' ),
				'show_code'     => __( 'Show Code', 'yet-another-related-posts-plugin' ),
				'hide_code'     => __( 'Hide Code', 'yet-another-related-posts-plugin' )
			);
			wp_localize_script( 'yarpp_options', 'yarpp_messages', $translation_strings );

			wp_enqueue_code_editor(array('type' => 'text/html'));
		}

		$metabox_post_types = $this->core->get_option( 'auto_display_post_types' );
		if ( ! is_null( $screen ) && ( $screen->id == 'post' || in_array( $screen->id, $metabox_post_types ) ) ) {
			wp_enqueue_script( 'yarpp_metabox', yarpp_get_file_url_for_environment( 'js/metabox.min.js', 'src/js/metabox.js' ), array( 'jquery' ), $version );
		}
	}

	public function settings_link( $links, $file ) {
		$this_plugin = dirname( plugin_basename( dirname( __FILE__ ) ) ) . '/yarpp.php';
		if ( $file == $this_plugin ) {
			$links[] = '<a href="options-general.php?page=yarpp">' . __( 'Settings' ) . '</a>';
		}
		return $links;
	}

	public function options_page() {
		$mode = ( isset( $_GET['mode'] ) ) ? htmlentities( strtolower( $_GET['mode'] ) ) : null;
		if ( $mode !== 'basic' && ( $mode === 'pro' || $this->core->yarppPro['active'] ) ) {
			include_once YARPP_DIR . '/includes/yarpp_pro_options.php';
		} else {
			include_once YARPP_DIR . '/includes/yarpp_options.php';
		}
	}
	/**
	 * Function to save the meta box.
	 *
	 * @param mixed $post_id Post ID.
	 */
	public function yarpp_save_meta_box( $post_id ) {
		$yarpp_meta = array();
		// Return if we're doing an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Verify our nonce here.
		if ( ! isset( $_POST['yarpp_display-nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['yarpp_display-nonce'] ), 'yarpp_display' ) ) {
			  return;
		}
		if ( isset( $_POST['yarpp_display_for_this_post'] ) ) {
			$yarpp_meta['yarpp_display_for_this_post'] = 1;
		} else {
			$yarpp_meta['yarpp_display_for_this_post'] = 0;
		}
		update_post_meta( $post_id, 'yarpp_meta', $yarpp_meta );
	}

	// @since 3.4: don't actually compute results here, but use ajax instead
	public function metabox() {
		global $post;
		$metabox_post_types = $this->core->get_option( 'auto_display_post_types' );
		$yarpp_meta         = get_post_meta( $post->ID, 'yarpp_meta', true );
		if ( isset( $yarpp_meta['yarpp_display_for_this_post'] ) && 0 === $yarpp_meta['yarpp_display_for_this_post'] ) {
			$yarpp_disable_here = 0;
		} else {
			$yarpp_disable_here = 1;
		}
		?>
	<style>
	  .yarpp-metabox-options {
		margin: 10px 0;
	  }
	   #yarpp-related-posts .spinner {
		float: none; visibility: hidden; opacity: 1; margin: 5px 7px 0 7px;
	  }
	</style>
		<?php if ( in_array( get_post_type(), $metabox_post_types ) ) { ?>
	  <p>
		<input type="checkbox" id="yarpp_display_for_this_post" name="yarpp_display_for_this_post" <?php checked( 1, $yarpp_disable_here, true ); ?> />
		<label for="yarpp_display_for_this_post"><strong><?php esc_html_e( 'Automatically display related content on this post', 'yet-another-related-posts-plugin' ); ?></strong></label>
		<br />
		<em><?php esc_html_e( 'If this is unchecked, then YARPP will not automatically insert the related posts at the end of this post.', 'yet-another-related-posts-plugin' ); ?></em>
	  </p>
	<?php } ?>
		<?php
		if ( ! get_the_ID() ) {
			echo '<div><p>' . __( 'Related posts will be displayed once you save this post', 'yet-another-related-posts-plugin' ) . '.</p></div>';
		} else {
			echo '<div id="yarpp-related-posts"><img height="20px" width="20px" src="' . esc_url( admin_url( 'images/spinner-2x.gif' ) ) . '" alt="loading..." /></div>';
		}
		wp_nonce_field( 'yarpp_display', 'yarpp_display-nonce', false );
	}

	// @since 3.3: default metaboxes to show:
	public function default_hidden_meta_boxes( $hidden, $screen ) {
		if ( $screen->id === 'settings_page_yarpp' ) {
			$hidden = $this->core->default_hidden_metaboxes;
		}
		return $hidden;
	}

	// @since 4: UI to copy templates
	public function can_copy_templates() {
		$theme_dir = get_stylesheet_directory();
		// If we can't write to the theme, return false
		if ( ! is_dir( $theme_dir ) || ! is_writable( $theme_dir ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem( false, get_stylesheet_directory() );
		global $wp_filesystem;
		// direct method is the only method that I've tested so far
		return $wp_filesystem->method === 'direct';
	}

	public function copy_templates() {
		$templates_dir = trailingslashit( trailingslashit( YARPP_DIR ) . 'yarpp-templates' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem( false, get_stylesheet_directory() );
		global $wp_filesystem;
		if ( $wp_filesystem->method !== 'direct' ) {
			return false;
		}

		return copy_dir( $templates_dir, get_stylesheet_directory(), array( '.svn' ) );
	}

	/*
	* AJAX SERVICES
	*/

	public function ajax_display_exclude_terms() {
		check_ajax_referer( 'yarpp_display_exclude_terms' );

		if ( ! isset( $_REQUEST['taxonomy'] ) ) {
			return;
		}

		$taxonomy = (string) $_REQUEST['taxonomy'];

		header( 'HTTP/1.1 200' );
		header( 'Content-Type: text/html; charset=UTF-8' );

		$exclude_tt_ids   = wp_parse_id_list( $this->core->get_option( 'exclude' ) );
		$exclude_term_ids = $this->get_term_ids_from_tt_ids( $taxonomy, $exclude_tt_ids );
		// if ('category' === $taxonomy) $exclude .= ','.get_option('default_category');

		$terms = get_terms(
			$taxonomy,
			array(
				'exclude'      => $exclude_term_ids,
				'hide_empty'   => false,
				'hierarchical' => false,
				'number'       => 100,
				'offset'       => $_REQUEST['offset'],
			)
		);

		if ( ! count( $terms ) ) {
			echo ':('; // no more :(
			exit;
		}

		foreach ( $terms as $term ) {
			echo "<span><input type='checkbox' name='exclude[{$term->term_taxonomy_id}]' id='exclude_{$term->term_taxonomy_id}' value='true' /> <label for='exclude_{$term->term_taxonomy_id}'>" . esc_html( $term->name ) . '</label></span> ';
		}
		exit;
	}

	public function get_term_ids_from_tt_ids( $taxonomy, $tt_ids ) {
		global $wpdb;
		$tt_ids = wp_parse_id_list( $tt_ids );
		if ( empty( $tt_ids ) ) {
			return array();
		}
		return $wpdb->get_col( "select term_id from $wpdb->term_taxonomy where taxonomy = '{$taxonomy}' and term_taxonomy_id in (" . join( ',', $tt_ids ) . ')' );
	}

	/**
	 * Handles populating the YARPP related metabox. When the page is initially loaded, this is called to populate it
	 * but $_REQUEST['refresh'] isn't set because we're happy using the cached results. But when the user clicks the
	 * "Refresh" button, $_REQUEST['refresh'] is set so we try to clear the cache and re-calculate the related content.
	 */
	public function ajax_display() {
		check_ajax_referer( 'yarpp_display' );

		if ( ! isset( $_REQUEST['ID'] ) ) {
			return;
		}

		$args = array(
			'domain' => isset( $_REQUEST['domain'] ) ? $_REQUEST['domain'] : 'website',
		);
		if ( isset( $_REQUEST['refresh'] ) && $this->core->cache instanceof YARPP_Cache ) {
			$this->core->cache->clear( $_REQUEST['ID'] );
		}
		$return = $this->core->display_related( absint( $_REQUEST['ID'] ), $args, false );

		header( 'HTTP/1.1 200' );
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo $return;

		die();
	}

	/**
	 * @deprecated since 5.26.0 use YARPP_Admin::ajax_display_preview() instead
	 * @see YARPP_Admin::ajax_display_preview()
	 */
	public function ajax_display_demo() {
		_deprecated_function( 'YARPP_Admin::ajax_display_demo', '5.26.0', 'YARPP_Admin::ajax_display_preview' );
		return $this->ajax_display_preview();
	}

	/**
	 * Generates a Demo Preview for YARPP core templates.
	 *
	 * AJAX Post Call
	 * Accepted Post parameters:
	 *
	 * @global int    $_POST['limit'].              Limit of Posts for display.
	 * @global string $_POST['template'].           Template to be selected. 'thumbnails' | 'list' | {custom template name}. default: 'list'
	 * @global string $_POST['order'].              Ordering the posts by: 'score DESC' | 'score ASC' | 'post_date DESC' | 'post_date ASC' | 'post_title ASC' | 'post_title DESC'. default: 'score DESC'
	 * @global bool   $_POST['promote_yarpp'].      YARPP promotional text
	 * @global string $_POST['thumbnails_heading']. Heading for the block
	 * @global string $_POST['thumbnails_default']. Default image for the thumbnails
	 * @global string $_POST['before_title'].       Works only for template 'list'
	 * @global string $_POST['after_title'].        Works only for template 'list'
	 * @global bool   $_POST['show_excerpt'].       Works only for template 'list'
	 * @global int    $_POST['excerpt_length'].     Works only for template 'list'
	 * @global string $_POST['before_post'].        Works only for template 'list'
	 * @global string $_POST['after_post'].         Works only for template 'list'
	 * @global string $_POST['before_related'].     Works only for template 'list'
	 * @global string $_POST['after_related'].      Works only for template 'list'
	 *
	 * @return JSON Response with structure:
	 *  array(
	 *    "styles" => inline styles of the requested template
	 *    "html" => HTML code of the requested template
	 *    "code" => HTML code Encoded of the requested template
	 *  )
	 */
	public function ajax_display_preview() {
		check_ajax_referer( 'yarpp_display_preview' );

		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => 'Not allowed'
			), 405 );
		}

		header( 'HTTP/1.1 200' );
		header( 'Content-Type: text/html; charset=UTF-8' );

		$defaults = array(
			'domain'        => 'website',
			'limit'         => yarpp_get_option( 'limit' ),
			'template'      => yarpp_get_option( 'template' ),
			'order'         => yarpp_get_option( 'order' ),
			'promote_yarpp' => yarpp_get_option( 'promote_yarpp' ),
			'show_excerpt'  => yarpp_get_option( 'show_excerpt' ),
			'thumbnails_default'  => yarpp_get_option( 'thumbnails_default' ),
		);

		$allowed = array(
			'limit',
			'template',
			'order',
			'promote_yarpp',
			'thumbnails_heading',
			'thumbnails_default',
			'before_title',
			'after_title',
			'show_excerpt',
			'excerpt_length',
			'before_post',
			'after_post',
			'before_related',
			'after_related',
			'size',
		);

		$args = array_intersect_key( $_POST, array_flip( $allowed ) );
		$args = array_merge( $defaults, $args );

		foreach ( $args as $key => $value ) {
			$args[$key] = wp_unslash($value);
		}

		$return = $this->core->display_demo_related( $args, false );

		$size = isset( $_POST['size'] ) ? sanitize_text_field( $_POST['size'] ) : 'thumbnail';

		$load_styles = file_get_contents( plugins_url( '/style/related.css', YARPP_MAIN_FILE ) );

		if ( 'thumbnails' === $args['template'] ) {
			$load_styles .= file_get_contents( plugins_url( '/style/styles_thumbnails.css', YARPP_MAIN_FILE ) );
		}
		if ( ! in_array( $args['template'], array( 'builtin', 'list' ), true ) ) {
			$load_styles .= yarpp_thumbnail_inline_css( yarpp_get_image_sizes( $size ) );
		}

		wp_send_json(
			array(
				'styles' => $load_styles,
				'html'   => $return,
				'code'   => htmlspecialchars( $return ),
			)
		);
	}

	/**
	 * Display optin data in a human readable format on the help tab.
	 */
	public function ajax_optin_data() {
		check_ajax_referer( 'yarpp_optin_data' );

		header( 'HTTP/1.1 200' );
		header( 'Content-Type: text/html; charset=UTF-8' );

		$data = $this->core->optin_data();
		$this->core->pretty_echo( $data );
		die();
	}

	public function ajax_optin_disable() {
		check_ajax_referer( 'yarpp_optin_disable' );

		$this->core->set_option( 'optin', false );

		header( 'HTTP/1.1 200' );
		header( 'Content-Type: text; charset=UTF-8' );
		echo 'ok';

		die();
	}

	public function ajax_optin_enable() {
		check_ajax_referer( 'yarpp_optin_enable' );

		$this->core->set_option( 'optin', true );
		$this->core->optin_ping();

		header( 'HTTP/1.1 200' );
		header( 'Content-Type: text; charset=UTF-8' );
		echo 'ok';

		die();
	}

	/**
	 * Handles switching between Pro and Basic versions
	 *
	 * For example:
	 * ../wp-admin/admin-ajax.php?action=yarpp_switch&go=pro
	 * ../wp-admin/admin-ajax.php?action=yarpp_switch&go=basic
	 *
	 * @since 5.1.0
	 */
	public function ajax_switch() {
		check_ajax_referer( 'yarpp_switch' );

		if ( ! is_admin() ||
		! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['go'] ) || trim( $_GET['go'] ) === '' ) {
			die();
		}

		$switch = htmlentities( $_GET['go'] );

		function switchYarppPro( $status ) {
			$yarppPro = get_option( 'yarpp_pro' );
			$yarpp    = get_option( 'yarpp' );

			if ( $status ) {
				$yarppPro['optin'] = (bool) $yarpp['optin'];
				$yarpp['optin']    = false;
			} else {
				$yarpp['optin'] = (bool) $yarppPro['optin'];
			}

			$yarppPro['active'] = $status;
			update_option( 'yarpp', $yarpp );
			update_option( 'yarpp_pro', $yarppPro );

			header( 'HTTP/1.1 200' );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			die( 'ok' );
		}

		switch ( $switch ) {
			case 'basic':
				switchYarppPro( 0 );
				break;
			case 'pro':
				switchYarppPro( 1 );
				break;
		}
	}

	/**
	 * @deprecated since 5.26.0 use YARPP_Admin::ajax_display_preview() instead
	 * @see YARPP_Admin::ajax_display_preview()
	 */
	public function ajax_set_display_code() {
		_deprecated_function( 'YARPP_Admin::ajax_set_display_code', '5.26.0', 'YARPP_Admin::ajax_display_preview' );
		return $this->ajax_display_preview();
	}

	/**
	 * Registers YARPP plugin for the deactivation survey library code.
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function deactivation_survey_data( $plugins ) {

		global $yarpp;
		if ( $yarpp instanceof YARPP && isset( $yarpp->cloud ) && $yarpp->cloud instanceof YARPP_Cloud ) {
			$api_key          = $yarpp->cloud->get_api_key();
			$verification_key = $yarpp->cloud->get_option( 'verification_key' );
			if ( empty( $verification_key ) ) {
				$verification_key = '';
			}
		} else {
			$api_key          = '';
			$verification_key = '';
		}
		$plugin_data = get_plugin_data( YARPP_MAIN_FILE, false, false );
		$plugins[]   = (object) array(
			'title_slugged'           => sanitize_title( $plugin_data['Name'] ),
			'basename'                => plugin_basename( YARPP_MAIN_FILE ),
			'logo'                    => plugins_url( '/images/icon-256x256.png', YARPP_MAIN_FILE ),
			'api_server'              => 'yarpp.com',
			'script_cache_ver'        => YARPP_VERSION,
			'bgcolor'                 => '#fff',
			'send'                    => array(
				'plugin_name'      => 'yarpp',
				'plugin_version'   => YARPP_VERSION,
				'api_key'          => $api_key,
				'verification_key' => $verification_key,
				'platform'         => 'wordpress',
				'domain'           => site_url(),
				'language'         => strtolower( get_bloginfo( 'language' ) ),
			),
			'reasons'                 => array(
				'error'                  => esc_html__( 'I think I found a bug', 'yet-another-related-posts-plugin' ),
				'feature-missing'        => esc_html__( 'It\'s missing a feature I need', 'yet-another-related-posts-plugin' ),
				'too-hard'               => esc_html__( 'I couldn\'t figure out how to do something', 'yet-another-related-posts-plugin' ),
				'inefficient'            => esc_html__( 'It\'s too slow or inefficient', 'yet-another-related-posts-plugin' ),
				'no-signup'              => esc_html__( 'I don\'t want to signup', 'yet-another-related-posts-plugin' ),
				'temporary-deactivation' => esc_html__( 'Temporarily deactivating or troubleshooting', 'yet-another-related-posts-plugin' ),
				'other'                  => esc_html__( 'Other', 'yet-another-related-posts-plugin' ),
			),
			'reasons_needing_comment' => array(
				'error',
				'feature-missing',
				'too-hard',
				'other',
			),
			'translations'            => array(
				'quick_feedback'        => esc_html__( 'Quick Feedback', 'yet-another-related-posts-plugin' ),
				'foreword'              => esc_html__(
					'If you would be kind enough, please tell us why you are deactivating the plugin:',
					'yet-another-related-posts-plugin'
				),
				'please_tell_us'        => esc_html__(
					'Please share anything you think might be helpful. The more we know about your problem, the faster we\'ll be able to fix it.',
					'yet-another-related-posts-plugin'
				),
				'cancel'                => esc_html__( 'Cancel', 'yet-another-related-posts-plugin' ),
				'skip_and_deactivate'   => esc_html__( 'Skip &amp; Deactivate', 'yet-another-related-posts-plugin' ),
				'submit_and_deactivate' => esc_html__( 'Submit &amp; Deactivate', 'yet-another-related-posts-plugin' ),
				'please_wait'           => esc_html__( 'Please wait...', 'yet-another-related-posts-plugin' ),
				'thank_you'             => esc_html__( 'Thank you!', 'yet-another-related-posts-plugin' ),
				'ask_for_support'       => sprintf(
					esc_html__(
						'Have you visited %1$sthe support forum%2$s and %3$sread the FAQs%2$s for help?',
						'yet-another-related-posts-plugin'
					),
					'<a href="https://wordpress.org/support/plugin/yet-another-related-posts-plugin/" target="_blank" >',
					'</a>',
					'<a href="https://wordpress.org/plugins/yet-another-related-posts-plugin/#faq" target="_blank" >'
				),
				'email_request'         => esc_html__(
					'If you would like to tell us more, please leave your email here. We will be in touch (only for product feedback, nothing else).',
					'yet-another-related-posts-plugin'
				),
			),

		);

		return $plugins;
	}
}
