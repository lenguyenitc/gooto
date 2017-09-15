<?php
/**
 * Plugin integrations.
 *
 * @todo make this less terrible. Mixed method types.
 */
class Listify_Integration {

	public static $integrations = array();

	/**
	 * Does this integration need the customizer?
	 *
	 * @since 1.8.0
	 * @type bool
	 */
	public $has_customizer = false;

	public function __construct() {
		$dir = trailingslashit( get_template_directory() . '/inc/integrations/' . $this->integration() );
		$url = trailingslashit( get_template_directory_uri() . '/inc/integrations/' . $this->integration() );

		self::$integrations[ $this->integration() ] = array(
			'dir' => $dir,
			'url' => $url
		);

		$this->includes();
		$this->setup_actions();

		if ( $this->has_customizer ) {
			$this->customizer();
		}

		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Add automatic customizer support for integrations.
	 *
	 * In the integration directory create the following structure:
	 *
	 *   class-integration.php
	 *   customizer/
	 *     defitions/
	 *       panels/*.php
	 *       controls/*.php
	 *       sections/*.php
	 *     output-styles/*.php
	 *
	 * @since 1.8.0
	 */
	public function customizer() {
		// frontend
		add_action( 'after_setup_theme', array( $this, 'customizer_output_styles' ) );

		// backend
		add_action( 'customize_register', array( $this, 'customizer_setup_panels' ), 10 );
		add_action( 'customize_register', array( $this, 'customizer_setup_sections' ), 20 );
		add_action( 'customize_register', array( $this, 'customizer_setup_controls' ), 30 );
	}

	/**
	 * Output custom CSS based on control values.
	 * 
	 * @since 1.8.0
	 */
	public function customizer_output_styles() {
		foreach ( glob( $this->get_dir() . '/customizer/definitions/output-styles/*.php' ) as $file ) {
			include_once( $file );
		}
	}

	/**
	 * Register and modify panels.
	 *
	 * @since 1.8.0
	 *
	 * @param object $wp_customize WP_Customize_Manager
	 */
	public function customizer_setup_panels( $wp_customize ) {
		foreach ( glob( $this->get_dir() . '/customizer/definitions/panels/*.php' ) as $file ) {
			include_once( $file );
		}
	}

	/**
	 * Register and modify sections.
	 *
	 * @since 1.8.0
	 *
	 * @param object $wp_customize WP_Customize_Manager
	 */
	public function customizer_setup_sections( $wp_customize ) {
		foreach ( glob( $this->get_dir() . '/customizer/definitions/sections/*.php' ) as $file ) {
			include_once( $file );
		}
	}

	/**
	 * Register and modify controls.
	 *
	 * @since 1.8.0
	 * 
	 * @param object $wp_customize WP_Customize_Manager
	 */
	public function customizer_setup_controls( $wp_customize ) {
		foreach ( glob( $this->get_dir() . '/customizer/definitions/controls/*.php' ) as $file ) {
			include_once( $file );
		}
	}

	public function body_class( $classes ) {
		$classes[] = $this->integration;

		return $classes;
	}

	public function integration() {
		return $this->integration;
	}

	private function includes() {
		if ( ! isset( $this->includes ) ) {
			return;
		}

		foreach ( $this->includes as $file ) {
			require_once( trailingslashit( self::get_dir() ) . $file );
		}
	}

	public static function get_integrations() {
		return self::$integrations;
	}

	public function get_dir() {
		return self::$integrations[ $this->integration() ][ 'dir' ];
	}

	public function get_url() {
		return self::$integrations[ $this->integration() ][ 'url' ];
	}

}
