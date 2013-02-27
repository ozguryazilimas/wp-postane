<?php
// this file contains the contents of the popup window
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Insert Slideshow</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.js"></script>
<script language="javascript" type="text/javascript" src="includes/tiny_mce_popup.js"></script>
<style type="text/css" src="includes/wp_theme/dialog.css"></style>
<link rel="stylesheet" href="includes/css/ps-tinymce.css" />

<script type="text/javascript">
 
var psDialog = {
	local_ed : 'ed',
	init : function(ed) {
		psDialog.local_ed = ed;
		tinyMCEPopup.resizeToInnerSize();
	},
	insert : function insertps(ed) {
	 
		// Try and remove existing style / blockquote
		tinyMCEPopup.execCommand('mceRemoveNode', false, null);
		 
		// set up variables to contain our input values
		var size = jQuery('#ps-dialog select#ps-size').val();	 
		var customwidth = jQuery('#ps-dialog input#ps-customwidth').val();
		var customheight = jQuery('#ps-dialog input#ps-customheight').val();
		var navstyle = jQuery('#ps-dialog select#ps-navstyle').val();
		var navpos = jQuery('#ps-dialog select#ps-navpos').val();
		var pagerstyle = jQuery('#ps-dialog select#ps-pagerstyle').val();	
		var pagerpos = jQuery('#ps-dialog select#ps-pagerpos').val();	
		
		if ( $('#ps-dialog input#ps-autoplay').is(":checked") ) {
			var autoplay = true;		 	 
		 } else {
		 	var autoplay = false;
		 }

		 if ( $('#ps-dialog input#ps-centered').is(":checked") ) {
			var centered = true;		 	 
		 } else {
		 	var centered = false;
		 }

		 if ( $('#ps-dialog input#ps-carousel').is(":checked") ) {
			var carousel = true;		 	 
		 } else {
		 	var carousel = false;
		 }

		 if ( $('#ps-dialog input#ps-random').is(":checked") ) {
			var random = true;		 	 
		 } else {
		 	var random = false;
		 }

		var output = '';
		
		// setup the output of our shortcode
		output = '[portfolio_slideshow ';
			output += 'size=' + size + ' ';
			if ( customwidth != 0 ) {
				output += 'width=' + customwidth + ' ';
			}	
			if ( customheight != 0 ) {
				output += 'height=' + customheight + ' ';
			}	
			output += 'autoplay=' + autoplay + ' ';
			output += 'random=' + random + ' ';
			output += 'centered=' + centered + ' ';
			output += 'carousel=' + carousel + ' ';
			output += 'navstyle=' + navstyle + ' ';
			output += 'navpos=' + navpos + ' ';
			output += 'pagerstyle=' + pagerstyle + ' ';
			output += 'pagerpos=' + pagerpos;
			output += psDialog.local_ed.selection.getContent() + ']';

		tinyMCEPopup.execCommand('mceReplaceContent', false, output);
		 
		// Return
		tinyMCEPopup.close();
	}
};
tinyMCEPopup.onInit.add(psDialog.init, psDialog);
 
$(function() {
		customSizeVal = $('#ps-dialog select#ps-size').val();
	if ( customSizeVal != "custom" ) { $('.custom-size').addClass("hidden");}
	
	$('#ps-dialog select#ps-size').change(function() {
		customSizeVal = $('#ps-dialog select#ps-size').val();
  		if ( customSizeVal != "custom" ) { $('.custom-size').addClass("hidden"); } else { $('.custom-size').removeClass("hidden"); }
  		
	});
});

</script>

</head>
<body>
	<div id="ps-dialog">
		<form action="/" method="get" accept-charset="utf-8">
			<div>
				<label for="ps-size">Size</label>
				<select name="ps-size" id="ps-size" size="1">
					<option value="thumbnail">thumb</option>
					<option value="medium" selected="selected">medium</option>
					<option value="large">large</option>
					<option value="custom">custom</option>
				</select>
			</div>
			<div class="custom-size">
				<label for="ps-customwidth">Width (px)</label>
				<input id='ps-customwidth' name='ps-customwidth' type='text' size='5' value='' />
			</div>
			<div class="custom-size">
				<label for="ps-customheight">Height (px)</label>
				<input id='ps-customheight' name='ps-customheight' type='text' size='5' value='' />
			</div>
			<div>
				<label for="ps-navstyle">Nav style</label>
				<select name="ps-navstyle" id="ps-navstyle" size="1">
					<option value="text">text</option>
					<option value="graphical" selected="selected">graphical</option>
				</select>
			</div>
			<div>
				<label for="ps-navpos">Nav position</label>
				<select name="ps-navpos" id="ps-navpos" size="1">
					<option value="top" selected="selected">top</option>
					<option value="bottom">bottom</option>
					<option value="disabled">disabled</option>
				</select>
			</div>
			<div>
				<label for="ps-pagerstyle">Pager style</label>
				<select name="ps-pagerstyle" id="ps-pagerstyle" size="1">
					<option value="thumbs" selected="selected">thumbs</option>
					<option value="numbers">numbers</option>
					<option value="bullets">bullets</option>
				</select>
			</div>
			<div>
				<label for="ps-pagerpos">Pager position</label>
				<select name="ps-pagerpos" id="ps-pagerpos" size="1">
					<option value="top">top</option>
					<option value="bottom" selected="selected">bottom</option>
					<option value="disabled">disabled</option>
				</select>
			</div>
			<div>
				<label for="ps-centered">Center slideshow</label>
				<input type="checkbox" id="ps-centered" name="ps-centered" value="1" />
			</div>
			<div>
				<label for="ps-carousel">Enable carousel</label>
				<input type="checkbox" id="ps-carousel" name="ps-carousel" value="1" />
			</div>

			<div>
				<label for="ps-autoplay">Autoplay</label>
				<input type="checkbox" id="ps-autoplay" name="ps-autoplay" value="1" />
			</div>

			<div>
				<label for="ps-random">Random</label>
				<input type="checkbox" id="ps-random" name="ps-random" value="1" />
			</div>
			<div>	
				<a href="javascript:psDialog.insert(psDialog.local_ed)" id="insert" style="display: block; line-height: 24px;">Insert</a>
			</div>
		</form>
	</div>
</body>
</html>