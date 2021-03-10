<?php

class YARPP_Meta_Box_Relatedness extends YARPP_Meta_Box {
    public function display() {
        global $yarpp;
        ?>
        <p><?php _e( 'YARPP limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.', 'yarpp' ); ?> <span class='yarpp_help dashicons dashicons-editor-help' data-help="<?php echo esc_attr( __( 'The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshold, take a look at some post\'s related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.', 'yarpp' ) ); ?>">&nbsp;</span></p>

        <?php
        $this->textbox( 'threshold', __( 'Match threshold:', 'yarpp' ) );
        $this->disabled_warning();
        $this->weight( 'title', __( "Titles: ", 'yarpp' ) );
        $this->weight( 'body', __( "Bodies: ", 'yarpp' ) );

        foreach ( $yarpp->get_taxonomies() as $taxonomy ) {
            $this->tax_weight( $taxonomy );
        }
    }

	/**
	 * If applicable, echos out a warning that the fulltext indexes don't exist and so comparing using titles and bodies
     * must be disabled until some database changes happen.
	 */
    protected function disabled_warning(){
        global $yarpp, $wpdb;
	    $database_supports_fulltext_indexes = $yarpp->db_schema->database_supports_fulltext_indexes();
	    if ( ! $database_supports_fulltext_indexes) $yarpp->disable_fulltext();
	    if ( !(bool) yarpp_get_option(YARPP_DB_Options::YARPP_MYISAM_OVERRIDE) && $yarpp->db_options->is_fulltext_disabled()) {
		    ?>
            <div class='yarpp-callout yarpp-notice'>
                <p><?php
				    esc_html_e('Comparing posts based on titles or bodies is currently disabled','yarpp');
				    ?>
                    &nbsp;&nbsp;<a href="#" id="yarpp_fulltext_expand"><?php
					    printf(
					    // translators: icon to expand
						    __('Show Details %s','yarpp'),
						    '[+]'
					    );
					    ?>
                    </a>
                </p>
                <div id="yarpp_fulltext_details" class="hidden">
				    <?php if ( $database_supports_fulltext_indexes ){ ?>
                        <p><?php esc_html_e('YARPP can automatically create "full-text indexes" to enable comparing posts based on titles and bodies. To do so, click the button below.');?></p>
				    <?php } else { ?>
                        <p><?php
						    printf(
							    esc_html__('Because full-text indexing is not supported by your current table engine, "%1$s", YARPP cannot compare posts based on their titles or bodies.','yarpp'),
							    'InnoDB',
							    '5.6.4',
							    '<code>' . $wpdb->posts . '</code>',
							    'MyISAM'
						    );
						    ?>
                        </p>
                        <p><?php
						    printf(
							    esc_html__('Please contact your host about updating MySQL to at latest version %1$s, or run the following SQL code on your MySQL client (eg PHPMyAdmin) or terminal:', 'yarpp'),
							    '5.6.4'
						    );
						    ?>
                        </p>
                        <code class="yarpp_separated">ALTER TABLE `<?php echo $wpdb->posts;?>` ENGINE = MyISAM;</code>
                        <p><?php
						    esc_html_e('After you have done that, click the button below to enable comparing titles and bodies using full-text indexes."','yarpp');
						    ?>
                        </p>
				    <?php } ?>
                    <form method="post" class="yarpp_separated">
                        <input type='submit' class='button yarpp_spin_on_click' name='myisam_override' value='Create FULLTEXT indexes'/>
                        <span class="spinner"></span>
                    </form>
                    <p><?php
					    esc_html_e(
						    'Note: although no data should be lost, it is always recommended to perform a full database backup before doing this.','yarpp'
					    );
					    ?>
                    </p>
				    <?php if( ! $database_supports_fulltext_indexes){ ?>
                        <p>
						    <?php
						    printf(
							    esc_html__('See MySQL %1$sstorage engines%2$s documentation for details on MySQL engines.','yarpp'),
							    '<a href="https://dev.mysql.com/doc/refman/8.0/en/storage-engines.html" target="_blank">',
							    '</a>'
						    );
						    ?>
                        </p>
				    <?php } ?>
                </div>
            </div>
		    <?php
	    }
    }
}