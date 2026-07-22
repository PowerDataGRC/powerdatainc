<?php
/**
 * Converts the legacy "Articles" listing (a Post with slug "posts", using
 * a Display Post Types shortcode) into a Page with slug "articles". Pages
 * aren't affected by the post permalink structure, so this gets a clean
 * /articles/ URL instead of /articles/articles/ once permalinks are
 * changed to /articles/%postname%/.
 *
 * Run BEFORE dev/assign-articles-template.php and BEFORE changing the
 * permalink structure.
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/convert-articles-page.php
 * Safe to re-run — no-ops if already converted.
 */

$existing_page = get_page_by_path( 'articles', OBJECT, 'page' );
if ( $existing_page ) {
	WP_CLI::success( 'Already converted — a Page with slug "articles" exists (ID ' . $existing_page->ID . ').' );
	return;
}

$post = get_page_by_path( 'posts', OBJECT, 'post' );
if ( ! $post ) {
	WP_CLI::error( 'No post with slug "posts" found. Nothing to convert.' );
}

wp_update_post( [
	'ID'        => $post->ID,
	'post_type' => 'page',
	'post_name' => 'articles',
] );

WP_CLI::success( "Converted post ID {$post->ID} to a Page with slug \"articles\"." );
