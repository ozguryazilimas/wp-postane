<?php

/*
 * Make sure BuddyPress is loaded before we do anything.
 */
if ( !function_exists( 'bp_core_install' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		require_once ( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
	} else {
		add_action( 'admin_notices', 'bp_profile_menu_install_buddypress_notice' );
		return;
	}
}


function bp_profile_menu_install_buddypress_notice() {
	echo '<div id="message" class="error fade"><p style="line-height: 150%">';
	_e('<strong>BuddyPress Profile Menu</strong></a> requires the BuddyPress plugin to work. Please <a href="http://buddypress.org/download">install BuddyPress</a> first, or <a href="plugins.php">deactivate BuddyPress Profile Menu</a>.');
	echo '</p></div>';
}


function bp_profile_menu_item( $items, $args ) {

	global $bp;
	$bp_user_link = $bp->loggedin_user->domain;
   if ( $args->theme_location == 'primary' ) : 
        $items .= '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'">Profile</a><ul class="sub-menu profile-menu">';
        
     if ( bp_is_active( 'activity' )) :  
        $items .= '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'activity">Activity</a></li>';       
     endif;
        
        
     	$items .= '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'profile">Profile</a></li>';
  	
  	if ( bp_is_active( 'messages' )) :
  		$items .=   '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'messages">Messages</a></li>';
  	endif; 
  	
  	if ( bp_is_active( 'friends' )) :
  		$items .=  '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'friends">Friends</a></li>';
  	endif;
  	
  	if ( bp_is_active( 'groups' )) : 
  		$items .=   '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'groups">Groups</a></li>';
  	endif; 
  	
  	if ( bp_is_active( 'forums' )): 
  		$items .=   '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'forums">Forums</a></li>';
  	 endif; 
  	 
  	if ( bp_is_active( 'settings' )) :
  		$items .=  '<li id="menu-item" class="menu-item"><a href="'.$bp_user_link.'settings">Settings</a></li>';
  	endif;
       
    
    if ( $notifications = bp_core_get_notifications_for_user( bp_loggedin_user_id() ) ) :

        if ( $notifications ) {
            $counter = 0;
            for ( $i = 0; $i < count($notifications); $i++ ) {
                $badge = count($notifications);
                $items .= '<li id="menu-item" class="menu-item">'.$notifications[$i].'</li></ul></li>';
            }
        }


    endif;
     endif ;
     
     return $items;
}


if ( is_user_logged_in() ) :
add_filter( 'wp_nav_menu_items', 'bp_profile_menu_item', 10, 2 );
endif ;

//add CSS so sub menu isn't off screen
function add_profile_menu_css(){
?>
<style type="text/css">
ul.profile-menu{
margin-left: -65px;
}
</style>

<?php	
}
add_action('wp_head', 'add_profile_menu_css');


?>