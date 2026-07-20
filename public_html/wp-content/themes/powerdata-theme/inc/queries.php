<?php
/**
 * Query hygiene for article listings — keeps archived posts out of
 * normal listings without hiding, unpublishing, or 404-ing them.
 *
 * @package PowerData
 */

add_action( 'pre_get_posts', function ( $q ) {

	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}

	// The Archive section itself must show archived posts, paginated.
	if ( $q->is_tax( 'article_status' ) ) {
		$q->set( 'posts_per_page', 12 );
		return;
	}

	if ( $q->is_home() || $q->is_archive() || $q->is_search() || $q->is_feed() ) {
		// Merge into any existing tax_query (e.g. a category archive's own
		// term filter) rather than clobbering it.
		$tax_query   = (array) $q->get( 'tax_query' );
		$tax_query[] = [
			'taxonomy' => 'article_status',
			'field'    => 'slug',
			'terms'    => [ 'archived' ],
			'operator' => 'NOT IN',
		];
		$q->set( 'tax_query', $tax_query );

		if ( ! $q->is_feed() ) {
			$q->set( 'posts_per_page', 12 );
		}
	}
} );
