<?php
/*
Plugin Name: Spoiler Block
Plugin URI: http://brunno.me/spoiler-block
Description: Create spoiler's block in your posts to hide contents.
Version: 1.7
Author: squiter
Author URI: http://brunno.me
License: GPL2
*/

/*  Copyright 2011  Brunno dos Santos  (email : brunno@brunno.me)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Let's code guys */

if (!defined('SPOILERBLOCK_PLUGIN_BASENAME')) {
  //spoiler-block/spoiler/block.php
  define('SPOILERBLOCK_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
if (!defined('SPOILERBLOCK_PLUGIN_NAME')) {
  //spoiler-block
  define('SPOILERBLOCK_PLUGIN_NAME', trim(dirname(SPOILERBLOCK_PLUGIN_BASENAME), '/'));
}
if (!defined('SPOILERBLOCK_NAME')) {
  define('SPOILERBLOCK_NAME', 'Spoiler Block');
}
if (!defined('SPOILERBLOCK_TEXTDOMAIN')) {
  define('SPOILERBLOCK_TEXTDOMAIN', 'spoiler-block');
}
if (!defined('SPOILERBLOCK_PLUGIN_DIR')) {
  // /path/to/wordpress/wp-content/plugins/spoiler-block
  define('SPOILERBLOCK_PLUGIN_DIR', dirname(__FILE__));
}
if (!defined('SPOILERBLOCK_PLUGIN_URL')) {
  // http://www.domain.com/wordpress/wp-content/plugins/spoiler-block
  define('SPOILERBLOCK_PLUGIN_URL', WP_PLUGIN_URL . '/' . SPOILERBLOCK_PLUGIN_NAME);
}
if (!defined('SPOILERBLOCK_CONFIG_PAGE')) {
  define('SPOILERBLOCK_CONFIG_PAGE', "spoiler-block-config");
}

add_action('wp_print_styles', 'add_sb_style');
add_action('wp_enqueue_scripts', 'add_sb_scripts');

load_plugin_textdomain( SPOILERBLOCK_TEXTDOMAIN, false, "/spoiler-block/languages" );

/* enfileirando os marotos */

function add_sb_style() {
    $myStyleUrl = SPOILERBLOCK_PLUGIN_URL . '/css/style.css';

    wp_register_style('spoiler_block', $myStyleUrl);
    wp_enqueue_style( 'spoiler_block');
}

function add_sb_scripts() {
	wp_enqueue_script("jquery");
   	wp_enqueue_script('scripts',
    	SPOILERBLOCK_PLUGIN_URL . '/js/scripts.js',
    	array('scriptaculous'),
    	'1.0', true );
}


/*
	Fazendo o nego funfar no admin =D
	- Adicionando o botão no editor e adicionando o estilo no texto :D
*/

add_filter('mce_external_plugins', "spoiler_register");
add_filter('mce_buttons', 'spoiler_add_button', 0);

function spoiler_add_button($buttons)
{
    array_push($buttons, "separator", "spoiler");
    return $buttons;
}
 
function spoiler_register($plugin_array){

    $url = SPOILERBLOCK_PLUGIN_URL . '/js/spoiler_plugin/spoiler_mce.js';
 
    $plugin_array["spoiler"] = $url;
    return $plugin_array;
}

/* Pra adicionar o estilo no texto tem que colocar esse CSS maroto junto com outros marotos que já estão carregados */

add_filter('mce_css', 'spoiler_editor_css');
function spoiler_editor_css($url) {

	if ( !empty($url) )
		$url .= ',';

	$url .= SPOILERBLOCK_PLUGIN_URL . '/css/spoiler_admin_style.css';

  return $url;
}

/* Criando página de configuração do plugin */
add_action('admin_menu', 'spoiler_config_menu');
function spoiler_config_menu(){
	add_plugins_page( "Spoiler Block", __("Spoiler Block Config", SPOILERBLOCK_TEXTDOMAIN), "activate_plugins", SPOILERBLOCK_CONFIG_PAGE, "spoiler_render_config");
}
	
function spoiler_render_config(){
	require("spoiler_render_config.php");
}



/* Selecionando a mesagem que vai ser exibida no spoiler */

add_action('wp_head', "spoiler_selected_message");

add_option("spoiler_alert", __("Warning! Spoiler area! To read click here!",SPOILERBLOCK_TEXTDOMAIN));

function spoiler_selected_message(){
	echo '<script type="text/javascript"> var spoiler_message = "' .get_option("spoiler_alert"). '"</script>';
}

add_action("wp_head","spoiler_selected_message");
?>