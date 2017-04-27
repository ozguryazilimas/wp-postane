<?php 
/** 
 * ################################################################################
 * UTILITIES
 * ################################################################################
 */

/**
 * Util function returns an array value, if not defined then returns default instead.
 *
 * @param Array $array
 * @param string $key
 * @param variant $default
 */
function imsanity_val( $arr, $key, $default='' ) {
	return isset( $arr[$key] ) ? $arr[ $key ] : $default;
}

/**
 * Get mimetype based on file extension instead of file contents when speed outweighs accuracy.
 *
 * @param string $path The name of the file.
 * @return string|bool The mime type based on the extension or false.
 */
function imsanity_quick_mimetype( $path ) {
	$pathextension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	switch ( $pathextension ) {
		case 'jpg':
		case 'jpeg':
		case 'jpe':
			return 'image/jpeg';
		case 'png':
			return 'image/png';
		case 'gif':
			return 'image/gif';
		case 'pdf':
			return 'application/pdf';
		default:
			return false;
	}
}

/**
 * Gets the orientation/rotation of a JPG image using the EXIF data.
 *
 * @param string $file Name of the file.
 * @param string $type Mime type of the file.
 * @return int|bool The orientation value or false.
 */
function imsanity_get_orientation( $file, $type ) {
	if ( function_exists( 'exif_read_data' ) && 'image/jpeg' === $type ) {
		$exif = @exif_read_data( $file );
		if ( is_array( $exif ) && array_key_exists( 'Orientation', $exif ) ) {
			return $exif['Orientation'];
		}
	}
	return false;
}

/**
 * output a fatal error and optionally die
 * 
 * @param string $message
 * @param string $title
 * @param bool $die
 */
function imsanity_fatal( $message, $title = "", $die = false ) {
	echo ( "<div style='margin:5px 0px 5px 0px;padding:10px;border: solid 1px red; background-color: #ff6666; color: black;'>"
		. ( $title ? "<h4 style='font-weight: bold; margin: 3px 0px 8px 0px;'>" . $title . "</h4>" : "" )
		. $message
		. "</div>" );
		
	if ( $die ) die();
}

/**
 * Replacement for deprecated image_resize function
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed WP_Error on failure. String with new destination path.
 */
function imsanity_image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 82 ) {
	if ( function_exists( 'wp_get_image_editor' ) ) {
		// WP 3.5 and up use the image editor
				
		$editor = wp_get_image_editor( $file );
		if ( is_wp_error( $editor ) )
			return $editor;
		$editor->set_quality( $jpeg_quality );
		
		$ftype = imsanity_quick_mimetype( $file );

		$orientation = imsanity_get_orientation( $file, $ftype );
		// try to correct for auto-rotation if the info is available
//		if ( function_exists( 'exif_read_data' ) && ( $ftype == 'image/jpeg' ) ) {
//			$exif = @exif_read_data( $file );
//			$orientation = is_array( $exif ) && array_key_exists( 'Orientation', $exif ) ? $exif['Orientation'] : 0;
			switch ( $orientation ) {
				case 3:
					$editor->rotate( 180 );
					break;
				case 6:
					$editor->rotate( -90 );
					break;
				case 8:
					$editor->rotate( 90 );
					break;
			}
//		}
		
		$resized = $editor->resize( $max_w, $max_h, $crop );
		if ( is_wp_error( $resized ) )
			return $resized;

		$dest_file = $editor->generate_filename( $suffix, $dest_path );
		
		// FIX: make sure that the destination file does not exist.  this fixes
		// an issue during bulk resize where one of the optimized media filenames may get 
		// used as the temporary file, which causes it to be deleted.
		while ( file_exists( $dest_file ) ) {
			$dest_file = $editor->generate_filename( 'TMP', $dest_path );
		}
		
		$saved = $editor->save( $dest_file );
	
		if ( is_wp_error( $saved ) )
			return $saved;
	
		return $dest_file;
	}
	return false;
}

?>
