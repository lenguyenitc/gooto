<div class="resource_booking_form">
	<form id="resource_booking_form_event_details">
		<h2><strong><?php _e( sprintf( "Booking Ref# %s <br/>%s", $booking_number, $venue_name ), 'noo-landmark' ); ?></strong></h2><br/>

		<p class="form_head"><label for="_start_date"><?php _e( 'Date', 'noo-landmark' ); ?></label></p>
		<p class="form_input"><input id="google_calender_start_date" class="datepicker" type="text" name="_start_date" value="<?php echo $start_date; ?>" placeholder="dd-mm-yyyy" required /></p>

		<p class="form_head"><label for="_start_time"><?php _e( 'Start Time', 'noo-landmark' ); ?></label></p>
		<!-- <p class="form_input"><input class="timepicker" type="text" name="_start_time" value="<?php echo $start_time; ?>" placeholder="24 hour time format ( HH:mm )" required /></p> -->
		<p class="form_input">
			<select name="_start_time" placeholder="<?php _e( 'Start time', 'noo-landmark' ); ?>" class="tb-nice-select" required >
				<option value='' ><?php _e( 'Select', 'noo-landmark' ); ?></option>
				<?php
				foreach( $timespan as $key => $value ) {
					echo "<option value='{$key}' " . selected( $timespan[$start_time], $value, false ) . " >{$value}</option>";
				}
				?>
			</select>
		</p>

		<p class="form_head"><label for="_end_time"><?php _e( 'End Time', '' ); ?></label></p>
		<!-- <p class="form_input"><input class="timepicker" type="text" name="_end_time" value="<?php echo $end_time; ?>" placeholder="24 hour time format ( HH:mm )" required /></p> -->
		<p class="form_input">
			<select name="_end_time" placeholder="<?php _e( 'End time', 'noo-landmark' ); ?>" class="tb-nice-select" required >
				<option value='' ><?php _e( 'Select', 'noo-landmark' ); ?></option>
				<?php
				foreach( $timespan as $key => $value ) {
					echo "<option value='{$key}' " . selected( $timespan[$end_time], $value, false ) . " >{$value}</option>";
				}
				?>
			</select>
		</p>

		<!-- <p class="form_head"><label for="_end_date"><?php _e( 'End Date', 'noo-landmark' ); ?></label></p>
		<p class="form_input"><input class="datepicker" type="text" name="_end_date" value="<?php echo $end_date; ?>" placeholder="yyyy-mm-dd" required /></p> -->

		<p class="form_head"><label for="_timezone"><?php _e( 'Timezone', '' ); ?></label></p>
		<p class="form_input">
			<select name="_timezone" placeholder="Timezone" class="tb-nice-select">
				<option value='' selected><?php _e( 'Select', 'noo-landmark' ); ?></option>
				<?php
				foreach( DateTimeZone::listIdentifiers() as $Timezone ) {
					echo "<option value='{$Timezone}' " . selected( $timezone, $Timezone, false ) . " >{$Timezone}</option>";
				}
				?>
			</select>
		</p>

		<p class="form_head"><label><?php _e( 'Booking fee (added to the venue bill) $1.00', 'noo-landmark' ); ?></label></p>

		<p class="form_head">
			<?php wp_nonce_field( $event_details_nonce ); ?>
			<input type="hidden" name="_booking_number" value="<?php echo $booking_number; ?>" />
			<input type="hidden" name="_property_id" value="<?php echo $property_id; ?>" />
			<button type="submit" name="submit" class="noo-button"><?php _e( 'Continue', 'noo-landmark' ); ?></button>
			<span class="resource_booking_form_loader" style="display: none;"><img src="<?php echo $loader_image_url; ?>"></span>
		</p>
	</form>
	<style type="text/css">
		.resource_booking_form_event_details_dialog {
			/* position: fixed !important; */
			z-index: 9999 !important;
		}
		#resource_booking_form_event_details .form_head {
			margin: 0;
		}
		#resource_booking_form_event_details .form_head label {
			font-size: 16px;
		}
		#resource_booking_form_event_details .form_input .tb-nice-select {
			width: 100%;
			margin-bottom: 15px;
		}
		#resource_booking_form_event_details .form_input .tb-nice-select > ul.list {
			height: 200px;
			overflow-y: scroll;
			width: 100%;
		}
		#resource_booking_form_event_details .form_input input {
			-webkit-tap-highlight-color: transparent;
			background-color: #fff;
			border-radius: 5px;
			border: solid 1px #e8e8e8;
			box-sizing: border-box;
			clear: both;
			cursor: pointer;
			display: block;
			float: left;
			font-family: inherit;
			font-size: 14px;
			font-weight: normal;
			height: 42px;
			line-height: 40px;
			outline: none;
			padding-left: 18px;
			padding-right: 30px;
			position: relative;
			text-align: left !important;
			-webkit-transition: all 0.2s ease-in-out;
			transition: all 0.2s ease-in-out;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
			white-space: nowrap;
			margin-bottom: 15px;
		}
	</style>
	<script type="text/javascript">
		jQuery( document ).ready(function() {
			jQuery('#google_calender_start_date').on('focus',function(){
				jQuery(this).trigger('blur');
			});
			jQuery('select.tb-nice-select').niceSelect(); 
			jQuery('body').on('click', '.tb-nice-select',function(){
				var height = jQuery(this).find('li').first().innerHeight(),
					count = jQuery(this).find('li').length;
					index = jQuery(this).find('li.selected').index();

				jQuery(this).find('ul.list').animate({
				  scrollTop: index * height,
				}, 400);
			});
		});
	</script>
</div>
