<?php
/**
 * Article Status taxonomy — "current" vs "archived".
 * Taxonomy, not post meta: filtering by meta_query is unindexed and
 * degrades past ~100 posts. See Phase 2 of the article-architecture brief.
 *
 * @package PowerData
 */

add_action( 'init', function () {
	register_taxonomy( 'article_status', [ 'post' ], [
		'labels' => [
			'name'          => 'Article Status',
			'singular_name' => 'Article Status',
			'menu_name'     => 'Article Status',
		],
		'hierarchical'      => false,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true, // required for the block editor sidebar
		'rewrite'           => [ 'slug' => 'articles/status', 'with_front' => false ],
	] );
} );

/**
 * Seed the two terms once on init.
 */
add_action( 'init', function () {
	foreach ( [ 'current' => 'Current', 'archived' => 'Archived' ] as $slug => $name ) {
		if ( ! term_exists( $slug, 'article_status' ) ) {
			wp_insert_term( $name, 'article_status', [ 'slug' => $slug ] );
		}
	}
}, 11 );

/**
 * Default new posts to "current" so nothing is left untermed —
 * an untermed post is excluded from nothing, but is also invisible
 * to status filtering.
 */
add_action( 'save_post_post', function ( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! has_term( '', 'article_status', $post_id ) ) {
		wp_set_object_terms( $post_id, 'current', 'article_status' );
	}
}, 10, 2 );
