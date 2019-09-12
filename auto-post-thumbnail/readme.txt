=== Auto Post Thumbnail ===
Contributors: creativemotion
Tags: post thumbnail, post thumbnails, featured image, thumbnail, thumbnails
Requires at least: 4.2
Tested up to: 5.2
Requires PHP: 5.4
Stable tag: trunk
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically generate the Post Thumbnail (Featured Thumbnail) from the first image in post or any custom post type only if Post Thumbnail is not set manually. 

== Description ==

Bulk Featured Images generation.
Featured Images selective generation.
Manual Featured Images Selection.
Image search in Google, Unsplash, Pixabay.
Compatibility with Elementor and Gutenberg. 

In this tutorial you can get more information about new features.
https://youtu.be/rucqKNdVQGY

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->APT - disable unused features screen to configure the plugin

== Screenshots ==

1. New image generation tools
2. Bulk Featured Images generation
3. Featured Images selective generation
4. Manual Featured Images Selection
5. Image search in Google, Unsplash, Pixabay
6. Compatibility with Elementor and Gutenberg

== Changelog ==
= 3.5.0 (2019-09-11) =
* Fixed: Php warning "is_readable(): open_basedir restriction in effect". [more](https://wordpress.org/support/topic/today-after-the-update/)

= 3.5.0 =
* Fixed: Errors in the plugin on user requests
* New: Bulk Featured Images generation
* New: Featured Images selective generation
* New: Manual Featured Images Selection
* New: Image search in Google, Unsplash, Pixabay
* New: Compatibility with Elementor and Gutenberg.

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
