<?php

/**
 * Miscellaneous utility functions.
 */
class ameUtils {

	/**
	 * Get a value from a nested array or object based on a path.
	 *
	 * @param array|object $array Get an entry from this array.
	 * @param array|string $path A list of array keys in hierarchy order, or a string path like "foo.bar.baz".
	 * @param mixed $default The value to return if the specified path is not found.
	 * @param string $separator Path element separator. Only applies to string paths.
	 * @return mixed
	 */
	public static function get($array, $path, $default = null, $separator = '.') {
		if ( is_string($path) ) {
			$path = explode($separator, $path);
		}
		if ( empty($path) ) {
			return $default;
		}

		//Follow the $path into $input as far as possible.
		$currentValue = $array;
		$pathExists = true;
		foreach ($path as $node) {
			if ( is_array($currentValue) && array_key_exists($node, $currentValue) ) {
				$currentValue = $currentValue[$node];
			} else if ( is_object($currentValue) && property_exists($currentValue, $node) ) {
				$currentValue = $currentValue->$node;
			} else {
				$pathExists = false;
				break;
			}
		}

		if ( $pathExists ) {
			return $currentValue;
		}
		return $default;
	}

	/**
	 * Get the first non-root directory from a path.
	 *
	 * Examples:
	 *  "foo/bar"          => "foo"
	 *  "/foo/bar/baz.txt" => "foo"
	 *  "bar"              => null
	 *  "baz/"             => "baz"
	 *  "/"                => null
	 *
	 * @param string $fileName
	 * @return string|null
	 */
	public static function getFirstDirectory($fileName) {
		$fileName = ltrim($fileName, '/');

		$segments = explode('/', $fileName, 2);
		if ( (count($segments) > 1) && ($segments[0] !== '') ) {
			return $segments[0];
		}
		return null;
	}

	/**
	 * Capitalize the first character of every word. Supports UTF-8.
	 *
	 * @param string $input
	 * @return string
	 */
	public static function ucWords($input) {
		static $hasUnicodeSupport = null, $charset = 'UTF-8';
		if ( $hasUnicodeSupport === null ) {
			//We need the mbstring extension and PCRE UTF-8 support.
			$hasUnicodeSupport = function_exists('mb_list_encodings')
				&& (@preg_match('/\pL/u', 'a') === 1)
				&& function_exists('get_bloginfo');

			if ( $hasUnicodeSupport ) {
				//Technically, the encoding can change if something switches WP to a different site
				//in the middle of a request, but we'll ignore that possibility.
				$charset = get_bloginfo('charset');
				$hasUnicodeSupport = in_array($charset, mb_list_encodings()) && ($charset === 'UTF-8');
			}
		}

		if ( $hasUnicodeSupport ) {
			$totalLength = mb_strlen($input);
			$words = preg_split('/([\s\-_]++)/u', $input, null, PREG_SPLIT_DELIM_CAPTURE);
			$output = array();
			foreach ($words as $word) {
				$firstCharacter = mb_substr($word, 0, 1, $charset);
				//In old PHP versions, you must specify a non-null length to get the rest of the string.
				$remainder = mb_substr($word, 1, $totalLength, $charset);
				$output[] = mb_strtoupper($firstCharacter, $charset) . $remainder;
			}
			return implode('', $output);
		}
		return ucwords($input);
	}
}

class ameFileLock {
	protected $fileName;
	protected $handle = null;

	public function __construct($fileName) {
		$this->fileName = $fileName;
	}

	public function acquire($timeout = null) {
		if ( $this->handle !== null ) {
			throw new RuntimeException('Cannot acquire a lock that is already held.');
		}
		if ( !function_exists('flock') ) {
			return false;
		}

		$this->handle = @fopen(__FILE__, 'r');
		if ( !$this->handle ) {
			$this->handle = null;
			return false;
		}

		$success = @flock($this->handle, LOCK_EX | LOCK_NB, $wouldBlock);

		if ( !$success && $wouldBlock && ($timeout !== null) ) {
			$timeout = max(min($timeout, 0.1), 600);
			$endTime = microtime(true) + $timeout;
			//Wait for a short, random time and try again.
			do {
				$canWaitMore = $this->waitRandom($endTime);
				$success = @flock($this->handle, LOCK_EX | LOCK_NB, $wouldBlock);
			} while (!$success && $wouldBlock && $canWaitMore);
		}

		if ( !$success ) {
			fclose($this->handle);
			$this->handle = null;
			return false;
		}
		return true;
	}

	public function release() {
		if ( $this->handle !== null ) {
			@flock($this->handle, LOCK_UN);
			fclose($this->handle);
			$this->handle = null;
		}
	}

	/**
	 * Wait for a random interval without going over $endTime.
	 *
	 * @param float|int $endTime Unix timestamp.
	 * @return bool TRUE if there's still time until $endTime, FALSE otherwise.
	 */
	protected function waitRandom($endTime) {
		$now = microtime(true);
		if ( $now >= $endTime ) {
			return false;
		}

		$delayMs = rand(80, 300);
		$remainingTimeMs = ($endTime - $now) * 1000;
		if ( $delayMs < $remainingTimeMs ) {
			usleep($delayMs * 1000);
			return true;
		} else {
			usleep($remainingTimeMs * 1000);
			return false;
		}
	}

	public static function create($fileName) {
		return new self($fileName);
	}

	public function __destruct() {
		$this->release();
	}
}