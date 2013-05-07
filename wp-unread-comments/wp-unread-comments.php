<?php
/*
Plugin Name: WP Unread Comments
Plugin URI: http://www.mclemon.org/?page_id=544
Description: This plugin displays unread comments in a sidebar widget and can highlight unread comments in comment lists.  This is useful when you have an active comments section on a blog and want users to be able to find the newest comments easily.  This is analagous to View Unread Posts on a phpbb forum.  If logged in, the user will be able to click on the title of the post in the widget to find the oldest of the unread comments in the list.
Version: 1.0.0
Author: Mary Ann Nicholson
Author URI: http://mclemon.org/
License: GPL2
*/

/*  Copyright 2011 Mary Ann Nicholson

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

require_once(dirname(__FILE__) . '/includes/class-wp-uc-widget.php');
$wp_uc = new WP_UC_Widget();

//widget
add_action('widgets_init', 'wp_unread_comments_init'); 

// timestamp functions - comment out these 2 calls if you place the actions in your template
add_action('get_header', 'wuc_get_time');
add_action('get_footer', 'wuc_set_time');

//register the functions
add_action('wuc_get_time', 'wuc_get_time');
add_action('wuc_set_time', 'wuc_set_time');

// Add the action to every comment
add_filter('comment_class', 'wuc_unread_class', 10);

//add css
add_action( 'init', 'wp_unread_comments_add_css' );

function wp_unread_comments_add_css() {
	/* Enqueue the WordPress hook Sniffer CSS file */
	wp_enqueue_style( 'wp-unread-comments', get_option('siteurl').'/wp-content/plugins/wp-unread-comments/css/wp-unread-comments.css' );	
}
// Updates the cookie when an user reads a post
function wuc_get_time() {
	global $wp_query, $wpdb,$user_ID;
	$post_id = $wp_query->post->ID;
	$post_key = 'wuc_post_id'.$post_id;
	$_SESSION['wuc_post_key']=$post_key;		
	$_SESSION[$post_key]=current_time('mysql', 1); 

}
function wuc_set_time(){
	global $wpdb, $user_ID;
	$post_key=$_SESSION['wuc_post_key'];
	update_user_meta( $user_ID, $post_key, $_SESSION[$post_key] );
}

// Adds the unread class to every matched comment
function wuc_unread_class($classes = array()) {
	global $comment, $wpdb, $user_ID, $wp_uc;
	$highlight = get_option( 'wp-unread-comment-highlight' );
	if (!$highlight) return $classes;
	$post_id = $comment->comment_post_ID;

	$post_key = 'wuc_post_id'.$post_id;
	$ts_a = strtotime(get_user_meta( $user_ID, $post_key, true ));
	
	$comment_time = strtotime($comment->comment_date_gmt);

	if ($comment_time > $ts_a)
	{
		$classes [] = 'wp-unread-comment';
	}


	return $classes;
}


 
?>
