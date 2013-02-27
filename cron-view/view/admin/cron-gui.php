<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="wrap" id="cron-gui">
	<div id="icon-tools" class="icon32"><br /></div>
	<h2><?php _e( 'What\'s in Cron?', 'cron-view' ); ?></h2>


	<h3><?php _e('Available schedules', 'cron-view'); ?></h3>
	
	<ul>
		<?php foreach( $schedules as $schedule ) { ?>
			<li><strong><?php echo $schedule[ 'display' ]; ?></strong>, every <?php echo human_time_diff( 0, $schedule[ 'interval' ] ); ?></li>
		<?php } ?>
	</ul>

	<h3><?php _e('Events', 'cron-view'); ?></h3>

	<table class="widefat fixed">
		<thead>
			<tr>
				<th scope="col"><?php _e('Next due (GMT/UTC)', 'cron-view'); ?></th>
				<th scope="col"><?php _e('Schedule', 'cron-view'); ?></th>
				<th scope="col"><?php _e('Hook', 'cron-view'); ?></th>
				<th scope="col"><?php _e('Arguments', 'cron-view'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $cron as $timestamp => $cronhooks ) { ?>
				<?php foreach ( (array) $cronhooks as $hook => $events ) { ?>
					<?php foreach ( (array) $events as $event ) { ?>
						<tr>
							<th scope="row"><?php echo $event[ 'date' ]; ?> (<?php echo $timestamp; ?>)</th>
							<td>
								<?php 
									if ( $event[ 'schedule' ] ) {
										echo $schedules [ $event[ 'schedule' ] ][ 'display' ]; 
									} else {
										?><em><?php _e('One-off event', 'cron-view'); ?></em><?php
									}
								?>
							</td>
							<td><?php echo $hook; ?></td>
							<td><?php if ( count( $event[ 'args' ] ) ) { ?>
								<ul>
									<?php foreach( $event[ 'args' ] as $key => $value ) { ?>
										<strong>[<?php echo $key; ?>]:</strong> <?php echo $value; ?>
									<?php } ?>
								</ul>
							<?php } ?></td>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	
</div>
