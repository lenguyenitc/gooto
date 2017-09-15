<?php
/**
 * WP Job Manager - Listing Labels
 *
 * @since 1.10.0
 */
class Listify_WP_Job_Manager_Listing_Labels extends Listify_Integration {

	/**
	 * @since 1.10.0
	 */
    public function __construct() {
        $this->integration = 'wp-job-manager-listing-labels';
        $this->includes = array(
			'../wp-job-manager-labels/widgets/class-widget-job_listing-labels.php',
            'widgets/class-widget-job_listing-listing-labels.php',
        );

        parent::__construct();
    }

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.10.0
	 */
    public function setup_actions() {
		$listing_labels = WPJMLL_Front_Setup::get_instance();
        remove_filter( 'the_job_description', array( $listing_labels, 'display_labels' ) );

        add_action( 'widgets_init', array( $this, 'widgets_init' ) );
    }

	/**
	 * Register widgets.
	 *
	 * @since 1.10.0
	 */
    public function widgets_init() {
        register_widget( 'Listify_Widget_Listing_Listing_Labels' );
    }

}

$GLOBALS[ 'listify_job_manager_listing_labels' ] = new Listify_WP_Job_Manager_Listing_Labels();
