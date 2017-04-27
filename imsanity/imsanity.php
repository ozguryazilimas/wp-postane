<?php
/*
Plugin Name: Imsanity
Plugin URI: https://wordpress.org/plugins/imsanity/
Description: Imsanity stops insanely huge image uploads
Author: Shane Bishop
Version: 2.3.9
Author URI: https://ewww.io/
Text Domain: imsanity
License: GPLv3
*/

define( 'IMSANITY_VERSION', '2.3.9' );
define( 'IMSANITY_SCHEMA_VERSION', '1.1' );

define( 'IMSANITY_DEFAULT_MAX_WIDTH', 2048 );
define( 'IMSANITY_DEFAULT_MAX_HEIGHT', 2048 );
define( 'IMSANITY_DEFAULT_BMP_TO_JPG', 1 );
define( 'IMSANITY_DEFAULT_PNG_TO_JPG', 0 );
define( 'IMSANITY_DEFAULT_QUALITY', 82 );

define( 'IMSANITY_SOURCE_POST', 1 );
define( 'IMSANITY_SOURCE_LIBRARY', 2 );
define( 'IMSANITY_SOURCE_OTHER', 4 );

if ( ! defined( 'IMSANITY_AJAX_MAX_RECORDS' ) ) {
	define( 'IMSANITY_AJAX_MAX_RECORDS', 250 );
}

function imsanity_init() {
	/**
	 * Load Translations
	 */
	load_plugin_textdomain( 'imsanity', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * import supporting libraries
 */
include_once( plugin_dir_path(__FILE__) . 'libs/utils.php' );
include_once( plugin_dir_path(__FILE__) . 'settings.php' );
include_once( plugin_dir_path(__FILE__) . 'ajax.php' );

/**
 * Inspects the request and determines where the upload came from.
 *
 * @return IMSANITY_SOURCE_POST | IMSANITY_SOURCE_LIBRARY | IMSANITY_SOURCE_OTHER
 */
function imsanity_get_source() {
	$id = array_key_exists('post_id', $_REQUEST) ? $_REQUEST['post_id'] : '';
	$action = array_key_exists('action', $_REQUEST) ? $_REQUEST['action'] : '';

	// a post_id indicates image is attached to a post
	if ($id > 0) return IMSANITY_SOURCE_POST;

	// post_id of 0 is 3.x otherwise use the action parameter
	if ( $id === 0 || $id === '0' || $action == 'upload-attachment' ) return IMSANITY_SOURCE_LIBRARY;

	// we don't know where this one came from but $_REQUEST['_wp_http_referer'] may contain info
	return IMSANITY_SOURCE_OTHER;
}

/**
 * Given the source, returns the max width/height
 *
 * @example:  list($w,$h) = imsanity_get_max_width_height(IMSANITY_SOURCE_LIBRARY);
 * @param int IMSANITY_SOURCE_POST | IMSANITY_SOURCE_LIBRARY | IMSANITY_SOURCE_OTHER
 */
function imsanity_get_max_width_height( $source ) {
	$w = imsanity_get_option( 'imsanity_max_width',IMSANITY_DEFAULT_MAX_WIDTH );
	$h = imsanity_get_option( 'imsanity_max_height',IMSANITY_DEFAULT_MAX_HEIGHT );

	switch ( $source ) {
		case IMSANITY_SOURCE_POST:
			break;
		case IMSANITY_SOURCE_LIBRARY:
			$w = imsanity_get_option( 'imsanity_max_width_library',$w );
			$h = imsanity_get_option( 'imsanity_max_height_library',$h );
			break;
		default:
			$w = imsanity_get_option( 'imsanity_max_width_other',$w );
			$h = imsanity_get_option( 'imsanity_max_height_other',$h );
			break;
	}

	return array( $w, $h );
}

/**
 * Handler after a file has been uploaded.  If the file is an image, check the size
 * to see if it is too big and, if so, resize and overwrite the original
 * @param Array $params
 */
function imsanity_handle_upload( $params ) {
	/* debug logging... */
	// file_put_contents ( "debug.txt" , print_r($params,1) . "\n" );

	// if "noresize" is included in the filename then we will bypass imsanity scaling
	if ( strpos( $params['file'], 'noresize' ) !== false ) return $params;

	// if preferences specify so then we can convert an original bmp or png file into jpg
	if ( $params['type'] == 'image/bmp' && imsanity_get_option( 'imsanity_bmp_to_jpg', IMSANITY_DEFAULT_BMP_TO_JPG ) ) {
		$params = imsanity_convert_to_jpg( 'bmp', $params );
	}

	if ( $params['type'] == 'image/png' && imsanity_get_option( 'imsanity_png_to_jpg', IMSANITY_DEFAULT_PNG_TO_JPG ) ) {
		$params = imsanity_convert_to_jpg( 'png', $params );
	}

	// make sure this is a type of image that we want to convert and that it exists
	// @TODO when uploads occur via RPC the image may not exist at this location
	$oldPath = $params['file'];

	// @HACK not currently working
	// @see https://wordpress.org/support/topic/images-dont-resize-when-uploaded-via-mobile-device
// 	if (!file_exists($oldPath)) {
// 		$ud = wp_upload_dir();
// 		$oldPath = $ud['path'] . DIRECTORY_SEPARATOR . $oldPath;
// 	}

	if ( ( ! is_wp_error( $params ) ) && is_file( $oldPath ) && is_readable( $oldPath ) && is_writable( $oldPath ) && filesize( $oldPath ) > 0 && in_array( $params['type'], array( 'image/png', 'image/gif', 'image/jpeg' ) ) ) {

		// figure out where the upload is coming from
		$source = imsanity_get_source();

		list( $maxW,$maxH ) = imsanity_get_max_width_height( $source );

		list( $oldW, $oldH ) = getimagesize( $oldPath );

		/* HACK: if getimagesize returns an incorrect value (sometimes due to bad EXIF data..?)
		$img = imagecreatefromjpeg ($oldPath);
		$oldW = imagesx ($img);
		$oldH = imagesy ($img);
		imagedestroy ($img);
		//*/

		/* HACK: an animated gif may have different frame sizes.  to get the "screen" size
		$data = ''; // TODO: convert file to binary
		$header = unpack('@6/vwidth/vheight', $data );
		$oldW = $header['width'];
		$oldH = $header['width'];
		//*/

		if ( ( $oldW > $maxW && $maxW > 0 ) || ( $oldH > $maxH && $maxH > 0 ) ) {
			$quality = imsanity_get_option( 'imsanity_quality', IMSANITY_DEFAULT_QUALITY );

			$ftype = imsanity_quick_mimetype( $oldPath );
			$orientation = imsanity_get_orientation( $oldPath, $ftype );
			// If we are going to rotate the image 90 degrees during the resize, swap the existing image dimensions.
			if ( 6 == $orientation || 8 == $orientation ) {
				$old_oldW = $oldW;
				$oldW = $oldH;
				$oldH = $old_oldW;
			}

			if ( $oldW > $maxW && $maxW > 0 && $oldH > $maxH && $maxH > 0 && apply_filters( 'imsanity_crop_image', false ) ) {
				$newW = $maxW;
				$newH = $maxH;
			} else {
				list( $newW, $newH ) = wp_constrain_dimensions( $oldW, $oldH, $maxW, $maxH );
			}

			remove_filter( 'wp_image_editors', 'ewww_image_optimizer_load_editor', 60 );
			$resizeResult = imsanity_image_resize( $oldPath, $newW, $newH, apply_filters( 'imsanity_crop_image', false ), null, null, $quality );
			if ( function_exists( 'ewww_image_optimizer_load_editor' ) ) {
				add_filter( 'wp_image_editors', 'ewww_image_optimizer_load_editor', 60 );
			}

			/* uncomment to debug error handling code: */
			// $resizeResult = new WP_Error('invalid_image', __(print_r($_REQUEST)), $oldPath);

			if ( $resizeResult && ! is_wp_error( $resizeResult ) ) {
				$newPath = $resizeResult;

				if ( is_file( $newPath ) && filesize( $newPath ) <  filesize( $oldPath ) ) {
					// we saved some file space. remove original and replace with resized image
					unlink( $oldPath );
					rename( $newPath, $oldPath );
				} elseif ( is_file( $newPath ) ) {
					// theresized image is actually bigger in filesize (most likely due to jpg quality).
					// keep the old one and just get rid of the resized image
					unlink( $newPath );
				}
			} else if ( $resizeResult === false ) {
				return $params;
			} else {
				// resize didn't work, likely because the image processing libraries are missing

				// remove the old image so we don't leave orphan files hanging around
				unlink( $oldPath );

				$params = wp_handle_upload_error( $oldPath ,
					sprintf( esc_html__( "Imsanity was unable to resize this image for the following reason: %s. If you continue to see this error message, you may need to install missing server components. If you think you have discovered a bug, please report it on the Imsanity support forum: %s", 'imsanity' ), $resizeResult->get_error_message(), 'https://wordpress.org/support/plugin/imsanity' ) );

			}
		}

	}
	return $params;
}


/**
 * read in the image file from the params and then save as a new jpg file.
 * if successful, remove the original image and alter the return
 * parameters to return the new jpg instead of the original
 *
 * @param string 'bmp' or 'png'
 * @param array $params
 * @return array altered params
 */
function imsanity_convert_to_jpg( $type, $params )
{

	$img = null;

	if ( $type == 'bmp' ) {
		include_once( 'libs/imagecreatefrombmp.php' );
		$img = imagecreatefrombmp( $params['file'] );
	} elseif ( $type == 'png' ) {
		if( ! function_exists( 'imagecreatefrompng' ) ) {
			return wp_handle_upload_error( $params['file'], esc_html__( 'Imsanity requires the GD library to convert PNG images to JPG', 'imsanity' ) );
		}

		$input = imagecreatefrompng( $params['file'] );
		// convert png transparency to white
		$img = imagecreatetruecolor( imagesx( $input ), imagesy( $input ) );
		imagefill( $img, 0, 0, imagecolorallocate( $img, 255, 255, 255 ) );
		imagealphablending( $img, TRUE );
		imagecopy($img, $input, 0, 0, 0, 0, imagesx($input), imagesy($input));
	}
	else {
		return wp_handle_upload_error( $params['file'], esc_html__( 'Unknown image type specified in imsanity_convert_to_jpg', 'imsanity' ) );
	}

	// we need to change the extension from the original to .jpg so we have to ensure it will be a unique filename
	$uploads = wp_upload_dir();
	$oldFileName = basename($params['file']);
	$newFileName = basename(str_ireplace(".".$type, ".jpg", $oldFileName));
	$newFileName = wp_unique_filename( $uploads['path'], $newFileName );

	$quality = imsanity_get_option('imsanity_quality', IMSANITY_DEFAULT_QUALITY );

	if ( imagejpeg( $img, $uploads['path'] . '/' . $newFileName, $quality ) ) {
		// conversion succeeded.  remove the original bmp & remap the params
		unlink($params['file']);

		$params['file'] = $uploads['path'] . '/' . $newFileName;
		$params['url'] = $uploads['url'] . '/' . $newFileName;
		$params['type'] = 'image/jpeg';
	}
	else
	{
		unlink($params['file']);

		return wp_handle_upload_error( $oldPath,
				sprintf( esc_html__( "Imsanity was unable to process the %s file. If you continue to see this error you may need to disable the conversion option in the Imsanity settings.", 'imsanity' ), $type ) );
	}

	return $params;
}

/* add filters to hook into uploads */
add_filter( 'wp_handle_upload', 'imsanity_handle_upload' );
add_action( 'plugins_loaded', 'imsanity_init' );
?>
