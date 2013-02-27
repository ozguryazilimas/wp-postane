<?php

function bp_menu_item_slugs( $menu_items, $args ) {

	global $bp;
	$bp_user_link = $bp->loggedin_user->domain;
	
	
	//filter the links to the correct user slug
    foreach ($menu_items as $key => $item) {
	
    if ( is_user_logged_in() ){
    
 		if ( strstr( $item->xfn, 'my-profile' ) ) {
	    	$menu_items[$key]->url = $bp_user_link . 'profile';
            if (bp_is_member()) {
               	$menu_items[$key]->classes[] = "current-menu-item";
           }           
        }
        
        if ( strstr( $item->xfn, 'user-nav-my-profile-view' ) ) {
            $menu_items[$key]->url = $bp_user_link . 'profile';    
        }
        if ( strstr( $item->xfn, 'user-nav-my-profile-edit' ) ) {
            $menu_items[$key]->url = $bp_user_link . 'profile/edit/';    
        }       
        if ( strstr( $item->xfn, 'user-nav-my-profile-avatar' ) ) {
            $menu_items[$key]->url = $bp_user_link . 'profile/change-avatar/';    
        }
        
                
        if ( strstr( $item->xfn, 'my-activity' ) ) {
            $menu_items[$key]->url = $bp_user_link . $bp->activity->slug;    
        }
        
        	if ( strstr( $item->xfn, 'my-activity-favorites' ) ) {
            	$menu_items[$key]->url = $bp_user_link . $bp->activity->slug . '/favorites/';
            }
        
        if ( strstr( $item->xfn, 'my-messages' ) ) {
            $menu_items[$key]->url = $bp_user_link . $bp->messages->slug;   
        }
        
        	if ( strstr( $item->xfn, 'my-messages-inbox' ) ) {
            	$menu_items[$key]->url = $bp_user_link . $bp->messages->slug;
            }

        	if ( strstr( $item->xfn, 'my-messages-sent' ) ) {
            	$menu_items[$key]->url = $bp_user_link . $bp->messages->slug . '/sentbox/';
            }

        	if ( strstr( $item->xfn, 'my-messages-compose' ) ) {
            	$menu_items[$key]->url = $bp_user_link . $bp->messages->slug . '/compose/';
            }
            
        	if ( strstr( $item->xfn, 'my-messages-notices' ) ) {
            	$menu_items[$key]->url = $bp_user_link . $bp->messages->slug . '/notices/';
            }
        
        if ( strstr( $item->xfn, 'my-groups' ) ) {
            $menu_items[$key]->url = $bp_user_link . $bp->groups->slug;
        }
	        if ( strstr( $item->xfn, 'my-group-memberships' ) ) {
	            $menu_items[$key]->url = $bp_user_link . $bp->groups->slug;
	        }
	        if ( strstr( $item->xfn, 'my-group-invites' ) ) {
	            $menu_items[$key]->url = $bp_user_link . $bp->groups->slug . '/invites/';
	        }
	                
        if ( strstr( $item->xfn, 'user-nav-my-topics' ) ) {
            $menu_items[$key]->url = $bp_user_link . $bp->forums->slug;
        }
        
	        if ( strstr( $item->xfn, 'my-forums-started' ) ) {
	            $menu_items[$key]->url = $bp_user_link . $bp->forums->slug;
	        }
	
	        if ( strstr( $item->xfn, 'my-forums-replied' ) ) {
	            $menu_items[$key]->url = $bp_user_link . $bp->forums->slug . '/replies/';
	        }
        
        if ( strstr( $item->xfn, 'my-settings' ) ) {
            $menu_items[$key]->url = $bp_user_link . $bp->settings->slug;
        }
	        if ( strstr( $item->xfn, 'my-settings-notifications' ) ) {
	            $menu_items[$key]->url = $bp_user_link . $bp->settings->slug . '/notifications/';
	        }        
	        if ( strstr( $item->xfn, 'my-settings-delete-account' ) ) {
	            $menu_items[$key]->url = $bp_user_link . $bp->settings->slug . '/delete-account/';
	        } 
	                    
     } else {
	     
	     // remove user links on logout
        if ( strstr( $item->xfn, 'user-nav' ) ) unset( $menu_items[$key] );

      }
    
      	//directory link routers
        if ( strstr( $item->xfn, 'directory-activity' ) ) {
            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->activity->root_slug ;
        }
        
        	if ( strstr( $item->xfn, 'all-activity' ) ) {
        		$menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->activity->root_slug ;
            }
                            
        if ( strstr( $item->xfn, 'directory-forums' ) ) {
            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->forums->root_slug;
        }
	        if ( strstr( $item->xfn, 'all-topics-forums' ) ) {
	            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->forums->root_slug;
	        }
        
        if ( strstr( $item->xfn, 'directory-members' ) ) {
            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->members->root_slug ;
        }
	        if ( strstr( $item->xfn, 'all-members' ) ) {
	            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->members->root_slug ;
	        }
        
        if ( strstr( $item->xfn, 'directory-groups' ) ) {
            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->groups->root_slug;
        }
	        if ( strstr( $item->xfn, 'bp-directory-all-groups-group' ) ) {
	            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->groups->root_slug ;
	        }        
        
                    if ( is_user_logged_in() ){
	        	if ( strstr( $item->xfn, 'my-friends-activity' ) ) {
	        		$menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->activity->root_slug ;
	            }
	
	        	if ( strstr( $item->xfn, 'my-groups-activity' ) ) {
	        		$menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->activity->root_slug ;
	            }
	            
	        	if ( strstr( $item->xfn, 'my-favorites-activity' ) ) {
	        		$menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->activity->root_slug ;
	            }
	            
	        	if ( strstr( $item->xfn, 'my-mentions-activity' ) ) {
	        		$menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->activity->root_slug ;
	            }
	        	if ( strstr( $item->xfn, 'my-friends-members' ) ) {
	        		$menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->members->root_slug ;
	            }
		        if ( strstr( $item->xfn, 'my-topics-forums' ) ) {
		            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->forums->root_slug;
		        }
		        if ( strstr( $item->xfn, 'bp-user-directory-my-groups-group' ) ) {
		            $menu_items[$key]->url = bp_get_root_domain() . '/' . $bp->groups->root_slug;
		        }	            
	         

            } else {
	
	            if ( strstr( $item->xfn, 'user-directory' ) ) unset( $menu_items[$key] );
	        }

        
     }
    
    return $menu_items;
       
}
add_filter( 'wp_nav_menu_objects', 'bp_menu_item_slugs', 10, 2 );

?>