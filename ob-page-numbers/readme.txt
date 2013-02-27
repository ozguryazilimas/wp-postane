=== OB Page Numbers ===
Contributors: OllyBenson
Version: 1.1
Author: Olly Benson, Jens T&ouml;rnell
Author URI: http://code.olib.co.uk
Tags: navigation, paging, page, numbers, archive, categories, plugin, seo
Tested up to: 3.2.1
Stable tag: 1.1.1

A simple paging navigation plugin for users and search engines. Instead of next and previous page it shows numbers and arrows. Settings available.

== Description ==

This plugin is based on the <a href="http://www.jenst.se/2008/03/29/wp-page-numbers">wp-page-numbers</a> plugin built by Jens T&ouml;rnell.  
The concept and css come from that, but the plugin has been totally recoded.

Please note: As with all pagination plugins, this plugin requires that you amend some coding in your theme. See the Installation guide.

= User friendly navigation =
* With page numbers instead of next and previous links users can easily navigate much quicker to the page they want. 
* It can help with SEO (Search Engine Optimisation) because it creates a tighter inner link structure. 
* Works with all well known browsers (Internet Explorer, Firefox, Opera and Safari).


== Installation ==

1. Upload the FOLDER 'ob-page-numbers' to the /wp-content/plugins/
2. Activate the plugin 'OB Page Numbers' through the 'Plugins' menu in admin
3. Make the code changes below.
4. Go to 'Options' or 'Settings' and then 'OB Page Numbers' to change the options

= Code changes =

This is the code change for the default Wordpress theme.  If you're using a different theme you may need to amend the code differently.

Replace the the 'next_posts_link()' and 'previous_posts_link()' with the code below in your theme (archive.php, index.php or search.php).<br />

<code><?php if(function_exists('ob_page_numbers')) { ob_page_numbers(); } ?></code>

== Screenshots ==

Below are the five styles you can choose from:

1. Classic
2. Default
3. Panther
4. Stylish
5. Tiny


== Changelog ==

= Verson 1.1.1 =

This update fixes a bug that means the final page is missed off the pagination list.


= How do I report a bug? =

* Contact me <a href="http://code.olib.co.uk/2011/11/20/wordpress-page-numbers-2/">here</a>. 
* Describe the problem as well as you can, your plugin version, Wordpress version and possible conflicting plugins and so on.
