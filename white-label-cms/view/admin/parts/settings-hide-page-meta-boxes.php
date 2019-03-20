<?php
$meta_boxes = array(
    'custom_fields' => 'Custom Fields',
    'author' => 'Author',
    'discussion' => 'Discussion',
    'revisions' => 'Revisions',
    'page_attributes' => 'Page Attributes',
    'slug' => 'Slug'
);
?>
<div class="wlcms-input-group">
    <ul>
        <?php
        foreach ($meta_boxes as $box_key => $box_value) {
        ?>
        <li>
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" id="metabox_page_<?php echo $box_key ?>" name="metabox_page_<?php echo $box_key ?>" value="1" type="checkbox" <?php checked(wlcms_field_setting('metabox_page_' . $box_key ), 1, true) ?>/>
            <label class="wlcms-toggle-btn" for="metabox_page_<?php echo $box_key ?>"></label><label class="toggle-label" for="metabox_page_<?php echo $box_key ?>"><?php _e($box_value, 'white-label-cms') ?></label> 
            </div>
        </li>
        <?php
        }
        ?>
    </ul>
</div>