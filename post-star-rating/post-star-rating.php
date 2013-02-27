<?php
/*
Plugin Name: Post Star Rating
Plugin URI: http://blog.abusemagazine.com/index.php/category/post-star-rating/
Description: Plugin that allows to rate a post with one to five stars
Version: 0.3.2
Author: O Doutor
Author URI: http://blog.abusemagazine.com/index.php/author/o-doutor/
*/

/*  Copyright 2005  O Doutor  (email : doutorquen@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


require_once('psr.class.php');

/**
 * Tag to use in templates that shows stars for voting
 *
 */
function PSR_show_voting_stars() {
	global $PSR;
	echo $PSR->getVotingStars();
}

/**
 * Tag to use in templates that shows stars with puntuation
 *
 */
function PSR_show_stars() {
	global $PSR;
	echo $PSR->getStars();
}

function PSR_bests_of_month($month = null, $limit = 10) {
	global $PSR;
	echo $PSR->getBestsOfMonth($month, $limit);
}

function PSR_bests_of_moment($limit = 10) {
	global $PSR;
	echo $PSR->getBestsOfMoment($limit);
}

/* Assigning hooks to actions */
$PSR =& new PSR();
add_action('activate_post-star-rating/post-star-rating.php', array(&$PSR, 'install')); /* only works on WP 2.x*/
add_action('init', array(&$PSR, 'init'));
add_action('wp_head', array(&$PSR, 'wp_head'));
?>