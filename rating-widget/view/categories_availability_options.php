<?php
    // Get all post categories.
    $categories = get_categories();
    
    if (is_array($categories) && count($categories) > 0)
    {
        $all = in_array("-1", $rw_categories);
?>
<div id="rw_categories_availability_settings" class="has-sidebar has-right-sidebar">
    <div class="has-sidebar-content">
        <div class="postbox rw-body">
            <h3>Categories Visibility Settings</h3>
            <div class="inside rw-ui-content-container rw-no-radius" style="padding: 5px; width: 610px;">
                <div class="rw-ui-img-radio rw-ui-hor rw-select-all<?php if ($all) echo ' rw-selected';?>">
                    <input type="checkbox" name="rw_categories[]" value="-1" <?php if ($all) echo ' checked="checked"';?>> <span>All Categories</span>
                </div>
<?php
                foreach ($categories as $category)
                {
                    $selected = ($all || in_array($category->cat_ID, $rw_categories));
?>
                <div class="rw-ui-img-radio rw-ui-hor<?php if ($selected) echo ' rw-selected';?>">
                    <input type="checkbox" name="rw_categories[]" value="<?php echo $category->cat_ID;?>" <?php if ($selected) echo ' checked="checked"';?>> <span><?php echo $category->cat_name;?></span>
                </div>
<?php                    
                }
?>
            </div>
        </div>
    </div>
</div>
<?php
    }
?>