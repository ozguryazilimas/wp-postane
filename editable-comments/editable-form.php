<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head profile="http://gmpg.org/xfn/11">
<script type="text/javascript">
function dialog_validate(){
	return true;
}
</script>
<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL; ?>/editable-comments/editable-comments.css" />
</head>
<body>
<div id="dialog_content">
<h1><?php _e('Edit comment','editablecomments'); ?></h1>
<form action="<?php echo get_permalink().'#comment-'.$editable_comment->comment_ID; ?>" method="post" id="dialog_commentform" onsubmit="return dialog_validate()">
	<input type="hidden" name="editable_comments_form" id="editable_comments_form" value="1" />
	<input type="hidden" name="comment_ID" id="dialog_comment_ID" value="<?php echo $editable_comment->comment_ID; ?>" />
	<p id="dialog_loader"><img src="<?php echo WP_PLUGIN_URL; ?>/editable-comments/dialog/loadingAnimation.gif" alt="loading..." /></p>
	<p><textarea name="comment" id="dialog_comment" cols="100%" rows="10" tabindex="1"><?php echo $editable_comment->comment_content; ?></textarea></p>
	<p id="editable_comment_buttons">
		<input name="submit" type="submit" id="submit" tabindex="2" value="<?php _e('Update','editablecomments'); ?>" class="button ui-button ui-state-default ui-corner-all"/>
		<?php if($options['dialog'] == 1){ ?>
		<input type="button" tabindex="3" value="<?php _e('Cancel','editablecomments'); ?>" onclick="jQuery('#dialog').dialog('close');" class="button ui-button ui-state-default ui-corner-all"/>
		<?php } else { ?>
		<a href="<?php echo get_permalink().'#comment-'.$editable_comment->comment_ID; ?>"><?php _e('Cancel','editablecomments'); ?></a>
		<?php } ?>
	</p>
		<?php if($promo){ ?> <a id="editable_comment_ja" href="http://julienappert.com" ><?php _e('Editable Comments, by Julien Appert','editablecomments'); ?></a><?php } ?>
</form>
</div>
</body>
</html>
