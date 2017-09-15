<?php
/**
 * FacetWP filters for the homepage.
 *
 * @since 1.9.0
 * @package Listify
 */

global $listify_facetwp;

$facets = isset( $instance[ 'facets' ] ) ? array_map( 'trim', explode( ',', $instance[ 'facets' ] ) ) : listify_theme_mod( 'listing-archive-facetwp-defaults', array( 'keyword', 'location', 'category' ) );
$_facets = $listify_facetwp->get_homepage_facets( $facets );
?>

<div class="job_search_form job_search_form--count-<?php echo absint( count( $_facets ) ); ?>">
	<?php echo $listify_facetwp->template->output_facet_html( $_facets ); ?>

	<div class="facetwp-submit">
		<input type="submit" value="<?php _e( 'Search', 'listify' ); ?>" onclick="fwp_redirect()" />
	</div>

	<div style="display: none;">
		<?php echo do_shortcode( '[facetwp template="listings"]' ); ?>
	</div>

</div>

<script>
function fwp_redirect() {
	FWP.parse_facets();
	FWP.set_hash();

	var query_string = FWP.build_query_string();
	if ('' != query_string) {
		query_string = '?' + query_string;
	}
	var url = query_string;
	window.location.href = '<?php echo get_post_type_archive_link( 'job_listing' ); ?>' + url;
}
</script>
