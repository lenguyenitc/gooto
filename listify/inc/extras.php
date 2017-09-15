<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Listify
 */

/**
 * Callback for a short excerpt length.
 *
 * @since 1.5.0
 * @param int $length
 * @return int
 */
function listify_short_excerpt_length( $length ) {
	return 15;
}

/**
 * Remove ellipsis from the excerpt
 */
function listify_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'listify_excerpt_more' );

function listify_widget_posts_args( $args ) {
	if ( ! is_author() ) {
		return $args;
	}

	$args[ 'author' ] = get_the_author_meta( 'ID' );

	return $args;
}
add_filter( 'widget_posts_args', 'listify_widget_posts_args' );

// Shortcodes in widgets
add_filter( 'widget_text', 'do_shortcode' );

function listify_array_filter_deep( $item ) {
    if ( is_array( $item ) ) {
        return array_filter( $item, 'listify_array_filter_deep' );
    }

    if ( ! empty( $item ) ) {
        return true;
    }
}

function listify_get_terms( $args = array() ) {
	$args = wp_parse_args( $args, apply_filters( 'listify_get_terms_args', array(
		'orderby' => 'id',
		'order' => 'ASC',
		'hide_empty' => 1,
		'child_of' => 0,
		'exclude' => '',
		'hierarchical' => 0,
		'update_term_meta_cache' => false,
		'taxonomy' => 'job_listing_category'
	) ) );

	if ( ! listify_has_integration( 'wp-job-manager' ) ) {
		return get_terms( $args );
	}

	$terms_hash = 'jm_cats_' . md5( json_encode( $args ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'jm_get_' . $args[ 'taxonomy' ] ) );
	$terms = get_transient( $terms_hash );

	if ( is_array( $terms ) ) {
		$terms = array_filter( $terms );
	}

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		$terms = get_terms( $args );

		set_transient( $terms_hash, $terms );
	}

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array();
	}

	return $terms;
}

function listify_header_video( $mod ) {
	// Ensure we are reading what is expected.
	wp_reset_query();

	if ( '' == get_the_content() ) {
		return $mod;
	}

	// Surely there is a better way to do this that I am missing.
	$shortcode = str_replace( strip_shortcodes( get_the_content() ), '', get_the_content() );
	$atts = shortcode_parse_atts( $shortcode );

	if ( empty( $atts ) ) {
		return $mod;
	}

	$srcs = array_merge( array( 'src' ), wp_get_video_extensions() );
	$srcs = array_fill_keys( array_values( $srcs ), '' );

	$url = array_intersect_key( $atts, $srcs );

	if ( ! empty( $url ) ) {
		return current( $url );
	}

	return $mod;
}
