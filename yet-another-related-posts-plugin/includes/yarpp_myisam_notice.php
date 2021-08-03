<?php
/**
 * @var $yarpp YARPP
 */

if ( $yarpp->db_options->has_fulltext_db_error() ) {
	?>
	<div class="notice notice-error" >
		<span class="yarpp-red"><?php esc_html_e( 'Full-text Index creation did not work!', 'yet-another-related-posts-plugin' ); ?></span><br/>
		<?php
			printf(
				esc_html__( 'There was an error adding the full-text index to your posts table: %s', 'yet-another-related-posts-plugin' ),
				$yarpp->db_options->get_fulltext_db_error()
			);
			$yarpp->db_options->delete_fulltext_db_error_record();
		?>
			<br/>
		<?php esc_html_e( 'Titles and bodies still cannot be used as relatedness criteria.', 'yet-another-related-posts-plugin' ); ?>
	</div>
	<?php
}
