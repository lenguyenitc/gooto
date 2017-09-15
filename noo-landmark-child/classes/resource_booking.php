<?php

/**
*
*/
class Resource_Booking {

	private $booking_submit_nonce 	= '';
	protected $gapi 				= '';
	protected $timestart 			= '';
	protected $timeend 				= '';
	protected $timestep 			= '';
	protected $timeformat 			= '';
	protected $timespan				= '';

	public function __construct() {
		$this->init();
		add_action( 'wp_enqueue_scripts', array( &$this, 'resource_booking_scripts' ) );
		add_action( 'noo_before_property_detail_content', array( &$this, 'add_booking_button_in_property_details_page' ), 1, 2 );
		add_action( 'wp_ajax_resource_booking_form_event_details', array( &$this, 'resource_booking_form_event_details' ) );
		add_action( 'wp_ajax_nopriv_resource_booking_form_event_details', array( &$this, 'resource_booking_form_event_details' ) );
		add_action( 'wp_ajax_resource_booking_form_event_details_submit', array( &$this, 'resource_booking_form_event_details_submit' ) );
		add_action( 'wp_ajax_nopriv_resource_booking_form_event_details_submit', array( &$this, 'resource_booking_form_event_details_submit' ) );
		add_action( 'wp_ajax_resource_booking_form_user_details_submit', array( &$this, 'resource_booking_form_user_details_submit' ) );
		add_action( 'wp_ajax_nopriv_resource_booking_form_user_details_submit', array( &$this, 'resource_booking_form_user_details_submit' ) );
		add_action( 'wp_ajax_resource_booking_form_new_search_submit', array( &$this, 'resource_booking_form_new_search_submit' ) );
		add_action( 'wp_ajax_nopriv_resource_booking_form_new_search_submit', array( &$this, 'resource_booking_form_new_search_submit' ) );


	}

	private function init() {
		$this->booking_submit_nonce = array(
			'event_details'	=> 'resource_booking_event_details_submit_nonce',
			'user_details'	=> 'resource_booking_user_details_submit_nonce',
		);

		$this->gapi 	= Google_Calender_API_Connection::get_instance();

		$this->timestart 	= 0;
		$this->timeend 		= 24;
		$this->timestep 	= 30;
		$this->timeformat 	= 12;
		$this->timespan		= timespan_array( $this->timestart, $this->timeend, $this->timestep, $this->timeformat );
	}

	public function change_wp_mail_from( $from_email ) {
		return "book@gooto.com.au";
	}

	public function change_wp_mail_from_name( $from_name ) {
		return "Book Gooto";
	}

	public function add_booking_button_in_property_details_page( $property_id, $agent_id ) {
		include_once( locate_template( 'templates/property_details_booking_button.php' ) );
	}

	public function resource_booking_scripts() {

		if( !wp_script_is( 'chosen', 'enqueued' ) ) {
			wp_enqueue_script( 'chosen' );
		}

		if( !wp_style_is( 'chosen', 'enqueued' ) ) {
			wp_enqueue_style( 'chosen' );
		}

		wp_register_script(
			'jquery-datetimepicker',
			child_theme_url( 'assets/js/jquery.datetimepicker.full.min.js' ),
			array( 'jquery' )
		);

		/*wp_register_script(
			'jquery-timepicker',
			child_theme_url( 'assets/js/timepicker.js' ),
			array( 'jquery' )
		);	*/

		wp_register_script(
			'js_book_resource',
			child_theme_url( 'assets/js/book_resource.js' ),
			array(
				'jquery',
				'jquery-ui-dialog',
				'jquery-datetimepicker',
				// 'jquery-timepicker'
			)
		);

		wp_enqueue_script( 'js_book_resource' );

		wp_localize_script(
			'js_book_resource',
			'BookResource',
			array(
				'ajaxurl'			=> admin_url( 'admin-ajax.php' ),
				'loader_image_html'	=> '<span class="resource_booking_form_loader"><img src="' . child_theme_url( 'assets/images/loader.gif' ) . '"></span>',
				'time'				=> array(
					'timespan'	=> array_keys( $this->timespan ),
					'step'		=> $this->timestep,
				),
			)
		); 

		wp_register_style(
			'jquery-ui-css',
			child_theme_url( 'assets/css/jquery-ui.min.css' ),
			array()
		);

		wp_enqueue_style( 'jquery-ui-css' );

		wp_register_style(
			'jquery-datetimepicker-css',
			child_theme_url( 'assets/css/jquery.datetimepicker.min.css' ),
			array()
		);

		wp_enqueue_style( 'jquery-datetimepicker-css' );

		wp_register_script(
			'floating-button',
			child_theme_url( 'assets/js/floating_button.js' ),
			array( 'jquery' )
		);

		wp_enqueue_script( 'floating-button' );
	}

	protected function next_booking_number( $last_booking_number = "0" ) {

		if( !is_string( $last_booking_number ) ) {
			throw new Exception( "Error Processing Booking Number" );
		}

    	$first 	= substr( $last_booking_number, 0, strlen( $last_booking_number ) -1 );
	    $first 	= !empty( $first ) ? $first : "0";
	    $last 	= substr( $last_booking_number, -1 );

	    if( ord( $last ) == ord( "9" ) ) {
	        $last = "A";
	    } elseif( ord( $last ) == ord( "Z" ) ) {
	        $last = "0";
	        $first = $this->next_booking_number( $first );
	    } else {
	        $last = chr( ord( $last ) + 1 );
	    }

	    if( ord( $first ) != ord( "0" ) ) {
	        $last_booking_number = $first . $last;
	    } else {
	        $last_booking_number = $last;
	    }

	    return $last_booking_number;
	}

	protected function get_last_booking_number() {
		return get_option( "_noo_landmark_last_booking_number", "0" );
	}

	protected function save_last_booking_number( $booking_number ) {

		$updated = false;

		if( is_string( $booking_number ) ) {
			$updated = update_option( "_noo_landmark_last_booking_number", $booking_number );
		}

		return $updated;
	}

	protected function generate_booking_number() {

		$last_booking_number 	= $this->get_last_booking_number();
		$booking_number 		= $this->next_booking_number( $last_booking_number );

		$this->save_last_booking_number( $booking_number );

		return $booking_number;
	}

	public function resource_booking_form_event_details() {
		if( isset( $_POST['form_data'] ) && !empty( $_POST['form_data'] ) ) {
			$form  = '';
			parse_str( $_POST['form_data'], $form );

			$bookings_setting 		= get_option( 'bookings_setting' );
			$google_calender 		= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';

			$property_id 			= absint( $form['property_id'] );
			$property 				= get_post( $property_id );
			$venue_name 			= $property->post_title;
			$start_date 			= isset( $form['start_date'] ) ? $form['start_date'] : '';
			$start_time 			= isset( $form['start_time'] ) ? $form['start_time'] : '';
			$end_time 				= isset( $form['end_time'] ) ? $form['end_time'] : '';
			$end_date 				= isset( $form['end_date'] ) ? $form['end_date'] : '';
			$timespan 				= $this->timespan;
			$timezone 				= isset( $form['timezone'] ) ? $form['timezone'] : '';

			$default_timezone		= isset( $google_calender['default_timezone'] ) ? $google_calender['default_timezone'] : '';
			$timezone 				= !empty( $timezone ) ? $timezone : $default_timezone;

			$event_details_nonce 	= $this->booking_submit_nonce['event_details'];
			$booking_number 		= isset( $form['booking_number'] ) ? $form['booking_number'] : $this->generate_booking_number();
			$loader_image_url		= child_theme_url( 'assets/images/loader.gif' );
			include( locate_template( 'templates/resource_booking_form_event_details.php' ) );
		}

		die;
	}

	public function resource_booking_form_event_details_submit() {

		if( isset( $_POST['form_data'] ) ) {
			$form = '';
			parse_str( $_POST['form_data'], $form );

			if( isset( $form['_wpnonce'] ) && wp_verify_nonce( $form['_wpnonce'], $this->booking_submit_nonce['event_details'] ) ) {

				unset( $form['_wpnonce'] );
				unset( $form['_wp_http_referer'] );

				require_once( child_theme_path( 'assets/php/calender-api-client/google_calender_api_connection.php' ) );

				try {
					$gapi = Google_Calender_API_Connection::get_instance();
					$service = $gapi->Google_Service_Calendar();

					$available = $this->is_resource_available( $service, $form );

					if( $available ) {
						$bookings_setting 		= get_option( 'bookings_setting' );
						$google_calender 		= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';

						$property_id 			= absint( $form['_property_id'] );
						$property 				= get_post( $property_id );
						$venue_name 			= $property->post_title;
						$booking_number 		= isset( $form['_booking_number'] ) ? $form['_booking_number'] : "0";
						$start_date 			= isset( $form['_start_date'] ) ? $form['_start_date'] : '';
						$start_time 			= isset( $form['_start_time'] ) ? $form['_start_time'] : '';
						$end_time 				= isset( $form['_end_time'] ) ? $form['_end_time'] : '';
						$end_date 				= isset( $form['_end_date'] ) ? $form['_end_date'] : '';
						$timezone 				= isset( $form['_timezone'] ) ? $form['_timezone'] : '';

						$default_timezone		= isset( $google_calender['default_timezone'] ) ? $google_calender['default_timezone'] : '';
						$timezone 				= !empty( $timezone ) ? $timezone : $default_timezone;

						$terms_and_conditions_page	= isset( $google_calender['terms_and_conditions_page'] ) ? $google_calender['terms_and_conditions_page'] : '';
						$terms_and_conditions_page	= $terms_and_conditions_page ? get_page_link( $terms_and_conditions_page ) : 'javascript:void(0);';
						$privacy_policy_page		= isset( $google_calender['privacy_policy_page'] ) ? $google_calender['privacy_policy_page'] : '';
						$privacy_policy_page		= $privacy_policy_page ? get_page_link( $privacy_policy_page ) : 'javascript:void(0);';

						$user_details_nonce 	= $this->booking_submit_nonce['user_details'];
						$loader_image_url		= child_theme_url( 'assets/images/loader.gif' );

						include( locate_template( 'templates/resource_booking_form_user_details.php' ) );
					} else {
						$bookings_setting 		= get_option( 'bookings_setting' );
						$google_calender 		= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';

						$property_id 			= absint( $form['_property_id'] );
						$booking_number 		= isset( $form['_booking_number'] ) ? $form['_booking_number'] : "0";
						$start_date 			= isset( $form['_start_date'] ) ? $form['_start_date'] : '';
						$start_time 			= isset( $form['_start_time'] ) ? $form['_start_time'] : '';
						$end_time 				= isset( $form['_end_time'] ) ? $form['_end_time'] : '';
						$end_date 				= isset( $form['_end_date'] ) ? $form['_end_date'] : '';
						$timezone 				= isset( $form['_timezone'] ) ? $form['_timezone'] : '';

						$default_timezone		= isset( $google_calender['default_timezone'] ) ? $google_calender['default_timezone'] : '';
						$timezone 				= !empty( $timezone ) ? $timezone : $default_timezone;
						$loader_image_url	= child_theme_url( 'assets/images/loader.gif' );

						include( locate_template( 'templates/resource_booking_form_not_available.php' ) );
					}

				} catch( Exception $e ) {
					$response = array(
						'status'	=> 'error',
						'message'	=> $e->getMessage(),
					);

					wp_send_json( $response );
				}
			}
		}

		die;
	}

	public function resource_booking_form_user_details_submit() {

		if( isset( $_POST['form_data'] ) ) {
			$form = '';
			parse_str( $_POST['form_data'], $form );

			if( isset( $form['_wpnonce'] ) && wp_verify_nonce( $form['_wpnonce'], $this->booking_submit_nonce['user_details'] ) ) {

				unset( $form['_wpnonce'] );
				unset( $form['_wp_http_referer'] );

				require_once( child_theme_path( 'assets/php/calender-api-client/google_calender_api_connection.php' ) );

				try {
					$gapi = Google_Calender_API_Connection::get_instance();
					$service = $gapi->Google_Service_Calendar();

					$available = $this->is_resource_available( $service, $form );

					if( $available ) {
						$calendarId = 'primary';

						$property_id	= absint( $form['_property_id'] );
						$property 		= get_post( $property_id );
						$booking_number	= $form['_booking_number'];
						$first_name 	= $form['_first_name'];
						$last_name 		= $form['_last_name'];
						$mobile 		= $form['_mobile'];
						$email 			= is_email( $form['_email'] ) ? $form['_email'] : '';

						$agent_id		= get_post_meta( $property_id, 'agent_responsible', true );
						$agent_name		= '';
						$agent_email	= '';
						$agent_phone	= '';
						$agent_mobile	= '';

						if( $agent_id ) {
							$agent 			= get_post( $agent_id );
							$agent_name		= $agent->post_title;
							$agent_email	= get_post_meta( $agent_id, 'noo_agent_email', true );
							$agent_phone	= get_post_meta( $agent_id, 'noo_agent_phone', true );
							$agent_mobile	= get_post_meta( $agent_id, 'noo_agent_mobile', true );
						}

						$description 	= __( 'Hi ', 'noo-landmark' ) . $first_name . __(',','noo-landmark'). '<br>';
						$description 	= __( 'Thank you for using gooto to book your meeting.', 'noo-landmark' ) . '<br><br>';
						
						$description 	.= __( 'To confirm booking, click \'Yes\'', 'noo-landmark' ) . '<br>';
						$description 	.= __( 'Note: Please allow 30 minutes for us notify ', 'noo-landmark' ) .$agent_name .__(' of your confirmation. You will receive a further update after this has been done.','noo-landmark'). '<br><br>';
						$description 	.= __( 'To cancel booking, click \'No\'', 'noo-landmark' ) . '<br>';
						$description 	.= __( 'To change the time, update the event\'s time', 'noo-landmark' ) . '<br>';
						$description 	.= __( 'To change the venue, click \'No\', cancel the booking and make a new booking.', 'noo-landmark' ) . '<br><br>';
						$description	.= __( 'Venue details:', 'noo-landmark' ) . '<br>';
						$description	.= __('Contact: ','noo-landmark').$agent_name . '<br>';
						$description	.= __( 'Ph: ', 'noo-landmark' ) . $agent_phone . '<br>';
						$description	.= __( 'Email: ', 'noo-landmark' ) . $agent_email . '<br>';
						$description 	.= __( 'If you have any further questions or specific requests with the venue, please contact the venue directly.', 'noo-landmark' ) . '<br><br>';
						
						$description	.= __( 'Your details: ', 'noo-landmark' );
						$description	.= __('Name: ','noo-landmark').$first_name . ' ' . $last_name . '<br>';
						$description	.= __('Ph: ','noo-landmark').$mobile . '<br>';
						$description	.= __('Email: ','noo-landmark').$email . '<br><br>';

						$summary		= __( sprintf( "%s (Ref# %s) ", $property, $booking_number->post_title ), 'noo-landmark' );
						/*$summary		= __( sprintf( "Gooto Booking Ref# %s for %s", $booking_number, $property->post_title ), 'noo-landmark' );*/


						$location		= $property->post_title;
						$location		.= ', ';
						$location 		.= get_post_meta( $property_id, 'address', true );
						$seating		= get_post_meta( $property_id, 'noo_property_seating', true );
						$seating 		= absint( $seating ) ? absint( $seating ) : 4;

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

						$attendees 	= 	array(
							array(
								"email"				=> $email,
								"additionalGuests"	=> 0,
								"displayName"		=> $first_name . ' ' . $last_name,
								"optional"			=> false,
								"organizer"			=> true,
								"resource"			=> false,
								"responseStatus"	=> "needsAction",
								"self"				=> false
							),
						);

						$resource 		= $this->fetch_google_resource_calender_from_property( $property_id );

						if( $resource !== null ) {
							$attendees[] = array(
								"email"		=> $resource->resourceEmail,
								"organizer"	=> false,
								"resource"	=> true,
							);
						}

						$extended_properties 	= array(
							"private"	=> array(
								"property_id"		=> $property_id,
								"booking_number"	=> $booking_number,
							),
						);

						$event = $gapi->Google_Service_Calendar_Event(
							array(
								"summary" 					=> $summary,
								"start"						=> array(
									"dateTime"	=> $start->format( 'c' ),
									"timeZone"	=> $timezone
								),
								"end"						=> array(
									"dateTime"	=> $end->format( 'c' ),
									"timeZone"	=> $timezone
								),
								"location"					=> $location,
								"description"				=> strip_tags(str_replace('<br>','
',$description)),
								"attendees" 				=> $attendees,
								"extendedProperties" 		=> $extended_properties,
								"guestsCanModify"			=> true,
								"guestsCanInviteOthers"		=> true,
								"guestsCanSeeOtherGuests"	=> true,
								"reminders"		=> array(
									"useDefault" => true,
									"overrides"	 => array(
										"method" => "popup",
										"minutes" => 60,
									),
								),
							)
						);

						$optional_params = array(
							'maxAttendees'			=> $seating,
							'sendNotifications'		=> true,
							'supportsAttachments'	=> false
						);

						$results = $service->events->insert( $calendarId, $event, $optional_params );

						if( !empty( $results ) ) {

							$this->send_email_invoice( $results, $form );

							$response = array(
								'status'	=> 'success',
								'message'	=> __( 'A calendar event has been sent to ', 'noo-landmark' ) . '<strong>' . $email . '</strong><p><p>' . __( 'Open the invitation and click YES to confirm the booking.', 'noo-landmark' ),
							);
						} else {
							$response = array(
								'status'	=> 'error',
								'message'	=> __( 'Could not create event due to some error', 'noo-landmark' ),
							);
						}

						wp_send_json( $response );

					} else {
						$bookings_setting 		= get_option( 'bookings_setting' );
						$google_calender 		= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';

						$property_id 			= absint( $form['_property_id'] );
						$booking_number 		= isset( $form['_booking_number'] ) ? $form['_booking_number'] : "0";
						$start_date 			= isset( $form['_start_date'] ) ? $form['_start_date'] : '';
						$start_time 			= isset( $form['_start_time'] ) ? $form['_start_time'] : '';
						$end_time 				= isset( $form['_end_time'] ) ? $form['_end_time'] : '';
						$end_date 				= isset( $form['_end_date'] ) ? $form['_end_date'] : '';
						$timezone 				= isset( $form['_timezone'] ) ? $form['_timezone'] : '';

						$default_timezone		= isset( $google_calender['default_timezone'] ) ? $google_calender['default_timezone'] : '';
						$timezone 				= !empty( $timezone ) ? $timezone : $default_timezone;
						$loader_image_url	= child_theme_url( 'assets/images/loader.gif' );

						include( locate_template( 'templates/resource_booking_form_not_available.php' ) );
					}
				} catch( Exception $e ) {
					$response = array(
						'status'	=> 'error',
						'message'	=> $e->getMessage(),
					);

					wp_send_json( $response );
				}
			}
		}

		die;
	}

	public function resource_booking_form_new_search_submit() {
		wp_send_json( array(
			'url'	=> trailingslashit( get_option( 'siteurl' ) ) . '#search',
		) );
	}


	public function is_resource_available( $service, $resource ) {
		$resource_available = false;

		$start_date 	= isset( $resource['_start_date'] ) ? sanitize_text_field( $resource['_start_date'] ) : '';
		$end_date 		= isset( $resource['_start_date'] ) ? sanitize_text_field( $resource['_start_date'] ) : '';
		// $end_date 		= isset( $resource['_end_date'] ) ? sanitize_text_field( $resource['_end_date'] ) : '';
		$start_time 	= isset( $resource['_start_time'] ) ? sanitize_text_field( $resource['_start_time'] ) : '';
		$end_time 		= isset( $resource['_end_time'] ) ? sanitize_text_field( $resource['_end_time'] ) : '';
		$timezone 		= isset( $resource['_timezone'] ) ? sanitize_text_field( $resource['_timezone'] ) : '';

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

		$property_id	= absint( $resource['_property_id'] );
		$resource 		= $this->fetch_google_resource_calender_from_property( $property_id );

		if( $resource !== null ) {
			$calendarId 		= $resource->resourceEmail;
			$service_calendar 	= $this->gapi->Google_Service_Calendar();

			$items 				= new Google_Service_Calendar_FreeBusyRequestItem();
			$items->setId( $calendarId );

			$postBody			= new Google_Service_Calendar_FreeBusyRequest();
			$postBody->setTimeMin( $start->format( 'c' ) );
			$postBody->setTimeMax( $end->format( 'c' ) );
			$postBody->setTimeZone( $timezone );
			$postBody->setItems( array( $items ) );

			$optParams 			= array();

			$freebusy 			= $service_calendar->freebusy->query( $postBody, $optParams );

			$resource_available = empty( $freebusy->getCalendars()[$calendarId]->getBusy() );
		}

		return $resource_available;
	}

	protected function fetch_google_resource_calender_from_property( $property_id = 0 ) {
		$resource_id 	= get_post_meta( $property_id, '_google_calender_resource_id', true );

		if( !empty( $resource_id ) ) {
			$customer			= 'my_customer';
			$calendarResourceId	= $resource_id;
			$optParams 			= array();

			$service_directory 	= $this->gapi->Google_Service_Directory();
			$resource 			= $service_directory->resources_calendars->get( $customer, $calendarResourceId, $optParams );

			if( $resource instanceof Google_Service_Directory_CalendarResource ) {
				return $resource;
			}
		}

		return null;
	}

	protected function send_email_invoice( $event, $form ) {

		add_filter( 'wp_mail_from', array( &$this, 'change_wp_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( &$this, 'change_wp_mail_from_name' ) );

		include_once( child_theme_path( 'classes/resource_booking_menu.php' ) );

		$placeholders		= Bookings_Menu::get_email_invoice_placeholders();
		$replacements		= Bookings_Menu::get_email_invoice_replacements( $event, $form );

		$bookings_setting 	= get_option( 'bookings_setting' );
		$email_invoice 		= $bookings_setting['email']['invoice'];
		$subject 			= isset( $email_invoice['_subject'] ) ? $email_invoice['_subject'] : '';
		$subject 			= str_replace( array_keys( $placeholders ), $replacements, $subject );
		$message 			= isset( $email_invoice['_message'] ) ? $email_invoice['_message'] : '';
		$message 			= str_replace( array_keys( $placeholders ), $replacements, $message );
		$to 				= is_email( $form['_email'] ) ? $form['_email'] : '';
		$headers 			= array(
			'Content-Type: text/html; charset=UTF-8'
		);
		$attachments 		= array();
		$escaped_url = (isset($_POST['link_url']))?$_POST['link_url']:'';
		$link_search = '<a href="' . $escaped_url . '">Clickin</a>';
		$message_book = '
		  Hi, Gooto! <br>
		  You have an order from website gooto.com.au with the info full bellow:
		  <br>
		  <p><br /><strong>Booking Detatils</strong><br />Ref : [booking_number]<br />Venue : [booking_venue]<br />Date and Time : [booking_date] [booking_start_time] - [booking_end_time]</p>
		  <p><strong>Customer Details</strong><br />Name: [customer_first_name] [customer_last_name]<br />Contact: [customer_contact]<br />Email: [customer_email]</p>
		  <p><strong>Link book:</strong><br />'.$link_search.'</p>
		';
		$message_book 	= str_replace( array_keys( $placeholders ), $replacements, $message_book );
		//wp_mail( 'book@gooto.com.au', $subject, $message_book, $headers);

		$headers2 			= array(
			'Content-Type: text/html; charset=UTF-8'
		);
		$headers2[] = 'Cc: Book Gooto<book@gooto.com.au>';
		$headers2[] = 'Cc: book@gooto.com.au';

		wp_mail( $to, $subject, $message, $headers2, $attachments );
	}
}

new Resource_Booking();

?>
