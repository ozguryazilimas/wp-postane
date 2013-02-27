<?php

/**
 * BuddyPress Menus Metabox Class
 *
 * Functions for creating metabox menu items
 *
 * @package BuddyPress Menus
 *
 */

class BP_Menus{

public function load(){
      //add the metabox to the admin menu page
      add_action( 'admin_init', array(__CLASS__,'add_meta_box'));

      // javascript for the meta box
      add_action( 'admin_enqueue_scripts', array(__CLASS__,'metabox_script') );


      //callback to create menu item and add it to menu
      add_action('wp_ajax_my-add-post-type-buddypress-links', array( __CLASS__, 'ajax_add_post_type'));

      //add menu item the appropriate url
      add_filter( 'wp_setup_nav_menu_item',  array(__CLASS__,'setup_buddypress_item') );
 }
 

 public function add_meta_box() {
      add_meta_box( 'add-buddypress', __('BuddyPress','my-post-type-buddypress-links'),array(__CLASS__,'metabox'),'nav-menus' ,'side','default');
 }

 public function metabox( ) {
      global $nav_menu_selected_id;

      	//array of profile menu items
      	$profile_items = array( 
      	'profile'  => array( 'title' => 'Profile',
							 'class' => 'bp-menu-top-item',
							 'component' => 'xprofile',
							 'xfn'   => 'bp-user-nav-my-profile'
							),
				    'profile-view' => array( 'title' => 'View',
									 		 'class' => 'bp-menu-sub-item',
									 		 'component' => 'xprofile',
									 		 'xfn'   => 'bp-user-nav-my-profile-view'
										),

				    'profile-edit' => array( 'title' => 'Edit',
									 		 'class' => 'bp-menu-sub-item',
									 		 'component' => 'xprofile',
									 		 'xfn'   => 'bp-user-nav-my-profile-edit'
										),
				    'profile-avatar' => array( 'title' => 'Change Avatar',
									 		   'class' => 'bp-menu-sub-item',
									 		   'component' => 'xprofile',
									 		   'xfn'   => 'bp-user-nav-my-profile-avatar'
										),
																	
				'activity' => array( 'title' => 'Activity',
								 	 'class' => 'bp-menu-main-item',
								 	 'component' => 'activity',
								 	 'xfn'   => 'bp-user-nav-my-activity'
									),
									
				    'activity-personal' => array( 'title' => 'Personal',
									 			  'class' => 'bp-menu-sub-item',
									 			  'component' => 'activity',
									 			  'xfn'   => 'bp-user-nav-my-activity-personal'
										),

				    'activity-favorites' => array( 'title' => 'Favorites',
									 			   'class' => 'bp-menu-sub-item',
									 			   'component' => 'activity',
									 			   'xfn'   => 'bp-user-nav-my-activity-favorites'
										),
					
									
				'group'    => array( 'title' => 'Groups',
									 'class' => 'bp-menu-main-item',
									 'component' => 'groups',
									 'xfn'   => 'bp-user-nav-my-profile-groups'
									),									
					'group-memberships'    => array( 'title' => 'My Memberships',
										 'class' => 'bp-menu-sub-item',
										 'component' => 'groups',
										 'xfn'   => 'bp-user-nav-my-group-memberships'
										),
					'group-invites'    => array( 'title' => 'My Invites',
										 'class' => 'bp-menu-sub-item',
										 'component' => 'groups',
										 'xfn'   => 'bp-user-nav-my-group-invites'
										),										
									
									
									
				'forum'    => array( 'title' => 'Forums',
								     'class' => 'bp-menu-main-item',
								     'component' => 'forums',
								     'xfn'   => 'bp-user-nav-my-forums'
									),
				    'forum-started' => array( 'title' => 'Topics Started',
									 'class' => 'bp-menu-sub-item',
									 'component' => 'forums',
									 'xfn'   => 'bp-user-nav-my-forums-started'
										),

				    'forum-replied' => array( 'title' => 'Replied To',
									 'class' => 'bp-menu-sub-item',
									 'component' => 'forums',
									 'xfn'   => 'bp-user-nav-my-forums-replied'
										),
									
									
									
				'messages' => array( 'title' => 'Messages',
									 'class' => 'bp-menu-main-item',
									 'component' => 'messages',
									 'xfn'   => 'bp-user-nav-my-messages'
									),
				    'messages-sent' => array( 'title' => 'Sent Messages',
									 'class' => 'bp-menu-sub-item',
									 'component' => 'messages',
									 'xfn'   => 'bp-user-nav-my-messages-sent'
										),
				    'messages-compose' => array( 'title' => 'Compose Message',
									 'class' => 'bp-menu-sub-item',
									 'component' => 'messages',
									 'xfn'   => 'bp-user-nav-my-messages-compose'
										),
				    'messages-notices' => array( 'title' => 'Notices',
									 'class' => 'bp-menu-sub-item',
									 'component' => 'messages',
									 'xfn'   => 'bp-user-nav-my-messages-notices'
										),			         											
										
									
				'settings' => array( 'title' => 'Settings',
									 'class' => 'bp-menu-main-item',
									 'component' => 'settings',
									 'xfn'   => 'bp-user-nav-my-settings'
									),
					'settings-notifications' => array( 'title' => 'Notifications',
										 'class' => 'bp-menu-sub-item',
										 'component' => 'settings',
										 'xfn'   => 'bp-user-nav-my-settings-notifications'
										),
					'settings-delete-account' => array( 'title' => 'Delete Account',
										 'class' => 'bp-menu-sub-item',
										 'component' => 'settings',
										 'xfn'   => 'bp-user-nav-my-settings-delete-account'
										)										
	);
  	
      	//array of directory menu items
       	$directory_items = array( 
       	'activity' => array( 'title' => 'Activity',
							 'class' => 'bp-menu-top-item',
							 'component' => 'activity',
							 'xfn'   => 'bp-directory-activity'
							),         											
				'all-activity' => array( 'title' => 'All Activity',
							             'class' => 'bp-menu-main-item',
							             'component' => 'activity',
							             'xfn'   => 'bp-directory-all-activity'
							),
				'my-friends-activity'  => array( 'title' => 'My Friends',
							             'class' => 'bp-menu-main-item',
							             'component' => 'activity',
							             'xfn'   => 'bp-directory-my-friends-activity'
							),
				'my-groups-activity'  => array( 'title' => 'My Groups',
							             'class' => 'bp-menu-main-item',
							             'component' => 'activity',
							             'xfn'   => 'bp-directory-my-groups-activity'
							),         											
				'my-favorites-activity'  => array( 'title' => 'My Favorites',
							             'class' => 'bp-menu-main-item',
							             'component' => 'activity',
							             'xfn'   => 'bp-directory-my-favorites-activity'
							),
				'my-mentions-activity'  => array( 'title' => 'My Mentions',
							             'class' => 'bp-menu-main-item',
							             'component' => 'activity',
							             'xfn'   => 'bp-directory-my-mentions-activity'
							),         											
							         											
		  'members' => array( 'title' => 'Members',
							  'class' => 'bp-menu-top-item',
							  'component' => 'members',
							  'xfn'   => 'bp-directory-members'
							),
				'all-members' => array( 'title' => 'All Members',
							            'class' => 'bp-menu-main-item',
							            'component' => 'members',
							            'xfn'   => 'bp-directory-all-members'
							),
				'my-friends'  => array( 'title' => 'My Friends',
							            'class' => 'bp-menu-main-item',
							            'component' => 'friends',
							            'xfn'   => 'bp-user-directory-my-friends-members'
							),
	
		  'groups' => array( 'title' => 'Groups',
							 'class' => 'bp-menu-top-item',
							 'component' => 'groups',
							 'xfn'   => 'bp-directory-groups'
							),
				'all-groups' => array( 'title' => 'All Groups',
							           'class' => 'bp-menu-main-item',
							           'component' => 'groups',
							           'xfn'   => 'bp-directory-all-groups-group'
							),
				'my-groups'  => array( 'title' => 'My Groups',
							           'class' => 'bp-menu-main-item',
							           'component' => 'groups',
							           'xfn'   => 'bp-user-directory-my-groups-group'
							),
						
		  'forums' => array( 'title' => 'Forums',
							 'class' => 'bp-menu-top-item',
							 'component' => 'forums',
							 'xfn'   => 'bp-directory-forums'
							),
				'all-topics' => array( 'title' => 'All Topics',
							           'class' => 'bp-menu-main-item',
							           'component' => 'forums',
							           'xfn'   => 'bp-directory-all-topics-forums'
							),
				'my-topics'  => array( 'title' => 'My Topics',
							           'class' => 'bp-menu-main-item',
							           'component' => 'forums',
							           'xfn'   => 'bp-user-directory-my-topics-forums'
							)         											
	);
	
	 $i = 1; // var for item loops
	
	 //remove items from inactive components
    $comp_items = array(activity, groups, forums, messsages, xprofile, settings, friends);
    
    foreach ( $comp_items as $item_name ):
    
    
	if( !bp_is_active($item_name) ) {
		foreach ($profile_items as $key => $item) {
			if ($item['component'] === $item_name) {
				unset($profile_items[$key]);
			}
		}
	
		foreach ($directory_items as $key => $item) {
			if ($item['component'] === $item_name) {
				unset($directory_items[$key]);
			}
		}
	}  
	
		$i ++;
           
     endforeach;


    ?>
    <ul id="buddypress-page-tabs" class="buddypress-tabs add-menu-item-tabs">
        <li><a class="nav-tab-link" href="/wp-admin/nav-menus.php?page-tab=bp#directory-tabs">Directory</a></li>
        <li class="tabs"><a class="nav-tab-link" href="/wp-admin/nav-menus.php?page-tab=bp#user-tabs">User</a></li>
        <li><a class="nav-tab-link" href="/wp-admin/nav-menus.php?page-tab=bp#group-tabs">Group</a></li>
        <li><a class="nav-tab-link" href="/wp-admin/nav-menus.php?page-tab=bp#forum-tabs">Forum</a></li>
        <li><a class="nav-tab-link" href="/wp-admin/nav-menus.php?page-tab=bp#plugin-tabs">Plugin</a></li>
    </ul>
    <div id="user-tabs" class="tabs-panel tabs-panel-active customlinkdiv" style="border:1px; border-style: solid; border-color:#DFDFDF; padding:0.5em 0.9em; max-height:205px; overflow:auto;">
      <ul id="post-type-buddypress-checklist" class="categorychecklist form-no-clear">
      
      <?php 
    	  
	  // add profile menu items
      foreach ( $profile_items as $item ):

          $item_arr = urlencode(serialize($item));
          	
          		echo '<li class="'. $item['class'] .'"><label class="menu-item-title"><input type="checkbox" value ="' . $item_arr . '" /> ' . $item['title'] . '</label></li>';  
                          
          		$i ++;
                       
      endforeach;
      ?>
      </ul>
    </div>
    
    <div id="directory-tabs" class="tabs-panel tabs-panel-inactive customlinkdiv" style="border:1px; border-style: solid; border-color:#DFDFDF; padding:0.5em 0.9em; max-height:205px; overflow:auto;">
      <ul id="post-type-buddypress-checklist" class="categorychecklist form-no-clear">
      
      <?php 
      
      // add directory menu items
      foreach ($directory_items as $item):
      
      		$item_arr = urlencode(serialize($item));
      
           echo '<li class="'. $item['class'] .'"><label class="menu-item-title"><input type="checkbox" value ="' . $item_arr . '" /> ' . $item['title'] . '</label></li>';  
           $i ++;
           
      endforeach;?>
      </ul>
    </div>
    
    <div id="group-tabs" class="tabs-panel tabs-panel-inactive customlinkdiv" style="border:1px; border-style: solid; border-color:#DFDFDF; padding:0.5em 0.9em;">
    <p>Group menu items will come in a future update</p>
    </div>
    
    <div id="forum-tabs" class="tabs-panel tabs-panel-inactive customlinkdiv" style="border:1px; border-style: solid; border-color:#DFDFDF; padding:0.5em 0.9em; max-height:205px; overflow:auto;">
    <p>Forum menu items will come in a future update</p>
    </div>
    
    <div id="plugin-tabs" class="tabs-panel tabs-panel-inactive customlinkdiv" style="border:1px; border-style: solid; border-color:#DFDFDF; padding:0.5em 0.9em; max-height:205px; overflow:auto;">
    <p>Plugin menu items will come in a future update</p>
    </div>


      <!-- 'Add to Menu' button -->
      <p class="button-controls" >
           <span class="add-to-menu" >
           	<img class="waiting" src="/wp-admin/images/wpspin_light.gif" alt="">
                <input type="submit" id="submit-post-type-buddypress" <?php disabled( $nav_menu_selected_id, 0 ); ?> value="<?php esc_attr_e('Add to Menu'); ?>" name="add-post-type-menu-item"  class="button-secondary submit-add-to-menu" />
                
           </span>
      </p>
 <?php
 }
 

 public function metabox_script($hook) {
      if( 'nav-menus.php' != $hook )
           return;

      //On Appearance > Menu page, enqueue script: 
      wp_enqueue_script( 'my-post-type-buddypress-links_metabox', plugins_url('/metabox.js', __FILE__),array('jquery'));
      
      wp_enqueue_style( 'my-post-type-buddypress_metabox', plugins_url('metabox.css', __FILE__));

      //Add nonce variable
     wp_localize_script('my-post-type-buddypress-links_metabox','my_posttype_bp_links', array('nonce'=>wp_create_nonce('my-add-post-type-buddypress-links')));
 }
  

 public function ajax_add_post_type(){

      if ( ! current_user_can( 'edit_theme_options' ) )
           die('-1');

      check_ajax_referer('my-add-post-type-buddypress-links','posttypebuddypress_nonce');

      require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
      
      if(empty($_POST['post_types']))
           exit;

      //Create menu items and store IDs in array
      $item_ids=array();
      
      foreach ( (array) $_POST['post_types'] as $post_type) {
      
           $post_type_obj[0] = get_post_type_object($post_type);

           $items_link = urldecode($post_type);               
           $item_arr = unserialize($items_link);


	if(!$post_type_obj)
		continue;

    $menu_item_data= array(
		'menu-item-title' =>  $item_arr['title'],
		'menu-item-type' => 'buddypress',
		'menu-item-object' => '',
		'menu-item-url' => '#',
		'menu-item-classes' => '',
		'menu-item-xfn' => $item_arr['xfn'],
		'menu-item-description' => ''
	);

	//collect the items. 
	$item_ids[] = wp_update_nav_menu_item(0, 0, $menu_item_data );
      }

      //If there was an error die here
      if ( is_wp_error( $item_ids ) )
           die('-1');
           
  

      //Set up menu items
      foreach ( (array) $item_ids as $menu_item_id ) {
           $menu_obj = get_post( $menu_item_id );
           if ( ! empty( $menu_obj->ID ) ) {
                $menu_obj = wp_setup_nav_menu_item( $menu_obj );
                $menu_obj->label = $menu_obj->title;
                $menu_items[] = $menu_obj;
           }
      }

      if ( ! empty( $menu_items ) ) {
           $args = array(
                'after' => '',
                'before' => '',
                'link_after' => '',
                'link_before' => '',
                'walker' => new Walker_Nav_Menu_Edit,
           );
           echo walk_nav_menu_tree( $menu_items, 0, (object) $args );

      }

      exit;
 }


 public function setup_buddypress_item($menu_item){
 
      if($menu_item->type !='post_type_buddypress')
          return $menu_item;

      $post_type = $menu_item->object;
      $menu_item->url =get_post_type_buddypress_link($post_type);

      return $menu_item;

 }

}
BP_MENUS::load();
?>
