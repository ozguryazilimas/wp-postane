function check_api_google(provider) {
    provider_input = jQuery('#wapt_' + provider + '_apikey');
    provider_input2 = jQuery('#wapt_' + provider + '_cse');
    if (provider_input.val() !== "" || provider_input2.val() !== "") {
        provider_input.addClass("checked_api_key_proccess");
        provider_input2.addClass("checked_api_key_proccess");
        jQuery.post(ajaxurl, {
            action: 'apt_check_api_key',
            provider: provider,
            key: provider_input.val(),
            key2: provider_input2.val(),
            nonce: jQuery('#wapt_ajax_nonce').val(),
        }).done(function (html) {
            console.log(html);
            provider_input.removeClass("checked_api_key_proccess");
            provider_input2.removeClass("checked_api_key_proccess");
            if (html) {
                provider_input.removeClass("checked_api_key_fail");
                provider_input2.removeClass("checked_api_key_fail");
                provider_input.addClass("checked_api_key_ok");
                provider_input2.addClass("checked_api_key_ok");
            } else {
                provider_input.removeClass("checked_api_key_ok");
                provider_input2.removeClass("checked_api_key_ok");
                provider_input.addClass("checked_api_key_fail");
                provider_input2.addClass("checked_api_key_fail");
            }
        });
    } else if (provider_input.val() === "" && provider_input2.val() === "") {
        provider_input.removeClass("checked_api_key_proccess");
        provider_input2.removeClass("checked_api_key_proccess");
        provider_input.removeClass("checked_api_key_fail");
        provider_input2.removeClass("checked_api_key_fail");
        provider_input.removeClass("checked_api_key_ok");
        provider_input2.removeClass("checked_api_key_ok");

    }
}

jQuery(document).on('change', '#wapt_google_apikey', function (event) {
    check_api_google('google');
});
jQuery(document).on('change', '#wapt_google_cse', function (event) {
    check_api_google('google');
});
