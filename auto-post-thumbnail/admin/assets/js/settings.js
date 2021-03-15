(function ($) {
    $(document).ready(function () {
        $('input#wapt_text-line-spacing').attr('step', '0.1');
        $('input#wapt_text-line-spacing').attr('min', '0');

        $(".wapt-icon-pro input[type='checkbox']").attr('disabled', 'disabled');
        //Чтобы при клике на label не открывались закрытые опции
        /*$("label[for*='wapt_']").on('click', function (e) {
            e.preventDefault();
        })*/
    });
})(jQuery);