<?php

/**
 * Contains the JSL3_FWF_Widget class
 *
 * Contains the JSL3_FWF_Widget class.  See class desciption for more
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

// {{{ JSL3_FWF_Widget

/**
 * Creates the JSL3 Facebook Wall Feed plugin widget
 *
 * Create a widget that can be dragged onto a sidebar.  The widget maintains
 * two properties: title and limit.  The widget also creates a list of your
 * Facebook wall posts.
 *
 * @category   WordPress_Plugin
 * @package    JSL3_FWF
 * @author     Takanudo <fwf@takanudo.com>
 * @author     Fedil Grogan <fedil@ukneeq.com>
 * @copyright  2011-2013
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    1.7.2
 * @link       http://takando.com/jsl3-facebook-wall-feed
 * @since      File available since Release 1.0
 */

class JSL3_FWF_Widget extends WP_Widget {

    // {{{ JSL3_FWF_Widget()

    /**
     * Constructor for this class
     *
     * Constructor sets the name and description of the widget.
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function JSL3_FWF_Widget() {
        parent::WP_Widget( false,
            $name = __( 'JSL3 Facebook Wall Feed', JSL3_FWF_TEXT_DOMAIN ),
            array( 'description' =>
                __( 'Display your facebook wall', JSL3_FWF_TEXT_DOMAIN ) ) );

    }

    // }}}
    // {{{ widget()

    /**
     * Actual widget output
     *
     * Actual widget ouput for a page, post, etc.  The actual code to display
     * the Facebook feed was originally written by Fedil Grogan.
     *
     * @param array $args Display arguments including before_title,
     *                    after_title, before_widget, and after_widget
     * @param array $instance The settings for the particular instance of the
     *                        widget.
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function widget( $args, $instance ) {
        extract( $args );

        // The widget title
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );

        // The number of facebook wall posts to get
        $limit = apply_filters( 'widget_title', $instance[ 'limit' ] );

        // The Facebook ID
        $fb_id = NULL;
        if ( isset( $instance[ 'fb_id' ] ) )
            $fb_id = apply_filters( 'widget_title', $instance[ 'fb_id' ] );

        // Before widget
        echo $before_widget;

        // Title of widget
        if ( $title ) { echo $before_title . $title . $after_title; }

        // Widget output
        $jsl3_fwf = new JSL3_Facebook_Wall_Feed();
        $dev_options = $jsl3_fwf->get_admin_options();

        if ( ! isset( $fb_id ) )
            $fb_id = $dev_options[ 'fb_id' ];

        $feed = new UKI_Facebook_Wall_Feed(
            $fb_id,
            FALSE,
            FALSE,
            $limit,
            $dev_options[ 'token' ],
            $dev_options[ 'fb_id_only' ],
            $dev_options[ 'privacy' ],
            $dev_options[ 'thorough' ],
            $dev_options[ 'new_window' ],
            $dev_options[ 'make_clickable' ],
            $dev_options[ 'show_status' ],
            $dev_options[ 'show_comments' ],
            $dev_options[ 'locale' ],
            $dev_options[ 'verify' ],
            $dev_options[ 'profile' ],
            $dev_options[ 'fb_icons' ] );
        //echo wp_kses_post( $feed->get_fb_wall_feed() );
        echo $feed->get_fb_wall_feed();

        // After widget
        echo $after_widget;
    } // End widget function

    // }}}
    // {{{ update()

    /**
     * Stores the properties into the WordPress databases.
     *
     * Gets the widget form properties and stores them into the WordPress
     * database.
     *
     * @param array $new_instance New settings for this instance as input by
     *                            the user via form().
     * @param array $old_instance Old settings for this instance.
     *
     * @return array Settings to save or bool false to cancel saving.
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( trim( $new_instance[ 'title' ] ) );
        $limit = strip_tags( trim( $new_instance[ 'limit' ] ) );
        if ( is_numeric( $limit ) && $limit >= 0 )
            $instance[ 'limit' ] = $limit;
        else
            $instance[ 'limit' ] = JSL3_FWF_WIDGET_LIMIT;
        $fb_id = strip_tags( trim( $new_instance[ 'fb_id' ] ) );
        if ( is_numeric( $fb_id ) && $fb_id >= 0 )
            $instance[ 'fb_id' ] = $fb_id;
        else
            $instance[ 'fb_id' ] = NULL;
            
        return $instance;
    }

    // }}}
    // {{{ form()

    /**
     * Creates the widget form
     *
     * Creates the widget form for the sidebar.  This widget takes in two
     * properties: title and limit
     *
     * @param array $instance Current settings
     *
     * @access public
     * @since Method available since Release 1.0
     */
    function form( $instance ) {
        $defaults = array(
            'title' => JSL3_FWF_WIDGET_TITLE,
            'fb_id' => '',
            'limit' => JSL3_FWF_WIDGET_LIMIT );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = esc_attr( $instance[ 'title' ] );
        $limit = esc_attr( $instance[ 'limit' ] );
        $fb_id = esc_attr( $instance[ 'fb_id' ] );
?>
<p>
  <label>
    <?php _e( 'Title', JSL3_FWF_TEXT_DOMAIN ); ?>:
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
  </label>
  <label>
    <?php _e( 'Facebook ID', JSL3_FWF_TEXT_DOMAIN ); ?>:
    <input class="widefat" id="<?php echo $this->get_field_id( 'fb_id' ); ?>" name="<?php echo $this->get_field_name( 'fb_id' ); ?>" type="text" value="<?php echo $fb_id; ?>" />
  </label>
  <label>
    <?php _e( 'Number of wall posts to get', JSL3_FWF_TEXT_DOMAIN ); ?>:
    <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo $limit; ?>" />
  </label>
</p>
<?php
    }

    // }}}

} // End jsl3fwf_widget class

// }}}

?>
