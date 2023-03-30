/**
 * Этот файл содержит скрипт исполняелся во время процедур с формой лицензирования.
 * Его основная роль отправка ajax запросов на проверку, активацию, деактивацию лицензии
 * и вывод уведомлений об ошибка или успешно выполнении проверок.
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 05.10.2018, Webcraftic
 * @version 1.1
 * @since 1.4.0
 */


jQuery(function ($) {

    $(document).on('click', '.wapt-control-btn', function () {

        $('.wapt-control-btn').hide();

        var wrapper = $('#wapt-license-wrapper'),
            loader = wrapper.data('loader');

        $(this).after('<img class="wapt-loader" src="' + loader + '">');

        var data = {
            action: 'wapt_check_license',
            _wpnonce: $('#_wpnonce').val(),
            license_action: $(this).data('action'),
            licensekey: ''
        };

        if ($(this).data('action') == 'activate') {
            data.licensekey = $('#license-key').val();
        }

        $.ajax(ajaxurl, {
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (response) {
                var noticeId;

                if (!response || !response.success) {

                    $('.wapt-control-btn').show();
                    $('.wapt-loader').remove();

                    if (response.data) {
                        console.log(response.data.error_message);
                        alert('Error: [' + response.data.error_message + ']');
                    } else {
                        console.log(response);
                    }

                    return;
                }

                if (response.data && response.data.message) {
                    $.wbcr_factory_templates_116.app.showNotice(response.data.message, 'success');

                    window.location.reload();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {

                $('.wapt-control-btn').show();
                $('.wapt-loader').remove();

                console.log(xhr.status, xhr.responseText, thrownError);

                alert('Error: [' + thrownError + '] Status: [' + xhr.status + '] Error massage: [' + xhr.responseText + ']');
            }
        });

        return false;
    });

});
