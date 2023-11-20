<?php

class YARPP_Meta_Box_Relatedness extends YARPP_Meta_Box {
	public function display() {
		global $yarpp;
		?>
		<p><?php _e( 'YARPP limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.', 'yet-another-related-posts-plugin' ); ?> <span class='yarpp_help dashicons dashicons-editor-help' data-help="<?php echo esc_attr( __( 'The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshold, take a look at some post\'s related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.', 'yet-another-related-posts-plugin' ) ); ?>">&nbsp;</span></p>

		<?php
		$this->textbox( 'threshold', __( 'Match threshold:', 'yet-another-related-posts-plugin' ) );
		$this->disabled_warning();
		$this->weight( 'title', __( 'Titles: ', 'yet-another-related-posts-plugin' ) );
		$this->weight( 'body', __( 'Bodies: ', 'yet-another-related-posts-plugin' ) );

		foreach ( $yarpp->get_taxonomies() as $taxonomy ) {
			$this->tax_weight( $taxonomy );
		}
	}

	/**
	 * If applicable, echos out a warning that the fulltext indexes don't exist and so comparing using titles and bodies
	 * must be disabled until some database changes happen.
	 */
	protected function disabled_warning() {
		global $yarpp, $wpdb;
		$database_supports_fulltext_indexes = $yarpp->db_schema->database_supports_fulltext_indexes();
		if ( ! $database_supports_fulltext_indexes ) {
			?>
			<div class='yarpp-callout yarpp-notice'>
				<p>
				<?php
					esc_html_e( 'Comparing posts based on Titles or Bodies is currently disabled', 'yet-another-related-posts-plugin' );
				?>
					&nbsp;&nbsp;<a href="#" id="yarpp_fulltext_expand">
					<?php
						printf(
						// translators: icon to expand
							__( 'Show Details %s', 'yet-another-related-posts-plugin' ),
							'[+]'
						);
					?>
					</a>
				</p>
				<div id="yarpp_fulltext_details" class="hidden">
					<p>
					<?php
						printf(
							esc_html__( 'Because full-text indexing is not supported by your current table engine, "%1$s", YARPP cannot compare posts based on their Titles or Bodies.', 'yet-another-related-posts-plugin' ),
							'InnoDB',
							'5.6.4',
							'<code>' . $wpdb->posts . '</code>',
							'MyISAM'
						);
					?>
					</p>
					<p>
					<?php
						printf(
							esc_html__( 'Please contact your host about updating MySQL to at latest version %1$s, or run the following SQL code on your MySQL client (eg PHPMyAdmin) or terminal:', 'yet-another-related-posts-plugin' ),
							'5.6.4'
						);
					?>
					</p>
					<p>
						<span class="dashicons <?php echo ( $yarpp->db_schema->content_column_has_index() === true ) ? 'dashicons-yes' : 'dashicons-clock'; ?>"></span>
						<code>
							ALTER TABLE <?php echo $wpdb->posts; ?> ADD FULLTEXT `yarpp_content` (`post_content`);
						</code>
						<br/>
						<span class="dashicons <?php echo ( $yarpp->db_schema->title_column_has_index() === true ) ? 'dashicons-yes' : 'dashicons-clock'; ?>"></span>
						<code>
							ALTER TABLE <?php echo $wpdb->posts; ?> ADD FULLTEXT `yarpp_title` (`post_title`);
						</code>
					</p>
					<p>
						<?php
						printf(
							esc_html__( 'See MySQL %1$sstorage engines%2$s documentation for details on MySQL engines.', 'yet-another-related-posts-plugin' ),
							'<a href="https://dev.mysql.com/doc/refman/8.0/en/storage-engines.html" target="_blank">',
							'</a>'
						);
						?>
					</p>
				</div>
			</div>
			<?php
		} elseif ( $yarpp->diagnostic_big_db() && ( ! $yarpp->db_schema->title_column_has_index() || ! $yarpp->db_schema->content_column_has_index() ) ) {
			// it's a big database. So while we *can* automatically add indexes, we need to warn the site owner.
			?>
			<div class='yarpp-callout yarpp-notice'>
				<p><strong><?php esc_html_e( 'Enabling comparisons using Titles or Bodies requires adding "fulltext indexes" to the posts table.', 'yet-another-related-posts-plugin' ); ?></strong>
				<a href="#" id="yarpp_fulltext_expand">
				<?php
					printf(
					// translators: icon to expand
						__( 'Show Details %s', 'yet-another-related-posts-plugin' ),
						'[+]'
					);
				?>
				</a>
			</p>
				<div id="yarpp_fulltext_details" class="hidden">
					<p><?php esc_html_e( '"Fulltext indexes" will improve YARPP’s algorithm but may affect performance.', 'yet-another-related-posts-plugin' ); ?></p>
					<p><?php esc_html_e( 'You have a large database and so adding them may take several minutes and cause the website to become unresponsive during this time. We recommend performing this action during off-peak hours.', 'yet-another-related-posts-plugin' ); ?></p>
					<p><?php esc_html_e( 'Please make a database backup before attempting this, and consider adding the indexes manually by running the following queries:', 'yet-another-related-posts-plugin' ); ?></p>
					<p>
						<span class="dashicons <?php echo ( $yarpp->db_schema->content_column_has_index() === true ) ? 'dashicons-yes' : 'dashicons-clock'; ?>"></span>
						<code>
							ALTER TABLE <?php echo $wpdb->posts; ?> ADD FULLTEXT `yarpp_content` (`post_content`);
						</code>
						<br/>
						<span class="dashicons <?php echo ( $yarpp->db_schema->title_column_has_index() === true ) ? 'dashicons-yes' : 'dashicons-clock'; ?>"></span>
						<code>
							ALTER TABLE <?php echo $wpdb->posts; ?> ADD FULLTEXT `yarpp_title` (`post_title`);
						</code>
					</p>
				</div>
			</div>
			<?php
		}
	}
}
