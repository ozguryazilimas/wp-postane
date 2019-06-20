<?php

/**
 * Template related functionality.
 *
 * @author Alexander Teshabaev <sasha.tesh@gmail.com>
 */
class APT_Template {
	/**
	 * Render and return content of the template.
	 *
	 * @param string $name Name of the template to get content of. Could be absolute or relative path. When relative
	 * path used, it will be based on plugins /templates/ dir, absolute will be used as it is.
	 *
	 * @return mixed
	 */
	public static function render ( $name ) {
		ob_start();
		if ( is_callable( $name ) ) {
			echo call_user_func( $name );
		} elseif ( strpos( $name, DIRECTORY_SEPARATOR ) !== false && ( is_file( $name ) || is_file( $name . '.php' ) ) ) {
			if ( is_file( $name ) ) {
				$path = $name;
			} else {
				$path = $name . '.php';
			}
		} else {
			$path = APT_ABSPATH . "/views/{$name}.php";
		}
		if ( ! is_file( $path ) ) {
			return '';
		}
		include $path;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
