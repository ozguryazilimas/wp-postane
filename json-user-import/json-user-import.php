<?php

/*
Plugin Name: JSON User Import
Plugin URI: http://pilli.com
Description: Allows the importation of users via an uploaded Jsonish file.
Author: ybrs
Version: 0.0.1
Author URI: http://pilli.com
*/

// always find line endings
ini_set('auto_detect_line_endings', true);

// add admin menu
add_action('admin_menu', 'csvuserimport_menu');

function csvuserimport_menu() {	
	add_submenu_page( 'users.php', 'Json User Import', 'Import', 'manage_options', 'json-user-import', 'jsonuserimport_page1');	
}



// show import form
function jsonuserimport_page1() {

	global $wpdb;

  	if (!current_user_can('manage_options')) {
    	wp_die( __('You do not have sufficient permissions to access this page.') );
  	}
    
    $base_path = realpath(dirname(__FILE__).'/../../');
    
	// if the form is submitted
	if ($_POST['mode'] == "submit") {
	        echo "importing from ".$_POST['file_url']."\n";	
		// $arr_rows = file($_FILES['json_file']['tmp_name']);
		$arr_rows = explode("\n", file_get_contents($_POST['file_url']));
                echo ">>>>".count($arr_rows)."\n"; 
		// loop around
		if (is_array($arr_rows)) {
			foreach ($arr_rows as $line) {
                echo $line."<br>";
				// split into values
				$row = json_decode($line);
				
				if ($row->login != 'admin'){
					echo "<br>============================<br>";
					print_r($row);
					echo "<br>============================<br>";				

					// firstname, lastname, username, password
					$firstname 		= $row->firstname;
					$lastname 		= $row->lastname;
					$password 		= time();
					$user_email 	= $row->email;				
				    
		            echo "check user: ". $row->login ."<br> \n";
		            $user_id = username_exists($row->login);
		            echo "user_id: ".$user_id." <br> \n";
                    echo ">>> 1 <br>";
                    $user_data = array(
                        'user_login' => $row->login,
                        'user_nicename' => sanitize_user($row->login, true),
                        'user_email' => $row->email,
                        'first_name' => $row->firstname,
                        'last_name' => $row->lastname,
                        'user_registered'  => $row->user_registered, # date( 'Y-m-d H:i:s' ),
                        'role' => 'contributor',
                        'user_pass'=>wp_generate_password()
                        
                    );
                    echo ">>> 2 ".$user_id."<br>";
                    if ($user_id){
                        // insert or update
                        $user_data['ID'] = $user_id;                    
                    }
                    echo ">>> 3 <br>";
                    
                    print_r($user_data);
                    echo ">>> 4 <br>";
                    if (!$user_id){
                    	$user_id = wp_insert_user( $user_data );
                    } else {
                        wp_update_user( array ('ID' => $user_id, 'user_registered' => $user_data['user_registered']) ) ;
                    }
				                        
					// dosya var mi bak
					$avatar_url = "/imaj/avatars/".$row->id."_b.jpg";
					if (file_exists($base_path.$avatar_url)){
						update_user_meta( $user_id, 'simple_local_avatar', 
                                                                               array( 'full' => $avatar_url ) );
					} else {
					    echo "file not exists: ".$base_path.$avatar_url."<br>";
					}
                                        
				}			

							 		
			}	// end of 'for each around arr_rows'

			$html_update = "<div class='updated'>All users appear to be have been imported successfully.</div>";
			
		} // end of 'if arr_rows is array'
		else {
			$html_update = "<div class='updated' style='color: red'>It seems the file was not uploaded correctly.</div>";			
		}
	} 	// end of 'if mode is submit'

?>
<div class="wrap">	
	<?php echo $html_update; ?>	
	<div id="icon-users" class="icon32"><br /></div>
	<h2>Json User Import</h2>
	<p>Please select the Json file you want to import below.</p>
	
	<form action="users.php?page=json-user-import" method="post" enctype="multipart/form-data">
		<input type="hidden" name="mode" value="submit">
		<!-- <input type="file" name="json_file" /> -->
		<input type="text" name="file_url" />
		<input type="submit" value="Import" />
	</form>
	
	<p>
        sample:
	    {username: 'foo', email:'foo@foo.bar', avatar: '/imaj/avatars/12345/b.jpg'}
	    {username: 'foo2', email:'foo2@foo.bar', avatar: '/imaj/avatars/12345/b.jpg'} 
	    {username: 'foo3', email:'foo3@foo.bar', avatar: '/imaj/avatars/12345/b.jpg'} 	    	         
    </p>
	
	<p style="color: red">Please make sure you back up your database before proceeding!</p>	
</div>
<?php
}	// end of function
?>
