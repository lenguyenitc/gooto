<div class="resource_booking_form">
		<p class="form_head"><label><?php _e( 'The venue is not available for the times requested. Please perform a new search', 'noo-landmark' ); ?></label></p>

		<form id="resource_booking_form_new_search">
			<button type="submit" name="new_search" class="noo-button"><?php _e( 'New Search', 'noo-landmark' ); ?></button>
		</form>
		<form id="resource_booking_form_edit_booking">
			<input type="hidden" name="start_date" value="<?php echo $start_date; ?>" />
			<input type="hidden" name="start_time" value="<?php echo $start_time; ?>" />
			<input type="hidden" name="end_time" value="<?php echo $end_time; ?>" />
			<input type="hidden" name="timezone" value="<?php echo $timezone; ?>" />
			<input type="hidden" name="booking_number" value="<?php echo $booking_number; ?>" />
			<input type="hidden" name="property_id" value="<?php echo $property_id; ?>" />
			<button type="submit" name="edit_booking" class="noo-button"><?php _e( 'Edit Booking', 'noo-landmark' ); ?></button>
		</form>
		<span class="resource_booking_form_loader" style="display: none;"><img src="<?php echo $loader_image_url; ?>"></span>
</div>