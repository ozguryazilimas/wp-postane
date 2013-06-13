<?php include dirname(__FILE__)."/admin_header.tpl"; ?>
<tr valign="top">
<th scope="row"><?php _e("Exceptions", 'dwls'); ?></th>

<td>
<?php $permalinkFormat = get_option('permalink_structure'); ?>

<div><span class="setting-description"><?php printf(__("Enter the %s of pages which should not have live searching, one per line. The * wildcard can be used at the start or end of a line. For example: %s", 'dwls'), empty($permalinkFormat) ? __('paths', 'dwls') : __('permalinks'), '<ul style="list-style-type:disc;margin-left: 3em;">' . empty($permalinkFormat) ? '<li>?page_id=123</li><li>page_id=1*</li>' : '<li>about</li><li>employee-*</li>' . '</ul>'); ?>

<p><strong><?php _e("NOTE", 'dwls'); ?>:</strong> <?php _e("These pages will still be returned in search results. This only disables the Live Search feature for the search box on these pages.", 'dwls'); ?></p></span></div>
<textarea name="daves-wordpress-live-search_exceptions" id="daves-wordpress-live-search_exceptions" rows="5" cols="60"><?php echo $exceptions; ?></textarea></td>
</tr>

<!-- X Offset -->
<tr valign="top">
<th scope="row"><?php _e("Search Results box X offset", 'dwls'); ?></th>

<td>
<div><span class="setting-description"><?php _e("Use this setting to move the search results box left or right to align exactly with your theme's search field. Value is in pixels. Negative values move the box to the left, positive values move it to the right.", 'dwls'); ?></span></div>

<input type="text" name="daves-wordpress-live-search_xoffset" id="daves-wordpress-live-search_xoffset" value="<?php echo $xOffset; ?>"</td>
</tr>

<!-- Y Offset -->
<tr valign="top">
<th scope="row"><?php _e("Search Results box Y offset", 'dwls'); ?></th>

<td>
<div><span class="setting-description"><?php _e("Use this setting to move the search results box up or down to align exactly with your theme's search field. Value is in pixels. Negative values move the box up, positive values move it down.", 'dwls'); ?></span></div>

<input type="text" name="daves-wordpress-live-search_yoffset" id="daves-wordpress-live-search_yoffset" value="<?php echo $yOffset; ?>"</td>
</tr>

<!-- Cache lifetime -->
<tr valign="top">
<th scope="row"><?php _e("Cache Lifetime", 'dwls'); ?></th>

<td><input type="text" name="daves-wordpress-live-search_cache_lifetime" id="daves-wordpress-live-search_cache_lifetime" value="<?php echo $cacheLifetime; ?>" class="regular-text code" /><span class="setting-description"><?php _e("Enter &quot;0&quot; to disable caching", 'dwls'); ?></span></td>
</tr>

<!-- Apply the_content filter -->
<tr valign="top">
<th scope="row"><?php _e("Enable content filter", 'dwls'); ?></th>

<td><input type="checkbox" name="daves-wordpress-live-search_apply_content_filter" id="daves-wordpress-live-search_apply_content_filter" value="true" <?php if($applyContentFilter): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_apply_content_filter"><?php _e("Allow other plugins to filter the content before looking for a thumbnail. This will affect Live Search performance, so only enable this if you really need it.", 'dwls'); ?></label></td>
</tr>

<!-- Clear Cache -->
<tr valign="top">
<th scope="row"><?php _e("Clear Cache", 'dwls'); ?></th>

<td>
	<?php submit_button( __("Clear Cache", 'dwls'), 'secondary', 'daves-wordpress-live-search_submit', false, array('value' => 'Clear Cache') ); ?>

	&nbsp;<label for="daves-wordpress-live-search_submit"><?php _e("If you change settings or post/edit content, your cache will be cleared automatically. Use this button to clear the cache manually if needed.", 'dwls'); ?></label></td>
</tr>

<!-- Submit buttons -->
<tr valign="top">
<td colspan="2"><div style="border-top: 1px solid #333;margin-top: 15px;padding: 5px;">
	<?php submit_button( NULL, 'primary', 'daves-wordpress-live-search_submit', false, array('id' => 'daves-wordpress-live-search_submit') ); ?>
</div></td>
</tr>

</tbody></table>

</form>
<?php include dirname(__FILE__)."/admin_footer.tpl"; ?>
</div>
