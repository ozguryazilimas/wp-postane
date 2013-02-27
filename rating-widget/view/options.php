<div class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3>Rating-Widget Options</h3>
            <div class="inside rw-ui-content-container rw-no-radius">
                <table>
                    <?php
                        $dirname = dirname(__FILE__);
                        
                        $odd = true;
                        include_once($dirname . "/settings/language.php");
                        $odd = !$odd;
                        include_once($dirname . "/settings/type.php");
                        $odd = !$odd;
                        include_once($dirname . "/settings/theme.php");
                        $odd = !$odd;
                        include_once($dirname . "/settings/size.php");
                        $odd = !$odd;
                        if (WP_RW__BP_INSTALLED && is_plugin_active(WP_RW__BP_CORE_FILE)){
                            include_once($dirname . "/settings/background.php");
                            $odd = !$odd;
                        }
                        include_once($dirname . "/settings/read_only.php");
                        $odd = !$odd;
                    ?>
                </table>
                <?php include_once($dirname . "/settings/advanced.php");?>
            </div>
        </div>
    </div>
</div>
