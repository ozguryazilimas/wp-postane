<?php
/*
Plugin Name: YARPP Experiments
Plugin URI: http://yarpp.org/
Description: Some extras for tuning and diagnosing YARPP.
Version: 1.1
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: http://tinyurl.com/donatetomitcho
*/

define( 'YARPP_EXPERIMENTS_VERSION', '1.1' );
function yarpp_experiments_version( $html ) {
	return $html . ' with YARPP Experiments ' . YARPP_EXPERIMENTS_VERSION;
}
add_filter( 'yarpp_version_html', 'yarpp_experiments_version' );

function yarpp_experiments_version_check() {
	global $current_screen;
	if ( !defined('YARPP_VERSION') ) {
		echo '<div class="updated"><p>YARPP Experiments will not do anything unless you have <a href="http://wordpress.org/extend/plugins/yet-another-related-posts-plugin/">YARPP</a> installed.</p></div>';
	} else if (is_object($current_screen) &&
		$current_screen->id == 'settings_page_yarpp') {
		
		if ( version_compare(YARPP_VERSION, '3.4b3') < 0 ) {
			echo '<div class="updated"><p>The Throttle feature of YARPP Experiments will not work without <a href="https://wordpress.org/extend/plugins/yet-another-related-posts-plugin/developers/">YARPP 3.4b3 or later</a> installed.</p></div>';
		} 
	
		if ( version_compare(YARPP_VERSION, '3.5.4b2') < 0 ) {
			echo '<div class="updated"><p>The Cache Statistics feature of YARPP Experiments will not work without <a href="https://wordpress.org/extend/plugins/yet-another-related-posts-plugin/developers/">YARPP 3.5.4b2 or later</a> installed.</p></div>';
		}
	}
}
add_action( 'admin_notices', 'yarpp_experiments_version_check' );

function add_yarpp_experiments_meta_boxes() {

	$experiment_label = ' <span class="experiment-label" style="-moz-transform:rotate(%1$ddeg); transform:rotate(%1$ddeg); -webkit-transform:rotate(%1$ddeg);">EXPERIMENT</span>';

	class YARPP_Meta_Box_Cache_Status extends YARPP_Meta_Box {
		function display() {
			global $wpdb, $yarpp;
						
			echo '<p><em>This panel computes some statistics on YARPP\'s cache usage and gives you some manual controls. In the future I may add a "send anonymous usage and settings data to help improve YARPP" to YARPP proper which would send some data like this. <a href="mailto:mitcho@mitcho.com">Comments welcome</a>.</em></p>';
			
			$status = $yarpp->cache->cache_status();
			echo "<p><strong>Cached:</strong> <span id='yarpp-percentage'>" . $status * 100 . "</span>%";
			if ( $status < 1 )
				echo " <input type='button' class='button' id='build-cache-button' value='Build cache now'/>";
			if ( $status > 0 ) {
				wp_nonce_field( 'yarpp_cache_flush', 'yarpp_cache_flush-nonce', false );
				echo " <input type='button' class='button delete' id='flush-cache-button' value='Flush'/>";
			}
			echo "</p>";
			
?>
	<div id='build-display' style='display:none;'>
		<div class="progress-container" style='border: 1px solid #ccc; width: 200px; margin: 2px 5px 2px 0; padding: 1px; background: white;'>
			<div id='yarpp-bar' style="width: 0%; height: 12px; background-color: #D7D7D7;">&nbsp;</div>
		</div>
		<p style='font-size: .8em' id='yarpp-latest'>starting...'</p>
		<p style='font-size: .8em; padding-left: 20px; background: url(<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>) no-repeat left top;' id='yarpp-time'></p>
	</div>
<?php

			echo "<p><strong>Cache type:</strong> {$yarpp->cache->name}</p>";
			
			if ( method_exists($yarpp->cache, 'stats') ) {
				$stats = $yarpp->cache->stats();
				if ( count($stats) && array_sum( $stats ) > 0 ) {
					echo "<h4>Number related: </h4>";
				
					$sum = array_sum(array_map('array_product', array_map(null, array_values($stats), array_keys($stats))));
					$avg = $sum / array_sum( $stats );

					echo "<table>";
					echo '<tr><th>' . join('</th><th>', array_keys($stats)) . '</th><th>Avg</th></tr>';
					echo '<tr><td>' . join('</td><td>', array_values($stats)) . '</td><td>' . round($avg,2) . '</td></tr>';
					echo "</table>";
				}
			}
			
			if ( 'postmeta' == $yarpp->cache->name ) {
				echo "<p><strong>Thank you for trying the postmeta cache!</strong> Unfortunately there's no good way to do statisitics over the postmeta cache. If you have comments on the postmeta cache, including any performance issues you may have noticed, please <a href='mailto:mitcho@mitcho.com'>contact me</a>.</p>";
			}

			if ( 'custom tables' == $yarpp->cache->name ) {
				$dates = $wpdb->get_results("select count(distinct `reference_ID`) as ct, date(date) as date from {$wpdb->prefix}yarpp_related_cache group by date(date) order by date desc");
				if ( !is_null($dates) && count($dates) ) {
					echo "<h4>Generation dates: </h4>";
					$table = array();
					foreach ($dates as $row) {
						$table[$row->date] = $row->ct;
					}
					echo "<div style='overflow:auto;width:100%'><table>";
					echo '<tr><th>' . join('</th><th>', array_keys($table)) . '</th></tr>';
					echo '<tr><td>' . join('</td><td>', array_values($table)) . '</td></tr>';
					echo "</table></div>";
				}
	
				$score = $wpdb->get_row("select avg(score) as avg, stddev(score) as sd from {$wpdb->prefix}yarpp_related_cache where ID != 0");
				echo "<p><strong>Score</strong> (among those related): avg " . round($score->avg, 3) . ', sd ' . round($score->sd, 3) . "</p>";
			}
		}
	}
	add_meta_box('yarpp_cache_status', 'Cache Status' . sprintf($experiment_label, mt_rand(-4,4)), array(new YARPP_Meta_Box_Cache_Status, 'display'), 'settings_page_yarpp', 'normal', 'core');
	
	class YARPP_Meta_Box_Throttle extends YARPP_Meta_Box {
		function display() {
			global $wpdb, $yarpp;
			
			echo '<p><em>This "Throttle" control lets you slow down YARPP\'s computation of "related" results when not cached. It may be useful for very high traffic sites, where suddenly turning YARPP on may cause some database lockups. When computation is thwarted using this throttle, there will simply be no related posts output, but the HTML comment <code>&lt;!--You got throttled!--&gt;</code> will be printed. <a href="mailto:mitcho@mitcho.com">Comments welcome</a>.</em></p>';
			
			$throttle = (int) yarpp_get_option('experiment_throttle');
			
			echo "<div style='overflow:auto;'><input type='range' value='{$throttle}' name='throttle' id='throttle' min='0' max='12' step='1'/></div>";
			echo "<p>When non-cached results are requested, compute results <span id='throttle_percent'></span>.</p>";
		}
	}
	add_meta_box('yarpp_throttle', 'Throttle' . sprintf($experiment_label, mt_rand(-4,4)), array(new YARPP_Meta_Box_Throttle, 'display'), 'settings_page_yarpp', 'normal', 'core');
		

	class YARPP_Meta_Box_Dingus extends YARPP_Meta_Box {
		function display() {
			global $wpdb, $yarpp;
			
			echo '<script>
			jQuery(function ($) {
				$("#yarpp_dingus_submit").click(function() {
					$("#yarpp_dingus_results").load(ajaxurl, "action=yarpp_dingus&" + $("#yarpp_dingus_form").find("input, textarea, select").serialize());
				});
			});
			</script>
			<style>#yarpp_dingus .inside {overflow:auto}
			#yarpp_dingus ol li {list-style: decimal}</style>';
			
			echo '<div style="width: 200px; float:right; margin-left: 10px;" id="yarpp_dingus_results"></div>';
			
			echo '<p><em>This "Dingus" lets you try different YARPP settings and test the results. Note the cache status: if it returns "bypass" YARPP\'s cache is not being used as the parameters are different enough from what the cache is based on. <a href="mailto:mitcho@mitcho.com">Comments welcome</a>.</em></p>';
						
			echo '<div id="yarpp_dingus_form"><pre>yarpp_get_related(<input type="number" size="12" name="yarpp_dingus[reference_ID]"/>,array(';
			
			$options = array(
				'limit' => 1,
				'threshold' => 1,
				'show_pass_post' => true,
				'past_only' => true,
				//'weight', 'exclude',
				'recent_only' => true,
				'recent_number' => 1,
				'recent_units' => array('day', 'week', 'month'),
				'order' => array('score DESC', 'score ASC', 'post_date DESC', 'post_date ASC', 'post_title ASC', 'post_title DESC')
			);
			
			foreach ( $options as $option => $desc ) {
				$current = yarpp_get_option($option);
				if ( is_array($desc) ) {
					echo "\n\t{$option} => <select name='yarpp_dingus[args][{$option}]'>";
					foreach ($desc as $val)
						echo "<option value='{$val}'" . selected($val, $current, false) . ">$val</option>";
					echo "</select>";
					continue;
				}

				if ( is_bool($desc) ) {
					echo "\n\t{$option} => <select name='yarpp_dingus[args][{$option}]'>";
					echo "<option val='true'" . selected(true, $current, false) . ">true</option>";
					echo "<option val='false'" . selected(false, $current, false) . ">false</option>";
					echo "</select>";
					continue;
				}

				echo "\n\t{$option} => <input name='yarpp_dingus[args][{$option}]'";
				if ( is_int($desc) )
					echo " type='number' size='12'";
				echo " value='" . esc_attr($current) . "'";
				echo "/>";
			}
			
			echo '))</pre></div>';
			echo '<input type="button" id="yarpp_dingus_submit" class="button" value="Relate!"/>';
		}
	}
	add_meta_box('yarpp_dingus', 'Dingus' . sprintf($experiment_label, mt_rand(-4,4)), array(new YARPP_Meta_Box_Dingus, 'display'), 'settings_page_yarpp', 'normal', 'core');
	
	class YARPP_Meta_Box_Pingbacks extends YARPP_Meta_Box {
		function display() {
			$pingback = yarpp_get_option('experiment_pingback');
			
			echo "<div><label for='yarpp_experiment_pingback'><input type='checkbox' " . checked($pingback, true, false) . " name='pingback' id='yarpp_experiment_pingback'/> Avoid pingbacks to own site</label></div>";
		}
	}
	add_meta_box('yarpp_pingback', 'Pingbacks' . sprintf($experiment_label, mt_rand(-4,4)), array(new YARPP_Meta_Box_Pingbacks, 'display'), 'settings_page_yarpp', 'normal', 'core');

	class YARPP_Meta_Box_Thumbnails extends YARPP_Meta_Box {
		function display() {
			$pingback = yarpp_get_option('manually_using_thumbnails');
			
			echo "<div><label for='yarpp_manually_using_thumbnails'><input type='checkbox' " . checked($pingback, true, false) . " name='manually_using_thumbnails' id='yarpp_manually_using_thumbnails'/> Generate thumbnails for manual thumbnail template use (for example, using the widget or via the API)</label></div>";
		}
	}
	add_meta_box('yarpp_thumbnails', 'Thumbnails' . sprintf($experiment_label, mt_rand(-4,4)), array(new YARPP_Meta_Box_Thumbnails, 'display'), 'settings_page_yarpp', 'normal', 'core');
}
add_action( 'add_meta_boxes_settings_page_yarpp', 'add_yarpp_experiments_meta_boxes' );

function yarpp_experiment_throttle_save( $options ) {
	if ( version_compare(YARPP_VERSION, '3.4b3') >= 0 && isset($_POST['throttle']) )
		$options['experiment_throttle'] = absint($_POST['throttle']);
	return $options;
}
add_filter( 'yarpp_settings_save', 'yarpp_experiment_throttle_save', 10, 1 );

function yarpp_experiment_pingback_save( $options ) {
	if ( version_compare(YARPP_VERSION, '4.0.4') >= 0 )
		$options['experiment_pingback'] = isset($_POST['pingback']);
	return $options;
}
add_filter( 'yarpp_settings_save', 'yarpp_experiment_pingback_save', 10, 1 );

function yarpp_manually_using_thumbnails_save( $options ) {
	if ( version_compare(YARPP_VERSION, '4.0.6b3') >= 0 )
		$options['manually_using_thumbnails'] = isset($_POST['manually_using_thumbnails']);
	return $options;
}
add_filter( 'yarpp_settings_save', 'yarpp_manually_using_thumbnails_save', 10, 1 );

function yarpp_experiments_admin_enqueue() {
	global $current_screen;
	if ( !is_object($current_screen) ||
		$current_screen->id != 'settings_page_yarpp' )
		return;

	wp_enqueue_style( 'yarpp-experiments', plugins_url('experiments.css', __FILE__), array(), YARPP_EXPERIMENTS_VERSION );
		
	wp_enqueue_script( 'jquery-range', plugins_url('jquery.range.js', __FILE__), array('jquery'), YARPP_EXPERIMENTS_VERSION );
	wp_enqueue_script( 'yarpp-experiments-status', plugins_url('status.js', __FILE__), array('jquery-range'), YARPP_EXPERIMENTS_VERSION );
	wp_enqueue_script( 'yarpp-experiments-throttle', plugins_url('throttle.js', __FILE__), array('jquery-range'), YARPP_EXPERIMENTS_VERSION );
	wp_enqueue_style( 'yarpp-experiments-throttle', plugins_url('throttle.css', __FILE__), array(), YARPP_EXPERIMENTS_VERSION );
}
add_action( 'admin_enqueue_scripts', 'yarpp_experiments_admin_enqueue' );

function yarpp_experiment_throttle( $cache_status, $ID ) {
	// only mess with things which are not yet cached:
	if ( YARPP_NOT_CACHED != $cache_status )
		return $cache_status;
		
	// Don't throttle computations triggered via ajax.
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return $cache_status;

	// $raw_throttle must be non-negative. Default value is 0.
	$raw_throttle = (int) yarpp_get_option( 'experiment_throttle' );
	$throttle = max( 0, $raw_throttle );
	
	if ( $throttle !== $raw_throttle )
		yarpp_set_option( 'experiment_throttle', $throttle );
	
	// so, 1 / 10^{throttle/2} of the time, we return YARPP_NOT_CACHED.
	// otherwise, we send the kill signal, YARPP_DONT_RUN, and echo a comment.
	if ( 1 == mt_rand( 1, pow(10, $throttle / 4) ) )
		return YARPP_NOT_CACHED;
	
	if ( headers_sent() )
		echo "<!--You got throttled!-->";
	return YARPP_DONT_RUN;
}
add_filter( 'yarpp_cache_enforce_status', 'yarpp_experiment_throttle', 10, 2 );

function yarpp_build_cache() {
	global $wpdb, $yarpp;
	if (!is_user_logged_in() || !current_user_can('manage_options')) {
		wp_die(__('You cannot rebuild the YARPP cache.', 'yarpp'));
	}

	$atATime = 30;
	if ( !isset($_POST['i']) || !$_POST['i'] ) {
		$i = 0;
		$uncached = $yarpp->cache->uncached($atATime, 0);
		$m = $wpdb->get_var("select found_rows()");
	} else {
		$i = $_POST['i'];
		$uncached = $yarpp->cache->uncached($atATime, $i);
		$m = $_POST['m'];
	}

	if ( !count($uncached) ) {
		header('Content-Type: application/json');
		echo json_encode(array(
			'result' => 'premature',
			'i' => $i,
			'm' => $m,
			'percent' => floor(1000 * $i/$m)/10,
			'status' => $yarpp->cache->cache_status()
		));
		exit;
	}

	foreach ($uncached as $id) {
		$result = $yarpp->cache->enforce((int) $id, true);

		if ( $result != YARPP_RELATED && $result != YARPP_NO_RELATED ) {
			$title = get_the_title($id);
			header('Content-Type: application/json');
			echo json_encode(array(
				'result' => 'error',
				'result_code' => $result,
				'title' => $title,
				'id' => $id,
				'i' => $i,
				'm' => $m,
				'percent' => floor(1000 * $i/$m)/10,
				'status' => $yarpp->cache->cache_status(),
				'last_sql' => ( isset($yarpp->cache->last_sql) ? $yarpp->cache->last_sql : '' )
			));
			exit;
		}

		$i++;
	}

	header('Content-Type: application/json');
	echo json_encode(array(
		'result' => 'success',
		'id' => $id,
		'i' => $i,
		'm' => $m,
		'percent' => floor(1000 * $i/$m)/10,
		'status' => $yarpp->cache->cache_status(),
		'last_sql' => ( isset($yarpp->cache->last_sql) ? $yarpp->cache->last_sql : '' )
	));
	exit;
}
add_action('wp_ajax_yarpp_build_cache', 'yarpp_build_cache');

function yarpp_notempty($x) {
	return !empty($x);
}
function yarpp_dingus_compute() {
	global $yarpp, $wpdb;
	if ( !isset($_REQUEST['yarpp_dingus']) )
		return;
	
	$start = microtime(true);
	
	$yarpp_dingus = $_REQUEST['yarpp_dingus'];
	$args = array_filter($yarpp_dingus['args'], 'yarpp_notempty');
	$boolean_options = array( 'show_pass_post', 'past_only', 'recent_only' );
	foreach ($boolean_options as $option) {
		if ( isset($args[$option]) )
			$args[$option] = $args[$option] == 'true';
	}
	//var_dump($args);

	if (empty($yarpp_dingus['reference_ID'])) {
		echo "The first argument, <code>reference_ID</code>, is required.";
		exit;
	}
	
	add_action('pre_get_posts', 'yarpp_dingus_print_cache_type', 100, 1);
	$posts = $yarpp->get_related($yarpp_dingus['reference_ID'], $args);
	remove_action('pre_get_posts', 'yarpp_dingus_print_cache_type');
	
	echo "<p>" . round(microtime(true) - $start, 5) . " seconds</p>";
	echo "<ol>";
	foreach ( $posts as $post )
		echo "<li><a href='" . esc_url(get_permalink($post->ID)) . "'>" . get_the_title($post->ID) . "</a> ({$post->score})</li>";
	echo "</ol>";
	exit;
}
add_action('wp_ajax_yarpp_dingus', 'yarpp_dingus_compute');

function yarpp_dingus_print_cache_type( $query ) {
	if (isset($query->yarpp_cache_type))
		echo "cache type: " . $query->yarpp_cache_type;
}

function yarpp_pingback_maybe_block_pingback($url, $post, $leavename) {
	global $yarpp;
	if ( is_feed() ||
		!is_object($yarpp) ||
		!yarpp_get_option('experiment_pingback') ||
		!$yarpp->cache->is_yarpp_time() )
		return $url;

	$home = parse_url( home_url() );
	$replacepart = $home['scheme'] . '://' . $home['host'];
	if ( isset($home['port']) )
		$replacepart .= ':' . $home['port'];
	$url = preg_replace('!^' . preg_quote($replacepart) . '!', '', $url);
	
	return $url;
}
add_filter( 'post_link', 'yarpp_pingback_maybe_block_pingback', 10, 3 );

function yarpp_graph_data() {
//	check_ajax_referer( 'yarpp_graph_data' );
	global $yarpp;

	header('Content-Type: application/json');

	$threshold = 5;
	if ( isset($_GET['threshold']) )
		$threshold = $_GET['threshold'];

	if ( method_exists($yarpp->cache, 'graph_data') ) {
		$data = $yarpp->cache->graph_data( $threshold );
		
		$nodes = array();
		$index = 0;
		foreach ( $data as $pair ) {
			list($a, $b) = explode('-', $pair->pair);

			if ( !isset($nodes[$a]) ) {
				$nodes[$a] = array( 'index' => $index, 'ID' => $a, 'link' => get_permalink($a), 'title' => get_the_title( $a ) );
				$index++;
			}
			if ( !isset($nodes[$b]) ) {
				$nodes[$b] = array( 'index' => $index, 'ID' => $b, 'link' => get_permalink($b), 'title' => get_the_title( $b ) );
				$index++;
			}
		}
		
		$links = array();
		foreach ( $data as $pair ) {
			list($a, $b) = explode('-', $pair->pair);

			$links[] = array( 'source' => $nodes[$a]['index'], 'target' => $nodes[$b]['index'], 'score' => (float) $pair->score );
		}

		$nodes = array_values($nodes);
		echo json_encode( compact('nodes', 'links'), JSON_UNESCAPED_UNICODE );
	}
	else echo '[]';
	
	exit;
}
add_action('wp_ajax_yarpp_graph_data', 'yarpp_graph_data');
