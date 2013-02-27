=== Sendloop Subscribe ===
Contributors: Sendloop.com <support@sendloop.com>, http://sendloop.com
Tags: sendloop, subscribe, newsletter
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.0.2

With this plug-in, blog owner will be able to "link" his Sendloop account with WordPress and start accepting email list subscriptions on his blog.

== Description ==

Sendloop is our email marketing platform. 

To use the Sendloop API, you will need your API key. With your API key, you will be able to open an API session and then access to protected API commands such as getting the list of your subscriber lists.

1. Login to your Sendloop account
1. Click "Settings" link on the top right corner
1. Click "API Settings" link on the right menu
1. Sendloop will show your API key on the screen

PS: You'll need an [sendloop.com API key](http://your_account.sendloop.com/settings/api/) to use it.

== Installation ==

Upload plugin folder to your blog (wp-content/plugins/), activate it, then enter your [sendloop.com credentials](http://your_account.sendloop.com/settings/api/).

== Changelog ==

= 1.0.2 =
* Display subscription errors on form

= 1.0 =
* Using Sendloop API 3

= 0.3.3 =
* Credentials are cleaned up for submission
* Fix in Dispatcher class to handle redirects properly when wrong account url is submitted
* Debug print in dispatcher class removed

= 0.3.2 =
* Credentials are removed from JS

= 0.3.1 =
* Fix for not pre-selected custom fields

= 0.3.0 =
* Customized success/error messages via admin panel
* Coloured messages on frontend
* Nice standard-look success/failure notes in settings panel
* Target subscribers list removed from settings panel. It only checks connection for given credentials before save
* Custom fields selection in the widget

= 0.2.0 =
* Subscribe/Unsubscribe functionality
* Better sender IP detection
* Custom fields, configurable via widget and visible on frontend to store additional data in sendloop.com account
* Separate target subscriber lists for individual widget instances

= 0.1.0 =

* First draft release