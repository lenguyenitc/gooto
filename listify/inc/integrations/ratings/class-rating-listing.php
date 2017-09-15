<?php

class Listify_Rating_Listing extends Listify_Rating {

    public function __construct( $args = array() ) {
        parent::__construct( $args );
    }

    public function save() {
        global $wpdb;

        delete_transient( 'listify_review_count_' . $this->object_id );

        $query = $wpdb->prepare( "
            SELECT SUM(wpcm.meta_value)
            FROM $wpdb->comments AS wpc
            JOIN $wpdb->commentmeta AS wpcm
                ON wpc.comment_id  = wpcm.comment_id
            WHERE wpcm.meta_key = 'rating'
                AND wpc.comment_post_ID = %s
                AND wpc.comment_approved = '1'
                AND wpc.user_id != %s
        ", $this->object_id, get_post_field( 'post_author', $this->object_id ) );

        $total = $wpdb->get_var( $query );
        $votes = $this->count();

        if ( ! $total || $votes == 0 ) {
            update_post_meta( $this->object_id, 'rating', 0 );

            return;
        }

        $avg    = $total / $votes;
        $rating = round( round( $avg * 2 ) / 2, 1 );

        update_post_meta( $this->object_id, 'rating', $rating );

        return $rating;
    }

    public function get() {
        $this->rating = $this->object->rating;

        if ( ! $this->rating ) {
            $this->rating = 0;
        }

        return $this->rating;
    }

    public function count() {
        global $wpdb;

        $post_id = $this->object_id;

        $count_hash = 'listify_review_count_' . $post_id;
        
        $result = get_transient( $count_hash );

        if ( false === $result ) {
            $where = '';

            if ( $post_id > 0 ) {
                $where = $wpdb->prepare( "
                    SELECT COUNT(comment_ID)
                    FROM $wpdb->comments AS wpc
                    WHERE wpc.comment_post_ID = %s
                    AND wpc.comment_approved = '1'
                    AND wpc.user_id != %s
                ", $post_id, get_post_field( 'post_author', $this->object_id ) );
            }

            $result = $wpdb->get_var( $where );

            set_transient( $count_hash, $result );
        }

        return $result;
    }

}
