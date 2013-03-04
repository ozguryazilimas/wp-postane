<?php
/*
Plugin Name: Editable Comments
Plugin URI: http://julienappert.com/realisations/plugin-editable-comments
Description: Allows users to edit or delete their own comment.
Version: 0.3.3
Author: Julien Appert
Author URI: http://julienappert.com
*/
class WPEditableComments{

	function WPEditableComments(){$this->__construct();}
		
	function __construct(){
		add_action('init', array(&$this, 'init'));
		add_action('wp', array(&$this, 'wp'));
		add_action('wp_head',array(&$this,'wp_head'));
		add_action('admin_menu', array(&$this,'admin_menu'));
		add_action('admin_head',array(&$this,'admin_head'));		
		add_filter('comment_text',array(&$this,'comment_notification'));
		register_activation_hook( __FILE__, array(&$this,'activate') );
	}
	function activate(){
		if(!get_option('editable-comments')){
			add_option('editable-comments', array('minutes'=>30,'deleteminutes'=>1,'delete'=>'','dialog'=>1,'promo'=>1));
		}		
	}
	
	function admin_menu(){
		add_options_page('Editable Comments', 'Editable Comments', 8, 'editable-comments.php',array(&$this,'adminpage'));
	}
	
	function admin_head(){
		if(is_admin() && $_SERVER['QUERY_STRING'] == 'page=editable-comments.php'){
			?>
			<style type="text/css">
			#editablecomment p{	overflow:hidden;	}
			#editablecomments p input.text{	height:25px; width:50px;}
			#editablecomments h3{margin:0; height:25px; line-height:25px; padding:0 20px; cursor:normal;}
			#editablecomments .inside{	padding:0 20px;  }
			#editablecomments #post-body-content{	background:#fff;}
			#editablecomments .links{	overflow:hidden;}
			#editablecomments .links a{	display:block; width:48px;}
			#editablecomments .links a.site{	float:left; margin-left:30px;}
			#editablecomments .links a.twitter{	float:right; margin-right:30px;}
			#editablecomments .links a img{border:0;}
			#editablecomments form.donate { text-align:center;}
			#editablecomments pre{	 background:#E6E6E6; width:100%; padding:10px; overflow:auto; }
			</style>
			<?php
		}
	}
	
	function adminpage(){ 
		if(isset($_POST['editablecomments-submit'])){
				update_option('editable-comments',array('minutes'=>$_POST['editablecomments-minutes'],'deleteminutes'=>$_POST['editablecomments-delete-minutes'],'delete'=>$_POST['editablecomments-delete'],'dialog'=>(bool)$_POST['editablecomments-dialog'],'promo'=>(bool)$_POST['editablecomments-promo']));
		}
		$editablecomments = get_option('editable-comments');
		$minutes = $editablecomments['minutes'];
		$deleteminutes = $editablecomments['deleteminutes'];
		$delete = $editablecomments['delete'];
		$dialog = $editablecomments['dialog'];
		$promo = $editablecomments['promo'];
	?>
		<div class="wrap" id="editablecomments">
			<h2><?php  _e("Editable Comments options","editablecomments"); ?></h2>	
			<div id="poststuff" class="metabox-holder has-right-sidebar">
			
				<div  class="inner-sidebar">
					<div class="postbox ">
						<h3><?php _e('Informations','editablecomments'); ?></h3>
						<div class="inside">
							<p><?php _e('Plugin developed by','editablecomments'); ?> Julien Appert:</p>
							<p class="links">
								<a class="site" href="http://julienappert.com" title="<?php _e('independent web developer','editablecomments'); ?>"><img src="<?php echo WP_PLUGIN_URL; ?>/editable-comments/home.png" alt="" /></a>
								<a class="twitter" href="http://twitter.com/julienappert" title="<?php _e('Follow me on twitter','editablecomments'); ?>"><img src="<?php echo WP_PLUGIN_URL; ?>/editable-comments/twitter.png" alt="" /></a>
							</p>			
							<p><strong><?php _e('Does this plugin help you ? Help keep it actively developed by clicking the donate button.','editablecomments'); ?></strong></p>
							<form class="donate" action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
								">
								<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
								<img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
							</form>							
						</div>
					</div>
				</div>

				<div id="post-body">
					<div id="post-body-content">
						<div class="stuffbox">

							<h3><?php _e('Configuration','editablecomments'); ?></h3>
							<div class="inside">
								<form action="" method="post">
									<p>
										<label for="editablecomments-minutes"><?php _e('Time before edit expiration:','editablecomments'); ?></label>
										<input type="text" class="text" name="editablecomments-minutes" id="editablecomments-minutes" value="<?php echo $minutes; ?>" /> <?php _e('minutes','editablecomments'); ?>
									</p>					
									<p>
										<input type="checkbox" class="checkbox" name="editablecomments-dialog" id="editablecomments-dialog" <?php if($dialog == 1){ echo 'checked="checked"';	} ?> />
										<label for="editablecomments-dialog"><?php _e('Use the javascript dialog box','editablecomments'); ?></label>
									</p>
									<p>
										<input type="checkbox" <?php if($delete == 'on') echo 'checked="checked"'; ?> name="editablecomments-delete" id="editablecomments-delete" />
										<label for="editablecomments-delete"><?php _e('Allow delete for users'); ?></label>
									</p>
									<p>
										<label for="editablecomments-delete-minutes"><?php _e('Time before delete expiration:','editablecomments'); ?></label>
										<input type="text" class="text" name="editablecomments-delete-minutes" id="editablecommentsdelete-minutes" value="<?php echo $deleteminutes; ?>" /> <?php _e('minutes','editablecomments'); ?>
									</p>									
									<p>
										<input type="checkbox" class="checkbox" name="editablecomments-promo" id="editablecomments-promo" <?php if($promo == 1){ echo 'checked="checked"';	} ?> />
										<label for="editablecomments-promo"><?php _e('Help to promote this plugin by keeping this box checked (show a link in the edit form)','editablecomments'); ?></label>
									</p>									
									<p class="submit">
										<input type="submit" name="editablecomments-submit" class="button-primary" value="<?php echo _e('Save the configuration','editablecomments'); ?>" />
									</p>					
								</form>
							</div>
						</div>
						<div class="stuffbox">
							<h3><?php _e('Installation','editablecomments'); ?></h3>
							<div class="inside">
								<p><?php _e('Copy/paste the following codes in the comments loop (into comments.php or functions.php if your template uses <code>wp_list_comments()</code>):','editablecomments'); ?></p>
								<pre>&lt;?php if  ( class_exists( 'WPEditableComments'  ) ) { WPEditableComments::edit('Modify'); } ?&gt;</pre>
								<pre>&lt;?php if  ( class_exists( 'WPEditableComments'  ) ) { WPEditableComments::delete('Delete'); } ?&gt;</pre>								
								<p><?php _e('These two functions accept three parameters:','editablecomments'); ?> <code>edit($text,$before,$after);</code> <?php _e('and','editablecomments'); ?> <code>delete($text,$before,$after);</code></p>
							</div>
						</div>
					</div>
				</div>
									
			</div>			
		</div>
			
	<?php 
	}
			
	
	function qualifications($comment,$type){
		global $post, $current_user;

        if (($comment->comment_author == $current_user->user_login) || (current_user_can( 'manage_options' )))
			return true;
		return false;	
	}
	
	// for backward compatibility
	function link($text = 'Edit', $before = '',$after = ''){
		WPEditableComments::edit($text,$before,$after);
	}
	
	function edit($text = 'Edit', $before = '',$after = ''){
		global $comment, $post;
		if(WPEditableComments::qualifications($comment,'edit')){
			$perma_struct = get_option('permalink_structure');
			if(strlen($perma_struct)==0)	$get = '&';
			else	$get = '?';
			echo $before.'<a class="editable-comment dialog" href="'.get_permalink().$get.'editable-comments='.$comment->comment_ID.'" rel="nofollow">'.$text.'</a>'.$after;
		}
	}
	
	function delete($text = 'Delete',$before = '',$after = ''){
		global $comment, $post;
		$editablecomments = get_option('editable-comments');
		$delete = $editablecomments['delete'];
		if(WPEditableComments::qualifications($comment,'delete') && $delete == 'on'){
			$perma_struct = get_option('permalink_structure');
			if(strlen($perma_struct)==0)	$get = '&';
			else	$get = '?';
			echo $before.'<a class="editable-deletecomment" href="'.get_permalink().$get.'editable-deletecomments='.$comment->comment_ID.'" rel="nofollow">'.$text.'</a>'.$after;
		}		
	}
	
	function dateValid($date,$type){
		$date = mysql2date('U',$date);
		$editablecomments = get_option('editable-comments');
		if($type == 'edit')
			$minutes = $editablecomments['minutes'];
		if($type == 'delete')
			$minutes = $editablecomments['deleteminutes'];		
		if($date >= time() - $minutes * 60)
			return true;

			return true;	
		
	}
	
	function init(){
		global $wp;	
		$locale = get_locale ();
		if ( empty($locale) )
			$locale = 'en_US';

		$mofile = dirname (__FILE__)."/locale/$locale.mo";
		load_textdomain ('editablecomments', $mofile);

		
		$wp->add_query_var('editable-comments');
		$wp->add_query_var('editable-deletecomments');
		$wp->add_query_var('editable-comments-notification');
		if(!is_admin() ){
			$editablecomments = get_option('editable-comments');
			if($editablecomments['dialog'] == 1){
				wp_enqueue_script('jquery-ui-dialog');
				wp_enqueue_script('editableComments',WP_PLUGIN_URL.'/editable-comments/editable-comments.js', array('jquery'));
			}
			wp_enqueue_style( 'dialog', WP_PLUGIN_URL.'/editable-comments/dialog/styles.css');
		}
	}
	
	function wp_head(){
		?>
		<script type="text/javascript">
		var imgLoader = '<?php echo WP_PLUGIN_URL; ?>/editable-comments/dialog/loadingAnimation.gif';
		jQuery(document).ready(function($){   
			$('a.editable-deletecomment').click(function(event){
				var conf = confirm('<?php _e('Confirm you want delete this comment ?','editablecomments'); ?>');
				if(!conf) event.preventDefault();
			});
		});		
		</script>
		<?php
	}
	
	function wp(){
		global $wp,$post;
		// le formulaire d'édition
		if(isset($wp->query_vars['editable-comments'])){
			$editable_comment = get_comment($wp->query_vars['editable-comments']);
			if($editable_comment){
				if($this->qualifications($editable_comment,'edit')){
					$options = get_option('editable-comments');
					$promo = $options['promo'];
					include('editable-form.php');
					exit;
				}
				else{	echo 'fail'; exit;	}
			}
		}
		if(isset($wp->query_vars['editable-deletecomments'])){
			$editable_comment = get_comment($wp->query_vars['editable-deletecomments']);
			if($editable_comment){
				if($this->qualifications($editable_comment,'delete')){
					wp_delete_comment($editable_comment->comment_ID, true);
				}
				wp_redirect(get_permalink($post->ID));
			}
		}		
		// l'édition
		if(isset($_POST['editable_comments_form'])){
			$editable_comment = get_comment($_POST['comment_ID']);
			if($editable_comment){
				if($this->qualifications($editable_comment,'edit')){
					if(isset($_POST['editable_comments_form'])){
						$commentarr = array('comment_ID' => $_POST['comment_ID'], 'comment_content' => $_POST['comment']);
						wp_update_comment( $commentarr);
						if($_POST['ajax'] == 'true'){
							echo 1;
							exit;
						}
					}
				}
				else{	echo 'fail'; exit;	}
			}
		}		
	}
	
	
	function comment_notification($comment_text){	
		global $comment, $wp;
		if(isset($wp->query_vars['editable-comments-notification']) || isset($_POST['editable_comments_form'])){
				if(in_array($comment->comment_ID, array($_POST['comment_ID'],$wp->query_vars['editable-comments-notification'])) )
					$comment_text .= '<p><span style="color:green">'.__('Your comment has been updated.','editablecomments').'</span></p>';
		}
		return $comment_text;
	}
}
new WPEditableComments();
?>
