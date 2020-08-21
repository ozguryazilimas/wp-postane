<?php
global $form;

$current_url = admin_url( 'admin.php?page=wapt_settings-' . WAPT_Plugin::app()->getPluginName() );
$current_tab = 'general';
$TABS        = array(
	'general'        => array(
		'current' => false,
		'caption' => 'General',
		'icon'    => 'icon-general',
		'url'     => $current_url . "&apt_tab=general",
	),
	'img_generation' => array(
		'current' => false,
		'caption' => 'Image generation',
		'icon'    => 'icon-image',
		'url'     => $current_url . "&apt_tab=img_generation",
	),
	'api'            => array(
		'current' => false,
		'caption' => 'API',
		'icon'    => 'icon-api',
		'url'     => $current_url . "&apt_tab=api",
	),
);
if ( isset( $_GET['apt_tab'] ) && ! empty( $_GET['apt_tab'] ) ) {
	$current_tab                     = htmlspecialchars( $_GET['apt_tab'] );
	$current_url                     .= "&apt_tab={$current_tab}";
	$TABS[ $current_tab ]['current'] = true;
} else {
	$current_tab                     = 'general';
	$current_url                     .= "&apt_tab={$current_tab}";
	$TABS[ $current_tab ]['current'] = true;
}
?>
<div class="wapt-container">
    <div class="wapt-page-title">
        <h1><?php _e( 'Settings of', 'apt' ) ?>&nbsp;<?php echo WAPT_Plugin::app()->getPluginTitle() . " " . WAPT_Plugin::app()->getPluginVersion(); ?></h1>
    </div>
    <div id="tabs" class="tabs">
        <nav>
            <ul>
				<?php
				foreach ( $TABS as $key => $tab ) {
					if ( $tab['current'] ) {
						echo "<li class='tab-current'>";
					} else {
						echo "<li>";
					}
					echo "<a href='{$tab['url']}' class='{$tab['icon']}'><span>{$tab['caption']}</span></a>";
					echo "</li>";
				}
				?>
            </ul>
        </nav>
        <div class="content">
            <section id="section-<?php echo $current_tab; ?>">
                <div class="wrap">
                    <div class="factory-bootstrap-433 factory-fontawesome-000">
                        <div class="row">
                            <div class="col-md-8">
                                <form method="post" class="form-horizontal">
									<?php if ( ! empty( $wbcr_saved ) ) { ?>
                                        <div id="message" class="alert alert-success">
                                            <p><?php _e( 'The settings have been updated successfully!', 'insert-php' ) ?></p>
                                        </div>
									<?php } ?>
                                    <div>
										<?php $form->html(); ?>
                                    </div>
                                    <div class="form-group form-horizontal">

                                        <div class="control-group controls col-sm-10">
											<?php wp_nonce_field( $this->plugin->getPrefix() . 'settings_form', $this->plugin->getPrefix() . 'nonce' ); ?>
                                            <input name="<?php echo $this->plugin->getPrefix() . 'saved' ?>"
                                                   class="btn btn-primary" type="submit"
                                                   value="<?php _e( 'Save settings', 'insert-php' ) ?>"/>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
								<?php if ( $current_tab == 'img_generation' ) { ?>
                                    <div id="wapt-image-preview" class="wapt-image-preview">
                                        <div class="wapt-image-preview-title"><h3>Post thumbnail preview</h3></div>
										<?php
										$format = WAPT_Plugin::app()->getOption( "image-type", "jpg" );
										switch ( $format ) {
											case 'png':
												$format = 'png';
												break;
											case 'jpg':
											case 'jpeg':
											default:
												$format = 'jpg';
												break;
										}

										$posts = get_posts( array( 'numberposts' => 0, ) );
										$id    = rand( 0, count( $posts ) - 1 );
										if ( count( $posts ) !== 0 ) {
											$txt = $posts[ $id ]->post_title;
										} else {
											$txt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas aliquet turpis quis ex elementum malesuada';
										}

										$image = apply_filters( 'wapt/generate/image', AutoPostThumbnails::generate_image_with_text( $txt ), $txt );

										$image->save( WAPT_PLUGIN_DIR . "/preview.{$format}", 100, $format );
										?>
                                        <img src="<?php echo WAPT_PLUGIN_URL . "/preview.{$format}?" . time(); ?>"
                                             width="100%" alt="">
                                    </div>
								<?php } ?>

								<?php if ( ! WAPT_Plugin::app()->is_premium() ) { ?>
                                    <div id="wapt-dashboard-widget" class="wapt-right-widget">
                                        <div style="padding:20px">
											<?php WAPT_Plugin::app()->get_adverts_manager()->render_placement( 'right_sidebar' ); ?>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div><!-- /content -->
    </div><!-- /tabs -->
    <div class="row wapt-footer">
        <div class="col-md-6">
            <div id="wbcr-clr-support-widget" class="wbcr-factory-sidebar-widget">
                <p><strong>Having Issues?</strong></p>
                <div class="wbcr-clr-support-widget-body">
                    <p>
                        We provide free support for this plugin. If you are pushed with a problem, just create a new
                        ticket. We will definitely help you! </p>
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