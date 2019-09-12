<?php global $form; ?>
<div class="wrap">
    <div class="factory-bootstrap-422 factory-fontawesome-000">
        <h3><?php _e( 'Settings', 'insert-php' ) ?></h3>
        <div class="row">
            <div class="col-md-9">
                <form method="post" class="form-horizontal">
					<?php if ( ! empty( $wbcr_saved ) ) { ?>
                        <div id="message" class="alert alert-success">
                            <p><?php _e( 'The settings have been updated successfully!', 'insert-php' ) ?></p>
                        </div>
					<?php } ?>
                    <div style="padding-top: 10px;">
						<?php $form->html(); ?>
                    </div>
                    <div class="form-group form-horizontal">
                        <label class="col-sm-2 control-label"> </label>
                        <div class="control-group controls col-sm-10">
							<?php wp_nonce_field( $this->plugin->getPrefix() . 'settings_form', $this->plugin->getPrefix() . 'nonce' ); ?>
                            <input name="<?php echo $this->plugin->getPrefix() . 'saved' ?>" class="btn btn-primary" type="submit" value="<?php _e( 'Save settings', 'insert-php' ) ?>"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <div id="wapt-dashboard-widget" class="wapt-right-widget">
                    <div style="padding:20px">
		                <?php WAPT_Plugin::app()->get_adverts_manager()->render_placement( 'right_sidebar'); ?>
                    </div>
                </div>
                <div id="wbcr-clr-support-widget" class="wbcr-factory-sidebar-widget">
                    <p><strong>Having Issues?</strong></p>
                    <div class="wbcr-clr-support-widget-body">
                        <p>
                            We provide free support for this plugin. If you are pushed with a problem, just create a new ticket. We will definitely help you!            </p>
                        <ul>
                            <li><span class="dashicons dashicons-sos"></span>
                                <a href="https://forum.webcraftic.com" target="_blank" rel="noopener">Get starting free support</a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>