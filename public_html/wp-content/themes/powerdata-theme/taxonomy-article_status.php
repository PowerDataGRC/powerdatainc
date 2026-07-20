<?php
/**
 * Article Status archive — currently only meaningfully browsed at the
 * "archived" term, where old articles stay reachable instead of
 * disappearing. See Phase 2 of the article-architecture brief.
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_article_status_archive' );

function pd_render_article_status_archive() {
	$term        = get_queried_object();
	$is_archived = $term && 'archived' === $term->slug;
	?>

	<section class="pd-section-tight">
		<div class="pd-wrap">
			<span class="eyebrow"><?php echo esc_html( $term ? $term->name : 'Articles' ); ?></span>
			<h1 class="h-section"><?php echo esc_html( $is_archived ? 'Archive' : ( $term ? $term->name : 'Articles' ) ); ?></h1>
			<p class="lede" style="max-width:640px;">
				<?php if ( $is_archived ) : ?>
					Older articles we're keeping available for reference. Some details may have changed since publication.
				<?php else : ?>
					Browse articles by status.
				<?php endif; ?>
			</p>
		</div>
	</section>

	<section class="pd-section-tight" style="background:var(--surface-2);">
		<div class="pd-wrap">
			<?php if ( have_posts() ) : ?>
				<div class="pd-grid pd-grid-3">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content-card' );
					endwhile;
					?>
				</div>
				<?php get_template_part( 'template-parts/pagination' ); ?>
			<?php else : ?>
				<?php
				get_template_part( 'template-parts/empty-state', null, [
					'message' => $is_archived
						? 'No archived articles yet.'
						: 'No articles here yet.',
				] );
				?>
			<?php endif; ?>
		</div>
	</section>

	<?php
}

genesis();
