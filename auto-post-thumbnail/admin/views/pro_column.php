<?php
echo esc_html__( 'Thumbnails of posts available in the PRO version' );
?>
<a href="<?php echo esc_url_raw( WAPT_Plugin::app()->get_support()->get_pricing_url( true, 'license_page' ) ); ?>"
   class="purchase-premium" target="_blank" rel="noopener">
<span class="btn btn-gold">
<?php printf( esc_html__( 'Upgrade to Premium', 'insert-php' ), esc_html( WAPT_Plugin::app()->premium->get_price() ) ); ?>
</span>
</a>

