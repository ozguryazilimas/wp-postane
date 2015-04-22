<?php


function fep_backticker_encode($text) {
	$text = $text[1];
    //$text = stripslashes($text); //already done
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
wp_die();

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
wp_die();
}

add_action('wp_ajax_fep_autosuggestion_ajax','fep_autosuggestion_ajax');	

function header_note() {
	$numNew = fep_get_new_message_number();
	$sm = ( $numNew != 1 ) ? __('new messages', 'fep'): __('new message', 'fep');
	
	echo __('You have', 'fep')." (<font color='red'>$numNew</font>) $sm";
	}
add_action ('fep_header_note',  'header_note');
		
		
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
      if ( get_option( "fep_db_version" ) != FEP_DB_VERSION || get_option( "fep_meta_db_version" ) != FEP_META_VERSION )
	  	{
			$wpdb->query( "ALTER TABLE ".FEP_META_TABLE." CHANGE COLUMN id meta_id int(11) NOT NULL auto_increment" );
	  		fep_plugin_activate();
			//var_dump('db_check');
		}
    }	
	
add_action('plugins_loaded', 'fep_check_db');

function fep_show_code_post_help()
    {
	echo '<p>' . __('Put code in between', 'fep'). ' <code>`'. __('backticks', 'fep').'`</code></p>';
    }	

add_action('fep_message_form_after_content', 'fep_show_code_post_help', 5 );
add_action('fep_reply_form_after_content', 'fep_show_code_post_help', 5 );	
add_action('fep_announcement_form_after_content', 'fep_show_code_post_help', 5 );

function fep_footer_credit()
    {
	if ( fep_get_option('hide_branding',0) == 1 )
				return;
	echo "<div><a href='http://frontendpm.blogspot.com/2015/03/front-end-pm.html' target='_blank'>Front End PM</a></div>";
    }	

add_action('fep_footer_note', 'fep_footer_credit' );

function fep_notification() 
		{
			if ( ! is_user_logged_in() )
				return;
			if ( fep_get_option('hide_notification',0) == 1 )
				return;
			
			$New_mgs = fep_get_new_message_number();
			$sm = ( $New_mgs != 1 ) ? __('new messages', 'fep'): __('new message', 'fep');
				
				$New_ann = 0;
				$show = '';
			if( class_exists('fep_announcement_class') )
				$New_ann = fep_announcement_class::init()->getAnnouncementsNum();
				$sa = ( $New_ann != 1 ) ? __('new announcements', 'fep'): __('new announcement', 'fep');
	
			if ( $New_mgs || $New_ann ) {
				$show = __("You have", 'fep');
	
			if ( $New_mgs )
				$show .= "<a href='".fep_action_url('messagebox')."'> $New_mgs $sm</a>";
	
			if ( $New_mgs && $New_ann )
				$show .= ' ' .__('and', 'fep');
	
			if ( $New_ann )
				$show .= "<a href='".fep_action_url('announcements')."'> $New_ann $sa</a>";
				
				}
				return apply_filters('fep_header_notification', $show);
		}
			

function fep_notification_div() {
	if ( ! is_user_logged_in() )
				return;
	if ( fep_get_option('hide_notification',0) == 1 )
				return;
				
	wp_enqueue_script( 'fep-notification-script' );
	$notification = fep_notification();
	if ( $notification )
	echo "<div id='fep-notification-bar'>$notification</div>";
	else
	echo "<div id='fep-notification-bar' style='display: none'></div>";
	}

add_action('wp_head', 'fep_notification_div');

function fep_notification_ajax() {

	if ( check_ajax_referer( 'fep-notification', 'token', false )) {
	
	$notification = fep_notification();
	if ( $notification )
	echo $notification;
	}
	wp_die();
	}

add_action('wp_ajax_fep_notification_ajax','fep_notification_ajax');