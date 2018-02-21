<?php
/**
* ################################################################################
* IMSANITY AJAX FUNCTIONS
* ################################################################################
*/

add_action('wp_ajax_imsanity_get_images', 'imsanity_get_images');
add_action('wp_ajax_imsanity_resize_image', 'imsanity_resize_image');

/**
 * Verifies that the current user has administrator permission and, if not,
 * renders a json warning and dies
 */
function imsanity_verify_permission()
{
	if ( ! current_user_can( 'administrator' ) ) { // this isn't a real capability, but super admins can do anything, so it works
		die( json_encode( array( 'success' => false, 'message' => esc_html__( 'Administrator permission is required', 'imsanity' ) ) ) );
	}
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'imsanity-bulk' ) ) {
		die( json_encode( array( 'success' => false, 'message' => esc_html__( 'Access token has expired, please reload the page.', 'imsanity' ) ) ) );
	}
}


/**
 * Searches for up to 250 images that are candidates for resize and renders them
 * to the browser as a json array, then dies
 */
function imsanity_get_images()
{
	imsanity_verify_permission();

	global $wpdb;
	$offset = 0;
	$limit = apply_filters( 'imsanity_attachment_query_limit', 3000 );
	$results = array();
	$maxW = imsanity_get_option( 'imsanity_max_width', IMSANITY_DEFAULT_MAX_WIDTH );
	$maxH = imsanity_get_option( 'imsanity_max_height', IMSANITY_DEFAULT_MAX_HEIGHT );
	$count = 0;

	while( $images = $wpdb->get_results( "SELECT metas.meta_value as file_meta,metas.post_id as ID FROM $wpdb->postmeta metas INNER JOIN $wpdb->posts posts ON posts.ID = metas.post_id WHERE posts.post_type LIKE 'attachment' AND posts.post_mime_type LIKE 'image%%' AND posts.post_mime_type NOT LIKE 'image/bmp' AND metas.meta_key = '_wp_attachment_metadata' LIMIT $offset,$limit" ) ) {

		foreach ( $images as $image ) {
			$meta = unserialize( $image->file_meta );

			if ( $meta['width'] > $maxW || $meta['height'] > $maxH ) {
				$count++;

				$results[] = array(
					'id'=>$image->ID,
					'width'=>$meta['width'],
					'height'=>$meta['height'],
					'file'=>$meta['file']
				);
			}

			// make sure we only return a limited number of records so we don't overload the ajax features
			if ( $count >= IMSANITY_AJAX_MAX_RECORDS ) break 2;
		}
		$offset += $limit;
	} // endwhile
	die( json_encode( $results ) );
}

/**
* Resizes the image with the given id according to the configured max width and height settings
* renders a json response indicating success/failure and dies
*/
function imsanity_resize_image()
{
	imsanity_verify_permission();

	global $wpdb;

	$id = (int) $_POST['id'];

	if ( ! $id ) {
		die( json_encode( array( 'success' => false, 'message' => esc_html__( 'Missing ID Parameter', 'imsanity' ) ) ) );
	}

	$meta = wp_get_attachment_metadata( $id );

	if ( $meta && is_array( $meta ) ) {
		$uploads = wp_upload_dir();
		// TODO: we can do better here, sub in a version of the EWWW file finder
		$oldPath = $uploads['basedir'] . "/" . $meta['file'];
		if ( ! is_writable( $oldPath ) ) {
			$msg = sprintf( esc_html__( '%s is not writable', 'imsanity' ), $oldPath );
			die( json_encode( array( 'success' => false, 'message' => $msg ) ) );
		}

		$maxW = imsanity_get_option( 'imsanity_max_width', IMSANITY_DEFAULT_MAX_WIDTH );
		$maxH = imsanity_get_option( 'imsanity_max_height', IMSANITY_DEFAULT_MAX_HEIGHT );

		// method one - slow but accurate, get file size from file itself
		list( $oldW, $oldH ) = getimagesize( $oldPath );
		// method two - get file size from meta, fast but resize will fail if meta is out of sync
		if ( ! $oldW || ! $oldH ) {
			$oldW = $meta['width'];
			$oldH = $meta['height'];
		}

		if ( ( $oldW > $maxW && $maxW > 0 ) || ( $oldH > $maxH && $maxH > 0 ) ) {
			$quality = imsanity_get_option( 'imsanity_quality', IMSANITY_DEFAULT_QUALITY );

			if ( $oldW > $maxW && $maxW > 0 && $oldH > $maxH && $maxH > 0 && apply_filters( 'imsanity_crop_image', false ) ) {
				$newW = $maxW;
				$newH = $maxH;
			} else {
				list( $newW, $newH ) = wp_constrain_dimensions( $oldW, $oldH, $maxW, $maxH );
			}

			$resizeResult = imsanity_image_resize( $oldPath, $newW, $newH, apply_filters( 'imsanity_crop_image', false ), null, null, $quality);
			// $resizeResult = new WP_Error('invalid_image', __('Could not read image size'), $oldPath);  // uncomment to debug fail condition

			if ( $resizeResult && ! is_wp_error( $resizeResult ) ) {
				$newPath = $resizeResult;

				if ( $newPath != $oldPath && is_file( $newPath ) && filesize( $newPath ) <  filesize( $oldPath ) ) {
					// we saved some file space. remove original and replace with resized image
					unlink( $oldPath );
					rename( $newPath, $oldPath );
					$meta['width'] = $newW;
					$meta['height'] = $newH;

					wp_update_attachment_metadata( $id, $meta );

					$results = array( 'success'=>true, 'id'=> $id, 'message' => sprintf( esc_html__( 'OK: %s', 'imsanity' ) , $oldPath ) );
				} elseif ( $newPath != $oldPath ) {
					// theresized image is actually bigger in filesize (most likely due to jpg quality).
					// keep the old one and just get rid of the resized image
					if ( is_file( $newPath ) ) {
						unlink( $newPath );
					}
					$results = array( 'success'=>false, 'id'=> $id, 'message' => sprintf( esc_html__( 'ERROR: %s (%s)', 'imsanity' ) , $oldPath, esc_html__( 'Resized image was larger than the original', 'imsanity' ) ) );
				} else {
					$results = array( 'success'=>false, 'id'=> $id, 'message' => sprintf( esc_html__( 'ERROR: %s (%s)', 'imsanity' ) , $oldPath, esc_html__( 'Unknown error, resizing function returned the same filename', 'imsanity' ) ) );
				}

			} else if ( $resizeResult === false ) {
				$results = array(
					'success' => false,
					'id' => $id,
					'message' => sprintf( esc_html__( 'ERROR: %s (%s)', 'imsanity' ), $oldPath, 'wp_get_image_editor missing' ),
				);
			} else {
				$results = array(
					'success' => false,
					'id' => $id,
					'message' => sprintf( esc_html__( 'ERROR: %s (%s)', 'imsanity' ), $oldPath, htmlentities( $resizeResult->get_error_message() ) )
				);
			}
		} else {
			$results = array('success'=>true,'id'=> $id, 'message' => sprintf( esc_html__( 'SKIPPED: %s (Resize not required)', 'imsanity' ) , $oldPath ) . " -- $oldW x $oldH" );
			if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
				if ( empty( $meta['width'] ) || $meta['width'] > $oldW ) {
					$meta['width'] = $oldW;
				}
				if ( empty( $meta['height'] ) || $meta['height'] > $oldH ) {
					$meta['height'] = $oldH;
				}
				wp_update_attachment_metadata( $id, $meta );
			}
		}
	} else {
		$results = array( 'success' => false, 'id'=> $id, 'message' => sprintf( esc_html__( 'ERROR: Attachment with ID of %s not found', 'imsanity' ) , htmlentities( $id ) ) );
	}

	// if there is a quota we need to reset the directory size cache so it will re-calculate
	delete_transient( 'dirsize_cache' );
	
	die( json_encode( $results ) );
}
?>
