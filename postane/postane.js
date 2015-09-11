jQuery(document).ready(function(){
  var postane_tooltip = {
    tooltipClass : 'postane_tooltip'
  };

  jQuery("#postane_messages_participants_button").tooltip(postane_tooltip);
  jQuery("#postane_back").tooltip(postane_tooltip);
  jQuery("#postane_messages_quit_button").tooltip(postane_tooltip);
  jQuery("#postane_messages_addparticipant_button").tooltip(postane_tooltip);
  jQuery("#postane_new_thread_participants").autocomplete( {
    source: function(request, response) {
      data = {
        'action' : 'postane',
        'postane_action' : 'autocomplete_username',
        'postane_autocomplete_input' : request.term
      };

      jQuery.post(ajaxurl, data, function(data) {
        response(jQuery.parseJSON(data));
      });
    }
  });

  jQuery("#postane_new_participant").autocomplete({
    source: function(request, response) {
      data = {
        'action' : 'postane',
        'postane_action' : 'autocomplete_username',
        'postane_autocomplete_input' : request.term
      };

      jQuery.post(ajaxurl, data, function(data) {
        response(jQuery.parseJSON(data));
      });
    }
  });

  function dump(obj) {
      var out = '';
      for (var i in obj) {
          out += i + ": " + obj[i] + "\n";
      }

      return out;
  }

  /*var handler = function(e) {
    if(e.target.id == "postane_thread_container") return;
  };

  jQuery("#postane").mouseenter(function(){
    jQuery("body").bind("mousewheel",handler);
  });

  jQuery("#postane").mouseleave(function(){
    jQuery("body").unbind("mousewheel", handler);
  });
*/

  var current_thread_id = null;
  var thread_step = 10;
  var thread_exclusion_list = [0];
  var message_step = 10;
  var message_exclusion_list = [0];
  var message_min_time = null;
  var message_max_time = null;

  var error_div = jQuery("#postane_error");
  var success_div = jQuery("#postane_success");
  function postane_announce_error(error) {
    success_div.fadeOut(300);
    error_div.html(error);
    error_div.fadeIn(300);
    error_div.delay(3000).fadeOut(300);
  }

  function postane_announce_success() {
    error_div.fadeOut(300);
    success_div.html("Başarılı");
    success_div.fadeIn(300);
    success_div.delay(3000).fadeOut(300);
  }

  jQuery("#postane").ajaxStart(function() {
    jQuery("#postane_loading img").attr("src", postane_base + "img/loading.gif");
  });
  jQuery("#postane").ajaxStop(function() {
    jQuery("#postane_loading img").attr("src", postane_base + "img/logo.png")
  });

  function message_container_scroll_to_bottom() {
    var postane_message_container = jQuery("#postane_message_container");
    postane_message_container.scrollTop(postane_message_container[0].scrollHeight);
  }


  function create_message_div(cur, participant_info) {
    var edited = (cur['edited'] == '1');
    var read = cur['read'] == '1';
    var avatar_div = jQuery("<div></div>");
    avatar_div.addClass("postane_user_avatar");
    avatar_div.html(participant_info[cur['user_id']]['avatar']);
    var username_div = jQuery("<div></div>");
    username_div.addClass("postane_username");
    username_div.html(participant_info[cur['user_id']]['display_name']);

    var message_content_div = jQuery("<div></div>");
    message_content_div.addClass("postane_message_content");
    message_content_div.html(cur['message_content']);

    
    var date_div = jQuery("<div></div>");
    date_div.addClass("postane_message_date");
    if(edited) {
      date_div.html('düzenlendi, ' + cur['edit_time']);
    } else {
      date_div.html(cur['message_creation_time']);
    }

  
    var message_div = jQuery("<div></div>");
    message_div.addClass("postane_message");

    var top_div = jQuery("<div></div>");
    top_div.append(avatar_div);
    top_div.append(username_div);
    top_div.addClass("postane_message_top_div");

    var delete_button_div = jQuery("<div></div>");
    delete_button_div.addClass("postane_messages_delete_button");
    delete_button_div.html("sil");
    delete_button_div.attr("data-message-id", cur['id']);
    top_div.append(delete_button_div);


    if(cur['can_edit']) {
      var edit_button_div = jQuery("<div></div>");
      edit_button_div.addClass("postane_messages_edit_button");
      edit_button_div.html("düzenle");
      edit_button_div.attr("data-message-id",cur['id']);
      top_div.append(edit_button_div);
    }

    message_div.append(top_div);
    message_div.append(message_content_div);
    message_div.append(date_div);

    if(edited) {
      message_div.addClass("postane_message_edited");
    }
    if(!read) {
      message_div.addClass("postane_message_not_read");
      message_div.attr("data-message-id", cur['id']);
    }
    return message_div;
  }

  function load_threads() {
    data = {
      'action' : 'postane',
      'postane_action' : 'get_threads',
      'postane_step' : thread_step,
      'postane_exclusion_list' : thread_exclusion_list
    };
    jQuery.post(ajaxurl, data, function(data) {
      var results = JSON.parse(data);
      //alert(results.length);
      var postane_thread_more_button = jQuery("#postane_thread_more_button");
      if(typeof results["success"] != 'undefined') {
        results = results["success"];
        if(results.length == thread_step) {
          jQuery("#postane_thread_more_button").fadeIn(300);
        } else {
          jQuery("#postane_thread_more_button").fadeOut(300);
        }
        for(i = 0; i < results.length; i++) {
          var thread_id = results[i]['thread_id'];
          var thread_title = results[i]['thread_title'];
          var last_message_time = results[i]['thread_last_message_time'];
          var last_read_time = results[i]['thread_last_read_time'];
          var participants = results[i]['participants'];
          var title_div = jQuery("<div></div>");
          title_div.addClass("postane_thread_title");
          title_div.html("<p>" + thread_title + "</p>");
          thread_exclusion_list.push(thread_id);
          var date_div = jQuery("<div></div>");
          date_div.addClass("postane_thread_date");
          date_div.html("<p>" + last_message_time.split(" ")[0] + " " + last_message_time.split(" ")[1].split(":")[0] + ":" + last_message_time.split(" ")[1].split(":")[1] + "</p>");
          var buttons_div = jQuery("<div></div>");
          buttons_div.addClass("postane_thread_buttons_div");
          var mark_as_read_div = jQuery("<div></div>");
          mark_as_read_div.addClass("postane_thread_mark_as_read");
          mark_as_read_div.attr("data-thread-id", thread_id);
          var mark_as_read_img = jQuery("<img>");
          mark_as_read_img.attr("src", postane_base + "img/read.png");
          mark_as_read_div.append(mark_as_read_img);
          mark_as_read_div.attr("title", "Tüm mesajları okundu olarak işaretle");
          mark_as_read_div.tooltip(postane_tooltip);

          var remove_messages_div = jQuery("<div></div>");
          remove_messages_div.addClass("postane_thread_remove_messages");
          remove_messages_div.attr("data-thread-id", thread_id);
          var remove_messages_img = jQuery("<img>");
          remove_messages_img.attr("src", postane_base + "img/clean.png");
          remove_messages_div.append(remove_messages_img);
          remove_messages_div.attr("title", "Bu konuşmadaki tüm mesajları sil");
          remove_messages_div.tooltip(postane_tooltip);
          var quit_thread_div = jQuery("<div></div>");
          quit_thread_div.addClass("postane_thread_quit_thread");
          quit_thread_div.attr("title", "Bu konuşmadaki tüm mesajları sil ve konuşmadan ayrıl");
          quit_thread_div.attr("data-thread-id", thread_id);
          quit_thread_div.tooltip(postane_tooltip);
          var quit_thread_img = jQuery("<img>");
          quit_thread_img.attr("src", postane_base + "img/cross.png");
          quit_thread_div.append(quit_thread_img);
          var thread_div = jQuery("<div></div>");
          thread_div.addClass("postane_thread");
          if(last_read_time < last_message_time) {
            thread_div.addClass("postane_thread_unread");
          }
          var participants_div = jQuery("<div></div>");
          participants_div.addClass("postane_threads_participant_list");
          participants_div.html("Kimden: ");
          for(j=0 ; j<participants.length; j++) {
            var p_div = jQuery("<div></div>");
            p_div.addClass("postane_threads_participant");
            var avatar_div = jQuery("<div></div>");
            avatar_div.addClass("postane_threads_avatar");
            avatar_div.html(participants[j]['avatar']);

            var username_div = jQuery("<div></div>");
            username_div.addClass("postane_threads_username");
            username_div.html('<a href="'+participants[j]['link'] + '">' + participants[j]['display_name'] + '</a>');
            p_div.append(avatar_div);
            p_div.append(username_div);
            participants_div.append(p_div);
            if(j != participants.length-1) {
              participants_div.html(participants_div.html() + ",");
            }
          }
          //buttons_div.append(mark_as_read_div);
          buttons_div.append(remove_messages_div);
          buttons_div.append(quit_thread_div);
          thread_div.append(title_div);
          thread_div.append(buttons_div);
          thread_div.append(participants_div);
          thread_div.append(date_div);
          thread_div.attr("data-thread-id", thread_id);
          postane_thread_more_button.before(thread_div);
        }
      } else {
        postane_announce_error(results["error"]);
      }
    });
  }
  load_threads();


  jQuery("#postane_new_thread_toggle").click(function(){
    jQuery("#postane_new_thread").slideToggle();
  });

  var participant_array = [''];
  jQuery("#postane_new_thread_participants").keyup(function(e) {
    if(e.keyCode == 13) {
      jQuery(this).html('');
    }
  });
  jQuery("#postane_new_thread_participants").keydown(function(e) {
    if(e.keyCode == 13) {
      var participants = jQuery(this);
      var html = participants.html();
      jQuery(this).html('');
      if (participant_array.indexOf(html) == -1) {
        data = {
          'action' : 'postane',
          'postane_action' : 'user_exists',
          'postane_username' : html
        }
        jQuery.post(ajaxurl, data, function(data) {
          participant_array.push(html);
          var result = JSON.parse(data);
          var participant_div = jQuery("<div></div>");
          participant_div.html(html);
          participant_div.addClass("postane_participant_array_element");
          if(typeof result["success"] != 'undefined') {
            participant_div.addClass("postane_existing_participant");
            //success
          } else {
            participant_div.addClass("postane_nonexisting_participant");
            //fail
          }
          var list = jQuery("#postane_new_thread_participant_list");
          list.css("display","block");
          list.append(participant_div);
        });
      }
    }
  });

  jQuery("div").delegate(".postane_participant_array_element", "click", function() {
    var dis = jQuery(this);
    html = dis.html();
    index = participant_array.indexOf(html);
    if(index != -1) {
      participant_array.splice(index, 1);
    }
    if(participant_array.length < 2) {
      jQuery("#postane_new_thread_participant_list").css("display","none");
    }
    dis.remove();
  });

  jQuery("#postane_new_thread_send").click(function(){
    var title = jQuery("#postane_new_thread_title").html();
    var message_content = jQuery("#postane_new_thread_message").html();
    var participants = [];
    jQuery(".postane_existing_participant").each(function(){
      participants.push(jQuery(this).html());
    });

    var data_array = {action : 'postane', postane_action: 'create_thread', postane_participants : participants, postane_thread_title : title, postane_message_content : message_content};
    jQuery.post(ajaxurl, data_array, function(data) {
      data = JSON.parse(data);
      if(typeof data["success"] != 'undefined') {
        jQuery("#postane_new_thread_participant_list").html('');
        jQuery("#postane_new_thread_participant_list").css('display', 'none');
        jQuery("#postane_new_thread_title").html('');
        jQuery("#postane_new_thread_message").html('');
        reset_thread_screen();
        jQuery("#postane_new_thread").slideUp(300);
        load_threads();
        participant_array = [''];
        postane_announce_success();
      } else {
        postane_announce_error(data["error"]);
      }
    });
  });

  jQuery("#postane_new_thread_title").keydown(function(e){
    if(e.keyCode == 13) {
      e.preventDefault();
      document.execCommand('insertHTML', false, '');
      return false;
    }
  });

  jQuery("#postane_new_thread_message").keydown(function(e){
    if(e.keyCode == 13) {
      document.execCommand('insertHTML', false, '<br/><br/>');
      return false;
    }
  });

  jQuery("#postane_thread_message").keydown(function(e){
    if(e.keyCode == 13) {
      document.execCommand('insertHTML', false, '<br/><br/>');
      return false;
    }
  });

  jQuery("#postane_thread_more_button").click(function(){
    load_threads();
  });


  function reset_message_screen() {
    jQuery("#postane_messages_addparticipant_button").hide();
    jQuery("#postane_email_checkbox").prop("checked", false);
    message_exclusion_list = [0];
    message_min_time = null;
    jQuery(".postane_message").remove();
    jQuery("#postane_participants_container").hide();
    jQuery("#postane_add_participant_container").hide();
  }  

  function reset_thread_screen() {
    thread_exclusion_list = [0];
    jQuery(".postane_thread").remove();
    current_thread_id = null;
  }

  var time_data = {
    'action' : 'postane',
    'postane_action' : 'get_current_time'
  }

  jQuery.post(ajaxurl, time_data, function(data){
    var result = JSON.parse(data);
    if(typeof result["success"] != 'undefined') {
      message_max_time = result["success"]['current_time'];
    } else {
      postane_announce_error(result["error"]);
    }
  });

  function load_messages_async(thread_id) {
    var postane_messages = jQuery("#postane_messages");
     var data = {
      'action' : 'postane',
      'postane_action' : 'get_messages_async',
      'postane_thread_id' : thread_id,
      'postane_exclusion_list' : message_exclusion_list,
      'postane_min_time' : message_max_time
    };
    jQuery.post(ajaxurl, data, function(data) {
      data = JSON.parse(data);
      if(typeof data["success"] != 'undefined') {
        data = data["success"];
        var participant_info = data["participants_for_message_info"];
        var message_info = data["message_info"];

        var message_container = jQuery("#postane_message_container");
        for(i=0; i<message_info.length; i++) {
          var cur = message_info[i];
          message_exclusion_list.push(cur['id']);
          if(i == message_info.length - 1) {
            message_min_time = cur['message_creation_time'];
          }
          message_max_time = cur['message_creation_time'];
          var message_div = create_message_div(cur, participant_info);
          message_div.css("display","none");
          message_container.append(message_div);
          message_div.fadeIn(500);
          message_container_scroll_to_bottom();
        }
        jQuery("#postane_message_container").scroll();
      } else {
        postane_announce_error(data["error"]);
      }
    });
  }

  setInterval(function() {
    if(current_thread_id != null) {
      load_messages_async(current_thread_id);
    }
  },4000);


  function load_messages(thread_id) {
    current_thread_id = thread_id;
    var postane_messages = jQuery("#postane_messages");

    var data = {
      'action' : 'postane',
      'postane_action' : 'get_messages',
      'postane_thread_id' : thread_id,
      'postane_step' : message_step,
      'postane_exclusion_list' : message_exclusion_list,
      'postane_max_time' : message_min_time
    };

    jQuery.post(ajaxurl, data, function(data) {
      data = JSON.parse(data);
      if(typeof data["success"] != 'undefined') {
        data = data["success"];
        var is_admin = data["is_current_user_admin"];
        var thread_info = data["thread_info"];
        var participant_current_info = data["participant_info"];
        var participant_info = data["participants_for_message_info"];
        var message_info = data["message_info"];
        var send_email = data["send_email"];

        if(send_email) {
          jQuery("#postane_email_checkbox").prop("checked", true);
        } else {
          jQuery("#postane_email_checkbox").prop("checked", false);
        }
        if(is_admin) {
          jQuery("#postane_messages_addparticipant_button").css("display", "inline-block");
        }
        var participant_table = jQuery("<table></table>");
        participant_table.addClass("postane_messages_participant_table");
        var participant_head_row = jQuery("<tr></tr>");
        participant_head_row.addClass("postane_messages_participant_header_row");
        
        var dummy_header = jQuery("<td></td>");
        participant_head_row.append(dummy_header);

        var dummy_header2 = jQuery("<td></td>");
        participant_head_row.append(dummy_header2);


        var name_header = jQuery("<td></td>");
        name_header.html("İsim");
        name_header.addClass("postane_messages_participants_name_header");

        var join_time_header = jQuery("<td></td>");
        join_time_header.html("Katılma zamanı");
        join_time_header.addClass("postane_messages_participants_join_header");

        participant_head_row.append(name_header);
        participant_head_row.append(join_time_header);
        participant_table.append(participant_head_row);
        for(var key in participant_current_info) {
          var cur = participant_current_info[key];
          var admin = cur['is_admin'] == 1;
          var table_row = jQuery("<tr></tr>");
          table_row.addClass("postane_messages_participant_row");
          table_row.attr("data-user-id", cur['user_id']);

          var admin_column = jQuery("<td></td>");
          if(admin) {
            var img_elem = jQuery("<img>");
            img_elem.attr("src", postane_base + 'img/admin.png');
            img_elem.addClass("postane_messages_participant_admin_image");
            admin_column.append(img_elem);
          }
          admin_column.addClass("postane_messages_participant_admin_column");

          var avatar_column = jQuery("<td></td>");
          avatar_column.html(cur['avatar']);
          avatar_column.addClass("postane_messages_participants_avatar_column");

          var name_column = jQuery("<td></td>");
          name_column.html('<a href="' + cur['author_url'] + '">' + cur['display_name'] + '</a>');
          name_column.addClass("postane_messages_participants_name_column");

          var join_time_column = jQuery("<td></td>");
          join_time_column.html(cur['join_time']);
          join_time_column.addClass("postane_messages_participants_join_column");

          table_row.append(admin_column);
          table_row.append(avatar_column);
          table_row.append(name_column);
          table_row.append(join_time_column);
          participant_table.append(table_row);
        }
        jQuery("#postane_participants_container").html('');
        jQuery("#postane_participants_container").append(participant_table);

        jQuery("#postane_messages_title").html(thread_info['thread_title']);
        jQuery("#postane_messages_title").attr("data-thread-id", thread_info['id']);
        var message_more_button = jQuery("#postane_message_more_button");
        //alert(JSON.stringify(message_info));
        if(message_info.length == message_step) {
          message_more_button.fadeIn(300);
        } else {
          message_more_button.fadeOut(300);
        }
        for(i=0; i<message_info.length; i++) {
          var cur = message_info[i];
          message_exclusion_list.push(cur['id']);
          if(i == message_info.length - 1) {
            message_min_time = cur['message_creation_time'];
          }
          if(message_max_time == null) {
            message_max_time = cur['message_creation_time'];
          }
          var message_div = create_message_div(cur, participant_info);
          message_div.css("display","none");
          message_more_button.after(message_div);
          message_div.fadeIn(500);
        }
        message_container_scroll_to_bottom();
      } else {
        postane_announce_error(data["error"]);
      }
    });
  }

  jQuery("div").delegate('.postane_thread','click', function() {
    //alert("here");
    var dis = jQuery(this);
    reset_message_screen();
    load_messages(dis.attr("data-thread-id"));
    jQuery("#postane_threads").fadeOut(50, function(){
      jQuery("#postane_messages").fadeIn(50, function(){
        message_container_scroll_to_bottom();
        jQuery("#postane_back").show();
      });
    });
    return false;
  });

  jQuery("#postane_new_message_send").click(function(){
    var thread_id = jQuery("#postane_messages_title").attr("data-thread-id");
    var message_content = jQuery("#postane_new_message").html();
    data = {
      'action' : 'postane',
      'postane_action' : 'add_message',
      'postane_thread_id' : thread_id,
      'postane_message_content' : message_content
    }

    jQuery.post(ajaxurl, data, function(data){
      var result = JSON.parse(data);
      if(typeof result["success"] != 'undefined') {
        result = result["success"];
        message_exclusion_list.push(result['message_id']);
        var avatar_div = jQuery("<div></div>");
        avatar_div.addClass("postane_user_avatar");
        avatar_div.html(result['avatar']);

        var username_div = jQuery("<div></div>");
        username_div.addClass("postane_username");
        username_div.html(result['username']);

        var message_content_div = jQuery("<div></div>");
        message_content_div.addClass("postane_message_content");
        message_content_div.html(result['message_content']);

          
        var date_div = jQuery("<div></div>");
        date_div.addClass("postane_message_date");
        date_div.html(result['message_time']);
        

        
        var message_div = jQuery("<div></div>");
        message_div.addClass("postane_message");

        var top_div = jQuery("<div></div>");
        top_div.append(avatar_div);
        top_div.append(username_div);
        top_div.addClass("postane_message_top_div");

        var edit_button_div = jQuery("<div></div>");
        edit_button_div.addClass("postane_messages_edit_button");
        edit_button_div.html("düzenle");
        edit_button_div.attr("data-message-id",result['message_id']);
        top_div.append(edit_button_div);

        message_div.append(top_div);
        message_div.append(message_content_div);
        message_div.append(date_div);
        message_div.css("display","none");

        jQuery("#postane_message_container").append(message_div);
        message_div.fadeIn(1000);
        jQuery("#postane_new_message").html("");
        message_container_scroll_to_bottom();
      } else {
        postane_announce_error(result["error"]);
      }
    });
  });


  jQuery("#postane_message_more_button").click(function(){
    load_messages(jQuery("#postane_messages_title").attr("data-thread-id"));
  });

  jQuery("div").delegate('.postane_messages_edit_button', 'click', function() {
    var dis = jQuery(this);
    dis.hide();
    var message_id = dis.attr("data-message-id");
    var parent_message = dis.parents(".postane_message");
    parent_message.each(function(){
      var pdis = jQuery(this);
      var content_div = pdis.children(".postane_message_content");
      content_div.each(function(){
        var cdis = jQuery(this);
        cdis.attr("contenteditable","true");
        cdis.focus();

        var edit_button = jQuery("<div></div>");
        edit_button.addClass("postane_messages_edit_action_button");
        edit_button.html("düzenle");
        edit_button.attr("data-message-id", message_id);
        cdis.after(edit_button);
        var cancel_button = jQuery("<div></div>");
        cancel_button.addClass("postane_messages_cancel_edit_button");
        cancel_button.html("iptal");
        cancel_button.attr("data-prev-html", cdis.html());
        edit_button.after(cancel_button);
      });
    });

    return false;
  });


  jQuery("div").delegate(".postane_messages_cancel_edit_button","click",function(){
    var dis = jQuery(this);
    var message_content = dis.attr("data-prev-html");
    var content_divs = dis.siblings(".postane_message_content");
    content_divs.each(function(){
      var cdis = jQuery(this);
      cdis.removeAttr("contenteditable");
      cdis.html(message_content);
    });
    var top_divs = dis.siblings(".postane_message_top_div");
    top_divs.each(function(){
      var tdis = jQuery(this);
      var edit_divs = tdis.children(".postane_messages_edit_button");
      edit_divs.each(function(){
        jQuery(this).show();
        return false;
      });
    });
    var edit_divs = dis.siblings(".postane_messages_edit_action_button");
    edit_divs.each(function(){
      jQuery(this).remove();
      return false;
    });
    dis.remove();
  });

  jQuery("div").delegate(".postane_messages_edit_action_button","click",function(){
    var dis = jQuery(this);
    var message_id = dis.attr("data-message-id");
    var message_content = null;
    var content_divs = dis.siblings(".postane_message_content");

    content_divs.each(function(){
      message_content = jQuery(this).html();
      return false;
    });

    data = {
      'action' : 'postane',
      'postane_action' : 'edit_message',
      'postane_message_id' : message_id,
      'postane_message_content' : message_content
    };

    jQuery.post(ajaxurl, data, function(data) {
      var result = JSON.parse(data);
      if(typeof result["success"] != 'undefined') {
        result = result["success"];
        var cancel_buttons = dis.siblings(".postane_messages_cancel_edit_button");
        cancel_buttons.each(function(){
          jQuery(this).remove();
          return false;
        });

        var message_content_divs = dis.siblings(".postane_message_content");
        message_content_divs.each(function(index,elem){
          jQuery(this).html(result['message_content']);
          jQuery(this).removeAttr("contenteditable");
          return false;
        });
        var top_divs = dis.siblings(".postane_message_top_div");
        top_divs.each(function(){
          var tdis = jQuery(this);
          var edit_divs = tdis.children(".postane_messages_edit_button");
          edit_divs.each(function(){
            jQuery(this).show();
            return false;
          });
        });
        var date_divs = dis.siblings(".postane_message_date");
        date_divs.each(function(){
          jQuery(this).html("düzenlendi, " + result['edit_time']);
          return false;
        });
        dis.remove();
      } else {
        postane_announce_error(result["error"]);
      }
    });
    return false;
  });


  jQuery("#postane_back").click(function() {
    var dis = jQuery(this);
    reset_thread_screen();
    load_threads();
    jQuery("#postane_messages").fadeOut(50, function(){
      jQuery("#postane_threads").fadeIn(50, function(){
        jQuery("#postane_back").hide();
      });
    });
    return false;
  });

  function is_visible(elem)
  {
    var message_container = jQuery("#postane_message_container");

    var docViewTop = message_container.scrollTop();
    var docViewBottom = docViewTop + message_container.height();

    var elemTop = elem.position().top;
    var elemBottom = elemTop + elem.height();
    return ((elemBottom <= docViewBottom) && (elemBottom >= docViewTop) || (elemTop <= docViewBottom) && (elemTop >= docViewTop));
  }

  jQuery("#postane_message_container").scroll(function(){
    jQuery(".postane_message_not_read").each(function() {
      var dis = jQuery(this);
      if(is_visible(dis)) {
        var message_id = dis.attr("data-message-id");
        var data = {
          'action' : 'postane',
          'postane_action' : 'mark_message_read',
          'postane_message_id' : message_id
        };
        jQuery.post(ajaxurl, data, function(data) {
          result = JSON.parse(data);
          if(typeof result["success"] != 'undefined') {
            dis.removeClass("postane_message_not_read");
            dis.addClass("postane_message_marked_read");
          } else {
            postane_announce_error(result["error"]);
          }
        });
      }
    });
  });

  jQuery("#postane_messages_participants_button").click(function(){
    jQuery("#postane_participants_container").slideToggle(200);
  });

  jQuery("#postane_messages_quit_button").click(function(){
    if(window.confirm("Konuşmadan ayrılmak istediğinize emin misiniz?")) {
      var thread_id = jQuery("#postane_messages_title").attr("data-thread-id");
      var data = {
        'action' : 'postane',
        'postane_action' : 'quit_thread',
        'postane_thread_id' : thread_id
      };
      jQuery.post(ajaxurl, data, function(data) {
        data = JSON.parse(data);
        if(typeof data["success"] != 'undefined') {
          jQuery("#postane_back").click();
        } else {
          postane_announce_error(data["error"]);
        }
      });
    }
  });

  jQuery("#postane_messages_addparticipant_button").click(function(){
    jQuery("#postane_add_participant_container").slideToggle();
  });


  var new_participant_array = [''];
  jQuery("#postane_new_participant").keyup(function(e) {
    if(e.keyCode == 13) {
      jQuery(this).html('');
    }
  });
  jQuery("#postane_new_participant").keydown(function(e) {
    if(e.keyCode == 13) {
      var participants = jQuery(this);
      var html = participants.html();
      jQuery(this).html('');
      if (new_participant_array.indexOf(html) == -1) {
        data = {
          'action' : 'postane',
          'postane_action' : 'user_exists',
          'postane_username' : html
        }
        jQuery.post(ajaxurl, data, function(data) {
          new_participant_array.push(html);
          var result = JSON.parse(data);
          var participant_div = jQuery("<div></div>");
          participant_div.html(html);
          participant_div.addClass("postane_participant_new_array_element");
          if(typeof result["success"] != 'undefined') {
            participant_div.addClass("postane_new_existing_participant");
          } else {
            participant_div.addClass("postane_new_nonexisting_participant");
          }
          var list = jQuery("#postane_new_participant_list");
          list.css("display","block");
          list.append(participant_div);
        });
      }
    }
  });
  jQuery("div").delegate(".postane_participant_new_array_element", "click", function() {
    var dis = jQuery(this);
    html = dis.html();
    index = new_participant_array.indexOf(html);
    if(index != -1) {
      new_participant_array.splice(index, 1);
    }
    if(new_participant_array.length < 2) {
      jQuery("#postane_new_participant_list").css("display","none");
    }
    dis.remove();
  });

  jQuery("#postane_new_participants_send").click(function(){
    var thread_id = jQuery("#postane_messages_title").attr("data-thread-id");
    var participants = [];
    jQuery(".postane_new_existing_participant").each(function(){
      participants.push(jQuery(this).html());
    });

    var data = {
      'action' : 'postane',
      'postane_action' : 'add_participants',
      'postane_participants' : participants,
      'postane_thread_id' : thread_id
    };

    jQuery.post(ajaxurl, data, function(data) {
      var result = JSON.parse(data);
      if(typeof result["success"] != 'undefined') {
        jQuery("#postane_new_participant_list").html('');
        new_participant_array = [''];
        jQuery("#postane_add_participant_container").slideUp(300);
        postane_announce_success();
      } else {
        postane_announce_error(result["error"]);
      }
    });
  });


  jQuery("div").delegate(".postane_thread_quit_thread","click", function(e) {
    e.stopPropagation();
    var dis = jQuery(this);
    var thread_id = dis.attr("data-thread-id");

    var data = {
      'action' : 'postane',
      'postane_action' : 'quit_thread',
      'postane_thread_id' : thread_id
    };

    jQuery.post(ajaxurl, data, function(data) {
      result = JSON.parse(data);
      if(typeof result["success"] != 'undefined') {
        dis.parents(".postane_thread").fadeOut(300, function() {
          jQuery(this).remove();
        });
      } else {
        postane_announce_error(result["error"]);
      }
    });
    return false;
  });


  jQuery("div").delegate(".postane_thread_remove_messages", "click", function(e) {
    e.stopPropagation();
    var dis = jQuery(this);
    var thread_id = dis.attr("data-thread-id");

    var data = {
      'action' : 'postane',
      'postane_action' : 'delete_all_messages',
      'postane_thread_id' : thread_id
    }

    jQuery.post(ajaxurl, data, function(data) {
      postane_announce_success();
    });
  });


  jQuery("div").delegate(".postane_messages_delete_button", "click", function() {
    var dis = jQuery(this);
    var message_id = dis.attr("data-message-id");

    var data = {
      'action' : 'postane',
      'postane_action' : 'delete_message',
      'postane_message_id' : message_id
    };

    jQuery.post(ajaxurl, data, function(data) {
      var result = JSON.parse(data);
      if(typeof result["success"] != 'undefined') {
        dis.parents(".postane_message").fadeOut(500, function() {
          jQuery(this).remove();
        });
      } else {
        postane_announce_error(result["error"]);
      }
    });
    return false;
  });

  jQuery("div").delegate(".postane_thread_mark_as_read", "click", function(e) {
    e.stopPropagation();
    var dis = jQuery(this);
    var thread_id = dis.attr("data-thread-id");
    var data = {
      'action' : 'postane',
      'postane_action' : 'mark_thread_read',
      'postane_thread_id' : thread_id
    };

    jQuery.post(ajaxurl, data, function(data) {
      var result = JSON.parse(data);
      if(typeof result["success"] != 'undefined') {
        dis.parents(".postane_thread").removeClass("postane_thread_unread");
        postane_announce_success();
      } else {
        postane_announce_error(result["error"]);
      }
    });
  });

  jQuery("#postane_email_checkbox").change(function() {
    var dis = jQuery(this);
    var checked = dis.prop("checked");
    var thread_id = jQuery("#postane_messages_title").attr("data-thread-id");

    var data = null;

    if(checked) {
      var data = {
        'action' : 'postane',
        'postane_action' : 'send_email',
        'postane_thread_id' : thread_id
      };
    } else {
      var data = {
        'action' : 'postane',
        'postane_action' : 'unsend_email',
        'postane_thread_id' : thread_id
      }
    }

    jQuery.post(ajaxurl, data, function(data) {
    });
  });

  function insertTextAtCursor(text) {
    var sel, range, html;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode( document.createTextNode(text) );
        }
    } else if (document.selection && document.selection.createRange) {
        document.selection.createRange().text = text;
    }
  }

  jQuery("[contenteditable]").on('paste', function(e) {
    e.preventDefault();
    insertTextAtCursor(e.originalEvent.clipboardData.getData('text'));
  });

  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

  var t_id = getParameterByName('postane_thread_id');
  if(t_id != '') {
    reset_message_screen();
    load_messages(parseInt(t_id));
    jQuery("#postane_threads").fadeOut(50, function(){
      jQuery("#postane_messages").fadeIn(50, function(){
        message_container_scroll_to_bottom();
        jQuery("#postane_back").show();
      });
    });
  }

  jQuery("div").delegate(".postane_threads_username", "click", function(e) {
    e.stopPropagation();
  });

  jQuery("#postane_loading").click(function(){
    jQuery("#postane_back").click();
  });
});
