=== Login-Logout ===
Contributors: webvitaly
Donate link: http://web-profile.net/donate/
Tags: login, logout, widget, meta, sidebar, admin, register
Requires at least: 4.0
Tested up to: 5.0
Stable tag: 3.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Widget with login, logout, admin and register links. Replacement of the default Meta widget.

== Description ==

> **[Speedup WordPress](http://codecanyon.net/item/silver-bullet-pro/15171769?ref=webvitalii "Speedup and protect WordPress in a smart way")** |
> **[Login-Logout](http://web-profile.net/wordpress/plugins/login-logout/ "Plugin page")** |
> **[Donate](http://web-profile.net/donate/ "Support the development")** |
> **[GitHub](https://github.com/webvitalii/login-logout "Fork")**

"Login-Logout" plugin adds widget with login or logout link. Also can be shown register or site-admin link. It is the replacement of the default Meta widget.

= If user is not logged in there are such links: =
* login (after login action user will return to previous page);
* register (if user can register) (if checkbox is active);

= If user is logged in there are such links: =
* welcome text with link to user profile (if checkbox is active);
* logout (after logout action user will return to previous page);
* site admin (if checkbox is active);


= Useful: =
* **[Silver Bullet Pro - Speedup and protect WordPress in a smart way](http://codecanyon.net/item/silver-bullet-pro/15171769?ref=webvitalii "Speedup and protect WordPress in a smart way")**
* **[Anti-spam Pro - Block spam in comments](http://codecanyon.net/item/antispam-pro/6491169?ref=webvitalii "Block spam in comments")**


== Screenshots ==

1. Login-Logout widget
2. Widget output when user is not logged in
3. Widget output when user is logged in
4. Widget output when user is logged in and inline option is on

== Changelog ==

= 3.8 =
* Fixed the PHP 7.2 Deprecated Notice on create_function. Thanks to joneiseman - https://wordpress.org/support/users/joneiseman/

= 3.7 =
* Using wp_registration_url() instead of hardcoding registration link. Thanks to dmasin - https://profiles.wordpress.org/dmasin/

= 3.6 =
* Bugfixing

= 3.5 =
* Bugfixing
* Code refactoring
* Added LOGIN_LOGOUT_PLUGIN_VERSION constant

= 3.4 =
* Added 'https' support. Thanks to Sven

= 3.3 =
* Added 'login_logout_username_link' filter. Idea and code provided by Jon

= 3.2 =
* One more replace PHP4 style constructors with PHP5 constructors

= 3.1 =
* Replace PHP4 style constructors with PHP5 constructors - https://make.wordpress.org/core/2015/07/02/deprecating-php4-style-constructors-in-wordpress-4-3/

= 3.0 =
* Minor bugfixing
* Code cleanup

= 2.9 =
* French translation made by Jean-Michel HAROUY - http://www.ceism-angers.fr/

= 2.8 =
* Czech translation made by Daniel Čermák - http://danielcermak.eu/

= 2.7 =
* Spanish translation made by Maria Ramos from WebHostingHub

= 2.6 =
* Serbian translation made by Borisa Djuraskovic from WebHostingHub

= 2.5 =
* minor changes

= 2.4 =
* minor changes

= 2.3 =
* Fixing minor bugs

= 2.2 =
* Added extra item for the list of items

= 2.1 =
* Added html tags support in many fields
* Added html classes to list items

= 2.0.1 =
* Link to profile bugfix

= 2.0.0 =
* Added inline option
* Added welcome text

= 1.8.0 =
* Translation ready

= 1.7.0 =
* Added "login text", "logout text", "register text" and "site admin text" options

= 1.6.0 =
* Splitting "redirect to" option into "redirect to after login" and "redirect to after logout"

= 1.5.0 =
* Added "redirect to" option

= 1.4.0 =
* Change method of widget registering

= 1.3.0 =
* Removed title if it is empty

= 1.2.0 =
* Split register and admin links

= 1.1.0 =
* Added show or hide register or site-admin link

= 1.0.0 =
* Initial release

== Installation ==

1. Install and activate the plugin on the Plugins page
2. Add "Login-Logout" widget to your sidebar and customize it