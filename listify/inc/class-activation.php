<?php

class Listify_Activation {

	public function __construct() {
		add_action( 'add_option_job_manager_installed_terms', array( $this, 'enable_categories' ) );
		add_action( 'after_switch_theme', array( $this, 'after_switch_theme' ), 10, 2 );

		add_action( 'admin_notices', array( $this, 'google_maps_api_key_notice' ) );
		add_action( 'wp_ajax_listify_google_maps_api_notice_dismiss', array( $this, 'listify_google_maps_api_notice_dismiss' ) );
	}

	/**
	 * Get the current version of the parent theme.
	 *
	 * @since 1.9.0
	 *
	 * @return int
	 */
	public function get_theme_version() {
		// Try to theme based on standard install location.
		$theme = wp_get_theme( 'listify' );

		// Get current active theme otherwise. 
		if ( ! $theme->exists() ) {
			$theme = wp_get_theme();
		}

		// Get the parent theme if it exists.
		if ( $theme->get_template() ) {
			$theme = wp_get_theme( $theme->get_template() );
		}

		return $theme->get( 'Version' );
	}

	public function upgrade( $run ) {
		$upgrade = '_upgrade_' . $run;
	
		if ( method_exists( $this, $upgrade ) ) {
			$this->$upgrade();
		}
	}

	public function after_switch_theme( $theme, $old ) {
		// If it's set just update version can cut out
		if ( get_option( 'listify_version' ) ) {
			$this->set_version();

			return;
		}

		// Don't let WP Job Manager run its setup guide
		update_option( 'wp_job_manager_version', 100 );

		// Don't let WooCommerce run its setup guide (sorry)
		update_option( 'woocommerce_version', 100 );
		update_option( 'woocommerce_cart_page_id', -1 );

		$this->flush_rules();
		$this->set_version();
		$this->enable_categories();
		$this->redirect();
	}

	public function set_version() {
		update_option( 'listify_version', $this->get_theme_version() );
	}

	public function flush_rules() {
		flush_rewrite_rules();
	}

	public function enable_categories() {
		update_option( 'job_manager_enable_categories', 1 );
	}

	public function redirect() {
		unset( $_GET[ 'action' ] );

		if ( class_exists( 'Astoundify_Setup_Guide' ) ) {
			wp_safe_redirect( Astoundify_Setup_Guide::get_page_url() );
			exit();
		}
	}

	/**
	 * Display a notice until a Google Maps API key is entered or this
	 * notice is dismissed.
	 *
	 * @since 1.5.3
	 * @return void
	 */
	public function google_maps_api_key_notice() {
		if ( '' != get_theme_mod( 'map-behavior-api-key', '' ) || get_option( 'listify-google-maps-api-notice', false ) ) {
			return;
		}

		wp_enqueue_script( 'wp-util' );
?>

<div class="listify-google-maps-api-notice notice notice-error is-dismissible">
	<p><?php printf( 
		__( '<strong>You have not entered a Google Maps API key!</strong> You will not have access to certain features of Listify. %s', 'listify' ), 
		'<a href="' . esc_url_raw( admin_url( 'customize.php?autofocus[control]=map-behavior-api-key' ) ) . '">' . __( 'Add an API key &rarr;', 'listify' ) . '</a>' 
	); ?></p>
</div>

<script>
jQuery(function($) {
	$( '.listify-google-maps-api-notice' ).on( 'click', '.notice-dismiss', function(e) {
		e.preventDefault();

		wp.ajax.send( 'listify_google_maps_api_notice_dismiss', {
			data: {
				security: '<?php echo wp_create_nonce( 'listify-google-maps-api-notice' ); ?>'
			}
		} );
	});
});
</script>

<?php
	}

	/**
	 * Persist notice dismiss.
	 *
	 * @since 1.5.3
	 * @return void
	 */
	public function listify_google_maps_api_notice_dismiss() {
		check_ajax_referer( 'listify-google-maps-api-notice', 'security' );

		add_option( 'listify-google-maps-api-notice', true );

		wp_send_json_success();
	}
}

$GLOBALS[ 'listify_activation' ] = new Listify_Activation();
