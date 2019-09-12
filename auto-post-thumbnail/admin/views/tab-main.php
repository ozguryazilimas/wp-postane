<div class="wrap">
    <h2><?php esc_html_e( 'Generate Featured images for all of your published posts', 'apt' ) ?></h2>
    <div class="factory-bootstrap-422 factory-fontawesome-000">
        <div class="row">
            <div class="col-md-9">
                <div class="wrap genpostthumbs">
                    <p><?php _e( 'Note: Thumbnails won\'t be generated for posts that already have post thumbnail or <strong><em>skip_post_thumb</em></strong> custom meta field.', 'apt' ) ?></p>
                    <p>
                        <button class="button button-primary button-large hide-if-no-js" name="generate-post-thumbnails" id="generate-post-thumbnails">
							<?php esc_attr_e( 'Generate Featured images', 'apt' ) ?>
                        </button>
                    <div id="message" class="updated fade" style="display:none"></div>
                    </p>
                    <noscript><p>
                            <em><?php esc_html_e( 'You must enable Javascript in order to proceed!', 'apt' ) ?></em>
                        </p>
                    </noscript>
                    <div id="genpostthumbsbar" style="position:relative;height:25px;">
                        <div id="genpostthumbsbar-percent"
                             style="position:absolute;left:50%;top:50%;width:50px;margin-left:-25px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
                    </div>
                    <!-- esc_html_e( 'We are generating post thumbnails. Please be patient!', 'apt' ); -->
                    <script type="text/javascript">
						// <![CDATA[
						jQuery(document).ready(function($) {
							jQuery('#generate-post-thumbnails').on('click', function(event) {
								rt_images = [];

								$("#generate-post-thumbnails").hide();
								$("#genpostthumbsbar").progressbar();
								$("#genpostthumbsbar-percent").html("1%");

								$.post("admin-ajax.php", {
									action: "get-posts-ids",
									_ajax_nonce: '<?php echo wp_create_nonce( 'get-posts' ); ?>'
								}, function(ids) {
									rt_images = JSON.parse("[" + ids + "]");

									var rt_total = rt_images.length;
									var rt_count = 1;
									var rt_percent = 0;
									var posted_count = 0;

									function genPostThumb(id) {
										$.post("admin-ajax.php", {
											action: "generatepostthumbnail",
											id: id,
											_ajax_nonce: '<?php echo wp_create_nonce( 'generate-post-thumbnails' ); ?>'
										}, function(posted) {
											console.log(posted);
											if( Number(posted) !== 0 ) {
												posted_count++;
											}
											rt_percent = (rt_count / rt_total) * 100;
											$("#genpostthumbsbar").progressbar("value", rt_percent);
											$("#genpostthumbsbar-percent").html(Math.round(rt_percent) + "%");
											rt_count = rt_count + 1;

											if( rt_images.length ) {
												genPostThumb(rt_images.shift());
											} else {
												$("#genpostthumbsbar").hide();
												$("#message").html("<p><strong><?php echo esc_html__( 'All done! Processed posts:', 'apt' ); ?> " + rt_total + "<br><?php echo esc_html__( 'Set featured image in posts:', 'apt' ); ?> " + posted_count + "</strong></p>");
												$("#message").show();
											}
										});
									}

									genPostThumb(rt_images.shift());
								});
							});
						});
						// ]]>
                    </script>
                </div>
            </div>
            <div class="col-md-3">
                <div style="padding:20px">
					<?php WAPT_Plugin::app()->get_adverts_manager()->render_placement( 'right_sidebar' ); ?>
                </div>
                <div id="wbcr-clr-support-widget" class="wbcr-factory-sidebar-widget">
                    <p><strong>Having Issues?</strong></p>
                    <div class="wbcr-clr-support-widget-body">
                        <p>
                            We provide free support for this plugin. If you are pushed with a problem, just create a new
                            ticket.
                            We will definitely help you! </p>
                        <ul>
                            <li><span class="dashicons dashicons-sos"></span>
                                <a href="https://forum.webcraftic.com" target="_blank" rel="noopener">Get starting free
                                    support</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>