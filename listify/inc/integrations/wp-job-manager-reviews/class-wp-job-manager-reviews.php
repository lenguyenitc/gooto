<?php
/**
 * WP Job Manager
 */

class Listify_WP_Job_Manager_Reviews extends Listify_Integration {

	public function integration() {
		return 'wp-job-manager-reviews';
	}

	public function __construct() {
		$this->has_customizer = true;
		$this->includes = array();
		$this->integration = 'wp-job-manager-reviews';

		parent::__construct();
	}

	public function setup_actions() {
        add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_scripts' ), 11 );

		add_action( 'single_job_listing_meta_start', array( $this, 'the_rating' ), 40 );
		add_action( 'listify_output_rating', array( $this, 'the_rating' ) );
		add_action( 'listify_listings_by_term_after', array( $this, 'the_rating' ) );

		add_filter( 'listify_listing_data', array( $this, 'listing_data' ), 30 );

		add_filter( 'get_comment_text', array( $this, 'review_comment_text' ), 11, 3 );
	}

    public function dequeue_scripts() {
        wp_dequeue_style( 'wp-job-manager-reviews' );
    }

	public function review_comment_text( $content, $comment, $args ) {
		if ( 0 != $comment->comment_parent || ! is_singular( 'job_listing' ) ) {
			return $content;
		}

		ob_start();
	?>
		<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="comment-rating">
			<span itemprop="ratingValue"><?php echo number_format( WPJMR()->review->get_review_average( $comment->comment_ID ), 1, '.', ',' ); ?></span>
		</div>
	<?php
		$average = ob_get_clean();

		return $average . $content;
	}

	public function the_rating( $comment ) {
		global $post;

		$number = WPJMR()->listing->reviews_count( $post->ID );
		$number = number_format( $number, 1, '.', ',' );
		$value  = number_format( WPJMR()->listing->reviews_average( $post->ID ), 1, '.', ',' );
		$stars  = WPJMR()->listing->display_stars( $post->ID );
		$has_rating = 0 != $value;
	?>
		<div <?php if ( $has_rating ) : ?>itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"<?php endif; ?> class="rating-<?php echo floor( $value ); ?> job_listing-rating-wrapper" title="<?php printf( '%d Reviews', $number ); ?>">
			<span class="job_listing-rating-stars">
				<?php echo $stars; ?>
			</span>

			<span class="job_listing-rating-average">
				<span <?php if ( $has_rating ) : ?>itemprop="ratingValue"<?php endif; ?>><?php echo $value; ?></span>
				
				<?php if ( $has_rating ) : ?>
					<meta itemprop="bestRating" content="5"/>
					<meta itemprop="worstRating" content="1"/>
					<meta itemprop="ratingCount"><?php echo $number; ?></meta>
				<?php endif; ?>
			</span>
			<span class="job_listing-rating-count">
				<?php printf( _n( '%d Review', '%d Reviews', $number, 'listify' ), $number ); ?>
			</span>
		</div>
	<?php
	}

	public function listing_data( $data ) {
		global $post;

		$rating = number_format( WPJMR()->listing->reviews_average( $post->ID ), 1, '.', ','  );

		$data[ 'rating' ] = sprintf( _n( '%d Star', '%d Stars', $rating, 'listify' ), $rating );

		return $data;
	}
}

$GLOBALS[ 'listify_job_manager_reviews' ] = new Listify_WP_Job_Manager_Reviews();
