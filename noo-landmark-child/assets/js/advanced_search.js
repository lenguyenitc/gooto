// Localized AdvancedSearch

(function($) {

	$( document ).ready(function() {
		$('#google_calender_start_date').on('focus',function(){
			$(this).trigger('blur');
		});
		var app = app || {
			init : function() {
				app.google_calender_fields();
			},

			google_calender_fields : function() {
				var $datepickers = $( '.datepicker input' )
					$timepickers = $( '.timepicker select' );

				$.each( $datepickers, function( index, value ) {
					var $datepicker = $( value );
					$datepicker.datetimepicker({
						timepicker	: false,
						datepicker 	: true,
  						format 		: 'd-m-Y',
  						minDate		: 0,
						beforeShow: function(){$('input').blur();}
					});
				});

				
				$.each( $timepickers, function( index, value ) {
					var $timepicker = $( value );
					/*$timepicker.datetimepicker({
						timepicker	: true,
						datepicker 	: false,
  						step 		: 30,
  						format 		: 'g:i a',
  						formatTime	: 'g:i a',
  						
  						onChangeDateTime : function( date, $input ) {
							if( $input.attr( 'name' ) == 'google_calender_start_time' ) {
								selected_date 	= new Date( date );
								default_time	= ( selected_date.getHours() + 1 ) + ':' + selected_date.getMinutes() + ':' + selected_date.getSeconds();
								$end_date 		= $( '.timepicker input[name="google_calender_end_time"]' );	
								$end_date.datetimepicker({
									defaultTime : default_time,
								});
							}					
						},
					});*/
					/* $timepicker.chosen().change(function( e ) {
			        	$start_time = $( this );
			        	
			        	if( $start_time.attr( 'name' ) == 'google_calender_start_time' ) {
			        		timespan		= AdvancedSearch.time.timespan;
			        		step 			= parseInt( 60 / AdvancedSearch.time.step );
			        		$end_time  		= $( 'select[name="google_calender_end_time"]' );
			        		$old_selected 	= $end_time.find( ':selected' );
			        		value			= $start_time.find(':selected').val();
			        		new_value 		= timespan[ timespan.indexOf( value ) + step ];
			        		$new_selected	= $end_time.find( 'option[value="' + new_value + '"]' );
							
							$old_selected.removeAttr( 'selected' );		        		
							$new_selected.attr( 'selected', 'selected' );  		
							$end_time.trigger( 'chosen:updated' );
			        	}
			        }); */
				});
				
				$('select.tb-nice-select').niceSelect(); 
				$('body').on('click', '.tb-nice-select',function(){
					var height = $(this).find('li').first().innerHeight(),
						count = $(this).find('li').length;
						index = $(this).find('li.selected').index();
					
					console.log(height);
					$(this).find('ul.list').animate({
					  scrollTop: index * height,
					}, 400);
				});
				$timepickers.on('change', function( e ) {
					var $start_time = $( this ),
						$end_time  		= $( 'select[name="google_calender_end_time"]' );
					
					if( $start_time.attr( 'name' ) == 'google_calender_start_time' ) {
						timespan		= AdvancedSearch.time.timespan;
						step 			= parseInt( 60 / AdvancedSearch.time.step );
						value			= $start_time.val();
						new_value 		= timespan[ timespan.indexOf( value ) + step ];
						
						$end_time.val(new_value);
						$end_time.niceSelect('update');
					}
				});
			}
		};

		app.init();

	});

})(jQuery)