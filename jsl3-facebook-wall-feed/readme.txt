=== JSL3 Facebook Wall Feed ===
Contributors: Takanudo
Donate link: http://takanudo.com/jsl3-facebook-wall-feed
Tags: facebook, wall, profile, page, feed, timeline, post
Requires at least: 3.2.1
Tested up to: 3.8
Stable tag: 1.7.2

Displays your Facebook wall as a widget or through shortcode on a post or page.

== Description ==

Displays your Facebook wall as a widget or through shortcode on a post or page. Makes use of Fedil Grogan's [Facebook Wall Feed for WordPress](http://fedil.ukneeq.com/2011/06/23/facebook-wall-feed-for-wordpress-updated) code and changes suggested by [Daniel Westergren](http://danielwestergren.se) and [Neil Pie](http://www.neilpie.co.uk). German translation provided by Remo Fleckinger.

== Installation ==

1. Extract the zip file to the '/wp-content/plugins/' directory.

1. **Activate** the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Where can I find support for this plugin? =

You can find support on the
[JSL3 Facebook Wall Feed](http://takanudo.com/jsl3-facebook-wall-feed) page.
Just add a comment and I will do my best to help you.

= How do I use shortcode to add the Facebook Wall Feed to a post or page? =

Switch to Text view and add the following:

    [jsl3_fwf]

To limit the number of posts displayed add the 'limit' attribute:

    [jsl3_fwf limit="1"]

To specify a specific feed to display add the 'fb_id' attribute and enter the
feed's Facebook ID:

    [jsl3_fwf fb_id="1405307559"]

If you do not enter a Facebook ID in the shortcode, the plugin will use the
Facebook ID entered on the settings page for the plugin.

= Can I translate the plugin? =

I would be happy if you translated the plugin.  You can use the 'default.po'
file found in the 'wp-content/plugins/jsl3-facebook-wall-feed/languages'
directory.  Use [Poedit](http://www.poedit.net) to translate the plugin into
your language and then save the PO file using the text domain ('jsl3-fwf'),
language code and country code as the name.  For example, if you translate the
plugin into German, you should save the file as 'jsl3-fwf-de_DE.po'.  Finally,
place the translated PO file and its corresponding MO file in the
'wp-content/plugins/jsl3-facebook-wall-feed/languages' directory.

Let me know the URL of the site with the translated plugin by posting a comment
on the [JSL3 Facebook Wall Feed](http://takanudo.com/jsl3-facebook-wall-feed)
page.

= How do I get rid of the 'Facebook Status' box? =

To remove the 'Facebook Status' box add the following to the bottom of the
style sheet on the settings page for the plugin:

    /* Remove Facebook Status */
    #facebook_status_box h2
    {
        display: none;
    }

= How can I adjust the width of the Facebook Wall Feed? =

To adjust the width of the Facebook Wall Feed add the following to the bottom
of the style sheet on the settings page for the plugin:

    /* Adjust width */
    #facebook_status_box
    {
        width: 225px;
    }

Change the number in front of "px" to one that fits for you.

= How do I adjust the height of the Facebook Wall Feed? =

To adjust the height of the Facebook Wall Feed add the following to the bottom
of the style sheet on the settings page for the plugin:

    /* Adjust height */
    #facebook_status_box
    {
        height: 500px;
    }
    #facebook_status_box #facebook_canvas
    {
        height: 460px;
    }

Change the numbers in front of "px" to ones that fits for you. Try to keep the
height in #facebook_status_box about 40px greater than the height
in #facebook_canvas if you are keeping the 'Facebook Status' box at the top of the
feed.

= Why is there so much space at the bottom of my page where the plugin is located? =

To remove the space at the bottom of your page add the following to the bottom
of the style sheet on the settings page for the plugin:

    /* Remove extra space */
    #facebook_status_box #facebook_canvas .fb_post .fb_commLink .fb_likes .tooltip
    {
        position: static !important;
        padding: 0 0 0 18px !important;
        opacity: 1 !important;
        filter: alpha(opacity=1) !important;
    }

= Why do comments look so bad? =

To fix the formatting of the comments, add the following to the bottom of the
style sheet on the settings page for the plugin:

    /* Format comments */
    #facebook_status_box .fb_msg p.fb_story
    {
        font-size: 10px;
        color: #999999;
    }
    #facebook_status_box .fb_post .fb_comments
    {
        background-color: #EDEFF4;
        font-size: 11px;
        border-bottom: 1px solid #e6e6fa;
        overflow: hidden;
        padding: 7px;
        margin: 0;
    }
    #facebook_status_box .fb_post .fb_comments p
    {
        font-size: 11px;
        margin: 0;
        padding: 0;
        float: left;
    }
    #facebook_status_box .fb_post .fb_comments a
    {
        color: #0A7A98;
        text-decoration: none;
    }
    #facebook_status_box .fb_post .fp_photo_content
    {
        width: 85%
    }

= Why is the like icon missing from comments? =

To add the like icon to comments, add the following to the bottom of the style
sheet on the settings page for the plugin:

    #facebook_status_box .fb_post .fb_comments .fb_comment_likes
    {
        background-image: url("https://fbstatic-a.akamaihd.net/rsrc.php/v2/y-/r/tbhIfdAHjXE.png");
        background-repeat: no-repeat;
        background-position: 0 -60px;
        height: 14px;
        padding-left: 18px;
        margin-left: 5px;
    }

= Why is my token set to expire in less than 24 hours? =

I am not sure why Facebook will give some users a short-lived token. Facebook
will only allow you to attempt to renew your token once per 24 hours. Try
waiting 24 hours from the last time you clicked "Save Changes" on the settings
page for the plugin, then try again. If you do not get a token that lasts about
60 days, then you may want to try creating a new Facebook App for the plugin.

= What does the error “OAuthException: Error validating access token: Session has expired at unix time [UNIX TIME]. The current unix time is [UNIX TIME]” mean? =

It means your access token has expired. Go to the settings page for the plugin
and click "Save Changes" to renew your token.

= What does the error "OAuthException: Error validating access token: The session has been invalidated because the user has changed the password" mean? =

It usually means you changed your Facebook password recently. Go to the
settings page for the plugin and click "Save Changes" to validate your session.

= What does the error "OAuthException: An access token is required to request this resource" mean? =

It usually means you do not have an access token.  Check that your App ID and
App Secret are correct.  Then click "Save Changes" on the settings page for the
plugin.

= What does the error "Exception: No node specified" mean? =

It usually means you have not set your Facebook ID.  Check that you have
entered your Facebook ID on the settings page for the plugin. Then click "Save
Changes" on the settings page for the plugin.

= What does the error "Exception: SETTINGS: Unrecognized pref_type 0 for NullProfileSettings pref name default_non_connection_tab" mean? =

It usually means are using an incorrect Facebook ID.  Check that your Facebook
ID is correct.  Then click "Save Changes" on the settings page for the plugin.

= What does "An error occurred with [Your App Name]. Please try again later" mean? =

This is a Facebook error and may also include the following message:

> API Error Code: 191
> API Error Description: The specified URL is not owned by the application
> Error Message: Invalid redirect_uri: Given URL is not allowed by the Application configuration. 

This error means that the App Domain and Site URL for your Facebook App do not
match the domain of the website where you are using the plugin. Go to
[https://developers.facebook.com/apps](https://developers.facebook.com/apps)
and click "Edit Settings". Under "Basic Info", change your "App Domain" to
match the domain of the website where the plugin is located. In the "Select how
your app integrates with Facebook" section, under "Website with Facebook Login",
change your "Site URL" to match the URL of the website where the plugin is
located.  Do not use "www." in your App Domain or Site URL.

= Why is my feed blank? =

First, a blank feed usually indicates an invalid Facebook ID. If you do not know
your Facebook ID, then go to [https://developers.facebook.com/tools/explorer](https://developers.facebook.com/tools/explorer).
Click "Get Access Token". You may be prompted to log in. If you are prompted to
"Select permissions", click "Get Access Token". In the text box next to the
"Submit" button, enter the "Facebook Username" used in your Facebook URL (for
example, my Facebook URL is https://www.facebook.com/takanudo so my Facebook
Username is takanudo) followed by "?fields=id". Click Submit. Your Facebook ID
will be in the results.

Second, The limit property tells Facebook how many posts to return. Some of
those posts could be filtered out depending on how you have configured the
plugin. For example, if you set the limit to one, the post returned may be
filtered out if you have "Only show posts made by this Facebook ID" checked or
"Show all status messages" unchecked or "Privacy" set to "Show only wall posts
labeled public". The thoroughness option forces the plugin to keep making
requests to Facebook until the limit number has been reached, but it will slow
down the plugin dramatically.

== Screenshots ==

1. **Activate** the plugin through the 'Plugins' menu in WordPress.

2. Allow Developer to access your basic information.

3. Click **Create New App**.

4. Enter an **App Name**.  All the other entries are optional.

5. On your App page, enter your **App Domain**. Set **Sandbox Mode** to
   **Disabled**. Under **Select how your app integrates with Facebook** click
   **Website with Facebook Login** and enter your **Site URL**.  Do not use
   **www.** in your App Domain or Site URL.

6. Go to **JSL3 Facebook Wall Feed** under **Settings** on the Dashboard menu.

7. Enter your **Facebook ID**, **App ID**, and  **App Secret**.  Click
   **Save Changes**.

8. Click **Okay** to give your App permission to acess your public profile,
   friend list, News Feed, status updates and groups and your friends' groups.

9. Click **Okay** to give your App permission to manage your Pages.

10. You will be returned to the JSL3 Facebook Wall Feed settings page with your
    **Access Token** and its expiration date.

11. Drag the **JSL3 Facebook Wall Feed** widget to the sidebar of your choice.

12. Give the widget a title (or leave it blank) and enter how many posts you
    want to get from your wall.  You may also enter the Facebook ID of the feed
    you want to display.  If you leave the Facebook ID blank, the plugin will
    use the Facebook ID entered on the settings page for the plugin.

13. Go check out your Facebook Wall Feed on your WordPress site.

14. Add the shortcode **`[jsl3_fwf]`** or **`[jsl3_fwf limit="1"]`** or even
    **`[jsl3_fwf limit="1" fb_id="1405307559"]`** to the **Text** view of a
    post or page.  If you do not enter a Facebook ID, the plugin will use the
    Facebook ID entered on the settings page for the plugin.

15. View your Facebook Wall Feed on your WordPress post or page.

== Changelog ==

= 1.7.2 =
* Fixed a bug with how the access token renew check was scheduled.

= 1.7.1 =
* Added group permissions to the feed.

= 1.7 =
* Added the ability to display multiple feeds.
* Added a German translation to the plugin.

= 1.6 =
* This update displays the likes count for each post and comment.

= 1.5.5 =
* Updated the help section.

= 1.5.4 =
* This update should send fewer notification emails.
* Changes all URLs to use https.

= 1.5.3 =
* Fixed a minor bug introduced in v1.5.2

= 1.5.2 =
* Fixed privacy setting to work with the change Facebook made to how they
  display privacy settings in the feed.
* Minor change to how the style sheet is enqueued into the header.

= 1.5.1 =
* Fixed a bug in the shortcode introduced in v1.5

= 1.5 =
* Added an option to disable the make_clickable() WordPress function added in
  v1.4.2
* The plugin will now notify the WordPress admin that their Facebook access
  token is about to expire a week from the expiration date.

= 1.4.2 =
* Added make_clickable() WordPress function to convert plain text URI to HTML
  links.

= 1.4.1 =
* Added CRON schedule to refresh expired tokens because Facebook no longer
  allows non-expiring tokens.
* Fixed 1 pixel images filtered through Facebook's safe_image.php file.
* Added ability to turn off displaying Facebook icons.
* Added additional security features.

= 1.3.1 =
* Made the feed validate XHTML 1.0 Strict.
* Made a cURL and allow_url_fopen check.
* Feed will now use the same locale as WordPress.
* Added ability to turn of SSL certificate verification.
* Added ability to display profile picture from Facebook pages with
  demographic restrictions.

= 1.2 =
* Added default.po file to support localization.
* Added thoroughness check.
* Added ability to show status messages.
* Added ability to show post comments.
* Added ability to open links in a new window or tab.
* Feed will now display a greater variety of wall posts.
* Accounted for newline character.

= 1.1 =
* Fixed a PHP Notice error when displaying video posts.
* Added shortcode capability.
* Added a property to limit posts to only the user (posts by other users
  are not displayed).
* Added a privacy setting to limit the feed to only public posts.
* Added contextual help.
* Added better error handling.

= 1.0 =
* This is the initial version.

== Upgrade Notice ==

= 1.7.2 =
Fixed a timing bug where the access token renew check would be scheduled to
run before the expiration date was stored in the database.

= 1.7.1 =
A minor update to add group permissions to the feed.

= 1.7 =
The plugin now has the abiliy to display feeds from different Facebook pages.
Also, a German translation of the plugin has been provided.

= 1.6 =
Facebook removed the likes count from the feed.  This update uses a different
method to get the likes count for each post.  The plugin also displays the
likes count for each comment.

= 1.5.5 =
The help section has been updated.

= 1.5.4 =
Hopefully this update will send fewer email notifications when your token is
about to expire. Also, changed all URLs to use https.

= 1.5.3 =
Fixed a minor bug introduced in v1.5.2.

= 1.5.2 =
Facebook changed public privacy setting to be a blank entry, so I have
adjusted the plugin to account for that.  Also, for some users, the
style sheet would be embedded more than once.  This update should fix
that.

= 1.5.1 =
This is a minor shortcode bug fix.

= 1.5 =
This update adds an option to disable the make_clickable() WordPress function
added in v1.4.2.  Also, the automatic Facebook access token renewal added in
v1.4.1 never worked properly.  So now the plugin will now notify the WordPress
admin that their Facebook access token is about to expire a week from the
expiration date.  Renewing the token should simply be a matter of clicking
"Save Changes" on the settings page for the plugin.

= 1.4.2 =
This is a minor update that adds the make_clickable() WordPress function to
convert plain text URI to HTML links.

= 1.4.1 =
This update adds additional security features.  It also adds a CRON schedule
to refresh expired tokens because Facebook no longer allows non-expiring
tokens.

= 1.3.1 =
This update should validate under XHTML 1.0 Strict.  It also checks to see if
cURL is loaded or allow_url_fopen is on.  The feed will now use the same locale
setting that WordPress is using.

= 1.2 =
This upgrade provides support for localization.  Feel free to use the
'default.po' file in the 'languages' directory to create a translation of
the plugin.

= 1.1 =
This upgrade provides added security measures and better error handling.

= 1.0 =
This is the initial version.

== Configuration ==

1. [Create your Facebook App](https://developers.facebook.com/apps).  NOTE: You
   cannot use a Facebook Page to create a Facebook App. You must use your
   personal Facebook profile. However, once you create your Facebook App, you
   can use its App ID and App Secret along with the Facebook ID of the Facebook
   Page you want to get the feed from on the settings page for the plugin. 

1. If you get a Request for Permission prompt, then **Allow** Developer to
   access your basic information.

1. Click **Create New App**.

1. Enter any **App Name**. I suggest using the name of your blog.  All the other
   entries are optional. Click **Continue**. You will be prompted with a
   security check.

1. On your App page, enter your **App Domain**.  Set **Sandbox Mode** to
   **Disabled**. Under **Select how your app integrates with Facebook** click
   **Website with Facebook Login** and enter your **Site URL**.  Do not use
   **www.* in your App Domain or Site URL.  Save your changes.

1. Record your **App ID** and **App Secret**. You will need these later.

1. Go to **JSL3 Facebook Wall Feed** under **Settings** on the Dashboard menu.

1. Enter your **Facebook ID**.  If you do not know your Facebook ID, then use
   the [Graph API Explorer](https://developers.facebook.com/tools/explorer).
   Click **Get Access Token**. You may be prompted to log in. If you are
   prompted to Select permissions, click **Get Access Token**. In the text box
   next to the Submit button, enter the **Facebook Username** used in your
   Facebook URL (for example, my Facebook URL is
   http://www.facebook.com/takanudo so my Facebook Username is takanudo)
   followed by **?fields=id**. Click **Submit**. Your Facebook ID will be in the
   results.  Enter the **App ID** and **App Secret** you recorded earlier.
   Click **Save Changes**.

1. You will be redirected to Facebook. You may be prompted to **Log In** a
   couple of times.

1. Click **Okay** to give your App permission to acess your public profile,
   friend list, News Feed, status updates and groups and your friends' groups.

1. Click **Okay** to give your App permission to manage your Pages.

1. You will be returned to the JSL3 Facebook Wall Feed settings page with your
   **Access Token** and its expiration date.

== Widget Usage ==

1. Go to **Widgets** under **Appearance** on the Dashboard menu.

1. Drag the **JSL3 Facebook Wall Feed** widget to the sidebar of your choice.

1. Give the widget a title (or leave it blank) and enter how many posts you
   want to get from your wall. You may also enter the Facebook ID of the
   Facebook page you want to display in the widget.  If you leave the Facebook
   ID blank, the widget will use the Facebook ID entered on the settings page
   for the plugin.  Click **Save**.

1. Go check out your Facebook Wall Feed on your WordPress site.

== Shortcode Usage ==

1. Add the shortcode **`[jsl3_fwf]`** or **`[jsl3_fwf limit="1"]`** or even
   **`[jsl3_fwf limit="1" fb_id="1405307559"]`** to the **Text** view of a post
   or page.  If you do not enter a Facebook ID, the plugin will use the
   Facebook ID entered on the settings page for the plugin.

1. View your Facebook Wall Feed on your WordPress post or page.
