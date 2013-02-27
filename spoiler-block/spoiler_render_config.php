<style>
	.donate{width:400px;padding:5px;margin-top:20px}
	.donate p{text-align:center}
	.donate .wrap-paypal{width:160px;margin:0px auto}
	
	.wrap-config{width:400px;border-radius:5px;border:1px solid #ECECEC;padding:5px}
	.wrap-config input{margin-left:10px}
	#alert{width:300px}
	label span{display:none}
</style>
<?php
if(isset($_POST["alert"]) && !empty($_POST["alert"])):
	update_option("spoiler_alert", $_POST["alert"]);
	?>
	<div id="message" class="updated">
		<p>
			<strong><?php _e("Congratulations! Your settings is updated! :D",SPOILERBLOCK_TEXTDOMAIN);?></strong>
		</p>
	</div>
	<?php
endif;
?>
<div class="wrap">
	<h2><?php _e("Spoiler Block Configuration",SPOILERBLOCK_TEXTDOMAIN);?></h2>
	<p><?php _e("This page is where you'll configure your plugin.<br />Let's go :)",SPOILERBLOCK_TEXTDOMAIN);?></p>
	
	<div class="wrap-config">
		<form action="" method="post" accept-charset="utf-8">		
			<table border="0">
				<tr>
					<td><label for="alert"><?php _e("Spoiler Alert:",SPOILERBLOCK_TEXTDOMAIN);?><span><?php _e("This alert  message will replace your spoilers. You need let your visitors know that they need to click in this message.",SPOILERBLOCK_TEXTDOMAIN);?></span></label></td>
					<td><input type="text" name="alert" value="<?php echo get_option("spoiler_alert");?>" id="alert" />
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" name="submit" value="Save" id="submit" /></td>
				</tr>
			</table>
		</form>
	</div>
		
	<div class="donate postbox">
		<p><?php _e("If you like my plugin, please buy me a Coke",SPOILERBLOCK_TEXTDOMAIN);?></p>
		<div class="wrap-paypal">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="DJSP4CU6YUL98">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
</div>