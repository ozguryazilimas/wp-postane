<?php


function fep_backticker_encode($text) {
	$text = $text[1];
    $text = stripslashes($text);
    $text = str_replace('&amp;lt;', '&lt;', $text);
    $text = str_replace('&amp;gt;', '&gt;', $text);
	$text = htmlspecialchars($text, ENT_QUOTES);
	$text = preg_replace("|\n+|", "\n", $text);
	$text = nl2br($text);
    $text = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $text);
	$text = preg_replace("/^ /", '&nbsp;', $text);
    $text = preg_replace("/(?<=&nbsp;| |\n) /", '&nbsp;', $text);
    
    return "<code>$text</code>";
}

function fep_backticker_display_code($text) {
    $text = preg_replace_callback("|`(.*?)`|", "fep_backticker_encode", $text);
    $text = str_replace('<code></code>', '`', $text);
    return $text;
}
add_filter('fep_filter_display_message', 'fep_backticker_display_code', 5);

function fep_message_filter_content($html) {
    $html = apply_filters('the_content', $html);
    return $html;
}
add_filter( 'fep_filter_display_message', 'fep_message_filter_content' );

function fep_message_filter_title($html) {
    $html = apply_filters('the_title', $html);
    return $html;
}
add_filter( 'fep_filter_display_title', 'fep_message_filter_title' );

function fep_autosuggestion_ajax() {
global $wpdb, $user_ID;

if(fep_get_option('hide_autosuggest') == '1' && !current_user_can('manage_options'))
die();

if ( check_ajax_referer( 'fep-autosuggestion', 'token', false )) {
$SQL_FROM = $wpdb->users;
$SQL_WHERE = 'display_name';

// do a version check
$version = get_bloginfo('version');
if ($version < 4.0 ) {
//Previous version
$searchq = like_escape ($_POST['searchBy']);
} else {
$searchq = $wpdb->esc_like ($_POST['searchBy']);
} 

$search = '%'.$searchq.'%';
$getRecord_sql = $wpdb->prepare("SELECT ID,user_login,display_name FROM {$SQL_FROM} WHERE {$SQL_WHERE} LIKE %s LIMIT 5",$search);
$rows = $wpdb->get_results($getRecord_sql);
if(strlen($searchq)>0)
{
	echo "<ul>";
	if ($wpdb->num_rows)
	{
		foreach($rows as $row)
		{
			if($row->ID != $user_ID) //Don't let users message themselves
			{
				
				?>
				<li><a href="#" onClick="fep_fill_autosuggestion('<?php echo $row->user_login; ?>','<?php echo $row->display_name; ?>');return false;"><?php echo $row->display_name; ?></a></li>
				<?php
			
			}
		}
	}
	else
		echo "<li>".__("No Matches Found", 'fep')."</li>";
	echo "</ul>";
}
}
die();
}

add_action('wp_ajax_fep_autosuggestion_ajax','fep_autosuggestion_ajax');	

function header_note() {
	$numNew = fep_get_new_message_number();
	$s = ( $numNew > 1 ) ? 's': '';
	
	echo __('You have', 'fep')." (<font color='red'>$numNew</font>) ".__("new message{$s}", 'fep');
	}
add_action ('fep_header_note',  'header_note');

function fep_notification() 
		{
			$New_mgs = fep_get_new_message_number();
				$sm = ( $New_mgs > 1 ) ? 's': '';
				
				$New_ann = 0;
			if( class_exists('fep_announcement_class') )
				$New_ann = fep_announcement_class::init()->getAnnouncementsNum();
				$sa = ( $New_ann > 1 ) ? 's': '';
	
			if ( $New_mgs || $New_ann ) {
				$show = __("You have", 'fep');
	
			if ( $New_mgs )
				$show .= "<a href='".fep_action_url()."'>".sprintf(__(" %d new message%s", 'fep'), $New_mgs, $sm ).'</a>';
	
			if ( $New_mgs && $New_ann )
				$show .= __(' and', 'fep');
	
			if ( $New_ann )
				$show .= "<a href='".fep_action_url('announcements')."'>".sprintf(__(" %d new announcement%s", 'fep'), $New_ann, $sa ).'</a>';
	
			echo "<div id='fep-notification-bar'>$show</div>";
				}
		}
		
if ( fep_get_option('hide_notification',0) != 1 )
add_action('wp_head', 'fep_notification');
		
		
function fep_send_new_message_check( $errors )
			{
			if ( '1' == fep_get_option('disable_new') && !current_user_can('manage_options') )
				$errors->add('disable_new', __("Send new message is disabled for users!", 'fep'));
				
			}
			
		
add_action('fep_before_send_new_message', 'fep_send_new_message_check');
		
function fep_send_new_message_filter( $newMsg )
			{
			if ( '1' == fep_get_option('disable_new') && !current_user_can('manage_options') )
				$newMsg = "<div id='fep-error'>".__('Send new message is disabled for users!', 'fep')."</div>";
				
				 return $newMsg ;
				
			}
			
		
add_filter('fep_filter_new_message_form', 'fep_send_new_message_filter');

function fep_check_db()
    {
	global $wpdb;
	$version = fep_get_version();
      if ( get_option( "fep_db_version" ) != $version['dbversion'] || get_option( "fep_meta_db_version" ) != $version['metaversion'] )
	  	{
			$wpdb->query( "ALTER TABLE ".FEP_META_TABLE." CHANGE COLUMN id meta_id int(11) NOT NULL auto_increment" );
	  		fep_plugin_activate();
			//var_dump('db_check');
		}
    }	
	
add_action('plugins_loaded', 'fep_check_db');

function fep_show_code_post_help()
    {
	echo '<p>Put code in between <code>`backticks`</code></p>';
    }	

add_action('fep_message_form_after_content', 'fep_show_code_post_help', 5 );
add_action('fep_reply_form_after_content', 'fep_show_code_post_help', 5 );	
add_action('fep_announcement_form_after_content', 'fep_show_code_post_help', 5 );