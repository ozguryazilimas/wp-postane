<?php
class ob_page_numbers_values {
var $group = "ob_page_numbers"; // defines setting groups (should be bespoke to your settings) 

var $menu = array( 
	'page_name' => "ob_page_numbers", // defines which pages settings will appear on. Either bespoke or media/discussion/reading etc
	'title' => "OB Page Numbers",  // page title that is displayed 
	'intro_text' => "This allows you to configure the page numbers exactly the way you want it", // text below title
	'nav_title' => "OB Page Numbers" // how page is listed on left-hand Settings panel
	);

var $sections = array(
    'page_numbers' => array(
			'title' => "Display options",
			'description' => "Settings to do with how the plugin is displayed",
			'fields' => array (
	'theme' => array (        
			'type' => 'dropdown', 
			'label' => "Theme",
			'description' => "Choose your prefered theme",
			'default_value' => "classic",
			'dropdown' => "ddTheme"
			),
    'limitPages' => array (
			'type' => 'text',
			'label' => "Number of buttons",
			'description' => "How many buttons should appear at any one time?",
			'default_value' => "10",
			'length' => 2
			),
	'isPageOfPage' => array (
			'type' => 'radio',
			'label' => "Show 'page of page' details?",
			'default_value' => "1"		
			),
    'pageOfPageText' => array (
			'type' => 'text',
			'label' => "Page of Page text",
			'description' => "How to display the page of page button. Use %u to indicate the numbers.",
			'default_value' => "Page %u of %u",
			'length' => 30
			),
	'isNextPrevButton' => array (
			'type' => 'radio',
			'label' => "Show next and previous buttons?",
			'default_value' => "1"		
			),
    'prevPage' => array (
			'type' => 'text',
			'label' => "Previous page",
			'description' => "What to display on previous page button",
			'default_value' => "&lt;",
			'length' => 30
			),
    'nextPage' => array (
			'type' => 'text',
			'label' => "Next page",
			'description' => "What to display on next page button",
			'default_value' => "&gt;",
			'length' => 30
			),
	'isFirstLastNumbers' => array (
			'type' => 'radio',
			'label' => "Show first and last pages?",
			'default_value' => "1"		
			),
	'isFirstLastGap' => array (
			'type' => 'radio',
			'label' => "Show gap between first and last buttons?",
			'default_value' => "1"		
			),
    'firstGap' => array (
			'type' => 'text',
			'label' => "First gap",
			'description' => "What to display to indicate gap to first page",
			'default_value' => "...",
			'length' => 30
			),
    'lastGap' => array (
			'type' => 'text',
			'label' => "Last gap",
			'description' => "What to display to indicate gap to last page",
			'default_value' => "...",
			'length' => 30
			)
          )
        )
    );

var $dropdownOptions = array (
    'ddTheme' => array ('classic' => 'Classic', 'Default' => 'Default', 'panther' => 'Panther', 'stylish' => 'Stylish', 'tiny' => 'Tiny', '10marifet-pager' => '10marifet')
    );

//  end class
};
	
?>