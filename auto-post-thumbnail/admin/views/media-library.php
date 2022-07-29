<?php
$ajaxloader        = WAPT_PLUGIN_URL . '/admin/assets/img/ajax-loader-line.gif';
$apt_content_nonce = wp_create_nonce( 'apt_content' );
$post_id           = - 1;
if ( isset( $_GET['post_id'] ) ) {
	$post_id = absint( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : - 1;
}


?>

<?php if ( WAPT_Plugin::app()->premium->is_activate() ) : ?>
	<div class="watson-categories">
		<div id="ajaxloader-watson" style="display: none">
			<img src="<?php echo esc_url_raw( $ajaxloader ); ?>" alt="">
		</div>
		<div id="message"></div>
		<div class="categories">
			<ul id="categories-list">
				<li></li>
			</ul>
		</div>
	</div>
<?php endif; ?>

<div class="tabs">
	<ul>
		<?php
		$i = 1;
		foreach ( $this->sources as $src => $slug ) {
			if ( '_skip' === $slug ) {
				continue;
			}

			$is_pro = '';
			if ( empty( $slug ) && ! WAPT_Plugin::app()->premium->is_activate() ) {
				$is_pro = ' (PRO)';
			}
			$is_pro = "<sup class='wapt-sup-pro'>" . $is_pro . '</sup>';

			echo "<li id='tabs-" . intval( $i ++ ) . "'>" . esc_html( strtoupper( $src ) ) . $is_pro . '</li>';
		}
		?>
	</ul>
	<div id='ajaxloader' style='display:none;'>
		<img src='<?php echo esc_url_raw( $ajaxloader ); ?>' width='150px' alt=''>
	</div>
	<div id="media-frame-content">
		<?php
		foreach ( $this->sources as $src => $slug ) {
			if ( '_skip' === $slug ) {
				continue;
			}

			echo "<div id='tab-" . esc_attr( strtolower( $src ) ) . "' class='tab'></div>";
		}
		?>
	</div>
</div>

<style>
	sup
	{
		font-size: 10px;
	}

	.tabs
	{
		display: inline-block;
		width: 100%;
		margin: 5px 0px 10px 0px;
	}

	.tabs > div
	{
		padding-top: 10px;
	}

	.tabs > ul
	{
		margin: 0px;
		padding: 0px;
	}

	.tabs > ul:after
	{
		content: "";
		display: block;
		clear: both;
		height: 1px;
		background: #008ec2;
	}

	.tabs > ul li
	{
		cursor: pointer;
		display: block;
		float: left;
		padding: 10px 0;
		background: #f1f1f1;
		color: #0073aa;
		width: 15%;
		border-radius: 10px 10px 0 0;
		font-weight: bold;
		text-align: center;
	}

	.tabs > ul li.active, .tabs ul li.active:hover
	{
		background: #008ec2;
		color: #ffffff;
		width: 15%;
	}

	.tabs > ul li:hover
	{
		background: #008ec2;
		color: #dddddd;
	}

	.tabs > ul li
	{
		margin-bottom: 0;
	}

	.tab
	{
		padding: 10px;
	}

	#ajaxloader
	{
		margin: 20px 10px 10px 30px;
	}

	#page_num_div
	{
		display: inline;
		font-weight: bold;
		padding: 20px;
	}

	.apt_pages
	{
		padding-top: 20px;
	}

	.divform
	{
		line-height: 1.5;
		margin: 1em 0;
		max-width: 500px;
		position: relative;
	}

	.input_query
	{
		width: 100%;
		padding: 7px 32px 7px 9px;
	}

	.submit_button
	{
		height: 90%;
		width: 70px;
		border: 0;
		cursor: pointer;
		position: absolute;
		right: 0px;
		top: 2px;
		outline: 0;
	}

	.custom-media-button
	{
		float: right;
		padding: 0px 20px 20px 0px;
		position: absolute;
		right: 0px;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery.fn.lightTabs = function (options) {

			var createTabs = function () {
				tabs = this;
				i = 0;

				showPage = function (i) {
					jQuery(tabs).children("div").children("div").hide();
					jQuery(tabs).children("ul").children("li").removeClass("active");
					jQuery('#' + jQuery(tabs).children("div").children("div").attr('id')).html('');

					jQuery('#' + jQuery(tabs).children("div").children("div").eq(i).attr('id')).html('');
					jQuery(tabs).children("div").children("div").eq(i).show();
					jQuery(tabs).children("ul").children("li").eq(i).addClass("active");

					jQuery('#ajaxloader').show();
					jQuery.post(ajaxurl, {
						action: 'source_content',
						source: jQuery(tabs).children("div").children("div").eq(i).attr('id'),
						wpnonce: '<?php echo esc_attr( $apt_content_nonce ); ?>',
						post_id: <?php echo intval( $post_id ); ?>,
					}).done(function (content) {
						jQuery('#ajaxloader').hide();
						if (jQuery(tabs).children("ul").children("li").eq(i).hasClass("active")) {
							jQuery('#' + jQuery(tabs).children("div").children("div").eq(i).attr('id')).html(content);
						}

						if (typeof window.search_query !== 'undefined') {
							jQuery(".input_query").val(window.search_query);
							jQuery(".submit_button").click();
						}
					});

				};

				showPage(0);

				jQuery(tabs).children("ul").children("li").each(function (index, element) {
					jQuery(element).attr("data-page", i);
					i++;
				});

				jQuery(tabs).children("ul").children("li").click(function () {
					showPage(parseInt(jQuery(this).attr("data-page")));
				});
			};
			return this.each(createTabs);
		};

		jQuery(".tabs").lightTabs();

		jQuery("#ajax-watson").on('click', function () {
			jQuery("#ajaxloader-watson").css('display', 'block');
			jQuery.post(ajaxurl, {
				action: 'apt_api_watson',
				postId: <?php echo intval( $post_id ); ?>,
				nonce: "<?php echo esc_attr( wp_create_nonce( 'apt_api_watson' ) ); ?>"
			}, function (response) {
				console.log(response);
				if (response.success) {
					jQuery("#ajaxloader-watson").css('display', 'none');
					response.data.categories.forEach(function (category) {
						var ul = jQuery(`<li style="cursor: pointer; color: #007bff" data-label="${category.label}">${category.label} (${(category.score * 100).toFixed(2)}%)</li>`);
						ul.on('click', function () {
							jQuery(".input_query").val(jQuery(this).attr('data-label'));
							jQuery(".submit_button").click();
						});
						jQuery("#categories-list").append(ul);
					});
				} else {
					jQuery("#message").html(response.data.message);
				}
			});
		});
	});
</script>
