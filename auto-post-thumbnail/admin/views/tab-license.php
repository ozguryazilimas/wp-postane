<div class="wrap">
	<div class="factory-bootstrap-467 factory-fontawesome-000">
		<?php wp_nonce_field( 'license' ); ?>
		<div id="wapt-license-wrapper"
		     data-loader="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/loader.gif' ); ?>"
		     data-plugin="<?php echo esc_attr( get_class( $this->plugin ) ); ?>">

			<div class="factory-bootstrap-413 onp-page-wrap <?php echo esc_attr( $this->get_license_type() ); ?>-license-manager-content"
			     id="license-manager">
				<div>
					<h3><?php printf( esc_html__( 'Activate %s', 'apt' ), esc_html( $this->plan_name ) ); ?></h3>
					<?php echo esc_html( $this->get_plan_description() ); ?>
				</div>
				<br>

				<div class="onp-container">
					<div class="license-details">
						<?php if ( $this->get_license_type() === 'free' ) : ?>
							<a href="<?php echo esc_url_raw( $this->plugin->get_support()->get_pricing_url( true, 'license_page' ) ); ?>"
							   class="purchase-premium" target="_blank" rel="noopener">
                            <span class="btn btn-gold btn-inner-wrap">
                            <?php printf( esc_html__( 'Upgrade to Premium', 'apt' ), esc_html( $this->premium->get_price() ) ); ?>
                            </span>
							</a>
							<p><?php printf( esc_html__( 'Your current license for %1$s:', 'apt' ), esc_html( $this->plugin->getPluginTitle() ) ); ?></p>
						<?php endif; ?>
						<div class="license-details-block <?php echo esc_html( $this->get_license_type() ); ?>-details-block">
							<?php if ( $this->is_premium ) : ?>
								<a data-action="deactivate" href="#"
								   class="btn btn-default btn-small license-delete-button wapt-control-btn">
									<?php esc_html_e( 'Delete Key', 'apt' ); ?>
								</a>
								<a data-action="sync" href="#"
								   class="btn btn-default btn-small license-synchronization-button wapt-control-btn">
									<?php esc_html_e( 'Synchronization', 'apt' ); ?>
								</a>
							<?php endif; ?>
							<h3>
								<?php echo esc_html( ucfirst( $this->get_plan() ) ); ?>

								<?php if ( $this->is_premium && $this->premium_has_subscription ) : ?>
									<span style="font-size: 15px;">
                                    (<?php printf( esc_html__( 'Automatic renewal, every %s', '' ), esc_attr( $this->get_billing_cycle_readable() ) ); ?>
                                                )
                                </span>
								<?php endif; ?>
							</h3>
							<?php if ( $this->is_premium ) : ?>
								<div class="license-key-identity">
									<code><?php echo esc_attr( $this->get_hidden_license_key() ); ?></code>
								</div>
							<?php endif; ?>
							<div class="license-key-description">
								<?php if ( ! $this->is_premium ) : ?>
									<p><?php esc_html_e( 'Public License is a GPLv3 compatible license allowing you to change and use this version of the plugin for free. Please keep in mind this license covers only free edition of the plugin. Premium versions are distributed with other type of a license.', 'apt' ); ?></p>
								<?php else : ?>
									<p><?php esc_html_e( 'Сommercial license, only to the premium add-on to this free plugin. You cannot distribute or modify the premium add-on. But free plugin is a GPLv3 compatible license allowing you to change and use this version of the plugin for free.', 'apt' ); ?></p>
								<?php endif; ?>
								<?php if ( $this->is_premium && $this->premium_has_subscription ) : ?>
									<p class="activate-trial-hint">
										<?php esc_html_e( 'You use a paid subscription for the plugin updates. In case you don’t want to receive paid updates, please, click <a data-action="unsubscribe" class="wapt-control-btn" href="#">cancel subscription</a>', 'apt' ); ?>
									</p>
								<?php endif; ?>

								<?php if ( $this->get_license_type() === 'trial' ) : ?>
									<p class="activate-error-hint">
										<?php printf( esc_html__( 'Your license has expired, please extend the license to get updates and support.', 'apt' ), '' ); ?>
									</p>
								<?php endif; ?>
							</div>
							<table class="license-params" colspacing="0" colpadding="0">
								<tr>
									<!--<td class="license-param license-param-domain">
										<span class="license-value"><?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?></span>
										<span class="license-value-name"><?php esc_html_e( 'domain', 'apt' ); ?></span>
									</td>-->
									<td class="license-param license-param-days">
										<span class="license-value"><?php echo esc_html( $this->get_plan() ); ?></span>
										<span class="license-value-name"><?php esc_html_e( 'plan', 'apt' ); ?></span>
									</td>
									<?php if ( $this->is_premium ) : ?>
										<td class="license-param license-param-sites">
                                        <span class="license-value">
                                            <?php echo esc_attr( $this->premium_license->get_count_active_sites() ); ?>
                                            <?php esc_html_e( 'of', 'apt' ); ?>
                                            <?php echo esc_attr( $this->premium_license->get_sites_quota() ); ?></span>
											<span class="license-value-name"><?php esc_html_e( 'active sites', 'apt' ); ?></span>
										</td>
									<?php endif; ?>
									<td class="license-param license-param-version">
										<span class="license-value"><?php echo esc_html( $this->plugin->getPluginVersion() ); ?></span>
										<span class="license-value-name"><span><?php esc_html_e( 'version', 'apt' ); ?></span></span>
									</td>
									<?php if ( $this->is_premium ) : ?>
										<td class="license-param license-param-days">
											<?php if ( $this->get_license_type() === 'trial' ) : ?>
												<span class="license-value"><?php esc_html_e( 'EXPIRED!', 'apt' ); ?></span>
												<span class="license-value-name"><?php esc_html_e( 'please update the key', 'apt' ); ?></span>
											<?php else : ?>
												<span class="license-value">
													<?php
													if ( $this->premium_license->is_lifetime() ) {
														echo 'infiniate';
													} else {
														echo esc_html( $this->get_expiration_days() );
													}
													?>
                                                            <small> <?php esc_html_e( 'day(s)', 'apt' ); ?></small>
                                             </span>
												<span class="license-value-name"><?php esc_html_e( 'remained', 'apt' ); ?></span>
											<?php endif; ?>
										</td>
									<?php endif; ?>
								</tr>
							</table>
						</div>
					</div>
					<div class="license-input">
						<form action="" method="post">
							<?php if ( $this->is_premium ) : ?>
						<p><?php esc_html_e( 'Have a key to activate the premium version? Paste it here:', 'apt' ); ?><p>
						<?php else : ?>
							<p><?php esc_html_e( 'Have a key to activate the plugin? Paste it here:', 'apt' ); ?>
							<p>
								<?php endif; ?>
							<div class="license-key-wrap">
								<input type="text" id="license-key" name="licensekey" value=""
								       class="form-control"/>
								<button data-action="activate" class="btn btn-default wapt-control-btn"
								        type="button"
								        id="license-submit">
									<?php esc_html_e( 'Submit Key', 'apt' ); ?>
								</button>
							</div>
							<?php if ( $this->is_premium ) : ?>
								<p style="margin-top: 10px;">
									<?php printf( wp_kses_post( '<a href="%s" target="_blank" rel="noopener">Lean more</a> about the premium version and get the license key to activate it now!', 'apt' ), esc_url_raw( $this->plugin->get_support()->get_pricing_url( true, 'license_page' ) ) ); ?>
								</p>
							<?php else : ?>
								<p style="margin-top: 10px;">
									<?php printf( wp_kses_post( 'Can’t find your key? Go to <a href="%s" target="_blank" rel="noopener">this page</a> and login using the e-mail address associated with your purchase.', 'apt' ), 'https://users.freemius.com/password/recover' ); ?>
								</p>
							<?php endif; ?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
