<?php
error_reporting(E_ALL);
//ini_set('display_errors', 1);
/*
*	Parent stylesheet
*/

add_action( 
	'wp_enqueue_scripts',
	function() {
		wp_enqueue_style( 
			'parent-style', 
			get_template_directory_uri() . '/style.css'
		);
		
		
		wp_enqueue_style( 
			'nice-select-style', 
			get_stylesheet_directory_uri() . '/assets/css/nice-select.css'
		);
		wp_enqueue_script( 
			'nice-select-script', 
			get_stylesheet_directory_uri() . '/assets/js/jquery.nice-select.js',
			array('jquery'),
			true
		);
	}
);

if( ! function_exists( 'child_theme_path' ) ) {
	function child_theme_path( $resource_name = '' ) {		
		return trailingslashit( get_stylesheet_directory() ) . $resource_name; 
	}
}

if( ! function_exists( 'child_theme_url' ) ) {
	function child_theme_url( $resource_name = '' ) {		
		return trailingslashit( get_stylesheet_directory_uri() ) . $resource_name; 
	}
}

if( ! function_exists( 'timespan_array' ) ) {
	function timespan_array( $start = 0, $end = 24, $step = 60, $format = 24 ) {

		$timespan 	= array();
		$start 		= ( absint( $start ) >= 0 && absint( $start ) <= 24 ) ? absint( $start ) : 0;
		$end 		= ( absint( $end ) >= 0 && absint( $end ) <= 24 ) ? absint( $end ) : 24;
		$step 		= ( absint( $step ) >= 0 && absint( $step ) <= 60 ) ? absint( $step ) : 60;
		$format 	= ( absint( $format ) == 12 || absint( $format ) == 24 ) ? absint( $format ) : 24;

		for( $i = $start,$j = 0; $i < $end; $j += $step ) {
			if( $j >= 60 ) {
				$j -= 60;
				$i++;
			}

			if( $i != 24 ) {
				if( $format == 24 ) {
					$timespan[ sprintf( "%02d:%02d", $i, $j ) ] = sprintf( "%02d:%02d", $i, $j );
				} else if( $format == 12 ) {
					$hour 	= ( $i > 12 ) ? $i%12 : $i;
					$hour 	= ( $hour == 0 ) ? 12 : $hour;
					$am_pm 	= ( $i >= 12 ) ? 'pm' : 'am';

					$timespan[ sprintf( "%02d:%02d", $i, $j ) ] = sprintf( "%02d:%02d %s", $hour, $j, $am_pm );
				}
			}
		}

		return $timespan;
	}
}

add_action( 
	'init',
	function() {
		require_once( child_theme_path( 'assets/php/calender-api-client/google_calender_api_connection.php' ) );
		require_once( child_theme_path( 'classes/property_resource_integration.php' ) );
		require_once( child_theme_path( 'classes/theme_modification.php' ) );
		require_once( child_theme_path( 'classes/resource_booking.php' ) );
		require_once( child_theme_path( 'classes/resource_booking_menu.php' ) );
		require_once( child_theme_path( 'classes/shortcode-advanced-search-property.php' ) );
	},
	99
);


if ( ! function_exists( 'noo_property_query_from_request' ) ) {

	function noo_property_query_from_request( &$query, $REQUEST = array() ) {
		if( empty( $query ) || empty( $REQUEST ) ) {
			return $query;
		}

		/**
		 * Fix conflic woocommerce
		 */
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			return;
		}

		$tax_query = noo_property_tax_query( $REQUEST );
		if( !empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
				$query->tax_query->queries = $tax_query;
				$query->query_vars['tax_query'] = $query->tax_query->queries;
			} elseif( is_array( $query ) ) {
				$query['tax_query'] = $tax_query;
			}
		}

		if(isset( $REQUEST['keyword'] ) && !empty( $REQUEST['keyword'] )){
			if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
				$query->set( 's', esc_html( $REQUEST['keyword'] ) );
			} elseif( is_array( $query ) ) {
				$query['s'] = esc_html( $REQUEST['keyword'] );
			}
		}

		$meta_query = array();
		if(isset( $REQUEST['min_area'] ) && !empty( $REQUEST['min_area'] )){
			$min_area['key']      = 'noo_property_area';
			$min_area['value']    = intval($REQUEST['min_area']);
			$min_area['type']     = 'NUMERIC';
			$min_area['compare']  = '>=';
			$meta_query[]     = $min_area;
		}
		if(isset( $REQUEST['max_area'] ) && !empty( $REQUEST['max_area'] )){
			$max_area['key']      = 'noo_property_area';
			$max_area['value']    = intval($REQUEST['max_area']);
			$max_area['type']     = 'NUMERIC';
			$max_area['compare']  = '<=';
			$meta_query[]     = $max_area;
		}
		if(isset( $REQUEST['min_price'] ) && !empty( $REQUEST['min_price'] )){
			$min_price['key']      = 'price';
			$min_price['value']    = floatval($REQUEST['min_price']);
			$min_price['type']     = 'NUMERIC';
			$min_price['compare']  = '>=';
			$meta_query[]     = $min_price;
		}
		if(isset( $REQUEST['max_price'] ) && !empty( $REQUEST['max_price'] )){
			$max_price['key']      = 'price';
			$max_price['value']    = floatval($REQUEST['max_price']);
			$max_price['type']     = 'NUMERIC';
			$max_price['compare']  = '<=';
			$meta_query[]     	   = $max_price;
		}

		/**
		 * Process request country
		 */
		if ( isset( $REQUEST['country'] ) && !empty( $REQUEST['country'] ) ) {
			$country['key']      = 'country';
			$country['value']    = esc_attr( $REQUEST['country'] );
			$meta_query[]     	  = $country;
		}

		/**
		 * Process request Bedrooms
		 */
		if ( isset( $REQUEST['noo_property_bedrooms'] ) && !empty( $REQUEST['noo_property_bedrooms'] ) ) {
			$bedrooms['key']      = 'noo_property_bedrooms';
			$bedrooms['value']    = esc_attr( $REQUEST['noo_property_bedrooms'] );
			$bedrooms['compare']  = '>=';
			$meta_query[]     	  = $bedrooms;
		}

		/**
		 * Process request Bathrooms
		 */
		if ( isset( $REQUEST['noo_property_bathrooms'] ) && !empty( $REQUEST['noo_property_bathrooms'] ) ) {
			$bathrooms['key']      = 'noo_property_bathrooms';
			$bathrooms['value']    = esc_attr( $REQUEST['noo_property_bathrooms'] );
			$bathrooms['compare']  = '>=';
			$meta_query[]     	  = $bathrooms;
		}

		/**
		 * Process request Garages
		 */
		if ( isset( $REQUEST['noo_property_garages'] ) && !empty( $REQUEST['noo_property_garages'] ) ) {
			$garages['key']      = 'noo_property_garages';
			$garages['value']    = esc_attr( $REQUEST['noo_property_garages'] );
			$garages['compare']  = '>=';
			$meta_query[]     	  = $garages;
		}

		/**
		 * Check status property
		 */
		// if( apply_filters( 'noo_hide_unavailable_property', true ) ) {
		// 	$stock['key']      = 'stock';
		// 	$stock['value']    = ( !empty( $REQUEST['stock'] ) ? esc_attr( $REQUEST['stock'] ) : 'available' );
		// 	$meta_query[]      = $stock;
		// }

		$property_fields = noo_property_render_fields();
		if ( !empty( $property_fields ) ) {
			unset($property_fields['']);
			foreach ( $property_fields as $field ) {

				if ( ! array_key_exists( 'name', $field ) ) {
					continue;
				}
				$field_id = 'noo_property' . noo_property_custom_fields_name( $field['name'] );
				if ($field_id == 'noo_property_bedrooms' || $field_id == 'noo_property_bathrooms' || $field_id == 'noo_property_garages') {
					continue;
				}
				if( isset( $REQUEST[$field_id] ) && !empty( $REQUEST[$field_id]) ) {
					$value    = noo_validate_data( $REQUEST[$field_id], $field );
					if( is_array( $value ) ){
						$temp_meta_query = array( 'relation' => 'OR' );
						foreach ($value as $v) {
							if( empty( $v ) ) continue;
							$temp_meta_query[]	= array(
								'key'     => $field_id,
								'value'   => '"'.$v.'"',
								'compare' => 'LIKE'
							);
						}
						$meta_query[] = $temp_meta_query;
					} else {
						$meta_query[]	= array(
							'key'     => $field_id,
							'value'   => esc_attr( $value )
						);
					}
				} elseif( ( isset( $field['type'] ) && $field['type'] == 'datepicker' ) && ( isset( $REQUEST[$field_id.'_start'] ) || isset( $REQUEST[$field_id.'_end'] ) ) ) {
					if( $field_id == 'date' ) {
						$date_query = array();
						if( isset( $REQUEST[$field_id.'_start'] ) && !empty( $REQUEST[$field_id.'_start'] ) ) {
							$start = is_numeric( $REQUEST[$field_id.'_start'] ) ? date('Y-m-d', $REQUEST[$field_id.'_start']) : $REQUEST[$field_id.'_start'];
							$date_query['after'] = date('Y-m-d', strtotime( $start . ' -1 day' ) );
						}
						if( isset( $REQUEST[$field_id.'_end'] ) && !empty( $REQUEST[$field_id.'_end'] ) ) {
							$end = is_numeric( $REQUEST[$field_id.'_end'] ) ? date('Y-m-d', $REQUEST[$field_id.'_end']) : $REQUEST[$field_id.'_end'];
							$date_query['before'] = date('Y-m-d', strtotime( $end . ' +1 day' ) );
						}

						if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
							$query->query_vars['date_query'][] = $date_query;
						} elseif( is_array( $query ) ) {
							$query['date_query'] = $date_query;
						}
					} else {
						$value_start = isset( $REQUEST[$field_id.'_start'] ) && !empty( $REQUEST[$field_id.'_start'] ) ? noo_validate_data( $REQUEST[$field_id.'_start'], $field ) : 0;
						$value_start = !empty( $value_start ) ? strtotime("midnight", $value_start) : 0;
						$value_end = isset( $REQUEST[$field_id.'_end'] ) && !empty( $REQUEST[$field_id.'_end'] ) ? noo_validate_data( $REQUEST[$field_id.'_end'], $field ) : 0;
						$value_end = !empty( $value_end ) ? strtotime("tomorrow", strtotime("midnight", $value_end)) - 1 : strtotime( '2090/12/31');

						$meta_query[]	= array(
							'key'     => $field_id,
							'value'   => array( $value_start, $value_end ),
							'compare' => 'BETWEEN',
							'type'    => 'NUMERIC'
						);
					}
				}
			}
		}

		$property_features = noo_render_featured_amenities();
		if ( !empty( $property_features ) ) {

			foreach ($property_features as $key => $feature) {
				$field_id = 'noo_property' . sanitize_title( $key );
				if( isset( $REQUEST[$field_id] ) && !empty( $REQUEST[$field_id]) ) {
					$meta_query[]	= array(
						'key'   => $field_id,
						'value' => '1'
					);
				}
			}

		}

		$meta_query = apply_filters( 'noo_property_search_meta_query', $meta_query, $REQUEST );
		if( !empty( $meta_query ) ) {
			$meta_query['relation'] = 'AND';
			if( is_object( $query ) && get_class( $query ) == 'WP_Query' ) {
				// $query->query_vars['meta_query'][] = $meta_query; //change
				$query->query_vars['meta_query'] = $meta_query;
			} elseif( is_array( $query ) ) {
				$query['meta_query'] = $meta_query;
			}
		}

		if( isset( $REQUEST['orderby'] ) && !empty( $REQUEST['orderby'] ) ){
			$order 			   = 'DESC';
			$query['orderby']  = 'date';
			$query['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
			$query['meta_key'] = '';
			
			switch ( $REQUEST['orderby'] ) {
				case 'rand' :
					$query['orderby']  = 'rand';
					break;
				case 'date' :
					$query['orderby']  = 'date';
					$query['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
					break;
				case 'bath' :
					$query['orderby']  = "meta_value_num meta_value";
					$query['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					$query['meta_key'] = 'noo_property_bathrooms';
					break;
				case 'bed' :
					$query['orderby']  = "meta_value_num meta_value";
					$query['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					$query['meta_key'] = 'noo_property_bedrooms';
					break;
				case 'area' :
					$query['orderby']  = "meta_value_num meta_value";
					$query['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					$query['meta_key'] = 'noo_property_area';
					break;
				case 'price' :
					$query['orderby']  = "meta_value_num meta_value";
					$query['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					$query['meta_key'] = 'price';
					break;
				case 'featured' :
					$query['orderby']  = "meta_value";
					$query['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					$query['meta_key'] = '_featured';
					break;
				case 'name' :
					$query['orderby']  = 'title';
					$query['order']    = 'ASC'; // $order == 'DESC' ? 'DESC' : 'ASC';
					break;
			}

		}
	}
}

if ( ! function_exists( 'noo_list_property_markers' ) ) {

	function noo_list_property_markers( $args = array() ) {
		global $wpdb;
		
		$defaults = array(
			'post_type'      =>  'noo_property',
			'post_status'    =>  'publish',
			'posts_per_page' => -1
		);

		/**
		 * Get list location
		 */
		$list_country = noo_list_country();
		$location     = array();
		foreach ( $list_country as $country ) {
			$location[] = $country['value'];
		}

		$markers    = array();
		$args       = wp_parse_args( $args, $defaults );
		$wp_query   = new WP_Query($args);
		
		if( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ): $wp_query->the_post();
				$property_id =  get_the_ID();
				$lat         =  get_post_meta( $property_id, 'latitude', true );
				$long        =  get_post_meta( $property_id, 'longitude', true );

				if( empty( $lat ) || empty( $long ) ) {
					continue;
				}
				$title        = wp_trim_words( get_the_title( $property_id ), 7 );
				$image        = noo_thumb_src( $property_id, 'noo-property-medium', '180x150' );
				$price        = get_post_meta( $property_id, 'price', true );
				$country      = get_post_meta( $property_id, 'country', true );
				$city         = get_post_meta( $property_id, 'city', true );
				$neighborhood = get_post_meta( $property_id, 'neighborhood', true );
				$zip          = get_post_meta( $property_id, 'zip', true );
				
				/**
				 * Get list status
				 */
				$property_status       = array();
				$property_status_terms = get_the_terms( $property_id, 'property_status' );
				if( $property_status_terms && !is_wp_error( $property_status_terms ) ) {
					foreach( $property_status_terms as $status_term ){
						if( empty( $status_term->term_id ) ) {
							continue;
						}
						$property_status[] = $status_term->term_id;
					}
				}

				/**
				 * Get list property type
				 */
				$property_type        = array();
				$property_type_terms  = get_the_terms( $property_id, 'property_type' );
				$icon_markers 		  = 'fa-circle';
				if( $property_type_terms && !is_wp_error( $property_type_terms ) ) {
					$map_markers = get_option( 'noo_type_map_markers' );
					foreach( $property_type_terms as $type_term ){
						if( empty( $type_term->term_id ) ) {
							continue;
						}
						$property_type[] = $type_term->term_id;
						$icon_markers = get_term_meta( $type_term->term_id, 'icon_type', true );
						if ( empty( $icon_markers ) ) {
							$icon_markers = 'fa-circle';
						}
					}
				}
				
				$args_property = array(
					'post_type'      =>  'noo_property',
					'post_status'    =>  'publish',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'latitude',
							'compare' => '=',
							'value' => $lat,
						),
						array(
							'key' => 'longitude',
							'compare' => '=',
							'value' => $long,
						)
					)
				 );
				
				//$list_properties = get_posts( $args_property );
				$list_properties = $wpdb->get_results( "SELECT wpgf_posts.* FROM wpgf_posts INNER JOIN wpgf_postmeta ON ( wpgf_posts.ID = wpgf_postmeta.post_id ) INNER JOIN wpgf_postmeta AS mt1 ON ( wpgf_posts.ID = mt1.post_id ) WHERE 1=1 AND ( ( wpgf_postmeta.meta_key = 'latitude' AND wpgf_postmeta.meta_value = '".$lat."' ) AND ( mt1.meta_key = 'longitude' AND mt1.meta_value = '".$long."' ) ) AND wpgf_posts.post_type = 'noo_property' AND ((wpgf_posts.post_status = 'publish')) GROUP BY wpgf_posts.ID ORDER BY wpgf_posts.post_date DESC", OBJECT );
				
				$html_info = '';
				$tb_property_array = array();
				foreach($list_properties as $list_property){
					$p_terms = wp_get_post_terms($list_property->ID, 'property_type', array("fields" => "all"));
					$term_list = '';
					foreach($p_terms as $key => $p_term){
						$term_list .= ($key == (count($p_terms) - 1)) ? $p_term->name : $p_term->name . ', ';
						
					}
					$html_info .= '<div class="noo-property-item-map noo-property-seating-'.noo_format_price( get_post_meta( $list_property->ID, 'noo_property_seating', true ), false ).'">'; 
					$html_info .=	 '<div class="noo-thumbnail">';
					$html_info .=		'<a target="_blank" href="'.get_permalink( $list_property->ID ).'" title="' .wp_trim_words( $list_property->post_title, 7 ). '">';
					$html_info .=			'<img src="' .noo_thumb_src( $list_property->ID, 'noo-property-medium', '180x150' ). '" alt="' .wp_trim_words( $list_property->post_title, 7 ). '" />';
					$html_info .=		'</a>';
					$html_info .=	'</div>';
					$html_info .=	'<div class="noo-content">';
					$html_info .=		'<h4 class="noo-content-title">';
					$html_info .=			'<a target="_blank" href="' .get_permalink( $list_property->ID ). '" title="' .wp_trim_words( $list_property->post_title, 7 ). '">'.wp_trim_words( $list_property->post_title, 7 ).'</a>';
					$html_info .=		'</h4>';
					$html_info .=		'<div class="noo-info">'.$term_list.'</div>';
					$html_info .=		'<div class="noo-action">';
					$html_info .=			'<div class="noo-price"><div><i class="fa fa-users" aria-hidden="true"></i> '.noo_format_price( get_post_meta( $list_property->ID, 'noo_property_seating', true ), false ).'</div></div>';
					$html_info .=			'<a class="more" target="_blank" href="' .get_permalink( $list_property->ID ). '" title="' .wp_trim_words( $list_property->post_title, 7 ). '"><i class="fa fa-plus" aria-hidden="true"></i></a>';
					$html_info .=		'</div>';
					$html_info .=	'</div>';
					$html_info .='</div>'; 
					
					$tb_property_array[] = array(
						'id' => $list_property->ID,
						'seating' => noo_format_price( get_post_meta( $list_property->ID, 'noo_property_seating', true ), false ),
						'noise_level' => get_post_meta( $list_property->ID, 'noo_property_noise', true ),
						'privacy' => get_post_meta( $list_property->ID, 'noo_property_privacy', true ),
						'permalink' => get_permalink( $list_property->ID ),
						'title' => wp_trim_words( $list_property->post_title, 7 ),
						'term_list' => $term_list,
						'image' => noo_thumb_src( $list_property->ID, 'noo-property-medium', '180x150' )
					);
				}
				wp_reset_query();
				wp_reset_postdata();
				/* $html_info .='<script type="text/javascript">';
				$html_info .='(function ( $ ) {';
				$html_info .='alert(1);';
				$html_info .='$(".noo-property-item-map").hide();';
				$html_info .='})( jQuery );';
				$html_info .='</script>'; */
				
				$marker = array(
					'latitude'     => $lat,
					'longitude'    => $long,
					'image'        => $image,
					'title'        => $title,
					'price'        => noo_format_price( $price, false ),
					'price_html'   => noo_property_price( $property_id ),
					'area'         => noo_get_property_area_html( $property_id ),
					'url'          => get_permalink( $property_id ),
					'icon_markers' => $icon_markers,
					'types'        => $property_type,
					'status'       => $property_status,
					'location'     => $country,
					'city'         => $city,
					'neighborhood' => $neighborhood,
					'zip'          => $zip,
					'html_info'		   => $html_info,
					'property_array' => $tb_property_array,
					// 'icon'         => $property_type_marker,
				);

				/**
				 * Show custom fields
				 */
				$custom_fields = noo_property_render_fields();
				$marker_merge  = array();
				foreach ( $custom_fields as $item ) :

					if ( empty( $item['name'] ) ) continue;

					$meta_key = 'noo_property' . $item['name'];

					$value = get_post_meta( $property_id, $meta_key, true );
					
					if ( !is_array( $value ) ) {
						// $marker_merge[ $item['name'] ] = sanitize_title( $value ); //change
						$marker_merge[ $meta_key ] = sanitize_title( $value );
					} else {
						// $marker_merge[ $meta_key ] = $value; //change
						$marker_merge[ $meta_key ] = $value;
					}

				endforeach;
		
				$marker = array_merge( $marker, $marker_merge );

				$markers[] = $marker;
			endwhile;
		}
		
		wp_reset_query();
		wp_reset_postdata();
		
		//echo "<pre>";
		//print_r($markers);
		//die();
		return json_encode( $markers, 512 );

	}
}


if ( ! function_exists( 'noo_advanced_search_fields' ) ) {
	
	function noo_advanced_search_fields( $name, $field = array() ) {

		if ( empty( $name ) || $name === 'none' ) return;
		$prefix = 'noo_property';
		$class  = 'noo-md-3';
		if ( !empty( $field['class'] ) ) $class = esc_attr( $field['class'] );
		switch ( $name ) {
			case 'keyword':
				$keyword = !empty( $_GET['keyword'] ) ? noo_validate_data( $_GET['keyword'] ) : '';
				$args_keyword = array(
					'name'        => 'keyword',
					'title'       => '',
					'type'        => 'text',
					'placeholder' => esc_html__( 'Enter Your Keyword...', 'noo-landmark-core' ),
					'class'		  => $class
				);
				noo_create_element( $args_keyword, $keyword );
				break;
			
			case 'property_status':
				$property_status = noo_get_list_tax( 'property_status' );
				$status          = !empty( $_GET['status'] ) ? noo_validate_data( $_GET['status'] ) : '';		
				$args_status     = array(
					'name'             => 'status',
					'title'            => '',
					'type'             => 'select',
					'placeholder'      => esc_html__( 'Status', 'noo-landmark-core' ),
					'class'            => $class,
					'options'          => $property_status,
					'show_none_option' => true
				);
				noo_create_element( $args_status, $status );
				break;
			
			case 'property_types':
				$property_types = noo_get_list_tax( 'property_type' );
				$types          = !empty( $_GET['types'] ) ? noo_validate_data( $_GET['types'] ) : '';
				$args_types     = array(
					'name'             => 'types',
					'title'            => '',
					'type'             => 'select',
					'placeholder'      => esc_html__( 'Property Types', 'noo-landmark-core' ),
					'class'            => $class,
					'options'          => $property_types,
					'show_none_option' => true
				);
				noo_create_element( $args_types, $types );
				break;
			
			case 'property_country':
				$list_country = noo_list_country();
				$country      = !empty( $_GET['country'] ) ? noo_validate_data( $_GET['country'] ) : '';
				$args_country = array(
					'name'             => 'country',
					'title'            => '',
					'type'             => 'select',
					'placeholder'      => esc_html__( 'Country', 'noo-landmark-core' ),
					'class'            => $class,
					'list'             => true,
					'options'          => $list_country,
					'show_none_option' => true
				);
				noo_create_element( $args_country, $country );
				break;
			
			case 'city':
			case 'neighborhood':
			case 'zip':
				$data_location = !empty( $_GET[$name] ) ? noo_validate_data( $_GET[$name] ) : '';
				global $wpdb;

				$transient_name = 'noo_advanced_search_fields_' . $data_location;

				// if ( false === ( $data_meta = get_transient( $transient_name ) ) ) {

				$data_meta = $wpdb->get_col(
					$wpdb->prepare('
						SELECT DISTINCT meta_value
						FROM %1$s
						LEFT JOIN %2$s ON %1$s.post_id = %2$s.ID
						WHERE meta_key = \'%3$s\' AND post_type = \'%4$s\' AND post_status = \'%5$s\'
						', $wpdb->postmeta, $wpdb->posts, $name, 'noo_property', 'publish'
					)
				);

				set_transient( $transient_name, $data_meta, DAY_IN_SECONDS );

				// }

				$label = '';

				if ( $name === 'city' ){

					$label = esc_html__( 'City', 'noo-landmark-core' );

				} elseif ( $name === 'neighborhood' ){

					$label = esc_html__( 'Neighborhood', 'noo-landmark-core' );

				} elseif ( $name === 'zip' ){

					$label = esc_html__( 'Zip', 'noo-landmark-core' );
					
				}

				$args_location     = array(
					'name'             => $name,
					'title'            => '',
					'type'             => 'select',
					'placeholder'      => sprintf( esc_html__( '%s', 'noo-landmark-core' ), $label ),
					'class'            => $class,
					'show_none_option' => true
				);

				$args_location['options'] = array_combine( $data_meta, $data_meta );

				noo_create_element( $args_location, $data_location );
				break;

			case 'price':
				noo_property_render_price_search_field( array( 'class' => $class, 'label' => esc_html__( 'Price', 'noo-landmark-core' ) ) );
				break;

			case 'noo_property_area':
				noo_property_render_area_search_field( array( 'class' => $class, 'label' => esc_html__( 'Area', 'noo-landmark-core' ) ) );
				break;

			default:
				$field_item              = noo_get_property_field( $name );
				
				if ( !empty( $field_item ) ) {

					/**
					 * Check type is area
					 */
					if ( $field_item['name'] === '_area' ) {
						noo_property_render_area_search_field( array( 'class' => $class ) );
					} else {

						$name_custom_field_item  = esc_attr( $prefix . $field_item['name'] );
						$value_custom_field_item = esc_html( $field_item['value'] );
						
						$label_custom_field_item = '';
						if ( !empty( $field_item['label'] ) ) {
							$label_custom_field_item = isset( $field_item['label_translated'] ) ? $field_item['label_translated'] : $field_item['label'];
						}
						$std_custom_field_item = '';
						if ( !empty( $field_item['std'] ) ) {
							$std_custom_field_item = esc_html( $field_item['std'] );
						}

						$args_custom_field_items = array();

						$field_item['name']        = $name_custom_field_item;
						$field_item['title']       = '';
						$field_item['class']       = $class;

					    if ( in_array( $field_item['type'], noo_has_choice_field_types() ) ) {
					        $list_options = explode( "\n", $field_item['value'] );
					        $options_custom_field_item = array();

					        foreach ($list_options as $option) {
					            $field_item['options'][sanitize_title( $option )] = esc_html( $option );
					        }

					        unset($field_item['std']);

					    } else {
					    	$field_item['placeholder'] = $label_custom_field_item;
					    }

					    /**
					     * Conver type text -> select
					     */
					    if ( $field_item['type'] === 'text' ) {
							$field_item['type'] = 'select';

							$transient_name = 'noo_advanced_search_fields_' . $name_custom_field_item;

							if ( false === ( $data_meta = get_transient( $transient_name ) ) ) {

								global $wpdb;

								$data_meta = $wpdb->get_col(
									$wpdb->prepare('
										SELECT DISTINCT meta_value
										FROM %1$s
										LEFT JOIN %2$s ON %1$s.post_id = %2$s.ID
										WHERE meta_key = \'%3$s\' AND post_type = \'%4$s\' AND post_status = \'%5$s\'
										ORDER BY meta_value',
										$wpdb->postmeta,
										$wpdb->posts,
										$name_custom_field_item,
										'noo_property',
										'publish'
									)
								);

								set_transient( $transient_name, $data_meta, DAY_IN_SECONDS );

							}

							$field_item['options'] = array_combine( $data_meta, $data_meta );
						}
					    
					    if ( $field_item['type'] === 'select' || $field_item['type'] === 'multiple_select' ) {
					    	$field_item['placeholder'] = sprintf( esc_html__( '%s', 'noo-landmark-core' ), esc_html( isset( $field_item['label_translated'] ) ? $field_item['label_translated'] : $field_item['label'] ) );
					    	$field_item['show_none_option'] = true;
					    }

					    unset( $field_item['label'] );
						unset( $field_item['value'] );
						unset( $field_item['readonly'] );

						$field_value = !empty( $_GET[$field_item['name']] ) ? noo_validate_data( $_GET[$field_item['name']] ) : '';
						
						noo_create_element( $field_item, $field_value );

					}

				}
				
				break;
		}

	}

}
add_action('wp_footer','show_form_book_map');
function show_form_book_map(){
		ob_start();
		?> 
		<div id="form_booking_map" style="display:none;">
			<div class="booking_map_wrrap">
				<div class="property_booking_button">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/loader.gif">
					<form id="booking_button" style="display:none;">
						<?php 
						$time = date('d-m-Y');
						?>
						<input type="hidden" name="start_date" value="<?php echo $time ?>">
						<input type="hidden" name="end_date" value="<?php echo $time ?>">
						<input type="hidden" name="start_time" value="09:00">
						<input type="hidden" name="end_time" value="10:00">
						<input type="hidden" name="timezone" value="Australia/Sydney">					
						<input type="hidden" class="property_id" name="property_id" value="0">
						<button type="submit" class="noo-button booking_button_submit">Book</button>
					</form>
				</div>
			</div> 
		</div>
		<script>
			var interval_obj  = setInterval(checkformbooking,1000);
			function checkformbooking(){
				if( jQuery('#resource_booking_form_event_details').length ) 
				{
					 jQuery('#form_booking_map').hide(); 
					 clearInterval(interval_obj);
				}
			}
			jQuery('.book_map_close').on('click',function(){
				jQuery('#form_booking_map').hide();
			}); 
			setInterval( function loadvaluebook(){
				if(jQuery("#google_calender_start_date").val() != ''){
					jQuery("#form_booking_map input[name='start_date']").val(jQuery("#google_calender_start_date").val());
				}
				if(jQuery("#google_calender_start_date").val() != ''){
					jQuery("#form_booking_map input[name='end_date']").val(jQuery("#google_calender_start_date").val());
				}
				if(jQuery("#noo-item-google_calender_start_time").val() != '' ){
					jQuery("#form_booking_map input[name='start_time']").val(jQuery("#noo-item-google_calender_start_time").val());
				}
				if(jQuery("#noo-item-google_calender_end_time").val() != ''){
					jQuery("#form_booking_map input[name='end_time']").val(jQuery("#noo-item-google_calender_end_time").val());
				}
				if(jQuery("#noo-item-google_calender_timezone").val() != ''){
					jQuery("#form_booking_map input[name='timezone']").val(jQuery("#noo-item-google_calender_timezone").val());
				}
			},1500);	 
		</script>
		<?php
		echo ob_get_clean();
}
?>