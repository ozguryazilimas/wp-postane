<?php
    /* Version
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__VERSION", "1.6.0");

    /* Localhost.
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__LOCALHOST", ($_SERVER["HTTP_HOST"] == "localhost:8080"));
    
    /* Load Unique-User-Key & API Secret
    -----------------------------------------------------------------------------------------*/
    if (file_exists(dirname(__FILE__) . "/key.php")){ require_once(dirname(__FILE__) . "/key.php"); }

    /* Server Address & Remote Address
    -----------------------------------------------------------------------------------------*/
    // To run your tests on a local machine, hardcode your IP here.
    // To find your IP go to http://www.ip-adress.com/
    if (WP_RW__LOCALHOST)
    {
        define("WP_RW__SERVER_ADDR", "123.123.123.123");
        define("WP_RW__CLIENT_ADDR", "123.123.123.123");
    }

    /* Uncomment for debug mode.
    -----------------------------------------------------------------------------------------*/
//    define("WP_RW__DEBUG", "");

    if (defined("WP_RW__DEBUG"))
    {
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors',true);
        ini_set('html_errors', true);                 
    }

    /* For Rating-Widget development mode.
    -----------------------------------------------------------------------------------------*/
    if (file_exists(dirname(__FILE__) . "/_dev_.php")){ require_once(dirname(__FILE__) . "/_dev_.php"); }

    /* General Consts
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__ID", "rating_widget");
    define("WP_RW__DEFAULT_LNG", "en");
    define("WP_RW__ADMIN_MENU_SLUG", "rating-widget");


    define("WP_RW__BLOG_POSTS_ALIGN", "rw_blog_posts_align");
    define("WP_RW__BLOG_POSTS_OPTIONS", "rw_blog_posts_options");

    define("WP_RW__COMMENTS_ALIGN", "rw_comments_align");
    define("WP_RW__COMMENTS_OPTIONS", "rw_comments_options");

    define("WP_RW__PAGES_ALIGN", "rw_pages_align");
    define("WP_RW__PAGES_OPTIONS", "rw_pages_options");

    define("WP_RW__FRONT_POSTS_ALIGN", "rw_front_posts_align");
    define("WP_RW__FRONT_POSTS_OPTIONS", "rw_front_posts_options");

    /* User-Key Options Consts.
    -----------------------------------------------------------------------------------------*/
        define("WP_RW__DB_OPTION_USER_KEY", "rw_user_key");
        define("WP_RW__DB_OPTION_USER_SECRET", "rw_user_secret");

    /* BuddyPress
    -----------------------------------------------------------------------------------------*/
    // BuddyPress plugin core file.
        define("WP_RW__BP_CORE_FILE", "buddypress/bp-loader.php");

        define("WP_RW__ACTIVITY_BLOG_POSTS_ALIGN", "rw_activity_blog_posts_align");
        define("WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS", "rw_activity_blog_posts_options");

        define("WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN", "rw_activity_blog_comments_align");
        define("WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS", "rw_activity_blog_comments_options");
        
        define("WP_RW__ACTIVITY_UPDATES_ALIGN", "rw_activity_updates_align");
        define("WP_RW__ACTIVITY_UPDATES_OPTIONS", "rw_activity_updates_options");

        define("WP_RW__ACTIVITY_COMMENTS_ALIGN", "rw_activity_comments_align");
        define("WP_RW__ACTIVITY_COMMENTS_OPTIONS", "rw_activity_comments_options");
        
    // bbPress component
        /*define("WP_RW__FORUM_TOPICS_ALIGN", "rw_forum_topics_align");
        define("WP_RW__FORUM_TOPICS_OPTIONS", "rw_forum_topics_options");*/

        define("WP_RW__FORUM_POSTS_ALIGN", "rw_forum_posts_align");
        define("WP_RW__FORUM_POSTS_OPTIONS", "rw_forum_posts_options");

        /*define("WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN", "rw_activity_forum_topics_align");
        define("WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS", "rw_activity_forum_topics_options");*/

        define("WP_RW__ACTIVITY_FORUM_POSTS_ALIGN", "rw_activity_forum_posts_align");
        define("WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS", "rw_activity_forum_posts_options");

    // User
        define("WP_RW__USERS_ALIGN", "rw_users_align");
        define("WP_RW__USERS_OPTIONS", "rw_users_options");
    // User accamulated ratings
        // Posts
        define("WP_RW__USERS_POSTS_ALIGN", "rw_users_posts_align");
        define("WP_RW__USERS_POSTS_OPTIONS", "rw_users_posts_options");
        // Pages
        define("WP_RW__USERS_PAGES_ALIGN", "rw_users_pages_align");
        define("WP_RW__USERS_PAGES_OPTIONS", "rw_users_pages_options");
        // Comments
        define("WP_RW__USERS_COMMENTS_ALIGN", "rw_users_comments_align");
        define("WP_RW__USERS_COMMENTS_OPTIONS", "rw_users_comments_options");
        // Activity-Updates
        define("WP_RW__USERS_ACTIVITY_UPDATES_ALIGN", "rw_users_activity_updates_align");
        define("WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS", "rw_users_activity_updates_options");
        // Activity-Comments
        define("WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN", "rw_users_activity_comments_align");
        define("WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS", "rw_users_activity_comments_options");
        // Forum-Posts
        define("WP_RW__USERS_FORUM_POSTS_ALIGN", "rw_users_forum_posts_align");
        define("WP_RW__USERS_FORUM_POSTS_OPTIONS", "rw_users_forum_posts_options");
        
        
    /* Settings
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__SHOW_ON_EXCERPT", "rw_show_on_excerpt");
    define("WP_RW__VISIBILITY_SETTINGS", "rw_visibility_settings");
    define("WP_RW__AVAILABILITY_SETTINGS", "rw_availability_settings");
    define("WP_RW__CATEGORIES_AVAILABILITY_SETTINGS", "rw_categories_availability_settings");

    /* Visibility Options
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__VISIBILITY_ALL_VISIBLE", 0);
    define("WP_RW__VISIBILITY_EXCLUDE", 1);
    define("WP_RW__VISIBILITY_INCLUDE", 2);
    
    /* Availability Options
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__AVAILABILITY_ACTIVE", 0);    // Active for all users.
    define("WP_RW__AVAILABILITY_DISABLED", 1);  // Disabled for logged out users.
    define("WP_RW__AVAILABILITY_HIDDEN", 2);    // Hidden from logged out users.

    /* Advanced Settings
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__FLASH_DEPENDENCY", "rw_flash_dependency");
    define("WP_RW__SHOW_ON_MOBILE", "rw_show_on_mobile");
    define("WP_RW__LOGGER", "rw_logger");

    define("WP_RW__USER_SECONDERY_ID", "00");
    define("WP_RW__POST_SECONDERY_ID", "01");
    define("WP_RW__PAGE_SECONDERY_ID", "02");
    define("WP_RW__COMMENT_SECONDERY_ID", "03");
    define("WP_RW__ACTIVITY_UPDATE_SECONDERY_ID", "04");
    define("WP_RW__ACTIVITY_COMMENT_SECONDERY_ID", "05");
    define("WP_RW__FORUM_POST_SECONDERY_ID", "06");

    /* Reports Consts
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__REPORT_RECORDS_MIN", 10);
    define("WP_RW__REPORT_RECORDS_MAX", 50);
    define("WP_RW__PERIOD_MONTH", 2678400);
    define("WP_RW__DEFAULT_DATE_FORMAT", "Y-n-d");
    define("WP_RW__DEFAULT_TIME_FORMAT", "H:i:s");

    /* Stars Consts
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__DEF_STARS", 5);
    define("WP_RW__MIN_STARS", 1);
    define("WP_RW__MAX_STARS", 20);

    /* Plugin dir and url
    -----------------------------------------------------------------------------------------*/
    define("WP_RW__PLUGIN_DIR", dirname(dirname(__FILE__)));
    define("WP_RW__PLUGIN_URL", plugins_url() . '/' . dirname(dirname(plugin_basename(__FILE__))) . '/');

    /* Rating-Widget URIs
    -----------------------------------------------------------------------------------------*/
    if (!defined("WP_RW__DOMAIN")){ define("WP_RW__DOMAIN", "rating-widget.com"); }
    
    define("WP_RW__HTTPS", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'));
    
    define("WP_RW__ADDRESS", "http://" . WP_RW__DOMAIN);
    
    define("WP_RW__BP_INSTALLED", function_exists("bp_activity_get_specific")); // BuddyPress earlier than v.1.5
    // Moved to rw_init_bp().
    // define("WP_RW__BBP_INSTALLED", (WP_RW__BP_INSTALLED && ("" != get_site_option("bb-config-location", ""))));

    /* Server Address & Remote Address
    -----------------------------------------------------------------------------------------*/
    if (!defined("WP_RW__SERVER_ADDR")){
        define("WP_RW__SERVER_ADDR", $_SERVER["SERVER_ADDR"]);
    }
    if (!defined("WP_RW__CLIENT_ADDR")){
        define("WP_RW__CLIENT_ADDR", $_SERVER["REMOTE_ADDR"]);
    }
?>
