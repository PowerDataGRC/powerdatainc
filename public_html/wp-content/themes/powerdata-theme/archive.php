<?php
/**
 * Fallback listing for any archive not covered by a more specific
 * template (category.php, taxonomy-article_status.php).
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_fallback_archive' );

function pd_render_fallback_archive() {
	?>

	<section class="pd-section-tight">
		<div class="pd-wrap">
			<span class="eyebrow">Articles</span>
			<h1 class="h-section"><?php the_archive_title(); ?></h1>
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
				get_template_part( 'template-parts/empty-state' );
				?>
			<?php endif; ?>
		</div>
	</section>

	<?php
}

genesis();
