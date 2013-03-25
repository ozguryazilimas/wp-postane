=== Comment Images ===
Contributors: tommcfarlin
Donate link: http://tommcfarlin.com/donate
Tags: comments, image
Requires at least: 3.4.1
Tested up to: 3.5.1
Stable tag: 1.8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow your readers easily to attach an image to their comment.

== Description ==

Comment Images gives readers the ability to upload an image to their comment right from the comment form.

Comment Images...

* Will notify the administrator if the plugin is not compatible with their hosting environment
* Supports PNG, GIF, JPG, and JPEG images
* Will notify readers if their attached image is not allowed to be uploaded
* Styles images so that they will fit within the comment display and not "bleed over" into the page
* Provides dashboard functionality for seeing the images that are attached to each comment
* Makes the images available in the Media Uploader
* Is fully localized and ready for translation

For more information or to follow the project, check out the [project page](http://tommcfarlin.com/projects/comment-images/).

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' Plugin Dashboard
1. Select `comment-images.zip` from your computer
1. Upload
1. Activate the plugin on the WordPress Plugin Dashboard

= Using FTP =

1. Extract `comment-images.zip` to your computer
1. Upload the `comment-images` directory to your `wp-content/plugins` directory
1. Activate the plugin on the WordPress Plugins dashboard

== Frequently Asked Questions ==

= Is this plugin compatible with P2? =

Not yet, but it's on the roadmap.

= Is this plugin compatible with JetPack comments? =

Currently, no. Comment Images is only compatible with the standard WordPress comment form.

= What about IntenseDebate or Disqus? =

Same as above :)

== Screenshots ==

1. The default comment form in Twentyeleven with the upload form built into the comment form
2. How the comment will appear once the image has been uploaded
3. The error message displayed when an invalid file is selected
4. The administrator's dashboard notice when their hosting environment doesn't allow uploads
5. The updated Comments Dashboard showing the Comment Image for each comment
6. The 'Recent Comments' widget showing a link when a recent comment as a comment image
7. The 'Comments' dashboard for a given post displaying the comment and the comment image
8. The updated 'All Posts' view showing when a post's comments contain comment images

== Changelog ==

= 1.8.2 =
* Fixing a conflict with Comment Image Reloaded

= 1.8.1
* Fixing a small case of being able to toggle the comment field on and off.

= 1.8 =
* Made the comment images available in the Media Uploader
* Added an option in the 'Recent Comments' Dashboard widget to link to the images for the comment
* Added a column on the 'All Posts' view that display if there are comment images for the given post
* Added the notification on the 'All Posts' view to link to that posts comments
* Added a column to the 'Comments' page that displays the comment image associated with the given comment
* Disable comments on a per post basis from the post dashboard

= 1.7 =
* Adding support for international languages in the file types

= 1.6.2 =
* Removing the custom.css support as it was causing issues with other plugin upgrades. Will be restored later, if requested.

= 1.6.1 =
* Improving support for adding custom.css so that the file is also managed properly during the plugin update process
* Updating localization files

= 1.6 =
* Adding a support for a custom.css file
* Verifying WordPress 3.5 support
* Updating localization calls
* Adding styles for images to improve their display in the comment thread
* Updating calls to play nicely with newer version of PHP

= 1.5 =
* Updating styles for images in comments to max sure they fit properly within the comment container
* Adding a donate link

= 1.4 =
* Resolving a bug that prevents comment images that are uploaded back-to-back from properly displaying.

= 1.3 =
* Adding support for comment images on pages

= 1.2 =
* Changing the way comment images are associated with comments to improve performance for users who aren't logged in
* Resolving a reported issue with Firefox and IE

= 1.1 =
* Updating README to show the screenshots.

= 1.0 =
* Initial release

== Development Information ==

Comment Images was built using...

* [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards)
* Native WordPress API's (specifically the [Plugin API](http://codex.wordpress.org/Plugin_API))
* [CodeKit](http://incident57.com/codekit/) using [LESS](http://lesscss.org/), [JSLint](http://www.jslint.com/lint.html), and [jQuery](http://jquery.com/)
* Respect for WordPress bloggers everywhere :)