<?php
/**
 * Related listings.
 *
 * These do not have microdata. This template file will be removed
 * in Listify 2.0.0
 *
 * @package Listify
 * @since 1.14.0
 */
?>

<?php do_action( 'listify-listing-card-before' ); ?>

<li id="job_listing-<?php the_ID(); ?>" <?php echo apply_filters( 'listify_job_listing_data', '', false ); ?>>

	<div class="content-box">

		<?php do_action( 'listify_content_job_listing_before' ); ?>

		<a href="<?php the_permalink(); ?>" class="job_listing-clickbox" <?php if ( get_theme_mod( 'listing-archive-window', false ) && ! is_front_page() ) : ?>target="_blank"<?php endif; ?>></a>

		<header <?php echo apply_filters( 'listify_cover', 'job_listing-entry-header listing-cover' ); ?>>
            <?php do_action( 'listify_content_job_listing_header_before' ); ?>

			<div class="job_listing-entry-header-wrapper cover-wrapper">
				<?php do_action( 'listify_content_job_listing_header_start' ); ?>

				<div class="job_listing-entry-thumbnail">
					<div <?php echo apply_filters( 'listify_cover', 'list-cover' ); ?>></div>
				</div>
				<div class="job_listing-entry-meta">
					<?php do_action( 'listify_content_job_listing_meta' ); ?>
				</div>

				<?php do_action( 'listify_content_job_listing_header_end' ); ?>
			</div>

            <?php do_action( 'listify_content_job_listing_header_after' ); ?>
		</header><!-- .entry-header -->

		<?php do_action( 'listify_content_job_listing_after' ); ?>

	</div>
</li><!-- #post-## -->

<?php do_action( 'listify-listing-card-before' ); ?>
