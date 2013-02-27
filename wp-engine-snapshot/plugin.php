<?php
/*
Plugin Name: WPEngine Snapshot
Plugin URI: http://wpengine.com/plugins
Description: Blog snapshot backup -- database, files, and all
Author: Jason Cohen (WPEngine)
Version: 1.3.2
Author URI: http://blog.asmartbear.com/
*/

if ( ! class_exists('PearTar') )
{
	require_once(dirname(__FILE__)."/PearTar.php");
}
//require_once(dirname(__FILE__)."/S3.php");
if ( ! class_exists('WpePlugin_snapshot') )
{
	require_once(dirname(__FILE__)."/common-snapshot.php");
}
if ( ! class_exists('WpeSnapshot') )
{
class WpeSnapshot extends WpePlugin_snapshot
{
	protected $options;

	public function get_plugin_title()
	{
		return "WPEngine Snapshot";
	}

	public function get_default_options()
	{
		// Determine the default snapshot path.
		// If we can manage to do it in the uploads directory, that's going to be most likely to
		// have correct permissions for writing AND easiest to download directly.
		$snapshot_path = $plugin_snapshot_path;		// ultimately, set this to the right path
		$plugin_snapshot_path = dirname(__FILE__) . '/snapshots';
		$uploads_snapshot_path = ABSPATH . 'wp-content/uploads/snapshots';
		foreach ( array( $uploads_snapshot_path, $plugin_snapshot_path ) as $path )
		{
			if ( ! file_exists( $path ) )		// doesn't exist, but try to make it
			{
				if ( ! @mkdir( $path, 0775 ) )
					continue;
			}
			if ( ! is_dir( $path ) )	// exists as directory?
				continue;
			// attempt to write a temporary file to the directory
			$test_file_path = tempnam( $path, 'write-test' );
			@file_put_contents( $test_file_path, 'foobar' );
			$readback = @file_get_contents( $test_file_path );
			@unlink( $test_file_path );
			if ( $readback == 'foobar' )		// can read and write a file here?
			{
				$snapshot_path = $path;
				break;
			}
		}

		// Return default options
		return array (
			'snapshot_path' => $snapshot_path,
		);
	}

	// Validates that the given set of options is a valid configuration.
	// If validation fails, a human-readable error message is returned, otherwise FALSE.
	public function validate_options( $options )
	{
		$snapshot_path = $options['snapshot_path'];
		if ( !file_exists($snapshot_path) )
			return "Snapshot directory does not exist!";
		else if ( !is_dir($snapshot_path) )
			return "Snapshot path is a file; should be a directory.";
		else if ( !is_readable($snapshot_path) || !is_writable($snapshot_path) )
			return "Snapshot path must be readable and writable by the server!";

		return FALSE;
	}

	// Singleton instance
	public function instance()
	{
		static $self = FALSE;
		if ( !$self )
			$self = new WpeSnapshot();
		return $self;
	}

	// Take a snapshot of the current blog.
	// Throws exception on error, otherwise returns the filenames of the new snapshot files as an array.
	public function take_snapshot( $dump_files, $dump_db )
	{
		// Try to expand your mind, man
		$this->increase_php_limits();

		// Validate options
		$error = $this->validate_options( $this->get_options() );
		if (!empty($error)) throw new Exception( $error );
		if ( !$dump_files && !$dump_db )
			throw new Exception("You didn't select anything to dump.");

		// Determine variables
		$site_token = preg_replace( "#^https?\\W+#", "", get_bloginfo('url') );
		$site_token = preg_replace( "#[^a-zA-Z-]+#", ".", $site_token );
		$site_token = preg_replace( "#\\.$#", "", $site_token );
		$zip_file_prefix = $site_token.'-'.date('Y-m-d-H-i-s');
		$zip_glob = $this->get_option('snapshot_path') . '/' . $zip_file_prefix . ".*";
		$zip_file_name = $zip_file_prefix . "-files.tar.gz";
		$zip_file_path = $this->get_option('snapshot_path') . '/' . $zip_file_name;
		$wp_root_dir = ABSPATH;

		if ( $dump_files )
		{
				// Create Tar archive object
				$zip = new PearTar( $zip_file_path, "gz");

				// Build regex for ignoring paths
				$re_ignore = '/.cvs$|/.svn$|/.git$';			// version control directories
				$re_ignore .= '|/.+~$';							// backup files
				$re_ignore .= '|/plugins/[^/]+/snapshots$';		// the snapshot directory itself
				$re_ignore .= '|/wp-content/uploads/snapshots$';		// the snapshot directory itself
				$re_ignore .= '|/wp-content/plugins/wp-file-cache/cache$';	// popular on-disk options cache plugin
				$re_ignore .= '|/wp-content/cache$';				// page cache for WPCache and WPSuperCache
				$re_ignore .= '|/wp-content/w3tc$';				// page cache for W3 Total Cache
				if ( defined('PWP_NAME') )			// known paths to skip with PWP sites
				{
					$re_ignore .= '|/cache$';						// the standard PWP cache directory
					$re_ignore .= '|/wp-content/mysql.sql(?:.gz)?$';		// our own backup-snapshot file
				}
				$re_ignore = '!' . $re_ignore . '!';	// turn it into a complete regular expression
				$zip->setIgnoreRegexp( $re_ignore );

				// Add all installation files
				$zip->addModify( $wp_root_dir, '', dirname($wp_root_dir) );
		}

		// Create and add database dump
		if ( $dump_db )
		{
				$mysql_file_name = $zip_file_prefix . "-database.sql.gz";
				$mysql_file_path = $this->get_option('snapshot_path') . '/' . $mysql_file_name;
				//system("mysqldump 428636_main_wp > $mysql_file_path");
				$this->_create_mysql_dump( $mysql_file_path );
		}

		// Return the file list
		return glob($zip_glob,GLOB_NOSORT);
	}

	// Given an array of files on disk, uploads them to the WPEngine incoming dropbox in the given folder name
	public function upload_to_s3( $file_list, $folder )
	{
		// Prepare S3 object.  No SSL because can cause issues with large files.
		// Unfortunately this doesn't work without WPEngine credentials.
		$s3 = new S3( null, null, FALSE );
		$bucket = "snapshots.wpengine";
		foreach ( $file_list as $path )
		{
			$uri = $folder . '/' . basename($path);
			$result = $s3->putObject( S3::inputFile($path), $bucket, $uri, S3::ACL_PUBLIC_READ );
			if ( ! $result )
				return FALSE;
		}
		return TRUE;
	}

	private function _create_mysql_dump( $path )
	{
		$one_table_per_file = FALSE;
		$first_table = array_key_exists( "db-table", $_REQUEST ) ? $_REQUEST['db-table'] : FALSE;
	
		// This seems wrong but it prevents UTF8 characters being re-encoded improperly.
		// Would be better to get down to the bottom of this!
		mysql_query("SET NAMES 'latin1'");
		mysql_query("SET CHARACTER SET 'latin1'");

		if ( ! $one_table_per_file )
			$fd = gzopen( $path, "wb" );
		$tables = $this->_mysql_get_rows("SHOW TABLES");
		foreach ( $tables as $table )
		{
			if ( $first_table )
			{
				if ( $table[0] == $first_table )
					$first_table = FALSE;
				else
					continue;
			}
			if ( $one_table_per_file )
			{
				$table_path = preg_replace( "/database/", "database-table-".$table[0], $path );
				$fd = gzopen( $table_path, "wb" );
			}
			$this->_dump_mysql_table( $fd, $table[0] );
			if ( $one_table_per_file )
				gzclose($fd);
		}
		if ( ! $one_table_per_file )
			gzclose($fd);
	}

	private function _dump_mysql_table( $fd, $table )
	{
		$rows = $this->_mysql_get_rows("SHOW CREATE TABLE $table");
		gzwrite($fd,"\n\n\n");
		gzwrite($fd,"DROP TABLE IF EXISTS $table;\n");
		gzwrite($fd,$rows[0][1]);
		gzwrite($fd,";\n");

		// Select all the rows, unbuffered since it can be a ton, and write out insert statements.
		$rs = mysql_unbuffered_query("SELECT * FROM $table" );
		while ( FALSE !== ( $row = mysql_fetch_row($rs) ) )
		{
			gzwrite($fd,"INSERT INTO $table VALUE ");
			$sepChar = '(';
			foreach ( array_values($row) as $x )
			{
				gzwrite($fd,$sepChar.$this->_mysql_const($x));
				$sepChar = ',';
			}
			gzwrite($fd,");\n");
		}
		mysql_free_result($rs);
	}

	private function _mysql_get_rows( $query )
	{
		$rows = array();
		$rs = mysql_query( $query );
		while ( FALSE !== ( $row = mysql_fetch_array($rs) ) )
			$rows[] = $row;
		mysql_free_result($rs);
		return $rows;
	}

	private function _mysql_const( $x )
	{
		if ( strlen($x) == 0 ) return "''";
		return "'" . mysql_real_escape_string($x) . "'";
	}

	private function _zip_add_recursive( $zip, $rel_path, $dir, $re_ignore )
	{
		// Add the directory itself -- no longer necessary?
		//$dirname = $rel_path . '/' . basename($dir);
		//$zip->addEmptyDir( substr($dirname,1) );

		// Load all included files and directories which we should also add.
		// Separate this from adding or recursion to reduce number of open handles.
		$file_names = array();
		$dir_names = array();
		if ( FALSE === ( $dh = opendir( $dir ) ) )
			throw new Exception("Unable to process directory: <code>".htmlspecialchars($dir)."</code>" );
		while ( FALSE !== ( $name = readdir($dh) ) )
		{
			if ( $name == '.' || $name == '..' ) continue;
			$path = "$dir/$name";
			if ( preg_match( $re_ignore, $path ) ) continue;	// ignore certain paths
			if ( is_dir($path) )
				$dir_names[] = $name;
			else if ( is_file($path) )
				$file_names[] = $name;
			else { /* skip unnatural files */ }
		}
		closedir($dh);

		// Add files to the archive
		foreach ( $file_names as $file_name )
		{
			$path = "$dir/$file_name";
			if ( ! $zip->addModify( $path, '', '' ) )
				throw new Exception("Unable to add file to archive: <code>".htmlspecialchars($path)."</code>");
		}

		// Recursively add directories to the archive
		foreach ( $dir_names as $dir_name )
		{
			$this->_zip_add_recursive( $zip, $dirname, "$dir/$dir_name", $re_ignore );
		}
	}

}
}

// Create an instance to get all our hooks installed
$wpe_snapshot = WpeSnapshot::instance();

?>
