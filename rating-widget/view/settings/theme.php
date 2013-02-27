<?php
    require_once(WP_RW__PLUGIN_DIR . "/themes/dir.php");
    require_once(WP_RW__PLUGIN_DIR . "/lib/defaults.php");
    require_once(WP_RW__PLUGIN_DIR . "/lib/def_settings.php");
    global $DEFAULT_OPTIONS;
?>
<tr id="rw_theme" class="rw-<?php echo ($odd ? "odd" : "even");?>">
    <td><span class="rw-ui-def">Theme:</span></td>
    <td>
        <img id="rw_theme_loader" src="<?php echo WP_RW__ADDRESS_IMG;?>rw.loader.gif" alt="" />
        <?php
            global $RW_THEMES;
            
            foreach ($RW_THEMES as $type => $type_themes)
            {
        ?>
        <div id="rw_<?php echo $type;?>_theme_select" class="rw-select" style="display: none;">
            <select tabindex="4"><option></option></select>
            <i class="rw-select-icon"></i>
            <div class="rw-select-frame" style="display: none;"></div>
            <div id="rw_<?php echo $type;?>_theme_selected" class="rw-li rw-selected-item">
            <?php
                if ($rw_options->type == $type && isset($rw_options->theme) && isset($type_themes[$rw_options->theme]))
                {
                    require(WP_RW__PLUGIN_DIR . "/themes/" . $type_themes[$rw_options->theme]["file"]);
                    $options = rw_enrich_options1($theme["options"], $DEFAULT_OPTIONS);
                    $options->size = "large";
                    $options->advanced->font->size = "20px";
                    $options->advanced->layout->lineHeight = "30px";
                    $options->advanced->layout->dir = "ltr";
                    $options->advanced->layout->align->hor = "right";
                    $vars["options"] = $options;
                    $vars["label"] = $theme["title"];
                    $vars["rate"] = 3.5;
                    $vars["likes"] = 8;
                    $vars["dislikes"] = 3;
                    require(dirname(dirname(__FILE__)) . "/{$type}_rating.php");
                    
                    $selected_theme = $rw_options->theme;
                }
                else
                {
                    $default_theme = ($type == "star") ? DEF_STAR_THEME : DEF_NERO_THEME;
                
                    require(WP_RW__PLUGIN_DIR . "/themes/" . $type_themes[$default_theme]["file"]);
                    $options = rw_enrich_options1($theme["options"], $DEFAULT_OPTIONS);
                    $options->size = "large";
                    $options->advanced->font->size = "20px";
                    $options->advanced->layout->lineHeight = "30px";
                    $options->advanced->layout->dir = "ltr";
                    $options->advanced->layout->align->hor = "right";
                    $vars["options"] = $options;
                    $vars["label"] = $theme["title"];
                    $vars["rate"] = 3.5;
                    $vars["likes"] = 8;
                    $vars["dislikes"] = 3;
                    require(dirname(dirname(__FILE__)) . "/{$type}_rating.php");
                    
                    $selected_theme = $default_theme;
                }
            ?>
            </div>
            <ul id="rw_<?php echo $type;?>_theme_select_list" class="rw-list" style="display: none;">
            <?php
                foreach ($type_themes as $theme_name => $data)
                {
                    if ($data["type"] == $type)
                    {
                        require(WP_RW__PLUGIN_DIR . "/themes/" . $data["file"]);
                        $options = rw_enrich_options1($theme["options"], $DEFAULT_OPTIONS);
            ?>
                <li class="rw-li<?php if ($selected_theme === $theme_name) echo " rw-selected";?>" onclick="jQuery('#rw_<?php echo $type;?>_theme_selected').html(this.innerHTML); jQuery('#rw_<?php echo $type;?>_theme_select_list li.rw-li.rw-selected').removeClass('rw-selected'); this.className += ' rw-selected'; RWM.Set.theme('<?php echo $theme_name;?>', RW.TYPE.<?php echo strtoupper($type);?>);" onmouseover="jQuery(this.parentNode.childNodes).removeClass('rw-hover'); this.className += ' rw-hover';">
            <?php
                        $options->size = "large";
                        $options->advanced->font->size = "20px";
                        $options->advanced->layout->lineHeight = "30px";
                        $options->advanced->layout->dir = "ltr";
                        $options->advanced->layout->align->hor = "right";
                        $vars["options"] = $options;
                        $vars["label"] = $theme["title"];
                        $vars["rate"] = 3.5;
                        $vars["likes"] = 8;
                        $vars["dislikes"] = 3;
                        require(dirname(dirname(__FILE__)) . "/{$type}_rating.php");
            ?>
                </li>
            <?php
                    }
                }
            ?>
            </ul>
        </div>
        <?php
            }
        ?>
    </td>
</tr>