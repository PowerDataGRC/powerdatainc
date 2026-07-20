<?php
/**
 * Category (topic) listings.
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_category_archive' );

function pd_render_category_archive() {
	$term = get_queried_object();
	?>

	<section class="pd-section-tight">
		<div class="pd-wrap">
			<span class="eyebrow">Articles</span>
			<h1 class="h-section"><?php echo esc_html( $term ? $term->name : 'Category' ); ?></h1>
			<?php if ( $term && $term->description ) : ?>
				<p class="lede" style="max-width:640px;"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
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
					'message' => 'No articles in this category yet. Check back soon.',
				] );
				?>
			<?php endif; ?>
		</div>
	</section>

	<?php
}

genesis();
