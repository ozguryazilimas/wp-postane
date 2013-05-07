<?php
/**
 * Copyright (c) 2012 Mary Ann Nicholson <mclemon.org>
 * @license http://www.gnu.org/licenses/gpl.html GNU GENERAL PUBLIC LICENSE VERSION 3.0 OR LATER
 */

/**
 * WP Unread Comments Widget Class
 */
class WP_UC_Widget extends WP_Widget
{
	function WP_UC_Widget()
	{
		$widget_ops = array('classname' => 'wp-uc-widget', 'description' => __( 'Show a list of unread comments.', 'wp-uc') );
		$control_ops = array('width' => 350);
		$this->WP_Widget('wp_unread_comments', __('WP Unread Comments', 'wp-uc'), $widget_ops, $control_ops);
	}

	function widget($args, $instance)
	{
		global $comments, $comment, $user_ID;
		
		$cache = wp_cache_get('widget_wp_unread_comments', 'widget');
		
		if ( ! is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		extract($args, EXTR_SKIP);
		
		$output = '';
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Unread Comments' ) : $instance['title'], $instance, $this->id_base );
		
		$order='asc';
		if($user_ID == ''){
			$title = apply_filters( 'widget_title', empty( $instance['title_recent'] ) ? __( 'Recent Comments' ) : $instance['title_recent'], $instance, $this->id_base );			
			$number = $instance['number_recent'];
			$order='desc';
			$show_recent = $instance['show_recent'];			
			$show_text = $instance['show_text'];
			$custom_text = $instance['custom_text'];
			
		}

		$comments = get_comments( array( 'number' => $number, 'order' => $order, 'status' => 'approve', 'post_status' => 'publish' ) );
		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul id="recentcomments">';
		$postarray=array();
		$commentarray=array();
		$number = $instance['number_unread'];
		
		if($show_text) $output.= '<li class="'.$rcclass.'">' . $custom_text . '</li>';
		if ( $comments ) {
			foreach ( (array) $comments as $comment) {
				if($user_ID=='')
				{
					if($show_recent)
					{
						$output.='<li class="'.$rcclass.'">' . /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', 'widgets'), get_comment_author_link(), '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';											
					}
				}
				else {
					$rcclass='recentcomments';
					$post_id = $comment->comment_post_ID;
					$post_key = 'wuc_post_id'.$post_id;
					$ts_a = strtotime(get_user_meta( $user_ID, $post_key, true ));
					$comment_time = strtotime($comment->comment_date_gmt);
					if(!in_array($post_id, $postarray))
					{
						if ($comment_time > $ts_a)
						{
							array_push($postarray, $post_id);
							array_push($commentarray, '<li class="'.$rcclass.'">' . 
							sprintf(_x('%1$s', 'widgets'),  '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>');											
						}
					}
					if($number>0 && count($commentarray) >= $number) break;
				}
			}
		}
		if($user_ID !='')
		{
			for ($i = count($commentarray) - 1; $i >=0 ; $i--) { 
				$output .= $commentarray[$i];
			}
			if(count($commentarray)==0) $output.='<li class="recentcomments">You have no unread comments</li>';
		
		}
		$output .= '</ul>';
		$output .= $after_widget;
		echo $output;

	}
	function update($new_instance, $old_instance) {       
	        $instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['number_unread'] = $new_instance['number_unread'];
			$instance['highlight'] = $new_instance['highlight'];
			$instance['title_recent'] = strip_tags($new_instance['title_recent']);
			$instance['number_recent'] = $new_instance['number_recent'];
			$instance['show_recent'] = $new_instance['show_recent'];			
			$instance['show_text'] = $new_instance['show_text'];
			$instance['custom_text'] = strip_tags($new_instance['custom_text']);
			update_option( 'wp-unread-comment-highlight', $instance['highlight'], '', 'yes' );

			return $instance;
	}
    function form($instance) { 
		global $wp_uc;

		$instance_name = strip_tags($instance['instance']);
		
		$title = (isset($instance['title'])) ? strip_tags($instance['title']) : '';
		$number_unread = empty($instance['number_unread'])?5:(int) $instance['number_unread'];
		$highlight = (isset($instance['highlight'])) ? true : false;
		$title_recent = (isset($instance['title_recent'])) ? strip_tags($instance['title_recent']) : 'Recent Comments' ;
		$number_recent = empty($instance['number_recent'])?5:(int) $instance['number_recent'];
		$show_recent = (isset($instance['show_recent'])) ? true : false;		
		$show_text = (isset($instance['show_text'])) ? true : false;
		$custom_text = (isset($instance['custom_text'])) ? strip_tags($instance['custom_text']) : 'You must be logged in to view unread comments';
		
		
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wp-uc'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id('number_unread'); ?>">Number of unread comments to show (0 for all):</label>
		<input id="<?php echo $this->get_field_id('number_unread'); ?>" name="<?php echo $this->get_field_name('number_unread'); ?>" type="text" value="<?php echo $number_unread; ?>" size="3" />
	    <p>
			<input class="checkbox" type="checkbox" <?php checked($highlight, true); ?> id="<?php echo $this->get_field_id('highlight'); ?>" name="<?php echo $this->get_field_name('highlight'); ?>" /> <label for="<?php echo $this->get_field_id('highlight'); ?>"><?php _e('Highlight unread comments', 'wp-rc'); ?></label>
		</p>
		<p><b>Options for users who aren't logged in:</b></p>
		
		<p><label for="<?php echo $this->get_field_id('title_recent'); ?>"><?php _e('Title for users who are not logged in:', 'wp-uc'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title_recent'); ?>" name="<?php echo $this->get_field_name('title_recent'); ?>" type="text" value="<?php echo esc_attr($title_recent); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($show_recent, true); ?> id="<?php echo $this->get_field_id('show_recent'); ?>" name="<?php echo $this->get_field_name('show_recent'); ?>" /> <label for="<?php echo $this->get_field_id('show_recent'); ?>"><?php _e('Show recent comments if users are not logged in', 'wp-rc'); ?></label>
		</p>
		<p><label for="<?php echo $this->get_field_id('number_recent'); ?>">Number of recent comments to show (0 for all):</label>
		<input id="<?php echo $this->get_field_id('number_recent'); ?>" name="<?php echo $this->get_field_name('number_recent'); ?>" type="text" value="<?php echo $number_recent; ?>" size="3" />
	    <p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked($show_text, true); ?> id="<?php echo $this->get_field_id('show_text'); ?>" name="<?php echo $this->get_field_name('show_text'); ?>" /> <label for="<?php echo $this->get_field_id('show_recent'); ?>"><?php _e('Show custom text if users are not logged in', 'wp-rc'); ?></label>
		</p>
		
		<p><label for="<?php echo $this->get_field_id('custom_text'); ?>"><?php _e('Custom Text:', 'wp-uc'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('custom_text'); ?>" name="<?php echo $this->get_field_name('custom_text'); ?>" type="text" value="<?php echo esc_attr($custom_text); ?>" /></p>
		
	<?php		
	}
}

function wp_unread_comments_init()
{

	register_widget('WP_UC_Widget');
}


?>
