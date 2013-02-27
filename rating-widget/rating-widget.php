<?php
/*
Plugin Name: Rating-Widget Plugin
Plugin URI: http://rating-widget.com
Description: Create and manage Rating-Widget ratings in WordPress.
Version: 1.6.0
Author: Vova Feldman
Author URI: http://il.linkedin.com/in/vovafeldman
License: A "Slug" license name e.g. GPL2
*/

// Load common config.
require_once(dirname(__FILE__) . "/lib/config.common.php");

// Require logger file.
require_once(WP_RW__PLUGIN_DIR . "/lib/logger.php");

/**
* Rating-Widget Plugin Class
* 
* @package Wordpress
* @subpackage Rating-Widget Plugin
* @author Vova Feldman
* @version 1
* @copyright Rating-Widget
*/
class RatingWidgetPlugin
{
    static $errors;
    static $success;
    static $ratings = array();
    
    var $is_admin;
    var $languages;
    var $languages_short;
    var $_visibilityList;
    var $categories_list;
    var $availability_list;
    var $show_on_excerpts_list;
    
    static $VERSION;
    
    public static $WP_RW__BP_INSTALLED = false;
    public static $WP_RW__HIDE_RATINGS = false;
    
    public function InitBuddyPress()
    {
        self::$WP_RW__BP_INSTALLED = true;
        
        // Activity page.
        add_action("bp_has_activities", array(&$this, "rw_before_activity_loop"));
        
        // Forum topic page.
        add_filter("bp_has_topic_posts", array(&$this, "rw_before_forum_loop"));
        
        // User profile page.
        add_action("bp_before_member_header_meta", array(&$this, "rw_display_user_profile_rating"));
        
        if (false !== WP_RW__USER_SECRET && ("" != get_site_option("bb-config-location", "")))
        {
            define("WP_RW__BBP_INSTALLED", true);
        }
    }
    
    
    public function __construct()
    {
        self::$errors = new WP_Error();
        self::$success = new WP_Error();
        
        if (defined("WP_RW__DEBUG") || true === $this->_getOption("WP_RW__LOGGER"))
        {
            // Start logger.
            RWLogger::PowerOn();
        }
        
        $rw_show_on_mobile = $this->_getOption(WP_RW__SHOW_ON_MOBILE);
        
        if (RWLogger::IsOn()){ RWLogger::Log("WP_RW__SHOW_ON_MOBILE", $rw_show_on_mobile); }
        
        if ("false" === $rw_show_on_mobile)
        {
            require_once(WP_RW__PLUGIN_DIR . "/vendors/class.mobile.detect.php");
            $detect = new Mobile_Detect();
            
            $is_mobile = $detect->isMobile();
            
            if (RWLogger::IsOn()){ RWLogger::Log("WP_RW__IS_MOBILE_CLIENT", ($is_mobile ? "true" : "false")); }
            
            if ($is_mobile)
            {
                // Don't show any ratings.
                self::$WP_RW__HIDE_RATINGS = true;
                
                return;
            }
        }
                
        // Load user key.
        $this->load_user_key();
        
        // Load config after keys are loaded.
        require_once(WP_RW__PLUGIN_DIR . "/lib/config.php");

        RWLogger::Log("WP_RW__VERSION", WP_RW__VERSION);
        
        if (RWLogger::IsOn())
        { 
            RWLogger::Log("WP_RW__USER_KEY", WP_RW__USER_KEY);
            RWLogger::Log("WP_RW__USER_SECRET", WP_RW__USER_SECRET);
            RWLogger::Log("WP_RW__DOMAIN", WP_RW__DOMAIN);
            RWLogger::Log("WP_RW__SERVER_ADDR", WP_RW__SERVER_ADDR);
            RWLogger::Log("WP_RW__CLIENT_ADDR", WP_RW__CLIENT_ADDR);
            RWLogger::Log("WP_RW__PLUGIN_DIR", WP_RW__PLUGIN_DIR);
            RWLogger::Log("WP_RW__PLUGIN_URL", WP_RW__PLUGIN_URL);
            //RWLogger::Log("WP_RW__BP_INSTALLED", (WP_RW__BP_INSTALLED ? "true" : "false"));
            //RWLogger::Log("WP_RW__BBP_INSTALLED", (WP_RW__BBP_INSTALLED ? "true" : "false"));
        }

        if (false !== WP_RW__USER_KEY)
        {
            // Posts/Pages/Comments
            add_action("loop_start", array(&$this, "rw_before_loop_start"));
            
            if (WP_RW__BP_INSTALLED)
            {
                // BuddyPress earlier than v.1.5
                $this->InitBuddyPress();
            }
            else
            {
                // BuddyPress v.1.5 and latter.
                add_action("bp_include", array(&$this, "InitBuddyPress"));
            }
            
            // wp_footer call validation.
//            add_action('init', array(&$this, 'test_footer_init'));
            
            // Rating-Widget main javascript load.
            add_action('wp_footer', array(&$this, "rw_attach_rating_js"));
            add_action('wp_footer', array(&$this, "DumpLog"));
        }
        
        add_action('admin_head', array(&$this, "rw_admin_menu_icon_css"));
        add_action('admin_menu', array(&$this, 'admin_menu'));
        add_action('admin_menu', array(&$this, 'AddPostMetaBox')); // Metabox for posts/pages
        add_action('save_post', array(&$this, 'SavePostData'));
        
        /**
        * IMPORTANT: 
        *   All scripts/styles must be enqueued from those actions, 
        *   otherwise it will mass-up the layout of the admin's dashboard
        *   on RTL WP versions.
        */
        add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
        
        require_once(WP_RW__PLUGIN_DIR . "/languages/dir.php");
        $this->languages = $rw_languages;
        $this->languages_short = array_keys($this->languages);
    }
    
    /* Private
    -------------------------------------------------*/
    private static function Urid2Id($pUrid, $pSubLength = 1, $pSubValue = 1)
    {
        return round((double)substr($pUrid, 0, strlen($pUrid) - $pSubLength) - $pSubValue);
    }

    private function _getPostRatingGuid($id = false)
    {
        if (false === $id){ $id = get_the_ID(); }
        $urid = ($id + 1) . "0";
        
        if (RWLogger::IsOn()){
            RWLogger::Log("post-id", $id);
            RWLogger::Log("post-urid", $urid);
        }
        
        return $urid;
    }
    public static function Urid2PostId($pUrid)
    {
        return self::Urid2Id($pUrid);
    }
    
    private function _getCommentRatingGuid($id = false)
    {
        if (false === $id){ $id = get_comment_ID(); }
        $urid = ($id + 1) . "1";

        if (RWLogger::IsOn()){
            RWLogger::Log("comment-id", $id);
            RWLogger::Log("comment-urid", $urid);
        }
        
        return $urid;
    }
    public static function Urid2CommentId($pUrid)
    {
        return self::Urid2Id($pUrid);
    }

    private function _getActivityRatingGuid($id = false)
    {
        if (false === $id){ $id = bp_get_activity_id(); }
        $urid = ($id + 1) . "2";

        if (RWLogger::IsOn()){
            RWLogger::Log("activity-id", $id);
            RWLogger::Log("activity-urid", $urid);
        }
        
        return $urid;
    }

    public static function Urid2ActivityId($pUrid)
    {
        return self::Urid2Id($pUrid);
    }

    private function _getForumPostRatingGuid($id = false)
    {
        if (false === $id){ $id = bp_get_the_topic_post_id(); }
        $urid = ($id + 1) . "3";

        if (RWLogger::IsOn()){
            RWLogger::Log("forum-post-id", $id);
            RWLogger::Log("forum-post-urid", $urid);
        }
        
        return $urid;
    }

    public static function Urid2ForumPostId($pUrid)
    {
        return self::Urid2Id($pUrid);
    }
    
    private function _getUserRatingGuid($secondery_id = WP_RW__USER_SECONDERY_ID, $id = false)
    {
        if (false === $id){ $id = bp_displayed_user_id(); }
        
        $len = strlen($secondery_id);
        $secondery_id = ($len == 0) ? WP_RW__USER_SECONDERY_ID : (($len == 1) ? "0" . $secondery_id : substr($secondery_id, 0, 2));
        $urid = ($id + 1) . $secondery_id . "4";

        if (RWLogger::IsOn()){
            RWLogger::Log("user-id", $id);
            RWLogger::Log("user-secondery-id", $secondery_id);
            RWLogger::Log("user-urid", $urid);
        }
        
        return $urid;
    }
    
    public static function Urid2UserId($pUrid)
    {
        return self::Urid2Id($pUrid, 3);
    }
    
    
    private static $OPTIONS_DEFAULTS = array(
        WP_RW__FRONT_POSTS_ALIGN => '{"ver": "top", "hor": "left"}',
        WP_RW__FRONT_POSTS_OPTIONS => '{"type": "star", "theme": "star_oxygen"}',
        
        WP_RW__BLOG_POSTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
        WP_RW__BLOG_POSTS_OPTIONS => '{"type": "star", "theme": "star_oxygen"}',
        
        WP_RW__COMMENTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
        WP_RW__COMMENTS_OPTIONS => '{"type": "nero", "theme": "thumbs_1"}',
        
        WP_RW__PAGES_ALIGN => '{"ver": "bottom", "hor": "left"}',
        WP_RW__PAGES_OPTIONS => '{"type": "star", "theme": "star_oxygen"}',

        // BuddyPress
            WP_RW__ACTIVITY_BLOG_POSTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS => '{"type": "star", "theme": "star_gray1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 1px 2px 0px 2px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',

            WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',

            WP_RW__ACTIVITY_UPDATES_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__ACTIVITY_UPDATES_OPTIONS => '{"type": "star", "theme": "star_gray1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 1px 2px 0px 2px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',

            WP_RW__ACTIVITY_COMMENTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__ACTIVITY_COMMENTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
        
        // bbPress
            /*WP_RW__FORUM_TOPICS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__FORUM_TOPICS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',*/

            WP_RW__FORUM_POSTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__FORUM_POSTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            
            /*WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',*/

            WP_RW__ACTIVITY_FORUM_POSTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
        // User
            WP_RW__USERS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            // Posts
            WP_RW__USERS_POSTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_POSTS_OPTIONS => '{"type": "star", "theme": "star_gray1", "readOnly": true, "advanced": {"css": {"container": "background: #F4F4F4; padding: 1px 2px 0px 2px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            // Pages
            WP_RW__USERS_PAGES_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_PAGES_OPTIONS => '{"type": "star", "theme": "star_gray1", "readOnly": true, "advanced": {"css": {"container": "background: #F4F4F4; padding: 1px 2px 0px 2px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            // Comments
            WP_RW__USERS_COMMENTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_COMMENTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "readOnly": true, "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            // Activity-Updates
            WP_RW__USERS_ACTIVITY_UPDATES_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS => '{"type": "star", "theme": "star_gray1", "readOnly": true, "advanced": {"css": {"container": "background: #F4F4F4; padding: 1px 2px 0px 2px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            // Avtivity-Comments
            WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "readOnly": true, "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
            // Forum-Posts
            WP_RW__USERS_FORUM_POSTS_ALIGN => '{"ver": "bottom", "hor": "left"}',
            WP_RW__USERS_FORUM_POSTS_OPTIONS => '{"type": "nero", "theme": "thumbs_bp1", "readOnly": true, "advanced": {"css": {"container": "background: #F4F4F4; padding: 4px 8px 1px 8px; margin-bottom: 2px; border-right: 1px solid #DDD; border-bottom: 1px solid #DDD; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px;"}}}',
        
        WP_RW__VISIBILITY_SETTINGS => "{}",
        WP_RW__AVAILABILITY_SETTINGS => '{"activity-update": 1, "activity-comment": 1, "forum-post": 1, "new-forum-post": 1, "user": 1, "user-post": 1, "user-comment": 1, "user-page": 1, "user-activity-update": 1, "user-activity-comment": 1, "user-forum-post": 1}', // By default, disable all activity ratings for un-logged users.
        WP_RW__CATEGORIES_AVAILABILITY_SETTINGS => "{}",
        
        WP_RW__SHOW_ON_EXCERPT => '{"front-post": false, "blog-post": false, "page": false}',
        
        WP_RW__FLASH_DEPENDENCY => "true",
        
        WP_RW__SHOW_ON_MOBILE => "true",
        
        WP_RW__LOGGER => false,
    );
    
    private static $OPTIONS_CACHE = array();
    
    
    public static function _getOption($pOption, $pFlush = false)
    {
        if ($pFlush || !isset(self::$OPTIONS_CACHE[$pOption]))
        {
            $default = isset(self::$OPTIONS_DEFAULTS[$pOption]) ? self::$OPTIONS_DEFAULTS[$pOption] : false;
            self::$OPTIONS_CACHE[$pOption] = get_option($pOption, $default);
        }
        
        return self::$OPTIONS_CACHE[$pOption];
    }
    
    private function _setOption($pOption, $pValue)
    {
        if (!isset(self::$OPTIONS_CACHE[$pOption]) ||
            $pValue != self::$OPTIONS_CACHE[$pOption])
        {
            // Update option.
            update_option($pOption, $pValue);
            
            // Update cache.
            self::$OPTIONS_CACHE[$pOption] = $pValue;
        }
    }

    private function _deleteOption($pOption)
    {
        delete_option($pOption);
        
        if (isset(self::$OPTIONS_DEFAULTS[$pOption])){
            self::$OPTIONS_CACHE[$pOption] = self::$OPTIONS_DEFAULTS[$pOption];
        }else{
            unset(self::$OPTIONS_CACHE[$pOption]);
        }
    }
    
    public static function AddToken(&$pData, $pServerCall = false)
    {
        $timestamp = time();
        $token = self::GenerateToken($timestamp, $pServerCall);
        $pData["timestamp"] = $timestamp;
        $pData["token"] = $token;
        
        return $pData;
    }
    
    public static function RemoteCall($pPage, &$pData)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("RemoteCall", $params, true); }
        
        if (RWLogger::IsOn()){ RWLogger::Log("address", WP_RW__ADDRESS . "/{$pPage}"); }
        
        if (false !== WP_RW__USER_SECRET)
        {
            if (RWLogger::IsOn()){ RWLogger::Log("is secure call", "true"); }
            
            self::AddToken($pData, true);
        }

        if (function_exists('wp_remote_post')) // WP 2.7+
        {
            if (RWLogger::IsOn()){ RWLogger::Log("wp_remote_post", "exist"); }
            
            $rw_ret_obj = wp_remote_post(WP_RW__ADDRESS . "/{$pPage}", array('body' => $pData));
            
            if (is_wp_error($rw_ret_obj))
            {
                self::$errors = $rw_ret_obj;
                
                if (RWLogger::IsOn()){ RWLogger::Log("ret_object", var_export($rw_ret_obj, true)); }
                
                return false;
            }
            
            $rw_ret_obj = wp_remote_retrieve_body($rw_ret_obj);
        }        
        else
        {
            $fp = fsockopen(
                WP_RW__DOMAIN,
                80,
                $err_num,
                $err_str,
                3
            );

            if (!$fp){
                self::$errors->add('connect', __("Can't connect to Rating-Widget.com", WP_RW__ID));
                
                if (RWLogger::IsOn()){ RWLogger::Log("ret_object", "Can't connect to Rating-Widget.com"); }
                
                return false;
            }

            if (function_exists('stream_set_timeout')){
                stream_set_timeout($fp, 3);
            }

            global $wp_version;

            $request_body = http_build_query($pData, null, '&');

            $request  = "POST {$pPage} HTTP/1.0\r\n";
            $request .= "Host: " . WP_RW__DOMAIN . "\r\n";
            $request .= "User-agent: WordPress/$wp_version\r\n";
            $request .= 'Content-Type: application/x-www-form-urlencoded; charset=' . get_option('blog_charset') . "\r\n";
            $request .= 'Content-Length: ' . strlen($request_body) . "\r\n";

            fwrite($fp, "$request\r\n$request_body");

            $response = '';
            while (!feof($fp)){
                $response .= fread($fp, 4096);
            }
            fclose($fp);
            
            list($headers, $rw_ret_obj) = explode("\r\n\r\n", $response, 2);
        }
        
        if (RWLogger::IsOn()){ RWLogger::Log("ret_object", var_export($rw_ret_obj, true)); }
        
        return $rw_ret_obj;
    }

    public static function QueueRatingData($urid, $title, $permalink, $rclass)
    {
        if (isset(self::$ratings[$urid])){ return; }
        
        $title_short = (mb_strlen($title) > 256) ? trim(mb_substr($title, 0, 256)) . '...' : $title;
        $permalink = (mb_strlen($permalink) > 512) ? trim(mb_substr($permalink, 0, 512)) . '...' : $permalink;
        self::$ratings[$urid] = array("title" => $title, "permalink" => $permalink, "rclass" => $rclass);
    }

    private function load_user_key()
    {
        $user_key = $this->_getOption(WP_RW__DB_OPTION_USER_KEY, true);

        if (!defined('WP_RW__USER_KEY'))
        {
            define('WP_RW__USER_KEY', $user_key);
        }
        else
        {
            if (is_string(WP_RW__USER_KEY) && (!is_string($user_key) || strtolower(WP_RW__USER_KEY) !== strtolower($user_key)))
            {
                // Override user key.
                $this->_setOption(WP_RW__DB_OPTION_USER_KEY, WP_RW__USER_KEY);
            }
        }

        $user_secret = $this->_getOption(WP_RW__DB_OPTION_USER_SECRET, true);

        if (!defined('WP_RW__USER_SECRET'))
        {
            define('WP_RW__USER_SECRET', $user_secret);
        }
        else
        {
            if (is_string(WP_RW__USER_SECRET) && (!is_string($user_secret) || strtolower(WP_RW__USER_SECRET) !== strtolower($user_secret)))
            {
                // Override user key.
                $this->_setOption(WP_RW__DB_OPTION_USER_SECRET, WP_RW__USER_SECRET);
            }
        }
    }

    private function _printMessages($messages, $class)
    {
        if (!$codes = $messages->get_error_codes()){ return; }
        
?>
<div class="<?php echo $class;?>">
<?php
        foreach ($codes as $code) :
            foreach ($messages->get_error_messages($code) as $message) :
?>
    <p><?php
        if ($code === "connect" || strtolower($message) == "couldn't connect to host")
        {
            echo "Couldn't connect to host. <b>If you keep getting this message over and over again, a workaround can be found <a href=\"". 
                 WP_RW__ADDRESS ."/http://rating-widget.com/blog/solution-for-wordpress-plugin-couldnt-connect-to-host/\" targe=\"_blank\">here</a>.</b>";
        }
        else
        {
            echo $messages->get_error_data($code) ? $message : esc_html($message);
        } 
    ?></p>
<?php
            endforeach;
        endforeach;
        $messages = new WP_Error();
?>
</div>
<br class="clear" />
<?php        
    }
    
    private function _printErrors()
    {
        $this->_printMessages(self::$errors, "error");
    }

    private function _printSuccess()
    {
        $this->_printMessages(self::$success, "updated");
    }
    
    public function GenerateToken($pTimestamp, $pServerCall = false)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("GenerateToken", $params, true); }

        $ip = (!$pServerCall) ? WP_RW__CLIENT_ADDR : WP_RW__SERVER_ADDR;

        if ($pServerCall)
        {
            if (RWLogger::IsOn()){ 
                RWLogger::Log("ServerToken", "ServerToken");
                RWLogger::Log("ServerIP", $ip);
            }
            
            $token = md5(/*$ip . */$pTimestamp . /*WP_RW__USER_KEY . */WP_RW__USER_SECRET);
        }
        else
        {
            if (RWLogger::IsOn()){
                RWLogger::Log("ClientToken", "ClientToken"); 
                RWLogger::Log("ClientIP", $ip);
            }

            $token = md5(/*$ip . */$pTimestamp ./* WP_RW__USER_KEY . */ WP_RW__USER_SECRET);
        }
        
        if (RWLogger::IsOn()){ RWLogger::Log("TOKEN", $token); }
        
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogDeparture("GenerateToken", $token); }
        
        return $token;
    }
    
    /* Public Static
    -------------------------------------------------*/
    static $TOP_RATED_WIDGET_LOADED = false;
    static function TopRatedWidgetLoaded()
    {
        self::$TOP_RATED_WIDGET_LOADED = true;
    }
    
    /* Admin Page Settings
    ---------------------------------------------------------------------------------------------------------------*/
    function rw_admin_menu_icon_css()
    {
        global $bp;
    ?>
        <style type="text/css">
            ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?> .wp-menu-image a
            { background-image: url( <?php echo WP_RW__PLUGIN_URL . 'icons.png' ?> ) !important; background-position: -1px -32px; }
            ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?>:hover .wp-menu-image a,
            ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?>.wp-has-current-submenu .wp-menu-image a,
            ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?>.current .wp-menu-image a
            { background-position: -1px 0; }
            ul#adminmenu li.toplevel_page_<?php echo WP_RW__ADMIN_MENU_SLUG;?> .wp-menu-image a img { display: none; }
        </style>

    <?php
    }
    
    function enqueue_scripts()
    {
        // Register and Enqueue jQuery.
        wp_enqueue_script('jquery');
    }
    
    function admin_enqueue_scripts()
    {
        // Register CSS stylesheets.
        wp_register_style('rw', WP_RW__ADDRESS_CSS . "settings.css", array(), WP_RW__VERSION);
        wp_register_style('rw_wp_settings', WP_RW__ADDRESS_CSS . "wordpress/settings.css", array(), WP_RW__VERSION);
        wp_register_style('rw_wp_reports', WP_RW__ADDRESS_CSS . "wordpress/reports.css", array(), WP_RW__VERSION);
        wp_register_style('rw_cp', WP_RW__ADDRESS_CSS . "colorpicker.css", array(), WP_RW__VERSION);

        // Register JS.
        wp_register_script('rw', WP_RW__ADDRESS_JS . "index.php", array(), WP_RW__VERSION);
        wp_register_script('rw_wp', WP_RW__ADDRESS_JS . "wordpress/settings.js", array(), WP_RW__VERSION);
        wp_register_script('rw_cp', WP_RW__ADDRESS_JS . "vendors/colorpicker.js", array(), WP_RW__VERSION);
        wp_register_script('rw_cp_eye', WP_RW__ADDRESS_JS . "vendors/eye.js", array(), WP_RW__VERSION);
        wp_register_script('rw_cp_utils', WP_RW__ADDRESS_JS . "vendors/utils.js", array(), WP_RW__VERSION);

        // Enqueue styles.
        wp_enqueue_style('rw');
        wp_enqueue_style('rw_wp_settings');
        wp_enqueue_style('rw_cp');

        // Enqueue scripts.
        wp_enqueue_script('json2');
        wp_enqueue_script('rw_cp');
        wp_enqueue_script('rw_cp_eye');
        wp_enqueue_script('rw_cp_utils');
        wp_enqueue_script('rw_wp');
        wp_enqueue_script('rw');
    }
    
    function admin_menu()
    {
        $this->is_admin = (bool)current_user_can('manage_options');
        
        if (!$this->is_admin){ return; }
        
        if (false === WP_RW__USER_KEY){
            add_options_page(__('Rating-Widget Settings', WP_RW__ID), __('Ratings', WP_RW__ID), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_user_key_page'));
            
            if ( function_exists('add_object_page') ){ // WP 2.7+
                $hook = add_object_page(__('Rating-Widget Settings', WP_RW__ID), __('Ratings', WP_RW__ID), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_user_key_page'), WP_RW__PLUGIN_URL . "icon.png" );
            }else{
                $hook = add_management_page(__('Rating-Widget Settings', WP_RW__ID), __('Ratings', WP_RW__ID), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_user_key_page') );
            }
            
            add_action("load-$hook", array( &$this, 'rw_user_key_page_load'));
            
            if ((empty($_GET['page']) || WP_RW__ADMIN_MENU_SLUG != $_GET['page'])){
                add_action('admin_notices', create_function('', 'echo "<div class=\"error\"><p>Rating-Widget is not activated yet. You need to <a class=\"button\" style=\"text-decoration: none; color: inherit;\" href=\"edit.php?page=' . WP_RW__ADMIN_MENU_SLUG . '\">activate the account</a> to start seeing the ratings.</p></div>";'));
            }

            return;
        }

        add_options_page(__('Rating-Widget Settings', WP_RW__ID), __('Ratings', WP_RW__ID), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_settings_page'));
        
        if ( function_exists('add_object_page') ){ // WP 2.7+
            $hook = add_object_page(__('Rating-Widget Settings', WP_RW__ID), __('Ratings', WP_RW__ID), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_settings_page'), WP_RW__PLUGIN_URL . "icon.png" );
        }else{
            $hook = add_management_page(__( 'Rating-Widget Settings', WP_RW__ID ), __( 'Ratings', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_settings_page') );
        }

        add_action("load-$hook", array( &$this, 'rw_settings_page_load'));
        
        if ($this->is_admin)
        { 
            add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; Basic', WP_RW__ID ), __('Basic', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG, array(&$this, 'rw_settings_page'));
            
            if (self::$WP_RW__BP_INSTALLED){
                add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; BuddyPress', WP_RW__ID ), __('BuddyPress', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG . '&amp;action=buddypress', array(&$this, 'rw_settings_page'));
            }
            if (defined('WP_RW__BBP_INSTALLED')){
                add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; bbPress', WP_RW__ID ), __('bbPress', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG . '&amp;action=bbpress', array(&$this, 'rw_settings_page'));
            }
            
            $user_label = (self::$WP_RW__BP_INSTALLED) ? "User" : "Author";
             
//            add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; ' . $user_label . " (Accumulated)", WP_RW__ID ), __($user_label . '  (Accumulated)', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG . '&amp;action=user', array(&$this, 'rw_settings_page'));
            add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; Advanced', WP_RW__ID ), __('Advanced', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG . '&amp;action=advanced', array(&$this, 'rw_settings_page'));
            add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; Reports', WP_RW__ID ), __('Reports', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG . '&amp;action=reports', array(&$this, 'rw_settings_page'));
            
            if (false !== WP_RW__USER_SECRET){
                add_submenu_page(WP_RW__ADMIN_MENU_SLUG, __( 'Ratings &ndash; Boost', WP_RW__ID ), __('Boost', WP_RW__ID ), 'edit_posts', WP_RW__ADMIN_MENU_SLUG . '&amp;action=boost', array(&$this, 'rw_settings_page'));
            }
        }
    }

    function rw_user_key_page_load()
    {
        if ('post' != strtolower($_SERVER['REQUEST_METHOD']) ||
            empty($_POST['action']) ||
            'account' != $_POST['action'])
        {
            return false;
        }
        
        if (!isset($_POST["rw_service_terms"]))
        {
            self::$errors->add('rw_service_terms', __("You can't create an account without accepting the Terms of Use and the Privacy Policy."));
            return false;
        }
        
        // Get reCAPTCHA inputs.
        $recaptcha_challenge = $_POST['recaptcha_challenge_field'];
        $recaptcha_response = $_POST['recaptcha_response_field'];
        
        $details = array( 
            'title' => urlencode(get_option('blogname', "")),
            'email' => urlencode(get_option('admin_email', "")),
            'domain' => urlencode(get_option('siteurl', "")),
            'challenge' => $recaptcha_challenge,
            'response' => $recaptcha_response,
        );
        
        $rw_ret_obj = self::RemoteCall("action/user.php", $details);

        if (false === $rw_ret_obj){ return false; }
        
        // Decode RW ret object.
        $rw_ret_obj = json_decode($rw_ret_obj);

        if (false == $rw_ret_obj->success)
        {
            self::$errors->add('rating_widget_captcha', __($rw_ret_obj->msg, WP_RW__ID));
            return false;
        }
        
        $rw_user_key = $rw_ret_obj->data[0]->uid;
        $this->_setOption("rw_user_key", $rw_user_key);
        define("WP_RW__USER_KEY", $rw_user_key);
        
        // Refresh the page.
        header("Location: " . $_SERVER["REQUEST_URI"]);
        
        return true;
    }
    
    function rw_user_key_page($flush = false)
    {
        if (false === $flush && false !== WP_RW__USER_KEY)
        {
            $this->rw_settings_page();
            return;
        }                               

        $this->_printErrors();
        
        require_once(dirname(__FILE__) . "/view/userkey_generation.php");
    }

    function rw_settings_page_load()
    {
        global $plugin_page;
        
        $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : false;
        
        /*if (!$this->is_admin){
            $action = "reports";
        }*/
        
        switch ($action)
        {
            case "buddypress":
                $plugin_page = WP_RW__ADMIN_MENU_SLUG . '&amp;action=buddypress';
                break;
            case "bbpress":
                $plugin_page = WP_RW__ADMIN_MENU_SLUG . '&amp;action=bbpress';
                break;
            case "advanced":
                $plugin_page = WP_RW__ADMIN_MENU_SLUG . '&amp;action=advanced';
                break;
            case "user":
                $plugin_page = WP_RW__ADMIN_MENU_SLUG . '&amp;action=user';
                break;
            case "reports":
//                wp_enqueue_script("jquery-ui-core1", WP_RW__ADDRESS_JS . "vendors/jquery.ui.core.js");
//                wp_enqueue_script("jquery-ui-datepicker", WP_RW__ADDRESS_JS . "vendors/jquery.ui.datepicker.js");
                wp_enqueue_script("jquery-ui-datepicker", WP_RW__ADDRESS_JS . "vendors/jquery-ui-1.8.9.custom.min.js");
                
                wp_enqueue_style('jquery-theme-smoothness', WP_RW__ADDRESS_CSS . "vendors/jquery/smoothness/jquery.smoothness.css");
                
                wp_enqueue_style('rw_external', WP_RW__ADDRESS_CSS . "external.php?all=t", array(), WP_RW__VERSION);
                wp_enqueue_style('rw_wp_reports');
                $plugin_page = WP_RW__ADMIN_MENU_SLUG . '&amp;action=reports';
                break;
            case "boost":
                $plugin_page = WP_RW__ADMIN_MENU_SLUG . '&amp;action=boost';
                break;
            case false:
            default:
                break;
        }
        
    }
    
    
    /* Reports
    ---------------------------------------------------------------------------------------------------------------*/
    private static function _getAddFilterQueryString($pQuery, $pName, $pValue)
    {
        $pos = strpos($pQuery, "{$pName}=");
        if (false !== $pos)
        {
            $end = $pos + strlen("{$pName}=");
            $cur = $end;
            $max = strlen($pQuery);
            while ($cur < $max && $pQuery[$cur] !== "&"){
                $cur++;
            }
            
            $pQuery = substr($pQuery, 0, $end) . urlencode($pValue) . substr($pQuery, $cur);
        }
        else
        {
            $pQuery .= (($pQuery === "") ? "" : "&") . "{$pName}=" . urlencode($pValue);
        }        
        
        return $pQuery;
    }
    
    private static function _getRemoveFilterFromQueryString($pQuery, $pName)
    {
        $pos = strpos($pQuery, "{$pName}=");
        
        if (false === $pos){ return $pQuery; }
        
        $end = $pos + strlen("{$pName}=");
        $cur = $end;
        $max = strlen($pQuery);
        while ($cur < $max && $pQuery[$cur] !== "&"){
            $cur++;
        }
        
        if ($pos > 0 && $pQuery[$pos - 1] === "&"){ $pos--; }
        
        return substr($pQuery, 0, $pos) . substr($pQuery, $cur);
    }
    
    
    function rw_general_report_page()
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_general_report_page", $params); }

        $elements = isset($_REQUEST["elements"]) ? $_REQUEST["elements"] : "posts";
        $orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"] : "created";
        $order = isset($_REQUEST["order"]) ? $_REQUEST["order"] : "DESC";
        $date_from = isset($_REQUEST["from"]) ? $_REQUEST["from"] : date(WP_RW__DEFAULT_DATE_FORMAT, time() - WP_RW__PERIOD_MONTH);
        $date_to = isset($_REQUEST["to"]) ? $_REQUEST["to"] : date(WP_RW__DEFAULT_DATE_FORMAT);
        $rw_limit = isset($_REQUEST["limit"]) ? max(WP_RW__REPORT_RECORDS_MIN, min(WP_RW__REPORT_RECORDS_MAX, $_REQUEST["limit"])) : WP_RW__REPORT_RECORDS_MIN;
        $rw_offset = isset($_REQUEST["offset"]) ? max(0, (int)$_REQUEST["offset"]) : 0;
        
        switch ($elements)
        {
            case "activity-updates":
                $rating_options = WP_RW__ACTIVITY_UPDATES_OPTIONS;
                $rclass = "activity-update";
                break;
            case "activity-comments":
                $rating_options = WP_RW__ACTIVITY_COMMENTS_OPTIONS;
                $rclass = "activity-comment";
                break;
            case "forum-posts":
                $rating_options = WP_RW__FORUM_POSTS_OPTIONS;
                $rclass = "forum-post,new-forum-post";
                break;
            case "users":
                $rating_options = WP_RW__USERS_OPTIONS;
                $rclass = "user";
                break;
            case "comments":
                $rating_options = WP_RW__COMMENTS_OPTIONS;
                $rclass = "comment,new-blog-comment";
                break;
            case "pages":
                $rating_options = WP_RW__PAGES_OPTIONS;
                $rclass = "page";
                break;
            case "posts":
            default:
                $rating_options = WP_RW__BLOG_POSTS_OPTIONS;
                $rclass = "front-post,blog-post,new-blog-post";
                break;
        }
        
        $rating_options = json_decode($this->_getOption($rating_options));
        $rating_type = $rating_options->type;
        $rating_stars = ($rating_type === "star") ? 
                        ((isset($rating_options->advanced) && isset($rating_options->advanced->star) && isset($rating_options->advanced->star->stars)) ? $rating_options->advanced->star->stars : WP_RW__DEF_STARS) :
                        false;
        
        $details = array( 
            "uid" => WP_RW__USER_KEY,
            "rclasses" => $rclass,
            "orderby" => $orderby,
            "order" => $order,
            "since_updated" => "{$date_from} 00:00:00",
            "due_updated" => "{$date_to} 23:59:59",
            "limit" => $rw_limit + 1,
            "offset" => $rw_offset,
        );
        
        $rw_ret_obj = self::RemoteCall("action/report/general.php", $details);

        if (false === $rw_ret_obj){ return false; }
        
        // Decode RW ret object.
        $rw_ret_obj = json_decode($rw_ret_obj);

        if (RWLogger::IsOn()){ RWLogger::Log("ret_object", var_export($rw_ret_obj, true)); }
        
        if (false == $rw_ret_obj->success)
        {
            $this->rw_report_example_page();
            return false;
        }
        
        // Override token to client's call token for iframes.
        $details["token"] = self::GenerateToken($details["timestamp"], false);
        
        $empty_result = (!is_array($rw_ret_obj->data) || 0 == count($rw_ret_obj->data));
?>
<div class="wrap rw-dir-ltr rw-report">
    <h2><?php echo __( 'Rating-Widget Reports', WP_RW__ID) . " (" . ucwords($elements) . ")";?></h2>
    <div id="message" class="updated fade">
        <p><strong style="color: red;">Notic: All reports are automatically cached for 30 minutes.</strong></p>
    </div>
    <form method="post" action="">
        <div class="tablenav">
            <div class="actions rw-control-bar">
                <span>Date Range:</span>
                <input type="text" value="<?php echo $date_from;?>" id="rw_date_from" name="rw_date_from" style="width: 90px; text-align: center;" />
                -
                <input type="text" value="<?php echo $date_to;?>" id="rw_date_to" name="rw_date_to" style="width: 90px; text-align: center;" />
                <script type="text/javascript">
                    jQuery.datepicker.setDefaults({
                        dateFormat: "yy-mm-dd"
                    })
                    
                    jQuery("#rw_date_from").datepicker({
                        maxDate: 0,
                        onSelect: function(dateText, inst){
                            jQuery("#rw_date_to").datepicker("option", "minDate", dateText);
                        }
                    });
                    jQuery("#rw_date_from").datepicker("setDate", "<?php echo $date_from;?>");
                    
                    jQuery("#rw_date_to").datepicker({
                        minDate: "<?php echo $date_from;?>",
                        maxDate: 0,
                        onSelect: function(dateText, inst){
                            jQuery("#rw_date_from").datepicker("option", "maxDate", dateText);
                        }
                    });
                    jQuery("#rw_date_to").datepicker("setDate", "<?php echo $date_to;?>");
                </script>
                <span>Element:</span>
                <select id="rw_elements">
                <?php
                    $select = array(
                        __('Posts', WP_RW__ID) => "posts",
                        __('Pages', WP_RW__ID) => "pages",
                        __('Comments', WP_RW__ID) => "comments"
                    );
                    
                    if (self::$WP_RW__BP_INSTALLED && is_plugin_active(WP_RW__BP_CORE_FILE))
                    {
                        $select[__('Activity-Updates', WP_RW__ID)] = "activity-updates";
                        $select[__('Activity-Comments', WP_RW__ID)] = "activity-comments";
                        $select[__('Users-Profiles', WP_RW__ID)] = "users";
                        
                        if (defined('WP_RW__BBP_INSTALLED'))
                        {
                            $select[__('Forum-Posts', WP_RW__ID)] = "forum-posts";
                        }
                    }
                    
                    foreach ($select as $option => $value)
                    {
                        $selected = '';
                        if ($value === $elements){ $selected = ' selected="selected"'; }
                ?>
                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $option; ?></option>
                <?php 
                    }
                ?>
                </select>
                <span>Order By:</span>                
                <select id="rw_orderby">
                <?php
                    $select = array(
                        "title" => __('Title', WP_RW__ID),
                        "urid" => __('Id', WP_RW__ID),
                        "created" => __('Start Date', WP_RW__ID),
                        "updated" => __('Last Update', WP_RW__ID),
                        "votes" => __('Votes', WP_RW__ID),
                        "avgrate" => __('Average Rate', WP_RW__ID),
                    );
                    foreach ($select as $value => $option)
                    {
                        $selected = '';
                        if ($value == $orderby)
                            $selected = ' selected="selected"';
                ?>
                        <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
                <?php
                    }
                ?>
                </select>
                <input class="button-secondary action" type="button" value="<?php _e("Show", WP_RW__ID);?>" onclick="top.location = RWM.enrichQueryString(top.location.href, ['from', 'to', 'orderby', 'elements'], [jQuery('#rw_date_from').val(), jQuery('#rw_date_to').val(), jQuery('#rw_orderby').val(), jQuery('#rw_elements').val()]);" />
            </div>
        </div>
        <br />
        <table class="widefat rw-chart-title">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">Votes Timeline</th>
                </tr>
            </thead>
        </table>
        <iframe class="rw-chart" src="<?php
            $details["since"] = $details["since_updated"];
            $details["due"] = $details["due_updated"];
            $details["date"] = "updated";
            unset($details["since_updated"], $details["due_updated"]);

            $details["width"] = 950;
            $details["height"] = 200;
            
            $query = "";
            foreach ($details as $key => $value)
            {
                $query .= ($query == "") ? "?" : "&";
                $query .= "{$key}=" . urlencode($value);
            }
            echo WP_RW__ADDRESS . "/action/chart/column.php{$query}";
        ?>" width="<?php echo $details["width"];?>" height="<?php echo ($details["height"] + 4);?>" frameborder="0"></iframe>
        <br /><br />
        <table class="widefat"><?php
        $records_num = $showen_records_num = 0;
        if (!is_array($rw_ret_obj->data) || count($rw_ret_obj->data) === 0){ ?>
            <tbody>
                <tr>
                    <td colspan="6"><?php printf(__('No ratings here.', WP_RW__ID), $elements); ?></td>
                </tr>
            </tbody><?php
        }else{  ?>
            <thead>
                <tr>
                    <th scope="col" class="manage-column"></th>
                    <th scope="col" class="manage-column">Title</th>
                    <th scope="col" class="manage-column">Id</th>
                    <th scope="col" class="manage-column">Start Date</th>
                    <th scope="col" class="manage-column">Last Update</th>
                    <th scope="col" class="manage-column">Votes</th>
                    <th scope="col" class="manage-column">Average Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $alternate = true;
                
                $records_num = count($rw_ret_obj->data);
                $showen_records_num = min($records_num, $rw_limit);
                for ($i = 0; $i < $showen_records_num; $i++)
                {
                    $rating = $rw_ret_obj->data[$i];
            ?>
                <tr<?php if ($alternate) echo ' class="alternate"';?>>
                    <td>
                        <a href="<?php
//                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "report", WP_RW__REPORT_RATING);
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "urid", $rating->urid);
                            $query_string = self::_getAddFilterQueryString($query_string, "type", $rating_type);
                            if ("star" === $rating_type){
                                $query_string = self::_getAddFilterQueryString($query_string, "stars", $rating_stars);
                            }
                            
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                        ?>"><img src="<?php echo WP_RW__ADDRESS_IMG;?>rw.pie.icon.png" alt="" title="Rating Report"></a>
                    </td>
                    <td><strong><a href="<?php echo $rating->url; ?>" target="_blank"><?php
                            echo (mb_strlen($rating->title) > 40) ?
                                  trim(mb_substr($rating->title, 0, 40)) . "..." :
                                  $rating->title;
                        ?></a></strong></td>
                    <td><?php echo $rating->urid;?></td>
                    <td><?php echo $rating->created;?></td>
                    <td><?php echo $rating->updated;?></td>
                    <td><?php echo $rating->votes;?></td>
                    <td>
                        <?php
                            $vars = array(
                                "votes" => $rating->votes,
                                "rate" => $rating->rate * ($rating_stars / WP_RW__DEF_STARS),
                                "dir" => "ltr",
                                "type" => $rating_type,
                                "stars" => $rating_stars,
                            );
                            
                            if ($rating_type == "star")
                            {
                                $vars["style"] = "yellow";
                                require(dirname(__FILE__) . "/view/rating.php");
                            }
                            else
                            {
                                $likes = floor($rating->rate / WP_RW__DEF_STARS);
                                $dislikes = max(0, $rating->votes - $likes);

                                $vars["style"] = "thumbs";
                                $vars["rate"] = 1;
                                require(dirname(__FILE__) . "/view/rating.php");
                                echo '<span style="line-height: 16px; color: darkGreen; padding-right: 5px;">' . $likes . '</span>';
                                $vars["rate"] = -1;
                                require(dirname(__FILE__) . "/view/rating.php");
                                echo '<span style="line-height: 16px; color: darkRed; padding-right: 5px;">' . $dislikes . '</span>';
                            }
                        ?>
                    </td>
                </tr>
            <?php                    
                    $alternate = !$alternate;
                }
            ?>
            </tbody>
        <?php 
        }
        ?>
        </table>
        <?php
            if ($showen_records_num > 0)
            {
        ?>
        <div class="rw-control-bar">
            <div style="float: left;">
                <span style="font-weight: bold; font-size: 12px;"><?php echo ($offset + 1); ?>-<?php echo ($offset + $showen_records_num); ?></span>
            </div>
            <div style="float: right;">
                <span>Show rows:</span>
                <select name="rw_limit" onchange="top.location = RWM.enrichQueryString(top.location.href, ['offset', 'limit'], [0, this.value]);">
                <?php
                    $limits = array(WP_RW__REPORT_RECORDS_MIN, 25, WP_RW__REPORT_RECORDS_MAX);
                    foreach ($limits as $limit)
                    {
                ?>
                    <option value="<?php echo $limit;?>"<?php if ($rw_limit == $limit) echo ' selected="selected"'; ?>><?php echo $limit;?></option>
                <?php
                    }
                ?>
                </select>
                <input type="button"<?php if ($rw_offset == 0) echo ' disabled="disabled"';?> class="button button-secondary action" style="margin-left: 20px;" onclick="top.location = '<?php
                    $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "offset", max(0, $rw_offset - $rw_limit));
                    echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                ?>';" value="Previous" />
                <input type="button"<?php if ($showen_records_num == $records_num) echo ' disabled="disabled"';?> class="button button-secondary action" onclick="top.location = '<?php
                    $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "offset", $rw_offset + $rw_limit);
                    echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                ?>';" value="Next" />
            </div>
        </div>
        <?php
            }
        ?>
    </form>
</div>
<?php        
    }
    
    public static function _isValidPCId($pPCId)
    {
        // Length check.
        if (strlen($pPCId) !== 36){
            return false;
        }
        
        if ($pPCId[8] != "-" ||
            $pPCId[13] != "-" ||
            $pPCId[18] != "-" ||
            $pPCId[23] != "-")
        {
            return false;
        }
        
       
        for ($i = 0; $i < 36; $i++)
        {
            if ($i == 8 || $i == 13 || $i == 18 || $i == 23){ $i++; }
            
            $code = ord($pPCId[$i]);
            if ($code < 48 || 
                $code > 70 || 
                ($code > 57 && $code < 65))
            {
                return false;
            }
        }

        return true;            
    }
        
    
    function rw_report_example_page()
    {
?>
<div class="wrap rw-dir-ltr rw-report">
    <h2><?php echo __( 'Rating-Widget Reports', WP_RW__ID);?></h2>
    <div style="width: 750px;">
        The Rating-Widget Reports page provides you with an analytical overview of your blog-ratings' votes in one page. 
        Here, you can gain an understanding of how interesting and attractive your blog elements (e.g. posts, pages), 
        how active your users, and check the segmentation of the votes.
    </div>
    <br />
    <div style="background: #FCE6E8; color: red; font-weight: bold; width: 725px; padding: 10px; text-align: center; border: 4px solid red; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius:10px;">
        This feature is not included in your free plugin version.<br />
        To access this area you'll need to <a href="http://rating-widget.com/get-the-word-press-plugin/" target="_blank">subscribe to the Premium Program</a>.
    </div>
    <br />
    <img src="<?php echo WP_RW__ADDRESS_IMG . "wordpress/rw.report.example.png"  ?>" alt="">
</div>
<?php        
    }
    
    function rw_explicit_report_page()
    {
        $filters = array(
            "vid" => array(
                "label" => "User Id",
                "validation" => create_function('$val', 'return (is_numeric($val) && $val >= 0);'),
            ),
            "pcid" => array(
                "label" => "PC Id",
                "validation" => create_function('$val', 'return (RatingWidgetPlugin::_isValidPCId($val));'),
            ),
            "ip" => array(
                "label" => "IP",
                "validation" => create_function('$val', 'return (1 === preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $val));'),
            ),
        );
        
        $elements = isset($_REQUEST["elements"]) ? $_REQUEST["elements"] : "posts";
        $orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"] : "created";
        $order = isset($_REQUEST["order"]) ? $_REQUEST["order"] : "DESC";
        $date_from = isset($_REQUEST["from"]) ? $_REQUEST["from"] : date(WP_RW__DEFAULT_DATE_FORMAT, time() - WP_RW__PERIOD_MONTH);
        $date_to = isset($_REQUEST["to"]) ? $_REQUEST["to"] : date(WP_RW__DEFAULT_DATE_FORMAT);
        $rw_limit = isset($_REQUEST["limit"]) ? max(WP_RW__REPORT_RECORDS_MIN, min(WP_RW__REPORT_RECORDS_MAX, $_REQUEST["limit"])) : WP_RW__REPORT_RECORDS_MIN;
        $rw_offset = isset($_REQUEST["offset"]) ? max(0, (int)$_REQUEST["offset"]) : 0;
        
        switch ($elements)
        {
            case "activity-updates":
                $rating_options = WP_RW__ACTIVITY_UPDATES_OPTIONS;
                $rclass = "activity-update";
                break;
            case "activity-comments":
                $rating_options = WP_RW__ACTIVITY_COMMENTS_OPTIONS;
                $rclass = "activity-comment";
                break;
            case "forum-posts":
                $rating_options = WP_RW__FORUM_POSTS_OPTIONS;
                $rclass = "forum-post,new-forum-post";
                break;
            case "users":
                $rating_options = WP_RW__USERS_OPTIONS;
                $rclass = "user";
                break;
            case "comments":
                $rating_options = WP_RW__COMMENTS_OPTIONS;
                $rclass = "comment,new-blog-comment";
                break;
            case "pages":
                $rating_options = WP_RW__PAGES_OPTIONS;
                $rclass = "page";
                break;
            case "posts":
            default:
                $rating_options = WP_RW__BLOG_POSTS_OPTIONS;
                $rclass = "front-post,blog-post,new-blog-post";
                break;
        }
        
        $rating_options = json_decode($this->_getOption($rating_options));
        $rating_type = $rating_options->type;
        $rating_stars = ($rating_type === "star") ? 
                        ((isset($rating_options->advanced) && isset($rating_options->advanced->star) && isset($rating_options->advanced->star->stars)) ? $rating_options->advanced->star->stars : WP_RW__DEF_STARS) :
                        false;
        
        $details = array( 
            "uid" => WP_RW__USER_KEY,
            "rclasses" => $rclass,
            "orderby" => $orderby,
            "order" => $order,
            "since_updated" => "{$date_from} 00:00:00",
            "due_updated" => "{$date_to} 23:59:59",
            "limit" => $rw_limit + 1,
            "offset" => $rw_offset,
        );
        
        // Attach filters data.
        foreach ($filters as $filter => $filter_data)
        {
            if (isset($_REQUEST[$filter]) && true === $filter_data["validation"]($_REQUEST[$filter])){
                $details[$filter] = $_REQUEST[$filter];
            }            
        }
        
        $rw_ret_obj = self::RemoteCall("action/report/explicit.php", $details);

        if (false === $rw_ret_obj){ return false; }
        
        // Decode RW ret object.
        $rw_ret_obj = json_decode($rw_ret_obj);

        if (RWLogger::IsOn()){ RWLogger::Log("ret_object", var_export($rw_ret_obj, true)); }

        if (false == $rw_ret_obj->success)
        {
            $this->rw_report_example_page();
            return false;
        }
        
        // Override token to client's call token for iframes.
        $details["token"] = self::GenerateToken($details["timestamp"], false);

        $empty_result = (!is_array($rw_ret_obj->data) || 0 == count($rw_ret_obj->data));
?>
<div class="wrap rw-dir-ltr rw-report">
    <h2><?php echo __( 'Rating-Widget Reports', WP_RW__ID);?></h2>
    <div id="message" class="updated fade">
        <p><strong style="color: red;">Notic: All reports are automatically cached for 30 minutes.</strong></p>
    </div>
    <form method="post" action="">
        <div class="tablenav">
            <div class="rw-control-bar actions">
                <span>Date Range:</span>
                <input type="text" value="<?php echo $date_from;?>" id="rw_date_from" name="rw_date_from" style="width: 90px; text-align: center;" />
                -
                <input type="text" value="<?php echo $date_to;?>" id="rw_date_to" name="rw_date_to" style="width: 90px; text-align: center;" />
                <script type="text/javascript">
                    jQuery.datepicker.setDefaults({
                        dateFormat: "yy-mm-dd"
                    })
                    
                    jQuery("#rw_date_from").datepicker({
                        maxDate: 0,
                        onSelect: function(dateText, inst){
                            jQuery("#rw_date_to").datepicker("option", "minDate", dateText);
                        }
                    });
                    jQuery("#rw_date_from").datepicker("setDate", "<?php echo $date_from;?>");
                    
                    jQuery("#rw_date_to").datepicker({
                        minDate: "<?php echo $date_from;?>",
                        maxDate: 0,
                        onSelect: function(dateText, inst){
                            jQuery("#rw_date_from").datepicker("option", "maxDate", dateText);
                        }
                    });
                    jQuery("#rw_date_to").datepicker("setDate", "<?php echo $date_to;?>");
                </script>
                <span>Order By:</span>                
                <select id="rw_orderby">
                <?php
                    $select = array(
                        "rid" => __('Rating Id', WP_RW__ID),
                        "created" => __('Start Date', WP_RW__ID),
                        "updated" => __('Last Update', WP_RW__ID),
                        "rate" => __('Rate', WP_RW__ID),
                        "vid" => __('User Id', WP_RW__ID),
                        "pcid" => __('PC Id', WP_RW__ID),
                        "ip" => __('IP', WP_RW__ID),
                    );
                    foreach ($select as $value => $option)
                    {
                        $selected = '';
                        if ($value == $orderby)
                            $selected = ' selected="selected"';
                ?>
                        <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
                <?php
                    }
                ?>
                </select>
                <input class="button-secondary action" type="button" value="<?php _e("Show", WP_RW__ID);?>" onclick="top.location = RWM.enrichQueryString(top.location.href, ['from', 'to', 'orderby'], [jQuery('#rw_date_from').val(), jQuery('#rw_date_to').val(), jQuery('#rw_orderby').val()]);" />
            </div>
        </div>
        <br />
        <div class="rw-filters">
        <?php
            foreach ($filters as $filter => $filter_data)
            {
                if (isset($_REQUEST[$filter]) && true === $filter_data["validation"]($_REQUEST[$filter]))
                {
        ?>
        <div class="rw-ui-report-filter">
            <a class="rw-ui-close" href="<?php
                $query_string = self::_getRemoveFilterFromQueryString($_SERVER["QUERY_STRING"], $filter);
                $query_string = self::_getRemoveFilterFromQueryString($query_string, "offset");
                echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
            ?>">x</a> |
            <span class="rw-ui-defenition"><?php echo $filter_data["label"];?>:</span>
            <span class="rw-ui-value"><?php echo $_REQUEST[$filter];?></span>
        </div>
        <?php
                }
            }
        ?>
        </div>
        <br />
        <br />
        <iframe class="rw-chart" src="<?php
            $details["since"] = $details["since_updated"];
            $details["due"] = $details["due_updated"];
            $details["date"] = "updated";
            unset($details["since_updated"], $details["due_updated"]);

            $details["width"] = 750;
            $details["height"] = 200;
            
            $query = "";
            foreach ($details as $key => $value)
            {
                $query .= ($query == "") ? "?" : "&";
                $query .= "{$key}=" . urlencode($value);
            }
            echo WP_RW__ADDRESS . "/action/chart/column.php{$query}";
        ?>" width="750" height="204" frameborder="0"></iframe>
        <br /><br />
        <table class="widefat"><?php
        $records_num = $showen_records_num = 0;
        if (!is_array($rw_ret_obj->data) || count($rw_ret_obj->data) === 0){ ?>
            <tbody>
                <tr>
                    <td colspan="6"><?php printf(__('No votes here.', WP_RW__ID)); ?></td>
                </tr>
            </tbody><?php
        }else{  ?>
            <thead>
                <tr>
                    <th scope="col" class="manage-column">Rating Id</th>
                    <th scope="col" class="manage-column">User Id</th>
                    <th scope="col" class="manage-column">PC Id</th>
                    <th scope="col" class="manage-column">IP</th>
                    <th scope="col" class="manage-column">Date</th>
                    <th scope="col" class="manage-column">Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $alternate = true;
                $records_num = count($rw_ret_obj->data);
                $showen_records_num = min($records_num, $rw_limit);
                for ($i = 0; $i < $showen_records_num; $i++)
                {
                    $vote = $rw_ret_obj->data[$i];
                    if ($vote->vid != "0"){
                        $user = get_userdata($vote->vid);
                    }
                    else
                    {
                        $user = new stdClass();
                        $user->user_login = "Anonymous";
                    }
            ?>
                <tr<?php if ($alternate) echo ' class="alternate"';?>>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "urid", $vote->urid);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo $vote->urid;?></a>
                    </td>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "vid", $vote->vid);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo $user->user_login;?></a>
                    </td>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "pcid", $vote->pcid);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo ($vote->pcid != "00000000-0000-0000-0000-000000000000") ? $vote->pcid : "Anonymous";?></a>
                    </td>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "ip", $vote->ip);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo $vote->ip;?></a>
                    </td>
                    <td><?php echo $vote->updated;?></td>
                    <td>
                        <?php
                            $vars = array(
                                "votes" => 1,
                                "rate" => $vote->rate * ($rating_stars / WP_RW__DEF_STARS),
                                "dir" => "ltr",
                                "type" => "star",
                                "stars" => $rating_stars,
                            );
                            
                            if ($rating_type == "star")
                            {
                                $vars["style"] = "yellow";
                                require(dirname(__FILE__) . "/view/rating.php");
                            }
                            else
                            {
                                $vars["type"] = "nero";
                                $vars["style"] = "thumbs";
                                $vars["rate"] = ($vars["rate"] > 0) ? 1 : -1;
                                require(dirname(__FILE__) . "/view/rating.php");
                            }
                        ?>
                    </td>
                </tr>
            <?php                    
                    $alternate = !$alternate;
                }
            ?>
            </tbody>
        <?php 
        }
        ?>
        </table>
        <?php
            if ($showen_records_num > 0)
            {
        ?>
        <div class="rw-control-bar">
            <div style="float: left;">
                <span style="font-weight: bold; font-size: 12px;"><?php echo ($offset + 1); ?>-<?php echo ($offset + $showen_records_num); ?></span>
            </div>
            <div style="float: right;">
                <span>Show rows:</span>
                <select name="rw_limit" onchange="top.location = RWM.enrichQueryString(top.location.href, ['offset', 'limit'], [0, this.value]);">
                <?php
                    $limits = array(WP_RW__REPORT_RECORDS_MIN, 25, WP_RW__REPORT_RECORDS_MAX);
                    foreach ($limits as $limit)
                    {
                ?>
                    <option value="<?php echo $limit;?>"<?php if ($rw_limit == $limit) echo ' selected="selected"'; ?>><?php echo $limit;?></option>
                <?php
                    }
                ?>
                </select>
                <input type="button"<?php if ($rw_offset == 0) echo ' disabled="disabled"';?> class="button button-secondary action" style="margin-left: 20px;" onclick="top.location = '<?php
                    $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "offset", max(0, $rw_offset - $rw_limit));
                    echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                ?>';" value="Previous" />
                <input type="button"<?php if ($showen_records_num == $records_num) echo ' disabled="disabled"';?> class="button button-secondary action" onclick="top.location = '<?php
                    $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "offset", $rw_offset + $rw_limit);
                    echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                ?>';" value="Next" />
            </div>
        </div>
        <?php
            }
        ?>
    </form>
</div>
<?php                
    }
    
    function rw_rating_report_page()
    {
        $filters = array(
            "urid" => array(
                "label" => "Rating Id",
                "validation" => create_function('$val', 'return (is_numeric($val) && $val >= 0);'),
            ),
            "vid" => array(
                "label" => "User Id",
                "validation" => create_function('$val', 'return (is_numeric($val) && $val >= 0);'),
            ),
            "pcid" => array(
                "label" => "PC Id",
                "validation" => create_function('$val', 'return (RatingWidgetPlugin::_isValidPCId($val));'),
            ),
            "ip" => array(
                "label" => "IP",
                "validation" => create_function('$val', 'return (1 === preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $val));'),
            ),
        );

        $orderby = isset($_REQUEST["orderby"]) ? $_REQUEST["orderby"] : "created";
        $order = isset($_REQUEST["order"]) ? $_REQUEST["order"] : "DESC";
        $date_from = isset($_REQUEST["from"]) ? $_REQUEST["from"] : date(WP_RW__DEFAULT_DATE_FORMAT, time() - WP_RW__PERIOD_MONTH);
        $date_to = isset($_REQUEST["to"]) ? $_REQUEST["to"] : date(WP_RW__DEFAULT_DATE_FORMAT);
        $rating_type = (isset($_REQUEST["type"]) && in_array($_REQUEST["type"], array("star", "nero"))) ? $_REQUEST["type"] : "star";
        $rating_stars = isset($_REQUEST["stars"]) ? max(WP_RW__MIN_STARS, min(WP_RW__MAX_STARS, (int)$_REQUEST["stars"])) : WP_RW__DEF_STARS;
        
        $rw_limit = isset($_REQUEST["limit"]) ? max(WP_RW__REPORT_RECORDS_MIN, min(WP_RW__REPORT_RECORDS_MAX, $_REQUEST["limit"])) : WP_RW__REPORT_RECORDS_MIN;
        $rw_offset = isset($_REQUEST["offset"]) ? max(0, (int)$_REQUEST["offset"]) : 0;
        
        $details = array( 
            "uid" => WP_RW__USER_KEY,
            "orderby" => $orderby,
            "order" => $order,
            "since" => "{$date_from} 00:00:00",
            "due" => "{$date_to} 23:59:59",
            "date" => "updated",
            "limit" => $rw_limit + 1,
            "offset" => $rw_offset,
            "stars" => $rating_stars,
            "type" => $rating_type,
        );
        
        // Attach filters data.
        foreach ($filters as $filter => $filter_data)
        {
            if (isset($_REQUEST[$filter]) && true === $filter_data["validation"]($_REQUEST[$filter])){
                $details[$filter] = $_REQUEST[$filter];
            }            
        }
        
        $rw_ret_obj = self::RemoteCall("action/report/rating.php", $details);
        if (false === $rw_ret_obj){ return; }
        
        // Decode RW ret object.
        $rw_ret_obj = json_decode($rw_ret_obj);

        if (false == $rw_ret_obj->success)
        {
            $this->rw_report_example_page();
            return false;
        }
        
        $empty_result = (!is_array($rw_ret_obj->data) || 0 == count($rw_ret_obj->data));

        // Override token to client's call token for iframes.
        $details["token"] = self::GenerateToken($details["timestamp"], false);
?>
<div class="wrap rw-dir-ltr rw-report">
    <h2><?php echo __( 'Rating-Widget Reports', WP_RW__ID) . " (Id = " . $_REQUEST["urid"] . ")";?></h2>
    <div id="message" class="updated fade">
        <p><strong style="color: red;">Notic: All reports are automatically cached for 30 minutes.</strong></p>
    </div>
    <form method="post" action="">
        <div class="tablenav">
            <div class="rw-control-bar actions">
                <span>Date Range:</span>
                <input type="text" value="<?php echo $date_from;?>" id="rw_date_from" name="rw_date_from" style="width: 90px; text-align: center;" />
                -
                <input type="text" value="<?php echo $date_to;?>" id="rw_date_to" name="rw_date_to" style="width: 90px; text-align: center;" />
                <script type="text/javascript">
                    jQuery.datepicker.setDefaults({
                        dateFormat: "yy-mm-dd"
                    })
                    
                    jQuery("#rw_date_from").datepicker({
                        maxDate: 0,
                        onSelect: function(dateText, inst){
                            jQuery("#rw_date_to").datepicker("option", "minDate", dateText);
                        }
                    });
                    jQuery("#rw_date_from").datepicker("setDate", "<?php echo $date_from;?>");
                    
                    jQuery("#rw_date_to").datepicker({
                        minDate: "<?php echo $date_from;?>",
                        maxDate: 0,
                        onSelect: function(dateText, inst){
                            jQuery("#rw_date_from").datepicker("option", "maxDate", dateText);
                        }
                    });
                    jQuery("#rw_date_to").datepicker("setDate", "<?php echo $date_to;?>");
                </script>
                <span>Order By:</span>                
                <select id="rw_orderby">
                <?php
                    $select = array(
                        "rid" => __('Id', WP_RW__ID),
                        "created" => __('Start Date', WP_RW__ID),
                        "updated" => __('Last Update', WP_RW__ID),
                        "rate" => __('Rate', WP_RW__ID),
                        "vid" => __('User Id', WP_RW__ID),
                        "pcid" => __('PC Id', WP_RW__ID),
                        "ip" => __('IP', WP_RW__ID),
                    );
                    foreach ($select as $value => $option)
                    {
                        $selected = '';
                        if ($value == $orderby)
                            $selected = ' selected="selected"';
                ?>
                        <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $option; ?></option>
                <?php
                    }
                ?>
                </select>
                <input class="button-secondary action" type="button" value="<?php _e("Show", WP_RW__ID);?>" onclick="top.location = RWM.enrichQueryString(top.location.href, ['from', 'to', 'orderby'], [jQuery('#rw_date_from').val(), jQuery('#rw_date_to').val(), jQuery('#rw_orderby').val()]);" />
            </div>
        </div>
        <br />
        <div class="rw-filters">
        <?php
            foreach ($filters as $filter => $filter_data)
            {
                if (isset($_REQUEST[$filter]) && true === $filter_data["validation"]($_REQUEST[$filter]))
                {
        ?>
        <div class="rw-ui-report-filter">
            <a class="rw-ui-close" href="<?php
                $query_string = self::_getRemoveFilterFromQueryString($_SERVER["QUERY_STRING"], $filter);
                $query_string = self::_getRemoveFilterFromQueryString($query_string, "offset");
                echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
            ?>">x</a> |
            <span class="rw-ui-defenition"><?php echo $filter_data["label"];?>:</span>
            <span class="rw-ui-value"><?php echo $_REQUEST[$filter];?></span>
        </div>
        <?php
                }
            }
        ?>
        </div>
        <br />
        <br />
        <iframe class="rw-chart" src="<?php
            $details["width"] = (!$empty_result) ? 647 : 950;
            $details["height"] = 200;

            $query = "";
            foreach ($details as $key => $value)
            {
                $query .= ($query == "") ? "?" : "&";
                $query .= "{$key}=" . urlencode($value);
            }
            echo WP_RW__ADDRESS . "/action/chart/column.php{$query}";
        ?>" width="<?php echo $details["width"];?>" height="<?php echo ($details["height"] + 4);?>" frameborder="0"></iframe>
        <?php
            if (!$empty_result)
            {
        ?>
        <iframe class="rw-chart" src="<?php
            $details["width"] = 300;
            $details["height"] = 200;

            $query = "";
            foreach ($details as $key => $value)
            {
                $query .= ($query == "") ? "?" : "&";
                $query .= "{$key}=" . urlencode($value);
            }
            $query .= "&stars={$rating_stars}";
            echo WP_RW__ADDRESS . "/action/chart/pie.php{$query}";
        ?>" width="<?php echo $details["width"];?>" height="<?php echo ($details["height"] + 4);?>" frameborder="0"></iframe>
        <?php
            }
        ?>
        <br /><br />
        <table class="widefat"><?php
        $records_num = $showen_records_num = 0;
        if (!is_array($rw_ret_obj->data) || count($rw_ret_obj->data) === 0){ ?>
            <tbody>
                <tr>
                    <td colspan="6"><?php printf(__('No votes here.', WP_RW__ID)); ?></td>
                </tr>
            </tbody><?php
        }else{  ?>
            <thead>
                <tr>
                    <th scope="col" class="manage-column">User Id</th>
                    <th scope="col" class="manage-column">PC Id</th>
                    <th scope="col" class="manage-column">IP</th>
                    <th scope="col" class="manage-column">Date</th>
                    <th scope="col" class="manage-column">Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $alternate = true;
                $records_num = count($rw_ret_obj->data);
                $showen_records_num = min($records_num, $rw_limit);
                for ($i = 0; $i < $showen_records_num; $i++)
                {
                    $vote = $rw_ret_obj->data[$i];
                    if ($vote->vid != "0"){
                        $user = get_userdata($vote->vid);
                    }
                    else
                    {
                        $user = new stdClass();
                        $user->user_login = "Anonymous";
                    }
            ?>
                <tr<?php if ($alternate) echo ' class="alternate"';?>>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "vid", $vote->vid);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo $user->user_login;?></a>
                    </td>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "pcid", $vote->pcid);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo ($vote->pcid != "00000000-0000-0000-0000-000000000000") ? $vote->pcid : "Anonymous";?></a>
                    </td>
                    <td>
                        <a href="<?php
                            $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "ip", $vote->ip);
                            echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;                        
                        ?>"><?php echo $vote->ip;?></a>
                    <td><?php echo $vote->updated;?></td>
                    <td>
                        <?php
                            $vars = array(
                                "votes" => 1,
                                "rate" => $vote->rate * ($rating_stars / WP_RW__DEF_STARS),
                                "dir" => "ltr",
                                "type" => "star",
                                "stars" => $rating_stars,
                            );
                            
                            if ($rating_type == "star")
                            {
                                $vars["style"] = "yellow";
                                require(dirname(__FILE__) . "/view/rating.php");
                            }
                            else
                            {
                                $vars["type"] = "nero";
                                $vars["style"] = "thumbs";
                                $vars["rate"] = ($vars["rate"] > 0) ? 1 : -1;
                                require(dirname(__FILE__) . "/view/rating.php");
                            }
                        ?>
                    </td>
                </tr>
            <?php                    
                    $alternate = !$alternate;
                }
            ?>
            </tbody>
        <?php 
        }
        ?>
        </table>
        <?php
            if ($showen_records_num > 0)
            {
        ?>
        <div class="rw-control-bar">
            <div style="float: left;">
                <span style="font-weight: bold; font-size: 12px;"><?php echo ($offset + 1); ?>-<?php echo ($offset + $showen_records_num); ?></span>
            </div>
            <div style="float: right;">
                <span>Show rows:</span>
                <select name="rw_limit" onchange="top.location = RWM.enrichQueryString(top.location.href, ['offset', 'limit'], [0, this.value]);">
                <?php
                    $limits = array(WP_RW__REPORT_RECORDS_MIN, 25, WP_RW__REPORT_RECORDS_MAX);
                    foreach ($limits as $limit)
                    {
                ?>
                    <option value="<?php echo $limit;?>"<?php if ($rw_limit == $limit) echo ' selected="selected"'; ?>><?php echo $limit;?></option>
                <?php
                    }
                ?>
                </select>
                <input type="button"<?php if ($rw_offset == 0) echo ' disabled="disabled"';?> class="button button-secondary action" style="margin-left: 20px;" onclick="top.location = '<?php
                    $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "offset", max(0, $rw_offset - $rw_limit));
                    echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                ?>';" value="Previous" />
                <input type="button"<?php if ($showen_records_num == $records_num) echo ' disabled="disabled"';?> class="button button-secondary action" onclick="top.location = '<?php
                    $query_string = self::_getAddFilterQueryString($_SERVER["QUERY_STRING"], "offset", $rw_offset + $rw_limit);
                    echo $_SERVER["SCRIPT_URI"] . "?" . $query_string;
                ?>';" value="Next" />
            </div>
        </div>
        <?php
            }
        ?>
    </form>
</div>
<?php                
    }
    
    /* Advanced Settings
    ---------------------------------------------------------------------------------------------------------------*/
    function rw_advanced_settings_page()
    {
        // Variables for the field and option names 
        $rw_form_hidden_field_name = "rw_form_hidden_field_name";

        // Get flash dependency.
        $rw_flash_dependency = $this->_getOption(WP_RW__FLASH_DEPENDENCY);
        
        // Get show on mobile flag.
        $rw_show_on_mobile =  $this->_getOption(WP_RW__SHOW_ON_MOBILE);
        
        if (isset($_POST[$rw_form_hidden_field_name]) && $_POST[$rw_form_hidden_field_name] == 'Y')
        {
            $rw_restore_defaults = (isset($_POST["rw_restore_defaults"]) && in_array($_POST["rw_restore_defaults"], array("true", "false"))) ? 
                                 $_POST["rw_restore_defaults"] : 
                                 "false";

            $rw_delete_history = (isset($_POST["rw_delete_history"]) && in_array($_POST["rw_delete_history"], array("true", "false"))) ? 
                                 $_POST["rw_delete_history"] : 
                                 "false";
            
            if ("true" === $rw_restore_defaults)
            {
                // Restore to defaults - delete all settings.
                $this->_deleteOption(WP_RW__ACTIVITY_COMMENTS_ALIGN);
                $this->_deleteOption(WP_RW__ACTIVITY_COMMENTS_OPTIONS);
                $this->_deleteOption(WP_RW__ACTIVITY_UPDATES_ALIGN);
                $this->_deleteOption(WP_RW__ACTIVITY_UPDATES_OPTIONS);
                $this->_deleteOption(WP_RW__AVAILABILITY_SETTINGS);
                $this->_deleteOption(WP_RW__USERS_ALIGN);
                $this->_deleteOption(WP_RW__USERS_OPTIONS);
                $this->_deleteOption(WP_RW__USERS_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__USERS_POSTS_OPTIONS);
                $this->_deleteOption(WP_RW__USERS_PAGES_ALIGN);
                $this->_deleteOption(WP_RW__USERS_PAGES_OPTIONS);
                $this->_deleteOption(WP_RW__USERS_COMMENTS_ALIGN);
                $this->_deleteOption(WP_RW__USERS_COMMENTS_OPTIONS);
                $this->_deleteOption(WP_RW__USERS_ACTIVITY_UPDATES_ALIGN);
                $this->_deleteOption(WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS);
                $this->_deleteOption(WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN);
                $this->_deleteOption(WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS);
                $this->_deleteOption(WP_RW__USERS_FORUM_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__USERS_FORUM_POSTS_OPTIONS);
                $this->_deleteOption(WP_RW__ACTIVITY_BLOG_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS);
                $this->_deleteOption(WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN);
                $this->_deleteOption(WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS);
                $this->_deleteOption(WP_RW__ACTIVITY_FORUM_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS);
                /*$this->_deleteOption(WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN);
                $this->_deleteOption(WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS);*/
                $this->_deleteOption(WP_RW__FORUM_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__FORUM_POSTS_OPTIONS);
                /*$this->_deleteOption(WP_RW__FORUM_TOPICS_ALIGN);
                $this->_deleteOption(WP_RW__FORUM_TOPICS_OPTIONS);*/
                $this->_deleteOption(WP_RW__BLOG_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__BLOG_POSTS_OPTIONS);
                $this->_deleteOption(WP_RW__COMMENTS_ALIGN);
                $this->_deleteOption(WP_RW__COMMENTS_OPTIONS);
                $this->_deleteOption(WP_RW__FLASH_DEPENDENCY);
                $this->_deleteOption(WP_RW__FRONT_POSTS_ALIGN);
                $this->_deleteOption(WP_RW__FRONT_POSTS_OPTIONS);
                $this->_deleteOption(WP_RW__PAGES_ALIGN);
                $this->_deleteOption(WP_RW__PAGES_OPTIONS);
                $this->_deleteOption(WP_RW__SHOW_ON_EXCERPT);
                $this->_deleteOption(WP_RW__VISIBILITY_SETTINGS);
                $this->_deleteOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS);
                
                // Re-Load all advanced settings.
                    // Flash dependency.
                    $rw_flash_dependency = $this->_getOption(WP_RW__FLASH_DEPENDENCY);

            }
            else if ("true" === $rw_delete_history)
            {
                // Delete user-key & secret.
                global $wpdb;
                $ret = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'rw_user_key' OR option_name = 'rw_user_secret'");
                
                // Goto user-key creation page.
                $this->rw_user_key_page(true);
                
                return;
            }
            else
            {
                // Save advanced settings.
                    // Get posted flash dependency.
                    if (isset($_POST["rw_flash_dependency"]) && 
                        in_array($_POST["rw_flash_dependency"], array("true", "false")) &&
                        $_POST["rw_flash_dependency"] != $rw_flash_dependency)
                    {
                        $rw_flash_dependency = $_POST["rw_flash_dependency"];
                        // Save flash dependency.
                        $this->_setOption(WP_RW__FLASH_DEPENDENCY, $rw_flash_dependency);
                    }

                    // Get mobile flag.
                    if (isset($_POST["rw_show_on_mobile"]) && 
                        in_array($_POST["rw_show_on_mobile"], array("true", "false")) &&
                        $_POST["rw_show_on_mobile"] != $rw_show_on_mobile)
                    {
                        $rw_show_on_mobile = $_POST["rw_show_on_mobile"];
                        // Save show on mobile flag.
                        $this->_setOption(WP_RW__SHOW_ON_MOBILE, $rw_show_on_mobile);
                    }
            }
?>
    <div class="updated"><p><strong><?php _e('settings saved.', WP_RW__ID ); ?></strong></p></div>
<?php
        }
        else
        {
            // Get advanced settings.
        }
?>
<div class="wrap rw-dir-ltr">
    <h2><?php echo __( 'Rating-Widget Advanced Settings', WP_RW__ID);?></h2>
    <br />
    <form id="rw_advanced_settings_form" method="post" action="">
        <div id="poststuff">
            <div style="float: left;">
                <div class="has-sidebar has-right-sidebar">
                    <div class="has-sidebar-content">
                        <div class="postbox rw-body">
                            <h3>API Details</h3>
                            <div class="inside rw-ui-content-container rw-no-radius">
                                <table cellspacing="0">
                                    <tr class="rw-odd">
                                        <td class="rw-ui-def">
                                            <span>API Key (<code>unique-user-key</code>):</span>
                                        </td>
                                        <td><span style="font-size: 14px; color: green;"><?php echo (false === WP_RW__USER_KEY) ? "NONE" : strtolower(WP_RW__USER_KEY);?></span></td>
                                    </tr>    
                                    <tr class="rw-even">
                                        <td class="rw-ui-def">
                                            <span>Secret Key (only for <a href="<?php echo WP_RW__ADDRESS;?>/get-the-word-press-plugin/" target="_blank">pro</a> users):</span>
                                        </td>
                                        <td><span style="font-size: 14px; color: green;"><?php echo (false === WP_RW__USER_SECRET) ? "NONE" : strtolower(WP_RW__USER_SECRET);?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="rw_flash_settings" class="has-sidebar has-right-sidebar">
                    <div class="has-sidebar-content">
                        <div class="postbox rw-body">
                            <h3>Flash Dependency</h3>
                            <div class="inside rw-ui-content-container rw-no-radius" style="padding: 5px; width: 610px;">
                                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_flash_dependency == "true") echo ' rw-selected';?>">
                                    <i class="rw-ui-sprite rw-ui-flash"></i> <input type="radio" name="rw_flash_dependency" value="true" <?php if ($rw_flash_dependency == "true") echo ' checked="checked"';?>> <span>Enable Flash dependency (track computers using LSO).</span>
                                </div>
                                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_flash_dependency == "false") echo ' rw-selected';?>">
                                    <i class="rw-ui-sprite rw-ui-flash-disabled"></i> <input type="radio" name="rw_flash_dependency" value="false" <?php if ($rw_flash_dependency == "false") echo ' checked="checked"';?>> <span>Disable Flash dependency (computers with identical IPs won't be distinguished).</span>
                                </div>
                                <span style="font-size: 10px; background: white; padding: 2px; border: 1px solid gray; display: block; margin-top: 5px; font-weight: bold; background: rgb(240,240,240); color: black;">Flash dependency <b style="text-decoration: underline;">don't</b> means that if a user don't have a flash player installed on his browser then it will stuck. The reason to disable flash is for users which have flash blocking add-ons (e.g. FF Flashblock add-on), which is quite rare.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="rw_mobile_settings" class="has-sidebar has-right-sidebar">
                    <div class="has-sidebar-content">
                        <div class="postbox rw-body">
                            <h3>Mobile Settings</h3>
                            <div class="inside rw-ui-content-container rw-no-radius" style="padding: 5px; width: 610px;">
                                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_show_on_mobile == "true") echo ' rw-selected';?>">
                                    <i class="rw-ui-sprite rw-ui-mobile"></i> <input type="radio" name="rw_show_on_mobile" value="true" <?php if ($rw_show_on_mobile == "true") echo ' checked="checked"';?>> <span>Show ratings on Mobile devices.</span>
                                </div>
                                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_show_on_mobile == "false") echo ' rw-selected';?>">
                                    <i class="rw-ui-sprite rw-ui-mobile-disabled"></i> <input type="radio" name="rw_show_on_mobile" value="false" <?php if ($rw_show_on_mobile == "false") echo ' checked="checked"';?>> <span>Hide ratings on Mobile devices.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="rw_critical_actions" class="has-sidebar has-right-sidebar">
                    <div class="has-sidebar-content">
                        <div class="postbox rw-body">
                            <h3>Critical Actions</h3>
                            <div class="inside rw-ui-content-container rw-no-radius">
                                <script type="text/javascript">
                                    (function($){
                                        if (typeof(RWM) === "undefined"){ RWM = {}; }
                                        if (typeof(RWM.Set) === "undefined"){ RWM.Set = {}; }
                                        
                                        RWM.Set.clearHistory = function(event)
                                        {
                                            if (confirm("Are you sure you want to delete all your ratings history?"))
                                            {
                                                $("#rw_delete_history").val("true");
                                                $("#rw_advanced_settings_form").submit(); 
                                            }
                                            
                                            event.stopPropagation();
                                        };
                                        
                                        RWM.Set.restoreDefaults = function(event)
                                        {
                                            if (confirm("Are you sure you want to restore to factory settings?"))
                                            {
                                                $("#rw_restore_defaults").val("true");
                                                $("#rw_advanced_settings_form").submit(); 
                                            }
                                            
                                            event.stopPropagation();
                                        };
                                        
                                        $(document).ready(function(){
                                            $("#rw_delete_history_con .rw-ui-button").click(RWM.Set.clearHistory);
                                            $("#rw_delete_history_con .rw-ui-button input").click(RWM.Set.clearHistory);

                                            $("#rw_restore_defaults_con .rw-ui-button").click(RWM.Set.restoreDefaults);
                                            $("#rw_restore_defaults_con .rw-ui-button input").click(RWM.Set.restoreDefaults);
                                        });
                                    })(jQuery);
                                </script>
                                <table cellspacing="0">
                                    <tr class="rw-odd" id="rw_restore_defaults_con">
                                        <td class="rw-ui-def">
                                            <input type="hidden" id="rw_restore_defaults" name="rw_restore_defaults" value="false" />
                                            <span class="rw-ui-button" onclick="RWM.firstUse();">
                                                <input type="button" style="background: none;" value="Restore to Defaults" onclick="RWM.firstUse();" />
                                            </span>
                                        </td>
                                        <td><span>Restore all Rating-Widget settings to factory.</span></td>
                                    </tr>    
                                    <tr class="rw-even" id="rw_delete_history_con">
                                        <td>
                                            <input type="hidden" id="rw_delete_history" name="rw_delete_history" value="false" />
                                            <span class="rw-ui-button rw-ui-critical">
                                                <input type="button" style="background: none;" value="Delete History" />
                                            </span>
                                        </td>
                                        <td><span>Delete your unique-user-key and generate new one.</span><br /><span><b style="color: red;">Notice: All your ratings data will be deleted.</b></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-left: 650px; width: 350px; padding-right: 20px; position: fixed;">
                <?php require_once(dirname(__FILE__) . "/view/save.php"); ?>
            </div>            
        </div>
    </form>
</div>
<?php                
    }
    
    function rw_settings_page()
    {
        // Must check that the user has the required capability.
        if (!current_user_can('manage_options')){
          wp_die(__('You do not have sufficient permissions to access this page.', WP_RW__ID) );
        }

        $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : false;
        
        if ($action == "reports")
        {
            if (false === WP_RW__USER_SECRET)
            {
                $this->rw_report_example_page();
            }
            else if (isset($_GET["urid"]) && is_numeric($_GET["urid"]))
            {
                $this->rw_rating_report_page();
            }
            else if (isset($_GET["ip"]) || isset($_GET["vid"]) || isset($_GET["pcid"]))
            {
                $this->rw_explicit_report_page();
            }
            else
            {
                $this->rw_general_report_page();
            }

//            if (RWLogger::IsOn())
//            {
                echo "\n<!-- RATING-WIDGET LOG START\n\n";
                RWLogger::Output("    ");
                echo "\n RATING-WIDGET LOG END-->\n";
//            }

            return;
        }
        else if ($action == "advanced")
        {
            $this->rw_advanced_settings_page();

            if (RWLogger::IsOn())
            {
                echo "\n<!-- RATING-WIDGET LOG START\n\n";
                RWLogger::Output("    ");
                echo "\n RATING-WIDGET LOG END-->\n";
            }
            
            return;
        }
        else if ($action == "boost")
        {
            if (false !== WP_RW__USER_SECRET)
            {
                $this->rw_boost_page();
            }
            
            return;
        }
        
        // Variables for the field and option names 
        $rw_form_hidden_field_name = "rw_form_hidden_field_name";

        
        if (($action === "buddypress" && self::$WP_RW__BP_INSTALLED) && is_plugin_active(WP_RW__BP_CORE_FILE))
        {
            $settings_data = array(
                "activity-blog-posts" => array(
                    "tab" => "Activity Blog Posts",
                    "class" => "new-blog-post",
                    "options" => WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS,
                    "align" => WP_RW__ACTIVITY_BLOG_POSTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__ACTIVITY_BLOG_POSTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
                "activity-blog-comments" => array(
                    "tab" => "Activity Blog Comments",
                    "class" => "new-blog-comment",
                    "options" => WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS,
                    "align" => WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
                "activity-updates" => array(
                    "tab" => "Activity Updates",
                    "class" => "activity-update",
                    "options" => WP_RW__ACTIVITY_UPDATES_OPTIONS,
                    "align" => WP_RW__ACTIVITY_UPDATES_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__ACTIVITY_UPDATES_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
                "activity-comments" => array(
                    "tab" => "Activity Comments",
                    "class" => "activity-comment",
                    "options" => WP_RW__ACTIVITY_COMMENTS_OPTIONS,
                    "align" => WP_RW__ACTIVITY_COMMENTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__ACTIVITY_COMMENTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
                "users" => array(
                    "tab" => "Users Profiles",
                    "class" => "user",
                    "options" => WP_RW__USERS_OPTIONS,
                    "align" => WP_RW__USERS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_ALIGN],
                    "excerpt" => false,
                    "show_align" => false,
                ),
            );

            $selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "activity-blog-posts";
            if (!isset($settings_data[$selected_key])){ $selected_key = "activity-blog-posts"; }
        }
        else if (($action === "bbpress" && defined('WP_RW__BBP_INSTALLED')) && is_plugin_active(WP_RW__BP_CORE_FILE))
        {
            $settings_data = array(
                /*"forum-topics" => array(
                    "tab" => "Forum Topics",
                    "class" => "forum-topic",
                    "options" => WP_RW__FORUM_TOPICS_OPTIONS,
                    "align" => WP_RW__FORUM_TOPICS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__FORUM_TOPICS_ALIGN],
                    "excerpt" => false,
                ),*/
                "forum-posts" => array(
                    "tab" => "Forum Posts",
                    "class" => "forum-post",
                    "options" => WP_RW__FORUM_POSTS_OPTIONS,
                    "align" => WP_RW__FORUM_POSTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__FORUM_POSTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
                /*"activity-forum-topics" => array(
                    "tab" => "Activity Forum Topics",
                    "class" => "new-forum-topic",
                    "options" => WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS,
                    "align" => WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN],
                    "excerpt" => false,
                ),*/
                "activity-forum-posts" => array(
                    "tab" => "Activity Forum Posts",
                    "class" => "new-forum-post",
                    "options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS,
                    "align" => WP_RW__ACTIVITY_FORUM_POSTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__ACTIVITY_FORUM_POSTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
            );
            
            $selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "forum-posts";
            if (!isset($settings_data[$selected_key])){ $selected_key = "forum-posts"; }
        }
        else if ($action === "user")
        {
            $settings_data = array(
                "users-posts" => array(
                    "tab" => "Posts",
                    "class" => "user-post",
                    "options" => WP_RW__USERS_POSTS_OPTIONS,
                    "align" => WP_RW__USERS_POSTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_POSTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => false,
                ),
                "users-pages" => array(
                    "tab" => "Pages",
                    "class" => "user-page",
                    "options" => WP_RW__USERS_PAGES_OPTIONS,
                    "align" => WP_RW__USERS_PAGES_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_PAGES_ALIGN],
                    "excerpt" => false,
                    "show_align" => false,
                ),
                "users-comments" => array(
                    "tab" => "Comments",
                    "class" => "user-comment",
                    "options" => WP_RW__USERS_COMMENTS_OPTIONS,
                    "align" => WP_RW__USERS_COMMENTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_COMMENTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => false,
                ),
            );
            
            if (self::$WP_RW__BP_INSTALLED && is_plugin_active(WP_RW__BP_CORE_FILE))
            {
                $settings_data["users-activity-updates"] = array(
                    "tab" => "Activity Updates",
                    "class" => "user-activity-update",
                    "options" => WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS,
                    "align" => WP_RW__USERS_ACTIVITY_UPDATES_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_ACTIVITY_UPDATES_ALIGN],
                    "excerpt" => false,
                    "show_align" => false,
                );
                $settings_data["users-activity-comments"] = array(
                    "tab" => "Activity Comments",
                    "class" => "user-activity-comment",
                    "options" => WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS,
                    "align" => WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_ACTIVITY_COMMENTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => false,
                );
                
                if (defined('WP_RW__BBP_INSTALLED'))
                {
                    $settings_data["users-forum-posts"] = array(
                        "tab" => "Forum Posts",
                        "class" => "user-forum-post",
                        "options" => WP_RW__USERS_FORUM_POSTS_OPTIONS,
                        "align" => WP_RW__USERS_FORUM_POSTS_ALIGN,
                        "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__USERS_FORUM_POSTS_ALIGN],
                        "excerpt" => false,
                        "show_align" => false,
                    );
                }
            }

            $selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "users-posts";
            if (!isset($settings_data[$selected_key])){ $selected_key = "users-posts"; }
        }
        else
        {
            $settings_data = array(
                "blog-posts" => array(
                    "tab" => "Blog Posts",
                    "class" => "blog-post",
                    "options" => WP_RW__BLOG_POSTS_OPTIONS,
                    "align" => WP_RW__BLOG_POSTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__BLOG_POSTS_ALIGN],
                    "excerpt" => true,
                    "show_align" => true,
                ),
                "front-posts" => array(
                    "tab" => "Front Page Posts",
                    "class" => "front-post",
                    "options" => WP_RW__FRONT_POSTS_OPTIONS,
                    "align" => WP_RW__FRONT_POSTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__FRONT_POSTS_ALIGN],
                    "excerpt" => true,
                    "show_align" => true,
                ),
                "comments" => array(
                    "tab" => "Comments",
                    "class" => "comment",
                    "options" => WP_RW__COMMENTS_OPTIONS,
                    "align" => WP_RW__COMMENTS_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__COMMENTS_ALIGN],
                    "excerpt" => false,
                    "show_align" => true,
                ),
                "pages" => array(
                    "tab" => "Pages",
                    "class" => "page",
                    "options" => WP_RW__PAGES_OPTIONS,
                    "align" => WP_RW__PAGES_ALIGN,
                    "default_align" => self::$OPTIONS_DEFAULTS[WP_RW__PAGES_ALIGN],
                    "excerpt" => true,
                    "show_align" => true,
                ),
            );
            
            $selected_key = isset($_GET["rating"]) ? $_GET["rating"] : "blog-posts";
            if (!isset($settings_data[$selected_key])){ $selected_key = "blog-posts"; }
        }
        
        $rw_current_settings = $settings_data[$selected_key];

        // Show on excerpts list must be loaded anyway.
        $this->show_on_excerpts_list = json_decode($this->_getOption(WP_RW__SHOW_ON_EXCERPT));
        
        // Visibility list must be loaded anyway.
        $this->_visibilityList = json_decode($this->_getOption(WP_RW__VISIBILITY_SETTINGS));

        // Categories Availability list must be loaded anyway.
        $this->categories_list = json_decode($this->_getOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS));

        // Availability list must be loaded anyway.
        $this->availability_list = json_decode($this->_getOption(WP_RW__AVAILABILITY_SETTINGS));

        // Some alias.
        $rw_class = $rw_current_settings["class"];
        
        // See if the user has posted us some information
        // If they did, this hidden field will be set to 'Y'
        if (isset($_POST[$rw_form_hidden_field_name]) && $_POST[$rw_form_hidden_field_name] == 'Y')
        {
            /* Widget align options.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_show_rating = isset($_POST["rw_show"]) ? true : false;
            $rw_align_str =  (!$rw_show_rating) ? "{}" : $rw_current_settings["default_align"];
            if ($rw_show_rating && isset($_POST["rw_align"]))
            {
                $align = explode(" ", $_POST["rw_align"]);
                if (is_array($align) && count($align) == 2)
                {
                    if (in_array($align[0], array("top", "bottom")) &&
                        in_array($align[1], array("left", "center", "right")))
                    {
                        $rw_align_str = '{"ver": "' . $align[0] . '", "hor": "' . $align[1] . '"}';
                    }
                }
            }
            $this->_setOption($rw_current_settings["align"], $rw_align_str);

            /* Show on excerpts.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_show_on_excerpts = false;
            if ($rw_current_settings["excerpt"] === true)
            {
                $rw_show_on_excerpts = isset($_POST["rw_show_excerpt"]) ? true : false;
                $this->show_on_excerpts_list->{$rw_class} = $rw_show_on_excerpts;
                $this->_setOption(WP_RW__SHOW_ON_EXCERPT, json_encode($this->show_on_excerpts_list));
            }
            
            /* Rating-Widget options.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_options_str = preg_replace('/\%u([0-9A-F]{4})/i', '\\u$1', urldecode($_POST["rw_options"]));
            if (null !== json_decode($rw_options_str)){
                $this->_setOption($rw_current_settings["options"], $rw_options_str);
            }
            
            /* Availability settings.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_availability = isset($_POST["rw_availability"]) ? max(0, min(2, (int)$_POST["rw_availability"])) : 0;
            
            $this->availability_list->{$rw_class} = $rw_availability;
            $this->_setOption(WP_RW__AVAILABILITY_SETTINGS, json_encode($this->availability_list));
            
            /* Categories Availability settings.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_categories = isset($_POST["rw_categories"]) ? $_POST["rw_categories"] : array();
            
            $this->categories_list->{$rw_class} = (in_array("-1", $_POST["rw_categories"]) ? array("-1") : $rw_categories);
            $this->_setOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS, json_encode($this->categories_list));
            
            /* Visibility settings
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_visibility = isset($_POST["rw_visibility"]) ? max(0, min(2, (int)$_POST["rw_visibility"])) : 0;
            $rw_visibility_exclude  = isset($_POST["rw_visibility_exclude"]) ? $_POST["rw_visibility_exclude"] : "";
            $rw_visibility_include  = isset($_POST["rw_visibility_include"]) ? $_POST["rw_visibility_include"] : "";
            
            $this->_visibilityList->{$rw_class}->selected = $rw_visibility;
            $this->_visibilityList->{$rw_class}->exclude = self::IDsCollectionToArray($rw_visibility_exclude);
            $this->_visibilityList->{$rw_class}->include = self::IDsCollectionToArray($rw_visibility_include);
            $this->_setOption(WP_RW__VISIBILITY_SETTINGS, json_encode($this->_visibilityList));
    ?>
    <div class="updated"><p><strong><?php _e('settings saved.', WP_RW__ID ); ?></strong></p></div>
    <?php
        }
        else
        {
            /* Get rating alignment.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_align_str = $this->_getOption($rw_current_settings["align"]);

            /* Get show on excerpts option.
            ---------------------------------------------------------------------------------------------------------------*/
                // Already loaded.

            /* Get rating options.
            ---------------------------------------------------------------------------------------------------------------*/
            $rw_options_str = $this->_getOption($rw_current_settings["options"]);
            
            /* Get availability settings.
            ---------------------------------------------------------------------------------------------------------------*/
                // Already loaded.

            /* Get visibility settings
            ---------------------------------------------------------------------------------------------------------------*/
                // Already loaded.
        }
        
            
        $rw_align = json_decode($rw_align_str);
        
        $rw_options = json_decode($rw_options_str);
        $rw_language_str = isset($rw_options->lng) ? $rw_options->lng : WP_RW__DEFAULT_LNG;
        
        if (!isset($this->_visibilityList->{$rw_class}))
        {
            $this->_visibilityList->{$rw_class} = new stdClass();
            $this->_visibilityList->{$rw_class}->selected = 0;
            $this->_visibilityList->{$rw_class}->exclude = "";
            $this->_visibilityList->{$rw_class}->include = "";
        }
        $rw_visibility_settings = $this->_visibilityList->{$rw_class};
        
        if (!isset($this->availability_list->{$rw_class})){
            $this->availability_list->{$rw_class} = 0;
        }
        $rw_availability_settings = $this->availability_list->{$rw_class};

        if (!isset($this->categories_list->{$rw_class})){
            $this->categories_list->{$rw_class} = array(-1);
        }
        $rw_categories = $this->categories_list->{$rw_class};
        
        if (!isset($this->show_on_excerpts_list->{$rw_class})){
            $this->show_on_excerpts_list->{$rw_class} = true;
        }
        $rw_show_on_excerpts = $this->show_on_excerpts_list->{$rw_class};
        
        require_once(WP_RW__PLUGIN_DIR . "/languages/{$rw_language_str}.php");
        require_once(WP_RW__PLUGIN_DIR . "/lib/defaults.php");
        require_once(WP_RW__PLUGIN_DIR . "/lib/def_settings.php");
        /*$rw_options_type = isset($rw_options->type) ? $rw_options->type : "star";
        if ($rw_options_type == "nero"){
            unset($rw_options->type);
            $rw_options_str = json_encode($rw_options);
            $rw_options->type = "nero";
        }*/
        
        global $DEFAULT_OPTIONS;
        rw_set_language_options($DEFAULT_OPTIONS, $dictionary, $dir, $hor);
        
        $rating_font_size_set = false;
        $rating_line_height_set = false;
        $theme_font_size_set = false;
        $theme_line_height_set = false;

        $rating_font_size_set = (isset($rw_options->advanced) && isset($rw_options->advanced->font) && isset($rw_options->advanced->font->size));
        $rating_line_height_set = (isset($rw_options->advanced) && isset($rw_options->advanced->layout) && isset($rw_options->advanced->layout->lineHeight));
        
        $def_options = $DEFAULT_OPTIONS;
        if (isset($rw_options->theme) && $rw_options->theme !== "")
        {
            require_once(WP_RW__PLUGIN_DIR . "/themes/dir.php");
            
            global $RW_THEMES;
            
            if (!isset($rw_options->type)){
                $rw_options->type = isset($RW_THEMES["star"][$rw_options->theme]) ? "star" : "nero";
            }
            if (isset($RW_THEMES[$rw_options->type][$rw_options->theme]))
            {
                require(WP_RW__PLUGIN_DIR . "/themes/" . $RW_THEMES[$rw_options->type][$rw_options->theme]["file"]);

                $theme_font_size_set = (isset($theme["options"]->advanced) && isset($theme["options"]->advanced->font) && isset($theme["options"]->advanced->font->size));
                $theme_line_height_set = (isset($theme["options"]->advanced) && isset($theme["options"]->advanced->layout) && isset($theme["options"]->advanced->layout->lineHeight));

                // Enrich theme options with defaults.
                $def_options = rw_enrich_options1($theme["options"], $DEFAULT_OPTIONS);
            }
        }

        // Enrich rating options with calculated default options (with theme reference).
        $rw_options = rw_enrich_options1($rw_options, $def_options);

        // If font size and line height isn't explicitly specified on rating
        // options or rating's theme, updated theme correspondingly
        // to rating size. 
        if (isset($rw_options->size))
        {
            $SIZE = strtoupper($rw_options->size);
            if (!$rating_font_size_set && !$theme_font_size_set)
            {
                global $DEF_FONT_SIZE;
                if (!isset($rw_options->advanced)){ $rw_options->advanced = new stdClass(); }
                if (!isset($rw_options->advanced->font)){ $rw_options->advanced->font = new stdClass(); }
                $rw_options->advanced->font->size = $DEF_FONT_SIZE->$SIZE;
            }
            if (!$rating_line_height_set && !$theme_line_height_set)
            {
                global $DEF_LINE_HEIGHT;
                if (!isset($rw_options->advanced)){ $rw_options->advanced = new stdClass(); }
                if (!isset($rw_options->advanced->layout)){ $rw_options->advanced->layout = new stdClass(); }
                $rw_options->advanced->layout->lineHeight = $DEF_LINE_HEIGHT->$SIZE;
            }
        }
        
        $rw_enrich_options_str = json_encode($rw_options);

        $browser_info = array("browser" => "msie", "version" => "7.0");
        $rw_languages = $this->languages;
    ?>
<div class="wrap rw-dir-ltr">
    <h2><?php echo __( 'Rating-Widget Settings', WP_RW__ID);?></h2>
    <form method="post" action="">
        <div id="poststuff">       
            <div style="float: left;">
                <div id="side-sortables"> 
                    <div id="categorydiv" class="categorydiv">
                        <ul id="category-tabs" class="category-tabs" style="height: 21px;">
                            <?php
                                foreach ($settings_data as $key => $settings)
                                {
                                    if ($settings_data[$key] == $rw_current_settings)
                                    {
                                ?>
                                    <li class="tabs" style="float: left;"><?php echo _e($settings["tab"], WP_RW__ID);?></li>
                                <?php
                                    }
                                    else
                                    {
                                ?>
                                    <li style="float: left;"><a href="<?php echo esc_url(add_query_arg(array('rating' => $key, 'message' => false)));?>"><?php echo _e($settings["tab"], WP_RW__ID);?></a></li>
                                <?php
                                    }
                                }
                            ?>
                        </ul>
                        <div class="tabs-panel rw-body" id="categories-all" style="background: white; height: auto; overflow: visible; width: 602px;">
                            <?php
                                $enabled = isset($rw_align->ver);
                            ?>
                            <div class="rw-ui-content-container rw-ui-light-bkg" style="width: 580px; margin: 10px 0 10px 0;">
                                <label for="rw_show">
                                    <input id="rw_show" type="checkbox" name="rw_show" value="true"<?php if ($enabled) echo ' checked="checked"';?> onclick="RWM_WP.enable(this);" /> Enable for <?php echo $rw_current_settings["tab"];?>
                                </label>
                            <?php
                                if (true === $rw_current_settings["show_align"])
                                {
                            ?>
                                <br />
                                <div class="rw-post-rating-align" style="height: 220px;">
                                    <div class="rw-ui-disabled"<?php if ($enabled) echo ' style="display: none;"';?>></div>
                                <?php
                                    $vers = array("top", "bottom");
                                    $hors = array("left", "center", "right");
                                    
                                    foreach ($vers as $ver)
                                    {
                                ?>
                                    <div style="height: 89px; padding: 5px;">
                                <?php
                                        foreach ($hors as $hor)
                                        {
                                            $checked = false;
                                            if ($enabled){
                                                $checked = ($ver == $rw_align->ver && $hor == $rw_align->hor);
                                            }
                                ?>
                                        <div class="rw-ui-img-radio<?php if ($checked) echo ' rw-selected';?>">
                                            <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-post-<?php echo $ver . $hor;?>"></i></i>
                                            <span><?php echo ucwords($ver) . ucwords($hor);?></span>
                                            <input type="radio" name="rw_align" value="<?php echo $ver . " " . $hor;?>"<?php if ($checked) echo ' checked="checked"';?> />
                                        </div>
                                <?php
                                        }
                                ?>
                                    </div>
                                <?php
                                    }
                                    
                                    if (true === $rw_current_settings["excerpt"])
                                    {
                                ?>
                                    <label for="rw_show_excerpt" style="margin-left: 20px; font-weight: bold;">
                                        <input id="rw_show_excerpt" type="checkbox" name="rw_show_excerpt" value="true"<?php if ($rw_show_on_excerpts) echo ' checked="checked"';?> /> Show on excerpts as well.
                                    </label>
                                <?php
                                    }
                                ?>
                                </label>
                                </div>
                            <?php
                                }
                            ?>
                            </div>
                        </div>
                    </div>
                </div>
                <br />
                <?php require_once(dirname(__FILE__) . "/view/options.php"); ?>
                <?php require_once(dirname(__FILE__) . "/view/availability_options.php"); ?>
                <?php require_once(dirname(__FILE__) . "/view/visibility_options.php"); ?>
                <?php require_once(dirname(__FILE__) . "/view/categories_availability_options.php"); ?>
            </div>
            <div id="rw_floating_container">
                <?php require_once(dirname(__FILE__) . "/view/preview.php"); ?>
                <?php // require_once(dirname(__FILE__) . "/view/save.php"); ?>
            </div>
            <div style="margin-left: 650px; padding-top: 215px; width: 350px; padding-right: 20px;">
                <?php require_once(dirname(__FILE__) . "/view/twitter.php"); ?>
                <?php require_once(dirname(__FILE__) . "/view/fb.php"); ?>
                <?php require_once(dirname(__FILE__) . "/view/sponsor.php"); ?>
            </div>
        </div>
    </form>
    <div class="rw-body">
    <?php include_once(WP_RW__PLUGIN_DIR . "/view/settings/custom_color.php");?>
    </div>
</div>

<?php
    }
    
    /* Posts/Pages & Comments Support
    ---------------------------------------------------------------------------------------------------------------*/
    var $post_align = false;
    var $post_class = "";
    var $comment_align = false;
    /**
    * This action invoked when WP starts looping over
    * the posts/pages. This function checks if Rating-Widgets
    * on posts/pages and/or comments are enabled, and saved
    * the settings alignment.
    */
    function rw_before_loop_start()
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_before_loop_start", $params); }
        
        $comment_align_str = $this->_getOption(WP_RW__COMMENTS_ALIGN);
        $comment_align = json_decode($comment_align_str);
        $comment_enabled = (isset($comment_align) && isset($comment_align->hor));
        if ($comment_enabled && WP_RW__AVAILABILITY_HIDDEN !== $this->rw_validate_availability("comment"))
        {
            $this->comment_align = $comment_align;
            
            // Hook comment rating showup.
            add_action('comment_text', array(&$this, "rw_display_comment_rating"));
        }
        
        if (is_page())
        {
            // Get rating pages alignment.
            $post_align_str = $this->_getOption(WP_RW__PAGES_ALIGN);
            $post_class = "page";
        }
        else if (is_home())
        {
            // Get rating front posts alignment.
            $post_align_str = $this->_getOption(WP_RW__FRONT_POSTS_ALIGN);
            $post_class = "front-post";
        }
        else
        {
            // Get rating blog posts alignment.
            $post_align_str = $this->_getOption(WP_RW__BLOG_POSTS_ALIGN);
            $post_class = "blog-post";
        }          
        $post_align = json_decode($post_align_str);
        
        $post_enabled = (isset($post_align) && isset($post_align->hor));

        if (/*$post_enabled && */WP_RW__AVAILABILITY_HIDDEN !== $this->rw_validate_availability($post_class))
        {
            $this->post_align = $post_align;
            $this->post_class = $post_class;

            // Hook post rating showup.
            add_action('the_content', array(&$this, "rw_display_post_rating"));
//            add_action('the_title', array(&$this, "rw_add_title_metadata"));
//            add_action('post_class', array(&$this, "rw_add_article_metadata"));
            
            if (!isset($this->show_on_excerpts_list)){
                $this->show_on_excerpts_list = json_decode($this->_getOption(WP_RW__SHOW_ON_EXCERPT));
            }
            
            if ($this->show_on_excerpts_list->{$post_class} === true)
            {
                // Hook post excerpt rating showup.
                add_action('the_excerpt', array(&$this, "rw_display_post_rating"));
            }
        }
    }
    
    static function IDsCollectionToArray(&$pIds)
    {
        if (null == $pIds || (is_string($pIds) && empty($pIds)))
            return array();

        if (!is_string($pIds) && is_array($pIds))
            return $pIds;
        
        $ids = explode(",", $pIds);
        $filtered = array();
        foreach ($ids as $id)
        {
            $id = trim($id);
            
            if (is_numeric($id))
                $filtered[] = $id;
        }
        
        return array_unique($filtered);
    }

    function rw_validate_category_availability($pId, $pClass)
    {
        if (!isset($this->categories_list)){
            $this->categories_list = json_decode($this->_getOption(WP_RW__CATEGORIES_AVAILABILITY_SETTINGS));
        }
        
        if (!isset($this->categories_list->{$pClass})){ return true; }
        
        // Alias.
        $categories = $this->categories_list->{$pClass};
        
        // Check if all categories.
        if (!is_array($categories) || in_array("-1", $categories)){ return true; }
        
        // No category selected.
        if (count($categories) == 0){ return false; }
        
        // Get post categories.
        $post_categories = get_the_category($pId);
        
        $post_categories_ids = array();
        
        if (is_array($post_categories) && count($post_categories) > 0)
        {
            foreach ($post_categories as $category)
            {
                $post_categories_ids[] = $category->cat_ID;
            }
        }

        $common_categories = array_intersect($categories, $post_categories_ids);

        return (is_array($common_categories) && count($common_categories) > 0);
    }
        
    function rw_validate_visibility($pId, $pClasses = false)
    {
        if (!isset($this->_visibilityList)){
            $this->_visibilityList = json_decode($this->_getOption(WP_RW__VISIBILITY_SETTINGS));
        }
        
        if (is_string($pClasses))
        {
            $pClasses = array($pClasses);
        }
        else if (false === $pClasses)
        {
            foreach ($this->_visibilityList as $class => $val)
            {
                $pClasses[] = $class;
            }
        }
        
        foreach ($pClasses as $class)
        {
            if (!isset($this->_visibilityList->{$class}))
                continue;
            
            // Alias.
            $visibility_list = $this->_visibilityList->{$class};
            
            // All visible.
            if ($visibility_list->selected === WP_RW__VISIBILITY_ALL_VISIBLE)
                continue;

            $visibility_list->exclude = self::IDsCollectionToArray($visibility_list->exclude);
            $visibility_list->include = self::IDsCollectionToArray($visibility_list->include);

            if (($visibility_list->selected === WP_RW__VISIBILITY_EXCLUDE && in_array($pId, $visibility_list->exclude)) ||
                ($visibility_list->selected === WP_RW__VISIBILITY_INCLUDE && !in_array($pId, $visibility_list->include)))
            {
                return false;
            }
        }
        
        return true;
    }
    
    function AddToVisibility($pId, $pClasses, $pIsVisible = true)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("AddToVisibility", $params, true); }
        
        if (!isset($this->_visibilityList)){
            $this->_visibilityList = json_decode($this->_getOption(WP_RW__VISIBILITY_SETTINGS));
        }

        if (is_string($pClasses))
        {
            $pClasses = array($pClasses);
        }
        else if (!is_array($pClasses) || 0 == count($pClasses))
        {
            return;
        }
        
        foreach ($pClasses as $class)
        {
            if (RWLogger::IsOn()){ RWLogger::Log("AddToVisibility", "CurrentClass = ". $class); }
            
            if (!isset($this->_visibilityList->{$class}))
            {
                $this->_visibilityList->{$class} = new stdClass();
                $this->_visibilityList->{$class}->selected = WP_RW__VISIBILITY_ALL_VISIBLE;
            }
            
            $visibility_list = $this->_visibilityList->{$class};
            
            if (!isset($visibility_list->include) || empty($visibility_list->include))
                $visibility_list->include = array();
            
            $visibility_list->include = self::IDsCollectionToArray($visibility_list->include);
                
            if (!isset($visibility_list->exclude) || empty($visibility_list->exclude))
                $visibility_list->exclude = array();
                
            $visibility_list->exclude = self::IDsCollectionToArray($visibility_list->exclude);
                
            if ($visibility_list->selected == WP_RW__VISIBILITY_ALL_VISIBLE)
            {
                if (RWLogger::IsOn()){ RWLogger::Log("AddToVisibility", "Currently All-Visible for {$class}"); }
                
                if (true == $pIsVisible)
                {
                    // Already all visible so just ignore this.
                }
                else
                {
                    // If all visible, and selected to hide this post - exclude specified post/page.
                    $visibility_list->selected = WP_RW__VISIBILITY_EXCLUDE;
                    $visibility_list->exclude[] = $pId;
                }
            }
            else
            {
                // If not all visible, move post id from one list to another (exclude/include).

                if (RWLogger::IsOn()){ RWLogger::Log("AddToVisibility", "Currently NOT All-Visible for {$class}"); }
                
                $remove_from = ($pIsVisible ? "exclude" : "include");
                $add_to = ($pIsVisible ? "include" : "exclude");

                if (RWLogger::IsOn()){ RWLogger::Log("AddToVisibility", "Remove {$pId} from {$class}'s " . strtoupper(($pIsVisible ? "exclude" : "include")) . "list."); }
                if (RWLogger::IsOn()){ RWLogger::Log("AddToVisibility", "Add {$pId} to {$class}'s " . strtoupper((!$pIsVisible ? "exclude" : "include")) . "list."); }

                if (!in_array($pId, $visibility_list->{$add_to}))
                    // Add to include list.
                    $visibility_list->{$add_to}[] = $pId;

                if (($key = array_search($pId, $visibility_list->{$remove_from})) !== false)
                    // Remove from exclude list.
                    $remove_from = array_splice($visibility_list->{$remove_from}, $key, 1);
                    
                if (WP_RW__VISIBILITY_EXCLUDE == $visibility_list->selected && 0 === count($visibility_list->exclude))
                    $visibility_list->selected = WP_RW__VISIBILITY_ALL_VISIBLE;
            }
        }
        
        if (RWLogger::IsOn()){ RWLogger::LogDeparture("AddToVisibility"); }
    }
    
    function SaveVisibility()
    {
        $this->_setOption(WP_RW__VISIBILITY_SETTINGS, json_encode($this->_visibilityList));
    }
    
    var $is_user_logged_in;
    function rw_validate_availability($pClass)
    {
        if (!isset($this->is_user_logged_in))
        {
            // Check if user logged in for availability check.
            $this->is_user_logged_in = is_user_logged_in();

            $this->availability_list = json_decode($this->_getOption(WP_RW__AVAILABILITY_SETTINGS));
        }
        
        if (true === $this->is_user_logged_in ||
            !isset($this->availability_list->{$pClass}))
        {
            return WP_RW__AVAILABILITY_ACTIVE;
        }
        
        return $this->availability_list->{$pClass};
    }
    
    /**
    * If Rating-Widget enabled for Posts, attach it
    * html container to the post content at the right position.
    * 
    * @param {string} $content
    */
    function rw_display_post_rating($content)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_post_rating", $params); }
        
        global $post;
        
        // Check if post category is selected.
        if (false === $this->rw_validate_category_availability($post->ID, $this->post_class)){ return $content; }
        
        // Checks if post isn't specificaly excluded.
        if (false === $this->rw_validate_visibility($post->ID, $this->post_class)){ return $content; }

        $urid = $this->_getPostRatingGuid();
        
        if ($this->rw_has_inline_rating($content))
        {
            $content = str_replace("[ratingwidget]", $this->EmbedRating($urid, $post->post_title, get_permalink($post->ID), $this->post_class, true), $content);
        }
        
        $post_enabled = (isset($this->post_align) && isset($this->post_align->hor));

        if ($post_enabled)
        {
            $rw = '<div class="rw-' . $this->post_align->hor . '">'.
                  $this->EmbedRating($urid, $post->post_title, get_permalink($post->ID), $this->post_class, true).
                  '</div>';
            return ($this->post_align->ver == "top") ?
                    $rw . $content :
                    $content . $rw;
        }
        else
        {
            return $content;
        }
    }
    
    /**
    * If Rating-Widget enabled for Comments, attach it
    * html container to the comment content at the right position.
    * 
    * @param {string} $content
    */
    function rw_display_comment_rating($content)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_comment_rating", $params); }
        
        global $post, $comment;

        if (false === $this->rw_validate_visibility($comment->comment_ID, "comment")){ return $content; }
        
        $urid = $this->_getCommentRatingGuid();
        self::QueueRatingData($urid, strip_tags($comment->comment_content), get_permalink($post->ID) . '#comment-' . $comment->comment_ID, "comment");
        
        $rw = '<div class="rw-' . $this->comment_align->hor . '"><div class="rw-ui-container rw-class-comment rw-urid-' . $urid . '"></div></div>';
        return ($this->comment_align->ver == "top") ?
                $rw . $content :
                $content . $rw;
    }
    
    /**
    * Check if content has embedded inline rating widget.
    * 
    * @param {string} $pContent
    * 
    * @version 1.3.3
    * 
    */
    function rw_has_inline_rating($pContent)
    {
        return (false !== strpos($pContent, "[ratingwidget]"));
    }
    
    /**
    * Queue rating data for footer JS hook and return rating's html.
    * 
    * @param {serial} $pUrid User rating id.
    * @param {string} $pTitle Element's title (for top-rated widget).
    * @param {string} $pPermalink Corresponding rating's element url.
    * @param {string} $pElementClass Rating element class.
    * 
    * @uses rw_rating_html
    * @version 1.3.3
    * 
    */
    function EmbedRating($pUrid, $pTitle, $pPermalink, $pElementClass, $pAddSchema = false)
    {
        self::QueueRatingData($pUrid, $pTitle, $pPermalink, $pElementClass);
        return $this->rw_rating_html($pUrid, $pElementClass, $pAddSchema, $pTitle);
    }
    
    /**
    * Return rating's html.
    * 
    * @param {serial} $pUrid User rating id.
    * @param {string} $pElementClass Rating element class.
    * 
    * @version 1.3.3
    * 
    */
    function rw_rating_html($pUrid, $pElementClass, $pAddSchema = false, $pTitle = "")
    {
        $rating_html = '<div class="rw-ui-container rw-class-' . $pElementClass . ' rw-urid-' . $pUrid . '"></div>';
        
        if (true === $pAddSchema && false !== WP_RW__USER_SECRET)
        {
            $details = array( 
                "uid" => WP_RW__USER_KEY,
                "rids" => $pUrid,
            );

            $rw_ret_obj = self::RemoteCall("action/api/rating.php", $details);
            
            if (false !== $rw_ret_obj)
            {
                // Decode RW ret object.
                $rw_ret_obj = json_decode($rw_ret_obj);

                if (true === $rw_ret_obj->success && isset($rw_ret_obj->data) && count($rw_ret_obj->data) > 0)
                {
                    $rate = (float)$rw_ret_obj->data[0]->rate;
                    $votes = (float)$rw_ret_obj->data[0]->votes;
                    $calc_rate = ($votes > 0) ? ((float)$rate / (float)$votes) : 0;
                    $title = mb_convert_to_utf8(trim($pTitle));
                    $rating_html = '<div class="rw-ui-container rw-class-' . $pElementClass . ' rw-urid-' . $pUrid . '" itemscope itemtype="http://schema.org/Product">
    <span itemprop="name" style="position: fixed; top: 100%;">' . esc_html($pTitle) . '</span>
    <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
        <meta itemprop="worstRating" content="0" />
        <meta itemprop="bestRating" content="5" />
        <meta itemprop="ratingValue" content="' . $calc_rate . '" />
        <meta itemprop="ratingCount" content="' . $votes . '" />
    </div>
</div>';
                }
            }
        }
        
        return $rating_html;
    }
    
    /* BuddyPress Support Actions
    ---------------------------------------------------------------------------------------------------------------*/
    var $activity_align = array();
    function rw_before_activity_loop($has_activities)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_before_activity_loop", $params); }
        
        if (!$has_activities){ return false; }
        
        $items = array(
            "activity-update" => array(
                "align_key" => WP_RW__ACTIVITY_UPDATES_ALIGN,
                "enabled" => false,
            ),
            "activity-comment" => array(
                "align_key" => WP_RW__ACTIVITY_COMMENTS_ALIGN,
                "enabled" => false,
            ),
            "new-blog-post" => array(
                "align_key" => WP_RW__ACTIVITY_BLOG_POSTS_ALIGN,
                "enabled" => false,
            ),
            "new-blog-comment" => array(
                "align_key" => WP_RW__ACTIVITY_BLOG_COMMENTS_ALIGN,
                "enabled" => false,
            ),
            /*"new-forum-topic" => array(
                "align_key" => WP_RW__ACTIVITY_FORUM_TOPICS_ALIGN,
                "enabled" => false,
            ),*/
            "new-forum-post" => array(
                "align_key" => WP_RW__ACTIVITY_FORUM_POSTS_ALIGN,
                "enabled" => false,
            ),
        );
        
        $ver_top = false;
        $ver_bottom = false;
        foreach ($items as $key => &$item)
        {
            $align_str = self::_getOption($item["align_key"]);
            $align = json_decode($align_str);
            $item["enabled"] = (isset($align) && isset($align->hor));
            
            if ($item["enabled"] && WP_RW__AVAILABILITY_HIDDEN !== $this->rw_validate_availability($key))
            {
                $this->activity_align[$key] = $align;
                
                if ($align->ver === "top"){
                    $ver_top = true;
                }else{
                    $ver_bottom = true;
                }
            }
        }
        
        if ($ver_top){
            // Hook activity TOP rating.
            add_filter("bp_get_activity_action", array(&$this, "rw_display_activity_rating_top"));
        }
        
        if ($ver_bottom){
            // Hook activity BOTTOM rating.
            add_action("bp_activity_entry_meta", array(&$this, "rw_display_activity_rating_bottom"));
        }
        
        if (true === $items["activity-comment"]["enabled"]){
            // Hook activity-comment rating showup.
            add_filter("bp_get_activity_content", array(&$this, "rw_display_activity_comment_rating"));
        }        
        
        return true;
    }

    function rw_get_activity_rating($ver)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_get_activity_rating", $params); }
        
        global $activities_template;
        
        // Set current activity-comment to current activity update (recursive comments).
        $this->current_comment = $activities_template->activity;
        
        $rclass = str_replace("_", "-", $activities_template->activity->type);
        
        $is_forum_topic = false;
        if ($rclass === "new-forum-topic"){
            $rclass = "new-forum-post";
            $is_forum_topic = true;
        }

        // Check if item rating is top positioned.
        if (!isset($this->activity_align[$rclass]) || $ver !== $this->activity_align[$rclass]->ver){ return false; }
        
        // Get item id.
        $item_id = ("activity-update" === $rclass || "activity-comment" === $rclass) ?
                    $activities_template->activity->id :
                    $activities_template->activity->secondary_item_id;
        
        if ($is_forum_topic)
        {
            // If forum topic, then we must extract post id
            // from forum posts table, because secondary_item_id holds
            // topic id.
            if (function_exists("bb_get_first_post"))
            {
                $post = bb_get_first_post($item_id);
            }
            else
            {
                // Extract post id straight from the BB DB.
                    $config_path = get_site_option("bb-config-location", "");
                    
                    // bbPress is not installed.
                    if (empty($config_path)){ return false; }
                    
                    global $bb_table_prefix;
                    // Load bbPress config file.
                    @include_once($config_path);
                    
                    // Failed loading config file.
                    if (!defined("BBDB_NAME")){ return false; }
                    
                    $connection = null;
                    if (!$connection = mysql_connect(BBDB_HOST, BBDB_USER, BBDB_PASSWORD, true)){ return false; }
                    if (!mysql_selectdb(BBDB_NAME, $connection)){ return false; }
                    $results = mysql_query("SELECT * FROM {$bb_table_prefix}posts WHERE topic_id={$item_id} AND post_position=1", $connection);
                    $post = mysql_fetch_object($results);
            }
            
            if (!isset($post->post_id) && empty($post->post_id)){ return false; }
            
            $item_id = $post->post_id;
        }
        
        // Validate that item isn't explicitly excluded.
        if (false === $this->rw_validate_visibility($item_id, $rclass)){ return false; }

        switch ($rclass)
        {
            case "activity-update":
            case "activity-comment":
                // Get activity rating user-rating-id.
                $urid = $this->_getActivityRatingGuid($item_id);
                break;
            case "new-blog-post":
                // Get activity rating user-rating-id.
                $urid = $this->_getPostRatingGuid($item_id);
                break;
            case "new-blog-comment":
                // Get activity rating user-rating-id.
                $urid = $this->_getCommentRatingGuid($item_id);
                break;
            /*case "new-forum-topic":*/
            case "new-forum-post":
                // Get activity rating user-rating-id.
                $urid = $this->_getForumPostRatingGuid($item_id);
                break;
        }
        
        // If the item is post, queue rating with post title.
        $title = ("new-blog-post" === $rclass) ?
                  get_the_title($item_id) :
                  $activities_template->activity->content;
        
        // Queue activity rating.
        self::QueueRatingData($urid, strip_tags($title), bp_activity_get_permalink($activities_template->activity->id), $rclass);

        // Return rating html container.
        return '<div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $urid . '"></div>';
    }
    
    // Activity item top rating.
    function rw_display_activity_rating_top($action)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_activity_rating_top", $params); }
        
        $rating_html = $this->rw_get_activity_rating("top");
        
        return ((false === $rating_html) ?
                $action :
                // Attach rating html container after activity actions line.
                $action . '<div class="rw-' . $this->activity_align[$rclass]->hor . '">' . $rating_html .'</div>');
    }
    
    // Activity item bottom rating.
    function rw_display_activity_rating_bottom($id = "", $type = "")
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_activity_rating_bottom", $params); }
        
        $rating_html = $this->rw_get_activity_rating("bottom");

        if (false !== $rating_html){
            // Echo rating html container on bottom actions line.
            echo $rating_html;
        }
    }

    /*var $current_comment;
    function rw_get_current_activity_comment($action)
    {
        global $activities_template;
        
        // Set current activity-comment to current activity update (recursive comments).
        $this->current_comment = $activities_template->activity;
        
        return $action;
    }*/

    // Activity-comment.
    function rw_display_activity_comment_rating($comment_content)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_activity_comment_rating", $params); }
        
        if (!isset($this->current_comment) || null === $this->current_comment)
        {
            if (RWLogger::IsOn()){ RWLogger::Log("rw_display_activity_comment_rating", "Current comment is not set."); }
            
            return $comment_content;
        }
        
        // Find current comment.
        while (!$this->current_comment->children || false === current($this->current_comment->children))
        {
            $this->current_comment = $this->current_comment->parent;
            next($this->current_comment->children);
        }
        
        $parent = $this->current_comment;
        $this->current_comment = current($this->current_comment->children);
        $this->current_comment->parent = $parent;
        
        // Check if comment rating isn't specifically excluded.
        if (false === $this->rw_validate_visibility($this->current_comment->id, "activity-comment")){ return $comment_content; }        

        // Get activity comment user-rating-id.
        $comment_urid = $this->_getActivityRatingGuid($this->current_comment->id);
        
        // Queue activity-comment rating.
        self::QueueRatingData($comment_urid, strip_tags($this->current_comment->content), bp_activity_get_permalink($this->current_comment->id), "activity-comment");
        
        $rw = '<div class="rw-' . $this->activity_align["activity-comment"]->hor . '"><div class="rw-ui-container rw-class-activity-comment rw-urid-' . $comment_urid . '"></div></div><p></p>';
        
        // Attach rating html container.
        return ($this->activity_align["activity-comment"]->ver == "top") ?
                $rw . $comment_content :
                $comment_content . $rw;
    }

    // User profile.
    function rw_display_user_profile_rating()
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_user_profile_rating", $params); }
        
        // Get displayed user id.
        $user_id = bp_displayed_user_id();

        // User rating class.
        $rclass = "user";

        $align_str = self::_getOption(WP_RW__USERS_ALIGN);
        $align = json_decode($align_str);
        $enabled = (isset($align) && isset($align->hor));
        
        if ($enabled && WP_RW__AVAILABILITY_HIDDEN !== $this->rw_validate_availability("user"))
        {
            // Check if user rating isn't specifically excluded.
            if (false === $this->rw_validate_visibility($user_id, $rclass)){ return; }

            // Get user profile user-rating-id.
            $user_urid = $this->_getUserRatingGuid(WP_RW__USER_SECONDERY_ID, $user_id);

            // Queue user profile rating.
            self::QueueRatingData($user_urid, bp_get_displayed_user_fullname(), bp_get_displayed_user_link(), $rclass);
            
            echo '<div><div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $user_urid . '"></div></div>';
        }

        
        
        /* Forum posts accamulator rating.
        ----------------------------------------------------*/
        /*    $rclass = $rclass . "-forum-post";
            // Get user profile user-rating-id.
            $user_urid = $this->_getUserRatingGuid(WP_RW__FORUM_POST_SECONDERY_ID, $user_id);
            
            // Queue user profile rating.
            self::QueueRatingData($user_urid, bp_get_displayed_user_fullname(), bp_get_displayed_user_link(), $rclass);
            
            echo '<div><div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $user_urid . '"></div></div>';*/
    }
    
    /* BuddyPress bbPress Component
    ---------------------------------------------------------------------------------------------------------------*/
    var $forum_align = array();
    function rw_before_forum_loop($has_posts)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_before_forum_loop", $params); }

        if (!$has_posts){ return false; }
        
        $items = array(
            /*"forum-topic" => array(
                "align_key" => WP_RW__FORUM_TOPICS_ALIGN,
                "enabled" => false,
            ),*/
            "forum-post" => array(
                "align_key" => WP_RW__FORUM_POSTS_ALIGN,
                "enabled" => false,
            ),
        );
        
        $hook = false;
        foreach ($items as $key => &$item)
        {
            $align_str = self::_getOption($item["align_key"]);
            $align = json_decode($align_str);
            $item["enabled"] = (isset($align) && isset($align->hor));
            
            if ($item["enabled"] && WP_RW__AVAILABILITY_HIDDEN !== $this->rw_validate_availability($key))
            {
                $this->forum_align[$key] = $align;
                $hook = true;
            }
        }

        if ($hook){
            // Hook forum posts.
            add_filter("bp_get_the_topic_post_content", array(&$this, "rw_display_forum_post_rating"));
        }

        return true;
    }
    
    function rw_display_forum_post_rating($content)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_display_forum_post_rating", $params); }

        //        global $bp, $topic_template, $forum_template;
        // if the topic is closed, return just the rating number
//        if ( 0 == (int)$forum_template->topic->topic_open && !is_site_admin() )
//            return $post_text . '<div class="rfp-rate"><b>'.get_option( 'rfp_help_text_closed' ).'</b><span class="counter">' . rfp_get_post_rating_signed( $topic_template->post->post_id ) . '</span></div>';
        
        $rclass = "forum-post";

        // Check if item rating is top positioned.
        if (!isset($this->forum_align[$rclass])){ return $content; }
        
        $post_id = bp_get_the_topic_post_id();
        
        // Validate that item isn't explicitly excluded.
        if (false === $this->rw_validate_visibility($post_id, $rclass)){ return $content; }

        // Get forum-post user-rating-id.
        $post_urid = $this->_getForumPostRatingGuid($post_id);
        
        // Queue activity-comment rating.
        self::QueueRatingData($post_urid, strip_tags($topic_template->post->post_text), bp_get_the_topic_permalink() . "#post-" . $post_id, $rclass);
        
        /*
        // Get corresponding user's accamulator rating id.
        $uarid = $this->_getUserRatingGuid(WP_RW__FORUM_POST_SECONDERY_ID, bp_get_the_topic_poster_id());

        $rw = '<div class="rw-' . $this->forum_align[$rclass]->hor . '"><div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $post_urid . ' rw-uarid-' . $uarid . '"></div></div>';
        */
        
        $rw = '<div class="rw-' . $this->forum_align[$rclass]->hor . '"><div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $post_urid . '"></div></div>';
        
        // Attach rating html container.
        return ($this->forum_align[$rclass]->ver == "top") ?
                $rw . $content :
                $content . $rw;
    }
    
    /* Final Rating-Widget JS attach (before </body>)
    ---------------------------------------------------------------------------------------------------------------*/
    function rw_attach_rating_js($pElement = false)
    {
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("rw_attach_rating_js", $params); }

        $rw_settings = array(
            "blog-post" => array("options" => WP_RW__BLOG_POSTS_OPTIONS),
            "front-post" => array("options" => WP_RW__FRONT_POSTS_OPTIONS),
            "comment" => array("options" => WP_RW__COMMENTS_OPTIONS),
            "page" => array("options" => WP_RW__PAGES_OPTIONS),

            "activity-update" => array("options" => WP_RW__ACTIVITY_UPDATES_OPTIONS),
            "activity-comment" => array("options" => WP_RW__ACTIVITY_COMMENTS_OPTIONS),
//            "new-forum-topic" => array("options" => WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS),
            "new-forum-post" => array("options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS),
            "new-blog-post" => array("options" => WP_RW__ACTIVITY_BLOG_POSTS_OPTIONS),
            "new-blog-comment" => array("options" => WP_RW__ACTIVITY_BLOG_COMMENTS_OPTIONS),
            
//            "forum-topic" => array("options" => WP_RW__ACTIVITY_FORUM_TOPICS_OPTIONS),
            "forum-post" => array("options" => WP_RW__ACTIVITY_FORUM_POSTS_OPTIONS),

            "user" => array("options" => WP_RW__USERS_OPTIONS),
            "user-post" => array("options" => WP_RW__USERS_POSTS_OPTIONS),
            "user-page" => array("options" => WP_RW__USERS_PAGES_OPTIONS),
            "user-comment" => array("options" => WP_RW__USERS_COMMENTS_OPTIONS),
            "user-activity-update" => array("options" => WP_RW__USERS_ACTIVITY_UPDATES_OPTIONS),
            "user-activity-comment" => array("options" => WP_RW__USERS_ACTIVITY_COMMENTS_OPTIONS),
            "user-forum-post" => array("options" => WP_RW__USERS_FORUM_POSTS_OPTIONS),
        );
        
        $attach_js = false;
        
        $is_logged = is_user_logged_in();
        if (is_array(self::$ratings) && count(self::$ratings) > 0)
        {
            foreach (self::$ratings as $urid => $data)
            {
                $rclass = $data["rclass"];
                if (isset($rw_settings[$rclass]) && !isset($rw_settings[$rclass]["enabled"]))
                {
                    $rw_settings[$rclass]["enabled"] = true;

                    // Get rating front posts settings.
                    $rw_settings[$rclass]["options"] = $this->_getOption($rw_settings[$rclass]["options"]);

                    if (WP_RW__AVAILABILITY_DISABLED === $this->rw_validate_availability($rclass))
                    {
                        // Disable ratings (set them to be readOnly).
                        $options_obj = json_decode($rw_settings[$rclass]["options"]);
                        $options_obj->readOnly = true;
                        $rw_settings[$rclass]["options"] = json_encode($options_obj);
                    }

                    $attach_js = true;
                }
            }
        }

        if ($attach_js || self::$TOP_RATED_WIDGET_LOADED)
        {
?>
        <div class="rw-js-container">
            <script type="text/javascript">
                // Initialize ratings.
                function RW_Async_Init(){
                    RW.init({<?php 
                        // User key (uid).
                        echo 'uid: "' . WP_RW__USER_KEY . '"';
                        
                        $user = wp_get_current_user();
                        if ($user->id !== 0)
                        {
                            // User logged-in.
                            $vid = $user->id;
                            // Set voter id to logged user id.
                            echo ", vid: {$vid}";
                        }
                        
                        if (false !== WP_RW__USER_SECRET)
                        {
                            // Secure connection.
                            $timestamp = time();
                            $token = self::GenerateToken($timestamp);
                            echo ', token: {timestamp: ' . $timestamp . ', token: "' . $token . '"}';
                        }
                    ?>,
                        source: "WordPress"
                    });
                    <?php
                        foreach ($rw_settings as $rclass => $options)
                        {
                            if (isset($rw_settings[$rclass]["enabled"]) && (true === $rw_settings[$rclass]["enabled"])){
                                if (!empty($rw_settings[$rclass]["options"])){
                                    echo 'RW.initClass("' . $rclass . '", ' . $rw_settings[$rclass]["options"] . ');';
                                }
                            }
                        }
                        
                        foreach (self::$ratings as $urid => $data)
                        {
                            echo 'RW.initRating("' . $urid . '", {title: "' . esc_js($data["title"]) . '", url: "' . esc_js($data["permalink"]) . '"});';
                        }
                    ?>
                    RW.render(null, <?php
                        echo (!self::$TOP_RATED_WIDGET_LOADED) ? "true" : "false";
                    ?>);
                }

                
                RW_Advanced_Options = {
                    blockFlash: !(<?php
                        echo $this->_getOption(WP_RW__FLASH_DEPENDENCY);
                    ?>)
                };
                
                // Append RW JS lib.
                if (typeof(RW) == "undefined"){ 
                    (function(){
                        var rw = document.createElement("script"); rw.type = "text/javascript"; rw.async = true;
                        rw.src = "<?php echo WP_RW__ADDRESS_JS; ?>external<?php
                            if (!defined("WP_RW__DEBUG")){ echo ".min"; }
                        ?>.js";
                        var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(rw, s);
                    })();
                }
            </script>
        </div> 
<?php
        }
        
        
        if (RWLogger::IsOn())
        {
            echo "\n<!-- RATING-WIDGET LOG START\n\n";
            RWLogger::Output("    ");
            echo "\n RATING-WIDGET LOG END-->\n";
        }
    }
    
    /* Boosting page
    ---------------------------------------------------------------------------------------------------------------*/
    function rw_boost_page_load()
    {
        if ('post' != strtolower($_SERVER['REQUEST_METHOD']) ||
            $_POST["rw_boost_posted"] != "Y")
        {
            return;
        }
        
        $element = (isset($_POST["rw_element"]) && in_array($_POST["rw_element"], array("post", "comment", "activity", "forum", "user"))) ?
                    $_POST["rw_element"] :
                    false;
        if (false === $element){ self::$errors->add('rating_widget_boost', __("Invalid element selection.", WP_RW__ID)); return; }

        $id = (isset($_POST["rw_id"]) && is_numeric($_POST["rw_id"]) && $_POST["rw_id"] >= 0) ?
               (int)$_POST["rw_id"] :
               false;
        if (false === $id){ self::$errors->add('rating_widget_boost', __("Invalid element id.", WP_RW__ID)); return; }
               
        $votes = (isset($_POST["rw_votes"]) && is_numeric($_POST["rw_votes"])) ?
                  (int)$_POST["rw_votes"] : 
                  false;
        if (false === $votes){ self::$errors->add('rating_widget_boost', __("Invalid votes number.", WP_RW__ID)); return; }

        $rate = (isset($_POST["rw_rate"]) && is_numeric($_POST["rw_rate"])) ?
                 (float)$_POST["rw_rate"] : 
                 false;
        if (false === $rate){ self::$errors->add('rating_widget_boost', __("Invalid votes rate.", WP_RW__ID)); return; }
        
        $urid = false;
        switch ($element)
        {
            case "post":
                $urid = $this->_getPostRatingGuid($id);
                break;
            case "comment":
                $urid = $this->_getCommentRatingGuid($id);
                break;
            case "activity":
                $urid = $this->_getActivityRatingGuid($id);
                break;
            case "forum":
                $urid = $this->_getForumPostRatingGuid($id);
                break;
            case "user":
                $urid = $this->_getUserRatingGuid($id);
                break;
        }
        
        $details = array(
            "uid" => WP_RW__USER_KEY,
            "urid" => $urid,
            "votes" => $votes,
            "rate" => $rate,
        );
        
        $rw_ret_obj = self::RemoteCall("action/api/boost.php", $details);
        if (false === $rw_ret_obj){ return; }
        
        // Decode RW ret object.
        $rw_ret_obj = json_decode($rw_ret_obj);

        if (false == $rw_ret_obj->success)
        {
            self::$errors->add('rating_widget_boost', __($rw_ret_obj->msg, WP_RW__ID));
        }
        else
        {
            self::$success->add('rating_widget_boost', __($rw_ret_obj->msg, WP_RW__ID));
        }
    }
    
    function rw_boost_page()
    {
        $this->rw_boost_page_load();

        $this->_printErrors();
        $this->_printSuccess();
?>
<div class="wrap rw-dir-ltr">
    <h2><?php _e( 'Rating-Widget Boosting', WP_RW__ID ); ?></h2>

    <p>
        Here you can boost your ratings.<br /><br />
        <b style="color: red;">Note: This action impact the rating record directly - it's on your own responsibility!</b><br /><br />
        Example:<br />
        <b>Element:</b> <i>Post</i>; <b>Id:</b> <i>2</i>; <b>Votes:</b> <i>3</i>; <b>Rate:</b> <i>4</i>;<br />
        This will add 3 votes with the rate of 4 stars to Post with Id=2.
    </p>

    <form action="" method="post">
        <input type="hidden" name="rw_boost_posted" value="Y" />
        <label for="rw_element">Element: 
            <select id="rw_element" name="rw_element">
                <option value="post" selected="selected">Post/Page</option>
                <option value="comment">Comment</option>
                <option value="activity">Activity Update</option>
                <option value="forum">Forum Post</option>
                <option value="user">User</option>
            </select>
        </label>
        <br /><br />
        <label for="rw_id">Id: <input type="text" id="rw_id" name="rw_id" value="" /></label>
        <br /><br />
        <label for="rw_votes">Votes: <input type="text" id="rw_votes" name="rw_votes" value="" /></label>
        <br /><br />
        <label for="rw_rate">Rate: <input type="text" id="rw_rate" name="rw_rate" value="" /></label>
        <br />
        <b style="font-size: 10px;">Note: Rate must be a number between -5 to 5.</b>
        <br /><br />
        <input type="submit" value="Boost" />
    </form>
</div>
<?php        
    }
    
    /**
    * Modifies post for Rich Snippets Compliance.
    * 
    */
    function rw_add_title_metadata($title, $id = '')
    {
        return '<mark itemprop="name" style="background: none; color: inherit;">' . $title . '</mark>';
    }
    
    function rw_add_article_metadata($classes, $class = '', $post_id = '')
    {
        $classes[] = '"';
        $classes[] = 'itemscope';
        $classes[] = 'itemtype="http://schema.org/Product';
        return $classes;
    }
    
    /* wp_footer() execution validation
     * Inspired by http://paste.sivel.net/24
     --------------------------------------------------------------------------------------------------------------*/
    function test_footer_init() 
    {
        // Hook in at admin_init to perform the check for wp_head and wp_footer
        add_action('admin_init', array(&$this, 'check_head_footer'));
     
        // If test-footer query var exists hook into wp_footer
        if (isset( $_GET['test-footer']))
            add_action('wp_footer', array(&$this, 'test_footer'), 99999); // Some obscene priority, make sure we run last
    }
     
    // Echo a string that we can search for later into the footer of the document
    // This should end up appearing directly before </body>
    function test_footer() 
    {
        echo '<!--wp_footer-->';
    }
 
    // Check for the existence of the strings where wp_head and wp_footer should have been called from
    function check_head_footer() 
    {
        // NOTE: uses home_url and thus requires WordPress 3.0
        if (!function_exists('home_url'))
            return;
        
        // Build the url to call, 
        $url = add_query_arg(array('test-footer' => ''), home_url());
        
        // Perform the HTTP GET ignoring SSL errors
        $response = wp_remote_get($url, array('sslverify' => false));
        
        // Grab the response code and make sure the request was sucessful
        $code = (int)wp_remote_retrieve_response_code($response);
        
        if ($code == 200) 
        {
            // Strip all tabs, line feeds, carriage returns and spaces
            $html = preg_replace('/[\t\r\n\s]/', '', wp_remote_retrieve_body($response));
            
            // Check to see if we found the existence of wp_footer
            if (!strstr($html, '<!--wp_footer-->'))
            {
                add_action('admin_notices', array(&$this, 'test_head_footer_notices'));
            }
        }
    }
 
    // Output the notices
    function test_head_footer_notices() 
    {
        // If we made it here it is because there were errors, lets loop through and state them all
        echo '<div class="updated highlight"><p><strong>' . 
              esc_html('If the Rating-Widget\'s ratings don\'t show up on your blog it\'s probably because your active theme is missing the call to <?php wp_footer(); ?> which should appear directly before </body>.').
              '</strong> '.
              'For more details check out our <a href="' . WP_RW__ADDRESS . '/faq/" target="_blank">FAQ</a>.</p></div>';
    }
    
    /* Post/Page Exclude Checkbox
    ---------------------------------------------------------------------------------------------------------------*/
    function AddPostMetaBox()
    {
        //add the meta box for posts/pages
        add_meta_box('rw-post-meta-box', __('Rating-Widget Exclude Option', WP_RW__ID), array(&$this, 'ShowPostMetaBox'), 'post', 'side', 'high');
        add_meta_box('rw-post-meta-box', __('Rating-Widget Exclude Option', WP_RW__ID), array(&$this, 'ShowPostMetaBox'), 'page', 'side', 'high');
    }
    
    // Callback function to show fields in meta box.
    function ShowPostMetaBox() 
    {
         global $post;
             
         // Use nonce for verification
         echo '<input type="hidden" name="rw_post_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

        // get whether current post is excluded or not
        $excluded_post = (false === $this->rw_validate_visibility($post->ID, array('front-post', 'blog-post', 'page')));
        
        $checked = $excluded_post ? '' : 'checked="checked"';

        echo '<p>';
        echo '<label for="rw_include_post"><input type="checkbox" name="rw_include_post" id="rw_include_post" value="1" ', $checked, ' /> ';
        echo __('Show Rating (Uncheck to Hide)', WP_RW__ID);
        echo '</label>';
        echo '</p>';
    }

    // Save data from meta box.
    function SavePostData($post_id)
    {    
        if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("SavePostData", $params, true); }
        
        // Verify nonce.
        if (!isset($_POST['rw_post_meta_box_nonce']) || !wp_verify_nonce($_POST['rw_post_meta_box_nonce'], basename(__FILE__)))
            return $post_id;

        // Check autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        if (RWLogger::IsOn()){ RWLogger::Log("post_type", $_POST['post_type']); }
        
        // Check permissions.
        if ('page' == $_POST['post_type']) 
        {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
        }
        else if (!current_user_can('edit_post', $post_id)) 
        {
            return $post_id;
        }

        //check whether this post/page is to be excluded
        $include_post = $_POST['rw_include_post'];

        $this->AddToVisibility(
            $_POST['ID'], 
            (('page' == $_POST['post_type']) ? array('page') : array('front-post', 'blog-post')),
            (isset($_POST['rw_include_post']) && "1" == $_POST['rw_include_post']));
        
        $this->SaveVisibility();
        
        if (RWLogger::IsOn()){ RWLogger::LogDeparture("SavePostData"); }
    }
    
    function DumpLog($pElement = false)
    {
        if (RWLogger::IsOn())
        {
            echo "\n<!-- RATING-WIDGET LOG START\n\n";
            RWLogger::Output("    ");
            echo "\n RATING-WIDGET LOG END-->\n";
        }
    }
}

if (class_exists("WP_Widget"))
{
    /* Top Rated Widget
    ---------------------------------------------------------------------------------------------------------------*/
    class RWTopRated extends WP_Widget
    {
        var $rw_address;
        var $version;
        
        function RWTopRated()
        {
            if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("RWTopRated Constructor", $params, true); }
            
            $this->rw_address = WP_RW__ADDRESS;
            
            $widget_ops = array('classname' => 'rw_top_rated', 'description' => __('A list of your top rated posts.'));
            $this->WP_Widget("RWTopRated", "Rating-Widget: Top Rated", $widget_ops);
            
            if (RWLogger::IsOn()){ RWLogger::LogDeparture("RWTopRated Constructor"); }
        }
    
        function widget($args, $instance)
        {
            if (RWLogger::IsOn()){ $params = func_get_args(); RWLogger::LogEnterence("RWTopRated.widget", $params, true); }

            if (!defined("WP_RW__USER_KEY") || false === WP_RW__USER_KEY){ return; }
            
            if (RatingWidgetPlugin::$WP_RW__HIDE_RATINGS){ return; }

            extract($args, EXTR_SKIP);
    
            $types = array(
                "posts" => array(
                    "rclass" => "blog-post", 
                    "classes" => "front-post,blog-post,new-blog-post,user-post",
                    "options" => WP_RW__BLOG_POSTS_OPTIONS,
                ),
                "pages" => array(
                    "rclass" => "page", 
                    "classes" => "page,user-page",
                    "options" => WP_RW__PAGES_OPTIONS,
                ),
                "comments" => array(
                    "rclass" => "comment",
                    "classes" => "comment,new-blog-comment,user-comment",
                    "options" => WP_RW__COMMENTS_OPTIONS,
                ),
                "activity_updates" => array(
                    "rclass" => "activity-update",
                    "classes" => "activity-update,user-activity-update",
                    "options" => WP_RW__ACTIVITY_UPDATES_OPTIONS,
                ),
                "activity_comments" => array(
                    "rclass" => "activity-comment",
                    "classes" => "activity-comment,user-activity-comment",
                    "options" => WP_RW__ACTIVITY_COMMENTS_OPTIONS,
                ),
                "forum_posts" => array(
                    "rclass" => "forum-post",
                    "classes" => "forum-post,new-forum-post,user-forum-post",
                    "options" => WP_RW__FORUM_POSTS_OPTIONS,
                ),
                "users" => array(
                    "rclass" => "user",
                    "classes" => "user",
                    "options" => WP_RW__FORUM_POSTS_OPTIONS,
                ),
            );

            $show_any = false;

            foreach ($types as $type => $data)
            {
                if (false !== $instance["show_$type"])
                {
                    $show_any = true;
                    break;
                }
            }

            if (false === $show_any)
            {
                // Nothing to show.
                return;                
            }
            
            $details = array( 
                "uid" => WP_RW__USER_KEY,
            );

            $queries = array();
           
            foreach ($types as $type => $type_data)
            {
                if (isset($instance["show_{$type}"]) && $instance["show_{$type}"] && $instance["{$type}_count"] > 0)
                {
                    $options = json_decode(RatingWidgetPlugin::_getOption($type_data["options"]));

                    $queries[$type] = array(
                        "rclasses" => $type_data["classes"],
                        "votes" => max(1, (int)$instance["{$type}_min_votes"]),
                        "orderby" => $instance["{$type}_orderby"],
                        "order" => $instance["{$type}_order"],
                        "limit" => (int)$instance["{$type}_count"],
                        "types" => isset($options->type) ? $options->type : "star",
                    );
                }
            }

            $details["queries"] = urlencode(json_encode($queries));
            
            $rw_ret_obj = RatingWidgetPlugin::RemoteCall("action/query/ratings.php", $details);
            
            if (false === $rw_ret_obj){ return; }
            
            $rw_ret_obj = json_decode($rw_ret_obj);
            
            if (null === $rw_ret_obj || true !== $rw_ret_obj->success){ return; }
            
            echo $before_widget;
            $title = empty($instance['title']) ? __('Top Rated', WP_RW__ID) : apply_filters('widget_title', $instance['title']);
            echo $before_title . $title . $after_title;

            $empty = true;
            if (count($rw_ret_obj->data) > 0)
            {
                foreach($rw_ret_obj->data as $type => $ratings)
                {                    
                    if (is_array($ratings) && count($ratings) > 0)
                    {
                        echo '<div id="rw_top_rated_' . $type . '">';
                        if ($instance["show_{$type}_title"]){ /* (1.3.3) - Conditional title display */
                            $instance["{$type}_title"] = empty($instance["{$type}_title"]) ? ucwords($type) : $instance["{$type}_title"];
                            echo '<p style="margin: 0;">' . $instance["{$type}_title"] . '</p>';
                        }
                        echo '<ul class="rw-top-rated-list">';
                        foreach ($ratings as $rating)
                        {
                            $urid = $rating->urid;
                            $rclass = $types[$type]["rclass"];
                            
                            RatingWidgetPlugin::QueueRatingData($urid, "", "", $rclass);

                            switch ($type)
                            {
                                case "posts":
                                case "pages":
                                    $id = RatingWidgetPlugin::Urid2PostId($urid);
                                    $post = get_post($id);
                                    $title = trim(strip_tags($post->post_title));
                                    $permalink = get_permalink($post->ID);
                                    break;
                                case "comments":
                                    $id = RatingWidgetPlugin::Urid2CommentId($urid);
                                    $comment = get_comment($id);
                                    $title = trim(strip_tags($comment->comment_content));
                                    $permalink = get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID;
                                    break;
                                case "activity_updates":
                                case "activity_comments":
                                    $id = RatingWidgetPlugin::Urid2ActivityId($urid);
                                    $activity = new bp_activity_activity($id);
                                    $title = trim(strip_tags($activity->content));
                                    $permalink = bp_activity_get_permalink($id);
                                    break;
                                case "users":
                                    $id = RatingWidgetPlugin::Urid2UserId($urid);
                                    $title = trim(strip_tags(bp_core_get_user_displayname($id)));
                                    $permalink = bp_core_get_user_domain($id);
                                    break;
                                case "forum_posts":
                                    $id = RatingWidgetPlugin::Urid2ForumPostId($urid);
                                    $forum_post = bp_forums_get_post($id);
                                    $title = trim(strip_tags($forum_post->post_text));
                                    $page = bb_get_page_number($forum_post->post_position);
                                    $permalink = get_topic_link($id, $page) . "#post-{$id}";
                                    break;
                            }
                            $short = (mb_strlen($title) > 30) ? trim(mb_substr($title, 0, 30)) . "..." : $title;
                            
                            echo '<li>'.
                                 '<a href="' . $permalink . '" title="' . $title . '">' . $short . '</a>'.
                                 '<br />'.
                                 '<div class="rw-ui-container rw-class-' . $rclass . ' rw-urid-' . $urid . '"></div>'.
                                 '</li>';
                        }
                        echo "</ul>";
                        echo "</div>";
                        
                        $empty = false;
                    }
                }                
            }

            if (true === $empty){
                echo '<p style="margin: 0;">There are no rated items for this period.</p>';
            }
            else
            {
                // Set a flag that the widget is loaded.
                RatingWidgetPlugin::TopRatedWidgetLoaded();
?>
<script type="text/javascript">
    // Hook render widget.
    if (typeof(RW_HOOK_READY) === "undefined"){ RW_HOOK_READY = []; }
    RW_HOOK_READY.push(function(){
        RW._foreach(RW._getByClassName("rw-top-rated-list", "ul"), function(list){
            RW._foreach(RW._getByClassName("rw-ui-container", "div", list), function(rating){
                // Deactivate rating.
                RW._Class.remove(rating, "rw-active");
                var i = (RW._getByClassName("rw-report-link", "a", rating))[0];
                if (RW._is(i)){ i.parentNode.removeChild(i); }
                
                // Update size to small.
                if (!RW._Class.has(rating, "rw-size-small"))
                {
                    RW._Class.add(rating, "rw-size-small");
                    RW._Class.remove(rating, "rw-size-medium");
                    RW._Class.remove(rating, "rw-size-large");
                }
                
                if (RW._Class.has(rating, "rw-ui-star"))
                {
                    RW._foreach(RW._getByTagName("li", rating), function(star){
                        // Clear star event handlers.
                        star.onmouseover =
                        star.onmouseout =
                        star.onclick = "";
                    });
                }
                else
                {
                    RW._foreach(RW._getByTagName("i", rating), function(thumb){
                        // Clear star event handlers.
                        thumb.onmouseover =
                        thumb.onmouseout =
                        thumb.onclick = "";
                    });

                    RW._foreach(['like', 'dislike'], function(label){
                        var labelItem = RW._getByClassName("rw-ui-like-label", "span", rating);
                        
                        if (labelItem.length == 1)
                            labelItem[0].style.fontSize = labelItem[0].style.lineHeight = "";
                    });
                }
                
                var label = (RW._getByClassName("rw-ui-info", "span", rating))[0];
                label.style.fontSize = label.style.lineHeight = "";
            });
        });
    });
</script>
<?php
            }
            echo $after_widget;
        }
    
        function update($new_instance, $old_instance)
        {
            $types = array("posts", "pages", "comments");
            
            if (RatingWidgetPlugin::$WP_RW__BP_INSTALLED)
            {
                $types[] = "activity_updates";
                $types[] = "activity_comments";
                $types[] = "users";
            }
            
            if (defined('WP_RW__BBP_INSTALLED'))
            {
                $types[] = "forum_posts";
            }
            
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            foreach ($types as $type)
            {
                $instance["show_{$type}"] = (int)$new_instance["show_{$type}"];
                $instance["show_{$type}_title"] = (int)$new_instance["show_{$type}_title"]; /* (1.3.3) - Conditional title display */
                $instance["{$type}_title"] = $new_instance["{$type}_title"]; /* (1.3.3) - Explicit title */
                $instance["{$type}_count"] = (int)$new_instance["{$type}_count"];
                $instance["{$type}_min_votes"] = (int)$new_instance["{$type}_min_votes"]; /* (1.3.7) - Min votes to appear */
                $instance["{$type}_orderby"] = $new_instance["{$type}_orderby"]; /* (1.3.7) - Order by */
                $instance["{$type}_order"] = $new_instance["{$type}_order"]; /* (1.3.8) - Order */
            }
            return $instance;
        }
    
        function form($instance)
        {
            $types = array("posts", "pages", "comments");
                        
            if (RatingWidgetPlugin::$WP_RW__BP_INSTALLED)
            {
                $types[] = "activity_updates";
                $types[] = "activity_comments";
                $types[] = "users";
            }
            
            if (defined('WP_RW__BBP_INSTALLED'))
            {
                $types[] = "forum_posts";
            }
            
            $orders = array("avgrate", "votes", "likes", "created", "updated");
            $orders_labels = array("Average Rate", "Votes Number", "Likes (for Thumbs)", "Created", "Updated");

            $show = array();
            $items = array();
            
            // Update default values.
            $values = array("title" => "");
            foreach ($types as $type)
            {
                $values["show_{$type}"] = "1";
                $values["{$type}_count"] = "2";
                $values["{$type}_min_votes"] = "1";
                $values["{$type}_orderby"] = "avgrate";
                $values["{$type}_order"] = "DESC";
            }

            $instance = wp_parse_args((array)$instance, $values);
            $title = strip_tags($instance['title']);
            foreach ($types as $type)
            {
                $values["show_{$type}"] = (int)$instance["show_{$type}"];
                $values["show_{$type}_title"] = (int)$instance["show_{$type}_title"];
                $values["{$type}_title"] = $instance["{$type}_title"];
                $values["{$type}_count"] = (int)$instance["{$type}_count"];
                $values["{$type}_min_votes"] = max(1, (int)$instance["{$type}_min_votes"]);
                $values["{$type}_orderby"] = $instance["{$type}_orderby"];
                if (!in_array($values["{$type}_orderby"], $orders)){ $values["{$type}_orderby"] = "avgrate"; }
                $values["{$type}_order"] = strtoupper($instance["{$type}_order"]);
                if (!in_array($values["{$type}_order"], array("DESC", "ASC"))){ $values["{$type}_order"] = "DESC"; }
            }
    ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', WP_RW__ID); ?>: <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
    <?php
            foreach ($types as $type)
            {
    ?>
        <hr>
        <h4><?php echo ucwords(str_replace("_", " ", $type)); ?></h5>
        <hr>
        <p>
            <label for="<?php echo $this->get_field_id("show_{$type}"); ?>">
                <?php
                    $checked = "";
                    if ($values["show_{$type}"] == 1){
                        $checked = ' checked="checked"';
                    }
                ?>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("show_{$type}"); ?>" name="<?php echo $this->get_field_name("show_{$type}"); ?>" value="1"<?php echo ($checked); ?> />
                 <?php _e("Show for {$type}", WP_RW__ID); ?>
            </label>
        </p>
        <?php
            /* (1.3.3) - Conditional title display */
        ?>
        <p>
            <label for="<?php echo $this->get_field_id("show_{$type}_title"); ?>">
                <?php
                    $checked = "";
                    if ($values["show_{$type}_title"] == 1){
                        $checked = ' checked="checked"';
                    }
                ?>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("show_{$type}_title"); ?>" name="<?php echo $this->get_field_name("show_{$type}_title"); ?>" value="1"<?php echo ($checked); ?> />
                 <?php _e("Show '" . ucwords($type) . "' title", WP_RW__ID); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("{$type}_title"); ?>"><?php _e(ucwords($type) . " Title", WP_RW__ID); ?>:
                <?php
                    $values["{$type}_title"] = empty($values["{$type}_title"]) ? ucwords($type) : $values["{$type}_title"];
                ?>
                <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name("{$type}_title"); ?>" type="text" value="<?php echo esc_attr($values["{$type}_title"]); ?>" style="width: 120px;" />
            </label>
        </p>
        <p>
            <label for="rss-items-<?php echo $values["{$type}_count"];?>"><?php _e("How many {$type} would you like to display?", WP_RW__ID); ?>
                    <select id="<?php echo $this->get_field_id("{$type}_count"); ?>" name="<?php echo $this->get_field_name("{$type}_count"); ?>">
                <?php
                    for ($i = 1; $i <= 25; $i++){
                        echo "<option value='{$i}' " . ($values["{$type}_count"] == $i ? "selected='selected'" : '') . ">{$i}</option>";
                    }
                ?>
                    </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("{$type}_min_votes"); ?>"><?php _e("Min Votes", WP_RW__ID); ?> (>= 1):
                <input style="width: 40px; text-align: center;" id="<?php echo $this->get_field_id("{$type}_min_votes"); ?>" name="<?php echo $this->get_field_name("{$type}_min_votes"); ?>" type="text" value="<?php echo esc_attr($values["{$type}_min_votes"]); ?>" />
            </label>
        </p>
        <p>
            <label for="rss-items-<?php echo $values["{$type}_orderby"];?>"><?php _e("Order By", WP_RW__ID); ?>:
                    <select id="<?php echo $this->get_field_id("{$type}_orderby"); ?>" name="<?php echo $this->get_field_name("{$type}_orderby"); ?>">
                    <?php
                        for ($i = 0, $len = count($orders); $i <  $len; $i++)
                        {
                            echo '<option value="' . $orders[$i] . '"' . ($values["{$type}_orderby"] == $orders[$i] ? "selected='selected'" : '') . '>' . $orders_labels[$i] . '</option>';
                        }
                    ?>
                    </select>
            </label>
        </p>
        <p>
            <label for="rss-items-<?php echo $values["{$type}_order"];?>"><?php _e("Order", WP_RW__ID); ?>:
                    <select id="<?php echo $this->get_field_id("{$type}_order"); ?>" name="<?php echo $this->get_field_name("{$type}_order"); ?>">
                        <option value="DESC"<?php echo ($values["{$type}_order"] == "DESC" ? " selected='selected'" : '');?>>BEST (Descending)</option>
                        <option value="ASC"<?php echo ($values["{$type}_order"] == "ASC" ? " selected='selected'" : '');?>>WORST (Ascending)</option>
                    </select>
            </label>
        </p>
<?php        
            }
        }    
    }
    
    add_action("widgets_init", create_function('', 'return register_widget("RWTopRated");')); 
}

/* For servers without mb string support.
---------------------------------------------------------------------------------------------------------------*/
if (!function_exists("mb_strlen")){
    function mb_strlen($str){ return strlen($str); }
    function mb_substr($str, $start, $length){ return substr($str, $start, $length); }
    function mb_convert_to_utf8($str){ return $str; }
}else{
    function mb_convert_to_utf8($str){ return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str)); }
}

// Invoke class.
global $rwp;
$rwp = new RatingWidgetPlugin();
?>
