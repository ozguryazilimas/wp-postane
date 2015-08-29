<?php
header("Content-type: application/javascript");
?>

jQuery(document).ready(function(){
  jQuery(".sc_subscribe_list_unsubscribe").click(function(){
    var dis = jQuery(this);
    var post_id = dis.attr("data-postid");
    var parent = dis.parent();

    var data = {
      'action': 'sc_unsubscribe',
      'post_id': post_id
    };

    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        parent.fadeOut(300);
      }
    });
  });

  jQuery(".sc_subscribe_list_email_ok").click(function(){
    var dis = jQuery(this);
    var post_id = dis.attr("data-postid");
    var parent = dis.parent();

    var data = {
      'action': 'sc_get_email',
      'post_id': post_id
    };

    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        dis.removeClass("sc_subscribe_list_display");
        parent.children(".sc_subscribe_list_email_no").addClass("sc_subscribe_list_display");
      }
    });

  });

  jQuery(".sc_subscribe_list_email_no").click(function(){
    var dis = jQuery(this);
    var post_id = dis.attr("data-postid");
    var parent = dis.parent();

    var data = {
      'action': 'sc_dont_get_email',
      'post_id': post_id
    };

    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        dis.removeClass("sc_subscribe_list_display");
        parent.children(".sc_subscribe_list_email_ok").addClass("sc_subscribe_list_display");
      }
    });

  });
  jQuery(".sc_mark_as_read").click(function(){
    var data = {
      'action':'sc_mark_as_read'
    };

    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        location.reload();
      }
    });
  });

});
