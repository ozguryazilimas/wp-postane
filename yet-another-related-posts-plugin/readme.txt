=== YARPP - Yet Another Related Posts Plugin ===
Contributors: jeffparker, shareaholic
Tags: related posts, contextual related posts, similar posts, posts, seo, thumbnails, feeds, multisite, multilingual
Donate link: https://yarpp.com
Requires at least: 3.7
Requires PHP: 5.3
License: GPLv2 or later
Tested up to: 6.2
Stable tag: 5.30.3

The best WordPress plugin for displaying related posts. Simple and flexible, with a powerful proven algorithm and inbuilt caching.

== Description ==

= Related Posts Plugin for WordPress =

*Yet Another Related Posts Plugin (YARPP)* is a professionally maintained, highly customizable, performant and feature rich plugin that displays pages, posts, and custom post types related to the current entry. YARPP introduces your visitors to other relevant content on your site -- boosting visitor engagement, time on site and SEO. Related Posts can increase your pageviews up to 10%. Simply install, activate and watch your sessions and pageviews increase.

= Key Features =

* **An advanced and versatile algorithm**: Using a customizable algorithm considering post titles, content, tags, categories, and custom taxonomies, YARPP finds related content from across your site
* **Caching**: Inbuilt cache makes subsequent queries super efficient and fast
* **Custom Templates and Styles**: Use a pre-built **list** or **thumbnail** template, or use the YARPP custom templating system for 100% control of how results are styles and displayed
* **Flexible** with a full range of placement options, including:
    + [Automatic Display Options](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation) (pick where to show YARPP from the YARPP settings page)
    + [RSS/Feed Options](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
    + [Block Editor (Gutenberg)](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
    + [[yarpp] Shortcode](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
    + [Widget](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
    + [YARPP functions()](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
    + [REST API](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
* **Works with all languages**, including those with full-width (double-byte) characters and those that don't use spaces between words
* **Custom post type and taxonomy** support
* **WordPress Multisite** support
* **bbPress forums** support
* **WooCommerce** support
* **Professionally maintained and supported** with regular updates

= YARPP Algorithm Explained =

https://videopress.com/v/0n2TSzkb

= Contribute: Translate YARPP =

YARPP is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/yet-another-related-posts-plugin/). Please check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/).

= Wide Support =

YARPP is the **most popular** and **the highest rated** [Related Posts Plugin for WordPress](https://yarpp.com/). With your support, this plugin always strives to be the best WordPress plugin for Content Discovery and Related Posts.

✔ Over 10 years of development
✔ Over 6 million downloads
✔ Translated into more than a dozen languages
✔ Professionally maintained and actively supported with regular updates
✔ Works with all languages

YARPP works best with PHP 5.3 or greater, MySQL 5.6 or greater OR MariaDB 10.1 or greater, and WordPress 3.7 or greater. See [the FAQ](https://wordpress.org/plugins/yet-another-related-posts-plugin/faq/) for answers to common questions.

== Screenshots ==

1. YARPP options in WP-Admin: Automatic Display Options
2. YARPP options in WP-Admin: "The Pool" settings
3. YARPP options in WP-Admin: "The Algorithm" settings
4. Example - YARPP on starwars.com
5. Example - YARPP on CB2.com
6. Example - YARPP on chrisguillebeau.com

== Installation ==

= Install YARPP from within WordPress =

1. Visit the plugins page within your WordPress dashboard and select `Add New`
1. Search for `YARPP`
1. Press "Install Now" for the `YARPP` plugin
1. Activate YARPP from your Plugins page
1. Go to the "YARPP" settings page to customize YARPP
1. *[optional]* copy the sample YARPP Custom Template files inside the `yarpp-templates` folder into your active theme folder.

= Install YARPP Manually =

1. Upload the `yet-another-related-posts-plugin` folder to the `/wp-content/plugins/` directory
1. Activate YARPP from your Plugins page
1. Go to the "YARPP" settings page to customize YARPP
1. *[optional]* copy the sample YARPP Custom Template files inside the `yarpp-templates` folder into your active theme folder.

= Install YARPP with the WP-CLI =

1. `wp plugin install yet-another-related-posts-plugin --activate`
1. Go to the "YARPP" settings page to customize YARPP
1. *[optional]* copy the sample YARPP Custom Template files inside the `yarpp-templates` folder into your active theme folder.

The *YARPP custom template* feature allows you to easily customize look and feel using the same coding conventions and [Template Tags](https://codex.wordpress.org/Template_Tags) as in WordPress themes.

Your YARPP Custom Template file must be in the *active theme's main directory* in order to be recognized by YARPP. If your theme did not ship with a YARPP templates, move the files in the `yarpp-templates` directory which ships with YARPP into your active theme's main directory. Be sure to move the *files* (which must be named `yarpp-template-`...`.php`) to your theme, not the entire directory. There is no limit restriction on custom templates.


= After Activation =

You should customize your Pool, Algorithm and Automatic Display Options settings from the YARPP settings page. YARPP supports a full range of placement options, including Automatic Display Options (pick where to show YARPP from the YARPP settings page), Block, Shortcode, Widget, YARPP functions() and REST API support. Details below.

= Automatic Display Options =

Configurable from the YARPP settings page, the *Automatic Display Option* automatically displays YARPP right below post content. You can configure which posts types to display YARPP on from the YARPP settings page.

If you do not want to show the Related Posts display in its default position (right below the post content), first go to YARPP options and turn off the "Automatic Display Options".

If you would like to instead display it in your sidebar and you have a widget-aware theme, YARPP provides a Related Posts widget which you can add under "Appearance" > "Widgets."

If you would like to add Related Posts elsewhere (such as inline related posts), edit your relevant theme file (most likely something like `single.php`) and add the shortcode code `[yarpp]` or PHP function `yarpp_related();` within [The Loop](https://codex.wordpress.org/The_Loop) where you want to display the related posts. Make sure you don't add `echo yarpp_related();` or you may end up with duplicates in your related posts section. The YARPP Block is also an option.

= Automatic Display in Feed Options =

Make sure the "display related posts in feeds" option is turned on if you would like to show related posts in your RSS and Atom feeds. The "display related posts in feeds" option can be used regardless of whether you auto display them on your website (and vice versa).

= Block =

YARPP ships with full support for Gutenberg Blocks. You can place YARPP's Block manually wherever you’d like in post content.

= Shortcode =

You can place YARPP's related posts manually wherever you’d like in post content or theme files.

To add to post content, use the shortcode:

`[yarpp]` to show content related to the current post. You may use more than one YARPP shortcode in a given post or template.

All the parameters accepted by `yarpp_related()` (see below) can also be used in the shortcode. Here are some examples...

If you know the reference Post ID that you want to show content related to, use:

`[yarpp reference_id=123]` to show content related to post 123

To specify which YARPP template to use, use the "template" attribute like so:

`[yarpp template="yarpp-template-photoblog"]` - where `yarpp-template-photoblog.php` is the file name of the custom template
`[yarpp template="list"]` - built-in "List" template
`[yarpp template="thumbnails"]` - built-in "Thumbnails" template

To specify maximum number of posts to show, use the "limit" attribute like so:

`[yarpp template="list" limit=3]`

To only show recent posts, use the "recent" attribute (provide it with a number, a space, and then either "day", "week" or "month")

`[yarpp recent="6 month"]`

To show another post type, use the "post_type" parameter.

`
[yarpp post_type="page"]
`

If you need to provide multiple values (eg posts and pages, or a list of categorires of posts to exlude), separate them with a comma but no space.
`
[yarpp post_type="post,page"] - include both posts and pages in results
[yarpp exclude="1,2,3"] - exclude posts with categories with IDs 1,2 and 3
`

To adjust the weights and taxonomy requirements, provide the value as JSON (using the same structure as for `yarpp_related()` below). Eg

`
[yarpp weight='{"body":1}'] - only consider body content
[yarpp weight='{"body":2,"title":2,"tax":{"category":0, "post_tag":0}}'] - give extra weight to bodies and titles, but none to categories
[yarpp require_tax='{"category": 2, "post_tag": 0}'] - require at least one category in common
`

To add YARPP shortcode to your theme files (eg. single.php), use:

`<?php echo do_shortcode('[yarpp]'); ?>`
`<?php echo do_shortcode('[yarpp reference_id=123]'); ?>`

If you're adding the shortcode *outside [the loop](http://codex.wordpress.org/The_Loop)*, you must provide the reference_id.

= Widget =

Related posts can also be displayed as a widget. Go to the Appearance > Widgets options page and add the "Related Posts (YARPP)" widget. Choose to display content from YARPP Basic. The widget will only be displayed on single entry (permalink) pages.

The YARPP widget can be used even if the "auto display" option is turned off.

= REST API =

Add related posts to your JavaScript driven app!

YARPP adds a REST API endpoint for fetching related posts. The JSON results from the REST API query are the same as you would see if you were visiting a post on your website (the same quantity and order). It is possible to override the quantity at the time of making the REST API request.

[[Documentation](https://support.shareaholic.com/hc/en-us/articles/360046456752)]

= YARPP functions() =

Developers can call YARPP's powerful relatedness algorithm from anywhere in their own code. Some examples and more details are in this [WordCamp Birmingham talk](https://www.slideshare.net/mitcho/relate-all-the-things).

You may use the functions defined in `includes/related_functions.php` in your own code, notably:

1. `yarpp_related()` - gets the HTML for related posts and displays it
1. `yarpp_related_exist()` - checks whether any related posts exists
1. `yarpp_get_related()` - returns the list of related posts (`WP_Post` objects)

*Examples:*

Show related posts, using all the settings set on the YARPP settings page:

`<?php yarpp_related(); ?>`

Each of these functions will default to using the settings set on the YARPP settings page, but can be customized. For example:

<code>
yarpp_related(
  array(
    // Pool options: these determine the "pool" of entities which are considered
    'post_type' => array('post', 'page', 'wc_product', ...), //  post types to include in results
    'show_pass_post' => true, // show password-protected posts
    'show_sticky_posts' => true, // show sticky posts
    'past_only' => true, // show only posts which were published before the reference post
    'exclude' => array(), // a list of term_taxonomy_ids. entities with any of these terms will be excluded from consideration.
    'recent' => false, // to limit to entries published recently, set to like '15 day', '20 week', or '12 month' (https://www.mysqltutorial.org/mysql-interval/)
    
    // Relatedness algorithm options: these determine how "relatedness" is computed
    // Weights are used to construct the "match score" between candidates and the reference post
    'weight' => array(
      'body' => 1,
      'title' => 2, // larger weights mean this criteria will be weighted more heavily
      'tax' => array(
        'post_tag' => 1,
        ... // put any taxonomies you want to consider here with their weights
      )
    ),
    // Specify taxonomies and a number here to require that a certain number be shared:
    'require_tax' => array(
      'post_tag' => 1 // for example, this requires all results to have at least one 'post_tag' in common
    ),
    // The threshold which must be met by the "match score" to be considered related
    'threshold' => 5,
    
    // Display options:
    'template' => 'thumbnails', // which theme/custom template to use. Built-in ones include "list" and "thumbnails", or the name of a YARPP template file in your active theme folder starting with "yarpp-template-". Example: yarpp-template-videos or yarpp-template-videos.php
    'limit' => 5, // maximum number of results
    'order' => 'score DESC', // column on "wp_posts" to order by, then a space, and whether to order in ascending ("ASC") or descending ("DESC") order
    'promote_yarpp' => true, // boolean indicating whether to add 'Powered by YARPP' below related posts
    'generate_missing_thumbnails' => true, // automatically generate missing thumbnail sizes on the fly
    'extra_css_class' => 'class_1 class_2', // add CSS classes to YARPP's parent div
  ),
 $reference_ID, // second argument: (optional) the post ID. If not included, will use the current post.
 true // third argument: (optional) true to echo the HTML block; false to return it
);
</code>

Options which are not specified will default to those specified on the YARPP settings page. Additionally, if you are using a builtin template rather than specifying a custom template file in `template`, the following arguments can be used to override the various parts of the builtin template: `before_title`, `after_title`, `before_post`, `after_post`, `before_related`, `after_related`, `no_results`, `excerpt_length`.

If you need to implement related entries programmatically or to know whether they exist, you can use the functions:

1. `yarpp_get_related( $args, $reference_ID )`
Returns an array of `post` objects, just like the WordPress function `get_posts`.
1. `yarpp_related_exist( $args, $reference_ID )`
Returns a boolean for whether any such related entries exist.

For each of these functions, `$args` takes the same arguments as those detailed for `yarpp_related()` above, except for the various display and template options.

Note that at this time custom YARPP queries using the functions mentioned here are *not* cached in the built-in YARPP caching system. Thus, if you notice any performance hits, you may need to write your own code to cache the results.

Example of how to use a custom YARPP query and cache the results for a day:

<code>
$result = get_transient('yarpp_custom_results_for_' . $post->ID);
if(! $result){
  $result = yarpp_related(['post_type' => 'reply'],null,false);
  set_transient('yarpp_custom_results_for_' . $post->ID, $result, DAY_IN_SECONDS);
}
echo $result;
</code>

Show at most 4 related WooCommerce products based on their title and especially on their categories, assuming custom YARPP template
`yarpp-related-wc-products.php` has been added to the active theme folder:

<code>
yarpp_related(
  array(
    'limit' => 4,
    'weight' => array(
      'title' => 1,
      'tax' => array(
        'category' => 2
      )
    ),
    'post_type' => 'wc_product',
    'template' => 'yarpp-related-wc-products.php'
  )
);
</code>

Check for posts related to post with ID 123, and loop through them in order to do some more custom logic:

<code>
$related_posts = yarpp_get_related(array(), 123);
if(! $related_posts){
  echo 'No related posts';
} else {
  foreach($related_posts as $post){
    // $post is a WP_Post object.
    echo $post->post_title;
  }
}
</code>

= YARPP Filters =

Documentation: [https://support.shareaholic.com/hc/en-us/articles/4408775687060](https://support.shareaholic.com/hc/en-us/articles/4408775687060)

== Frequently Asked Questions ==

If your question isn't here, ask your own question at [the WordPress.org forums](https://wordpress.org/support/plugin/yet-another-related-posts-plugin).

= Many pages list "no related posts." =

Most likely you have "no related posts" right now because the default "match threshold" is too high. Here's what we recommend to find an appropriate match threshold: lower your match threshold in the YARPP "Algorithm" options to something very low, like 1. (If you don't see the match threshold, you may need to display the "Algorithm" options via the "Screen Options" tab at the top.) Most likely the really low threshold will pull up many posts that aren't actually related (false positives), so look at some of your posts' related posts and their match scores. This will help you find an appropriate threshold. You want it lower than what you have now, but high enough so it doesn't have many false positives.

= How can I limit related posts to a certain time frame? For instance, I don't want to show posts from two years ago. =

In Wordpress, go to "Settings" and "Related Posts (YARPP)" and make sure "The Pool" is checked in the "Screen Options" section at the top of the page.  In "The Pool" section, check the box next to "Show only posts from the past *X* months."

= Where do I tell YARPP to display related posts only by tags? =

In WordPress, go to "Settings" and "YARPP" and make sure "Algorithm" is checked in the "Screen Options" section at the top of the page.  In the "Algorithm" section, configure the dropdown boxes next to "Titles," "Bodies," "Categories," and "Tags."

= Can I manually specify related posts? =

Sorry, but specifying related posts, displaying related posts from external WordPress sites, and pulling content from the Comments section are all outside the scope of YARPP at this time.

= I'm seeing related posts displayed on the home page. How do I prevent that? =

Some WordPress themes treat the home page as an archive or a "page." Go to "Settings" then "Related Posts (YARPP)" and view the "Automatic Display Options" section. Make sure "Pages" and "Also display in archives" are not checked.

= How can I prevent the "related posts" list from displaying on specific posts? =

**Option 1:**

On the edit post page, uncheck “Display Related Posts” in the YARPP box. Be sure to update or publish to save your changes.

**Option 2:**

Add `<!--noyarpp-->` to the HTML code of any post to prevent related posts from displaying.

**Option 3:**

Use the `noyarpp` filter. For example:

<code>
// Disable YARPP Automatic Display in specific categories
function yarpp_disable_categories() {
  // Examples of in_category usage: https://codex.wordpress.org/Function_Reference/in_category
  if ( in_category( array( 'my_category_1_slug', 'my_category_2_slug' ) ) ) {
    return true;
  }
}
add_filter( 'noyarpp', 'yarpp_disable_categories' );
</code>

These solutions only work if you are using "Automatic Display" in the "Display Options" section. If you are programatically calling `yarpp_related()`, the shortcode `[yarpp]` from PHP code or the YARPP Block, you will need to do your own conditional checks.

= I'm using the Thumbnails display in YARPP 4+. How do I override the style of the text that displays? The title only shows two lines, the font is larger than I'd like, I'd like to center the thumbnails, etc. =

If you're familiar with CSS, you can override any YARPP styles by editing your theme's `style.css` file, or any other CSS file you may have created that loads after the YARPP one. To edit your theme's CSS file, go to "Appearance" then "Editor" and then click `style.css` in the right sidebar. Add changes at the bottom of the file and click "Save." If you do edit this file, just make sure you add `!important` after each style declaration, to make sure they'll override the YARPP rules.

Some common overrides that YARPP users have added are:

<code>
/* Reduces the title font size and displays more than two title lines */
.yarpp-thumbnail {height: 200px !important;}
.yarpp-thumbnail-title {font-size:0.8em !important; max-height: 4em !important}

/* Centers the thumbnail section */
.yarpp-related-widget {text-align:center !important;}
</code>

Once you save any CSS changes, empty your browser's cache and reload your page to see the effect.

= How do I remove sold out WooCommerce products from recommendations (and otherwise filter by postmeta)? = 
If you use YARPP to show related products and want to exclude products that are sold out, use the following code snippet:

<code>
function yarpp_custom_wp_query($query) {
    if(isset($query->yarpp_cache_type)){
        $query->set('post_type', 'product');
        $meta_query = [
            'relation' => 'AND',
            [
                'key' => '_stock_status',
                'value' => ['instock','onbackorder'],
                'compare' => 'IN',
            ]
        ];
        $query->set('meta_query', $meta_query);
    }

    return $query;
}
add_filter('pre_get_posts', 'yarpp_custom_wp_query', 100);
</code>

Note: this filter applies after the related items were already calculated, so when it filters out an item you will see fewer related items than you requested.

You can similarly [filter WordPress' meta queries](https://developer.wordpress.org/reference/classes/wp_meta_query/) to include/excluded posts from YARPP's related results.

= I'm using the Thumbnails display. How can I change the thumbnail size? =

As of YARPP v5.19.0, YARPP usually defaults to using WordPress' default thumbnail size. This can be changed to another thumbnail size using the YARPP setting "Thumbnail Size".

However, if you used YARPP before v5.19.0, or your theme defines a "yarpp-thumbnail" size, the default is the "yarpp-thumbnail". To change "yarpp-thumbnail" size, add the following to your theme's `functions.php` file with appropriate width and height variables:

`add_image_size( 'yarpp-thumbnail', $width, $height, true );` 

When you do this, make sure you also set the YARPP setting "Thumbnail Size" to "yarpp-thumbnail".

Each time you change YARPP's thumbnail dimensions like this, you will probably want to have WordPress regenerate appropriate sized thumbnails for all of your images. We highly recommend the [Regenerate Thumbnails](https://wordpress.org/extend/plugins/regenerate-thumbnails/) plugin for this purpose.

Note: if you don't use YARPP's thumbnail size, you might want to avoid generating it for newly uploaded images to save space. To do that add the following code snippet to your theme's `functions.php` file:

`add_filter( 'yarpp_add_image_size', "__return_false" );`

= I'm using the Thumbnails display. Why aren't the right size thumbnails being served? =

By default, if an appropriately sized thumbnail is not available in WordPress, a larger image will be served and will be made to fit in the thumbnail space via CSS. Sometimes this means images will be scaled down in a weird way, so it is not ideal. What you really want is for YARPP to serve appropriately-sized thumbnails.

There are two options for doing so:

* First, you can use the [Regenerate Thumbnails](https://wordpress.org/extend/plugins/regenerate-thumbnails/) plugin to generate all these thumbnail-sized images in a batch process. This puts you in control of when this resizing process happens on your server (which is good because it can be processor-intensive). New images which are uploaded to WordPress should automatically get the appropriate thumbnail generated when the image is uploaded.

* Second, you can turn on a feature in YARPP to auto-generate appropriate size thumbnails on the fly, if they have not yet been created. Doing this type of processing on the fly does not scale well, so this feature is turned off by default. But if you run a smaller site with less traffic, it may work for you. Simply add `define('YARPP_GENERATE_THUMBNAILS', true);` to your theme's `functions.php` file.

= I'm using the Thumbnails display. Why are some of my posts missing appropriate images? =

YARPP's thumbnail view requires that a WordPress "featured image" be set for each post. If you have many posts that never had a featured image set, we recommend the plugin [Auto Post Thumbnail](https://wordpress.org/extend/plugins/auto-post-thumbnail/), which will generate post thumbnails for you.

= Is YARPP compatible with WordPress Multisite? =

YARPP should work fine in a multisite environment, and many users are running it without any issues using WordPress Multisite. It will, however, only get results *within* each blog. It will not display related posts results from across your network.

= I want to use YARPP on a site with content in multiple languages. =

The recommended solution in such cases is to use the [Polylang](https://polylang.wordpress.com/) plugin. Polylang has posted [a tutorial for using YARPP with Polylang](https://polylang.wordpress.com/2013/05/03/polylang-and-yarpp/).

= Does YARPP work with full-width characters or languages that don't use spaces between words? =

YARPP works fine with full-width (double-byte) characters, assuming your WordPress database is set up with Unicode support. 99% of the time, if you're able to write blog posts with full-width characters and they're displayed correctly, YARPP will work on your blog.

However, YARPP does have difficulty with languages that don't place spaces between words (Chinese, Japanese, etc.). For these languages, the "consider body" and "consider titles" options in the "Algorithm options" may not be very helpful. Using only tags and categories may work better for these languages.

= Does YARPP slow down my blog/server? =

YARPP is a highly optimized plugin with an inbuilt cache that makes subsequent queries super efficient. This means that YARPP will not slow your site down.

If you are running a large site and need to throttle YARPP's computation, try the official [YARPP Experiments](https://wordpress.org/extend/plugins/yarpp-experiments/) plugin which adds this throttling functionality.

= Are there any plugins that are incompatible with YARPP? =

* [SEO_Pager plugin](https://wordpress.org/support/topic/267966): turn off the automatic display option in SEO Pager and instead add the code manually.
* Other related posts plugins, obviously, may also be incompatible.

Please submit similar bugs by starting a new thread on [the WordPress.org forums](https://wordpress.org/support/plugin/yet-another-related-posts-plugin). We check the forums regularly and will try to release a quick bugfix.

= YARPP seems to be broken since I upgraded to WordPress X.X. =

Before upgrading to a new WordPress version, you should first deactivate all plugins, then upgrade your WordPress, and then reactivate your plugins. Even then, you may still find that something went wrong with your YARPP functionality. If so, try these steps:

1. Visit the "YARPP" settings page to verify your settings.
2. Deactivate YARPP, replace the YARPP files on the server with a fresh copy of the new version, and then reactivate it.
3. From the "YARPP" settings page, flush the YARPP cache

= Can I clear my cache? Can I build up the cache manually? =

Yes, there is a button to clear YARPP's cache table in YARPP's WP Admin options.

= How to add support for a Custom Post Type (CPT)? =

To make YARPP support your Custom Post Type (CPT), the attribute `yarpp_support` must be set to true when the CPT is registered. The CPT will then be available in the YARPP settings page.

`'yarpp_support' => true`

For example:

<code>
function register_my_cpt() {
  $args = array(
    'public' => true,
    'label'  => 'Books',
    'yarpp_support' => true,
  );
  register_post_type( 'book', $args );
}
add_action( 'init', 'register_my_cpt' );
</code>

If you do not have access to the code which is registering the CPT, maybe because it is a third-party plugin that is creating it, you can still add the `yarpp_support` argument:

<code>
/**
 * Filter the CPT to register more options
 *
 * @param $args       array    The original CPT args.
 * @param $post_type  string   The CPT slug.
 *
 * @return array
 */
function add_yarpp_support_to_post_types( $args, $post_type ) {

  // If not our target CPT, exit.
  if ( 'my_custom_post_type' !== $post_type ) {
    return $args;
  }

  // Add additional YARPP support option.
  $cpt_args = array(
    'yarpp_support' => true
  );

  // Merge args together.
  return array_merge( $args, $cpt_args );
}
add_filter( 'register_post_type_args', 'add_yarpp_support_to_post_types', 10, 2 );
</code>

*You should replace `my_custom_post_type` with the CPT that you need to add YARPP support to and add this code to the `functions.php` of your theme.*

If you would like to programmatically control which post types are considered in an automatically-displayed related posts display, use the `yarpp_map_post_types` filter.

= Does YARPP support custom taxonomies? =

Yes. Any taxonomy, including custom taxonomies, may be specified in the `weight` or `require_tax` arguments in a custom display as above. `term_taxonomy_id` specified in the `exclude` argument may be of any taxonomy.

If you would like to choose custom taxonomies to choose in the YARPP settings UI, either to exclude certain terms or to consider them in the relatedness formula via the UI, the taxonomy must (a) have either the `show_ui` or `yarpp_support` attribute set to true and (b) must apply to either the post types `post` or `page` or both.

= Can I disable the Review Notice forever? =

If you want to prevent the Review Notice from appearing you can use the function below:

<code>
/**
 * Disable YARPP Review Notice
 *
 */
function yarpp_disable_review_notice() {
  remove_action('admin_notices', array('YARPP_Admin', 'display_review_notice'));
}
add_action('admin_init', 'yarpp_disable_review_notice', 11);
</code>

= Can I disable the request-for-feedback modal when deactivating the plugin? =
Sure. Use the following code:

<code>
add_action(
  'admin_init',
  function(){
    remove_all_filters('shareaholic_deactivate_feedback_form_plugins');
  },
  11
);
</code>

= I removed the YARPP plugin but I still see YARPP-related database tables. Shouldn't those be removed, too? =

Beginning with version 4.0.7, YARPP includes clean uninstall functionality. If you no longer wish to use YARPP, first deactivate YARPP using the "Plugins" page in WordPress, then click the "Delete" link found on the same page. This process will automatically remove all YARPP-related files, including temp tables. If you manually try to remove YARPP files instead of going through WordPress, some files or temp tables could remain.


== Changelog ==
= 5.30.3 (28-APRIL-2023) =
* [Bugfix](https://wpscan.com/vulnerability/b34976b3-54c3-45b7-86a0-387ee0a4b680): Improved how function attributes are sanitized to avoid possible security issues

= 5.30.2 (29-January-2023) =
* [Bugfix](https://wpscan.com/vulnerability/c6cf792b-054c-4d77-bcae-3b700f42130b): Improved how function attributes are sanitized to avoid possible security issues

= 5.30.1 (08-November-2022) =
* [Bugfix](https://wordpress.org/support/topic/something-wrong-with-yaarp-plugin-this-morning/): Fixes issue caused by stricter type checks for arithmetic operators in PHP8

= 5.27.8 (01-December-2021) =
* New: `[yarpp]` shortcode supports all the same parameters as `yarpp_related()`. [See the updated documentation.](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
* Bugfix: Apply the additional CSS class(es) defined in YARPP Advanced Block settings

= 5.27.7 (29-October-2021) =
* Enhancement: CSS improvements for better cross-theme compatibility
* Documentation: [Available YARPP filters](https://support.shareaholic.com/hc/en-us/articles/4408775687060)

= 5.27.6 (12-October-2021) =
* Bugfix: Support for drop-in caches. Resolves `Undefined variable: found` warnings.
* Bugfix: Add missing string for translations
* Bugfix: Workaround for [WordPress ca-bundle.crt issue](https://core.trac.wordpress.org/ticket/54207)
* Bugfix: Properly update the template preview in YARPP's admin console when Thumbnail size selection is updated
* Bugfix: Use dummy example thumbnail in previews
* Enhancement: For RSS Feed Display Options use dropdown select for thumbnail sizes instead of radio buttons

= 5.27.5 (15-September-2021) =
* New: Add support for "show_sticky_posts" and "generate_missing_thumbnails" to the [yarpp_related() function](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
* New: Add support for "recent" in [YARPP shortcode](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation)
    + For example, to limit results to those published in the past 12 months: `[yarpp recent="12 month"]`
* Enhancement: For Automatic Display Options use dropdown select for thumbnail sizes instead of radio buttons

= 5.27.4 (25-August-2021) = 
* [Bugfix](https://wordpress.org/support/topic/php-8-deprecated-required-parameter-follows-optional/): PHP 8 compatibility by making `$args` optional on `YARPP::get_template_content()` 

= 5.27.3 (16-August-2021) =
* [Bugfix](https://wordpress.org/support/topic/fatal-error-for-custom-template-after-upgrading-5-27-2/): Make `$related_query` available to custom templates

= 5.27.2 (12-August-2021) =
* New: Conversion of legacy widget YARPP block into YARPP Block
* [Bugfix](https://wordpress.org/support/topic/display-in-sidebart-but-not-on-the-main-page/): New Blocks on widget's sections were showing on homepage
* Bugfix: Remove the legacy widget YARPP block on the widgets interface
* New: Option to add a custom reference ID on a YARPP Block
* Enhancement: Consistency on "Heading" input fields on widgets and blocks
* Enhancement: Show demo related post on the Widgets YARPP Blocks

= 5.27.1 (03-August-2021) =
* Bugfix: i18n / localization for new text strings was not working.
    + P.s. YARPP is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/yet-another-related-posts-plugin/). Please check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/).
* [Bugfix](https://wordpress.org/support/topic/wp_query-not-working-for-some-reason/): Remove YARPP's `WP_Query` filters after the query is done rather than waiting until rendering is done because any queries done in that time will be affected.

= 5.27.0 (20-July-2021) =
* WordPress v5.8 compatibility
* [Bugfix](https://wordpress.org/support/topic/trying-to-get-property-id-of-non-object-yarpp_core-line-1880/): Fixes PHP debug notice "Trying to get property 'ID' of non-object"

= 5.26.0 (14-July-2021) =
* [New](https://wordpress.org/support/topic/disable-yarpp-thumbnail-120x120/): Adds a `yarpp_add_image_size` filter to avoid creating YARPP thumbnail sizes
    + `add_filter( 'yarpp_add_image_size', '__return_false' );`
* New: Preview YARPP Templates on the YARPP settings page under Automatic Display Options
* Docs: Added FAQ on how to remove sold out WooCommerce products from recommendations (and otherwise filter by postmeta)

= 5.25.0 (23-June-2021) =
* Enhancement: Cleaned up old unused code (adkengage)

= 5.24.0 (17-June-2021) =
* Enhancement: Option to automatically generate missing thumbnail sizes on the fly when using Custom YARPP Templates
* [New](https://wordpress.org/support/topic/remove-yarpp-from-the_content-or-the_excerpt/): Adds a `noyarpp` filter to control YARPP Automatic Display programatically.
    + For example: `add_filter( 'noyarpp', 'custom_function' );`
* Enhancement: Use the faster and less memory intensive function `strpos()` instead of `stristr()`

= 5.23.0 (02-June-2021) =
* [New](https://wordpress.org/support/topic/is-it-possible-to-use-different-styles-of-yarpp-in-every-post/): Ability to specify maximum number of posts to show in the YARPP shortcode.
    + For example, to set maximum post limit to 3: `[yarpp template="list" limit=3]`
* Bugfix: Check `wp_parse_list` exists for backwards compatibility to older versions of WordPress
* [Bugfix](https://wordpress.org/support/topic/yarpp_related-does-not-return-all-posts/): Use query parameters when priming cache using `yarpp_related`. Resolves bug with `yarpp_function` not fully accounting for all parameters passed to it.

= 5.22.0 (24-May-2021) =
* [New](https://wordpress.org/support/topic/display-results-from-specific-post-types/): Choose the exact post types to include in "The Pool"
* [New](https://wordpress.org/support/topic/is-it-possible-to-use-different-styles-of-yarpp-in-every-post/): Ability to specify built-in YARPP templates in the YARPP shortcode. You may use more than one YARPP shortcode in a given post or template and each can have a different template specified. For example:
    + `[yarpp template="yarpp-template-simple"]` // where `yarpp-template-simple.php` is the file name of the custom YARPP template in your active theme folder
    + `[yarpp template="list"]` // built-in "List" template
    + `[yarpp template="thumbnails"]` // built-in "Thumbnails" template
* Enhancement: YARPP Automatic Placement, YARPP Shortcode, YARPP Block, YARPP Widget and RSS output are all now wrapped in a `div` with classes `yarpp`, `yarpp-related` and `yarpp-template-TEMPLATE-NAME` to facilitate easier CSS customizations
* Enhancement: All admin CSS is now minified

= 5.21.0 (14-May-2021) =
* Bugfix: Disable "Edit as HTML" for YARPP Block
* Bugfix ([1](https://wordpress.org/support/topic/template-does-not-change-ver-5-20-0/)|[2](https://wordpress.org/support/topic/custom-template-not-showing-since-v5-10-2/)): Fixes issue with the correct template not being applied

= 5.20.0 (06-May-2021) =
* New: Option to automatically generate missing thumbnail sizes on the fly
* New: Option to include or exclude Sticky Posts
* Enhancement: Dynamic CSS for the built-in "Thumbnails" theme is now in-lined for much improved cross compatibility with optimization and caching plugins
* [Bugfix](https://wordpress.org/support/topic/crashes-post-screen/): Fixes conflict with the [Page Optimize plugin](https://wordpress.org/plugins/page-optimize/)

= 5.19.0 (28-April-2021) =
* New: **YARPP Block** 🎉 now available for the WordPress Block Editor (Gutenberg) for drag and drop placement customization
* New: Choose image size for YARPP custom theme templates
* [Bugfix](https://wordpress.org/support/topic/database-entries-being-added-to-the-post-as-html): Indicate `yarpp_meta` postmeta is protected to prevent themes from displaying it
* [Bugfix](https://wordpress.org/support/topic/fatal-error-3858/): Check `wp_get_additional_image_sizes` exists for backwards compatibility to older versions of WordPress

= 5.18.2 (19-April-2021) =
* [Bugfix](https://wordpress.org/support/topic/thumbnail-size-wont-change/): Allow default WP image sizes to be picked for the YARPP Thumbnail theme

= 5.18.1 (19-April-2021) =
* New: Choose image size for YARPP Thumbnail theme
* [Bugfix](https://wordpress.org/support/topic/warning-message-yarpp_cache-php/): Resolves `join` warning (Part 2/2)

= 5.17.0 (06-April-2021) =
* [New](https://wordpress.org/support/topic/unable-to-dequeue-related-css-stylesheet/): Adds `yarpp_enqueue_related_style` filter to be able to dequeue related.css
    + `add_filter( 'yarpp_enqueue_related_style', '__return_false' );`
* New: Adds friendly per-post meta box options to disable YARPP automatic display on a specific post

= 5.16.1 (29-March-2021) =
* Bugfix: include new minified JS files

= 5.16.0 (29-March-2021) =
* Enhancement: Switches to minified JavaScript in YARPP Admin for a speed boost
* [Bugfix](https://wordpress.org/support/topic/cannot-save-changes-spinning-forever/): Resolves conflict with Easy Forms for MailChimp which was preventing YARPP settings from being saved
* [Bugfix](https://wordpress.org/support/topic/warning-message-yarpp_cache-php/): Resolves `join` warning (Part 1/2)

= 5.15.3 (15-March-2021) =
* Enhancement: Improves readability of the database indexes prompt

= 5.15.2 (12-March-2021) =
* Tested up to WordPress 5.7

= 5.15.1 (11-March-2021) =
* Bugfix: Fixes issue pushing 5.15.0

= 5.15.0 (11-March-2021) =
* [Enhancement](https://wordpress.org/support/topic/5-14-0-rendered-our-site-unusable/): Default to not comparing using titles and bodies and only add database indexes when they are enabled to improve performance
* Enhancement: Default algorithm threshold changed from 4 to 1 to more reliably find related content on new installs
* Enhancement: Clear cache button uses modal dialogs instead of native browser alerts
* [Bugfix](https://wordpress.org/support/topic/breaking-layout-5-14-0-wp5-7-twenty-fifteen/): apparent Oxygen Builder conflict

= 5.14.0 (9-March-2021) =
* Enhancement: Improve pageload speed by avoiding checking YARPP database requirements on frontend requests

= 5.13.1 (1-March-2021) =
* Add some of 5.13.0's changes that somehow weren't included

= 5.13.0 (1-March-2021) =
* New: Clear YARPP cache button on settings page
* [Bugfix](https://wordpress.org/support/topic/undefined-variable-post_types/) Undefined variable ``$post_types`

= 5.12.0 (22-February-2021) =
* New shortcode template attribute. Eg `[yarpp template="yarpp-template-photoblog"]`
* PHP 8.0 compatibility
* Deprecated: functions `related_posts()`, `related_pages()` and `related_entries() [use `yarpp_related() instead`]
* Deprecated: functions `related_posts_exist()`, `related_pages_exist()` and `related_entries_exist()` [use `yarpp_related_exist()` instead]
* Bugfix: consistently use "post_type" parameter and "cross-relate" from all YARPP functions
* [Bugfix](https://wordpress.org/support/topic/cannot-be-translated-because-there-is-no-text-domain-description/): Add textdomain to allow translating the readme file

= 5.11.0 (08-February-2021) =
* [New](https://wordpress.org/support/topic/why-related-topics-doesnt-show-up-under-topics-and-replies/): Adds native support for bbPress! Have you ever wanted a nifty Related Posts section on your bbPress topic pages, like the ones you've seen on forums like StackOverflow? It's now possible with YARPP Related Posts!
* Enhancement: Updates to provided custom template examples
* Enhancement: YARPP post metabox enhancements - adds "edit" and "view" hover options to each link, mimicking the main posts table
* Enhancement: Add missing debug info
* Bugfix: Avoid REST API warning by providing the page parameter which WP core expects
* Deprecated: functions `YARPP::maybe_enqueue_thumbnails()` and `YARPP::enqueue_thumbnails()`

= 5.10.2 (23-November-2020) =
* Enhancement: Faster queries and a speed boost ⚡️ (Re-introduces database query improvements while avoiding the fatal error identified by some in v5.10.0)
* [Enhancement](https://wordpress.org/support/topic/yarpp-query-migrate-helper-warnings/): Speed up by not loading widget.css when not necessary
* [Bugfix](https://wordpress.org/support/topic/yarpp-query-migrate-helper-warnings/): Part 2 of 2 - Resolves jQuery Migrate Helper warnings
* [Bugfix](https://wordpress.org/support/topic/php-error-423/): Fixes PHP warning on options page
* [Bugfix](https://wordpress.org/support/topic/plugin-causes-issues-with-algolia-search-integration/): Fixes integration with Algolia search
* [Bugfix](https://wordpress.org/support/topic/require-at-least-one-category-in-common/): Respect "Require at least 1 {Taxonomy} in common" when you press "refresh" in the meta box while editing a post

= 5.10.1 (23-October-2020) =
* [Critical Bugfix](https://wordpress.org/support/topic/version-5-10-0-generated-error-500/): Reverts query changes from the v5.10.0 update as it caused 500 fatal errors on some WordPress installations. We sincerely apologies for any issues it may have caused. We did test the release for weeks, but each environment is just a bit different. We are actively testing a bug fix with those affected that volunteered before we release it again.

= 5.10.0 (22-October-2020) =
* Enhancement: Faster queries and a speed boost ⚡
* [Enhancement](https://wordpress.org/support/topic/yarp-page-break/): On posts with page breaks, show YARPP only on the last page
* Enhancement: [Documentation](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation) on how to use `[yarpp]` shortcode from a theme file (like single.php) or post content
* Bugfix: If YARPP has already added its content to a post and somehow `apply_filters('the_content'...)` gets called again, don't try to re-add YARPP's content to the post again.
* Bugfix: Fixes showing/hiding excerpt options when checking excerpt checkboxes
* [Bugfix](https://wordpress.org/support/topic/bug-fix-for-role-related-code/): Fixes "role" in deactivation survey
* [Bugfix](https://wordpress.org/support/topic/php-error-423/): Fixes PHP warning on options page
* [Bugfix](https://wordpress.org/support/topic/yarpp-query-migrate-helper-warnings/): Part 1 of 2 - Resolves jQuery Migrate Helper warnings

= 5.9.0 (21-September-2020) =
* Enhancement: Clarify that "Also display in archives" includes front page and category pages
* Enhancement: `reference_id` parameter support for the YARPP shortcode. For example:  `[yarpp reference_id=123]` to show content related to Post ID 123 (can be used either inside or outside the loop from theme code)
* Enhancement: Place warning about comparing using titles and bodies next to the affected inputs, rather than at the top of the page where it could be lost with notices from other plugins
* [Bugfix](https://wordpress.org/support/topic/require-at-least-one-taxonomy-limited-to-taxonomies-available-the-post-type/): Don't require a category or term, even if set by the admin, on post types that don't use categories or terms (also applies to custom taxonomies)

= 5.8.0 (08-September-2020) =
* Enhancement: Code hygiene upgrade with usage of `WPDB->prepare` everywhere possible and related improvements
* [Bugfix](https://wordpress.org/support/topic/i-need-to-ad-yarpp-to-my-sidebar-but-it-wont-let-me-upgrade-to-pro/): Since there is no YARPP Pro currently, removed mention of it from the widget form

= 5.7.0 (18-August-2020) =
* Enhancement: Ensure that when a post is finally published, YARPP cache for peer posts is cleared to ensure the most related posts are always recommended across every post
* [Bugfix](https://wordpress.org/support/topic/an-error-occurred-while-processing-the-directive-6/): Fixed warning in Admin UI caused by an unnecessary HTML comment
* [Bugfix](https://wordpress.org/support/topic/related-posts-show-on-pages-not-posts/#post-13177845): When priming YARPP's cache, if "Display posts from other post types" is false, we now only include posts of the current post type

= 5.6.0 (13-August-2020) =
* New: Configurable in-browser caching option for YARPP REST API
* Bugfix: Always include unused categories and terms in YARPP Admin options
* [Bugfix](https://wordpress.org/support/topic/support-for-multilingual-2/): Fixed issue with loading CSS files from wrong domain on multilingual sites
* Various Admin UI fixes

= 5.5.0 (06-August-2020) =
* New: Support for custom page types and taxonomies, including those added by WooCommerce
* Enhancement: Show spinner while full-text indexes are being created
* Enhancement: Various YARPP Admin UX/UI improvements
* [Bugfix](https://wordpress.org/support/topic/nice-plugin-crappy-ever-present-notice/): Fixes bug where Review Notice was not getting dismissed for non-admins
* [Bugfix](https://wordpress.org/support/topic/please-exclude-the-gutenberg-blocks/): Exclude Gutenberg blocks so that they don't appear as related

= 5.4.0 (03-August-2020) =
* New: Shortcode support! `[yarpp]` ([documentation](https://wordpress.org/plugins/yet-another-related-posts-plugin/#installation))
* Enhancement: Adds YARPP score to REST API responses ([documentation](https://support.shareaholic.com/hc/en-us/articles/360046456752))
* Enhancement: Prepare for default jQuery version update in upcoming WordPress Core
* Bugfix: Fixed admin panel tooltips
* [Bugfix](https://wordpress.org/support/topic/copy-templates-does-nothing/): Fixed admin panel "copy template" button

= 5.3.0 (29-July-2020) =
* New: REST API support! ([documentation](https://support.shareaholic.com/hc/en-us/articles/360046456752))
* Enhancement: [WP Rest Cache Plugin](https://wordpress.org/plugins/wp-rest-cache/) support
* Enhancement: Adds filters to change the priority for YARPP's filters on `the_content`, `the_content_feed` and `the_excerpt_rss`
    + For example:  `add_filter( 'yarpp_content_priority', 1 );`
* Bugfix: Fixes deactivation survey when Google Translate in Chrome auto translates the admin page

= 5.2.2 (21-July-2020) =
* [Critical Bugfix](https://wordpress.org/support/topic/slow-website-after-update-to-5-2-1/): Fixes plugin upgrade process. This in turn fixes a page slow down issue introduced for some sites with v5.2.0.

= 5.2.1 (20-July-2020) =
* Enhancement: Don't recommend switching database engine unless necessary for fulltext indexes.

= 5.2.0 (20-July-2020) =
* Enhancement: Adds support for InnoDb fulltext search! One of our most requested features. Fulltext indexing is supported in MyISAM or InnoDB with MySQL 5.6.4 and higher.
* Enhancement: Optimized database queries, resulting in ~2x speed improvement in some queries.

= 5.1.9 (25-June-2020) =
* [Bugfix](https://wordpress.org/support/topic/wont-let-me-deactivate-or-delete-plugin/) Fixes deactivation survey plugin conflict

= 5.1.8 (17-June-2020) =
* [Bugfix](https://wordpress.org/support/topic/upgrade-issue-29/) Add missing file to previous release!

= 5.1.7 (17-June-2020) =
* [Bugfix](https://wordpress.org/support/topic/cant-use-indexing/) Double-check database indexes exist before creating them
* Bugfix: Titles were only being considered if body text was also considered for the relatedness algorithm. This update fixes the logic to work where only titles are set to be considered.
* [Bugfix](https://wordpress.org/support/topic/wont-let-me-deactivate-or-delete-plugin/): Do not dismiss deactivation modal when clicking outside it

= 5.1.6 (15-May-2020) =
* [Bugfix](https://wordpress.org/support/topic/403-when-saving-changes/): 403 error when saving changes resolved
* Enhancement: Feedback form enhancements

= 5.1.5 (11-May-2020) =
* Bugfix: Use correct deactivation reason code

= 5.1.4 (11-May-2020) =
* Require PHP 5.3 as a bare minimum for compatibility features
* New: Display optional feedback form on plugin deactivation

= 5.1.3 (07-April-2020) =
* Support for WordPress 5.4+
* Enhancement: Switch over to secure (https) endpoints

= 5.1.2 (06-November-2019) =
* Support for WordPress 5.3+

= 5.1.1 (23-September-2019) =
* Enhancement: Review Notice updates + instructions on how to disable it programmatically

= 5.1.0 (10-July-2019) =
* [Bugfix](https://wordpress.org/support/topic/yarpp-broken-in-gutenberg-editor/): Related Posts metabox did not load within Gutenberg Editor
* Bugfix: Fixed 'Deactivate YARPP Pro' button, including moving functionality to proper WP AJAX functions
* Enhancement: Related Post thumbnails should not be 'pinnable' to Pinterest
* Enhancement: Review Notice
* Enhancement: Modernized Editor Metabox design
* Enhancement: Added 'Refresh' button to Editor Metabox

= 5.0.1 (08-July-2019) =
* [Bugfix](https://wordpress.org/support/topic/styles_thumbnails-css-php-invalid-value/): Fixed invalid CSS rule
* Enhancement: Set Cache headers for CSS file

= 5.0.0 (01-July-2019) =
* [Bugfix](https://wordpress.org/support/topic/php-notice-get_currentuserinfo-is-deprecated-since-version-4-5-0/): Fixed warning from using get_currentuserinfo()

= 4.6 (01-July-2019) =
* [Bugfix](https://wordpress.org/support/topic/plugin-flagged-by-wordfence/): Removed links from Plugin Readme to resolve WordFence false positive.

= 4.5 (18-May-2019) =
After a bit of a hiatus, we're back! The plugin had been delisted due to a minor [email address issue](https://make.wordpress.org/plugins/2018/10/22/reminder-plugins-are-closed-if-emails-bounce/). This has been resolved with this release and the plugin has been reinstated. We consider this to be a big step, and yes, after a 6-month hiatus of not being in the plugin directory.🙂

The plugin is also no longer maintained by Adknowledge. A group of us with **deep expertise in Content Recommendations** and **10+ years experience with maintaining very popular plugins** have very recently taken Yarpp over from Adknowlege. More on this will be shared very soon.

After a break of many years, the plugin is 100% supported now that the baton has been passed on. A larger update (hand-in-hand with proper communication) is being carefully worked on with a focus on a host of bug fixes and compatibility updates which will be available once it is properly tested, ready and updates properly communicated. Stay tuned.❤️

= 4.4 =
* Discontinuing the YARPP Pro service

= 4.3.6 =
* Removed new file

= 4.3.5 =
* Fix 'unable to update'

= 4.3.4 =
* Don't allow new YARPP Pro signups
* Discontinuing the YARPP Pro service as of 01/31/2017

= 4.3.3 =
* Fix 'Undefined index' in YARPP_Cache.php (credit to Derrick Hammer/@pcfreak30)

= 4.3.2 =
* Fix ksort error in YARPP_Cache.php (credit to Derrick Hammer/@pcfreak30)
* Discontinuing the YARPP Pro service as of 12/31/2016

= 4.3.1 =
* Tested on WordPress 4.4.
* Fix $lang missing error in YARPP_Cache.php
* Fix WP_Widget deprecation PHP error

= 4.2.5 =
* Tested on WordPress 4.2.2.
* Styling fix: User can now override all YARPP CSS styling

= 4.2.4 =
* Bugfix: Missing internal version number update

= 4.2.3 =
* Tested on WordPress 4.0.
* Bugfix: Made logo image url in css scheme-less (fix http/https)

= 4.2.2 =
* Tested on WordPress 3.9.1.
* Bugfix: Duplicate related post links removed.
* Bugfix: Removed extra &lt;br&gt; at the end of post content.

= 4.2.1 =
* Bugfix: Resolved the issue related to "Warning: in_array() expects parameter 2 to be array, null given".

= 4.2 =
* Tested on WordPress 3.9.
* YARPP Basic and YARPP Pro can now be used simultaneously, rather than being mutually exclusive.
* Added a YARPP Pro option to the YARPP sidebar widget.
* Enlarged "Display Options" panel textbox for increased usability.
* Updated YARPP Pro script to allow for future enhancements.
* Updated MyISAM check notice message to explain its impact on "Pages."
* Added descriptive text to explain new enhancements.

= 4.1.2 =
* Tested on Wordpress 3.8.
* Added field boxes with API Key information to Domain page.
* MyISAM check notice message was updated to better express what it does and a feedback message was added in case the fulltext index creation fails.
* Bugfix: MyISAM check overwrite was broken since 4.1.x release.
* Bugfix: PHP title not defined warning when creating new cache entry.
* Bugfix: Uninstall script now deletes options with empty, false or 0 values.
* Bugfix: Scrollbar functionality on YARPP Pro Domain Settings page.

= 4.1.1 =
* Bugfix: Incompatibility with PHP < 5.3.x

= 4.1 =
* Added optional YARPP Pro enhancements:
    + Revenue-generating ad content interspersed with related posts display
    + Detailed reports for Clicks, Click-Through Rate, and Revenue
    + Ability to display related content from multiple domains
    + UI settings for related content display, including thumbnail size and layout
    + Refined "relatedness" algorithm is now independent of MyISAM or InnoDB engines
    + "Relatedness" is calculated and stored externally, minimizing server load
* Based on user feedback, the "Screen Options" section displays all YARPP options panels by default.

= 4.0.8 =
* The recent 4.0.7 YARPP update included a settings modification to opt in users to our tracking pixel by default. By doing so, our intent was to use this expanded information to better understand the geographic reach of the popular plugin. We have been made aware that this change infringed upon the WordPress guidelines. We apologize for the issue and have remedied the situation in update 4.0.8. Going forward, we would really appreciate your input to help us continue to improve the product. We are primarily looking for country, domain, and date installed information. Please help us make YARPP better by opting in to this information and by filling out our quick, [5 question survey](http://www.surveymonkey.com/s/Z278L88). Thank you.

= 4.0.7 =
* [Bugfix](https://wordpress.org/support/topic/orderby-error): Now more robust against certain custom options.
* Updated plugin de-activate/delete functionality to drop all tables. Prior to fix some legacy tables remained which required manual deletion in wp_options from phpmyadmin.
* Updated FAQs section.
* Update to YARPP's data collection terms and conditions.
* Added Macedonian (`mk_MK`) localization by WPdiscounts.

= 4.0.6 =
* YARPP's automatic display will not run on posts which include the HTML comment `<!--noyarpp-->` [by request](https://wordpress.org/support/topic/disabling-yarrp-on-specific-pages).
* More robust activation handling, particularly when network-activated.
* Improved handling of exceptions, for example when fulltext indexes cannot be created or non-MyISAM tables are used.
* YARPP no longer triggers the generation of YARPP-thumbnail-sized images (120x120) when YARPP thumbnails are not used.
	* If you are using YARPP programmatically and using the thumbnails view and having troubles with YARPP's thumbnail size being registered, a manual control to force image size registration has been added to the [YARPP Experiments](https://wordpress.org/extend/plugins/yarpp-experiments/) plugin.
* [Bugfix](https://wordpress.org/support/topic/yarpp_related_exist-and-begin_yarpp_time-error): calls to `yarpp_related_exist()` type functions were causing errors.
* Bundled `yarpp-template-wpml.php` is now called `yarpp-template-multilingual.php`, following [discussion with the author of the Polylang plugin](https://wordpress.org/support/topic/better-integration-of-yarpp-and-polylang).
* Localizations
	* Added Estonian (`est_EST`) by journal24.info
	* Added Gujrati (`gu_IN`) by Vikas Arora of wiznicworld.com

= 4.0.5 =
* [Bugfix](https://wordpress.org/support/topic/bug-in-upgrading-from-yarp-3_5_2b2): Some upgrade code would try to access the global $yarpp before it was properly initialized
* Added experimental graph data method to the YARPP table cache class

= 4.0.4 =
* [Bugfix](https://wordpress.org/support/topic/yarp-403-breaks-paginated-posts): 4.0.3 broke some paginated post displays
* [Bugfix](https://wordpress.org/support/topic/custom-post-type-support-on-widget): custom post type support was not working properly in widget displays
* Bugfix: widget control JS was not working right when first adding a widget
* Added ability to set widget-specific heading for the thumbnails view, [by request](https://wordpress.org/support/topic/yarpp-on-pages-in-sidebar-even-when-turned-off-in-settings)
* Added the filter `yarpp_results`
* Localizations:
	* Updated French localization and stopword list
	* Added Slovenian (`sl_SI`) localization by [Silvo Katalenić](https://twitter.com/silvoslaf)
* Bugfix: forces the DB Cache Reloaded (Fix) plugins to flush when necessary

= 4.0.3 =
* Bugfix: on sites where custom templates are not available, the "thumbnails" display option would get reset when visiting the YARPP settings page
* [Bugfix](https://wordpress.org/support/topic/yarpp-css-is-delayed-or-doesnt-load): the CSS for YARPP's thumbnails display would load at the foot of the page, and therefore would cause some style-flashing. This is fixed for automatic includes, but not for widgets or manual calls.
* Restoration of the `$post` global after YARPP is now more robust. Fixes the display of incorrect metadata on some complex themes.
* YARPP template files no longer recognize `Template Name` fields in their headers, instead using `YARPP Template`. This is to avoid confusion with regular page templates.
* Added "Related Posts" meta box to other "auto display" post types
* Updated Polish localization

= 4.0.2 =
* [Bugfix](https://wordpress.org/support/topic/yarpp-doesnt-update-suggestions-with-older-posts): cache should be cleared when the "show only previous posts?" option is changed
* [Bugfix](https://wordpress.org/support/topic/no-default-image-showing?replies=4): In the thumbnail display, sometimes the default image was not displayed, even though no post thumbnail was available.
* Localization updates
	* Updated Polish, Japanese, Hebrew localizations
	* Better right-to-left layout support

= 4.0.1 =
* Improvements to thumbnail handling
	* See new FAQ entry for practical details
	* Thumbnail size can be specified programmatically (see FAQ)
	* YARPP now registers its thumbnail size properly as `yarpp-thumbnail`
	* Fixed a typo and simplified an item in the dynamic `styles-thumbnails.php` styles
	* Code to generate thumbnails of appropriate size on the fly has been added, but is turned off by default for performance reasons (see FAQ)
* Bugfix: a class of `yarpp-related-` with a stray hyphen was sometimes being produced. Now fixed so it produces `yarpp-related`.
* [Bugfix](https://wordpress.org/support/topic/bug-in-sql-function-in-yarpp_cache): `term_relationships` table was being joined when unnecessary
* [Bugfix](https://wordpress.org/support/topic/no-option-to-add-widget-title-in-theme-using-hybrid-core-framework): widget options would not display if custom templates were not available
* Bugfix: some transients expired too soon if object caching was used
* The `yarpp_map_post_types` filter now also applies to feeds and takes an extra argument to know whether the context is `website` or `rss`.

= 4.0 =
* New thumbnail template option!
	* No PHP required—just visit the settings page
	* Edit your theme's CSS file to modify the styling
* Auto display settings changes:
	* Easily choose which post types you want related posts to display on
	* Added an "also display in archives" option
* [Bugfix](https://wordpress.org/support/topic/related-posts-disappearing-cache-issue): uses of `related_posts_exist()` and `get_related()` without explicit reference ID parameter would incorrectly return no related posts.
* Changes to the output HTML:
	* All YARPP output is now wrapped in a `div` with class `yarpp-related`, `yarpp-related-widget`, or `yarpp-related-rss` as appropriate ([by request](https://wordpress.org/support/topic/adding-a-main-div-to-default-template)). If there are no results, a `yarpp-related-none` class is added.
	* The "related posts brought to you by YARPP" text is only added if there were results.
* Refinements to settings UI:
	* A new design for the template chooser
	* Example code display is now hidden by default; turn them back on from the "screen options" tab.
	* A new "copy templates" button allows one-button installation of bundled templates into the current theme, if filesystem permissions make it possible.
	* Header information in YARPP custom templates are now displayed to users in the settings UI. Available fields are `YARPP Template`, `Description`, `Author`, `Author URI`, in the same format as plugin and theme file headers. See bundled templates for examples.
* Code cleanup:
	* Settings screen UI have been rewritten to use `div`s rather than `table`s!
	* Inline help in settings screen now use WordPress pointers
	* Removed keyword cache table, as it does not ctually improve performance much and the overhead of an additional table is not worth it.
* Default option changes:
	* Default result count is now 4
	* Default match threshold is now 4
	* Default for "before related entries" heading uses `h3` instead of `p`
* Added `yarpp_map_post_types` filter to programmatically specify what post types should be looked at for automatic displays
* Added option to send YARPP setting and usage information back to YARPP (off by default). This information will be used to make more informed decisions about future YARPP development. More info available in the settings.

= 3.5.6 =
* Typo fix for postmeta cache
* Added Traditional Chinese (Taiwan, `zh_TW`) localization by Pseric

= 3.5.5 =
* Quick bugfix for how admin screen code was loaded in in WordPress < 3.3.

= 3.5.4 =
* New Help tab, which displays help text from the readme.
* Retina icons! Now served faster, in sprite form.
* Added Croatian (`hr`) localization by gocroatia.com
* Cleanup:
	* Bugfix: stopwords would not be loaded if WPLANG is defined but blank.
	* Added new `stats` method to `YARPP_Cache_*` objects.
	* Load meta boxes on `screen_option` hook. Improves performance on admin pages.
	* Changed default option of "show only previous posts" to `false` and removed FAQ text, as it no longer improves performance much.

= 3.5.3 =
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-no-related-posts-7): Fixed a common cause of "no related posts"!
* Better post revision handling
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-database-errors-upon-activation): setup wasn't automatic for network activations.
* Code cleanup:
	* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-latin1-instead-of-utf-8): tables should be created using WordPress charset settings
	* YARPP_Cache_*::update methods are now protected
	* Simplified some post status transition handling
	* Ensure that `delete_post` hook receives relevant post ID information
	* Various functions now refer to the `enforce` method which will activate if it's a new install, or else upgrade if necessary. (Part of the fix for the network activation above.)

= 3.5.2 =
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-bug-found-with-solution): fix an unfortunate typo which caused "no related posts" on many environments with non-MyISAM tables
* Fixed a bug where related posts would not be recomputed on post update, on environments using the `table` YARPP cache method and a persistent object caching system, like W3 Total Cache or memcached
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-351-error-call-to-a-member-function): reference to `get_post_types()` failed in ajax display
* Fixed a bug where some RSS display options were not being obeyed
* Fixed a bug where the "automatic display" was being displayed on some custom post types without any control.
* Localizations:
	* Added Czech (`cs_CZ`) localization by Zdenek Hejl
	* Added Serbian (`sr_RS`) by Zarko Zivkovic
	* Fixed bug in Dutch localization
* Clarified readme to require WordPress 3.1
* Code cleanup:
	* PHP 5.3+: replaced an instance of `ereg_replace`
	* Removed warning on settings save
	* Sometimes [a warning]((https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-warning-invalid-argument-supplied-for-foreach)) was printed on upgrade from YARPP < 3.4.4
	* Fixed [PHP warning](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-php-warning) when no taxonomies are considered
	* No longer using `clear_pre` function which has been deprecated since WordPress 3.4.

= 3.5.1 =
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-catchable-fatal-error-object-of-class-stdclass-could-not-be-converted-to-string): change `$yarpp->get_post_types()` to return array of names by default
* Ensure that all supported post types are used when "display results from all post types" is set
= 3.5 =
* New public YARPP query API, which supports custom post types
	* Documentation in the "other notes" section of the readme
	* Changed format of `weight`, `template`, `recent` parameters in options and in optional args
* Further main query optimization:
	* What's cooler than joining four tables? Joining two.
	* Exclude now simply uses `term_taxonomy_id`s instead of `term_id`s
* Bugfix: "related posts" preview metabox was not always working properly
* Changes to the `related_*()` and `yarpp_related()` function signatures.
* Added "consider with extra weight" to taxonomy criteria as well
* Code cleanup:
	* Don't clear the cache when it's already empty
	* `protect` the `sql` method as it shouldn't be `public`
	* Further use of utility functions from 3.1 like `wp_list_pluck()`
	* New constant, `YARPP_EXTRA_WEIGHT` to define the "extra weight." By default, it's 3.
* Localizations:
	* Added Slovak (`sk_SK`) localization by Forex
	* Added Romanian (`ro_RO`) localization by Uhren Shop
	* Updated `it_IT`, `ko_KR`, `fr_FR`, `sv_SE`, `ja` localizations

= 3.4.3 =
* Bugfix: keywords were not getting cleared on post update, meaning new posts (which start blank) were not getting useful title + body keyword matches. This often resulted in "no related posts" for new posts.
* Postmeta cache: make sure to clear keyword cache on flush too
* Make welcome pointer more robust
* More custom post type support infrastructure
* Updated Turkish localization by Barış Ünver

= 3.4.2 =
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-not-working-version-341-and-custom-template): 3.4 and 3.4.1 assumed existence of `wp_posts` table.
* Fix typo in `yarpp-template-random.php` example template file
* Improve compatibility with DB Cache Reloaded plugin which doesn't properly implement `set_charset` method.

= 3.4.1 =
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-34-images-problem-using-template): restore `global $post` access to custom templates
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-some-clarification-assistance) for missing `join_filter` on bypass cache
* Bugfixes to query changes:
	* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-34-images-problem-using-template/page/2?replies=36#post-2498791): Shared taxonomy terms were not counted correctly
	* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-use-2-times-related_posts-in-the-singlephp-longer-works): exclusion was not working
* [Bugfix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-34-images-problem-using-template/page/2?replies=36#post-2498791): "disallow" terms were not being displayed for custom taxonomies.
* Add defaults for the `post_type` arg
* Strengthen default post ID values for `related_*` functions
* Added nonce to cache flushing. If you would like to manually flush the cache now, you must use the [YARPP Experiments](https://wordpress.org/extend/plugins/yarpp-experiments/) plugin.
* Updated `sv_SE`, `ko_KR`, `fr_FR` localizations

= 3.4 =
* Major optimizations to the main related posts query, in particular with regard to taxonomy lookups
	* Performance improvements on pages with "no related posts"
* Now can consider custom taxonomies (of posts and pages), in addition to tags and cateogories! Custom taxonomies can also be used to exclude certain content from The Pool.
* Add welcome message, inviting users to check out the settings page
* [Bug fix](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-version-333-breaks-templates-in-widget): Custom templates could not be used in widget display
* Significant code cleanup
	* Move many internal functions into a global object `$yarpp` of class `YARPP`; references to the global `$yarpp_cache` should now be to global `$yarpp->cache`
	* Created the "bypass" cache engine which is used when custom arguments are specified.
		* Switch to bypass cache for demos
	* Now only clears cache on post update, and only computes results for actual posts, not revisions (thanks to Andrei Mikhaylov)
	* Removed the many different options entries, replacing them with a single `yarpp` option (except `yarpp_version`)
	* Fixed issues with display options field data escaping and slashing once and for all
	* Streamlined keyword storage in `YARPP_Cache_Postmeta`
	* Create `YARPP_Cache` abstract class
	* Updated minor bug for computing how many results should be cached
	* Adding some filters: yarpp_settings_save, yarpp_blacklist, yarpp_blackmethods, yarpp_keywords_overused_words, yarpp_title_keywords, yarpp_body_keywords, yarpp_extract_keywords
	* New systematic use of YARPP_ constants to communicate cache status
	* Use `get_terms` to load terms
* Get lazy and embrace asynchronicity:
	* Implement lazy/infinite scrolling for the "disallow tags" and "disallow categories," so the YARPP settings screen doesn't lock up the browser for sites which have a crazy number or tags or categories
	* Don't compute related posts for the metabox on the edit screen; display them via ajax instead
	* Only clear cache on post save, not recompute
* Added `yarpp_get_related()` function can be used similar to `get_posts()`
* Support for [YARPP Experiments](https://wordpress.org/extend/plugins/yarpp-experiments/).
* Fix formatting of the Related Posts meta box
* Localizations
	* Updated `it_IT` localization
	* Added Portuguese stopwords by Leandro Coelho Logística Descomplicada

= 3.3.3 =
* [Bug fix](https://wordpress.org/support/topic/no-related-posts-1): a fix for keyword computation for pages; should improve results on pages. May require flushing of cache: see FAQ for instructions.
* Init YARPP on the `init` action, [for compatibility with WPML](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-load-sequence-yarpp-starts-before-the-wordpress-init-completes)
* Updated Polish, Italian, and Japanese localizations; added Dutch stopwords by Paul Kessels
* Code cleanup:
	* Minor speedup to unnecessarily slow i18n code
	* Fixed fatal error in postmeta keyword caching code
	* Fewer `glob`s
	* [Bug fix](https://wordpress.org/support/topic/the-problem-when-publish-a-post): ignore empty `blog_charset`s

= 3.3.2 =
* [Bugfix](https://wordpress.org/support/topic/missing-translate-strings): removed an unlocalized string
* Bugfix for users of WordPress 3.0.x.

= 3.3.1 =
* Quick bugfix to [relatedness options panel bug](https://wordpress.org/support/topic/relatedness-options-for-titles-and-bodies-cant-be-changed)

= 3.3 =
* Pretty major rewrite to the options page for extensibility and screen options support
	* By default, the options screen now only show the display options. "The Pool" and "Relatedness" options can be shown in the screen options tab in the top right corner of the screen.
	* Removed the "reset options" button, because it wasn't actually doing anything.
* Rebuilt the new version notice to actually have a link which triggers the WordPress plugin updater, at least for new full versions
* Changed default "relatedness" settings to not consider categories, to improve performance
* Added BlogGlue partnership module
* Localizations
	* Quick fix to Czech word list file name
	* Updated Italian localization (`it_IT`)
	* Added Hungarian (`hu_HU`) by daSSad
	* Added Kazakh (`kk_KZ`) by DachaDecor
	* Added Irish (`gb_IR`) by Ray Gren

= 3.2.2 =
* Now [ignores soft hyphens](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-french-overused-words) in keyword construction
* Minor fix for "cross-relate posts and pages" option and more accurate `related_*()` results across post types
* Localization updates:
	* Updated `de_DE` German localization files
	* Fixed an encoding issue in the `pt_PT` Portuguese localization files
	* Added `es_ES` Spanish localization by Rene of WordPress Webshop
	* Added `ge_KA` Georgian by Kasia Ciszewski of Find My Hosting
	* Added Czech (`cs_CZ`) overused words list [by berniecz](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-french-overused-words)

= 3.2.1 =
* Bugfix: [Duplicate results shown for some users](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-yarpp-post-duplicate-related-articles)
* Bugfix: [With PHP4, the "related posts" would simply show the current post](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-yarpp-showing-same-post)
	* This was due to an issue with [object references in PHP4](http://www.obdev.at/developers/articles/00002.html). What a pain!
	* A big thanks to Brendon Held of inMotion Graphics for being incredibly patient and letting me try out different diagnostics on his server.
* Better handling of [`post_status` transitions](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-changed-post-to-draft-still-showing-up-as-related-to-other-posts).
* Bugfix: [the widget was not working on pages](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-showing-yarp-widget-in-pages-and-subpages)
* Added overused words list for French, thanks to [saymonz](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-french-overused-words)
* Minor code cleanup:
	* Fixed [a bug in `yarpp_related_exists()`](https://wordpress.org/support/topic/plugin-yet-another-related-posts-plugin-fatal-error-call-to-undefined-method-yarpp_cache_tablesstart_yarpp_time)
	* Removed legacy code for gracefully upgrading from YARPP versions < 1.5 and working with WordPress versions < 2.8.
	* Cleanup of `yarpp_upgrade_check()` calling
	* Cleanup of `yarpp_version_json()`, including caching and minor security fix
	* Eliminated a couple globals
	* Cleaned up some edge case causes for "unexpected output" on plugin activation
	* Removed WP Help Center badge, as they are closing

= 3.2 =
* Better caching performance:
  * Previously, the cache would never actually build up properly. This is now fixed. Thanks to Artefact for pointing this out.
  * The appropriate caches are cleared after posts are deleted ([#1245](http://plugins.trac.wordpress.org/ticket/1245)).
  * Caching is no longer performed while batch-importing posts.
* A new object-based abstraction for the caching system. YARPP by default uses custom database tables (same behavior as 3.1.x), but you now have an option to use the `postmeta` table instead. To use `postmeta` caching, add `define('YARPP_CACHE_TYPE', 'postmeta');` to your `wp-config.php` file.<!--YARPP no longer uses custom tables! Both custom tables (`yarpp_related_cache` and `yarpp_keywords_cache`) are automatically removed if you have them. WordPress Post Meta is used instead for caching.-->
* Localizations:
	* added Bulgarian (`bg_BG`) by Flash Gallery
	* added Farsi/Persian (`fa_IR`) by Moshen Derakhshan
	* added Bahasa Indonesia (`id_ID`) by Hendry Lee of Kelayang
	* added Norwegian (`nb_NO`) by Tom Arne Sundtjønn
	* added Portuguese (`pt_PT`) by Stefan Mueller
	* updated Lithuanian (`lt_LT`) by Mantas Malcius
* Added WordPress HelpCenter widget for quick access to professional support.
* Some code cleanup (bug [#1246](http://plugins.trac.wordpress.org/ticket/1246))
* No longer supporting WordPress versions before 3.0, not because I suddenly started using something that requires 3.0, but in order to simplify testing.

= 3.1.9 =
* Added Standard Arabic localization (`ar`) by led
* The Related Posts Widget now can also use custom templates. ([#1143](http://plugins.trac.wordpress.org/ticket/1143))
* Fixed a [conflict with the Magazine Premium theme](https://wordpress.org/support/topic/419174)
* Fixes a WordPress warning of "unexpected output" on plugin installation.
* Fixes a PHP warning message regarding `array_key`.
* Fixed a strict WordPress warning about capabilities.
* Bugfix: widget now obeys cross-relate posts and pages option
* For WPMU + Multisite users, reverted 3.1.8's `get_site_option`s to `get_option`s, so that individual site options can be maintained.

= 3.1.8 =
* Added Turkish localization (`tr_TR`)
* Bugfix: related pages and "cross-relate posts and pages" functionality is now working again.
* Some bare minimum changes for Multisite (WPMU) support.
* Reimplemented the old "show only previous posts" option. May improve performance for sites with frequent new posts, as there is then no longer a need to recompute the previous posts' related posts set, as it cannot include the new post anyway.
* Minor bugfix to threshold limiting.
* Minor fix which may help reduce [`strip_tags()` errors](https://wordpress.org/support/topic/353588).
* Updated FAQ.
* Code cleanup.

= 3.1.7 =
* Added Egyptian Arabic localization (`ar_EG`)
* Changed default option for automatic display of related posts in feeds to OFF. May improve performance for new users who use the default settings.
* "Use template" options are now disabled when templates are not found. Other minor tweaks to options screen.
* 3.1.7 has been lightly tested with WP 3.0. Multisite (WPMU) compatibility has not been tested yet.

= 3.1.6 =
* Added Latvian localization (`lv_LV`)
* Added a template which displays post thumbnails; requires WordPress 2.9 and a theme which has post thumbnail support

= 3.1.5 =
* Quick bugfix to new widget template (removed extra quote).

= 3.1.4 =
* Improved widget code
* Localization improvements - descriptions can now be localized
* [Compatibility with PageBar](https://wordpress.org/support/topic/346714) - thanks to Latz for the patch!
* Bugfix: [`related_posts_exist` was giving incorrect values](https://wordpress.org/support/topic/362347)
* Bugfix: [SQL error for setups with blank DB_CHARSET](https://wordpress.org/support/topic/358757)

= 3.1.3 =
* Performance improvements:
  * Turning off cache expiration, made possible by smarter caching system of 3.1 - should improve caching database performance over time.
  * [updated primary key for cache](https://wordpress.org/support/topic/345070) by Pinoy.ca - improves client-side pageload times.
* Code cleanup
  * Rewrote `include` and `require` paths
* Bugfix: localizations were not working with WordPress 2.9 ([a CodeStyling Localizations bug](https://wordpress.org/support/topic/343389))
* Bugfix: [redundant entries for "unrelatedness" were being inserted](https://wordpress.org/support/topic/344859)
* Bugfix: [`yarpp_clear_cache` bug on empty input](https://wordpress.org/support/topic/343001)
* Version checking code no longer uses Snoopy.
* New localization: Hindi by Outshine Solutions

= 3.1.2 =
* Bugfix: [saving posts would sometimes timeout](https://wordpress.org/support/topic/343001)

= 3.1.1 =
* [Possible fix for the "no related posts" issue](https://wordpress.org/support/topic/284209/page/2) by [vkovalcik](https://wordpress.org/support/profile/5032111)
* Bugfix: [slight optimization to keyword function](https://wordpress.org/support/topic/284209/page/2) by [vkovalcik](https://wordpress.org/support/profile/5032111)
* Bugfix: [regex issue with br-stripping](https://wordpress.org/support/topic/323823)

= 3.1 =
* New snazzy options screen
* Smarter, less confusing caching
  * No more manual caching option—"on the fly" caching is always on now.
* Bugfix: [fixed related pages functionality](https://wordpress.org/support/topic/273008)
* Bugfix: [an issue with options saving](https://wordpress.org/support/topic/312637)
* Bugfix: [a slash escaping bug](https://wordpress.org/support/topic/315560)
* Minor fixes:
  * Fixed `yarpp_settings_link` dependency when disabled.
  * Breaks (&lt;br /&gt;) are now stripped out of titles.
  * Added plugin incompatibility info for Pagebar.
  * Faster post saving.

= 3.0.13 =
* Quick immediate bugfix to 3.0.12

= 3.0.12 =
* Yet another DISQUS note... sigh.
* Changed [default markup](https://wordpress.org/support/topic/307890) to be make the output validate better.
* Reformatted the version log in readme.txt
* Added a Settings link to the plugins page
* Some initial WPML support:
  * Tweaked a SQL query so that it was WPML compatible
  * Added YARPP template to be used with WPML
* Added Hebrew localization

= 3.0.11 =
* Quick fix for `compare_version` code.

= 3.0.10 =
* Added Ukrainian localization
* Incorporated a quick update for the widget display [thanks to doodlebee](https://wordpress.org/support/topic/281575).
* Now properly uses `compare_version` in lieu of old hacky versioning.

= 3.0.9 =
* Added Uzbek, Greek, Cypriot Greek, and Vietnamese localizations
* Further bugfixes for the [pagination issue](https://wordpress.org/support/topic/267350)

= 3.0.8 =
* Bugfix: [a pagination issue](https://wordpress.org/support/topic/267350) (may not be completely fixed yet)
* Bugfix: a quick bugfix for widgets, thanks to Chris Northwood
* Added Korean and Lithuanian localizations
* Bugfix: [when ad-hoc caching was off, the cached status would always say "0% cached" ](https://wordpress.org/support/topic/286395)
* Bugfix: enabled Polish and Italian stopwords and [fixed encoding of Italian stopwords](https://wordpress.org/support/topic/288808).
* Bugfix: `is_single` and other such flags are now set properly within the related posts Loop (as a result, now [compatible with WP Greet Box](https://wordpress.org/support/topic/288230))
* Confirmed compatibility with 2.8.2
* Bugfix: [the Related Posts metabox now respects the Screen Options](https://wordpress.org/support/topic/289290)

= 3.0.7 =
* Bugfix: additional bugfix for widgets.
* Reinstating excerpt length by number of words (was switched to letters in 3.0.6 without accompanying documentation)
* Localizations:
  * Updated Italian
  * Added Belarussian by Fat Cow
* Confirmed compatibility with 2.8.1

= 3.0.6 =
* Bugfix: [updated excerpting to use `wp_html_excerpt`](https://wordpress.org/support/topic/268934) (for WP 2.5+)
* Bugfix: [fixed broken widget display](https://wordpress.org/support/topic/276031)
* Added Russian (`ru_RU`) localization by Marat Latypov
* Confirmed 2.8 compatibility
* Added note on [incompatibility with SEO Pager plugin](https://wordpress.org/support/topic/267966)

= 3.0.5 =
* Added link to manual SQL setup information [by request](https://wordpress.org/support/topic/266752)
* Added Portuguese localization
* Updated info on "on the fly" caching - it is *strongly recommended* for larger blogs.
* Updated "incomplete cache" warning message so it is only displayed when the "on the fly" option is off.

= 3.0.4 =
* A fix to the version checking in the options page - now uses Snoopy
* Adding Dutch localization

= 3.0.3 =
* Reinstated the 3.0.1 bugfix for includes
* Bugfix: Fixed encoding issue in keyword caching algorithm
* Bugfix: One SQL query assumed `wp_` prefix on tables
* Added Polish localization
* Added note on DISQUS in readme
* Making some extra strings localizable
* Bugfix: [a problem with the Italian localization](https://wordpress.org/support/topic/265952)

= 3.0.2 =
* Bugfix: [Templating wasn't working with child templates.](https://wordpress.org/support/topic/265515)
* Bugfix: In some situations, [SQL errors were printed in the AJAX preview displays](https://wordpress.org/support/topic/265728).

= 3.0.1 =
* Bugfix: In some situations before YARPP options were updated, an `include` PHP error was displayed.

= 3.0 =
* Major new release!
* Caching for better SQL performance
* A new templating feature for custom related posts displays
* Cleaned up options page
* New and updated localizations

= 2.1.6 =
* Versioning bugfix - same as 2.1.5

= 2.1.5 =
* Bugfix: In certain scenarios, [related posts would be displayed in RSS feeds even when that option was off](https://wordpress.org/support/topic/216145)
* Bugfix: The `related_*()` functions were missing the `echo` parameter
* Some localization bugfixes
* Localizations:
	* Japanese (`ja`) by Michael Yoshitaka Erlewine

= 2.1.4 =
* Bugfix: [Settings' sumbmit button took you to PayPal](https://wordpress.org/support/topic/214090)
* Bugfix: Fixed [keyword algorithm for users without `mbstring`](https://wordpress.org/support/topic/216420)
* Bugfix: `title` attributes were not properly escaped
* Bugfix: [keywords did not filter tags](https://wordpress.org/support/topic/218211). (This bugfix may vastly improve "relatedness" on some blogs.)
* Localizations:
	* Simplified Chinese (`zh_CN`) by Jor Wang (mail at jorwang dot com) of jorwang.com
	* German (`de_DE`) by Michael Kalina of 3th.be
* The "show excerpt" option now shows the first `n` words of the excerpt, rather than the content ([by request](https://wordpress.org/support/topic/212577))
* Added an `echo` parameter to the `related_*()` functions, with default value of `true`. If `false`, the function will simply return the output.
* Added support for the [AllWebMenus Pro](https://wordpress.org/extend/plugins/allwebmenus-wordpress-menu-plugin/) plugin
* Further internationalization:
	* the donate button! ^^
	* overused words lists ([by request](https://wordpress.org/support/topic/159359))), with a German word list.

= 2.1.3 =
* Bugfix: Turned off [the experimental caching](https://wordpress.org/support/topic/216194#post-894440) which shouldn't have been on in this release...
* Bugfix: an issue with the [keywords algorithm for non-ASCII characters](https://wordpress.org/support/topic/216078)

= 2.1.2 =
* Bugfix: MyISAM override handling bug

= 2.1.1 =
* Bugfix: keywords with forward slashes (\) could make the main SQL query ill-formed.
* Bugfix: Added an override option for the [false MyISAM warnings](https://wordpress.org/support/topic/211043).
* Preparing for localization! (See note at the bottom of the FAQ.)
* Adding a debug mode--just try adding `&yarpp_debug=1` to your URL's and look at the HTML source.

= 2.1 - The RSS edition! =
* RSS feed support!: the option to automagically show related posts in RSS feeds and to customize their display, [by popular request](https://wordpress.org/support/topic/151766).
* A link to [the Yet Another Related Posts Plugin RSS feed](https://wordpress.org/support/topic/208469).
* Further optimization of the main SQL query in cases where not all of the match criteria (title, body, tags, categories) are chosen.
* A new format for pushing arguments to the `related_posts()` functions.
* Bugfix: [compatibility](https://wordpress.org/support/topic/207286) with the [dzoneZ-Et](https://wordpress.org/extend/plugins/dzonez-et/) and [reddZ-Et](https://wordpress.org/extend/plugins/reddz-et/) plugins.
* Bugfix: `related_*_exist()` functions produced invalid queries
* A warning for `wp_posts` with non-MyISAM engines and semi-compatibility with non-MyISAM setups.
* Bugfix: [a better notice for users of Wordpress < 2.5](http://www.mattcutts.com/blog/wordpress-plugin-related-posts/#comment-131194) regarding the "compare tags" and "compare categories" features.

= 2.0.6 =
* A quick emergency bugfix (In one instance, assumed existence of `wp_posts`)

= 2.0.5 =
* Further optimized algorithm - should be faster on most systems. Good bye [subqueries](http://dev.mysql.com/doc/refman/5.0/en/unnamed-views.html)!
* Bugfix: restored MySQL 4.0 support
* Bugfix: [widgets required the "auto display" option](https://wordpress.org/support/topic/190454)
* Bugfix: sometimes default values were not set properly on (re)activation
* Bugfix: [quotes in HTML tag options would get escaped](https://wordpress.org/support/topic/199139)
* Bugfix: `user_level` was being checked in a deprecated manner
* A helpful little tooltip for the admin-only threshold display

= 2.0.4 - what 2.0 should have been =
* Bugfix: new fulltext query for MySQL 5 compatibility
* Bugfix: updated `apply_filters` to work with WP 2.6

= 2.0.3 =
* Bugfix: [2.0.2 accidentally required some tags or categories to be disabled](https://wordpress.org/support/topic/188745)

= 2.0.2 =
* Versioning bugfix (rerelease of 2.0.1)

= 2.0.1 =
* Bugfix: [`admin_menu` instead of `admin_head`](http://konstruktors.com/blog/wordpress/277-fixing-postpost-and-ozh-absolute-comments-plugins/)
* Bugfix: [a variable scope issue](https://wordpress.org/support/topic/188550) crucial for 2.0 upgrading

= 2.0 =
* New algorithm which considers tags and categories, by frequent request
* Order by score, date, or title, [by request](https://wordpress.org/support/topic/158459)
* Excluding certain tags or categories, [by request](https://wordpress.org/support/topic/161263)
* Sample output displayed in the options screen
* Bugfix: [an excerpt length bug](https://wordpress.org/support/topic/155034?replies=5)
* Bugfix: now compatible with the following plugins:
	- diggZEt
	- WP-Syntax
	- Viper's Video Quicktags
	- WP-CodeBox
	- WP shortcodes

= 1.5.1 =
* Bugfix: standardized directory references to `yet-another-related-posts-plugin`

= 1.5 =
* Simple installation: automatic display of a basic related posts install
* code and variable cleanup
* FAQ in the documentation

= 1.1 =
* Related pages support!
* Also, uses `apply_filters` to apply whatever content text transformation you use (Wikipedia link, Markdown, etc.) before computing similarity.

= 1.0.1 =
* Bugfix: 1.0 assumed you had Markdown installed

= 1.0 =
* Initial upload

== Upgrade Notice ==
= 5.30.3 =
We update YARPP regularly so we can keep making it better for you. Update to the latest version for the latest features and improvements. Thank you for using YARPP!
