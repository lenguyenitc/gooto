<?php
/**
* 
*/
class Theme_Modification {
	
	public function __construct() {
		add_filter( 'noo_locate_template', array( &$this, 'override_noo_locate_template' ), 99, 2 );

		remove_filter( 'noo_after_property_content', 'noo_property_add_box_agent_contact', 10, 3 );
		remove_action( 'noo_after_property_content', 'noo_property_add_box_comment', 20, 2 );
	}

	public function override_noo_locate_template( $located, $template_name ) {

		if( file_exists( child_theme_path( $template_name . '.php' ) ) ) {
			$located = child_theme_path( $template_name . '.php' );
		}

		return $located;
	}
}

new Theme_Modification();

?>