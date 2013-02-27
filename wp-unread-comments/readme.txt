=== WP Unread Comments ===
Contributors: Liz Lemon
Donate link: https://www.paypal.com/us/cgi-bin/webscr?cmd=_flow&SESSION=hhbZ8E50U4mP8FZXd3mNaXAsGJumpoUjXVvsPjtHkuJIpm23JREhsZeYIu8&dispatch=5885d80a13c0db1f8e263663d3faee8dcbcd55a50598f04d927139403713ca13
Tags: comments, unread comments, unread comments widgets, wordpress unread comments, view unread comments
Requires at least: 3.3.1
Tested up to: 3.3.1
Stable tag: 1.0.0

This plugin displays unread comments in a sidebar widget and can highlight unread comments in comment lists.

== Description ==

**Support for this plugin at http://www.mclemon.org/?page_id=544**

This plugin displays unread comments in a sidebar widget and can highlight unread comments in comment lists.  This is useful when you have an active comments section on a blog and want users to be able to find the newest comments easily.  This is analagous to View Unread Posts on a phpbb forum.  If logged in, the user will be able to click on the title of the post in the widget to find the oldest of the unread comments in the list.

I created this plug-in to fill a need that I had for my readers on a more active blog.  Most of these readers are used to reading forums where they can easily find a list of all the threads that have new comments and by clicking an icon, be taken directly to the oldest new comment in their list.  I was unable to find any plug-ins in Wordpress to do something like this so I decided to write this one.  I've customized it as much as I could think to for other users to use it with some flexibility.  

== Installation ==

For WordPress 3.+:

1. Download the last version of the plugin from its page.
1. Upload the .zip through the Plugins/Add new/Upload of the WordPress administrator.
1. Activate the plugin through the Plugins menu of the WordPress administrator.
1. Pull the widget named WP Unread Comments into your sidebar.

This plug in will work as is, but if you find that the unread list in the sidebar doesn't clear until you reload the page, you can take the following steps for a more fine-tuned updating of reader times.

Find the plug-in file wp-unread-comments.php and comment out the lines

`add_action('get_header', 'wuc_get_time');`
`add_action('get_footer', 'wuc_set_time');`

In your templates comments.php file, add the following line at the top of the file:
`<?php do_action('wuc_get_time');?>`
In your templates comments.php file, add the following line at the end of the file:
`<?php do_action( 'wuc_set_time');?>`



== Frequently Asked Questions ==

= What are the options for this plug in? =

1. Title: This is what appears when a user is logged in.  Default: Unread Comments
1. Number of unread comments to show:  This limits how many posts are displayed at a time when a user is logged in.
1. Highlight Unread Comments: If you check this on, the unread comments will appear in the reader's list with a background color to set them apart from the read comments. Once a user has read those comments, they will appear as your template color. The color for unread comments is set in /css/wp-unread-comments.css.  If you make a change, you will likely need to clear your cache to see the new color.
1. Title for users who are not logged in: You might want this to read Recent Comments or Unread Comments depending on what you are going to show in the widget based on the following options.
1. Show recent comments if users are not logged in: This will cause the widget to revert to default wp Recent Comments logic for guests or users who aren't logged in.
1. Number of recent comments to show: This limits how many comments are displayed. Similar to the Recent Comments widget.
1. Show custom text if users are not logged in:  You might want to alert readers that they can log in to see the Unread Comments feature.  
1. Custom text: The value to show if the above is checked.  Default: You must be logged in to view unread comments.

