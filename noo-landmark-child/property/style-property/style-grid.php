<?php
/**
 * Template Name: Style Grid Property
 *
 * @package LandMark
 * @author  KENT <tuanlv@vietbrain.com>
 */
$url_page_favorites = get_permalink( noo_get_page_by_template( 'my-favorites.php' ) );
$class_column       = !empty( $class_column ) ? $class_column : '';

$primary_field_1      = noo_get_data_field( $property_id, Noo_Property::get_setting( 'primary_field', 'primary_field_1', '_area' ) );
$primary_field_icon_1 = noo_get_data_field_icon( Noo_Property::get_setting( 'primary_field', 'primary_field_icon_1', 'icon-ruler' ) );

$primary_field_2      = noo_get_data_field( $property_id, Noo_Property::get_setting( 'primary_field', 'primary_field_2', '_bedrooms' ) );
$primary_field_icon_2 = noo_get_data_field_icon( Noo_Property::get_setting( 'primary_field', 'primary_field_icon_2', 'icon-bed' ) );

$primary_field_3      = noo_get_data_field( $property_id, Noo_Property::get_setting( 'primary_field', 'primary_field_3', '_garages' ) );
$primary_field_icon_3 = noo_get_data_field_icon( Noo_Property::get_setting( 'primary_field', 'primary_field_icon_3', 'icon-storage' ) );

$primary_field_4      = noo_get_data_field( $property_id, Noo_Property::get_setting( 'primary_field', 'primary_field_4', '_bathrooms' ) );
$primary_field_icon_4 = noo_get_data_field_icon( Noo_Property::get_setting( 'primary_field', 'primary_field_icon_4', 'icon-bath' ) );

$price                = noo_property_price( $property_id );
?>
<div class="noo-property-item <?php echo esc_attr( $class_column ) ?>">
	
	<div class="noo-property-item-wrap">

		<div class="noo-item-head">

			<h4 class="item-title">
				<?php
				/**
				 * Check property is feautred
				 */
				$featured = get_post_meta( $property_id, '_featured', true ); 
				if ( !empty( $featured ) && $featured === 'yes' ) {
					echo '<i class="ion-bookmark">' . esc_html__( 'Featured', 'noo-landmark-core' ) . '</i>';
				}
				?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					<?php the_title(); ?>
				</a>
			</h4>

			<?php if ( !empty( $address ) ) : ?>
				<span class="location">
					<?php echo esc_html( $address ); ?>
				</span>
			<?php endif; ?>

		</div>
		<div class="noo-item-featured">
			<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
				<img src="<?php echo noo_thumb_src( $property_id, 'noo-property-medium' ); ?>" alt="<?php the_title(); ?>" />
			</a>
			<?php
				$property_status = get_the_terms( $property_id, 'property_status' );

	            if ( !empty( $property_status ) && ! is_wp_error( $property_status ) ) {
	                $types = array();
	                foreach( $property_status as $status ) {
	                    $types[] = $status->name;
	                }
	                echo '<span class="property-status">' . implode(', ', $types) . '</span>';
	            }
			?>
		</div>

		<div class="noo-info">
			<?php if ( !empty( $primary_field_1 ) ) : ?>
				<span class="noo-primary-file-1">
					<?php echo wp_kses( $primary_field_icon_1, noo_allowed_html() ); ?>
					<span><?php echo wp_kses( $primary_field_1, noo_allowed_html() ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( !empty( $primary_field_2 ) ) : ?>
				<span class="noo-primary-file-2">
					<?php echo wp_kses( $primary_field_icon_2, noo_allowed_html() ); ?>
					<span><?php echo wp_kses( $primary_field_2, noo_allowed_html() ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( !empty( $primary_field_3 ) ) : ?>
				<span class="noo-primary-file-3">
					<?php echo wp_kses( $primary_field_icon_3, noo_allowed_html() ); ?>
					<span><?php echo wp_kses( $primary_field_3, noo_allowed_html() ); ?></span>
				</span>
			<?php endif; ?>
			<?php if ( !empty( $primary_field_4 ) ) : ?>
				<span class="noo-primary-file-4">
					<?php echo wp_kses( $primary_field_icon_4, noo_allowed_html() ); ?>
					<span><?php echo wp_kses( $primary_field_4, noo_allowed_html() ); ?></span>
				</span>
			<?php endif; ?>
		</div>

		<div class="noo-action">
			<div class="noo-price">
				<?php echo wp_kses( $price, noo_allowed_html() ); ?>
			</div>
			<div class="noo-action-post">
				
				<?php if ( !empty( $show_remove_property ) ) : ?>
					<i class="noo-tooltip-action ion-trash-a" data-id="<?php echo esc_attr( $property_id ); ?>" data-user="<?php echo esc_attr( $user_id ); ?>" data-process="remove_favorites" data-content="<?php echo esc_html__( 'Remove', 'noo-landmark-core' ) ?>"></i>
				<?php endif; ?>
				
				<?php if ( !empty( $show_favories ) && empty( $hide_favories ) ) : ?>
					<i class="noo-tooltip-action fa <?php echo esc_attr( $icon_favorites ); ?>" data-id="<?php echo esc_attr( $property_id ); ?>" data-user="<?php echo esc_attr( $user_id ); ?>" data-process="favorites" data-status="<?php echo esc_attr( $class_favorites ); ?>" data-content="<?php echo esc_html__( 'Favorites', 'noo-landmark-core' ) ?>" data-url="<?php echo esc_attr( $url_page_favorites ); ?>"></i>
				<?php endif; ?>
							
				<?php if ( !empty( $show_social ) && empty( $hide_social ) ) : ?>
					<div class="noo-property-sharing">
						<i class="noo-tooltip-action ion-android-share-alt" data-id="<?php echo esc_attr( $property_id ); ?>" data-process="share"></i>
						<?php noo_social_sharing_property( $property_id ); ?>
					</div>
				<?php endif; ?>

				<?php if ( !empty( $show_compare ) && empty( $hide_compare ) ) : ?>
					<i class="noo-tooltip-action ion-arrow-swap" data-id="<?php echo esc_attr( $property_id ); ?>" data-user="<?php echo esc_attr( $user_id ); ?>" data-process="compare" data-content="<?php echo esc_html__( 'Compare', 'noo-landmark-core' ) ?>" data-thumbnail="<?php echo noo_thumb_src( $property_id, 'noo-property-medium' ); ?>"></i>
				<?php endif; ?>

			</div>
		</div>
		<div class="noo-row">
			<div class="property_listing_booking_button">
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
					<button type="submit" class="noo-button booking_button_submit"><?php _e( 'Book', 'noo-landmark' ); ?></button>
				</form>
			</div>
			<?php
				$more_details  = trailingslashit( get_permalink() );
				if( isset( $_SERVER['QUERY_STRING'] ) && !empty( $_SERVER['QUERY_STRING'] ) ) {
					$more_details .= '?' . $_SERVER['QUERY_STRING'];
				}
			?>
			<a href="<?php echo $more_details; ?>"><button class="noo-button"><?php _e( 'More Detail', 'noo-landmark' ); ?></button></a>
		</div>

	</div><!-- /.noo-property-item-wrap -->

</div><!-- /.noo-property-item -->