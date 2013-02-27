<?php
/**
 * CubePM Admin - Setup.
 * Admin page with instructions on how to setup CubePM.
 * @package cubepm
 */


/**
 * CubePM Admin Setup Page
 * 
 * @return null
 */
function cpm_admin_setup(){
?>
<div class="wrap"> 
	<div id="icon-tools" class="icon32"><br /></div> 
<h2>CubePM - <?php _e('Setup', 'cubepm'); ?></h2> 
<p><strong><?php _e('Follow the simple instructions below to set up CubePM:', 'cubepm'); ?></strong></p> 
<p><ol>
<li><?php _e('Create a new page and name it as you wish.', 'cubepm'); ?></li>
<li><?php _e('Add the following shortcode to the contents of the page:', 'cubepm'); ?> <code>[cubepm]</code></li>
<li><?php _e('Save the page and you are done setting up CubePM!', 'cubepm'); ?></li>
<li><?php _e('You may also wish to tweak how CubePM works by visiting the settings page.', 'cubepm'); ?></li>
</ol></p>
 
</div> 
<?php
}