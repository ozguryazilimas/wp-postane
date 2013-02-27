<?php 

/*
Plugin Name: WP Pending Post Notifier
Plugin URI: http://www.fixwordpress.net/?p=491
Version: 1.1
Author: larry Ngaosi
Author URI: http://www.fixwordpress.net

Description: NOTE( this is plugin is no longer developed ) This plugin will email a notification when a post has been submitted for review, pending publication. Useful for moderated multi-author blogs. [<a href="http://www.fixwordpress.net/?p=491">WP Pending Post Notifier</a>]

*/
 
 /*  Copyright YEAR  Larry Ngaosi  (email : ngaosi.larry@gmail.com)

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
 register_activation_hook( __FILE__, 'ppn_activate' );
 register_deactivation_hook( __FILE__, 'ppn_deactivation' );
 
 // Hook for adding admin menus
add_action('admin_menu', 'sn_add_option_page');

// action function for above hook
function sn_add_option_page() {
    // Add a new submenu under options:
    add_options_page('Pending Post Notifications', 'Pending Notifications', 'edit_themes', 'status_notifier', 'sn_options_page');
}

function sn_options_page() {
	if(isset($_POST['save'])) {
      update_option('notificationemails',$_POST['notificationemails']);
      update_option('approvednotification',$_POST['approvednotification']);
      update_option('declinednotification',$_POST['declinednotification']);
	  echo "<div id='message' class='updated fade'><p>Notification settings saved.</p></div>";
    }
    ?>
	<div class="wrap"><h2>Pending Post Notifications</h2>
	<form name="site" action="" method="post" id="notifier">
<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row"><label for="notificationemails">Notification Reciever</label></th>
			<td><input type="text" size="50" name="notificationemails" tabindex="1" id="notificationemails" value="<?php echo esc_attr(get_option('notificationemails')); ?>"><br>
 Enter email addresses which should be notified of posts pending review</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Pending Review Notifications</th>
			<td>
				<fieldset>
					<legend class="hidden">Pending Review Notifications</legend>

					<label for="approvednotification"><input type="checkbox" tabindex="2" id="approvednotification" name="approvednotification" value="yes" <?php if(get_option('approvednotification')=='yes') echo 'checked="checked"'; ?> /> Notify contributor when their post is approved</label><br>
					<label for="declinednotification"><input type="checkbox" tabindex="3" id="declinednotification" name="declinednotification" value="yes" <?php if(get_option('declinednotification')=='yes') echo 'checked="checked"'; ?> /> Notify contributor when their post is declined (sent back to drafts)</label><br>
					 
				</fieldset>
			</td>
		</tr>
		
		 
		 
		 
	</tbody></table>
	
 
	<p class="submit">
	<input name="save" type="submit" id="savenotifier" tabindex="6" style="font-weight: bold;" value="Save Settings" />
	</p>
 
	</form>
 	</div>
	<?php
}


add_filter('transition_post_status', 'notify_status',10,3);
function notify_status($new_status, $old_status, $post) {
    global $current_user;
	$contributor = get_userdata($post->post_author);
    if ($old_status != 'pending' && $new_status == 'pending') {
      $emails=get_option('notificationemails');
      if(strlen($emails)) {
        $subject='['.get_option('blogname').'] "'.$post->post_title.'" pending review';
        $message="A new post by {$contributor->display_name} is pending review.\n\n";
        $message.="Author   : {$contributor->user_login} {$contributor->user_email} (IP: {$_SERVER['REMOTE_ADDR']})\n";
        $message.="Title    : {$post->post_title}\n";
		$category = get_the_category($post->ID);
		if(isset($category[0])) 
			$message.="Category : {$category[0]->name}\n";;
        $message.="Review it: ".get_option('siteurl')."/wp-admin/post.php?action=edit&post={$post->ID}\n\n\n";
 
$queried_post = get_post($post->ID);
 
 
		
		
        $message.="Title: ".$queried_post->post_title."\n";
        $message.="Content: \n".$queried_post->post_content."\n\n\n";
		
		
		
        wp_mail( $emails, $subject, $message);
      }
	} elseif ($old_status == 'pending' && $new_status == 'publish' && $current_user->ID!=$contributor->ID) {
      if(get_option('approvednotification')=='yes') {
        $subject='['.get_option('blogname').'] "'.$post->post_title.'" onaylandı';
        $message="{$contributor->display_name},\n\nGönderdiğiniz yazı ".get_permalink($post->ID)." adresinde yayına alındı.\n\nÇok çok teşekkür ederiz. \n\n";
        wp_mail( $contributor->user_email, $subject, $message);
      }
	} elseif ($old_status == 'pending' && $new_status == 'draft' && $current_user->ID!=$contributor->ID) {
      if(get_option('declinednotification')=='yes') {
        $subject='['.get_option('blogname').'] "'.$post->post_title.'" yayına alınamadı';
        $message="{$contributor->display_name},\n\nGönderdiğiniz yazı maalesef yayına alınamadı. ".get_option('siteurl')."/wp-admin/post.php?action=edit&post={$post->ID} adresini ziyaret ederek üzerinde değişiklikler yaptıktan sonra tekrar deneyebilirsiniz. \n\nYayın şartlarımızı şu adreste bulabilirsiniz: ".get_option('siteurl')."/yayin-sartlari";
        wp_mail( $contributor->user_email, $subject, $message);
      }
	}
}

function ppn_activate() {
  add_option('notificationemails',get_option('admin_email'));
    add_option('approvednotification','yes');
    add_option('declinednotification','yes');
}
function ppn_deactivation() {
 delete_option('notificationemails');
 delete_option('approvednotification');
 delete_option('declinednotification');
 }
 
 
?>