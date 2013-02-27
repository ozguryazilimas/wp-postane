//The ajax to add menu items

 jQuery(document).ready(function($) {
      $('#submit-post-type-buddypress').click(function(event){
           event.preventDefault();
           
           $('#add-buddypress img.waiting').show();

           /* Get checked boxes */
           var postTypes = [];
           $('#add-buddypress li :checked').each(function() {
                postTypes.push($(this).val());
           });

           /* Send checked post types with our action, and nonce */
           $.post( ajaxurl, {
                     action: "my-add-post-type-buddypress-links",
                     posttypebuddypress_nonce: my_posttype_bp_links.nonce,
                     post_types: postTypes,
                },

                /* AJAX returns html to add to the menu */
                function( response ) {
                
                $('input:checkbox').attr('checked', false);
                $('#add-buddypress img.waiting').hide();
                $('#menu-to-edit').append(response);

                }

           );
      })
 });
