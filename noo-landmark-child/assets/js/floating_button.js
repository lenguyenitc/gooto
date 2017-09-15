(function($) {

	$( document ).ready(function() {

		var app = app || {
			init : function() {
				app.$booking_button = $('.property_details_booking_button');
				if( app.$booking_button.length ) {
					app.imgTop 			= app.$booking_button.offset().top + app.$booking_button.height();
					app.imgLeft			= app.$booking_button.offset().left - parseInt( app.$booking_button.css( 'margin-left' ) );
					app.navbarHeight	= $( 'div.navbar' ).height() + app.$booking_button.height();
				}
				app.bindEvents();
			},

			bindEvents : function() {
				$( window ).scroll( app.fixDiv );
			},

			fixDiv : function( event ) {
				if ( $( window ).scrollTop() > app.imgTop ){
					app.$booking_button.css({
						'position'	: 'fixed',
						'top'		: app.navbarHeight,
						'left'		: app.imgLeft,
					});
				} else {
					app.$booking_button.css({
						'position'	: 'relative',
						'top'		: 'auto',
						'left'		: 'auto',
					});
				}
			},
		};

		app.init();

	});

})(jQuery)


