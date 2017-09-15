<?php
/**
* 
*/
class Property_Resource_Integration {
	
	protected $gapi 				= '';

	function __construct(){
		try {
			$this->init();
			
			add_action( 'add_meta_boxes', array( &$this, 'add_property_metaboxes'), 99, 2 );
			add_action( 'save_post_noo_property', array( &$this, 'save_property_metaboxes'), 99, 3 );

		} catch( Exception $e ) {
			print_r( $e->getMessage() );
		}
	}

	protected function init() {
		$this->gapi 				= Google_Calender_API_Connection::get_instance();
	}

	public function get_google_calender_resources( $customer = 'my_customer', $optParams = array() ) {
		$service = $this->gapi->Google_Service_Directory();
		$results = $service->resources_calendars->listResourcesCalendars( $customer, $optParams );
		
		return $results->getItems();
	}

	public function add_property_metaboxes( $post_type, $post ) {

		$id				= 'google_calender_resource'; 
		$title			= __( 'Google Caleder Resource', 'noo-landmark' );
		$callback 		= array( &$this, 'google_calender_resource_html' ); 
		$screen 		= 'noo_property';
		$context 		= 'side';
		$priority 		= 'default';
		$callback_args 	= null;

		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );
	}

	public function google_calender_resource_html( $post ) {
		$resources 		= $this->get_google_calender_resources();
		$resource_id	= get_post_meta( $post->ID, '_google_calender_resource_id', true ); 

		echo "<div class='noo-control'><select name='google_calender_resource_id'>";
		echo "<option value=''>" . __( "-select-", "noo-landmark" ) . "</option>";

		foreach( $resources as $resource ) {
			echo "<option value='{$resource->resourceId}' " . selected( $resource_id, $resource->resourceId, false ) . ">{$resource->resourceName}</option>";
		}

		echo "</select></div>";

	}

	public function save_property_metaboxes( $post_ID, $post, $update ) {
		if( isset( $_POST['google_calender_resource_id'] ) ) {
			return update_post_meta( $post_ID, '_google_calender_resource_id', sanitize_text_field( $_POST['google_calender_resource_id'] ) );
		}
	}
}

new Property_Resource_Integration();

?>