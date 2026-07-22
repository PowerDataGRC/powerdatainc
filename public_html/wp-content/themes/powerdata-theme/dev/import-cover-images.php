<?php
/**
 * Imports the 5 category cover images into the Media Library as real
 * attachments, so publishers can manually pick one as a Featured Image
 * (in addition to the automatic category-based fallback in inc/covers.php,
 * which serves the same files directly from covers/ and doesn't need
 * them in the Media Library at all).
 *
 * Only the hero (1600x900) file is imported per category — WordPress
 * generates the pd-article-thumb (800x450) crop from it automatically,
 * same 16:9 ratio, no separate thumb attachment needed.
 *
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/import-cover-images.php
 * Safe to re-run — skips any cover already imported (matched by title).
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "This file must be run via WP-CLI (wp eval-file ...).\n";
	exit( 1 );
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$manifest_path = get_stylesheet_directory() . '/covers/covers.json';
if ( ! file_exists( $manifest_path ) ) {
	WP_CLI::error( 'covers/covers.json not found — run the render pipeline first.' );
}

$manifest = json_decode( file_get_contents( $manifest_path ), true );
if ( ! $manifest ) {
	WP_CLI::error( 'covers/covers.json is empty or invalid.' );
}

foreach ( $manifest as $entry ) {
	$title = 'PowerData Cover — ' . ucwords( str_replace( '-', ' ', $entry['category'] ) );

	$existing = get_page_by_title( $title, OBJECT, 'attachment' );
	if ( $existing ) {
		WP_CLI::log( "'$title' already imported (attachment ID {$existing->ID}) — skipping." );
		continue;
	}

	$file_path = get_stylesheet_directory() . '/covers/' . $entry['file'];
	if ( ! file_exists( $file_path ) ) {
		WP_CLI::warning( "File not found for '{$entry['category']}': {$entry['file']} — skipping." );
		continue;
	}

	// media_handle_sideload() needs a real uploaded-style tmp copy, not a
	// direct path into the theme — copy it into the uploads dir first.
	$upload_dir = wp_upload_dir();
	$tmp_path   = $upload_dir['path'] . '/' . $entry['file'];
	copy( $file_path, $tmp_path );

	$file_array = [
		'name'     => $entry['file'],
		'tmp_name' => $tmp_path,
	];

	$attachment_id = media_handle_sideload( $file_array, 0, $title );

	if ( is_wp_error( $attachment_id ) ) {
		WP_CLI::warning( "Failed to import '{$entry['category']}': " . $attachment_id->get_error_message() );
		@unlink( $tmp_path );
		continue;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $entry['alt'] );
	wp_update_post( [
		'ID'           => $attachment_id,
		'post_excerpt' => 'PowerData on-brand cover — ' . $entry['category'] . ' category.',
	] );

	WP_CLI::success( "Imported '$title' as attachment ID $attachment_id." );
}

WP_CLI::success( 'Cover image import complete.' );
