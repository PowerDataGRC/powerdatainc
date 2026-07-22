<?php
/**
 * Assigns the PD Articles Listing template to the "Articles" page
 * (identified by slug, not a hardcoded post ID, so this is portable
 * across environments).
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/assign-articles-template.php
 * Safe to re-run.
 */

$page = get_page_by_path( 'articles', OBJECT, 'page' );
if ( ! $page ) {
	WP_CLI::error( 'No page with slug "articles" found.' );
}

update_post_meta( $page->ID, '_wp_page_template', 'page-articles.php' );
WP_CLI::success( "Assigned page-articles.php to page ID {$page->ID}." );
