<?php

/*

Plugin Name: Captcha

Plugin URI: https://wordpress.org/plugins/captcha/

Description: This plugin allows you to implement super security captcha form into web forms.

Author: simplywordpress

Text Domain: captcha

Domain Path: /languages

Version: 4.3.3

Author URI: https://profiles.wordpress.org/wpdevmgr2678

License: GPLv2 or later

*/



/*  © Copyright 2017



    This program is free software; you can redistribute it and/or modify

    it under the terms of the GNU General Public License, version 2, as

    published by the Free Software Foundation.



    This program is distributed in the hope that it will be useful,

    but WITHOUT ANY WARRANTY; without even the implied warranty of

    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

    GNU General Public License for more details.



    You should have received a copy of the GNU General Public License

    along with this program; if not, write to the Free Software

    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


function hctpc_enqueue_backend_script() {

        wp_register_script( 'hctpc_backend_script', plugin_dir_url( __FILE__ ) . 'js/back_end_script.js', false, '1.0.0' );
		wp_enqueue_script( 'hctpc_backend_script' );

}
add_action( 'admin_enqueue_scripts', 'hctpc_enqueue_backend_script' );



if ( ! function_exists( 'hctpc_admin_menu' ) ) {

	function hctpc_admin_menu() {

		add_menu_page( __( 'Captcha Settings', 'captcha' ), 'Captcha', 'manage_options', 'captcha.php', 'hctpc_page_router' );



		add_submenu_page( 'captcha.php', __( 'Captcha Settings', 'captcha' ), __( 'Settings', 'captcha' ), 'manage_options', 'captcha.php', 'hctpc_page_router' );



		add_submenu_page( 'captcha.php', __( 'Captcha Packages', 'captcha' ), __( 'Packages', 'captcha' ), 'manage_options', 'captcha-packages.php', 'hctpc_page_router' );



		add_submenu_page( 'captcha.php', __( 'Captcha Whitelist', 'captcha' ), __( 'Whitelist', 'captcha' ), 'manage_options', 'captcha-whitelist.php', 'hctpc_page_router' );

	}

}



if ( ! function_exists( 'hctpc_plugins_loaded' ) ) {

	function hctpc_plugins_loaded() {

		/* Internationalization */

		load_plugin_textdomain( 'captcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

}



if ( ! function_exists ( 'hctpc_init' ) ) {

	function hctpc_init() {

		global $hctpc_plugin_info, $hctpc_ip_in_whitelist, $hctpc_options;



		if ( ! $hctpc_plugin_info ) {

			if ( ! function_exists( 'get_plugin_data' ) )

				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$hctpc_plugin_info = get_plugin_data( __FILE__ );

		}



		$is_admin = is_admin() && ! defined( 'DOING_AJAX' );



		/* Call register settings function */

		$pages = array(

			'captcha.php',

			'captcha-packages.php',

			'captcha-whitelist.php'

		);



		if ( ! $is_admin || ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) )

			hctpc_settings();



		if ( $is_admin )

			return;



		$user_loggged_in       = is_user_logged_in();

		$hctpc_ip_in_whitelist = hctpc_whitelisted_ip();



		/*

		 * Add the CAPTCHA to the WP login form

		 */

		if ( $hctpc_options['forms']['wp_login']['enable'] ) {

			add_action( 'login_form', 'hctpc_login_form' );

			if ( ! $hctpc_ip_in_whitelist )

				add_filter( 'authenticate', 'hctpc_login_check', 21, 1 );

		}



		/*

		 * Add the CAPTCHA to the WP register form

		 */

		if ( $hctpc_options['forms']['wp_register']['enable'] ) {

			add_action( 'register_form', 'hctpc_register_form' );

			add_action( 'signup_extra_fields', 'wpmu_hctpc_register_form' );

			add_action( 'signup_blogform', 'wpmu_hctpc_register_form' );



			if ( ! $hctpc_ip_in_whitelist ) {

				add_filter( 'registration_errors', 'hctpc_register_check', 9, 1 );

				if ( is_multisite() ) {

					add_filter( 'wpmu_validate_user_signup', 'hctpc_register_validate' );

					add_filter( 'wpmu_validate_blog_signup', 'hctpc_register_validate' );

				}

			}

		}



		/*

		 * Add the CAPTCHA into the WP lost password form

		 */

		if ( $hctpc_options['forms']['wp_lost_password']['enable'] ) {

			add_action( 'lostpassword_form', 'hctpc_lostpassword_form' );

			if ( ! $hctpc_ip_in_whitelist )

				add_filter( 'allow_password_reset', 'hctpc_lostpassword_check' );

		}



		/*

		 * Add the CAPTCHA to the WP comments form

		 */

		if ( hctpc_captcha_is_needed( 'wp_comments', $user_loggged_in ) ) {

			global $wp_version;

			/*

			 * Common hooks to add necessary actions for the WP comment form,

			 * but some themes don't contain these hooks in their comments form templates

			 */

			add_action( 'comment_form_after_fields', 'hctpc_comment_form_wp3', 1 );

			add_action( 'comment_form_logged_in_after', 'hctpc_comment_form_wp3', 1 );

			/*

			 * Try to display the CAPTCHA before the close tag </form>

			 * in case if hooks 'comment_form_after_fields' or 'comment_form_logged_in_after'

			 * are not included to the theme comments form template

			 */

			add_action( 'comment_form', 'hctpc_comment_form' );

			if ( ! $hctpc_ip_in_whitelist )

				add_filter( 'preprocess_comment', 'hctpc_comment_post' );

		}

	}

}



if ( ! function_exists( 'hctpc_create_table' ) ) {

	function hctpc_create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}hctpc_whitelist` (

			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

			`ip` CHAR(31) NOT NULL,

			`ip_from_int` BIGINT,

			`ip_to_int` BIGINT,

			`add_time` DATETIME,

			PRIMARY KEY (`id`)

			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );



		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}hctpc_images` (

			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

			`name` CHAR(100) NOT NULL,

			`package_id` INT NOT NULL,

			`number` INT NOT NULL,

			PRIMARY KEY (`id`)

			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );



		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}hctpc_packages` (

			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

			`name` CHAR(100) NOT NULL,

			`folder` CHAR(100) NOT NULL,

			`settings` LONGTEXT NOT NULL,

			`user_settings` LONGTEXT NOT NULL,

			`add_time` DATETIME NOT NULL,

			PRIMARY KEY (`id`)

			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		dbDelta( $sql );

	}

}



/**

 * Activation plugin function

 */

if ( ! function_exists( 'hctpc_plugin_activate' ) ) {

	function hctpc_plugin_activate( $networkwide ) {

		global $wpdb;

		/* Activation function for network, check if it is a network activation - if so, run the activation function for each blog id */

		if ( function_exists( 'is_multisite' ) && is_multisite() && $networkwide ) {

			$old_blog = $wpdb->blogid;

			/* Get all blog ids */

			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );

			foreach ( $blogids as $blog_id ) {

				switch_to_blog( $blog_id );

				hctpc_create_table();

				hctpc_settings();

			}

			switch_to_blog( $old_blog );

			return;

		}

		hctpc_create_table();

		hctpc_settings();



		register_uninstall_hook( __FILE__, 'hctpc_delete_options' );

	}

}



/* Register settings function */

if ( ! function_exists( 'hctpc_settings' ) ) {

	function hctpc_settings() {

		global $hctpc_options, $hctpc_plugin_info, $wpdb;



		if ( empty( $hctpc_plugin_info ) ) {

			if ( ! function_exists( 'get_plugin_data' ) )

				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$hctpc_plugin_info = get_plugin_data( dirname(__FILE__) . '/captcha.php' );

		}



		$db_version = '1.4';

		$need_update = false;



		$hctpc_options = get_option( 'hctpc_options' );

		if ( empty( $hctpc_options ) ) {

			$old_options = get_option( 'cptch_options' );

			if ( ! empty( $old_options ) ) {

				$hctpc_options = $old_options;

				$hctpc_options['renaming_notice'] = 1;

				update_option( 'hctpc_options', $hctpc_options );

				$wpdb->query( "CREATE TABLE {$wpdb->prefix}hctpc_whitelist LIKE {$wpdb->prefix}cptch_whitelist;" );

				$wpdb->query( "INSERT {$wpdb->prefix}hctpc_whitelist SELECT * FROM {$wpdb->prefix}cptch_whitelist;" );

				$wpdb->query( "CREATE TABLE {$wpdb->prefix}hctpc_images LIKE {$wpdb->prefix}cptch_images;" );

				$wpdb->query( "INSERT {$wpdb->prefix}hctpc_images SELECT * FROM {$wpdb->prefix}cptch_images;" );

				$wpdb->query( "CREATE TABLE {$wpdb->prefix}hctpc_packages LIKE {$wpdb->prefix}cptch_packages;" );

				$wpdb->query( "INSERT {$wpdb->prefix}hctpc_packages SELECT * FROM {$wpdb->prefix}cptch_packages;" );

				if ( is_multisite() ) {

					switch_to_blog( 1 );

					$upload_dir = wp_upload_dir();

					restore_current_blog();

				} else {

					$upload_dir = wp_upload_dir();

				}

				$images_upload_dir = $upload_dir['basedir'] . '/captcha_images';

				$images_upload_dir_old = $upload_dir['basedir'] . '/bws_captcha_images';

				$rename_result = rename( $images_upload_dir_old, $images_upload_dir );

				if ( ! $rename_result )

					unset( $hctpc_options['plugin_db_version'] );				

			} else {

				if ( ! function_exists( 'hctpc_get_default_options' ) )

					require_once( dirname( __FILE__ ) . '/includes/helpers.php' );

				$hctpc_options = hctpc_get_default_options();

				update_option( 'hctpc_options', $hctpc_options );

			}

		}



		if (

			empty( $hctpc_options['plugin_option_version'] ) ||

			$hctpc_options['plugin_option_version'] != $hctpc_plugin_info["Version"]

		) {

			$need_update = true;



			if ( ! function_exists( 'hctpc_get_default_options' ) )

				require_once( dirname( __FILE__ ) . '/includes/helpers.php' );

			$default_options = hctpc_get_default_options();



			/* Enabling notice about possible conflict with W3 Total Cache */

			if ( version_compare( $hctpc_options['plugin_option_version'], '4.2.7', '<=' ) ) {

				$hctpc_options['w3tc_notice'] = 1;

			}

		}



		/* Update tables when update plugin and tables changes*/

		if ( empty( $hctpc_options['plugin_db_version'] ) || $hctpc_options['plugin_db_version'] != $db_version ) {

			$need_update = true;

			hctpc_create_table();



			if ( empty( $hctpc_options['plugin_db_version'] ) ) {

				if ( ! class_exists( 'hctpc_Package_Loader' ) )

					require_once( dirname( __FILE__ ) . '/includes/class-hctpc-package-loader.php' );

				$package_loader = new hctpc_Package_Loader();

				$package_loader->save_packages( dirname( __FILE__ ) . '/images/package', false );

			}



			$hctpc_options['plugin_db_version'] = $db_version;

		}



		if ( $need_update )

			update_option( 'hctpc_options', $hctpc_options );

	}

}



/* Generate key */

if ( ! function_exists( 'hctpc_generate_key' ) ) {

	function hctpc_generate_key( $lenght = 15 ) {

		global $hctpc_options;

		/* Under the string $simbols you write all the characters you want to be used to randomly generate the code. */

		$simbols = get_bloginfo( "url" ) . time();

		$simbols_lenght = strlen( $simbols );

		$simbols_lenght--;

		$str_key = NULL;

		for ( $x = 1; $x <= $lenght; $x++ ) {

			$position = rand( 0, $simbols_lenght );

			$str_key .= substr( $simbols, $position, 1 );

		}



		$hctpc_options['str_key']['key']  = md5( $str_key );

		$hctpc_options['str_key']['time'] = time();

		update_option( 'hctpc_options', $hctpc_options );

	}

}



if ( ! function_exists( 'hctpc_whitelisted_ip' ) ) {

	function hctpc_whitelisted_ip() {

		global $hctpc_options, $wpdb;

		$checked = false;

		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );

		$table = 'hctpc_whitelist';

		$whitelist_exist = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}{$table}'" );

		if ( ! empty( $whitelist_exist ) ) {

			$ip = '';

			if ( isset( $_SERVER ) ) {

				$sever_vars = array( 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

				foreach( $sever_vars as $var ) {

					if ( isset( $_SERVER[ $var ] ) && ! empty( $_SERVER[ $var ] ) ) {

						if ( filter_var( $_SERVER[ $var ], FILTER_VALIDATE_IP ) ) {

							$ip = $_SERVER[ $var ];

							break;

						} else { /* if proxy */

							$ip_array = explode( ',', $_SERVER[ $var ] );

							if ( is_array( $ip_array ) && ! empty( $ip_array ) && filter_var( $ip_array[0], FILTER_VALIDATE_IP ) ) {

								$ip = $ip_array[0];

								break;

							}

						}

					}

				}

			}



			if ( ! empty( $ip ) ) {

				$column_exists = $wpdb->query( "SHOW COLUMNS FROM `{$wpdb->prefix}{$table}` LIKE 'ip_from_int'" );

				/* LimitAttempts Free hasn't `ip_from_int`, `ip_to_int` COLUMNS */

				if ( 0 == $column_exists ) {

					$result = $wpdb->get_var(

						"SELECT `id`

						FROM `{$wpdb->prefix}{$table}`

						WHERE `ip` = '{$ip}' LIMIT 1;"

					);

				} else {

					$ip_int = sprintf( '%u', ip2long( $ip ) );

					$result = $wpdb->get_var(

						"SELECT `id`

						FROM `{$wpdb->prefix}{$table}`

						WHERE ( `ip_from_int` <= {$ip_int} AND `ip_to_int` >= {$ip_int} ) OR `ip` LIKE '{$ip}' LIMIT 1;"

					);

				}

				$checked = is_null( $result ) || ! $result ? false : true;

			}

		}

		return $checked;

	}

}



/**

 * Function displays captcha admin-pages

 * @see   groups_action_create_group(),

 * @since 4.3.1

 * @return void

 */

if ( ! function_exists( 'hctpc_page_router' ) ) {

	function hctpc_page_router() { ?>

		<div class="wrap">

			<?php if ( 'captcha.php' == $_GET['page'] ) {

				require_once( dirname( __FILE__ ) . '/includes/class-hctpc-settings-tabs.php' );

				$page = new hctpc_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>

				<h1><?php _e( 'Captcha Settings', 'captcha' ); ?></h1>

				<?php $page->display_content();

			} else {

				switch ( $_GET['page'] ) {

					case 'captcha-packages.php':

						if ( ! class_exists( 'hctpc_Package_Loader' ) )

							require_once( dirname( __FILE__ ) . '/includes/class-hctpc-package-list.php' );



						$page = new hctpc_Package_List();

						break;

					case 'captcha-whitelist.php':

						require_once( dirname( __FILE__ ) . '/includes/helpers.php' );

						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

						require_once( dirname( __FILE__ ) . '/includes/class-hctpc-whitelist.php' );

						$page = new hctpc_Whitelist( plugin_basename( __FILE__ ) );

						break;

					default:

						/* closing of the div.wrap */

						echo '</div>';

						return;

				}



				$page->display_content();

			} ?>

		</div>

	<?php }

}



/************** WP LOGIN FORM HOOKS ********************/



if ( ! function_exists( 'hctpc_login_form' ) ) {

	function hctpc_login_form() {

		global $hctpc_options, $hctpc_ip_in_whitelist;

		if ( ! $hctpc_ip_in_whitelist ) {

			if ( "" == session_id() )

			@session_start();



			if ( isset( $_SESSION["hctpc_login"] ) )

				unset( $_SESSION["hctpc_login"] );

		}



		echo hctpc_display_captcha_custom( 'wp_login', 'hctpc_wp_login' ) . '<br />';

		return true;

	}

}



if ( ! function_exists( 'hctpc_login_check' ) ) {

	function hctpc_login_check( $user ) {

		global $hctpc_options;



		if ( ! isset( $_POST['wp-submit'] ) )

			return $user;



		if ( ! isset( $hctpc_options['str_key'] ) )

			$hctpc_options = get_option( 'hctpc_options' );

		$str_key = $hctpc_options['str_key']['key'];



		if ( ! function_exists( 'is_plugin_active' ) )

			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );



		if ( "" == session_id() )

			@session_start();



		if ( isset( $_SESSION["hctpc_login"] ) && true === $_SESSION["hctpc_login"] )

			return $user;



		/* Delete errors, if they set */

		if ( isset( $_SESSION['hctpc_error'] ) )

			unset( $_SESSION['hctpc_error'] );



		if ( is_plugin_active( 'limit-login-attempts/limit-login-attempts.php' ) ) {

			if ( isset( $_REQUEST['loggedout'] ) && isset( $_REQUEST['hctpc_number'] ) && "" ==  $_REQUEST['hctpc_number'] ) {

				return $user;

			}

		}


		if ( hctpc_limit_exhausted() ) {

			$_SESSION['hctpc_login'] = false;

			$error = new WP_Error();

			$error->add( 'hctpc_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:' . '&nbsp;' . $hctpc_options['time_limit_off'] );

			return $error;

		}

		/* Add error if captcha is empty */

		if ( ( ! isset( $_REQUEST['hctpc_number'] ) || "" ==  $_REQUEST['hctpc_number'] ) && isset( $_REQUEST['loggedout'] ) ) {

			$error = new WP_Error();

			$error->add( 'hctpc_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $hctpc_options['no_answer'] );

			wp_clear_auth_cookie();

			return $error;

		}



		if ( isset( $_REQUEST['hctpc_result'] ) && isset( $_REQUEST['hctpc_number'] ) && isset( $_REQUEST['hctpc_time'] ) ) {

			if ( 0 === strcasecmp( trim( hctpc_decode( $_REQUEST['hctpc_result'], $str_key, $_REQUEST['hctpc_time'] ) ), $_REQUEST['hctpc_number'] ) ) {

				/* Captcha was matched */

				$_SESSION['hctpc_login'] = true;

				return $user;

			} else {

				$_SESSION['hctpc_login'] = false;

				wp_clear_auth_cookie();

				/* Add error if captcha is incorrect */

				$error = new WP_Error();

				if ( "" == $_REQUEST['hctpc_number'] )

					$error->add( 'hctpc_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $hctpc_options['no_answer'] );

				else

					$error->add( 'hctpc_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $hctpc_options['wrong_answer'] );

				return $error;

			}

		} else {

			/* Captcha was matched */

			if ( isset( $_REQUEST['log'] ) && isset( $_REQUEST['pwd'] ) ) {

				/* captcha was not found in _REQUEST */

				$_SESSION['hctpc_login'] = false;

				$error = new WP_Error();

				$error->add( 'hctpc_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $hctpc_options['no_answer'] );

				return $error;

			} else {

				/* it is not a submit */

				return $user;

			}

		}

	}

}



/************** WP REGISTER FORM HOOKS ********************/



if ( ! function_exists( 'hctpc_register_form' ) ) {

	function hctpc_register_form() {

		echo hctpc_display_captcha_custom( 'wp_register', 'hctpc_wp_register' ) . '<br />';

		return true;

	}

}



if ( ! function_exists ( 'wpmu_hctpc_register_form' ) ) {

	function wpmu_hctpc_register_form( $errors ) {

		global $hctpc_options, $hctpc_ip_in_whitelist;



		/* the captcha html - register form */

		echo '<div class="hctpc_block">';

		if ( "" != $hctpc_options['title'] )

			echo '<span class="hctpc_title">' . $hctpc_options['title'] . '<span class="required"> ' . $hctpc_options['required_symbol'] . '</span></span>';

		if ( ! $hctpc_ip_in_whitelist ) {

			if ( is_wp_error( $errors ) ) {

				$error_codes = $errors->get_error_codes();

				if ( is_array( $error_codes ) && ! empty( $error_codes ) ) {

					foreach ( $error_codes as $error_code ) {

						if ( "captcha_" == substr( $error_code, 0, 8 ) ) {

							$error_message = $errors->get_error_message( $error_code );

							echo '<p class="error">' . $error_message . '</p>';

						}

					}

				}

			}

			echo hctpc_display_captcha( 'wp_register' );

		} else

			echo '<label class="hctpc_whitelist_message">' . $hctpc_options['whitelist_message'] . '</label>';

		echo '</div><br />';

	}

}



if ( ! function_exists ( 'hctpc_register_check' ) ) {

	function hctpc_register_check( $error ) {

		global $hctpc_options;

		$str_key = $hctpc_options['str_key']['key'];



		if ( hctpc_limit_exhausted() ) {

			if ( ! is_wp_error( $error ) )

				$error = new WP_Error();

			$error->add( 'captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:&nbsp;' . $hctpc_options['time_limit_off'] );

		} elseif ( isset( $_REQUEST['hctpc_number'] ) && "" == $_REQUEST['hctpc_number'] ) {

			if ( ! is_wp_error( $error ) )

				$error = new WP_Error();

			$error->add( 'captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:&nbsp;' . $hctpc_options['no_answer'] );

		} elseif (

			! isset( $_REQUEST['hctpc_result'] ) ||

			! isset( $_REQUEST['hctpc_number'] ) ||

			! isset( $_REQUEST['hctpc_time'] ) ||

			0 !== strcasecmp( trim( hctpc_decode( $_REQUEST['hctpc_result'], $str_key, $_REQUEST['hctpc_time'] ) ), $_REQUEST['hctpc_number'] )

		) {

			if ( ! is_wp_error( $error ) )

				$error = new WP_Error();

			$error->add( 'captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:&nbsp;' . $hctpc_options['wrong_answer'] );

		}

		return $error;

	}

}



if ( ! function_exists( 'hctpc_register_validate' ) ) {

	function hctpc_register_validate( $results ) {

		global $current_user, $hctpc_options;



		if ( empty( $current_user->data->ID ) ) {

			$str_key = $hctpc_options['str_key']['key'];

			$time_limit_exhausted = hctpc_limit_exhausted();

			if ( $time_limit_exhausted ) {

				$error_slug    = 'captcha_time_limit';

				$error_message = $hctpc_options['time_limit_off'];

			} else {

				$error_slug    = 'captcha_blank';

				$error_message = $hctpc_options['no_answer'];

			}



			/* If captcha is blank - add error */

			if ( ( isset( $_REQUEST['hctpc_number'] ) && "" ==  $_REQUEST['hctpc_number'] ) || $time_limit_exhausted ) {

				$results['errors']->add( $error_slug, '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $error_message );

				return $results;

			}



			if (

				! isset( $_REQUEST['hctpc_result'] ) ||

				! isset( $_REQUEST['hctpc_number'] ) ||

				! isset( $_REQUEST['hctpc_time'] ) ||

				0 !== strcasecmp( trim( hctpc_decode( $_REQUEST['hctpc_result'], $str_key, $_REQUEST['hctpc_time'] ) ), $_REQUEST['hctpc_number'] )

			)

				$results['errors']->add( 'captcha_wrong', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>: ' . $hctpc_options['wrong_answer'] );

			return $results;

		} else {

			return $results;

		}

	}

}



/************** WP LOST PASSWORD FORM HOOKS ********************/



if ( ! function_exists ( 'hctpc_lostpassword_form' ) ) {

	function hctpc_lostpassword_form() {

		echo hctpc_display_captcha_custom( 'wp_lost_password', 'hctpc_wp_lost_password' ) . '<br />';

		return true;

	}

}



if ( ! function_exists ( 'hctpc_lostpassword_check' ) ) {

	function hctpc_lostpassword_check( $allow ) {

		global $hctpc_options;

		$str_key = $hctpc_options['str_key']['key'];

		$error   = '';



		if ( hctpc_limit_exhausted() ) {

			$error = new WP_Error();

			$error->add( 'captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:&nbsp;' . $hctpc_options['time_limit_off'] );

		} elseif ( isset( $_REQUEST['hctpc_number'] ) && "" == $_REQUEST['hctpc_number'] ) {

			$error = new WP_Error();

			$error->add( 'captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:&nbsp;' . $hctpc_options['no_answer'] );

		} elseif (

			! isset( $_REQUEST['hctpc_result'] ) ||

			! isset( $_REQUEST['hctpc_number'] ) ||

			! isset( $_REQUEST['hctpc_time'] ) ||

			0 !== strcasecmp( trim( hctpc_decode( $_REQUEST['hctpc_result'], $str_key, $_REQUEST['hctpc_time'] ) ), $_REQUEST['hctpc_number'] )

		) {

			$error = new WP_Error();

			$error->add( 'captcha_error', '<strong>' . __( 'ERROR', 'captcha' ) . '</strong>:&nbsp;' . $hctpc_options['wrong_answer'] );

		}

		return is_wp_error( $error ) ? $error : $allow;

	}

}



/************** WP COMMENT FORM HOOKS ********************/



if ( ! function_exists( 'hctpc_comment_form' ) ) {

	function hctpc_comment_form() {

		echo hctpc_display_captcha_custom( 'wp_comments', 'hctpc_wp_comments' );

		return true;

	}

}



if ( ! function_exists( 'hctpc_comment_form_wp3' ) ) {

	function hctpc_comment_form_wp3() {

		remove_action( 'comment_form', 'hctpc_comment_form' );

		echo hctpc_display_captcha_custom( 'wp_comments', 'hctpc_wp_comments' );

		return true;

	}

}



if ( ! function_exists( 'hctpc_comment_post' ) ) {

	function hctpc_comment_post( $comment ) {

		global $hctpc_options;



		if ( is_user_logged_in() && 1 == $hctpc_options['hctpc_hide_register'] )

			return $comment;



		$str_key = $hctpc_options['str_key']['key'];



		$time_limit_exhausted = hctpc_limit_exhausted();

		$error_message = $time_limit_exhausted ? $hctpc_options['time_limit_off'] : $hctpc_options['no_answer'];



		/* Added for compatibility with WP Wall plugin */

		/* This does NOT add CAPTCHA to WP Wall plugin, */

		/* It just prevents the "Error: You did not enter a Captcha phrase." when submitting a WP Wall comment */

		if ( function_exists( 'WPWall_Widget' ) && isset( $_REQUEST['wpwall_comment'] ) ) {

			/* Skip capthca */

			return $comment;

		}



		/* Skip captcha for comment replies from the admin menu */

		if ( isset( $_REQUEST['action'] ) && 'replyto-comment' == $_REQUEST['action'] &&

		( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {

			return $comment;

		}



		/* Skip captcha for trackback or pingback */

		if ( '' != $comment['comment_type'] && 'comment' != $comment['comment_type'] ) {

			return $comment;

		}



		/* If captcha is empty */

		if ( ( isset( $_REQUEST['hctpc_number'] ) && "" ==  $_REQUEST['hctpc_number'] ) || $time_limit_exhausted )

			wp_die( __( 'Error', 'captcha' ) . ':&nbsp' . $error_message . ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? '' : ' ' . __( "Click the BACK button on your browser, and try again.", 'captcha' ) ) );



		if ( isset( $_REQUEST['hctpc_result'] ) && isset( $_REQUEST['hctpc_number'] ) && isset( $_REQUEST['hctpc_time'] ) && 0 === strcasecmp( trim( hctpc_decode( $_REQUEST['hctpc_result'], $str_key, $_REQUEST['hctpc_time'] ) ), $_REQUEST['hctpc_number'] ) ) {

			/* Captcha was matched */

			return( $comment );

		} else {

			wp_die( __( 'Error', 'captcha' ) . ':&nbsp' . $hctpc_options['wrong_answer'] . ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? '' : ' ' . __( "Click the BACK button on your browser, and try again.", 'captcha' ) ) );

		}

	}

}



/************** DISPLAY CAPTCHA VIA SHORTCODE ********************/



/**

 *

 * @since 4.2.3

 */

if ( ! function_exists( 'hctpc_display_captcha_shortcode' ) ) {

	function hctpc_display_captcha_shortcode( $args ) {

		global $hctpc_options;



		if ( ! is_array( $args ) || empty( $args ) )

			return hctpc_display_captcha_custom( 'general', 'hctpc_shortcode' );



		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );



		$form_slug  = empty( $args["form_slug"] ) ? 'general' : $args["form_slug"];

		$form_slug  = esc_attr( $form_slug );

		$form_slug  = empty( $form_slug ) || ! array_key_exists( $form_slug, $hctpc_options['forms'] ) ? 'general' : $form_slug;

		$class_name = empty( $args["class_name"] ) ? 'hctpc_shortcode' : esc_attr( $args["class_name"] );



		return

				'general' == $form_slug ||

				$hctpc_options['forms'][ $form_slug ]['enable']

			?

				hctpc_display_captcha_custom( $form_slug, $class_name)

			:

				'';

	}

}



/************** DISPLAY CAPTCHA VIA FILTER HOOK ********************/

/**

 *

 * @since 4.2.3

 */

if ( ! function_exists( 'hctpc_display_filter' ) ) {

	function hctpc_display_filter( $content = '', $form_slug = 'general', $class_name = "" ) {

		$args = array(

			'form_slug'  => $form_slug,

			'class_name' => $class_name

		);

		return $content . hctpc_display_captcha_shortcode( $args );

	}

}



/* Functionality of the captcha logic work for custom form */

if ( ! function_exists( 'hctpc_display_captcha_custom' ) ) {

	function hctpc_display_captcha_custom( $form_slug = 'general', $class_name = "", $input_name = 'hctpc_number' ) {

		global $hctpc_options, $hctpc_ip_in_whitelist;



		if ( empty( $hctpc_ip_in_whitelist ) )

			$hctpc_ip_in_whitelist = hctpc_whitelisted_ip();



		if ( empty( $class_name ) ) {

			$label = $tag_open = $tag_close = '';

		} else {

			$label =

					"" != $hctpc_options['title']

				?

					'<span class="hctpc_title">' . $hctpc_options['title'] .'<span class="required"> ' . $hctpc_options['required_symbol'] . '</span></span>'

				:

					'';

			$tag_open  = '<p class="hctpc_block"><span class="wpcf7-form-control-wrap hctpc_number"><span class="wpcf7-form-control wpcf7-radio">';

			$tag_close = '</span></span></p>';

		}



		$content = $hctpc_ip_in_whitelist ? '<label class="hctpc_whitelist_message">' . $hctpc_options['whitelist_message'] . '</label>' : hctpc_display_captcha( $form_slug, $class_name, $input_name );

		return $tag_open . $label . $content . $tag_close;

	}

}



/**

 * Checks the answer for the CAPTCHA

 * @param  mixed   $allow          The result of the pevious checking

 * @param  string  $return_format  The type of the cheking result. Can be set as 'string' or 'wp_error

 * @return mixed                   boolean(true) - in case when the CAPTCHA answer is right, or user`s IP is in the whitelist,

 *                                 string or WP_Error object ( depending on the $return_format variable ) - in case when the CAPTCHA answer is wrong

 */

if ( ! function_exists( 'hctpc_check_custom_form' ) ) {

	function hctpc_check_custom_form( $allow = true, $return_format = 'string' ) {

		global $hctpc_options, $hctpc_ip_in_whitelist;



		/*

		 * Whether the user's IP is in the whitelist

		 */

		if ( is_null( $hctpc_ip_in_whitelist ) )

			$hctpc_ip_in_whitelist = hctpc_whitelisted_ip();



		if ( $hctpc_ip_in_whitelist )

			return $allow;



		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );



		$error_code = '';



		/* The time limit is exhausted */

		if ( hctpc_limit_exhausted() )

			$error_code = 'time_limit_off';

		/* Not enough data to verify the CAPTCHA answer */

		elseif (

			! isset( $_REQUEST['hctpc_result'] ) ||

			! isset( $_REQUEST['hctpc_number'] ) ||

			! isset( $_REQUEST['hctpc_time'] )

		)

			$error_code = 'no_answer';

		/* The CAPTCHA answer is wrong */

		elseif (

			0 !== strcasecmp( trim( hctpc_decode( $_REQUEST['hctpc_result'], $hctpc_options['str_key']['key'], $_REQUEST['hctpc_time'] ) ), $_REQUEST['hctpc_number'] )

		)

			$error_code = 'wrong_answer';



		/* The CAPTCHA answer is right */

		if ( empty( $error_code ) )

			return $allow;



		/* Fetch the error message */

		if ( 'string' == $return_format ) {

			$allow = $hctpc_options[ $error_code ];

		} else {

			if ( ! is_wp_error( $allow ) )

				$allow = new WP_Error();

			$allow->add( "hctpc_error_{$error_code}", $hctpc_options[ $error_code ] );

		}



		return $allow;

	}

}



/* Functionality of the captcha logic work */

if ( ! function_exists( 'hctpc_display_captcha' ) ) {

	function hctpc_display_captcha( $form_slug = 'general', $class_name = "", $input_name = 'hctpc_number' ) {

		global $hctpc_options;



		if ( ! isset( $hctpc_options['str_key'] ) )

			$hctpc_options = get_option( 'hctpc_options' );

		if ( empty( $hctpc_options['str_key']['key'] ) || $hctpc_options['str_key']['time'] < time() - ( 24 * 60 * 60 ) )

			hctpc_generate_key();

		$str_key = $hctpc_options['str_key']['key'];



		/**

		 * Escaping function parameters

		 * @since 4.2.3

		 */

		$form_slug  = esc_attr( $form_slug );

		$form_slug  = empty( $form_slug ) ? 'general' : $form_slug;

		$class_name = esc_attr( $class_name );

		$input_name = esc_attr( $input_name );

		$input_name = empty( $input_name ) ? 'hctpc_number' : $input_name;



		/**

		 * In case when the CAPTCHA uses in the custom form

		 * and there is no saved settings for this form

		 * making an attempt to get default settings

		 * @since 4.2.3

		 */

		if ( ! array_key_exists( $form_slug, $hctpc_options['forms'] ) ) {

			if ( ! function_exists( 'hctpc_get_default_options' ) )

				require_once( dirname( __FILE__ ) . '/includes/helpers.php' );

			$default_options = hctpc_get_default_options();

			/* prevent the need to get default settings on the next displaying of the CAPTCHA */

			if ( array_key_exists( $form_slug, $default_options['forms'] ) ) {

				$hctpc_options['forms'][ $form_slug ] = $default_options['forms'][ $form_slug ];

				update_option( 'hctpc_options' );

			} else {

				$form_slug = 'general';

			}

		}



		/**

		 * Display only the CAPTCHA container to replace it with the CAPTCHA

		 * after the whole page loading via AJAX

		 * @since 4.2.3

		 */

		if ( $hctpc_options['load_via_ajax'] && ! defined( 'hctpc_RELOAD_AJAX' ) ) {

			return hctpc_add_scripts() .

				'<span

				class="hctpc_wrap hctpc_ajax_wrap"

				data-hctpc-form="' . $form_slug . '"

				data-hctpc-input="' . $input_name . '"

				data-hctpc-class="' . $class_name . '">

					<noscript>' .

					__( 'In order to pass the CAPTCHA please enable JavaScript', 'captcha' ) .

					'</noscript>

				</span>';

		}



		$id_postfix = rand( 0, 100 );

		$hidden_result_name = $input_name == 'hctpc_number' ? 'hctpc_result' : $input_name . '-hctpc_result';

		$time = time();



		if ( 'recognition' == $hctpc_options['type'] ) {

			$string = '';

			$captcha_content = '<span class="hctpc_images_wrap">';

			$count = $hctpc_options['images_count'];

			while ( $count != 0 ) {

				/*

				 * get element

				 */

				$image = rand( 1, 9 );

				$array_key = mt_rand( 0, abs( count( $hctpc_options['used_packages'] ) - 1 ) );

				$operand =

						empty( $hctpc_options['used_packages'][ $array_key ] )

					?

						hctpc_generate_value( $image, false )

					:

						hctpc_get_image( $image, '', $hctpc_options['used_packages'][ $array_key ], false );



				$captcha_content .= '<span class="hctpc_span">' . $operand . '</span>';

				$string .= $image;

				$count--;

			}

			$captcha_content .= '</span>

				<input id="hctpc_input_' . $id_postfix . '" class="hctpc_input ' . $class_name . '" type="text" autocomplete="off" name="' . $input_name . '" value="" maxlength="' . $hctpc_options['images_count'] . '" size="' . $hctpc_options['images_count'] . '" aria-required="true" required="required" style="margin-bottom:0;font-size: 12px;max-width:100%;" />

				<input type="hidden" name="' . $hidden_result_name . '" value="' . hctpc_encode( $string, $str_key, $time ) . '" />';

		} else {

			/*

			 * array of math actions

			 */

			$math_actions = array();

			if ( in_array( 'plus', $hctpc_options['math_actions'] ) )

				$math_actions[] = '&#43;';

			if ( in_array( 'minus', $hctpc_options['math_actions'] ) )

				$math_actions[] = '&minus;';

			if ( in_array( 'multiplications', $hctpc_options['math_actions'] ) )

				$math_actions[] = '&times;';

			/* current math action */

			$rand_math_action = rand( 0, count( $math_actions) - 1 );



			/*

			 * get elements of mathematical expression

			 */

			$array_math_expression    = array();

			$array_math_expression[0] = rand( 1, 9 ); /* first part */

			$array_math_expression[1] = rand( 1, 9 ); /* second part */

			/* Calculation of the result */

			switch( $math_actions[ $rand_math_action ] ) {

				case "&#43;":

					$array_math_expression[2] = $array_math_expression[0] + $array_math_expression[1];

					break;

				case "&minus;":

					/* Result must not be equal to the negative number */

					if ( $array_math_expression[0] < $array_math_expression[1] ) {

						$number = $array_math_expression[0];

						$array_math_expression[0] = $array_math_expression[1];

						$array_math_expression[1] = $number;

					}

					$array_math_expression[2] = $array_math_expression[0] - $array_math_expression[1];

					break;

				case "&times;":

					$array_math_expression[2] = $array_math_expression[0] * $array_math_expression[1];

					break;

			}



			/*


			 * array of allowed formats

			 */

			$allowed_formats = array();

			$use_words = $use_numbeers = false;

			if ( in_array( 'numbers', $hctpc_options["operand_format"] ) ) {

				$allowed_formats[] = 'number';

				$use_words         = true;

			}

			if ( in_array( 'words', $hctpc_options["operand_format"] ) ) {

				$allowed_formats[] = 'word';

				$use_numbeers      = true;

			}

			if ( in_array( 'images', $hctpc_options["operand_format"] ) )

				$allowed_formats[] = 'image';

			$use_only_words = ( $use_words && ! $use_numbeers ) || ! $use_words;

			/* number of field, which will be as <input type="number"> */

			$rand_input = rand( 0, 2 );



			/*

			 * get current format for each operand

			 * for example array( 'text', 'input', 'number' )

			 */

			$operand_formats = array();

			$max_rand_value = count( $allowed_formats ) - 1;

			for ( $i = 0; $i < 3; $i ++ )

				$operand_formats[] = $rand_input == $i ? 'input' : $allowed_formats[ mt_rand( 0, $max_rand_value ) ];



			/*

			 * get value of each operand

			 */

			$operand    = array();



			foreach ( $operand_formats as $key => $format ) {

				switch ( $format ) {

					case 'input':

						$operand[] = '<input id="hctpc_input_' . $id_postfix . '" class="hctpc_input ' . $class_name . '" type="text" autocomplete="off" name="' . $input_name . '" value="" maxlength="2" size="2" aria-required="true" required="required" style="margin-bottom:0;display:inline;font-size: 12px;width: 40px;" />';

						break;

					case 'word':

						$operand[] = hctpc_generate_value( $array_math_expression[ $key ] );

						break;

					case 'image':

						$array_key = mt_rand( 0, abs( count( $hctpc_options['used_packages'] ) - 1 ) );

						$operand[] =

								empty( $hctpc_options['used_packages'][ $array_key ] )

							?

								hctpc_generate_value( $array_math_expression[ $key ] )

							:

								hctpc_get_image( $array_math_expression[ $key ], $key, $hctpc_options['used_packages'][ $array_key ], $use_only_words );

						break;

					case 'number':

					default:

						$operand[] = $array_math_expression[ $key ];

						break;

				}

			}

			$captcha_content = '<span class="hctpc_span">' . $operand[0] . '</span>

					<span class="hctpc_span">&nbsp;' . $math_actions[ $rand_math_action ] . '&nbsp;</span>

					<span class="hctpc_span">' . $operand[1] . '</span>

					<span class="hctpc_span">&nbsp;=&nbsp;</span>

					<span class="hctpc_span">' . $operand[2] . '</span>

					<input type="hidden" name="' . $hidden_result_name . '" value="' . hctpc_encode( $array_math_expression[ $rand_input ], $str_key, $time ) . '" />';

		}



		return

			hctpc_add_time_limit_notice( $id_postfix ) .

			hctpc_add_scripts() .

			'<span class="hctpc_wrap hctpc_' . $hctpc_options['type'] . '">

				<label class="hctpc_label" for="hctpc_input_' . $id_postfix . '">' .

					$captcha_content .

					'<input type="hidden" name="hctpc_time" value="' . $time . '" />

					<input type="hidden" name="hctpc_form" value="' . $form_slug . '" />

				</label>' .

				hctpc_add_reload_button( !! $hctpc_options['display_reload_button'] ) .

			'</span>';

	}

}



/**

 * Add necessary js scripts

 * @uses     for including necessary scripts on the pages witn the CAPTCHA only

 * @since    4.2.0

 * @param    void

 * @return   string   empty string - if the form has been loaded by PHP or the CAPTCHA has been reloaded, inline javascript - if the form has been loaded by AJAX

 */

if ( ! function_exists( 'hctpc_add_scripts' ) ) {

	function hctpc_add_scripts () {

		global $hctpc_options;



		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			return

					defined( 'hctpc_RELOAD_AJAX' )

				?

					''

				:

					/*

					 * this script will be included if the from was loaded via AJAX only

					 * but not during the CAPTCHA reloading

					 */

					'<script class="hctpc_to_remove" type="text/javascript">

						(function( d, tag, id ) {

							var script = d.getElementById( id );

							if ( script )

								return;

							add_script( "", "", id );



							if ( typeof( hctpc_vars ) == "undefined" ) {

								var local = {

									nonce:     "' . wp_create_nonce( 'hctpc', 'hctpc_nonce' ) . '",

									ajaxurl:   "' . admin_url( 'admin-ajax.php' ) . '",

									enlarge:   "' . $hctpc_options['enlarge_images'] . '"

								};

								add_script( "", "/* <![CDATA[ */var hctpc_vars=" + JSON.stringify( local ) + "/* ]]> */" );

							}



							d.addEventListener( "DOMContentLoaded", function() {

								var scripts         = d.getElementsByTagName( tag ),

									captcha_script  = /' . addcslashes( plugins_url( 'js/front_end_script.js' , __FILE__ ), '/' ) . '/,

									include_captcha = true;

								if ( scripts ) {

									for ( var i = 0; i < scripts.length; i++ ) {

										if ( scripts[ i ].src.match( captcha_script ) ) {

											include_captcha = false;

											break;

										}

									}

								}

								if ( typeof jQuery == "undefined" ) {

									var siteurl = "' . get_option( 'siteurl' ) . '";

									add_script( siteurl + "/wp-includes/js/jquery/jquery.js" );

									add_script( siteurl + "/wp-includes/js/jquery/jquery-migrate.min.js" );

								}

								if ( include_captcha )

									add_script( "' . plugins_url( 'js/front_end_script.js' , __FILE__ ) . '" );

							});



							function add_script( url, content, js_id ) {

								url     = url     || "";

								content = content || "";

								js_id   = js_id   || "";

								var script = d.createElement( tag );

								if ( url )

									script.src = url;

								if ( content )

									script.appendChild( d.createTextNode( content ) );

								if ( js_id )

									script.id = js_id;

								script.setAttribute( "type", "text/javascript" );

								d.body.appendChild( script );

							}

						})( document, "script", "hctpc_script_loader" );

					</script>';

		} elseif ( ! wp_script_is( 'hctpc_front_end_script', 'registered' ) ) {

			wp_register_script( 'hctpc_front_end_script', plugins_url( 'js/front_end_script.js' , __FILE__ ), array( 'jquery' ), false, $hctpc_options['plugin_option_version'] );

			add_action( 'wp_footer', 'hctpc_front_end_scripts' );

			if (

				$hctpc_options['forms']['wp_login']['enable'] ||

				$hctpc_options['forms']['wp_register']['enable'] ||

				$hctpc_options['forms']['wp_lost_password']['enable']

			)

				add_action( 'login_footer', 'hctpc_front_end_scripts' );

		}

		return '';

	}

}



/**

 * Adds a notice about the time expiration

 * @since     4.2.0

 * @param     int        $id_postfix    to generate an unique css ID on the page if there are more then one CAPTCHA

 * @return    string                    the message about the exhaustion of time limit and inline script for the displaying of this message

 */

if ( ! function_exists( 'hctpc_add_time_limit_notice' ) ) {

	function hctpc_add_time_limit_notice( $id_postfix ) {

		global $hctpc_options;



		if ( ! $hctpc_options['enable_time_limit'] || ! $hctpc_options['time_limit'] )

			return '';



		$id = "hctpc_time_limit_notice_{$id_postfix}";

		return

			'<script class="hctpc_to_remove">

				(function( timeout ) {

					setTimeout(

						function() {

							var notice = document.getElementById("' . $id . '");

							if ( notice )

								notice.style.display = "block";

						},

						timeout

					);

				})(' . $hctpc_options['time_limit'] . '000);

			</script>

			<span id="' . $id . '" class="hctpc_time_limit_notice hctpc_to_remove">' . $hctpc_options['time_limit_off_notice'] . '</span>';

	}

}



/**

 * Add a reload button to the CAPTCHA block

 * @since     4.2.0

 * @param     boolean     $add_button  if 'true' - the button will be added

 * @return    string                   the button`s HTML-content

 */

if ( ! function_exists( 'hctpc_add_reload_button' ) ) {

	function hctpc_add_reload_button( $add_button ) {

		return

				$add_button

			?

				'<span class="hctpc_reload_button_wrap hide-if-no-js">

					<noscript>

						<style type="text/css">

							.hide-if-no-js {

								display: none !important;

							}

						</style>

					</noscript>

					<span class="hctpc_reload_button dashicons dashicons-update"></span>

				</span>'

			:

				'';

	}

}



/**

 * Display image in CAPTCHA

 * @param    int     $value       value of element of mathematical expression

 * @param    int     $place       which is an element in the mathematical expression

 * @param    array   $package_id  what package to use in current CAPTCHA ( if it is '-1' then all )

 * @return   string               html-structure of element

 */

if ( ! function_exists( 'hctpc_get_image' ) ) {

	function hctpc_get_image( $value, $place, $package_id, $use_only_words ) {

		global $wpdb, $hctpc_options;



		$result = array();

		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );



		if ( empty( $hctpc_options['used_packages'] ) )

			return hctpc_generate_value( $value, $use_only_words );



		$where = -1 == $package_id ? ' IN (' . implode( ',', $hctpc_options['used_packages'] ) . ')' : '=' . $package_id;

		$images = $wpdb->get_results(

			"SELECT

				`{$wpdb->base_prefix}hctpc_images`.`name` AS `file`,

				`{$wpdb->base_prefix}hctpc_packages`.`folder` AS `folder`

			FROM

				`{$wpdb->base_prefix}hctpc_images`

			LEFT JOIN

				`{$wpdb->base_prefix}hctpc_packages`

			ON

				`{$wpdb->base_prefix}hctpc_packages`.`id`=`{$wpdb->base_prefix}hctpc_images`.`package_id`

			WHERE

				`{$wpdb->base_prefix}hctpc_images`.`package_id` {$where}

				AND

				`{$wpdb->base_prefix}hctpc_images`.`number`={$value};",

			ARRAY_N

		);

		if ( empty( $images ) )

			return hctpc_generate_value( $value, $use_only_words );



		if ( is_multisite() ) {

			switch_to_blog( 1 );

			$upload_dir = wp_upload_dir();

			restore_current_blog();

		} else {

			$upload_dir = wp_upload_dir();

		}

		$current_image = $images[ mt_rand( 0, count( $images ) - 1 ) ];

		$src = $upload_dir['basedir'] . '/captcha_images/' . $current_image[1] . '/' . $current_image[0];

		if ( file_exists( $src ) ) {

			if ( 1 == $hctpc_options['enlarge_images'] ) {

				switch( $place ) {

					case 0:

						$class = 'hctpc_left';

						break;

					case 1:

						$class = 'hctpc_center';

						break;

					case 2:

						$class = 'hctpc_right';

						break;

					default:

						$class = '';

						break;

				}

			} else {

				$class = '';

			}

			$src = $upload_dir['basedir'] . '/captcha_images/' . $current_image[1] . '/' . $current_image[0];

			$image_data = getimagesize( $src );

			return isset( $image_data['mime'] ) && ! empty( $image_data['mime'] ) ? '<img class="hctpc_img ' . $class . '" src="data:' . $image_data['mime'] . ';base64,'. base64_encode( file_get_contents( $src ) ) . '" alt="image"/>' :  hctpc_generate_value( $value, $use_only_words );

		} else {

			return hctpc_generate_value( $value, $use_only_words );

		}

	}

}



if ( ! function_exists( 'hctpc_generate_value' ) ) {

	function hctpc_generate_value( $value, $use_only_words = true ) {

		$random = $use_only_words  ? 1 : mt_rand( 0, 1 );

		if ( 1 == $random ) {

			$number_string = array(

				0 => __( 'zero', 'captcha' ),

				1 => __( 'one', 'captcha' ),

				2 => __( 'two', 'captcha' ),

				3 => __( 'three', 'captcha' ),

				4 => __( 'four', 'captcha' ),

				5 => __( 'five', 'captcha' ),

				6 => __( 'six', 'captcha' ),

				7 => __( 'seven', 'captcha' ),

				8 => __( 'eight', 'captcha' ),

				9 => __( 'nine', 'captcha' ),



				10 => __( 'ten', 'captcha' ),

				11 => __( 'eleven', 'captcha' ),

				12 => __( 'twelve', 'captcha' ),

				13 => __( 'thirteen', 'captcha' ),

				14 => __( 'fourteen', 'captcha' ),

				15 => __( 'fifteen', 'captcha' ),

				16 => __( 'sixteen', 'captcha' ),

				17 => __( 'seventeen', 'captcha' ),

				18 => __( 'eighteen', 'captcha' ),

				19 => __( 'nineteen', 'captcha' ),



				20 => __( 'twenty', 'captcha' ),

				21 => __( 'twenty one', 'captcha' ),

				22 => __( 'twenty two', 'captcha' ),

				23 => __( 'twenty three', 'captcha' ),

				24 => __( 'twenty four', 'captcha' ),

				25 => __( 'twenty five', 'captcha' ),

				26 => __( 'twenty six', 'captcha' ),

				27 => __( 'twenty seven', 'captcha' ),

				28 => __( 'twenty eight', 'captcha' ),

				29 => __( 'twenty nine', 'captcha' ),



				30 => __( 'thirty', 'captcha' ),

				31 => __( 'thirty one', 'captcha' ),

				32 => __( 'thirty two', 'captcha' ),

				33 => __( 'thirty three', 'captcha' ),

				34 => __( 'thirty four', 'captcha' ),

				35 => __( 'thirty five', 'captcha' ),

				36 => __( 'thirty six', 'captcha' ),

				37 => __( 'thirty seven', 'captcha' ),

				38 => __( 'thirty eight', 'captcha' ),

				39 => __( 'thirty nine', 'captcha' ),



				40 => __( 'forty', 'captcha' ),

				41 => __( 'forty one', 'captcha' ),

				42 => __( 'forty two', 'captcha' ),

				43 => __( 'forty three', 'captcha' ),

				44 => __( 'forty four', 'captcha' ),

				45 => __( 'forty five', 'captcha' ),

				46 => __( 'forty six', 'captcha' ),

				47 => __( 'forty seven', 'captcha' ),

				48 => __( 'forty eight', 'captcha' ),

				49 => __( 'forty nine', 'captcha' ),



				50 => __( 'fifty', 'captcha' ),

				51 => __( 'fifty one', 'captcha' ),

				52 => __( 'fifty two', 'captcha' ),

				53 => __( 'fifty three', 'captcha' ),

				54 => __( 'fifty four', 'captcha' ),

				55 => __( 'fifty five', 'captcha' ),

				56 => __( 'fifty six', 'captcha' ),

				57 => __( 'fifty seven', 'captcha' ),

				58 => __( 'fifty eight', 'captcha' ),

				59 => __( 'fifty nine', 'captcha' ),



				60 => __( 'sixty', 'captcha' ),

				61 => __( 'sixty one', 'captcha' ),

				62 => __( 'sixty two', 'captcha' ),

				63 => __( 'sixty three', 'captcha' ),

				64 => __( 'sixty four', 'captcha' ),

				65 => __( 'sixty five', 'captcha' ),

				66 => __( 'sixty six', 'captcha' ),

				67 => __( 'sixty seven', 'captcha' ),

				68 => __( 'sixty eight', 'captcha' ),

				69 => __( 'sixty nine', 'captcha' ),



				70 => __( 'seventy', 'captcha' ),

				71 => __( 'seventy one', 'captcha' ),

				72 => __( 'seventy two', 'captcha' ),

				73 => __( 'seventy three', 'captcha' ),

				74 => __( 'seventy four', 'captcha' ),

				75 => __( 'seventy five', 'captcha' ),

				76 => __( 'seventy six', 'captcha' ),

				77 => __( 'seventy seven', 'captcha' ),

				78 => __( 'seventy eight', 'captcha' ),

				79 => __( 'seventy nine', 'captcha' ),



				80 => __( 'eighty', 'captcha' ),

				81 => __( 'eighty one', 'captcha' ),

				82 => __( 'eighty two', 'captcha' ),

				83 => __( 'eighty three', 'captcha' ),

				84 => __( 'eighty four', 'captcha' ),

				85 => __( 'eighty five', 'captcha' ),

				86 => __( 'eighty six', 'captcha' ),

				87 => __( 'eighty seven', 'captcha' ),

				88 => __( 'eighty eight', 'captcha' ),

				89 => __( 'eighty nine', 'captcha' ),



				90 => __( 'ninety', 'captcha' ),

				91 => __( 'ninety one', 'captcha' ),

				92 => __( 'ninety two', 'captcha' ),

				93 => __( 'ninety three', 'captcha' ),

				94 => __( 'ninety four', 'captcha' ),

				95 => __( 'ninety five', 'captcha' ),

				96 => __( 'ninety six', 'captcha' ),

				97 => __( 'ninety seven', 'captcha' ),

				98 => __( 'ninety eight', 'captcha' ),

				99 => __( 'ninety nine', 'captcha' )

			);

			$value = hctpc_converting( $number_string[ $value ] );

		}

		return $value;

	}

}



if ( ! function_exists ( 'hctpc_converting' ) ) {

	function hctpc_converting( $number_string ) {

		global $hctpc_options;



		if ( in_array( 'words', $hctpc_options['operand_format'] ) && 'en-US' == get_bloginfo( 'language' ) ) {

			/* Array of htmlspecialchars for numbers and english letters */

			$htmlspecialchars_array			=	array();

			$htmlspecialchars_array['a']	=	'&#97;';

			$htmlspecialchars_array['b']	=	'&#98;';

			$htmlspecialchars_array['c']	=	'&#99;';

			$htmlspecialchars_array['d']	=	'&#100;';

			$htmlspecialchars_array['e']	=	'&#101;';

			$htmlspecialchars_array['f']	=	'&#102;';

			$htmlspecialchars_array['g']	=	'&#103;';

			$htmlspecialchars_array['h']	=	'&#104;';

			$htmlspecialchars_array['i']	=	'&#105;';

			$htmlspecialchars_array['j']	=	'&#106;';

			$htmlspecialchars_array['k']	=	'&#107;';

			$htmlspecialchars_array['l']	=	'&#108;';

			$htmlspecialchars_array['m']	=	'&#109;';

			$htmlspecialchars_array['n']	=	'&#110;';

			$htmlspecialchars_array['o']	=	'&#111;';

			$htmlspecialchars_array['p']	=	'&#112;';

			$htmlspecialchars_array['q']	=	'&#113;';

			$htmlspecialchars_array['r']	=	'&#114;';

			$htmlspecialchars_array['s']	=	'&#115;';

			$htmlspecialchars_array['t']	=	'&#116;';

			$htmlspecialchars_array['u']	=	'&#117;';

			$htmlspecialchars_array['v']	=	'&#118;';

			$htmlspecialchars_array['w']	=	'&#119;';

			$htmlspecialchars_array['x']	=	'&#120;';

			$htmlspecialchars_array['y']	=	'&#121;';

			$htmlspecialchars_array['z']	=	'&#122;';



			$simbols_lenght = strlen( $number_string );

			$simbols_lenght--;

			$number_string_new	=	str_split( $number_string );

			$converting_letters	=	rand( 1, $simbols_lenght );

			while ( $converting_letters != 0 ) {

				$position = rand( 0, $simbols_lenght );

				$number_string_new[ $position ] = isset( $htmlspecialchars_array[ $number_string_new[ $position ] ] ) ? $htmlspecialchars_array[ $number_string_new[ $position ] ] : $number_string_new[ $position ];

				$converting_letters--;

			}

			$number_string = '';

			foreach ( $number_string_new as $key => $value ) {

				$number_string .= $value;

			}

			return $number_string;

		} else

			return $number_string;

	}

}



/* Function for encodinf number */

if ( ! function_exists( 'hctpc_encode' ) ) {

	function hctpc_encode( $String, $Password, $timestamp ) {

		/* Check if key for encoding is empty */

		if ( ! $Password ) die ( __( "Encryption password is not set", 'captcha' ) );



		$Salt   = md5( $timestamp, true );

		$String = substr( pack( "H*", sha1( $String ) ), 0, 1 ) . $String;

		$StrLen = strlen( $String );

		$Seq    = $Password;

		$Gamma  = '';



		while ( strlen( $Gamma ) < $StrLen ) {

			$Seq = pack( "H*", sha1( $Seq . $Gamma . $Salt ) );

			$Gamma .=substr( $Seq, 0, 8 );

		}



		return base64_encode( $String ^ $Gamma );

	}

}



/* Function for decoding number */

if ( ! function_exists( 'hctpc_decode' ) ) {

	function hctpc_decode( $String, $Key, $timestamp ) {

		/* Check if key for encoding is empty */

		if ( ! $Key ) die ( __( "Decryption password is not set", 'captcha' ) );



		$Salt   = md5( $timestamp, true );

		$StrLen = strlen( $String );

		$Seq    = $Key;

		$Gamma  = '';



		while ( strlen( $Gamma ) < $StrLen ) {

			$Seq = pack( "H*", sha1( $Seq . $Gamma . $Salt ) );

			$Gamma.= substr( $Seq, 0, 8 );

		}



		$String = base64_decode( $String );

		$String = $String ^ $Gamma;

		$DecodedString = substr( $String, 1 );

		$Error         = ord( substr( $String, 0, 1 ) ^ substr( pack( "H*", sha1( $DecodedString ) ), 0, 1 ) );

		return $Error ? false : $DecodedString;

	}

}



/**

 * Check CAPTCHA life time

 * @return boolean

 */

if ( ! function_exists( 'hctpc_limit_exhausted' ) ) {

	function hctpc_limit_exhausted() {

		global $hctpc_options;

		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );

		return

				1 == $hctpc_options['enable_time_limit'] &&       /* if 'Enable time limit' option is enabled */

				isset( $_REQUEST['hctpc_time'] ) &&            /* if form was sended */

				$hctpc_options['time_limit'] < time() - $_REQUEST['hctpc_time'] /* if time limit is exhausted */

			?

				true

			:

				false;

	}

}



if ( ! function_exists( 'hctpc_front_end_styles' ) ) {

	function hctpc_front_end_styles() {

		if ( ! is_admin() ) {

			global $hctpc_options;

			if ( empty( $hctpc_options ) )

				$hctpc_options = get_option( 'hctpc_options' );



			wp_enqueue_style( 'hctpc_stylesheet', plugins_url( 'css/front_end_style.css', __FILE__ ), array(), $hctpc_options['plugin_option_version'] );

			wp_enqueue_style( 'dashicons' );



			$device_type = isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Windows Phone|Opera Mini/i', $_SERVER['HTTP_USER_AGENT'] ) ? 'mobile' : 'desktop';

			wp_enqueue_style( "hctpc_{$device_type}_style", plugins_url( "css/{$device_type}_style.css", __FILE__ ), array(), $hctpc_options['plugin_option_version'] );

		}

	}

}



if ( ! function_exists( 'hctpc_front_end_scripts' ) ) {

	function hctpc_front_end_scripts() {

		global $hctpc_options;



		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );



		if (

			wp_script_is( 'hctpc_front_end_script', 'registered' ) &&

			! wp_script_is( 'hctpc_front_end_script', 'enqueued' )

		) {

			wp_enqueue_script( 'hctpc_front_end_script' );

			$args = array(

				'nonce'   => wp_create_nonce( 'hctpc', 'hctpc_nonce' ),

				'ajaxurl' => admin_url( 'admin-ajax.php' ),

				'enlarge' => $hctpc_options['enlarge_images']

			);

			wp_localize_script( 'hctpc_front_end_script', 'hctpc_vars', $args );

		}

	}

}



if ( ! function_exists ( 'hctpc_admin_head' ) ) {

	function hctpc_admin_head() {

		global $hctpc_options;



		$pages = array(

			'captcha.php',

			'captcha-packages.php',

			'captcha-whitelist.php'

		);



		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], $pages ) ) {

			global $wp_scripts;



			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.12.1';



			wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );

			wp_enqueue_script( 'jquery-ui-resizable' );

			wp_enqueue_script( 'jquery-ui-tabs' );



			wp_enqueue_style( 'hctpc_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $hctpc_options['plugin_option_version'] );



			wp_enqueue_script( 'hctpc_script', plugins_url( 'js/script.js' , __FILE__ ), array( 'jquery', 'jquery-ui-resizable', 'jquery-ui-tabs', 'jquery-ui-tooltip' ), $hctpc_options['plugin_option_version'] );



		}

	}

}



if ( ! function_exists( 'hctpc_reload' ) ) {

	function hctpc_reload() {

		check_ajax_referer( 'hctpc', 'hctpc_nonce' );



		if ( ! defined( 'hctpc_RELOAD_AJAX' ) )

			define( 'hctpc_RELOAD_AJAX', true );



		$form_slug  = isset( $_REQUEST['hctpc_form_slug'] )   ? esc_attr( $_REQUEST['hctpc_form_slug'] )   : 'general';

		$class      = isset( $_REQUEST['hctpc_input_class'] ) ? esc_attr( $_REQUEST['hctpc_input_class'] ) : '';

		$input_name = isset( $_REQUEST['hctpc_input_name'] )  ? esc_attr( $_REQUEST['hctpc_input_name'] )  : '';



		echo hctpc_display_captcha_custom( $form_slug, $class, $input_name );

		die();

	}

}



if ( ! function_exists( 'hctpc_plugin_action_links' ) ) {

	function hctpc_plugin_action_links( $links, $file ) {

		if ( ! is_network_admin() ) {

			static $this_plugin;

			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);



			if ( $file == $this_plugin ) {

				$settings_link = '<a href="admin.php?page=captcha.php">' . __( 'Settings', 'captcha' ) . '</a>';

				array_unshift( $links, $settings_link );

			}

		}

		return $links;

	}

}



if ( ! function_exists( 'hctpc_register_plugin_links' ) ) {

	function hctpc_register_plugin_links( $links, $file ) {

		$base = plugin_basename( __FILE__ );

		if ( $file == $base ) {

			if ( ! is_network_admin() )

				$links[]	=	'<a href="admin.php?page=captcha.php">' . __( 'Settings', 'captcha' ) . '</a>';

			$links[]	=	'<a href="https://wordpress.org/plugins/captcha/" target="_blank">' . __( 'FAQ', 'captcha' ) . '</a>';

			$links[]	=	'<a href="https://wordpress.org/plugins/captcha/" target="_blank">' . __( 'Support', 'captcha' ) . '</a>';

		}

		return $links;

	}

}



/* Notice on the settings page about possible conflict with W3 Total Cache plugin */

if ( ! function_exists( 'hctpc_w3tc_notice' ) ) {

	function hctpc_w3tc_notice() {

		global $hctpc_options, $hctpc_plugin_info;

		if ( ! is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {

			return;

		}



		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );



		if ( empty( $hctpc_options['w3tc_notice'] ) )

			return '';



		if ( isset( $_GET['hctpc_nonce'] ) && wp_verify_nonce( $_GET['hctpc_nonce'], 'hctpc_clean_w3tc_notice' ) ) {

			unset( $hctpc_options['w3tc_notice'] );

			update_option( 'hctpc_options', $hctpc_options );

			return '';

		}



		$url = add_query_arg(

			array(

				'hctpc_clean_w3tc_notice'	=> '1',

				'hctpc_nonce'				=> wp_create_nonce( 'hctpc_clean_w3tc_notice' )

			),

			( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']

		);

		$close_link = "<a href=\"{$url}\" class=\"close_icon notice-dismiss\"></a>";

		$settings_link = sprintf(

			'<a href="%1$s">%2$s</a>',

			admin_url( 'admin.php?page=captcha.php#hctpc_load_via_ajax' ),

			__( 'settings page', 'captcha' )

		);

		$message = sprintf(

			__( 'You\'re using W3 Total Cache plugin. If %1$s doesn\'t work properly, please clear the cache in W3 Total Cache plugin and turn on \'%2$s\' option on the plugin %3$s.', 'captcha' ),

			$hctpc_plugin_info['Name'],

			__( 'Show CAPTCHA after the end of the page loading', 'captcha' ),

			$settings_link

		);

		return

			"<style>

				.hctpc_w3tc_notice {

					position: relative;

				}

				.hctpc_w3tc_notice a {

					text-decoration: none;

				}

			</style>

			<div class=\"hctpc_w3tc_notice error\"><p>{$message}</p>{$close_link}</div>";

	}

}



if ( ! function_exists( 'hctpc_renaming_notice' ) ) {

	function hctpc_renaming_notice() {

		global $hctpc_options;



		if ( empty( $hctpc_options ) )

			$hctpc_options = get_option( 'hctpc_options' );



		if ( empty( $hctpc_options['renaming_notice'] ) )

			return '';



		if ( isset( $_GET['hctpc_nonce'] ) && wp_verify_nonce( $_GET['hctpc_nonce'], 'hctpc_clean_renaming_notice' ) ) {

			unset( $hctpc_options['renaming_notice'] );

			update_option( 'hctpc_options', $hctpc_options );

			return '';

		}



		$url = add_query_arg(

			array(

				'hctpc_clean_renaming_notice'	=> '1',

				'hctpc_nonce'				=> wp_create_nonce( 'hctpc_clean_renaming_notice' )

			),

			( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']

		);

		$message = sprintf(

			__( 'Using CAPTCHA for a custom form? Please rename functions - %1$s.', 'captcha' ),

			'<a href="https://wordpress.org/plugins/captcha/#faq" target="_blank">' . __( 'FAQ', 'captcha' ) . '</a>'			

		);

		return

			"<style>

				.hctpc_renaming_notice {

					position: relative;

				}

				.hctpc_renaming_notice a {

					text-decoration: none;

				}

			</style>

			<div class=\"hctpc_renaming_notice error\"><p>{$message}</p><a href=\"{$url}\" class=\"close_icon notice-dismiss\"></a></div>";

	}

}



if ( ! function_exists ( 'hctpc_plugin_banner' ) ) {

	function hctpc_plugin_banner() {

		/* Displays notice about possible conflict with W3 Total Cache plugin */

		echo hctpc_w3tc_notice();



		/* Displays notice about renaming functions */

		echo hctpc_renaming_notice();

	}

}



/* Function for delete delete options */

if ( ! function_exists ( 'hctpc_delete_options' ) ) {

	function hctpc_delete_options() {

		global $wpdb;

		$all_plugins        = get_plugins();

		$is_another_captcha = array_key_exists( 'captcha-plus/captcha-plus.php', $all_plugins ) || array_key_exists( 'captcha-pro/captcha_pro.php', $all_plugins );



		/* do nothing more if Plus or Pro BWS CAPTCHA are installed */

		if ( $is_another_captcha )

			return;



		if ( is_multisite() ) {

			$old_blog = $wpdb->blogid;

			/* Get all blog ids */

			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );

			foreach ( $blogids as $blog_id ) {

				switch_to_blog( $blog_id );

				delete_option( 'hctpc_options' );

				$prefix = 1 == $blog_id ? $wpdb->base_prefix : $wpdb->base_prefix . $blog_id . '_';

				$wpdb->query( "DROP TABLE `{$prefix}hctpc_whitelist`;" );

			}

			switch_to_blog( 1 );

			$upload_dir = wp_upload_dir();

			switch_to_blog( $old_blog );

		} else {

			delete_option( 'hctpc_options' );

			$wpdb->query( "DROP TABLE `{$wpdb->prefix}hctpc_whitelist`;" );

			$upload_dir = wp_upload_dir();

		}



		/* delete images */

		$wpdb->query( "DROP TABLE `{$wpdb->base_prefix}hctpc_images`, `{$wpdb->base_prefix}hctpc_packages`;" );

		$images_dir = $upload_dir['basedir'] . '/captcha_images';

		$packages   = scandir( $images_dir );

		if ( is_array( $packages ) ) {

			foreach ( $packages as $package ) {

				if ( ! in_array( $package, array( '.', '..' ) ) ) {

					/* remove all files from package */

					array_map( 'unlink', glob( "{$images_dir}/{$package}/*.*" ) );

					/* remove package */

					rmdir( "{$images_dir}/{$package}" );

				}

			}

		}

		rmdir( $images_dir );

	}

}



/**

 *

 * @since 4.2.3

 */

if( ! function_exists( 'hctpc_captcha_is_needed' ) ) {

	function hctpc_captcha_is_needed( $form_slug, $user_loggged_in ) {

		global $hctpc_options;

		return

			$hctpc_options['forms'][ $form_slug ]['enable'] &&

			(

				! $user_loggged_in ||

				! $hctpc_options['forms'][ $form_slug ]['hide_from_registered']

			);

	}

}



register_activation_hook( __FILE__, 'hctpc_plugin_activate' );



add_action( 'admin_menu', 'hctpc_admin_menu' );



add_action( 'init', 'hctpc_init' );



add_action( 'plugins_loaded', 'hctpc_plugins_loaded' );



/* Additional links on the plugin page */

add_filter( 'plugin_action_links', 'hctpc_plugin_action_links', 10, 2 );

add_filter( 'plugin_row_meta', 'hctpc_register_plugin_links', 10, 2 );



add_action( 'admin_notices', 'hctpc_plugin_banner' );



add_action( 'admin_enqueue_scripts', 'hctpc_admin_head' );

add_action( 'wp_enqueue_scripts', 'hctpc_front_end_styles' );

add_action( 'login_enqueue_scripts', 'hctpc_front_end_styles' );



add_action( 'wp_ajax_hctpc_reload', 'hctpc_reload' );

add_action( 'wp_ajax_nopriv_hctpc_reload', 'hctpc_reload' );



add_filter( 'hctpc_display', 'hctpc_display_filter', 10, 3 );

add_filter( 'hctpc_verify', 'hctpc_check_custom_form', 10, 2 );

add_shortcode( 'hctpc_captcha', 'hctpc_display_captcha_shortcode' );

include "hctpc-contact-form-integration.php";


function captcha_shortcode_custom($tag){

	$captcha =  hctpc_display_filter();
	return $captcha;
	
}
add_shortcode('wp_captcha', 'captcha_shortcode_custom');