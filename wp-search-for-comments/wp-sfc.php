<?php   
	/* 
	Plugin Name: WP Search for comments LITE
	Plugin URI: http://codecanyon.net/item/wp-search-for-comments/2907860
	Description: Plugin for search in comments. Lite, free version. It's search through comments. Can be added to page automatically or manually with shortcode < ?php if(function_exists('wp_sfc')) wp_sfc(); ?>. It returns comment author name, comment content and date in "times ago" format. Output can be styled with css.
	Author: Gleb Makarov 
	Version: 1.2.2
	Author URI: http://www.desadent.com 
	*/  

	add_action('wp_head', 'wp_sfc_print_styles');
	add_action('admin_menu', 'wp_sfc_admin_actions');
	add_action('admin_print_styles', 'wp_sfc_print_admin_styles');
	
	function wp_sfc_print_admin_styles() {
	
		wp_enqueue_style('admin_sfc', plugins_url('/wp-search-for-comments/css/wp-sfc-admin.css'), false, "1.0", "all");
	}

	if(get_option('wp_sfc_add')){
		add_action('loop_end', 'wp_sfc_check');
	}

	if( (version_compare( $GLOBALS['wp_version'], '2.4.999', '>' ) && get_option('wp_sfc_limit') == '0') || (version_compare( $GLOBALS['wp_version'], '2.4.999', '>' ) && get_option('wp_sfc_limit') == '') ) {
		add_action('admin_notices', 'wp_sfc_notice');
	}

	function wp_sfc_notice() {
		$assignoptionsoncemessage = __('You just installed the "WP Search for comments" plugin. Please <a href="options-general.php?page=Search_for_Comments">save the options</a> to assign the new capabilities to the system! Even if you don\'t want to change anything in plugin settings!<br />You think this plugin is good and you will use it, please buy the full version on <a href="http://codecanyon.net/item/wp-search-for-comments/2907860">codecanyon.net</a>');
		echo '<div id="assignoptionsoncemessage" class="error fade">
			<p>
				<strong>
					' . $assignoptionsoncemessage . '
				</strong>
			</p>
		</div>';
	}


	function wp_sfc_admin() {  
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
        include('wp_sfc_admin.php');  
    }  
      
    function wp_sfc_admin_actions() {  
        add_options_page("Search for Comments", "Search for Comments", 1, "Search_for_Comments", "wp_sfc_admin");  
    }  
      
      


	function wp_sfc_print_styles() {
		wp_enqueue_style('wp-sfc', plugins_url('/wp-search-for-comments/css/wp-sfc.css'), 'css');

	}

	function wp_sfc_check(){
		if(is_search()) wp_sfc();
	}

	function wp_sfc(){
		global $wpdb;
		$length = 0;
		$count = get_option('wp_sfc_limit');

		$commenters = array();
		
		$results = $wpdb->get_results("SELECT comment_content, comment_date, comment_ID, user_id, comment_author, comment_author_url, comment_post_ID FROM $wpdb->comments WHERE comment_approved = '1'");

		foreach($results as $result){
			if(strpos(strtolower($result->comment_content), strtolower(get_search_query())) !== false){
				$commenters[] = (array) $result;
			}
		}
		
		$thetimearr = array();
		foreach ($commenters as $key => $row){
			$thetimearr[$key] = $row['comment_date'];
		}
		
		array_multisort($thetimearr, SORT_DESC, $commenters);
		
		$paged = 1;

		$current_page = $paged;
		$total_pages = ceil(count($commenters) / $count);
		
		$start = ($paged - 1) * $count;
		$end = $paged * $count;
		if ($end > count($commenters)) $end = count($commenters);
		
		
		

		
		$output = '<div class="searc-output-comments">';
		if(count($commenters) != 0) {
			$output .= '<span class="s-titles fixtitle1">Comments results for: '.get_search_query().'</span>';
		}else{
			$output .= '<span class="s-titles fixtitle1">No comment results!</span>';
		}
		$cc = 1;
		if ($paged > 1) $cc = ($count * $paged) - ($count -1);

		for ($i = $start; $i < $end; $i++){
			
					$comment = $commenters[$i];
					$permalink = get_permalink( $comment['comment_post_ID'] );
					$output .= '<div class="output-post">';
					$output .= '	<div class="postcounter">'.$cc.'</div>';
					$output .= '	<div class="s-p-wrap">';
					$output .= '		<div class="s-p-w-top">';
					$output .= '			<span class="author-name"><a href="'.$permalink.'#comment-'.$comment['comment_ID'].'">'.$comment['comment_author'].'</a></span>';
					$output .= '			<div class="comment-date">'.time_ago($comment['comment_date']).'</div>';
					$output .= '		</div>';
					$output .= '	<div class="s-p-w-bottom">';					
					$output .= '			<p><a href="'.$permalink.'#comment-'.$comment['comment_ID'].'">'.$comment['comment_content'].'</a></p>';
					$output .= '		</div>';
					$output .= '	</div>';
					$output .= '</div>';
					$cc++;
				
			
		}
		$output .= '</div>';
	
		echo $output;

		return array(
			'current_page' => $current_page,
			'total_pages' => $total_pages
		);
	}

	function time_ago($date) {
		$chunks = array(
			array( 60 * 60 * 24 * 365 , __( 'year' ), __( 'years' ) ),
			array( 60 * 60 * 24 * 30 , __( 'month' ), __( 'months' ) ),
			array( 60 * 60 * 24 * 7, __( 'week' ), __( 'weeks' ) ),
			array( 60 * 60 * 24 , __( 'day' ), __( 'days' ) ),
			array( 60 * 60 , __( 'hour' ), __( 'Hours' ) ),
			array( 60 , __( 'minute' ), __( 'minutes' ) ),
			array( 1, __( 'second' ), __( 'seconds' ) )
		);
	 
		if ( !is_numeric( $date ) ) {
			$time_chunks = explode( ':', str_replace( ' ', ':', $date ) );
			$date_chunks = explode( '-', str_replace( ' ', '-', $date ) );
			$date = gmmktime( (int)$time_chunks[1], (int)$time_chunks[2], (int)$time_chunks[3], (int)$date_chunks[1], (int)$date_chunks[2], (int)$date_chunks[0] );
		}
	 
		$current_time = current_time( 'mysql', $gmt = 0 );
		$newer_date = strtotime( $current_time );
		$since = $newer_date - $date;
	 
		if ( 0 > $since )
			return __( 'sometime' );

		for ( $i = 0, $j = count($chunks); $i < $j; $i++) {
			$seconds = $chunks[$i][0];
			if ( ( $count = floor($since / $seconds) ) != 0 )
				break;
		}
	 
		$output = ( 1 == $count ) ? '1 '. $chunks[$i][1] : $count . ' ' . $chunks[$i][2];

		if ( !(int)trim($output) ){
			$output = '0 ' . __( 'seconds' );
		}

		$output .= __(' ago');
		return $output;
	}

/* --- END OF FILE --- */ 