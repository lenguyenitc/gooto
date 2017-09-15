<?php
/**
 * Template Name: Property Detail
 *
 * @package LandMark
 * @author  KENT <tuanlv@vietbrain.com>
 */
$user_id            = noo_get_current_user(true);

$agent_id           = intval( get_user_meta( $user_id, '_associated_agent_id', true ) );
					  apply_filters( 'wpml_object_id', $agent_id, 'noo_agent' );

$property_id        = get_the_ID();
$is_favorites       = get_user_meta( $user_id, 'is_favorites', true );
$check_is_favorites = ( !empty( $is_favorites ) && in_array( $property_id, $is_favorites ) ) ? true : false;
$class_favorites    = $check_is_favorites ? 'is_favorites' : 'add_favorites';
$text_favorites     = $check_is_favorites ? esc_html__( 'View favorites', 'noo-landmark-core' ) : esc_html__( 'Add to favorites', 'noo-landmark-core' );
$icon_favorites     = $check_is_favorites ? 'fa-heart' : 'fa-heart-o';

$body_style 		= isset($_GET['content_style']) ? $_GET['content_style'] : get_theme_mod( 'noo_property_post_content_style', 'default');
$body_style 		= apply_filters('noo_property_body_style', $body_style, $property_id );
$show_social 		= get_theme_mod( 'noo_property_social', true);
$show_print 		= get_theme_mod( 'noo_property_print', true);
$show_favories 		= get_theme_mod( 'noo_property_favories', true);
$url_page_favorites = get_permalink( noo_get_page_by_template( 'my-favorites.php' ) );


$property_status    = get_post_status( $property_id );
?>
<?php get_header(); ?>
    <div id="primary" class="content-area">
    	<?php
			/**
			 * @hook noo_before_property_detail
			 *
			 * @noo_property_add_gallery_image - 10
			 */
			if ( Noo_Property::is_expired() ) {
				do_action( 'noo_before_property_detail', $property_id, $agent_id, $user_id );
			}
		?>
	    <main class="site-main noo-container">
	        <div class="noo-row noo-single-property">
				<?php 
				/**
				 * Check property expired
				 */
				if ( Noo_Property::is_expired() ) :
				?>
				<div class="<?php noo_landmark_func_main_class(); ?> noo-single-property-content">
	                
					<?php
						/**
						 * @hook noo_before_property_content
						 */
						do_action( 'noo_before_property_content', $property_id, $agent_id, $user_id );
					?>

	                <?php while ( have_posts() ) : the_post(); ?>
						
						<?php
						/**
						 * Show notice when property is expired
						 */
						if ( $property_status === 'expired' ) {
							echo sprintf(
								'<div class="noo-property-expired content">%s</div>',
								esc_html__( 'This property is expired, please contact administrator!', 'noo-landmark-core' )
							);
						}
						?>

	                	<div class="noo-single-property-detail <?php echo apply_filters( 'noo_property_post_body_style', $body_style, $property_id ); ?>">

	                		<?php
	                			// Require file body template
            			 		require noo_get_template( 'property/single-property/body-style/' . $body_style );
            				?>
							
	                	</div>
	                <?php endwhile; ?>
	                <?php wp_reset_postdata(); ?>
					
					<?php
						/**
						 * @hook noo_after_property_content
						 *
						 * @noo_property_add_box_agent_contact - 10
						 * @noo_property_add_box_comment - 20
						 * @noo_property_add_related_property - 30
						 */
						do_action( 'noo_after_property_content', $property_id, $agent_id, $user_id );
					?>
					
	            </div>
	            <?php get_sidebar(); ?>

	            <?php
					/**
					 * Check property expired
					 */
					else :
						echo sprintf(
							'<div class="noo-property-expired">%s</div>',
							esc_html__( 'This property is expired, please contact administrator!', 'noo-landmark-core' )
						);
					endif;
				?>
			</div><!--/.noo-agent-list-->
	    </main><!-- .site-main -->
	    <?php
			/**
			 * @hook noo_after_property_detail
			 */
			do_action( 'noo_after_property_detail', $property_id, $agent_id, $user_id );
		?>
	</div><!-- .content-area -->
<?php get_footer(); ?>