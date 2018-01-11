// (function ($) {
//   $(document).ready(function($) {
    quicktags({
      id: "comment",
      buttons: "link,em,strong"
    });
    quicktags({
      id: "bbp_reply_content",
      buttons: "link,em,strong"
    });
    quicktags({
      id: "bbp_topic_content",
      buttons: "link,em,strong"
    });
    quicktags({
      id: "posttext",
      buttons: "link,em,strong"
    });
    QTags.addButton('quote', bcq_script_vars.quote, '<blockquote>', '</blockquote>', 'quote');
//   });
// })(jQuery);