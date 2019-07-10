jQuery(document).ready(function($) {
  var loaded_metabox = false;
  var display = $('#yarpp-related-posts');
  
  /*
  * Populates Metabox initially
  */
  function yarpp_metabox_initial_display() {
    if (!$('#yarpp_relatedposts') ||
        !display.length ||
        !$('#post_ID').val() )
      return;

    if (!loaded_metabox) {
      loaded_metabox = true;
      yarpp_metabox_populate();
    }
  }
  
  /*
  * Populates Metabox
  */
  function yarpp_metabox_populate() {
    $.ajax({
      type:'POST',
      url: ajaxurl,
      data: {
        action: 'yarpp_display',
        domain: 'metabox',
        ID: parseInt($('#post_ID').val()),
        '_ajax_nonce': $('#yarpp_display-nonce').val()
      },
      error: function() {
        display.html("Error");
      },
      success: function(html){
        display.html(html)},
        dataType: 'html'
      }
    );
  }

  $('#yarpp_relatedposts .handlediv, #yarpp_relatedposts-hide').click(function() {
    setTimeout(yarpp_metabox_initial_display, 0);
  });
  
  /*
  * Metabox Refresh Button
  */
  $(document).on('click', '#yarpp-refresh', function(e) {
    e.preventDefault();
  
    var display = $('#yarpp-related-posts');
  
    if( $(this).hasClass('disabled') )
      return false;

    $refresh_button = $(this);
    $spinner = $refresh_button.siblings('.spinner');

    $refresh_button.addClass( 'disabled' );
    $spinner.css( 'visibility', 'visible' );

    $('#yarpp-list').css( 'opacity', 0.6 );
    yarpp_metabox_populate();
  });
  
  /*
  * Initial Load
  */
  yarpp_metabox_initial_display();
  
});