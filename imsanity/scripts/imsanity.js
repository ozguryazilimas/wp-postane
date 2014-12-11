/**
 * imsanity admin javascript functions
 */

// this must run inline so that the script is detected correctly
var imsanity_scripts = document.getElementsByTagName("script");
var imsanity_script_url = imsanity_scripts[imsanity_scripts.length-1].src;

/**
 * Begin the process of re-sizing all of the checked images
 */
function imsanity_resize_images()
{
	var images = [];
	jQuery('.imsanity_image_cb:checked').each(function(i) {
       images.push(this.value);
    });

	var target = jQuery('#resize_results'); 
	target.html('');
	target.show();
	jQuery(document).scrollTop(target.offset().top);

	// start the recursion
	imsanity_resize_next(images,0);
}

/**
 * Detect the base url for the imsanity plugin folder
 * @returns
 */
function imsanity_get_base_url()
{
	return imsanity_script_url.substring(0,imsanity_script_url.indexOf('scripts/imsanity.js'));
}


/** 
 * recursive function for resizing images
 */
function imsanity_resize_next(images,next_index)
{
	if (next_index >= images.length) return imsanity_resize_complete();
	
	jQuery.post(
		ajaxurl, // (defined by wordpress - points to admin-ajax.php)
		{action: 'imsanity_resize_image', id: images[next_index]}, 
		function(response) 
		{
			var result;
			var target = jQuery('#resize_results'); 
			
			try {
				result = JSON.parse(response);
				target.append('<div>' + (next_index+1) + ' of ' + images.length + ' &gt;&gt; ' + result['message'] +'</div>');
			}
			catch(e) {
				target.append('<div>Error parsing server response for POST ' + images[next_index] + ': '+ e.message +'.  Check the console for details.</div>');
				if (console) console.warn('Invalid JSON Response: ' + response);
		    }

			target.animate({scrollTop: target.height()}, 500);

			// recurse
			imsanity_resize_next(images,next_index+1);
		}
	);
}

/**
 * fired when all images have been resized
 */
function imsanity_resize_complete()
{
	var target = jQuery('#resize_results'); 
	target.append('<div>RESIZE COMPLETE</div>');
	target.animate({scrollTop: target.height()}, 500);
}

/** 
 * ajax post to return all images that are candidates for resizing
 * @param string the id of the html element into which results will be appended
 */
function imsanity_load_images(container_id)
{
	var container = jQuery('#'+container_id);
	container.html('<div id="imsanity_target" style="border: solid 2px #666666; padding: 10px; height: 0px; overflow: auto;" />');

	var target = jQuery('#imsanity_target');

	target.html('<div><image src="'+ imsanity_get_base_url()  +'images/ajax-loader.gif" style="margin-bottom: .25em; vertical-align:middle;" /> Examining existing attachments.  This may take a few moments...</div>');

	target.animate({height: [250,'swing']},500, function()
	{
		jQuery(document).scrollTop(container.offset().top);

		jQuery.post(
				ajaxurl, // (global defined by wordpress - points to admin-ajax.php)
				{action: 'imsanity_get_images'}, 
				function(response) 
				{
					var images = JSON.parse(response); 

					if (images.length > 0)
					{
						target.html('<div><input id="imsanity_check_all" type="checkbox" checked="checked" onclick="jQuery(\'.imsanity_image_cb\').attr(\'checked\', this.checked);" /> Select All</div>');
						
						for (var i = 0; i < images.length; i++)
						{
							target.append('<div><input class="imsanity_image_cb" name="imsanity_images" value="' + images[i].id + '" type="checkbox" checked="checked" /> POST ' + images[i].id + ': ' + images[i].file +' ('+images[i].width+' x '+images[i].height+')</div>');
						}

						container.append('<p class="submit"><button class="button-primary" onclick="imsanity_resize_images();">Resize Checked Images...</button></p>');
						container.append('<div id="resize_results" style="display: none; border: solid 2px #666666; padding: 10px; height: 250px; overflow: auto;" />');
					}
					else
					{
						target.html('<div>There are no existing attachments that require resizing.  Blam!</div>');
						
					}
				}
			);
	});
}