<?php
/*
Plugin Name: Homing Pidgeon
Plugin URI: http://www.ozguryazilim.com.tr
Description: Notifies configured users when a post is submitted as pending

Version: 0.5.0
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


if (is_admin()) {
 add_action('admin_menu', 'homing_pidgeon_menu');
}

function homing_pidgeon_init() {
  load_plugin_textdomain('homing_pidgeon', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('init', 'homing_pidgeon_init');

function homing_pidgeon_menu() {
  add_options_page('Homing Pidgeon Options', 'Homing Pidgeon', 'manage_options', 'homing-pidgeon-settings', 'homing_pidgeon_options');
  add_action('admin_init', 'register_homing_pidgeon_settings' );
}

function register_homing_pidgeon_settings() {
  register_setting('homing-pidgeon-group', 'homing_pidgeon_recipient');
}

function homing_pidgeon_options() {
?>
  <div class="wrap">
  <h2>Homing Pidgeon</h2>
  <p><?php echo __('Pending post notification recipients', 'homing_pidgeon') ?></p>
  <form method="post" action="options.php">
    <?php settings_fields('homing-pidgeon-group'); ?>
    <?php do_settings_sections('homing-pidgeon-group'); ?>
    <table class="form-table">
      <tr valign="top">
      <th scope="row"><?php echo __('Email', 'homing_pidgeon') ?>:</th>
      <td><input type="text" name="homing_pidgeon_recipient" class="regular-text" value="<?php echo get_option('homing_pidgeon_recipient'); ?>" /></td>
      </tr>
    </table>
    <?php submit_button(); ?>
    </form>
  </div>
<?php
}

function homing_pidgeon_send_email($new_status, $old_status, $post) {
  global $current_user;
  $contributor = get_userdata($post->post_author);

  if ($new_status == 'pending' && $old_status != 'pending') {
    $recipient_option = get_option('homing_pidgeon_recipient');
    $recipients = (empty($recipient_option)) ? get_option('admin_email') : $recipient_option;

    if (strlen($recipients)) {
      $url = get_permalink($post->ID);
      $edit_link = get_edit_post_link($post->ID, '');
      $preview_link = get_permalink($post->ID) . '&preview=true';
      $category = get_the_category($post->ID);
      $subject = '[' . get_option('blogname') . '] ' . sprintf(__("%s is waiting for review", 'homing_pidgeon'), $post->post_title);

      $message = "\r\n";
      $message .= sprintf(__("A new post by %s is pending review", 'homing_pidgeon'), $contributor->display_name);
      $message .= "\r\n";
      $message .= "\r\n";
      $message .= __('Author', 'homing_pidgeon') . " : {$contributor->user_login} {$contributor->user_email} (IP: {$_SERVER['REMOTE_ADDR']})";
      $message .= "\r\n";
      $message .= __('Title', 'homing_pidgeon') . " : $post->post_title";
      $message .= "\r\n";
      if (isset($category[0])) {
        $message .= __('Category', 'homing_pidgeon') . " : {$category[0]->name}";
        $message .= "\r\n";
      }
      $message .= __('Edit', 'homing_pidgeon') . " : $edit_link";
      $message .= "\r\n";
      $message .= __('Preview', 'homing_pidgeon') . " : $preview_link";
      $message .= "\r\n";
      $message .= __('Content', 'homing_pidgeon') . " :";
      $message .= "\r\n";
      $message .= "\r\n";
      $message .= $post->post_content;
      $message .= "\r\n";
      $message .= "\r\n";

      $result = wp_mail($recipients, $subject, $message);
    }
  } elseif ($old_status == 'pending' && $new_status == 'publish' && $current_user->ID != $contributor->ID) {
    $url = get_permalink($post->ID);

    $subject = '[' . get_option('blogname') . '] ' . sprintf(__("%s is approved", 'homing_pidgeon'), $post->post_title);
    $message = "{$contributor->display_name},";
    $message .= "\r\n";
    $message .= "\r\n";
    $message .= sprintf(__("Your submission is published at %s.\n\nThank you very much.", 'homing_pidgeon'), get_permalink($post->ID));
    $message .= "\r\n";
    $message .= "\r\n";

    $result = wp_mail($contributor->user_email, $subject, $message);
  }
}
add_action('transition_post_status','homing_pidgeon_send_email', 10, 3);


?>
