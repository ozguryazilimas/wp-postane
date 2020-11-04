<?php

/**
 * Add notification for headline analyzer
 * Recurrence: 60 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_Headline_Analyzer extends ExactMetrics_Notification_Event {

	public $notification_id             = 'exactmetrics_notification_headline_analyzer';
	public $notification_interval       = 60; // in days
	public $notification_first_run_time = '+7 day';
	public $notification_type           = array( 'basic', 'lite', 'master', 'plus', 'pro' );

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {
		$notification['title']   = __( 'Headline Analyzer to Boost Your Clicks & Traffic', 'google-analytics-dashboard-for-wp' );
		// Translators: Headline Analyzer notification content
		$notification['content'] = sprintf( __( 'Did you know that 36%% of SEO experts think the headline is the most important SEO element? Yet many website owners don’t know how to optimize their headlines for SEO and clicks. Instead, they write copy and hope for the best, only to see disappointing results. Now there’s an easier way! <br><br>%sWith the ExactMetrics Headline Analyzer%s, you can get targeted suggestions to improve your headlines, right in the WordPress editor.', 'google-analytics-dashboard-for-wp' ), '<a href="'. $this->build_external_link('https://www.exactmetrics.com/introducing-exactmetrics-built-in-headline-analyzer/' ) .'" target="_blank">', '</a>' );
		$notification['btns'] = array(
			"learn_more" => array(
				'url'   => $this->build_external_link('https://www.exactmetrics.com/introducing-exactmetrics-built-in-headline-analyzer/' ),
				'text'  => __( 'Learn More', 'google-analytics-dashboard-for-wp' )
			),
		);

		return $notification;
	}

}

// initialize the class
new ExactMetrics_Notification_Headline_Analyzer();
