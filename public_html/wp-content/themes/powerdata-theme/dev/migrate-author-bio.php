<?php
/**
 * Recovers the author bio text that was entered in the editor but never
 * rendered anywhere (Phase 1 bug — see git history) from a revision of
 * the "Cybersecurity Interactive Plan for Small Businesses" post, and
 * saves it to the post author's Pods user fields.
 * Run AFTER dev/setup-pods-user-fields.php.
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/migrate-author-bio.php
 * Safe to re-run — skips if the user's Pods author_bio is already set.
 */

if ( ! function_exists( 'pods' ) ) {
	WP_CLI::error( 'Pods is not active.' );
}

$post = get_page_by_path( 'cybersecurity-interactive-plan-for-small-businesses', OBJECT, 'post' );
if ( ! $post ) {
	WP_CLI::error( 'Could not find the "Cybersecurity Interactive Plan" post.' );
}

$author_id = (int) $post->post_author;
$pod       = pods( 'user', $author_id );

if ( $pod->display( 'author_bio' ) ) {
	WP_CLI::success( 'Author bio already set on user ' . $author_id . ' — skipping.' );
	return;
}

// The live post's own "author_bio" meta is empty (the original bug); the
// real text survives in whichever revision last carried a non-empty value.
$revisions = wp_get_post_revisions( $post->ID );
$bio       = '';
foreach ( $revisions as $revision ) {
	$value = get_post_meta( $revision->ID, 'author_bio', true );
	if ( $value ) {
		$bio = $value;
		break;
	}
}

if ( ! $bio ) {
	WP_CLI::error( 'No author_bio value found in any revision of that post — nothing to migrate.' );
}

$pod->save( 'author_bio', wp_kses_post( trim( $bio ) ) );
$pod->save( 'author_title', 'Founder, PowerData Solutions Inc.' );

WP_CLI::success( "Migrated author bio to user ID $author_id." );
