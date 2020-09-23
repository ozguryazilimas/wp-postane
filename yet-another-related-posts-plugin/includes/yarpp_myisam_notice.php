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
            <div class="notice notice-success">
            <?php
            esc_html_e(
                'Full-text indexes have been added to the posts table. You may now use titles and bodies as relatedness criteria.',
                'yarpp'
            );
            ?></div>
        <?php

    } else {

        yarpp_set_option(YARPP_DB_Options::YARPP_MYISAM_OVERRIDE, 0);
        ?><div class="notice notice-error" >
                <span class="yarpp-red"><?php esc_html_e('Full-text Index creation did not work!','yarpp');?></span><br/>
	            <?php
                    printf(
	                    esc_html__( 'There was an error adding the full-text index to your posts table: %s', 'yarpp' ),
                        $yarpp->db_options->get_fulltext_db_error()
                    );
                    ?><br/>
	            <?php esc_html_e( 'Titles and bodies still cannot be used as relatedness criteria.', 'yarpp' ); ?>
            </div>
        <?php
    }
}
