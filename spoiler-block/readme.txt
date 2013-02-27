=== Spoiler Block ===
Author: squiter
Contributors: squiter
Tags: spoiler, block text, content spoiler, spoilers, hidden content, content
Requires at least: 3.2.1
Tested up to: 3.4.1
Stable tag: 1.7
Licence: GPLv2

Plugin to block spoilers in your posts.

== Description ==

This plugin create a button in your Wordpress editor to set some parts of your posts as spoilers.
These parts show a blocked content to your visitors, then must click in these blocks to show the original content.

= Translators =

* Brazilian (pt_BR) - [Brunno dos Santos](http://brunno.me/spoiler-block)
* Romanian (ro) - [Alexander Ovsov](http://webhostinggeeks.com)


== Installation ==

To do a new installation of the plugin, please follow these steps:

1. Download the zipped plugin file to your local machine.
2. Unzip the file.
3. Upload the `spoiler-block` folder to the `/wp-content/plugins/` directory.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Optionally, go to the Options page and set a new spoiler alert.

If you have already installed the plugin:

1. De-activate the plugin.
2. Download the latest files.
2. Follow the new installation steps.

To set a Spoiler block, select a post, or create a new one, select any text then click in spoiler button in your Wordpress Editor. Your text now is set as spoiler.

To remove a spoiler block, just select the spoiler block then click in spoiler button.

Brunno dos Santos
spoilerblock@brunno.me
http://brunno.me/spoiler-block

== Changelog ==

= 1.7 =
* Now the spoilers has a span that hidden the content the spoiler with CSS (thanks to Mario Ludwig);
* Resolve some Bugs;

= 1.6.4 =
* Added function to close the spoiler block when user click

= 1.6.3 =
* Added Romanian translation by Alexander Ovsov http://webhostinggeeks.com/

= 1.6.2 =
* Change contact e-mails and sites urls

= 1.6.1 =
* Bug fixes

= 1.6 =
* Resolve some bugs;
* Add i18n support;
* Include Brazilian Portugueses language;

= 1.5 =
* Now you can clear spoiler block if you click in spoiler button! :)

= 1.2 =
* Correction in CSS Queue.

= 1.1 =
* Add missing files to Wordpress SVN

== Upgrade Notice ==

= 1.5 =
* Now you can clear spoiler block if you click in spoiler button! :)

= 1.2 =
* Correction in CSS Queue.

= 1.1 =
* Some files are missing in svn commit, old versions does't work.

== Screenshots ==

1. Wordpress Editor with Spoiler Block button
2. Editing a spoiler post
3. Show a post with spoiler
4. Show a post with spoiler open

== Frequently Asked Questions ==

= How can I remove a spoiler of my post? =

To remove a spoiler block, just select the spoiler block then click in spoiler button.

= How can I customize the spoilers blocks in my blog? =

To customize the blocks of spoilers, just add the follow selectors in your css file:
* span.spoiler to spoilers closed blocks;
* span.spoiler-open to spoilers already clicked by users;