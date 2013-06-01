<?php
/*
 * Bismillahirrahmanirrahim
 * @jQuery Popup
 * @since 1.3.1
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Lütfen Olmuyor Böyle'); }
include_once 'admin.options.php';
milatAyarGuncelle();
?>
<link rel="stylesheet" href="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/css/colorpicker.css" type="text/css" />
<link rel="stylesheet" href="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/css/stil.css" type="text/css" />
<link rel="stylesheet" href="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/style.css" type="text/css" />
<script src="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/js/colorpicker.js"></script>
<script src="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/js/eye.js"></script>
<script src="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/js/layout.js"></script>
<script src="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/js/jquery.js"></script>
<script src="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/js/milat.admin.js"></script>   
<div class="wrap">
	<table width="100%">
		<tr>
			<td width="80%"><div id="icon-options-general" class="icon32"></div><h2><?php _e("jQuery Popup Settings",MILAT_MILAT) ?></h2></td>
			<td width="17%">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="NPF49KTZJX5VY">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/tr_TR/i/scr/pixel.gif" width="1" height="1">
				</form>
			</td>
		</tr>
		<tr>
			<td colspan="2"><?php milatMesajSonuc();?></td>
		</tr>
	</table>
    <br class="clear"/>
    <form action="<?php echo $_SERVER['REQUEST_URI'];?>" id="milat" method="POST">
    <div id="milat_menu" class="milat_menu">
        <div class="milat_tabs_container">
            <div class="milat_slide_container">
                <ul class="milat_tabs">
                    <li><a href="milat_icerik_1" rel="tab_1" class="milat_tab milat_firmilat_tab <?php aktif('milat_tur','html','milat_tab_active'); ?>"><?php _e("Html & Text",MILAT_MILAT) ?></a></li>
                    <li><a href="milat_icerik_2" rel="tab_2" class="milat_tab <?php aktif('milat_tur','resim','milat_tab_active'); ?>"><?php _e("Image",MILAT_MILAT) ?></a></li>
                    <li><a href="milat_icerik_3" rel="tab_3" class="milat_tab <?php aktif('milat_tur','video','milat_tab_active'); ?>"><?php _e("Video",MILAT_MILAT) ?></a></li>
                </ul>
            </div> <!-- /.milat_slide_container -->
        </div> <!-- /.milat_tabs_container -->
        <div class="milat_view_container">
            <div class="milat_view">
                <div id="milat_icerik_1" class="milat_tab_view <?php aktif('milat_tur','html','milat_firmilat_tab_view'); ?>">
                    <div class="text">
					<div style="display:none;">
						<input type="radio" name='milat_tur' value='html' id='html' <?php aktif('milat_tur','html','checked'); ?>  />
						<input type="radio" name='milat_tur' value='resim' id='resim' />
						<input type="radio" name='milat_tur' value='video' id='video' />
					</div>
					<table class="widefat fixed" cellspacing="0" />
						<tbody>
						<tr>
							<th scope="col" width="270px">
								<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('basliks');">
						   <?php  _e("Title",MILAT_MILAT) ?></a>
							<div style="text-align:left; display:none" id="basliks"><?php _e("Optional",MILAT_MILAT) ?></div>
							</th>
							<td>
							<label for="milat_baslik"><input type="text" id="milat_baslik" name="milat_baslik" size="60" value="<?php _e(get_option('milat_baslik'));?>" class="regular-text" /></label>
							</td>
						  </tr>
						  <tr valign="top">
							<th scope="col" width="270px">
								<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('yazi');"><?php _e("Text",MILAT_MILAT) ?></a>
								<div style="text-align:left; display:none" id="yazi"><?php _e("Edit the visual or HTML. (Required)",MILAT_MILAT) ?></div>
							</th>
							<td>
							 <?php milatTinyMCE();?>
							</td>
					   </tr>
					   </tbody>
					   </table>
                    </div>
                </div>

                <div id="milat_icerik_2" class="milat_tab_view <?php aktif('milat_tur','resim','milat_firmilat_tab_view'); ?>">
                    <div class="text">
					<div style="display:none;">
						<input type="radio" name='milat_tur' value='html' id='html' />
						<input type="radio" name='milat_tur' value='resim' id='resim' <?php aktif('milat_tur','resim','checked'); ?> />
						<input type="radio" name='milat_tur' value='video' id='video' />
					</div>
					 <table class="widefat fixed" cellspacing="0" />
						<tbody>
							<tr valign="top">
								<th scope="row" width="270px">
								<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('resim_up');"><?php _e("Upload Image",MILAT_MILAT) ?></a>
								<div style="text-align:left; display:none" id="resim_up"><?php _e("To any size file you have opened the homepage",MILAT_MILAT) ?></div>
								</th>
								<td>
									<label for="milat_resim">
										<input id="milat_resim" type="text" size="76" name="milat_resim" value="<?php _e(get_option('milat_resim'));?>" />
										<input id="milat_resim_button" type="button" class="button"  value="Upload Image" />
										<br /><span><?php _e("Enter an URL or upload an image for the banner.",MILAT_MILAT) ?></span>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" width="270px">
								<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('resim_lin');"><?php _e("Image go to url",MILAT_MILAT) ?></a>
								<div style="text-align:left; display:none" id="resim_lin"><?php _e("Image clicked goes to the address you want to *Optional",MILAT_MILAT) ?></div>
								</th>
								<td>
									<label for="milat_resim_link">
										<input id="milat_resim_link" type="text" size="76" name="milat_resim_link" value="<?php _e(get_option('milat_resim_link'));?>" />
									</label>
								</td>
							</tr>
					   </tbody>
					   </table>

					</div>
                </div>

                <div id="milat_icerik_3" class="milat_tab_view <?php aktif('milat_tur','video','milat_firmilat_tab_view'); ?>">
                    <div class="text">
                     <div style="display:none;">
						<input type="radio" name='milat_tur' value='html' id='html' />
						<input type="radio" name='milat_tur' value='resim' id='resim' />
						<input type="radio" name='milat_tur' value='video' id='video' <?php aktif('milat_tur','video','checked'); ?> />
					</div>
                        <div id="milat_yatay" class="milat_yatay">

        <div class="milat_yatay_tabs_container">

            <div class="milat_yatay_slide_container">

                <ul class="milat_yatay_tabs">
                    <li><a href="#milat_ic_duzen_1" rel="v_tab_1" class="milat_yatay_tab <?php aktif('milat_tur_icerik','youtube','milat_yatay_tab_active'); ?>">Youtube<span>Youtube Video</span></a></li>
                    <li><a href="#milat_ic_duzen_2" rel="v_tab_2" class="milat_yatay_tab <?php aktif('milat_tur_icerik','daily','milat_yatay_tab_active'); ?>" >Dailymotion<span>Dailymotion Video</span></a></li>
                    <li><a href="#milat_ic_duzen_3" rel="v_tab_3" class="milat_yatay_tab <?php aktif('milat_tur_icerik','vimeo','milat_yatay_tab_active'); ?>">Vimeo<span>Vimeo Video</span></a></li>
                    <li><a href="#milat_ic_duzen_4" rel="v_tab_4" class="milat_yatay_tab <?php aktif('milat_tur_icerik','swf','milat_yatay_tab_active'); ?>">Swf<span>Swf Player</span></a></li>
                </ul>


            </div> <!-- /.st_slide_container -->

        </div> <!-- /.st_tabs_container -->

        <div class="milat_yatay_view_container">

            <div class="milat_yatay_view">

                <div id="milat_ic_duzen_1" class="milat_yatay_tab_view <?php aktif('milat_tur_icerik','youtube','milat_yatay_first_tab_view'); ?>">
                    <h2><?php _e("YOUTUBE VIDEOS OPENS A POPUP",MILAT_MILAT) ?></h2>

                    <div class="text">
					<div style="display:none;">
						<input type="radio" name='milat_tur_icerik' value='youtube' id='youtube' <?php aktif('milat_tur_icerik','youtube','checked'); ?> />
						<input type="radio" name='milat_tur_icerik' value='daily' id='daily' />
						<input type="radio" name='milat_tur_icerik' value='vimeo' id='vimeo' />
						<input type="radio" name='milat_tur_icerik' value='swf' id='swf' />
					</div>
                         <table class="widefat fixed" cellspacing="0" />
							<tbody>
							   <tr valign="top">
									<th scope="row" width="270px">
									<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('vid_you');">Youtube</a>
									<div style="text-align:left; display:none" id="vid_you"><?php _e("Please only video id",MILAT_MILAT) ?></div>
									</th>
									<td>
										<label for="milat_youtube">
											<input id="milat_youtube" type="text" size="26" name="milat_youtube" value="<?php _e(get_option('milat_youtube'));?>" />
										</label>
									<br /><span class="normal">Demo:</span><span class="demo">http://www.youtube.com/watch?v=</span><span class="kirmizi"><b>3hIB8KKNo68</b></span>
									</td>
								</tr>
						   </tbody>
					   </table>
					</div>
                </div>

                <div id="milat_ic_duzen_2" class="milat_yatay_tab_view <?php aktif('milat_tur_icerik','daily','milat_yatay_first_tab_view'); ?>">
                    <h2><?php _e("DAILYMOTION VIDEOS OPENS A POPUP",MILAT_MILAT) ?></h2>


                    <div class="text">
					<div style="display:none;">
						<input type="radio" name='milat_tur_icerik' value='youtube' id='youtube' />
						<input type="radio" name='milat_tur_icerik' value='daily' id='daily' <?php aktif('milat_tur_icerik','daily','checked'); ?> />
						<input type="radio" name='milat_tur_icerik' value='vimeo' id='vimeo' />
						<input type="radio" name='milat_tur_icerik' value='swf' id='swf' />
					</div>
                        <table class="widefat fixed" cellspacing="0" />
							<tbody>
							   <tr valign="top">
									<th scope="row" width="270px">
									<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('vid_dai');">Dailymotion</a>
									<div style="text-align:left; display:none" id="vid_dai"><?php _e("Please only video id",MILAT_MILAT) ?></div>
									</th>
									<td>
										<label for="milat_dailymotion">
											<input id="milat_dailymotion" type="text" size="26" name="milat_dailymotion" value="<?php _e(get_option('milat_dailymotion'));?>" />
										</label>
									<br /><span class="normal">Demo:</span><span class="demo">http://www.dailymotion.com/video/<span class="kirmizi"><b>xlfqlg</b></span>_melih-kibar-sucu-cocuk_music</span>
									</td>
								</tr>
						   </tbody>
					   </table>

                    </div>
                </div>

                <div id="milat_ic_duzen_3" class="milat_yatay_tab_view <?php aktif('milat_tur_icerik','vimeo','milat_yatay_first_tab_view'); ?>">

                    <h2><?php _e("VIMEO VIDEOS OPENS A POPUP",MILAT_MILAT) ?></h2>

                    <div class="text">
					<div style="display:none;">
						<input type="radio" name='milat_tur_icerik' value='youtube' id='youtube' />
						<input type="radio" name='milat_tur_icerik' value='daily' id='daily' />
						<input type="radio" name='milat_tur_icerik' value='vimeo' id='vimeo' <?php aktif('milat_tur_icerik','vimeo','checked'); ?> />
						<input type="radio" name='milat_tur_icerik' value='swf' id='swf' />
					</div>
                         <table class="widefat fixed" cellspacing="0" />
							<tbody>
							   <tr valign="top">
									<th scope="row" width="270px">
									<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('vid_vim');">Vimeo</a>
									<div style="text-align:left; display:none" id="vid_vim"><?php _e("Please only video id",MILAT_MILAT) ?></div>
									</th>
									<td>
										<label for='vimeo'>
											<input id="milat_vimeo" type="text" size="26" name="milat_vimeo" value="<?php _e(get_option('milat_vimeo'));?>" />
										</label>
										<br /><span class="normal">Demo:</span><span class="demo">http://vimeo.com/</span><span class="kirmizi"><b>25592064</b></span>
									</td>
								</tr>
						   </tbody>
					   </table>
                    </div>
                </div>

                <div id="milat_ic_duzen_4" class="milat_yatay_tab_view <?php aktif('milat_tur_icerik','swf','milat_yatay_first_tab_view'); ?>">
                    <h2><?php _e("SWF FILES IN THE PUBLICATION EMBED CODE",MILAT_MILAT) ?></h2>

                    <div class="text">
					<div style="display:none;">
						<input type="radio" name='milat_tur_icerik' value='youtube' id='youtube' />
						<input type="radio" name='milat_tur_icerik' value='daily' id='daily' />
						<input type="radio" name='milat_tur_icerik' value='vimeo' id='vimeo' />
						<input type="radio" name='milat_tur_icerik' value='swf' id='swf' <?php aktif('milat_tur_icerik','swf','checked'); ?> />
					</div>
                         <table class="widefat fixed" cellspacing="0" />
							<tbody>
							   <tr valign="top">
									<th scope="row" width="270px">
									<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('vid_swf');">Swf</a>
									<div style="text-align:left; display:none" id="vid_swf"><?php _e("Please .swf file url",MILAT_MILAT) ?></div>
									</th>
									<td>
										<label for="milat_swf">
											<input id="milat_swf" type="text" size="76" name="milat_swf" value="<?php _e(get_option('milat_swf'));?>" />
										</label>
									<br /><span class="normal">Demo:</span><span class="kirmizi"><b>http://www.milat.org/indir/vbkurulum.swf</b></span>
									</td>
								</tr>
						   </tbody>
					   </table>

                    </div>
                </div>





            </div> <!-- /.st_view -->

        </div> <!-- /.st_view_container -->


    </div> <!-- /#st_vertical -->
                    </div>

                </div>
                </div>
            </div> <!-- /.milat_view -->
        </div> <!-- /.milat_view_container -->


	<table class="widefat fixed" cellspacing="0" />
		<thead>
			<tr>
				<th scope="col" width="270px"></th>
				<th scope="col"><?php _e("General Settings",MILAT_MILAT) ?></th>
			</tr>
		</thead>
		<tbody>
		  <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('anasayfa');"><?php _e("Homepage View ?",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="anasayfa"><?php _e("If you select No Popup will not open the Homepage",MILAT_MILAT) ?></div>
			</th>
			<td>
			  <input type="radio" name="milat_anasayfa" value="yes" <?php echo _r(get_option('milat_anasayfa'), 'yes');?> /><b><?php _e("Yes",MILAT_MILAT) ?>
			  </b><br />
			  <input type="radio" name="milat_anasayfa" value="no" <?php echo _r(get_option('milat_anasayfa'), 'no');?> /><b><?php _e("No",MILAT_MILAT) ?>
				</b>
			</td>
		  </tr>
		  <tr>
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('sayfalar');"><?php _e("Get all the pages to Active ?",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="sayfalar"><?php _e("If they yes, Writings, and other Pages Display",MILAT_MILAT) ?></div>
			</th>
			<td>
			  <input type="radio" name="milat_heryer" value="yes" <?php echo _r(get_option('milat_heryer'), 'yes');?> /><b><?php _e("Yes",MILAT_MILAT) ?>
			  </b> <br />
			  <input type="radio" name="milat_heryer" value="no" <?php echo _r(get_option('milat_heryer'), 'no');?> /><b><?php _e("No",MILAT_MILAT) ?>
				</b>
			</td>
		  </tr>
		  <tr>
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('kapat');"><?php _e("Button Style",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="kapat"><?php _e("Click on whichever you want to get the shutdown button",MILAT_MILAT) ?></div>
			</th>
			<td>
				<label><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/button_black_close.png" /> <div style="display:none;"><input type="radio" name="milat_buton_stil" id="black" value="black" <?php echo _r(get_option('milat_buton_stil'), 'black');?> /></div></label>
				<label><div style="display:inline;margin-left:20px;"><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/button_grey_close.png" /></div> <div style="display:none;"><input type="radio" name="milat_buton_stil" id="grey" value="grey" <?php echo _r(get_option('milat_buton_stil'), 'grey');?> /></div></label>
				<label><div style="display:inline;margin-left:20px;"><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/button_white_close.png" /></div> <div style="display:none;"><input type="radio" name="milat_buton_stil" id="white" value="white" <?php echo _r(get_option('milat_buton_stil'), 'white');?> /></div></label>
				<label><div style="display:inline;margin-left:20px;"><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/button_red_close.png" /></div> <div style="display:none;"><input type="radio" name="milat_buton_stil" id="red" value="red" <?php echo _r(get_option('milat_buton_stil'), 'red');?> /></div></label>
				<label><div style="display:inline;margin-left:20px;"><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/button_blue_close.png" /></div> <div style="display:none;"><input type="radio" name="milat_buton_stil" id="blue" value="blue" <?php echo _r(get_option('milat_buton_stil'), 'blue');?> /></div></label>
				<label><div style="display:inline;margin-left:20px;"><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/css/button_brown_close.png" /></div> <div style="display:none;"><input type="radio" name="milat_buton_stil" id="brown" value="brown" <?php echo _r(get_option('milat_buton_stil'), 'brown');?> /></div></label>

			</td>
		  </tr>
		 <!-- <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('stil');"><?php _e("Close Type",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="stil"><?php _e("aciklahuseyin.",MILAT_MILAT) ?></div>
			</th>
			<td>
				<label><input type="checkbox" name="esckapat" id="esckapat" value="aktif" <?php echo _r(get_option('milat_esckapat'), 'aktif');?>  /><b> <?php _e("esc close",MILAT_MILAT) ?> </b></label><br />
                <label><input type="checkbox" name="arkaplan" id="arkaplan" value="aktif" <?php echo _r(get_option('milat_arkakapat'), 'aktif');?> /><b>  <?php _e("arka plan",MILAT_MILAT) ?></b></label>
			</td>
		  </tr>-->
		  <tr>
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('cerez');"><?php _e("Cookie Storage Time",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="cerez"><?php _e("How many hours open to visitors Popup.",MILAT_MILAT) ?></div>
			</th>
			<td>
			   <input type="text" name="milat_cookie_saat" size="3" value="<?php echo get_option('milat_cookie_saat');?>" />
				<b><?php _e("Hours of",MILAT_MILAT) ?></b>  <a style="cursor:pointer;" title="<?php _e("Always want to open (0)",MILAT_MILAT) ?>"><img src="<?php _e(MILAT_PLUGIN_URL) ?>lib/admin/css/image/ques.png" /></a>

			</td>
		  </tr>
		  <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('genislik');"><?php _e("Popup Weight",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="genislik"><?php _e("Popup Weight 200px to 900px.",MILAT_MILAT) ?></div>
			</th>
			<td>
				<input type="range" name="milat_genislik" id="milat_genislik" size="3" min="200" max="900" value="<?php echo get_option('milat_genislik');?>" />
				<b>px</b>
			</td>
		  </tr>
		  <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('yukseklik');"><?php _e("Popup Height",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="yukseklik"><?php _e("Popup Height 100px to 500px",MILAT_MILAT) ?></div>
			</th>
			<td>
				<input type="range" name="milat_yukseklik" id="milat_yukseklik" size="3" min="100" max="500" value="<?php echo get_option('milat_yukseklik');?>" />
				<b>px</b>
			</td>
		  </tr>

		  <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('arkaplan');"><?php _e("Popup background color",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="arkaplan"><?php _e("You can change the background color of popup",MILAT_MILAT) ?></div>
			</th>
			<td>
			 <input type="text" name="milat_arkaplan" id="colorpickerField1" size="5" value="<?php echo get_option('milat_arkaplan');?>" />
			</td>
		  </tr>
		  <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('border');"><?php _e("Border Color",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="border"><?php _e("Border Color optional choose",MILAT_MILAT) ?></div>
			</th>
			<td>
			 <input type="text" name="milat_border" id="colorpickerField2" size="5" value="<?php echo get_option('milat_border');?>" />
			</td>
		  </tr>
		  <tr valign="top">
			<th>
				<a style="cursor:pointer;" title="<?php _e("Click for Help!",MILAT_MILAT) ?>" onclick="toggleVisibility('border_gen');"><?php _e("Border Width",MILAT_MILAT) ?></a>
				<div style="text-align:left; display:none" id="border_gen"><?php _e("Border width 1px to 20px",MILAT_MILAT) ?></div>
			</th>
			<td>
				<input type="range" name="milat_border_genislik" id="milat_border_genislik" size="3" min="1" max="20" value="<?php echo get_option('milat_border_genislik');?>" />
				<b>px</b>
			</td>
		  </tr>
		  <th></th>
		  <td>
		  <input type="submit" class="button-primary" value="<?php _e("Save",MILAT_MILAT) ?>" />    <input type="button" rel="preview" class="button-primary" value="<?php _e("Live Preview",MILAT_MILAT) ?>" />
		  </td>

		  <input type="hidden" name="gonder" value="milat_ayarlari_guncelle" />

	    </tbody>
   	  </table>
    </form>
	<div id="kutu">
		<div style="display:none;" id="pencere" style="" class="window">
        	<a href="#" class="close"></a>
		</div>
		<div id="karartma"></div>
	</div>
  <input type="hidden" id="adres" value="<?php _e(MILAT_PLUGIN_URL) ?>" />
</div>
	<script>
	$(":range").rangeinput();
	</script>