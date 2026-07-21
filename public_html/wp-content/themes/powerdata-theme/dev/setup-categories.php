<?php
/**
 * Creates the 5 approved article categories and assigns them to the
 * existing articles (identified by slug, not post ID, so this is
 * portable across environments).
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/setup-categories.php
 * Safe to re-run — skips categories/assignments that already exist.
 */

$categories = [
	'operational-efficiency' => 'Operational Efficiency',
	'data-protection'        => 'Data Protection',
	'compliance'             => 'Compliance',
	'health-check'           => 'Health Check',
	'cyber-security'         => 'Cyber-Security',
];

foreach ( $categories as $slug => $name ) {
	if ( ! term_exists( $slug, 'category' ) ) {
		wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
		WP_CLI::log( "Created category '$name'." );
	} else {
		WP_CLI::log( "Category '$name' already exists — skipping." );
	}
}

// slug => category slug (replaces whatever categories the post currently has)
$assignments = [
	'five-operational-risks-small-human-service-organizations-commonly-overlook' => 'operational-efficiency',
	'why-your-smb-needs-a-m365-administrator'                                    => 'cyber-security',
	'cybersecurity-interactive-plan-for-small-businesses'                        => 'cyber-security',
	'incident-reporting-is-not-risk-management'                                  => 'health-check',
	'hippaa-readiness-beyond-annual-training'                                    => 'compliance',
];

foreach ( $assignments as $post_slug => $cat_slug ) {
	$post = get_page_by_path( $post_slug, OBJECT, 'post' );
	if ( ! $post ) {
		WP_CLI::warning( "No post with slug '$post_slug' found — skipping." );
		continue;
	}
	wp_set_object_terms( $post->ID, $cat_slug, 'category' );
	WP_CLI::log( "Set category '$cat_slug' on post '$post_slug'." );
}

WP_CLI::success( 'Categories ready.' );
