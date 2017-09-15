// localized BookResource

(function($){

	$( document ).ready(function() {

		var app = app || {
			init : function() {
				app.elements();
				app.bindEvents();
			},

			elements : function() {
				app.booking_button 			= '#booking_button';
				app.resource_booking_form	= '#resource_booking_form';
				app.form_event_details		= '#resource_booking_form_event_details';
				app.form_user_details		= '#resource_booking_form_user_details';
				app.form_new_search			= '#resource_booking_form_new_search';
				app.form_edit_booking		= '#resource_booking_form_edit_booking';
				app.email_invoice_form		= '#booking_email_invoice_template';
			},

			setupForm : function( form ) {
				var $datepickers = $( form ).find( '.datepicker' ),
					$timepickers = $( form ).find( '.timepicker' );

				$( '._chosen' ).chosen({
		            width: '100%',
		            disable_search_threshold: 10,
		        });

				$.each( $datepickers, function( index, value ) {
					var $datepicker = $( value );
					$datepicker.datetimepicker({
						timepicker	: false,
						datepicker 	: true,
  						format 		: 'd-m-Y',
  						minDate		: 0,
					});
				});

				$.each( $timepickers, function( index, value ) {
					var $timepicker = $( value );
					
					$timepicker.chosen().change(function( e ) {
			        	$start_time = $( this );
			        	
			        	if( $start_time.attr( 'name' ) == '_start_time' ) {
			        		timespan		= BookResource.time.timespan;
			        		step 			= parseInt( 60 / BookResource.time.step );
			        		$end_time  		= $( 'select[name="_end_time"]' );
			        		$old_selected 	= $end_time.find( ':selected' );
			        		value			= $start_time.find(':selected').val();
			        		new_value 		= timespan[ timespan.indexOf( value ) + step ];
			        		$new_selected	= $end_time.find( 'option[value="' + new_value + '"]' );
							
							$old_selected.removeAttr( 'selected' );		        		
							$new_selected.attr( 'selected', 'selected' );  		
							$end_time.trigger( 'chosen:updated' );
			        	}
			        });
				});
			},

			bindEvents : function() {
				// $( document ).on( 'submit', app.booking_button, app.getBookingForm );
				$( document ).on( 'submit', app.booking_button, app.getBookingFormEventDetails );
				// $( document ).on( 'submit', app.resource_booking_form, app.submitBookingForm );
				$( document ).on( 'submit', app.form_event_details, app.submitBookingFormEventDetails );
				$( document ).on( 'submit', app.form_user_details, app.submitBookingFormUserDetails );
				$( document ).on( 'submit', app.form_new_search, app.submitBookingFormNewSearch );
				$( document ).on( 'submit', app.form_edit_booking, app.getBookingFormEventDetails );
				$( document ).on( 'submit', app.email_invoice_form, app.saveEmailInvoiceSettings );
				
			},

			getBookingFormEventDetails : function( event ) {

				event.preventDefault();

				var $this_form 			= $( this ),
					$booking_button		= $this_form.find( '.booking_button_submit' ),
					form_data			= $this_form.serialize(),
					$loader 			= $( BookResource.loader_image_html );
	
				var ajaxRequest = $.ajax({
					method 		: 'POST',
					url			: BookResource.ajaxurl,
					data		: {
						form_data	: form_data,
						action		: 'resource_booking_form_event_details', 
					},
					beforeSend	: function() {
						$booking_button.replaceWith( $loader );
					},
				})
				.done(function(response) {

					if( typeof app.$html != 'undefined' && typeof app.$html.dialog( 'instance' ) != 'undefined' ) {
						app.$html.dialog( 'close' );	
					}
					
					app.$html = $(response);
					$( 'body' ).after( app.$html );
					app.$html.dialog({
						dialogClass	: "resource_booking_form_event_details_dialog",
						modal		: true,
						minWidth	: 500,

						open		: function( event, ui ) {
							$( '.ui-widget-overlay' ).css({
								opacity	: 0.5,
							})
							.on('click', function( event ) {
								app.$html.dialog( 'close' );
							});
						},

						close		: function( event, ui ) {
							$( '.ui-widget-overlay' ).css({
								opacity	: 1
							});

							app.$html.dialog( 'destroy' );
							app.$html.remove();
						}
					});
					app.setupForm( app.form_event_details );
				})
				.complete(function() {
					$loader.replaceWith( $booking_button );
				}); 
				
			},
	
			/*getBookingForm : function( event ) {

				event.preventDefault();

				var $this_form 			= $( this ),
					//property_id				= $this_booking_button.data( 'property_id' ),
					$booking_button		= $this_form.find( '.booking_button_submit' ),
					form_data			= $this_form.serialize(),
					$loader 			= $( BookResource.loader_image_html );

				var ajaxRequest = $.ajax({
					method 		: 'POST',
					url			: BookResource.ajaxurl,
					data		: {
						form_data	: form_data,
						// property_id	: property_id,
						action		: 'resource_booking_form', 
					},
					beforeSend	: function() {
						$booking_button.replaceWith( $loader );
					},
				})
				.done(function(response) {
					app.$html = $(response);
					$( 'body' ).after( app.$html );
					app.$html.dialog({
						dialogClass	: "resource_booking_form_dialog",
						modal		: true,

						open		: function( event, ui ) {
							$( '.ui-widget-overlay' ).css({
								opacity	: 0.5,
							})
							.on('click', function( event ) {
								app.$html.dialog( 'close' );
							});
						},

						close		: function( event, ui ) {
							$( '.ui-widget-overlay' ).css({
								opacity	: 1
							});

							app.$html.dialog( 'destroy' );
							app.$html.remove();
						}
					});
					app.setupForm();
				})
				.complete(function() {
					$loader.replaceWith( $booking_button );
				}); 
				
			},*/

			submitBookingFormEventDetails : function( event ) {
				event.preventDefault();

				var form_data 		= $( this ).serialize(),
					$submit_button	= $( this ).find( 'button[name="submit"]' ),
					$loader 		= $( BookResource.loader_image_html );

				var ajaxRequest = $.ajax({
					method 		: 'POST',
					url			: BookResource.ajaxurl,
					data		: {
						form_data	: form_data,
						action		: 'resource_booking_form_event_details_submit', 
					},
					beforeSend	: function() {
						$submit_button.replaceWith( $loader );
					},
				})
				.done(function(response) {
					if( typeof response.status == 'undefined' ) {
						app.$html.dialog( 'close' );
						app.$html = $( response );
						$( 'body' ).after( app.$html );
						app.$html.dialog({
							dialogClass	: "resource_booking_form_user_details_dialog",
							modal		: true,
							minWidth	: 500,

							open		: function( event, ui ) {
								$( '.ui-widget-overlay' ).css({
									opacity	: 0.5,
								})
								.on('click', function( event ) {
									app.$html.dialog( 'close' );
								});
							},

							close		: function( event, ui ) {
								$( '.ui-widget-overlay' ).css({
									opacity	: 1
								});

								app.$html.dialog( 'destroy' );
								app.$html.remove();
							}
						});
					}
				});
			},

			submitBookingFormUserDetails : function( event ) {
				event.preventDefault();

				var form_data 		= $( this ).serialize(),
					$submit_button	= $( this ).find( 'button[name="submit"]' ),
					$loader 		= $( BookResource.loader_image_html );
				var link_url = window.location.href;
				var ajaxRequest = $.ajax({
					method 		: 'POST',
					url			: BookResource.ajaxurl,
					data		: {
						form_data	: form_data,
						link_url : link_url,
						action		: 'resource_booking_form_user_details_submit', 
					},
					beforeSend	: function() {
						$submit_button.replaceWith( $loader );
					},
				})
				.done(function(response) {
					
					app.$html.dialog( 'close' );

					if( typeof response.status == 'undefined' ) {
						app.$html = $( response );
						$( 'body' ).after( app.$html );
						app.$html.dialog({
							dialogClass	: "resource_booking_form_error_dialog",
							modal		: true,
							minWidth	: 500,

							open		: function( event, ui ) {
								$( '.ui-widget-overlay' ).css({
									opacity	: 0.5,
								})
								.on('click', function( event ) {
									app.$html.dialog( 'close' );
								});
							},

							close		: function( event, ui ) {
								$( '.ui-widget-overlay' ).css({
									opacity	: 1
								});

								app.$html.dialog( 'destroy' );
								app.$html.remove();
							}
						});
					} else {
						if( response.status == "error" ) {
							var error;
							try {
								error = JSON.parse( response.message );
							} catch( exception ) {
								error = response.message;
							}

							if( typeof error == "object" ) {
								message = error.error.message;
							} else {
								message = error;
							}
						} else {
							message = response.message;
						}

						app.submitMessage( message, response );	
					}
				});
			},

			submitBookingFormNewSearch : function( event ) {
				event.preventDefault();

				var $submit_button	= $( this ).find( 'button[name="submit"]' ),
					$loader 		= $( BookResource.loader_image_html );

				var ajaxRequest = $.ajax({
					method 		: 'POST',
					url			: BookResource.ajaxurl,
					data		: {
						action		: 'resource_booking_form_new_search_submit', 
					},
					beforeSend	: function() {
						$submit_button.replaceWith( $loader );
					},
				})
				.done(function(response) {
					$loader.replaceWith( $submit_button );
					app.$html.dialog( 'close' );

					if( typeof response.url != 'undefined' ) {
						window.location.href = response.url;
					}
				});
			},

			/*submitBookingForm : function( event ) {
				event.preventDefault();

				var form_data 		= $( app.resource_booking_form ).serialize(),
					$submit_button	= $( app.resource_booking_form ).find( 'button[name="submit"]' ),
					$loader 		= $( BookResource.loader_image_html );

				var ajaxRequest = $.ajax({
					method 		: 'POST',
					url			: BookResource.ajaxurl,
					data		: {
						form_data	: form_data,
						action		: 'resource_booking_form_submit', 
					},
					beforeSend	: function() {
						$submit_button.replaceWith( $loader );
					},
				})
				.done(function(response) {
					console.log( 'response', response );

					var message = response;

					if( response != "undefined" ) {
						if( response.status == "success" ) {
							message = response.message;
						} else if( response.status == "error" ) {
							var error;
							try {
								error = JSON.parse( response.message );
							} catch( exception ) {
								error = response.message;
							}

							if( typeof error == "object" ) {
								message = error.error.message;
							} else {
								message = error;
							}	
						}

						console.log( 'message', message );
					} else {
						message = "something wrong with response";
					}

					$loader.replaceWith( $submit_button );
					app.$html.dialog( 'close' );

					app.submitMessage( message, response );
				});
			},*/

			submitMessage : function( message, response ) {
				var $message_dialog 	= $( '<span>' + message + '</span>' ); 

				status = response.status ? response.status : 'error';

				$( 'body' ).after( $message_dialog ); 
				$message_dialog
				.addClass( 'submit_message_' + status )
				.dialog({
					dialogClass	: "submit_message_dialog",
					modal		: true,
					minWidth	: 500,
					buttons 	: [
				        {
				            text	: "OK",
				            class 	: 'ok_button',
				            click 	: function() {
				                $( this ).dialog( "close" );
				            }
				        }
				    ],

					open		: function( event, ui ) {
						$( '.ui-widget-overlay' ).css({
							opacity	: 0.5,
						})
						.on('click', function( event ) {
							$message_dialog.dialog( 'close' );
						});;
					},

					close		: function( event, ui ) {
						$( '.ui-widget-overlay' ).css({
							opacity	: 1
						});

						$message_dialog.dialog( 'destroy' );
						$message_dialog.remove();
					}
				});

				// OK button, force style to noo-button as theme
				$( '.submit_message_dialog' )
				.find( '.ok_button' )
				.removeClass()
				.addClass( 'noo-button' )
				.on('hover', function() {
					$( this ).removeClass().addClass( 'noo-button' );
				});
			},

			saveEmailInvoiceSettings : function( event ) {
				event.preventDefault();

				var form_data = $( app.email_invoice_form ).serialize();

				var ajaxRequest = $.ajax({
					method		: 'POST',
					url 		: BookResource.ajaxurl,
					data 		: {
						form_data 	: form_data,
						action		: 'save_email_invoice_settings'
					},
					beforeSend 	: function() {
						console.log('sent');
					}
				})
				.done(function( response ) {

				})
				.complete(function() {

				});
			},
		};

		app.init();

	});

})(jQuery)