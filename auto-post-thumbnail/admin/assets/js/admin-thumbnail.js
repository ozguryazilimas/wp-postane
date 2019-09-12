/**
 * This function:
 *
 * Generates a custom image uploader / selector tied to a post where the click action originated
 * Upon clicking "Use as thumbnail" the image selected is set to be the post thumbnail
 * A thumbnail image is then shown in the All Posts / All Pages / All Custom Post types Admin Dashboard view
 *
 * @since 1.0.0
 *
 * global ajaxurl, apt_thumb - language array
 */

//Отображение окна со всеми картинками в тексте поста
jQuery(document).ready(function($){

    window.aptModalShow = function(that, postid, wpnonce){

        var $modal = $('#post_imgs_'+postid).find('> p');
        var $ajaximg = $('#post_imgs_'+postid).find('> span');
        //$modal.html('');
        $('.imgs').find('> p').html(''); //очистка всех модальных окон, чтобы исключить конфликты

        tb_show( apt_thumb.modal_title, '/?TB_inline&inlineId=post_imgs_'+postid+'&width=600&height=500' );

        $ajaximg.show();
        // AJAX запрос для загрузки контента окна
        jQuery.post ( ajaxurl, {
            action:         action_column_get_thumbnails,
            post_id:        postid,
            _ajax_nonce:    wpnonce,
            cookie:         encodeURIComponent( document.cookie )
        }).done( function( html ) {
            $ajaximg.hide();
            $modal.html(html);
            $('#apt_thumbid2').autocolumnlist({
                columns: 3,
                classname: 'column-apt',
                min: 1
            });
        });

        //return false; // для ссылки
    }
});