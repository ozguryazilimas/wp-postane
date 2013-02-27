<div class="postbox rw-body" style="width: 350px;">
    <h3>Live Preview</h3>
    <div class="inside" style="padding: 10px;">
        <div id="rw_preview_container" style="text-align: <?php
            if ($rw_options->advanced->layout->align->ver != "middle")
            {
                echo "center";
            }
            else
            {
                if ($rw_options->advanced->layout->align->hor == "right"){
                    echo "left";
                }else{
                    echo "right";
                }
            }
        ?>;">
            <div id="rw_preview_star" class="rw-ui-container rw-urid-3"></div>
            <div id="rw_preview_nero" class="rw-ui-container rw-ui-nero rw-urid-17" style="display: none;"></div>
        </div>
        <div class="rw-js-container">
            <script type="text/javascript">
                var rwStar, rwNero;
                
                // Initialize ratings.
                function RW_Async_Init(){
                    RW.init("cfcd208495d565ef66e7dff9f98764da");
                    <?php
                        $b_type = $rw_options->type;
                        $b_theme = $rw_options->theme;
                        $b_style = $rw_options->style;
                        
                        $types = array("star", "nero");
                        $default_themes = array("star" => DEF_STAR_THEME, "nero" => DEF_NERO_THEME);
                        $ratings_uids = array("star" => 3, "nero" => 17);
                        foreach($types as $type)
                        {
                    ?>
                    RW.initRating(<?php
                        if ($rw_options->type !== $type)
                        {
                            $rw_options->type = $type;
                            $rw_options->theme = $default_themes[$type];
                            $rw_options->style = "";
                        }
                        
                        echo $ratings_uids[$type] . ", ";
                        echo json_encode($rw_options);
                        
                        // Recover.
                        $rw_options->type = $b_type;
                        $rw_options->theme = $b_theme;
                        $rw_options->style = $b_style;                        
                    ?>);
                    <?php
                        }
                    ?>
                    RW.render(function(ratings){
                        rwStar = RWM.STAR = ratings[3];
                        rwNero = RWM.NERO = ratings[17];
                        
                        jQuery("#rw_theme_loader").hide();
                        jQuery("#rw_<?php echo $rw_options->type;?>_theme_select").show();
                        
                        RWM.Set.sizeIcons(RW.TYPE.<?php echo strtoupper($rw_options->type);?>);
                        
                        <?php
                            if ($rw_options->type == "star"){
                                echo 'jQuery("#rw_preview_nero").hide();';
                                echo 'jQuery("#rw_preview_star").show();';
                            }else{
                                echo 'jQuery("#rw_preview_star").hide();';
                                echo 'jQuery("#rw_preview_nero").show();';
                            }
                        ?>
                        
                        // Set selected themes.
                        RWM.Set.selectedTheme.star = "<?php
                            echo (isset($rw_options->type) && 
                                  $rw_options->type == "star" && 
                                  isset($rw_options->theme) && 
                                  $rw_options->theme !== "") ? $rw_options->theme : DEF_STAR_THEME;
                        ?>";
                        RWM.Set.selectedTheme.nero = "<?php
                            echo (isset($rw_options->type) &&
                                  $rw_options->type == "nero" &&
                                  isset($rw_options->theme) && 
                                  $rw_options->theme !== "") ? $rw_options->theme : DEF_NERO_THEME;
                        ?>";
                        
                        RWM.Set.selectedType = RW.TYPE.<?php echo strtoupper($rw_options->type);?>;
                        
                        // Add all themes inline css.
                        for (var t in RWT)
                        {
                            if (RWT[t].options.color == RW.COLOR.CUSTOM){
                                RW._addCustomImgStyle(RWT[t].options.imgUrl.large, [RWT[t].options.type], "theme", t);
                            }
                        }
                    }, false);
                }

                // Append RW JS lib.
                if (typeof(RW) == "undefined"){ 
                    (function(){
                        var rw = document.createElement("script"); rw.type = "text/javascript"; rw.async = true;
                        rw.src = "<?php echo WP_RW__ADDRESS_JS; ?>external.js";
                        var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(rw, s);
                    })();
                }
            </script>
        </div>
        <p class="submit" style="margin-top: 10px;">
            <input type="hidden" name="<?php echo $rw_form_hidden_field_name; ?>" value="Y">
            <input type="hidden" id="rw_options_hidden" name="rw_options" value="" />
            <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
            <a href="<?php echo WP_RW__ADDRESS;?>/get-the-word-press-plugin/" class="button-secondary" target="_blank">Go Pro!</a>
        </p>
    </div>
</div>
