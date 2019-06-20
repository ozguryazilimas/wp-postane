=== Auto Post Thumbnail ===
Contributors: creativemotion
Tags: Post, thumbnail, automatic, posts, featured image, image, featured, images, admin
Requires at least: 4.2
Tested up to: 5.2.2
Stable tag: 3.4.2

Automatically generate the Post Thumbnail (Featured Thumbnail) from the first image in post or any custom post type only if Post Thumbnail is not set manually.

== Description ==

Auto Post Thumbnails didn’t please you with updated lately. However, great news today! We are about to tell you about all the spectacular changes that are planned for our plugin! 

First of all, we proudly announce that a new group of developers, Creative Motion, are helping us with plugin improvement.
Auto Post Thumbnails has perfectly fit in our close family of popular plugins with more than 600,000 users worldwide.
What you can expect soon:

3.4.2
As you’ve already noticed, we haven’t updated the plugin for more than 2 years. This new version fixes existing problems. APT becomes a fully functional plugin.

3.5.0
In the next release, you can automatically generate featured images from any image in the post, not only the first one.
Besides, we offer you an advanced tool – choose an image for the featured image right from the Posts tab. You no longer need to edit each post to install or change the featured image. Feel free to do it right from the list of posts. It saves much time and efforts.

3.5.1
Starting from this version, the APT plugin evolves from being an aiding tool to the full-featured search & image editing system with a Creative Commons license for your website.
It means that you get:
Image search through the 5 popular stock services from the plugin interface. Just enter a search query and choose an image(images) you like.
Advanced APT editor. You can edit images using layers. It means that you can overlay text, logo, or mask, adjust color, brightness, and contract and use other great features. Save presets and apply them on any image in one click.
The editor doesn’t replace the default WordPress editor.

3.5.2
Upload images from the external URL to your post or product (for Woocommerce).
Compatibility with the most popular builders.



== Installation ==

1. Upload directory 'auto-post-thumbnail' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Sorry, no more steps :)

== Changelog ==

= 3.4.1 =
* Fix for unchecked extension of uploaded files

= 3.4.0 =
* Tested with the latest wordpress release.

= 3.3.3 =
* Fix for SQL error begin caused due to no ID

= 3.3.2 =
* Tested with WordPress-3.6.x
* Small tweaks

= 3.3.1 =
* Tested with WordPress-3.5.1

= 3.3.0 =
* Added fix for featured images behaving differently in Wordpress version 3.4. NOTE: This version will fix only images in future posts. For fixing images of past posts see http://www.clickthrough-marketing.com/how-to-fix-auto-post-thumbnail-on-wordpress-3.4-seo-friendly-800610805/

= 3.2.3 =
* Added fix for jquery progress bar error causing due to Wordpress version 3.1

= 3.2.2 =
* Added back publish_post action so that regular posts work without any issues.
* Added code to check whether the image exists in database before trying to fetch it.

= 3.2.1 =
* Added code to correctly link the featured/post thumbnail with the post so that the Media Library shows the association correctly.
* Assigning **title** to the generated featured/post thumbnail by extracting it from the title of processed image.

= 3.2 =
Added support for creating featured thumbnails for custom post types as well. Batch processing will also generate thumbnails for any type of post.

= 3.1 =
Renamed **Gen. Post Thumbnails** to **Auto Post Thumbnail** and moved it under Settings menu.

= 3.0 =
* Added Batch Processing capability to generate post thumbnails for already published posts.
* A new menu item **Gen. Post Thumbnails** is added under Tools menu.

= 2.0 =
Added functionality to generate Post Thumbnail for scheduled posts. Thumbnail will be generated when scheduled post gets published.

= 1.1 =
Added a wrapper function using cURL for file_get_contents in case 'allow_url_fopen' ini setting is off.

= 1.0 =
First release
