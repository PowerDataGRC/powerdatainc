<?php
/**
 * [pd_articles] — small in-content "related reading" shortcode.
 * Replaces Display Post Types for this use case: reuses content-card.php
 * (the single source of truth for card markup) and always excludes
 * archived posts.
 *
 * @package PowerData
 */

add_shortcode( 'pd_articles', function ( $atts ) {

	$a = shortcode_atts( [
		'count'    => 3,
		'category' => '',
		'exclude'  => get_the_ID(),
	], $atts, 'pd_articles' );

	$args = [
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, (int) $a['count'] ),
		'post__not_in'        => array_filter( [ (int) $a['exclude'] ] ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true, // not paginating — skip the COUNT query
		'tax_query'           => [ [
			'taxonomy' => 'article_status',
			'field'    => 'slug',
			'terms'    => [ 'archived' ],
			'operator' => 'NOT IN',
		] ],
	];

	if ( $a['category'] ) {
		$args['category_name'] = sanitize_title( $a['category'] );
	}

	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		return '';
	}

	ob_start();
	echo '<div class="pd-grid pd-grid-3">';
	while ( $q->have_posts() ) {
		$q->the_post();
		get_template_part( 'template-parts/content-card', null, [ 'variant' => 'compact' ] );
	}
	echo '</div>';
	wp_reset_postdata();

	return ob_get_clean();
} );
