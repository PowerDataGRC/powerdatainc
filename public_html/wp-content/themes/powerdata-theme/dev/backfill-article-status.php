<?php
/**
 * Assigns the "current" article_status term to every published post that
 * has no article_status term yet.
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/backfill-article-status.php
 * Safe to re-run — skips posts that already have a term.
 */

$posts = get_posts( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	// One-time backfill CLI job, not a template/shortcode listing query —
	// the "no posts_per_page => -1" rule targets request-time queries.
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

$updated = 0;
foreach ( $posts as $post_id ) {
	if ( ! has_term( '', 'article_status', $post_id ) ) {
		wp_set_object_terms( $post_id, 'current', 'article_status' );
		$updated++;
	}
}

WP_CLI::success( "Backfilled article_status on $updated post(s) (" . count( $posts ) . ' total published posts checked).' );
