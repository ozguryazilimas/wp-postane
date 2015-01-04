=== Front End PM ===
Contributors: shamim51
Tags: front end pm,front-end-pm,pm,private message,personal message,front end,frontend pm,frontend,message,email,mail,contact form, secure contact form, simple contact form,akismet check,akismet
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=4HKBQ3QFSCPHJ&lc=US&item_name=Front%20End%20PM&item_number=Front%20End%20PM&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 2.8
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system from front end.

== Description ==
Front End PM is a Private Messaging system and a secure contact form to your WordPress site.This is full functioning messaging system from front end. The messaging is done entirely through the front-end of your site rather than the Dashboard. This is very helpful if you want to keep your users out of the Dashboard area.

* Works through a Page rather than the dashboard. This is very helpful if you want to keep your users out of the Dashboard area!
* Users can privately message one another
* Threaded messages
* BBCode in messages
* Ability to embed things into messages like YouTube, Photobucket, Flickr, Wordpress TV, more.
* Admins can create a page for "Front End PM" by one click (see Installation for more details).
* Admins can send a public announcement for all users to see
* Admins can set the max amount of messages a user can keep in his/her box. This is helpful for keeping Database sizes down.
* Admins can set how many messages to show per page in the message box.
* Admins can set how many user to show per page in front end directory.
* Admins can set will email be sent to all users when a new announcement is published or not.
* Admins can set "to" field of announcement email.
* Admins can set Directory will be shown to all or not.
* Admins can block any user to send private message.
* Admins can set time delay between two messages send by a user.
* Admins can see all other's private message.
* Admins can block all users to send new message but they can send reply of their messages.
* Admins can hide autosuggestion for users.
* There are two types of sidebar widget.(button widget and text widget).
* Users can select whether or not they want to receive messages
* Users can select whether or not they want to be notified by email when they receive a new message.
* Users can select whether or not they want to be notified by email when a new announcement is published.

**FEP Contact Form**

* Added from version 2.0 a secure contact form.
* Can select department and to whom message will be send for that department.
* Manual and AKISMET check of contact message.
* Reply directly to Email address from front end.
* IP, Email blacklist, Whitelist.
* Time delay between two messages send by same user/visitor.

**Translation**

* German translation thanks to palatino.
* Simplified Chinese thanks to Changmeng Hu.


To know more visit [Front End PM](http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/)

== Installation ==
1. Upload "front-end-pm" to the "/wp-content/plugins/" directory.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Create a new page.
1. Paste code `[front-end-pm]` for Front End pm and `[fep-contact-form]` for FEP Contact Form under the HTML tab of the page editor.
1. Publish the page.

Or you can create page for Front End PM by one click. Go to **Front End PM>Instruction** give a Title(required) for Front End PM page and Slug(optional) then click "Create Page". It will automatically create a page for your Message. If you keep Slug field blank, slug of page will be automatically created based on your given title.

For more instruction please visit [Front End PM](http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/)

== Frequently Asked Questions ==
= Can i use this plugin to my language? =
Yes. this plugin is translate ready. But If your language is not available you can make one. If you want to help us to translate this plugin to your language you are welcome.

= Where to report bug if found? =
please visit [Front End PM](http://www.banglardokan.com/blog/recent/project/front-end-pm-2215/) and report.

== Screenshots ==
1. Admin settings page.
2. Front End PM setup instruction.
3. Front End pm.
4. Button widgets.
5. Text widgets.
6. Front End Directory.
7. FEP Contact Form Settings
8. FEP Contact Form Settings 2
9. FEP Contact Form

== Changelog ==

= 2.2 =

* New option to send attachment in both pm and contact form.
* Attachment in stored in front-end-pm folder inside upload folder and contact form attachment is stored inside front-end-pm/contact-form folder.
* Message count in header bug fixes.
* Security bug fixes where non-admin user could see all messages.

= 2.1 =

* IP blacklist now support range and wildcard.
* Email address blacklist,whitelist.
* Time delay for logged out visitors also.
* Double name when auto suggestion off fixes.
* Department name bug fixes.
* Other some small bug fixes.

= 2.0 =

* Added a secure contact form.
* Manual check of contact message.
* AKISMET check of contact message.
* Can configure CAPTCHA for contact message form.
* Separate settings page for contact message.
* Can select department and to whom message will be send for that department.
* Can set separate time delay to send message of a user via contact message.
* Reply directly to Email address from front end.
* Send Email to any email addresss from front end.
* Use wordpress nonce instead of cookie.
* All forms nonce check before process.
* Added capability check to use messaging.
* Capability and nonce check before any action.
* Security Update.
* Some css fix.
* POT file updated.

= 1.3 =

* Parent ID and time check server side.
* Escape properly before input into database.
* Some css fix.
* Email template change.
* Recommended to update because some core functions have been changed and from this version (1.3) those functions will be used.

= 1.2 =

* Using display name instead of user_login to send message (partially).
* Send email to all users when a new announcement is published (there are options to control).
* Now admins can set time delay between two messages send by a user.
* Bug fixes in bbcode and code in content when send message.
* Security fixes in autosuggestion.
* New options are added in admin settings.
* No more sending email to sender.
* Javascript fixes.

= 1.1 =

* Initial release.

== Upgrade Notice ==

= 2.2 =

* New option to send attachment in both pm and contact form.
* Attachment is stored in front-end-pm folder inside upload folder and contact form attachment is stored inside front-end-pm/contact-form folder.
* Message count in header bug fixes.
* Security bug fixes where non-admin user could see all messages.

= 2.1 =

* IP blacklist now support range and wildcard.
* Email address blacklist,whitelist.
* Time delay for logged out visitors also.
* Double name when auto suggestion off fixes.
* Department name bug fixes.
* Other some small bug fixes.

= 2.0 =

* Added a secure contact form.
* Manual check of contact message.
* AKISMET check of contact message.
* Can configure CAPTCHA for contact message form.
* Separate settings page for contact message.
* Can select department and to whom message will be send for that department.
* Can set separate time delay to send message of a user via contact message.
* Reply directly to Email address from front end.
* Send Email to any email addresss from front end.
* Use wordpress nonce instead of cookie.
* All forms nonce check before process.
* Added capability check to use messaging.
* Capability and nonce check before any action.
* Security Update.
* Some css fix.
* POT file updated.

= 1.3 =

* Parent ID and time check server side.
* Escape properly before input into database.
* Some css fix.
* Email template change.
* Recommended to update this plugin because some core functions (this plugin) have been changed and from this version (1.3) those functions will be used.

= 1.2 =

* Using display name instead of user_login to send message (partially).
* Send email to all users when a new announcement is published (there are options to control).
* Now admins can set time delay between two messages send by a user.
* Bug fixes in bbcode and code in content when send message.
* Security fixes in autosuggestion.
* New options are added in admin settings.
* No more sending email to sender.
* Javascript fixes.

= 1.1 =

* Initial release.