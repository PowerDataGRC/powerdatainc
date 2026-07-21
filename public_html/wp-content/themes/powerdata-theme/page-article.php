<?php
/**
 * Template Name: PD Article
 * Template Post Type: post, page
 *
 * Full-width article template for PowerData Insights.
 * Uses ACF for author bio and related posts.
 * Body content comes from the standard WordPress editor.
 *
 * @package PowerData
 */

// Remove the default Genesis loop; add ours instead.
remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_article_template' );

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
	$has_acf = function_exists( 'get_field' );

	// ACF author fields — fall back to WP author data when ACF isn't active.
	$author_name     = $has_acf ? get_field( 'article_author_name' )     : '';
	$author_title    = $has_acf ? get_field( 'article_author_title' )    : '';
	$author_bio      = $has_acf ? get_field( 'article_author_bio' )      : '';
	$author_photo    = $has_acf ? get_field( 'article_author_photo' )    : null;
	$author_linkedin = $has_acf ? get_field( 'article_author_linkedin' ) : '';
	$related_posts   = $has_acf ? get_field( 'article_related_posts' )   : [];

	if ( empty( $author_name ) ) {
		$author_name = get_the_author_meta( 'display_name' );
	}

	// Category label for eyebrow
	$cats     = get_the_category();
	$cat_name = $cats ? $cats[0]->name : 'Insights';

	// Reading-time estimate
	$reading_min = pd_reading_time_minutes( get_the_content() );

	// Author photo URL (ACF returns array or string depending on return format)
	$photo_url = '';
	if ( $author_photo ) {
		$photo_url = is_array( $author_photo ) ? ( $author_photo['url'] ?? '' ) : $author_photo;
	}
	?>

	<!-- ░░ ARTICLE HERO ░░ -->
	<section class="pd-article-hero pd-section-tight">
		<div class="pd-wrap">
			<div class="pd-article-hero-inner">
				<span class="eyebrow"><?php echo esc_html( $cat_name ); ?></span>
				<h1 class="pd-article-title"><?php the_title(); ?></h1>
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
	<?php if ( has_post_thumbnail() ) : ?>
	<div class="pd-article-featured-image">
		<div class="pd-wrap" style="max-width:900px;">
			<?php
			the_post_thumbnail(
				'full',
				[
					'class'   => 'pd-article-img',
					'loading' => 'eager',
					'alt'     => esc_attr( get_the_title() ),
				]
			);
			?>
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

				<?php if ( $author_name || $author_bio ) : ?>
				<!-- ── Author Bio ── -->
				<div class="pd-author-box">
					<?php if ( $photo_url ) : ?>
					<div class="pd-author-photo" aria-hidden="true">
						<img src="<?php echo esc_url( $photo_url ); ?>"
						     alt="<?php echo esc_attr( $author_name ); ?>"
						     width="72" height="72" loading="lazy">
					</div>
					<?php endif; ?>
					<div class="pd-author-info">
						<div class="pd-author-name-row">
							<strong class="pd-author-name"><?php echo esc_html( $author_name ); ?></strong>
							<?php if ( $author_title ) : ?>
								<span class="pd-author-name-title"><?php echo esc_html( $author_title ); ?></span>
							<?php endif; ?>
							<?php if ( $author_linkedin ) : ?>
							<a href="<?php echo esc_url( $author_linkedin ); ?>"
							   target="_blank" rel="noopener noreferrer"
							   class="pd-author-social"
							   aria-label="<?php echo esc_attr( $author_name ); ?> on LinkedIn">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
								</svg>
							</a>
							<?php endif; ?>
						</div>
						<?php if ( $author_bio ) : ?>
						<p class="pd-author-bio"><?php echo esc_html( $author_bio ); ?></p>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>

			</div><!-- /.pd-article-content-col -->
		</div><!-- /.pd-wrap -->
	</section>

	<!-- ░░ RELATED ARTICLES ░░ -->
	<?php if ( ! empty( $related_posts ) ) : ?>
	<section class="pd-section-tight" style="background:var(--surface-2);">
		<div class="pd-wrap">
			<h2 class="h-section" style="margin-bottom:40px;">Related Insights</h2>
			<div class="pd-grid pd-grid-3">
				<?php foreach ( array_slice( $related_posts, 0, 3 ) as $related ) :
					$rid = is_object( $related ) ? $related->ID : (int) $related;
					$rcats = get_the_category( $rid );
					?>
				<a href="<?php echo esc_url( get_permalink( $rid ) ); ?>" class="pd-art" style="text-decoration:none;">
					<?php if ( has_post_thumbnail( $rid ) ) : ?>
					<div class="pd-art-thumb">
						<?php echo get_the_post_thumbnail( $rid, 'medium', [ 'loading' => 'lazy' ] ); ?>
					</div>
					<?php endif; ?>
					<span class="pd-art-cat"><?php echo esc_html( $rcats ? $rcats[0]->name : 'Insights' ); ?></span>
					<h4><?php echo esc_html( get_the_title( $rid ) ); ?></h4>
					<p class="muted" style="font-size:14.5px;margin-top:4px;"><?php echo wp_trim_words( get_the_excerpt( $rid ), 18, '…' ); ?></p>
				</a>
				<?php endforeach; ?>
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

genesis();
