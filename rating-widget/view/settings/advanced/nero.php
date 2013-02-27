<table id="rw_nero_settings" cellspacing="0" style="display: none;">
    <tr id="rw_nero_show" class="rw-odd">
        <td class="rw-ui-def-width">
            <span class="rw-ui-def">Show Thumbs:</span>
        </td>
        <td>
            <div class="rw-ui-img-radio<?php if ($rw_options->advanced->nero->showLike) echo " rw-selected";?>" onclick="rwStar.toggleLike(); rwNero.toggleLike();">
                <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-large rw-like"></i></i>
                <span>Like</span>
                <input type="checkbox" name="rw-nero-show-like" value="0"<?php if ($rw_options->advanced->nero->showLike) echo ' checked="checked"';?> />
            </div>
            <div class="rw-ui-img-radio<?php if ($rw_options->advanced->nero->showDislike) echo " rw-selected";?>" onclick="rwStar.toggleDislike(); rwNero.toggleDislike();">
                <i class="rw-ui-holder"><i class="rw-ui-sprite rw-ui-large rw-dislike"></i></i>
                <span>Dislike</span>
                <input type="checkbox" name="rw-nero-show-dislike" value="1"<?php if ($rw_options->advanced->nero->showDislike) echo ' checked="checked"';?> />
            </div>
        </td>
    </tr>
</table> 
