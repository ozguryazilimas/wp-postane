<?php


/**
 * Via http://justintadlock.com/archives/2009/05/26/the-complete-guide-to-creating-widgets-in-wordpress-28
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */

add_action( 'widgets_init', 'portfolio_slideshow_widget' );

/**
 * Register our widget.
 *
 * @since 0.1
 */
if ( ! function_exists( 'portfolio_slideshow_widget' ) ) { 
	function portfolio_slideshow_widget() {
		register_widget( 'Portfolio_Slideshow_Widget' );
	}
}

/**
 * Example Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Portfolio_Slideshow_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Portfolio_Slideshow_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'ps-widget', 'description' => __('Add a slideshow to any widgetized area.', 'portfolio-slideshow-pro') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 350, 'id_base' => 'ps-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'ps-widget', __('Portfolio Slideshow', 'portfolio-slideshow-pro'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$id = $instance['ID'];
		$image_size = $instance['image_size'];
		$show_nav = isset( $instance['show_nav'] ) ? $instance['show_nav'] : false;
		$autoplay = isset( $instance['autoplay'] ) ? $instance['autoplay'] : false;
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* Display name from widget settings if one was input. */
		if ( $id && $image_size ) {
			
			//echo "portfolio_slideshow id=$id size=$image_size";

			$shortcode = "portfolio_slideshow id=". $id . " size=" . $image_size . " showtitles=false showcaps=false showdesc=false pagerpos=disabled";
			if ( $show_nav ) {
			$shortcode .= " navpos=top";
			} else { $shortcode .= " navpos=disabled"; }

			if ( $autoplay ) {
			$shortcode .= " autoplay=true";
			} else { $shortcode .= " autoplay=false"; }
			
			echo do_shortcode("[$shortcode]");

		}
	
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		$instance['ID'] = strip_tags( $new_instance['ID'] );
		
		/* No need to strip tags for image_size and show_image_size. */
		$instance['image_size'] = $new_instance['image_size'];

		$instance['show_nav'] = isset($new_instance['show_nav']);
		
		$instance['autoplay'] = isset($new_instance['autoplay']);
		
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'ID' => __('', 'portfolio-slideshow-pro'), 'title' => __('title', 'portfolio-slideshow-pro'), 'image_size' => 'thumbnail', 'show_image_size' => true, 'show_nav' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'portfolio-slideshow-pro' );?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>
			
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'ID' ); ?>"><?php _e('Slideshow ID:', 'portfolio-slideshow-pro'); ?></label>
			<input id="<?php echo $this->get_field_id( 'ID' ); ?>" name="<?php echo $this->get_field_name( 'ID' ); ?>" value="<?php echo $instance['ID']; ?>" style="width:20%;" />
		</p>

		<!-- Image Size -->
		<p>
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e('Image Size:', 'portfolio-slideshow-pro'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>" class="widefat" style="width:40%;">
				<?php // Get the intermediate image sizes, add full & custom sizes size to the array.
				$sizes = get_intermediate_image_sizes();
				$sizes[] = 'full';

				// Loop through each of the image sizes.
				foreach ( $sizes as $size ) {
					if ( $size != "ps-thumb" ) {
						echo "<option value='$size'";
						if ( $instance['image_size'] == $size ){
							echo " selected='selected'"; 
						}
						echo " style='width:20%;'>$size</option>";
					}
				}?>
			</select>
		</p>
		<!-- Show Navigation -->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_nav'], true ); ?> id="<?php echo $this->get_field_id( 'show_nav' ); ?>" name="<?php echo $this->get_field_name( 'show_nav' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_nav' ); ?>"><?php _e( 'Display navigation?', 'portfolio-slideshow-pro' );?></label>
		</p>
	
		<!-- Autoplay -->
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['autoplay'], true ); ?> id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'autoplay' ); ?>"><?php _e( 'Autoplay?', 'portfolio-slideshow-pro' );?></label>
		</p>
	


<?php } }?>
