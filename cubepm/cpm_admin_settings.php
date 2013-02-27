<?php
/**
 * CubePM Admin - Settings.
 * Admin configuration page for CubePM.
 * @package cubepm
 */


/**
 * CubePM Admin Settings Page
 * 
 * @return null
 */
function cpm_admin_settings(){
	
	if(isset($_POST['cpm_admin_update']) && check_admin_referer('cpm_admin_settings_update', 'cpm_admin_settings_nonce')){
		$errors = array();
		$cpm_permission_newtopic = (array) $_POST['cpm_permission_newtopic'];
		$cpm_email = (bool) $_POST['cpm_email'];
		$cpm_email_from_name = stripslashes(trim($_POST['cpm_email_from_name']));
		$cpm_email_from_email = stripslashes(trim($_POST['cpm_email_from_email']));
		$cpm_email_subject = stripslashes(trim($_POST['cpm_email_subject']));
		$cpm_email_body = stripslashes(trim($_POST['cpm_email_body']));
		if($cpm_email_from_name == ''){
			$errors[] = __('Please enter a name which the email notification would be sent from.', 'cubepm');
		}
		if(!is_email($cpm_email_from_email) && $cpm_email_from_email != '%blog_email%'){
			$errors[] = __('Please enter a valid email address which the email notification would be sent from.', 'cubepm');
		}
		if($cpm_email_subject == ''){
			$errors[] = __('Please enter a subject for the email notification.', 'cubepm');
		}
		if($cpm_email_body == ''){
			$errors[] = __('The email body cannot be left blank.', 'cubepm');
		}
		if(count($errors)>0){
			foreach($errors as $error){
				echo '<div class="error"><p><strong>' . $error . '</strong></p></div>';
			}
		}
		else{
			update_option('cpm_permission_newtopic', $cpm_permission_newtopic);
			update_option('cpm_email', $cpm_email);
			update_option('cpm_email_from_name', $cpm_email_from_name);
			update_option('cpm_email_from_email', $cpm_email_from_email);
			update_option('cpm_email_subject', $cpm_email_subject);
			update_option('cpm_email_body', $cpm_email_body);
			echo '<div class="updated"><p><strong>' . __('Settings saved.', 'cubepm') . '</strong></p></div>';
		}
	}
	
	$cpm_email = get_option('cpm_email') ? 'checked="checked"' : "";
	$cpm_email_from_name = htmlspecialchars(get_option('cpm_email_from_name'));
	$cpm_email_from_email = htmlspecialchars(get_option('cpm_email_from_email'));
	$cpm_email_subject = htmlspecialchars(get_option('cpm_email_subject'));
	$cpm_email_body = htmlspecialchars(get_option('cpm_email_body'));
	$cpm_permission_newtopic = (array) get_option('cpm_permission_newtopic');
?>
<div class="wrap"> 
	<div id="icon-options-general" class="icon32"><br /></div> 
<h2>CubePM - <?php _e('Settings', 'cubepm'); ?></h2> 
 
<form method="post">
<input type='hidden' name='cpm_admin_update' value='settings' />
<?php wp_nonce_field('cpm_admin_settings_update','cpm_admin_settings_nonce'); ?>

<h3 class="title"><?php _e('Permissions', 'cubepm'); ?></h3>
<table class="form-table"> 

<tr valign="top"> 
<th scope="row"><?php _e('Allowed to start PM topics', 'cubepm'); ?></th> 
<td><fieldset>
<p>
<label><input name="cpm_permission_newtopic[]"  type="checkbox" value="administrator" <?php echo in_array('administrator', $cpm_permission_newtopic) ? 'checked="checked"' : ''  ; ?> /> <?php _e('Administrators', 'cubepm'); ?></label><br />
<label><input name="cpm_permission_newtopic[]"  type="checkbox" value="editor" <?php echo in_array('editor', $cpm_permission_newtopic) ? 'checked="checked"' : ''  ; ?> /> <?php _e('Editors', 'cubepm'); ?></label><br />
<label><input name="cpm_permission_newtopic[]"  type="checkbox" value="author" <?php echo in_array('author', $cpm_permission_newtopic) ? 'checked="checked"' : ''  ; ?> /> <?php _e('Authors', 'cubepm'); ?></label><br />
<label><input name="cpm_permission_newtopic[]"  type="checkbox" value="contributor" <?php echo in_array('contributor', $cpm_permission_newtopic) ? 'checked="checked"' : ''  ; ?> /> <?php _e('Contributors', 'cubepm'); ?></label><br />
<label><input name="cpm_permission_newtopic[]"  type="checkbox" value="subscriber" <?php echo in_array('subscriber', $cpm_permission_newtopic) ? 'checked="checked"' : ''  ; ?> /> <?php _e('Subscribers', 'cubepm'); ?></label>
</p> 
</fieldset></td> 
</tr>

</table>

<h3 class="title"><?php _e('Email Notifications', 'cubepm'); ?></h3>
<table class="form-table"> 

<tr valign="top">
<th scope="row"><?php _e('Email notifications', 'cubepm'); ?></th> 
<td> <fieldset><label for="cpm_email">
<input name="cpm_email" type="checkbox" id="cpm_email" value="1" <?php echo $cpm_email; ?>/>
<?php _e('Enable email notifications for new PMs', 'cubepm'); ?></label>
</fieldset></td>
</tr>

<tr valign="top">
<th scope="row"><label for="cpm_email_from_name"><?php _e('Sent From', 'cubepm'); ?> (<?php _e('name', 'cubepm'); ?>)</label></th>
<td><input name="cpm_email_from_name" type="text" id="cpm_email_from_name" value="<?php echo $cpm_email_from_name; ?>" class="regular-text" />
<span class="description">The name in which email notifications are sent from.</span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="cpm_email_from_email"><?php _e('Sent From', 'cubepm'); ?> (<?php _e('email', 'cubepm'); ?>)</label></th>
<td><input name="cpm_email_from_email" type="text" id="cpm_email_from_email" value="<?php echo $cpm_email_from_email; ?>" class="regular-text" />
<span class="description"><?php _e('The email address in which email notifications are sent from.', 'cubepm'); ?></span></td>
</tr>

<tr valign="top">
<th scope="row"><label for="cpm_email_subject"><?php _e('Subject', 'cubepm'); ?></label></th>
<td><input name="cpm_email_subject" type="text" id="cpm_email_subject" value="<?php echo $cpm_email_subject; ?>" class="regular-text" />
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="cpm_email_body"><?php _e('Email Body', 'cubepm'); ?></label></th>
<td><fieldset>
<p>
<textarea name="cpm_email_body" rows="10" cols="50" id="cpm_email_body" class="large-text code"><?php echo $cpm_email_body; ?></textarea>
</p>
<p><?php _e('You may use the following variables for the above fields:'); ?></p>
<table>
<tr><td><strong>%blog_name%</strong></td><td><?php _e('Name of your blog', 'cubepm'); ?> (<?php bloginfo('name'); ?>)</td></tr>
<tr><td><strong>%blog_email%</strong></td><td><?php _e('Email address of blog owner', 'cubepm'); ?> (<?php bloginfo('admin_email'); ?>)</td></tr>
<tr><td><strong>%sender%</strong></td><td><?php _e('Display name of the person who sent the PM', 'cubepm'); ?></td></tr>
<tr><td><strong>%subject%</strong></td><td><?php _e('Subject of the PM', 'cubepm'); ?></td></tr>
<tr><td><strong>%pm_link%</strong></td><td><?php _e('URL to the PM thread', 'cubepm'); ?></td></tr>
<tr><td><strong>%message%</strong></td><td><?php _e('Message contents of PM', 'cubepm'); ?></td></tr>
</table>
</fieldset></td>
</tr>

</table>
 
<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes', 'cubepm'); ?>"  /></p></form> 
 
</div>
<?php
}