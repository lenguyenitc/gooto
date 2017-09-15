<?php
/**
 */

global $tgmpa;
?>

<form id="astoundify-content-importer" action="" method="">

	<div id="content-pack">
		<p style="margin: 30px 0 0 -15px">
			<label for="default" class="content-pack">
				<span class="content-pack-label">Business Directory</span>
				<input type="radio" value="default" name="demo_style" id="default" checked="checked" />
				<span class="content-pack-img">
					<span class="dashicons dashicons-yes"></span>
					<img src="<?php echo get_template_directory_uri(); ?>/inc/setup/assets/images/content-default.png" />
				</span>
				<span class="screen-reader-text"><?php _e( 'Default', 'listify' ); ?></span>
			</label>
			<label for="rentals" class="content-pack">
				<span class="content-pack-label">Rentals</span>
				<input type="radio" value="rentals" name="demo_style" id="rentals" />
				<span class="content-pack-img">
					<span class="dashicons dashicons-yes"></span>
					<img src="<?php echo get_template_directory_uri(); ?>/inc/setup/assets/images/content-rentals.png" />
				</span>
				<span class="screen-reader-text"><?php _e( 'Extended', 'listify' ); ?></span>
			</label>
			<label for="coming-soon" class="content-pack">
				<span class="content-pack-label">&nbsp;</span>
				<input type="radio" value="coming-soon" name="" disabled="disabled" id="coming-soon" />
				<span class="content-pack-img">
					<img src="<?php echo get_template_directory_uri(); ?>/inc/setup/assets/images/content-coming-soon.png" />
				</span>
				<span class="screen-reader-text"><?php _e( 'Coming Soon...', 'listify' ); ?></span>
			</label>
		</p>
	</div>

	<div id="import-summary" style="display: none;">
		<p><?php _e( 'Please do not navigate away from this page. This process may take a few minutes depending on your server capabilities and internet connection.', 'listify' ); ?></p>

		<p><strong id="import-status"><?php _e( 'Summary:', 'listify' ); ?></strong></p>

		<?php foreach ( Listify_Setup::$content_importer_strings[ 'type_labels' ] as $key => $labels ) : ?>
		<p id="import-type-<?php echo esc_attr( $key ); ?>" class="import-type">
			<span class="dashicons import-type-<?php echo esc_attr( $key ); ?>"></span>&nbsp;
			<strong class="process-type"><?php echo esc_attr( $labels[1] ); ?>:</strong>
			<span class="process-count">
				<span id="<?php echo esc_attr( $key ); ?>-processed">0</span> / <span id="<?php echo esc_attr( $key ); ?>-total">0</span>
			</span>
			<span id="<?php echo esc_attr( $key ); ?>-spinner" class="spinner"></span>
		</p>
		<?php endforeach; ?>
	</div>

	<ul id="import-errors"></ul>

	<div id="plugins-to-import">
		<p><?php _e( 'Listify requires the following plugins to be active in order to import content.', 'listify' ); ?></p>

		<ul>
		<?php foreach ( Listify_Setup::get_required_plugins() as $key => $plugin ) : ?>
		<li>
			<?php if ( $plugin[ 'condition' ] ) : ?>
				<span class="active"><span class="dashicons dashicons-yes"></span></span>
			<?php else : ?>
				<span class="inactive"><span class="dashicons dashicons-no"></span></span>
			<?php endif; ?>

			<?php echo $plugin[ 'label' ]; ?>

			<?php if ( ! $plugin[ 'condition' ] ) : ?>
			&mdash; <span class="inactive"><?php _e( 'Demo content for this plugin will not be imported.', 'listify' ); ?></span>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
		</ul>

		<p><?php printf( __( 'Want extra features on your site? Activate the following plugins for even more demo content; saving you setup time! Visit your %1$splugins page%2$s to activate any premium plugins you have installed.', 'listify' ), '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">', '</a>' ); ?></p>

		<ul id="astoundify-recommended-plugins">
		<?php foreach ( Listify_Setup::get_recommended_plugins() as $key => $plugin ) : ?>
		<li data-pack="<?php echo implode( ' ', $plugin[ 'pack' ] ); ?>">
			<?php if ( $plugin[ 'condition' ] ) : ?>
				<span class="active dashicons dashicons-yes"></span>
			<?php else : ?>
				<span class="dashicons dashicons-minus" style="color: #b4b9be;"></span>
			<?php endif; ?>

			<?php echo $plugin[ 'label' ]; ?>

			<?php if ( ! $plugin[ 'condition' ] ) : ?>
			<em>(<?php _e( 'Demo content will not be imported for this inactive plugin', 'listify' ); ?>)</em>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
		</ul>
	</div>

	<p>
		<?php submit_button( __( 'Import Content', 'listify' ), 'primary', 'import', false ); ?>
		&nbsp;
		<?php submit_button( __( 'Reset Content', 'listify' ), 'secondary', 'reset', false ); ?>
	</p>

</form>
