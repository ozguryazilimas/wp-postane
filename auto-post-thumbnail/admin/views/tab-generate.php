<?php
/**
 * @var array $data
 */

$stats           = $data['stats'] ?? [];
$log             = $data['log'] ?? [];
$generate_option = $data['generate_option'];
?>

<div class="wrap" id="wapt-generate-page">
	<div class="factory-bootstrap-467 factory-fontawesome-000">
		<div class="row wapt-statistic-row">
			<div class="wapt-generate-statistic">
				<div class="wapt-chart-container">
					<div class="wapt-chart-wrapper">
						<canvas id="wapt-main-chart" width="200" height="200"
						        data-no_featured_image="<?php echo esc_attr( $stats['no_featured_image'] ); ?>"
						        data-w_featured_image="<?php echo esc_attr( $stats['w_featured_image'] ); ?>"
						        data-errors="<?php echo esc_attr( $stats['error'] ); ?>"
						        style="display: block;">
						</canvas>
					</div>
					<div id="wapt-overview-chart-percent" class="wapt-chart-percent">
						<?php echo esc_attr( trim( $stats['featured_image_percent'] ) ); ?><span>%</span>
					</div>
					<p class="wapt-global-phrase">
						<span class="wapt-total-percent"><?php echo esc_attr( $stats['featured_image_percent'] ); ?>%</span>
						<?php esc_html_e( 'of your posts have a featured image', 'apt' ); ?>
					</p>
				</div>
				<div class="wapt-fillters-form">
					<div class="wapt-row0">
						<div class="row">
							<div id="wapt-overview-chart-legend">
								<ul class="wapt-doughnut-legend">
									<li>
										<span style="background-color:#d6d6d6"></span>
										<?php echo esc_html__( 'Without featured image', 'apt' ); ?> -
										<span class="wapt-num"
										      id="wapt-unset-num"><?php echo intval( $stats['no_featured_image'] ); ?></span>
									</li>
									<li>
										<span style="background-color:#8bc34a"></span>
										<?php echo esc_html__( 'With featured image', 'apt' ); ?> -
										<span class="wapt-num"
										      id="wapt-generated-num"><?php echo intval( $stats['w_featured_image'] ); ?></span>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="wapt-row1">
						<?php
						if ( \WAPT_Plugin::app()->is_premium() ) {
							do_action( 'wapt/filter_form_print' );
						} else {
							$stati = get_post_stati( [
									'_builtin'                  => true,
									'show_in_admin_status_list' => true,
							], 'objects' );

							$post_types = get_post_types( [
									'public'             => true,
									'publicly_queryable' => 1,
							], 'objects', 'or' );
							unset( $post_types['attachment'] ); // удалим attachment

							$categories = get_categories( [
									'taxonomy' => 'category',
									'type'     => 'post',
									'orderby'  => 'name',
									'order'    => 'ASC',
							] );
							?>
							<div class="row wapt-filter-row">
								<div class="col-md-2">
									<label for="filter_posttype"
									       class="apt-filter-label"><?php esc_html_e( 'Post type', 'apt' ); ?></label>
								</div>
								<div class="col-md-10">
									<select name="filter_posttype" id="filter_posttype" class="apt-filter-input">
										<option value="post"><?php echo esc_html__( 'Posts', 'apt' ); ?></option>
										<option value="page"><?php echo esc_html__( 'Pages', 'apt' ); ?></option>
									</select>
								</div>
							</div>

							<div class="row wapt-filter-row wapt-pro-row">
								<div class="col-md-2">
									<label for="filter_poststatus"
									       class="apt-filter-label"><?php esc_html_e( 'Post status', 'aptp' ); ?></label>
								</div>
								<div class="col-md-10">
									<select name="filter_poststatus" id="filter_poststatus" class="apt-filter-input"
									        tabindex="-1">
										<option value="">&nbsp;</option>
										<?php
										foreach ( $stati as $stat ) {
											echo '<option value="' . esc_attr( $stat->name ) . '">' . esc_html( $stat->label ) . '</option>';
										}
										?>
									</select><span>&nbsp;</span>
								</div>
							</div>

							<div class="row wapt-filter-row wapt-pro-row">
								<div class="col-md-2">
									<label for="filter_postcategory"
									       class="apt-filter-label"><?php esc_html_e( 'Post category', 'aptp' ); ?></label>
								</div>
								<div class="col-md-10">
									<select name="filter_postcategory" id="filter_postcategory" class="apt-filter-input"
									        tabindex="-1">
										<option value="">&nbsp;</option>
										<?php
										foreach ( $categories as $cat ) {
											echo '<option value="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . ' (' . (int) $cat->count . ')</option>';
										}
										?>
									</select><span>&nbsp;</span>
								</div>
							</div>

							<div class="row wapt-filter-row wapt-pro-row">
								<div class="col-md-2">
									<label for="filter_startdate"
									       class="apt-filter-label"><?php esc_html_e( 'Date from', 'aptp' ); ?></label>
								</div>
								<div class="col-md-10">
									<input type="text" name="filter_startdate" id="filter_startdate"
									       class="apt-filter-input datepicker" tabindex="-1"><span>&nbsp;</span>
									<label for="filter_enddate"
									       class="apt-filter-label"><?php esc_html_e( 'to', 'aptp' ); ?></label>
									<input type="text" name="filter_enddate" id="filter_enddate"
									       class="apt-filter-input datepicker" tabindex="-1"><span>&nbsp;</span>
								</div>
							</div>
						<?php } ?>

						<div class="row wapt-filter-row">
							<div class="col-md-2">
								<label for="filter_posttype" class="apt-filter-label">
									<?php echo esc_html__( 'Generation method', 'apt' ); ?>
								</label>
							</div>
							<div class="col-md-10">
								<?php echo "<strong>{$generate_option['title']}</strong> <br> {$generate_option['hint']}"; ?>
								<br>
								<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=wapt_settings-wbcr_apt' ) ); ?>">
									<?php echo esc_html__( 'Change method in the settings', 'apt' ); ?>
								</a>
							</div>
						</div>

					</div>
					<div class="wapt-row2">
						<div class="row wapt-filter-row">
							<div class="wapt-statistic-buttons-wrap">
								<div>
									<button class="hide-if-no-js wapt-generate-button"
									        name="generate-post-thumbnails"
									        id="generate-post-thumbnails">
										<?php esc_attr_e( 'Generate Featured images', 'apt' ); ?>
									</button>&nbsp;
									<button class="hide-if-no-js wapt-unset-button"
									        name="delete-post-thumbnails"
									        id="delete-post-thumbnails">
										<?php esc_attr_e( 'Delete Featured images', 'apt' ); ?>
									</button>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="genpostthumbsbar" class="wapt-genpostthumbsbar"
			     style="position:relative;height:40px;display: none;">
				<div id="genpostthumbsbar-percent"
				     style="position:absolute;left:50%;top:50%;margin-left:-25px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
			</div>
			<div class="wapt-statistic-message">
				<p><?php wp_kses_post( 'Note: Thumbnails won\'t be generated for posts that already have post thumbnail or <strong><em>skip_post_thumb</em></strong> custom meta field.', 'apt' ); ?></p>
			</div>
		</div>
		<div class="row wapt-generation-progress">
			<div class="wbcr-factory-page-group-header" style="margin-bottom:0;">
				<strong><?php echo esc_html__( 'Generation log', 'apt' ); ?></strong>
				<p><?php echo esc_html__( 'Generation log shows the last 100 generated images.', 'apt' ); ?></p>
			</div>
			<div class="wapt-table-container">
				<table class="wapt-table">
					<thead>
					<tr>
						<th class="wapt-image-td"></th>
						<th class="wapt-title-td"><?php echo esc_html__( 'Post title', 'apt' ); ?></th>
						<th><?php echo esc_html__( 'Image size', 'apt' ); ?></th>
						<th><?php echo esc_html__( 'Generation type', 'apt' ); ?></th>
						<th><?php echo esc_html__( 'Status', 'apt' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $log as $item ) :
						if ( isset( $item['error_msg'] ) && $item['error_msg'] ) :
							?>
							<tr class="flash wapt-table-item wapt-row-id-<?php echo esc_attr( $item['post_id'] ); ?> wapt-error">
								<td class="wapt-image-td"></td>
								<td class="wapt-title-td">
									<a href="<?php echo esc_url_raw( $item['url'] ); ?>"
									   target="_blank"><?php echo esc_html( $item['title'] ); ?></a>
								</td>
								<td></td>
								<td><?php echo esc_html( $item['type'] ); ?></td>
								<td><?php echo esc_html( $item['error_msg'] ); ?></td>
							</tr>
						<?php else : ?>
							<tr class="flash wapt-table-item wapt-row-id-<?php echo intval( $item['post_id'] ); ?>">
								<td class="wapt-image-td">
									<img height="50" src="<?php echo esc_url_raw( $item['thumbnail_url'] ?? '' ); ?>">
								</td>
								<td class="wapt-title-td">
									<a href="<?php echo esc_url_raw( $item['url'] ); ?>" target="_blank">
										<?php echo esc_html( $item['title'] ); ?></a>
								</td>
								<td><?php echo esc_html( $item['image_size'] ?? '' ); ?></td>
								<td><?php echo esc_html( $item['type'] ); ?></td>
								<td><?php echo esc_html( $item['status'] ); ?></td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
