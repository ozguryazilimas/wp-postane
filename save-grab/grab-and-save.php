<?php
/*
Plugin Name: Grab & Save
Plugin URI: http://digitalmemo.neobie.net/grab-save
Author: Lim Kai Yang
Author URI: http://digitalmemo.neobie.net
Version: 1.0.4
Description: This plugin allows you to fetch the remote image and save to your local wordpress.
*/

class GrabAndSave {

	var $imageName;

	function GrabAndSave(){$this->__construct();}
		
	function __construct(){
		global $wp_version;
		
		if ($wp_version < 3.5) {
			if ( basename($_SERVER['PHP_SELF']) != "media-upload.php" ) return;
		} else {
			if ( basename($_SERVER['PHP_SELF']) != "media-upload.php" && basename($_SERVER['PHP_SELF']) != "post.php" && basename($_SERVER['PHP_SELF']) != "post-new.php") return;
		}
		
		add_filter("media_upload_tabs",array(&$this,"build_tab"));
		add_action("media_upload_grabAndSave", array(&$this, "menu_handle"));
	}
	
	/*
	 * Merge an array into middle of another array
	 *
	 * @param array $array the array to insert
	 * @param array $insert array to be inserted
	 * @param int $position index of array
	 */
	function array_insert(&$array, $insert, $position) {
		settype($array, "array");
		settype($insert, "array");
		settype($position, "int");

		//if pos is start, just merge them
		if($position==0) {
			$array = array_merge($insert, $array);
		} else {


			//if pos is end just merge them
			if($position >= (count($array)-1)) {
				$array = array_merge($array, $insert);
			} else {
				//split into head and tail, then merge head+inserted bit+tail
				$head = array_slice($array, 0, $position);
				$tail = array_slice($array, $position);
				$array = array_merge($head, $insert, $tail);
			}
		}
		return $array;
	}

	
	function build_tab($tabs) {
		$newtab = array('grabAndSave' => __('İnternet\'ten Yükle', 'grabAndSave'));
		return $this->array_insert($tabs, $newtab, 2);
		//return array_merge($tabs,$newtab);
	}
	function menu_handle() {
		return wp_iframe(array($this,"media_process"));
	}
	function fetch_image($url) {
		if ( function_exists("curl_init") ) {
			return $this->curl_fetch_image($url);
		} elseif ( ini_get("allow_url_fopen") ) {
			return $this->fopen_fetch_image($url);
		}
	}
	function curl_fetch_image($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$image = curl_exec($ch);
		curl_close($ch);
		return $image;
	}
	function fopen_fetch_image($url) {
		$image = file_get_contents($url, false, $context);
		return $image;
	}
	
	function media_process() {
		
		if ( $_POST['imageurl'] ) {
			$imageurl = $_POST['imageurl'];
			$imageurl = stripslashes($imageurl);
			$uploads = wp_upload_dir();
			$post_id = isset($_GET['post_id'])? (int) $_GET['post_id'] : 0;
			$ext = pathinfo( basename($imageurl) , PATHINFO_EXTENSION);
			$newfilename = $_POST['newfilename'] ? $_POST['newfilename'] . "." . $ext : basename($imageurl);
			
			$filename = wp_unique_filename( $uploads['path'], $newfilename, $unique_filename_callback = null );
			$wp_filetype = wp_check_filetype($filename, null );
			$fullpathfilename = $uploads['path'] . "/" . $filename;
			
			try {
				if ( !substr_count($wp_filetype['type'], "image") ) {
					throw new Exception( basename($imageurl) . ' is not a valid image. ' . $wp_filetype['type']  . '' );
				}
			
				$image_string = $this->fetch_image($imageurl);
				$fileSaved = file_put_contents($uploads['path'] . "/" . $filename, $image_string);
				if ( !$fileSaved ) {
					throw new Exception("The file cannot be saved.");
				}
				
				$attachment = array(
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
					 'post_content' => '',
					 'post_status' => 'inherit',
					 'guid' => $uploads['url'] . "/" . $filename
				);
				$attach_id = wp_insert_attachment( $attachment, $fullpathfilename, $post_id );
				if ( !$attach_id ) {
					throw new Exception("Failed to save record into database.");
				}
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attach_data = wp_generate_attachment_metadata( $attach_id, $fullpathfilename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
			
			} catch (Exception $e) {
				$error = '<div id="message" class="error"><p>' . $e->getMessage() . '</p></div>';
			}

		}
		media_upload_header();
		if ( !function_exists("curl_init") && !ini_get("allow_url_fopen") ) {
			echo '<div id="message" class="error"><p><b>cURL</b> or <b>allow_url_fopen</b> needs to be enabled. Please consult your server Administrator.</p></div>';
		} elseif ( $error ) {
			echo $error;
		} else {
			if ( $fileSaved && $attach_id ) {
				echo '<div id="message" class="updated"><p>File saved.</p></div>';
			}
		}
		?>
		<form action="" method="post" id="image-form" class="media-upload-form type-form">
		<h3 class="media-title">İnternet'ten Resim Ekle</h3>
		<div class="describe">Resim URL: 
		<input id="src" type="text" name="imageurl"><br/>
		<input type="submit" class="button" value="Ekle">
		</div>
		
		</form>
		<?php
		
		if ( $attach_id )  {
			$this->media_upload_type_form("image", $errors, $attach_id);
		}
	}
	
	
	/*
	 * modification from media.php function
	 *
	 * @param unknown_type $type
	 * @param unknown_type $errors
	 * @param unknown_type $id
	 */
	function media_upload_type_form($type = 'file', $errors = null, $id = null) {

		$post_id = isset( $_REQUEST['post_id'] )? intval( $_REQUEST['post_id'] ) : 0;

		$form_action_url = admin_url("media-upload.php?type=$type&tab=type&post_id=$post_id");
		$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);
		?>

		<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form type-form validate" id="<?php echo $type; ?>-form">
		<input type="submit" class="hidden" name="save" value="" />
		<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
		<?php wp_nonce_field('media-form'); ?>

		<script type="text/javascript">
		//<![CDATA[
		jQuery(function($){
			var preloaded = $(".media-item.preloaded");
			if ( preloaded.length > 0 ) {
				preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
			}
			updateMediaForm();
		});
		//]]>
		</script>
		<div id="media-items">
		<?php
		if ( $id ) {
			if ( !is_wp_error($id) ) {
				add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2);
				echo get_media_items( $id, $errors );
			} else {
				echo '<div id="media-upload-error">'.esc_html($id->get_error_message()).'</div>';
				exit;
			}
		}
		?>
		</div>
		<p class="savebutton ml-submit">
		<input type="submit" class="button" name="save" value="<?php esc_attr_e( 'Save all changes' ); ?>" />
		</p>
		</form>
		
		<?php
	}
}

new GrabAndSave();
?>
