<?php
/**
 * Extends the Pods "user" content type with author-bio fields.
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/setup-pods-user-fields.php
 * Safe to re-run — skips any field that already exists.
 */

if ( ! function_exists( 'pods_api' ) ) {
    WP_CLI::error( 'Pods is not active.' );
}

$api = pods_api();

$pod = $api->load_pod( [ 'name' => 'user' ], false );
if ( ! $pod ) {
    $pod_id = $api->save_pod( [
        'name'    => 'user',
        'label'   => 'Users',
        'type'    => 'pod',
        'storage' => 'meta',
        'object'  => 'user',
    ] );
    $pod = $api->load_pod( [ 'id' => $pod_id ], false );
    WP_CLI::log( 'Created Pods "user" pod (extends WP Users).' );
}

$fields = [
    'author_bio'      => [
        'label' => 'Author Bio',
        'type'  => 'wysiwyg',
    ],
    'author_title'    => [
        'label' => 'Author Title',
        'type'  => 'text',
    ],
    'author_photo'    => [
        'label'     => 'Author Photo',
        'type'      => 'file',
        'file_type' => 'image',
    ],
    // Not in the original brief's field table, but preserves the LinkedIn
    // link the existing per-post author box already renders.
    'author_linkedin' => [
        'label' => 'Author LinkedIn URL',
        'type'  => 'website',
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

WP_CLI::success( 'Pods user fields ready.' );
