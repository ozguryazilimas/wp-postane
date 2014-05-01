=== Basic Comment Quicktags ===
Contributors: Ipstenu, MarcDK
Tags: comments, wysiwyg, quicktags, bbpress
Requires at least: 3.8
Tested up to: 3.9
Stable tag: 3.2.3
Donate Link: https://store.halfelf.org/donate/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays bold, italic, add link and quote buttons on top of the comment form.

== Description ==

This plugin displays the most basic of quicktag buttons on your comment forms, using the Quicktag API built into WordPress (as of 3.3).

You can turn these on and off for bbPress or comments on the discussions page (see the screenshots). Please note, the plugin defaults to <em>off</em>, so you have to go turn them on. Auto-activating isn't nice, as it may conflict with other plugins.

**Misc**

* [Plugin Site](http://halfelf.org/plugins/basic-comment-quicktags/)
* [Basic Support](http://wordpress.org/support/plugin/basic-comment-quicktags)
* [Donate](https://store.halfelf.org/donate/)

== Frequently Asked Questions ==

= Why did you do this? =

MarcTV did it because he needed a simple plugin to do this job and decided to do it on his own. I kind of love him for that.

I forked it so I could extend it to bbPress, put in more checks and ifs/thens, and all the other toolbars were too heavy.

= Why only those tags? =

After careful consideration, and a long review of my users, those are the only ones they ever use! If you have a reasonable argument why I should add in others, I'll listen. Thus far, the only suggestion interesting was 'code' and I determined people won't use that unless they know about it, and thus are capable to entering it manually.

= Will you add in options to pick and chose our tags? =

No. I don't have a need for it, and I don't want to include code I'm not going to personally make use of. It makes it much harder on me to support it later. (Read <a href="http://www.sohar.com/proj_pub/download/COMPAS93.pdf">Herbert Hecht's article "Rare Conditions – An Important Cause of Failures"</a> to understand my views on including rarely used code. tl;dr: I try not to.)

= Can I use this on older versions of WordPress? =

No. This plugin uses the <a href="http://codex.wordpress.org/Quicktags_API">Quicktags API</a> built in to WordPress 3.3. I only support the current version of WP and one back, so keep that in mind.

= Does this work on Multisite? =

Yes. It can be network activated or per-site, works fine.

= What version of bbPress does this work on? =

It's been tested up to 2.5.1 as of December 7, 2013.

= This isn't showing up on bbPress! =

If you're using the bbPress Fancy Editor, it won't work. Really, the fancy editor is 'more' than this, so you shouldn't be using both.

= Will you expand this to BuddyPress? =

Not at this time.

= My users say they don't see anything on IE8 =

IE8 cheerfully ignores the rules of jQuery. 

Sometimes it works, sometimes it doesn't. I got it to work, and then came back an hour later to no changes and it broke. At which point I bashed my head into the wall and went to the gym. Between caching and IE8 being inconsistant, I gave up. If anyone can fix it for everyone better than Trepmal did, you officially win. This is as good as I can get it. It works like a hero on Firefox, Safari, Chrome and IE9+, so I strongly suggest for consistant Internet behavior, upgrade IE.

== Changelog ==

= 3.2.3 =
* 2014 Apr 11, by Ipstenu
* Omitted a line in the last updated like a doofus (<a href="https://wordpress.org/support/topic/php-error-77">props KTS915</a>)

= 3.2.2 =
* 2014 Apr 7, by Ipstenu
* Switch order of comments open check to correct non-reproducable error (it doesn't break anything on my end)
* Fixed actual default to on for comments *sigh*

= 3.2.1 =
* 2013 Dec 9, by Ipstenu
* Force prevent any code from running on login pages.

= 3.2 =
* 2013 Dec 7, by Ipstenu
* Tweak to bbPress call because JJJ hates me (not really)

= 3.1 =
* 2013 May 14, by Ipstenu
* Left out js dependancy causing buttons not to show.

= 3.0 =
* 2013 May 14, by Ipstenu
* Better options handling
* Internationalization
* Classification (singletons, basically better code)
* Better option validation and output

= 2.2 =
* 2013 May 5, by Ipstenu
* I thought I had a check for if comments are open or not. I didn't. I suck.

= 2.1 =
* 2013 Feb 27, by Ipstenu
* There is a difference between is_single and is_singular.

= 2.0 =
* 2013 Feb 18, by Ipstenu
* More judicious script calling

= 1.7 = 
* 2012 June 29, by Ipstenu (and aahan)
* Dropping the call for editor.css and including just what we need. <a href="http://wordpress.org/support/topic/plugin-basic-comment-quicktags-why-use-editorcss">Read this thread for why</a>.

= 1.6 = 
* 2012 June 28, by Ipstenu (and sleary)
* Booyah! P2 in the hizzouse.

= 1.5 =
* 2012 June 18, by Ipstenu
* Curse you, JJJ! (bbPress 2.1 includes its own quicky taggies)

= 1.4 = 
* 2012 June 02, by Ipstenu
* Reported issue with bbPress. Verified.

= 1.3 = 
* 2012 May 07, by Ipstenu
* Crap, typo'd. Really sorry :(

= 1.2 =
* 2012 May 07, by Ipstenu
* bbPress is strange, should work now.

= 1.1 = 
* 2012 May 02, by Ipstenu (and trepmal)
* Added options to turn on and off for both comments <em>and</em> bbPress (per request of leeuniverse) - You will <em>only</em> see the bbPress box if you have bbPress installed (so as not to confuse people).
* Fixed IE being an asshat (props trepmal).

= 1.0 =
* 2012 April 27, by Ipstenu
* Initial fork of MarcTV Comment QuickTag
* Added in support for bbPress
* Added in checks to only load JS/CSS when on a page where one might leave comments
* Added failure if not WordPress 3.3 or greater

== Upgrade Notice ==

== Installation ==

1. Install and activate the plugin.
2. Visit your <em>Discussion Settings</em> page.
3. Look for 'Quicktags' and check boxes as desired. (Comments are activated by default)

== Screenshots ==

1. The Quicktags
2. The Options
