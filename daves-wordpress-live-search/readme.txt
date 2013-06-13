=== Dave's WordPress Live Search ===
Contributors: csixty4
Donate link: http://catguardians.org
Tags: search, AJAX, live search
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 3.3
License: MIT
License URI: http://www.opensource.org/licenses/mit-license.php

Adds "live search" functionality to your WordPress site. Uses the built-in search and jQuery.

== Description ==

Dave's WordPress Live Search adds "live search" functionality to your WordPress site. As visitors type words into your WordPress site's search box, the plugin continually queries WordPress, looking for search results that match what the user has typed so far.

The [live search](http://ajaxpatterns.org/Live_Search) technique means that most people will find the results they are looking for before they finish typing their query, and it saves them the step of having to click a submit button to get their search results.

This functionality requires Javascript, but the search box still works normally if Javascript is not available.

http://www.youtube.com/watch?v=7CGR2bJ1mLM
[transcript](http://plugins.svn.wordpress.org/daves-wordpress-live-search/assets/intro_video_transcript.txt)

This plugin is compatible with the [xLanguage](http://wordpress.org/extend/plugins/xlanguage/) and [WPML](http://wpml.org/) plugins for internationalization (i18n) of search results.

This plugin also integrates with the Relevanssi plugin for improved search results.

**Translators Wanted**

Since v2.3, Dave's WordPress Live Search supports multiple languages, but I need native speakers and translators.

Interested? Visit [this project's CrowdIn page](http://crowdin.net/project/daves-wordpress-live-search/invite) to get started!

BIG THANKS to all these CrowdIn users who submitted translations already: abibouba, andreio, baky1er, bps, bugmeniet, cinetrailer, Jess-Nielsen, jganer, kabboch, Kriszta, levati, malaa83, rebelidea, Remco_Landegge, sella, thambaru, tunglam, and vderLinden AS WELL AS everyone who submitted a translation before the switch to CrowdIn: Klemen Tušar, Jesper Hessius, Daniele of W3B.it, Andreu Llos, Utku Sönmez, Maxime Chevasson, Paul Göttgens, Łukasz Wilkowski, Sociedade Euromilhoes, and Norbert Grund.

You guys rock.

== Installation ==

http://www.youtube.com/watch?v=7CGR2bJ1mLM

Dave's WordPress Live Search is a plugin that brings live search, sometimes called "autocomplete", to your WordPress site. Many of the web's most popular destinations use this technique so customers can get right to the information or products they're after, and now you can deliver that same experience to your site's visitors. Like WordPress itself, it's designed to be simple to set up and use right away, but powerful and customizable for advanced users.

You can install the plugin right from WordPress itself by going to the Plugins section, clicking "Add New", and searching for "Dave's WordPress Live Search". Click "install now" to add the plugin to your site. If you prefer to install the plugin manually, it's available for download in the WordPress plugins directory.

After you install the plugin, go to the Settings menu and choose "Live Search". Let's take a look at the main "settings" tab, which controls how the plugin looks and behaves. The "Maximum Results to Display" option lets you limit the number of search results your users see. If you have a large site, you should set this to 5 or less or it might take a long time to display. Remember, your visitors can still click the "search" button like they normally would to get all the matching results. The "Minimum characters" option lets you choose how much people need to type before the plugin starts offering results. The longer you wait before showing results, the better the first set of results will be.

The "Results Direction" setting lets you choose whether the results should grow up or down from the search box. People are used to seeing the results underneath the box, but if space is tight on your site or blog, you should show them above the search box to make sure they fit on the screen.

You can also choose what's included with the search results. They can include a thumnail image, excerpt, even who wrote the content and when. You may want to include the "View more results" link at the bottom of the search results, so users can click through to see more results.

Dave's WordPress Live Search ships with three color schemes: gray, red, and blue. Advanced users and developers can use the other styling options to better match their site's theme.

When you're ready, save your changes, and Dave's WordPress Live Search is ready to use. It automatically attaches itself to any search boxes in your theme, or any search widgets you add, giving your site's visitors a better search experience with a minimum of fuss.

== Screenshots ==

1. Demonstration of Dave's WordPress Live Search

== Frequently Asked Questions ==

== Filters ==

1. dwls_alter_results
1. dwls_attachment_thumbnail
1. dwls_post_title
1. dwls_the_excerpt
1. dwls_post_date
1. dwls_author_name

== Wish List ==

Features I want to implement in future releases:

1. "No results found" message (optional)

== Changelog ==

= 3.3 =
* 2013-06-05 Dave Ross <dave@csixty4.com>
* Added search results width to the customizer
* Faster startup on AJAX requests (fewer includes & hooks registered)
* If SCRIPT_DEBUG is set, use non-minified scripts
* Now uses a proper loop to get search results. Hopefully improves compatibility with other search plugins
* The parameters to the dwls_alter_results hook have changed: array of results (instead of a query object), deprecated (always -1), DavesWordPressLiveSearchResults instance.
* Now uses WordPress's native submit_button() function to generate submit buttons in the admin interface for more consistent styling with the rest of admin
* Requires WordPress 3.5 or higher

= 3.2 =
* 2013-01-22 Dave Ross <dave@csixty4.com>
* Fixed a Javascript error when resizing a window
* Removed the "debug" tab - wasn't very useful
* Force a high z-index on the spinner

= 3.1.1 =
* 2012-12-21 Dave Ross <dave@csixty4.com>
* Always include the "custom" css file on admin pages. Ensures customizer preview always looks right.

= 3.1 =
* 2012-12-21 Dave Ross <dave@csixty4.com>
* German (de_DE) translation
* Color picker for WP 3.5+

= 3.0.1 =
* 2012-11-07 Dave Ross <dave@csixty4.com>
* Simplified jQuery selector for older jQuery versions

= 3.0 =
* 2012-11-03 Dave Ross <dave@csixty4.com> and many awesome translators!
* Turkish (tr_TR) translation
* French (fr_FR) translation
* Dutch (nl_NL) translation
* Polish (pl_PL) translation
* Portugese (pt_PT) translation
* Improved method of getting the first thumbnail
* Canvas/VML-based spinner eliminates the need for the transparent .gif
* Italian (it_IT) translation
* Spanish (es_ES) translation
* Added hooks & filters for people to plug into DWLS
* Fixed regex parsing issue when permalinks aren't enabled
* Support for multiple search boxes (finally!)
* You can now click anywhere in the result box to go to that result
* WP E-Commerce is officially supported again
* Toggle to enable/disable the_content filter.
* "View more results" is now translatable
* Fix to jQuery dependency definition (great catch by Robert Windisch)
* Rewrote DWLSTransients for faster performance/simplicity

= 2.8 =
* 2012-04-29 Dave Ross <dave@csixty4.com>
* Back-ported fix from 3.0 which sets defaults on activation
* Tested for compatibility with WordPress 3.4

= 2.7 =
* 2011-11-10 Dave Ross <dave@csixty4.com>
* Slovenian (sl_SI) translation
* Swedish (sv_SE) translation
* Various i18N fixes now that I have translations to work with

= 2.6 =
* 2011-09-15 Dave Ross <dave@csixty4.com>
* Fix for search results x positioning

= 2.5 =
* 2011-09-14 Dave Ross <dave@csixty4.com>
* WPML compatibility (thanks for the license!)
* Pruned some outdated code
* Better handling of Javascript config variables

= 2.4 =
* 2011-08-29 Dave Ross <dave@csixty4.com>
* Fix for i18n loading issue

= 2.3 =
* 2011-08-03 Dave Ross <dave@csixty4.com>
* Added a hook to keep Relevanssi integration out of the main code
* i18n for admin screens and user-facing text

= 2.2 =
* 2011-07-27 Dave Ross <dave@csixty4.com>
* Option to disable "View more results" link

= 2.1.1 =
* 2011-07-16 Dave Ross <dave@csixty4.com>
* Fix for Relevanssi issue
* Passing correct $capability string to admin_menu() - thanks lag47!
* Configurable excerpt length (for Alok)

= 2.1 =
* 2011-05-20 Dave Ross <dave@csixty4.com>
* Major code cleanup & streamlining
* Added z-index:999999 to all the default CSS files
* Removed jQuery Dimensions plugin. It's in jQuery core > 1.2.6 & recent WP releases include jQuery 1.4
* (Hopefully) fixed IE8 hang issue (jQuery Dimensions)

= 2.0.2 =
* 2011-03-09 Dave Ross <dave@csixty4.com>
* Fix for determining plugin URL when WordPress is installed in a subdirectory
* Sprinkled in static declarations where needed
* Better? fix for determining plugin URL when WP Subdomains is installed

= 2.0.1 =
* 2011-02-28 Dave Ross <dave@csixty4.com>
* Pass WP_Query a parameter telling it to only return "publish" items (no drafts) -- WP3.1 regression case?

= 2.0 =
* 2011-02-24 Dave Ross <dave@csixty4.com>
* Using wp_ajax actions now (deprecating my bootstrap on every AJAX call)
* Static Javascript (not generated) and inline configuration
* Cached search results for anonymous users
* Debug tab for advanced users. Just shows cache dump for now.
* WordPress 3.1 compatibility!

= 1.20 =
* 2011-01-10 Dave Ross <dave@csixty4.com>
* Generate a static version of the Javascript file

= 1.19 =
* 2011-01-09 Dave Ross <dave@csixty4.com>
* Fixed bug with Relevanssi and setting to display 0 (unlimited) posts
* Display an alert if another plugin is loading jQuery < 1.2.6

= 1.18 =
* 2010-12-04 Dave Ross <dave@csixty4.com>
* Search "any" content (posts, pages, custom post types)
* Better compatibility with Relevanssi plugin (worked with Mikko from that project)
* Minor jQuery performance improvements

= 1.17 =
* 2010-10-25 Dave Ross <dave@csixty4.com>
* Split options into "Settings" and "Advanced" tabs
* X Offset setting to position the results dropdown
* Include pages (as well as posts) in search results
* Raised minimum requirement to WP 2.9 (required for "Include pages" change)

= 1.16 =
* 2010-10-07 Dave Ross <dave@csixty4.com>
* Fixed "max results" functionality lost when implementing WP E-Commerce compatibility
* Compatibility w/servers that don't allow getimagesize() to use URLs
* Merged in Ron Schirmacher's code for WP E-Commerce tag & meta search
* Compatibility with WP E-Commerce when table names are used
* Fix for autocomplete suppression

= 1.15.1 =
* 2010-08-17 Dave Ross <dave@csixty4.com>
* Default hidden source option to 0 (search WP, not WP E-Commerce)

= 1.15 =
* 2010-08-12 Dave Ross <dave@csixty4.com>
* Option to not include any additional CSS file (thanks Andy Hall!)
* Support for xLanguage plugin
* Support for searching WP E-Commerce products
* Replaced clearfix that was somehow lost

= 1.14 =
* 2010-07-03 Dave Ross <dave@csixty4.com>
* z-index:9999 for cadbloke to keep search results above all other content (that doesn't already have a z-index of 9999 or higher)
* Hidden results display when resizing window http://wordpress.org/support/topic/410612

= 1.13 =
* 2010-05-02 Dave Ross <dave@csixty4.com>
* Fixed changelog formatting
* Reposition the search results popup when the window is resized
* Send AJAX results with a test/javascript content-type

= 1.12 =
* 2010-03-23 Dave Ross <dave@csixty4.com>
* Now compatible with WP-Subdomains plugin
* Format dates using WordPress date setting
* Added MIT license text to daves-wordpress-live-search.js.php and daves-wordpress-live-search-bootstrap.php files

= 1.11 =
* 2010-02-24 Dave Ross <dave@csixty4.com>
* Support for wp-config.php outside main WordPress directory (2.6+)
* Fix to make "view more results" link work on pages other than home
* Fix path to stylesheet directory
* Fix compatibility with child themes

= 1.10 =
* 2010-02-14 Dave Ross <dave@csixty4.com>
* Added option for minimum number of characters that need to be entered before triggering a search
* More graceful failure message in PHP4
* Added code to ignore E_STRICT warnings when E_STRICT enabled on PHP 5.3
* Possible fix for compatibility with Relevanssi plugin (some concern this isn't working yet)
* Fix for compatibility with child themes
* Added MIT license to DavesWordPressLiveSearch.php class

= 1.9 =
* 2009-12-02 Dave Ross <dave@csixty4.com>
* Tested compatibility with WordPress 2.3-2.9 beta 2
* Fixed stylesheet issue with WordPress 2.5.x
* Set minimum WordPress version to 2.6. Admin page doesn't appear in 2.5.
* Added support for WordPress 2.9 "post thumbnails"
* Put autocomplete="off" on the form instead of the search box (fix for Firefox issue?)
* Use "Display Name" instead of "username"
* Javascript performance improvements
* Added page exception list
* Live search no longer tries to add itself to admin pages

= 1.8 =
* 2009-10-25 Dave Ross <dave@csixty4.com>
* Added note about WP-Minify
* Tested with WordPress 2.8.5
* Moved JavaScript to an external file
* Security - nonce checking on admin screen
* Security - check "manage_options" security setting
* Notes on configuration in readme.txt

= 1.7 =
* 2009-08-27 Dave Ross <dave@csixty4.com>
* Thumbnails in the search results
* Excerpts in the search results

= 1.6 =
* 2009-08-17 Dave Ross <dave@csixty4.com>
* Implemented selectable CSS files
* Fixed a bug that broke live searches containing ' characters

= 1.5 =
* 2009-07-08 Dave Ross  <dave@csixty4.com>
* Fixed compatibility with Search Everything plugin, possibly others

= 1.4 =
* 2009-06-03 Dave Ross  <dave@csixty4.com>
* Renamed release 1.3.1 to 1.4 because WordPress.org doesn't seem to like 1.3.1. Seems like kind of a waste to do a full point release for this
* Building permalinks instead of using post guid (problem with posts imported from another blog)

= 1.3 =
* 2009-05-22  Dave Ross  <dave@csixty4.com>   
* Fixed an annoying bug where the search results div collapsed and expanded again every time an AJAX request completed
* Cancel any existing AJAX requests before sending a new one
* Check for PHP 5.x. Displays an error when you try to activate the plugin on PHP < 5   
* No longer sends the entire WP_Query object to the browser. This was a potential information disclosure issue, plus it was a lot to serialize on the server and parse in the brower
* Minor code cleanup & optimizations
     
= 1.2 =
* 2009-04-10  Dave Ross  <dave@csixty4.com>
* Code cleanup & optimizations 
* Styled the admin screen to fit in with WordPress better 
* New option: display the results above or below the search box 
* Included a note on the admin screen recommending the Google Libraries plugin
	 
= 1.1 =
* 2009-03-30  Dave Ross  <dave@csixty4.com>
* Code cleanup & optimizations
* Fixed compatibility issues with PHP < 5.2.0 and PHP < 5.1.2
* New option: limit the number of results to display
	 
= 1.0 =
* 2009-03-13  Dave Ross  <dave@csixty4.com>
* Initial release

== Upgrade Notice ==

= 3.3 =

Dave's WordPress Live Search v3.3 requires WordPress 3.5 or higher. Older versions of the plugin are still available from http://wordpress.org/plugins/daves-wordpress-live-search/developers/ if needed.

= 3.1.1 =

If you upgraded to v3.1 in the first 15 minutes or so, this fixes a bug where the customizer preview may not have reflected the right colors if one of the other styling options were selected when the page rendered.

= 3.1 =

Tired of default red, gray, and blue? WordPress 3.5 users can now choose their own colors on the admin page. Click the new "appearance" tab and build your own "custom" style!

= 3.0 =

Version 3.0 includes many bug fixes and improvements, including:

* New translations: Turkish (tr_TR), French (fr_FR), Dutch (nl_NL) translation, Polish (pl_PL), Portugese (pt_PT), Italian (it_IT), Spanish (es_ES)
* Canvas/VML-based spinner for better looks on high-DPI displays
* Added hooks & filters for people to plug into DWLS
* Support for multiple search boxes (finally!)
* You can now click anywhere in the result (instead of just the title)
* WP E-Commerce is officially supported again
* Completely rewritten caching for better performance

For a complete list of changes, see [the changelog](http://wordpress.org/extend/plugins/daves-wordpress-live-search/changelog/). 

= 2.6 =
This release fixes the horizontal positioning bug in v2.5.

= 2.5 =
This plugin is now compatible with the WPML internationalization plugin.

= 2.0 =
If you use a caching plugin, please clear your cache after upgrading. New: Performance & compatibility improvements. Works with WordPress 3.1! Debug feature for advanced users.
