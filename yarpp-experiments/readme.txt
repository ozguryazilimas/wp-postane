=== YARPP Experiments ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Plugin URI: http://yarpp.org/
Donate link: http://tinyurl.com/donatetomitcho
Tags: yarpp, debug, experiment, experimental, related, related posts, utilities, options, advanced
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 0.8

Some extras for tuning and diagnosing YARPP.

== Description ==

Some extras for tuning and diagnosing YARPP. Requires YARPP 3.5.4b2 for full compatibility.

Currently includes the following experiments:

* **Cache Status:** computes some statistics which give an overall picture of YARPP's results and cache usage. Some controls to flush and build up the cache.
* **Throttle:** lets you slow down YARPP's computation of "related" results when not cached. It may be useful for very high traffic sites, where suddenly turning YARPP on may cause some database lockups.
* **Dingus:** lets you try different YARPP settings, returning results as well as caching and performance information.

Use at your own risk!

== Installation ==

1. Install YARPP.
2. Install this plugin.
3. Handle with care.

== Screenshots ==

1. Cache Status, with manual cache building
2. Throttle
3. Dingus

== Changelog ==

= 0.8 =
* Updated for updated `stats` method in YARPP 3.5.4b2

= 0.7 =
* Update jQuery rangeinput code; fix CSS
* Require `stats` method, implemented in YARPP 3.5.4b1
* Fix a bug where computation would die prematurely

= 0.6 =
* Added nonce for cache flushing... now requires YARPP 3.4.1b6 for cache flushing to work
* Removed unnecessary `required` attribute in dingus

= 0.5 =
* Previous version required PHP 5.3.

= 0.4 =
* Added the Dingus which lets you try different YARPP settings.

= 0.3 =
* Support for the `$yarpp` global

= 0.2 =
* JavaScript tweak: now bundles the necessary jQuery.range library
* Add EXPERIMENTAL badge
* Added the old-school manual cache controls
* Don't throttle if we're DOING_AJAX

= 0.1 =
* Initial commit, with stats and throttle.