
jQuery(document).ready(function() {
  var on_mobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  var url_format_matcher = /(&?[a-zA-Z0-9]+=)?{([a-zA-Z0-9]+)}/g;
  var popup_args = 'width=600, height=400, location=0, menubar=0, resizeable=0, scrollbars=0, status=0, titlebar=0, toolbar=0';

  if (on_mobile) {
    jQuery('.bududu_mobile').show();
  }

  jQuery('.bududu_share_link').on('click', function(e) {
    var share_url = jQuery(this).data('share');

    var opts = {
      text: jQuery(this).data('text'),
      url: jQuery(this).data('url')
    }

    var url = share_url.replace(url_format_matcher, function(match, key, field) {
      var val = opts[field];
      return key + window.encodeURIComponent(val)
    });

    window.open(url, null, popup_args);
    return false;
  });
});

