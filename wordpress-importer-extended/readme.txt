=== WordPress Importer Extended ===
Contributors: nkuttler
Author URI: http://www.nicolaskuttler.com/
Plugin URI: http://www.nicolaskuttler.com/wordpress-plugin/auto-import-wxr-files/
Donate link: http://www.nicolaskuttler.com/donations/
Tags: admin, plugin, development, content generation, theme development, plugin development, development, developer, auto import, import, WXR, reset
Requires at least: 3.1
Tested up to: 3.2
Stable tag: 0.3

Auto import content on plugin activation, or import a file from the server.

== Description ==
Well, the WordPress importer is kinda flawed, but as a developer I use it quite often. One of the more annoying things is that you can't simply import a file that's on the server. This plugin makes this possible. Please make a full backup of your WordPress install before attemting such an import.

Another feature is the auto-import of files during the plugin activation. This is especially useful in combination with the WordPress Reset plugin. Just reset the blog and auto-import all the content you need. Right now you'll need my patched version of the reset plugin that you can download from the plugin page on my site.

More recommended developer tools:

 * [WordPress Reset](http://wordpress.org/extend/plugins/wordpress-reset/)
 * [Better Lorem Ipsum Generator](http://wordpress.org/extend/plugins/better-lorem)
 * [WordPress Importer Extended](http://wordpress.org/extend/plugins/wordpress-importer-extended)
 * [Rebuild Post Thumbnails](http://wordpress.org/extend/plugins/ajax-thumbnail-rebuild/)
 * [Show Template](http://wordpress.org/extend/plugins/show-template/)
 * [Debug Bar](http://wordpress.org/extend/plugins/debug-bar/)
 * [Theme Unit tests](http://codex.wordpress.org/Theme_Unit_Test)

= Other plugins I wrote =

 * [Better Lorem Ipsum Generator](http://www.nicolaskuttler.com/wordpress-plugin/wordpress-lorem-ipsum-generator-plugin/)
 * [WordPress Importer Extended](http://www.nicolaskuttler.com/wordpress-plugin/wordpress-importer-extended/)
 * [Custom Avatars For Comments](http://www.nicolaskuttler.com/wordpress-plugin/custom-avatars-for-comments/)
 * [Better Tag Cloud](http://www.nicolaskuttler.com/wordpress-plugin/a-better-tag-cloud-widget/)
 * [Theme Switch](http://www.nicolaskuttler.com/wordpress-plugin/theme-switch-and-preview-plugin/)
 * [MU fast backend switch](http://www.nicolaskuttler.com/wordpress-plugin/wpmu-switch-backend/)
 * [Visitor Movies for WordPress](http://www.nicolaskuttler.com/wordpress-plugin/record-movies-of-visitors/)
 * [Zero Conf Mail](http://www.nicolaskuttler.com/wordpress-plugin/zero-conf-mail/)
 * [Move WordPress Comments](http://www.nicolaskuttler.com/wordpress-plugin/move-wordpress-comments/)
 * [Delete Pending Comments](http://www.nicolaskuttler.com/wordpress-plugin/delete-pending-comments)
 * [Snow and more](http://www.nicolaskuttler.com/wordpress-plugin/snow-balloons-and-more/)

== Installation ==

You have to install the normal [WordPress Importer](http://wordpress.org/extend/plugins/wordpress-importer/) plugin before you can do anything with this one.

= For manual import =

1. Make a full backup of your WordPress install.
2. Unzip
3. Upload to your plugins directory
4. Enable the plugin
5. Add define('WP_LOAD_IMPORTERS', true); to your wp-config.php file. Remember to disable this after the import has finished.
6. Add define('WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS', true); to your wp-config.php file if you with to download the attachments.
7. Go to Tools->WordPress Importer Extended and enter the path to your WXR file (relative from the WordPress root directory).

= For automatic import on activation =

Upload the plugin. Then you have to set
`<?php
define( 'WP_LOAD_IMPORTERS', true );
?>`
in your wp-config.php file. To specify which files to import use for example
`<?php
define('WORDPRESS_IMPORTER_EXTENDED_AUTO', 'wp-content/test-data.2011-01-17.xml');
?>`
Then you just need to activate the plugin to start the import. The recommended method is to use the WordPress Reset plugin and to configure this plugin and the normal WordPress Import to autostart by adding this to your wp-config.php:
`<?php
define( 'WORDPRESS_RESET_REACTIVATE_PLUGINS', 'wordpress-reset/wordpress-reset.php,wordpress-importer/wordpress-importer.php,wordpress-importer-extended/wordpress-importer-extended.php' );
?>`
If you wish to download the attachments specify define('WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS', true); 

== Screenshots ==

None yet.

== Frequently Asked Questions ==

None yet.

== Changelog ==
= 0.3 ( 2011-05-24 ) =
 * Update documentation
= 0.2 ( 2011-05-23 ) =
 * Misc bugfixes
 * Allow importing of attachments
 * First public release
= 0.1 ( 2011-05-22 ) =
 * Autoimport on activation works
 * Manual import works
