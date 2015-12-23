=== Auto Post Thumbnail ===
Contributors: tariquesani
Tags: Post, thumbnail, automatic, posts, featured image, image, featured, images, admin
Requires at least: 3.6.1
Tested up to: 4.4.x
Stable tag: 3.4.0

Automatically generate the Post Thumbnail (Featured Thumbnail) from the first image in post or any custom post type only if Post Thumbnail is not set manually.

== Description ==

Go PRO! A premium version of the plugin has been launched with many more features - [See for details](http://codecanyon.net/item/auto-post-thumbnail-pro/4322624?ref=sanisoft)

Auto post thumbnail is a plugin to generate post thumbnail from first image in post or any custom post type. If the first image doesn't work it will automatically search for the next one and so on until the post thumbnail is inserted.

If the post thumbnail is already present, the plugin will do nothing.
If you don't want a post thumbnail for some post with images, just add a custom field *skip_post_thumb* to the post and the plugin will restrain itself from generating post thumbnail.
The plugin also provides a Batch Processing capability to generate post thumbnails for already published posts. A new menu item **Gen. Post Thumbnails** will get added under Tools menu after this plugin is installed.

For more details, see http://www.sanisoft.com/blog/2010/04/19/wordpress-plugin-automatic-post-thumbnail/

== Installation ==

1. Upload directory 'auto-post-thumbnail' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Sorry, no more steps :)

== Changelog ==

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
