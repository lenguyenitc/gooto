<div class="resource_booking_form">
	<form id="resource_booking_form_user_details">
		<h2><strong><?php _e( sprintf( "Booking Ref# %s <br/>%s", $booking_number, $venue_name ), 'noo-landmark' ); ?></strong></h2><br/>

		<p class="form_head"><label for="_first_name"><?php _e( 'First Name', 'noo-landmark' ); ?></label></p>
		<p class="form_input"><input type="text" name="_first_name" required /></p>

		<p class="form_head"><label for="_last_name"><?php _e( 'Last Name', 'noo-landmark' ); ?></label></p>
		<p class="form_input"><input type="text" name="_last_name" required /></p>

		<p class="form_head"><label for="_mobile"><?php _e( 'Mobile', 'noo-landmark' ); ?></label></p>
		<p class="form_input"><input type="text" name="_mobile" required /></p>

		<p class="form_head"><label for="_email"><?php _e( 'Email', 'noo-landmark' ); ?></label></p>
		<p class="form_input"> <input type="text" name="_email" required /> </p>

		<p class="form_head"><label class="terms-and-policy"><?php _e( sprintf( "By continuing with your booking, you agree you have read and accept our <a href='%s' target='_blank'><u>User Licence Agreement</u></a> and <a href='%s' target='_blank'><u>Personal Information Statement</u></a>.", $terms_and_conditions_page, $privacy_policy_page ), 'noo-landmark' ); ?></label></p>

		<p class="form_head">
			<?php wp_nonce_field( $user_details_nonce ); ?>
			<input type="hidden" name="_start_date" value="<?php echo $start_date; ?>" />
			<input type="hidden" name="_start_time" value="<?php echo $start_time; ?>" />
			<input type="hidden" name="_end_time" value="<?php echo $end_time; ?>" />
			<input type="hidden" name="_timezone" value="<?php echo $timezone; ?>" />
			<input type="hidden" name="_booking_number" value="<?php echo $booking_number; ?>" />
			<input type="hidden" name="_property_id" value="<?php echo $property_id; ?>" />
			<button type="submit" name="submit" class="noo-button"><?php _e( 'Submit', 'noo-landmark' ); ?></button>
			<span class="resource_booking_form_loader" style="display: none;"><img src="<?php echo $loader_image_url; ?>"></span>
		</p>
	</form>
</div>