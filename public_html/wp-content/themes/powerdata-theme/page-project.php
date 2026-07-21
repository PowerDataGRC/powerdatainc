<?php
/**
 * Template Name: PD Project
 * Template Post Type: post, page
 *
 * Case study / portfolio template for PowerData technology projects.
 * Uses Pods for project meta: client, purpose, tech tags, stats,
 * testimonial, and technology toolbox. Body content (challenge, solution,
 * results prose) comes from the standard WordPress editor.
 *
 * Pods fields expected (registered in dev/setup-pods-post-fields.php):
 *   project_client             – text
 *   project_purpose            – text (one sentence)
 *   project_tech_tags          – text (comma-separated)
 *   project_stats              – repeatable group: stat_value / stat_label
 *   project_testimonial_quote  – textarea
 *   project_testimonial_attr   – text
 *   project_technologies       – text (comma-separated)
 *   project_hero_image         – file (image)
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_project_template' );

function pd_render_project_template() {
	if ( ! have_posts() ) {
		return;
	}
	while ( have_posts() ) {
		the_post();
		pd_project_body();
	}
}

function pd_project_body() {
	$pod = function_exists( 'pods' ) ? pods( 'post', get_the_ID() ) : null;

	$client       = $pod ? $pod->display( 'project_client' )            : '';
	$purpose      = $pod ? $pod->display( 'project_purpose' )           : '';
	$tech_tags    = $pod ? $pod->display( 'project_tech_tags' )         : '';
	$stats        = $pod ? (array) $pod->field( 'project_stats' )       : [];
	$testimonial  = $pod ? $pod->display( 'project_testimonial_quote' ) : '';
	$attr         = $pod ? $pod->display( 'project_testimonial_attr' )  : '';
	$technologies = $pod ? $pod->display( 'project_technologies' )      : '';
	$img_url      = $pod ? $pod->field( 'project_hero_image.guid' )     : '';

	// Parse comma-separated tag/technology strings into arrays.
	$tags_arr = $tech_tags
		? array_filter( array_map( 'trim', explode( ',', $tech_tags ) ) )
		: [];
	$tech_arr = $technologies
		? array_filter( array_map( 'trim', explode( ',', $technologies ) ) )
		: [];

	// Build hero background style when a custom image is provided.
	$hero_bg_style = '';
	if ( $img_url ) {
		$hero_bg_style = sprintf(
			'background-image:linear-gradient(rgba(15,27,45,.82),rgba(15,27,45,.92)),url(%s);background-size:cover;background-position:center;',
			esc_url( $img_url )
		);
	}
	?>

	<!-- ░░ HERO / PROJECT SUMMARY ░░ -->
	<section class="pd-project-hero"<?php if ( $hero_bg_style ) echo ' style="' . esc_attr( $hero_bg_style ) . '"'; ?>>
		<div class="pd-wrap">
			<div class="pd-project-hero-inner reveal">

				<p class="eyebrow on-dark">Case Study</p>

				<h1 class="pd-project-title"><?php the_title(); ?></h1>

				<?php if ( $client ) : ?>
				<p class="pd-project-client">
					<span style="opacity:.6;">Client:</span>
					<?php echo esc_html( $client ); ?>
				</p>
				<?php endif; ?>

				<?php if ( $purpose ) : ?>
				<p class="pd-project-purpose"><?php echo esc_html( $purpose ); ?></p>
				<?php endif; ?>

				<?php if ( $tags_arr ) : ?>
				<div class="pd-project-tags" aria-label="Technologies used">
					<?php foreach ( $tags_arr as $tag ) : ?>
					<span class="pd-project-tag"><?php echo esc_html( $tag ); ?></span>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

			</div>
		</div>
	</section>

	<!-- ░░ AT-A-GLANCE STATS ░░ -->
	<?php if ( $stats ) : ?>
	<section class="pd-project-stats-band">
		<div class="pd-wrap">
			<div class="pd-project-stats-row">
				<?php foreach ( $stats as $stat ) :
					$val   = $stat['stat_value'] ?? '';
					$label = $stat['stat_label']  ?? '';
					if ( ! $val && ! $label ) continue;
					?>
				<div class="pd-project-stat reveal">
					<b><?php echo esc_html( $val ); ?></b>
					<span><?php echo esc_html( $label ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- ░░ PROJECT DESCRIPTION (WP editor content) ░░ -->
	<section class="pd-section-tight" style="background:var(--surface);">
		<div class="pd-wrap">
			<div class="pd-project-content-col">
				<div class="pd-project-prose">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</section>

	<!-- ░░ RESULTS & TESTIMONIAL ░░ -->
	<?php if ( $testimonial ) : ?>
	<section class="pd-project-results-band pd-section-tight">
		<div class="pd-wrap">
			<blockquote class="pd-project-testimonial">
				<p>"<?php echo esc_html( $testimonial ); ?>"</p>
				<?php if ( $attr ) : ?>
				<cite><?php echo esc_html( $attr ); ?></cite>
				<?php endif; ?>
			</blockquote>
		</div>
	</section>
	<?php endif; ?>

	<!-- ░░ TECHNOLOGY TOOLBOX ░░ -->
	<?php if ( $tech_arr ) : ?>
	<section class="pd-section-tight" style="background:var(--surface-2);">
		<div class="pd-wrap">
			<p class="eyebrow" style="margin-bottom:12px;">Tools &amp; Methodologies</p>
			<h2 class="h-section" style="margin-bottom:10px;">Technologies Used</h2>
			<p class="lede" style="margin-bottom:36px;">Platforms, frameworks, and methodologies deployed in this engagement.</p>
			<div class="pd-project-toolbox">
				<?php foreach ( $tech_arr as $tech ) : ?>
				<span class="pd-project-tool"><?php echo esc_html( $tech ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- ░░ CTA ░░ -->
	<section class="pd-section-tight" style="background:var(--paper);">
		<div class="pd-wrap">
			<div class="pd-project-cta-band">
				<div class="pd-project-cta-glow" aria-hidden="true"></div>
				<div class="pd-project-cta-inner">
					<div>
						<p class="eyebrow on-dark" style="margin-bottom:16px;">Start a conversation</p>
						<h2 style="color:#fff;font-size:clamp(26px,3.5vw,40px);line-height:1.1;margin-bottom:14px;">
							Ready to tackle your next IT project?
						</h2>
						<p style="color:#AEB9C8;font-size:17px;line-height:1.6;max-width:500px;">
							Let's talk about your challenges and how PowerData can help you move faster with less risk.
						</p>
					</div>
					<div style="display:flex;flex-direction:column;gap:12px;">
						<a href="https://priamtiv.com/hello"
						   class="btn btn-primary btn-lg"
						   target="_blank" rel="noopener noreferrer">
							Book Your Consultation →
						</a>
						<a href="<?php echo esc_url( home_url( '/consulting/' ) ); ?>"
						   class="btn btn-on-dark-ghost">
							See Our Services
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php
}

genesis();
