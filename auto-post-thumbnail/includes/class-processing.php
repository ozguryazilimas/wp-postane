<?php

namespace WBCR\APT;

use WBCR\Factory_Processing_104\WP_Background_Process;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for generating in the background
 *
 * @author        Artem Prikhodko <webtemyk@yandex.ru>
 * @copyright (c) 2022, CreativeMotion
 * @version       1.0
 */
class ProcessingBase extends WP_Background_Process {

	protected function task( $item ) {
	}
}
