<?php
/**
 * The article body layout — single source of truth for how an individual
 * article renders. Used by both single.php (the default template for all
 * posts) and page-article.php (kept for any Page that explicitly selects
 * the "PD Article" template).
 *
 * @package PowerData
 */

/**
 * Main loop wrapper — stays thin; all layout lives in pd_article_body().
 */
function pd_render_article_template() {
	if ( ! have_posts() ) {
		return;
	}
	while ( have_posts() ) {
		the_post();
		pd_article_body();
	}
}

/**
 * Output the full article layout for the current post in The Loop.
 */
function pd_article_body() {
	$post_id       = get_the_ID();
	$pod           = function_exists( 'pods' ) ? pods( 'post', $post_id ) : null;
	$related_posts = $pod ? (array) $pod->field( 'article_related_posts' ) : [];
	$subtitle      = $pod ? $pod->display( 'article_subtitle' ) : '';
	$author_name   = get_the_author_meta( 'display_name' );

	// Category label for eyebrow
	$cats     = get_the_category();
	$cat_name = $cats ? $cats[0]->name : 'Insights';

	// Reading-time estimate
	$reading_min = pd_reading_time_minutes( get_the_content() );
	$is_archived = has_term( 'archived', 'article_status' );
	?>

	<?php if ( $is_archived ) : ?>
	<!-- ░░ ARCHIVED NOTICE ░░ -->
	<div class="pd-archived-notice">
		<div class="pd-wrap">
			<span class="pill amber">Archived</span>
			<span>This article is kept for reference. Some details may have changed since publication.</span>
		</div>
	</div>
	<?php endif; ?>

	<!-- ░░ ARTICLE HERO ░░ -->
	<section class="pd-article-hero pd-section-tight">
		<div class="pd-wrap">
			<div class="pd-article-hero-inner">
				<span class="eyebrow"><?php echo esc_html( $cat_name ); ?></span>
				<h1 class="pd-article-title"><?php the_title(); ?></h1>
				<?php if ( $subtitle ) : ?>
					<p class="pd-article-subtitle lede"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
				<div class="pd-article-meta">
					<span>By <?php echo esc_html( $author_name ); ?></span>
					<span class="pd-article-meta-sep" aria-hidden="true">·</span>
					<span><?php echo get_the_date( 'F j, Y' ); ?></span>
					<?php if ( $reading_min ) : ?>
						<span class="pd-article-meta-sep" aria-hidden="true">·</span>
						<span><?php echo esc_html( $reading_min ); ?> min read</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- ░░ FEATURED IMAGE ░░ -->
	<?php
	// Checking the returned HTML (not has_post_thumbnail()) so posts with no
	// manually set Featured Image still get the category cover fallback —
	// see inc/covers.php's post_thumbnail_html filter.
	$featured_image = get_the_post_thumbnail(
		get_the_ID(),
		'pd-article-hero',
		[
			'class'   => 'pd-article-img',
			'loading' => 'eager',
			'alt'     => esc_attr( get_the_title() ),
		]
	);
	?>
	<?php if ( $featured_image ) : ?>
	<div class="pd-article-featured-image">
		<div class="pd-wrap" style="max-width:900px;">
			<?php echo $featured_image; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- ░░ ARTICLE BODY ░░ -->
	<section class="pd-section-tight" style="background:var(--surface);">
		<div class="pd-wrap">
			<div class="pd-article-content-col">

				<div class="pd-article-prose">
					<?php the_content(); ?>
				</div>

				<?php get_template_part( 'template-parts/author-bio' ); ?>

			</div><!-- /.pd-article-content-col -->
		</div><!-- /.pd-wrap -->
	</section>

	<!-- ░░ RELATED ARTICLES ░░ -->
	<?php if ( ! empty( $related_posts ) ) : ?>
	<section class="pd-section-tight" style="background:var(--surface-2);">
		<div class="pd-wrap">
			<h2 class="h-section" style="margin-bottom:40px;">Related Insights</h2>
			<div class="pd-grid pd-grid-3">
				<?php
				foreach ( array_slice( $related_posts, 0, 3 ) as $related ) :
					$rid = is_array( $related ) ? (int) $related['ID'] : (int) $related;
					get_template_part( 'template-parts/content-card', null, [ 'post_id' => $rid, 'variant' => 'compact' ] );
				endforeach;
				?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- ░░ CTA BAND ░░ -->
	<section class="pd-section-tight" style="background:var(--paper);">
		<div class="pd-wrap">
			<div class="pd-article-cta-band">
				<div class="pd-article-cta-glow" aria-hidden="true"></div>
				<div class="pd-article-cta-inner">
					<div>
						<p class="eyebrow on-dark">Ready to act?</p>
						<h2 style="color:#fff;font-size:clamp(26px,3.5vw,38px);line-height:1.1;margin-bottom:14px;">
							Stop juggling spreadsheets.
						</h2>
						<p style="color:#AEB9C8;font-size:17px;line-height:1.6;max-width:460px;">
							Schedule a 30-min walkthrough and find out how PRIAM can simplify your operation.
						</p>
					</div>
					<div class="pd-article-cta-actions">
						<a href="https://priamtiv.com/hello"
						   class="btn btn-primary btn-lg"
						   target="_blank" rel="noopener noreferrer">
							Book a Walkthrough →
						</a>
						<a href="<?php echo esc_url( home_url( '/priam/' ) ); ?>"
						   class="btn btn-on-dark-ghost">
							Learn About PRIAM
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php
}
