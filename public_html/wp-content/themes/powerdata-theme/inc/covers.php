<?php
/**
 * Article cover image system — sizes + category auto-rotation fallback.
 * See covers/DEVELOPERS.md for how the images themselves are built.
 *
 * @package PowerData
 */

add_action( 'after_setup_theme', function () {
	add_image_size( 'pd-article-hero', 1600, 900, true );
	add_image_size( 'pd-article-thumb', 800, 450, true );
} );

/**
 * When a post has no manually set Featured Image, fall back to a cover
 * assigned deterministically from its primary category. A real Featured
 * Image always overrides — this filter only runs when $html is empty.
 */
add_filter( 'post_thumbnail_html', 'pd_default_cover', 10, 5 );
function pd_default_cover( $html, $post_id, $thumb_id, $size, $attr ) {
	if ( $html ) {
		return $html;
	}

	$terms = get_the_category( $post_id );
	if ( empty( $terms ) ) {
		return $html;
	}

	$slug  = $terms[0]->slug;
	$count = pd_cover_count( $slug );
	if ( $count < 1 ) {
		return $html;
	}

	$n = ( $post_id % $count ) + 1;

	$is_thumb = in_array( $size, [ 'pd-article-thumb', 'thumbnail', 'medium' ], true );
	$file     = $is_thumb
		? sprintf( '%s-%02d-thumb.webp', $slug, $n )
		: sprintf( '%s-%02d.webp', $slug, $n );
	$url      = get_stylesheet_directory_uri() . '/covers/' . $file;

	$manifest = pd_cover_manifest_entry( $slug, $n );
	$alt      = $manifest ? $manifest['alt'] : get_the_title( $post_id );

	// A real Featured Image would have been built via wp_get_attachment_image(),
	// which honors whatever $attr the caller passed (class, loading, etc.) —
	// do the same here instead of hardcoding, so the fallback gets identical
	// CSS treatment (aspect-ratio/object-fit) to a real upload in the same
	// call site. get_the_post_thumbnail() passes $attr through unmodified
	// when there's no thumbnail ID, so this is exactly what the caller asked for.
	$attr            = is_array( $attr ) ? $attr : [];
	$attr['class']   = trim( ( $attr['class'] ?? '' ) . ' pd-cover' );
	$attr['loading'] = $attr['loading'] ?? 'lazy';
	$attr['alt']     = $attr['alt'] ?? $alt;

	$attr_html = '';
	foreach ( $attr as $key => $value ) {
		$attr_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	return sprintf(
		'<img src="%s" width="%d" height="%d"%s />',
		esc_url( $url ),
		$is_thumb ? 800 : 1600,
		$is_thumb ? 450 : 900,
		$attr_html
	);
}

/**
 * How many covers exist for a given category slug (reads the manifest,
 * falls back to counting files if it's missing).
 */
function pd_cover_count( $slug ) {
	$manifest = pd_cover_manifest();
	$count    = 0;
	foreach ( $manifest as $entry ) {
		if ( isset( $entry['category'] ) && $entry['category'] === $slug ) {
			$count++;
		}
	}
	return $count;
}

function pd_cover_manifest_entry( $slug, $n ) {
	$manifest = pd_cover_manifest();
	$matches  = array_values( array_filter( $manifest, function ( $entry ) use ( $slug ) {
		return isset( $entry['category'] ) && $entry['category'] === $slug;
	} ) );
	return $matches[ $n - 1 ] ?? null;
}

function pd_cover_manifest() {
	static $manifest = null;
	if ( null === $manifest ) {
		$path     = get_stylesheet_directory() . '/covers/covers.json';
		$manifest = file_exists( $path ) ? ( json_decode( file_get_contents( $path ), true ) ?: [] ) : [];
	}
	return $manifest;
}
