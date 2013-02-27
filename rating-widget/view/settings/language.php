<tr id="rw_language" class="rw-<?php echo ($odd ? "odd" : "even");?>">
    <td><span class="rw-ui-def">Language:</span></td>
    <td>
        <select id="rw_lng_select" tabindex="1" name="rw_language" style="font-size: 12px;" onchange="RWM.Set.language(this.value);">
            <?php
                $rw_language_str = isset($rw_language_str) ? $rw_language_str : "en";
                foreach ($rw_languages as $short => $long)
                {
                    echo '<option value="' . $short . '"' . (($short == $rw_language_str) ? ' selected="selected"' : '') . '>' . $long . '</option>';
                }
            ?>
        </select>
    </td>
</tr>