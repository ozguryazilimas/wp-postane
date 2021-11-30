<?php
$ajaxloader       = WAPT_PLUGIN_URL . '/admin/assets/img/ajax-loader-line.gif';
$apt_google_nonce = wp_create_nonce( 'apt_api' );

$apt_google_key = WAPT_Plugin::app()->getPopulateOption( 'google_apikey' );
$apt_google_cse = WAPT_Plugin::app()->getPopulateOption( 'google_cse' );

if ( isset( $_REQUEST['post'] ) ) {
	$pid = intval( $_REQUEST['post'] );
} else {
	$pid = 0;
}

if ( $apt_google_key && $apt_google_cse ) {
	?>
	<script>
		window.wapt_no_hits = '<?php esc_html_e( 'No hits', 'apt' ); ?>';
		window.wapt_download_svg = '<?php echo esc_url( WAPT_PLUGIN_URL . '/admin/assets/img/download.svg' ); ?>';
	</script>
	<script type="text/javascript">

		function call_api(query, page = 1) {
			findImages('google', 'apt_api_google', '<?php echo esc_attr( $apt_google_nonce ); ?>', query, page, {
				rights: jQuery("#filter_rights").attr('checked') === 'checked' ? 1 : 0,
			});
		}

		function do_submit() {
			jQuery('#loader_flex-google').show();
			q = jQuery('#query', form).val();
			p = jQuery('#page_num', form).val();

			if (jQuery('#filter_rights', form).is(':checked')) {
				rights = 1;
			} else rights = 0;

			jQuery('#google_results').html('');
			call_api(q, p);
		}

		jQuery('#prev_page').click(function (e) {
			jQuery('#page_num', form).val(parseInt(jQuery('#page_num', form).val(), 10) - 1);
			do_submit();
		});
		jQuery('#next_page').click(function (e) {
			jQuery('#page_num', form).val(parseInt(jQuery('#page_num', form).val(), 10) + 1);
			do_submit();
		});

		//Кнопка поиска
		jQuery(document).ready(function () {
			form = jQuery('#google_images_form');

			form.submit(function (e) {
				e.preventDefault();
				do_submit();
			});
		});

		//загрузка в медиабиблиотеку

		jQuery(document).on('click', '#tab-google .upload_google', function (e) {
			if (jQuery(e.target).is('a')) return;
			//jQuery(document).off('click', '.upload_google');
			// loading animation
			var downdiv = jQuery(this);
			downdiv.addClass('uploading').find('.download img').replaceWith('<img src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/loading.svg' ); ?>" style="height:80px !important">');

			downloadMedia(
					jQuery(this).data('service'),
					jQuery(this).data('url'),
					jQuery("#query").val(),
					<?php echo intval( $pid ); ?>,
					jQuery(this).data('title'),
					'<a href="' + jQuery(this).data('link') + '" target="_blank">' + jQuery(this).data('title') + '</a>',
					'<?php echo esc_attr( $apt_google_nonce ); ?>',
					jQuery(this)
			);

			return false;
		});

	</script>

	<div style="padding:10px 15px 25px">
		<form id="google_images_form" style="margin:0">
			<div class="divform">
				<input id="query" type="text" value="" class="input_query" autofocus
				       placeholder="<?php echo esc_attr__( 'Search...', 'apt' ); ?>">
				<input id="page_num" type="hidden" value="1">
				<button type="submit" class="submit_button" title="<?php echo esc_attr__( 'Search', 'apt' ); ?>">
					<img src="<?php echo esc_url_raw( WAPT_PLUGIN_URL . '/admin/assets/img/search.png' ); ?>">
				</button>
			</div>
			<div style="margin:1em 0;padding-left:2px;line-height:2">
				<label style="margin-right:15px;white-space:nowrap">
					<input type="checkbox"
					       id="filter_rights"><?php echo esc_html__( 'Commercial and derived use', 'apt' ); ?>
				</label>
			</div>
		</form>
		<div id="loader_flex-google" style="display: none;">
			<img src='<?php echo esc_url_raw( $ajaxloader ); ?>' width='100px' alt=''>
		</div>
		<div id="google_results" class="flex-images"></div>
		<div class="apt_pages">
			<button id="prev_page" style="display: none;"><span
						class="dashicons dashicons-arrow-left-alt"></span> <?php echo esc_html__( 'Prev', 'apt' ); ?>
			</button>
			<div id="page_num_div" style="display: none;"></div>
			<button id="next_page" style="display: none;"><?php echo esc_html__( 'Next', 'apt' ); ?> <span
						class="dashicons dashicons-arrow-right-alt"></span>
			</button>
		</div>
	</div>
	<?php
} else {
	?>
	<div><?php echo esc_html__( 'API key is missing. Add it in APT settings', 'apt' ); ?> ->
		<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=wapt_settings-wbcr_apt' ) ); ?>" target="_blank">here</a>
	</div>
	<?php
} ?>
