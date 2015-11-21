=== Plugin Name ===
Contributors: verysimple
Donate link: http://verysimple.com/products/imsanity/
Tags: imsanity, image, images, automatic scale, automatic resize, image resizer, image scaler, automatic image resizer, auto image resize, auto image resizer, space saver, image shrinker, image skruncher, image cruncher
Requires at least: 2.9
Tested up to: 4.4
Stable tag: trunk

Imsanity automatically resizes huge image uploads.  Are contributors uploading
huge photos?  Tired of manually scaling?  Imsanity to the rescue!

== Description ==

Imsanity automatically resizes huge image uploads down to a size that is
more reasonable for display in browser, yet still more than large enough for typical website use.
The plugin is configurable with a max width, height and quality.  When a contributor uploads an
image that is larger than the configured size, Imsanity will automatically scale it down to the
configured size and replace the original image.

Imsanity also provides a bulk-resize feature to selectively resize previously uploaded images
to free up disk space.

This plugin is ideal for blogs that do not require hi-resolution original images
to be stored and/or the contributors don't want (or understand how) to scale images
before uploading.

= Features =

* Automatically scales large image uploads to a more "sane" size
* Bulk-resize feature to selectively resize existing images
* Allows configuration of max width/height and jpg quality
* Optionally converts BMP files to JPG so image can be scaled
* Once enabled, Imsanity requires no actions on the part of the user
* Uses WordPress built-in image scaling functions

== Installation ==

Automatic Installation:

1. Go to Admin - Plugins - Add New and search for "imsanity"
2. Click the Install Button
3. Click 'Activate'

Manual Installation:

1. Download imsanity.zip
2. Unzip and upload the 'imsanity' folder to your '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress

Optional:

If you prefer not to see the Imsanity girl logo image on the settings page, add the following line to wp-config.php:

define('IMSANITY_HIDE_LOGO',true);

== Screenshots ==

1. Imsanity girl will cut you
2. Imsanity settings page to configure max height/width
3. Imsanity bulk image resize feature

== Frequently Asked Questions ==

= What is Imsanity? =

Imsanity is a plugin that automatically resizes uploaded images that are larger than the configured max width/height

= 1. Will installing the Imsanity plugin alter existing images in my blog? =

Activating Imsanity will not alter any existing images.  Imsanity resizes images as they are uploaded so
it does not affect existing images unless you specifically use the "Bulk Image Resize" feature on
the Imsanity settings page.  The "Bulk Image Resize" feature allows you to selectively resize existing images.

= 2. Why aren't all of my images detected when I try to use the bulk resize feature? =

Imsanity doesn't search your file system to find large files, instead it looks at the "metadata"
in the WordPress media library database.  When you upload files, WordPress stores all of the information 
about the image.

= 3. Why am I getting an error saying that my "File is not an image" ? =

WordPress uses the GD library to handle the image manipulation.  GD can be installed and configured to support
various types of images.  If GD is not configured to handle a particular image type then you will get
this message when you try to upload it.  For more info see http://php.net/manual/en/image.installation.php

= 4. How can I tell Imsanity to ingore a certain image so I can upload it without being resized? =

You can re-name your file and add "-noresize" to the filename.  For example if your file is named
"photo.jpg" you can rename it "photo-noresize.jpg" and Imsanity will ignore it, allowing you
to upload the full-sized image.

Optionally you can temporarily adjust the max image size settings and set them to a number that is
higher than the resolution of the image you wish to upload

= 5. Why would I need this plugin? =

Photos taken on any modern camera and even most cellphones are too large for display full-size in a browser.
In the case of modern DSLR cameras, the image sizes are intended for high-quality printing and are ridiculously
over-sized for display on a web page.

Imsanity allows you to set a sanity limit so that all uploaded images will be constrained
to a reasonable size which is still more than large enough for the needs of a typical website.
Imsanity hooks into WordPress immediately after the image upload, but before WordPress processing
occurs.  So WordPress behaves exactly the same in all ways, except it will be as if the contributor
had scaled their image to a reasonable size before uploading.

The size limit that imsanity uses is configurable.  The default value is large enough to fill
the average vistors entire screen without scaling so it is still more than large enough for
typical usage.

= 6. Why would I NOT want to use this plugin? =

You might not want to use Imsanity if you use WordPress as a stock art download
site, provide high-res images for print or use WordPress as a high-res photo
storage archive.  If you are doing any of these things then most likely
you already have a good understanding of image resolution.

= 7. Doesn't WordPress already automatically scale images? =

When an image is uploaded WordPress keeps the original and, depending on the size of the original,
will create up to 3 smaller sized copies of the file (Large, Medium, Thumbnail) which are intended
for embedding on your pages.  Unless you have special photographic needs, the original usually sits
there unused, but taking up disk quota.

= 8. Why did you spell Insanity wrong and why does Imsanity girl want to cut me? =

Imsanity is short for "Image Sanity Limit"  A sanity limit is a term for limiting something down to
a size or value that is reasonable.  Imsanity girl cuts because you drive her insane by uploading
unecessarily large images for no good reason.

= 9. How can I hide or remove the Imsanity logo image on the settings page? =

If you prefer not to see the logo, add the following line to wp-config.php:

define('IMSANITY_HIDE_LOGO',true);

= 10. Where do I go for support? =

Documentation is available on the plugin homepage at http://wordpress.org/tags/imsanity and questions may
be posted on the support forum at http://wordpress.org/tags/imsanity

= TODO =

* Add a network settings to override the individual plugin settings text

== Upgrade Notice ==

= 2.3.6	 =
* tested up to WP 4.4
* if resized image is not smaller than original, then keep original
* allow IMSANITY_AJAX_MAX_RECORDS to be overridden in wp-config.php
* if png-to-jpg is enabled, replace png transparency with white

== Changelog ==

= 2.3.6	 =
* tested up to WP 4.4
* if resized image is not smaller than original, then keep original
* allow IMSANITY_AJAX_MAX_RECORDS to be overridden in wp-config.php
* if png-to-jpg is enabled, replace png transparency with white

= 2.3.5	 =
* Add option to hide Imsanity girl logo image on settings screen

= 2.3.4	 =
* Security update to network settings page

= 2.3.3	 =
* Update default size from 1024 to 2048
* Tested up to WordPress 4.1.1
* Move screenshots to /assets folder
* Added 256x256 icon

= 2.3.2	 =
* Add PNG-To-JPG Option thanks to Jody Nesbitt

= 2.3.1	 =
* ignore errors if EXIF data is not readable
* show counter when bulk resizing images

= 2.3.0 =
* fix for incorrectly identifying media uploads as coming from 'other' on WP 4+

= 2.2.9 =
* add "noresize" to filename will bypass imsanity
* fix issue trying to auto-resize non-jpg images
* add danish language support

= 2.2.8 =
* hotfix for bux exif_read_data constant

= 2.2.7 =
* Automatically rotate images according to EXIF rotation data if available
* Reset the quota cache after bulk resizing images

= 2.2.6 =
* fixed bug in network settings where width/height input fields are in the same place

= 2.2.5 =
* fixed bug with bulk upload deleting the "large" image size in certain situations
* style settings page input boxes a little wider

= 2.2.4 =
* load js properly to avoid warnings with certain security plugins
* update settings text to be more clear about max w/h
* updated language translation .pot file
* updated FAQ

= 2.2.3 =
* improved error reporting in bulk resize

= 2.2.2 =
* replaced image_resize() call for wordpress prior to 3.5

= 2.2.1 =
* removed deprecated call to image_resize() in bulk resize

= 2.2.0 =
* removed deprecated call to image_resize() on < wordpress > 3.5

= 2.1.7 =
* fixed call to is_multisite() on < wordpress < 3.0

= 2.1.6 =
* internationalization support & French translation thanks to https://twitter.com/ChrysMTP

= 2.1.5 =
* fixed undefined error in setup.php

= 2.1.4 =
* fixed crash when uploading images via Android app via RPC (thanks Pieter!)

= 2.1.3 =
* fixed issue where only first 250 images could be re-sized in bulk-resize feature

= 2.1.2 =
* really fixed bug w/ multisite network settings only working on main site

= 2.1.1 =
* fixed bug w/ multisite network settings only working on main site

= 2.1.0 =
* max height/width can be configured separately for different upload types

= 2.0.0 =
* added network configuration for multi-site installation to enforce server-wide settings
* added "bulk resize" feature to resize existing images and recover disk space

= 1.1.0 =
* added ms-config.php file that can be used for global settings configuration

= 1.0.2 =
* added feature & setting to convert bmp images to jpg
* added setting to control JPG quality

= 1.0.1 =
* added error handling & reporting when image_resize returns an error

= 1.0.0 =
* original release.  fresh!