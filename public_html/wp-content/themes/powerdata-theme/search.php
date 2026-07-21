<?php
/**
 * Search results.
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_search_results' );

function pd_render_search_results() {
	$term = get_search_query();
	?>

	<section class="pd-section-tight">
		<div class="pd-wrap">
			<span class="eyebrow">Search</span>
			<h1 class="h-section">Results for &ldquo;<?php echo esc_html( $term ); ?>&rdquo;</h1>
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
					'message' => "We couldn't find anything for that search. Try a different word or two.",
				] );
				?>
			<?php endif; ?>
		</div>
	</section>

	<?php
}

genesis();
