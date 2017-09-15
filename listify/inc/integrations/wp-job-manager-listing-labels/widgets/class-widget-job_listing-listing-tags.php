<?php
/**
 * Single Listing: Listing Labels
 *
 * @since Listify 1.11.0
 */
class Listify_Widget_Listing_Listing_Labels extends Listify_Widget_Listing_Tags {

	/**
	 * @since 1.14.0
	 */
	public function __construct() {
		$this->widget_name = __( 'Listify - Listing: Labels', 'listify' );

		parent::__construct();
	}

}
