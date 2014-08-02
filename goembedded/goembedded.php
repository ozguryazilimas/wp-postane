<?php
/*
Plugin Name: Goembedded
Plugin URI: http://www.ozguryazilim.com.tr
Description: This plugin activates Wordpress oEmbed capabilities for comments.
Version: 0.1.0
Author: Onur Küçük
Author URI: http://www.delipenguen.net
License: GPL2
*/

/*  Copyright (C) 2014, Onur Küçük

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class GE_OEmbed_Comments {
  function __construct() {
    add_action( 'init', array($this, 'init'));
  }

  function init() {
    if (!is_admin()) {
      $this->add_oembed_comment_filter();
    }
  }

  function oembed_filter($comment_text) {
    global $wp_embed;

    // Automatic discovery might be a security risk
    add_filter('embed_oembed_discover', '__return_false', 999);
    $comment_text = $wp_embed->autoembed($comment_text);

    // reenable automatic discovery
    remove_filter('embed_oembed_discover', '__return_false', 999);

    return $comment_text;
  }

  function add_oembed_comment_filter() {
    // make_clickable breaks oEmbed regex, try to activate before that
    $has_clickable = has_filter('get_comment_text', 'make_clickable');
    $priority = $has_clickable ? ($has_clickable - 1) : 8;

    add_filter('get_comment_text', array($this, 'oembed_filter'), $priority);
  }

}

new GE_OEmbed_Comments;

?>
