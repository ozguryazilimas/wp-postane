<?php

define( WPE_NAME, 'wpenginesnapshot' );

	if (!current_user_can('manage_options')) {
		wp_die(__("You don't have enough privileges to do this", WPE_NAME));
	}

	$wpe_snapshot = WpeSnapshot::instance();
	$message = '';
	$error   = '';
	$options = $wpe_snapshot->get_options();

	// Handle ZIP file downloads
	$dl_name = wpe_param('dl_snapshot');
	if ( !empty($dl_name) )
	{
		$dl_path = $wpe_snapshot->get_option('snapshot_path') . '/' . $dl_name;

		// Attempt to empty the PHP buffer.  If we're successful, we just allow
		// the transfer to proceed and we good.  If not, tell the user what happened and
		// explain where the files are they need to download.
		$so_far = ob_get_clean();		// retreive and truncate the current output buffer
		if ( $so_far === FALSE )		// no dice
		{
?>
			<p>
				Because PHP output buffering isn't enabled, or isn't big enough, we cannot
				stream out your snapshot file.  You'll need to download the file yourself from
				your server:
			</p>
			<p>
				<code><?php echo($dl_path) ?></code>
			</p>
<?php
		}
		else			// output buffer clear; we can do whatever we want!
		{
			header('Content-Type: application/x-gzip');
			header('Content-Disposition: attachment; filename="'.$dl_name.'"');
			readfile($dl_path);
		}
		exit(0);
	}

	// Process form submissions
	if (isset($_POST['options']) && isset($_POST['submit'])) {
		check_admin_referer(WPE_NAME.'-config');

		foreach ($options as $key => $value) {
			if (isset($_POST['options'][$key])) {
				$wpe_snapshot->set_option( $key, $options[$key] = stripslashes($_POST['options'][$key]) );
			}
		}

		$error = $wpe_snapshot->validate_options( $options );
		if (empty($error)) {
			$message = __("Settings have been successfully updated", WPE_NAME);
		}
	}
	elseif (isset($_POST['options']) && isset($_POST['revert'])) {
		check_admin_referer(WPE_NAME.'-config');
		$wpe_snapshot->restore_default_options();
		$options = $wpe_snapshot->get_options();		// refresh local variable with options
		$message = __("Settings have been restored to defaults", WPE_NAME);
	}
	elseif (isset($_POST['take-snapshot'])) {
		check_admin_referer(WPE_NAME.'-config');
		try
		{
			$file_paths = $wpe_snapshot->take_snapshot( wpe_el($_POST,'dump-files'), wpe_el($_POST,'dump-db') );
			$message = __("<b>Snapshot complete.</b>  See the bottom of the screen for the list of files to download.", WPE_NAME);
		} catch ( Exception $e ) {
			$error = __("Error while taking snapshot: ",WPE_NAME) . $e->getMessage();
		}
	}
	elseif (isset($_POST['delete-snapshots'])) {
		check_admin_referer(WPE_NAME.'-config');
		$snapshot_dir = $wpe_snapshot->get_option('snapshot_path');
		$snapshot_names = get_snapshots();
		foreach ( $snapshot_names as $name )
			unlink( "$snapshot_dir/$name" );
		$message = "Deleted ".count($snapshot_names)." snapshots.";
	}
	elseif (isset($_POST['s3-upload'])) {
		check_admin_referer(WPE_NAME.'-config');
		$snapshot_dir = $wpe_snapshot->get_option('snapshot_path');
		$snapshot_names = get_snapshots();
		$folder = $_SERVER['HTTP_HOST'];
		if ( ! $folder )
			$folder = $_SERVER['SERVER_NAME'];
		if ( ! $folder )
			$folder = "unnamed-upload-" . time();
		$message = "Uploading into the '$folder' directory...<br>";
		foreach ( $snapshot_names as $name )
		{
			$path = "$snapshot_dir/$name";
			if ( ! $wpe_snapshot->upload_to_s3( array($path), $folder ) )
			{
				$message = "";
				$error = "Got an error uploading to S3.<br><br>Instead, send support@wpengine.com the URLs to your snapshot files, or download them yourself and find a way to send us the files.";
				break;
			}
			$message .= "Uploaded $name<br>";
		}
	}
?>
<div class="wrap">
	<h2><?php _e("WPEngine Snapshot Options", WPE_NAME); ?></h2>

	<?php if (!empty($error)) : ?>
	<div class="error"><p><?php echo $error; ?></p></div>
	<?php endif; ?>

	<?php if (!empty($message)) : ?>
	<div class="updated fade"><p><?php echo $message; ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo attribute_escape(stripslashes($_SERVER['REQUEST_URI'])); ?>">

		<table class="form-table">
			<tbody valign="top">
				<tr>
					<th scope="row"><label for="snapshot_path"><?php _e('Snapshot location', WPE_NAME); ?></label></th>
					<td><input type="text" id="snapshot_path" size="70" maxlength="255" name="options[snapshot_path]" value="<?php echo attribute_escape($options['snapshot_path']); ?>" <?php if(is_wpe()) echo("readonly"); ?>  /><br/>
						<?php _e("This is the directory where WPEngine Snapshot will store completed snapshot files.", WPE_NAME); ?><br/>
						<?php if(!is_wpe()) _e("<strong>Please note:</strong> this directory must be writable by the web server.<br/>", WPE_NAME); ?>
						<?php if(is_wpe()) _e("<strong>This setting cannot be changed.</strong><br/>", WPE_NAME); ?>
					</td>
				</tr>
		</table>

		<p class="submit submit-top">
			<?php wp_nonce_field(WPE_NAME.'-config'); ?>
			<input type="submit" name="submit" value="<?php _e('Save Changes', WPE_NAME) ?>" class="button-primary"/>
			<input type="submit" name="revert" value="<?php _e('Restore Defaults', WPE_NAME) ?>" class="deletion"/>

			<!--
			<input type="submit" name="erase-snapshots" value="<?php _e('Erase all snapshots', WPE_NAME) ?>" class="deletion" onclick="return confirm('<?php _e("Are you sure you want to delete all snapshots currently in the snapshot directory?", WPE_NAME); ?>')"/>
			-->
		</p>

		<hr/>

		<h2><?php _e("Take a Snapshot",WPE_NAME); ?></h2>

		<p>
			A "snapshot" is a complete dump of your blog including the database, system files, uploaded media files,
			and plugins.
		</p>

		<p>
			For bigger blogs it can take a while to complete the dump, so <strong style="font-weight:bold; color: #900; background: inherit;">please be patient!</strong>
		</p>

		<p>
			<input type="checkbox" name="dump-files" <?php if(wpe_param('dump-files',1))echo("checked"); ?>>
			Create an archive of all files on disk.
		</p>

		<p>
			<input type="checkbox" name="dump-db" <?php if(wpe_param('dump-db',1))echo("checked"); ?>>
			Snapshot the database contents.
		</p>
<!--
		<p>
			(Advanced!) Start dumping at this table: <input type="text" name="db-table" value="">
		</p>
-->
		<p>
			<input type="submit" name="take-snapshot" value="<?php _e('Take Snapshot', WPE_NAME) ?>" />
		</p>

	<hr/>
	<h2><?php _e("Download Your Snapshots",WPE_NAME); ?></h2>

<?php
	// Load snapshot contents
	$snapshot_dir = $wpe_snapshot->get_option("snapshot_path");
	if ( ! file_exists( $snapshot_dir ) )
	{
		echo("<p><i>(<b>Snapshot directory doesn't exist.</b>  Edit the options above.)</i></p>");
	} else {
		$snapshot_names = get_snapshots();

		if ( ! count($snapshot_names) )
		{
			echo("<p><i>(No snapshots yet!  Use the button above to take a snapshot.)</i></p>");
		} else {
?>
			<ul>
<?php
			foreach ( $snapshot_names as $name )
			{
				$dl_url = $_SERVER['REQUEST_URI'] . "&dl_snapshot=" . urlencode($name);
				$path = "$snapshot_dir/$name";
				if ( can_download_directly( $path ) )		// better URL if we can manage it
					$dl_url = $wpe_snapshot->get_uri_to_local_file( $path );
				$size = filesize( $path );
				$str_size = wpe_format_bytes( $size, 1 );
				echo("<li><a href=\"$dl_url\">$name</a> ($str_size)</li>");
			}
?>
			</ul>

		<!--
		<p>
			<input type="submit" name="s3-upload" value="<?php _e('Send Snapshots to WPEngine', WPE_NAME) ?>" />
			(Uploads your snapshots to our secure "incoming" dropbox on Amazon S3)
		</p>
		-->

		<p>
			<input type="submit" name="delete-snapshots" value="<?php _e('Delete All Snapshots', WPE_NAME) ?>" />
		</p>
<?php
		}
	}
?>


	</form>
</div>

<?php

function get_snapshots()
{
	global $wpe_snapshot;

	$snapshot_names = array();
	$snapshot_dir = $wpe_snapshot->get_option("snapshot_path");
	@$dh = opendir($snapshot_dir);
	if ( $dh !== FALSE )
	{
		while ( FALSE !== ( $name = readdir($dh) ) )
		{
			if ( preg_match("!\\.gz\$!",$name) )
				$snapshot_names[] = $name;
		}
		closedir($dh);
	}
	sort($snapshot_names);
	return $snapshot_names;
}

// True if we believe we can download this path directly by the browser, false if
// we think we need to indirectly stream the data to the user.
function can_download_directly( $path )
{
	$uploads_path = ABSPATH . 'wp-content/uploads/';
	if ( substr($path,0,strlen($uploads_path)) == $uploads_path )
		return TRUE;
	return FALSE;
}

?>
