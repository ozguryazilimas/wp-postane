<tr id="rw_rate_background" class="rw-<?php echo ($odd ? "odd" : "even");?>">
    <td class="rw-ui-def-width">
        <span class="rw-ui-def">Background:</span>
    </td>
    <td>
        <div class="rw-ui-img-radio<?php if ($rw_options->advanced->css->container !== "") echo " rw-selected";?>" onclick="RWM.Set.background('buddy');">
            <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-buddy"></i></i>
            <span>Buddy</span>
            <input type="radio" name="rw-background" value="buddypress"<?php if ($rw_options->advanced->css->container !== "") echo ' checked="checked"';?> />
        </div>
        <div class="rw-ui-img-radio<?php if ($rw_options->advanced->css->container === "") echo " rw-selected";?>" onclick="RWM.Set.background('transparent');">
            <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-transparent rw-ui-default"></i></i>
            <span>Clear</span>
            <input type="radio" name="rw-background" value="transparent"<?php if ($rw_options->advanced->css->container === "") echo ' checked="checked"';?> />
        </div>
    </td>
</tr>
