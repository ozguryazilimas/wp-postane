=== Sharing is Caring ===
Contributors: michaelbeacom
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SKTSKKWDGHGJ6
Tags: Facebook, like, Twitter, tweet, Google Plus, plus, Pinterest, pin, Social Media, share, opengraph, schema.org, xfbml, html5, shortcode, custom hook
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: trunk

Displays the social widgets from Facebook, Twitter, Google+ and Pinterest with your posts. Also adds some meta tags for opengraph and schema.org.

== Description ==

Displays the social widgets from Facebook, Twitter, Google+ and Pinterest with your posts. It also adds some meta tags for opengraph and schema.org as recommended by Facebook and Google. The standard method to do this is by filtering the content of the posts, which is controlled by the options in the admin menu.  Buttons can also be displayed by the shortcode [sic] with ability to disable individual buttons for that instance of the shortcode. You can also add it to your theme with do_action('sic_sharing'); where you want it to render. 

Allows some custom text and css insertion as well as most options for the buttons in the admin page. Compatible with html5 (optional).
== Installation ==

This section describes how to install the plugin and get it working.

Manual Install

* Download the plugin from the wordpress plugin directory
* Unzip the plugin
* Upload /sharing-is-caring/ directory to the /wp-content/plugins/ directory
* Activate the plugin through the 'Plugins' menu in WordPress

Automatic Install

* Go to the admin page and select the 'Plugins' menu, use the 'Add new' menu item
* Search for the plugin
* Press the "Install Now" button
* Activate the plugin

Administer it from the 'Sharing is Caring' submenu of the 'Settings' menu.

== Frequently Asked Questions ==

= Where can I get support for this plugin? =

Comment on the post at http://michaelbea.com/sharing-is-caring/

== Screenshots ==

1. Buttons
2. Admin page options part 1: basic and extended settings
3. Admin page options part 2: controls for the individual buttons

== Changelog ==

= 1.0 =
* Initial release

= 1.1 =
* Fixed: meta tags for each post were listed on pages with multiple posts
* Fixed: apostrophes and quotes were improperly escaped in description meta tags

= 1.1.1 =
* Fixed: Pinterest button only displayed horizontal count
* Updated screenshots

= 1.1.2 =
* Fixed: Fatal Error on conflict with duplicate funtion name in another plugin

= 1.1.3 =
* Fixed: html tags in post titles causing problems with closing quotes

= 1.2 =
* Added: support for custom post type index pages

= 1.3 =
* Added: optional title for button box
* Added: sic-box, sic-title, and sic-button classes for better css flexibility

= 1.4 =
* Added: [sic] shortcode for manual display

= 1.4.1 =
* Added: [sic] shortcode options to disable display of specific buttons
* Added: classes sic-facebook, sic-twitter, sic-gplus, sic-pinterest for better css flexibility
* Added: check for existence of sic_sharing hook for theme integration

= 1.4.2 =
* Fixed: using the last post in the loop when on homepage in some instances.

= 1.4.3 =
* Fixed: using the homepage url for every post on the homepage

== Upgrade Notice == 

= 1.0 =
* Initial release

= 1.1 =
* some bugfixes with the opengraph and schema.org meta tags

= 1.1.1 = 
* bugfix for pinterest button only showing horizontal count

= 1.1.2 =
* fix for conflict with another plugin with duplicate function name

= 1.1.3 =
* fix for problems caused by html tags in post titles

= 1.2 =
* added support for custom post type index pages

= 1.3 =
* added title and css classes

= 1.4 =
* added shortcode

= 1.4.1 =
* minor updates to shortcode functionality, css, and theme integration

= 1.4.2 =
* minor bugfix with shortcode using the wrong url in some uses on the homepage

= 1.4.3 =
* bugfix for posts on the homepage using the homepage url