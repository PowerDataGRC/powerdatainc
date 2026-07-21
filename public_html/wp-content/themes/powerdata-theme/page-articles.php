<?php
/**
 * Template Name: PD Articles Listing
 * Template Post Type: page
 *
 * The "All Articles" index (lives at /articles/ via the "Articles" Page).
 * Replaces the old Display Post Types shortcode with a real, paginated
 * WP_Query built on the same card/pagination/empty-state parts as every
 * other listing in the theme.
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_articles_listing' );

function pd_render_articles_listing() {
	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	$query = new WP_Query( [
		'post_type'      => 'post',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'tax_query'      => [
			[
				'taxonomy' => 'article_status',
				'field'    => 'slug',
				'terms'    => [ 'archived' ],
				'operator' => 'NOT IN',
			],
		],
	] );
	?>

	<section class="pd-section-tight">
		<div class="pd-wrap">
			<span class="eyebrow">Articles</span>
			<h1 class="h-section"><?php the_title(); ?></h1>
			<p class="lede" style="max-width:640px;">Practical guidance on cyber protection, compliance, and running a tighter operation — written for owners, not IT departments.</p>
		</div>
	</section>

	<section class="pd-section-tight" style="background:var(--surface-2);">
		<div class="pd-wrap">
			<?php if ( $query->have_posts() ) : ?>
				<div class="pd-grid pd-grid-3">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						get_template_part( 'template-parts/content-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
				echo paginate_links( [
					'total'     => $query->max_num_pages,
					'current'   => $paged,
					'prev_text' => '← Newer',
					'next_text' => 'Older →',
					'mid_size'  => 1,
				] );
				?>
			<?php else : ?>
				<?php
				get_template_part( 'template-parts/empty-state', null, [
					'message' => 'No articles published yet. Check back soon.',
				] );
				?>
			<?php endif; ?>
		</div>
	</section>

	<?php
}

genesis();
