
<?php

header("Content-type:application/javascript")
?>
jQuery(document).ready(function(){

  jQuery(".sc_minus").click(function(){
    var dis = jQuery(this);
    var parent = dis.parent(".sc_subscribe_button");
    var post_id = parent.attr("data-postid");
  
    var data = {
      'action': 'sc_unsubscribe',
      'post_id': post_id
    };

    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        dis.removeClass("sc_display");
        parent.children('.sc_plus').addClass("sc_display");
        alert("Artık bu yazıyı takip etmiyorsunuz.");
      }
    });
  });

  jQuery(".sc_plus").click(function(){
    var dis = jQuery(this);
    var parent = dis.parent(".sc_subscribe_button");
    var post_id = parent.attr("data-postid");
  
    var data = {
      'action': 'sc_subscribe',
      'post_id': post_id
    };
    
    jQuery.post('<?php echo $_GET['sc_url']; ?>',data,function(response){
      if(response=="done") {
        dis.removeClass("sc_display");
        parent.children('.sc_minus').addClass("sc_display");
        alert("Artık bu yazıyı takip ediyorsunuz.");
      }
    });
  });
});

