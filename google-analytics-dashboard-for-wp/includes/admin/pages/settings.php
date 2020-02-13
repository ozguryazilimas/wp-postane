<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Callback to output the ExactMetrics settings page.
 *
 * @since 7.4.0
 * @access public
 *
 * @return void
 */
function exactmetrics_settings_page() {
	echo exactmetrics_ublock_notice();
	exactmetrics_settings_error_page( 'exactmetrics-vue-site-settings' );
	exactmetrics_settings_inline_js();
}

function exactmetrics_network_page() {
    echo exactmetrics_ublock_notice();
    exactmetrics_settings_error_page( 'exactmetrics-vue-network-settings' );
    exactmetrics_settings_inline_js();
}

/**
 * Attempt to catch the js error preventing the Vue app from loading and displaying that message for better support.
 */
function exactmetrics_settings_inline_js() {
	?>
	<script type="text/javascript">
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf( 'MSIE ' );
		if ( msie > 0 ) {
			var browser_error = document.getElementById( 'exactmetrics-error-browser' );
			var js_error = document.getElementById( 'exactmetrics-error-js' );
			js_error.style.display = 'none';
			browser_error.style.display = 'block';
		} else {
			window.onerror = function myErrorHandler( errorMsg, url, lineNumber ) {
                /* Don't try to put error in container that no longer exists post-vue loading */
				var message_container = document.getElementById( 'exactmetrics-nojs-error-message' );
                if ( ! message_container ) {
                    return false;
                }
				var message = document.getElementById( 'exactmetrics-alert-message' );
				message.innerHTML = errorMsg;
				message_container.style.display = 'block';
				return false;
			}
		}
	</script>
	<?php
}


/**
  * Error page HTML
**/
function exactmetrics_settings_error_page( $id = 'exactmetrics-vue-site-settings', $footer = '', $margin = '82px 0' ) {
    $inline_logo_image = 'data:image/png;base64,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';
    ?>
    <style type="text/css">
        #exactmetrics-settings-area {
          visibility: hidden;
          animation: loadExactMetricsSettingsNoJSView 0s 2s forwards;
        }

        @keyframes loadExactMetricsSettingsNoJSView{
          to   { visibility: visible; }
        }
    </style>
    <!--[if IE]>
        <style>
            #exactmetrics-settings-area{
                visibility: visible !important;
            }
        </style>
    <![endif]-->
    <div id="<?php echo $id; ?>">
        <div id="exactmetrics-settings-area" class="exactmetrics-settings-area mi-container" style="font-family:'Helvetica Neue', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, 'Lucida Grande', sans-serif;margin: auto;width: 750px;max-width: 100%;">
            <div id="exactmetrics-settings-error-loading-area">
                <div class="" style="text-align: center; background-color: #fff;border: 1px solid #D6E2EC; padding: 15px 50px 30px; color: #777777; margin: <?php echo esc_attr( $margin ); ?>">
                    <div class="" style="border-bottom: 0;padding: 5px 20px 0;">
                        <img class="" src="<?php echo esc_attr( $inline_logo_image ); ?>" alt="" style="max-width: 100%;width: 240px;padding: 30px 0 15px;">
                    </div>
                    <div id="exactmetrics-error-js">
                        <h3 class="" style="font-size: 20px;color: #434343;font-weight: 500;line-height:1.4;"><?php esc_html_e( 'Ooops! It Appears JavaScript Didn’t Load', 'google-analytics-dashboard-for-wp' ); ?></h3>
                        <p class="info" style="line-height: 1.5;margin: 1em 0;font-size: 16px;color: #434343;padding: 5px 20px 20px;"><?php esc_html_e( 'There seems to be an issue running JavaScript on your website, which ExactMetrics is crafted in to give you the best experience possible.', 'google-analytics-dashboard-for-wp' ); ?></p>
						<p class="info"style="line-height: 1.5;margin: 1em 0;font-size: 16px;color: #434343;padding: 5px 20px 20px;">
							<?php
							// Translators: Placeholders make the text bold.
							printf( esc_html__( 'If you are using an %1$sad blocker%2$s, please disable or whitelist the current page to load ExactMetrics correctly.', 'google-analytics-dashboard-for-wp' ), '<strong>', '</strong>' );
							?>
						</p>
                        <div style="display: none" id="exactmetrics-nojs-error-message">
                            <div class="" style="  border: 1px solid #E75066;
                                                                border-left: 3px solid #E75066;
                                                                background-color: #FEF8F9;
                                                                color: #E75066;
                                                                font-size: 14px;
                                                                padding: 18px 18px 18px 21px;
                                                                font-weight: 300;
                                                                text-align: left;">
                                <strong style="font-weight: 500;" id="exactmetrics-alert-message"></strong>
                            </div>
                            <p class="" style="font-size: 14px;color: #777777;padding-bottom: 15px;"><?php esc_html_e( 'Copy the error message above and paste it in a message to the ExactMetrics support team.', 'google-analytics-dashboard-for-wp' ); ?></p>
                        </div>
                        <a href="https://www.exactmetrics.com/docs/fix-javascript-error" target="_blank" style="margin-left: auto;background-color: #6528F5;border-color: #6528F5;border-bottom-width: 2px;color: #fff;border-radius: 3px;font-weight: 500;transition: all 0.1s ease-in-out;transition-duration: 0.2s;padding: 14px 35px;font-size: 16px;margin-top: 10px;margin-bottom: 20px; text-decoration: none; display: inline-block;">
                            <?php esc_html_e( 'Resolve This Issue', 'google-analytics-dashboard-for-wp' ); ?>
                        </a>
                    </div>
                    <div id="exactmetrics-error-browser" style="display: none">
                        <h3 class="" style="font-size: 20px;color: #434343;font-weight: 500;"><?php esc_html_e( 'Your browser version is not supported', 'google-analytics-dashboard-for-wp' ); ?></h3>
                        <p class="info" style="line-height: 1.5;margin: 1em 0;font-size: 16px;color: #434343;padding: 5px 20px 20px;"><?php esc_html_e( 'You are using a browser which is no longer supported by ExactMetrics. Please update or use another browser in order to access the plugin settings.', 'google-analytics-dashboard-for-wp' ); ?></p>
                        <a href="https://www.exactmetrics.com/docs/browser-support-policy/" target="_blank" style="margin-left: auto;background-color: #6528F5;border-color: #6528F5;border-bottom-width: 2px;color: #fff;border-radius: 3px;font-weight: 500;transition: all 0.1s ease-in-out;transition-duration: 0.2s;padding: 14px 35px;font-size: 16px;margin-top: 10px;margin-bottom: 20px; text-decoration: none; display: inline-block;">
                            <?php esc_html_e( 'View supported browsers', 'google-analytics-dashboard-for-wp' ); ?>
                        </a>
                    </div>
                </div>
            </div>
			<div style="text-align: center;">
				<?php echo wp_kses_post( $footer ); ?>
			</div>
        </div>
    </div>
    <?php
}
