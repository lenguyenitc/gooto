<?php

/**
* 
*/
class Bookings_Menu {
	
	public function __construct() {
		// $this->auth_code_redirect();
		add_action( 'admin_init', array( &$this, 'register_bookings_setting' ) );
		add_action( 'admin_menu', array( &$this, 'add_bookings_menu' ) );
		add_action( 'admin_init', array( &$this, 'save_bookings_setting' ) );
	}

	public function register_bookings_setting() {
		register_setting( 'bookings_setting', 'bookings_setting', array( &$this, 'sanitize_bookings_setting' ) );
	}

	protected function auth_code_redirect() {
		if( isset( $_GET['code'] ) ) {
			if( wp_redirect( admin_url( "admin.php?page=noo-landmark-bookings-settings&google_auth_code={$_GET['code']}" ) ) ) {
				exit;
			}
		}
	}

	public function add_bookings_menu() {
		$this->add_menu_page_bookings();
		$this->add_submenu_page_google_calender_settings();
		$this->add_submenu_page_email();
	}

	protected function add_menu_page_bookings() {
		$page_title	= __( 'Bookings', 'noo-landmark' ); 
		$menu_title	= __( 'Bookings', 'noo-landmark' );
		$capability	= 'administrator';
		$menu_slug	= 'noo-landmark-bookings';
		$function 	= array( &$this, 'bookings_menu_render' );
		$icon_url 	= 'dashicons-book';
		$position 	= 3;

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );	
	}

	protected function add_submenu_page_google_calender_settings() {
		$parent_slug 	= 'noo-landmark-bookings';
		$page_title		= __( 'Google Calender & Form Settings', 'noo-landmark' );
		$menu_title		= __( 'Google Calender & Form Settings', 'noo-landmark' );
		$capability		= 'administrator';
		$menu_slug		= 'noo-landmark-bookings-calednder-settings';
		$function 		= array( &$this, 'bookings_submenu_calender_settings_render' );;

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	protected function add_submenu_page_email() {
		$parent_slug 	= 'noo-landmark-bookings';
		$page_title		= __( 'Email', 'noo-landmark' );
		$menu_title		= __( 'Email', 'noo-landmark' );
		$capability		= 'administrator';
		$menu_slug		= 'noo-landmark-bookings-email';
		$function 		= array( &$this, 'bookings_submenu_email_render' );;

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	public function bookings_menu_render() {
		echo "<h3>Coming Soon...</h3>";
	}

	public function bookings_submenu_calender_settings_render() {

		require_once( child_theme_path( 'assets/php/calender-api-client/google_calender_api_connection.php' ) );

		$auth_url	= '';

		if( class_exists( 'Google_Calender_API_Connection' ) ) {
			try {
				$gapi 		= Google_Calender_API_Connection::get_instance();
				$client 	= $gapi->Google_Client();
				$auth_url	= $client->createAuthUrl();
			} catch( Exception $e ) {
				print_r( $e->getMessage() );
			}
		}

		$bookings_setting 			= get_option( 'bookings_setting' );
		$google_calender 			= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';
		$secret_uploaded			= isset( $google_calender['secret_uploaded'] ) ? $google_calender['secret_uploaded'] : '';
		$client_secret_file			= isset( $google_calender['client_secret_file'] ) ? $google_calender['client_secret_file'] : '';
		$auth_code 					= isset( $google_calender['auth_code'] ) ? $google_calender['auth_code'] : '';
		$default_timezone			= isset( $google_calender['default_timezone'] ) ? $google_calender['default_timezone'] : '';
		$terms_and_conditions_page	= isset( $google_calender['terms_and_conditions_page'] ) ? $google_calender['terms_and_conditions_page'] : '';
		$privacy_policy_page		= isset( $google_calender['privacy_policy_page'] ) ? $google_calender['privacy_policy_page'] : '';

		?>
		<form method="POST" enctype='multipart/form-data'>
		<table class="form-table">
			<thead></thead>
			<tbody>
				<tr>
					<th>
						<label for="client_secret_file"><?php _e( 'Upload Client Secret File', 'noo-landmark' ); ?></label>
					</th>
					<td>
						<?php if( !$secret_uploaded ) { ?>
							<input type="file" name="client_secret_file">
						<?php } else { ?>
							<input type="checkbox" name="reupload_client_secret_file"><?php _e( 'Delete old client secret & <strong>Re-Upload</strong> file', 'noo-landmark' ); ?>
						<?php } ?>
					</td>
					
				</tr>
				<tr>
					<th>
						<label for="bookings_setting[google_calender][auth_code]"><?php _e( 'Authentication Code', 'noo-landmark' ); ?></label>
					</th>
					<td>
						<input class="large-text" type="text" name="bookings_setting[google_calender][auth_code]" value="<?php echo $auth_code; ?>" />
					</td>
					<?php if( $auth_url ) { ?>
					<td>
						<a href="<?php echo $auth_url; ?>" target="_blank">
							<input type="button" value="<?php _e( 'Get Authentication Code', 'noo-landmark' ); ?>" />
						</a>
					</td>
					<?php } ?>
				</tr>
				<tr>
					<th>
						<label for="bookings_setting[google_calender][default_timezone]"><?php _e( 'Default Timezone', 'noo-landmark' ); ?></label>
					</th>
					<td>
						<select name="bookings_setting[google_calender][default_timezone]">
							<option value=""><?php _e( 'Select', 'noo-landmark' ); ?></option>
							<?php
							foreach( DateTimeZone::listIdentifiers() as $value ) {
								echo "<option value='{$value}'" . selected( $default_timezone, $value ) . ">$value</option>";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="bookings_setting[google_calender][terms_and_conditions_page]"><?php _e( 'Terms & Condition Page', 'noo-landmark' ); ?></label></th>
					<td><?php
						$args = array(
							'name'				=> 'bookings_setting[google_calender][terms_and_conditions_page]',
							'id'				=> 'terms_and_conditions_page',
							'selected'			=> $terms_and_conditions_page,
							'show_option_none'	=> __( 'Select', 'noo-landmark' ),
							'option_none_value'	=> '',
						); 
						wp_dropdown_pages( $args ); 
					?></td>
				</tr>
				<tr>
					<th><label for="bookings_setting[google_calender][privacy_policy_page]"><?php _e( 'Privacy Policy Page', 'noo-landmark' ); ?></label></th>
					<td><?php
						$args = array(
							'name'				=> 'bookings_setting[google_calender][privacy_policy_page]',
							'id'				=> 'privacy_policy_page',
							'selected'			=> $privacy_policy_page,
							'show_option_none'	=> __( 'Select', 'noo-landmark' ),
							'option_none_value'	=> '',
						); 
						wp_dropdown_pages( $args ); 
					?></td>
				</tr>
				<tr>
					<td>
					<?php wp_nonce_field( 'bookings_setting_nonce' ); ?>
					<?php submit_button( __( 'Update', 'noo-landmark' ), 'primary' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
		</form>
		<?php
	}

	public function bookings_submenu_email_render() {
		
		$bookings_setting 	= get_option( 'bookings_setting' );
		$email_invoice 		= isset( $bookings_setting['email'] ) && isset( $bookings_setting['email']['invoice'] ) ? $bookings_setting['email']['invoice'] : '';
		$subject 			= isset( $email_invoice['_subject'] ) ? $email_invoice['_subject'] : '';
		$message 			= isset( $email_invoice['_message'] ) ? $email_invoice['_message'] : '';

		?>
		<form id="booking_email_invoice_template" method="POST">
			<table class="form-table">
				<thead>
					<tr>
						<th><h3><?php _e( 'Email Template', 'noo-landmark' ); ?></h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>
							<label for="bookings_setting[email][invoice][_subject]"><?php _e( 'Subject', 'noo-landmark' ); ?></label>
						</th>
						<td>
							<input type="text" class="large-text" name="bookings_setting[email][invoice][_subject]" value="<?php echo $subject; ?>" />
						</td>
					</tr>
					<tr>
						<th>
							<label for="bookings_setting[email][invoice][_message]"><?php _e( 'Message', 'noo-landmark' ); ?></label>
						</th>
						<td>
							<?php
								$editor_id = 'textblock' . uniqid();
								wp_editor(
									$message, 
									$editor_id, 
									array(
			                            'media_buttons' => false,
			                            'quicktags' 	=> true,
			                            'textarea_rows' => 50,
			                            'textarea_name' => 'bookings_setting[email][invoice][_message]',
			                            'wpautop' 		=> false
			                        )
	                            );
							?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<p class="description"><strong><?php _e( 'You can use the following placeholder in the email' ); ?></strong></p>
							<?php
								$placeholders = self::get_email_invoice_placeholders();
								foreach( $placeholders as $key => $value) {
									echo "<p class='description'><code>{$key}</code> - {$value}</p>";
								}
							?>
						</td>
					</tr>
					<tr>
						<td>
						<?php wp_nonce_field( 'bookings_setting_nonce' ); ?>
						<?php submit_button( __( 'Update', 'noo-landmark' ), 'primary' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
	}

	public function save_bookings_setting() {
		if( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'bookings_setting_nonce' ) ) {

			$bookings_setting = get_option( 'bookings_setting' );

			/* to sanitize $_POST['bookings_setting'] use callback function sanitize_bookings_setting() */

			$new_booking_setting = array_replace_recursive( (array)$bookings_setting, $_POST['bookings_setting'] );

			update_option( 'bookings_setting', $new_booking_setting );
		}
	}

	public function sanitize_bookings_setting($bookings_setting) {
		/* sanitize $bookings_setting here, if needed */
		if( isset( $_FILES['client_secret_file'] ) ) {
			if( isset( $_FILES['client_secret_file']['name'] ) ) {
				$file_type = pathinfo( $_FILES['client_secret_file']['name'], PATHINFO_EXTENSION );

				if( $file_type == "json" ) {
					
					$file_name = sha1_file( $_FILES['client_secret_file']['tmp_name'] ) . '.' . $file_type;

					if( !empty( $bookings_setting['google_calender']['client_secret_file'] ) ) {
						$old_client_secret_file = child_theme_path( "assets/php/calender-api-client/" . $bookings_setting['google_calender']['client_secret_file'] );
						if( file_exists( $old_client_secret_file ) ) {
							unlink( $old_client_secret_file );
							$bookings_setting['google_calender']['client_secret_file'] = '';
						}
					}

					$successfully_moved = move_uploaded_file( 
						$_FILES['client_secret_file']['tmp_name'],
						child_theme_path( "assets/php/calender-api-client/$file_name" )
					);

					if( $successfully_moved ) {
						$bookings_setting['google_calender']['client_secret_file'] 	= $file_name; 
						$bookings_setting['google_calender']['secret_uploaded']		= true;
						$bookings_setting['google_calender']['auth_code']			= '';

						$old_credentials = child_theme_path( 'assets/php/calender-api-client/credentials/calendar-access-tokens.json' );

						if( file_exists( $old_credentials ) ) {
							unlink( $old_credentials );
						}
					}
				}
			}
			
		}

		if( isset( $_POST['reupload_client_secret_file'] ) ) {
			$bookings_setting['google_calender']['secret_uploaded']	= false;

			if( !empty( $bookings_setting['google_calender']['client_secret_file'] ) ) {
				$old_client_secret_file = child_theme_path( "assets/php/calender-api-client/" . $bookings_setting['google_calender']['client_secret_file'] );
				if( file_exists( $old_client_secret_file ) ) {
					unlink( $old_client_secret_file );
					$bookings_setting['google_calender']['client_secret_file'] = '';
				}
			}
		}

		return $bookings_setting;
	}

	public static function get_email_invoice_placeholders() {

		$placeholders = array(
			'[customer_first_name]'	=> __( 'Cutomers first name', 'noo-landmark' ),
			'[customer_last_name]'	=> __( 'Cutomers last name', 'noo-landmark' ),
			'[customer_email]'		=> __( 'Cutomers email address', 'noo-landmark' ),
			'[customer_contact]'	=> __( 'Cutomers contact number', 'noo-landmark' ),
			'[booking_number]'		=> __( 'Booking number', 'noo-landmark' ),
			'[booking_venue]'		=> __( 'Booking location', 'noo-landmark' ),
			'[booking_date]'		=> __( 'Booking date', 'noo-landmark' ),
			// '[booking_start_date]'	=> __( 'Booking start date', 'noo-landmark' ),
			// '[booking_end_date]'	=> __( 'Booking end date', 'noo-landmark' ),
			'[booking_start_time]'	=> __( 'Booking start time', 'noo-landmark' ),
			'[booking_end_time]'	=> __( 'Booking end time', 'noo-landmark' ),
			'[agent_name]'			=> __( 'Contact person\'s name', 'noo-landmark' ),
			'[agent_email]'			=> __( 'Contact person\'s email', 'noo-landmark' ),
			'[agent_phone]'			=> __( 'Contact person\'s phone', 'noo-landmark' ),
			'[agent_mobile]'		=> __( 'Contact person\'s mobile', 'noo-landmark' ),
			'[venue_image_link]'	=> __( 'Featured image of property with link', 'noo-landmark' ),
			'[venue_link]'			=> __( 'Permalink of property', 'noo-landmark' ),
		);

		return apply_filters( 'email_invoice_placeholders', $placeholders );
	}

	public static function get_email_invoice_replacements( $event, $form ) {
		
		$property_id	= absint( $form['_property_id'] );
		$first_name 	= sanitize_text_field( $form['_first_name'] );
		$last_name 		= sanitize_text_field( $form['_last_name'] );
		$email 			= sanitize_email( $form['_email'] );
		$contact 		= sanitize_text_field( $form['_mobile'] );
		$number			= $event->extendedProperties->private['booking_number'];
		$venue 			= get_the_title( $property_id ) . ', ' . get_post_meta( $property_id, 'address', true );
		$start_date 	= isset( $form['_start_date'] ) ? sanitize_text_field( $form['_start_date'] ) : '';
		$end_date 		= isset( $form['_start_date'] ) ? sanitize_text_field( $form['_start_date'] ) : '';
		// $end_date 		= isset( $form['_end_date'] ) ? sanitize_text_field( $form['_end_date'] ) : '';
		$start_time 	= isset( $form['_start_time'] ) ? sanitize_text_field( $form['_start_time'] ) : '';
		$end_time 		= isset( $form['_end_time'] ) ? sanitize_text_field( $form['_end_time'] ) : '';
		$timezone 		= isset( $form['_timezone'] ) ? sanitize_text_field( $form['_timezone'] ) : '';

		if( $timezone ) {
			$timezone = new DateTimeZone( $timezone );
		} else {
			$timezone = new DateTimeZone( 'UTC' );
		}

		if( $start_date ) {
			$start = new DateTime( $start_date, $timezone );
		} else {
			$start = new DateTime( 'now', $timezone );
		}

		if( $end_date ) {
			$end = new DateTime( $end_date, $timezone );
		} else {
			$end = new DateTime( 'now', $timezone );
		}

		if( $start_time ) {
			$hour 	= date( 'H', strtotime( $start_time ) );
			$minute	= date( 'i', strtotime( $start_time ) );
			$second = date( 's', strtotime( $start_time ) );
			$start->setTime( $hour, $minute, $second );
		}

		if( $end_time ) {
			$hour 	= date( 'H', strtotime( $end_time ) );
			$minute	= date( 'i', strtotime( $end_time ) );
			$second = date( 's', strtotime( $end_time ) );
			$end->setTime( $hour, $minute, $second );
		}

		$agent_id		= get_post_meta( $property_id, 'agent_responsible', true );
		$agent_name		= '';
		$agent_email	= '';
		$agent_phone	= '';
		$agent_mobile	= '';

		if( $agent_id ) {
			$agent_name		= esc_html( get_the_title( $agent_id ) );
			$agent_email	= get_post_meta( $agent_id, 'noo_agent_email', true );
			$agent_phone	= get_post_meta( $agent_id, 'noo_agent_phone', true );
			$agent_mobile	= get_post_meta( $agent_id, 'noo_agent_mobile', true );
		}

		$venue_link			= get_permalink( $property_id );
		$venue_attachment	= wp_get_attachment_image_src( get_post_thumbnail_id( $property_id ) );
		$venue_image_link 	= "<a href='{$venue_link}' ><img src='{$venue_attachment[0]}' ></a>";


		$replacements = array(
			'[customer_first_name]'	=> $first_name,
			'[customer_last_name]'	=> $last_name,
			'[customer_email]'		=> $email,
			'[customer_contact]'	=> $contact,
			'[booking_number]'		=> $number,
			'[booking_venue]'		=> $venue,
			'[booking_date]'		=> $start->format( 'D d-m-Y' ),
			// '[booking_start_date]'	=> $start->format( 'D d-M-y' ),
			// '[booking_end_date]'	=> $end->format( 'D d-M-y' ),
			'[booking_start_time]'	=> $start->format( 'h:ia' ),
			'[booking_end_time]'	=> $end->format( 'h:ia' ),
			'[agent_name]'			=> $agent_name,
			'[agent_email]'			=> $agent_email,
			'[agent_phone]'			=> $agent_phone,
			'[agent_mobile]'		=> $agent_mobile,
			'[venue_image_link]'	=> $venue_image_link,
			'[venue_link]'			=> $venue_link,
		);

		return apply_filters( 'email_invoice_placeholders_replacements', $replacements );
	}
}

new Bookings_Menu();

?>