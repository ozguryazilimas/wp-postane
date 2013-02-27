=== Plugin Name ===
Contributors: towardstech
Donate link: http://answer2me.com/
Tags: post, views, post view, count views
Requires at least: 2.7 or higher
Tested up to: 3.1.1
Stable tag: trunk

== Description ==

**For Users:**

This plugin allow you to display every visits/views count in each post.
This plugin counts every views as long as you refresh the page or view the page, it does not uniquely capture visitors.
Easily copy paste of 1 line of code into your wordpress file to display the views in the post.

**For Developers:**

This plugin also contain detailed source code for developers who just started plugin development for wordpress,
a suitable plugin source code to take a look on how to use database manipulation, commenting and simple hooks.
I hope this would assist you in your learning journey for wordpress plugin. 

== Installation ==

1. Upload `wp-post-view folder` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place echo_post_views(get_the_ID()); anywhere in the file codes to display AFTER <?php if (have_posts ()) : while (have_posts ()) : the_post(); ?> in single.php file.

== Frequently Asked Questions ==


= Why reinvent the wheel? So many plugin has done this? =

This plugin is kept simple for users and developers in mind,
users can easily integrate this plugin intheir wordpress to check the amount of views in their post without much hassle.
This is also simple as it does not contain heavy graphs, charts or crazy statistics, every view is already displayed in each row in the posts of the admin panel.

For developers: You get to see a single source file with commented of each function and how to do simple database manipulation as well as integrating hooks.


= How do I integrate this plugin into my posts? =

Easily place echo_post_views(get_the_ID()); anywhere in the your file codes usually(single.php) to display ONLY AFTER <?php if (have_posts ()) : while (have_posts ()) : the_post(); ?>

For an example:
<?php if (have_posts ()) : while (have_posts ()) : the_post(); ?>

You place your <?php echo_post_views(get_the_ID()); ?> here!


<?php endwhile; ?>


== Screenshots ==

1. screenshot-1.png

== Changelog ==

no changes.

== Arbitrary section ==

== Upgrade Notice ==

no upgrade notices.


