<?php 
/*
 * Simple modal popup template for Portfolio Slideshow Pro
 */
?> 

<?php /* Include Wordpress */
define('WP_USE_THEMES', false);
require('../../../../../wp-blog-header.php');?>

<?php $id = esc_attr($_GET['id']);
			$height = esc_attr($_GET['h']);
			$slideheight = esc_attr($_GET['sh']);
			$width = esc_attr($_GET['w']);
			$carousel = esc_attr($_GET['carousel']);
			$carouselsize = esc_attr($_GET['carouselsize']);
			$showcaps = esc_attr($_GET['caps']);
			$showtitles = esc_attr($_GET['titles']);
			$showdesc = esc_attr($_GET['desc']);
			$centered = esc_attr($_GET['centered']);
			$shownav = esc_attr($_GET['nav']);
			$navstyle = esc_attr($_GET['navstyle']);
			$navpos = esc_attr($_GET['navpos']);
			$autoplay = esc_attr($_GET['autoplay']);
?>

<?php if ( ! $id ) die; ?>

<?php /* Set up some defaults for a few things if they weren't passed in the URL */
	global $ps_options;
	if ( ! $height ) $height = 450;
	if ( ! $width ) $width = 999;
	if ( ! $slideheight ) $slideheight = 460;
	if ( ! $carousel ) $carousel = "true";
	if ( ! $centered ) $centered = $ps_options['centered'];
	if ( ! $carouselsize ) $carouselsize = $ps_options['carouselsize'];
	if ( ! $pagerpos ) $pagerpos = $ps_options['pagerpos'];
	if ( ! $showtitles ) $showtitles = $ps_options['showtitles'];
	if ( ! $showcaps ) $showcaps = $ps_options['showcaps'];
	if ( ! $showdesc ) $showdesc = $ps_options['showdesc'];
	if ( ! $navstyle ) $navstyle = $ps_options['navstyle'];
	if ( ! $navpos ) $navpos = $ps_options['navpos'];
	if ( ! $autoplay ) $autoplay = $ps_options['autoplay'];

?>

<!doctype html public>
<!--[if lt IE 7]> <html lang="en-us" class="no-js ie6"> <![endif]-->
<!--[if IE 7]>    <html lang="en-us" class="no-js ie7"> <![endif]-->
<!--[if IE 8]>    <html lang="en-us" class="no-js ie8"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en-us" class="no-js"> <!--<![endif]-->
<head>

 <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
  
  <title dir="ltr"><?php echo get_the_title($id);?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">  

  <link rel="stylesheet" media="screen" href="<?php echo PORTFOLIO_SLIDESHOW_PRO_URL;?>/css/portfolio-slideshow.css" >
	<link rel="stylesheet" media="screen" href="<?php echo PORTFOLIO_SLIDESHOW_PRO_URL;?>/js/fancybox/jquery.fancybox-1.3.4.min.css" >
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>

 <?php portfolio_slideshow_head();?>
</head>
<body class="popup-viewer">

	<section class="main"> 
		
	<?php if ( $carousel == "true" ) { $pagerpos="bottom"; } else { $pagerpos="disabled"; }
		
	echo do_shortcode( "[portfolio_slideshow id=$id click=advance width=$width height=$height slideheight=$slideheight carousel=$carousel carouselsize=$carouselsize pagerpos=$pagerpos showtitles=$showtitles showcaps=$showcaps showdesc=$showdesc centered=$centered navstyle=$navstyle navpos=$navpos autoplay=$autoplay]");
	
	?>

	</section>
	
	<script src="<?php echo PORTFOLIO_SLIDESHOW_PRO_URL;?>/js/jquery.cycle.all.min.js"></script>
	<script src="<?php echo PORTFOLIO_SLIDESHOW_PRO_URL;?>/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<script src="<?php echo PORTFOLIO_SLIDESHOW_PRO_URL;?>/js/scrollable.min.js"></script>
	<script src="<?php echo PORTFOLIO_SLIDESHOW_PRO_URL;?>/js/portfolio-slideshow.js"></script>
	<?php portfolio_slideshow_foot();?>
</body>
</html>