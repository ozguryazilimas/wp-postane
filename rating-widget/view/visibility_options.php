<?php
    $types = ucwords(str_replace("-", " ", $selected_key));
    $type = substr($types, 0, strlen($types) - 1);
    
    function implode_or_empty($array)
    {
        if (is_string($array))
            return $array;
            
        if (!is_array($array))
            return "";
            
        return implode(',', $array);
    }
?>
<div id="rw_visibiliy_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3>Specific Visibility Settings</h3>
            <div class="inside rw-ui-content-container rw-no-radius" style="padding: 5px; width: 610px;">
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_visibility_settings->selected == 0) echo ' rw-selected';?>">
                    <i class="rw-ui-sprite rw-ui-visibility-all"></i> <input type="radio" name="rw_visibility" value="0" <?php if ($rw_visibility_settings->selected == 0) echo ' checked="checked"';?>> <span>Show rating on every <?php echo $type;?></span>
                </div>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_visibility_settings->selected == 1) echo ' rw-selected';?>" onclick="jQuery(this).children('input[type=text]').focus();">
                    <i class="rw-ui-sprite rw-ui-visibility-exclude"></i> <input type="radio" name="rw_visibility" value="1" <?php if ($rw_visibility_settings->selected == 1) echo ' checked="checked"';?>> <span>Show rating on every <?php echo $type;?> except the listed <?php echo $types;?></span> <input type="text" name="rw_visibility_exclude" value="<?php echo implode_or_empty($rw_visibility_settings->exclude);?>" />
                </div>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($rw_visibility_settings->selected == 2) echo ' rw-selected';?>" onclick="jQuery(this).children('input[type=text]').focus();">
                    <i class="rw-ui-sprite  rw-ui-visibility-include"></i> <input type="radio" name="rw_visibility" value="2" <?php if ($rw_visibility_settings->selected == 2) echo ' checked="checked"';?>> <span>Show rating on only the listed <?php echo $types;?></span> <input type="text" name="rw_visibility_include" value="<?php echo implode_or_empty($rw_visibility_settings->include);?>" />
                </div>
                <span style="font-size: 10px; background: white; padding: 2px; border: 1px solid gray; display: block; margin-top: 5px; font-weight: bold; background: rgb(240,240,240); color: black;">Seperate ids with commas (e.g. "3,17,8").</span>
            </div>
        </div>
    </div>
</div>
