<?php
/*
Plugin Name: Comment Image Embedder
Plugin URI: http://trevorfitzgerald.com/wordpress-comment-images/
Description: Allows your commenters to post images from external sites in comments with a simple interface.
Version: 1.5
Author: Trevor Fitzgerald
Author URI: http://trevorfitzgerald.com/
*/

function embed_images($content) {
	$content = preg_replace('/\[img=?\]*(.*?)(\[\/img)?\]/e', '"<img src=\"$1\" alt=\"" . basename("$1") . "\" />"', $content);
	return $content;
}

function embed_image_instructions($id) {
	echo '<p>You can add images to your comment by <a id="addCommentImage" href="#">clicking here</a>.</p>';
	return $id;
}

add_filter('comment_text', 'embed_images');
add_action('comment_form', 'embed_image_instructions');
wp_enqueue_script('comment-images', plugins_url('js/comment-images.js', __FILE__), array('jquery'), '1.4');

?>
