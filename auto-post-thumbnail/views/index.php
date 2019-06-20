<?php
global $wpdb;
?>
<div class="wrap">

	<h1><?php esc_html_e( 'Generate Post Thumbnails', 'apt' ); ?></h1>

	<div>
		<?php
		$tabs = [
			'main'     => [
				'label' => esc_html__( 'Generate Thumbnails', 'apt' ),
				'link'  => admin_url( 'options-general.php?page=generate-post-thumbnails&tab=main' ),
			],
			'custom'   => [
				'label' => esc_html__( 'Custom generation', 'apt' ),
				'link'  => admin_url( 'options-general.php?page=generate-post-thumbnails&tab=custom' ),
			],
			'settings' => [
				'label' => esc_html__( 'Settings', 'apt' ),
				'link'  => admin_url( 'options-general.php?page=generate-post-thumbnails&tab=settings' ),
			],
			'about'    => [
				'label' => esc_html__( 'About', 'apt' ),
				'link'  => admin_url( 'options-general.php?page=generate-post-thumbnails&tab=about' ),
			],
		];

		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'main';

		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = 'main';
		}
		?>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<a class="nav-tab <?php echo( $key === $active_tab ? 'nav-tab-active' : '' ); ?>"
					href="<?php echo $tab['link']; ?>"><?php echo $tab['label']; ?></a>
			<?php endforeach; ?>
		</h2>

		<div style="width: 100%;">
			<?php
			$content = APT_Template::render( 'tab-' . $active_tab );
			echo $content;
			?>
		</div>
	</div>
</div>
