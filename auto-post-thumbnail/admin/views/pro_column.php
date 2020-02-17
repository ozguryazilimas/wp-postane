<?php
echo __( "Thumbnails of posts available in the PRO version" );
?>
<a href="<?php echo WAPT_Plugin::app()->get_support()->get_pricing_url( true, 'license_page' ); ?>"
   class="purchase-premium" target="_blank" rel="noopener">
<span class="btn btn-gold">
<?php printf( __( 'Upgrade to Premium', 'insert-php' ), WAPT_Plugin::app()->premium->get_price() ) ?>
</span>
</a>

