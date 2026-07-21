<?php
/**
 * The article card — single source of truth for every listing
 * (archive, category, search, related posts, the Archive section).
 * Do not duplicate this markup elsewhere.
 *
 * Usage: get_template_part( 'template-parts/content-card', null, [
 *     'post_id' => $id,       // optional, defaults to the current loop post
 *     'variant' => 'compact', // optional, defaults to 'default'
 * ] );
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}

$variant       = isset( $args['variant'] ) ? $args['variant'] : 'default';
$cats          = get_the_category( $post_id );
$cat_name      = $cats ? $cats[0]->name : 'Insights';
$excerpt_words = 'compact' === $variant ? 18 : 22;
$is_archived   = has_term( 'archived', 'article_status', $post_id );
?>
<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="pd-art" style="text-decoration:none;">
	<?php if ( has_post_thumbnail( $post_id ) ) : ?>
	<div class="pd-art-thumb">
		<?php echo get_the_post_thumbnail( $post_id, 'medium', [ 'loading' => 'lazy' ] ); ?>
	</div>
	<?php endif; ?>
	<span class="pd-art-cat"><?php echo esc_html( $cat_name ); ?><?php echo $is_archived ? ' · Archived' : ''; ?></span>
	<h4><?php echo esc_html( get_the_title( $post_id ) ); ?></h4>
	<p class="muted" style="font-size:14.5px;margin-top:4px;"><?php echo wp_trim_words( get_the_excerpt( $post_id ), $excerpt_words, '…' ); ?></p>
	<?php if ( 'default' === $variant ) : ?>
	<span class="muted" style="font-size:13px;"><?php echo esc_html( get_the_date( 'F j, Y', $post_id ) ); ?></span>
	<?php endif; ?>
</a>
