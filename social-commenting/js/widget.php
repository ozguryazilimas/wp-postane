<?php
header("Content-type: application/javascript");
?>

jQuery(document).ready(function(){
  jQuery(".sc_widget_email_ok").click(function(){
    // email al
    var dis = jQuery(this);
    var parent = dis.parent(".sc_widget_email_me");
    var post_id = parent.attr("data-post-id");
  
    var data = {
      'action': 'sc_get_email',
      'post_id': post_id
    };
    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        dis.removeClass("sc_widget_display");
        parent.children('.sc_widget_email_no').addClass("sc_widget_display");
        alert("Bundan sonra bu yazıya yeni yorum geldiğinde email alacaksınız.");
      }
    });
  });
  jQuery(".sc_widget_email_no").click(function(){
    // email alma
    var dis = jQuery(this);
    var parent = dis.parent(".sc_widget_email_me");
    var post_id = parent.attr("data-post-id");
  
    var data = {
      'action': 'sc_dont_get_email',
      'post_id': post_id
    };
    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        dis.removeClass("sc_widget_display");
        parent.children('.sc_widget_email_ok').addClass("sc_widget_display");
        alert("Bundan sonra bu yazıya yeni yorum geldiğinde email almayacaksınız.");
      }
    });
  });
});
