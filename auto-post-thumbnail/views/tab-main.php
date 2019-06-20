<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap genpostthumbs">
	<?php
    global $wpdb;
	// If the button was clicked
	if ( ! empty( $_POST['generate-post-thumbnails'] ) ):

		// Form nonce check
		check_admin_referer( 'generate-post-thumbnails' );

		// Get id's of all the published posts for which post thumbnails does not exist.
		$query = auto_post_thumbnails()->get_posts_query();
		$posts = $wpdb->get_results( $query );

	if ( empty( $posts ) ):
		esc_html_e( 'Currently there are no published posts available to generate thumbnails.', 'apt' );
	else:
		esc_html_e( 'We are generating post thumbnails. Please be patient!', 'apt' );

		// Generate the list of IDs
		$ids = array();
		foreach ( $posts as $post ) {
			$ids[] = $post->ID;
		}
		$ids = implode( ',', $ids );

		$count = count( $posts );
		?>
        <noscript><p>
                <em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'apt' ) ?></em>
            </p></noscript>

        <div id="genpostthumbsbar" style="position:relative;height:25px;">
            <div id="genpostthumbsbar-percent"
                 style="position:absolute;left:50%;top:50%;width:50px;margin-left:-25px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
        </div>

        <script type="text/javascript">
            // <![CDATA[
            jQuery(document).ready(function ($) {
                var rt_images = [<?php echo $ids; ?>];
                var rt_total = rt_images.length;
                var rt_count = 1;
                var rt_percent = 0;

                $("#genpostthumbsbar").progressbar();
                $("#genpostthumbsbar-percent").html("0%");

                function genPostThumb(id) {
                    $.post("admin-ajax.php", {
                        action: "generatepostthumbnail",
                        id: id
                    }, function () {
                        rt_percent = (rt_count / rt_total) * 100;
                        $("#genpostthumbsbar").progressbar("value", rt_percent);
                        $("#genpostthumbsbar-percent").html(Math.round(rt_percent) + "%");
                        rt_count = rt_count + 1;

                        if (rt_images.length) {
                            genPostThumb(rt_images.shift());
                        } else {
                            $("#message").html("<p><strong><?php echo sprintf( esc_html__( 'All done! Processed posts: %d', 'apt' ), $count ); ?></strong></p>");
                            $("#message").show();
                        }
                    });
                }

                genPostThumb(rt_images.shift());
            });
            // ]]>
        </script>
	<?php
	endif;
	else:
	?>
        <p><?php esc_html_e( 'Use this tool to generate Post Thumbnail (Featured Thumbnail) for your Published posts.', 'apt' ) ?></p>

        <p><?php _e( 'If the script stops executing for any reason, just <strong>Reload</strong> the page and it will continue from where it stopped.', 'apt' ) ?></p>

        <form method="post" action="">
			<?php wp_nonce_field( 'generate-post-thumbnails' ) ?>


            <p><input type="submit" class="button hide-if-no-js" name="generate-post-thumbnails"
                      id="generate-post-thumbnails" value="<?php esc_attr_e( 'Generate Thumbnails', 'apt' ) ?>"/></p>

            <noscript>
                <p>
                    <em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'apt' ) ?></em>
                </p>
            </noscript>

        </form>
        <p><?php _e( 'Note: Thumbnails won\'t be generated for posts that already have post thumbnail or <strong><em>skip_post_thumb</em></strong> custom meta field.', 'apt' ) ?></p>
	<?php endif; ?>
</div>