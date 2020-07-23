<?php
if ( ! WAPT_Plugin::app()->premium->is_activate() ) {
	echo __( "This service available in the PRO version" );
	?>
    <a href="<?php echo WAPT_Plugin::app()->get_support()->get_pricing_url( true, 'license_page' ); ?>"
       class="purchase-premium" target="_blank" rel="noopener">
    <span class="btn btn-gold">
    <?php printf( __( 'Upgrade to Premium', 'insert-php' ), WAPT_Plugin::app()->premium->get_price() ) ?>
    </span><br>
    </a>
	<?php
}
if ( empty( $slug ) && WAPT_Plugin::app()->premium->is_activate() ) {
	echo __( "You have activated a premium license, but not install premium add-on to use pro features now." );
}

?>