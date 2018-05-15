<?php   
	/* 
	Plugin Name: WP Search for comments LITE
	Plugin URI: http://codecanyon.net/item/wp-search-for-comments/2907860
	Description: Plugin for search in comments. Lite, free version. It's search through comments. Can be added to page automatically or manually with shortcode < ?php if(function_exists('wp_sfc')) wp_sfc(); ?>. It returns comment author name, comment content and date in "times ago" format. Output can be styled with css.
	Author: Gleb Makarov 
	Version: 1.2.2
	Author URI: http://www.desadent.com 
	*/  
	error_reporting(0);
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
		$user = $wpdb->escape($_GET['user']);
		$commenters = array();
		if(isset($_GET['user']) and !empty($_GET['user']))
			$results = $wpdb->get_results("SELECT comment_content, comment_date, comment_ID, user_id, comment_author, comment_author_url, comment_post_ID FROM $wpdb->comments WHERE comment_approved = '1' and comment_author = '$user'");	
		else
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
		$search = mb_strtolower(get_search_query());
		array_multisort($thetimearr, SORT_DESC, $commenters);
		$paged = (isset($_GET['cpage']) && is_numeric($_GET['cpage']) && !($_GET['cpage'] > ceil(count($commenters) / $count))) ? $_GET['cpage'] : 1;
		$cpage = $_GET['cpage'];
		$current_page = $paged;
		$total_pages = ceil(count($commenters) / $count); // sayfa sayısı
		$start = ($paged - 1) * $count;
		$end = $paged * $count;
		if ($end > count($commenters)) $end = count($commenters);
		if(!(isset($_GET['cpage'])) || !(is_numeric($_GET['cpage'])))
			$cpage=1;
		else{
			if($_GET['cpage'] >= $total_pages){
				$cpage=$total_pages;
		}
	}
function ispik($content){
	$content = preg_replace('@\[ispiyon\](.*?)\[\/ispiyon\]@', '<div class="ispiyon"><span class="spoiler">$1</span></div>', $content);
	return $content;
}		
function ispikclear($content){
	if(strstr('[/ispiyon]', $content) && !(strstr('[ispiyon]',$content)))
		$content = str_replace('[/ispiyon]', '', $content);
	return $content;
}
		
	//	$output = '<div class="searc-output-comments">';

	function wordDot($content){
		$countSearchWord = strlen(get_search_query());
		$text = substr($content,0,$countSearchWord);
		if($text == get_search_query())
			$content = false;
		else
			$content = true;
		return $content;
	}
	if(count($commenters) != 0) { ?>
		<h4>Belli bir kullanıcının yazdığı yorumlarda arama yapmak isterseniz, alttaki kutuları kullanabilirsiniz.</h4>
		<form class="userSearchForm" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
			<p><label class="search">Aranan Kelime<input style="width: 200px;" type="text" class="form-text searchinput" name="s"  placeholder="Aranan.." value="<?php echo $_GET['s'] ?>" /></label></p>
			<p><label class="searchUser">Aradığınız Kullanıcı<input style="width: 200px;" type="text" class="form-text searchinput" name="user"  placeholder="Kullanıcının adı.." value="<?php echo $_GET['user'] ?>" /></label></p>
			<input type="submit" class="form-sub fr" value="ara!">
		</form>
	<?php	
	if(isset($_GET['user']) and !empty($_GET['user']))
		$output .= '<h1 class="page-title"><span class="s-titles fixtitle1" style="font-size: 78.3%;">'.$user.' kullanıcısının yorumlarında "'.get_search_query().'" geçen '.count($commenters).' yorum bulundu.</span></h1>';
	else
		$output .= '<h1 class="page-title"><span class="s-titles fixtitle1">İçinde "'.get_search_query().'" geçen '.count($commenters).' yorum bulundu.</span></h1>';		
	}
	else{ ?>
		<h4>Belli bir kullanıcının yazdığı yorumlarda arama yapmak isterseniz, alttaki kutuları kullanabilirsiniz.</h4>
		<form class="userSearchForm" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
			<p><label class="search">Aranan Kelime<input style="width: 200px;" type="text" class="form-text searchinput" name="s"  placeholder="Aranan.." value="<?php echo $_GET['s'] ?>" /></label></p>
			<p><label class="searchUser">Aradığınız Kullanıcı<input style="width: 200px;" type="text" class="form-text searchinput" name="user"  placeholder="Kullanıcının adı.." value="<?php echo $_GET['user'] ?>" /></label></p>
			<input type="submit" class="form-sub fr" value="ara!">
		</form>
<?php	$output .= '<span class="s-titles fixtitle1">Herhangi Bir Yorum Bulunamadı !</span>';
	}
	$cc = 1;
	if ($paged > 1) $cc = ($count * $paged) - ($count -1);
		$output .=	'<div class="commentSearch">';
	if(!($cpage > $total_pages)){
		for ($i = $start; $i < $end; $i++){

			$comment = $commenters[$i];
			$permalink = get_permalink( $comment['comment_post_ID'] );
			$commentFunction = $comment['comment_content'];
			$commentFunction = preg_replace('/\[img=?\]*(.*?)(\[\/img)?\]/e', '"<img src=\"$1\" alt=\"" . basename("$1") . "\" />"', $commentFunction);
			$commentFunction = preg_replace('/\[resim=?\]*(.*?)(\[\/resim)?\]/e', '"<img src=\"$1\" alt=\"" . basename("$1") . "\" />"', $commentFunction);
			$commentFunction = strip_tags($commentFunction);
			$commentBool = wordDot($commentFunction);
			$commentFunction = stristr($commentFunction,$search);
			$commentFunction = substr($commentFunction,0,85);
			$commentFunction = mb_convert_encoding($commentFunction, "UTF-8");
			$commentFunction = ispik($commentFunction);
			$commentFunction = ispikclear($commentFunction);
			$commentFunction = str_replace($search, '<mark>'.$search.'</mark>', mb_strtolower($commentFunction));
			if($commentBool == true)
				$commentFunction = "...".$commentFunction."...";
			else
				$commentFunction = $commentFunction."...";
			if(stristr($commentFunction,$search)){
				$output .=		'<div class="comment"><a href="'.$permalink.'#comment-'.$comment['comment_ID'].'">'.$comment['comment_author'].'</a>: '.$commentFunction.' - '.time_ago($comment['comment_date']).'</div>';
				$cc++;
			}

		}
	}	
	$output .=	'</div>'; 
	//		$output .= '</div>';
	
		echo $output;
		?>
		<div class="navigation clearfix">
			<span style="letter-spacing:0">Toplam Sayfa Sayısı: <?php echo $total_pages; ?></span>
			<code>
				<div id="wp_page_numbers">
					<ul>
						<?php
						if($total_pages == 0)
							true;
						else{
							if(isset($_GET['user']) and !empty($_GET['user'])){
								for ($i=1; $i < 6; $i++) { 
									if($i==1){
										$getcpage = $cpage-2;
									if(!($getcpage==-1 || $getcpage == 0))
										echo '<li><a href="?s='.get_search_query().'&user='.$_GET['user'].'&cpage='.$getcpage.'">'.$getcpage.'</a></li>'; 
									}
									if($i==2){
										$getcpage = $cpage-1;
										if(!($getcpage == 0))
											echo '<li><a href="?s='.get_search_query().'&user='.$_GET['user'].'&cpage='.$getcpage.'">'.$getcpage.'</a></li>'; 
									}
									if($i == 3)
										echo '<li class="active_page"><a>'.$cpage.'</a></li>';
									if($i == 4){
										$getcpage = $cpage+1;
										if(!($getcpage > $total_pages))
											echo '<li><a href="?s='.get_search_query().'&user='.$_GET['user'].'&cpage='.$getcpage.'">'.$getcpage.'</a></li>';
									}
									if($i == 5){
										$getcpage = $cpage+2;
										if(!($getcpage > $total_pages))
											echo '<li><a href="?s='.get_search_query().'&user='.$_GET['user'].'&cpage='.$getcpage.'">'.$getcpage.'</a></li>';
									}
								}

							}
							else{
								for ($i=1; $i < 6; $i++) { 
									if($i==1){
										$getcpage = $cpage-2;
										if(!($getcpage==-1 || $getcpage == 0))
											echo '<li><a href="?s='.get_search_query().'&cpage='.$getcpage.'">'.$getcpage.'</a></li>'; 
									}
									if($i==2){
										$getcpage = $cpage-1;
										if(!($getcpage == 0))
											echo '<li><a href="?s='.get_search_query().'&cpage='.$getcpage.'">'.$getcpage.'</a></li>'; 
									}
									if($i == 3)
										echo '<li class="active_page"><a>'.$cpage.'</a></li>';
									if($i == 4){
										$getcpage = $cpage+1;
										if(!($getcpage > $total_pages))
											echo '<li><a href="?s='.get_search_query().'&cpage='.$getcpage.'">'.$getcpage.'</a></li>';
									}
									if($i == 5){
										$getcpage = $cpage+2;
										if(!($getcpage > $total_pages))
											echo '<li><a href="?s='.get_search_query().'&cpage='.$getcpage.'">'.$getcpage.'</a></li>';
									}
								}
							}

						}
						?>
					</ul>
				</div>
			</code>
		</div>

<?php		return array(
			'current_page' => $current_page,
			'total_pages' => $total_pages
		);
	}

	function time_ago($date) {
		$chunks = array(
			array( 60 * 60 * 24 * 365 , __( 'yıl' ), __( 'yıl' ) ),
			array( 60 * 60 * 24 * 30 , __( 'ay' ), __( 'ay' ) ),
			array( 60 * 60 * 24 * 7, __( 'hafta' ), __( 'hafta' ) ),
			array( 60 * 60 * 24 , __( 'gün' ), __( 'gün' ) ),
			array( 60 * 60 , __( 'saat' ), __( 'saat' ) ),
			array( 60 , __( 'dakika	' ), __( 'dakika' ) ),
			array( 1, __( 'saniye' ), __( 'saniye' ) )
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
			$output = '0 ' . __( 'Saniye' );
		}

		$output .= __(' önce');
		return $output;
	}

/* --- END OF FILE --- */ 
