<?php

/**
 * Contains the UKI_Facebook_Wall_Feed class
 *
 * Contains the UKI_Facebook_Wall_Feed class.  See class desciption for more
 * information.
 *
 * PHP version 5
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   WordPress_Plugin
 * @package    JSL3_FWF
 * @author     Takanudo <fwf@takanudo.com>
 * @author     Fedil Grogan <fedil@ukneeq.com>
 * @copyright  2011-2013
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License 3
 * @version    1.7.2
 * @link       http://takando.com/jsl3-facebook-wall-feed
 * @since      File available since Release 1.0
 */

// {{{ UKI_Facebook_Wall_Feed

/**
 * Loads and presents a Facebook Wall feed
 *
 * Loads the Facebook Wall feed. Then parses the JSON sent from Facebook.
 * Finally, prints out the Facebook Wall. This class was originally written
 * by Fedil Grogan.
 *
 * @category   WordPress_Plugin
 * @package    JSL3_FWF
 * @author     Fedil Grogan <fedil@ukneeq.com>
 * @author     Takanudo <fwf@takanudo.com>
 * @copyright  2011-2013
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    1.7.2
 * @link       http://takando.com/jsl3-facebook-wall-feed
 * @since      File available since Release 1.0
 */

class UKI_Facebook_Wall_Feed {
    // {{{ properties

    /**
     * Facebook ID
     *
     * ID of the Facebook profile from which to grab the feed.
     *
     * @var string
     */
    var $fb_id;

    /**
     * Facebook App ID
     *
     * ID of the Facebook App used by the feed.
     *
     * @var string
     */
    var $fb_app_id;
    
    /**
     * Limit of wall posts
     *
     * Holds the top number of facebook wall posts to get.
     *
     * @var int
     */
    var $fb_limit;
    
    /**
     * Access Token
     *
     * Access token received from Facebook that is used to get the Facebook
     * wall feed.
     *
     * @var string
     */
    var $access_token;
    
    /**
     * Facebook ID Only
     *
     * Determines if only posts made by this FacebookID are shown
     *
     * @var boolean
     */
    var $fb_id_only;
    
    /**
     * Facebook Privacy Settings
     *
     * Determines if all posts or only public posts are shown.
     *
     * @var string
     */
    var $fb_privacy;

    /**
     * Post count
     *
     * Counts the number of posts disaplyed.
     *
     * @var int
     */
    var $post_count;

    /**
     * Run thorough search
     *
     * Will make multiple class to facebook graph api to get posts.
     *
     * @var boolean
     */
    var $thorough;

    /**
     * Show status messages
     *
     * Will show all status messages.
     *
     * @var boolean
     */
    var $show_status;

    /**
     * Show comments
     *
     * Will show comments made on a post.
     *
     * @var boolean
     */
    var $show_comments;

    /**
     * Show facebook icons
     *
     * Will show Facebook icons.
     *
     * @var boolean
     */
    var $fb_icons;

    /**
     * Open links in a new window
     *
     * Will add 'target="_blank"' to all anchor tags
     *
     * @var boolean
     */
    var $new_win;

    /**
     * Converts plan text URI to HTML link
     *
     * Flag to call make_clickable() WordPress function
     *
     * @var boolean
     */
    var $make_link;
    
    /**
     * Facebook Locale Settings
     *
     * Determines the locale of the facebook feed.
     *
     * @var string
     */
    var $fb_locale;
    
    /**
     * cURL SSL Verification
     *
     * Determines if cURL will verify SSL certificates.
     *
     * @var boolean
     */
    var $verify;
    
    /**
     * Gets Profile Picture
     *
     * Determines if access token will be used to get profile picture from
     * a Facebook page with demographic restrictions.
     *
     * @var boolean
     */
    var $profile;

    // }}}
    // {{{ UKI_Facebook_Wall_Feed()

    /**
     * Constructor for this class
     *
     * Constructor initializes class variables.
     *
     * @param string  $id          the ID of the Facebook profile.
     * @param string  $app_id      the Facebook App ID (deprecated)
     * @param string  $app_secret  the Facebook App Secret (deprecated)
     * @param int     $limit       the number of posts to get from the wall
     * @param string  $token       the Facebook access token
     * @param boolean $id_only     determines if posts by other usres are shown
     * @param string  $privacy     determines if only public or all posts are
     *                             show
     * @param boolean $be_thorough determines if multiple calls to facebook
     *                             graph will be made
     * @param boolean $new_window  determines if links open in a new window
     * @param boolean $make_click  determines if plain text URI are converted
     *                             to HTML links
     * @param boolean $show_all    determines if all status messages are shown
     * @param boolean $show_comm   determines if post comments are shown
     * @param string  $locale      sets the language locale
     * @param boolean $verify_ssl  determines if SSL verification takes place
     * @param boolean $get_profile determines if access token is used to get
     *                             profile picture
     * @param boolean $show_icons  determines id Facebook icons are shown
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function UKI_Facebook_Wall_Feed(
        $id, $app_id = '', $app_secret = '', $limit = JSL3_FWF_WIDGET_LIMIT,
        $token, $id_only = FALSE, $privacy = 'All', $be_thorough = FALSE,
        $new_window = FALSE, $make_click = TRUE, $show_all = FALSE,
        $show_comm = FALSE, $locale = 'en_US', $verify_ssl = TRUE,
        $get_profile = FALSE, $show_icons = TRUE ) {

        $this->fb_id          = $id;
        $this->fb_app_id      = $app_id;
        $this->fb_limit       = $limit;
        $this->access_token   = $token;
        $this->fb_id_only     = $id_only;
        $this->fb_privacy     = $privacy;
        $this->thorough       = $be_thorough;
        $this->new_win        = $new_window;
        $this->make_link      = $make_click;
        $this->show_status    = $show_all;
        $this->show_comments  = $show_comm;
        $this->fb_locale      = $locale;
        $this->verify         = $verify_ssl;
        $this->profile        = $get_profile;
        $this->fb_icons       = $show_icons;
        $this->post_count     = 0;
        //echo 'Initializing (' . $this->fbID . ')...<br />';

    }
    
    // }}}
    // {{{ get_fb_wall_feed()

    /**
     * Gets the Facebook Wall feed
     *
     * Gets the Facebook Wall feed which is sent from Facebook as JSON.  JSON
     * is then decoded and the resulting array is stored.
     *
     * @return string the facebook wall as html
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function get_fb_wall_feed() {
        //echo 'Contacting FaceBook...<br />';
        $id = $this->fb_id;
        $app_id = $this->fb_app_id;
        $limit = $this->fb_limit;
        $locale = $this->fb_locale;
        $token = 'access_token=' . $this->access_token;
        $result = '';

        // start building facebool wall feed
        $result .= '<div id="facebook_status_box">' .
                  '  <h2>' .
                  __( 'Facebook Status', JSL3_FWF_TEXT_DOMAIN ) . '</h2>' .
                  '  <div id="facebook_canvas">';

        // if limit is 0 then we are done
        if ( $limit == 0 ) {
            $result .=
                  '  </div>' .
                  '</div>';

            return $result;
        }

        // inital facebook graph call
        if ( $this->thorough )
            $fb_url = "https://graph.facebook.com/$id/feed?" .
                "locale=$locale&limit=100&$token";
        else
            $fb_url = "https://graph.facebook.com/$id/feed?" .
                "locale=$locale&limit=$limit&$token";
        $fb_url .= "&fields=from.fields(id,name),privacy,message,name,caption,description,id,created_time,picture,source,link,likes.limit(1).summary(true),properties,icon,story,comments";
        
        // loop until we have reached the limit or have the entire feed
        do {
            
            // get the next page
            if ( isset( $json_feed[ 'paging' ] ) )
                $fb_url = $json_feed[ 'paging' ][ 'next' ];
            
            //error_log( $fb_url );
            $raw_feed = $this->get_json_feed( $fb_url );
            //error_log( $raw_feed );
            $raw_feed =
                str_replace( '\n', '\u003cjsl3fwfbr \/\u003e', $raw_feed );
            //error_log( $raw_feed );
            $json_feed = json_decode( $raw_feed, TRUE );

            // get the data from the feed
            if ( isset( $json_feed[ 'data' ] ) ) {
                $result .=
                    $this->display_fb_wall_feed( $json_feed[ 'data' ] );
                
                // if we have reached the limit the exit
                if ( $this->post_count >= $limit )
                    break;
                $is_error = FALSE;
            
            // grab an error messages
            } elseif ( isset( $json_feed[ 'error' ] ) ) {
                $fb_feed = $json_feed[ 'error' ];
                $is_error = TRUE;
            
            // check if something else was sent from facebook
            } else {
                $is_error = TRUE;
            }
        
            // display error message
            if ( $is_error ) {
                $result .=
                  '    <div style="margin: 5px 0 15px; color: #000000; background-color: #FFEBE8; border-color: #CC0000; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; padding: 0 0.6em;">' .
                  '      <strong>';
                if ( $raw_feed == 'SERVER_CONFIG_ERROR' )
                    $result .=
                        __( 'Server Configuration Error: allow_url_fopen is off and cURL is not loaded.', JSL3_FWF_TEXT_DOMAIN );
                elseif ( isset( $fb_feed[ 'type' ] ) )
                    $result .=
                        $fb_feed[ 'type' ] . ': ' . $fb_feed[ 'message' ];
                elseif ( empty( $raw_feed ) )
                    $result .=
                        __( 'No feed returned. Please double check you have the correct Facebook ID, App ID, and App Secret.', JSL3_FWF_TEXT_DOMAIN );
                else
                    $result .= $raw_feed;
                $result .=
                  '      </strong>' .
                  '    </div>' .
                  '  </div>' .
                  '</div>';
            
                return $result;
            }

            // if not makeing a thorough search then exit before loop
            if ( ! $this->thorough )
                break;

        } while ( ! empty( $json_feed[ 'data' ] ) );
        
        $result .=
                  '  </div>' .
                  '</div>';

        $result = str_replace( '&lt;jsl3fwfbr /&gt;', '<br />', $result );

        if ( $this->make_link ) {
            $result = make_clickable( $result );

            if ( $this->new_win )
                $result = str_replace( 'rel="nofollow"', 'rel="nofollow" target="_blank"', $result );
        }
        
        return $result;
    
    } // End get_fb_wall_feed function
    
    // }}}
    // {{{ get_json_feed()

    /**
     * Gets the Facebook Wall feed
     *
     * Gets the Facebook Wall feed which is sent from Facebook as JSON.
     *
     * @param array $url the facebook graph url.
     *
     * @return string the raw facebook wall feed JSON
     *
     * @access public
     * @since Method available since Release 1.2
     */
    function get_json_feed( $url ) {
        $err_msg = '';
        $result = FALSE;
        
        // check if cURL is loaded
        if ( in_array( 'curl', get_loaded_extensions() ) ) {
            $ch = curl_init();
            
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, $this->verify );
            
            $result = curl_exec( $ch );

            //print_r( curl_getinfo( $ch ) );

            if ( ! $result ) 
                $err_msg = '[' . curl_errno( $ch ) . '] ' . curl_error( $ch );

            curl_close( $ch );
        }
        
        // check if allow_url_fopen is on
        if ( ! $result && ini_get( 'allow_url_fopen' ) ) {
            $result = @file_get_contents( $url );

            if ( ! $result && empty( $err_msg ) )
                $err_msg =
                    __( 'file_get_contents failed to open URL.', JSL3_FWF_TEXT_DOMAIN );
        
        }

        // no way to get the feed
        if ( ! $result && empty( $err_msg ) )
            $err_msg = 'SERVER_CONFIG_ERROR';

        if ( ! $result && ! empty( $err_msg ) )
            $result = $err_msg;

        return $result;

    }

    // }}}
    // {{{ display_fb_wall_feed()

    /**
     * Displays the Facebook Wall feed
     *
     * Parses the Facebook Wall feed data and prints out the wall feed html.
     *
     * @param array $fb_feed an array of Facebook Wall feed data.
     *
     * @return string the facebook wall as html
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function display_fb_wall_feed( $fb_feed ) {

        $result = '';
        $target = '';
        if ( $this->new_win )
            $target = ' target="_blank"';

        // loop through each post in the feed
        for ( $i = 0; $i < count( $fb_feed ); $i++) {
            
            // exit the loop if we have reached the limit
            if ( $this->post_count >= $this->fb_limit )
                break;

            $fb_id = $fb_feed[ $i ][ 'from' ][ 'id' ];

            // privacy check, if no privacy then assume public
            $privacy = 'EVERYONE';
            if ( isset( $fb_feed[ $i ][ 'privacy' ][ 'value' ] ) )
                $privacy = $fb_feed[ $i ][ 'privacy' ][ 'value' ];

            $privacy_good = FALSE;
            if ( empty( $privacy ) )
                $privacy_good = TRUE;
            elseif ( $this->fb_privacy == 'All' )
                $privacy_good = TRUE;
            elseif ( $this->fb_privacy == $privacy )
                $privacy_good = TRUE;
                    
            // check to see if we are not getting posts by other facebook
            // friends
            $show_post = FALSE;
            if ( $this->fb_id_only ) {
                if ( $this->fb_id == $fb_id ) {
                    if ( $privacy_good )
                        $show_post = TRUE;
                }
            } else {
                if ( $privacy_good )
                    $show_post = TRUE;
            }

            $is_status = TRUE;
            if ( isset( $fb_feed[ $i ][ 'message' ] ) ||
                isset( $fb_feed[ $i ][ 'name' ] ) ||
                isset( $fb_feed[ $i ][ 'caption' ] ) ||
                isset( $fb_feed[ $i ][ 'description' ] ) )
                $is_status = FALSE;
                
            // don't display posts without a message, name, caption,
            // or description they are just usually "is now friends with"
            // posts
            if ( $show_post && ( $this->show_status || ! $is_status ) ) {
                    
                $comment_link =
                    $this->fb_comment_link( $fb_feed[ $i ][ 'id' ] );
                $like_link =
                    $this->fb_like_link( $fb_feed[ $i ][ 'id' ] );
                if ( $this->profile )
                    $fb_photo  =
                        'https://graph.facebook.com/' . $fb_id .
                        '/picture?access_token=' . $this->access_token;
                else
                    $fb_photo  =
                        "https://graph.facebook.com/$fb_id/picture";
                $post_time =
                    $this->parse_fb_timestamp(
                        $fb_feed[ $i ][ 'created_time' ] );
                $fb_picture = NULL;
                if ( isset( $fb_feed[ $i ][ 'picture' ] ) )
                    $fb_picture =
                        $this->fb_fix( $fb_feed[ $i ][ 'picture' ] );
                $fb_source = NULL;
                if ( isset( $fb_feed[ $i ][ 'source' ] ) )
                    $fb_source = $fb_feed[ $i][ 'source' ];
                $fb_link = NULL;
                if ( isset( $fb_feed[ $i ][ 'link' ] ) )
                    $fb_link = $fb_feed[ $i ][ 'link' ];
                $fb_likes = 0;
                if ( isset( $fb_feed[ $i ][ 'likes' ][ 'summary' ][ 'total_count' ] ) )
                    $fb_likes = $fb_feed[ $i ][ 'likes' ][ 'summary' ][ 'total_count' ];
                $fb_prop = FALSE;
                $fb_prop_name = NULL;
                $fb_prop_text = NULL;
                $fb_prop_href = NULL;
                if ( isset( $fb_feed[ $i ][ 'properties' ][ 0 ] ) ) {
                    $fb_prop = TRUE;
                    if ( isset(
                        $fb_feed[ $i ][ 'properties' ][ 0 ][ 'name' ] ) )
                        $fb_prop_name =
                            $fb_feed[ $i ][ 'properties' ][ 0 ][ 'name' ];
                    if ( isset(
                        $fb_feed[ $i ][ 'properties' ][ 0 ][ 'text' ] ) )
                        $fb_prop_text =
                            $fb_feed[ $i ][ 'properties' ][ 0 ][ 'text' ];
                    if ( isset(
                        $fb_feed[ $i ][ 'properties' ][ 0 ][ 'href' ] ) )
                        $fb_prop_href =
                            $fb_feed[ $i ][ 'properties' ][ 0 ][ 'href' ];
                }

                $result .=
                  '    <div class="fb_post">' .
                  '      <div class="fb_photoblock">' .
                  '        <div class="fb_photo">' .
                  '          <a href="https://www.facebook.com/profile.php?id=' . $fb_id . '"' . $target . '>' .
                  '            <img src="' . $fb_photo . '" alt="' . __( 'Facebook Profile Pic', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
                  '          </a>' .
                  '        </div>' .
                  '        <div class="fb_photo_content">' .
                  '          <h5>' .
                  '            <a href="https://www.facebook.com/profile.php?id=' . $fb_id  . '"' . $target . '>' . $fb_feed[ $i ][ 'from' ][ 'name' ] . '</a>' .
                  '          </h5>' .
                  '          <div class="fb_time">';
                if ( $this->fb_icons && isset( $fb_feed[ $i ][ 'icon' ] ) )
                    $result .=
                  '            <img class="fb_post_icon" src="' . htmlentities( $fb_feed[ $i ][ 'icon' ], ENT_QUOTES, 'UTF-8' ) . '" alt="' . __( 'Facebook Icon', JSL3_FWF_TEXT_DOMAIN ) . '" />';
                $result .= $post_time .
                  '          </div>' .
                  '        </div>' .
                  '      </div>' .
                  '      <div class="fb_msg">';
                if ( isset( $fb_feed[ $i ][ 'story' ] ) )
                    $result .= 
                  '        <p class="fb_story">' . htmlentities( $fb_feed[ $i ][ 'story' ], ENT_QUOTES, 'UTF-8' ) . '</p>';
                if ( isset( $fb_feed[ $i ][ 'message' ] ) )
                    $result .= 
                  '        <p>' . htmlentities( $fb_feed[ $i ][ 'message' ], ENT_QUOTES, 'UTF-8' ) . '</p>';
                $result .= 
                  '        <div class="fb_link_post">';
                if ( isset( $fb_picture ) && isset( $fb_source ) )
                    $result .=
                  '          <a href="' . htmlentities( $fb_source, ENT_QUOTES, 'UTF-8' ) . '"' . $target . '>';
                elseif ( isset( $fb_picture ) && isset( $fb_link ) )
                    $result .=
                  '          <a href="' . htmlentities( $fb_link, ENT_QUOTES, 'UTF-8' ) . '"' . $target . '>';
                if ( isset( $fb_picture ) )
                    $result .= $fb_picture;
                if ( isset( $fb_picture ) && ( isset( $fb_source ) ||
                    isset( $fb_link ) ) )
                    $result .=
                  '          </a>';
                if ( isset( $fb_feed[ $i ][ 'name' ] ) )
                    $result .=
                  '          <h6><a href="' . htmlentities( $fb_link, ENT_QUOTES, 'UTF-8' ) . '"' . $target . '>' . htmlentities( $fb_feed[ $i ][ 'name' ], ENT_QUOTES, 'UTF-8' ) . '</a></h6>';
                if ( isset( $fb_feed[ $i ][ 'caption' ] ) )
                    $result .=
                  '          <p class="fb_cap">' . htmlentities( $fb_feed[ $i ][ 'caption' ], ENT_QUOTES, 'UTF-8' ) . '</p>';
                if ( isset( $fb_feed[ $i ][ 'description' ] ) )
                    $result .=
                  '          <p class="fb_desc">' . htmlentities( $fb_feed[ $i ][ 'description' ], ENT_QUOTES, 'UTF-8' ) . '</p>';
                if ( $fb_prop )
                    $result .=
                  '          <p class="fb_vid_length">';
                if ( isset( $fb_prop_name ) )
                    $result .= $fb_prop_name . ': ';
                if ( isset( $fb_prop_href ) )
                    $result .= '<a href="' . htmlentities( $fb_prop_href, ENT_QUOTES, 'UTF-8' ) . '"' . $target . '>';
                if ( isset( $fb_prop_text ) )
                    $result .= $fb_prop_text;
                if ( isset( $fb_prop_href ) )
                    $result .= '</a>';
                if ( $fb_prop )
                    $result .=
                  '          </p>';
                $result .=
                  '        </div>' .
                  '      </div>';
                if ( $this->show_comments &&
                    isset( $fb_feed[ $i ][ 'comments' ][ 'data' ] ) )
                    $result .= $this->display_comments(
                        $fb_feed[ $i ][ 'comments' ][ 'data' ] );
                $result .=
                  '      <div class="fb_commLink">' .
                  '        <span class="fb_likes">';
                if ( $fb_likes > 0 )
                    $result .=
                  '          <a class="tooltip" title="' . $fb_likes . ' ' . __( 'people like this', JSL3_FWF_TEXT_DOMAIN ) . '" href="' . $like_link . '"' . $target . '>' . $fb_likes . '</a>';
                $result .=
                  '        </span>' .
                  '        <span class="fb_comment">' .
                  '          <a href="' . $comment_link . '"' . $target . '>' . __( 'Comment', JSL3_FWF_TEXT_DOMAIN ) . '</a>' .
                  '        </span>' .
                  '      </div>' .
                  '      <div style="clear: both;"></div>' .
                  '    </div>';
                
                $this->post_count++;

            } // end if
            
        } // End for

        return $result;

    } // End display_fb_wall_feed function
    
    // }}}
    // {{{ display_comments()

    /**
     * Displays comments
     *
     * Parses the comments for a particular post and prints out the html.
     *
     * @param array $fb_feed an array of comment data.
     *
     * @return string the comments as html
     *
     * @access public
     * @since Method available since Release 1.2
     */
    function display_comments( $fb_feed ) {

        $result = '';
        $target = '';
        if ( $this->new_win )
            $target = ' target="_blank"';

        // loop through each post in the feed
        for ( $i = 0; $i < count( $fb_feed ); $i++) {
            
            $fb_id = $fb_feed[ $i ][ 'from' ][ 'id' ];

            // check to see if we are not getting posts by other facebook
            // friends
            $show_post = FALSE;
            if ( $this->fb_id_only ) {
                if ( $this->fb_id == $fb_id )
                    $show_post = TRUE;
            } else {
                $show_post = TRUE;
            }

            if ( $show_post ) {
            
                if ( $this->profile )
                    $fb_photo  = 'https://graph.facebook.com/' . $fb_id .
                        '/picture?access_token=' . $this->access_token;
                else
                    $fb_photo  = "https://graph.facebook.com/$fb_id/picture";
                $post_time = $this->parse_fb_timestamp(
                    $fb_feed[ $i ][ 'created_time' ] );
                $fb_comment_likes = 0;
                if ( isset( $fb_feed[ $i ][ 'like_count' ] ) )
                    $fb_comment_likes = $fb_feed[ $i ][ 'like_count' ];
                
                $result .=
              '          <div class="fb_comments">' .
              '            <div class="fb_photo">' .
              '              <a href="https://www.facebook.com/profile.php?id=' . $fb_id . '"' . $target . '>' .
              '                <img src="' . $fb_photo . '" alt="' . __( 'Facebook Profile Pic', JSL3_FWF_TEXT_DOMAIN ) . '" />' .
              '              </a>' .
              '            </div>' .
              '            <div class="fb_photo_content">' .
              '              <p>' .
              '                <a href="https://www.facebook.com/profile.php?id=' . $fb_id  . '"' . $target . '>' . $fb_feed[ $i ][ 'from' ][ 'name' ] . '</a>' .
              '                ' . htmlentities( $fb_feed[ $i ][ 'message' ], ENT_QUOTES, 'UTF-8' ) .
              '              </p>' .
              '              <p class="fb_time">';
                $result .= $post_time .
              '              </p>';
                if ( $fb_comment_likes > 0 )
                    $result .= 
              '              <span class="fb_comment_likes">' . $fb_comment_likes . '</span>';
                $result .=
              '            </div>' .
              '          </div>';

            } // End if
            
        } // End for

        return $result;

    } // End display_comments function
    
    // }}}
    // {{{ fb_comment_link()

    /**
     * Forms a Facebook comment link
     *
     * Forms a Facebook comment link by parsing the ID of the post.
     *
     * @param string $fb_story_id the id of the post to be parsed.
     *
     * @return string the parsed comment link
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function fb_comment_link( $fb_story_id ) {
        $link = 'https://www.facebook.com/permalink.php?';
        $split_id = explode( '_', $fb_story_id );
        $link .= 'id=' . $split_id[ 0 ] . '&amp;story_fbid=' . $split_id[ 1 ];

        return $link;
    }

    // }}}
    // {{{ fb_like_link()

    /**
     * Forms a Facebook like link
     *
     * Forms a Facebook like link by parsing the ID of the post.
     *
     * @param string $fb_story_id the id of the post to be parsed.
     *
     * @return string the parsed comment link
     *
     * @access public
     * @since Method available since Release 1.2
     */
    function fb_like_link( $fb_story_id ) {
        $link = 'https://www.facebook.com/';
        $split_id = explode( '_', $fb_story_id );
        $link .= $split_id[ 0 ] . '/posts/' . $split_id[ 1 ];

        return $link;
    }

    // }}}
    // {{{ parse_fb_timestamp()

    /**
     * Forms a time stamp
     *
     * Adjusts the time stamp to local time.
     *
     * @param string $fb_time the time stamp of the post.
     *
     * @return string the parsed time stamp.
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function parse_fb_timestamp( $fb_time ) {
        $time_stamp = explode( 'T', $fb_time );
        $date_str = $time_stamp[ 0 ];
        $date_items = explode( '-', $date_str );
        $time_arr = explode( ':', $time_stamp[ 1 ] );
        $time_hr = $time_arr[ 0 ] + get_option( 'gmt_offset' );
        if ( $time_hr < 0 ) {
            $time_hr += 24;
            $date_items[ 2 ]--;
        }

        $unix_time_stamp = mktime( $time_hr, $time_arr[ 1 ], 0,
                    $date_items[ 1 ], $date_items[ 2 ], $date_items[ 0 ] );
        $date_str = date_i18n( get_option( 'date_format' ), $unix_time_stamp );

        $time_str = date( get_option( 'time_format' ), $unix_time_stamp );

        return $date_str . ' ' . __( 'at', JSL3_FWF_TEXT_DOMAIN ) . ' ' . $time_str;
    }

    // }}}
    // {{{ fb_fix()

    /**
     * Facebook image fix
     *
     * Fixes issue with safe_image.php displaying 1 pixel image..
     *
     * @param string $str the image url
     *
     * @return string the fixed image url.
     *
     * @access public
     * @since Method available since Release 1.4
     */
    function fb_fix( $str ) {
        $pos = strpos( $str, 'safe_image.php' );
        if ( $pos !== FALSE ) {
            parse_str( $str );
            $str = $url;
        }

        $result = '<img src="' . htmlentities( $str, ENT_QUOTES, 'UTF-8' ) . '" alt="' . __( 'Facebook Picture', JSL3_FWF_TEXT_DOMAIN );
        if ( isset( $w ) && isset( $h ) )
            $result .= '" width="' . $w . '" height="' . $h;
        $result .= '" />';

        return $result;
    }

    // }}}

} // End UKI_Facebook_Wall_Feed class

// }}}

?>
