=== Permalink Editor ===
Contributors: Fubra, 36flavours
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=99WUGVV4HY5ZE&lc=GB&item_name=CatN%20Plugin%20Donation&item_number=catn-plugin-permalink-editor&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: permalink, url, link, post, page, custom, redirect, edit, structure, category, tag, author
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.2.12

Fully customise the permalink for an individual page or post and globally set the permalink structure for pages, categories, tags or authors.

== Description ==

This plugin adds two areas of functionality: Global page, category or tag permalink structures and individual custom permalinks.

Options are added to the Permalinks Settings page allowing you to specify the structure for pages, categories, tags and authors.

By default - if custom permalinks are enabled - pages are accessible in the format `/page/` or `/parent/page/`.

You can modify this format in many different ways, for example:

* Add an extension: `/%pagename%.html`
* Add a parent directory name: `/content/%pagename%/`
* Prefix the page name: `/page-%pagename%/`
* Or using a combination of the above.

This same format applies for categories, tags and authors, however the structure tokens differ:

* Categories: `%category%` (E.g. `/category/%category%.html`)
* Tags: `%post_tag%` (E.g. `/tag/%post_tag%.html`)
* Authors: `%author%` (E.g. `/author/%author%.html`)

Each *permalink base* can be edited directly via these settings, for example using `/people/%author%.html` as the Author permalink structure will replace `/author/` with `/people/`.

If no prefix is found, permalinks will be prepended with a default (category, tag or author) - with the exception of pages.

**Note:** Ensure you have included the correct structure tag somewhere in the url.

Additionally, an option is added to the edit screen allow you to specify the permalink for an individual post or page.

== Installation ==

1. Unzip the package, and upload `page-permalink` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to the permalink settings page *(Settings > Permalinks)* and set your custom global page permalink structure.
1. Individual post permalinks can be edited via the edit post screen.

== Frequently Asked Questions ==

= Why is the customise button now showing? =

If you have not enabled custom permalinks *(Settings > Permalinks)* and they are set to the default option,
the plugin will not recognise that custom permalink structures are enabled.

= What is a permalink alias? =

A permalink alias is an additional permalink value that can be set to redirect to the actual permalink.

If a user enters the URL of an existing alias value, it will header redirect *(301)* them to the correct location.

= How can I remove a custom permalink? =

* Click the `Customise` permalink button on the admin edit screen.
* Empty the input containing the permalink.
* Click on `OK` and update the entry to apply the changes.

The default permalink structure will then be applied.

= Why do numbers keep appearing at the end of my permalink? =

Permalinks should by unique across your site, if you are trying to define a duplicate a numeric value will be appended to the end.

For example, if there is an existing custom slug of "/post.html", it will be turned into "/post.html2".

= What features are there still left to implement? =

* Complete removal of the Category or Tag base.
* Option to remove parent categories from the category permalink, e.g. "/parent/child/" becomes just "/child/".
* Ability to customise the archive pages, e.g. "/2011-02.html".
* Option to edit the author name in author permalinks.
* Ability to disable individual / custom page permalinks to speed up sites using custom structures only.

= What is the order of priority used for redirects? =

1. Find an existing page by the specified path, if one exists then redirect to that page.
1. Check for a custom permalink if the current request returns a 404 error. *(Defined on the individual edit page)*
1. Lookup an alias permalink if no existing page is found. *(Defined on the individual edit page)*
1. Use the global permalink structures. *(Defined on the permalink settings page)*

== Screenshots ==

1. The customise button is added to the permalink edit area.
1. Customise button allows you to edit the whole permalink.
1. The permalink alias box appears towards the bottom of the edit screen.
1. Define the permalink structures on Settings > Permalinks options page.

== Upgrade Notice ==

= 0.2.9 =

Fix for bug introduced in 0.2.8 where rewrite rules were not always correctly flushed. 
They are are now regenerated when you visit the "Permalinks > Settings" page and click "Save Changes".

= 0.2.8 =

Rewrite rules are now only flushed when saving your permalink settings. If you are experiencing
a lot of 404 errors, please visit the *Settings > Permalinks* page and click "Save Changes".

= 0.2 =

Due to the addition of category and tag permalink customisation, in order to keep the plugin footprint to a minimum
the method of storing settings has changed.

This means that anybody using the global page structure settings in any plugin version < 0.2, will need
to visit the Permalink Settings page and re-enter the page structure.

Apologies for making this incompatible change.

== Changelog ==

= 0.2.12 =
* Bug fixes for issues introduced in the previous release (0.2.11).
* *Updated 2011-10-14*
= 0.2.11 =
* Modified generate_rewrite_rules() in an attempt to solve the 404 issues.
* Changed permalink lookup order in `get_post_by_custom_permalink` function.
* Added additional support for using utf-8 characters in individual custom permalinks.
= 0.2.10 =
* Added closing PHP tag to the plugin index file.
* Modules only loaded if rewrite rules are enabled.
= 0.2.9 =
* Rewrite Rules are are now regenerated when you visit the "Permalinks > Settings" page and click "Save Changes", or when saving a post or page.
= 0.2.8 =
* Speed improvements introduced by only flushing rewrites rules when the permalink settings are saved.
* Bug fix for causing some 404 errors when setting custom permalinks, including th use UTF8 characters.
* Minor bug fix for notice when parsing the request url.
* *Updated 2011-07-29*
= 0.2.7 =
* Updated the reformatting function to convert all accent characters to ASCII characters.
* Applied fix to prevent 404s for custom permalinks when WordPress is within sub directory.
* Added some backwards compatibility for versions of PHP < 5.1.2.
* *Updated 2011-04-12*
= 0.2.6 =
* Fixed issue generating permalinks when the WordPress install resides within a sub directory.
* *Updated 2011-03-28*
= 0.2.5 =
* Category, tag and author permalink structures are now required to have a prefix (a default will be prepended).
* Category and tag permalink structures now inherit their specified base value.
* *Updated 2011-03-02*
= 0.2.4 =
* Added more robust checking for an existing page / post by a given path (includung custom post types).
* Lowered the init priority to 11 in order to try and catch post types added on or before the default priority (1-10).
* *Updated 2011-02-23*
= 0.2.3 =
* Fix for adding custom permalinks to custom post types. (Adjusted lookup query to use an array of public post types instead of 'any'.)
* *Updated 2011-02-22*
= 0.2.2 =
* Fixes issue where links were internal links were redirecting to the homepage when the front page was set to a static page.
* Added option to customise the author permalinks.
* *Updated 2011-02-21*
= 0.2.1 =
* Fixes to allow custom permalinks to work when using "Almost Pretty" permalinks.
* Added two new filters `permalink_editor_page_link` and `permalink_editor_request` allowing other plugins to manipulate the permalinks.
* Added ability to include extra modules, allowing this plugin to work alongside others that manipulate permalinks.
* Added donate link.
* *Updated 2011-02-21*
= 0.2 =
* Added the ability to customise category and tag permastructs.
* Adjusted addition of filters so they are not applied if custom permalinks are disabled.
* Changed user capability check to 'manage_options'.
* *Updated 2011-02-15.*
= 0.1.9 =
* Fixed error output when trying to access a page using a permalink alias.
* Addition on the version update dates in the change log.
* *Updated 2011-02-14.*
= 0.1.8 =
* Changed the method used for checking file extensions.
* Fixed Edit Permalink button when creating a new post / page.
* Removed formatting of permalinks if rewriting is not enabled / default permalinks used.
* *Updated 2011-02-07.*
= 0.1.7 =
* Modified the method of including the JavaScript file, affected the customise button in versions prior to WP 3.1.
* *Updated 2011-02-05.*
= 0.1.6 =
* Enabled permalinks on drafts to be edited and customised.
* *Updated 2011-02-04.*
= 0.1.5 =
* Removed unexpected output and modified contributers list.
* *Updated 2011-02-04.*
= 0.1.4 =
* Removed debugging info.
* *Updated 2011-02-03.*
= 0.1.3 =
* Trailing slash check now applies to all post types, including custom post types.
* Reordered get_custom_permalink_sample arguments to ensure the permalink is returned by default.
* Modified the way requests are parsed.
* *Updated 2011-02-03.*
= 0.1.2 =
* Removed manual setting of 404 error. (Fixes lookup for non-custom permalinks)
* *Updated 2011-02-01.*
= 0.1.1 =
* Fallback check to get post by custom permalink now passes original lookup parameters. (Fixes failed alias lookup)
* *Updated 2011-02-01.*
= 0.1 =
* This is the very first version.
* *Updated 2011-02-01.*