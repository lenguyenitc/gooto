<?php
/**
 * A single listing result.
 *
 * Choose what to output and output content for the `content-single-job_listing.php`
 * template file. 
 *
 * @since 1.8.0
 *
 * @see Listify_WP_Job_Manager_Map_Template
 * @package Listify
 */
class Listify_WP_Job_Manager_Template_Single_Listing {

	/**
	 * WP Job Manager templating class.
	 *
	 * Instead of moving callbacks to this class they need to remain
	 * in their current class until a better backwards compatible
	 * setup can be added. They are also used in more than cards.
	 *
	 * Most callbacks in Listify_WP_Job_Manager_Template that relate to cards
	 * have a mirrored method in this class that checks if it should be displayed
	 * based on the customizer setting.
	 *
	 * @type object
	 */
	public static $template;

	/**
	 * The selected display style.
	 * @type string
	 */
	public static $style;

	public function __construct() {
		add_action( 'after_setup_theme', array( __CLASS__, 'template_tags' ) );
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public static function template_tags() {
		add_action( 'single_job_listing_meta_start', array( __CLASS__, 'the_secondary_image' ), 7 );
	}

	/**
	 * Get WP Job Manager template object.
	 *
	 * This object contains callbacks for outputting the card data.
	 *
	 * @since 1.8.0
	 *
	 * @return object $template
	 */
	public static function get_template() {
		if ( ! self::$template ) {
			global $listify_job_manager;

			self::$template = $listify_job_manager->template;
		}

		return self::$template;
	}

	/**
	 * Maybe display secondary image.
	 *
	 * @since Listify 1.8.0
	 */
	public static function the_secondary_image() {
		if ( ! get_theme_mod( 'single-listing-secondary-image-display', false ) ) {
			return;
		}

		self::get_template()->the_company_image();
	}

}

new Listify_WP_Job_Manager_Template_Single_Listing();
