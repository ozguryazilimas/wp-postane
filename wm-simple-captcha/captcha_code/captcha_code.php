<?php 
session_start();
header('Content-Type: image/jpeg');// defining the image type to be shown in browser widow

//Settings: You can customize the captcha here
if(isset($_SESSION['wmsc_options'])){
	$wmsc_options 				= $_SESSION['wmsc_options'];
	$image_width 				= $wmsc_options['captcha_image_width'];
	$image_height 				= $wmsc_options['captcha_image_height'];
	$characters_on_image 		= $wmsc_options['captcha_image_characters'];
	$captcha_enable_space 		= isset_value('captcha_enable_space',$wmsc_options,0);
	$captcha_image_font_adj		= isset_value('captcha_image_font_adj',$wmsc_options,0.6);
	$font 						= isset($wmsc_options['captcha_image_font']) ? $wmsc_options['captcha_image_font'] :'arial.ttf';
	$possible_letters			= $wmsc_options['captcha_possible_letters'];
	$random_dots 				= $wmsc_options['captcha_random_dots'];
	$random_lines 				= $wmsc_options['captcha_random_lines'];
	$captcha_text_color			= "0x".$wmsc_options['captcha_text_color'];
	$captcha_dots_color			= "0x".$wmsc_options['captcha_dots_color'];
	$captcha_line_color 		= "0x".$wmsc_options['captcha_line_color'];
	$captcha_background_color	= "0x".$wmsc_options['captcha_background_color'];
	$font_path					= isset($wmsc_options['font_path']) ? $wmsc_options['font_path'] : "../fonts/";
	
	
}else{
	$image_width 				= 120;
	$image_height 				= 25;
	$characters_on_image 		= 5;
	$font 						= 'arial.ttf';
	$possible_letters 			= '23456789bcdfghjkmnpqrstvwxyz';//avoid confusing characters (l 1 and i for example)
	$random_dots 				= 0;
	$random_lines 				= 0;
	$captcha_text_color			= "0x142864";
	$captcha_dots_color 		= "0x142864";
	$captcha_line_color 		= "0x142864";
	$captcha_background_color 	= "0xFFFFFF";
	$captcha_enable_space		= 0;
	$captcha_image_font_adj		= "0.6";
	$font_path					= "../fonts/";
}

$code 			= '';
$code_string 	= '';
$space 			= '';
$font 			= $font_path.$font;

if(!file_exists($font)) $font = "../fonts/arial.ttf";

if($captcha_enable_space == 1) $space = ' ';

$i = 0;
while ($i < $characters_on_image) {
	$c = substr($possible_letters, mt_rand(0, strlen($possible_letters)-1), 1);
	$code_string .= $c.$space;
	$code .= $c;
	$i++;
}

//$code_string = "Refresh";

$font_size 		= $image_height * $captcha_image_font_adj;
$image 			= @imagecreate($image_width, $image_height);


/* setting the background, text and dots colours here */
$arr_background_color = hexrgb($captcha_background_color);
$background_color = imagecolorallocate($image, $arr_background_color['red'], $arr_background_color['green'], $arr_background_color['blue']);

$arr_text_color = hexrgb($captcha_text_color);
$image_text_color = imagecolorallocate($image, $arr_text_color['red'], $arr_text_color['green'], $arr_text_color['blue']);

$arr_dots_color = hexrgb($captcha_dots_color);
$image_dots_color = imagecolorallocate($image, $arr_dots_color['red'], $arr_dots_color['green'], $arr_dots_color['blue']);

$arr_line_color = hexrgb($captcha_line_color);
$image_line_color = imagecolorallocate($image, $arr_line_color['red'], $arr_line_color['green'], $arr_line_color['blue']);


/* generating lines randomly in background of image */
for( $i=0; $i<$random_lines; $i++ ) {
	imageline($image, mt_rand(0,$image_width), mt_rand(0,$image_height),mt_rand(0,$image_width), mt_rand(0,$image_height), $image_line_color);
}

/* generating the dots randomly in background */
for( $i=0; $i<$random_dots; $i++ ) {
	imagefilledellipse($image, mt_rand(0,$image_width),	mt_rand(0,$image_height), 2, 3, $image_dots_color);
}


//$code_string = "WM Simple Captcha";

/* create a text box and add 6 letters code in it */
$textbox = imagettfbbox($font_size, 0, $font, $code_string); 
$x = ($image_width - $textbox[4])/2;
$y = ($image_height - $textbox[5])/2;
imagettftext($image, $font_size, 0, $x, $y, $image_text_color, $font , $code_string);



/* Show captcha image in the page html page */
imagejpeg($image);//showing the image
imagedestroy($image);//destroying the image instance
$_SESSION['6_letters_code'] = $code;

function isset_value ($name,$data = NULL, $default = ''){
	if(isset($data[$name]))	return $data[$name];
	return $default;
}

function hexrgb ($hexstr){
  $int = hexdec($hexstr);
  return array("red" => 0xFF & ($int >> 0x10),"green" => 0xFF & ($int >> 0x8),"blue" => 0xFF & $int);
}
?>