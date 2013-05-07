<?php
/**
 * Copyright (c) 2012 Mary Ann Nicholson <mclemon.org>
 * @license http://www.gnu.org/licenses/gpl.html GNU GENERAL PUBLIC LICENSE VERSION 3.0 OR LATER
 */

/**
 * Copied from Wordpress 3.4.2 and modified to add date time functionality
 */
function wp_unread_comments_get_comments( $args = '' ) {
	$query = new WP_Unread_Comments_Comment_Query;
	return $query->query( $args );
}

/**
 * Copied from Wordpress 3.4.2 and modified to add date time functionality
 *
 */
class WP_Unread_Comments_Comment_Query {

	/**
	 * Execute the query
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $query_vars
	 * @return int|array
	 */
	function query( $query_vars ) {
		global $wpdb;

		$defaults = array(
			'author_email' => '',
			'ID' => '',
			'karma' => '',
			'number' => '',
			'offset' => '',
			'orderby' => '',
			'order' => 'DESC',
			'parent' => '',
			'post_ID' => '',
			'post_id' => 0,
			'post_author' => '',
			'post_name' => '',
			'post_parent' => '',
			'post_status' => '',
			'post_type' => '',
			'status' => '',
			'type' => '',
			'user_id' => '',
			'search' => '',
			'from_time' => '',
			'exclude_read_for' => '',
			'count' => false
		);

		$this->query_vars = wp_parse_args( $query_vars, $defaults );
		do_action_ref_array( 'pre_wp_unread_comments_get_comments', array( &$this ) );
		extract( $this->query_vars, EXTR_SKIP );

		// $args can be whatever, only use the args defined in defaults to compute the key
		$key = md5( serialize( compact(array_keys($defaults)) )  );
		$last_changed = wp_cache_get('last_changed', 'comment');
		if ( !$last_changed ) {
			$last_changed = time();
			wp_cache_set('last_changed', $last_changed, 'comment');
		}
		$cache_key = "wp_unread_comments_get_comments:$key:$last_changed";

		if ( $cache = wp_cache_get( $cache_key, 'comment' ) ) {
			return $cache;
		}

		$post_id = absint($post_id);

		if ( 'hold' == $status )
			$approved = "comment_approved = '0'";
		elseif ( 'approve' == $status )
			$approved = "comment_approved = '1'";
		elseif ( 'spam' == $status )
			$approved = "comment_approved = 'spam'";
		elseif ( 'trash' == $status )
			$approved = "comment_approved = 'trash'";
		else
			$approved = "( comment_approved = '0' OR comment_approved = '1' )";

		$order = ( 'ASC' == strtoupper($order) ) ? 'ASC' : 'DESC';

		if ( ! empty( $orderby ) ) {
			$ordersby = is_array($orderby) ? $orderby : preg_split('/[,\s]/', $orderby);
			$ordersby = array_intersect(
				$ordersby,
				array(
					'comment_agent',
					'comment_approved',
					'comment_author',
					'comment_author_email',
					'comment_author_IP',
					'comment_author_url',
					'comment_content',
					'comment_date',
					'comment_date_gmt',
					'comment_ID',
					'comment_karma',
					'comment_parent',
					'comment_post_ID',
					'comment_type',
					'user_id',
				)
			);
			$orderby = empty( $ordersby ) ? 'comment_date_gmt' : implode(', ', $ordersby);
		} else {
			$orderby = 'comment_date_gmt';
		}

		$number = absint($number);
		$offset = absint($offset);

		if ( !empty($number) ) {
			if ( $offset )
				$limits = 'LIMIT ' . $offset . ',' . $number;
			else
				$limits = 'LIMIT ' . $number;
		} else {
			$limits = '';
		}

		if ( $count )
			$fields = 'COUNT(*)';
		else
			$fields = '*';

		$join = '';
		$where = $approved;

		if ( ! empty($post_id) )
			$where .= $wpdb->prepare( ' AND comment_post_ID = %d', $post_id );
		if ( '' !== $author_email )
			$where .= $wpdb->prepare( ' AND comment_author_email = %s', $author_email );
		if ( '' !== $karma )
			$where .= $wpdb->prepare( ' AND comment_karma = %d', $karma );
		if ( 'comment' == $type ) {
			$where .= " AND comment_type = ''";
		} elseif( 'pings' == $type ) {
			$where .= ' AND comment_type IN ("pingback", "trackback")';
		} elseif ( ! empty( $type ) ) {
			$where .= $wpdb->prepare( ' AND comment_type = %s', $type );
		}
		if ( '' !== $parent )
			$where .= $wpdb->prepare( ' AND comment_parent = %d', $parent );
		if ( '' !== $user_id )
			$where .= $wpdb->prepare( ' AND user_id = %d', $user_id );
		if ( '' !== $from_time )
			$where .= $wpdb->prepare( ' AND comment_date >= ( CURDATE() - INTERVAL %d DAY )', $from_time );
		if ( '' !== $exclude_read_for)
			// $where .= $wpdb->prepare( " AND comment_post_ID NOT IN (SELECT SUBSTRING(meta_key, 12) FROM $wpdb->usermeta WHERE user_id=%s AND $wpdb->usermeta.meta_key LIKE 'wuc_post_id%')", $exclude_read_for);
			$where .= $wpdb->prepare( " AND comment_date >= IFNULL(
				(SELECT meta_value FROM $wpdb->usermeta
					WHERE user_id=%s AND $wpdb->usermeta.meta_key=CONCAT('wuc_post_id', comment_post_ID)
				),0)
				", $exclude_read_for);
		if ( '' !== $search )
			$where .= $this->get_search_sql( $search, array( 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_content' ) );

		$post_fields = array_filter( compact( array( 'post_author', 'post_name', 'post_parent', 'post_status', 'post_type', ) ) );
		if ( ! empty( $post_fields ) ) {
			$join = "JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID";
			foreach( $post_fields as $field_name => $field_value )
				$where .= $wpdb->prepare( " AND {$wpdb->posts}.{$field_name} = %s", $field_value );
		}

		$pieces = array( 'fields', 'join', 'where', 'orderby', 'order', 'limits' );
		$clauses = apply_filters_ref_array( 'comments_clauses', array( compact( $pieces ), &$this ) );
		foreach ( $pieces as $piece )
			$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';

		$query = "SELECT $fields FROM $wpdb->comments $join WHERE $where ORDER BY $orderby $order $limits";

		if ( $count )
			return $wpdb->get_var( $query );

		$comments = $wpdb->get_results( $query );
		$comments = apply_filters_ref_array( 'the_comments', array( $comments, &$this ) );

		wp_cache_add( $cache_key, $comments, 'comment' );

		return $comments;
	}

	/*
	 * Used internally to generate an SQL string for searching across multiple columns
	 *
	 * @access protected
	 * @since 3.1.0
	 *
	 * @param string $string
	 * @param array $cols
	 * @return string
	 */
	function get_search_sql( $string, $cols ) {
		$string = esc_sql( like_escape( $string ) );

		$searches = array();
		foreach ( $cols as $col )
			$searches[] = "$col LIKE '%$string%'";

		return ' AND (' . implode(' OR ', $searches) . ')';
	}
}


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
		$from_time = 10; // ignore comments older than 10 days ago
		$number = 10; // only get 10 comments for login user

		/*
			If the user is not a guest, use a modified get_comments to be able to pass time range, optimize queries in db and number limit
		*/
		if($user_ID == ''){
			$title = apply_filters( 'widget_title', empty( $instance['title_recent'] ) ? __( 'Recent Comments' ) : $instance['title_recent'], $instance, $this->id_base );			
			$number = $instance['number_recent'];
			$order='desc';
			$show_recent = $instance['show_recent'];			
			$show_text = $instance['show_text'];
			$custom_text = $instance['custom_text'];
			$comments = get_comments( array( 'number' => $number, 'order' => $order, 'status' => 'approve', 'post_status' => 'publish' ) );
		} else {
			$comments = wp_unread_comments_get_comments( array( 'number' => $number, 'from_time' => $from_time, 'exclude_read_for' => $user_ID, 'order' => $order, 'status' => 'approve', 'post_status' => 'publish' ) );
		}

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

					/*
					$ts_nearfuture = strtotime("-10 days");
					if($ts_nearfuture > $ts_a) {
						$ts_a = $ts_nearfuture;
					}
					 */

					$comment_time = strtotime($comment->comment_date_gmt);
					if(!in_array($post_id, $postarray))
					{
						//if ($comment_time > $ts_a)
						//{
							array_push($postarray, $post_id);
							array_push($commentarray, '<li class="'.$rcclass.'">' . 
							sprintf(_x('%1$s', 'widgets'),  '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>');											
						//}
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
			if(count($commentarray)==0) $output.='<li class="recentcomments">Okumadığınız yorum kalmamış ki...</li>';
		
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
