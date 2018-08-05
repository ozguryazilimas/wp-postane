
jQuery(document).ready(function() {
  var on_mobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

  if (on_mobile) {
    jQuery('#bududu_whatsapp').show();

    jQuery('#bududu_whatsapp_link').on('click', function(e) {
      var post_text = jQuery(this).data('text');
      var post_url = jQuery(this).data('link');
      var url = "whatsapp://send?text=" + encodeURIComponent(post_text + ' - ' + post_url);

      window.location.href= url;
    });
  }
});

