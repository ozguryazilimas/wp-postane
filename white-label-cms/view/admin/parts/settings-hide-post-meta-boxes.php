<?php
$meta_boxes = array(
    'excerpt' => 'Excerpt',
    'slug' => 'Slug',
    'tags' => 'Tags',
    'author' => 'Author',
    'comments' => 'Comments',
    'revisions' => 'Revisions',
    'discussion' => 'Discussion',
    'categories' => 'Categories',
    'custom_fields' => 'Custom Fields',
    'send_trackbacks' => 'Send Trackbacks'
);
?>
<div class="wlcms-input-group">
    <ul>
        <?php
        foreach ($meta_boxes as $box_key => $box_value) {
        ?>
        <li>
            <div class="wlcms-input">
            <input class="wlcms-toggle wlcms-toggle-light" id="metabox_post_<?php echo $box_key ?>" name="metabox_post_<?php echo $box_key ?>" value="1" type="checkbox" <?php checked(wlcms_field_setting('metabox_post_' . $box_key ), 1, true) ?>/>
            <label class="wlcms-toggle-btn" for="metabox_post_<?php echo $box_key ?>"></label><label class="toggle-label" for="metabox_post_<?php echo $box_key ?>"><?php _e($box_value, 'white-label-cms') ?></label> 
            </div>
        </li>
        <?php
        }
        ?>
    </ul>
</div>