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
// Checking the returned HTML (not has_post_thumbnail()) so posts with no
// manually set Featured Image still get the category cover fallback —
// see inc/covers.php's post_thumbnail_html filter.
$thumb_image   = get_the_post_thumbnail( $post_id, 'pd-article-thumb', [ 'loading' => 'lazy' ] );
?>
<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="pd-art" style="text-decoration:none;">
	<?php if ( $thumb_image ) : ?>
	<div class="pd-art-thumb">
		<?php echo $thumb_image; ?>
	</div>
	<?php endif; ?>
	<span class="pd-art-cat"><?php echo esc_html( $cat_name ); ?><?php echo $is_archived ? ' · Archived' : ''; ?></span>
	<h4><?php echo esc_html( get_the_title( $post_id ) ); ?></h4>
	<p class="muted" style="font-size:14.5px;margin-top:4px;"><?php echo wp_trim_words( get_the_excerpt( $post_id ), $excerpt_words, '…' ); ?></p>
	<?php if ( 'default' === $variant ) : ?>
	<span class="muted" style="font-size:13px;"><?php echo esc_html( get_the_date( 'F j, Y', $post_id ) ); ?></span>
	<?php endif; ?>
</a>
