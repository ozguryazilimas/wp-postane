<?php
/*
Plugin Name: Son Ahkamlar widget
Description: Adds a sidebar widget to display posts from a specified category
Author: Mike Jolley, jolley_small@tesco.net
Version: 1.1
Author URI: http://blue-anvil.com
*/

function widget_myRecentPosts_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

		function widget_myRecentPosts($args) {
		
			// "$args is an array of strings that help widgets to conform to
			// the active theme: before_widget, before_title, after_widget,
			// and after_title are the array keys." - These are set up by the theme
			extract($args);

			// These are our own options
			$options = get_option('widget_myRecentPosts');
			$title = $options['title'];  // Title in sidebar for widget
			$show = $options['show'];  // # of Posts we are showing
            if ($show<1) $show = 1;
		

		// Output
		echo $before_widget . $before_title . $title . $after_title;

		// GET POSTS

			global $wpdb;
			$sql = 'select DISTINCT C.comment_post_ID, (select post_title from '.$wpdb->posts.' P where P.ID=C.comment_post_ID) p_title, (select count(*) from '.$wpdb->comments.' C1 where C1.comment_post_ID=C.comment_post_ID) comment_count from '.$wpdb->comments.' C ORDER BY C.comment_date_gmt DESC LIMIT 0, '.$show.';';


			$posts = $wpdb->get_results($sql);
			
			// start list
			echo '<ul>';
				// were there any posts found?
				if (!empty($posts)) {
					// posts were found, loop through them
					 foreach ($posts as $post) {

							//output to screen
							echo '<li><a rel="bookmark" href="'.get_permalink($post->comment_post_ID).'" onClick="_gaq.push([\'_trackEvent\', \'nasil-browse-ediyorlar\', \'son-ahkamlar\']);">'.$post->p_title.'</a><sup class="counter">('.$post->comment_count.')</sup></li>';
					 }
				} else echo "<li>Maalesef..</li>";
		// end list
		echo '</ul>';
		
		// echo widget closing tag
		echo $after_widget;
	}


	// Settings form
	function widget_myRecentPosts_control() {

		// Get options
		$options = get_option('widget_myRecentPosts');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('title'=>'Recent Posts', 'show'=>'5');
		
		// form posted?
		if ( $_POST['myRecentPosts-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			$options['title'] = strip_tags(stripslashes($_POST['myRecentPosts-title']));
			$options['show'] = strip_tags(stripslashes($_POST['myRecentPosts-show']));
			update_option('widget_myRecentPosts', $options);
		}

		// Get options for form fields to show
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$show = htmlspecialchars($options['show'], ENT_QUOTES);
		
		// The form fields
		echo '<p style="text-align:right;">
				<label for="myRecentPosts-title">' . __('Title:') . ' 
				<input style="width: 200px;" id="myRecentPosts-title" name="myRecentPosts-title" type="text" value="'.$title.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="myRecentPosts-show">' . __('Show:') . ' 
				<input style="width: 200px;" id="myRecentPosts-show" name="myRecentPosts-show" type="text" value="'.$show.'" />
				</label></p>';
		echo '<input type="hidden" id="myRecentPosts-submit" name="myRecentPosts-submit" value="1" />';
	}
	
	// Register widget for use
	register_sidebar_widget(array('My Recent Posts', 'widgets'), 'widget_myRecentPosts');

	// Register settings for use, 300x100 pixel form
	register_widget_control(array('My Recent Posts', 'widgets'), 'widget_myRecentPosts_control', 300, 200);
}

// Run code and init
add_action('widgets_init', 'widget_myRecentPosts_init');

?>