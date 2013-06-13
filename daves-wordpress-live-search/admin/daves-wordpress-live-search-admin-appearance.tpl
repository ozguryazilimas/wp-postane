<?php include dirname(__FILE__)."/admin_header.tpl"; ?>
<tr valign="top">
<th scope="row"><?php _e("Display Metadata", 'dwls'); ?></th>

<td>
    <input type="hidden" name="daves-wordpress-live-search_display_post_meta" value="" />
    <input type="checkbox" name="daves-wordpress-live-search_display_post_meta" id="daves-wordpress-live-search_display_post_meta" value="true" <?php if($displayPostMeta): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_display_post_meta"><?php _e("Display author & date for every search result", 'dwls'); ?></label>
</td>
</tr>

<!-- Display post thumbnail -->
<tr valign="top">
<th scope="row"><?php _e("Display Post Thumbnail", 'dwls'); ?></th>

<td>
    <input type="hidden" name="daves-wordpress-live-search_thumbs" value="" />
    <input type="checkbox" name="daves-wordpress-live-search_thumbs" id="daves-wordpress-live-search_thumbs" value="true" <?php if($showThumbs): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_thumbs"><?php _e("Display thumbnail images for every search result with at least one image", 'dwls'); ?></label>
</td>
</tr>

<!-- Display post excerpt -->
<tr valign="top">
<th scope="row"><?php _e("Display Post Excerpt", 'dwls'); ?></th>

<td>
    <input type="hidden" name="daves-wordpress-live-search_excerpt" value="" />
    <input type="checkbox" name="daves-wordpress-live-search_excerpt" id="daves-wordpress-live-search_excerpt" value="true" <?php if($showExcerpt): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_excerpt"><?php printf(__("Display an excerpt for every search result. If the post doesn't have one, use the first %s characters.", 'dwls'), "<input type=\"text\" name=\"daves-wordpress-live-search_excerpt_length\" id=\"daves-wordpress-live-search_excerpt_length\" value=\"$excerptLength\" size=\"3\" />"); ?></label>
</td>
</tr>

<!-- Display 'more results' -->
<tr valign="top">
<th scope="row"><?php _e("Display &quot;View more results&quot; link", 'dwls'); ?></th>

<td>
    <input type="hidden" name="daves-wordpress-live-search_more_results" value="" />
    <input type="checkbox" name="daves-wordpress-live-search_more_results" id="daves-wordpress-live-search_more_results" value="true" <?php if($showMoreResultsLink): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_more_results"><?php _e("Display the &quot;View more results&quot; link after the search results.", 'dwls'); ?></label>
</td>
</tr>

<!-- CSS styles -->
<tr valign="top">
<td colspan="2"><h3><?php _e("Styles", 'dwls'); ?></h3></td>
</tr>

<tr valign="top">
<th scope="row"> </th>
<td>
<input type="radio" name="daves-wordpress-live-search_css" id="daves-wordpress-live-search_css_default_gray" value="default_gray" <?php if('default_gray' == $cssOption): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_css_default_gray"><?php _e("Default Gray", 'dwls'); ?></label><br /><span class="setting-description"><?php _e("Default style in gray.", 'dwls'); ?></span>
<br /><br />
<input type="radio" name="daves-wordpress-live-search_css" id="daves-wordpress-live-search_css_default_red" value="default_red" <?php if('default_red' == $cssOption): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_css_default_red"><?php _e("Default Red", 'dwls'); ?></label><br /><span class="setting-description"><?php _e("Default style in red", 'dwls'); ?></span>
<br /><br />
<input type="radio" name="daves-wordpress-live-search_css" id="daves-wordpress-live-search_css_default_blue" value="default_blue" <?php if('default_blue' == $cssOption): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_css_default_blue"><?php _e("Default Blue", 'dwls'); ?></label><br /><span class="setting-description"><?php _e("Default style in blue", 'dwls'); ?></span>
<br /><br />

<?php if($color_picker_supported) : ?>

<input type="radio" name="daves-wordpress-live-search_css" id="daves-wordpress-live-search_css_custom" value="custom" <?php if('custom' == $cssOption): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_css_custom"><?php _e("Custom", 'dwls'); ?></label><br /><span class="setting-description"><?php _e("Customize the search results display here", 'dwls'); ?></span>

<div id="custom_colors" style="display:none;">

<div id="dwls_design_preview">
<ul class="search_results dwls_search_results" style="display: block;"><input type="hidden" name="query" value="sample"><li class="daves-wordpress-live-search_result"><a href="#" class="daves-wordpress-live-search_title">Sample Page</a><p class="excerpt clearfix"></p><p>This is an example page. It’s different from a blog post because it will stay in one place and will [...]</p> <p></p><p class="meta clearfix" id="daves-wordpress-live-search_author">Posted by Admin</p><p id="daves-wordpress-live-search_date" class="meta clearfix">December 5, 2012</p><div class="clearfix"></div></li><div class="clearfix search_footer dwls_search_footer"><a href="#">View more results</a></div></ul>
</div>

<div id="custom_colors_options">

<div><label><?php _e("Width (in pixels)", 'dwls'); ?></label><input type="number" name="daves-wordpress-live-search_custom[width]" id="daves-wordpress-live-search_custom_width" value="<?php if(!empty($customOptions['width'])) echo $customOptions['width']; else echo "250" ?>" /></div>

<div><label><?php _e("Title", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[title]" id="daves-wordpress-live-search_custom_title" value="<?php if(!empty($customOptions['title'])) echo $customOptions['title']; ?>" data-default-color="#000" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Excerpt", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[fg]" id="daves-wordpress-live-search_custom_fg" value="<?php if(!empty($customOptions['fg'])) echo $customOptions['fg']; ?>" data-default-color="#000" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Background", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[bg]" id="daves-wordpress-live-search_custom_bg" value="<?php if(!empty($customOptions['bg'])) echo $customOptions['bg']; ?>" data-default-color="#ddd" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Hover Background", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[hoverbg]" id="daves-wordpress-live-search_custom_hoverbg" value="<?php if(!empty($customOptions['hoverbg'])) echo $customOptions['hoverbg']; ?>" data-default-color="#fff" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Divider", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[divider]" id="daves-wordpress-live-search_custom_divider" value="<?php if(!empty($customOptions['divider'])) echo $customOptions['divider']; ?>" data-default-color="#aaa" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Footer Background", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[footbg]" id="daves-wordpress-live-search_custom_footbg" value="<?php if(!empty($customOptions['footbg'])) echo $customOptions['footbg']; ?>" data-default-color="#888" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Footer Text", 'dwls'); ?></label><input type="text" name="daves-wordpress-live-search_custom[footfg]" id="daves-wordpress-live-search_custom_footfg" value="<?php if(!empty($customOptions['footfg'])) echo $customOptions['footfg']; ?>" data-default-color="#fff" class="dwls_color_picker" pattern="^#[0-9,a-f]{3,6}" /></div>

<div><label><?php _e("Shadow", 'dwls'); ?></label><input type="checkbox" name="daves-wordpress-live-search_custom[shadow]" id="daves-wordpress-live-search_custom_shadow" value="true" class="dwls_design_toggle" <?php if(!empty($customOptions['shadow'])) echo 'checked="checked"'; ?> /></div>

</div>

</div>
<br /><br />

<?php endif; ?>

<input type="radio" name="daves-wordpress-live-search_css" id="daves-wordpress-live-search_css_theme" value="theme" <?php if('theme' == $cssOption): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_css_theme"><?php _e("Theme-specific", 'dwls'); ?></label><br /><span class="setting-description"><strong><?php _e("For advanced users", 'dwls'); ?>:</strong> <?php _e("Theme must include a CSS file named daves-wordpress-live-search.css. If your theme does not have one, copy daves-wordpress-live-search_default_gray.css from this plugin's directory into your theme's directory and modify as desired.", 'dwls'); ?></span>
<br /><br />
<input type="radio" name="daves-wordpress-live-search_css" id="daves-wordpress-live-search_css_existing_theme" value="notheme" <?php if('notheme' == $cssOption): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_css_theme"><?php _e("Theme-specific (theme's own CSS file)", 'dwls'); ?></label><br /><span class="setting-description"><strong><?php _e("For advanced users", 'dwls'); ?>:</strong> <?php _e("Use the styles contained within your Theme's stylesheet. Don't include a separate stylesheet for Live Search.", 'dwls'); ?>
</td>
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