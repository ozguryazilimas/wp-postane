<?php
/*
	Call this from wp-config.php to initialize everything as early as necessary.
*/

if ( ! class_exists('WPEngineProfiler') ) {		// guard against doubly-including this file

if ( ! defined('WPE_PROFILER_EMAIL') )
	define('WPE_PROFILER_EMAIL','mark@wpengine.com');

if ( ! defined('WPE_PROFILER_THRESHOLD_MS') )
	define('WPE_PROFILER_THRESHOLD_MS',10000);

if ( ! defined('WPE_PROFILER_MATCH_URL') )
	define('WPE_PROFILER_MATCH_URL','yazi/10marifet-ofisine-guzel-kizlar-geldi');

class WPEngineTimer
{
	private $start;
	
	public function __construct()
	{
		$this->start = microtime(true);
	}
	
	// Gets integer number of milliseconds since the start.
	// Optionally restart the timer.
	public function get_ms( $restart_timer = FALSE )
	{
		$now = microtime(true);
		$ms = round( 1000 * ($now - $this->start) );
		if ( $restart_timer )
			$this->start = $now;
		return $ms;
	}
}

class WPEngineProfiler
{
	private $min_duration_to_display = 10;		// has to be this number of ms or greater to log in our display
	
	private $log = array();
	private $timer;
	private $hook_timers = array();
	
	// Should the profiler even be attached?
	public static function is_profiler_enabled()
	{
		$url = $_SERVER['REQUEST_URI'];
		
		// No if this is a snapshot blog
		if ( isset($_SERVER['IS_WPE_SNAPSHOT']) ) return false;
		// No if we're not logged in
		if ( ! isset($_COOKIE[LOGGED_IN_COOKIE]) || empty($_COOKIE[LOGGED_IN_COOKIE]) ) return false;
		// Yes if it's matches the URL we're interested in profiling
		if ( preg_match('#'. WPE_PROFILER_MATCH_URL .'#',$url) ) return true;
		// No otherwise
		return false;
	}

	// Should we emit an HTML comment footer with profiling information?
	public static function should_emit_profiler_footer()
	{
		// Check for "Content-Type" header, looking for HTML.
		$is_html = FALSE;
		foreach ( headers_list() as $header ) {
			$parts = preg_split('#:\s+#',$header,2);
			if ( count($parts) == 2 ) {
				$key = strtolower(trim($parts[0]));
				$value = $parts[1];
				if ( $key == "content-type" ) {
					if ( 0 == strncasecmp( $value, "text/html", 9 ) ) {
						$is_html = TRUE;
						break;
					}
				}
			}
		}
		return $is_html;
	}
	
	public function __construct()
	{
		// Start the global timer
		$this->timer = new WPEngineTimer();
		// Hook PHP output buffer with our own call-back so we can do whatever we want.
		ob_start( array( $this, 'append_log_as_html_comment' ) );
		// Log that we started
		$this->log('sys','WordPress starting');
		// Hook every hook (and climb every mountain)
		add_action('all', array($this, 'start_hook_profile'), -10000000);
	}
	
	public function log( $type, $message, $duration = FALSE )
	{
		$ms = $this->timer->get_ms();
		$this->log[] = array (
			'time_index' => $ms,
			'duration' => $duration,
			'type' => $type,
			'message' => $message,
		);
	}

	public function start_hook_profile($what)
	{
		global $wp_filter;
		$tag = (empty($what)) ? current_filter() : $what;

		if (!empty($wp_filter[$tag])) {
			if (empty($wp_filter[$tag][10000000]['wpe_profiler_hooker'])) {
				$wp_filter[$tag][10000000]['wpe_profiler_hooker'] = array(
					'function'      => array($this, 'end_hook_profile'),
					'accepted_args' => 1,
				);
			}
			$this->hook_timers[$tag] = new WPEngineTimer();
			$this->log('hook_st',$tag);
			
			// Start: tag-dumping section.
			// Enable the following to dump the things hooking a particular tag
			/*
			if ( $tag == "admin_init" ) {
				foreach( $wp_filter[$tag] as $hook ) {
					$this->log('sys',"hooked $tag: " . var_export($hook,TRUE) );
				}
			}
			*/
			// End: tag-dumping section
		}
	}

	public function end_hook_profile($arg = null, $what = null)
	{
		$tag = (empty($what)) ? current_filter() : $what;
		$duration_ms = $this->hook_timers[$tag]->get_ms();
		$this->log('hook_end',$tag,$duration_ms);
		return $arg;
	}
	
	public function append_log_as_html_comment( $html )
	{	
		global $wpe_wpdb;
		if ( isset($wpe_wpdb) )
			$wpe_wpdb->log_summary();
		$this->log('sys','Request finished.');

		// Make sure we should even emit this.
		if ( ! WPEngineProfiler::should_emit_profiler_footer() )
			return $html;
		
		// Make a pass, computing the "duration" of each call, which is actually the
		// time until the next one, which isn't necessarily due just to it.
		// Don't override existing durations if we had one.
		for( $k = 1 ; $k < count($this->log) ; $k++ ) {
			if ( FALSE === $this->log[$k-1]['duration'] ) {
				$prev_ms = $this->log[$k-1]['time_index'];
				$ms = $this->log[$k]['time_index'];
				$this->log[$k-1]['duration'] = $ms - $prev_ms;
			}
		}
		
		// Create the log, in HTML
		$html_log  = "   Server: ".gethostname()."\n";
		$html_log .= "Timestamp: ".date('c')."\n";
		$html_log .= "  Account: ".PWP_NAME."\n";
		$html_log .= "   Domain: ".$_SERVER['HTTP_HOST']."\n";
		$html_log .= "     Path: ".$_SERVER['REQUEST_URI']."\n";
		$html_log .= "  Referer: ".$_SERVER['HTTP_REFERRER']."\n";
		$html_log .= "\n";
		$min_duration = $this->min_duration_to_display;
		$line_counter = 0;
		foreach ( $this->log as $line ) {
			$line_counter++;
			$ms = $line['time_index'];
			$type = $line['type'];
			$duration = $line['duration'];
			$message = $line['message'];
			if ( $type == 'sys' || ( $duration >= $min_duration && $type != 'hook_st' ) ) {
				if ( is_bool($message) ) $message = $message ? 'TRUE' : 'FALSE';		// convert booleans to readable values
				if ( is_object($message) || is_array($message) ) $message = var_export($message,TRUE);	// convert to string
				$html_log .= sprintf("#%4d: %5d (%3d)ms: %8s: %s\n", $line_counter, $ms, $duration, $type, $line['message']);
			}
		}
		$html_log .= "\nPHP Environment:\n";
		foreach ( $_SERVER as $key => $value ) {
			$html_log .= "\$_SERVER['$key']=$value\n";
		}


		// If running time too large, email tech support
		if ( $this->timer->get_ms() > WPE_PROFILER_THRESHOLD_MS ) {
			$html_log .= " +++ Emailed this slow page-load to WP Engine tech support.\n";
			// mail( WPE_PROFILER_EMAIL, "Slow admin page for ".PWP_NAME, $html_log, 'From: support@wpengine.com' );
		}

		// Append the log to the HTML we're about to return, and return it
		$html_log = "\n\n<!-- \n$html_log\n -->\n\n";
		return $html . $html_log;
	}
	
}

// Set up global instance, or null if the profiler is disabled
if ( !isset($wpe_profiler) ) {
	if ( WPEngineProfiler::is_profiler_enabled() )
		$wpe_profiler = new WPEngineProfiler();
	else
		$wpe_profiler = null;
}

}		// endif: class exists
