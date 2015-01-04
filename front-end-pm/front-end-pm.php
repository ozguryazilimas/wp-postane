<?php
/*
Plugin Name: Front End PM
Plugin URI: http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/
Description: Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system from front end. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.
Version: 2.2
dbVersion: 2.2
metaVersion: 2.2
Author: Shamim
Author URI: http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/
Text Domain: fep
License: GPLv2
*/

//INCLUDE THE CLASS FILES
include_once("fep-class.php");
include_once("fep-contact-form.php");

//DECLARE AN INSTANCE OF THE CLASS
if(class_exists("fep_main_class"))
	$FrontEndPM = new fep_main_class();
if(class_exists("fep_cf_class"))
	$FEPcf = new fep_cf_class();

//HOOKS
if (isset($FrontEndPM))
{
	//ACTIVATE PLUGIN
	register_activation_hook(__FILE__ , array(&$FrontEndPM, "fepActivate"));
	//Activate/Deactivate schedule 
	register_activation_hook( __FILE__, array(&$FEPcf, 'schedule_activation' ));
	register_deactivation_hook( __FILE__, array(&$FEPcf, 'schedule_deactivation' ));

	//ADD SHORTCODES
	add_shortcode('front-end-pm', array(&$FrontEndPM, "displayAll")); //for FRONT END PM
	add_shortcode('fep-contact-form', array(&$FEPcf, "contact_form")); //for FEP CONTACT FORM

	//ADD ACTIONS
	add_action('plugins_loaded', array(&$FrontEndPM, "translation"));
	add_action('plugins_loaded', array(&$FrontEndPM, "checkDB"));
	add_action('init', array(&$FrontEndPM, "jsInit"));
	add_action('wp_enqueue_scripts', array(&$FrontEndPM, "fep_enqueue_scripts"));
	add_action('admin_menu', array(&$FrontEndPM, "addAdminPage"));
	add_filter('plugin_action_links', array(&$FEPcf, "addSettings"), 10, 2);
	add_filter('cron_schedules', array(&$FEPcf, 'fep_cron_add_weekly' ));
	add_action('fep_weekly_event_hook', array(&$FEPcf, 'fep_weekly_spam_delete' )); //DELETE spam messages weekly
	

	//ADD WIDGET
	wp_register_sidebar_widget("fep-button-widget",__("FEP button widget", "fep"), array(&$FrontEndPM, "widget"));
	wp_register_sidebar_widget("fep-text-widget",__("FEP text widget", "fep"), array(&$FrontEndPM, "widget_text"));
}

?>