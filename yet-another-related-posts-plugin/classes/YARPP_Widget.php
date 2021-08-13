<?php

class YARPP_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'description' => 'Related Posts and/or Sponsored Content',
			'show_instance_in_rest' => true
		);
		add_filter( 'widget_types_to_hide_from_legacy_widget_block', array($this,'hide_yarpp_widget_legacy_editor') );
		parent::__construct( false, 'Related Posts (YARPP)', $widget_ops );
	}

	public function widget( $args, $instance ) {
		if ( ! is_singular() ) {
			return;
		}

		global $yarpp;
		extract( $args );

		/* Compatibility with pre-3.5 settings: */
		if ( isset( $instance['use_template'] ) ) {
			$instance['template'] = ( $instance['use_template'] ) ? ( $instance['template_file'] ) : false;
		}

		// Per display_related the template must be false if "list" template was selected
		if ( $instance['template'] === 'list' || $instance['template'] === 'builtin' ) {
			$instance['template'] = false;
		}

		$instance['heading'] = $this->get_heading($instance);
		$heading             = apply_filters( 'widget_title', $instance['heading'] );
		$output              = $before_widget;
		if ( ! $instance['template'] ) {
			$output .= $before_title;
			$output .= $heading;
			$output .= $after_title;
		}
		$instance['domain'] = 'widget';
		$output            .= $yarpp->display_related( null, $instance, false );
		$output            .= $after_widget;
		echo $output;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array(
			'template'           => false,
			'heading'            => $new_instance['heading'],
			'use_pro'            => false,
			'pro_dpid'           => null,
			'promote_yarpp'      => false,
		);

		if ( isset($new_instance['use_template']) && $new_instance['use_template'] === 'thumbnails' ) {
			$instance['template'] = 'thumbnails';
		} elseif ( isset($new_instance['use_template']) && $new_instance['use_template'] === 'custom' ) {
			$instance['template'] = $new_instance['template_file'];
		} else {
			$instance['template'] = isset($new_instance['template_file']) ? $new_instance['template_file'] : false;
		}

		// Legacy Widget block triggers this function on save but with the new instance.
		if ( isset($new_instance['template']) ) {
			$instance['template'] = $new_instance['template'];
		}

		return $instance;
	}

	public function form( $instance ) {
		global $yarpp;
		$id       = rtrim( $this->get_field_id( null ), '-' );
		$instance = wp_parse_args(
			$instance,
			array(
				'heading'            => $this->get_heading($instance),
				'template'           => false,
				'use_pro'            => false,
				'pro_dpid'           => null,
				'promote_yarpp'      => false,
			)
		);

		/*
		 * TODO: Deprecate
		 * Compatibility with pre-3.5 settings
		 */
		if ( isset( $instance['use_template'] ) ) {
			$instance['template'] = $instance['template_file'];
		}

		$choice = ( $instance['template'] ) ? ( ( $instance['template'] === 'thumbnails' ) ? 'thumbnails' : 'custom' ) : 'builtin';

		/* Check if YARPP templates are installed */
		$block_templates = $yarpp->get_all_templates();

		if ( ! $yarpp->diagnostic_custom_templates() && $choice === 'custom' ) {
			$choice = 'builtin';
		}

		include YARPP_DIR . '/includes/phtmls/yarpp_widget_form.phtml';
	}

	/**
	 * Hides the yarpp widget from the block list
	 * WordPress 5.8.0 - https://developer.wordpress.org/block-editor/how-to-guides/widgets/legacy-widget-block/#3-hide-the-widget-from-the-legacy-widget-block
	 *
	 */
	public function hide_yarpp_widget_legacy_editor( $widget_types ) {
		$widget_types[] = 'yarpp_widget';
		return $widget_types;
	}

	/**
	 * Get the heading of the widget backwards compatibility
	 *
	 * @param object $instance
	 * @return string
	 */
	protected function get_heading( $instance ) {
		$heading = __('You may also like', 'yet-another-related-posts-plugin');

		if ( empty($instance) ) {
			return $heading;
		}

		if ( $instance['template'] === 'thumbnails' && isset($instance['thumbnails_heading']) ) {
			$heading = $instance['thumbnails_heading'];
		} elseif ( $instance['template'] === false && isset($instance['title']) ) {
			$heading = $instance['title'];
		} elseif ( ! empty($instance['heading']) ) {
			$heading = $instance['heading'];
		}

		return $heading;
	}
}

/**
 * @since 2.0 Add as a widget
 */
function yarpp_widget_init() {
	register_widget( 'YARPP_Widget' );
}

add_action( 'widgets_init', 'yarpp_widget_init' );
