<?php
//
//  SETTINGS CONFIGURATION CLASS
//
//  By Olly Benson / v 1.4 / 26 November 2011 / http://code.olib.co.uk
//
//  HOW TO USE
//  * add a include() to this file in your plugin. 
//  * Full details of how to use Settings see here: http://codex.wordpress.org/Settings_API  
 

class ob_settings_v1_4 {
var $config = NULL;
 
function init($class_values) {
	if (!class_exists('ob_page_numbers_values')) :
		printf("OB Settings can not find %s class. Unable to create settings",$class_values);
		exit;
		endif;
	$this->config = get_class_vars($class_values);
    if (function_exists('add_action')) :
      add_action('admin_init', array( &$this, 'admin_init'));
      add_action('admin_menu', array( &$this, 'admin_add_page'));
      endif;
}
 
function admin_add_page() {
	extract($this->config['menu']);
	add_options_page($title,$nav_title, 'manage_options', $page_name, array( &$this,'options_page'));
	}
 
function options_page() {
	printf('</pre><div><h2>%s</h2>%s<form action="options.php" method="post">',$this->config['menu']['title'],$this->config['menu']['intro_text']);
	settings_fields($this->config['group']);
	do_settings_sections($this->config['menu']['page_name']);
	printf('<p><input type="submit" name="Submit" value="%s" /></p></form></div><pre>',__('Save Changes'));
	}
 
function admin_init(){
  foreach ($this->config["sections"] AS $section_key=>$section_value) :
    add_settings_section($section_key, $section_value['title'], array( &$this, 'section_text'), $this->config['menu']['page_name'], $section_value);
    foreach ($section_value['fields'] AS $field_key=>$field_value) :
      add_settings_field(
			sprintf("%s_%s",$this->config['group'],$field_key), 
			$field_value['label'], 
			(!empty($field_value['function'])) ? $field_value['function'] : array(&$this, 'setting_'.$field_value['type']), 
			$this->config['menu']['page_name'],
			$section_key,
			array_merge($field_value,array('name' => $this->config['group'].'_'.$field_key))
			);
      register_setting(
			$this->config['group'], 
			sprintf("%s_%s",$this->config['group'],$field_key),
			(isset($field_value['callback']) && !empty($field_value['callback'])) ? $field_value['callback'] : NULL
			);
      endforeach;
    endforeach;
  }
 
function section_text($value = NULL) {
	printf($this->config['sections'][$value['id']]['description']);
	}
 
function setting_text($value = NULL) {
	$options = get_option($value['name']);
	$default_value = (isset($value['default_value']) && !empty ($value['default_value'])) ? $value['default_value'] : NULL;
	printf('<input id="%s" type="text" name="%1$s[text_string]" value="%2$s" size="%3$d" maxlength="%4$d" /> %5$s%6$s',
		$value['name'],
		(!empty ($options['text_string'])) ? $options['text_string'] : $default_value,
		($value['length']<40) ? $value['length'] : 40,
		$value['length'],
		(!empty ($value['suffix'])) ? $value['suffix'] : NULL,
		(!empty ($value['description'])) ? sprintf("<br /><em>%s</em>",$value['description']) : NULL);
  }

function setting_radio($value = NULL,$labels = NULL) {
	$options = get_option($value['name']);
	$currentValue = (isset($options['text_string']) && $options['text_string']!==NULL) ? $options['text_string'] : 
		((isset($value['default_value']) && !empty ($value['default_value'])) ? $value['default_value'] : NULL);
	if ($labels==NULL) $labels = array ('1' => "Yes", '0' => "No");
	foreach ($labels AS $labelKey => $labelValue) :
		printf('<input id="%1$s_%2$s" type="radio" name="%1$s[text_string]" value="%2$s" %4$s /><label for="%1$s_%2$s">%3$s</label><br />',
			$value['name'],
			$labelKey,
			$labelValue,
			($currentValue==$labelKey) ? "CHECKED" : NULL);
		endforeach;
 }


  
function setting_dropdown($value = NULL) {
  $options = get_option($value['name']);
  $default_value = (!empty ($value['default_value'])) ? $value['default_value'] : NULL;
  $current_value = ($options['text_string']) ? $options['text_string'] : $default_value;
    $chooseFrom = array();
    $choices = $this->config['dropdownOptions'][$value['dropdown']];
  foreach($choices AS $key=>$option) $chooseFrom[]= sprintf('<option value="%s" %s>%s</option>',$key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
  printf('<select id="%s" name="%1$s[text_string]">%2$s</select>%3$s',$value['name'],implode("",$chooseFrom),(!empty ($value['description'])) ? sprintf("<br /><em>%s</em>",$value['description']) : NULL);
  }
 
//end class
}
?>