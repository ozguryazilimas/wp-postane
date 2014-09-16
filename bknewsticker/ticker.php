<?php
/*
Plugin Name: BkNewsticker
Plugin URI: http://bekero.com/bknewsticker/
Description: BkNewsticker for Wordpress is a multi-functional data display plugin
Author: Bechetaru Constantin
Author URI: http://www.bekero.com
Version: 1.0.5
*/
define('TICKER_VERSION', '1.0.5');
define('TICKER_MAX_INT', defined('PHP_INT_MAX') ? PHP_INT_MAX : 32767);
define('PHPREQ',5);
$phpver=phpversion();$phpmaj=$phpver[0];
function ticker_add_pages() {
	add_options_page('BkNewsticker', 'BkNewsticker', 'edit_pages','tickeroptions', 'ticker_options_page');
}
// BkNewsticker languages

add_action( 'plugins_loaded', 'bknewsticker_localization' );
/**
 * Setup localization
 *
 * @since 1.0.4
 */
function bknewsticker_localization() {
	load_plugin_textdomain( 'bknewsticker', false, 'bknewsticker/languages/' );
}


// BkNewsticker plugin

if($phpmaj>=PHPREQ){ require_once('bk-rss.php'); }
register_activation_hook( __FILE__, 'ticker_activate' );
register_deactivation_hook( __FILE__, 'ticker_deactivate' );
add_action('switch_theme', 'ticker_activate');
add_action('admin_menu', 'ticker_add_pages');

function bk_ticker_js(){	
	if(!is_admin()){
		wp_enqueue_script ('jquery');	
		wp_enqueue_script ('ticker_pack', plugins_url('cycle.js', __FILE__ ),array( 'jquery' ), '1.0.4', false );
	}
}
add_action( 'wp_enqueue_scripts', 'bk_ticker_js' );

add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'my-script-handle', plugins_url('bk-color.js', __FILE__ ), array( 'wp-color-picker' ), false );
}
function bkticker_register_style() {
	if (!is_admin()) {
		wp_enqueue_style('bktickercss', plugins_url('ticker.css', __FILE__ ),'1.0.4');
	}
}
add_action('wp_enqueue_scripts', 'bkticker_register_style');
function bkfont_register_style() {
		wp_enqueue_style('bkfont', plugins_url('font-awesome/css/font-awesome.min.css', __FILE__ ));
}
add_action('wp_enqueue_scripts', 'bkfont_register_style');


function bk_header_style()
{	
	require_once('ticker-color.php');
}
add_action('wp_head', 'bk_header_style');

// BkNewsticker insert
function insert_bknewsticker(){
	$tickerspeed=get_option('ticker_speed');
	$tickertimeout=get_option('ticker_timeout');
	$tickeranimation=get_option('ticker_anim');
	?>
	<script type="text/javascript" language="javascript">
	jQuery(document).ready(function(){
		jQuery('#bknewsticker').cycle({ 
			speed: <?php echo $tickerspeed; ?>000,
			timeout: <?php echo $tickertimeout; ?>000,
			height: 40,
			fx: '<?php echo $tickeranimation; ?>',
			pause: 1,
			containerResize: 1
		});
	});
</script><div id="allticker"> 
<div id="bknews-title"><h3><?php $color_border = get_option('tiker_content_title'); echo $color_border;?> <i class="fa fa-forward"></i></h3></div>
<ul id="bknewsticker" style="overflow:hidden;"><?php  ticker_content(); ?></ul>
</div>
<?php
}

function ticker_content(){
	$phpver=phpversion();$phpmaj=$phpver[0];
	if($phpmaj<PHPREQ){
		$postorcomment=get_option('ticker_rss');
		update_option('ticker_rss','norss');
	}
	$site_url = get_option('siteurl');
	$rss_opt_val = get_option('ticker_rss');
	$dates_opt_val = get_option('ticker_dates');
	$content_opt_val = get_option('ticker_content');
	$type_opt_val = get_option('ticker_type');
	if($type_opt_val=='recent-comments' && $rss_opt_val=='norss'){
		$posts = ticker_recent_comments(
			get_option('ticker_num_posts'),
			get_option('ticker_auto_excerpt_length')
			);

	}else{
		switch($rss_opt_val){
			case 'external':
			include_once(ABSPATH.WPINC.'/rss.php');
			$namenum = get_option('ext_rss');
			$maxnum = get_option('ext_rss_num');
			$feed = fetch_rss($namenum); 
			$items = array_slice($feed->items, 0, $maxnum);
			break;
			case 'comments':
			$posts = bkticker_use_rss(get_bloginfo('comments_rss2_url'));
			break;
			case 'entries':
			$posts = bkticker_use_rss(get_bloginfo('rss2_url'));
			break;
			case 'norss':
			$posts = ticker_get_posts(
				get_option('ticker_type'),
				get_option('ticker_category_filter'),
				get_option('ticker_num_posts'),
				get_option('ticker_user_specified_posts')
				);
			break;
			case 'norss-comments':
			break;
			default:
			$posts = ticker_get_posts(
				get_option('ticker_type'),
				get_option('ticker_category_filter'),
				get_option('ticker_num_posts'),
				get_option('ticker_user_specified_posts')
				);
			break;
		}
	}
	if ($rss_opt_val=='external') {
		if (!empty($items)) : 
			foreach ($items as $item) : ?> 
		<li>
			<span class="tickerDate"><?php $pubdate = substr($item['pubdate'], 4, 12); echo $pubdate; ?></span> 
			<span class="tickerLink"><a href="<?php echo $item['link']; ?>"><?php echo $item['title']; ?></a><?php echo $item['description']; ?></span>
		</li>
		<?php endforeach; 
		endif; 
	} else {  

		foreach ($posts as $post_id => $post){
			$title	= $posts[$post_id]['post_title'];
			$excerpt	= $posts[$post_id]['post_excerpt'];
			$link	= $posts[$post_id]['url'];
			$date = (isset($posts[$post_id]['post_human_date']) ? $posts[$post_id]['post_human_date'] : null);
			?>
			<li>
				<?php if($dates_opt_val=='checked') { ?>
				<span class="tickerDate"><?php echo $date; ?></span><?php } ?>
				<span class="tickerLink"><a href="<?php echo $link; ?>"><?php echo $title;?></a></span>
				<?php if($content_opt_val=='checked') { ?><span class="tickerText"><i class="fa fa-bullhorn"></i> <?php echo $excerpt; ?>..</span>
				<span class="tickermore"><a href="<?php echo $link; ?>"><i class="fa fa-arrow-right"></i></a></span>
				<?php } ?></li>
				<?php
			}
		}
	}

function ticker_recent_comments($src_count, $src_length) {
	global $wpdb;
	
	$sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID, comment_post_ID, comment_author, comment_date_gmt, comment_approved, comment_type, 
	SUBSTRING(comment_content,1,$src_length) AS com_excerpt 
	FROM $wpdb->comments 
	LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) 
	WHERE comment_approved = '1' AND comment_type = '' AND post_password = '' 
	ORDER BY comment_date_gmt DESC 
	LIMIT $src_count";
	$sql = apply_filters('ticker-recent-comments',$sql);
	$comments = $wpdb->get_results($sql);

	foreach($comments as $comment){
		$title="Comment on ".$comment->post_title." by ".$comment->comment_author;
		$link =get_permalink($comment->ID);
		$description=$comment->com_excerpt;

		$posts[$comment->comment_ID]['post_title']=ticker_html_to_text($title);
		$posts[$comment->comment_ID]['post_excerpt']=ticker_html_to_text($description);
		$posts[$comment->comment_ID]['url']=$link;
	}
	return $posts;
}

function ticker_get_posts($type, $cat_filter, $n, $post_list=null){
	switch($type){
		case 'popular':
		$days = get_option('ticker_popular_days');
		$popular_posts = stats_get_csv('postviews', "days=$days&limit=0"); 
		$post_list = '';
		foreach ($popular_posts as $post) {
			if($post_list!='')
				$post_list .= ', ';

			$post_list .= $post['post_id'];
		}
		return ticker_get_posts('userspecified', $cat_filter, $n, $post_list);
		break;
		case 'recent':
		$posts = get_posts(
			array(
				'numberposts' => TICKER_MAX_INT, 
				'orderby' => 'post_date',
				'suppress_filters' => 0,
				)
			);
		break;
		case 'commented':
		$posts = get_posts(
			array(
				'numberposts' => TICKER_MAX_INT,
				'orderby' => 'comment_count',
				'suppress_filters' => 0,
				)
			);
		break;
		case 'userspecified':
		$posts_tmp = get_posts(
			array(
				'numberposts' => TICKER_MAX_INT,
				'include' => $post_list,
				'suppress_filters' => 0,
				)
			);
		$posts = array();
		$post_list_arr = preg_split('/[\s,]+/', $post_list); 

		foreach($post_list_arr as $post_id) {
			foreach($posts_tmp as $post) {
				if($post->ID==$post_id) {
					$posts[] = $post;
					break; 
				}
			}
		}
		break;
		default:
		$posts = null;
		break;
	}
	$cat_filter = apply_filters('category-filter', $cat_filter);
	if($cat_filter==null || sizeof($cat_filter)<1)
		$do_category_filter = false;
	else
		$do_category_filter = true;
	$posts_fixed = array();
	if($posts!=null && sizeof($posts)>0 && is_object($posts[0])) {
		foreach($posts as $k => $v){
			if(sizeof($posts_fixed)==$n)
				break;
			$post_categories = wp_get_post_categories($v->ID);
			if(!$do_category_filter || ($do_category_filter && sizeof(array_intersect($cat_filter, $post_categories))>0))
				$posts_fixed[$v->ID] = (array) $v;
		}
	}
	ticker_get_posts_categories($posts_fixed);
	ticker_get_posts_tags($posts_fixed);
	ticker_get_posts_meta($posts_fixed);
	ticker_get_posts_tweak($posts_fixed);
    return $posts_fixed;
}
function ticker_get_posts_categories(&$posts) {
	foreach ($posts as $post_id => $post) {
		$cats = wp_get_post_categories($post_id);
		$categories = '';
		$cat_num = 1;
		foreach ($cats as $cat_id) {
			$cat = get_category($cat_id);
			if($categories!='')
				$categories .= ', ';
			$categories .= $cat->name;
			$posts[$post_id]["category_$cat_num"] = $cat->name;
			$cat_num++;
		}
		$posts[$post_id]['categories'] = $categories;
	}
}

function ticker_get_posts_tags(&$posts) {
	foreach ($posts as $post_id => $post) {
		$tags = get_the_tags($post_id);
		$tags_str = '';
		if($tags!=null && sizeof($tags)>0) {
			$tag_num = 1;
			foreach ($tags as $tag) {
				if($tags_str!='')
					$tags_str .= ', ';
				$tags_str .= $tag->name;
				$posts[$post_id]["tag_$tag_num"] = $tag->name;
				$tag_num++;
			}
		}
		$posts[$post_id]['tags'] = $tags_str;
	}
}
function ticker_get_posts_meta(&$posts) {
	foreach ($posts as $post_id => $post) {
		$custom_fields = get_post_custom($post_id);
		foreach ($custom_fields as $k => $v) {
			$posts[$post_id][$k] = $v[0];
		}
	}
}
function ticker_get_posts_tweak(&$posts) {	
	$date_chars = array('d', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'W', 'F', 'm', 'M', 'n', 't', 'L', 'o', 'Y', 'y', 'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'e', 'I', 'O', 'P', 'T', 'Z', 'c', 'r', 'U');
	foreach ($posts as $post_id => $post) {
		$date_str = $post['post_date'];
		$date = ticker_parse_date($date_str);
		$posts[$post_id]['post_human_date'] = ticker_date_to_human_date($date);
		$posts[$post_id]['post_long_human_date'] = ticker_date_to_long_human_date($date);
		$posts[$post_id]['post_slashed_date'] = ticker_date_to_slashed_date($date);
		$posts[$post_id]['post_dotted_date'] = ticker_date_to_dotted_date($date);
		$posts[$post_id]['post_human_time'] = ticker_date_to_human_time($date);
		$posts[$post_id]['post_long_human_time'] = ticker_date_to_long_human_time($date);
		$posts[$post_id]['post_military_time'] = ticker_date_to_military_time($date);
		foreach($date_chars as $dc)
		$posts[$post_id]["post_date_$dc"] = date($dc, $date);
		$date_str = $post['post_modified'];
		$date = ticker_parse_date($date_str);
		$posts[$post_id]['post_modified_human_date'] = ticker_date_to_human_date($date);
		$posts[$post_id]['post_modified_long_human_date'] = ticker_date_to_long_human_date($date);
		$posts[$post_id]['post_modified_slashed_date'] = ticker_date_to_slashed_date($date);
		$posts[$post_id]['post_modified_dotted_date'] = ticker_date_to_dotted_date($date);
		$posts[$post_id]['post_modified_human_time'] = ticker_date_to_human_time($date);
		$posts[$post_id]['post_modified_long_human_time'] = ticker_date_to_long_human_time($date);
		$posts[$post_id]['post_modified_military_time'] = ticker_date_to_military_time($date);
		foreach($date_chars as $dc)
		$posts[$post_id]["post_modified_date_$dc"] = date($dc, $date);		
		$posts[$post_id]['post_content'] = $post['post_content'];
		if(function_exists('do_shortcode'))
		$posts[$post_id]['post_content'] = do_shortcode($posts[$post_id]['post_content']);
		$posts[$post_id]['post_content'] =
		ticker_html_to_text(		
			str_replace("\xC2\xA0", '',
				$posts[$post_id]['post_content']
				)
			);
		if($posts[$post_id]['post_excerpt']==null || $posts[$post_id]['post_excerpt']=='') {
			$auto_excerpt_chars = get_option('ticker_auto_excerpt_length');
			$s = $posts[$post_id]['post_content'];
			$s = substr($s, 0, $auto_excerpt_chars);
			$s = substr($s, 0, strrpos($s, ' '));
		    $posts[$post_id]['post_excerpt'] = $s;
		}
		else {
			$posts[$post_id]['post_excerpt'] = ticker_html_to_text($posts[$post_id]['post_excerpt']);
		}
		$posts[$post_id]['nickname'] = get_user_meta($post['post_author'], 'nickname');
		$posts[$post_id]['url'] = apply_filters('the_permalink', get_permalink($post_id));
	}
}

function ticker_html_to_text($html) {
	$html = preg_replace('/<style[^>]*>.*?<\/style[^>]*>/si','',$html);
	$html = preg_replace('/<script[^>]*>.*?<\/script[^>]*>/si','',$html);
	$tags = array (
		0 => '/<(\/)?h[123][^>]*>/si',
		1 => '/<(\/)?h[456][^>]*>/si',
		2 => '/<(\/)?table[^>]*>/si',
		3 => '/<(\/)?tr[^>]*>/si',
		4 => '/<(\/)?li[^>]*>/si',
		5 => '/<(\/)?br[^>]*>/si',
		6 => '/<(\/)?p[^>]*>/si',
		7 => '/<(\/)?div[^>]*>/si',
		);
	$html = preg_replace($tags, "\n", $html);
	$html = preg_replace('/<[^>]+>/s', '', $html);
	$html = preg_replace('/\&nbsp;/', ' ', $html);
	$html = preg_replace('/ +/s', ' ', $html);
	$html = preg_replace('/^\s+/m', '', $html);
	$html = preg_replace('/\s+$/m', '', $html);
	$html = preg_replace('/\n+/s', '-!Line Break123!-', $html);
	$html = preg_replace('/(-!Line Break123!-)+/s', ' - ', $html);
	$html = preg_replace('/ +/s', ' ', $html);
	$html = preg_replace('/^\s+/m', '', $html);
	$html = preg_replace('/\s+$/m', '', $html);
	return $html;
}

function ticker_date_to_human_date($date) {
	return date('F j, Y', $date);
}
function ticker_date_to_long_human_date($date) {
	return date('l jS \of F Y', $date);
}
function ticker_date_to_slashed_date($date) {
	return date('m/d/y', $date);
}
function ticker_date_to_dotted_date($date) {
	return date('m.d.y', $date);
}
function ticker_date_to_human_time($date) {
	return date('g:i a', $date);
}
function ticker_date_to_long_human_time($date) {
	return date('g:i:s a', $date);
}
function ticker_date_to_military_time($date) {
	return date('H:i:s', $date);
}
function ticker_parse_date($string) {
	preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches);
	return mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
}
function ticker_activate()
{
	ticker_set_default_options();
}
function ticker_deactivate()
{
}
function ticker_set_default_options() {
	if(get_option('ticker_background_posts')===false)			    add_option('ticker_background_posts', '#333333');
	if(get_option('ticker_color_excerpt')===false)			        add_option('ticker_color_excerpt', '#ffffff');
	if(get_option('ticker_color_border')===false)			        add_option('ticker_color_border', '#cccccc');
	if(get_option('ticker_color_title')===false)			        add_option('ticker_color_title', '#ffffff');
	if(get_option('ticker_color_link')===false)			        	add_option('ticker_color_link', '#DD3333');
	if(get_option('tiker_content_title')===false)			        add_option('tiker_content_title', News);
	if(get_option('ticker_background_title')===false)			    add_option('ticker_background_title', '#2222222');
	if(get_option('ticker_dates')===false)		                    add_option('ticker_dates', 'checked');
	if(get_option('ticker_content')===false)		                add_option('ticker_content', 'checked');
	if(get_option('ticker_type')===false)		                    add_option('ticker_type', 'commented');
	if(get_option('ticker_category_filter')===false)		        add_option('ticker_category_filter', array());
	if(get_option('ticker_user_specified_posts')===false)		    add_option('ticker_user_specified_posts', '');
	if(get_option('ticker_num_posts')===false)			            add_option('ticker_num_posts', 5);
	if(get_option('bke_textall_size')===false)			            add_option('bke_textall_size', 14);
	if(get_option('border_size_ticker')===false)			        add_option('border_size_ticker', 1);
	if(get_option('ticker_popular_days')===false)			        add_option('ticker_popular_days', 90);
	if(get_option('ticker_auto_excerpt_length')===false)		    add_option('ticker_auto_excerpt_length', 90);
	if(get_option('ticker_admin_messages_to_show_once')===false)    add_option('ticker_admin_messages_to_show_once', array());
	if(get_option('ticker_rss')===false)		                    add_option('ticker_rss', 'norss');
	if(get_option('ext_rss')===false)		                        add_option('ext_rss', '');
	if(get_option('ext_rss_num')===false)		                    add_option('ext_rss_num', '');
	if(get_option('ticker_speed')===false)		                    add_option('ticker_speed', 1);
	if(get_option('ticker_timeout')===false)		                add_option('ticker_timeout', 2);
	if(get_option('ticker_anim')===false)		                    add_option('ticker_anim', 'fade');
}
function ticker_delete_options() {
	delete_option('ticker_dates');
	delete_option('ticker_content');
	delete_option('ticker_type');
	delete_option('ticker_category_filter');
	delete_option('ticker_user_specified_posts');
	delete_option('tiker_content_title');
	delete_option('ticker_num_posts');
	delete_option('bke_textall_size');
	delete_option('ticker_background_posts');
	delete_option('ticker_color_excerpt');
	delete_option('ticker_color_border');
	delete_option('border_size_ticker');
	delete_option('ticker_color_title');
	delete_option('ticker_color_link');
	delete_option('ticker_background_title');
	delete_option('ticker_popular_days');
	delete_option('ticker_auto_excerpt_length');
	delete_option('ticker_admin_messages_to_show_once');
	delete_option('ticker_rss');
	delete_option('ext_rss');
	delete_option('ext_rss_num');
	delete_option('ticker_speed');
	delete_option('ticker_timeout');
	delete_option('ticker_anim');
	
}
function ticker_options_page() {
	$background_bk_news = 'ticker_background_posts';
	$ticker_bk_excerpt = 'ticker_color_excerpt';
	$color_bk_border = 'ticker_color_border';
	$ticker_bk_title = 'ticker_color_title';
	$ticker_bk_link = 'ticker_color_link';
	$ticker_bk_bktitle = 'ticker_background_title';
	$hidden_field_name = 'ticker_submit_hidden';
	$dates_opt_name = 'ticker_dates';
	$content_opt_name = 'ticker_content';
	$type_opt_name = 'ticker_type';
	$category_filter_opt_name = 'ticker_category_filter';
	$num_posts_opt_name = 'ticker_num_posts';
	$bk_textall_size_name = 'bke_textall_size';
	$border_bk_size = 'border_size_ticker';
	$tiker_bk_title = 'tiker_content_title';
	$auto_excerpt_length_opt_name = 'ticker_auto_excerpt_length';
	$rss_opt_name = 'ticker_rss';
	$ext_rss_name = 'ext_rss';
	$ext_rss_num_name = 'ext_rss_num';
	$ticker_speed_opt_name = 'ticker_speed';
	$ticker_timeout_opt_name = 'ticker_timeout';
	$ticker_anim_opt_name = 'ticker_anim';
	
	$background_bk_news_val = get_option($background_bk_news);
	$ticker_bk_excerpt_val = get_option($ticker_bk_excerpt);
	$color_bk_border_val = get_option($color_bk_border);
	$ticker_bk_title_val = get_option($ticker_bk_title);
	$ticker_bk_link_val = get_option($ticker_bk_link);
	$ticker_bk_bktitle_val = get_option($ticker_bk_bktitle);
	$dates_opt_val = get_option($dates_opt_name);
	$content_opt_val = get_option($content_opt_name);
	$type_opt_val = get_option($type_opt_name);
	$category_filter_val = get_option($category_filter_opt_name);
	$num_posts_opt_val = get_option($num_posts_opt_name);
	$bk_textall_size_val = get_option($bk_textall_size_name);
	$border_bk_size_val = get_option($border_bk_size);
	$tiker_bk_title_val = get_option($tiker_bk_title);
	$auto_excerpt_length_opt_val = get_option($auto_excerpt_length_opt_name);
	$rss_opt_val = get_option($rss_opt_name);
	$ext_rss_val = get_option($ext_rss_name);
	$ext_rss_num_val = get_option($ext_rss_num_name);
	$ticker_speed_opt_val = get_option($ticker_speed_opt_name);
	$ticker_timeout_opt_val = get_option($ticker_timeout_opt_name);
	$ticker_anim_opt_val = get_option($ticker_anim_opt_name);
	
	if( isset( $_POST[ $hidden_field_name ]) == 'Y' ) {

		$background_bk_news_val = $_POST[$background_bk_news];
		$ticker_bk_excerpt_val = $_POST[$ticker_bk_excerpt];
		$color_bk_border_val = $_POST[$color_bk_border];
		$ticker_bk_title_val = $_POST[$ticker_bk_title];
		$ticker_bk_link_val = $_POST[$ticker_bk_link];
		$ticker_bk_bktitle_val = $_POST[$ticker_bk_bktitle];
		$dates_opt_val = $_POST[$dates_opt_name];
		$content_opt_val = $_POST[$content_opt_name];
		$type_opt_val = $_POST[$type_opt_name];
		$category_filter_val = $_POST[$category_filter_opt_name];
		$frequency_opt_val = 10;
		$num_posts_opt_val = $_POST[$num_posts_opt_name];
		$bk_textall_size_val = $_POST[$bk_textall_size_name];
		$border_bk_size_val = $_POST[$border_bk_size];
		$tiker_bk_title_val = $_POST[$tiker_bk_title];
		$auto_excerpt_length_opt_val = $_POST[$auto_excerpt_length_opt_name];
		$rss_opt_val = $_POST[$rss_opt_name];
		$ext_rss_val = $_POST[$ext_rss_name];
		$ext_rss_num_val = $_POST[$ext_rss_num_name];
		$ticker_speed_opt_val = $_POST[$ticker_speed_opt_name];
		$ticker_timeout_opt_val = $_POST[$ticker_timeout_opt_name];
		$ticker_anim_opt_val = $_POST[$ticker_anim_opt_name];
		
		if($type_opt_val=='popular' && !function_exists('stats_get_csv')) {
			echo "<div class='updated' style='background-color:#f66;'><p>
			<a href='options-general.php?page=tickeroptions'>Ticker for Wordpress</a> needs attention: please install the <a href='http://wordpress.org/extend/plugins/stats/'>Wordpress.com Stats</a> plugin to use the 'Most popular' post selection type.  Until the plugin is installed, consider using the 'Most commented' post selection type instead.</p></div>";
			$type_opt_val = 'commented';
		}
		
		update_option($dates_opt_name, $dates_opt_val);
		update_option($content_opt_name, $content_opt_val);
		update_option($type_opt_name, $type_opt_val);
		update_option($category_filter_opt_name, $category_filter_val);
		update_option($num_posts_opt_name, $num_posts_opt_val);
		update_option($bk_textall_size_name, $bk_textall_size_val);
		update_option($border_bk_size, $border_bk_size_val);
		update_option($tiker_bk_title, $tiker_bk_title_val);
		update_option($ticker_bk_link, $ticker_bk_link_val);
		update_option($background_bk_news, $background_bk_news_val);
		update_option($ticker_bk_excerpt, $ticker_bk_excerpt_val);
		update_option($color_bk_border, $color_bk_border_val);
		update_option($ticker_bk_title, $ticker_bk_title_val);
		update_option($ticker_bk_bktitle, $ticker_bk_bktitle_val);
		update_option($auto_excerpt_length_opt_name, $auto_excerpt_length_opt_val);
		update_option($rss_opt_name, $rss_opt_val);
		update_option($ext_rss_name, $ext_rss_val);
		update_option($ext_rss_num_name, $ext_rss_num_val);
		update_option($ticker_speed_opt_name, $ticker_speed_opt_val);
		update_option($ticker_timeout_opt_name, $ticker_timeout_opt_val);
		update_option($ticker_anim_opt_name, $ticker_anim_opt_val);
		
			echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
	}
	$stats_installed_str = function_exists('stats_get_csv')?'<font color="#00cc00">is installed</font>':'<font color="#ff0000">is not installed</font>';
	$plugin_directory = ticker_get_plugin_root();

	?>

	<div class="wrap">
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$(".external-extra").hide("fast");
			$(".ruler").click(function(){
				if($(this).val()==="norss") $(".post-extra").show("fast"); else $(".post-extra").hide("fast");
			});
			$(".ruler").click(function(){
				if($(this).val()==="external") $(".external-extra").show("fast"); else $(".external-extra").hide("fast");
			});
		});
		</script>
	<!-- BkNewsticker style -->
		<style>

		tr.bkadmin {
			background:#333333;
			color: #fff;
		}
		.bktitle-admin{
			color: #fff!important;
			margin-left: 10px;
			text-transform: uppercase;
		}
		.bktitle-admin-two{
			color: #222!important;
			margin-left: 10px;
			text-transform: uppercase;
			width: 150px;
		}
		.bktitle-setup{
			color: #222!important;
			margin-left: 5px;
			text-transform: uppercase;
		}
		.bkform-table{
		border-collapse: collapse;
    	clear: both;
    	margin-top: 0.5em;
   	 	width: 70%;
   	 	border-left: 1px solid #ccc;
   	   }
   	    .bkform-table, .bkform-table td, .bkform-table td p, .bkform-table th, .bkform-wrap label {
        font-size: 14px;
        line-height: 22px;
        text-align: left;
       	}
		.bktitle-csetup{
			color: #fff!important;
			background: #EB5959;
			margin-left: 5px;
			font-size: 16px;
			text-transform: uppercase;
		}
		.headercs{
			background: #EB5959;
		}
		.bkpost-extra{
			background: #fff;
		}
		</style>
		<!-- 	BkNewsticker admin options -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2><?php _e( 'BkNewsticker Settings',  'bknewsticker' ); ?></h2>
		<p></p>
		<hr />

		<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">


			<table class="bkform-table">
				<tr class="headercs" valign="top"><th scope="row"><div class="bktitle-csetup"><i class="fa fa-cog"></i> <?php _e( 'Content Setup',  'bknewsticker' ); ?></div></th></tr>
				<tr class="bkadmin" valign="top">
					<th scope="row"><div class="bktitle-admin"><i class="fa fa-comment"></i> <?php _e( 'Content Source:',  'bknewsticker' ); ?></div></th>
					<td>
						<?php $phpver=phpversion();$phpmaj=$phpver[0];
						if($phpmaj<PHPREQ){ ?>
						<?php _e( 'Use of these options currently requires php version ', 'bknewsticker' ); ?><?php echo PHPREQ; ?>. 
						<?php _e( 'Your current version is ', 'bknewsticker' ); ?><?php echo $phpver;?>.<br /></td>
						<?php
					} else { 
						?>
						<input class="ruler" type="radio" name="<?php echo $rss_opt_name; ?>" value='norss' <?php if($rss_opt_val=='norss'){echo 'checked';} ?> > <?php _e( '
						Blog Posts',  'bknewsticker' ); ?><br/>
						<input class="ruler" type="radio" name="<?php echo $rss_opt_name; ?>" value='entries' <?php if($rss_opt_val=='entries'){echo 'checked';} ?>>
						<?php _e( 'Local Entries RSS feed',  'bknewsticker' ); ?> <br/>
						<input class="ruler" type="radio" name="<?php echo $rss_opt_name; ?>" value='comments' <?php if($rss_opt_val=='comments'){echo 'checked';} ?>>
						<?php _e( 'Local Comments RSS feed',  'bknewsticker' ); ?><br/>
						<input class="ruler" type="radio" name="<?php echo $rss_opt_name; ?>" value='external' <?php if($rss_opt_val=='external'){echo 'checked';} ?>>
						<?php _e( 'External RSS feed',  'bknewsticker' ); ?><br/>
						<div class="external-extra">
							<?php _e( 'External RSS Feed Url:',  'bknewsticker' ); ?>        <input type="text" name="<?php echo $ext_rss_name; ?>" value="<?php echo $ext_rss_val; ?>" size="50" /><br/>
							<?php _e( 'Number of entries to display:',  'bknewsticker' ); ?> <input type="text" name="<?php echo $ext_rss_num_name; ?>" value="<?php echo $ext_rss_num_val; ?>" size="2" />
						</div>
					</td>
					<?php } ?>
				</tr>
				<tr valign="top" class="bkpost-extra">
					<th scope="row"><div class="bktitle-admin-two"><i class="fa fa-folder-o"></i> <?php _e( 'Category Filter:',  'bknewsticker' ); ?></div></th>
					<td> <?php _e( 'Select the categories to include.',  'bknewsticker' ); ?><br />
						    <select style="height: auto;" name="<?php echo $category_filter_opt_name; ?>[]" multiple="multiple">
							<?php 
							$categories =  get_categories(array('hide_empty' => false));
							if($categories!=null) {
								foreach ($categories as $cat) {
									if(in_array($cat->cat_ID, $category_filter_val))
										$selected = 'selected="selected"';
									else
									$selected = '';
									$option = '<option value="'.$cat->cat_ID.'" '.$selected.'>';
									$option .= $cat->cat_name;
									$option .= ' ('.$cat->category_count.')';
									$option .= '</option>';
									echo $option;
								}
							}
							?>
						</select></td>
					</tr>
					<tr valign="top" class="post-extra bkadmin">
						<th scope="row"><div class="bktitle-admin"><i class="fa fa-pencil-square"></i> <?php _e( 'Post Selection:',  'bknewsticker' ); ?></div></th>
						<td><input type="radio" name="<?php echo $type_opt_name; ?>" value='commented' <?php if($type_opt_val=='commented') { echo 'checked'; } ?>>
							<?php _e( 'Most Commented Posts',  'bknewsticker' ); ?><br/>
							<input type="radio" name="<?php echo $type_opt_name; ?>" value='recent' <?php if($type_opt_val=='recent') { echo 'checked'; } ?>>
							<?php _e( 'Recent Posts',  'bknewsticker' ); ?><br/>
							<input type="radio" name="<?php echo $type_opt_name; ?>" value='recent-comments' <?php if($type_opt_val=='recent-comments') { echo 'checked'; } ?>>
							<?php _e( 'Recent Comments',  'bknewsticker' ); ?><br/>
									   </tr>
				<tr class="headercs" valign="top"><th scope="row"><div class="bktitle-csetup"><i class="fa fa-cogs"></i> <?php _e( 'Ticker Setup',  'bknewsticker' ); ?></div></th></tr>

						<tr valign="top" class="post-extra bkadmin">
							<th scope="row"><div class="bktitle-admin"><?php _e( 'Contents:',  'bknewsticker' ); ?></div></th>
							<td>
								<input type="checkbox" name="<?php echo $dates_opt_name; ?>" <?php if (isset($dates_opt_name)) { echo 'value="checked"'; }?> <?php if($dates_opt_val=='checked') { echo 'checked'; } ?>>
								<?php _e( 'Show Dates',  'bknewsticker' ); ?><br/>
								<input type="checkbox" name="<?php echo $content_opt_name; ?>" <?php if (isset($content_opt_name)) { echo 'value="checked"'; }?> <?php if($content_opt_val=='checked') { echo 'checked'; } ?>>
								<?php _e( 'Show Excerpts',  'bknewsticker' ); ?><br/>
							</td>
						</tr>

						<tr valign="top" class="bkpost-extra">
							<th scope="row" ><div class="bktitle-admin-two"><i class="fa fa-dot-circle-o"></i> <?php _e( 'Number of Posts:',  'bknewsticker' ); ?></div></th>
							<td><input type="text" name="<?php echo $num_posts_opt_name; ?>" value="<?php echo $num_posts_opt_val; ?>" size="2">
								posts </td>
							</tr>
						<tr valign="top" class="bkpost-extra">
							<th scope="row" ><div class="bktitle-admin-two"><i class="fa fa-dot-circle-o"></i> <?php _e( 'Text size:',  'bknewsticker' ); ?></div></th>
							<td><input type="text" name="<?php echo $bk_textall_size_name; ?>" value="<?php echo $bk_textall_size_val; ?>" size="2">
								px  ( Maxim Value 25) Example : 12 </td>
							</tr>	
						<tr valign="top" class="post-extra bkadmin">
							<th scope="row" ><div class="bktitle-admin"><i class="fa fa-tint"></i> <?php _e( 'Border bottom size:',  'bknewsticker' ); ?></div></th>
							<td><input type="text" name="<?php echo $border_bk_size; ?>" value="<?php echo $border_bk_size_val; ?>" size="2">
								px </td>
							</tr>

						<tr valign="top" class="bkpost-extra">
							<th scope="row" ><div class="bktitle-admin-two"><i class="fa fa-th"></i> <?php _e( 'Newsticker Title:',  'bknewsticker' ); ?></div></th>
							<td><input type="text" name="<?php echo $tiker_bk_title; ?>" value="<?php echo $tiker_bk_title_val; ?>" size="20">
								Add title for newsticker </td>
							</tr>
							<tr valign="top" class="bkadmin">
								<th scope="row"><div class="bktitle-admin"><i class="fa fa-pencil"></i> <?php _e( 'Ticker Length:',  'bknewsticker' ); ?></div></th>
								<td><?php _e( 'Enter how many characters long the auto-excerpt should be.',  'bknewsticker' ); ?><br />
									<?php _e( 'First',  'bknewsticker' ); ?>
									<input type="text" name="<?php echo $auto_excerpt_length_opt_name; ?>" value="<?php echo $auto_excerpt_length_opt_val; ?>" size="3">
									<?php _e( 'characters',  'bknewsticker' ); ?> </td>
								</tr>
						<tr valign="top" class="bkpost-extra">
							<th scope="row"><div class="bktitle-admin-two"><i class="fa fa-eye"></i> <?php _e( 'News Ticker Color:',  'bknewsticker' ); ?></div></th>
							<td><?php _e( 'Background ticker:',  'bknewsticker' ); ?><br/>
								<input type="text"  class="bk-color-field" name="<?php echo $background_bk_news; ?>" value="<?php echo $background_bk_news_val; ?>" size="20">
								<br/>
								<?php _e( 'Border bottom color:',  'bknewsticker' ); ?><br/>
								<input type="text"  class="bk-color-field" name="<?php echo $color_bk_border; ?>" value="<?php echo $color_bk_border_val; ?>" size="20">
								<br/>
								<?php _e( 'Excerpt Color:',  'bknewsticker' ); ?><br/>
								<input type="text" class="bk-color-field" name="<?php echo $ticker_bk_excerpt; ?>" value="<?php echo $ticker_bk_excerpt_val; ?>" size="20">
								<br/>
								<?php _e( 'Background Title:',  'bknewsticker' ); ?><br/>
								<input type="text"class="bk-color-field" name="<?php echo $ticker_bk_bktitle; ?>" value="<?php echo $ticker_bk_bktitle_val; ?>" size="20">
								<br/>
								<?php _e( 'Color Title:',  'bknewsticker' ); ?><br/>
								<input type="text"  class="bk-color-field" name="<?php echo $ticker_bk_title; ?>" value="<?php echo $ticker_bk_title_val; ?>" size="20">
								<br/>
								<?php _e( 'Color Link:',  'bknewsticker' ); ?><br/>
								<input type="text"  class="bk-color-field" name="<?php echo $ticker_bk_link; ?>" value="<?php echo $ticker_bk_link_val; ?>" size="20">
								<br/>
							</td>
						</tr>
									<tr valign="top" class="bkadmin">
									<th scope="row"><div class="bktitle-admin"><i class="fa fa-tachometer"></i> <?php _e( 'Ticker Speed:',  'bknewsticker' ); ?></div></th>
									<td><?php _e( 'The speed of ticker transition',  'bknewsticker' ); ?>.<br />
										<select name="<?php echo $ticker_speed_opt_name; ?>" value="<?php echo $ticker_speed_opt_val; ?>">
											<?php for($j="1"; $j<="10";$j++){
												if($j==$ticker_speed_opt_val){
													echo "<option value='$j' selected='selected'>$j</option>";
												}else{
													echo "<option value='$j'>$j</option>";
												}
											}?>
										</select> <?php _e( '(Seconds)',  'bknewsticker' ); ?></td>
									</tr>
									<tr valign="top" class="bkpost-extra">
										<th scope="row"><div class="bktitle-admin-two"><i class="fa  fa-exchange"></i> <?php _e( 'Ticker Timeout:',  'bknewsticker' ); ?></div></th>
										<td><?php _e( 'The time between ticker transitions',  'bknewsticker' ); ?>.<br />
											<select name="<?php echo $ticker_timeout_opt_name; ?>" value="<?php echo $ticker_timeout_opt_val; ?>">
												<?php for($i="1"; $i<="10";$i++){
													if($i==$ticker_timeout_opt_val){
														echo "<option value='$i' selected='selected'>$i</option>";
													}else{
														echo "<option value='$i'>$i</option>";
													}
												}?>
											</select> <?php _e( '(Seconds)',  'bknewsticker' ); ?></td>
										</tr>
										<tr valign="top" class="bkadmin">
											<th scope="row"><div class="bktitle-admin"><i class="fa fa-refresh fa-spin"></i> <?php _e( 'Ticker Animation:',  'bknewsticker' ); ?></div></th>
											<td> <?php _e( 'Select the ticker animation',  'bknewsticker' ); ?>.<br />
												<select name="<?php echo $ticker_anim_opt_name; ?>" value="<?php echo $ticker_anim_opt_val; ?>">
													<option value="fade" <?php if($ticker_anim_opt_val=='fade'){echo "selected='selected'";} ?>><?php _e( 'Fade',  'bknewsticker' ); ?></option>
													<option value="fadeZoom" <?php if($ticker_anim_opt_val=='fadeZoom'){echo "selected='selected'";} ?>><?php _e( 'Fade and Expand',  'bknewsticker' ); ?></option>
													<option value="scrollUp" <?php if($ticker_anim_opt_val=='scrollUp'){echo "selected='selected'";} ?>><?php _e( 'Scroll up',  'bknewsticker' ); ?></option>
													<option value="scrollDown" <?php if($ticker_anim_opt_val=='scrollDown'){echo "selected='selected'";} ?>><?php _e( 'Scroll down',  'bknewsticker' ); ?></option>
													<option value="scrollLeft" <?php if($ticker_anim_opt_val=='scrollLeft'){echo "selected='selected'";} ?>><?php _e( 'Scroll left',  'bknewsticker' ); ?></option>
													<option value="scrollRight" <?php if($ticker_anim_opt_val=='scrollRight'){echo "selected='selected'";} ?>><?php _e( 'Scroll right',  'bknewsticker' ); ?></option>
													<option value="zoom" <?php if($ticker_anim_opt_val=='zoom'){echo "selected='selected'";} ?>><?php _e( 'Zoom',  'bknewsticker' ); ?></option>
													<option value="uncover" <?php if($ticker_anim_opt_val=='uncover'){echo "selected='selected'";} ?>><?php _e( 'Uncover',  'bknewsticker' ); ?></option>
													<option value="turnRight" <?php if($ticker_anim_opt_val=='turnRight'){echo "selected='selected'";} ?>><?php _e( 'Turn Right',  'bknewsticker' ); ?></option>
													<option value="turnLeft" <?php if($ticker_anim_opt_val=='turnLeft'){echo "selected='selected'";} ?>><?php _e( 'Turn Left',  'bknewsticker' ); ?></option>
													<option value="turnUp" <?php if($ticker_anim_opt_val=='turnUp'){echo "selected='selected'";} ?>><?php _e( 'Turn Up',  'bknewsticker' ); ?></option>
													<option value="turnDown" <?php if($ticker_anim_opt_val=='turnDown'){echo "selected='selected'";} ?>><?php _e( 'Turn Down',  'bknewsticker' ); ?></option>
													<option value="slideY" <?php if($ticker_anim_opt_val=='slideY'){echo "selected='selected'";} ?>><?php _e( 'Slide Y',  'bknewsticker' ); ?></option>
													<option value="slideX" <?php if($ticker_anim_opt_val=='slideX'){echo "selected='selected'";} ?>><?php _e( 'Slide X',  'bknewsticker' ); ?></option>
													<option value="none" <?php if($ticker_anim_opt_val=='none'){echo "selected='selected'";} ?>><?php _e( 'None',  'bknewsticker' ); ?></option>
												</select></td>
											</tr>
											<tr valign="top" class="bkpost-extra">
										<th scope="row"><div class="bktitle-admin-two"><i class="fa fa-info-circle"> <?php _e( 'Integration:',  'bknewsticker' ); ?></div></th>
										<td><?php _e( 'Paste the PHP code to your desired template location: Copy and past the below mentioned code to your desired template location.',  'bknewsticker' ); ?><br/>
										 <code><?php echo htmlspecialchars( sprintf( "<?php if ( function_exists('insert_bknewsticker') ) { insert_bknewsticker(); } ?>") ) ?></code>

											<br />
											</td>
										</tr>
											</table>
										<hr />
										<p class="submit">
											<input type="submit" name="Submit" value="<?php _e('Update Options', 'bknewsticker' ) ?>" />
										</p>
									</form>
								</div>
								<?php
							}
							function ticker_get_plugin_root() {
								return dirname(__FILE__).'/';
							}
							function ticker_get_plugin_web_root(){
								$site_url = get_option('siteurl');

								$pos = ticker_strpos_nth(3, $site_url, '/');
								$plugin_root = ticker_get_plugin_root();
	
	$plugin_dir_name = substr($plugin_root, strrpos(substr($plugin_root, 0, strlen($plugin_root)-2), DIRECTORY_SEPARATOR)+1); //-2 to skip the trailing '/' on $plugin_root
	if($pos===false)
		$web_root = substr($site_url, strlen($site_url));
	else
		$web_root = '/' . substr($site_url, $pos);
	if($web_root[strlen($web_root)-1]!='/')
		$web_root .= '/';
	$web_root .= 'wp-content/plugins/' . $plugin_dir_name;
	return $web_root;
}
function ticker_strpos_nth($n, $haystack, $needle, $offset=0){
	$needle_len = strlen($needle);
	$hits = 0;
	while($hits!=$n) {
		$offset = strpos($haystack, $needle, $offset);
		if($offset===false)
			return false;
		$offset += $needle_len;
		$hits++;
	}
	return $offset;
}
function ticker_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$igr_links = '<a href="'.get_admin_url().'options-general.php?page=tickeroptions">'._e( 'Settings',  'bknewsticker' ).'</a>';
		array_unshift( $links, $igr_links );
	}
	return $links;
}
?>