<?php

/**
 * Copyright (c) 2009 Dave Ross <dave@csixty4.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * */

/**
 * Special wrapper around the WP transients API
 */
class DWLSTransients {

	static function offset( $clear = false ) {
		static $offset = null;

		if ( $offset === null ) {
			$offset = get_transient( "dwls_offset" );
			if ( $offset === false ) {
				$offset = 0;
				set_transient( "dwls_offset", $offset );
			}
		}

		if ( $clear ) {
			$offset += 1;
			if ( $offset > 99 ) {
				$offset = 1;
			}
			set_transient( "dwls_offset", $offset );
		}

		return $offset;
	}

	static function clear() {
		self::offset( true );
	}

	static function set( $key, $value, $expiration ) {
		$offset = self::offset();
		$hash = md5( $key );
		set_transient( "dwls_res{$offset}_{$hash}", $value, $expiration );
	}

	static function get( $key ) {
		$hash = md5( $key );
		$offset = self::offset();
		$cache = get_transient( "dwls_res{$offset}_{$hash}" );
		if ( $cache ) {
			return $cache;
		}

		return false;
	}

	static function indexes() {
		return array();
	}

}
