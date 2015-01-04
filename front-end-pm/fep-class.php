<?php
include_once('bbcode.php');
//Main CLASS
if (!class_exists("fep_main_class"))
{
  class fep_main_class
  {
/******************************************SETUP BEGIN******************************************/
    //Constructor
    function __construct()
    {
      $this->setupLinks();
      $this->adminOps = $this->getAdminOps();
    }

    function fepActivate()
    {
      global $wpdb;
	  $version = $this->get_version();

      $charset_collate = '';
      if( $wpdb->has_cap('collation'))
      {
        if(!empty($wpdb->charset))
          $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if(!empty($wpdb->collate))
          $charset_collate .= " COLLATE $wpdb->collate";
      }
	  $installed_ver = get_option( "fep_db_version" );
	  $installed_meta_ver = get_option( "fep_meta_db_version" );

	if( $installed_ver != $version['dbversion'] || $wpdb->get_var("SHOW TABLES LIKE '".$this->fepTable."'") != $this->fepTable) {

      $sqlMsgs = 	"CREATE TABLE ".$this->fepTable." (
            id int(11) NOT NULL auto_increment,
            parent_id int(11) NOT NULL default '0',
            from_user int(11) NOT NULL default '0',
			from_name varchar(256) NOT NULL,
			from_email varchar(256) NOT NULL,
            to_user int(11) NOT NULL default '0',
			department varchar(256) NOT NULL,
            last_sender int(11) NOT NULL default '0',
            send_date datetime NOT NULL default '0000-00-00 00:00:00',
            last_date datetime NOT NULL default '0000-00-00 00:00:00',
            message_title varchar(256) NOT NULL,
            message_contents longtext NOT NULL,
            status int(11) NOT NULL default '0',
            to_del int(11) NOT NULL default '0',
            from_del int(11) NOT NULL default '0',
            PRIMARY KEY (id))
            {$charset_collate};";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      dbDelta($sqlMsgs);
	  update_option( "fep_db_version", $version['dbversion'] );
	  }
	  
	  	if( $installed_meta_ver != $version['metaversion'] || $wpdb->get_var("SHOW TABLES LIKE '".$this->metaTable."'") != $this->metaTable) {

      $sqlCF = 	"CREATE TABLE ".$this->metaTable." (
            id int(11) NOT NULL auto_increment,
            message_id int(11) NOT NULL default '0',
            field_name varchar(128) NOT NULL,
            field_value longtext NOT NULL,
			attachment_type varchar(128) NOT NULL,
			attachment_url varchar(512) NOT NULL,
			attachment_path varchar(512) NOT NULL,
            PRIMARY KEY (id))
            {$charset_collate};";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      dbDelta($sqlCF);
	  update_option( "fep_meta_db_version", $version['metaversion'] );
	  }
    }
	
	function translation()
	{
	//SETUP TEXT DOMAIN FOR TRANSLATIONS
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('fep', false, $plugin_dir.'/languages/');
	}

    function widget($args)
    {
      global $user_login;
	  $this->setPageURLs();
      echo $args['before_widget'];
      if (!$user_login)
        echo __("Login to view your messages", "fep");
      else
      {
	  	$numNew = $this->getNewMsgs_btn();
	  $numAnn = $this->getAnnouncementsNum_btn();
	  $myconNew = $this->mycontact_new();
	  $tocheck = get_option('fep_cf_to_field');
	  
        echo "<a class='fep-button' href='".$this->pageURL."'>".__("Inbox", "fep")."".$numNew."</a>
		<a class='fep-button' href='".$this->actionURL."viewannouncements'>".__("Announcement", "fep")."".$numAnn."</a>";
		if ($tocheck){
		if (in_array($user_login,$tocheck)){
		echo "<a class='fep-button' href='".$this->actionURL."mycontactmgs'>".sprintf(__("Contact Message%s", "fep"),$myconNew) . "</a>";}}
		if (current_user_can('manage_options'))
		echo "<a class='fep-button' href='".$this->actionURL."newemail'>".__("Send Email", "fep") . "</a>";
		
      }
      echo $args['after_widget'];
    }
	
	  function widget_text($args)
    {
      global $user_ID;
      $uData = get_userdata($user_ID);
	  $this->setPageURLs();
      echo $args['before_widget'];
      echo $args['before_title'].__("Messages", "fep").$args['after_title'];
      if (!$uData)
        echo __("Login to view your messages", "fep");
      else
      {
	  	$tocheck = get_option('fep_cf_to_field');
        $numNew = $this->getNewMsgs();
        $numAnn = $this->getAnnouncementsNum();
		$numNewadm = $this->getNewMsgs_admin();
		$myconNew = $this->mycontact_new();
		$conNew = $this->getcontact_new();
        echo __("Hi", "fep")." ".$uData->display_name.",<br/>".
        __("You have", "fep")." <a href='".$this->pageURL."'>(<font color='red'>".$numNew."</font>) ".__("new message(s)", "fep")."</a><br/>".
        __("There are", "fep")." <a href='".$this->actionURL."viewannouncements'>(<font color='red'>".$numAnn."</font>) ".__("announcement(s)", "fep")."</a><br/>";
		if ($tocheck){
		if (in_array($uData->user_login,$tocheck)){
		echo "<a href='".$this->actionURL."mycontactmgs'>".sprintf(__("Contact Message%s", "fep"),$myconNew) . "</a><br/>";}}
		if (current_user_can('manage_options')){
		echo "<a href='".$this->actionURL."contactmgs'>".sprintf(__("All Contact Message%s", "fep"),$conNew) . "</a><br/>";
		echo "<a href='".$this->actionURL."viewallmgs'>".__("All Message", "fep")."".$numNewadm."</a><br/>";}
		
      } 
      echo $args['after_widget'];
    }

    //Setup some variables
    var $adminOpsName = "FEP_options";
    var $adminOps = array();
    var $userOpsName = "FEP_uOptions";
    var $userOps = array();

    var $error = "";
	var $success = "";

    var $pluginDir = "";
    var $pluginURL = "";
    var $styleDir = "";
    var $styleURL = "";
    var $pageURL = "";
    var $actionURL = "";
    var $jsURL = "";

    var $fepTable = "";
	var $metaTable = "";

    function jsInit()
    {
	if (isset($_GET['fepjscript']))
      if($_GET['fepjscript'] == '1')
      {
        global $wpdb, $user_ID;
        require_once('js/search.php');
      }
    }

    function setupLinks() //And DB table name too :)
    {
      global $wpdb;
      $this->pluginDir = plugin_dir_path( __FILE__ )."/";
      $this->pluginURL = plugins_url()."/front-end-pm/";
      $this->styleDir = $this->pluginDir."style/";
      $this->styleURL = $this->pluginURL."style/";
      $this->jsURL = $this->pluginURL."js/";

      $this->fepTable = $wpdb->prefix."fep_messages";
	  $this->metaTable = $wpdb->prefix."fep_meta";
    }

    function fep_enqueue_scripts()
    {
	wp_enqueue_style( 'fep-style', $this->styleURL . 'style.css' );
	wp_enqueue_script( 'fep-script', $this->jsURL . 'script.js', array(), '1.0.0', true );
    }

    function getPageID()
    {
      global $wpdb;
      return $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[front-end-pm]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1");
    }

    function setPageURLs()
    {
      global $wp_rewrite;
      if($wp_rewrite->using_permalinks())
        $delim = "?";
      else
        $delim = "&";
      $this->pageURL = get_permalink($this->getPageID());
      $this->actionURL = $this->pageURL.$delim."fepaction=";
    }
/******************************************SETUP END******************************************/

/******************************************ADMIN SETTINGS PAGE BEGIN******************************************/
    function addAdminPage()
    {
	$FEPcf = new fep_cf_class();
	  add_menu_page('Front End PM', 'Front End PM', 'manage_options', 'fep-admin-settings', array(&$this, "dispAdminPage"),plugins_url( 'front-end-pm/images/msgBox.gif' ));
	add_submenu_page('fep-admin-settings', 'Front End PM - ' .__('Settings','fep'), __('Settings','fep'), 'manage_options', 'fep-admin-settings', array(&$this, "dispAdminPage"));
	add_submenu_page('fep-admin-settings', 'Front End PM - ' .__('FEP Contact Form settings','fep'), __('FEP Contact Form settings','fep'), 'manage_options', 'fep-contact-form', array(&$FEPcf, "CFmenuPage"));
	add_submenu_page('fep-admin-settings', 'Front End PM - ' .__('Instruction','fep'), __('Instruction','fep'), 'manage_options', 'fep-instruction', array(&$this, "dispInstructionPage"));
	
    }

    function dispAdminPage()
    {
      if ($this->pmAdminSave())
        echo "<div id='message' class='updated fade'><p>".__("Options successfully saved", "fep")."</p></div>";
      $viewAdminOps = $this->getAdminOps(); //Get current options
	  $token = $this->fep_create_nonce();
	  $url = 'http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/';
	  $cfURL = admin_url( 'admin.php?page=fep-contact-form' );
	  $ReviewURL = 'https://wordpress.org/support/view/plugin-reviews/front-end-pm';
	  $capUrl = 'http://codex.wordpress.org/Roles_and_Capabilities';
      echo 	"<div class='wrap'>
          <h2>".__("Front End PM Settings", "fep")."</h2>
		  <h4>".sprintf(__("For FEP Contact Form Settings <a href='%s' >Click Here</a>", "fep"),esc_url($cfURL))."</h4>
		  <h5>".sprintf(__("If you like this plugin please <a href='%s' target='_blank'>Review in Wordpress.org</a> and give 5 star", "fep"),esc_url($ReviewURL))."</h5>
          <form id='fep-admin-save-form' name='fep-admin-save-form' method='post' action=''>
          <table class='widefat'>
          <thead>
          <tr><th width='30%'>".__("Setting", "fep")."</th><th width='70%'>".__("Value", "fep")."</th></tr>
          </thead>
          <tr><td>".__("Max messages a user can keep in box? (0 = Unlimited)", "fep")."<br /><small>".__("Admins always have Unlimited", "fep")."</small></td><td><input type='text' size='10' name='num_messages' value='".$viewAdminOps['num_messages']."' /><br/> ".__("Default","fep").": 50</td></tr>
          <tr><td>".__("Messages to show per page", "fep")."<br/><small>".__("Do not set this to 0!", "fep")."</small></td><td><input type='text' size='10' name='messages_page' value='".$viewAdminOps['messages_page']."' /><br/> ".__("Default","fep").": 15</td></tr>
		  <tr><td>".__("Maximum user per page in Directory", "fep")."<br/><small>".__("Do not set this to 0!", "fep")."</small></td><td><input type='text' size='10' name='user_page' value='".$viewAdminOps['user_page']."' /><br/> ".__("Default","fep").": 50</td></tr>
		  <tr><td>".__("Time delay between two messages send by a user in minutes (0 = No delay required)", "fep")."<br/><small>".__("Admins have no restriction", "fep")."</small></td><td><input type='text' size='10' name='time_delay' value='".$viewAdminOps['time_delay']."' /><br/> ".__("Default","fep").": 5</td></tr>
		  <tr><td>".__("Block Username", "fep")."<br /><small>".__("Separated by comma", "fep")."</small></td><td><TEXTAREA name='have_permission'>".$viewAdminOps['have_permission']." </TEXTAREA></td></tr>
		  <tr><td>".__("Valid email address for \"to\" field of announcement email", "fep")."<br /><small>".__("All users email will be in \"Bcc\" field", "fep")."</small></td><td><input type='text' size='30' name='ann_to' value='".$viewAdminOps['ann_to']."' /></td></tr>
		  <tr><td>".__("Minimum Capability to use messaging", "fep")."<br /><small>".sprintf(__("see <a href='%s' target='_blank'>WORDPRESS CAPABILITIES</a> to get capabilities (use only one capability)", "fep"),esc_url($capUrl))."</small></td><td><input type='text' size='30' name='min_cap' value='".$viewAdminOps['min_cap']."' /><br /><small>".__("Keep blank if allowed for all users", "fep")."</small></td></tr>
		  <tr><td><input type='checkbox' name='allow_attachment' value='1' ".checked($viewAdminOps['allow_attachment'], '1', false)." />".__("Allow to send attachment", "fep")."<br /><small>".__("Set maximum size of attachment", "fep")."</small></td><td><input type='text' size='30' name='attachment_size' value='".$viewAdminOps['attachment_size']."' /><br /><small>".__("Use KB, MB or GB.(eg. 4MB)", "fep")."</small></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='notify_ann' value='1' ".checked($viewAdminOps['notify_ann'], '1', false)." /> ".__("Send email to all users when a new announcement is published?", "fep")."</td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='hide_directory' value='1' ".checked($viewAdminOps['hide_directory'], '1', false)." /> ".__("Hide Directory from front end?", "fep")."<br /><small>".__("Always shown to Admins", "fep")."</small></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='hide_autosuggest' value='1' ".checked($viewAdminOps['hide_autosuggest'], '1', false)." /> ".__("Hide Autosuggestion when typing recipient name?", "fep")."<br /><small>".__("Always shown to Admins", "fep")."</small></td></tr>
		  <tr><td colspan='2'><input type='checkbox' name='disable_new' value='1' ".checked($viewAdminOps['disable_new'], '1', false)." /> ".__("Disable \"send new message\" for all users except admins?", "fep")."<br /><small>".__("Users can send reply", "fep")."</small></td></tr>
          <tr><td colspan='2'><input type='checkbox' name='hide_branding' value='1' ".checked($viewAdminOps['hide_branding'], '1', false)." /> ".__("Hide Branding Footer?", "fep")."</td></tr>
          <tr><td colspan='2'><span><input class='button-primary' type='submit' name='fep-admin-save' value='".__("Save Options", "fep")."' /></span></td><td><input type='hidden' name='token' value='$token' /></td></tr>
          </table>
		  </form>
		  <ul>".sprintf(__("For more info or report bug pleasse visit <a href='%s' target='_blank'>Front End PM</a>", "fep"),esc_url($url))."</ul>
          </div>";
    }
	
	function dispInstructionPage()
	{
	$url = 'http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/';
	echo 	"<div class='wrap'>
          <h2>".__("Front End PM Setup Instruction", "fep")."</h2>
          <p><ul><li>".__("Create a new page.", "fep")."</li>
          <li>".__("Paste following code under the HTML tab of the page editor", "fep")."<code>[front-end-pm]</code></li>
          <li>".__("Publish the page.", "fep")."</li>
		  <li>".__("Or you can create a page below.", "fep")."</li></ul></p>
		  <h2>".__("FEP Contact Form Setup Instruction", "fep")."</h2>
          <p><ul><li>".__("Create a new page or post.", "fep")."</li>
		  <li>".__("Paste following code under the HTML tab of the page/post editor", "fep")."<code>[fep-contact-form]</code></li>
          <li>".__("Publish the page/post.", "fep")."</li>
		  <li>".sprintf(__("For more info or report bug pleasse visit <a href='%s' target='_blank'>Front End PM</a>", "fep"),esc_url($url))."</li>
          </ul></p>
		  <h2>".__("Create Page For \"Front End PM\"", "fep")."</h2>
		  ".$this->fep_createPage()."</div>";
		  }

    function pmAdminSave()
    {
      if (isset($_POST['fep-admin-save']))
      {
	  
	  if (!is_email($_POST['ann_to'])) {
	  echo "<div id='message' class='error'><p>".__("Please enter a valid email address!", "fep")."</p></div>";
	  return;}
	  if (!ctype_digit($_POST['num_messages']) || !$this->is_positive($_POST['messages_page']) || !$this->is_positive($_POST['user_page']) || !ctype_digit($_POST['time_delay'])) {
	  echo "<div id='message' class='error'><p>".__("First four fields support only positive numbers!", "fep")."</p></div>"; 
	  return;}
        $saveAdminOps = array('num_messages' 	=> $_POST['num_messages'],
                              'messages_page' => $_POST['messages_page'],
							  'user_page' => $_POST['user_page'],
							  'time_delay' => $_POST['time_delay'],
							  'have_permission' => $_POST['have_permission'],
							  'ann_to' => $_POST['ann_to'],
							  'min_cap' => trim($_POST['min_cap']),
							  'attachment_size' => trim($_POST['attachment_size']),
							  'allow_attachment' => ( isset( $_POST['allow_attachment'] ) ) ? $_POST['allow_attachment']: false,
							  'notify_ann' => ( isset( $_POST['notify_ann'] ) ) ? $_POST['notify_ann']: false,
							  'hide_directory' => ( isset( $_POST['hide_directory'] ) ) ? $_POST['hide_directory']: false,
							  'hide_autosuggest' => ( isset( $_POST['hide_autosuggest'] ) ) ? $_POST['hide_autosuggest']: false,
							  'disable_new' => ( isset( $_POST['disable_new'] ) ) ? $_POST['disable_new']: false,
							  'hide_branding' => ( isset( $_POST['hide_branding'] ) ) ? $_POST['hide_branding']: false
        );
		$postedToken = filter_input(INPUT_POST, 'token');
		if($this->fep_verify_nonce($postedToken) && current_user_can('manage_options')){
        update_option($this->adminOpsName, $saveAdminOps);
        return true;}
      }
      return false;
    }

    function getAdminOps()
    {
      $pmAdminOps = array('num_messages' => 50,
                          'messages_page' => 15,
						  'user_page' => 50,
						  'time_delay' => 5,
						  'have_permission' => '',
						  'ann_to' => get_bloginfo("admin_email"),
						  'min_cap' => 'read',
						  'attachment_size' => '4MB',
						  'allow_attachment' => false,
						  'notify_ann' => false,
						  'hide_directory' => false,
						  'hide_autosuggest' => false,
						  'disable_new' => false,
                          'hide_branding' => false
      );

      //Get old values if they exist
      $adminOps = get_option($this->adminOpsName);
      if (!empty($adminOps))
      {
        foreach ($adminOps as $key => $option)
          $pmAdminOps[$key] = $option;
      }

      update_option($this->adminOpsName, $pmAdminOps);
      $this->adminOps = $pmAdminOps;
      return $pmAdminOps;
    }
	
	function fep_createPage(){
	$token = $this->fep_create_nonce();
	$form = "<p>
      <form name='fep-create-page' action='".$this->fep_createPage_action()."' method='post'>
      ".__("Title of \"Front End PM\" Page", "fep").":<br/>
      <input type='text' name='fep-create-page-title' value='' /><br/>
	  <strong>".__("Slug", "fep")."</strong>: <em>".__("If blank, slug will be automatically created based on Title", "fep")."</em><br/>
      <input type='text' name='fep-create-page-slug' value='' /><br/>
	  <input type='hidden' name='token' value='$token' /><br/>
      <input class='button-primary' type='submit' name='fep-create-page' value='".__("Create Page", "fep")."' />
      </form></p>";

      return $form;
    }

	function fep_createPage_action(){
	if (isset($_POST['fep-create-page'])){
      	$titlePre = wp_strip_all_tags($_POST['fep-create-page-title']);
		$title = utf8_encode($titlePre);
		$slugPre = wp_strip_all_tags($_POST['fep-create-page-slug']);
		$slug = utf8_encode($slugPre);
		
		if ($this->getPageID() !=''){
		echo "<div id='message' class='error'><p>" .sprintf(__("Already created page <a href='%s'>%s </a> for \"Front End PM\". Please use that page instead!", "fep"),get_permalink($this->getPageID()),get_the_title($this->getPageID()))."</p></div>";
        return;}
		if (!$title){
          echo "<div id='message' class='error'><p>" .__("You must enter a valid Title!", "fep")."</p></div>";
        return;}
		// Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
	  	if (empty($postedToken))
     	 {
	 	 echo "<div id='message' class='error'><p>" .__("Invalid Token. Please try again!", "fep")."</p></div>";
        return;
      	}
  		if(!$this->fep_verify_nonce($postedToken)){
    	// Actually This is not first form submission. First Submission Pass this condition and inserted into db.
		echo "<div id='message' class='updated'><p>" .__("Page for \"Front End PM\" successfully created!", "fep")."</p></div>";
        return;
		}
		
		$fep_page = array(
  		'post_title'    => $title,
		'post_name'    => $slug,
  		'post_content'  => '[front-end-pm]',
  		'post_status'   => 'publish',
  		'post_type' => 'page'
		);
	$pageID = wp_insert_post( $fep_page );
	if($pageID == 0){
	echo "<div id='message' class='error'><p>" .__("Something wrong.Please try again to create page!", "fep")."</p></div>";
        return;
		} else {
		echo "<div id='message' class='updated'><p>" .sprintf(__("Page <a href='%s'>%s </a> for \"Front End PM\" successfully created!", "fep"),get_permalink($pageID),get_the_title($pageID))."</p></div>";
        return;}
		
		}
	}
/******************************************ADMIN SETTINGS PAGE END******************************************/

/******************************************USER SETTINGS PAGE BEGIN******************************************/
    function dispUserPage()
    {
      global $user_ID;
      if ($this->pmUserSave())
        $this->success = __("Your settings have been saved!", "fep");
      $viewUserOps = $this->getUserOps($user_ID); //Get current options
	  $token = $this->fep_create_nonce();
      $prefs = "<p><strong>".__("Set your preferences below", "fep").":</strong></p>
      <form id='fep-user-save-form' name='fep-user-save-form' method='post' action=''>
      <input type='checkbox' name='allow_messages' value='true'";
      if($viewUserOps['allow_messages'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Allow others to send me messages?", "fep")."</i><br/>

      <input type='checkbox' name='allow_emails' value='true'";
      if($viewUserOps['allow_emails'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Email me when I get new messages?", "fep")."</i><br/>
	  
	  <input type='checkbox' name='allow_ann' value='true'";
      if($viewUserOps['allow_ann'] == 'true')
        $prefs .= "checked='checked'";
      $prefs .= "/> <i>".__("Email me when New announcement is published?", "fep")."</i><br/>
	  <input type='hidden' name='token' value='$token' /><br/>
      <input class='button' type='submit' name='fep-user-save' value='".__("Save Options", "fep")."' />
      </form>";
      return $prefs;
    }

    function pmUserSave()
    {
      global $user_ID;
      if (isset($_POST['fep-user-save']))
      {
        $saveUserOps = array(	'allow_emails' 	=> esc_sql($_POST['allow_emails']),
                    'allow_messages' => esc_sql($_POST['allow_messages']),
					'allow_ann' => esc_sql($_POST['allow_ann'])
        );
		$postedToken = filter_input(INPUT_POST, 'token');
		if($this->fep_verify_nonce($postedToken)){
        update_user_meta($user_ID, $this->userOpsName, $saveUserOps);
        return true;}
      }
      return false;
    }

    function getUserOps($ID)
    {
      $pmUserOps = array(	'allow_emails' 		=> 'true',
                'allow_messages' 	=> 'true',
				'allow_ann' 	=> 'true'
      );

      //Get old values if they exist
      $userOps = get_user_meta($ID, $this->userOpsName, true);
      if (!empty($userOps))
      {
        foreach ($userOps as $key => $option)
          $pmUserOps[$key] = $option;
      }

      update_user_meta($ID, $this->userOpsName, $pmUserOps);
      return $pmUserOps;
    }
/******************************************USER SETTINGS PAGE END******************************************/

/******************************************NEW MESSAGE PAGE BEGIN******************************************/
    function dispNewMsg()
    {
      global $user_ID;
	  $token = $this->fep_create_nonce();
      $adminOps = $this->getAdminOps();
	  if (isset($_GET['to'])){
      $to = $_GET['to'];
	  }else{ $to = '';}
		if (!$this->have_permission())
		{
        $this->error = __("You cannot send messages because you are blocked by administrator!", "fep");
        return;
      }
	  if ($this->adminOps['disable_new'] == '1' && !current_user_can('manage_options'))
		{
        $this->error = __("Send new message is disabled for users!", "fep");
        return;
      }
      if (!$this->isBoxFull($user_ID, $adminOps['num_messages'], '1'))
      {
	$message_to = ( isset( $_REQUEST['message_to'] ) ) ? $_REQUEST['message_to']: $this->convertToUser($to);
	$message_top = ( isset( $_REQUEST['message_top'] ) ) ? $_REQUEST['message_top']: $this->convertToDisplay($to);
	$message_title = ( isset( $_REQUEST['message_title'] ) ) ? $_REQUEST['message_title']: '';
	$message_content = ( isset( $_REQUEST['message_content'] ) ) ? $_REQUEST['message_content']: '';
	$parent_id = ( isset( $_REQUEST['parent_id'] ) ) ? $_REQUEST['parent_id']: 0;
	
        $newMsg = "<p><strong>".__("Create New Message", "fep").":</strong></p>";
        $newMsg .= "<form name='message' action='".$this->actionURL."checkmessage' method='post' enctype='multipart/form-data'>".
        __("To", "fep")."<font color='red'>*</font>: ";
		if($this->adminOps['hide_autosuggest'] != '1' || current_user_can('manage_options')) { 
		$newMsg .="<noscript>Username of recipient</noscript><br/>";
        $newMsg .="<input type='hidden' id='search-qq' name='message_to' autocomplete='off' value='$message_to' />
		<input type='text' id='search-q' onkeyup='javascript:FEPautosuggest(\"".$this->actionURL."\")' name='message_top' placeholder='Name of recipient' autocomplete='off' value='$message_top' /><br/>
        <div id='fep-result'></div>";
		} else {
		$newMsg .="<br/><input type='text' name='message_to' placeholder='Username of recipient' autocomplete='off' value='$message_to' /><br/>";}
		
        $newMsg .= __("Subject", "fep")."<font color='red'>*</font>:<br/>
        <input type='text' name='message_title' placeholder='Subject' maxlength='65' value='$message_title' /><br/>".
        __("Message", "fep")."<font color='red'>*</font>:<br/>".$this->get_form_buttons()."<br/>
        <textarea name='message_content' placeholder='Message Content'>$message_content</textarea>";
		
		if ($adminOps['allow_attachment'] == '1') {
		$newMsg .="<br/><input type='file' name='fep_upload' />";}
		
        $newMsg .="<input type='hidden' name='message_from' value='$user_ID' />
        <input type='hidden' name='parent_id' value='$parent_id' />
		<input type='hidden' name='token' value='$token' /><br/>
        <input type='submit' id='submit' value='".__("Send Message", "fep")."' />
        </form>";
        
        return $newMsg;
      }
      else
      {
        $this->error = __("You cannot send messages because your message box is full! Please delete some messages.", "fep");
        return;
      }
    }
/******************************************NEW MESSAGE PAGE END******************************************/

/******************************************READ MESSAGE PAGE BEGIN******************************************/
    function dispReadMsg()
    {
      global $wpdb, $user_ID;

      $pID = preg_replace('/\D/', '',$_GET['id']);
      $wholeThread = $this->getWholeThread($pID);
	  $token = $this->fep_create_nonce();

      $threadOut = "<p><strong>".__("Message Thread", "fep").":</strong></p>
      <table><tr><th width='15%'>".__("Sender", "fep")."</th><th width='85%'>".__("Message", "fep")."</th></tr>";

      foreach ($wholeThread as $post)
      {
	  $msgsMeta = $this->getcontact_meta($post->id);
        //Check for privacy errors first
        if ($post->to_user != $user_ID && $post->from_user != $user_ID && !current_user_can( 'manage_options' ))
        {
          $this->error = __("You do not have permission to view this message!", "fep");
          return;
        }

        //setup info for the reply form
        if ($post->parent_id == 0) //If it is the parent message
        {
          $to = $post->from_user;
          if ($to == $user_ID) //Make sure user doesn't send a message to himself
            $to = $post->to_user;
          $message_title = $this->output_filter($post->message_title);
          if (substr_count($message_title, __("Re:", "fep")) < 1) //Prevent all the Re:'s from happening
            $re = __("Re:", "fep");
          else
            $re = "";
        }

        $uData = get_userdata($post->from_user);
        $threadOut .= "<tr><td><a href='".get_author_posts_url( $uData->ID )."'>".$uData->display_name."</a><br/><small>".$this->formatDate($post->send_date)."</small><br/>".get_avatar($post->from_user, 60)."</td>";

        if ($post->parent_id == 0) //If it is the parent message
        {
          $threadOut .= "<td class='pmtext'><strong>".__("Subject", "fep").": </strong>".$this->output_filter($post->message_title)."<hr/>".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."";
		  foreach ($msgsMeta as $meta){
		if ($meta->attachment_url ) {
		$attachment_id = $meta->id; 
		$threadOut .= "<hr /><strong>" . __("Attachment", "fep") . ":</strong><br />";
		$threadOut .= "<a href='{$this->pluginURL}attachment-download.php?attachment_id=$attachment_id' title='Download ". basename($meta->attachment_url)."'>". basename($meta->attachment_url)."</a>"; } }
		$threadOut .="</td></tr>";
        }
        else
        {
          $threadOut .= "<td class='pmtext'>".apply_filters("comment_text", $this->autoembed($this->output_filter($post->message_contents)))."";
		  foreach ($msgsMeta as $meta){
		if ($meta->attachment_url ) {
		$attachment_id = $meta->id; 
		$threadOut .= "<hr /><strong>" . __("Attachment", "fep") . ":</strong><br />";
		$threadOut .= "<a href='{$this->pluginURL}attachment-download.php?attachment_id=$attachment_id' title='Download ". basename($meta->attachment_url)."'>". basename($meta->attachment_url)."</a>"; } }
		$threadOut .="</td></tr>";
        }
      }

      $threadOut .= "</table>";

      //SHOW THE REPLY FORM
	  if ($this->have_permission()){
      $threadOut .= "
      <p><strong>".__("Add Reply", "fep").":</strong></p>
      <form name='message' action='".$this->actionURL."checkmessage' method='post' enctype='multipart/form-data'>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'></textarea>";
		
		if ($this->adminOps['allow_attachment'] == '1') {
		$threadOut .="<br/><input type='file' name='fep_upload' />";}
		
        $threadOut .="
      <input type='hidden' name='message_to' value='".get_userdata($to)->user_login."' />
	  <input type='hidden' name='message_top' value='".get_userdata($to)->display_name."' />
      <input type='hidden' name='message_title' value='".$re.$message_title."' />
      <input type='hidden' name='message_from' value='".$user_ID."' />
      <input type='hidden' name='parent_id' value='".$pID."' />
	  <input type='hidden' name='token' value='".$token."' /><br/>
      <input type='submit' value='".__("Send Message", "fep")."' />
      </form>";
	  } else {
        $this->error = __("You cannot send messages because you are blocked by administrator!", "fep");
      }

      if ($post->status == 0 && $user_ID == $post->to_user) //Update only if the reader is the reciever 
        $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET status = 1 WHERE id = %d", $pID));

      return $threadOut;
    }
	

    function getWholeThread($id)
    {
      global $wpdb;
      $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE id = %d OR parent_id = %d ORDER BY send_date ASC", $id, $id));
      return $results;
    }
	
    function getInfo($id)
    {
      global $wpdb;
      $to = $wpdb->get_var($wpdb->prepare("SELECT to_user FROM {$this->fepTable} WHERE id = %d", $id));
	  $from = $wpdb->get_var($wpdb->prepare("SELECT from_user FROM {$this->fepTable} WHERE id = %d", $id));
      return array ( 'to' => $to , 'from' => $from );
    }

    function convertToUser($to)
    {
	$result = '';
	if ($to){
      $user = get_user_by( 'login' , $to );
	  $result = $user->user_login;}
      return $result;
    }
	function convertToDisplay($to)
    {
	$result = '';
	if ($to){
	$user = get_user_by( 'login' , $to );
	$result = $user->display_name;}
      return $result;
    }
/******************************************READ MESSAGE PAGE END******************************************/

/******************************************CHECK MESSAGE PAGE BEGIN******************************************/
    function dispCheckMsg()
    {
      global $wpdb, $user_ID;
      $from = $_POST['message_from'];
	  $uData = get_userdata($from);
      $fromName = $uData->display_name;
      if ($_POST['message_to']) {
	  $preTo = $_POST['message_to'];
	  } else {
	  $preTo = $_POST['message_top']; }
      $to = $this->convertToID($preTo);
      $title = $this->input_filter($_POST['message_title']);
      $content = $this->input_filter($_POST['message_content']);
      $parentID = $_POST['parent_id'];
      $send_date = current_time('mysql');
      
      $adminOps = $this->getAdminOps();
      if ($to)
        $toUserOps = $this->getUserOps($to);

      //Check for errors first
      if (!$to || !$title || !$content || ($from != $user_ID))
      {
        if (!$to)
          $theError = __("You must enter a valid recipient!", "fep");
        if (!$title)
          $theError = __("You must enter a valid subject!", "fep");
        if (!$content)
          $theError = __("You must enter some message content!", "fep");
        if ($from != $user_ID)
          $theError = __("You do not have permission to send this message!", "fep");
        $this->error = $theError;
        return $this->dispNewMsg();
      }
      if ($toUserOps['allow_messages'] != 'true')
      {
        $this->error = __("This user does not want to receive messages!", "fep");
        return;
      }
      if ($this->isBoxFull($to, $adminOps['num_messages'], $parentID))
      {
        $this->error = __("Your or Recipients Message Box Is Full!", "fep");
        return;
      }
	  if (!$this->have_permission())
      {
        $this->error = __("You cannot send messages because you are blocked by administrator!", "fep");
        return;
      }
	  $timeDelay = $this->TimeDelay($adminOps['time_delay']);
	  if ($timeDelay['diffr'] < $adminOps['time_delay'] && !current_user_can('manage_options'))
      {
        $this->error = sprintf(__("Please wait at least more %s to send another message!", "fep"),$timeDelay['time']);
        return;
      }
	  if ($parentID != 0) {
	  $mgsInfo = $this->getInfo($parentID);
	  if ($mgsInfo['to'] != $user_ID && $mgsInfo['from'] != $user_ID && !current_user_can( 'manage_options' ))
        {
          $this->error = __("You do not have permission to send this message!", "fep");
          return;
        }
		}
		if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['fep_upload'];
		$upload_overrides = array( 'test_form' => false );
		
		add_filter('upload_dir', array(&$this, 'fep_upload_dir'));
		add_filter( 'wp_handle_upload_prefilter', array(&$this, 'fep_upload_size' ));
		$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
		remove_filter( 'wp_handle_upload_prefilter', array(&$this, 'fep_upload_size' ));
		remove_filter('upload_dir', array(&$this, 'fep_upload_dir'));
		
		if ( $uploadedfile['tmp_name'] && (!$movefile || $movefile['error'])) {
		$this->error = sprintf(__("Attachment error. %s", "fep"),$movefile['error']);
        return;
		}
	  // Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
	  if (empty($postedToken))
      {
        $this->error = __("Invalid Token. Please try again!", "fep");
        return;
      }

      //If no errors then continue on
	  if($this->fep_verify_nonce($postedToken)){
      if ($parentID == 0){
        $wpdb->query($wpdb->prepare("INSERT INTO {$this->fepTable} (from_user, to_user, message_title, message_contents, parent_id, last_sender, send_date, last_date) VALUES ( %d, %d, %s, %s, %d, %d, %s, %s )", $from, $to, $title, $content, $parentID, $from, $send_date, $send_date));
		$message_id = $wpdb->insert_id;
		if ($message_id && $movefile['url'] && $movefile['file'] && $adminOps['allow_attachment'] == '1') {
		$wpdb->query($wpdb->prepare('INSERT INTO '.$this->metaTable.' (message_id, attachment_type, attachment_url, attachment_path) VALUES ( %d, %s, %s, %s )', $message_id, $movefile['type'], $movefile['url'], $movefile['file']));}
      } else {
        $wpdb->query($wpdb->prepare("INSERT INTO {$this->fepTable} (from_user, to_user, message_title, message_contents, parent_id, send_date) VALUES ( %d, %d, %s, %s, %d, %s)", $from, $to, $title, $content, $parentID, $send_date));
		
		$mgs_id = $wpdb->insert_id;
		if ($mgs_id && $movefile['url'] && $movefile['file'] && $adminOps['allow_attachment'] == '1') {
		$wpdb->query($wpdb->prepare('INSERT INTO '.$this->metaTable.' (message_id, attachment_type, attachment_url, attachment_path) VALUES ( %d, %s, %s, %s )', $mgs_id, $movefile['type'], $movefile['url'], $movefile['file']));}
		
        $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET status = 0,last_sender = %d,last_date = %s, to_del = 0, from_del = 0 WHERE id = %d", $from, $send_date, $parentID));
      }
	  $this->sendEmail($to, $fromName, $title); }

      $this->success = __("Your message was successfully sent!", "fep");

      return;
    }
	
	function fep_upload_dir($upload) {
	/* Append year/month folders if that option is set */
		$subdir = '';
        if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
                $time = current_time( 'mysql' );

            $y = substr( $time, 0, 4 );
            $m = substr( $time, 5, 2 );

            $subdir = "/$y/$m";    
        }
	$upload['subdir']	= '/front-end-pm' . $subdir;
	$upload['path']		= $upload['basedir'] . $upload['subdir'];
	$upload['url']		= $upload['baseurl'] . $upload['subdir'];
	return $upload;
	}	

	function fep_upload_size($file)
	{
	$adminOps = $this->getAdminOps();
	$filesize = $file['size']; // bytes
	$allowed = $adminOps['attachment_size'];
	$allowedsize = wp_convert_hr_to_bytes( $allowed );
	if ( $filesize > $allowedsize)
	$file['error'] = sprintf(__('Maximum allowed attachment size is %s!'),$allowed);

    return $file;
	}

    function isBoxFull($to, $boxSize, $parentID)
    {
      global $wpdb;

      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE (to_user = %d AND parent_id = 0 AND to_del = 0) OR (from_user = %d AND parent_id = 0 AND from_del = 0)", $to, $to));
      $num = $wpdb->num_rows;

      if ($boxSize == 0 || $num < $boxSize || $parentID != 0 || current_user_can('manage_options') || user_can( $to, 'manage_options' ))
        return false;
      else
        return true;
    }

    function sendEmail($to, $fromName, $title)
    {
      $toOptions = $this->getUserOps($to);
      $notify = $toOptions['allow_emails'];
      if ($notify == 'true')
      {
        $sendername = get_bloginfo("name");
        $sendermail = get_bloginfo("admin_email");
        $headers = "MIME-Version: 1.0\r\n" .
          "From: ".$sendername." "."<".$sendermail.">\r\n" . 
          "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n";
		$subject = "" . get_bloginfo("name").": New Message";
		$message = "You have received a new message in \r\n";
		$message .= get_bloginfo("name")."\r\n";
		$message .= "From: ".$fromName. "\r\n";
		$message .= "Subject: ".$title. "\r\n";
		$message .= "Please Click the following link to view full Message. \r\n";
		$message .= $this->pageURL."\r\n";		
        $mUser = get_userdata($to);
        $mailTo = $mUser->user_email;
		
		//wp_mail($mailTo, $subject, $message, $headers); // uncomment this line if you want blog name in message from, comment following line
        wp_mail($mailTo, $subject, $message);
      }
    }

    function convertToID($preTo)
    {
      global $user_ID;
	  $result = 0;
		$user = get_user_by( 'login' , $preTo );
		if ($user != '')
		$result = $user->ID;
      if ($result != $user_ID)
        return $result;
      else
        return 0;
    }
/******************************************CHECK MESSAGE PAGE END******************************************/

/******************************************MESSAGE-BOX PAGE BEGIN******************************************/
    function dispMsgBox()
    {
      global $wpdb, $user_ID;

      $adminOps = $this->getAdminOps();
	  $token = $this->fep_create_nonce();
	  
	  $numMsgs = $this->getUserNumMsgs();
	  $msgs = $this->getMsgs();
	  
      if ($numMsgs)
      {
	  if ($_GET['fepaction'] === 'viewallmgs' && current_user_can('manage_options')){
              $msgsOut .= "<p><strong>".__("All Messages", "fep").": ($numMsgs)</strong></p>";
			  } else {
			  $msgsOut .= "<p><strong>".__("Your Messages", "fep").": ($numMsgs)</strong></p>";
			  }
        $numPgs = $numMsgs / $adminOps['messages_page'];
        if ($numPgs > 1)
        {
          $msgsOut .= "<p><strong>".__("Page", "fep").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['pmpage'] != $i){
			if ($_GET['fepaction'] === 'viewallmgs' && current_user_can('manage_options')){
              $msgsOut .= "<a href='".$this->actionURL."viewallmgs&pmpage=".$i."'>".($i+1)."</a> ";
			  } else {
			  $msgsOut .= "<a href='".$this->actionURL."messagebox&pmpage=".$i."'>".($i+1)."</a> ";
			  }
            } else {
              $msgsOut .= "[<b>".($i+1)."</b>] ";}
          $msgsOut .= "</p>";
        }

        $msgsOut .= "<table><tr class='head'>
        <th width='20%'>".__("Started By", "fep")."</th>
		<th width='20%'>".__("To", "fep")."</th>
        <th width='30%'>".__("Subject", "fep")."</th>
        <th width='20%'>".__("Last Reply By", "fep")."</th>
        <th width='10%'>".__("Delete", "fep")."</th></tr>";
        
		$a = 0;
        foreach ($msgs as $msg)
        {
          if ($msg->status == 0 && $msg->last_sender != $user_ID)
            $status = "<font color='#FF0000'>".__("Unread", "fep")."</font>";
          else
            $status = __("Read", "fep");
          $uSend = get_userdata($msg->from_user);
          $uLast = get_userdata($msg->last_sender);
          $toUser = get_userdata($msg->to_user);
		  $msgsOut .= "<tr class='trodd".$a."'>";
		  if ($uSend->ID != $user_ID){
          $msgsOut .= "<td><a href='".get_author_posts_url( $uSend->ID )."'>" .$uSend->display_name. "</a><br/><small>".$this->formatDate($msg->send_date)."</small></td>"; }
		  else {
		  $msgsOut .= "<td>" .$uSend->display_name. "<br/><small>".$this->formatDate($msg->send_date)."</small></td>"; }
		  if ($toUser->ID != $user_ID){
          $msgsOut .= "<td><a href='".get_author_posts_url( $toUser->ID )."'>" .$toUser->display_name. "</a></td>";}
		  else {
		  $msgsOut .= "<td>" .$toUser->display_name. "</td>";}
		  $msgsOut .= "<td><a href='".$this->actionURL."viewmessage&id=".$msg->id."'>".$this->output_filter($msg->message_title)."</a><br/><small>".$status."</small></td>";
		  $msgsOut .= "<td>" .$uLast->display_name. "<br/><small>".$this->formatDate($msg->last_date)."</small></td>";
		  
		  if ( $_GET['fepaction'] === 'viewallmgs' && current_user_can('manage_options')){
              $msgsOut .= "<td><a href='".$this->actionURL."deletemessageadmin&id=".$msg->id."&token=$token' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>";
			  } else {
			  $msgsOut .= "<td><a href='".$this->actionURL."deletemessage&id=".$msg->id."&token=$token' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>";
			  }
          $msgsOut .=  "</tr>";
		   //Alternate table colors
		  if ($a) $a = 0; else $a = 1;
        }
        $msgsOut .= "</table>";

        return $msgsOut;
      }
      else
      {
        $this->error = __("Your message box is empty!", "fep");
        return;
      }
    }
	
	function getUserNumMsgs()
    {
      global $wpdb, $user_ID;
	  if ($_GET['fepaction'] === 'viewallmgs' && current_user_can('manage_options')){
	  $get_messages = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE parent_id = 0 AND (status = 0 OR status = 1)");
	  } else {
      $get_messages = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE ((to_user = %d AND parent_id = 0 AND to_del = 0) OR (from_user = %d AND parent_id = 0 AND from_del = 0)) AND (status = 0 OR status = 1)", $user_ID, $user_ID));}
      return $wpdb->num_rows;
    }

    function getMsgs()
    {
      global $wpdb, $user_ID;
	  if (isset($_GET['pmpage'])){
      $page = preg_replace('/\D/', '',$_GET['pmpage']);
	  }else{$page = 0;}
      $adminOps = $this->getAdminOps();
      $start = $page * $adminOps['messages_page'];
      $end = $adminOps['messages_page'];
	  
	  if ($_GET['fepaction'] === 'viewallmgs' && current_user_can('manage_options')){
	  $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE parent_id = 0 AND (status = 0 OR status = 1) ORDER BY last_date DESC LIMIT %d, %d", $start, $end));
	  } else {
	  $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE ((to_user = %d AND parent_id = 0 AND to_del = 0) OR (from_user = %d AND parent_id = 0 AND from_del = 0)) AND (status = 0 OR status = 1) ORDER BY last_date DESC LIMIT %d, %d", $user_ID, $user_ID, $start, $end));
	  }

      return $get_messages;
    }
	
	function getNewMsgs()
    {
      global $wpdb, $user_ID;

      $get_pms = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE (to_user = %d AND parent_id = 0 AND to_del = 0 AND status = 0 AND last_sender <> %d) OR (from_user = %d AND parent_id = 0 AND from_del = 0 AND status = 0 AND last_sender <> %d)", $user_ID, $user_ID, $user_ID, $user_ID));
      return $wpdb->num_rows;
    }
	function getNewMsgs_btn(){
	if ($this->getNewMsgs()){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $this->getNewMsgs();
		$newmgs .="</font>)";
		} else {
		$newmgs = "";}
		
		return $newmgs;
		}
		
		function getNewMsgs_admin()
    {
      global $wpdb, $user_ID;

      $get_pmss = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 0 AND parent_id = 0");
	  if ($wpdb->num_rows){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $wpdb->num_rows;
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		
		return $newmgs;
    }
/******************************************MESSAGE-BOX PAGE END******************************************/

/******************************************DELETE PAGE BEGIN******************************************/
    function dispDelMsg()
    {
      global $wpdb, $user_ID;

      $delID = preg_replace('/\D/', '',$_GET['id']);
	  
	  if (!$this->fep_verify_nonce($_GET['token'])){
	  return "<div id='fep-error'>".__("Invalid Token!", "fep")."</div>";}
	  
	  $result = $wpdb->get_row($wpdb->prepare("SELECT from_user, to_user, to_del, from_del FROM {$this->fepTable} WHERE id = %d", $delID));

      if ($result->to_user == $user_ID)
      {
        if ($result->from_del == 0){
          $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET to_del = 1 WHERE id = %d", $delID));
        } else {
		$ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
	  $id = implode(',',$ids);
	  $results = $wpdb->get_col("SELECT attachment_path FROM {$this->metaTable} WHERE message_id IN ({$id})" );
	  foreach ($results as $result){
		if ($result)
		unlink($result);
		}
          $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
		  $wpdb->query("DELETE FROM {$this->metaTable} WHERE message_id IN ({$id})");
		  }
      }
      elseif ($result->from_user == $user_ID)
      {
        if ($result->to_del == 0){
          $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET from_del = 1 WHERE id = %d", $delID));
        } else {
		$ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
	  $id = implode(',',$ids);
	  $results = $wpdb->get_col("SELECT attachment_path FROM {$this->metaTable} WHERE message_id IN ({$id})" );
	  foreach ($results as $result){
		if ($result)
		unlink($result);
		}
          $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
		  $wpdb->query("DELETE FROM {$this->metaTable} WHERE message_id IN ({$id})");
		  }
      } else {
	  $this->error = __("No permission!", "fep");
      return;}

      $this->success = __("Your message was successfully deleted!", "fep");

      return;
    }
	
	function deleteMessage()
    {
      global $wpdb;

      $delID = preg_replace('/\D/', '',$_GET['id']);
	  if (!$this->fep_verify_nonce($_GET['token'])){
	  return "<div id='fep-error'>".__("Invalid Token!", "fep")."</div>";}
	  
	  if (current_user_can('manage_options')) {
	  $ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
	  $id = implode(',',$ids);
	  $results = $wpdb->get_col("SELECT attachment_path FROM {$this->metaTable} WHERE message_id IN ({$id})" );
	  foreach ($results as $result){
		if ($result)
		unlink($result);
		}
	  $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d OR parent_id = %d", $delID, $delID));
	  $wpdb->query("DELETE FROM {$this->metaTable} WHERE message_id IN ({$id})");

      $this->success = __("Message was successfully deleted!", "fep"); 
	  } else {
	  $this->error = __("No permission!", "fep");}

      return;
    }
	
//Delete all spam messages
	function emptySpam()
    {
      global $wpdb;
	  if (!$this->fep_verify_nonce($_GET['token'])){
	  return "<div id='fep-error'>".__("Invalid Token!", "fep")."</div>";}
	  
	  $spams = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 7 OR status = 8 ORDER BY id ASC");
	  $spamID = array();
	  foreach ($spams as $spam) {
	  $spamID[] = $spam->id;
	  }
	  $query = implode(",", $spamID);
	  $results = $wpdb->get_col("SELECT attachment_path FROM {$this->metaTable} WHERE message_id IN ({$query})" );
	  foreach ($results as $result){
		if ($result)
		unlink($result);
		}
	  $wpdb->query("DELETE FROM {$this->metaTable} WHERE message_id IN ({$query})");
	  $wpdb->query("DELETE FROM {$this->fepTable} WHERE status = 7 OR status = 8");
	  $this->success = __("All spam messages successfully deleted!", "fep");
      return;
    }
/******************************************DELETE PAGE END******************************************/

/******************************************VIEW ANNOUNCEMENTS BEGIN******************************************/

    function dispAnnouncement()
    {
      global $wpdb, $user_ID;
      $announcements = $this->getAnnouncements();
      $num = $wpdb->num_rows;
	  $token = $this->fep_create_nonce();

      if ($this->deleteAnnouncement()) //Deleting an announcement?
      {
        $this->success = __("The announcement was successfully deleted!", "fep");
        return;
      }

      if (!$num) //Just viewing announcements
      {
        $announce = "<p><strong>".__("Announcements", "fep").":</strong></p>";
        if (current_user_can('manage_options'))
        {
          $announce .= $this->dispAnnounceForm();
        }
        $this->error = __("There are no announcements!", "fep");
      }
      else
      {
        $announce = "<p><strong>".__("Announcements", "fep").":</strong></p>";
        if (current_user_can('manage_options'))
        {
          $announce .= $this->dispAnnounceForm();
        }
        $announce .= "<table>";
        $a = 0;
        foreach ($announcements as $announcement)
        {
          $announce .= "<tr class='trodd".$a."'><td class='pmtext'><strong>".__("Subject", "fep").":</strong> ".$this->output_filter($announcement->message_title).
          "<br/><strong>".__("Date", "fep").":</strong> ".$this->formatDate($announcement->send_date);
          if (current_user_can('manage_options')) {
		  $announce .= "<br/><strong>".__("Added by", "fep").":</strong> ".get_userdata($announcement->from_user)->display_name;
            $announce .= "<br/><a href='".$this->actionURL."viewannouncements&del=1&id=".$announcement->id."&token=$token' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a>"; }
          $announce .= "<hr/>";
          $announce .= "<strong>".__("Message", "fep").":</strong><br/>".apply_filters("comment_text", $this->output_filter($announcement->message_contents))."</td></tr>";
          if ($a) $a = 0; else $a = 1; //Alternate table colors
        }
        $announce .= "</table>";
      }

      return $announce;
    }

    function dispAnnounceForm()
    {
		global $user_ID;
		$token = $this->fep_create_nonce();

	$message_title = ( isset( $_REQUEST['message_title'] ) ) ? $_REQUEST['message_title']: '';
	$message_content = ( isset( $_REQUEST['message_content'] ) ) ? $_REQUEST['message_content']: '';
	
      $form = "<p>".__("Add a new announcement below", "fep")."</p>
      <form name='message' action='".$this->actionURL."addannouncement' method='post'>
      ".__("Subject", "fep").":<br/>
      <input type='text' name='message_title' value='$message_title' /><br/>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'>$message_content</textarea>
	  <input type='hidden' name='message_from' value='$user_ID' />
	  <input type='hidden' name='token' value='$token' /><br/>
      <input type='submit' name='add-announcement' value='".__("Submit", "fep")."' />
      </form>";

      return $form;
    }

    function getAnnouncements()
    {
      global $wpdb; //status = 2 indicates that the msg is an announcement :)
      $results = $wpdb->get_results("SELECT * FROM {$this->fepTable} WHERE status = 2 ORDER BY id DESC");
      return $results;
    }

    function getAnnouncementsNum()
    {
      global $wpdb; //status = 2 indicates that the msg is an announcement :)
      $results = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 2 ORDER BY id DESC");
      return $wpdb->num_rows;
    }
	function getAnnouncementsNum_btn(){
	if ($this->getAnnouncementsNum()){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $this->getAnnouncementsNum();
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		
		return $newmgs;
		}

    function addAnnouncement()
    {
      global $wpdb,$user_ID;
	  $adminOps = $this->getAdminOps();
      $title = $this->input_filter($_POST['message_title']);
      $contents = $this->input_filter($_POST['message_content']);
	  $from = $_POST['message_from'];
      $send_date = current_time('mysql');
      $status = '2';
	  
	  if (!$title || !$contents || $from != $user_ID)
      {
        if (!$title)
          $theError = __("You must enter a valid subject!", "fep");
        if (!$contents)
          $theError = __("You must enter some content!", "fep");
		 if ($from != $user_ID)
          $theError = __("Please try again!", "fep");
        $this->error = $theError;
        return $this->dispAnnounceForm();
		}
	  
	  // Check if a form has been sent
		$postedToken = filter_input(INPUT_POST, 'token');
		if (empty($postedToken))
      {
        $this->error = __("Invalid Token. Please try again!", "fep");
        return;
      }
  		if(!$this->fep_verify_nonce($postedToken)){
    // Actually This is not first form submission. First Submission Pass this condition and inserted into db if was valid.
	$this->success = __("The announcement was successfully added!", "fep");
        return;
  			}
		//if nothing wrong continue
        $wpdb->query($wpdb->prepare("INSERT INTO {$this->fepTable} (from_user, message_title, message_contents, send_date, status) VALUES ( %s, %s, %s, %s, %d )",$from, $title, $contents, $send_date, $status));

	  if ($adminOps['notify_ann'] == '1') {
	  $this->notify_users($title);
	  $this->success = __("The announcement was successfully added and sent email to all users!", "fep");
        return;
	  } else {
      $this->success = __("The announcement was successfully added!", "fep");
        return;
		}
    }

    function deleteAnnouncement()
    {
      global $wpdb;
	  
	  if (isset($_GET['id'])){$delID = $_GET['id'];}
	  if (isset($_GET['del'])){$delm = $_GET['del'];}else{ $delm = ''; }
      if (current_user_can('manage_options') && $delm && $this->fep_verify_nonce($_GET['token'])) //Make sure only admins can delete announcements
      {
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->fepTable} WHERE id = %d", $delID));
        return true;
      }
      return false;
    }
	
	//Mass emails when announcement is created
		function notify_users($title) {
		
		$domain_name =  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']);
		$usersarray = get_users("orderby=ID");
		$adminOps = $this->getAdminOps();
		$to = $adminOps['ann_to'];
		$from = 'noreply@'.$domain_name;
		
		$bcc = array();
		foreach  ($usersarray as $user) {
		$toOptions = $this->getUserOps($user->ID);
		$notify = $toOptions['allow_ann'];
		if (in_array($notify == 'true',$usersarray)){
		$bcc[] = $user->user_email;
		}
		}
		
	$chunked_bcc = array_chunk($bcc, 25);
	
	$subject = "" . get_bloginfo("name").": New Announcement";
	$message = "A new Announcement is Published in \r\n";
	$message .= get_bloginfo("name")."\r\n";
	$message .= "Title: ".$title. "\r\n";
	$message .= "Please Click the following link to view full Announcement. \r\n";
	$message .= $this->actionURL."viewannouncements \r\n";
	foreach($chunked_bcc as $bcc_chunk){
        $headers = array();
		$headers['From'] = 'From: '.get_bloginfo("name").'<'.$from.'>';
        $headers['Bcc'] = 'Bcc: '.implode(', ', $bcc_chunk);
        wp_mail($to , $subject, $message, $headers);
		}
		return;
    }
/******************************************VIEW ANNOUNCEMENTS END******************************************/

/******************************************CONTACT MESSAGE PAGE BEGIN******************************************/
	   function contact_message()
    {
      global $wpdb, $user_ID;

      $adminOps = $this->getAdminOps();
	  $msgs = $this->getcontact_mgs();
	  $numMsgs = $this->getcontact_mgsNum();
	  $token = $this->fep_create_nonce();
	 
      if ($numMsgs)
      {
        $msgsOut = "<p><strong>".__("All Messages", "fep").": ($numMsgs) </strong>";
		
		if ($_GET['fepaction'] === 'spam' && current_user_can('manage_options'))
		$msgsOut .= "<a href='".$this->actionURL."emptyspam&token=$token' onclick='return confirm(\"".__('Are you sure you want to delete all spam messages? This action CAN NOT be undone.', 'fep')."\");'>Empty Spam Folder</a> ";
		$msgsOut .= "</p>";
		
        $numPgs = $numMsgs / $adminOps['messages_page'];
        if ($numPgs > 1)
        {
          $msgsOut .= "<p><strong>".__("Page", "fep").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['cfpage'] != $i){
			if ($_GET['fepaction'] === 'contactmgs' && current_user_can('manage_options')){
              $msgsOut .= "<a href='".$this->actionURL."contactmgs&cfpage=".$i."'>".($i+1)."</a> ";
			  } elseif ($_GET['fepaction'] === 'spam' && current_user_can('manage_options')){
			  $msgsOut .= "<a href='".$this->actionURL."spam&cfpage=".$i."'>".($i+1)."</a> ";
			  } elseif ($_GET['fepaction'] === 'mycontactmgs'){
			  $msgsOut .= "<a href='".$this->actionURL."mycontactmgs&cfpage=".$i."'>".($i+1)."</a> ";}
            } else {
              $msgsOut .= "[<b>".($i+1)."</b>] "; }
          $msgsOut .= "</p>";
        }

        $msgsOut .= "<table><tr class='head'>
        <th width='20%'>".__("From", "fep")."</th>
        <th width='20%'>".__("To", "fep")."</th>
        <th width='50%'>".__("Subject", "fep")."</th>
        <th width='10%'>".__("Action", "fep")."</th></tr>";
		$a = 0;
        foreach ($msgs as $msg)
        {
          if ($msg->status == 5 || $msg->status == 7)
            $status = "<font color='#FF0000'>".__("Unread", "fep")."</font>";
          else
            $status = __("Read", "fep");
		if ($msg->from_user != 0)
            $reg = __("Registered", "fep");
          else
            $reg = __("Unregistered", "fep");
          $toUser = get_userdata($msg->to_user);
		  $msgsOut .= "<tr class='trodd".$a."'>";
		  $msgsOut .= "<td>" .$msg->from_name. "<br/><small>$reg</small><br /><small>".$this->formatDate($msg->send_date)."</small></td>";
          $msgsOut .= "<td>$toUser->display_name<br/><small>$msg->department</small></td>";
		  $msgsOut .= "<td><a href='".$this->actionURL."viewcontact&id=".$msg->id."'>".$this->output_filter($msg->message_title)."</a><br/><small>$status</small></td><td>";
		  if ($_GET['fepaction'] === 'contactmgs' && current_user_can('manage_options')){
		  $msgsOut .= "<a href='".$this->actionURL."deletemessageadmin&id=".$msg->id."&token=$token' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>";
		  } elseif ($_GET['fepaction'] === 'spam' && current_user_can('manage_options')){
		  $msgsOut .= "<a href='".$this->actionURL."notspam&id=".$msg->id."'>".__("Not-Spam", "fep")."</a><br />";
          $msgsOut .= "<a href='".$this->actionURL."deletemessageadmin&id=".$msg->id."&token=$token' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>";
		  } elseif ($_GET['fepaction'] === 'mycontactmgs'){
		  $msgsOut .= "<a href='".$this->actionURL."deletemessage&id=".$msg->id."&token=$token' onclick='return confirm(\"".__('Are you sure?', 'fep')."\");'>".__("Delete", "fep")."</a></td>";}
		  
          $msgsOut .=  "</tr>";
		  //Alternate table colors
		  if ($a) $a = 0; else $a = 1;
        }
        $msgsOut .= "</table>";

        return $msgsOut;
      }
      else
      {
        $this->error = __("Message box is empty!", "fep");
        return;
      }
    }
	
	function dispContactMgs()
    {
      global $wpdb, $user_ID;

      $pID = preg_replace('/\D/', '',$_GET['id']);
	  $fullMgs = $this->getcontact_full($pID);
	  $fullMgsMeta = $this->getcontact_meta($pID);
	  
	  if ($fullMgs->to_user != $user_ID && !current_user_can( 'manage_options' ))
        {
          $this->error = __("You do not have permission to view this message!", "fep");
          return;
        }
	  
	  $uData = get_userdata($fullMgs->from_user);
	  if ($uData) {
            $reg = "<small>" .__("Registered", "fep"). "</small><br /><a href='".$this->actionURL."newmessage&to=".$uData->user_login."'>".__("Reply", "fep")."</a>";
         } else {
            $reg = "<small>" .__("Unregistered", "fep"). "</small><br /><a href='".$this->actionURL."newemail&to=".$fullMgs->from_email."'>".__("Reply", "fep")."</a>";}
	  $threadOut = "<p><strong>".__("Message Thread", "fep").":</strong></p>
      <table><tr><th width='15%'>".__("Sender", "fep")."</th><th width='85%'>".__("Message", "fep")."</th></tr>";
	  
        $threadOut .= "<tr><td>$fullMgs->from_name<br/>$reg<br /><small>".$this->formatDate($fullMgs->send_date)."</small><br/>".get_avatar($fullMgs->from_email, 60)."</td>";
		$threadOut .= "<td class='pmtext'><strong>".__("Subject", "fep").": </strong>".$this->output_filter($fullMgs->message_title)."<hr/>".apply_filters("comment_text", $this->autoembed($this->output_filter($fullMgs->message_contents)))."";
		foreach ($fullMgsMeta as $meta){
		if ($meta->attachment_url ) {
		$attachment_id = $meta->id; 
		$threadOut .= "<hr /><strong>" . __("Attachment", "fep") . ":</strong><br />";
		$threadOut .= "<a href='{$this->pluginURL}attachment-download.php?attachment_id=$attachment_id' title='Download ". basename($meta->attachment_url)."'>". basename($meta->attachment_url)."</a>"; }
		if ($meta->field_value)
		$threadOut .="<strong>". ucwords($meta->field_name) . ":</strong> " . apply_filters("comment_text",$this->output_filter($meta->field_value)) . ""; }
	  $threadOut .= "</td></tr></table>";
	  if ($this->have_permission() && $uData){
      $threadOut .= "
      <p><strong>".__("Add Reply", "fep").":</strong></p>
      <form name='message' action='".$this->actionURL."checkmessage' method='post' enctype='multipart/form-data'>".
      $this->get_form_buttons()."<br/>
      <textarea name='message_content'></textarea>";
		
		if ($this->adminOps['allow_attachment'] == '1') {
		$threadOut .="<br/><input type='file' name='fep_upload' />";}
		
        $threadOut .="
      <input type='hidden' name='message_to' value='".get_userdata($to)->user_login."' />
	  <input type='hidden' name='message_top' value='".get_userdata($to)->display_name."' />
      <input type='hidden' name='message_title' value='".$re.$message_title."' />
      <input type='hidden' name='message_from' value='".$user_ID."' />
      <input type='hidden' name='parent_id' value='".$pID."' />
	  <input type='hidden' name='token' value='".$token."' /><br/>
      <input type='submit' value='".__("Send Message", "fep")."' />
      </form>";
	  }
	  if ($fullMgs->status == 5 && $fullMgs->to_user == $user_ID){
        $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET status = 6 WHERE id = %d", $pID));
		} elseif($fullMgs->status == 7 && $fullMgs->to_user == $user_ID){
        $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET status = 8 WHERE id = %d", $pID));}
	  return $threadOut;
	  }
	
		function getcontact_mgs()
    {
      global $wpdb, $user_ID;
	  if (isset($_GET['cfpage'])){
      $page = preg_replace('/\D/', '',$_GET['cfpage']);
	  }else{$page = 0;}
      $adminOps = $this->getAdminOps();
      $start = $page * $adminOps['messages_page'];
      $end = $adminOps['messages_page']; //status = 5/6 indicates that the msg is a contact message, 7/8 indicates that the msg is a spam :)
	  $get_messages = '';
	  if ($_GET['fepaction'] === 'contactmgs' && current_user_can('manage_options')){
	  $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE status = 5 OR status = 6 ORDER BY send_date DESC LIMIT %d, %d", $start, $end));
	  } elseif ($_GET['fepaction'] === 'spam' && current_user_can('manage_options')){
	  $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE status = 7 OR status = 8 ORDER BY send_date DESC LIMIT %d, %d", $start, $end));
	  } elseif ($_GET['fepaction'] === 'mycontactmgs'){
	  $get_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE to_user = %d AND to_del = 0 AND (status = 5 OR status = 6) ORDER BY send_date DESC LIMIT %d, %d", $user_ID, $start, $end));
	  }

      return $get_messages;
    }
	
	function getcontact_full($id)
    {
      global $wpdb;
      $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->fepTable} WHERE id = %d", $id));
      return $results;
    }
	function getcontact_meta($id)
    {
      global $wpdb;
	  $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->metaTable} WHERE message_id = %d", $id));
      return $results;
    }
	
	function getcontact_mgsNum()
    {
      global $wpdb, $user_ID; //status = 5/6 indicates that the msg is a contact message, 7/8 indicates that the msg is a spam :)
	  
	  if ($_GET['fepaction'] === 'contactmgs' && current_user_can('manage_options')){
	  $results = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 5 OR status = 6");
	  } elseif ($_GET['fepaction'] === 'spam' && current_user_can('manage_options')){
	  $results = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 7 OR status = 8");
	  } elseif ($_GET['fepaction'] === 'mycontactmgs'){
	  $results = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE to_user = %d AND to_del = 0 AND (status = 5 OR status = 6)", $user_ID));
	  }
      return $wpdb->num_rows;
    }
	function getcontact_new()
    {
      global $wpdb; //status = 5 indicates that the msg is a new contact message :)
	  
	  $results = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 5");
	  
	  if ($wpdb->num_rows){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $wpdb->num_rows;
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		return $newmgs;
    }
	function getSpam_new()
    {
      global $wpdb; //status = 7 indicates that the msg is a new spam message :)
	  
	  $results = $wpdb->get_results("SELECT id FROM {$this->fepTable} WHERE status = 7");
	  
	  if ($wpdb->num_rows){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $wpdb->num_rows;
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		return $newmgs;
    }
	function mycontact_new()
    {
      global $wpdb, $user_ID; //status = 5 indicates that the msg is a new contact message :)
	  
	  $results = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$this->fepTable} WHERE to_user = %d AND status = 5 AND to_del = 0", $user_ID));
	  
	  if ($wpdb->num_rows){
	  	$newmgs = " (<font color='red'>";
		$newmgs .= $wpdb->num_rows;
		$newmgs .="</font>)";
		} else {
		$newmgs ="";}
		return $newmgs;
    }
	function notSpam()
    {
	global $wpdb;
	if (isset($_GET['id'])){$id = preg_replace('/\D/', '',$_GET['id']);}else{ $id = 0; }
      $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM {$this->fepTable} WHERE id = %d", $id));
	  if ( $status == 7 && current_user_can('manage_options')){
	  $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET status = 5 WHERE id = %d", $id));
	  } elseif ( $status == 8 && current_user_can('manage_options')){
	  $wpdb->query($wpdb->prepare("UPDATE {$this->fepTable} SET status = 6 WHERE id = %d", $id));}
	  $this->success = __("Your message was successfully moved to Contact Message!", "fep");
        return;
	
	}

/******************************************CONTACT MESSAGE PAGE END******************************************/

/******************************************DIRECTORY DISPLAY BEGIN******************************************/

function dispDirectory()
    {
	if($this->adminOps['hide_directory'] == '1' && !current_user_can('manage_options'))
	  return;
	  if (isset($_GET['upage'])){
	  $page = preg_replace('/\D/', '',$_GET['upage']);
	  }else{$page = 0;}
      $adminOps = $this->getAdminOps();
      $offset = $page * $adminOps['user_page'];
	  $args = array(
					'number' => $adminOps['user_page'],
					'offset' => $offset,
					'orderby' => 'display_name',
					'order' => 'ASC'
		);

	// The Query
	$user_query = new WP_User_Query( $args );
	  $result = count_users();
	  $total = $result['total_users'];
      if (! empty( $user_query->results))
      {
        $directory = "<p><strong>".__("Total Users", "fep").": (".$total.")</strong></p>";
        $numPgs = $total / $adminOps['user_page'];
        if ($numPgs > 1)
        {
          $directory .= "<p><strong>".__("Page", "fep").": </strong> ";
          for ($i = 0; $i < $numPgs; $i++)
            if ($_GET['upage'] != $i)
              $directory .= "<a href='".$this->actionURL."directory&upage=".$i."'>".($i+1)."</a> ";
            else
              $directory .= "[<b>".($i+1)."</b>] ";
          $directory .= "</p>";
        }
		$directory .= "<table><tr class='head'>
        <th width='50%'>".__("User", "fep")."</th>
        <th width='50%'>".__("Send Message", "fep")."</th></tr>";
		$a=0;

      foreach($user_query->results as $u)
      {
	  $directory .= "<tr class='trodd".$a."'><td>".$u->display_name."</td>";
          $directory .= "<td><a href='".$this->actionURL."newmessage&to=".$u->user_login."'>".__("Send Message", "fep")."</a></td></tr>";
		  if ($a) $a = 0; else $a = 1;
      }
	  $directory .= "</table>";

        return $directory;
      }
      else
      {
        $this->error = __("No users found.", "fep");
        return;
      }
    }
	
/******************************************DIRECTORY DISPLAY END******************************************/

/******************************************MAIN DISPLAY BEGIN******************************************/
    function dispHeader()
    {
      global $user_ID, $user_login;

      $numNew = $this->getNewMsgs();
      $numAnn = $this->getAnnouncementsNum();
      $msgBoxSize = $this->getUserNumMsgs();
      $adminOps = $this->getAdminOps();
      if ($adminOps['num_messages'] == 0 || current_user_can('manage_options'))
        $msgBoxTotal = __("Unlimited", "fep");
      else
        $msgBoxTotal = $adminOps['num_messages'];

      $header = "<div id='fep-wrapper'>";
      $header .= "<div id='fep-header'>";
      $header .= get_avatar($user_ID, 55)."<p><strong>".__("Welcome", "fep").": ".$this->convertToDisplay($user_login)."</strong><br/>";
      $header .= __("You have", "fep")." (<font color='red'>".$numNew."</font>) ".__("new messages", "fep").
      " ".__("and", "fep")." (<font color='red'>".$numAnn."</font>) ".__("announcement(s)", "fep")."<br/>";
      if ($msgBoxTotal == __("Unlimited", "fep") || $msgBoxSize < $msgBoxTotal)
        $header .= __("Message box size", "fep").": ".$msgBoxSize." ".__("of", "fep")." ".$msgBoxTotal."</p>";
      else
        $header .= "<font color='red'>".__("Your Message Box Is Full! Please delete some messages.", "fep")."</font></p>";
      $header .= "</div>";
      return $header;
    }

    function dispMenu()
    {
	global $user_login;

      $numNew = $this->getNewMsgs_btn();
	  $allNew = $this->getNewMsgs_admin();
	  $numAnn = $this->getAnnouncementsNum_btn();
	  $myconNew = $this->mycontact_new();
	  $conNew = $this->getcontact_new();
	  $spamNew = $this->getSpam_new();
	  $tocheck = get_option('fep_cf_to_field');
	  
      $menu = "<div id='fep-menu'>";
	  $menu .= "<a class='fep-button' href='".$this->actionURL."newmessage'>".__("New Message", "fep")."</a>";
      $menu .= "<a class='fep-button' href='".$this->pageURL."'>".sprintf(__("Message Box%s", "fep"),$numNew)."</a>";
	  if ($tocheck){
	  if (in_array($user_login,$tocheck)){
	  $menu .= "<a class='fep-button' href='".$this->actionURL."mycontactmgs'>".sprintf(__("Contact Message%s", "fep"),$myconNew) . "</a>";}}
      $menu .= "<a class='fep-button' href='".$this->actionURL."viewannouncements'>".sprintf(__("Announcements%s", "fep"),$numAnn)."</a>";
	  if($this->adminOps['hide_directory'] != '1' || current_user_can('manage_options'))
      $menu .= "<a class='fep-button' href='".$this->actionURL."directory'>".__("Directory", "fep")."</a>";
      $menu .= "<a class='fep-button' href='".$this->actionURL."settings'>".__("Settings", "fep")."</a>";
	  if(current_user_can('manage_options')){
		$menu .= "<a class='fep-button' href='".$this->actionURL."viewallmgs'>".sprintf(__("All Messages%s", "fep"),$allNew) . "</a>";
		$menu .= "<a class='fep-button' href='".$this->actionURL."contactmgs'>".sprintf(__("All Contact Message%s", "fep"),$conNew) . "</a>";
		$menu .= "<a class='fep-button' href='".$this->actionURL."spam'>".sprintf(__("Spam%s", "fep"),$spamNew) . "</a>";
		$menu .= "<a class='fep-button' href='".$this->actionURL."newemail'>".__("Send Email", "fep") . "</a>";}
		$menu .="</div>";
      $menu .= "<div id='fep-content'>";
      return $menu;
    }

    function dispNotify()
    {
	if ($this->success != ""){
      $notify = "<div id='fep-success'>".$this->success."</div>";
	  } elseif ($this->error != "") {
	  $notify = "<div id='fep-error'>".$this->error."</div>";
	  }
      return $notify;
    }

    function dispFooter()
    {
      $footer = "</div>"; //End content
        //Maybe Add Notify
        if ($this->error != "" || $this->success != "")
          $footer .= $this->dispNotify();
      
      if($this->adminOps['hide_branding'] != '1'){
	  $version = $this->get_version();
        $footer .= "<div id='fep-footer'><a href='http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/'>Front End PM ".$version['version']."</a></div>";}
      
      $footer .= "</div>"; //End main wrapper
      
      return $footer;
    }

    //Display the proper contents
   function displayAll()
    {
      global $user_ID;
      if ($user_ID)
      {
	  $cf = new fep_cf_class();
	  
	  if ($this->adminOps['min_cap'] != ''){ 
	  //Required capability
	  $cap = $this->adminOps['min_cap'];
	  if (!current_user_can($cap)){
	  
	  return "<div id='fep-error'>".sprintf(__("Messaging is only allowed for users at least %s capability!", "fep"), $cap)."</div>";}}
	  
        //Finish the setup since these wouldn't work in the constructor
        $this->userOps = $this->getUserOps($user_ID);
        $this->setPageURLs();

        //Add header
        $out = $this->dispHeader();

        //Add Menu
        $out .= $this->dispMenu();

        //Start the guts of the display
		if (isset($_GET['fepaction'])){
		$switch = $_GET['fepaction'];
		}else{ $switch = '';}
        switch ($switch)
        {
          case 'newmessage':
            $out .= $this->dispNewMsg();
            break;
		case 'newemail':
            $out .= $cf->NewEmail();
            break;
          case 'checkmessage':
            $out .= $this->dispCheckMsg();
            break;
          case 'viewmessage':
            $out .= $this->dispReadMsg();
            break;
          case 'deletemessage':
            $out .= $this->dispDelMsg();
            break;
		case 'deletemessageadmin':
            $out .= $this->deleteMessage();
            break;
          case 'directory':
		  if($this->adminOps['hide_directory'] != '1' || current_user_can('manage_options'))
            $out .= $this->dispDirectory();
			else
			$out .= $this->dispMsgBox();
            break;
          case 'settings':
            $out .= $this->dispUserPage();
            break;
          case 'viewannouncements':
            $out .= $this->dispAnnouncement();
            break;
		  case 'addannouncement':
            $out .= $this->addAnnouncement();
            break;
		case 'mycontactmgs':
		case 'contactmgs':
		case 'spam':
            $out .= $this->contact_message();
            break;
		case 'notspam':
			$out .= $this->notSpam();
            break;
		case 'emptyspam':
			$out .= $this->emptySpam();
            break;
		case 'viewcontact':
            $out .= $this->dispContactMgs();
            break;
		case 'viewallmgs':
          default: //Message box is shown by Default
            $out .= $this->dispMsgBox();
            break;
        }

        //Add footer
        $out .= $this->dispFooter();
      }
      else
      {
        $out = "<div id='fep-error'>".__("You must be logged-in to view your message.", "fep")."</div>";
      }
      return $out;
    }
/******************************************MAIN DISPLAY END******************************************/

/******************************************MISC. FUNCTIONS BEGIN******************************************/

 /**
 * Creates a token usable in a form
 * return nonce with time
 * @return string
 */
	function fep_create_nonce($action = -1) {
   	 $time = time();
    	$nonce = wp_create_nonce($time.$action);
    return $nonce . '-' . $time;
	}	

 /**
 * Check if a token is valid. Mark it as used
 * @param string $_nonce The token
 * @return bool
 */
	function fep_verify_nonce( $_nonce, $action = -1) {

    //Extract timestamp and nonce part of $_nonce
    $parts = explode( '-', $_nonce );
    $nonce = $parts[0]; // Original nonce generated by WordPress.
    $generated = $parts[1]; //Time when generated

    $nonce_life = 60*60; //We want these nonces to have a short lifespan
    $expire = (int) $generated + $nonce_life;
    $time = time(); //Current time
		// bad formatted onetime-nonce
	if ( empty( $nonce ) || empty( $generated ) )
		return false;

    //Verify the nonce part and check that it has not expired
    if( ! wp_verify_nonce( $nonce, $generated.$action ) || $time > $expire )
        return false;

    //Get used nonces
    $used_nonces = get_option('_fep_used_nonces');

    //Nonce already used.
    if( isset( $used_nonces[$nonce] ) )
        return false;

    foreach ($used_nonces as $nonces => $timestamp){
        if( $timestamp < $time ){
        //This nonce has expired, so we don't need to keep it any longer
        unset( $used_nonces[$nonces] );
		}
    }

    //Add nonce to used nonces and sort
    $used_nonces[$nonce] = $expire;
    asort( $used_nonces );
    update_option( '_fep_used_nonces',$used_nonces );
	return true;
}
	
	//Check is user blocked by admin
	function have_permission(){
	global $user_login;
	if ($user_login){
	$adminOps = $this->getAdminOps();
	$wpusers = (array) explode(',', $adminOps['have_permission']);
	foreach($wpusers as $wpuser){
		$wpuser = trim($wpuser);
		if($wpuser == $user_login)
		return false;
			}
		} //User not logged in
	return true;
	}

    function get_form_buttons()
    {
      $button = '
      <a title="'.__("Bold", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[b]", "[/b]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/b.png" /></a>
      <a title="'.__("Italic", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[i]", "[/i]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/i.png" /></a>
      <a title="'.__("Underline", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[u]", "[/u]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/u.png" /></a>
      <a title="'.__("Strikethrough", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[s]", "[/s]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/s.png" /></a>
      <a title="'.__("Code", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[code]", "[/code]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/code.png" /></a>
      <a title="'.__("Quote", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[quote]", "[/quote]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/quote.png" /></a>
      <a title="'.__("List", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[list]", "[/list]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/list.png" /></a>
      <a title="'.__("List item", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[*]", "", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/li.png" /></a>
      <a title="'.__("Link", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[url]", "[/url]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/url.png" /></a>
      <a title="'.__("Image", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[img]", "[/img]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/img.png" /></a>
      <a title="'.__("Email", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[email]", "[/email]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/email.png" /></a>
      <a title="'.__("Add Hex Color", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[color=#]", "[/color]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/color.png" /></a>
            <a title="'.__("Embed", "fep").'" href="javascript:void(0);" onclick=\'FEPsurroundText("[embed]", "[/embed]", document.forms.message.message_content); return false;\'><img src="'.$this->pluginURL.'/images/bbc/embed.png" /></a>';

      return $button;
    }

    function output_filter($string)
    {
      $parser = new fepBBCParser();
	  $strip = esc_html($string);
	  $html = stripslashes($parser->bbc2html($strip));
      return ent2ncr($html);
    }

    function input_filter($string)
    {
      return esc_attr($string);
    }

    function formatDate($date)
    {
		$now = current_time('mysql');
      //return date('M d, h:i a', strtotime($date));
	  return human_time_diff(strtotime($date),strtotime($now)).' ago';
    }
	
	function TimeDelay($DeTime)
    {
		global $wpdb, $user_ID;
		$now = current_time('mysql');
		$Dtime = $DeTime * 60;
		$Prev = $wpdb->get_var($wpdb->prepare("SELECT last_date FROM {$this->fepTable} WHERE parent_id = 0 AND last_sender = %d ORDER BY last_date DESC LIMIT 1", $user_ID));
	  $diff = strtotime($now) - strtotime($Prev);
	  $diffr = $diff/60;
	  $next = strtotime($Prev) + $Dtime;
	  $Ntime = human_time_diff(strtotime($now),$next);
	   return array('diffr' => $diffr, 'time' => $Ntime);
    }
	
	function is_positive($str) {
 	 return (is_numeric($str) && $str > 0 && $str == round($str));
	}

    function autoembed($string)
    {
      global $wp_embed;
      if (is_object($wp_embed))
        return $wp_embed->autoembed($string);
      else
        return $string;
    }

    function checkDB()
    {
	global $wpdb;
	$version = $this->get_version();
      if ( get_option( "fep_db_version" ) != $version['dbversion'] || get_option( "fep_meta_db_version" ) != $version['metaversion'] )
	  $this->fepActivate();
    }

    function get_version()
    {
      $plugin_data = implode('', file($this->pluginDir."front-end-pm.php"));
      if (preg_match("|Version:(.*)|i", $plugin_data, $version))
        $version = trim($version[1]);
		if (preg_match("|dbVersion:(.*)|i", $plugin_data, $dbversion))
        $dbversion = trim($dbversion[1]);
		if (preg_match("|metaVersion:(.*)|i", $plugin_data, $metaversion))
        $metaversion = trim($metaversion[1]);
      return array('version' => $version, 'dbversion' => $dbversion, 'metaversion' => $metaversion);
    }
/******************************************MISC. FUNCTIONS END******************************************/
  } //END CLASS
} //ENDIF
?>