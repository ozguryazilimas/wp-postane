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
                // Классы перекрываются каким-то другим !important
                // Пришлось костыльнуть так
                provider_input.attr('style', 'border-color: green !important');
                provider_input2.attr('style', 'border-color: green !important');
            } else {
                // Классы перекрываются каким-то другим !important
                // Пришлось костыльнуть так
                provider_input.attr('style', 'border-color: red !important');
                provider_input2.attr('style', 'border-color: red !important');
            }
        });
    } else if (provider_input.val() === "" && provider_input2.val() === "") {
        provider_input.removeClass("checked_api_key_proccess");
        provider_input2.removeClass("checked_api_key_proccess");
        // Классы перекрываются каким-то другим !important
        // Пришлось костыльнуть так
        provider_input.removeAttr('style');
        provider_input2.removeAttr('style');

    }
}

jQuery(document).on('change', '#wapt_google_apikey', function (event) {
    check_api_google('google');
});
jQuery(document).on('change', '#wapt_google_cse', function (event) {
    check_api_google('google');
});
