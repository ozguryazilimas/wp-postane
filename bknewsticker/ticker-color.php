<style type="text/css" media="screen">
#bknewsticker,#allticker{
background: <?php $color_background = get_option('ticker_background_posts'); echo $color_background;?>;
}
#bknews-title{
	border-right: 6px solid <?php $color_background = get_option('ticker_background_posts'); echo $color_background;?>;

}
.tickerText{
	color: <?php $color_exerpt = get_option('ticker_color_excerpt'); echo $color_exerpt;?>;
	font-size: <?php $size_exerpt_text = get_option('bke_textall_size'); echo $size_exerpt_text;?>px;
}

.tickerDate{
    color: <?php $color_title = get_option('ticker_color_title'); echo $color_title;?>;
    font-size: <?php $size_exerpt_text = get_option('bke_textall_size'); echo $size_exerpt_text;?>px;
}
.tickerDate{
	background: <?php $background_title = get_option('ticker_background_title'); echo $background_title;?>;
}
#bknews-title h3{
	 color: <?php $color_title = get_option('ticker_color_title'); echo $color_title;?>;
	 border-bottom: 30px solid <?php $background_title = get_option('ticker_background_title'); echo $background_title;?>;
	 font-size: <?php $size_exerpt_text = get_option('bke_textall_size'); echo $size_exerpt_text;?>px;
}
#allticker{
	border-bottom: <?php $size_border = get_option('border_size_ticker'); echo $size_border;?>px solid <?php $color_border = get_option('ticker_color_border'); echo $color_border;?>!important;
}
.tickerLink a, .tickermore a{
	color: <?php $color_exerpt = get_option('ticker_color_link'); echo $color_exerpt;?>;
	font-size: <?php $size_exerpt_text = get_option('bke_textall_size'); echo $size_exerpt_text;?>px;
}
</style>	
