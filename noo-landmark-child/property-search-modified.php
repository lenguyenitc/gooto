<?php
/**
 * Template Name: Property Search Modified
 *
 * @package LandMark
 * @author  KENT <tuanlv@vietbrain.com>
 */
$display_style 		= get_theme_mod( 'noo_property_listing_style', 'style-list' );
$class_grid		    = '';
if ( $display_style === 'style-grid' ) {
	$class_grid    = 'style-grid column';
	$class_column = 'noo-md-6';
}

$user_id            = noo_get_current_user(true);

$show_social 		= get_theme_mod( 'noo_property_social', true);
$show_favories 		= get_theme_mod( 'noo_property_favories', true);
$show_compare 		= get_theme_mod( 'noo_property_compare', true);

$is_favorites       = get_user_meta( $user_id, 'is_favorites', true );


$get_layout_page_search = noo_get_layout_page_search_template();
if ( !empty( $get_layout_page_search ) && $get_layout_page_search === 'half-map' ) :
	require noo_get_template( 'property-half-map' );
else :
?>
<?php get_header(); ?>
<?php //echo do_shortcode( '[vc_row 0="" container_width="yes" el_id="search" css=".vc_custom_1496063286189{margin-top: 0px !important;border-top-width: 0px !important;padding-top: 0px !important;}"][vc_column 0="" css=".vc_custom_1496063407891{background-color: rgba(255,255,255,0.8) !important;*background-color: rgb(255,255,255) !important;}"][noo_advanced_search_property title="Find A Meeting Space" text_button_search="Go"][/vc_column][/vc_row]' ); ?>
<?php 
echo do_shortcode( '[vc_row 0="" container_width="yes" el_id="search" css=".vc_custom_1496063286189{margin-top: 0px !important;border-top-width: 0px !important;padding-top: 0px !important;}"][vc_column 0="" css=".vc_custom_1496063407891{background-color: rgba(255,255,255,0.8) !important;*background-color: rgb(255,255,255) !important;}"][noo_advanced_search_property title="FIND A MEETING SPACE" option_2="_noise" option_4="none" option_5="none" option_6="none" option_7="none" text_button_search="Go"][/vc_column][/vc_row]' ); ?>


<?php 
/**
 * Create query
 */
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
if( is_front_page() && empty( $paged ) ) {
	$paged = get_query_var( 'page' );
}

$args = array(
	'post_type'   => 'noo_property',
	'post_status' => 'publish',
	'paged'       => $paged,
	'orderby' => 'meta_value',
    'order' => 'ASC',
);
if( function_exists( 'pll_current_language' ) ) {
	$args['lang'] = pll_current_language();
}

$r    = new WP_Query( $args );

?>
    <div id="primary" class="content-area">
	    <main id="main" class="site-main noo-container">
	        <div class="noo-row">
				<div class="<?php noo_landmark_func_main_class(); ?> noo-list-property <?php echo esc_attr( $class_grid ); ?>">
	                <?php
	                /**
	                 * Call box save search
	                 */
	                if ( isset( $_GET ) ) {
		               // noo_save_search_property( $_GET );
	                }
	                
	                ?>
	                <div class="noo-title-header noo-md-12">
				        <?php
				        /**
				         * Render title
				         */
				        noo_title_first_word( 'Your search results', sprintf( esc_html__( '%s listings found', 'noo-landmark-core' ), $r->found_posts ) );
				        ?>
				    </div>
					<?php

	                if ( $r->have_posts() ) :
		                while ( $r->have_posts() ) : $r->the_post();
							$property_id        = get_the_ID();
							$check_is_favorites = ( !empty( $is_favorites ) && in_array( $property_id, $is_favorites ) ) ? true : false;
							$class_favorites    = $check_is_favorites ? 'is_favorites' : 'add_favorites';
							$text_favorites     = $check_is_favorites ? esc_html__( 'View favorites', 'noo-landmark-core' ) : esc_html__( 'Add to favorites', 'noo-landmark-core' );
							$icon_favorites     = $check_is_favorites ? 'fa-heart' : 'fa-heart-o';
		                	require noo_get_template( 'property/item-property' );

		                endwhile;
		                noo_pagination_loop( array(), $r );
		                wp_reset_postdata();
		                wp_reset_query();
		            else :
		            	echo '<div class="noo-found">' . esc_html__( 'Nothing Found!', 'noo-landmark-core' ) . '</div>';
		            endif; ?>
	            </div><!-- /.noo-list-property -->
	            <?php get_sidebar(); ?>
			</div>
	    </main><!-- .site-main -->
	</div><!-- .content-area -->
<?php get_footer(); ?>
<?php endif;