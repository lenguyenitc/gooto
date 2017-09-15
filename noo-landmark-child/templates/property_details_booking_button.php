<div class="property_details_booking_button">
	<form id="booking_button">
		<?php
			$start_date 	= isset( $_GET['google_calender_start_date'] ) ? sanitize_text_field( $_GET['google_calender_start_date'] ) : '';
			if( $start_date ) {
				echo "<input type='hidden' name='start_date' value=" . $start_date . " />";
			}

            $end_date     = isset( $_GET['google_calender_start_date'] ) ? sanitize_text_field( $_GET['google_calender_start_date'] ) : '';
			// $end_date 		= isset( $_GET['google_calender_end_date'] ) ? sanitize_text_field( $_GET['google_calender_end_date'] ) : '';
			if( $end_date ) {
				echo "<input type='hidden' name='end_date' value=" . $end_date . " />";
			}

			$start_time 	= isset( $_GET['google_calender_start_time'] ) ? sanitize_text_field( $_GET['google_calender_start_time'] ) : '';
			if( $start_time ) {
				echo "<input type='hidden' name='start_time' value=" . $start_time . " />";
			}

			$end_time 		= isset( $_GET['google_calender_end_time'] ) ? sanitize_text_field( $_GET['google_calender_end_time'] ) : '';
			if( $end_time ) {
				echo "<input type='hidden' name='end_time' value=" . $end_time . " />";
			}

			$timezone 		= isset( $_GET['google_calender_timezone'] ) ? sanitize_text_field( $_GET['google_calender_timezone'] ) : '';

			if( $timezone ) {
				echo "<input type='hidden' name='timezone' value=" . $timezone . " />";
			}
		?>
		<input type="hidden" name="property_id" value="<?php echo $property_id; ?>" />
		<button type="submit" class="noo-button booking_button_submit"><?php _e( 'Book', '' ); ?></button>
	</form>
</div>