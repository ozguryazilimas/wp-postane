<?php
/*
Plugin Name: Imsanity
Plugin URI: http://verysimple.com/products/imsanity/
Description: Imsanity stops insanely huge image uploads
Author: Jason Hinkle
Version: 2.3.6
Author URI: http://verysimple.com/
*/

define('IMSANITY_VERSION','2.3.6');
define('IMSANITY_SCHEMA_VERSION','1.1');

define('IMSANITY_DEFAULT_MAX_WIDTH',2048);
define('IMSANITY_DEFAULT_MAX_HEIGHT',2048);
define('IMSANITY_DEFAULT_BMP_TO_JPG',1);
define('IMSANITY_DEFAULT_PNG_TO_JPG',0);
define('IMSANITY_DEFAULT_QUALITY',90);

define('IMSANITY_SOURCE_POST',1);
define('IMSANITY_SOURCE_LIBRARY',2);
define('IMSANITY_SOURCE_OTHER',4);

if (!defined('IMSANITY_AJAX_MAX_RECORDS')) define('IMSANITY_AJAX_MAX_RECORDS',250);

/**
 * Load Translations
 */
load_plugin_textdomain('imsanity', false, 'imsanity/languages/');

/**
 * import supporting libraries
 */
include_once(plugin_dir_path(__FILE__).'libs/utils.php');
include_once(plugin_dir_path(__FILE__).'settings.php');
include_once(plugin_dir_path(__FILE__).'ajax.php');

/**
 * Fired with the WordPress upload dialog is displayed
 */
function imsanity_upload_ui()
{
	// TODO: output a message on the upload form showing that imanity is enabled
	// echo '<p class="imsanity-upload-message">' . __("Imsanity plugin is enabled.  Add the text 'noresize' to the filename to bypass.") . '</p>';	
}


/**
 * Inspects the request and determines where the upload came from
 * @return IMSANITY_SOURCE_POST | IMSANITY_SOURCE_LIBRARY | IMSANITY_SOURCE_OTHER
 */
function imsanity_get_source()
{

	$id = array_key_exists('post_id', $_REQUEST) ? $_REQUEST['post_id'] : '';
	$action = array_key_exists('action', $_REQUEST) ? $_REQUEST['action'] : '';
	
	// a post_id indicates image is attached to a post
	if ($id > 0) return IMSANITY_SOURCE_POST; 
	
	// post_id of 0 is 3.x otherwise use the action parameter
	if ($id === 0 || $action == 'upload-attachment') return IMSANITY_SOURCE_LIBRARY;
	
	// we don't know where this one came from but $_REQUEST['_wp_http_referer'] may contain info
	return IMSANITY_SOURCE_OTHER;
}

/**
 * Given the source, returns the max width/height
 *
 * @example:  list($w,$h) = imsanity_get_max_width_height(IMSANITY_SOURCE_LIBRARY);
 * @param int IMSANITY_SOURCE_POST | IMSANITY_SOURCE_LIBRARY | IMSANITY_SOURCE_OTHER
 */
function imsanity_get_max_width_height($source)
{
	$w = imsanity_get_option('imsanity_max_width',IMSANITY_DEFAULT_MAX_WIDTH);
	$h = imsanity_get_option('imsanity_max_height',IMSANITY_DEFAULT_MAX_HEIGHT);

	switch ($source)
	{
		case IMSANITY_SOURCE_POST:
			break;
		case IMSANITY_SOURCE_LIBRARY:
			$w = imsanity_get_option('imsanity_max_width_library',$w);
			$h = imsanity_get_option('imsanity_max_height_library',$h);
			break;
		default:
			$w = imsanity_get_option('imsanity_max_width_other',$w);
			$h = imsanity_get_option('imsanity_max_height_other',$h);
			break;
	}

	return array($w,$h);
}

/**
 * Handler after a file has been uploaded.  If the file is an image, check the size
 * to see if it is too big and, if so, resize and overwrite the original
 * @param Array $params
 */
function imsanity_handle_upload($params)
{
	/* debug logging... */
	// file_put_contents ( "debug.txt" , print_r($params,1) . "\n" );
	
	// if "noresize" is included in the filename then we will bypass imsanity scaling
	if (strpos($params['file'],'noresize') !== false) return $params;

	// if preferences specify so then we can convert an original bmp or png file into jpg
	if ($params['type'] == 'image/bmp' && imsanity_get_option('imsanity_bmp_to_jpg',IMSANITY_DEFAULT_BMP_TO_JPG)) {
		$params = imsanity_convert_to_jpg('bmp',$params);
	}
	
	if ($params['type'] == 'image/png' && imsanity_get_option('imsanity_png_to_jpg',IMSANITY_DEFAULT_PNG_TO_JPG)) {
		$params = imsanity_convert_to_jpg('png',$params);
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
	
	if ( (!is_wp_error($params)) && file_exists($oldPath) && in_array($params['type'], array('image/png','image/gif','image/jpeg')))
	{

		// figure out where the upload is coming from
		$source = imsanity_get_source();

		list($maxW,$maxH) = imsanity_get_max_width_height($source);

		list($oldW, $oldH) = getimagesize( $oldPath );
		
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

		if (($oldW > $maxW && $maxW > 0) || ($oldH > $maxH && $maxH > 0))
		{
			$quality = imsanity_get_option('imsanity_quality',IMSANITY_DEFAULT_QUALITY);

			list($newW, $newH) = wp_constrain_dimensions($oldW, $oldH, $maxW, $maxH);

			// this is wordpress prior to 3.5 (image_resize deprecated as of 3.5)
			$resizeResult = imsanity_image_resize( $oldPath, $newW, $newH, false, null, null, $quality);

			/* uncomment to debug error handling code: */
			// $resizeResult = new WP_Error('invalid_image', __(print_r($_REQUEST)), $oldPath);

			if (!is_wp_error($resizeResult))
			{
				$newPath = $resizeResult;
				
				if (filesize($newPath) <  filesize($oldPath)) {
					// we saved some file space. remove original and replace with resized image
					unlink($oldPath);
					rename($newPath, $oldPath);
				}
				else {
					// theresized image is actually bigger in filesize (most likely due to jpg quality).
					// keep the old one and just get rid of the resized image
					unlink($newPath);
				}
			}
			else
			{
				// resize didn't work, likely because the image processing libraries are missing
				
				// remove the old image so we don't leave orphan files hanging around
				unlink($oldPath);
				
				$params = wp_handle_upload_error( $oldPath ,
					sprintf( __("Oh Snap! Imsanity was unable to resize this image "
					. "for the following reason: '%s'
					.  If you continue to see this error message, you may need to either install missing server"
					. " components or disable the Imsanity plugin."
					. "  If you think you have discovered a bug, please report it on the Imsanity support forum.", 'imsanity' ) ,$resizeResult->get_error_message() ) );

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
function imsanity_convert_to_jpg($type,$params)
{

	$img = null;
	
	if ($type == 'bmp') {
		include_once('libs/imagecreatefrombmp.php');
		$img = imagecreatefrombmp($params['file']);
	}
	elseif ($type == 'png') {
		
		if(!function_exists('imagecreatefrompng')) {
			return wp_handle_upload_error( $params['file'],'imsanity_convert_to_jpg requires gd library enabled');
		}

		$img = imagecreatefrompng($params['file']);
		// convert png transparency to white
		$bg = imagecreatetruecolor(imagesx($img), imagesy($img));
		imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
		imagealphablending($bg, TRUE);
		imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));

	}
	else {
		return wp_handle_upload_error( $params['file'],'Unknown image type specified in imsanity_convert_to_jpg');
	}

	// we need to change the extension from the original to .jpg so we have to ensure it will be a unique filename
	$uploads = wp_upload_dir();
	$oldFileName = basename($params['file']);
	$newFileName = basename(str_ireplace(".".$type, ".jpg", $oldFileName));
	$newFileName = wp_unique_filename( $uploads['path'], $newFileName );
	
	$quality = imsanity_get_option('imsanity_quality',IMSANITY_DEFAULT_QUALITY);
	
	if (imagejpeg($img,$uploads['path'] . '/' . $newFileName, $quality))
	{
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
				__("Oh Snap! Imsanity was Unable to process the $type file.  "
						."If you continue to see this error you may need to disable the $type-To-JPG "
						."feature in Imsanity settings.", 'imsanity' ) );
	}
	
	return $params;
}


/* add filters to hook into uploads */
add_filter( 'wp_handle_upload', 'imsanity_handle_upload' );

/* add filters/actions to customize upload page */
add_action('post-upload-ui', 'imsanity_upload_ui');




// TODO: if necessary to update the post data in the future...
// add_filter( 'wp_update_attachment_metadata', 'imsanity_handle_update_attachment_metadata' );

?>