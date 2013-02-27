<?php
/*
Plugin Name: WPEngine Profiler
Plugin URI: http://wpengine.com
Description: Tools for profiling WordPress
Author: Jason Cohen
Version: 0.4
Author URI: http://blog.asmartbear.com/
*/

require_once( dirname(__FILE__)."/wpe-profiler-preloader.php");

// Only run this part of the plugin if the profiler is enabled
if ( isset($wpe_profiler) && $wpe_profiler ) {

// Class to replace WPDB by wrapping it.
class WPEngine_WPDB {
	
	private $wpdb;
	
	// Create with existing $wpdb object to wrap it
	public function __construct( $wpdb )
	{
		$this->wpdb = $wpdb;
	}
	
	// All method invocations pass through, and we might do stuff before and after.
	public function __call( $name, $args )
	{
		$state = $this->pre_call($name,$args);
		$result = call_user_func_array( array($this->wpdb,$name), $args );
		$this->post_call($name,$args,$result,$state);
		return $result;
	}
	public static function __callStatic( $name, $args )
	{
		$state = $this->pre_call($name,$args);
		$result = call_user_func_array( array('wpdb',$name), $args );
		$this->post_call($name,$args,$result,$state);
		return $result;
	}
	
	// Member variables are passed through
	public function __set( $name, $value )
	{
		$this->wpdb->$name = $value;
	}
	public function __get( $name )
	{
		return $this->wpdb->$name;
	}
	public function __isset( $name )
	{
		return isset($this->wpdb->$name);
	}
	public function __unset( $name )
	{
		unset($this->wpdb->$name);
	}
	
	// Stuff we do before a method invocation, returning an object that will be passed back in later.
	private function pre_call( $name, $args )
	{
		$state = array();
		$state['name'] = $name;
		$state['start'] = microtime(true);
		return $state;
	}
	
	// Stuff we do after a method invocation, passing in the state object from pre_call() and the result of the function call.
	private function post_call( $name, $args, $result, $state )
	{
		global $total_duration, $total_queries, $wpe_profiler;
		
		$duration_ms = round( (microtime(true) - $state['start']) * 1000 );
		$total_duration += $duration_ms;
		$total_queries++;
		$wpe_profiler->log('wpdb', "#/ms = $total_queries/$total_duration; $name: $duration_ms ms, last query: ".$this->wpdb->last_query );
	}
	
	public function log_summary()
	{
		global $total_duration, $total_queries, $wpe_profiler;
		$wpe_profiler->log('sys', "wpdb: $total_queries calls totalling $total_duration ms" );
	}
}

$wpe_wpdb = new WPEngine_WPDB( $wpdb );
$wpdb = $wpe_wpdb;

}		// endif: profiler enabled

