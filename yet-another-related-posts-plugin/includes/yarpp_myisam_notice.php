<?php
/**
 * @var $yarpp YARPP
 */
if (isset($_POST['myisam_override'])) {

    yarpp_set_option(YARPP_DB_Options::YARPP_MYISAM_OVERRIDE, true);
    $enabled = $yarpp->enable_fulltext();

    if($enabled){

        $yarpp->db_options->set_fulltext_disabled(false);
        ?>
            <div class="updated">
            <?php
            esc_html_e(
                'The fulltext indexes have been added. You may now use titles and bodies as relatedness criteria.',
                'yarpp'
            );
            ?></div>
        <?php

    } else {

        yarpp_set_option(YARPP_DB_Options::YARPP_MYISAM_OVERRIDE, 0);
        ?><div class="error" >
                <span class="yarpp-red"><?php esc_html_e('Fulltext Index creation did not work!','yarpp');?></span><br/>
	            <?php
                    printf(
	                    esc_html__( 'There was an error adding the fulltext index onto your posts table: %s', 'yarpp' ),
                        $yarpp->db_options->get_fulltext_db_error()
                    );
                    ?><br/>
	            <?php esc_html_e( 'Titles and bodies still cannot be used as relatedness criteria.', 'yarpp' ); ?>
            </div>
        <?php
    }
}

$database_supports_fulltext_indexes = $yarpp->db_schema->database_supports_fulltext_indexes();
if ( ! $database_supports_fulltext_indexes) $yarpp->disable_fulltext();
if ( !(bool) yarpp_get_option(YARPP_DB_Options::YARPP_MYISAM_OVERRIDE) && $yarpp->db_options->is_fulltext_disabled()) {
        ?>
    <div class='updated'>
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
            <p><?php esc_html_e('YARPP can automatically create "fulltext indexes" to enable comparing posts based on titles and bodies. To do so, click the button below.');?></p>
        <?php } else { ?>
                <p><?php
		            printf(
			            esc_html__('Because fulltext indexing is not supported by your current table engine, "%1$s", YARPP cannot compare posts based on their titles or bodies.','yarpp'),
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
		            esc_html_e('After you have done that, click the button below to enable comparing titles and bodies using "fulltext indexes."','yarpp');
		            ?>
                </p>
        <?php } ?>
	        <form method="post" class="yarpp_separated">
	            <input type='submit' class='button' name='myisam_override' value='Create fulltext indexes'/>
	        </form>
	        <p><?php
	                esc_html_e(
	                    'Note: although no data should be lost by altering the table’s engine, it is always recommended to perform a full database backup before doing this.','yarpp'
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