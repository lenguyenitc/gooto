<?php
/**
 * Search filters. 
 *
 * The theme contains a `job-filters-flat.php` and `job-filters.php` template file.
 * The latter being an override of the WP Job Manager plugin.
 *
 * This handles some modifications to those template files.
 *
 * @since 1.8.0
 *
 * @package Listify
 */
class Listify_WP_Job_Manager_Template_Filters {

	/**
	 * Get homepage filters.
	 *
	 * @since 1.8.0
	 *
	 * @return array $filters An array of filters and their respective markup.
	 */
	public static function get_filters( $display = 'home', $atts = array() ) {
		$filters = self::get_all_filters( $atts );
		$output = array();

		$default = array( 'keyword', 'location', 'category' );
		$chosen = get_theme_mod( 'search-filters-' . $display, $default );

		// convert a list
		if ( ! is_array( $chosen ) ) {
			$chosen = array_map( 'trim', explode( ',', $chosen ) );
		}

		$chosen = array_unique( $chosen );

		foreach ( $chosen as $key ) {
			$output[] = $filters[ $key ];
		}

		return $output;
	}

	/**
	 * Get the available filters and their output.
	 *
	 * @since 1.8.0
	 *
	 * @return array $filters An array of filters and their respective markup.
	 */
	public static function get_all_filters( $atts ) {
		return array(
			'keyword' => self::get_keywords_filter( $atts ),
			'location' => self::get_location_filter( $atts ),
			'category' => self::get_category_filter( $atts )
		);
	}

	/**
	 * Get the keywords filter markup.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_keywords_filter( $atts = array() ) {
		ob_start();

		if ( ! isset( $atts[ 'keywords' ] ) ) {
			$atts[ 'keywords' ] = '';
		}

		if ( ! empty( $_GET['search_keywords'] ) ) {
			$atts[ 'keywords' ] = sanitize_text_field( $_GET['search_keywords'] );
		}
?>

<div class="search_keywords">
	<label for="search_keywords"><?php _e( 'Keywords', 'listify' ); ?></label>
	<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'What are you looking for?', 'listify' ); ?>" value="<?php echo esc_attr( $atts[ 'keywords' ] ); ?>" />
</div>

<?php
		return ob_get_clean();
	}

	/**
	 * Get the location filter markup.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_location_filter( $atts = array() ) {
		ob_start();

		if ( ! isset( $atts[ 'location' ] ) ) {
			$atts[ 'location' ] = '';
		}

		if ( ! empty( $_GET['search_location'] ) ) {
			$atts[ 'location' ] = sanitize_text_field( $_GET['search_location'] );
		}
?>

<div class="search_location">
	<label for="search_location"><?php _e( 'Location', 'listify' ); ?></label>
	<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'listify' ); ?>" value="<?php echo esc_attr( $atts[ 'location' ] ); ?>" />
</div>

<?php
		return ob_get_clean();
	}

	/**
	 * Get the category filter markup.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_category_filter( $atts = array() ) {
		if ( ! get_option( 'job_manager_enable_categories' ) ) {
			return;
		}

		if ( ! isset( $atts[ 'show_category_multiselect' ] ) ) {
			$atts[ 'show_category_multiselect' ] = get_option( 'job_manager_enable_default_category_multiselect', false );

			/**
			 * Don't output a placeholder or select option on mobile since the chosen
			 * library will think this is a selected option.
			 *
			 * @see https://github.com/Astoundify/listify/issues/1319
			 */
			if ( $atts[ 'show_category_multiselect' ] ) {
				if ( wp_is_mobile() ) {
					$atts[ 'placeholder' ] = null;
					$atts[ 'show_option_all' ] = false;
				}
			}
		}

		if ( ! isset( $atts[ 'selected_category' ] ) ) {
			$atts[ 'selected_category' ] = '';
		}

		if ( ! empty( $_GET['search_category'] ) ) {
			$atts[ 'selected_category' ] = sanitize_text_field( $_GET[ 'search_category' ] );
		}

		$atts[ 'selected_category' ] = is_array( $atts[ 'selected_category' ] ) ? $atts[ 'selected_category' ] : array_map( 'trim', explode( ',', $atts[ 'selected_category' ] ) );

		ob_start();
?>

<div class="search_categories<?php if ( $atts[ 'show_category_multiselect' ] ) : ?> search_categories--multiselect<?php endif; ?>">
	<label for="search_categories"><?php _e( 'Category', 'listify' ); ?></label>

	<?php if ( $atts[ 'show_category_multiselect' ] ) : ?>
		<?php job_manager_dropdown_categories( array( 'taxonomy' => 'job_listing_category', 'hierarchical' => 1, 'show_option_all' => __( 'All categories', 'listify' ), 'name' => 'search_categories', 'orderby' => 'name', 'selected' => $atts[ 'selected_category' ] ) ); ?>
	<?php else : ?>
		<?php job_manager_dropdown_categories( array( 'taxonomy' => 'job_listing_category', 'hierarchical' => 1, 'show_option_all' => __( 'All categories', 'listify' ), 'name' => 'search_categories', 'orderby' => 'name', 'multiple' => false, 'selected' => $atts[ 'selected_category' ] ) ); ?>
	<?php endif; ?>
</div>

<?php
		return ob_get_clean();
	}

}

new Listify_WP_Job_Manager_Template_Filters();
