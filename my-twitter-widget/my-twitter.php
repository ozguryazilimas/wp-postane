<?php
/**
 * Plugin Name: My Twitter Widget
 * Plugin URI: http://www.pooks.com/
 * Description: The absolute best <strong>twitter feed sidebar widget</strong> for Wordpress yet. Easy to use, install, setup and comes with several options to control how it looks on your wordpress website. Download and install this <strong>twitter widget</strong> and see just how great it is and how easy it is to use. Full support and even help installing it are avilable upon request. 
 * Author: Jack Higgins
 * Version: 1.3.4
 * Author URI: http://pooks.com
 * License: GPLv2 or later 
 */
/*  Copyright 2010  Jack HIggins (info@pooks.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
	
    **********************************************************************
	
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    **********************************************************************

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('MY_VERSION', '1.0');

define('MY_PLUGINBASENAME', dirname(plugin_basename(__FILE__)));

define('MY_PLUGINPATH', PLUGINDIR . '/' . MY_PLUGINBASENAME);

class My_Twitter_Widget extends WP_Widget {

	function My_Twitter_Widget() {
	
		if(function_exists('load_plugin_textdomain')) {
			load_plugin_textdomain('my_tw', MY_TW_PLUGINPATH . '/languages', MY_PLUGINBASENAME . '/languages');
		}

		$widget_ops = array(
			'classname' => 'my_twitter_widget',
			'description' => __('List your last tweet by displaying content, date, and link to follow you', 'my_tw')
		);

		$control_ops = array();

		$this->WP_Widget('my_twitter_widget', __('My Twitter', 'my_tw'), $widget_ops, $control_ops);
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array(
			'my_twitter_title' => '',
			
			'my_twitter_username' => '',
			
			'my_twitter_no_tweets' => '1',
			
			'my_twitter_show_avatar' => false,
			
			'my_twitter_cache_duration' => 0,
			
			'my_twitter_default_css' => false
		));
		
		$default_css_checked = ' checked="checked"';
		
		if ( $instance['my_twitter_default_css'] == false )
			$default_css_checked = '';
			
		$show_avatar_checked = ' checked="checked"';
		
		if ( $instance['my_twitter_show_avatar'] == false )
			$show_avatar_checked = '';
			
		// Version of the plugin (hidden field)
		$jzoutput  = '<input id="' . $this->get_field_id('plugin-version') . '" name="' . $this->get_field_name('plugin-version') . '" type="hidden" value="' . MY_TW_VERSION . '" />';

		// Title
		$jzoutput .= '
			<p style="border-bottom: 1px solid #DFDFDF;">
			
				<label for="' . $this->get_field_id('my_twitter_title') . '"><strong>' . __('Title', 'my_tw') . '</strong></label>
				
			</p>
			<p>
			
				<input id="' . $this->get_field_id('my_twitter_title') . '" name="' . $this->get_field_name('my_twitter_title') . '" type="text" value="' . $instance['my_twitter_title'] . '" />
				
			</p>
		';

		// Settings
		$jzoutput .= '
			<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . __('Preferences', 'my_tw') . '</strong></p>
	
			<p>
				<label>' . __('Username', 'my_tw') . '<br />
				
				<span style="color:#999;">@</span><input id="' . $this->get_field_id('my_twitter_username') . '" name="' . $this->get_field_name('my_twitter_username') . '" type="text" value="' . $instance['my_twitter_username'] . '" /> <abbr title="' . __('No @, just your username', 'my_tw') . '">(?)</abbr></label>
				
			</p>
			
			<p>
			
				<label>' . __('Number of tweets to show', 'my_tw') . '<br />
				
				<input style="margin-left: 1em;" id="' . $this->get_field_id('my_twitter_no_tweets') . '" name="' . $this->get_field_name('my_twitter_no_tweets') . '" type="text" value="' . $instance['my_twitter_no_tweets'] . '" /> <abbr title="' . __('Just a number, between 1 and 5 for example', 'my_tw') . '">(?)</abbr></label>
				
			</p>
			
			<p>
			
				<label>' . __('Duration of cache', 'my_tw') . '<br />
				
				<input style="margin-left: 1em; text-align:right;" id="' . $this->get_field_id('my_twitter_cache_duration') . '" name="' . $this->get_field_name('my_twitter_cache_duration') . '" type="text" size="10" value="' . $instance['my_twitter_cache_duration'] . '" /> '.__('Seconds', 'my_tw').' <abbr title="' . __('A big number save your page speed. Try to use the delay between each tweet you make. (e.g. 1800 s = 30 min)', 'my_tw') . '">(?)</abbr></label>
				
			</p>
			
			<p>
			
				<label>' . __('Show your avatar?', 'my_tw') . ' 
				<input type="checkbox" name="' . $this->get_field_name('my_twitter_show_avatar') . '" id="' . $this->get_field_id('my_twitter_show_avatar') . '"'.$show_avatar_checked.' /> <abbr title="' . __("If it's possible, display your avatar at the top of twitter list", 'my_tw') . '">(?)</abbr></label>
				
			</p>
			
		';
		
		// Default & Own CSS
		$jzoutput .= '
			<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . __('Manage CSS', 'my_tw') . '</strong></p>
			
			<p>
			
				<label>' . __('Use the default CSS?', 'my_tw') . ' 
				<input type="checkbox" name="' . $this->get_field_name('my_twitter_default_css') . '" id="' . $this->get_field_id('my_twitter_default_css') . '"'.$default_css_checked.' /> <abbr title="' . __('Load a little CSS file with default styles for the widget', 'my_tw') . '">(?)</abbr></label>
				
			</p>
			
			<p>
			
				<label for="' . $this->get_field_id('my-tw-own-css') . '" style="display:inline-block;">' . __('Your own CSS', 'my_tw') . ':  <abbr title="' . __('Write your CSS here to replace or overwrite the default CSS', 'my_tw') . '">(?)</abbr></label>
				
				<textarea id="' . $this->get_field_id('my-tw-own-css') . '" rows="7" cols="30" name="' . $this->get_field_name('my-tw-own-css') . '">' . $instance['my-tw-own-css'] . '</textarea>
				
			</p>
		';
		
		echo $jzoutput;
	}
	function update($new_instance, $old_instance) {	
		$instance = $old_instance;
		
		$new_instance = wp_parse_args((array) $new_instance, array(
			'my_twitter_title' => '',
			
			'my_twitter_username' => '',
			
			'my_twitter_no_tweets' => '1',
			
			'my_twitter_show_avatar' => false,
			
			'my_twitter_cache_duration' => 0,
			
			'my_twitter_default_css' => false
		));
		$instance['plugin-version'] = strip_tags($new_instance['my_twitter-version']);
		
		$instance['my_twitter_title'] = strip_tags($new_instance['my_twitter_title']);
		
		$instance['my_twitter_username'] = strip_tags($new_instance['my_twitter_username']);
		
		$instance['my_twitter_no_tweets'] = strip_tags($new_instance['my_twitter_no_tweets']);
		
		$instance['my_twitter_show_avatar'] = strip_tags($new_instance['my_twitter_show_avatar']);
		
		$instance['my_twitter_default_css'] = $new_instance['my_twitter_default_css'];
		
		$instance['my-tw-own-css'] = $new_instance['my-tw-own-css'];
		
		return $instance;
	}

	function widget($args, $instance) {
		extract($args);
		
		echo $before_widget;
		
		$title = (empty($instance['my_twitter_title'])) ? '' : apply_filters('widget_title', $instance['my_twitter_title']);
		
		if(!empty($title)) {
			echo $before_title . $title . $after_title;
			
		}

		echo $this->my_twitter_output($instance, 'widget');
		
		echo $after_widget;
	}

	function my_twitter_output($args = array(), $position) {	
		$the_username = $args['my_twitter_username'];
		
		$the_username = preg_replace('#^@(.+)#', '$1', $the_username);
		
		$the_nb_tweet = $args['my_twitter_no_tweets'];
		
		$need_cache = ($args['my_twitter_cache_duration']!='0') ? true : false;
		
		$show_avatar = ($args['my_twitter_show_avatar']) ? true : false;

		if ( !function_exists ('my_tw_filter_handler') ) {
			function my_tw_filter_handler ( $seconds ) {		
				// change the default feed cache recreation period to 2 hours			
				return intval($args['my_twitter_cache_duration']); //seconds
			}
		}
		
		add_filter( 'wp_feed_cache_transient_lifetime' , 'my_tw_filter_handler' ); 	 
		
			function jltw_format_since($date){
				
				$timestamp = strtotime($date);
				
				$the_date = '';
				
				$now = time();
				
				$diff = $now - $timestamp;
				
				if($diff < 60 ) {
				
					$the_date .= $diff.' ';
					
					$the_date .= ($diff > 1) ?  __('saniye', 'my_tw') :  __('saniye', 'my_tw');
				}
				elseif($diff < 3600 ) {
				
					$the_date .= round($diff/60).' ';
					
					$the_date .= (round($diff/60) > 1) ?  __('dakika', 'my_tw') :  __('dakika', 'my_tw');
				}
				elseif($diff < 86400 ) {
				
					$the_date .=  round($diff/3600).' ';
					
					$the_date .= (round($diff/3600) > 1) ?  __('saat', 'my_tw') :  __('saat', 'my_tw');
				}
				else {
				
					$the_date .=  round($diff/86400).' ';
					
					$the_date .= (round($diff/86400) > 1) ?  __('Days', 'my_tw') :  __('Day', 'my_tw');
				}
			
				return $the_date;
			}
			
			function jltw_format_tweettext($raw_tweet, $username) {

				$i_text = htmlspecialchars_decode($raw_tweet);
				
				/* $i_text = preg_replace('#(([a-zA-Z0-9_-]{1,130})\.([a-z]{2,4})(/[a-zA-Z0-9_-]+)?((\#)([a-zA-Z0-9_-]+))?)#','<a href="//$1">$1</a>',$i_text); */
				$i_text = preg_replace('#(((https?|ftp)://(w{3}\.)?)(?<!www)(\w+-?)*\.([a-z]{2,4})(/[a-zA-Z0-9_-]+)?)#',' <a href="$1" rel="nofollow" class="my_twitter_url">$5.$6$7</a>',$i_text);
				
				$i_text = preg_replace('#@([a-zA-z0-9_]+)#i','<a href="http://twitter.com/$1" class="my_twitter_tweetos" rel="nofollow">@$1</a>',$i_text);
				
				$i_text = preg_replace('#[^&]\#([a-zA-z0-9_]+)#i',' <a href="http://twitter.com/#!/search/%23$1" class="my_twitter_hastag" rel="nofollow">#$1</a>',$i_text);
				
				$i_text = preg_replace( '#^'.$username.': #i', '', $i_text );
				
				return $i_text;
			
			}
			
			function jltw_format_tweetsource($raw_source) {
			
				$i_source = htmlspecialchars_decode($raw_source);
				
				$i_source = preg_replace('#^web$#','<a href="http://twitter.com">Twitter</a>', $i_source);
				
				return $i_source;
			
			}
			
			function jltw_get_the_user_timeline($username, $nb_tweets, $show_avatar) {
				
				$username = (empty($username)) ? 'teamwebusa' : $username;
				
				$nb_tweets = (empty($nb_tweets) OR $nb_tweets == 0) ? 1 : $nb_tweets;
				
				$xml_result = $the_best_feed = '';
				
				// include of WP's feed functions
				include_once(ABSPATH . WPINC . '/feed.php');
				
				// some RSS feed with timeline user
				$search_feed1 = "http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=".$username."&count=".intval($nb_tweets);
				
				$search_feed2 = "http://search.twitter.com/search.rss?q=from%3A".$username."&rpp=".intval($nb_tweets);
				
				// get the better feed
				// try with the first one
				
				$sf_rss = fetch_feed ( $search_feed1 );
				
				if ( is_wp_error($sf_rss) ) {
					// if first one is not ok, try with the second one
					$sf_rss = fetch_feed ( $search_feed2 );
					
					if ( is_wp_error($sf_rss) ) $the_best_feed = false;
					else $the_best_feed = '2';
					
				}
				else $the_best_feed = '1';
				
				
				// if one of the rss is readable
				if ( $the_best_feed ) {
					$max_i = $sf_rss -> get_item_quantity($nb_tweets);
					
					$rss_i = $sf_rss -> get_items(0, $max_i);
					
					$i = 0;
					
					foreach ( $rss_i as $tweet ) {
						$i++;
						$i_title = jltw_format_tweettext($tweet -> get_title() , $username);
						
						$i_creat = jltw_format_since( $tweet -> get_date() );
						
						$i_guid = $tweet->get_link();
						
						$author_tag = $tweet->get_item_tags('','author');
						
						$author_a = $author_tag[0]['data'];
						
						$author = substr($author_a, 0, stripos($author_a, "@") );
						
						$source_tag = $tweet->get_item_tags('http://api.twitter.com','source');
						
						$i_source = $source_tag[0]['data'];
						
						$i_source = jltw_format_tweetsource($i_source);
						
						$i_source = ($i_source) ? '<span class="my_source">via ' . $i_source : '</span>';
						
						if ( $the_best_feed == '1' && $show_avatar) {
							$avatar = "http://api.twitter.com/1/users/profile_image/". $username .".xml?size=normal"; // or bigger
						}
						elseif ($the_best_feed == '2' && $show_avatar) {
							$avatar_tag = $tweet->get_item_tags('http://base.google.com/ns/1.0','image_link');
							
							$avatar = $avatar_tag[0]['data'];
						}
						
						$html_avatar = ($i==1 && $show_avatar && $avatar) ? '<span class="user_avatar">
						
						<a href="http://twitter.com/' . $username . '" title="' . __('Follow', 'my_tw') . ' @'.$author.' ' . __('on twitter.', 'my_tw') . '"><img src="'.$avatar.'" alt="'.$author.'" width="48" height="48" /></a></span>' : '';
						
						//echo $i_title.'<br />'.$i_creat.'<br />'.$link_tag.'<br />'.$author.'('.$avatar.')<br /><br />';
						
						$xml_result .= '
							<li>
							
								'.$html_avatar.'
								<span class="my_lt_content">' . $i_title . '</span>
								
								<em class="my_twitter_inner">
									<a href="'.$i_guid .'" target="_blank">' . $i_creat . '</a> önce
									
									'. $i_source .'
								</em>
							</li>
						';
					}
				}
				// if any feed is readable
				else 
					$xml_result = '<li><em>'.__('The RSS feed for this twitter account is not loadable for the moment.', 'my_tw').'</em></li>';

				return $xml_result;
			}
			
			// display the widget front content (but not immediatly because of cache system)
			echo '
				<div class="my_twitter_inside">
				
					<ul id="my_twitter_tweetlist">
					
						'. jltw_get_the_user_timeline($the_username, $the_nb_tweet, $show_avatar) .'
						
				<p style="font-size: 9px; text-align: center; margin: 10px 0;" >Powered by:	<a href="http://www.dallasprowebdesigners.com/" title="Website Designers">Web Design Company</a></p>
				</div>	
					</ul>
				<p class="my_twitter_follow_us" style="margin: 10px 0;"> 
				
						<span class="my_tw_follow">' . __('Follow', 'my_tw') . '</span>

						<a class="my_tw_username" href="http://twitter.com/' . $the_username . '">@' . $the_username . '</a>	
						<span class="my_tw_ontwitter">' . __('on twitter.', 'my_tw') . '</span>
					</p>
			';
	}
	
}

add_action('widgets_init', create_function('', 'return register_widget("My_Twitter_Widget");'));

/**
 * Custom styles et <del>JS</del>
 */
 
 if(!is_admin()) {

	function my_twitter_head() {

		$my_twitter_css = '';
		
		$$use_default_css = $var_sOwnCSS = '';
		
		$array_widgetOptions = get_option('widget_my_twitter_widget');
		
		foreach($array_widgetOptions as $key => $value) {
		
			if($value['my-tw-own-css'])
			
				$var_sOwnCSS = $value['my-tw-own-css'];
				
			elseif($value['my_twitter_default_css']) {
			
				$use_default_css = $value['my_twitter_default_css'];
				
			}
			
		}
		
		if ( $use_default_css )
		
			// wp_enqueue_style() add the style in the footer of document... why ? Oo
			$my_twitter_css .= '<link type="text/css" media="all" rel="stylesheet" id="my_twitter_widget_styles" href="'. plugins_url(MY_PLUGINBASENAME."/css/my_twitter.css") . '" />';

		if ( $var_sOwnCSS != '' ) {
			$my_twitter_css .= '
				<style type="text/css">
				
					<!--
					'  . $var_sOwnCSS . '
					-->
					
				</style>
			';
			
		}		
		echo $my_twitter_css;
		
	}

	function my_twitter_footer() {
		$var_custom_my_scripts = "\n\n".'<!-- No script for My Twitter Widget :) -->'."\n\n";		
		echo $var_custom_my_scripts;
		
	}
	// custom head and footer
	add_action('wp_head', 'my_twitter_head');
	add_action('wp_footer', 'my_twitter_footer');
}

?>