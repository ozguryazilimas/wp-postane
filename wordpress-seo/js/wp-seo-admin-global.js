function wpseo_setIgnore( option, hide, nonce ) {
	jQuery.post(ajaxurl, {
			action: 'wpseo_set_ignore',
			option: option,
			_wpnonce: nonce
		}, function(data) { 
			if (data) {
				jQuery('#'+hide).hide();
				jQuery('#hidden_ignore_'+option).val('ignore');
			}
		}
	);
}

function wpseo_presstrends_ajax(nonce, val) {
    jQuery.post(ajaxurl, {
            action: 'wpseo_presstrends_ajax',
            value: val,
            _wpnonce: nonce
        }, function(data) {
            if (data)
                jQuery('#wp-pointer-0').hide();
        }
    );
}
