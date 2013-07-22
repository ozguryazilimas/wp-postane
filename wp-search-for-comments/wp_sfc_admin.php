
<?php   
	

    if(isset($_POST['wp_sfc_hidden']) and $_POST['wp_sfc_hidden'] == 'Y') {  
        //Form data sent  
        $wp_sfc_limit = $_POST['wp_sfc_limit'];  
        update_option('wp_sfc_limit', $wp_sfc_limit);  

        
		//if(isset($_POST['wp_sfc_add'])){
			$wp_sfc_add = isset($_POST['wp_sfc_add']);
			update_option('wp_sfc_add', $wp_sfc_add);
			$wp_sfc_add = get_option('wp_sfc_add');
		//}
		


        ?>  
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
        <?php  
    } else {  
        //Normal page display  
		if(get_option('wp_sfc_limit') == ""){
			$wp_sfc_limit = '25';  
		}else{
			$wp_sfc_limit = get_option('wp_sfc_limit');  
		}


		
		$wp_sfc_add = get_option('wp_sfc_add');
		


		
    }  
?>
<div class="mainwrap">  
	<div class="wrap">  
		<h2>WP Search for Comments v1.2.1 - LITE</h2>
		 
		<form name="oscimp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="wp_sfc_hidden" value="Y">  
			<h3>Please setup your Comments Search:</h3>
			
			<p><b style="width: 120px; display: block; float: left; margin-top: 5px;">OUTPUT LIMIT</b> <input type="text" name="wp_sfc_limit" value="<?php echo $wp_sfc_limit; ?>" size="3"> - Number of comments that will be printed out.</p>  
			
			<p><b style="width: 120px; display: block; float: left; margin-top: 5px;">SHOW COUNTER?</b> 
			"yes" - Do you want the "1, 2, 3..." in the comments output? ( Note: disabled in light version )</p
			<p>
			
			
			<p><b style="width: 120px; display: block; float: left; margin-top: 5px;">HIGHTLIGHT?</b> 
			
			"no" - Do you want to highlight the search word in results? ( Note: disabled in light version )</p>    

			<p>
			
			
			<p><b style="width: 120px; display: block; float: left; margin-top: 5px;">STYLING</b> 
			
			"Simple"  ( Note: disabled in light version )
			
			<div id="stylepreview"></div>
			</p>  
			

			<p>
			
			<input id="wp_sfc_add" name="wp_sfc_add" type="checkbox" value="1" <?php echo $wp_sfc_add?'checked="checked"':''; ?> />&nbsp;Add Automaticaly<br>
			(NOTE: Will be added below search loop! If search query returns no results, Search for Comments results will be NOT added!)
			<br><br> 
			<span style="font-weight: bold;">
			To add Search for Comments output manually, add <code>&lt?php if(function_exists('wp_sfc')) wp_sfc(); ?&gt</code> at any place on search page. In this case Search for Comments will display results even if there is no results from search loop.
			</span>
			</p> 
			
			<h3>Translations:</h3>
			
			<p>
			( Note: disabled in light version )			
			</p> 
			
			<p class="submit">  
			<input type="submit" name="Submit" value="<?php _e('Update Options', 'oscimp_trdom' ) ?>" />  
			</p> 
		</form>  
		
		<p>
			<a href="http://codecanyon.net/item/wp-search-for-comments/2907860" target="_blank"><img src="<?php echo plugins_url(); ?>/wp-search-for-comments/css/i/logo-light.png" /></a>
		</p>
	</div> 
	<div class="wrap">  
		<br/><br/>
		<h4>Thank you for interest in this plugin. If you like it and want other features to be activated, please grab it from <a href="http://codecanyon.net/item/wp-search-for-comments/2907860" target="_blank">here</a>.</h4>
		<h4>Thanks so much! Desadent.</h4>  
	</div> 
</div> 