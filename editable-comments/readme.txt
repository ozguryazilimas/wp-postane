=== Editable Comments ===
Contributors: julienappert
Tags:edit, comment, comments, edit comment, edit comments
Requires at least: 2.7
Tested up to:3.5.1
Stable tag:0.3.3

Allows users to edit or delete their own comment.

== Description ==

Allows users to edit or delete their own comment.

Add the following codes in the comments.php file of your template, in the loop of the comments list (for example after comment_text() ) :

&lt;?php if ( class_exists( 'WPEditableComments' ) ) { WPEditableComments::edit('Edit'); } ?&gt;

&lt;?php if ( class_exists( 'WPEditableComments' ) ) { WPEditableComments::delete('Delete'); } ?&gt;

The link will appear if :

* logged user can edit current post,
* user is the commenter (i.e. same IP) and time before edit or delete expiration has not passed. 

Languages :

* English (en_US)
* Français (fr_FR)
* Deutch (de_DE) : [Marc](http://gregel.com)
* Italiano (it_IT) : Aldo Latino
* Czech (cs_CZ) : [Ajvngou](http://www.ajvngou.cz)
* Spanish (es_ES) : [Eduardo Larequi](http://www.labitacoradeltigre.com/)

== Installation ==

1. Upload editable-comments directory to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Copy/paste the required code
4. That's it !

== Screenshots ==

1. editable comments backend
2. editable comments in action

== Changelog ==

= 0.3.3 =
* using WordPress' jQuery dialog file

= 0.3.2 =
* czech et spanish languages

= 0.3.1 =
* bugfix with comment date

= 0.3 =
* adding of delete capacity
* adding of the required codes in the administration page
* bugfix with default permalink

= 0.2.3 =
* bugfix with localization
* german and italian languages

= 0.2.2 =
* bugfix (opera browser)

= 0.2.1 =
* bugfix (backend)

= 0.2 =
* bugfix (msie7.0)
* localization

= 0.1 =
* first release