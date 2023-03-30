jQuery(function ($) {

    var $element = $(".factory-bootstrap-467 .factory-wapt-fonts");
    var upload_button = $('#wapt-upload-button');
    var upload_loader = $('#wapt-upload-loader');

    $element.factoryBootstrap467_dropdownControl();

    upload_button.on('click', function (e) {
        e.preventDefault();
        $('#wapt-font-file').trigger('click');
    });

    $('#wapt-font-file').on('change', function (event) {
        upload_button.attr('disabled', 'disabled');
        upload_loader.toggleClass('wapt-loader-invisible');
        files = this.files;
        //event.stopPropagation(); // остановка всех текущих JS событий

        // ничего не делаем если files пустой
        if (typeof files == 'undefined' || files.length < 1) return;

        var data = new FormData();

        // заполняем объект данных файлами в подходящем для отправки формате
        $.each(files, function (key, value) {
            data.append(key, value);
        });

        data.append('action', 'wapt_upload_font');
        data.append('wpnonce', wapt_upload_font.nonce);
        data.append('is_font_upload', 1);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (respond, status, jqXHR) {
                if (typeof respond.error === 'undefined') {
                    $('#wapt-font-file').val([]);
                    console.log(respond.files);
                    file = respond.files;
                    var $option = $('<option />')
                        .attr('value', file.name)
                        .text(file.name)
                        .appendTo($element);
                } else {
                    alert('ERROR: ' + respond.error);
                    console.log('ERROR: ' + respond.error);
                }
            },
            error: function (jqXHR, status, errorThrown) {
                console.log('AJAX error: ' + status, jqXHR);
            },
            complete: function () {
                upload_button.removeAttr('disabled');
                upload_loader.toggleClass('wapt-loader-invisible');
            }

        });
    });

});
