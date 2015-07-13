/*
jQuery(document).ready(function() {
	jQuery('#upload_refresh_image_button').click(function() {
		formfield = jQuery('#captcha_refresh_image').attr('name');
		tb_show('', 'media-upload.php?type=image&TB_iframe=true');
		return false;
	});
	
	window.send_to_editor = function(html) {
		imgurl = jQuery('img',html).attr('src');
		jQuery('#captcha_refresh_image').val(imgurl);
		tb_remove();
	}
	jQuery('.color_picker_callback').wpColorPicker();
});
*/

jQuery(document).ready(function($){
	
	jQuery('._color_picker_callback').wpColorPicker();
	
    var custom_uploader;

    $('#upload_refresh_image_button').click(function(e) {

        e.preventDefault();

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose File',
            button: {
                text: 'Choose File'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#captcha_refresh_image').val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();

    });

});