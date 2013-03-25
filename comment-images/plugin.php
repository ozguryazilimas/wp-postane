<?php
/*
Plugin Name: Comment Images
Donate URI: http://tommcfarlin.com/donate/
Plugin URI: http://tommcfarlin.com/comment-images/
Description: Allow your readers easily to attach an image to their comment.
Version: 1.8.2
Author: Tom McFarlin
Author URI: http://tommcfarlin.com/
Author Email: tom@tommcfarlin.com
License:

  Copyright 2012 - 2013 Tom McFarlin (tom@tommcfarlin.com)

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

/**
 * Backlog
 *
 *  + Features
 *		- P2 Compatibility
 *		- JetPack compatibility
 *		- Is there a way to re-size the images before uploading?
 *		- User's shouldn't have to enter text to leave a comment.
 *
 *	+ Bugs
 * 		- Warning: file_get_contents() [function.file-get-contents]: Filename cannot be empty in /home/airtaxi/public_html/wp-content/plugins/comment-images/plugin.php on line 199
 *		- I actually tested the plugin on my original enquiry and it appears that the images actually get *removed* from the comments when the plugin is disabled.
 */

/**
 * Include dependencies necessary for adding Comment Images to the Media Uplower
 *
 * See also:	http://codex.wordpress.org/Function_Reference/media_sideload_image
 * @since		1.8
 */
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

class Comment_Image {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	function __construct() {
	
		// Load plugin textdomain
		add_action( 'init', array( $this, 'plugin_textdomain' ) );
	
		// Determine if the hosting environment can save files.
		if( $this->can_save_files() ) {
	
			// We need to update all of the comments thus far
			if( false == get_option( 'update_comment_images' ) || null == get_option( 'update_comment_images' ) ) {
				$this->update_old_comments();
			} // end if
	
			// Add comment related stylesheets and Java Script
			add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
			
			// Add the Upload input to the comment form
			add_action( 'comment_form' , array( $this, 'add_image_upload_form' ) );
			add_filter( 'wp_insert_comment', array( $this, 'save_comment_image' ) );
			add_filter( 'comments_array', array( $this, 'display_comment_image' ) );
			
			// Add a note to recent comments that they have Comment Images
			add_filter( 'comment_row_actions', array( $this, 'recent_comment_has_image' ), 20, 2 );
			
			// Add a column to the Post editor indicating if there are Comment Images
			add_filter( 'manage_posts_columns', array( $this, 'post_has_comment_images' ) );
			add_filter( 'manage_posts_custom_column', array( $this, 'post_comment_images' ), 20, 2 );
			
			// Add a column to the comment images if there is an image for the given comment
			add_filter( 'manage_edit-comments_columns', array( $this, 'comment_has_image' ) );
			add_filter( 'manage_comments_custom_column', array( $this, 'comment_image' ), 20, 2 );
			
			// Setup the Project Completion metabox
			add_action( 'add_meta_boxes', array( $this, 'add_comment_image_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_comment_image_display' ) );
			
		// If not, display a notice.	
		} else {
		
			add_action( 'admin_notices', array( $this, 'save_error_notice' ) );
			
		} // end if/else

	} // end constructor
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	 
	 /**
	  * Adds a column to the 'All Posts' page indicating whether or not there are 
	  * Comment Images available for this post.
	  *
	  * @param	array	$cols	The columns displayed on the page.
	  * @param	array	$cols	The updated array of columns.
	  * @since	1.8
	  */
	 public function post_has_comment_images( $cols ) {
	 
		 $cols['comment-images'] = __( 'Comment Images', 'comment-images' );
		 
		 return $cols;
		 
	 } // end post_has_comment_images
	 
	 /**
	  * Provides a link to the specified post's comments page if the post has comments that contain
	  * images.
	  *
	  * @param	string	$column_name	The name of the column being rendered.
	  * @param	int		$int			The ID of the post being rendered.
	  * @since	1.8
	  */
	 public function post_comment_images( $column_name, $post_id ) {

		 if( 'comment-images' == strtolower( $column_name ) ) {
		 
		 	// Get the comments for the current post.
		 	$args = array( 
		 		'post_id' => $post_id
		 	);
		 	$comments = get_comments( $args );
		 	
		 	// Look at each of the comments to determine if there's at least one comment image
		 	$has_comment_image = false;
		 	foreach( $comments as $comment ) {
			 	
			 	// If the comment meta indicates there's a comment image and we've not yet indicated that it does...
			 	if( 0 != get_comment_meta( $comment->comment_ID, 'comment_image', true ) && ! $has_comment_image ) {
			 		
			 		// ..Make a note in the column and link them to the media for that post
					$html = '<a href="edit-comments.php?p=' . $comment->comment_post_ID . '">';
						$html .= __( 'View Post Comment Images', 'comment-images' );
					$html .= '</a>';
			 		
			 		echo $html;
			 		
			 		// Mark that we've discovered at least one comment image
			 		$has_comment_image = true;
			 		
			 	} // end if
			 	
		 	} // end foreach
		 
		 } // end if
		 
	 } // end post_comment_images
	 
	 /**
	  * Adds a column to the 'Comments' page indicating whether or not there are 
	  * Comment Images available.
	  *
	  * @param	array	$columns	The columns displayed on the page.
	  * @param	array	$columns	The updated array of columns.
	  */
	 public function comment_has_image( $columns ) {
		 
		 $columns['comment-image'] = __( 'Comment Image', 'comment-images' );
		 
		 return $columns;
		 
	 } // end comment_has_image
	 
	 /**
	  * Renders the actual image for the comment.
	  *
	  * @param	string	The name of the column being rendered.
	  * @param	int		The ID of the comment being rendered.
	  * @since	1.8
	  */
	 public function comment_image( $column_name, $comment_id ) {

		 if( 'comment-image' == strtolower( $column_name ) ) {

			 if( 0 != ( $comment_image_data = get_comment_meta( $comment_id, 'comment_image', true ) ) ) {
	
				 $image_url = $comment_image_data['url'];
				 $html = '<img src="' . $image_url . '" width="150" />';
				 
				 echo $html;
			 
	 		 } // end if
 		 
 		 } // end if/else
		 
	 } // end comment_image
	 
	 /**
	  * Determines whether or not the current comment has comment images. If so, adds a new link
	  * to the 'Recent Comments' dashboard.
	  *
	  * @param	array	$options	The array of options for each recent comment
	  * @param	object	$comment	The current recent comment
	  * @return	array	$options	The updated list of options
	  * @since	1.8
	  */
	 public function recent_comment_has_image( $options, $comment ) {
	 
		 if( 0 != ( $comment_image = get_comment_meta( $comment->comment_ID, 'comment_image', true ) ) ) {
			 
			 $html = '<a href="edit-comments.php?p=' . $comment->comment_post_ID . '">';
			 	$html .= __( 'Comment Images', 'comment-images' );
			 $html .= '</a>';
			 
			 $options['comment-images'] = $html;

		 } // end if
		 
		 return $options;
		 
	 } // end recent_comment_has_image
	 
	 /**
	  * Loads the plugin text domain for translation
	  */
	 function plugin_textdomain() {
		 load_plugin_textdomain( 'comment-images', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	 } // end plugin_textdomain
	 
	 /**
	  * In previous versions of the plugin, the image were written out after the comments. Now,
	  * they are actually part of the comment content so we need to update all old options.
	  *
	  * Note that this option is not removed on deactivation because it will run *again* if the
	  * user ever re-activates it this duplicating the image.
	  */
	 private function update_old_comments() {
		 
		// Update the option that this has not run
		update_option( 'update_comment_images', false );
		
		// Iterate through each of the comments...
 		foreach( get_comments() as $comment ) {
 		
			// If the comment image meta value exists...
			if( true == get_comment_meta( $comment->comment_ID, 'comment_image' ) ) {
			
				// Get the associated comment image
				$comment_image = get_comment_meta( $comment->comment_ID, 'comment_image', true );
				
				// Append the image to the comment content
				$comment->comment_content .= '<p class="comment-image">';
					$comment->comment_content .= '<img src="' . $comment_image['url'] . '" alt="" />';
				$comment->comment_content .= '</p><!-- /.comment-image -->';
				
				// Now we need to actually update the comment
				wp_update_comment( (array)$comment );
				
			} // end if
 		
		} // end if
		
		// Update the fact that this has run so we don't run it again
		update_option( 'update_comment_images', true );
		 
	 } // end update_old_comments
	 
	 /**
	  * Display a WordPress error to the administrator if the hosting environment does not support 'file_get_contents.'
	  */
	 function save_error_notice() {
		 
		 $html = '<div id="comment-image-notice" class="error">';
		 	$html .= '<p>';
		 		$html .= __( '<strong>Comment Images Notice:</strong> Unfortunately, your host does not allow uploads from the comment form. This plugin will not work for your host.', 'comment-images' );
		 	$html .= '</p>';
		 $html .= '</div><!-- /#comment-image-notice -->';
		 
		 echo $html;
		 
	 } // end save_error_notice
	 
	 /**
	  * Adds the public stylesheet to the single post page.
	  */
	 function add_styles() {
	
		if( is_single() || is_page() ) {
			
			wp_register_style( 'comment-images', plugins_url( '/comment-images/css/plugin.css' ) );
			wp_enqueue_style( 'comment-images' );
			
		} // end if
		
	} // end add_scripts
	 
	/**
	 * Adds the public JavaScript to the single post page.
	 */ 
	function add_scripts() {
	
		if( is_single() || is_page() ) {
			
			wp_register_script( 'comment-images', plugins_url( '/comment-images/js/plugin.min.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'comment-images' );
			
		} // end if
		
	} // end add_scripts

	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @param	$post_id	The ID of the post on which the comment is being added.
	 */
 	function add_image_upload_form( $post_id ) {

	 	// Create the label and the input field for uploading an image
	 	if( 'disable' != get_post_meta( $post_id, 'comment_images_toggle', true ) ) {
	 	
		 	$html = '<div id="comment-image-wrapper">';
			 	$html .= '<p id="comment-image-error">';
			 		$html .= __( '<strong>Heads up!</strong> You are attempting to upload an invalid image. If saved, this image will not display with your comment.', 'comment-image' );
			 	$html .= '</p>';
				 $html .= "<label for='comment_image_$post_id'>";
				 	$html .= __( 'Select an image for your comment (GIF, PNG, JPG, JPEG):', 'comment-images' );
				 $html .= "</label>";
				 $html .= "<input type='file' name='comment_image_$post_id' id='comment_image' />";
			 $html .= '</div><!-- #comment-image-wrapper -->';
	
			 echo $html;
		 
		 } // end if
		 
	} // end add_image_upload_form
	
	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @param	$comment_id	The ID of the comment to which we're adding the image.
	 */
	function save_comment_image( $comment_id ) {
	
		// The ID of the post on which this comment is being made
		$post_id = $_POST['comment_post_ID'];
		
		// The key ID of the comment image
		$comment_image_id = "comment_image_$post_id";
		
		// If the nonce is valid and the user uploaded an image, let's upload it to the server
		if( isset( $_FILES[ $comment_image_id ] ) && ! empty( $_FILES[ $comment_image_id ] ) ) {
			
			// Store the parts of the file name into an array
			$file_name_parts = explode( '.', $_FILES[ $comment_image_id ]['name'] );
			
			// If the file is valid, upload the image, and store the path in the comment meta
			if( $this->is_valid_file_type( $file_name_parts[ count( $file_name_parts ) - 1 ] ) ) {;
			
				// Upload the comment image to the uploads directory
				$comment_image_file = wp_upload_bits( $_FILES[ $comment_image_id ]['name'], null, file_get_contents( $_FILES[ $comment_image_id ]['tmp_name'] ) );
				
				// Now, we need to actually create a post so that this shows up in the media uploader
				$img_url = media_sideload_image( $comment_image_file['url'], $post_id );
				
				// And strip out the name of the image file so we can save this to the meta data
				// Regex is usually bad for this, but WordPress is predictable in the format
				preg_match_all( "#[^<img src='](.*)[^'alt='' />]#", $img_url, $matches );
				$comment_image_file['url'] = $matches[0][0];
				
				// Set post meta about this image. Need the comment ID and need the path.
				if( false == $comment_image_file['error'] ) {
					
					// Since we've already added the key for this, we'll just update it with the file.
					add_comment_meta( $comment_id, 'comment_image', $comment_image_file );
					
				} // end if/else
			
			} // end if
 		
		} // end if
		
	} // end save_comment_image
	
	/**
	 * Appends the image below the content of the comment.
	 *
	 * @param	$comment	The content of the comment.
	 */
	function display_comment_image( $comments ) {

		// Make sure that there are comments
		if( count( $comments ) > 0 ) {
		
			// Loop through each comment...
			foreach( $comments as $comment ) {
			
				// ...and if the comment has a comment image...
				if( true == get_comment_meta( $comment->comment_ID, 'comment_image' ) ) {
			
					// ...get the comment image meta
					$comment_image = get_comment_meta( $comment->comment_ID, 'comment_image', true );
					
					// ...and render it in a paragraph element appended to the comment
					$comment->comment_content .= '<p class="comment-image">';
						$comment->comment_content .= '<img src="' . $comment_image['url'] . '" alt="" />';
					$comment->comment_content .= '</p><!-- /.comment-image -->';	
				
				} // end if
				
			} // end foreach
			
		} // end if
		
		return $comments;

	} // end display_comment_image
	
	/*--------------------------------------------*
	 * Meta Box Functions
	 *---------------------------------------------*/
	
	 /**
	  * Registers the meta box for displaying the 'Comment Images' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.8
	  */
	 public function add_comment_image_meta_box() {
		 
		 add_meta_box(
		 	'disable_comment_images',
		 	__( 'Comment Images', 'comment-images' ),
		 	array( $this, 'comment_images_display' ),
		 	'post',
		 	'side',
		 	'low'
		 );
		 
	 } // end add_project_completion_meta_box
	 
	 /**
	  * Displays the option for disabling the Comment Images upload field.
	  *
	  * @version	1.0
	  * @since 		1.8
	  */
	 public function comment_images_display( $post ) {
		 
		 wp_nonce_field( plugin_basename( __FILE__ ), 'comment_images_display_nonce' );

		 $html = '<select name="comment_images_toggle" id="comment_images_toggle" class="comment_images_toggle_select">';
		 	$html .= '<option value="enable" ' . selected( 'enable', get_post_meta( $post->ID, 'comment_images_toggle', true ), false ) . '>' . __( 'Enable comment images for this post.', 'comment-images' ) . '</option>';
		 	$html .= '<option value="disable" ' . selected( 'disable', get_post_meta( $post->ID, 'comment_images_toggle', true ), false ) . '>' . __( 'Disable comment images for this post.', 'comment-images' ) . '</option>';
		 $html .= '</select>';
  
		 echo $html;
		 
	 } // end comment_images_display
	 
	 /**
	  * Saves the meta data for displaying the 'Comment Images' options in the post editor.
	  *
	  * @version	1.0
	  * @since 		1.8
	  */
	 public function save_comment_image_display( $post_id ) {
		 
		 // If the user has permission to save the meta data...
		 if( $this->user_can_save( $post_id, 'comment_images_display_nonce' ) ) { 
		 
		 	// Delete any existing meta data for the owner
			if( get_post_meta( $post_id, 'comment_images_toggle' ) ) {
				delete_post_meta( $post_id, 'comment_images_toggle' );
			} // end if
			update_post_meta( $post_id, 'comment_images_toggle', $_POST[ 'comment_images_toggle' ] );		
			 
		 } // end if
		 
	 } // end save_comment_image_display
	
	/*--------------------------------------------*
	 * Utility Functions
	 *---------------------------------------------*/
	
	/**
	 * Determines if the specified type if a valid file type to be uploaded.
	 *
	 * @param	$type	The file type attempting to be uploaded.
	 * @return			Whether or not the specified file type is able to be uploaded.
	 */ 
	private function is_valid_file_type( $type ) { 
	
		$type = strtolower( trim ( $type ) );
		return $type == __( 'png', 'comment-images' ) || $type == __( 'gif', 'comment-images' ) || $type == __( 'jpg', 'comment-images' ) || $type == __( 'jpeg', 'comment-images' );
		
	} // end is_valid_file_type
	
	/**
	 * Determines if the hosting environment allows the users to upload files.
	 *
	 * @return			Whether or not the hosting environment supports the ability to upload files.
	 */ 
	private function can_save_files() {
		return function_exists( 'file_get_contents' );
	} // end can_save_files
	
	 /**
	  * Determines whether or not the current user has the ability to save meta data associated with this post.
	  *
	  * @param		int		$post_id	The ID of the post being save
	  * @param		bool				Whether or not the user has the ability to save this post.
	  * @version	1.0
	  * @since		1.8
	  */
	 private function user_can_save( $post_id, $nonce ) {
		
	    $is_autosave = wp_is_post_autosave( $post_id );
	    $is_revision = wp_is_post_revision( $post_id );
	    $is_valid_nonce = ( isset( $_POST[ $nonce ] ) && wp_verify_nonce( $_POST[ $nonce ], plugin_basename( __FILE__ ) ) ) ? true : false;
	    
	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision) && $is_valid_nonce;

	 } // end user_can_save
  
} // end class

new Comment_Image();
?>