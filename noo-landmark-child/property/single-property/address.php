<?php
/**
 * Template Name: Box Address
 *
 * @package LandMark
 * @author  KENT <tuanlv@vietbrain.com>
 */
$location_address      = Noo_Property::get_setting( 'google_map', 'location_address', true );
$location_country      = Noo_Property::get_setting( 'google_map', 'location_country', true );
$location_city         = Noo_Property::get_setting( 'google_map', 'location_city', true );
$location_neighborhood = Noo_Property::get_setting( 'google_map', 'location_neighborhood', true );
$location_zip          = Noo_Property::get_setting( 'google_map', 'location_zip', true );

if ( $location_address || $location_country || $location_city || $location_neighborhood || $location_zip ) :
	$address      = get_post_meta( $property_id, 'address', true );
	$country      = get_post_meta( $property_id, 'country', true );
	$city         = get_post_meta( $property_id, 'city', true );
	$neighborhood = get_post_meta( $property_id, 'neighborhood', true );
	$zip          = get_post_meta( $property_id, 'zip', true );

	$list_country = noo_list_country();
	$list_country = noo_list_country();
	if ( !empty( $country ) ) {
		$key_country = array_search( $country , array_column( $list_country, 'value' ) );
		$country = '';
		if ( !empty( $list_country[$key_country]['label'] ) ) {
			$country = $list_country[$key_country]['label'];
		}
	}
?>
<div class="noo-property-box">
	<h3 class="noo-title-box">
		<?php echo esc_html__( 'Address', 'noo-landmark-core' ); ?>
	</h3>
	<div class="noo-content-box noo-row">
		<?php if ( $location_address || $location_country || $location_city ) : ?>

			<div class="noo-md-6 noo-column-left">

				<?php if ( !empty( $address ) && $location_address ) : ?>
					<div class="noo-content-box-item">
						<label><?php echo esc_html__( 'Address', 'noo-landmark-core' ); ?></label>
						<span><?php echo esc_attr( $address ) ?></span>
					</div>
				<?php endif; ?>

				<?php if ( !empty( $country ) && $location_country ) : ?>
					<div class="noo-content-box-item">
						<label><?php echo esc_html__( 'Country', 'noo-landmark-core' ); ?></label>
						<span><?php echo esc_attr( $country ) ?></span>
					</div>
				<?php endif; ?>

				<?php if ( !empty( $city ) && $location_city ) : ?>
					<div class="noo-content-box-item">
						<label><?php echo esc_html__( 'City', 'noo-landmark-core' ); ?></label>
						<span><?php echo esc_attr( $city ) ?></span>
					</div>
				<?php endif; ?>

			</div><!-- /.noo-column-left -->

		<?php endif; ?>
		
		<?php if ( $location_neighborhood || $location_zip ) : ?>
			
			<div class="noo-md-6 noo-column-right">

				<?php if ( !empty( $neighborhood ) && $location_neighborhood ) : ?>
					<div class="noo-content-box-item">
						<label><?php echo esc_html__( 'Neighborhood', 'noo-landmark-core' ); ?></label>
						<span><?php echo esc_attr( $neighborhood ) ?></span>
					</div>
				<?php endif; ?>

				<?php if ( !empty( $zip ) && $location_zip ) : ?>
					<div class="noo-content-box-item">
						<label><?php echo esc_html__( 'Postcode', 'noo-landmark-core' ); ?></label>
						<span><?php echo esc_attr( $zip ) ?></span>
					</div>
				<?php endif; ?>

			</div><!-- /.noo-column-right -->
		
		<?php endif; ?>

	</div>
</div>
<?php endif; ?>