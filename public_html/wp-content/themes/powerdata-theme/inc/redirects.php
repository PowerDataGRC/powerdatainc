<?php
/**
 * URL migration redirects — posts moved from the site root to
 * /articles/%postname%/. Preserves live inbound links.
 *
 * @package PowerData
 */

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}

	$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );

	if ( ! $path || strpos( $path, '/' ) !== false ) {
		return; // only single-segment root paths
	}

	// /posts/ -> /articles/. The "Articles" listing is now a Page (so it
	// gets a clean /articles/ URL instead of /articles/articles/), which
	// means WP core's wp_old_slug_redirect() doesn't catch this: it only
	// checks the query_vars['name'] post-permalink var, not 'pagename'.
	if ( 'posts' === $path ) {
		wp_safe_redirect( home_url( '/articles/' ), 301 );
		exit;
	}

	$post = get_page_by_path( $path, OBJECT, 'post' );
	if ( $post ) {
		wp_safe_redirect( get_permalink( $post ), 301 );
		exit;
	}
} );
