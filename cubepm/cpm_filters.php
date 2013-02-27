<?php
/**
 * CubePM Filters.
 * Filters message contents and subject of PMs.
 * @package cubepm
 */

/** Filters for PM subject */
add_filter('cpm_subject', 'cpm_filter_utfEncode', 1000);
add_filter('cpm_subject', 'htmlspecialchars', 1);
add_filter('cpm_subject', 'stripslashes', 10);

/** Filters for PM contents */
add_filter('cpm_message', 'cpm_filter_utfEncode', 1000);
add_filter('cpm_message', 'htmlspecialchars', 1);
add_filter('cpm_message', 'wpautop', 10);
add_filter('cpm_message', 'wptexturize', 10);
add_filter('cpm_message', 'make_clickable', 10);
add_filter('cpm_message', 'stripslashes', 10);

/** Filters for user link */
add_filter('cpm_user_link', 'cpm_filter_sendPM', 1, 2);

/**
 * Filter that links names of users to send new PM
 * 
 * @param string $value
 * @param object $user
 * @return string
 */
function cpm_filter_sendPM($value, $user){
	return cpm_buildURL(array('cpm_action'=>'new', 'cpm_recipient'=>$user->user_login));
}

/**
 * Filter handles encoding of characters to UTF
 * 
 * @param string $string
 * @return string
 */
function cpm_filter_utfEncode($string){
	return mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');
}