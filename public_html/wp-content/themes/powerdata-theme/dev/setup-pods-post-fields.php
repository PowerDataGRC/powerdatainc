<?php
/**
 * Extends the Pods "post" content type with the fields migrated off ACF
 * in Phase 4 (article subtitle/related posts, project case-study fields).
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/setup-pods-post-fields.php
 * Safe to re-run — skips any field that already exists.
 */

if ( ! function_exists( 'pods_api' ) ) {
	WP_CLI::error( 'Pods is not active.' );
}

$api = pods_api();

$pod = $api->load_pod( [ 'name' => 'post' ], false );
if ( ! $pod ) {
	// type 'post_type' + name 'post' extends the existing "post" post type
	// (it already exists in WP, so Pods attaches fields rather than
	// re-registering it) — same pattern as extending 'user' in Phase 1.
	$pod_id = $api->save_pod( [
		'name' => 'post',
		'label' => 'Posts',
		'type' => 'post_type',
	] );
	$pod = $api->load_pod( [ 'id' => $pod_id ], false );
	WP_CLI::log( 'Created Pods "post" pod (extends the built-in post type).' );
}

$fields = [
	'article_subtitle' => [
		'label' => 'Article Subtitle',
		'type'  => 'text',
	],
	'article_related_posts' => [
		'label'       => 'Related Articles',
		'type'        => 'pick',
		'pick_object' => 'post_type',
		'pick_val'    => 'post',
		'pick_format_type'   => 'multi',
		'pick_format_single' => 'autocomplete',
	],
	'project_client' => [
		'label' => 'Client',
		'type'  => 'text',
	],
	'project_purpose' => [
		'label' => 'Project Goal',
		'type'  => 'text',
	],
	'project_tech_tags' => [
		'label' => 'Technology Tags',
		'type'  => 'text',
	],
	'project_testimonial_quote' => [
		'label' => 'Client Testimonial',
		'type'  => 'textarea',
	],
	'project_testimonial_attr' => [
		'label' => 'Quote Attribution',
		'type'  => 'text',
	],
	'project_technologies' => [
		'label' => 'Technologies & Methodologies',
		'type'  => 'text',
	],
	'project_hero_image' => [
		'label'     => 'Hero Background Image',
		'type'      => 'file',
		'file_type' => 'image',
	],
	'project_stats' => [
		'label'      => 'At-a-Glance Stats',
		'type'       => 'group',
		'repeatable' => 1,
		'fields'     => [
			'stat_value' => [ 'label' => 'Value', 'type' => 'text' ],
			'stat_label' => [ 'label' => 'Label', 'type' => 'text' ],
		],
	],
];

foreach ( $fields as $name => $args ) {
	if ( isset( $pod['fields'][ $name ] ) ) {
		WP_CLI::log( "Field '$name' already exists — skipping." );
		continue;
	}
	$api->save_field( array_merge( $args, [
		'pod_id' => $pod['id'],
		'name'   => $name,
	] ) );
	WP_CLI::log( "Created field '$name'." );
}

WP_CLI::success( 'Pods post fields ready.' );
