<tr id="rw_rate_readonly" class="rw-<?php echo ($odd ? "odd" : "even");?>">
    <td class="rw-ui-def-width">
        <span class="rw-ui-def">Read Only:</span>
    </td>
    <td>
        <div class="rw-ui-img-radio<?php if ($rw_options->readOnly == false) echo " rw-selected";?>" onclick="rwStar.setReadOnly(false); rwNero.setReadOnly(false);">
            <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-unlocked rw-ui-default"></i></i>
            <span>Active</span>
            <input type="radio" name="rw-readonly" value="star"<?php if ($rw_options->readOnly == false) echo ' checked="checked"';?> />
        </div>
        <div class="rw-ui-img-radio<?php if ($rw_options->readOnly == true) echo " rw-selected";?>" onclick="rwStar.setReadOnly(true); rwNero.setReadOnly(true);">
            <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-locked"></i></i>
            <span>ReadOnly</span>
            <input type="radio" name="rw-readonly" value="nero"<?php if ($rw_options->readOnly == true) echo ' checked="checked"';?> />
        </div>
    </td>
</tr>
