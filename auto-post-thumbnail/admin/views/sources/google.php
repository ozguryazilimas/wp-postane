<?php  $ajaxloader = WAPT_PLUGIN_URL."/admin/assets/img/ajax-loader-line.gif";
$apt_google_nonce = wp_create_nonce( 'apt_upload' );

$apt_google_key = WAPT_Plugin::app()->getOption('google_apikey');
$apt_google_cse = WAPT_Plugin::app()->getOption('google_cse');

if(isset($_REQUEST['post'])) $pid = $_REQUEST['post'];
else $pid = 0;

if($apt_google_key && $apt_google_cse)
{
?>
<script type="text/javascript">
    var thisModal = window.parent.window.parent.window.wp.media.frame;
    var API_KEY = '<?php  echo $apt_google_key;?>';
    var CSE = '<?php  echo $apt_google_cse;?>';
    function call_api(query, page = 1) {
        if (page < 1) page = 1; //защита
        var start = ((page-1)*10) + 1;
        var URL = "https://www.googleapis.com/customsearch/v1?searchType=image&start="+start+"&q=" + encodeURIComponent(query) + "&key=" + API_KEY + "&cx=" + CSE;

        jQuery.getJSON(URL, function (data) {
            if (!parseInt(data.searchInformation.totalResults) > 0) {
                jQuery('#loader_flex').hide();
                jQuery('#page_num_div').hide();
                jQuery('#prev_page').hide();
                jQuery('#next_page').hide();
                jQuery('#google_results').html('<?php  echo __( 'No hits', 'apt' ); ?>');
                return false;
            }
            show_images(data, page);
        });
    }

    function show_images(data, page) {
        var s = '';
        var totalhits = data.searchInformation.totalResults;
        jQuery.each(data.items, function (k, v) {
            descr = v.title;
            if(!descr) descr = "google_image";
            s += '<div class="item upload_google" ' +
                'data-service="google" ' +
                'data-title="'+ descr +'" ' +
                'data-url="' + v.link +'" '+
                'data-link="' + v.image.contextLink + '" ' +
                'data-w="' + v.image.width + '" ' +
                'data-h="' + v.image.height + '">' +
                '<img src="' + v.image.thumbnailLink + '">' +
                '<div class="download"><img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/download.svg' ?>">' +
                '<div>' + v.image.width + '×' + v.image.height + '<br>' +
                '<a href="'+ v.image.contextLink +'" target="_blank">'+descr.substr(0,15)+'</a>' +
                '</div>' +
                '</div>' +
                '</div>';
            //console.log(v.urls.regular);
        });
        jQuery('#google_results').html(jQuery('#google_results').html() + s);
        jQuery('.flex-images').flexImages({rowHeight: 160});
        jQuery('#loader_flex').hide();

        if (page > 1) jQuery('#prev_page').show();
        else jQuery('#prev_page').hide();
        if (page < parseInt(totalhits, 10) / 20) jQuery('#next_page').show();

        jQuery('#page_num_div').html(page);
        jQuery('#page_num_div').show();
    }

    function do_submit() {
        jQuery('#loader_flex').show();
        q = jQuery('#query', form).val();
        p = jQuery('#page_num', form).val();

        jQuery('#google_results').html('');
        call_api(q, p);
    }

    jQuery('#prev_page').click(function (e) {
        jQuery('#page_num', form).val(parseInt(jQuery('#page_num', form).val(), 10) - 1);
        do_submit();
    });
    jQuery('#next_page').click(function (e) {
        jQuery('#page_num', form).val(parseInt(jQuery('#page_num', form).val(), 10) + 1);
        do_submit();
    });

    //Кнопка поиска
    jQuery(document).ready(function () {
        form = jQuery('#google_images_form');

        form.submit(function (e) {
            e.preventDefault();
            do_submit();
        });
    });

    //загрузка в медиабиблиотеку
    jQuery(document).on('click', '.upload_google', function (e) {
        if (jQuery(e.target).is('a')) return;
        //jQuery(document).off('click', '.upload_google');
        // loading animation
        var downdiv = jQuery(this);
        downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg' ?>" style="height:80px !important">');
        jQuery.post(ajaxurl,
            {
                action: 'upload_to_library',
                is_upload: "1",
                service: jQuery(this).data('service'),
                image_url: jQuery(this).data('url'),
                image_user: jQuery(this).data('user'),
                q: q,
                postid: <?php echo $pid;?>,
                title: jQuery(this).data('title'),
                excerpt: '<a href="'+ jQuery(this).data('link') +'" target="_blank">'+jQuery(this).data('title')+'</a>',
                wpnonce: '<?php  echo $apt_google_nonce; ?>'
            },
            function (data) {
                if (parseInt(data) == data)
                {
                    downdiv.removeClass('uploading').find('.download img').replaceWith('DOWNLOADED');
                    downdiv.removeClass('upload_google');
                    jQuery('#apt-button-next').prop('disabled',false);

                    if(window.cvapt_media_refresh !== undefined) {
                        window.parent.window.cvapt_media_refresh();
                    }
                }
                else
                {
                    alert(data);
                    downdiv.removeClass('uploading').find('.download img').replaceWith('ERROR');
                    downdiv.removeClass('upload_google');
                }
            });
        return false;
    });
</script>

<div style="padding:10px 15px 25px">
    <form id="google_images_form" style="margin:0">
        <div class="divform">
            <input id="query" type="text" value="" class="input_query" autofocus
                   placeholder="<?php echo __( 'Search...', 'apt' ); ?>">
            <input id="page_num" type="hidden" value="1">
            <button type="submit" class="submit_button" title="<?php echo __( 'Search', 'apt' ); ?>"><img
                        src="<?php echo WAPT_PLUGIN_URL . '/admin/assets/img/search.png' ?>"></button>
        </div>
    </form>
    <div id="loader_flex" style="display: none;"><img src='<?php  echo $ajaxloader; ?>' width='100px' alt=''></div>
    <div id="google_results" class="flex-images"></div>
    <div class="apt_pages">
        <button id="prev_page" style="display: none;"><span class="dashicons dashicons-arrow-left-alt"></span> <?php echo __('Prev','apt'); ?>
        </button>
        <div id="page_num_div" style="display: none;"></div>
        <button id="next_page" style="display: none;"><?php echo __('Next','apt'); ?> <span class="dashicons dashicons-arrow-right-alt"></span>
        </button>
    </div>
</div>
<?php
}
else
{
    ?>
    <div><?php echo __('API key is missing. Add it in APT settings','apt');?> ->
        <a href="<?= admin_url('admin.php?page=wapt_settings-wbcr_apt'); ?>" target="_blank">here</a></div>
    <?php
} ?>