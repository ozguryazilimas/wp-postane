<?php

namespace WBCR\APT;

use WAPT_Plugin, Exception;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for image processing
 *
 * @author        Artem Prikhodko <webtemyk@yandex.ru>
 * @copyright (c) 2020 Webraftic Ltd
 * @version       1.0
 */
class Image {

	/**
	 * @see self::app()
	 * @var Image
	 */
	private static $app;

	/**
	 * @var integer
	 */
	public $width;

	/**
	 * @var integer
	 */
	public $height;

	/**
	 * @var string
	 */
	private $font_path = WAPT_PLUGIN_DIR . '/fonts/arial.ttf';

	/**
	 * @var integer
	 */
	public $font_size;

	/**
	 * @var string|array
	 */
	public $font_color;

	/**
	 * @var string
	 */
	public $text;

	/**
	 * @var string
	 */
	public $background;

	/**
	 * @var string
	 */
	private $reference_text = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ-!?.,_"[]';

	/**
	 * @var Resource
	 */
	private $image;

	/**
	 * @var string
	 */
	public $padding_left = 0;

	/**
	 * @var string
	 */
	public $padding_top = 0;

	/**
	 * @var string
	 */
	public $line_spacing = 1;

	/**
	 * @var array
	 */
	public $params = [];

	/**
	 * @return
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param string $padding_left
	 * @param string $padding_top
	 */
	public function setPadding( $padding_left, $padding_top ) {
		$this->padding_left = $padding_left;
		$this->padding_top  = $padding_top;
	}

	/**
	 * @param int $width
	 */
	public function setWidth( $width ) {
		$this->width = $width;
	}

	/**
	 * @param int $height
	 */
	public function setHeight( $height ) {
		$this->height = $height;
	}

	/**
	 * @param string $font_path
	 */
	public function setFontPath( $font_path ) {
		if ( file_exists( $font_path ) ) {
			$this->font_path = $font_path;
		}
	}

	/**
	 * {PLUGIN_DIR}/fonts/{font}.ttf
	 *
	 * @param string $font
	 */
	public function setFont( $font ) {
		$this->font_path = WAPT_PLUGIN_DIR . "/fonts/{$font}.ttf";
	}

	/**
	 * @param int $font_size
	 */
	public function setFontSize( $font_size ) {
		$this->font_size = $font_size;
	}

	/**
	 * @param array|string $font_color
	 */
	public function setFontColor( $font_color ) {
		$this->font_color = $font_color;
	}

	/**
	 * @param string $text
	 */
	public function setText( $text ) {
		$this->text = $text;
	}

	/**
	 * @param array|string $background
	 */
	public function setBackground( $background ) {
		$this->background = $background;
	}

	/**
	 * Конструктор
	 *
	 * @param string $width
	 * @param string $height
	 * @param array|string $background = '#ffffff'
	 * @param string $font = ''
	 * @param integer $font_size = 0
	 * @param string $font_color = '#000000'
	 */
	public function __construct( $width, $height, $background = '#ffffff', $font = '', $font_size = 0, $font_color = '#000000' ) {
		self::$app = $this;

		$this->width      = $width;
		$this->height     = $height;
		$this->background = $background;
		$this->font_path  = $font;
		$this->font_size  = $font_size;
		$this->font_color = $font_color;

		$this->image = $this->create( $width, $height, $background );
	}

	/**
	 * Статический метод для быстрого доступа к интерфейсу плагина.
	 *
	 * @return Image
	 */
	public static function app() {
		return self::$app;
	}

	/**
	 * Create image
	 *
	 * @return Resource
	 */
	public function create( $width, $height, $background = '#ffffff' ) {
		if ( is_numeric( $background ) ) { //image
			$image = wp_get_attachment_metadata( $background );
			if ( $image ) {
				$upload_dir = wp_upload_dir();
				$file_path  = $upload_dir['basedir'] . '/' . $image['file'];
				$file_type  = wp_check_filetype( $file_path );
				switch ( $file_type['type'] ) {
					case 'image/jpeg':
						$im = imagecreatefromjpeg( $file_path );
						$this->setWidth( $image['width'] );
						$this->setHeight( $image['height'] );
						break;

					case 'image/png':
						$im = imagecreatefrompng( $file_path );
						imagesavealpha( $im, true );
						$this->setWidth( $image['width'] );
						$this->setHeight( $image['height'] );
						break;

					default:
						$im = $this->create( $width, $height );
						break;
				}
			} else {
				$im = $this->create( $width, $height );
			}
		} else { //color
			$im       = imagecreatetruecolor( $width, $height );
			$color    = $this->color_hex_to_rgb( $background );
			$bg_color = imagecolorallocate( $im, $color['r'], $color['g'], $color['b'] );
			imagefill( $im, 0, 0, $bg_color );
		}

		return $im;
	}

	/**
	 * Convert hex color to RGB
	 *
	 * @param string $hex
	 *
	 * @return array
	 */
	private function color_hex_to_rgb( $hex = '' ) {
		if ( empty( $hex ) ) {
			$hex = $this->font_color;
		}
		[ $r, $g, $b ] = sscanf( $hex, '#%02x%02x%02x' );

		return [
			'r' => $r,
			'g' => $g,
			'b' => $b,
		];
	}

	/**
	 * Get width of the letter in the font.
	 *
	 * return array(
	 *      'width' => int
	 *      'height' => int
	 * );
	 * OR false
	 *
	 * @return array|false
	 */
	public function get_font_char_size() {
		if ( $this->font_path !== '' && $this->font_size !== 0 ) {

			$text = ! empty( $this->text ) ? $this->text : $this->reference_text;
			//$txt_image = $this->create( 500, 500 );
			$box    = imagettfbbox( $this->font_size, 0, $this->font_path, $text );
			$width  = ceil( ( $box[2] - $box[0] ) / strlen( $text ) );
			$height = $box[1] - $box[7];
			$result = [
				'width'  => $width ? $width : 1, //средняя ширина одного символа
				'height' => $height ? $height : 1, //высота одного символа
			];

			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Write text on the image
	 *
	 * @param string $text
	 * @param string $font = ''
	 * @param integer $font_size = 0
	 * @param string $font_color = '#000000'
	 * @param string $align
	 * @param string $valign
	 * @param float $line_spacing
	 * @param string $shadow_color
	 *
	 * @return bool
	 */
	public function write_text( $text, $font = '', $font_size = '', $font_color = '', $align = 'left', $valign = 'top', $line_spacing = '1.5', $shadow_color = '' ) {
		if ( ! empty( $text ) ) {
			$text = mb_convert_encoding( $text, 'UTF-8' );

			if ( empty( $font ) ) {
				$font = $this->font_path;
			}
			if ( empty( $font_size ) ) {
				$font_size = $this->font_size;
			}
			if ( empty( $font_color ) ) {
				$font_color = $this->font_color;
			}
			$this->setText( $text );
			$char_size = $this->get_font_char_size();

			$pad_left = (int) $this->padding_left;
			$pad_top  = (int) $this->padding_top;

			$color      = $this->color_hex_to_rgb( $font_color );
			$font_color = imagecolorallocate( $this->image, $color['r'], $color['g'], $color['b'] );
			if ( ! empty( $shadow_color ) ) {
				$color        = $this->color_hex_to_rgb( $shadow_color );
				$shadow_color = imagecolorallocate( $this->image, $color['r'], $color['g'], $color['b'] );
			}
			$line_spacing = (float) $line_spacing;

			$width  = $this->width - $pad_left * 2;
			$height = $this->height - $pad_top * 2;

			$chars_per_line = ceil( $width / $char_size['width'] * 0.9 ); //count of chars per line
			$text2          = wordwrap( $text, $chars_per_line, "\n", false );
			$text2          = str_replace( '[br]', "\n", $text2 );
			$line_count     = count( explode( "\n", $text2 ) );
			$lines          = explode( "\n", $text2 );
			for ( $i = 0; $i < $line_count; $i ++ ) {
				$box = imagettfbbox( $font_size, 0, $font, $this->commas_cut( $lines[ $i ] ) );
				$w   = $box[4] - $box[6];
				if ( $w > $width ) {
					$font_size --;
					$i = 0;
				}
			}

			$text_height = $line_count * $char_size['height'];
			while ( $text_height > $height || ( $height - $text_height <= ( 2 * $pad_left ) ) ) {
				$this->font_size --;
				$font_size --;
				$char_size = $this->get_font_char_size();
				if ( ! $char_size ) {
					break;
				}
				$line_width  = ceil( $width / $char_size['width'] * 0.9 ); //count of chars per line
				$text2       = wordwrap( $text, (int) $line_width, "\n", false );
				$text2       = str_replace( '[br]', "\n", $text2 );
				$line_count  = count( explode( "\n", $text2 ) );
				$text_height = $line_count * ( $char_size['height'] * $line_spacing );
			}
			$width  = $this->width;
			$height = $this->height;

			$lines = explode( "\n", $text2 );
			if ( $valign == 'bottom' ) {
				$lines = array_reverse( $lines );
			}

			foreach ( $lines as $key => $line ) {
				$box = imagettfbbox( $font_size, 0, $font, $this->commas_cut( $line ) );
				$h   = $char_size['height'] * count( $lines ) + ( $line_spacing - 1 ) * $char_size['height'] * count( $lines );
				$w   = $box[4] - $box[6];
				$num = $line_spacing * $key;

				$x = 0;
				$y = 0;
				switch ( $align . '-' . $valign ) {
					case 'left-top':
						$x = $pad_left;
						$y = ceil( $pad_top + $char_size['height'] + ( $char_size['height'] * $num ) );
						break;
					case 'left-center':
						$x = $pad_left;
						$y = ceil( ( $height / 2 - $h / 2 ) + $char_size['height'] + ( $char_size['height'] * $num ) );
						break;
					case 'left-bottom':
						$x = $pad_left;
						$y = ceil( ( $height - $pad_top ) - ( $char_size['height'] * $num ) );
						break;
					//-------------------------
					case 'center-top':
						$x = ceil( $width / 2 - $w / 2 );
						$y = ceil( $pad_top + $char_size['height'] + ( $char_size['height'] * $num ) );
						break;
					case 'center-center':
						$x = ceil( $width / 2 - $w / 2 );
						$y = ceil( ( $height / 2 - $h / 2 ) + $char_size['height'] + ( $char_size['height'] * $num ) );
						break;
					case 'center-bottom':
						$x = ceil( $width / 2 - $w / 2 );
						$y = ceil( ( $height - $pad_top ) - ( $char_size['height'] * $num ) );
						break;
					//-------------------------
					case 'right-top':
						$x = $width - $w - $pad_left;
						$y = ceil( $pad_top + $char_size['height'] + ( $char_size['height'] * $num ) );
						break;
					case 'right-center':
						$x = $width - $w - $pad_left;
						$y = ceil( ( $height / 2 - $h / 2 ) + $char_size['height'] + ( $char_size['height'] * $num ) );
						break;
					case 'right-bottom':
						$x = $width - $w - $pad_left;
						$y = ceil( ( $height - $pad_top ) - ( $char_size['height'] * $num ) );
						break;
				}
				//shadow
				if ( ! empty( $shadow_color ) ) {
					imagettftext( $this->image, $font_size, 0, $x + 2, $y + 2, $shadow_color, $font, trim( $line ) );
				}

				//text
				imagettftext( $this->image, $font_size, 0, (int) $x, (int) $y, $font_color, $font, trim( $line ) );
				//imagerectangle($this->image, 0,$y,$width,$y, 1);
				//imagerectangle($this->image, 0,$height/2,$width,$height/2, 2);
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Save image
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function commas_cut( $text ) {
		return str_replace( ',', '', $text );
	}

	/**
	 * Save image
	 *
	 * @param string $path
	 * @param integer $quality
	 * @param string $format
	 */
	public function save( $path, $quality = 100, $format = 'jpg' ) {
		switch ( strtolower( $format ) ) {
			case 'jpg':
			case 'jpeg':
				imagejpeg( $this->image, $path, $quality );
				break;
			case 'png':
				imagepng( $this->image, $path );
				break;
		}
	}

}
