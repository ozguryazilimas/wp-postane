<?php include dirname(__FILE__)."/admin_header.tpl"; ?>
<tr valign="top">
<th scope="row"><?php _e("Maximum Results to Display", 'dwls'); ?></th>

<td><input type="text" name="daves-wordpress-live-search_max_results" id="daves-wordpress-live-search_max_results" value="<?php echo $maxResults; ?>" class="regular-text code" /><span class="setting-description"><?php _e("Enter &quot;0&quot; to display all matching results", 'dwls'); ?></span></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("Minimum characters before searching", 'dwls'); ?></th>

<td>
<select name="daves-wordpress-live-search_minchars">
<option value="1" <?php if($minCharsToSearch == 1) echo 'selected="selected"'; ?>><?php _e("Search right away", 'dwls'); ?></option>
<option value="2" <?php if($minCharsToSearch == 2) echo 'selected="selected"'; ?>><?php _e("Wait for two characters", 'dwls'); ?></option>
<option value="3" <?php if($minCharsToSearch == 3) echo 'selected="selected"'; ?>><?php _e("Wait for three characters", 'dwls'); ?></option>
<option value="4" <?php if($minCharsToSearch == 4) echo 'selected="selected"'; ?>><?php _e("Wait for four characters", 'dwls'); ?></option>
</select>
</td>
</tr>


<tr valign="top">
<th scope="row"><?php _e("Results Direction", 'dwls'); ?></th>

<td><input type="radio" name="daves-wordpress-live-search_results_direction" id="daves-wordpress-live-search_results_direction_down" value="down" <?php if($resultsDirection == 'down'): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_results_direction_down"><?php _e("Down", 'dwls'); ?></input></label>

<input type="radio" name="daves-wordpress-live-search_results_direction" id="daves-wordpress-live-search_results_direction_up" value="up" <?php if($resultsDirection == 'up'): ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_results_direction_up"><?php _e("Up", 'dwls'); ?></label><br /><span class="setting-description"><?php _e("When search results are displayed, in which direction should the results box extend from the search box?", 'dwls'); ?></span></td>
</tr>

<!-- WP E-Commerce -->
<?php if(defined('WPSC_VERSION')) : ?>
<tr valign="top">
<td colspan="2"><h3><?php _e("WP E-Commerce", 'dwls'); ?></h3></td>
</tr>

<tr valign="top">
<th scope="row"> </th>
<td>
    <div><span class="setting-description"><?php printf(__("When used with the %sWP E-Commerce%s plugin, Dave&apos;s WordPress Live Search can search your product catalog instead of posts & pages.", 'dwls'), '<a href="http://getshopped.org/">', '</a>'); ?></span></div>
    <table>
        <tr><td><input type="radio" id="daves-wordpress-live-search_source_1" name="daves-wordpress-live-search_source" value="0" <?php if(0 == $searchSource) : ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_source_1"><?php _e("Search posts &amp; pages", 'dwls'); ?></label></td></tr>
        <tr><td><input type="radio" id="daves-wordpress-live-search_source_2" name="daves-wordpress-live-search_source" value="1" <?php if(1 == $searchSource) : ?>checked="checked"<?php endif; ?> /> <label for="daves-wordpress-live-search_source_2"><?php _e("Search products", 'dwls'); ?></label></td></tr>
    </table>

</td>
</tr>
<?php else : ?>
<input type="hidden" name="daves-wordpress-live-search_source" value="0" />
<?php endif; ?>

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